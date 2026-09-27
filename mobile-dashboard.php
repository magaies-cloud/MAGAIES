<?php
/**
 * ═════════════════════════════════════════════════════════════════════
 * 📱 MAGAIEES ERP 2026 - POCKET EXECUTIVE MOBILE EDITION
 * ═════════════════════════════════════════════════════════════════════
 * النسخة التنفيذية الذكية للهواتف الذكية والأجهزة اللوحية
 * تصميم ثنائي المحرك: [ ⚡ العمليات السريعة ] + [ 📊 التقارير الذكية ]
 * متصل مباشرة بقاعدة البيانات الحقيقية (MariaDB) ويدعم 4 سمات فائقة التباين
 */

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isMobileUserAgent = (bool)preg_match('/(android|iphone|ipad|ipod|mobile|blackberry|iemobile|opera mini)/i', $userAgent);
$forceMobile = isset($_GET['force_mobile']) && $_GET['force_mobile'] === '1';
$prefCookie = $_COOKIE['magaiees_view_preference'] ?? '';

// Check if accessing from desktop without mobile override
if ($forceMobile) {
    setcookie('magaiees_view_preference', 'mobile', time() + (86400 * 365), '/');
} elseif (!$isMobileUserAgent && $prefCookie !== 'mobile') {
    header('Location: magaiees_unified_dashboard.php');
    exit;
}

$page_title = 'مقاييس الموبايل | النسخة السريعة';
require_once __DIR__ . '/db_bridge.php';

// Detect active theme from cookie or default to midnight
$activeTheme = $_COOKIE['magaiees_mobile_theme'] ?? 'midnight';
if (!in_array($activeTheme, ['solar', 'midnight', 'navy', 'forest'])) {
    $activeTheme = 'midnight';
}

// ═════════════════════════════════════════════════════════════════════
// 🌐 SERVER-SIDE REAL DATABASE PRELOADER (MARIADB DIRECT)
// ═════════════════════════════════════════════════════════════════════
$preloadedData = [
    'companyName' => 'مقاييس لإدارة الأعمال',
    'tenantId' => 1,
    'vaults' => [],
    'customers' => [],
    'items' => [],
    'receipts' => [],
    'payments' => [],
    'sales' => []
];

try {
    $pdo = getDBConnection();
    
    // Auto-detect tenant priority: Query Param > Subdomain > Cookie > Session > GLOBAL_TENANT_ID > 1
    $currentTenantId = null;
    if (isset($_GET['tenant_id']) && is_numeric($_GET['tenant_id'])) {
        $currentTenantId = (int)$_GET['tenant_id'];
    } elseif (!empty($_SERVER['HTTP_HOST']) && preg_match('/^([a-z0-9_-]+)\.elsajana\.com$/i', $_SERVER['HTTP_HOST'], $m)) {
        $sub = strtolower($m[1]);
        $stmtSub = $pdo->prepare("SELECT id FROM tenants WHERE subdomain = ? LIMIT 1");
        $stmtSub->execute([$sub]);
        $subId = $stmtSub->fetchColumn();
        if ($subId) $currentTenantId = (int)$subId;
    } elseif (isset($_COOKIE['currentTenantId']) && is_numeric($_COOKIE['currentTenantId'])) {
        $currentTenantId = (int)$_COOKIE['currentTenantId'];
    } elseif (isset($_COOKIE['tenant_id']) && is_numeric($_COOKIE['tenant_id'])) {
        $currentTenantId = (int)$_COOKIE['tenant_id'];
    } elseif (isset($_SESSION['user']['tenant_id']) && is_numeric($_SESSION['user']['tenant_id'])) {
        $currentTenantId = (int)$_SESSION['user']['tenant_id'];
    }
    
    if (!$currentTenantId) {
        $currentTenantId = $GLOBAL_TENANT_ID ?? 1;
    }
    
    $GLOBAL_TENANT_ID = $currentTenantId;
    $preloadedData['tenantId'] = $currentTenantId;

    // 1. Company Name from Tenant Record
    $stmtT = $pdo->prepare("SELECT company_name FROM tenants WHERE id = ?");
    $stmtT->execute([$currentTenantId]);
    $cName = $stmtT->fetchColumn();
    if ($cName) $preloadedData['companyName'] = $cName;

    // 2. Real Vaults & Liquidity
    $preloadedData['vaults'] = getRealTimeLiquidity($pdo);

    // 3. Real Customers & Debts
    $preloadedData['customers'] = getCustomerExposureData($pdo);

    // 4. Real Suppliers
    $preloadedData['suppliers'] = [];
    try {
        $stmtSup = $pdo->prepare("SELECT data FROM suppliers WHERE tenant_id = ? ORDER BY id DESC LIMIT 100");
        $stmtSup->execute([$currentTenantId]);
        while ($r = $stmtSup->fetch()) {
            $supObj = json_decode($r['data'], true);
            if ($supObj) $preloadedData['suppliers'][] = $supObj;
        }
    } catch(Exception $e){}

    // 5. Real Banks & Safes
    $preloadedData['banks'] = [];
    try {
        $stmtB = $pdo->prepare("SELECT data FROM banks WHERE tenant_id = ? ORDER BY id DESC");
        $stmtB->execute([$currentTenantId]);
        while ($r = $stmtB->fetch()) {
            $bObj = json_decode($r['data'], true);
            if ($bObj) $preloadedData['banks'][] = $bObj;
        }
    } catch(Exception $e){}

    $preloadedData['safes'] = [];
    try {
        $stmtS = $pdo->prepare("SELECT data FROM safes WHERE tenant_id = ? ORDER BY id DESC");
        $stmtS->execute([$currentTenantId]);
        while ($r = $stmtS->fetch()) {
            $sObj = json_decode($r['data'], true);
            if ($sObj) $preloadedData['safes'][] = $sObj;
        }
    } catch(Exception $e){}

    // 6. Real Products & Items
    $stmtItm = $pdo->prepare("SELECT data FROM items WHERE tenant_id = ? ORDER BY id DESC LIMIT 50");
    $stmtItm->execute([$currentTenantId]);
    while ($r = $stmtItm->fetch()) {
        $itemObj = json_decode($r['data'], true);
        if ($itemObj) $preloadedData['items'][] = $itemObj;
    }

    // 5. Real Receipts & Payments & Invoices
    $stmtRec = $pdo->prepare("SELECT data FROM single_receipts WHERE tenant_id = ? ORDER BY id DESC LIMIT 20");
    $stmtRec->execute([$currentTenantId]);
    while ($r = $stmtRec->fetch()) {
        $recObj = json_decode($r['data'], true);
        if ($recObj) $preloadedData['receipts'][] = $recObj;
    }

    $stmtPay = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ? ORDER BY id DESC LIMIT 20");
    $stmtPay->execute([$currentTenantId]);
    while ($r = $stmtPay->fetch()) {
        $payObj = json_decode($r['data'], true);
        if ($payObj) $preloadedData['payments'][] = $payObj;
    }

    $stmtSale = $pdo->prepare("SELECT data FROM sales_invoices WHERE tenant_id = ? ORDER BY id DESC LIMIT 20");
    $stmtSale->execute([$currentTenantId]);
    while ($r = $stmtSale->fetch()) {
        $saleObj = json_decode($r['data'], true);
        if ($saleObj) $preloadedData['sales'][] = $saleObj;
    }
} catch (Exception $e) {
    error_log('Mobile Dashboard Preloader Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="<?php echo htmlspecialchars($activeTheme); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="<?php echo ($activeTheme === 'solar') ? '#f1f5f9' : (($activeTheme === 'navy') ? '#030a1a' : (($activeTheme === 'forest') ? '#03140d' : '#000000')); ?>">
    <title><?php echo htmlspecialchars($preloadedData['companyName']); ?> - النسخة السريعة</title>

    <!-- Client-Side Auth Gate Check -->
    <script>
    (function() {
        const token = localStorage.getItem('authToken');
        const isLocalhost = window.location.hostname.includes('localhost') || window.location.hostname.includes('127.0.0.1');
        if (!token && !isLocalhost) {
            window.location.replace('login.php?redirect=mobile-dashboard.php');
        }
    })();
    </script>

    <!-- Preconnect & Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/fontawesome/all.min.css">

    <!-- Permissions & Audio System -->
    <script src="js/permissions-manager.js"></script>
    <script src="js/modern-alerts.js"></script>

    <!-- Server Preloaded Data Injection -->
    <script>
    window.SERVER_DATA = <?php echo json_encode($preloadedData, JSON_UNESCAPED_UNICODE); ?>;
    </script>

    <style>
        /* ═════════════════════════════════════════════════════════════════
           🎨 4 WORLD-CLASS HIGH-CONTRAST ERGONOMIC THEMES
           ═════════════════════════════════════════════════════════════════ */
        
        /* 1. DEFAULT & THEME 2: 🌙 OLED PITCH MIDNIGHT (سواد فاحم مريح) */
        :root,
        html[data-theme="midnight"],
        body[data-theme="midnight"] {
            --bg-base: #000000;
            --bg-gradient: radial-gradient(circle at 10% 20%, rgba(56, 189, 248, 0.08) 0%, transparent 40%),
                           radial-gradient(circle at 90% 80%, rgba(16, 185, 129, 0.08) 0%, transparent 40%);
            --surface-1: #090e17;
            --surface-2: #111827;
            --surface-card: #0c121e;
            --surface-sub: rgba(255, 255, 255, 0.04);
            --border-glass: rgba(255, 255, 255, 0.16);
            --border-card: rgba(56, 189, 248, 0.28);
            --primary: #38bdf8;
            --emerald: #10b981;
            --amber: #f59e0b;
            --rose: #f43f5e;
            --purple: #a855f7;
            --text-main: #ffffff;
            --text-muted: #94a3b8;
            --header-bg: rgba(0, 0, 0, 0.94);
            --nav-bg: rgba(0, 0, 0, 0.96);
            --sheet-bg: #070b12;
            --input-bg: #0d1522;
            --input-border: rgba(255, 255, 255, 0.2);
            --input-color: #ffffff;
            --pill-bg: #0d1522;
            --pill-active-bg: linear-gradient(135deg, rgba(56, 189, 248, 0.25), rgba(16, 185, 129, 0.2));
            --pill-active-text: #ffffff;
            --grand-color: #34d399;
            --grand-sub: #a7f3d0;
            --shadow-card: 0 10px 30px rgba(0, 0, 0, 0.8);
            --safe-bottom: env(safe-area-inset-bottom, 16px);
        }

        /* 2. THEME 1: ☀️ SOLAR WHITE (نهار وشمس ساطعة - تباين فائق 21:1) */
        html[data-theme="solar"],
        body[data-theme="solar"] {
            --bg-base: #f1f5f9;
            --bg-gradient: radial-gradient(circle at 10% 20%, rgba(2, 132, 199, 0.06) 0%, transparent 40%),
                           radial-gradient(circle at 90% 80%, rgba(5, 150, 105, 0.06) 0%, transparent 40%);
            --surface-1: #ffffff;
            --surface-2: #e2e8f0;
            --surface-card: #ffffff;
            --surface-sub: #f8fafc;
            --border-glass: rgba(15, 23, 42, 0.2);
            --border-card: #cbd5e1;
            --primary: #0284c7;
            --emerald: #059669;
            --amber: #d97706;
            --rose: #dc2626;
            --purple: #7c3aed;
            --text-main: #020617;
            --text-muted: #334155;
            --header-bg: rgba(255, 255, 255, 0.96);
            --nav-bg: rgba(255, 255, 255, 0.98);
            --sheet-bg: #ffffff;
            --input-bg: #f8fafc;
            --input-border: #cbd5e1;
            --input-color: #020617;
            --pill-bg: #e2e8f0;
            --pill-active-bg: #ffffff;
            --pill-active-text: #0284c7;
            --grand-color: #047857;
            --grand-sub: #059669;
            --shadow-card: 0 6px 20px rgba(0, 0, 0, 0.08);
        }

        /* 3. THEME 3: 🧠 EXECUTIVE ROYAL NAVY & GOLD (التركيز المالي السيادي) */
        html[data-theme="navy"],
        body[data-theme="navy"] {
            --bg-base: #030a1a;
            --bg-gradient: radial-gradient(circle at 10% 20%, rgba(56, 189, 248, 0.12) 0%, transparent 40%),
                           radial-gradient(circle at 90% 80%, rgba(245, 158, 11, 0.1) 0%, transparent 40%);
            --surface-1: #0a1628;
            --surface-2: #10223d;
            --surface-card: #0d1d36;
            --surface-sub: rgba(255, 255, 255, 0.05);
            --border-glass: rgba(56, 189, 248, 0.25);
            --border-card: rgba(251, 191, 36, 0.45);
            --primary: #38bdf8;
            --emerald: #10b981;
            --amber: #fbbf24;
            --rose: #f43f5e;
            --purple: #818cf8;
            --text-main: #ffffff;
            --text-muted: #93c5fd;
            --header-bg: rgba(3, 10, 26, 0.94);
            --nav-bg: rgba(3, 10, 26, 0.96);
            --sheet-bg: #060e20;
            --input-bg: #0a172c;
            --input-border: rgba(56, 189, 248, 0.35);
            --input-color: #ffffff;
            --pill-bg: #0a172c;
            --pill-active-bg: linear-gradient(135deg, rgba(251, 191, 36, 0.25), rgba(56, 189, 248, 0.25));
            --pill-active-text: #fbbf24;
            --grand-color: #fbbf24;
            --grand-sub: #fef08a;
            --shadow-card: 0 10px 30px rgba(0, 0, 0, 0.7);
        }

        /* 4. THEME 4: 🌿 FOREST EMERALD & MINT (الراحة واليقظة الممتدة) */
        html[data-theme="forest"],
        body[data-theme="forest"] {
            --bg-base: #03140d;
            --bg-gradient: radial-gradient(circle at 15% 25%, rgba(52, 211, 153, 0.12) 0%, transparent 45%),
                           radial-gradient(circle at 85% 75%, rgba(16, 185, 129, 0.1) 0%, transparent 45%);
            --surface-1: #072317;
            --surface-2: #0e3424;
            --surface-card: #09281a;
            --surface-sub: rgba(255, 255, 255, 0.05);
            --border-glass: rgba(52, 211, 153, 0.28);
            --border-card: rgba(52, 211, 153, 0.4);
            --primary: #34d399;
            --emerald: #10b981;
            --amber: #fbbf24;
            --rose: #f43f5e;
            --purple: #6ee7b7;
            --text-main: #ffffff;
            --text-muted: #a7f3d0;
            --header-bg: rgba(3, 20, 13, 0.94);
            --nav-bg: rgba(3, 20, 13, 0.96);
            --sheet-bg: #04170f;
            --input-bg: #072517;
            --input-border: rgba(52, 211, 153, 0.35);
            --input-color: #ffffff;
            --pill-bg: #072517;
            --pill-active-bg: linear-gradient(135deg, rgba(52, 211, 153, 0.25), rgba(16, 185, 129, 0.25));
            --pill-active-text: #34d399;
            --grand-color: #34d399;
            --grand-sub: #a7f3d0;
            --shadow-card: 0 10px 30px rgba(0, 0, 0, 0.7);
        }

        /* ═════════════════════════════════════════════════════════════════
           CORE LAYOUT & COMPONENTS (THEME-VARIABLE POWERED)
           ═════════════════════════════════════════════════════════════════ */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background-color: var(--bg-base);
            background-image: var(--bg-gradient);
            background-attachment: fixed;
            font-family: 'Tajawal', sans-serif;
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            padding-bottom: calc(85px + var(--safe-bottom));
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        /* Ambient Mobile Header */
        .mobile-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: var(--header-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1.5px solid var(--border-glass);
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            flex: 1;
        }
        .brand-avatar {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--emerald));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            color: #fff;
            box-shadow: 0 4px 14px rgba(56, 189, 248, 0.35);
            flex-shrink: 0;
        }
        .brand-meta {
            min-width: 0;
            overflow: hidden;
        }
        .brand-meta h1 {
            font-size: 0.98rem;
            font-weight: 900;
            color: var(--text-main);
            line-height: 1.2;
            letter-spacing: 0.2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .brand-meta span {
            font-size: 0.74rem;
            color: var(--primary);
            font-weight: 800;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }

        .btn-switch-desktop {
            background: var(--surface-2);
            border: 1.5px solid var(--border-glass);
            color: var(--text-main);
            padding: 7px 11px;
            border-radius: 10px;
            font-size: 0.78rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-switch-desktop:active {
            transform: scale(0.95);
            border-color: var(--primary);
            color: var(--primary);
        }

        @media (max-width: 420px) {
            .btn-switch-desktop span {
                display: none;
            }
            .btn-switch-desktop {
                padding: 8px 10px;
            }
            .brand-meta h1 {
                font-size: 0.88rem;
            }
            .brand-meta span {
                font-size: 0.68rem;
            }
        }

        /* Dual-Engine Mode Switcher (Pill Segmented Control) */
        .mode-switcher-container {
            padding: 14px 16px 6px 16px;
        }
        .mode-segmented-pill {
            display: flex;
            background: var(--pill-bg);
            border: 1.5px solid var(--border-glass);
            border-radius: 16px;
            padding: 4px;
            position: relative;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
        }
        .mode-tab-btn {
            flex: 1;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 800;
            padding: 12px 10px;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .mode-tab-btn.active {
            background: var(--pill-active-bg);
            color: var(--pill-active-text);
            border: 1.5px solid var(--primary);
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.25);
        }
        .mode-tab-btn.active i {
            color: var(--primary);
            transform: scale(1.1);
        }

        /* Content Container */
        .mobile-content {
            padding: 12px 16px;
        }

        /* Liquidity Pulse Card */
        .liquidity-card {
            background: var(--surface-card);
            border: 2px solid var(--border-card);
            border-radius: 20px;
            padding: 18px 20px;
            box-shadow: var(--shadow-card);
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            transition: all 0.25s ease;
        }
        .liquidity-card::before {
            content: '';
            position: absolute;
            top: -50px;
            left: -50px;
            width: 120px;
            height: 120px;
            background: radial-gradient(circle, var(--primary), transparent 70%);
            opacity: 0.25;
            pointer-events: none;
        }
        .liquidity-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .liquidity-title {
            font-size: 0.84rem;
            font-weight: 800;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .liquidity-grand {
            font-size: 2.2rem;
            font-weight: 900;
            color: var(--grand-color);
            letter-spacing: -0.5px;
            font-family: 'Inter', 'Tajawal', sans-serif;
            line-height: 1.1;
            margin-bottom: 14px;
        }
        .liquidity-grand small {
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--grand-sub);
            margin-right: 4px;
        }
        .liquidity-breakdown {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding-top: 12px;
            border-top: 1.5px dashed var(--border-glass);
        }
        .vault-item {
            background: var(--surface-sub);
            border-radius: 12px;
            padding: 8px 12px;
            border: 1px solid var(--border-glass);
        }
        .vault-name {
            font-size: 0.74rem;
            color: var(--text-muted);
            font-weight: 800;
            display: block;
            margin-bottom: 3px;
        }
        .vault-amt {
            font-size: 1.15rem;
            font-weight: 900;
            color: var(--text-main);
            font-family: 'Inter', 'Tajawal', sans-serif;
        }

        /* Power Quick Action Cards (4 Core Buttons) */
        .section-heading {
            font-size: 0.95rem;
            font-weight: 900;
            color: var(--text-main);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .actions-2x2-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 22px;
        }
        .power-action-btn {
            background: var(--surface-card);
            border: 1.5px solid var(--border-glass);
            border-radius: 18px;
            padding: 16px 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: var(--shadow-card);
            min-height: 105px;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }
        .power-action-btn:active {
            transform: scale(0.96);
            border-color: var(--primary);
        }
        .action-icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
            transition: transform 0.2s;
        }
        .power-action-btn:active .action-icon-circle {
            transform: scale(1.1);
        }
        .action-title {
            font-size: 0.95rem;
            font-weight: 900;
            color: var(--text-main);
            line-height: 1.25;
        }
        .action-subtitle {
            font-size: 0.74rem;
            color: var(--text-muted);
            font-weight: 700;
        }

        /* Specific Action Flavors */
        .act-receipt .action-icon-circle { background: rgba(16, 185, 129, 0.2); border: 1.5px solid var(--emerald); color: var(--emerald); }
        .act-payment .action-icon-circle { background: rgba(244, 63, 94, 0.2); border: 1.5px solid var(--rose); color: var(--rose); }
        .act-pos .action-icon-circle { background: rgba(56, 189, 248, 0.2); border: 1.5px solid var(--primary); color: var(--primary); }
        .act-statement .action-icon-circle { background: rgba(168, 85, 247, 0.2); border: 1.5px solid var(--purple); color: var(--purple); }

        /* Recent Activity Feed */
        .activity-card {
            background: var(--surface-card);
            border: 1.5px solid var(--border-glass);
            border-radius: 16px;
            padding: 14px 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            transition: transform 0.2s;
        }
        .activity-card:active {
            transform: scale(0.98);
        }
        .activity-info {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            flex: 1;
        }
        .activity-badge {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .act-badge-in { background: rgba(16, 185, 129, 0.18); color: var(--emerald); border: 1px solid var(--emerald); }
        .act-badge-out { background: rgba(244, 63, 94, 0.18); color: var(--rose); border: 1px solid var(--rose); }
        .act-badge-sale { background: rgba(56, 189, 248, 0.18); color: var(--primary); border: 1px solid var(--primary); }

        .activity-details {
            min-width: 0;
        }
        .activity-details h5 {
            font-size: 0.92rem;
            font-weight: 900;
            color: var(--text-main);
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .activity-details span {
            font-size: 0.74rem;
            color: var(--text-muted);
            font-weight: 700;
            display: block;
        }
        .activity-amount {
            text-align: left;
            flex-shrink: 0;
        }
        .activity-val {
            font-size: 1.1rem;
            font-weight: 900;
            font-family: 'Inter', sans-serif;
            display: block;
        }
        .activity-val.positive { color: var(--emerald); }
        .activity-val.negative { color: var(--rose); }
        .btn-wa-direct {
            background: rgba(34, 197, 94, 0.18);
            border: 1px solid #22c55e;
            color: #22c55e;
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 800;
            cursor: pointer;
            margin-top: 4px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* 📊 Analytics Mode Styling */
        .analytics-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 18px;
        }
        .kpi-card {
            background: var(--surface-card);
            border: 1.5px solid var(--border-glass);
            border-radius: 16px;
            padding: 14px 16px;
            box-shadow: var(--shadow-card);
        }
        .kpi-label {
            font-size: 0.76rem;
            font-weight: 800;
            color: var(--text-muted);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .kpi-value {
            font-size: 1.5rem;
            font-weight: 900;
            font-family: 'Inter', 'Tajawal', sans-serif;
            line-height: 1.2;
            color: var(--text-main);
        }

        .radar-card {
            background: var(--surface-card);
            border: 1.5px solid var(--border-glass);
            border-radius: 18px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: var(--shadow-card);
        }
        .radar-title {
            font-size: 0.95rem;
            font-weight: 900;
            color: var(--text-main);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .radar-row {
            background: var(--surface-sub);
            border: 1px solid var(--border-glass);
            border-radius: 14px;
            padding: 12px 14px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Bottom Sheet Modals (Native Mobile Sheet with Spring Animation) */
        .mobile-sheet-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .mobile-sheet-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }
        .mobile-bottom-sheet {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            max-height: 92vh;
            background: var(--sheet-bg);
            border-top: 3.5px solid var(--primary);
            border-radius: 26px 26px 0 0;
            box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.85);
            z-index: 1001;
            transform: translateY(100%);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            padding-bottom: var(--safe-bottom);
            color: var(--text-main);
        }
        .mobile-bottom-sheet.active {
            transform: translateY(0);
        }
        .sheet-drag-handle {
            width: 44px;
            height: 5px;
            background: var(--border-glass);
            border-radius: 10px;
            margin: 10px auto 4px auto;
        }
        .sheet-header {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1.5px solid var(--border-glass);
        }
        .sheet-title {
            font-size: 1.15rem;
            font-weight: 900;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-sheet-close {
            background: var(--surface-2);
            border: 1px solid var(--border-glass);
            color: var(--text-muted);
            width: 34px;
            height: 34px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sheet-body {
            padding: 16px 20px;
            overflow-y: auto;
            flex: 1;
        }

        /* Mobile Form Controls */
        .m-form-group {
            margin-bottom: 14px;
        }
        .m-label {
            display: block;
            font-size: 0.86rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 6px;
        }
        .m-input, .m-select {
            width: 100%;
            padding: 13px 16px;
            background: var(--input-bg);
            border: 2px solid var(--input-border);
            color: var(--input-color);
            border-radius: 14px;
            font-size: 1.05rem;
            font-weight: 700;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        .m-input:focus, .m-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.25);
        }
        .m-input.huge-number {
            font-size: 1.8rem;
            font-weight: 900;
            text-align: center;
            color: var(--emerald);
            letter-spacing: 1px;
            font-family: 'Inter', monospace, sans-serif;
        }

        .m-btn-submit {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 14px;
            font-size: 1.08rem;
            font-weight: 900;
            font-family: inherit;
            color: #ffffff;
            background: linear-gradient(135deg, #10b981, #059669);
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }
        .m-btn-submit:active {
            transform: scale(0.98);
        }

        /* ═════════════════════════════════════════════════════════════════
           💸 UNIFIED FINANCIAL TRANSACTION & SMART ACCOUNT PICKER STYLES
           ═════════════════════════════════════════════════════════════════ */
        .tx-type-pill-container {
            display: flex;
            background: var(--surface-sub);
            border: 1.5px solid var(--border-glass);
            border-radius: 14px;
            padding: 4px;
            gap: 4px;
            margin-bottom: 14px;
        }
        .tx-type-btn {
            flex: 1;
            padding: 9px 4px;
            border-radius: 10px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-size: 0.84rem;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
            font-family: inherit;
        }
        .tx-type-btn.active.type-receipt {
            background: rgba(16, 185, 129, 0.22);
            color: var(--emerald);
            border: 1px solid var(--emerald);
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.25);
        }
        .tx-type-btn.active.type-payment {
            background: rgba(244, 63, 94, 0.22);
            color: var(--rose);
            border: 1px solid var(--rose);
            box-shadow: 0 2px 10px rgba(244, 63, 94, 0.25);
        }
        .tx-type-btn.active.type-transfer {
            background: rgba(56, 189, 248, 0.22);
            color: var(--primary);
            border: 1px solid var(--primary);
            box-shadow: 0 2px 10px rgba(56, 189, 248, 0.25);
        }

        /* Account Selector Boxes */
        .account-selector-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
            position: relative;
            margin-bottom: 14px;
        }
        .box-account-picker {
            border-radius: 16px;
            padding: 12px 14px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }
        .box-account-picker:active {
            transform: scale(0.98);
        }
        /* 🔴 FROM ACCOUNT: Light Soft Red */
        .box-account-from {
            background: rgba(239, 68, 68, 0.10);
            border: 1.8px solid rgba(239, 68, 68, 0.38);
        }
        html[data-theme="solar"] .box-account-from {
            background: #fef2f2 !important;
            border-color: #fca5a5 !important;
        }
        .box-account-from .box-acc-tag {
            color: #ef4444;
            font-size: 0.8rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* 🟢 TO ACCOUNT: Light Soft Green */
        .box-account-to {
            background: rgba(16, 185, 129, 0.10);
            border: 1.8px solid rgba(16, 185, 129, 0.38);
        }
        html[data-theme="solar"] .box-account-to {
            background: #ecfdf5 !important;
            border-color: #6ee7b7 !important;
        }
        .box-account-to .box-acc-tag {
            color: #10b981;
            font-size: 0.8rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .box-acc-left {
            display: flex;
            flex-direction: column;
            gap: 3px;
            min-width: 0;
            flex: 1;
        }
        .box-acc-title {
            font-size: 1.05rem;
            font-weight: 900;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .box-acc-meta {
            font-size: 0.74rem;
            color: var(--text-muted);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .box-acc-right {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
            color: var(--text-muted);
        }
        .btn-swap-accounts {
            align-self: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--surface-card);
            border: 1.5px solid var(--border-glass);
            color: var(--text-main);
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--shadow-card);
            transition: transform 0.25s ease;
            margin: -6px auto;
            z-index: 2;
        }
        .btn-swap-accounts:active {
            transform: rotate(180deg) scale(0.92);
        }

        /* Searchable Accounts Modal Sheet */
        .picker-search-bar {
            position: sticky;
            top: 0;
            background: var(--sheet-bg);
            padding: 0 0 10px 0;
            z-index: 10;
        }
        .picker-category-pills {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            padding-bottom: 8px;
            scrollbar-width: none;
        }
        .picker-category-pills::-webkit-scrollbar {
            display: none;
        }
        .cat-pill {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.76rem;
            font-weight: 800;
            background: var(--surface-sub);
            border: 1px solid var(--border-glass);
            color: var(--text-muted);
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
        }
        .cat-pill.active {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
        }
        .account-item-card {
            background: var(--surface-card);
            border: 1px solid var(--border-glass);
            border-radius: 14px;
            padding: 12px 14px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .account-item-card:active {
            transform: scale(0.98);
            border-color: var(--primary);
        }
        .acc-badge {
            padding: 3px 8px;
            border-radius: 8px;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.2px;
        }
        .badge-bank { background: rgba(56, 189, 248, 0.18); color: #38bdf8; border: 1px solid #38bdf8; }
        .badge-safe { background: rgba(16, 185, 129, 0.18); color: #10b981; border: 1px solid #10b981; }
        .badge-customer { background: rgba(251, 191, 36, 0.18); color: #fbbf24; border: 1px solid #fbbf24; }
        .badge-supplier { background: rgba(168, 85, 247, 0.18); color: #a855f7; border: 1px solid #a855f7; }
        .badge-expense { background: rgba(244, 63, 94, 0.18); color: #f43f5e; border: 1px solid #f43f5e; }
        .badge-revenue { background: rgba(52, 211, 153, 0.18); color: #34d399; border: 1px solid #34d399; }

        /* AI Slip Extractor Modal Sheet */
        .ai-slip-textarea {
            width: 100%;
            min-height: 110px;
            padding: 12px 14px;
            background: var(--input-bg);
            border: 2px solid var(--input-border);
            border-radius: 14px;
            color: var(--input-color);
            font-size: 0.92rem;
            font-family: inherit;
            line-height: 1.5;
            resize: vertical;
            outline: none;
            box-sizing: border-box;
        }
        .ai-slip-textarea:focus {
            border-color: var(--primary);
        }
        .ai-preview-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin: 12px 0;
        }
        .ai-chip {
            background: var(--surface-sub);
            border: 1px solid var(--border-glass);
            border-radius: 12px;
            padding: 8px 10px;
        }
        .ai-chip-label {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-weight: 800;
            display: block;
            margin-bottom: 2px;
        }
        .ai-chip-val {
            font-size: 0.95rem;
            font-weight: 900;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* WhatsApp Target Modal */
        .wa-target-option {
            background: var(--surface-card);
            border: 1.5px solid var(--border-glass);
            border-radius: 16px;
            padding: 14px 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .wa-target-option:active {
            transform: scale(0.98);
            border-color: #22c55e;
        }
        .wa-opt-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        /* Fixed Bottom Navigation Bar */
        .bottom-nav-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--nav-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-top: 1.5px solid var(--border-glass);
            padding: 8px 12px calc(8px + var(--safe-bottom)) 12px;
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 990;
        }
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.72rem;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 10px;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            background: transparent;
        }
        .nav-item i {
            font-size: 1.25rem;
            transition: transform 0.2s;
        }
        .nav-item.active {
            color: var(--primary);
        }
        .nav-item.active i {
            transform: translateY(-2px);
            color: var(--primary);
        }

        /* Theme Selection Sheet Styles */
        .theme-card-option {
            background: var(--surface-sub);
            border: 2px solid var(--border-glass);
            border-radius: 16px;
            padding: 13px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.22s ease;
            position: relative;
        }
        .theme-card-option:active { transform: scale(0.97); }
        .theme-card-option.active {
            border-color: var(--primary);
            background: rgba(56, 189, 248, 0.15);
            box-shadow: 0 4px 18px rgba(56, 189, 248, 0.25);
        }
        .theme-swatch-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
            flex-shrink: 0;
        }
        .theme-info-box { flex: 1; }
        .theme-title-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3px;
            flex-wrap: wrap;
            gap: 4px;
        }
        .theme-title-row strong {
            font-size: 0.92rem;
            color: var(--text-main);
            font-weight: 900;
        }
        .theme-badge {
            font-size: 0.68rem;
            padding: 2px 7px;
            border-radius: 6px;
        }
        .theme-info-box p {
            font-size: 0.74rem;
            color: var(--text-muted);
            line-height: 1.35;
            margin: 0;
            font-weight: 600;
        }
        .theme-check-icon {
            font-size: 1.35rem;
            color: transparent;
            transition: color 0.2s;
        }
        .theme-card-option.active .theme-check-icon {
            color: var(--primary);
        }

        /* ═════════════════════════════════════════════════════════════════
           ⚠️ MOBILE FINANCIAL SECURITY & AUDIT MODALS
           ═════════════════════════════════════════════════════════════════ */
        .mobile-alert-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.88);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: mobModalFadeIn 0.25s ease-out;
        }
        @keyframes mobModalFadeIn {
            from { opacity: 0; transform: scale(0.92); }
            to { opacity: 1; transform: scale(1); }
        }
        .mobile-alert-card {
            background: #0f172a;
            border-radius: 24px;
            padding: 26px 20px;
            width: 100%;
            max-width: 380px;
            text-align: center;
            box-shadow: 0 25px 60px rgba(0,0,0,0.85);
            position: relative;
            overflow: hidden;
        }
        .mobile-alert-card.type-warning {
            border: 2px solid #f59e0b;
            box-shadow: 0 0 35px rgba(245, 158, 11, 0.35);
        }
        .mobile-alert-card.type-critical {
            border: 2px solid #ef4444;
            box-shadow: 0 0 40px rgba(239, 68, 68, 0.45);
        }
        .mob-alert-icon-ring {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            animation: mobPulseRing 2s infinite;
        }
        @keyframes mobPulseRing {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5); }
            70% { transform: scale(1.06); box-shadow: 0 0 0 16px rgba(239, 68, 68, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
        .mob-alert-title {
            font-size: 1.35rem;
            font-weight: 900;
            color: #ffffff;
            margin-bottom: 8px;
            font-family: 'Tajawal', sans-serif;
        }
        .mob-alert-body {
            font-size: 0.92rem;
            color: #cbd5e1;
            line-height: 1.6;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .mob-alert-btn {
            width: 100%;
            padding: 13px;
            border-radius: 14px;
            font-weight: 900;
            font-size: 1rem;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: 'Tajawal', sans-serif;
        }
    </style>
</head>
<body data-theme="<?php echo htmlspecialchars($activeTheme); ?>">

    <!-- 1. Sticky Mobile App Header -->
    <header class="mobile-header">
        <div class="brand-section">
            <div class="brand-avatar">
                <i class="fa-solid fa-gem"></i>
            </div>
            <div class="brand-meta">
                <h1 id="mobile-company-name"><?php echo htmlspecialchars($preloadedData['companyName']); ?></h1>
                <span id="mobile-user-greeting">مرحباً بك 🌟</span>
            </div>
        </div>
        <div class="header-actions">
            <!-- 🎨 Theme Trigger Button -->
            <button type="button" onclick="openBottomSheet('sheet-theme')" class="btn-switch-desktop btn-theme-trigger" title="اختيار سمة التباين والألوان">
                <i class="fa-solid fa-palette text-amber-400" id="current-theme-icon"></i>
                <span id="current-theme-label">السمة</span>
            </button>
            <!-- 🖥️ Switch to Full Desktop Dashboard Button -->
            <button type="button" onclick="switchToDesktopDashboard()" class="btn-switch-desktop" title="الانتقال لنسخة الكمبيوتر الكاملة">
                <i class="fa-solid fa-desktop"></i>
                <span>الكمبيوتر</span>
            </button>
            <!-- Fast Refresh Button -->
            <button type="button" onclick="refreshMobileData(true)" class="btn-switch-desktop" style="padding:7px 9px;" title="تحديث البيانات">
                <i class="fa-solid fa-arrows-rotate" id="refresh-icon"></i>
            </button>
        </div>
    </header>

    <!-- 2. Dual-Engine Segmented Pill Switcher -->
    <div class="mode-switcher-container">
        <div class="mode-segmented-pill">
            <button type="button" id="tab-btn-operations" class="mode-tab-btn active" onclick="switchMobileMode('operations')">
                <i class="fa-solid fa-bolt"></i>
                <span>⚡ العمليات السريعة</span>
            </button>
            <button type="button" id="tab-btn-analytics" class="mode-tab-btn" onclick="switchMobileMode('analytics')">
                <i class="fa-solid fa-chart-pie"></i>
                <span>📊 التقارير الذكية</span>
            </button>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════
         MODE 1: ⚡ العمليات السريعة (OPERATIONS MODE)
         ═════════════════════════════════════════════════════════════════ -->
    <main id="mode-operations-section" class="mobile-content">
        <!-- Real-Time Liquidity Pulse Card -->
        <div class="liquidity-card">
            <div class="liquidity-top">
                <span class="liquidity-title">
                    <i class="fa-solid fa-wallet text-sky-400"></i> إجمالي السيولة النقدية والبنكية اللحظية
                </span>
                <span style="font-size:0.75rem; background:rgba(16,185,129,0.18); border:1px solid #10b981; color:#10b981; padding:2px 8px; border-radius:20px; font-weight:800;">
                    مباشر ⚡
                </span>
            </div>
            <div class="liquidity-grand" id="mobile-total-liquidity">
                0 <small>SDG</small>
            </div>
            <div class="liquidity-breakdown" id="mobile-vaults-container">
                <!-- Dynamic Vaults Loaded Here -->
            </div>
        </div>

        <!-- 4 Power Quick Actions ($2x2 Grid) -->
        <div class="section-heading">
            <span>⚡ الإجراءات المالية السريعة</span>
            <small style="color:var(--text-muted); font-size:0.75rem;">لمسة واحدة للتنفيذ</small>
        </div>
        <div class="actions-2x2-grid">
            <!-- 1. Quick Financial Movement (حركة مالية - قيد مزدوج) -->
            <div class="power-action-btn act-receipt" onclick="openFinancialTxSheet()">
                <div class="action-icon-circle">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                </div>
                <div>
                    <div class="action-title">حركة مالية</div>
                    <div class="action-subtitle">من حساب إلى حساب (دائن / مدين)</div>
                </div>
            </div>

            <!-- 2. AI Smart Slip Parser (تفريغ إشعار ذكي بالذكاء الاصطناعي) -->
            <div class="power-action-btn act-ai" onclick="openBottomSheet('sheet-ai-slip')">
                <div class="action-icon-circle">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <div>
                    <div class="action-title">تفريغ إشعار (AI)</div>
                    <div class="action-subtitle">إشعار بنكك / واتساب</div>
                </div>
            </div>

            <!-- 3. Quick Sales Invoice (فاتورة مبيعات) -->
            <div class="power-action-btn act-pos" onclick="openBottomSheet('sheet-sale')">
                <div class="action-icon-circle">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div>
                    <div class="action-title">فاتورة بيع</div>
                    <div class="action-subtitle">بيع فوري + واتساب</div>
                </div>
            </div>

            <!-- 4. Quick Customer Ledger (كشف حساب عميل) -->
            <div class="power-action-btn act-statement" onclick="openBottomSheet('sheet-statement')">
                <div class="action-icon-circle">
                    <i class="fa-solid fa-address-book"></i>
                </div>
                <div>
                    <div class="action-title">كشف حساب</div>
                    <div class="action-subtitle">رصيد ومطالبة واتساب</div>
                </div>
            </div>
        </div>

        <!-- Real Recent Activity Feed -->
        <div class="section-heading">
            <span>🕒 سجل العمليات المباشرة (اليوم)</span>
            <small id="mobile-tx-count" style="color:var(--text-muted); font-size:0.75rem;">0 حركة</small>
        </div>
        <div id="mobile-activity-feed">
            <!-- Dynamic Feed Loaded Here -->
        </div>
    </main>

    <!-- ═════════════════════════════════════════════════════════════════
         MODE 2: 📊 التقارير الذكية (EXECUTIVE ANALYTICS MODE)
         ═════════════════════════════════════════════════════════════════ -->
    <main id="mode-analytics-section" class="mobile-content" style="display:none;">
        <!-- Executive KPI Matrix -->
        <div class="analytics-grid">
            <div class="kpi-card">
                <div class="kpi-label">
                    <i class="fa-solid fa-chart-line text-sky-400"></i> مبيعات اليوم
                </div>
                <div class="kpi-value" id="kpi-today-sales" style="color:var(--primary);">0</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">
                    <i class="fa-solid fa-arrow-down text-emerald-400"></i> تحصيلات اليوم
                </div>
                <div class="kpi-value" id="kpi-today-receipts" style="color:var(--emerald);">0</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">
                    <i class="fa-solid fa-arrow-up text-rose-400"></i> منصرفات اليوم
                </div>
                <div class="kpi-value" id="kpi-today-payments" style="color:var(--rose);">0</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">
                    <i class="fa-solid fa-scale-balanced text-amber-400"></i> صافي التدفق
                </div>
                <div class="kpi-value" id="kpi-today-net" style="color:var(--amber);">0</div>
            </div>
        </div>

        <!-- Top Debtors Radar (العملاء الأكثر مديونية) -->
        <div class="radar-card">
            <div class="radar-title">
                <span><i class="fa-solid fa-triangle-exclamation text-rose-400"></i> أعلى 5 عملاء مديونية (مطلوب سدادهم)</span>
            </div>
            <div id="mobile-debtors-container">
                <!-- Dynamic Debtors Rendered Here -->
            </div>
        </div>

        <!-- Low Stock Radar (المخزون الحرج) -->
        <div class="radar-card">
            <div class="radar-title">
                <span><i class="fa-solid fa-boxes-stacked text-amber-400"></i> رادار المخزون الحرج (أوشك على النفاد)</span>
            </div>
            <div id="mobile-low-stock-container">
                <!-- Dynamic Low Stock Rendered Here -->
            </div>
        </div>
    </main>

    <!-- ═════════════════════════════════════════════════════════════════
         BOTTOM SHEETS (POWER ACTION MODALS)
         ═════════════════════════════════════════════════════════════════ -->

    <!-- ═════════════════════════════════════════════════════════════════
         SHEET 1: 💸 حركة مالية موحدة (قيد مزدوج: من حساب إلى حساب)
         ═════════════════════════════════════════════════════════════════ -->
    <div class="mobile-sheet-overlay" id="overlay-sheet-financial-tx" onclick="closeBottomSheet('sheet-financial-tx')"></div>
    <div class="mobile-bottom-sheet" id="sheet-financial-tx" style="max-height:94vh;">
        <div class="sheet-drag-handle"></div>
        <div class="sheet-header">
            <span class="sheet-title" id="tx-sheet-title" style="color:var(--primary); font-weight:900;">
                <i class="fa-solid fa-money-bill-transfer"></i> حركة مالية (قيد مزدوج)
            </span>
            <div style="display:flex; align-items:center; gap:8px;">
                <button type="button" class="btn-switch-desktop" onclick="openBottomSheet('sheet-ai-slip')" style="padding:5px 10px; font-size:0.78rem; background:rgba(56,189,248,0.15); border:1px solid #38bdf8; color:#38bdf8;" title="تفريغ إشعار بالذكاء الاصطناعي">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> تفريغ إشعار AI
                </button>
                <button type="button" class="btn-sheet-close" onclick="closeBottomSheet('sheet-financial-tx')">✕</button>
            </div>
        </div>
        <div class="sheet-body">
            <p style="font-size:0.8rem; color:var(--text-muted); font-weight:700; margin:2px 0 10px 0;">
                حدد الحساب الدافع (من) والحساب المستلم (إلى) والمبلغ، وسيقوم النظام بتوجيه القيد دفترياً آلياً:
            </p>

            <form onsubmit="handleFinancialTxSubmit(event)">
                <input type="hidden" id="tx-operation-type" value="financial_movement">

                <!-- 1. Amount Field with Thousands Preview -->
                <div class="m-form-group">
                    <label class="m-label" style="display:flex; justify-content:space-between;">
                        <span>المبلغ المالي (SDG):</span>
                        <span id="tx-amount-preview" style="color:var(--emerald); font-family:'Inter',sans-serif; font-size:0.9rem; font-weight:900;">0 SDG</span>
                    </label>
                    <input type="number" id="tx-amount" class="m-input huge-number" placeholder="0.00" step="any" required inputmode="decimal" oninput="updateTxAmountPreview(this.value)">
                </div>

                <!-- 2. Dual Account Box (من حساب إلى حساب) -->
                <div class="account-selector-container">
                    <!-- 🔴 FROM ACCOUNT (الصارف / المدفوع منه) -->
                    <div class="box-account-picker box-account-from" onclick="openAccountPicker('from')">
                        <div class="box-acc-left">
                            <span class="box-acc-tag">
                                <i class="fa-solid fa-arrow-up-from-bracket"></i> من حساب (الصارف / الدافع / الدائن):
                            </span>
                            <div class="box-acc-title" id="display-from-name">-- اضغط لاختيار الحساب الدافع --</div>
                            <div class="box-acc-meta" id="display-from-meta">
                                <span>اختر عميلاً أو خزينة أو بنكاً</span>
                            </div>
                        </div>
                        <div class="box-acc-right">
                            <span class="acc-badge" id="display-from-badge" style="display:none;"></span>
                            <i class="fa-solid fa-chevron-left" style="font-size:0.85rem;"></i>
                        </div>
                    </div>
                    <input type="hidden" id="tx-from-id" required>
                    <input type="hidden" id="tx-from-name" required>
                    <input type="hidden" id="tx-from-type" required>
                    <input type="hidden" id="tx-from-phone">

                    <!-- SWAP BUTTON -->
                    <button type="button" class="btn-swap-accounts" onclick="swapTxAccounts()" title="تبديل الحسابين">
                        <i class="fa-solid fa-arrow-up-down"></i>
                    </button>

                    <!-- 🟢 TO ACCOUNT (المستلم / المقبوض إليه) -->
                    <div class="box-account-picker box-account-to" onclick="openAccountPicker('to')">
                        <div class="box-acc-left">
                            <span class="box-acc-tag">
                                <i class="fa-solid fa-arrow-down-to-bracket"></i> إلى حساب (المستلم / المستفيد / المدين):
                            </span>
                            <div class="box-acc-title" id="display-to-name">-- اضغط لاختيار الحساب المستلم --</div>
                            <div class="box-acc-meta" id="display-to-meta">
                                <span>اختر بنكاً أو خزينة أو عميلاً أو مورداً</span>
                            </div>
                        </div>
                        <div class="box-acc-right">
                            <span class="acc-badge" id="display-to-badge" style="display:none;"></span>
                            <i class="fa-solid fa-chevron-left" style="font-size:0.85rem;"></i>
                        </div>
                    </div>
                    <input type="hidden" id="tx-to-id" required>
                    <input type="hidden" id="tx-to-name" required>
                    <input type="hidden" id="tx-to-type" required>
                    <input type="hidden" id="tx-to-phone">
                </div>

                <!-- 3. Transaction Date & Slip Ref with 6 Digits & Duplicate Constraints -->
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <div class="m-form-group">
                        <label class="m-label">تاريخ الحركة: <span style="color:#ef4444;">*</span></label>
                        <input type="date" id="tx-date" class="m-input" required>
                    </div>
                    <div class="m-form-group">
                        <label class="m-label" style="display:flex; justify-content:space-between;">
                            <span>رقم التحويلة / الإشعار:</span>
                            <span style="font-size:0.7rem; color:var(--text-muted); font-weight:700;">(6 أرقام فأكثر)</span>
                        </label>
                        <input type="text" id="tx-ref" class="m-input" placeholder="مثال: 9481023" style="font-family:'Inter',monospace; font-weight:800; letter-spacing:1px;" autocomplete="off" oninput="validateRefNoLive(this.value)">
                    </div>
                </div>
                <div id="tx-ref-validation-hint" style="font-size:0.75rem; font-weight:800; margin-top:-6px; margin-bottom:10px; display:none; padding:4px 10px; border-radius:8px;"></div>

                <!-- 4. Notes / Statement -->
                <div class="m-form-group">
                    <label class="m-label">البيان / ملاحظات السند:</label>
                    <input type="text" id="tx-notes" class="m-input" placeholder="دفعة حساب / تحويل بنكي / منصرف">
                </div>

                <!-- 5. Submit Buttons -->
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <button type="submit" class="m-btn-submit" id="btn-save-tx" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="fa-solid fa-check"></i> حفظ الحركة المالية
                    </button>
                    <button type="button" class="m-btn-submit" onclick="handleTxWhatsAppShareClick()" style="background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 4px 15px rgba(34,197,94,0.35);">
                        <i class="fa-brands fa-whatsapp"></i> حفظ ومشاركة عبر واتساب 💬
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════
         SHEET: 🔍 شاشة البحث واختيار الحسابات الشاملة (من / إلى)
         ═════════════════════════════════════════════════════════════════ -->
    <div class="mobile-sheet-overlay" id="overlay-sheet-account-picker" onclick="closeBottomSheet('sheet-account-picker')"></div>
    <div class="mobile-bottom-sheet" id="sheet-account-picker" style="max-height:94vh;">
        <div class="sheet-drag-handle"></div>
        <div class="sheet-header">
            <span class="sheet-title" style="color:var(--text-main);">
                <i class="fa-solid fa-magnifying-glass"></i> <span id="picker-modal-heading">اختر الحساب</span>
            </span>
            <button type="button" class="btn-sheet-close" onclick="closeBottomSheet('sheet-account-picker')">✕</button>
        </div>
        <div class="sheet-body" style="padding-top:8px;">
            <!-- Sticky Search & Filter Category Pills -->
            <div class="picker-search-bar">
                <div style="position:relative; margin-bottom:8px;">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute; right:14px; top:14px; color:var(--text-muted); font-size:0.95rem;"></i>
                    <input type="text" id="picker-search-input" class="m-input" style="padding-right:40px;" placeholder="ابحث بالاسم، الهاتف، أو نوع الحساب..." oninput="filterAccountsList()" autocomplete="off">
                </div>
                <div class="picker-category-pills">
                    <button type="button" class="cat-pill active" onclick="setPickerCategory('all', this)">الكل</button>
                    <button type="button" class="cat-pill" onclick="setPickerCategory('bank', this)">🏦 البنوك</button>
                    <button type="button" class="cat-pill" onclick="setPickerCategory('safe', this)">💵 الخزن</button>
                    <button type="button" class="cat-pill" onclick="setPickerCategory('customer', this)">👤 العملاء</button>
                    <button type="button" class="cat-pill" onclick="setPickerCategory('supplier', this)">🏢 الموردين</button>
                    <button type="button" class="cat-pill" onclick="setPickerCategory('expense', this)">📉 المصروفات</button>
                </div>
            </div>

            <!-- List of Filtered Accounts -->
            <div id="picker-accounts-container">
                <!-- Dynamically Rendered Account Items -->
            </div>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════
         SHEET: 🤖 المساعد الذكي لتفريغ إشعارات بنكك والواتساب (AI)
         ═════════════════════════════════════════════════════════════════ -->
    <div class="mobile-sheet-overlay" id="overlay-sheet-ai-slip" onclick="closeBottomSheet('sheet-ai-slip')"></div>
    <div class="mobile-bottom-sheet" id="sheet-ai-slip">
        <div class="sheet-drag-handle"></div>
        <div class="sheet-header">
            <span class="sheet-title" style="color:var(--primary);">
                <i class="fa-solid fa-wand-magic-sparkles"></i> المساعد الذكي لتفريغ الإشعارات
            </span>
            <button type="button" class="btn-sheet-close" onclick="closeBottomSheet('sheet-ai-slip')">✕</button>
        </div>
        <div class="sheet-body">
            <p style="font-size:0.82rem; color:var(--text-muted); font-weight:700; margin-bottom:10px;">
                انسخ رسالة إشعار البنك (بنكك، فوري، أوكاش) أو رسالة الواتساب والصقها هنا، وسيقوم الذكاء الاصطناعي باستخراج كافة البيانات وتعبئة السند آلياً:
            </p>
            
            <div style="position:relative; margin-bottom:8px;">
                <textarea id="ai-slip-text" class="ai-slip-textarea" placeholder="الصق نص الإشعار هنا..." oninput="parseSlipInputLive(this.value)"></textarea>
            </div>

            <div style="display:flex; gap:8px; margin-bottom:12px;">
                <button type="button" class="btn-switch-desktop" onclick="pasteFromClipboardToAI()" style="flex:1; padding:8px 12px; font-size:0.8rem; background:rgba(56,189,248,0.15); border:1px solid #38bdf8; color:#38bdf8;">
                    <i class="fa-solid fa-paste"></i> لصق من الحافظة
                </button>
                <button type="button" class="btn-switch-desktop" onclick="loadSampleBankakSlip()" style="padding:8px 12px; font-size:0.8rem; background:rgba(16,185,129,0.15); border:1px solid #10b981; color:#10b981;">
                    <i class="fa-solid fa-flask"></i> إشعار تجريبي
                </button>
            </div>

            <!-- Live Extracted Preview Chips -->
            <div id="ai-parsed-preview" style="display:none;">
                <div style="font-size:0.84rem; font-weight:900; color:var(--emerald); margin-bottom:6px;">
                    ✨ البيانات المستخرجة بنجاح:
                </div>
                <div class="ai-preview-grid">
                    <div class="ai-chip">
                        <span class="ai-chip-label">💰 المبلغ:</span>
                        <div class="ai-chip-val" id="ai-chip-amount" style="color:var(--emerald);">0 SDG</div>
                    </div>
                    <div class="ai-chip">
                        <span class="ai-chip-label">🔖 رقم الإشعار / المرجع:</span>
                        <div class="ai-chip-val" id="ai-chip-ref">-</div>
                    </div>
                    <div class="ai-chip">
                        <span class="ai-chip-label">🔴 الصارف (من حساب):</span>
                        <div class="ai-chip-val" id="ai-chip-from" style="color:#ef4444;">-</div>
                    </div>
                    <div class="ai-chip">
                        <span class="ai-chip-label">🟢 المستلم (إلى حساب):</span>
                        <div class="ai-chip-val" id="ai-chip-to" style="color:#10b981;">-</div>
                    </div>
                    <div class="ai-chip" style="grid-column: span 2;">
                        <span class="ai-chip-label">📅 التاريخ والوقت:</span>
                        <div class="ai-chip-val" id="ai-chip-date">-</div>
                    </div>
                </div>
                <button type="button" class="m-btn-submit" onclick="applyAIExtractedData()" style="background: linear-gradient(135deg, var(--primary), #0284c7); margin-top:8px;">
                    <i class="fa-solid fa-arrow-down-short-wide"></i> تطبيق وتعبئة السند فوراً
                </button>
            </div>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════
         SHEET: 💬 نافذة اختيار مستلم إشعار الواتساب (من / إلى / مخصص)
         ═════════════════════════════════════════════════════════════════ -->
    <div class="mobile-sheet-overlay" id="overlay-sheet-whatsapp-target" onclick="closeBottomSheet('sheet-whatsapp-target')"></div>
    <div class="mobile-bottom-sheet" id="sheet-whatsapp-target">
        <div class="sheet-drag-handle"></div>
        <div class="sheet-header">
            <span class="sheet-title" style="color:#22c55e;">
                <i class="fa-brands fa-whatsapp"></i> مشاركة السند عبر واتساب
            </span>
            <button type="button" class="btn-sheet-close" onclick="closeBottomSheet('sheet-whatsapp-target')">✕</button>
        </div>
        <div class="sheet-body">
            <p style="font-size:0.84rem; color:var(--text-muted); font-weight:700; margin-bottom:12px;">
                حدد الطرف الذي ترغب في إرسال الإشعار له عبر واتساب:
            </p>

            <!-- Option 1: Payer (من حساب) -->
            <div class="wa-target-option" onclick="sendVoucherWhatsApp('from')">
                <div class="wa-opt-icon" style="background:rgba(239,68,68,0.15); color:#ef4444; border:1px solid #ef4444;">
                    <i class="fa-solid fa-arrow-up-from-bracket"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:0.75rem; color:#ef4444; font-weight:800;">إرسال إلى الحساب الصارف (من):</div>
                    <div style="font-size:1rem; font-weight:900; color:var(--text-main);" id="wa-from-name">--</div>
                    <div style="font-size:0.75rem; color:var(--text-muted);" id="wa-from-phone">هاتف: غير مسجل</div>
                </div>
                <i class="fa-brands fa-whatsapp" style="color:#22c55e; font-size:1.3rem;"></i>
            </div>

            <!-- Option 2: Payee (إلى حساب) -->
            <div class="wa-target-option" onclick="sendVoucherWhatsApp('to')">
                <div class="wa-opt-icon" style="background:rgba(16,185,129,0.15); color:#10b981; border:1px solid #10b981;">
                    <i class="fa-solid fa-arrow-down-to-bracket"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:0.75rem; color:#10b981; font-weight:800;">إرسال إلى الحساب المستلم (إلى):</div>
                    <div style="font-size:1rem; font-weight:900; color:var(--text-main);" id="wa-to-name">--</div>
                    <div style="font-size:0.75rem; color:var(--text-muted);" id="wa-to-phone">هاتف: غير مسجل</div>
                </div>
                <i class="fa-brands fa-whatsapp" style="color:#22c55e; font-size:1.3rem;"></i>
            </div>

            <!-- Option 3: Custom Phone -->
            <div class="wa-target-option" onclick="sendVoucherWhatsApp('custom')">
                <div class="wa-opt-icon" style="background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid #38bdf8;">
                    <i class="fa-solid fa-mobile-screen"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:0.75rem; color:#38bdf8; font-weight:800;">إرسال إلى رقم واتساب مخصص:</div>
                    <div style="font-size:0.95rem; font-weight:900; color:var(--text-main);">اختيار أي جهة اتصال في واتساب</div>
                </div>
                <i class="fa-brands fa-whatsapp" style="color:#22c55e; font-size:1.3rem;"></i>
            </div>
        </div>
    </div>

    <!-- SHEET 3: فاتورة بيع سريعة -->
    <div class="mobile-sheet-overlay" id="overlay-sheet-sale" onclick="closeBottomSheet('sheet-sale')"></div>
    <div class="mobile-bottom-sheet" id="sheet-sale">
        <div class="sheet-drag-handle"></div>
        <div class="sheet-header">
            <span class="sheet-title" style="color:var(--primary);">
                <i class="fa-solid fa-receipt"></i> إصدار فاتورة بيع فورية
            </span>
            <button type="button" class="btn-sheet-close" onclick="closeBottomSheet('sheet-sale')">✕</button>
        </div>
        <div class="sheet-body">
            <form onsubmit="handleQuickSaleSubmit(event)">
                <div class="m-form-group">
                    <label class="m-label">العميل:</label>
                    <select id="sale-customer" class="m-select">
                        <option value="walkin">عميل نقدي عام (Walk-in)</option>
                    </select>
                </div>
                <div class="m-form-group">
                    <label class="m-label">الصنف المراد بيعه:</label>
                    <select id="sale-item" class="m-select" onchange="handleItemSelect(this)" required>
                        <option value="">-- اختر الصنف --</option>
                    </select>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <div class="m-form-group">
                        <label class="m-label">الكمية المباعة:</label>
                        <input type="number" id="sale-qty" class="m-input" value="1" min="1" step="any" oninput="calculateSaleTotal()" required inputmode="numeric">
                    </div>
                    <div class="m-form-group">
                        <label class="m-label">سعر الوحدة (SDG):</label>
                        <input type="number" id="sale-price" class="m-input" placeholder="0.00" oninput="calculateSaleTotal()" required inputmode="decimal">
                    </div>
                </div>
                <div class="m-form-group">
                    <label class="m-label">إجمالي الفاتورة الصافي:</label>
                    <div id="sale-total-display" style="font-size:1.6rem; font-weight:900; color:var(--emerald); background:var(--surface-sub); padding:10px; border-radius:12px; text-align:center; border:1px solid var(--border-glass); font-family:'Inter', sans-serif;">
                        0 SDG
                    </div>
                </div>
                <div class="m-form-group">
                    <label class="m-label">طريقة السداد:</label>
                    <select id="sale-payment-method" class="m-select">
                        <option value="cash">نقداً (كاش)</option>
                        <option value="bankak">تحويل بنكك</option>
                        <option value="credit">آجل على الحساب (دين)</option>
                    </select>
                </div>
                <button type="submit" class="m-btn-submit" style="background: linear-gradient(135deg, var(--primary), #0284c7);">
                    <i class="fa-solid fa-print"></i> حفظ الفاتورة وتوليد إشعار واتساب 💬
                </button>
            </form>
        </div>
    </div>

    <!-- SHEET 4: كشف حساب عميل ومطالبة -->
    <div class="mobile-sheet-overlay" id="overlay-sheet-statement" onclick="closeBottomSheet('sheet-statement')"></div>
    <div class="mobile-bottom-sheet" id="sheet-statement">
        <div class="sheet-drag-handle"></div>
        <div class="sheet-header">
            <span class="sheet-title" style="color:var(--purple);">
                <i class="fa-solid fa-address-book"></i> رصيد العميل ومطالبة واتساب
            </span>
            <button type="button" class="btn-sheet-close" onclick="closeBottomSheet('sheet-statement')">✕</button>
        </div>
        <div class="sheet-body">
            <div class="m-form-group">
                <label class="m-label">اختر العميل:</label>
                <select id="statement-customer-select" class="m-select" onchange="renderCustomerMiniStatement(this.value)">
                    <option value="">-- اختر عميلاً لعرض رصيده الفعلي --</option>
                </select>
            </div>
            
            <div id="statement-result-card" style="display:none;">
                <div style="background:var(--surface-sub); border:2px solid var(--border-glass); border-radius:16px; padding:16px; text-align:center; margin-bottom:14px;">
                    <span style="font-size:0.8rem; color:var(--text-muted); font-weight:700;">الرصيد المتبقي على العميل:</span>
                    <div id="cust-balance-amt" style="font-size:2rem; font-weight:900; color:var(--rose); font-family:'Inter', sans-serif; margin:4px 0;">
                        0.00
                    </div>
                    <span id="cust-balance-status" style="font-size:0.84rem; font-weight:800; color:var(--text-main);">مدين للشركة</span>
                </div>

                <div class="m-form-group" style="display:flex; gap:8px;">
                    <button type="button" onclick="sendWhatsAppClaim()" style="flex:1; background:#22c55e; color:#fff; border:none; padding:13px; border-radius:12px; font-weight:900; font-size:0.95rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 4px 15px rgba(34,197,94,0.3);">
                        <i class="fa-brands fa-whatsapp" style="font-size:1.2rem;"></i> إرسال مطالبة بالواتساب
                    </button>
                    <button type="button" onclick="openFullStatementInDesktop()" style="background:var(--surface-2); color:var(--text-main); border:1.5px solid var(--border-glass); padding:12px 14px; border-radius:12px; font-weight:800; cursor:pointer;">
                        الكشف الكامل 📄
                    </button>
                </div>

                <div style="margin-top:14px;">
                    <span style="font-size:0.84rem; font-weight:800; color:var(--text-main); margin-bottom:8px; display:block;">آخر المعاملات المسجلة للعميل:</span>
                    <div id="cust-recent-txs" style="font-size:0.84rem; color:var(--text-muted);"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- SHEET 5: 🎨 سمات التباين والراحة البصرية -->
    <div class="mobile-sheet-overlay" id="overlay-sheet-theme" onclick="closeBottomSheet('sheet-theme')"></div>
    <div class="mobile-bottom-sheet" id="sheet-theme">
        <div class="sheet-drag-handle"></div>
        <div class="sheet-header">
            <span class="sheet-title" style="color:var(--amber);">
                <i class="fa-solid fa-palette"></i> سمات التباين والراحة البصرية
            </span>
            <button type="button" class="btn-sheet-close" onclick="closeBottomSheet('sheet-theme')">✕</button>
        </div>
        <div class="sheet-body" style="padding-bottom: 24px;">
            <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 14px; font-weight: 700;">
                اختر السمة المناسبة لظروف إضاءتك وحركتك الحالية:
            </p>
            
            <div class="theme-options-grid" style="display: flex; flex-direction: column; gap: 10px;">
                <!-- Theme 1: Solar -->
                <div class="theme-card-option" id="theme-opt-solar" onclick="selectTheme('solar')">
                    <div class="theme-swatch-box" style="background: #ffffff; border: 2px solid #cbd5e1;">
                        <span style="background: #020617; width: 12px; height: 12px; border-radius: 50%;"></span>
                        <span style="background: #0284c7; width: 12px; height: 12px; border-radius: 50%;"></span>
                        <span style="background: #059669; width: 12px; height: 12px; border-radius: 50%;"></span>
                    </div>
                    <div class="theme-info-box">
                        <div class="theme-title-row">
                            <strong>☀️ نهار وشمس ساطعة (Solar White)</strong>
                            <span class="theme-badge" style="background: #e2e8f0; color: #020617; font-weight:900;">تباين 21:1 | ضد التوهج</span>
                        </div>
                        <p>خلفية بيضاء ناصعة مع خطوط سوداء فاحمة مخصصة للقراءة تحت أشعة الشمس المباشرة وتمنع انعكاس زجاج السيارة تماماً.</p>
                    </div>
                    <i class="fa-solid fa-circle-check theme-check-icon"></i>
                </div>

                <!-- Theme 2: Midnight OLED -->
                <div class="theme-card-option" id="theme-opt-midnight" onclick="selectTheme('midnight')">
                    <div class="theme-swatch-box" style="background: #000000; border: 2px solid rgba(255,255,255,0.25);">
                        <span style="background: #ffffff; width: 12px; height: 12px; border-radius: 50%;"></span>
                        <span style="background: #38bdf8; width: 12px; height: 12px; border-radius: 50%;"></span>
                        <span style="background: #10b981; width: 12px; height: 12px; border-radius: 50%;"></span>
                    </div>
                    <div class="theme-info-box">
                        <div class="theme-title-row">
                            <strong>🌙 ليل أسود فاحم (OLED Midnight)</strong>
                            <span class="theme-badge" style="background: rgba(56, 189, 248, 0.2); color: #38bdf8; font-weight:900;">سواد 100% | حماية العين</span>
                        </div>
                        <p>سواد مطبق خالٍ من أي إشعاع زائد لحماية شبكية العين في الغرف المظلمة وتوفير طاقة بطارية الهاتف لأقصى حد.</p>
                    </div>
                    <i class="fa-solid fa-circle-check theme-check-icon"></i>
                </div>

                <!-- Theme 3: Executive Navy & Gold -->
                <div class="theme-card-option" id="theme-opt-navy" onclick="selectTheme('navy')">
                    <div class="theme-swatch-box" style="background: #030a1a; border: 2px solid rgba(251, 191, 36, 0.45);">
                        <span style="background: #fbbf24; width: 12px; height: 12px; border-radius: 50%;"></span>
                        <span style="background: #ffffff; width: 12px; height: 12px; border-radius: 50%;"></span>
                        <span style="background: #38bdf8; width: 12px; height: 12px; border-radius: 50%;"></span>
                    </div>
                    <div class="theme-info-box">
                        <div class="theme-title-row">
                            <strong>🧠 التركيز المالي السيادي (Executive Navy)</strong>
                            <span class="theme-badge" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; font-weight:900;">هدوء وتركيز مصرفي</span>
                        </div>
                        <p>كحلي مصرفي ملكي يقلل التوتر ويرفع التركيز، مع أرقام ذهبية مشعة تبرز المبالغ والديون فوراً للعين.</p>
                    </div>
                    <i class="fa-solid fa-circle-check theme-check-icon"></i>
                </div>

                <!-- Theme 4: Forest Emerald & Mint -->
                <div class="theme-card-option" id="theme-opt-forest" onclick="selectTheme('forest')">
                    <div class="theme-swatch-box" style="background: #03140d; border: 2px solid rgba(52, 211, 153, 0.4);">
                        <span style="background: #34d399; width: 12px; height: 12px; border-radius: 50%;"></span>
                        <span style="background: #ffffff; width: 12px; height: 12px; border-radius: 50%;"></span>
                        <span style="background: #10b981; width: 12px; height: 12px; border-radius: 50%;"></span>
                    </div>
                    <div class="theme-info-box">
                        <div class="theme-title-row">
                            <strong>🌿 الراحة واليقظة الممتدة (Forest Emerald)</strong>
                            <span class="theme-badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399; font-weight:900;">صفر إجهاد | عمل متواصل</span>
                        </div>
                        <p>الطول الموجي الأخضر الأريح لعدسة العين علمياً؛ ممتاز للمراجعة والتدقيق والعمل المتواصل دون إرهاق بصري.</p>
                    </div>
                    <i class="fa-solid fa-circle-check theme-check-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════
         5. FIXED BOTTOM NAVIGATION BAR
         ═════════════════════════════════════════════════════════════════ -->
    <nav class="bottom-nav-bar">
        <button type="button" class="nav-item active" id="bnav-operations" onclick="switchMobileMode('operations')">
            <i class="fa-solid fa-bolt"></i>
            <span>العمليات</span>
        </button>
        <button type="button" class="nav-item" id="bnav-analytics" onclick="switchMobileMode('analytics')">
            <i class="fa-solid fa-chart-pie"></i>
            <span>التقارير</span>
        </button>
        <button type="button" class="nav-item" onclick="openBottomSheet('sheet-sale')">
            <i class="fa-solid fa-receipt"></i>
            <span>فاتورة</span>
        </button>
        <button type="button" class="nav-item" onclick="openBottomSheet('sheet-theme')">
            <i class="fa-solid fa-palette"></i>
            <span>السمات</span>
        </button>
        <button type="button" class="nav-item" onclick="switchToDesktopDashboard()">
            <i class="fa-solid fa-desktop"></i>
            <span>الكمبيوتر</span>
        </button>
    </nav>

    <!-- ═════════════════════════════════════════════════════════════════
         CORE JAVASCRIPT APPLICATION ENGINE
         ═════════════════════════════════════════════════════════════════ -->
    <script>
        // Haptic Feedback Helper
        function triggerHaptic(type = 'light') {
            if (navigator.vibrate) {
                if (type === 'heavy') navigator.vibrate([25, 45, 25]);
                else navigator.vibrate(15);
            }
        }

        // ═════════════════════════════════════════════════════════════════
        // THEME MANAGER ENGINE
        // ═════════════════════════════════════════════════════════════════
        function selectTheme(themeKey) {
            triggerHaptic('medium');
            document.documentElement.setAttribute('data-theme', themeKey);
            document.body.setAttribute('data-theme', themeKey);
            localStorage.setItem('magaiees_mobile_theme', themeKey);
            document.cookie = "magaiees_mobile_theme=" + themeKey + "; path=/; max-age=31536000; SameSite=Lax";
            
            // Update browser status bar theme color
            const metaColors = {
                solar: '#f1f5f9',
                midnight: '#000000',
                navy: '#030a1a',
                forest: '#03140d'
            };
            const metaTag = document.querySelector('meta[name="theme-color"]');
            if (metaTag && metaColors[themeKey]) {
                metaTag.setAttribute('content', metaColors[themeKey]);
            }

            updateThemeUI(themeKey);
            setTimeout(() => {
                closeBottomSheet('sheet-theme');
            }, 250);
        }

        function updateThemeUI(themeKey) {
            document.querySelectorAll('.theme-card-option').forEach(el => el.classList.remove('active'));
            const activeCard = document.getElementById('theme-opt-' + themeKey);
            if (activeCard) activeCard.classList.add('active');

            const iconMap = {
                solar: 'fa-sun text-amber-500',
                midnight: 'fa-moon text-sky-400',
                navy: 'fa-brain text-amber-400',
                forest: 'fa-leaf text-emerald-400'
            };
            const labelMap = {
                solar: 'شمس',
                midnight: 'ليل',
                navy: 'كحلي',
                forest: 'زمرد'
            };
            const iconEl = document.getElementById('current-theme-icon');
            const labelEl = document.getElementById('current-theme-label');
            if (iconEl && iconMap[themeKey]) iconEl.className = 'fa-solid ' + iconMap[themeKey];
            if (labelEl && labelMap[themeKey]) labelEl.textContent = labelMap[themeKey];
        }

        function initTheme() {
            const savedTheme = localStorage.getItem('magaiees_mobile_theme') || document.body.getAttribute('data-theme') || 'midnight';
            document.documentElement.setAttribute('data-theme', savedTheme);
            document.body.setAttribute('data-theme', savedTheme);
            updateThemeUI(savedTheme);
        }

        // Switch to Full Desktop Dashboard & Persist Preference
        function switchToDesktopDashboard() {
            triggerHaptic('light');
            localStorage.setItem('magaiees_view_preference', 'desktop');
            document.cookie = "magaiees_view_preference=desktop; path=/; max-age=31536000; SameSite=Lax";
            window.location.href = 'magaiees_unified_dashboard.php?force_desktop=1';
        }

        // Dual Engine Mode Switcher
        function switchMobileMode(mode) {
            triggerHaptic('light');
            const opBtn = document.getElementById('tab-btn-operations');
            const anBtn = document.getElementById('tab-btn-analytics');
            const opSec = document.getElementById('mode-operations-section');
            const anSec = document.getElementById('mode-analytics-section');
            const bnavOp = document.getElementById('bnav-operations');
            const bnavAn = document.getElementById('bnav-analytics');

            if (mode === 'operations') {
                opBtn.classList.add('active');
                anBtn.classList.remove('active');
                opSec.style.display = 'block';
                anSec.style.display = 'none';
                bnavOp.classList.add('active');
                bnavAn.classList.remove('active');
            } else {
                anBtn.classList.add('active');
                opBtn.classList.remove('active');
                anSec.style.display = 'block';
                opSec.style.display = 'none';
                bnavAn.classList.add('active');
                bnavOp.classList.remove('active');
                renderAnalyticsKPIs();
            }
        }

        // Bottom Sheets Handling
        function openBottomSheet(sheetId) {
            triggerHaptic('light');
            const sheet = document.getElementById(sheetId);
            const overlay = document.getElementById('overlay-' + sheetId);
            if (sheet && overlay) {
                overlay.classList.add('active');
                sheet.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeBottomSheet(sheetId) {
            triggerHaptic('light');
            const sheet = document.getElementById(sheetId);
            const overlay = document.getElementById('overlay-' + sheetId);
            if (sheet && overlay) {
                sheet.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        // Formats
        function fmt(n) {
            return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        }

        // ═════════════════════════════════════════════════════════════════
        // LIVE DATA ENGINE & DATABASE SYNC
        // ═════════════════════════════════════════════════════════════════
        let M_CUSTOMERS = [];
        let M_ITEMS = [];
        let M_VAULTS = [];
        let M_TRANSACTIONS = [];
        let activeStatementCustomer = null;
        let ALL_ACCOUNTS = [];
        let CURRENT_PICKER_TARGET = 'from';
        let CURRENT_PICKER_CATEGORY = 'all';
        let ACTIVE_TX_FOR_WA = null;
        let SELECTED_TX_TYPE = 'receipt';
        let SELECTED_FROM_ACCOUNT = null;
        let SELECTED_TO_ACCOUNT = null;
        let AI_EXTRACTED_DATA = null;

        function getTenantKey(baseKey) {
            try {
                const u = JSON.parse(localStorage.getItem('user') || '{}');
                if (u.tenant_id && u.tenant_id !== 'default') {
                    return 'tenant_' + u.tenant_id + '_' + baseKey;
                }
            } catch(e){}
            return baseKey;
        }

        function loadTenantArray(baseKey) {
            const tKey = getTenantKey(baseKey);
            let raw = localStorage.getItem(tKey);
            if (!raw && tKey !== baseKey) {
                raw = localStorage.getItem(baseKey);
            }
            try {
                return JSON.parse(raw || '[]');
            } catch(e) {
                return [];
            }
        }

        function saveTenantArray(baseKey, arr) {
            const tKey = getTenantKey(baseKey);
            const str = JSON.stringify(arr);
            try {
                localStorage.setItem(tKey, str);
                localStorage.setItem(baseKey, str);
            } catch(e){}
        }

        function refreshMobileData(showToastAnim = false) {
            const icon = document.getElementById('refresh-icon');
            if (icon) icon.classList.add('fa-spin');

            // 1. User & Company
            try {
                const u = JSON.parse(localStorage.getItem('user') || '{}');
                if (u.fullName || u.username) {
                    document.getElementById('mobile-user-greeting').innerText = `مرحباً بك، ${u.fullName || u.username} ✨`;
                }
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                const compName = window.SERVER_DATA?.companyName || cfg.companyName || localStorage.getItem('companyName');
                if (compName) {
                    document.getElementById('mobile-company-name').innerText = compName;
                }
            } catch(e) {}

            // 2. Load Customers (Real DB First, Local Storage fallback)
            M_CUSTOMERS = [];
            if (window.SERVER_DATA?.customers && window.SERVER_DATA.customers.length > 0) {
                M_CUSTOMERS = window.SERVER_DATA.customers.map(c => ({
                    id: c.customer_id || c.id,
                    name: c.customer_name || c.name,
                    phone: c.phone || '',
                    balance: parseFloat(c.total_exposure ?? c.ledger_balance ?? c.currentBalance ?? 0)
                }));
            } else {
                const localCusts = loadTenantArray('erp_customers_v1');
                if (localCusts.length) {
                    M_CUSTOMERS = localCusts.map(c => ({
                        id: c.id,
                        name: c.name,
                        phone: c.phone || '',
                        balance: parseFloat(c.currentBalance || c.balance || 0)
                    }));
                }
            }

            // 3. Load Products & Items
            M_ITEMS = [];
            if (window.SERVER_DATA?.items && window.SERVER_DATA.items.length > 0) {
                M_ITEMS = window.SERVER_DATA.items.map(it => ({
                    id: it.id,
                    name: it.name,
                    price: parseFloat(it.salePrice || it.price || 0),
                    stock: parseFloat(it.quantity || it.stock || 0)
                }));
            } else {
                const localItems = loadTenantArray('erp_items_v1');
                if (localItems.length) {
                    M_ITEMS = localItems.map(it => ({
                        id: it.id,
                        name: it.name,
                        price: parseFloat(it.salePrice || it.price || 0),
                        stock: parseFloat(it.quantity || it.stock || 0)
                    }));
                }
            }

            // 4. Load Vaults & Liquidity
            M_VAULTS = [];
            if (window.SERVER_DATA?.vaults && window.SERVER_DATA.vaults.length > 0) {
                M_VAULTS = window.SERVER_DATA.vaults.map(v => ({
                    name: v.vault_name || v.name,
                    amount: parseFloat(v.amount || v.balance || 0),
                    color: v.color || '#10b981'
                }));
            } else {
                const localBanks = loadTenantArray('erp_banks_v1');
                if (localBanks.length) {
                    M_VAULTS = localBanks.map(b => ({
                        name: b.name,
                        amount: parseFloat(b.balance || 0),
                        color: b.color || '#38bdf8'
                    }));
                }
            }

            // Populate Customer Select Dropdowns
            ['receipt-customer', 'statement-customer-select', 'sale-customer'].forEach(id => {
                const sel = document.getElementById(id);
                if (sel) {
                    const curVal = sel.value;
                    let opts = (id === 'sale-customer') ? '<option value="walkin">عميل نقدي عام (Walk-in)</option>' : '<option value="">-- اختر العميل من قاعدة البيانات --</option>';
                    M_CUSTOMERS.forEach(c => {
                        opts += `<option value="${c.id}">${c.name} (${fmt(c.balance || 0)} SDG)</option>`;
                    });
                    sel.innerHTML = opts;
                    if (curVal) sel.value = curVal;
                }
            });

            // Populate Item Select Dropdown
            const itemSel = document.getElementById('sale-item');
            if (itemSel) {
                let opts = '<option value="">-- اختر الصنف من المستودع --</option>';
                M_ITEMS.forEach(it => {
                    opts += `<option value="${it.id}" data-price="${it.price}">${it.name} - ${fmt(it.price)} SDG</option>`;
                });
                itemSel.innerHTML = opts;
            }

            // Populate Account Select Dropdowns (Receipt Destination & Payment Source)
            ['receipt-destination', 'payment-source'].forEach(id => {
                const sel = document.getElementById(id);
                if (sel) {
                    const curVal = sel.value;
                    let opts = '';
                    if (M_VAULTS.length > 0) {
                        M_VAULTS.forEach(v => {
                            opts += `<option value="${v.name}">${v.name} (${fmt(v.amount)} SDG)</option>`;
                        });
                    } else {
                        opts = '<option value="خزنة الكاش">الخزينة الرئيسية</option><option value="بنكك">بنك الخرطوم (بنكك)</option>';
                    }
                    sel.innerHTML = opts;
                    if (curVal) sel.value = curVal;
                }
            });

            // Build Universal Chart of Accounts for Searchable Picker
            buildAllAccountsList();

            // Render Liquidity Pulse Numbers
            renderLiquidityDisplay();

            // Render Recent Live Transactions
            renderLiveFeed();

            // Background Async Sync with Database API
            triggerBackgroundSync();

            setTimeout(() => {
                if (icon) icon.classList.remove('fa-spin');
                if (showToastAnim && window.showModernToast) {
                    window.showModernToast('✅ تم تحديث بيانات الموبايل الحية بنجاح', 'success', 2000);
                }
            }, 350);
        }

        function renderLiquidityDisplay() {
            let total = 0;
            let vaultsHtml = '';
            if (M_VAULTS.length > 0) {
                M_VAULTS.forEach(v => {
                    total += v.amount;
                    vaultsHtml += `
                        <div class="vault-item">
                            <span class="vault-name">🏦 ${v.name}</span>
                            <span class="vault-amt">${fmt(v.amount)} SDG</span>
                        </div>
                    `;
                });
            } else {
                vaultsHtml = `
                    <div class="vault-item">
                        <span class="vault-name">💵 الخزينة</span>
                        <span class="vault-amt">0 SDG</span>
                    </div>
                    <div class="vault-item">
                        <span class="vault-name">🏦 بنكك</span>
                        <span class="vault-amt">0 SDG</span>
                    </div>
                `;
            }
            document.getElementById('mobile-total-liquidity').innerHTML = fmt(total) + ' <small>SDG</small>';
            document.getElementById('mobile-vaults-container').innerHTML = vaultsHtml;
        }

        function renderLiveFeed() {
            let txs = [];
            
            // 1. Unified Financial Movements
            const financialTxs = loadTenantArray('erp_financial_txs_v1');
            financialTxs.forEach(f => {
                let typeLabel = (f.type === 'payment') ? 'سند صرف' : ((f.type === 'transfer') ? 'تحويل بيني' : 'سند قبض');
                let parties = (f.fromAccount?.name || 'حساب') + ' ➜ ' + (f.toAccount?.name || 'حساب');
                txs.push({
                    type: f.type || 'receipt',
                    title: `${typeLabel} - ${parties}`,
                    amount: parseFloat(f.amount || 0),
                    time: f.date || f.createdAt || 'اليوم',
                    ref: f.refNo || f.id || 'TX'
                });
            });

            // 2. Combine receipts, payments, sales from server preloaded or local arrays
            const receipts = window.SERVER_DATA?.receipts || loadTenantArray('erp_single_receipts_v1');
            const payments = window.SERVER_DATA?.payments || loadTenantArray('erp_single_payments_v1');
            const sales = window.SERVER_DATA?.sales || loadTenantArray('erp_sales_invoices_v1');

            receipts.forEach(r => {
                const rRef = r.refNo || r.id || 'REC';
                if (!txs.some(t => t.ref === rRef)) {
                    txs.push({
                        type: 'receipt',
                        title: 'سند قبض - ' + (r.customerName || r.customer || 'عميل'),
                        amount: parseFloat(r.amount || 0),
                        time: r.date || r.createdAt || 'اليوم',
                        ref: rRef
                    });
                }
            });

            payments.forEach(p => {
                const pRef = p.refNo || p.id || 'PAY';
                if (!txs.some(t => t.ref === pRef)) {
                    txs.push({
                        type: 'payment',
                        title: 'سند صرف - ' + (p.recipient || p.party || 'مصروف'),
                        amount: parseFloat(p.amount || 0),
                        time: p.date || p.createdAt || 'اليوم',
                        ref: pRef
                    });
                }
            });

            sales.forEach(s => {
                txs.push({
                    type: 'sale',
                    title: 'فاتورة بيع - ' + (s.customerName || 'عميل نقدي'),
                    amount: parseFloat(s.grandTotal || s.total || 0),
                    time: s.date || s.createdAt || 'اليوم',
                    ref: s.invoiceNo || s.id || 'INV'
                });
            });

            document.getElementById('mobile-tx-count').innerText = `${txs.length} حركة مسجلة`;

            let feedHtml = '';
            if (txs.length === 0) {
                feedHtml = `
                    <div style="text-align:center; padding:30px 10px; color:var(--text-muted); font-size:0.9rem;">
                        <i class="fa-solid fa-file-invoice" style="font-size:2rem; margin-bottom:8px; opacity:0.5;"></i><br>
                        لا توجد حركات مالية مسجلة اليوم حتى الآن.<br>استخدم الأزرار السريعة بالأعلى لتسجيل حركة جديدة.
                    </div>
                `;
            } else {
                txs.slice(0, 10).forEach(tx => {
                    let badgeClass = 'act-badge-in';
                    let iconClass = 'fa-arrow-down';
                    let colorClass = 'positive';

                    if (tx.type === 'payment') {
                        badgeClass = 'act-badge-out';
                        iconClass = 'fa-arrow-up';
                        colorClass = 'negative';
                    } else if (tx.type === 'sale') {
                        badgeClass = 'act-badge-sale';
                        iconClass = 'fa-receipt';
                        colorClass = 'positive';
                    }

                    feedHtml += `
                        <div class="activity-card">
                            <div class="activity-info">
                                <div class="activity-badge ${badgeClass}">
                                    <i class="fa-solid ${iconClass}"></i>
                                </div>
                                <div class="activity-details">
                                    <h5>${tx.title}</h5>
                                    <span>المرجع: ${tx.ref} • ${tx.time}</span>
                                </div>
                            </div>
                            <div class="activity-amount">
                                <span class="activity-val ${colorClass}">${fmt(tx.amount)} SDG</span>
                                <button type="button" class="btn-wa-direct" onclick="shareTxWhatsApp('${tx.title}', ${tx.amount}, '${tx.ref}')">
                                    <i class="fa-brands fa-whatsapp"></i> واتساب
                                </button>
                            </div>
                        </div>
                    `;
                });
            }
            document.getElementById('mobile-activity-feed').innerHTML = feedHtml;
        }

        // WhatsApp Share Helper
        function shareTxWhatsApp(title, amount, ref) {
            triggerHaptic('medium');
            const comp = document.getElementById('mobile-company-name').innerText;
            const msg = `🧾 *إشعار مالي معتمد - ${comp}*\n━━━━━━━━━━━━━━━━━━━━\n📌 *البيان:* ${title}\n💰 *المبلغ:* ${fmt(amount)} SDG\n🔢 *رقم المرجع:* ${ref}\n📅 *التاريخ:* ${new Date().toLocaleDateString('ar-SD')}\n━━━━━━━━━━━━━━━━━━━━\n✓ تمت العملية بنجاح عبر نظام مقاييس السحابي.`;
            const waUrl = `https://wa.me/?text=${encodeURIComponent(msg)}`;
            window.open(waUrl, '_blank');
        }

        // ═════════════════════════════════════════════════════════════════
        // 1. UNIVERSAL CHART OF ACCOUNTS BUILDER
        // ═════════════════════════════════════════════════════════════════
        function buildAllAccountsList() {
            ALL_ACCOUNTS = [];

            // 1. Banks (🏦 البنوك)
            const bankItems = [];
            if (window.SERVER_DATA?.banks && window.SERVER_DATA.banks.length > 0) {
                window.SERVER_DATA.banks.forEach((b, i) => {
                    bankItems.push({
                        id: 'bank_' + (b.id || i + 1),
                        rawId: b.id || i + 1,
                        name: b.name || b.bank_name || 'بنك ' + (i + 1),
                        category: 'bank',
                        categoryName: 'بنك',
                        balance: parseFloat(b.balance || b.amount || 0),
                        phone: b.accountNumber || b.account_number || '',
                        icon: 'fa-building-columns',
                        color: '#38bdf8'
                    });
                });
            } else {
                const localBanks = loadTenantArray('erp_banks_v1');
                if (localBanks.length > 0) {
                    localBanks.forEach((b, i) => {
                        bankItems.push({
                            id: 'bank_' + (b.id || i + 1),
                            rawId: b.id || i + 1,
                            name: b.name || 'بنك ' + (i + 1),
                            category: 'bank',
                            categoryName: 'بنك',
                            balance: parseFloat(b.balance || 0),
                            phone: b.accountNumber || '',
                            icon: 'fa-building-columns',
                            color: '#38bdf8'
                        });
                    });
                } else {
                    bankItems.push({
                        id: 'bank_bankak',
                        rawId: 'bankak',
                        name: 'بنك الخرطوم (بنكك)',
                        category: 'bank',
                        categoryName: 'بنك',
                        balance: 0,
                        phone: '',
                        icon: 'fa-building-columns',
                        color: '#38bdf8'
                    });
                    bankItems.push({
                        id: 'bank_faisal',
                        rawId: 'faisal',
                        name: 'بنك فيصل الإسلامي (فوري)',
                        category: 'bank',
                        categoryName: 'بنك',
                        balance: 0,
                        phone: '',
                        icon: 'fa-building-columns',
                        color: '#38bdf8'
                    });
                    bankItems.push({
                        id: 'bank_omdurman',
                        rawId: 'omdurman',
                        name: 'بنك أم درمان الوطني (أوكاش)',
                        category: 'bank',
                        categoryName: 'بنك',
                        balance: 0,
                        phone: '',
                        icon: 'fa-building-columns',
                        color: '#38bdf8'
                    });
                }
            }

            // Also include any bank vaults from M_VAULTS
            M_VAULTS.forEach((v, idx) => {
                const isSafe = v.name.includes('خزن') || v.name.includes('كاش') || v.name.includes('صندوق');
                if (!isSafe) {
                    const exists = bankItems.some(b => b.name.toLowerCase() === v.name.toLowerCase());
                    if (!exists) {
                        bankItems.push({
                            id: 'vault_bank_' + idx,
                            rawId: 'vault_bank_' + idx,
                            name: v.name,
                            category: 'bank',
                            categoryName: 'بنك',
                            balance: v.amount || 0,
                            phone: '',
                            icon: 'fa-building-columns',
                            color: '#38bdf8'
                        });
                    }
                }
            });

            // 2. Safes (💵 الخزن)
            const safeItems = [];
            if (window.SERVER_DATA?.safes && window.SERVER_DATA.safes.length > 0) {
                window.SERVER_DATA.safes.forEach((s, i) => {
                    safeItems.push({
                        id: 'safe_' + (s.id || i + 1),
                        rawId: s.id || i + 1,
                        name: s.name || s.safe_name || 'الخزينة الرئيسية',
                        category: 'safe',
                        categoryName: 'خزينة',
                        balance: parseFloat(s.balance || s.amount || 0),
                        phone: '',
                        icon: 'fa-vault',
                        color: '#10b981'
                    });
                });
            } else {
                const localSafes = loadTenantArray('erp_safes_v1');
                if (localSafes.length > 0) {
                    localSafes.forEach((s, i) => {
                        safeItems.push({
                            id: 'safe_' + (s.id || i + 1),
                            rawId: s.id || i + 1,
                            name: s.name || 'الخزينة الرئيسية',
                            category: 'safe',
                            categoryName: 'خزينة',
                            balance: parseFloat(s.balance || 0),
                            phone: '',
                            icon: 'fa-vault',
                            color: '#10b981'
                        });
                    });
                } else {
                    safeItems.push({
                        id: 'safe_main',
                        rawId: 'safe_main',
                        name: 'الخزينة الرئيسية (كاش)',
                        category: 'safe',
                        categoryName: 'خزينة',
                        balance: 0,
                        phone: '',
                        icon: 'fa-vault',
                        color: '#10b981'
                    });
                }
            }

            // Add cash vaults from M_VAULTS
            M_VAULTS.forEach((v, idx) => {
                const isSafe = v.name.includes('خزن') || v.name.includes('كاش') || v.name.includes('صندوق');
                if (isSafe) {
                    const exists = safeItems.some(s => s.name.toLowerCase() === v.name.toLowerCase());
                    if (!exists) {
                        safeItems.push({
                            id: 'vault_safe_' + idx,
                            rawId: 'vault_safe_' + idx,
                            name: v.name,
                            category: 'safe',
                            categoryName: 'خزينة',
                            balance: v.amount || 0,
                            phone: '',
                            icon: 'fa-vault',
                            color: '#10b981'
                        });
                    }
                }
            });

            // 3. Customers (👤 العملاء)
            const customerItems = M_CUSTOMERS.map(c => ({
                id: 'cust_' + c.id,
                rawId: c.id,
                name: c.name,
                category: 'customer',
                categoryName: 'عميل',
                balance: c.balance || 0,
                phone: c.phone || '',
                icon: 'fa-user',
                color: '#fbbf24'
            }));

            // 4. Suppliers (🏢 الموردين)
            const supplierItems = [];
            const rawSups = window.SERVER_DATA?.suppliers || loadTenantArray('erp_suppliers_v1');
            if (rawSups.length > 0) {
                rawSups.forEach((s, i) => {
                    supplierItems.push({
                        id: 'sup_' + (s.id || s.supplier_id || i + 1),
                        rawId: s.id || s.supplier_id || i + 1,
                        name: s.name || s.supplier_name || 'مورد ' + (i + 1),
                        category: 'supplier',
                        categoryName: 'مورد',
                        balance: parseFloat(s.balance || s.current_balance || s.exposure || 0),
                        phone: s.phone || '',
                        icon: 'fa-truck-field',
                        color: '#a855f7'
                    });
                });
            }

            // 5. Standard Expense & Revenue Accounts (📉 المصروفات)
            const expenseItems = [
                { id: 'exp_admin', rawId: 'exp_admin', name: 'مصروفات عمومية وإدارية', category: 'expense', categoryName: 'مصروف', balance: 0, phone: '', icon: 'fa-money-bill-trend-up', color: '#f43f5e' },
                { id: 'exp_salaries', rawId: 'exp_salaries', name: 'مرتبات وأجور العاملين', category: 'expense', categoryName: 'مصروف', balance: 0, phone: '', icon: 'fa-users', color: '#f43f5e' },
                { id: 'exp_rent', rawId: 'exp_rent', name: 'إيجار مقرات ومخازن', category: 'expense', categoryName: 'مصروف', balance: 0, phone: '', icon: 'fa-warehouse', color: '#f43f5e' },
                { id: 'exp_utilities', rawId: 'exp_utilities', name: 'كهرباء ومياه وإنترنت', category: 'expense', categoryName: 'مصروف', balance: 0, phone: '', icon: 'fa-bolt', color: '#f43f5e' },
                { id: 'exp_hospitality', rawId: 'exp_hospitality', name: 'نثريات وضيافة', category: 'expense', categoryName: 'مصروف', balance: 0, phone: '', icon: 'fa-mug-hot', color: '#f43f5e' },
                { id: 'exp_fuel', rawId: 'exp_fuel', name: 'وقود ونقليات وترحيل', category: 'expense', categoryName: 'مصروف', balance: 0, phone: '', icon: 'fa-gas-pump', color: '#f43f5e' },
                { id: 'exp_maintenance', rawId: 'exp_maintenance', name: 'صيانة وتشغيل', category: 'expense', categoryName: 'مصروف', balance: 0, phone: '', icon: 'fa-wrench', color: '#f43f5e' },
                { id: 'exp_purchases', rawId: 'exp_purchases', name: 'مشتريات بضاعة نقدية', category: 'expense', categoryName: 'مصروف', balance: 0, phone: '', icon: 'fa-cart-shopping', color: '#f43f5e' }
            ];

            // Combine into unified accounts array
            ALL_ACCOUNTS = [...bankItems, ...safeItems, ...customerItems, ...supplierItems, ...expenseItems];
        }

        // ═════════════════════════════════════════════════════════════════
        // 2. UNIFIED FINANCIAL MOVEMENT ENGINE (قيد مزدوج: دائن / مدين)
        // ═════════════════════════════════════════════════════════════════
        function openFinancialTxSheet() {
            triggerHaptic('light');
            buildAllAccountsList();

            // Set default date to today YYYY-MM-DD
            const dateInp = document.getElementById('tx-date');
            if (dateInp && !dateInp.value) {
                dateInp.value = new Date().toISOString().split('T')[0];
            }

            // Clear ref validation hint
            const hint = document.getElementById('tx-ref-validation-hint');
            if (hint) hint.style.display = 'none';

            // Intelligent defaults if no accounts selected yet
            if (!SELECTED_FROM_ACCOUNT) {
                const firstSafeOrBank = ALL_ACCOUNTS.find(a => a.category === 'safe') || ALL_ACCOUNTS.find(a => a.category === 'bank');
                if (firstSafeOrBank) selectAccountDirectly('from', firstSafeOrBank);
            }

            openBottomSheet('sheet-financial-tx');
        }

        // Backward compatibility shim if any legacy button calls setTxType
        function setTxType(type) {
            SELECTED_TX_TYPE = type || 'financial_movement';
            const opEl = document.getElementById('tx-operation-type');
            if (opEl) opEl.value = SELECTED_TX_TYPE;
        }

        // Auto-infer movement nature based on the chosen parties
        function inferTransactionNature(fromAcc, toAcc) {
            if (!fromAcc || !toAcc) {
                return { type: 'financial_movement', label: 'حركة مالية عامة (قيد مزدوج)' };
            }
            const fCat = fromAcc.category;
            const tCat = toAcc.category;

            if (fCat === 'customer' && (tCat === 'bank' || tCat === 'safe')) {
                return { type: 'receipt', label: 'سند قبض نقدية من عميل' };
            }
            if ((fCat === 'bank' || fCat === 'safe') && tCat === 'supplier') {
                return { type: 'payment', label: 'سند صرف وسداد لمورد' };
            }
            if ((fCat === 'bank' || fCat === 'safe') && tCat === 'expense') {
                return { type: 'payment', label: 'سند صرف منصرفات وتشغيل' };
            }
            if ((fCat === 'bank' || fCat === 'safe') && (tCat === 'bank' || tCat === 'safe')) {
                return { type: 'transfer', label: 'تحويل مالي بيني' };
            }
            if (fCat === 'customer' && tCat === 'customer') {
                return { type: 'journal_transfer', label: 'تسوية وتحويل ذمة بين عميلين' };
            }
            if (fCat === 'supplier' && tCat === 'supplier') {
                return { type: 'journal_transfer', label: 'تسوية وتحويل بين موردين' };
            }
            return { type: 'financial_movement', label: 'حركة مالية عامة (قيد مزدوج)' };
        }

        // ═════════════════════════════════════════════════════════════════
        // 🔒 DESKTOP FINANCIAL CONSTRAINTS: DUPLICATE & LENGTH CHECKERS
        // ═════════════════════════════════════════════════════════════════
        function checkTransferDuplicate(tno, currentTxId) {
            if (!tno || String(tno).trim().length < 6) return false;
            const cleanTno = String(tno).trim().toLowerCase();
            const targetSuffix = cleanTno.slice(-6);

            const getTenantKey = window.getTenantKey || ((k) => k);
            const keys = [
                getTenantKey('erp_financial_txs_v1'),
                getTenantKey('erp_single_payments_v1'),
                getTenantKey('erp_payments_v1'),
                getTenantKey('erp_single_receipts_v1'),
                getTenantKey('erp_receipts_v1'),
                getTenantKey('erp_comprehensive_payments_v1'),
                getTenantKey('erp_comprehensive_receipts_v1'),
                getTenantKey('erp_journal_v1')
            ];

            const isMatch = (val) => {
                if (!val) return false;
                const s = String(val).trim().toLowerCase();
                if (s === cleanTno) return true;
                if (s.length >= 6 && s.slice(-6) === targetSuffix) return true;
                return false;
            };

            for (let key of keys) {
                let list = [];
                try {
                    list = JSON.parse(localStorage.getItem(key)) || [];
                } catch(e) { continue; }

                for (let item of list) {
                    if (!item || item.deleted) continue;
                    if (currentTxId && (item.id === currentTxId || item.refNo === currentTxId || item.voucherNo === currentTxId)) continue;

                    if (isMatch(item.refNo) || isMatch(item.ref) || isMatch(item.id)) return true;
                    if (isMatch(item.mobileTransferNo) || isMatch(item.transferNo)) return true;

                    if (item.bankTransfers && Array.isArray(item.bankTransfers)) {
                        for (let bt of item.bankTransfers) {
                            if (isMatch(bt.transferNo)) return true;
                        }
                    }
                }
            }

            // Also check SERVER_DATA preloaded records
            if (window.SERVER_DATA) {
                const sRecs = window.SERVER_DATA.receipts || [];
                const sPays = window.SERVER_DATA.payments || [];
                for (let r of sRecs) {
                    if (isMatch(r.refNo) || isMatch(r.id) || isMatch(r.transferNo) || isMatch(r.mobileTransferNo)) return true;
                }
                for (let p of sPays) {
                    if (isMatch(p.refNo) || isMatch(p.id) || isMatch(p.transferNo) || isMatch(p.mobileTransferNo)) return true;
                }
            }

            return false;
        }

        function showLengthWarningModal(transferNo) {
            triggerHaptic('heavy');
            const existing = document.getElementById('mob-length-modal');
            if (existing) existing.remove();

            const tStr = String(transferNo || '').trim();
            const len = tStr.length;

            const modalHTML = `
                <div id="mob-length-modal" class="mobile-alert-backdrop">
                    <div class="mobile-alert-card type-warning">
                        <div class="mob-alert-icon-ring" style="background:rgba(245,158,11,0.18); color:#f59e0b; border:2px solid #f59e0b;">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div class="mob-alert-title">تنبيه رقابي أمني!</div>
                        <div class="mob-alert-body">
                            رقم التحويلة / الإشعار البنكي <span style="background:rgba(245,158,11,0.25); color:#f59e0b; font-weight:900; padding:2px 8px; border-radius:6px; font-family:monospace; font-size:1.15rem;">${tStr || '(فارغ)'}</span><br>
                            <strong>أقل من 6 أرقام!</strong> (أدخلت ${len} ${len === 1 ? 'رقم' : 'أرقام'} فقط).<br>
                            <span style="font-size:0.8rem; color:#94a3b8; display:block; margin-top:8px;">
                                يشترط النظام الأساسي أن يتكون رقم التحويلة من 6 أرقام على الأقل لضمان صحة القيود المصرفية ومنع التلاعب.
                            </span>
                        </div>
                        <button type="button" class="mob-alert-btn" style="background:#f59e0b; color:#000;" onclick="document.getElementById('mob-length-modal').remove(); document.getElementById('tx-ref')?.focus();">
                            <i class="fa-solid fa-pen-to-square"></i> تصحيح رقم التحويلة الآن
                        </button>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }

        function showDuplicateWarningModal(transferNo) {
            triggerHaptic('heavy');
            const existing = document.getElementById('mob-duplicate-modal');
            if (existing) existing.remove();

            const tStr = String(transferNo || '').trim();

            const modalHTML = `
                <div id="mob-duplicate-modal" class="mobile-alert-backdrop">
                    <div class="mobile-alert-card type-critical">
                        <div class="mob-alert-icon-ring" style="background:rgba(239,68,68,0.2); color:#ef4444; border:2px solid #ef4444;">
                            <i class="fa-solid fa-ban"></i>
                        </div>
                        <div class="mob-alert-title" style="color:#ef4444;">🚨 تحويلة مكررة محظورة!</div>
                        <div class="mob-alert-body">
                            رقم التحويلة البنكية <span style="background:rgba(239,68,68,0.3); color:#fff; font-weight:900; padding:2px 8px; border-radius:6px; font-family:monospace; font-size:1.2rem;">${tStr}</span><br>
                            <strong>مسجل مسبقاً في النظام!</strong> (تطابق آخر 6 أرقام مع سند آخر).<br>
                            <span style="font-size:0.8rem; color:#fca5a5; display:block; margin-top:8px;">
                                يمنع نظام الرقابة والتدقيق تكرار استخدام نفس رقم التحويلة البنكية في أكثر من حركة لمنع الازدواجية والخطأ المالي.
                            </span>
                        </div>
                        <button type="button" class="mob-alert-btn" style="background:#ef4444; color:#fff;" onclick="document.getElementById('mob-duplicate-modal').remove(); document.getElementById('tx-ref')?.focus();">
                            <i class="fa-solid fa-arrow-rotate-left"></i> إدخال رقم تحويلة مختلف
                        </button>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }

        function validateRefNoLive(val) {
            const hint = document.getElementById('tx-ref-validation-hint');
            if (!hint) return;
            const clean = String(val || '').trim();
            if (!clean) {
                hint.style.display = 'none';
                return;
            }

            if (clean.length < 6) {
                hint.style.display = 'block';
                hint.style.background = 'rgba(245, 158, 11, 0.15)';
                hint.style.color = '#f59e0b';
                hint.style.border = '1px solid #f59e0b';
                hint.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> الرقم الحالي (${clean.length} أرقام) - يشترط 6 أرقام فأكثر`;
                return;
            }

            const isDuplicate = checkTransferDuplicate(clean, null);
            if (isDuplicate) {
                hint.style.display = 'block';
                hint.style.background = 'rgba(239, 68, 68, 0.18)';
                hint.style.color = '#ef4444';
                hint.style.border = '1px solid #ef4444';
                hint.innerHTML = `<i class="fa-solid fa-circle-xmark"></i> هذا الرقم مسجل مسبقاً في حركة أخرى (مكرر)!`;
                return;
            }

            hint.style.display = 'block';
            hint.style.background = 'rgba(16, 185, 129, 0.15)';
            hint.style.color = '#10b981';
            hint.style.border = '1px solid #10b981';
            hint.innerHTML = `<i class="fa-solid fa-circle-check"></i> رقم التحويلة نظامي ومتاح للتقييد (${clean.length} أرقام)`;
        }

        function updateTxAmountPreview(val) {
            const preview = document.getElementById('tx-amount-preview');
            if (!preview) return;
            const num = parseFloat(val) || 0;
            preview.innerText = fmt(num) + ' SDG';
        }

        function selectAccountDirectly(target, acc) {
            if (!acc) return;
            if (target === 'from') {
                SELECTED_FROM_ACCOUNT = acc;
                document.getElementById('tx-from-id').value = acc.id;
                document.getElementById('tx-from-name').value = acc.name;
                document.getElementById('tx-from-type').value = acc.category;
                document.getElementById('tx-from-phone').value = acc.phone || '';

                document.getElementById('display-from-name').textContent = acc.name;
                document.getElementById('display-from-meta').innerHTML = `
                    <span>${acc.categoryName} ${acc.phone ? '• ' + acc.phone : ''} • الرصيد: <strong>${fmt(acc.balance)} SDG</strong></span>
                `;
                const badge = document.getElementById('display-from-badge');
                if (badge) {
                    badge.style.display = 'inline-block';
                    badge.style.background = acc.color + '22';
                    badge.style.color = acc.color;
                    badge.textContent = acc.categoryName;
                }
            } else {
                SELECTED_TO_ACCOUNT = acc;
                document.getElementById('tx-to-id').value = acc.id;
                document.getElementById('tx-to-name').value = acc.name;
                document.getElementById('tx-to-type').value = acc.category;
                document.getElementById('tx-to-phone').value = acc.phone || '';

                document.getElementById('display-to-name').textContent = acc.name;
                document.getElementById('display-to-meta').innerHTML = `
                    <span>${acc.categoryName} ${acc.phone ? '• ' + acc.phone : ''} • الرصيد: <strong>${fmt(acc.balance)} SDG</strong></span>
                `;
                const badge = document.getElementById('display-to-badge');
                if (badge) {
                    badge.style.display = 'inline-block';
                    badge.style.background = acc.color + '22';
                    badge.style.color = acc.color;
                    badge.textContent = acc.categoryName;
                }
            }
        }

        function swapTxAccounts() {
            triggerHaptic('medium');
            const temp = SELECTED_FROM_ACCOUNT;
            SELECTED_FROM_ACCOUNT = SELECTED_TO_ACCOUNT;
            SELECTED_TO_ACCOUNT = temp;

            if (SELECTED_FROM_ACCOUNT) selectAccountDirectly('from', SELECTED_FROM_ACCOUNT);
            if (SELECTED_TO_ACCOUNT) selectAccountDirectly('to', SELECTED_TO_ACCOUNT);

            if (window.showModernToast) {
                window.showModernToast('🔄 تم تبديل الحسابين (من / إلى)', 'info', 1500);
            }
        }

        // ═════════════════════════════════════════════════════════════════
        // 3. UNIVERSAL SEARCHABLE ACCOUNT PICKER
        // ═════════════════════════════════════════════════════════════════
        function openAccountPicker(target) {
            triggerHaptic('light');
            CURRENT_PICKER_TARGET = target;
            const heading = document.getElementById('picker-modal-heading');
            if (heading) {
                if (target === 'from') {
                    heading.innerHTML = '<span style="color:#ef4444;">🔴 اختر الحساب الصارف (المدفوع منه / الدائن)</span>';
                } else {
                    heading.innerHTML = '<span style="color:#10b981;">🟢 اختر الحساب المستلم (المقبوض إليه / المدين)</span>';
                }
            }

            const searchInp = document.getElementById('picker-search-input');
            if (searchInp) searchInp.value = '';

            CURRENT_PICKER_CATEGORY = 'all';
            document.querySelectorAll('.picker-category-pills .cat-pill').forEach((p, idx) => {
                if (idx === 0) p.classList.add('active');
                else p.classList.remove('active');
            });

            filterAccountsList();
            openBottomSheet('sheet-account-picker');
            setTimeout(() => {
                if (searchInp) searchInp.focus();
            }, 200);
        }

        function setPickerCategory(cat, btn) {
            triggerHaptic('light');
            CURRENT_PICKER_CATEGORY = cat;
            document.querySelectorAll('.picker-category-pills .cat-pill').forEach(p => p.classList.remove('active'));
            if (btn) btn.classList.add('active');
            filterAccountsList();
        }

        function filterAccountsList() {
            const q = (document.getElementById('picker-search-input')?.value || '').trim().toLowerCase();
            const container = document.getElementById('picker-accounts-container');
            if (!container) return;

            let filtered = ALL_ACCOUNTS;

            if (CURRENT_PICKER_CATEGORY !== 'all') {
                filtered = filtered.filter(a => a.category === CURRENT_PICKER_CATEGORY);
            }

            if (q) {
                filtered = filtered.filter(a => {
                    const nameMatch = (a.name || '').toLowerCase().includes(q);
                    const phoneMatch = (a.phone || '').toLowerCase().includes(q);
                    const catMatch = (a.categoryName || '').toLowerCase().includes(q);
                    return nameMatch || phoneMatch || catMatch;
                });
            }

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div style="text-align:center; padding:35px 15px; color:var(--text-muted);">
                        <i class="fa-solid fa-magnifying-glass" style="font-size:2.2rem; opacity:0.35; margin-bottom:10px;"></i>
                        <div style="font-size:0.95rem; font-weight:800; color:var(--text-main);">لم يتم العثور على حساب مطابق</div>
                        <div style="font-size:0.8rem; margin-top:4px;">تأكد من كتابة الاسم أو الرقم بشكل صحيح</div>
                    </div>
                `;
                return;
            }

            let html = '';
            filtered.forEach(acc => {
                const isFromSelected = SELECTED_FROM_ACCOUNT && SELECTED_FROM_ACCOUNT.id === acc.id;
                const isToSelected = SELECTED_TO_ACCOUNT && SELECTED_TO_ACCOUNT.id === acc.id;
                const isSelected = (CURRENT_PICKER_TARGET === 'from' && isFromSelected) || (CURRENT_PICKER_TARGET === 'to' && isToSelected);

                let balLabel = 'الرصيد الدفتري';
                let balColor = 'var(--text-main)';
                if (acc.category === 'customer') {
                    if (acc.balance > 0) {
                        balLabel = 'مدين للشركة';
                        balColor = 'var(--rose)';
                    } else {
                        balLabel = 'خالص';
                        balColor = 'var(--emerald)';
                    }
                } else if (acc.category === 'supplier') {
                    balLabel = 'مستحق للمورد';
                    balColor = 'var(--amber)';
                }

                html += `
                    <div class="account-item-card ${isSelected ? 'active-selected' : ''}" onclick="selectAccount('${acc.id}')">
                        <div class="acc-item-icon" style="background:${acc.color}22; color:${acc.color}; border:1px solid ${acc.color}55;">
                            <i class="fa-solid ${acc.icon}"></i>
                        </div>
                        <div class="acc-item-details">
                            <div class="acc-item-name">${acc.name}</div>
                            <div class="acc-item-meta">
                                <span class="acc-badge" style="background:${acc.color}22; color:${acc.color};">${acc.categoryName}</span>
                                ${acc.phone ? `<span style="display:flex; align-items:center; gap:4px;"><i class="fa-solid fa-phone" style="font-size:0.7rem;"></i> ${acc.phone}</span>` : ''}
                            </div>
                        </div>
                        <div class="acc-item-balance">
                            <div class="acc-item-balance-val" style="color:${balColor}; font-weight:900;">${fmt(acc.balance)}</div>
                            <div class="acc-item-balance-lbl">${balLabel}</div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function selectAccount(accId) {
            triggerHaptic('medium');
            const acc = ALL_ACCOUNTS.find(a => a.id === accId);
            if (!acc) return;

            selectAccountDirectly(CURRENT_PICKER_TARGET, acc);
            closeBottomSheet('sheet-account-picker');
        }

        // ═════════════════════════════════════════════════════════════════
        // 4. TRANSACTION SUBMIT & LEDGER ADJUSTMENT
        // ═════════════════════════════════════════════════════════════════
        function handleFinancialTxSubmit(e, openWhatsAppDirect = false) {
            if (e && e.preventDefault) e.preventDefault();
            triggerHaptic('heavy');

            const amt = parseFloat(document.getElementById('tx-amount').value) || 0;
            const txDate = document.getElementById('tx-date').value || new Date().toISOString().split('T')[0];
            const txRef = document.getElementById('tx-ref').value.trim();
            const txNotes = document.getElementById('tx-notes').value.trim();

            // 1. Amount Validation
            if (amt <= 0) {
                alert('⚠️ يرجى إدخال مبلغ صحيح أكبر من صفر');
                document.getElementById('tx-amount').focus();
                return false;
            }

            // 2. Financial Limits Permission Check (Desktop Rule)
            if (window.MagaieesPermissions && typeof window.MagaieesPermissions.checkFinancialLimits === 'function') {
                const limitCheck = window.MagaieesPermissions.checkFinancialLimits('voucher_amount', amt);
                if (!limitCheck.allowed) {
                    alert('⚠️ تجاوز سقف الصرف المالي المسموح به: ' + limitCheck.message);
                    return false;
                }
            }

            // 3. Accounts Selection & Distinct Identity Validation
            if (!SELECTED_FROM_ACCOUNT) {
                alert('⚠️ يرجى اختيار الحساب الدافع (من حساب)');
                openAccountPicker('from');
                return false;
            }

            if (!SELECTED_TO_ACCOUNT) {
                alert('⚠️ يرجى اختيار الحساب المستلم (إلى حساب)');
                openAccountPicker('to');
                return false;
            }

            if (SELECTED_FROM_ACCOUNT.id === SELECTED_TO_ACCOUNT.id) {
                alert('⚠️ خطأ محاسبي: لا يمكن أن يكون الحساب الدافع والحساب المستلم نفس الحساب!');
                return false;
            }

            // 4. Transfer / Reference Number Rules (Desktop Rules Enforcement: Min 6 digits & Duplicate check)
            const isBankInvolved = (SELECTED_FROM_ACCOUNT.category === 'bank' || SELECTED_TO_ACCOUNT.category === 'bank' ||
                                    (SELECTED_FROM_ACCOUNT.name || '').includes('بنك') || (SELECTED_TO_ACCOUNT.name || '').includes('بنك') ||
                                    (SELECTED_FROM_ACCOUNT.name || '').includes('بنكك') || (SELECTED_TO_ACCOUNT.name || '').includes('بنكك'));

            // If bank is involved, reference number is mandatory
            if (isBankInvolved && !txRef) {
                showLengthWarningModal('');
                document.getElementById('tx-ref').focus();
                return false;
            }

            // If ref is provided (or bank is involved), enforce length >= 6 and duplicate prevention
            if (txRef) {
                if (txRef.length < 6) {
                    showLengthWarningModal(txRef);
                    document.getElementById('tx-ref').focus();
                    return false;
                }

                if (checkTransferDuplicate(txRef, null)) {
                    showDuplicateWarningModal(txRef);
                    document.getElementById('tx-ref').focus();
                    return false;
                }
            }

            // 5. Fiscal Period & Date Validation
            if (typeof window.canCreateRecord === 'function') {
                if (!window.canCreateRecord(txDate)) return false;
            }

            // 6. Overdraft / Safe Negative Warning
            if (SELECTED_FROM_ACCOUNT.category === 'safe' || SELECTED_FROM_ACCOUNT.category === 'bank') {
                const currentBal = parseFloat(SELECTED_FROM_ACCOUNT.balance || 0);
                if (amt > currentBal && currentBal > 0) {
                    const allowOverdraft = confirm(`⚠️ تنبيه سيولة: رصيد [${SELECTED_FROM_ACCOUNT.name}] الحالي هو (${fmt(currentBal)} SDG)، بينما المبلغ المطلوب صرفه هو (${fmt(amt)} SDG).\n\nهل تريد المتابعة والسماح بالحركة على المكشوف؟`);
                    if (!allowOverdraft) return false;
                }
            }

            // 7. Auto-infer Movement Nature based on chosen accounts
            const nature = inferTransactionNature(SELECTED_FROM_ACCOUNT, SELECTED_TO_ACCOUNT);
            const finalRef = txRef || ('TX-' + Date.now().toString().slice(-6));

            // Build Transaction Record
            const txRecord = {
                id: finalRef,
                refNo: finalRef,
                transferNo: txRef || '',
                mobileTransferNo: txRef || '',
                amount: amt,
                date: txDate,
                type: nature.type,
                natureLabel: nature.label,
                fromAccount: {
                    id: SELECTED_FROM_ACCOUNT.id,
                    rawId: SELECTED_FROM_ACCOUNT.rawId,
                    name: SELECTED_FROM_ACCOUNT.name,
                    type: SELECTED_FROM_ACCOUNT.category,
                    categoryName: SELECTED_FROM_ACCOUNT.categoryName,
                    phone: SELECTED_FROM_ACCOUNT.phone
                },
                toAccount: {
                    id: SELECTED_TO_ACCOUNT.id,
                    rawId: SELECTED_TO_ACCOUNT.rawId,
                    name: SELECTED_TO_ACCOUNT.name,
                    type: SELECTED_TO_ACCOUNT.category,
                    categoryName: SELECTED_TO_ACCOUNT.categoryName,
                    phone: SELECTED_TO_ACCOUNT.phone
                },
                notes: txNotes,
                createdAt: new Date().toLocaleTimeString('ar-SD', { hour: '2-digit', minute: '2-digit' })
            };

            // 8. Save to financial txs array
            let allFinancialTxs = loadTenantArray('erp_financial_txs_v1');
            allFinancialTxs.unshift(txRecord);
            saveTenantArray('erp_financial_txs_v1', allFinancialTxs);

            // 9. Adjust Balances
            if (SELECTED_FROM_ACCOUNT.category === 'customer') {
                let custs = loadTenantArray('erp_customers_v1');
                const c = custs.find(x => x.id === SELECTED_FROM_ACCOUNT.rawId || 'cust_' + x.id === SELECTED_FROM_ACCOUNT.id);
                if (c) {
                    c.currentBalance = (parseFloat(c.currentBalance || c.balance || 0) - amt);
                    saveTenantArray('erp_customers_v1', custs);
                }
            } else if (SELECTED_FROM_ACCOUNT.category === 'supplier') {
                let sups = loadTenantArray('erp_suppliers_v1');
                const s = sups.find(x => x.id === SELECTED_FROM_ACCOUNT.rawId || 'sup_' + x.id === SELECTED_FROM_ACCOUNT.id);
                if (s) {
                    s.balance = (parseFloat(s.balance || 0) - amt);
                    saveTenantArray('erp_suppliers_v1', sups);
                }
            } else if (SELECTED_FROM_ACCOUNT.category === 'bank' || SELECTED_FROM_ACCOUNT.category === 'safe') {
                SELECTED_FROM_ACCOUNT.balance = (parseFloat(SELECTED_FROM_ACCOUNT.balance || 0) - amt);
            }

            if (SELECTED_TO_ACCOUNT.category === 'customer') {
                let custs = loadTenantArray('erp_customers_v1');
                const c = custs.find(x => x.id === SELECTED_TO_ACCOUNT.rawId || 'cust_' + x.id === SELECTED_TO_ACCOUNT.id);
                if (c) {
                    c.currentBalance = (parseFloat(c.currentBalance || c.balance || 0) + amt);
                    saveTenantArray('erp_customers_v1', custs);
                }
            } else if (SELECTED_TO_ACCOUNT.category === 'supplier') {
                let sups = loadTenantArray('erp_suppliers_v1');
                const s = sups.find(x => x.id === SELECTED_TO_ACCOUNT.rawId || 'sup_' + x.id === SELECTED_TO_ACCOUNT.id);
                if (s) {
                    s.balance = (parseFloat(s.balance || 0) + amt);
                    saveTenantArray('erp_suppliers_v1', sups);
                }
            } else if (SELECTED_TO_ACCOUNT.category === 'bank' || SELECTED_TO_ACCOUNT.category === 'safe') {
                SELECTED_TO_ACCOUNT.balance = (parseFloat(SELECTED_TO_ACCOUNT.balance || 0) + amt);
            }

            // 10. Backward Compatibility Sync with Legacy Receipts and Payments
            if (nature.type === 'receipt') {
                let recs = loadTenantArray('erp_single_receipts_v1');
                recs.unshift({
                    id: finalRef,
                    refNo: finalRef,
                    transferNo: txRef,
                    mobileTransferNo: txRef,
                    amount: amt,
                    customer: SELECTED_FROM_ACCOUNT.name,
                    customerId: SELECTED_FROM_ACCOUNT.rawId,
                    customerName: SELECTED_FROM_ACCOUNT.name,
                    destination: SELECTED_TO_ACCOUNT.name,
                    notes: txNotes,
                    date: txDate
                });
                saveTenantArray('erp_single_receipts_v1', recs);
            } else if (nature.type === 'payment') {
                let pays = loadTenantArray('erp_single_payments_v1');
                pays.unshift({
                    id: finalRef,
                    refNo: finalRef,
                    transferNo: txRef,
                    mobileTransferNo: txRef,
                    amount: amt,
                    recipient: SELECTED_TO_ACCOUNT.name,
                    source: SELECTED_FROM_ACCOUNT.name,
                    notes: txNotes,
                    date: txDate
                });
                saveTenantArray('erp_single_payments_v1', pays);
            }

            // Push to backend
            pushChangesToBackend();

            ACTIVE_TX_FOR_WA = txRecord;
            document.getElementById('tx-amount').value = '';
            document.getElementById('tx-ref').value = '';
            document.getElementById('tx-notes').value = '';
            updateTxAmountPreview(0);

            const hint = document.getElementById('tx-ref-validation-hint');
            if (hint) hint.style.display = 'none';

            closeBottomSheet('sheet-financial-tx');
            refreshMobileData();

            if (window.showModernToast) {
                window.showModernToast(`✅ تم حفظ الحركة المالية (${fmt(amt)} SDG) بنجاح!`, 'success', 3000);
            }

            if (openWhatsAppDirect) {
                setTimeout(() => {
                    promptWhatsAppRecipient();
                }, 300);
            }

            return true;
        }

        function handleTxWhatsAppShareClick() {
            const amt = parseFloat(document.getElementById('tx-amount').value) || 0;
            if (amt > 0) {
                handleFinancialTxSubmit(null, true);
                return;
            }
            if (ACTIVE_TX_FOR_WA) {
                promptWhatsAppRecipient();
            } else {
                alert('⚠️ يرجى إدخال المبلغ وتفاصيل الحركة أولاً');
            }
        }

        // ═════════════════════════════════════════════════════════════════
        // 5. SMART WHATSAPP RECIPIENT TARGET SELECTOR
        // ═════════════════════════════════════════════════════════════════
        function promptWhatsAppRecipient() {
            if (!ACTIVE_TX_FOR_WA) return;
            const tx = ACTIVE_TX_FOR_WA;

            const fromNameEl = document.getElementById('wa-from-name');
            const fromPhoneEl = document.getElementById('wa-from-phone');
            const toNameEl = document.getElementById('wa-to-name');
            const toPhoneEl = document.getElementById('wa-to-phone');

            if (fromNameEl) fromNameEl.textContent = tx.fromAccount?.name || 'حساب الصارف';
            if (fromPhoneEl) fromPhoneEl.textContent = tx.fromAccount?.phone ? ('هاتف: ' + tx.fromAccount.phone) : 'هاتف: غير مسجل (اختيار جهة الاتصال)';

            if (toNameEl) toNameEl.textContent = tx.toAccount?.name || 'حساب المستلم';
            if (toPhoneEl) toPhoneEl.textContent = tx.toAccount?.phone ? ('هاتف: ' + tx.toAccount.phone) : 'هاتف: غير مسجل (اختيار جهة الاتصال)';

            openBottomSheet('sheet-whatsapp-target');
        }

        function sendVoucherWhatsApp(targetOption) {
            triggerHaptic('medium');
            if (!ACTIVE_TX_FOR_WA) {
                closeBottomSheet('sheet-whatsapp-target');
                return;
            }

            const tx = ACTIVE_TX_FOR_WA;
            const compName = document.getElementById('mobile-company-name')?.innerText || 'مؤسسة السجانة';

            let typeTitle = tx.natureLabel || 'حركة مالية (قيد مزدوج)';
            if (!tx.natureLabel) {
                if (tx.type === 'receipt') typeTitle = 'سند قبض واستلام نقدية';
                else if (tx.type === 'payment') typeTitle = 'سند صرف ودفع نقدية';
                else if (tx.type === 'transfer') typeTitle = 'سند تحويل مالي بيني';
                else if (tx.type === 'journal_transfer') typeTitle = 'تسوية وتحويل ذمة بين حسابين';
            }

            const voucherMsg = 
`🧾 *إشعار مالي معتمد*
🏢 *${compName}*
━━━━━━━━━━━━━━━━━━━━
📌 *نوع الحركة:* ${typeTitle}
💰 *المبلغ الصافي:* ${fmt(tx.amount)} SDG
🔴 *من حساب (الصارف):* ${tx.fromAccount?.name || '--'}
🟢 *إلى حساب (المستلم):* ${tx.toAccount?.name || '--'}
🔢 *رقم الإشعار / المرجع:* ${tx.refNo}
📅 *التاريخ:* ${tx.date}
📝 *البيان:* ${tx.notes || 'سند معتمد بنجاح'}
━━━━━━━━━━━━━━━━━━━━
✓ تم قيد وتسجيل الحركة دفترياً بنجاح عبر نظام مقاييس السحابي.`;

            let phone = '';
            if (targetOption === 'from' && tx.fromAccount?.phone) {
                phone = tx.fromAccount.phone.replace(/[^0-9]/g, '');
            } else if (targetOption === 'to' && tx.toAccount?.phone) {
                phone = tx.toAccount.phone.replace(/[^0-9]/g, '');
            }

            let waUrl = '';
            if (phone) {
                if (phone.length === 9) phone = '249' + phone;
                else if (phone.length === 10 && phone.startsWith('0')) phone = '249' + phone.substring(1);
                waUrl = `https://wa.me/${phone}?text=${encodeURIComponent(voucherMsg)}`;
            } else {
                waUrl = `https://wa.me/?text=${encodeURIComponent(voucherMsg)}`;
            }

            closeBottomSheet('sheet-whatsapp-target');
            window.open(waUrl, '_blank');
        }

        // ═════════════════════════════════════════════════════════════════
        // 6. AI BANK SLIP / NOTIFICATION EXTRACTOR
        // ═════════════════════════════════════════════════════════════════
        function parseSlipInputLive(rawText) {
            if (!rawText || !rawText.trim()) {
                const preview = document.getElementById('ai-parsed-preview');
                if (preview) preview.style.display = 'none';
                AI_EXTRACTED_DATA = null;
                return;
            }

            // 1. Amount Extraction
            let extractedAmount = null;
            const amountRegexes = [
                /(?:المبلغ|مبلغ|المبلغ المحول|المبلغ المخصوم|المبلغ المسحوب|Amount|Total)\s*[:=]?\s*([0-9]{1,3}(?:,[0-9]{3})*(?:\.[0-9]+)?|[0-9]+(?:\.[0-9]+)?)/i,
                /([0-9]{1,3}(?:,[0-9]{3})*(?:\.[0-9]+)?|[0-9]+(?:\.[0-9]+)?)\s*(?:SDG|ج\.س|جنيه|ريال|دولار)/i
            ];
            for (let rx of amountRegexes) {
                const m = rawText.match(rx);
                if (m && m[1]) {
                    const clean = m[1].replace(/,/g, '');
                    const parsed = parseFloat(clean);
                    if (parsed > 0) {
                        extractedAmount = parsed;
                        break;
                    }
                }
            }

            // 2. Ref / Transaction ID
            let extractedRef = null;
            const refRegexes = [
                /(?:رقم العملية|رقم المرجع|المرجع|رقم الإشعار|رقم المعاملة|Transaction ID|Ref(?:erence)?\s*(?:No)?)\s*[:=]?\s*([A-Za-z0-9\-_]{4,35})/i,
                /(?:TX|TRX|REF|FT)[0-9A-Za-z]{6,25}/i
            ];
            for (let rx of refRegexes) {
                const m = rawText.match(rx);
                if (m) {
                    extractedRef = m[1] || m[0];
                    break;
                }
            }

            // 3. Date & Time
            let extractedDate = null;
            const dateRegexes = [
                /(\d{4}[-/.]\d{1,2}[-/.]\d{1,2})/,
                /(\d{1,2}[-/.]\d{1,2}[-/.]\d{4})/
            ];
            for (let rx of dateRegexes) {
                const m = rawText.match(rx);
                if (m) {
                    extractedDate = m[1].replace(/\//g, '-').replace(/\./g, '-');
                    break;
                }
            }
            if (!extractedDate) {
                extractedDate = new Date().toISOString().split('T')[0];
            }

            // 4. Sender / Payer (من / المحول)
            let extractedFrom = null;
            const fromRegexes = [
                /(?:من حساب|من المحول|المحول|المرسل|من)\s*[:=]?\s*([^\n\r,–—]+)/i
            ];
            for (let rx of fromRegexes) {
                const m = rawText.match(rx);
                if (m && m[1]) {
                    extractedFrom = m[1].trim();
                    break;
                }
            }

            // 5. Receiver / Payee (إلى / المحول إليه / المستلم)
            let extractedTo = null;
            const toRegexes = [
                /(?:إلى حساب|المحول إليه|المستلم|إلى)\s*[:=]?\s*([^\n\r,–—]+)/i
            ];
            for (let rx of toRegexes) {
                const m = rawText.match(rx);
                if (m && m[1]) {
                    extractedTo = m[1].trim();
                    break;
                }
            }

            if (ALL_ACCOUNTS.length === 0) buildAllAccountsList();

            let matchedFromAcc = null;
            let matchedToAcc = null;

            if (extractedFrom) {
                matchedFromAcc = ALL_ACCOUNTS.find(a => 
                    a.name.toLowerCase().includes(extractedFrom.toLowerCase()) || 
                    extractedFrom.toLowerCase().includes(a.name.toLowerCase())
                );
            }

            if (extractedTo) {
                matchedToAcc = ALL_ACCOUNTS.find(a => 
                    a.name.toLowerCase().includes(extractedTo.toLowerCase()) || 
                    extractedTo.toLowerCase().includes(a.name.toLowerCase())
                );
            }

            if (!matchedToAcc && (rawText.includes('بنكك') || rawText.includes('بنك الخرطوم') || rawText.includes('Bankak'))) {
                matchedToAcc = ALL_ACCOUNTS.find(a => a.name.includes('الخرطوم') || a.name.includes('بنكك')) || ALL_ACCOUNTS.find(a => a.category === 'bank');
            }

            AI_EXTRACTED_DATA = {
                amount: extractedAmount,
                ref: extractedRef || ('AI-' + Date.now().toString().slice(-6)),
                date: extractedDate,
                senderText: extractedFrom,
                receiverText: extractedTo,
                matchedFromAcc: matchedFromAcc,
                matchedToAcc: matchedToAcc
            };

            const previewBox = document.getElementById('ai-parsed-preview');
            if (previewBox) {
                previewBox.style.display = 'block';
                document.getElementById('ai-chip-amount').innerText = extractedAmount ? (fmt(extractedAmount) + ' SDG') : 'لم يتم تحديده';
                document.getElementById('ai-chip-ref').innerText = extractedRef || 'غير محدد';
                document.getElementById('ai-chip-from').innerText = matchedFromAcc ? (matchedFromAcc.name + ' (' + matchedFromAcc.categoryName + ')') : (extractedFrom || 'غير محدد');
                document.getElementById('ai-chip-to').innerText = matchedToAcc ? (matchedToAcc.name + ' (' + matchedToAcc.categoryName + ')') : (extractedTo || 'بنك الخرطوم');
                document.getElementById('ai-chip-date').innerText = extractedDate || 'اليوم';
            }
        }

        function loadSampleBankakSlip() {
            triggerHaptic('light');
            const sample = 
`إشعار تحويل بنكك - بنك الخرطوم
رقم العملية: 94820148
المبلغ: 250,000.00 SDG
من: محمد عثمان إدريس
إلى: مؤسسة السجانة التجارية
التاريخ: ${new Date().toISOString().split('T')[0]} 14:35
حالة العملية: ناجحة ومكتملة`;

            const textarea = document.getElementById('ai-slip-text');
            if (textarea) {
                textarea.value = sample;
                parseSlipInputLive(sample);
            }
        }

        async function pasteFromClipboardToAI() {
            triggerHaptic('light');
            try {
                if (navigator.clipboard && navigator.clipboard.readText) {
                    const text = await navigator.clipboard.readText();
                    if (text) {
                        const textarea = document.getElementById('ai-slip-text');
                        if (textarea) {
                            textarea.value = text;
                            parseSlipInputLive(text);
                        }
                        return;
                    }
                }
            } catch(err) {}
            alert('يرجى لصق النص يدوياً داخل المربع بالضغط المطول واختيار "لصق"');
        }

        function applyAIExtractedData() {
            triggerHaptic('heavy');
            if (!AI_EXTRACTED_DATA) return;

            const data = AI_EXTRACTED_DATA;
            buildAllAccountsList();

            if (data.amount) {
                document.getElementById('tx-amount').value = data.amount;
                updateTxAmountPreview(data.amount);
            }

            if (data.ref) {
                document.getElementById('tx-ref').value = data.ref;
            }

            if (data.date) {
                document.getElementById('tx-date').value = data.date;
            }

            if (data.matchedFromAcc) {
                selectAccountDirectly('from', data.matchedFromAcc);
            }
            if (data.matchedToAcc) {
                selectAccountDirectly('to', data.matchedToAcc);
            }

            document.getElementById('tx-notes').value = `تحويل بنكك آلي - مرجع ${data.ref}`;

            closeBottomSheet('sheet-ai-slip');
            openBottomSheet('sheet-financial-tx');

            if (window.showModernToast) {
                window.showModernToast('✨ تم تفريغ الإشعار وتعبئة السند بنجاح!', 'success', 2500);
            }
        }

        // Backward compatibility shims
        function handleQuickReceiptSubmit(e) {
            openFinancialTxSheet('receipt');
        }

        function handleQuickPaymentSubmit(e) {
            openFinancialTxSheet('payment');
        }

        // 3. Fast POS Invoice
        function handleItemSelect(sel) {
            const opt = sel.selectedOptions[0];
            if (opt && opt.dataset.price) {
                document.getElementById('sale-price').value = opt.dataset.price;
                calculateSaleTotal();
            }
        }

        function calculateSaleTotal() {
            const qty = parseFloat(document.getElementById('sale-qty').value) || 0;
            const price = parseFloat(document.getElementById('sale-price').value) || 0;
            const total = qty * price;
            document.getElementById('sale-total-display').innerText = fmt(total) + ' SDG';
        }

        function handleQuickSaleSubmit(e) {
            e.preventDefault();
            triggerHaptic('heavy');
            const custSel = document.getElementById('sale-customer');
            const custId = custSel.value;
            const custName = custSel.selectedOptions[0]?.text || 'عميل نقدي';
            const itemSel = document.getElementById('sale-item');
            const itemId = itemSel.value;
            const itemName = itemSel.selectedOptions[0]?.text || 'صنف';
            const qty = parseFloat(document.getElementById('sale-qty').value) || 1;
            const price = parseFloat(document.getElementById('sale-price').value) || 0;
            const total = qty * price;
            const method = document.getElementById('sale-payment-method').value;
            const invNo = 'INV-' + Date.now().toString().slice(-6);

            if (total <= 0) {
                alert('يرجى تحديد الصنف والكمية بشكل صحيح');
                return;
            }

            const inv = {
                id: invNo,
                invoiceNo: invNo,
                customer: custId,
                customerName: custName,
                grandTotal: total,
                total: total,
                items: [{ id: itemId, name: itemName, qty: qty, price: price, subtotal: total }],
                paymentMethod: method,
                date: new Date().toLocaleTimeString('ar-SD', { hour: '2-digit', minute:'2-digit' })
            };

            let sales = loadTenantArray('erp_sales_invoices_v1');
            sales.unshift(inv);
            saveTenantArray('erp_sales_invoices_v1', sales);

            pushChangesToBackend();

            closeBottomSheet('sheet-sale');
            if (window.showModernToast) {
                window.showModernToast(`✅ تم إصدار الفاتورة ${invNo} بمبلغ ${fmt(total)} SDG بنجاح!`, 'success');
            }
            refreshMobileData();

            setTimeout(() => {
                if (confirm(`تم إصدار الفاتورة بنجاح!\nهل تريد مشاركة الفاتورة (${fmt(total)} SDG) مع العميل عبر واتساب الآن؟`)) {
                    shareTxWhatsApp(`فاتورة بيع (${itemName} × ${qty})`, total, invNo);
                }
            }, 300);
        }

        // 4. Customer Mini Statement & Claim
        function renderCustomerMiniStatement(custId) {
            const card = document.getElementById('statement-result-card');
            if (!custId) {
                card.style.display = 'none';
                return;
            }
            const c = M_CUSTOMERS.find(x => x.id === custId);
            if (!c) return;

            activeStatementCustomer = c;
            card.style.display = 'block';

            const balEl = document.getElementById('cust-balance-amt');
            const statusEl = document.getElementById('cust-balance-status');
            const bal = c.balance || 0;

            balEl.innerText = fmt(bal) + ' SDG';
            if (bal > 0) {
                balEl.style.color = 'var(--rose)';
                statusEl.innerText = '🔴 مطلوب سداد هذا المبلغ للشركة';
            } else {
                balEl.style.color = 'var(--emerald)';
                statusEl.innerText = '🟢 الحساب خالص / رصيد دائن';
            }

            // Real customer recent activity
            const allTxs = [
                ...(window.SERVER_DATA?.sales || loadTenantArray('erp_sales_invoices_v1')).filter(x => x.customer === custId || x.customerId === custId),
                ...(window.SERVER_DATA?.receipts || loadTenantArray('erp_single_receipts_v1')).filter(x => x.customerId === custId || x.customer === custId)
            ];

            let txSummary = '';
            if (allTxs.length > 0) {
                allTxs.slice(0, 3).forEach(t => {
                    const isRec = !!t.destination;
                    txSummary += `• ${isRec ? 'سند قبض' : 'فاتورة'}: ${fmt(t.amount || t.grandTotal)} SDG (${t.date || 'مؤخراً'})<br>`;
                });
            } else {
                txSummary = '• لا توجد فواتير سابقة مسجلة لهذا العميل.';
            }

            document.getElementById('cust-recent-txs').innerHTML = `
                <div style="background:var(--surface-sub); border-radius:10px; padding:10px; border:1px solid var(--border-glass);">
                    ${txSummary}
                </div>
            `;
        }

        function sendWhatsAppClaim() {
            if (!activeStatementCustomer) return;
            triggerHaptic('heavy');
            const c = activeStatementCustomer;
            const bal = fmt(c.balance || 0);
            const comp = document.getElementById('mobile-company-name').innerText;
            const msg = `السيد المحترم/ *${c.name}*\nتحية طيبة من *${comp}* 🌟\n\nنود تذكيركم بلطف بأن الرصيد المتبقي على حسابكم حتى تاريخ اليوم هو:\n💰 *${bal} SDG*\n\nيرجى التكرم بالتحويل عبر حسابنا في بنكك أو مراجعة الحساب، ولكم وافر الشكر والتقدير.`;
            const phone = c.phone ? c.phone.replace(/[^0-9]/g, '') : '';
            const waUrl = phone ? `https://wa.me/249${phone.slice(-9)}?text=${encodeURIComponent(msg)}` : `https://wa.me/?text=${encodeURIComponent(msg)}`;
            window.open(waUrl, '_blank');
        }

        function openFullStatementInDesktop() {
            if (!activeStatementCustomer) return;
            localStorage.setItem('magaiees_view_preference', 'desktop');
            window.location.href = `account-statement.php?accountId=${encodeURIComponent(activeStatementCustomer.id)}&force_desktop=1`;
        }

        // 5. Analytics KPI Render
        function renderAnalyticsKPIs() {
            let sales = 0;
            let receipts = 0;
            let payments = 0;

            const sList = window.SERVER_DATA?.sales || loadTenantArray('erp_sales_invoices_v1');
            const rList = window.SERVER_DATA?.receipts || loadTenantArray('erp_single_receipts_v1');
            const pList = window.SERVER_DATA?.payments || loadTenantArray('erp_single_payments_v1');

            if (sList.length) sales = sList.reduce((a, b) => a + (parseFloat(b.grandTotal || b.total) || 0), 0);
            if (rList.length) receipts = rList.reduce((a, b) => a + (parseFloat(b.amount) || 0), 0);
            if (pList.length) payments = pList.reduce((a, b) => a + (parseFloat(b.amount) || 0), 0);

            document.getElementById('kpi-today-sales').innerText = fmt(sales);
            document.getElementById('kpi-today-receipts').innerText = fmt(receipts);
            document.getElementById('kpi-today-payments').innerText = fmt(payments);
            document.getElementById('kpi-today-net').innerText = fmt(receipts - payments);

            // Real Debtors Radar from DB
            let debtorsHtml = '';
            const topDebtors = [...M_CUSTOMERS].filter(c => (c.balance || 0) > 0).sort((a, b) => (b.balance || 0) - (a.balance || 0)).slice(0, 5);
            if (topDebtors.length === 0) {
                debtorsHtml = '<div style="text-align:center; padding:20px; color:var(--text-muted); font-size:0.85rem;">لا توجد مديونيات قائمة على العملاء حالياً.</div>';
            } else {
                topDebtors.forEach((c, idx) => {
                    debtorsHtml += `
                        <div class="radar-row">
                            <div>
                                <div class="radar-name">${idx + 1}. ${c.name}</div>
                                <div style="font-size:0.75rem; color:var(--text-muted);">هاتف: ${c.phone || 'غير مسجل'}</div>
                            </div>
                            <div style="text-align:left;">
                                <div style="font-size:1.15rem; font-weight:900; color:var(--rose); font-family:'Inter', sans-serif;">${fmt(c.balance)} SDG</div>
                                <button type="button" onclick="activeStatementCustomer=M_CUSTOMERS.find(x=>x.id==='${c.id}'); sendWhatsAppClaim();" style="background:rgba(34,197,94,0.18); border:1px solid #22c55e; color:#22c55e; border-radius:6px; padding:3px 8px; font-size:0.74rem; font-weight:800; cursor:pointer;">
                                    <i class="fa-brands fa-whatsapp"></i> مطالبة
                                </button>
                            </div>
                        </div>
                    `;
                });
            }
            document.getElementById('mobile-debtors-container').innerHTML = debtorsHtml;

            // Real Low Stock Radar from DB
            let stockHtml = '';
            const lowStockItems = [...M_ITEMS].slice(0, 5);
            if (lowStockItems.length === 0) {
                stockHtml = '<div style="text-align:center; padding:20px; color:var(--text-muted); font-size:0.85rem;">لا توجد أصناف في المستودع.</div>';
            } else {
                lowStockItems.forEach(it => {
                    stockHtml += `
                        <div class="radar-row">
                            <div>
                                <div class="radar-name">📦 ${it.name}</div>
                                <div style="font-size:0.75rem; color:var(--amber); font-weight:700;">سعر البيع: ${fmt(it.price)} SDG</div>
                            </div>
                            <div style="font-size:1.15rem; font-weight:900; color:var(--amber); font-family:'Inter', sans-serif;">
                                المتبقي: ${it.stock || 0}
                            </div>
                        </div>
                    `;
                });
            }
            document.getElementById('mobile-low-stock-container').innerHTML = stockHtml;
        }

        // Background Live Sync with Database
        function triggerBackgroundSync() {
            const token = localStorage.getItem('authToken');
            if (!token) return;
            fetch('api_index.php/sync/pull', {
                headers: { 'Authorization': 'Bearer ' + token }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data) {
                    const u = JSON.parse(localStorage.getItem('user') || '{}');
                    const prefix = (u.tenant_id && u.tenant_id !== 'default') ? 'tenant_' + u.tenant_id + '_' : '';
                    Object.keys(data.data).forEach(k => {
                        try {
                            const val = typeof data.data[k] === 'string' ? data.data[k] : JSON.stringify(data.data[k]);
                            if (prefix) localStorage.setItem(prefix + k, val);
                            localStorage.setItem(k, val);
                        } catch(e){}
                    });
                }
            })
            .catch(() => {});
        }

        function pushChangesToBackend() {
            const token = localStorage.getItem('authToken');
            if (!token) return;
            const payload = {
                customers: loadTenantArray('erp_customers_v1'),
                single_receipts: loadTenantArray('erp_single_receipts_v1'),
                single_payments: loadTenantArray('erp_single_payments_v1'),
                sales_invoices: loadTenantArray('erp_sales_invoices_v1')
            };
            fetch('api_index.php/sync/push', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                body: JSON.stringify(payload)
            }).catch(() => {});
        }

        // Initialize on DOM load
        document.addEventListener('DOMContentLoaded', () => {
            initTheme();
            refreshMobileData();
        });
    </script>
</body>
</html>
