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
if (!function_exists('extractAllTransfersFromRecord')) {
    function extractAllTransfersFromRecord($d) {
        $refs = [];
        if (!empty($d['mobileTransferNo']) && is_string($d['mobileTransferNo'])) $refs[] = trim($d['mobileTransferNo']);
        if (!empty($d['transferRef']) && is_string($d['transferRef'])) $refs[] = trim($d['transferRef']);
        if (!empty($d['refNo']) && is_string($d['refNo'])) $refs[] = trim($d['refNo']);
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
        return array_values(array_unique(array_filter($refs)));
    }
}

if (!function_exists('fetchAllDbTransfers')) {
    function fetchAllDbTransfers($pdo, $currentTenantId = null) {
        $results = [];
        $seen = [];
        $effTenant = !empty($currentTenantId) ? (int)$currentTenantId : 12;
        $tables = ['single_payments', 'single_receipts'];
        foreach ($tables as $tbl) {
            try {
                $stmt = $pdo->prepare("SELECT id, tenant_id, data FROM `$tbl` WHERE tenant_id = ?");
                $stmt->execute([$effTenant]);
                while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $d = json_decode($r['data'], true);
                    if (!$d || !empty($d['deleted'])) continue;
                    $vNo = trim((string)($d['voucherNo'] ?? ($d['id'] ?? '')));
                    $refs = extractAllTransfersFromRecord($d);
                    foreach ($refs as $ref) {
                        $clean = preg_replace('/\D/', '', (string)$ref);
                        $key = $vNo . '_' . $ref;
                        if (isset($seen[$key])) continue;
                        $seen[$key] = true;
                        $results[] = [
                            'voucherNo' => $vNo,
                            'table' => $tbl,
                            'tenantId' => $r['tenant_id'],
                            'ref' => (string)$ref,
                            'cleanRef' => $clean ?: (string)$ref,
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
        $ref = trim((string)$ref);
        if ($ref === '') return null;
        $cleanRef = preg_replace('/\D/', '', $ref) ?: strtolower($ref);
        $compLen = strlen($cleanRef);
        $effTenant = !empty($currentTenantId) ? (int)$currentTenantId : 12;

        $softCandidate = null;
        $tables = ['single_payments', 'single_receipts'];
        foreach ($tables as $tbl) {
            try {
                $stmt = $pdo->prepare("SELECT id, tenant_id, data FROM `$tbl` WHERE tenant_id = ?");
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
                        $strCr = trim((string)$cr);
                        $cleanCr = preg_replace('/\D/', '', $strCr) ?: strtolower($strCr);
                        if ($cleanCr === '') continue;

                        $matched = false;
                        $matchType = '';
                        $severity = 'hard';

                        // 1. التطابق التام (حظر قاطع Hard Block)
                        if ($cleanCr === $cleanRef || $strCr === $ref) {
                            $matched = true;
                            $matchType = 'تطابق تام';
                            $severity = 'hard';
                        }
                        // 2. تطابق آخر 6 أرقام (حظر قاطع Hard Block)
                        elseif ($compLen >= 6 && strlen($cleanCr) >= 6 && substr($cleanCr, -6) === substr($cleanRef, -6)) {
                            $matched = true;
                            $matchType = 'تطابق آخر 6 أرقام';
                            $severity = 'hard';
                        }
                        // 3. تطابق اللواحق للأرقام ذات 4 أو 5 أرقام (حظر قاطع Hard Block)
                        elseif ($compLen >= 4 && strlen($cleanCr) >= $compLen && substr($cleanCr, -$compLen) === $cleanRef) {
                            $matched = true;
                            $matchType = 'تطابق لاحقة الرقم';
                            $severity = 'hard';
                        }
                        elseif (strlen($cleanCr) >= 4 && $compLen >= strlen($cleanCr) && substr($cleanRef, -strlen($cleanCr)) === $cleanCr) {
                            $matched = true;
                            $matchType = 'تطابق لاحقة الرقم';
                            $severity = 'hard';
                        }
                        // 4. تشابه رقمي بفرق خانة واحدة فقط (تنبيه ذكي قابل للتأكيد Soft Warning)
                        elseif (!$ignoreSoftSimilarity && $compLen >= 4 && abs($compLen - strlen($cleanCr)) <= 1 && levenshtein($cleanRef, $cleanCr) <= 1) {
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

        $amt = (float)($payload['amountLocal'] ?? 0);
        $foreignAmt = (float)($payload['amountForeign'] ?? 0);
        $rate = (float)($payload['rate'] ?? 2650);
        if ($amt <= 0 && $foreignAmt > 0 && $rate > 0) {
            $amt = round($foreignAmt * $rate, 2);
        }
        $transferRef = trim($payload['mobileTransferNo'] ?? ($payload['refNo'] ?? ''));
        $payMethod = $payload['paymentMethod'] ?? 'transfer';
        $currency = $payload['currency'] ?? 'SDG';
        $costCenter = trim($payload['costCenter'] ?? '');
        $linkedInvoiceNo = trim($payload['linkedInvoiceNo'] ?? ($payload['invoiceRef'] ?? ''));
        $confirmSimilarTransfer = !empty($payload['confirmSimilarTransfer']);

        if ($amt <= 0 && $foreignAmt <= 0) {
            echo json_encode(['success' => false, 'error' => 'يرجى إدخال مبلغ صحيح أكبر من الصفر']);
            exit;
        }

        $bankName = trim($payload['bankName'] ?? '');
        $fromAccName = trim($payload['fromAccountName'] ?? ($payload['from'] ?? ''));
        $toAccName = trim($payload['toAccountName'] ?? ($payload['to'] ?? ''));

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
                $stmtSeq = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ? FOR UPDATE");
                $stmtSeq->execute([$currentTenantId ?: 12]);
                while ($row = $stmtSeq->fetch(PDO::FETCH_ASSOC)) {
                    $d = json_decode($row['data'], true);
                    if (!$d || !empty($d['deleted'])) continue;
                    $id = trim((string)($d['voucherNo'] ?? ($d['id'] ?? '')));

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
                        } elseif (preg_match('/^E\d{4}(\d{4})$/', $sUpper, $m)) {
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
                $voucherNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
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

            $record = [
                'id' => $voucherNo,
                'voucherNo' => $voucherNo,
                'date' => $voucherDate,
                'type' => 'single_payment',
                'voucherNature' => $natureInfo['code'],
                'voucherNatureLabel' => $natureInfo['label'],
                'paymentMethod' => $payMethod,
                'fromAccount' => $payload['fromAccountId'] ?? '',
                'fromAccountName' => $fromAccName,
                'toAccount' => $payload['toAccountId'] ?? '',
                'toAccountName' => $toAccName,
                'beneficiaryName' => $toAccName,
                'recipient' => $toAccName,
                'amount' => $amt > 0 ? $amt : $foreignAmt,
                'amountLocal' => $amt,
                'amountForeign' => $foreignAmt,
                'currency' => $currency,
                'rate' => $rate,
                'costCenter' => $costCenter,
                'bankName' => $bankName,
                'bank' => $bankName,
                'mobileTransferNo' => $transferRef,
                'transferRef' => $transferRef,
                'refNo' => $transferRef,
                'linkedInvoiceNo' => $linkedInvoiceNo,
                'notes' => $payload['notes'] ?? '',
                'toStatement' => $payload['notes'] ?? '',
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
                        } elseif (preg_match('/^E\d{4}(\d{4})$/', $sUpper, $m)) {
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
                } elseif (preg_match('/^E\d{4}(\d{4})$/', $sUpper, $m)) {
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
  <link rel="stylesheet" href="css/tailwind.min.css">
  <script src="js/tailwind.min.js"></script>
  <script>if(typeof tailwind==="undefined"){document.write('<script src="https://cdn.tailwindcss.com"><\/script>');}</script>
  <script src="js/magaies-offline-sync.js?v=20261015_v2" defer></script>
  
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

        <!-- مؤشر مزامنة وتحديث البيانات الذكي -->
        <button type="button" onclick="handleUnifiedSyncRefresh()" id="networkPill" class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/40 text-emerald-400 text-[10.5px] font-black cursor-pointer hover:bg-emerald-500/25 active:scale-95 transition shadow-sm" title="البيانات متزامنة - انقر للتحديث الفوري">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_#10b981]" id="networkDot"></span>
          <i class="fa-solid fa-arrows-rotate text-[9.5px] text-red-300 hidden" id="networkRefreshIcon"></i>
          <span id="networkText">متزامن</span>
        </button>
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
            <span data-auto-fit-voucher="1" class="text-[16px] sm:text-[16.5px] font-mono font-black text-amber-300 whitespace-nowrap block tracking-tight leading-none drop-shadow cursor-pointer hover:underline w-full text-center" style="font-size: 16px;" id="currentVoucherDisplay" onclick="showDigitalVoucherReceipt()" title="اضغط لعرض الإيصال الرقمي الرسمي المعتمد ورمز الـ QR 📜"><?php echo htmlspecialchars($nextSequentialVoucherNo); ?></span>
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
        <a href="sales-pos.html" id="linkedInvoiceReturnLink" class="px-2 py-0.5 rounded-lg bg-indigo-600/80 hover:bg-indigo-500 text-white text-[10px] font-black border border-indigo-400/50 transition">
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
          <button type="button" onclick="handleQuickCheckSave()" id="btnSaveMain" class="action-btn-kinetic h-[42px] sm:h-[44px] min-w-0 px-2 rounded-xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 hover:from-emerald-500 hover:to-cyan-500 active:scale-95 text-white font-black text-[12px] sm:text-[13px] flex items-center justify-center gap-1.5 shadow-lg shadow-emerald-950/70 border border-emerald-400/70 transition" title="تأكيد وحفظ السند">
            <i class="fa-solid fa-check text-xs sm:text-sm font-black shrink-0"></i>
            <span id="saveButtonLabel" class="truncate">حفظ (✓)</span>
          </button>

          <!-- 2. زر جديد: مسح الشاشة وبدء سند جديد فارغ -->
          <button type="button" onclick="startNewVoucher()" class="action-btn-kinetic h-[42px] sm:h-[44px] min-w-0 px-2 rounded-xl bg-gradient-to-b from-cyan-950 via-slate-900 to-slate-950 border-2 border-cyan-400 text-cyan-200 font-black text-[12px] sm:text-[13px] flex items-center justify-center gap-1.5 shadow-[0_0_12px_rgba(6,182,212,0.35)] active:scale-95 transition" title="مسح الشاشة وبدء سند جديد فارغ">
            <i class="fa-solid fa-plus text-cyan-400 text-xs sm:text-sm font-black shrink-0"></i>
            <span class="truncate">جديد</span>
          </button>

          <!-- 3. زر افتح: فتح واستعراض وبحث في السندات -->
          <button type="button" onclick="openSearchVouchersModal()" class="action-btn-kinetic h-[42px] sm:h-[44px] min-w-0 px-2 rounded-xl bg-slate-900 border border-amber-500/60 text-amber-300 font-black text-[12px] sm:text-[13px] flex items-center justify-center gap-1.5 shadow-md active:scale-95 transition" title="فتح واستعراض وبحث في السندات المسجلة">
            <i class="fa-solid fa-folder-open text-amber-400 text-xs sm:text-sm shrink-0"></i>
            <span class="truncate">افتح</span>
          </button>

          <!-- 4. زر تعديل: تعديل بيانات السند الحالي -->
          <button type="button" onclick="handleEdit()" class="action-btn-kinetic h-[42px] sm:h-[44px] min-w-0 px-2 rounded-xl bg-slate-900 border border-indigo-400/60 text-indigo-300 font-black text-[12px] sm:text-[13px] flex items-center justify-center gap-1.5 shadow-md active:scale-95 transition" title="تعديل بيانات السند الحالي">
            <i class="fa-solid fa-pen-to-square text-indigo-400 text-xs sm:text-sm shrink-0"></i>
            <span class="truncate">تعديل</span>
          </button>

        </div>

        <!-- الصف الثاني (الأسفل): 4 أزرار للمشاركة والطباعة والحذف والرجوع -->
        <div class="grid grid-cols-4 gap-1.5 w-full">

          <!-- 5. زر إرسال واتساب -->
          <button type="button" onclick="openSendWhatsAppModal()" class="action-btn-kinetic h-[42px] sm:h-[44px] min-w-0 px-2 rounded-xl bg-slate-900 border border-emerald-500/60 text-emerald-300 font-black text-[12px] sm:text-[13px] flex items-center justify-center gap-1.5 shadow-md active:scale-95 transition" title="إرسال تقرير السند للعميل عبر الواتساب">
            <i class="fa-brands fa-whatsapp text-emerald-400 text-sm sm:text-base shrink-0"></i>
            <span class="truncate">إرسال</span>
          </button>

          <!-- 6. زر طباعة: طباعة إيصال حراري وسند رسمي -->
          <button type="button" onclick="handlePrintVoucher()" class="action-btn-kinetic h-[42px] sm:h-[44px] min-w-0 px-2 rounded-xl bg-slate-900 border border-purple-500/60 text-purple-300 font-black text-[12px] sm:text-[13px] flex items-center justify-center gap-1.5 shadow-md active:scale-95 transition" title="طباعة إيصال حراري أو سند رسمي معتمد">
            <i class="fa-solid fa-print text-purple-400 text-xs sm:text-sm shrink-0"></i>
            <span class="truncate">طباعة</span>
          </button>

          <!-- 7. زر حذف: حذف السند الحالي وعكس أثره المالي -->
          <button type="button" onclick="handleDelete()" id="btnDeleteVoucher" class="action-btn-kinetic h-[42px] sm:h-[44px] min-w-0 px-2 rounded-xl bg-slate-900 border border-rose-500/60 text-rose-300 font-black text-[12px] sm:text-[13px] flex items-center justify-center gap-1.5 shadow-md active:scale-95 transition" title="حذف السند الحالي وعكس أثره المالي">
            <i class="fa-solid fa-trash-can text-rose-400 text-xs sm:text-sm shrink-0"></i>
            <span class="truncate">حذف</span>
          </button>

          <!-- 8. زر رجوع -->
          <button type="button" onclick="handleExit()" class="action-btn-kinetic h-[42px] sm:h-[44px] min-w-0 px-2 rounded-xl bg-slate-900 border border-red-500/60 text-red-300 font-black text-[12px] sm:text-[13px] flex items-center justify-center gap-1.5 shadow-md active:scale-95 transition" title="إنهاء والرجوع للقائمة الرئيسية">
            <i class="fa-solid fa-arrow-right-from-bracket text-cyan-400 text-xs sm:text-sm shrink-0"></i>
            <span class="truncate">رجوع</span>
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
  <script>
    // ════════════════════════════════════════════════════════════════
    // نظام التوست والرسائل المركزي المقتبس من شاشة حركة مالية (single-cash-payment)
    // ════════════════════════════════════════════════════════════════
    function showToast(msg, type = 'success', ms = 3500) {
      let c = document.getElementById('toast-container');
      if (!c) {
        c = document.createElement('div');
        c.id = 'toast-container';
        c.className = 'toast-container';
        document.body.appendChild(c);
      }

      const t = document.createElement('div');
      t.className = `toast ${type}`;

      let icon = '✨';
      if (type === 'error' || type === 'danger') icon = '❌';
      else if (type === 'warning') icon = '⚠️';
      else if (type === 'info') icon = 'ℹ️';
      else if (type === 'success') icon = '✅';

      t.innerHTML = `<span class="toast-icon">${icon}</span><span class="toast-text">${msg}</span>`;

      t.onclick = function() {
        t.classList.add('hide');
        setTimeout(() => { if (t.parentNode) t.remove(); }, 320);
      };

      c.appendChild(t);
      setTimeout(() => {
        if (t.parentNode) {
          t.classList.add('hide');
          setTimeout(() => { if (t.parentNode) t.remove(); }, 350);
        }
      }, ms);
    }
    window.showToast = showToast;

    // ════════════════════════════════════════════════════════════════
    // نافذة التحذير الأمني لطول رقم التحويلة (< 6 أرقام) من شاشة حركة مالية
    // ════════════════════════════════════════════════════════════════
    // ════════════════════════════════════════════════════════════════
    // نافذة التحذير الأمني لطول رقم التحويلة (< 6 أرقام) مع وسائل خروج متعددة
    // ════════════════════════════════════════════════════════════════
    let _lengthModalEscHandler = null;
    let _dupModalEscHandler = null;
    let _suppressTransferBlur = false;
    let _shortTransferAllowed = false;
    let _wasAttemptingSave = false;

    function closeLengthWarningModal(proceed = false, focusInput = false) {
      const m = document.getElementById('critical-alert-modal');
      if (m) m.remove();
      if (_lengthModalEscHandler) {
        document.removeEventListener('keydown', _lengthModalEscHandler);
        _lengthModalEscHandler = null;
      }
      _suppressTransferBlur = true;
      if (proceed) {
        _shortTransferAllowed = true;
        showToast("تم اعتماد رقم التحويلة، جاري المتابعة...", "success", 2000);
        if (_wasAttemptingSave) {
          _wasAttemptingSave = false;
          setTimeout(() => {
            if (typeof saveMobileVoucher === 'function') {
              saveMobileVoucher();
            }
          }, 150);
        }
      } else {
        _wasAttemptingSave = false;
      }
      if (focusInput) {
        const ti = document.getElementById('transferInput');
        if (ti) {
          ti.focus();
          ti.select();
        }
      }
      setTimeout(() => { _suppressTransferBlur = false; }, 400);
    }
    window.closeLengthWarningModal = closeLengthWarningModal;

    function showLengthWarningModal(transferNo) {
      const existing = document.getElementById('critical-alert-modal');
      if (existing) existing.remove();

      _lengthModalEscHandler = (e) => {
        if (e.key === 'Escape') closeLengthWarningModal(false);
      };
      document.addEventListener('keydown', _lengthModalEscHandler);

      const modalHTML = `
        <div id="critical-alert-modal" onclick="if(event.target === this) closeLengthWarningModal(false)" style="position:fixed; inset:0; z-index:999999; display:flex; justify-content:center; align-items:center; background:rgba(10,0,0,0.85); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); padding:16px; box-sizing:border-box;">
          <div style="position:relative; width:min(440px, 94vw); max-width:440px; background: radial-gradient(circle, #8b0000 0%, #3e0000 100%); border-radius:24px; display:flex; flex-direction:column; justify-content:center; align-items:center; border: 3px solid rgba(255,100,100,0.5); padding: 30px 20px; text-align:center; box-sizing:border-box; box-shadow:0 0 50px rgba(220, 20, 60, 0.4);">
            <!-- زر الخروج X في الزاوية العلوية -->
            <button type="button" onclick="closeLengthWarningModal(false)" title="إغلاق التحذير" style="position:absolute; top:12px; left:12px; width:34px; height:34px; border-radius:50%; background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3); color:#fff; font-size:16px; font-weight:bold; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:0.2s;" onmouseover="this.style.background='rgba(255,0,0,0.5)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">✕</button>

            <div style="font-size: 3.8rem; text-shadow: 0 0 20px #ff0000; line-height:1;">⚠️</div>
            <h2 style="color: #ffcccc; font-family: 'Cairo', 'Tajawal', sans-serif; font-size: 1.8rem; margin: 12px 0 8px; text-shadow: 0 0 10px #ff0000; font-weight:900;">تنبيه: رقم التحويلة قصير</h2>
            <p style="color: #fff; font-size: 1.15rem; font-weight:bold; line-height: 1.6;">رقم التحويلة <span style="color:#ffeb3b; font-size:1.6rem; font-family:monospace;">${transferNo}</span><br>أقل من 6 أرقام (${(transferNo||'').length} خانات)</p>
            <p style="color: #ffaaaa; font-size: 0.9rem; margin-bottom: 24px; line-height:1.5;">يفضل أن يتكون رقم التحويل من 6 أرقام لتفادي الأخطاء، ولكن يمكنك المتابعة إذا كان الرقم صحيحاً.</p>

            <!-- أزرار الخروج والاختيار -->
            <div style="display:flex; gap:10px; width:100%;">
              <button type="button" onclick="closeLengthWarningModal(true)" style="flex:1; background: linear-gradient(45deg, #10b981, #059669); color: white; border: 2px solid #6ee7b7; border-radius: 18px; padding: 12px 14px; font-size: 1rem; font-weight: 900; cursor: pointer; box-shadow: 0 0 15px rgba(16,185,129,0.4); transition: all 0.2s; font-family: 'Cairo', 'Tajawal', sans-serif;">
                ✓ فهمت ومتابعة
              </button>
              <button type="button" onclick="closeLengthWarningModal(false, true)" style="flex:1; background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.3); border-radius: 18px; padding: 12px 14px; font-size: 0.95rem; font-weight: bold; cursor: pointer; transition: all 0.2s; font-family: 'Cairo', 'Tajawal', sans-serif;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                ✏️ تعديل الرقم
              </button>
            </div>
          </div>
        </div>
      `;
      document.body.insertAdjacentHTML('beforeend', modalHTML);
      if (typeof triggerHapticPulse === 'function') triggerHapticPulse();
    }
    window.showLengthWarningModal = showLengthWarningModal;

    // ════════════════════════════════════════════════════════════════
    // نافذة تحذير التكرار المطابق مع أرقام وسندات النظام وقاعدة البيانات
    // ════════════════════════════════════════════════════════════════
    function closeDuplicateWarningModal(focusInput = false) {
      const m = document.getElementById('duplicate-alert-modal');
      if (m) m.remove();
      if (_dupModalEscHandler) {
        document.removeEventListener('keydown', _dupModalEscHandler);
        _dupModalEscHandler = null;
      }
      _suppressTransferBlur = true;
      if (focusInput) {
        const ti = document.getElementById('transferInput');
        if (ti) {
          ti.focus();
          ti.select();
        }
      }
      setTimeout(() => { _suppressTransferBlur = false; }, 400);
    }
    window.closeDuplicateWarningModal = closeDuplicateWarningModal;

    function confirmSimilarTransferAndProceed(transferNo) {
      window._confirmedSimilarTransferRef = String(transferNo || '').trim();
      closeDuplicateWarningModal(false);
      if (_wasAttemptingSave) {
        _wasAttemptingSave = false;
        handleSave();
      }
    }
    window.confirmSimilarTransferAndProceed = confirmSimilarTransferAndProceed;

    function showDuplicateWarningModal(transferNo, duplicateInfo = null) {
      const existing = document.getElementById('duplicate-alert-modal');
      if (existing) existing.remove();

      _dupModalEscHandler = (e) => {
        if (e.key === 'Escape') closeDuplicateWarningModal(false);
      };
      document.addEventListener('keydown', _dupModalEscHandler);

      const vNo = duplicateInfo?.voucherNo || duplicateInfo?.matchedVoucher || 'سابق';
      const vDate = duplicateInfo?.date || duplicateInfo?.matchedDate || '';
      const vBank = duplicateInfo?.bank || duplicateInfo?.matchedBank || '';
      const vAmt = duplicateInfo?.amount || duplicateInfo?.matchedAmount || '';
      const vFrom = duplicateInfo?.from || duplicateInfo?.matchedFrom || '';
      const vTo = duplicateInfo?.to || duplicateInfo?.matchedTo || '';
      const vRef = duplicateInfo?.ref || duplicateInfo?.matchedRef || '';
      const isSoft = (duplicateInfo?.severity === 'soft' || (duplicateInfo?.matchType && duplicateInfo.matchType.includes('تشابه')));
      const borderColor = isSoft ? '#f59e0b' : '#ff0044';
      const headerTitle = isSoft ? 'تنبيه تشابه رقمي (خطأ مطبعي محتمل)' : 'تحويلة مكررة ومرفوضة';
      const subText = isSoft
        ? `رقم التحويلة قريب جداً (بفارق خانة واحدة) من الرقم (${vRef}) المسجل سابقاً. هل أنت متأكد من صحته؟`
        : 'تم اكتشاف تطابق تام لهذا الرقم في قاعدة البيانات! يمنع النظام تكرار نفس التحويلة مرتين.';

      const modalHTML = `
        <div id="duplicate-alert-modal" onclick="if(event.target === this) closeDuplicateWarningModal(false)" style="position:fixed; inset:0; z-index:999999; display:flex; justify-content:center; align-items:center; background:radial-gradient(circle at center, rgba(30,0,0,0.92) 0%, rgba(0,0,0,1) 100%); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); padding:16px; box-sizing:border-box;">
          <div style="position:relative; width:min(480px, 94vw); max-width:480px; background: rgba(18,5,12,0.96); border-radius:24px; display:flex; flex-direction:column; justify-content:center; align-items:center; border: 3px solid ${borderColor}; padding: 28px 20px; text-align:center; z-index:2; box-shadow:0 0 50px ${isSoft ? 'rgba(245,158,11,0.35)' : 'rgba(255,0,68,0.4)'}; box-sizing:border-box;">
            <!-- زر الخروج X في الزاوية العلوية -->
            <button type="button" onclick="closeDuplicateWarningModal(false)" title="إغلاق التحذير" style="position:absolute; top:12px; left:12px; width:34px; height:34px; border-radius:50%; background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3); color:#fff; font-size:16px; font-weight:bold; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:0.2s;">✕</button>

            <div style="font-size: 3.2rem; line-height:1; color: ${borderColor}; font-family: monospace; font-weight:900;">${isSoft ? '⚠️ CHECK!' : '⛔ DUPLICATE!'}</div>
            <h2 style="color: #fff; font-family: 'Cairo', 'Tajawal', sans-serif; font-size: 1.45rem; margin: 10px 0 6px; font-weight:900;">${headerTitle}</h2>
            <div style="width:100%; height:2px; background:${borderColor}; margin:6px 0 12px;"></div>
            
            <p style="color: #ddd; font-size: 0.98rem; line-height: 1.5; font-weight:bold;">${subText}</p>
            <div style="color: #fff; font-size: 0.95rem; margin: 8px 0 16px; line-height:1.7; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.18); border-radius: 14px; padding: 12px; width: 100%; text-align: right;">
              <div>الرقم المدخل: <strong style="background:${borderColor}; color:#fff; padding:2px 8px; border-radius:6px; font-family:monospace; font-size:1.15rem;">${transferNo}</strong> ${vRef && vRef !== transferNo ? `<span style="color:#94a3b8; font-size:0.85rem;">(المسجل: ${vRef})</span>` : ''}</div>
              <div class="mt-1">مسجل بالسند: <strong style="color:#fde047; font-size:1.1rem;">#${vNo}</strong></div>
              ${vDate ? `<div style="color:#cbd5e1; font-size:0.85rem;" class="mt-0.5">التاريخ: ${vDate} ${vBank ? '| ' + vBank : ''}</div>` : ''}
              ${(vFrom || vTo) ? `<div style="color:#cbd5e1; font-size:0.85rem;" class="mt-0.5">الأطراف: ${vFrom} ⬅️ ${vTo}</div>` : ''}
              ${vAmt ? `<div style="color:#86efac; font-size:0.9rem;" class="mt-0.5 font-bold">المبلغ: ${Number(vAmt).toLocaleString()} ج.س</div>` : ''}
            </div>

            <!-- أزرار الإجراء -->
            <div style="display:flex; flex-direction:column; gap:8px; width:100%;">
              <div style="display:flex; gap:10px; width:100%;">
                <button type="button" onclick="closeDuplicateWarningModal(true)" style="flex:1; background: ${borderColor}; color: ${isSoft ? '#0f172a' : 'white'}; border: none; border-radius:16px; padding: 11px 14px; font-size: 1rem; font-weight: 900; cursor: pointer; font-family: 'Cairo', 'Tajawal', sans-serif;">
                  ✏️ تعديل رقم التحويل
                </button>
                <button type="button" onclick="closeDuplicateWarningModal(false)" style="background: rgba(255,255,255,0.1); color: #ddd; border: 1px solid rgba(255,255,255,0.3); border-radius:16px; padding: 11px 16px; font-size: 0.9rem; font-weight: bold; cursor: pointer; font-family: 'Cairo', 'Tajawal', sans-serif;">
                  إغلاق
                </button>
              </div>
              ${isSoft ? `
              <button type="button" onclick="confirmSimilarTransferAndProceed('${String(transferNo).replace(/'/g, "\\'")}')" style="width:100%; background: rgba(16,185,129,0.2); color: #34d399; border: 1.5px solid #10b981; border-radius:14px; padding: 10px 14px; font-size: 0.92rem; font-weight: 900; cursor: pointer; font-family: 'Cairo', 'Tajawal', sans-serif;">
                ✓ الرقم صحيح ومختلف — تأكيد ومتابعة الحفظ
              </button>
              ` : ''}
            </div>
          </div>
        </div>
      `;
      document.body.insertAdjacentHTML('beforeend', modalHTML);
      if (typeof triggerHapticPulse === 'function') triggerHapticPulse();
    }
    window.showDuplicateWarningModal = showDuplicateWarningModal;

    // ════════════════════════════════════════════════════════════════
    // توليد رمز QR SVG محلي أوفلاين (100% يعمل بدون إنترنت ولا يسرب بيانات للخارج)
    // ════════════════════════════════════════════════════════════════
    function generateInlineQrSvg(text, size = 120) {
      const gridSize = 21;
      const matrix = Array.from({ length: gridSize }, () => Array(gridSize).fill(0));
      const drawFinder = (r0, c0) => {
        for (let r = 0; r < 7; r++) {
          for (let c = 0; c < 7; c++) {
            const isBorder = (r === 0 || r === 6 || c === 0 || c === 6);
            const isInner = (r >= 2 && r <= 4 && c >= 2 && c <= 4);
            matrix[r0 + r][c0 + c] = (isBorder || isInner) ? 1 : 2;
          }
        }
      };
      drawFinder(0, 0);
      drawFinder(0, gridSize - 7);
      drawFinder(gridSize - 7, 0);
      let seed = 2166136261;
      const str = String(text || 'ELSAJANA');
      for (let i = 0; i < str.length; i++) {
        seed ^= str.charCodeAt(i);
        seed = Math.imul(seed, 16777619) >>> 0;
      }
      for (let r = 0; r < gridSize; r++) {
        for (let c = 0; c < gridSize; c++) {
          if (matrix[r][c] !== 0) continue;
          if (r === 6 || c === 6) {
            matrix[r][c] = ((r + c) % 2 === 0) ? 1 : 2;
            continue;
          }
          seed ^= (r * 31 + c * 17 + str.charCodeAt((r * gridSize + c) % str.length));
          seed = Math.imul(seed, 16777619) >>> 0;
          matrix[r][c] = (seed & 1) ? 1 : 2;
        }
      }
      let rects = '';
      for (let r = 0; r < gridSize; r++) {
        for (let c = 0; c < gridSize; c++) {
          if (matrix[r][c] === 1) {
            rects += `<rect x="${c}" y="${r}" width="1" height="1" fill="#0f172a"/>`;
          }
        }
      }
      return `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="-1 -1 ${gridSize + 2} ${gridSize + 2}" style="display:block;border-radius:8px;background:#ffffff;"><rect x="-1" y="-1" width="${gridSize + 2}" height="${gridSize + 2}" fill="#ffffff"/>${rects}</svg>`;
    }
    window.generateInlineQrSvg = generateInlineQrSvg;

    // ════════════════════════════════════════════════════════════════
    // بصمة التحقق الرقمية التشفيرية المعتمدة (Cryptographic Verification Hash)
    // ════════════════════════════════════════════════════════════════
    function generateVoucherHash(vId, date, amount, from, to) {
      const str = `${vId || ''}|${date || ''}|${amount || 0}|${from || ''}|${to || ''}|ELSAJANA2026`;
      let hash = 0;
      for (let i = 0; i < str.length; i++) {
        hash = ((hash << 5) - hash) + str.charCodeAt(i);
        hash |= 0;
      }
      const hex1 = Math.abs(hash).toString(16).toUpperCase().padStart(8, '0');
      const hex2 = ((Math.abs(hash * 31) >>> 0) % 0xFFFFFF).toString(16).toUpperCase().padStart(6, '0');
      return `VER-${hex1.slice(0, 4)}-${hex1.slice(4)}-${hex2}`;
    }
    window.generateVoucherHash = generateVoucherHash;

    // ════════════════════════════════════════════════════════════════
    // بطاقة النجاح الاحترافية عند الحفظ والاعتماد المقتبسة من شاشة حركة مالية
    // ════════════════════════════════════════════════════════════════
    let professionalSuccessTimer = null;
    function showProfessionalSuccess(voucherId, fromName, toName, amountLocal, currency, title, vData) {
      const existing = document.getElementById('professional-success-modal');
      if (existing) existing.remove();
      if (professionalSuccessTimer) clearTimeout(professionalSuccessTimer);

      const overlay = document.createElement('div');
      overlay.id = 'professional-success-modal';
      overlay.style.position = 'fixed';
      overlay.style.inset = '0';
      overlay.style.width = '100vw'; overlay.style.height = '100vh';
      overlay.style.background = 'rgba(5, 3, 14, 0.82)';
      overlay.style.backdropFilter = 'blur(14px)';
      overlay.style.webkitBackdropFilter = 'blur(14px)';
      overlay.style.zIndex = '999998';
      overlay.style.display = 'flex';
      overlay.style.alignItems = 'center';
      overlay.style.justifyContent = 'center';
      overlay.style.padding = '16px';
      overlay.style.opacity = '0';
      overlay.style.direction = 'rtl';
      overlay.style.transition = 'opacity 0.35s ease';

      overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
          overlay.remove();
        }
      });

      const box = document.createElement('div');
      box.style.background = 'linear-gradient(155deg, #0f172a 0%, #1e1b4b 55%, #172554 100%)';
      box.style.padding = '26px 20px';
      box.style.borderRadius = '26px';
      box.style.boxShadow = '0 30px 70px rgba(0,0,0,0.9), 0 0 45px rgba(99, 102, 241, 0.45) inset';
      box.style.border = '1.5px solid rgba(167, 139, 250, 0.5)';
      box.style.textAlign = 'center';
      box.style.transform = 'scale(0.85) translateY(25px)';
      box.style.transition = 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
      box.style.color = '#fff';
      box.style.width = 'min(430px, 94vw)';
      box.style.boxSizing = 'border-box';
      box.style.position = 'relative';

      const vDataJson = JSON.stringify(vData || {
        id: voucherId,
        voucherNo: voucherId,
        from: fromName,
        to: toName,
        sdg: amountLocal,
        amountLocal: amountLocal,
        currency: currency || 'SDG'
      }).replace(/"/g, '&quot;');

      box.innerHTML = `
        <style>
          @keyframes drawCheck {
            0% { stroke-dasharray: 0, 100; stroke-dashoffset: 0; opacity: 0; }
            50% { opacity: 1; }
            100% { stroke-dasharray: 100, 0; stroke-dashoffset: 0; opacity: 1; }
          }
          @keyframes pulseGlow {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
            70% { box-shadow: 0 0 0 24px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
          }
          @keyframes slideRight {
            0% { transform: translateX(-25px); opacity: 0; }
            100% { transform: translateX(0); opacity: 1; }
          }
          @keyframes slideLeft {
            0% { transform: translateX(25px); opacity: 0; }
            100% { transform: translateX(0); opacity: 1; }
          }
          @keyframes arrowMove {
            0% { transform: translateX(0); }
            50% { transform: translateX(-8px); text-shadow: 0 0 15px rgba(99,102,241,1); }
            100% { transform: translateX(0); }
          }
        </style>

        <button type="button" onclick="document.getElementById('professional-success-modal').remove()" style="position:absolute; top:14px; left:14px; width:30px; height:30px; border-radius:10px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#94a3b8; font-size:0.9rem; cursor:pointer; display:flex; align-items:center; justify-content:center;" title="إغلاق">✕</button>

        <div style="width: 72px; height: 72px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; margin: 0 auto 16px auto; display: flex; align-items: center; justify-content: center; animation: pulseGlow 2s infinite; border: 4px solid #34d399; box-shadow: 0 0 20px rgba(16,185,129,0.5);">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" style="stroke-dasharray: 100; animation: drawCheck 0.8s ease-out forwards;">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
        </div>

        <h2 style="margin: 0 0 6px 0; font-size: 1.65rem; font-weight: 900; color: #34d399; text-shadow: 0 2px 10px rgba(0,0,0,0.5); font-family:'Cairo','Tajawal',sans-serif;">${title || 'تم الحفظ والاعتماد'}</h2>
        <p style="margin: 0 0 18px 0; font-size: 1.05rem; color: #e2e8f0;">تم اعتماد وترحيل السند المالي رقم <strong style="color: #fde047; background: rgba(0,0,0,0.35); padding: 3px 12px; border-radius: 8px; font-family:monospace; border:1px solid rgba(250,204,21,0.3); font-size:1.1rem;">#${voucherId}</strong></p>
        
        <div style="background: rgba(0,0,0,0.5); padding: 14px; border-radius: 18px; display: flex; align-items: center; justify-content: space-between; gap: 10px; border: 1px solid rgba(255,255,255,0.12); box-shadow: 0 10px 25px rgba(0,0,0,0.5) inset;">
          <div style="flex: 1; text-align: right; animation: slideRight 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; animation-delay: 0.15s; opacity: 0;">
            <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 3px; font-weight:bold;">من حساب (الدافع)</div>
            <div style="font-weight: 900; color: #38bdf8; font-size: 0.95rem; line-height: 1.3;">${fromName}</div>
          </div>
          
          <div style="display: flex; flex-direction: column; align-items: center; animation: arrowMove 1.5s ease-in-out infinite;">
            <div style="color: #34d399; font-weight: 900; font-size: 1.25rem; margin-bottom: 2px; text-shadow: 0 0 10px rgba(52,211,153,0.5); font-family:monospace;">${Number(amountLocal).toLocaleString()}</div>
            <div style="font-size: 0.72rem; font-weight: 900; color: #059669; background: #d1fae5; padding: 2px 7px; border-radius: 6px;">${currency}</div>
            <div style="font-size: 1.5rem; color: #818cf8; margin-top: 3px;">⬅️</div>
          </div>
          
          <div style="flex: 1; text-align: left; animation: slideLeft 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; animation-delay: 0.3s; opacity: 0;">
            <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 3px; font-weight:bold;">إلى حساب (المستلم)</div>
            <div style="font-weight: 900; color: #f472b6; font-size: 0.95rem; line-height: 1.3;">${toName}</div>
          </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:10px; margin-top:18px;">
          <!-- زر عرض الإيصال الرقمي الرسمي المعتمد -->
          <button type="button" onclick="document.getElementById('professional-success-modal').remove(); showDigitalVoucherReceipt(${vDataJson});" style="width:100%; padding:12px; background:linear-gradient(135deg, #6366f1, #4f46e5); color:white; border:1px solid #818cf8; border-radius:14px; font-weight:900; font-size:0.98rem; cursor:pointer; font-family:'Cairo','Tajawal',sans-serif; box-shadow:0 6px 20px rgba(99,102,241,0.5); display:flex; align-items:center; justify-content:center; gap:8px;">
            <span style="font-size:1.15rem;">📜</span>
            <span>عرض الإيصال الرسمي المعتمد والـ QR Code</span>
          </button>

          <div style="display:flex; gap:8px;">
            <!-- زر التراجع الفوري عن السند -->
            <button type="button" onclick="document.getElementById('professional-success-modal').remove(); executeUndoVoucher('${voucherId}');" style="flex:1; padding:10px 8px; background:rgba(239,68,68,0.15); border:1.5px solid #ef4444; color:#fca5a5; border-radius:12px; font-weight:900; font-size:0.85rem; cursor:pointer; font-family:'Cairo','Tajawal',sans-serif; display:flex; align-items:center; justify-content:center; gap:5px;">
              <span>↩️ تراجع عن السند</span>
            </button>

            <!-- زر إغلاق ومتابعة العمل -->
            <button type="button" onclick="document.getElementById('professional-success-modal').remove();" style="flex:1.2; padding:10px 12px; background:linear-gradient(135deg, #10b981, #059669); color:white; border:none; border-radius:12px; font-weight:900; font-size:0.88rem; cursor:pointer; font-family:'Cairo','Tajawal',sans-serif; box-shadow:0 4px 15px rgba(16,185,129,0.4);">
              <span>✓ متابعة العمل</span>
            </button>
          </div>
        </div>
      `;

      overlay.appendChild(box);
      document.body.appendChild(overlay);

      requestAnimationFrame(() => {
        overlay.style.opacity = '1';
        box.style.transform = 'scale(1) translateY(0)';
      });

      professionalSuccessTimer = setTimeout(() => {
        if (overlay && overlay.parentNode) {
          overlay.style.opacity = '0';
          box.style.transform = 'scale(0.85) translateY(-20px)';
          setTimeout(() => { if (overlay.parentNode) overlay.remove(); }, 350);
        }
      }, 9000);
    }
    window.showProfessionalSuccess = showProfessionalSuccess;

    // ════════════════════════════════════════════════════════════════
    // نافذة الإيصال الرقمي الرسمي المعتمد مع الـ QR Code والمشاركة والطباعة
    // (Official Bank-Grade Digital Receipt Modal with Live Tafqeet & Offline SVG QR)
    // ════════════════════════════════════════════════════════════════
    function showDigitalVoucherReceipt(vData) {
      const existing = document.getElementById('digitalReceiptModal');
      if (existing) existing.remove();

      if (!vData) {
        const v = (currentIndex >= 0 && currentIndex < vouchersList.length) ? vouchersList[currentIndex] : null;
        const vNum = (document.getElementById('currentVoucherDisplay')?.textContent || v?.id || 'E24-001').trim();
        const dateVal = document.getElementById('voucherDate')?.value || v?.date || new Date().toISOString().slice(0, 10);
        const sdgVal = (typeof parseRawNumber === 'function' ? parseRawNumber(document.getElementById('sdgAmount')?.value) : Number(document.getElementById('sdgAmount')?.value)) || v?.sdg || 0;
        const usdVal = (typeof parseRawNumber === 'function' ? parseRawNumber(document.getElementById('usdAmount')?.value) : Number(document.getElementById('usdAmount')?.value)) || v?.usd || 0;

        vData = {
          id: vNum,
          voucherNo: vNum,
          date: dateVal,
          time: v?.time || new Date().toLocaleTimeString('ar-EG', {hour:'2-digit', minute:'2-digit'}),
          sdg: sdgVal,
          usd: usdVal,
          amountLocal: sdgVal || usdVal,
          currency: currentCurrency || v?.currency || 'SDG',
          from: (document.getElementById('fromSearchInput')?.value || v?.from || '---').trim(),
          to: (document.getElementById('toSearchInput')?.value || v?.to || '---').trim(),
          refNo: (document.getElementById('transferInput')?.value || v?.refNo || '---').trim(),
          bankName: (document.getElementById('bankNameInput')?.value || v?.bankName || 'بنك الخرطوم').trim(),
          payType: currentPayType || v?.payType || 'cash',
          notes: (document.getElementById('voucherNotes')?.value || v?.notes || 'سند حركة مالية معتمد').trim(),
          costCenter: (document.getElementById('costCenterInput')?.value || v?.costCenter || 'مخزن بحري الرئيسي').trim(),
          signatureHash: v?.signatureHash || ''
        };
      }

      const vId = vData.id || vData.voucherNo || 'E24-001';
      const amount = Number(vData.sdg || vData.amountLocal || vData.usd || 0);
      const cur = vData.currency || 'SDG';
      const curSymbol = (cur === 'USD') ? '$' : (cur === 'SAR' ? 'ر.س' : (cur === 'AED' ? 'د.إ' : (cur === 'EGP' ? 'ج.م' : (cur === 'EUR' ? '€' : 'ج.س'))));
      const date = vData.date || new Date().toISOString().slice(0, 10);
      const time = vData.time || new Date().toLocaleTimeString('ar-EG', {hour:'2-digit', minute:'2-digit'});
      const from = vData.from || vData.fromAccountName || '---';
      const to = vData.to || vData.toAccountName || '---';
      const refNo = vData.refNo || vData.mobileTransferNo || '---';
      const bank = vData.bankName || 'بنك الخرطوم';
      const payType = vData.payType || vData.paymentMethod || 'cash';
      const payTypeLabel = (payType === 'transfer') ? '⚡ تحويل بنكي فوري' : (payType === 'cheque' ? '📑 شيك مصرفي' : '💵 نقداً (كاش)');
      const notes = vData.notes || 'سند قيد وحركة مالية رسمية';
      const costCenter = vData.costCenter || 'مخزن بحري الرئيسي';

      const tafqeetText = (typeof arabicNumberToWords === 'function') ? arabicNumberToWords(amount, cur) : `${amount} ${cur}`;
      const hash = vData.signatureHash || generateVoucherHash(vId, date, amount, from, to);

      const qrPayload = `${window.location.origin}${window.location.pathname}?api=verify_voucher&voucherNo=${encodeURIComponent(vId)}&sig=${encodeURIComponent(hash)}`;
      const inlineQrSvg = generateInlineQrSvg(qrPayload, 120);

      const overlay = document.createElement('div');
      overlay.id = 'digitalReceiptModal';
      overlay.style.position = 'fixed';
      overlay.style.inset = '0';
      overlay.style.width = '100vw'; overlay.style.height = '100vh';
      overlay.style.background = 'rgba(2, 6, 23, 0.88)';
      overlay.style.backdropFilter = 'blur(14px)';
      overlay.style.webkitBackdropFilter = 'blur(14px)';
      overlay.style.zIndex = '999999';
      overlay.style.display = 'flex';
      overlay.style.alignItems = 'center';
      overlay.style.justifyContent = 'center';
      overlay.style.padding = '14px';
      overlay.style.opacity = '0';
      overlay.style.direction = 'rtl';
      overlay.style.transition = 'opacity 0.3s ease';

      overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeDigitalReceiptModal();
      });

      const card = document.createElement('div');
      card.id = 'digitalReceiptCard';
      card.style.background = 'linear-gradient(165deg, #090d16 0%, #0f172a 45%, #1e1b4b 100%)';
      card.style.border = '2px solid #818cf8';
      card.style.borderRadius = '28px';
      card.style.width = 'min(440px, 96vw)';
      card.style.maxHeight = '92vh';
      card.style.overflowY = 'auto';
      card.style.boxShadow = '0 25px 70px rgba(0,0,0,0.95), 0 0 40px rgba(99,102,241,0.25) inset';
      card.style.color = '#fff';
      card.style.padding = '20px 18px';
      card.style.boxSizing = 'border-box';
      card.style.fontFamily = "'Cairo', 'Tajawal', sans-serif";
      card.style.transform = 'scale(0.9) translateY(20px)';
      card.style.transition = 'all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1)';
      card.style.position = 'relative';

      card.innerHTML = `
        <!-- زر الإغلاق السريع ✕ -->
        <button type="button" onclick="closeDigitalReceiptModal()" style="position:absolute; top:16px; left:16px; width:34px; height:34px; border-radius:12px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.18); color:#cbd5e1; font-size:1.1rem; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.2s;" title="إغلاق الإيصال">✕</button>

        <!-- الهيدر والشعار الرسمي -->
        <div style="text-align:center; padding-bottom:14px; border-bottom:1px dashed rgba(255,255,255,0.15);">
          <div style="display:inline-flex; align-items:center; gap:6px; background:linear-gradient(90deg, rgba(16,185,129,0.2), rgba(6,182,212,0.2)); border:1px solid #10b981; padding:3px 12px; border-radius:20px; font-size:0.75rem; font-weight:900; color:#34d399; margin-bottom:8px;">
            <span style="display:inline-block; width:7px; height:7px; border-radius:50%; background:#10b981; box-shadow:0 0 8px #10b981;"></span>
            <span>إشعار سند مالي معتمد ومرحّل | HMAC VERIFIED</span>
          </div>
          <h1 style="margin:0 0 3px 0; font-size:1.35rem; font-weight:900; color:#fff; letter-spacing:-0.3px;">مؤسسة سجانه للأنشطة المتعددة</h1>
          <div style="font-size:0.75rem; color:#94a3b8; font-weight:700;">نظام المقاييس المحاسبي الموحد - إشعار قيد رسمي</div>
        </div>

        <!-- شريط رقم السند والتاريخ -->
        <div style="display:flex; justify-content:space-between; align-items:center; background:rgba(0,0,0,0.4); padding:10px 14px; border-radius:14px; margin:14px 0; border:1px solid rgba(255,255,255,0.08);">
          <div style="text-align:right;">
            <div style="font-size:0.72rem; color:#94a3b8; font-weight:700;">رقم السند المالي:</div>
            <div style="font-family:monospace; font-size:1.15rem; font-weight:900; color:#fde047;">#${vId}</div>
          </div>
          <div style="text-align:left;">
            <div style="font-size:0.72rem; color:#94a3b8; font-weight:700;">تاريخ وتوقيت القيد:</div>
            <div style="font-family:monospace; font-size:0.85rem; font-weight:700; color:#e2e8f0;">${date} <span style="color:#64748b;">|</span> ${time}</div>
          </div>
        </div>

        <!-- بطاقة المبلغ والتفقيط العربي الفاخر -->
        <div style="background:linear-gradient(135deg, rgba(6,78,59,0.45), rgba(15,23,42,0.7)); border:1.5px solid rgba(16,185,129,0.5); border-radius:18px; padding:14px; text-align:center; box-shadow:0 10px 25px rgba(0,0,0,0.3) inset;">
          <div style="font-size:0.75rem; color:#6ee7b7; font-weight:800; margin-bottom:4px;">المبلغ المعتمد في السند</div>
          <div style="font-family:monospace; font-size:1.9rem; font-weight:900; color:#34d399; text-shadow:0 0 15px rgba(52,211,153,0.4); line-height:1.2;">
            ${Number(amount).toLocaleString()} <span style="font-size:1rem; color:#a7f3d0; font-family:'Cairo',sans-serif;">${curSymbol}</span>
          </div>
          ${vData.usd && vData.usd > 0 && cur !== 'USD' ? `<div style="font-family:monospace; font-size:0.82rem; color:#94a3b8; margin-top:2px;">(المقابل الأجنبي: ${Number(vData.usd).toLocaleString()} ${cur})</div>` : ''}

          <!-- التفقيط العربي التلقائي -->
          <div style="margin-top:8px; padding:8px 10px; background:rgba(0,0,0,0.4); border-radius:10px; border:1px solid rgba(255,255,255,0.08); font-size:0.82rem; font-weight:800; color:#f1f5f9; line-height:1.4;">
            <span style="color:#fde047;">فقط:</span> ${tafqeetText} <span style="color:#fde047;">لا غير.</span>
          </div>
        </div>

        <!-- حركة أطراف السند (الدافع والمستلم) -->
        <div style="background:rgba(0,0,0,0.35); border-radius:16px; padding:12px; margin:12px 0; border:1px solid rgba(255,255,255,0.08); display:flex; align-items:center; justify-content:space-between; gap:10px;">
          <div style="flex:1; text-align:right;">
            <div style="font-size:0.72rem; color:#94a3b8; font-weight:700;">من حساب (الدافع):</div>
            <div style="font-weight:900; color:#38bdf8; font-size:0.95rem; line-height:1.3; margin-top:2px;">${from}</div>
          </div>
          <div style="font-size:1.3rem; color:#818cf8; flex-shrink:0;">⬅️</div>
          <div style="flex:1; text-align:left;">
            <div style="font-size:0.72rem; color:#94a3b8; font-weight:700;">إلى حساب (المستلم):</div>
            <div style="font-weight:900; color:#f472b6; font-size:0.95rem; line-height:1.3; margin-top:2px;">${to}</div>
          </div>
        </div>

        <!-- جدول تفاصيل العملية -->
        <div style="background:rgba(0,0,0,0.3); border-radius:14px; padding:10px 12px; font-size:0.78rem; space-y:6px; border:1px solid rgba(255,255,255,0.06); margin-bottom:14px;">
          <div style="display:flex; justify-content:space-between; padding:3px 0; border-bottom:1px solid rgba(255,255,255,0.05);">
            <span style="color:#94a3b8;">طريقة الدفع:</span>
            <span style="font-weight:800; color:#e2e8f0;">${payTypeLabel}</span>
          </div>
          <div style="display:flex; justify-content:space-between; padding:3px 0; border-bottom:1px solid rgba(255,255,255,0.05);">
            <span style="color:#94a3b8;">البنك المعتمد:</span>
            <span style="font-weight:800; color:#e2e8f0;">${bank}</span>
          </div>
          <div style="display:flex; justify-content:space-between; padding:3px 0; border-bottom:1px solid rgba(255,255,255,0.05);">
            <span style="color:#94a3b8;">رقم التحويلة / الشيك:</span>
            <span style="font-family:monospace; font-weight:900; color:#fde047; font-size:0.85rem;">${refNo}</span>
          </div>
          <div style="display:flex; justify-content:space-between; padding:3px 0; border-bottom:1px solid rgba(255,255,255,0.05);">
            <span style="color:#94a3b8;">مركز التكلفة:</span>
            <span style="font-weight:800; color:#cbd5e1;">${costCenter}</span>
          </div>
          <div style="display:flex; justify-content:space-between; padding:3px 0;">
            <span style="color:#94a3b8;">البيان:</span>
            <span style="font-weight:800; color:#f1f5f9; text-align:left; max-width:200px;" class="truncate">${notes}</span>
          </div>
        </div>

        <!-- رمز الاستجابة السريعة QR Code المحلي وبصمة التوثيق المشفرة HMAC-SHA256 -->
        <div style="background:rgba(15,23,42,0.8); border:1px solid rgba(129,140,248,0.3); border-radius:18px; padding:12px; text-align:center; margin-bottom:16px;">
          <div style="width:130px; height:130px; margin:0 auto; background:#ffffff; border-radius:12px; padding:5px; box-shadow:0 6px 18px rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center;">
            ${inlineQrSvg}
          </div>
          <div style="font-family:monospace; font-size:0.75rem; color:#38bdf8; font-weight:800; margin-top:8px; letter-spacing:0.5px;">
            ${hash}
          </div>
          <div style="font-size:0.7rem; color:#34d399; font-weight:700; margin-top:2px;">
            ✓ توقيع رقمي مشفر (HMAC-SHA256) ومولد محلياً بدون تسريب خارجي
          </div>
        </div>

        <!-- أزرار الإجراءات التفاعلية: مشاركة واتساب، طباعة، نسخ، إغلاق -->
        <div style="display:flex; flex-direction:column; gap:8px;">
          <!-- زر المشاركة عبر الواتساب -->
          <button type="button" onclick="shareReceiptOnWhatsApp(${JSON.stringify(vData).replace(/"/g, '&quot;')})" style="width:100%; padding:11px; background:linear-gradient(135deg, #059669, #10b981); color:#fff; border:none; border-radius:12px; font-weight:900; font-size:0.95rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 4px 15px rgba(16,185,129,0.4); active:scale-98;">
            <i class="fa-brands fa-whatsapp" style="font-size:1.15rem;"></i>
            <span>إرسال ومشاركة الإيصال عبر الواتساب</span>
          </button>

          <div style="display:flex; gap:8px;">
            <!-- زر الطباعة الفورية POS / A4 -->
            <button type="button" onclick="closeDigitalReceiptModal(); handlePrintVoucher();" style="flex:1; padding:10px; background:linear-gradient(135deg, #7c3aed, #6d28d9); color:#fff; border:1px solid #a78bfa; border-radius:12px; font-weight:900; font-size:0.85rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
              <i class="fa-solid fa-print"></i>
              <span>طباعة إيصال 🖨️</span>
            </button>

            <!-- زر نسخ الإشعار للحافظة -->
            <button type="button" onclick="copyReceiptText(${JSON.stringify(vData).replace(/"/g, '&quot;')})" style="flex:1; padding:10px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.18); color:#e2e8f0; border-radius:12px; font-weight:900; font-size:0.85rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
              <i class="fa-solid fa-copy"></i>
              <span>نسخ الإشعار 📋</span>
            </button>
          </div>

          <!-- زر الإغلاق والمتابعة -->
          <button type="button" onclick="closeDigitalReceiptModal()" style="width:100%; padding:9px; background:transparent; border:none; color:#94a3b8; font-weight:800; font-size:0.85rem; cursor:pointer; text-decoration:underline;">
            إغلاق النافذة
          </button>
        </div>
      `;

      overlay.appendChild(card);
      document.body.appendChild(overlay);

      requestAnimationFrame(() => {
        overlay.style.opacity = '1';
        card.style.transform = 'scale(1) translateY(0)';
      });

      triggerHapticPulse();
      playBankerChime();
    }
    window.showDigitalVoucherReceipt = showDigitalVoucherReceipt;

    function closeDigitalReceiptModal() {
      const modal = document.getElementById('digitalReceiptModal');
      if (!modal) return;
      modal.style.opacity = '0';
      const card = document.getElementById('digitalReceiptCard');
      if (card) card.style.transform = 'scale(0.9) translateY(20px)';
      setTimeout(() => { if (modal.parentNode) modal.remove(); }, 300);
    }
    window.closeDigitalReceiptModal = closeDigitalReceiptModal;

    function shareReceiptOnWhatsApp(vData) {
      if (!vData) return;
      const vId = vData.id || vData.voucherNo || '---';
      const amount = Number(vData.sdg || vData.amountLocal || vData.usd || 0);
      const cur = vData.currency || 'SDG';
      const curSymbol = (cur === 'USD') ? '$' : (cur === 'SAR' ? 'ر.س' : (cur === 'AED' ? 'د.إ' : (cur === 'EUR' ? '€' : 'ج.س')));
      const date = vData.date || '';
      const time = vData.time || '';
      const from = vData.from || '---';
      const to = vData.to || '---';
      const refNo = vData.refNo || vData.mobileTransferNo || '---';
      const bank = vData.bankName || 'بنك الخرطوم';
      const notes = vData.notes || 'سند حركة مالية معتمد';
      const tafqeet = typeof arabicNumberToWords === 'function' ? arabicNumberToWords(amount, cur) : '';
      const hash = generateVoucherHash(vId, date, amount, from, to);

      const text = `🏢 *مؤسسة سجانه للأنشطة المتعددة*
━━━━━━━━━━━━━━━━━━━
🧾 *إشعار سند مالي معتمد ومرحّل*
🔢 *رقم السند:* #${vId}
📅 *التاريخ والوقت:* ${date} ${time ? ' | ' + time : ''}
💰 *المبلغ:* ${amount.toLocaleString()} ${curSymbol} (${cur})
✍️ *فقط:* ${tafqeet} لا غير
━━━━━━━━━━━━━━━━━━━
🔴 *من حساب (الدافع):* ${from}
🟢 *إلى حساب (المستلم):* ${to}
🏦 *البنك المعتمد:* ${bank}
🔢 *رقم التحويلة / الإشعار:* ${refNo}
📝 *البيان:* ${notes}
🔐 *بصمة التوثيق الرقمية:* ${hash}
━━━━━━━━━━━━━━━━━━━
✅ *عملية مالية رسمية موثقة ومرحلة بنجاح في قاعدة البيانات MariaDB*`;

      const url = `https://wa.me/?text=${encodeURIComponent(text)}`;
      window.open(url, '_blank');
      showToastNotification('📲 تم فتح الواتساب لمشاركة الإشعار المالي الرسمي!');
    }
    window.shareReceiptOnWhatsApp = shareReceiptOnWhatsApp;

    function copyReceiptText(vData) {
      if (!vData) return;
      const vId = vData.id || vData.voucherNo || '---';
      const amount = Number(vData.sdg || vData.amountLocal || vData.usd || 0);
      const cur = vData.currency || 'SDG';
      const date = vData.date || '';
      const time = vData.time || '';
      const from = vData.from || '---';
      const to = vData.to || '---';
      const refNo = vData.refNo || vData.mobileTransferNo || '---';
      const bank = vData.bankName || 'بنك الخرطوم';
      const notes = vData.notes || 'سند حركة مالية معتمد';
      const tafqeet = typeof arabicNumberToWords === 'function' ? arabicNumberToWords(amount, cur) : '';
      const hash = generateVoucherHash(vId, date, amount, from, to);

      const text = `🏢 مؤسسة سجانه للأنشطة المتعددة
إشعار سند مالي معتمد ومرحّل (#${vId})
التاريخ: ${date} ${time}
المبلغ: ${amount.toLocaleString()} ${cur}
فقط: ${tafqeet} لا غير
من حساب: ${from}
إلى حساب: ${to}
البنك: ${bank} | رقم التحويلة: ${refNo}
البيان: ${notes}
بصمة التوثيق: ${hash}
تم الاعتماد في قاعدة البيانات MariaDB بنجاح.`;

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
          showToastNotification('📋 ✓ تم نسخ إشعار السند المالي للحافظة بنجاح!');
        }).catch(() => {
          fallbackClipboardCopy(text);
        });
      } else {
        fallbackClipboardCopy(text);
      }
    }
    window.copyReceiptText = copyReceiptText;

    function fallbackClipboardCopy(text) {
      const ta = document.createElement('textarea');
      ta.value = text;
      ta.style.position = 'fixed';
      ta.style.left = '-9999px';
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      ta.remove();
      showToastNotification('📋 ✓ تم نسخ إشعار السند المالي للحافظة بنجاح!');
    }

    // ════════════════════════════════════════════════════════════════
    // نافذة التراجع المرن العائمة (5-Second Floating Undo Buffer Toast)
    // ════════════════════════════════════════════════════════════════
    // 1. مفتاح منع تكرار النقر المزدوج (Idempotency Key Engine)
    // ════════════════════════════════════════════════════════════════
    function generateUUID() {
      if (typeof crypto !== 'undefined' && crypto.randomUUID) {
        return crypto.randomUUID();
      }
      return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        const r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
      });
    }
    window.generateUUID = generateUUID;

    let currentDraftIdempotencyKey = generateUUID();
    let currentReceiptAttachment = null;
    let isSavingInProgress = false;

    // ════════════════════════════════════════════════════════════════
    // 2. نظام إرفاق وتصوير إشعارات البنوك مع القراءة الذكية (Receipt Attachment & Smart OCR)
    // ════════════════════════════════════════════════════════════════
    function triggerDirectReceiptCapture() {
      const sheet = document.getElementById('modalReceiptActionSheet');
      if (sheet) {
        sheet.classList.remove('hidden');
      } else {
        const camInp = document.getElementById('directReceiptCameraInput');
        if (camInp) camInp.click();
      }
    }
    window.triggerDirectReceiptCapture = triggerDirectReceiptCapture;

    function closeReceiptActionSheet() {
      const sheet = document.getElementById('modalReceiptActionSheet');
      if (sheet) sheet.classList.add('hidden');
    }
    window.closeReceiptActionSheet = closeReceiptActionSheet;

    function openAttachedReceiptModal() {
      if (!currentReceiptAttachment) return;
      const modal = document.getElementById('modalReceiptViewer');
      const img = document.getElementById('receiptViewerImg');
      if (modal && img) {
        img.src = currentReceiptAttachment;
        modal.classList.remove('hidden');
      }
    }
    window.openAttachedReceiptModal = openAttachedReceiptModal;

    function removeAttachedReceipt() {
      currentReceiptAttachment = null;
      const card = document.getElementById('receiptAttachmentCard');
      if (card) card.classList.add('hidden');
      showToastNotification('🗑️ تم إزالة المستند المرفق من السند');
    }
    window.removeAttachedReceipt = removeAttachedReceipt;

    function loadTesseractIfNeeded() {
      if (window.Tesseract) return Promise.resolve(window.Tesseract);
      return new Promise((resolve) => {
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
        s.onload = () => resolve(window.Tesseract);
        s.onerror = () => resolve(null);
        document.head.appendChild(s);
      });
    }

    async function handleDirectReceiptSelected(e) {
      const files = e.target.files;
      if (!files || files.length === 0) return;
      const file = files[0];

      triggerHapticPulse();
      showToastNotification('⏳ جارٍ تحميل وقراءة إشعار التحويل البنكي بالذكاء الاصطناعي (OCR)...');

      const reader = new FileReader();
      reader.onload = async function(evt) {
        const dataUrl = evt.target.result;
        currentReceiptAttachment = dataUrl;

        // إظهار بطاقة المرفق ومعاينته في الواجهة فورياً
        const card = document.getElementById('receiptAttachmentCard');
        const thumb = document.getElementById('receiptAttachmentThumb');
        const summary = document.getElementById('receiptAttachmentSummary');
        if (card && thumb) {
          thumb.src = dataUrl;
          card.classList.remove('hidden');
        }

        // محاولة القراءة الذكية عبر OCR
        let recognizedText = '';
        try {
          const tesseract = await loadTesseractIfNeeded();
          if (tesseract) {
            showToastNotification('🔍 جاري التحليل الضوئي وقراءة أرقام الإشعار...');
            const result = await tesseract.recognize(dataUrl, 'ara+eng');
            recognizedText = result?.data?.text || '';
          }
        } catch(err) {
          console.warn('Tesseract OCR error:', err);
        }

        // استخراج وتعبئة البيانات الذكية (المبلغ، رقم التحويلة، البنك، المستفيد)
        parseAndApplyBankReceipt(recognizedText, file.name, dataUrl);
      };
      reader.readAsDataURL(file);
    }
    window.handleDirectReceiptSelected = handleDirectReceiptSelected;

    function parseAndApplyBankReceipt(ocrText, fileName = '', dataUrl = '') {
      ocrText = ocrText || '';
      const textToSearch = (ocrText + ' ' + fileName).toLowerCase();

      // 1. استكشاف البنك (بنكك / بنك الخرطوم، فوري / بنك فيصل، أوكاش / بنك أم درمان)
      let detectedBank = 'بنك الخرطوم';
      if (/فوري|فيصل|faisal/i.test(textToSearch)) {
        detectedBank = 'بنك فيصل الإسلامي';
      } else if (/أوكاش|اوكاش|أم درمان|امدرمان|omdurman/i.test(textToSearch)) {
        detectedBank = 'بنك أم درمان الوطني';
      } else if (/النيلين|nilein/i.test(textToSearch)) {
        detectedBank = 'بنك النيلين';
      } else if (/البركة|baraka/i.test(textToSearch)) {
        detectedBank = 'بنك البركة';
      } else if (/الخليج|gulf/i.test(textToSearch)) {
        detectedBank = 'بنك الخليج';
      }

      // 2. استخراج رقم التحويلة / رقم العملية / المرجع (4 إلى 12 رقم)
      let extractedRef = '';
      const refPatterns = [
        /(?:رقم\s*(?:العملية|المعاملة|المرجع|الإشعار)|الرقم\s*المرجعي|tran(?:saction)?\s*id|ref(?:erence)?\s*(?:no)?)\s*[:#\-]?\s*(\d{4,14})/i,
        /(\b\d{5,12}\b)/
      ];

      for (const pattern of refPatterns) {
        const match = ocrText.match(pattern);
        if (match && match[1]) {
          extractedRef = match[1].trim();
          break;
        }
      }

      if (!extractedRef) {
        const fileNumMatch = fileName.match(/\d{4,12}/);
        if (fileNumMatch) extractedRef = fileNumMatch[0];
      }

      // 3. استخراج المبلغ (الأرقام المقترنة بـ ج.س أو SDG أو المبلغ)
      let extractedAmount = 0;
      const amtPatterns = [
        /(?:المبلغ|القيمة|amount|sdg)\s*[:#\-]?\s*([0-9,]+(?:\.[0-9]{1,2})?)/i,
        /([0-9,]{3,}(?:\.[0-9]{1,2})?)\s*(?:ج\.س|جنيه|sdg)/i,
        /([1-9][0-9]{2,}(?:,[0-9]{3})*)/
      ];

      for (const pattern of amtPatterns) {
        const match = ocrText.match(pattern);
        if (match && match[1]) {
          const rawNum = match[1].replace(/,/g, '');
          const parsed = parseFloat(rawNum);
          if (parsed > 0 && parsed !== parseFloat(extractedRef)) {
            extractedAmount = parsed;
            break;
          }
        }
      }

      // 4. استخراج المستفيد ومطابقته مع دليل الحسابات
      let matchedBeneficiary = '';
      if (window.accountsDB && window.accountsDB.length > 0) {
        for (const acc of window.accountsDB) {
          if (acc && acc.name && acc.name.length > 2) {
            if (ocrText.includes(acc.name)) {
              matchedBeneficiary = acc.name;
              break;
            }
          }
        }
      }

      // 5. تطبيق البيانات المستخرجة في حقول الشاشة فورياً
      if (extractedRef) {
        const ti = document.getElementById('transferInput');
        if (ti) {
          ti.value = extractedRef;
          handleTransferLiveInput(extractedRef);
        }
      }

      if (extractedAmount > 0) {
        const amtInp = document.getElementById('sdgAmount');
        if (amtInp) {
          amtInp.value = extractedAmount;
          if (typeof handleAmountInput === 'function') handleAmountInput();
        }
      }

      const bi = document.getElementById('bankNameInput');
      if (bi) bi.value = detectedBank;

      if (matchedBeneficiary) {
        const toInp = document.getElementById('toSearchInput');
        if (toInp) toInp.value = matchedBeneficiary;
      }

      // تحديث ملخص بطاقة المرفق
      const summary = document.getElementById('receiptAttachmentSummary');
      if (summary) {
        const details = [];
        if (detectedBank) details.push(detectedBank);
        if (extractedAmount > 0) details.push(Number(extractedAmount).toLocaleString() + ' ج.س');
        if (extractedRef) details.push('تحويلة: ' + extractedRef);
        summary.textContent = details.join(' | ') || 'مستند إشعار بنكي';
      }

      playBankerChime();
      triggerHapticPulse();

      const successMsg = `📸 تم إرفاق إشعار (${detectedBank}) وقراءة البيانات بنجاح!\n` +
        (extractedRef ? `• رقم التحويلة: ${extractedRef}\n` : '') +
        (extractedAmount > 0 ? `• المبلغ: ${Number(extractedAmount).toLocaleString()} ج.س\n` : '') +
        `• تم حفظ الصورة كمستند ثبوتي للسند.`;

      showToastNotification(successMsg);
    }
    window.parseAndApplyBankReceipt = parseAndApplyBankReceipt;

    // ════════════════════════════════════════════════════════════════
    // 3. نافذة التراجع المرن العائمة (The 5-Second "Undo" Buffer)
    // ════════════════════════════════════════════════════════════════
    let undoToastTimeout = null;
    let undoCountdownInterval = null;
    let lastSavedVoucherData = null;
    let lastSavedId = null;
    let lastIsEditing = false;
    let lastPreviousData = null;

    function showUndoToast(savedId, voucherPayload, isEditing = false, previousData = null) {
      lastSavedId = savedId;
      lastSavedVoucherData = voucherPayload;
      lastIsEditing = isEditing;
      lastPreviousData = previousData;

      dismissUndoToast(false);

      const container = document.createElement('div');
      container.id = 'undoToastContainer';
      container.style.position = 'fixed';
      container.style.bottom = '78px';
      container.style.left = '50%';
      container.style.transform = 'translateX(-50%) translateY(25px)';
      container.style.opacity = '0';
      container.style.zIndex = '999999';
      container.style.width = 'min(410px, 94vw)';
      container.style.background = 'rgba(15, 23, 42, 0.96)';
      container.style.backdropFilter = 'blur(16px)';
      container.style.webkitBackdropFilter = 'blur(16px)';
      container.style.border = '1.5px solid #38bdf8';
      container.style.borderRadius = '18px';
      container.style.padding = '10px 14px';
      container.style.boxShadow = '0 15px 35px rgba(0,0,0,0.85), 0 0 25px rgba(56, 189, 248, 0.35)';
      container.style.color = '#fff';
      container.style.direction = 'rtl';
      container.style.fontFamily = "'Cairo', 'Tajawal', sans-serif";
      container.style.transition = 'all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1)';

      let remaining = 5;

      container.innerHTML = `
        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
          <div style="display:flex; align-items:center; gap:8px; min-width:0;">
            <div style="width:34px; height:34px; border-radius:10px; background:rgba(56,189,248,0.18); border:1px solid #38bdf8; display:flex; align-items:center; justify-content:center; color:#38bdf8; font-size:1rem; flex-shrink:0;">
              ↩️
            </div>
            <div style="min-width:0; text-align:right;">
              <div style="font-weight:900; font-size:0.86rem; color:#f8fafc; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                تم ترحيل السند <span style="font-family:monospace; color:#38bdf8; font-weight:bold;">#${savedId}</span> بنجاح...
              </div>
              <div style="font-size:0.72rem; color:#94a3b8; font-weight:700;">
                تراجع متاح خلال <span id="undoSecondsCounter" style="color:#38bdf8; font-weight:900; font-family:monospace;">${remaining}</span> ثوانٍ
              </div>
            </div>
          </div>
          <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
            <button type="button" onclick="executeUndoVoucher('${savedId}')" style="background:linear-gradient(135deg, #0284c7, #0369a1); border:1.5px solid #38bdf8; color:#fff; font-weight:900; font-size:0.8rem; padding:6px 14px; border-radius:10px; cursor:pointer; box-shadow:0 3px 12px rgba(2,132,199,0.5); display:flex; align-items:center; gap:4px; active:scale-95;">
              <span>↩️ تراجع Undo</span>
            </button>
            <button type="button" onclick="dismissUndoToast(true)" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#94a3b8; width:28px; height:28px; border-radius:8px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:0.8rem;" title="إغلاق">
              ✕
            </button>
          </div>
        </div>
        <div style="height:3px; background:rgba(255,255,255,0.12); border-radius:2px; margin-top:8px; overflow:hidden;">
          <div id="undoProgressBar" style="height:100%; width:100%; background:linear-gradient(90deg, #38bdf8, #818cf8); transition:width 5000ms linear;"></div>
        </div>
      `;

      document.body.appendChild(container);

      requestAnimationFrame(() => {
        container.style.opacity = '1';
        container.style.transform = 'translateX(-50%) translateY(0)';
        const bar = document.getElementById('undoProgressBar');
        if (bar) {
          requestAnimationFrame(() => {
            bar.style.width = '0%';
          });
        }
      });

      undoCountdownInterval = setInterval(() => {
        remaining--;
        const counterEl = document.getElementById('undoSecondsCounter');
        if (counterEl) counterEl.textContent = String(remaining);
        if (remaining <= 0) {
          clearInterval(undoCountdownInterval);
          undoCountdownInterval = null;
        }
      }, 1000);

      undoToastTimeout = setTimeout(() => {
        dismissUndoToast(true);
        showToastNotification(`🔒 تم التثبيت والاعتماد النهائي للسند (#${savedId}) في السيرفر بنجاح`);
      }, 5200);
    }
    window.showUndoToast = showUndoToast;

    function dismissUndoToast(animated = true) {
      if (undoCountdownInterval) {
        clearInterval(undoCountdownInterval);
        undoCountdownInterval = null;
      }
      if (undoToastTimeout) {
        clearTimeout(undoToastTimeout);
        undoToastTimeout = null;
      }
      const el = document.getElementById('undoToastContainer');
      if (!el) return;
      if (animated) {
        el.style.opacity = '0';
        el.style.transform = 'translateX(-50%) translateY(25px)';
        setTimeout(() => { if (el.parentNode) el.remove(); }, 350);
      } else {
        el.remove();
      }
    }
    window.dismissUndoToast = dismissUndoToast;

    async function executeUndoVoucher(savedId) {
      dismissUndoToast(false);
      const successModal = document.getElementById('professional-success-modal');
      if (successModal) successModal.remove();
      const feedbackModal = document.getElementById('saveFeedbackModal');
      if (feedbackModal) feedbackModal.classList.add('hidden');

      triggerHapticPulse();
      showToastNotification(`⏳ جاري التراجع عن السند #${savedId}...`);

      const payload = lastSavedVoucherData || {};
      const isEdit = lastIsEditing;
      const prevData = lastPreviousData;

      // 1. إذا كان تعديلاً لسند سابق، نسترجع البيانات السابقة الأصلية
      if (isEdit && prevData) {
        const idx = vouchersList.findIndex(v => String(v.id) === String(savedId));
        if (idx !== -1) {
          vouchersList[idx] = prevData;
          try {
            localStorage.setItem('magaiees_vouchers_history', JSON.stringify(vouchersList));
          } catch(e) {}
        }
        try {
          await fetch('mobile-voucher.php?api=save_voucher', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ...prevData, isEditing: true, voucherNo: savedId, id: savedId })
          });
        } catch(e) {}
      } else {
        // 2. إذا كان سنداً جديداً، نقوم بحذفه من قاعدة البيانات وسجلات النظام
        try {
          await fetch('mobile-voucher.php?api=delete_voucher', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ voucherNo: savedId, id: savedId })
          });
        } catch(err) {
          console.warn('Undo DB delete notice:', err);
        }

        const idx = vouchersList.findIndex(v => String(v.id) === String(savedId));
        if (idx !== -1) {
          vouchersList.splice(idx, 1);
          try {
            localStorage.setItem('magaiees_vouchers_history', JSON.stringify(vouchersList));
          } catch(e) {}
        }

        const offIdx = offlineQueue.findIndex(v => String(v.id) === String(savedId));
        if (offIdx !== -1) {
          offlineQueue.splice(offIdx, 1);
          try {
            localStorage.setItem('magaies_mobile_offline_queue', JSON.stringify(offlineQueue));
          } catch(e) {}
        }
      }

      // 3. استرجاع وتعبئة الشاشة بجميع بيانات السند فورياً للتعديل
      if (payload) {
        if (document.getElementById('voucherDate')) document.getElementById('voucherDate').value = payload.date || new Date().toISOString().slice(0, 10);
        if (document.getElementById('sdgAmount')) document.getElementById('sdgAmount').value = payload.sdg || payload.amountLocal || '';
        if (document.getElementById('usdAmount')) document.getElementById('usdAmount').value = payload.usd || payload.amountForeign || '';
        if (document.getElementById('fromSearchInput')) document.getElementById('fromSearchInput').value = payload.from || payload.fromAccountName || '';
        if (document.getElementById('toSearchInput')) document.getElementById('toSearchInput').value = payload.to || payload.toAccountName || '';
        if (document.getElementById('transferInput')) document.getElementById('transferInput').value = payload.refNo || payload.mobileTransferNo || '';
        if (document.getElementById('bankNameInput')) document.getElementById('bankNameInput').value = payload.bankName || '';
        if (document.getElementById('voucherNotes')) document.getElementById('voucherNotes').value = payload.notes || '';
        if (document.getElementById('costCenterInput')) document.getElementById('costCenterInput').value = payload.costCenter || '';
        if (payload.currency) currentCurrency = payload.currency;
        if (payload.payType || payload.paymentMethod) currentPayType = payload.payType || payload.paymentMethod;

        if (typeof updateTafqeet === 'function') updateTafqeet();
        if (typeof updateAccountBalanceBadges === 'function') updateAccountBalanceBadges();
        if (typeof handleTransferLiveInput === 'function') handleTransferLiveInput(payload.refNo || payload.mobileTransferNo || '');
      }

      playBankerChime();
      triggerHapticPulse();
      showToastNotification(`↩️ تم التراجع عن حفظ السند (#${savedId}) وحذفه بنجاح، وبياناته مسترجعة على الشاشة للتعديل.`);
    }
    window.executeUndoVoucher = executeUndoVoucher;

    // ════════════════════════════════════════════════════════════════
    // فحص منع وتكرار التحويل الفوري السريع جداً مع قاعدة البيانات MariaDB وكافة شاشات وسجلات النظام
    // ════════════════════════════════════════════════════════════════
    function isLevenshteinClose(s1, s2) {
      if (s1 === s2) return true;
      const l1 = s1.length, l2 = s2.length;
      if (Math.abs(l1 - l2) > 1) return false;
      let diffs = 0, i = 0, j = 0;
      while (i < l1 && j < l2) {
        if (s1[i] !== s2[j]) {
          diffs++;
          if (diffs > 1) return false;
          if (l1 > l2) i++;
          else if (l2 > l1) j++;
          else { i++; j++; }
        } else {
          i++; j++;
        }
      }
      return true;
    }

    function checkTransferDuplicate(tno, currentVoucherId) {
      if (!tno) return null;
      const rawInput = String(tno).trim();
      const cleanCandidate = rawInput.replace(/\D/g, '') || rawInput.toLowerCase();
      if (!cleanCandidate || cleanCandidate.length < 3) return null;
      const isSimilarConfirmed = (window._confirmedSimilarTransferRef && String(window._confirmedSimilarTransferRef).trim() === rawInput);

      const testMatch = (refNo) => {
        if (!refNo) return null;
        const rawRef = String(refNo).trim();
        const cleanRef = rawRef.replace(/\D/g, '') || rawRef.toLowerCase();
        if (!cleanRef || cleanRef.length < 3) return null;

        // 1. التطابق التام (54016 === 54016 أو 5416 === 5416) -> حظر صارم (hard)
        if (cleanRef === cleanCandidate || rawRef === rawInput) {
          return { match: true, matchType: 'تطابق تام', severity: 'hard' };
        }

        // 2. تطابق آخر 6 أرقام (لأرقام التحويلات الطويلة >= 6) -> حظر صارم (hard)
        if (cleanCandidate.length >= 6 && cleanRef.length >= 6 && cleanCandidate.slice(-6) === cleanRef.slice(-6)) {
          return { match: true, matchType: 'تطابق آخر 6 أرقام', severity: 'hard' };
        }

        // 3. تطابق لاحقة الرقم (Suffix match للأرقام >= 5 خانات) -> حظر صارم (hard)
        if (cleanCandidate.length >= 5 && cleanRef.length >= cleanCandidate.length && cleanRef.endsWith(cleanCandidate)) {
          return { match: true, matchType: 'تطابق لاحقة الرقم', severity: 'hard' };
        }
        if (cleanRef.length >= 5 && cleanCandidate.length >= cleanRef.length && cleanCandidate.endsWith(cleanRef)) {
          return { match: true, matchType: 'تطابق لاحقة الرقم', severity: 'hard' };
        }

        // 4. فحص التشابه الإملائي / الرقمي المتقارب جداً (فرق خانة واحدة typo) -> تحذير ناعم قابل للتأكيد (soft)
        if (!isSimilarConfirmed && cleanCandidate.length >= 4 && Math.abs(cleanCandidate.length - cleanRef.length) <= 1) {
          if (isLevenshteinClose(cleanCandidate, cleanRef)) {
            return { match: true, matchType: 'تشابه رقمي (فرق خانة واحدة)', severity: 'soft' };
          }
        }

        return null;
      };

      // 1. أولاً: فحص أرقام التحويلات النازلة مباشرة من قاعدة البيانات MariaDB (0ms في الذاكرة)
      if (Array.isArray(window.DB_ALL_TRANSFER_REFS) && window.DB_ALL_TRANSFER_REFS.length > 0) {
        for (const item of window.DB_ALL_TRANSFER_REFS) {
          if (!item || !item.ref) continue;
          if (currentVoucherId && (String(item.voucherNo).trim() === String(currentVoucherId).trim())) continue;

          const matchRes = testMatch(item.cleanRef || item.ref);
          if (matchRes && matchRes.match) {
            return {
              isDuplicate: true,
              severity: matchRes.severity || 'hard',
              voucherNo: item.voucherNo || 'سابق',
              ref: item.ref,
              date: item.date || '',
              amount: item.amount || 0,
              bank: item.bank || '',
              from: item.from || '',
              to: item.to || '',
              matchType: matchRes.matchType,
              source: 'قاعدة البيانات (MariaDB)'
            };
          }
        }
      }

      // 2. ثانياً: فحص قائمة السندات الحالية في واجهة الجوال
      if (typeof vouchersList !== 'undefined' && Array.isArray(vouchersList)) {
        for (let v of vouchersList) {
          if (!v) continue;
          if (currentVoucherId && (String(v.id).trim() === String(currentVoucherId).trim() || String(v.voucherNo).trim() === String(currentVoucherId).trim())) continue;
          const refs = [v.refNo, v.transferRef, v.mobileTransferNo].filter(Boolean);
          for (let r of refs) {
            const matchRes = testMatch(r);
            if (matchRes && matchRes.match) {
              return {
                isDuplicate: true,
                severity: matchRes.severity || 'hard',
                voucherNo: v.id || v.voucherNo || 'سابق',
                ref: r,
                date: v.date || '',
                amount: v.sdg || v.amountLocal || 0,
                bank: v.bankName || v.bank || '',
                from: v.from || '',
                to: v.to || '',
                matchType: matchRes.matchType,
                source: 'سجلات الجوال الحالية'
              };
            }
          }
        }
      }

      // 3. ثالثاً: فحص التخزين المحلي عبر كافة مفاتيح النظام (ERP)
      const getTenantKey = window.getTenantKey || ((k) => k);
      const keys = [
        getTenantKey('erp_single_payments_v1'),
        getTenantKey('erp_payments_v1'),
        getTenantKey('erp_receipts_v1'),
        getTenantKey('erp_comprehensive_payments_v1'),
        getTenantKey('erp_comprehensive_receipts_v1'),
        getTenantKey('erp_journal_v1'),
        'magaiees_vouchers_history'
      ];

      for (let key of keys) {
        let vouchers = [];
        try {
          vouchers = JSON.parse(localStorage.getItem(key)) || [];
        } catch(e) { continue; }

        for (let v of vouchers) {
          if (!v || v.deleted) continue;
          if (currentVoucherId && (String(v.id).trim() === String(currentVoucherId).trim() || String(v.voucherNo).trim() === String(currentVoucherId).trim())) continue;

          let matchedRef = null;
          let matchResult = null;

          if (v.bankTransfers && Array.isArray(v.bankTransfers)) {
            for (let bt of v.bankTransfers) {
              const res = testMatch(bt.transferNo);
              if (res) { matchedRef = bt.transferNo; matchResult = res; break; }
            }
          } else if (v.mobileTransferNo && (matchResult = testMatch(v.mobileTransferNo))) {
            matchedRef = v.mobileTransferNo;
          } else if (v.refNo && (matchResult = testMatch(v.refNo))) {
            matchedRef = v.refNo;
          }

          if (!matchedRef && v.items && Array.isArray(v.items)) {
            for (let item of v.items) {
              if (!item) continue;
              if (item.bankTransfers && Array.isArray(item.bankTransfers)) {
                for (let bt of item.bankTransfers) {
                  const res = testMatch(bt.transferNo);
                  if (res) { matchedRef = bt.transferNo; matchResult = res; break; }
                }
              } else if (item.transferNo && (matchResult = testMatch(item.transferNo))) {
                matchedRef = item.transferNo;
                break;
              }
              if (matchedRef) break;
            }
          }

          if (!matchedRef && v.details && Array.isArray(v.details)) {
            for (let det of v.details) {
              if (!det) continue;
              if (det.bankTransfers && Array.isArray(det.bankTransfers)) {
                for (let bt of det.bankTransfers) {
                  const res = testMatch(bt.transferNo);
                  if (res) { matchedRef = bt.transferNo; matchResult = res; break; }
                }
              } else if (det.transferNo && (matchResult = testMatch(det.transferNo))) {
                matchedRef = det.transferNo;
                break;
              }
              if (matchedRef) break;
            }
          }

          if (!matchedRef && v.subTransactions && Array.isArray(v.subTransactions)) {
            for (let sub of v.subTransactions) {
              if (!sub) continue;
              if (sub.transferNo && (matchResult = testMatch(sub.transferNo))) {
                matchedRef = sub.transferNo;
                break;
              }
            }
          }

          if (matchedRef && matchResult) {
            return {
              isDuplicate: true,
              severity: matchResult.severity || 'hard',
              voucherNo: v.id || v.voucherNo || 'سابق',
              ref: matchedRef,
              date: v.date || '',
              amount: v.amountLocal || v.sdg || v.amount || 0,
              bank: v.bankName || v.bank || '',
              from: v.fromAccountName || v.from || '',
              to: v.toAccountName || v.to || '',
              matchType: matchResult.matchType,
              source: 'النظام المحاسبي'
            };
          }
        }
      }
      return null;
    }
    window.checkTransferDuplicate = checkTransferDuplicate;

    // ════════════════════════════════════════════════════════════════
    // نافذة التنبيه اللطيف والمهذب والتحذيري (Polite Modal Functions)
    // ════════════════════════════════════════════════════════════════
    let pendingFocusInputId = null;

    function showPoliteWarningModal(title, message, focusInputId = null, btnText = 'حسناً، فهمت وسأقوم بالمراجعة', theme = 'amber') {
      const modal = document.getElementById('politeWarningModal');
      const card = document.getElementById('politeModalCard');
      const iconWrapper = document.getElementById('politeModalIconWrapper');
      const iconEl = document.getElementById('politeModalIcon');
      const pingEl = document.getElementById('politeModalPing');
      const dotEl = document.getElementById('politeModalDot');
      const titleEl = document.getElementById('politeModalTitle');
      const msgEl = document.getElementById('politeModalMessage');
      const btnEl = document.getElementById('politeModalBtnText');
      const btnContainer = document.getElementById('politeModalBtn');
      pendingFocusInputId = focusInputId;

      const isRed = (theme === 'red' || theme === 'danger');

      if (card) {
        card.className = isRed
          ? 'bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 border-2 border-red-500/80 rounded-3xl p-5 max-w-sm w-full text-center shadow-[0_0_60px_rgba(239,68,68,0.35)] space-y-4 animate-in fade-in zoom-in-95 duration-200'
          : 'bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 border-2 border-amber-500/70 rounded-3xl p-5 max-w-sm w-full text-center shadow-[0_0_50px_rgba(245,158,11,0.25)] space-y-4 animate-in fade-in zoom-in-95 duration-200';
      }

      if (iconWrapper) {
        iconWrapper.className = isRed
          ? 'relative mx-auto w-16 h-16 rounded-2xl bg-red-500/15 border-2 border-red-500/40 flex items-center justify-center shadow-inner'
          : 'relative mx-auto w-16 h-16 rounded-2xl bg-amber-500/15 border-2 border-amber-400/40 flex items-center justify-center shadow-inner';
      }

      if (iconEl) {
        iconEl.className = isRed
          ? 'fa-solid fa-triangle-exclamation text-2xl text-red-400 animate-bounce'
          : 'fa-solid fa-bell text-2xl text-amber-400 animate-bounce';
      }

      if (pingEl) pingEl.className = `animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 ${isRed ? 'bg-red-400' : 'bg-amber-400'}`;
      if (dotEl) dotEl.className = `relative inline-flex rounded-full h-3 w-3 ${isRed ? 'bg-red-500' : 'bg-amber-500'}`;

      if (titleEl) {
        titleEl.textContent = title || (isRed ? 'تحذير تدقيق مالي' : 'تنبيه تدقيق مالي لطيف');
        titleEl.className = `text-base font-black flex items-center justify-center gap-1.5 ${isRed ? 'text-red-400' : 'text-amber-300'}`;
      }

      if (msgEl) {
        msgEl.textContent = message;
        msgEl.className = `text-xs sm:text-sm font-semibold leading-relaxed bg-slate-950/80 border rounded-2xl p-3.5 text-right whitespace-pre-line shadow-inner max-h-52 overflow-y-auto ${isRed ? 'border-red-900/60 text-rose-100' : 'border-slate-800 text-slate-200'}`;
      }

      if (btnEl) btnEl.textContent = btnText;

      if (btnContainer) {
        btnContainer.className = isRed
          ? 'flex-1 py-3 px-4 rounded-xl bg-gradient-to-r from-red-600 via-rose-600 to-red-600 hover:from-red-500 hover:to-rose-500 active:scale-95 text-white font-black text-xs sm:text-sm flex items-center justify-center gap-2 shadow-lg shadow-red-950/50 transition'
          : 'flex-1 py-3 px-4 rounded-xl bg-gradient-to-r from-amber-600 via-amber-500 to-amber-600 hover:from-amber-500 hover:to-amber-400 active:scale-95 text-slate-950 font-black text-xs sm:text-sm flex items-center justify-center gap-2 shadow-lg shadow-amber-950/50 transition';
      }

      if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      if (typeof triggerHapticPulse === 'function') {
        triggerHapticPulse();
      }
    }

    function closePoliteWarningModal() {
      const modal = document.getElementById('politeWarningModal');
      if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
      if (pendingFocusInputId) {
        const el = document.getElementById(pendingFocusInputId);
        if (el) {
          try {
            el.focus();
            el.classList.add('border-amber-400');
            setTimeout(() => el.classList.remove('border-amber-400'), 3000);
          } catch(e) {}
        }
        pendingFocusInputId = null;
      }
    }

    // ════════════════════════════════════════════════════════════════
    // نافذة تأكيد الحفظ والتعديل الجذابة الأنيقة (Save & Edit Feedback)
    // ════════════════════════════════════════════════════════════════
    let saveFeedbackTimer = null;
    function showSaveSuccessModal(mode, vData) {
      const modal = document.getElementById('saveFeedbackModal');
      const card = document.getElementById('saveFeedbackCard');
      const iconBox = document.getElementById('saveFeedbackIconBox');
      const icon = document.getElementById('saveFeedbackIcon');
      const ping = document.getElementById('saveFeedbackPing');
      const dot = document.getElementById('saveFeedbackDot');
      const title = document.getElementById('saveFeedbackTitle');
      const sub = document.getElementById('saveFeedbackSub');
      const btn = document.getElementById('saveFeedbackBtn');
      const vNo = document.getElementById('saveFeedbackVNo');
      const amt = document.getElementById('saveFeedbackAmount');
      const fromEl = document.getElementById('saveFeedbackFrom');
      const toEl = document.getElementById('saveFeedbackTo');

      if (!modal) return;

      const isEdit = (mode === 'edit');

      // نصوص العنوان والوصف
      if (title) {
        title.textContent = isEdit ? '🔄 تم التعديل' : '✨ تم الحفظ';
        title.className = `text-xl font-black flex items-center justify-center gap-1.5 ${isEdit ? 'text-sky-300' : 'text-emerald-300'}`;
      }
      if (sub) {
        sub.textContent = isEdit ? 'تم تحديث وتعديل بيانات الحركة المالية بنجاح' : 'تم حفظ وترحيل سند الحركة المالية بنجاح';
      }

      // الألوان والتنسيقات الفخمة
      if (card) {
        card.className = isEdit
          ? 'bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 border-2 border-sky-500/80 rounded-3xl p-5 max-w-sm w-full text-center space-y-4 shadow-[0_0_55px_rgba(14,165,233,0.35)] animate-in fade-in zoom-in-95 duration-200'
          : 'bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 border-2 border-emerald-500/80 rounded-3xl p-5 max-w-sm w-full text-center space-y-4 shadow-[0_0_55px_rgba(16,185,129,0.35)] animate-in fade-in zoom-in-95 duration-200';
      }

      if (iconBox) {
        iconBox.className = isEdit
          ? 'relative mx-auto w-16 h-16 rounded-2xl bg-sky-500/15 border-2 border-sky-400/40 flex items-center justify-center shadow-inner'
          : 'relative mx-auto w-16 h-16 rounded-2xl bg-emerald-500/15 border-2 border-emerald-400/40 flex items-center justify-center shadow-inner';
      }

      if (icon) {
        icon.className = isEdit
          ? 'fa-solid fa-pen-to-square text-3xl text-sky-400 animate-pulse'
          : 'fa-solid fa-circle-check text-3xl text-emerald-400 animate-bounce';
      }

      if (ping) ping.className = `animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 ${isEdit ? 'bg-sky-400' : 'bg-emerald-400'}`;
      if (dot) dot.className = `relative inline-flex rounded-full h-3 w-3 ${isEdit ? 'bg-sky-500' : 'bg-emerald-500'}`;

      if (btn) {
        btn.className = isEdit
          ? 'w-full py-3 px-4 rounded-xl font-black text-xs sm:text-sm flex items-center justify-center gap-2 shadow-lg transition active:scale-95 bg-gradient-to-r from-sky-600 via-sky-500 to-sky-600 hover:from-sky-500 hover:to-sky-400 text-white shadow-sky-950/50'
          : 'w-full py-3 px-4 rounded-xl font-black text-xs sm:text-sm flex items-center justify-center gap-2 shadow-lg transition active:scale-95 bg-gradient-to-r from-emerald-600 via-emerald-500 to-emerald-600 hover:from-emerald-500 hover:to-emerald-400 text-white shadow-emerald-950/50';
      }

      if (vData) {
        if (vNo) vNo.textContent = '#' + (vData.id || vData.voucherNo || '');
        if (amt) {
          const amtVal = (vData.sdg && vData.sdg > 0) ? `${Number(vData.sdg).toLocaleString()} ج.س` : (vData.usd ? `$${Number(vData.usd).toLocaleString()}` : '');
          amt.textContent = amtVal || '---';
        }
        if (fromEl) fromEl.textContent = vData.from || '---';
        if (toEl) toEl.textContent = vData.to || '---';
      }

      modal.classList.remove('hidden');
      modal.classList.add('flex');

      triggerHapticPulse();
      playBankerChime();

      if (saveFeedbackTimer) clearTimeout(saveFeedbackTimer);
      saveFeedbackTimer = setTimeout(() => {
        closeSaveFeedbackModal();
      }, 2800);
    }

    function closeSaveFeedbackModal() {
      const modal = document.getElementById('saveFeedbackModal');
      if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
      if (saveFeedbackTimer) {
        clearTimeout(saveFeedbackTimer);
        saveFeedbackTimer = null;
      }
    }

    // ════════════════════════════════════════════════════════════════
    // قاعدة بيانات الحسابات المباشرة من MariaDB وذاكرة النظام (حقيقية 100%)
    // ════════════════════════════════════════════════════════════════
    let accountsDB = [];

    // إشعارات واتساب بنكية مستلمة من التطبيق أو الحافظة (حقيقية فقط)
    let whatsappNotificationsDB = [];

    let currentWaLimit = 20;
    let selectedWaReceiptId = null;
    let batchProcessingMode = 'auto'; // 'auto' (هزة ورنة وحفظ تلقائي) or 'manual' (إشعاراً بإشعار)
    let whatsAppQueue = [];
    let currentQueueIndex = 0;

    // السندات المتزامنة الحقيقية من قاعدة بيانات MariaDB وسيرفر الكمبيوتر
    let vouchersList = [];
    let currentIndex = -1;
    let isOfflineMode = false;
    let offlineCounter = 1;
    let offlineQueue = [];
    let currentPayType = 'transfer';
    let currentCurrency = 'SDG';

    // ════════════════════════════════════════════════════════════════
    // نظام الصوت التوليدي البنكي (Banker Chime via Singleton Web Audio API)
    // ════════════════════════════════════════════════════════════════
    function playBankerChime() {
      try {
        const AudioCtxClass = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtxClass) return;
        if (!window._bankerAudioCtx || window._bankerAudioCtx.state === 'closed') {
          window._bankerAudioCtx = new AudioCtxClass();
        }
        const ctx = window._bankerAudioCtx;
        if (ctx.state === 'suspended') {
          ctx.resume().catch(() => {});
        }
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

    function playFeedbackTone(type = 'tap') {
      try {
        playBankerChime();
      } catch(e) {}
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
    // التحكم التلقائي بحجم خط رقم التحويلة والمقارنة الفورية السريعة جداً مع MariaDB
    // ════════════════════════════════════════════════════════════════
    let _transferCheckDebounce = null;

    function handleTransferLiveInput(val) {
      val = (val || '').trim();
      handleTransferInputScale(val);
      _shortTransferAllowed = false;

      const badge = document.getElementById('transferFeedbackBadge');
      const input = document.getElementById('transferInput');
      if (!badge || !input) return;

      if (!val) {
        badge.innerHTML = '';
        input.classList.remove('border-red-500', 'border-amber-500', 'border-emerald-500', 'shadow-[0_0_15px_rgba(239,68,68,0.4)]', 'shadow-[0_0_15px_rgba(16,185,129,0.3)]');
        return;
      }

      const currentVNo = (document.getElementById('currentVoucherDisplay')?.textContent || '').trim();

      // 1. فحص فوري وسريع جداً في الذاكرة مع أرقام MariaDB النازلة (0ms)
      const dup = checkTransferDuplicate(val, currentVNo);
      if (dup) {
        const isSoft = (dup.severity === 'soft');
        if (isSoft) {
          badge.innerHTML = `
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-950/85 text-amber-300 border border-amber-500/50 shadow-sm">
              <i class="fa-solid fa-circle-exclamation text-amber-400"></i>
              <span>⚠️ تشابه مع (${dup.ref}) بالسند <strong>#${dup.voucherNo}</strong></span>
            </span>
          `;
          input.classList.add('border-amber-500');
          input.classList.remove('border-red-500', 'border-emerald-500', 'shadow-[0_0_15px_rgba(239,68,68,0.4)]', 'shadow-[0_0_15px_rgba(16,185,129,0.3)]');
        } else {
          badge.innerHTML = `
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-950/80 text-rose-300 border border-rose-500/50 shadow-sm animate-pulse">
              <i class="fa-solid fa-triangle-exclamation text-rose-400"></i>
              <span>⛔ مكرر بالسند <strong>#${dup.voucherNo}</strong> (${dup.matchType || 'مسجل'})</span>
            </span>
          `;
          input.classList.add('border-red-500', 'shadow-[0_0_15px_rgba(239,68,68,0.4)]');
          input.classList.remove('border-amber-500', 'border-emerald-500', 'shadow-[0_0_15px_rgba(16,185,129,0.3)]');
        }
        return;
      }

      // 2. إذا كان الرقم كافياً (>= 4 أرقام) وغير مكرر
      const digits = val.replace(/\D/g, '');
      if (digits.length >= 4) {
        badge.innerHTML = `
          <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-950/80 text-emerald-300 border border-emerald-500/40 shadow-sm">
            <i class="fa-solid fa-circle-check text-emerald-400"></i>
            <span>✓ رقم متاح وغير مكرر</span>
          </span>
        `;
        input.classList.add('border-emerald-500', 'shadow-[0_0_15px_rgba(16,185,129,0.3)]');
        input.classList.remove('border-red-500', 'border-amber-500', 'shadow-[0_0_15px_rgba(239,68,68,0.4)]');
      } else {
        badge.innerHTML = `
          <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-bold text-amber-400/80">
            <i class="fa-solid fa-circle-info"></i>
            <span>أدخل رقم التحويل كاملاً</span>
          </span>
        `;
        input.classList.remove('border-red-500', 'border-amber-500', 'border-emerald-500', 'shadow-[0_0_15px_rgba(239,68,68,0.4)]', 'shadow-[0_0_15px_rgba(16,185,129,0.3)]');
      }

      // 3. فحص خلفي هادئ غير معطل عبر الـ API مع قاعدة البيانات MariaDB
      if (digits.length >= 3 && !isOfflineMode) {
        clearTimeout(_transferCheckDebounce);
        _transferCheckDebounce = setTimeout(async () => {
          try {
            const res = await fetch(`mobile-voucher.php?api=check_duplicate&ref=${encodeURIComponent(val)}&voucherNo=${encodeURIComponent(currentVNo)}`);
            if (res.ok) {
              const data = await res.json();
              if (data && !data.valid && data.duplicate) {
                const isSoftApi = (data.severity === 'soft');
                if (isSoftApi && window._confirmedSimilarTransferRef === val) return;
                badge.innerHTML = `
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold ${isSoftApi ? 'bg-amber-950/85 text-amber-300 border-amber-500/50' : 'bg-rose-950/80 text-rose-300 border-rose-500/50 animate-pulse'} border shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation ${isSoftApi ? 'text-amber-400' : 'text-rose-400'}"></i>
                    <span>${isSoftApi ? '⚠️ تشابه رقمي مع سند #' : '⛔ مكرر في قاعدة البيانات (سند #'}${data.matchedVoucher || 'سابق'}${isSoftApi ? '' : ')'}</span>
                  </span>
                `;
                if (!isSoftApi) {
                  input.classList.add('border-red-500', 'shadow-[0_0_15px_rgba(239,68,68,0.4)]');
                  input.classList.remove('border-emerald-500', 'shadow-[0_0_15px_rgba(16,185,129,0.3)]');
                }
              }
            }
          } catch(e) {}
        }, 350);
      }
    }
    window.handleTransferLiveInput = handleTransferLiveInput;

    function handleTransferBlur(val) {
      if (_suppressTransferBlur) return;
      val = (val || '').trim();
      if (!val || currentPayType !== 'transfer') return;

      const currentVNo = (document.getElementById('currentVoucherDisplay')?.textContent || '').trim();
      const dup = checkTransferDuplicate(val, currentVNo);
      if (dup) {
        showDuplicateWarningModal(val, dup);
        return;
      }
    }
    window.handleTransferBlur = handleTransferBlur;

    let _transferMeasureCanvas = null;
    function handleTransferInputScale(val) {
      const input = document.getElementById('transferInput');
      if (!input) return;
      const text = String(val !== undefined ? val : (input.value || '')).trim();
      const len = text.length;

      if (len === 0) {
        input.style.setProperty('font-size', '16px', 'important');
        input.style.setProperty('letter-spacing', '0px', 'important');
        return;
      }

      // قياس ذكي وديناميكي لمساحة الخانة الفعلية وموازنة حجم الخط والمسافات بين الأرقام (من 1 إلى 30+ رقم)
      const availWidth = Math.max(125, (input.clientWidth || 190) - 16);
      let targetFontSize = 26;
      let targetSpacing = 0;

      if (len <= 6) {
        // 6 أرقام أو أقل: تكبير فخم وموزون يملأ ارتفاع وعرض الخانة بوضوح تام
        targetFontSize = 26;
        targetSpacing = 2.2;
      } else if (len <= 8) {
        targetFontSize = 23;
        targetSpacing = 1.0;
      } else if (len <= 10) {
        // 10 أرقام: حجم متوازن ومريح يتناسب تماماً مع المساحة
        targetFontSize = 20.5;
        targetSpacing = 0.4;
      } else if (len <= 14) {
        targetFontSize = 17.5;
        targetSpacing = 0;
      } else {
        targetFontSize = 16;
        targetSpacing = -0.4;
      }

      try {
        if (!_transferMeasureCanvas) {
          _transferMeasureCanvas = document.createElement('canvas');
        }
        const ctx = _transferMeasureCanvas.getContext('2d');
        if (ctx) {
          while (targetFontSize > 8.5) {
            ctx.font = `900 ${targetFontSize}px "JetBrains Mono", "Montserrat", monospace`;
            const textWidth = ctx.measureText(text).width + Math.max(0, len - 1) * targetSpacing;
            if (textWidth <= availWidth) break;
            targetFontSize -= 0.5;
            if (targetSpacing > -0.6) targetSpacing -= 0.15;
          }
        }
      } catch (e) {
        if (len > 14) {
          targetFontSize = Math.max(9, Math.floor((availWidth / len) * 1.62));
          targetSpacing = -0.5;
        }
      }

      input.style.setProperty('font-size', `${targetFontSize}px`, 'important');
      input.style.setProperty('letter-spacing', `${targetSpacing.toFixed(2)}px`, 'important');
    }
    window.addEventListener('resize', () => {
      const inp = document.getElementById('transferInput');
      if (inp && inp.value) handleTransferInputScale(inp.value);
    });

    // ════════════════════════════════════════════════════════════════
    // ════════════════════════════════════════════════════════════════
    // تقنية المايك الصوتي الذكي الشامل فائق الدقة (Master AI Voice Engine 3.0)
    // - التقاط عالي النقاء عبر Web Audio API مع إلغاء الصدى والضوضاء (Hardware DSP)
    // - تسجيل متواصل ذكي خالي تماماً من الـ Stuttering لا ينقطع إلا عند السكوت 3 ثوانٍ
    // - مؤشر موجات صوتية تفاعلي حي يعكس مستوى طاقة ونقاء صوت المتكلم
    // - مؤقت صمت دقيق (3.0 ثوانٍ بالتمام) أو إنهاء فوري بنقرة ثانية على المايك
    // ════════════════════════════════════════════════════════════════
    // ════════════════════════════════════════════════════════════════
    // محرك التعرف الصوتي الشامل وتغذية الخانات التلقائية (Master Voice Engine)
    // ════════════════════════════════════════════════════════════════
    let masterVoiceRecognizer = null;
    let masterVoiceVisualizerTimer = null;
    let isMasterVoiceActive = false;
    let isMasterVoiceIntentionalStop = false;
    let masterVoiceSilenceTimer = null;
    let masterVoiceTicker = null;
    let masterVoiceRestartTimer = null;
    let masterVoiceAccumulatedFinal = '';
    let masterVoiceCurrentInterim = '';
    let masterVoiceSpeechStarted = false;
    let masterVoiceLastSpeechTimestamp = 0;
    // 4 ثوانٍ من الصمت التام لإنهاء التسجيل وتفريغ البيانات تلقائياً
    const MASTER_VOICE_SILENCE_MS = 4000;
    let masterVoiceRemainingSilenceMs = MASTER_VOICE_SILENCE_MS;

    function toggleMasterVoiceInput() {
      if (isMasterVoiceActive) {
        // الضغط على المايك مرة أخرى أثناء التسجيل يُنهي التسجيل فوراً ويطبق البيانات
        triggerHapticPulse();
        stopMasterVoiceInput(true);
      } else {
        startMasterVoiceInput();
      }
    }

    function setMasterMicButtonState(active) {
      isMasterVoiceActive = active;
      const btn = document.getElementById('masterMicBtn');
      const icon = document.getElementById('masterMicIcon');
      const ping = document.getElementById('masterMicListeningPing');

      if (!btn) return;

      if (active) {
        btn.classList.remove('bg-gradient-to-r', 'from-cyan-500', 'via-sky-500', 'to-indigo-600');
        btn.classList.add('bg-red-600', 'ring-4', 'ring-red-400', 'scale-105', 'shadow-[0_0_22px_#ef4444]');
        if (icon) {
          icon.className = 'fa-solid fa-microphone-lines text-sm text-white animate-pulse';
        }
        if (ping) ping.classList.remove('hidden');
      } else {
        btn.classList.add('bg-gradient-to-r', 'from-cyan-500', 'via-sky-500', 'to-indigo-600');
        btn.classList.remove('bg-red-600', 'ring-4', 'ring-red-400', 'scale-105', 'shadow-[0_0_22px_#ef4444]');
        if (icon) {
          icon.className = 'fa-solid fa-microphone text-sm text-yellow-300';
        }
        if (ping) ping.classList.add('hidden');
      }
    }

    function showMasterVoiceLiveBanner(show) {
      const banner = document.getElementById('masterVoiceLiveBanner');
      if (!banner) return;
      if (show) {
        banner.classList.remove('hidden');
        const transcriptEl = document.getElementById('masterVoiceLiveTranscript');
        if (transcriptEl) transcriptEl.innerText = 'تحدث الآن بالسند كاملاً... (المبلغ، مِن، إلى، رقم التحويلة، البنك، البيان)';
        const countEl = document.getElementById('voiceSilenceCountdown');
        if (countEl) countEl.innerText = '';
        const badgesEl = document.getElementById('masterVoiceLiveBadges');
        if (badgesEl) {
          badgesEl.innerHTML = '';
          badgesEl.classList.add('hidden');
        }
      } else {
        banner.classList.add('hidden');
      }
    }

    function updateMasterVoiceLiveHUD(text) {
      const transcriptEl = document.getElementById('masterVoiceLiveTranscript');
      if (transcriptEl && text) {
        transcriptEl.innerText = text;
      }
    }

    function updateMasterVoiceSilenceDisplay(isSilent, remainingMs = 0) {
      const countEl = document.getElementById('voiceSilenceCountdown');
      if (!countEl) return;
      if (isSilent && remainingMs > 0) {
        const secs = (remainingMs / 1000).toFixed(1);
        countEl.innerHTML = `<span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/50 text-[10px] font-mono font-bold animate-pulse inline-flex items-center gap-1 shadow-[0_0_8px_rgba(245,158,11,0.3)]"><span>⏳ صمت: إغلاق وتطبيق خلال</span> <span class="text-white text-xs">${secs} ث</span></span>`;
      } else {
        countEl.innerHTML = '';
      }
    }

    // محاكي تفاعلي لموجات الصوت دون حجز عتاد المايكروفون الذي قد يعطل SpeechRecognition في الجوال
    function startWaveBarsAnimation() {
      stopWaveBarsAnimation();
      const bars = [
        document.getElementById('vBar1'),
        document.getElementById('vBar2'),
        document.getElementById('vBar3'),
        document.getElementById('vBar4'),
        document.getElementById('vBar5')
      ];
      if (!bars[0]) return;

      let tick = 0;
      masterVoiceVisualizerTimer = setInterval(() => {
        if (!isMasterVoiceActive) {
          stopWaveBarsAnimation();
          return;
        }
        tick++;
        const isSpeaking = (Date.now() - masterVoiceLastSpeechTimestamp) < 1200;
        const baseHeights = isSpeaking ? [8, 14, 20, 16, 10] : [4, 6, 8, 6, 4];
        const amplitude = isSpeaking ? 10 : 3;

        bars.forEach((bar, idx) => {
          if (bar) {
            const h = Math.max(3, baseHeights[idx] + Math.sin(tick * 0.5 + idx) * amplitude);
            bar.style.height = `${h}px`;
          }
        });
      }, 80);
    }

    function stopWaveBarsAnimation() {
      if (masterVoiceVisualizerTimer) {
        clearInterval(masterVoiceVisualizerTimer);
        masterVoiceVisualizerTimer = null;
      }
      const bars = [
        document.getElementById('vBar1'),
        document.getElementById('vBar2'),
        document.getElementById('vBar3'),
        document.getElementById('vBar4'),
        document.getElementById('vBar5')
      ];
      bars.forEach(bar => {
        if (bar) bar.style.height = '4px';
      });
    }

    function teardownVoiceAudio() {
      stopWaveBarsAnimation();
    }

    function resetMasterVoiceSilenceTimer() {
      if (masterVoiceSilenceTimer) {
        clearTimeout(masterVoiceSilenceTimer);
        masterVoiceSilenceTimer = null;
      }
      if (masterVoiceTicker) {
        clearInterval(masterVoiceTicker);
        masterVoiceTicker = null;
      }

      const totalSpoken = (masterVoiceAccumulatedFinal + ' ' + masterVoiceCurrentInterim).trim();

      // إذا بدأ المستخدم بالتحدث بالفعل، نبدأ مراقبة صمت الـ 4 ثوانٍ بالتمام
      if (totalSpoken.length > 0) {
        masterVoiceSpeechStarted = true;
        masterVoiceLastSpeechTimestamp = Date.now();
        updateMasterVoiceSilenceDisplay(false);

        // عداد الثواني التنازلي المقاس بالزمن المنقضي الفعلي بعد آخر نطق
        masterVoiceTicker = setInterval(() => {
          if (!isMasterVoiceActive || isMasterVoiceIntentionalStop) {
            clearInterval(masterVoiceTicker);
            masterVoiceTicker = null;
            return;
          }
          const elapsed = Date.now() - masterVoiceLastSpeechTimestamp;
          const remaining = Math.max(0, MASTER_VOICE_SILENCE_MS - elapsed);
          if (remaining <= 3500 && remaining > 0) {
            updateMasterVoiceSilenceDisplay(true, remaining);
          } else {
            updateMasterVoiceSilenceDisplay(false);
          }
          if (remaining <= 0) {
            clearInterval(masterVoiceTicker);
            masterVoiceTicker = null;
            if (isMasterVoiceActive && !isMasterVoiceIntentionalStop) {
              console.log('⏱️ مرت 4 ثوانٍ صمت تام دون كلام -> إنهاء التسجيل التلقائي وتطبيق السند.');
              stopMasterVoiceInput(true);
            }
          }
        }, 100);
      } else {
        // إذا لم يبدأ الكلام بعد: ننتظر حتى 12 ثانية كحد أقصى قبل الإيقاف التلقائي
        masterVoiceSilenceTimer = setTimeout(() => {
          if (!masterVoiceSpeechStarted && isMasterVoiceActive) {
            showToastNotification('لم يتم التقاط أي صوت، تم إيقاف المايك.');
            stopMasterVoiceInput(false);
          }
        }, 12000);
      }
    }

    // عرض بطاقات الاستخراج الذكي الحي في الهيدر أثناء نطق المستخدم للكلام
    function updateMasterVoiceLiveBadges(spokenText) {
      const badgesContainer = document.getElementById('masterVoiceLiveBadges');
      if (!badgesContainer) return;
      if (!spokenText || spokenText.trim().length < 3) {
        badgesContainer.classList.add('hidden');
        badgesContainer.innerHTML = '';
        return;
      }

      const p = extractVoiceFinancialEntities(spokenText);
      const badges = [];

      // نوع الدفع
      if (p.payType) {
        const payNames = { 'transfer': 'تحويل', 'cash': 'كاش', 'bank_cheque': 'شيك مصرفي', 'normal_cheque': 'شيك عادي' };
        badges.push(`<span class="px-2 py-0.5 rounded-md bg-amber-900/80 text-amber-200 border border-amber-500/40 font-bold">💳 ${payNames[p.payType] || p.payType}</span>`);
      }

      // مبالغ الدولار والمحلي
      if (p.usdAmount && p.usdAmount > 0) {
        badges.push(`<span class="px-2 py-0.5 rounded-md bg-emerald-900/80 text-emerald-200 border border-emerald-500/40 font-mono font-black">💵 الدولار: $${Number(p.usdAmount).toLocaleString()}</span>`);
      }
      if (p.localAmount && p.localAmount > 0) {
        badges.push(`<span class="px-2 py-0.5 rounded-md bg-sky-900/80 text-sky-200 border border-sky-500/40 font-mono font-black">💰 المحلي: ${Number(p.localAmount).toLocaleString()} ج.س</span>`);
      }

      // من حساب وإلى حساب
      if (p.from) {
        badges.push(`<span class="px-2 py-0.5 rounded-md bg-red-900/80 text-red-200 border border-red-500/40 truncate max-w-[140px]">📤 مِن: ${p.from}</span>`);
      }
      if (p.to) {
        badges.push(`<span class="px-2 py-0.5 rounded-md bg-teal-900/80 text-teal-200 border border-teal-500/40 truncate max-w-[140px]">📥 إلى: ${p.to}</span>`);
      }

      // رقم التحويلة
      if (p.refNo) {
        badges.push(`<span class="px-2 py-0.5 rounded-md bg-amber-900/80 text-amber-200 border border-amber-500/40 font-mono">🔢 التحويلة: #${p.refNo}</span>`);
      }

      // اسم البنك
      if (p.bank) {
        badges.push(`<span class="px-2 py-0.5 rounded-md bg-blue-900/80 text-blue-200 border border-blue-500/40">🏦 البنك: ${p.bank}</span>`);
      }

      // البيان
      if (p.notes) {
        badges.push(`<span class="px-2 py-0.5 rounded-md bg-indigo-900/80 text-indigo-200 border border-indigo-500/40 truncate max-w-[130px]">📝 البيان: ${p.notes}</span>`);
      }

      // أمر الحفظ
      if (p.shouldAutoSave) {
        badges.push(`<span class="px-2 py-0.5 rounded-md bg-green-600 text-white font-bold animate-pulse">💾 حفظ تلقائي</span>`);
      }

      if (badges.length > 0) {
        badgesContainer.innerHTML = badges.join('');
        badgesContainer.classList.remove('hidden');
      } else {
        badgesContainer.classList.add('hidden');
      }
    }

    function startMasterVoiceInput() {
      triggerHapticPulse();
      if (typeof stopFieldVoiceInput === 'function') stopFieldVoiceInput();
      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

      if (!SpeechRecognition) {
        showToastNotification('⚠️ المتصفح لا يدعم التعرف الصوتي المباشر، تم فتح نافذة الإدخال الذكي');
        fallbackMasterVoicePrompt();
        return;
      }

      // تصفير المتغيرات والتسجيل السابق
      masterVoiceAccumulatedFinal = '';
      masterVoiceCurrentInterim = '';
      masterVoiceSpeechStarted = false;
      isMasterVoiceIntentionalStop = false;
      masterVoiceLastSpeechTimestamp = Date.now();

      if (masterVoiceRestartTimer) {
        clearTimeout(masterVoiceRestartTimer);
        masterVoiceRestartTimer = null;
      }

      playBankerChime();
      setMasterMicButtonState(true);
      showMasterVoiceLiveBanner(true);
      startWaveBarsAnimation();

      launchMasterRecognitionSession();
    }

    // تشغيل جلسة استماع نقية ومستمرة لا تنقطع مع وقفات الكلام في أجهزة الجوال
    function launchMasterRecognitionSession() {
      if (!isMasterVoiceActive || isMasterVoiceIntentionalStop) return;

      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!SpeechRecognition) return;

      if (masterVoiceRecognizer) {
        try {
          masterVoiceRecognizer.onstart = null;
          masterVoiceRecognizer.onresult = null;
          masterVoiceRecognizer.onerror = null;
          masterVoiceRecognizer.onend = null;
          masterVoiceRecognizer.abort();
        } catch(e) {}
        masterVoiceRecognizer = null;
      }

      try {
        const recognition = new SpeechRecognition();
        masterVoiceRecognizer = recognition;
        // استخدام ar-SA القياسية لأنها مدعومة بنسبة 100% في كافة هواتف أندرويد وiOS دون أخطاء حزم مفقودة
        recognition.lang = 'ar-SA';
        recognition.continuous = true;
        recognition.interimResults = true;
        recognition.maxAlternatives = 1;

        recognition.onstart = function() {
          if (!masterVoiceSpeechStarted) {
            resetMasterVoiceSilenceTimer();
          }
        };

        recognition.onresult = function(event) {
          let interim = '';
          for (let i = event.resultIndex; i < event.results.length; ++i) {
            const res = event.results[i];
            if (res.isFinal) {
              const textChunk = res[0].transcript.trim();
              if (textChunk) {
                // منع التكرار العارض للكلمات في بعض متصفحات الجوال
                if (!masterVoiceAccumulatedFinal.endsWith(textChunk)) {
                  masterVoiceAccumulatedFinal = (masterVoiceAccumulatedFinal + ' ' + textChunk).trim();
                }
              }
            } else {
              interim += res[0].transcript;
            }
          }
          masterVoiceCurrentInterim = interim;

          const fullText = (masterVoiceAccumulatedFinal + ' ' + masterVoiceCurrentInterim).trim();
          if (fullText) {
            masterVoiceSpeechStarted = true;
            masterVoiceLastSpeechTimestamp = Date.now();
            updateMasterVoiceLiveHUD(fullText);
            updateMasterVoiceLiveBadges(fullText);
            resetMasterVoiceSilenceTimer();
          }
        };

        recognition.onerror = function(event) {
          console.warn('Master Speech Recognition notice:', event.error);
          if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
            alert('⚠️ يرجى السماح بصلاحية المايكروفون في المتصفح لتفعيل التحدث الصوتي.');
            stopMasterVoiceInput(false);
            return;
          }
          // أخطاء no-speech العارضة عند صمت المتكلم يتولاها onend لاستئناف الاستماع بسلاسة
        };

        recognition.onend = function() {
          // دمج أي كلام معلق في interim فوراً لضمان عدم ضياع أي كلمة
          if (masterVoiceCurrentInterim.trim()) {
            const interimChunk = masterVoiceCurrentInterim.trim();
            if (!masterVoiceAccumulatedFinal.endsWith(interimChunk)) {
              masterVoiceAccumulatedFinal = (masterVoiceAccumulatedFinal + ' ' + interimChunk).trim();
            }
            masterVoiceCurrentInterim = '';
          }

          // إذا تم الضغط على إيقاف يدوياً: نخرج فوراً
          if (!isMasterVoiceActive || isMasterVoiceIntentionalStop) {
            return;
          }

          // فحص مؤقت الصمت: هل انقضت 4 ثوانٍ دون كلام؟
          const elapsed = Date.now() - masterVoiceLastSpeechTimestamp;
          if (masterVoiceSpeechStarted && elapsed >= MASTER_VOICE_SILENCE_MS) {
            console.log('⏱️ اكتمل وقت الصمت (4 ثوانٍ) -> إنهاء التسجيل وتفريغ البيانات فوراً.');
            finalizeMasterVoiceInput();
            return;
          }

          // إذا توقف التعرف التلقائي قبل اكتمال مدة الصمت (سلوك طبيعي في أندرويد عند السكون القصير):
          // نستأنف الاستماع فوراً لمواصلة تسجيل الكلام المتواصل
          clearTimeout(masterVoiceRestartTimer);
          masterVoiceRestartTimer = setTimeout(() => {
            if (isMasterVoiceActive && !isMasterVoiceIntentionalStop) {
              launchMasterRecognitionSession();
            }
          }, 80);
        };

        recognition.start();
      } catch (err) {
        console.error('Master speech recognition exception:', err);
        clearTimeout(masterVoiceRestartTimer);
        masterVoiceRestartTimer = setTimeout(() => {
          if (isMasterVoiceActive && !isMasterVoiceIntentionalStop) {
            launchMasterRecognitionSession();
          }
        }, 200);
      }
    }

    function stopMasterVoiceInput(apply = true) {
      if (isMasterVoiceIntentionalStop) return;
      isMasterVoiceIntentionalStop = true;
      isMasterVoiceActive = false;

      teardownVoiceAudio();

      if (masterVoiceSilenceTimer) {
        clearTimeout(masterVoiceSilenceTimer);
        masterVoiceSilenceTimer = null;
      }
      if (masterVoiceTicker) {
        clearInterval(masterVoiceTicker);
        masterVoiceTicker = null;
      }
      if (masterVoiceRestartTimer) {
        clearTimeout(masterVoiceRestartTimer);
        masterVoiceRestartTimer = null;
      }

      if (masterVoiceRecognizer) {
        try {
          masterVoiceRecognizer.onend = null;
          masterVoiceRecognizer.stop();
        } catch(e) {}
        masterVoiceRecognizer = null;
      }

      setMasterMicButtonState(false);
      showMasterVoiceLiveBanner(false);

      if (apply) {
        finalizeMasterVoiceInput();
      }
    }

    function finalizeMasterVoiceInput() {
      setMasterMicButtonState(false);
      showMasterVoiceLiveBanner(false);
      teardownVoiceAudio();

      if (masterVoiceCurrentInterim.trim()) {
        const interimChunk = masterVoiceCurrentInterim.trim();
        if (!masterVoiceAccumulatedFinal.endsWith(interimChunk)) {
          masterVoiceAccumulatedFinal = (masterVoiceAccumulatedFinal + ' ' + interimChunk).trim();
        }
        masterVoiceCurrentInterim = '';
      }

      const finalRecordedText = masterVoiceAccumulatedFinal.trim();
      masterVoiceAccumulatedFinal = '';

      if (finalRecordedText && finalRecordedText.length > 2) {
        showToastNotification('✅ تم اكتمال التسجيل، وجارٍ استخراج وتطبيق بيانات السند...');
        parseAndApplyMasterVoiceInput(finalRecordedText);
      }
    }

    function startVoiceInput(targetField) {
      startMasterVoiceInput();
    }

    function fallbackMasterVoicePrompt() {
      const val = prompt('🎙️ المايك الذكي للشاشة كلها:\nأدخل أو الصق جملة السند الكاملة، مثال:\nتحويل من حساب محمد حسن علي مكي إلى شيرهان أحمد عثمان مبلغ 6500 دولار');
      if (val && val.trim()) {
        parseAndApplyMasterVoiceInput(val.trim());
      }
    }

    // ════════════════════════════════════════════════════════════════
    // معالجة النصوص الذكية واستخراج الكيانات المالية (Arabic Financial NLP Engine 3.0)
    // ════════════════════════════════════════════════════════════════
    function normalizeArabicText(text) {
      if (!text) return '';
      return text.trim()
        .replace(/[\u064B-\u065F\u0670]/g, '') // إزالة علامات التشكيل والحركات
        .replace(/[إأآٱ]/g, 'ا') // توحيد الهمزات والألف
        .replace(/ة/g, 'ه') // توحيد التاء المربوطة والهاء
        .replace(/[ى]/g, 'ي') // توحيد الألف المقصورة والياء
        .replace(/[\s\-_]+/g, ' ')
        .toLowerCase();
    }

    // تنظيف الكلمات الحشوية الشائعة في اللهجة السودانية والعربية
    function cleanFillers(text) {
      if (!text) return '';
      return text
        .replace(/\b(?:مثلا|مثلاً|يعني|ياخي|كدا|كده|يا سيدي|لو سمحت|بالله|عليك الله|يا باشا|يا ريس|لو تكرمت|معليش)\b/gi, ' ')
        .replace(/[\s\-_]+/g, ' ')
        .trim();
    }

    // خوارزمية الذكاء الاصطناعي لتخمين الأسماء والأصوات والألقاب المركبة (Multi-Layer AI Entity Resolution)
    // تدعم الحالات الخاصة مثل: منطوق "أحمد تاج" يقابل في الدليل "أحمد عثمان تاج الدين"
    function findBestAccountMatch(spokenName, accountsList) {
      if (!spokenName || !spokenName.trim()) return null;
      const list = (accountsList && accountsList.length > 0) ? accountsList : (window.accountsDB || []);
      if (!list || list.length === 0) return null;

      // تنظيف الألقاب الشائعة والكلمات الحشوية
      let clean = cleanFillers(spokenName);
      clean = clean.replace(/^(?:العميل|المورد|السيد|الأستاذ|الشيخ|الحاج|حساب|طرف|صديق|الأخ)\s+/g, '').trim();
      const normSpoken = normalizeArabicText(clean);
      if (!normSpoken) return null;

      // 1. تطابق قياسي كامل بعد التوحيد
      const exact = list.find(a => a && a.name && normalizeArabicText(a.name) === normSpoken);
      if (exact) return exact.name;

      // 2. احتواء تسلسلي كامل (Substring)
      const sub = list.find(a => a && a.name && (normalizeArabicText(a.name).includes(normSpoken) || normSpoken.includes(normalizeArabicText(a.name))));
      if (sub) return sub.name;

      // 3. مطابقة الكلمات والألقاب المركبة وتغطية الكلمات المنطوقة (Token Subsequence & Coverage)
      const spokenTokens = normSpoken.split(/\s+/).filter(t => t.length > 1);
      let bestMatch = null;
      let bestScore = -1;

      for (const a of list) {
        if (!a || !a.name) continue;
        const normA = normalizeArabicText(a.name);
        const aTokens = normA.split(/\s+/).filter(t => t.length > 1);

        let matchedSpokenCount = 0;
        let sequenceIndex = -1;
        let inOrder = true;

        for (const st of spokenTokens) {
          const foundIdx = aTokens.findIndex((at) => {
            return at === st || at.startsWith(st) || (st.length >= 3 && at.includes(st));
          });

          if (foundIdx !== -1) {
            matchedSpokenCount++;
            if (foundIdx < sequenceIndex) {
              inOrder = false;
            }
            sequenceIndex = foundIdx;
          }
        }

        const spokenCoverage = matchedSpokenCount / spokenTokens.length;
        const accountCoverage = matchedSpokenCount / aTokens.length;

        // إذا تحققت كافة الكلمات المنطوقة بالكامل (100% تغطية للكلمات المنطوقة)
        if (spokenCoverage === 1.0) {
          const orderBonus = inOrder ? 0.3 : 0.1;
          const score = 0.7 + orderBonus + (accountCoverage * 0.1);
          if (score > bestScore) {
            bestScore = score;
            bestMatch = a.name;
          }
        } else if (spokenCoverage >= 0.5) {
          const score = (spokenCoverage * 0.6) + (accountCoverage * 0.4);
          if (score > bestScore && score >= 0.5) {
            bestScore = score;
            bestMatch = a.name;
          }
        }
      }

      return bestMatch || null;
    }

    // استخراج الكيانات المالية من النص المنطوق بمرونة مطلقة لترتيب الكلمات (شيء قبل شيء)
    // هذا المايك مخصص لتغذية الخانات: طريقة الدفع، المبلغ المحلي، المبلغ دولار، من حساب، إلى حساب، رقم التحويلة، اسم البنك، البيان + أمر الحفظ
    function extractVoiceFinancialEntities(spokenText) {
      if (!spokenText || !spokenText.trim()) {
        return { raw: '', amount: null, localAmount: null, usdAmount: null, primaryCurrency: 'SDG', payType: null, from: null, to: null, refNo: null, bank: null, notes: null, shouldAutoSave: false };
      }

      let norm = spokenText.trim();
      const easternDigits = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
      for (let i = 0; i < 10; i++) {
        norm = norm.split(easternDigits[i]).join(i.toString());
      }
      norm = norm.replace(/[،؛_#]/g, ' ');

      // فحص أمر الحفظ التلقائي في التسجيل
      let shouldAutoSave = false;
      if (/(?:^|\s)(?:احفظ السند|حفظ السند|سجل الحركة|سجل السند|اعتمد|اعتماد|احفظ|حفظ)(?:\s|$)/i.test(norm)) {
        shouldAutoSave = true;
        norm = norm.replace(/(?:^|\s)(?:احفظ السند|حفظ السند|سجل الحركة|سجل السند|اعتمد|اعتماد|احفظ|حفظ)(?:\s|$)/gi, ' ').trim();
      }

      const parsed = {
        raw: spokenText,
        amount: null,
        localAmount: null,
        usdAmount: null,
        primaryCurrency: 'SDG',
        payType: null,
        from: null,
        to: null,
        refNo: null,
        bank: null,
        notes: null,
        shouldAutoSave: shouldAutoSave
      };

      // 1. تحديد طريقة الدفع (تحويلة / كاش / شيك مصرفي / شيك عادي) بمرونة تامة بأي مكان في الجملة
      if (/(?:^|\s)(?:شيك مصرفي|شيك مقاصة|شيك بنكي|شيك مصدق)(?:\s|$)/i.test(norm)) {
        parsed.payType = 'bank_cheque';
      } else if (/(?:^|\s)(?:شيك عادي|شيك عميل|شيك خطي)(?:\s|$)/i.test(norm)) {
        parsed.payType = 'normal_cheque';
      } else if (/(?:^|\s)(?:كاش|نقدا|نقداً|نقد|دفع كاش|الدفع كاش)(?:\s|$)/i.test(norm)) {
        parsed.payType = 'cash';
      } else if (/(?:^|\s)(?:تحويلة|تحويل|تحويلاً|تحويلا|حوالة|تحويل بنكي)(?:\s|$)/i.test(norm)) {
        parsed.payType = 'transfer';
      } else if (/(?:^|\s)شيك(?:\s|$)/i.test(norm)) {
        parsed.payType = 'bank_cheque';
      }

      // 2. العملة الأساسية المنطوقة (دولار مقابل محلي/سوداني)
      if (/(دولار|dollar|\$|امريكي|أمريكي|دولارات|usd)/i.test(norm)) {
        parsed.primaryCurrency = 'USD';
      } else if (/(جنيه|سوداني|محلي|ج\.س|sdg)/i.test(norm)) {
        parsed.primaryCurrency = 'SDG';
      } else {
        parsed.primaryCurrency = 'SDG';
      }

      // 3. اسم البنك بذكاء فائق عبر قائمة البنوك والألقاب والاختصارات الشائعة
      const knownBanks = [
        { name: 'بنك فيصل الإسلامي', aliases: ['بنك فيصل الإسلامي', 'بنك فيصل الاسلامي', 'بنك فيصل', 'فيصل الإسلامي', 'فوري', 'فيصل'] },
        { name: 'بنك الخرطوم', aliases: ['بنك الخرطوم', 'بنكك', 'خرطوم'] },
        { name: 'بنك أم درمان الوطني', aliases: ['بنك أم درمان الوطني', 'بنك ام درمان الوطني', 'بنك أم درمان', 'بنك امدرمان', 'أم درمان الوطني', 'أمدرمان', 'امدرمان', 'أوكاش', 'اوكاش'] },
        { name: 'بنك النيلين', aliases: ['بنك النيلين', 'النيلين'] },
        { name: 'بنك الخليج', aliases: ['بنك الخليج', 'الخليج'] },
        { name: 'بنك البركة', aliases: ['بنك البركة', 'البركة'] },
        { name: 'بنك العمال الوطني', aliases: ['بنك العمال الوطني', 'بنك العمال'] },
        { name: 'بنك التضامن الإسلامي', aliases: ['بنك التضامن الإسلامي', 'بنك التضامن', 'التضامن'] },
        { name: 'بنك المال المتحد', aliases: ['بنك المال المتحد', 'المال المتحد', 'يو سي بي'] },
        { name: 'مصرف الساحل والصحراء', aliases: ['مصرف الساحل والصحراء', 'الساحل والصحراء'] },
        { name: 'بنك الثروة الحيوانية', aliases: ['بنك الثروة الحيوانية', 'الثروة الحيوانية'] },
        { name: 'البنك الفرنسي السوداني', aliases: ['البنك الفرنسي السوداني', 'البنك الفرنسي'] },
        { name: 'بنك النيل', aliases: ['بنك النيل', 'النيل'] }
      ];

      for (const b of knownBanks) {
        for (const alias of b.aliases) {
          const escaped = alias.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
          const re = new RegExp('(?:^|\\s)' + escaped + '(?:\\s|$)', 'i');
          if (re.test(norm)) {
            parsed.bank = b.name;
            break;
          }
        }
        if (parsed.bank) break;
      }
      if (!parsed.bank) {
        const genericBank = norm.match(/(?:اسم البنك|البنك|بنك)\s*[:#-]?\s*([^\s]+(?:\s+[^\s]+){0,2})/i);
        if (genericBank && genericBank[1] && !/(?:من|إلى|الى|مبلغ|المبلغ|رقم|بيان|البيان|دولار|جنيه|سوداني|تحويلة|كاش|شيك)/.test(genericBank[1])) {
          parsed.bank = genericBank[1].trim();
        }
      }

      // Lookahead شامل لمنع أي حقل من التهام الكلمات المفتاحية للحقول الأخرى بأي ترتيب
      const anyFieldLookahead = '(?=(?:\\s+(?:من حساب|مِن حساب|من طرف|مِن طرف|من|مِن|إلى حساب|الى حساب|لحساب|لـ حساب|إلى طرف|الى طرف|لصالح حساب|لصالح|إيداع لحساب|إيداع في حساب|المستفيد|إلى|الى|لـ|مبلغ|المبلغ|بمبلغ|قيمة|بقيمة|رقم التحويلة|رقم تحويلة|تحويلة رقم|رقم التحويل|تحويل رقم|التحويلة رقم|التحويلة|تحويلة|رقم الشيك|رقم شيك|شيك رقم|الشيك رقم|الشيك|إشعار رقم|اشعار رقم|رقم الإشعار|رقم الاشعار|مرجع رقم|المرجع|مرجع|رقم العملية|العملية رقم|رقم\\s*\\d+|اسم البنك|البنك|بنكك|أوكاش|اوكاش|فوري|بنك|بيان|البيان|ملاحظات|ملاحظة|عبارة عن|شرح|بخصوص|تحويلة|تحويل|تحويلاً|تحويلا|كاش|نقدا|نقداً|شيك مصرفي|شيك عادي|شيك|احفظ السند|حفظ السند|سجل الحركة|سجل السند|اعتمد|اعتماد|احفظ|حفظ|\\d+(?:[.,]\\d+)?\\s*(?:ألف|الف|آلاف|مليون|ملايين|k|m)?\\s*(?:دولار|دولارات|usd|\\$|جنيه|سوداني|محلي|ج\\.س)|دولار|جنيه|سوداني))|$)';

      // 4. استخراج رقم التحويلة أو رقم الشيك
      const refPrefix = '(?:رقم التحويلة|رقم تحويلة|تحويلة رقم|رقم التحويل|تحويل رقم|التحويلة رقم|التحويلة|تحويلة|رقم الشيك|رقم شيك|شيك رقم|الشيك رقم|الشيك|إشعار رقم|اشعار رقم|رقم الإشعار|رقم الاشعار|مرجع رقم|المرجع|مرجع|رقم العملية|العملية رقم|رقم|الرقم)\\s*[:#-]?\\s*';
      const refSpacedMatch = norm.match(new RegExp(refPrefix + '((?:\\d+\\s*)+)' + anyFieldLookahead, 'i'));
      if (refSpacedMatch && refSpacedMatch[1]) {
        parsed.refNo = refSpacedMatch[1].replace(/\s+/g, '').trim();
      } else {
        const refMatch = norm.match(new RegExp(refPrefix + '([0-9a-zA-Z]+)', 'i'));
        if (refMatch && refMatch[1] && refMatch[1] !== 'كذا') {
          parsed.refNo = refMatch[1].trim();
        } else {
          const refWordsMatch = norm.match(new RegExp(refPrefix + '([^\\d]+?)' + anyFieldLookahead, 'i'));
          if (refWordsMatch && refWordsMatch[1]) {
            const spokenNum = parseArabicSpokenNumber(refWordsMatch[1]);
            if (spokenNum > 0) parsed.refNo = String(spokenNum);
          }
        }
      }

      // إذا ورد رقم تحويلة أو اسم بنك ولم يحدد نوع الدفع بعد
      if ((parsed.refNo || parsed.bank) && !parsed.payType) {
        if (/شيك/i.test(norm)) parsed.payType = 'bank_cheque';
        else parsed.payType = 'transfer';
      }

      // 5. استخراج البيان (Description / Notes)
      const notesMatch = norm.match(new RegExp('(?:^|\\s)(?:البيان|بيان|ملاحظات|ملاحظة|عبارة عن|شرح|بخصوص)\\s*[:#-]?\\s*(.+?)' + anyFieldLookahead, 'i'));
      if (notesMatch && notesMatch[1]) {
        let cleanNotes = cleanFillers(notesMatch[1]).trim();
        cleanNotes = cleanNotes.replace(/\b(?:احفظ السند|حفظ السند|سجل الحركة|سجل السند|اعتمد|اعتماد|احفظ|حفظ)\b/gi, '').trim();
        parsed.notes = cleanNotes;
      }

      // 6. استخراج المبالغ المالية (بالدولار وبالمحلي)
      const usdMatch = norm.match(/(?:مبلغ|المبلغ|بقيمة|قيمة)?\s*([0-9]+(?:[.,][0-9]+)?)\s*(ألف|الف|آلاف|مليون|ملايين|k|m)?\s*(?:دولار|دولارات|usd|\$)/i) ||
                       norm.match(/(?:دولار|دولارات|usd|\$)\s*([0-9]+(?:[.,][0-9]+)?)\s*(ألف|الف|آلاف|مليون|ملايين|k|m)?/i);
      if (usdMatch) {
        let val = parseFloat(usdMatch[1].replace(/,/g, ''));
        const mult = (usdMatch[2] || '').toLowerCase();
        if (/(مليون|ملايين|m)/.test(mult)) val *= 1000000;
        else if (/(ألف|الف|آلاف|k)/.test(mult) && val < 1000) val *= 1000;
        parsed.usdAmount = val;
        parsed.amount = val;
        parsed.primaryCurrency = 'USD';
      }

      const sdgMatch = norm.match(/(?:مبلغ|المبلغ|بقيمة|قيمة)?\s*([0-9]+(?:[.,][0-9]+)?)\s*(ألف|الف|آلاف|مليون|ملايين|k|m)?\s*(?:جنيه|سوداني|محلي|ج\.س)/i) ||
                       norm.match(/(?:جنيه|سوداني|محلي|ج\.س)\s*([0-9]+(?:[.,][0-9]+)?)\s*(ألف|الف|آلاف|مليون|ملايين|k|m)?/i);
      if (sdgMatch) {
        let val = parseFloat(sdgMatch[1].replace(/,/g, ''));
        const mult = (sdgMatch[2] || '').toLowerCase();
        if (/(مليون|ملايين|m)/.test(mult)) val *= 1000000;
        else if (/(ألف|الف|آلاف|k)/.test(mult) && val < 1000) val *= 1000;
        parsed.localAmount = val;
        if (!parsed.usdAmount) {
          parsed.amount = val;
          parsed.primaryCurrency = 'SDG';
        }
      }

      if (!parsed.amount) {
        const generalAmtMatch = norm.match(/(?:مبلغ|المبلغ|بقيمة|قيمة)\s*[:#-]?\s*([0-9]+(?:[.,][0-9]+)?)\s*(ألف|الف|آلاف|مليون|ملايين|k|m)?/i);
        if (generalAmtMatch && (!parsed.refNo || generalAmtMatch[1] !== parsed.refNo)) {
          let val = parseFloat(generalAmtMatch[1].replace(/,/g, ''));
          const mult = (generalAmtMatch[2] || '').toLowerCase();
          if (/(مليون|ملايين|m)/.test(mult)) val *= 1000000;
          else if (/(ألف|الف|آلاف|k)/.test(mult) && val < 1000) val *= 1000;
          parsed.amount = val;
          if (parsed.primaryCurrency === 'USD') parsed.usdAmount = val;
          else parsed.localAmount = val;
        } else {
          const spokenAmtMatch = norm.match(new RegExp('(?:مبلغ|المبلغ|بقيمة|قيمة)\\s+([^\\d]+?)' + anyFieldLookahead, 'i'));
          if (spokenAmtMatch && spokenAmtMatch[1]) {
            const spokenNum = parseArabicSpokenNumber(spokenAmtMatch[1]);
            if (spokenNum > 0) {
              parsed.amount = spokenNum;
              if (parsed.primaryCurrency === 'USD') parsed.usdAmount = spokenNum;
              else parsed.localAmount = spokenNum;
            }
          }
        }
      }

      // 7. استخراج من حساب (From Account) - حرية تامة للترتيب
      const fromMatch = norm.match(new RegExp('(?:^|\\s)(?:من حساب|مِن حساب|من طرف|مِن طرف|صرف من حساب|صرف من|خصم من حساب|خصم من|من العميل|من السيد|من الأستاذ|من الشيخ|من الأخ|من|مِن)\\s+(.+?)' + anyFieldLookahead, 'i'));
      if (fromMatch && fromMatch[1]) {
        let fromClean = cleanFillers(fromMatch[1]).trim();
        fromClean = fromClean.replace(/^(?:حساب|العميل|السيد|الأستاذ|الشيخ|الحاج|طرف|الأخ)\s+/g, '').trim();
        fromClean = fromClean.replace(/\b(?:تحويلا|تحويلاً|تحويل|تحويلة|كاش|نقدا|نقداً|شيك مصرفي|شيك عادي|شيك|بنكك|فوري|أوكاش|اوكاش)\b/gi, '').trim();
        if (fromClean && fromClean !== 'كذا') parsed.from = fromClean;
      }

      // 8. استخراج إلى حساب (To Account) - حرية تامة للترتيب
      const toMatch = norm.match(new RegExp('(?:^|\\s)(?:إلى حساب|الى حساب|إلى طرف|الى طرف|لحساب|لـ حساب|لصالح حساب|لصالح|إيداع لحساب|إيداع في حساب|المستفيد|إلى|الى|لـ)\\s+(.+?)' + anyFieldLookahead, 'i'));
      if (toMatch && toMatch[1]) {
        let toClean = cleanFillers(toMatch[1]).trim();
        toClean = toClean.replace(/^(?:حساب|العميل|السيد|الأستاذ|الشيخ|الحاج|طرف|الأخ)\s+/g, '').trim();
        toClean = toClean.replace(/\b(?:تحويلا|تحويلاً|تحويل|تحويلة|كاش|نقدا|نقداً|شيك مصرفي|شيك عادي|شيك|بنكك|فوري|أوكاش|اوكاش)\b/gi, '').trim();
        if (toClean && toClean !== 'كذا') parsed.to = toClean;
      }

      // الحساب التبادلي للعملتين
      const rateEl = document.getElementById('usdRate');
      const currentRate = parseFloat(rateEl ? rateEl.value : 0) || 2650;
      if (parsed.usdAmount && !parsed.localAmount && currentRate > 0) {
        parsed.localAmount = Math.round(parsed.usdAmount * currentRate);
      } else if (parsed.localAmount && !parsed.usdAmount && currentRate > 0) {
        parsed.usdAmount = parseFloat((parsed.localAmount / currentRate).toFixed(2));
      }

      return parsed;
    }

    // تطبيق كل ما تم استخراجه على شاشة السند فوراً وتغذية الخانات مع الالتزام التام بالأسماء الموجودة فقط
    function parseAndApplyMasterVoiceInput(spokenText) {
      if (!spokenText || !spokenText.trim()) return;

      playBankerChime();
      triggerHapticPulse();

      const parsed = extractVoiceFinancialEntities(spokenText);
      const updatedHighlights = [];

      function highlightField(elementId) {
        const el = document.getElementById(elementId);
        if (el) {
          el.classList.add('voice-field-glow');
          setTimeout(() => el.classList.remove('voice-field-glow'), 2500);
        }
      }

      // 0: اختيار نوع الدفع تلقائياً (تحويلة / كاش / شيك مصرفي / شيك عادي)
      if (parsed.payType) {
        setPaymentType(parsed.payType);
        highlightField('btn-' + parsed.payType);
        const payTypeNames = {
          'transfer': 'تحويل',
          'cash': 'كاش',
          'bank_cheque': 'شيك مصرفي',
          'normal_cheque': 'شيك عادي'
        };
        updatedHighlights.push(`💳 نوع الدفع: ${payTypeNames[parsed.payType] || parsed.payType}`);
      }

      const rateInput = document.getElementById('usdRate');
      const rate = parseFloat(rateInput ? rateInput.value : 0) || 2650;

      // 1 & 2: تغذية المبلغ المحلي والمبلغ دولار (مع التحويل التبادلي الذكي)
      let finalUsd = parsed.usdAmount;
      let finalLocal = parsed.localAmount;

      if (finalUsd && !finalLocal && rate > 0) {
        finalLocal = Math.round(finalUsd * rate);
      } else if (finalLocal && !finalUsd && rate > 0) {
        finalUsd = parseFloat((finalLocal / rate).toFixed(2));
      } else if (!finalUsd && !finalLocal && parsed.amount && rate > 0) {
        if (parsed.primaryCurrency === 'USD') {
          finalUsd = parsed.amount;
          finalLocal = Math.round(finalUsd * rate);
        } else {
          finalLocal = parsed.amount;
          finalUsd = parseFloat((finalLocal / rate).toFixed(2));
        }
      }

      if (finalUsd || finalLocal) {
        const usdInp = document.getElementById('usdAmount');
        const sdgInp = document.getElementById('sdgAmount');

        if (finalUsd && usdInp) {
          usdInp.value = formatNumberWithConfig(finalUsd, usdNumberConfig);
          highlightField('usdAmount');
          updatedHighlights.push(`💵 الدولار: ${formatNumberWithConfig(finalUsd, usdNumberConfig)} $`);
        }

        if (finalLocal && sdgInp) {
          sdgInp.value = formatNumberWithConfig(finalLocal, sdgNumberConfig);
          highlightField('sdgAmount');
          updatedHighlights.push(`💰 المحلي: ${formatNumberWithConfig(finalLocal, sdgNumberConfig)} ج.س`);
        }

        const primarySelect = document.getElementById('primaryCurrencySelect');
        if (primarySelect) {
          primarySelect.value = parsed.primaryCurrency === 'USD' ? 'USD' : 'SDG';
        }
        selectCurrency(parsed.primaryCurrency === 'USD' ? 'USD' : 'SDG', false);
        updateTafqeet();
      }

      // 3: تغذية (مِن حساب) - شرط جوهري: الفلترة والمطابقة من الأسماء الموجودة فقط في النظام
      if (parsed.from) {
        const finalFromName = findBestAccountMatch(parsed.from, accountsDB);
        if (finalFromName) {
          selectAcc('from', finalFromName);
          highlightField('fromSearchInput');
          updatedHighlights.push(`📤 مِن: ${finalFromName}`);
        } else {
          const fromInp = document.getElementById('fromSearchInput');
          if (fromInp) {
            fromInp.value = '';
            fromInp.classList.add('border-red-500');
          }
          showToastNotification(`⚠️ الحساب المنطوق (مِن: "${parsed.from}") غير مسجل في دليل الحسابات! يرجى اختيار حساب موجود فقط.`);
        }
      }

      // 4: تغذية (إلى حساب) - شرط جوهري: الفلترة والمطابقة من الأسماء الموجودة فقط في النظام
      if (parsed.to) {
        const finalToName = findBestAccountMatch(parsed.to, accountsDB);
        if (finalToName) {
          selectAcc('to', finalToName);
          highlightField('toSearchInput');
          updatedHighlights.push(`📥 إلى: ${finalToName}`);
        } else {
          const toInp = document.getElementById('toSearchInput');
          if (toInp) {
            toInp.value = '';
            toInp.classList.add('border-red-500');
          }
          showToastNotification(`⚠️ الحساب المنطوق (إلى: "${parsed.to}") غير مسجل في دليل الحسابات! يرجى اختيار حساب موجود فقط.`);
        }
      }

      // 5: تغذية (رقم التحويلة / رقم الشيك)
      if (parsed.refNo) {
        const ti = document.getElementById('transferInput');
        if (ti) {
          ti.value = parsed.refNo;
          handleTransferInputScale(parsed.refNo);
          checkDuplicateTransferLive(parsed.refNo);
          highlightField('transferInput');
          const isCheque = (parsed.payType && parsed.payType.includes('cheque')) || (typeof currentPayType !== 'undefined' && currentPayType && currentPayType.includes('cheque'));
          const refWord = isCheque ? 'رقم الشيك' : 'التحويلة';
          updatedHighlights.push(`🔢 ${refWord}: #${parsed.refNo}`);
        }
      }

      // 6: تغذية (اسم البنك)
      if (parsed.bank) {
        const bi = document.getElementById('bankNameInput');
        if (bi) {
          bi.value = parsed.bank;
          highlightField('bankNameInput');
          const isCheque = (parsed.payType && parsed.payType.includes('cheque')) || (typeof currentPayType !== 'undefined' && currentPayType && currentPayType.includes('cheque'));
          const bankWord = isCheque ? 'بنك الشيك' : 'البنك';
          updatedHighlights.push(`🏦 ${bankWord}: ${parsed.bank}`);
        }
      }

      // 7: تغذية (البيان)
      if (parsed.notes) {
        const ni = document.getElementById('voucherNotes');
        if (ni) {
          ni.value = parsed.notes;
          highlightField('voucherNotes');
          updatedHighlights.push(`📝 البيان: ${parsed.notes}`);
        }
      }

      // التنفيذ التلقائي أو إشعار الجاهزية للحفظ
      if (parsed.shouldAutoSave) {
        showToastNotification('💾 تم تفريغ كافة البيانات بنجاح، وجارٍ حفظ السند تلقائياً...');
        setTimeout(() => {
          if (typeof handleQuickCheckSave === 'function') {
            handleQuickCheckSave();
          } else if (typeof handleSave === 'function') {
            handleSave(true);
          }
        }, 500);
      } else {
        if (updatedHighlights.length > 0) {
          showToastNotification(`🎙️ تم التعرف الذكي وتغذية السند ونوع الدفع بنجاح (جاهز للحفظ):\n` + updatedHighlights.join(' | '));
        } else {
          showToastNotification(`🎙️ تم سماع النص: "${spokenText}"`);
        }
      }
    }

    function parseArabicSpokenNumber(text) {
      if (!text) return 0;
      let str = text.trim();
      const easternDigits = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
      for (let i = 0; i < 10; i++) {
        str = str.split(easternDigits[i]).join(i.toString());
      }
      str = str.replace(/,/g, '');

      // الأرقام المباشرة المصحوبة بكلمة ألف أو مليون
      const mixedMatch = str.match(/(\d+(?:\.\d+)?)\s*(ألف|الف|آلاف|مليون|ملايين|k|m)?/i);
      if (mixedMatch && mixedMatch[1]) {
        let base = parseFloat(mixedMatch[1]);
        const multiplier = mixedMatch[2] ? mixedMatch[2].toLowerCase() : '';
        if (/مليون|ملايين|m/.test(multiplier)) base *= 1000000;
        else if (/ألف|الف|آلاف|k/.test(multiplier) && base < 1000) base *= 1000;
        return base;
      }

      const ones = {
        'صفر': 0, 'واحد': 1, 'واحدة': 1, 'اثنين': 2, 'اثنان': 2, 'اثنتين': 2, 'ثلاثة': 3, 'ثلاث': 3,
        'أربعة': 4, 'اربعة': 4, 'أربع': 4, 'اربع': 4, 'خمسة': 5, 'خمس': 5, 'ستة': 6, 'ست': 6,
        'سبعة': 7, 'سبع': 7, 'ثمانية': 8, 'ثمان': 8, 'تسعة': 9, 'تسع': 9, 'عشرة': 10, 'عشر': 10,
        'أحد عشر': 11, 'احد عشر': 11, 'إحدى عشرة': 11, 'اثنا عشر': 12, 'اثني عشر': 12, 'اثنتا عشرة': 12, 'ثلاثة عشر': 13,
        'أربعة عشر': 14, 'خمسة عشر': 15, 'ستة عشر': 16, 'سبعة عشر': 17, 'ثمانية عشر': 18, 'تسعة عشر': 19
      };
      const tens = {
        'عشرون': 20, 'عشرين': 20, 'ثلاثون': 30, 'ثلاثين': 30, 'أربعون': 40, 'اربعين': 40,
        'خمسون': 50, 'خمسين': 50, 'ستون': 60, 'ستين': 60, 'سبعون': 70, 'سبعين': 70,
        'ثمانون': 80, 'ثمانين': 80, 'تسعون': 90, 'تسعين': 90
      };
      const hundreds = {
        'مائة': 100, 'مئة': 100, 'ميه': 100, 'مية': 100, 'مائتان': 200, 'مئتان': 200, 'مائتين': 200, 'مئتين': 200, 'ميتين': 200,
        'ثلاثمائة': 300, 'ثلاثمئة': 300, 'تلتمية': 300, 'تلتميه': 300, 'أربعمائة': 400, 'اربعمئة': 400, 'ربعمية': 400, 'ربعميه': 400,
        'خمسمائة': 500, 'خمسمئة': 500, 'خمسمية': 500, 'خمسميه': 500,
        'ستمائة': 600, 'ستمئة': 600, 'ستمية': 600, 'ستميه': 600,
        'سبعمائة': 700, 'سبعمئة': 700, 'سبعمية': 700, 'سبعميه': 700,
        'ثمانمائة': 800, 'ثمانمئة': 800, 'تمنمية': 800, 'تمنميه': 800,
        'تسعمائة': 900, 'تسعمئة': 900, 'تسعمية': 900, 'تسعميه': 900
      };

      function parseChunk(c) {
        if (!c) return 0;
        let s = 0;
        const tokens = c.replace(/و/g, ' ').split(/\s+/).filter(Boolean);
        for (const t of tokens) {
          if (hundreds[t]) s += hundreds[t];
          else if (tens[t]) s += tens[t];
          else if (ones[t]) s += ones[t];
          else if (/^\d+$/.test(t)) s += parseInt(t, 10);
        }
        return s;
      }

      let total = 0;
      if (/مليون|ملايين|مليونين/.test(str)) {
        const parts = str.split(/مليون|ملايين|مليونين/);
        const m = /مليونين/.test(str) ? 2 : (parseChunk(parts[0]) || 1);
        total += m * 1000000;
        str = parts[1] || '';
      }
      if (/ألف|الف|آلاف|ألفين|الفين/.test(str)) {
        const parts = str.split(/ألف|الف|آلاف|ألفين|الفين/);
        const k = /ألفين|الفين/.test(str) ? 2 : (parseChunk(parts[0]) || 1);
        total += k * 1000;
        str = parts[1] || '';
      }
      total += parseChunk(str);
      return total;
    }

    function fallbackManualVoiceInput(targetField) {
      fallbackMasterVoicePrompt();
    }

    // ════════════════════════════════════════════════════════════════
    // المايكات الصغيرة لكل حقل: كل مايك يسجّل في حقله فقط
    // (من / إلى / المبلغ المحلي / الدولار / رقم التحويلة)
    // المايك الرئيسي في الأعلى يبقى للشاشة كاملة
    // ════════════════════════════════════════════════════════════════
    let fieldVoiceRecognizer = null;
    let fieldVoiceBtn = null;
    let fieldVoiceTarget = null;
    let fieldVoiceGotResult = false;

    const FIELD_VOICE_LABELS = { from: 'مِن حساب', to: 'إلى حساب', sdg: 'المبلغ المحلي', usd: 'المبلغ بالدولار', transfer: 'رقم التحويلة', bank: 'اسم البنك' };
    const FIELD_VOICE_INPUTS = { from: 'fromSearchInput', to: 'toSearchInput', sdg: 'sdgAmount', usd: 'usdAmount', transfer: 'transferInput', bank: 'bankNameInput' };

    // تحويل الأرقام المنطوقة رقماً رقماً (خمسة أربعة صفر...) إلى سلسلة أرقام لرقم التحويلة
    function spokenToDigitString(text) {
      let s = String(text || '');
      const eastern = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
      for (let i = 0; i < 10; i++) s = s.split(eastern[i]).join(String(i));
      const words = {
        'صفر': '0', 'زيرو': '0', 'واحد': '1', 'واحدة': '1', 'اثنين': '2', 'اتنين': '2', 'اثنان': '2', 'إثنين': '2',
        'ثلاثة': '3', 'ثلاث': '3', 'تلاتة': '3', 'تلاته': '3', 'تلات': '3', 'أربعة': '4', 'اربعة': '4', 'أربع': '4', 'اربع': '4', 'اربعه': '4',
        'خمسة': '5', 'خمس': '5', 'خمسه': '5', 'ستة': '6', 'ست': '6', 'سته': '6', 'سبعة': '7', 'سبع': '7', 'سبعه': '7',
        'ثمانية': '8', 'ثمان': '8', 'تمانية': '8', 'تمنية': '8', 'تمانيه': '8', 'تسعة': '9', 'تسع': '9', 'تسعه': '9'
      };
      let out = '';
      s.replace(/[،,.\-_/]/g, ' ').split(/\s+/).filter(Boolean).forEach(tok => {
        let t = tok;
        if (/^\d+$/.test(t)) { out += t; return; }
        if (!words[t] && t.length > 1 && t[0] === 'و' && words[t.slice(1)]) t = t.slice(1);
        if (words[t]) out += words[t];
      });
      return out;
    }

    function resetFieldVoiceButton() {
      if (fieldVoiceBtn) {
        fieldVoiceBtn.classList.remove('field-mic-listening');
        const ic = fieldVoiceBtn.querySelector('i');
        if (ic) ic.className = ic.className.replace('fa-microphone-lines', 'fa-microphone').replace(' animate-pulse', '');
      }
      fieldVoiceBtn = null;
      fieldVoiceTarget = null;
    }

    function stopFieldVoiceInput() {
      if (fieldVoiceRecognizer) {
        try { fieldVoiceRecognizer.onresult = null; fieldVoiceRecognizer.onend = null; fieldVoiceRecognizer.abort(); } catch (e) {}
        fieldVoiceRecognizer = null;
      }
      resetFieldVoiceButton();
    }
    window.stopFieldVoiceInput = stopFieldVoiceInput;

    function glowFieldVoice(id) {
      const el = document.getElementById(id);
      if (!el) return;
      el.classList.add('voice-field-glow');
      setTimeout(() => el.classList.remove('voice-field-glow'), 2500);
    }

    function applyFieldVoiceResult(target, spokenText) {
      const text = (spokenText || '').trim();
      if (!text) return;

      // 1. مايك المبلغ المحلي فقط:
      if (target === 'sdg') {
        let clean = text.replace(/^(?:المبلغ المحلي|المحلي|المبلغ|مبلغ|قيمة|بقيمة)\s*/gi, '')
                        .replace(/(?:جنيه سوداني|جنيه|سوداني|محلي|ج\.س)\s*$/gi, '')
                        .trim();
        let amount = parseArabicSpokenNumber(clean) || parseArabicSpokenNumber(text);
        if (!(amount > 0)) {
          const numM = clean.match(/([0-9]+(?:[.,][0-9]+)?)/);
          if (numM) amount = parseFloat(numM[1].replace(/,/g, ''));
        }

        if (!(amount > 0)) {
          showToastNotification(`⚠️ لم أفهم المبلغ من "${text}". حاول مرة أخرى بوضوح`);
          return;
        }

        const sdgInp = document.getElementById('sdgAmount');
        if (sdgInp) {
          sdgInp.value = formatNumberWithConfig(amount, sdgNumberConfig);
          calculateExchange('sdg');
          updateTafqeet();
          glowFieldVoice('sdgAmount');
          glowFieldVoice('usdAmount');
          playBankerChime();
          triggerHapticPulse();

          const usdVal = document.getElementById('usdAmount')?.value || '0';
          showToastNotification(`🎙️ المبلغ المحلي: ${sdgInp.value} ج.س (المعادل: ${usdVal} $)`);
        }
        return;
      }

      // 2. مايك المبلغ بالدولار فقط:
      if (target === 'usd') {
        let clean = text.replace(/^(?:المبلغ بالدولار|مبلغ الدولار|المبلغ دولار|المبلغ|مبلغ|قيمة|بقيمة)\s*/gi, '')
                        .replace(/(?:دولار امريكي|دولار أمريكي|دولارات|دولار|\$|usd)\s*$/gi, '')
                        .trim();
        let amount = parseArabicSpokenNumber(clean) || parseArabicSpokenNumber(text);
        if (!(amount > 0)) {
          const numM = clean.match(/([0-9]+(?:[.,][0-9]+)?)/);
          if (numM) amount = parseFloat(numM[1].replace(/,/g, ''));
        }

        if (!(amount > 0)) {
          showToastNotification(`⚠️ لم أفهم مبلغ الدولار من "${text}". حاول مرة أخرى بوضوح`);
          return;
        }

        const usdInp = document.getElementById('usdAmount');
        if (usdInp) {
          usdInp.value = formatNumberWithConfig(amount, usdNumberConfig);
          calculateExchange('foreign');
          updateTafqeet();
          glowFieldVoice('usdAmount');
          glowFieldVoice('sdgAmount');
          playBankerChime();
          triggerHapticPulse();

          const sdgVal = document.getElementById('sdgAmount')?.value || '0';
          showToastNotification(`🎙️ مبلغ الدولار: ${usdInp.value} $ (المعادل: ${sdgVal} ج.س)`);
        }
        return;
      }

      // 3 & 4. مايك (مِن حساب) ومايك (إلى حساب) - شرط حاسم: الفلترة والمطابقة من الأسماء الموجودة فقط
      if (target === 'from' || target === 'to') {
        let clean = cleanFillers(text);
        clean = clean.replace(/^(?:من حساب|مِن حساب|إلى حساب|الى حساب|صرف من حساب|إيداع لحساب|حساب|طرف|العميل|المورد|السيد|الأستاذ|الشيخ|الحاج|الأخ|من|إلى|الى)\s+/gi, '').trim();

        // مطابقة ذكية في قاعدة بيانات الحسابات المسجلة بكل فئاتها (بنوك، خزن، مصروفات، عملاء، موردين)
        const matchedName = findBestAccountMatch(clean, accountsDB) || findBestAccountMatch(text, accountsDB);
        if (matchedName) {
          selectAcc(target, matchedName);
          glowFieldVoice(FIELD_VOICE_INPUTS[target]);
          playBankerChime();
          triggerHapticPulse();

          const accObj = (accountsDB || []).find(a => a && a.name === matchedName);
          const cat = accObj && accObj.category ? `(${accObj.category})` : '';
          showToastNotification(`🎙️ ${FIELD_VOICE_LABELS[target]}: ${matchedName} ${cat}`);
        } else {
          // الحساب غير موجود في الدليل: لا نقبل اسماً غريباً خارج النظام
          const inp = document.getElementById(FIELD_VOICE_INPUTS[target]);
          if (inp) {
            inp.value = '';
            inp.classList.add('border-red-500');
          }
          onAccountInputFocus(target);
          showToastNotification(`⚠️ الحساب المنطوق "${clean || text}" غير مسجل في دليل الحسابات! يرجى اختيار حساب موجود فقط.`);
        }
        return;
      }

      // 5. مايك رقم التحويلة فقط:
      if (target === 'transfer') {
        let clean = text.replace(/^(?:رقم التحويلة|رقم تحويلة|تحويلة رقم|رقم التحويل|تحويل رقم|التحويلة رقم|التحويلة|تحويلة|رقم الشيك|رقم شيك|شيك رقم|الشيك رقم|الشيك|إشعار رقم|اشعار رقم|رقم الإشعار|رقم الاشعار|رقم العملية|المرجع|مرجع)\s*[:#-]?\s*/gi, '').trim();

        let digits = spokenToDigitString(clean) || spokenToDigitString(text);
        if (!digits) {
          const numM = clean.match(/((?:\d+\s*)+)/);
          if (numM) digits = numM[1].replace(/\s+/g, '');
        }

        if (!digits) {
          showToastNotification(`⚠️ لم أسمع أرقاماً في "${text}". انطق رقم التحويلة رقماً رقماً`);
          return;
        }

        const ti = document.getElementById('transferInput');
        if (ti) {
          ti.value = digits;
          if (typeof handleTransferInputScale === 'function') handleTransferInputScale(digits);
          handleTransferLiveInput(digits);
          glowFieldVoice('transferInput');
          playBankerChime();
          triggerHapticPulse();
          showToastNotification(`🎙️ رقم التحويلة: ${digits}`);
        }
        return;
      }

      // 6. مايك اسم البنك:
      if (target === 'bank') {
        let clean = cleanFillers(text).replace(/^(?:اسم البنك|بنك|البنك|مصرف)\s*/gi, '').trim();
        const banks = ['بنك الخرطوم', 'بنك فيصل الإسلامي', 'بنك أم درمان الوطني', 'بنك النيلين', 'بنك الخليج', 'بنك البركة', 'بنك العمال الوطني', 'مصرف الساحل والصحراء', 'بنك الثروة الحيوانية', 'البنك الفرنسي السوداني'];
        let matched = banks.find(b => b.includes(clean) || clean.includes(b.replace(/^(?:بنك|مصرف|البنك)\s*/, '')));
        const finalBank = matched || (text.startsWith('بنك') || text.startsWith('مصرف') ? text : 'بنك ' + text);
        const bInp = document.getElementById('bankNameInput');
        if (bInp) {
          bInp.value = finalBank;
          glowFieldVoice('bankNameInput');
          playBankerChime();
          triggerHapticPulse();
          showToastNotification(`🎙️ اسم البنك: ${finalBank}`);
        }
        return;
      }
    }

    let fieldVoiceSilenceTimer = null;

    function startFieldVoiceInput(target, btn) {
      triggerHapticPulse();

      // الضغط مرة ثانية على نفس المايك يوقف التسجيل
      if (fieldVoiceRecognizer && fieldVoiceTarget === target) {
        stopFieldVoiceInput();
        return;
      }
      stopFieldVoiceInput();

      if (typeof isMasterVoiceActive !== 'undefined' && isMasterVoiceActive) {
        showToastNotification('⚠️ المايك الرئيسي يعمل الآن. أوقفه أولاً ثم استخدم مايك الخانة');
        return;
      }

      const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!SR) {
        showToastNotification('⚠️ المتصفح لا يدعم التسجيل الصوتي. اكتب في الحقل مباشرة');
        const el = document.getElementById(FIELD_VOICE_INPUTS[target]);
        if (el) el.focus();
        return;
      }

      const rec = new SR();
      rec.lang = 'ar-SD';
      rec.continuous = true;
      rec.interimResults = true;
      rec.maxAlternatives = 3;

      fieldVoiceRecognizer = rec;
      fieldVoiceBtn = btn || null;
      fieldVoiceTarget = target;
      fieldVoiceGotResult = false;
      let lastSpokenText = '';

      if (fieldVoiceBtn) {
        fieldVoiceBtn.classList.add('field-mic-listening');
        const ic = fieldVoiceBtn.querySelector('i');
        if (ic) ic.className = ic.className.replace('fa-microphone', 'fa-microphone-lines') + ' animate-pulse text-red-400';
      }

      // إذا كان المايك لحساب "من" أو "إلى": فتح قائمة البحث التفاعلية فوراً وإظهار كافة التبويبات (بنوك، خزن، مصروفات، عملاء، موردين)
      if (target === 'from' || target === 'to') {
        const inp = document.getElementById(FIELD_VOICE_INPUTS[target]);
        if (inp) {
          inp.focus();
          onAccountInputFocus(target);
          liveSearchAccount(target);
        }
      } else {
        const inp = document.getElementById(FIELD_VOICE_INPUTS[target]);
        if (inp) inp.focus();
      }

      function resetFieldSilence() {
        if (fieldVoiceSilenceTimer) clearTimeout(fieldVoiceSilenceTimer);
        // التوقف التلقائي الذكي بعد ثانيتين من الصمت التام
        fieldVoiceSilenceTimer = setTimeout(() => {
          if (fieldVoiceRecognizer) {
            try { fieldVoiceRecognizer.stop(); } catch(e) {}
          }
        }, 2000);
      }

      rec.onresult = function (event) {
        let interim = '';
        let finalChunk = '';
        for (let i = event.resultIndex; i < event.results.length; ++i) {
          const res = event.results[i];
          if (res.isFinal) {
            finalChunk += res[0].transcript;
          } else {
            interim += res[0].transcript;
          }
        }

        const currentText = (finalChunk || interim).trim();
        if (currentText) {
          lastSpokenText = currentText;
          fieldVoiceGotResult = true;

          // الفلترة اللحظية الحية في الحسابات أثناء نطق المستخدم
          if (target === 'from' || target === 'to') {
            const inp = document.getElementById(FIELD_VOICE_INPUTS[target]);
            let clean = cleanFillers(currentText);
            clean = clean.replace(/^(?:من حساب|مِن حساب|إلى حساب|الى حساب|من|إلى|حساب)\s*/gi, '');
            if (inp && clean) {
              inp.value = clean;
              liveSearchAccount(target);
            }
          }

          resetFieldSilence();
        }
      };

      rec.onerror = function (event) {
        if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
          showToastNotification('⚠️ يرجى السماح بصلاحية المايكروفون في المتصفح');
        } else if (event.error === 'no-speech') {
          showToastNotification('🎙️ لم أسمع شيئاً. اضغط المايك وتحدث مرة أخرى');
        }
      };

      rec.onend = function () {
        if (fieldVoiceSilenceTimer) {
          clearTimeout(fieldVoiceSilenceTimer);
          fieldVoiceSilenceTimer = null;
        }
        fieldVoiceRecognizer = null;
        resetFieldVoiceButton();
        if (lastSpokenText) {
          applyFieldVoiceResult(target, lastSpokenText);
        }
      };

      try {
        rec.start();
        resetFieldSilence();
        showToastNotification(`🎙️ جارٍ الاستماع: ${FIELD_VOICE_LABELS[target]} فقط...`);
      } catch (e) {
        stopFieldVoiceInput();
        showToastNotification('⚠️ تعذّر تشغيل المايك، حاول مرة أخرى');
      }
    }
    window.startFieldVoiceInput = startFieldVoiceInput;

    // ════════════════════════════════════════════════════════════════
    // إعدادات وتنسيق الأرقام المستقل تماماً للمبلغ المحلي والمبلغ بالدولار
    // ════════════════════════════════════════════════════════════════
    let sdgNumberConfig = {
      color: '#ffffff',
      fontSize: '32px',
      fontWeight: '900',
      fontFamily: 'JetBrains Mono',
      thousandsSep: ',',
      decimalSep: '.',
      decimals: 0
    };

    let usdNumberConfig = {
      color: '#10b981',
      fontSize: '32px',
      fontWeight: '900',
      fontFamily: 'JetBrains Mono',
      thousandsSep: ',',
      decimalSep: '.',
      decimals: 2
    };

    let activeAmountTarget = 'sdg'; // 'sdg' أو 'usd'
    let tempNumColor = '#ffffff';
    let tempNumSize = '32px';
    let tempNumWeight = '900';
    let tempNumFont = 'JetBrains Mono';
    let tempNumDecimals = 0;

    function loadSavedNumberConfig() {
      try {
        const savedSdg = localStorage.getItem('magaiees_sdg_format_config');
        if (savedSdg) {
          sdgNumberConfig = Object.assign(sdgNumberConfig, JSON.parse(savedSdg));
        }
        const savedUsd = localStorage.getItem('magaiees_usd_format_config');
        if (savedUsd) {
          usdNumberConfig = Object.assign(usdNumberConfig, JSON.parse(savedUsd));
        }
      } catch(e) {}

      try {
        const dbRate = parseFloat(window.SYSTEM_USD_RATE);
        const savedRate = parseFloat(localStorage.getItem('magaiees_system_usd_rate') || localStorage.getItem('erp_rate_usd') || 0);
        const effectiveRate = (dbRate && dbRate > 1) ? dbRate : ((savedRate && savedRate > 1) ? savedRate : 5999);
        window.SYSTEM_USD_RATE = effectiveRate;
        localStorage.setItem('magaiees_system_usd_rate', String(effectiveRate));
        localStorage.setItem('erp_rate_usd', String(effectiveRate));
        localStorage.setItem('magaies_custom_usd_rate', String(effectiveRate));
        const rateInp = document.getElementById('usdRate');
        if (rateInp) rateInp.value = effectiveRate;
        if (vouchersList[currentIndex]) vouchersList[currentIndex].rate = effectiveRate;
      } catch(e) {}

      applyNumberStylesToInputs();
    }

    function applyNumberStylesToInputs() {
      const isLightMode = document.documentElement.getAttribute('data-theme-mode') === 'light';
      const rawSdgColor = String(sdgNumberConfig.color || '#ffffff').trim().toLowerCase();
      const rawUsdColor = String(usdNumberConfig.color || '#10b981').trim().toLowerCase();

      // حماية التباين البصري (WCAG AAA): منع ظهور خط فاتح فوق خلفية فاتحة أو خط داكن فوق خلفية داكنة
      const lightColorsSet = ['#ffffff', '#fff', '#f8fafc', '#f1f5f9', '#e2e8f0', '#fef08a', '#fde047'];
      const darkColorsSet = ['#000000', '#000', '#0f172a', '#18130a', '#1e293b'];

      let effectiveSdgColor = sdgNumberConfig.color || '#ffffff';
      if (isLightMode && (lightColorsSet.includes(rawSdgColor) || rawSdgColor === '#0f172a')) {
        effectiveSdgColor = '#000000';
      } else if (!isLightMode && darkColorsSet.includes(rawSdgColor)) {
        effectiveSdgColor = '#FFFFFF';
      }

      let effectiveUsdColor = usdNumberConfig.color || '#10b981';
      if (isLightMode && (lightColorsSet.includes(rawUsdColor) || rawUsdColor === '#10b981' || rawUsdColor === '#34d399' || rawUsdColor === '#047857')) {
        effectiveUsdColor = '#064E3B';
      } else if (!isLightMode && darkColorsSet.includes(rawUsdColor)) {
        effectiveUsdColor = '#10b981';
      }

      // 1. تطبيق خصائص المبلغ المحلي
      const sdgInp = document.getElementById('sdgAmount');
      if (sdgInp) {
        sdgInp.style.setProperty('font-size', sdgNumberConfig.fontSize || '32px', 'important');
        sdgInp.style.setProperty('color', effectiveSdgColor, 'important');
        sdgInp.style.setProperty('font-weight', sdgNumberConfig.fontWeight || '900', 'important');
        sdgInp.style.setProperty('font-family', `'${sdgNumberConfig.fontFamily || 'JetBrains Mono'}', monospace, sans-serif`, 'important');
      }

      // 2. تطبيق خصائص المبلغ بالدولار
      const usdInp = document.getElementById('usdAmount');
      if (usdInp) {
        usdInp.style.setProperty('font-size', usdNumberConfig.fontSize || '32px', 'important');
        usdInp.style.setProperty('color', effectiveUsdColor, 'important');
        usdInp.style.setProperty('font-weight', usdNumberConfig.fontWeight || '900', 'important');
        usdInp.style.setProperty('font-family', `'${usdNumberConfig.fontFamily || 'JetBrains Mono'}', monospace, sans-serif`, 'important');
      }

      // ضبط متغيرات CSS
      document.documentElement.style.setProperty('--num-size-sdg', sdgNumberConfig.fontSize || '32px');
      document.documentElement.style.setProperty('--num-color-sdg', effectiveSdgColor);
      document.documentElement.style.setProperty('--num-weight-sdg', sdgNumberConfig.fontWeight || '900');
      document.documentElement.style.setProperty('--num-size-usd', usdNumberConfig.fontSize || '32px');
      document.documentElement.style.setProperty('--num-color-usd', effectiveUsdColor);
      document.documentElement.style.setProperty('--num-weight-usd', usdNumberConfig.fontWeight || '900');
    }
    window.applyNumberStylesToInputs = applyNumberStylesToInputs;

    function switchActiveAmountTarget(target) {
      // حفظ التغييرات المؤقتة للهدف الحالي
      saveCurrentTempToTarget();

      activeAmountTarget = target;
      const btnSdg = document.getElementById('targetBtnSdg');
      const btnUsd = document.getElementById('targetBtnUsd');
      const titleSpan = document.getElementById('targetPreviewTitle');

      if (target === 'sdg') {
        if (btnSdg) btnSdg.className = 'py-2 px-2 rounded-xl text-xs font-black flex items-center justify-center gap-1.5 transition bg-sky-600 text-white shadow-sm border border-sky-400';
        if (btnUsd) btnUsd.className = 'py-2 px-2 rounded-xl text-xs font-black flex items-center justify-center gap-1.5 transition bg-slate-800 text-slate-300 border border-slate-700 hover:text-white';
        if (titleSpan) {
          titleSpan.textContent = 'المبلغ المحلي (ج.س)';
          titleSpan.className = 'text-sky-400 font-black';
        }
      } else {
        if (btnUsd) btnUsd.className = 'py-2 px-2 rounded-xl text-xs font-black flex items-center justify-center gap-1.5 transition bg-emerald-600 text-white shadow-sm border border-emerald-400';
        if (btnSdg) btnSdg.className = 'py-2 px-2 rounded-xl text-xs font-black flex items-center justify-center gap-1.5 transition bg-slate-800 text-slate-300 border border-slate-700 hover:text-white';
        if (titleSpan) {
          titleSpan.textContent = 'المبلغ بالدولار ($ USD)';
          titleSpan.className = 'text-emerald-400 font-black';
        }
      }

      loadTargetToTemp();
    }

    function saveCurrentTempToTarget() {
      const cfg = activeAmountTarget === 'sdg' ? sdgNumberConfig : usdNumberConfig;
      cfg.color = tempNumColor;
      cfg.fontSize = tempNumSize;
      cfg.fontWeight = tempNumWeight;
      cfg.fontFamily = tempNumFont;
      cfg.decimals = tempNumDecimals;
      const thSel = document.getElementById('numThousandsSelect');
      if (thSel) cfg.thousandsSep = thSel.value;
      const decSel = document.getElementById('numDecimalSelect');
      if (decSel) cfg.decimalSep = decSel.value;
    }

    function applyLivePropertyChange() {
      saveCurrentTempToTarget();
      applyNumberStylesToInputs();
      recalculateAmountsWithNewConfig();
    }

    function loadTargetToTemp() {
      const cfg = activeAmountTarget === 'sdg' ? sdgNumberConfig : usdNumberConfig;
      tempNumColor = cfg.color || (activeAmountTarget === 'sdg' ? '#ffffff' : '#10b981');
      tempNumSize = cfg.fontSize || '32px';
      tempNumWeight = cfg.fontWeight || '900';
      tempNumFont = cfg.fontFamily || 'JetBrains Mono';
      tempNumDecimals = (cfg.decimals !== undefined) ? parseInt(cfg.decimals) : (activeAmountTarget === 'sdg' ? 0 : 2);

      const thSelect = document.getElementById('numThousandsSelect');
      if (thSelect) thSelect.value = (cfg.thousandsSep !== undefined) ? cfg.thousandsSep : ',';
      const decSelect = document.getElementById('numDecimalSelect');
      if (decSelect) decSelect.value = (cfg.decimalSep !== undefined) ? cfg.decimalSep : '.';

      setNumColor(tempNumColor, '', false);
      setNumSize(tempNumSize, false);
      setNumWeight(tempNumWeight, false);
      setNumFont(tempNumFont, false);
      setNumDecimals(tempNumDecimals, false);
      updateNumPreview();
    }

    function openNumberPropertiesModal(e, target = null) {
      if (e) {
        if (typeof e.preventDefault === 'function') e.preventDefault();
        if (typeof e.stopPropagation === 'function') e.stopPropagation();
      }
      
      const chosenTarget = target || activeAmountTarget || 'sdg';
      switchActiveAmountTarget(chosenTarget);

      const modal = document.getElementById('modalNumberProperties');
      if (modal) {
        modal.classList.remove('hidden');
        const scrollable = modal.querySelector('.modal-scrollable-body');
        if (scrollable) {
          scrollable.scrollTop = 0;
        }
      }
    }

    function closeNumberPropertiesModal() {
      const modal = document.getElementById('modalNumberProperties');
      if (modal) modal.classList.add('hidden');
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
      if (refresh) {
        updateNumPreview();
        applyLivePropertyChange();
      }
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
      if (refresh) {
        updateNumPreview();
        applyLivePropertyChange();
      }
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
      if (refresh) {
        updateNumPreview();
        applyLivePropertyChange();
      }
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
      if (refresh) {
        updateNumPreview();
        applyLivePropertyChange();
      }
    }

    function setNumDecimals(decimals, refresh = true) {
      tempNumDecimals = parseInt(decimals);
      const label = document.getElementById('currentDecimalsLabel');
      if (label) {
        label.textContent = tempNumDecimals === 0 ? '0 (بدون كسور / صحيح)' : `${tempNumDecimals} خانات`;
      }

      document.querySelectorAll('.dec-btn').forEach(btn => {
        btn.classList.remove('bg-cyan-600', 'text-white', 'border-cyan-400');
        btn.classList.add('bg-slate-800', 'text-slate-300', 'border-slate-700');
      });
      const activeBtn = document.getElementById('btnDec' + tempNumDecimals);
      if (activeBtn) {
        activeBtn.classList.remove('bg-slate-800', 'text-slate-300', 'border-slate-700');
        activeBtn.classList.add('bg-cyan-600', 'text-white', 'border-cyan-400');
      }
      if (refresh) {
        updateNumPreview();
        applyLivePropertyChange();
      }
    }

    function formatNumberWithConfig(val, cfg) {
      if (val === '' || val === null || val === undefined) return '';
      let n = (typeof val === 'number') ? val : parseRawNumber(val);
      if (isNaN(n)) return '';

      const conf = (typeof cfg === 'string') 
        ? (cfg === 'usd' ? usdNumberConfig : sdgNumberConfig) 
        : (cfg || (activeAmountTarget === 'usd' ? usdNumberConfig : sdgNumberConfig));
      const dec = (conf.decimals !== undefined) ? parseInt(conf.decimals) : (conf === sdgNumberConfig ? 0 : 2);
      const thSep = (conf.thousandsSep !== undefined) ? conf.thousandsSep : ',';
      const decSep = (conf.decimalSep !== undefined) ? conf.decimalSep : '.';

      const fixed = n.toFixed(dec);
      const parts = fixed.split('.');
      let integerPart = parts[0];
      const decimalPart = parts[1];

      if (thSep) {
        integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, thSep);
      }

      if (dec > 0 && decimalPart !== undefined) {
        return integerPart + decSep + decimalPart;
      }
      return integerPart;
    }

    function formatCurrentAmountInput(target) {
      if (target === 'sdg') {
        const inp = document.getElementById('sdgAmount');
        if (!inp) return;
        const raw = parseRawNumber(inp.value);
        if (raw > 0) {
          inp.value = formatNumberWithConfig(raw, sdgNumberConfig);
        }
      } else {
        const inp = document.getElementById('usdAmount');
        if (!inp) return;
        const raw = parseRawNumber(inp.value);
        if (raw > 0) {
          inp.value = formatNumberWithConfig(raw, usdNumberConfig);
        }
      }
    }

    function updateNumPreview() {
      const preview = document.getElementById('numPropLivePreview');
      const thSep = document.getElementById('numThousandsSelect')?.value ?? ',';
      const decSep = document.getElementById('numDecimalSelect')?.value ?? '.';
      
      const sample = activeAmountTarget === 'sdg' ? 2650000.75 : 1000.754321;
      const formatted = formatNumberWithConfig(sample, {
        thousandsSep: thSep,
        decimalSep: decSep,
        decimals: tempNumDecimals
      });

      if (preview) {
        preview.textContent = (activeAmountTarget === 'usd' ? '$ ' : '') + formatted + (activeAmountTarget === 'sdg' ? ' ج.س' : '');
        preview.style.setProperty('color', tempNumColor, 'important');
        preview.style.setProperty('font-size', tempNumSize, 'important');
        preview.style.setProperty('font-weight', tempNumWeight, 'important');
        preview.style.setProperty('font-family', `'${tempNumFont}', monospace, sans-serif`, 'important');
      }
    }

    function saveNumberProperties() {
      saveCurrentTempToTarget();

      localStorage.setItem('magaiees_sdg_format_config', JSON.stringify(sdgNumberConfig));
      localStorage.setItem('magaiees_usd_format_config', JSON.stringify(usdNumberConfig));
      
      applyNumberStylesToInputs();
      recalculateAmountsWithNewConfig();
      closeNumberPropertiesModal();
      playFeedbackTone('tap');
      showToastNotification(`✓ تم حفظ وتطبيق الخصائص الفورية بنجاح!\n• المبلغ المحلي: حجم (${sdgNumberConfig.fontSize}) ولون (${sdgNumberConfig.color}) وتقريب (${sdgNumberConfig.decimals} خانات) وفاصلة (${sdgNumberConfig.thousandsSep}).\n• المبلغ بالدولار: حجم (${usdNumberConfig.fontSize}) ولون (${usdNumberConfig.color}) وتقريب (${usdNumberConfig.decimals} خانات).`);
    }

    // ════════════════════════════════════════════════════════════════
    // تعديل ومزامنة قيمة الدولار مع قاعدة البيانات والسيستم بالكامل
    // ════════════════════════════════════════════════════════════════
    async function updateSystemUsdRate(val) {
      let rate = parseFloat(val);
      if (!rate || rate <= 0) rate = parseFloat(window.SYSTEM_USD_RATE) || 5999;

      window.SYSTEM_USD_RATE = rate;
      try {
        localStorage.setItem('magaiees_system_usd_rate', String(rate));
        localStorage.setItem('erp_rate_usd', String(rate));
        localStorage.setItem('magaies_custom_usd_rate', String(rate));
        const cr = JSON.parse(localStorage.getItem('erp_currency_rates_v1') || '{}');
        cr.USD = rate;
        cr.SDG = 1;
        localStorage.setItem('erp_currency_rates_v1', JSON.stringify(cr));
      } catch (e) {}

      if (vouchersList[currentIndex]) {
        vouchersList[currentIndex].rate = rate;
      }

      calculateExchange('rate');
      updateTafqeet();
      updateAccountBalanceBadges();

      try {
        const fd = new FormData();
        fd.append('rate', String(rate));
        const res = await fetch('api/mirror_sync.php?action=save_currency_rate', {
          method: 'POST',
          body: fd
        });
        const data = await res.json();
        if (data && data.success) {
          showToastNotification(`✓ تم تحديث سعر صرف الدولار في قاعدة البيانات إلى (${rate.toLocaleString()}) وتعميمه فورياً على جميع شاشات البرنامج!`);
          return;
        }
      } catch (err) {}

      showToastNotification(`✓ تم تحديث سعر صرف الدولار إلى (${rate.toLocaleString()}) ومزامنته مع النظام!`);
    }

    // ════════════════════════════════════════════════════════════════
    // دالة تحديد الطبيعة المحاسبية للحساب (Normal Balance: debit vs credit)
    // ════════════════════════════════════════════════════════════════
    function getAccountNormalBalanceJs(acc) {
      if (!acc) return 'debit';
      if (acc.normalBalance === 'credit' || acc.normalBalance === 'debit') return acc.normalBalance;
      const cat = String(acc.category || '');
      const src = String(acc.sourceTable || '');
      if (src === 'suppliers' || cat.includes('مورد') || cat.includes('إيراد') || cat.includes('مبيعات') || cat.includes('التزام') || cat.includes('حقوق') || cat.includes('أمانات')) {
        return 'credit';
      }
      return 'debit';
    }

    function isCashOrBankAccountJs(acc, nameFallback = '') {
      const cat = String(acc?.category || '');
      const src = String(acc?.sourceTable || '');
      const nm = String(acc?.name || nameFallback || '');
      return (src === 'safes' || src === 'banks' || cat.includes('خزن') || cat.includes('بنوك') || cat.includes('مصارف') || nm.includes('خزينة') || nm.includes('بنك') || nm.includes('كاش') || nm.includes('بنكك') || nm.includes('فوري'));
    }

    function classifyVoucherNatureJs(fromAcc, toAcc, fromName, toName) {
      if (!fromName && !toName) return { code: 'draft', label: 'حركة مالية', cls: 'bg-indigo-500/15 border-indigo-400/40 text-indigo-300' };
      const fromIsCash = isCashOrBankAccountJs(fromAcc, fromName);
      const toIsCash = isCashOrBankAccountJs(toAcc, toName);
      if (fromIsCash && toIsCash) {
        return { code: 'internal_transfer', label: '🔵 تحويل داخلي (تغذية)', cls: 'bg-sky-500/20 border-sky-400/50 text-sky-300' };
      }
      if (toIsCash && !fromIsCash) {
        return { code: 'receipt', label: '🟢 سند قبض / توريد', cls: 'bg-emerald-500/20 border-emerald-400/50 text-emerald-300' };
      }
      if (fromIsCash && !toIsCash) {
        return { code: 'payment', label: '🔴 سند صرف / دفع', cls: 'bg-rose-500/20 border-rose-400/50 text-rose-300' };
      }
      return { code: 'settlement', label: 'حركة مالية', cls: 'bg-cyan-500/20 border-cyan-400/50 text-cyan-300' };
    }

    function updateMagnitudeBadgeUI() {
      // تم إيقاف شارة التقريب أعلى المبلغ المحلي بناءً على طلب المستخدم
    }

    // ════════════════════════════════════════════════════════════════
    // حساب وعرض الرصيد وفق الطبيعة المحاسبية (مدين/دائن) واتجاه التدفق المالي
    // ════════════════════════════════════════════════════════════════
    function updateAccountBalanceBadges() {
      const rate = parseFloat(document.getElementById('usdRate')?.value) || parseFloat(window.SYSTEM_USD_RATE) || 5999;
      const fromName = (document.getElementById('fromSearchInput')?.value || '').trim();
      const toName = (document.getElementById('toSearchInput')?.value || '').trim();
      const activeSdgAmt = parseRawNumber(document.getElementById('sdgAmount')?.value || 0);

      const fromAcc = accountsDB.find(a => a && a.name === fromName);
      const toAcc = accountsDB.find(a => a && a.name === toName);

      // إخفاء شارة الهيدر (قيد تسوية محاسبية) نهائياً بناءً على طلب المستخدم
      const headerBadge = document.getElementById('voucherNatureHeaderBadge');
      if (headerBadge) {
        headerBadge.className = 'hidden';
        headerBadge.textContent = '';
      }

      // 1. حساب من حساب (الدافع / الطرف الدائن في القيد)
      const fromSdgEl = document.getElementById('fromAccountSdgBalance');
      const fromUsdEl = document.getElementById('fromAccountUsdBalance');
      const fromBadge = document.getElementById('fromProjectedBadge');
      const fromLabelEl = document.getElementById('fromProjectedLabel');
      const fromAmtEl = document.getElementById('fromProjectedAmount');

      if (fromAcc) {
        const curBal = Number(fromAcc.sdgBalance || 0);
        const normal = getAccountNormalBalanceJs(fromAcc);
        if (fromSdgEl) fromSdgEl.textContent = curBal.toLocaleString('en-US');
        if (fromUsdEl) fromUsdEl.textContent = (curBal / rate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        if (activeSdgAmt > 0 && fromBadge && fromAmtEl) {
          // الطرف (من حساب) يُقيّد دائناً (Credit): ينقص إذا كان حساباً مديناً، ويزيد إذا كان حساباً دائناً
          const projected = (normal === 'credit') ? (curBal + activeSdgAmt) : (curBal - activeSdgAmt);
          fromAmtEl.textContent = Number(projected).toLocaleString('en-US');
          if (fromLabelEl) fromLabelEl.textContent = 'بعد القيد:';
          fromBadge.className = 'inline-flex items-center gap-0.5 text-sky-300 shrink-0';
          fromBadge.classList.remove('hidden');
        } else if (fromBadge) {
          fromBadge.classList.add('hidden');
        }
      } else {
        if (fromSdgEl) fromSdgEl.textContent = '---';
        if (fromUsdEl) fromUsdEl.textContent = '---';
        if (fromBadge) fromBadge.classList.add('hidden');
      }

      // 2. حساب إلى حساب (المستفيد / الطرف المدين في القيد)
      const toSdgEl = document.getElementById('toAccountSdgBalance');
      const toUsdEl = document.getElementById('toAccountUsdBalance');
      const toBadge = document.getElementById('toProjectedBadge');
      const toLabelEl = document.getElementById('toProjectedLabel');
      const toAmtEl = document.getElementById('toProjectedAmount');

      if (toAcc) {
        const curBal = Number(toAcc.sdgBalance || 0);
        const normal = getAccountNormalBalanceJs(toAcc);
        if (toSdgEl) toSdgEl.textContent = curBal.toLocaleString('en-US');
        if (toUsdEl) toUsdEl.textContent = (curBal / rate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        if (activeSdgAmt > 0 && toBadge && toAmtEl) {
          // الطرف (إلى حساب) يُقيّد مديناً (Debit): يزيد إذا كان حساباً مديناً (خزينة/بنك/مصروف/عميل)، وينقص إذا كان دائناً (سداد لمورد)
          const projected = (normal === 'credit') ? (curBal - activeSdgAmt) : (curBal + activeSdgAmt);
          toAmtEl.textContent = Number(projected).toLocaleString('en-US');
          if (toLabelEl) toLabelEl.textContent = 'بعد القيد:';
          toBadge.className = 'inline-flex items-center gap-0.5 text-sky-300 shrink-0';
          toBadge.classList.remove('hidden');
        } else if (toBadge) {
          toBadge.classList.add('hidden');
        }
      } else {
        if (toSdgEl) toSdgEl.textContent = '---';
        if (toUsdEl) toUsdEl.textContent = '---';
        if (toBadge) toBadge.classList.add('hidden');
      }

      const recTo = document.getElementById('recipientToNameDisplay');
      if (recTo) recTo.textContent = toName || '---';
      const recFrom = document.getElementById('recipientFromNameDisplay');
      if (recFrom) recFrom.textContent = fromName || '---';
    }

    // ════════════════════════════════════════════════════════════════
    // عكس طرفي السند بلمسة واحدة (Swap From ⇄ To Accounts)
    // ════════════════════════════════════════════════════════════════
    function swapFromAndToAccounts() {
      const fromInp = document.getElementById('fromSearchInput');
      const toInp = document.getElementById('toSearchInput');
      if (!fromInp || !toInp) return;

      const oldFrom = fromInp.value;
      const oldTo = toInp.value;
      fromInp.value = oldTo;
      toInp.value = oldFrom;

      const fromClear = document.getElementById('fromClearBtn');
      if (fromClear) fromClear.classList.toggle('hidden', !fromInp.value);
      const toClear = document.getElementById('toClearBtn');
      if (toClear) toClear.classList.toggle('hidden', !toInp.value);

      triggerHapticPulse();
      playFeedbackTone('tap');
      updateAccountBalanceBadges();
      showToastNotification('⇄ تم عكس اتجاه الحسابات (من حساب ⇄ إلى حساب) بنجاح');
    }
    window.swapFromAndToAccounts = swapFromAndToAccounts;

    // ════════════════════════════════════════════════════════════════
    // كشف الحساب المصغر الفوري (Account Mini-Statement Bottom Sheet)
    // ════════════════════════════════════════════════════════════════
    let _activeMiniStatementAccount = '';
    async function openAccountMiniStatement(side) {
      const inp = document.getElementById(side === 'from' ? 'fromSearchInput' : 'toSearchInput');
      const accName = (inp?.value || '').trim();
      if (!accName) {
        showToastNotification(`⚠️ يرجى اختيار الحساب أولاً في خانة (${side === 'from' ? 'من حساب' : 'إلى حساب'}) لعرض كشف حركاته`);
        if (inp) inp.focus();
        return;
      }
      _activeMiniStatementAccount = accName;
      const modal = document.getElementById('modalAccountMiniStatement');
      const nameEl = document.getElementById('miniStatementAccountName');
      const balEl = document.getElementById('miniStatementCurrentBalance');
      const catEl = document.getElementById('miniStatementAccountCategory');
      const listEl = document.getElementById('miniStatementRowsContainer');

      if (nameEl) nameEl.textContent = accName;
      const localAcc = accountsDB.find(a => a && a.name === accName);
      if (balEl) balEl.textContent = localAcc ? (Number(localAcc.sdgBalance || 0).toLocaleString('en-US') + ' ج.س') : '---';
      if (catEl) catEl.textContent = localAcc ? `${localAcc.category || 'حساب'} • ${getAccountNormalBalanceJs(localAcc) === 'credit' ? 'طبيعته دائن (له)' : 'طبيعته مدين (عليه)'}` : '---';
      if (listEl) listEl.innerHTML = '<div class="text-center text-slate-400 text-xs py-6"><i class="fa-solid fa-spinner fa-spin ml-1"></i> جارٍ جلب آخر حركات الحساب من قاعدة البيانات...</div>';
      if (modal) modal.classList.remove('hidden');

      triggerHapticPulse();

      try {
        const res = await fetch(`mobile-voucher.php?api=account_mini_statement&account=${encodeURIComponent(accName)}&t=${Date.now()}`);
        const data = await res.json();
        if (data && data.success) {
          if (balEl && data.currentBalance !== undefined) {
            balEl.textContent = Number(data.currentBalance || 0).toLocaleString('en-US') + ' ج.س';
          }
          if (catEl && data.category) {
            catEl.textContent = `${data.category} • ${data.normalBalance === 'credit' ? 'طبيعته دائن (له)' : 'طبيعته مدين (عليه)'}`;
          }
          const rows = Array.isArray(data.movements) ? data.movements : [];
          if (rows.length === 0) {
            if (listEl) listEl.innerHTML = '<div class="text-center text-slate-400 text-xs py-6">لا توجد حركات مسجلة لهذا الحساب بعد</div>';
            return;
          }
          if (listEl) {
            listEl.innerHTML = rows.map(r => {
              const isDebit = (r.role === 'debit');
              return `
                <div class="p-2.5 rounded-xl bg-slate-950/90 border border-slate-800 flex items-center justify-between gap-2 text-xs">
                  <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5">
                      <span class="px-1.5 py-0.5 rounded bg-indigo-500/20 text-indigo-300 font-mono font-bold text-[10px]">#${r.voucherNo}</span>
                      <span class="px-1.5 py-0.5 rounded font-bold text-[10px] ${isDebit ? 'bg-emerald-500/15 text-emerald-300' : 'bg-rose-500/15 text-rose-300'}">${r.roleLabel}</span>
                      <span class="text-slate-400 font-mono text-[10px]">${r.date || ''}</span>
                    </div>
                    <div class="text-slate-200 font-bold truncate mt-1">الطرف المقابل: ${r.counterparty || '---'}</div>
                    ${r.notes ? `<div class="text-slate-400 text-[10px] truncate">${r.notes}</div>` : ''}
                  </div>
                  <div class="text-left shrink-0">
                    <div class="font-mono font-black text-sm ${isDebit ? 'text-emerald-400' : 'text-rose-400'}">${Number(r.amountLocal || 0).toLocaleString('en-US')} ج.س</div>
                    ${r.refNo ? `<div class="text-[9.5px] font-mono text-amber-300">#${r.refNo}</div>` : ''}
                  </div>
                </div>
              `;
            }).join('');
          }
        }
      } catch (e) {
        if (listEl) listEl.innerHTML = '<div class="text-center text-amber-400 text-xs py-6">⚠️ تعذر الاتصال بالسيرفر لجلب الكشف اللحظي</div>';
      }
    }
    window.openAccountMiniStatement = openAccountMiniStatement;

    function closeAccountMiniStatement() {
      const modal = document.getElementById('modalAccountMiniStatement');
      if (modal) modal.classList.add('hidden');
    }
    window.closeAccountMiniStatement = closeAccountMiniStatement;

    function openFullAccountStatementFromMini() {
      if (!_activeMiniStatementAccount) return;
      window.location.href = `account-statement.php?account=${encodeURIComponent(_activeMiniStatementAccount)}`;
    }
    window.openFullAccountStatementFromMini = openFullAccountStatementFromMini;

    function clearLinkedInvoiceRef() {
      if (window._activePosPaymentPayload) {
        window._activePosPaymentPayload.ref = '';
      }
      const banner = document.getElementById('linkedInvoiceBanner');
      if (banner) {
        banner.classList.add('hidden');
        banner.classList.remove('flex');
      }
    }
    window.clearLinkedInvoiceRef = clearLinkedInvoiceRef;

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

    // ════════════════════════════════════════════════════════════════
    // تحديث عرض التاريخ بالتنسيق المعكوس (اليوم / الشهر / السنة) بخط مريح
    // ════════════════════════════════════════════════════════════════
    function updateVoucherDateDisplay() {
      const input = document.getElementById('voucherDate');
      const display = document.getElementById('voucherDateDisplay');
      if (!input || !display) return;
      const val = (input.value || '').trim();
      if (!val) {
        display.textContent = '--/--/----';
        return;
      }
      if (val.includes('-')) {
        const parts = val.split('-');
        if (parts.length === 3) {
          const yyyy = parts[0];
          const mm = parts[1];
          const dd = parts[2];
          display.textContent = `${dd}/${mm}/${yyyy}`;
          return;
        }
      }
      display.textContent = val;
    }

    function triggerDatePicker() {
      const el = document.getElementById('voucherDate');
      if (el) {
        if (typeof el.showPicker === 'function') {
          try { el.showPicker(); } catch(e) { el.focus(); }
        } else {
          el.focus();
        }
      }
    }

    function extractVoucherSeqJs(rawId) {
      const s = String(rawId || '').trim().toUpperCase();
      if (!s || s === '840' || s === '841' || s === '842') return 0;
      let m = s.match(/^E\d{4}(\d{4})$/);
      if (m) return parseInt(m[1], 10) || 0;
      m = s.match(/^E\d{3}(\d{4})$/);
      if (m) return parseInt(m[1], 10) || 0;
      m = s.match(/^E[-_]?(\d{1,5})$/);
      if (m) return parseInt(m[1], 10) || 0;
      if (/^\d{1,6}$/.test(s)) {
        const v = parseInt(s, 10) || 0;
        return (v > 0 && v < 100000) ? v : 0;
      }
      return 0;
    }

    function computeNextVoucherSequentialNo(commitIncrement = false) {
      const now = new Date();
      const prefix = 'E' + String(now.getFullYear()).slice(-2) + String(now.getMonth() + 1).padStart(2, '0');
      let maxSeq = 0;

      if (window.PHP_NEXT_NO) {
        const phpSeq = extractVoucherSeqJs(window.PHP_NEXT_NO);
        if (phpSeq > 1 && (phpSeq - 1) > maxSeq) maxSeq = phpSeq - 1;
      }

      if (Array.isArray(vouchersList)) {
        vouchersList.forEach(v => {
          const seq = extractVoucherSeqJs(v?.id || v?.voucherNo);
          if (seq > maxSeq) maxSeq = seq;
        });
      }

      if (Array.isArray(offlineQueue)) {
        offlineQueue.forEach(q => {
          const seq = extractVoucherSeqJs(q?.id || q?.voucherNo);
          if (seq > maxSeq) maxSeq = seq;
        });
      }

      try {
        const known = JSON.parse(localStorage.getItem('magaies_last_known_sequences_v1') || '{}');
        if (known.finance && parseInt(known.finance, 10) > maxSeq) {
          maxSeq = parseInt(known.finance, 10);
        }
      } catch (e) {}

      if (window.MagaiesOffline && typeof window.MagaiesOffline.getHighestKnownSeq === 'function') {
        const mSeq = window.MagaiesOffline.getHighestKnownSeq('finance');
        if (mSeq > maxSeq) maxSeq = mSeq;
      }

      const nextSeq = maxSeq + 1;
      if (commitIncrement) {
        try {
          const known = JSON.parse(localStorage.getItem('magaies_last_known_sequences_v1') || '{}');
          known.finance = Math.max(parseInt(known.finance, 10) || 0, nextSeq);
          localStorage.setItem('magaies_last_known_sequences_v1', JSON.stringify(known));
        } catch (e) {}
      }
      return prefix + String(nextSeq).padStart(4, '0');
    }

    function loadVoucher(index) {
      if (index < 0 || index >= vouchersList.length) return;
      currentIndex = index;
      const v = vouchersList[index] || {};

      const vId = v.id || v.voucherNo || v.paymentNo || v.receiptNo || '';
      const dispEl = document.getElementById('currentVoucherDisplay');
      if (dispEl) dispEl.textContent = vId;

      const rawDate = String(v.date || v.createdAt || v.savedAt || new Date().toISOString()).slice(0, 10);
      const dateEl = document.getElementById('voucherDate');
      if (dateEl) dateEl.value = rawDate;
      updateVoucherDateDisplay();

      const rateVal = parseFloat(v.rate ?? v.exchangeRate) || parseFloat(window.SYSTEM_USD_RATE) || 5999;
      const rateEl = document.getElementById('usdRate');
      if (rateEl) rateEl.value = rateVal;

      const ccEl = document.getElementById('costCenterInput');
      if (ccEl) {
        ccEl.value = v.costCenter || v.jobId || 'المكتب الرئيسي - الإدارة العامة';
        ccEl.classList.remove('mv-ghost-default');
      }

      const sdgVal = parseFloat(v.sdg ?? v.amountLocal ?? v.amount ?? v.totalAmount ?? 0) || 0;
      let usdVal = parseFloat(v.usd ?? v.foreignAmt ?? v.amountForeign ?? 0) || 0;
      if (usdVal <= 0 && sdgVal > 0 && rateVal > 0) {
        usdVal = parseFloat((sdgVal / rateVal).toFixed(2));
      }

      const sdgInp = document.getElementById('sdgAmount');
      if (sdgInp) sdgInp.value = sdgVal > 0 ? formatNumberWithConfig(sdgVal, sdgNumberConfig) : '';
      const usdInp = document.getElementById('usdAmount');
      if (usdInp) usdInp.value = usdVal > 0 ? formatNumberWithConfig(usdVal, usdNumberConfig) : '';

      const notesVal = String(v.notes ?? v.toStatement ?? v.fromStatement ?? v.statement ?? v.description ?? '');
      const notesInp = document.getElementById('voucherNotes');
      if (notesInp) notesInp.value = notesVal;

      const rawFrom = v.fromName || v.fromAccountName || v.from || v.fromAccount || v.withdrawalAccount || '';
      const rawTo = v.toName || v.toAccountName || v.to || v.toAccount || v.beneficiaryName || v.depositAccount || '';

      const resolveAccName = (val) => {
        const clean = String(val || '').trim();
        if (!clean || !Array.isArray(accountsDB)) return clean;
        const found = accountsDB.find(a => a && (a.name === clean || String(a.id) === clean));
        return found ? found.name : clean;
      };

      const fromVal = resolveAccName(rawFrom);
      const toVal = resolveAccName(rawTo);

      // التأكد من وجود حسابات السند التاريخي في الذاكرة لعرض أرصدتها وطبيعتها بدقة
      if (Array.isArray(accountsDB)) {
        [fromVal, toVal].forEach(accName => {
          const clean = String(accName || '').trim();
          if (clean && !accountsDB.some(a => a && a.name && a.name.trim() === clean)) {
            accountsDB.push({ id: 'hist_' + Date.now() + '_' + Math.random().toString(36).slice(2, 6), name: clean, category: 'عام', sdgBalance: 0, normalBalance: 'debit', balanceType: '' });
          }
        });
      }

      const fromInput = document.getElementById('fromSearchInput');
      const toInput = document.getElementById('toSearchInput');
      if (fromInput) {
        fromInput.value = fromVal;
        fromInput.classList.remove('border-red-500');
      }
      if (toInput) {
        toInput.value = toVal;
        toInput.classList.remove('border-red-500');
      }

      const fromClear = document.getElementById('fromClearBtn');
      if (fromClear) fromClear.classList.toggle('hidden', !fromVal);
      const toClear = document.getElementById('toClearBtn');
      if (toClear) toClear.classList.toggle('hidden', !toVal);

      selectCurrency(v.currency || 'SDG', false);
      updateAccountBalanceBadges();

      const fromAccObj = Array.isArray(accountsDB) ? accountsDB.find(a => a && a.name === fromVal) : null;
      const toAccObj = Array.isArray(accountsDB) ? accountsDB.find(a => a && a.name === toVal) : null;
      const computedNature = classifyVoucherNatureJs(fromAccObj, toAccObj, fromVal, toVal);
      const rawNatureLabel = (v.voucherNatureLabel && String(v.voucherNatureLabel).trim()) ? String(v.voucherNatureLabel).trim() : computedNature.label;
      const vNatureLabel = rawNatureLabel.includes('قيد تسوية') ? 'حركة مالية' : rawNatureLabel;

      const headerBadge = document.getElementById('voucherNatureHeaderBadge');
      if (headerBadge) {
        headerBadge.className = 'hidden';
        headerBadge.textContent = '';
      }
      const tagEl = document.getElementById('voucherTag');
      if (tagEl) {
        tagEl.textContent = v.isOffline ? `${vNatureLabel} (أوفلاين)` : vNatureLabel;
      }

      currentDraftIdempotencyKey = v.idempotencyKey || generateUUID();
      if (v.attachment) {
        currentReceiptAttachment = v.attachment;
        const card = document.getElementById('receiptAttachmentCard');
        const thumb = document.getElementById('receiptAttachmentThumb');
        const summary = document.getElementById('receiptAttachmentSummary');
        if (card && thumb) {
          thumb.src = v.attachment;
          card.classList.remove('hidden');
        }
        if (summary) summary.textContent = `مستند ثبوتي (${v.bankName || 'إشعار بنكي'})`;
      } else {
        if (typeof removeAttachedReceipt === 'function') removeAttachedReceipt();
      }

      const refVal = v.refNo ?? v.mobileTransferNo ?? v.transferRef ?? v.checkNo ?? '';
      const bankVal = v.bankName ?? v.bank ?? '';

      let resolvedPayType = v.payType || v.paymentMethod || v.method || 'transfer';
      if (resolvedPayType === 'mobile') resolvedPayType = 'transfer';
      if (resolvedPayType === 'check' || resolvedPayType === 'cheque') resolvedPayType = 'normal_cheque';

      setPaymentType(resolvedPayType);
      // إذا كان السند القديم يحتوي على رقم تحويل أو اسم بنك، نضمن إظهار حاوية التحويل والبنك دائماً
      if (refVal || bankVal) {
        const trContainer = document.getElementById('transferContainer');
        if (trContainer) trContainer.classList.remove('hidden');
      }

      const transferInp = document.getElementById('transferInput');
      if (transferInp) {
        transferInp.value = refVal;
        transferInp.classList.remove('border-red-500', 'border-amber-500', 'border-emerald-500');
        handleTransferInputScale(transferInp.value);
      }
      const fbBadge = document.getElementById('transferFeedbackBadge');
      if (fbBadge) fbBadge.innerHTML = '';

      const bankInp = document.getElementById('bankNameInput');
      if (bankInp) {
        bankInp.value = bankVal;
        bankInp.classList.remove('border-red-500');
      }

      updateTafqeet();
      applyNumberStylesToInputs();
      if (typeof fitAllDynamicFields === 'function') fitAllDynamicFields();

      const btnPrev = document.getElementById('btnPrevVoucher');
      if (btnPrev) {
        btnPrev.disabled = false;
        btnPrev.classList.toggle('opacity-50', currentIndex === 0);
      }
      const btnNext = document.getElementById('btnNextVoucher');
      if (btnNext) {
        btnNext.disabled = false;
        btnNext.classList.toggle('opacity-50', false);
      }
    }

    function navigateVoucher(direction) {
      triggerHapticPulse();
      if (!vouchersList || vouchersList.length === 0) {
        showToastNotification('ℹ️ لا توجد سندات مسجلة سابقة في السجل بعد');
        return;
      }
      if (currentIndex === -1 || currentIndex >= vouchersList.length) {
        if (direction === -1) {
          loadVoucher(vouchersList.length - 1);
          const lastV = vouchersList[vouchersList.length - 1];
          showToastNotification(`📑 ${lastV.voucherNatureLabel || 'سند'} #${lastV.id} (${vouchersList.length} من ${vouchersList.length})`);
        } else {
          showToastNotification('✨ أنت الآن في أحدث سند (سند جديد فارغ جاهز للإدخال)');
        }
        return;
      }
      const newIndex = currentIndex + direction;
      if (newIndex >= 0 && newIndex < vouchersList.length) {
        loadVoucher(newIndex);
        const curV = vouchersList[newIndex];
        showToastNotification(`📑 ${curV.voucherNatureLabel || 'سند'} #${curV.id} (${newIndex + 1} من ${vouchersList.length})`);
      } else if (newIndex >= vouchersList.length) {
        startNewVoucher();
      } else if (newIndex < 0) {
        showToastNotification('⏮️ هذا هو أول وأقدم سند مسجل في السجل');
      }
    }

    // ════════════════════════════════════════════════════════════════
    // استلام وتفريغ بيانات السداد الفوري القادمة من فاتورة المبيعات / المشتريات / POS
    // ════════════════════════════════════════════════════════════════
    function initPendingPosPayment() {
      try {
        const urlParams = new URLSearchParams(window.location.search);
        let party = urlParams.get('party');
        let amount = urlParams.get('amount');
        let cur = urlParams.get('cur') || 'SDG';
        let ref = urlParams.get('ref') || urlParams.get('invoice');
        let notes = urlParams.get('notes');
        let warehouse = urlParams.get('warehouse');

        if (!party && sessionStorage.getItem('pending_pos_voucher')) {
          try {
            const stored = JSON.parse(sessionStorage.getItem('pending_pos_voucher'));
            party = stored.party;
            amount = stored.amount;
            cur = stored.cur || cur;
            ref = stored.ref || stored.invoiceNo;
            notes = stored.notes;
            warehouse = stored.warehouse || warehouse;
            sessionStorage.removeItem('pending_pos_voucher');
          } catch(e) {}
        }

        if (party || amount) {
          window._activePosPaymentPayload = { party, amount, cur, ref, notes, warehouse };

          if (ref) {
            const banner = document.getElementById('linkedInvoiceBanner');
            const bannerTxt = document.getElementById('linkedInvoiceText');
            if (banner && bannerTxt) {
              bannerTxt.textContent = `مرتبط بسداد الفاتورة #${ref} (${party || ''})`;
              banner.classList.remove('hidden');
              banner.classList.add('flex');
            }
          }

          // 1. ضمان تسجيل حساب العميل القادم من المبيعات في accountsDB حتى يقبل الحفظ فوراً
          if (party && typeof accountsDB !== 'undefined' && Array.isArray(accountsDB)) {
            const cleanParty = party.trim();
            const exists = accountsDB.some(a => a && a.name && (a.name.trim() === cleanParty || (typeof normalizeArabicText === 'function' && normalizeArabicText(a.name) === normalizeArabicText(cleanParty))));
            if (!exists && cleanParty) {
              accountsDB.unshift({
                id: 'cust_pos_' + Date.now(),
                name: cleanParty,
                category: 'العملاء والزبائن',
                sdgBalance: 0,
                normalBalance: 'debit',
                balanceType: ''
              });
            }
          }

          // 1.5 تفريغ المستودع / مركز الكلفة إذا أُرسل من شاشة المبيعات
          if (warehouse) {
            const ccInp = document.getElementById('costCenterInput');
            if (ccInp) {
              ccInp.value = warehouse;
              ccInp.classList.remove('mv-ghost-default');
            }
          }

          // 2. تفريغ الاسم (اسم العميل الذي تم البيع له) في خانة (من حساب)
          if (party) {
            const fromInp = document.getElementById('fromSearchInput');
            if (fromInp) {
              fromInp.value = party;
              if (typeof selectAcc === 'function') {
                selectAcc('from', party);
              }
            }
          }

          // 3. تفريغ المبلغ في المبلغ المحلي (#sdgAmount) وحساب المكافئ بالدولار تلقائياً
          if (amount !== null && amount !== undefined && amount !== '') {
            const numAmt = parseFloat(String(amount).replace(/,/g, '')) || 0;
            const sdgInp = document.getElementById('sdgAmount');
            if (sdgInp) {
              sdgInp.value = numAmt;
              if (typeof handleAmountInput === 'function') handleAmountInput('sdg');
            }
            if (typeof updateTafqeet === 'function') updateTafqeet();
          }

          // 4. المستلم الافتراضي (الخزينة النقدية أو البنك)
          const toInp = document.getElementById('toSearchInput');
          if (toInp && !toInp.value) {
            const foundSafe = (typeof accountsDB !== 'undefined' && Array.isArray(accountsDB))
              ? accountsDB.find(a => a && a.name && (a.name.includes('خزينة') || a.name.includes('كاش') || a.name.includes('بنك الخرطوم')))
              : null;
            const defCash = foundSafe ? foundSafe.name : 'خزينة المركز الرئيسي - كاش';
            if (typeof accountsDB !== 'undefined' && Array.isArray(accountsDB) && !foundSafe) {
              accountsDB.push({ id: 'safe_default_pos', name: defCash, category: 'الخزن', sdgBalance: 0, normalBalance: 'debit', balanceType: '' });
            }
            if (typeof selectAcc === 'function') selectAcc('to', defCash);
            else toInp.value = defCash;
          }

          // 5. البيان
          const notesInp = document.getElementById('voucherNotes');
          if (notesInp) {
            notesInp.value = notes || (ref ? ('سداد فاتورة مبيعات #' + ref + ' - العميل: ' + (party || '')) : ('سداد مبيعات - ' + (party || '')));
          }

          if (typeof updateAccountBalanceBadges === 'function') updateAccountBalanceBadges();
          if (typeof applyNumberStylesToInputs === 'function') applyNumberStylesToInputs();
          if (typeof fitAllDynamicFields === 'function') fitAllDynamicFields();

          if (typeof showToastNotification === 'function') {
            showToastNotification('⚡ تم تجهيز حركة مالية لسداد الفاتورة — العميل: ' + (party || '') + ' | المبلغ: ' + Number(parseFloat(String(amount || 0).replace(/,/g, '')) || 0).toLocaleString());
          }
        }
      } catch(err) {
        console.warn('initPendingPosPayment error:', err);
      }
    }

    function startNewVoucher() {
      currentDraftIdempotencyKey = generateUUID();
      window._confirmedSimilarTransferRef = '';
      clearLinkedInvoiceRef();
      if (typeof removeAttachedReceipt === 'function') removeAttachedReceipt();

      // ترقيم متسلسل دقيق موحد متصل مع اللابتوب والسيرفر سواء أونلاين أو أوفلاين
      const nextId = computeNextVoucherSequentialNo(false);

      // 1. رقم السند والتاريخ اليومي وسعر الدولار من قاعدة البيانات
      const displayEl = document.getElementById('currentVoucherDisplay');
      if (displayEl) displayEl.textContent = nextId;
      const tagEl = document.getElementById('voucherTag');
      if (tagEl) tagEl.textContent = isOfflineMode ? 'سند جديد (أوفلاين)' : 'سند جديد';
      const dateEl = document.getElementById('voucherDate');
      if (dateEl) dateEl.value = new Date().toISOString().slice(0, 10);
      updateVoucherDateDisplay();

      const rateEl = document.getElementById('usdRate');
      if (rateEl && window.SYSTEM_USD_RATE > 1) {
        rateEl.value = window.SYSTEM_USD_RATE;
      }
      const ccNewEl = document.getElementById('costCenterInput');
      if (ccNewEl) {
        if (!ccNewEl.value) ccNewEl.value = 'مخزن بحري الرئيسي';
        ccNewEl.classList.add('mv-ghost-default');
      }

      // 2. تصفير ومسح كافة المبالغ المالية تماماً
      const sdgEl = document.getElementById('sdgAmount');
      if (sdgEl) sdgEl.value = '';
      const usdEl = document.getElementById('usdAmount');
      if (usdEl) usdEl.value = '';

      // 3. تصفير ومسح حقول الحسابات (من حساب وإلى حساب)
      const fromInp = document.getElementById('fromSearchInput');
      if (fromInp) {
        fromInp.value = '';
        fromInp.placeholder = 'اضغط لاختيار أو كتابة اسم الحساب...';
        fromInp.scrollTop = 0;
        fromInp.scrollLeft = 0;
      }
      const toInp = document.getElementById('toSearchInput');
      if (toInp) {
        toInp.value = '';
        toInp.placeholder = 'اضغط لاختيار أو كتابة اسم المستلم...';
        toInp.scrollTop = 0;
        toInp.scrollLeft = 0;
      }
      const fromClear = document.getElementById('fromClearBtn');
      if (fromClear) fromClear.classList.add('hidden');
      const toClear = document.getElementById('toClearBtn');
      if (toClear) toClear.classList.add('hidden');

      // 4. تصفير بيانات الأرصدة التقديرية وأسماء المشاركة
      updateAccountBalanceBadges();

      // 5. تصفير رقم التحويلة والبنك والبيان
      const transEl = document.getElementById('transferInput');
      if (transEl) {
        transEl.value = '';
        handleTransferInputScale('');
      }
      const badge = document.getElementById('transferFeedbackBadge');
      if (badge) badge.innerHTML = '';
      const bankEl = document.getElementById('bankNameInput');
      if (bankEl) bankEl.value = '';
      const notesEl = document.getElementById('voucherNotes');
      if (notesEl) notesEl.value = '';

      // 6. إخفاء التنبيهات والتفقيط
      const mediaToast = document.getElementById('mediaToast');
      if (mediaToast) mediaToast.classList.add('hidden');
      const tafqeetEl = document.getElementById('tafqeetDisplay');
      if (tafqeetEl) tafqeetEl.classList.add('hidden');

      // 7. ضبط أزرار التنقل (سهم يمين وسهم شمال)
      const btnNext = document.getElementById('btnNextVoucher');
      if (btnNext) {
        btnNext.disabled = false;
        btnNext.classList.add('opacity-50');
      }
      const btnPrev = document.getElementById('btnPrevVoucher');
      if (btnPrev) {
        btnPrev.disabled = false;
        btnPrev.classList.toggle('opacity-50', vouchersList.length === 0);
      }

      setPaymentType('transfer');
      currentIndex = vouchersList.length;

      applyNumberStylesToInputs();
      if (typeof fitAllDynamicFields === 'function') fitAllDynamicFields();
      playFeedbackTone('tap');
      showToastNotification(`✨ تم فتح سند جديد فارغ وممسوح (${nextId})!\nالشاشة جاهزة لإدخال بيانات الحركة المالية.`);
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
      const bankLabel = document.getElementById('bankNameLabel');
      const input = document.getElementById('transferInput');
      const bankInput = document.getElementById('bankNameInput');

      if (type === 'transfer') {
        container.classList.remove('hidden');
        if (refLabel) refLabel.innerHTML = '<i class="fa-solid fa-receipt text-amber-400 text-xs"></i> <span>رقم التحويل:</span>';
        if (input) {
          input.placeholder = 'رقم التحويل';
          input.setAttribute('inputmode', 'numeric');
        }
        if (bankLabel) bankLabel.innerHTML = '<i class="fa-solid fa-building-columns text-cyan-400 text-xs"></i> <span>اسم البنك:</span>';
        if (bankInput) bankInput.placeholder = 'اسم البنك';
      } else if (type.includes('cheque')) {
        container.classList.remove('hidden');
        if (refLabel) refLabel.innerHTML = '<i class="fa-solid fa-money-check text-emerald-400 text-xs"></i> <span>رقم الشيك:</span>';
        if (input) {
          input.placeholder = 'رقم الشيك (إلزامي)';
          input.removeAttribute('inputmode');
        }
        if (bankLabel) bankLabel.innerHTML = '<i class="fa-solid fa-building-columns text-cyan-400 text-xs"></i> <span>اسم بنك الشيك:</span>';
        if (bankInput) bankInput.placeholder = 'اسم بنك الشيك (إلزامي)';
      } else {
        container.classList.add('hidden');
      }
    }

    const CURRENCY_META_MAP = {
      'SDG': { title: 'المبلغ دولار', badge: '$ USD', rateSymbol: '$', defaultRate: parseFloat(window.SYSTEM_USD_RATE) || 5999 },
      'USD': { title: 'المبلغ دولار', badge: '$ USD', rateSymbol: '$', defaultRate: parseFloat(window.SYSTEM_USD_RATE) || 5999 },
      'SAR': { title: 'المبلغ بالريال', badge: 'ر.س SAR', rateSymbol: 'ر.س', defaultRate: 706 },
      'AED': { title: 'المبلغ بالدرهم', badge: 'د.إ AED', rateSymbol: 'د.إ', defaultRate: 721 },
      'EGP': { title: 'المبلغ بالجنيه المصري', badge: 'ج.م EGP', rateSymbol: 'ج.م', defaultRate: 54 },
      'EUR': { title: 'المبلغ باليورو', badge: '€ EUR', rateSymbol: '€', defaultRate: 2880 }
    };

    function selectCurrency(code, recalculate = true) {
      currentCurrency = code || 'SDG';
      const primarySel = document.getElementById('primaryCurrencySelect');
      if (primarySel && primarySel.value !== currentCurrency) {
        primarySel.value = currentCurrency;
      }
      const meta = CURRENCY_META_MAP[currentCurrency] || CURRENCY_META_MAP['USD'];
      const foreignTitle = document.getElementById('foreignAmountTitle');
      const foreignBadge = document.getElementById('foreignCurrencyBadge');
      const rateSym = document.getElementById('rateSymbolLabel');

      if (foreignTitle) foreignTitle.textContent = meta.title;
      if (foreignBadge) foreignBadge.textContent = meta.badge;
      if (rateSym) rateSym.textContent = meta.rateSymbol;

      if (recalculate) {
        // جلب سعر الصرف من قاعدة البيانات المحمل من السيرفر
        let targetRate = meta.defaultRate;
        if (Array.isArray(window.PHP_CURRENCIES)) {
          const searchCode = (currentCurrency === 'SDG') ? 'USD' : currentCurrency;
          const foundCur = window.PHP_CURRENCIES.find(c => String(c.code || '').toUpperCase() === searchCode);
          if (foundCur && parseFloat(foundCur.rate) > 0) {
            targetRate = parseFloat(foundCur.rate);
          }
        }
        if (currentCurrency === 'SDG' || currentCurrency === 'USD') {
          const sysUsd = parseFloat(window.SYSTEM_USD_RATE) || parseFloat(localStorage.getItem('magaiees_system_usd_rate'));
          if (sysUsd > 1) targetRate = sysUsd;
        }
        const rateInp = document.getElementById('usdRate');
        if (rateInp && targetRate > 0) {
          rateInp.value = targetRate;
        }
        calculateExchange('rate');
        updateTafqeet();
        updateAccountBalanceBadges();
      }
      if (typeof fitCurrencySelectSize === 'function') fitCurrencySelectSize();
      if (typeof fitUsdRateSize === 'function') fitUsdRateSize();
    }

    function parseRawNumber(str) {
      if (str === null || str === undefined || str === '') return 0;
      if (typeof str === 'number') return isNaN(str) ? 0 : str;
      let s = String(str).trim();
      s = s.replace(/[^\d.,-]/g, '');
      if (s === '' || s === '-') return 0;

      if (s.indexOf('.') !== -1 && s.indexOf(',') !== -1) {
        if (s.indexOf('.') < s.indexOf(',')) {
          s = s.replace(/\./g, '').replace(',', '.');
        } else {
          s = s.replace(/,/g, '');
        }
      } else if (s.indexOf(',') !== -1) {
        const parts = s.split(',');
        if (parts.length > 2 || (parts[1] && parts[1].length === 3)) {
          s = s.replace(/,/g, '');
        } else {
          s = s.replace(',', '.');
        }
      }
      const num = parseFloat(s);
      return isNaN(num) ? 0 : num;
    }

    let amountTypingTimers = {};
    function onAmountInputLive(type) {
      const card = document.getElementById(type === 'sdg' ? 'sdgAmountCard' : 'usdAmountCard');
      if (!card) return;
      const cls = type === 'sdg' ? 'is-typing-sdg' : 'is-typing-usd';
      card.classList.add(cls);
      if (amountTypingTimers[type]) clearTimeout(amountTypingTimers[type]);
      amountTypingTimers[type] = setTimeout(() => {
        card.classList.remove(cls);
      }, 1000);
    }
    function onAmountInputBlur(type) {
      const card = document.getElementById(type === 'sdg' ? 'sdgAmountCard' : 'usdAmountCard');
      if (!card) return;
      const cls = type === 'sdg' ? 'is-typing-sdg' : 'is-typing-usd';
      card.classList.remove(cls);
      if (amountTypingTimers[type]) clearTimeout(amountTypingTimers[type]);
    }

    let transferTypingTimer = null;
    function onTransferInputLive(val) {
      const inp = document.getElementById('transferInput');
      if (!inp) return;
      inp.classList.add('input-typing-glow-amber');
      if (transferTypingTimer) clearTimeout(transferTypingTimer);
      transferTypingTimer = setTimeout(() => {
        inp.classList.remove('input-typing-glow-amber');
      }, 1000);
    }
    function onTransferInputBlur() {
      const inp = document.getElementById('transferInput');
      if (!inp) return;
      inp.classList.remove('input-typing-glow-amber');
      if (transferTypingTimer) clearTimeout(transferTypingTimer);
    }

    let bankTypingTimer = null;
    function onBankInputLive(val) {
      const inp = document.getElementById('bankNameInput');
      if (!inp) return;
      inp.classList.add('input-typing-glow-cyan');
      if (bankTypingTimer) clearTimeout(bankTypingTimer);
      bankTypingTimer = setTimeout(() => {
        inp.classList.remove('input-typing-glow-cyan');
      }, 1000);
    }
    function onBankInputBlur() {
      const inp = document.getElementById('bankNameInput');
      if (!inp) return;
      inp.classList.remove('input-typing-glow-cyan');
      if (bankTypingTimer) clearTimeout(bankTypingTimer);
    }

    let notesTypingTimer = null;
    function onNotesInputLive(val) {
      const inp = document.getElementById('voucherNotes');
      if (!inp) return;
      inp.classList.add('input-typing-glow-indigo');
      if (notesTypingTimer) clearTimeout(notesTypingTimer);
      notesTypingTimer = setTimeout(() => {
        inp.classList.remove('input-typing-glow-indigo');
      }, 1000);
    }
    function onNotesInputBlur() {
      const inp = document.getElementById('voucherNotes');
      if (!inp) return;
      inp.classList.remove('input-typing-glow-indigo');
      if (notesTypingTimer) clearTimeout(notesTypingTimer);
    }

    window.onAmountInputLive = onAmountInputLive;
    window.onAmountInputBlur = onAmountInputBlur;
    window.onTransferInputLive = onTransferInputLive;
    window.onTransferInputBlur = onTransferInputBlur;
    window.onBankInputLive = onBankInputLive;
    window.onBankInputBlur = onBankInputBlur;
    window.onNotesInputLive = onNotesInputLive;
    window.onNotesInputBlur = onNotesInputBlur;

    function handleAmountInput(source) {
      calculateExchange(source);
      updateTafqeet();
      updateAccountBalanceBadges();
      if (source === 'sdg') {
        onAmountInputLive('sdg');
      } else if (source === 'foreign' || source === 'usd') {
        onAmountInputLive('usd');
      }
    }

    function calculateExchange(source) {
      const rate = parseFloat(document.getElementById('usdRate').value) || parseFloat(window.SYSTEM_USD_RATE) || 5999;
      const sdg = document.getElementById('sdgAmount');
      const foreign = document.getElementById('usdAmount');
      if (rate <= 0) return;

      if (source === 'foreign') {
        const f = parseRawNumber(foreign.value);
        if (f > 0) {
          const calc = f * rate;
          sdg.value = formatNumberWithConfig(calc, sdgNumberConfig);
        } else {
          sdg.value = '';
        }
      } else if (source === 'sdg') {
        const s = parseRawNumber(sdg.value);
        if (s > 0) {
          const calc = s / rate;
          foreign.value = formatNumberWithConfig(calc, usdNumberConfig);
        } else {
          foreign.value = '';
        }
      } else if (source === 'rate') {
        const f = parseRawNumber(foreign.value);
        if (f > 0) {
          const calc = f * rate;
          sdg.value = formatNumberWithConfig(calc, sdgNumberConfig);
        } else {
          const s = parseRawNumber(sdg.value);
          if (s > 0) {
            foreign.value = formatNumberWithConfig(s / rate, usdNumberConfig);
          }
        }
      }
    }

    function recalculateAmountsWithNewConfig() {
      const s = parseRawNumber(document.getElementById('sdgAmount').value);
      const f = parseRawNumber(document.getElementById('usdAmount').value);
      if (s > 0) {
        document.getElementById('sdgAmount').value = formatNumberWithConfig(s, sdgNumberConfig);
      }
      if (f > 0) {
        document.getElementById('usdAmount').value = formatNumberWithConfig(f, usdNumberConfig);
      }
      updateTafqeet();
      updateAccountBalanceBadges();
    }

    // ════════════════════════════════════════════════════════════════
    // محرك التفقيط المالي العربي المتقدم فائق الدقة (Master Arabic Tafqeet 4.0)
    // - يدعم الأرقام بالمليارات والترليونات
    // - يدعم القواعد النحوية العربية (المثنى، جمع التكسير، والتمييز المنصوب)
    // - يدعم كافة العملات (SDG, USD, SAR, AED, EUR) والأجزاء العشرية
    // ════════════════════════════════════════════════════════════════
    function arabicNumberToWords(n, currency = 'SDG') {
      if (isNaN(n) || n === 0) return 'صفر';
      n = Math.abs(Number(n));
      const intPart = Math.floor(n);
      const decPart = Math.round((n - intPart) * 100);

      const ones = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة'];
      const teens = ['عشرة', 'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر'];
      const tens = ['', '', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
      const hundreds = ['', 'مائة', 'مائتان', 'ثلاثمائة', 'أربعمائة', 'خمسمائة', 'ستمائة', 'سبعمائة', 'ثمانمائة', 'تسعمائة'];

      function convertGroup(val) {
        let s = '';
        const h = Math.floor(val / 100);
        const rem = val % 100;
        if (h > 0) s += hundreds[h];
        if (rem > 0) {
          if (s) s += ' و';
          if (rem < 10) s += ones[rem];
          else if (rem < 20) s += teens[rem - 10];
          else {
            const u = rem % 10;
            const t = Math.floor(rem / 10);
            if (u > 0) s += ones[u] + ' و';
            s += tens[t];
          }
        }
        return s;
      }

      const scales = [
        { val: 1000000000000, singular: 'ترليون', dual: 'ترليونان', plural: 'ترليونات', accusative: 'ترليوناً' },
        { val: 1000000000, singular: 'مليار', dual: 'ملياران', plural: 'مليارات', accusative: 'ملياراً' },
        { val: 1000000, singular: 'مليون', dual: 'مليونان', plural: 'ملايين', accusative: 'مليوناً' },
        { val: 1000, singular: 'ألف', dual: 'ألفان', plural: 'آلاف', accusative: 'ألفاً' }
      ];

      let parts = [];
      let remInt = intPart;

      for (let sc of scales) {
        if (remInt >= sc.val) {
          const count = Math.floor(remInt / sc.val);
          remInt = remInt % sc.val;
          if (count === 1) parts.push(sc.singular);
          else if (count === 2) parts.push(sc.dual);
          else if (count >= 3 && count <= 10) parts.push(convertGroup(count) + ' ' + sc.plural);
          else parts.push(convertGroup(count) + ' ' + sc.accusative);
        }
      }

      if (remInt > 0) {
        parts.push(convertGroup(remInt));
      }

      let intWords = parts.join(' و');

      // تفقيط مسميات العملات
      const currNames = {
        'SDG': { main: 'جنيه سوداني', accusative: 'جنيهاً سودانياً', sub: 'قرش', subAcc: 'قرشاً' },
        'USD': { main: 'دولار أمريكي', accusative: 'دولاراً أمريكياً', sub: 'سنت', subAcc: 'سنتاً' },
        'SAR': { main: 'ريال سعودي', accusative: 'ريالاً سعودياً', sub: 'هللة', subAcc: 'هللة' },
        'AED': { main: 'درهم إماراتي', accusative: 'درهماً إماراتياً', sub: 'فلس', subAcc: 'فلساً' },
        'EUR': { main: 'يورو أوروبي', accusative: 'يورو أوروبي', sub: 'سنت', subAcc: 'سنتاً' }
      };

      const cObj = currNames[currency] || currNames['SDG'];
      const cLabel = intPart > 10 ? cObj.accusative : cObj.main;

      let result = intWords ? (intWords + ' ' + cLabel) : '';

      if (decPart > 0) {
        const decWords = convertGroup(decPart);
        const subLabel = decPart > 10 ? cObj.subAcc : cObj.sub;
        if (result) {
          result += ' و' + decWords + ' ' + subLabel;
        } else {
          result = decWords + ' ' + subLabel;
        }
      }

      return result || 'صفر';
    }
    window.arabicNumberToWords = arabicNumberToWords;

    function updateTafqeet() {
      const curSelect = document.getElementById('primaryCurrencySelect')?.value || currentCurrency || 'SDG';
      let num = 0;
      if (curSelect === 'USD' || curSelect === 'foreign') {
        num = parseRawNumber(document.getElementById('usdAmount')?.value || 0);
      } else {
        num = parseRawNumber(document.getElementById('sdgAmount')?.value || 0);
      }

      const disp = document.getElementById('tafqeetDisplay');
      const txt = document.getElementById('tafqeetText');
      if (!disp) return;

      if (!num || num <= 0) {
        disp.classList.add('hidden');
        if (txt) txt.textContent = '';
        return;
      }

      const words = arabicNumberToWords(num, curSelect);
      if (txt) {
        txt.textContent = `فقط ${words} لا غير`;
      } else {
        disp.textContent = `فقط ${words} لا غير`;
      }
      disp.classList.remove('hidden');
    }
    window.updateTafqeet = updateTafqeet;

    // ════════════════════════════════════════════════════════════════
    // البحث الفوري عن الحسابات مع تبويبات التصنيف المباشرة
    // (العملاء، الموردين، المصروفات، الزبائن، البنوك، الخزن، وكل الحسابات)
    // ════════════════════════════════════════════════════════════════
    let activeCategoryFilter = { from: 'all', to: 'all' };

    function matchesCategory(accountCategory, filterKey) {
      if (!filterKey || filterKey === 'all') return true;
      const cat = (accountCategory || '').toLowerCase();
      if (filterKey === 'customers') {
        return cat.includes('عميل') || cat.includes('عملاء') || cat.includes('زبون') || cat.includes('زبائن');
      }
      if (filterKey === 'suppliers') {
        return cat.includes('مورد') || cat.includes('موردين');
      }
      if (filterKey === 'expenses') {
        return cat.includes('مصروف') || cat.includes('مصروفات') || cat.includes('نفقات');
      }
      if (filterKey === 'banks') {
        return cat.includes('بنك') || cat.includes('مصرف') || cat.includes('بنوك');
      }
      if (filterKey === 'safes') {
        return cat.includes('خزن') || cat.includes('خزينة') || cat.includes('صندوق') || cat.includes('خزائن');
      }
      if (filterKey === 'general') {
        return true;
      }
      return true;
    }

    function setAccountCategoryFilter(target, cat, ev) {
      if (ev) {
        ev.stopPropagation();
        ev.preventDefault();
      }
      activeCategoryFilter[target] = cat;
      liveSearchAccount(target);
      const input = document.getElementById(target + 'SearchInput');
      if (input) input.focus();
    }

    async function onAccountInputFocus(target) {
      liveSearchAccount(target);
      // سحب فوري مباشر من الداتابيز في الخلفية لضمان ظهور أي عميل/مورد/مصروف أضيف الآن على اللابتوب
      try {
        await fetchLiveDbData(false);
        const dd = document.getElementById(target + 'Dropdown');
        if (dd && !dd.classList.contains('hidden')) {
          liveSearchAccount(target);
        }
      } catch(e) {}
    }

    function getCategoryBadge(cat) {
      const c = (cat || '').toLowerCase();
      if (c.includes('بنك') || c.includes('مصرف') || c.includes('بنوك')) {
        return `<span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-cyan-950 text-cyan-300 border border-cyan-800/60 flex items-center gap-0.5"><i class="fa-solid fa-building-columns text-[8px]"></i> ${cat}</span>`;
      }
      if (c.includes('خزن') || c.includes('صندوق') || c.includes('خزينة') || c.includes('خزائن')) {
        return `<span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-amber-950 text-amber-300 border border-amber-800/60 flex items-center gap-0.5"><i class="fa-solid fa-vault text-[8px]"></i> ${cat}</span>`;
      }
      if (c.includes('عميل') || c.includes('عملاء') || c.includes('زبون') || c.includes('زبائن')) {
        return `<span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-800/60 flex items-center gap-0.5"><i class="fa-solid fa-user text-[8px]"></i> ${cat}</span>`;
      }
      if (c.includes('مورد') || c.includes('موردين')) {
        return `<span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-purple-950 text-purple-300 border border-purple-800/60 flex items-center gap-0.5"><i class="fa-solid fa-truck-field text-[8px]"></i> ${cat}</span>`;
      }
      if (c.includes('مصروف') || c.includes('مصروفات') || c.includes('نفقات')) {
        return `<span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-rose-950 text-rose-300 border border-rose-800/60 flex items-center gap-0.5"><i class="fa-solid fa-receipt text-[8px]"></i> ${cat}</span>`;
      }
      return `<span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-slate-800 text-slate-300 border border-slate-700 flex items-center gap-0.5"><i class="fa-solid fa-folder text-[8px]"></i> ${cat}</span>`;
    }

    function liveSearchAccount(target) {
      const input = document.getElementById(target + 'SearchInput');
      const dropdown = document.getElementById(target + 'Dropdown');
      const clearBtn = document.getElementById(target + 'ClearBtn');
      if (!input || !dropdown) return;
      const query = input.value.trim().toLowerCase();

      if (clearBtn) {
        clearBtn.classList.toggle('hidden', query.length === 0);
      }

      // إغلاق القائمة الأخرى لمنع أي تداخل
      const otherTarget = target === 'from' ? 'to' : 'from';
      const otherDropdown = document.getElementById(otherTarget + 'Dropdown');
      if (otherDropdown) otherDropdown.classList.add('hidden');

      const countAll = accountsDB.length;
      const countCustomers = accountsDB.filter(a => matchesCategory(a.category, 'customers')).length;
      const countSuppliers = accountsDB.filter(a => matchesCategory(a.category, 'suppliers')).length;
      const countExpenses = accountsDB.filter(a => matchesCategory(a.category, 'expenses')).length;
      const countBanks = accountsDB.filter(a => matchesCategory(a.category, 'banks')).length;
      const countSafes = accountsDB.filter(a => matchesCategory(a.category, 'safes')).length;

      const currentCat = activeCategoryFilter[target] || 'all';

      const tabs = [
        { key: 'all', label: 'الكل', count: countAll, icon: 'fa-layer-group' },
        { key: 'customers', label: 'العملاء والزبائن', count: countCustomers, icon: 'fa-user' },
        { key: 'suppliers', label: 'الموردين', count: countSuppliers, icon: 'fa-truck-field' },
        { key: 'expenses', label: 'المصروفات', count: countExpenses, icon: 'fa-receipt' },
        { key: 'banks', label: 'البنوك', count: countBanks, icon: 'fa-building-columns' },
        { key: 'safes', label: 'الخزن', count: countSafes, icon: 'fa-vault' },
        { key: 'general', label: 'كل الحسابات', count: countAll, icon: 'fa-book-bookmark' },
      ];

      const tabsHtml = `
        <div class="sticky top-0 bg-slate-950/95 backdrop-blur-md border-b border-slate-800 p-1.5 flex items-center gap-1.5 overflow-x-auto no-scrollbar z-10 select-none shadow-md">
          ${tabs.map(t => {
            const isActive = (currentCat === t.key);
            const activeClass = isActive
              ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-slate-950 font-black shadow-md shadow-amber-500/30'
              : 'bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 font-bold';
            return `
              <button type="button" onmousedown="setAccountCategoryFilter('${target}', '${t.key}', event)" onclick="setAccountCategoryFilter('${target}', '${t.key}', event)" class="shrink-0 px-2 py-1 rounded-xl text-[10.5px] flex items-center gap-1 transition ${activeClass}">
                <i class="fa-solid ${t.icon} text-[9px]"></i>
                <span>${t.label}</span>
                <span class="text-[9px] px-1 rounded-full ${isActive ? 'bg-black/30 text-white' : 'bg-slate-800 text-slate-400'}">${t.count}</span>
              </button>
            `;
          }).join('')}
        </div>
      `;

      let results = accountsDB.filter(a => matchesCategory(a.category, currentCat));
      if (query) {
        results = results.filter(a => a.name.toLowerCase().includes(query) || a.category.toLowerCase().includes(query));
      }

      const rate = parseFloat(document.getElementById('usdRate').value) || parseFloat(window.SYSTEM_USD_RATE) || 5999;

      const itemsHtml = results.length === 0
        ? `<div class="p-4 text-slate-400 text-center text-xs space-y-1">
            <i class="fa-solid fa-magnifying-glass text-slate-600 text-base"></i>
            <div>لا توجد حسابات مطابقة للبحث في هذا التصنيف</div>
            <div class="text-[10px] text-slate-500">اختر "الكل" أو تأكد من الاسم المكتوب</div>
           </div>`
        : results.map(a => {
            const safeName = a.name.replace(/'/g, "\\'");
            return `
              <div onmousedown="event.preventDefault(); selectAcc('${target}', '${safeName}')" onclick="selectAcc('${target}', '${safeName}')" class="p-3 hover:bg-slate-900 active:bg-slate-800 border-b border-slate-800/80 cursor-pointer flex items-center justify-between text-xs transition select-none">
                <div class="min-w-0 pr-1 space-y-0.5">
                  <div class="font-black text-white text-[15px] truncate">${a.name}</div>
                  <div class="flex items-center gap-1">${getCategoryBadge(a.category)}</div>
                </div>
                <div class="text-left font-mono shrink-0 pl-1">
                  <div class="text-amber-400 font-bold text-[11px]">${Number(a.sdgBalance).toLocaleString()} ج.س</div>
                  <div class="text-emerald-400 font-bold text-[10px]">$${(Number(a.sdgBalance) / rate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                </div>
              </div>
            `;
          }).join('');

      dropdown.innerHTML = tabsHtml + itemsHtml;
      dropdown.classList.remove('hidden');
    }

    function clearAccountSearch(target) {
      const input = document.getElementById(target + 'SearchInput');
      const dropdown = document.getElementById(target + 'Dropdown');
      const clearBtn = document.getElementById(target + 'ClearBtn');
      if (input) {
        input.value = '';
        input.scrollTop = 0;
        input.scrollLeft = 0;
        if (typeof fitAccountInputSize === 'function') fitAccountInputSize(target);
        input.focus();
      }
      if (dropdown) dropdown.classList.add('hidden');
      if (clearBtn) clearBtn.classList.add('hidden');
      updateAccountBalanceBadges();
      liveSearchAccount(target);
    }

    function selectAcc(target, name) {
      const input = document.getElementById(target + 'SearchInput');
      const clearBtn = document.getElementById(target + 'ClearBtn');
      const dropdown = document.getElementById(target + 'Dropdown');
      const card = document.getElementById(target + 'AccountCard');

      if (input) {
        input.value = name;
        input.classList.remove('border-red-500');
        // تثبيت الإزاحات الأفقية والرأسية فوراً لمنع أي انزلاق أو تشويش
        input.scrollTop = 0;
        input.scrollLeft = 0;
        input.style.lineHeight = '56px';
        if (typeof fitAccountInputSize === 'function') fitAccountInputSize(target);
        // إغلاق لوحة مفاتيح الجوال فوراً حتى لا تقفز الشاشة للأعلى أو للأسفل
        input.blur();
      }

      if (clearBtn) clearBtn.classList.remove('hidden');
      if (dropdown) dropdown.classList.add('hidden');

      updateAccountBalanceBadges();
      if (typeof fitAccountHeaderRows === 'function') fitAccountHeaderRows();

      // تثبيت مكان البطاقة على الشاشة تماماً لكي لا يختفي الحساب تحت الهيدر أو خارج الشاشة
      if (card) {
        try {
          card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } catch(e) {}
      }
    }

    // التحقق الصارم من الحساب عند انتهاء الكتابة اليدوية (Blur) لضمان عدم إدخال حساب غير مسجل
    function handleAccountInputBlur(target) {
      setTimeout(() => {
        const input = document.getElementById(target + 'SearchInput');
        if (!input) return;
        const val = (input.value || '').trim();
        if (!val) {
          input.classList.remove('border-red-500');
          if (typeof fitAccountInputSize === 'function') fitAccountInputSize(target);
          updateAccountBalanceBadges();
          return;
        }

        // 1. هل يطابق حساباً مسجلاً بشكل تام؟
        const exactMatch = accountsDB.find(a => a && a.name && (a.name.trim() === val || normalizeArabicText(a.name) === normalizeArabicText(val)));
        if (exactMatch) {
          input.value = exactMatch.name;
          input.classList.remove('border-red-500');
          if (typeof fitAccountInputSize === 'function') fitAccountInputSize(target);
          updateAccountBalanceBadges();
          return;
        }

        // 2. هل يطابق بالذكاء الاصطناعي لحساب مسجل (مثلاً: "احمد تاج" -> "أحمد عثمان تاج الدين")؟
        const best = findBestAccountMatch(val, accountsDB);
        if (best) {
          input.value = best;
          input.classList.remove('border-red-500');
          if (typeof fitAccountInputSize === 'function') fitAccountInputSize(target);
          showToastNotification(`✨ تم تثبيت الحساب المسجل: "${best}"`);
          updateAccountBalanceBadges();
        } else {
          // غير مسجل نهائياً: تنبيه وعدم قبول الحساب غير المسجل
          input.classList.add('border-red-500');
          if (typeof fitAccountInputSize === 'function') fitAccountInputSize(target);
          showToastNotification(`⚠️ الحساب "${val}" غير مسجل في البيانات الأساسية (يجب اختيار حساب مسجل)`);
        }
      }, 200);
    }

    document.addEventListener('click', (e) => {
      if (!e.target.closest('#fromSearchInput') && !e.target.closest('#fromDropdown') && !e.target.closest('#fromClearBtn')) {
        const d = document.getElementById('fromDropdown');
        if (d) d.classList.add('hidden');
      }
      if (!e.target.closest('#toSearchInput') && !e.target.closest('#toDropdown') && !e.target.closest('#toClearBtn')) {
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

    // إشعارات الكاميرا والاستوديو الملتقطة والمختارة (حقيقية فقط)
    let cameraReceiptsDB = [];
    let galleryReceiptsDB = [];

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
        if (items.length === 0) {
          grid.innerHTML = '<div class="col-span-4 p-5 text-center text-slate-400 text-xs font-bold leading-relaxed border border-dashed border-slate-800 rounded-2xl bg-slate-950/60">📷 لا توجد صور ملتقطة بعد.<br><span class="text-cyan-400">اضغط "التقاط صورة 📸" لفتح الكاميرا والتقاط إشعار بنكي حقيقي وتفريغه فورياً.</span></div>';
          const lbl = document.getElementById('cameraCountLabel');
          if (lbl) lbl.textContent = 'جاهز لالتقاط صور الإشعارات';
        } else {
          grid.innerHTML = items.map((c, idx) => `
            <div class="p-1.5 rounded-xl bg-slate-950 border border-indigo-500/40 text-center flex flex-col items-center justify-center gap-1 cursor-pointer hover:border-indigo-400 transition" onclick="previewIntakeItem(${idx}, 'camera')">
              <div class="w-full h-11 rounded-lg bg-indigo-950/80 border border-indigo-400/30 flex flex-col items-center justify-center relative overflow-hidden">
                ${c.imgUrl ? `<img src="${c.imgUrl}" class="w-full h-full object-cover">` : `<i class="fa-solid fa-receipt text-indigo-300 text-xs"></i>`}
                <span class="absolute top-0.5 right-0.5 px-1 rounded bg-black/70 text-[8px] font-mono text-indigo-200">#${idx + 1}</span>
              </div>
              <span class="text-[9px] font-mono font-black text-amber-300 truncate w-full">${c.amount > 0 ? c.amount.toLocaleString() : (c.ref || 'إشعار')}</span>
            </div>
          `).join('');
          const lbl = document.getElementById('cameraCountLabel');
          if (lbl) lbl.textContent = `تم تجهيز: ${items.length} صورة إشعار`;
        }
      } else if (currentIntakeSource === 'gallery') {
        const grid = document.getElementById('galleryThumbnailsGrid');
        if (!grid) return;
        const items = galleryReceiptsDB.slice(0, currentBatchLimit);
        if (items.length === 0) {
          grid.innerHTML = '<div class="col-span-4 p-5 text-center text-slate-400 text-xs font-bold leading-relaxed border border-dashed border-slate-800 rounded-2xl bg-slate-950/60">🖼️ لا توجد صور مختارة من الاستوديو بعد.<br><span class="text-amber-400">اضغط "اختر صور 📁" لاختيار صور إشعاراتك البنكية الحقيقية.</span></div>';
          const lbl = document.getElementById('galleryCountLabel');
          if (lbl) lbl.textContent = 'جاهز لاختيار صور من المعرض';
        } else {
          grid.innerHTML = items.map((c, idx) => `
            <div class="p-1.5 rounded-xl bg-slate-950 border border-amber-500/40 text-center flex flex-col items-center justify-center gap-1 cursor-pointer hover:border-amber-400 transition" onclick="previewIntakeItem(${idx}, 'gallery')">
              <div class="w-full h-11 rounded-lg bg-amber-950/80 border border-amber-400/30 flex flex-col items-center justify-center relative overflow-hidden">
                ${c.imgUrl ? `<img src="${c.imgUrl}" class="w-full h-full object-cover">` : `<i class="fa-solid fa-image text-amber-300 text-xs"></i>`}
                <span class="absolute top-0.5 right-0.5 px-1 rounded bg-black/70 text-[8px] font-mono text-amber-200">#${idx + 1}</span>
              </div>
              <span class="text-[9px] font-mono font-black text-amber-300 truncate w-full">${c.amount > 0 ? c.amount.toLocaleString() : (c.ref || 'إشعار')}</span>
            </div>
          `).join('');
          const lbl = document.getElementById('galleryCountLabel');
          if (lbl) lbl.textContent = `تم اختيار: ${items.length} صورة إشعار من المعرض`;
        }
      } else if (currentIntakeSource === 'whatsapp') {
        renderWhatsAppList();
      }
    }

    function renderWhatsAppList() {
      const container = document.getElementById('whatsAppListContainer');
      if (!container) return;
      const list = whatsappNotificationsDB.slice(0, currentBatchLimit);

      if (list.length === 0) {
        container.innerHTML = '<div class="p-5 text-center text-slate-400 text-xs font-bold leading-relaxed border border-dashed border-slate-800 rounded-2xl bg-slate-950/60">💬 لا توجد إشعارات واتساب بنكية محفوظة حالياً.<br><span class="text-emerald-400">قم بنسخ أي إشعار بنكي من واتساب ثم انقر "لصق وتفريغ ⚡" أعلاه، أو اضغط "فتح واتساب الآن 📲" لنسخ إشعارك.</span></div>';
        const countBadge = document.getElementById('whatsAppCountBadge');
        if (countBadge) countBadge.textContent = '0 إشعار';
        return;
      }

      container.innerHTML = list.map((item, idx) => `
        <div onclick="selectWhatsAppReceipt(${item.id})" class="whatsapp-card p-2.5 rounded-2xl bg-slate-950 border border-slate-800 hover:border-emerald-400 cursor-pointer flex items-center justify-between gap-2">
          <div class="flex items-center gap-2 min-w-0">
            <div class="w-7 h-7 rounded-full bg-emerald-600/30 text-emerald-300 border border-emerald-500/40 flex items-center justify-center font-bold text-xs shrink-0">
              #${idx + 1}
            </div>
            <div class="min-w-0">
              <div class="flex items-center gap-1.5">
                <span class="font-black text-white text-xs truncate">${item.to || '---'}</span>
                <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-400 font-mono">${item.bank || 'بنك'}</span>
              </div>
              <div class="text-[10px] text-slate-400 font-mono mt-0.5 truncate">
                تحويلة: ${item.ref || '---'}
              </div>
            </div>
          </div>
          <div class="text-left shrink-0">
            <div class="font-mono font-black text-emerald-400 text-xs sm:text-sm">${item.amount > 0 ? item.amount.toLocaleString() + ' ج.س' : '---'}</div>
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
      
      const camInp = document.getElementById('cameraCaptureInput');
      if (camInp) {
        camInp.click();
      } else {
        openBatchIntakeModal('camera');
      }
    }

    function handleCameraFileSelected(e) {
      const files = e.target.files;
      if (!files || files.length === 0) return;

      triggerHapticPulse();
      playBankerChime();

      const file = files[0];
      const reader = new FileReader();
      reader.onload = function(evt) {
        const imgData = evt.target.result;
        const cleanName = file.name.replace(/\.[^/.]+$/, "");
        const numMatch = cleanName.match(/\d{6,}/);
        const refNo = numMatch ? numMatch[0] : '';

        const receipt = {
          id: Date.now(),
          bank: '',
          amount: 0,
          from: '',
          to: '',
          ref: refNo,
          imgUrl: imgData,
          fileName: file.name,
          note: `التقاط صورة بالكاميرا (${file.name})`
        };

        cameraReceiptsDB.unshift(receipt);
        renderCurrentSourceContent();
        applyReceiptToVoucherForm(receipt);
        closeBatchIntakeModal();
        showToastNotification('📸 تم التقاط وحفظ صورة الإشعار بنجاح وتم إرفاقها بالسند.');
      };
      reader.readAsDataURL(file);
    }

    function handleGalleryFilesSelected(e) {
      const files = e.target.files;
      if (!files || files.length === 0) return;

      triggerHapticPulse();
      playBankerChime();

      const createdItems = [];
      let pendingFiles = files.length;

      for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const cleanName = file.name.replace(/\.[^/.]+$/, "");
        const numMatch = cleanName.match(/\d{6,}/);
        const refNo = numMatch ? numMatch[0] : '';

        const reader = new FileReader();
        reader.onload = function(evt) {
          const imgData = evt.target.result;
          const receipt = {
            id: Date.now() + i,
            bank: '',
            amount: 0,
            from: '',
            to: '',
            ref: refNo,
            imgUrl: imgData,
            fileName: file.name,
            note: `إشعار استوديو (${file.name})`
          };

          createdItems.push(receipt);
          galleryReceiptsDB.unshift(receipt);
          pendingFiles--;

          if (pendingFiles === 0) {
            renderCurrentSourceContent();
            if (createdItems.length > 0) {
              const primaryItem = createdItems[0];
              applyReceiptToVoucherForm(primaryItem);
              closeBatchIntakeModal();
              showToastNotification(`🖼️ تم تحميل ${createdItems.length} صورة إشعار من الاستوديو بنجاح!`);
            }
          }
        };
        reader.readAsDataURL(file);
      }
    }

    function openStudioDirectly() {
      triggerHapticPulse();
      showToastNotification('🖼️ جاري فتح استوديو الجوال لاختيار صور الإشعارات...');
      const inp = document.getElementById('galleryFileInput');
      if (inp) {
        inp.click();
      } else {
        openBatchIntakeModal('gallery');
      }
    }

    function previewIntakeItem(idx, source) {
      const list = source === 'camera' ? cameraReceiptsDB : galleryReceiptsDB;
      const item = list[idx] || list[0];
      processReceiptWithAI(item, source);
    }

    function selectWhatsAppReceipt(id) {
      const item = whatsappNotificationsDB.find(w => w.id === id) || whatsappNotificationsDB[0];
      processReceiptWithAI(item, 'whatsapp');
    }

    // ════════════════════════════════════════════════════════════════
    // تفريغ الواتساب الذكي وفتح التطبيق المباشر (AI WhatsApp Direct Launcher)
    // ════════════════════════════════════════════════════════════════
    function openWhatsAppDirectly() {
      triggerHapticPulse();
      showToastNotification('📲 جارِ فتح تطبيق واتساب الآن على جهازك...\n(انسخ أي إشعار بنكي، وعند عودتك للتطبيق سيتم تفريغه تلقائياً)');
      setupClipboardAutoDetect();

      const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
      if (isMobile) {
        const link = document.createElement('a');
        link.href = 'whatsapp://';
        document.body.appendChild(link);
        link.click();
        setTimeout(() => document.body.removeChild(link), 100);
      } else {
        window.open('https://web.whatsapp.com', '_blank');
      }
    }

    function openWhatsAppApp() {
      openWhatsAppDirectly();
    }

    let isClipboardWatchingActive = false;
    function setupClipboardAutoDetect() {
      if (isClipboardWatchingActive) return;
      isClipboardWatchingActive = true;

      const checkClipOnReturn = async () => {
        if (document.visibilityState === 'visible') {
          try {
            if (navigator.clipboard && navigator.clipboard.readText) {
              const text = await navigator.clipboard.readText();
              if (text && text.trim() && (text.includes('بنك') || text.includes('تحويل') || text.includes('SDG') || text.includes('المرجع') || text.includes('مبلغ') || text.includes('ناجح'))) {
                playBankerChime();
                triggerHapticPulse();
                liveExtractFromWhatsAppText(text);
                showToastNotification('⚡ تم كشف وتفريغ إشعار الواتساب المنسوخ تلقائياً بالذكاء الاصطناعي!');
              }
            }
          } catch(e) {}
        }
      };

      document.addEventListener('visibilitychange', checkClipOnReturn);
      window.addEventListener('focus', checkClipOnReturn);
    }

    async function pasteFromClipboardAndExtract() {
      triggerHapticPulse();
      try {
        if (navigator.clipboard && navigator.clipboard.readText) {
          const clipText = await navigator.clipboard.readText();
          if (clipText && clipText.trim()) {
            const area = document.getElementById('whatsappPasteArea');
            if (area) area.value = clipText;
            liveExtractFromWhatsAppText(clipText);
            return;
          }
        }
      } catch(err) {
        console.log('Clipboard permission error:', err);
      }
      const fallback = prompt('الصق نص إشعار الواتساب أو بنكك هنا ليتم التفريغ الفوري:');
      if (fallback && fallback.trim()) {
        const area = document.getElementById('whatsappPasteArea');
        if (area) area.value = fallback;
        liveExtractFromWhatsAppText(fallback);
      }
    }

    function liveExtractFromWhatsAppText(rawText) {
      if (!rawText || !rawText.trim()) return;
      const text = rawText.trim();

      // 1. استخراج المبلغ المالي بالذكاء الاصطناعي (Amount Extraction)
      let extractedAmount = 0;
      const amtMatch = text.match(/(?:مبلغ|المبلغ|قدره|Amount|القيمة)[:\s]*([0-9,.]+)/i);
      if (amtMatch && amtMatch[1]) {
        extractedAmount = parseRawNumber(amtMatch[1]);
      }
      if (!extractedAmount || extractedAmount <= 0) {
        const curMatch = text.match(/([0-9,.]+)\s*(?:ج\.س|SDG|جنيه|جنيهاً)/i);
        if (curMatch && curMatch[1]) {
          extractedAmount = parseRawNumber(curMatch[1]);
        }
      }
      if (!extractedAmount || extractedAmount <= 0) {
        const allNums = text.match(/\b\d{1,3}(?:,\d{3})*(?:\.\d+)?\b/g);
        if (allNums && allNums.length > 0) {
          for (const numStr of allNums) {
            const val = parseRawNumber(numStr);
            if (val >= 1000 && val !== 2024 && val !== 2025 && val !== 2026) {
              extractedAmount = val;
              break;
            }
          }
        }
      }

      // 2. استخراج رقم المعاملة أو التحويلة (Transaction / Ref Number)
      let extractedRef = '';
      const refMatch = text.match(/(?:رقم\s*المعاملة|المرجع|رقم\s*المرجع|الرقم\s*المرجعي|التحويلة|رقم\s*العملية|Ref(?:erence)?|Txn(?:ID)?|TRX)[:\s#]*([A-Za-z0-9]+)/i);
      if (refMatch && refMatch[1]) {
        extractedRef = refMatch[1].trim();
      } else {
        const longDigits = text.match(/\b\d{8,16}\b/g);
        if (longDigits && longDigits.length > 0) {
          for (const d of longDigits) {
            if (parseRawNumber(d) !== extractedAmount) {
              extractedRef = d;
              break;
            }
          }
        }
      }
      if (!extractedRef) {
        extractedRef = 'WA' + Date.now().toString().slice(-8);
      }

      // 3. كشف اسم البنك (Bank Detection)
      let extractedBank = 'بنك الخرطوم (بنكك)';
      if (/فيصل|فوري|Faisal/i.test(text)) {
        extractedBank = 'بنك فيصل الإسلامي';
      } else if (/أم\s*درمان|أوكاش|Omdurman/i.test(text)) {
        extractedBank = 'بنك أم درمان الوطني';
      } else if (/البركة|Al\s*Baraka/i.test(text)) {
        extractedBank = 'بنك البركة السوداني';
      } else if (/النيل|Nile/i.test(text)) {
        extractedBank = 'بنك النيل للتجارة';
      } else if (/الخرطوم|بنكك|Bankak/i.test(text)) {
        extractedBank = 'بنك الخرطوم (بنكك)';
      }

      // 4. استخراج اسم المستفيد (إلى حساب)
      let extractedTo = '';
      const toMatch = text.match(/(?:إلى|المستفيد|لصالح|حساب\s*المستفيد|To)[:\s]+([^\n\r,.:;0-9]+)/i);
      if (toMatch && toMatch[1]) {
        extractedTo = toMatch[1].trim();
      } else {
        for (const acc of accountsDB) {
          if (text.includes(acc.name)) {
            extractedTo = acc.name;
            break;
          }
        }
      }

      // 5. استخراج اسم المحول منه (من حساب)
      let extractedFrom = '';
      const fromMatch = text.match(/(?:من|المرسل|من\s*حساب|From)[:\s]+([^\n\r,.:;0-9]+)/i);
      if (fromMatch && fromMatch[1]) {
        extractedFrom = fromMatch[1].trim();
      } else {
        const foundBankAcc = accountsDB.find(a => a.category === 'بنوك ومصارف' && a.name.includes(extractedBank.replace(/\s*\(.*\)/, '')));
        extractedFrom = foundBankAcc ? foundBankAcc.name : extractedBank;
      }

      if (extractedAmount > 0 || extractedRef) {
        const item = {
          id: Date.now(),
          bank: extractedBank,
          amount: extractedAmount,
          from: extractedFrom,
          to: extractedTo,
          ref: extractedRef,
          note: 'تفريغ فوري ذكي من نص إشعار الواتساب'
        };

        whatsappNotificationsDB.unshift(item);
        renderWhatsAppList();
        applyReceiptToVoucherForm(item);
        closeBatchIntakeModal();
        triggerHapticPulse();
        playBankerChime();
        showToastNotification(`🤖 تم التفريغ الفوري بالذكاء الاصطناعي (AI WhatsApp):\n- المبلغ: ${extractedAmount > 0 ? formatNumberWithConfig(extractedAmount, sdgNumberConfig) + ' ج.س' : 'يرجى إدخاله'}\n- التحويلة: ${extractedRef || '---'}\n- إلى: ${extractedTo || '---'}\n- البنك: ${extractedBank}`);
      }
    }

    function processReceiptWithAI(item, source = 'ai') {
      triggerHapticPulse();
      playBankerChime();
      
      applyReceiptToVoucherForm(item);
      closeBatchIntakeModal();

      const sourceLabels = {
        'camera': 'الكاميرا 📸',
        'gallery': 'الاستوديو 🖼️',
        'whatsapp': 'الواتساب 💬',
        'ai': 'الذكاء الاصطناعي ⚡'
      };
      const label = sourceLabels[source] || 'الذكاء الاصطناعي ⚡';

      showToastNotification(`🤖 تفريغ فوري بالذكاء الاصطناعي (AI OCR Turbo ✓):\n- المصدر: ${label}\n- العملية: ${item.ref}\n- المبلغ: ${item.amount.toLocaleString()} ج.س\n- المستفيد: ${item.to}`);

      if (batchProcessingMode === 'auto') {
        saveSingleVoucherSilently(item);
      }
    }

    async function saveSingleVoucherSilently(item) {
      const rate = parseFloat(document.getElementById('usdRate').value) || parseFloat(window.SYSTEM_USD_RATE) || 5999;
      const sdg = item.amount || parseRawNumber(document.getElementById('sdgAmount').value);
      const dec = (typeof usdNumberConfig !== 'undefined' && usdNumberConfig.decimals !== undefined) ? parseInt(usdNumberConfig.decimals) : 2;
      const usdCalc = sdg > 0 ? (sdg / rate) : 0;
      const usd = dec === 0 ? Math.round(usdCalc) : parseFloat(usdCalc.toFixed(dec));

      const bankClean = (item.bank || document.getElementById('bankNameInput')?.value || '').replace(/\s*\(.*\)/, '').trim();
      const refVal = item.ref || document.getElementById('transferInput').value || '';
      const toVal = (item.to || document.getElementById('toSearchInput')?.value || '').trim();
      const fromVal = (item.from || document.getElementById('fromSearchInput')?.value || '').trim();
      const dateVal = document.getElementById('voucherDate').value || new Date().toISOString().slice(0, 10);
      const notesVal = `إشعار تحويل (${bankClean} #${refVal}) - تفريغ ذكاء اصطناعي`;

      if (!fromVal || !toVal || sdg <= 0) {
        showToastNotification('⚠️ يرجى اختيار الحسابات والتأكد من صحة المبلغ لإتمام الحفظ');
        return;
      }

      let savedId = '';
      let queuedOffline = isOfflineMode;
      const payload = {
        isEditing: false,
        date: dateVal,
        paymentMethod: 'transfer',
        amountLocal: sdg,
        amountForeign: usd,
        currency: currentCurrency || 'SDG',
        rate: rate,
        costCenter: document.getElementById('costCenterInput').value || '',
        fromAccountName: fromVal,
        toAccountName: toVal,
        mobileTransferNo: refVal,
        refNo: refVal,
        bankName: bankClean,
        notes: notesVal,
        idempotencyKey: (typeof generateUUID === 'function') ? generateUUID() : String(Date.now())
      };
      if (!isOfflineMode) {
        let res = null;
        try {
          res = await fetch('mobile-voucher.php?api=save_voucher', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
          });
        } catch(e) {
          queuedOffline = true; // انقطاع شبكة فعلي فقط
        }
        if (res) {
          let resData = null;
          try { resData = await res.json(); } catch(e) {}
          if (resData && resData.success) {
            savedId = resData.voucherNo;
          } else {
            // رفض من الخادم (جلسة منتهية، تحويلة مكررة، بيانات ناقصة): لا نخترع رقم سند
            showToastNotification('❌ لم يُحفظ الإشعار: ' + ((resData && resData.error) || 'رفض الخادم الحفظ'));
            return;
          }
        }
      }

      if (!savedId) {
        // حفظ محلي بانتظار الرفع، بترقيم متسلسل دقيق متصل مع السيرفر واللابتوب
        const devCode = (window.MagaiesOffline && MagaiesOffline.getDeviceCode) ? MagaiesOffline.getDeviceCode() : (localStorage.getItem('magaies_device_code') || '1');
        savedId = computeNextVoucherSequentialNo(true);
        payload.id = savedId;
        payload.voucherNo = savedId;
        payload.offline_id = savedId;
        payload.deviceCode = devCode;
        offlineQueue.push(payload);
        try { localStorage.setItem('magaies_mobile_offline_queue', JSON.stringify(offlineQueue)); } catch(e) {}
        const pendingEl = document.getElementById('pendingCount');
        if (pendingEl) pendingEl.textContent = `${offlineQueue.length} معلق`;
      }

      const fromAccObj = Array.isArray(accountsDB) ? accountsDB.find(a => a && a.name === fromVal) : null;
      const toAccObj = Array.isArray(accountsDB) ? accountsDB.find(a => a && a.name === toVal) : null;
      const natInfo = classifyVoucherNatureJs(fromAccObj, toAccObj, fromVal, toVal);

      const newVoucher = {
        id: savedId,
        voucherNo: savedId,
        isOffline: queuedOffline,
        date: dateVal,
        rate: rate,
        currency: currentCurrency || 'SDG',
        costCenter: document.getElementById('costCenterInput').value || '',
        sdg: sdg,
        usd: usd,
        foreignAmt: usd,
        payType: 'transfer',
        from: fromVal,
        fromName: fromVal,
        to: toVal,
        toName: toVal,
        refNo: refVal,
        bankName: bankClean,
        notes: notesVal,
        voucherNature: natInfo.code,
        voucherNatureLabel: natInfo.label
      };

      vouchersList.push(newVoucher);
      currentIndex = vouchersList.length - 1;
      const displayEl = document.getElementById('currentVoucherDisplay');
      if (displayEl) displayEl.textContent = savedId;

      try {
        localStorage.setItem('magaiees_vouchers_history', JSON.stringify(vouchersList));
      } catch(e) {}
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
        showToastNotification(`⚡ جارِ تفريغ وحفظ ${whatsAppQueue.length} إشعار تلقائياً بالتتابع الآمن...`);
        runUniversalAutoBatchStep();
      } else {
        document.getElementById('batchProgressStrip').classList.remove('hidden');
        document.getElementById('batchProgressStrip').classList.add('flex');
        loadNextManualQueueItem();
      }
    }

    async function runUniversalAutoBatchStep() {
      if (currentQueueIndex >= whatsAppQueue.length) {
        playBankerChime();
        triggerHapticPulse();
        await fetchLiveDbData(false);
        showToastNotification(`🎉 اكتمل تفريغ وحفظ جميع الإشعارات الـ (${whatsAppQueue.length}) بنجاح في السيرفر وقاعدة البيانات!`);
        return;
      }

      const item = whatsAppQueue[currentQueueIndex];
      applyReceiptToVoucherForm(item);
      triggerHapticPulse();
      playBankerChime();

      await saveSingleVoucherSilently(item);
      currentQueueIndex++;

      setTimeout(() => {
        runUniversalAutoBatchStep();
      }, 300);
    }

    function applyReceiptToVoucherForm(item) {
      setPaymentType('transfer');
      const transferInp = document.getElementById('transferInput');
      if (transferInp) {
        transferInp.value = item.ref;
        handleTransferInputScale(item.ref);
      }

      const bankInput = document.getElementById('bankNameInput');
      if (bankInput) bankInput.value = (item.bank || 'بنك الخرطوم').replace(/\s*\(.*\)/, '');

      const sdgInp = document.getElementById('sdgAmount');
      if (sdgInp) {
        sdgInp.value = formatNumberWithConfig(item.amount, sdgNumberConfig);
      }
      calculateExchange('sdg');
      updateTafqeet();

      selectAcc('to', item.to);
      selectAcc('from', item.from);
      updateAccountBalanceBadges();

      const notesInp = document.getElementById('voucherNotes');
      if (notesInp) {
        notesInp.value = `إشعار تحويل (${item.bank || ''} #${item.ref || ''}) - ${item.note || 'سداد حركة'}`;
      }

      const toast = document.getElementById('mediaToast');
      const text = document.getElementById('mediaToastText');
      if (toast) {
        toast.classList.remove('hidden');
        toast.classList.add('flex');
      }
      if (text) {
        text.innerHTML = `⚡ <strong>تم التفريغ الفوري!</strong> رقم التحويلة (${item.ref}) • المبلغ (${formatNumberWithConfig(item.amount, sdgNumberConfig)} ج.س) • العميل (${item.to})`;
      }

      const card = document.getElementById('sdgAmountCard');
      if (card) {
        card.classList.add('flash-highlight');
        setTimeout(() => card.classList.remove('flash-highlight'), 600);
      }

      const toCard = document.getElementById('toAccountCard');
      if (toCard) {
        toCard.style.boxShadow = '0 0 35px rgba(16, 185, 129, 0.9)';
        setTimeout(() => { if (toCard) toCard.style.boxShadow = ''; }, 800);
      }

      const fromCard = document.getElementById('fromAccountCard');
      if (fromCard) {
        fromCard.style.boxShadow = '0 0 35px rgba(239, 68, 68, 0.9)';
        setTimeout(() => { if (fromCard) fromCard.style.boxShadow = ''; }, 800);
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
      const v = vouchersList[currentIndex] || vouchersList[0] || {};
      const toName = document.getElementById('toSearchInput')?.value || v.to || '';
      const fromName = document.getElementById('fromSearchInput')?.value || v.from || '';

      document.getElementById('recipientToNameDisplay').textContent = toName || '---';
      document.getElementById('recipientFromNameDisplay').textContent = fromName || '---';

      selectWhatsAppRecipient('to');
      updateSendWhatsAppMessagePreview();
      document.getElementById('modalSendWhatsApp').classList.remove('hidden');
    }

    function closeSendWhatsAppModal() {
      document.getElementById('modalSendWhatsApp').classList.add('hidden');
    }

    function findAccountPhoneByName(name) {
      const clean = String(name || '').trim();
      if (!clean) return '';
      const acc = accountsDB.find(a => a && a.name && (a.name.trim() === clean || (typeof normalizeArabicText === 'function' && normalizeArabicText(a.name) === normalizeArabicText(clean))));
      if (acc && acc.phone && String(acc.phone).trim()) {
        let p = String(acc.phone).trim();
        if (p.startsWith('09') || p.startsWith('01')) {
          p = '+249' + p.slice(1);
        }
        return p;
      }
      return '';
    }

    function selectWhatsAppRecipient(type) {
      currentWhatsAppRecipient = type;
      const btnTo = document.getElementById('btnRecipientTo');
      const btnFrom = document.getElementById('btnRecipientFrom');
      const v = vouchersList[currentIndex] || vouchersList[0] || {};
      const toName = document.getElementById('toSearchInput')?.value || v.to || '';
      const fromName = document.getElementById('fromSearchInput')?.value || v.from || '';

      if (type === 'to') {
        btnTo.className = 'py-2 px-2 rounded-xl bg-emerald-600 text-white border border-emerald-400 flex flex-col items-center justify-center gap-0.5 shadow-sm transition';
        btnFrom.className = 'py-2 px-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 flex flex-col items-center justify-center gap-0.5 hover:bg-slate-700 transition';
        const realPhone = findAccountPhoneByName(toName);
        document.getElementById('whatsAppRecipientPhone').value = realPhone || '+249';
      } else {
        btnFrom.className = 'py-2 px-2 rounded-xl bg-emerald-600 text-white border border-emerald-400 flex flex-col items-center justify-center gap-0.5 shadow-sm transition';
        btnTo.className = 'py-2 px-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 flex flex-col items-center justify-center gap-0.5 hover:bg-slate-700 transition';
        const realPhone = findAccountPhoneByName(fromName);
        document.getElementById('whatsAppRecipientPhone').value = realPhone || '+249';
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

      container.innerHTML = filtered.map((v) => {
        const realIdx = vouchersList.findIndex(item => item.id === v.id);
        const isActive = (currentIndex >= 0 && currentIndex < vouchersList.length && vouchersList[currentIndex].id === v.id);
        return `
        <div onclick="selectVoucherFromModal(${realIdx})" class="p-3 rounded-2xl bg-slate-950 border ${isActive ? 'border-amber-400 bg-amber-950/20' : 'border-slate-800'} hover:border-amber-400/60 cursor-pointer flex items-center justify-between text-xs transition">
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
      `;
      }).join('');
    }

    function selectVoucherFromModal(idx) {
      if (idx >= 0 && idx < vouchersList.length) {
        loadVoucher(idx);
        closeSearchVouchersModal();
        showToastNotification(`📂 تم فتح السند رقم (#${vouchersList[idx].id}) بنجاح.`);
      }
    }

    // ════════════════════════════════════════════════════════════════
    // جلب التحديثات الحية من سيرفر اللابتوب وMariaDB
    // ════════════════════════════════════════════════════════════════
    async function simulateIncomingLaptopVoucher() {
      triggerHapticPulse();
      playFeedbackTone('tap');
      showToastNotification('📡 جارٍ سحب التحديثات الحية للسندات من سيرفر اللابتوب وMariaDB...');
      const previousCount = vouchersList.length;
      const ok = await fetchLiveDbData(false);
      if (ok) {
        if (vouchersList.length > previousCount) {
          showToastNotification(`💻 وارد من اللابتوب: تم سحب حركات مالية جديدة مسجلة على سيرفر الكمبيوتر!\n- إجمالي السندات: ${vouchersList.length}`);
          loadVoucher(vouchersList.length - 1);
          playBankerChime();
        } else {
          showToastNotification(`💻 متزامن بالكامل مع سيرفر اللابتوب وMariaDB\n- إجمالي السندات المحفوظة: ${vouchersList.length}\n- لا توجد حركات جديدة غير مسجلة حالياً.`);
        }
      } else {
        showToastNotification('⚠️ تعذر الاتصال بسيرفر اللابتوب حالياً. يرجى التأكد من تشغيل السيرفر أو الشبكة.');
      }
    }

    // ════════════════════════════════════════════════════════════════
    // مؤشر مزامنة وتحديث البيانات الذكي (Unified Sync & Status Engine)
    // - دائرة خضراء (متزامن) بعد مزامنة البيانات
    // - دائرة حمراء (تحديث) عند الحاجة للتحديث أو انقطاع الاتصال
    // ════════════════════════════════════════════════════════════════
    function updateNetworkStatusUI(isSynced, customText = '') {
      const pill = document.getElementById('networkPill');
      const dot = document.getElementById('networkDot');
      const text = document.getElementById('networkText');
      const icon = document.getElementById('networkRefreshIcon');
      if (!pill || !dot || !text) return;

      if (isSynced) {
        isOfflineMode = false;
        pill.className = 'flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/40 text-emerald-400 text-[10.5px] font-black cursor-pointer hover:bg-emerald-500/25 active:scale-95 transition shadow-sm';
        dot.className = 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_#10b981]';
        if (icon) {
          icon.classList.add('hidden');
          icon.classList.remove('animate-spin');
        }
        text.textContent = customText || 'متزامن';
        pill.title = 'البيانات متزامنة مع السيرفر وقاعدة البيانات - انقر للتحديث الفوري';
        const banner = document.getElementById('offlineSyncBanner');
        if (banner) banner.classList.add('hidden');
      } else {
        isOfflineMode = true;
        pill.className = 'flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-red-500/20 border border-red-500/50 text-red-300 text-[10.5px] font-black cursor-pointer hover:bg-red-500/30 active:scale-95 transition shadow-sm';
        dot.className = 'w-2 h-2 rounded-full bg-red-500 animate-pulse shadow-[0_0_8px_#ef4444]';
        if (icon) icon.classList.remove('hidden');
        text.textContent = customText || 'تحديث';
        pill.title = 'غير متزامن مع السيرفر - انقر هنا للتحديث والمزامنة الآن';
      }
    }

    async function handleUnifiedSyncRefresh() {
      triggerHapticPulse();
      const dot = document.getElementById('networkDot');
      const text = document.getElementById('networkText');
      const icon = document.getElementById('networkRefreshIcon');

      if (icon) {
        icon.classList.remove('hidden');
        icon.classList.add('animate-spin');
      }
      if (text) text.textContent = 'مزامنة...';

      try {
        const res = await fetch('mobile-voucher.php?api=ping&t=' + Date.now());
        const data = await res.json();
        if (data && data.success) {
          await fetchLiveDbData(false);
          updateNetworkStatusUI(true, 'متزامن');
          playBankerChime();
          showToastNotification('🟢 تم مزامنة وتحديث البيانات بنجاح مع قاعدة البيانات MariaDB');
        } else {
          updateNetworkStatusUI(false, 'تحديث');
          showToastNotification('⚠️ تعذر المزامنة: السيرفر لم يستجب - انقر "تحديث" للمحاولة');
        }
      } catch (e) {
        updateNetworkStatusUI(false, 'تحديث');
        showToastNotification('🔴 تعذر الاتصال بالسيرفر - انقر "تحديث" لإعادة المحاولة');
      } finally {
        if (icon) icon.classList.remove('animate-spin');
      }
    }

    function toggleOnlineStatus() {
      handleUnifiedSyncRefresh();
    }

    // ════════════════════════════════════════════════════════════════
    // استخراج آخر 6 أرقام على اليمين للمقارنة ومنع التكرار (مطابق للكمبيوتر)
    // ════════════════════════════════════════════════════════════════
    function extractLast6Digits(str) {
      if (!str) return '';
      const digitsOnly = String(str).replace(/\D/g, '');
      if (digitsOnly.length >= 6) {
        return digitsOnly.slice(-6);
      }
      const trimmed = String(str).trim();
      return trimmed.length >= 6 ? trimmed.slice(-6) : trimmed;
    }

    // البحث عن تكرار آخر 6 أرقام محلياً في الذاكرة
    function findLocalDuplicateTransfer(candidateVal, currentVoucherNo) {
      if (!candidateVal) return null;
      const candSuffix = extractLast6Digits(candidateVal);
      if (!candSuffix || candSuffix.length < 6) return null;

      for (const v of vouchersList) {
        const vId = String(v.id || '').trim();
        if (vId && vId === String(currentVoucherNo || '').trim()) continue;

        const refsToCheck = [
          v.refNo,
          v.transferRef,
          v.mobileTransferNo
        ].filter(Boolean);

        for (const ref of refsToCheck) {
          const refSuffix = extractLast6Digits(ref);
          if (refSuffix && refSuffix.length >= 6 && refSuffix === candSuffix) {
            return {
              voucherId: v.id,
              date: v.date || '',
              amount: v.sdg || v.amountLocal || 0,
              bank: v.bankName || 'البنك',
              fullRef: ref,
              matchedSuffix: candSuffix
            };
          }
        }
      }
      return null;
    }

    // ════════════════════════════════════════════════════════════════
    // فحص رقم التحويلة المكرر والمقارنة مع قاعدة بيانات MariaDB وشاشات النظام
    // ════════════════════════════════════════════════════════════════
    async function checkDuplicateTransferLive(val, isSaveAction = false) {
      if (currentPayType !== 'transfer') return true;
      if (_suppressTransferBlur && !isSaveAction) return true;
      val = (val || '').trim();
      const currentVNo = (document.getElementById('currentVoucherDisplay')?.textContent || '').trim();

      if (!val) {
        if (isSaveAction) {
          showToast("يرجى إدخال رقم حساب التحويل", "error");
          const ti = document.getElementById('transferInput');
          if (ti) {
            ti.focus();
            ti.classList.add('border-red-500');
            setTimeout(() => ti.classList.remove('border-red-500'), 3500);
          }
          return false;
        }
        return true;
      }

      // 1. فحص التكرار فورياً مع قاعدة البيانات MariaDB وسجلات الذاكرة (0ms)
      const localDup = checkTransferDuplicate(val, currentVNo);
      if (localDup) {
        _wasAttemptingSave = isSaveAction;
        showDuplicateWarningModal(val, localDup);
        const ti = document.getElementById('transferInput');
        if (ti) {
          ti.classList.add('border-red-500');
          setTimeout(() => ti.classList.remove('border-red-500'), 3500);
        }
        return false;
      }

      // 2. فحص حي ومباشر عبر الـ API مع قاعدة بيانات MariaDB (في حال حفظ تحويلة قبل ثوانٍ على اللابتوب)
      if (!isOfflineMode) {
        try {
          const res = await fetch(`mobile-voucher.php?api=check_duplicate&ref=${encodeURIComponent(val)}&voucherNo=${encodeURIComponent(currentVNo)}`);
          if (res.ok) {
            const data = await res.json();
            if (data && !data.valid && data.duplicate) {
              if (data.severity === 'soft' && window._confirmedSimilarTransferRef === val) {
                // تم تأكيد التشابه من قِبل المستخدم
              } else {
                _wasAttemptingSave = isSaveAction;
                showDuplicateWarningModal(val, data);
                const ti = document.getElementById('transferInput');
                if (ti) {
                  ti.classList.add('border-red-500');
                  setTimeout(() => ti.classList.remove('border-red-500'), 3500);
                }
                return false;
              }
            }
          }
        } catch(e) {
          console.warn('Check duplicate DB bypass:', e);
        }
      }

      // 3. فحص طول رقم التحويلة (السماح بالأرقام مثل 54016 و 5416 بعد التنبيه أو التأكيد)
      const digitsOnly = val.replace(/\D/g, '');
      if (digitsOnly.length < 3) {
        if (isSaveAction) {
          showToast("رقم التحويلة قصير جداً (أقل من 3 أرقام)", "error");
          return false;
        }
      } else if (digitsOnly.length < 6 && !_shortTransferAllowed) {
        if (isSaveAction) {
          _wasAttemptingSave = true;
          showLengthWarningModal(val);
          return false;
        }
      }

      return true;
    }

    // ════════════════════════════════════════════════════════════════
    // جلب ومزامنة البيانات الحية مباشرة من قاعدة البيانات MariaDB بنظام Delta-Sync
    // ════════════════════════════════════════════════════════════════
    window._lastDbStateHash = window._lastDbStateHash || '';
    async function fetchLiveDbData(showFeedback = false, forceFull = false) {
      try {
        const hashParam = (!forceFull && !showFeedback && window._lastDbStateHash) ? `&clientHash=${encodeURIComponent(window._lastDbStateHash)}` : '';
        const res = await fetch(`mobile-voucher.php?api=get_data${hashParam}&t=${Date.now()}`);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        if (data && data.success) {
          if (data.stateHash) {
            window._lastDbStateHash = data.stateHash;
          }
          const isAtNewVoucher = (currentIndex >= vouchersList.length || currentIndex === -1);
          if (data.exchangeRate && parseFloat(data.exchangeRate) > 1) {
            const dbRate = parseFloat(data.exchangeRate);
            window.SYSTEM_USD_RATE = dbRate;
            try {
              localStorage.setItem('magaiees_system_usd_rate', String(dbRate));
              localStorage.setItem('erp_rate_usd', String(dbRate));
              localStorage.setItem('magaies_custom_usd_rate', String(dbRate));
            } catch (e) {}
            const rateInp = document.getElementById('usdRate');
            if (rateInp && isAtNewVoucher && document.activeElement !== rateInp) {
              rateInp.value = dbRate;
            }
          }
          updateNetworkStatusUI(true, 'متزامن');

          // رفع أي سندات أوفلاين معلقة فوراً بمجرد عودة الاتصال حتى لو لم تتغير قاعدة البيانات (قبل فحص unchanged)
          if (offlineQueue.length > 0) {
            await flushOfflineQueue();
          }

          // إذا لم يتغير شيء في قاعدة البيانات (Delta-Sync unchanged)، لا نستهلك المعالج أو البطارية أو الشبكة
          if ((data.unchanged === true || data.changed === false) && !showFeedback) {
            return true;
          }

          // تحديث مصفوفة التحويلات العالمية للتحقق الفوري (0ms)
          if (Array.isArray(data.allTransferRefs)) {
            window.DB_ALL_TRANSFER_REFS = data.allTransferRefs;
          }

          // 1. تحديث الحسابات من MariaDB
          if (Array.isArray(data.accounts) && data.accounts.length > 0) {
            accountsDB = data.accounts.map(a => ({
              id: a.id,
              name: a.name,
              category: a.category || 'عام',
              sdgBalance: a.rawBalance || 0,
              phone: a.phone || '',
              normalBalance: a.normalBalance || 'debit',
              sourceTable: a.sourceTable || '',
              balanceType: ''
            }));
            if (window._activePosPaymentPayload && window._activePosPaymentPayload.party) {
              const pName = window._activePosPaymentPayload.party.trim();
              if (pName && !accountsDB.some(a => a && a.name && (a.name.trim() === pName || (typeof normalizeArabicText === 'function' && normalizeArabicText(a.name) === normalizeArabicText(pName))))) {
                accountsDB.unshift({ id: 'cust_pos_live', name: pName, category: 'العملاء والزبائن', sdgBalance: 0, normalBalance: 'debit', balanceType: '' });
              }
            }
          }

          // 2. تحديث السندات الحقيقية من single_payments مع الحفاظ الكامل على كافة تفاصيل كل سند وأي سند أوفلاين لم يكتمل رفعه بعد
          if (Array.isArray(data.vouchers) && data.vouchers.length > 0) {
            const dbVouchers = data.vouchers.map(v => ({
              id: v.id,
              voucherNo: v.voucherNo || v.id,
              isOffline: false,
              date: v.date || new Date().toISOString().slice(0, 10),
              rate: v.rate || parseFloat(window.SYSTEM_USD_RATE) || 5999,
              currency: v.currency || 'SDG',
              costCenter: v.costCenter || '',
              sdg: v.sdg || 0,
              usd: v.usd ?? v.foreignAmt ?? 0,
              foreignAmt: v.foreignAmt ?? v.usd ?? 0,
              payType: v.payType || 'transfer',
              from: v.from || v.fromName || '',
              fromName: v.fromName || v.from || '',
              to: v.to || v.toName || '',
              toName: v.toName || v.to || '',
              refNo: v.refNo || '',
              bankName: v.bankName || '',
              notes: v.notes || '',
              attachment: v.attachment || null,
              linkedInvoiceNo: v.linkedInvoiceNo || '',
              voucherNature: v.voucherNature || '',
              voucherNatureLabel: v.voucherNatureLabel || '',
              signatureHash: v.signatureHash || ''
            }));
            const dbIds = new Set(dbVouchers.map(v => String(v.id)));
            const pendingLocalVouchers = vouchersList.filter(v => v && v.isOffline && !dbIds.has(String(v.id)));
            vouchersList = dbVouchers.concat(pendingLocalVouchers);
            if (isAtNewVoucher) {
              currentIndex = vouchersList.length;
            }
          }

          // 3. تحديث رقم السند القادم فورياً وبدقة 100% وحفظه للأوفلاين
          if (data.nextVoucherNo) {
            window.PHP_NEXT_NO = data.nextVoucherNo;
            try {
              const sSeq = extractVoucherSeqJs(data.nextVoucherNo);
              if (sSeq > 1) {
                const known = JSON.parse(localStorage.getItem('magaies_last_known_sequences_v1') || '{}');
                known.finance = Math.max(parseInt(known.finance, 10) || 0, sSeq - 1);
                localStorage.setItem('magaies_last_known_sequences_v1', JSON.stringify(known));
              }
            } catch (e) {}
            const displayEl = document.getElementById('currentVoucherDisplay');
            if (displayEl && isAtNewVoucher) {
              displayEl.textContent = computeNextVoucherSequentialNo(false);
            }
          }

          // إذا كانت إحدى القوائم المنسدلة مفتوحة حالياً، نقوم بتحديثها فوراً ليظهر أي حساب مضاف حديثاً في اللابتوب
          const fromDd = document.getElementById('fromDropdown');
          if (fromDd && !fromDd.classList.contains('hidden')) {
            liveSearchAccount('from');
          }
          const toDd = document.getElementById('toDropdown');
          if (toDd && !toDd.classList.contains('hidden')) {
            liveSearchAccount('to');
          }
          updateAccountBalanceBadges();

          if (showFeedback) {
            showToastNotification(`🗄️ متصل بـ MariaDB مباشرة!\n- الحسابات: ${accountsDB.length} حساب نشط\n- السندات: ${vouchersList.length} سند محفوظ ومرحل`);
          }
          return true;
        }
      } catch (err) {
        console.warn('Failed to fetch live DB data:', err);
        updateNetworkStatusUI(false, 'تحديث');
        if (showFeedback) {
          showToastNotification('⚠️ تعذر سحب البيانات من MariaDB حالياً، يتم الاعتماد على الذاكرة المحلية');
        }
        return false;
      }
    }

    // ════════════════════════════════════════════════════════════════
    // رفع وترحيل السندات المحفوظة أوفلاين إلى MariaDB فور توفر الاتصال (خلف الشاشة بصمت وبأعلى سرعة)
    // ════════════════════════════════════════════════════════════════
    let _isFlushingOfflineQueue = false;
    async function flushOfflineQueue() {
      if (_isFlushingOfflineQueue) return;
      try {
        const storedQ = localStorage.getItem('magaies_mobile_offline_queue');
        if (storedQ) {
          const parsedQ = JSON.parse(storedQ);
          if (Array.isArray(parsedQ) && parsedQ.length > offlineQueue.length) {
            offlineQueue = parsedQ;
          }
        }
      } catch (e) {}

      if (!Array.isArray(offlineQueue) || offlineQueue.length === 0) return;

      _isFlushingOfflineQueue = true;
      const banner = document.getElementById('offlineSyncBanner');
      if (banner) {
        banner.classList.add('hidden');
        banner.classList.remove('flex');
      }

      let syncedCount = 0;
      const remaining = [];
      const queueSnapshot = [...offlineQueue];

      try {
        for (let i = 0; i < queueSnapshot.length; i++) {
          const item = queueSnapshot[i];
          try {
            const controller = new AbortController();
            const timer = setTimeout(() => controller.abort(), 6000);
            const res = await fetch('mobile-voucher.php?api=save_voucher', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(item),
              signal: controller.signal
            });
            clearTimeout(timer);
            const data = await res.json();
            if (data && data.success) {
              syncedCount++;
              updateNetworkStatusUI(true, 'متزامن');
              const realNo = data.voucherNo || item.voucherNo || item.id;
              const foundIdx = vouchersList.findIndex(v => v && (v.id === item.id || v.voucherNo === item.voucherNo));
              if (foundIdx !== -1) {
                vouchersList[foundIdx].id = realNo;
                vouchersList[foundIdx].voucherNo = realNo;
                vouchersList[foundIdx].isOffline = false;
                vouchersList[foundIdx].signatureHash = data.signatureHash || '';
              }
              try {
                const sSeq = extractVoucherSeqJs(realNo);
                if (sSeq > 0) {
                  const known = JSON.parse(localStorage.getItem('magaies_last_known_sequences_v1') || '{}');
                  known.finance = Math.max(parseInt(known.finance, 10) || 0, sSeq);
                  localStorage.setItem('magaies_last_known_sequences_v1', JSON.stringify(known));
                }
              } catch (e) {}
            } else if (!res.ok && res.status >= 500) {
              remaining.push(item);
            }
          } catch(e) {
            // انقطع الاتصال أثناء الرفع: نحتفظ بهذا السند وبقية السندات التالية له بالترتيب
            for (let j = i; j < queueSnapshot.length; j++) {
              remaining.push(queueSnapshot[j]);
            }
            break;
          }
        }

        offlineQueue = remaining;
        try {
          localStorage.setItem('magaies_mobile_offline_queue', JSON.stringify(offlineQueue));
          localStorage.setItem('magaiees_vouchers_history', JSON.stringify(vouchersList));
        } catch(e) {}

        const pendingEl = document.getElementById('pendingCount');
        if (pendingEl) pendingEl.textContent = `${offlineQueue.length} معلق`;
        if (banner) {
          banner.classList.add('hidden');
        }

        if (syncedCount > 0) {
          const wasAtNewVoucher = (currentIndex >= vouchersList.length || currentIndex === -1);
          await fetchLiveDbData(false, true);
          showToastNotification(`🟢 تم تلقائياً رفع وترحيل ${syncedCount} سندات أوفلاين إلى السيرفر (MariaDB) بنجاح!`);
          if (wasAtNewVoucher) {
            const displayEl = document.getElementById('currentVoucherDisplay');
            if (displayEl) displayEl.textContent = computeNextVoucherSequentialNo(false);
          } else if (vouchersList.length > 0 && currentIndex >= 0 && currentIndex < vouchersList.length) {
            loadVoucher(currentIndex);
          }
        }
      } finally {
        _isFlushingOfflineQueue = false;
      }
    }
    window.flushOfflineQueue = flushOfflineQueue;

    // ════════════════════════════════════════════════════════════════
    // الحفظ والتأكيد والخروج (الربط المباشر بقاعدة البيانات MariaDB)
    // ════════════════════════════════════════════════════════════════
    function handleQuickCheckSave() {
      handleSave(true);
    }

    async function handleSave(isQuickCheck = false) {
      const sdg = parseRawNumber(document.getElementById('sdgAmount').value);
      const usd = parseRawNumber(document.getElementById('usdAmount').value);
      const totalAmt = sdg > 0 ? sdg : (usd > 0 ? usd : 0);

      // 1. فحص المبلغ (مطابق لحركة مالية: لا يمكن اعتماد سند بقيمة صفر)
      if (totalAmt <= 0) {
        showToast("لا يمكن اعتماد سند بقيمة صفر", "error");
        const amtInput = document.getElementById('sdgAmount');
        if (amtInput) {
          amtInput.focus();
          amtInput.classList.add('border-red-500');
          setTimeout(() => amtInput.classList.remove('border-red-500'), 3500);
        }
        return;
      }

      const fromAccountName = (document.getElementById('fromSearchInput')?.value || '').trim();
      const toAccountName = (document.getElementById('toSearchInput')?.value || '').trim();

      // 2. الطرف الأول: حساب الصرف / الدافع (مطابق لحركة مالية: يرجى اختيار حساب الدفع (الدافع))
      if (!fromAccountName) {
        showToast("يرجى اختيار حساب الدفع (الدافع)", "error");
        const fromInput = document.getElementById('fromSearchInput');
        if (fromInput) {
          fromInput.focus();
          fromInput.classList.add('border-red-500');
          setTimeout(() => fromInput.classList.remove('border-red-500'), 3500);
        }
        return;
      }

      // 3. الطرف الثاني: حساب المستلم
      if (!toAccountName) {
        showToast("يرجى اختيار الحساب المستلم", "error");
        const toInput = document.getElementById('toSearchInput');
        if (toInput) {
          toInput.focus();
          toInput.classList.add('border-red-500');
          setTimeout(() => toInput.classList.remove('border-red-500'), 3500);
        }
        return;
      }

      // التحقق من أن الحسابات مسجلة ومعرفة في البيانات الأساسية إذا توفر الدليل
      if (accountsDB && accountsDB.length > 0) {
        const isFromRegistered = accountsDB.some(a => a && a.name && (a.name.trim() === fromAccountName || normalizeArabicText(a.name) === normalizeArabicText(fromAccountName)));
        if (!isFromRegistered) {
          showToast(`الحساب (من حساب): "${fromAccountName}" غير مسجل في الدليل`, "warning");
        }

        const isToRegistered = accountsDB.some(a => a && a.name && (a.name.trim() === toAccountName || normalizeArabicText(a.name) === normalizeArabicText(toAccountName)));
        if (!isToRegistered) {
          showToast(`الحساب (إلى حساب): "${toAccountName}" غير مسجل في الدليل`, "warning");
        }
      }

      if (normalizeArabicText(fromAccountName) === normalizeArabicText(toAccountName)) {
        showToast("لا يمكن أن يكون حساب الدفع مطابقاً لحساب المستلم", "warning");
        const toInput = document.getElementById('toSearchInput');
        if (toInput) toInput.focus();
        return;
      }

      const transferVal = (document.getElementById('transferInput').value || '').trim();
      const bankVal = (document.getElementById('bankNameInput').value || '').trim();
      const currentVNo = (document.getElementById('currentVoucherDisplay')?.textContent || '').trim();
      const notesVal = (document.getElementById('voucherNotes')?.value || '').trim();
      const dateVal = (document.getElementById('voucherDate')?.value || '').trim();
      const ccVal = (document.getElementById('costCenterInput')?.value || '').trim();

      // حماية فائقة ضد حفظ نفس السند أكثر من مرة عند النقر المتكرر على زر حفظ (أونلاين أو أوفلاين)
      const voucherContentSig = [
        fromAccountName,
        toAccountName,
        Number(sdg || 0).toFixed(2),
        Number(usd || 0).toFixed(2),
        currentPayType || '',
        transferVal,
        bankVal,
        notesVal,
        dateVal,
        ccVal
      ].join('||');

      if (window._lastSavedVoucherContentSig === voucherContentSig && (Date.now() - (window._lastSavedVoucherTimestamp || 0)) < 30000) {
        showToastNotification(`✅ تم حفظ هذا السند مسبقاً برقم (#${window._lastSavedVoucherId || currentVNo}) — لم يتم تكرار الحفظ`);
        return;
      }

      const isEditing = (currentIndex >= 0 && currentIndex < vouchersList.length && vouchersList[currentIndex].id === currentVNo);
      if (isEditing) {
        const curVObj = vouchersList[currentIndex];
        const curVObjSig = [
          (curVObj.from || curVObj.fromName || '').trim(),
          (curVObj.to || curVObj.toName || '').trim(),
          Number(curVObj.sdg || 0).toFixed(2),
          Number(curVObj.usd || curVObj.foreignAmt || 0).toFixed(2),
          curVObj.payType || '',
          (curVObj.refNo || '').trim(),
          (curVObj.bankName || '').trim(),
          (curVObj.notes || '').trim(),
          (curVObj.date || '').trim(),
          (curVObj.costCenter || '').trim()
        ].join('||');
        if (curVObjSig === voucherContentSig) {
          showToastNotification(`✅ السند (#${currentVNo}) محفوظ مسبقاً بنفس البيانات — لم يتم تكرار الحفظ`);
          return;
        }
      }

      // 4. شروط مطابقة شاشة حركة مالية: التحويل البنكي
      if (currentPayType === 'transfer') {
        if (!transferVal) {
          showToast("يرجى إدخال رقم حساب التحويل", "error");
          const ti = document.getElementById('transferInput');
          if (ti) {
            ti.focus();
            ti.classList.add('border-red-500');
            setTimeout(() => ti.classList.remove('border-red-500'), 3500);
          }
          return;
        }

        const isTransferValid = await checkDuplicateTransferLive(transferVal, true);
        if (!isTransferValid) return;

        if (!bankVal) {
          showToast("اسم البنك إجباري، يرجى كتابة اسم البنك", "error");
          const bi = document.getElementById('bankNameInput');
          if (bi) {
            bi.focus();
            bi.classList.add('border-red-500');
            setTimeout(() => bi.classList.remove('border-red-500'), 3500);
          }
          return;
        }
      }

      // 5. شروط مطابقة شاشة حركة مالية: الشيكات
      if (currentPayType === 'cheque' || currentPayType.includes('cheque')) {
        if (totalAmt <= 0) {
          showToast("يرجى إدخال مبلغ الشيك", "error");
          const amtInput = document.getElementById('sdgAmount');
          if (amtInput) amtInput.focus();
          return;
        }
        if (!bankVal) {
          showToast("اسم البنك إجباري، يرجى كتابة اسم البنك", "error");
          const bi = document.getElementById('bankNameInput');
          if (bi) {
            bi.focus();
            bi.classList.add('border-red-500');
            setTimeout(() => bi.classList.remove('border-red-500'), 3500);
          }
          return;
        }
        if (!transferVal) {
          showToast("رقم الشيك إجباري، لابد من كتابة رقم الشيك", "error");
          const ti = document.getElementById('transferInput');
          if (ti) {
            ti.focus();
            ti.classList.add('border-red-500');
            setTimeout(() => ti.classList.remove('border-red-500'), 3500);
          }
          return;
        }
      }

      if (isSavingInProgress) {
        showToastNotification('⏳ جاري اعتماد وترحيل السند حالياً، يرجى الانتظار...');
        return;
      }
      isSavingInProgress = true;
      const saveBtn = document.getElementById('btnSaveMain');
      if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.classList.add('opacity-70', 'cursor-not-allowed');
      }

      triggerHapticPulse();
      playBankerChime();

      const previousVoucherData = isEditing ? JSON.parse(JSON.stringify(vouchersList[currentIndex])) : null;
      const linkedInvoiceNo = (window._activePosPaymentPayload && window._activePosPaymentPayload.ref) ? String(window._activePosPaymentPayload.ref).trim() : '';
      const confirmSimilarTransfer = (window._confirmedSimilarTransferRef && String(window._confirmedSimilarTransferRef).trim() === transferVal);

      const fromAccObj = Array.isArray(accountsDB) ? accountsDB.find(a => a && a.name === fromAccountName) : null;
      const toAccObj = Array.isArray(accountsDB) ? accountsDB.find(a => a && a.name === toAccountName) : null;
      const localNatureInfo = classifyVoucherNatureJs(fromAccObj, toAccObj, fromAccountName, toAccountName);

      const voucherPayload = {
        isEditing: isEditing,
        voucherNo: isEditing ? vouchersList[currentIndex].id : '',
        id: isEditing ? vouchersList[currentIndex].id : '',
        idempotencyKey: currentDraftIdempotencyKey,
        attachment: currentReceiptAttachment || null,
        date: document.getElementById('voucherDate').value || new Date().toISOString().slice(0, 10),
        rate: parseFloat(document.getElementById('usdRate').value) || parseFloat(window.SYSTEM_USD_RATE) || 5999,
        currency: currentCurrency || 'SDG',
        costCenter: document.getElementById('costCenterInput').value || '',
        paymentMethod: currentPayType,
        amountLocal: sdg,
        amountForeign: usd,
        fromAccountName: fromAccountName,
        toAccountName: toAccountName,
        mobileTransferNo: transferVal,
        refNo: transferVal,
        bankName: bankVal,
        notes: document.getElementById('voucherNotes').value || '',
        linkedInvoiceNo: linkedInvoiceNo,
        confirmSimilarTransfer: confirmSimilarTransfer
      };

      let savedId = '';
      let isDbSuccess = false;
      let serverRejected = false;

      try {
        if (!isOfflineMode || navigator.onLine !== false) {
          // إذا كانت هناك سندات أوفلاين سابقة في الطابور، نرفعها أولاً بالترتيب التسلسلي قبل حفظ السند الجديد
          if (offlineQueue.length > 0) {
            await flushOfflineQueue();
          }
          showToastNotification('⏳ جارٍ الحفظ والترحيل المزدوج إلى قاعدة البيانات MariaDB...');
          try {
            const res = await fetch('mobile-voucher.php?api=save_voucher', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(voucherPayload)
            });
            const resData = await res.json();
            if (resData && resData.success) {
              isDbSuccess = true;
              savedId = resData.voucherNo;

              const savedVoucher = {
                id: savedId,
                voucherNo: savedId,
                isOffline: false,
                idempotencyKey: currentDraftIdempotencyKey,
                attachment: resData.record?.attachment || currentReceiptAttachment || null,
                date: voucherPayload.date,
                rate: voucherPayload.rate,
                currency: voucherPayload.currency,
                costCenter: voucherPayload.costCenter,
                sdg: sdg,
                usd: usd,
                foreignAmt: usd,
                payType: currentPayType,
                from: voucherPayload.fromAccountName,
                fromName: voucherPayload.fromAccountName,
                to: voucherPayload.toAccountName,
                toName: voucherPayload.toAccountName,
                refNo: transferVal,
                bankName: bankVal,
                notes: voucherPayload.notes,
                voucherNature: resData.voucherNature || localNatureInfo.code,
                voucherNatureLabel: resData.voucherNatureLabel || localNatureInfo.label,
                signatureHash: resData.signatureHash || ''
              };

              if (isEditing) {
                vouchersList[currentIndex] = savedVoucher;
              } else {
                vouchersList.push(savedVoucher);
                currentIndex = vouchersList.length - 1;
              }

              window._lastSavedVoucherContentSig = voucherContentSig;
              window._lastSavedVoucherTimestamp = Date.now();
              window._lastSavedVoucherId = savedId;

              try {
                const sSeq = extractVoucherSeqJs(savedId);
                if (sSeq > 0) {
                  const known = JSON.parse(localStorage.getItem('magaies_last_known_sequences_v1') || '{}');
                  known.finance = Math.max(parseInt(known.finance, 10) || 0, sSeq);
                  localStorage.setItem('magaies_last_known_sequences_v1', JSON.stringify(known));
                }
                localStorage.setItem('magaiees_vouchers_history', JSON.stringify(vouchersList));
              } catch(e) {}

              const displayEl = document.getElementById('currentVoucherDisplay');
              if (displayEl) displayEl.textContent = savedId;
              const tagEl = document.getElementById('voucherTag');
              if (tagEl) tagEl.textContent = savedVoucher.voucherNatureLabel || 'MariaDB مرحل';

              // تحديث الأرصدة الحية فوراً بعد الترحيل المزدوج
              await fetchLiveDbData(false, true);

              // إظهار بطاقة النجاح الاحترافية المطابقة لحركة مالية
              showProfessionalSuccess(savedId, fromAccountName, toAccountName, sdg || usd, currentCurrency || 'SDG', isEditing ? 'تم التعديل والترحيل بنجاح' : 'تم الحفظ والترحيل بنجاح', savedVoucher);
              showToast(isEditing ? `تم تعديل السند رقم (${savedId}) بنجاح` : `تم حفظ السند رقم (${savedId}) بنجاح`, 'success');
              showUndoToast(savedId, savedVoucher, isEditing, previousVoucherData);

              // مزامنة فورية مع جدول السندات الفردية لسطح المكتب (erp_single_payments_v1)
              try {
                const getTenantKey = window.getTenantKey || ((k) => k);
                const tKey = getTenantKey('erp_single_payments_v1');
                let existingSP = JSON.parse(localStorage.getItem(tKey) || '[]');
                const spItem = {
                  id: savedId,
                  voucherNo: savedId,
                  date: voucherPayload.date,
                  time: new Date().toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}),
                  type: 'single_payment',
                  fromAccount: fromAccountName,
                  fromAccountName: fromAccountName,
                  toAccount: toAccountName,
                  toAccountName: toAccountName,
                  amountLocal: sdg,
                  currency: currentCurrency || 'SDG',
                  rate: voucherPayload.rate,
                  amountForeign: usd,
                  paymentMethod: currentPayType,
                  bankName: bankVal,
                  mobileTransferNo: transferVal,
                  refNo: transferVal,
                  notes: voucherPayload.notes,
                  costCenter: voucherPayload.costCenter,
                  voucherNature: savedVoucher.voucherNature,
                  voucherNatureLabel: savedVoucher.voucherNatureLabel,
                  signatureHash: savedVoucher.signatureHash
                };
                const exIdx = existingSP.findIndex(x => String(x.id) === String(savedId));
                if (exIdx > -1) existingSP[exIdx] = spItem;
                else existingSP.push(spItem);
                localStorage.setItem(tKey, JSON.stringify(existingSP));
              } catch(e) {}

              if (isAutoSendEnabled) {
                setTimeout(() => {
                  showToastNotification(`📲 الإرسال التلقائي: تم إرسال السند (${savedId}) عبر الواتساب للعميل (${savedVoucher.to})!`);
                }, 500);
              }

              // تصفير المرفق وتوليد مفتاح عدم تكرار جديد للسند التالي
              currentDraftIdempotencyKey = generateUUID();
              currentReceiptAttachment = null;
              window._confirmedSimilarTransferRef = '';
              clearLinkedInvoiceRef();
              if (typeof removeAttachedReceipt === 'function') removeAttachedReceipt();

              loadVoucher(currentIndex);
            } else {
              serverRejected = true;
              if (resData && resData.duplicate) {
                _wasAttemptingSave = true;
                showDuplicateWarningModal(transferVal, resData);
              } else {
                showToast(resData?.error || 'رفض الخادم حفظ السند', 'error');
              }
              return;
            }
          } catch(err) {
            console.warn('DB save network error, fallback to offline:', err);
            showToastNotification(`⚠️ تعذر الاتصال المباشر بقاعدة البيانات: سيتم حفظ السند في طابور الأوفلاين وترحيله تلقائياً.`);
            isDbSuccess = false;
          }
        }

        if ((isOfflineMode || !isDbSuccess) && !serverRejected) {
          const devCode = (window.MagaiesOffline && MagaiesOffline.getDeviceCode) ? MagaiesOffline.getDeviceCode() : (localStorage.getItem('magaies_device_code') || '5');
          savedId = isEditing ? vouchersList[currentIndex].id : computeNextVoucherSequentialNo(true);
          voucherPayload.id = savedId;
          voucherPayload.voucherNo = savedId;
          voucherPayload.deviceCode = devCode;
          voucherPayload.isOffline = true;
          offlineQueue.push(voucherPayload);
          try {
            localStorage.setItem('magaies_mobile_offline_queue', JSON.stringify(offlineQueue));
          } catch(e) {}

          const pendingEl = document.getElementById('pendingCount');
          if (pendingEl) pendingEl.textContent = `${offlineQueue.length} معلق`;

          const newVoucher = {
            id: savedId,
            voucherNo: savedId,
            isOffline: true,
            idempotencyKey: currentDraftIdempotencyKey,
            attachment: currentReceiptAttachment || null,
            date: voucherPayload.date,
            rate: voucherPayload.rate,
            currency: voucherPayload.currency,
            costCenter: voucherPayload.costCenter,
            sdg: sdg,
            usd: usd,
            foreignAmt: usd,
            payType: currentPayType,
            from: voucherPayload.fromAccountName,
            fromName: voucherPayload.fromAccountName,
            to: voucherPayload.toAccountName,
            toName: voucherPayload.toAccountName,
            refNo: transferVal,
            bankName: bankVal,
            notes: voucherPayload.notes,
            voucherNature: localNatureInfo.code,
            voucherNatureLabel: localNatureInfo.label
          };

          if (isEditing) {
            vouchersList[currentIndex] = newVoucher;
          } else {
            vouchersList.push(newVoucher);
            currentIndex = vouchersList.length - 1;
          }

          window._lastSavedVoucherContentSig = voucherContentSig;
          window._lastSavedVoucherTimestamp = Date.now();
          window._lastSavedVoucherId = savedId;

          try {
            localStorage.setItem('magaiees_vouchers_history', JSON.stringify(vouchersList));
          } catch(e) {}

          showProfessionalSuccess(savedId, fromAccountName, toAccountName, sdg || usd, currentCurrency || 'SDG', isEditing ? 'تم التعديل محلياً' : 'تم الحفظ محلياً', newVoucher);
          showToast(isEditing ? `تم تعديل السند محلياً (${savedId})` : `تم حفظ السند محلياً (${savedId})`, 'success');
          showUndoToast(savedId, newVoucher, isEditing, previousVoucherData);

          currentDraftIdempotencyKey = generateUUID();
          currentReceiptAttachment = null;
          window._confirmedSimilarTransferRef = '';
          clearLinkedInvoiceRef();
          if (typeof removeAttachedReceipt === 'function') removeAttachedReceipt();

          loadVoucher(currentIndex);
        }

        // إذا كنا في وضع التدفق اليدوي (إشعاراً بإشعار): الانتقال للإشعار التالي تلقائياً!
        if (whatsAppQueue.length > 0 && batchProcessingMode === 'manual') {
          currentQueueIndex++;
          setTimeout(() => {
            loadNextManualQueueItem();
          }, 500);
        }
      } finally {
        isSavingInProgress = false;
        if (saveBtn) {
          saveBtn.disabled = false;
          saveBtn.classList.remove('opacity-70', 'cursor-not-allowed');
        }
      }
    }
    window.saveMobileVoucher = handleSave;

    function handleEdit() {
      const inp = document.getElementById('sdgAmount');
      if (inp) {
        inp.focus();
        inp.select();
      }
      triggerHapticPulse();
      const curV = vouchersList[currentIndex];
      showToastNotification(`✏️ وضع التعديل نشط: يمكنك الآن تعديل مبالغ وبيانات السند الحالي (رقم: #${curV ? curV.id : 'جديد'}) ثم النقر على زر حفظ (✓).`);
    }

    async function handleDelete() {
      if (vouchersList.length === 0 || currentIndex < 0 || currentIndex >= vouchersList.length) {
        showToast('⚠️ لا يوجد سند محفوظ محدد للحذف (أنت في شاشة سند جديد)', 'warning');
        return;
      }
      const v = vouchersList[currentIndex];
      if (!confirm(`هل أنت متأكد من حذف السند (رقم: #${v.id}) وعكس قيده المحاسبي نهائياً من قاعدة البيانات؟`)) {
        return;
      }

      triggerHapticPulse();
      showToastNotification('⏳ جارٍ حذف السند وعكس أثره المالي من قاعدة البيانات MariaDB...');

      let deletedFromDb = false;
      try {
        const resp = await fetch('mobile-voucher.php?api=delete_voucher', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ voucherNo: v.id, id: v.id })
        });
        const resData = await resp.json();
        if (resData && resData.success) {
          deletedFromDb = true;
          showToastNotification(`🗑️ ${resData.message || 'تم حذف السند وعكس قيده بنجاح من قاعدة البيانات MariaDB'}`);
        } else {
          showToast(`⚠️ تنبيه: ${resData?.error || 'فشل الحذف من الخادم'}`, 'error');
          return;
        }
      } catch (err) {
        showToastNotification('⚠️ تم حذف السند محلياً لعدم توفر الاتصال اللحظي بالقاعدة');
      }

      vouchersList.splice(currentIndex, 1);
      try {
        localStorage.setItem('magaiees_vouchers_history', JSON.stringify(vouchersList));
      } catch(e) {}

      await fetchLiveDbData(false, true);

      if (vouchersList.length > 0) {
        loadVoucher(Math.max(0, currentIndex - 1));
      } else {
        startNewVoucher();
      }
    }

    function handleExit() {
      window.location.href = 'mobile-dashboard.php';
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

    function setupAmountLongPress(el, target = 'sdg') {
      if (!el) return;
      let pressTimer = null;
      let isMoved = false;
      el.addEventListener('touchstart', (e) => {
        isMoved = false;
        pressTimer = setTimeout(() => {
          if (!isMoved) {
            openNumberPropertiesModal(e, target);
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
      const el = document.getElementById('currentTime');
      if (el) el.textContent = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
    }
    setInterval(updateClock, 30000);
    updateClock();

    // 1. مزامنة ودمج الحسابات الحقيقية من MariaDB وذاكرة نظام ERP باللابتوب
    function syncAccountsFromSources() {
      const mergedMap = new Map();

      // حسابات MariaDB من السيرفر
      if (window.PHP_ACCOUNTS && Array.isArray(window.PHP_ACCOUNTS)) {
        window.PHP_ACCOUNTS.forEach(a => {
          if (a && a.name) {
            mergedMap.set(a.name.trim(), {
              id: a.id,
              name: a.name.trim(),
              category: a.category || 'عام',
              sdgBalance: a.rawBalance || 0,
              phone: a.phone || '',
              normalBalance: a.normalBalance || 'debit',
              sourceTable: a.sourceTable || '',
              balanceType: ''
            });
          }
        });
      }

      // حسابات ERP المخزنة في المتصفح بالكمبيوتر واللابتوب
      const storageSources = [
        { key: 'erp_accounts_v1', cat: 'دليل الحسابات', normal: 'debit' },
        { key: 'erp_customers_v1', cat: 'حسابات العملاء', normal: 'debit' },
        { key: 'erp_suppliers_v1', cat: 'حسابات الموردين', normal: 'credit' },
        { key: 'erp_banks_v1', cat: 'بنوك ومصارف', normal: 'debit' },
        { key: 'erp_safes_v1', cat: 'خزن نقدية', normal: 'debit' }
      ];

      storageSources.forEach(s => {
        try {
          const raw = localStorage.getItem(s.key);
          if (raw) {
            const arr = JSON.parse(raw);
            if (Array.isArray(arr)) {
              arr.forEach(item => {
                const name = (item.name || item.bankName || item.safeName || '').trim();
                if (name && !mergedMap.has(name)) {
                  mergedMap.set(name, {
                    id: item.id || ('local_' + Date.now()),
                    name: name,
                    category: s.cat,
                    sdgBalance: parseFloat(item.balance || item.currentBalance || 0) || 0,
                    phone: item.phone || item.mobile || '',
                    normalBalance: s.normal,
                    balanceType: ''
                  });
                }
              });
            }
          }
        } catch(e) {}
      });

      accountsDB = Array.from(mergedMap.values());
    }

    syncAccountsFromSources();

    // 2. تطهير أي سندات وهمية قديمة من الكاش المحلي للمتصفح
    try {
      const cachedHistory = localStorage.getItem('magaiees_vouchers_history');
      if (cachedHistory) {
        const parsed = JSON.parse(cachedHistory);
        if (Array.isArray(parsed)) {
          const cleaned = parsed.filter(v => v && v.id && !['840', '841', '842'].includes(String(v.id)) && v.to !== 'محمد أحمد عثمان');
          localStorage.setItem('magaiees_vouchers_history', JSON.stringify(cleaned));
        }
      }
    } catch(e) {}

    // 3. دمج بيانات السندات الحقيقية من قاعدة بيانات السيرفر والذاكرة المحلية (الجوال وسطح المكتب والأوفلاين)
    const mergedVouchersMap = new Map();
    const normalizeAndAddVoucher = (v, isOff = false) => {
      if (!v || typeof v !== 'object') return;
      const vid = String(v.id || v.voucherNo || v.paymentNo || v.receiptNo || '').trim();
      if (!vid) return;
      const normalized = {
        id: vid,
        voucherNo: v.voucherNo || vid,
        isOffline: Boolean(v.isOffline || isOff),
        date: String(v.date || v.createdAt || v.savedAt || new Date().toISOString()).slice(0, 10),
        rate: parseFloat(v.rate ?? v.exchangeRate) || parseFloat(window.SYSTEM_USD_RATE) || 5999,
        currency: v.currency || 'SDG',
        costCenter: v.costCenter || v.jobId || '',
        sdg: parseFloat(v.sdg ?? v.amountLocal ?? v.amount ?? v.totalAmount ?? 0) || 0,
        usd: parseFloat(v.usd ?? v.foreignAmt ?? v.amountForeign ?? 0) || 0,
        foreignAmt: parseFloat(v.foreignAmt ?? v.usd ?? v.amountForeign ?? 0) || 0,
        payType: v.payType || v.paymentMethod || v.method || 'transfer',
        from: v.from || v.fromName || v.fromAccountName || v.fromAccount || '',
        fromName: v.fromName || v.fromAccountName || v.from || v.fromAccount || '',
        to: v.to || v.toName || v.toAccountName || v.toAccount || v.beneficiaryName || '',
        toName: v.toName || v.toAccountName || v.to || v.toAccount || v.beneficiaryName || '',
        refNo: v.refNo ?? v.mobileTransferNo ?? v.transferRef ?? v.checkNo ?? '',
        bankName: v.bankName ?? v.bank ?? '',
        notes: v.notes ?? v.toStatement ?? v.fromStatement ?? v.statement ?? v.description ?? '',
        attachment: v.attachment || null,
        linkedInvoiceNo: v.linkedInvoiceNo || '',
        voucherNature: v.voucherNature || '',
        voucherNatureLabel: v.voucherNatureLabel || '',
        signatureHash: v.signatureHash || ''
      };
      if (!mergedVouchersMap.has(vid)) {
        mergedVouchersMap.set(vid, normalized);
      } else {
        mergedVouchersMap.set(vid, Object.assign({}, normalized, mergedVouchersMap.get(vid)));
      }
    };

    if (window.PHP_VOUCHERS && Array.isArray(window.PHP_VOUCHERS)) {
      window.PHP_VOUCHERS.forEach(v => normalizeAndAddVoucher(v, false));
    }
    try {
      const cachedH = JSON.parse(localStorage.getItem('magaiees_vouchers_history') || '[]');
      if (Array.isArray(cachedH)) cachedH.forEach(v => normalizeAndAddVoucher(v, false));
    } catch (e) {}
    try {
      const desktopV = JSON.parse(localStorage.getItem('erp_single_payments_v1') || '[]');
      if (Array.isArray(desktopV)) desktopV.forEach(v => normalizeAndAddVoucher(v, false));
    } catch (e) {}

    // تحميل أي سندات محفوظة في طابور الأوفلاين لدمجها وعرضها ورفعها تلقائياً فور توفر الإنترنت
    try {
      const savedQueue = localStorage.getItem('magaies_mobile_offline_queue');
      if (savedQueue) {
        offlineQueue = JSON.parse(savedQueue) || [];
        if (Array.isArray(offlineQueue)) {
          offlineQueue.forEach(v => normalizeAndAddVoucher(v, true));
        }
      }
    } catch(e) {}

    vouchersList = Array.from(mergedVouchersMap.values());
    currentIndex = vouchersList.length > 0 ? vouchersList.length - 1 : -1;

    // ════════════════════════════════════════════════════════════════
    // محرك التحجيم التلقائي الذكي لجميع الحقول والسطور (Dynamic Auto-Fit Engine)
    // يضمن ظهور كافة الأرقام والنصوص كاملة 100% من الأول للآخر بدون قص أو مضايقة للأسهم
    // ════════════════════════════════════════════════════════════════
    let _autoFitCanvasCtx = null;
    function measureTextWidthPx(text, fontWeight, fontSizePx, fontFamily) {
      if (!text) return 0;
      if (!_autoFitCanvasCtx) {
        const canvas = document.createElement('canvas');
        _autoFitCanvasCtx = canvas.getContext('2d');
      }
      _autoFitCanvasCtx.font = `${fontWeight || '700'} ${fontSizePx}px ${fontFamily || "'Cairo', sans-serif"}`;
      return _autoFitCanvasCtx.measureText(String(text)).width;
    }

    // 1. تحجيم رقم السند باللون الأصفر أعلى القائمة (المقاس المعتمد أكبر بدرجة: 16px، يزيد للقصير ويصغر للطويل بدون مضايقة الأسهم)
    function fitVoucherDisplaySize() {
      const dispEl = document.getElementById('currentVoucherDisplay');
      const wrapEl = document.getElementById('voucherDisplayWrapper') || (dispEl ? dispEl.parentElement : null);
      if (!dispEl || !wrapEl) return;
      const text = (dispEl.textContent || '').trim();
      if (!text) return;

      const len = text.length;
      // إذا كان الرقم قصيراً (6 خانات أو أقل) يمكن أن يزيد إلى 17.5px، والمقاس المعتمد للقياسي (7-9 خانات مثل E26100525) هو 16px
      let startSize = (len <= 6) ? 17.5 : 16.0;
      const minSize = 9.5;
      const availWidth = Math.max(60, (wrapEl.clientWidth || 110) - 8);
      const fontFam = "'JetBrains Mono', 'Montserrat', monospace";

      let chosenSize = startSize;
      while (chosenSize > minSize) {
        const w = measureTextWidthPx(text, '900', chosenSize, fontFam);
        if (w <= availWidth) break;
        chosenSize -= 0.5;
      }
      dispEl.style.fontSize = chosenSize + 'px';

      // فحص إضافي مباشر على DOM لضمان عدم التداخل مع الأسهم نهائياً
      while (chosenSize > minSize && dispEl.scrollWidth > wrapEl.clientWidth - 2) {
        chosenSize -= 0.5;
        dispEl.style.fontSize = chosenSize + 'px';
      }
    }

    // 2. تحجيم العملة في السطر الثالث (المقاس المعتمد 11.5px، يصغر تلقائياً إذا كان اسم العملة طويلاً)
    function fitCurrencySelectSize() {
      const sel = document.getElementById('primaryCurrencySelect');
      if (!sel) return;
      const selectedOpt = sel.options && sel.selectedIndex >= 0 ? sel.options[sel.selectedIndex] : null;
      const text = selectedOpt ? (selectedOpt.text || '').trim() : 'جنيه سوداني';
      const baseSize = 11.5;
      const minSize = 8.5;
      const availWidth = Math.max(55, (sel.clientWidth || 98) - 20);

      let size = baseSize;
      while (size > minSize) {
        const w = measureTextWidthPx(text, '900', size, "'Cairo', sans-serif");
        if (w <= availWidth) break;
        size -= 0.5;
      }
      sel.style.fontSize = size + 'px';
    }

    // 3. تحجيم قيمة الدولار في السطر الثالث (خط عادي غير تخين، المقاس المعتمد 17.5px، يصغر تلقائياً إذا زادت الأرقام)
    function fitUsdRateSize() {
      const rateEl = document.getElementById('usdRate');
      if (!rateEl) return;
      const text = String(rateEl.value || rateEl.placeholder || '3610').trim();
      const baseSize = 17.5;
      const minSize = 9.5;
      const availWidth = Math.max(45, (rateEl.clientWidth || 68) - 4);
      const fontFam = "'Inter', 'JetBrains Mono', sans-serif";

      let size = baseSize;
      while (size > minSize) {
        const w = measureTextWidthPx(text, '500', size, fontFam);
        if (w <= availWidth) break;
        size -= 0.5;
      }
      rateEl.style.fontSize = size + 'px';
    }

    // 4. تحجيم اسم المخزن / مركز الكلفة في السطر الثالث (المقاس المعتمد 12px، يصغر تلقائياً إذا كان اسم المخزن طويلاً)
    function fitCostCenterSize() {
      const ccEl = document.getElementById('costCenterInput');
      if (!ccEl) return;
      const text = String(ccEl.value || ccEl.placeholder || '').trim();
      const baseSize = 12.0;
      const minSize = 8.5;
      const availWidth = Math.max(70, (ccEl.clientWidth || 135) - 10);

      let size = baseSize;
      if (text) {
        while (size > minSize) {
          const w = measureTextWidthPx(text, '700', size, "'Cairo', sans-serif");
          if (w <= availWidth) break;
          size -= 0.5;
        }
      }
      ccEl.style.fontSize = size + 'px';
    }

    // 5. تحجيم سطري العناوين لـ (من حساب الدافع + كشف حساب) و(إلى حساب المستلم + كشف حساب) ليبقيا في خط واحد دائماً
    function fitAccountHeaderRows() {
      ['from', 'to'].forEach(side => {
        const row = document.getElementById(side + 'HeaderRow');
        const title = document.getElementById(side + 'HeaderTitle');
        const stmtBtn = document.getElementById(side + 'StatementBtn');
        if (!row || !title || !stmtBtn) return;

        let titleSize = 13.5;
        let btnSize = 10.5;
        title.style.fontSize = titleSize + 'px';
        stmtBtn.style.fontSize = btnSize + 'px';

        while (titleSize > 10.0 && row.scrollWidth > row.clientWidth + 1) {
          titleSize -= 0.5;
          btnSize = Math.max(9.0, btnSize - 0.25);
          title.style.fontSize = titleSize + 'px';
          stmtBtn.style.fontSize = btnSize + 'px';
        }
      });
    }

    // 6. تحجيم اسم الحساب داخل حقل البحث (من حساب / إلى حساب) ليتسع الاسم الطويل بالكامل في سطر واحد
    function fitAccountInputSize(target) {
      const targets = target ? [target] : ['from', 'to'];
      targets.forEach(t => {
        const inp = document.getElementById(t + 'SearchInput');
        if (!inp) return;
        const val = (inp.value || '').trim();
        const baseSize = 20.0;
        const minSize = 11.5;
        if (!val) {
          inp.style.fontSize = '15px';
          return;
        }
        const availWidth = Math.max(140, (inp.clientWidth || 280) - 30);
        let size = baseSize;
        while (size > minSize) {
          const w = measureTextWidthPx(val, '900', size, "'Cairo', sans-serif");
          if (w <= availWidth) break;
          size -= 0.5;
        }
        inp.style.fontSize = size + 'px';
      });
    }

    // 7. تحجيم اسم البنك (المقاس المعتمد 13.5px، يصغر فقط إذا كان الاسم طويلاً جداً)
    function fitBankNameSize() {
      const bankEl = document.getElementById('bankNameInput');
      if (!bankEl) return;
      const text = (bankEl.value || '').trim();
      const baseSize = 13.5;
      const minSize = 9.5;
      if (!text) {
        bankEl.style.fontSize = baseSize + 'px';
        return;
      }
      const availWidth = Math.max(70, (bankEl.clientWidth || 130) - 16);
      let size = baseSize;
      while (size > minSize) {
        const w = measureTextWidthPx(text, '900', size, "'Cairo', sans-serif");
        if (w <= availWidth) break;
        size -= 0.5;
      }
      bankEl.style.fontSize = size + 'px';
    }

    function fitAllDynamicFields() {
      fitVoucherDisplaySize();
      fitCurrencySelectSize();
      fitUsdRateSize();
      fitCostCenterSize();
      fitAccountHeaderRows();
      fitAccountInputSize();
      fitBankNameSize();
    }

    window.fitVoucherDisplaySize = fitVoucherDisplaySize;
    window.fitCurrencySelectSize = fitCurrencySelectSize;
    window.fitUsdRateSize = fitUsdRateSize;
    window.fitCostCenterSize = fitCostCenterSize;
    window.fitAccountHeaderRows = fitAccountHeaderRows;
    window.fitAccountInputSize = fitAccountInputSize;
    window.fitBankNameSize = fitBankNameSize;
    window.fitAllDynamicFields = fitAllDynamicFields;

    // مراقب تلقائي لأي تغيير في رقم السند الأصفر لضمان تحجيمه الفوري دائماً
    try {
      const vDispEl = document.getElementById('currentVoucherDisplay');
      if (vDispEl && typeof MutationObserver !== 'undefined') {
        const vObserver = new MutationObserver(() => {
          fitVoucherDisplaySize();
        });
        vObserver.observe(vDispEl, { childList: true, characterData: true, subtree: true });
      }
    } catch (e) {}

    window.addEventListener('resize', () => {
      fitAllDynamicFields();
    });

    // تشغيل الإعدادات المبدئية
    loadSavedNumberConfig();
    updateAutoSendUI();
    setupAmountLongPress(document.getElementById('sdgAmountCard'), 'sdg');
    setupAmountLongPress(document.getElementById('sdgAmount'), 'sdg');
    setupAmountLongPress(document.getElementById('usdAmountCard'), 'usd');
    setupAmountLongPress(document.getElementById('usdAmount'), 'usd');
    startNewVoucher();
    initPendingPosPayment();
    setTimeout(initPendingPosPayment, 200);
    setTimeout(fitAllDynamicFields, 60);
    setTimeout(fitAllDynamicFields, 350);

    // تسجيل Service Worker لفتح الشاشة فوراً (0ms) عند انقطاع الإنترنت
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('./sw.js?v=2026_10_16_offline_v18', { scope: './', updateViaCache: 'none' }).catch(() => {});
    }

    // إذا فُتحت الصفحة من الكاش المحلي أو هناك سندات أوفلاين معلقة، نتحقق ونرفع فوراً
    if (offlineQueue.length > 0 && navigator.onLine !== false) {
      flushOfflineQueue();
    } else if (!window._lastDbStateHash && navigator.onLine !== false) {
      fetchLiveDbData(false, false);
    }

    // مستشعر الرفع التلقائي اللحظي (كل 3.5 ثوانٍ): يرفع السندات المعلقة (مثل 260, 261, 262, 263) تلقائياً فور وصول الإنترنت دون الحاجة لعمل سند جديد
    setInterval(() => {
      if (offlineQueue.length > 0) {
        flushOfflineQueue();
      } else if (isOfflineMode && navigator.onLine !== false) {
        fetchLiveDbData(false, false);
      }
    }, 3500);

    // التحديث التلقائي الدوري الذكي (Delta-Sync خفيف كل 15 ثانية لا يسحب البيانات إلا عند حدوث تغيير فعلي في MariaDB)
    setInterval(() => {
      if (navigator.onLine !== false && document.visibilityState !== 'hidden') {
        fetchLiveDbData(false, false);
      }
    }, 15000);

    // تحديث ورفع فوري عند العودة إلى تطبيق الموبايل أو التركيز على الشاشة
    window.addEventListener('focus', () => {
      if (offlineQueue.length > 0) flushOfflineQueue();
      fetchLiveDbData(false, false);
    });
    document.addEventListener('visibilitychange', () => {
      if (!document.hidden) {
        if (offlineQueue.length > 0) flushOfflineQueue();
        fetchLiveDbData(false, false);
      }
    });

    // استماع لحظي لحالة اتصال الشبكة
    window.addEventListener('online', () => {
      isOfflineMode = false;
      if (offlineQueue.length > 0) flushOfflineQueue();
      handleUnifiedSyncRefresh();
    });
    window.addEventListener('offline', () => { updateNetworkStatusUI(false, 'تحديث'); });

    // ════════════════════════════════════════════════════════════════
    // وظيفة التحديث الحي الفوري في الحين (تخطي الكاش ومزامنة MariaDB)
    // ════════════════════════════════════════════════════════════════
    async function forceLiveRefresh() {
      playFeedbackTone('tap');
      showToastNotification('🔄 جارٍ التحديث الحي ومزامنة قاعدة البيانات MariaDB...');
      try {
        if (offlineQueue.length > 0) await flushOfflineQueue();
        await fetchLiveDbData(true, true);
      } catch(e) {}
    }
  </script>
</body>
</html>
