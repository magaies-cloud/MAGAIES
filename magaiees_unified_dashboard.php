<?php if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() <= 1 && (int)ob_get_length() === 0 && stripos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false && !in_array('ob_gzhandler', ob_list_handlers(), true)) { @ob_start('ob_gzhandler'); } ?>
<?php
// ═════════════════════════════════════════════════════════════════════
// 🌐 SMART DEVICE ROUTER (Unified Link Architecture)
// ═════════════════════════════════════════════════════════════════════
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$secChUaMobile = $_SERVER['HTTP_SEC_CH_UA_MOBILE'] ?? '';
$isLaptopOrPC = (bool)preg_match('/(windows nt|macintosh|x11; linux x86_64|cros)/i', $userAgent) && !preg_match('/(android|iphone|ipad|ipod|mobile)/i', $userAgent);
$isMobileUserAgent = !$isLaptopOrPC && ((bool)preg_match('/(android|iphone|ipad|ipod|mobile|blackberry|iemobile|opera mini|samsung|huawei|honor|harmonyos|xiaomi|redmi|poco|oppo|vivo|oneplus|pixel)/i', $userAgent) || ($secChUaMobile === '?1'));
$forceDesktop = isset($_GET['force_desktop']) && $_GET['force_desktop'] === '1';

if ($forceDesktop || $isLaptopOrPC) {
    setcookie('magaiees_view_preference', 'desktop', time() + (86400 * 365), '/');
} elseif ($isMobileUserAgent) {
    header('Location: mobile-dashboard.php');
    exit;
}
if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
}
$page_title = 'لوحة التحكم';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js?v=<?= @filemtime(__DIR__ . '/js/lang.js') ?: '2027' ?>"></script>
<script>
(function(){
    var l = localStorage.getItem('erp_lang') || 'ar';
    if(l==='en') {
        document.documentElement.setAttribute('dir','ltr');
        document.documentElement.setAttribute('lang','en');
    }
    try {
        var _ssoP = new URLSearchParams(window.location.search);
        if (_ssoP.has('sso_token')) {
            var _sTok = _ssoP.get('sso_token');
            localStorage.setItem('authToken', _sTok);
            localStorage.setItem('saas_token', _sTok);
            if (_ssoP.has('sso_user')) {
                var _rawU = _ssoP.get('sso_user');
                localStorage.setItem('user', _rawU);
                try {
                    var _pU = JSON.parse(_rawU);
                    if (_pU && _pU.tenant_id) {
                        localStorage.setItem('currentTenantId', String(_pU.tenant_id));
                        document.cookie = "currentTenantId=" + encodeURIComponent(_pU.tenant_id) + "; path=/; max-age=2592000; SameSite=Lax";
                    }
                    if (_pU && _pU.subdomain) {
                        document.cookie = "currentSubdomain=" + encodeURIComponent(_pU.subdomain) + "; path=/; max-age=2592000; SameSite=Lax";
                    }
                    if (_pU && _pU.company_name) {
                        localStorage.setItem('companyName', _pU.company_name);
                    }
                } catch (e) {}
            }
            document.cookie = "session_active=true; path=/; max-age=2592000; SameSite=Lax";
            document.cookie = "saas_token=" + encodeURIComponent(_sTok) + "; path=/; max-age=2592000; SameSite=Lax";
            sessionStorage.setItem('session_active', 'true');
            sessionStorage.setItem('dashboard_unlocked', '1');
            _ssoP.delete('sso_token');
            _ssoP.delete('sso_user');
            var _cleanQ = _ssoP.toString();
            if (window.history && window.history.replaceState) {
                window.history.replaceState({}, document.title, window.location.pathname + (_cleanQ ? '?' + _cleanQ : ''));
            }
        }
    } catch (e) {}
    var _earlyTok = localStorage.getItem('authToken') || localStorage.getItem('saas_token');
    if (!_earlyTok || _earlyTok === 'local-offline-token' || _earlyTok.indexOf('offline_token_') === 0) {
        _earlyTok = 'magaiees-direct-access-token';
        localStorage.setItem('authToken', _earlyTok);
        localStorage.setItem('saas_token', _earlyTok);
        document.cookie = "session_active=true; path=/; max-age=2592000; SameSite=Lax";
        document.cookie = "saas_token=" + encodeURIComponent(_earlyTok) + "; path=/; max-age=2592000; SameSite=Lax";
        sessionStorage.setItem('session_active', 'true');
        sessionStorage.setItem('dashboard_unlocked', '1');
    }
    // Check force desktop flag or mobile auto-redirect
    var ua = navigator.userAgent || '';
    var isLaptopOrPC = /Windows NT|Macintosh|X11; Linux x86_64|CrOS/i.test(ua) && !/Android|iPhone|iPad|iPod|Mobile/i.test(ua);
    var params = new URLSearchParams(window.location.search);
    if (params.get('force_desktop') === '1' || isLaptopOrPC) {
        localStorage.setItem('magaiees_view_preference', 'desktop');
        document.cookie = "magaiees_view_preference=desktop; path=/; max-age=31536000; SameSite=Lax";
    } else if (params.get('force_mobile') === '1') {
        localStorage.setItem('magaiees_view_preference', 'mobile');
        document.cookie = "magaiees_view_preference=mobile; path=/; max-age=31536000; SameSite=Lax";
        window.location.replace('mobile-dashboard.php?force_mobile=1');
    } else {
        var screenWidth = window.innerWidth || document.documentElement.clientWidth || screen.width;
        var isMobile = !isLaptopOrPC && (/Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Samsung|Huawei|Honor|HarmonyOS|Xiaomi|POCO|Redmi|OPPO|vivo|OnePlus|Pixel/i.test(ua) || screenWidth < 768);
        var pref = localStorage.getItem('magaiees_view_preference');
        if (isMobile && pref !== 'desktop') {
            window.location.replace('mobile-dashboard.php');
        }
    }
})();
function switchToMobileEdition() {
    localStorage.setItem('magaiees_view_preference', 'mobile');
    document.cookie = "magaiees_view_preference=mobile; path=/; max-age=31536000; SameSite=Lax";
    window.location.href = 'mobile-dashboard.php?force_mobile=1';
}
</script>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    
    <!-- PWA & Native Standalone Configuration -->
    <link rel="manifest" href="manifest.json?v=2026_m_v5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="السيطرة ERP">
    <link rel="apple-touch-icon" href="assets/apple-touch-icon.png?v=2026_m_v5">
    <link rel="icon" type="image/svg+xml" href="assets/icon.svg?v=2026_m_v5">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/icon-192.png?v=2026_m_v5">
    <link rel="icon" type="image/png" sizes="256x256" href="assets/icon-256.png?v=2026_m_v5">
    <link rel="icon" type="image/png" sizes="384x384" href="assets/icon-384.png?v=2026_m_v5">
    <link rel="icon" type="image/png" sizes="512x512" href="assets/icon-512.png?v=2026_m_v5">
    <link rel="icon" type="image/x-icon" href="favicon.ico?v=2026_m_v5">
    <link rel="shortcut icon" href="assets/favicon.ico?v=2026_m_v5">
    <meta name="theme-color" content="#0F172A">

    <link rel="stylesheet" href="css/local-fonts.css?v=2027">
    <title>Magaiees ERP - التحكم المتكاملة | الشاشة العالمية الموحدة</title>
    <link rel="stylesheet" href="css/tailwind.min.css">
    <style>.text-\[10\.5px\]{font-size:10.5px}</style>
    <link rel="preload" href="css/design-system.css?v=20260723_v14" as="style">
    <link rel="stylesheet" href="css/design-system.css?v=20260723_v14">
    <link rel="stylesheet" href="css/theme.css?v=<?php echo file_exists('css/theme.css') ? filemtime('css/theme.css') : '12'; ?>">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/unified-brain.css?v=20260722">
    <script src="js/pwa-manager.js" defer></script>
    <script defer src="js/vendor/chart.umd.min.js"></script>
<link rel="stylesheet" href="css/screens/magaiees_unified_dashboard-s1.css?v=<?= @filemtime(__DIR__ . '/css/screens/magaiees_unified_dashboard-s1.css') ?: '2027' ?>">
    <style>
        :root.light-theme {
            --bg-color: #f1f5f9;
            --surface: #ffffff;
            --surface-hover: rgba(241, 245, 249, 0.98);
            --border: rgba(0, 0, 0, 0.08);
            --border-highlight: rgba(0, 0, 0, 0.15);
            --primary-accent: #2563eb;
            --theme-pink: #db2777;
            --theme-blue: #1d4ed8;
            --theme-green: #059669;
            --theme-orange: #d97706;
            --theme-red: #dc2626;
            --theme-purple: #7c3aed;
        }
        :root.light-theme body {
            background-color: var(--bg-color);
            color: #000000;
        }
        :root.light-theme .ambient-bg {
            background: radial-gradient(circle at 50% 50%, #f8fafc 0%, #cbd5e1 100%) !important;
        }
        :root.light-theme .panel,
        :root.light-theme .neo-card,
        :root.light-theme .glass-panel {
            background: var(--surface) !important;
            border-color: var(--border) !important;
            color: #000000 !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06) !important;
        }
        :root.light-theme td, :root.light-theme th, :root.light-theme tr {
            color: #000000 !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
        }
        :root.light-theme .pos-grid thead th {
            background: rgba(0, 0, 0, 0.04) !important;
            color: #000000 !important;
        }
        :root.light-theme .pos-grid tbody tr {
            background: rgba(255, 255, 255, 0.95) !important;
        }
        :root.light-theme .pos-grid tbody tr:hover {
            background: rgba(0, 0, 0, 0.04) !important;
        }
        :root.light-theme input, :root.light-theme select, :root.light-theme textarea {
            background: #ffffff !important;
            color: #000000 !important;
            border: 1px solid rgba(0, 0, 0, 0.18) !important;
        }
        :root.light-theme .admin-action-btn {
            background: rgba(0,0,0,0.03);
            border-color: rgba(0,0,0,0.1);
            color: #000000;
        }
        :root.light-theme .admin-action-btn:hover {
            background: rgba(0,0,0,0.06);
            border-color: rgba(0,0,0,0.18);
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        :root.light-theme .qa-icon {
            border: 2px solid rgba(0, 0, 0, 0.15) !important;
            background: rgba(0, 0, 0, 0.03) !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05) !important;
        }
        
        /* Light Mode specific color coding (lighter backgrounds, clear borders) */
        :root.light-theme .admin-action-settings .qa-icon { background: rgba(100, 116, 139, 0.1) !important; border-color: rgba(100, 116, 139, 0.35) !important; }
        :root.light-theme .admin-action-master .qa-icon { background: rgba(59, 130, 246, 0.1) !important; border-color: rgba(59, 130, 246, 0.35) !important; }
        :root.light-theme .admin-action-jobs .qa-icon { background: rgba(16, 185, 129, 0.1) !important; border-color: rgba(16, 185, 129, 0.35) !important; }
        :root.light-theme .admin-action-notifications .qa-icon { background: rgba(239, 68, 68, 0.1) !important; border-color: rgba(239, 68, 68, 0.35) !important; }
        :root.light-theme .qa-primary .qa-icon { background: rgba(56, 189, 248, 0.1) !important; border-color: rgba(56, 189, 248, 0.35) !important; }
        :root.light-theme .qa-warning .qa-icon { background: rgba(251, 191, 36, 0.1) !important; border-color: rgba(251, 191, 36, 0.35) !important; }
        :root.light-theme .qa-danger .qa-icon { background: rgba(248, 113, 113, 0.1) !important; border-color: rgba(248, 113, 113, 0.35) !important; }
        :root.light-theme .qa-purple .qa-icon { background: rgba(192, 132, 252, 0.1) !important; border-color: rgba(192, 132, 252, 0.35) !important; }
        :root.light-theme a[data-href="inventory-report.php"] .qa-icon { background: rgba(167, 139, 250, 0.1) !important; border-color: rgba(167, 139, 250, 0.35) !important; }

        /* Hover states in Light Mode: backgrounds darken, borders thicken (4.5px), scale up to 1.4 */
        :root.light-theme .admin-action-btn:hover .qa-icon,
        :root.light-theme .quick-action-btn:hover .qa-icon {
            border-width: 4.5px !important;
            transform: scale(1.4) rotate(5deg) translateY(-2px) !important;
        }
        
        :root.light-theme .admin-action-settings:hover .qa-icon { background: rgba(100, 116, 139, 0.3) !important; border-color: #64748b !important; }
        :root.light-theme .admin-action-master:hover .qa-icon { background: rgba(59, 130, 246, 0.3) !important; border-color: #3b82f6 !important; }
        :root.light-theme .admin-action-jobs:hover .qa-icon { background: rgba(16, 185, 129, 0.3) !important; border-color: #10b981 !important; }
        :root.light-theme .admin-action-notifications:hover .qa-icon { background: rgba(239, 68, 68, 0.3) !important; border-color: #ef4444 !important; }
        :root.light-theme .qa-primary:hover .qa-icon { background: rgba(56, 189, 248, 0.3) !important; border-color: #38bdf8 !important; }
        :root.light-theme .qa-warning:hover .qa-icon { background: rgba(251, 191, 36, 0.3) !important; border-color: #fbbf24 !important; }
        :root.light-theme .qa-danger:hover .qa-icon { background: rgba(248, 113, 113, 0.3) !important; border-color: #f87171 !important; }
        :root.light-theme .qa-purple:hover .qa-icon { background: rgba(192, 132, 252, 0.3) !important; border-color: #c084fc !important; }
        :root.light-theme a[data-href="inventory-report.php"]:hover .qa-icon { background: rgba(167, 139, 250, 0.3) !important; border-color: #a78bfa !important; }
        :root.light-theme .admin-actions-row {
            background: rgba(0, 0, 0, 0.03);
            border-color: rgba(0, 0, 0, 0.06);
            box-shadow: none;
        }
        :root.light-theme .operations-actions-row {
            background: rgba(0, 0, 0, 0.015);
            border-color: rgba(0, 0, 0, 0.04);
            box-shadow: none;
        }
        :root.light-theme .actions-row-title {
            color: #1e293b;
            border-left-color: rgba(0,0,0,0.12);
        }
        :root.light-theme .islamic-ticker-container {
            background: rgba(0, 0, 0, 0.04) !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
        }
        :root.light-theme #ticker-profit,
        :root.light-theme #ticker-monthly-profit {
            color: #059669 !important; /* High contrast green */
        }
    
        
        /* Universal Light Theme Overrides for Internal Screens */
        :root.light-theme body,
        :root.light-theme .ambient-bg {
            background: #f8fafc !important;
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
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
            color: #0f172a !important;
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
\n
        /* =======================================================================
           تعديلات التبسيط الجمالي لتوحيد التصميم وجعله مريحاً للعين (حذف النيون والألوان الصارخة)
           ======================================================================= */
        .neon-border-primary,
        .neon-border-success,
        .neon-border-warning,
        .neon-border-danger,
        .neon-border-purple {
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        :root.light-theme .neon-border-primary,
        :root.light-theme .neon-border-success,
        :root.light-theme .neon-border-warning,
        :root.light-theme .neon-border-danger,
        :root.light-theme .neon-border-purple {
            border: 1px solid rgba(0, 0, 0, 0.06) !important;
            border-top: 1px solid rgba(0, 0, 0, 0.06) !important;
        }

        .quick-action-btn, .admin-action-btn {
            border-bottom: 1.5px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15) !important;
            transition: transform 0.3s cubic-bezier(0.25, 1, 0.5, 1) !important, opacity 0.3s cubic-bezier(0.25, 1, 0.5, 1) !important, background-color 0.3s cubic-bezier(0.25, 1, 0.5, 1) !important, border-color 0.3s cubic-bezier(0.25, 1, 0.5, 1) !important, color 0.3s cubic-bezier(0.25, 1, 0.5, 1) !important;
        }

        .quick-action-btn:hover, .admin-action-btn:hover {
            transform: translateY(-2px) !important;
            background: rgba(255, 255, 255, 0.08) !important;
            border-color: rgba(255, 255, 255, 0.25) !important;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25) !important;
            color: #fff !important;
            animation: none !important;
        }

        .qa-primary, .qa-warning, .qa-success, .qa-danger, .qa-purple,
        .admin-action-settings, .admin-action-master, .admin-action-jobs, .admin-action-notifications {
            border-bottom: 1.5px solid rgba(255, 255, 255, 0.08) !important;
        }
        
        .qa-primary:hover, .qa-warning:hover, .qa-success:hover, .qa-danger:hover, .qa-purple:hover,
        .admin-action-settings:hover, .admin-action-master:hover, .admin-action-jobs:hover, .admin-action-notifications:hover {
            background: rgba(255, 255, 255, 0.08) !important;
            color: #fff !important;
            border-color: rgba(255, 255, 255, 0.25) !important;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25) !important;
        }

        :root.light-theme .quick-action-btn, 
        :root.light-theme .admin-action-btn,
        :root.light-theme .qa-primary, 
        :root.light-theme .qa-warning, 
        :root.light-theme .qa-success, 
        :root.light-theme .qa-danger, 
        :root.light-theme .qa-purple,
        :root.light-theme .admin-action-settings, 
        :root.light-theme .admin-action-master, 
        :root.light-theme .admin-action-jobs, 
        :root.light-theme .admin-action-notifications {
            background: #ffffff !important;
            color: #1e293b !important;
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
            border-bottom: 1.5px solid rgba(0, 0, 0, 0.12) !important;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.04) !important;
        }
        
        :root.light-theme .quick-action-btn:hover, 
        :root.light-theme .admin-action-btn:hover,
        :root.light-theme .qa-primary:hover, 
        :root.light-theme .qa-warning:hover, 
        :root.light-theme .qa-success:hover, 
        :root.light-theme .qa-danger:hover, 
        :root.light-theme .qa-purple:hover,
        :root.light-theme .admin-action-settings:hover, 
        :root.light-theme .admin-action-master, 
        :root.light-theme .admin-action-jobs, 
        :root.light-theme .admin-action-notifications {
            background: rgba(0, 0, 0, 0.02) !important;
            color: #0f172a !important;
            border-color: rgba(0, 0, 0, 0.18) !important;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.06) !important;
        }
    
</style>
    
    <link rel="stylesheet" href="css/all.min.css?v=2027">
</head>
<body>

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="dash-main">

        <!-- Unified Premium Header -->
                <?php $header_theme_class = 'header-theme-dashboard'; ?>
        <?php include 'includes/header.php'; ?>

        <div id="market-broker-ticker-wrap" style="display:flex;flex-direction:column;gap:6px;margin:0;">
            <div class="mkt-broker-row" id="market-broker-offers-row">
                <div style="flex:1;overflow:hidden;position:relative;direction:rtl;">
                    <div id="market-broker-ticker-track"></div>
                </div>
                <button type="button" onclick="openMarketBrokerQuickPostModal('offer')" title="تحكم شريط العروض العلوي: جدول العروض + إضافة عرض سعر + السرعة + القفل" style="width:18px;height:18px;min-width:18px;padding:0;border-radius:5px;background:#059669;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;border:none;font-size:10.5px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;z-index:6;">🎛️</button>
            </div>
            <div class="mkt-broker-row" id="market-broker-orders-row">
                <div style="flex:1;overflow:hidden;position:relative;direction:rtl;">
                    <div id="market-broker-orders-track"></div>
                </div>
                <button type="button" onclick="openMarketBrokerQuickPostModal('order')" title="تحكم شريط الطلبات السفلي: جدول الطلبات + إضافة طلب شراء + السرعة + القفل" style="width:18px;height:18px;min-width:18px;padding:0;border-radius:5px;background:#d97706;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;border:none;font-size:10.5px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;z-index:6;">🎛️</button>
            </div>
        </div>

        <!-- Ultra Premium World-Class 2-Row Command Matrix (Screens + Customize(12) + 12 Equal Luminous Shortcuts = 14 Equal Cells in 2 Rows of 7) -->
        <div class="glass-panel" id="qa-bar-container" style="margin-bottom: 14px; position: relative;" oncontextmenu="if(!event.target.closest('button, a')){ openQACustomizer(event); return false; }">
            <!-- 1. Screens Master Button -->
            <button id="screens-dropdown-btn" onclick="toggleScreensDropdown(event)" class="qa-master-btn qa-ribbon-btn" style="--btn-border-color: #a855f7; --btn-glow-color: rgba(168, 85, 247, 0.55); border-bottom: 3.5px solid #a855f7 !important;">
                <span class="btn-text" style="color: #fff;">الشاشات</span>
                <i class="fa-solid fa-chevron-down" id="screens-chevron" style="font-size: 0.72rem; transition: transform 0.3s ease; color: #c084fc;"></i>
            </button>

            <!-- 2. Customize (12) Master Button -->
            <button onclick="openQACustomizer(event)" class="qa-master-btn qa-ribbon-btn" style="--btn-border-color: #f59e0b; --btn-glow-color: rgba(245, 158, 11, 0.55); border-bottom: 3.5px solid #f59e0b !important;" title="تخصيص الـ 12 زر في الصفين">
                <span class="btn-text" id="qa-customizer-btn-label" style="color: #ffffff;">تخصيص (12)</span>
            </button>

            <!-- 3. 12 Customizable Quick Action Buttons (5 in Row 1 + 7 in Row 2) -->
            <div id="qa-single-row-grid" style="display: contents;"></div>
        </div>

        <!-- Categorized Navigation Hub Bar ("تجميع حسب الاختصاصات") -->
        <div class="glass-panel" id="category-hubs-container" style="display: none !important; padding: 14px 18px; border-radius: 18px; border: 1.5px solid rgba(56, 189, 248, 0.25); background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.8)); margin-bottom: 14px; position: relative;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <div style="font-weight: 900; font-size: 0.9rem; color: #38bdf8; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-layer-group text-sky-400" style="font-size: 1.1rem;"></i> الشاشات والتقارير مجمعة حسب الاختصاص والقطاع 📂
                    <span style="font-size: 0.72rem; font-weight: 700; color: #94a3b8; margin-right: 6px;">(اختر أي تخصص لتنسدل لك قائمه المخصصة فوراً)</span>
                </div>
            </div>
            
            <!-- 7 Domain Specialty Buttons Grid -->
            <div id="category-hubs-grid" style="display: flex; gap: 10px; width: 100%; overflow-x: auto; padding-bottom: 8px; scrollbar-width: none; -ms-overflow-style: none;">
                <!-- Rendered dynamically by renderCategoryHubs() -->
            </div>
        </div>

        <!-- Floating Popover Container for Domain Dropdowns -->
        <div id="category-dropdown-popover" style="display: none; position: fixed; width: 380px; max-width: 94vw; background: var(--bg-color, #070a12) !important; border: 2px solid #38bdf8; border-radius: 18px; box-shadow: 0 25px 95px rgba(0,0,0,0.99), 0 0 45px rgba(56, 189, 248, 0.55); z-index: 999999; padding: 14px; direction: rtl;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; margin-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.12);">
                <div id="cat-popover-title" style="font-size: 0.95rem; font-weight: 900; color: #38bdf8; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-briefcase"></i> قائمة الاختصاص
                </div>
                <button onclick="closeCategoryDropdown()" style="background: rgba(248, 113, 113, 0.25); border: 1.5px solid #f87171; color: #f87171; border-radius: 10px; padding: 4px 12px; font-size: 0.82rem; font-weight: 900; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: all 0.2s;" onmouseover="this.style.background='rgba(248,113,113,0.4)';" onmouseout="this.style.background='rgba(248,113,113,0.25)';">
                    <span>إغلاق</span> &times;
                </button>
            </div>
            <div id="cat-popover-list" style="max-height: 420px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; padding-left: 4px;">
                <!-- Rendered via JS -->
            </div>
        </div>

        <!-- 100% OPAQUE Floating Popover Menu for Uncustomized Screens (Zero Transparency, Covers Top Bar) -->
        <div id="screens-dropdown-menu" style="display: none; position: fixed; width: 500px; max-width: 96vw; background: var(--bg-color, #070a12) !important; border: 2px solid #a855f7; border-radius: 18px; box-shadow: 0 25px 95px rgba(0,0,0,0.99), 0 0 45px rgba(168, 85, 247, 0.55); z-index: 999999; padding: 14px; direction: rtl;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; margin-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.12);">
                <div style="font-size: 0.98rem; font-weight: 900; color: #c084fc; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-desktop" style="font-size: 1.1rem; color: #a855f7;"></i> بقية الشاشات (غير المخصصة)
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span id="screens-count-badge" style="background: rgba(168, 85, 247, 0.35); color: #e9d5ff; border: 1px solid #c084fc; font-size: 0.75rem; padding: 2px 10px; border-radius: 10px; font-weight: 900;">0</span>
                    <button onclick="toggleScreensDropdown(event)" style="background: rgba(248, 113, 113, 0.25); border: 1.5px solid #f87171; color: #f87171; border-radius: 10px; padding: 5px 14px; font-size: 0.88rem; font-weight: 900; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 4px 12px rgba(248,113,113,0.3); display: flex; align-items: center; gap: 6px;" title="إغلاق القائمة" onmouseover="this.style.background='rgba(248,113,113,0.4)'; this.style.transform='scale(1.05)';" onmouseout="this.style.background='rgba(248,113,113,0.25)'; this.style.transform='scale(1)';">
                        <span>إغلاق</span>
                        <i class="fa-solid fa-xmark" style="font-size: 1rem;"></i>
                    </button>
                </div>
            </div>
            <div style="margin-bottom: 8px;">
                <input type="text" id="screens-search-input" oninput="filterScreensDropdown()" placeholder="🔍 ابحث عن أي شاشة..." style="width: 100%; box-sizing: border-box; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.22); border-radius: 8px; padding: 6px 12px; color: #fff; font-size: 0.82rem; font-family: 'Tajawal', sans-serif; outline: none;">
            </div>
            
            <!-- Compact Sorting Controls Bar -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; gap: 6px; background: rgba(255,255,255,0.03); padding: 4px 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.08);">
                <div style="font-size: 0.75rem; font-weight: 800; color: #c084fc; display: flex; align-items: center; gap: 5px;">
                    <i class="fa-solid fa-arrow-down-short-wide"></i> ترتيب:
                </div>
                <div style="display: flex; gap: 4px;">
                    <button onclick="sortScreensDropdown('default')" id="sort-btn-default" style="background: rgba(168, 85, 247, 0.35); border: 1px solid #c084fc; color: #ffffff; font-size: 0.72rem; font-weight: 800; padding: 3px 10px; border-radius: 6px; cursor: pointer; transition: all 0.2s ease;">الافتراضي</button>
                    <button onclick="sortScreensDropdown('alphabetical')" id="sort-btn-alphabetical" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); color: #94a3b8; font-size: 0.72rem; font-weight: 800; padding: 3px 10px; border-radius: 6px; cursor: pointer; transition: all 0.2s ease;">أبجديًا (أ-ي)</button>
                    <button onclick="sortScreensDropdown('category')" id="sort-btn-category" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); color: #94a3b8; font-size: 0.72rem; font-weight: 800; padding: 3px 10px; border-radius: 6px; cursor: pointer; transition: all 0.2s ease;">حسب الفئة</button>
                </div>
            </div>

            <div id="screens-dropdown-list" style="max-height: calc(100vh - 140px); overflow-y: auto; display: flex; flex-direction: column; gap: 8px; padding-left: 4px;">
                <!-- Rendered dynamically via JS -->
            </div>
        </div>

        <div id="new-unified-dashboard-design" class="space-y-5 pb-10">
            <!-- الشريط الموحد الشامل المثبت في الأعلى (الفترات الزمنية + العملة + الصرف + المحاور البروفيسور + الإيجاز الذكي في سطر واحد + 10 مؤشرات بخط مكبر وارتفاع أوضح) -->
            <style>
                html .dash-main { gap: 4px !important; padding-top: 4px !important; }
                html .dash-main > .unified-header, html .unified-header { margin-bottom: 2px !important; }
                html #qa-bar-container, html[data-theme] #qa-bar-container, html.light-theme #qa-bar-container { margin-top: 0 !important; margin-bottom: 4px !important; }
                #qa-bar-container .qa-ribbon-btn .btn-text { font-size: 14.5px !important; font-weight: 900 !important; }
                #new-unified-dashboard-design { display: flex !important; flex-direction: column !important; gap: 6px !important; }
                #new-unified-dashboard-design > * { margin-top: 0 !important; margin-bottom: 0 !important; }
                #new-unified-dashboard-design #sec-calm,
                #new-unified-dashboard-design #sec-global-ai-innovations,
                #new-unified-dashboard-design #sec-flow,
                #new-unified-dashboard-design #sec-ops-audit { gap: 14px !important; margin-top: 0 !important; }
                @keyframes brkNewPulse { 0%, 100% { box-shadow: 0 0 12px rgba(253,224,71,0.75), inset 0 0 8px rgba(253,224,71,0.35); border-color: #fde047 !important; } 50% { box-shadow: 0 0 22px rgba(16,185,129,0.95), inset 0 0 12px rgba(253,224,71,0.55); border-color: #34d399 !important; } }
                .broker-new-item-pill { animation: none; /* cpu-opt */ background: linear-gradient(135deg, rgba(6,95,70,0.96), rgba(15,23,42,0.96)) !important; border-width: 2px !important; }
                @keyframes execPrismGlow { 0%, 100% { box-shadow: 0 10px 26px rgba(245,158,11,0.38), 0 4px 14px rgba(253,224,71,0.32), 0 0 20px rgba(251,191,36,0.52), inset 0 1.5px 3px rgba(255,255,255,0.62); border-color: #fde047; } 50% { box-shadow: 0 12px 32px rgba(245,158,11,0.52), 0 6px 18px rgba(253,224,71,0.45), 0 0 28px rgba(254,240,138,0.72), inset 0 2px 4px rgba(255,255,255,0.75); border-color: #fef08a; } }
                html, body { overflow-x: clip !important; }
                html .dash-main, body .dash-main, #new-unified-dashboard-design { overflow: visible !important; gap: 6px !important; padding-top: 4px !important; }
                #exec-sticky-command-bar, html.light-theme #exec-sticky-command-bar { position: -webkit-sticky !important; position: sticky !important; top: 0px !important; left: auto !important; right: auto !important; width: 100% !important; z-index: 99990 !important; margin-top: 0 !important; margin-bottom: 0 !important; padding: 7px 12px; border-radius: 16px; border: 2.5px solid #fef08a !important; background: linear-gradient(135deg, #78350f 0%, #b45309 28%, #d97706 50%, #b45309 72%, #78350f 100%) !important; box-shadow: 0 8px 22px rgba(0,0,0,0.55) !important; display: flex !important; align-items: center !important; justify-content: space-between !important; gap: 6px; flex-wrap: nowrap !important; overflow: hidden !important; direction: rtl !important; box-sizing: border-box !important; transform: translateZ(0); backface-visibility: hidden; }
                #exec-sticky-command-bar.is-pinned-fixed, html.light-theme #exec-sticky-command-bar.is-pinned-fixed { position: fixed !important; top: 2px !important; z-index: 99995 !important; margin: 0 !important; }
                #exec-sticky-command-bar .exec-period-pill { position: relative; overflow: hidden; height: 36px; padding: 0 11px; border-radius: 11px; font-size: 13.5px !important; font-weight: 900 !important; display: inline-flex; align-items: center; justify-content: center; white-space: nowrap; cursor: pointer; transition: border-color 0.2s ease, box-shadow 0.2s ease; border: 1.5px solid rgba(253,224,71,0.6); background: linear-gradient(180deg, rgba(15,23,42,0.94) 0%, rgba(2,6,23,0.98) 100%); color: #fef08a !important; -webkit-text-fill-color: #fef08a !important; box-shadow: 0 4px 10px rgba(0,0,0,0.4), inset 0 1px 1px rgba(255,255,255,0.22); }
                #exec-sticky-command-bar .exec-period-pill::after, #exec-sticky-command-bar .exec-royal-action::after { content: ''; position: absolute; inset: 0; background: linear-gradient(120deg, transparent 25%, rgba(255,255,255,0.28) 50%, transparent 75%); transform: translateX(-120%); transition: transform 0.55s ease; pointer-events: none; }
                #exec-sticky-command-bar .exec-period-pill:hover { transform: translateY(-1px); border-color: #38bdf8; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; box-shadow: 0 6px 16px rgba(56,189,248,0.45), 0 0 12px rgba(56,189,248,0.5); }
                #exec-sticky-command-bar .exec-period-pill:hover::after, #exec-sticky-command-bar .exec-royal-action:hover::after { transform: translateX(120%); }
                #exec-sticky-command-bar .exec-period-pill.active-period { background: linear-gradient(135deg, #059669 0%, #0284c7 100%) !important; border: 2px solid #fde047 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; box-shadow: 0 6px 16px rgba(16,185,129,0.5), 0 0 14px rgba(253,224,71,0.45), inset 0 1px 2px rgba(255,255,255,0.45) !important; }
                #exec-sticky-command-bar .exec-royal-action { position: relative; overflow: hidden; height: 36px !important; padding: 0 10px !important; border-radius: 11px !important; font-size: 12.8px !important; font-weight: 900 !important; display: inline-flex; align-items: center; justify-content: center; gap: 5px !important; white-space: nowrap; cursor: pointer; transition: filter 0.2s ease, box-shadow 0.2s ease !important; box-shadow: 0 5px 14px rgba(0,0,0,0.45), inset 0 1px 2px rgba(255,255,255,0.32) !important; }
                #exec-sticky-command-bar .exec-royal-action:hover { transform: translateY(-1px) !important; filter: brightness(1.15); box-shadow: 0 8px 18px rgba(0,0,0,0.55), 0 0 14px rgba(253,224,71,0.55) !important; }
                #dash-today-harvest-strip .exec-jewel-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; }
                @media (max-width: 900px) { #dash-today-harvest-strip .exec-jewel-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
                #dash-today-harvest-strip .exec-jewel-btn, html.light-theme #dash-today-harvest-strip .exec-jewel-btn { position: relative; overflow: hidden; min-height: 78px; padding: 8px 12px 9px !important; border-radius: 12px !important; border-top: 1px solid rgba(255,255,255,0.14) !important; border-left: 1px solid rgba(255,255,255,0.12) !important; border-bottom: 1px solid rgba(255,255,255,0.12) !important; border-right: 5px solid var(--jewel-accent, #38bdf8) !important; display: flex; flex-direction: column !important; align-items: stretch !important; justify-content: space-between !important; gap: 6px; transition: transform 0.24s ease !important, opacity 0.24s ease !important, background-color 0.24s ease !important, border-color 0.24s ease !important, color 0.24s ease !important; box-shadow: inset 0 1px 0 rgba(255,255,255,0.15), 0 8px 20px rgba(0,0,0,0.42) !important; }
                #dash-today-harvest-strip .exec-jewel-btn::after { content: ''; position: absolute; inset: 0; background: linear-gradient(120deg, transparent 25%, rgba(255,255,255,0.16) 50%, transparent 75%); transform: translateX(-120%); transition: transform 0.55s ease; pointer-events: none; }
                #dash-today-harvest-strip .exec-jewel-btn:hover { transform: translateY(-2px) !important; filter: brightness(1.12); z-index: 5; box-shadow: 0 12px 26px rgba(0,0,0,0.6), 0 0 18px var(--jewel-glow, rgba(56,189,248,0.55)) !important; }
                #dash-today-harvest-strip .exec-jewel-btn:hover::after { transform: translateX(120%); }
                #dash-today-harvest-strip .exec-jewel-lbl, html.light-theme #dash-today-harvest-strip .exec-jewel-lbl,
                #dash-today-harvest-strip .exec-jewel-lbl-ap, html.light-theme #dash-today-harvest-strip .exec-jewel-lbl-ap { font-size: 13.5px !important; font-weight: 900 !important; color: #e2e8f0 !important; -webkit-text-fill-color: #e2e8f0 !important; white-space: nowrap; letter-spacing: 0; display: flex; align-items: center; justify-content: space-between; width: 100%; padding: 0 2px; }
                #dash-today-harvest-strip .exec-jewel-val { width: 100%; font-size: 19.5px !important; font-weight: 900 !important; white-space: nowrap; text-shadow: 0 2px 8px rgba(0,0,0,0.65); padding: 5px 10px; border-radius: 8px; background: rgba(2, 6, 23, 0.86); border: 1px solid rgba(255, 255, 255, 0.14); box-shadow: inset 0 2px 6px rgba(0,0,0,0.75); display: flex; align-items: baseline; justify-content: center; gap: 4px; letter-spacing: 0.3px; }
                #dash-today-harvest-strip .exec-jewel-val .exec-unit-tag { font-size: 11px !important; font-weight: 800 !important; opacity: 0.92; letter-spacing: 0; }
                @keyframes mktBrokerMarquee { 0% { transform: translate3d(0, 0, 0); } 100% { transform: translate3d(50%, 0, 0); } }
                .glass-panel, .exec-jewel-btn { backdrop-filter: none !important; -webkit-backdrop-filter: none !important; }
                .brk-new-electro-badge { display: inline-flex; align-items: center; gap: 3px; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; padding: 1px 8px; border-radius: 999px; border: 1.5px solid #fde047; background: linear-gradient(135deg, #e11d48, #be123c) !important; font-weight: 900 !important; animation: none !important; }
                .broker-new-item-pill { animation: none !important; background: linear-gradient(135deg, rgba(6,95,70,0.96), rgba(15,23,42,0.96)) !important; border-width: 2px !important; }
                html body #market-broker-ticker-wrap#market-broker-ticker-wrap, html.light-theme body #market-broker-ticker-wrap#market-broker-ticker-wrap { position: relative; width: 100% !important; max-width: 100% !important; box-sizing: border-box !important; background: #52646c !important; border: 1px solid #475569 !important; border-radius: 7px !important; padding: 0 !important; margin: 0 !important; display: flex; flex-direction: column !important; gap: 6px !important; overflow: hidden; box-shadow: 0 3px 10px rgba(2,6,23,0.45) !important; direction: rtl; }
                .global-sidebar .sidebar-footer .sidebar-logout, #magaiees-master-back-btn, #sl-sovereign-law1-bar [onclick*="directExitToMain"] { display: none !important; }
                html body #market-broker-ticker-wrap#market-broker-ticker-wrap.is-broker-hidden, html.light-theme body #market-broker-ticker-wrap#market-broker-ticker-wrap.is-broker-hidden { display: none !important; }
                #market-broker-ticker-wrap > .mkt-broker-row { display: flex; align-items: center !important; gap: 6px !important; overflow: hidden; width: 100% !important; min-height: 0 !important; height: auto !important; max-height: none !important; padding: 1px 8px !important; margin: 0 !important; border-radius: 0 !important; border: none !important; line-height: 1.1 !important; box-sizing: border-box !important; }
                #market-broker-ticker-wrap > .mkt-broker-row.is-row-hidden { display: none !important; }
                #market-broker-offers-row, html.light-theme #market-broker-offers-row, html.light-theme[data-light-tone] #market-broker-offers-row { background: #0c1d47 !important; border: none !important; box-shadow: none !important; }
                #market-broker-orders-row, html.light-theme #market-broker-orders-row, html.light-theme[data-light-tone] #market-broker-orders-row { background: #0a2536 !important; border: none !important; box-shadow: none !important; }
                #market-broker-ticker-track > div > span[onclick], #market-broker-orders-track > div > span[onclick] { padding: 0 10px !important; border: none !important; line-height: 1.15 !important; }
                html.light-theme body #market-broker-ticker-wrap#market-broker-ticker-wrap { border: 2.5px solid var(--lt-border, #5c2808) !important; box-shadow: 0 4px 12px rgba(15,23,42,0.22) !important; }
                #market-broker-ticker-wrap .brk-pill-hdr-off, html.light-theme #market-broker-ticker-wrap .brk-pill-hdr-off { color: #fde047 !important; -webkit-text-fill-color: #fde047 !important; font-weight: 900 !important; }
                #market-broker-ticker-wrap .brk-off-txt-0, html.light-theme #market-broker-ticker-wrap .brk-off-txt-0 { color: #fdba74 !important; -webkit-text-fill-color: #fdba74 !important; }
                #market-broker-ticker-wrap .brk-off-txt-1, html.light-theme #market-broker-ticker-wrap .brk-off-txt-1 { color: #fde047 !important; -webkit-text-fill-color: #fde047 !important; }
                #market-broker-ticker-wrap .brk-off-txt-2, html.light-theme #market-broker-ticker-wrap .brk-off-txt-2 { color: #fb923c !important; -webkit-text-fill-color: #fb923c !important; }
                #market-broker-ticker-wrap .brk-off-time, html.light-theme #market-broker-ticker-wrap .brk-off-time { color: #fef08a !important; -webkit-text-fill-color: #fef08a !important; }
                #market-broker-ticker-wrap .brk-pill-hdr-ord, html.light-theme #market-broker-ticker-wrap .brk-pill-hdr-ord { color: #bbf7d0 !important; -webkit-text-fill-color: #bbf7d0 !important; font-weight: 900 !important; }
                #market-broker-ticker-wrap .brk-ord-txt-0, html.light-theme #market-broker-ticker-wrap .brk-ord-txt-0 { color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
                #market-broker-ticker-wrap .brk-ord-txt-1, html.light-theme #market-broker-ticker-wrap .brk-ord-txt-1 { color: #dcfce7 !important; -webkit-text-fill-color: #dcfce7 !important; }
                #market-broker-ticker-wrap .brk-ord-txt-2, html.light-theme #market-broker-ticker-wrap .brk-ord-txt-2 { color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
                #market-broker-ticker-wrap .brk-ord-time, html.light-theme #market-broker-ticker-wrap .brk-ord-time { color: #bbf7d0 !important; -webkit-text-fill-color: #bbf7d0 !important; font-weight: 900 !important; }
                html:not(.light-theme) #dash-today-harvest-strip .exec-jewel-btn { background: var(--jewel-bg, linear-gradient(165deg, rgba(14,165,233,0.16), rgba(15,23,42,0.96))) !important; }
                html.light-theme body #new-unified-dashboard-design #dash-today-harvest-strip { background: linear-gradient(150deg, var(--lt-card-1, #fffdf8) 0%, var(--lt-card-2, #f1e4ce) 100%) !important; border: 2.5px solid var(--lt-border, #4a2106) !important; box-shadow: 0 8px 22px rgba(15,23,42,0.18), inset 0 1px 0 #ffffff !important; }
                html.light-theme body #new-unified-dashboard-design #dash-today-harvest-strip .exec-jewel-btn { border: 2.5px solid var(--lt-border, #4a2106) !important; border-right: 6px solid var(--jewel-accent, #0284c7) !important; background: #ffffff !important; box-shadow: 0 4px 12px rgba(15,23,42,0.16) !important; }
                html.light-theme body #new-unified-dashboard-design #dash-today-harvest-strip .exec-jewel-btn:hover { border-color: var(--jewel-accent, #0284c7) !important; border-right: 6px solid var(--jewel-accent, #0284c7) !important; box-shadow: 0 8px 20px rgba(15,23,42,0.24) !important; }
                html.light-theme body #new-unified-dashboard-design #dash-today-harvest-strip .exec-jewel-lbl, html.light-theme body #new-unified-dashboard-design #dash-today-harvest-strip .exec-jewel-lbl > span, html.light-theme body #new-unified-dashboard-design #dash-today-harvest-strip .exec-jewel-lbl-ap, html.light-theme body #new-unified-dashboard-design #dash-today-harvest-strip .exec-jewel-lbl-ap > span { color: #000000 !important; -webkit-text-fill-color: #000000 !important; font-weight: 900 !important; }
                html.light-theme body #new-unified-dashboard-design #dash-today-harvest-strip .exec-jewel-lbl small, html.light-theme body #new-unified-dashboard-design #dash-today-harvest-strip .exec-jewel-lbl-ap small { background: var(--lt-sub-2, #e5d8c0) !important; color: #000000 !important; -webkit-text-fill-color: #000000 !important; border: 1.5px solid var(--lt-border, #4a2106) !important; padding: 1px 6px !important; border-radius: 6px !important; font-weight: 900 !important; }
                html.light-theme body #new-unified-dashboard-design #dash-today-harvest-strip .exec-jewel-val, html.prof-ux-2027.light-theme body #new-unified-dashboard-design #dash-today-harvest-strip .exec-jewel-val { background: var(--lt-card-1, #fcfaf5) !important; border: 2px solid var(--lt-border, #4a2106) !important; font-weight: 900 !important; text-shadow: none !important; box-shadow: inset 0 1px 2px rgba(0,0,0,0.08) !important; }
                html.light-theme body #new-unified-dashboard-design #dash-today-harvest-strip .exec-unit-tag { color: #000000 !important; -webkit-text-fill-color: #000000 !important; font-weight: 900 !important; opacity: 1 !important; }
                html:not(.light-theme) #strip-sal-amt, html:not(.light-theme) #strip-ap-amt { color: #fde047 !important; -webkit-text-fill-color: #fde047 !important; }
                html:not(.light-theme) #strip-pur-amt { color: #fbbf24 !important; -webkit-text-fill-color: #fbbf24 !important; }
                html:not(.light-theme) #strip-liq-amt { color: #6ee7b7 !important; -webkit-text-fill-color: #6ee7b7 !important; }
                html:not(.light-theme) #strip-prf-amt, html:not(.light-theme) #strip-ar-amt { color: #4ade80 !important; -webkit-text-fill-color: #4ade80 !important; }
                html:not(.light-theme) #strip-exp-amt { color: #fda4af !important; -webkit-text-fill-color: #fda4af !important; }
                html:not(.light-theme) #stock-mkt-val { color: #7dd3fc !important; -webkit-text-fill-color: #7dd3fc !important; }
                html:not(.light-theme) #strip-in-amt { color: #5eead4 !important; -webkit-text-fill-color: #5eead4 !important; }
                html:not(.light-theme) #strip-out-amt { color: #fb7185 !important; -webkit-text-fill-color: #fb7185 !important; }
                html.light-theme body #new-unified-dashboard-design #strip-sal-amt { color: #0c4a6e !important; -webkit-text-fill-color: #0c4a6e !important; border-color: #0369a1 !important; }
                html.light-theme body #new-unified-dashboard-design #strip-pur-amt { color: #7c2d12 !important; -webkit-text-fill-color: #7c2d12 !important; border-color: #b45309 !important; }
                html.light-theme body #new-unified-dashboard-design #strip-liq-amt { color: #022c22 !important; -webkit-text-fill-color: #022c22 !important; border-color: #047857 !important; }
                html.light-theme body #new-unified-dashboard-design #strip-prf-amt { color: #064e3b !important; -webkit-text-fill-color: #064e3b !important; border-color: #065f46 !important; }
                html.light-theme body #new-unified-dashboard-design #strip-exp-amt { color: #881337 !important; -webkit-text-fill-color: #881337 !important; border-color: #be123c !important; }
                html.light-theme body #new-unified-dashboard-design #stock-mkt-val { color: #1e3a8a !important; -webkit-text-fill-color: #1e3a8a !important; border-color: #1d4ed8 !important; }
                html.light-theme body #new-unified-dashboard-design #strip-in-amt { color: #115e59 !important; -webkit-text-fill-color: #115e59 !important; border-color: #0f766e !important; }
                html.light-theme body #new-unified-dashboard-design #strip-out-amt { color: #7f1d1d !important; -webkit-text-fill-color: #7f1d1d !important; border-color: #b91c1c !important; }
                html.light-theme body #new-unified-dashboard-design #strip-ap-amt { color: #4c1d95 !important; -webkit-text-fill-color: #4c1d95 !important; border-color: #6d28d9 !important; }
                html.light-theme body #new-unified-dashboard-design #strip-ar-amt { color: #064e3b !important; -webkit-text-fill-color: #064e3b !important; border-color: #047857 !important; }
                #exec-sticky-command-bar > div { display: flex !important; align-items: center !important; flex-shrink: 0 !important; gap: 5px !important; }
                #market-broker-ticker-track, #market-broker-orders-track { display: inline-flex !important; width: max-content !important; align-items: center !important; gap: 18px !important; white-space: nowrap !important; direction: rtl !important; will-change: transform; backface-visibility: hidden; -webkit-font-smoothing: antialiased; animation: mktBrokerMarquee var(--brk-dur, 78s) linear infinite !important; }
                #market-broker-offers-row:hover #market-broker-ticker-track, #market-broker-orders-row:hover #market-broker-orders-track { animation-play-state: paused !important; }
                html.prof-ux-2027 #exec-sticky-command-bar .exec-period-pill:hover,
                html.prof-ux-2027 #exec-sticky-command-bar .exec-royal-action:hover,
                html.prof-ux-2027 #dash-today-harvest-strip .exec-jewel-btn:hover { transform: translateY(-1px) scale(1) !important; }
                html.prof-ux-2027 #exec-sticky-command-bar .exec-period-pill.active-period { transform: none !important; }
                html.prof-ux-2027 #exec-sticky-command-bar .exec-period-extra { display: none !important; }
                html.prof-ux-2027.show-all-periods #exec-sticky-command-bar .exec-period-extra { display: inline-flex !important; }
                html.prof-zen-mode #sec-calm, html.prof-zen-mode #sec-global-ai-innovations, html.prof-zen-mode #sec-flow, html.prof-zen-mode #sec-ops-audit { display: none !important; }
            </style>
            <div id="exec-sticky-command-placeholder" style="display:none;height:62px;"></div>
            <div id="exec-sticky-command-bar">
                <div id="exec-summary-period-pills" class="flex items-center gap-1.5 shrink-0">
                    <button type="button" onclick="cycleExecutiveSummaryPeriod(0)" class="exec-period-pill active-period">اليوم</button>
                    <button type="button" onclick="cycleExecutiveSummaryPeriod(1)" class="exec-period-pill">أسبوع</button>
                    <button type="button" onclick="cycleExecutiveSummaryPeriod(2)" class="exec-period-pill exec-period-extra">10 أيام</button>
                    <button type="button" onclick="cycleExecutiveSummaryPeriod(3)" class="exec-period-pill">شهر</button>
                    <button type="button" onclick="cycleExecutiveSummaryPeriod(4)" class="exec-period-pill exec-period-extra">3 شهور</button>
                    <button type="button" onclick="cycleExecutiveSummaryPeriod(5)" class="exec-period-pill exec-period-extra">6 شهور</button>
                    <button type="button" onclick="cycleExecutiveSummaryPeriod(6)" class="exec-period-pill exec-period-extra">9 شهور</button>
                    <button type="button" onclick="cycleExecutiveSummaryPeriod(7)" class="exec-period-pill">سنة</button>
                    <button type="button" id="exec-more-periods-btn" onclick="document.documentElement.classList.toggle('show-all-periods');this.textContent=document.documentElement.classList.contains('show-all-periods')?'− طي':'+ فترات';" class="exec-period-pill" title="إظهار أو طي الفترات الإضافية" style="padding:0 10px;border-color:#38bdf8;">+ فترات</button>
                </div>
                <div class="flex items-center gap-1.5 shrink-0" style="margin-right: auto;">
                    <button type="button" id="exec-calm-pulse-badge" onclick="const liq=(document.getElementById('strip-liq-amt')?.innerText||'').trim(),ap=(document.getElementById('strip-ap-amt')?.innerText||'').trim(),ar=(document.getElementById('strip-ar-amt')?.innerText||'').trim();const msg='مؤشر السيولة المطمئنة: يقارن سيولتك الجاهزة ('+liq+') بمستحقات الموردين ('+ap+') وديون العملاء ('+ar+') ليضمن أن نقديتك تغطي التزاماتك بأمان تام.';if(typeof _speakDashArabicText==='function')_speakDashArabicText(msg);const sec=document.getElementById('sec-flow')||document.getElementById('sec-calm');if(sec)sec.scrollIntoView({behavior:'smooth',block:'center'});" class="exec-royal-action" style="background:linear-gradient(135deg,#065f46,#047857);border:1.5px solid #6ee7b7;color:#fff!important;-webkit-text-fill-color:#fff!important;cursor:pointer;" title="مؤشر النبض المالي: اضغط لسماع وعرض مقارنة السيولة الفورية بمستحقات الموردين">🟢 سيولة مطمئنة</button>
                    <button type="button" id="prof-zen-btn" onclick="toggleProfZenMode()" class="exec-royal-action" style="background:linear-gradient(135deg,#4f46e5,#312e81);border:1.5px solid #c7d2fe;color:#fff!important;-webkit-text-fill-color:#fff!important;" title="وضع التركيز الهادئ: طي الأقسام السفلية لراحة العين">🧘 تركيز</button>
                    <button onclick="toggleCurrency()" id="currency-btn" class="exec-royal-action" style="background:linear-gradient(135deg,#10b981,#059669)!important;border:1.5px solid #fef08a!important;color:#fff!important;-webkit-text-fill-color:#fff!important;" title="اضغط للتحويل الفوري بين الجنيه والدولار">
                        <span>🪙</span><span id="currency-btn-txt">جنيه ⇄</span>
                    </button>
                    <button type="button" class="exec-royal-action num-font" style="background:linear-gradient(135deg,#1e293b,#0f172a);border:1.5px solid #38bdf8;color:#fff!important;-webkit-text-fill-color:#fff!important;" title="سعر الصرف المعتمد">
                        <span>💱 الصرف:</span>
                        <strong id="hdr-usd" style="color:#fde047!important;-webkit-text-fill-color:#fde047!important;font-size:14.5px!important;font-weight:900;">600</strong>
                    </button>
                    <button type="button" id="exec-live-mic-btn" onclick="startLiveDashVoiceAdvisor()" class="exec-royal-action" style="background:linear-gradient(135deg,#0284c7,#1e40af);border:1.5px solid #fde047;color:#fff!important;-webkit-text-fill-color:#fff!important;" title="اضغط ليضيء المايك مباشرة وتتحاور بالصوت الدافئ المفتوح بدون نوافذ">
                        <span>🎙️</span><span>مايك البروفيسور</span>
                    </button>
                    <button onclick="speakExecutiveDashboardBriefing()" class="exec-royal-action" style="background:linear-gradient(135deg,#0d9488,#065f46);border:1.5px solid #6ee7b7;color:#fff!important;-webkit-text-fill-color:#fff!important;" title="قراءة صوتية ذكية فورية لأهم مؤشرات الشاشة">
                        <span>🔊</span><span>إيجاز ذكي</span>
                    </button>
                    <button type="button" id="prof-ux-revert-btn" onclick="toggleProfUx2027()" class="exec-royal-action" style="background:linear-gradient(135deg,#334155,#0f172a);border:1.5px solid #fde047;color:#fde047!important;-webkit-text-fill-color:#fde047!important;" title="تفعيل أو التراجع الفوري عن تحسينات البروفيسور الخمسة">↩️ تراجع</button>
                    <span id="hdr-rebar" style="display:none;">420,000</span>
                    <span id="hdr-rebar-unit" style="display:none;">ج.س</span>
                </div>
            </div>
            <script>
                (function(){
                    const applyUx = (on) => {
                        document.documentElement.classList.toggle('prof-ux-2027', on);
                        const b = document.getElementById('prof-ux-revert-btn');
                        const mb = document.getElementById('exec-more-periods-btn');
                        const cb = document.getElementById('exec-calm-pulse-badge');
                        const zb = document.getElementById('prof-zen-btn');
                        if (b) b.innerHTML = on ? '↩️ تراجع' : '✨ تفعيل التحسينات';
                        if (mb) mb.style.display = 'inline-flex';
                        if (cb) cb.style.display = 'inline-flex';
                        if (zb) zb.style.display = 'inline-flex';
                        if (!on) document.documentElement.classList.remove('prof-zen-mode');
                    };
                    window.toggleProfUx2027 = function() {
                        const next = !document.documentElement.classList.contains('prof-ux-2027');
                        localStorage.setItem('magaiees_prof_ux_2027', next ? '1' : '0');
                        applyUx(next);
                    };
                    window.toggleProfZenMode = function() {
                        const z = document.documentElement.classList.toggle('prof-zen-mode');
                        const zb = document.getElementById('prof-zen-btn');
                        if (zb) zb.innerHTML = z ? '📂 إظهار الأقسام' : '🧘 تركيز';
                    };
                    window.updateCalmPulseBadge = function() {
                        const cb = document.getElementById('exec-calm-pulse-badge');
                        if (!cb) return;
                        const liq = parseFloat(document.getElementById('strip-liq-amt')?.getAttribute('data-sdg') || '3450000') || 0;
                        const ap = parseFloat(document.getElementById('strip-ap-amt')?.getAttribute('data-sdg') || '290000') || 1;
                        const ratio = liq / Math.max(1, ap);
                        if (ratio >= 1.5) { cb.innerHTML = '🟢 سيولة مطمئنة'; cb.style.background = 'linear-gradient(135deg,#065f46,#047857)'; }
                        else if (ratio >= 0.8) { cb.innerHTML = '🟡 سيولة متوازنة'; cb.style.background = 'linear-gradient(135deg,#b45309,#92400e)'; }
                        else { cb.innerHTML = '🔴 تنبيه التزامات'; cb.style.background = 'linear-gradient(135deg,#be123c,#881337)'; }
                    };
                    applyUx(localStorage.getItem('magaiees_prof_ux_2027') !== '0');
                    setTimeout(window.updateCalmPulseBadge, 200);
                })();
            </script>
            <div id="dash-today-harvest-strip" class="bento-card" style="padding: 12px 16px; border-radius: 18px; border: 1.5px solid rgba(56,189,248,0.45); background: linear-gradient(180deg, rgba(9,15,29,0.98) 0%, rgba(4,8,18,0.99) 100%); box-shadow: 0 18px 42px rgba(0,0,0,0.68), inset 0 1px 2px rgba(255,255,255,0.18);">
                <div class="exec-jewel-grid num-font">
                    <button type="button" onclick="switchSalesProfitCardView('day')" class="inner-box exec-jewel-btn text-right cursor-pointer" style="--jewel-accent: #0284c7; --jewel-glow: rgba(56,189,248,0.78); --jewel-bg: linear-gradient(165deg, rgba(14,165,233,0.16), rgba(15,23,42,0.96));">
                        <span class="exec-jewel-lbl"><span>📈 المبيعات</span><small style="font-size:10px;color:#38bdf8;">مؤشر حي</small></span>
                        <strong class="exec-jewel-val text-amber-300" id="strip-sal-amt" data-sdg="1890000">1,890,000 <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                    <button type="button" onclick="switchSalesProfitCardView('purchases', document.getElementById('btn-card2-purchases'))" class="inner-box exec-jewel-btn text-right cursor-pointer" style="--jewel-accent: #d97706; --jewel-glow: rgba(245,158,11,0.75); --jewel-bg: linear-gradient(165deg, rgba(251,191,36,0.15), rgba(15,23,42,0.96));">
                        <span class="exec-jewel-lbl"><span>🛒 المشتريات</span><small style="font-size:10px;color:#fbbf24;">توريد</small></span>
                        <strong class="exec-jewel-val text-amber-300" id="strip-pur-amt" data-sdg="1420000">1,420,000 <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                    <button type="button" onclick="switchLiquidityCardView('overview')" class="inner-box exec-jewel-btn text-right cursor-pointer" style="--jewel-accent: #059669; --jewel-glow: rgba(52,211,153,0.75); --jewel-bg: linear-gradient(165deg, rgba(52,211,153,0.16), rgba(15,23,42,0.96));">
                        <span class="exec-jewel-lbl"><span>💎 السيولة</span><small style="font-size:10px;color:#6ee7b7;">خزن وبنوك</small></span>
                        <strong class="exec-jewel-val text-emerald-300" id="strip-liq-amt" data-sdg="3450000">3,450,000 <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                    <button type="button" onclick="switchSalesProfitCardView('day')" class="inner-box exec-jewel-btn text-right cursor-pointer" id="strip-prf-card" style="--jewel-accent: #15803d; --jewel-glow: rgba(16,185,129,0.8); --jewel-bg: linear-gradient(165deg, rgba(16,185,129,0.18), rgba(15,23,42,0.96));">
                        <span class="exec-jewel-lbl" id="strip-prf-lbl"><span>🏆 الأرباح</span><small style="font-size:10px;color:#4ade80;">صافي</small></span>
                        <strong class="exec-jewel-val text-emerald-400" id="strip-prf-amt" data-sdg="348000">+348,000 <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                    <button type="button" onclick="switchCashflowCardView('ai_cf')" class="inner-box exec-jewel-btn text-right cursor-pointer" style="--jewel-accent: #e11d48; --jewel-glow: rgba(244,63,94,0.75); --jewel-bg: linear-gradient(165deg, rgba(244,63,94,0.16), rgba(15,23,42,0.96));">
                        <span class="exec-jewel-lbl" id="strip-exp-lbl"><span>📉 المصروفات</span><small style="font-size:10px;color:#fda4af;">تشغيل</small></span>
                        <strong class="exec-jewel-val text-rose-300" id="strip-exp-amt" data-sdg="42000">42,000 <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                    <button type="button" onclick="switchSalesProfitCardView('warehouses')" title="القيمة السوقية الإجمالية = الكاش + البنوك + الشيكات + قيمة البضاعة بسعر السوق اليوم" class="inner-box exec-jewel-btn text-right cursor-pointer" style="--jewel-accent: #1d4ed8; --jewel-glow: rgba(14,165,233,0.8); --jewel-bg: linear-gradient(165deg, rgba(14,165,233,0.16), rgba(15,23,42,0.96));">
                        <span class="exec-jewel-lbl"><span>🏛️ القيمة السوقية</span><small style="font-size:10px;color:#7dd3fc;">إجمالي</small></span>
                        <strong class="exec-jewel-val text-sky-300" id="stock-mkt-val" data-sdg="184300000">184.3M <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                    <button type="button" onclick="switchLiveRadarFeed('vouchers')" class="inner-box exec-jewel-btn text-right cursor-pointer" style="--jewel-accent: #0f766e; --jewel-glow: rgba(20,184,166,0.75); --jewel-bg: linear-gradient(165deg, rgba(20,184,166,0.16), rgba(15,23,42,0.96));">
                        <span class="exec-jewel-lbl"><span>📥 المقبوضات</span><small style="font-size:10px;color:#5eead4;">تحصيل</small></span>
                        <strong class="exec-jewel-val text-emerald-300" id="strip-in-amt" data-sdg="1320000">+1,320,000 <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                    <button type="button" onclick="switchLiveRadarFeed('vouchers')" class="inner-box exec-jewel-btn text-right cursor-pointer" style="--jewel-accent: #c2410c; --jewel-glow: rgba(249,115,22,0.75); --jewel-bg: linear-gradient(165deg, rgba(249,115,22,0.16), rgba(15,23,42,0.96));">
                        <span class="exec-jewel-lbl"><span>📤 المدفوعات</span><small style="font-size:10px;color:#fb7185;">منصرف</small></span>
                        <strong class="exec-jewel-val text-rose-400" id="strip-out-amt" data-sdg="368000">-368,000 <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                    <button type="button" onclick="openFullCardModal('debt_aging')" title="مجموع المبالغ التي يريدها منا الموردون كلهم" class="inner-box exec-jewel-btn text-right cursor-pointer" style="--jewel-accent: #7c3aed; --jewel-glow: rgba(168,85,247,0.75); --jewel-bg: linear-gradient(165deg, rgba(168,85,247,0.16), rgba(15,23,42,0.96));">
                        <span class="exec-jewel-lbl-ap"><span>🚚 مستحقات الموردين</span><small style="font-size:10px;color:#d8b4fe;">التزامات</small></span>
                        <strong class="exec-jewel-val text-amber-300" id="strip-ap-amt" data-sdg="290000">290,000 <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                    <button type="button" onclick="openFullCardModal('debt_aging')" title="مجموع المبالغ التي نريدها من العملاء كلهم" class="inner-box exec-jewel-btn text-right cursor-pointer" style="--jewel-accent: #047857; --jewel-glow: rgba(52,211,153,0.75); --jewel-bg: linear-gradient(165deg, rgba(52,211,153,0.16), rgba(15,23,42,0.96));">
                        <span class="exec-jewel-lbl"><span>👥 ديون العملاء</span><small style="font-size:10px;color:#4ade80;">لنا بالسوق</small></span>
                        <strong class="exec-jewel-val text-emerald-300" id="strip-ar-amt" data-sdg="820,000">820,000 <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                </div>
            </div>

            <!-- 3. الصف الأول (توزيع بانورامي مريح مقسوم على بطاقتين عريضتين 6 + 6 لكسر التماثل الممل) -->
            <section id="sec-calm" class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                <!-- البطاقة اليمنى العريضة (6 أعمدة): صافي السيولة النقدية والبنكية المتاحة الآن -->
                <div class="lg:col-span-6 bento-card p-5 flex flex-col justify-between">
                    <div>
                        <div class="dash-card-hdr-row flex items-center justify-between gap-1.5 mb-2">
                            <div class="dash-card-hdr-lead flex items-center gap-1.5 min-w-0 flex-1">
                                <button type="button" onclick="openFullCardModal('vaults')" title="عرض كشف جميع الخزن والحسابات البنكية بالكامل" class="dash-hdr-mini-btn rounded-lg bg-emerald-500/25 hover:bg-emerald-500/45 border-2 border-emerald-400 text-white font-black flex items-center justify-center cursor-pointer shrink-0 shadow">+</button>
                                <button type="button" onclick="openChartCardModal('vaults')" title="عرض الرسوم البيانية الشاملة لكل الخزن والبنوك والسيولة" class="dash-hdr-mini-btn rounded-lg bg-amber-500/25 hover:bg-amber-500/45 border-2 border-amber-400 text-amber-300 font-black flex items-center justify-center cursor-pointer shrink-0 shadow">📊</button>
                                <span class="dash-card-hdr-title font-black text-emerald-300 flex-1" id="dash-liq-card-title">صافي السيولة النقدية والبنكية المتاحة</span>
                            </div>
                            <div class="dash-card-hdr-trail flex items-center gap-1 shrink-0">
                                <button type="button" id="dash-liq-period-btn" onclick="toggleLiquidityPeriod()" class="px-1.5 py-0.5 rounded-lg bg-slate-900 border border-emerald-400/50 text-[10px] font-black cursor-pointer whitespace-nowrap" style="color:#fde68a!important;-webkit-text-fill-color:#fde68a!important;">📅 اليوم ⇄</button>
                                <span title="نسبة نمو السيولة النقدية والبنكية مقارنة بالفترة السابقة" class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-300 shrink-0 whitespace-nowrap" id="dash-liq-card-badge">نمو +12.4% ▲</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-4 gap-1.5 mb-2" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:5px;">
                            <button type="button" onclick="switchLiquidityCardView('overview', this)" class="radar-glow-btn active-radar-tab py-1 px-1.5 rounded-lg bg-emerald-600 text-white text-[11px] font-black text-center whitespace-nowrap">💵 السيولة المتاحة</button>
                            <button type="button" onclick="switchLiquidityCardView('banks', this)" class="radar-glow-btn py-1 px-1.5 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">🏦 تفصيل البنوك</button>
                            <button type="button" onclick="switchLiquidityCardView('coverage', this)" class="radar-glow-btn py-1 px-1.5 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">🧾 تغطية الشيكات</button>
                            <button type="button" onclick="switchLiquidityCardView('ai_cash', this)" class="radar-glow-btn py-1 px-1.5 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">🤖 التنبؤ النقدي</button>
                        </div>
                    </div>
                    <div class="my-2 num-font flex items-center justify-between">
                        <div>
                            <div class="text-4xl font-black text-white flex items-baseline flex-wrap gap-2" id="main-liquidity">3,450,000 <span class="text-base font-bold text-emerald-400">ج.س</span> <span style="font-size:16px;font-weight:900;color:#fde68a;">(≈ $5,750 دولار)</span></div>
                            <div class="text-[11px] font-bold text-emerald-400 mt-1" id="main-liquidity-sub">▲ تدفق إيجابي متصاعد (+910,000 صافي اليوم • يغطي الالتزامات 11.8 مرة)</div>
                        </div>
                        <svg width="56" height="56" viewBox="0 0 36 36" class="-rotate-90 shrink-0"><circle cx="18" cy="18" r="15.5" fill="none" stroke="rgba(16,185,129,0.18)" stroke-width="3.6"/><circle cx="18" cy="18" r="15.5" fill="none" stroke="#10b981" stroke-width="3.6" stroke-dasharray="86, 100" stroke-linecap="round"/></svg>
                    </div>
                    <div id="dash-liq-bottom-boxes" class="grid grid-cols-3 gap-2.5 pt-3 border-t border-white/10 text-xs num-font">
                        <div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">💵 الصندوق:</span><strong class="text-emerald-300 text-sm font-extrabold" id="sub-cash">1,450,000</strong></div>
                        <div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">💳 البنوك:</span><strong class="text-sky-300 text-sm font-extrabold" id="sub-bank">2,000,000</strong></div>
                        <div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">🧾 شيكات بالتحصيل:</span><strong class="text-amber-300 text-sm font-extrabold" id="sub-checks-in">4,250,000</strong></div>
                    </div>
                </div>

                <!-- البطاقة اليسرى العريضة (6 أعمدة): إجمالي المبيعات والأرباح + رصيد المخازن والأوزان والتنبيهات -->
                <div class="lg:col-span-6 bento-card p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-1.5 mb-2">
                            <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                <button type="button" onclick="openFullCardModal('sales_stock')" title="عرض تفاصيل المبيعات والمشتريات والأرباح والأوزان بالكامل" class="w-7 h-7 rounded-lg bg-sky-500/25 hover:bg-sky-500/45 border-2 border-sky-400 text-white font-black text-lg flex items-center justify-center cursor-pointer shrink-0 shadow">+</button>
                                <button type="button" onclick="openChartCardModal('sales_stock')" title="عرض الرسم البياني للمبيعات والمشتريات والأرباح والمخازن" class="w-7 h-7 rounded-lg bg-amber-500/25 hover:bg-amber-500/45 border-2 border-amber-400 text-amber-300 font-black text-sm flex items-center justify-center cursor-pointer shrink-0 shadow">📊</button>
                                <span class="font-black text-sky-300 whitespace-nowrap flex-1" style="font-size:19.5px!important;line-height:1.2;">المبيعات والمشتريات والأرباح</span>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <span onclick="switchSalesProfitCardView('strategy', document.getElementById('btn-card2-strategy'))" title="اضغط لعرض حماية هامش الربح ومعدل دوران المخزون وأثر العملة" class="px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-300 cursor-pointer hover:bg-emerald-500/35 whitespace-nowrap">🛡️ ربح آمن • دوران 2.4x</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-7 gap-1 mb-2" style="display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:3px;">
                            <button type="button" onclick="switchSalesProfitCardView('hour', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-slate-800 text-slate-200 text-[10.5px] font-bold text-center whitespace-nowrap">⏱️ الساعة</button>
                            <button type="button" onclick="switchSalesProfitCardView('day', this)" class="radar-glow-btn active-radar-tab py-1 px-1 rounded-lg bg-sky-600 text-white text-[10.5px] font-black text-center whitespace-nowrap">📅 اليوم</button>
                            <button type="button" onclick="switchSalesProfitCardView('month', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-slate-800 text-slate-200 text-[10.5px] font-bold text-center whitespace-nowrap">📆 الشهر</button>
                            <button type="button" onclick="switchSalesProfitCardView('year', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-slate-800 text-slate-200 text-[10.5px] font-bold text-center whitespace-nowrap">🏆 السنة</button>
                            <button type="button" id="btn-card2-purchases" onclick="switchSalesProfitCardView('purchases', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-emerald-600 text-white text-[10.5px] font-black text-center whitespace-nowrap">🛒 المشتريات</button>
                            <button type="button" onclick="switchSalesProfitCardView('warehouses', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-slate-800 text-slate-200 text-[10.5px] font-bold text-center whitespace-nowrap">⚖️ المخازن</button>
                            <button type="button" id="btn-card2-strategy" onclick="switchSalesProfitCardView('strategy', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-amber-600 text-white text-[10.5px] font-black text-center whitespace-nowrap">🧠 سلوكيات</button>
                        </div>
                    </div>
                    <div id="dash-sales-profit-mid" class="grid grid-cols-3 gap-3 my-2 num-font">
                        <div class="inner-box p-3">
                            <span class="text-xs text-slate-300 block mb-1" id="lbl-card2-box1">إجمالي مبيعات اليوم <b class="text-emerald-400">▲ 16%</b></span>
                            <div class="text-xl font-black text-white" id="main-sales">1,890,000 <span class="text-xs text-sky-400">ج.س</span></div>
                        </div>
                        <div class="inner-box p-3">
                            <span class="text-xs text-slate-300 block mb-1" id="lbl-card2-box2">صافي أرباح اليوم <b class="text-emerald-400">▲ 18.4%</b></span>
                            <div class="text-xl font-black text-emerald-400" id="main-profit">+348,000 <span class="text-xs text-emerald-300">ج.س</span></div>
                        </div>
                        <div class="inner-box p-3">
                            <span class="text-xs text-slate-300 block mb-1" id="lbl-card2-box3">النقد المحصل اليوم <b class="text-amber-400">▲ 70%</b></span>
                            <div class="text-xl font-black text-amber-400" id="main-tons-box">1,320,000 <span class="text-xs font-bold text-amber-200">ج.س</span> <span id="main-tons" style="display:none">420.5</span></div>
                        </div>
                    </div>
                    <div id="dash-sales-profit-bot" class="grid grid-cols-3 gap-3 pt-2.5 border-t border-white/10 text-xs num-font">
                        <div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">مبيعات آجلة اليوم:</span><strong class="text-rose-400 font-bold" id="sub-sales-cash">570,000 ج.س</strong></div>
                        <div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">الفواتير والأصناف اليوم:</span><strong class="text-amber-300 font-bold" id="main-bars-bundles">38 فاتورة (20 صنفاً)</strong></div>
                        <div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">الوحدات المباعة اليوم:</span><strong class="text-sky-300 font-bold" id="sub-hourly-profit">32.5 طن • 410 كرتونة • 180 لوح</strong></div>
                    </div>
                </div>
            </section>

            <!-- 3B. الصف الثاني (توزيع متوازن يضع بطاقة الكعكات المفرغة في منتصف الشاشة تماماً: 4.5 يمين + 3 وسط + 4.5 يسار) -->
            <section id="sec-global-ai-innovations" class="grid grid-cols-1 gap-5">
                <!-- 1. يمين (4.5 وحدة): تحليل الأصناف (الأكثر مبيعاً | الأقل مبيعاً | الأعلى ربحاً | الأقل ربحاً) في سطر واحد و4 أصناف كبيرة -->
                <div class="bento-card p-4 flex flex-col justify-between">
                    <div class="mb-2">
                        <div class="flex items-center gap-1.5 mb-2">
                            <button type="button" onclick="openFullCardModal('items')" title="اضغط لعرض قائمة كل الأصناف بالكامل" class="w-7 h-7 rounded-lg bg-emerald-500/25 hover:bg-emerald-500/45 border-2 border-emerald-400 text-white font-black text-lg flex items-center justify-center cursor-pointer shrink-0 shadow">+</button>
                            <button type="button" onclick="openChartCardModal('items')" title="عرض التحليل البياني البصري للأصناف والمخزون (أعمدة ومقارنات)" class="w-7 h-7 rounded-lg bg-amber-500/25 hover:bg-amber-500/45 border-2 border-amber-400 text-amber-300 font-black text-sm flex items-center justify-center cursor-pointer shrink-0 shadow">📊</button>
                            <h2 class="font-black text-white whitespace-nowrap flex-1" id="dash-items-radar-title" style="font-size:18px!important;line-height:1.25;">الأصناف الـ 6 الأكثر مبيعاً وربحاً</h2>
                            <button type="button" id="dash-items-group-toggle" onclick="toggleItemsGroup()" class="radar-glow-btn mr-auto rounded-lg bg-slate-900 hover:bg-slate-800 font-black cursor-pointer shrink-0 shadow" style="color:#fde68a!important;-webkit-text-fill-color:#fde68a!important;font-size:10.5px!important;padding:2px 7px!important;min-height:26px!important;">⇄ المخزن</button>
                        </div>
                        <div id="dash-items-tabs-sales" class="grid grid-cols-4 gap-1.5" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:5px;">
                            <button type="button" onclick="switchItemsRadar('top_sell', this)" class="radar-glow-btn active-radar-tab py-1.5 px-1 rounded-lg bg-emerald-600 text-white text-[11px] font-black text-center whitespace-nowrap">الأكثر مبيعاً</button>
                            <button type="button" onclick="switchItemsRadar('low_sell', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">الأقل مبيعاً</button>
                            <button type="button" onclick="switchItemsRadar('top_profit', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">الأعلى ربحاً</button>
                            <button type="button" onclick="switchItemsRadar('low_profit', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">الأقل ربحاً</button>
                        </div>
                        <div id="dash-items-tabs-stock" class="grid grid-cols-5 gap-1" style="display:none;grid-template-columns:repeat(5,minmax(0,1fr));gap:3px;">
                            <button type="button" onclick="switchItemsRadar('stock_most', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-emerald-600 text-white text-[10.5px] font-black text-center whitespace-nowrap">📦 الأكثر بالمخزن</button>
                            <button type="button" onclick="switchItemsRadar('stock_least', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-slate-800 text-slate-200 text-[10.5px] font-bold text-center whitespace-nowrap">📉 الأقل بالمخزن</button>
                            <button type="button" onclick="switchItemsRadar('stock_alerts', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-rose-600 text-white text-[10.5px] font-black text-center whitespace-nowrap">🚨 حوجة الطلب</button>
                            <button type="button" onclick="switchItemsRadar('stock_days', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-slate-800 text-slate-200 text-[10.5px] font-bold text-center whitespace-nowrap">⏳ أيام الكفاية</button>
                            <button type="button" onclick="switchItemsRadar('stock_transit', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-slate-800 text-slate-200 text-[10.5px] font-bold text-center whitespace-nowrap">🚚 بالطريق</button>
                        </div>
                    </div>
                    <div id="dash-top-items-list" class="gap-1.5 text-sm num-font flex-1 flex flex-col justify-between">
                        <div class="inner-box py-2 px-3 flex items-center justify-between gap-1.5">
                            <span class="font-black text-white truncate" style="font-size:15px!important;">1. سيخ 12مم السجانة <small class="font-extrabold" style="color:#fde68a;font-size:13.5px!important;">(420,000 • 38%)</small></span>
                            <span class="px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 font-black shrink-0" style="width:116px;font-size:13.5px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:flex-start;gap:3px;">🟢 ▲ 185 طن</span>
                        </div>
                        <div class="inner-box py-2 px-3 flex items-center justify-between gap-1.5">
                            <span class="font-black text-white truncate" style="font-size:15px!important;">2. سيخ 16مم تسليح <small class="font-extrabold" style="color:#fde68a;font-size:13.5px!important;">(415,000 • 27%)</small></span>
                            <span class="px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 font-black shrink-0" style="width:116px;font-size:13.5px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:flex-start;gap:3px;">🟢 ▲ 120 طن</span>
                        </div>
                        <div class="inner-box py-2 px-3 flex items-center justify-between gap-1.5">
                            <span class="font-black text-white truncate" style="font-size:15px!important;">3. أسمنت عطبرة <small class="font-extrabold" style="color:#fde68a;font-size:13.5px!important;">(185,000 • 21%)</small></span>
                            <span class="px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-300 font-black shrink-0" style="width:116px;font-size:13.5px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:flex-start;gap:3px;">🟡 ▲ 95 طن</span>
                        </div>
                        <div class="inner-box py-2 px-3 flex items-center justify-between gap-1.5">
                            <span class="font-black text-white truncate" style="font-size:15px!important;">4. سلك رباط مجلفن <small class="font-extrabold" style="color:#fde68a;font-size:13.5px!important;">(38,000 • 14%)</small></span>
                            <span class="px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-300 font-black shrink-0" style="width:116px;font-size:13.5px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:flex-start;gap:3px;">🟡 ▲ 310 لفة</span>
                        </div>
                        <div class="inner-box py-2 px-3 flex items-center justify-between gap-1.5">
                            <span class="font-black text-white truncate" style="font-size:15px!important;">5. قطاعات ومواسير <small class="font-extrabold" style="color:#fde68a;font-size:13.5px!important;">(510,000 • 11%)</small></span>
                            <span class="px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 font-black shrink-0" style="width:116px;font-size:13.5px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:flex-start;gap:3px;">🟢 ▲ 64 طن</span>
                        </div>
                        <div class="inner-box py-2 px-3 flex items-center justify-between gap-1.5">
                            <span class="font-black text-white truncate" style="font-size:15px!important;">6. كمر حديد H-Beam <small class="font-extrabold" style="color:#fde68a;font-size:13.5px!important;">(450,000 • 9%)</small></span>
                            <span class="px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-300 font-black shrink-0" style="width:116px;font-size:13.5px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:flex-start;gap:3px;">🟡 ▲ 42 طن</span>
                        </div>
                    </div>
                </div>

                <!-- 2. وسط الشاشة تماماً (3 وحدات): الكعكات المفرغة والرسوم البيانية للحصص والسيولة بدون أي تكرار -->
                <div id="dash-center-chart-card" class="bento-card p-4 flex flex-col justify-between">
                    <div class="mb-1.5">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <button type="button" onclick="openFullCardModal('donuts')" title="توسيع نافذة الرسوم والنسب" class="w-7 h-7 rounded-lg bg-sky-500/25 hover:bg-sky-500/45 border-2 border-sky-400 text-white font-black text-lg flex items-center justify-center cursor-pointer shrink-0 shadow">+</button>
                                <button type="button" onclick="openChartCardModal('items', 'horizontal', window._centerChartMode || 'sales_share')" title="عرض الرسم البياني التفصيلي للحصص والنسب" class="w-7 h-7 rounded-lg bg-amber-500/25 hover:bg-amber-500/45 border-2 border-amber-400 text-amber-300 font-black text-sm flex items-center justify-center cursor-pointer shrink-0 shadow">📊</button>
                                <h2 class="font-black whitespace-nowrap text-amber-300" id="dash-center-chart-title" style="font-size:16px!important;">رسوم الحصص والسيولة</h2>
                            </div>
                        </div>
                        <div class="grid grid-cols-4 gap-1" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:4px;">
                            <button type="button" onclick="switchCenterChartCard('donuts', this)" class="radar-glow-btn active-radar-tab py-1 px-1 rounded-lg bg-sky-600 text-white text-xs font-black text-center whitespace-nowrap">🍩 النسب</button>
                            <button type="button" onclick="switchCenterChartCard('sales_share', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-slate-800 text-slate-200 text-xs font-bold text-center whitespace-nowrap">📊 المبيعات</button>
                            <button type="button" onclick="switchCenterChartCard('profit_share', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-slate-800 text-slate-200 text-xs font-bold text-center whitespace-nowrap">💎 الأرباح</button>
                            <button type="button" onclick="switchCenterChartCard('wh_share', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-emerald-600 text-white text-xs font-black text-center whitespace-nowrap">🏛️ المخازن</button>
                        </div>
                    </div>
                    <div id="dash-center-chart-body" class="flex-1 flex flex-col justify-between gap-2.5 my-1 num-font">
                        <div class="grid grid-cols-2 gap-2.5 items-center">
                            <div class="inner-box p-2.5 flex flex-col items-center">
                                <div class="relative w-24 h-24 flex items-center justify-center">
                                    <svg class="w-full h-full -rotate-90" viewBox="0 0 42 42">
                                        <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="rgba(148,163,184,0.15)" stroke-width="5.2"/>
                                        <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#10b981" stroke-width="5.2" stroke-dasharray="58 42" stroke-dashoffset="0"/>
                                        <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#38bdf8" stroke-width="5.2" stroke-dasharray="28 72" stroke-dashoffset="-58"/>
                                        <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#f59e0b" stroke-width="5.2" stroke-dasharray="14 86" stroke-dashoffset="-86"/>
                                    </svg>
                                    <div class="absolute text-center"><span class="text-sm font-black text-white block">100%</span><span class="text-xs font-bold text-emerald-300">السيولة</span></div>
                                </div>
                                <div class="flex flex-wrap justify-center gap-1.5 mt-1.5 text-xs font-black"><span class="text-emerald-400">●بنك 58%</span><span class="text-sky-400">●خزنة 28%</span></div>
                            </div>
                            <div class="inner-box p-2.5 flex flex-col items-center">
                                <div class="relative w-24 h-24 flex items-center justify-center">
                                    <svg class="w-full h-full -rotate-90" viewBox="0 0 42 42">
                                        <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="rgba(148,163,184,0.15)" stroke-width="5.2"/>
                                        <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#38bdf8" stroke-width="5.2" stroke-dasharray="70 30" stroke-dashoffset="0"/>
                                        <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#a855f7" stroke-width="5.2" stroke-dasharray="20 80" stroke-dashoffset="-70"/>
                                        <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#f43f5e" stroke-width="5.2" stroke-dasharray="10 90" stroke-dashoffset="-90"/>
                                    </svg>
                                    <div class="absolute text-center"><span class="text-sm font-black text-white block">70%</span><span class="text-xs font-bold text-sky-300">نقدي</span></div>
                                </div>
                                <div class="flex flex-wrap justify-center gap-1.5 mt-1.5 text-xs font-black"><span class="text-sky-400">●نقد 70%</span><span class="text-purple-400">●آجل 30%</span></div>
                            </div>
                        </div>
                        <div class="inner-box p-3 flex flex-col justify-between gap-2 flex-1">
                            <div class="flex items-center justify-between text-xs font-black"><span class="text-slate-200" id="center-chart-period-lbl">📊 أعمدة وخطوط التدفق والربحية (اليوم)</span><span class="text-emerald-400" id="center-chart-growth-lbl">▲ +12.4% نمو</span></div>
                            <div class="grid grid-cols-7 gap-1.5 items-end h-14 pt-2 px-1 border-b border-white/10" id="center-chart-cols-grid">
                                <div class="flex flex-col items-center gap-1"><div class="w-full rounded-t bg-emerald-500/75" style="height:24px"></div><span class="text-[10px] text-slate-300 font-bold">8ص</span></div>
                                <div class="flex flex-col items-center gap-1"><div class="w-full rounded-t bg-sky-500/75" style="height:32px"></div><span class="text-[10px] text-slate-300 font-bold">10ص</span></div>
                                <div class="flex flex-col items-center gap-1"><div class="w-full rounded-t bg-emerald-500/80" style="height:38px"></div><span class="text-[10px] text-slate-300 font-bold">12ظ</span></div>
                                <div class="flex flex-col items-center gap-1"><div class="w-full rounded-t bg-amber-500/85" style="height:42px"></div><span class="text-[10px] text-slate-300 font-bold">2م</span></div>
                                <div class="flex flex-col items-center gap-1"><div class="w-full rounded-t bg-sky-500/85" style="height:36px"></div><span class="text-[10px] text-slate-300 font-bold">4م</span></div>
                                <div class="flex flex-col items-center gap-1"><div class="w-full rounded-t bg-emerald-400" style="height:46px"></div><span class="text-[10px] text-slate-300 font-bold">6م</span></div>
                                <div class="flex flex-col items-center gap-1"><div class="w-full rounded-t bg-emerald-400 shadow-lg" style="height:52px"></div><span class="text-[10px] text-emerald-300 font-black">الآن</span></div>
                            </div>
                            <div class="space-y-1.5 pt-0.5 text-xs font-black">
                                <div><div class="flex justify-between mb-0.5"><span class="text-slate-300">🏦 سيولة البنوك والخزائن الجاهزة</span><span class="text-emerald-300">86%</span></div><div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden"><div class="h-full bg-emerald-500 rounded-full" style="width:86%"></div></div></div>
                                <div><div class="flex justify-between mb-0.5"><span class="text-slate-300">⚡ كفاءة التحصيل النقدي الفوري</span><span class="text-sky-300">78%</span></div><div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden"><div class="h-full bg-sky-500 rounded-full" style="width:78%"></div></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. يسار (4.5 وحدة مساوية لليمين): رادار التنبؤ الذكي الثلاثي (السيولة | الأصناف | الموردين) -->
                <div class="bento-card p-5 flex flex-col justify-between">
                    <div class="mb-2.5">
                        <div class="flex items-center justify-between gap-1.5 mb-2">
                            <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                <button type="button" onclick="openFullCardModal('forecast')" title="توسيع نافذة التنبؤ الذكي بالكامل" class="w-7 h-7 rounded-lg bg-emerald-500/25 hover:bg-emerald-500/45 border-2 border-emerald-400 text-white font-black text-lg flex items-center justify-center cursor-pointer shrink-0 shadow">+</button>
                                <button type="button" onclick="openChartCardModal('forecast')" title="عرض الرسوم البيانية للتنبؤ الذكي" class="w-7 h-7 rounded-lg bg-amber-500/25 hover:bg-amber-500/45 border-2 border-amber-400 text-amber-300 font-black text-sm flex items-center justify-center cursor-pointer shrink-0 shadow">📊</button>
                                <h2 class="font-black text-white whitespace-nowrap flex-1" id="dash-forecast-radar-title" style="font-size:18px!important;line-height:1.25;">التنبؤ الذكي والتنبيهات الحيوية</h2>
                            </div>
                            <button onclick="speakExecutiveDashboardBriefing()" class="radar-glow-btn rounded-lg bg-emerald-500/20 border border-emerald-400/50 text-emerald-300 font-black cursor-pointer shrink-0" style="font-size:11px!important;padding:2px 8px!important;min-height:28px!important;">🔊 إيجاز صوتي</button>
                        </div>
                        <div class="grid grid-cols-5 gap-1" style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:4px;">
                            <button type="button" onclick="switchForecastRadar('liquidity', this)" class="radar-glow-btn active-radar-tab py-1 px-1 rounded-lg bg-emerald-600 text-white text-[11px] font-black text-center whitespace-nowrap">💵 السيولة</button>
                            <button type="button" onclick="switchForecastRadar('items', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">🏗️ الأصناف</button>
                            <button type="button" onclick="switchForecastRadar('suppliers', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">🚚 الموردين</button>
                            <button type="button" onclick="switchForecastRadar('customers', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">👥 العملاء</button>
                            <button type="button" onclick="switchForecastRadar('vital_alerts', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-rose-600 text-white text-[11px] font-black text-center whitespace-nowrap">🚨 منبهات</button>
                        </div>
                    </div>
                    <div id="dash-forecast-list" class="gap-1.5 text-sm num-font flex-1 flex flex-col justify-between">
                        <div class="inner-box py-2 px-3 flex items-center justify-between gap-2">
                            <span class="text-slate-200 font-black truncate" style="font-size:15px!important;">📈 سيولة متوقعة نهاية الشهر:</span>
                            <strong class="text-emerald-400 font-black shrink-0" style="font-size:15px!important;" id="ai-forecast-liq">▲ 4,820,000 ج.س</strong>
                        </div>
                        <div class="inner-box py-2 px-3 flex items-center justify-between gap-2">
                            <span class="text-slate-200 font-black truncate" style="font-size:15px!important;">🛡️ فحص الانحرافات السعرية:</span>
                            <strong class="text-sky-300 font-black shrink-0" style="font-size:15px!important;">0 انحراف (آمن 100% ✓)</strong>
                        </div>
                        <div class="inner-box py-2 px-3 flex items-center justify-between gap-2">
                            <span class="text-slate-200 font-black truncate" style="font-size:15px!important;">⚡ تغطية الالتزامات والتعادل:</span>
                            <strong class="text-amber-300 font-black shrink-0" style="font-size:15px!important;">مغطى بـ 11.8 ضعفاً ▲</strong>
                        </div>
                        <div class="inner-box py-2 px-3 flex items-center justify-between gap-2">
                            <span class="text-slate-200 font-black truncate" style="font-size:15px!important;">📦 كفاية المخزون الاستراتيجي:</span>
                            <strong class="text-emerald-300 font-black shrink-0" style="font-size:15px!important;">يكفي 45 يوماً (آمن ✓)</strong>
                        </div>
                        <div class="inner-box py-2 px-3 flex items-center justify-between gap-2">
                            <span class="text-slate-200 font-black truncate" style="font-size:15px!important;">🎯 تحصيل الديون المتوقع (7 أيام):</span>
                            <strong class="text-sky-300 font-black shrink-0" style="font-size:15px!important;">+640,000 ج.س (78% ▲)</strong>
                        </div>
                    </div>
                </div>
            </section>
            <!-- 4. الطبقة الثانية: بطاقات العملاء والموردين متجاورة جنباً إلى جنب (رادار دوريات الشراء + أعمار الديون والسقوفات) -->
            <section id="sec-flow" class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                <div class="lg:col-span-6 bento-card p-5 flex flex-col justify-between">
                    <div class="mb-2">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                <button type="button" onclick="openFullCardModal('partners')" title="اضغط لعرض قائمة كل الشركات والعملاء بالكامل" class="w-7 h-7 rounded-lg bg-sky-500/25 hover:bg-sky-500/45 border-2 border-sky-400 text-white font-black text-lg flex items-center justify-center cursor-pointer shrink-0 shadow">+</button>
                                <button type="button" onclick="openChartCardModal('partners')" title="عرض التحليل البياني البصري للعملاء والموردين والدوريات" class="w-7 h-7 rounded-lg bg-amber-500/25 hover:bg-amber-500/45 border-2 border-amber-400 text-amber-300 font-black text-sm flex items-center justify-center cursor-pointer shrink-0 shadow">📊</button>
                                <h2 class="font-black text-white whitespace-nowrap flex-1" id="dash-partners-radar-title" style="font-size:19px!important;line-height:1.2;">رادار دوريات شراء العملاء</h2>
                            </div>
                            <button type="button" id="dash-partners-group-toggle" onclick="togglePartnersGroup()" class="px-3 py-1 rounded-lg bg-amber-600 text-white text-xs font-black cursor-pointer shrink-0" style="background:#d97706!important;color:#fff!important;-webkit-text-fill-color:#fff!important;">⇄ الموردين</button>
                        </div>
                        <div id="dash-partners-tabs-cust" class="grid grid-cols-5 gap-1" style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:4px;margin-top:8px;">
                            <button type="button" onclick="switchPartnersRadar('freq_alert', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-rose-600 text-white text-[11px] font-black text-center whitespace-nowrap">🔔 دوريات الشراء</button>
                            <button type="button" onclick="switchPartnersRadar('top_cust', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">🏆 الأكثر شراءً</button>
                            <button type="button" onclick="switchPartnersRadar('low_cust', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">📉 الأقل شراءً</button>
                            <button type="button" onclick="switchPartnersRadar('profit_cust', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">⚠️ المعرض للتوقف</button>
                            <button type="button" onclick="switchPartnersRadar('behavior_cust', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-amber-600 text-white text-[11px] font-black text-center whitespace-nowrap">🧠 سلوك وتوقعات</button>
                        </div>
                        <div id="dash-partners-tabs-supp" class="grid grid-cols-5 gap-1" style="display:none;grid-template-columns:repeat(5,minmax(0,1fr));gap:4px;margin-top:8px;">
                            <button type="button" onclick="switchPartnersRadar('supp_cycle', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-emerald-600 text-white text-[11px] font-black text-center whitespace-nowrap">🔔 دوريات التوريد</button>
                            <button type="button" onclick="switchPartnersRadar('top_supp', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">🏆 الأكثر توريداً</button>
                            <button type="button" onclick="switchPartnersRadar('low_supp', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">📉 الأقل توريداً</button>
                            <button type="button" onclick="switchPartnersRadar('best_supp', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">⚠️ المعرض للانقطاع</button>
                            <button type="button" onclick="switchPartnersRadar('behavior_supp', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-amber-600 text-white text-[11px] font-black text-center whitespace-nowrap">🧠 التزام الموردين</button>
                        </div>
                    </div>
                    <div id="dash-partners-radar-list" class="flex flex-col gap-2 justify-between text-sm num-font flex-1">
                        <div class="flex items-center justify-between py-2.5 px-3.5 rounded-xl bg-emerald-500/15 border border-emerald-500/40 gap-2"><div class="min-w-0 flex-1"><span class="font-black text-white text-sm block truncate">1. مؤسسة البنيان الحديثة</span><span class="text-xs font-bold block truncate" style="color:#fde68a;">كل 5 أيام ⬅️ موعده اليوم (18,400,000 ج.س)</span></div><div class="flex items-center gap-1.5 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('مؤسسة البنيان الحديثة', 'موعد الشراء الدوري اليوم (18,400,000 ج.س)')" title="مراسلة وتنبيه ذكي عبر واتساب" class="wa-svg-btn"><svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg></button><button type="button" onclick="showPartnerItemsDetail('مؤسسة البنيان الحديثة')" class="py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-sky-300 border border-sky-500/30 font-black cursor-pointer" style="width:74px;font-size:13px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:center;">📦 الأصناف</button><span class="px-2 py-1 rounded-lg bg-emerald-700 text-white font-black" style="width:62px;font-size:14px!important;white-space:nowrap;text-align:center;display:inline-flex;align-items:center;justify-content:center;">اليوم</span><span title="منتظم في موعد الشراء المعتاد (اليوم)" class="py-1 rounded-lg bg-emerald-600 text-white font-black" style="width:98px;font-size:14px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:flex-start;padding:4px 8px;gap:5px;">🟢 منتظم</span></div></div>
                        <div class="flex items-center justify-between py-2.5 px-3.5 rounded-xl bg-emerald-500/15 border border-emerald-500/40 gap-2"><div class="min-w-0 flex-1"><span class="font-black text-white text-sm block truncate">2. شركة النيل للمقاولات</span><span class="text-xs font-bold block truncate" style="color:#fde68a;">كل 7 أيام ⬅️ تأخر يوم واحد (12,600,000 ج.س)</span></div><div class="flex items-center gap-1.5 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('شركة النيل للمقاولات', 'تأخر يوم واحد عن موعد الشراء (12,600,000 ج.س)')" title="مراسلة وتنبيه ذكي عبر واتساب" class="wa-svg-btn"><svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg></button><button type="button" onclick="showPartnerItemsDetail('شركة النيل للمقاولات')" class="py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-sky-300 border border-sky-500/30 font-black cursor-pointer" style="width:74px;font-size:13px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:center;">📦 الأصناف</button><span class="px-2 py-1 rounded-lg bg-emerald-700 text-white font-black" style="width:62px;font-size:14px!important;white-space:nowrap;text-align:center;display:inline-flex;align-items:center;justify-content:center;">1 يوم</span><span title="منتظم (تأخر يوم واحد فقط ضمن النطاق الأخضر)" class="py-1 rounded-lg bg-emerald-600 text-white font-black" style="width:98px;font-size:14px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:flex-start;padding:4px 8px;gap:5px;">🟢 منتظم</span></div></div>
                        <div class="flex items-center justify-between py-2.5 px-3.5 rounded-xl bg-amber-500/15 border border-amber-500/40 gap-2"><div class="min-w-0 flex-1"><span class="font-black text-white text-sm block truncate">3. مغلق الوادي للحديد</span><span class="text-xs font-bold block truncate" style="color:#fde68a;">كل 5 أيام ⬅️ تأخر يومين (9,200,000 ج.س)</span></div><div class="flex items-center gap-1.5 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('مغلق الوادي للحديد', 'تأخر يومين عن دورية الشراء (9,200,000 ج.س)')" title="مراسلة وتنبيه ذكي عبر واتساب" class="wa-svg-btn"><svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg></button><button type="button" onclick="showPartnerItemsDetail('مغلق الوادي للحديد')" class="py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-sky-300 border border-sky-500/30 font-black cursor-pointer" style="width:74px;font-size:13px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:center;">📦 الأصناف</button><span class="px-2 py-1 rounded-lg bg-amber-500 font-black" style="width:62px;font-size:14px!important;white-space:nowrap;text-align:center;display:inline-flex;align-items:center;justify-content:center;color:#000000!important;-webkit-text-fill-color:#000000!important;">يومان</span><span title="استعداد ومتابعة: تأخر يومين عن موعد الشراء" class="py-1 rounded-lg bg-amber-500 font-black" style="width:98px;font-size:14px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:flex-start;padding:4px 8px;gap:5px;color:#000000!important;-webkit-text-fill-color:#000000!important;">🟡 استعداد</span></div></div>
                        <div class="flex items-center justify-between py-2.5 px-3.5 rounded-xl bg-amber-500/15 border border-amber-500/40 gap-2"><div class="min-w-0 flex-1"><span class="font-black text-white text-sm block truncate">4. مقاولات السلام السكنية</span><span class="text-xs font-bold block truncate" style="color:#fde68a;">كل 6 أيام ⬅️ تأخر 3 أيام (6,400,000 ج.س)</span></div><div class="flex items-center gap-1.5 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('مقاولات السلام السكنية', 'تأخر 3 أيام عن دورية الشراء (6,400,000 ج.س)')" title="مراسلة وتنبيه ذكي عبر واتساب" class="wa-svg-btn"><svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg></button><button type="button" onclick="showPartnerItemsDetail('مقاولات السلام السكنية')" class="py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-sky-300 border border-sky-500/30 font-black cursor-pointer" style="width:74px;font-size:13px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:center;">📦 الأصناف</button><span class="px-2 py-1 rounded-lg bg-amber-500 font-black" style="width:62px;font-size:14px!important;white-space:nowrap;text-align:center;display:inline-flex;align-items:center;justify-content:center;color:#000000!important;-webkit-text-fill-color:#000000!important;">3 أيام</span><span title="استعداد ومتابعة: تأخر 3 أيام عن موعد الشراء" class="py-1 rounded-lg bg-amber-500 font-black" style="width:98px;font-size:14px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:flex-start;padding:4px 8px;gap:5px;color:#000000!important;-webkit-text-fill-color:#000000!important;">🟡 استعداد</span></div></div>
                        <div class="flex items-center justify-between py-2.5 px-3.5 rounded-xl bg-rose-500/15 border border-rose-500/40 gap-2"><div class="min-w-0 flex-1"><span class="font-black text-white text-sm block truncate">5. مقاولات أبراج الخرطوم</span><span class="text-xs font-bold block truncate" style="color:#fde68a;">كل 5 أيام ⬅️ تأخر 4 أيام (8,900,000 ج.س)</span></div><div class="flex items-center gap-1.5 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('مقاولات أبراج الخرطوم', 'خطر تأخر 4 أيام عن الشراء (8,900,000 ج.س)')" title="مراسلة وتنبيه ذكي عبر واتساب" class="wa-svg-btn"><svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg></button><button type="button" onclick="showPartnerItemsDetail('مقاولات أبراج الخرطوم')" class="py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-sky-300 border border-sky-500/30 font-black cursor-pointer" style="width:74px;font-size:13px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:center;">📦 الأصناف</button><span class="px-2 py-1 rounded-lg bg-rose-600 text-white font-black" style="width:62px;font-size:14px!important;white-space:nowrap;text-align:center;display:inline-flex;align-items:center;justify-content:center;">4 أيام</span><span title="خطر: تأخر 4 أيام عن موعد الشراء المعتاد" class="py-1 rounded-lg bg-rose-600 text-white font-black" style="width:98px;font-size:14px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:flex-start;padding:4px 8px;gap:5px;">🔴 خطر</span></div></div>
                        <div class="flex items-center justify-between py-2.5 px-3.5 rounded-xl bg-rose-500/15 border border-rose-500/40 gap-2"><div class="min-w-0 flex-1"><span class="font-black text-white text-sm block truncate">6. شركة إعمار الهندسية</span><span class="text-xs font-bold block truncate" style="color:#fde68a;">كل 10 أيام ⬅️ تأخر 5 أيام (5,100,000 ج.س)</span></div><div class="flex items-center gap-1.5 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('شركة إعمار الهندسية', 'خطر تأخر 5 أيام عن الشراء (5,100,000 ج.س)')" title="مراسلة وتنبيه ذكي عبر واتساب" class="wa-svg-btn"><svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg></button><button type="button" onclick="showPartnerItemsDetail('شركة إعمار الهندسية')" class="py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-sky-300 border border-sky-500/30 font-black cursor-pointer" style="width:74px;font-size:13px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:center;">📦 الأصناف</button><span class="px-2 py-1 rounded-lg bg-rose-600 text-white font-black" style="width:62px;font-size:14px!important;white-space:nowrap;text-align:center;display:inline-flex;align-items:center;justify-content:center;">5 أيام</span><span title="خطر: تأخر 5 أيام عن موعد الشراء المعتاد" class="py-1 rounded-lg bg-rose-600 text-white font-black" style="width:98px;font-size:14px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:flex-start;padding:4px 8px;gap:5px;">🔴 خطر</span></div></div>
                        <div class="flex items-center justify-between py-2.5 px-3.5 rounded-xl border border-white/30 gap-2" style="background:rgba(2,6,23,0.92);"><div class="min-w-0 flex-1"><span class="font-black text-white text-sm block truncate">7. ورشة الأمانة للحدادة</span><span class="text-xs font-bold block truncate" style="color:#fde68a;">كل 5 أيام ⬅️ تأخر 7 أيام (2,400,000 ج.س)</span></div><div class="flex items-center gap-1.5 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('ورشة الأمانة للحدادة', 'انقطاع 7 أيام عن الشراء (2,400,000 ج.س)')" title="مراسلة وتنبيه ذكي عبر واتساب" class="wa-svg-btn"><svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg></button><button type="button" onclick="showPartnerItemsDetail('ورشة الأمانة للحدادة')" class="py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-sky-300 border border-sky-500/30 font-black cursor-pointer" style="width:74px;font-size:13px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:center;">📦 الأصناف</button><span class="px-2 py-1 rounded-lg font-black" style="width:62px;font-size:14px!important;white-space:nowrap;text-align:center;display:inline-flex;align-items:center;justify-content:center;background:#020617;color:#fff;border:1px solid #fff;">7 أيام</span><span title="متوقف: تأخر 7 أيام فأكثر عن موعد الشراء" class="py-1 rounded-lg font-black" style="width:98px;font-size:14px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:flex-start;padding:4px 8px;gap:5px;background:#020617;color:#fff;border:1px solid #fff;">⬛ متوقف</span></div></div>
                    </div>
                </div>
                <div class="lg:col-span-6 bento-card p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                <button type="button" onclick="openFullCardModal('debt')" title="اضغط لعرض قائمة كل أعمار الديون والسقوفات والربحية والشيكات بالكامل" class="w-7 h-7 rounded-lg bg-rose-500/25 hover:bg-rose-500/45 border-2 border-rose-400 text-white font-black text-lg flex items-center justify-center cursor-pointer shrink-0 shadow">+</button>
                                <button type="button" onclick="openChartCardModal('debt')" title="عرض التحليل البياني البصري الشامل لأعمار الديون والربحية والشيكات" class="w-7 h-7 rounded-lg bg-amber-500/25 hover:bg-amber-500/45 border-2 border-amber-400 text-amber-300 font-black text-sm flex items-center justify-center cursor-pointer shrink-0 shadow">📊</button>
                                <h2 class="font-black text-white whitespace-nowrap flex-1" id="dash-debt-aging-title" style="font-size:19px!important;line-height:1.2;">سجل الديون والسقوفات والأرباح</h2>
                            </div>
                            <button type="button" id="dash-debt-group-toggle" onclick="toggleDebtGroup()" class="radar-glow-btn px-3 py-1 rounded-lg bg-amber-600 text-white text-xs font-black cursor-pointer shrink-0" style="background:#d97706!important;color:#fff!important;-webkit-text-fill-color:#fff!important;">⇄ الموردين</button>
                        </div>
                        <div id="dash-debt-tabs-row" class="grid grid-cols-4 gap-1.5 mb-2" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:6px;">
                            <button type="button" onclick="switchDebtView('aging', this)" class="radar-glow-btn active-radar-tab py-1.5 px-2 rounded-lg bg-rose-600 text-white text-xs font-black text-center whitespace-nowrap">⏳ أعمار الديون</button>
                            <button type="button" onclick="switchDebtView('limits', this)" class="radar-glow-btn py-1.5 px-2 rounded-lg bg-slate-800 text-slate-200 text-xs font-bold text-center whitespace-nowrap">🛡️ سقف الائتمان</button>
                            <button type="button" onclick="switchDebtView('profit_checks', this)" class="radar-glow-btn py-1.5 px-2 rounded-lg bg-slate-800 text-slate-200 text-xs font-bold text-center whitespace-nowrap">💎 صافي الربحية</button>
                            <button type="button" onclick="switchDebtView('checks', this)" class="radar-glow-btn py-1.5 px-2 rounded-lg bg-slate-800 text-slate-200 text-xs font-bold text-center whitespace-nowrap">🧾 الشيكات</button>
                        </div>
                        <div id="dash-debt-tabs-supp" class="grid grid-cols-5 gap-1 mb-2" style="display:none;grid-template-columns:repeat(5,minmax(0,1fr));gap:4px;">
                            <button type="button" onclick="switchDebtView('supp_aging', this)" class="radar-glow-btn active-radar-tab py-1.5 px-1 rounded-lg bg-rose-600 text-white text-[11px] font-black text-center whitespace-nowrap" title="مديونية الموردين التي يطالبوننا بسدادها ومواعيدها">⏳ المديونية</button>
                            <button type="button" onclick="switchDebtView('supp_limits', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap" title="سقوفات الائتمان المسموح لنا بالسحب الآجل بها من كل مورد">🛡️ السقوفات</button>
                            <button type="button" onclick="switchDebtView('supp_profit', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap" title="الخصم المكتسب والربح المتحقق لنا من مشتريات كل مورد">💎 الخصم والربح</button>
                            <button type="button" onclick="switchDebtView('supp_checks', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap" title="الشيكات الصادرة منا للموردين">🧾 الشيكات</button>
                            <button type="button" onclick="switchDebtView('supp_prices', this)" class="radar-glow-btn py-1.5 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap" title="مقارنة أسعار الموردين لمعرفة الأرخص">⚖️ مقارنة الأسعار</button>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5 mb-2 num-font">
                            <div class="inner-box py-1.5 px-2.5 border-r-4 border-r-sky-400 flex items-center justify-between"><div><span class="text-[11px] text-slate-300 font-bold block">لنا بالسوق (المدينون - ذمم العملاء)</span><strong class="text-lg font-black text-sky-400" id="card-ar">820,000</strong><span class="text-[10px] text-rose-400 font-bold block">⚠️ عميلان تجاوزا دورية السداد!</span></div><span class="text-xl">👥</span></div>
                            <div class="inner-box py-1.5 px-2.5 border-r-4 border-r-rose-500 flex items-center justify-between"><div><span class="text-[11px] text-slate-300 font-bold block">علينا للموردين (الدائنون - التزامات)</span><strong class="text-lg font-black text-rose-400" id="card-ap">290,000</strong><span class="text-[10px] text-emerald-400 font-bold block">مغطى بالسيولة بالكامل ✓</span></div><span class="text-xl">🏛️</span></div>
                        </div>
                    </div>
                    <div id="dash-debt-aging-box" class="space-y-1.5 inner-box p-2.5 num-font text-sm flex-1 flex flex-col justify-between">
                        <div class="flex items-center justify-between py-2.5 px-3.5 rounded-xl bg-emerald-500/15 border border-emerald-500/40 gap-2">
                            <div class="min-w-0 flex-1"><span class="font-black text-white text-sm block truncate">🟢 1. مقاولات السلام السكنية (منتظم تماماً)</span><span class="text-xs font-bold block truncate" style="color:#fde68a;">المعتاد: يسدد كل <b>10 أيام</b> ⬅️ مرّ <b>3 أيام</b> (آمن 100%)</span></div>
                            <div class="flex items-center gap-1.5 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('مقاولات السلام السكنية', 'رصيد 110,000 ج.س • مر 3 أيام من دورية 10 أيام')" title="مراسلة ذكية عبر واتساب" class="wa-svg-btn"><svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg></button><span class="px-2 py-1 rounded-lg bg-emerald-600 text-white font-black" style="width:68px;font-size:14px!important;text-align:center;display:inline-flex;align-items:center;justify-content:center;">3 أيام</span><strong class="text-emerald-400 font-black text-sm" style="width:118px;text-align:left;display:inline-block;white-space:nowrap;">110,000 ج.س</strong></div>
                        </div>
                        <div class="flex items-center justify-between py-2.5 px-3.5 rounded-xl bg-emerald-500/15 border border-emerald-500/40 gap-2">
                            <div class="min-w-0 flex-1"><span class="font-black text-white text-sm block truncate">🟢 2. شركة النيل للمقاولات (منتظم تماماً)</span><span class="text-xs font-bold block truncate" style="color:#fde68a;">المعتاد: يسدد كل <b>15 يوماً</b> ⬅️ مرّ <b>4 أيام</b> (آمن 100%)</span></div>
                            <div class="flex items-center gap-1.5 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('شركة النيل للمقاولات', 'رصيد 165,000 ج.س • مر 4 أيام من دورية 15 يوماً')" title="مراسلة ذكية عبر واتساب" class="wa-svg-btn"><svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg></button><span class="px-2 py-1 rounded-lg bg-emerald-600 text-white font-black" style="width:68px;font-size:14px!important;text-align:center;display:inline-flex;align-items:center;justify-content:center;">4 أيام</span><strong class="text-emerald-400 font-black text-sm" style="width:118px;text-align:left;display:inline-block;white-space:nowrap;">165,000 ج.س</strong></div>
                        </div>
                        <div class="flex items-center justify-between py-2.5 px-3.5 rounded-xl bg-amber-500/15 border border-amber-500/40 gap-2">
                            <div class="min-w-0 flex-1"><span class="font-black text-white text-sm block truncate">🟡 3. مؤسسة البنيان الحديثة (اقتراب الاستحقاق)</span><span class="text-xs font-bold block truncate" style="color:#fde68a;">المعتاد: يسدد كل <b>7 أيام</b> ⬅️ مرّ <b>8 أيام</b> (تأخير +1 يوم)</span></div>
                            <div class="flex items-center gap-1.5 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('مؤسسة البنيان الحديثة', 'استحقاق 420,000 ج.س • مر 8 أيام (تأخير +1 يوم)')" title="تذكير سداد ذكي عبر واتساب" class="wa-svg-btn"><svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg></button><span class="px-2 py-1 rounded-lg bg-amber-500 font-black" style="width:68px;font-size:14px!important;text-align:center;display:inline-flex;align-items:center;justify-content:center;color:#000000!important;-webkit-text-fill-color:#000000!important;">8 أيام</span><strong class="text-amber-300 font-black text-sm" style="width:118px;text-align:left;display:inline-block;white-space:nowrap;">420,000 ج.س</strong></div>
                        </div>
                        <div class="flex items-center justify-between py-2.5 px-3.5 rounded-xl bg-amber-500/15 border border-amber-500/40 gap-2">
                            <div class="min-w-0 flex-1"><span class="font-black text-white text-sm block truncate">🟡 4. مغلق الوادي للحديد (اقتراب الاستحقاق)</span><span class="text-xs font-bold block truncate" style="color:#fde68a;">المعتاد: يسدد كل <b>7 أيام</b> ⬅️ مرّ <b>9 أيام</b> (تأخير +2 يوم)</span></div>
                            <div class="flex items-center gap-1.5 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('مغلق الوادي للحديد', 'استحقاق 140,000 ج.س • مر 9 أيام (تأخير +2 يوم)')" title="تذكير سداد ذكي عبر واتساب" class="wa-svg-btn"><svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg></button><span class="px-2 py-1 rounded-lg bg-amber-500 font-black" style="width:68px;font-size:14px!important;text-align:center;display:inline-flex;align-items:center;justify-content:center;color:#000000!important;-webkit-text-fill-color:#000000!important;">9 أيام</span><strong class="text-amber-300 font-black text-sm" style="width:118px;text-align:left;display:inline-block;white-space:nowrap;">140,000 ج.س</strong></div>
                        </div>
                        <div class="flex items-center justify-between py-2.5 px-3.5 rounded-xl bg-rose-500/15 border border-rose-500/40 gap-2">
                            <div class="min-w-0 flex-1"><span class="font-black text-white text-sm block truncate">🔴 5. مغلق السجانة الهندسي (تأخر سداد!)</span><span class="text-xs font-bold block truncate" style="color:#fde68a;">المعتاد: يسدد كل <b>5 أيام</b> ⬅️ مرّ <b>10 أيام</b> (تأخير +5 أيام)</span></div>
                            <div class="flex items-center gap-1.5 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('مغلق السجانة الهندسي', 'متأخر 235,000 ج.س • مر 10 أيام (+5 أيام)')" title="مطالبة سداد ذكية عبر واتساب" class="wa-svg-btn"><svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg></button><span class="px-2 py-1 rounded-lg bg-rose-600 text-white font-black" style="width:68px;font-size:14px!important;text-align:center;display:inline-flex;align-items:center;justify-content:center;">10 أيام</span><strong class="text-rose-400 font-black text-sm" style="width:118px;text-align:left;display:inline-block;white-space:nowrap;">235,000 ج.س</strong></div>
                        </div>
                        <div class="flex items-center justify-between py-2.5 px-3.5 rounded-xl border border-white/30 gap-2" style="background:rgba(2,6,23,0.92);">
                            <div class="min-w-0 flex-1"><span class="font-black text-white text-sm block truncate">⬛ 6. ورشة الأمانة للحدادة (تجاوز 10 أيام!)</span><span class="text-xs font-bold block truncate" style="color:#fde68a;">المعتاد: يسدد كل <b>5 أيام</b> ⬅️ مرّ <b>16 يوماً</b> (تأخير +11 يوماً)</span></div>
                            <div class="flex items-center gap-1.5 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('ورشة الأمانة للحدادة', 'متأخر حرج 195,000 ج.س • مر 16 يوماً (+11 يوماً)')" title="إنذار سداد عاجل عبر واتساب" class="wa-svg-btn"><svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg></button><span class="px-2 py-1 rounded-lg font-black" style="width:68px;font-size:14px!important;text-align:center;display:inline-flex;align-items:center;justify-content:center;background:#020617;color:#fff;border:1px solid #fff;">16 يوماً</span><strong class="text-white font-black text-sm" style="width:118px;text-align:left;display:inline-block;white-space:nowrap;">195,000 ج.س</strong></div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- 5. الطبقة الثالثة: غرفة القيادة وميزان التدقيق + الرادار والأذونات + حاسبة أوزان السجانة -->
            <section id="sec-ops-audit" class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                <div class="lg:col-span-4 bento-card p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                <button type="button" onclick="openFullCardModal('audit')" title="عرض غرف القيادة وميزانية المطابقة بالكامل" class="w-7 h-7 rounded-lg bg-emerald-500/25 hover:bg-emerald-500/45 border-2 border-emerald-400 text-white font-black text-lg flex items-center justify-center cursor-pointer shrink-0 shadow">+</button>
                                <button type="button" onclick="openChartCardModal('audit')" title="عرض الرسم البياني للمركز المالي وميزانية المطابقة" class="w-7 h-7 rounded-lg bg-amber-500/25 hover:bg-amber-500/45 border-2 border-amber-400 text-amber-300 font-black text-sm flex items-center justify-center cursor-pointer shrink-0 shadow">📊</button>
                                <h2 class="font-black text-white whitespace-nowrap flex-1" style="font-size:18px!important;">غرف القيادة وميزانية المطابقة</h2>
                            </div>
                            <span class="px-2 py-0.5 rounded-lg bg-emerald-500/20 text-emerald-300 text-[10.5px] font-black shrink-0">متطابق 100% ✓</span>
                        </div>
                        <div class="grid grid-cols-4 gap-1 mb-2.5" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:4px;">
                            <button type="button" onclick="switchAuditCommandView('trial', this)" class="radar-glow-btn active-radar-tab py-1 px-1 rounded-lg bg-emerald-600 text-white text-[11px] font-black text-center whitespace-nowrap">⚖️ المطابقة</button>
                            <button type="button" onclick="switchAuditCommandView('pnl', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">📊 الدخل والربح</button>
                            <button type="button" onclick="switchAuditCommandView('balance', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">🏛️ الميزانية</button>
                            <button type="button" onclick="switchAuditCommandView('ai_audit', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">🤖 تدقيق ذكي</button>
                        </div>
                        <table class="w-full text-right text-sm num-font">
                            <thead>
                                <tr class="text-slate-300 border-b border-white/15"><th class="pb-2.5">البند المحاسبي / المخزني</th><th class="pb-2.5">الرصيد الدفتري</th><th class="pb-2.5">الفعلي / التدقيق</th></tr>
                            </thead>
                            <tbody id="dash-audit-command-tbody" class="divide-y divide-white/10">
                                <tr><td class="py-3 font-bold text-white">صافي السيولة بالخزائن والبنوك</td><td class="py-3 font-extrabold text-emerald-300" id="aud-liq">3,450,000</td><td class="py-3"><span class="px-2 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 font-bold text-xs">متطابق 100% ✓</span></td></tr>
                                <tr><td class="py-3 font-bold text-white">مخزون سيخ التسليح والأسمنت</td><td class="py-3 font-extrabold text-amber-300" id="aud-stock">420.5 طن (56,767 سيخة)</td><td class="py-3"><span class="px-2 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 font-bold text-xs">مضبوط وزنياً وعدداً ✓</span></td></tr>
                                <tr><td class="py-3 font-bold text-white">إجمالي ذمم العملاء (المدينون)</td><td class="py-3 font-extrabold text-sky-300" id="aud-ar">820,000</td><td class="py-3"><span class="px-2 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 font-bold text-xs">مؤكد بالقيود ✓</span></td></tr>
                                <tr><td class="py-3 font-bold text-white">التزامات الموردين (الدائنون)</td><td class="py-3 font-extrabold text-rose-400" id="aud-ap">290,000</td><td class="py-3"><span class="px-2 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 font-bold text-xs">مؤكد بالقيود ✓</span></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2.5 pt-2.5 border-t border-white/15 flex flex-col gap-2 num-font">
                        <div class="inner-box p-2.5 flex flex-col gap-1.5">
                            <div class="flex items-center justify-between text-xs font-black">
                                <span class="text-amber-300">📊 هيكل الملاءة وتغطية الأصول للالتزامات</span>
                                <span class="text-emerald-400">نسبة الأمان 99.8% ▲</span>
                            </div>
                            <div class="w-full h-3 rounded-full overflow-hidden flex border border-white/20">
                                <div style="width:74%;background:linear-gradient(90deg,#059669,#10b981);" title="المخزون السلعي 74%"></div>
                                <div style="width:18%;background:linear-gradient(90deg,#0284c7,#38bdf8);" title="السيولة النقدية والبنكية 18%"></div>
                                <div style="width:6%;background:#f59e0b;" title="ذمم العملاء 6%"></div>
                                <div style="width:2%;background:#f43f5e;" title="التزامات الموردين 2%"></div>
                            </div>
                            <div class="flex justify-between text-[11px] font-bold text-slate-300">
                                <span>🟢 مخزون 74%</span><span>🔵 سيولة 18%</span><span>🟡 عملاء 6%</span><span>🔴 التزامات 2%</span>
                            </div>
                        </div>
                        <div class="inner-box p-2 border-r-4 border-r-emerald-400 text-xs font-bold text-slate-200 leading-relaxed">
                            💡 <strong class="text-emerald-300">خلاصة المطابقة الذكية:</strong> الأصول السائلة والمخزنية تغطي التزامات الموردين بأكثر من <strong class="text-amber-300">11.8 ضعفاً</strong> مع صفر فروقات دفترية معلقة.
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" onclick="window.location.href='direct-sales.php'" class="py-2 px-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-xs cursor-pointer">🛒 شاشة المبيعات</button>
                            <button type="button" onclick="window.location.href='posting-accounts.php'" class="py-2 px-3 rounded-xl bg-sky-500 hover:bg-sky-400 text-slate-950 font-extrabold text-xs cursor-pointer">📑 ترحيل الحسابات</button>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-4 bento-card p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                <button type="button" onclick="openFullCardModal('live_radar')" title="توسيع الرادار المحاسبي الحي والأذونات" class="w-7 h-7 rounded-lg bg-sky-500/25 hover:bg-sky-500/45 border-2 border-sky-400 text-white font-black text-lg flex items-center justify-center cursor-pointer shrink-0 shadow">+</button>
                                <button type="button" onclick="openChartCardModal('live_radar')" title="عرض الرسم البياني للحركات اليومية والأذونات" class="w-7 h-7 rounded-lg bg-amber-500/25 hover:bg-amber-500/45 border-2 border-amber-400 text-amber-300 font-black text-sm flex items-center justify-center cursor-pointer shrink-0 shadow">📊</button>
                                <h2 class="font-black text-white whitespace-nowrap flex-1" style="font-size:18.5px!important;">الرادار المحاسبي الحي</h2>
                            </div>
                            <span class="text-xs text-emerald-300 font-bold shrink-0">طباعة فورية 🖨️</span>
                        </div>
                        <div class="grid grid-cols-4 gap-1 mb-2.5" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:4px;">
                            <button type="button" onclick="switchLiveRadarFeed('all', this)" class="radar-glow-btn active-radar-tab py-1 px-1 rounded-lg bg-sky-600 text-white text-[11px] font-black text-center whitespace-nowrap">⚡ الكل</button>
                            <button type="button" onclick="switchLiveRadarFeed('sales', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">🛒 المبيعات</button>
                            <button type="button" onclick="switchLiveRadarFeed('purchases', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">🚚 التوريد</button>
                            <button type="button" onclick="switchLiveRadarFeed('vouchers', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">🧾 السندات</button>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5 mb-2.5 num-font">
                            <div class="inner-box p-2.5 flex items-center justify-between"><span class="text-xs text-slate-300">رصيد الخزينة اللحظي:</span><strong id="radar-cash-now" class="text-sm font-black text-emerald-400">3,450,000</strong></div>
                            <div class="inner-box p-2.5 flex items-center justify-between"><span class="text-xs text-slate-300">رصيد المخزون اللحظي:</span><strong id="radar-stock-now" class="text-sm font-black text-amber-400">420.5 طن</strong></div>
                        </div>
                        <div id="radar-feed" class="space-y-2.5 pl-1 num-font">
                            <div onclick="printReceipt('مؤسسة البنيان الحديثة', 'فاتورة بيع نقدي', '1.0', '135', '9.0', '420,000')" class="inner-box p-3.5 hover:border-emerald-400 cursor-pointer transition flex items-center justify-between"><div><div class="font-bold text-white text-sm">مؤسسة البنيان الحديثة <span class="text-emerald-400">(فاتورة بيع نقدي)</span></div><div class="text-xs text-slate-300">الوزن: 1.0 طن | 135 سيخة تجاري | 9.0 شيلة</div></div><div class="text-left"><div class="font-black text-emerald-400 text-sm" data-sdg="420000">420,000 ج.س</div><div class="text-[11px] text-slate-300">اضغط للطباعة 🖨️</div></div></div>
                            <div onclick="printReceipt('توريد سيخ تسليح 16مم', 'سند مشتريات', '15.0', '2025', '135.0', '5,850,000')" class="inner-box p-3.5 hover:border-emerald-400 cursor-pointer transition flex items-center justify-between"><div><div class="font-bold text-white text-sm">توريد سيخ تسليح 16مم <span class="text-amber-400">(سند مشتريات)</span></div><div class="text-xs text-slate-300">الوزن: 15.0 طن | مخزن السجانة الرئيسي</div></div><div class="text-left"><div class="font-black text-amber-400 text-sm" data-sdg="5850000">5,850,000 ج.س</div><div class="text-[11px] text-slate-300">اضغط للطباعة 🖨️</div></div></div>
                            <div onclick="printReceipt('شركة النيل للمقاولات', 'سند قبض بنكي', '3.0', '405', '27.0', '1,250,000')" class="inner-box p-3.5 hover:border-emerald-400 cursor-pointer transition flex items-center justify-between"><div><div class="font-bold text-white text-sm">شركة النيل للمقاولات <span class="text-sky-400">(سند قبض بنكي)</span></div><div class="text-xs text-slate-300">تحصيل دفعة عبر بنك الخرطوم • مطابقة فورية</div></div><div class="text-left"><div class="font-black text-sky-400 text-sm" data-sdg="1250000">1,250,000 ج.س</div><div class="text-[11px] text-slate-300">اضغط للطباعة 🖨️</div></div></div>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-white/15">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-extrabold text-amber-300">📦 أذونات لم تُستلم وتنبيهات المهام</h3>
                            <span id="pending-badge" class="px-2 py-0.5 rounded-md bg-rose-500/20 text-rose-300 text-xs font-bold">يتطلب إجراء</span>
                        </div>
                        <div id="alerts-list" class="space-y-2 text-sm">
                            <div class="inner-box p-2.5 border-r-4 border-r-amber-400 flex items-center justify-between"><div><div class="font-bold text-white text-sm">إذن استلام مخزني #1084 (لم يُستلم بعد)</div><div class="text-xs text-slate-300">شحنة 20 طن سيخ 12مم - بانتظار مطابقة الميزان</div></div><button onclick="resolveTask(this)" class="px-3 py-1.5 rounded-lg bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold text-xs">اعتماد ✓</button></div>
                            <div class="inner-box p-2.5 border-r-4 border-r-rose-500 flex items-center justify-between"><div><div class="font-bold text-white text-sm">مهمة: تحصيل دفعة مغلق السجانة</div><div class="text-xs text-slate-300">تجاوز سقف الائتمان المسموح بنسبة 94%</div></div><button onclick="resolveTask(this)" class="px-3 py-1.5 rounded-lg bg-rose-500 hover:bg-rose-400 text-white font-extrabold text-xs">معالجة</button></div>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-4 bento-card p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                <button type="button" onclick="openFullCardModal('cashflow')" title="توسيع مؤشرات التدفق والمبيعات والمخزون بالكامل" class="w-7 h-7 rounded-lg bg-emerald-500/25 hover:bg-emerald-500/45 border-2 border-emerald-400 text-white font-black text-lg flex items-center justify-center cursor-pointer shrink-0 shadow">+</button>
                                <button type="button" onclick="openChartCardModal('cashflow')" title="عرض الرسم البياني لمؤشرات التدفق والمبيعات والمخزون" class="w-7 h-7 rounded-lg bg-amber-500/25 hover:bg-amber-500/45 border-2 border-amber-400 text-amber-300 font-black text-sm flex items-center justify-center cursor-pointer shrink-0 shadow">📊</button>
                                <h2 class="font-black text-white whitespace-nowrap flex-1" style="font-size:17.5px!important;">مؤشرات التدفق والمبيعات والمخزون</h2>
                            </div>
                            <span class="px-2 py-0.5 rounded-lg inner-box text-[10.5px] text-emerald-300 font-black shrink-0">🤖 ذكاء حي</span>
                        </div>
                        <div class="grid grid-cols-4 gap-1 mb-2" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:4px;">
                            <button type="button" onclick="switchCashflowCardView('kpis', this)" class="radar-glow-btn active-radar-tab py-1 px-1 rounded-lg bg-emerald-600 text-white text-[11px] font-black text-center whitespace-nowrap">💧 الكفاءة</button>
                            <button type="button" onclick="switchCashflowCardView('ccc', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">🔄 دورة النقد</button>
                            <button type="button" onclick="switchCashflowCardView('ai_cf', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">💸 المصروفات</button>
                            <button type="button" onclick="switchCashflowCardView('stress', this)" class="radar-glow-btn py-1 px-1 rounded-lg bg-slate-800 text-slate-200 text-[11px] font-bold text-center whitespace-nowrap">🛡️ التحوط</button>
                        </div>
                    </div>
                    <div id="dash-cashflow-kpis-box" class="grid grid-cols-2 gap-2.5 my-1 num-font flex-1">
                        <div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟢 التدفق النقدي</span><strong class="text-base font-black text-emerald-300">84%</strong></div>
                        <div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟢 سيولة المبيعات</span><strong class="text-base font-black text-emerald-300">72%</strong></div>
                        <div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟡 وفرة المخزون</span><strong class="text-base font-black text-amber-300">78%</strong></div>
                        <div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟡 سعة المخازن</span><strong class="text-base font-black text-amber-300">64%</strong></div>
                        <div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟢 كفاءة التحصيل</span><strong class="text-base font-black text-emerald-300">81%</strong></div>
                        <div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟢 دوران البضاعة</span><strong class="text-base font-black text-sky-300">2.4x</strong></div>
                        <div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟢 تغطية الشيكات</span><strong class="text-base font-black text-emerald-300">96%</strong></div>
                        <div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟢 هامش الأمان</span><strong class="text-base font-black text-emerald-300">88%</strong></div>
                    </div>
                    <div class="inner-box p-2.5 mt-2 flex flex-col gap-1.5 num-font">
                        <div class="flex items-center justify-between text-xs font-black">
                            <span class="text-sky-300">📈 منحنى صافي التدفق ودوران النقدية (7 أيام)</span>
                            <span class="text-emerald-400">+22.4% فائض تشغيلي ▲</span>
                        </div>
                        <svg viewBox="0 0 320 54" style="width:100%;height:50px;overflow:visible;">
                            <defs><linearGradient id="cfMiniG" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#10b981" stop-opacity="0.45"/><stop offset="100%" stop-color="#0284c7" stop-opacity="0.02"/></linearGradient></defs>
                            <path d="M4,42 Q52,36 98,28 T196,18 T316,8 L316,50 L4,50 Z" fill="url(#cfMiniG)"/>
                            <path d="M4,42 Q52,36 98,28 T196,18 T316,8" fill="none" stroke="#34d399" stroke-width="2.8" stroke-linecap="round"/>
                            <circle cx="98" cy="28" r="3.5" fill="#38bdf8"/><circle cx="196" cy="18" r="3.5" fill="#fbbf24"/><circle cx="316" cy="8" r="4" fill="#fde047" stroke="#059669" stroke-width="1.5"/>
                        </svg>
                        <div class="text-[11px] font-bold text-slate-300">⚡ سرعة التحول النقدي ممتازة: المتاح النقدي يمول دورة التوريد القادمة دون سحب ائتماني.</div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-white/10 text-xs num-font">
                        <div class="inner-box p-2.5"><span class="text-slate-300 block text-xs mb-1">1. التدفقات الداخلة</span><strong class="text-emerald-400 text-sm font-black" id="cf-in-val">+1,320,000</strong></div>
                        <div class="inner-box p-2.5"><span class="text-slate-300 block text-xs mb-1">2. التدفقات الخارجة</span><strong class="text-rose-400 text-sm font-black" id="cf-out-val">-410,000</strong></div>
                        <div class="inner-box p-2.5"><span class="text-slate-300 block text-xs mb-1">3. صافي المركز المالي</span><strong class="text-amber-300 text-sm font-black" id="net-worth-val">3,980,000</strong></div>
                    </div>
                    <div style="display:none" aria-hidden="true"><input id="sajana-tons" type="hidden" value="1.0"><input id="sajana-amount" type="hidden" value="420,000"><span id="res-comm"></span><span id="res-weight"></span><span id="res-bundles"></span></div>
                </div>
            </section>
            <!-- 5.5 المنظومة العالمية للتحليل البياني البصري التلقائي ورادار التنبؤ بالذكاء الاصطناعي للمخاطر -->
            <div id="auto-charts-slot" class="w-full mt-4"></div>
            <div id="curtain-drawer" style="display:none!important;" aria-hidden="true">
                <input id="cur-liq" type="hidden" value="3450000">
                <input id="cur-usd" type="hidden" value="600">
                <input id="cur-ar" type="hidden" value="820000">
                <input id="cur-ap" type="hidden" value="290000">
                <input id="cur-tons" type="hidden" value="420.5">
                <input id="cur-rebar" type="hidden" value="420000">
            </div>

            <!-- 7. نافذة طباعة الفاتورة الفورية -->
            <div id="print-modal" class="fixed inset-0 bg-black/75 z-50 hidden items-center justify-center p-4">
                <div class="bg-white text-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl num-font">
                    <div class="text-center border-b-2 border-slate-200 pb-3 mb-4">
                        <h2 class="text-xl font-black text-emerald-800">نظام السيطرة ERP - السجانة</h2>
                        <p class="text-xs text-slate-500" id="prt-type">فاتورة مبيعات وأوزان معتمدة</p>
                    </div>
                    <div class="space-y-2 text-xs mb-4">
                        <div class="flex justify-between"><span>العميل / الجهة:</span><strong id="prt-client">-</strong></div>
                        <div class="flex justify-between"><span>الوزن الفعلي بالطن:</span><strong id="prt-tons">-</strong></div>
                        <div class="flex justify-between"><span>العدد المعادل (تجاري 135):</span><strong id="prt-bars">-</strong></div>
                        <div class="flex justify-between"><span>عدد الشيلات (15/شيلة):</span><strong id="prt-bundles">-</strong></div>
                        <div class="flex justify-between text-sm border-t pt-2 mt-2"><span>الإجمالي الكلي:</span><strong class="text-emerald-700 font-black" id="prt-total">-</strong></div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="window.print()" class="flex-1 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-xs">🖨️ طباعة الآن</button>
                        <button onclick="closePrintModal()" class="px-4 py-2.5 rounded-xl bg-slate-200 text-slate-800 font-bold text-xs">إغلاق</button>
                    </div>
                </div>
            </div>

            <!-- Hidden Legacy DOM Hooks -->
            <div style="display:none;" aria-hidden="true">
                <span id="current-scope-label"></span><span id="dash-live-fx-badge"></span><button id="dash-cur-toggle-btn"></button><span id="dash-top-jobs-count"></span>
                <span id="kpi-sales-title"></span><span id="kpi-sales"></span><span id="kpi-vault-liquidity"></span><span id="kpi-liquidity"></span><span id="kpi-monthly-profit"></span><span id="kpi-inventory"></span><span id="kpi-warehouses-count"></span><span id="kpi-alerts"></span>
                <span id="cf-summary-in"></span><span id="cf-summary-out"></span><span id="cf-summary-net"></span>
                <canvas id="customersChart"></canvas><canvas id="cashflowChart"></canvas><canvas id="categoryChart"></canvas><canvas id="inventoryChart"></canvas><canvas id="warehousesChart"></canvas>
                <table><tbody id="recent-transactions-tbody"></tbody></table><div id="pending-deliveries-list"></div><div id="low-stock-list"></div><div id="dashboard-jobs-list"></div>
            </div>
            <!-- 8. المحرك البرمجي الشامل -->
<script src="js/screens/magaiees_unified_dashboard-j1.js?v=<?= @filemtime(__DIR__ . '/js/screens/magaiees_unified_dashboard-j1.js') ?: '2027' ?>"></script>
        </div>
    </div>

    <!-- Dual-Tab Notifications & Alerts Control Modal Overlay -->
    <div id="notifications-dropdown" style="display:none; position:fixed; top:0; left:0; width: 100%; height:100vh; background:rgba(5,3,8,0.85);  z-index:9999; justify-content:center; align-items:center; opacity:0; transition: opacity 0.3s ease;" onclick="toggleNotifications()">
        <div class="glass-panel" style="width:95%; max-width:640px; max-height:85vh; overflow-y:auto; padding:22px; border:2px solid rgba(244,63,94,0.3); border-radius:24px; position:relative; box-shadow: 0 25px 60px rgba(0,0,0,0.7); background: rgba(14, 8, 28, 0.95);" onclick="event.stopPropagation()">
            
            <!-- Modal Header & Tabs -->
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:12px; margin-bottom:15px; flex-wrap:wrap; gap:8px;">
                <div style="display:flex; gap:8px;">
                    <button type="button" id="notif-tab-live-btn" onclick="switchModalNotifTab('LIVE')" style="padding:8px 14px; border-radius:12px; font-size:0.85rem; font-weight:800; border:1px solid #f43f5e; background:rgba(244,63,94,0.25); color:#fff; cursor:pointer; display:flex; align-items:center; gap:6px;">
                        <i class="fa-solid fa-bell text-rose-400"></i> الإنذارات النشطة
                    </button>
                    <button type="button" id="notif-tab-ctrl-btn" onclick="switchModalNotifTab('CTRL')" style="padding:8px 14px; border-radius:12px; font-size:0.85rem; font-weight:800; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.05); color:#94a3b8; cursor:pointer; display:flex; align-items:center; gap:6px;">
                        <i class="fa-solid fa-sliders text-amber-400"></i> 🎛️ التحكم في التنبيهات (On/Off)
                    </button>
                </div>
                <button type="button" onclick="toggleNotifications()" style="background:none; border:none; color:#94a3b8; font-size:1.3rem; cursor:pointer; padding:4px 8px; font-weight:900;">✕</button>
            </div>

            <!-- Tab 1: Live Alerts List -->
            <div id="modal-tab-live">
                <div id="notif-list" style="display:flex; flex-direction:column; gap:10px;">
                    <div style="color:var(--text-secondary); text-align:center; font-size:0.85rem; padding:15px;">جاري تحميل التنبيهات...</div>
                </div>
            </div>

            <!-- Tab 2: Quick Rules Control Panel -->
            <div id="modal-tab-ctrl" style="display:none;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; gap:8px; flex-wrap:wrap;">
                    <span style="font-size:0.8rem; color:#cbd5e1; font-weight:700;">🎛️ يمكنك تفعيل أو إيقاف أي تنبيه بنقرة واحدة:</span>
                    <a href="alarm.php" style="font-size:0.75rem; color:#38bdf8; text-decoration:none; font-weight:800; background:rgba(56,189,248,0.1); padding:4px 10px; border-radius:8px; border:1px solid rgba(56,189,248,0.25);">مركز التخصيص المتقدم ↗</a>
                </div>
                <div style="margin-bottom:10px;">
                    <input type="text" id="modal-rules-search" oninput="filterModalRules(this.value)" placeholder="🔍 ابحث عن أي تنبيه أو شرط..." style="width:100%; box-sizing:border-box; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); border-radius:10px; padding:7px 12px; color:#fff; font-size:0.8rem; outline:none; font-family:'Tajawal', sans-serif;">
                </div>
                <div id="modal-rules-list" style="display:flex; flex-direction:column; gap:8px; max-height:480px; overflow-y:auto; padding-right:4px;">
                    <div style="text-align:center; color:#94a3b8; font-size:0.85rem; padding:15px;">جاري تحميل قواعد التنبيهات...</div>
                </div>
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20261104_v30"></script>
<script src="js/screens/magaiees_unified_dashboard-j2.js?v=<?= @filemtime(__DIR__ . '/js/screens/magaiees_unified_dashboard-j2.js') ?: '2027' ?>"></script>

        <!-- AI CFO Modal -->
        <div id="aiCFO-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center; direction: rtl;">
            <div class="glass-panel" style="width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto; padding: 25px; position: relative;">
                <button onclick="document.getElementById('aiCFO-modal').style.display='none'" style="position: absolute; top: 15px; left: 15px; background: none; border: none; color: #f43f5e; font-size: 1.5rem; cursor: pointer;">&times;</button>
                <h3 style="color: #0ea5e9; margin-bottom: 20px; font-weight: 800; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
                    <span style="font-size: 1.5rem;">🤖</span> تقرير المستشار المالي (AI CFO)
                </h3>
                <div id="aiCFO-content" style="color: #e2e8f0; line-height: 1.8; font-size: 1.05rem;">
                    جاري تحليل بيانات الشركة واستخراج التقرير... يرجى الانتظار ⏳
                </div>
            </div>
        </div>

<script src="js/screens/magaiees_unified_dashboard-j3.js?v=<?= @filemtime(__DIR__ . '/js/screens/magaiees_unified_dashboard-j3.js') ?: '2027' ?>"></script>
    
        <!-- Client Support Tickets FAB (Removed as per user request to integrate into AI menu) -->

        <!-- Client Support Modal -->
        <div id="client-support-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7);  z-index: 10000; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
            <div class="glass-panel" style="width: 100%; max-width: 600px; max-height: 90vh; display: flex; flex-direction: column; padding: 0; overflow: hidden; border-radius: 20px;">
                <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.2);">
                    <h3 style="margin: 0; font-size: 1.2rem; display: flex; align-items: center; gap: 10px; color: white;">
                        <i class="fa-solid fa-headset" style="color: #60a5fa;"></i> مركز الدعم الفني
                    </h3>
                    <button onclick="closeClientSupportModal()" style="background: transparent; border: none; color: #ef4444; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 5px; border-radius: 50%; transition: background 0.2s;" onmouseover="this.style.backgroundColor='rgba(239, 68, 68, 0.1)'" onmouseout="this.style.backgroundColor='transparent'">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                
                <div style="display: flex; border-bottom: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.1);">
                    <button onclick="switchSupportTab('list')" id="tab-btn-list" style="flex: 1; padding: 15px; background: transparent; border: none; color: white; cursor: pointer; border-bottom: 2px solid #3b82f6; font-weight: bold; outline: none; transition: all 0.2s;">تذاكري</button>
                    <button onclick="switchSupportTab('new')" id="tab-btn-new" style="flex: 1; padding: 15px; background: transparent; border: none; color: #94a3b8; cursor: pointer; border-bottom: 2px solid transparent; font-weight: bold; outline: none; transition: all 0.2s;">تذكرة جديدة</button>
                </div>

                <!-- Tab: Ticket List -->
                <div id="support-tab-list" style="padding: 20px; overflow-y: auto; flex: 1; min-height: 300px;">
                    <div id="client-tickets-container" style="display: flex; flex-direction: column; gap: 10px;">
                        <!-- Tickets load here -->
                    </div>
                </div>

                <!-- Tab: New Ticket -->
                <div id="support-tab-new" style="padding: 20px; display: none; overflow-y: auto; flex: 1; min-height: 300px;">
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 8px; color: #cbd5e1; font-size: 0.9rem;">عنوان المشكلة / الطلب</label>
                        <input type="text" id="new-ticket-subject" placeholder="مثال: مشكلة في طباعة الفاتورة" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3); color: white; outline: none; box-sizing: border-box; font-family: inherit;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 8px; color: #cbd5e1; font-size: 0.9rem;">التفاصيل</label>
                        <textarea id="new-ticket-message" rows="5" placeholder="اشرح المشكلة بالتفصيل لمساعدتنا في حلها بسرعة..." style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3); color: white; outline: none; box-sizing: border-box; resize: vertical; font-family: inherit;"></textarea>
                    </div>
                    <button onclick="submitNewClientTicket()" style="width: 100%; padding: 14px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: opacity 0.2s; font-family: inherit;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">إرسال التذكرة</button>
                </div>
                
                <!-- Tab: Ticket Conversation -->
                <div id="support-tab-chat" style="padding: 0; display: none; flex-direction: column; flex: 1; min-height: 300px;">
                    <div style="padding: 15px; background: rgba(0,0,0,0.2); border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 10px;">
                        <button onclick="switchSupportTab('list')" style="background: rgba(255,255,255,0.1); border: none; border-radius: 6px; color: white; width: 30px; height: 30px; cursor: pointer; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-arrow-right"></i></button>
                        <h4 id="chat-ticket-subject" style="margin: 0; color: white; flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">...</h4>
                    </div>
                    <div id="chat-messages-container" style="flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; min-height: 250px;">
                        <!-- Messages -->
                    </div>
                    <div style="padding: 15px; background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.1); display: flex; gap: 10px;">
                        <input type="hidden" id="chat-ticket-id">
                        <input type="text" id="chat-reply-input" placeholder="اكتب ردك هنا..." style="flex: 1; padding: 12px 15px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3); color: white; outline: none; font-family: inherit;" onkeypress="if(event.key==='Enter') sendClientReply()">
                        <button onclick="sendClientReply()" style="background: #10b981; color: white; border: none; border-radius: 50%; width: 45px; height: 45px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; padding-right: 5px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" style="transform: rotate(180deg); pointer-events: none;">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" style="pointer-events: none;"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        function openClientSupportModal() {
            document.getElementById('client-support-modal').style.display = 'flex';
            switchSupportTab('list');
            loadClientTickets();
        }

        function closeClientSupportModal() {
            document.getElementById('client-support-modal').style.display = 'none';
        }

        function switchSupportTab(tab) {
            document.getElementById('support-tab-list').style.display = tab === 'list' ? 'block' : 'none';
            document.getElementById('support-tab-new').style.display = tab === 'new' ? 'block' : 'none';
            document.getElementById('support-tab-chat').style.display = tab === 'chat' ? 'flex' : 'none';
            
            document.getElementById('tab-btn-list').style.borderBottomColor = tab === 'list' ? '#3b82f6' : 'transparent';
            document.getElementById('tab-btn-list').style.color = tab === 'list' ? 'white' : '#94a3b8';
            document.getElementById('tab-btn-new').style.borderBottomColor = tab === 'new' ? '#3b82f6' : 'transparent';
            document.getElementById('tab-btn-new').style.color = tab === 'new' ? 'white' : '#94a3b8';
        }

        async function loadClientTickets() {
            const container = document.getElementById('client-tickets-container');
            container.innerHTML = '<div style="text-align:center; padding: 20px; color: #94a3b8;"><i class="fa-solid fa-circle-notch fa-spin"></i> جاري التحميل...</div>';
            try {
                const res = await fetch('api_index.php?r=client-tickets', {
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('authToken') }
                });
                const data = await res.json();
                if (data.success) {
                    if (data.tickets.length === 0) {
                        container.innerHTML = '<div style="text-align:center; padding: 30px; color: #64748b;"><i class="fa-solid fa-inbox" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i><br>لا توجد تذاكر حالية</div>';
                        return;
                    }
                    container.innerHTML = '';
                    data.tickets.forEach(tk => {
                        let statusColor = '#f59e0b'; let statusText = 'مفتوحة';
                        if (tk.status === 'in_progress') { statusColor = '#38bdf8'; statusText = 'قيد المعالجة'; }
                        if (tk.status === 'closed') { statusColor = '#10b981'; statusText = 'مغلقة'; }
                        
                        container.innerHTML += `
                            <div onclick="openClientTicketChat(${tk.id}, '${tk.subject.replace(/'/g, "\\'")}')" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 12px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='rgba(0,0,0,0.3)'">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <strong style="color: white; font-size: 1.05rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 70%;">${tk.subject}</strong>
                                    <span style="background: ${statusColor}20; color: ${statusColor}; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; border: 1px solid ${statusColor}40;">${statusText}</span>
                                </div>
                                <div style="color: #64748b; font-size: 0.8rem; display: flex; justify-content: space-between;">
                                    <span>تذكرة #${tk.id}</span>
                                    <span style="direction: ltr;">${new Date(tk.created_at).toLocaleString('ar-SA')}</span>
                                </div>
                            </div>
                        `;
                    });
                }
            } catch (e) {
                container.innerHTML = '<div style="text-align:center; color: #ef4444;">❌ خطأ في تحميل التذاكر</div>';
            }
        }

        async function submitNewClientTicket() {
            const subject = document.getElementById('new-ticket-subject').value.trim();
            const message = document.getElementById('new-ticket-message').value.trim();
            if (!subject || !message) {
                if (typeof showToast === 'function') showToast('يرجى كتابة عنوان وتفاصيل المشكلة', 'warning');
                else alert('يرجى كتابة عنوان وتفاصيل المشكلة');
                return;
            }
            try {
                const res = await fetch('api_index.php?r=client-tickets', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + localStorage.getItem('authToken') },
                    body: JSON.stringify({ subject, message })
                });
                const data = await res.json();
                if (data.success) {
                    document.getElementById('new-ticket-subject').value = '';
                    document.getElementById('new-ticket-message').value = '';
                    switchSupportTab('list');
                    loadClientTickets();
                    if (typeof showToast === 'function') showToast('✅ تم فتح التذكرة وإرسالها للدعم الفني', 'success');
                }
            } catch (e) {}
        }

        async function openClientTicketChat(id, subject) {
            document.getElementById('chat-ticket-id').value = id;
            document.getElementById('chat-ticket-subject').innerText = subject;
            switchSupportTab('chat');
            
            const container = document.getElementById('chat-messages-container');
            container.innerHTML = '<div style="text-align:center; padding: 20px; color: #94a3b8;"><i class="fa-solid fa-circle-notch fa-spin"></i> جاري التحميل...</div>';
            
            try {
                const res = await fetch(`api_index.php?r=client-tickets&a=replies&id=${id}`, {
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('authToken') }
                });
                const data = await res.json();
                if (data.success) {
                    container.innerHTML = '';
                    data.replies.forEach(rep => {
                        const isClient = rep.sender === 'client';
                        const align = isClient ? 'flex-start' : 'flex-end';
                        const bg = isClient ? '#3b82f6' : 'rgba(255,255,255,0.1)';
                        const color = isClient ? 'white' : '#cbd5e1';
                        const radius = isClient ? '15px 15px 0 15px' : '15px 15px 15px 0';
                        const name = isClient ? 'أنت' : 'الدعم الفني';
                        const icon = isClient ? 'fa-user' : 'fa-headset';
                        
                        container.innerHTML += `
                            <div style="align-self: ${align}; max-width: 85%; display: flex; flex-direction: column; gap: 5px;">
                                <span style="font-size: 0.75rem; color: #64748b; margin: 0 5px; align-self: ${align}; display: flex; align-items: center; gap: 5px;"><i class="fa-solid ${icon}"></i> ${name}</span>
                                <div style="background: ${bg}; color: ${color}; padding: 12px 18px; border-radius: ${radius}; font-size: 0.95rem; line-height: 1.6; border: 1px solid rgba(255,255,255,0.05);">
                                    ${rep.message.replace(/\n/g, '<br>')}
                                </div>
                                <span style="font-size: 0.7rem; color: #475569; align-self: ${align}; direction: ltr;">${new Date(rep.created_at).toLocaleString('ar-SA')}</span>
                            </div>
                        `;
                    });
                    if (data.replies.length === 0) {
                        container.innerHTML = '<div style="text-align:center; padding: 20px; color: #64748b;">لا توجد ردود حتى الآن</div>';
                    }
                    setTimeout(() => container.scrollTop = container.scrollHeight, 100);
                }
            } catch (e) {
                container.innerHTML = '<div style="text-align:center; color: #ef4444;">❌ خطأ في تحميل الردود</div>';
            }
        }

        async function sendClientReply() {
            const id = document.getElementById('chat-ticket-id').value;
            const input = document.getElementById('chat-reply-input');
            const message = input.value.trim();
            if (!message) return;
            
            input.disabled = true;
            try {
                const res = await fetch('api_index.php?r=client-tickets&a=reply', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + localStorage.getItem('authToken') },
                    body: JSON.stringify({ id, message })
                });
                const data = await res.json();
                if (data.success) {
                    input.value = '';
                    openClientTicketChat(id, document.getElementById('chat-ticket-subject').innerText);
                } else {
                    if (typeof showToast === 'function') showToast(data.error || 'خطأ في إرسال الرد', 'error');
                    else alert(data.error || 'خطأ في إرسال الرد');
                }
            } catch (e) {
                console.error("Reply Error: ", e);
                alert("حدث خطأ غير متوقع: " + e.message);
            }
            input.disabled = false;
            input.focus();
        }
        </script>
<!-- Floating Return to Mobile Button (Only displays when viewed on mobile / small screen) -->
<div id="mobile-return-float-container" style="position:fixed; bottom:22px; right:22px; z-index:99999; display:none;">
    <button type="button" onclick="switchToMobileEdition()" style="background:linear-gradient(135deg, #10b981, #059669); color:#fff; border:2px solid #34d399; padding:12px 22px; border-radius:30px; font-weight:900; font-size:0.95rem; box-shadow:0 10px 30px rgba(16,185,129,0.5); cursor:pointer; display:flex; align-items:center; gap:8px; font-family:'Tajawal',sans-serif;">
        <i class="fa-solid fa-mobile-screen-button"></i> 📱 العودة لنسخة الجوال / Mobile View
    </button>
</div>
<script>
(function() {
    var params = new URLSearchParams(window.location.search);
    var isMobile = /Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Samsung|Huawei|Honor/i.test(navigator.userAgent) 
                || (navigator.maxTouchPoints && navigator.maxTouchPoints > 0 && window.innerWidth <= 1024)
                || (window.screen.width <= 1024)
                || (params.get('force_desktop') === '1');
    if (isMobile) {
        var btn = document.getElementById('mobile-return-float-container');
        if (btn) btn.style.display = 'block';
    }
})();
</script>
<script src="js/magaies-offline-sync.js?v=20261014"></script>
<script src="js/unified-brain.js?v=20260722"></script>
    <!-- ══════════════════════════════════════════════════════════════════════════
         PURCHASE DESIGN CONSTITUTION 2027 — UNIFIED DASHBOARD
         9 Tones + 3px Sculpted Borders + 3D KPI Capsules + Radar Lamps + FX Toggle
         ══════════════════════════════════════════════════════════════════════════ -->
<link rel="stylesheet" id="dashboard-purchase-constitution-2027-style" href="css/screens/magaiees_unified_dashboard-s2.css?v=<?= @filemtime(__DIR__ . '/css/screens/magaiees_unified_dashboard-s2.css') ?: '2027' ?>">
</body>
</html>
