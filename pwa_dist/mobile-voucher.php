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

header("Cache-Control: private, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
    ob_start('ob_gzhandler');
}

require_once __DIR__ . '/db_bridge.php';
require_once __DIR__ . '/mobile_auth_guard.php';

$pdo = getDBConnection();
global $GLOBAL_TENANT_ID;

// ────────────────────────────────────────────────────────────────────────────
// المصادقة وتحديد الشركة: رقم الشركة يُؤخذ من حساب المستخدم الموثّق فقط
// ────────────────────────────────────────────────────────────────────────────
$isApiRequest = isset($_GET['api']) || isset($_POST['api']);
$requestedApiAction = $_GET['api'] ?? ($_POST['api'] ?? '');
$authUser = null;
if ($isApiRequest && $requestedApiAction === 'ping') {
    $authUser = mg_authenticate_user($pdo);
    if (!$authUser && mg_is_loopback_request()) {
        $authUser = ['id' => 0, 'tenant_id' => 1, 'username' => 'localhost-dev', 'role' => 'admin', 'source' => 'loopback-dev'];
    }
} else {
    $authUser = requireMobileAuth($pdo, $isApiRequest);
}
$currentTenantId = !empty($authUser['tenant_id']) ? (int)$authUser['tenant_id'] : (int)($GLOBAL_TENANT_ID ?: 12);
$GLOBAL_TENANT_ID = $currentTenantId;

// تحرير قفل الجلسة فوراً لطلبات القراءة لمنع تعليق الطلبات المتوازية على شبكات 2G
if (session_status() === PHP_SESSION_ACTIVE && (!$isApiRequest || in_array($requestedApiAction, ['ping', 'get_data', 'sync_data'], true))) {
    session_write_close();
}

// دالة فائقة السرعة (1ms) لحساب بصمة قاعدة البيانات عبر استعلام SQL واحد مجمّع دون فك تشفير الجداول
if (!function_exists('computeFastVoucherStateHash')) {
    function computeFastVoucherStateHash($pdo, $tenantId) {
        $effTenant = !empty($tenantId) ? (int)$tenantId : 12;
        try {
            $sql = "SELECT
                (SELECT CONCAT(COUNT(*), ':', COALESCE(MAX(id),0), ':', COALESCE(MAX(updated_at),'')) FROM safes WHERE tenant_id = ?) AS s,
                (SELECT CONCAT(COUNT(*), ':', COALESCE(MAX(id),0), ':', COALESCE(MAX(updated_at),'')) FROM banks WHERE tenant_id = ?) AS b,
                (SELECT CONCAT(COUNT(*), ':', COALESCE(MAX(id),0), ':', COALESCE(MAX(updated_at),'')) FROM customers WHERE tenant_id = ?) AS c,
                (SELECT CONCAT(COUNT(*), ':', COALESCE(MAX(id),0), ':', COALESCE(MAX(updated_at),'')) FROM suppliers WHERE tenant_id = ?) AS sp,
                (SELECT CONCAT(COUNT(*), ':', COALESCE(MAX(id),0), ':', COALESCE(MAX(updated_at),'')) FROM accounts WHERE tenant_id = ?) AS a,
                (SELECT CONCAT(COUNT(*), ':', COALESCE(MAX(id),0), ':', COALESCE(MAX(updated_at),'')) FROM single_payments WHERE tenant_id = ?) AS p,
                (SELECT CONCAT(COUNT(*), ':', COALESCE(MAX(id),0), ':', COALESCE(MAX(updated_at),'')) FROM single_receipts WHERE tenant_id = ?) AS r,
                (SELECT COALESCE(GROUP_CONCAT(setting_value SEPARATOR '|'), '') FROM global_settings WHERE setting_key IN ('exchange_rate', 'erp_rate_usd', 'erp_currency_rates_v1')) AS gs";
            $st = $pdo->prepare($sql);
            $st->execute([$effTenant, $effTenant, $effTenant, $effTenant, $effTenant, $effTenant, $effTenant]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return md5(implode('|', array_values($row)));
            }
        } catch (Throwable $e) {}
        return md5('fallback:' . $effTenant);
    }
}

// ────────────────────────────────────────────────────────────────────────────
// دالة الجلب الموحدة لجميع الحسابات الحية من قاعدة البيانات MariaDB
// تشمل: الموردين، العملاء والزبائن، المصروفات، البنوك، الخزن، وكل الحسابات
// ────────────────────────────────────────────────────────────────────────────
if (!function_exists('fetchAllLiveAccounts')) {
    function fetchAllLiveAccounts($pdo, $currentTenantId) {
        $accountsList = [];
        $effTenant = !empty($currentTenantId) ? (int)$currentTenantId : 12;

        $extractPhone = function($d, $r) {
            foreach (['phone', 'mobile', 'whatsapp', 'tel', 'phoneNumber', 'contactPhone'] as $k) {
                if (!empty($d[$k]) && is_scalar($d[$k])) return trim((string)$d[$k]);
                if (!empty($r[$k]) && is_scalar($r[$k])) return trim((string)$r[$k]);
            }
            return '';
        };

        // 1. الخزن النقدية (طبيعة مدينة - Asset/Debit)
        try {
            $stmtSafes = $pdo->prepare("SELECT * FROM safes WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
            $stmtSafes->execute([$effTenant]);
            while ($r = $stmtSafes->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'] ?? '{}', true) ?: $r;
                if (!empty($d['deleted'])) continue;
                $id = $r['id'] ?? ($d['id'] ?? 'safe_' . count($accountsList));
                $name = trim($d['name'] ?? ($r['name'] ?? 'خزينة نقدية'));
                if (!$name) continue;
                $bal = (float)($d['currentBalance'] ?? ($d['balance'] ?? ($r['balance'] ?? 0)));
                $accountsList[] = [
                    'id' => 'safe_' . $id,
                    'name' => $name,
                    'category' => 'الخزن',
                    'type' => 'safe',
                    'normalBalance' => 'debit',
                    'phone' => $extractPhone($d, $r),
                    'balance' => number_format($bal, 2) . ' ج.س',
                    'rawBalance' => $bal,
                    'icon' => '💵'
                ];
            }
        } catch(Exception $e) {}

        // 2. البنوك والمصارف (طبيعة مدينة - Asset/Debit)
        try {
            $stmtBanks = $pdo->prepare("SELECT * FROM banks WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
            $stmtBanks->execute([$effTenant]);
            while ($r = $stmtBanks->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'] ?? '{}', true) ?: $r;
                if (!empty($d['deleted'])) continue;
                $id = $r['id'] ?? ($d['id'] ?? 'bank_' . count($accountsList));
                $name = trim($d['name'] ?? ($r['name'] ?? 'حساب بنكي'));
                if (!$name) continue;
                $accNo = trim($d['accountNumber'] ?? ($d['accountNo'] ?? ($r['account_number'] ?? '')));
                $bal = (float)($d['currentBalance'] ?? ($d['balance'] ?? ($r['balance'] ?? 0)));
                $accountsList[] = [
                    'id' => 'bank_' . $id,
                    'name' => $name . ($accNo ? " ($accNo)" : ''),
                    'category' => 'البنوك',
                    'type' => 'bank',
                    'normalBalance' => 'debit',
                    'phone' => $extractPhone($d, $r),
                    'balance' => number_format($bal, 2) . ' ج.س',
                    'rawBalance' => $bal,
                    'icon' => '🏦'
                ];
            }
        } catch(Exception $e) {}

        // 3. العملاء والزبائن (طبيعة مدينة - Receivable/Debit)
        try {
            $stmtCust = $pdo->prepare("SELECT * FROM customers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
            $stmtCust->execute([$effTenant]);
            while ($r = $stmtCust->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'] ?? '{}', true) ?: $r;
                if (!empty($d['deleted'])) continue;
                $id = $r['id'] ?? ($d['id'] ?? 'cust_' . count($accountsList));
                $name = trim($d['name'] ?? ($r['name'] ?? ''));
                if (!$name) continue;
                $bal = (float)($d['currentBalance'] ?? ($d['balance'] ?? 0));
                $accountsList[] = [
                    'id' => 'cust_' . $id,
                    'name' => $name,
                    'category' => 'العملاء والزبائن',
                    'type' => 'customer',
                    'normalBalance' => 'debit',
                    'phone' => $extractPhone($d, $r),
                    'balance' => number_format($bal, 2) . ' ج.س',
                    'rawBalance' => $bal,
                    'icon' => '👤'
                ];
            }
        } catch(Exception $e) {}

        // 4. الموردين (طبيعة دائنة - Payable/Credit)
        try {
            $stmtSupp = $pdo->prepare("SELECT * FROM suppliers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
            $stmtSupp->execute([$effTenant]);
            while ($r = $stmtSupp->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'] ?? '{}', true) ?: $r;
                if (!empty($d['deleted'])) continue;
                $id = $r['id'] ?? ($d['id'] ?? 'supp_' . count($accountsList));
                $name = trim($d['name'] ?? ($r['name'] ?? ''));
                if (!$name) continue;
                $bal = (float)($d['currentBalance'] ?? ($d['balance'] ?? 0));
                $accountsList[] = [
                    'id' => 'supp_' . $id,
                    'name' => $name,
                    'category' => 'الموردين',
                    'type' => 'supplier',
                    'normalBalance' => 'credit',
                    'phone' => $extractPhone($d, $r),
                    'balance' => number_format($bal, 2) . ' ج.س',
                    'rawBalance' => $bal,
                    'icon' => '🏭'
                ];
            }
        } catch(Exception $e) {}

        // 5. دليل الحسابات العام والمصروفات
        try {
            $stmtAcc = $pdo->prepare("SELECT * FROM accounts WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
            $stmtAcc->execute([$effTenant]);
            while ($r = $stmtAcc->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'] ?? '{}', true) ?: $r;
                if (!empty($d['deleted'])) continue;
                $id = $r['id'] ?? ($d['id'] ?? '');
                $name = trim($d['name'] ?? ($r['name'] ?? ''));
                if (!$name) continue;
                $type = $d['type'] ?? ($r['type'] ?? 'general');
                $bal = (float)($d['currentBalance'] ?? ($d['balance'] ?? 0));

                $isExpense = (stripos($type, 'expense') !== false || stripos($name, 'مصروف') !== false || stripos($name, 'إيجار') !== false || stripos($name, 'مرتب') !== false || stripos($name, 'صيانة') !== false);
                $isCreditNature = (stripos($type, 'liability') !== false || stripos($type, 'revenue') !== false || stripos($type, 'equity') !== false || stripos($name, 'إيراد') !== false || stripos($name, 'مبيعات') !== false);
                $catLabel = $isExpense ? 'المصروفات' : 'كل الحسابات';
                $icon = $isExpense ? '📉' : '📑';

                $accountsList[] = [
                    'id' => 'acc_' . $id,
                    'name' => $name,
                    'category' => $catLabel,
                    'type' => $isExpense ? 'expense' : 'general',
                    'normalBalance' => $isCreditNature ? 'credit' : 'debit',
                    'phone' => $extractPhone($d, $r),
                    'balance' => number_format($bal, 2) . ' ج.س',
                    'rawBalance' => $bal,
                    'icon' => $icon
                ];
            }
        } catch(Exception $e) {}

        // 6. قائمة بنود المصروفات المحاسبية الأساسية القياسية (المصروفات)
        $standardExpenses = [
            ['id' => 'exp_general', 'name' => 'مصروفات عمومية وإدارية', 'category' => 'المصروفات', 'type' => 'expense', 'normalBalance' => 'debit', 'phone' => '', 'balance' => '0.00 ج.س', 'rawBalance' => 0, 'icon' => '📉'],
            ['id' => 'exp_salaries', 'name' => 'مرتبات وأجور العاملين', 'category' => 'المصروفات', 'type' => 'expense', 'normalBalance' => 'debit', 'phone' => '', 'balance' => '0.00 ج.س', 'rawBalance' => 0, 'icon' => '👥'],
            ['id' => 'exp_rent', 'name' => 'إيجار مقرات ومخازن', 'category' => 'المصروفات', 'type' => 'expense', 'normalBalance' => 'debit', 'phone' => '', 'balance' => '0.00 ج.س', 'rawBalance' => 0, 'icon' => '🏢'],
            ['id' => 'exp_utilities', 'name' => 'كهرباء ومياه وإنترنت', 'category' => 'المصروفات', 'type' => 'expense', 'normalBalance' => 'debit', 'phone' => '', 'balance' => '0.00 ج.س', 'rawBalance' => 0, 'icon' => '⚡'],
            ['id' => 'exp_fuel', 'name' => 'وقود ونقليات وترحيل', 'category' => 'المصروفات', 'type' => 'expense', 'normalBalance' => 'debit', 'phone' => '', 'balance' => '0.00 ج.س', 'rawBalance' => 0, 'icon' => '🚚'],
            ['id' => 'exp_hospitality', 'name' => 'نثريات وضيافة', 'category' => 'المصروفات', 'type' => 'expense', 'normalBalance' => 'debit', 'phone' => '', 'balance' => '0.00 ج.س', 'rawBalance' => 0, 'icon' => '☕'],
            ['id' => 'exp_maintenance', 'name' => 'صيانة وتشغيل', 'category' => 'المصروفات', 'type' => 'expense', 'normalBalance' => 'debit', 'phone' => '', 'balance' => '0.00 ج.س', 'rawBalance' => 0, 'icon' => '🔧'],
            ['id' => 'exp_purchases', 'name' => 'مشتريات بضاعة نقدية', 'category' => 'المصروفات', 'type' => 'expense', 'normalBalance' => 'debit', 'phone' => '', 'balance' => '0.00 ج.س', 'rawBalance' => 0, 'icon' => '🛒'],
            ['id' => 'exp_taxes', 'name' => 'ضرائب ورسوم حكومية', 'category' => 'المصروفات', 'type' => 'expense', 'normalBalance' => 'debit', 'phone' => '', 'balance' => '0.00 ج.س', 'rawBalance' => 0, 'icon' => '🏛️'],
            ['id' => 'exp_marketing', 'name' => 'دعاية وإعلان وتسويق', 'category' => 'المصروفات', 'type' => 'expense', 'normalBalance' => 'debit', 'phone' => '', 'balance' => '0.00 ج.س', 'rawBalance' => 0, 'icon' => '📢'],
            ['id' => 'exp_bank_charges', 'name' => 'عمولات ومصروفات بنكية', 'category' => 'المصروفات', 'type' => 'expense', 'normalBalance' => 'debit', 'phone' => '', 'balance' => '0.00 ج.س', 'rawBalance' => 0, 'icon' => '💳'],
            ['id' => 'exp_other', 'name' => 'مصروفات متنوعة أخرى', 'category' => 'المصروفات', 'type' => 'expense', 'normalBalance' => 'debit', 'phone' => '', 'balance' => '0.00 ج.س', 'rawBalance' => 0, 'icon' => '📌']
        ];

        $existingNames = array_column($accountsList, 'name');
        foreach ($standardExpenses as $se) {
            if (!in_array($se['name'], $existingNames)) {
                $accountsList[] = $se;
            }
        }

        return $accountsList;
    }
}

// ────────────────────────────────────────────────────────────────────────────
// الدوال المساعدة لفحص وتتبع وتدقيق أرقام التحويلات في قاعدة البيانات MariaDB
// ────────────────────────────────────────────────────────────────────────────
if (!function_exists('normalizeTransferRefStrict')) {
    function normalizeTransferRefStrict($val) {
        $s = trim((string)$val);
        if ($s === '') return '';
        $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩','۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $latin  = ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'];
        $s = str_replace($arabic, $latin, $s);
        if (preg_match('/^(OUT-OFF-|IN-OFF-|OFF-|WA\d|AUTO-|TEMP-|null|none|n\/a)/i', $s)) return '';
        if (preg_match('/^\d{4}\s+\d{4}(?:\s+\d{4}){1,2}$/', $s)) return '';
        $digits = preg_replace('/\D/', '', $s);
        if (strlen($digits) < 6) return '';
        if (strlen($digits) === 14 && $digits[0] === '0' && preg_match('/000[1-9]$/', $digits)) return '';
        if (strlen($digits) === 21 && substr($digits, 0, 2) === '02') return '';
        if (strlen($digits) === 16 && $digits[0] === '0' && preg_match('/(0001|0002|0101|5001)$/', $digits)) return '';
        return $digits;
    }
}

if (!function_exists('extractAllTransfersFromRecord')) {
    function extractAllTransfersFromRecord($d) {
        if (!is_array($d) || (!empty($d['deleted']) && $d['deleted'] !== 'false' && $d['deleted'] !== '0')) return [];
        $refs = [];
        if (!empty($d['mobileTransferNo']) && is_string($d['mobileTransferNo'])) $refs[] = trim($d['mobileTransferNo']);
        if (!empty($d['transferNo']) && is_string($d['transferNo'])) $refs[] = trim($d['transferNo']);
        if (!empty($d['transferRef']) && is_string($d['transferRef'])) $refs[] = trim($d['transferRef']);
        if (!empty($d['refNo']) && is_string($d['refNo']) && !preg_match('/^(OUT-OFF-|IN-OFF-|OFF-)/i', trim($d['refNo']))) {
            $refs[] = trim($d['refNo']);
        }
        if (!empty($d['bankTransfers']) && is_array($d['bankTransfers'])) {
            foreach ($d['bankTransfers'] as $bt) {
                if (!empty($bt['transferNo']) && is_string($bt['transferNo'])) $refs[] = trim($bt['transferNo']);
            }
        }
        if (!empty($d['details']) && is_array($d['details'])) {
            foreach ($d['details'] as $det) {
                if (!empty($det['transferNo']) && is_string($det['transferNo'])) $refs[] = trim($det['transferNo']);
                if (!empty($det['bankTransfers']) && is_array($det['bankTransfers'])) {
                    foreach ($det['bankTransfers'] as $bt) {
                        if (!empty($bt['transferNo']) && is_string($bt['transferNo'])) $refs[] = trim($bt['transferNo']);
                    }
                }
            }
        }
        if (!empty($d['items']) && is_array($d['items'])) {
            foreach ($d['items'] as $item) {
                if (!empty($item['transferNo']) && is_string($item['transferNo'])) $refs[] = trim($item['transferNo']);
                if (!empty($item['bankTransfers']) && is_array($item['bankTransfers'])) {
                    foreach ($item['bankTransfers'] as $bt) {
                        if (!empty($bt['transferNo']) && is_string($bt['transferNo'])) $refs[] = trim($bt['transferNo']);
                    }
                }
            }
        }
        $valid = [];
        foreach ($refs as $r) {
            $norm = normalizeTransferRefStrict($r);
            if ($norm !== '') $valid[] = $norm;
        }
        return array_values(array_unique($valid));
    }
}

if (!function_exists('fetchAllDbTransfers')) {
    function fetchAllDbTransfers($pdo, $currentTenantId = null) {
        $results = [];
        $seen = [];
        $effTenant = !empty($currentTenantId) ? (int)$currentTenantId : 12;
        $tables = ['single_payments', 'single_receipts', 'payments', 'receipts'];
        foreach ($tables as $tbl) {
            try {
                $stmt = $pdo->prepare("SELECT id, tenant_id, data FROM `$tbl` WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmt->execute([$effTenant]);
                while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $d = json_decode($r['data'], true);
                    if (!$d || !empty($d['deleted'])) continue;
                    $vNo = trim((string)($d['voucherNo'] ?? ($d['id'] ?? '')));
                    $refs = extractAllTransfersFromRecord($d);
                    foreach ($refs as $ref) {
                        $clean = normalizeTransferRefStrict($ref);
                        if ($clean === '') continue;
                        $key = $vNo . '_' . $clean;
                        if (isset($seen[$key])) continue;
                        $seen[$key] = true;
                        $results[] = [
                            'voucherNo' => $vNo,
                            'table' => $tbl,
                            'tenantId' => $r['tenant_id'],
                            'ref' => $clean,
                            'cleanRef' => $clean,
                            'date' => $d['date'] ?? '',
                            'amount' => (float)($d['amountLocal'] ?? ($d['amount'] ?? 0)),
                            'bank' => $d['bankName'] ?? ($d['bank'] ?? ''),
                            'from' => $d['fromAccountName'] ?? ($d['from'] ?? ''),
                            'to' => $d['toAccountName'] ?? ($d['to'] ?? ($d['beneficiaryName'] ?? ''))
                        ];
                    }
                }
            } catch(Throwable $e) {}
        }
        return $results;
    }
}

if (!function_exists('findTransferDuplicateInDb')) {
    function findTransferDuplicateInDb($pdo, $ref, $currentVNo = '', $currentTenantId = null, $ignoreSoftSimilarity = false) {
        $cleanRef = normalizeTransferRefStrict($ref);
        if ($cleanRef === '') return null;
        $compLen = strlen($cleanRef);
        $effTenant = !empty($currentTenantId) ? (int)$currentTenantId : 12;

        $softCandidate = null;
        $tables = ['single_payments', 'single_receipts', 'payments', 'receipts'];
        foreach ($tables as $tbl) {
            try {
                $stmt = $pdo->prepare("SELECT id, tenant_id, data FROM `$tbl` WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmt->execute([$effTenant]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $d = json_decode($row['data'], true);
                    if (!$d || !empty($d['deleted'])) continue;
                    $existingVNo = trim((string)($d['voucherNo'] ?? ($d['id'] ?? '')));
                    if ($existingVNo !== '' && $currentVNo !== '' && $existingVNo === $currentVNo) {
                        continue;
                    }
                    $candidates = extractAllTransfersFromRecord($d);
                    foreach ($candidates as $cr) {
                        $cleanCr = normalizeTransferRefStrict($cr);
                        if ($cleanCr === '') continue;

                        $matched = false;
                        $matchType = '';
                        $severity = 'hard';

                        // 1. التطابق التام (حظر قاطع Hard Block)
                        if ($cleanCr === $cleanRef) {
                            $matched = true;
                            $matchType = 'تطابق تام';
                            $severity = 'hard';
                        }
                        // 2. تطابق آخر 8 أرقام (أو آخر 6 أرقام لنفس البنك)
                        elseif ($compLen >= 9 && strlen($cleanCr) >= 9 && (substr($cleanCr, -8) === substr($cleanRef, -8) || (substr($cleanCr, -6) === substr($cleanRef, -6) && substr($cleanCr, 0, 2) === substr($cleanRef, 0, 2)))) {
                            $matched = true;
                            $matchType = 'تطابق رقم العملية البنكية';
                            $severity = 'hard';
                        }
                        elseif ($compLen !== strlen($cleanCr) && min($compLen, strlen($cleanCr)) >= 6 && substr(($compLen > strlen($cleanCr) ? $cleanRef : $cleanCr), -min($compLen, strlen($cleanCr))) === ($compLen < strlen($cleanCr) ? $cleanRef : $cleanCr)) {
                            $matched = true;
                            $matchType = 'تطابق لاحقة رقم العملية';
                            $severity = 'hard';
                        }
                        // 4. تشابه رقمي بفرق خانة واحدة فقط (تنبيه ذكي قابل للتأكيد Soft Warning)
                        elseif (!$ignoreSoftSimilarity && $compLen >= 6 && abs($compLen - strlen($cleanCr)) <= 1 && levenshtein($cleanRef, $cleanCr) <= 1) {
                            $matched = true;
                            $matchType = 'تشابه رقمي (فرق رقم واحد)';
                            $severity = 'soft';
                        }

                        if ($matched) {
                            $matchObj = [
                                'voucherNo' => $existingVNo ?: 'سابق',
                                'ref' => $strCr,
                                'date' => $d['date'] ?? '',
                                'amount' => $d['amountLocal'] ?? ($d['amount'] ?? 0),
                                'bank' => $d['bankName'] ?? ($d['bank'] ?? ''),
                                'from' => $d['fromAccountName'] ?? ($d['from'] ?? ''),
                                'to' => $d['toAccountName'] ?? ($d['to'] ?? ($d['beneficiaryName'] ?? '')),
                                'table' => $tbl,
                                'matchType' => $matchType,
                                'severity' => $severity
                            ];
                            if ($severity === 'hard') {
                                return $matchObj;
                            }
                            if (!$softCandidate) {
                                $softCandidate = $matchObj;
                            }
                        }
                    }
                }
            } catch(Throwable $e) {}
        }
        return $softCandidate;
    }
}

// ────────────────────────────────────────────────────────────────────────────
// محرك الرقابة المحاسبية المزدوجة (Double-Entry GL, Balances, Fiscal Lock, Signature)
// ────────────────────────────────────────────────────────────────────────────
if (!function_exists('classifyVoucherNature')) {
    function classifyVoucherNature($fromName, $toName, $accountsList = [], $fromId = '', $toId = '') {
        static $cachedMap = null;
        static $cachedCount = -1;
        if (!empty($accountsList) && count($accountsList) !== $cachedCount) {
            $cachedMap = [];
            foreach ($accountsList as $a) {
                $n = trim((string)($a['name'] ?? ''));
                if ($n !== '') $cachedMap[$n] = $a['type'] ?? 'general';
            }
            $cachedCount = count($accountsList);
        }
        $findType = function($name, $accId = '') use (&$cachedMap) {
            $idStr = trim((string)$accId);
            if ($idStr !== '') {
                if (strpos($idStr, 'safe_') === 0) return 'safe';
                if (strpos($idStr, 'bank_') === 0) return 'bank';
                if (strpos($idStr, 'exp_') === 0) return 'expense';
                if (strpos($idStr, 'cust_') === 0 || strpos($idStr, 'supp_') === 0) return 'party';
            }
            $clean = trim((string)$name);
            if ($cachedMap !== null && isset($cachedMap[$clean])) {
                return $cachedMap[$clean];
            }
            if (strpos($clean, 'خزينة') !== false || strpos($clean, 'صندوق') !== false || strpos($clean, 'كاش') !== false) return 'safe';
            if (strpos($clean, 'بنك') !== false || strpos($clean, 'مصرف') !== false) return 'bank';
            if (strpos($clean, 'مصروف') !== false || strpos($clean, 'إيجار') !== false || strpos($clean, 'مرتب') !== false) return 'expense';
            return 'party';
        };
        $fromType = $findType($fromName, $fromId);
        $toType = $findType($toName, $toId);
        $fromIsLiquid = in_array($fromType, ['safe', 'bank'], true);
        $toIsLiquid = in_array($toType, ['safe', 'bank'], true);

        if ($fromIsLiquid && $toIsLiquid) {
            return ['code' => 'internal_transfer', 'label' => 'تحويل داخلي'];
        }
        if (!$fromIsLiquid && $toIsLiquid) {
            return ['code' => 'receipt', 'label' => 'سند قبض'];
        }
        if ($fromIsLiquid && !$toIsLiquid) {
            return ['code' => 'payment', 'label' => 'سند صرف'];
        }
        return ['code' => 'settlement', 'label' => 'حركة مالية'];
    }
}

if (!function_exists('checkFiscalPeriodOpen')) {
    function checkFiscalPeriodOpen($pdo, $tenantId, $voucherDate) {
        if (empty($voucherDate)) return null;
        try {
            $stmt = $pdo->prepare("SELECT name, start_date, end_date FROM financial_periods WHERE tenant_id = ? AND is_closed = 1 AND ? BETWEEN start_date AND end_date LIMIT 1");
            $stmt->execute([(int)$tenantId, $voucherDate]);
            $closed = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($closed) {
                $pName = $closed['name'] ?: ($closed['start_date'] . ' إلى ' . $closed['end_date']);
                return "لا يمكن الحفظ أو التعديل: الفترة المالية ({$pName}) مغلقة محاسبياً ومعتمدة.";
            }
        } catch (Throwable $e) {}
        return null;
    }
}

if (!function_exists('mutateSingleAccountBalance')) {
    function mutateSingleAccountBalance($pdo, $tenantId, $accountName, $deltaSdg) {
        $accountName = trim((string)$accountName);
        $deltaSdg = (float)$deltaSdg;
        if ($accountName === '' || abs($deltaSdg) < 0.0001) return null;

        // إزالة رقم الحساب البنكي بين قوسين إن وجد عند البحث في جدول البنوك
        $baseName = trim(preg_replace('/\s*\([^)]*\)\s*$/u', '', $accountName));
        $tables = ['safes', 'banks', 'customers', 'suppliers', 'accounts'];

        foreach ($tables as $tbl) {
            try {
                $stmt = $pdo->prepare("SELECT id, data FROM `$tbl` WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmt->execute([(int)$tenantId]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $d = json_decode($row['data'] ?? '{}', true);
                    if (!is_array($d) || !empty($d['deleted'])) continue;
                    $rName = trim((string)($d['name'] ?? ''));
                    if ($rName === '') continue;

                    if ($rName === $accountName || $rName === $baseName) {
                        $oldBal = (float)($d['currentBalance'] ?? ($d['balance'] ?? 0));
                        $newBal = round($oldBal + $deltaSdg, 2);
                        $d['currentBalance'] = $newBal;
                        $d['balance'] = $newBal;
                        $d['updatedAt'] = date('c');
                        $upStmt = $pdo->prepare("UPDATE `$tbl` SET data = ?, updated_at = NOW() WHERE id = ? AND tenant_id = ?");
                        $upStmt->execute([json_encode($d, JSON_UNESCAPED_UNICODE), $row['id'], (int)$tenantId]);
                        return ['table' => $tbl, 'id' => $row['id'], 'oldBalance' => $oldBal, 'newBalance' => $newBal];
                    }
                }
            } catch (Throwable $e) {}
        }
        return null;
    }
}

if (!function_exists('applyVoucherAccountBalances')) {
    function applyVoucherAccountBalances($pdo, $tenantId, $fromName, $toName, $amountSdg, $isReverse = false) {
        $amt = (float)$amountSdg;
        if ($amt <= 0) return [];
        $sign = $isReverse ? -1.0 : 1.0;
        // الطرف الدافع (من حساب - الدائن): ينقص رصيده (-amt)
        // الطرف المستفيد (إلى حساب - المدين): يزيد رصيده (+amt)
        $fromRes = mutateSingleAccountBalance($pdo, $tenantId, $fromName, -1.0 * $amt * $sign);
        $toRes   = mutateSingleAccountBalance($pdo, $tenantId, $toName, +1.0 * $amt * $sign);
        return ['from' => $fromRes, 'to' => $toRes];
    }
}

if (!function_exists('syncDoubleEntryJournal')) {
    function syncDoubleEntryJournal($pdo, $tenantId, $voucherRecord, $isDelete = false) {
        $vNo = trim((string)($voucherRecord['voucherNo'] ?? ($voucherRecord['id'] ?? '')));
        if ($vNo === '') return;
        $glId = 'GL-' . $vNo;
        try {
            $delStmt = $pdo->prepare("DELETE FROM gl_entries WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.voucherNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
            $delStmt->execute([(int)$tenantId, $vNo, $glId]);
            if ($isDelete) return;

            $amtLocal = (float)($voucherRecord['amountLocal'] ?? ($voucherRecord['amount'] ?? 0));
            $fromName = $voucherRecord['fromAccountName'] ?? '';
            $toName   = $voucherRecord['toAccountName'] ?? '';
            $glPayload = [
                'id' => $glId,
                'voucherNo' => $vNo,
                'date' => $voucherRecord['date'] ?? date('Y-m-d'),
                'voucherNature' => $voucherRecord['voucherNature'] ?? 'payment',
                'voucherNatureLabel' => $voucherRecord['voucherNatureLabel'] ?? 'سند حركة مالية',
                'description' => $voucherRecord['notes'] ?: ("سند حركة مالية #{$vNo} - من {$fromName} إلى {$toName}"),
                'currency' => $voucherRecord['currency'] ?? 'SDG',
                'rate' => (float)($voucherRecord['rate'] ?? 1),
                'costCenter' => $voucherRecord['costCenter'] ?? '',
                'totalDebit' => $amtLocal,
                'totalCredit' => $amtLocal,
                'isBalanced' => true,
                'lines' => [
                    [
                        'accountName' => $toName,
                        'accountId' => $voucherRecord['toAccount'] ?? '',
                        'debit' => $amtLocal,
                        'credit' => 0,
                        'memo' => 'طرف مدين (إلى حساب المستفيد)'
                    ],
                    [
                        'accountName' => $fromName,
                        'accountId' => $voucherRecord['fromAccount'] ?? '',
                        'debit' => 0,
                        'credit' => $amtLocal,
                        'memo' => 'طرف دائن (من حساب الدافع)'
                    ]
                ],
                'createdAt' => date('c')
            ];
            $insStmt = $pdo->prepare("INSERT INTO gl_entries (tenant_id, data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
            $insStmt->execute([(int)$tenantId, json_encode($glPayload, JSON_UNESCAPED_UNICODE)]);
        } catch (Throwable $e) {}
    }
}

if (!function_exists('recordVoucherAuditLog')) {
    function recordVoucherAuditLog($pdo, $tenantId, $userId, $actionType, $voucherNo, $beforeData = null, $afterData = null) {
        try {
            $logPayload = [
                'id' => 'AUD-' . strtoupper(bin2hex(random_bytes(5))),
                'entity' => 'single_payments',
                'voucherNo' => $voucherNo,
                'action' => $actionType,
                'userId' => $userId,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'userAgent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 180),
                'before' => $beforeData,
                'after' => $afterData,
                'timestamp' => date('c')
            ];
            $stmt = $pdo->prepare("INSERT INTO audit_logs (tenant_id, data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
            $stmt->execute([(int)$tenantId, json_encode($logPayload, JSON_UNESCAPED_UNICODE)]);
        } catch (Throwable $e) {}
    }
}

if (!function_exists('updateLinkedInvoiceSettlement')) {
    function updateLinkedInvoiceSettlement($pdo, $tenantId, $linkedInvoiceNo, $deltaPaidSdg) {
        $invNo = trim((string)$linkedInvoiceNo);
        $delta = (float)$deltaPaidSdg;
        if ($invNo === '' || abs($delta) < 0.001) return null;

        foreach (['sales_invoices', 'purchase_invoices'] as $tbl) {
            try {
                $stmt = $pdo->prepare("SELECT id, data, total_val FROM `$tbl` WHERE tenant_id = ? AND (invoice_no = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                $stmt->execute([(int)$tenantId, $invNo, $invNo, $invNo]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $d = json_decode($row['data'] ?? '{}', true) ?: [];
                    $total = (float)($d['total'] ?? ($d['netTotal'] ?? ($row['total_val'] ?? 0)));
                    $oldPaid = (float)($d['paidAmount'] ?? ($d['paid'] ?? 0));
                    $newPaid = max(0, round($oldPaid + $delta, 2));
                    $remaining = max(0, round($total - $newPaid, 2));
                    $status = ($total > 0 && $newPaid >= $total) ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid');
                    $d['paidAmount'] = $newPaid;
                    $d['paid'] = $newPaid;
                    $d['remainingAmount'] = $remaining;
                    $d['paymentStatus'] = $status;
                    $d['status'] = $status;
                    $d['lastPaymentAt'] = date('c');

                    $up = $pdo->prepare("UPDATE `$tbl` SET data = ?, updated_at = NOW() WHERE id = ? AND tenant_id = ?");
                    $up->execute([json_encode($d, JSON_UNESCAPED_UNICODE), $row['id'], (int)$tenantId]);
                    return ['table' => $tbl, 'invoiceNo' => $invNo, 'paidAmount' => $newPaid, 'remainingAmount' => $remaining, 'status' => $status];
                }
            } catch (Throwable $e) {}
        }
        return null;
    }
}

if (!function_exists('generateServerVoucherSignature')) {
    function generateServerVoucherSignature($voucherNo, $date, $amountLocal, $fromName, $toName, $tenantId) {
        $secret = getenv('MAGAIEES_VOUCHER_HMAC_KEY') ?: ('ELSAJANA_SOVEREIGN_HMAC_2026_TENANT_' . (int)$tenantId);
        $canonical = implode('|', [
            trim((string)$voucherNo),
            trim((string)$date),
            number_format((float)$amountLocal, 2, '.', ''),
            trim((string)$fromName),
            trim((string)$toName),
            (int)$tenantId
        ]);
        $rawHmac = strtoupper(hash_hmac('sha256', $canonical, $secret));
        return 'VER-' . substr($rawHmac, 0, 4) . '-' . substr($rawHmac, 4, 4) . '-' . substr($rawHmac, 8, 6);
    }
}

// ────────────────────────────────────────────────────────────────────────────
// 1. API BACKEND ENDPOINTS (AJAX: Save, Delete, Check Duplicate, Verify, Statement, Sync)
// ────────────────────────────────────────────────────────────────────────────
if (isset($_GET['api']) || isset($_POST['api'])) {
    header('Content-Type: application/json; charset=UTF-8');
    $action = $_GET['api'] ?? ($_POST['api'] ?? '');

    // 1.1 فحص رقم التحويلة البنكية ومطابقة قاعدة البيانات MariaDB فورياً وسريعاً جداً
    if ($action === 'check_duplicate') {
        $ref = trim($_GET['ref'] ?? ($_POST['ref'] ?? ''));
        $currentVNo = trim($_GET['voucherNo'] ?? ($_POST['voucherNo'] ?? ''));

        if ($ref === '') {
            echo json_encode(['valid' => true, 'duplicate' => false, 'message' => 'رقم فارغ']);
            exit;
        }

        $cleanRef = preg_replace('/\D/', '', $ref);
        $refLen = strlen($cleanRef ?: $ref);
        $isShort = ($refLen < 6);

        $dup = findTransferDuplicateInDb($pdo, $ref, $currentVNo, $currentTenantId, false);

        if ($dup) {
            $isSoft = (($dup['severity'] ?? 'hard') === 'soft');
            $msg = $isSoft
                ? "تنبيه تشابه رقمي: رقم التحويلة ({$ref}) يتشابه بفرق رقم واحد مع التحويلة ({$dup['ref']}) في السند ({$dup['voucherNo']})."
                : "عفواً، رقم التحويلة ({$ref}) مكرر ومسجل مسبقاً في السند رقم ({$dup['voucherNo']})";
            if (!empty($dup['date'])) $msg .= " بتاريخ ({$dup['date']})";
            if (!empty($dup['bank'])) $msg .= " لدى ({$dup['bank']})";
            $msg .= " ({$dup['matchType']}).";

            echo json_encode([
                'valid' => false,
                'duplicate' => true,
                'severity' => $dup['severity'] ?? 'hard',
                'isShort' => $isShort,
                'error' => $msg,
                'matchedVoucher' => $dup['voucherNo'],
                'matchedRef' => $dup['ref'],
                'matchedDate' => $dup['date'],
                'matchedAmount' => $dup['amount'],
                'matchedBank' => $dup['bank'],
                'matchedFrom' => $dup['from'],
                'matchedTo' => $dup['to'],
                'matchType' => $dup['matchType']
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'valid' => true,
                'duplicate' => false,
                'isShort' => $isShort,
                'message' => 'رقم التحويلة سليم وغير مسجل مسبقاً في قاعدة البيانات'
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // 1.2 حفظ أو تعديل السند المالي مباشرة في MariaDB مع القيد المزدوج وتحديث الأرصدة
    if ($action === 'save_voucher') {
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true) ?: $_POST;

        if (!$payload) {
            echo json_encode(['success' => false, 'error' => 'بيانات السند غير صالحة']);
            exit;
        }

        $isNew = empty($payload['isEditing']) || strpos((string)($payload['voucherNo'] ?? ''), 'أوفلاين') !== false;
        $idempotencyKey = trim($payload['idempotencyKey'] ?? ($payload['client_draft_id'] ?? ''));

        // فحص مفتاح منع تكرار النقر المزدوج (Idempotency Protection)
        if ($isNew && !empty($idempotencyKey)) {
            $stmtCheckIdem = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ? AND JSON_UNQUOTE(JSON_EXTRACT(data, '$.idempotencyKey')) = ? LIMIT 1");
            $stmtCheckIdem->execute([$currentTenantId, $idempotencyKey]);
            $existingRow = $stmtCheckIdem->fetch(PDO::FETCH_ASSOC);
            if ($existingRow) {
                $existingRecord = json_decode($existingRow['data'], true);
                echo json_encode([
                    'success' => true,
                    'voucherNo' => $existingRecord['voucherNo'] ?? ($existingRecord['id'] ?? ''),
                    'record' => $existingRecord,
                    'idempotent' => true,
                    'message' => 'تم استرجاع السند المعتمد مسبقاً لمنع التكرار (Idempotency Protection)'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        $voucherDate = trim($payload['date'] ?? date('Y-m-d'));
        $periodLockError = checkFiscalPeriodOpen($pdo, $currentTenantId, $voucherDate);
        if ($periodLockError) {
            echo json_encode(['success' => false, 'error' => $periodLockError], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $bt0 = (!empty($payload['bankTransfers']) && is_array($payload['bankTransfers']) && isset($payload['bankTransfers'][0]) && is_array($payload['bankTransfers'][0])) ? $payload['bankTransfers'][0] : [];
        $ch0 = (!empty($payload['cheques']) && is_array($payload['cheques']) && isset($payload['cheques'][0]) && is_array($payload['cheques'][0])) ? $payload['cheques'][0] : [];

        $amt = (float)($payload['amountLocal'] ?? ($payload['sdg'] ?? ($payload['amount'] ?? ($payload['totalAmount'] ?? ($bt0['amount'] ?? ($ch0['amount'] ?? 0))))));
        $foreignAmt = (float)($payload['amountForeign'] ?? ($payload['usd'] ?? ($payload['foreignAmt'] ?? 0)));
        $rate = (float)($payload['rate'] ?? ($payload['exchangeRate'] ?? 2650));
        if ($rate <= 0) $rate = 2650;
        if ($amt <= 0 && $foreignAmt > 0 && $rate > 0) {
            $amt = round($foreignAmt * $rate, 2);
        } elseif ($foreignAmt <= 0 && $amt > 0 && $rate > 0) {
            $foreignAmt = round($amt / $rate, 2);
        }
        $transferRef = trim((string)($payload['mobileTransferNo'] ?? ($payload['refNo'] ?? ($payload['transferNo'] ?? ($payload['transferRef'] ?? ($payload['checkNo'] ?? ($bt0['transferNo'] ?? ($ch0['checkNo'] ?? ''))))))));
        $payMethod = $payload['paymentMethod'] ?? ($payload['payType'] ?? ($payload['method'] ?? 'transfer'));
        $currency = $payload['currency'] ?? 'SDG';
        $costCenter = trim((string)($payload['costCenter'] ?? ($payload['jobId'] ?? '')));
        $linkedInvoiceNo = trim((string)($payload['linkedInvoiceNo'] ?? ($payload['invoiceRef'] ?? '')));
        $confirmSimilarTransfer = !empty($payload['confirmSimilarTransfer']);

        if ($amt <= 0 && $foreignAmt <= 0) {
            echo json_encode(['success' => false, 'error' => 'يرجى إدخال مبلغ صحيح أكبر من الصفر']);
            exit;
        }

        $bankName = trim((string)($payload['bankName'] ?? ($payload['bank'] ?? ($payload['mobileNote'] ?? ($payload['checkBranch'] ?? ($bt0['bankName'] ?? ($bt0['note'] ?? ($ch0['checkBranch'] ?? ''))))))));
        $fromAccName = trim((string)($payload['fromAccountName'] ?? ($payload['fromName'] ?? ($payload['from'] ?? ($payload['fromAccount'] ?? '')))));
        $toAccName = trim((string)($payload['toAccountName'] ?? ($payload['toName'] ?? ($payload['to'] ?? ($payload['toAccount'] ?? ($payload['beneficiaryName'] ?? ''))))));
        $notesText = trim((string)($payload['notes'] ?? ($payload['toStatement'] ?? ($payload['fromStatement'] ?? ($payload['statement'] ?? ($payload['description'] ?? ($bt0['statement'] ?? '')))))));

        if (empty($fromAccName)) {
            echo json_encode(['success' => false, 'error' => 'يرجى اختيار حساب الدفع (الدافع)'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (empty($toAccName)) {
            echo json_encode(['success' => false, 'error' => 'يرجى اختيار الحساب المستفيد'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($payMethod === 'transfer') {
            if (empty($transferRef)) {
                echo json_encode(['success' => false, 'error' => 'يرجى إدخال رقم حساب التحويل'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $cleanTrLen = strlen(preg_replace('/\D/', '', $transferRef) ?: $transferRef);
            if ($cleanTrLen < 3) {
                echo json_encode(['success' => false, 'error' => 'رقم التحويلة قصير جداً! يرجى التأكد من كتابة رقم التحويل بشكل صحيح.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            if (empty($bankName)) {
                echo json_encode(['success' => false, 'error' => 'اسم البنك إجباري، يرجى كتابة اسم البنك'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $currentVNo = trim($payload['voucherNo'] ?? ($payload['id'] ?? ''));
            $dup = findTransferDuplicateInDb($pdo, $transferRef, $currentVNo, $currentTenantId, $confirmSimilarTransfer);
            if ($dup) {
                echo json_encode([
                    'success' => false,
                    'duplicate' => true,
                    'severity' => $dup['severity'] ?? 'hard',
                    'matchedVoucher' => $dup['voucherNo'],
                    'matchedRef' => $dup['ref'],
                    'matchedDate' => $dup['date'],
                    'matchedAmount' => $dup['amount'],
                    'matchedBank' => $dup['bank'],
                    'matchType' => $dup['matchType'],
                    'error' => "تحويلة مكررة: رقم التحويلة ({$transferRef}) تم تسجيله مسبقاً في النظام بالسند رقم ({$dup['voucherNo']})" . (!empty($dup['date']) ? " بتاريخ {$dup['date']}" : "") . " ({$dup['matchType']})."
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        } elseif (stripos($payMethod, 'cheque') !== false) {
            if (empty($bankName)) {
                echo json_encode(['success' => false, 'error' => 'اسم البنك إجباري، يرجى كتابة اسم البنك'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            if (empty($transferRef)) {
                echo json_encode(['success' => false, 'error' => 'رقم الشيك إجباري، لابد من كتابة رقم الشيك'], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        try {
            $pdo->beginTransaction();

            $previousRecord = null;
            $idemKeyReq = trim((string)($payload['idempotencyKey'] ?? ''));
            if ($isNew) {
                $prefix = 'E' . date('ym');
                $maxSeq = 0;
                $existingVoucherNos = [];
                $stmtSeq = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ? FOR UPDATE");
                $stmtSeq->execute([$currentTenantId ?: 12]);
                while ($row = $stmtSeq->fetch(PDO::FETCH_ASSOC)) {
                    $d = json_decode($row['data'], true);
                    if (!$d || !empty($d['deleted'])) continue;
                    $id = trim((string)($d['voucherNo'] ?? ($d['id'] ?? '')));
                    if ($id !== '') $existingVoucherNos[strtoupper($id)] = true;

                    // حماية صارمة ضد التكرار عند النقر المتعدد على زر الحفظ (أونلاين أو أوفلاين)
                    if ($idemKeyReq !== '' && !empty($d['idempotencyKey']) && $d['idempotencyKey'] === $idemKeyReq) {
                        $pdo->rollBack();
                        echo json_encode([
                            'success' => true,
                            'deduplicated' => true,
                            'voucherNo' => $id,
                            'signatureHash' => $d['signatureHash'] ?? '',
                            'voucherNature' => $d['voucherNature'] ?? '',
                            'voucherNatureLabel' => $d['voucherNatureLabel'] ?? '',
                            'record' => $d,
                            'message' => 'تم حفظ السند مسبقاً (منع تكرار الحفظ)'
                        ], JSON_UNESCAPED_UNICODE);
                        exit;
                    }

                    // استخراج شامل لرقم التسلسل من كافة الصيغ (E2610XXXX، E260XXXX، 00625)
                    $sUpper = strtoupper($id);
                    if (!in_array($sUpper, ['840', '841', '842'], true)) {
                        $seq = 0;
                        if (strpos($sUpper, $prefix) === 0) {
                            $tail = substr($sUpper, strlen($prefix));
                            if (preg_match('/^\d{1,4}$/', $tail)) $seq = (int)$tail;
                        } elseif (preg_match('/^(?:E|M|BP|BR)\d{3,4}(\d{3,6})$/', $sUpper, $m)) {
                            $seq = (int)$m[1];
                        } elseif (preg_match('/^E\d{3}(\d{4})$/', $sUpper, $m)) {
                            $seq = (int)$m[1];
                        } elseif (preg_match('/^E[-_]?(\d{1,5})$/', $sUpper, $m)) {
                            $seq = (int)$m[1];
                        } elseif (preg_match('/^\d{1,6}$/', $sUpper)) {
                            $valNum = (int)$sUpper;
                            if ($valNum > 0 && $valNum < 100000) $seq = $valNum;
                        }
                        if ($seq > $maxSeq) $maxSeq = $seq;
                    }
                }
                $requestedOfflineNo = trim((string)($payload['voucherNo'] ?? ($payload['id'] ?? '')));
                if ($requestedOfflineNo !== '' && strpos($requestedOfflineNo, 'أوفلاين') === false && !isset($existingVoucherNos[strtoupper($requestedOfflineNo)])) {
                    $voucherNo = $requestedOfflineNo;
                } else {
                    $voucherNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
                }
            } else {
                $voucherNo = trim($payload['voucherNo'] ?? ($payload['id'] ?? ''));
                $stmtPrev = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.voucherNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1 FOR UPDATE");
                $stmtPrev->execute([$currentTenantId, $voucherNo, $voucherNo]);
                $prevRow = $stmtPrev->fetch(PDO::FETCH_ASSOC);
                if ($prevRow) {
                    $previousRecord = json_decode($prevRow['data'], true);
                }
            }

            // فحص أمني صارم وحفظ صورة الإشعار البنكي المرفقة (تحقق MIME الحقيقي وحجم <= 5MB)
            $attachmentPath = $previousRecord['attachment'] ?? null;
            if (!empty($payload['attachment'])) {
                $att = $payload['attachment'];
                if (is_array($att) && isset($att['dataUrl'])) {
                    $att = $att['dataUrl'];
                }
                if (is_string($att) && preg_match('/^data:image\/([a-zA-Z0-9+.-]+);base64,/', $att)) {
                    $base64Data = substr($att, strpos($att, ',') + 1);
                    $decoded = base64_decode($base64Data, true);
                    if ($decoded !== false && strlen($decoded) <= 5 * 1024 * 1024) {
                        $imgInfo = @getimagesizefromstring($decoded);
                        $allowedMimeMap = [
                            'image/jpeg' => 'jpg',
                            'image/png'  => 'png',
                            'image/webp' => 'webp'
                        ];
                        $detectedMime = $imgInfo['mime'] ?? '';
                        if (isset($allowedMimeMap[$detectedMime])) {
                            $safeExt = $allowedMimeMap[$detectedMime];
                            $dir = __DIR__ . '/uploads/receipts/';
                            if (!is_dir($dir)) {
                                @mkdir($dir, 0755, true);
                            }
                            $cleanVNo = preg_replace('/[^a-zA-Z0-9_-]/', '', $voucherNo);
                            $fileName = 'receipt_' . $cleanVNo . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $safeExt;
                            if (@file_put_contents($dir . $fileName, $decoded)) {
                                $attachmentPath = 'uploads/receipts/' . $fileName;
                            }
                        }
                    }
                } elseif (is_string($att) && preg_match('#^uploads/receipts/[a-zA-Z0-9_.-]+\.(jpg|jpeg|png|webp)$#i', $att)) {
                    $attachmentPath = $att;
                }
            }

            $natureInfo = classifyVoucherNature($fromAccName, $toAccName, [], $payload['fromAccountId'] ?? '', $payload['toAccountId'] ?? '');
            $signatureHash = generateServerVoucherSignature($voucherNo, $voucherDate, $amt, $fromAccName, $toAccName, $currentTenantId);

            $bankTransfersArr = (!empty($payload['bankTransfers']) && is_array($payload['bankTransfers']))
                ? $payload['bankTransfers']
                : (($transferRef !== '' || $bankName !== '') ? [[
                    'id' => 1,
                    'amount' => $amt,
                    'transferNo' => $transferRef,
                    'bankName' => $bankName,
                    'note' => $bankName,
                    'statement' => $notesText
                ]] : []);

            $record = [
                'id' => $voucherNo,
                'voucherNo' => $voucherNo,
                'date' => $voucherDate,
                'type' => 'single_payment',
                'voucherNature' => $natureInfo['code'],
                'voucherNatureLabel' => $natureInfo['label'],
                'paymentMethod' => $payMethod,
                'payType' => $payMethod,
                'method' => $payMethod,
                'fromAccount' => $payload['fromAccountId'] ?? ($payload['fromAccount'] ?? $fromAccName),
                'fromAccountName' => $fromAccName,
                'fromName' => $fromAccName,
                'from' => $fromAccName,
                'toAccount' => $payload['toAccountId'] ?? ($payload['toAccount'] ?? $toAccName),
                'toAccountName' => $toAccName,
                'toName' => $toAccName,
                'to' => $toAccName,
                'beneficiaryName' => $toAccName,
                'recipient' => $toAccName,
                'amount' => $amt > 0 ? $amt : $foreignAmt,
                'amountLocal' => $amt,
                'sdg' => $amt,
                'totalAmount' => $amt,
                'amountForeign' => $foreignAmt,
                'usd' => $foreignAmt,
                'foreignAmt' => $foreignAmt,
                'currency' => $currency,
                'rate' => $rate,
                'exchangeRate' => $rate,
                'costCenter' => $costCenter,
                'bankName' => $bankName,
                'bank' => $bankName,
                'mobileNote' => $bankName,
                'mobileTransferNo' => $transferRef,
                'transferRef' => $transferRef,
                'transferNo' => $transferRef,
                'refNo' => $transferRef,
                'bankTransfers' => $bankTransfersArr,
                'linkedInvoiceNo' => $linkedInvoiceNo,
                'notes' => $notesText,
                'toStatement' => $notesText,
                'fromStatement' => $notesText,
                'statement' => $notesText,
                'description' => $notesText,
                'attachment' => $attachmentPath,
                'idempotencyKey' => $idempotencyKey,
                'signatureHash' => $signatureHash,
                'createdByUserId' => $authUser['id'] ?? 0,
                'isPosted' => true,
                'source' => 'mobile_v2',
                'timestamp' => date('c')
            ];

            // إذا كان تعديلاً لسند سابق: نعكس الأثر المالي القديم أولاً قبل تطبيق الأثر الجديد
            if (!$isNew && $previousRecord) {
                $oldAmt = (float)($previousRecord['amountLocal'] ?? ($previousRecord['amount'] ?? 0));
                $oldFrom = $previousRecord['fromAccountName'] ?? ($previousRecord['from'] ?? '');
                $oldTo   = $previousRecord['toAccountName'] ?? ($previousRecord['to'] ?? '');
                applyVoucherAccountBalances($pdo, $currentTenantId, $oldFrom, $oldTo, $oldAmt, true);
                if (!empty($previousRecord['linkedInvoiceNo'])) {
                    updateLinkedInvoiceSettlement($pdo, $currentTenantId, $previousRecord['linkedInvoiceNo'], -$oldAmt);
                }
            }

            $jsonRecord = json_encode($record, JSON_UNESCAPED_UNICODE);

            if ($isNew || !$previousRecord) {
                $stmtIns = $pdo->prepare("INSERT INTO single_payments (tenant_id, data) VALUES (?, ?)");
                $stmtIns->execute([$currentTenantId, $jsonRecord]);
            } else {
                $stmtUp = $pdo->prepare("UPDATE single_payments SET data = ? WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.voucherNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                $stmtUp->execute([$jsonRecord, $currentTenantId, $voucherNo, $voucherNo]);
            }

            // 1. تحديث أرصدة الخزن والبنوك والعملاء والموردين والحسابات فعلياً في MariaDB
            $balanceMutations = applyVoucherAccountBalances($pdo, $currentTenantId, $fromAccName, $toAccName, $amt, false);

            // 2. ترحيل القيد المحاسبي المزدوج في جدول الأستاذ العام gl_entries
            syncDoubleEntryJournal($pdo, $currentTenantId, $record, false);

            // 3. تسوية الفاتورة المرتبطة (إن وجدت) في sales_invoices أو purchase_invoices
            $invoiceSettlement = null;
            if (!empty($linkedInvoiceNo)) {
                $invoiceSettlement = updateLinkedInvoiceSettlement($pdo, $currentTenantId, $linkedInvoiceNo, $amt);
            }

            // 4. توثيق العملية في سجل الرقابة والتدقيق audit_logs
            recordVoucherAuditLog($pdo, $currentTenantId, $authUser['id'] ?? 0, $isNew ? 'CREATE_VOUCHER' : 'UPDATE_VOUCHER', $voucherNo, $previousRecord, $record);

            // 5. إرسال لطابور المزامنة التلقائية لضمان مطابقة اللابتوب
            try {
                $syncId = 'sync-' . bin2hex(random_bytes(6));
                $stmtSync = $pdo->prepare("INSERT INTO sync_queue (id, entity_name, record_id, operation, payload, status, created_at, synced_at) VALUES (?, 'single_payments', ?, ?, ?, 'SYNCED', NOW(), NOW())");
                $stmtSync->execute([$syncId, $voucherNo, $isNew ? 'INSERT' : 'UPDATE', $jsonRecord]);
            } catch (Throwable $es) {}

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'voucherNo' => $voucherNo,
                'signatureHash' => $signatureHash,
                'voucherNature' => $natureInfo['code'],
                'voucherNatureLabel' => $natureInfo['label'],
                'balanceMutations' => $balanceMutations,
                'invoiceSettlement' => $invoiceSettlement,
                'record' => $record,
                'message' => 'تم حفظ وترحيل السند والقيد المزدوج وتحديث الأرصدة بنجاح في MariaDB'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errRef = strtoupper(bin2hex(random_bytes(3)));
            error_log("mobile-voucher save_voucher [$errRef] tenant=$currentTenantId user=" . ($authUser['id'] ?? 0) . ': ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => "تعذّر حفظ السند بسبب خطأ في الخادم. لم يتم ترحيل أي مبلغ. رمز المرجع: $errRef"], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // 1.3 حذف سند مع عكس الأثر المالي والقيد المحاسبي وتوثيق الحذف في سجل التدقيق
    if ($action === 'delete_voucher') {
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true) ?: $_POST;
        $vNo = trim($payload['voucherNo'] ?? ($payload['id'] ?? ''));

        if (!$vNo) {
            echo json_encode(['success' => false, 'error' => 'رقم السند مطلوب']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $stmtFind = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.voucherNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1 FOR UPDATE");
            $stmtFind->execute([$currentTenantId, $vNo, $vNo]);
            $existingRow = $stmtFind->fetch(PDO::FETCH_ASSOC);
            $beforeData = $existingRow ? json_decode($existingRow['data'], true) : null;

            if ($beforeData) {
                $periodLockError = checkFiscalPeriodOpen($pdo, $currentTenantId, $beforeData['date'] ?? '');
                if ($periodLockError) {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'error' => $periodLockError], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                $oldAmt = (float)($beforeData['amountLocal'] ?? ($beforeData['amount'] ?? 0));
                $oldFrom = $beforeData['fromAccountName'] ?? ($beforeData['from'] ?? '');
                $oldTo   = $beforeData['toAccountName'] ?? ($beforeData['to'] ?? '');
                applyVoucherAccountBalances($pdo, $currentTenantId, $oldFrom, $oldTo, $oldAmt, true);
                if (!empty($beforeData['linkedInvoiceNo'])) {
                    updateLinkedInvoiceSettlement($pdo, $currentTenantId, $beforeData['linkedInvoiceNo'], -$oldAmt);
                }
                syncDoubleEntryJournal($pdo, $currentTenantId, $beforeData, true);
            }

            $stmtDel = $pdo->prepare("DELETE FROM single_payments WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.voucherNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
            $stmtDel->execute([$currentTenantId, $vNo, $vNo]);

            recordVoucherAuditLog($pdo, $currentTenantId, $authUser['id'] ?? 0, 'DELETE_VOUCHER', $vNo, $beforeData, null);

            try {
                $syncId = 'sync-del-' . bin2hex(random_bytes(6));
                $stmtSync = $pdo->prepare("INSERT INTO sync_queue (id, entity_name, record_id, operation, payload, status, created_at, synced_at) VALUES (?, 'single_payments', ?, 'DELETE', ?, 'SYNCED', NOW(), NOW())");
                $stmtSync->execute([$syncId, $vNo, json_encode(['voucherNo' => $vNo])]);
            } catch (Exception $es) {}

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => "تم حذف السند ($vNo) وعكس أثره المالي من الأرصدة والأستاذ العام بنجاح"], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errRef = strtoupper(bin2hex(random_bytes(3)));
            error_log("mobile-voucher delete_voucher [$errRef] tenant=$currentTenantId user=" . ($authUser['id'] ?? 0) . ': ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => "تعذّر حذف السند بسبب خطأ في الخادم. رمز المرجع: $errRef"], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // 1.3.1 التحقق التشفيري من صحة وختم السند عبر QR أو البصمة (HMAC-SHA256 Verification)
    if ($action === 'verify_voucher') {
        $vNo = trim($_GET['voucherNo'] ?? ($_POST['voucherNo'] ?? ''));
        $sig = trim($_GET['sig'] ?? ($_POST['sig'] ?? ''));
        if ($vNo === '') {
            echo json_encode(['success' => false, 'verified' => false, 'error' => 'رقم السند مطلوب للتحقق'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        try {
            $stmtV = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.voucherNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
            $stmtV->execute([$currentTenantId, $vNo, $vNo]);
            $row = $stmtV->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                echo json_encode(['success' => true, 'verified' => false, 'error' => 'السند غير موجود في قاعدة البيانات'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $d = json_decode($row['data'], true) ?: [];
            $expectedSig = $d['signatureHash'] ?? generateServerVoucherSignature(
                $vNo,
                $d['date'] ?? '',
                (float)($d['amountLocal'] ?? ($d['amount'] ?? 0)),
                $d['fromAccountName'] ?? ($d['from'] ?? ''),
                $d['toAccountName'] ?? ($d['to'] ?? ''),
                $currentTenantId
            );
            $isMatch = ($sig === '' || hash_equals($expectedSig, $sig));
            echo json_encode([
                'success' => true,
                'verified' => $isMatch,
                'voucherNo' => $vNo,
                'signatureHash' => $expectedSig,
                'date' => $d['date'] ?? '',
                'amountLocal' => (float)($d['amountLocal'] ?? ($d['amount'] ?? 0)),
                'currency' => $d['currency'] ?? 'SDG',
                'fromAccountName' => $d['fromAccountName'] ?? '',
                'toAccountName' => $d['toAccountName'] ?? '',
                'voucherNatureLabel' => $d['voucherNatureLabel'] ?? 'سند حركة مالية'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'verified' => false, 'error' => 'تعذّر التحقق من السند'], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // 1.3.2 كشف الحساب المصغر الفوري لأي حساب مختار (آخر 10 حركات + الرصيد الحي)
    if ($action === 'account_mini_statement') {
        $accName = trim($_GET['account'] ?? ($_POST['account'] ?? ''));
        if ($accName === '') {
            echo json_encode(['success' => false, 'error' => 'يرجى تحديد اسم الحساب'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $liveAccounts = fetchAllLiveAccounts($pdo, $currentTenantId);
        $accMeta = null;
        foreach ($liveAccounts as $la) {
            if (trim($la['name']) === $accName) {
                $accMeta = $la;
                break;
            }
        }
        $movements = [];
        try {
            $stmtM = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ? ORDER BY id DESC LIMIT 250");
            $stmtM->execute([$currentTenantId]);
            while ($r = $stmtM->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'], true);
                if (!$d || !empty($d['deleted'])) continue;
                $from = trim((string)($d['fromAccountName'] ?? ($d['from'] ?? '')));
                $to   = trim((string)($d['toAccountName'] ?? ($d['to'] ?? ($d['beneficiaryName'] ?? ''))));
                if ($from !== $accName && $to !== $accName) continue;

                $amt = (float)($d['amountLocal'] ?? ($d['amount'] ?? 0));
                $isDebit = ($to === $accName);
                $movements[] = [
                    'voucherNo' => $d['voucherNo'] ?? ($d['id'] ?? ''),
                    'date' => $d['date'] ?? '',
                    'direction' => $isDebit ? 'debit' : 'credit',
                    'directionLabel' => $isDebit ? 'مدين (استلم)' : 'دائن (دفع)',
                    'counterparty' => $isDebit ? $from : $to,
                    'amount' => $amt,
                    'payMethod' => $d['paymentMethod'] ?? 'transfer',
                    'refNo' => $d['mobileTransferNo'] ?? ($d['refNo'] ?? ''),
                    'notes' => $d['notes'] ?? ($d['toStatement'] ?? '')
                ];
                if (count($movements) >= 10) break;
            }
        } catch (Exception $e) {}

        echo json_encode([
            'success' => true,
            'accountName' => $accName,
            'category' => $accMeta['category'] ?? 'عام',
            'normalBalance' => $accMeta['normalBalance'] ?? 'debit',
            'phone' => $accMeta['phone'] ?? '',
            'currentBalance' => (float)($accMeta['rawBalance'] ?? 0),
            'movements' => $movements
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 1.4 فحص الاتصال الحي بقاعدة البيانات (Ping MariaDB)
    if ($action === 'ping') {
        echo json_encode([
            'success' => true,
            'db' => 'MariaDB',
            'status' => 'connected',
            'time' => date('c'),
            'authenticated' => (bool)$authUser,
            'tenantId' => $authUser ? $currentTenantId : null
        ]);
        exit;
    }

    // 1.5 جلب وسحب البيانات المباشرة الحية من قاعدة البيانات MariaDB (مع دعم Delta-Sync السريع 1ms)
    if ($action === 'get_data' || $action === 'sync_data') {
        $clientHash = trim($_GET['clientHash'] ?? ($_POST['clientHash'] ?? ''));
        $fastStateHash = computeFastVoucherStateHash($pdo, $currentTenantId);
        $liveSystemUsdRate = getSystemUsdExchangeRate($pdo, $currentTenantId);

        // إذا لم يتغير أي شيء في قاعدة البيانات، نرد فوراً في 1 ملي ثانية بدون سحب الجداول!
        if ($clientHash !== '' && hash_equals($fastStateHash, $clientHash)) {
            echo json_encode([
                'success' => true,
                'unchanged' => true,
                'changed' => false,
                'stateHash' => $fastStateHash,
                'exchangeRate' => $liveSystemUsdRate,
                'serverTime' => date('Y-m-d H:i:s')
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $liveAccounts = fetchAllLiveAccounts($pdo, $currentTenantId);

        // سندات سابقة من single_payments
        $liveVouchers = [];
        $prefix = 'E' . date('ym');
        $maxSeq = 0;

        $accLookup = [];
        foreach ($liveAccounts as $la) {
            $accLookup[$la['id']] = $la['name'];
            $cleanId = preg_replace('/^(safe_|bank_|cust_|supp_|acc_|exp_)/', '', $la['id']);
            if ($cleanId) $accLookup[$cleanId] = $la['name'];
        }

        try {
            $stmtV = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ? ORDER BY id ASC");
            $stmtV->execute([$currentTenantId]);
            while ($row = $stmtV->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($row['data'], true);
                if (!$d || !empty($d['deleted'])) continue;
                $vNo = $d['voucherNo'] ?? ($d['id'] ?? '');
                if ($vNo) {
                    $rawFrom = $d['fromAccount'] ?? ($d['from'] ?? '');
                    $fromKey = is_array($rawFrom) ? ($rawFrom['id'] ?? ($rawFrom['name'] ?? '')) : (string)$rawFrom;
                    $fromName = $d['fromAccountName'] ?? ($d['fromName'] ?? ($accLookup[$fromKey] ?? $fromKey));
                    if (is_array($fromName)) $fromName = $fromName['name'] ?? '';

                    $rawTo = $d['toAccount'] ?? ($d['to'] ?? '');
                    $toKey = is_array($rawTo) ? ($rawTo['id'] ?? ($rawTo['name'] ?? '')) : (string)$rawTo;
                    $toName = $d['toAccountName'] ?? ($d['toName'] ?? ($d['beneficiaryName'] ?? ($d['recipient'] ?? ($accLookup[$toKey] ?? $toKey))));
                    if (is_array($toName)) $toName = $toName['name'] ?? '';

                    $bt0 = (!empty($d['bankTransfers']) && is_array($d['bankTransfers']) && isset($d['bankTransfers'][0]) && is_array($d['bankTransfers'][0])) ? $d['bankTransfers'][0] : [];
                    $ch0 = (!empty($d['cheques']) && is_array($d['cheques']) && isset($d['cheques'][0]) && is_array($d['cheques'][0])) ? $d['cheques'][0] : [];

                    $bankName = $d['bankName'] ?? ($d['bank'] ?? ($d['mobileNote'] ?? ($d['checkBranch'] ?? ($bt0['bankName'] ?? ($bt0['note'] ?? ($ch0['checkBranch'] ?? ''))))));
                    if (is_array($bankName)) $bankName = $bankName['name'] ?? '';

                    $refNo = $d['mobileTransferNo'] ?? ($d['refNo'] ?? ($d['transferRef'] ?? ($d['checkNo'] ?? ($bt0['transferNo'] ?? ($ch0['checkNo'] ?? '')))));
                    if (is_array($refNo)) $refNo = '';

                    $notesVal = $d['notes'] ?? ($d['toStatement'] ?? ($d['fromStatement'] ?? ($d['description'] ?? ($d['memo'] ?? ($bt0['statement'] ?? '')))));
                    if (is_array($notesVal)) $notesVal = '';

                    $rateVal = (float)($d['rate'] ?? $liveSystemUsdRate);
                    if ($rateVal <= 0) $rateVal = $liveSystemUsdRate;
                    $amtLocal = (float)($d['amountLocal'] ?? ($d['amount'] ?? ($d['totalAmount'] ?? ($d['sdg'] ?? 0))));
                    $amtForeign = (float)($d['amountForeign'] ?? ($d['usd'] ?? ($d['foreignAmt'] ?? 0)));
                    if ($amtForeign <= 0 && $amtLocal > 0 && $rateVal > 0) {
                        $amtForeign = round($amtLocal / $rateVal, 2);
                    } elseif ($amtLocal <= 0 && $amtForeign > 0 && $rateVal > 0) {
                        $amtLocal = round($amtForeign * $rateVal, 2);
                    }

                    $vNature = $d['voucherNature'] ?? '';
                    $vNatureLabel = $d['voucherNatureLabel'] ?? '';
                    if ($vNatureLabel === '' || strpos($vNatureLabel, 'قيد تسوية') !== false) {
                        $natObj = classifyVoucherNature((string)$fromName, (string)$toName, $liveAccounts);
                        $vNature = $vNature ?: ($natObj['code'] ?? 'payment');
                        $vNatureLabel = $natObj['label'] ?? 'حركة مالية';
                    }

                    $sigHash = $d['signatureHash'] ?? generateServerVoucherSignature($vNo, $d['date'] ?? date('Y-m-d'), $amtLocal, $fromName, $toName, $currentTenantId);

                    $liveVouchers[] = [
                        'id' => $vNo,
                        'voucherNo' => $vNo,
                        'date' => $d['date'] ?? date('Y-m-d'),
                        'voucherNature' => $vNature,
                        'voucherNatureLabel' => $vNatureLabel,
                        'signatureHash' => $sigHash,
                        'linkedInvoiceNo' => $d['linkedInvoiceNo'] ?? '',
                        'attachment' => $d['attachment'] ?? null,
                        'currency' => $d['currency'] ?? 'SDG',
                        'rate' => $rateVal,
                        'costCenter' => is_array($d['costCenter'] ?? '') ? '' : ($d['costCenter'] ?? ''),
                        'sdg' => $amtLocal,
                        'usd' => $amtForeign,
                        'foreignAmt' => $amtForeign,
                        'payType' => $d['paymentMethod'] ?? ($d['payType'] ?? 'transfer'),
                        'fromId' => $fromKey,
                        'from' => $fromName,
                        'fromName' => $fromName,
                        'toId' => $toKey,
                        'to' => $toName,
                        'toName' => $toName,
                        'refNo' => (string)$refNo,
                        'bankName' => (string)$bankName,
                        'notes' => (string)$notesVal
                    ];

                    $sUpper = strtoupper(trim((string)$vNo));
                    if (!in_array($sUpper, ['840', '841', '842'], true)) {
                        $s = 0;
                        if (strpos($sUpper, $prefix) === 0) {
                            $tail = substr($sUpper, strlen($prefix));
                            if (preg_match('/^\d{1,4}$/', $tail)) $s = (int)$tail;
                        } elseif (preg_match('/^(?:E|M|BP|BR)\d{3,4}(\d{3,6})$/', $sUpper, $m)) {
                            $s = (int)$m[1];
                        } elseif (preg_match('/^E\d{3}(\d{4})$/', $sUpper, $m)) {
                            $s = (int)$m[1];
                        } elseif (preg_match('/^E[-_]?(\d{1,5})$/', $sUpper, $m)) {
                            $s = (int)$m[1];
                        } elseif (preg_match('/^\d{1,6}$/', $sUpper)) {
                            $valNum = (int)$sUpper;
                            if ($valNum > 0 && $valNum < 100000) $s = $valNum;
                        }
                        if ($s > $maxSeq) $maxSeq = $s;
                    }
                }
            }
        } catch(Exception $e) {}

        $nextVNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
        $allDbTransfersLive = fetchAllDbTransfers($pdo, $currentTenantId);

        echo json_encode([
            'success' => true,
            'unchanged' => false,
            'changed' => true,
            'stateHash' => $fastStateHash,
            'dbConnected' => true,
            'serverTime' => date('Y-m-d H:i:s'),
            'tenantId' => $currentTenantId,
            'exchangeRate' => $liveSystemUsdRate,
            'totalAccounts' => count($liveAccounts),
            'totalVouchers' => count($liveVouchers),
            'nextVoucherNo' => $nextVNo,
            'accounts' => $liveAccounts,
            'vouchers' => $liveVouchers,
            'allTransferRefs' => $allDbTransfersLive
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ────────────────────────────────────────────────────────────────────────────
// 2. DATA PRELOADING: الحسابات، مراكز الكلفة، العملات، السندات السابقة
// ────────────────────────────────────────────────────────────────────────────
$systemUsdRate = getSystemUsdExchangeRate($pdo, $currentTenantId);
$accountsList = fetchAllLiveAccounts($pdo, $currentTenantId);
$allDbTransfersPreload = fetchAllDbTransfers($pdo, $currentTenantId);

// 2.6 جلب السندات السابقة من single_payments لمطابقة اللابتوب والتنقل (◀ ▶)
$vouchersHistory = [];
$prefix = 'E' . date('ym');
$maxSeq = 0;

$accLookupHistory = [];
foreach ($accountsList as $al) {
    $accLookupHistory[$al['id']] = $al['name'];
    $cleanId = preg_replace('/^(safe_|bank_|cust_|supp_|acc_|exp_)/', '', $al['id']);
    if ($cleanId) $accLookupHistory[$cleanId] = $al['name'];
}

try {
    $effTenant = !empty($currentTenantId) ? (int)$currentTenantId : 12;
    $stmtV = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ? ORDER BY id ASC");
    $stmtV->execute([$effTenant]);
    while ($row = $stmtV->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($row['data'], true);
        if (!$d || !empty($d['deleted'])) continue;
        $vNo = $d['voucherNo'] ?? ($d['id'] ?? '');
        if (!empty($vNo)) {
            $rawFrom = $d['fromAccount'] ?? ($d['from'] ?? '');
            $fromKey = is_array($rawFrom) ? ($rawFrom['id'] ?? ($rawFrom['name'] ?? '')) : (string)$rawFrom;
            $fromName = $d['fromAccountName'] ?? ($d['fromName'] ?? ($accLookupHistory[$fromKey] ?? $fromKey));
            if (is_array($fromName)) $fromName = $fromName['name'] ?? '';

            $rawTo = $d['toAccount'] ?? ($d['to'] ?? '');
            $toKey = is_array($rawTo) ? ($rawTo['id'] ?? ($rawTo['name'] ?? '')) : (string)$rawTo;
            $toName = $d['toAccountName'] ?? ($d['toName'] ?? ($d['beneficiaryName'] ?? ($d['recipient'] ?? ($accLookupHistory[$toKey] ?? $toKey))));
            if (is_array($toName)) $toName = $toName['name'] ?? '';

            $bt0 = (!empty($d['bankTransfers']) && is_array($d['bankTransfers']) && isset($d['bankTransfers'][0]) && is_array($d['bankTransfers'][0])) ? $d['bankTransfers'][0] : [];
            $ch0 = (!empty($d['cheques']) && is_array($d['cheques']) && isset($d['cheques'][0]) && is_array($d['cheques'][0])) ? $d['cheques'][0] : [];

            $bankName = $d['bankName'] ?? ($d['bank'] ?? ($d['mobileNote'] ?? ($d['checkBranch'] ?? ($bt0['bankName'] ?? ($bt0['note'] ?? ($ch0['checkBranch'] ?? ''))))));
            if (is_array($bankName)) $bankName = $bankName['name'] ?? '';

            $refNo = $d['mobileTransferNo'] ?? ($d['refNo'] ?? ($d['transferRef'] ?? ($d['checkNo'] ?? ($bt0['transferNo'] ?? ($ch0['checkNo'] ?? '')))));
            if (is_array($refNo)) $refNo = '';

            $notesVal = $d['notes'] ?? ($d['toStatement'] ?? ($d['fromStatement'] ?? ($d['description'] ?? ($d['memo'] ?? ($bt0['statement'] ?? '')))));
            if (is_array($notesVal)) $notesVal = '';

            $rateVal = (float)($d['rate'] ?? $systemUsdRate);
            if ($rateVal <= 0) $rateVal = $systemUsdRate;
            $amtLocal = (float)($d['amountLocal'] ?? ($d['amount'] ?? ($d['totalAmount'] ?? ($d['sdg'] ?? 0))));
            $amtForeign = (float)($d['amountForeign'] ?? ($d['usd'] ?? ($d['foreignAmt'] ?? 0)));
            if ($amtForeign <= 0 && $amtLocal > 0 && $rateVal > 0) {
                $amtForeign = round($amtLocal / $rateVal, 2);
            } elseif ($amtLocal <= 0 && $amtForeign > 0 && $rateVal > 0) {
                $amtLocal = round($amtForeign * $rateVal, 2);
            }

            $vNature = $d['voucherNature'] ?? '';
            $vNatureLabel = $d['voucherNatureLabel'] ?? '';
            if ($vNatureLabel === '' || strpos($vNatureLabel, 'قيد تسوية') !== false) {
                $natObj = classifyVoucherNature((string)$fromName, (string)$toName, $accountsList);
                $vNature = $vNature ?: ($natObj['code'] ?? 'payment');
                $vNatureLabel = $natObj['label'] ?? 'حركة مالية';
            }

            $sigHash = $d['signatureHash'] ?? generateServerVoucherSignature($vNo, $d['date'] ?? date('Y-m-d'), $amtLocal, $fromName, $toName, $effTenant);

            $vouchersHistory[] = [
                'id' => $vNo,
                'voucherNo' => $vNo,
                'date' => $d['date'] ?? date('Y-m-d'),
                'voucherNature' => $vNature,
                'voucherNatureLabel' => $vNatureLabel,
                'signatureHash' => $sigHash,
                'linkedInvoiceNo' => $d['linkedInvoiceNo'] ?? '',
                'attachment' => $d['attachment'] ?? null,
                'currency' => $d['currency'] ?? 'SDG',
                'rate' => $rateVal,
                'costCenter' => is_array($d['costCenter'] ?? '') ? '' : ($d['costCenter'] ?? ''),
                'sdg' => $amtLocal,
                'usd' => $amtForeign,
                'foreignAmt' => $amtForeign,
                'payType' => $d['paymentMethod'] ?? ($d['payType'] ?? 'transfer'),
                'fromId' => $fromKey,
                'from' => $fromName,
                'fromName' => $fromName,
                'toId' => $toKey,
                'to' => $toName,
                'toName' => $toName,
                'refNo' => (string)$refNo,
                'bankName' => (string)$bankName,
                'notes' => (string)$notesVal
            ];

            $sUpper = strtoupper(trim((string)$vNo));
            if (!in_array($sUpper, ['840', '841', '842'], true)) {
                $s = 0;
                if (strpos($sUpper, $prefix) === 0) {
                    $tail = substr($sUpper, strlen($prefix));
                    if (preg_match('/^\d{1,4}$/', $tail)) $s = (int)$tail;
                } elseif (preg_match('/^(?:E|M|BP|BR)\d{3,4}(\d{3,6})$/', $sUpper, $m)) {
                    $s = (int)$m[1];
                } elseif (preg_match('/^E\d{3}(\d{4})$/', $sUpper, $m)) {
                    $s = (int)$m[1];
                } elseif (preg_match('/^E[-_]?(\d{1,5})$/', $sUpper, $m)) {
                    $s = (int)$m[1];
                } elseif (preg_match('/^\d{1,6}$/', $sUpper)) {
                    $valNum = (int)$sUpper;
                    if ($valNum > 0 && $valNum < 100000) $s = $valNum;
                }
                if ($s > $maxSeq) $maxSeq = $s;
            }
        }
    }
} catch (Exception $e) {}

$nextSequentialVoucherNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
$initialStateHash = computeFastVoucherStateHash($pdo, $currentTenantId);

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

// العملات النشطة وسعر الصرف الافتراضي المتزامن مع قاعدة البيانات
$currenciesData = [
    'SDG' => ['name' => 'جنيه سوداني', 'symbol' => 'ج.س', 'flag' => '🇸🇩', 'defaultRate' => 1, 'isBase' => true],
    'USD' => ['name' => 'دولار أمريكي', 'symbol' => '$', 'flag' => '🇺🇸', 'defaultRate' => $systemUsdRate, 'isBase' => false],
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
    <?php $mgJsonFlags = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT; ?>
    window.SYSTEM_USD_RATE = <?php echo json_encode((float)$systemUsdRate, $mgJsonFlags); ?>;
    window.PHP_ACCOUNTS = <?php echo json_encode($accountsList, $mgJsonFlags); ?>;
    window.PHP_VOUCHERS = <?php echo json_encode($vouchersHistory, $mgJsonFlags); ?>;
    window.PHP_NEXT_NO = <?php echo json_encode($nextSequentialVoucherNo, $mgJsonFlags); ?>;
    window.DB_ALL_TRANSFER_REFS = <?php echo json_encode($allDbTransfersPreload, $mgJsonFlags); ?>;
    window.PHP_CURRENCIES = <?php echo json_encode($currenciesData, $mgJsonFlags); ?>;
    window._lastDbStateHash = <?php echo json_encode($initialStateHash, $mgJsonFlags); ?>;


    // ── اعتراض انتهاء الجلسة (401) لكل طلبات الـ API: رسالة واضحة بدل الحفظ الصامت أوفلاين ──
    (function () {
      const nativeFetch = window.fetch.bind(window);
      let bannerShown = false;
      function showSessionExpiredBanner(msg) {
        if (bannerShown) return;
        bannerShown = true;
        const show = () => {
          const bar = document.createElement('div');
          bar.id = 'sessionExpiredBanner';
          bar.setAttribute('role', 'alert');
          bar.style.cssText = 'position:fixed;top:0;left:0;right:0;z-index:2147483647;background:#7f1d1d;color:#fff;border-bottom:2px solid #f87171;padding:12px 14px;direction:rtl;font-family:Cairo,system-ui,sans-serif;font-weight:800;font-size:14px;display:flex;align-items:center;justify-content:space-between;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,.6)';
          const text = document.createElement('span');
          text.textContent = '🔒 ' + (msg || 'انتهت جلسة الدخول. يرجى تسجيل الدخول مرة أخرى.') + ' (بيانات السند الحالي لم تُحذف)';
          const btn = document.createElement('a');
          btn.href = 'login.php?redirect=' + encodeURIComponent('mobile-voucher.php');
          btn.textContent = 'تسجيل الدخول';
          btn.style.cssText = 'background:#fff;color:#7f1d1d;padding:8px 14px;border-radius:10px;text-decoration:none;white-space:nowrap;font-weight:900';
          bar.appendChild(text);
          bar.appendChild(btn);
          document.body.appendChild(bar);
        };
        if (document.body) show(); else document.addEventListener('DOMContentLoaded', show);
      }
      window.fetch = async function (input, init) {
        const url = typeof input === 'string' ? input : (input && input.url) || '';
        const isOwnApi = url.indexOf('mobile-voucher.php?api=') !== -1;
        const opts = isOwnApi ? Object.assign({ credentials: 'same-origin' }, init || {}) : init;
        const res = await nativeFetch(input, opts);
        if (isOwnApi && res.status === 401) {
          window.MG_SESSION_EXPIRED = true;
          try { const j = await res.clone().json(); showSessionExpiredBanner(j && j.error); }
          catch (e) { showSessionExpiredBanner(); }
        }
        return res;
      };
    })();
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
  
  <!-- Local Fast Assets with Instant 0ms Load & CDN Fallback -->
  <link rel="stylesheet" href="css/all.min.css">
  <link rel="stylesheet" href="css/mobile-tailwind.min.css?v=2026_11_03_fast">
  <script src="js/magaies-offline-sync.js?v=2026_10_16_v23" defer></script>
  
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

    /* Hide number spinner arrows for usdRate and native datalist indicators that shift text vertically */
    #usdRate::-webkit-inner-spin-button,
    #usdRate::-webkit-outer-spin-button {
      -webkit-appearance: none !important;
      margin: 0 !important;
    }
    #usdRate {
      -moz-appearance: textfield !important;
      appearance: textfield !important;
      font-family: 'Inter', 'JetBrains Mono', sans-serif !important;
      font-size: 17.5px;
      font-weight: 500 !important;
      -webkit-text-stroke: 0 !important;
      letter-spacing: -0.2px !important;
      text-shadow: none !important;
    }
    input::-webkit-calendar-picker-indicator {
      display: none !important;
      -webkit-appearance: none !important;
      opacity: 0 !important;
      width: 0 !important;
      height: 0 !important;
      margin: 0 !important;
      padding: 0 !important;
      pointer-events: none !important;
    }
    @media (max-width: 600px) {
      input:not(#transferInput):not(#sdgAmount):not(#usdAmount):not(#usdRate):not(#costCenterInput):not(#bankNameInput):not(.account-search-input),
      select:not(#primaryCurrencySelect),
      textarea {
        font-size: 15.5px !important;
      }
    }
    #costCenterInput {
      font-size: 12px;
      height: 40px !important;
      max-height: 40px !important;
      line-height: 40px !important;
      padding: 0 4px !important;
      margin: 0 !important;
      text-align: center !important;
      vertical-align: middle !important;
      box-sizing: border-box !important;
    }
    #primaryCurrencySelect {
      font-size: 11.5px;
      height: 40px !important;
      line-height: 40px !important;
      padding-top: 0 !important;
      padding-bottom: 0 !important;
    }
    #bankNameInput {
      font-size: 13.5px;
      height: 48px !important;
      max-height: 48px !important;
      line-height: 46px !important;
      padding-top: 0 !important;
      padding-bottom: 0 !important;
      vertical-align: middle !important;
      box-sizing: border-box !important;
    }
    #transferInput {
      height: 48px !important;
      max-height: 48px !important;
      line-height: 46px !important;
      padding-top: 0 !important;
      padding-bottom: 0 !important;
      vertical-align: middle !important;
      box-sizing: border-box !important;
      font-family: 'JetBrains Mono', 'Montserrat', monospace !important;
      font-weight: 900 !important;
      -webkit-text-stroke: 0.35px currentColor;
    }

    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    /* تثبيت حقول أسماء الحسابات ومنع أي تشويش أو حركة رأسية داخل الصندوق والمساحة بالكامل للاسم */
    .account-search-input {
      height: 58px !important;
      max-height: 58px !important;
      line-height: 56px !important;
      padding-top: 0 !important;
      padding-bottom: 0 !important;
      padding-right: 14px !important;
      padding-left: 14px !important;
      overflow: hidden !important;
      white-space: nowrap !important;
      text-overflow: clip !important;
      box-sizing: border-box !important;
      text-align: right !important;
      direction: rtl !important;
      font-weight: 900 !important;
      font-size: 20px;
      vertical-align: middle !important;
    }

    /* توهج ضوئي حي نابض للحقول التي يتم ملؤها وتحديثها عبر المايك الذكي */
    @keyframes voiceGlowPulse {
      0% { box-shadow: 0 0 0 0 rgba(6, 182, 212, 0.8); border-color: #22d3ee !important; }
      50% { box-shadow: 0 0 22px 5px rgba(16, 185, 129, 0.8); border-color: #10b981 !important; transform: scale(1.01); }
      100% { box-shadow: 0 0 0 0 rgba(6, 182, 212, 0); transform: scale(1); }
    }
    .voice-field-glow {
      animation: voiceGlowPulse 2.4s ease-out;
      border-color: #06b6d4 !important;
    }

    /* المايكات الصغيرة لكل حقل */
    .field-mic-btn { position: relative; }
    .field-mic-btn::after { content: ''; position: absolute; inset: -8px; border-radius: 9999px; } /* مساحة لمس أكبر من الزر الظاهر */
    @keyframes fieldMicPulse {
      0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.75); }
      70% { box-shadow: 0 0 0 9px rgba(239, 68, 68, 0); }
      100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }
    .field-mic-btn.field-mic-listening {
      background: #dc2626 !important;
      border-color: #fca5a5 !important;
      color: #fff !important;
      animation: fieldMicPulse 1.2s infinite;
    }

    /* ═════════════════════════════════════════════════════════════════
       نظام التنبيهات والتوست المركزي الفاخر المقتبس من شاشة حركة مالية (single-cash-payment)
       ═════════════════════════════════════════════════════════════════ */
    .toast-container {
      position: fixed !important;
      top: 50% !important;
      left: 50% !important;
      transform: translate(-50%, -50%) !important;
      z-index: 1000000 !important;
      display: flex !important;
      flex-direction: column !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 15px !important;
      pointer-events: none !important;
      width: auto !important;
      max-width: 94vw !important;
    }

    .toast {
      padding: 16px 28px !important;
      border-radius: 20px !important;
      font-weight: 800 !important;
      font-size: 1.15rem !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 14px !important;
      pointer-events: auto !important;
      backdrop-filter: blur(25px) saturate(180%) !important;
      -webkit-backdrop-filter: blur(25px) saturate(180%) !important;
      box-shadow: 0 25px 60px -10px rgba(0, 0, 0, 0.8), 0 0 35px rgba(0, 0, 0, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.25) !important;
      animation: toastCenterIn 0.38s cubic-bezier(0.175, 0.885, 0.32, 1.275) both !important;
      font-family: 'Cairo', 'Tajawal', system-ui, sans-serif !important;
      min-width: 280px !important;
      max-width: 94vw !important;
      text-align: center !important;
      direction: rtl !important;
      letter-spacing: 0.3px !important;
      cursor: pointer !important;
      user-select: none !important;
      transition: transform 0.2s ease, opacity 0.3s ease !important;
    }

    .toast:hover {
      transform: scale(1.02) !important;
    }

    .toast .toast-icon {
      font-size: 1.5rem !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      flex-shrink: 0 !important;
      filter: drop-shadow(0 2px 8px rgba(0,0,0,0.4)) !important;
    }

    .toast .toast-text {
      flex: 1 !important;
      line-height: 1.5 !important;
      text-shadow: 0 2px 4px rgba(0,0,0,0.6) !important;
    }

    .toast.error, .toast.danger {
      background: linear-gradient(145deg, rgba(38, 8, 16, 0.96), rgba(80, 15, 28, 0.94)) !important;
      border: 1.5px solid rgba(248, 113, 113, 0.65) !important;
      color: #ffffff !important;
      box-shadow: 0 20px 60px rgba(239, 68, 68, 0.35), 0 0 30px rgba(239, 68, 68, 0.2), inset 0 0 15px rgba(239, 68, 68, 0.15) !important;
    }

    .toast.warning {
      background: linear-gradient(145deg, rgba(40, 24, 6, 0.96), rgba(85, 48, 12, 0.94)) !important;
      border: 1.5px solid rgba(251, 191, 36, 0.65) !important;
      color: #ffffff !important;
      box-shadow: 0 20px 60px rgba(245, 158, 11, 0.35), 0 0 30px rgba(245, 158, 11, 0.2), inset 0 0 15px rgba(245, 158, 11, 0.15) !important;
    }

    .toast.success {
      background: linear-gradient(145deg, rgba(6, 36, 24, 0.96), rgba(12, 70, 45, 0.94)) !important;
      border: 1.5px solid rgba(52, 211, 153, 0.65) !important;
      color: #ffffff !important;
      box-shadow: 0 20px 60px rgba(16, 185, 129, 0.35), 0 0 30px rgba(16, 185, 129, 0.2), inset 0 0 15px rgba(16, 185, 129, 0.15) !important;
    }

    .toast.info {
      background: linear-gradient(145deg, rgba(12, 30, 52, 0.96), rgba(20, 60, 100, 0.94)) !important;
      border: 1.5px solid rgba(56, 189, 248, 0.65) !important;
      color: #ffffff !important;
      box-shadow: 0 20px 60px rgba(56, 189, 248, 0.35), 0 0 30px rgba(56, 189, 248, 0.2), inset 0 0 15px rgba(56, 189, 248, 0.15) !important;
    }

    .toast.hide {
      animation: toastCenterOut 0.32s cubic-bezier(0.4, 0, 1, 1) forwards !important;
    }

    @keyframes toastCenterIn {
      0% {
        opacity: 0;
        transform: scale(0.8) translateY(20px);
        filter: blur(10px);
      }
      100% {
        opacity: 1;
        transform: scale(1) translateY(0);
        filter: blur(0);
      }
    }

    @keyframes toastCenterOut {
      0% {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
      100% {
        opacity: 0;
        transform: scale(0.8) translateY(-20px);
        filter: blur(8px);
      }
    }

    /* شريط التمرير الانسيابي للنافذة المنبثقة لخصائص الأرقام للتحرك لأعلى ولأسفل بسهولة */
    .modal-scrollable-body::-webkit-scrollbar {
      width: 6px;
    }
    .modal-scrollable-body::-webkit-scrollbar-track {
      background: rgba(15, 23, 42, 0.7);
      border-radius: 8px;
    }
    .modal-scrollable-body::-webkit-scrollbar-thumb {
      background: linear-gradient(180deg, #0ea5e9, #0284c7);
      border-radius: 8px;
      border: 1px solid rgba(56, 189, 248, 0.3);
    }
    .modal-scrollable-body {
      scrollbar-width: thin;
      scrollbar-color: #0ea5e9 rgba(15, 23, 42, 0.7);
      -webkit-overflow-scrolling: touch;
      overscroll-behavior: contain;
    }

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

    /* ═══════════════════════════════════════════════════════════════════
       إضاءة الحواف المتناغمة (Neon Edge Illumination Suite):
       المستوى الأساسي موحّد لجميع الحقول والبطاقات تماماً كمستوى التاريخ ورقم المستند.
       وحينما يأتي عليه المؤشر (Hover) تزيد الإضاءة، وعند النقر والتركيز (Focus) تزيد أكثر وأكثر.
       ═══════════════════════════════════════════════════════════════════ */
    .glow-border-cyan {
      border: 2px solid rgba(6, 182, 212, 0.8) !important;
      box-shadow: 0 0 14px rgba(6, 182, 212, 0.35) !important;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .glow-border-cyan:hover {
      border-color: #22d3ee !important;
      box-shadow: 0 0 22px rgba(6, 182, 212, 0.65), inset 0 0 6px rgba(6, 182, 212, 0.18) !important;
    }
    .glow-border-cyan:focus, .glow-border-cyan:focus-within, .glow-border-cyan.is-active-glow {
      border-color: #67e8f9 !important;
      box-shadow: 0 0 30px rgba(6, 182, 212, 0.9), inset 0 0 12px rgba(6, 182, 212, 0.28) !important;
    }

    .glow-border-amber {
      border: 2px solid rgba(245, 158, 11, 0.8) !important;
      box-shadow: 0 0 14px rgba(245, 158, 11, 0.35) !important;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .glow-border-amber:hover {
      border-color: #fbbf24 !important;
      box-shadow: 0 0 22px rgba(245, 158, 11, 0.65), inset 0 0 6px rgba(245, 158, 11, 0.18) !important;
    }
    .glow-border-amber:focus, .glow-border-amber:focus-within, .glow-border-amber.is-active-glow {
      border-color: #fde047 !important;
      box-shadow: 0 0 30px rgba(245, 158, 11, 0.9), inset 0 0 12px rgba(245, 158, 11, 0.28) !important;
    }

    .glow-border-emerald {
      border: 2px solid rgba(16, 185, 129, 0.8) !important;
      box-shadow: 0 0 14px rgba(16, 185, 129, 0.35) !important;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .glow-border-emerald:hover {
      border-color: #34d399 !important;
      box-shadow: 0 0 22px rgba(16, 185, 129, 0.65), inset 0 0 6px rgba(16, 185, 129, 0.18) !important;
    }
    .glow-border-emerald:focus, .glow-border-emerald:focus-within, .glow-border-emerald.is-active-glow {
      border-color: #6ee7b7 !important;
      box-shadow: 0 0 30px rgba(16, 185, 129, 0.9), inset 0 0 12px rgba(16, 185, 129, 0.28) !important;
    }

    .glow-border-sky {
      border: 2px solid rgba(56, 189, 248, 0.8) !important;
      box-shadow: 0 0 14px rgba(56, 189, 248, 0.35) !important;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .glow-border-sky:hover {
      border-color: #38bdf8 !important;
      box-shadow: 0 0 22px rgba(56, 189, 248, 0.65), inset 0 0 6px rgba(56, 189, 248, 0.18) !important;
    }
    .glow-border-sky:focus, .glow-border-sky:focus-within, .glow-border-sky.is-active-glow {
      border-color: #7dd3fc !important;
      box-shadow: 0 0 30px rgba(56, 189, 248, 0.9), inset 0 0 12px rgba(56, 189, 248, 0.28) !important;
    }

    .glow-border-indigo {
      border: 2px solid rgba(99, 102, 241, 0.8) !important;
      box-shadow: 0 0 14px rgba(99, 102, 241, 0.35) !important;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .glow-border-indigo:hover {
      border-color: #818cf8 !important;
      box-shadow: 0 0 22px rgba(99, 102, 241, 0.65), inset 0 0 6px rgba(99, 102, 241, 0.18) !important;
    }
    .glow-border-indigo:focus, .glow-border-indigo:focus-within, .glow-border-indigo.is-active-glow {
      border-color: #a5b4fc !important;
      box-shadow: 0 0 30px rgba(99, 102, 241, 0.9), inset 0 0 12px rgba(99, 102, 241, 0.28) !important;
    }

    .glow-border-red {
      border: 2px solid rgba(239, 68, 68, 0.8) !important;
      box-shadow: 0 0 14px rgba(239, 68, 68, 0.35) !important;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .glow-border-red:hover {
      border-color: #f87171 !important;
      box-shadow: 0 0 22px rgba(239, 68, 68, 0.65), inset 0 0 6px rgba(239, 68, 68, 0.18) !important;
    }
    .glow-border-red:focus, .glow-border-red:focus-within, .glow-border-red.is-active-glow {
      border-color: #fca5a5 !important;
      box-shadow: 0 0 30px rgba(239, 68, 68, 0.9), inset 0 0 12px rgba(239, 68, 68, 0.28) !important;
    }

    /* أزرار الدفع الزجاجية العصرية المضيئة بتباين فائق */
    .glass-pay-btn {
      position: relative;
      overflow: hidden;
      background: rgba(30, 41, 59, 0.92) !important;
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1.5px solid rgba(148, 163, 184, 0.45) !important;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5), inset 0 1px 1px 0 rgba(255, 255, 255, 0.25);
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .glass-pay-btn:hover {
      border-color: rgba(56, 189, 248, 0.8) !important;
      box-shadow: 0 0 14px rgba(56, 189, 248, 0.35);
    }
    .glass-pay-btn.is-active {
      background: linear-gradient(135deg, rgba(2, 132, 199, 0.95) 0%, rgba(30, 58, 138, 0.98) 100%) !important;
      border-color: #38bdf8 !important;
      color: #ffffff !important;
      box-shadow: 0 0 20px rgba(14, 165, 233, 0.7), inset 0 1px 2px rgba(255, 255, 255, 0.6) !important;
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
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .amount-display-card.is-typing-sdg {
      border-color: #7dd3fc !important;
      box-shadow: 0 0 32px rgba(56, 189, 248, 0.95), inset 0 0 14px rgba(56, 189, 248, 0.3) !important;
    }
    .amount-display-card.is-typing-usd {
      border-color: #6ee7b7 !important;
      box-shadow: 0 0 32px rgba(16, 185, 129, 0.95), inset 0 0 14px rgba(16, 185, 129, 0.3) !important;
    }
    .input-typing-glow-amber {
      border-color: #fde047 !important;
      box-shadow: 0 0 32px rgba(245, 158, 11, 0.95), inset 0 0 14px rgba(245, 158, 11, 0.3) !important;
    }
    .input-typing-glow-cyan {
      border-color: #67e8f9 !important;
      box-shadow: 0 0 32px rgba(6, 182, 212, 0.95), inset 0 0 14px rgba(6, 182, 212, 0.3) !important;
    }
    .input-typing-glow-indigo {
      border-color: #a5b4fc !important;
      box-shadow: 0 0 32px rgba(99, 102, 241, 0.95), inset 0 0 14px rgba(99, 102, 241, 0.3) !important;
    }

    #amountContainerCard {
      background: linear-gradient(165deg, rgba(30, 41, 59, 0.75) 0%, rgba(15, 23, 42, 0.94) 100%);
      border: 1px solid rgba(148, 163, 184, 0.22);
      box-shadow: 0 8px 32px -4px rgba(0, 0, 0, 0.5), inset 0 1px 0 0 rgba(255, 255, 255, 0.08);
      position: relative;
    }
    #amountContainerCard::before {
      content: '';
      position: absolute;
      top: 0;
      left: 12%;
      right: 12%;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(56, 189, 248, 0.5), rgba(16, 185, 129, 0.5), transparent);
      pointer-events: none;
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

    /* ═══════════════════════════════════════════════════════════════════════════
       محرك السمات المحاسبية السبع المعتمدة (7 Master Accounting Themes - WCAG AAA)
       توحيد لوني وبصري كامل 100% دون المساس بأي خاصية تمرير أو أبعاد (Zero Scroll Lag)
       ═══════════════════════════════════════════════════════════════════════════ */
    html[data-theme="warm-alabaster"] {
      --mv-bg: #F6F0E4;
      --mv-surface: #EDE4D3;
      --mv-card: #E5DAC4;
      --mv-input: #FFFFFF;
      --mv-text: #000000;
      --mv-text-sub: #1C140D;
      --mv-border: #785A3C;
    }
    html[data-theme="pure-platinum"] {
      --mv-bg: #FFFFFF;
      --mv-surface: #F1F5F9;
      --mv-card: #E6ECF5;
      --mv-input: #FFFFFF;
      --mv-text: #000000;
      --mv-text-sub: #0F172A;
      --mv-border: #475569;
    }
    html[data-theme="pearl-platinum"] {
      --mv-bg: #E5E9F0;
      --mv-surface: #D8E0EB;
      --mv-card: #DCE3EE;
      --mv-input: #FFFFFF;
      --mv-text: #000000;
      --mv-text-sub: #0F172A;
      --mv-border: #334155;
    }
    html[data-theme="midnight-executive"] {
      --mv-bg: #04091A;
      --mv-surface: #081026;
      --mv-card: #0A132C;
      --mv-input: #040918;
      --mv-text: #F8FAFC;
      --mv-text-sub: #CBD5E1;
      --mv-border: rgba(99, 102, 241, 0.38);
    }
    html[data-theme="sepia-brown"] {
      --mv-bg: #1A110B;
      --mv-surface: #241710;
      --mv-card: #2C1D14;
      --mv-input: #140C08;
      --mv-text: #FDF8F5;
      --mv-text-sub: #E7D5C9;
      --mv-border: rgba(217, 119, 6, 0.45);
    }
    html[data-theme="obsidian-charcoal"] {
      --mv-bg: #000000;
      --mv-surface: #09090B;
      --mv-card: #121215;
      --mv-input: #050507;
      --mv-text: #FFFFFF;
      --mv-text-sub: #D4D4D8;
      --mv-border: rgba(161, 161, 170, 0.35);
    }
    html[data-theme="twilight-indigo"] {
      --mv-bg: #1E293B;
      --mv-surface: #263349;
      --mv-card: #2D3C54;
      --mv-input: #0F172A;
      --mv-text: #F8FAFC;
      --mv-text-sub: #CBD5E1;
      --mv-border: rgba(148, 163, 184, 0.42);
    }

    /* تطبيق الخلفيات والألوان الموحدة للسمات الداكنة المخصصة (البني الموكا، الأسود الفحمي، الرمادي الداكن) */
    html[data-theme="sepia-brown"] body,
    html[data-theme="obsidian-charcoal"] body,
    html[data-theme="twilight-indigo"] body {
      background-color: var(--mv-bg) !important;
      color: var(--mv-text) !important;
    }
    html[data-theme="sepia-brown"] #mobileContainer,
    html[data-theme="obsidian-charcoal"] #mobileContainer,
    html[data-theme="twilight-indigo"] #mobileContainer {
      background: var(--mv-bg) !important;
      background-image: none !important;
      border-color: var(--mv-border) !important;
    }
    html[data-theme="sepia-brown"] #mobileContainer > div.sticky,
    html[data-theme="obsidian-charcoal"] #mobileContainer > div.sticky,
    html[data-theme="twilight-indigo"] #mobileContainer > div.sticky,
    html[data-theme="sepia-brown"] .bottom-bar-fixed,
    html[data-theme="obsidian-charcoal"] .bottom-bar-fixed,
    html[data-theme="twilight-indigo"] .bottom-bar-fixed {
      background: var(--mv-surface) !important;
      border-color: var(--mv-border) !important;
    }
    html[data-theme="sepia-brown"] #amountContainerCard,
    html[data-theme="obsidian-charcoal"] #amountContainerCard,
    html[data-theme="twilight-indigo"] #amountContainerCard,
    html[data-theme="sepia-brown"] #fromAccountCard,
    html[data-theme="obsidian-charcoal"] #fromAccountCard,
    html[data-theme="twilight-indigo"] #fromAccountCard,
    html[data-theme="sepia-brown"] #toAccountCard,
    html[data-theme="obsidian-charcoal"] #toAccountCard,
    html[data-theme="twilight-indigo"] #toAccountCard,
    html[data-theme="sepia-brown"] #transferContainer,
    html[data-theme="obsidian-charcoal"] #transferContainer,
    html[data-theme="twilight-indigo"] #transferContainer,
    html[data-theme="sepia-brown"] main > div,
    html[data-theme="obsidian-charcoal"] main > div,
    html[data-theme="twilight-indigo"] main > div {
      background: var(--mv-card) !important;
      background-image: none !important;
    }
    html[data-theme="sepia-brown"] #sdgAmountCard,
    html[data-theme="obsidian-charcoal"] #sdgAmountCard,
    html[data-theme="twilight-indigo"] #sdgAmountCard,
    html[data-theme="sepia-brown"] #usdAmountCard,
    html[data-theme="obsidian-charcoal"] #usdAmountCard,
    html[data-theme="twilight-indigo"] #usdAmountCard,
    html[data-theme="sepia-brown"] #fromSearchInput,
    html[data-theme="obsidian-charcoal"] #fromSearchInput,
    html[data-theme="twilight-indigo"] #fromSearchInput,
    html[data-theme="sepia-brown"] #toSearchInput,
    html[data-theme="obsidian-charcoal"] #toSearchInput,
    html[data-theme="twilight-indigo"] #toSearchInput,
    html[data-theme="sepia-brown"] #transferInput,
    html[data-theme="obsidian-charcoal"] #transferInput,
    html[data-theme="twilight-indigo"] #transferInput,
    html[data-theme="sepia-brown"] #bankNameInput,
    html[data-theme="obsidian-charcoal"] #bankNameInput,
    html[data-theme="twilight-indigo"] #bankNameInput,
    html[data-theme="sepia-brown"] #voucherNotes,
    html[data-theme="obsidian-charcoal"] #voucherNotes,
    html[data-theme="twilight-indigo"] #voucherNotes,
    html[data-theme="sepia-brown"] #primaryCurrencySelect,
    html[data-theme="obsidian-charcoal"] #primaryCurrencySelect,
    html[data-theme="twilight-indigo"] #primaryCurrencySelect,
    html[data-theme="sepia-brown"] #fromDropdown,
    html[data-theme="obsidian-charcoal"] #fromDropdown,
    html[data-theme="twilight-indigo"] #fromDropdown,
    html[data-theme="sepia-brown"] #toDropdown,
    html[data-theme="obsidian-charcoal"] #toDropdown,
    html[data-theme="twilight-indigo"] #toDropdown,
    html[data-theme="sepia-brown"] #fromBalanceWrapper,
    html[data-theme="obsidian-charcoal"] #fromBalanceWrapper,
    html[data-theme="twilight-indigo"] #fromBalanceWrapper,
    html[data-theme="sepia-brown"] #toBalanceWrapper,
    html[data-theme="obsidian-charcoal"] #toBalanceWrapper,
    html[data-theme="twilight-indigo"] #toBalanceWrapper {
      background-color: var(--mv-input) !important;
    }

    /* ═══════════════════════════════════════════════════════════════════════════
       تطبيق السمات الفاتحة الثلاث (الأبيض الجليدي، البيج الكريمي الطبي، الرمادي البلاتيني)
       تباين فائق الوضوح (WCAG AAA): خلفية فاتحة موحدة + خط داكن حاد وغامق جداً 100%
       ═══════════════════════════════════════════════════════════════════════════ */
    html[data-theme-mode="light"] body {
      background-color: var(--mv-bg) !important;
      color: var(--mv-text) !important;
    }
    html[data-theme-mode="light"] #mobileContainer {
      background: var(--mv-bg) !important;
      background-image: none !important;
      color: var(--mv-text) !important;
      border-color: var(--mv-border) !important;
    }
    html[data-theme-mode="light"] #mobileContainer > div.sticky,
    html[data-theme-mode="light"] .bottom-bar-fixed,
    html[data-theme-mode="light"] .desktop-screen-switcher {
      background: var(--mv-surface) !important;
      background-image: none !important;
      border-color: var(--mv-border) !important;
      color: var(--mv-text) !important;
    }
    html[data-theme-mode="light"] main > div,
    html[data-theme-mode="light"] #amountContainerCard,
    html[data-theme-mode="light"] #fromAccountCard,
    html[data-theme-mode="light"] #toAccountCard,
    html[data-theme-mode="light"] #transferContainer {
      background: var(--mv-card) !important;
      background-image: none !important;
      border: 1.5px solid var(--mv-border) !important;
      color: var(--mv-text) !important;
      box-shadow: 0 3px 10px rgba(15, 23, 42, 0.08) !important;
    }

    /* حواف وبطاقة (من حساب - الدافع) باللون الأحمر القرمزي الغامق والواضح */
    html[data-theme-mode="light"] #fromAccountCard {
      background: linear-gradient(180deg, rgba(254, 205, 211, 0.72) 0%, var(--mv-card) 100%) !important;
      border: 2px solid #9F1239 !important;
      box-shadow: 0 3px 12px rgba(159, 18, 57, 0.14) !important;
    }
    /* حواف وبطاقة (إلى حساب - المستلم) باللون الأخضر الزمردي الغامق والواضح */
    html[data-theme-mode="light"] #toAccountCard {
      background: linear-gradient(180deg, rgba(167, 243, 208, 0.72) 0%, var(--mv-card) 100%) !important;
      border: 2px solid #047857 !important;
      box-shadow: 0 3px 12px rgba(4, 120, 87, 0.14) !important;
    }

    /* خلفيات الحقول البيضاء الناصعة مع حواف غامقة وحادة حسب وظيفة كل حقل */
    html[data-theme-mode="light"] #sdgAmountCard,
    html[data-theme-mode="light"] #usdAmountCard,
    html[data-theme-mode="light"] #fromSearchInput,
    html[data-theme-mode="light"] #toSearchInput,
    html[data-theme-mode="light"] #transferInput,
    html[data-theme-mode="light"] #bankNameInput,
    html[data-theme-mode="light"] #voucherNotes,
    html[data-theme-mode="light"] #primaryCurrencySelect,
    html[data-theme-mode="light"] #fromBalanceWrapper,
    html[data-theme-mode="light"] #toBalanceWrapper,
    html[data-theme-mode="light"] .glow-border-cyan,
    html[data-theme-mode="light"] .glow-border-amber,
    html[data-theme-mode="light"] .glow-border-emerald,
    html[data-theme-mode="light"] .glow-border-sky,
    html[data-theme-mode="light"] .glow-border-indigo,
    html[data-theme-mode="light"] .glow-border-red {
      background: var(--mv-input) !important;
      background-color: var(--mv-input) !important;
      color: var(--mv-text) !important;
    }

    /* تغميق الحواف الدلالية (الأحمر، الأخضر، الكهرماني، الأزرق، البنفسجي) في السمات الفاتحة */
    html[data-theme-mode="light"] .glow-border-red,
    html[data-theme-mode="light"] #fromSearchInput {
      border: 2px solid #9F1239 !important;
      box-shadow: 0 2px 6px rgba(159, 18, 57, 0.14) !important;
    }
    html[data-theme-mode="light"] .glow-border-emerald,
    html[data-theme-mode="light"] #toSearchInput,
    html[data-theme-mode="light"] #usdAmountCard {
      border: 2px solid #047857 !important;
      box-shadow: 0 2px 6px rgba(4, 120, 87, 0.14) !important;
    }
    html[data-theme-mode="light"] .glow-border-amber,
    html[data-theme-mode="light"] #primaryCurrencySelect,
    html[data-theme-mode="light"] #transferInput {
      border: 2px solid #92400E !important;
      box-shadow: 0 2px 6px rgba(146, 64, 14, 0.14) !important;
    }
    html[data-theme-mode="light"] .glow-border-cyan,
    html[data-theme-mode="light"] .glow-border-sky,
    html[data-theme-mode="light"] #sdgAmountCard,
    html[data-theme-mode="light"] #bankNameInput {
      border: 2px solid #075985 !important;
      box-shadow: 0 2px 6px rgba(7, 89, 133, 0.14) !important;
    }
    html[data-theme-mode="light"] .glow-border-indigo,
    html[data-theme-mode="light"] #voucherNotes {
      border: 2px solid #3730A3 !important;
      box-shadow: 0 2px 6px rgba(55, 48, 163, 0.14) !important;
    }

    html[data-theme-mode="light"] #voucherDate {
      color-scheme: light !important;
    }

    /* ضمان التباين الفائق واللون الأسود/الغامق الحاد والأنيق للنصوص المكتوبة فعلياً داخل الحقول في السمات الفاتحة */
    html[data-theme-mode="light"] #fromSearchInput,
    html[data-theme-mode="light"] #toSearchInput,
    html[data-theme-mode="light"] #voucherNotes,
    html[data-theme-mode="light"] #voucherSearchFilterInput {
      color: #0F172A !important;
      font-weight: 900 !important;
    }
    /* المخزن / مركز الكلفة: لون بنفسجي/أسود غامق جداً وأنيق عند الكتابة أو الاختيار، وأخف قليلاً كقيمة توضيحية افتراضية */
    html[data-theme-mode="light"] #costCenterInput {
      color: #1E1B4B !important;
      font-weight: 900 !important;
    }
    html[data-theme-mode="light"] #costCenterInput.mv-ghost-default {
      color: #64748B !important;
      opacity: 0.68 !important;
      font-weight: 600 !important;
    }
    html:not([data-theme-mode="light"]) #costCenterInput.mv-ghost-default {
      color: rgba(165, 180, 252, 0.58) !important;
      font-weight: 600 !important;
    }

    /* النصوص والأرقام التوضيحية (Placeholders) داخل المربعات في الحركة المالية: أخف بدرجة مريحة وأنيقة قبل الكتابة */
    html[data-theme-mode="light"] #fromSearchInput::placeholder,
    html[data-theme-mode="light"] #toSearchInput::placeholder,
    html[data-theme-mode="light"] #costCenterInput::placeholder,
    html[data-theme-mode="light"] #voucherNotes::placeholder,
    html[data-theme-mode="light"] #transferInput::placeholder,
    html[data-theme-mode="light"] #bankNameInput::placeholder,
    html[data-theme-mode="light"] #voucherSearchFilterInput::placeholder {
      color: #64748B !important;
      opacity: 0.55 !important;
      font-weight: 500 !important;
    }
    html[data-theme-mode="light"] #sdgAmount::placeholder,
    html[data-theme-mode="light"] #usdAmount::placeholder {
      color: #64748B !important;
      opacity: 0.45 !important;
      font-weight: 500 !important;
    }
    html:not([data-theme-mode="light"]) #fromSearchInput::placeholder,
    html:not([data-theme-mode="light"]) #toSearchInput::placeholder,
    html:not([data-theme-mode="light"]) #costCenterInput::placeholder,
    html:not([data-theme-mode="light"]) #voucherNotes::placeholder,
    html:not([data-theme-mode="light"]) #transferInput::placeholder,
    html:not([data-theme-mode="light"]) #bankNameInput::placeholder,
    html:not([data-theme-mode="light"]) #voucherSearchFilterInput::placeholder {
      color: #94A3B8 !important;
      opacity: 0.46 !important;
      font-weight: 500 !important;
    }
    html:not([data-theme-mode="light"]) #sdgAmount::placeholder,
    html:not([data-theme-mode="light"]) #usdAmount::placeholder {
      color: #64748B !important;
      opacity: 0.42 !important;
      font-weight: 500 !important;
    }

    /* التاريخ واسم البنك والوقت وعنوان المبلغ المحلي: أزرق كحلي غامق جداً وأنيق عند الكتابة */
    html[data-theme-mode="light"] #voucherDateDisplay,
    html[data-theme-mode="light"] #bankNameInput,
    html[data-theme-mode="light"] #bankNameLabel,
    html[data-theme-mode="light"] #bankNameLabel i,
    html[data-theme-mode="light"] #currentTime,
    html[data-theme-mode="light"] #amountContainerCard .text-sky-300 {
      color: #082F49 !important;
      font-weight: 900 !important;
    }
    html[data-theme-mode="light"] .glow-border-cyan i.fa-calendar {
      color: #075985 !important;
    }
    html[data-theme-mode="light"] #currentTime {
      background: var(--mv-input) !important;
      border-color: var(--mv-border) !important;
    }

    /* العملة (جنيه سوداني) ورقم السند ورقم التحويل: أحمر عنابي / بني محروق غامق جداً */
    html[data-theme-mode="light"] #primaryCurrencySelect {
      color: #7F1D1D !important;
      font-weight: 900 !important;
    }
    html[data-theme-mode="light"] #primaryCurrencySelect + div,
    html[data-theme-mode="light"] #primaryCurrencySelect ~ div {
      color: #7F1D1D !important;
    }
    html[data-theme-mode="light"] #currentVoucherDisplay,
    html[data-theme-mode="light"] #transferInput,
    html[data-theme-mode="light"] #refLabel,
    html[data-theme-mode="light"] #refLabel i,
    html[data-theme-mode="light"] #fromAccountSdgBalance,
    html[data-theme-mode="light"] #toAccountSdgBalance {
      color: #78350F !important;
      font-weight: 900 !important;
    }

    /* السعر الدولاري ومبلغ الدولار وإلى حساب (المستلم): أخضر غامق جداً (زمردي داكن) */
    html[data-theme-mode="light"] #usdRate,
    html[data-theme-mode="light"] #rateSymbolLabel,
    html[data-theme-mode="light"] #foreignAmountTitle,
    html[data-theme-mode="light"] #toHeaderTitle,
    html[data-theme-mode="light"] #fromAccountUsdBalance,
    html[data-theme-mode="light"] #toAccountUsdBalance {
      color: #064E3B !important;
      font-weight: 900 !important;
    }

    /* من حساب (الدافع): أحمر قرمزي غامق جداً */
    html[data-theme-mode="light"] #fromHeaderTitle {
      color: #7F1D1D !important;
      font-weight: 900 !important;
    }
    html[data-theme-mode="light"] #fromStatementBtn {
      background: #FECDD3 !important;
      color: #7F1D1D !important;
      border: 1.5px solid #9F1239 !important;
      font-weight: 900 !important;
    }
    html[data-theme-mode="light"] #toStatementBtn {
      background: #A7F3D0 !important;
      color: #064E3B !important;
      border: 1.5px solid #047857 !important;
      font-weight: 900 !important;
    }

    /* شريط الرصيد الحي وبعد القيد داخل بطاقتي من حساب وإلى حساب */
    html[data-theme-mode="light"] #fromBalanceWrapper {
      border: 1.5px solid #9F1239 !important;
    }
    html[data-theme-mode="light"] #toBalanceWrapper {
      border: 1.5px solid #047857 !important;
    }
    html[data-theme-mode="light"] #fromBalanceWrapper .text-slate-400,
    html[data-theme-mode="light"] #toBalanceWrapper .text-slate-400 {
      color: #000000 !important;
      font-weight: 900 !important;
    }
    html[data-theme-mode="light"] #fromBalanceWrapper .text-amber-300\/80,
    html[data-theme-mode="light"] #toBalanceWrapper .text-amber-300\/80 {
      color: #78350F !important;
      font-weight: 900 !important;
    }
    html[data-theme-mode="light"] #fromBalanceWrapper .text-emerald-400,
    html[data-theme-mode="light"] #toBalanceWrapper .text-emerald-400 {
      color: #064E3B !important;
      font-weight: 900 !important;
    }
    html[data-theme-mode="light"] #fromProjectedBadge,
    html[data-theme-mode="light"] #fromProjectedBadge *,
    html[data-theme-mode="light"] #toProjectedBadge,
    html[data-theme-mode="light"] #toProjectedBadge * {
      color: #0C4A6E !important;
      font-weight: 900 !important;
    }

    html[data-theme-mode="light"] #tafqeetDisplay {
      background: #FEF3C7 !important;
      border: 2px solid #B45309 !important;
    }
    html[data-theme-mode="light"] #tafqeetText {
      color: #78350F !important;
      font-weight: 900 !important;
    }

    /* أزرار طرق الدفع (تحويل، كاش، شيك مصرفي، شيك عادي) بلون أسود غامق وحاد جداً */
    html[data-theme-mode="light"] .glass-pay-btn:not(.is-active) {
      background: #FFFFFF !important;
      color: #000000 !important;
      border: 2px solid var(--mv-border) !important;
      box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08) !important;
      font-weight: 900 !important;
    }
    html[data-theme-mode="light"] .glass-pay-btn:not(.is-active) span {
      color: #000000 !important;
      font-weight: 900 !important;
    }
    html[data-theme-mode="light"] #btn-transfer:not(.is-active) i {
      color: #075985 !important;
    }
    html[data-theme-mode="light"] #btn-cash:not(.is-active) i {
      color: #047857 !important;
    }
    html[data-theme-mode="light"] #btn-bank_cheque:not(.is-active) i {
      color: #92400E !important;
    }
    html[data-theme-mode="light"] #btn-normal_cheque:not(.is-active) i {
      color: #3730A3 !important;
    }
    html[data-theme-mode="light"] .glass-pay-btn.is-active {
      background: linear-gradient(135deg, #0C4A6E 0%, #1E3A8A 100%) !important;
      color: #FFFFFF !important;
      border: 2px solid #082F49 !important;
    }
    html[data-theme-mode="light"] .glass-pay-btn.is-active span,
    html[data-theme-mode="light"] .glass-pay-btn.is-active i {
      color: #FFFFFF !important;
    }

    /* شريط آلية الحفظ وأزرار المرفقات والشريط السفلي ذو الصفين في السمات الفاتحة */
    html[data-theme-mode="light"] #batchModeBarWrapper {
      background: var(--mv-input) !important;
      border: 1.5px solid var(--mv-border) !important;
    }
    html[data-theme-mode="light"] #batchModeToggleGroup {
      background: var(--mv-surface) !important;
      border-color: var(--mv-border) !important;
    }
    html[data-theme-mode="light"] #btnMainModeManual {
      color: #1E293B !important;
    }
    html[data-theme-mode="light"] .modern-media-card {
      background: #FFFFFF !important;
      color: #000000 !important;
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.1) !important;
    }
    html[data-theme-mode="light"] .modern-media-card span {
      color: #000000 !important;
      font-weight: 900 !important;
    }
    html[data-theme-mode="light"] .action-btn-kinetic:not(#btnSaveMain) {
      background: #FFFFFF !important;
      color: #0F172A !important;
      border: 1.5px solid var(--mv-border) !important;
      box-shadow: 0 2px 6px rgba(15, 23, 42, 0.1) !important;
    }
    html[data-theme-mode="light"] .action-btn-kinetic:not(#btnSaveMain) span {
      color: #0F172A !important;
      font-weight: 900 !important;
    }
    html[data-theme-mode="light"] #btnSaveMain,
    html[data-theme-mode="light"] #btnSaveMain span,
    html[data-theme-mode="light"] #btnSaveMain i {
      color: #FFFFFF !important;
    }
    html[data-theme-mode="light"] #btnNextVoucher,
    html[data-theme-mode="light"] #btnPrevVoucher {
      background: var(--mv-surface) !important;
      color: #78350F !important;
      border: 1.5px solid #92400E !important;
    }
    /* القوائم المنسدلة للحسابات والنوافذ المنبثقة في السمات الفاتحة */
    html[data-theme-mode="light"] #fromDropdown,
    html[data-theme-mode="light"] #toDropdown {
      background: var(--mv-input) !important;
      color: #000000 !important;
      border: 2px solid var(--mv-border) !important;
      box-shadow: 0 12px 32px rgba(15, 23, 42, 0.22) !important;
    }
    html[data-theme-mode="light"] #fromDropdown .sticky,
    html[data-theme-mode="light"] #toDropdown .sticky {
      background: var(--mv-surface) !important;
      border-color: var(--mv-border) !important;
    }
    html[data-theme-mode="light"] #fromDropdown .text-white,
    html[data-theme-mode="light"] #toDropdown .text-white,
    html[data-theme-mode="light"] #mobileContainer .text-white:not(#masterMicBtn):not(#masterMicBtn *):not(#btnSaveMain):not(#btnSaveMain *):not(.glass-pay-btn.is-active):not(.glass-pay-btn.is-active *):not(#btnMainModeAuto):not(#btnMainModeAuto *):not(.modern-media-card i):not(#crystalFlowBtn *) {
      color: #000000 !important;
    }
    html[data-theme-mode="light"] #fromDropdown .text-amber-400,
    html[data-theme-mode="light"] #toDropdown .text-amber-400 {
      color: #78350F !important;
      font-weight: 900 !important;
    }
    html[data-theme-mode="light"] #fromDropdown .text-emerald-400,
    html[data-theme-mode="light"] #toDropdown .text-emerald-400 {
      color: #064E3B !important;
      font-weight: 900 !important;
    }
    html[data-theme-mode="light"] #fromDropdown > div:hover,
    html[data-theme-mode="light"] #toDropdown > div:hover {
      background: var(--mv-surface) !important;
    }
    html[data-theme-mode="light"] #modalAccountMiniStatement > div,
    html[data-theme-mode="light"] #modalBatchIntake > div,
    html[data-theme-mode="light"] #modalSendWhatsApp > div,
    html[data-theme-mode="light"] #modalPrintVoucher > div,
    html[data-theme-mode="light"] #modalSearchVouchers > div,
    html[data-theme-mode="light"] #modalNumberProperties > div,
    html[data-theme-mode="light"] #politeModalCard,
    html[data-theme-mode="light"] #saveFeedbackCard {
      background: var(--mv-bg) !important;
      color: #000000 !important;
      border-color: var(--mv-border) !important;
    }
    html[data-theme-mode="light"] #modalSearchVouchers .bg-slate-950,
    html[data-theme-mode="light"] #modalAccountMiniStatement .bg-slate-950,
    html[data-theme-mode="light"] #modalBatchIntake .bg-slate-950,
    html[data-theme-mode="light"] #modalSendWhatsApp .bg-slate-950,
    html[data-theme-mode="light"] #modalNumberProperties .bg-slate-950 {
      background: var(--mv-surface) !important;
      color: #000000 !important;
      border-color: var(--mv-border) !important;
    }
    html[data-theme-mode="light"] #modalSearchVouchers .text-white,
    html[data-theme-mode="light"] #modalAccountMiniStatement .text-white,
    html[data-theme-mode="light"] #modalBatchIntake .text-white:not(.bg-emerald-600 *):not(.bg-indigo-600 *):not(.bg-amber-600 *),
    html[data-theme-mode="light"] #modalSendWhatsApp .text-white:not(.bg-emerald-600 *),
    html[data-theme-mode="light"] #modalNumberProperties .text-white:not(.bg-cyan-600 *):not(.bg-sky-600 *):not(.bg-emerald-600 *) {
      color: #000000 !important;
    }
  </style>
  <script>
    (function() {
      const VOUCHER_THEME_MAP = {
        'warm_cream': 'warm-alabaster',
        'warm-alabaster': 'warm-alabaster',
        'alabaster-warm': 'warm-alabaster',
        'paper-ivory': 'warm-alabaster',
        'sand-linen': 'warm-alabaster',
        'mint-breeze': 'warm-alabaster',
        'sage-calm': 'warm-alabaster',
        'sky-morning': 'warm-alabaster',
        'ultra_white': 'pure-platinum',
        'pure-platinum': 'pure-platinum',
        'light': 'pure-platinum',
        'arctic-frost': 'pure-platinum',
        'exec_gray': 'pearl-platinum',
        'pearl-platinum': 'pearl-platinum',
        'lavender-mist': 'pearl-platinum',
        'rose-quartz': 'pearl-platinum',
        'midnight_obsidian': 'midnight-executive',
        'midnight-executive': 'midnight-executive',
        'dark': 'midnight-executive',
        'navy-command': 'midnight-executive',
        'royal-sapphire': 'midnight-executive',
        'oceanic-teal': 'midnight-executive',
        'emerald-vault': 'midnight-executive',
        'mocha_brown': 'sepia-brown',
        'sepia-brown': 'sepia-brown',
        'espresso-gold': 'sepia-brown',
        'crimson-velvet': 'sepia-brown',
        'pure_oled': 'obsidian-charcoal',
        'obsidian-charcoal': 'obsidian-charcoal',
        'nordic-frost': 'obsidian-charcoal',
        'slate_twilight': 'twilight-indigo',
        'twilight-indigo': 'twilight-indigo',
        'amethyst-night': 'twilight-indigo'
      };
      const DESKTOP_SYNC_MAP = {
        'warm-alabaster': 'warm_cream',
        'pure-platinum': 'ultra_white',
        'pearl-platinum': 'exec_gray',
        'midnight-executive': 'midnight_obsidian',
        'sepia-brown': 'mocha_brown',
        'obsidian-charcoal': 'pure_oled',
        'twilight-indigo': 'slate_twilight'
      };
      const LIGHT_THEMES = { 'warm-alabaster': true, 'pure-platinum': true, 'pearl-platinum': true };

      window.resolveVoucherThemeKey = function(raw) {
        const k = String(raw || '').trim();
        return VOUCHER_THEME_MAP[k] || 'midnight-executive';
      };
      window.isVoucherLightTheme = function(k) {
        return !!LIGHT_THEMES[window.resolveVoucherThemeKey(k)];
      };
      window.applyVoucherMasterTheme = function(rawKey, persist) {
        const resolved = window.resolveVoucherThemeKey(rawKey);
        const isLight = !!LIGHT_THEMES[resolved];
        document.documentElement.setAttribute('data-theme', resolved);
        document.documentElement.setAttribute('data-theme-mode', isLight ? 'light' : 'dark');
        if (document.body) {
          document.body.setAttribute('data-theme', resolved);
        }
        if (persist !== false) {
          try {
            const desktopKey = DESKTOP_SYNC_MAP[resolved] || 'midnight_obsidian';
            localStorage.setItem('magaies_active_theme', desktopKey);
            localStorage.setItem('magaiees_mobile_theme', resolved);
            localStorage.setItem('magaiees_global_theme', resolved);
            localStorage.setItem('ui_theme', isLight ? 'light' : 'dark');
            localStorage.setItem('erp_theme', isLight ? 'light' : 'dark');
          } catch (e) {}
        }
        if (typeof window.applyNumberStylesToInputs === 'function') {
          window.applyNumberStylesToInputs();
        }
        const picker = document.getElementById('voucherQuickThemeDrawer');
        if (picker) {
          picker.querySelectorAll('[data-vtheme]').forEach(btn => {
            const active = btn.getAttribute('data-vtheme') === resolved;
            btn.style.outline = active ? '2px solid #10b981' : 'none';
          });
        }
      };

      try {
        const saved = localStorage.getItem('magaiees_mobile_theme') ||
                      localStorage.getItem('magaies_active_theme') ||
                      localStorage.getItem('magaiees_global_theme') ||
                      localStorage.getItem('ui_theme') ||
                      localStorage.getItem('erp_theme') ||
                      'midnight-executive';
        window.applyVoucherMasterTheme(saved, false);
      } catch (e) {}

      window.addEventListener('storage', function(e) {
        if (e && ['magaiees_mobile_theme', 'magaies_active_theme', 'magaiees_global_theme'].includes(e.key) && e.newValue) {
          window.applyVoucherMasterTheme(e.newValue, false);
        }
      });
    })();
  </script>
  <link rel="stylesheet" href="css/magaiees-royal-buttons.css?v=<?= file_exists(__DIR__ . '/css/magaiees-royal-buttons.css') ? filemtime(__DIR__ . '/css/magaiees-royal-buttons.css') : time() ?>">
  <script src="js/magaiees-royal-buttons.js?v=<?= file_exists(__DIR__ . '/js/magaiees-royal-buttons.js') ? filemtime(__DIR__ . '/js/magaiees-royal-buttons.js') : time() ?>" defer></script>
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
  <div id="mobileContainer" class="mobile-device-outer w-full max-w-[393px] bg-gradient-to-b from-[#04091a] via-[#070e24] to-[#030712] sm:rounded-[36px] sm:border-[4px] sm:border-slate-800/90 sm:shadow-2xl overflow-hidden flex flex-col relative transition-all duration-300 min-h-screen sm:min-h-[852px]">

    <!-- ═════════════════════════════════════════════════════════════════
         1. السطر الأول المرفوع (الهيدر العلوي):
         الرجوع + المايك الذكي + حركة مالية + حالة المزامنة + السمات السبع + الساعة
         ═════════════════════════════════════════════════════════════════ -->
    <div class="px-2.5 py-1 bg-[#050b1d]/95 border-b border-indigo-950/80 flex items-center justify-between sticky top-0 z-40 select-none backdrop-blur-md">
      
      <!-- اليمين: مايك الذكاء الصوتي الشامل + عنوان الشاشة (حركة مالية) + منطقة المزامنة -->
      <div class="flex items-center gap-1.5">
        <!-- زر الرجوع الفوري للشاشة الرئيسية -->
        <a href="mobile-dashboard.php" class="w-7 h-7 rounded-xl bg-slate-900/90 hover:bg-slate-800 active:scale-95 text-cyan-400 border border-cyan-500/30 flex items-center justify-center shrink-0 shadow transition cursor-pointer" title="الرجوع للشاشة الرئيسية">
          <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
        <!-- زر المايك الصوتي الذكي الشامل للشاشة بأكملها -->
        <button type="button" onclick="toggleMasterVoiceInput()" id="masterMicBtn" class="w-7 h-7 rounded-xl bg-gradient-to-r from-cyan-500 via-sky-500 to-indigo-600 hover:from-cyan-400 hover:to-indigo-500 active:scale-95 text-white flex items-center justify-center shrink-0 border border-cyan-300 shadow-[0_0_14px_rgba(6,182,212,0.55)] transition cursor-pointer relative group" title="المايك الصوتي الذكي الشامل: اضغط وتحدث لتعبئة كامل السند تلقائياً">
          <i class="fa-solid fa-microphone text-xs text-yellow-300" id="masterMicIcon"></i>
          <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 rounded-full animate-ping hidden" id="masterMicListeningPing"></span>
        </button>

        <!-- حركة مالية فقط بخط واضح وفخم -->
        <span class="text-sm sm:text-base font-black text-white tracking-wide flex items-center gap-1 shrink-0">
          <i class="fa-solid fa-money-bill-transfer text-cyan-400 text-xs"></i>
          <span>حركة مالية</span>
        </span>

        <!-- مؤشر مزامنة وتحديث البيانات الذكي + زر سجل تدقيق المزامنة الخلفية -->
        <div class="flex items-center gap-1">
          <button type="button" onclick="handleUnifiedSyncRefresh()" oncontextmenu="event.preventDefault(); if(window.MagaiesOffline) window.MagaiesOffline.showSyncAuditModal();" id="networkPill" class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/40 text-emerald-400 text-[10.5px] font-black cursor-pointer hover:bg-emerald-500/25 active:scale-95 transition shadow-sm relative" title="انقر للتحديث الفوري • اضغط مطولاً لفتح سجل تدقيق المزامنة التلقائية">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_#10b981]" id="networkDot"></span>
            <i class="fa-solid fa-arrows-rotate text-[9.5px] text-red-300 hidden" id="networkRefreshIcon"></i>
            <span id="networkText">متزامن</span>
            <span id="voucherSyncPendingBadge" class="hidden bg-amber-500 text-slate-950 text-[8.5px] font-mono font-black px-1 rounded-full">0</span>
          </button>
          <button id="voucherSyncAuditBtn" type="button" onclick="if(window.MagaiesOffline) window.MagaiesOffline.showSyncAuditModal(); if(window.triggerHapticPulse)triggerHapticPulse(15);" class="w-6 h-6 rounded-full bg-slate-900/90 hover:bg-slate-800 border border-cyan-500/40 text-cyan-300 flex items-center justify-center text-[10px] active:scale-95 transition cursor-pointer shadow-sm relative" title="درع الأمان وسجل تدقيق المزامنة التلقائية والرفع الخلفي (Sync Audit Log)">
            <i class="fa-solid fa-shield-halved"></i>
            <span id="voucherSyncAuditPendingBadge" class="hidden absolute -top-1 -right-1 bg-amber-500 text-slate-950 text-[7px] font-mono font-black px-1 rounded-full">0</span>
          </button>
        </div>
      </div>

      <!-- اليسار: زر السمات السبع الموحدة + الساعة الحقيقية -->
      <div class="flex items-center gap-1.5 text-slate-300 relative">
        <button type="button" onclick="const d=document.getElementById('voucherQuickThemeDrawer'); if(d) d.classList.toggle('hidden');" class="w-7 h-7 rounded-lg bg-slate-900/90 hover:bg-slate-800 border border-amber-500/40 text-amber-400 flex items-center justify-center text-xs shadow-sm active:scale-95 transition" title="السمات المحاسبية السبع المعتمدة (7 Themes)">
          <i class="fa-solid fa-palette"></i>
        </button>
        <span id="voucherNatureHeaderBadge" class="hidden"></span>
        <span id="currentTime" class="font-bold font-mono text-cyan-200/90 text-[11px] bg-slate-900/90 px-2 py-0.5 rounded-lg border border-indigo-900/60 shadow-inner">13:15</span>

        <!-- نافذة سريعة لاختيار إحدى السمات السبع المعتمدة مباشرة من شاشة الحركة المالية -->
        <div id="voucherQuickThemeDrawer" class="hidden absolute top-9 left-0 w-64 rounded-2xl bg-slate-900 border-2 border-amber-500/60 p-2.5 shadow-2xl z-50 space-y-1.5 text-right" dir="rtl">
          <div class="flex items-center justify-between border-b border-slate-800 pb-1.5 mb-1">
            <span class="text-[11px] font-black text-amber-400">🎨 السمات المحاسبية الـ 7 (AAA)</span>
            <button type="button" onclick="document.getElementById('voucherQuickThemeDrawer').classList.add('hidden')" class="text-slate-400 hover:text-white text-xs font-bold px-1">✕</button>
          </div>
          <div class="text-[9.5px] font-bold text-slate-400">☀️ سمات نهارية فاتحة (خط داكن حاد):</div>
          <div class="grid grid-cols-3 gap-1">
            <button type="button" data-vtheme="warm-alabaster" onclick="window.applyVoucherMasterTheme('warm-alabaster', true); document.getElementById('voucherQuickThemeDrawer').classList.add('hidden');" class="p-1.5 rounded-xl border border-amber-400/60 flex flex-col items-center gap-0.5 text-[9.5px] font-black" style="background:#FBF8F1; color:#18130A;">
              <span>📜 بيج كريمي</span>
            </button>
            <button type="button" data-vtheme="pure-platinum" onclick="window.applyVoucherMasterTheme('pure-platinum', true); document.getElementById('voucherQuickThemeDrawer').classList.add('hidden');" class="p-1.5 rounded-xl border border-slate-300 flex flex-col items-center gap-0.5 text-[9.5px] font-black" style="background:#FFFFFF; color:#000000;">
              <span>☀️ أبيض ناصع</span>
            </button>
            <button type="button" data-vtheme="pearl-platinum" onclick="window.applyVoucherMasterTheme('pearl-platinum', true); document.getElementById('voucherQuickThemeDrawer').classList.add('hidden');" class="p-1.5 rounded-xl border border-slate-400 flex flex-col items-center gap-0.5 text-[9.5px] font-black" style="background:#EEF2F6; color:#0F172A;">
              <span>🏛️ بلاتيني</span>
            </button>
          </div>
          <div class="text-[9.5px] font-bold text-slate-400 pt-1">🌙 سمات ليلية داكنة (خط فاتح ناصع):</div>
          <div class="grid grid-cols-2 gap-1">
            <button type="button" data-vtheme="midnight-executive" onclick="window.applyVoucherMasterTheme('midnight-executive', true); document.getElementById('voucherQuickThemeDrawer').classList.add('hidden');" class="p-1.5 rounded-xl border border-indigo-500/50 flex items-center justify-center gap-1 text-[9.5px] font-black" style="background:#04091A; color:#F8FAFC;">
              <span>🌌 كحلي ملكي</span>
            </button>
            <button type="button" data-vtheme="sepia-brown" onclick="window.applyVoucherMasterTheme('sepia-brown', true); document.getElementById('voucherQuickThemeDrawer').classList.add('hidden');" class="p-1.5 rounded-xl border border-amber-600/50 flex items-center justify-center gap-1 text-[9.5px] font-black" style="background:#1A110B; color:#FDF8F5;">
              <span>☕ بني موكا</span>
            </button>
            <button type="button" data-vtheme="obsidian-charcoal" onclick="window.applyVoucherMasterTheme('obsidian-charcoal', true); document.getElementById('voucherQuickThemeDrawer').classList.add('hidden');" class="p-1.5 rounded-xl border border-zinc-600 flex items-center justify-center gap-1 text-[9.5px] font-black" style="background:#000000; color:#FFFFFF;">
              <span>🌑 أسود OLED</span>
            </button>
            <button type="button" data-vtheme="twilight-indigo" onclick="window.applyVoucherMasterTheme('twilight-indigo', true); document.getElementById('voucherQuickThemeDrawer').classList.add('hidden');" class="p-1.5 rounded-xl border border-slate-500 flex items-center justify-center gap-1 text-[9.5px] font-black" style="background:#1E293B; color:#F8FAFC;">
              <span>🛡️ رمادي داكن</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════
         شريط الاستماع الصوتي الذكي المباشر (HUD Live Audio Visualizer)
         يظهر فقط أثناء الاستماع بالمايك، ويعرض النص المباشر + عداد الصمت + زر إيقاف
         ═════════════════════════════════════════════════════════════════ -->
    <div id="masterVoiceLiveBanner" class="hidden bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border-b-2 border-cyan-400 p-2.5 text-xs text-white shadow-2xl sticky top-[36px] z-50 transition-all select-none">
      <div class="flex items-center justify-between gap-2 mb-1.5">
        <div class="flex items-center gap-2">
          <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
          </span>
          <span class="font-black text-cyan-300 text-[11px] flex items-center gap-1.5">
            <span>🎙️ جارٍ الاستماع الذكي</span>
            <!-- مؤشر الموجات الصوتية الذكية المتفاعلة مع طبقة الصوت ونقاء المايك -->
            <span id="voiceWaveBars" class="inline-flex items-center gap-0.5 h-3 px-1 py-0.5 rounded bg-cyan-950/80 border border-cyan-500/30">
              <span class="w-1 bg-cyan-400 rounded-full h-1.5 transition-all duration-75" id="vBar1"></span>
              <span class="w-1 bg-cyan-300 rounded-full h-2.5 transition-all duration-75" id="vBar2"></span>
              <span class="w-1 bg-emerald-400 rounded-full h-3 transition-all duration-75" id="vBar3"></span>
              <span class="w-1 bg-cyan-300 rounded-full h-2 transition-all duration-75" id="vBar4"></span>
              <span class="w-1 bg-cyan-400 rounded-full h-1 transition-all duration-75" id="vBar5"></span>
            </span>
            <span id="voiceSilenceCountdown" class="text-[10px] text-amber-300 font-mono font-bold"></span>
          </span>
        </div>
        <button type="button" onclick="stopMasterVoiceInput(true)" class="px-2.5 py-1 rounded-lg bg-red-600 hover:bg-red-500 text-white font-black text-[10.5px] active:scale-95 shadow transition flex items-center gap-1">
          <i class="fa-solid fa-stop text-[9px]"></i>
          <span>إنهاء وتطبيق</span>
        </button>
      </div>
      <!-- نص الكلام المباشر المسموع لحظة بلحظة -->
      <div class="bg-black/60 rounded-xl p-2 border border-cyan-500/30 text-[11.5px] font-medium text-slate-100 min-h-[34px] max-h-[75px] overflow-y-auto leading-relaxed shadow-inner select-text" id="masterVoiceLiveTranscript">
        تحدث الآن بالسند كاملاً... (المبلغ، مِن، إلى، رقم التحويلة، البنك، البيان)
      </div>
      <!-- شارات الاستخراج والفلترة الذكية الفورية أثناء الكلام -->
      <div id="masterVoiceLiveBadges" class="hidden mt-1.5 flex flex-wrap items-center gap-1.5 text-[10px]"></div>
      <div class="text-[9.5px] text-slate-400 mt-1 flex items-center justify-between">
        <span class="text-cyan-300/90 font-medium">⚡ تسجيل متواصل ذكي ينتهي تلقائياً عند السكوت لمدة 3 ثوانٍ</span>
        <span class="text-slate-400">أو انقر زر المايك ثانية للإيقاف الفوري</span>
      </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════
         2. السطر الثاني المخصص (بتقليل المسافة العلوية لدمج أنيق ومريح):
          التاريخ | الرقم التسلسلي والأسهم
          ═════════════════════════════════════════════════════════════════ -->
    <div class="bg-[#081026]/95 px-2.5 py-1 border-b border-indigo-950/80 sticky top-[36px] z-30 backdrop-blur-md">
      <div class="grid grid-cols-2 gap-2 w-full">
        
        <!-- 1. مساحة التاريخ: اليوم / الشهر / السنة -->
        <div class="relative bg-[#040918] glow-border-cyan rounded-2xl px-2 py-0.5 flex items-center justify-center h-[45px] shadow-inner transition cursor-pointer select-none" onclick="triggerDatePicker()" title="انقر لتعديل التاريخ">
          <div class="flex items-center justify-center gap-1.5 pointer-events-none select-none" dir="ltr">
            <i class="fa-regular fa-calendar text-cyan-400/80 text-xs mr-0.5"></i>
            <span id="voucherDateDisplay" class="text-cyan-200 text-[17.5px] sm:text-[18.5px] font-mono font-black tracking-tighter leading-none"><?php echo date('d/m/Y'); ?></span>
          </div>
          <input type="date" id="voucherDate" value="<?php echo date('Y-m-d'); ?>" oninput="updateVoucherDateDisplay()" onchange="updateVoucherDateDisplay()" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" style="color-scheme: dark;">
        </div>

        <!-- 2. مساحة الرقم التسلسلي والأسهم -->
        <div class="bg-[#040918] glow-border-amber rounded-2xl px-1.5 py-0.5 flex items-center justify-between gap-1 h-[45px] shadow-inner transition cursor-pointer overflow-hidden">
          <!-- سهم يمين صغير على الطرف (للطلوع للأحدث) -->
          <button type="button" onclick="navigateVoucher(1)" id="btnNextVoucher" class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-slate-800/90 hover:bg-slate-700 active:scale-90 text-amber-300 hover:text-amber-200 flex items-center justify-center transition border border-amber-500/40 shadow shrink-0 z-10" title="سهم يمين: السند التالي (للطلوع للأحدث)">
            <i class="fa-solid fa-arrow-right text-[10px]"></i>
          </button>

          <!-- الرقم التسلسلي لوحده كامل ومصغر/مكبر تلقائياً ليظهر 100% بدون قص وبدون مضايقة الأسهم -->
          <div id="voucherDisplayWrapper" class="text-center px-1 flex-1 min-w-0 flex items-center justify-center overflow-hidden">
            <span id="voucherTag" class="hidden"></span>
            <span data-auto-fit-voucher="1" class="text-[22px] sm:text-[23px] font-mono font-black text-amber-300 whitespace-nowrap block tracking-tight leading-none drop-shadow cursor-pointer hover:underline w-full text-center" style="font-size: 22px;" id="currentVoucherDisplay" onclick="showDigitalVoucherReceipt()" title="اضغط لعرض الإيصال الرقمي الرسمي المعتمد ورمز الـ QR 📜"><?php echo htmlspecialchars($nextSequentialVoucherNo); ?></span>
          </div>

          <!-- سهم شمال صغير على الطرف (للنزول للأقدم) -->
          <button type="button" onclick="navigateVoucher(-1)" id="btnPrevVoucher" class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-slate-800/90 hover:bg-slate-700 active:scale-90 text-amber-300 hover:text-amber-200 flex items-center justify-center transition border border-amber-500/40 shadow shrink-0 z-10" title="سهم شمال: السند السابق (للنزول للأقدم)">
            <i class="fa-solid fa-arrow-left text-[10px]"></i>
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

    <!-- Offline Sync Banner Notification (مخفي خلف الشاشة للعمل التلقائي الصامت) -->
    <div id="offlineSyncBanner" class="hidden" style="display: none !important;">
      <div class="flex items-center gap-1.5">
        <span>⚠️</span>
        <span id="offlineBannerText"></span>
      </div>
      <span id="pendingCount">0 معلق</span>
    </div>

    <!-- شريط ربط السند بفاتورة المبيعات أو المشتريات (POS Invoice Integration Banner) -->
    <div id="linkedInvoiceBanner" class="hidden bg-gradient-to-r from-indigo-950 via-slate-900 to-emerald-950 border-b border-indigo-500/50 px-3 py-1.5 text-[11px] text-white items-center justify-between font-bold shadow-md">
      <div class="flex items-center gap-1.5 min-w-0">
        <i class="fa-solid fa-link text-amber-400 text-xs shrink-0"></i>
        <span id="linkedInvoiceBannerText" class="truncate text-indigo-200">مرتبط بفاتورة #---</span>
      </div>
      <div class="flex items-center gap-1 shrink-0">
        <a href="sales-pos.php" id="linkedInvoiceReturnLink" class="px-2 py-0.5 rounded-lg bg-indigo-600/80 hover:bg-indigo-500 text-white text-[10px] font-black border border-indigo-400/50 transition">
          العودة للفاتورة ↩
        </a>
        <button type="button" onclick="clearLinkedInvoiceRef()" class="w-5 h-5 rounded-md bg-slate-800 hover:bg-red-900/60 text-slate-300 hover:text-white flex items-center justify-center text-[10px]" title="إلغاء ربط الفاتورة">✕</button>
      </div>
    </div>

    <!-- Main Scrollable Content (بدون مساحات ميتة متصل بانسيابية) -->
    <main class="flex-1 overflow-y-auto no-scrollbar px-2.5 pt-1.5 pb-36 space-y-2 text-xs">

      <!-- أزرار الدفع الزجاجية العصرية المضيئة الحواف بوضوح تام -->
      <div class="p-1.5 rounded-2xl bg-[#0a132c]/95 border border-indigo-500/35 shadow-[0_4px_18px_rgba(14,165,233,0.14)]">
        <div class="grid grid-cols-4 gap-1.5 text-[11px] font-black" id="paymentMethodsGroup">
          <button type="button" onclick="setPaymentType('transfer')" id="btn-transfer" class="glass-pay-btn is-active py-1.5 px-1 rounded-xl text-center flex flex-col items-center justify-center gap-0.5">
            <i class="fa-solid fa-arrow-right-arrow-left text-xs mb-0.5 text-sky-400"></i>
            <span>تحويل</span>
          </button>
          <button type="button" onclick="setPaymentType('cash')" id="btn-cash" class="glass-pay-btn py-1.5 px-1 rounded-xl text-slate-200 text-center flex flex-col items-center justify-center gap-0.5">
            <i class="fa-solid fa-money-bill-wave text-xs mb-0.5 text-emerald-400"></i>
            <span>كاش</span>
          </button>
          <button type="button" onclick="setPaymentType('bank_cheque')" id="btn-bank_cheque" class="glass-pay-btn py-1.5 px-1 rounded-xl text-slate-200 text-center flex flex-col items-center justify-center gap-0.5">
            <i class="fa-solid fa-money-check text-xs mb-0.5 text-amber-400"></i>
            <span>شيك مصرفي</span>
          </button>
          <button type="button" onclick="setPaymentType('normal_cheque')" id="btn-normal_cheque" class="glass-pay-btn py-1.5 px-1 rounded-xl text-slate-200 text-center flex flex-col items-center justify-center gap-0.5">
            <i class="fa-regular fa-file-lines text-xs mb-0.5 text-indigo-400"></i>
            <span>شيك عادي</span>
          </button>
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           شريط العملة + قيمة الدولار (بخط عادي واضح وتحجيم تلقائي) + مركز الكلفة
           ═════════════════════════════════════════════════════════════════ -->
      <div class="bg-[#0a132c]/95 border border-indigo-500/35 rounded-2xl px-2 py-1.5 flex items-center gap-1.5 shadow-[0_4px_16px_rgba(14,165,233,0.12)]">
        
        <!-- مساحة العملة: خط معتمد واضح يصغر تلقائياً إذا كان الاسم طويلاً -->
        <div class="relative shrink-0 w-[98px]">
          <select id="primaryCurrencySelect" onchange="selectCurrency(this.value); fitCurrencySelectSize();" class="w-full appearance-none bg-[#040918] glow-border-amber rounded-xl pr-1.5 pl-3.5 py-1 text-[11.5px] font-black text-amber-300 focus:outline-none focus:border-amber-400 cursor-pointer h-[40px] shadow-inner text-center" title="اختيار العملة">
            <option value="SDG" selected>جنيه سوداني</option>
            <option value="USD">دولار ($)</option>
            <option value="SAR">ريال سعودي</option>
            <option value="AED">درهم إماراتي</option>
            <option value="EGP">جنيه مصري</option>
            <option value="EUR">يورو (€)</option>
          </select>
          <div class="absolute left-1 top-3.5 pointer-events-none text-amber-300 text-[8.5px]">
            <i class="fa-solid fa-chevron-down"></i>
          </div>
        </div>

        <!-- قيمة الصرف (بخط عادي واضح ومقاس معتمد 17.5px يصغر تلقائياً عند زيادة الأرقام) -->
        <div class="bg-[#040918] glow-border-emerald rounded-xl px-1.5 py-1 flex items-center justify-center gap-0.5 w-[92px] shrink-0 h-[40px] shadow-inner transition cursor-pointer overflow-hidden" onclick="document.getElementById('usdRate').focus()" title="سعر الصرف الرسمي المعتمد في قاعدة البيانات - انقر للتعديل والمزامنة الفورية في كل الشاشات">
          <span class="text-emerald-400 font-mono text-xs font-bold shrink-0" id="rateSymbolLabel">$</span>
          <input type="number" id="usdRate" value="<?php echo htmlspecialchars((string)$systemUsdRate); ?>" onchange="updateSystemUsdRate(this.value); fitUsdRateSize();" oninput="calculateExchange('rate'); fitUsdRateSize();" class="w-full min-w-0 bg-transparent text-emerald-300 font-mono font-medium text-[17.5px] focus:outline-none text-center cursor-text tracking-tight [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" placeholder="<?php echo htmlspecialchars((string)$systemUsdRate); ?>" title="سعر صرف الدولار في قاعدة البيانات">
        </div>

        <!-- المخزن / مركز الكلفة: مقاس معتمد 12px يصغر تلقائياً إذا كان اسم المخزن طويلاً ليظهر كاملاً -->
        <div class="flex-1 min-w-0 bg-[#040918] glow-border-indigo rounded-xl px-1.5 flex items-center justify-center h-[40px] relative shadow-inner overflow-hidden">
          <input type="text" id="costCenterInput" list="costCentersDataList" onfocus="this.classList.remove('mv-ghost-default')" oninput="this.classList.remove('mv-ghost-default'); fitCostCenterSize();" onchange="this.classList.remove('mv-ghost-default'); fitCostCenterSize();" placeholder="المخزن / مركز الكلفة" class="mv-ghost-default w-full bg-transparent text-indigo-100 placeholder:text-slate-500 placeholder:font-medium text-center font-bold focus:outline-none" value="مخزن بحري الرئيسي">
          <datalist id="costCentersDataList">
            <option value="مخزن بحري الرئيسي"></option>
            <option value="مخزن أم درمان"></option>
            <option value="فرع بورتسودان"></option>
            <option value="الإدارة العامة"></option>
          </datalist>
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           المبالغ المالية (مستوى متساوٍ ومحاذاة تامة بدون مربعات التقريب أو شارة العملة الصغيرة):
           - اليمين: المبلغ المحلي
           - اليسار: المبلغ الأجنبي/الدولار
           ═════════════════════════════════════════════════════════════════ -->
      <div id="amountContainerCard" class="rounded-2xl p-2.5 space-y-2 backdrop-blur-md relative">
        
        <div class="grid grid-cols-2 gap-2.5">
          
          <!-- 1. اليمين: المبلغ المحلي -->
          <div>
            <div class="flex items-center justify-between mb-1 px-1 h-6">
              <div class="flex items-center gap-1.5">
                <button type="button" onclick="startFieldVoiceInput('sdg', this)" class="field-mic-btn w-6 h-6 rounded-full bg-sky-950/95 border border-sky-400/80 text-sky-200 flex items-center justify-center active:scale-90 transition shrink-0" title="تسجيل صوتي للمبلغ المحلي فقط" aria-label="تسجيل صوتي للمبلغ المحلي">
                  <i class="fa-solid fa-microphone text-[10px]"></i>
                </button>
                <span class="text-xs sm:text-sm font-black text-sky-300">المبلغ المحلي</span>
              </div>
            </div>
            <div id="sdgAmountCard" class="amount-display-card bg-[#040918]/95 p-2 rounded-2xl glow-border-sky relative cursor-pointer flex items-center justify-center min-h-[72px]" onclick="if(event.target.id!=='sdgAmount') openNumberPropertiesModal(event, 'sdg')" oncontextmenu="openNumberPropertiesModal(event, 'sdg'); return false;" title="انقر لتخصيص الألوان والتقريب والحجم للمبلغ المحلي">
              <input type="text" id="sdgAmount" placeholder="0" inputmode="decimal" onfocus="this.select()" onblur="formatCurrentAmountInput('sdg'); onAmountInputBlur('sdg');" oninput="handleAmountInput('sdg'); updateTafqeet(); onAmountInputLive('sdg');" onkeydown="if(event.key===' ' || event.code==='Space'){ event.preventDefault(); openNumberPropertiesModal(event, 'sdg'); }" oncontextmenu="openNumberPropertiesModal(event, 'sdg'); return false;" ondblclick="openNumberPropertiesModal(event, 'sdg')" class="amount-number-input w-full bg-transparent text-center focus:outline-none placeholder-slate-700 font-black tracking-tight" style="font-size: var(--num-size-sdg, 32px); font-weight: var(--num-weight-sdg, 900); color: var(--num-color-sdg, #ffffff); text-align: center;">
            </div>
          </div>

          <!-- 2. اليسار: المبلغ الأجنبي / الدولار -->
          <div>
            <div class="flex items-center justify-between mb-1 px-1 h-6">
              <div class="flex items-center gap-1">
                <span class="text-xs sm:text-sm font-black text-emerald-300" id="foreignAmountTitle">مبلغ الدولار</span>
              </div>
              <div class="flex items-center gap-1.5">
                <button type="button" onclick="startFieldVoiceInput('usd', this)" class="field-mic-btn w-6 h-6 rounded-full bg-emerald-950/95 border border-emerald-400/80 text-emerald-200 flex items-center justify-center active:scale-90 transition shrink-0" title="تسجيل صوتي للمبلغ بالدولار فقط" aria-label="تسجيل صوتي للمبلغ بالدولار">
                  <i class="fa-solid fa-microphone text-[10px]"></i>
                </button>
              </div>
            </div>
            <div id="usdAmountCard" class="amount-display-card bg-[#040918]/95 p-2 rounded-2xl glow-border-emerald relative cursor-pointer flex items-center justify-center min-h-[72px]" onclick="if(event.target.id!=='usdAmount') openNumberPropertiesModal(event, 'usd')" oncontextmenu="openNumberPropertiesModal(event, 'usd'); return false;" title="انقر لتخصيص الألوان والتقريب والحجم للمبلغ بالدولار">
              <input type="text" id="usdAmount" placeholder="0.00" inputmode="decimal" onfocus="this.select()" onblur="formatCurrentAmountInput('usd'); onAmountInputBlur('usd');" oninput="handleAmountInput('foreign'); updateTafqeet(); onAmountInputLive('usd');" onkeydown="if(event.key===' ' || event.code==='Space'){ event.preventDefault(); openNumberPropertiesModal(event, 'usd'); }" oncontextmenu="openNumberPropertiesModal(event, 'usd'); return false;" ondblclick="openNumberPropertiesModal(event, 'usd')" class="amount-number-input w-full bg-transparent text-center focus:outline-none placeholder-slate-700 font-black tracking-tight" style="font-size: var(--num-size-usd, 32px); font-weight: var(--num-weight-usd, 900); color: var(--num-color-usd, #10b981); text-align: center;">
            </div>
          </div>

        </div>

        <!-- تفقيط المبلغ كتابة بالحروف العربية بتصميم فاخر ومريح للأعصاب -->
        <div id="tafqeetDisplay" class="hidden transition-all duration-300 bg-gradient-to-r from-amber-950/70 via-slate-950 to-amber-950/70 border border-amber-500/60 px-3 py-2 rounded-xl text-center shadow-[0_0_15px_rgba(245,158,11,0.2)] flex items-center justify-center gap-2">
          <i class="fa-solid fa-feather-pointed text-amber-400 text-xs shrink-0 animate-pulse"></i>
          <span id="tafqeetText" class="text-xs sm:text-sm font-black text-amber-300 tracking-wide font-sans leading-relaxed"></span>
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           من حساب (الطرف الدافع / المسلِّم - دائن):
           - السطر الأول في خط واحد دائماً: علامة المايك + من حساب (الدافع) + كشف حساب (بدون كلمة مِن المنفردة)
           ═════════════════════════════════════════════════════════════════ -->
      <div id="fromAccountCard" class="bg-gradient-to-b from-rose-950/45 via-[#0b132b]/95 to-[#060c1e]/95 glow-border-red rounded-2xl p-2.5 sm:p-3 relative">
        <div id="fromHeaderRow" class="flex items-center justify-between mb-1.5 gap-1.5 flex-nowrap whitespace-nowrap overflow-hidden">
          <div class="flex items-center gap-1.5 min-w-0 flex-nowrap whitespace-nowrap">
            <button type="button" onclick="startFieldVoiceInput('from', this)" class="field-mic-btn w-7 h-7 rounded-full bg-rose-950/90 border border-rose-400/70 text-rose-200 flex items-center justify-center active:scale-90 transition shrink-0" title="تسجيل صوتي لحساب (من حساب) فقط" aria-label="تسجيل صوتي لحساب من">
              <i class="fa-solid fa-microphone text-[11px]"></i>
            </button>
            <span id="fromHeaderTitle" class="text-[13.5px] sm:text-sm font-black text-rose-200 whitespace-nowrap leading-none">من حساب (الدافع)</span>
          </div>

          <div class="flex items-center gap-1 shrink-0 flex-nowrap whitespace-nowrap">
            <!-- زر كشف الحساب المصغر الفوري -->
            <button type="button" id="fromStatementBtn" onclick="openAccountMiniStatement('from')" class="px-2 py-1 rounded-lg bg-rose-950/85 hover:bg-rose-900 text-rose-200 border border-rose-500/50 text-[10.5px] font-black flex items-center gap-1 shrink-0 whitespace-nowrap active:scale-95 transition shadow-sm" title="عرض آخر 10 حركات وكشف الحساب">
              <span>📊 كشف حساب</span>
            </button>
            <!-- زر مسح الحساب -->
            <button type="button" onclick="clearAccountSearch('from')" id="fromClearBtn" class="hidden w-6 h-6 rounded-lg bg-rose-950/80 hover:bg-rose-800 text-rose-200 border border-rose-500/40 flex items-center justify-center text-xs font-black shrink-0 active:scale-90 transition" title="مسح الحساب">✕</button>
          </div>
        </div>

        <!-- شريط الرصيد الحي وبعد القيد في نفس المستوى والسطر -->
        <div id="fromBalanceWrapper" class="mb-1.5 flex items-center whitespace-nowrap overflow-x-auto no-scrollbar gap-x-1 px-2 py-1 rounded-xl bg-[#040918]/90 border border-rose-500/25 text-[10px]">
          <span class="text-slate-400 font-bold shrink-0">الرصيد:</span>
          <span id="fromAccountSdgBalance" class="font-mono font-black text-amber-300 shrink-0">---</span>
          <span class="text-amber-300/80 text-[9px] font-bold shrink-0">ج.س</span>
          <span id="fromBalanceNatureTag" class="hidden px-1 py-0.2 rounded text-[8.5px] font-black shrink-0"></span>
          <span class="text-slate-600 shrink-0">|</span>
          <span class="text-emerald-400 font-mono font-black text-[10px] shrink-0">$<span id="fromAccountUsdBalance">---</span></span>
          <span id="fromProjectedBadge" class="hidden inline-flex items-center gap-0.5 text-sky-300 shrink-0">
            <span class="text-slate-600">|</span>
            <span id="fromProjectedLabel" class="font-bold text-sky-300">بعد القيد:</span>
            <span id="fromProjectedAmount" class="font-mono font-black text-sky-300">---</span>
            <span class="text-sky-300/80 text-[9px] font-bold">ج.س</span>
          </span>
        </div>

        <!-- حقل اسم الحساب -->
        <div class="relative w-full">
          <input type="text" id="fromSearchInput" oninput="liveSearchAccount('from'); fitAccountInputSize('from');" onblur="handleAccountInputBlur('from'); fitAccountInputSize('from');" onclick="onAccountInputFocus('from')" onfocus="onAccountInputFocus('from')" placeholder="اضغط لاختيار أو كتابة اسم الحساب..." value="" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" class="account-search-input w-full bg-[#040918] glow-border-red rounded-xl text-white focus:outline-none placeholder-slate-500">
          
          <!-- القائمة المنسدلة الذكية الفورية مع تبويبات التصنيف الذكية -->
          <div id="fromDropdown" class="hidden absolute top-full right-0 left-0 mt-1.5 bg-[#040918] border-2 border-rose-500/60 rounded-2xl shadow-2xl z-40 max-h-72 overflow-y-auto no-scrollbar"></div>
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           مؤشر اتجاه التدفق المالي من (من حساب) إلى (إلى حساب)
           ═════════════════════════════════════════════════════════════════ -->
      <div class="flex items-center justify-center my-[-6px] relative z-20 px-4">
        <div class="flow-stream-track"></div>
        <button type="button" onclick="triggerCrystalFlowAnimation()" id="crystalFlowBtn" class="kinetic-flow-capsule active:scale-90 mx-2 shrink-0" title="مسار تدفق الحركة المالية من الأعلى (من حساب) إلى الأسفل (إلى حساب)">
          <div class="flex flex-col items-center justify-center leading-none gap-0.5 pointer-events-none">
            <i class="fa-solid fa-chevron-down text-[10px] chevron-stream-1"></i>
            <i class="fa-solid fa-arrow-down text-xs font-black text-white chevron-stream-2"></i>
          </div>
        </button>
        <div class="flow-stream-track"></div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           إلى حساب (الطرف المستلم - مدين):
           - السطر الأول في خط واحد دائماً باللون الأخضر: علامة المايك + إلى حساب (المستلم) + كشف حساب (بدون كلمة إلى المنفردة)
           ═════════════════════════════════════════════════════════════════ -->
      <div id="toAccountCard" class="bg-gradient-to-b from-emerald-950/45 via-[#0b132b]/95 to-[#060c1e]/95 glow-border-emerald rounded-2xl p-2.5 sm:p-3 relative">
        <div id="toHeaderRow" class="flex items-center justify-between mb-1.5 gap-1.5 flex-nowrap whitespace-nowrap overflow-hidden">
          <div class="flex items-center gap-1.5 min-w-0 flex-nowrap whitespace-nowrap">
            <button type="button" onclick="startFieldVoiceInput('to', this)" class="field-mic-btn w-7 h-7 rounded-full bg-emerald-950/90 border border-emerald-400/70 text-emerald-200 flex items-center justify-center active:scale-90 transition shrink-0" title="تسجيل صوتي لحساب (إلى حساب) فقط" aria-label="تسجيل صوتي لحساب إلى">
              <i class="fa-solid fa-microphone text-[11px]"></i>
            </button>
            <span id="toHeaderTitle" class="text-[13.5px] sm:text-sm font-black text-emerald-300 whitespace-nowrap leading-none">إلى حساب (المستلم)</span>
          </div>

          <div class="flex items-center gap-1 shrink-0 flex-nowrap whitespace-nowrap">
            <!-- زر كشف الحساب المصغر الفوري باللون الأخضر الموحد -->
            <button type="button" id="toStatementBtn" onclick="openAccountMiniStatement('to')" class="px-2 py-1 rounded-lg bg-emerald-950/85 hover:bg-emerald-900 text-emerald-300 border border-emerald-500/50 text-[10.5px] font-black flex items-center gap-1 shrink-0 whitespace-nowrap active:scale-95 transition shadow-sm" title="عرض آخر 10 حركات وكشف الحساب">
              <span>📊 كشف حساب</span>
            </button>
            <!-- زر مسح الحساب -->
            <button type="button" onclick="clearAccountSearch('to')" id="toClearBtn" class="hidden w-6 h-6 rounded-lg bg-emerald-950/80 hover:bg-emerald-800 text-emerald-200 border border-emerald-500/40 flex items-center justify-center text-xs font-black shrink-0 active:scale-90 transition" title="مسح الحساب">✕</button>
          </div>
        </div>

        <!-- شريط الرصيد الحي وبعد القيد في نفس المستوى والسطر -->
        <div id="toBalanceWrapper" class="mb-1.5 flex items-center whitespace-nowrap overflow-x-auto no-scrollbar gap-x-1 px-2 py-1 rounded-xl bg-[#040918]/90 border border-emerald-500/25 text-[10px]">
          <span class="text-slate-400 font-bold shrink-0">الرصيد:</span>
          <span id="toAccountSdgBalance" class="font-mono font-black text-amber-300 shrink-0">---</span>
          <span class="text-amber-300/80 text-[9px] font-bold shrink-0">ج.س</span>
          <span id="toBalanceNatureTag" class="hidden px-1 py-0.2 rounded text-[8.5px] font-black shrink-0"></span>
          <span class="text-slate-600 shrink-0">|</span>
          <span class="text-emerald-400 font-mono font-black text-[10px] shrink-0">$<span id="toAccountUsdBalance">---</span></span>
          <span id="toProjectedBadge" class="hidden inline-flex items-center gap-0.5 text-sky-300 shrink-0">
            <span class="text-slate-600">|</span>
            <span id="toProjectedLabel" class="font-bold text-sky-300">بعد القيد:</span>
            <span id="toProjectedAmount" class="font-mono font-black text-sky-300">---</span>
            <span class="text-sky-300/80 text-[9px] font-bold">ج.س</span>
          </span>
        </div>

        <!-- حقل اسم الحساب -->
        <div class="relative w-full">
          <input type="text" id="toSearchInput" oninput="liveSearchAccount('to'); fitAccountInputSize('to');" onblur="handleAccountInputBlur('to'); fitAccountInputSize('to');" onclick="onAccountInputFocus('to')" onfocus="onAccountInputFocus('to')" placeholder="اضغط لاختيار أو كتابة اسم المستلم..." value="" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" class="account-search-input w-full bg-[#040918] glow-border-emerald rounded-xl text-white focus:outline-none placeholder-slate-500">
          
          <!-- القائمة المنسدلة الذكية الفورية مع تبويبات التصنيف الذكية -->
          <div id="toDropdown" class="hidden absolute top-full right-0 left-0 mt-1.5 bg-[#040918] border-2 border-emerald-500/60 rounded-2xl shadow-2xl z-40 max-h-72 overflow-y-auto no-scrollbar"></div>
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           رقم التحويلة واسم البنك (زيادة الارتفاع 20% إلى 48px + تحجيم ذكي موزّن):
           - رقم التحويلة: لون ذهبي دافئ أنيق بخط ذكي يتكيف تلقائياً (6 أرقام كبيرة، 10 متوسطة، حتى 30 رقم)
           - اسم البنك: لون أزرق جليدي فيروزي أنيق (الخط المعتمد 13.5px)
           ═════════════════════════════════════════════════════════════════ -->
      <div id="transferContainer" class="bg-gradient-to-br from-[#0c1631]/90 via-[#081024]/95 to-[#040918]/98 border border-indigo-500/35 rounded-2xl px-2.5 py-2 space-y-1.5 shadow-[0_6px_24px_rgba(0,0,0,0.45),inset_0_1px_0_0_rgba(255,255,255,0.08)] backdrop-blur-md">
        <input type="file" id="directReceiptCameraInput" accept="image/*" capture="environment" class="hidden" onchange="handleDirectReceiptSelected(event)">
        <input type="file" id="directReceiptGalleryInput" accept="image/*" class="hidden" onchange="handleDirectReceiptSelected(event)">

        <div class="grid grid-cols-[1.18fr_0.82fr] gap-2 items-start">
          <!-- 1. رقم التحويلة (ارتفاع 48px بزيادة 20% + وزن خط تلقائي بالذكاء الاصطناعي حسب عدد الأرقام) -->
          <div class="flex flex-col">
            <div class="flex items-center gap-1 mb-1 h-5">
              <button type="button" onclick="startFieldVoiceInput('transfer', this)" class="field-mic-btn w-5 h-5 rounded-full bg-amber-950/95 border border-amber-400/80 text-amber-200 flex items-center justify-center active:scale-90 transition shrink-0" title="تسجيل صوتي لرقم التحويلة فقط" aria-label="تسجيل صوتي لرقم التحويلة">
                <i class="fa-solid fa-microphone text-[9px]"></i>
              </button>
              <label class="text-xs font-black text-amber-300 flex items-center gap-1" id="refLabel">
                <i class="fa-solid fa-receipt text-amber-400 text-[11px]"></i>
                <span>رقم التحويل:</span>
              </label>
            </div>
            <input type="text" id="transferInput" oninput="handleTransferLiveInput(this.value); onTransferInputLive(this.value);" onblur="handleTransferBlur(this.value); onTransferInputBlur();" placeholder="رقم التحويل" class="w-full bg-[#040918] glow-border-amber rounded-xl px-2 h-[48px] font-mono font-black text-yellow-300 focus:outline-none tracking-tight box-border cursor-text" style="font-size: 16px; text-align: center; direction: ltr;">
          </div>

          <!-- 2. اسم البنك (الخط المعتمد 13.5px بارتفاع 48px لموازنة الصف) -->
          <div class="flex flex-col">
            <div class="flex items-center gap-1 mb-1 h-5">
              <button type="button" onclick="startFieldVoiceInput('bank', this)" class="field-mic-btn w-5 h-5 rounded-full bg-cyan-950/95 border border-cyan-400/80 text-cyan-200 flex items-center justify-center active:scale-90 transition shrink-0" title="تسجيل صوتي لاسم البنك" aria-label="تسجيل صوتي لاسم البنك">
                <i class="fa-solid fa-microphone text-[9px]"></i>
              </button>
              <label class="text-xs font-black text-cyan-300 flex items-center gap-1" id="bankNameLabel">
                <i class="fa-solid fa-building-columns text-cyan-400 text-[11px]"></i>
                <span>اسم البنك:</span>
              </label>
            </div>
            <input type="text" id="bankNameInput" list="banksDataList" oninput="onBankInputLive(this.value); fitBankNameSize();" onchange="fitBankNameSize()" onblur="onBankInputBlur(); fitBankNameSize();" placeholder="اسم البنك" class="w-full bg-[#040918] glow-border-cyan rounded-xl px-2 h-[48px] text-[13.5px] font-black text-cyan-300 focus:outline-none box-border cursor-text" value="">
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

          <!-- شارة التحقق من رقم التحويلة (تختفي تماماً عند عدم وجود تنبيه لمنع الفراغ السفلي) -->
          <div id="transferFeedbackBadge" class="empty:hidden col-span-2 transition-all flex items-center justify-center"></div>
        </div>

        <!-- بطاقة الإشعار البنكي المرفق والمثبت كمستند ثبوتي -->
        <div id="receiptAttachmentCard" class="hidden p-2 rounded-xl bg-slate-950 border border-emerald-500/50 flex items-center justify-between gap-2 shadow-inner">
          <div class="flex items-center gap-2 min-w-0">
            <div class="w-10 h-10 rounded-lg border border-emerald-400/40 overflow-hidden bg-black shrink-0 cursor-pointer" onclick="openAttachedReceiptModal()">
              <img id="receiptAttachmentThumb" src="" alt="إشعار البنك" class="w-full h-full object-cover">
            </div>
            <div class="min-w-0 text-right">
              <div class="text-[11px] font-black text-emerald-300 flex items-center gap-1">
                <i class="fa-solid fa-shield-halved text-[10px]"></i>
                <span>مستند ثبوتي معتمد (مرفق)</span>
              </div>
              <div id="receiptAttachmentSummary" class="text-[10px] text-slate-300 font-mono truncate">إشعار بنكك</div>
            </div>
          </div>
          <div class="flex items-center gap-1 shrink-0">
            <button type="button" onclick="openAttachedReceiptModal()" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs flex items-center justify-center border border-slate-700 active:scale-90" title="معاينة وتكبير الإشعار">
              <i class="fa-solid fa-eye"></i>
            </button>
            <button type="button" onclick="triggerDirectReceiptCapture()" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-300 text-xs flex items-center justify-center border border-slate-700 active:scale-90" title="إعادة التصوير">
              <i class="fa-solid fa-camera-rotate"></i>
            </button>
            <button type="button" onclick="removeAttachedReceipt()" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-950/50 text-red-400 text-xs flex items-center justify-center border border-slate-700 active:scale-90" title="حذف المرفق">
              <i class="fa-solid fa-trash-can"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           البيان:
           - لون بلاتيني ناصع وأنيق
           - يكتب فقط "البيان" داخل المساحة بلون خفيف
           ═════════════════════════════════════════════════════════════════ -->
      <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-2">
        <textarea id="voucherNotes" rows="2" oninput="onNotesInputLive(this.value)" onblur="onNotesInputBlur()" placeholder="البيان" class="w-full bg-slate-950 glow-border-indigo rounded-xl p-2.5 text-slate-100 placeholder-slate-400/60 placeholder:font-medium text-xs font-bold focus:outline-none resize-none cursor-text"></textarea>
      </div>

      <!-- ═════════════════════════════════════════════════════════════════
           أيقونات المرفقات العصرية (الكاميرا، الاستوديو، من واتساب):
           - زر تحكم لآلية الحفظ: أوتوماتيك (رنة وهزة وحفظ فوري) أو يدوي (إشعاراً بإشعار)
           - تصوير حتى 20 إشعار من الكاميرا
           - اختيار حتى 20 إشعار من الاستوديو
           - سحب حتى 20 إشعار من الواتساب
           ═════════════════════════════════════════════════════════════════ -->
      <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-2 space-y-2">
        
        <!-- زر التحكم في آلية الحفظ والتفريغ (أوتوماتيك ⚡ / يدوي 🖐️) -->
        <div id="batchModeBarWrapper" class="flex items-center justify-between p-1.5 rounded-xl bg-slate-950 border border-slate-700/70 shadow-inner">
          <div class="flex items-center gap-1 text-[11px] font-bold text-slate-300">
            <i class="fa-solid fa-sliders text-cyan-400 text-xs"></i>
            <span>آلية الحفظ:</span>
          </div>
          <div id="batchModeToggleGroup" class="flex items-center gap-1 bg-slate-900 p-0.5 rounded-lg border border-slate-800">
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

        <!-- أزرار المرفقات الأربعة العصرية المتناسقة: كاميرا | استوديو | OCR | واتساب -->
        <div class="grid grid-cols-4 gap-1.5 sm:gap-2">
          
          <!-- 1. الكاميرا -->
          <button type="button" onclick="openBatchIntakeModal('camera')" class="modern-media-card media-card-camera flex flex-col items-center justify-center py-2 px-1 rounded-2xl bg-gradient-to-b from-indigo-950/90 via-slate-900 to-slate-950 border border-indigo-500/40 text-white shadow-lg active:scale-95 transition-all group cursor-pointer min-h-[72px]" title="تصوير إشعارات بالكاميرا حتى 20 إشعار">
            <div class="relative w-8 h-8 rounded-xl bg-indigo-600/80 flex items-center justify-center mb-1 shadow-md border border-indigo-300/40 group-hover:scale-110 transition">
              <i class="fa-solid fa-camera text-sm text-white"></i>
              <span class="absolute top-1 right-1 w-1.5 h-1.5 rounded-full bg-red-400 animate-ping"></span>
            </div>
            <span class="text-[11px] font-black text-indigo-100">كاميرا</span>
          </button>

          <!-- 2. الاستوديو -->
          <button type="button" onclick="openStudioDirectly()" ondblclick="openBatchIntakeModal('gallery')" class="modern-media-card media-card-gallery flex flex-col items-center justify-center py-2 px-1 rounded-2xl bg-gradient-to-b from-amber-950/80 via-slate-900 to-slate-950 border border-amber-500/40 text-slate-100 shadow-lg active:scale-95 transition-all group cursor-pointer min-h-[72px]" title="انقر لفتح الاستوديو فوراً واختيار الإشعارات وتفريغها تلقائياً">
            <div class="w-8 h-8 rounded-xl bg-amber-600/80 flex items-center justify-center mb-1 shadow-md border border-amber-300/40 group-hover:scale-110 transition">
              <i class="fa-solid fa-images text-sm text-white"></i>
            </div>
            <span class="text-[11px] font-black text-amber-200">استوديو</span>
          </button>

          <!-- 3. إشعار OCR -->
          <button type="button" onclick="triggerDirectReceiptCapture()" class="modern-media-card media-card-ocr flex flex-col items-center justify-center py-2 px-1 rounded-2xl bg-gradient-to-b from-cyan-950/90 via-slate-900 to-slate-950 border border-cyan-500/40 text-cyan-100 shadow-lg active:scale-95 transition-all group cursor-pointer min-h-[72px]" title="تصوير أو رفع إشعار واستخراج البيانات بالذكاء الاصطناعي (OCR)">
            <div class="relative w-8 h-8 rounded-xl bg-cyan-600/80 flex items-center justify-center mb-1 shadow-md border border-cyan-300/40 group-hover:scale-110 transition">
              <i class="fa-solid fa-file-invoice text-sm text-white"></i>
              <span class="absolute -top-1 -right-1 px-1 rounded bg-teal-400 text-black text-[8px] font-black">AI</span>
            </div>
            <span class="text-[11px] font-black text-cyan-200">إشعار OCR</span>
          </button>

          <!-- 4. من واتساب -->
          <button type="button" onclick="openWhatsAppDirectly()" ondblclick="openBatchIntakeModal('whatsapp')" class="modern-media-card media-card-whatsapp flex flex-col items-center justify-center py-2 px-1 rounded-2xl bg-gradient-to-b from-emerald-950/90 via-slate-900 to-slate-950 border border-emerald-500/40 text-white shadow-lg active:scale-95 transition-all group cursor-pointer min-h-[72px]" title="انقر لفتح تطبيق واتساب مباشرة لسحب وتفريغ الإشعارات">
            <div class="relative w-8 h-8 rounded-xl bg-emerald-600 flex items-center justify-center mb-1 shadow-md border border-emerald-300/50 group-hover:scale-110 transition">
              <i class="fa-brands fa-whatsapp text-base text-white"></i>
              <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
            </div>
            <span class="text-[11px] font-black text-emerald-200">واتساب</span>
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
         الشريط السفلي التفاعلي المريح مقسم باحترافية إلى صفين (4 أزرار في كل صف):
         - الصف الأول (الأعلى): العمليات الأساسية للسند [حفظ (✓)] [جديد] [افتح] [تعديل]
         - الصف الثاني (الأسفل): المشاركة والتوثيق والتنقل [إرسال] [طباعة] [حذف] [رجوع]
         ═════════════════════════════════════════════════════════════════ -->
    <div class="bottom-bar-fixed bg-slate-950/95 border-t border-slate-800 p-2 backdrop-blur-md">
      <div class="flex flex-col gap-1.5 w-full">
        
        <!-- الصف الأول (الأعلى): 4 أزرار أساسية للإنشاء والحفظ والاستعراض والتعديل -->
        <div class="grid grid-cols-4 gap-1.5 w-full">
          
          <!-- 1. زر حفظ (✓): الزر الأساسي الأبرز -->
          <button type="button" onclick="handleQuickCheckSave()" id="btnSaveMain" class="action-btn-kinetic royal-crystal-btn royal-btn-save h-[42px] sm:h-[44px] min-w-0 px-2 rounded-xl text-white font-black text-[12px] sm:text-[13px] flex items-center justify-center gap-1.5 transition" title="تأكيد وحفظ السند">
            <i class="fa-solid fa-check text-xs sm:text-sm font-black shrink-0"></i>
            <span id="saveButtonLabel" class="truncate">حفظ (✓)</span>
          </button>

          <!-- 2. زر جديد: مسح الشاشة وبدء سند جديد فارغ -->
          <button type="button" onclick="startNewVoucher()" id="btnNewVoucher" class="action-btn-kinetic royal-crystal-btn royal-btn-new h-[42px] sm:h-[44px] min-w-0 px-2 rounded-xl text-white font-black text-[12px] sm:text-[13px] flex items-center justify-center gap-1.5 transition" title="مسح الشاشة وبدء سند جديد فارغ">
            <i class="fa-solid fa-plus text-white text-xs sm:text-sm font-black shrink-0"></i>
            <span class="truncate">جديد</span>
          </button>

          <!-- 3. زر افتح: فتح واستعراض وبحث في السندات -->
          <button type="button" onclick="openSearchVouchersModal()" id="btnOpenVoucher" class="action-btn-kinetic royal-crystal-btn royal-btn-open h-[42px] sm:h-[44px] min-w-0 px-2 rounded-xl text-white font-black text-[12px] sm:text-[13px] flex items-center justify-center gap-1.5 transition" title="فتح واستعراض وبحث في السندات المسجلة">
            <i class="fa-solid fa-folder-open text-white text-xs sm:text-sm shrink-0"></i>
            <span class="truncate">افتح</span>
          </button>

          <!-- 4. زر تعديل: تعديل بيانات السند الحالي -->
          <button type="button" onclick="handleEdit()" id="btnEditVoucher" class="action-btn-kinetic royal-crystal-btn royal-btn-edit h-[42px] sm:h-[44px] min-w-0 px-2 rounded-xl text-white font-black text-[12px] sm:text-[13px] flex items-center justify-center gap-1.5 transition" title="تعديل بيانات السند الحالي">
            <i class="fa-solid fa-pen-to-square text-white text-xs sm:text-sm shrink-0"></i>
            <span class="truncate">تعديل</span>
          </button>

        </div>

        <!-- الصف الثاني (الأسفل): 4 أزرار للمشاركة والطباعة والحذف والرجوع -->
        <div class="grid grid-cols-4 gap-1.5 w-full">

          <!-- 5. زر إرسال واتساب -->
          <button type="button" onclick="openSendWhatsAppModal()" id="btnSendWhatsApp" class="action-btn-kinetic royal-crystal-btn royal-btn-whatsapp h-[42px] sm:h-[44px] min-w-0 px-2 rounded-xl text-white font-black text-[12px] sm:text-[13px] flex items-center justify-center gap-1.5 transition" title="إرسال تقرير السند للعميل عبر الواتساب">
            <i class="fa-brands fa-whatsapp text-white text-sm sm:text-base shrink-0"></i>
            <span class="truncate">إرسال</span>
          </button>

          <!-- 6. زر طباعة: طباعة إيصال حراري وسند رسمي -->
          <button type="button" onclick="handlePrintVoucher()" id="btnPrintVoucher" class="action-btn-kinetic royal-crystal-btn royal-btn-print h-[42px] sm:h-[44px] min-w-0 px-2 rounded-xl text-white font-black text-[12px] sm:text-[13px] flex items-center justify-center gap-1.5 transition" title="طباعة إيصال حراري أو سند رسمي معتمد">
            <i class="fa-solid fa-print text-white text-xs sm:text-sm shrink-0"></i>
            <span class="truncate">طباعة</span>
          </button>

          <!-- 7. زر حذف: حذف السند الحالي وعكس أثره المالي -->
          <button type="button" onclick="handleDelete()" id="btnDeleteVoucher" class="action-btn-kinetic royal-crystal-btn royal-btn-delete h-[42px] sm:h-[44px] min-w-0 px-2 rounded-xl text-white font-black text-[12px] sm:text-[13px] flex items-center justify-center gap-1.5 transition" title="حذف السند الحالي وعكس أثره المالي">
            <i class="fa-solid fa-trash-can text-white text-xs sm:text-sm shrink-0"></i>
            <span class="truncate">حذف</span>
          </button>

          <!-- 8. زر خروج / رجوع -->
          <button type="button" onclick="handleExit()" id="btnBackVoucher" class="action-btn-kinetic royal-crystal-btn royal-btn-exit h-[42px] sm:h-[44px] min-w-0 px-2 rounded-xl text-white font-black text-[12px] sm:text-[13px] flex items-center justify-center gap-1.5 transition" title="إنهاء والخروج للقائمة الرئيسية">
            <i class="fa-solid fa-arrow-right-from-bracket text-white text-xs sm:text-sm shrink-0"></i>
            <span class="truncate">خروج</span>
          </button>

        </div>

        <!-- عناصر داعمة صامتة بدون أخطاء برمجية -->
        <button type="button" id="btnAutoSendToggle" onclick="toggleAutoSend()" class="hidden"></button>
        <span id="autoSendStatusText" class="hidden">أون</span>

      </div>
    </div>

  </div>

  <!-- ═════════════════════════════════════════════════════════════════
       نافذة كشف الحساب المصغر الفوري (Mini Account Statement Bottom-Sheet)
       تعرض الرصيد الحي وآخر 10 حركات للطرف المختار مع رابط لكشف الحساب الشامل
       ═════════════════════════════════════════════════════════════════ -->
  <div id="modalAccountMiniStatement" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-3 bg-black/85 backdrop-blur-md select-none" dir="rtl" onclick="if(event.target === this) closeAccountMiniStatement()">
    <div class="bg-slate-900 border-2 border-cyan-500/50 w-full max-w-md rounded-t-3xl sm:rounded-3xl p-4 shadow-2xl space-y-3 max-h-[88vh] flex flex-col">
      <div class="flex items-center justify-between border-b border-slate-800 pb-2.5">
        <div class="flex items-center gap-2 min-w-0">
          <div class="w-9 h-9 rounded-xl bg-cyan-500/15 border border-cyan-400/40 flex items-center justify-center text-cyan-300 shrink-0">
            <i class="fa-solid fa-file-invoice-dollar text-base"></i>
          </div>
          <div class="min-w-0">
            <h3 id="miniStmtAccountTitle" class="text-sm font-black text-white truncate">كشف حساب مصغر</h3>
            <p id="miniStmtAccountMeta" class="text-[10.5px] text-slate-400 font-bold">آخر 10 حركات مالية مسجلة في MariaDB</p>
          </div>
        </div>
        <button type="button" onclick="closeAccountMiniStatement()" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-red-900/50 text-slate-300 hover:text-white flex items-center justify-center text-sm font-black">✕</button>
      </div>

      <!-- ملخص الرصيد الحي للطرف -->
      <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3 flex items-center justify-between">
        <div>
          <div class="text-[10px] text-slate-400 font-bold">الرصيد الحالي المعتمد</div>
          <div id="miniStmtCurrentBalance" class="text-lg font-mono font-black text-amber-300">0.00 ج.س</div>
        </div>
        <div id="miniStmtNaturePill" class="px-2.5 py-1 rounded-xl text-xs font-black bg-slate-800 text-slate-200 border border-slate-700">
          متوازن
        </div>
      </div>

      <!-- قائمة آخر 10 حركات -->
      <div id="miniStmtMovementsList" class="flex-1 overflow-y-auto space-y-2 pr-0.5 max-h-[46vh]">
        <div class="text-center text-slate-400 py-6 text-xs">جارٍ تحميل الحركات...</div>
      </div>

      <!-- أزرار فتح كشف الحساب الشامل (25 نموذج عالمي) أو الإغلاق -->
      <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-800">
        <a id="miniStmtFullLink" href="account-statement.php" class="py-2.5 px-3 rounded-xl bg-gradient-to-r from-cyan-600 to-indigo-600 hover:from-cyan-500 hover:to-indigo-500 text-white font-black text-xs flex items-center justify-center gap-1.5 shadow-lg transition">
          <i class="fa-solid fa-up-right-from-square text-[10px]"></i>
          <span>كشف الحساب الشامل</span>
        </a>
        <button type="button" onclick="closeAccountMiniStatement()" class="py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs flex items-center justify-center">
          إغلاق
        </button>
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

      <!-- محتوى المصدر 1: الكاميرا (تصوير الإشعارات بالكاميرا مباشرة) -->
      <div id="contentSourceCamera" class="space-y-2 flex-1 overflow-y-auto no-scrollbar">
        <input type="file" id="cameraCaptureInput" accept="image/*" capture="environment" class="hidden" onchange="handleCameraFileSelected(event)">
        <div class="p-2.5 rounded-2xl bg-indigo-950/40 border border-indigo-500/40 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white text-base shadow-md">
              <i class="fa-solid fa-camera"></i>
            </div>
            <div>
              <span class="text-xs font-black text-white block">تصوير الإشعارات المتتابعة</span>
              <span class="text-[10px] text-indigo-300 font-bold" id="cameraCountLabel">جاهز لالتقاط صور الإشعارات</span>
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
              <span class="text-[10px] text-amber-300 font-bold" id="galleryCountLabel">جاهز لاختيار صور من المعرض</span>
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

      <!-- محتوى المصدر 3: الواتساب (تفريغ ذكي فوري + فتح تطبيق الواتساب) -->
      <div id="contentSourceWhatsApp" class="hidden space-y-2.5 flex-1 overflow-y-auto no-scrollbar">
        
        <!-- زر فتح تطبيق الواتساب مباشرة على الجوال أو الكمبيوتر -->
        <div class="grid grid-cols-2 gap-2">
          <button type="button" onclick="openWhatsAppApp()" class="py-2 px-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-black text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-emerald-900/50 border border-emerald-400 transition" title="فتح تطبيق واتساب على الجهاز مباشرة">
            <i class="fa-brands fa-whatsapp text-base"></i>
            <span>فتح واتساب الآن 📲</span>
          </button>
          
          <button type="button" onclick="document.getElementById('whatsappImageInput').click()" class="py-2 px-3 rounded-2xl bg-slate-800 hover:bg-slate-700 active:scale-95 text-emerald-300 font-black text-xs flex items-center justify-center gap-1.5 border border-emerald-500/40 shadow-sm transition" title="اختيار صورة إشعار من مجلد صور واتساب">
            <i class="fa-solid fa-file-image text-emerald-400 text-sm"></i>
            <span>صورة من واتساب 🖼️</span>
          </button>
          <input type="file" id="whatsappImageInput" accept="image/*" class="hidden" onchange="handleGalleryFilesSelected(event)">
        </div>

        <!-- صندوق لصق وتفريغ نصوص إشعارات الواتساب بالذكاء الاصطناعي (بنكك / فيصل / فوري) -->
        <div class="p-2.5 bg-slate-950 rounded-2xl border border-emerald-500/50 space-y-1.5">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-black text-emerald-300 flex items-center gap-1">
              <i class="fa-solid fa-wand-magic-sparkles text-amber-300 text-xs"></i>
              <span>تفريغ رسالة الواتساب بالذكاء الاصطناعي:</span>
            </span>
            <button type="button" onclick="pasteFromClipboardAndExtract()" class="px-2 py-0.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[10px] font-black flex items-center gap-1 active:scale-95 transition shadow-sm">
              <i class="fa-solid fa-paste text-[10px]"></i>
              <span>لصق وتفريغ ⚡</span>
            </button>
          </div>
          <textarea id="whatsappPasteArea" oninput="liveExtractFromWhatsAppText(this.value)" placeholder="الصق نص إشعار بنكك أو بنك فيصل أو رسالة الواتساب هنا ليتم التفريغ الفوري في السند..." class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2 text-white text-xs font-mono focus:outline-none focus:border-emerald-400 min-h-[52px] resize-none placeholder:text-slate-500"></textarea>
        </div>

        <!-- أزرار تصفية واختيار عدد الإشعارات الجاهزة -->
        <div class="space-y-1">
          <div class="flex items-center justify-between text-[10.5px] font-bold text-slate-300">
            <span>أو اختر من الإشعارات الواردة (انقر للتفريغ الفوري):</span>
            <span id="whatsAppCountBadge" class="text-emerald-400 font-mono font-bold">0 إشعار</span>
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
            <span class="font-black truncate max-w-[130px]" id="recipientToNameDisplay">---</span>
          </button>
          <button type="button" onclick="selectWhatsAppRecipient('from')" id="btnRecipientFrom" class="py-2 px-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 flex flex-col items-center justify-center gap-0.5 hover:bg-slate-700 transition">
            <span class="text-[10px] opacity-80">المرسل (من حساب)</span>
            <span class="font-black truncate max-w-[130px]" id="recipientFromNameDisplay">---</span>
          </button>
        </div>

        <!-- حقل رقم الواتساب -->
        <div>
          <label class="block text-[10.5px] font-bold text-slate-400 mb-1">رقم الواتساب (مع رمز الدولة):</label>
          <div class="relative">
            <i class="fa-solid fa-phone absolute left-3 top-3 text-slate-400 text-xs"></i>
            <input type="tel" id="whatsAppRecipientPhone" value="" placeholder="+249XXXXXXXXX" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white font-mono font-bold text-xs focus:outline-none focus:border-emerald-400" style="direction: ltr; text-align: left;">
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

  <!-- نافذة خيارات التقاط إشعار البنك (كاميرا حية / استوديو سكرين شوت) -->
  <div id="modalReceiptActionSheet" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-3 bg-black/85 backdrop-blur-md select-none" dir="rtl" onclick="closeReceiptActionSheet()">
    <div class="bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 border border-emerald-500/50 w-full max-w-sm rounded-t-3xl sm:rounded-3xl p-4 shadow-2xl space-y-3" onclick="event.stopPropagation()">
      <div class="flex items-center justify-between border-b border-slate-800 pb-2">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-xl bg-emerald-600/30 text-emerald-400 border border-emerald-500/40 flex items-center justify-center text-sm">
            <i class="fa-solid fa-camera"></i>
          </div>
          <div>
            <div class="text-xs font-black text-white">إرفاق إشعار البنك بالذكاء الاصطناعي (OCR)</div>
            <div class="text-[10px] text-slate-400">بنكك، فوري، أوكاش مع استخراج تلقائي للرقم والمبلغ</div>
          </div>
        </div>
        <button type="button" onclick="closeReceiptActionSheet()" class="w-7 h-7 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 flex items-center justify-center text-xs">✕</button>
      </div>

      <div class="space-y-2 pt-1">
        <button type="button" onclick="document.getElementById('directReceiptCameraInput').click(); closeReceiptActionSheet();" class="w-full py-3 px-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-xs flex items-center justify-between shadow-md active:scale-95 transition">
          <div class="flex items-center gap-2.5">
            <i class="fa-solid fa-camera text-base"></i>
            <span class="text-right">تصوير الإشعار بالكاميرا الحية 📸</span>
          </div>
          <i class="fa-solid fa-chevron-left text-[10px] opacity-70"></i>
        </button>

        <button type="button" onclick="document.getElementById('directReceiptGalleryInput').click(); closeReceiptActionSheet();" class="w-full py-3 px-3 rounded-2xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white font-black text-xs flex items-center justify-between active:scale-95 transition">
          <div class="flex items-center gap-2.5">
            <i class="fa-solid fa-images text-base text-cyan-400"></i>
            <span class="text-right">اختيار سكرين شوت من المعرض 🖼️</span>
          </div>
          <i class="fa-solid fa-chevron-left text-[10px] opacity-70"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- نافذة تكبير وعرض مستند إشعار البنك الثبوتي المرفق بالسند -->
  <div id="modalReceiptViewer" class="hidden fixed inset-0 z-[1000001] bg-black/90 backdrop-blur-md flex items-center justify-center p-3 select-none" dir="rtl" onclick="this.classList.add('hidden')">
    <div class="max-w-md w-full max-h-[90vh] bg-slate-900 border border-slate-700 rounded-3xl p-3 relative flex flex-col items-center" onclick="event.stopPropagation()">
      <button type="button" onclick="document.getElementById('modalReceiptViewer').classList.add('hidden')" class="absolute top-3 left-3 w-8 h-8 rounded-full bg-slate-800 text-white font-bold flex items-center justify-center text-xs">✕</button>
      <div class="text-xs font-black text-emerald-400 mb-2 flex items-center gap-1.5">
        <i class="fa-solid fa-shield-halved"></i>
        <span>مستند إشعار البنك الثبوتي المرفق بالسند</span>
      </div>
      <div class="flex-1 overflow-auto rounded-2xl bg-black w-full flex items-center justify-center p-1">
        <img id="receiptViewerImg" src="" alt="إشعار البنك" class="max-h-[75vh] w-auto object-contain rounded-xl">
      </div>
    </div>
  </div>

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
  <!-- ═════════════════════════════════════════════════════════════════
       نافذة منبثقة: خصائص وتنسيق الأرقام
       (تتحرك لأعلى وأسفل بسلاسة لإظهار كافة الأزرار مع تثبيت الهيدر وزر الحفظ)
       ═════════════════════════════════════════════════════════════════ -->
  <div id="modalNumberProperties" class="hidden fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 bg-black/85 backdrop-blur-md select-none overflow-y-auto" dir="rtl" onclick="if(event.target === this) closeNumberPropertiesModal()">
    <div class="bg-slate-900 border-2 border-slate-700/80 shadow-[0_0_35px_rgba(14,165,233,0.3)] w-full max-w-md rounded-3xl flex flex-col max-h-[92vh] max-h-[92dvh] overflow-hidden my-auto animate-in fade-in zoom-in-95 duration-200">
      
      <!-- الهيدر الثابت في الأعلى مع زر الإغلاق والتبديل السريع -->
      <div class="flex items-center justify-between border-b border-slate-800 px-4 py-3 shrink-0 bg-slate-900/95 sticky top-0 z-10 backdrop-blur-md">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-xl bg-cyan-600/20 border border-cyan-500/40 flex items-center justify-center text-cyan-300 text-sm">
            <i class="fa-solid fa-sliders"></i>
          </div>
          <div>
            <h3 class="text-xs sm:text-sm font-black text-white">خصائص وتنسيق الأرقام</h3>
            <span class="text-[9.5px] text-cyan-400 font-medium">مرر لأعلى ولأسفل لتعديل كافة الأزرار والخيارات ↕</span>
          </div>
        </div>
        <button type="button" onclick="closeNumberPropertiesModal()" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 active:scale-90 text-slate-300 hover:text-white flex items-center justify-center text-sm transition" title="إغلاق">
          ✕
        </button>
      </div>

      <!-- جسم النافذة القابل للتحريك والتمرير لأعلى وأسفل بسلاسة فائقة لإظهار كافة الأزرار -->
      <div class="flex-1 overflow-y-auto px-4 py-3 space-y-3.5 modal-scrollable-body overscroll-contain" style="-webkit-overflow-scrolling: touch;">

        <!-- تبديل واختيار المبلغ المراد ضبطه: المحلي (ج.س) أو الدولار ($) -->
        <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-950 rounded-2xl border border-slate-800">
          <button type="button" onclick="switchActiveAmountTarget('sdg')" id="targetBtnSdg" class="py-2 px-2 rounded-xl text-xs font-black flex items-center justify-center gap-1.5 transition bg-sky-600 text-white shadow-sm border border-sky-400">
            <i class="fa-solid fa-money-bill-wave text-sky-200"></i>
            <span>المبلغ المحلي (ج.س)</span>
          </button>
          <button type="button" onclick="switchActiveAmountTarget('usd')" id="targetBtnUsd" class="py-2 px-2 rounded-xl text-xs font-black flex items-center justify-center gap-1.5 transition bg-slate-800 text-slate-300 border border-slate-700 hover:text-white">
            <i class="fa-solid fa-dollar-sign text-emerald-400"></i>
            <span>المبلغ بالدولار ($)</span>
          </button>
        </div>

        <!-- معاينة فورية لشكل الرقم المنسق -->
        <div class="bg-slate-950 p-2.5 rounded-2xl border border-slate-800 text-center space-y-1">
          <div class="flex items-center justify-between text-[10px] text-slate-400 font-bold px-1">
            <span>معاينة مباشرة للرقم المنسق</span>
            <span id="targetPreviewTitle" class="text-sky-400 font-black">المبلغ المحلي (ج.س)</span>
          </div>
          <div id="numPropLivePreview" class="py-1 transition-all" style="direction: ltr; font-family: 'JetBrains Mono', monospace; font-size: 32px; font-weight: 900; color: #ffffff;">
            2,650,000 ج.س
          </div>
        </div>

        <!-- 1. درجات التكبير: (18, 22, 26, 30, 32, 34, 36) -->
        <div class="space-y-1">
          <div class="flex justify-between items-center text-[11px] font-bold">
            <span class="text-slate-300">تكبير الدرجات (حجم الخط):</span>
            <span id="currentSizeLabel" class="text-cyan-400 font-mono text-xs">30px</span>
          </div>
          <div class="grid grid-cols-7 gap-1 text-xs font-bold font-mono">
            <button type="button" onclick="setNumSize('18px')" id="btnSize18" class="size-btn py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700 active:scale-95 transition">18</button>
            <button type="button" onclick="setNumSize('22px')" id="btnSize22" class="size-btn py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700 active:scale-95 transition">22</button>
            <button type="button" onclick="setNumSize('26px')" id="btnSize26" class="size-btn py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700 active:scale-95 transition">26</button>
            <button type="button" onclick="setNumSize('30px')" id="btnSize30" class="size-btn py-1.5 rounded-lg bg-cyan-600 text-white border-cyan-400 text-center hover:bg-cyan-500 active:scale-95 transition">30</button>
            <button type="button" onclick="setNumSize('32px')" id="btnSize32" class="size-btn py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700 active:scale-95 transition">32</button>
            <button type="button" onclick="setNumSize('34px')" id="btnSize34" class="size-btn py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700 active:scale-95 transition">34</button>
            <button type="button" onclick="setNumSize('36px')" id="btnSize36" class="size-btn py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700 active:scale-95 transition">36</button>
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

        <!-- 5. الخانات العشرية والتقريب (0، 1، 2، 3، 4، 5، 6 خانات) -->
        <div class="space-y-1">
          <div class="flex justify-between items-center text-[11px] font-bold">
            <span class="text-slate-300">الخانات العشرية والتقريب:</span>
            <span id="currentDecimalsLabel" class="text-cyan-400 font-mono text-xs">2 خانة</span>
          </div>
          <div class="grid grid-cols-7 gap-1 text-xs font-bold font-mono">
            <button type="button" onclick="setNumDecimals(0)" id="btnDec0" class="dec-btn py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700" title="بدون كسور (أرقام صحيحة مقربة)">0</button>
            <button type="button" onclick="setNumDecimals(1)" id="btnDec1" class="dec-btn py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700" title="خانة عشرية واحدة">1</button>
            <button type="button" onclick="setNumDecimals(2)" id="btnDec2" class="dec-btn py-1.5 rounded-lg bg-cyan-600 text-white border-cyan-400 text-center" title="خانتان عشريتان">2</button>
            <button type="button" onclick="setNumDecimals(3)" id="btnDec3" class="dec-btn py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700" title="3 خانات عشرية">3</button>
            <button type="button" onclick="setNumDecimals(4)" id="btnDec4" class="dec-btn py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700" title="4 خانات عشرية">4</button>
            <button type="button" onclick="setNumDecimals(5)" id="btnDec5" class="dec-btn py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700" title="تقريب لـ 5 خانات عشرية">5</button>
            <button type="button" onclick="setNumDecimals(6)" id="btnDec6" class="dec-btn py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-center hover:bg-slate-700" title="تقريب لـ 6 خانات عشرية">6</button>
          </div>
          <div class="text-[9px] text-slate-400">تفعيل التقريب الدقيق للعملات والكسور حتى 5 و6 خانات عشريّة</div>
        </div>

        <!-- 6. الفواصل (الألفية والعشرية) -->
        <div class="grid grid-cols-2 gap-2 text-xs">
          <div>
            <label class="block text-[10.5px] font-bold text-slate-300 mb-1">الفاصلة الألفية:</label>
            <select id="numThousandsSelect" onchange="updateNumPreview(); applyLivePropertyChange();" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-2 py-1.5 text-white font-mono text-xs focus:outline-none focus:border-cyan-500">
              <option value="," selected>فاصلة ( , ) 1,000</option>
              <option value=".">نقطة ( . ) 1.000</option>
              <option value=" ">مسافة ( 1 000 )</option>
              <option value="">بدون فاصلة (1000)</option>
            </select>
          </div>

          <div>
            <label class="block text-[10.5px] font-bold text-slate-300 mb-1">الفاصلة العشرية:</label>
            <select id="numDecimalSelect" onchange="updateNumPreview(); applyLivePropertyChange();" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-2 py-1.5 text-white font-mono text-xs focus:outline-none focus:border-cyan-500">
              <option value="." selected>نقطة ( . ) 0.50</option>
              <option value=",">فاصلة ( , ) 0,50</option>
            </select>
          </div>
        </div>

      </div>

      <!-- شريط الأزرار السفلي الثابت في الأسفل دائماً لسهولة الحفظ الفوري من أي مكان -->
      <div class="px-4 py-3 bg-slate-950/95 border-t border-slate-800 shrink-0 flex items-center gap-2">
        <button type="button" onclick="closeNumberPropertiesModal()" class="py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-300 font-bold text-xs flex items-center justify-center gap-1 transition">
          <i class="fa-solid fa-xmark text-xs"></i>
          <span>إلغاء</span>
        </button>
        <button type="button" onclick="saveNumberProperties()" class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-cyan-600 via-sky-600 to-emerald-600 hover:from-cyan-500 hover:to-emerald-500 active:scale-95 text-white font-black text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-cyan-900/40 border border-cyan-400/40 transition">
          <i class="fa-solid fa-floppy-disk text-xs"></i>
          <span>حفظ وتطبيق الخصائص للبرنامج</span>
        </button>
      </div>

    </div>

  <!-- ═════════════════════════════════════════════════════════════════
       نافذة التنبيه اللطيف والمهذب (Polite Gentle Warning Modal)
       إشعارات تدقيق مالي لطيفة ومهذبة ومريحة بصرياً بدون أي إزعاج
       ═════════════════════════════════════════════════════════════════ -->
  <!-- ═════════════════════════════════════════════════════════════════
       نافذة التنبيه اللطيف والمهذب والتحذيري (Polite Gentle & Warning Modal)
       تدعم التنبيه اللطيف العادي والتنبيه التحذيري باللون الأحمر لرقم التحويلة المكرر وأقل من 6 أرقام
       ═════════════════════════════════════════════════════════════════ -->
  <div id="politeWarningModal" onclick="if(event.target === this) closePoliteWarningModal()" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4">
    <div id="politeModalCard" class="relative bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 border-2 border-amber-500/70 rounded-3xl p-5 max-w-sm w-full text-center shadow-[0_0_50px_rgba(245,158,11,0.25)] space-y-4 animate-in fade-in zoom-in-95 duration-200">
      <button type="button" onclick="closePoliteWarningModal()" title="إغلاق التنبيه" class="absolute top-3 left-3 w-8 h-8 rounded-full bg-slate-800/80 hover:bg-red-500/30 text-slate-300 hover:text-white flex items-center justify-center text-sm font-bold border border-slate-700 transition">✕</button>
      
      <!-- أيقونة التنبيه اللطيفة أو التحذيرية -->
      <div id="politeModalIconWrapper" class="relative mx-auto w-16 h-16 rounded-2xl bg-amber-500/15 border-2 border-amber-400/40 flex items-center justify-center shadow-inner">
        <i id="politeModalIcon" class="fa-solid fa-bell text-2xl text-amber-400 animate-bounce"></i>
        <span class="absolute -top-1 -right-1 flex h-3 w-3">
          <span id="politeModalPing" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
          <span id="politeModalDot" class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
        </span>
      </div>

      <!-- العنوان والرسالة اللطيفة المهذبة أو التحذيرية -->
      <div class="space-y-2">
        <h3 id="politeModalTitle" class="text-base font-black text-amber-300 flex items-center justify-center gap-1.5">
          <span>تنبيه تدقيق مالي لطيف</span>
        </h3>
        <div id="politeModalMessage" class="text-xs sm:text-sm font-semibold text-slate-200 leading-relaxed bg-slate-950/80 border border-slate-800 rounded-2xl p-3.5 text-right whitespace-pre-line shadow-inner max-h-52 overflow-y-auto">
          عذراً ولطفاً: يرجى مراجعة بيانات الحركة المالية
        </div>
      </div>

      <!-- زر المتابعة والإغلاق -->
      <div class="pt-1 flex gap-2">
        <button id="politeModalBtn" type="button" onclick="closePoliteWarningModal()" class="flex-1 py-3 px-4 rounded-xl bg-gradient-to-r from-amber-600 via-amber-500 to-amber-600 hover:from-amber-500 hover:to-amber-400 active:scale-95 text-slate-950 font-black text-xs sm:text-sm flex items-center justify-center gap-2 shadow-lg shadow-amber-950/50 transition">
          <i class="fa-solid fa-check text-sm"></i>
          <span id="politeModalBtnText">حسناً، فهمت وسأقوم بالمراجعة</span>
        </button>
      </div>

    </div>
  </div>

  <!-- ═════════════════════════════════════════════════════════════════
       نافذة تأكيد الحفظ والتعديل الجذابة الأنيقة (Save & Edit Success Modal)
       تصميم جمالي ظريف وفخم يعرض "تم الحفظ" أو "تم التعديل" بوضوح ورونق
       ═════════════════════════════════════════════════════════════════ -->
  <div id="saveFeedbackModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4">
    <div id="saveFeedbackCard" class="bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 border-2 rounded-3xl p-5 max-w-sm w-full text-center space-y-4 animate-in fade-in zoom-in-95 duration-200">
      
      <!-- الأيقونة المتحركة اللامعة -->
      <div id="saveFeedbackIconBox" class="relative mx-auto w-16 h-16 rounded-2xl flex items-center justify-center shadow-inner">
        <i id="saveFeedbackIcon" class="fa-solid fa-circle-check text-3xl"></i>
        <span class="absolute -top-1 -right-1 flex h-3 w-3">
          <span id="saveFeedbackPing" class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"></span>
          <span id="saveFeedbackDot" class="relative inline-flex rounded-full h-3 w-3"></span>
        </span>
      </div>

      <!-- العنوان والرسالة الجذابة -->
      <div class="space-y-1.5">
        <h3 id="saveFeedbackTitle" class="text-xl font-black flex items-center justify-center gap-1.5">
          <span>تم الحفظ</span>
        </h3>
        <p id="saveFeedbackSub" class="text-xs font-bold text-slate-300"></p>
        
        <!-- ملخص السند اللطيف -->
        <div id="saveFeedbackDetails" class="mt-2 text-xs font-semibold text-slate-200 bg-slate-950/80 border border-slate-800 rounded-2xl p-3 text-right space-y-1.5 shadow-inner">
          <div class="flex justify-between items-center text-slate-400">
            <span>رقم السند:</span>
            <span id="saveFeedbackVNo" class="font-mono font-black text-amber-300"></span>
          </div>
          <div class="flex justify-between items-center text-slate-400">
            <span>المبلغ:</span>
            <span id="saveFeedbackAmount" class="font-mono font-bold text-emerald-400"></span>
          </div>
          <div class="flex justify-between items-center text-slate-400">
            <span>مِن حساب:</span>
            <span id="saveFeedbackFrom" class="text-white truncate max-w-[170px]"></span>
          </div>
          <div class="flex justify-between items-center text-slate-400">
            <span>إلى حساب:</span>
            <span id="saveFeedbackTo" class="text-white truncate max-w-[170px]"></span>
          </div>
        </div>
      </div>

      <!-- أزرار الإجراء السريع: عرض الإيصال والمتابعة -->
      <div class="grid grid-cols-2 gap-2 pt-1">
        <button type="button" onclick="closeSaveFeedbackModal(); showDigitalVoucherReceipt();" class="py-2.5 px-2 rounded-xl font-black text-xs flex items-center justify-center gap-1.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white shadow-lg shadow-indigo-950 transition active:scale-95">
          <i class="fa-solid fa-receipt text-xs"></i>
          <span>الإيصال والـ QR 📜</span>
        </button>
        <button id="saveFeedbackBtn" type="button" onclick="closeSaveFeedbackModal()" class="w-full py-2.5 px-2 rounded-xl font-black text-xs sm:text-sm flex items-center justify-center gap-1.5 shadow-lg transition active:scale-95">
          <i class="fa-solid fa-check text-sm"></i>
          <span>متابعة العمل ✓</span>
        </button>
      </div>

    </div>
  </div>

  <!-- Application Logic -->
  <script src="js/mobile-voucher-core.js?v=20261029_fast"></script>
  <!-- =========================================================================
       MOBILE CONSTITUTION 2 OF 10 INHERITANCE (Voucher Sub-Screen)
       Refined Soft Borders, Locked Input Contrast, Viewport Scale & Keyboard Guard
       ========================================================================= -->
  <style id="mgVoucherConstitution2Styles">
    html[data-theme="dark"] body,
    html:not([data-theme="light"]) body {
      --mg-c2-border-soft: rgba(255, 255, 255, 0.22);
      --mg-c2-input-bg: #000000;
      --mg-c2-input-text: #FFFFFF;
    }
    html[data-theme="light"] body,
    body.theme-light {
      --mg-c2-border-soft: rgba(0, 0, 0, 0.20);
      --mg-c2-input-bg: #FFFFFF;
      --mg-c2-input-text: #000000;
    }
    input:not([type="checkbox"]):not([type="radio"]),
    select,
    textarea {
      border: 1.25px solid var(--mg-c2-border-soft) !important;
      background-color: var(--mg-c2-input-bg) !important;
      color: var(--mg-c2-input-text) !important;
      -webkit-text-fill-color: var(--mg-c2-input-text) !important;
      font-weight: 800 !important;
      text-overflow: clip !important;
    }
    input:not([type="checkbox"]):not([type="radio"]):focus,
    select:focus,
    textarea:focus,
    input:-webkit-autofill {
      background-color: var(--mg-c2-input-bg) !important;
      color: var(--mg-c2-input-text) !important;
      -webkit-box-shadow: 0 0 0 1000px var(--mg-c2-input-bg) inset !important;
      -webkit-text-fill-color: var(--mg-c2-input-text) !important;
    }
  </style>
  <script id="mgVoucherConstitution2Script">
    (function() {
      try {
        var raw = localStorage.getItem('mg_mobile_viewport_profile_v2');
        if (raw) {
          var p = JSON.parse(raw);
          if (p && p.scale && p.scale !== 1) {
            document.documentElement.style.zoom = p.scale;
          }
        }
        var sun = localStorage.getItem('mg_mobile_sunlight_mode');
        if (sun === '1') {
          document.documentElement.setAttribute('data-sunlight-mode', 'true');
        }
      } catch (e) {}

      document.addEventListener('focusin', function(e) {
        var t = e.target;
        if (!t || !/^(INPUT|SELECT|TEXTAREA)$/.test(t.tagName)) return;
        setTimeout(function() {
          try {
            t.scrollIntoView({ behavior: 'smooth', block: 'center' });
          } catch (err) {
            try { t.scrollIntoView(false); } catch (e2) {}
          }
        }, 180);
      }, true);
    })();
  </script>
  <!-- =========================================================================
       MOBILE CONSTITUTION 3 OF 10 INHERITANCE (Voucher Sub-Screen)
       Disable Pull-to-Refresh, Incoming Call Shield, Cursor Silk-Flow & Auto-Select
       ========================================================================= -->
  <style id="mgVoucherConstitution3Styles">
    html, body {
      overscroll-behavior-y: none !important;
      overscroll-behavior-x: none !important;
    }
  </style>
  <script id="mgVoucherConstitution3Script">
    (function() {
      var SHIELD_KEY = 'mg_voucher_call_shield_v3';

      function saveVoucherCallShield() {
        try {
          var act = document.activeElement;
          var vals = {};
          document.querySelectorAll('input[id], select[id], textarea[id]').forEach(function(el) {
            if (el.type !== 'file' && el.value !== '') vals[el.id] = el.value;
          });
          sessionStorage.setItem(SHIELD_KEY, JSON.stringify({
            ts: Date.now(),
            activeId: act && act.id ? act.id : null,
            selStart: act && typeof act.selectionStart === 'number' ? act.selectionStart : null,
            selEnd: act && typeof act.selectionEnd === 'number' ? act.selectionEnd : null,
            vals: vals
          }));
        } catch (e) {}
      }

      function restoreVoucherCallShield() {
        try {
          var raw = sessionStorage.getItem(SHIELD_KEY);
          if (!raw) return;
          var data = JSON.parse(raw);
          if (!data || (Date.now() - data.ts > 3600000)) return;
          if (data.vals) {
            Object.keys(data.vals).forEach(function(id) {
              var el = document.getElementById(id);
              if (el && el.value === '' && data.vals[id] !== '') el.value = data.vals[id];
            });
          }
          if (data.activeId) {
            var a = document.getElementById(data.activeId);
            if (a) {
              a.focus();
              if (typeof data.selStart === 'number' && a.setSelectionRange) {
                try { a.setSelectionRange(data.selStart, data.selEnd || data.selStart); } catch (e2) {}
              }
            }
          }
        } catch (e) {}
      }

      document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden') saveVoucherCallShield();
        else if (document.visibilityState === 'visible') restoreVoucherCallShield();
      });
      window.addEventListener('pagehide', saveVoucherCallShield);

      // التظليل التلقائي للرقم القديم + انسيابية المؤشر عبر Enter
      document.addEventListener('focusin', function(e) {
        var t = e.target;
        if (t && t.tagName === 'INPUT' && (t.type === 'number' || t.type === 'tel' || t.type === 'text')) {
          var v = String(t.value || '').trim();
          if (v !== '' && /^[\d,.\s]+$/.test(v)) {
            setTimeout(function() { try { t.select(); } catch (err) {} }, 25);
          }
        }
      }, true);

      document.addEventListener('keydown', function(e) {
        if (e.key !== 'Enter') return;
        var t = e.target;
        if (!t || t.tagName !== 'INPUT') return;
        var list = Array.prototype.slice.call(document.querySelectorAll('input:not([type="hidden"]):not([disabled]):not([readonly]), select:not([disabled])'))
          .filter(function(el) { return el.offsetParent !== null; });
        var idx = list.indexOf(t);
        if (idx > -1 && idx + 1 < list.length) {
          e.preventDefault();
          list[idx + 1].focus();
        }
      }, true);
    })();
  </script>
  <!-- =========================================================================
       MOBILE CONSTITUTION 4 OF 10 INHERITANCE (Voucher Sub-Screen)
       Unified Button DNA, Single-Save Lock, Under-Glow, Haptics & Smart Paste
       ========================================================================= -->
  <style id="mgVoucherConstitution4Styles">
    button:not(:disabled) {
      transition: transform 0.14s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.14s ease !important;
      will-change: transform;
    }
    button:not(:disabled):hover,
    button:not(:disabled):active {
      transform: scale(1.03) !important;
      box-shadow: 0 8px 20px -4px rgba(56, 189, 248, 0.5), 0 0 12px rgba(56, 189, 248, 0.3) !important;
    }
    button[data-mg-save-locked="1"] {
      opacity: 0.65 !important;
      pointer-events: none !important;
    }
    @keyframes mgInvalidFieldShake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-8px); }
      50% { transform: translateX(8px); }
      75% { transform: translateX(-6px); }
    }
    .mg-field-error-shake {
      animation: mgInvalidFieldShake 0.4s ease-in-out !important;
      border-color: #f43f5e !important;
    }
  </style>
  <script id="mgVoucherConstitution4Script">
    (function() {
      document.addEventListener('click', function(e) {
        var btn = e.target && e.target.closest ? e.target.closest('button') : null;
        if (!btn) return;
        if (navigator.vibrate) { try { navigator.vibrate(12); } catch (err) {} }
        var txt = (btn.textContent || '').trim();
        if (/(?:حفظ|اعتماد|ترحيل|إصدار)/.test(txt)) {
          if (btn.dataset.mgSaveLocked === '1') {
            e.stopImmediatePropagation();
            e.preventDefault();
            return;
          }
          btn.dataset.mgSaveLocked = '1';
          setTimeout(function() { btn.dataset.mgSaveLocked = '0'; }, 1800);
        }
      }, true);
    })();
  </script>
  <!-- =========================================================================
       MOBILE CONSTITUTION 5 OF 10 INHERITANCE (Voucher Sub-Screen)
       Exclusive Top "المرشد" Button, Male Voice, Nano-HUD & Golden Rules
       ========================================================================= -->
  <div id="mgVoucherNanoHud" class="hidden fixed bottom-2 left-2 right-2 z-[10005] bg-slate-950/95 border border-cyan-400/60 rounded-2xl p-2 shadow-2xl text-right" dir="rtl">
    <div class="flex items-center justify-between gap-1 mb-1">
      <span class="text-[10px] font-black text-amber-300">🎙️ المرشد الذكي — شاشة السندات المالية</span>
      <div class="flex items-center gap-1">
        <button type="button" onclick="window.MgVoucherGuide.prev()" class="px-1.5 py-0.5 rounded bg-slate-800 text-white text-[10px] font-black">⏮️</button>
        <button type="button" onclick="window.MgVoucherGuide.next()" class="px-1.5 py-0.5 rounded bg-cyan-500 text-slate-950 text-[10px] font-black">⏭️</button>
        <button type="button" onclick="window.MgVoucherGuide.stop()" class="px-1.5 py-0.5 rounded bg-rose-500/30 text-rose-200 text-[10px] font-black">✕</button>
      </div>
    </div>
    <div id="mgVoucherNanoDesc" class="text-[10px] font-bold text-slate-200 leading-snug"></div>
    <div id="mgVoucherNanoRule" class="text-[9.5px] font-black text-emerald-300 mt-0.5"></div>
  </div>
  <script id="mgVoucherConstitution5Script">
    (function() {
      var hasPlayedOpeningInvocation = false;
      var steps = [];
      var idx = 0;
      var active = false;

      function speakMale(txt) {
        if (!window.speechSynthesis) return;
        try { window.speechSynthesis.cancel(); } catch (e) {}
        var clean = String(txt || '').replace(/شرح\s+من\s+الألف\s+إلى\s+الياء/g, '');
        var u = new SpeechSynthesisUtterance(clean);
        u.lang = 'ar-SA';
        u.pitch = 0.84;
        u.rate = 0.98;
        window.speechSynthesis.speak(u);
      }

      window.MgVoucherGuide = {
        start: function() {
          active = true;
          steps = [{
            title: 'الشرح العام لشاشة الحركة المالية (القبض والصرف)',
            desc: 'تأتيها المعلومات من حسابات العملاء والموردين وإشعارات بنكك، وتُسمّع في الخزينة والبنك وكشف الحساب والدفتر الأستاذ.',
            rule: '🥇 القاعدة الذهبية: طابق المبلغ ورقم الإشعار وتأكد من نوع السند (قبض أو صرف) قبل الحفظ.'
          }];
          document.querySelectorAll('input:not([type="hidden"]), select, button').forEach(function(el) {
            if (el.offsetParent === null) return;
            var lbl = (el.getAttribute('placeholder') || el.innerText || el.id || '').trim().slice(0, 40);
            if (lbl) {
              steps.push({
                el: el,
                title: lbl,
                desc: 'عنصر (' + lbl + ') لضبط بيانات السند المالي بدقة وسرعة.',
                rule: '🥇 القاعدة الذهبية: الشاشة محمية بقفل الحفظ مرة واحدة وحارس المكالمات.'
              });
            }
          });
          idx = 0;
          document.getElementById('mgVoucherNanoHud').classList.remove('hidden');
          var prefix = '';
          if (!hasPlayedOpeningInvocation) {
            hasPlayedOpeningInvocation = true;
            prefix = 'اللهم صل وسلم على نبينا محمد. ';
          }
          this.render(prefix);
        },
        render: function(prefix) {
          var s = steps[idx] || steps[0];
          document.getElementById('mgVoucherNanoDesc').textContent = s.title + ': ' + s.desc;
          document.getElementById('mgVoucherNanoRule').textContent = s.rule;
          speakMale((prefix || '') + s.title + '. ' + s.desc + ' ' + s.rule);
        },
        next: function() { idx = (idx + 1) % steps.length; this.render(''); },
        prev: function() { idx = (idx - 1 + steps.length) % steps.length; this.render(''); },
        stop: function() {
          active = false;
          if (window.speechSynthesis) { try { window.speechSynthesis.cancel(); } catch (e) {} }
          document.getElementById('mgVoucherNanoHud').classList.add('hidden');
        }
      };
    })();
  </script>
  <!-- =========================================================================
       MOBILE CONSTITUTION 6 OF 10 INHERITANCE (Voucher Sub-Screen)
       Sticky Column, Tabular Numerals, Arabic Numeral/Decimal Normalization & Flash Sync
       ========================================================================= -->
  <style id="mgVoucherConstitution6Styles">
    table th:first-child,
    table td:first-child {
      position: sticky !important;
      right: 0 !important;
      z-index: 10 !important;
      background-color: #091024 !important;
    }
    html[data-theme="light"] table th:first-child,
    html[data-theme="light"] table td:first-child,
    body.theme-light table th:first-child,
    body.theme-light table td:first-child {
      background-color: #F8FAFC !important;
    }
    table tbody tr:nth-child(even) {
      background-color: rgba(30, 41, 59, 0.4) !important;
    }
    input[type="number"],
    input[type="tel"],
    .font-mono,
    table td {
      font-variant-numeric: tabular-nums lining-nums !important;
    }
  </style>
  <script id="mgVoucherConstitution6Script">
    (function() {
      var arDigits = '٠١٢٣٤٥٦٧٨٩';
      var faDigits = '۰۱۲۳۴۵۶۷۸۹';
      function normDigits(str, isNum) {
        var out = String(str || '');
        for (var i = 0; i < 10; i++) {
          out = out.replace(new RegExp(arDigits[i], 'g'), String(i))
                   .replace(new RegExp(faDigits[i], 'g'), String(i));
        }
        if (isNum) {
          out = out.replace(/(\d)\s*[،,و]\s*(\d)/g, '$1.$2').replace(/[،,]/g, '.');
        }
        return out;
      }

      document.addEventListener('input', function(e) {
        var t = e.target;
        if (!t || !/^(INPUT|TEXTAREA)$/.test(t.tagName)) return;
        if (t.type !== 'number' && typeof t.value === 'string') {
          var isN = (t.type === 'tel' || /Amount|Price|Rate|Total/i.test(t.id || ''));
          var conv = normDigits(t.value, isN);
          if (conv !== t.value) t.value = conv;
        }
      }, true);

      document.addEventListener('change', function(e) {
        var t = e.target;
        if (t && (t.type === 'text' || t.tagName === 'TEXTAREA')) {
          t.value = normDigits(t.value, false).replace(/\s+/g, ' ').trim();
        }
      }, true);

      window.addEventListener('online', function() {
        if (window.MagaiesOffline && typeof window.MagaiesOffline.syncNow === 'function') {
          try { window.MagaiesOffline.syncNow(); } catch (err) {}
        }
      });
    })();
  </script>
  <!-- =========================================================================
       MOBILE CONSTITUTION 7 OF 10 INHERITANCE (Voucher Sub-Screen)
       Inline Math Evaluator, Instant Arabic Tafqeet, Fat-Finger Guard & 40KB Image Compressor
       ========================================================================= -->
  <script id="mgVoucherConstitution7Script">
    (function() {
      function safeEvalMath(str) {
        if (!str) return null;
        var clean = String(str).replace(/[×xX]/g, '*').replace(/[÷]/g, '/').replace(/,/g, '').replace(/\s+/g, '');
        if (!/^[0-9+\-*/.()]+$/.test(clean) || !/[+\-*/]/.test(clean)) return null;
        try {
          var res = Function('"use strict"; return (' + clean + ')')();
          return (typeof res === 'number' && isFinite(res)) ? Math.round(res * 100) / 100 : null;
        } catch (e) { return null; }
      }

      document.addEventListener('keydown', function(e) {
        if (e.key !== 'Enter') return;
        var t = e.target;
        if (!t || t.tagName !== 'INPUT') return;
        var val = safeEvalMath(t.value);
        if (val !== null) {
          t.value = String(val);
          t.dispatchEvent(new Event('input', { bubbles: true }));
        }
      }, true);

      document.addEventListener('focusout', function(e) {
        var t = e.target;
        if (!t || t.tagName !== 'INPUT') return;
        var val = safeEvalMath(t.value);
        if (val !== null) {
          t.value = String(val);
          t.dispatchEvent(new Event('input', { bubbles: true }));
        }
      }, true);
    })();
  </script>
  <!-- =========================================================================
       MOBILE CONSTITUTION 8 OF 10 INHERITANCE (Voucher Sub-Screen)
       100% Double-Entry Accounting Brain Behind Compact UI & Lifecycle Link
       ========================================================================= -->
  <script id="mgVoucherConstitution8Script">
    (function() {
      window.MgVoucherErpEngine = {
        verifyDoubleEntryBalance: function(debit, credit) {
          var d = Math.round((parseFloat(debit) || 0) * 100);
          var c = Math.round((parseFloat(credit) || 0) * 100);
          return d === c;
        }
      };
    })();
  </script>
  <!-- =========================================================================
       MOBILE CONSTITUTION 9 OF 10 INHERITANCE (Voucher Sub-Screen)
       Cheque Lifecycle, Bankak/Fawry Anti-Fraud Lock & Financial Period Lock
       ========================================================================= -->
  <script id="mgVoucherConstitution9Script">
    (function() {
      document.addEventListener('change', function(e) {
        var t = e.target;
        if (!t) return;
        // فحص عدم وقوع التاريخ في فترة مالية مغلقة
        if (t.tagName === 'INPUT' && t.type === 'date') {
          try {
            var gov = JSON.parse(localStorage.getItem('mg_financial_period_lock_v9') || '{}');
            if (gov.closedBeforeDate && t.value <= gov.closedBeforeDate) {
              alert('⛔ الفترة المالية مغلقة حتى (' + gov.closedBeforeDate + '): يُمنع إدخال أو تعديل سند بتاريخ قديم!');
              t.value = new Date().toISOString().slice(0, 10);
            }
          } catch (err) {}
        }
        // فحص عدم تكرار رقم إشعار بنكك/فوري ضد الاختلاس
        if (t.tagName === 'INPUT' && /Ref|Trx|Notification|CheckNo/i.test(t.id || t.name || '')) {
          try {
            var refs = JSON.parse(localStorage.getItem('mg_anti_fraud_bank_refs_v9') || '{}');
            var v = (t.value || '').trim();
            if (v && refs[v]) {
              t.classList.add('mg-field-error-shake');
              alert('🚨 تنبيه أمني أحمر: رقم الإشعار/العملية (' + v + ') مستخدم مسبقاً! تم رفض الرقم لمنع التكرار والاختلاس.');
              t.value = '';
            }
          } catch (err2) {}
        }
      }, true);
    })();
  </script>
  <!-- =========================================================================
       MOBILE CONSTITUTION 10 OF 10 INHERITANCE (Voucher Sub-Screen)
       Biometric Idle Lock, Anti-Espionage Blur Curtain, Time-Lock & Print Guard
       ========================================================================= -->
  <div id="mgVoucherBlurCurtain" class="hidden fixed inset-0 z-[10008] bg-slate-950/95 backdrop-blur-2xl flex flex-col items-center justify-center p-4 text-center" dir="rtl">
    <span class="text-3xl mb-2">🫆</span>
    <span class="text-xs font-black text-white">🛡️ درع منع التجسس وستارة التعتيم التلقائي نشطة</span>
  </div>
  <script id="mgVoucherConstitution10Script">
    (function() {
      var TIME_LOCK_KEY = 'mg_monotonic_high_watermark_v10';
      var now = Date.now();
      var savedHigh = parseInt(localStorage.getItem(TIME_LOCK_KEY) || '0', 10);
      if (savedHigh > 0 && now < savedHigh - 120000) {
        alert('🚨 القفل الزمني المشفر: تم اكتشاف إرجاع ساعة الجوال للوراء في الأوفلاين! تم قفل الحفظ وإشعار المالك.');
      } else if (now > savedHigh) {
        localStorage.setItem(TIME_LOCK_KEY, String(now));
      }

      document.addEventListener('visibilitychange', function() {
        var c = document.getElementById('mgVoucherBlurCurtain');
        if (!c) return;
        if (document.visibilityState === 'hidden') c.classList.remove('hidden');
        else c.classList.add('hidden');
      });

      document.addEventListener('change', function(e) {
        var t = e.target;
        if (!t || !/^(INPUT|TEXTAREA)$/.test(t.tagName)) return;
        t.value = String(t.value || '').replace(/<\s*script[^>]*>[\s\S]*?<\s*\/\s*script\s*>/gi, '');
      }, true);
    })();
  </script>
<script src="js/magaies-smart-guide.js" defer></script>
</body>
</html>
