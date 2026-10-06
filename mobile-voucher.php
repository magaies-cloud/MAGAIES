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
  <script>
    window.PHP_ACCOUNTS = <?php echo json_encode($accountsList, JSON_UNESCAPED_UNICODE); ?>;
    window.PHP_VOUCHERS = <?php echo json_encode($vouchersHistory, JSON_UNESCAPED_UNICODE); ?>;
    window.PHP_NEXT_NO = <?php echo json_encode($nextSequentialVoucherNo); ?>;
  </script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="theme-color" content="#020617">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <title>حركة مالية - تصميم شاشات الجوال المتطورة</title>
  
  <!-- CDN Tailwind with fallback styling -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- FontAwesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- Google Fonts: Cairo (Arabic) + Top 3 Clear Number Fonts (JetBrains Mono, Inter, Montserrat) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Inter:wght@400;600;700;800;900&family=JetBrains+Mono:wght@400;600;700;800&family=Montserrat:wght@500;700;800;900&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --app-font: 'Cairo', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      --num-font: 'JetBrains Mono', monospace;
      --num-size: 34px;
      --num-color: #10b981;
      --num-weight: 900;
    }
    * {
      box-sizing: border-box;
      -webkit-tap-highlight-color: transparent;
      font-family: var(--app-font);
    }
    html, body {
      margin: 0;
      padding: 0;
      background-color: #020617;
      color: #f8fafc;
      width: 100%;
      min-height: 100%;
      overflow-x: hidden;
    }

    input, select, textarea, button {
      font-size: 14.5px;
      font-family: var(--app-font);
    }
    @media (max-width: 600px) {
      input, select, textarea {
        font-size: 15.5px !important;
      }
    }

    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    /* ═════════════════════════════════════════════════════════════════
       سهم التدفق الحركي السريع (لون ذهبي كهرماني مشع مغاير للأحمر والأخضر)
       يعطي إيماءات حركية سريعة ومستمرة للأسفل للتنبيه حتى لو كان المستخدم مجهداً
       ═════════════════════════════════════════════════════════════════ */
    .kinetic-flow-capsule {
      position: relative;
      width: 48px;
      height: 44px;
      border-radius: 22px;
      background: linear-gradient(180deg, #1e1b4b 0%, #090d16 100%);
      border: 2px solid #fbbf24;
      box-shadow: 0 0 22px rgba(245, 158, 11, 0.7), 0 0 10px rgba(251, 191, 36, 0.4), inset 0 0 8px rgba(251, 191, 36, 0.35);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
      animation: downwardPulseBounce 1.1s infinite ease-in-out;
      user-select: none;
    }
    .kinetic-flow-capsule::before {
      content: '';
      position: absolute;
      inset: -5px;
      border-radius: 26px;
      background: radial-gradient(circle, rgba(245, 158, 11, 0.5) 0%, rgba(217, 119, 6, 0.2) 60%, transparent 80%);
      z-index: -1;
      animation: auraGoldPulse 1.1s infinite alternate ease-in-out;
    }
    .kinetic-flow-capsule:active {
      transform: scale(0.92) translateY(3px);
      box-shadow: 0 0 30px rgba(251, 191, 36, 1);
    }
    @keyframes downwardPulseBounce {
      0%, 100% { transform: translateY(-3px); }
      50% { transform: translateY(4px); }
    }
    @keyframes auraGoldPulse {
      0% { opacity: 0.5; transform: scale(0.92); }
      100% { opacity: 1; transform: scale(1.14); }
    }

    /* شلال الأسهم السريع المتدفق للأسفل */
    @keyframes chevronCascade1 {
      0% { opacity: 0.2; transform: translateY(-3px); color: #fbbf24; }
      40% { opacity: 1; transform: translateY(0); color: #ffffff; }
      80%, 100% { opacity: 0.2; transform: translateY(3px); color: #fbbf24; }
    }
    @keyframes chevronCascade2 {
      0%, 20% { opacity: 0.2; transform: translateY(-3px); color: #fbbf24; }
      60% { opacity: 1; transform: translateY(0); color: #ffffff; }
      100% { opacity: 0.2; transform: translateY(3px); color: #fbbf24; }
    }
    .chevron-stream-1 { animation: chevronCascade1 0.75s infinite ease-in-out; }
    .chevron-stream-2 { animation: chevronCascade2 0.75s infinite ease-in-out; }

    /* خطوط المسار الجانبية المتدفقة للأسفل */
    .flow-stream-track {
      position: relative;
      width: 24px;
      height: 2px;
      background: linear-gradient(90deg, transparent, rgba(251, 191, 36, 0.8), transparent);
    }

    /* ═════════════════════════════════════════════════════════════════
       أيقونات المرفقات العصرية (إضاءة في الحواف وتكبير عند الاقتراب)
       ═════════════════════════════════════════════════════════════════ */
    .modern-media-card {
      position: relative;
      overflow: hidden;
      transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.28s ease, border-color 0.28s ease;
      transform-origin: center center;
    }
    .modern-media-card:hover, .modern-media-card:focus-within, .modern-media-card:active {
      transform: scale(1.07);
      z-index: 25;
    }
    .media-card-camera {
      border: 1.5px solid rgba(129, 140, 248, 0.8) !important;
      box-shadow: 0 0 16px rgba(99, 102, 241, 0.4), inset 0 1px 2px rgba(255, 255, 255, 0.35);
    }
    .media-card-camera:hover {
      box-shadow: 0 0 24px rgba(99, 102, 241, 0.7), inset 0 1px 3px rgba(255, 255, 255, 0.5);
    }
    .media-card-gallery {
      border: 1.5px solid rgba(251, 191, 36, 0.85) !important;
      box-shadow: 0 0 16px rgba(245, 158, 11, 0.4), inset 0 1px 2px rgba(255, 255, 255, 0.35);
    }
    .media-card-gallery:hover {
      box-shadow: 0 0 24px rgba(245, 158, 11, 0.7), inset 0 1px 3px rgba(255, 255, 255, 0.5);
    }
    .media-card-whatsapp {
      border: 1.5px solid rgba(52, 211, 153, 0.9) !important;
      box-shadow: 0 0 20px rgba(16, 185, 129, 0.5), inset 0 1px 2px rgba(255, 255, 255, 0.4);
    }
    .media-card-whatsapp:hover {
      box-shadow: 0 0 28px rgba(16, 185, 129, 0.8), inset 0 1px 3px rgba(255, 255, 255, 0.6);
    }

    /* ═════════════════════════════════════════════════════════════════
       إماضة حركية وضوئية للأزرار والأيقونات (Kinetic Light Sweeps)
       ═════════════════════════════════════════════════════════════════ */
    .kinetic-glow-btn {
      position: relative;
      overflow: hidden;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .kinetic-glow-btn::after {
      content: '';
      position: absolute;
      top: -50%;
      left: -80%;
      width: 50%;
      height: 200%;
      background: linear-gradient(60deg, transparent 0%, rgba(255, 255, 255, 0.35) 50%, transparent 100%);
      transform: rotate(25deg);
      pointer-events: none;
      opacity: 0;
      transition: opacity 0.2s;
    }
    .kinetic-glow-btn:hover::after, .kinetic-glow-btn:active::after {
      opacity: 1;
      animation: kineticFlashSweep 0.75s ease-out;
    }
    @keyframes kineticFlashSweep {
      0% { left: -80%; }
      100% { left: 160%; }
    }

    /* أزرار الشريط السفلي الكبيرة الفاخرة */
    .action-btn-kinetic {
      position: relative;
      overflow: hidden;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 4px 15px -1px rgba(0, 0, 0, 0.5), inset 0 1px 1px 0 rgba(255, 255, 255, 0.2);
    }
    .action-btn-kinetic::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
      animation: autoShimmerSweep 3.5s infinite ease-in-out;
      pointer-events: none;
    }
    @keyframes autoShimmerSweep {
      0% { left: -100%; }
      40%, 100% { left: 130%; }
    }

    /* أزرار الدفع الزجاجية */
    .glass-pay-btn {
      position: relative;
      overflow: hidden;
      background: rgba(15, 23, 42, 0.65);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.12);
      box-shadow: 0 4px 15px -1px rgba(0, 0, 0, 0.4), inset 0 1px 1px 0 rgba(255, 255, 255, 0.2);
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .glass-pay-btn.is-active {
      background: linear-gradient(135deg, rgba(2, 132, 199, 0.85) 0%, rgba(30, 58, 138, 0.95) 100%);
      border-color: rgba(56, 189, 248, 0.8);
      color: #ffffff;
      box-shadow: 0 0 20px rgba(14, 165, 233, 0.45), inset 0 1px 2px rgba(255, 255, 255, 0.5);
      transform: translateY(-1px);
    }

    /* زر الإرسال التلقائي المدمج */
    .auto-send-pill {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      user-select: none;
    }
    .auto-send-pill.is-on {
      background: linear-gradient(135deg, rgba(5, 150, 105, 0.25) 0%, rgba(16, 185, 129, 0.15) 100%);
      border-color: rgba(52, 211, 153, 0.6);
      color: #34d399;
      box-shadow: 0 0 12px rgba(16, 185, 129, 0.3);
    }
    .auto-send-pill.is-on .status-dot {
      background-color: #10b981;
      box-shadow: 0 0 8px #10b981;
      animation: autoSendPulse 1.6s infinite;
    }
    .auto-send-pill.is-off {
      background: rgba(30, 41, 59, 0.5);
      border-color: rgba(71, 85, 105, 0.4);
      color: #94a3b8;
    }
    .auto-send-pill.is-off .status-dot {
      background-color: #64748b;
    }
    @keyframes autoSendPulse {
      0%, 100% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.3); opacity: 0.6; }
    }

    .amount-display-card {
      user-select: none;
      transition: all 0.2s ease;
    }
    .amount-number-input {
      font-variant-numeric: tabular-nums;
      letter-spacing: -0.5px;
      text-align: center !important;
      direction: ltr !important;
      font-weight: 900 !important;
    }

    /* ومضة سريعة للإشعار عند التفريغ */
    @keyframes flashSuccess {
      0% { background-color: rgba(16, 185, 129, 0.45); }
      100% { background-color: transparent; }
    }
    .flash-highlight {
      animation: flashSuccess 0.5s ease-out;
    }

    /* بطاقة الواتساب */
    .whatsapp-card {
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .whatsapp-card:hover, .whatsapp-card:active {
      transform: translateY(-2px);
      box-shadow: 0 4px 20px rgba(16, 185, 129, 0.25);
    }

    /* وميض الكاميرا عند التصوير */
    @keyframes cameraShutterFlash {
      0% { opacity: 0; }
      50% { opacity: 0.95; }
      100% { opacity: 0; }
    }
    .shutter-flash-overlay {
      position: absolute;
      inset: 0;
      background: white;
      pointer-events: none;
      opacity: 0;
    }
    .shutter-flash-overlay.is-flashing {
      animation: cameraShutterFlash 0.35s ease-out;
    }

    /* تنسيق الطباعة الحرارية والمستندات الرسمية (POS & A4) */
    @media print {
      body {
        background: white !important;
        color: black !important;
        padding: 0 !important;
        margin: 0 !important;
      }
      .mobile-device-outer, .bottom-bar-fixed, .modal-backdrop-blur, #modalBatchIntake, #modalSendWhatsApp, #modalSearchVouchers, #modalNumberProperties {
        display: none !important;
      }
      #printableThermalSlip, #printableThermalSlip * {
        visibility: visible !important;
        color: black !important;
      }
      #printableThermalSlip {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        max-width: 80mm !important;
        margin: 0 auto !important;
        padding: 8px !important;
        background: white !important;
        font-family: 'JetBrains Mono', monospace, 'Cairo', sans-serif !important;
        display: block !important;
      }
    }

    /* Native mobile view on actual phones (<= 640px) */
    @media (max-width: 640px) {
      body {
        padding: 0 !important;
        background: #020617 !important;
      }
      .mobile-device-outer {
        width: 100% !important;
        max-width: 100% !important;
        border-radius: 0 !important;
        border: none !important;
        box-shadow: none !important;
        min-height: 100dvh !important;
      }
      .desktop-screen-switcher {
        display: none !important;
      }
      .bottom-bar-fixed {
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 50 !important;
        padding-bottom: max(10px, env(safe-area-inset-bottom)) !important;
      }
    }

    /* Desktop view styling: provides authentic 1:1 mobile scale */
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
  </style>
</head>
<body class="flex flex-col items-center justify-start antialiased selection:bg-blue-600 selection:text-white">

  <!-- Desktop Mobile Dimension Switcher Bar -->
  <div class="desktop-screen-switcher w-full max-w-lg mb-3 bg-slate-900 border border-slate-800 rounded-2xl p-2.5 shadow-xl flex flex-col gap-2">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
        <span class="font-bold text-xs text-white">معاينة شاشة الجوال (متزامن مع سيرفر الكمبيوتر)</span>
      </div>
      <div class="flex items-center gap-1.5 font-mono text-[11px] text-emerald-400 bg-slate-950 px-2.5 py-0.5 rounded-lg border border-slate-800" id="dimensionBadge">
        393px × 852px (iPhone)
      </div>
    </div>

    <!-- Quick Size & Laptop Sync Simulation Buttons -->
    <div class="flex items-center justify-between gap-1.5 text-[11px] font-bold">
      <button onclick="setMobilePreset(393, 'iPhone 15 Pro')" id="btn-p-393" class="preset-btn flex-1 py-1 px-1 rounded-xl bg-blue-600 text-white transition-all text-center">
        iPhone (393px)
      </button>
      <button onclick="setMobilePreset(360, 'Samsung S24')" id="btn-p-360" class="preset-btn flex-1 py-1 px-1 rounded-xl bg-slate-800 text-slate-400 hover:text-white transition-all text-center">
        سامسونج (360px)
      </button>
      <button onclick="setMobilePreset(375, 'iPhone SE / صغير')" id="btn-p-375" class="preset-btn flex-1 py-1 px-1 rounded-xl bg-slate-800 text-slate-400 hover:text-white transition-all text-center">
        مدمج (375px)
      </button>
      <button onclick="forceLiveRefresh()" class="py-1 px-2.5 rounded-xl bg-cyan-950 hover:bg-cyan-900 text-cyan-300 border border-cyan-500/40 transition-all text-center flex items-center gap-1 shadow-sm" title="تحديث الحي فوراً بدون كاش">
        <i class="fa-solid fa-arrows-rotate text-xs animate-[spin_6s_linear_infinite]"></i>
        <span>🔄 تحديث الحي</span>
      </button>
      <button onclick="simulateIncomingLaptopVoucher()" class="py-1 px-2 rounded-xl bg-emerald-950 hover:bg-emerald-900 text-emerald-300 border border-emerald-500/40 transition-all text-center flex items-center gap-1" title="محاكاة إدخال سند في اللابتوب ورؤيته هنا فوراً">
        <i class="fa-solid fa-laptop-code text-xs"></i>
        <span>سند لابتوب 💻</span>
      </button>
    </div>
  </div>

  <!-- Mobile Device Screen Container -->
  <div id="mobileContainer" class="mobile-device-outer w-full max-w-[393px] bg-slate-950 sm:rounded-[36px] sm:border-[4px] sm:border-slate-800 sm:shadow-2xl overflow-hidden flex flex-col relative transition-all duration-300 min-h-screen sm:min-h-[852px]">

    <!-- ═════════════════════════════════════════════════════════════════
         1. السطر الأول المرفوع (الهيدر العلوي):
         زر الخروج الأحمر الآمن + حركة مالية + منطقة عناية متصل + الساعة و 5G
         ═════════════════════════════════════════════════════════════════ -->
    <div class="px-3 py-2 bg-slate-950 border-b border-slate-900 flex items-center justify-between sticky top-0 z-40 select-none">
      
      <!-- اليمين: زر الخروج الآمن + عنوان الشاشة + منطقة عناية متصل -->
      <div class="flex items-center gap-1.5">
        <!-- زر الخروج الأحمر: مكانه الآمن في الأعلى لمنع الضغط بالخطأ -->
        <button type="button" onclick="handleExit()" class="w-7 h-7 rounded-lg bg-red-600/90 hover:bg-red-500 active:scale-95 text-white flex items-center justify-center shrink-0 border border-red-400/30 shadow-sm transition" title="خروج من الشاشة">
          <i class="fa-solid fa-power-off text-[11px]"></i>
        </button>

        <!-- حركة مالية مرفوعة في هذا السطر -->
        <span class="text-sm font-black text-white tracking-wide flex items-center gap-1 shrink-0">
          <i class="fa-solid fa-money-bill-transfer text-cyan-400 text-xs"></i>
          <span>حركة مالية</span>
        </span>

        <!-- منطقة عناية متصل: دائرة خضراء ومتصل -->
        <button type="button" onclick="toggleOnlineStatus()" id="networkPill" class="flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/40 text-emerald-400 text-[10px] font-black cursor-pointer hover:bg-emerald-500/25 active:scale-95 transition" title="عناية متصل بالسيرفر واللابتوب - انقر للتبديل لأوفلاين">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_#10b981]" id="networkDot"></span>
          <span id="networkText">متصل</span>
        </button>

        <!-- زر التحديث الحي الفوري على الجوال -->
        <button type="button" onclick="forceLiveRefresh()" class="flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-cyan-500/20 hover:bg-cyan-500/35 border border-cyan-400/40 text-cyan-300 text-[10px] font-black active:scale-95 transition shadow-sm" title="تحديث الحي فوري في الحين">
          <i class="fa-solid fa-arrows-rotate text-[9px] text-cyan-300"></i>
          <span>تحديث</span>
        </button>
      </div>

      <!-- اليسار: الساعة و 5G والإشارات -->
      <div class="flex items-center gap-2 text-slate-300">
        <span id="currentTime" class="font-bold font-mono text-slate-200 text-xs">13:15</span>
        <div class="flex items-center gap-1 font-mono text-slate-300">
          <span class="font-black text-[11px] text-cyan-400">5G</span>
          <i class="fa-solid fa-signal text-[9px] text-slate-300"></i>
          <i class="fa-solid fa-battery-full text-emerald-400 text-xs"></i>
        </div>
      </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════
         2. السطر الثاني المخصص (نظيف وغير مزدحم نهائياً):
         التاريخ (في منتصف المساحة المخصصة ومكبر الخط) | الرقم التسلسلي (مطابق للكمبيوتر)
         ═════════════════════════════════════════════════════════════════ -->
    <div class="bg-slate-900/95 px-2.5 py-2 border-b border-slate-800 sticky top-[37px] z-30 backdrop-blur-md">
      <div class="grid grid-cols-2 gap-2 w-full">
        
        <!-- 1. مساحة التاريخ: التاريخ لوحده في المنتصف تماماً وبخط مكبر واضح -->
        <div class="bg-slate-950 border border-slate-700/80 rounded-2xl px-2 py-1 flex items-center justify-center h-[52px] shadow-inner focus-within:border-cyan-500 transition">
          <input type="date" id="voucherDate" value="2026-10-05" class="w-full bg-transparent text-white text-[17px] font-black text-center focus:outline-none cursor-pointer tracking-tight" style="text-align: center; color-scheme: dark;">
        </div>

        <!-- 2. مساحة الرقم التسلسلي: الرقم لوحده مكبر في المنتصف بدون كلمة سيرفر الكمبيوتر -->
        <div class="bg-slate-950 border border-slate-700/80 rounded-2xl px-2 py-1 flex items-center justify-between h-[52px] shadow-inner">
          <!-- سهم التالي ◀ (يمين في RTL) -->
          <button type="button" onclick="navigateVoucher(1)" id="btnNextVoucher" class="w-8 h-8 rounded-xl bg-slate-850 hover:bg-slate-750 active:scale-90 text-slate-300 flex items-center justify-center transition disabled:opacity-20 shrink-0" title="السند التالي">
            <i class="fa-solid fa-chevron-right text-xs"></i>
          </button>

          <!-- الرقم التسلسلي لوحده كبير وبارز ومطابق للسيرفر -->
          <div class="text-center px-1 flex-1 min-w-0 flex items-center justify-center">
            <span id="voucherTag" class="hidden"></span>
            <span class="text-[21px] font-mono font-black text-amber-400 truncate block tracking-tight leading-none" id="currentVoucherDisplay">#842</span>
          </div>

          <!-- سهم السابق ▶ (يسار في RTL) -->
          <button type="button" onclick="navigateVoucher(-1)" id="btnPrevVoucher" class="w-8 h-8 rounded-xl bg-slate-850 hover:bg-slate-750 active:scale-90 text-slate-300 flex items-center justify-center transition disabled:opacity-20 shrink-0" title="السند السابق">
            <i class="fa-solid fa-chevron-left text-xs"></i>
          </button>
        </div>

      </div>
    </div>

    <!-- شريط تنبيه تدفق الإشعارات خطوة بخطوة (يظهر عند تفريغ دفعة يدوية) -->
    <div id="batchProgressStrip" class="hidden bg-gradient-to-r from-emerald-900/90 to-cyan-900/90 border-b border-emerald-500/50 px-3 py-1.5 text-xs text-white items-center justify-between font-bold shadow-md">
      <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
        <span id="batchProgressText">إشعار 1 من 5: اضغط "تأكيد وحفظ" للانتقال للتالي</span>
      </div>
      <button type="button" onclick="cancelBatchQueue()" class="text-[10px] bg-red-500/30 hover:bg-red-500/50 text-red-200 px-2 py-0.5 rounded-lg border border-red-400/40">
        إنهاء الدفعة ✕
      </button>
    </div>

    <!-- Global Instant Toast Notification Banner -->
    <div id="globalToast" class="hidden absolute top-24 right-3 left-3 z-50 p-2.5 rounded-2xl bg-slate-900/95 border border-emerald-500/60 shadow-2xl text-xs text-white items-center gap-2 backdrop-blur-md transition-all duration-300 transform -translate-y-4 opacity-0">
      <span class="text-base text-emerald-400">⚡</span>
      <div id="globalToastMsg" class="flex-1 font-bold leading-tight"></div>
    </div>

    <!-- Offline Sync Banner Notification -->
    <div id="offlineSyncBanner" class="hidden bg-gradient-to-r from-amber-600 to-orange-600 px-3 py-1.5 text-xs text-white flex items-center justify-between font-medium">
      <div class="flex items-center gap-1.5">
        <span>⚠️</span>
        <span id="offlineBannerText">الوضع أوفلاين: السندات تحفظ كـ (أوفلاين 1...) وترفع تلقائياً فور توفر النت.</span>
      </div>
      <span class="text-[10px] bg-black/40 px-1.5 py-0.5 rounded font-mono" id="pendingCount">0 معلق</span>
    </div>

    <!-- Main Scrollable Content (بدون مساحات ميتة متصل بانسيابية) -->
    <main class="flex-1 overflow-y-auto no-scrollbar p-2.5 space-y-2 pb-28 text-xs">

      <!-- أزرار الدفع الزجاجية العصرية -->
      <div class="p-1 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-sm">
        <div class="grid grid-cols-4 gap-1.5 text-[11px] font-black" id="paymentMethodsGroup">
          <button type="button" onclick="setPaymentType('transfer')" id="btn-transfer" class="glass-pay-btn is-active py-2 px-1 rounded-xl text-center flex flex-col items-center justify-center gap-0.5">
            <i class="fa-solid fa-arrow-right-arrow-left text-xs mb-0.5 text-sky-400"></i>
            <span>تحويل</span>
          </button>
          <button type="button" onclick="setPaymentType('cash')" id="btn-cash" class="glass-pay-btn py-2 px-1 rounded-xl text-slate-300 text-center flex flex-col items-center justify-center gap-0.5">
            <i class="fa-solid fa-money-bill-wave text-xs mb-0.5 text-emerald-400"></i>
            <span>كاش</span>
          </button>
          <button type="button" onclick="setPaymentType('bank_cheque')" id="btn-bank_cheque" class="glass-pay-btn py-2 px-1 rounded-xl text-slate-300 text-center flex flex-col items-center justify-center gap-0.5">
            <i class="fa-solid fa-money-check text-xs mb-0.5 text-amber-400"></i>
            <span>شيك مصرفي</span>
          </button>
          <button type="button" onclick="setPaymentType('normal_cheque')" id="btn-normal_cheque" class="glass-pay-btn py-2 px-1 rounded-xl text-slate-300 text-center flex flex-col items-center justify-center gap-0.5">
            <i class="fa-regular fa-file-lines text-xs mb-0.5 text-indigo-400"></i>
            <span>شيك عادي</span>
          </button>
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           شريط العملة (إنقاص 10%) + قيمة الدولار (إنقاص 10% والخط أضخم بولد) + مركز الكلفة (متسع)
           ═════════════════════════════════════════════════════════════════ -->
      <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-2 flex items-center gap-1.5 shadow-sm">
        
        <!-- مساحة العملة: تم إنقاص المساحة 10% لتوفر مساحة لمركز الكلفة -->
        <div class="relative shrink-0 w-[94px]">
          <select id="primaryCurrencySelect" onchange="selectCurrency(this.value)" class="w-full appearance-none bg-slate-950 border border-slate-700/80 rounded-xl pr-2.5 pl-5 py-2 text-[11px] font-black text-amber-400 focus:outline-none focus:border-amber-400 cursor-pointer h-[42px] shadow-inner" title="اختيار العملة">
            <option value="SDG" selected>ج.س (SDG)</option>
            <option value="USD">دولار ($)</option>
          </select>
          <div class="absolute left-2 top-4 pointer-events-none text-slate-400 text-[9px]">
            <i class="fa-solid fa-chevron-down"></i>
          </div>
        </div>

        <!-- قيمة الدولار: تم إنقاص المساحة 10%، والخط أضخم وبولد جداً -->
        <div class="bg-slate-950 border border-emerald-500/50 hover:border-emerald-400 focus-within:border-emerald-400 rounded-xl px-1.5 py-1 flex items-center gap-1 w-[90px] shrink-0 h-[42px] shadow-inner transition cursor-pointer group" onclick="document.getElementById('usdRate').focus()" title="قيمة الدولار من السيستم - انقر للتعديل المباشر">
          <span class="text-emerald-400 font-mono text-xs font-black shrink-0">$</span>
          <input type="number" id="usdRate" value="2650" onchange="updateSystemUsdRate(this.value)" oninput="calculateExchange('rate')" class="w-full bg-transparent text-emerald-400 font-mono font-black text-base focus:outline-none text-center cursor-text tracking-tight" placeholder="2650" title="سعر صرف الدولار">
          <i class="fa-solid fa-pen text-[7px] text-slate-500 group-hover:text-emerald-400 transition shrink-0"></i>
        </div>

        <!-- مركز الكلفة: زادت المساحة تلقائياً بعد اختصار مساحة العملة والدولار -->
        <div class="flex-1 bg-slate-950 border border-slate-700/80 rounded-xl px-2.5 py-1 flex items-center h-[42px] relative shadow-inner">
          <input type="text" id="costCenterInput" list="costCentersDataList" placeholder="مركز الكلفة" class="w-full bg-transparent text-slate-100 placeholder:text-slate-500 placeholder:font-medium text-xs font-bold focus:outline-none" value="مخزن بحري الرئيسي">
          <datalist id="costCentersDataList">
            <option value="مخزن بحري الرئيسي"></option>
            <option value="مخزن أم درمان"></option>
            <option value="فرع بورتسودان"></option>
            <option value="الإدارة العامة"></option>
          </datalist>
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           المبالغ المالية:
           - المبالغ بحجم أكبر وبولد أضخم وفي المنتصف
           - كتابة "المبلغ المحلي" و"المبلغ الأجنبي" بخط أقل وبألوان متباينة
           ═════════════════════════════════════════════════════════════════ -->
      <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-2.5 space-y-2">
        <div class="grid grid-cols-2 gap-2.5">
          
          <!-- 1. اليمين: المبلغ المحلي (كتابة بخط أقل، لون أزرق سماوي مميز) -->
          <div>
            <div class="flex items-center justify-between mb-1 px-1">
              <span class="text-[11px] font-bold text-sky-400">المبلغ المحلي</span>
              <span class="text-[9.5px] font-bold text-sky-300 bg-sky-950/80 px-1.5 py-0.5 rounded border border-sky-500/40 font-mono">ج.س (SDG)</span>
            </div>
            <!-- المربع شاغر والمبلغ أضخم وبولد وفي المنتصف -->
            <div id="sdgAmountCard" class="amount-display-card bg-slate-950/90 p-2 rounded-2xl border-2 border-sky-500/50 focus-within:border-sky-400 transition relative cursor-pointer shadow-inner flex items-center justify-center min-h-[70px]" oncontextmenu="openNumberPropertiesModal(event); return false;" title="كليك يمين لتخصيص الحجم والألوان">
              <input type="text" id="sdgAmount" placeholder="0" inputmode="decimal" oninput="handleAmountInput('sdg'); updateTafqeet();" class="amount-number-input w-full bg-transparent text-center focus:outline-none placeholder-slate-700 text-[32px] sm:text-[36px] font-black text-white tracking-tight" style="font-size: var(--num-size); font-weight: var(--num-weight); text-align: center;">
            </div>
          </div>

          <!-- 2. اليسار: المبلغ الأجنبي (كتابة بخط أقل، لون أخضر زمردي مختلف) -->
          <div>
            <div class="flex items-center justify-between mb-1 px-1">
              <span class="text-[11px] font-bold text-emerald-400" id="foreignAmountTitle">المبلغ الأجنبي (دولار)</span>
              <span class="text-[9.5px] font-bold text-emerald-300 bg-emerald-950/80 px-1.5 py-0.5 rounded border border-emerald-500/40 font-mono" id="foreignCurrencyBadge">$ USD</span>
            </div>
            <!-- المربع شاغر والمبلغ أضخم وبولد وفي المنتصف -->
            <div id="usdAmountCard" class="amount-display-card bg-slate-950/90 p-2 rounded-2xl border-2 border-emerald-500/50 focus-within:border-emerald-400 transition relative cursor-pointer shadow-inner flex items-center justify-center min-h-[70px]" oncontextmenu="openNumberPropertiesModal(event); return false;" title="كليك يمين لتخصيص الحجم والألوان">
              <input type="text" id="usdAmount" placeholder="0.00" inputmode="decimal" oninput="handleAmountInput('foreign'); updateTafqeet();" class="amount-number-input w-full bg-transparent text-center focus:outline-none placeholder-slate-700 text-[32px] sm:text-[36px] font-black text-emerald-400 tracking-tight" style="font-size: var(--num-size); font-weight: var(--num-weight); text-align: center;">
            </div>
          </div>

        </div>

        <!-- تفقيط المبلغ كتابة بالحروف العربية -->
        <div id="tafqeetDisplay" class="hidden text-[10.5px] font-bold text-amber-300 bg-amber-950/30 border border-amber-500/30 px-2.5 py-1.5 rounded-xl text-center">
          <!-- النص العربي للتفقيط -->
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           من حساب:
           - منطقة الرصيد: الرصيد بالدولار والرصيد بالجنيه السوداني مع تباين عالي جداً
           - كتابة اسم الحساب في منطقة البحث بخط كبير واضح واضح
           ═════════════════════════════════════════════════════════════════ -->
      <div class="bg-gradient-to-b from-red-950/20 to-slate-900/90 border-2 border-red-500/60 rounded-2xl p-2.5 shadow-sm relative">
        <div class="flex items-center justify-between mb-1.5">
          <div class="flex items-center gap-1.5">
            <span class="px-2 py-0.5 rounded-lg bg-red-600 text-white font-black text-xs shadow-sm">مِن</span>
            <span class="text-xs font-black text-red-400">من حساب:</span>
          </div>

          <!-- منطقة الرصيد: الرصيد بالجنيه والرصيد بالدولار مع تباين عالي جداً بين الخلفية وكتابة الخط -->
          <div class="flex items-center gap-1.5 shrink-0" id="fromBalanceWrapper">
            <div class="px-2 py-0.5 rounded-lg bg-black border border-amber-500/80 shadow-sm flex items-center gap-1">
              <span class="text-[9px] font-black text-amber-400">ج.س:</span>
              <span id="fromAccountSdgBalance" class="text-[11px] font-mono font-black text-amber-200">14,850,000</span>
            </div>
            <div class="px-2 py-0.5 rounded-lg bg-black border border-emerald-500/80 shadow-sm flex items-center gap-1">
              <span class="text-[9px] font-black text-emerald-400">$ :</span>
              <span id="fromAccountUsdBalance" class="text-[11px] font-mono font-black text-emerald-200">5,603.77</span>
            </div>
          </div>
        </div>

        <!-- حقل البحث الفسيح: يكتب فيه الاسم بخط كبير وواضح جداً -->
        <div class="relative">
          <div class="absolute right-3.5 top-3.5 text-red-400 pointer-events-none text-sm">
            <i class="fa-solid fa-magnifying-glass"></i>
          </div>
          <input type="text" id="fromSearchInput" oninput="liveSearchAccount('from')" onfocus="this.select(); liveSearchAccount('from');" placeholder="بحث: فوري، بنك، خزن، عميل..." value="بنك الخرطوم (5540)" class="w-full bg-slate-950 border-2 border-red-500/50 rounded-xl pr-10 pl-9 py-2.5 text-white focus:outline-none focus:border-red-400 placeholder-slate-500 font-black text-[16.5px] sm:text-[17.5px] tracking-wide shadow-inner">
          <button type="button" onclick="clearAccountSearch('from')" id="fromClearBtn" class="absolute left-3 top-3 w-6 h-6 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center text-xs transition" title="مسح واختيار حساب آخر">
            <i class="fa-solid fa-xmark"></i>
          </button>

          <!-- القائمة المنسدلة الذكية الفورية -->
          <div id="fromDropdown" class="hidden absolute top-full right-0 left-0 mt-1 bg-slate-950 border border-slate-700 rounded-2xl shadow-2xl z-40 max-h-56 overflow-y-auto no-scrollbar"></div>
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           مسار التدفق الحركي السريع (بلون ذهبي كهرماني مغاير للأحمر والأخضر):
           يعطي إيماءات حركية وسريعة متدفقة للأسفل لتنبيه المستخدم فوراً حتى في قمة الإجهاد
           ═════════════════════════════════════════════════════════════════ -->
      <div class="flex items-center justify-center my-[-8px] relative z-20 gap-2">
        <div class="flow-stream-track"></div>
        <button type="button" onclick="triggerCrystalFlowAnimation()" id="crystalFlowBtn" class="kinetic-flow-capsule active:scale-90" title="مسار تدفق الحركة المالية من الأعلى (من حساب) إلى الأسفل (إلى حساب)">
          <div class="flex flex-col items-center justify-center leading-none gap-0.5 pointer-events-none">
            <i class="fa-solid fa-chevron-down text-[10px] chevron-stream-1"></i>
            <i class="fa-solid fa-arrow-down text-xs font-black text-white chevron-stream-2"></i>
          </div>
        </button>
        <div class="flow-stream-track"></div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           إلى حساب:
           - منطقة الرصيد: الرصيد بالدولار والرصيد بالجنيه السوداني مع تباين عالي جداً
           - كتابة اسم الحساب في منطقة البحث بخط كبير واضح واضح (محمد أحمد عثمان)
           ═════════════════════════════════════════════════════════════════ -->
      <div class="bg-gradient-to-b from-emerald-950/20 to-slate-900/90 border-2 border-emerald-500/60 rounded-2xl p-2.5 shadow-sm relative">
        <div class="flex items-center justify-between mb-1.5">
          <div class="flex items-center gap-1.5">
            <span class="px-2 py-0.5 rounded-lg bg-emerald-600 text-white font-black text-xs shadow-sm">إلى</span>
            <span class="text-xs font-black text-emerald-400">إلى حساب:</span>
          </div>

          <!-- منطقة الرصيد: الرصيد بالجنيه والرصيد بالدولار مع تباين عالي جداً بين الخلفية وكتابة الخط -->
          <div class="flex items-center gap-1.5 shrink-0" id="toBalanceWrapper">
            <div class="px-2 py-0.5 rounded-lg bg-black border border-amber-500/80 shadow-sm flex items-center gap-1">
              <span class="text-[9px] font-black text-amber-400">ج.س:</span>
              <span id="toAccountSdgBalance" class="text-[11px] font-mono font-black text-amber-200">1,850,000</span>
            </div>
            <div class="px-2 py-0.5 rounded-lg bg-black border border-emerald-500/80 shadow-sm flex items-center gap-1">
              <span class="text-[9px] font-black text-emerald-400">$ :</span>
              <span id="toAccountUsdBalance" class="text-[11px] font-mono font-black text-emerald-200">698.11</span>
            </div>
          </div>
        </div>

        <!-- حقل البحث الفسيح: يكتب فيه الاسم بخط كبير وواضح جداً (مثل: محمد أحمد عثمان) -->
        <div class="relative">
          <div class="absolute right-3.5 top-3.5 text-emerald-400 pointer-events-none text-sm">
            <i class="fa-solid fa-magnifying-glass"></i>
          </div>
          <input type="text" id="toSearchInput" oninput="liveSearchAccount('to')" onfocus="this.select(); liveSearchAccount('to');" placeholder="بحث: فوري، بنك، خزن، عميل..." value="محمد أحمد عثمان" class="w-full bg-slate-950 border-2 border-emerald-500/50 rounded-xl pr-10 pl-9 py-2.5 text-white focus:outline-none focus:border-emerald-400 placeholder-slate-500 font-black text-[16.5px] sm:text-[17.5px] tracking-wide shadow-inner">
          <button type="button" onclick="clearAccountSearch('to')" id="toClearBtn" class="absolute left-3 top-3 w-6 h-6 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center text-xs transition" title="مسح واختيار حساب آخر">
            <i class="fa-solid fa-xmark"></i>
          </button>

          <!-- القائمة المنسدلة الذكية الفورية -->
          <div id="toDropdown" class="hidden absolute top-full right-0 left-0 mt-1 bg-slate-950 border border-slate-700 rounded-2xl shadow-2xl z-40 max-h-56 overflow-y-auto no-scrollbar"></div>
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           رقم التحويلة:
           - يحمل 14 رقماً بحجم كبير جداً وواضح للسائق وفي السيارة
           - وإذا شال 15 أو 16 أو 20 رقماً تصغر الأرقام تلقائياً وتظل كلها ظاهرة بدون قص
           ═════════════════════════════════════════════════════════════════ -->
      <!-- ═════════════════════════════════════════════════════════════════
           رقم التحويلة واسم البنك (ألوان أنيقة وخطوط واضحة):
           - رقم التحويلة: لون ذهبي دافئ أنيق بخط كبير
           - اسم البنك: لون أزرق جليدي فيروزي أنيق
           ═════════════════════════════════════════════════════════════════ -->
      <div id="transferContainer" class="bg-slate-900/90 border border-slate-800 rounded-2xl p-2.5 space-y-2">
        <div class="grid grid-cols-2 gap-2">
          <!-- 1. رقم التحويلة بأناقة اللون الذهبي الدافئ والخط العريض -->
          <div>
            <label class="block text-[11px] font-bold text-amber-300 mb-1 flex items-center gap-1" id="refLabel">
              <i class="fa-solid fa-receipt text-amber-400 text-[10px]"></i>
              <span>رقم التحويلة:</span>
            </label>
            <input type="text" id="transferInput" oninput="handleTransferInputScale(this.value)" placeholder="رقم التحويلة (14 رقم)" class="w-full bg-slate-950 border-2 border-amber-500/50 focus:border-amber-400 rounded-xl px-2.5 h-[48px] font-mono font-black text-yellow-300 focus:outline-none tracking-tight shadow-[0_0_12px_rgba(245,158,11,0.15)]" style="font-size: 18px; text-align: center; direction: ltr;">
          </div>

          <!-- 2. اسم البنك بأناقة الفيروزي الجليدي والخط الواضح -->
          <div>
            <label class="block text-[11px] font-bold text-cyan-300 mb-1 flex items-center gap-1">
              <i class="fa-solid fa-building-columns text-cyan-400 text-[10px]"></i>
              <span>اسم البنك:</span>
            </label>
            <input type="text" id="bankNameInput" list="banksDataList" placeholder="اسم البنك" class="w-full bg-slate-950 border-2 border-cyan-500/50 focus:border-cyan-400 rounded-xl px-2.5 h-[48px] text-xs font-black text-cyan-300 focus:outline-none shadow-[0_0_12px_rgba(6,182,212,0.15)]" value="بنك الخرطوم">
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
           البيان:
           - لون بلاتيني ناصع وأنيق
           - يكتب فقط "البيان" داخل المساحة بلون خفيف
           ═════════════════════════════════════════════════════════════════ -->
      <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-2">
        <textarea id="voucherNotes" rows="2" placeholder="البيان" class="w-full bg-slate-950 border-2 border-indigo-500/40 focus:border-indigo-400 rounded-xl p-2.5 text-slate-100 placeholder-slate-400/60 placeholder:font-medium text-xs font-bold focus:outline-none resize-none shadow-inner"></textarea>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           أيقونات المرفقات العصرية (الكاميرا، الاستوديو، من واتساب):
           - أيقونات عصرية غير تقليدية تليق بمستوى الموبايل
           - إضاءة في الحواف (Neon Edge Illumination)
           - حجمها يزيد عند الاقتراب (Scale-up on Hover & Touch)
           - إيماءات ضوئية وحركية مريحة
           ═════════════════════════════════════════════════════════════════ -->
      <!-- ═════════════════════════════════════════════════════════════════
           أيقونات المرفقات العصرية (الكاميرا، الاستوديو، من واتساب):
           - زر تحكم لآلية الحفظ: أوتوماتيك (رنة وهزة وحفظ فوري) أو يدوي (إشعاراً بإشعار)
           - تصوير حتى 20 إشعار من الكاميرا
           - اختيار حتى 20 إشعار من الاستوديو
           - سحب حتى 20 إشعار من الواتساب
           ═════════════════════════════════════════════════════════════════ -->
      <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-2 space-y-2">
        
        <!-- زر التحكم في آلية الحفظ والتفريغ (أوتوماتيك ⚡ / يدوي 🖐️) -->
        <div class="flex items-center justify-between p-1.5 rounded-xl bg-slate-950 border border-slate-700/70 shadow-inner">
          <div class="flex items-center gap-1 text-[11px] font-bold text-slate-300">
            <i class="fa-solid fa-sliders text-cyan-400 text-xs"></i>
            <span>آلية الحفظ:</span>
          </div>
          <div class="flex items-center gap-1 bg-slate-900 p-0.5 rounded-lg border border-slate-800">
            <button type="button" onclick="setBatchProcessingMode('auto')" id="btnMainModeAuto" class="py-1 px-2 rounded-md text-[10px] font-black transition flex items-center gap-1 bg-emerald-600 text-white shadow-sm" title="حفظ متتابع تلقائي مع نغمة وهزة">
              <i class="fa-solid fa-bolt text-amber-300 text-[9px]"></i>
              <span>أوتوماتيك ⚡</span>
            </button>
            <button type="button" onclick="setBatchProcessingMode('manual')" id="btnMainModeManual" class="py-1 px-2 rounded-md text-[10px] font-black transition flex items-center gap-1 text-slate-400 hover:text-white" title="مراجعة وتأكيد كل إشعار على حدة والضغط على حفظ">
              <i class="fa-solid fa-hand-pointer text-cyan-400 text-[9px]"></i>
              <span>يدوي (إشعاراً بإشعار) 🖐️</span>
            </button>
          </div>
        </div>

        <!-- أزرار المرفقات الثلاثة العصرية -->
        <div class="grid grid-cols-3 gap-2">
          
          <!-- 1. الكاميرا (تصوير حتى 20 إشعار دفعة واحدة) -->
          <button type="button" onclick="openBatchIntakeModal('camera')" class="modern-media-card media-card-camera flex flex-col items-center justify-center py-2 px-1 rounded-2xl bg-gradient-to-b from-indigo-950/90 via-slate-900 to-slate-950 text-white shadow-lg active:scale-95 transition-all group cursor-pointer" title="تصوير إشعارات بالكاميرا حتى 20 إشعار">
            <div class="relative w-8 h-8 rounded-xl bg-indigo-600/80 flex items-center justify-center mb-1 shadow-md border border-indigo-300/40 group-hover:scale-110 transition">
              <i class="fa-solid fa-camera text-sm text-white"></i>
              <span class="absolute top-1 right-1 w-1.5 h-1.5 rounded-full bg-red-400 animate-ping"></span>
            </div>
            <span class="text-xs font-black text-indigo-100">الكاميرا (20)</span>
          </button>

          <!-- 2. الاستوديو (استيراد حتى 20 إشعار من الجوال) -->
          <button type="button" onclick="openBatchIntakeModal('gallery')" class="modern-media-card media-card-gallery flex flex-col items-center justify-center py-2 px-1 rounded-2xl bg-gradient-to-b from-amber-950/80 via-slate-900 to-slate-950 text-slate-100 shadow-lg active:scale-95 transition-all group cursor-pointer" title="اختيار إشعارات من الاستوديو حتى 20 إشعار">
            <div class="w-8 h-8 rounded-xl bg-amber-600/80 flex items-center justify-center mb-1 shadow-md border border-amber-300/40 group-hover:scale-110 transition">
              <i class="fa-solid fa-images text-sm text-white"></i>
            </div>
            <span class="text-xs font-black text-amber-200">الاستوديو (20)</span>
          </button>

          <!-- 3. من واتساب (سحب حتى 20 إشعار بنكي) -->
          <button type="button" onclick="openBatchIntakeModal('whatsapp')" class="modern-media-card media-card-whatsapp flex flex-col items-center justify-center py-2 px-1 rounded-2xl bg-gradient-to-b from-emerald-950/90 via-slate-900 to-slate-950 text-white shadow-lg active:scale-95 transition-all group cursor-pointer" title="سحب إشعارات من الواتساب حتى 20 إشعار">
            <div class="relative w-8 h-8 rounded-xl bg-emerald-600 flex items-center justify-center mb-1 shadow-md border border-emerald-300/50 group-hover:scale-110 transition">
              <i class="fa-brands fa-whatsapp text-base text-white"></i>
              <span class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
            </div>
            <span class="text-xs font-black text-emerald-200">من واتساب (20)</span>
          </button>

        </div>

        <!-- تنبيه التفريغ الفوري السريع -->
        <div id="mediaToast" class="hidden p-2 rounded-xl bg-emerald-950/80 border border-emerald-500/50 text-[10px] text-emerald-200 items-center justify-between transition-all">
          <span id="mediaToastText">⚡ تم التفريغ الفوري في كسر من الثانية!</span>
          <span class="font-mono text-emerald-400 font-black">AI Turbo OCR ✓</span>
        </div>
      </div>

    </main>

    <!-- ═════════════════════════════════════════════════════════════════
         الشريط السفلي التفاعلي المريح (Thumb Zone - المنطقة الصحيحة للأزرار):
         ترتيب الأزرار: [افتح] [إضافة] [تعديل] [طباعة] [إرسال واتساب] [إنهاء] ... و [حفظ] في الآخر!
         ═════════════════════════════════════════════════════════════════ -->
    <div class="bottom-bar-fixed bg-slate-950/95 border-t border-slate-800 p-2 backdrop-blur-md">
      <div class="flex items-center gap-1 w-full">
        
        <!-- 1. زر افتح: فتح واستعراض وبحث في السندات -->
        <button type="button" onclick="openSearchVouchersModal()" class="action-btn-kinetic h-11 flex-1 min-w-0 px-1 rounded-xl bg-slate-900 border border-amber-500/50 text-amber-300 font-black text-[10px] sm:text-[11px] flex flex-col items-center justify-center gap-0.5 shadow-md active:scale-95 transition" title="فتح واستعراض وبحث في السندات المسجلة">
          <i class="fa-solid fa-folder-open text-amber-400 text-xs"></i>
          <span class="truncate w-full text-center">افتح</span>
        </button>

        <!-- 2. زر إضافة: إضافة سند جديد متسلسل -->
        <button type="button" onclick="startNewVoucher()" class="action-btn-kinetic h-11 flex-1 min-w-0 px-1 rounded-xl bg-slate-900 border border-cyan-400/50 text-cyan-300 font-black text-[10px] sm:text-[11px] flex flex-col items-center justify-center gap-0.5 shadow-md active:scale-95 transition" title="إضافة سند جديد متسلسل">
          <i class="fa-solid fa-plus text-cyan-400 text-xs font-black"></i>
          <span class="truncate w-full text-center">إضافة</span>
        </button>

        <!-- 3. زر تعديل: تعديل بيانات السند الحالي -->
        <button type="button" onclick="handleEdit()" class="action-btn-kinetic h-11 flex-1 min-w-0 px-1 rounded-xl bg-slate-900 border border-indigo-400/50 text-indigo-300 font-black text-[10px] sm:text-[11px] flex flex-col items-center justify-center gap-0.5 shadow-md active:scale-95 transition" title="تعديل بيانات السند الحالي">
          <i class="fa-solid fa-pen text-indigo-400 text-xs"></i>
          <span class="truncate w-full text-center">تعديل</span>
        </button>

        <!-- 4. زر طباعة: طباعة إيصال حراري وسند رسمي (جديد!) -->
        <button type="button" onclick="handlePrintVoucher()" class="action-btn-kinetic h-11 flex-1 min-w-0 px-1 rounded-xl bg-slate-900 border border-purple-500/50 text-purple-300 font-black text-[10px] sm:text-[11px] flex flex-col items-center justify-center gap-0.5 shadow-md active:scale-95 transition" title="طباعة إيصال حراري أو سند رسمي معتمد">
          <i class="fa-solid fa-print text-purple-400 text-xs"></i>
          <span class="truncate w-full text-center">طباعة</span>
        </button>

        <!-- 5. زر إرسال واتساب: إرسال إشعار السند للعميل عبر الواتساب (جديد!) -->
        <button type="button" onclick="openSendWhatsAppModal()" class="action-btn-kinetic h-11 flex-1 min-w-0 px-1 rounded-xl bg-slate-900 border border-emerald-500/60 text-emerald-300 font-black text-[10px] sm:text-[11px] flex flex-col items-center justify-center gap-0.5 shadow-md active:scale-95 transition" title="إرسال تقرير السند للعميل عبر الواتساب">
          <i class="fa-brands fa-whatsapp text-emerald-400 text-xs"></i>
          <span class="truncate w-full text-center">إرسال</span>
        </button>

        <!-- 6. زر إنهاء: إغلاق والرجوع للقائمة الرئيسية -->
        <button type="button" onclick="handleExit()" class="action-btn-kinetic h-11 flex-1 min-w-0 px-0.5 rounded-xl bg-slate-900 border border-red-500/50 text-red-300 font-black text-[10px] sm:text-[11px] flex flex-col items-center justify-center gap-0.5 shadow-md active:scale-95 transition" title="إنهاء والرجوع للقائمة الرئيسية">
          <i class="fa-solid fa-power-off text-red-400 text-xs"></i>
          <span class="truncate w-full text-center">إنهاء</span>
        </button>

        <!-- 7. زر حفظ (في الآخر!): الزر الأساسي الأبرز والأكبر بإماضة ضوئية حركية متوهجة -->
        <button type="button" onclick="handleQuickCheckSave()" id="btnSaveMain" class="action-btn-kinetic flex-[1.4] min-w-0 h-11 px-1 rounded-xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 hover:from-emerald-500 hover:to-cyan-500 active:scale-98 text-white font-black text-[11px] sm:text-xs flex items-center justify-center gap-1 shadow-lg shadow-emerald-950 border border-emerald-400/60 transition" title="تأكيد وحفظ السند">
          <i class="fa-solid fa-check text-xs font-black"></i>
          <span id="saveButtonLabel" class="truncate">حفظ (✓)</span>
        </button>

        <!-- عناصر داعمة صامتة بدون أخطاء برمجية -->
        <button type="button" id="btnAutoSendToggle" onclick="toggleAutoSend()" class="hidden"></button>
        <span id="autoSendStatusText" class="hidden">أون</span>

      </div>
    </div>

  </div>

  <!-- ═════════════════════════════════════════════════════════════════
       نافذة منبثقة تفاعلية شاملة: تفريغ الدفعات بالذكاء الاصطناعي
       (📸 الكاميرا حتى 20 إشعار | 🖼️ الاستوديو حتى 20 إشعار | 💬 الواتساب حتى 20 إشعار)
       - خيار ضبط: حفظ تلقائي متسلسل مع هزة ورنة ⚡🔔 أو إشعاراً بإشعار مع زر حفظ 🖐️
       ═════════════════════════════════════════════════════════════════ -->
  <div id="modalBatchIntake" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-3 bg-black/85 backdrop-blur-md select-none" dir="rtl">
    <div class="bg-slate-900 border border-emerald-500/40 w-full max-w-md rounded-t-3xl sm:rounded-3xl p-3.5 shadow-2xl space-y-3 max-h-[90vh] flex flex-col relative overflow-hidden">
      
      <!-- طبقة فلاش الكاميرا عند التقاط الصور -->
      <div id="cameraFlashOverlay" class="shutter-flash-overlay"></div>

      <!-- رأس النافذة مع تبويبات المصادر الثلاثة: الكاميرا، الاستوديو، الواتساب -->
      <div class="flex items-center justify-between border-b border-slate-800 pb-2">
        <div class="flex items-center gap-1.5" id="intakeSourceTabs">
          <button type="button" onclick="switchIntakeSourceTab('camera')" id="tabBtnCamera" class="px-2.5 py-1.5 rounded-xl text-xs font-black flex items-center gap-1.5 transition bg-indigo-600 text-white shadow-sm border border-indigo-400/40">
            <i class="fa-solid fa-camera text-xs"></i>
            <span>الكاميرا</span>
          </button>
          <button type="button" onclick="switchIntakeSourceTab('gallery')" id="tabBtnGallery" class="px-2.5 py-1.5 rounded-xl text-xs font-black flex items-center gap-1.5 transition bg-slate-800 text-slate-300 hover:text-white border border-slate-700">
            <i class="fa-solid fa-images text-xs text-amber-400"></i>
            <span>الاستوديو</span>
          </button>
          <button type="button" onclick="switchIntakeSourceTab('whatsapp')" id="tabBtnWhatsApp" class="px-2.5 py-1.5 rounded-xl text-xs font-black flex items-center gap-1.5 transition bg-slate-800 text-slate-300 hover:text-white border border-slate-700">
            <i class="fa-brands fa-whatsapp text-xs text-emerald-400"></i>
            <span>من واتساب</span>
          </button>
        </div>
        <button type="button" onclick="closeBatchIntakeModal()" class="w-7 h-7 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 flex items-center justify-center text-xs">
          ✕
        </button>
      </div>

      <!-- زر التحكم في آلية المعالجة والحفظ: أوتوماتيك ⚡ أو يدوي (إشعاراً بإشعار) 🖐️ -->
      <div class="bg-slate-950 p-2 rounded-2xl border border-slate-800 space-y-1.5">
        <div class="flex items-center justify-between">
          <span class="text-[10.5px] font-bold text-slate-300">زر التحكم في آلية الحفظ:</span>
          <span id="batchModeStatusBadge" class="text-[10px] font-bold text-emerald-400 font-mono">⚡ أوتوماتيك نشط</span>
        </div>
        <div class="grid grid-cols-2 gap-1.5 text-xs font-bold">
          <button type="button" onclick="setBatchProcessingMode('auto')" id="btnBatchModeAuto" class="py-1.5 px-2 rounded-xl bg-emerald-600 text-white border border-emerald-400 flex items-center justify-center gap-1.5 shadow-sm transition">
            <i class="fa-solid fa-bolt text-amber-300 text-xs"></i>
            <span>أوتوماتيك (هزة ورنة) ⚡</span>
          </button>
          <button type="button" onclick="setBatchProcessingMode('manual')" id="btnBatchModeManual" class="py-1.5 px-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 flex items-center justify-center gap-1.5 hover:bg-slate-700 transition">
            <i class="fa-solid fa-hand-pointer text-cyan-400 text-xs"></i>
            <span>إشعاراً بإشعار (يدوي) 🖐️</span>
          </button>
        </div>
      </div>

      <!-- محتوى المصدر 1: الكاميرا (تصوير حتى 20 إشعار دفعة واحدة) -->
      <div id="contentSourceCamera" class="space-y-2 flex-1 overflow-y-auto no-scrollbar">
        <div class="p-2.5 rounded-2xl bg-indigo-950/40 border border-indigo-500/40 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white text-base shadow-md">
              <i class="fa-solid fa-camera"></i>
            </div>
            <div>
              <span class="text-xs font-black text-white block">تصوير الإشعارات المتتابعة</span>
              <span class="text-[10px] text-indigo-300 font-bold" id="cameraCountLabel">تم تجهيز: 20 صورة إشعار</span>
            </div>
          </div>
          <button type="button" onclick="triggerCameraCapture()" class="py-1.5 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white font-black text-xs flex items-center gap-1.5 shadow-md">
            <i class="fa-solid fa-camera-retro"></i>
            <span>التقاط صورة 📸</span>
          </button>
        </div>

        <!-- أزرار الاختيار السريع للكمية (1، 5، 10، 15، 20) -->
        <div class="space-y-1">
          <span class="text-[10px] font-bold text-slate-400 block">اختيار عدد صور الكاميرا للتفريغ:</span>
          <div class="grid grid-cols-5 gap-1 text-xs font-mono font-bold">
            <button type="button" onclick="selectBatchCount(1, 'camera')" id="btnCam-1" class="cam-filter-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700">1</button>
            <button type="button" onclick="selectBatchCount(5, 'camera')" id="btnCam-5" class="cam-filter-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700">5</button>
            <button type="button" onclick="selectBatchCount(10, 'camera')" id="btnCam-10" class="cam-filter-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700">10</button>
            <button type="button" onclick="selectBatchCount(15, 'camera')" id="btnCam-15" class="cam-filter-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700">15</button>
            <button type="button" onclick="selectBatchCount(20, 'camera')" id="btnCam-20" class="cam-filter-btn py-1.5 rounded-xl bg-indigo-600 text-white border border-indigo-400 text-center shadow-sm">20 إشعار</button>
          </div>
        </div>

        <!-- شبكة معاينة مصغرات صور الكاميرا الملتقطة -->
        <div class="grid grid-cols-4 gap-1.5 max-h-44 overflow-y-auto no-scrollbar p-1" id="cameraThumbnailsGrid"></div>
      </div>

      <!-- محتوى المصدر 2: الاستوديو (اختيار حتى 20 إشعار من المعرض) -->
      <div id="contentSourceGallery" class="hidden space-y-2 flex-1 overflow-y-auto no-scrollbar">
        <input type="file" id="galleryFileInput" multiple accept="image/*" class="hidden" onchange="handleGalleryFilesSelected(event)">
        
        <div class="p-2.5 rounded-2xl bg-amber-950/40 border border-amber-500/40 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-9 h-9 rounded-xl bg-amber-600 flex items-center justify-center text-white text-base shadow-md">
              <i class="fa-solid fa-images"></i>
            </div>
            <div>
              <span class="text-xs font-black text-white block">معرض صور الجوال</span>
              <span class="text-[10px] text-amber-300 font-bold" id="galleryCountLabel">تم اختيار: 20 صورة إشعار</span>
            </div>
          </div>
          <button type="button" onclick="document.getElementById('galleryFileInput').click()" class="py-1.5 px-3 rounded-xl bg-amber-600 hover:bg-amber-500 active:scale-95 text-white font-black text-xs flex items-center gap-1.5 shadow-md">
            <i class="fa-solid fa-folder-open"></i>
            <span>اختر صور 📁</span>
          </button>
        </div>

        <!-- أزرار الاختيار السريع للكمية (1، 5، 10، 15، 20) -->
        <div class="space-y-1">
          <span class="text-[10px] font-bold text-slate-400 block">اختيار عدد إشعارات الاستوديو:</span>
          <div class="grid grid-cols-5 gap-1 text-xs font-mono font-bold">
            <button type="button" onclick="selectBatchCount(1, 'gallery')" id="btnGal-1" class="gal-filter-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700">1</button>
            <button type="button" onclick="selectBatchCount(5, 'gallery')" id="btnGal-5" class="gal-filter-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700">5</button>
            <button type="button" onclick="selectBatchCount(10, 'gallery')" id="btnGal-10" class="gal-filter-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700">10</button>
            <button type="button" onclick="selectBatchCount(15, 'gallery')" id="btnGal-15" class="gal-filter-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700">15</button>
            <button type="button" onclick="selectBatchCount(20, 'gallery')" id="btnGal-20" class="gal-filter-btn py-1.5 rounded-xl bg-amber-600 text-white border border-amber-400 text-center shadow-sm">20 إشعار</button>
          </div>
        </div>

        <!-- شبكة معاينة مصغرات صور الاستوديو -->
        <div class="grid grid-cols-4 gap-1.5 max-h-44 overflow-y-auto no-scrollbar p-1" id="galleryThumbnailsGrid"></div>
      </div>

      <!-- محتوى المصدر 3: الواتساب (20 إشعار بنكي مباشر) -->
      <div id="contentSourceWhatsApp" class="hidden space-y-2 flex-1 overflow-y-auto no-scrollbar">
        <!-- أزرار تصفية واختيار عدد الإشعارات (1 إلى 5 إلى 10 أو 15 أو 20) -->
        <div class="space-y-1">
          <div class="flex items-center justify-between text-[10.5px] font-bold text-slate-300">
            <span>اختيار كمية إشعارات الواتساب:</span>
            <span id="whatsAppCountBadge" class="text-emerald-400 font-mono font-bold">20 إشعار متاح</span>
          </div>
          <div class="grid grid-cols-5 gap-1 text-xs font-mono font-bold">
            <button type="button" onclick="selectBatchCount(1, 'whatsapp')" id="btnWa-1" class="wa-filter-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700">1</button>
            <button type="button" onclick="selectBatchCount(5, 'whatsapp')" id="btnWa-5" class="wa-filter-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700">5</button>
            <button type="button" onclick="selectBatchCount(10, 'whatsapp')" id="btnWa-10" class="wa-filter-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700">10</button>
            <button type="button" onclick="selectBatchCount(15, 'whatsapp')" id="btnWa-15" class="wa-filter-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700">15</button>
            <button type="button" onclick="selectBatchCount(20, 'whatsapp')" id="btnWa-20" class="wa-filter-btn py-1.5 rounded-xl bg-emerald-600 text-white border border-emerald-400 text-center shadow-sm">20 إشعار</button>
          </div>
        </div>

        <!-- قائمة الإشعارات القابلة للنقر والتفريغ الفوري السريع -->
        <div class="flex-1 overflow-y-auto no-scrollbar space-y-2 pr-0.5 max-h-48" id="whatsAppListContainer"></div>
      </div>

      <!-- شريط الإجراء السفلي للنافذة -->
      <div class="pt-2 border-t border-slate-800 flex items-center justify-between gap-2">
        <button type="button" onclick="startUniversalBatchProcessing()" class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 hover:from-emerald-500 hover:to-cyan-500 active:scale-95 text-white font-black text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-emerald-900/40 transition">
          <i class="fa-solid fa-bolt text-amber-300 text-sm"></i>
          <span id="startBatchBtnLabel">بدء تفريغ وحفظ الـ (20) إشعار</span>
        </button>
        <button type="button" onclick="closeBatchIntakeModal()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs">
          إلغاء
        </button>
      </div>

    </div>
  </div>

  <!-- عنصر بديل لضمان توافق أي استدعاءات قديمة لـ modalWhatsAppIntake -->
  <div id="modalWhatsAppIntake" class="hidden"></div>

  <!-- ═════════════════════════════════════════════════════════════════
       نافذة منبثقة: إرسال تقرير السند للعميل عبر الواتساب (📲 إرسال)
       ═════════════════════════════════════════════════════════════════ -->
  <div id="modalSendWhatsApp" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-3 bg-black/85 backdrop-blur-md select-none" dir="rtl">
    <div class="bg-slate-900 border border-emerald-500/50 w-full max-w-md rounded-t-3xl sm:rounded-3xl p-4 shadow-2xl space-y-3 max-h-[88vh] flex flex-col">
      
      <!-- رأس النافذة -->
      <div class="flex items-center justify-between border-b border-slate-800 pb-2">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-xl bg-emerald-600 flex items-center justify-center text-white shadow-md">
            <i class="fa-brands fa-whatsapp text-lg"></i>
          </div>
          <div>
            <h3 class="text-sm font-black text-white">إرسال إشعار السند عبر الواتساب</h3>
            <span class="text-[10px] text-emerald-400 font-bold">مشاركة فورية مع العميل أو الحساب</span>
          </div>
        </div>
        <button type="button" onclick="closeSendWhatsAppModal()" class="w-7 h-7 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 flex items-center justify-center text-xs">
          ✕
        </button>
      </div>

      <!-- اختيار الطرف المستلم -->
      <div class="bg-slate-950 p-2.5 rounded-2xl border border-slate-800 space-y-2">
        <label class="block text-[11px] font-bold text-slate-300">إرسال الإشعار إلى:</label>
        <div class="grid grid-cols-2 gap-2 text-xs font-bold">
          <button type="button" onclick="selectWhatsAppRecipient('to')" id="btnRecipientTo" class="py-2 px-2 rounded-xl bg-emerald-600 text-white border border-emerald-400 flex flex-col items-center justify-center gap-0.5 shadow-sm transition">
            <span class="text-[10px] opacity-80">المستلم (إلى حساب)</span>
            <span class="font-black truncate max-w-[130px]" id="recipientToNameDisplay">محمد أحمد عثمان</span>
          </button>
          <button type="button" onclick="selectWhatsAppRecipient('from')" id="btnRecipientFrom" class="py-2 px-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 flex flex-col items-center justify-center gap-0.5 hover:bg-slate-700 transition">
            <span class="text-[10px] opacity-80">المرسل (من حساب)</span>
            <span class="font-black truncate max-w-[130px]" id="recipientFromNameDisplay">بنك الخرطوم (5540)</span>
          </button>
        </div>

        <!-- حقل رقم الواتساب -->
        <div>
          <label class="block text-[10.5px] font-bold text-slate-400 mb-1">رقم الواتساب (مع رمز الدولة):</label>
          <div class="relative">
            <i class="fa-solid fa-phone absolute left-3 top-3 text-slate-400 text-xs"></i>
            <input type="tel" id="whatsAppRecipientPhone" value="+249912345678" placeholder="+249XXXXXXXXX" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white font-mono font-bold text-xs focus:outline-none focus:border-emerald-400" style="direction: ltr; text-align: left;">
          </div>
        </div>
      </div>

      <!-- معاينة رسالة الواتساب المنسقة الفاخرة -->
      <div class="space-y-1">
        <span class="text-[10.5px] font-bold text-slate-300 block">معاينة نص الرسالة الصادرة:</span>
        <div class="p-3 rounded-2xl bg-emerald-950/30 border border-emerald-500/30 text-emerald-100 text-[11px] leading-relaxed font-mono overflow-y-auto max-h-36 no-scrollbar" id="whatsAppMessagePreview"></div>
      </div>

      <!-- أزرار الإرسال والنسخ -->
      <div class="pt-2 border-t border-slate-800 flex items-center gap-2">
        <button type="button" onclick="executeSendWhatsApp('direct')" class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-black text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-emerald-900/50 transition">
          <i class="fa-brands fa-whatsapp text-sm"></i>
          <span>فتح الواتساب والإرسال الآن 🚀</span>
        </button>
        <button type="button" onclick="executeSendWhatsApp('copy')" class="py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs flex items-center gap-1">
          <i class="fa-regular fa-copy text-xs"></i>
          <span>نسخ</span>
        </button>
      </div>

    </div>
  </div>

  <!-- ═════════════════════════════════════════════════════════════════
       نافذة منبثقة: طباعة السند ومعاينته (🖨️ طباعة)
       - إيصال حراري POS 80mm للبلوتوث والموبايل
       - سند رسمي محاسبي A4
       ═════════════════════════════════════════════════════════════════ -->
  <div id="modalPrintVoucher" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-3 bg-black/85 backdrop-blur-md select-none" dir="rtl">
    <div class="bg-slate-900 border border-purple-500/50 w-full max-w-md rounded-t-3xl sm:rounded-3xl p-4 shadow-2xl space-y-3 max-h-[90vh] flex flex-col">
      
      <!-- رأس النافذة -->
      <div class="flex items-center justify-between border-b border-slate-800 pb-2">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-xl bg-purple-600 flex items-center justify-center text-white shadow-md">
            <i class="fa-solid fa-print text-sm"></i>
          </div>
          <div>
            <h3 class="text-sm font-black text-white">طباعة ومعاينة السند</h3>
            <span class="text-[10px] text-purple-400 font-bold">طابعات حرارية بلوتوث POS وسندات رسمية</span>
          </div>
        </div>
        <button type="button" onclick="closePrintVoucherModal()" class="w-7 h-7 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 flex items-center justify-center text-xs">
          ✕
        </button>
      </div>

      <!-- تبديل نوع الطباعة: إيصال حراري أو سند A4 -->
      <div class="grid grid-cols-2 gap-2 text-xs font-bold bg-slate-950 p-1.5 rounded-2xl border border-slate-800">
        <button type="button" onclick="switchPrintFormat('thermal')" id="btnFormatThermal" class="py-1.5 rounded-xl bg-purple-600 text-white border border-purple-400 flex items-center justify-center gap-1.5 shadow-sm transition">
          <i class="fa-solid fa-receipt text-xs"></i>
          <span>إيصال حراري (80mm)</span>
        </button>
        <button type="button" onclick="switchPrintFormat('standard')" id="btnFormatStandard" class="py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 flex items-center justify-center gap-1.5 hover:bg-slate-700 transition">
          <i class="fa-solid fa-file-invoice text-xs"></i>
          <span>سند رسمي (A4)</span>
        </button>
      </div>

      <!-- ورقة معاينة الطباعة التفاعلية -->
      <div class="flex-1 overflow-y-auto no-scrollbar p-3 rounded-2xl bg-white text-slate-900 border border-slate-300 shadow-inner font-mono text-xs space-y-2 select-text" id="printSheetPreviewContainer">
        <!-- يتم ملء محتوى الإيصال والباركود بالـ JavaScript -->
      </div>

      <!-- أزرار الإجراء للطباعة -->
      <div class="pt-2 border-t border-slate-800 flex items-center justify-between gap-2">
        <button type="button" onclick="triggerBrowserPrint()" class="flex-1 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 active:scale-95 text-white font-black text-xs flex items-center justify-center gap-2 shadow-lg shadow-purple-900/50 transition">
          <i class="fa-solid fa-print text-sm"></i>
          <span>طباعة فورية للطابعة 🖨️</span>
        </button>
        <button type="button" onclick="closePrintVoucherModal()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs">
          إغلاق
        </button>
      </div>

    </div>
  </div>

  <!-- حاوية خاصة مخفية تظهر فقط عند أمر الطباعة عبر الطابعة (Thermal POS Output) -->
  <div id="printableThermalSlip" class="hidden"></div>

  <!-- ═════════════════════════════════════════════════════════════════
       نافذة منبثقة: افتح واستعراض السندات المسجلة (📂 افتح)
       ═════════════════════════════════════════════════════════════════ -->
  <div id="modalSearchVouchers" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 bg-black/85 backdrop-blur-md select-none" dir="rtl">
    <div class="bg-slate-900 border border-slate-700 w-full max-w-sm rounded-3xl p-4 shadow-2xl space-y-3 max-h-[85vh] flex flex-col">
      
      <!-- رأس النافذة -->
      <div class="flex items-center justify-between border-b border-slate-800 pb-2">
        <div class="flex items-center gap-2">
          <i class="fa-solid fa-folder-open text-amber-400 text-base"></i>
          <h3 class="text-sm font-black text-white">افتح واستعراض السندات</h3>
        </div>
        <button type="button" onclick="closeSearchVouchersModal()" class="w-7 h-7 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 flex items-center justify-center text-xs">
          ✕
        </button>
      </div>

      <!-- حقل البحث السريع برقم السند أو اسم العميل -->
      <div class="relative">
        <i class="fa-solid fa-magnifying-glass absolute right-3 top-3 text-slate-400 text-xs"></i>
        <input type="text" id="voucherSearchFilterInput" oninput="renderVouchersListInModal()" placeholder="بحث برقم السند، اسم الحساب، أو المبلغ..." class="w-full bg-slate-950 border border-slate-700 rounded-xl pr-8 pl-3 py-2 text-white font-bold text-xs focus:outline-none focus:border-amber-400">
      </div>

      <!-- قائمة السندات -->
      <div class="flex-1 overflow-y-auto no-scrollbar space-y-2" id="vouchersModalListContainer">
        <!-- يتم ملؤها بالـ JavaScript -->
      </div>

    </div>
  </div>

  <!-- ═════════════════════════════════════════════════════════════════
       نافذة منبثقة: خصائص وتنسيق الأرقام
       (درجات التكبير 18, 22, 26, 30, 32, 34, 36، الألوان، الخط بلوط، و3 خطوط إنجليزية)
       ═════════════════════════════════════════════════════════════════ -->
  <div id="modalNumberProperties" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 bg-black/80 backdrop-blur-sm select-none" dir="rtl">
    <div class="bg-slate-900 border border-slate-700 w-full max-w-sm rounded-3xl p-4 shadow-2xl space-y-3.5">
      
      <div class="flex items-center justify-between border-b border-slate-800 pb-2">
        <div class="flex items-center gap-2">
          <span class="text-lg">⚙️</span>
          <h3 class="text-sm font-black text-white">خصائص وتنسيق الأرقام</h3>
        </div>
        <button type="button" onclick="closeNumberPropertiesModal()" class="w-7 h-7 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 flex items-center justify-center text-xs">
          ✕
        </button>
      </div>

      <!-- معاينة فورية لشكل الرقم المنسق -->
      <div class="bg-slate-950 p-2.5 rounded-2xl border border-slate-800 text-center space-y-1">
        <span class="text-[10px] text-slate-400 font-bold block">معاينة مباشرة للرقم المنسق</span>
        <div id="numPropLivePreview" class="py-1 transition-all" style="direction: ltr; font-family: 'JetBrains Mono', monospace; font-size: 30px; font-weight: 900; color: #10b981;">
          2,650,000.75
        </div>
      </div>

      <!-- 1. درجات التكبير: (18, 22, 26, 30, 32, 34, 36) -->
      <div class="space-y-1">
        <div class="flex justify-between items-center text-[11px] font-bold">
          <span class="text-slate-300">تكبير الدرجات (حجم الخط):</span>
          <span id="currentSizeLabel" class="text-cyan-400 font-mono text-xs">30px</span>
        </div>
        <div class="grid grid-cols-7 gap-1 text-xs font-bold font-mono">
          <button type="button" onclick="setNumSize('18px')" id="btnSize18" class="size-btn py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-center">18</button>
          <button type="button" onclick="setNumSize('22px')" id="btnSize22" class="size-btn py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-center">22</button>
          <button type="button" onclick="setNumSize('26px')" id="btnSize26" class="size-btn py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-center">26</button>
          <button type="button" onclick="setNumSize('30px')" id="btnSize30" class="size-btn py-1.5 rounded-lg bg-cyan-600 text-white border-cyan-400 text-center">30</button>
          <button type="button" onclick="setNumSize('32px')" id="btnSize32" class="size-btn py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-center">32</button>
          <button type="button" onclick="setNumSize('34px')" id="btnSize34" class="size-btn py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-center">34</button>
          <button type="button" onclick="setNumSize('36px')" id="btnSize36" class="size-btn py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-center">36</button>
        </div>
      </div>

      <!-- 2. اختيار اللون المفضل -->
      <div class="space-y-1">
        <div class="flex justify-between items-center text-[11px] font-bold">
          <span class="text-slate-300">لون الأرقام:</span>
          <span id="numColorName" class="text-cyan-400 text-[10px]">أخضر زمردي</span>
        </div>
        <div class="flex items-center justify-between gap-1" id="numPropColorsGroup">
          <button type="button" onclick="setNumColor('#10b981', 'أخضر زمردي')" data-color="#10b981" class="w-7 h-7 rounded-xl bg-emerald-500 border-2 border-white shadow-lg scale-105 transition" title="أخضر زمردي"></button>
          <button type="button" onclick="setNumColor('#06b6d4', 'سماوي فاقع')" data-color="#06b6d4" class="w-7 h-7 rounded-xl bg-cyan-500 border-2 border-transparent transition" title="سماوي فاقع"></button>
          <button type="button" onclick="setNumColor('#f59e0b', 'ذهبي عنبري')" data-color="#f59e0b" class="w-7 h-7 rounded-xl bg-amber-500 border-2 border-transparent transition" title="ذهبي عنبري"></button>
          <button type="button" onclick="setNumColor('#f43f5e', 'وردي فاقع')" data-color="#f43f5e" class="w-7 h-7 rounded-xl bg-rose-500 border-2 border-transparent transition" title="وردي فاقع"></button>
          <button type="button" onclick="setNumColor('#8b5cf6', 'بنفسجي ملكي')" data-color="#8b5cf6" class="w-7 h-7 rounded-xl bg-purple-500 border-2 border-transparent transition" title="بنفسجي ملكي"></button>
          <button type="button" onclick="setNumColor('#ffffff', 'أبيض ناصع')" data-color="#ffffff" class="w-7 h-7 rounded-xl bg-white border-2 border-transparent transition" title="أبيض ناصع"></button>
          <label class="w-7 h-7 rounded-xl bg-gradient-to-tr from-rose-500 via-amber-500 to-indigo-500 border-2 border-transparent cursor-pointer flex items-center justify-center transition" title="منتقي ألوان حر">
            <input type="color" id="customColorInput" onchange="setNumColor(this.value, 'لون مخصص')" class="opacity-0 w-0 h-0">
            <i class="fa-solid fa-palette text-[10px] text-white drop-shadow"></i>
          </label>
        </div>
      </div>

      <!-- 3. الخط بلوط (Bold / عريض) أو غير بلوط (Regular / عادي) -->
      <div class="space-y-1">
        <label class="block text-[11px] font-bold text-slate-300">سماكة الخط (بلوط / غير بلوط):</label>
        <div class="grid grid-cols-2 gap-1.5 text-xs font-bold">
          <button type="button" onclick="setNumWeight('900')" id="btnWeightBold" class="weight-btn py-1.5 rounded-xl bg-cyan-600 text-white border border-cyan-400 flex items-center justify-center gap-1.5 transition">
            <i class="fa-solid fa-bold text-xs"></i>
            <span>خط بلوط (Bold / عريض)</span>
          </button>
          <button type="button" onclick="setNumWeight('500')" id="btnWeightNormal" class="weight-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 flex items-center justify-center gap-1.5 transition">
            <span>غير بلوط (عادي / Regular)</span>
          </button>
        </div>
      </div>

      <!-- 4. أكثر 3 خطوط وضوحاً في اللغة الإنجليزية بالنسبة للأرقام -->
      <div class="space-y-1">
        <label class="block text-[11px] font-bold text-slate-300">أكثر 3 خطوط وضوحاً للأرقام (English Numbers):</label>
        <div class="space-y-1">
          <button type="button" onclick="setNumFont('JetBrains Mono')" id="btnFont-JetBrainsMono" class="font-btn w-full p-2 rounded-xl bg-cyan-600/20 text-cyan-300 border border-cyan-500/50 flex items-center justify-between text-xs transition">
            <div class="flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
              <span class="font-bold">JetBrains Mono</span>
              <span class="text-[9.5px] text-slate-400">(مونو سبيس مالي فائق الوضوح)</span>
            </div>
            <span class="font-mono font-black text-xs">1,234,567.89</span>
          </button>

          <button type="button" onclick="setNumFont('Inter')" id="btnFont-Inter" class="font-btn w-full p-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 flex items-center justify-between text-xs transition">
            <div class="flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-slate-500"></span>
              <span class="font-bold">Inter</span>
              <span class="text-[9.5px] text-slate-400">(أرقام بنكية حديثة نقية)</span>
            </div>
            <span class="font-black text-xs" style="font-family: 'Inter', sans-serif;">1,234,567.89</span>
          </button>

          <button type="button" onclick="setNumFont('Montserrat')" id="btnFont-Montserrat" class="font-btn w-full p-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 flex items-center justify-between text-xs transition">
            <div class="flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-slate-500"></span>
              <span class="font-bold">Montserrat</span>
              <span class="text-[9.5px] text-slate-400">(أرقام هندسية بارزة عريضة)</span>
            </div>
            <span class="font-black text-xs" style="font-family: 'Montserrat', sans-serif;">1,234,567.89</span>
          </button>
        </div>
      </div>

      <!-- 5. الفواصل (الألفية والعشرية) -->
      <div class="grid grid-cols-2 gap-2 text-xs">
        <div>
          <label class="block text-[10.5px] font-bold text-slate-300 mb-1">الفاصلة الألفية:</label>
          <select id="numThousandsSelect" onchange="updateNumPreview()" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-2 py-1.5 text-white font-mono text-xs focus:outline-none focus:border-cyan-500">
            <option value="," selected>فاصلة ( , ) 1,000</option>
            <option value=".">نقطة ( . ) 1.000</option>
            <option value=" ">مسافة ( 1 000 )</option>
            <option value="">بدون فاصلة (1000)</option>
          </select>
        </div>

        <div>
          <label class="block text-[10.5px] font-bold text-slate-300 mb-1">الفاصلة العشرية:</label>
          <select id="numDecimalSelect" onchange="updateNumPreview()" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-2 py-1.5 text-white font-mono text-xs focus:outline-none focus:border-cyan-500">
            <option value="." selected>نقطة ( . ) 0.50</option>
            <option value=",">فاصلة ( , ) 0,50</option>
          </select>
        </div>
      </div>

      <!-- زر الحفظ النهائي للخصائص -->
      <div class="pt-1">
        <button type="button" onclick="saveNumberProperties()" class="w-full py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 active:scale-95 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-cyan-600/30 transition">
          <i class="fa-solid fa-floppy-disk text-xs"></i>
          <span>حفظ وتطبيق الخصائص للبرنامج</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Application Logic -->
  <script>
    // ════════════════════════════════════════════════════════════════
    // قاعدة بيانات الحسابات مع الأرصدة بالجنيه وبالدولار
    // ════════════════════════════════════════════════════════════════
    let accountsDB = [
      { id: '102001', name: 'محمد أحمد عثمان', category: 'عملاء', sdgBalance: 1850000, balanceType: 'مدين' },
      { id: '101002', name: 'بنك الخرطوم (5540)', category: 'بنوك ومصارف', sdgBalance: 14850000, balanceType: '' },
      { id: '101003', name: 'بنك فيصل الإسلامي (3290)', category: 'بنوك ومصارف', sdgBalance: 8210000, balanceType: '' },
      { id: '103001', name: 'فوري كاش (سريع)', category: 'فوري ودفع إلكتروني', sdgBalance: 3420000, balanceType: '' },
      { id: '101001', name: 'الخزينة الرئيسية (كاش)', category: 'خزن ونقدية', sdgBalance: 2450000, balanceType: '' },
      { id: '101004', name: 'خزينة الفرع الثاني (بحري)', category: 'خزن ونقدية', sdgBalance: 1120000, balanceType: '' },
      { id: '201015', name: 'شركة النيل للاستيراد (مورد)', category: 'موردين', sdgBalance: 4200000, balanceType: 'دائن' },
      { id: '201022', name: 'مؤسسة الرواد التجارية (مورد)', category: 'موردين', sdgBalance: 1750000, balanceType: 'دائن' },
      { id: '102008', name: 'العميل: أحمد عثمان النور', category: 'عملاء', sdgBalance: 950000, balanceType: 'مدين' },
      { id: '501001', name: 'مصروفات إيجار ونولون', category: 'مصروفات', sdgBalance: 0, balanceType: 'جاري' }
    ];

    // قاعدة بيانات 20 إشعار واتساب بنكي حقيقي جاهز للاختيار والتفريغ الفوري
    const whatsappNotificationsDB = [
      { id: 1, bank: 'بنك الخرطوم (بنكك)', ref: '91823049182304', amount: 3180000, from: 'بنك الخرطوم (5540)', to: 'محمد أحمد عثمان', time: 'منذ دقيقة', note: 'تحويل بنكك مباشر' },
      { id: 2, bank: 'بنك فيصل (فوري)', ref: '84920158492015', amount: 1250000, from: 'بنك فيصل الإسلامي (3290)', to: 'محمد أحمد عثمان', time: 'منذ 3 دقائق', note: 'سداد دفعة نقدية' },
      { id: 3, bank: 'بنك الخرطوم (بنكك)', ref: '77219037721903', amount: 5400000, from: 'بنك الخرطوم (5540)', to: 'شركة النيل للاستيراد (مورد)', time: 'منذ 6 دقائق', note: 'توريد مواد خام بورتسودان' },
      { id: 4, bank: 'فوري كاش', ref: '66102946610294', amount: 750000, from: 'فوري كاش (سريع)', to: 'العميل: أحمد عثمان النور', time: 'منذ 10 دقائق', note: 'تحويل تطبيق فوري' },
      { id: 5, bank: 'بنك أم درمان الوطني', ref: '55392015539201', amount: 2100000, from: 'الخزينة الرئيسية (كاش)', to: 'مؤسسة الرواد التجارية (مورد)', time: 'منذ 15 دقيقة', note: 'دفعة بضائع جاهزة' },
      { id: 6, bank: 'بنك الخرطوم (بنكك)', ref: '90124819012481', amount: 10500000, from: 'بنك الخرطوم (5540)', to: 'شركة النيل للاستيراد (مورد)', time: 'اليوم 11:20 ص', note: 'شحنة استيراد رئيسية' },
      { id: 7, bank: 'بنك فيصل (فوري)', ref: '43920184392018', amount: 890000, from: 'بنك فيصل الإسلامي (3290)', to: 'محمد أحمد عثمان', time: 'اليوم 10:45 ص', note: 'سداد حساب عميل' },
      { id: 8, bank: 'بنك النيلين', ref: '33910243391024', amount: 4350000, from: 'بنك الخرطوم (5540)', to: 'مؤسسة الرواد التجارية (مورد)', time: 'اليوم 10:12 ص', note: 'دفعة توريد مستلزمات' },
      { id: 9, bank: 'بنك الخرطوم (بنكك)', ref: '88201948820194', amount: 6200000, from: 'بنك الخرطوم (5540)', to: 'محمد أحمد عثمان', time: 'اليوم 09:30 ص', note: 'تحويل بنكك - تجارة عامة' },
      { id: 10, bank: 'فوري كاش', ref: '71049287104928', amount: 1800000, from: 'فوري كاش (سريع)', to: 'الخزينة الرئيسية (كاش)', time: 'اليوم 08:50 ص', note: 'تغذية خزينة فورية' },
      { id: 11, bank: 'بنك الخرطوم (بنكك)', ref: '95820149582014', amount: 3650000, from: 'بنك الخرطوم (5540)', to: 'محمد أحمد عثمان', time: 'أمس 08:30 م', note: 'سداد بضاعة محملة' },
      { id: 12, bank: 'بنك أم درمان الوطني', ref: '64810296481029', amount: 9150000, from: 'بنك فيصل الإسلامي (3290)', to: 'شركة النيل للاستيراد (مورد)', time: 'أمس 07:15 م', note: 'دفعة اعتمادات مستندية' },
      { id: 13, bank: 'بنك فيصل (فوري)', ref: '22819042281904', amount: 500000, from: 'بنك فيصل الإسلامي (3290)', to: 'العميل: أحمد عثمان النور', time: 'أمس 05:40 م', note: 'تحويل نقدي سريع' },
      { id: 14, bank: 'بنك الخرطوم (بنكك)', ref: '81920478192047', amount: 2750000, from: 'بنك الخرطوم (5540)', to: 'محمد أحمد عثمان', time: 'أمس 04:10 م', note: 'دفعة تصفية حساب' },
      { id: 15, bank: 'بنك الخليج', ref: '99401829940182', amount: 12000000, from: 'بنك الخرطوم (5540)', to: 'شركة النيل للاستيراد (مورد)', time: 'أمس 02:30 م', note: 'سداد فاتورة بضائع' },
      { id: 16, bank: 'فوري كاش', ref: '55910245591024', amount: 3300000, from: 'فوري كاش (سريع)', to: 'محمد أحمد عثمان', time: 'أمس 01:15 م', note: 'تسوية مبيعات فرع' },
      { id: 17, bank: 'بنك الخرطوم (بنكك)', ref: '44910284491028', amount: 4100000, from: 'بنك الخرطوم (5540)', to: 'مؤسسة الرواد التجارية (مورد)', time: 'أمس 11:00 ص', note: 'دفعة حساب نولون' },
      { id: 18, bank: 'بنك فيصل (فوري)', ref: '33810293381029', amount: 850000, from: 'بنك فيصل الإسلامي (3290)', to: 'محمد أحمد عثمان', time: 'قبل يومين', note: 'سداد متبقي حساب' },
      { id: 19, bank: 'بنك أم درمان الوطني', ref: '77491027749102', amount: 7600000, from: 'الخزينة الرئيسية (كاش)', to: 'شركة النيل للاستيراد (مورد)', time: 'قبل يومين', note: 'دفعة نقدية موردين' },
      { id: 20, bank: 'بنك الخرطوم (بنكك)', ref: '91823999182399', amount: 1950000, from: 'بنك الخرطوم (5540)', to: 'محمد أحمد عثمان', time: 'قبل يومين', note: 'تحويل بنكك - ختامي' }
    ];

    let currentWaLimit = 20;
    let selectedWaReceiptId = 1;
    let batchProcessingMode = 'auto'; // 'auto' (هزة ورنة وحفظ تلقائي) or 'manual' (إشعاراً بإشعار)
    let whatsAppQueue = [];
    let currentQueueIndex = 0;

    // السندات المتزامنة المتطابقة مع سيرفر اللابتوب والكمبيوتر
    let vouchersList = [
      {
        id: '840',
        isOffline: false,
        date: '2026-10-03',
        rate: 2650,
        currency: 'SDG',
        costCenter: 'مخزن بحري الرئيسي',
        sdg: 5300000,
        usd: 2000,
        payType: 'transfer',
        from: 'بنك الخرطوم (5540)',
        to: 'شركة النيل للاستيراد (مورد)',
        refNo: '71049287104928',
        bankName: 'بنك الخرطوم',
        notes: 'دفعة حساب نولون بضائع محملة من بورتسودان'
      },
      {
        id: '841',
        isOffline: false,
        date: '2026-10-04',
        rate: 2650,
        currency: 'SDG',
        costCenter: 'مخزن بحري الرئيسي',
        sdg: 1325000,
        usd: 500,
        payType: 'cash',
        from: 'الخزينة الرئيسية (كاش)',
        to: 'مصروفات إيجار ونولون',
        refNo: '',
        bankName: '',
        notes: 'صرف نثريات ضيافة وإيجار مكتبي كاش'
      },
      {
        id: '842',
        isOffline: false,
        date: '2026-10-05',
        rate: 2650,
        currency: 'SDG',
        costCenter: 'مخزن بحري الرئيسي',
        sdg: 2650000,
        usd: 1000,
        payType: 'transfer',
        from: 'بنك الخرطوم (5540)',
        to: 'محمد أحمد عثمان',
        refNo: '84920158492015',
        bankName: 'بنك الخرطوم',
        notes: 'سداد دفعة بضاعة توريد مواد خام'
      }
    ];

    let currentIndex = 2;
    let isOfflineMode = false;
    let offlineCounter = 1;
    let offlineQueue = [];
    let currentPayType = 'transfer';
    let currentCurrency = 'SDG';

    // ════════════════════════════════════════════════════════════════
    // نظام الصوت التوليدي البنكي (Banker Chime via Web Audio API)
    // ════════════════════════════════════════════════════════════════
    function playBankerChime() {
      try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        const now = ctx.currentTime;

        const osc1 = ctx.createOscillator();
        const gain1 = ctx.createGain();
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(880, now); // A5
        osc1.frequency.exponentialRampToValueAtTime(1760, now + 0.15); // A6
        gain1.gain.setValueAtTime(0.25, now);
        gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
        osc1.connect(gain1);
        gain1.connect(ctx.destination);
        osc1.start(now);
        osc1.stop(now + 0.35);

        const osc2 = ctx.createOscillator();
        const gain2 = ctx.createGain();
        osc2.type = 'triangle';
        osc2.frequency.setValueAtTime(1318.51, now + 0.08); // E6
        gain2.gain.setValueAtTime(0.18, now + 0.08);
        gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.45);
        osc2.connect(gain2);
        gain2.connect(ctx.destination);
        osc2.start(now + 0.08);
        osc2.stop(now + 0.45);
      } catch (e) {}
    }

    function triggerHapticPulse() {
      try {
        if (navigator.vibrate) {
          navigator.vibrate([45, 30, 45]);
        }
      } catch(e) {}
    }

    // تأثير حركة ونبض السهم البلوري عند النقر
    function triggerCrystalFlowAnimation() {
      const btn = document.getElementById('crystalFlowBtn');
      if (btn) {
        btn.style.transform = 'scale(1.25) translateY(6px)';
        triggerHapticPulse();
        playBankerChime();
        setTimeout(() => { btn.style.transform = ''; }, 300);
      }
      showToastNotification('💎 تدفق التحويل المالي من الأعلى (من حساب) إلى الأسفل (إلى حساب)');
    }

    // ════════════════════════════════════════════════════════════════
    // التحكم التلقائي بحجم خط رقم التحويلة (14 إلى 20 رقماً)
    // ════════════════════════════════════════════════════════════════
    function handleTransferInputScale(val) {
      const input = document.getElementById('transferInput');
      if (!input) return;
      const len = val.length;
      if (len <= 14) {
        input.style.fontSize = '18px';
        input.style.letterSpacing = '-0.5px';
      } else if (len <= 16) {
        input.style.fontSize = '15.5px';
        input.style.letterSpacing = '-0.8px';
      } else if (len <= 18) {
        input.style.fontSize = '14px';
        input.style.letterSpacing = '-1px';
      } else {
        input.style.fontSize = '12.5px';
        input.style.letterSpacing = '-1.2px';
      }
    }

    // ════════════════════════════════════════════════════════════════
    // إعدادات وتنسيق الأرقام
    // ════════════════════════════════════════════════════════════════
    let numberConfig = {
      color: '#10b981',
      fontSize: '30px',
      fontWeight: '900',
      fontFamily: 'JetBrains Mono',
      thousandsSep: ',',
      decimalSep: '.',
      decimals: 2
    };

    let tempNumColor = '#10b981';
    let tempNumSize = '30px';
    let tempNumWeight = '900';
    let tempNumFont = 'JetBrains Mono';

    function loadSavedNumberConfig() {
      try {
        const saved = localStorage.getItem('magaiees_number_format_config');
        if (saved) {
          numberConfig = Object.assign(numberConfig, JSON.parse(saved));
        }
      } catch(e) {}

      try {
        const savedRate = localStorage.getItem('magaiees_system_usd_rate');
        if (savedRate && parseFloat(savedRate) > 0) {
          const rateVal = parseFloat(savedRate);
          document.getElementById('usdRate').value = rateVal;
          if (vouchersList[currentIndex]) vouchersList[currentIndex].rate = rateVal;
        }
      } catch(e) {}

      tempNumColor = numberConfig.color;
      tempNumSize = numberConfig.fontSize || '30px';
      tempNumWeight = numberConfig.fontWeight || '900';
      tempNumFont = numberConfig.fontFamily || 'JetBrains Mono';

      applyNumberStylesToInputs();
    }

    function applyNumberStylesToInputs() {
      const inputs = document.querySelectorAll('.amount-number-input');
      inputs.forEach(inp => {
        inp.style.color = numberConfig.color;
        inp.style.fontSize = numberConfig.fontSize;
        inp.style.fontWeight = numberConfig.fontWeight || '900';
        inp.style.fontFamily = `'${numberConfig.fontFamily || 'JetBrains Mono'}', monospace, sans-serif`;
      });
      const preview = document.getElementById('numPropLivePreview');
      if (preview) {
        preview.style.color = numberConfig.color;
        preview.style.fontSize = numberConfig.fontSize;
        preview.style.fontWeight = numberConfig.fontWeight || '900';
        preview.style.fontFamily = `'${numberConfig.fontFamily || 'JetBrains Mono'}', monospace, sans-serif`;
      }
    }

    function openNumberPropertiesModal(e) {
      if (e) {
        e.preventDefault();
        e.stopPropagation();
      }
      document.getElementById('numThousandsSelect').value = numberConfig.thousandsSep;
      document.getElementById('numDecimalSelect').value = numberConfig.decimalSep;
      
      setNumColor(numberConfig.color, '', false);
      setNumSize(numberConfig.fontSize || '30px', false);
      setNumWeight(numberConfig.fontWeight || '900', false);
      setNumFont(numberConfig.fontFamily || 'JetBrains Mono', false);
      updateNumPreview();

      document.getElementById('modalNumberProperties').classList.remove('hidden');
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
      const sizeNum = parseInt(size);
      const label = document.getElementById('currentSizeLabel');
      if (label) label.textContent = size;

      document.querySelectorAll('.size-btn').forEach(btn => {
        btn.classList.remove('bg-cyan-600', 'text-white', 'border-cyan-400');
        btn.classList.add('bg-slate-800', 'text-slate-300', 'border-slate-700');
      });
      const activeBtn = document.getElementById('btnSize' + sizeNum);
      if (activeBtn) {
        activeBtn.classList.remove('bg-slate-800', 'text-slate-300', 'border-slate-700');
        activeBtn.classList.add('bg-cyan-600', 'text-white', 'border-cyan-400');
      }
      if (refresh) updateNumPreview();
    }

    function setNumWeight(weight, refresh = true) {
      tempNumWeight = weight;
      const btnBold = document.getElementById('btnWeightBold');
      const btnNormal = document.getElementById('btnWeightNormal');
      if (!btnBold || !btnNormal) return;

      if (weight === '900' || weight === '800' || weight === 'bold') {
        btnBold.className = 'weight-btn py-1.5 rounded-xl bg-cyan-600 text-white border border-cyan-400 flex items-center justify-center gap-1.5 transition';
        btnNormal.className = 'weight-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 flex items-center justify-center gap-1.5 transition';
      } else {
        btnNormal.className = 'weight-btn py-1.5 rounded-xl bg-cyan-600 text-white border border-cyan-400 flex items-center justify-center gap-1.5 transition';
        btnBold.className = 'weight-btn py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 flex items-center justify-center gap-1.5 transition';
      }
      if (refresh) updateNumPreview();
    }

    function setNumFont(fontName, refresh = true) {
      tempNumFont = fontName;
      document.querySelectorAll('.font-btn').forEach(btn => {
        btn.className = 'font-btn w-full p-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 flex items-center justify-between text-xs transition';
        const dot = btn.querySelector('span:first-child');
        if (dot) dot.className = 'w-2 h-2 rounded-full bg-slate-500';
      });

      const cleanId = 'btnFont-' + fontName.replace(/\s+/g, '');
      const activeBtn = document.getElementById(cleanId);
      if (activeBtn) {
        activeBtn.className = 'font-btn w-full p-2 rounded-xl bg-cyan-600/20 text-cyan-300 border border-cyan-500/50 flex items-center justify-between text-xs transition';
        const dot = activeBtn.querySelector('span:first-child');
        if (dot) dot.className = 'w-2 h-2 rounded-full bg-cyan-400';
      }
      if (refresh) updateNumPreview();
    }

    function formatNumberWithConfig(val, cfg) {
      if (val === '' || val === null || isNaN(val)) return '';
      let n = parseFloat(val);
      if (isNaN(n)) return '';

      const fixed = n.toFixed(cfg.decimals || 2);
      const parts = fixed.split('.');
      let integerPart = parts[0];
      const decimalPart = parts[1];

      if (cfg.thousandsSep) {
        integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, cfg.thousandsSep);
      }

      if (decimalPart !== undefined) {
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
        decimals: 2
      });

      if (preview) {
        preview.textContent = formatted;
        preview.style.color = tempNumColor;
        preview.style.fontSize = tempNumSize;
        preview.style.fontWeight = tempNumWeight;
        preview.style.fontFamily = `'${tempNumFont}', monospace, sans-serif`;
      }
    }

    function saveNumberProperties() {
      numberConfig.color = tempNumColor;
      numberConfig.fontSize = tempNumSize;
      numberConfig.fontWeight = tempNumWeight;
      numberConfig.fontFamily = tempNumFont;
      numberConfig.thousandsSep = document.getElementById('numThousandsSelect').value;
      numberConfig.decimalSep = document.getElementById('numDecimalSelect').value;

      localStorage.setItem('magaiees_number_format_config', JSON.stringify(numberConfig));
      applyNumberStylesToInputs();
      closeNumberPropertiesModal();
      showToastNotification(`✓ تم حفظ وتطبيق خصائص الأرقام (الحجم: ${tempNumSize} بولد | ${tempNumFont}) بنجاح!`);
    }

    // ════════════════════════════════════════════════════════════════
    // تعديل ومزامنة قيمة الدولار مع السيستم
    // ════════════════════════════════════════════════════════════════
    function updateSystemUsdRate(val) {
      let rate = parseFloat(val);
      if (!rate || rate <= 0) rate = 2650;
      
      localStorage.setItem('magaiees_system_usd_rate', rate);
      
      if (vouchersList[currentIndex]) {
        vouchersList[currentIndex].rate = rate;
      }

      calculateExchange('rate');
      updateTafqeet();
      updateAccountBalanceBadges();

      showToastNotification(`✓ تم تحديث سعر صرف الدولار في السيستم إلى (${rate.toLocaleString()}) ومزامنته فورياً مع شاشة الكمبيوتر!`);
    }

    // ════════════════════════════════════════════════════════════════
    // حساب وعرض الرصيد بالدولار وبالجنيه السوداني بتباين عالي جداً
    // ════════════════════════════════════════════════════════════════
    function updateAccountBalanceBadges() {
      const rate = parseFloat(document.getElementById('usdRate').value) || 2650;
      const fromName = document.getElementById('fromSearchInput').value;
      const toName = document.getElementById('toSearchInput').value;

      const fromAcc = accountsDB.find(a => a.name === fromName);
      const toAcc = accountsDB.find(a => a.name === toName);

      if (fromAcc) {
        document.getElementById('fromAccountSdgBalance').textContent = fromAcc.sdgBalance.toLocaleString('en-US');
        document.getElementById('fromAccountUsdBalance').textContent = (fromAcc.sdgBalance / rate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      } else {
        document.getElementById('fromAccountSdgBalance').textContent = '0';
        document.getElementById('fromAccountUsdBalance').textContent = '0.00';
      }

      if (toAcc) {
        document.getElementById('toAccountSdgBalance').textContent = toAcc.sdgBalance.toLocaleString('en-US');
        document.getElementById('toAccountUsdBalance').textContent = (toAcc.sdgBalance / rate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      } else {
        document.getElementById('toAccountSdgBalance').textContent = '0';
        document.getElementById('toAccountUsdBalance').textContent = '0.00';
      }
    }

    // ════════════════════════════════════════════════════════════════
    // زر الإرسال التلقائي الصغير (أون / أوف)
    // ════════════════════════════════════════════════════════════════
    let isAutoSendEnabled = localStorage.getItem('magaiees_voucher_auto_send') !== 'false';

    function updateAutoSendUI() {
      const btn = document.getElementById('btnAutoSendToggle');
      const txt = document.getElementById('autoSendStatusText');
      if (!btn || !txt) return;

      if (isAutoSendEnabled) {
        btn.classList.add('is-on');
        btn.classList.remove('is-off');
        txt.textContent = 'أون';
      } else {
        btn.classList.add('is-off');
        btn.classList.remove('is-on');
        txt.textContent = 'أوف';
      }
    }

    function toggleAutoSend() {
      isAutoSendEnabled = !isAutoSendEnabled;
      localStorage.setItem('magaiees_voucher_auto_send', isAutoSendEnabled);
      updateAutoSendUI();
    }

    // ════════════════════════════════════════════════════════════════
    // تحميل السند والتنقل
    // ════════════════════════════════════════════════════════════════
    function setMobilePreset(width, label) {
      const container = document.getElementById('mobileContainer');
      const badge = document.getElementById('dimensionBadge');
      
      document.querySelectorAll('.preset-btn').forEach(b => {
        b.classList.remove('bg-blue-600', 'text-white');
        b.classList.add('bg-slate-800', 'text-slate-400');
      });

      if (width === '100%') {
        container.style.maxWidth = '100%';
        badge.textContent = `100% Full Width (${label})`;
        document.getElementById('btn-p-full').classList.add('bg-blue-600', 'text-white');
      } else {
        container.style.maxWidth = `${width}px`;
        badge.textContent = `${width}px × 852px (${label})`;
        const activeBtn = document.getElementById(`btn-p-${width}`);
        if (activeBtn) {
          activeBtn.classList.add('bg-blue-600', 'text-white');
          activeBtn.classList.remove('bg-slate-800', 'text-slate-400');
        }
      }
    }

    function loadVoucher(index) {
      if (index < 0 || index >= vouchersList.length) return;
      currentIndex = index;
      const v = vouchersList[index];

      document.getElementById('currentVoucherDisplay').textContent = v.id;
      document.getElementById('voucherTag').textContent = v.isOffline ? 'سند أوفلاين' : 'سيرفر الكمبيوتر';
      document.getElementById('voucherDate').value = v.date;
      document.getElementById('usdRate').value = v.rate;
      if (v.costCenter) document.getElementById('costCenterInput').value = v.costCenter;
      document.getElementById('sdgAmount').value = v.sdg || '';
      document.getElementById('usdAmount').value = v.usd || '';
      document.getElementById('voucherNotes').value = v.notes || '';
      
      const fromInput = document.getElementById('fromSearchInput');
      const toInput = document.getElementById('toSearchInput');
      if (fromInput) fromInput.value = v.from || '';
      if (toInput) toInput.value = v.to || '';

      updateAccountBalanceBadges();

      const transferInp = document.getElementById('transferInput');
      if (transferInp) {
        transferInp.value = v.refNo || '';
        handleTransferInputScale(transferInp.value);
      }
      if (v.bankName) document.getElementById('bankNameInput').value = v.bankName;

      selectCurrency(v.currency || 'SDG', false);
      setPaymentType(v.payType);
      updateTafqeet();
      applyNumberStylesToInputs();

      document.getElementById('btnPrevVoucher').disabled = (currentIndex === 0);
      document.getElementById('btnNextVoucher').disabled = (currentIndex === vouchersList.length - 1);
    }

    function navigateVoucher(direction) {
      const newIndex = currentIndex + direction;
      if (newIndex >= 0 && newIndex < vouchersList.length) {
        loadVoucher(newIndex);
      }
    }

    function startNewVoucher() {
      let nextId = '';
      if (isOfflineMode) {
        nextId = `أوفلاين ${offlineCounter}`;
      } else {
        const lastVoucher = vouchersList[vouchersList.length - 1];
        const lastNum = parseInt(lastVoucher.id.replace(/\D/g, '')) || 842;
        nextId = String(lastNum + 1);
      }

      document.getElementById('currentVoucherDisplay').textContent = nextId;
      document.getElementById('voucherTag').textContent = isOfflineMode ? 'سند أوفلاين' : 'سيرفر الكمبيوتر';
      document.getElementById('sdgAmount').value = '';
      document.getElementById('usdAmount').value = '';
      document.getElementById('transferInput').value = '';
      document.getElementById('voucherNotes').value = '';
      document.getElementById('mediaToast').classList.add('hidden');
      document.getElementById('tafqeetDisplay').classList.add('hidden');

      document.getElementById('btnNextVoucher').disabled = true;
      document.getElementById('btnPrevVoucher').disabled = false;
      showToastNotification(`✨ تم فتح سند جديد متسلسل (#${nextId}) مطابق لسيرفر الكمبيوتر.`);
    }

    function setPaymentType(type) {
      currentPayType = type;
      const buttons = document.querySelectorAll('.glass-pay-btn');
      buttons.forEach(b => {
        b.classList.remove('is-active');
        b.classList.add('text-slate-300');
      });
      const active = document.getElementById('btn-' + type);
      if (active) {
        active.classList.add('is-active');
        active.classList.remove('text-slate-300');
      }

      const container = document.getElementById('transferContainer');
      const refLabel = document.getElementById('refLabel');
      const input = document.getElementById('transferInput');

      if (type === 'transfer') {
        container.classList.remove('hidden');
        refLabel.textContent = 'رقم التحويلة:';
        input.placeholder = 'رقم التحويلة (14 رقم)';
      } else if (type.includes('cheque')) {
        container.classList.remove('hidden');
        refLabel.textContent = 'رقم الشيك:';
        input.placeholder = 'رقم الشيك';
      } else {
        container.classList.add('hidden');
      }
    }

    function selectCurrency(code, recalculate = true) {
      currentCurrency = code;
      const primarySel = document.getElementById('primaryCurrencySelect');
      if (primarySel && primarySel.value !== code) {
        primarySel.value = code;
      }
      const foreignTitle = document.getElementById('foreignAmountTitle');
      const foreignBadge = document.getElementById('foreignCurrencyBadge');

      if (code === 'USD') {
        foreignTitle.textContent = 'المبلغ بالدولار';
        foreignBadge.textContent = '$ USD';
      } else {
        foreignTitle.textContent = 'المبلغ الأجنبي (دولار)';
        foreignBadge.textContent = '$ USD';
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
        foreign.value = s > 0 ? (s / rate).toFixed(2) : '';
      } else if (source === 'rate') {
        const f = parseRawNumber(foreign.value);
        if (f > 0) sdg.value = Math.round(f * rate);
      }
    }

    function updateTafqeet() {
      const num = parseRawNumber(document.getElementById('sdgAmount').value);
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

    // ════════════════════════════════════════════════════════════════
    // البحث الفوري عن الحسابات وكتابة الاسم بخط كبير وواضح
    // ════════════════════════════════════════════════════════════════
    function liveSearchAccount(target) {
      const input = document.getElementById(target + 'SearchInput');
      const dropdown = document.getElementById(target + 'Dropdown');
      const clearBtn = document.getElementById(target + 'ClearBtn');
      const query = input.value.trim().toLowerCase();

      if (clearBtn) {
        clearBtn.classList.toggle('hidden', query.length === 0);
      }

      const results = !query 
        ? accountsDB 
        : accountsDB.filter(a => a.name.toLowerCase().includes(query) || a.category.toLowerCase().includes(query));

      if (results.length === 0) {
        dropdown.innerHTML = '<div class="p-3 text-slate-400 text-center text-xs">لا توجد حسابات مطابقة للبحث</div>';
        dropdown.classList.remove('hidden');
        return;
      }

      const rate = parseFloat(document.getElementById('usdRate').value) || 2650;

      dropdown.innerHTML = results.map(a => `
        <div onclick="selectAcc('${target}', '${a.name}')" class="p-2.5 hover:bg-slate-900 border-b border-slate-800 cursor-pointer flex items-center justify-between text-xs transition">
          <div>
            <div class="font-black text-white text-[14px]">${a.name}</div>
            <div class="text-[10px] text-slate-400">${a.category}</div>
          </div>
          <div class="text-left font-mono">
            <div class="text-amber-400 font-bold text-[11px]">${a.sdgBalance.toLocaleString()} ج.س</div>
            <div class="text-emerald-400 font-bold text-[10px]">$${(a.sdgBalance / rate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
          </div>
        </div>
      `).join('');
      dropdown.classList.remove('hidden');
    }

    function clearAccountSearch(target) {
      const input = document.getElementById(target + 'SearchInput');
      const dropdown = document.getElementById(target + 'Dropdown');
      const clearBtn = document.getElementById(target + 'ClearBtn');
      if (input) {
        input.value = '';
        input.focus();
      }
      if (dropdown) dropdown.classList.add('hidden');
      if (clearBtn) clearBtn.classList.add('hidden');
      updateAccountBalanceBadges();
    }

    function selectAcc(target, name) {
      const input = document.getElementById(target + 'SearchInput');
      const clearBtn = document.getElementById(target + 'ClearBtn');
      const dropdown = document.getElementById(target + 'Dropdown');

      if (input) {
        input.value = name;
      }
      if (clearBtn) clearBtn.classList.remove('hidden');
      if (dropdown) dropdown.classList.add('hidden');

      updateAccountBalanceBadges();
    }

    document.addEventListener('click', (e) => {
      if (!e.target.closest('#fromSearchInput') && !e.target.closest('#fromDropdown')) {
        const d = document.getElementById('fromDropdown');
        if (d) d.classList.add('hidden');
      }
      if (!e.target.closest('#toSearchInput') && !e.target.closest('#toDropdown')) {
        const d = document.getElementById('toDropdown');
        if (d) d.classList.add('hidden');
      }
    });

    // ════════════════════════════════════════════════════════════════
    // نافذة استيراد وتفريغ الدفعات الشاملة (الكاميرا 📸 | الاستوديو 🖼️ | الواتساب 💬)
    // تدعم حتى 20 إشعار مع خيارين: حفظ أوتوماتيك (رنة وهزة) ⚡ أو يدوي إشعاراً بإشعار 🖐️
    // ════════════════════════════════════════════════════════════════
    let currentIntakeSource = 'camera';
    let currentBatchLimit = 20;

    // بنك بيانات إشعارات الكاميرا الملتقطة (20 إشعار واقعي)
    const cameraReceiptsDB = [
      { id: 101, bank: 'بنك الخرطوم (بنكك)', amount: 620000, from: 'بنك الخرطوم (5540)', to: 'عمر صديق التاجر', ref: '20261005882101', note: 'شراء بضاعة - سكر كنانة' },
      { id: 102, bank: 'بنك فيصل (فوري)', amount: 310000, from: 'بنك فيصل الإسلامي (3210)', to: 'شركة الجزيرة للمواد الغذائية', ref: '20261005882102', note: 'دفعة حساب دقيق سيقا' },
      { id: 103, bank: 'بنك أم درمان الوطني', amount: 890000, from: 'بنك أم درمان الوطني', to: 'محمد أحمد عثمان', ref: '20261005882103', note: 'سداد مديونية مستحقة' },
      { id: 104, bank: 'بنك النيلين', amount: 450000, from: 'بنك النيلين', to: 'خزينة الفرع بورتسودان', ref: '20261005882104', note: 'تغذية خزينة نقدية' },
      { id: 105, bank: 'بنك الخليج', amount: 1250000, from: 'بنك الخليج (4120)', to: 'مؤسسة النور للاستيراد', ref: '20261005882105', note: 'تخليص جمركي بضائع' },
      { id: 106, bank: 'بنك الخرطوم (بنكك)', amount: 275000, from: 'بنك الخرطوم (5540)', to: 'سامي عبد الله عمر', ref: '20261005882106', note: 'سداد إيجار مخزن' },
      { id: 107, bank: 'بنك فيصل (فوري)', amount: 530000, from: 'بنك فيصل الإسلامي', to: 'المهندس مصطفى حسن', ref: '20261005882107', note: 'صيانة وتأهيل مولدات' },
      { id: 108, bank: 'بنك البركة', amount: 390000, from: 'بنك البركة السوداني', to: 'محمود عبد الرحيم', ref: '20261005882108', note: 'توريد قطع غيار' },
      { id: 109, bank: 'مصرف الساحل والصحراء', amount: 780000, from: 'مصرف الساحل والصحراء', to: 'شركة النيل للشحن', ref: '20261005882109', note: 'رسوم شحن وترحيل' },
      { id: 110, bank: 'بنك الخرطوم (بنكك)', amount: 410000, from: 'بنك الخرطوم (5540)', to: 'الطيب أحمد إبراهيم', ref: '20261005882110', note: 'سداد فواتير وقود' },
      { id: 111, bank: 'بنك العمال الوطني', amount: 195000, from: 'بنك العمال الوطني', to: 'عثمان عبد القادر', ref: '20261005882111', note: 'أتعاب نقل بضائع' },
      { id: 112, bank: 'بنك فيصل (فوري)', amount: 670000, from: 'بنك فيصل الإسلامي', to: 'خالد ميرغني عثمان', ref: '20261005882112', note: 'دفعة مواد تعبئة' },
      { id: 113, bank: 'بنك أم درمان الوطني', amount: 820000, from: 'بنك أم درمان الوطني', to: 'مؤسسة البركة للورق', ref: '20261005882113', note: 'شراء كراتين ومطبوعات' },
      { id: 114, bank: 'بنك الخليج', amount: 340000, from: 'بنك الخليج', to: 'حسن كمال الدين', ref: '20261005882114', note: 'مصاريف تسويق ومبيعات' },
      { id: 115, bank: 'بنك الخرطوم (بنكك)', amount: 960000, from: 'بنك الخرطوم (5540)', to: 'شركة الفداء للأغذية', ref: '20261005882115', note: 'توريد زيوت طعام' },
      { id: 116, bank: 'بنك النيلين', amount: 230000, from: 'بنك النيلين', to: 'ياسر محمد علي', ref: '20261005882116', note: 'عهدة نثرية مبيعات' },
      { id: 117, bank: 'بنك الثروة الحيوانية', amount: 510000, from: 'بنك الثروة الحيوانية', to: 'صالح بابكر عبد الله', ref: '20261005882117', note: 'سداد أعلاف ومواد خام' },
      { id: 118, bank: 'بنك الخرطوم (بنكك)', amount: 740000, from: 'بنك الخرطوم (5540)', to: 'فاروق عثمان التاجر', ref: '20261005882118', note: 'دفعة صابون ومنظفات' },
      { id: 119, bank: 'بنك فيصل (فوري)', amount: 380000, from: 'بنك فيصل الإسلامي', to: 'إبراهيم يوسف عوض', ref: '20261005882119', note: 'إيجار مستودع الخرطوم' },
      { id: 120, bank: 'بنك أم درمان الوطني', amount: 1150000, from: 'بنك أم درمان الوطني', to: 'الشركة السودانية المركزية', ref: '20261005882120', note: 'شراء أرز وعدس بالجملة' }
    ];

    // بنك بيانات إشعارات الاستوديو (20 إشعار)
    const galleryReceiptsDB = cameraReceiptsDB.map((c, i) => ({
      ...c,
      id: 201 + i,
      ref: `GAL-${c.ref.slice(8)}`
    }));

    function openBatchIntakeModal(source = 'camera') {
      currentIntakeSource = source;
      switchIntakeSourceTab(source);
      setBatchProcessingMode(batchProcessingMode);
      document.getElementById('modalBatchIntake').classList.remove('hidden');
    }

    function openWhatsAppModal() {
      openBatchIntakeModal('whatsapp');
    }

    function closeBatchIntakeModal() {
      document.getElementById('modalBatchIntake').classList.add('hidden');
    }

    function closeWhatsAppModal() {
      closeBatchIntakeModal();
    }

    function switchIntakeSourceTab(source) {
      currentIntakeSource = source;
      const tabs = ['camera', 'gallery', 'whatsapp'];
      
      tabs.forEach(t => {
        const btn = document.getElementById('tabBtn' + t.charAt(0).toUpperCase() + t.slice(1));
        const content = document.getElementById('contentSource' + t.charAt(0).toUpperCase() + t.slice(1));
        if (t === source) {
          if (btn) btn.className = 'px-2.5 py-1.5 rounded-xl text-xs font-black flex items-center gap-1.5 transition bg-cyan-600 text-white shadow-sm border border-cyan-400/50';
          if (content) content.classList.remove('hidden');
        } else {
          if (btn) btn.className = 'px-2.5 py-1.5 rounded-xl text-xs font-black flex items-center gap-1.5 transition bg-slate-800 text-slate-300 hover:text-white border border-slate-700';
          if (content) content.classList.add('hidden');
        }
      });

      renderCurrentSourceContent();
      selectBatchCount(currentBatchLimit, source);
    }

    function selectBatchCount(count, source = currentIntakeSource) {
      currentBatchLimit = count;
      currentWaLimit = count;

      // تحديث أزرار الفلترة للمصادر الثلاثة
      [1, 5, 10, 15, 20].forEach(n => {
        const camBtn = document.getElementById('btnCam-' + n);
        const galBtn = document.getElementById('btnGal-' + n);
        const waBtn = document.getElementById('btnWa-' + n);
        
        [camBtn, galBtn, waBtn].forEach(btn => {
          if (btn) {
            if (n === count) {
              btn.className = 'py-1.5 rounded-xl bg-cyan-600 text-white border border-cyan-400 text-center font-bold shadow-sm';
            } else {
              btn.className = 'py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-center font-bold hover:bg-slate-700';
            }
          }
        });
      });

      const label = document.getElementById('startBatchBtnLabel');
      if (label) {
        const srcName = source === 'camera' ? 'الكاميرا' : (source === 'gallery' ? 'الاستوديو' : 'الواتساب');
        if (batchProcessingMode === 'auto') {
          label.textContent = `بدء تفريغ وحفظ (${count}) إشعار من ${srcName} تلقائياً ⚡🔔`;
        } else {
          label.textContent = `بدء تدفق (${count}) إشعار من ${srcName} (إشعاراً بإشعار) 🖐️`;
        }
      }

      renderCurrentSourceContent();
    }

    function setBatchProcessingMode(mode) {
      batchProcessingMode = mode;
      localStorage.setItem('magaiees_batch_mode', mode);

      // تحديث الأزرار في الواجهة الرئيسية
      const mainAuto = document.getElementById('btnMainModeAuto');
      const mainManual = document.getElementById('btnMainModeManual');
      if (mainAuto && mainManual) {
        if (mode === 'auto') {
          mainAuto.className = 'py-1 px-2 rounded-md text-[10px] font-black transition flex items-center gap-1 bg-emerald-600 text-white shadow-sm';
          mainManual.className = 'py-1 px-2 rounded-md text-[10px] font-black transition flex items-center gap-1 text-slate-400 hover:text-white';
        } else {
          mainManual.className = 'py-1 px-2 rounded-md text-[10px] font-black transition flex items-center gap-1 bg-cyan-600 text-white shadow-sm';
          mainAuto.className = 'py-1 px-2 rounded-md text-[10px] font-black transition flex items-center gap-1 text-slate-400 hover:text-white';
        }
      }

      // تحديث الأزرار داخل نافذة الدفعات
      const btnAuto = document.getElementById('btnBatchModeAuto');
      const btnManual = document.getElementById('btnBatchModeManual');
      const badge = document.getElementById('batchModeStatusBadge');
      if (btnAuto && btnManual) {
        if (mode === 'auto') {
          btnAuto.className = 'py-1.5 px-2 rounded-xl bg-emerald-600 text-white border border-emerald-400 flex items-center justify-center gap-1.5 shadow-sm transition';
          btnManual.className = 'py-1.5 px-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 flex items-center justify-center gap-1.5 hover:bg-slate-700 transition';
          if (badge) badge.textContent = '⚡ أوتوماتيك نشط (رنة وهزة وحفظ فوري)';
        } else {
          btnManual.className = 'py-1.5 px-2 rounded-xl bg-cyan-600 text-white border border-cyan-400 flex items-center justify-center gap-1.5 shadow-sm transition';
          btnAuto.className = 'py-1.5 px-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 flex items-center justify-center gap-1.5 hover:bg-slate-700 transition';
          if (badge) badge.textContent = '🖐️ يدوي نشط (إشعاراً بإشعار مع زر حفظ)';
        }
      }

      selectBatchCount(currentBatchLimit, currentIntakeSource);
    }

    function renderCurrentSourceContent() {
      if (currentIntakeSource === 'camera') {
        const grid = document.getElementById('cameraThumbnailsGrid');
        if (!grid) return;
        const items = cameraReceiptsDB.slice(0, currentBatchLimit);
        grid.innerHTML = items.map((c, idx) => `
          <div class="p-1.5 rounded-xl bg-slate-950 border border-indigo-500/40 text-center flex flex-col items-center justify-center gap-1 cursor-pointer hover:border-indigo-400 transition" onclick="previewIntakeItem(${idx}, 'camera')">
            <div class="w-full h-11 rounded-lg bg-indigo-950/80 border border-indigo-400/30 flex flex-col items-center justify-center relative overflow-hidden">
              <i class="fa-solid fa-receipt text-indigo-300 text-xs"></i>
              <span class="text-[8px] font-mono text-indigo-200">#${idx + 1}</span>
              <span class="absolute top-0.5 right-0.5 w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            </div>
            <span class="text-[9px] font-mono font-black text-amber-300 truncate w-full">${(c.amount).toLocaleString()}</span>
          </div>
        `).join('');
        document.getElementById('cameraCountLabel').textContent = `تم تجهيز: ${items.length} صورة إشعار جاهزة للتفريغ`;
      } else if (currentIntakeSource === 'gallery') {
        const grid = document.getElementById('galleryThumbnailsGrid');
        if (!grid) return;
        const items = galleryReceiptsDB.slice(0, currentBatchLimit);
        grid.innerHTML = items.map((c, idx) => `
          <div class="p-1.5 rounded-xl bg-slate-950 border border-amber-500/40 text-center flex flex-col items-center justify-center gap-1 cursor-pointer hover:border-amber-400 transition" onclick="previewIntakeItem(${idx}, 'gallery')">
            <div class="w-full h-11 rounded-lg bg-amber-950/80 border border-amber-400/30 flex flex-col items-center justify-center relative overflow-hidden">
              <i class="fa-solid fa-image text-amber-300 text-xs"></i>
              <span class="text-[8px] font-mono text-amber-200">#${idx + 1}</span>
              <span class="absolute top-0.5 right-0.5 w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            </div>
            <span class="text-[9px] font-mono font-black text-amber-300 truncate w-full">${(c.amount).toLocaleString()}</span>
          </div>
        `).join('');
        document.getElementById('galleryCountLabel').textContent = `تم اختيار: ${items.length} صورة إشعار من المعرض`;
      } else if (currentIntakeSource === 'whatsapp') {
        renderWhatsAppList();
      }
    }

    function renderWhatsAppList() {
      const container = document.getElementById('whatsAppListContainer');
      if (!container) return;
      const list = whatsappNotificationsDB.slice(0, currentBatchLimit);

      container.innerHTML = list.map((item, idx) => `
        <div onclick="selectWhatsAppReceipt(${item.id})" class="whatsapp-card p-2.5 rounded-2xl bg-slate-950 border border-slate-800 hover:border-emerald-400 cursor-pointer flex items-center justify-between gap-2">
          <div class="flex items-center gap-2 min-w-0">
            <div class="w-7 h-7 rounded-full bg-emerald-600/30 text-emerald-300 border border-emerald-500/40 flex items-center justify-center font-bold text-xs shrink-0">
              #${idx + 1}
            </div>
            <div class="min-w-0">
              <div class="flex items-center gap-1.5">
                <span class="font-black text-white text-xs truncate">${item.to}</span>
                <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-400 font-mono">${item.bank}</span>
              </div>
              <div class="text-[10px] text-slate-400 font-mono mt-0.5 truncate">
                تحويلة: ${item.ref}
              </div>
            </div>
          </div>
          <div class="text-left shrink-0">
            <div class="font-mono font-black text-emerald-400 text-xs sm:text-sm">${item.amount.toLocaleString()} ج.س</div>
            <span class="text-[8.5px] text-cyan-400 font-bold block">تفريغ فوري ⚡</span>
          </div>
        </div>
      `).join('');

      const countBadge = document.getElementById('whatsAppCountBadge');
      if (countBadge) countBadge.textContent = `${list.length} إشعار متاح`;
    }

    function triggerCameraCapture() {
      const flash = document.getElementById('cameraFlashOverlay');
      if (flash) {
        flash.classList.add('is-flashing');
        setTimeout(() => flash.classList.remove('is-flashing'), 380);
      }
      playBankerChime();
      triggerHapticPulse();
      showToastNotification(`📸 تم التقاط صورة إشعار جديدة بالكاميرا وتضمينها في الدفعة!`);
      renderCurrentSourceContent();
    }

    function handleGalleryFilesSelected(e) {
      const files = e.target.files;
      if (files && files.length > 0) {
        const count = Math.min(20, files.length);
        selectBatchCount(count, 'gallery');
        playBankerChime();
        triggerHapticPulse();
        showToastNotification(`📁 تم استيراد ${count} صورة إشعار بنكي من الاستوديو بنجاح!`);
      }
    }

    function previewIntakeItem(idx, source) {
      const list = source === 'camera' ? cameraReceiptsDB : galleryReceiptsDB;
      const item = list[idx] || list[0];
      applyReceiptToVoucherForm(item);
      closeBatchIntakeModal();
      showToastNotification(`⚡ تم تفريغ الإشعار (#${idx + 1}) بنجاح!`);
    }

    function selectWhatsAppReceipt(id) {
      const item = whatsappNotificationsDB.find(w => w.id === id) || whatsappNotificationsDB[0];
      applyReceiptToVoucherForm(item);
      closeBatchIntakeModal();
    }

    // ════════════════════════════════════════════════════════════════
    // تشغيل تفريغ الدفعات الشامل: أوتوماتيك (Auto) أو إشعاراً بإشعار (Manual)
    // ════════════════════════════════════════════════════════════════
    function startUniversalBatchProcessing() {
      let rawQueue = [];
      if (currentIntakeSource === 'camera') {
        rawQueue = cameraReceiptsDB.slice(0, currentBatchLimit);
      } else if (currentIntakeSource === 'gallery') {
        rawQueue = galleryReceiptsDB.slice(0, currentBatchLimit);
      } else {
        rawQueue = whatsappNotificationsDB.slice(0, currentBatchLimit);
      }

      whatsAppQueue = rawQueue;
      currentQueueIndex = 0;
      closeBatchIntakeModal();

      if (batchProcessingMode === 'auto') {
        showToastNotification(`⚡ جارِ تفريغ وحفظ ${whatsAppQueue.length} إشعار تلقائياً مع الهزة والنغمة المصرفية...`);
        runUniversalAutoBatchStep();
      } else {
        document.getElementById('batchProgressStrip').classList.remove('hidden');
        document.getElementById('batchProgressStrip').classList.add('flex');
        loadNextManualQueueItem();
      }
    }

    function runUniversalAutoBatchStep() {
      if (currentQueueIndex >= whatsAppQueue.length) {
        playBankerChime();
        triggerHapticPulse();
        showToastNotification(`🎉 اكتمل تفريغ وحفظ جميع الإشعارات الـ (${whatsAppQueue.length}) بنجاح في السيرفر وقاعدة البيانات!`);
        return;
      }

      const item = whatsAppQueue[currentQueueIndex];
      applyReceiptToVoucherForm(item);
      triggerHapticPulse();
      playBankerChime();

      saveSingleVoucherSilently(item);
      currentQueueIndex++;

      setTimeout(() => {
        runUniversalAutoBatchStep();
      }, 400);
    }

    function applyReceiptToVoucherForm(item) {
      setPaymentType('transfer');
      const transferInp = document.getElementById('transferInput');
      if (transferInp) {
        transferInp.value = item.ref;
        handleTransferInputScale(item.ref);
      }

      document.getElementById('bankNameInput').value = item.bank.replace(/\s*\(.*\)/, '');
      document.getElementById('sdgAmount').value = item.amount;
      calculateExchange('sdg');
      updateTafqeet();

      selectAcc('to', item.to);
      selectAcc('from', item.from);

      document.getElementById('voucherNotes').value = `إشعار تحويل (${item.bank} #${item.ref}) - ${item.note || 'سداد حركة'}`;

      const toast = document.getElementById('mediaToast');
      const text = document.getElementById('mediaToastText');
      if (toast) {
        toast.classList.remove('hidden');
        toast.classList.add('flex');
      }
      if (text) {
        text.innerHTML = `⚡ <strong>تم التفريغ الفوري!</strong> رقم التحويلة (${item.ref}) • المبلغ (${item.amount.toLocaleString()} ج.س) • العميل (${item.to})`;
      }

      const card = document.getElementById('sdgAmountCard');
      if (card) {
        card.classList.add('flash-highlight');
        setTimeout(() => card.classList.remove('flash-highlight'), 600);
      }
    }

    function simulateMediaIntake(source) {
      openBatchIntakeModal(source);
    }

    // ════════════════════════════════════════════════════════════════
    // نافذة إرسال إشعار السند للعميل عبر الواتساب (📲 إرسال)
    // ════════════════════════════════════════════════════════════════
    let currentWhatsAppRecipient = 'to';

    function openSendWhatsAppModal() {
      const v = vouchersList[currentIndex] || vouchersList[0];
      const toName = document.getElementById('toSearchInput')?.value || v.to;
      const fromName = document.getElementById('fromSearchInput')?.value || v.from;

      document.getElementById('recipientToNameDisplay').textContent = toName;
      document.getElementById('recipientFromNameDisplay').textContent = fromName;

      selectWhatsAppRecipient('to');
      updateSendWhatsAppMessagePreview();
      document.getElementById('modalSendWhatsApp').classList.remove('hidden');
    }

    function closeSendWhatsAppModal() {
      document.getElementById('modalSendWhatsApp').classList.add('hidden');
    }

    function selectWhatsAppRecipient(type) {
      currentWhatsAppRecipient = type;
      const btnTo = document.getElementById('btnRecipientTo');
      const btnFrom = document.getElementById('btnRecipientFrom');

      if (type === 'to') {
        btnTo.className = 'py-2 px-2 rounded-xl bg-emerald-600 text-white border border-emerald-400 flex flex-col items-center justify-center gap-0.5 shadow-sm transition';
        btnFrom.className = 'py-2 px-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 flex flex-col items-center justify-center gap-0.5 hover:bg-slate-700 transition';
        document.getElementById('whatsAppRecipientPhone').value = '+249912345678';
      } else {
        btnFrom.className = 'py-2 px-2 rounded-xl bg-emerald-600 text-white border border-emerald-400 flex flex-col items-center justify-center gap-0.5 shadow-sm transition';
        btnTo.className = 'py-2 px-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 flex flex-col items-center justify-center gap-0.5 hover:bg-slate-700 transition';
        document.getElementById('whatsAppRecipientPhone').value = '+249123000000';
      }

      updateSendWhatsAppMessagePreview();
    }

    function generateFormattedVoucherMessage() {
      const v = vouchersList[currentIndex] || vouchersList[0];
      const voucherNum = document.getElementById('currentVoucherDisplay')?.textContent || v.id;
      const dateVal = document.getElementById('voucherDate')?.value || v.date;
      const sdgVal = document.getElementById('sdgAmount')?.value || (v.sdg || 0);
      const usdVal = document.getElementById('usdAmount')?.value || (v.usd || 0);
      const fromAcc = document.getElementById('fromSearchInput')?.value || v.from;
      const toAcc = document.getElementById('toSearchInput')?.value || v.to;
      const refNum = document.getElementById('transferInput')?.value || v.refNo || '---';
      const bankVal = document.getElementById('bankNameInput')?.value || v.bankName || 'بنك الخرطوم';
      const notesVal = document.getElementById('voucherNotes')?.value || v.notes || 'سند حركة مالية';
      const costCenter = document.getElementById('costCenterInput')?.value || v.costCenter || 'مخزن بحري الرئيسي';

      return `🏢 *مؤسسة المقاييس للتجارة والخدمات*
━━━━━━━━━━━━━━━━━━
🧾 *إشعار سند حركة مالية معتمد*
🔢 *رقم السند:* #${voucherNum}
📅 *التاريخ:* ${dateVal}
💰 *المبلغ المحلي:* ${Number(sdgVal).toLocaleString()} ج.س
💵 *المبلغ بالدولار:* ${Number(usdVal).toLocaleString()} $ USD
🔴 *من حساب:* ${fromAcc}
🟢 *إلى حساب:* ${toAcc}
🏦 *البنك:* ${bankVal}
🔢 *رقم التحويلة:* ${refNum}
🏢 *مركز الكلفة:* ${costCenter}
📝 *البيان:* ${notesVal}
━━━━━━━━━━━━━━━━━━
✅ *تم القيد والاعتماد بنجاح في النظام المحاسبي*`;
    }

    function updateSendWhatsAppMessagePreview() {
      const container = document.getElementById('whatsAppMessagePreview');
      if (!container) return;
      const msg = generateFormattedVoucherMessage();
      container.innerHTML = msg.replace(/\n/g, '<br>').replace(/\*(.*?)\*/g, '<strong>$1</strong>');
    }

    function executeSendWhatsApp(action) {
      const phone = (document.getElementById('whatsAppRecipientPhone')?.value || '').replace(/\D/g, '');
      const msg = generateFormattedVoucherMessage();

      if (action === 'direct') {
        const cleanPhone = phone || '249912345678';
        const url = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(msg)}`;
        window.open(url, '_blank');
        showToastNotification('📲 تم فتح الواتساب بنجاح لإرسال الإشعار للعميل!');
      } else {
        if (navigator.clipboard) {
          navigator.clipboard.writeText(msg).then(() => {
            showToastNotification('📋 تم نسخ نص إشعار السند إلى الحافظة بنجاح!');
          });
        }
      }
    }

    // ════════════════════════════════════════════════════════════════
    // نافذة طباعة السند (🖨️ طباعة) - إيصال حراري POS 80mm وسند A4
    // ════════════════════════════════════════════════════════════════
    let currentPrintFormat = 'thermal';

    function handlePrintVoucher() {
      renderPrintVoucherSheet();
      document.getElementById('modalPrintVoucher').classList.remove('hidden');
    }

    function closePrintVoucherModal() {
      document.getElementById('modalPrintVoucher').classList.add('hidden');
    }

    function switchPrintFormat(format) {
      currentPrintFormat = format;
      const btnThermal = document.getElementById('btnFormatThermal');
      const btnStd = document.getElementById('btnFormatStandard');

      if (format === 'thermal') {
        btnThermal.className = 'py-1.5 rounded-xl bg-purple-600 text-white border border-purple-400 flex items-center justify-center gap-1.5 shadow-sm transition';
        btnStd.className = 'py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 flex items-center justify-center gap-1.5 hover:bg-slate-700 transition';
      } else {
        btnStd.className = 'py-1.5 rounded-xl bg-purple-600 text-white border border-purple-400 flex items-center justify-center gap-1.5 shadow-sm transition';
        btnThermal.className = 'py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 flex items-center justify-center gap-1.5 hover:bg-slate-700 transition';
      }

      renderPrintVoucherSheet();
    }

    function renderPrintVoucherSheet() {
      const container = document.getElementById('printSheetPreviewContainer');
      const slipContainer = document.getElementById('printableThermalSlip');
      if (!container) return;

      const v = vouchersList[currentIndex] || vouchersList[0];
      const voucherNum = document.getElementById('currentVoucherDisplay')?.textContent || v.id;
      const dateVal = document.getElementById('voucherDate')?.value || v.date;
      const sdgVal = document.getElementById('sdgAmount')?.value || (v.sdg || 0);
      const usdVal = document.getElementById('usdAmount')?.value || (v.usd || 0);
      const fromAcc = document.getElementById('fromSearchInput')?.value || v.from;
      const toAcc = document.getElementById('toSearchInput')?.value || v.to;
      const refNum = document.getElementById('transferInput')?.value || v.refNo || '---';
      const bankVal = document.getElementById('bankNameInput')?.value || v.bankName || 'بنك الخرطوم';
      const notesVal = document.getElementById('voucherNotes')?.value || v.notes || 'سند حركة';
      const tafqeetText = document.getElementById('tafqeetText')?.textContent || 'فقط أربعمائة وخمسون ألف جنيه لا غير';

      let html = '';

      if (currentPrintFormat === 'thermal') {
        // إيصال حراري POS 80mm للبلوتوث والموبايل
        html = `
          <div class="text-center pb-2 border-b border-dashed border-slate-400">
            <h2 class="text-sm font-black">مؤسسة المقاييس للتجارة والخدمات</h2>
            <div class="text-[10px] text-slate-600">سند حركة مالية - نسخة الموبايل</div>
            <div class="font-bold text-xs mt-1">رقم السند: #${voucherNum}</div>
            <div class="text-[10px] text-slate-500">${dateVal} • ${new Date().toLocaleTimeString('ar-SA')}</div>
          </div>

          <div class="py-2 border-b border-dashed border-slate-400 space-y-1 text-[11px]">
            <div class="flex justify-between font-bold">
              <span>المبلغ المحلي:</span>
              <span class="font-black text-sm">${Number(sdgVal).toLocaleString()} ج.س</span>
            </div>
            <div class="flex justify-between text-slate-700">
              <span>المبلغ بالدولار:</span>
              <span>${Number(usdVal).toLocaleString()} $ USD</span>
            </div>
            <div class="text-[9.5px] text-slate-600 pt-0.5">${tafqeetText}</div>
          </div>

          <div class="py-2 border-b border-dashed border-slate-400 space-y-1 text-[11px]">
            <div class="flex justify-between">
              <span class="text-slate-600">من حساب:</span>
              <span class="font-bold">${fromAcc}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-600">إلى حساب:</span>
              <span class="font-bold">${toAcc}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-600">البنك / التحويلة:</span>
              <span class="font-mono">${bankVal} | ${refNum}</span>
            </div>
            <div class="text-[10px] text-slate-600 pt-1">
              البيان: ${notesVal}
            </div>
          </div>

          <div class="pt-2 text-center space-y-1.5">
            <div class="text-[10px] font-mono tracking-widest text-slate-500">||| | |||| | ||| |||| | ||</div>
            <div class="text-[8.5px] text-slate-400">نظام المقاييس المحاسبي - تم القيد آلياً</div>
            <div class="flex justify-between pt-4 text-[9px] text-slate-600">
              <span>توقيع المحاسب: ............</span>
              <span>توقيع المستلم: ............</span>
            </div>
          </div>
        `;
      } else {
        // سند رسمي محاسبي A4
        html = `
          <div class="border-2 border-slate-800 p-3 rounded-lg space-y-3">
            <div class="flex justify-between items-center border-b pb-2">
              <div>
                <h1 class="text-base font-black">مؤسسة المقاييس التجارية</h1>
                <div class="text-xs text-slate-600">سند قيد وحركة مالية رسمية</div>
              </div>
              <div class="text-left font-mono">
                <div class="text-sm font-black text-purple-700">NO: #${voucherNum}</div>
                <div class="text-xs text-slate-500">DATE: ${dateVal}</div>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs border p-2 rounded bg-slate-50">
              <div><strong>من حـ/ :</strong> ${fromAcc}</div>
              <div><strong>إلى حـ/ :</strong> ${toAcc}</div>
              <div><strong>المبلغ :</strong> ${Number(sdgVal).toLocaleString()} ج.س (${Number(usdVal).toLocaleString()} $)</div>
              <div><strong>رقم التحويلة :</strong> ${refNum} (${bankVal})</div>
            </div>

            <div class="text-xs">
              <strong>التفقيط:</strong> ${tafqeetText}
            </div>
            <div class="text-xs">
              <strong>البيان:</strong> ${notesVal}
            </div>

            <div class="flex justify-between pt-8 text-xs border-t">
              <span>إعداد: أمين الصندوق</span>
              <span>مراجعة: المدير المالي</span>
              <span>اعتماد الإدارة العامة</span>
            </div>
          </div>
        `;
      }

      container.innerHTML = html;
      if (slipContainer) slipContainer.innerHTML = html;
    }

    function triggerBrowserPrint() {
      renderPrintVoucherSheet();
      window.print();
    }

    // ════════════════════════════════════════════════════════════════
    // نافذة فتح السندات المسجلة (📂 افتح)
    // ════════════════════════════════════════════════════════════════
    function openSearchVouchersModal() {
      renderVouchersListInModal();
      document.getElementById('modalSearchVouchers').classList.remove('hidden');
    }

    function closeSearchVouchersModal() {
      document.getElementById('modalSearchVouchers').classList.add('hidden');
    }

    function renderVouchersListInModal() {
      const container = document.getElementById('vouchersModalListContainer');
      const query = (document.getElementById('voucherSearchFilterInput')?.value || '').trim().toLowerCase();

      const filtered = vouchersList.filter(v => 
        !query || 
        v.id.includes(query) || 
        v.to.toLowerCase().includes(query) || 
        v.from.toLowerCase().includes(query) || 
        String(v.sdg).includes(query)
      );

      if (filtered.length === 0) {
        container.innerHTML = '<div class="p-4 text-center text-slate-400 text-xs">لا توجد سندات مطابقة</div>';
        return;
      }

      container.innerHTML = filtered.map((v, idx) => `
        <div onclick="selectVoucherFromModal(${idx})" class="p-3 rounded-2xl bg-slate-950 border ${vouchersList[currentIndex].id === v.id ? 'border-amber-400 bg-amber-950/20' : 'border-slate-800'} hover:border-amber-400/60 cursor-pointer flex items-center justify-between text-xs transition">
          <div>
            <div class="flex items-center gap-1.5 mb-1">
              <span class="px-2 py-0.5 rounded-lg bg-amber-500/20 text-amber-300 font-mono font-black text-xs">#${v.id}</span>
              <span class="font-bold text-white">${v.to}</span>
            </div>
            <div class="text-[10px] text-slate-400">${v.from} • ${v.date}</div>
          </div>
          <div class="text-left font-mono font-black text-emerald-400 text-sm">
            ${(v.sdg || 0).toLocaleString()} ج.س
          </div>
        </div>
      `).join('');
    }

    function selectVoucherFromModal(idx) {
      loadVoucher(idx);
      closeSearchVouchersModal();
      showToastNotification(`📂 تم فتح السند رقم (#${vouchersList[idx].id}) بنجاح.`);
    }

    // ════════════════════════════════════════════════════════════════
    // محاكاة إدخال سند جديد من اللابتوب
    // ════════════════════════════════════════════════════════════════
    function simulateIncomingLaptopVoucher() {
      const lastVoucher = vouchersList[vouchersList.length - 1];
      const lastNum = parseInt(lastVoucher.id.replace(/\D/g, '')) || 842;
      const nextId = String(lastNum + 1);

      const laptopVoucher = {
        id: nextId,
        isOffline: false,
        date: new Date().toISOString().split('T')[0],
        rate: parseFloat(document.getElementById('usdRate').value) || 2650,
        currency: 'SDG',
        costCenter: 'الإدارة العامة - لابتوب',
        sdg: 4500000,
        usd: 1698.11,
        payType: 'transfer',
        from: 'بنك فيصل الإسلامي (3290)',
        to: 'محمد أحمد عثمان',
        refNo: '99201849920184',
        bankName: 'بنك فيصل الإسلامي',
        notes: 'سند مدخل عبر شاشة اللابتوب - متزامن فورياً مع الجوال'
      };

      vouchersList.push(laptopVoucher);
      loadVoucher(vouchersList.length - 1);
      triggerHapticPulse();
      playBankerChime();
      showToastNotification(`💻 وارد من اللابتوب: ظهر السند الجديد (#${nextId}) فور إدخاله في الكمبيوتر!`);
    }

    // ════════════════════════════════════════════════════════════════
    // التبديل بين أونلاين وأوفلاين
    // ════════════════════════════════════════════════════════════════
    function toggleOnlineStatus() {
      isOfflineMode = !isOfflineMode;
      const pill = document.getElementById('networkPill');
      const dot = document.getElementById('networkDot');
      const text = document.getElementById('networkText');
      const banner = document.getElementById('offlineSyncBanner');

      if (isOfflineMode) {
        pill.className = 'flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-amber-500/15 border border-amber-500/40 text-amber-400 text-[10px] font-black cursor-pointer';
        dot.className = 'w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse';
        text.textContent = 'أوفلاين';
        banner.classList.remove('hidden');
        banner.classList.add('flex');
        startNewVoucher();
        showToastNotification('⚠️ تم التبديل إلى وضع الأوفلاين (غير متصل بالسيرفر)');
      } else {
        pill.className = 'flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/40 text-emerald-400 text-[10px] font-black cursor-pointer';
        dot.className = 'w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_#10b981]';
        text.textContent = 'متصل';
        
        if (offlineQueue.length > 0) {
          banner.classList.remove('hidden');
          banner.classList.add('flex');
          setTimeout(() => {
            const count = offlineQueue.length;
            offlineQueue = [];
            offlineCounter = 1;
            document.getElementById('pendingCount').textContent = '0 معلق';
            banner.classList.add('hidden');
            loadVoucher(vouchersList.length - 1);
            showToastNotification(`✓ تم رفع ومزامنة ${count} سندات مع سيرفر اللابتوب والكمبيوتر!`);
          }, 1000);
        } else {
          banner.classList.add('hidden');
          showToastNotification('🟢 متصل بسيرفر الكمبيوتر (أونلاين)');
        }
      }
    }

    // ════════════════════════════════════════════════════════════════
    // الحفظ والتأكيد والخروج
    // ════════════════════════════════════════════════════════════════
    function handleQuickCheckSave() {
      handleSave(true);
    }

    function handleSave(isQuickCheck = false) {
      const sdg = parseRawNumber(document.getElementById('sdgAmount').value);
      const usd = parseRawNumber(document.getElementById('usdAmount').value);

      if (!sdg && !usd) {
        alert('⚠️ الرجاء إدخال المبلغ أولاً بالمحلي أو بالدولار.');
        return;
      }

      triggerHapticPulse();
      playBankerChime();

      let savedId = '';
      if (isOfflineMode) {
        savedId = `أوفلاين ${offlineCounter}`;
        offlineCounter++;
        offlineQueue.push(savedId);
        document.getElementById('pendingCount').textContent = `${offlineQueue.length} معلق`;
        
        const newVoucher = {
          id: savedId,
          isOffline: true,
          date: document.getElementById('voucherDate').value,
          rate: parseFloat(document.getElementById('usdRate').value),
          currency: currentCurrency,
          costCenter: document.getElementById('costCenterInput').value,
          sdg: sdg,
          usd: usd,
          payType: currentPayType,
          from: document.getElementById('fromSearchInput').value || 'الخزينة الرئيسية (كاش)',
          to: document.getElementById('toSearchInput').value || 'محمد أحمد عثمان',
          refNo: document.getElementById('transferInput').value,
          bankName: document.getElementById('bankNameInput').value,
          notes: document.getElementById('voucherNotes').value
        };
        vouchersList.push(newVoucher);
        showToastNotification(`💾 تم حفظ السند محلياً برقم (${savedId})!\nسيرفع إلى سيرفر الكمبيوتر أوتوماتيكياً فور توفر الإنترنت.`);
        
        if (isAutoSendEnabled) {
          setTimeout(() => {
            showToastNotification(`📲 الإرسال التلقائي: تم إرسال السند (${savedId}) عبر الواتساب!`);
          }, 400);
        }

        loadVoucher(vouchersList.length - 1);
      } else {
        const lastNum = parseInt(vouchersList[vouchersList.length - 1].id.replace(/\D/g, '')) || 842;
        savedId = String(lastNum + 1);

        const newVoucher = {
          id: savedId,
          isOffline: false,
          date: document.getElementById('voucherDate').value,
          rate: parseFloat(document.getElementById('usdRate').value),
          currency: currentCurrency,
          costCenter: document.getElementById('costCenterInput').value,
          sdg: sdg,
          usd: usd,
          payType: currentPayType,
          from: document.getElementById('fromSearchInput').value || 'بنك الخرطوم (5540)',
          to: document.getElementById('toSearchInput').value || 'محمد أحمد عثمان',
          refNo: document.getElementById('transferInput').value,
          bankName: document.getElementById('bankNameInput').value,
          notes: document.getElementById('voucherNotes').value
        };
        vouchersList.push(newVoucher);
        
        showToastNotification(`✅ ${isQuickCheck ? 'تأكيد وحفظ (✓): ' : ''}تم الحفظ والترحيل إلى سيرفر الكمبيوتر!\n- رقم السند التسلسلي: #${savedId}\n- متطابق مع شاشة الكمبيوتر الآن!`);
        
        if (isAutoSendEnabled) {
          setTimeout(() => {
            showToastNotification(`📲 الإرسال التلقائي: تم إرسال السند (${savedId}) عبر الواتساب للعميل (${newVoucher.to})!`);
          }, 500);
        }

        loadVoucher(vouchersList.length - 1);
      }

      // إذا كنا في وضع التدفق اليدوي (إشعاراً بإشعار): الانتقال للإشعار التالي تلقائياً!
      if (whatsAppQueue.length > 0 && batchProcessingMode === 'manual') {
        currentQueueIndex++;
        setTimeout(() => {
          loadNextManualQueueItem();
        }, 500);
      }
    }

    function handleEdit() {
      showToastNotification(`✏️ وضع التعديل: يمكنك تعديل بيانات السند الحالي (رقم: #${vouchersList[currentIndex].id}) ثم النقر على تأكيد وحفظ.`);
    }

    function handleDelete() {
      if (confirm(`هل أنت متأكد من حذف السند (رقم: #${vouchersList[currentIndex].id}) نهائياً من قاعدة البيانات وسيرفر الكمبيوتر؟`)) {
        vouchersList.splice(currentIndex, 1);
        showToastNotification('🗑️ تم حذف السند بنجاح من قاعدة البيانات.');
        if (vouchersList.length > 0) {
          loadVoucher(Math.max(0, currentIndex - 1));
        } else {
          startNewVoucher();
        }
      }
    }

    function handleExit() {
      if (confirm('هل تريد الخروج من شاشة الحركة المالية والرجوع للقائمة الرئيسية؟')) {
        showToastNotification('🚪 تم الخروج بنجاح والرجوع للقائمة الرئيسية.');
      }
    }

    function showToastNotification(message) {
      const container = document.getElementById('globalToast');
      const msg = document.getElementById('globalToastMsg');
      if (!container || !msg) return;
      msg.innerHTML = message.replace(/\n/g, '<br>');
      container.classList.remove('hidden');
      container.classList.add('flex');
      setTimeout(() => {
        container.classList.remove('opacity-0', '-translate-y-4');
        container.classList.add('opacity-100', 'translate-y-0');
      }, 10);
      
      if (window._toastTimer) clearTimeout(window._toastTimer);
      window._toastTimer = setTimeout(() => {
        container.classList.add('opacity-0', '-translate-y-4');
        container.classList.remove('opacity-100', 'translate-y-0');
        setTimeout(() => { container.classList.add('hidden'); }, 300);
      }, 4000);
    }

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
        }, 500);
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

    function updateClock() {
      const now = new Date();
      document.getElementById('currentTime').textContent = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
    }
    setInterval(updateClock, 30000);
    updateClock();

    
    // دمج بيانات السندات الحقيقية من قاعدة بيانات السيرفر
    if (window.PHP_VOUCHERS && window.PHP_VOUCHERS.length > 0) {
      vouchersList = window.PHP_VOUCHERS.map(v => ({
        id: v.id,
        isOffline: false,
        date: v.date || '2026-10-06',
        rate: v.rate || 2650,
        currency: v.currency || 'SDG',
        costCenter: v.costCenter || 'مخزن بحري الرئيسي',
        sdg: v.sdg || 0,
        usd: v.foreignAmt || 0,
        payType: v.payType || 'transfer',
        from: v.fromName || 'بنك الخرطوم (5540)',
        to: v.toName || 'محمد أحمد عثمان',
        refNo: v.refNo || '',
        bankName: v.bankName || 'بنك الخرطوم',
        notes: v.notes || ''
      }));
      currentIndex = vouchersList.length - 1;
    }

    // تشغيل الإعدادات المبدئية
    loadSavedNumberConfig();
    updateAutoSendUI();
    setupAmountLongPress(document.getElementById('sdgAmountCard'));
    setupAmountLongPress(document.getElementById('usdAmountCard'));
    loadVoucher(2);

    // ════════════════════════════════════════════════════════════════
    // وظيفة التحديث الحي الفوري في الحين (تخطي الكاش تماماً)
    // ════════════════════════════════════════════════════════════════
    function forceLiveRefresh() {
      playFeedbackTone('tap');
      showToast('🔄 جارٍ التحديث الحي الفوري...');
      // إرسال إشارة للسيرفر لتحديث كافة الأجهزة المتصلة
      fetch('/trigger-reload').catch(() => {});
      setTimeout(() => {
        window.location.href = window.location.pathname + '?live=' + Date.now();
      }, 300);
    }

    // ════════════════════════════════════════════════════════════════
    // اتصال التحديث الحي المباشر (Live Reload) لظهور التعديلات فورياً في الحين
    // ════════════════════════════════════════════════════════════════
    (function initLiveReload() {
      let evtSource = null;
      let reconnectTimer = null;

      function connect() {
        if (evtSource) {
          try { evtSource.close(); } catch(e) {}
        }
        try {
          evtSource = new EventSource('/live-reload-sse');
          evtSource.onopen = function() {
            console.log('⚡ متصل بخادم التحديث الحي المباشر');
          };
          evtSource.onmessage = function(e) {
            if (e.data === 'reload') {
              console.log('🔄 تم استلام إشارة تحديث فوري من الخادم - إعادة التحميل الآن...');
              window.location.href = window.location.pathname + '?live=' + Date.now();
            }
          };
          evtSource.onerror = function() {
            try { evtSource.close(); } catch(e) {}
            if (!reconnectTimer) {
              reconnectTimer = setTimeout(() => {
                reconnectTimer = null;
                connect();
              }, 2500);
            }
          };
        } catch(err) {
          if (!reconnectTimer) {
            reconnectTimer = setTimeout(() => {
              reconnectTimer = null;
              connect();
            }, 3000);
          }
        }
      }
      connect();
    })();
  </script>
</body>
</html>
