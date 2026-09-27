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

    // 4. Real Products & Items
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
            <!-- 1. Quick Receipt (قبض / إشعار بنكك) -->
            <div class="power-action-btn act-receipt" onclick="openBottomSheet('sheet-receipt')">
                <div class="action-icon-circle">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <div class="action-title">سند قبض</div>
                    <div class="action-subtitle">إشعار بنكك / كاش</div>
                </div>
            </div>

            <!-- 2. Quick Payment (صرف نقدية / مصاريف) -->
            <div class="power-action-btn act-payment" onclick="openBottomSheet('sheet-payment')">
                <div class="action-icon-circle">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                </div>
                <div>
                    <div class="action-title">سند صرف</div>
                    <div class="action-subtitle">سداد مورد / منصرف</div>
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

    <!-- SHEET 1: سند قبض وبنكك -->
    <div class="mobile-sheet-overlay" id="overlay-sheet-receipt" onclick="closeBottomSheet('sheet-receipt')"></div>
    <div class="mobile-bottom-sheet" id="sheet-receipt">
        <div class="sheet-drag-handle"></div>
        <div class="sheet-header">
            <span class="sheet-title" style="color:var(--emerald);">
                <i class="fa-solid fa-hand-holding-dollar"></i> تسجيل سند قبض جديد
            </span>
            <button type="button" class="btn-sheet-close" onclick="closeBottomSheet('sheet-receipt')">✕</button>
        </div>
        <div class="sheet-body">
            <form onsubmit="handleQuickReceiptSubmit(event)">
                <div class="m-form-group">
                    <label class="m-label">المبلغ المستلم (SDG):</label>
                    <input type="number" id="receipt-amount" class="m-input huge-number" placeholder="0.00" step="any" required inputmode="decimal">
                </div>
                <div class="m-form-group">
                    <label class="m-label">اسم العميل:</label>
                    <select id="receipt-customer" class="m-select" required>
                        <option value="">-- جاري التحميل من قاعدة البيانات... --</option>
                    </select>
                </div>
                <div class="m-form-group">
                    <label class="m-label">الجهة المستلمة (الخزنة / البنك):</label>
                    <select id="receipt-destination" class="m-select" required>
                        <!-- Loaded dynamically -->
                    </select>
                </div>
                <div class="m-form-group">
                    <label class="m-label">رقم إشعار بنكك / المرجع (اختياري):</label>
                    <input type="text" id="receipt-ref" class="m-input" placeholder="مثال: 9481023">
                </div>
                <div class="m-form-group">
                    <label class="m-label">ملاحظات أو بيان السند:</label>
                    <input type="text" id="receipt-notes" class="m-input" placeholder="دفعة تحت الحساب">
                </div>
                <button type="submit" class="m-btn-submit">
                    <i class="fa-solid fa-check"></i> حفظ السند ومشاركة إيصال الواتساب 💬
                </button>
            </form>
        </div>
    </div>

    <!-- SHEET 2: سند صرف -->
    <div class="mobile-sheet-overlay" id="overlay-sheet-payment" onclick="closeBottomSheet('sheet-payment')"></div>
    <div class="mobile-bottom-sheet" id="sheet-payment">
        <div class="sheet-drag-handle"></div>
        <div class="sheet-header">
            <span class="sheet-title" style="color:var(--rose);">
                <i class="fa-solid fa-money-bill-transfer"></i> تسجيل سند صرف / نثريات
            </span>
            <button type="button" class="btn-sheet-close" onclick="closeBottomSheet('sheet-payment')">✕</button>
        </div>
        <div class="sheet-body">
            <form onsubmit="handleQuickPaymentSubmit(event)">
                <div class="m-form-group">
                    <label class="m-label">المبلغ المصروف (SDG):</label>
                    <input type="number" id="payment-amount" class="m-input huge-number" style="color:var(--rose);" placeholder="0.00" step="any" required inputmode="decimal">
                </div>
                <div class="m-form-group">
                    <label class="m-label">يصرف إلى (اسم المستلم أو المورد):</label>
                    <input type="text" id="payment-recipient" class="m-input" placeholder="مثال: شركة الكهرباء أو اسم المورد" required>
                </div>
                <div class="m-form-group">
                    <label class="m-label">يصرف من حساب:</label>
                    <select id="payment-source" class="m-select" required>
                        <!-- Loaded dynamically -->
                    </select>
                </div>
                <div class="m-form-group">
                    <label class="m-label">بيان الصرف:</label>
                    <input type="text" id="payment-notes" class="m-input" placeholder="مصاريف تشغيل / نثريات" required>
                </div>
                <button type="submit" class="m-btn-submit" style="background: linear-gradient(135deg, var(--rose), #be123c);">
                    <i class="fa-solid fa-check"></i> حفظ وترحيل سند الصرف فوراً
                </button>
            </form>
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
            
            // Combine receipts, payments, sales from server preloaded or local arrays
            const receipts = window.SERVER_DATA?.receipts || loadTenantArray('erp_single_receipts_v1');
            const payments = window.SERVER_DATA?.payments || loadTenantArray('erp_single_payments_v1');
            const sales = window.SERVER_DATA?.sales || loadTenantArray('erp_sales_invoices_v1');

            receipts.forEach(r => {
                txs.push({
                    type: 'receipt',
                    title: 'سند قبض - ' + (r.customerName || r.customer || 'عميل'),
                    amount: parseFloat(r.amount || 0),
                    time: r.date || r.createdAt || 'اليوم',
                    ref: r.refNo || r.id || 'REC'
                });
            });

            payments.forEach(p => {
                txs.push({
                    type: 'payment',
                    title: 'سند صرف - ' + (p.recipient || p.party || 'مصروف'),
                    amount: parseFloat(p.amount || 0),
                    time: p.date || p.createdAt || 'اليوم',
                    ref: p.refNo || p.id || 'PAY'
                });
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

        // 1. Submit Quick Receipt
        function handleQuickReceiptSubmit(e) {
            e.preventDefault();
            triggerHaptic('heavy');
            const amt = parseFloat(document.getElementById('receipt-amount').value) || 0;
            const custId = document.getElementById('receipt-customer').value;
            const custName = document.getElementById('receipt-customer').selectedOptions[0]?.text || 'عميل';
            const dest = document.getElementById('receipt-destination').value;
            const ref = document.getElementById('receipt-ref').value.trim() || 'REC-' + Date.now().toString().slice(-6);
            const notes = document.getElementById('receipt-notes').value.trim();

            if (amt <= 0) {
                alert('يرجى كتابة مبلغ صحيح أكبر من صفر');
                return;
            }

            const rec = {
                id: ref,
                refNo: ref,
                amount: amt,
                customerId: custId,
                customerName: custName,
                destination: dest,
                notes: notes,
                date: new Date().toLocaleTimeString('ar-SD', { hour: '2-digit', minute:'2-digit' })
            };

            // Save to local tenant array
            let receipts = loadTenantArray('erp_single_receipts_v1');
            receipts.unshift(rec);
            saveTenantArray('erp_single_receipts_v1', receipts);

            // Deduct customer debt
            let custs = loadTenantArray('erp_customers_v1');
            const targetCust = custs.find(c => c.id === custId);
            if (targetCust) {
                targetCust.currentBalance = (parseFloat(targetCust.currentBalance || targetCust.balance || 0) - amt);
                saveTenantArray('erp_customers_v1', custs);
            }

            // Sync with backend API
            pushChangesToBackend();

            closeBottomSheet('sheet-receipt');
            if (window.showModernToast) {
                window.showModernToast(`✅ تم حفظ سند القبض بمبلغ ${fmt(amt)} SDG بنجاح!`, 'success');
            }
            refreshMobileData();

            setTimeout(() => {
                if (confirm(`تم حفظ سند القبض بنجاح!\nهل تريد مشاركة إيصال الاستلام (${fmt(amt)} SDG) للعميل عبر واتساب الآن؟`)) {
                    shareTxWhatsApp(`سند قبض (${custName})`, amt, ref);
                }
            }, 300);
        }

        // 2. Submit Quick Payment
        function handleQuickPaymentSubmit(e) {
            e.preventDefault();
            triggerHaptic('heavy');
            const amt = parseFloat(document.getElementById('payment-amount').value) || 0;
            const recipient = document.getElementById('payment-recipient').value.trim();
            const source = document.getElementById('payment-source').value;
            const notes = document.getElementById('payment-notes').value.trim();
            const ref = 'PAY-' + Date.now().toString().slice(-6);

            if (amt <= 0) {
                alert('يرجى كتابة مبلغ صحيح أكبر من صفر');
                return;
            }

            const pay = {
                id: ref,
                refNo: ref,
                amount: amt,
                recipient: recipient,
                source: source,
                notes: notes,
                date: new Date().toLocaleTimeString('ar-SD', { hour: '2-digit', minute:'2-digit' })
            };

            let payments = loadTenantArray('erp_single_payments_v1');
            payments.unshift(pay);
            saveTenantArray('erp_single_payments_v1', payments);

            pushChangesToBackend();

            closeBottomSheet('sheet-payment');
            if (window.showModernToast) {
                window.showModernToast(`✅ تم تسجيل سند الصرف بمبلغ ${fmt(amt)} SDG بنجاح!`, 'success');
            }
            refreshMobileData();
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
