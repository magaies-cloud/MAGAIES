<?php 
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$secMobile = $_SERVER['HTTP_SEC_CH_UA_MOBILE'] ?? '';
$isDesktopParam = isset($_GET['force_desktop']) && $_GET['force_desktop'] === '1';
$isMobileDevice = (bool)preg_match('/(android|iphone|ipad|ipod|mobile|blackberry|iemobile|opera mini)/i', $userAgent) || ($secMobile === '?1');
if ($isMobileDevice && !$isDesktopParam && empty($_POST)) {
    $qs = !empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '';
    header("Location: mobile-voucher.php{$qs}");
    exit;
}

require_once 'db_bridge.php';
$systemUsdRate = function_exists('getSystemUsdExchangeRate') ? getSystemUsdExchangeRate($pdo ?? null) : 5999.0;
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
        $tables = ['single_payments', 'single_receipts', 'payments', 'receipts'];
        foreach ($tables as $tbl) {
            try {
                $stmt = $pdo->query("SELECT id, tenant_id, data FROM `$tbl`");
                while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $d = json_decode($r['data'], true);
                    if (!$d) continue;
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
                            'bank' => $d['bankName'] ?? ($d['bank'] ?? ($d['fromAccount'] ?? ''))
                        ];
                    }
                }
            } catch(Exception $e) {}
        }
        return $results;
    }
}

$pdo = getDBConnection();
global $GLOBAL_TENANT_ID;

$SERVER_PAYMENTS_json = '[]';
$SERVER_RECEIPTS_json = '[]';

// --- AJAX save logic for num properties ---
if (isset($_POST['action']) && $_POST['action'] === 'save_num_props') {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS ui_preferences (
            tenant_id VARCHAR(50) NOT NULL,
            pref_key VARCHAR(100) NOT NULL,
            pref_value LONGTEXT,
            PRIMARY KEY (tenant_id, pref_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $val = $_POST['num_props'] ?? '{}';
        $tId = !empty($GLOBAL_TENANT_ID) ? (string)$GLOBAL_TENANT_ID : '1';
        
        // Save for current tenant
        $stmt = $pdo->prepare("INSERT INTO ui_preferences (tenant_id, pref_key, pref_value) VALUES (?, 'multi_cash_num_props', ?) ON DUPLICATE KEY UPDATE pref_value = ?");
        $stmt->execute([$tId, $val, $val]);
        
        // Also save to 'default' and '1' as a permanent system-wide baseline
        $stmt->execute(['default', $val, $val]);
        if ($tId !== '1') {
            $stmt->execute(['1', $val, $val]);
        }
        
        echo "success";
    } catch (Exception $e) {
        echo "error: " . $e->getMessage();
    }
    exit;
}

// --- AJAX save logic for OCR left column properties ---
if (isset($_POST['action']) && $_POST['action'] === 'save_ocr_props') {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS ui_preferences (
            tenant_id VARCHAR(50) NOT NULL,
            pref_key VARCHAR(100) NOT NULL,
            pref_value LONGTEXT,
            PRIMARY KEY (tenant_id, pref_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $val = $_POST['ocr_props'] ?? '{}';
        $tId = !empty($GLOBAL_TENANT_ID) ? (string)$GLOBAL_TENANT_ID : '1';
        
        $stmt = $pdo->prepare("INSERT INTO ui_preferences (tenant_id, pref_key, pref_value) VALUES (?, 'ocr_left_col_props', ?) ON DUPLICATE KEY UPDATE pref_value = ?");
        $stmt->execute([$tId, $val, $val]);
        $stmt->execute(['default', $val, $val]);
        if ($tId !== '1') {
            $stmt->execute(['1', $val, $val]);
        }
        echo "success";
    } catch (Exception $e) {
        echo "error: " . $e->getMessage();
    }
    exit;
}

// ═══ Enterprise Concurrency Lock & Idempotency Handlers ═══
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $tId = !empty($GLOBAL_TENANT_ID) ? (string)$GLOBAL_TENANT_ID : '1';

    // 1. Acquire or Check Concurrency Lock
    if ($action === 'acquire_voucher_lock') {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $vNo = trim($_POST['voucher_no'] ?? '');
            $vType = trim($_POST['voucher_type'] ?? 'payment');
            $uName = trim($_POST['user_name'] ?? 'مستخدم');
            $uId = trim($_POST['user_id'] ?? '');
            $sId = trim($_POST['session_id'] ?? '');
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';

            if (empty($vNo)) {
                echo json_encode(['success' => false, 'message' => 'رقم السند مطلوب']);
                exit;
            }

            // Clean up expired locks (> 90 seconds inactive)
            $pdo->exec("DELETE FROM voucher_concurrency_locks WHERE last_heartbeat < (NOW() - INTERVAL 90 SECOND)");

            // Check if another active session has this voucher locked
            $stmt = $pdo->prepare("SELECT user_name, session_id, locked_at, last_heartbeat FROM voucher_concurrency_locks WHERE tenant_id = ? AND voucher_no = ? LIMIT 1");
            $stmt->execute([$tId, $vNo]);
            $lock = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($lock && $lock['session_id'] !== $sId) {
                // Another active user owns the lock!
                $elapsedSec = time() - strtotime($lock['last_heartbeat']);
                echo json_encode([
                    'success' => true,
                    'is_locked' => true,
                    'locked_by' => $lock['user_name'],
                    'locked_at' => $lock['locked_at'],
                    'elapsed_sec' => $elapsedSec
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Otherwise, lock acquired or refreshed for this session
            $stmtUpsert = $pdo->prepare("INSERT INTO voucher_concurrency_locks (tenant_id, voucher_no, voucher_type, user_name, user_id, session_id, ip_address, locked_at, last_heartbeat)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE last_heartbeat = NOW(), user_name = VALUES(user_name), session_id = VALUES(session_id), user_id = VALUES(user_id)");
            $stmtUpsert->execute([$tId, $vNo, $vType, $uName, $uId, $sId, $ip]);

            echo json_encode([
                'success' => true,
                'is_locked' => false,
                'lock_acquired' => true
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // 2. Heartbeat to maintain lock
    if ($action === 'heartbeat_voucher_lock') {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $vNo = trim($_POST['voucher_no'] ?? '');
            $sId = trim($_POST['session_id'] ?? '');
            if (!empty($vNo) && !empty($sId)) {
                $stmt = $pdo->prepare("UPDATE voucher_concurrency_locks SET last_heartbeat = NOW() WHERE tenant_id = ? AND voucher_no = ? AND session_id = ?");
                $stmt->execute([$tId, $vNo, $sId]);
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // 3. Release lock (when closing voucher or resetting)
    if ($action === 'release_voucher_lock') {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $vNo = trim($_POST['voucher_no'] ?? '');
            $sId = trim($_POST['session_id'] ?? '');
            if (!empty($vNo) && !empty($sId)) {
                $stmt = $pdo->prepare("DELETE FROM voucher_concurrency_locks WHERE tenant_id = ? AND voucher_no = ? AND session_id = ?");
                $stmt->execute([$tId, $vNo, $sId]);
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // 4. Force Takeover of Lock
    if ($action === 'takeover_voucher_lock') {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $vNo = trim($_POST['voucher_no'] ?? '');
            $vType = trim($_POST['voucher_type'] ?? 'payment');
            $uName = trim($_POST['user_name'] ?? 'مستخدم');
            $uId = trim($_POST['user_id'] ?? '');
            $sId = trim($_POST['session_id'] ?? '');
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';

            if (!empty($vNo)) {
                $stmtUpsert = $pdo->prepare("INSERT INTO voucher_concurrency_locks (tenant_id, voucher_no, voucher_type, user_name, user_id, session_id, ip_address, locked_at, last_heartbeat)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE locked_at = NOW(), last_heartbeat = NOW(), user_name = VALUES(user_name), session_id = VALUES(session_id), user_id = VALUES(user_id)");
                $stmtUpsert->execute([$tId, $vNo, $vType, $uName, $uId, $sId, $ip]);
            }
            echo json_encode(['success' => true, 'takeover' => true], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // 5. Cryptographic Idempotency Check
    if ($action === 'verify_idempotency') {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $key = trim($_POST['idempotency_key'] ?? '');
            $vNo = trim($_POST['voucher_no'] ?? '');
            $uName = trim($_POST['user_name'] ?? 'مستخدم');

            if (empty($key)) {
                echo json_encode(['success' => false, 'message' => 'مفتاح التحقق مطلوب']);
                exit;
            }

            // Cleanup keys older than 1 hour
            $pdo->exec("DELETE FROM voucher_idempotency_keys WHERE created_at < (NOW() - INTERVAL 1 HOUR)");

            // Check if key already registered within the last 5 minutes
            $stmt = $pdo->prepare("SELECT id, voucher_no, created_at FROM voucher_idempotency_keys WHERE tenant_id = ? AND idempotency_key = ? LIMIT 1");
            $stmt->execute([$tId, $key]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Key exists: this is a double-click or replay!
                echo json_encode([
                    'success' => true,
                    'duplicate' => true,
                    'message' => 'تم استلام هذا السند مسبقاً وتأمينه ضد التكرار'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Register key
            $stmtInsert = $pdo->prepare("INSERT INTO voucher_idempotency_keys (tenant_id, idempotency_key, voucher_no, action_type, user_name, created_at) VALUES (?, ?, ?, 'save', ?, NOW())");
            $stmtInsert->execute([$tId, $key, $vNo, $uName]);

            echo json_encode([
                'success' => true,
                'duplicate' => false,
                'registered' => true
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}

// --- Fetch num properties for initial load ---
$db_num_props_json = '{}';
$db_ocr_props_json = '{}';
try {
    if ($pdo) {
        $tId = !empty($GLOBAL_TENANT_ID) ? (string)$GLOBAL_TENANT_ID : '1';
        $stmt = $pdo->prepare("SELECT pref_value FROM ui_preferences WHERE tenant_id IN (?, 'default', '1') AND pref_key = 'multi_cash_num_props' ORDER BY CASE WHEN tenant_id = ? THEN 1 WHEN tenant_id = '1' THEN 2 ELSE 3 END LIMIT 1");
        $stmt->execute([$tId, $tId]);
        $res = $stmt->fetchColumn();
        if ($res) $db_num_props_json = $res;

        $stmt2 = $pdo->prepare("SELECT pref_value FROM ui_preferences WHERE tenant_id IN (?, 'default', '1') AND pref_key = 'ocr_left_col_props' ORDER BY CASE WHEN tenant_id = ? THEN 1 WHEN tenant_id = '1' THEN 2 ELSE 3 END LIMIT 1");
        $stmt2->execute([$tId, $tId]);
        $res2 = $stmt2->fetchColumn();
        if ($res2) $db_ocr_props_json = $res2;
    }
} catch(Exception $e) {}
// ------------------------------------------

$SERVER_COST_CENTERS_json = '[]';
try {
    if ($pdo) {
        // Fetch payments
        $stmt = $pdo->prepare("SELECT data FROM payments WHERE tenant_id = ? ORDER BY id ASC");
        $stmt->execute([$GLOBAL_TENANT_ID]);
        $arrPay = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $arrPay[] = json_decode($row['data'], true);
        }
        $SERVER_PAYMENTS_json = json_encode($arrPay, JSON_UNESCAPED_UNICODE);

        // Fetch receipts
        $stmt = $pdo->prepare("SELECT data FROM receipts WHERE tenant_id = ? ORDER BY id ASC");
        $stmt->execute([$GLOBAL_TENANT_ID]);
        $arrRec = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $arrRec[] = json_decode($row['data'], true);
        }
        $SERVER_RECEIPTS_json = json_encode($arrRec, JSON_UNESCAPED_UNICODE);

        // Fetch cost centers / projects
        try {
            $stmtCc = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key IN ('erp_cost_centers_v1', 'erp_jobs_v1') ORDER BY FIELD(setting_key, 'erp_cost_centers_v1', 'erp_jobs_v1') LIMIT 1");
            $stmtCc->execute();
            $ccVal = $stmtCc->fetchColumn();
            if ($ccVal) $SERVER_COST_CENTERS_json = $ccVal;
        } catch (Exception $e) {}
    }
} catch (Exception $e) {}

$page_title = 'حركة مالية مجمعة'; 
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js"></script>
<script>
(function(){
    var l = localStorage.getItem('erp_lang') || 'ar';
    if(l==='en') {
        document.documentElement.setAttribute('dir','ltr');
        document.documentElement.setAttribute('lang','en');
    }
    var width = window.innerWidth || screen.width;
    var ua = navigator.userAgent || '';
    var isRealMobileUA = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua);
    var params = new URLSearchParams(window.location.search);
    if (params.get('force_desktop') !== '1' && isRealMobileUA && width < 768) {
        window.location.replace('mobile-voucher.php' + window.location.search);
    }
})();
window.SERVER_PAYMENTS = <?php echo $SERVER_PAYMENTS_json; ?>;
window.DB_ALL_TRANSFER_REFS = <?php echo json_encode($pdo ? fetchAllDbTransfers($pdo, $GLOBAL_TENANT_ID) : [], JSON_UNESCAPED_UNICODE); ?>;
window.SERVER_RECEIPTS = <?php echo $SERVER_RECEIPTS_json; ?>;
window.SERVER_COST_CENTERS = <?php echo $SERVER_COST_CENTERS_json; ?>;
</script>
<head>

<script>
(function initScreenMaster7ThemeEarly() {
  var raw = localStorage.getItem('magaies_active_theme') || 
            localStorage.getItem('magaiees_global_theme') || 
            localStorage.getItem('magaiees_mobile_theme') || 
            localStorage.getItem('ui_theme') || 
            'warm_cream';
  var aliasMap = {
    'warm-alabaster': 'warm_cream', 'beige': 'warm_cream', 'ivory': 'warm_cream', 'light-linen': 'warm_cream',
    'pure-platinum': 'ultra_white', 'offwhite': 'ultra_white', 'white': 'ultra_white', 'light': 'ultra_white',
    'pearl-platinum': 'exec_gray', 'nordic-paper': 'warm_cream', 'solar_contrast': 'warm_cream',
    'midnight-executive': 'midnight_obsidian', 'navy-kohl': 'midnight_obsidian', 'royal-blue': 'midnight_obsidian', 'ocean-navy': 'midnight_obsidian', 'navy': 'midnight_obsidian',
    'sepia-brown': 'mocha_brown', 'desert-mocha': 'mocha_brown', 'brown': 'mocha_brown',
    'obsidian-charcoal': 'pure_oled', 'midnight': 'pure_oled', 'black': 'pure_oled', 'dark': 'pure_oled',
    'twilight-indigo': 'slate_twilight', 'emerald-forest': 'slate_twilight', 'forest': 'slate_twilight'
  };
  var activeKey = aliasMap[raw] || raw;
  var valid7 = ['warm_cream', 'ultra_white', 'exec_gray', 'midnight_obsidian', 'mocha_brown', 'pure_oled', 'slate_twilight'];
  if (!valid7.includes(activeKey)) activeKey = 'warm_cream';
  var isLight = ['warm_cream', 'ultra_white', 'exec_gray'].includes(activeKey);

  document.documentElement.setAttribute('data-theme', activeKey);
  if (isLight) document.documentElement.classList.add('light-theme');
  else document.documentElement.classList.remove('light-theme');

  document.addEventListener("DOMContentLoaded", function() {
    if (document.body) {
      document.body.setAttribute('data-theme', activeKey);
      if (isLight) document.body.classList.add('light-theme');
      else document.body.classList.remove('light-theme');
    }
  });
})();
</script>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حركة مالية مجمعة | مقاييس (MAGAIEES)</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css?v=<?php echo file_exists('css/theme.css') ? filemtime('css/theme.css') : '12'; ?>">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <style>
        @keyframes flowOut {
            0% { transform: translateX(0); opacity: 0.4; }
            50% { transform: translateX(-10px); opacity: 1; filter: drop-shadow(0 0 3px #ef4444); }
            100% { transform: translateX(0); opacity: 0.4; }
        }
        .animated-arrow-left {
            display: inline-block;
            margin-right: 8px;
            color: #ef4444;
            animation: flowOut 1.5s infinite ease-in-out;
            font-size: 1.6rem;
            vertical-align: middle;
            font-weight: bold;
        }

        @keyframes flowIn {
            0% { transform: translateX(0); opacity: 0.4; }
            50% { transform: translateX(10px); opacity: 1; filter: drop-shadow(0 0 3px currentColor); }
            100% { transform: translateX(0); opacity: 0.4; }
        }
        .animated-arrow-right {
            display: inline-block;
            margin-right: 8px;
            color: inherit;
            animation: flowIn 1.5s infinite ease-in-out;
            font-size: 1.6rem;
            vertical-align: middle;
            font-weight: bold;
        }

        /* ═══ Real-Time Concurrency Lock & Idempotency Styles ═══ */
        @keyframes concurrencyPulse {
            0% { border-color: rgba(245, 158, 11, 0.7); box-shadow: 0 8px 25px rgba(0,0,0,0.6), 0 0 15px rgba(245, 158, 11, 0.25); }
            100% { border-color: #fbbf24; box-shadow: 0 12px 35px rgba(0,0,0,0.75), 0 0 30px rgba(245, 158, 11, 0.55); }
        }

        /* ═══ Ultra-Modern Glassmorphic Crystal Theme & Dynamic Light System ═══ */
        @keyframes lightWavePulse {
            0% { box-shadow: 0 0 0 0 var(--btn-glow, rgba(56, 189, 248, 0.6)), 0 8px 25px rgba(0,0,0,0.4); }
            50% { box-shadow: 0 0 20px 4px var(--btn-glow, rgba(56, 189, 248, 0.6)), 0 12px 35px rgba(0,0,0,0.6); }
            100% { box-shadow: 0 0 0 0 var(--btn-glow, rgba(56, 189, 248, 0.6)), 0 8px 25px rgba(0,0,0,0.4); }
        }

        @keyframes crystalBeamSweep {
            0% { transform: translateX(-120%) rotate(25deg); }
            100% { transform: translateX(220%) rotate(25deg); }
        }

        @keyframes rimGlowFlow {
            0% { border-color: rgba(255, 255, 255, 0.18); }
            50% { border-color: rgba(255, 255, 255, 0.45); }
            100% { border-color: rgba(255, 255, 255, 0.18); }
        }

        /* ═══ Cognitive Ergonomics & Fat-Finger Prevention Styles ═══ */
        @keyframes goldenErgonomicGlow {
            0% {
                border-color: #f59e0b;
                box-shadow: 0 0 10px rgba(245, 158, 11, 0.4), inset 0 0 6px rgba(245, 158, 11, 0.15);
            }
            50% {
                border-color: #fbbf24;
                box-shadow: 0 0 22px rgba(251, 191, 36, 0.75), inset 0 0 10px rgba(251, 191, 36, 0.25);
            }
            100% {
                border-color: #f59e0b;
                box-shadow: 0 0 10px rgba(245, 158, 11, 0.4), inset 0 0 6px rgba(245, 158, 11, 0.15);
            }
        }

        @keyframes ultraAmountPulse {
            0% {
                border-color: #ef4444;
                box-shadow: 0 0 12px rgba(239, 68, 68, 0.5), inset 0 0 8px rgba(239, 68, 68, 0.2);
            }
            50% {
                border-color: #f87171;
                box-shadow: 0 0 25px rgba(248, 113, 113, 0.85), inset 0 0 12px rgba(248, 113, 113, 0.35);
            }
            100% {
                border-color: #ef4444;
                box-shadow: 0 0 12px rgba(239, 68, 68, 0.5), inset 0 0 8px rgba(239, 68, 68, 0.2);
            }
        }

        .amount-tier-mega {
            border: 2.5px solid #f59e0b !important;
            background: rgba(245, 158, 11, 0.12) !important;
            animation: goldenErgonomicGlow 2.4s infinite ease-in-out !important;
        }

        .amount-tier-ultra {
            border: 2.8px solid #ef4444 !important;
            background: rgba(239, 68, 68, 0.15) !important;
            animation: ultraAmountPulse 1.8s infinite ease-in-out !important;
        }

        .block-tafqeet-row {
            animation: fadeInDown 0.25s ease-out;
        }

        .alert-badge-mega {
            background: rgba(245, 158, 11, 0.25) !important;
            border: 1px solid #f59e0b !important;
            color: #fef08a !important;
        }

        .alert-badge-ultra {
            background: rgba(239, 68, 68, 0.28) !important;
            border: 1px solid #ef4444 !important;
            color: #fca5a5 !important;
        }

        /* Glassmorphism Crystal Containers */
        .glass-container {
            padding: 22px 26px;
            border-radius: 18px;
            margin-bottom: 25px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.16);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.45), inset 0 1px 1px rgba(255, 255, 255, 0.25);
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.65), rgba(15, 23, 42, 0.85));
            position: relative;
            overflow: visible !important;
        }

        .glass-container::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 1.5px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
            pointer-events: none;
        }

        .glass-container:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.6), inset 0 1px 2px rgba(255, 255, 255, 0.35);
            border-color: rgba(255, 255, 255, 0.3);
        }

        #secondary-table-container {
            padding-top: 4px !important;
        }

        #secondary-table-container th {
            padding-top: 6px !important;
            padding-bottom: 6px !important;
        }

        /* Crystal Red Container (المستطيل الرئيسي للصرف - منطقة من حساب) */
        .glass-red {
            position: relative; overflow: hidden;
            background: linear-gradient(135deg, rgba(110, 30, 45, 0.75), rgba(15, 23, 42, 0.85)) !important;
            border: 1.5px solid rgba(244, 63, 94, 0.5) !important;
            border-right: 6px solid #f43f5e !important;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.55), inset 0 1px 2px rgba(244, 63, 94, 0.35), 0 0 25px rgba(244, 63, 94, 0.25) !important;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        /* إنارة الحواف الفاخرة باللون الياقوتي الكريستالي الراقي عند التمرير أو التركيز */
        .glass-red:hover,
        .glass-red:focus-within,
        #primary-inputs-container:hover,
        #primary-inputs-container:focus-within {
            background: linear-gradient(135deg, rgba(135, 28, 48, 0.88), rgba(15, 23, 42, 0.94)) !important;
            border-color: rgba(244, 63, 94, 0.8) !important;
            border-right-color: #f43f5e !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.75), 0 0 35px rgba(244, 63, 94, 0.38), inset 0 0 20px rgba(244, 63, 94, 0.15) !important;
        }

        /* Crystal Green Container (مستطيلات الحسابات المستلمة - إلى حساب) */
        .glass-green {
            position: relative; overflow: hidden;
            background: linear-gradient(135deg, rgba(16, 60, 40, 0.85), rgba(15, 23, 42, 0.95)) !important;
            border: 1px solid rgba(39, 174, 96, 0.45) !important;
            border-right: 5px solid #27ae60 !important;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.55), inset 0 1px 2px rgba(39, 174, 96, 0.35), 0 0 25px rgba(39, 174, 96, 0.2) !important;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        /* إضاءة الحواف التلقائية بمجرد قدوم المؤشر إلى حساب */
        .glass-green:hover,
        .glass-green:focus-within,
        .account-block:hover,
        .account-block:focus-within {
            background: linear-gradient(135deg, rgba(20, 75, 50, 0.9), rgba(15, 23, 42, 0.98)) !important;
            border-color: #34d399 !important;
            border-right-color: #10b981 !important;
            box-shadow: 0 20px 55px rgba(0, 0, 0, 0.8), 0 0 35px rgba(16, 185, 129, 0.85), 0 0 70px rgba(5, 150, 105, 0.45), inset 0 0 20px rgba(52, 211, 153, 0.25) !important;
            transform: translateY(-3px) scale(1.005) !important;
        }

        /* High Contrast Inputs, Placeholders, and Labels */
        input::placeholder,
        select::placeholder,
        textarea::placeholder,
        .form-input::placeholder,
        .form-select::placeholder,
        .multi-input::placeholder {
            color: rgba(255, 255, 255, 0.6) !important;
            opacity: 1 !important;
        }

        input, select, textarea, .form-input, .form-select, .multi-input { color: var(--text-primary, #ffffff) !important; }

        .glass-container label { color: var(--text-primary, #e2e8f0); }

        /* High Contrast for Payment Method Details Inputs & Labels */
        .payment-method-box label { color: var(--text-primary, #e2e8f0) !important; opacity: 1 !important; }
        .payment-method-box input { color: var(--text-primary, #ffffff) !important; }
        .payment-method-box input::placeholder {
            color: rgba(255, 255, 255, 0.6) !important;
            opacity: 1 !important;
        }

        /* Auto-Expanding heights for payment containers */
        #payment-panel,
        .payment-layout,
        .payment-details-card,
        .payment-method-box,
        #transfers-container,
        #cheques-container {
            height: auto !important;
            min-height: fit-content !important;
            overflow: visible !important;
        }

        .top-action-bar { background: rgba(249, 115, 22, 0.1); border-bottom: 1px solid rgba(249, 115, 22, 0.2); }
        .action-tab.active { background: #f97316; color: #fff; border-color: #f97316; }

        .form-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 20px;
        }
        
        .form-input, .form-select {
            width: 100%; background: rgba(15, 23, 42, 0.65); border: 1px solid rgba(255,255,255,0.15);
            color: #fff; padding: 10px 14px; border-radius: 10px;
            font-family: inherit; font-size: 0.95rem; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);
        }
        .form-input:focus, .form-select:focus { 
            border-color: #38bdf8; background: rgba(15, 23, 42, 0.9); outline: none;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2), 0 0 15px rgba(56, 189, 248, 0.15);
        }
        
        .form-section-title { 
            font-size: 1.1rem; font-weight: 700; color: #38bdf8; 
            margin-bottom: 15px; display: flex; align-items: center; gap: 8px; 
            border-bottom: 1px solid rgba(56,189,248,0.2); padding-bottom: 10px; 
        }

        /* Grid */
        .multi-table { width: 100%; border-collapse: separate; border-spacing: 0 5px; }
        .multi-table th { text-align: right; padding: 12px 10px; color: #94a3b8; font-weight: 600; font-size: 0.85rem; border-bottom: 1px solid rgba(255,255,255,0.05); white-space: nowrap; }
        .multi-row { transition: all 0.2s; }
        .multi-row:hover { background: rgba(255,255,255,0.02); }
        .multi-row td { padding: 5px; }
        .multi-input {
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);
            color: #f8fafc; padding: 8px 10px; border-radius: 6px; font-size: 0.9rem; min-width: 100px;
        }
        .multi-input:focus { border-color: #f97316; outline: none; background: rgba(0,0,0,0.4); }
        
        .btn-add-row {
            background: rgba(249, 115, 22, 0.1); color: #f97316; border: 1px dashed #f97316;
            padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 700;
            width: 100%; margin-top: 10px; transition: all 0.2s; font-family: 'Tajawal', sans-serif;
        }
        .btn-add-row:hover { background: rgba(249, 115, 22, 0.2); }

        .btn-save {
            background: #10b981; color: #fff; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: bold; font-family: 'Tajawal', sans-serif; transition: 0.2s; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(16,185,129,0.3);
        }
        .btn-save:hover { background: #059669; transform: translateY(-2px); }

        .btn-del { background: none; border: none; color: #ef4444; font-size: 1.2rem; cursor: pointer; padding: 5px; transition: 0.2s; }
        .btn-del:hover { transform: scale(1.2); }

        /* Choices custom overrides */
        .choices__inner { background-color: rgba(0,0,0,0.2) !important; border: 1px solid rgba(255,255,255,0.1) !important; color: #fff !important; border-radius: 8px !important; min-height: 42px !important; }
        .choices__list--dropdown { background-color: #1e293b !important; color: #fff !important; border: 1px solid #f97316 !important; }
        .choices__item--selectable.is-highlighted { background-color: #f97316 !important; }
        /* Toast */
        .toast-container { position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
        .toast { padding:12px 20px; border-radius:12px; font-weight:700; font-size:0.85rem; display:flex; align-items:center; gap:8px; pointer-events:auto; backdrop-filter:blur(20px); box-shadow:0 12px 40px rgba(0,0,0,0.5); animation:toastIn 0.4s cubic-bezier(0.16,1,0.3,1) both; }
        .toast.success { background:rgba(52,211,153,0.15); border:1px solid rgba(52,211,153,0.4); color:#34d399; }
        .toast.error { background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#f87171; }
        .toast.hide { animation:toastOut 0.4s forwards; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOut { to { opacity:0; transform:translateY(-20px) scale(0.95); } }

        /* Context Menu */
        .context-menu {
            position: absolute; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 10px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.5); z-index: 1000;
            display: none; min-width: 200px;
        }
        .context-menu.active { display: flex; flex-direction: column; gap: 8px; animation: fadeIn 0.2s; }
        .context-menu-item {
            display: flex; align-items: center; gap: 10px; color: #fff;
            padding: 8px 12px; border-radius: 8px; cursor: pointer; transition: 0.2s; font-size: 0.9rem;
        }
        .context-menu-item:hover { background: rgba(255,255,255,0.05); }
        .context-menu-item input[type="checkbox"] { accent-color: #f97316; width: 16px; height: 16px; cursor: pointer; }

        /* Dynamic Columns */
        .col-cost-center, .col-note, .col-check-no, .col-check-date, .col-check-due { display: none; }
        table.show-col-cost-center .col-cost-center { display: table-cell; }
        table.show-col-note .col-note { display: table-cell; }
        table.show-col-check-no .col-check-no { display: table-cell; }
        table.show-col-check-date .col-check-date { display: table-cell; }
        table.show-col-check-due .col-check-due { display: table-cell; }

        /* Sticky Footer */
        .sticky-footer { position: sticky; bottom: 0; background: rgba(15,23,42,0.9); border-top: 1px solid rgba(249,115,22,0.3); padding: 15px 25px; border-radius: 0 0 16px 16px; display: flex; justify-content: space-between; align-items: center; z-index: 50; backdrop-filter: blur(10px); margin-top: 20px; margin-left: -25px; margin-right: -25px; margin-bottom: -25px; }

        /* ═══ MASTER DATA ULTRA-PREMIUM ICON & BUTTON DESIGN SYSTEM ═══ */

        /* 1. Highly Visible Glitter Shimmer Sparkles (From Master Data) */
        @keyframes glitterShimmer {
            0% {
                background-position: 0 0, 0 0, 0 0, 0 0, 0 0, -100% -100%;
                filter: brightness(1.3) drop-shadow(0 0 3px #ffffff);
            }
            50% {
                filter: brightness(2.4) drop-shadow(0 0 8px #ffffff);
            }
            100% {
                background-position: 55px 55px, -40px 40px, 48px -48px, -42px -42px, 30px -30px, 200% 200%;
                filter: brightness(1.3) drop-shadow(0 0 3px #ffffff);
            }
        }

        /* 2. Rear Bottom Light Ray Pulse */
        @keyframes rearRayPulse {
            0%, 100% { transform: scaleX(1) translateY(0); opacity: 0.65; }
            50% { transform: scaleX(1.35) translateY(3px); opacity: 1; filter: blur(16px) brightness(1.5); }
        }

        /* 3. Rim Glow Pulse Animation */
        @keyframes rimPulseGlow {
            0%, 100% { box-shadow: 0 0 16px var(--tab-glow-color, rgba(56, 189, 248, 0.5)), inset 0 1px 2px rgba(255, 255, 255, 0.5); }
            50% { box-shadow: 0 0 32px var(--tab-glow-color, rgba(56, 189, 248, 0.9)), inset 0 2px 5px rgba(255, 255, 255, 0.8); }
        }

        /* ═══ Base Crystal Button (Master Data Inspired) ═══ */
        .crystal-btn, .pay-toggle-btn {
            position: relative;
            background: rgba(0, 0, 0, 0.2); /* Darker background, no inner light */
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 3.5px solid var(--tab-border-color, rgba(255, 255, 255, 0.25)) !important;
            border-radius: 14px !important;
            padding: 10px 18px;
            font-size: 1rem;
            font-weight: 800;
            color: #ffffff !important;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
            overflow: visible;
            transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.45);
            z-index: 1;
            font-family: 'Tajawal', sans-serif;
            user-select: none;
            height: 44px; /* Uniform height & width alignment */
            box-sizing: border-box;
        }

        .btn-text, .crystal-btn .btn-text, .btn-dock-btn .btn-text {
            color: #ffffff !important; /* الكتابة باللون الأبيض الناصع دائماً */
            font-weight: 800 !important;
            text-shadow: none !important;
        }

        /* Highly Visible Internal Glitter Sparkles Overlay (DISABLED PER USER) */
        .crystal-btn::after, .pay-toggle-btn::after {
            display: none !important;
        }

        .crystal-btn:hover::after, .pay-toggle-btn:hover::after {
            display: none !important;
        }

        /* Rear Bottom Light Ray Beam Projection: DISABLED to guarantee zero inside glow */
        .crystal-btn::before, .pay-toggle-btn::before, .btn-dock-btn::before {
            display: none !important;
        }

        /* Icon Styling (صورة الكاميرا وصورة اليد لا تعمل أي شعاع نهائياً) */
        .btn-icon, .md-tab-icon {
            font-size: 1.35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            filter: none !important; /* صورة الكاميرا وصورة اليد لا تعمل أي شعاع نهائياً */
            position: relative;
            z-index: 4;
        }

        /* Detailed Icon Rear Glow Ring */
        .btn-icon::after, .md-tab-icon::after {
            display: none !important;
        }

        /* Hover Scale + Crisp Text + Pure External Glow (الشعاع من الخارج فقط وبدون أي شعاع داخلي) */
        .crystal-btn:hover, .pay-toggle-btn:hover {
            transform: translateY(-3px) scale(1.05) !important;
            color: #ffffff !important;
            text-shadow: none !important; /* كتابة بيضاء ناصعة حادة بدون أي توهج أو ضباب داخلي */
            border-color: var(--tab-border-color) !important;
            box-shadow: 0 4px 14px rgba(0,0,0,0.75), 0 0 12px var(--tab-glow-color, rgba(56,189,248,0.7)), 0 0 24px var(--tab-glow-color, rgba(56,189,248,0.4)) !important; /* شعاع خارجي فقط بدون inset */
            background: #000000 !important;
            background-color: #000000 !important;
            background-image: none !important;
            z-index: 10;
        }

        .crystal-btn:hover .btn-icon, .pay-toggle-btn:hover .btn-icon {
            transform: scale(1.2) rotate(4deg);
            filter: none !important; /* لا شعاع نهائياً من صورة الأيقونة */
        }

        .crystal-btn:hover .btn-icon::after, .pay-toggle-btn:hover .btn-icon::after {
            display: none !important;
        }

        /* ═══ Payment Method Toggle Buttons (نقدي 💵 / تحويل 🏦 / شيك ✍️) ═══ */
        .pay-toggle-btn.block-btn-cash {
            --tab-border-color: rgba(213, 189, 175, 0.45);
            --tab-glow-color: rgba(213, 189, 175, 0.65);
            --tab-hover-bg: linear-gradient(135deg, rgba(213, 189, 175, 0.35), rgba(179, 154, 138, 0.5));
        }
        .pay-toggle-btn.active.block-btn-cash {
            --tab-border-color: #d5bdaf;
            background: linear-gradient(135deg, rgba(213, 189, 175, 0.45), rgba(179, 154, 138, 0.7)) !important;
            border-color: #d5bdaf !important;
            color: #ffffff !important;
            text-shadow: 0 0 10px rgba(213, 189, 175, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
            box-shadow: 0 10px 25px rgba(213, 189, 175, 0.5), 0 0 20px rgba(213, 189, 175, 0.3), inset 0 1px 2px rgba(255,255,255,0.6) !important;
            transform: translateY(-3px) scale(1.05) !important;
        }

        /* ═══ Unified 4-Icon Dock & Crystal Buttons (قاعدة الأيقونات الأربعة كوحدة واحدة) ═══ */
        .crystal-unified-dock {
            display: flex;
            flex-direction: column;
            gap: 6px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.75), rgba(30, 41, 59, 0.85));
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            padding: 6px 8px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.45), inset 0 1px 1px rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            flex-shrink: 0;
            user-select: none;
            box-sizing: border-box;
            overflow: visible;
        }

        .btn-dock-btn {
            position: relative;
            height: 38px !important;
            padding: 0 12px !important;
            font-size: 0.95rem !important;
            font-weight: 800 !important;
            font-family: 'Tajawal', sans-serif !important;
            border-radius: 14px !important;
            border-width: 3.5px !important;
            border-style: solid !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            white-space: nowrap !important;
            cursor: pointer !important;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
            box-sizing: border-box !important;
            flex: 1;
            min-width: 105px;
            color: #ffffff !important; /* الكتابة باللون الأبيض الناصع لجميع الأيقونات */
            overflow: visible;
        }

        .btn-dock-btn .btn-icon {
            font-size: 1.25rem !important;
            filter: none !important; /* لا شعاع من صورة الكاميرا أو الأيقونات نهائياً */
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* عندما نأتي إليها: الحجم يزيد شوية + إنارة معتدلة من الخارج فقط + خلفية سوداء والخط أبيض ناصع بدون أي شعاع داخلي */
        .btn-dock-btn:hover {
            transform: translateY(-3px) scale(1.06) !important;
            background: #000000 !important; /* خلفية سوداء عند الوقوف بالمؤشر */
            background-image: none !important;
            color: #ffffff !important; /* الخط باللون الأبيض الناصع دائماً */
            border-width: 3.5px !important;
            border-color: var(--tab-border-color) !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px var(--tab-glow-color), 0 0 24px var(--tab-glow-color) !important; /* شعاع خارجي معتدل فقط وبدون أي inset داخلي */
            text-shadow: none !important; /* كتابة بيضاء ناصعة حادة بدون أي توهج أو ضباب داخلي */
            z-index: 15 !important;
        }

        .btn-dock-btn:hover .btn-icon {
            transform: scale(1.25) rotate(4deg) !important;
            filter: none !important; /* لا شعاع من صورة الكاميرا نهائياً */
        }

        .btn-dock-btn::before,
        .btn-dock-btn:hover::before {
            display: none !important;
        }

        /* 1. إشعار AI (Cyan / Blue Neon) */
        .btn-dock-btn.block-btn-ai-scan {
            --tab-border-color: #06b6d4;
            --tab-glow-color: rgba(6, 182, 212, 0.85);
            border: 3.5px solid #06b6d4 !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.38), rgba(14, 116, 144, 0.72)) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5), 0 0 10px rgba(6, 182, 212, 0.35) !important;
            text-shadow: none !important;
        }
        .btn-dock-btn.block-btn-ai-scan:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #22d3ee !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(6, 182, 212, 0.85), 0 0 24px rgba(6, 182, 212, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-3px) scale(1.06) !important;
        }

        /* 1.5. واتساب AI (WhatsApp Emerald Green Neon) */
        .btn-dock-btn.block-btn-ai-whatsapp {
            --tab-border-color: #25D366;
            --tab-glow-color: rgba(37, 211, 102, 0.85);
            border: 3.5px solid #25D366 !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, rgba(37, 211, 102, 0.38), rgba(18, 140, 126, 0.72)) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5), 0 0 10px rgba(37, 211, 102, 0.35) !important;
            text-shadow: none !important;
        }
        .btn-dock-btn.block-btn-ai-whatsapp:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #4ade80 !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(37, 211, 102, 0.85), 0 0 24px rgba(37, 211, 102, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-3px) scale(1.06) !important;
        }

        /* 2. نماذج (Purple / Violet Neon) */
        .btn-dock-btn.block-btn-ai-trainer {
            --tab-border-color: #a855f7;
            --tab-glow-color: rgba(168, 85, 247, 0.85);
            border: 3.5px solid #a855f7 !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.38), rgba(126, 34, 206, 0.72)) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5), 0 0 10px rgba(168, 85, 247, 0.35) !important;
            text-shadow: none !important;
        }
        .btn-dock-btn.block-btn-ai-trainer:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #c084fc !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(168, 85, 247, 0.85), 0 0 24px rgba(168, 85, 247, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-3px) scale(1.06) !important;
        }

        /* 3. تحويل (Amber Gold / Warm Sun) */
        .btn-dock-btn.block-btn-bank {
            --tab-border-color: #f59e0b;
            --tab-glow-color: rgba(245, 158, 11, 0.85);
            border: 3.5px solid #f59e0b !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.38), rgba(180, 83, 9, 0.72)) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5), 0 0 10px rgba(245, 158, 11, 0.35) !important;
            text-shadow: none !important;
        }
        .btn-dock-btn.block-btn-bank:hover,
        .btn-dock-btn.active.block-btn-bank:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #fbbf24 !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(245, 158, 11, 0.85), 0 0 24px rgba(245, 158, 11, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-3px) scale(1.06) !important;
        }
        .btn-dock-btn.active.block-btn-bank {
            border: 3.5px solid #fbbf24 !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, #d97706, #fbbf24) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 18px rgba(251, 191, 36, 0.65), 0 0 16px rgba(245, 158, 11, 0.4) !important;
            transform: translateY(-1px) scale(1.03) !important;
        }

        /* 4. شيك (Teal / Turquoise Green - رسمية ومصرفية ورقية متباينة عن الكاش) */
        .btn-dock-btn.block-btn-cheque {
            --tab-border-color: #0d9488;
            --tab-glow-color: rgba(20, 184, 166, 0.7);
            border: 3.5px solid #0d9488 !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, rgba(13, 148, 136, 0.28), rgba(15, 118, 110, 0.58)) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5), 0 0 10px rgba(20, 184, 166, 0.35) !important;
            text-shadow: none !important;
        }
        .btn-dock-btn.block-btn-cheque:hover,
        .btn-dock-btn.active.block-btn-cheque:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #14b8a6 !important;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.85), 0 0 14px rgba(20, 184, 166, 0.7), 0 5px 14px rgba(20, 184, 166, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-2px) scale(1.04) !important;
        }
        .btn-dock-btn.active.block-btn-cheque {
            border: 3.5px solid #2dd4bf !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, #0d9488, #14b8a6) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 18px rgba(20, 184, 166, 0.65), 0 0 16px rgba(13, 148, 136, 0.4) !important;
            transform: translateY(-1px) scale(1.03) !important;
        }

        /* ═══ Footer Action Buttons (أزرار التحكم السفلية بالخلفية السوداء والإنارة الخارجية المعتدلة) ═══ */
        .btn-save-master {
            --tab-border-color: #34d399;
            --tab-glow-color: rgba(16, 185, 129, 0.85);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.45), rgba(4, 120, 87, 0.75)) !important;
            border: 3.5px solid #34d399 !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            text-shadow: none !important;
        }
        .btn-save-master:hover,
        .crystal-btn.btn-save-master:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #34d399 !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(16, 185, 129, 0.85), 0 0 24px rgba(16, 185, 129, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-4px) scale(1.06) !important;
        }

        .btn-new {
            --tab-border-color: #38bdf8;
            --tab-glow-color: rgba(56, 189, 248, 0.85);
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.3), rgba(3, 105, 161, 0.6)) !important;
            border: 3.5px solid #38bdf8 !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            text-shadow: none !important;
        }
        .btn-new:hover,
        .crystal-btn.btn-new:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #38bdf8 !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(56, 189, 248, 0.85), 0 0 24px rgba(56, 189, 248, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-4px) scale(1.06) !important;
        }

        .btn-print {
            --tab-border-color: #c084fc;
            --tab-glow-color: rgba(168, 85, 247, 0.85);
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.3), rgba(126, 34, 206, 0.6)) !important;
            border: 3.5px solid #c084fc !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            text-shadow: none !important;
        }
        .btn-print:hover,
        .crystal-btn.btn-print:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #c084fc !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(168, 85, 247, 0.85), 0 0 24px rgba(168, 85, 247, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-4px) scale(1.06) !important;
        }

        .btn-print-preview {
            --tab-border-color: #22d3ee;
            --tab-glow-color: rgba(6, 182, 212, 0.85);
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.3), rgba(14, 116, 144, 0.6)) !important;
            border: 3.5px solid #22d3ee !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            text-shadow: none !important;
        }
        .btn-print-preview:hover,
        .crystal-btn.btn-print-preview:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #22d3ee !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(6, 182, 212, 0.85), 0 0 24px rgba(6, 182, 212, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-4px) scale(1.06) !important;
        }

        .btn-delete {
            --tab-border-color: #fb7185;
            --tab-glow-color: rgba(244, 63, 94, 0.85);
            background: linear-gradient(135deg, rgba(244, 63, 94, 0.3), rgba(190, 18, 60, 0.6)) !important;
            border: 3.5px solid #fb7185 !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            text-shadow: none !important;
        }
        .btn-delete:hover,
        .crystal-btn.btn-delete:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #fb7185 !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(244, 63, 94, 0.85), 0 0 24px rgba(244, 63, 94, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-4px) scale(1.06) !important;
        }

        .btn-exit {
            --tab-border-color: #94a3b8;
            --tab-glow-color: rgba(148, 163, 184, 0.85);
            background: linear-gradient(135deg, rgba(100, 116, 139, 0.3), rgba(51, 65, 85, 0.6)) !important;
            border: 3.5px solid #94a3b8 !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            text-shadow: none !important;
        }
        .btn-exit:hover,
        .crystal-btn.btn-exit:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #cbd5e1 !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(148, 163, 184, 0.85), 0 0 24px rgba(148, 163, 184, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-4px) scale(1.06) !important;
        }

        /* ═══ زر إضافة حساب مستلم (أنيق ومدمج) ═══ */
        .btn-add-account-block {
            border: 2px solid #10b981 !important;
            border-radius: 10px !important;
            color: #34d399 !important;
            background: rgba(16, 185, 129, 0.12) !important;
            font-size: 0.94rem !important;
            padding: 7px 22px !important;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3) !important;
            transition: all 0.25s ease !important;
        }
        .btn-add-account-block:hover {
            background: rgba(16, 185, 129, 0.25) !important;
            border: 2px solid #34d399 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.45) !important;
            transform: translateY(-2px) !important;
            text-shadow: none !important;
        }

        /* ═══ زر الواتساب كأيقونة فقط في السطر الموحد ═══ */
        .btn-whatsapp-icon-only {
            --tab-border-color: #25d366;
            --tab-glow-color: rgba(37, 211, 102, 0.85);
            background: linear-gradient(135deg, rgba(37, 211, 102, 0.28), rgba(18, 140, 126, 0.55)) !important;
            border: 2.2px solid #25d366 !important;
            border-radius: 12px !important;
            color: #ffffff !important;
            width: 44px !important;
            height: 42px !important;
            min-width: 44px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: pointer !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5), 0 0 12px rgba(37, 211, 102, 0.35) !important;
            flex-shrink: 0 !important;
            transition: all 0.25s ease !important;
        }
        .btn-whatsapp-icon-only:hover,
        .crystal-btn.btn-whatsapp-icon-only:hover {
            background: #000000 !important;
            color: #25d366 !important;
            border: 2.2px solid #25d366 !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 16px rgba(37, 211, 102, 0.85) !important;
            transform: translateY(-3px) scale(1.06) !important;
        }
        .btn-remove-block {
            border: 3.5px solid #ef4444 !important;
        }
        .btn-remove-block:hover {
            background: #000000 !important;
            background-image: none !important;
            border: 3.5px solid #ef4444 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(239, 68, 68, 0.85), 0 0 24px rgba(239, 68, 68, 0.45) !important;
            transform: scale(1.15) !important;
        }
        button[data-action="add-bank-row"] {
            border: 3.5px solid #22d3ee !important;
            border-radius: 14px !important;
            color: #ffffff !important;
        }
        button[data-action="add-bank-row"]:hover {
            background: #000000 !important;
            background-image: none !important;
            border: 3.5px solid #22d3ee !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(6, 182, 212, 0.85), 0 0 24px rgba(34, 211, 238, 0.45) !important;
            transform: scale(1.15) !important;
        }
        button[data-action="add-cheque-row"] {
            border: 3.5px solid #c084fc !important;
            border-radius: 14px !important;
            color: #ffffff !important;
        }
        button[data-action="add-cheque-row"]:hover {
            background: #000000 !important;
            background-image: none !important;
            border: 3.5px solid #c084fc !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(168, 85, 247, 0.85), 0 0 24px rgba(192, 132, 252, 0.45) !important;
            transform: scale(1.15) !important;
        }

        /* Luminous Glowing Borders on Hover for Account Blocks & Details Panels */
        .glass-container:hover, .account-block:hover {
            border-color: rgba(255, 255, 255, 0.45) !important;
            box-shadow: 
                0 20px 50px rgba(0, 0, 0, 0.7),
                0 0 35px var(--block-glow-color, rgba(56, 189, 248, 0.4)),
                inset 0 1px 3px rgba(255, 255, 255, 0.5) !important;
        }

        .glass-red:hover,
        .glass-red:focus-within,
        #primary-inputs-container:hover,
        #primary-inputs-container:focus-within {
            --block-glow-color: rgba(244, 63, 94, 0.45);
            border-color: rgba(244, 63, 94, 0.8) !important;
            border-right-color: #f43f5e !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.75), 0 0 35px rgba(244, 63, 94, 0.38), inset 0 0 20px rgba(244, 63, 94, 0.15) !important;
        }
        .glass-green:hover,
        .glass-green:focus-within,
        .account-block:hover,
        .account-block:focus-within {
            --block-glow-color: rgba(16, 185, 129, 0.85);
            border-color: #34d399 !important;
            border-right-color: #10b981 !important;
            box-shadow: 0 20px 55px rgba(0, 0, 0, 0.8), 0 0 35px rgba(16, 185, 129, 0.85), 0 0 70px rgba(5, 150, 105, 0.45), inset 0 0 20px rgba(52, 211, 153, 0.25) !important;
        }

        .block-cash-details:hover {
            border-color: rgba(213, 189, 175, 0.75) !important;
            box-shadow: 0 0 25px rgba(213, 189, 175, 0.35), inset 0 1px 2px rgba(255,255,255,0.4) !important;
        }
        /* CSS variables for dynamic bank transfer colors */
        .block-bank-details {
            --bank-bg-start: rgba(12, 74, 110, 0.6);
            --bank-bg-end: rgba(14, 165, 233, 0.15);
            --bank-border: rgba(14, 165, 233, 0.7);
            --bank-shadow: rgba(14, 165, 233, 0.15);
            --bank-hover-border: rgba(56, 189, 248, 1);
            --bank-hover-shadow-outer: rgba(14, 165, 233, 0.9);
            --bank-hover-shadow-inner: rgba(14, 165, 233, 0.5);
            --bank-icon: #38bdf8;
            
            background: linear-gradient(135deg, var(--bank-bg-start), var(--bank-bg-end)) !important;
            border: 1px solid var(--bank-border) !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.6), inset 0 0 20px var(--bank-shadow) !important;
        }
        
        body[data-voucher-type="receipt"] .block-bank-details {
            --bank-bg-start: rgba(8, 145, 178, 0.4); /* Cyan 600 */
            --bank-bg-end: rgba(6, 182, 212, 0.15); /* Cyan 500 */
            --bank-border: rgba(6, 182, 212, 0.7);
            --bank-shadow: rgba(6, 182, 212, 0.25);
            --bank-hover-border: rgba(34, 211, 238, 1); /* Cyan 400 */
            --bank-hover-shadow-outer: rgba(6, 182, 212, 0.8);
            --bank-hover-shadow-inner: rgba(6, 182, 212, 0.5);
            --bank-icon: #22d3ee;
        }
        
        body[data-voucher-type="receipt"] .bank-icon-label {
            color: var(--bank-icon) !important;
        }

        .block-bank-details:hover {
            border-color: var(--bank-hover-border) !important;
            box-shadow: 0 0 40px var(--bank-hover-shadow-outer), inset 0 0 25px var(--bank-hover-shadow-inner) !important;
        }
        .block-cheque-details {
            background: linear-gradient(135deg, rgba(88, 28, 135, 0.6), rgba(168, 85, 247, 0.15)) !important;
            border: 1px solid rgba(168, 85, 247, 0.7) !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.6), inset 0 0 20px rgba(168, 85, 247, 0.15) !important;
        }
        .block-cheque-details:hover {
            border-color: rgba(216, 180, 254, 1) !important;
            box-shadow: 0 0 40px rgba(168, 85, 247, 0.9), inset 0 0 25px rgba(168, 85, 247, 0.5) !important;
        }
        @keyframes shimmerWave {
            0% { transform: translateX(-150%) skewX(-45deg); opacity: 0; }
            11.5% { opacity: 0.15; }
            23% { transform: translateX(150%) skewX(-45deg); opacity: 0; }
            100% { transform: translateX(150%) skewX(-45deg); opacity: 0; }
        }
        .block-bank-details, .block-cheque-details, .glass-red, .glass-green {
            position: relative;
            overflow: hidden;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.4s ease, border-color 0.4s ease, background 0.4s ease;
        }
        .block-bank-details::before, .block-cheque-details::before, .glass-red::before, .glass-green::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(105deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.02) 45%, rgba(255,255,255,0.12) 50%, rgba(255,255,255,0.02) 55%, rgba(255,255,255,0) 100%);
            z-index: 1; opacity: 0; transition: opacity 0.5s ease; pointer-events: none;
        }
        .block-bank-details:hover::before, .block-cheque-details:hover::before, .glass-red:hover::before, .glass-green:hover::before { animation: shimmerWave 6.5s infinite; opacity: 1; }
        .block-bank-details:hover, .block-cheque-details:hover {
            transform: translateY(-2px) scale(1.01) !important;
            z-index: 10;
        }


        /* Advanced Layout & Animations */
        .split-layout-wrapper {
            display: flex; flex-direction: row-reverse; gap: 0; margin-top: 15px; width: 100%; transition: all 0.6s cubic-bezier(0.25, 1, 0.2, 1);
        }
        
        /* Side Panel */
        .side-panel {
            width: 0; opacity: 0; overflow: hidden; transform: translateX(-80px) scale(0.95);
            background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(25px); border-radius: 16px;
            border: none; box-shadow: 0 15px 40px rgba(0,0,0,0.4);
            transition: all 0.6s cubic-bezier(0.25, 1, 0.2, 1); flex-shrink: 0;
            padding: 0; /* Padding is 0 when hidden */
        }
        
        /* When side panel is active */
        .split-layout-wrapper.has-sidebar { gap: 20px; }
        .split-layout-wrapper.has-sidebar .side-panel {
            width: 380px; opacity: 1; transform: translateX(0) scale(1); padding: 25px;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        /* Main Panel */
        .main-panel {
            flex-grow: 1; transition: all 0.6s cubic-bezier(0.25, 1, 0.2, 1);
            background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(12px);
            border-radius: 16px; padding: 25px; border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transform-origin: left center;
        }

        @media (max-width: 900px) {
            .split-layout-wrapper.has-sidebar { flex-direction: column; }
            .split-layout-wrapper.has-sidebar .side-panel { width: 100%; transform: translateX(0) translateY(-20px); }
        }

        /* Payment Methods Tabs */
        .payment-methods-tabs {
            display: flex; gap: 10px; background: rgba(0,0,0,0.4); padding: 8px;
            border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.2);
        }
        .pm-tab { flex: 1; cursor: pointer; }
        .pm-tab input { display: none; }
        .pm-tab-content {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 15px 5px; gap: 10px; border-radius: 10px; transition: all 0.4s cubic-bezier(0.25, 1, 0.2, 1);
            color: #94a3b8; font-weight: 700; font-size: 0.9rem;
        }
        .pm-icon { font-size: 1.8rem; transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s; filter: grayscale(100%); opacity: 0.5; }
        
        .pm-tab input:checked + .pm-tab-content {
            background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.05)); 
            color: #fff; box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.2);
            transform: translateY(-4px);
        }
        .pm-tab input:checked + .pm-tab-content .pm-icon {
            transform: scale(1.3); filter: grayscale(0%); opacity: 1;
            text-shadow: 0 4px 15px rgba(255,255,255,0.3);
        }
        /* Custom Searchable Dropdown */
        
        /* Fix Dropdown Z-Index */
        .custom-dropdown-input-wrap { z-index: 10; }
        .custom-dropdown-input-wrap:focus-within { z-index: 999999 !important; }
        .voucher-block:focus-within { z-index: 999999 !important; }
        .custom-dropdown-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 9999;
            background: #0b1120 !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            max-height: 280px;
            overflow-y: auto;
            text-align: right;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6);
            display: none;
            margin-top: 6px;
        }
        .custom-dropdown-list.show {
            display: block;
            animation: slideDown 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .custom-dropdown-item {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .custom-dropdown-item:hover {
            background: rgba(255, 255, 255, 0.08) !important;
        }
        .custom-dropdown-item:last-child {
            border-bottom: none;
        }

        /* ══════════════════════════════════════════════════════════════════════
           VOUCHER TYPE TOGGLE BUTTONS (صرف إلى مجموعة 📤 / استلام من مجموعة 📥)
           ستايل فاخر، متناسق ومتقن، بألوان مخففة، كتابة متناسقة، وإنارة حواف أخاذة
           ══════════════════════════════════════════════════════════════════════ */
        .voucher-type-btn {
            position: relative !important;
            min-width: 140px !important;
            height: 38px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            border-radius: 10px !important;
            cursor: pointer !important;
            font-family: 'Tajawal', sans-serif !important;
            font-size: 0.96rem !important;
            font-weight: 800 !important;
            letter-spacing: 0.2px !important;
            white-space: nowrap !important;
            user-select: none !important;
            -webkit-user-select: none !important;
            transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1) !important;
            backdrop-filter: blur(14px) !important;
            -webkit-backdrop-filter: blur(14px) !important;
            box-sizing: border-box !important;
            padding: 0 16px !important;
            text-decoration: none !important;
            overflow: hidden !important;
            z-index: 2 !important;
        }

        /* حماية خط النص والأيقونات من أي تأثير خارجي وضمان تناسق ألوان الخط */
        .voucher-type-btn .btn-text,
        .voucher-type-btn .btn-icon,
        .voucher-type-btn span,
        .voucher-type-btn i {
            color: inherit !important;
            font-family: inherit !important;
            text-decoration: none !important;
        }

        /* ─── إيماءة لمعان زجاجي سائل عند المرور بالفأرة (Kinetic Glass Sweep) ─── */
        .voucher-type-btn::after {
            content: '' !important;
            position: absolute !important;
            top: 0 !important;
            left: -140% !important;
            width: 80% !important;
            height: 100% !important;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.28), transparent) !important;
            transform: skewX(-25deg) !important;
            pointer-events: none !important;
            transition: left 0.65s cubic-bezier(0.16, 1, 0.3, 1) !important;
            z-index: 3 !important;
        }
        .voucher-type-btn:hover::after {
            left: 140% !important;
        }

        /* ─── 1. زر صرف إلى مجموعة (حالة الراحة / غير مفعل) ─── */
        #btn-payment:not(.active-payment) {
            background: linear-gradient(135deg, rgba(50, 16, 24, 0.55), rgba(20, 8, 14, 0.75)) !important;
            border: 3.5px solid rgba(244, 63, 94, 0.7) !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
            transform: scale(0.98) !important;
        }
        #btn-payment:not(.active-payment):hover {
            background: #000000 !important;
            background-image: none !important;
            border: 3.5px solid #fb7185 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(244, 63, 94, 0.85), 0 0 24px rgba(244, 63, 94, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-3px) scale(1.03) !important;
        }

        /* ─── زر صرف إلى مجموعة (مفعل: أحمر غامق فاخر، كتابة ناصعة متناسقة، إنارة حواف) ─── */
        .voucher-type-btn.active-payment,
        #btn-payment.active-payment {
            background: linear-gradient(135deg, #781827 0%, #4a0e19 100%) !important;
            border: 3.5px solid #fb7185 !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            font-weight: 800 !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5), 0 0 12px rgba(159, 18, 57, 0.45) !important;
            transform: translateY(-2px) scale(1.03) !important;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.8) !important;
            animation: voucherEdgePulsePayment 3.5s infinite alternate ease-in-out;
        }
        .voucher-type-btn.active-payment:hover,
        #btn-payment.active-payment:hover,
        #btn-payment:not(.active-payment):hover {
            background: #000000 !important;
            background-image: none !important;
            border: 3.5px solid #fb7185 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(244, 63, 94, 0.85), 0 0 24px rgba(244, 63, 94, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-3px) scale(1.05) !important;
        }

        /* ─── 2. زر استلام من مجموعة (حالة الراحة / غير مفعل) ─── */
        #btn-receipt:not(.active-receipt) {
            background: linear-gradient(135deg, rgba(14, 42, 34, 0.55), rgba(8, 24, 18, 0.75)) !important;
            border: 3.5px solid rgba(16, 185, 129, 0.7) !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
            transform: scale(0.98) !important;
        }
        #btn-receipt:not(.active-receipt):hover {
            background: #000000 !important;
            background-image: none !important;
            border: 3.5px solid #34d399 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(16, 185, 129, 0.85), 0 0 24px rgba(16, 185, 129, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-3px) scale(1.03) !important;
        }

        /* ─── زر استلام من مجموعة (مفعل: زمردي غامق فاخر، كتابة ناصعة متناسقة، إنارة حواف) ─── */
        .voucher-type-btn.active-receipt,
        #btn-receipt.active-receipt {
            background: linear-gradient(135deg, #0d5440 0%, #063326 100%) !important;
            border: 3.5px solid #34d399 !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            font-weight: 800 !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5), 0 0 12px rgba(16, 185, 129, 0.45) !important;
            transform: translateY(-2px) scale(1.03) !important;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.8) !important;
            animation: voucherEdgePulseReceipt 3.5s infinite alternate ease-in-out;
        }
        .voucher-type-btn.active-receipt:hover,
        #btn-receipt.active-receipt:hover {
            background: #000000 !important;
            background-image: none !important;
            border: 3.5px solid #34d399 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(16, 185, 129, 0.85), 0 0 24px rgba(16, 185, 129, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-3px) scale(1.05) !important;
        }

        /* ─── نبض إنارة الحواف الهادئ والمستمر لكلا الزرين المفعلين ─── */
        @keyframes voucherEdgePulsePayment {
            0% { 
                border-color: rgba(244, 63, 94, 0.7);
                box-shadow: 0 6px 20px rgba(0,0,0,0.45), 0 0 16px rgba(159, 18, 57, 0.45), inset 0 1px 1px rgba(255, 255, 255, 0.25); 
            }
            100% { 
                border-color: #fb7185;
                box-shadow: 0 8px 26px rgba(0,0,0,0.5), 0 0 26px rgba(244, 63, 94, 0.75), inset 0 1px 2px rgba(255, 255, 255, 0.4); 
            }
        }
        @keyframes voucherEdgePulseReceipt {
            0% { 
                border-color: rgba(16, 185, 129, 0.7);
                box-shadow: 0 6px 20px rgba(0,0,0,0.45), 0 0 16px rgba(16, 185, 129, 0.45), inset 0 1px 1px rgba(255, 255, 255, 0.25); 
            }
            100% { 
                border-color: #34d399;
                box-shadow: 0 8px 26px rgba(0,0,0,0.5), 0 0 26px rgba(16, 185, 129, 0.75), inset 0 1px 2px rgba(255, 255, 255, 0.4); 
            }
        }

        /* ─── حركة إيماءات الأيقونة الداخلية ─── */
        .voucher-type-btn .btn-icon {
            font-size: 1.3rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.35s ease !important;
        }
        .voucher-type-btn.active-payment .btn-icon i {
            filter: none !important;
        }
        .voucher-type-btn.active-receipt .btn-icon i {
            filter: none !important;
        }
        .voucher-type-btn:hover .btn-icon {
            transform: scale(1.22) rotate(-8deg) !important;
        }

        /* ══════════════════════════════════════════════════════════════════════
           ULTRA-SUBTLE AMBIENT GLASS RIPPLES & SCREEN-WIDE LIGHT WAVES
           تموجات ولمعات زجاجية خفيفة جداً لكامل الشاشة لا تؤثر على البصر
           ══════════════════════════════════════════════════════════════════════ */
        .glass-ripple-mesh {
            position: fixed !important;
            top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;
            width: 100vw !important; height: 100vh !important;
            pointer-events: none !important;
            z-index: 0 !important;
        }

        .glass-ripple-1 {
            background: radial-gradient(ellipse 70% 50% at 25% 25%, rgba(255, 255, 255, 0.035) 0%, rgba(56, 189, 248, 0.02) 40%, transparent 70%) !important;
            filter: blur(48px) !important;
            animation: slowGlassWave1 36s ease-in-out infinite alternate !important;
        }

        .glass-ripple-2 {
            background: radial-gradient(ellipse 65% 45% at 75% 75%, rgba(255, 255, 255, 0.03) 0%, rgba(168, 85, 247, 0.018) 40%, transparent 70%) !important;
            filter: blur(56px) !important;
            animation: slowGlassWave2 46s ease-in-out infinite alternate !important;
        }

        .glass-ripple-3 {
            background: radial-gradient(ellipse 80% 55% at 50% 50%, rgba(255, 255, 255, 0.025) 0%, rgba(16, 185, 129, 0.015) 35%, rgba(251, 191, 36, 0.012) 65%, transparent 80%) !important;
            filter: blur(65px) !important;
            animation: slowGlassWave3 54s ease-in-out infinite alternate !important;
        }

        .glass-specular-shimmer {
            position: fixed !important;
            top: 0 !important; left: -100% !important;
            width: 300% !important; height: 100vh !important;
            background: linear-gradient(115deg, transparent 0%, transparent 42%, rgba(255, 255, 255, 0.02) 50%, transparent 58%, transparent 100%) !important;
            pointer-events: none !important;
            animation: specularGlassSweep 42s linear infinite !important;
            z-index: 0 !important;
        }

        @keyframes slowGlassWave1 {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); opacity: 0.55; }
            50% { transform: translate(3%, 3%) scale(1.08) rotate(2deg); opacity: 0.85; }
            100% { transform: translate(-3%, 4%) scale(0.96) rotate(-2deg); opacity: 0.55; }
        }

        @keyframes slowGlassWave2 {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); opacity: 0.5; }
            50% { transform: translate(-4%, -3%) scale(1.1) rotate(-3deg); opacity: 0.8; }
            100% { transform: translate(3%, -2%) scale(0.95) rotate(2deg); opacity: 0.5; }
        }

        @keyframes slowGlassWave3 {
            0% { transform: scale(1) translate(0, 0); opacity: 0.45; }
            50% { transform: scale(1.12) translate(2%, -3%); opacity: 0.8; }
            100% { transform: scale(0.92) translate(-2%, 2%); opacity: 0.45; }
        }

        @keyframes specularGlassSweep {
            0% { transform: translateX(-35%); }
            100% { transform: translateX(35%); }
        }

        /* لمسة ضوئية زجاجية فائقة الرقة وغير محسوسة تتهادى فوق اللوحة الرئيسية */
        #main-form-panel::before {
            content: '' !important;
            position: absolute !important;
            top: -30% !important; left: -30% !important;
            width: 160% !important; height: 160% !important;
            background: radial-gradient(ellipse 60% 40% at 50% 50%, rgba(255, 255, 255, 0.02) 0%, transparent 60%) !important;
            pointer-events: none !important;
            animation: glassSubtleDrift 28s ease-in-out infinite alternate !important;
            z-index: 0 !important;
        }

        @keyframes glassSubtleDrift {
            0% { transform: translate(0, 0); opacity: 0.4; }
            50% { transform: translate(30px, 20px); opacity: 0.75; }
            100% { transform: translate(-20px, 30px); opacity: 0.4; }
        }

        /* خط كريستالي علوي شفاف يضفي لمسة زجاجية مصقولة راقية */
        #main-form-panel,
        #payment-methods-row,
        .glass-container,
        .account-block {
            border-top: 1.5px solid rgba(255, 255, 255, 0.22) !important;
        }

        /* Color Wave Animation */
        @keyframes colorWave {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .wave-bg {
            background-size: 300% 300% !important;
            animation: colorWave 5s ease infinite !important;
        }

        /* ═══ Row Add/Delete Animations ═══ */
        @keyframes rowSlideIn {
            from { opacity: 0; transform: translateY(-15px) scale(0.97); max-height: 0; }
            to { opacity: 1; transform: translateY(0) scale(1); max-height: 100px; }
        }
        @keyframes rowSlideOut {
            from { opacity: 1; transform: translateX(0) scale(1); max-height: 100px; }
            to { opacity: 0; transform: translateX(40px) scale(0.9); max-height: 0; padding: 0; margin: 0; }
        }
        .multi-row { animation: rowSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) both; }
        .multi-row.removing { animation: rowSlideOut 0.3s ease forwards; pointer-events: none; }

        /* ═══ Focus Glow Effect ═══ */
        .multi-input:focus, .v-input:focus, .ctrl-input:focus {
            outline: none !important;
            border-color: rgba(56, 189, 248, 0.6) !important;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15), 0 0 15px rgba(56, 189, 248, 0.1) !important;
            transition: border-color 0.3s, box-shadow 0.3s !important;
        }

        /* ═══ Shake Animation for Errors ═══ */
        @keyframes shakeError {
            0%, 100% { transform: translateX(0); }
            10%, 50%, 90% { transform: translateX(-6px); }
            30%, 70% { transform: translateX(6px); }
        }
        .shake-error {
            animation: shakeError 0.5s ease;
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
        }

        /* ═══ Save Success Celebration ═══ */
        @keyframes saveSuccess {
            0% { transform: scale(1); }
            30% { transform: scale(1.15); }
            50% { transform: scale(0.95); }
            70% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .save-success-anim {
            animation: saveSuccess 0.6s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }

        /* ═══ Confetti Overlay ═══ */
        @keyframes confettiFall {
            0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }
        .confetti-piece {
            position: fixed;
            width: 10px;
            height: 10px;
            top: -10px;
            z-index: 99999;
            border-radius: 2px;
            animation: confettiFall 2.5s linear forwards;
            pointer-events: none;
        }

        /* ═══ Number Counter Transition ═══ */
        .amount-animate {
            transition: transform 0.2s ease;
        }
        .amount-animate.changed {
            transform: scale(1.08);
            color: #fbbf24 !important;
        }

        /* ═══ Button Active Press ═══ */
        .crystal-btn:active, .btn-add-row-inline:active {
            transform: scale(0.92) !important;
            transition: transform 0.1s !important;
        }

        /* ═══ Transfer/Cheque Row Animations ═══ */
        .transfer-row, .cheque-row {
            animation: rowSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .transfer-row.removing, .cheque-row.removing {
            animation: rowSlideOut 0.3s ease forwards;
            pointer-events: none;
        }

        /* ═══ Payment Section Smooth Transition ═══ */
        #cash-section, #bank-section, #cheque-section {
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* ═══ Skeleton Loading ═══ */
        @keyframes skeletonShimmer {
            0% { background-position: -200px 0; }
            100% { background-position: calc(200px + 100%) 0; }
        }
        .skeleton-loading {
            background: linear-gradient(90deg, rgba(255,255,255,0.04) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.04) 75%);
            background-size: 200px 100%;
            animation: skeletonShimmer 1.5s infinite;
            border-radius: 6px;
        }

        /* ══════════════════════════════════════════════════════════════════════
           AUTOMATIC LUMINOUS EDGE ILLUMINATION (إضاءة الحواف التلقائية التفاعلية)
           تأتي الإضاءة لوحدها بمجرد وقوف المؤشر (Hover) أو الضغط والتركيز (Focus)
           ══════════════════════════════════════════════════════════════════════ */

        /* ─── 1. حقول المبالغ والأرقام: إضاءة الحواف التلقائية بمجرد الوقوف والضغط ─── */
        .transfer-amount, .cheque-amount, .block-sub-amount, .block-cash-amount, .block-bank-amount, .block-cheque-amount, .block-total-amount, .total-val, .val, .value-field, .multi-input[placeholder*="المبلغ"], .multi-input[placeholder*="القيمة"] {
            background: rgba(15, 23, 42, 0.45) !important;
            color: #ffffff !important;
            border: 3.5px solid rgba(255, 255, 255, 0.35) !important;
            border-radius: 12px !important;
            transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }

        .block-total-amount,
        input[id^="amount-"],
        .transfer-amount,
        .cheque-amount,
        .block-sub-amount,
        .multi-input[placeholder*="المبلغ"] {
            border: 3.5px solid #fbbf24 !important;
            border-radius: 12px !important;
        }

        /* عند وقوف المؤشر على أي حقل مبلغ (Hover) */
        .block-total-amount:hover,
        input[id^="amount-"]:hover,
        .transfer-amount:hover,
        .cheque-amount:hover,
        .block-sub-amount:hover,
        .multi-input[placeholder*="المبلغ"]:hover {
            border: 3.5px solid #fbbf24 !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5), 0 0 12px rgba(251, 191, 36, 0.35) !important;
            background: rgba(251, 191, 36, 0.12) !important;
            transform: translateY(-2px) scale(1.02) !important;
        }

        /* عند الضغط على المبلغ والتركيز (Focus) */
        .transfer-amount:focus, .cheque-amount:focus, .block-sub-amount:focus, .block-total-amount:focus, .multi-input[placeholder*="المبلغ"]:focus {
            border: 3.5px solid #fbbf24 !important;
            box-shadow: 0 15px 40px rgba(0,0,0,0.95), 0 0 15px rgba(251, 191, 36, 0.4) !important;
            outline: none !important;
        }

        /* حقول مبالغ التحويلات البنكية عند الوقوف */
        .block-bank-amount {
            border: 3.5px solid #06b6d4 !important;
            border-radius: 12px !important;
        }
        .block-bank-amount:hover {
            border: 3.5px solid #22d3ee !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5), 0 0 12px rgba(34, 211, 238, 0.35) !important;
            background: rgba(6, 182, 212, 0.18) !important;
            color: #ffffff !important;
            transform: translateY(-2px) scale(1.02) !important;
        }

        /* حقول مبالغ الشيكات عند الوقوف */
        .block-cheque-amount {
            border: 3.5px solid #a855f7 !important;
            border-radius: 12px !important;
        }
        .block-cheque-amount:hover {
            border: 3.5px solid #c084fc !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5), 0 0 12px rgba(192, 132, 252, 0.35) !important;
            background: rgba(147, 51, 234, 0.18) !important;
            color: #ffffff !important;
            transform: translateY(-2px) scale(1.02) !important;
        }

        /* ─── 2. منطقة الدولار (سعر التعادل): أرقام حادة بدون ظل أو هالة ─── */
        #exchange-rate {
            font-size: 1.8rem !important;
            font-weight: 900 !important;
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            filter: none !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            text-rendering: geometricPrecision !important;
            font-family: 'Consolas', 'Courier New', monospace !important;
            border: 3.5px solid #10b981 !important;
            border-radius: 12px !important;
            transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        #exchange-rate:hover,
        #exchange-rate:focus,
        #exchange-rate:active {
            border: 3.5px solid #10b981 !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5), 0 0 12px rgba(16, 185, 129, 0.4) !important;
            background: rgba(16, 185, 129, 0.2) !important;
            color: #ffffff !important;
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            transform: translateY(-2px) scale(1.04) !important;
            outline: none !important;
        }
        #label-exchange-rate {
            transition: all 0.25s ease;
        }
        #label-exchange-rate:hover {
            color: #34d399 !important;
            text-shadow: none !important;
        }
        #label-exchange-rate:hover span:last-child {
            transform: rotate(90deg) scale(1.25);
            filter: drop-shadow(0 0 4px rgba(251,191,36,0.6));
        }

        /* ─── 3. منطقة إجمالي المحلي والمعادل بالأجنبي (حواف حمراء موحدة بقوة 3.5px وخلفية بيضاء) ─── */
        #amount-local-hdr, #amount-foreign {
            font-size: 2.6rem !important;
            font-weight: 900 !important;
            color: #ef4444 !important;
            -webkit-text-fill-color: #ef4444 !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            text-align: center !important;
            border: 3.5px solid #ef4444 !important;
            border-radius: 12px !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important;
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            filter: none !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            text-rendering: geometricPrecision !important;
            font-family: 'Consolas', 'Courier New', monospace !important;
            width: 100% !important;
            box-sizing: border-box !important;
            transition: all 0.25s ease !important;
        }
        #amount-local-hdr:hover, #amount-local-hdr:focus,
        #amount-foreign:hover, #amount-foreign:focus {
            border: 3.5px solid #dc2626 !important;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2) !important;
            color: #ef4444 !important;
            -webkit-text-fill-color: #ef4444 !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            filter: none !important;
            transform: scale(1.02) !important;
            outline: none !important;
        }

        /* ─── 4. إنارة الحواف في منطقة «من حساب» باللون الياقوتي الراقي ─── */
        #primary-inputs-container .custom-dropdown-input-wrap {
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        #primary-inputs-container .custom-dropdown-input-wrap:hover,
        #primary-inputs-container .custom-dropdown-input-wrap:focus-within {
            box-shadow: 0 0 20px rgba(244, 63, 94, 0.35);
        }

        #main-account-search {
            border: 1.5px solid rgba(244, 63, 94, 0.6) !important;
            transition: all 0.3s ease !important;
        }
        #main-account-search:hover,
        #main-account-search:focus {
            border: 2px solid #f43f5e !important;
            box-shadow: 0 0 24px rgba(244, 63, 94, 0.45), inset 0 0 8px rgba(244, 63, 94, 0.15) !important;
            transform: translateY(-2px);
            outline: none !important;
        }

        /* ─── 5. إنارة الحواف في منطقة «إلى حساب» التلقائية ─── */
        .block-account-search {
            transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .block-account-search:hover,
        .block-account-search:focus {
            border: 2px solid #34d399 !important;
            box-shadow: 0 0 28px rgba(52, 211, 153, 0.95), 0 0 50px rgba(16, 185, 129, 0.5), inset 0 0 10px rgba(52, 211, 153, 0.25) !important;
            transform: translateY(-2px);
            outline: none !important;
        }

        /* ─── 6. كل شيء عندما نأتي إليه الإضاءة تأتي لوحدها (Universal Controls) ─── */
        #currency:hover, #currency:focus {
            border: 2px solid #fbbf24 !important;
            box-shadow: 0 0 22px rgba(251, 191, 36, 0.9), 0 0 40px rgba(245, 158, 11, 0.45), inset 0 0 8px rgba(251, 191, 36, 0.25) !important;
            transform: translateY(-2px);
            outline: none !important;
        }

        #voucher-desc:hover, #voucher-desc:focus,
        input[id^="statement-"]:hover, input[id^="statement-"]:focus,
        .block-bank-desc-input:hover, .block-bank-desc-input:focus,
        .block-cheque-desc-input:hover, .block-cheque-desc-input:focus {
            border-color: #fbbf24 !important;
            box-shadow: 0 0 22px rgba(251, 191, 36, 0.85), inset 0 0 8px rgba(251, 191, 36, 0.25) !important;
            outline: none !important;
        }

        /* تفاصيل السند العلوية (السند، التاريخ، المرجع) */
        #payment-methods-row > div:last-child > div {
            transition: all 0.28s ease;
        }
        #payment-methods-row > div:last-child > div:hover,
        #payment-methods-row > div:last-child > div:focus-within {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 24px rgba(56, 189, 248, 0.8), inset 0 0 8px rgba(56, 189, 248, 0.25) !important;
            transform: translateY(-2px);
        }
        #voucher-ref:hover, #voucher-ref:focus {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 20px rgba(56, 189, 248, 0.85) !important;
            outline: none !important;
        }

        /* ═══ زر كشف الحساب: إنارة خضراء ساطعة بالحواف عند الوقوف عليه ═══ */
        .btn-statement-glow {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.3)) !important;
            border: 1.5px solid rgba(16, 185, 129, 0.55) !important;
            border-right: none !important;
            color: #34d399 !important;
            padding: 0 14px !important;
            border-top-left-radius: 8px !important;
            border-bottom-left-radius: 8px !important;
            cursor: pointer !important;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: auto !important;
            font-size: 0.8rem !important;
            font-weight: bold !important;
            white-space: nowrap !important;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.3), inset 0 1px 2px rgba(255, 255, 255, 0.2) !important;
            position: relative !important;
            overflow: hidden !important;
            user-select: none !important;
        }

        .btn-statement-glow:hover, 
        .btn-statement-glow:focus {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 1.5px solid #10b981 !important;
            border-right: none !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(16, 185, 129, 0.85), 0 0 24px rgba(16, 185, 129, 0.45) !important;
            transform: scale(1.03) !important;
            filter: none !important;
            text-shadow: none !important;
            z-index: 5 !important;
        }

        .btn-statement-glow:active {
            transform: scale(0.97) !important;
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.7) !important;
        }
        
        /* 🌟 مقاسات الوضع الطبيعي الأساسي لحقول المبالغ والأرقام */
        input.block-total-amount, input.block-bank-amount, input.block-cheque-amount, input.transfer-amount, input.cheque-amount, input.block-sub-amount {
            font-size: 3.1rem !important;
            height: 62px !important;
            font-weight: 900 !important;
            color: #ffffff !important;
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            filter: none !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            text-rendering: geometricPrecision !important;
            font-family: 'Consolas', 'Courier New', monospace !important;
            will-change: transform, width, height, box-shadow;
            transition: all 0.42s cubic-bezier(0.16, 1, 0.3, 1) !important;
            transform-origin: center right;
        }
        
        input.block-transfer-no-input, input.block-cheque-no-input, input.transfer-no, input.cheque-no {
            font-size: 1.4rem !important;
            height: 62px !important;
            font-weight: 900 !important;
            letter-spacing: 0.5px !important;
            color: #ffffff !important;
            text-align: center !important;
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            filter: none !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            text-rendering: geometricPrecision !important;
            font-family: 'Consolas', 'Courier New', monospace !important;
            will-change: transform, width, height, box-shadow;
            transition: all 0.42s cubic-bezier(0.16, 1, 0.3, 1) !important;
            transform-origin: center right;
        }

        /* 🏷️ خط مريح وواضح لنافذة كتابة النص التوضيحي (Placeholder) ليظهر بالكامل دون قص */
        input.block-transfer-no-input::placeholder,
        input.block-cheque-no-input::placeholder,
        input.transfer-no::placeholder,
        input.cheque-no::placeholder {
            font-size: 1.15rem !important;
            font-weight: 800 !important;
            color: rgba(255, 255, 255, 0.55) !important;
            letter-spacing: 0px !important;
            font-family: 'Tajawal', sans-serif !important;
            text-align: center !important;
        }

        input.block-bank-amount::placeholder,
        input.block-cheque-amount::placeholder,
        input.transfer-amount::placeholder,
        input.cheque-amount::placeholder,
        input.block-sub-amount::placeholder {
            font-size: 1.25rem !important;
            font-weight: 800 !important;
            color: rgba(255, 255, 255, 0.55) !important;
            letter-spacing: 0px !important;
            font-family: 'Tajawal', sans-serif !important;
            text-align: center !important;
        }
        
        /* 🔍 تضخيم وتوسيع احترافي سينمائي لحقول المبالغ (ذهب ملكي مشع) عند النقر والتركيز */
        input.block-total-amount:focus,
        input.block-bank-amount:focus,
        input.block-cheque-amount:focus,
        input.transfer-amount:focus,
        input.cheque-amount:focus,
        input.block-sub-amount:focus {
            font-size: 4.5rem !important;
            height: 98px !important;
            width: 490px !important;
            min-width: 460px !important;
            max-width: none !important;
            position: relative !important;
            z-index: 99999 !important;
            margin-right: -60px !important;
            color: #fbbf24 !important; /* Gold / Amber */
            -webkit-text-fill-color: #fbbf24 !important;
            caret-color: #fbbf24 !important;
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            filter: none !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            text-rendering: geometricPrecision !important;
            font-family: 'Consolas', 'Courier New', monospace !important;
            background: rgba(13, 17, 28, 0.98) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            border: 3.5px solid #fbbf24 !important;
            border-radius: 14px !important;
            transform: translateY(-4px) scale(1.015) !important;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.92), 
                        0 0 25px rgba(251, 191, 36, 0.45), 
                        inset 0 1px 1px rgba(255, 255, 255, 0.25) !important;
            transition: all 0.42s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        
        /* 🔍 تضخيم وتوسيع احترافي سينمائي لحقول أرقام التحويلات والشيكات (أزرق ياقوتي سايان كهربائي) */
        input.block-transfer-no-input:focus,
        input.block-cheque-no-input:focus,
        input.transfer-no:focus,
        input.cheque-no:focus {
            font-size: 4.5rem !important;
            height: 98px !important;
            width: 490px !important;
            min-width: 460px !important;
            max-width: none !important;
            position: relative !important;
            z-index: 99999 !important;
            margin-right: -60px !important;
            letter-spacing: 1.5px !important;
            color: #38bdf8 !important; /* Vivid Cyan */
            -webkit-text-fill-color: #38bdf8 !important;
            caret-color: #38bdf8 !important;
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            filter: none !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            text-rendering: geometricPrecision !important;
            font-family: 'Consolas', 'Courier New', monospace !important;
            background: rgba(13, 17, 28, 0.98) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            border: 3.5px solid #38bdf8 !important;
            border-radius: 14px !important;
            transform: translateY(-4px) scale(1.015) !important;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.92), 
                        0 0 25px rgba(56, 189, 248, 0.45), 
                        inset 0 1px 1px rgba(255, 255, 255, 0.25) !important;
            transition: all 0.42s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }

        /* Larger Bank Name Font */
        input.block-bank-name-input, input.block-cheque-bank-input, input.block-bank-desc-input, input.block-cheque-desc-input {
            font-size: 1.55rem !important;
            height: 62px !important;
            font-weight: bold !important;
        }

        /* Bank Row Layout Overrides */
        .bank-row > div:nth-child(2) { flex: 1.5 !important; min-width: 140px !important; width: auto !important; }
        .bank-row > div:nth-child(3) { flex: 2 !important; min-width: 160px !important; width: auto !important; }
        .bank-row > div:nth-child(4) { flex: 1.6 !important; min-width: 140px !important; }
        .bank-row > div:nth-child(5) { flex: 3 !important; min-width: 250px !important; }

        /* Cheque Row Layout Overrides */
        .cheque-row > div:nth-child(2) { flex: 1.5 !important; min-width: 140px !important; width: auto !important; }
        .cheque-row > div:nth-child(3) { flex: 2 !important; min-width: 160px !important; width: auto !important; }
        .cheque-row > div:nth-child(4) { flex: 0.8 !important; min-width: 90px !important; } /* Cheque No (Smaller) */
        .cheque-row > div:nth-child(5) { flex: 1.2 !important; min-width: 120px !important; } /* Cheque Date */
        .cheque-row > div:nth-child(6) { flex: 2.5 !important; min-width: 210px !important; } /* Cheque Desc (Wider) */

        /* ═══════════════════════════════════════════════════════════════════════════════
           🎯 حواف أرقام ومبالغ حادة ونقية جداً بدون أي ظل أو إضاءة أو هالة (Sharp & Crisp Numbers)
           ═══════════════════════════════════════════════════════════════════════════════ */
        #amount-local-hdr,
        #amount-foreign,
        #exchange-rate,
        input.block-total-amount,
        input.block-bank-amount,
        input.block-cheque-amount,
        input.block-sub-amount,
        input.transfer-amount,
        input.cheque-amount,
        input.block-transfer-no-input,
        input.block-cheque-no-input,
        input.transfer-no,
        input.cheque-no,
        input[id^="amount-"] {
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            filter: none !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            text-rendering: geometricPrecision !important;
            font-feature-settings: "tnum" 1, "zero" 1 !important;
            font-variant-numeric: tabular-nums !important;
        }

        #amount-local-hdr:focus, #amount-local-hdr:hover,
        #amount-foreign:focus, #amount-foreign:hover,
        #exchange-rate:focus, #exchange-rate:hover,
        input.block-total-amount:focus, input.block-total-amount:hover,
        input.block-bank-amount:focus, input.block-bank-amount:hover,
        input.block-cheque-amount:focus, input.block-cheque-amount:hover,
        input.block-sub-amount:focus, input.block-sub-amount:hover,
        input.transfer-amount:focus, input.transfer-amount:hover,
        input.cheque-amount:focus, input.cheque-amount:hover,
        input.block-transfer-no-input:focus, input.block-transfer-no-input:hover,
        input.block-cheque-no-input:focus, input.block-cheque-no-input:hover,
        input.transfer-no:focus, input.transfer-no:hover,
        input.cheque-no:focus, input.cheque-no:hover,
        input[id^="amount-"]:focus, input[id^="amount-"]:hover {
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            filter: none !important;
        }

        /* Custom Number Properties Menu (Enhanced Luxury) */
        #custom-num-menu {
            display: none;
            position: absolute;
            z-index: 100000;
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.98), rgba(30, 41, 59, 0.96));
            backdrop-filter: blur(14px);
            border: 1.5px solid rgba(56, 189, 248, 0.6);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8), 0 0 25px rgba(56, 189, 248, 0.25);
            border-radius: 14px;
            padding: 18px 20px;
            width: 320px;
            color: #e2e8f0;
            font-family: 'Tajawal', 'Inter', sans-serif;
            direction: rtl;
            user-select: none;
        }
        #custom-num-menu .props-header {
            font-weight: 800;
            color: #38bdf8;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            text-align: center;
            font-size: 1.12rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        #custom-num-menu .props-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        #custom-num-menu .props-row-label {
            color: #cbd5e1;
            font-size: 0.92rem;
            font-weight: 700;
        }
        #custom-num-menu .weight-btn {
            background: #1e293b;
            border: 1px solid #334155;
            color: #94a3b8;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }
        #custom-num-menu .weight-btn.active {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.3), rgba(217, 119, 6, 0.5));
            border-color: #fbbf24;
            color: #fbbf24;
            box-shadow: 0 0 10px rgba(245, 158, 11, 0.4);
        }
        #custom-num-menu .color-dot {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            transition: transform 0.15s, border-color 0.15s;
        }
        #custom-num-menu .color-dot:hover {
            transform: scale(1.25);
            border-color: #ffffff;
        }
        #custom-num-menu .color-dot.active {
            border-color: #ffffff;
            box-shadow: 0 0 10px currentColor;
            transform: scale(1.15);
        }
        #custom-num-menu .prop-action-btn {
            background: #1e293b;
            border: 1px solid #334155;
            color: #38bdf8;
            border-radius: 6px;
            width: 32px;
            height: 32px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.15rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
        }
        #custom-num-menu .prop-action-btn:hover {
            background: #334155;
            border-color: #38bdf8;
            color: #fff;
            transform: scale(1.08);
        }

        /* ═══════════════════════════════════════════════════════════
           🌟 AUTO-ASCENT SMOOTH SCROLL & EXECUTIVE WORKSPACE CONTROLS
           ═══════════════════════════════════════════════════════════ */
        /* 1. Floating Return-to-Header Glass Pill */
        .auto-scroll-return-pill {
            position: fixed;
            top: 14px;
            left: 50%;
            transform: translateX(-50%) translateY(-65px);
            opacity: 0;
            pointer-events: none;
            z-index: 999999;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.94), rgba(30, 41, 59, 0.96));
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1.5px solid rgba(56, 189, 248, 0.45);
            border-radius: 30px;
            padding: 6px 18px;
            color: #f8fafc;
            font-size: 0.85rem;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.65), 0 0 20px rgba(56, 189, 248, 0.25);
            display: flex;
            align-items: center;
            gap: 9px;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease, box-shadow 0.3s ease, border-color 0.3s ease, background 0.3s ease;
            user-select: none;
        }
        .auto-scroll-return-pill.visible {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
            pointer-events: auto;
        }
        .auto-scroll-return-pill:hover {
            border-color: #38bdf8;
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.98), rgba(15, 23, 42, 0.98));
            color: #38bdf8;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.8), 0 0 28px rgba(56, 189, 248, 0.55);
            transform: translateX(-50%) translateY(-2px) scale(1.03);
        }
        .auto-scroll-return-pill .pill-arrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: rgba(56, 189, 248, 0.2);
            color: #38bdf8;
            font-size: 0.75rem;
            transition: all 0.25s;
        }
        .auto-scroll-return-pill:hover .pill-arrow {
            background: #38bdf8;
            color: #0f172a;
            transform: translateY(-2px);
        }

        /* 3. Header Navigation Quick Toggle in payment-methods-row */
        .header-nav-toggle-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 8px;
            height: 38px;
            padding: 0 12px;
            color: #cbd5e1;
            font-size: 0.84rem;
            font-weight: 800;
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            outline: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
            user-select: none;
        }
        .header-nav-toggle-btn:hover {
            background: rgba(56, 189, 248, 0.2);
            border-color: #38bdf8;
            color: #38bdf8;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.35);
        }
        .header-nav-toggle-btn .toggle-icon {
            font-size: 0.9rem;
            color: #38bdf8;
            display: flex;
            align-items: center;
            transition: transform 0.25s;
        }
        .header-nav-toggle-btn:hover .toggle-icon {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <div class="ambient-bg">
        <div class="orb orb-1"></div><div class="orb orb-2"></div><div class="orb orb-3"></div>
        <div class="glass-ripple-mesh glass-ripple-1"></div>
        <div class="glass-ripple-mesh glass-ripple-2"></div>
        <div class="glass-ripple-mesh glass-ripple-3"></div>
        <div class="glass-specular-shimmer"></div>
    </div>
    <div class="toast-container" id="toast-container"></div>

    <div class="pos-wrapper" style="max-width: 98%; margin: 0 auto; padding: 15px 10px; padding-bottom: 100px;">
        <?php $header_theme_class = 'header-theme-sales'; ?>
        <?php include 'includes/header.php'; ?>

        <input type="hidden" id="voucher-type" value="payment">

        <div class="split-layout-wrapper" id="main-layout-wrapper" style="display: block; width: 100%;">
            <!-- Main Content area (Full width) -->
            <div class="main-panel glass-panel" id="main-form-panel" style="padding: 25px; position: relative; width: 100%;">
                
                <!-- Real-Time Concurrency Lock Warning Banner (تنبيه التداخل اللحظي وقفل السند) -->
                <div id="concurrency-warning-badge" style="display:none; margin-bottom:12px; width:100%; box-sizing:border-box; animation:slideDown 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; background:linear-gradient(135deg, rgba(30, 20, 10, 0.96) 0%, rgba(120, 53, 15, 0.45) 50%, rgba(30, 20, 10, 0.96) 100%); border:2px solid #fbbf24; border-radius:14px; padding:10px 16px; box-shadow:0 10px 30px rgba(0,0,0,0.65), 0 0 25px rgba(245,158,11,0.35); backdrop-filter:blur(14px); color:#ffffff; animation:concurrencyPulse 3s infinite alternate ease-in-out;">
                        <div style="display:flex; align-items:center; gap:12px; min-width:0; flex:1;">
                            <!-- Animated Warning Icon -->
                            <div style="width:42px; height:42px; min-width:42px; border-radius:12px; background:rgba(245,158,11,0.25); border:1.8px solid #fbbf24; display:flex; align-items:center; justify-content:center; font-size:1.5rem; box-shadow:0 0 15px rgba(245,158,11,0.5); animation:pulseScale 1.4s infinite alternate;">
                                ⚠️
                            </div>
                            <!-- Message Details -->
                            <div style="display:flex; flex-direction:column; gap:2px; min-width:0;">
                                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                    <span style="font-size:1.02rem; font-weight:900; color:#fbbf24; text-shadow:0 0 12px rgba(251,191,36,0.6);">تنبيه تداخل متزامن:</span>
                                    <span style="font-size:0.95rem; font-weight:800; color:#ffffff;">هذا السند مفتوح حالياً للمراجعة بواسطة المحاسب <span id="concurrency-lock-username" style="color:#38bdf8; background:rgba(15,23,42,0.8); border:1px solid #38bdf8; padding:2px 10px; border-radius:14px; font-weight:900;">أحمد</span> <span id="concurrency-lock-time" style="color:#fef08a; font-size:0.85rem; font-weight:700;">(منذ قليل)</span></span>
                                </div>
                                <span style="font-size:0.80rem; color:#cbd5e1; font-weight:700; line-height:1.4;">يُنصح بعدم التعديل المتزامن لتفادي تعارض البيانات والكتابة فوق عمل زميلك.</span>
                            </div>
                        </div>
                        <!-- Actions -->
                        <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                            <button type="button" onclick="ConcurrencyLockManager.takeoverLock()" style="background:linear-gradient(135deg, #d97706, #b45309); border:1.5px solid #fbbf24; color:#ffffff; padding:6px 14px; border-radius:10px; font-size:0.86rem; font-weight:800; cursor:pointer; display:flex; align-items:center; gap:6px; box-shadow:0 4px 15px rgba(245,158,11,0.4); transition:all 0.2s;" title="أخذ الإذن واستلام التحرير لهذا السند">
                                <span>🔄 استلام التحرير</span>
                            </button>
                            <button type="button" onclick="ConcurrencyLockManager.dismissBadge()" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); color:#cbd5e1; width:34px; height:34px; border-radius:10px; font-size:1.1rem; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s;" title="إغلاق التنبيه">
                                ✕
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Unified Top Row (Payment Methods Row - Slim Single Level) -->
                <div id="payment-methods-row" style="display: flex; align-items: center; justify-content: flex-start; gap: 10px; margin-bottom: 12px; background: linear-gradient(135deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.98)); padding: 6px 14px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.18); flex-wrap: nowrap; transition: all 0.4s ease; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.45); position: relative; overflow-x: auto; min-height: 48px; box-sizing: border-box; width: 100%;">
                    
                    <!-- Quick Toggle Header Button (إظهار / إخفاء الترويسة) -->
                    <button type="button" id="btn-toggle-top-header" class="header-nav-toggle-btn" onclick="AutoHeaderScroll.toggleHeader()" title="الرجوع إلى أعلى الصفحة وإظهار الترويسة">
                        <span class="toggle-icon"><i class="fa-solid fa-angles-up"></i></span>
                        <span class="toggle-text">الترويسة</span>
                    </button>

                    <!-- User Icon Only (حذف كلمة بيان الطرف الرئيسي والإبقاء على الأيقونة فقط) -->
                    <div id="main-party-title" style="display: flex; align-items: center; justify-content: center; font-size: 1.35rem; color: #ffffff; flex-shrink: 0; padding: 0 4px;" title="الطرف الرئيسي">
                        <span>👤</span>
                    </div>

                    <!-- Toggles: صرف إلى مجموعة / استلام من مجموعة -->
                    <div style="display: flex; gap: 6px; flex-shrink: 0;">
                        <button id="btn-payment" class="voucher-type-btn active-payment" onclick="setVoucherType('payment')" title="الصرف من حساب واحد إلى مجموعة حسابات">
                            <span class="btn-icon"><i class="fa-solid fa-wallet"></i></span> <span class="btn-text">صرف إلى مجموعة</span>
                        </button>
                        <button id="btn-receipt" class="voucher-type-btn" onclick="setVoucherType('receipt')" title="استلام في حساب واحد من مجموعة حسابات">
                            <span class="btn-icon"><i class="fa-solid fa-sack-dollar"></i></span> <span class="btn-text">استلام من مجموعة</span>
                        </button>
                    </div>

                    <!-- السند مع أزرار التنقل -->
                    <div style="display: flex; align-items: center; gap: 4px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; height: 38px; padding: 0 8px; flex-shrink: 0;">
                        <button type="button" onclick="navigateVoucher(-1)" style="background: transparent; border: none; color: #38bdf8; cursor: pointer; font-size: 0.95rem; padding: 0 4px; outline: none; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'" title="السند السابق">◀</button>
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <span style="color: #94a3b8; font-size: 0.92rem; font-weight: bold; white-space: nowrap;">📄 السند:</span>
                            <input type="text" id="voucher-no-display" readonly style="background: transparent !important; border: none !important; color: #fff; font-weight: bold; width: 95px; text-align: center; outline: none !important; font-size: 1.25rem; box-shadow: none !important; padding: 0;" value="">
                        </div>
                        <button type="button" onclick="navigateVoucher(1)" style="background: transparent; border: none; color: #38bdf8; cursor: pointer; font-size: 0.95rem; padding: 0 4px; outline: none; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'" title="السند التالي">▶</button>
                    </div>

                    <div id="multi-voucher-user-audit-container" style="display: flex; align-items: center; flex-shrink: 0;"></div>

                    <!-- التاريخ -->
                    <div style="display: flex; align-items: center; gap: 6px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; height: 38px; padding: 0 10px; flex-shrink: 0;">
                        <span style="color: #94a3b8; font-size: 0.92rem; font-weight: bold; white-space: nowrap;">📅 التاريخ:</span>
                        <input type="date" id="voucher-date" style="background: transparent !important; border: none !important; color: #fff; font-weight: bold; padding: 0; margin: 0; height: 100%; width: 135px; outline: none !important; box-shadow: none !important; font-size: 1.05rem; -webkit-appearance: none; appearance: none; cursor: pointer;">
                    </div>

                    <!-- رقم المرجع -->
                    <div style="display: flex; align-items: center; flex-shrink: 0;">
                        <input type="text" autocomplete="off" id="voucher-ref" class="form-input" placeholder="رقم المرجع (اختياري)" style="height: 38px; width: 140px; background: rgba(0,0,0,0.3); font-size: 0.88rem; padding: 0 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); color: #fff; margin: 0;">
                    </div>

                    <!-- زر صندوق إشعارات بنكك والتحصيلات الواردة من العملاء -->
                    <button type="button" id="btn-inbound-receipts-drawer" class="btn crystal-btn" onclick="toggleInboundReceiptsDrawer()" style="height: 38px; padding: 0 12px; border-radius: 8px; font-weight: 800; font-size: 0.88rem; background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(5, 150, 105, 0.35)); border: 1.5px solid #10b981; color: #a7f3d0; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; flex-shrink: 0; box-shadow: 0 0 15px rgba(16, 185, 129, 0.3); transition: all 0.2s;" title="صندوق إشعارات تحويلات بنكك الواردة من العملاء ومطابقتها فورياً بالسند">
                        <span style="font-size: 1.15rem; filter: drop-shadow(0 0 4px #10b981);">📥</span>
                        <span style="white-space: nowrap;">إشعارات بنكك</span>
                        <span id="inbound-receipts-badge-count" style="background: #ef4444; color: #ffffff; padding: 1px 7px; border-radius: 10px; font-size: 0.75rem; font-weight: 900; box-shadow: 0 0 8px rgba(239,68,68,0.8); display: inline-block;">0</span>
                    </button>
                </div>

                <!-- Primary Inputs Glass Container -->
                <div id="primary-inputs-container" class="glass-container glass-red" style="position: relative; z-index: 15;">
                    <!-- Form Inputs Row -->
                    <div style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; overflow: visible; padding-bottom: 10px; margin-bottom: 15px;">

                        <div style="flex: 0 0 35%; max-width: 35%; min-width: 250px;">
                            <label id="main-account-label" style="color: var(--red, #f43f5e); font-size: 1.25rem !important; font-weight: 800 !important; margin-bottom: 5px; display: flex; align-items: center; gap: 8px; transition: color 0.3s; white-space: nowrap;">من حساب (صرف) <span class="animated-arrow-left" style="color: var(--red, #f43f5e);">←</span></label>
                            <div class="custom-dropdown-input-wrap" style="flex-grow: 1; width: 100%; min-width: 0; position: relative; display: flex;">
                                <input type="hidden" id="main-account">
                                <input type="text" autocomplete="off" class="form-control form-select" id="main-account-search" placeholder="ابحث عن حساب الصرف (خزينة/بنك)..." onfocus="showAccountDropdown('main-account')" onblur="hideAccountDropdownDelayed('main-account')" oninput="filterAccountDropdown('main-account')" style="background-color: var(--surface, var(--input-bg)); color: var(--amber, #fbbf24); font-size: 1.4rem !important; font-weight: 900 !important; border: 1px solid rgba(244, 63, 94, 0.5); flex: 1; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                <button type="button" class="btn-statement-glow" onclick="openLedgerForMainAccount()" title="دفتر الأستاذ العام"><i class="fa fa-book" style="margin-left: 4px;"></i> كشف حساب</button>
                                <div class="custom-dropdown-list" id="main-account-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; margin-top: 5px; z-index: 9999;"></div>
                            </div>
                        </div>

                        <div style="flex: 0.8; min-width: 90px;">
                            <label id="currency-label" style="color: var(--amber, #fbbf24); font-size: 0.85rem; margin-bottom: 5px; display: block; white-space: nowrap;">العملة</label>
                            <select id="currency" class="form-input system-currency-select input-glow-focus hover-glow" onchange="typeof onCurrencyChange === 'function' ? onCurrencyChange() : (typeof updateCurrency === 'function' ? updateCurrency() : null);" style="border-radius: 10px; flex-grow: 1; min-width: 0; width: 100%; font-size: 1.1rem !important; font-weight: 800;">
                                <option value="SDG">جنيه (SDG)</option>
                                <option value="USD">دولار (USD)</option>
                                <option value="AED">درهم (AED)</option>
                            </select>
                        </div>
                        
                        <div style="flex: 0.8; min-width: 95px; position: relative;">
                            <label id="label-exchange-rate" style="color: var(--amber, #fbbf24); font-size: 0.85rem; margin-bottom: 5px; display: flex; align-items: center; justify-content: space-between; white-space: nowrap; cursor: pointer;" onclick="openNumPropsFor('exchange-rate', event)" title="كليك يمين أو يسار لتخصيص الخصائص">
                                <span>التعادل (سعر $)</span>
                                <span style="font-size: 0.85rem; opacity: 0.85; transition: transform 0.2s;" onmouseover="this.style.transform='rotate(45deg)'" onmouseout="this.style.transform='rotate(0deg)'" title="خصائص سعر الدولار">⚙️</span>
                            </label>
                            <input type="text" inputmode="decimal" autocomplete="off" id="exchange-rate" class="form-input" value="<?php echo htmlspecialchars((string)$systemUsdRate); ?>" readonly style="font-weight: 900; font-family: monospace; text-align: center; cursor: pointer; background: rgba(16,185,129,0.1); color: var(--green, #10b981); border-radius: 10px;" onclick="if(typeof window.openGlobalExchangeRateModal === 'function') window.openGlobalExchangeRateModal((r)=>{this.dataset.raw=r; this.dataset.userEdited='1'; this.value=r; if(typeof applyNumPropsToAll === 'function') applyNumPropsToAll(); if(typeof calcTotal === 'function') calcTotal(); else if(typeof recalcAll === 'function') recalcAll();});" oninput="if(typeof calcTotal === 'function') calcTotal();" title="كليك يسار لتعديل السعر | كليك يمين لتخصيص (السمك، الحجم، اللون، الخانات، الفاصلة)">
                        </div>
                        
                        <!-- إجمالي المحلي والمعادل بالأجنبي متساويين مناصفة 50% / 50% (حواف أرقام حادة بدون ظل أو هالة) -->
                        <div id="totals-equal-container" style="display: flex; gap: 15px; flex: 2.8; min-width: 380px; align-items: flex-end;">
                            <div style="flex: 1 1 50%; min-width: 0; width: 50%;">
                                <label id="label-amount-local" style="color: var(--green, #10b981); font-size: 1.3rem; margin-bottom: 5px; display: block; font-weight: bold; white-space: nowrap;">💵 الإجمالي بالمحلي (SDG)</label>
                                <input type="text" id="amount-local-hdr" class="form-input" value="0.00" readonly style="font-size: 2.6rem; font-weight: 900; font-family: 'Consolas', 'Courier New', monospace; color: #ef4444 !important; -webkit-text-fill-color: #ef4444 !important; background: #ffffff !important; text-align: center; border: 3.5px solid #ef4444 !important; border-radius: 12px !important; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important; text-shadow: none !important; -webkit-text-stroke: 0px !important; filter: none !important; -webkit-font-smoothing: antialiased; text-rendering: geometricPrecision; width: 100%;">
                            </div>
                            
                            <div style="flex: 1 1 50%; min-width: 0; width: 50%;">
                                <label id="label-amount-foreign" style="color: var(--red, #ef4444); font-size: 1.3rem; margin-bottom: 5px; display: block; font-weight: bold; white-space: nowrap;">💵 المعادل بالأجنبي (USD)</label>
                                <input type="text" id="amount-foreign" class="form-input" value="0.00" readonly style="font-size: 2.6rem; font-weight: 900; font-family: 'Consolas', 'Courier New', monospace; color: #ef4444 !important; -webkit-text-fill-color: #ef4444 !important; background: #ffffff !important; text-align: center; border: 3.5px solid #ef4444 !important; border-radius: 12px !important; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important; text-shadow: none !important; -webkit-text-stroke: 0px !important; filter: none !important; -webkit-font-smoothing: antialiased; text-rendering: geometricPrecision; width: 100%;">
                            </div>
                        </div>
                    </div>

                    <!-- ═══ Grand Total Live Tafqeet Bar (التفقيط المالي لإجمالي السند وحماية الأصفار) ═══ -->
                    <div id="grand-tafqeet-container" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; background: var(--bg-secondary, linear-gradient(135deg, rgba(15, 23, 42, 0.88) 0%, rgba(30, 41, 59, 0.85) 100%)); border: 1.5px solid rgba(245, 158, 11, 0.35); border-radius: 10px; padding: 10px 16px; margin-top: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); transition: all 0.35s ease;">
                        <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0;">
                            <span style="font-size: 1.35rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3)); flex-shrink: 0;">📜</span>
                            <span style="color: var(--amber, #fbbf24); font-weight: 800; font-size: 0.95rem; white-space: nowrap; flex-shrink: 0;">إجمالي السند بالحروف:</span>
                            <span id="grand-tafqeet-text" style="color: var(--text-primary, #fef08a); font-size: 1.12rem; font-weight: 900; font-family: 'Tajawal', sans-serif; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">فقط صفر جنيه سوداني لا غير</span>
                        </div>
                        <div id="grand-fat-finger-alert" style="display: none; align-items: center; gap: 6px; font-size: 0.88rem; font-weight: 800; padding: 4px 12px; border-radius: 8px; flex-shrink: 0; transition: all 0.3s ease;"></div>
                    </div>

                    <div class="mcp-voucher-desc-bar" style="display: flex; align-items: center; gap: 15px; background: var(--bg-secondary, rgba(0,0,0,0.25)); padding: 12px 15px; min-height: 48px; border-radius: 10px; border: 1px solid var(--border, rgba(255,255,255,0.08)); margin-top: 12px;">
                        <label style="color: var(--amber, #fbbf24); font-size: 0.95rem; font-weight: bold; margin: 0; white-space: nowrap; display: flex; align-items: center; gap: 5px;">✍️ بيان حساب الصرف:</label>
                        <input type="text" autocomplete="off" id="voucher-desc" class="form-input" placeholder="اكتب البيان التفصيلي لحساب الصرف هنا (الذي يظهر في كشف الأستاذ العام)..." style="flex: 1; margin: 0; background: var(--surface, rgba(15,23,42,0.4)); border: 1px solid var(--border, rgba(255,255,255,0.1)); padding: 8px 12px; height: 38px !important; font-size: 0.95rem; color: var(--text-primary, #fff); border-radius: 6px;">
                    </div>
                </div>

                <!-- ═══ حاوية الحسابات (Accounts Blocks Container) ═══ -->
                <div id="accounts-blocks-container" style="display: flex; flex-direction: column; gap: 20px; position: relative; z-index: 10;">
                    <!-- Blocks will be dynamically inserted here -->
                </div>

                <!-- ═══ زر إضافة حساب جديد ═══ -->
                <div style="display: flex; justify-content: center; align-items: center; margin-top: 6px; margin-bottom: 12px;">
                    <button type="button" class="btn-add-account-block" onclick="addAccountBlock()" style="padding: 7px 22px; border-radius: 10px; background: rgba(16,185,129,0.14); color: var(--green, #34d399); border: 2px solid #10b981 !important; font-weight: 800; font-size: 0.94rem; cursor: pointer; font-family: 'Tajawal', sans-serif; transition: all 0.25s ease; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 3px 12px rgba(16,185,129,0.2);">
                        <span style="font-size: 1.05rem;">➕</span> <span>إضافة حساب مستلم / دافع آخر</span>
                    </button>
                </div>

                <!-- ═══ قالب الكتلة المخفي (Hidden Template) ═══ -->
                <template id="account-block-template">
                    <div class="account-block glass-container glass-green" data-block-id="{ID}" style="position: relative; padding: 15px; border-radius: 12px; transition: all 0.3s ease;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; padding-bottom: 0px;">
                            <div style="display: flex; align-items: center; gap: 8px; font-size: 1.25rem; font-weight: 800; color: var(--green, #34d399);">
                                <span>📋</span> <span class="block-label">إلى حساب (مستلم)</span> <span class="animated-arrow-right" style="color: var(--green, #34d399);">→</span>
                            </div>
                            <button type="button" class="btn-remove-block" data-action="remove-block" data-block="{ID}" style="background: rgba(239,68,68,0.15); border: 3.5px solid #ef4444; color: #ef4444; border-radius: 50%; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.1rem; transition: all 0.2s;" title="حذف هذا الحساب">✖</button>
                        </div>

                        <!-- Row 1: Account, Amount, Statement, Cost Center, and Unified 5-Button Base -->
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 12px;">
                            <!-- Account (توسعة مسافة الحساب ليكون بنفس حجم خانة بحث عن حساب الصرف الخزنة كشف حساب 35%) -->
                            <div style="flex: 0 0 35%; max-width: 35%; min-width: 250px; position: relative; display: flex; flex-direction: column;">
                                <div class="custom-dropdown-input-wrap" style="position: relative; flex: 1; display: flex;">
                                    <input type="hidden" id="account-{ID}-id" class="block-account-id">
                                    <input type="hidden" id="account-{ID}" class="block-account-name">
                                    <input type="text" autocomplete="off" class="form-input block-account-search" id="account-{ID}-search" placeholder="الحساب / الجهة المستفيدة..." onfocus="showAccountDropdown('account-{ID}')" onblur="hideAccountDropdownDelayed('account-{ID}')" oninput="filterAccountDropdown('account-{ID}')" style="font-size: 1.4rem !important; font-weight: 900 !important; color: var(--amber, #fbbf24); flex: 1; margin-bottom: 0; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                    <button type="button" class="btn-statement-glow" onclick="openLedgerForBlockAccount('{ID}')" title="دفتر الأستاذ العام"><i class="fa fa-book" style="margin-left: 4px;"></i> كشف حساب</button>
                                    <div class="custom-dropdown-list" id="account-{ID}-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; margin-top: 5px; z-index: 9999;"></div>
                                </div>
                            </div>
                            
                            <!-- Hidden Total Amount (للحفاظ على العمليات الحسابية بدون إظهار خانة المبلغ الملغية) -->
                            <input type="hidden" id="amount-{ID}" class="form-input block-total-amount" value="0.00">
                            
                            <!-- Statement (بيان التحويل - تم توسيعه لامتصاص بقية المساحة) -->
                            <div style="flex: 2.2; min-width: 180px; display: flex; flex-direction: column; position: relative;">
                                <input type="text" autocomplete="off" id="statement-{ID}" class="form-input" placeholder="بيان التحويل (اختياري)..." style="font-size: 1rem; flex: 1; margin-bottom: 0; background: var(--surface, rgba(255,255,255,0.05)); color: var(--text-primary, #fff); width: 100%;">
                            </div>

                            <!-- Cost Center / Project / Branch / Vehicle (مركز التكلفة / المشروع) -->
                            <div style="flex: 1.1; min-width: 145px; display: flex; flex-direction: column; position: relative;">
                                <div class="custom-dropdown-input-wrap" style="position: relative; flex: 1; display: flex;">
                                    <input type="hidden" id="cost-center-{ID}-id" class="block-cost-center-id">
                                    <input type="text" autocomplete="off" id="cost-center-{ID}" class="form-input block-cost-center" placeholder="🏷️ مركز تكلفة / مشروع..." onfocus="showCostCenterDropdown('{ID}')" onblur="hideCostCenterDropdownDelayed('{ID}')" oninput="filterCostCenterDropdown('{ID}')" style="font-size: 0.95rem; flex: 1; margin-bottom: 0; background: rgba(255,255,255,0.05); color: #38bdf8; border: 1.4px solid rgba(56, 189, 248, 0.35); text-overflow: ellipsis;" title="ربط الصرف بـ (مركز تكلفة / مشروع / فرع / شاحنة) - اختياري">
                                    <div class="custom-dropdown-list" id="cost-center-{ID}-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; margin-top: 5px; z-index: 9999; max-height: 220px; overflow-y: auto;"></div>
                                </div>
                            </div>
                            
                            <!-- ═══ Unified 5-Icon Base (قاعدة الأيقونات الموحدة) ═══ -->
                            <div class="crystal-unified-dock" style="flex: 2.2; min-width: 320px;">
                                <div style="display: flex; gap: 6px; align-items: center; width: 100%;">
                                    <button type="button" class="btn crystal-btn btn-dock-btn block-btn-ai-scan" onclick="FinancialOCR.openScanner({ target: 'multi', blockId: '{ID}' })" title="مسح وقراءة إشعار التحويل البنكي بالذكاء الاصطناعي">
                                        <span class="btn-icon">📷</span> <span class="btn-text">إشعار AI</span>
                                    </button>
                                    <button type="button" class="btn crystal-btn btn-dock-btn block-btn-ai-whatsapp block-btn-ai-whatsapp-ingest" onclick="handleBlockWhatsAppClick('{ID}')" title="إرسال كمبيالة وإشعار السند لهذا الحساب مباشرة عبر الواتساب (إرسال مباشر ⚡)">
                                        <span class="btn-icon" style="display: inline-flex; align-items: center; justify-content: center; gap: 3px;">
                                            <span style="font-size: 1.15rem; filter: drop-shadow(0 0 4px #25d366);">💬</span>
                                            <span style="font-size: 0.95rem; color: #fef08a; font-weight: 900; line-height: 1;">⚡</span>
                                        </span>
                                        <span class="btn-text">واتساب ⚡</span>
                                    </button>
                                    <button type="button" class="btn crystal-btn btn-dock-btn block-btn-ai-trainer" onclick="FinancialOCR.openTrainer()" title="نماذج الذكاء الاصطناعي">
                                        <span class="btn-icon">🧠</span> <span class="btn-text">نماذج</span>
                                    </button>
                                </div>
                                <div style="display: flex; gap: 6px; align-items: center; width: 100%;">
                                    <button type="button" id="pm-bank-{ID}" class="btn crystal-btn pay-toggle-btn btn-dock-btn block-btn-bank" data-action="toggle-pm" data-block="{ID}" data-method="bank" title="تحويل بنكي">
                                        <span class="btn-icon">🏦</span> <span class="btn-text">تحويل</span>
                                    </button>
                                    <button type="button" id="pm-cheque-{ID}" class="btn crystal-btn pay-toggle-btn btn-dock-btn block-btn-cheque" data-action="toggle-pm" data-block="{ID}" data-method="cheque" title="شيك">
                                        <span class="btn-icon">✍️</span> <span class="btn-text">شيك</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- ═══ Live Arabic Words & Cognitive Ergonomics Bar (التفقيط المالي اللحظي وحماية الأصفار) ═══ -->
                        <div id="tafqeet-row-{ID}" class="block-tafqeet-row" style="display: none; align-items: center; justify-content: space-between; gap: 10px; background: rgba(15, 23, 42, 0.75); border: 1.4px solid rgba(251, 191, 36, 0.35); border-radius: 8px; padding: 6px 14px; margin-bottom: 10px; font-size: 0.95rem; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                            <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0;">
                                <span style="font-size: 1.1rem; flex-shrink: 0;">✍️</span>
                                <span style="color: #94a3b8; font-size: 0.85rem; font-weight: 700; flex-shrink: 0;">فقط بالحروف:</span>
                                <span id="tafqeet-text-{ID}" class="block-tafqeet-text" style="color: #fef08a; font-weight: 800; font-family: 'Tajawal', sans-serif; letter-spacing: 0.3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <div id="tafqeet-alert-{ID}" class="block-fat-finger-alert" style="display: none; align-items: center; gap: 6px; font-size: 0.82rem; font-weight: 800; padding: 2px 10px; border-radius: 6px; flex-shrink: 0;"></div>
                                <span id="wa-dispatch-badge-{ID}" class="block-wa-dispatch-badge" style="display: none; align-items: center; gap: 4px; font-size: 0.8rem; font-weight: 800; background: rgba(37, 211, 102, 0.18); border: 1px solid #25d366; color: #4ade80; padding: 2px 10px; border-radius: 6px; flex-shrink: 0;"></span>
                            </div>
                        </div>

                        <!-- Dynamic Details Sections -->
                        <!-- Bank Details (supports multiple transfers) -->
                        <div id="bank-details-{ID}" class="block-bank-details" style="display: none; border-radius: 12px; padding: 10px; margin-bottom: 8px; animation: rowSlideIn 0.3s ease; backdrop-filter: blur(12px);">
                            <div class="bank-rows-container" id="bank-rows-{ID}">
                                <div class="bank-row" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 6px;">
                                    <div class="bank-icon-label" style="width: 80px; font-weight: bold; display: flex; align-items: center; gap: 5px; flex-shrink: 0; transition: color 0.4s ease;">🏦 بنكي</div>
                                    <div style="width: 150px; flex-shrink: 0;">
                                        <input type="text" autocomplete="off" class="form-input block-bank-amount block-sub-amount" data-block-id="{ID}" placeholder="المبلغ" style="margin-bottom: 0; font-family: 'Consolas', monospace; font-weight: 900; text-align: center; text-shadow: none !important; -webkit-text-stroke: 0px !important;">
                                    </div>
                                    <div style="flex: 1; min-width: 120px;">
                                        <input type="text" autocomplete="off" list="master-banks-datalist" class="form-input block-bank-name-input" placeholder="اسم البنك" style="margin-bottom: 0;">
                                    </div>
                                    <div style="flex: 1; min-width: 120px;">
                                        <input type="text" autocomplete="off" class="form-input block-transfer-no-input" placeholder="رقم التحويلة" style="margin-bottom: 0;">
                                    </div>
                                    <div style="flex: 1.5; min-width: 150px;">
                                        <input type="text" autocomplete="off" class="form-input block-bank-desc-input" placeholder="بيان التحويل" style="margin-bottom: 0;">
                                    </div>
                                    <button type="button" data-action="add-bank-row" data-block="{ID}" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(6, 182, 212, 0.2); border: 3.5px solid #22d3ee; color: #22d3ee; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;" title="إضافة تحويلة أخرى">➕</button>
                                </div>
                            </div>
                        </div>

                        <!-- Cheque Details (supports multiple cheques) -->
                        <div id="cheque-details-{ID}" class="block-cheque-details" style="display: none; border-radius: 12px; padding: 10px; margin-bottom: 8px; animation: rowSlideIn 0.3s ease; backdrop-filter: blur(12px);">
                            <div class="cheque-rows-container" id="cheque-rows-{ID}">
                                <div class="cheque-row" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 6px;">
                                    <div style="width: 80px; font-weight: bold; color: #d8b4fe; display: flex; align-items: center; gap: 5px; flex-shrink: 0;">✍️ شيك</div>
                                    <div style="width: 150px; flex-shrink: 0;">
                                        <input type="text" autocomplete="off" class="form-input block-cheque-amount block-sub-amount" data-block-id="{ID}" placeholder="المبلغ" style="margin-bottom: 0; font-family: 'Consolas', monospace; font-weight: 900; text-align: center; text-shadow: none !important; -webkit-text-stroke: 0px !important;">
                                    </div>
                                    <div style="flex: 1; min-width: 90px;">
                                        <input type="text" autocomplete="off" list="master-banks-datalist" class="form-input block-cheque-bank-input" placeholder="اسم البنك" style="margin-bottom: 0;">
                                    </div>
                                    <div style="flex: 1; min-width: 80px;">
                                        <input type="text" autocomplete="off" class="form-input block-cheque-no-input" placeholder="رقم الشيك" style="margin-bottom: 0;">
                                    </div>
                                    <div style="flex: 1; min-width: 100px;">
                                        <input type="date" class="form-input block-cheque-date-input" title="تاريخ الاستحقاق" style="margin-bottom: 0;">
                                    </div>
                                    <div style="flex: 1.5; min-width: 150px;">
                                        <input type="text" autocomplete="off" class="form-input block-cheque-desc-input" placeholder="بيان الشيك" style="margin-bottom: 0;">
                                    </div>
                                    <button type="button" data-action="add-cheque-row" data-block="{ID}" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(139,92,246,0.2); border: 3.5px solid #c084fc; color: #c084fc; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;" title="إضافة شيك آخر">➕</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

            <!-- Hidden radios to prevent legacy JS checks breaking -->
            <div style="display: none;">
                <input type="radio" name="payment_method" value="cash" id="radio-cash" checked>
                <input type="radio" name="payment_method" value="check" id="radio-check">
                <input type="radio" name="payment_method" value="mobile" id="radio-mobile">
                <input type="radio" name="payment_method" value="hybrid" id="radio-hybrid">
                <!-- Hidden legacy inputs -->
                <input type="text" autocomplete="off" id="mobile-transfer-no">
                <input type="text" autocomplete="off" id="mobile-note">
                <input type="text" autocomplete="off" id="check-no">
                <input type="text" autocomplete="off" id="check-branch">
                <input type="date" id="check-date">
                <input type="date" id="check-due-date">
                <table><tbody id="entries-body"></tbody></table>
            </div>

            <style>
                .footer-actions.unified-footer-bar .btn {
                    height: 42px !important;
                    font-size: 0.92rem !important;
                    padding: 0 18px !important;
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    gap: 6px !important;
                    border-radius: 12px !important;
                    box-sizing: border-box !important;
                    white-space: nowrap !important;
                }
                .footer-actions.unified-footer-bar .btn-save-master {
                    font-size: 0.98rem !important;
                    padding: 0 24px !important;
                    font-weight: 900 !important;
                }
                .footer-actions.unified-footer-bar .btn-whatsapp-icon-only {
                    width: 44px !important;
                    height: 42px !important;
                    min-width: 44px !important;
                    padding: 0 !important;
                }
                
                /* Auto-Send Control Button & Options in Footer */
                .footer-actions.unified-footer-bar #auto-send-control-wrapper {
                    display: inline-flex !important;
                    align-items: center !important;
                    position: relative !important;
                    direction: rtl !important;
                }
                .footer-actions.unified-footer-bar #btn-toggle-auto-send {
                    height: 42px !important;
                    font-size: 0.88rem !important;
                    padding: 0 14px 0 10px !important;
                    border-radius: 0 12px 12px 0 !important;
                    border-left: none !important;
                    font-weight: 800 !important;
                    cursor: pointer !important;
                    white-space: nowrap !important;
                    transition: all 0.25s ease !important;
                }
                .footer-actions.unified-footer-bar #btn-auto-send-options {
                    height: 42px !important;
                    width: 28px !important;
                    min-width: 28px !important;
                    padding: 0 !important;
                    border-radius: 12px 0 0 12px !important;
                    border-right: 1px solid rgba(255, 255, 255, 0.2) !important;
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    cursor: pointer !important;
                    font-size: 0.72rem !important;
                    transition: all 0.25s ease !important;
                }
                .btn-auto-send-toggle.active,
                .btn-auto-send-options.active {
                    background: linear-gradient(135deg, rgba(16, 185, 129, 0.25) 0%, rgba(5, 150, 105, 0.42) 100%) !important;
                    border-color: #10b981 !important;
                    color: #ffffff !important;
                    box-shadow: 0 4px 14px rgba(0,0,0,0.4), 0 0 14px rgba(16, 185, 129, 0.35) !important;
                }
                .btn-auto-send-toggle.active:hover,
                .btn-auto-send-options.active:hover {
                    background: linear-gradient(135deg, rgba(16, 185, 129, 0.35) 0%, rgba(5, 150, 105, 0.55) 100%) !important;
                    box-shadow: 0 6px 18px rgba(0,0,0,0.5), 0 0 20px rgba(16, 185, 129, 0.55) !important;
                    transform: translateY(-2px) !important;
                }
                .btn-auto-send-toggle.inactive,
                .btn-auto-send-options.inactive {
                    background: linear-gradient(135deg, rgba(51, 65, 85, 0.45) 0%, rgba(30, 41, 59, 0.6) 100%) !important;
                    border-color: rgba(148, 163, 184, 0.38) !important;
                    color: #cbd5e1 !important;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.25) !important;
                }
                .btn-auto-send-toggle.inactive:hover,
                .btn-auto-send-options.inactive:hover {
                    background: linear-gradient(135deg, rgba(71, 85, 105, 0.6) 0%, rgba(51, 65, 85, 0.75) 100%) !important;
                    border-color: #94a3b8 !important;
                    color: #ffffff !important;
                    transform: translateY(-2px) !important;
                }
                .auto-send-opt-item:hover {
                    background: rgba(255, 255, 255, 0.08) !important;
                }
                :root.light-theme .btn-auto-send-toggle.active,
                :root.light-theme .btn-auto-send-options.active {
                    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%) !important;
                    border-color: #059669 !important;
                    color: #065f46 !important;
                }
                :root.light-theme .btn-auto-send-toggle.inactive,
                :root.light-theme .btn-auto-send-options.inactive {
                    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%) !important;
                    border-color: #cbd5e1 !important;
                    color: #475569 !important;
                }
                :root.light-theme #auto-send-dropdown-menu {
                    background: #ffffff !important;
                    border-color: #e2e8f0 !important;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
                }
                :root.light-theme .auto-send-opt-item:hover {
                    background: #f1f5f9 !important;
                }
                
                /* Universal Light Theme Overrides for Internal Screens */
                :root.light-theme body,
                :root.light-theme .ambient-bg {
                    background: var(--bg-color, #f8fafc) !important;
                }
                :root.light-theme .glass-panel,
                :root.light-theme .neo-card,
                :root.light-theme .summary-card,
                :root.light-theme .filter-panel,
                :root.light-theme .chart-card,
                :root.light-theme .kpi-card,
                :root.light-theme .premium-header,
                :root.light-theme .top-header,
                :root.light-theme .header-area {
                    background: var(--surface, #ffffff) !important;
                    border-color: var(--border, rgba(0, 0, 0, 0.08)) !important;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
                    color: var(--text-primary, #0f172a) !important;
                }
                :root.light-theme .glass-panel *,
                :root.light-theme .neo-card *,
                :root.light-theme .summary-card * {
                    text-shadow: none !important;
                }
                :root.light-theme table.pro-table th,
                :root.light-theme .pos-grid thead th,
                :root.light-theme .ledger-table th,
                :root.light-theme .report-table th {
                    background: #f1f5f9 !important;
                    color: #0f172a !important;
                    border-bottom: 2px solid #cbd5e1 !important;
                }
                :root.light-theme table.pro-table td,
                :root.light-theme .pos-grid tbody td,
                :root.light-theme .ledger-table td,
                :root.light-theme .report-table td {
                    color: #1e293b !important;
                    border-bottom: 1px solid rgba(0,0,0,0.08) !important;
                }
                :root.light-theme table.pro-table tbody tr:hover td,
                :root.light-theme .pos-grid tbody tr:hover td,
                :root.light-theme .ledger-table tbody tr:hover td,
                :root.light-theme .report-table tbody tr:hover td {
                    background: rgba(0,0,0,0.02) !important;
                }
                :root.light-theme .h-label,
                :root.light-theme .form-label,
                :root.light-theme .tot-lbl,
                :root.light-theme .lbl,
                :root.light-theme label,
                :root.light-theme .table-header {
                    color: #334155 !important;
                }
                :root.light-theme .tot-val,
                :root.light-theme .val {
                    color: #0f172a !important;
                }
                :root.light-theme .h-form-group,
                :root.light-theme .control-panel {
                    background: transparent !important;
                    border-color: rgba(0,0,0,0.08) !important;
                }
                :root.light-theme .modern-btn {
                    color: #1e293b !important;
                }
                :root.light-theme .modern-btn:hover {
                    color: #0f172a !important;
                }
                /* Neon text colors correction for light theme */
                :root.light-theme .text-emerald-400, :root.light-theme span[style*="color: #34d399"], :root.light-theme span[style*="color:#34d399"] { color: #059669 !important; }
                :root.light-theme .text-rose-400, :root.light-theme span[style*="color: #f87171"], :root.light-theme span[style*="color:#f87171"] { color: #e11d48 !important; }
                :root.light-theme .text-sky-400, :root.light-theme span[style*="color: #38bdf8"], :root.light-theme span[style*="color:#38bdf8"] { color: #0284c7 !important; }
                :root.light-theme .text-purple-400, :root.light-theme span[style*="color: #c084fc"], :root.light-theme span[style*="color:#c084fc"] { color: #7c3aed !important; }
                :root.light-theme .text-amber-400, :root.light-theme span[style*="color: #fbbf24"], :root.light-theme span[style*="color:#fbbf24"] { color: #b45309 !important; }
            </style>

            <!-- Action Buttons Section Integrated Into Main Card (Single Unified Row) -->
            <div style="padding: 12px 0 6px 0; border-top: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: center; align-items: center; margin-top: 8px; width: 100%;">
                <div class="footer-actions unified-footer-bar" style="display: flex; gap: 10px; flex-wrap: nowrap; justify-content: center; width: auto; max-width: 100%; align-items: center; padding: 4px 6px;">
                    <button type="button" class="btn crystal-btn btn-new" onclick="resetVoucher()" title="بدء مستند جديد وتصفير الخانات" style="height: 42px; padding: 0 18px; border-radius: 12px; font-weight: 800; font-size: 0.92rem; white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
                        <span>✨</span> <span>مستند جديد</span>
                    </button>
                    <button type="button" class="btn crystal-btn btn-print" onclick="directPrint()" title="طباعة فورية للسند المالي" style="height: 42px; padding: 0 18px; border-radius: 12px; font-weight: 800; font-size: 0.92rem; white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
                        <span>🖨️</span> <span>طباعة</span>
                    </button>
                    <button type="button" class="btn crystal-btn btn-print-preview" onclick="printVoucher()" title="معاينة طباعة السند المالي" style="height: 42px; padding: 0 18px; border-radius: 12px; font-weight: 800; font-size: 0.92rem; white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
                        <span>👁️</span> <span>معاينة</span>
                    </button>
                    
                    <!-- زر الواتساب - يظهر كشعار واتساب رسمي احترافي -->
                    <button type="button" class="btn crystal-btn btn-whatsapp-icon-only" id="btn-whatsapp-master" onclick="openMasterWhatsAppDispatchModal()" title="إرسال كمبيالة وإشعار السند عبر واتساب">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" style="display: block;">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 0C5.373 0 0 5.373 0 12c0 2.115.552 4.103 1.516 5.832L.057 24l6.34-1.662A11.95 11.95 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z" fill="#25D366"/>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" fill="#ffffff"/>
                        </svg>
                    </button>

                    <button type="button" class="btn crystal-btn btn-delete" onclick="confirmDelete()" title="حذف هذا المستند" style="height: 42px; padding: 0 16px; border-radius: 12px; font-weight: 800; font-size: 0.92rem; white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
                        <span>🗑️</span> <span>حذف المستند</span>
                    </button>

                    <!-- ═══ زر التحكم في الإرسال التلقائي مع الحفظ ═══ -->
                    <div id="auto-send-control-wrapper">
                        <button type="button" class="btn crystal-btn btn-auto-send-toggle inactive" id="btn-toggle-auto-send" onclick="window.toggleAutoSendMode()" title="التحكم في الإرسال التلقائي: انقر للتبديل بين الحفظ مع الإرسال، أو الحفظ فقط بدون إرسال">
                            <span id="auto-send-icon" style="font-size: 1.1rem;">💾</span>
                            <span id="auto-send-label" style="font-weight: 800;">إرسال تلقائي:</span>
                            <span id="auto-send-status-pill" style="padding: 2px 8px; border-radius: 7px; font-size: 0.76rem; font-weight: 900;">معطّل (حفظ فقط)</span>
                        </button>
                        <button type="button" class="btn crystal-btn btn-auto-send-options inactive" id="btn-auto-send-options" onclick="window.toggleAutoSendDropdown(event)" title="خيارات وجهة الإرسال التلقائي (المستلم / الصارف / كلاهما)">
                            ▼
                        </button>
                        
                        <!-- القائمة المنسدلة للخيارات السريعة -->
                        <div id="auto-send-dropdown-menu" style="display: none; position: absolute; bottom: 50px; left: 0; background: #0f172a; border: 1.5px solid #334155; border-radius: 12px; padding: 6px; min-width: 260px; box-shadow: 0 12px 30px rgba(0,0,0,0.7), 0 0 20px rgba(0,0,0,0.5); z-index: 9999; direction: rtl; text-align: right; backdrop-filter: blur(12px);">
                            <div style="font-size: 0.78rem; color: #94a3b8; font-weight: 800; padding: 5px 10px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
                                <span>⚙️ وضع الحفظ والإرسال:</span>
                            </div>
                            <div class="auto-send-opt-item" onclick="window.selectAutoSendOption(false)" style="padding: 7px 10px; border-radius: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #cbd5e1; display: flex; align-items: center; gap: 8px; margin-bottom: 3px; transition: background 0.2s;">
                                <span>💾</span> <span>حفظ فقط (بدون إرسال)</span>
                            </div>
                            <div class="auto-send-opt-item" onclick="window.selectAutoSendOption(true, 'both')" style="padding: 7px 10px; border-radius: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #34d399; display: flex; align-items: center; gap: 8px; margin-bottom: 3px; transition: background 0.2s;">
                                <span>⚡👥</span> <span>إرسال تلقائي للطرفين (المستلم + الصارف)</span>
                            </div>
                            <div class="auto-send-opt-item" onclick="window.selectAutoSendOption(true, 'debtor')" style="padding: 7px 10px; border-radius: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #60a5fa; display: flex; align-items: center; gap: 8px; margin-bottom: 3px; transition: background 0.2s;">
                                <span>📥</span> <span>إرسال تلقائي للطرف المستلم فقط</span>
                            </div>
                            <div class="auto-send-opt-item" onclick="window.selectAutoSendOption(true, 'creditor')" style="padding: 7px 10px; border-radius: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #f472b6; display: flex; align-items: center; gap: 8px; transition: background 0.2s;">
                                <span>📤</span> <span>إرسال تلقائي للطرف الصارف فقط</span>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn crystal-btn btn-save-master" id="btn-save" onclick="saveVoucher()" title="حفظ واعتماد وترحيل السند المالي" style="height: 42px; padding: 0 22px; border-radius: 12px; font-weight: 900; font-size: 0.98rem; white-space: nowrap; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 18px rgba(16, 185, 129, 0.45);">
                        <span>✅</span> <span>حفظ واعتماد السند</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- ═══ WhatsApp Enterprise Voucher Dispatch Modals (نوافذ إرسال الواتساب الذكي) ═══ -->
    <style>
        /* أنماط السحب وتغيير الحجم والتحكم بالنافذة */
        .wa-modal-resizable {
            resize: both;
            overflow: hidden;
            min-width: 380px;
            min-height: 400px;
            max-width: 96vw;
            max-height: 92vh;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            box-shadow: 0 16px 45px rgba(0,0,0,0.7), 0 0 25px rgba(37, 211, 102, 0.25);
        }
        @media (max-width: 768px) {
            #wa-voucher-modal-card {
                width: 96vw !important;
                padding: 12px 10px !important;
            }
            .wa-dual-grid {
                grid-template-columns: 1fr !important;
            }
        }
        .wa-modal-header-draggable {
            cursor: grab;
            cursor: -webkit-grab;
            user-select: none;
            -webkit-user-select: none;
        }
        .wa-modal-header-draggable:active {
            cursor: grabbing;
            cursor: -webkit-grabbing;
        }
        .wa-modal-scroll-area {
            overflow-y: auto;
            overflow-x: hidden;
            flex: 1 1 auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(37, 211, 102, 0.45) rgba(0,0,0,0.25);
        }
        .wa-modal-scroll-area::-webkit-scrollbar {
            width: 6px;
        }
        .wa-modal-scroll-area::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.25);
            border-radius: 4px;
        }
        .wa-modal-scroll-area::-webkit-scrollbar-thumb {
            background: rgba(37, 211, 102, 0.45);
            border-radius: 4px;
        }
        .wa-modal-scroll-area::-webkit-scrollbar-thumb:hover {
            background: rgba(37, 211, 102, 0.75);
        }
        .wa-resize-handle-corner {
            position: absolute;
            bottom: 2px;
            left: 2px;
            width: 14px;
            height: 14px;
            cursor: sw-resize;
            opacity: 0.45;
            pointer-events: none;
            color: #25d366;
            font-size: 11px;
            line-height: 1;
            display: flex;
            align-items: flex-end;
            justify-content: flex-start;
        }
        .wa-btn-header-ctrl {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #cbd5e1;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            font-size: 0.95rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .wa-btn-header-ctrl:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            transform: scale(1.05);
        }
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 16px rgba(239, 68, 68, 0.4); }
            50% { transform: scale(1.015); box-shadow: 0 0 26px rgba(239, 68, 68, 0.85); }
            100% { transform: scale(1); box-shadow: 0 0 16px rgba(239, 68, 68, 0.4); }
        }

        /* ═══════════════════════════════════════════════════════════════════════════════
           🌟 شعار الواتساب الفخم والمتحرك (أيقونة متوهجة بموجات ضوئية وإيماءات حركية)
           ═══════════════════════════════════════════════════════════════════════════════ */
        .wa-luxury-badge {
            position: relative;
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            cursor: pointer;
            user-select: none;
        }

        .wa-ripple-container {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        .wa-wave-pulse {
            position: absolute;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid #25d366;
            animation: waWaveRipple 2.6s infinite cubic-bezier(0, 0.2, 0.8, 1);
            pointer-events: none;
        }

        .wa-wave-pulse.wave-1 {
            animation-delay: 0s;
        }
        .wa-wave-pulse.wave-2 {
            animation-delay: 0.85s;
        }
        .wa-wave-pulse.wave-3 {
            animation-delay: 1.7s;
        }

        .wa-luxury-icon-circle {
            position: relative;
            z-index: 2;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
            border: 2px solid #a7f3d0;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 14px rgba(37, 211, 102, 0.65), inset 0 1px 2px rgba(255, 255, 255, 0.7);
            animation: waSphereGlow 2.5s infinite ease-in-out, waMicroMotion 3.5s infinite ease-in-out;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .wa-luxury-icon-circle svg {
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.35));
            transition: transform 0.25s ease;
        }

        .wa-luxury-badge:hover .wa-luxury-icon-circle {
            transform: scale(1.14) rotate(8deg);
            box-shadow: 0 0 24px rgba(37, 211, 102, 1), 0 0 35px rgba(37, 211, 102, 0.6), inset 0 1px 4px #ffffff;
            border-color: #ffffff;
        }

        .wa-luxury-badge:hover .wa-luxury-icon-circle svg {
            transform: scale(1.1);
        }

        @keyframes waWaveRipple {
            0% {
                transform: scale(0.85);
                opacity: 0.95;
                border-color: rgba(37, 211, 102, 0.9);
                box-shadow: 0 0 10px rgba(37, 211, 102, 0.7);
            }
            50% {
                opacity: 0.5;
                border-color: rgba(52, 211, 153, 0.6);
            }
            100% {
                transform: scale(1.75);
                opacity: 0;
                border-color: rgba(16, 185, 129, 0);
                box-shadow: 0 0 18px rgba(37, 211, 102, 0);
            }
        }

        @keyframes waSphereGlow {
            0%, 100% {
                box-shadow: 0 0 12px rgba(37, 211, 102, 0.6), inset 0 1px 2px rgba(255, 255, 255, 0.65);
                filter: drop-shadow(0 0 5px rgba(37, 211, 102, 0.5));
            }
            50% {
                box-shadow: 0 0 22px rgba(37, 211, 102, 0.95), inset 0 1px 4px rgba(255, 255, 255, 0.9);
                filter: drop-shadow(0 0 12px rgba(37, 211, 102, 0.9));
            }
        }

        @keyframes waMicroMotion {
            0%, 100% {
                transform: scale(1) rotate(0deg);
            }
            25% {
                transform: scale(1.05) rotate(-3deg);
            }
            50% {
                transform: scale(0.97) rotate(2deg);
            }
            75% {
                transform: scale(1.04) rotate(-1deg);
            }
        }

        /* ═══════════════════════════════════════════════════════════════════════════════
           💱 شريحة تبديل واختيار العملة الموحدة داخل كروت من وإلى (Unified Currency Chip)
           ═══════════════════════════════════════════════════════════════════════════════ */
        .wa-currency-chip {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: #0f172a;
            color: #fef08a;
            border: 1.5px solid #fbbf24;
            border-radius: 6px;
            font-size: 1.05rem;
            font-weight: 900;
            padding: 2px 6px 2px 20px;
            cursor: pointer;
            text-align: center;
            outline: none;
            box-shadow: 0 2px 6px rgba(0,0,0,0.5), inset 0 1px 2px rgba(255,255,255,0.15);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23fbbf24' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: left 4px center;
            background-size: 10px;
            transition: all 0.2s ease;
            height: 32px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            vertical-align: middle;
            font-family: 'Consolas', monospace;
            box-sizing: border-box;
        }

        .wa-currency-chip:hover, .wa-currency-chip:focus {
            border-color: #f59e0b;
            background-color: #1e293b;
            box-shadow: 0 0 10px rgba(251, 191, 36, 0.6);
            transform: translateY(-1px);
        }
    </style>

    <!-- 1. Single & Dual Recipient Dispatch Modal with Debtor/Creditor/Both Switcher (Grand 2-Column Master Layout) -->
    <div id="wa-voucher-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); backdrop-filter: blur(10px); z-index: 99999; align-items: center; justify-content: center;">
        <div id="wa-voucher-modal-card" class="modal-card glass-container wa-modal-resizable" style="width: 96vw; max-width: 1450px; height: 95vh; max-height: 96vh; border: 2.5px solid #25d366; padding: 12px 18px; border-radius: 14px; background: #0b1120; position: relative; box-shadow: 0 20px 60px rgba(0,0,0,0.9), 0 0 35px rgba(37,211,102,0.35); display: flex; flex-direction: column; box-sizing: border-box; overflow: hidden;">
            
            <!-- Header (Draggable + Window Controls) -->
            <div id="wa-voucher-modal-header" class="wa-modal-header-draggable" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid rgba(37, 211, 102, 0.4); padding-bottom: 8px; margin-bottom: 8px; flex-shrink: 0;" title="اضغط مع السحب لتحريك النافذة يميناً ويساراً">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.3rem; color: #94a3b8; cursor: grab;" title="اضغط مع السحب لتحريك النافذة">⠿</span>
                    <span style="font-size: 1.5rem;">📱</span>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <h3 style="margin: 0; color: #25d366; font-size: 1.30rem; font-weight: 900; line-height: 1.2; text-shadow: 0 0 10px rgba(37,211,102,0.35);">إرسال واعتماد إشعار السند المالي عبر واتساب</h3>
                        <span id="wa-modal-subtitle" style="background: rgba(251, 191, 36, 0.16); border: 1.5px solid rgba(251, 191, 36, 0.6); color: #fef08a; font-size: 0.86rem; font-weight: 800; padding: 3px 12px; border-radius: 6px;">📜 ورقة كمبيالة رسمية مؤطرة (تشمل رصيد دفتر الأستاذ العام)</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <!-- زر التكبير / الاستعادة -->
                    <button type="button" id="btn-wa-modal-maximize" class="wa-btn-header-ctrl" onclick="toggleWAModalMaximize()" title="تكبير / استعادة الحجم" style="width: 32px; height: 32px; font-size: 1.05rem;">🗖</button>
                    <!-- زر الإغلاق -->
                    <button type="button" onclick="closeWAVoucherModal()" class="wa-btn-header-ctrl" style="width: 32px; height: 32px; font-size: 1.05rem; background: rgba(239, 68, 68, 0.25); border-color: #ef4444; color: #ef4444;" title="إغلاق">✕</button>
                </div>
            </div>

            <!-- ═══ شبكة التقسيم الرئيسية (زيادة القسم اليمين 50% وإنقاص القسم اليسار 50%) ═══ -->
            <div id="wa-voucher-modal-body" style="display: grid; grid-template-columns: minmax(0, 1.8fr) minmax(0, 1fr); gap: 14px; flex: 1; min-height: 0; overflow: hidden;">
                <input type="hidden" id="wa-modal-block-id">
                <input type="hidden" id="wa-modal-target-mode" value="both">
                <input type="hidden" id="wa-modal-format-type" value="promissory_note">
                <input type="hidden" id="wa-both-active-tab" value="debtor">

                <!-- ════════ العمود الأيمن الموسع (لوحة أطراف السند والمعلومات وقنوات الإرسال) ════════ -->
                <div style="display: flex; flex-direction: column; justify-content: space-between; gap: 7px; overflow-y: auto; padding-right: 2px;" class="wa-modal-scroll-area">
                    
                    <!-- 1. شريط وجهة العملية والأطراف واختيار العملة الموحدة -->
                    <div style="background: #0f172a; border: 1.5px solid rgba(37,211,102,0.4); border-radius: 9px; padding: 7px 12px; display: flex; flex-direction: column; gap: 6px; box-shadow: 0 3px 10px rgba(0,0,0,0.4);">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 0.92rem; font-weight: 900; color: #fbbf24; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <span style="display: flex; align-items: center; gap: 6px;"><span>🎯</span> <span>وجهة إشعار السند:</span></span>
                                <div style="display: flex; align-items: center; gap: 6px; background: rgba(0,0,0,0.45); padding: 2px 8px; border-radius: 6px; border: 1.5px solid #fbbf24;">
                                    <span style="font-size: 1.05rem;">💱</span>
                                    <span style="font-size: 0.86rem; color: #fbbf24; font-weight: 900; white-space: nowrap;">العملة الموحدة:</span>
                                    <select id="wa-modal-currency-select" onchange="window.setWAModalCurrency(this.value)" style="background: #1e293b; border: 1.5px solid #fbbf24; color: #fde047; font-weight: 900; font-size: 0.88rem; padding: 2px 6px; border-radius: 5px; cursor: pointer; outline: none;">
                                        <option value="SDG">جنيه سوداني (SDG)</option>
                                        <option value="USD">دولار أمريكي (USD)</option>
                                        <option value="SAR">ريال سعودي (SAR)</option>
                                        <option value="AED">درهم إماراتي (AED)</option>
                                        <option value="EGP">جنيه مصري (EGP)</option>
                                    </select>
                                </div>
                            </div>
                            <span id="wa-target-status-badge" style="font-size: 0.84rem; font-weight: 900; padding: 3px 12px; border-radius: 6px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.3) 0%, rgba(239, 68, 68, 0.3) 100%); color: #fef08a; border: 1.5px solid rgba(251, 191, 36, 0.6);">
                                محدد: إرسال لكليهما معاً (إلى + من) 👥
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; justify-content: space-between;">
                            <!-- زر الطرف المدين (المستلم) -->
                            <button type="button" id="btn-wa-target-debtor" class="wa-target-btn" onclick="setWATargetMode('debtor')" style="flex: 1; height: 42px; padding: 4px 10px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; background: #022c22; border: 2px solid #10b981; color: #a7f3d0; font-size: 1.08rem; font-weight: 900; white-space: nowrap;">
                                <span style="font-size: 1.25rem;">📥</span> <span id="wa-debtor-role-title">إلى</span>: <b id="wa-debtor-acc-name" style="color: #fef08a; font-size: 1.15rem; font-weight: 900;">-</b>
                            </button>
                            <!-- سهم التبديل الأوسط -->
                            <button type="button" id="btn-wa-swap-parties" onclick="swapWATargetParties()" title="تبديل الإرسال بين الأطراف" style="background: #1e293b; border: 2px solid #fbbf24; color: #fbbf24; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; cursor: pointer; flex-shrink: 0; transition: all 0.25s; box-shadow: 0 2px 6px rgba(0,0,0,0.5);">
                                ⇄
                            </button>
                            <!-- زر الطرف الدائن (الصارف) -->
                            <button type="button" id="btn-wa-target-creditor" class="wa-target-btn" onclick="setWATargetMode('creditor')" style="flex: 1; height: 42px; padding: 4px 10px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; background: #2a080c; border: 2px solid #ef4444; color: #fecaca; font-size: 1.08rem; font-weight: 900; white-space: nowrap;">
                                <span style="font-size: 1.25rem;">📤</span> <span id="wa-creditor-role-title">من</span>: <b id="wa-creditor-acc-name" style="color: #fef08a; font-size: 1.15rem; font-weight: 900;">-</b>
                            </button>
                            <!-- زر كليهما معاً (مصغر وأنيق) -->
                            <button type="button" id="btn-wa-target-both" class="wa-target-btn active" onclick="setWATargetMode('both')" style="flex: 0 0 auto; height: 42px; padding: 4px 14px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; background: linear-gradient(135deg, #047857 0%, #1d4ed8 50%, #b91c1c 100%); border: 2px solid #fbbf24; color: #ffffff; box-shadow: 0 0 12px rgba(251, 191, 36, 0.45); font-size: 1.02rem; font-weight: 900; white-space: nowrap;" title="إرسال لكليهما معاً">
                                <span style="font-size: 1.15rem;">👥</span> <span>كليهما معاً ⚡</span>
                            </button>
                        </div>
                    </div>

                    <!-- 2. بطاقات الأطراف في وضع كليهما معاً (تصميم تنفيذي عالي التباين والوضوح) -->
                    <div id="wa-both-parties-view" style="display: flex; flex-direction: column; gap: 8px;">
                        <!-- بطاقة الطرف المدين (المستلم) -->
                        <div id="wa-both-debtor-card" style="background: #061f18; border: 2px solid #10b981; border-right: 8px solid #10b981; border-radius: 12px; padding: 10px 14px; display: flex; flex-direction: column; gap: 8px; transition: all 0.25s ease; box-shadow: 0 4px 14px rgba(0,0,0,0.5); box-sizing: border-box;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                                <div style="display: flex; align-items: center; gap: 8px; min-width: 0; flex: 1;">
                                    <span style="font-size: 1.35rem; flex-shrink: 0;">📥</span>
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; min-width: 0;">
                                        <span style="background: rgba(16, 185, 129, 0.25); border: 1.5px solid #10b981; color: #86efac; font-size: 1.15rem; font-weight: 900; padding: 3px 10px; border-radius: 6px; white-space: nowrap; min-width: 40px; text-align: center; display: inline-block; box-sizing: border-box;" id="wa-both-debtor-role-label">إلى</span>:
                                        <b id="wa-both-debtor-name" style="color: #ffffff; font-weight: 900; font-size: 1.32rem; letter-spacing: 0.3px; line-height: 1.3; text-shadow: 0 1px 3px rgba(0,0,0,0.8);">-</b>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                    <span id="wa-debtor-status-tag" style="background: #1e293b; border: 1.5px solid #475569; color: #cbd5e1; font-size: 0.98rem; font-weight: 800; padding: 4px 12px; border-radius: 6px; white-space: nowrap; min-width: 110px; text-align: center; display: inline-block; box-sizing: border-box;">⏳ بانتظار الإرسال</span>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; justify-content: space-between;">
                                <div id="wa-both-debtor-balance" style="background: #022c22; border: 2px solid #10b981; padding: 4px 10px; border-radius: 8px; white-space: nowrap; flex: 0 0 auto; width: auto; height: 44px; box-sizing: border-box; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.45);">
                                    <span style="color: #6ee7b7; font-weight: 900; font-size: 1.05rem; white-space: nowrap;">📊 رصيد المستلم:</span>
                                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                                        <b style="color: #ffffff; background: #064e3b; padding: 3px 8px; border-radius: 6px; border: 1.5px solid #10b981; font-family: 'Consolas', monospace; font-size: 1.25rem; font-weight: 900; letter-spacing: 0.5px;">-</b>
                                        <select class="wa-currency-chip" onchange="window.setWAModalCurrency(this.value)" title="تغيير عملة السند والأرصدة الموحدة">
                                            <option value="SDG">SDG</option>
                                            <option value="USD">USD</option>
                                            <option value="SAR">SAR</option>
                                            <option value="AED">AED</option>
                                            <option value="EGP">EGP</option>
                                        </select>
                                        <span style="background: #10b981; color: #ffffff; border: 1.5px solid #34d399; padding: 3px 8px; border-radius: 6px; font-size: 1.02rem; font-weight: 900; min-width: 32px; text-align: center;">-</span>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0;">
                                    <div class="wa-luxury-badge" title="رقم الواتساب المعتمد للإرسال">
                                        <div class="wa-ripple-container">
                                            <span class="wa-wave-pulse wave-1"></span>
                                            <span class="wa-wave-pulse wave-2"></span>
                                            <span class="wa-wave-pulse wave-3"></span>
                                        </div>
                                        <div class="wa-luxury-icon-circle">
                                            <svg viewBox="0 0 24 24" width="22" height="22" fill="#ffffff">
                                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <input type="tel" id="wa-both-debtor-phone" class="form-input" oninput="if(typeof cachePartyPhone==='function') cachePartyPhone('debtor', this.value)" placeholder="أدخل رقم الواتساب للمستلم هنا..." style="flex: 1; min-width: 0; width: 100%; height: 44px; background: #021a14; border: 2px solid #10b981; color: #ffffff; font-weight: 900; direction: ltr; text-align: left; font-size: 1.28rem; padding: 2px 14px; border-radius: 8px; box-sizing: border-box; transition: all 0.25s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.6);">
                                </div>
                                <button type="button" class="btn crystal-btn" onclick="sendSinglePartyDirectly('debtor')" style="height: 44px; min-width: 105px; flex-shrink: 0; background: linear-gradient(135deg, #059669 0%, #047857 100%); border: 2px solid #34d399; color: #ffffff; font-weight: 900; font-size: 1.10rem; padding: 4px 16px; border-radius: 8px; cursor: pointer; white-space: nowrap; box-shadow: 0 3px 10px rgba(16,185,129,0.45); display: inline-flex; align-items: center; justify-content: center; gap: 6px; box-sizing: border-box;" title="إرسال إشعار هذا الطرف فقط">
                                    <span>⚡ إرسال</span>
                                </button>
                            </div>
                        </div>

                        <!-- بطاقة الطرف الدائن (الصارف) -->
                        <div id="wa-both-creditor-card" style="background: #200a0f; border: 2px solid #ef4444; border-right: 8px solid #ef4444; border-radius: 12px; padding: 10px 14px; display: flex; flex-direction: column; gap: 8px; transition: all 0.25s ease; box-shadow: 0 4px 14px rgba(0,0,0,0.5); box-sizing: border-box;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                                <div style="display: flex; align-items: center; gap: 8px; min-width: 0; flex: 1;">
                                    <span style="font-size: 1.35rem; flex-shrink: 0;">📤</span>
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; min-width: 0;">
                                        <span style="background: rgba(239, 68, 68, 0.25); border: 1.5px solid #ef4444; color: #fca5a5; font-size: 1.15rem; font-weight: 900; padding: 3px 10px; border-radius: 6px; white-space: nowrap; min-width: 40px; text-align: center; display: inline-block; box-sizing: border-box;" id="wa-both-creditor-role-label">من</span>:
                                        <b id="wa-both-creditor-name" style="color: #ffffff; font-weight: 900; font-size: 1.32rem; letter-spacing: 0.3px; line-height: 1.3; text-shadow: 0 1px 3px rgba(0,0,0,0.8);">-</b>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                    <span id="wa-creditor-status-tag" style="background: #1e293b; border: 1.5px solid #475569; color: #cbd5e1; font-size: 0.98rem; font-weight: 800; padding: 4px 12px; border-radius: 6px; white-space: nowrap; min-width: 110px; text-align: center; display: inline-block; box-sizing: border-box;">⏳ بانتظار الإرسال</span>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; justify-content: space-between;">
                                <div id="wa-both-creditor-balance" style="background: #2a080c; border: 2px solid #ef4444; padding: 4px 10px; border-radius: 8px; white-space: nowrap; flex: 0 0 auto; width: auto; height: 44px; box-sizing: border-box; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.45);">
                                    <span style="color: #fca5a5; font-weight: 900; font-size: 1.05rem; white-space: nowrap;">📊 رصيد الصرف:</span>
                                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                                        <b style="color: #ffffff; background: #7f1d1d; padding: 3px 8px; border-radius: 6px; border: 1.5px solid #ef4444; font-family: 'Consolas', monospace; font-size: 1.25rem; font-weight: 900; letter-spacing: 0.5px;">-</b>
                                        <select class="wa-currency-chip" onchange="window.setWAModalCurrency(this.value)" title="تغيير عملة السند والأرصدة الموحدة">
                                            <option value="SDG">SDG</option>
                                            <option value="USD">USD</option>
                                            <option value="SAR">SAR</option>
                                            <option value="AED">AED</option>
                                            <option value="EGP">EGP</option>
                                        </select>
                                        <span style="background: #ef4444; color: #ffffff; border: 1.5px solid #f87171; padding: 3px 8px; border-radius: 6px; font-size: 1.02rem; font-weight: 900; min-width: 32px; text-align: center;">-</span>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0;">
                                    <div class="wa-luxury-badge" title="رقم الواتساب المعتمد للإرسال">
                                        <div class="wa-ripple-container">
                                            <span class="wa-wave-pulse wave-1"></span>
                                            <span class="wa-wave-pulse wave-2"></span>
                                            <span class="wa-wave-pulse wave-3"></span>
                                        </div>
                                        <div class="wa-luxury-icon-circle">
                                            <svg viewBox="0 0 24 24" width="22" height="22" fill="#ffffff">
                                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <input type="tel" id="wa-both-creditor-phone" class="form-input" oninput="if(typeof cachePartyPhone==='function') cachePartyPhone('creditor', this.value)" placeholder="أدخل رقم الواتساب لحساب الصرف هنا..." style="flex: 1; min-width: 0; width: 100%; height: 44px; background: #1f070b; border: 2px solid #ef4444; color: #ffffff; font-weight: 900; direction: ltr; text-align: left; font-size: 1.28rem; padding: 2px 14px; border-radius: 8px; box-sizing: border-box; transition: all 0.25s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.6);">
                                </div>
                                <button type="button" class="btn crystal-btn" onclick="sendSinglePartyDirectly('creditor')" style="height: 44px; min-width: 105px; flex-shrink: 0; background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); border: 2px solid #f87171; color: #ffffff; font-weight: 900; font-size: 1.10rem; padding: 4px 16px; border-radius: 8px; cursor: pointer; white-space: nowrap; box-shadow: 0 3px 10px rgba(239,68,68,0.45); display: inline-flex; align-items: center; justify-content: center; gap: 6px; box-sizing: border-box;" title="إرسال إشعار هذا الطرف فقط">
                                    <span>⚡ إرسال</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 3. بطاقة الطرف الفردي في وضع التحديد المفرد -->
                    <div id="wa-single-party-view" style="display: none; flex-direction: column; gap: 8px; background: #0f172a; border: 1.5px solid rgba(255,255,255,0.2); border-radius: 10px; padding: 10px 14px; box-shadow: 0 4px 14px rgba(0,0,0,0.4);">
                        <div>
                            <label id="wa-label-recipient-name" style="color: #34d399; font-size: 1.15rem; font-weight: 900; display: block; margin-bottom: 5px;">👤 الطرف المحدد:</label>
                            <input type="text" id="wa-modal-recipient-name" class="form-input" readonly style="background: #1e293b; color: #ffffff; font-weight: 900; font-size: 1.30rem; padding: 3px 12px; height: 44px; border-radius: 8px; border: 1.5px solid #475569;">
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <div id="wa-modal-balance-container" style="flex: 0 0 auto; width: auto; display: inline-flex; align-items: center; justify-content: space-between; gap: 8px; background: #082f49; border: 2px solid #38bdf8; border-radius: 8px; padding: 4px 12px; height: 44px; box-sizing: border-box;">
                                <span style="color: #bae6fd; font-size: 1.05rem; font-weight: 900; white-space: nowrap;">📊 رصيد الأستاذ:</span>
                                <div style="display: inline-flex; align-items: center; gap: 6px;">
                                    <span id="wa-modal-live-balance" style="color: #ffffff; background: #0369a1; padding: 3px 8px; border-radius: 6px; font-family: 'Consolas', monospace; font-size: 1.25rem; font-weight: 900;">0.00</span>
                                    <select id="wa-modal-single-currency-chip" class="wa-currency-chip" onchange="window.setWAModalCurrency(this.value)" title="تغيير عملة السند والأرصدة الموحدة">
                                        <option value="SDG">SDG</option>
                                        <option value="USD">USD</option>
                                        <option value="SAR">SAR</option>
                                        <option value="AED">AED</option>
                                        <option value="EGP">EGP</option>
                                    </select>
                                    <span id="wa-modal-live-balance-tag" style="font-size: 1.02rem; font-weight: 900; padding: 3px 8px; border-radius: 6px; background: #38bdf8; color: #082f49;">-</span>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0;">
                                <div class="wa-luxury-badge" title="رقم الواتساب المعتمد للإرسال">
                                    <div class="wa-ripple-container">
                                        <span class="wa-wave-pulse wave-1"></span>
                                        <span class="wa-wave-pulse wave-2"></span>
                                        <span class="wa-wave-pulse wave-3"></span>
                                    </div>
                                    <div class="wa-luxury-icon-circle">
                                        <svg viewBox="0 0 24 24" width="22" height="22" fill="#ffffff">
                                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                        </svg>
                                    </div>
                                </div>
                                <input type="tel" id="wa-modal-phone" class="form-input" oninput="if(typeof cachePartyPhone==='function') cachePartyPhone(document.getElementById('wa-modal-target-mode')?.value||'debtor', this.value)" placeholder="أدخل رقم الواتساب هنا..." style="background: #021a14; border: 2px solid #10b981; color: #ffffff; font-weight: 900; direction: ltr; text-align: left; font-size: 1.28rem; padding: 2px 14px; height: 44px; border-radius: 8px; flex: 1; min-width: 0; width: 100%; box-sizing: border-box;">
                            </div>
                        </div>
                    </div>

                    <!-- 4. زر الإرسال الرئيسي الشامل لكلا الطرفين (المستلم + الصارف) -->
                    <button type="button" id="btn-wa-send-both-action" onclick="executeWASend('gateway')" class="btn crystal-btn" style="width: 100%; height: 48px; padding: 4px 16px; background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%); border: 2px solid #34d399; border-radius: 10px; color: #ffffff; font-weight: 900; font-size: 1.22rem; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 4px 16px rgba(16, 185, 129, 0.45); cursor: pointer; transition: all 0.2s ease;" title="إرسال كمبيالة السند مباشرة عبر خادم الواتساب المربوط">
                        <span style="font-size: 1.45rem;">🚀</span>
                        <span id="btn-wa-send-both-action-text">إرسال إلى كل الأطراف عبر الواتساب ⚡</span>
                    </button>

                    <!-- بانر التسليم الذكي للمرحلة الثانية (Handover Panel) -->
                    <div id="wa-modal-handover-panel" style="display: none; background: #0f172a; border: 2px solid #10b981; border-radius: 9px; padding: 6px 12px; box-shadow: 0 0 16px rgba(16, 185, 129, 0.4); animation: fadeIn 0.2s ease;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                            <span style="color: #86efac; font-weight: 900; font-size: 0.90rem; display: flex; align-items: center; gap: 6px;">
                                <span>✅</span> <span id="wa-handover-status-text">تم إرسال إشعار المستلم بنجاح!</span>
                            </span>
                            <span id="wa-handover-subtext" style="color: #cbd5e1; font-weight: 700; font-size: 0.82rem; width: 100%;">اضغط الزر لإرسال إشعار الصارف فوراً:</span>
                            <span id="wa-handover-countdown-badge" style="display: none;"><b id="wa-handover-timer"></b></span>
                            <div style="display: flex; gap: 8px; align-items: center; width: 100%; margin-top: 3px;">
                                <button type="button" id="btn-wa-handover-next" onclick="triggerHandoverNextParty()" style="flex: 1; height: 36px; padding: 4px 12px; background: linear-gradient(135deg, #10b981, #059669); border: 1.5px solid #34d399; border-radius: 7px; color: #fff; font-size: 0.88rem; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; white-space: nowrap;">
                                    <span>📤</span> <span id="btn-wa-handover-label">إرسال إشعار الطرف الصارف الآن ➔</span>
                                </button>
                                <button type="button" onclick="closeWAVoucherModal()" style="height: 36px; padding: 4px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 7px; color: #cbd5e1; font-size: 0.82rem; font-weight: 800; cursor: pointer;">
                                    إغلاق
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 5. شريط أدوات تصوير ونسخ صورة السند الفوري (Image & Capture Tools) -->
                    <div style="background: #0f172a; border: 1.5px dashed #fbbf24; border-radius: 9px; padding: 5px 14px; display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                        <span style="color: #fbbf24; font-size: 0.90rem; font-weight: 900; display: flex; align-items: center; gap: 6px;">
                            <span style="font-size: 1.15rem;">📸</span> <span>تصوير وحفظ السند:</span>
                        </span>
                        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                            <button type="button" onclick="downloadWAVoucherImage()" class="btn crystal-btn" style="height: 34px; background: #78350f; border: 1.5px solid #fbbf24; color: #fef08a; font-size: 0.86rem; font-weight: 900; padding: 4px 14px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 6px;" title="تنزيل كارت السند المصور عالي الدقة كصورة PNG">
                                <span>💾</span> حفظ كارت السند (PNG)
                            </button>
                            <button type="button" onclick="copyWAVoucherImageToClipboard()" class="btn crystal-btn" style="height: 34px; background: #075985; border: 1.5px solid #38bdf8; color: #e0f2fe; font-size: 0.86rem; font-weight: 900; padding: 4px 14px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 6px;" title="نسخ صورة السند للحافظة للصقها مباشرة في واتساب">
                                <span>📋</span> نسخ الصورة للحافظة
                            </button>
                        </div>
                    </div>

                    <!-- 6. قناة الإرسال الموحدة عبر خادم الواتساب المربوط -->
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button type="button" id="btn-wa-send-gateway" class="btn crystal-btn" onclick="executeWASend('gateway')" style="width: 100%; height: 48px; background: linear-gradient(135deg, #15803d 0%, #047857 100%); border: 2px solid #4ade80; color: #ffffff; font-weight: 900; font-size: 1.18rem; padding: 4px 16px; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 16px rgba(21,128,61,0.45); cursor: pointer;" title="إرسال كمبيالة السند كملف PDF معتمد وموثق عبر الواتساب">
                            <span style="font-size: 1.45rem;">📄⚡</span> <span id="btn-wa-send-gateway-text">إرسال كمبيالة السند المعتمدة (ملف PDF) عبر الواتساب</span>
                        </button>
                    </div>

                    <!-- 7. شريط الإعدادات والإلغاء السفلي -->
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; border-top: 1.5px solid rgba(255,255,255,0.1); padding-top: 6px;">
                        <button type="button" class="btn crystal-btn btn-ghost" onclick="closeWAVoucherModal()" style="font-size: 0.85rem; font-weight: 800; padding: 4px 14px;">إلغاء</button>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #86efac; font-size: 0.84rem; font-weight: 900; margin: 0; background: rgba(37,211,102,0.1); border: 1.5px dashed rgba(37,211,102,0.4); padding: 3px 10px; border-radius: 6px;">
                            <input type="checkbox" id="wa-modal-auto-dispatch-sync" onchange="toggleWAAutoDispatch(this.checked)" style="width: 15px; height: 15px; cursor: pointer; accent-color: #25d366;">
                            <span>⚡ إرسال تلقائي دائم فور حفظ السند</span>
                        </label>
                    </div>

                    <div id="wa-modal-status-alert" style="display: none; padding: 5px 8px; border-radius: 6px; font-size: 0.84rem; font-weight: 800;"></div>
                </div>

                <!-- ════════ العمود الأيسر (لوحة كمبيالة السند المعتمدة الكاملة - Full Promissory Note Document) ════════ -->
                <div style="display: flex; flex-direction: column; background: #070d18; border: 1.5px solid rgba(251, 191, 36, 0.4); border-radius: 10px; padding: 8px 10px; overflow: hidden; box-shadow: inset 0 0 30px rgba(0,0,0,0.65);">
                    
                    <!-- ترويسة لوحة الكمبيالة والتبويبات المدمجة -->
                    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid rgba(255,255,255,0.12); padding-bottom: 5px; margin-bottom: 5px; flex-shrink: 0; flex-wrap: wrap; gap: 5px;">
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span style="color: #fbbf24; font-size: 0.86rem; font-weight: 900; display: flex; align-items: center; gap: 5px;">
                                <span>📜</span> <span>المعاينة المعتمدة:</span>
                            </span>
                        </div>
                        
                        <div style="display: flex; gap: 4px; align-items: center; flex-wrap: wrap;">
                            <!-- تبويب المستلم والصارف في وضع كليهما معاً -->
                            <div id="wa-both-tabs-toolbar" style="display: flex; gap: 4px;">
                                <button type="button" id="btn-wa-both-tab-debtor" onclick="switchWABothPreviewTab('debtor')" style="background: rgba(16, 185, 129, 0.35); border: 1.5px solid #10b981; color: #34d399; font-size: 0.78rem; font-weight: 900; padding: 2px 8px; border-radius: 5px; cursor: pointer;">
                                    📥 المستلم
                                </button>
                                <button type="button" id="btn-wa-both-tab-creditor" onclick="switchWABothPreviewTab('creditor')" style="background: rgba(255, 255, 255, 0.08); border: 1.5px solid rgba(255, 255, 255, 0.25); color: #cbd5e1; font-size: 0.78rem; font-weight: 900; padding: 2px 8px; border-radius: 5px; cursor: pointer;">
                                    📤 الصارف
                                </button>
                            </div>
                            <!-- زر تحميل PDF الفوري -->
                            <button type="button" onclick="downloadPromissoryPDF()" class="btn crystal-btn" style="background: linear-gradient(135deg, #b45309, #78350f); border: 1px solid #fbbf24; color: #fef08a; font-size: 0.76rem; font-weight: 900; padding: 2px 8px; border-radius: 5px; cursor: pointer;" title="تنزيل كمبيالة السند كملف PDF معتمد">
                                📥 PDF
                            </button>
                            <!-- زر الطباعة المباشرة -->
                            <button type="button" onclick="printPromissoryNote()" class="btn crystal-btn" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.25); color: #f1f5f9; font-size: 0.76rem; font-weight: 800; padding: 2px 7px; border-radius: 5px; cursor: pointer;" title="طباعة كمبيالة السند">
                                🖨️ طباعة
                            </button>
                            <!-- زر نسخ النص بالكامل -->
                            <button type="button" onclick="copyFullTextMessage()" class="btn crystal-btn" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.25); color: #f1f5f9; font-size: 0.76rem; font-weight: 800; padding: 2px 7px; border-radius: 5px; cursor: pointer;" title="نسخ نص الكمبيالة بالكامل إلى الحافظة">
                                📋 نسخ
                            </button>
                            <!-- زر التبديل بين كمبيالة PDF والنص -->
                            <button type="button" id="btn-wa-toggle-preview-mode" onclick="toggleWAPreviewMode()" class="btn crystal-btn" style="background: rgba(56, 189, 248, 0.15); border: 1.5px solid rgba(56, 189, 248, 0.5); color: #38bdf8; font-size: 0.76rem; font-weight: 900; padding: 2px 7px; border-radius: 5px; cursor: pointer;" title="تبديل العرض بين كمبيالة PDF والنص">
                                📄 PDF / 📝 نص
                            </button>
                        </div>
                    </div>

                    <!-- مساحة عرض كمبيالة السند الكاملة (Certified PDF Document & Monospace Area) -->
                    <div style="flex: 1; min-height: 0; display: flex; flex-direction: column; position: relative;">
                        <!-- حاوية المعاينة الحية لكمبيالة السند الرسمية المعتمدة (PDF Document Live Preview) -->
                        <div id="wa-promissory-preview-wrapper" style="flex: 1; min-height: 0; display: flex; flex-direction: column; overflow-y: auto; overflow-x: hidden; background: #0c1424; border-radius: 8px; border: 1.5px solid rgba(251, 191, 36, 0.35); padding: 8px; box-sizing: border-box;">
                            <div id="wa-promissory-note-container" style="background: #ffffff; color: #0f172a; border-radius: 6px; box-shadow: 0 4px 20px rgba(0,0,0,0.5); font-family: 'Tajawal', sans-serif; direction: rtl; width: 100%;">
                            </div>
                        </div>

                        <!-- صندوق نص كمبيالة كليهما معاً (Hidden by default, available when user toggles text mode) -->
                        <div id="wa-preview-both-box" style="display: none; flex: 1; min-height: 0; flex-direction: column;">
                            <textarea id="wa-both-preview-message" class="form-input" oninput="saveWABothPreviewEdit(this.value)" style="width: 100%; height: 100%; flex: 1; background: #050b14; border: 1.5px solid rgba(251, 191, 36, 0.35); color: #f8fafc; font-size: 0.84rem; line-height: 1.55; font-weight: 600; border-radius: 7px; resize: none; padding: 8px 10px; font-family: 'Consolas', 'Courier New', monospace; box-sizing: border-box; box-shadow: inset 0 0 25px rgba(0,0,0,0.85); white-space: pre-wrap; word-break: break-word;"></textarea>
                        </div>

                        <!-- صندوق نص كمبيالة الطرف الفردي -->
                        <div id="wa-preview-single-box" style="display: none; flex: 1; min-height: 0; flex-direction: column;">
                            <textarea id="wa-modal-message" class="form-input" style="width: 100%; height: 100%; flex: 1; background: #050b14; border: 1.5px solid rgba(251, 191, 36, 0.35); color: #f8fafc; font-size: 0.84rem; line-height: 1.55; font-weight: 600; border-radius: 7px; resize: none; padding: 8px 10px; font-family: 'Consolas', 'Courier New', monospace; box-sizing: border-box; box-shadow: inset 0 0 25px rgba(0,0,0,0.85); white-space: pre-wrap; word-break: break-word;"></textarea>
                        </div>

                        <!-- حاوية كارت السند المصور اللحظي (Graphic Preview Container) -->
                        <div id="wa-modal-image-preview-container" style="display: none; flex: 1; min-height: 0; align-items: center; justify-content: center; background: rgba(0,0,0,0.65); border: 1.5px solid rgba(251,191,36,0.35); border-radius: 7px; overflow: hidden; padding: 6px; position: relative;">
                            <img id="wa-modal-image-preview" src="" alt="كارت السند المصور" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 6px; box-shadow: 0 10px 30px rgba(0,0,0,0.85);">
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 4px; padding-top: 4px; border-top: 1px solid rgba(255,255,255,0.08); font-size: 0.74rem; font-weight: 800; color: #94a3b8;">
                        <span>💡 ورقة كمبيالة معتمدة ومقيدة</span>
                        <span style="color: #fbbf24;">⚡ قيد آلي رقمي</span>
                    </div>
                </div>

            </div>

            <!-- Visual corner resize indicator -->
            <div class="wa-resize-handle-corner" title="اسحب لتغيير الحجم">◢</div>
        </div>
    </div>

    <!-- 2. Batch Recipients Modal (إرسال مجمع آمن يحفظ الخصوصية للأطراف) -->
    <div id="wa-batch-voucher-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.78); backdrop-filter: blur(8px); z-index: 99999; align-items: center; justify-content: center;">
        <div id="wa-batch-voucher-modal-card" class="modal-card glass-container wa-modal-resizable" style="max-width: 650px; width: 92%; max-height: 88vh; border: 2px solid #25d366; padding: 14px 18px; border-radius: 14px; background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.96)); position: relative; display: flex; flex-direction: column;">
            <div id="wa-batch-voucher-modal-header" class="wa-modal-header-draggable" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(37, 211, 102, 0.3); padding-bottom: 8px; margin-bottom: 10px; flex-shrink: 0;" title="اضغط مع السحب لتحريك النافذة">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.1rem; color: #94a3b8; cursor: grab;">⠿</span>
                    <span style="font-size: 1.3rem;">📱</span>
                    <div>
                        <h3 style="margin: 0; color: #25d366; font-size: 1.1rem; font-weight: 800; line-height: 1.2;">إرسال إشعارات السند المجمع عبر واتساب</h3>
                        <span style="font-size: 0.76rem; color: #94a3b8;">إرسال آمن ومستقل لكل طرف حفاظاً على السرية المصرفية 🛡️</span>
                    </div>
                </div>
                <button type="button" onclick="closeWABatchModal()" class="wa-btn-header-ctrl" style="background: rgba(239, 68, 68, 0.15); border-color: #ef4444; color: #ef4444;" title="إغلاق">✕</button>
            </div>

            <div class="wa-modal-scroll-area" style="display: flex; flex-direction: column; gap: 10px; padding-right: 2px;">
                <p style="color: #fef08a; font-size: 0.82rem; margin: 0; background: rgba(245, 158, 11, 0.12); padding: 6px 10px; border-radius: 6px; border: 1px solid rgba(245, 158, 11, 0.3);">
                    ℹ️ سيتم إرسال إشعار رسمي منفصل ومخصص لكل طرف برقم تحويلته ومبلغه فقط، دون كشف بيانات الأطراف الأخرى.
                </p>

                <!-- Quick Filter & Swap Toolbar -->
                <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap; background: rgba(0,0,0,0.25); padding: 5px 8px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.08);">
                    <button type="button" onclick="filterWABatchTargets('all')" class="btn-batch-filter active" id="btn-batch-filter-all" style="background: rgba(37,211,102,0.2); border: 1.5px solid #25d366; color: #86efac; border-radius: 6px; padding: 4px 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">👥 تحديد الكل</button>
                    <button type="button" onclick="filterWABatchTargets('blocks')" class="btn-batch-filter" id="btn-batch-filter-blocks" style="background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.35); color: #86efac; border-radius: 6px; padding: 4px 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">📥 إلى حساب (المستلمين)</button>
                    <button type="button" onclick="filterWABatchTargets('main')" class="btn-batch-filter" id="btn-batch-filter-main" style="background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.35); color: #fca5a5; border-radius: 6px; padding: 4px 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">🏛️ الصرف من حساب</button>
                    <button type="button" onclick="toggleWABatchChecks()" style="background: transparent; border: 1px dashed rgba(251,191,36,0.5); color: #fbbf24; border-radius: 6px; padding: 4px 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer; margin-right: auto;">⇄ عكس التحديد</button>
                </div>

                <!-- Recipients List -->
                <div id="wa-batch-recipients-list" style="max-height: 220px; overflow-y: auto; display: flex; flex-direction: column; gap: 6px; padding-right: 2px;">
                    <!-- Rows generated dynamically -->
                </div>

                <!-- Progress info -->
                <div id="wa-batch-progress-bar" style="display: none; width: 100%; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden; margin-top: 4px;">
                    <div id="wa-batch-progress-fill" style="width: 0%; height: 100%; background: #25d366; transition: width 0.3s ease;"></div>
                </div>
                <div id="wa-batch-status-text" style="font-size: 0.8rem; font-weight: bold; color: #38bdf8; text-align: center; display: none;"></div>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 8px; flex-shrink: 0; flex-wrap: wrap;">
                <button type="button" class="btn crystal-btn btn-ghost" onclick="closeWABatchModal()" style="font-size: 0.85rem; padding: 6px 14px;">إغلاق</button>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <button type="button" id="btn-wa-batch-send" class="btn crystal-btn" onclick="executeBatchWASend()" style="background: linear-gradient(135deg, #25d366, #128c7e); color: #fff; font-weight: 800; font-size: 0.88rem; padding: 6px 14px; display: flex; align-items: center; gap: 6px;">
                        <span>🚀</span> بدء الإرسال للأطراف المحددة
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Context Menu -->
    <div class="context-menu" id="column-context-menu">
        <div style="color: #fbbf24; font-size: 0.8rem; margin-bottom: 5px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 5px;">إظهار / إخفاء الأعمدة</div>
        <label class="context-menu-item"><input type="checkbox" value="cost-center" onchange="toggleColumn('cost-center', this.checked)"> مركز التكلفة</label>
        <label class="context-menu-item"><input type="checkbox" value="note" onchange="toggleColumn('note', this.checked)"> ملحوظة</label>
        <label class="context-menu-item"><input type="checkbox" value="check-no" onchange="toggleColumn('check-no', this.checked)"> رقم الشيك</label>
        <label class="context-menu-item"><input type="checkbox" value="check-date" onchange="toggleColumn('check-date', this.checked)"> تاريخ الشيك</label>
        <label class="context-menu-item"><input type="checkbox" value="check-due" onchange="toggleColumn('check-due', this.checked)"> الاستحقاق</label>
    </div>

    <!-- Print Preview Modal -->
    <div id="print-preview-modal" class="preview-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 2000; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); justify-content: center; align-items: center; padding: 20px; box-sizing: border-box;">
        <div class="preview-modal-content" style="background: #ffffff; width: 100%; max-width: 900px; height: 90vh; border-radius: 16px; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden; animation: zoomIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);">
            <!-- Modal Header -->
            <div style="padding: 15px 25px; background: #0f172a; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 10px; color: #38bdf8;">
                    <span style="font-size: 1.4rem;">👁️</span>
                    <h3 style="margin: 0; font-size: 1.15rem; font-weight: bold; font-family: 'Tajawal', sans-serif;">معاينة المستند قبل الطباعة</h3>
                </div>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <button onclick="window.print()" style="background: #0ea5e9; border: none; border-radius: 8px; color: #fff; padding: 8px 16px; font-weight: bold; cursor: pointer; font-family: 'Tajawal', sans-serif; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(14,165,233,0.3); transition: transform 0.2s;">🖨️ طباعة</button>
                    <button onclick="closePrintPreviewModal()" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #94a3b8; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.1rem; transition: all 0.2s;">✖</button>
                </div>
            </div>
            <!-- Scrollable Preview Area -->
            <div id="preview-modal-body" style="flex: 1; overflow-y: auto; padding: 30px; background: #f8fafc; direction: rtl;">
                <!-- Content will be injected here -->
            </div>
        </div>
    </div>
    
    <style>
        @keyframes zoomIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        @media print {
            body > *:not(#print-preview-modal) {
                display: none !important;
            }
            #print-preview-modal {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                height: auto !important;
                background: #fff !important;
                padding: 0 !important;
                display: flex !important;
            }
            .preview-modal-content {
                box-shadow: none !important;
                height: auto !important;
                max-width: 100% !important;
                border-radius: 0 !important;
            }
            #preview-modal-body {
                background: #fff !important;
                padding: 0 !important;
            }
            /* Hide modal header with print/close buttons when printing */
            #print-preview-modal > div > div:first-child {
                display: none !important;
            }
        }
    </style>

    <!-- Edit Warning Modal -->
    <div id="edit-warning-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(15px); z-index: 999999; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
        <div style="background: linear-gradient(135deg, #7f1d1d, #991b1b); border: 2px solid #f87171; border-radius: 20px; padding: 40px; width: 90%; max-width: 600px; text-align: center; box-shadow: 0 25px 50px rgba(0,0,0,0.5), 0 0 40px rgba(239,68,68,0.3); transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
            <div style="font-size: 5rem; margin-bottom: 20px; animation: pulse-glow 2s infinite;">⚠️</div>
            <h2 style="color: #ffffff; font-size: 2.2rem; font-weight: 900; margin: 0 0 15px 0; font-family: 'Tajawal', sans-serif; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">تنبيه: هل تريد التعديل؟</h2>
            <p style="color: #fecaca; font-size: 1.2rem; line-height: 1.6; margin-bottom: 35px; font-weight: 500;">أنت تقوم الآن بتعديل سند قديم تم حفظه مسبقاً.<br>حفظ التعديلات سيؤدي إلى تغيير البيانات المالية بأثر رجعي.</p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <button onclick="confirmEditOldVoucher()" style="background: #ef4444; color: white; font-size: 1.3rem; font-weight: 900; padding: 15px 40px; border: none; border-radius: 12px; cursor: pointer; box-shadow: 0 4px 15px rgba(239,68,68,0.4); transition: transform 0.2s;">نعم، اعتمد التعديل</button>
                <button onclick="cancelEditOldVoucher()" style="background: rgba(255,255,255,0.1); color: #ffffff; font-size: 1.3rem; font-weight: 900; padding: 15px 30px; border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; cursor: pointer;">تراجع ❌</button>
            </div>
        </div>
    </div>



    <!-- Scripts -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/html2pdf.bundle.min.js"></script>
    <script defer src="js/whatsapp-integration.js?v=<?= file_exists('js/whatsapp-integration.js') ? filemtime('js/whatsapp-integration.js') : '1' ?>"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>
    <script defer src="js/promissory-flight-animation.js?v=<?= time() ?>"></script>
    <script defer src="js/master-banks-helper.js?v=<?= file_exists('js/master-banks-helper.js') ? filemtime('js/master-banks-helper.js') : '1' ?>"></script>
    <script defer src="js/multi-cash-payment.js?v=<?= time() ?>"></script>
    <script>
    let hasWarnedAboutEdit = false;

    document.addEventListener('DOMContentLoaded', () => {
        // Intercept save by wrapping the original function unconditionally
        if (typeof window.saveVoucher === 'function') {
            const originalSave = window.saveVoucher;
            window.saveVoucher = function(...args) {
                let editIndex = -1;
                try {
                    if (typeof currentVoucherIndex !== 'undefined') {
                        editIndex = currentVoucherIndex;
                    }
                } catch(e) {}
                
                const isEditing = (editIndex !== -1) || (new URLSearchParams(window.location.search).get('id') != null);
                
                if (isEditing && !hasWarnedAboutEdit) {
                    showEditWarningModal();
                } else {
                    hasWarnedAboutEdit = false;
                    return originalSave.apply(this, args);
                }
            };
        }
    });

    function showEditWarningModal(e) {
        if(e && e.target && typeof e.target.blur === 'function') {
            e.target.blur();
        }
        const modal = document.getElementById('edit-warning-modal');
        if(modal) {
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.style.opacity = '1';
                modal.firstElementChild.style.transform = 'scale(1)';
            }, 10);
        }
    }

    function confirmEditOldVoucher() {
        hasWarnedAboutEdit = true;
        const modal = document.getElementById('edit-warning-modal');
        if(modal) {
            modal.style.opacity = '0';
            modal.firstElementChild.style.transform = 'scale(0.9)';
            setTimeout(() => {
                modal.style.display = 'none';
                // Trigger the save action now that warning is accepted
                if (typeof window.saveVoucher === 'function') {
                    window.saveVoucher();
                }
            }, 300);
        }
    }

    function cancelEditOldVoucher() {
        if(document.referrer) {
            window.history.back();
        } else {
            window.location.href = 'dashboard-pro.php';
        }
    }
    </script>
    <!-- Custom Number Properties Menu (Enhanced Luxury) -->
    <div id="custom-num-menu">
        <div class="props-header">
            <span>⚙️</span> خصائص: <span id="custom-num-target-name" style="color:#fbbf24; font-weight:800; margin-right:4px;">سعر الدولار</span>
        </div>
        
        <!-- 1. حجم الخط -->
        <div class="props-row">
            <span class="props-row-label">📏 حجم الخط</span>
            <div style="display: flex; align-items: center; gap: 6px;">
                <button type="button" class="prop-action-btn" onclick="changeNumProp('font', -1); event.stopPropagation();">−</button>
                <span id="num-prop-font-size-val" style="color: #38bdf8; width: 34px; text-align: center; font-weight: bold; font-size: 1rem;">0</span>
                <button type="button" class="prop-action-btn" onclick="changeNumProp('font', 1); event.stopPropagation();">+</button>
            </div>
        </div>

        <!-- 2. سمك الخط (Font Weight) -->
        <div class="props-row">
            <span class="props-row-label">💪 سمك الخط</span>
            <div style="display: flex; gap: 4px;" id="weight-btn-group">
                <button type="button" class="weight-btn" data-weight="400" onclick="setNumPropWeight('400'); event.stopPropagation();">عادي</button>
                <button type="button" class="weight-btn" data-weight="600" onclick="setNumPropWeight('600'); event.stopPropagation();">متوسط</button>
                <button type="button" class="weight-btn" data-weight="700" onclick="setNumPropWeight('700'); event.stopPropagation();">عريض</button>
                <button type="button" class="weight-btn" data-weight="900" onclick="setNumPropWeight('900'); event.stopPropagation();">ضخم</button>
            </div>
        </div>

        <!-- 3. لون الرقم (Color) -->
        <div class="props-row">
            <span class="props-row-label">🎨 لون الرقم</span>
            <div style="display: flex; align-items: center; gap: 6px;">
                <span class="color-dot" data-color="#10b981" onclick="setNumPropColor('#10b981'); event.stopPropagation();" style="background:#10b981;" title="أخضر زمردي"></span>
                <span class="color-dot" data-color="#fbbf24" onclick="setNumPropColor('#fbbf24'); event.stopPropagation();" style="background:#fbbf24;" title="أصفر ذهبي"></span>
                <span class="color-dot" data-color="#38bdf8" onclick="setNumPropColor('#38bdf8'); event.stopPropagation();" style="background:#38bdf8;" title="سماوي"></span>
                <span class="color-dot" data-color="#ef4444" onclick="setNumPropColor('#ef4444'); event.stopPropagation();" style="background:#ef4444;" title="أحمر"></span>
                <span class="color-dot" data-color="#c084fc" onclick="setNumPropColor('#c084fc'); event.stopPropagation();" style="background:#c084fc;" title="بنفسجي"></span>
                <span class="color-dot" data-color="#ffffff" onclick="setNumPropColor('#ffffff'); event.stopPropagation();" style="background:#ffffff; border:1px solid #aaa;" title="أبيض"></span>
                <input type="color" id="num-prop-color-input" onchange="setNumPropColor(this.value); event.stopPropagation();" oninput="setNumPropColor(this.value); event.stopPropagation();" style="width: 26px; height: 26px; border: none; cursor: pointer; background: transparent; padding: 0; border-radius: 4px;" title="منتقي ألوان مخصص">
            </div>
        </div>

        <!-- 4. الفاصلة الألفية (Thousands Separator) -->
        <div class="props-row">
            <span class="props-row-label">🔢 الفاصلة الألفية (،)</span>
            <span id="num-prop-sep-status" onclick="toggleNumPropSep(); event.stopPropagation();" style="color: #10b981; font-weight: bold; cursor: pointer; background: rgba(16,185,129,0.15); padding: 4px 14px; border-radius: 6px; border: 1px solid #10b981; font-size: 0.9rem;">مفعل</span>
        </div>

        <!-- 5. الخانات العشرية (Decimals) -->
        <div class="props-row">
            <span class="props-row-label">✨ الخانات العشرية</span>
            <div style="display: flex; align-items: center; gap: 6px;">
                <button type="button" class="prop-action-btn" onclick="changeNumProp('dec', -1); event.stopPropagation();">−</button>
                <span id="num-prop-dec-val" style="color: #fbbf24; width: 30px; text-align: center; font-weight: bold; font-size: 1.1rem;">0</span>
                <button type="button" class="prop-action-btn" onclick="changeNumProp('dec', 1); event.stopPropagation();">+</button>
                <button type="button" onclick="setNumPropDecimals(0); event.stopPropagation();" style="background:#1e293b; border:1px solid #334155; color:#94a3b8; border-radius:4px; padding:3px 6px; cursor:pointer; font-size:0.75rem;">.0</button>
                <button type="button" onclick="setNumPropDecimals(2); event.stopPropagation();" style="background:#1e293b; border:1px solid #334155; color:#94a3b8; border-radius:4px; padding:3px 6px; cursor:pointer; font-size:0.75rem;">.00</button>
            </div>
        </div>

        <!-- 6. نوع الخط (Font Family) -->
        <div class="props-row" style="margin-bottom: 16px;">
            <span class="props-row-label">🔤 نوع الخط</span>
            <div onclick="toggleNumPropFontFamily(); event.stopPropagation();" style="cursor: pointer; background: rgba(255,255,255,0.08); padding: 5px 14px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.18);">
                <span id="num-prop-font-val" style="color: #fbbf24; font-weight: bold; font-size: 0.95rem;">monospace</span>
            </div>
        </div>

        <!-- 7. زر الحفظ -->
        <button type="button" id="save-num-props-btn" onclick="saveNumProps(); event.stopPropagation();" style="width: 100%; background: linear-gradient(135deg, #10b981, #059669); border: none; color: white; padding: 11px; border-radius: 8px; font-weight: 800; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 15px rgba(16,185,129,0.35); transition: transform 0.15s, box-shadow 0.15s;">
            حفظ وتطبيق الخصائص 💾
        </button>
    </div>

    <script>
    let dbNumProps = <?php echo $db_num_props_json ?: '{}'; ?>;
    window.DB_OCR_PROPS = <?php echo $db_ocr_props_json ?: '{}'; ?>;
    let allNumProps = (dbNumProps && Object.keys(dbNumProps).length > 0) ? dbNumProps : (JSON.parse(localStorage.getItem('all-num-props')) || {});
    // Sanitize any white color on amount-local-hdr or amount-foreign (never allow white on white)
    if (!allNumProps['amount-local-hdr'] || !allNumProps['amount-local-hdr'].color || allNumProps['amount-local-hdr'].color.toLowerCase() === '#ffffff') {
        if (!allNumProps['amount-local-hdr']) allNumProps['amount-local-hdr'] = { fontSizeMod: 0, thousandsSep: true, decimals: 2, fontFamily: 'monospace', fontWeight: '900' };
        allNumProps['amount-local-hdr'].color = '#ef4444';
    }
    if (!allNumProps['amount-foreign'] || !allNumProps['amount-foreign'].color || allNumProps['amount-foreign'].color.toLowerCase() === '#ffffff') {
        if (!allNumProps['amount-foreign']) allNumProps['amount-foreign'] = { fontSizeMod: 0, thousandsSep: true, decimals: 2, fontFamily: 'monospace', fontWeight: '900' };
        allNumProps['amount-foreign'].color = '#ef4444';
    }
    try { localStorage.setItem('all-num-props', JSON.stringify(allNumProps)); } catch(e) {}
    let currentPropId = 'exchange-rate';

    const targetNames = {
        'exchange-rate': 'سعر الدولار / التعادل',
        'amount-local-hdr': 'الإجمالي بالمحلي (SDG)',
        'amount-foreign': 'المعادل بالأجنبي (USD)',
        'block-bank-amount': 'مبالغ البنك',
        'block-cheque-amount': 'مبالغ الشيكات',
        'block-total-amount': 'مبالغ الحسابات الإجمالية',
        'block-sub-amount': 'مبالغ الحسابات الفرعية',
        'default': 'الرقم المالي'
    };

    function getCanonicalPropId(elOrId) {
        if (!elOrId) return 'default';
        let el = (typeof elOrId === 'object' && elOrId !== null) ? elOrId : document.getElementById(elOrId);
        let strId = (typeof elOrId === 'string') ? elOrId : (el ? (el.id || '') : '');

        if (strId === 'exchange-rate') return 'exchange-rate';
        if (strId === 'amount-local-hdr') return 'amount-local-hdr';
        if (strId === 'amount-foreign') return 'amount-foreign';

        if (el && el.classList) {
            if (el.classList.contains('block-bank-amount') || el.classList.contains('transfer-amount')) return 'block-bank-amount';
            if (el.classList.contains('block-cheque-amount') || el.classList.contains('cheque-amount')) return 'block-cheque-amount';
            if (el.classList.contains('block-sub-amount')) return 'block-sub-amount';
            if (el.classList.contains('block-total-amount')) return 'block-total-amount';
        }

        if (strId) {
            if (strId.startsWith('amount-') || strId.startsWith('total-')) return 'block-total-amount';
            if (strId.includes('bank')) return 'block-bank-amount';
            if (strId.includes('cheque')) return 'block-cheque-amount';
            if (strId.includes('sub')) return 'block-sub-amount';
        }

        return strId || 'default';
    }

    function getActiveProp() {
        currentPropId = getCanonicalPropId(currentPropId);
        if(!allNumProps[currentPropId]) {
            if (currentPropId === 'exchange-rate') {
                allNumProps[currentPropId] = {
                    fontSizeMod: 0,
                    thousandsSep: true,
                    decimals: 0,
                    fontFamily: 'monospace',
                    fontWeight: '900',
                    color: '#10b981'
                };
            } else if (currentPropId === 'amount-local-hdr' || currentPropId === 'amount-foreign') {
                allNumProps[currentPropId] = {
                    fontSizeMod: 0,
                    thousandsSep: true,
                    decimals: 2,
                    fontFamily: 'monospace',
                    fontWeight: '900',
                    color: '#ef4444'
                };
            } else {
                allNumProps[currentPropId] = {
                    fontSizeMod: 0,
                    thousandsSep: true,
                    decimals: 2,
                    fontFamily: 'monospace',
                    fontWeight: '900',
                    color: '#fbbf24'
                };
            }
        }
        const p = allNumProps[currentPropId];
        if (p.fontWeight === undefined) p.fontWeight = '900';
        if (currentPropId === 'amount-local-hdr' || currentPropId === 'amount-foreign') {
            if (!p.color || p.color.toLowerCase() === '#ffffff') p.color = '#ef4444';
        } else if (p.color === undefined) {
            p.color = (currentPropId === 'exchange-rate') ? '#10b981' : '#fbbf24';
        }
        if (p.fontSizeMod === undefined) p.fontSizeMod = 0;
        if (p.thousandsSep === undefined) p.thousandsSep = true;
        if (p.decimals === undefined) p.decimals = (currentPropId === 'exchange-rate') ? 0 : 2;
        if (p.fontFamily === undefined) p.fontFamily = 'monospace';
        return p;
    }

    function getPropFor(el) {
        let id = getCanonicalPropId(el);
        if(!allNumProps[id]) {
            if (id === 'exchange-rate') {
                allNumProps[id] = {
                    fontSizeMod: 0,
                    thousandsSep: true,
                    decimals: 0,
                    fontFamily: 'monospace',
                    fontWeight: '900',
                    color: '#10b981'
                };
            } else if (id === 'amount-local-hdr' || id === 'amount-foreign') {
                allNumProps[id] = {
                    fontSizeMod: 0,
                    thousandsSep: true,
                    decimals: 2,
                    fontFamily: 'monospace',
                    fontWeight: '900',
                    color: '#ef4444'
                };
            } else {
                allNumProps[id] = {
                    fontSizeMod: 0,
                    thousandsSep: true,
                    decimals: 2,
                    fontFamily: 'monospace',
                    fontWeight: '900',
                    color: '#fbbf24'
                };
            }
        }
        const p = allNumProps[id];
        if (p.fontWeight === undefined) p.fontWeight = '900';
        if (id === 'amount-local-hdr' || id === 'amount-foreign') {
            if (!p.color || p.color.toLowerCase() === '#ffffff') p.color = '#ef4444';
        } else if (p.color === undefined) {
            p.color = (id === 'exchange-rate') ? '#10b981' : '#fbbf24';
        }
        if (p.fontSizeMod === undefined) p.fontSizeMod = 0;
        if (p.thousandsSep === undefined) p.thousandsSep = true;
        if (p.decimals === undefined) p.decimals = (id === 'exchange-rate') ? 0 : 2;
        if (p.fontFamily === undefined) p.fontFamily = 'monospace';
        return p;
    }

    const fontFamilies = ['monospace', 'Inter', 'Tajawal', 'Cairo', 'Arial', 'Tahoma'];

    function updateMenuUI() {
        const conf = getActiveProp();
        const targetTitle = document.getElementById('custom-num-target-name');
        if (targetTitle) targetTitle.textContent = targetNames[currentPropId] || 'سعر الدولار / التعادل';

        // 1. Font size mod
        const fontSizeVal = document.getElementById('num-prop-font-size-val');
        if (fontSizeVal) fontSizeVal.textContent = (conf.fontSizeMod > 0 ? '+' : '') + conf.fontSizeMod;

        // 2. Font weight
        document.querySelectorAll('#weight-btn-group .weight-btn').forEach(btn => {
            if (btn.getAttribute('data-weight') === String(conf.fontWeight)) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // 3. Color
        document.querySelectorAll('#custom-num-menu .color-dot').forEach(dot => {
            if (dot.getAttribute('data-color').toLowerCase() === (conf.color || '').toLowerCase()) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
        const colorInput = document.getElementById('num-prop-color-input');
        if (colorInput && conf.color) colorInput.value = conf.color;

        // 4. Thousands sep
        const sepStatus = document.getElementById('num-prop-sep-status');
        if (sepStatus) {
            sepStatus.textContent = conf.thousandsSep ? 'مفعل (،)' : 'معطل';
            sepStatus.style.color = conf.thousandsSep ? '#10b981' : '#ef4444';
            sepStatus.style.borderColor = conf.thousandsSep ? '#10b981' : '#ef4444';
            sepStatus.style.background = conf.thousandsSep ? 'rgba(16,185,129,0.15)' : 'rgba(239,68,68,0.15)';
        }

        // 5. Decimals
        const decVal = document.getElementById('num-prop-dec-val');
        if (decVal) decVal.textContent = conf.decimals;

        // 6. Font family
        const fontVal = document.getElementById('num-prop-font-val');
        if (fontVal) fontVal.textContent = conf.fontFamily;
    }

    function setNumPropWeight(weight) {
        const conf = getActiveProp();
        conf.fontWeight = weight;
        updateMenuUI();
        applyNumPropsToAll();
    }

    function setNumPropColor(color) {
        const conf = getActiveProp();
        conf.color = color;
        updateMenuUI();
        applyNumPropsToAll();
    }

    function setNumPropDecimals(dec) {
        const conf = getActiveProp();
        conf.decimals = dec;
        updateMenuUI();
        applyNumPropsToAll();
    }

    function changeNumProp(type, val) {
        const conf = getActiveProp();
        if(type === 'font') {
            conf.fontSizeMod += val;
            applyNumPropsToAll();
            updateMenuUI();
        } else if(type === 'dec') {
            conf.decimals += val;
            if(conf.decimals < 0) conf.decimals = 0;
            if(conf.decimals > 6) conf.decimals = 6;
            updateMenuUI();
            applyNumPropsToAll();
        }
    }

    function toggleNumPropSep() {
        const conf = getActiveProp();
        conf.thousandsSep = !conf.thousandsSep;
        updateMenuUI();
        applyNumPropsToAll();
    }

    function toggleNumPropFontFamily() {
        const conf = getActiveProp();
        let idx = fontFamilies.indexOf(conf.fontFamily);
        idx = (idx + 1) % fontFamilies.length;
        conf.fontFamily = fontFamilies[idx];
        updateMenuUI();
        applyNumPropsToAll();
    }

    function saveNumProps() {
        const cleanedProps = {};
        for (let k in allNumProps) {
            if (!k.startsWith('amount-1') && !k.startsWith('amount-2')) {
                cleanedProps[k] = allNumProps[k];
            }
        }
        allNumProps = cleanedProps;
        localStorage.setItem('all-num-props', JSON.stringify(allNumProps));
        
        let formData = new FormData();
        formData.append('action', 'save_num_props');
        formData.append('num_props', JSON.stringify(allNumProps));
        
        const saveBtn = document.getElementById('save-num-props-btn');
        if (saveBtn) {
            saveBtn.textContent = 'جاري الحفظ في قاعدة البيانات... ⏳';
            saveBtn.style.opacity = '0.7';
        }

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        }).then(r => r.text()).then(txt => {
            if (saveBtn) {
                saveBtn.textContent = 'تم الحفظ في قاعدة البيانات بنجاح ✅';
                saveBtn.style.background = 'linear-gradient(135deg, #10b981, #047857)';
                saveBtn.style.opacity = '1';
                setTimeout(() => {
                    saveBtn.textContent = 'حفظ وتطبيق الخصائص 💾';
                    const menu = document.getElementById('custom-num-menu');
                    if (menu) menu.style.display = 'none';
                }, 800);
            }
        }).catch(err => {
            if (saveBtn) {
                saveBtn.textContent = 'حفظ وتطبيق الخصائص 💾';
                saveBtn.style.opacity = '1';
            }
        });

        applyNumPropsToAll();
    }

    function formatNumberCustom(valStr, conf) {
        if(!valStr && valStr !== 0 && valStr !== "0") return "";
        let val = parseFloat(valStr.toString().replace(/,/g, ''));
        if(isNaN(val)) return valStr;
        
        let parts = val.toFixed(conf.decimals).split('.');
        if(conf.thousandsSep) {
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
        return parts.join('.');
    }

    function applyNumPropsToAll() {
        const targets = [
            document.getElementById('exchange-rate'),
            document.getElementById('amount-local-hdr'),
            document.getElementById('amount-foreign'),
            ...document.querySelectorAll('.block-bank-amount'),
            ...document.querySelectorAll('.block-cheque-amount'),
            ...document.querySelectorAll('.block-total-amount'),
            ...document.querySelectorAll('.block-sub-amount')
        ];
        
        targets.forEach(el => {
            if(!el) return;
            const conf = getPropFor(el);
            
            // Force razor-sharp digits with zero shadow / zero stroke
            el.style.setProperty('text-shadow', 'none', 'important');
            el.style.setProperty('-webkit-text-stroke', '0px', 'important');
            el.style.setProperty('filter', 'none', 'important');
            el.style.setProperty('-webkit-font-smoothing', 'antialiased', 'important');
            el.style.setProperty('-moz-osx-font-smoothing', 'grayscale', 'important');
            el.style.setProperty('text-rendering', 'geometricPrecision', 'important');
            
            // 1. Apply font family
            if (conf.fontFamily) {
                let fam = (conf.fontFamily === 'monospace' || !conf.fontFamily) ? "'Consolas', 'Courier New', monospace" : conf.fontFamily;
                el.style.setProperty('font-family', fam, 'important');
            }
            
            // 2. Apply font weight (السمك)
            if (conf.fontWeight) {
                el.style.setProperty('font-weight', conf.fontWeight, 'important');
            }
            
            // 3. Apply color (اللون)
            if (el === document.activeElement && (
                el.classList.contains('block-total-amount') ||
                el.classList.contains('block-bank-amount') ||
                el.classList.contains('block-cheque-amount') ||
                el.classList.contains('block-sub-amount') ||
                el.classList.contains('transfer-amount') ||
                el.classList.contains('cheque-amount')
            )) {
                el.style.setProperty('color', '#fbbf24', 'important');
                el.style.setProperty('-webkit-text-fill-color', '#fbbf24', 'important');
                el.style.setProperty('caret-color', '#fbbf24', 'important');
                el.style.setProperty('text-shadow', 'none', 'important');
                el.style.setProperty('-webkit-text-stroke', '0px', 'important');
            } else if (el.id === 'amount-local-hdr' || el.id === 'amount-foreign') {
                let chosenColor = '#ef4444';
                if (conf.color && conf.color.toLowerCase() !== '#ffffff') {
                    chosenColor = conf.color;
                }
                el.style.setProperty('color', chosenColor, 'important');
                el.style.setProperty('-webkit-text-fill-color', chosenColor, 'important');
                el.style.setProperty('background', '#ffffff', 'important');
                el.style.setProperty('background-color', '#ffffff', 'important');
                el.style.setProperty('border', '3.5px solid #ef4444', 'important');
                el.style.setProperty('text-shadow', 'none', 'important');
                el.style.setProperty('-webkit-text-stroke', '0px', 'important');
                el.style.setProperty('opacity', '1', 'important');
                el.style.setProperty('visibility', 'visible', 'important');
            } else if (conf.color) {
                el.style.setProperty('color', conf.color, 'important');
                el.style.setProperty('-webkit-text-fill-color', conf.color, 'important');
            }
            
            // 4. Apply font size (الحجم)
            if (el !== document.activeElement) {
                let baseSize = 1.4;
                if (el.id === 'exchange-rate') baseSize = 1.6;
                else if (el.id === 'amount-local-hdr' || el.id === 'amount-foreign') baseSize = 2.6;
                else baseSize = 2.4;

                let newSize = baseSize + (conf.fontSizeMod * 0.1);
                if(newSize < 0.6) newSize = 0.6;
                el.style.setProperty('font-size', newSize + 'rem', 'important');
            }
            
            // 5. Format value
            if(el.value !== "") {
                let cleanVal = el.value.toString().replace(/,/g, '');
                if(!el.dataset.raw) el.dataset.raw = cleanVal;
                else if (cleanVal !== el.dataset.raw && !el.dataset.isFormatting) {
                     el.dataset.raw = cleanVal;
                }
                
                el.dataset.isFormatting = 'true';
                el.value = formatNumberCustom(el.dataset.raw, conf);
                el.dataset.isFormatting = '';
            }
        });
    }

    // Direct function to open the menu for any target
    window.openNumPropsFor = function(targetIdOrEl, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        let el = (typeof targetIdOrEl === 'object' && targetIdOrEl !== null) ? targetIdOrEl : document.getElementById(targetIdOrEl);
        currentPropId = getCanonicalPropId(el || targetIdOrEl);
        
        updateMenuUI();
        
        const menu = document.getElementById('custom-num-menu');
        if (!menu) return;
        
        menu.style.display = 'block';
        
        let x = (e && e.pageX) ? e.pageX : (el ? el.getBoundingClientRect().left + window.scrollX : 200);
        let y = (e && e.pageY) ? e.pageY : (el ? el.getBoundingClientRect().bottom + window.scrollY + 5 : 200);
        
        if (x + 330 > window.innerWidth) {
            x = Math.max(10, window.innerWidth - 340);
        }
        menu.style.left = x + 'px';
        menu.style.top = y + 'px';
    };

    // Right-click context menu event listener
    document.addEventListener('contextmenu', function(e) {
        let target = e.target;
        if (!target) return;
        
        let matched = null;
        if (target.id === 'exchange-rate' || target.closest('#exchange-rate') || target.closest('#label-exchange-rate') || (target.textContent && target.textContent.includes('التعادل') && target.closest('div') && target.closest('div').querySelector('#exchange-rate'))) {
            matched = document.getElementById('exchange-rate');
        } else if (target.closest) {
            matched = target.closest('#exchange-rate, #amount-local-hdr, #amount-foreign, .block-bank-amount, .block-cheque-amount, .block-total-amount, .block-sub-amount');
        }
        
        if (matched) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            openNumPropsFor(matched, e);
        }
    }, true);

    // Mousedown right-click listener (button === 2)
    document.addEventListener('mousedown', function(e) {
        if (e.button !== 2) return;
        let target = e.target;
        if (!target) return;
        
        let matched = null;
        if (target.id === 'exchange-rate' || target.closest('#exchange-rate') || target.closest('#label-exchange-rate') || (target.textContent && target.textContent.includes('التعادل') && target.closest('div') && target.closest('div').querySelector('#exchange-rate'))) {
            matched = document.getElementById('exchange-rate');
        } else if (target.closest) {
            matched = target.closest('#exchange-rate, #amount-local-hdr, #amount-foreign, .block-bank-amount, .block-cheque-amount, .block-total-amount, .block-sub-amount');
        }
        
        if (matched) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            openNumPropsFor(matched, e);
        }
    }, true);

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        const menu = document.getElementById('custom-num-menu');
        if (menu && menu.style.display === 'block' && !menu.contains(e.target)) {
            menu.style.display = 'none';
        }
    });

    // Handle raw value tracking
    document.addEventListener('input', function(e) {
        if (e.target.id === 'exchange-rate' || 
            e.target.id === 'amount-local-hdr' || 
            e.target.id === 'amount-foreign' || 
            e.target.classList.contains('block-bank-amount') || 
            e.target.classList.contains('block-cheque-amount') ||
            e.target.classList.contains('block-total-amount') ||
            e.target.classList.contains('block-sub-amount')) {
            e.target.dataset.raw = e.target.value.replace(/,/g, '');
        }
    });

    // Initial formatting on page load
    document.addEventListener('DOMContentLoaded', function() {
        applyNumPropsToAll();
        setTimeout(applyNumPropsToAll, 400);
    });

    // 🔍 محرك التضخيم والتكبير الفوري بمؤثر حركي انسيابي عالي الاحترافية (Dynamic Fluid Focus Magnification Engine)
    document.addEventListener('focusin', function(e) {
        const el = e.target;
        if (!el || !el.classList) return;
        
        const isAmount = el.classList.contains('block-bank-amount') || 
                         el.classList.contains('block-cheque-amount') || 
                         el.classList.contains('transfer-amount') || 
                         el.classList.contains('cheque-amount') || 
                         el.classList.contains('block-total-amount') ||
                         el.classList.contains('block-sub-amount');
                         
        const isNumber = el.classList.contains('block-transfer-no-input') || 
                         el.classList.contains('block-cheque-no-input') || 
                         el.classList.contains('transfer-no') || 
                         el.classList.contains('cheque-no');
                         
        if (isAmount || isNumber) {
            el.style.setProperty('font-size', '4.5rem', 'important');
            el.style.setProperty('height', '98px', 'important');
            el.style.setProperty('width', '490px', 'important');
            el.style.setProperty('min-width', '460px', 'important');
            el.style.setProperty('position', 'relative', 'important');
            el.style.setProperty('z-index', '99999', 'important');
            el.style.setProperty('margin-right', '-60px', 'important');
            el.style.setProperty('font-weight', '900', 'important');
            el.style.setProperty('background', 'rgba(13, 17, 28, 0.98)', 'important');
            el.style.setProperty('border-radius', '14px', 'important');
            el.style.setProperty('transform', 'translateY(-4px) scale(1.015)', 'important');
            el.style.setProperty('transition', 'all 0.42s cubic-bezier(0.16, 1, 0.3, 1)', 'important');
            
            if (isAmount) {
                el.style.setProperty('color', '#fbbf24', 'important');
                el.style.setProperty('-webkit-text-fill-color', '#fbbf24', 'important');
                el.style.setProperty('caret-color', '#fbbf24', 'important');
                el.style.setProperty('text-shadow', 'none', 'important');
                el.style.setProperty('-webkit-text-stroke', '0px', 'important');
                el.style.setProperty('filter', 'none', 'important');
                el.style.setProperty('-webkit-font-smoothing', 'antialiased', 'important');
                el.style.setProperty('-moz-osx-font-smoothing', 'grayscale', 'important');
                el.style.setProperty('text-rendering', 'geometricPrecision', 'important');
                el.style.setProperty('font-family', "'Consolas', 'Courier New', monospace", 'important');
                el.style.setProperty('border', '3.5px solid #fbbf24', 'important');
                el.style.setProperty('box-shadow', '0 20px 45px rgba(0,0,0,0.92), 0 0 25px rgba(251, 191, 36, 0.45), inset 0 1px 1px rgba(255, 255, 255, 0.25)', 'important');
            } else {
                el.style.setProperty('color', '#38bdf8', 'important');
                el.style.setProperty('-webkit-text-fill-color', '#38bdf8', 'important');
                el.style.setProperty('caret-color', '#38bdf8', 'important');
                el.style.setProperty('text-shadow', 'none', 'important');
                el.style.setProperty('-webkit-text-stroke', '0px', 'important');
                el.style.setProperty('filter', 'none', 'important');
                el.style.setProperty('-webkit-font-smoothing', 'antialiased', 'important');
                el.style.setProperty('-moz-osx-font-smoothing', 'grayscale', 'important');
                el.style.setProperty('text-rendering', 'geometricPrecision', 'important');
                el.style.setProperty('font-family', "'Consolas', 'Courier New', monospace", 'important');
                el.style.setProperty('border', '3.5px solid #38bdf8', 'important');
                el.style.setProperty('box-shadow', '0 20px 45px rgba(0,0,0,0.92), 0 0 25px rgba(56, 189, 248, 0.45), inset 0 1px 1px rgba(255, 255, 255, 0.25)', 'important');
                el.style.setProperty('letter-spacing', '1.5px', 'important');
            }
            
            if (el.parentElement && el.parentElement.style) {
                el.parentElement.dataset.origZIndex = el.parentElement.style.zIndex || '';
                el.parentElement.dataset.origPosition = el.parentElement.style.position || '';
                el.parentElement.style.zIndex = '99999';
                el.parentElement.style.position = 'relative';
            }
        }
    }, true);

    document.addEventListener('focusout', function(e) {
        const el = e.target;
        if (!el || !el.classList) return;
        
        const isAmount = el.classList.contains('block-bank-amount') || 
                         el.classList.contains('block-cheque-amount') || 
                         el.classList.contains('transfer-amount') || 
                         el.classList.contains('cheque-amount') || 
                         el.classList.contains('block-total-amount') ||
                         el.classList.contains('block-sub-amount');
                         
        const isNumber = el.classList.contains('block-transfer-no-input') || 
                         el.classList.contains('block-cheque-no-input') || 
                         el.classList.contains('transfer-no') || 
                         el.classList.contains('cheque-no');
                         
        if (isAmount || isNumber) {
            el.style.setProperty('transition', 'all 0.38s cubic-bezier(0.16, 1, 0.3, 1)', 'important');
            el.style.removeProperty('height');
            el.style.removeProperty('width');
            el.style.removeProperty('min-width');
            el.style.removeProperty('position');
            el.style.removeProperty('z-index');
            el.style.removeProperty('margin-right');
            el.style.removeProperty('background');
            el.style.removeProperty('border');
            el.style.removeProperty('border-radius');
            el.style.removeProperty('box-shadow');
            el.style.removeProperty('transform');
            el.style.removeProperty('color');
            el.style.removeProperty('-webkit-text-fill-color');
            el.style.removeProperty('caret-color');
            el.style.removeProperty('text-shadow');
            el.style.removeProperty('letter-spacing');
            
            el.style.removeProperty('font-size');
            
            if (el.parentElement && el.parentElement.style) {
                setTimeout(() => {
                    if (document.activeElement !== el) {
                        el.parentElement.style.zIndex = el.parentElement.dataset.origZIndex || '';
                        el.parentElement.style.position = el.parentElement.dataset.origPosition || '';
                    }
                }, 400);
            }
        }
        
        if (isAmount || 
            e.target.id === 'amount-local-hdr' || 
            e.target.id === 'amount-foreign' || 
            e.target.id === 'exchange-rate' ||
            e.target.classList.contains('block-bank-amount') || 
            e.target.classList.contains('block-cheque-amount') ||
            e.target.classList.contains('block-total-amount') ||
            e.target.classList.contains('block-sub-amount')) {
            applyNumPropsToAll();
        }
    }, true);
    
    // Periodically apply styles to newly added rows dynamically
    setInterval(applyNumPropsToAll, 1000);
    </script>
    <!-- Inbound Bankak Receipts Drawer Component -->
    <?php include 'includes/inbound-bankak-drawer.php'; ?>

    <!-- Financial OCR Modal & Script -->
    <?php include 'includes/financial-ocr-modal.php'; ?>
    <script src="js/financial-ocr.js?v=<?php echo file_exists('js/financial-ocr.js') ? filemtime('js/financial-ocr.js') : 1; ?>"></script>
</body>
</html>











