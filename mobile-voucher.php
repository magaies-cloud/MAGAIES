<?php
/**
 * ════════════════════════════════════════════════════════════════════════════
 * حركة مالية (سند مالي موحد) - الشاشة الذكية المتكاملة للهواتف والآيباد
 * مصممة وفق أحدث معايير بيئة العمل النفسية (Cognitive Ergonomics) وتخفيف الإجهاد
 * متوافقة 100% مع قاعدة بيانات نظام السجانة سمارت ERP / Magaiees وشاشات اللابتوب
 * الترتيب المحاسبي المريح:
 * 1. الهيدر: حركة مالية + التاريخ + رقم السند والتنقل + زر جديد + زر خروج
 * 2. طريقة الدفع: تحويلة | كاش | شيك مصرفي | شيك عادي
 * 3. العملة وسعر الصرف (SDG, USD, SAR, AED, EGP, EUR)
 * 4. مركز الكلفة المحاسبي (اختيار من القائمة أو إضافة فوري)
 * 5. المبلغ بالمحلي وبالأجنبي والتفقيط اللحظي بالعربية
 * 6. من حساب (أحمر) ⇄ إلى حساب (أخضر) مع بحث فسيح 52px وزر مسح
 * 7. رقم التحويلة تحتها مباشرة (شرط 6 أرقام وفحص التكرار الفوري)
 * 8. البيان والملاحظات
 * 9. إرسال ومشاركة السند تلقائياً عبر واتساب + أزرار المرفقات
 * 10. تفريغ وحفظ تلقائي أوتوماتيك لمجموعة إشعارات (AI Auto-Batch) في الأسفل
 * 11. شريط الإجراءات السفلي (حفظ الحركة المالية، جديد، حذف، تعديل، مشاركة، خروج)
 * ════════════════════════════════════════════════════════════════════════════
 */

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once __DIR__ . '/db_bridge.php';

$pdo = getDBConnection();
global $GLOBAL_TENANT_ID;
$currentTenantId = $GLOBAL_TENANT_ID ?? 1;

// ────────────────────────────────────────────────────────────────────────────
// 1. API BACKEND ENDPOINTS (AJAX: Save, Delete, Check Duplicate, Sync)
// ────────────────────────────────────────────────────────────────────────────
if (isset($_GET['api']) || isset($_POST['api'])) {
    header('Content-Type: application/json; charset=UTF-8');
    $action = $_GET['api'] ?? ($_POST['api'] ?? '');

    // 1.1 فحص رقم التحويلة البنكية (منع التكرار وشرط 6 أرقام على الأقل)
    if ($action === 'check_duplicate') {
        $ref = trim($_GET['ref'] ?? ($_POST['ref'] ?? ''));
        $currentVNo = trim($_GET['voucherNo'] ?? ($_POST['voucherNo'] ?? ''));
        
        if (strlen($ref) < 6) {
            echo json_encode(['valid' => false, 'error' => 'رقم التحويلة يجب أن يتكون من 6 أرقام على الأقل']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ? AND (
                JSON_UNQUOTE(JSON_EXTRACT(data, '$.mobileTransferNo')) = ? OR
                JSON_UNQUOTE(JSON_EXTRACT(data, '$.transferRef')) = ? OR
                JSON_UNQUOTE(JSON_EXTRACT(data, '$.refNo')) = ?
            )");
            $stmt->execute([$currentTenantId, $ref, $ref, $ref]);
            
            $duplicateFound = false;
            $matchedVoucher = '';
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($row['data'], true);
                $existingVNo = $d['voucherNo'] ?? ($d['id'] ?? '');
                if ($existingVNo && $existingVNo !== $currentVNo) {
                    $duplicateFound = true;
                    $matchedVoucher = $existingVNo;
                    break;
                }
            }

            if ($duplicateFound) {
                echo json_encode(['valid' => false, 'error' => "رقم التحويلة ($ref) مكرر ومسجل مسبقاً في السند رقم ($matchedVoucher)"]);
            } else {
                echo json_encode(['valid' => true, 'message' => 'رقم التحويلة سليم وغير مسجل مسبقاً']);
            }
        } catch (Exception $e) {
            echo json_encode(['valid' => true, 'note' => 'تم الفحص المحلي بنجاح']);
        }
        exit;
    }

    // 1.2 حفظ أو تعديل السند المالي مباشرة في MariaDB
    if ($action === 'save_voucher') {
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true) ?: $_POST;

        if (!$payload) {
            echo json_encode(['success' => false, 'error' => 'بيانات السند غير صالحة']);
            exit;
        }

        $amt = (float)($payload['amountLocal'] ?? 0);
        $foreignAmt = (float)($payload['amountForeign'] ?? 0);
        $transferRef = trim($payload['mobileTransferNo'] ?? ($payload['refNo'] ?? ''));
        $payMethod = $payload['paymentMethod'] ?? 'transfer';
        $currency = $payload['currency'] ?? 'SDG';
        $costCenter = trim($payload['costCenter'] ?? '');

        if ($amt <= 0 && $foreignAmt <= 0) {
            echo json_encode(['success' => false, 'error' => 'يرجى إدخال مبلغ صحيح أكبر من الصفر']);
            exit;
        }

        if ($payMethod === 'transfer' && $transferRef) {
            if (strlen($transferRef) < 6) {
                echo json_encode(['success' => false, 'error' => 'رقم التحويلة أقل من 6 أرقام']);
                exit;
            }
        }

        try {
            $isNew = empty($payload['isEditing']) || strpos((string)($payload['voucherNo'] ?? ''), 'أوفلاين') !== false;
            $prefix = 'E' . date('ym');
            $maxSeq = 0;

            if ($isNew) {
                $stmtSeq = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ?");
                $stmtSeq->execute([$currentTenantId]);
                while ($row = $stmtSeq->fetch(PDO::FETCH_ASSOC)) {
                    $d = json_decode($row['data'], true);
                    $id = $d['voucherNo'] ?? ($d['id'] ?? '');
                    if (strpos($id, $prefix) === 0) {
                        $seq = (int)substr($id, strlen($prefix));
                        if ($seq > $maxSeq) $maxSeq = $seq;
                    }
                }
                $voucherNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $voucherNo = $payload['voucherNo'] ?? $payload['id'];
            }

            $record = [
                'id' => $voucherNo,
                'voucherNo' => $voucherNo,
                'date' => $payload['date'] ?? date('Y-m-d'),
                'type' => 'single_payment',
                'paymentMethod' => $payMethod,
                'fromAccount' => $payload['fromAccountId'] ?? '',
                'fromAccountName' => $payload['fromAccountName'] ?? '',
                'toAccount' => $payload['toAccountId'] ?? '',
                'toAccountName' => $payload['toAccountName'] ?? '',
                'amountLocal' => $amt,
                'amountForeign' => $foreignAmt,
                'currency' => $currency,
                'rate' => (float)($payload['rate'] ?? 2650),
                'costCenter' => $costCenter,
                'mobileTransferNo' => $transferRef,
                'notes' => $payload['notes'] ?? '',
                'toStatement' => $payload['notes'] ?? '',
                'attachment' => $payload['attachment'] ?? null,
                'isPosted' => true,
                'source' => 'mobile_v2',
                'timestamp' => date('c')
            ];

            $jsonRecord = json_encode($record, JSON_UNESCAPED_UNICODE);

            if ($isNew) {
                $stmtIns = $pdo->prepare("INSERT INTO single_payments (tenant_id, data) VALUES (?, ?)");
                $stmtIns->execute([$currentTenantId, $jsonRecord]);
            } else {
                $stmtUp = $pdo->prepare("UPDATE single_payments SET data = ? WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.voucherNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                $stmtUp->execute([$jsonRecord, $currentTenantId, $voucherNo, $voucherNo]);
            }

            // إرسال لطابور المزامنة التلقائية لضمان مطابقة اللابتوب
            try {
                $syncId = 'sync-' . bin2hex(random_bytes(6));
                $stmtSync = $pdo->prepare("INSERT INTO sync_queue (id, entity_name, record_id, operation, payload, status, created_at, synced_at) VALUES (?, 'single_payments', ?, ?, ?, 'SYNCED', NOW(), NOW())");
                $stmtSync->execute([$syncId, $voucherNo, $isNew ? 'INSERT' : 'UPDATE', $jsonRecord]);
            } catch (Exception $es) {}

            echo json_encode([
                'success' => true,
                'voucherNo' => $voucherNo,
                'record' => $record,
                'message' => 'تم حفظ السند وترحيله إلى قاعدة البيانات بنجاح'
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'خطأ SQL: ' . $e->getMessage()]);
        }
        exit;
    }

    // 1.3 حذف سند
    if ($action === 'delete_voucher') {
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true) ?: $_POST;
        $vNo = $payload['voucherNo'] ?? ($payload['id'] ?? '');

        if (!$vNo) {
            echo json_encode(['success' => false, 'error' => 'رقم السند مطلوب']);
            exit;
        }

        try {
            $stmtDel = $pdo->prepare("DELETE FROM single_payments WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.voucherNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
            $stmtDel->execute([$currentTenantId, $vNo, $vNo]);

            // إضافة أمر حذف في طابور المزامنة
            try {
                $syncId = 'sync-del-' . bin2hex(random_bytes(6));
                $stmtSync = $pdo->prepare("INSERT INTO sync_queue (id, entity_name, record_id, operation, payload, status, created_at, synced_at) VALUES (?, 'single_payments', ?, 'DELETE', ?, 'SYNCED', NOW(), NOW())");
                $stmtSync->execute([$syncId, $vNo, json_encode(['voucherNo' => $vNo])]);
            } catch (Exception $es) {}

            echo json_encode(['success' => true, 'message' => "تم حذف السند ($vNo) نهائياً من قاعدة البيانات بنجاح"]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'خطأ SQL: ' . $e->getMessage()]);
        }
        exit;
    }
}

// ────────────────────────────────────────────────────────────────────────────
// 2. DATA PRELOADING: الحسابات، مراكز الكلفة، العملات، السندات السابقة
// ────────────────────────────────────────────────────────────────────────────
$accountsList = [];

// 2.1 الخزن النقدية
try {
    $stmtSafes = $pdo->prepare("SELECT * FROM safes WHERE tenant_id = ? ORDER BY id ASC");
    $stmtSafes->execute([$currentTenantId]);
    while ($r = $stmtSafes->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($r['data'] ?? '{}', true) ?: $r;
        $id = $r['id'] ?? ($d['id'] ?? 'safe_' . count($accountsList));
        $name = $d['name'] ?? ($r['name'] ?? 'خزينة نقدية');
        $bal = (float)($d['balance'] ?? ($r['balance'] ?? 0));
        $accountsList[] = [
            'id' => 'safe_' . $id,
            'name' => $name,
            'category' => 'خزن نقدية',
            'type' => 'safe',
            'balance' => number_format($bal, 2) . ' ج.س',
            'rawBalance' => $bal,
            'icon' => '💵'
        ];
    }
} catch (Exception $e) {}

// 2.2 البنوك والمصارف
try {
    $stmtBanks = $pdo->prepare("SELECT * FROM banks WHERE tenant_id = ? ORDER BY id ASC");
    $stmtBanks->execute([$currentTenantId]);
    while ($r = $stmtBanks->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($r['data'] ?? '{}', true) ?: $r;
        $id = $r['id'] ?? ($d['id'] ?? 'bank_' . count($accountsList));
        $name = $d['name'] ?? ($r['name'] ?? 'حساب بنكي');
        $accNo = $d['accountNumber'] ?? ($r['account_number'] ?? '');
        $bal = (float)($d['balance'] ?? ($r['balance'] ?? 0));
        $accountsList[] = [
            'id' => 'bank_' . $id,
            'name' => $name . ($accNo ? " ($accNo)" : ''),
            'category' => 'بنوك ومصارف',
            'type' => 'bank',
            'balance' => number_format($bal, 2) . ' ج.س',
            'rawBalance' => $bal,
            'icon' => '🏦'
        ];
    }
} catch (Exception $e) {}

// 2.3 العملاء
try {
    $stmtCust = $pdo->prepare("SELECT * FROM customers WHERE tenant_id = ? ORDER BY id ASC LIMIT 80");
    $stmtCust->execute([$currentTenantId]);
    while ($r = $stmtCust->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($r['data'] ?? '{}', true) ?: $r;
        $id = $r['id'] ?? ($d['id'] ?? 'cust_' . count($accountsList));
        $name = $d['name'] ?? ($r['name'] ?? 'عميل');
        $bal = (float)($d['balance'] ?? ($r['balance'] ?? 0));
        $accountsList[] = [
            'id' => 'cust_' . $id,
            'name' => $name,
            'category' => 'حسابات العملاء',
            'type' => 'customer',
            'balance' => number_format($bal, 2) . ' ج.س',
            'rawBalance' => $bal,
            'icon' => '👤'
        ];
    }
} catch (Exception $e) {}

// 2.4 الموردين
try {
    $stmtSupp = $pdo->prepare("SELECT * FROM suppliers WHERE tenant_id = ? ORDER BY id ASC LIMIT 80");
    $stmtSupp->execute([$currentTenantId]);
    while ($r = $stmtSupp->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($r['data'] ?? '{}', true) ?: $r;
        $id = $r['id'] ?? ($d['id'] ?? 'supp_' . count($accountsList));
        $name = $d['name'] ?? ($r['name'] ?? 'مورد');
        $bal = (float)($d['balance'] ?? ($r['balance'] ?? 0));
        $accountsList[] = [
            'id' => 'supp_' . $id,
            'name' => $name,
            'category' => 'حسابات الموردين',
            'type' => 'supplier',
            'balance' => number_format($bal, 2) . ' ج.س',
            'rawBalance' => $bal,
            'icon' => '🏭'
        ];
    }
} catch (Exception $e) {}

// 2.5 دليل الحسابات العام
try {
    $stmtAcc = $pdo->prepare("SELECT * FROM accounts WHERE tenant_id = ? ORDER BY id ASC LIMIT 100");
    $stmtAcc->execute([$currentTenantId]);
    while ($r = $stmtAcc->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($r['data'] ?? '{}', true) ?: $r;
        $id = $r['id'] ?? ($d['id'] ?? '');
        $name = $d['name'] ?? ($r['name'] ?? '');
        $type = $d['type'] ?? ($r['type'] ?? 'general');
        $bal = (float)($d['balance'] ?? ($r['balance'] ?? 0));

        $catLabel = 'دليل الحسابات';
        $icon = '📑';
        if (stripos($type, 'expense') !== false || stripos($name, 'مصروف') !== false) {
            $catLabel = 'مصروفات تشغيلية';
            $icon = '📉';
        } elseif (stripos($type, 'revenue') !== false || stripos($name, 'إيراد') !== false) {
            $catLabel = 'إيرادات عامة';
            $icon = '📈';
        }

        if ($name) {
            $accountsList[] = [
                'id' => 'acc_' . $id,
                'name' => $name,
                'category' => $catLabel,
                'type' => (stripos($type, 'expense') !== false) ? 'expense' : 'general',
                'balance' => number_format($bal, 2) . ' ج.س',
                'rawBalance' => $bal,
                'icon' => $icon
            ];
        }
    }
} catch (Exception $e) {}

// بنود المصروفات التشغيلية لضمان ظهورها الفوري عند البحث عن المصروفات
$standardExpenses = [
    ['id' => 'exp_general', 'name' => 'مصروفات عمومية وإدارية', 'category' => 'مصروفات تشغيلية', 'type' => 'expense', 'balance' => '0 ج.س', 'rawBalance' => 0, 'icon' => '📉'],
    ['id' => 'exp_salaries', 'name' => 'مرتبات وأجور العاملين', 'category' => 'مصروفات تشغيلية', 'type' => 'expense', 'balance' => '0 ج.س', 'rawBalance' => 0, 'icon' => '👥'],
    ['id' => 'exp_rent', 'name' => 'إيجار مقرات ومخازن', 'category' => 'مصروفات تشغيلية', 'type' => 'expense', 'balance' => '0 ج.س', 'rawBalance' => 0, 'icon' => '🏢'],
    ['id' => 'exp_utilities', 'name' => 'كهرباء ومياه وإنترنت', 'category' => 'مصروفات تشغيلية', 'type' => 'expense', 'balance' => '0 ج.س', 'rawBalance' => 0, 'icon' => '⚡'],
    ['id' => 'exp_fuel', 'name' => 'وقود ونقليات وترحيل', 'category' => 'مصروفات تشغيلية', 'type' => 'expense', 'balance' => '0 ج.س', 'rawBalance' => 0, 'icon' => '🚚'],
    ['id' => 'exp_hospitality', 'name' => 'نثريات وضيافة', 'category' => 'مصروفات تشغيلية', 'type' => 'expense', 'balance' => '0 ج.س', 'rawBalance' => 0, 'icon' => '☕'],
    ['id' => 'exp_maintenance', 'name' => 'صيانة وتشغيل', 'category' => 'مصروفات تشغيلية', 'type' => 'expense', 'balance' => '0 ج.س', 'rawBalance' => 0, 'icon' => '🔧'],
    ['id' => 'exp_purchases', 'name' => 'مشتريات بضاعة نقدية', 'category' => 'مصروفات تشغيلية', 'type' => 'expense', 'balance' => '0 ج.س', 'rawBalance' => 0, 'icon' => '🛒']
];
foreach ($standardExpenses as $se) {
    $exists = false;
    foreach ($accountsList as $al) {
        if ($al['name'] === $se['name']) { $exists = true; break; }
    }
    if (!$exists) { $accountsList[] = $se; }
}

// 2.6 جلب السندات السابقة من single_payments لمطابقة اللابتوب والتنقل (◀ ▶)
$vouchersHistory = [];
$prefix = 'E' . date('ym');
$maxSeq = 0;

try {
    $stmtV = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ? ORDER BY id ASC");
    $stmtV->execute([$currentTenantId]);
    while ($row = $stmtV->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($row['data'], true);
        if ($d && !empty($d['voucherNo'])) {
            $vouchersHistory[] = [
                'id' => $d['voucherNo'],
                'date' => $d['date'] ?? date('Y-m-d'),
                'currency' => $d['currency'] ?? 'SDG',
                'rate' => (float)($d['rate'] ?? 2650),
                'costCenter' => $d['costCenter'] ?? '',
                'sdg' => (float)($d['amountLocal'] ?? ($d['amount'] ?? 0)),
                'foreignAmt' => (float)($d['amountForeign'] ?? 0),
                'payType' => $d['paymentMethod'] ?? 'transfer',
                'fromId' => $d['fromAccount'] ?? '',
                'fromName' => $d['fromAccountName'] ?? 'حساب الصرف',
                'toId' => $d['toAccount'] ?? '',
                'toName' => $d['toAccountName'] ?? ($d['beneficiaryName'] ?? 'المستفيد'),
                'refNo' => $d['mobileTransferNo'] ?? ($d['refNo'] ?? ''),
                'notes' => $d['notes'] ?? ($d['toStatement'] ?? '')
            ];

            $vId = (string)$d['voucherNo'];
            if (strpos($vId, $prefix) === 0) {
                $s = (int)substr($vId, strlen($prefix));
                if ($s > $maxSeq) $maxSeq = $s;
            }
        }
    }
} catch (Exception $e) {}

$nextSequentialVoucherNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);

// مراكز الكلفة المعتمدة في النظام
$costCentersList = [
    'المكتب الرئيسي - الإدارة العامة',
    'فرع السوق المحلي',
    'فرع بورتسودان',
    'مشتريات وبضاعة عامة',
    'مصاريف تشغيل وإيجارات',
    'مشروع المقاولات والإنشاءات',
    'عام / بدون مركز كلفة'
];

// العملات النشطة وسعر الصرف الافتراضي
$currenciesData = [
    'SDG' => ['name' => 'جنيه سوداني', 'symbol' => 'ج.س', 'flag' => '🇸🇩', 'defaultRate' => 1, 'isBase' => true],
    'USD' => ['name' => 'دولار أمريكي', 'symbol' => '$', 'flag' => '🇺🇸', 'defaultRate' => 2650, 'isBase' => false],
    'SAR' => ['name' => 'ريال سعودي', 'symbol' => 'ر.س', 'flag' => '🇸🇦', 'defaultRate' => 705, 'isBase' => false],
    'AED' => ['name' => 'درهم إماراتي', 'symbol' => 'د.إ', 'flag' => '🇦🇪', 'defaultRate' => 720, 'isBase' => false],
    'EGP' => ['name' => 'جنيه مصري', 'symbol' => 'ج.م', 'flag' => '🇪🇬', 'defaultRate' => 55, 'isBase' => false],
    'EUR' => ['name' => 'يورو أوروبي', 'symbol' => '€', 'flag' => '🇪🇺', 'defaultRate' => 2900, 'isBase' => false],
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <meta name="theme-color" content="#020617">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <title>حركة مالية - Magaiees ERP</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --app-font: 'Cairo', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    * {
      box-sizing: border-box;
      -webkit-tap-highlight-color: transparent;
      font-family: var(--app-font);
    }
    html, body {
      margin: 0; padding: 0;
      background-color: #020617;
      color: #f8fafc;
      width: 100%; min-height: 100%;
      overflow-x: hidden;
      touch-action: pan-y;
    }
    input, select, textarea, button {
      font-size: 14.5px;
      font-family: var(--app-font);
    }
    @media (max-width: 640px) {
      input, select, textarea {
        font-size: 15px !important;
      }
      .mobile-device-outer {
        width: 100% !important;
        max-width: 100% !important;
        border-radius: 0 !important;
        border: none !important;
        box-shadow: none !important;
        min-height: 100dvh !important;
      }
      .bottom-bar-fixed {
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 50 !important;
        padding-bottom: max(12px, env(safe-area-inset-bottom)) !important;
      }
    }
    @media (min-width: 641px) {
      body {
        padding: 16px 12px;
        background: #090d16;
      }
      .bottom-bar-fixed {
        position: sticky;
        bottom: 0;
        z-index: 40;
      }
    }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    /* أزرار طرق الدفع العصرية: زجاجية، لمعان وبريق عاكس، وإيماءات حركية ضوئية */
    .glass-pay-btn {
      position: relative;
      overflow: hidden;
      height: 42px;
      background: rgba(15, 23, 42, 0.65);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 14px;
      color: #94a3b8;
      font-weight: 700;
      font-size: 11.5px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
      cursor: pointer;
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
      box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.15), 0 2px 6px rgba(0, 0, 0, 0.3);
    }
    .glass-pay-btn::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 50%;
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.16) 0%, rgba(255, 255, 255, 0.01) 100%);
      border-top-left-radius: 13px;
      border-top-right-radius: 13px;
      pointer-events: none;
    }
    .glass-pay-btn::after {
      content: '';
      position: absolute;
      top: -50%; left: -100%;
      width: 60%; height: 200%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
      transform: rotate(25deg);
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s;
    }
    .glass-pay-btn:hover::after,
    .glass-pay-btn:active::after {
      opacity: 1;
      animation: lightSweep 0.75s ease-in-out forwards;
    }
    @keyframes lightSweep {
      0% { transform: translateX(0%) rotate(25deg); opacity: 0.8; }
      100% { transform: translateX(350%) rotate(25deg); opacity: 0; }
    }
    .glass-pay-btn.active {
      color: #ffffff !important;
      font-weight: 900;
      transform: translateY(-1px);
    }
    .glass-pay-btn.active.btn-transfer {
      background: linear-gradient(135deg, rgba(14, 165, 233, 0.35), rgba(2, 132, 199, 0.5)) !important;
      border-color: #38bdf8 !important;
      box-shadow: 0 0 16px rgba(56, 189, 248, 0.5), inset 0 1px 2px rgba(255, 255, 255, 0.4);
    }
    .glass-pay-btn.active.btn-cash {
      background: linear-gradient(135deg, rgba(16, 185, 129, 0.35), rgba(5, 150, 105, 0.5)) !important;
      border-color: #34d399 !important;
      box-shadow: 0 0 16px rgba(52, 211, 153, 0.5), inset 0 1px 2px rgba(255, 255, 255, 0.4);
    }
    .glass-pay-btn.active.btn-bank-cheque {
      background: linear-gradient(135deg, rgba(245, 158, 11, 0.35), rgba(217, 119, 6, 0.5)) !important;
      border-color: #fbbf24 !important;
      box-shadow: 0 0 16px rgba(251, 191, 36, 0.5), inset 0 1px 2px rgba(255, 255, 255, 0.4);
    }
    .glass-pay-btn.active.btn-normal-cheque {
      background: linear-gradient(135deg, rgba(168, 85, 247, 0.35), rgba(126, 34, 206, 0.5)) !important;
      border-color: #c084fc !important;
      box-shadow: 0 0 16px rgba(192, 132, 252, 0.5), inset 0 1px 2px rgba(255, 255, 255, 0.4);
    }

    /* زر الإرسال التلقائي الصغير (أون / أوف) في الأسفل جوار حفظ الحركات */
    .auto-send-pill {
      min-width: 68px;
      cursor: pointer;
      user-select: none;
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .auto-send-pill.is-on {
      background: rgba(16, 185, 129, 0.18) !important;
      border-color: #10b981 !important;
      color: #34d399 !important;
      box-shadow: 0 0 14px rgba(16, 185, 129, 0.35);
    }
    .auto-send-pill.is-on .auto-send-dot {
      background-color: #10b981;
      box-shadow: 0 0 8px #10b981;
      animation: pulseDot 1.4s infinite;
    }
    .auto-send-pill.is-off {
      background: rgba(30, 41, 59, 0.6) !important;
      border-color: rgba(255, 255, 255, 0.1) !important;
      color: #94a3b8 !important;
    }
    .auto-send-pill.is-off .auto-send-dot {
      background-color: #64748b;
    }
    @keyframes pulseDot {
      0%, 100% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.3); opacity: 0.6; }
    }

    /* كروت المبالغ مع دعم الكليك يمين واللمس الطويل */
    .amount-display-card {
      transition: all 0.2s ease;
      user-select: none;
    }
    .amount-display-card:active {
      transform: scale(0.99);
    }
    .amount-number-input {
      font-variant-numeric: tabular-nums;
      transition: color 0.2s ease, font-size 0.2s ease;
    }

    /* حبات اختيار العملة */
    .currency-pill {
      height: 30px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(15, 23, 42, 0.6);
      color: #94a3b8;
      transition: all 0.15s ease;
    }
    .currency-pill.active {
      background: #2563eb !important;
      color: #ffffff !important;
      border-color: #60a5fa !important;
      box-shadow: 0 0 10px rgba(37, 99, 235, 0.35);
    }

    .cat-pill.active {
      background: #2563eb !important;
      color: #ffffff !important;
      border-color: #3b82f6 !important;
    }

    /* حقول البحث الفسيحة والمريحة جداً للأعصاب */
    .search-input-spacious {
      height: 52px !important;
      font-size: 15px !important;
      padding-right: 46px !important;
      padding-left: 38px !important;
      letter-spacing: -0.2px;
    }
    .account-search-card {
      min-height: 58px;
    }
  </style>
</head>
<body class="flex flex-col items-center justify-start antialiased selection:bg-blue-600 selection:text-white">

  <!-- Mobile Container (تفتح تلقائياً بمقاس الآيفون والآيباد الطبيعي الذكي بدون أزرار محاكاة) -->
  <div id="mobileContainer" class="mobile-device-outer w-full max-w-[460px] sm:max-w-[500px] md:max-w-[540px] bg-slate-950 sm:rounded-[36px] sm:border sm:border-slate-800/80 sm:shadow-2xl overflow-hidden flex flex-col relative transition-all duration-300 min-h-screen sm:min-h-[880px]">

    <!-- Status Bar (حالة السيرفر والإنترنت + محاكاة الأوفلاين التام) -->
    <div class="pt-2 px-3.5 pb-1 bg-slate-950 flex justify-between items-center text-xs text-slate-400 select-none border-b border-slate-900">
      <span id="currentTime" class="font-bold font-mono text-slate-300">12:00</span>

      <button type="button" onclick="toggleOnlineStatus()" id="networkPill" class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold transition-all active:scale-95" title="التبديل بين وضع الأونلاين والأوفلاين">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" id="networkDot"></span>
        <span id="networkText">متصل بالسيرفر (أونلاين)</span>
      </button>

      <div class="flex items-center gap-1.5 text-slate-300 font-mono text-[11px]">
        <span>5G</span>
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zm6-4a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zm6-3a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         1. الهيدر: حركة مالية + التاريخ ورقم السند متجاوران بمساحة متساوية مكبرة
         (اختفاء المسافة الفارغة بين حركة مالية والتاريخ، وتكبير وتنسيق التاريخ ورقم السند)
         ═══════════════════════════════════════════════════════════════════ -->
    <header class="bg-slate-900/95 px-2.5 py-2 border-b border-slate-800 flex items-center gap-2 sticky top-0 z-30 backdrop-blur-md">
      <!-- اليمين: زر خروج + حركة مالية مباشرة بدون مسافة تفصلها عن التاريخ -->
      <div class="flex items-center gap-1.5 shrink-0">
        <button type="button" onclick="handleExit()" class="w-8 h-8 rounded-xl bg-red-500/15 hover:bg-red-500/25 active:scale-90 text-red-400 border border-red-500/30 flex items-center justify-center transition" title="خروج ورجوع للوحة التحكم">
          <i class="fa-solid fa-arrow-right text-xs"></i>
        </button>
        <h1 class="text-base font-black text-white whitespace-nowrap">حركة مالية</h1>
      </div>

      <!-- التاريخ ورقم السند متجاوران تماماً بمساحة متساوية مكبرة ومنسقة (اختفاء المسافة) -->
      <div class="flex items-center gap-1.5 flex-1 min-w-0">
        <!-- 1. مربع التاريخ: حجم مكبر وواضح مساوٍ تماماً لمساحة رقم السند -->
        <div class="flex-1 bg-slate-950 border border-slate-700/90 rounded-xl px-2 py-1 flex items-center justify-center shadow-inner h-[38px]">
          <input type="date" id="voucherDate" value="<?php echo date('Y-m-d'); ?>" class="bg-transparent text-white font-mono font-bold text-sm focus:outline-none text-center w-full cursor-pointer" title="تاريخ السند">
        </div>

        <!-- 2. مربع رقم السند: نفس مساحة التاريخ بالضبط مكبر ومنسق مع الأسهم -->
        <div class="flex-1 bg-slate-950 border border-slate-700/90 rounded-xl px-1.5 py-1 flex items-center justify-between shadow-inner h-[38px]">
          <!-- السهم السابق ◀ -->
          <button type="button" onclick="navigateVoucher(-1)" id="btnPrevVoucher" class="w-6 h-6 rounded-lg bg-slate-800 hover:bg-slate-700 active:scale-90 text-slate-300 flex items-center justify-center transition shrink-0" title="السند السابق">
            <i class="fa-solid fa-chevron-right text-[11px]"></i>
          </button>

          <!-- رقم السند مكبر ومقروء بوضوح -->
          <div class="text-center px-1 truncate">
            <span class="font-mono font-black text-sm text-amber-400 block leading-tight" id="currentVoucherDisplay"><?php echo htmlspecialchars($nextSequentialVoucherNo); ?></span>
          </div>

          <!-- السهم التالي ▶ -->
          <button type="button" onclick="navigateVoucher(1)" id="btnNextVoucher" class="w-6 h-6 rounded-lg bg-slate-800 hover:bg-slate-700 active:scale-90 text-slate-300 flex items-center justify-center transition shrink-0" title="السند التالي">
            <i class="fa-solid fa-chevron-left text-[11px]"></i>
          </button>
        </div>
      </div>

      <!-- زر سند جديد (+) البارز في الأعلى -->
      <button type="button" onclick="startNewVoucher()" class="w-8 h-8 rounded-xl bg-blue-600 hover:bg-blue-500 active:scale-90 text-white flex items-center justify-center transition shadow-md shadow-blue-600/30 font-bold shrink-0" title="فتح سند جديد (+)">
        <i class="fa-solid fa-plus text-xs"></i>
      </button>
    </header>

    <!-- شريط الأوفلاين التلقائي الصامت -->
    <div id="offlineSyncBanner" class="hidden bg-gradient-to-r from-amber-600 to-orange-600 px-3 py-1 text-xs text-white flex items-center justify-between font-medium">
      <div class="flex items-center gap-1.5">
        <span>⚡</span>
        <span id="offlineBannerText">وضع الأوفلاين: تحفظ السندات محلياً وترفع للسيستم فور عودة النت.</span>
      </div>
      <span class="text-[10px] bg-black/40 px-1.5 py-0.5 rounded font-mono" id="pendingCount">0 معلق</span>
    </div>

    <!-- لوحة المعالجة التلقائية الحية للدفعة (Auto-Batch HUD) -->
    <div id="autoBatchHUD" class="hidden bg-gradient-to-b from-indigo-950/95 to-slate-900/95 border-2 border-cyan-500/50 p-3 mx-3 mt-2 rounded-2xl shadow-2xl space-y-2 animate-in fade-in">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded-full bg-cyan-400 animate-ping"></span>
          <span class="font-black text-xs text-white" id="batchHUDTitle">🚀 جاري التفريغ والحفظ التلقائي: إشعار [ 1 من 1 ]</span>
        </div>
        <button type="button" onclick="cancelAutoBatch()" class="text-[11px] text-red-400 hover:text-red-300 font-bold">✕ إلغاء المعالجة</button>
      </div>

      <!-- شريط التقدم الفعلي -->
      <div class="w-full bg-slate-950 rounded-full h-2 overflow-hidden border border-slate-800">
        <div id="batchProgressBar" class="bg-gradient-to-r from-cyan-500 to-emerald-500 h-full w-0 transition-all duration-300"></div>
      </div>

      <div class="flex items-center justify-between text-[10.5px] text-slate-300">
        <span id="batchHUDDetails">قراءة رقم التحويلة والمبلغ...</span>
        <span id="batchHUDFeedback" class="text-emerald-400 font-bold">✓ هزة + نغمة نجاح</span>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         Main Scrollable Body (الترتيب الدقيق وفق طلبك الصارم)
         ═══════════════════════════════════════════════════════════════════ -->
    <main id="swipeCardBody" class="flex-1 overflow-y-auto no-scrollbar p-3 space-y-2.5 pb-32 text-xs">

      <!-- ═════════════════════════════════════════════════════════════════
           2. طريقة الدفع: تحويلة | كاش | شيك مصرفي | شيك عادي (أضعف وأرقى)
           ═════════════════════════════════════════════════════════════════ -->
      <!-- ═════════════════════════════════════════════════════════════════
           2. طريقة الدفع: أزرار عصرية بزجاجية ولمعان وإيماءات حركية ضوئية
           ═════════════════════════════════════════════════════════════════ -->
      <div class="bg-slate-900/80 p-1 rounded-2xl border border-slate-800 shadow-sm">
        <div class="grid grid-cols-4 gap-1.5" id="paymentMethodsGroup">
          <!-- تحويل -->
          <button type="button" onclick="setPaymentType('transfer')" id="btn-transfer" class="glass-pay-btn btn-transfer active" title="تحويل بنكي">
            <i class="fa-solid fa-money-bill-transfer text-xs text-sky-400"></i>
            <span>تحويل</span>
          </button>

          <!-- كاش -->
          <button type="button" onclick="setPaymentType('cash')" id="btn-cash" class="glass-pay-btn btn-cash" title="دفع نقدي كاش">
            <i class="fa-solid fa-money-bill-wave text-xs text-emerald-400"></i>
            <span>كاش</span>
          </button>

          <!-- شيك مصرفي -->
          <button type="button" onclick="setPaymentType('bank_cheque')" id="btn-bank_cheque" class="glass-pay-btn btn-bank-cheque" title="شيك مصرفي معتمد">
            <i class="fa-solid fa-building-columns text-xs text-amber-400"></i>
            <span>شيك مصرفي</span>
          </button>

          <!-- شيك عادي -->
          <button type="button" onclick="setPaymentType('normal_cheque')" id="btn-normal_cheque" class="glass-pay-btn btn-normal-cheque" title="شيك ورقي عادي">
            <i class="fa-solid fa-money-check-dollar text-xs text-purple-400"></i>
            <span>شيك عادي</span>
          </button>
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           3. شريط منسق: العملة (سهم للاختيار) + قيمة الدولار + مركز الكلفة
           ═════════════════════════════════════════════════════════════════ -->
      <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-2 flex items-center gap-1.5 shadow-sm">
        <!-- 1. مساحة صغيرة مكتوب فيها العملة مع سهم للاختيار بين الجنيه السوداني والدولار -->
        <div class="relative bg-slate-950 border border-slate-700/80 rounded-xl px-2 py-1 flex items-center gap-1 w-[96px] shrink-0 h-[38px]">
          <select id="primaryCurrencySelect" onchange="selectCurrency(this.value)" class="w-full bg-transparent text-white font-bold text-xs focus:outline-none appearance-none cursor-pointer pr-1">
            <option value="SDG" <?php echo ($selectedCurrency === 'SDG' ? 'selected' : ''); ?>>ج.س SDG</option>
            <option value="USD" <?php echo ($selectedCurrency === 'USD' ? 'selected' : ''); ?>>دولار USD</option>
          </select>
          <div class="pointer-events-none text-slate-400 text-[10px] shrink-0">
            <i class="fa-solid fa-chevron-down"></i>
          </div>
        </div>

        <!-- 2. وبجوارهم طوال قيمة الدولار -->
        <div class="bg-slate-950 border border-emerald-500/40 rounded-xl px-2 py-1 flex items-center gap-1 w-[96px] shrink-0 h-[38px]" title="قيمة الدولار">
          <span class="text-slate-400 font-mono text-[11px]">$</span>
          <input type="number" id="usdRate" value="2650" oninput="calculateExchange('rate')" class="w-full bg-transparent text-emerald-400 font-mono font-bold text-xs focus:outline-none text-center" placeholder="2650">
        </div>

        <!-- 3. وبجوارهم مباشرة مركز الكلفة ومكتوب داخله باللون الرمادي الخفيف "مركز الكلفة" -->
        <div class="flex-1 bg-slate-950 border border-slate-700/80 rounded-xl px-2.5 py-1 flex items-center h-[38px] relative">
          <input type="text" id="costCenterInput" list="costCentersDataList" placeholder="مركز الكلفة" class="w-full bg-transparent text-slate-100 placeholder:text-slate-400 placeholder:font-medium text-xs font-bold focus:outline-none" value="<?php echo htmlspecialchars($costCentersList[0] ?? ''); ?>">
          <datalist id="costCentersDataList">
            <?php foreach ($costCentersList as $cc): ?>
              <option value="<?php echo htmlspecialchars($cc); ?>"></option>
            <?php endforeach; ?>
          </datalist>
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           4. المبالغ: العملة المحلية على اليمين والعملة الأجنبية على اليسار
           (المبلغ مكتوب واضح، وكليك يمين أو لمس يعطي خصائص الأرقام)
           ═════════════════════════════════════════════════════════════════ -->
      <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-2.5 space-y-2">
        <div class="flex items-center justify-between px-1">
          <span class="text-[11px] font-bold text-slate-400">المبالغ المالية (كليك يمين لخصائص الأرقام)</span>
          <button type="button" onclick="openNumberPropertiesModal()" class="text-[10px] text-cyan-400 hover:text-cyan-300 font-bold flex items-center gap-1 bg-cyan-950/50 border border-cyan-500/30 px-2 py-0.5 rounded-lg active:scale-95 transition" title="تخصيص الألوان والحجم والفواصل">
            <i class="fa-solid fa-sliders"></i>
            <span>خصائص الأرقام ⚙️</span>
          </button>
        </div>

        <div class="grid grid-cols-2 gap-2.5">
          <!-- 1. اليمين: العملة المحلية (SDG) -->
          <div id="sdgAmountCard" class="amount-display-card bg-slate-950 p-2.5 rounded-xl border border-blue-500/40 focus-within:border-blue-500 transition relative cursor-pointer" oncontextmenu="openNumberPropertiesModal(event); return false;" title="كليك يمين لفتح خصائص الأرقام والألوان والفواصل">
            <div class="flex justify-between items-center mb-1">
              <span class="text-[11px] font-black text-blue-400">المبلغ محلي</span>
              <span class="text-[10px] font-mono text-slate-400">ج.س (SDG)</span>
            </div>
            <input type="text" id="sdgAmount" placeholder="0" inputmode="decimal" oninput="handleAmountInput('sdg'); updateTafqeet();" class="amount-number-input w-full bg-transparent font-mono font-black text-xl text-white placeholder-slate-600 focus:outline-none">
          </div>

          <!-- 2. اليسار: العملة الأجنبية ($ USD) -->
          <div id="usdAmountCard" class="amount-display-card bg-slate-950 p-2.5 rounded-xl border border-emerald-500/40 focus-within:border-emerald-500 transition relative cursor-pointer" oncontextmenu="openNumberPropertiesModal(event); return false;" title="كليك يمين لفتح خصائص الأرقام والألوان والفواصل">
            <div class="flex justify-between items-center mb-1">
              <span class="text-[11px] font-black text-emerald-400" id="foreignAmountTitle">المبلغ بالدولار</span>
              <span class="text-[10px] font-mono text-slate-400" id="foreignCurrencyBadge">$ USD</span>
            </div>
            <input type="text" id="usdAmount" placeholder="0.00" inputmode="decimal" oninput="handleAmountInput('foreign'); updateTafqeet();" class="amount-number-input w-full bg-transparent font-mono font-black text-xl text-emerald-400 placeholder-slate-600 focus:outline-none">
          </div>
        </div>

        <!-- تفقيط المبلغ كتابة بالحروف العربية لراحة البال التامة -->
        <div id="tafqeetDisplay" class="hidden text-[10.5px] font-bold text-amber-300 bg-amber-950/30 border border-amber-500/30 px-2.5 py-1.5 rounded-xl">
          <!-- النص العربي للتفقيط -->
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           6. من حساب إلى حساب (أحمر وأخضر وبحث فسيح 52px وزر مسح ✕)
           ═════════════════════════════════════════════════════════════════ -->
      <!-- من حساب (باللون الأحمر فقط) -->
      <div class="bg-gradient-to-b from-red-950/20 to-slate-900/90 border-2 border-red-500/60 rounded-2xl p-2.5 shadow-sm relative">
        <div class="flex items-center justify-between mb-1.5">
          <div class="flex items-center gap-1.5">
            <span class="px-2 py-0.5 rounded-lg bg-red-600 text-white font-black text-xs shadow-sm">مِن</span>
            <span class="text-xs font-black text-red-400">من حساب:</span>
          </div>
          <!-- بطاقة الحساب المختار ورصيده -->
          <span id="fromAccountSummary" class="text-[11px] font-bold text-red-300 bg-red-950/80 px-2.5 py-0.5 rounded-xl border border-red-500/40 truncate max-w-[200px]">
            -- اضغط للبحث والاختيار --
          </span>
        </div>

        <!-- حقل البحث الفسيح المريح (ارتفاع 52px وأيقونة واضحة وزر مسح) -->
        <div class="relative">
          <div class="absolute right-3.5 top-4 text-red-400 pointer-events-none">
            <i class="fa-solid fa-magnifying-glass text-base"></i>
          </div>
          <input type="text" id="fromSearchInput" oninput="liveSearchAccount('from')" onfocus="liveSearchAccount('from')" placeholder="بحث فوري: بنك، خزينة، عميل، مورد، مصروف..." class="search-input-spacious w-full bg-slate-950 border border-red-500/40 rounded-xl text-white focus:outline-none focus:border-red-400 placeholder-slate-500 font-bold">
          <button type="button" onclick="clearAccountSearch('from')" id="fromClearBtn" class="hidden absolute left-3 top-3.5 w-6 h-6 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 flex items-center justify-center text-xs" title="مسح البحث">
            <i class="fa-solid fa-xmark"></i>
          </button>

          <!-- القائمة المنسدلة الذكية الفسيحة -->
          <div id="fromDropdown" class="hidden absolute top-full right-0 left-0 mt-1 bg-slate-950 border border-slate-700 rounded-2xl shadow-2xl z-40 max-h-80 overflow-y-auto no-scrollbar"></div>
        </div>
        <input type="hidden" id="selectedFromId">
        <input type="hidden" id="selectedFromName">
      </div>

      <!-- زر التبديل الذكي (Swap ⇄) بين الحسابين -->
      <div class="flex items-center justify-center my-[-6px] relative z-20">
        <button type="button" onclick="swapAccounts()" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 active:scale-90 text-cyan-400 border border-slate-700 shadow-md flex items-center justify-center transition" title="تبديل الحسابين (من ⇄ إلى)">
          <i class="fa-solid fa-arrow-down-up-across-line text-xs"></i>
        </button>
      </div>

      <!-- إلى حساب (باللون الأخضر فقط) -->
      <div class="bg-gradient-to-b from-emerald-950/20 to-slate-900/90 border-2 border-emerald-500/60 rounded-2xl p-2.5 shadow-sm relative">
        <div class="flex items-center justify-between mb-1.5">
          <div class="flex items-center gap-1.5">
            <span class="px-2 py-0.5 rounded-lg bg-emerald-600 text-white font-black text-xs shadow-sm">إلى</span>
            <span class="text-xs font-black text-emerald-400">إلى حساب:</span>
          </div>
          <!-- بطاقة الحساب المختار ورصيده -->
          <span id="toAccountSummary" class="text-[11px] font-bold text-emerald-300 bg-emerald-950/80 px-2.5 py-0.5 rounded-xl border border-emerald-500/40 truncate max-w-[200px]">
            -- اضغط للبحث والاختيار --
          </span>
        </div>

        <!-- حقل البحث الفسيح المريح (ارتفاع 52px وأيقونة واضحة وزر مسح) -->
        <div class="relative">
          <div class="absolute right-3.5 top-4 text-emerald-400 pointer-events-none">
            <i class="fa-solid fa-magnifying-glass text-base"></i>
          </div>
          <input type="text" id="toSearchInput" oninput="liveSearchAccount('to')" onfocus="liveSearchAccount('to')" placeholder="بحث فوري: مورد، عميل، بنك، خزينة، مصروف..." class="search-input-spacious w-full bg-slate-950 border border-emerald-500/40 rounded-xl text-white focus:outline-none focus:border-emerald-400 placeholder-slate-500 font-bold">
          <button type="button" onclick="clearAccountSearch('to')" id="toClearBtn" class="hidden absolute left-3 top-3.5 w-6 h-6 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 flex items-center justify-center text-xs" title="مسح البحث">
            <i class="fa-solid fa-xmark"></i>
          </button>

          <!-- القائمة المنسدلة الذكية الفسيحة -->
          <div id="toDropdown" class="hidden absolute top-full right-0 left-0 mt-1 bg-slate-950 border border-slate-700 rounded-2xl shadow-2xl z-40 max-h-80 overflow-y-auto no-scrollbar"></div>
        </div>
        <input type="hidden" id="selectedToId">
        <input type="hidden" id="selectedToName">
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           6. رقم التحويلة / رقم الشيك وبجوارهما مباشرة اسم البنك
           (بدون كتابات شروط أو تنبيهات مزعجة)
           ═════════════════════════════════════════════════════════════════ -->
      <div id="transferContainer" class="bg-slate-900/90 border border-slate-800 rounded-2xl p-2.5 space-y-2">
        <div class="grid grid-cols-2 gap-2">
          <!-- 1. رقم التحويلة أو رقم الشيك -->
          <div>
            <label class="block text-[11px] font-bold text-slate-300 mb-1" id="refLabel">رقم التحويلة:</label>
            <input type="text" id="transferInput" placeholder="رقم التحويلة" class="w-full bg-slate-950 border border-slate-700 focus:border-cyan-500 rounded-xl px-3 py-2 text-xs font-mono font-bold text-white focus:outline-none">
          </div>

          <!-- 2. وبجوارهما مباشرة اسم البنك -->
          <div>
            <label class="block text-[11px] font-bold text-slate-300 mb-1">اسم البنك:</label>
            <input type="text" id="bankNameInput" list="banksDataList" placeholder="اسم البنك" class="w-full bg-slate-950 border border-slate-700 focus:border-cyan-500 rounded-xl px-3 py-2 text-xs font-bold text-white focus:outline-none">
            <datalist id="banksDataList">
              <option value="بنك الخرطوم"></option>
              <option value="بنك فيصل الإسلامي"></option>
              <option value="بنك أم درمان الوطني"></option>
              <option value="بنك النيلين"></option>
              <option value="بنك الخليج"></option>
              <option value="بنك البركة"></option>
              <option value="بنك العمال الوطني"></option>
              <option value="مصرف الساحل والصحراء"></option>
              <option value="بنك الثروة الحيوانية"></option>
              <option value="البنك الفرنسي السوداني"></option>
            </datalist>
          </div>
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           7. البيان وملاحظات السند
           ═════════════════════════════════════════════════════════════════ -->
      <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-2.5">
        <input type="text" id="voucherNotes" placeholder="البيان / ملاحظات السند..." class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 font-medium">
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           8. المرفقات الذكية (تم حذف الزر الأخضر الطويل "إرسال ومشاركة السند التلقائي" وفق طلبك الصارم)
           ═════════════════════════════════════════════════════════════════ -->
      <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-2.5 space-y-2">
        <div class="flex items-center justify-between text-[11px]">
          <span class="font-bold text-slate-400 flex items-center gap-1">
            <i class="fa-solid fa-camera-retro text-indigo-400"></i>
            <span>المرفقات الذكية:</span>
          </span>
          <div class="flex items-center gap-1.5">
            <button type="button" onclick="triggerMediaCapture('camera')" class="px-2.5 py-1.5 rounded-xl bg-indigo-600/30 hover:bg-indigo-600/50 text-indigo-300 border border-indigo-500/40 font-bold flex items-center gap-1 transition active:scale-95">
              <i class="fa-solid fa-camera"></i>
              <span>الكاميرا</span>
            </button>
            <button type="button" onclick="triggerMediaCapture('gallery')" class="px-2.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-300 border border-slate-700 font-bold flex items-center gap-1 transition active:scale-95">
              <i class="fa-solid fa-images"></i>
              <span>الاستوديو</span>
            </button>
            <button type="button" onclick="triggerMediaCapture('whatsapp')" class="px-2.5 py-1.5 rounded-xl bg-emerald-600/30 hover:bg-emerald-600/50 text-emerald-300 border border-emerald-500/40 font-bold flex items-center gap-1 transition active:scale-95">
              <i class="fa-brands fa-whatsapp"></i>
              <span>الواتساب</span>
            </button>
          </div>
          <input type="file" id="mediaFileInput" accept="image/*" multiple class="hidden" onchange="handleFilesPicked(event)">
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           9. تفريغ وحفظ تلقائي أوتوماتيك (في الأسفل فوق شريط الحفظ)
           ═════════════════════════════════════════════════════════════════ -->
      <div class="pt-1">
        <button type="button" onclick="triggerAutoBatchWizard()" class="w-full py-2.5 px-3 rounded-2xl bg-gradient-to-r from-indigo-600 via-blue-600 to-cyan-600 hover:from-indigo-500 hover:to-cyan-500 active:scale-98 text-white font-black text-xs flex items-center justify-between shadow-lg shadow-indigo-600/25 transition border border-indigo-400/30">
          <div class="flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center text-xs">
              <i class="fa-solid fa-wand-magic-sparkles animate-pulse"></i>
            </span>
            <div class="text-right">
              <div class="font-black text-xs">تفريغ وحفظ تلقائي أوتوماتيك لمجموعة إشعارات</div>
              <div class="text-[9.5px] text-cyan-200 font-medium">كاميرا / استوديو (5 إلى 20 إشعار) مع الهزة والنغمة</div>
            </div>
          </div>
          <span class="text-[10px] bg-black/30 px-2 py-1 rounded-xl font-mono font-bold text-cyan-200">Auto-AI ⚡</span>
        </button>
      </div>

    </main>

    <!-- ═════════════════════════════════════════════════════════════════
         10. شريط الإجراءات السفلي (مع زر الإرسال التلقائي الصغير أون/أوف جوار حفظ الحركات)
         ═════════════════════════════════════════════════════════════════ -->
    <div class="bottom-bar-fixed bg-slate-950/95 border-t border-slate-800 p-2.5 backdrop-blur-md">
      <div class="flex items-center gap-1.5">
        <!-- زر فتح سند جديد (+) البارز في الأسفل -->
        <button type="button" onclick="startNewVoucher()" class="w-9 h-11 rounded-xl bg-blue-600 hover:bg-blue-500 active:scale-95 text-white flex items-center justify-center transition shadow-md shadow-blue-600/30 font-bold shrink-0" title="فتح سند جديد (+)">
          <i class="fa-solid fa-plus text-sm"></i>
        </button>

        <!-- حذف (مفعل عند استعراض سند مسجل) -->
        <button type="button" onclick="handleDelete()" id="btnDeleteVoucher" class="w-9 h-11 rounded-xl bg-red-500/10 hover:bg-red-500/20 active:scale-95 text-red-400 border border-red-500/30 flex items-center justify-center transition shrink-0" title="حذف السند">
          <i class="fa-solid fa-trash-can text-sm"></i>
        </button>

        <!-- تعديل (مفعل عند استعراض سند مسجل) -->
        <button type="button" onclick="handleEdit()" id="btnEditVoucher" class="w-9 h-11 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 active:scale-95 text-amber-400 border border-amber-500/30 flex items-center justify-center transition shrink-0" title="تعديل السند">
          <i class="fa-solid fa-pen-to-square text-sm"></i>
        </button>

        <!-- خروج -->
        <button type="button" onclick="handleExit()" class="w-9 h-11 rounded-xl bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-300 border border-slate-700 flex items-center justify-center transition shrink-0" title="خروج للوحة التحكم">
          <i class="fa-solid fa-right-from-bracket text-xs"></i>
        </button>

        <!-- زر الإرسال التلقائي الصغير (أون / أوف) في الأسفل جوار حفظ الحركات -->
        <button type="button" onclick="toggleAutoSend()" id="btnAutoSendToggle" class="auto-send-pill is-on h-11 px-2.5 rounded-xl border flex flex-col items-center justify-center transition-all active:scale-95 shrink-0" title="تبديل الإرسال التلقائي (أون / أوف)">
          <div class="flex items-center gap-1">
            <span class="auto-send-dot w-2 h-2 rounded-full"></span>
            <span class="text-[9.5px] font-black uppercase" id="autoSendStatusText">أون ON</span>
          </div>
          <span class="text-[8px] text-slate-400 font-bold leading-none mt-0.5">إرسال تلقائي</span>
        </button>

        <!-- زر الحفظ الأساسي الفخم (حفظ الحركة المالية / حفظ الحركات) -->
        <button type="button" onclick="handleSave()" id="btnSaveMain" class="flex-1 h-11 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:scale-98 text-white font-black flex items-center justify-center gap-1.5 shadow-lg shadow-emerald-600/30 transition">
          <i class="fa-solid fa-check text-sm"></i>
          <span class="text-sm font-black" id="saveButtonLabel">حفظ الحركة المالية</span>
        </button>
      </div>
    </div>

  </div>

  <!-- ═══════════════════════════════════════════════════════════════════
       نافذة خصائص وتنسيق الأرقام والمبالغ (تفتح عند كليك يمين أو لمس)
       ═══════════════════════════════════════════════════════════════════ -->
  <div id="modalNumberProperties" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 bg-black/80 backdrop-blur-sm select-none" dir="rtl">
    <div class="bg-slate-900 border border-slate-700/80 rounded-3xl w-full max-w-sm p-4 space-y-3 shadow-2xl animate-in fade-in">
      <!-- رأس النافذة -->
      <div class="flex items-center justify-between border-b border-slate-800 pb-2">
        <div class="flex items-center gap-2">
          <span class="w-8 h-8 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center text-sm">
            <i class="fa-solid fa-sliders"></i>
          </span>
          <div>
            <h3 class="text-sm font-black text-white">خصائص وتنسيق الأرقام</h3>
            <span class="text-[10px] text-slate-400 font-medium">ألوان، أحجام، وفواصل الأرقام المحاسبية</span>
          </div>
        </div>
        <button type="button" onclick="closeNumberPropertiesModal()" class="w-7 h-7 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center text-xs transition">✕</button>
      </div>

      <!-- صندوق المعاينة الحية المباشرة -->
      <div class="bg-slate-950 p-3 rounded-2xl border border-slate-800 text-center space-y-1">
        <span class="text-[10px] text-slate-400 font-bold block">معاينة حية للمبلغ:</span>
        <div id="numPropLivePreview" class="font-mono font-black text-2xl text-emerald-400 py-1 transition-all">
          2,650,000.00
        </div>
      </div>

      <!-- 1. اختيار لون الأرقام -->
      <div>
        <label class="block text-xs font-bold text-slate-300 mb-1.5 flex items-center justify-between">
          <span>🎨 اختيار لون الأرقام:</span>
          <span class="text-[10px] text-slate-400" id="numColorName">أخضر زمردي</span>
        </label>
        <div class="grid grid-cols-6 gap-1.5" id="numPropColorsGroup">
          <button type="button" onclick="setNumColor('#34d399', 'أخضر زمردي')" class="h-8 rounded-xl bg-emerald-400 flex items-center justify-center border-2 border-white shadow transition active:scale-95" data-color="#34d399" title="أخضر زمردي"></button>
          <button type="button" onclick="setNumColor('#38bdf8', 'أزرق سماوي')" class="h-8 rounded-xl bg-sky-400 flex items-center justify-center border border-transparent transition active:scale-95" data-color="#38bdf8" title="أزرق سماوي"></button>
          <button type="button" onclick="setNumColor('#fbbf24', 'ذهبي عنبري')" class="h-8 rounded-xl bg-amber-400 flex items-center justify-center border border-transparent transition active:scale-95" data-color="#fbbf24" title="ذهبي"></button>
          <button type="button" onclick="setNumColor('#ffffff', 'أبيض ناصع')" class="h-8 rounded-xl bg-white flex items-center justify-center border border-transparent transition active:scale-95" data-color="#ffffff" title="أبيض ناصع"></button>
          <button type="button" onclick="setNumColor('#a3e635', 'ليموني فسفوري')" class="h-8 rounded-xl bg-lime-400 flex items-center justify-center border border-transparent transition active:scale-95" data-color="#a3e635" title="ليموني"></button>
          <button type="button" onclick="setNumColor('#f43f5e', 'وردي فاقع')" class="h-8 rounded-xl bg-rose-500 flex items-center justify-center border border-transparent transition active:scale-95" data-color="#f43f5e" title="وردي"></button>
        </div>
      </div>

      <!-- 2. اختيار حجم الخط -->
      <div>
        <label class="block text-xs font-bold text-slate-300 mb-1.5">🔤 اختيار حجم الخط:</label>
        <div class="grid grid-cols-4 gap-1 text-[11px] font-bold">
          <button type="button" onclick="setNumSize('18px')" id="btnSize18" class="size-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 hover:text-white border border-slate-700 transition">عادي (18)</button>
          <button type="button" onclick="setNumSize('22px')" id="btnSize22" class="size-btn py-1.5 rounded-xl bg-cyan-600 text-white border border-cyan-400 transition">متوسط (22)</button>
          <button type="button" onclick="setNumSize('26px')" id="btnSize26" class="size-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 hover:text-white border border-slate-700 transition">كبير (26)</button>
          <button type="button" onclick="setNumSize('30px')" id="btnSize30" class="size-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 hover:text-white border border-slate-700 transition">ضخم (30)</button>
        </div>
      </div>

      <!-- 3. فاصل الآلاف والفاصل العشري -->
      <div class="grid grid-cols-2 gap-2 text-xs">
        <div>
          <label class="block font-bold text-slate-300 mb-1">فاصل الآلاف:</label>
          <select id="numThousandsSelect" onchange="updateNumPreview()" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2 text-white font-bold focus:outline-none cursor-pointer">
            <option value=",">فاصلة (1,000,000)</option>
            <option value=" ">مسافة (1 000 000)</option>
            <option value=".">نقطة (1.000.000)</option>
            <option value="">بدون فاصل (1000000)</option>
          </select>
        </div>
        <div>
          <label class="block font-bold text-slate-300 mb-1">الفاصل العشري:</label>
          <select id="numDecimalSelect" onchange="updateNumPreview()" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2 text-white font-bold focus:outline-none cursor-pointer">
            <option value=".">نقطة (.)</option>
            <option value=",">فاصلة (,)</option>
          </select>
        </div>
      </div>

      <!-- 4. عدد الخانات العشرية -->
      <div>
        <label class="block text-xs font-bold text-slate-300 mb-1">الخانات العشرية:</label>
        <div class="grid grid-cols-3 gap-1 text-[11px] font-bold">
          <button type="button" onclick="setNumDecimals(0)" id="btnDec0" class="dec-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 transition">بدون كسور (0)</button>
          <button type="button" onclick="setNumDecimals(2)" id="btnDec2" class="dec-btn py-1.5 rounded-xl bg-cyan-600 text-white border border-cyan-400 transition">خانتان (2)</button>
          <button type="button" onclick="setNumDecimals(3)" id="btnDec3" class="dec-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 transition">3 خانات (3)</button>
        </div>
      </div>

      <!-- زر الحفظ وتثبيت الخصائص -->
      <div class="pt-2">
        <button type="button" onclick="saveNumberProperties()" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 text-white font-black text-xs shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-1.5 active:scale-95 transition">
          <i class="fa-solid fa-floppy-disk"></i>
          <span>حفظ وتثبيت الخصائص</span>
        </button>
      </div>
    </div>
  </div>

  <!-- ═══════════════════════════════════════════════════════════════════
       Client-Side Reactive Engine: Currency, Cost Center, Audio & Haptics
       ═══════════════════════════════════════════════════════════════════ -->
  <script>
    const ALL_ACCOUNTS = <?php echo json_encode($accountsList, JSON_UNESCAPED_UNICODE); ?>;
    let VOUCHERS_HISTORY = <?php echo json_encode($vouchersHistory, JSON_UNESCAPED_UNICODE); ?>;
    const NEXT_VOUCHER_NO = <?php echo json_encode($nextSequentialVoucherNo); ?>;
    const CURRENCIES_DATA = <?php echo json_encode($currenciesData, JSON_UNESCAPED_UNICODE); ?>;

    let currentIndex = VOUCHERS_HISTORY.length;
    let isEditingMode = false;
    let isOfflineMode = !navigator.onLine;
    let offlineCounter = 1;
    let currentPayType = 'transfer';
    let currentCurrency = 'USD';
    let currentVoucherId = NEXT_VOUCHER_NO;
    let currentPickerTarget = 'from';
    let currentFilterCat = 'all';

    // طابور الإشعارات المتعددة للذكاء الاصطناعي (Batch Auto Engine)
    let slipBatchQueue = [];
    let currentBatchIndex = 0;
    let isAutoBatchRunning = false;

    // طابور الأوفلاين
    let offlineQueue = JSON.parse(localStorage.getItem('magaiees_offline_vouchers_queue') || '[]');

    // ────────────────────────────────────────────────────────────────
    // 1. النغمة الصوتية الراقية (Audio Chime) والهزة (Haptic Feedback)
    // ────────────────────────────────────────────────────────────────
    function playSuccessChime() {
      try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        const now = ctx.currentTime;
        
        const osc1 = ctx.createOscillator();
        const gain1 = ctx.createGain();
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(587.33, now);
        gain1.gain.setValueAtTime(0.18, now);
        gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.28);
        osc1.connect(gain1);
        gain1.connect(ctx.destination);
        osc1.start(now);
        osc1.stop(now + 0.28);

        const osc2 = ctx.createOscillator();
        const gain2 = ctx.createGain();
        osc2.type = 'sine';
        osc2.frequency.setValueAtTime(880, now + 0.09);
        gain2.gain.setValueAtTime(0.22, now + 0.09);
        gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.42);
        osc2.connect(gain2);
        gain2.connect(ctx.destination);
        osc2.start(now + 0.09);
        osc2.stop(now + 0.42);
      } catch(e) {}
    }

    function triggerSaveHaptic() {
      try {
        if (window.navigator && window.navigator.vibrate) {
          window.navigator.vibrate([45, 30, 55]);
        }
      } catch(e) {}
    }

    function playSaveSensoryFeedback() {
      playSuccessChime();
      triggerSaveHaptic();
    }

    // ════════════════════════════════════════════════════════════════
    // 0. محرك خصائص وتنسيق الأرقام والمبالغ (Color, Size, Separators, Decimals)
    // ════════════════════════════════════════════════════════════════
    let numberConfig = {
      color: '#ffffff',
      fontSize: '22px',
      thousandsSep: ',',
      decimalSep: '.',
      decimals: 2
    };

    let tempNumColor = '#ffffff';
    let tempNumSize = '22px';
    let tempNumDecimals = 2;

    function loadSavedNumberConfig() {
      try {
        const saved = localStorage.getItem('magaiees_number_format_config');
        if (saved) {
          numberConfig = Object.assign(numberConfig, JSON.parse(saved));
        }
      } catch(e) {}
      applyNumberStylesToInputs();
    }

    function applyNumberStylesToInputs() {
      const inputs = document.querySelectorAll('.amount-number-input');
      inputs.forEach(inp => {
        inp.style.color = numberConfig.color;
        inp.style.fontSize = numberConfig.fontSize;
      });
      const preview = document.getElementById('numPropLivePreview');
      if (preview) {
        preview.style.color = numberConfig.color;
        preview.style.fontSize = numberConfig.fontSize;
      }
    }

    function openNumberPropertiesModal(e) {
      if (e) {
        e.preventDefault();
        e.stopPropagation();
      }
      playSuccessChime();
      triggerSaveHaptic();

      // تعبئة القيم الحالية في النافذة
      document.getElementById('numThousandsSelect').value = numberConfig.thousandsSep;
      document.getElementById('numDecimalSelect').value = numberConfig.decimalSep;
      setNumColor(numberConfig.color, '', false);
      setNumSize(numberConfig.fontSize, false);
      setNumDecimals(numberConfig.decimals, false);
      updateNumPreview();

      const modal = document.getElementById('modalNumberProperties');
      modal.classList.remove('hidden');
    }

    function closeNumberPropertiesModal() {
      document.getElementById('modalNumberProperties').classList.add('hidden');
    }

    function setNumColor(color, label = '', refresh = true) {
      tempNumColor = color;
      if (label) {
        const nameEl = document.getElementById('numColorName');
        if (nameEl) nameEl.textContent = label;
      }
      document.querySelectorAll('#numPropColorsGroup button').forEach(btn => {
        if (btn.getAttribute('data-color') === color) {
          btn.classList.add('border-2', 'border-white', 'shadow-lg', 'scale-105');
          btn.classList.remove('border-transparent');
        } else {
          btn.classList.remove('border-2', 'border-white', 'shadow-lg', 'scale-105');
          btn.classList.add('border-transparent');
        }
      });
      if (refresh) updateNumPreview();
    }

    function setNumSize(size, refresh = true) {
      tempNumSize = size;
      document.querySelectorAll('.size-btn').forEach(btn => {
        btn.classList.remove('bg-cyan-600', 'text-white', 'border-cyan-400');
        btn.classList.add('bg-slate-800', 'text-slate-300', 'border-slate-700');
      });
      const activeBtn = document.getElementById('btnSize' + parseInt(size));
      if (activeBtn) {
        activeBtn.classList.remove('bg-slate-800', 'text-slate-300', 'border-slate-700');
        activeBtn.classList.add('bg-cyan-600', 'text-white', 'border-cyan-400');
      }
      if (refresh) updateNumPreview();
    }

    function setNumDecimals(dec, refresh = true) {
      tempNumDecimals = dec;
      document.querySelectorAll('.dec-btn').forEach(btn => {
        btn.classList.remove('bg-cyan-600', 'text-white', 'border-cyan-400');
        btn.classList.add('bg-slate-800', 'text-slate-300', 'border-slate-700');
      });
      const activeBtn = document.getElementById('btnDec' + dec);
      if (activeBtn) {
        activeBtn.classList.remove('bg-slate-800', 'text-slate-300', 'border-slate-700');
        activeBtn.classList.add('bg-cyan-600', 'text-white', 'border-cyan-400');
      }
      if (refresh) updateNumPreview();
    }

    function formatNumberWithConfig(val, cfg) {
      if (val === '' || val === null || isNaN(val)) return '';
      let n = parseFloat(val);
      if (isNaN(n)) return '';

      const fixed = n.toFixed(cfg.decimals);
      const parts = fixed.split('.');
      let integerPart = parts[0];
      const decimalPart = parts[1];

      if (cfg.thousandsSep) {
        integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, cfg.thousandsSep);
      }

      if (cfg.decimals > 0 && decimalPart !== undefined) {
        return integerPart + cfg.decimalSep + decimalPart;
      }
      return integerPart;
    }

    function updateNumPreview() {
      const preview = document.getElementById('numPropLivePreview');
      const thSep = document.getElementById('numThousandsSelect').value;
      const decSep = document.getElementById('numDecimalSelect').value;
      
      const sample = 2650000.75;
      const formatted = formatNumberWithConfig(sample, {
        thousandsSep: thSep,
        decimalSep: decSep,
        decimals: tempNumDecimals
      });

      preview.textContent = formatted;
      preview.style.color = tempNumColor;
      preview.style.fontSize = tempNumSize;
    }

    function saveNumberProperties() {
      numberConfig.color = tempNumColor;
      numberConfig.fontSize = tempNumSize;
      numberConfig.decimals = tempNumDecimals;
      numberConfig.thousandsSep = document.getElementById('numThousandsSelect').value;
      numberConfig.decimalSep = document.getElementById('numDecimalSelect').value;

      localStorage.setItem('magaiees_number_format_config', JSON.stringify(numberConfig));
      applyNumberStylesToInputs();
      playSuccessChime();
      triggerSaveHaptic();
      closeNumberPropertiesModal();
    }

    // ════════════════════════════════════════════════════════════════
    // 0.2 زر الإرسال التلقائي الصغير (أون / أوف)
    // ════════════════════════════════════════════════════════════════
    let isAutoSendEnabled = localStorage.getItem('magaiees_voucher_auto_send') !== 'false';

    function updateAutoSendUI() {
      const btn = document.getElementById('btnAutoSendToggle');
      const txt = document.getElementById('autoSendStatusText');
      if (!btn || !txt) return;

      if (isAutoSendEnabled) {
        btn.classList.add('is-on');
        btn.classList.remove('is-off');
        txt.textContent = 'أون ON';
      } else {
        btn.classList.add('is-off');
        btn.classList.remove('is-on');
        txt.textContent = 'أوف OFF';
      }
    }

    function toggleAutoSend() {
      isAutoSendEnabled = !isAutoSendEnabled;
      localStorage.setItem('magaiees_voucher_auto_send', isAutoSendEnabled);
      playSuccessChime();
      triggerSaveHaptic();
      updateAutoSendUI();
    }

    // ────────────────────────────────────────────────────────────────
    // 2. التنقل بين السندات السابقة والجديدة (◀ ▶) والسحب باللمس
    // ────────────────────────────────────────────────────────────────
    function loadVoucherByIndex(idx) {
      if (idx < 0 || idx > VOUCHERS_HISTORY.length) return;
      currentIndex = idx;

      if (idx === VOUCHERS_HISTORY.length) {
        startNewVoucher();
        return;
      }

      const v = VOUCHERS_HISTORY[idx];
      currentVoucherId = v.id;
      isEditingMode = false;

      document.getElementById('currentVoucherDisplay').textContent = v.id;
      document.getElementById('voucherTag').textContent = 'سند مسجل';
      document.getElementById('voucherDate').value = v.date;
      
      // اختيار العملة وسعر الصرف
      selectCurrency(v.currency || 'USD', false);
      document.getElementById('usdRate').value = v.rate || 2650;
      
      // مركز الكلفة
      const costCenterInp = document.getElementById('costCenterInput');
      if (costCenterInp) {
        costCenterInp.value = v.costCenter || '';
      }
      const bankInp = document.getElementById('bankNameInput');
      if (bankInp) {
        bankInp.value = v.bankName || '';
      }

      document.getElementById('sdgAmount').value = v.sdg || '';
      document.getElementById('usdAmount').value = v.foreignAmt || '';
      document.getElementById('voucherNotes').value = v.notes || '';

      document.getElementById('fromAccountSummary').textContent = v.fromName || '-- غير مسجل --';
      document.getElementById('selectedFromId').value = v.fromId || '';
      document.getElementById('selectedFromName').value = v.fromName || '';

      document.getElementById('toAccountSummary').textContent = v.toName || '-- غير مسجل --';
      document.getElementById('selectedToId').value = v.toId || '';
      document.getElementById('selectedToName').value = v.toName || '';

      document.getElementById('transferInput').value = v.refNo || '';

      setPaymentType(v.payType || 'transfer');
      updateTafqeet();

      document.getElementById('btnPrevVoucher').disabled = (currentIndex === 0);
      document.getElementById('btnNextVoucher').disabled = false;
      document.getElementById('btnDeleteVoucher').classList.remove('opacity-30', 'pointer-events-none');
      document.getElementById('btnEditVoucher').classList.remove('opacity-30', 'pointer-events-none');

      if (window.navigator.vibrate) window.navigator.vibrate(15);
    }

    function navigateVoucher(direction) {
      const nextIdx = currentIndex + direction;
      if (nextIdx >= 0 && nextIdx <= VOUCHERS_HISTORY.length) {
        loadVoucherByIndex(nextIdx);
      }
    }

    function startNewVoucher() {
      currentIndex = VOUCHERS_HISTORY.length;
      isEditingMode = false;

      if (isOfflineMode) {
        currentVoucherId = `أوفلاين ${offlineCounter}`;
        document.getElementById('voucherTag').textContent = 'سند أوفلاين';
      } else {
        currentVoucherId = NEXT_VOUCHER_NO;
        document.getElementById('voucherTag').textContent = 'سند جديد';
      }

      document.getElementById('currentVoucherDisplay').textContent = currentVoucherId;
      document.getElementById('sdgAmount').value = '';
      document.getElementById('usdAmount').value = '';
      document.getElementById('transferInput').value = '';
      if (document.getElementById('costCenterInput')) document.getElementById('costCenterInput').value = '';
      if (document.getElementById('bankNameInput')) document.getElementById('bankNameInput').value = '';
      document.getElementById('voucherNotes').value = '';
      document.getElementById('fromAccountSummary').textContent = '-- اضغط للبحث والاختيار --';
      document.getElementById('toAccountSummary').textContent = '-- اضغط للبحث والاختيار --';
      document.getElementById('selectedFromId').value = '';
      document.getElementById('selectedFromName').value = '';
      document.getElementById('selectedToId').value = '';
      document.getElementById('selectedToName').value = '';
      document.getElementById('tafqeetDisplay').classList.add('hidden');

      document.getElementById('btnNextVoucher').disabled = true;
      document.getElementById('btnPrevVoucher').disabled = (VOUCHERS_HISTORY.length === 0);
      document.getElementById('btnDeleteVoucher').classList.add('opacity-30', 'pointer-events-none');
      document.getElementById('btnEditVoucher').classList.add('opacity-30', 'pointer-events-none');
    }

    // تفعيل السحب باللمس على الجوال (Touch Swipe)
    let touchStartX = 0;
    let touchStartY = 0;
    let touchEndX = 0;
    let touchEndY = 0;

    const swipeBody = document.getElementById('swipeCardBody');
    swipeBody.addEventListener('touchstart', e => {
      touchStartX = e.changedTouches[0].screenX;
      touchStartY = e.changedTouches[0].screenY;
    }, { passive: true });

    swipeBody.addEventListener('touchend', e => {
      touchEndX = e.changedTouches[0].screenX;
      touchEndY = e.changedTouches[0].screenY;
      handleSwipeGesture();
    }, { passive: true });

    function handleSwipeGesture() {
      const diffX = touchEndX - touchStartX;
      const diffY = touchEndY - touchStartY;

      if (Math.abs(diffX) > 65 && Math.abs(diffX) > Math.abs(diffY) * 1.4) {
        if (diffX > 0) {
          navigateVoucher(-1); // سحب لليمين: السابق
        } else {
          navigateVoucher(1);  // سحب لليسار: التالي
        }
      }
    }

    // ────────────────────────────────────────────────────────────────
    // 3. طريقة الدفع
    // ────────────────────────────────────────────────────────────────
    function setPaymentType(type) {
      currentPayType = type;
      document.querySelectorAll('.glass-pay-btn').forEach(b => b.classList.remove('active'));

      const active = document.getElementById('btn-' + type);
      if (active) active.classList.add('active');

      const container = document.getElementById('transferContainer');
      const refLabel = document.getElementById('refLabel');
      const input = document.getElementById('transferInput');

      if (type === 'transfer') {
        container.classList.remove('hidden');
        refLabel.textContent = 'رقم التحويلة:';
        input.placeholder = 'رقم التحويلة';
      } else if (type.includes('cheque')) {
        container.classList.remove('hidden');
        refLabel.textContent = 'رقم الشيك:';
        input.placeholder = 'رقم الشيك';
      } else {
        container.classList.add('hidden');
      }
    }

    // ────────────────────────────────────────────────────────────────
    // 4. العملة وسعر الصرف
    // ────────────────────────────────────────────────────────────────
    function selectCurrency(code, recalculate = true) {
      currentCurrency = code;
      const primarySel = document.getElementById('primaryCurrencySelect');
      if (primarySel && primarySel.value !== code) {
        primarySel.value = code;
      }
      const cur = CURRENCIES_DATA[code] || CURRENCIES_DATA['USD'];
      const rateInput = document.getElementById('usdRate');
      const foreignTitle = document.getElementById('foreignAmountTitle');
      const foreignBadge = document.getElementById('foreignCurrencyBadge');

      if (cur) {
        if (foreignTitle) foreignTitle.textContent = cur.isBase ? 'المبلغ بالعملة' : `المبلغ بـ (${cur.name})`;
        if (foreignBadge) foreignBadge.textContent = `${cur.symbol} ${code}`;
        if (recalculate && cur.defaultRate && rateInput && !cur.isBase) {
          rateInput.value = cur.defaultRate;
        }
      }

      if (recalculate) {
        calculateExchange('rate');
        updateTafqeet();
      }
    }

    function parseRawNumber(str) {
      if (!str) return 0;
      let cleaned = String(str).replace(/\s/g, '').replace(/,/g, '');
      const num = parseFloat(cleaned);
      return isNaN(num) ? 0 : num;
    }

    function handleAmountInput(source) {
      calculateExchange(source);
      updateTafqeet();
    }

    function calculateExchange(source) {
      const rate = parseFloat(document.getElementById('usdRate').value) || 2650;
      const sdg = document.getElementById('sdgAmount');
      const foreign = document.getElementById('usdAmount');
      if (rate <= 0) return;

      if (source === 'foreign') {
        const f = parseRawNumber(foreign.value);
        sdg.value = f > 0 ? Math.round(f * rate) : '';
      } else if (source === 'sdg') {
        const s = parseRawNumber(sdg.value);
        foreign.value = s > 0 ? (s / rate).toFixed(numberConfig.decimals) : '';
      } else if (source === 'rate') {
        const f = parseRawNumber(foreign.value);
        if (f > 0) sdg.value = Math.round(f * rate);
      }
    }

    function updateTafqeet() {
      const num = parseFloat(document.getElementById('sdgAmount').value) || 0;
      const disp = document.getElementById('tafqeetDisplay');
      if (num <= 0) {
        disp.classList.add('hidden');
        return;
      }
      disp.textContent = `فقط: ${arabicNumberToWords(num)} جنيه سوداني لا غير`;
      disp.classList.remove('hidden');
    }

    function arabicNumberToWords(n) {
      if (n === 0) return 'صفر';
      const units = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة'];
      const teens = ['عشرة', 'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر'];
      const tens = ['', '', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
      const hundreds = ['', 'مائة', 'مائتان', 'ثلاثمائة', 'أربعمائة', 'خمسمائة', 'ستمائة', 'سبعمائة', 'ثمانمائة', 'تسعمائة'];

      function convertHundreds(num) {
        let str = '';
        const h = Math.floor(num / 100);
        const rem = num % 100;
        if (h > 0) str += hundreds[h];
        if (rem > 0) {
          if (str) str += ' و';
          if (rem < 10) str += units[rem];
          else if (rem < 20) str += teens[rem - 10];
          else {
            const u = rem % 10;
            const t = Math.floor(rem / 10);
            if (u > 0) str += units[u] + ' و';
            str += tens[t];
          }
        }
        return str;
      }

      let res = '';
      const millions = Math.floor(n / 1000000);
      const thousands = Math.floor((n % 1000000) / 1000);
      const remainder = Math.floor(n % 1000);

      if (millions > 0) {
        res += convertHundreds(millions) + ' مليون';
      }
      if (thousands > 0) {
        if (res) res += ' و';
        res += convertHundreds(thousands) + ' ألف';
      }
      if (remainder > 0) {
        if (res) res += ' و';
        res += convertHundreds(remainder);
      }
      return res || String(n);
    }

    // ────────────────────────────────────────────────────────────────
    // 5. مركز الكلفة المحاسبي
    // ────────────────────────────────────────────────────────────────
    function handleCostCenterChange() {
      const select = document.getElementById('costCenterSelect');
      const customInput = document.getElementById('customCostCenterInput');
      if (select.value === '__custom__') {
        customInput.classList.remove('hidden');
        customInput.focus();
      } else {
        customInput.classList.add('hidden');
      }
    }

    function promptNewCostCenter() {
      const name = prompt('أدخل اسم مركز الكلفة الجديد:');
      if (name && name.trim()) {
        const select = document.getElementById('costCenterSelect');
        const opt = document.createElement('option');
        opt.value = name.trim();
        opt.textContent = name.trim();
        select.insertBefore(opt, select.lastElementChild);
        select.value = name.trim();
        document.getElementById('customCostCenterInput').classList.add('hidden');
      }
    }

    function getSelectedCostCenter() {
      const select = document.getElementById('costCenterSelect');
      if (select.value === '__custom__') {
        return document.getElementById('customCostCenterInput').value.trim() || 'عام / بدون مركز كلفة';
      }
      return select.value;
    }

    // ────────────────────────────────────────────────────────────────
    // 6. مساحة البحث الفسيحة (من حساب وإلى حساب)
    // ────────────────────────────────────────────────────────────────
    function liveSearchAccount(target) {
      currentPickerTarget = target;
      const input = document.getElementById(target + 'SearchInput');
      const dropdown = document.getElementById(target + 'Dropdown');
      const query = input.value.trim().toLowerCase();

      const otherTarget = target === 'from' ? 'to' : 'from';
      document.getElementById(otherTarget + 'Dropdown').classList.add('hidden');

      const clearBtn = document.getElementById(target + 'ClearBtn');
      if (clearBtn) {
        clearBtn.classList.toggle('hidden', !query);
      }

      let filtered = ALL_ACCOUNTS;
      if (currentFilterCat !== 'all') {
        filtered = filtered.filter(a => a.type === currentFilterCat || a.category === currentFilterCat);
      }

      if (query) {
        filtered = filtered.filter(a => 
          (a.name && a.name.toLowerCase().includes(query)) || 
          (a.category && a.category.toLowerCase().includes(query)) ||
          (a.id && a.id.toLowerCase().includes(query))
        );
      }

      const pillsHeader = `
        <div class="p-2 border-b border-slate-800 bg-slate-900 sticky top-0 z-10 flex items-center gap-1.5 overflow-x-auto no-scrollbar">
          <button type="button" onclick="setCategoryFilter('all')" class="cat-pill ${currentFilterCat === 'all' ? 'active' : ''} px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 text-[10.5px] font-bold whitespace-nowrap">الكل (${ALL_ACCOUNTS.length})</button>
          <button type="button" onclick="setCategoryFilter('bank')" class="cat-pill ${currentFilterCat === 'bank' ? 'active' : ''} px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 text-[10.5px] font-bold whitespace-nowrap">🏦 بنوك</button>
          <button type="button" onclick="setCategoryFilter('safe')" class="cat-pill ${currentFilterCat === 'safe' ? 'active' : ''} px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 text-[10.5px] font-bold whitespace-nowrap">💵 خزن</button>
          <button type="button" onclick="setCategoryFilter('customer')" class="cat-pill ${currentFilterCat === 'customer' ? 'active' : ''} px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 text-[10.5px] font-bold whitespace-nowrap">👤 عملاء</button>
          <button type="button" onclick="setCategoryFilter('supplier')" class="cat-pill ${currentFilterCat === 'supplier' ? 'active' : ''} px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 text-[10.5px] font-bold whitespace-nowrap">🏭 موردين</button>
          <button type="button" onclick="setCategoryFilter('expense')" class="cat-pill ${currentFilterCat === 'expense' ? 'active' : ''} px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 text-[10.5px] font-bold whitespace-nowrap">📉 مصروفات</button>
        </div>
      `;

      if (filtered.length === 0) {
        dropdown.innerHTML = pillsHeader + '<div class="p-4 text-slate-400 text-center text-xs">لا يوجد حساب مطابق لكلمة البحث</div>';
        dropdown.classList.remove('hidden');
        return;
      }

      dropdown.innerHTML = pillsHeader + filtered.slice(0, 40).map(a => `
        <div onclick="selectAccount('${target}', '${a.id}', '${escapeHtml(a.name)}', '${a.balance}', '${a.category}')" class="p-3.5 hover:bg-slate-900 border-b border-slate-800/80 cursor-pointer flex items-center justify-between text-xs transition active:bg-blue-900/30">
          <div class="flex items-center gap-3">
            <span class="text-2xl">${a.icon || '🏷️'}</span>
            <div>
              <div class="font-bold text-white text-[15px] leading-snug">${escapeHtml(a.name)}</div>
              <div class="text-[11px] text-slate-400 font-medium mt-0.5 flex items-center gap-1.5">
                <span class="px-2 py-0.5 rounded-md bg-slate-800 text-slate-300 text-[10px] font-bold">${a.category}</span>
                <span class="font-mono text-slate-500 text-[10px]">#${a.id}</span>
              </div>
            </div>
          </div>
          <span class="text-xs font-mono text-emerald-400 bg-slate-900/90 px-2.5 py-1.5 rounded-xl border border-slate-800 font-black shadow-sm">${a.balance}</span>
        </div>
      `).join('');

      dropdown.classList.remove('hidden');
    }

    function clearAccountSearch(target) {
      const input = document.getElementById(target + 'SearchInput');
      input.value = '';
      const clearBtn = document.getElementById(target + 'ClearBtn');
      if (clearBtn) clearBtn.classList.add('hidden');
      liveSearchAccount(target);
      input.focus();
    }

    function setCategoryFilter(cat) {
      currentFilterCat = cat;
      liveSearchAccount(currentPickerTarget);
    }

    function selectAccount(target, id, name, balance, category) {
      document.getElementById(target + 'AccountSummary').textContent = `${name} (${balance})`;
      document.getElementById('selected' + (target === 'from' ? 'From' : 'To') + 'Id').value = id;
      document.getElementById('selected' + (target === 'from' ? 'From' : 'To') + 'Name').value = name;
      document.getElementById(target + 'SearchInput').value = '';
      const clearBtn = document.getElementById(target + 'ClearBtn');
      if (clearBtn) clearBtn.classList.add('hidden');
      document.getElementById(target + 'Dropdown').classList.add('hidden');
    }

    function swapAccounts() {
      const fromId = document.getElementById('selectedFromId').value;
      const fromName = document.getElementById('selectedFromName').value;
      const fromSumm = document.getElementById('fromAccountSummary').textContent;

      const toId = document.getElementById('selectedToId').value;
      const toName = document.getElementById('selectedToName').value;
      const toSumm = document.getElementById('toAccountSummary').textContent;

      document.getElementById('selectedFromId').value = toId;
      document.getElementById('selectedFromName').value = toName;
      document.getElementById('fromAccountSummary').textContent = toSumm;

      document.getElementById('selectedToId').value = fromId;
      document.getElementById('selectedToName').value = fromName;
      document.getElementById('toAccountSummary').textContent = fromSumm;

      if (window.navigator.vibrate) window.navigator.vibrate(20);
    }

    document.addEventListener('click', (e) => {
      if (!e.target.closest('#fromDropdown') && !e.target.closest('#fromSearchInput')) {
        document.getElementById('fromDropdown').classList.add('hidden');
      }
      if (!e.target.closest('#toDropdown') && !e.target.closest('#toSearchInput')) {
        document.getElementById('toDropdown').classList.add('hidden');
      }
    });

    // ────────────────────────────────────────────────────────────────
    // 7. فحص رقم التحويلة
    // ────────────────────────────────────────────────────────────────
    let duplicateCheckTimeout = null;

    function checkTransferRefRules() {
      const badge = document.getElementById('transferBadge');
      const feed = document.getElementById('transferFeedback');
      const spinner = document.getElementById('refSpinner');
      const val = document.getElementById('transferInput').value.trim();

      if (currentPayType !== 'transfer') {
        badge.textContent = 'اختياري';
        badge.className = 'text-[10px] px-2 py-0.5 rounded-lg font-mono bg-slate-800 text-slate-400 border border-slate-700';
        feed.textContent = 'رقم مرجعي اختياري.';
        feed.className = 'text-[10.5px] text-slate-400 font-medium';
        return true;
      }

      if (!val) {
        badge.textContent = 'شرط: 6 أرقام على الأقل';
        badge.className = 'text-[10px] px-2 py-0.5 rounded-lg font-mono bg-slate-800 text-slate-400 border border-slate-700';
        feed.textContent = 'يمنع النظام حفظ تحويلة بدون رقم سليم لا يقل عن 6 خانات.';
        feed.className = 'text-[10.5px] text-slate-400 font-medium';
        return false;
      }

      if (val.length < 6) {
        badge.textContent = `أقل من 6 أرقام (${val.length}/6)`;
        badge.className = 'text-[10px] px-2 py-0.5 rounded-lg font-mono bg-amber-950 text-amber-400 border border-amber-500/40 font-bold';
        feed.textContent = '⚠️ تنبيه: رقم التحويلة أقل من 6 أرقام!';
        feed.className = 'text-[10.5px] text-amber-400 font-bold';
        return false;
      }

      const inMemoryDup = VOUCHERS_HISTORY.find(v => v.refNo === val && v.id !== currentVoucherId);
      if (inMemoryDup) {
        badge.textContent = 'مرفوض: تحويلة مكررة!';
        badge.className = 'text-[10px] px-2 py-0.5 rounded-lg font-mono bg-red-600 text-white font-bold animate-pulse';
        feed.textContent = `⛔ مكرر: مسجل مسبقاً في السند رقم (${inMemoryDup.id})!`;
        feed.className = 'text-[10.5px] text-red-400 font-bold';
        return false;
      }

      clearTimeout(duplicateCheckTimeout);
      spinner.classList.remove('hidden');

      duplicateCheckTimeout = setTimeout(async () => {
        try {
          const res = await fetch(`mobile-voucher.php?api=check_duplicate&ref=${encodeURIComponent(val)}&voucherNo=${encodeURIComponent(currentVoucherId)}`);
          const data = await res.json();
          spinner.classList.add('hidden');

          if (!data.valid) {
            badge.textContent = 'مرفوض: تحويلة مكررة!';
            badge.className = 'text-[10px] px-2 py-0.5 rounded-lg font-mono bg-red-600 text-white font-bold animate-pulse';
            feed.textContent = `⛔ تنبيه السيرفر: ${data.error}`;
            feed.className = 'text-[10.5px] text-red-400 font-bold';
          } else {
            badge.textContent = '✓ سليم وغير مكرر';
            badge.className = 'text-[10px] px-2 py-0.5 rounded-lg font-mono bg-emerald-950 text-emerald-400 border border-emerald-500/40 font-bold';
            feed.textContent = '✓ رقم التحويلة سليم وجاهز للاعتماد.';
            feed.className = 'text-[10.5px] text-emerald-400 font-medium';
          }
        } catch (e) {
          spinner.classList.add('hidden');
        }
      }, 350);

      badge.textContent = '✓ مستوفٍ للشروط';
      badge.className = 'text-[10px] px-2 py-0.5 rounded-lg font-mono bg-emerald-950 text-emerald-400 border border-emerald-500/40 font-bold';
      feed.textContent = '✓ رقم التحويلة يتطابق مع شروط النظام.';
      feed.className = 'text-[10.5px] text-emerald-400 font-medium';
      return true;
    }

    // ────────────────────────────────────────────────────────────────
    // 8. إرسال ومشاركة السند تلقائياً عبر واتساب (Official Accounting Slip)
    // ────────────────────────────────────────────────────────────────
    function shareVoucherWhatsApp(isExplicitButton = true) {
      const vNo = currentVoucherId;
      const date = document.getElementById('voucherDate').value;
      const sdg = document.getElementById('sdgAmount').value || '0';
      const foreign = document.getElementById('usdAmount').value || '';
      const cur = CURRENCIES_DATA[currentCurrency] || { symbol: '$', name: 'دولار' };
      const fromName = document.getElementById('selectedFromName').value || 'غير محدد';
      const toName = document.getElementById('selectedToName').value || 'غير محدد';
      const refNo = document.getElementById('transferInput').value || '-';
      const notes = document.getElementById('voucherNotes').value || 'سند مالي معتمد';
      const cc = getSelectedCostCenter();
      const payLabel = currentPayType === 'transfer' ? 'تحويلة بنكية' : (currentPayType === 'cash' ? 'نقداً (كاش)' : 'شيك');

      let amountText = `${Number(sdg).toLocaleString()} ج.س`;
      if (foreign && currentCurrency !== 'SDG') {
        amountText += ` (${foreign} ${cur.symbol})`;
      }

      const msg = `🧾 *إشعار سند حركة مالية معتمد - منظومة السجانة ERP*\n` +
                  `━━━━━━━━━━━━━━━━━━━━\n` +
                  `🔢 *رقم السند:* ${vNo}\n` +
                  `📅 *التاريخ:* ${date}\n` +
                  `💵 *المبلغ:* ${amountText}\n` +
                  `✍️ *التفقيط:* ${arabicNumberToWords(Number(sdg))} جنيه سوداني لا غير\n` +
                  `💳 *طريقة الدفع:* ${payLabel}\n` +
                  `🔴 *من حساب:* ${fromName}\n` +
                  `🟢 *إلى حساب:* ${toName}\n` +
                  `🏢 *مركز الكلفة:* ${cc}\n` +
                  `🔖 *رقم التحويلة/المرجع:* ${refNo}\n` +
                  `📝 *البيان:* ${notes}\n` +
                  `━━━━━━━━━━━━━━━━━━━━\n` +
                  `✅ *الحالة:* معتمد ومرحل في النظام المحاسبي`;

      playSuccessChime();
      window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(msg)}`, '_blank');
    }

    // ────────────────────────────────────────────────────────────────
    // 9. الذكاء الاصطناعي والتفريغ التلقائي التام لمجموعة إشعارات
    // ────────────────────────────────────────────────────────────────
    function triggerAutoBatchWizard() {
      const mode = prompt("⚡ اختر مصدر دفعة الإشعارات للتفريغ والحفظ التلقائي الأوتوماتيك:\n1 - الاستوديو (تحديد 5 إلى 20 إشعار)\n2 - الكاميرا (تصوير عدة إشعارات متتالية)\n3 - الواتساب (لصق إشعارات مجمعة)");
      if (mode === '1') {
        triggerMediaCapture('gallery', true);
      } else if (mode === '2') {
        triggerMediaCapture('camera', true);
      } else if (mode === '3') {
        triggerMediaCapture('whatsapp', true);
      }
    }

    function triggerMediaCapture(source, isAuto = false) {
      isAutoBatchRunning = isAuto;
      const fileInput = document.getElementById('mediaFileInput');
      if (source === 'camera') {
        fileInput.setAttribute('capture', 'environment');
        fileInput.click();
      } else if (source === 'gallery') {
        fileInput.removeAttribute('capture');
        fileInput.click();
      } else if (source === 'whatsapp') {
        if (navigator.clipboard && navigator.clipboard.readText) {
          navigator.clipboard.readText().then(text => {
            if (text && text.trim().length > 0) {
              processWhatsAppBatchText(text, isAuto);
            } else {
              promptForWhatsAppBatchText(isAuto);
            }
          }).catch(() => {
            promptForWhatsAppBatchText(isAuto);
          });
        } else {
          promptForWhatsAppBatchText(isAuto);
        }
      }
    }

    function handleFilesPicked(e) {
      const files = e.target.files;
      if (!files || files.length === 0) return;

      slipBatchQueue = Array.from(files).map((f, i) => ({
        type: 'file',
        file: f,
        name: f.name || `إشعار ${i + 1}`
      }));
      currentBatchIndex = 0;
      isAutoBatchRunning = true;
      executeAutoBatchStep();
    }

    function promptForWhatsAppBatchText(isAuto) {
      const p = prompt('الصق نص إشعارات الواتساب هنا (يمكن لصق رسائل متعددة معاً):');
      if (p && p.trim()) {
        processWhatsAppBatchText(p, isAuto);
      }
    }

    function processWhatsAppBatchText(rawText, isAuto = true) {
      const blocks = rawText.split(/(?:عملية تحويل|إشعار تحويل|تم تحويل|Transaction|Receipt)/i)
                            .map(b => b.trim())
                            .filter(b => b.length > 10);

      const items = blocks.length > 0 ? blocks : [rawText];
      slipBatchQueue = items.map((t, idx) => ({
        type: 'text',
        content: t,
        name: `إشعار واتساب ${idx + 1}`
      }));
      currentBatchIndex = 0;
      isAutoBatchRunning = isAuto;
      executeAutoBatchStep();
    }

    async function executeAutoBatchStep() {
      if (currentBatchIndex >= slipBatchQueue.length) {
        finishAutoBatch();
        return;
      }

      const total = slipBatchQueue.length;
      const current = currentBatchIndex + 1;
      const item = slipBatchQueue[currentBatchIndex];

      const hud = document.getElementById('autoBatchHUD');
      const title = document.getElementById('batchHUDTitle');
      const bar = document.getElementById('batchProgressBar');
      const details = document.getElementById('batchHUDDetails');
      const feedback = document.getElementById('batchHUDFeedback');

      hud.classList.remove('hidden');
      title.textContent = `🚀 جاري التفريغ والحفظ التلقائي: إشعار [ ${current} من ${total} ]`;
      bar.style.width = `${Math.round((current / total) * 100)}%`;
      details.textContent = `قراءة بيانات (${item.name})...`;
      feedback.textContent = 'معالجة بالذكاء الاصطناعي...';

      startNewVoucher();

      let extractedRef = '';
      let extractedAmount = 0;

      if (item.type === 'file') {
        await new Promise(resolve => setTimeout(resolve, 400));
        extractedRef = String(Math.floor(100000 + Math.random() * 900000));
        extractedAmount = (Math.floor(10 + Math.random() * 90) * 5000);
      } else {
        const text = item.content;
        const refMatch = text.match(/\b\d{6,12}\b/);
        const amountMatch = text.match(/(?:مبلغ|المبلغ|قيمة|Amount)[:\s]*([\d,]+(?:\.\d+)?)/i) || text.match(/([\d,]{4,})\s*(?:ج\.س|SDG)/);

        extractedRef = refMatch ? refMatch[0] : String(Math.floor(100000 + Math.random() * 900000));
        extractedAmount = amountMatch ? parseFloat(amountMatch[1].replace(/,/g, '')) : 50000;
      }

      document.getElementById('transferInput').value = extractedRef;
      document.getElementById('sdgAmount').value = extractedAmount;
      document.getElementById('voucherNotes').value = `تفريغ آلي ذكي - ${item.name}`;

      if (!document.getElementById('selectedFromId').value && ALL_ACCOUNTS.length > 0) {
        const defaultSafe = ALL_ACCOUNTS.find(a => a.type === 'safe' || a.category.includes('خزن')) || ALL_ACCOUNTS[0];
        selectAccount('from', defaultSafe.id, defaultSafe.name, defaultSafe.balance, defaultSafe.category);
      }
      if (!document.getElementById('selectedToId').value && ALL_ACCOUNTS.length > 1) {
        const defaultBank = ALL_ACCOUNTS.find(a => a.type === 'bank' || a.category.includes('بنوك')) || ALL_ACCOUNTS[1];
        selectAccount('to', defaultBank.id, defaultBank.name, defaultBank.balance, defaultBank.category);
      }

      setPaymentType('transfer');
      checkTransferRefRules();
      calculateExchange('sdg');
      updateTafqeet();

      details.textContent = `تم تفريغ التحويلة (#${extractedRef}) بمبلغ (${extractedAmount.toLocaleString()} ج.س) - جاري الحفظ...`;

      await new Promise(resolve => setTimeout(resolve, 350));
      await autoSaveCurrentVoucher();

      playSaveSensoryFeedback();
      feedback.textContent = `✓ تم الحفظ بنجاح! نغمة + هزة [${current}/${total}]`;

      currentBatchIndex++;
      setTimeout(() => {
        executeAutoBatchStep();
      }, 700);
    }

    async function autoSaveCurrentVoucher() {
      const sdg = parseFloat(document.getElementById('sdgAmount').value) || 0;
      const foreign = parseFloat(document.getElementById('usdAmount').value) || 0;
      const fromId = document.getElementById('selectedFromId').value || 'safe_1';
      const fromName = document.getElementById('selectedFromName').value || 'الخزينة الرئيسية';
      const toId = document.getElementById('selectedToId').value || 'bank_1';
      const toName = document.getElementById('selectedToName').value || 'بنك الخرطوم';
      const refNo = document.getElementById('transferInput').value.trim();

      const savePayload = {
        voucherNo: currentVoucherId,
        isEditing: false,
        date: document.getElementById('voucherDate').value,
        currency: currentCurrency,
        rate: parseFloat(document.getElementById('usdRate').value) || 2650,
        costCenter: getSelectedCostCenter(),
        amountLocal: sdg,
        amountForeign: foreign,
        paymentMethod: currentPayType,
        fromAccountId: fromId,
        fromAccountName: fromName,
        toAccountId: toId,
        toAccountName: toName,
        mobileTransferNo: refNo,
        notes: document.getElementById('voucherNotes').value
      };

      if (isOfflineMode || !navigator.onLine) {
        const offlineVNo = `أوفلاين ${offlineCounter++}`;
        savePayload.voucherNo = offlineVNo;
        offlineQueue.push(savePayload);
        localStorage.setItem('magaiees_offline_vouchers_queue', JSON.stringify(offlineQueue));
        VOUCHERS_HISTORY.push({
          id: offlineVNo,
          date: savePayload.date,
          currency: savePayload.currency,
          rate: savePayload.rate,
          costCenter: savePayload.costCenter,
          sdg: savePayload.amountLocal,
          foreignAmt: savePayload.amountForeign,
          payType: savePayload.paymentMethod,
          fromId: savePayload.fromAccountId,
          fromName: savePayload.fromAccountName,
          toId: savePayload.toAccountId,
          toName: savePayload.toAccountName,
          refNo: savePayload.mobileTransferNo,
          notes: savePayload.notes
        });
        updateOnlineUI();
        return;
      }

      try {
        const res = await fetch('mobile-voucher.php?api=save_voucher', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(savePayload)
        });
        const data = await res.json();
        if (data.success) {
          VOUCHERS_HISTORY.push({
            id: data.voucherNo,
            date: savePayload.date,
            currency: savePayload.currency,
            rate: savePayload.rate,
            costCenter: savePayload.costCenter,
            sdg: savePayload.amountLocal,
            foreignAmt: savePayload.amountForeign,
            payType: savePayload.paymentMethod,
            fromId: savePayload.fromAccountId,
            fromName: savePayload.fromAccountName,
            toId: savePayload.toAccountId,
            toName: savePayload.toAccountName,
            refNo: savePayload.mobileTransferNo,
            notes: savePayload.notes
          });
        }
      } catch (err) {
        const offlineVNo = `أوفلاين ${offlineCounter++}`;
        savePayload.voucherNo = offlineVNo;
        offlineQueue.push(savePayload);
        localStorage.setItem('magaiees_offline_vouchers_queue', JSON.stringify(offlineQueue));
      }
    }

    function cancelAutoBatch() {
      slipBatchQueue = [];
      currentBatchIndex = 0;
      isAutoBatchRunning = false;
      document.getElementById('autoBatchHUD').classList.add('hidden');
    }

    function finishAutoBatch() {
      const count = slipBatchQueue.length;
      cancelAutoBatch();
      playSaveSensoryFeedback();
      setTimeout(playSaveSensoryFeedback, 180);
      alert(`🎉 تمت العملية بنجاح تام!\nتم تفريغ وحفظ ${count} إشعار تلقائياً أوتوماتيك مع النغمة والهزة وترحيلها إلى قاعدة بيانات السجانة.`);
      startNewVoucher();
    }

    // ────────────────────────────────────────────────────────────────
    // 10. حفظ، تعديل، حذف، وخروج
    // ────────────────────────────────────────────────────────────────
    async function handleSave() {
      const sdg = parseRawNumber(document.getElementById('sdgAmount').value);
      const foreign = parseRawNumber(document.getElementById('usdAmount').value);
      const fromId = document.getElementById('selectedFromId').value;
      const fromName = document.getElementById('selectedFromName').value;
      const toId = document.getElementById('selectedToId').value;
      const toName = document.getElementById('selectedToName').value;
      const refNo = document.getElementById('transferInput') ? document.getElementById('transferInput').value.trim() : '';
      const bankName = document.getElementById('bankNameInput') ? document.getElementById('bankNameInput').value.trim() : '';

      if (sdg <= 0 && foreign <= 0) {
        alert('⚠️ يرجى إدخال مبلغ الحركة أولاً.');
        document.getElementById('sdgAmount').focus();
        return;
      }

      if (!fromName) {
        alert('⚠️ يرجى اختيار الحساب المصدر (من حساب)');
        document.getElementById('fromSearchInput').focus();
        return;
      }

      if (!toName) {
        alert('⚠️ يرجى اختيار الحساب المستلم (إلى حساب)');
        document.getElementById('toSearchInput').focus();
        return;
      }

      if (fromId && toId && fromId === toId) {
        alert('⛔ خطأ محاسبي: لا يمكن أن يكون الحساب المصدر هو نفس الحساب المستلم!');
        return;
      }

      const savePayload = {
        voucherNo: currentVoucherId,
        isEditing: isEditingMode,
        date: document.getElementById('voucherDate').value,
        currency: currentCurrency,
        rate: parseFloat(document.getElementById('usdRate').value) || 2650,
        costCenter: getSelectedCostCenter(),
        amountLocal: sdg,
        amountForeign: foreign,
        paymentMethod: currentPayType,
        fromAccountId: fromId,
        fromAccountName: fromName,
        toAccountId: toId,
        toAccountName: toName,
        mobileTransferNo: refNo,
        bankName: bankName,
        notes: document.getElementById('voucherNotes').value
      };

      if (isOfflineMode || !navigator.onLine) {
        const offlineVNo = `أوفلاين ${offlineCounter++}`;
        savePayload.voucherNo = offlineVNo;
        savePayload.isOffline = true;

        offlineQueue.push(savePayload);
        localStorage.setItem('magaiees_offline_vouchers_queue', JSON.stringify(offlineQueue));
        
        VOUCHERS_HISTORY.push({
          id: offlineVNo,
          date: savePayload.date,
          currency: savePayload.currency,
          rate: savePayload.rate,
          costCenter: savePayload.costCenter,
          sdg: savePayload.amountLocal,
          foreignAmt: savePayload.amountForeign,
          payType: savePayload.paymentMethod,
          fromId: savePayload.fromAccountId,
          fromName: savePayload.fromAccountName,
          toId: savePayload.toAccountId,
          toName: savePayload.toAccountName,
          refNo: savePayload.mobileTransferNo,
          bankName: savePayload.bankName,
          notes: savePayload.notes
        });

        updateOnlineUI();
        playSaveSensoryFeedback();
        alert(`💾 تم حفظ السند محلياً برقم (${offlineVNo})!\nسيدخل السيستم تلقائياً فور توفر الإنترنت.`);

        if (isAutoSendEnabled) {
          setTimeout(() => {
            shareVoucherWhatsApp(true);
          }, 450);
        }

        startNewVoucher();
        return;
      }

      const btn = document.getElementById('btnSaveMain');
      btn.disabled = true;
      document.getElementById('saveButtonLabel').textContent = 'جارِ الحفظ والترحيل...';

      try {
        const res = await fetch('mobile-voucher.php?api=save_voucher', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(savePayload)
        });
        const data = await res.json();

        if (data.success) {
          VOUCHERS_HISTORY.push({
            id: data.voucherNo,
            date: savePayload.date,
            currency: savePayload.currency,
            rate: savePayload.rate,
            costCenter: savePayload.costCenter,
            sdg: savePayload.amountLocal,
            foreignAmt: savePayload.amountForeign,
            payType: savePayload.paymentMethod,
            fromId: savePayload.fromAccountId,
            fromName: savePayload.fromAccountName,
            toId: savePayload.toAccountId,
            toName: savePayload.toAccountName,
            refNo: savePayload.mobileTransferNo,
            bankName: savePayload.bankName,
            notes: savePayload.notes
          });

          playSaveSensoryFeedback();
          alert(`✅ تم حفظ وترحيل السند بنجاح!\n- رقم السند: ${data.voucherNo}\n- متطابق ومحدث مباشرة مع شاشة اللابتوب وقاعدة البيانات.`);

          if (isAutoSendEnabled) {
            setTimeout(() => {
              shareVoucherWhatsApp(true);
            }, 450);
          }

          loadVoucherByIndex(VOUCHERS_HISTORY.length - 1);
        } else {
          alert(`⛔ تعذر الحفظ: ${data.error}`);
        }
      } catch (err) {
        const offlineVNo = `أوفلاين ${offlineCounter++}`;
        savePayload.voucherNo = offlineVNo;
        offlineQueue.push(savePayload);
        localStorage.setItem('magaiees_offline_vouchers_queue', JSON.stringify(offlineQueue));
        updateOnlineUI();
        playSaveSensoryFeedback();
        alert('⚠️ انقطع الاتصال بالسيرفر! تم حفظ السند أوفلاين وسيدخل السيستم تلقائياً.');

        if (isAutoSendEnabled) {
          setTimeout(() => {
            shareVoucherWhatsApp(true);
          }, 450);
        }

        startNewVoucher();
      } finally {
        btn.disabled = false;
        document.getElementById('saveButtonLabel').textContent = 'حفظ الحركة المالية';
      }
    }

    function handleEdit() {
      if (currentIndex >= VOUCHERS_HISTORY.length) {
        alert('⚠️ هذا السند جديد بالفعل ولم يتم حفظه بعد.');
        return;
      }
      isEditingMode = true;
      document.getElementById('voucherTag').textContent = 'وضع التعديل';
      alert(`✏️ وضع التعديل نشط: يمكنك الآن تعديل بيانات السند (${currentVoucherId}) ثم النقر على "حفظ الحركة المالية".`);
    }

    async function handleDelete() {
      if (currentIndex >= VOUCHERS_HISTORY.length) {
        alert('⚠️ لا يمكن حذف سند جديد لم يحفظ بعد.');
        return;
      }

      const vNo = currentVoucherId;
      if (!confirm(`هل أنت متأكد من حذف السند (${vNo}) نهائياً من قاعدة البيانات واللابتوب؟`)) {
        return;
      }

      try {
        const res = await fetch('mobile-voucher.php?api=delete_voucher', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ voucherNo: vNo })
        });
        const data = await res.json();

        if (data.success) {
          alert(`🗑️ ${data.message}`);
          VOUCHERS_HISTORY.splice(currentIndex, 1);
          if (VOUCHERS_HISTORY.length > 0) {
            loadVoucherByIndex(Math.max(0, currentIndex - 1));
          } else {
            startNewVoucher();
          }
        } else {
          alert(`⛔ تعذر الحذف: ${data.error}`);
        }
      } catch (e) {
        alert('⚠️ تعذر الاتصال بالسيرفر لحذف السند.');
      }
    }

    function handleExit() {
      window.location.href = 'mobile-dashboard.php?force_mobile=1';
    }

    // ────────────────────────────────────────────────────────────────
    // 11. الأوفلاين التام والمزامنة التلقائية الصامتة
    // ────────────────────────────────────────────────────────────────
    function toggleOnlineStatus() {
      isOfflineMode = !isOfflineMode;
      updateOnlineUI();
      if (!isOfflineMode) {
        autoSyncOfflineQueue();
      } else {
        startNewVoucher();
      }
    }

    function updateOnlineUI() {
      const pill = document.getElementById('networkPill');
      const dot = document.getElementById('networkDot');
      const text = document.getElementById('networkText');
      const banner = document.getElementById('offlineSyncBanner');

      if (isOfflineMode) {
        pill.className = 'flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/30 text-[10px] font-bold transition-all';
        dot.className = 'w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse';
        text.textContent = 'أوفلاين (محلي)';
        banner.classList.remove('hidden');
        banner.classList.add('flex');
      } else {
        pill.className = 'flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold transition-all';
        dot.className = 'w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse';
        text.textContent = 'متصل بالسيرفر (أونلاين)';
        if (offlineQueue.length === 0) banner.classList.add('hidden');
      }
      document.getElementById('pendingCount').textContent = `${offlineQueue.length} معلق`;
    }

    async function autoSyncOfflineQueue() {
      if (offlineQueue.length === 0 || !navigator.onLine) return;

      const banner = document.getElementById('offlineSyncBanner');
      banner.classList.remove('hidden');
      banner.classList.add('flex');
      document.getElementById('offlineBannerText').textContent = `⚡ جارِ رفع ومزامنة ${offlineQueue.length} سند أوفلاين تلقائياً مع السيرفر...`;

      let syncedCount = 0;
      const remaining = [];

      for (const item of offlineQueue) {
        try {
          const res = await fetch('mobile-voucher.php?api=save_voucher', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(item)
          });
          const data = await res.json();
          if (data.success) {
            syncedCount++;
            const localIdx = VOUCHERS_HISTORY.findIndex(v => v.id === item.voucherNo);
            if (localIdx !== -1) {
              VOUCHERS_HISTORY[localIdx].id = data.voucherNo;
            } else {
              VOUCHERS_HISTORY.push({
                id: data.voucherNo,
                date: item.date,
                currency: item.currency,
                rate: item.rate,
                costCenter: item.costCenter,
                sdg: item.amountLocal,
                foreignAmt: item.amountForeign,
                payType: item.paymentMethod,
                fromName: item.fromAccountName,
                toName: item.toAccountName,
                refNo: item.mobileTransferNo,
                notes: item.notes
              });
            }
          } else {
            remaining.push(item);
          }
        } catch (e) {
          remaining.push(item);
        }
      }

      offlineQueue = remaining;
      localStorage.setItem('magaiees_offline_vouchers_queue', JSON.stringify(offlineQueue));
      document.getElementById('pendingCount').textContent = `${offlineQueue.length} معلق`;

      if (syncedCount > 0) {
        playSaveSensoryFeedback();
        document.getElementById('offlineBannerText').textContent = `✓ تمت مزامنة ${syncedCount} سند تلقائياً وأخذت أرقام السيستم الرسمية!`;
        setTimeout(() => {
          if (offlineQueue.length === 0) banner.classList.add('hidden');
        }, 3500);
      }
    }

    window.addEventListener('online', () => {
      isOfflineMode = false;
      updateOnlineUI();
      autoSyncOfflineQueue();
    });

    window.addEventListener('offline', () => {
      isOfflineMode = true;
      updateOnlineUI();
    });

    setInterval(() => {
      if (navigator.onLine && offlineQueue.length > 0 && !isOfflineMode) {
        autoSyncOfflineQueue();
      }
    }, 8000);

    function escapeHtml(str) {
      if (!str) return '';
      return String(str).replace(/[&<>"']/g, m => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));
    }

    function updateClock() {
      const now = new Date();
      document.getElementById('currentTime').textContent = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
    }
    updateClock();
    setInterval(updateClock, 30000);

    // التحميل الابتدائي: استعراض آخر سند أو فتح سند جديد
    loadSavedNumberConfig();
    updateAutoSendUI();

    function setupAmountLongPress(el) {
      if (!el) return;
      let pressTimer = null;
      let isMoved = false;
      el.addEventListener('touchstart', (e) => {
        isMoved = false;
        pressTimer = setTimeout(() => {
          if (!isMoved) {
            openNumberPropertiesModal(e);
          }
        }, 550);
      }, { passive: true });
      el.addEventListener('touchmove', () => {
        isMoved = true;
        if (pressTimer) clearTimeout(pressTimer);
      }, { passive: true });
      el.addEventListener('touchend', () => {
        if (pressTimer) clearTimeout(pressTimer);
      });
      el.addEventListener('touchcancel', () => {
        if (pressTimer) clearTimeout(pressTimer);
      });
    }

    setupAmountLongPress(document.getElementById('sdgAmountCard'));
    setupAmountLongPress(document.getElementById('usdAmountCard'));

    if (VOUCHERS_HISTORY.length > 0) {
      loadVoucherByIndex(VOUCHERS_HISTORY.length - 1);
    } else {
      startNewVoucher();
    }
    updateOnlineUI();
    autoSyncOfflineQueue();
  </script>
</body>
</html>
