<?php if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() === 0) { @ob_start('ob_gzhandler'); } ?>
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
<script src="js/lang.js"></script>
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
    <meta name="apple-mobile-web-app-title" content="مقاييس ERP">
    <link rel="apple-touch-icon" href="assets/apple-touch-icon.png?v=2026_m_v5">
    <link rel="icon" type="image/svg+xml" href="assets/icon.svg?v=2026_m_v5">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/icon-192.png?v=2026_m_v5">
    <link rel="icon" type="image/png" sizes="256x256" href="assets/icon-256.png?v=2026_m_v5">
    <link rel="icon" type="image/png" sizes="384x384" href="assets/icon-384.png?v=2026_m_v5">
    <link rel="icon" type="image/png" sizes="512x512" href="assets/icon-512.png?v=2026_m_v5">
    <link rel="icon" type="image/x-icon" href="favicon.ico?v=2026_m_v5">
    <link rel="shortcut icon" href="assets/favicon.ico?v=2026_m_v5">
    <meta name="theme-color" content="#0F172A">

    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="dns-prefetch" href="https://cdn.jsdelivr.net"><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&family=Cairo:wght@500;600;700;800;900&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Magaiees ERP - التحكم المتكاملة | الشاشة العالمية الموحدة</title>
    <link rel="stylesheet" href="css/tailwind.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preload" href="css/design-system.css?v=20260723_v14" as="style">
    <link rel="stylesheet" href="css/design-system.css?v=20260723_v14">
    <link rel="stylesheet" href="css/theme.css?v=<?php echo file_exists('css/theme.css') ? filemtime('css/theme.css') : '12'; ?>">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/unified-brain.css?v=20260722">
    <script src="js/pwa-manager.js" defer></script>
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        html {
            font-size: 16px;
        }
        @media (min-width: 1440px) {
            html { font-size: 17px; }
        }
        @media (min-width: 1920px) {
            html { font-size: 18px; }
        }

        :root {
            --bg-color: #050308;
            --surface: rgba(18, 15, 25, 0.65);
            --surface-hover: rgba(28, 24, 38, 0.85);
            --border: rgba(255, 255, 255, 0.08);
            --border-highlight: rgba(255, 255, 255, 0.2);
            
            --primary-accent: #6366f1;
            --theme-pink: #ec4899;
            --theme-blue: #3b82f6;
            --theme-green: #10b981;
            --theme-orange: #f59e0b;
            --theme-red: #f87171;
            --theme-purple: #c084fc;
        }

        body {
            background-color: var(--bg-color);
            margin: 0; padding: 0;
            min-height: 100vh;
            display: flex; flex-direction: column;
            overflow-x: hidden;
            font-family: 'Tajawal', sans-serif;
            color: #fff;
            font-weight: 500;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.2); }
        ::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.4); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(99, 102, 241, 0.8); }

        /* Ambient Animated Background Orbs */
        .ambient-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            overflow: hidden; z-index: -1; pointer-events: none;
            background: radial-gradient(circle at 50% 50%, #0c0812 0%, #000000 100%);
        }

        .orb {
            position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.12;
            animation: floatOrb 20s infinite alternate ease-in-out;
            will-change: transform;
            contain: layout style paint;
        }

        .orb-1 { width: 500px; height: 500px; background: var(--primary-accent); top: -5%; right: -5%; }
        .orb-2 { width: 450px; height: 450px; background: var(--theme-pink); bottom: -10%; left: -5%; animation-delay: -7s; }
        .orb-3 { width: 600px; height: 600px; background: var(--theme-blue); top: 30%; left: 30%; animation-duration: 25s; opacity: 0.1; }

        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-30px, 30px) scale(0.9); }
            100% { transform: translate(0, 0) scale(1); }
        }

        /* Compact Layout Container */
        .dash-main {
            width: 100%;
            max-width: 1920px;
            margin: 0 auto;
            padding: 8px 16px 36px 16px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 12px;
            flex: 1;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Athkar Ticker */
        .islamic-ticker-container { overflow: hidden; white-space: nowrap; max-width: 400px; margin: 10px auto 0; position: relative; display: flex; align-items: center; border-radius: 8px; background: rgba(0,0,0,0.2); padding: 5px 10px; border: 1px solid rgba(255,255,255,0.05); }
        .islamic-ticker {
            display: inline-block; font-weight: 700; font-size: 0.95rem; color: transparent;
            background: linear-gradient(90deg, #f59e0b, #10b981, #3b82f6, #a855f7, #f59e0b);
            background-size: 200% auto; -webkit-background-clip: text; background-clip: text;
            animation: gradientMove 3s linear infinite, scrollTicker 20s linear infinite;
            text-shadow: 0 0 10px rgba(245, 158, 11, 0.2); white-space: nowrap; padding-left: 100%;
        }
        @keyframes scrollTicker { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }

        .header-brand { display: flex; align-items: center; gap: 12px; }
        .brand-icon {
            width: 40px; height: 40px; border-radius: 12px;
            background: linear-gradient(135deg, #38bdf8, #818cf8);
            display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
            box-shadow: 0 6px 15px rgba(56, 189, 248, 0.4), inset 0 2px 5px rgba(255,255,255,0.3); border: 1px solid rgba(255,255,255,0.2);
        }
        .brand-text h1 { margin: 0; font-size: 1.1rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; text-shadow: 0 2px 5px rgba(255,255,255,0.1); }
        .brand-text p { margin: 0; font-size: 0.75rem; color: #94a3b8; font-weight: 500; }

        .header-center { text-align: center; }
        .header-center h2 { margin: 0; font-size: 1.2rem; font-weight: 900; background: linear-gradient(to right, #fff, #cbd5e1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 1px; }

        .header-controls { display: flex; align-items: center; gap: 12px; }
        
        .clock-container {
            display: flex; flex-direction: column; align-items: center; 
            background: rgba(15,23,42,0.6); padding: 6px 12px; border-radius: 12px; 
            border: 1px solid rgba(255,255,255,0.08); min-width: 100px;
        }
        .clock-time { color: #38bdf8; font-size: 1rem; font-weight: 900; font-variant-numeric: tabular-nums; letter-spacing: 1px; text-shadow: 0 0 8px rgba(56,189,248,0.5); font-family: 'Outfit', sans-serif;}
        .clock-date { color: var(--text-secondary); font-size: 0.65rem; margin-top: 1px; }

        @keyframes tactileBuzz {
            0% { transform: translateY(-3px) scale(1.02) rotate(0deg); }
            25% { transform: translateY(-3px) scale(1.02) rotate(-0.5deg); }
            50% { transform: translateY(-3px) scale(1.02) rotate(0.5deg); }
            75% { transform: translateY(-3px) scale(1.02) rotate(-0.5deg); }
            100% { transform: translateY(-3px) scale(1.02) rotate(0deg); }
        }

        .icon-btn {
            background: rgba(255,255,255,0.03); border: 1px solid var(--border); width: 36px; height: 36px;
            border-radius: 10px; color: #fff; font-size: 1rem; display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1); position: relative;
        }
        .icon-btn:hover { 
            animation: tactileBuzz 0.1s linear infinite;
            background: rgba(56, 189, 248, 0.1); 
            border-color: #38bdf8; 
            box-shadow: 0 0 15px rgba(56,189,248,0.5), 0 0 30px rgba(56,189,248,0.3), inset 0 0 10px rgba(56,189,248,0.2); 
            z-index: 10;
        }
        .icon-btn > * { transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: inline-block; }
        .icon-btn:hover > * { transform: scale(1.2); }

        .user-info {
            display: flex; align-items: center; gap: 8px; background: rgba(15,23,42,0.6); padding: 6px 12px; 
            border-radius: 12px; border: 1px solid rgba(255,255,255,0.08);
        }

        /* Compact Glass Panels */
        .glass-panel {
            background: var(--surface); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border); border-radius: 18px; padding: 18px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3), inset 0 1px 1px rgba(255,255,255,0.03);
            transition: all 0.4s ease;
        }
        .glass-panel:hover { border-color: var(--border-highlight); box-shadow: 0 15px 35px rgba(0,0,0,0.4); transform: translateY(-3px); }

        .glass-panel h3 { margin: 0 0 15px 0; font-size: 1.05rem; font-weight: 800; display:flex; align-items:center; gap:8px; }

        /* Neon Borders */
        .neon-border-primary { border-top: 3px solid var(--theme-blue); }
        .neon-border-success { border-top: 3px solid var(--theme-green); }
        .neon-border-warning { border-top: 3px solid var(--theme-orange); }
        .neon-border-danger { border-top: 3px solid var(--theme-red); }
        .neon-border-purple { border-top: 3px solid var(--theme-purple); }

        /* Compact KPI Cards - Slim Executive 6-Card Grid (50% Reduced Height) */
        .kpi-cards {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(165px, 1fr)); gap: 8px;
        }
        .kpi-cards .glass-panel {
            padding: 5px 10px !important;
            border-radius: 10px !important;
            min-height: 44px !important;
        }
        .kpi-title { color: #ffffff !important; font-size: 0.74rem !important; font-weight: 800 !important; letter-spacing: 0.2px; text-shadow: 0 1px 2px rgba(0,0,0,0.5); }
        .kpi-icon-bg { padding: 2px 5px !important; border-radius: 6px !important; }
        .kpi-icon-bg span { font-size: 0.75rem !important; }
        .kpi-val { margin:0 !important; font-size:0.80rem !important; font-weight:800; font-family: 'Outfit', 'Tajawal', sans-serif; line-height: 1.1 !important; }
        .kpi-desc { font-size:0.6rem !important; margin-top:0 !important; font-weight: 700; }

        /* Dynamic Spotlight Glass Effect for KPI Cards */
        .spotlight-card {
            position: relative;
            overflow: hidden;
            transform: translateZ(0);
        }
        .spotlight-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(
                450px circle at var(--mouse-x, 0) var(--mouse-y, 0),
                rgba(255, 255, 255, 0.12),
                transparent 45%
            );
            z-index: 0;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .spotlight-card:hover::before {
            opacity: 1;
        }
        .spotlight-card > * {
            position: relative;
            z-index: 1;
        }

        /* Compact Charts Grid */
        .charts-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 15px;
        }
        .chart-container { position: relative; height: 240px; width: 100%; }
        
        .bottom-section {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 15px;
        }

        /* Compact Quick Action Buttons */
        .qa-card-wrapper {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.4) 0%, rgba(15, 23, 42, 0.6) 100%);
            padding: 15px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); 
            box-shadow: inset 0 0 15px rgba(255,255,255,0.02), 0 5px 15px rgba(0,0,0,0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease; height: 100%; box-sizing: border-box;
        }
        .qa-card-wrapper:hover {
            transform: translateY(-3px); box-shadow: inset 0 0 20px rgba(255,255,255,0.04), 0 8px 25px rgba(0,0,0,0.3);
        }
        .qa-card-wrapper h4 { margin: 0 0 12px 0; font-size: 0.95rem; font-weight: 800; display: flex; align-items: center; gap: 8px; }

        .quick-action-btn {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px; border-radius: 12px; text-decoration: none; font-weight: 800; font-size: 0.9rem;
            color: #fff; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 3px 10px rgba(0,0,0,0.2), inset 0 0 0 1px rgba(255,255,255,0.05);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1); position: relative; overflow: hidden;
            font-family: 'Tajawal', sans-serif;
        }
        .quick-action-btn:hover { 
            animation: tactileBuzz 0.1s linear infinite;
            border-color: rgba(255,255,255,0.3);
            z-index: 10;
        }

        .qa-primary { border-bottom: 2px solid rgba(56,189,248,0.5); }
        .qa-primary:hover { background: rgba(56,189,248,0.1); color: #38bdf8; border-color: #38bdf8; box-shadow: 0 0 15px rgba(56,189,248,0.6), 0 0 30px rgba(56,189,248,0.3), inset 0 0 15px rgba(56,189,248,0.2) !important; }
        
        .qa-warning { border-bottom: 2px solid rgba(251,191,36,0.5); }
        .qa-warning:hover { background: rgba(251,191,36,0.1); color: #fbbf24; border-color: #fbbf24; box-shadow: 0 0 15px rgba(251,191,36,0.6), 0 0 30px rgba(251,191,36,0.3), inset 0 0 15px rgba(251,191,36,0.2) !important; }
        
        .qa-success { border-bottom: 2px solid rgba(52,211,153,0.5); }
        .qa-success:hover { background: rgba(52,211,153,0.1); color: #34d399; border-color: #34d399; box-shadow: 0 0 15px rgba(52,211,153,0.6), 0 0 30px rgba(52,211,153,0.3), inset 0 0 15px rgba(52,211,153,0.2) !important; }
        
        .qa-danger { border-bottom: 2px solid rgba(248,113,113,0.5); }
        .qa-danger:hover { background: rgba(248,113,113,0.1); color: #f87171; border-color: #f87171; box-shadow: 0 0 15px rgba(248,113,113,0.6), 0 0 30px rgba(248,113,113,0.3), inset 0 0 15px rgba(248,113,113,0.2) !important; }
        
        .qa-purple { border-bottom: 2px solid rgba(192,132,252,0.5); }
        .qa-purple:hover { background: rgba(192,132,252,0.1); color: #c084fc; border-color: #c084fc; box-shadow: 0 0 15px rgba(192,132,252,0.6), 0 0 30px rgba(192,132,252,0.3), inset 0 0 15px rgba(192,132,252,0.2) !important; }

        .quick-action-btn:not([class*="qa-"]):hover { box-shadow: 0 0 15px rgba(255,255,255,0.4), 0 0 30px rgba(255,255,255,0.2), inset 0 0 15px rgba(255,255,255,0.1) !important; }

        .qa-icon {
            font-size: 1.3rem; /* Increased size by one step */
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px; /* Slightly larger wrapper */
            height: 38px;
            border-radius: 10px; /* Sharp rounded corners */
            border: 2px solid rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.04);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            margin-left: 8px; /* Spacing for RTL */
            flex-shrink: 0;
        }
        
        /* Default/Dark Mode: Color coding for the quick action buttons */
        /* 1. Settings */
        .admin-action-settings .qa-icon { background: rgba(100, 116, 139, 0.15); border-color: rgba(100, 116, 139, 0.35); }
        /* 2. Master Data */
        .admin-action-master .qa-icon { background: rgba(59, 130, 246, 0.15); border-color: rgba(59, 130, 246, 0.35); }
        /* 3. Jobs/Tasks */
        .admin-action-jobs .qa-icon { background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.35); }
        /* 4. Notifications */
        .admin-action-notifications .qa-icon { background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.35); }
        /* 5. Sales (qa-primary) */
        .qa-primary .qa-icon { background: rgba(56, 189, 248, 0.15); border-color: rgba(56, 189, 248, 0.35); }
        /* 6. Purchases & 8. Checks (qa-warning) */
        .qa-warning .qa-icon { background: rgba(251, 191, 36, 0.15); border-color: rgba(251, 191, 36, 0.35); }
        /* 7. Transactions (qa-danger) */
        .qa-danger .qa-icon { background: rgba(248, 113, 113, 0.15); border-color: rgba(248, 113, 113, 0.35); }
        /* 9. Reports (qa-purple) */
        .qa-purple .qa-icon { background: rgba(192, 132, 252, 0.15); border-color: rgba(192, 132, 252, 0.35); }
        /* 10. Warehouses (inventory-report link) */
        a[data-href="inventory-report.php"] .qa-icon { background: rgba(167, 139, 250, 0.15); border-color: rgba(167, 139, 250, 0.35); }

        /* Hover states in default/Dark Mode: scale to 1.4, border to 4.5px, background darkens */
        .admin-action-btn:hover .qa-icon,
        .quick-action-btn:hover .qa-icon {
            transform: scale(1.4) rotate(5deg) translateY(-2px) !important;
            border-width: 4.5px !important;
        }
        
        .admin-action-settings:hover .qa-icon { background: rgba(100, 116, 139, 0.45) !important; border-color: #64748b !important; }
        .admin-action-master:hover .qa-icon { background: rgba(59, 130, 246, 0.45) !important; border-color: #3b82f6 !important; }
        .admin-action-jobs:hover .qa-icon { background: rgba(16, 185, 129, 0.45) !important; border-color: #10b981 !important; }
        .admin-action-notifications:hover .qa-icon { background: rgba(239, 68, 68, 0.45) !important; border-color: #ef4444 !important; }
        .qa-primary:hover .qa-icon { background: rgba(56, 189, 248, 0.45) !important; border-color: #38bdf8 !important; }
        .qa-warning:hover .qa-icon { background: rgba(251, 191, 36, 0.45) !important; border-color: #fbbf24 !important; }
        .qa-danger:hover .qa-icon { background: rgba(248, 113, 113, 0.45) !important; border-color: #f87171 !important; }
        .qa-purple:hover .qa-icon { background: rgba(192, 132, 252, 0.45) !important; border-color: #c084fc !important; }
        a[data-href="inventory-report.php"]:hover .qa-icon { background: rgba(167, 139, 250, 0.45) !important; border-color: #a78bfa !important; }

        /* Compact Tables */
        .premium-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.85rem; }
        .premium-table th { padding: 10px; text-align: right; color: #94a3b8; border-bottom: 1px solid rgba(255,255,255,0.05); font-weight: 700; }
        .premium-table td { padding: 10px; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.03); }
        .premium-table tbody tr:hover td { background: rgba(255,255,255,0.02); }

        /* List Items */
        .list-item {
            display:flex; align-items:center; gap:12px; padding:12px; border-radius:12px; 
        }
        .list-icon {
            width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; 
        }
        .list-title { margin:0; color:#fff; font-size:0.9rem; }
        .list-sub { color:var(--text-secondary); font-size:0.75rem; }
        .list-btn { padding:6px 12px; background:var(--primary-accent); color:#fff; border-radius:8px; text-decoration:none; font-size:0.8rem; font-weight:800; }

        /* Admin action buttons styling */
        .admin-action-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 16px; border-radius: 20px; text-decoration: none; font-weight: 800; font-size: 0.85rem;
            color: #fff; background: rgba(15, 23, 42, 0.4); border: 1.5px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1); cursor: pointer;
            font-family: 'Tajawal', sans-serif;
            box-shadow: inset 0 0 10px rgba(255,255,255,0.01), 0 4px 10px rgba(0,0,0,0.15);
        }
        .admin-action-btn:hover {
            transform: translateY(-2px);
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.25);
            box-shadow: 0 6px 15px rgba(0,0,0,0.3);
        }
        .admin-action-settings {
            border-bottom: 2.5px solid #64748b !important;
        }
        .admin-action-settings:hover {
            color: #fff;
            box-shadow: 0 0 15px rgba(100,116,139,0.5), inset 0 0 10px rgba(100,116,139,0.15) !important;
            border-color: #94a3b8 !important;
        }
        .admin-action-master {
            border-bottom: 2.5px solid var(--theme-blue) !important;
        }
        .admin-action-master:hover {
            color: #fff;
            box-shadow: 0 0 15px rgba(59,130,246,0.5), inset 0 0 10px rgba(59,130,246,0.15) !important;
            border-color: #60a5fa !important;
        }
        .admin-action-jobs {
            border-bottom: 2.5px solid #10b981 !important;
        }
        .admin-action-jobs:hover {
            color: #fff;
            box-shadow: 0 0 15px rgba(16,185,129,0.5), inset 0 0 10px rgba(16,185,129,0.15) !important;
            border-color: #34d399 !important;
        }
        .admin-action-notifications {
            border-bottom: 2.5px solid var(--theme-red) !important;
        }
        .admin-action-notifications:hover {
            color: #fff;
            box-shadow: 0 0 15px rgba(248,113,113,0.5), inset 0 0 10px rgba(248,113,113,0.15) !important;
            border-color: #f87171 !important;
        }
        .admin-action-btn:hover .qa-icon {
            transform: scale(1.4) rotate(5deg) translateY(-2px);
            filter: brightness(1.25) drop-shadow(0 2px 6px rgba(255,255,255,0.4));
        }

        /* Two levels rows styling */
        .admin-actions-row {
            display: flex; gap: 15px; margin-bottom: 8px; align-items: center; 
            border: 1px solid rgba(255,255,255,0.05); background: rgba(15, 23, 42, 0.25); 
            padding: 6px 14px; border-radius: 14px; flex-wrap: wrap; 
            box-shadow: inset 0 0 15px rgba(255,255,255,0.01);
        }
        .operations-actions-row {
            display: flex; gap: 15px; align-items: center; 
            border: 1px solid rgba(255,255,255,0.03); background: rgba(15, 23, 42, 0.15); 
            padding: 6px 14px; border-radius: 14px; flex-wrap: wrap; 
            box-shadow: inset 0 0 10px rgba(255,255,255,0.01);
        }
        .actions-row-title {
            font-size: 0.82rem; font-weight: 800; color: #94a3b8; 
            display: flex; align-items: center; gap: 8px; min-width: 140px; 
            border-left: 2px solid rgba(255,255,255,0.15); padding-left: 10px; 
            margin-left: 5px;
        }

        @media (max-width: 768px) {
            .header-controls { justify-content: center; width: 100%; flex-wrap: wrap; }
            .charts-grid, .bottom-section { grid-template-columns: 1fr; }
        }
        
        /* Fix double sidebar spacing issue */
        body.has-sidebar { padding-right: 0 !important; }

        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOut { from { opacity:1; transform:translateY(0) scale(1); } to { opacity:0; transform:translateY(-20px) scale(0.95); } }

        /* Ultra-Premium Glassmorphic Screen Dropdown & Animated Micro-Interactions */
        #screens-dropdown-menu {
            transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1), transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* 💎 Ultra-Premium Master Quick Action Buttons (+4% Height: 54px, Luminous Edge & Under-Glow) */
        .qa-master-btn {
            position: relative;
            overflow: hidden;
            height: 54px !important;
            min-height: 54px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 10px !important;
            padding: 10px 18px !important;
            border-radius: 14px !important;
            font-size: 0.92rem !important;
            font-weight: 900 !important;
            color: #ffffff !important;
            background: rgba(15, 23, 42, 0.78) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.16) !important;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.35), inset 0 1.5px 1px rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(14px);
            transition: transform 0.22s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.25s ease, border-color 0.25s ease, background 0.25s ease !important;
            cursor: pointer !important;
            white-space: nowrap !important;
            text-overflow: ellipsis !important;
            text-decoration: none !important;
            box-sizing: border-box !important;
            flex: 0 0 auto !important;
        }

        /* Animated Light Wave Shimmer Effect */
        .qa-master-btn::before {
            content: '';
            position: absolute;
            top: 0; left: -150%;
            width: 70%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.35), transparent);
            transform: skewX(-25deg);
            pointer-events: none;
            transition: none;
        }

        .qa-master-btn:hover::before {
            left: 170%;
            transition: all 0.7s ease-in-out;
        }

        /* Subtle Border Illumination + Colored Light Shadow Directly Underneath Button on Hover */
        .qa-master-btn:hover,
        #qa-bar-container .qa-ribbon-btn:hover,
        #category-hubs-grid .cat-hub-btn:hover {
            transform: translateY(-3px) scale(1.02) !important;
            background: radial-gradient(130% 130% at 50% 0%, var(--btn-glow-color, rgba(56, 189, 248, 0.38)) 0%, rgba(22, 34, 58, 0.96) 75%) !important;
            border-color: var(--btn-border-color, #38bdf8) !important;
            border-bottom: 3.5px solid var(--btn-border-color, #38bdf8) !important;
            box-shadow: 0 10px 20px -3px var(--btn-border-color, #38bdf8), 0 4px 12px var(--btn-glow-color, rgba(56, 189, 248, 0.55)), 0 0 8px var(--btn-border-color, rgba(56, 189, 248, 0.55)), inset 0 1.5px 2px rgba(255, 255, 255, 0.42) !important;
            z-index: 10;
        }

        /* Icon Micro-Animations & Glow */
        .qa-master-btn .qa-icon {
            width: 30px !important;
            height: 30px !important;
            min-width: 30px !important;
            font-size: 0.9rem !important;
            border-radius: 8px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex-shrink: 0 !important;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.35s ease !important;
        }

        .qa-master-btn:hover .qa-icon {
            transform: scale(1.35) rotate(8deg) !important;
            box-shadow: 0 0 16px var(--btn-glow-color, rgba(168, 85, 247, 0.8)) !important;
        }

        .qa-master-btn .btn-text {
            font-size: 0.92rem !important;
            font-weight: 900 !important;
            color: #ffffff !important;
            transition: text-shadow 0.3s ease, color 0.3s ease;
        }
        #qa-bar-container {
            display: grid !important;
            grid-template-columns: repeat(7, minmax(0, 1fr)) !important;
            gap: 12px !important;
            padding: 16px 18px !important;
            border-radius: 20px !important;
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.92), rgba(7, 11, 22, 0.96)) !important;
            border: 1.5px solid rgba(56, 189, 248, 0.25) !important;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.55), 0 0 28px rgba(56, 189, 248, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.15) !important;
            transition: border-color 0.28s ease, box-shadow 0.28s ease, background 0.28s ease !important;
        }
        @media (max-width: 1280px) {
            #qa-bar-container { grid-template-columns: repeat(4, minmax(0, 1fr)) !important; }
        }
        @media (max-width: 768px) {
            #qa-bar-container { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
        }
        #qa-bar-container .qa-ribbon-btn {
            width: 100% !important;
            min-width: 0 !important;
            height: 56px !important;
            min-height: 56px !important;
            padding: 8px 8px !important;
            gap: 6px !important;
            border-radius: 15px !important;
            justify-content: center !important;
            text-align: center !important;
            background: radial-gradient(120% 120% at 50% 10%, var(--btn-glow-color, rgba(56, 189, 248, 0.24)) 0%, rgba(15, 23, 42, 0.94) 72%) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.18) !important;
            border-bottom: 3.5px solid var(--btn-border-color, #38bdf8) !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.42), 0 3px 10px -2px var(--btn-glow-color, rgba(56, 189, 248, 0.25)), inset 0 1.5px 1px rgba(255, 255, 255, 0.28) !important;
        }
        #qa-bar-container .qa-ribbon-btn .qa-icon {
            display: none !important;
        }
        #qa-bar-container .qa-ribbon-btn .btn-text {
            font-size: clamp(0.78rem, 1.05vw, 0.96rem) !important;
            font-weight: 900 !important;
            letter-spacing: 0px;
            text-align: center !important;
            white-space: nowrap !important;
            overflow: visible !important;
            text-overflow: clip !important;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.65);
        }

        .qa-master-btn:hover .btn-text {
            color: #ffffff !important;
            text-shadow: 0 0 12px var(--btn-glow-color, rgba(255, 255, 255, 0.9)) !important;
        }

        #screens-dropdown-list {
            scroll-behavior: smooth !important;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
            padding-bottom: 30px !important;
        }
        #screens-dropdown-list::-webkit-scrollbar {
            width: 7px;
        }
        #screens-dropdown-list::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.7);
            border-radius: 10px;
        }
        #screens-dropdown-list::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #a855f7, #6366f1);
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        #screens-dropdown-list::-webkit-scrollbar-thumb:hover {
            background: #c084fc;
        }

        .scope-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #94a3b8;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.25s ease;
            font-family: 'Tajawal', sans-serif;
        }
        .scope-btn:hover {
            background: rgba(56, 189, 248, 0.15);
            border-color: rgba(56, 189, 248, 0.4);
            color: #38bdf8;
        }
        .scope-btn.active {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.3), rgba(99, 102, 241, 0.3));
            border-color: #38bdf8;
            color: #ffffff;
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.4);
        }

        .screen-popover-item {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 12px 16px !important;
            min-height: 52px !important;
            border-radius: 14px !important;
            background: rgba(15, 23, 42, 0.88) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.14) !important;
            text-decoration: none !important;
            transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1) !important;
            cursor: pointer;
            box-sizing: border-box;
        }

        /* Glass Shimmer Overlay Sweep */
        .screen-popover-item::after {
            content: '';
            position: absolute;
            top: 0; left: -150%;
            width: 80%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
            transform: skewX(-20deg);
            transition: none;
            pointer-events: none;
        }

        .screen-popover-item:hover::after {
            left: 150%;
            transition: all 0.6s ease-in-out;
        }

        /* Hover Motion & Dynamic Glow */
        .screen-popover-item:hover {
            transform: translateX(-10px) translateY(-2px) scale(1.03) !important;
            background: rgba(30, 41, 59, 0.98) !important;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.7), 0 0 25px var(--item-glow-color, rgba(168, 85, 247, 0.6)), 0 0 40px var(--item-glow-color, rgba(168, 85, 247, 0.3)) !important;
            border-color: var(--item-border-color, #c084fc) !important;
            z-index: 5;
        }

        .screen-popover-item .qa-icon {
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.35s ease !important;
        }

        .screen-popover-item:hover .qa-icon {
            transform: scale(1.4) rotate(8deg) !important;
            box-shadow: 0 0 20px var(--item-glow-color, rgba(168, 85, 247, 0.8)), 0 0 35px var(--item-glow-color, rgba(168, 85, 247, 0.5)) !important;
        }

        .screen-popover-item .screen-title {
            font-size: 1.0rem !important; /* Increased font by 1 step */
            font-weight: 900 !important;
            letter-spacing: 0.4px;
            color: #ffffff !important;
            transition: color 0.3s ease, text-shadow 0.3s ease;
        }

        .screen-popover-item:hover .screen-title {
            color: #ffffff !important;
            text-shadow: 0 0 12px var(--item-glow-color, rgba(255, 255, 255, 0.9)) !important;
        }

        .screen-popover-item .screen-arrow {
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

                /* Theme-Adaptive Dropdown Menus (Beige, White, Gray, Dark) */
        html[data-theme] #category-dropdown-popover,
        html[data-theme] #screens-dropdown-menu {
            background: var(--bg-color, #070a12) !important;
            color: var(--text-primary, #ffffff) !important;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25) !important;
        }
        html[data-theme] .screen-popover-item {
            background: var(--surface, rgba(15, 23, 42, 0.95)) !important;
            color: var(--text-primary, #ffffff) !important;
        }
        html[data-theme] .screen-popover-item:hover {
            background: var(--surface-hover, rgba(30, 41, 59, 0.98)) !important;
        }
        html[data-theme] .screen-popover-item .screen-title,
        html[data-theme] .screen-popover-item:hover .screen-title {
            color: var(--text-primary, #ffffff) !important;
            text-shadow: none !important;
        }
        .screen-popover-item:hover .screen-arrow {
            transform: translateX(-6px) scale(1.3);
            opacity: 1 !important;
        }
    </style>
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
            transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1) !important;
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
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        <div id="market-broker-restore-pill" style="display:none;align-self:flex-start;">
            <button type="button" onclick="toggleMarketBrokerTicker(true)" style="padding:3px 12px;border-radius:999px;background:linear-gradient(135deg,#059669,#047857);color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #fde047;font-size:12px;font-weight:900;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,0.4);">📢 إظهار شريطي البورصة (العروض والطلبات)</button>
        </div>
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
                .broker-new-item-pill { animation: brkNewPulse 1.8s infinite ease-in-out; background: linear-gradient(135deg, rgba(6,95,70,0.96), rgba(15,23,42,0.96)) !important; border-width: 2px !important; }
                @keyframes execPrismGlow { 0%, 100% { box-shadow: 0 14px 34px rgba(0,0,0,0.75), 0 0 24px rgba(251,191,36,0.48), inset 0 1.5px 3px rgba(254,240,138,0.55); border-color: #fde047; } 50% { box-shadow: 0 16px 40px rgba(0,0,0,0.82), 0 0 32px rgba(245,158,11,0.68), inset 0 2px 4px rgba(255,255,255,0.6); border-color: #fef08a; } }
                #exec-sticky-command-bar, html.light-theme #exec-sticky-command-bar { position: sticky; top: 8px; z-index: 99990; margin-top: 0 !important; margin-bottom: 0 !important; padding: 10px 16px; border-radius: 20px; border: 2.5px solid #fde047 !important; background: linear-gradient(135deg, #78350f 0%, #b45309 28%, #d97706 50%, #b45309 72%, #78350f 100%) !important; backdrop-filter: blur(24px) saturate(190%); -webkit-backdrop-filter: blur(24px) saturate(190%); animation: execPrismGlow 4.5s infinite ease-in-out; display: flex !important; align-items: center !important; justify-content: space-between !important; gap: 8px; flex-wrap: nowrap; overflow-x: auto; direction: rtl !important; box-sizing: border-box !important; transition: box-shadow 0.25s ease; }
                #exec-sticky-command-bar > div { display: flex !important; align-items: center !important; flex-shrink: 0 !important; gap: 6px !important; }
                #exec-sticky-command-bar.is-pinned-fixed, html.light-theme #exec-sticky-command-bar.is-pinned-fixed { position: fixed !important; top: 8px !important; margin-top: 0 !important; margin-bottom: 0 !important; z-index: 2147483500 !important; background: linear-gradient(135deg, #78350f 0%, #b45309 28%, #d97706 50%, #b45309 72%, #78350f 100%) !important; border: 2.5px solid #fde047 !important; }
                #exec-sticky-command-bar .exec-period-pill { position: relative; overflow: hidden; height: 40px; padding: 0 14px; border-radius: 13px; font-size: 14.5px !important; font-weight: 900 !important; display: inline-flex; align-items: center; justify-content: center; white-space: nowrap; cursor: pointer; transition: all 0.26s cubic-bezier(0.34, 1.56, 0.64, 1); border: 1.5px solid rgba(253,224,71,0.55); background: linear-gradient(180deg, rgba(15,23,42,0.92) 0%, rgba(2,6,23,0.96) 100%); color: #fef08a !important; -webkit-text-fill-color: #fef08a !important; box-shadow: 0 5px 14px rgba(0,0,0,0.45), inset 0 1px 1px rgba(255,255,255,0.22); }
                #exec-sticky-command-bar .exec-period-pill::after, #exec-sticky-command-bar .exec-royal-action::after { content: ''; position: absolute; inset: 0; background: linear-gradient(120deg, transparent 25%, rgba(255,255,255,0.28) 50%, transparent 75%); transform: translateX(-120%); transition: transform 0.55s ease; pointer-events: none; }
                #exec-sticky-command-bar .exec-period-pill:hover { transform: translateY(-3px) scale(1.06); border-color: #38bdf8; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; box-shadow: 0 8px 20px rgba(56,189,248,0.45), 0 0 14px rgba(56,189,248,0.5); }
                #exec-sticky-command-bar .exec-period-pill:hover::after, #exec-sticky-command-bar .exec-royal-action:hover::after { transform: translateX(120%); }
                #exec-sticky-command-bar .exec-period-pill.active-period { background: linear-gradient(135deg, #059669 0%, #0284c7 100%) !important; border: 2px solid #fde047 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; box-shadow: 0 8px 22px rgba(16,185,129,0.55), 0 0 18px rgba(253,224,71,0.5), inset 0 1px 2px rgba(255,255,255,0.45) !important; transform: translateY(-2px) scale(1.05); }
                #exec-sticky-command-bar .exec-royal-action { position: relative; overflow: hidden; height: 42px; padding: 0 15px !important; border-radius: 14px !important; font-size: 14.5px !important; font-weight: 900 !important; display: inline-flex; align-items: center; justify-content: center; gap: 7px; white-space: nowrap; cursor: pointer; transition: all 0.26s cubic-bezier(0.34, 1.56, 0.64, 1) !important; box-shadow: 0 8px 20px rgba(0,0,0,0.5), inset 0 1px 2px rgba(255,255,255,0.32) !important; }
                #exec-sticky-command-bar .exec-royal-action:hover { transform: translateY(-3px) scale(1.055) !important; filter: brightness(1.18) saturate(1.22); box-shadow: 0 12px 26px rgba(0,0,0,0.65), 0 0 20px rgba(56,189,248,0.6) !important; }
                #dash-today-harvest-strip .exec-jewel-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; }
                @media (max-width: 900px) { #dash-today-harvest-strip .exec-jewel-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
                #dash-today-harvest-strip .exec-jewel-btn, html.light-theme #dash-today-harvest-strip .exec-jewel-btn { position: relative; overflow: hidden; min-height: 64px; padding: 10px 16px !important; border-radius: 16px !important; display: flex; align-items: center; justify-content: space-between !important; gap: 8px; transition: all 0.26s cubic-bezier(0.34, 1.56, 0.64, 1) !important; box-shadow: 0 8px 22px rgba(0,0,0,0.48), inset 0 1px 1px rgba(255,255,255,0.22) !important; }
                #dash-today-harvest-strip .exec-jewel-btn::after { content: ''; position: absolute; inset: 0; background: linear-gradient(120deg, transparent 25%, rgba(255,255,255,0.22) 50%, transparent 75%); transform: translateX(-120%); transition: transform 0.55s ease; pointer-events: none; }
                #dash-today-harvest-strip .exec-jewel-btn:hover { transform: translateY(-4px) scale(1.035) !important; filter: brightness(1.18) saturate(1.2); z-index: 5; box-shadow: 0 14px 30px rgba(0,0,0,0.68), 0 0 24px var(--jewel-glow, rgba(56,189,248,0.7)), inset 0 0 14px rgba(255,255,255,0.28) !important; }
                #dash-today-harvest-strip .exec-jewel-btn:hover::after { transform: translateX(120%); }
                #dash-today-harvest-strip .exec-jewel-lbl, html.light-theme #dash-today-harvest-strip .exec-jewel-lbl { font-size: 15.5px !important; font-weight: 900 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; white-space: nowrap; letter-spacing: -0.2px; padding-right: 4px; }
                #dash-today-harvest-strip .exec-jewel-lbl-ap, html.light-theme #dash-today-harvest-strip .exec-jewel-lbl-ap { font-size: 13.2px !important; font-weight: 900 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; white-space: nowrap; letter-spacing: -0.3px; padding-right: 2px; }
                #dash-today-harvest-strip .exec-jewel-val { font-size: 18px !important; font-weight: 900 !important; white-space: nowrap; text-shadow: 0 2px 8px rgba(0,0,0,0.55); padding: 4px 11px; border-radius: 10px; background: rgba(2, 6, 23, 0.62); border: 1px solid rgba(255, 255, 255, 0.16); display: inline-flex; align-items: baseline; gap: 3px; }
                #dash-today-harvest-strip .exec-jewel-val .exec-unit-tag { font-size: 10.5px !important; font-weight: 800 !important; opacity: 0.9; letter-spacing: 0; }
                @keyframes mktBrokerMarquee { 0% { transform: translateX(0); } 100% { transform: translateX(50%); } }
                #market-broker-ticker-wrap, html.light-theme #market-broker-ticker-wrap { position: relative; background: #020617 !important; border: none !important; border-radius: 7px !important; padding: 0 !important; margin: 0 !important; display: flex !important; flex-direction: column !important; gap: 6px !important; overflow: hidden; box-shadow: 0 4px 14px rgba(2,6,23,0.65) !important; direction: rtl; }
                #market-broker-ticker-wrap > .mkt-broker-row { display: flex !important; align-items: center !important; gap: 6px !important; overflow: hidden; width: 100% !important; height: 36px !important; min-height: 36px !important; max-height: 36px !important; padding: 0 8px !important; margin: 0 !important; border-radius: 6px; line-height: 1.2 !important; box-sizing: border-box !important; }
                #market-broker-offers-row, html.light-theme #market-broker-offers-row, html.light-theme[data-light-tone] #market-broker-offers-row { background: #0c1d47 !important; border: 1px solid rgba(253,224,71,0.55) !important; box-shadow: 0 2px 6px rgba(15,23,42,0.35) !important; }
                #market-broker-orders-row, html.light-theme #market-broker-orders-row, html.light-theme[data-light-tone] #market-broker-orders-row { background: #0a2536 !important; border: 1px solid rgba(134,239,172,0.5) !important; box-shadow: 0 2px 6px rgba(15,23,42,0.35) !important; }
                html.light-theme[data-light-tone="rose_blossom"] body, html.light-theme[data-light-tone="rose_blossom"] .dash-main { background: linear-gradient(180deg, #fdf2f8 0%, #fce7f3 50%, #fdf2f8 100%) !important; color: #000000 !important; }
                html.light-theme[data-light-tone="royal_bronze"] body, html.light-theme[data-light-tone="royal_bronze"] .dash-main { background: linear-gradient(180deg, #fffbeb 0%, #fef3c7 50%, #fffbeb 100%) !important; color: #000000 !important; }
                html.light-theme[data-light-tone="crystal_silver"] body, html.light-theme[data-light-tone="crystal_silver"] .dash-main, html.light-theme[data-light-tone="platinum_gray"] body, html.light-theme[data-light-tone="platinum_gray"] .dash-main { background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 50%, #f8fafc 100%) !important; color: #000000 !important; }
                html.light-theme[data-light-tone="sage_pearl"] body, html.light-theme[data-light-tone="sage_pearl"] .dash-main { background: linear-gradient(180deg, #f0fdf4 0%, #dcfce7 50%, #f0fdf4 100%) !important; color: #000000 !important; }
                #market-broker-ticker-wrap .brk-pill-hdr-off, html.light-theme #market-broker-ticker-wrap .brk-pill-hdr-off { color: #fde047 !important; -webkit-text-fill-color: #fde047 !important; font-weight: 800 !important; }
                #market-broker-ticker-wrap .brk-off-txt-0, html.light-theme #market-broker-ticker-wrap .brk-off-txt-0 { color: #7dd3fc !important; -webkit-text-fill-color: #7dd3fc !important; }
                #market-broker-ticker-wrap .brk-off-txt-1, html.light-theme #market-broker-ticker-wrap .brk-off-txt-1 { color: #fde047 !important; -webkit-text-fill-color: #fde047 !important; }
                #market-broker-ticker-wrap .brk-off-txt-2, html.light-theme #market-broker-ticker-wrap .brk-off-txt-2 { color: #7dd3fc !important; -webkit-text-fill-color: #7dd3fc !important; }
                #market-broker-ticker-wrap .brk-off-time, html.light-theme #market-broker-ticker-wrap .brk-off-time { color: #99f6e4 !important; -webkit-text-fill-color: #99f6e4 !important; }
                #market-broker-ticker-wrap .brk-pill-hdr-ord, html.light-theme #market-broker-ticker-wrap .brk-pill-hdr-ord { color: #86efac !important; -webkit-text-fill-color: #86efac !important; font-weight: 800 !important; }
                #market-broker-ticker-wrap .brk-ord-txt-0, html.light-theme #market-broker-ticker-wrap .brk-ord-txt-0 { color: #f8fafc !important; -webkit-text-fill-color: #f8fafc !important; }
                #market-broker-ticker-wrap .brk-ord-txt-1, html.light-theme #market-broker-ticker-wrap .brk-ord-txt-1 { color: #a7f3d0 !important; -webkit-text-fill-color: #a7f3d0 !important; }
                #market-broker-ticker-wrap .brk-ord-txt-2, html.light-theme #market-broker-ticker-wrap .brk-ord-txt-2 { color: #f8fafc !important; -webkit-text-fill-color: #f8fafc !important; }
                #market-broker-ticker-wrap .brk-ord-time, html.light-theme #market-broker-ticker-wrap .brk-ord-time { color: #bbf7d0 !important; -webkit-text-fill-color: #bbf7d0 !important; }
                #market-broker-ticker-track { display: flex; width: max-content; align-items: center; gap: 22px; white-space: nowrap; will-change: transform; animation: none !important; }
                #market-broker-orders-track { display: flex; width: max-content; align-items: center; gap: 22px; white-space: nowrap; will-change: transform; animation: none !important; }
            </style>
            <div id="exec-sticky-command-placeholder" style="display:none;height:62px;"></div>
            <div id="exec-sticky-command-bar">
                <div id="exec-summary-period-pills" class="flex items-center gap-1.5 shrink-0">
                    <button type="button" onclick="cycleExecutiveSummaryPeriod(0)" class="exec-period-pill active-period">اليوم</button>
                    <button type="button" onclick="cycleExecutiveSummaryPeriod(1)" class="exec-period-pill">أسبوع</button>
                    <button type="button" onclick="cycleExecutiveSummaryPeriod(2)" class="exec-period-pill">10 أيام</button>
                    <button type="button" onclick="cycleExecutiveSummaryPeriod(3)" class="exec-period-pill">شهر</button>
                    <button type="button" onclick="cycleExecutiveSummaryPeriod(4)" class="exec-period-pill">3 شهور</button>
                    <button type="button" onclick="cycleExecutiveSummaryPeriod(5)" class="exec-period-pill">6 شهور</button>
                    <button type="button" onclick="cycleExecutiveSummaryPeriod(6)" class="exec-period-pill">9 شهور</button>
                    <button type="button" onclick="cycleExecutiveSummaryPeriod(7)" class="exec-period-pill">سنة</button>
                </div>
                <div class="flex items-center gap-2 shrink-0" style="margin-right: auto;">
                    <button onclick="toggleCurrency()" id="currency-btn" class="exec-royal-action" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; border: 2px solid #fef08a !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; box-shadow: 0 6px 18px rgba(16,185,129,0.55), inset 0 1px 2px rgba(255,255,255,0.4) !important;" title="اضغط للتحويل الفوري بين الجنيه والدولار">
                        <span>🪙</span><span id="currency-btn-txt">جنيه ⇄</span>
                    </button>
                    <button type="button" class="exec-royal-action num-font" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border: 1.5px solid #38bdf8; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;" title="سعر الصرف المعتمد">
                        <span>💱 الصرف:</span>
                        <strong id="hdr-usd" style="color: #fde047 !important; -webkit-text-fill-color: #fde047 !important; font-size: 16.5px !important; font-weight: 900;">600</strong>
                    </button>
                    <button onclick="startLiveDashVoiceAdvisor()" class="exec-royal-action" style="background: linear-gradient(135deg, #0284c7 0%, #1e40af 100%); border: 1.5px solid #7dd3fc; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;" title="حاور البروفيسور الذكي بالصوت أو النص لمناقشة المؤشرات والقرارات">
                        <span>🎓</span><span>حاور البروفيسور</span>
                    </button>
                    <button onclick="speakExecutiveDashboardBriefing()" class="exec-royal-action" style="background: linear-gradient(135deg, #0d9488 0%, #065f46 100%); border: 1.5px solid #6ee7b7; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;" title="قراءة صوتية ذكية فورية لأهم مؤشرات الشاشة">
                        <span>🔊</span><span>إيجاز ذكي</span>
                    </button>
                    <span id="hdr-rebar" style="display:none;">420,000</span>
                    <span id="hdr-rebar-unit" style="display:none;">ج.س</span>
                </div>
            </div>
            <div id="dash-today-harvest-strip" class="bento-card" style="padding: 14px 18px; border-radius: 22px; border: 1.5px solid rgba(56,189,248,0.55); background: linear-gradient(180deg, rgba(15,23,42,0.98) 0%, rgba(7,12,24,0.99) 100%); box-shadow: 0 18px 42px rgba(0,0,0,0.68), inset 0 1px 2px rgba(255,255,255,0.22);">
                <div class="exec-jewel-grid num-font">
                    <button type="button" onclick="switchSalesProfitCardView('day')" class="inner-box exec-jewel-btn text-right cursor-pointer" style="--jewel-glow: rgba(56,189,248,0.78); border: 1.5px solid rgba(56,189,248,0.5) !important; border-bottom: 3.5px solid #38bdf8 !important; background: linear-gradient(180deg, rgba(14,165,233,0.22), rgba(15,23,42,0.95)) !important;">
                        <span class="exec-jewel-lbl">المبيعات:</span>
                        <strong class="exec-jewel-val text-amber-300" style="color:#fde047!important;-webkit-text-fill-color:#fde047!important;" id="strip-sal-amt" data-sdg="1890000">1,890,000 <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                    <button type="button" onclick="switchSalesProfitCardView('purchases', document.getElementById('btn-card2-purchases'))" class="inner-box exec-jewel-btn text-right cursor-pointer" style="--jewel-glow: rgba(245,158,11,0.75); border: 1.5px solid rgba(251,191,36,0.5) !important; border-bottom: 3.5px solid #f59e0b !important; background: linear-gradient(180deg, rgba(251,191,36,0.20), rgba(15,23,42,0.95)) !important;">
                        <span class="exec-jewel-lbl">المشتريات:</span>
                        <strong class="exec-jewel-val text-amber-300" style="color:#fbbf24!important;-webkit-text-fill-color:#fbbf24!important;" id="strip-pur-amt" data-sdg="1420000">1,420,000 <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                    <button type="button" onclick="switchLiquidityCardView('overview')" class="inner-box exec-jewel-btn text-right cursor-pointer" style="--jewel-glow: rgba(52,211,153,0.75); border: 1.5px solid rgba(52,211,153,0.5) !important; border-bottom: 3.5px solid #34d399 !important; background: linear-gradient(180deg, rgba(52,211,153,0.20), rgba(15,23,42,0.95)) !important;">
                        <span class="exec-jewel-lbl">السيولة:</span>
                        <strong class="exec-jewel-val text-emerald-300" style="color:#6ee7b7!important;-webkit-text-fill-color:#6ee7b7!important;" id="strip-liq-amt" data-sdg="3450000">3,450,000 <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                    <button type="button" onclick="switchSalesProfitCardView('day')" class="inner-box exec-jewel-btn text-right cursor-pointer" id="strip-prf-card" style="--jewel-glow: rgba(16,185,129,0.8); border: 1.5px solid rgba(16,185,129,0.55) !important; border-bottom: 3.5px solid #10b981 !important; background: linear-gradient(180deg, rgba(16,185,129,0.22), rgba(15,23,42,0.95)) !important;">
                        <span class="exec-jewel-lbl" id="strip-prf-lbl">الأرباح:</span>
                        <strong class="exec-jewel-val text-emerald-400" style="color:#4ade80!important;-webkit-text-fill-color:#4ade80!important;" id="strip-prf-amt" data-sdg="348000">+348,000 <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                    <button type="button" onclick="switchCashflowCardView('ai_cf')" class="inner-box exec-jewel-btn text-right cursor-pointer" style="--jewel-glow: rgba(244,63,94,0.75); border: 1.5px solid rgba(244,63,94,0.5) !important; border-bottom: 3.5px solid #f43f5e !important; background: linear-gradient(180deg, rgba(244,63,94,0.20), rgba(15,23,42,0.95)) !important;">
                        <span class="exec-jewel-lbl" id="strip-exp-lbl">المصروفات:</span>
                        <strong class="exec-jewel-val text-rose-300" style="color:#fda4af!important;-webkit-text-fill-color:#fda4af!important;" id="strip-exp-amt" data-sdg="42000">42,000 <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                    <button type="button" onclick="switchSalesProfitCardView('warehouses')" title="القيمة السوقية الإجمالية = الكاش + البنوك + الشيكات + قيمة البضاعة بسعر السوق اليوم" class="inner-box exec-jewel-btn text-right cursor-pointer" style="--jewel-glow: rgba(14,165,233,0.8); border: 1.5px solid rgba(14,165,233,0.5) !important; border-bottom: 3.5px solid #0ea5e9 !important; background: linear-gradient(180deg, rgba(14,165,233,0.20), rgba(15,23,42,0.95)) !important;">
                        <span class="exec-jewel-lbl">القيمة السوقية:</span>
                        <strong class="exec-jewel-val text-sky-300" style="color:#7dd3fc!important;-webkit-text-fill-color:#7dd3fc!important;" id="stock-mkt-val" data-sdg="184300000">184.3M <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                    <button type="button" onclick="switchLiveRadarFeed('vouchers')" class="inner-box exec-jewel-btn text-right cursor-pointer" style="--jewel-glow: rgba(20,184,166,0.75); border: 1.5px solid rgba(20,184,166,0.5) !important; border-bottom: 3.5px solid #14b8a6 !important; background: linear-gradient(180deg, rgba(20,184,166,0.20), rgba(15,23,42,0.95)) !important;">
                        <span class="exec-jewel-lbl">المقبوضات:</span>
                        <strong class="exec-jewel-val text-emerald-300" style="color:#5eead4!important;-webkit-text-fill-color:#5eead4!important;" id="strip-in-amt" data-sdg="1320000">+1,320,000 <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                    <button type="button" onclick="switchLiveRadarFeed('vouchers')" class="inner-box exec-jewel-btn text-right cursor-pointer" style="--jewel-glow: rgba(249,115,22,0.75); border: 1.5px solid rgba(249,115,22,0.5) !important; border-bottom: 3.5px solid #f97316 !important; background: linear-gradient(180deg, rgba(249,115,22,0.20), rgba(15,23,42,0.95)) !important;">
                        <span class="exec-jewel-lbl">المدفوعات:</span>
                        <strong class="exec-jewel-val text-rose-400" style="color:#fb7185!important;-webkit-text-fill-color:#fb7185!important;" id="strip-out-amt" data-sdg="368000">-368,000 <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                    <button type="button" onclick="openFullCardModal('debt_aging')" title="مجموع المبالغ التي يريدها منا الموردون كلهم" class="inner-box exec-jewel-btn text-right cursor-pointer" style="--jewel-glow: rgba(168,85,247,0.75); border: 1.5px solid rgba(192,132,252,0.5) !important; border-bottom: 3.5px solid #a855f7 !important; background: linear-gradient(180deg, rgba(168,85,247,0.20), rgba(15,23,42,0.95)) !important;">
                        <span class="exec-jewel-lbl-ap">مستحقات الموردين:</span>
                        <strong class="exec-jewel-val text-amber-300" style="color:#fde047!important;-webkit-text-fill-color:#fde047!important;" id="strip-ap-amt" data-sdg="290000">290,000 <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                    <button type="button" onclick="openFullCardModal('debt_aging')" title="مجموع المبالغ التي نريدها من العملاء كلهم" class="inner-box exec-jewel-btn text-right cursor-pointer" style="--jewel-glow: rgba(52,211,153,0.75); border: 1.5px solid rgba(52,211,153,0.5) !important; border-bottom: 3.5px solid #34d399 !important; background: linear-gradient(180deg, rgba(52,211,153,0.20), rgba(15,23,42,0.95)) !important;">
                        <span class="exec-jewel-lbl">ديون العملاء:</span>
                        <strong class="exec-jewel-val text-emerald-300" style="color:#4ade80!important;-webkit-text-fill-color:#4ade80!important;" id="strip-ar-amt" data-sdg="820000">820,000 <small class="exec-unit-tag">ج.س</small></strong>
                    </button>
                </div>
            </div>

            <!-- 3. الصف الأول (توزيع بانورامي مريح مقسوم على بطاقتين عريضتين 6 + 6 لكسر التماثل الممل) -->
            <section id="sec-calm" class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                <!-- البطاقة اليمنى العريضة (6 أعمدة): صافي السيولة النقدية والبنكية المتاحة الآن -->
                <div class="lg:col-span-6 bento-card p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                <button type="button" onclick="openFullCardModal('vaults')" title="عرض كشف جميع الخزن والحسابات البنكية بالكامل" class="w-7 h-7 rounded-lg bg-emerald-500/25 hover:bg-emerald-500/45 border-2 border-emerald-400 text-white font-black text-lg flex items-center justify-center cursor-pointer shrink-0 shadow">+</button>
                                <button type="button" onclick="openChartCardModal('vaults')" title="عرض الرسوم البيانية الشاملة لكل الخزن والبنوك والسيولة" class="w-7 h-7 rounded-lg bg-amber-500/25 hover:bg-amber-500/45 border-2 border-amber-400 text-amber-300 font-black text-sm flex items-center justify-center cursor-pointer shrink-0 shadow">📊</button>
                                <span class="font-black text-emerald-300 whitespace-nowrap flex-1" id="dash-liq-card-title" style="font-size:19px!important;line-height:1.2;">صافي السيولة النقدية والبنكية المتاحة</span>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <button type="button" id="dash-liq-period-btn" onclick="toggleLiquidityPeriod()" class="px-2 py-0.5 rounded-lg bg-slate-900 border border-emerald-400/50 text-[11px] font-black cursor-pointer" style="color:#fde68a!important;-webkit-text-fill-color:#fde68a!important;">📅 اليوم ⇄</button>
                                <span title="نسبة نمو السيولة النقدية والبنكية مقارنة بالفترة السابقة" class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-500/20 text-emerald-300 shrink-0" id="dash-liq-card-badge">نمو +12.4% ▲</span>
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
                    <div class="grid grid-cols-1 gap-2.5 mt-3 pt-3 border-t border-white/10">
                        <button type="button" onclick="window.location.href='direct-sales.php'" class="py-2.5 px-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-xs transition cursor-pointer">🛒 فتح شاشة المبيعات الكاملة</button>
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
            <!-- 6. ستارة الأرصدة والسمات المنزلقة -->
            <aside id="curtain-drawer" class="fixed top-0 left-0 h-full w-96 bento-card z-50 p-6 transform -translate-x-full transition-transform duration-300 overflow-y-auto rounded-none border-r-2 border-emerald-400">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-white/15">
                    <h3 class="font-extrabold text-base text-white">🎛️ ستارة الأرصدة الافتتاحية</h3>
                    <button onclick="toggleCurtain()" class="px-3 py-1 rounded-xl bg-rose-500/20 text-rose-300 text-xs font-extrabold">✕ إغلاق</button>
                </div>
                <div class="space-y-4 text-xs num-font">
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="font-bold text-slate-300 block mb-1">رصيد السيولة (الخزينة/البنك)</label><input id="cur-liq" type="number" value="3450000" oninput="updateFromCurtain()" class="w-full inner-box p-2.5 text-white font-black text-sm"></div>
                        <div><label class="font-bold text-slate-300 block mb-1">سعر صرف الدولار ($)</label><input id="cur-usd" type="number" value="600" oninput="updateFromCurtain()" class="w-full inner-box p-2.5 text-amber-400 font-black text-sm"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="font-bold text-slate-300 block mb-1">لنا بالسوق (المدينون)</label><input id="cur-ar" type="number" value="820000" oninput="updateFromCurtain()" class="w-full inner-box p-2.5 text-sky-400 font-black text-sm"></div>
                        <div><label class="font-bold text-slate-300 block mb-1">علينا للموردين (الدائنون)</label><input id="cur-ap" type="number" value="290000" oninput="updateFromCurtain()" class="w-full inner-box p-2.5 text-rose-400 font-black text-sm"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="font-bold text-slate-300 block mb-1">إجمالي مخزون السيخ (بالطن)</label><input id="cur-tons" type="number" step="0.5" value="420.5" oninput="updateFromCurtain()" class="w-full inner-box p-2.5 text-amber-300 font-black text-sm"></div>
                        <div><label class="font-bold text-slate-300 block mb-1">سعر طن السيخ اليوم (ج.س)</label><input id="cur-rebar" type="number" value="420000" oninput="updateFromCurtain()" class="w-full inner-box p-2.5 text-emerald-400 font-black text-sm"></div>
                    </div>
                    <button onclick="resetDemoRecords()" class="w-full mt-4 py-3 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 border border-rose-500/40 text-rose-200 font-extrabold text-xs">🧹 تصفير سجل الفواتير التجريبية والبدء بسجل نظيف</button>
                </div>
            </aside>

            <!-- 7. نافذة طباعة الفاتورة الفورية -->
            <div id="print-modal" class="fixed inset-0 bg-black/75 z-50 hidden items-center justify-center p-4">
                <div class="bg-white text-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl num-font">
                    <div class="text-center border-b-2 border-slate-200 pb-3 mb-4">
                        <h2 class="text-xl font-black text-emerald-800">نظام مقاييس ERP - السجانة</h2>
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
            <script>
                let displayUSD = false;

                window.addEventListener('DOMContentLoaded', () => {
                    const savedState = localStorage.getItem('magaiees_state_v1');
                    if (savedState) {
                        try {
                            const st = JSON.parse(savedState);
                            if (st.liq !== undefined) document.getElementById('cur-liq').value = st.liq;
                            if (st.usd !== undefined) document.getElementById('cur-usd').value = st.usd;
                            if (st.ar !== undefined) document.getElementById('cur-ar').value = st.ar;
                            if (st.ap !== undefined) document.getElementById('cur-ap').value = st.ap;
                            if (st.tons !== undefined) document.getElementById('cur-tons').value = st.tons;
                            if (st.rebar !== undefined) document.getElementById('cur-rebar').value = st.rebar;
                        } catch (e) {}
                    }
                    updateFromCurtain();
                });

                function setTheme(themeName) {
                    document.documentElement.setAttribute('data-theme', themeName);
                    document.documentElement.classList.remove('light-theme');
                    document.documentElement.classList.add('dark-theme');
                    if (document.body) {
                        document.body.setAttribute('data-theme', themeName);
                        document.body.classList.remove('light-theme');
                        document.body.classList.add('dark-theme');
                    }
                    localStorage.setItem('magaiees_theme', themeName);
                }

                function toggleCurtain() {
                    document.getElementById('curtain-drawer').classList.toggle('-translate-x-full');
                }

                function calcSajanaWeights() {
                    const tons = parseFloat(document.getElementById('sajana-tons').value) || 0;
                    const rebarPrice = parseFloat(document.getElementById('cur-rebar').value) || 420000;
                    document.getElementById('sajana-amount').value = Math.round(tons * rebarPrice).toLocaleString();
                    document.getElementById('res-comm').innerText = Math.round(tons * 135) + ' سيخة';
                    document.getElementById('res-weight').innerText = Math.round(tons * 140) + ' سيخة';
                    document.getElementById('res-bundles').innerText = ((tons * 135) / 15).toFixed(1) + ' شيلة';
                }

                function updateFromCurtain() {
                    const liq = parseFloat(document.getElementById('cur-liq').value) || 0;
                    const usd = parseFloat(document.getElementById('cur-usd').value) || 600;
                    const ar = parseFloat(document.getElementById('cur-ar').value) || 0;
                    const ap = parseFloat(document.getElementById('cur-ap').value) || 0;
                    const tons = parseFloat(document.getElementById('cur-tons').value) || 0;
                    const rebar = parseFloat(document.getElementById('cur-rebar').value) || 420000;

                    localStorage.setItem('magaiees_state_v1', JSON.stringify({ liq, usd, ar, ap, tons, rebar }));

                    const factor = displayUSD ? usd : 1;
                    const unit = displayUSD ? '$' : 'ج.س';
                    window._fmtDualHero = function(sdgVal, unitColor = 'text-emerald-400') {
                        const s = Math.round(sdgVal).toLocaleString();
                        const u = Math.round(sdgVal / (usd || 600)).toLocaleString();
                        return displayUSD
                            ? `$${u} <span class="text-base font-bold ${unitColor}">دولار</span> <span style="font-size:15px;font-weight:900;color:#fde68a;">(≈ ${s} ج.س)</span>`
                            : `${s} <span class="text-base font-bold ${unitColor}">ج.س</span> <span style="font-size:15px;font-weight:900;color:#fde68a;">(≈ $${u} دولار)</span>`;
                    };
                    window._fmtDualSub = function(sdgVal, prefix = '', unitColor = 'text-sky-400') {
                        const s = Math.round(sdgVal).toLocaleString();
                        const u = Math.round(sdgVal / (usd || 600)).toLocaleString();
                        return displayUSD
                            ? `${prefix}$${u} <span class="text-xs font-bold ${unitColor}">$</span> <small style="font-size:11px;font-weight:800;color:#fde68a;">(${s} ج.س)</small>`
                            : `${prefix}${s} <span class="text-xs font-bold ${unitColor}">ج.س</span> <small style="font-size:11px;font-weight:800;color:#fde68a;">($${u})</small>`;
                    };

                    document.getElementById('hdr-usd').innerText = usd.toLocaleString();
                    document.getElementById('hdr-rebar').innerText = Math.round(rebar / factor).toLocaleString();
                    document.getElementById('main-liquidity').innerHTML = window._fmtDualHero(liq, 'text-emerald-400');
                    document.getElementById('sub-cash').innerText = Math.round((liq * 0.42) / factor).toLocaleString();
                    document.getElementById('sub-bank').innerText = Math.round((liq * 0.58) / factor).toLocaleString();
                    document.getElementById('card-ar').innerText = Math.round(ar / factor).toLocaleString();
                    document.getElementById('card-ap').innerText = Math.round(ap / factor).toLocaleString();
                    document.getElementById('net-worth-val').innerText = Math.round((liq + ar - ap) / factor).toLocaleString();

                    document.getElementById('main-tons').innerText = tons;
                    const bars = Math.round(tons * 135);
                    const bundles = Math.round(bars / 15);
                    document.getElementById('main-bars-bundles').innerText = `حديد: ${tons}ط | كراتين وألواح: 1,850 وحدة`;
                    document.getElementById('stock-mkt-val').innerHTML = `${(((tons * rebar) / factor) / 1000000).toFixed(1)}M <small class="exec-unit-tag">${unit}</small>`;

                    document.getElementById('aud-liq').innerText = Math.round(liq / factor).toLocaleString();
                    document.getElementById('aud-stock').innerText = `${tons} طن + 1,850 وحدة`;
                    document.getElementById('aud-ar').innerText = Math.round(ar / factor).toLocaleString();
                    document.getElementById('aud-ap').innerText = Math.round(ap / factor).toLocaleString();

                    document.getElementById('radar-cash-now').innerText = Math.round(liq / factor).toLocaleString() + ' ' + unit;
                    document.getElementById('radar-stock-now').innerText = tons + ' طن';
                    const rebarUnitEl = document.getElementById('hdr-rebar-unit');
                    if (rebarUnitEl) rebarUnitEl.innerText = unit;

                    const activeScope = window._currentDashScope || 'today';
                    const salesRaw = { today: 1890000, week: 9450000, month: 38200000, all: 142000000 };
                    const profitRaw = { today: 348000, week: 1740000, month: 7120000, all: 26400000 };
                    document.getElementById('main-sales').innerHTML = window._fmtDualSub(salesRaw[activeScope] || 1890000, '', 'text-sky-400');
                    document.getElementById('main-profit').innerHTML = window._fmtDualSub(profitRaw[activeScope] || 348000, '+', 'text-emerald-300');

                    calcSajanaWeights();
                    const cfInEl = document.getElementById('cf-in-val');
                    if (cfInEl) cfInEl.innerText = '+' + Math.round(1320000 / factor).toLocaleString() + ' ' + unit;
                    const cfOutEl = document.getElementById('cf-out-val');
                    if (cfOutEl) cfOutEl.innerText = '-' + Math.round(410000 / factor).toLocaleString() + ' ' + unit;
                    const nwEl = document.getElementById('net-worth-val');
                    if (nwEl) nwEl.innerText = Math.round((liq + ar - ap) / factor).toLocaleString() + ' ' + unit;
                    document.querySelectorAll('#radar-feed [data-sdg]').forEach(el => {
                        const raw = parseFloat(el.getAttribute('data-sdg')) || 0;
                        el.innerText = Math.round(raw / factor).toLocaleString() + ' ' + unit;
                    });
                    if (typeof cycleExecutiveSummaryPeriod === 'function') {
                        cycleExecutiveSummaryPeriod(window._execSumPeriodIdx || 0, true);
                    }
                    if (typeof switchPartnersRadar === 'function') {
                        switchPartnersRadar(window._lastPartnersRadarMode || (window._partnersGroupMode === 'supp' ? 'supp_cycle' : 'freq_alert'), window._lastPartnersRadarBtn);
                    }
                    if (typeof switchItemsRadar === 'function') {
                        switchItemsRadar(window._lastItemsRadarMode || 'top_sell', window._lastItemsRadarBtn);
                    }
                    if (typeof switchDebtView === 'function') {
                        switchDebtView(window._debtAgingMode || 'aging', null);
                    }
                    if (typeof switchForecastRadar === 'function') {
                        switchForecastRadar(window._lastForecastMode || 'liquidity', window._lastForecastBtn);
                    }
                }

                function toggleCurrency() {
                    displayUSD = !displayUSD;
                    const txtEl = document.getElementById('currency-btn-txt');
                    if (txtEl) {
                        txtEl.innerText = displayUSD ? 'دولار ⇄' : 'جنيه ⇄';
                    } else {
                        document.getElementById('currency-btn').innerText = displayUSD ? 'دولار ⇄' : 'جنيه ⇄';
                    }
                    updateFromCurtain();
                }

                function onDashBenchmarkItemChange(sel) {
                    if (!sel) return;
                    const price = parseFloat(sel.value) || 420000;
                    const curRebar = document.getElementById('cur-rebar');
                    if (curRebar) curRebar.value = price;
                    updateFromCurtain();
                }

                function _speakDashArabicText(rawText) {
                    const cleanText = String(rawText || '').replace(/ج\.س/g, ' جنيه سوداني ').replace(/\$/g, ' دولار أمريكي ');
                    if (typeof window.speakMdExplanationPanelText === 'function') {
                        window.speakMdExplanationPanelText(cleanText);
                        return;
                    }
                    if ('speechSynthesis' in window) {
                        window.speechSynthesis.cancel();
                        const u = new SpeechSynthesisUtterance(cleanText);
                        u.lang = 'ar-SA';
                        u.rate = 1.02;
                        window.speechSynthesis.speak(u);
                    }
                }

                function speakExecutiveDashboardBriefing() {
                    const sal = (document.getElementById('strip-sal-amt')?.innerText || '').trim();
                    const liq = (document.getElementById('strip-liq-amt')?.innerText || '').trim();
                    const prf = (document.getElementById('strip-prf-amt')?.innerText || '').trim();
                    const mkt = (document.getElementById('stock-mkt-val')?.innerText || '').trim();
                    const ar = (document.getElementById('strip-ar-amt')?.innerText || '').trim();
                    const ap = (document.getElementById('strip-ap-amt')?.innerText || '').trim();
                    const text = `الإيجاز القيادي الذكي للوحة التحكم: إجمالي المبيعات ${sal}، وصافي السيولة المتاحة ${liq}، والأرباح المحققة ${prf}. القيمة السوقية الإجمالية لأصولك ومخزونك ${mkt}. ديون العملاء المطلوب تحصيلها ${ar}، ومستحقات الموردين ${ap}.`;
                    _speakDashArabicText(text);
                }

                function startLiveDashVoiceAdvisor() {
                    if (typeof window.openGlobalProfessorAiChatModal === 'function') {
                        window.openGlobalProfessorAiChatModal();
                        return;
                    }
                    const sal = (document.getElementById('strip-sal-amt')?.innerText || '').trim();
                    const prf = (document.getElementById('strip-prf-amt')?.innerText || '').trim();
                    const ar = (document.getElementById('strip-ar-amt')?.innerText || '').trim();
                    const ap = (document.getElementById('strip-ap-amt')?.innerText || '').trim();
                    const msg = `مرحباً بك في المحاور البروفيسوري الذكي. بناءً على قراءة العشر مؤشرات الحالية: مبيعاتك ${sal} بصافي ربح ${prf}. أنصحك بتحصيل ديون العملاء البالغة ${ar} لتغطية مستحقات الموردين البالغة ${ap} وتعزيز السيولة الفورية.`;
                    _speakDashArabicText(msg);
                    if (typeof openFullCardModal === 'function') openFullCardModal('forecast');
                }

                function _activateMiniTab(btn, activeCls) {
                    if (!btn || !btn.parentElement) return;
                    Array.from(btn.parentElement.children).forEach(b => {
                        b.className = 'radar-glow-btn py-1.5 px-1.5 rounded-lg bg-slate-800 text-slate-200 text-xs font-black text-center whitespace-nowrap';
                        b.removeAttribute('style');
                    });
                    btn.className = 'radar-glow-btn active-radar-tab py-1.5 px-1.5 rounded-lg ' + (activeCls || 'bg-emerald-600') + ' text-white text-xs font-black text-center whitespace-nowrap';
                    const bgMap = { 'bg-emerald-600': '#059669', 'bg-rose-600': '#e11d48', 'bg-sky-600': '#0284c7', 'bg-amber-600': '#d97706' };
                    btn.style.cssText = 'background:linear-gradient(180deg,' + (bgMap[activeCls] || '#059669') + ' 0%,#0f172a 140%)!important;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;border:1.5px solid #38bdf8!important;border-bottom:3px solid #38bdf8!important;box-shadow:0 6px 16px rgba(2,132,199,0.45),inset 0 1px 1px rgba(255,255,255,0.35)!important;';
                }

                if ('caches' in window) { caches.keys().then(ks => ks.forEach(k => caches.delete(k))).catch(()=>{}); }
                if ('serviceWorker' in navigator) { navigator.serviceWorker.getRegistrations().then(rs => rs.forEach(r => r.update())).catch(()=>{}); }
                (function _initStickyCommandBarPin() {
                    const syncPin = () => {
                        const bar = document.getElementById('exec-sticky-command-bar');
                        const ph = document.getElementById('exec-sticky-command-placeholder');
                        const ref = document.getElementById('dash-today-harvest-strip');
                        if (!bar || !ph || !ref) return;
                        const mainEl = document.querySelector('.dash-main');
                        if (mainEl && mainEl.style.transform) mainEl.style.transform = '';
                        const refRect = ref.getBoundingClientRect();
                        if (refRect.top <= 84) {
                            ph.style.display = 'block';
                            bar.classList.add('is-pinned-fixed');
                            bar.style.left = refRect.left + 'px';
                            bar.style.width = refRect.width + 'px';
                        } else {
                            ph.style.display = 'none';
                            bar.classList.remove('is-pinned-fixed');
                            bar.style.left = '';
                            bar.style.width = '';
                        }
                    };
                    window.addEventListener('scroll', syncPin, { passive: true });
                    document.addEventListener('scroll', syncPin, true);
                    window.addEventListener('resize', syncPin, { passive: true });
                    setInterval(syncPin, 100);
                    setTimeout(syncPin, 80);
                })();
                window._hiddenBrokerIds = JSON.parse(localStorage.getItem('magaiees_hidden_broker_ids') || '[]');
                function toggleMarketBrokerTicker(show) {
                    const wrap = document.getElementById('market-broker-ticker-wrap');
                    const pill = document.getElementById('market-broker-restore-pill');
                    const ordRow = document.getElementById('market-broker-orders-row');
                    if (wrap) wrap.style.display = show ? 'flex' : 'none';
                    if (show && ordRow) ordRow.style.display = 'flex';
                    if (pill) pill.style.display = show ? 'none' : 'block';
                    localStorage.setItem('magaiees_hide_broker_ticker', show ? '0' : '1');
                }
                function _safeBrkSet(k, v) {
                    try { localStorage.setItem(k, v); } catch (e) {
                        try { ['erp_auto_backups', 'erp_audit_logs', 'erp_items_v1'].forEach(rk => localStorage.removeItem(rk)); localStorage.setItem(k, v); } catch (e2) {}
                    }
                }
                const _savedBrkPrefs = (() => {
                    try {
                        const m = document.cookie.match(/(?:^|;\s*)magaiees_broker_prefs_v2=([^;]+)/);
                        if (m && m[1]) return JSON.parse(decodeURIComponent(m[1])) || {};
                    } catch (e) {}
                    try { return JSON.parse(localStorage.getItem('magaiees_broker_prefs_v2') || '{}') || {}; } catch (e) { return {}; }
                })();
                window._brokerOffersSec = Math.max(20, Math.min(200, parseInt(_savedBrkPrefs.offersSec ?? '160', 10) || 160));
                window._brokerOrdersSec = Math.max(20, Math.min(200, parseInt(_savedBrkPrefs.ordersSec ?? '200', 10) || 200));
                window._brokerFontStep = Math.max(-3, Math.min(5, parseInt(_savedBrkPrefs.fontStep ?? localStorage.getItem('magaiees_broker_font_step') ?? '2', 10)));
                if (isNaN(window._brokerFontStep)) window._brokerFontStep = 2;
                window._brokerMaxAgeHours = parseInt(_savedBrkPrefs.maxAgeHours ?? localStorage.getItem('magaiees_broker_max_age_h') ?? '24', 10) || 24;
                window._brokerHideNames = _savedBrkPrefs.hideNames !== undefined ? !!_savedBrkPrefs.hideNames : (localStorage.getItem('magaiees_broker_hide_names') === '1');
                window._brokerDockMode = (_savedBrkPrefs.dockMode === 'bottom' || localStorage.getItem('magaiees_broker_dock_mode') === 'bottom') ? 'bottom' : 'top';
                function saveBrokerPrefs(showNotice) {
                    const prefs = {
                        fontStep: window._brokerFontStep ?? 2,
                        offersSec: window._brokerOffersSec || 160,
                        ordersSec: window._brokerOrdersSec || 200,
                        maxAgeHours: window._brokerMaxAgeHours || 24,
                        hideNames: !!window._brokerHideNames,
                        myItemsFilter: window._brokerMyItemsFilter !== false,
                        dockMode: window._brokerDockMode === 'bottom' ? 'bottom' : 'top',
                        offersLocked: document.getElementById('market-broker-offers-row')?.style.display === 'none',
                        ordersLocked: document.getElementById('market-broker-orders-row')?.style.display === 'none',
                        hiddenIds: window._hiddenBrokerIds || []
                    };
                    document.cookie = 'magaiees_broker_prefs_v2=' + encodeURIComponent(JSON.stringify(prefs)) + '; path=/; max-age=31536000; SameSite=Lax';
                    _safeBrkSet('magaiees_broker_prefs_v2', JSON.stringify(prefs));
                    _safeBrkSet('magaiees_broker_font_step', String(prefs.fontStep));
                    _safeBrkSet('magaiees_broker_offers_sec', String(prefs.offersSec));
                    _safeBrkSet('magaiees_broker_orders_sec', String(prefs.ordersSec));
                    _safeBrkSet('magaiees_broker_max_age_h', String(prefs.maxAgeHours));
                    _safeBrkSet('magaiees_broker_dock_mode', prefs.dockMode);
                    fetch('api/market_broker_ticker.php?action=save_prefs', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(prefs)
                    }).then(() => {
                        if (showNotice && typeof window.showToast === 'function') window.showToast('✅ تم حفظ إعدادات شريطي البورصة (المقاس والسرعة والموقع) نهائياً', 'success');
                    }).catch(() => {});
                }
                function formatBrokerFontSizeLabel(step) {
                    const s = Math.max(-3, Math.min(5, step || 0));
                    const px = Math.round(13.2 + s * 1.6);
                    return px + 'px (' + (s === 0 ? '0' : (s > 0 ? '+' + s : s)) + ')';
                }
                function formatBrokerSpeedLabel(which) {
                    const isOrd = (which === 'orders' || (which === undefined && window._brkActiveScope === 'order'));
                    const sec = isOrd ? (window._brokerOrdersSec || 200) : (window._brokerOffersSec || 160);
                    const lvl = Math.max(1, Math.min(10, Math.round((220 - sec) / 20) + 1));
                    return (isOrd ? 'سرعة الطلبات ' : 'سرعة العروض ') + lvl + '/10';
                }
                function adjustMarketBrokerFont(delta) {
                    window._brokerFontStep = Math.max(-3, Math.min(5, (window._brokerFontStep || 0) + delta));
                    _safeBrkSet('magaiees_broker_font_step', String(window._brokerFontStep));
                    const lbl = document.getElementById('brk-font-step-lbl');
                    if (lbl) lbl.textContent = formatBrokerFontSizeLabel(window._brokerFontStep);
                    applyMarketBrokerDock(window._brokerDockMode || 'top');
                    saveBrokerPrefs();
                    window._lastBrokerSig = '';
                    loadMarketBrokerTicker();
                }
                function toggleMarketBrokerNames() {
                    window._brokerHideNames = !window._brokerHideNames;
                    _safeBrkSet('magaiees_broker_hide_names', window._brokerHideNames ? '1' : '0');
                    const btn = document.getElementById('brk-toggle-names-btn');
                    if (btn) {
                        btn.innerHTML = window._brokerHideNames ? '👤 الأسماء: مخفية (عروض صافية)' : '👤 الأسماء: ظاهرة';
                        btn.style.background = window._brokerHideNames ? '#059669' : '#1e293b';
                    }
                    saveBrokerPrefs();
                    window._lastBrokerSig = '';
                    loadMarketBrokerTicker();
                }
                function applyMarketBrokerDock(mode) {
                    const w = document.getElementById('market-broker-ticker-wrap');
                    if (!w) return;
                    const next = (mode === 'bottom') ? 'bottom' : 'top';
                    window._brokerDockMode = next;
                    _safeBrkSet('magaiees_broker_dock_mode', next);
                    const qaBar = document.getElementById('qa-bar-container');
                    if (next === 'bottom') {
                        w.style.cssText = 'position:fixed;bottom:2px;left:6px;right:calc(var(--sb-collapsed, 62px) + 6px);z-index:9990;display:flex;flex-direction:column;gap:6px;padding:0;margin:0;background:#020617;border-radius:7px;';
                    } else {
                        if (qaBar && qaBar.parentNode && w.nextElementSibling !== qaBar) qaBar.parentNode.insertBefore(w, qaBar);
                        w.style.cssText = 'display:flex;flex-direction:column;gap:6px;padding:0;margin:0;z-index:9990;background:#020617;border-radius:7px;';
                    }
                    const btn = document.getElementById('brk-dock-pos-btn');
                    if (btn) btn.innerHTML = next === 'top' ? '📍 الموقع: أعلى الشاشة ⇄' : '📍 الموقع: أسفل الشاشة ⇄';
                }
                function toggleMarketBrokerDock() {
                    const cur = window._brokerDockMode === 'bottom' ? 'bottom' : 'top';
                    applyMarketBrokerDock(cur === 'top' ? 'bottom' : 'top');
                    saveBrokerPrefs(true);
                }
                setTimeout(() => {
                    applyMarketBrokerDock(window._brokerDockMode || 'top');
                    if (_savedBrkPrefs.offersLocked) { const r = document.getElementById('market-broker-offers-row'); if (r) r.style.display = 'none'; }
                    if (_savedBrkPrefs.ordersLocked) { const r = document.getElementById('market-broker-orders-row'); if (r) r.style.display = 'none'; }
                }, 20);
                function getBrokerSpeedPxPerSec(secVal) {
                    const s = Math.max(20, Math.min(200, secVal || 80));
                    const lvl = Math.max(1, Math.min(10, Math.round((220 - s) / 20) + 1));
                    const table = [18, 30, 44, 60, 78, 98, 120, 145, 175, 210];
                    return table[lvl - 1] || 78;
                }
                function applyMarketBrokerSpeed() {
                    const spLbl = document.getElementById('brk-speed-step-lbl');
                    if (spLbl) spLbl.textContent = formatBrokerSpeedLabel(window._brkActiveScope === 'order' ? 'orders' : 'offers');
                    const offLbl = document.getElementById('brk-speed-offers-lbl');
                    if (offLbl) offLbl.textContent = formatBrokerSpeedLabel('offers');
                    const ordLbl = document.getElementById('brk-speed-orders-lbl');
                    if (ordLbl) ordLbl.textContent = formatBrokerSpeedLabel('orders');
                }
                if (!window._brokerRafStarted) {
                    window._brokerRafStarted = true;
                    window._brkPosOff = 0;
                    window._brkPosOrd = 0;
                    let lastTs = performance.now();
                    const stepTickers = (now) => {
                        const dt = Math.min(0.08, Math.max(0, (now - lastTs) / 1000));
                        lastTs = now;
                        const track = document.getElementById('market-broker-ticker-track');
                        const ordTrack = document.getElementById('market-broker-orders-track');
                        const offRow = document.getElementById('market-broker-offers-row');
                        const ordRow = document.getElementById('market-broker-orders-row');
                        if (track && (!offRow || !offRow.matches(':hover'))) {
                            const halfW = (track.scrollWidth || 0) / 2;
                            if (halfW > 50) {
                                window._brkPosOff = (window._brkPosOff + getBrokerSpeedPxPerSec(window._brokerOffersSec) * dt) % halfW;
                                track.style.transform = `translate3d(${window._brkPosOff.toFixed(2)}px, 0, 0)`;
                            }
                        }
                        if (ordTrack && (!ordRow || !ordRow.matches(':hover'))) {
                            const halfW = (ordTrack.scrollWidth || 0) / 2;
                            if (halfW > 50) {
                                window._brkPosOrd = (window._brkPosOrd + getBrokerSpeedPxPerSec(window._brokerOrdersSec) * dt) % halfW;
                                ordTrack.style.transform = `translate3d(${window._brkPosOrd.toFixed(2)}px, 0, 0)`;
                            }
                        }
                        requestAnimationFrame(stepTickers);
                    };
                    requestAnimationFrame(stepTickers);
                }
                function adjustMarketBrokerSpeed(dir, which) {
                    if (which === 'orders') {
                        window._brokerOrdersSec = Math.max(20, Math.min(200, (window._brokerOrdersSec || 200) + (dir > 0 ? -20 : 20)));
                        localStorage.setItem('magaiees_broker_orders_sec', String(window._brokerOrdersSec));
                    } else {
                        window._brokerOffersSec = Math.max(20, Math.min(200, (window._brokerOffersSec || 160) + (dir > 0 ? -20 : 20)));
                        localStorage.setItem('magaiees_broker_offers_sec', String(window._brokerOffersSec));
                    }
                    applyMarketBrokerSpeed();
                    saveBrokerPrefs(true);
                }
                async function reportBrokerItem(id, senderName, phone, ev) {
                    if (ev) ev.stopPropagation();
                    try {
                        const res = await fetch('api/market_broker_ticker.php?action=report', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id, sender_name: senderName, phone })
                        }).then(r => r.json());
                        const pts = res.black_points || 1;
                        alert(res.banned ? `🚫 تم تسجيل النقطة السوداء رقم (${pts}/3) وحظر عروض ورسائل (${senderName}) تلقائياً من البورصة!` : `⚠️ تم تسجيل نقطة سوداء (${pts}/3) ضد (${senderName}). عند وصوله إلى 3 بلاغات يتم حجبه تلقائياً.`);
                    } catch (e) {}
                    window._lastBrokerSig = '';
                    loadMarketBrokerTicker();
                }
                function hideSingleBrokerOffer(id, ev) {
                    if (ev) ev.stopPropagation();
                    if (!window._hiddenBrokerIds.includes(id)) window._hiddenBrokerIds.push(id);
                    localStorage.setItem('magaiees_hidden_broker_ids', JSON.stringify(window._hiddenBrokerIds));
                    saveBrokerPrefs(false);
                    window._lastBrokerSig = '';
                    loadMarketBrokerTicker();
                }
                async function loadMarketBrokerTicker() {
                    const track = document.getElementById('market-broker-ticker-track');
                    const ordTrack = document.getElementById('market-broker-orders-track');
                    if (!track) return;
                    if (localStorage.getItem('magaiees_hide_broker_ticker') === '1') toggleMarketBrokerTicker(false);
                    applyMarketBrokerSpeed();
                    try {
                        const reqs = [fetch('api/market_broker_ticker.php?action=list&_t=' + Date.now()).then(r => r.json())];
                        if (location.hostname === 'localhost' || location.hostname === '127.0.0.1') {
                            reqs.push(fetch('https://c.elsajana.com/api/market_broker_ticker.php?action=list&_t=' + Date.now()).then(r => r.json()));
                        }
                        const settled = await Promise.allSettled(reqs);
                        const mergedMap = new Map();
                        if (!window._brokerPrefsSynced && settled[0]?.status === 'fulfilled' && settled[0].value?.prefs) {
                            window._brokerPrefsSynced = true;
                            const p = settled[0].value.prefs;
                            if (typeof p.fontStep === 'number') { window._brokerFontStep = p.fontStep; _safeBrkSet('magaiees_broker_font_step', String(p.fontStep)); }
                            if (typeof p.offersSec === 'number') { window._brokerOffersSec = p.offersSec; _safeBrkSet('magaiees_broker_offers_sec', String(p.offersSec)); }
                            if (typeof p.ordersSec === 'number') { window._brokerOrdersSec = p.ordersSec; _safeBrkSet('magaiees_broker_orders_sec', String(p.ordersSec)); }
                            if (typeof p.maxAgeHours === 'number') { window._brokerMaxAgeHours = p.maxAgeHours; _safeBrkSet('magaiees_broker_max_age_h', String(p.maxAgeHours)); }
                            if (typeof p.hideNames === 'boolean') { window._brokerHideNames = p.hideNames; _safeBrkSet('magaiees_broker_hide_names', p.hideNames ? '1' : '0'); }
                            if (typeof p.myItemsFilter === 'boolean') { window._brokerMyItemsFilter = p.myItemsFilter; _safeBrkSet('magaiees_broker_my_items_only', p.myItemsFilter ? '1' : '0'); }
                            if (typeof p.dockMode === 'string') { applyMarketBrokerDock(p.dockMode); }
                            document.cookie = 'magaiees_broker_prefs_v2=' + encodeURIComponent(JSON.stringify(p)) + '; path=/; max-age=31536000; SameSite=Lax';
                            _safeBrkSet('magaiees_broker_prefs_v2', JSON.stringify(p));
                            applyMarketBrokerSpeed();
                            window._lastBrokerSig = '';
                        }
                        settled.forEach(s => {
                            if (s.status === 'fulfilled' && s.value) {
                                if (Array.isArray(s.value.items)) {
                                    s.value.items.forEach(it => {
                                        const k = (it.text || '') + '|' + (it.type || '');
                                        if (!mergedMap.has(k)) mergedMap.set(k, it);
                                    });
                                }
                            }
                        });
                        const maxAgeMs = (window._brokerMaxAgeHours || 24) * 3600 * 1000;
                        const nowMs = Date.now();
                        const isWithinMaxAge = (it) => {
                            if (it.ts && Number(it.ts) > 1000000000) return (nowMs - Number(it.ts) * 1000) <= maxAgeMs;
                            return true;
                        };
                        const rawItems = Array.from(mergedMap.values()).filter(x => !window._hiddenBrokerIds.includes(x.id) && isWithinMaxAge(x));
                        if (window._brokerMyItemsFilter === undefined) window._brokerMyItemsFilter = localStorage.getItem('magaiees_broker_my_items_only') !== '0';
                        const isMyItemMatch = (txt) => {
                            if (!window._brokerMyItemsFilter) return true;
                            const s = String(txt || '');
                            if (/ألومنيوم|الومنيوم|زجاج|سيكوريت|كلادينج|سباكة|خلاطات|بوهية|دهان|أخشاب|أبلكاش/i.test(s) && !/سيخ|حديد|كمر|أسمنت|اسمنت|مواسير|صاج|سلك|زوايا/i.test(s)) return false;
                            return true;
                        };
                        const items = rawItems.filter(x => isMyItemMatch(x.text));
                        window._brokerItemsList = items;
                        const sig = JSON.stringify(items.map(x => x.id + ':' + x.text)) + ':' + window._brokerMyItemsFilter + ':' + window._brokerHideNames + ':' + window._brokerFontStep + ':' + window._brokerMaxAgeHours;
                        if (window._lastBrokerSig === sig && track.innerHTML.trim() !== '') return;
                        window._lastBrokerSig = sig;
                        const offers = items.filter(x => x.type !== 'order');
                        const orders = items.filter(x => x.type === 'order');
                        const newestOfferId = offers[0]?.id;
                        const newestOrderId = orders[0]?.id;
                        const formatElapsed = (it) => {
                            const now = Date.now();
                            let diffMin = 0;
                            if (it.ts && Number(it.ts) > 1000000000) {
                                diffMin = Math.max(0, Math.floor((now - Number(it.ts) * 1000) / 60000));
                            } else if (it.time && /^\d{1,2}:\d{2}$/.test(String(it.time).trim())) {
                                const [hh, mm] = String(it.time).trim().split(':').map(Number);
                                const d = new Date();
                                d.setHours(hh, mm, 0, 0);
                                if (d.getTime() > now) d.setDate(d.getDate() - 1);
                                diffMin = Math.max(0, Math.floor((now - d.getTime()) / 60000));
                            }
                            if (diffMin <= 2) return 'منذ لحظات';
                            if (diffMin < 60) return `منذ ${diffMin} دقيقة`;
                            const totalHrs = Math.floor(diffMin / 60);
                            const days = Math.floor(totalHrs / 24);
                            const hrs = totalHrs % 24;
                            const fmtHr = (h) => h === 1 ? 'ساعة' : (h === 2 ? 'ساعتين' : (h <= 10 ? `${h} ساعات` : `${h} ساعة`));
                            const fmtDay = (d) => d === 1 ? 'يوم' : (d === 2 ? 'يومين' : `${d} أيام`);
                            if (days === 0) return `منذ ${fmtHr(hrs)}`;
                            return hrs > 0 ? `منذ ${fmtDay(days)} و${fmtHr(hrs)}` : `منذ ${fmtDay(days)}`;
                        };
                        const renderPill = (it, idx) => {
                            const isOrd = it.type === 'order';
                            const txtCls = (isOrd ? 'brk-ord-txt-' : 'brk-off-txt-') + (idx % 3);
                            const hdrCls = isOrd ? 'brk-pill-hdr-ord' : 'brk-pill-hdr-off';
                            const timeCls = isOrd ? 'brk-ord-time' : 'brk-off-time';
                            const isNew = (it.id === newestOfferId || it.id === newestOrderId || it.id === window._justAddedBrokerId);
                            const isMasked = it.privacy === 'masked';
                            const badge = isOrd ? '🟠 مطلوب' : '🟢 عرض';
                            const cleanSender = String(it.sender_name || '').replace(/\s*#?\d+\s*🔒?/g, '').trim();
                            const fPx = (13.2 + (window._brokerFontStep || 0) * 1.6).toFixed(1);
                            const newTag = isNew ? `<span style="background:#e11d48;color:#fff!important;-webkit-text-fill-color:#fff!important;padding:1px 6px;border-radius:999px;font-size:${(parseFloat(fPx)*0.78).toFixed(1)}px;font-weight:700;">🔥 جديد</span>` : '';
                            const hdrTxt = (window._brokerHideNames || isMasked || !cleanSender || /تاجر معتمد|مورد معتمد|مشتري معتمد/.test(cleanSender)) ? `${badge}:` : `${badge} (${cleanSender}):`;
                            const elapsedTxt = formatElapsed(it);
                            const tailTxt = `<small class="${timeCls}" style="font-size:${(parseFloat(fPx)*0.88).toFixed(1)}px;font-weight:700;">⏱️ ${elapsedTxt}</small>`;
                            return `<span onclick="openBrokerDealAction('${it.sender_name}', '${it.text}', '${it.deal_code || ''}', '${it.privacy || 'public'}', '${it.phone || ''}')" class="${txtCls}" style="display:inline-flex;align-items:center;gap:5px;padding:0 4px;background:transparent;border:none;font-size:${fPx}px!important;font-weight:600;letter-spacing:0.15px;cursor:pointer;line-height:1.25;"><button type="button" onclick="hideSingleBrokerOffer('${it.id}', event)" title="إخفاء" style="width:13px;height:13px;padding:0;background:rgba(225,29,72,0.18);color:#e11d48;border:none;border-radius:50%;font-size:8.5px;font-weight:700;line-height:1;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;">✕</button>${newTag}<span class="${hdrCls}" style="font-size:${fPx}px!important;font-weight:700;">${hdrTxt}</span><span class="${txtCls}" style="font-size:${fPx}px!important;">${it.text}</span>${tailTxt}<span style="opacity:0.45;margin-right:8px;">✦</span></span>`;
                        };
                        const buildLoopList = (arr) => {
                            if (!arr.length) return [];
                            let base = [...arr];
                            while (base.length < 4) base = base.concat(arr);
                            return [...base, ...base];
                        };
                        track.innerHTML = buildLoopList(offers).map(renderPill).join('');
                        if (ordTrack) ordTrack.innerHTML = buildLoopList(orders).map(renderPill).join('');
                        requestAnimationFrame(() => applyMarketBrokerSpeed());
                        if (document.getElementById('broker-post-modal')?.style.display === 'flex') renderBrokerModalTable();
                    } catch (e) {}
                }
                function toggleBrokerMyItemsFilter() {
                    window._brokerMyItemsFilter = !window._brokerMyItemsFilter;
                    localStorage.setItem('magaiees_broker_my_items_only', window._brokerMyItemsFilter ? '1' : '0');
                    const btn = document.getElementById('brk-my-items-btn');
                    if (btn) {
                        btn.innerHTML = window._brokerMyItemsFilter ? '🎯 ذكاء الأصناف: مطابق لنشاطي' : '🌐 ذكاء الأصناف: كل السوق';
                        btn.style.background = window._brokerMyItemsFilter ? '#059669' : '#1e293b';
                    }
                    window._lastBrokerSig = '';
                    loadMarketBrokerTicker();
                }
                function openBrokerDealAction(sender, text, code, privacy, phone) {
                    const dealCode = code || '101';
                    let em = document.getElementById('broker-escrow-modal');
                    if (!em) {
                        em = document.createElement('div');
                        em.id = 'broker-escrow-modal';
                        em.style.cssText = 'position:fixed;inset:0;z-index:2147483635;background:rgba(2,6,23,0.88);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;padding:12px;direction:rtl;font-family:Cairo,Tajawal,sans-serif;';
                        document.body.appendChild(em);
                    }
                    const isMasked = privacy === 'masked' || window._brokerHideNames;
                    em.innerHTML = `<div style="width:100%;max-width:620px;background:linear-gradient(160deg,#0f172a,#020617);border:2.5px solid #10b981;border-radius:20px;padding:16px 18px;color:#fff;box-shadow:0 24px 60px rgba(0,0,0,0.92);display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:8px;border-bottom:1.5px solid rgba(16,185,129,0.35);">
                            <strong style="font-size:16px;color:#34d399!important;-webkit-text-fill-color:#34d399!important;">🛡️ وساطة وضمان منصة مقاييس المعتمد (صفقة #${dealCode})</strong>
                            <button type="button" onclick="document.getElementById('broker-escrow-modal').style.display='none'" style="background:#e11d48;color:#fff;border:none;border-radius:8px;padding:4px 12px;font-weight:900;cursor:pointer;">✕ إغلاق</button>
                        </div>
                        <div style="padding:10px 12px;border-radius:12px;background:rgba(15,23,42,0.9);border:1.5px solid #38bdf8;font-size:13.5px;font-weight:900;">
                            <div style="color:#fde047!important;-webkit-text-fill-color:#fde047!important;margin-bottom:4px;">${isMasked ? '🔒 الهوية محجوبة ومحمية بالكامل بواسطة مقاييس (كود #' + dealCode + ')' : '👤 الطرف العارض: ' + sender}</div>
                            <div style="color:#fff!important;-webkit-text-fill-color:#fff!important;">📦 التفاصيل: ${text}</div>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;font-size:11.5px;font-weight:900;text-align:center;">
                            <div style="padding:8px;border-radius:10px;background:rgba(16,185,129,0.14);border:1px solid #10b981;color:#6ee7b7!important;-webkit-text-fill-color:#6ee7b7!important;">1️⃣ ربط سري وتثبيت الكمية والسعر فوراً</div>
                            <div style="padding:8px;border-radius:10px;background:rgba(2,132,199,0.14);border:1px solid #38bdf8;color:#bae6fd!important;-webkit-text-fill-color:#bae6fd!important;">2️⃣ ضمان مالي عبر حساب مقاييس الضامن</div>
                            <div style="padding:8px;border-radius:10px;background:rgba(245,158,11,0.14);border:1px solid #f59e0b;color:#fde68a!important;-webkit-text-fill-color:#fde68a!important;">3️⃣ فحص واستلام البضاعة وتحرير السداد</div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                            <input id="escrow-buyer-name" type="text" placeholder="اسمك / شركتك (محفوظ سرياً لدى مقاييس)" style="height:36px;border-radius:9px;background:#1e293b;color:#fff;border:1px solid #475569;padding:0 10px;font-size:12.5px;font-weight:800;">
                            <input id="escrow-buyer-phone" type="text" placeholder="رقم واتسابك للتأكيد السري" value="249123005622" style="height:36px;border-radius:9px;background:#1e293b;color:#fff;border:1px solid #475569;padding:0 10px;font-size:12.5px;font-weight:800;direction:ltr;">
                        </div>
                        <input id="escrow-buyer-note" type="text" placeholder="الكمية المطلوبة أو ملاحظة التسليم (مثلاً: أرغب بشراء الكمية كاملة تسليم فوري بضمان مقاييس)..." style="height:36px;border-radius:9px;background:#1e293b;color:#fff;border:1.5px solid #38bdf8;padding:0 10px;font-size:12.5px;font-weight:800;">
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <button type="button" onclick="executeProtectedBrokerDeal('${dealCode}', '${text.replace(/'/g, '')}')" style="flex:2;height:40px;border-radius:10px;background:linear-gradient(135deg,#059669,#0284c7);color:#fff!important;-webkit-text-fill-color:#fff!important;border:1.5px solid #fde047;font-size:13.5px;font-weight:900;cursor:pointer;">🛡️ اعتماد وربط الصفقة سرياً بضمان منصة مقاييس</button>
                            ${!isMasked ? `<button type="button" onclick="document.getElementById('broker-escrow-modal').style.display='none';openAiPartnerWhatsAppModal('${sender}', '${text.replace(/'/g, '')}');" style="flex:1;height:40px;border-radius:10px;background:#1e293b;color:#fde047!important;-webkit-text-fill-color:#fde047!important;border:1px solid #f59e0b;font-size:12.5px;font-weight:900;cursor:pointer;">📞 تواصل مباشر</button>` : ''}
                        </div>
                    </div>`;
                    em.style.display = 'flex';
                }
                async function executeProtectedBrokerDeal(dealCode, dealText) {
                    const bName = (document.getElementById('escrow-buyer-name')?.value || 'تاجر معتمد عبر مقاييس').trim();
                    const bPhone = (document.getElementById('escrow-buyer-phone')?.value || '249123005622').trim();
                    const bNote = (document.getElementById('escrow-buyer-note')?.value || 'موافق على الشراء/التنفيذ الفوري بضمان منصة مقاييس').trim();
                    document.getElementById('broker-escrow-modal').style.display = 'none';
                    try {
                        await fetch('api/market_broker_ticker.php?action=add', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ text: 'تواصل ' + dealCode, sender_name: bName, phone: bPhone, note: bNote })
                        });
                    } catch (e) {}
                    openAiPartnerWhatsAppModal(`غرفة ضمان مقاييس #${dealCode}`, `🛡️ عقد ضمان وساطة مقاييس للصفقة السرية #${dealCode}\n• الصنف: ${dealText}\n• طالب التنفيذ: ${bName} (${bPhone})\n• التوجيه: ${bNote}\n• الضامن: منصة مقاييس (ربط سري كامل وحماية للطرفين)`);
                }
                function toggleSingleBrokerRow(rowId, btn) {
                    const r = document.getElementById(rowId);
                    if (!r) return;
                    const show = r.style.display === 'none';
                    r.style.display = show ? 'flex' : 'none';
                    if (btn) btn.textContent = show ? '🔒 قفل الشريط' : '🔓 إظهار الشريط';
                }
                function switchBrokerControlScope(scope) {
                    window._brkActiveScope = (scope === 'order') ? 'order' : 'offer';
                    const isOrd = window._brkActiveScope === 'order';
                    const btnOff = document.getElementById('brk-scope-offer');
                    const btnOrd = document.getElementById('brk-scope-order');
                    if (btnOff) {
                        btnOff.style.background = !isOrd ? 'linear-gradient(135deg,#059669,#047857)' : '#1e293b';
                        btnOff.style.border = !isOrd ? '2px solid #fde047' : '1px solid #475569';
                    }
                    if (btnOrd) {
                        btnOrd.style.background = isOrd ? 'linear-gradient(135deg,#d97706,#b45309)' : '#1e293b';
                        btnOrd.style.border = isOrd ? '2px solid #fde047' : '1px solid #475569';
                    }
                    const selType = document.getElementById('brk-type');
                    if (selType) selType.value = isOrd ? 'order' : 'offer';
                    const inpTxt = document.getElementById('brk-text');
                    if (inpTxt) inpTxt.placeholder = isOrd ? '🟠 اكتب تفاصيل طلب الشراء هنا لينزل فوراً في شريط الطلبات بشارة 🔥 جديد...' : '🟢 اكتب تفاصيل عرض السعر هنا لينزل فوراً في شريط العروض بشارة 🔥 جديد...';
                    const lockBtn = document.getElementById('brk-unified-lock-btn');
                    const targetRow = document.getElementById(isOrd ? 'market-broker-orders-row' : 'market-broker-offers-row');
                    if (lockBtn && targetRow) {
                        const isHidden = targetRow.style.display === 'none';
                        lockBtn.innerHTML = isHidden ? (isOrd ? '🔓 إظهار الطلبات' : '🔓 إظهار العروض') : (isOrd ? '🔒 قفل الطلبات' : '🔒 قفل العروض');
                    }
                    const spLbl = document.getElementById('brk-speed-step-lbl');
                    if (spLbl) spLbl.textContent = formatBrokerSpeedLabel(isOrd ? 'orders' : 'offers');
                    renderBrokerModalTable(window._brkActiveScope);
                }
                function renderBrokerModalTable(filterMode) {
                    if (filterMode) window._brkTblFilter = filterMode;
                    const mode = window._brkTblFilter || window._brkActiveScope || 'offer';
                    const box = document.getElementById('brk-table-body');
                    if (!box) return;
                    const allList = window._brokerItemsList || [];
                    const list = mode === 'offer' ? allList.filter(x => x.type !== 'order') : (mode === 'order' ? allList.filter(x => x.type === 'order') : allList);
                    const newestOff = allList.find(x => x.type !== 'order')?.id;
                    const newestOrd = allList.find(x => x.type === 'order')?.id;
                    const allBtn = document.getElementById('brk-flt-all');
                    if (allBtn) allBtn.style.border = (mode === 'all') ? '2px solid #fde047' : '1px solid #475569';
                    box.innerHTML = list.map((it, idx) => {
                        const isOrd = it.type === 'order';
                        const isNew = (it.id === newestOff || it.id === newestOrd || it.id === window._justAddedBrokerId);
                        return `<tr style="border-bottom:1px solid rgba(148,163,184,0.22);background:${isNew ? 'rgba(16,185,129,0.18)' : (idx % 2 ? 'rgba(15,23,42,0.5)' : 'transparent')};">
                            <td style="padding:8px 10px;font-weight:900;white-space:nowrap;"><span style="padding:2px 8px;border-radius:7px;background:${isOrd?'#d97706':'#059669'};color:#fff!important;-webkit-text-fill-color:#fff!important;font-size:12px;">${isOrd?'🟠 طلب شراء':'🟢 عرض سعر'}</span> ${isNew?'<span style="background:#e11d48;color:#fff!important;-webkit-text-fill-color:#fff!important;padding:1px 6px;border-radius:6px;font-size:10.5px;border:1px solid #fde047;">🔥 جديد</span>':''}</td>
                            <td class="text-amber-300" style="padding:8px 10px;font-weight:900;font-size:13.5px;white-space:nowrap;">${it.sender_name}${it.deal_code ? ' (#'+it.deal_code+')' : ''}</td>
                            <td class="text-white" style="padding:8px 10px;font-weight:900;font-size:14px;">${it.text}</td>
                            <td class="text-sky-300" style="padding:8px 10px;font-size:12px;font-weight:800;white-space:nowrap;">${it.time} ${it.privacy==='masked'?'🔒 سري':'📞 مباشر'}</td>
                            <td style="padding:8px 10px;white-space:nowrap;display:flex;gap:5px;">
                                <button type="button" onclick="openBrokerDealAction('${it.sender_name}', '${it.text}', '${it.deal_code || ''}', '${it.privacy || 'public'}', '${it.phone || ''}')" style="padding:4px 10px;border-radius:7px;background:#059669;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;font-size:12px;font-weight:900;cursor:pointer;">📲 تواصل</button>
                                <button type="button" onclick="reportBrokerItem('${it.id}', '${it.sender_name}', '${it.phone || ''}', event)" title="بلاغ مخالفة (نقطة سوداء - 3 بلاغات = حظر تلقائي)" style="padding:4px 8px;border-radius:7px;background:#7f1d1d;color:#fde047!important;-webkit-text-fill-color:#fde047!important;border:1px solid #ef4444;font-size:11.5px;font-weight:900;cursor:pointer;">🚨 بلاغ</button>
                                <button type="button" onclick="hideSingleBrokerOffer('${it.id}', event);renderBrokerModalTable();" style="padding:4px 8px;border-radius:7px;background:#e11d48;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;font-size:12px;font-weight:900;cursor:pointer;">✕</button>
                            </td>
                        </tr>`;
                    }).join('');
                }
                function openMarketBrokerQuickPostModal(defaultType) {
                    let m = document.getElementById('broker-post-modal');
                    if (!m) {
                        m = document.createElement('div');
                        m.id = 'broker-post-modal';
                        m.style.cssText = 'position:fixed;inset:0;z-index:2147483620;background:rgba(2,6,23,0.86);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;padding:10px;direction:rtl;font-family:Cairo,Tajawal,sans-serif;';
                        m.innerHTML = `<div style="width:100%;max-width:1120px;max-height:94vh;display:flex;flex-direction:column;gap:8px;background:linear-gradient(160deg,#0f172a,#020617);border:2px solid #38bdf8;border-radius:18px;padding:12px 16px;color:#fff;box-shadow:0 24px 60px rgba(0,0,0,0.92);">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;padding:6px 10px;border-radius:12px;background:#0f172a;border:1.5px solid rgba(56,189,248,0.35);">
                                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                    <button type="button" id="brk-scope-offer" onclick="switchBrokerControlScope('offer')" style="height:32px;padding:0 14px;border-radius:9px;background:linear-gradient(135deg,#059669,#047857);color:#fff!important;-webkit-text-fill-color:#fff!important;border:2px solid #fde047;font-size:13px;font-weight:900;cursor:pointer;">🟢 العروض</button>
                                    <button type="button" id="brk-scope-order" onclick="switchBrokerControlScope('order')" style="height:32px;padding:0 14px;border-radius:9px;background:#1e293b;color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #475569;font-size:13px;font-weight:900;cursor:pointer;">🟠 الطلبات</button>
                                    <button type="button" id="brk-flt-all" onclick="renderBrokerModalTable('all')" style="height:32px;padding:0 12px;border-radius:9px;background:#1e293b;color:#bae6fd!important;-webkit-text-fill-color:#bae6fd!important;border:1px solid #475569;font-size:12px;font-weight:900;cursor:pointer;">📋 عرض الكل</button>
                                </div>
                                <div style="display:flex;align-items:center;gap:5px;flex-wrap:wrap;">
                                    <button type="button" onclick="adjustMarketBrokerSpeed(-1, window._brkActiveScope==='order'?'orders':'offers')" style="height:32px;padding:0 10px;border-radius:9px;background:#1e293b;color:#fde047!important;-webkit-text-fill-color:#fde047!important;border:1px solid #f59e0b;font-size:12px;font-weight:900;cursor:pointer;">🐢 سرعة −</button>
                                    <span id="brk-speed-step-lbl" style="height:32px;padding:0 10px;border-radius:8px;background:#0f172a;color:#38bdf8!important;-webkit-text-fill-color:#38bdf8!important;border:1px solid #38bdf8;font-size:12px;font-weight:900;display:inline-flex;align-items:center;">${formatBrokerSpeedLabel(window._brkActiveScope==='order'?'orders':'offers')}</span>
                                    <button type="button" onclick="adjustMarketBrokerSpeed(1, window._brkActiveScope==='order'?'orders':'offers')" style="height:32px;padding:0 10px;border-radius:9px;background:#0284c7;color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #7dd3fc;font-size:12px;font-weight:900;cursor:pointer;">⚡ سرعة +</button>
                                    <button type="button" onclick="adjustMarketBrokerFont(-1)" title="إنقاص الخط حتى -3" style="height:32px;padding:0 9px;border-radius:9px;background:#1e293b;color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #475569;font-size:12px;font-weight:900;cursor:pointer;">🔡 خط −</button>
                                    <span id="brk-font-step-lbl" style="height:32px;padding:0 8px;border-radius:8px;background:#0f172a;color:#fde047!important;-webkit-text-fill-color:#fde047!important;border:1px solid #38bdf8;font-size:11.5px;font-weight:900;display:inline-flex;align-items:center;">${formatBrokerFontSizeLabel(window._brokerFontStep)}</span>
                                    <button type="button" onclick="adjustMarketBrokerFont(1)" title="تكبير الخط حتى +5" style="height:32px;padding:0 9px;border-radius:9px;background:#0284c7;color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #7dd3fc;font-size:12px;font-weight:900;cursor:pointer;">🔠 خط +</button>
                                    <button type="button" id="brk-toggle-names-btn" onclick="toggleMarketBrokerNames()" style="height:32px;padding:0 9px;border-radius:9px;background:${window._brokerHideNames?'#059669':'#1e293b'};color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #10b981;font-size:11.5px;font-weight:900;cursor:pointer;">${window._brokerHideNames?'👤 الأسماء: مخفية':'👤 الأسماء: ظاهرة'}</button>
                                    <button type="button" id="brk-my-items-btn" onclick="toggleBrokerMyItemsFilter()" style="height:32px;padding:0 9px;border-radius:9px;background:${window._brokerMyItemsFilter!==false?'#059669':'#1e293b'};color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #34d399;font-size:11.5px;font-weight:900;cursor:pointer;">${window._brokerMyItemsFilter!==false?'🎯 ذكاء الأصناف: نشاطي':'🌐 ذكاء الأصناف: الكل'}</button>
                                    <button type="button" id="brk-unified-lock-btn" onclick="toggleSingleBrokerRow(window._brkActiveScope==='order'?'market-broker-orders-row':'market-broker-offers-row');switchBrokerControlScope(window._brkActiveScope);saveBrokerPrefs();" style="height:32px;padding:0 9px;border-radius:9px;background:#be123c;color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #fda4af;font-size:11.5px;font-weight:900;cursor:pointer;">🔒 قفل العروض</button>
                                    <select id="brk-max-age-sel" onchange="window._brokerMaxAgeHours=parseInt(this.value,10)||24;saveBrokerPrefs(true);window._lastBrokerSig='';loadMarketBrokerTicker().then(()=>renderBrokerModalTable());" title="تحديد المدة الزمنية لبقاء العروض والطلبات قبل إخفائها تلقائياً" style="height:32px;padding:0 8px;border-radius:9px;background:#0f172a;color:#fde047!important;-webkit-text-fill-color:#fde047!important;border:1.5px solid #f59e0b;font-size:11.5px;font-weight:900;cursor:pointer;">
                                        <option value="2" ${window._brokerMaxAgeHours===2?'selected':''}>⏱️ التحديث: ساعتان</option>
                                        <option value="4" ${window._brokerMaxAgeHours===4?'selected':''}>⏱️ التحديث: 4 ساعات</option>
                                        <option value="8" ${window._brokerMaxAgeHours===8?'selected':''}>⏱️ التحديث: 8 ساعات</option>
                                        <option value="12" ${window._brokerMaxAgeHours===12?'selected':''}>⏱️ التحديث: 12 ساعة</option>
                                        <option value="24" ${(!window._brokerMaxAgeHours||window._brokerMaxAgeHours===24)?'selected':''}>⏱️ التحديث: كل يوم (24 ساعة)</option>
                                        <option value="48" ${window._brokerMaxAgeHours===48?'selected':''}>⏱️ التحديث: يومان (48 ساعة)</option>
                                        <option value="168" ${window._brokerMaxAgeHours===168?'selected':''}>⏱️ التحديث: أسبوع كامل</option>
                                    </select>
                                    <button type="button" id="brk-dock-pos-btn" onclick="toggleMarketBrokerDock()" style="height:32px;padding:0 9px;border-radius:9px;background:#1e293b;color:#fde047!important;-webkit-text-fill-color:#fde047!important;border:1px solid #f59e0b;font-size:11.5px;font-weight:900;cursor:pointer;">📍 الموقع: ${localStorage.getItem('magaiees_broker_dock_mode')==='bottom'?'أسفل الشاشة ⇄':'أعلى الشاشة ⇄'}</button>
                                    <button type="button" onclick="window._hiddenBrokerIds=[];window._brokerFontStep=2;window._brokerOffersSec=160;window._brokerOrdersSec=200;window._brokerMaxAgeHours=24;window._brokerHideNames=false;window._brokerMyItemsFilter=true;const agSel=document.getElementById('brk-max-age-sel');if(agSel)agSel.value='24';localStorage.setItem('magaiees_hidden_broker_ids','[]');applyMarketBrokerDock('top');applyMarketBrokerSpeed();saveBrokerPrefs(true);window._lastBrokerSig='';loadMarketBrokerTicker();" style="height:32px;padding:0 9px;border-radius:9px;background:#0f172a;color:#38bdf8!important;-webkit-text-fill-color:#38bdf8!important;border:1px solid #38bdf8;font-size:11.5px;font-weight:900;cursor:pointer;">♻️ استعادة الافتراضي</button>
                                    <button type="button" onclick="saveBrokerPrefs(true);document.getElementById('broker-post-modal').style.display='none'" style="height:32px;padding:0 12px;border-radius:9px;background:linear-gradient(135deg,#059669,#047857);color:#fff!important;-webkit-text-fill-color:#fff!important;border:1.5px solid #fde047;font-size:12px;font-weight:900;cursor:pointer;">💾 حفظ وخروج</button>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;padding:6px 8px;border-radius:10px;background:rgba(15,23,42,0.9);border:1px solid #38bdf8;">
                                <select id="brk-type" onchange="switchBrokerControlScope(this.value)" style="height:32px;border-radius:8px;background:#1e293b;color:#fff;border:1px solid #38bdf8;padding:0 8px;font-size:12px;font-weight:900;">
                                    <option value="offer">🟢 عرض أسعار (بيع)</option>
                                    <option value="order">🟠 طلب شراء (مطلوب)</option>
                                </select>
                                <select id="brk-privacy" style="height:32px;border-radius:8px;background:#1e293b;color:#fde047;border:1px solid #f59e0b;padding:0 8px;font-size:12px;font-weight:900;">
                                    <option value="public">👁️ عام بالاسم</option>
                                    <option value="masked">🔒 سري بكود</option>
                                </select>
                                <input id="brk-name" type="text" placeholder="الاسم (مثلاً: مهدي)" style="width:130px;height:32px;border-radius:8px;background:#1e293b;color:#fff;border:1px solid #475569;padding:0 8px;font-size:12px;font-weight:800;">
                                <input id="brk-phone" type="text" placeholder="رقم الواتساب" value="249123005622" style="width:130px;height:32px;border-radius:8px;background:#1e293b;color:#fff;border:1px solid #475569;padding:0 8px;font-size:12px;font-weight:800;direction:ltr;">
                                <input id="brk-text" type="text" placeholder="🟢 اكتب تفاصيل عرض السعر هنا لينزل فوراً في شريط العروض بشارة 🔥 جديد..." style="flex:1;min-width:220px;height:32px;border-radius:8px;background:#1e293b;color:#fff;border:1.5px solid #38bdf8;padding:0 10px;font-size:12.5px;font-weight:800;">
                                <button type="button" onclick="submitBrokerPost()" style="padding:0 16px;height:32px;border-radius:8px;background:linear-gradient(135deg,#059669,#0284c7);color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #fde047;font-size:12.5px;font-weight:900;cursor:pointer;white-space:nowrap;">🚀 انشر الآن</button>
                            </div>
                            <div style="flex:1;min-height:0;display:flex;flex-direction:column;border-radius:12px;border:1.5px solid rgba(56,189,248,0.35);overflow:hidden;background:rgba(2,6,23,0.65);">
                                <div style="flex:1;max-height:66vh;min-height:400px;overflow-y:auto;">
                                    <table style="width:100%;border-collapse:collapse;font-size:13.5px;text-align:right;">
                                        <thead style="position:sticky;top:0;z-index:2;"><tr style="background:#0f172a;color:#38bdf8!important;-webkit-text-fill-color:#38bdf8!important;border-bottom:1.5px solid rgba(56,189,248,0.35);"><th style="padding:8px 10px;">النوع</th><th style="padding:8px 10px;">العارض / الكود</th><th style="padding:8px 10px;">الأصناف والتفاصيل والأسعار المعروضة</th><th style="padding:8px 10px;">التوقيت</th><th style="padding:8px 10px;">إجراء</th></tr></thead>
                                        <tbody id="brk-table-body"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>`;
                        document.body.appendChild(m);
                    }
                    switchBrokerControlScope(defaultType || 'offer');
                    m.style.display = 'flex';
                }
                async function submitBrokerPost() {
                    const txt = (document.getElementById('brk-text')?.value || '').trim();
                    if (!txt) return;
                    await fetch('api/market_broker_ticker.php?action=add', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            type: document.getElementById('brk-type').value,
                            privacy: document.getElementById('brk-privacy').value,
                            sender_name: document.getElementById('brk-name').value,
                            phone: document.getElementById('brk-phone').value,
                            text: txt
                        })
                    });
                    document.getElementById('brk-text').value = '';
                    document.getElementById('broker-post-modal').style.display = 'none';
                    window._lastBrokerSig = '';
                    loadMarketBrokerTicker();
                }
                loadMarketBrokerTicker();
                setInterval(loadMarketBrokerTicker, 900);
                window._execSumPeriodIdx = 0;
                function cycleExecutiveSummaryPeriod(targetIdx, skipScopeSync) {
                    const periods = [
                        { lbl: 'اليوم ⇄', scope: 'today', mult: 1, mktSdg: 184300000, apSdg: 290000, arSdg: 820000 },
                        { lbl: 'أسبوع ⇄', scope: 'week', mult: 6.5, mktSdg: 186800000, apSdg: 410000, arSdg: 1150000 },
                        { lbl: '10 أيام ⇄', scope: 'week', mult: 9.2, mktSdg: 188400000, apSdg: 480000, arSdg: 1340000 },
                        { lbl: 'شهر ⇄', scope: 'month', mult: 28, mktSdg: 194200000, apSdg: 690000, arSdg: 2400000 },
                        { lbl: '3 شهور ⇄', scope: 'all', mult: 82, mktSdg: 208500000, apSdg: 920000, arSdg: 4100000 },
                        { lbl: '6 شهور ⇄', scope: 'all', mult: 162, mktSdg: 225000000, apSdg: 1180000, arSdg: 5800000 },
                        { lbl: '9 شهور ⇄', scope: 'all', mult: 240, mktSdg: 241600000, apSdg: 1350000, arSdg: 6900000 },
                        { lbl: 'سنة ⇄', scope: 'all', mult: 335, mktSdg: 264000000, apSdg: 1540000, arSdg: 8200000 }
                    ];
                    window._execSumPeriodIdx = (typeof targetIdx === 'number') ? targetIdx : ((window._execSumPeriodIdx + 1) % periods.length);
                    const p = periods[window._execSumPeriodIdx] || periods[0];
                    const usd = parseFloat(document.getElementById('cur-usd')?.value) || 600;
                    const factor = displayUSD ? usd : 1;
                    const unit = displayUSD ? '$' : 'ج.س';
                    const uTag = `<small class="exec-unit-tag">${unit}</small>`;
                    const m = v => `${Math.round(v / factor).toLocaleString()} ${uTag}`;
                    const lblEl = document.getElementById('exec-summary-period-lbl');
                    if (lblEl) lblEl.innerText = p.lbl;
                    const pillsBox = document.getElementById('exec-summary-period-pills');
                    if (pillsBox) {
                        Array.from(pillsBox.children).forEach((b, i) => {
                            b.className = i === window._execSumPeriodIdx
                                ? 'exec-period-pill active-period'
                                : 'exec-period-pill';
                        });
                    }
                    const setHtml = (id, v) => { const el = document.getElementById(id); if (el) el.innerHTML = v; };
                    setHtml('strip-sal-amt', m(1890000 * p.mult));
                    setHtml('strip-pur-amt', m(1420000 * p.mult));
                    setHtml('strip-liq-amt', m(3450000 * (1 + (p.mult - 1) * 0.08)));
                    const prfVal = Math.round(348000 * p.mult);
                    const prfLbl = document.getElementById('strip-prf-lbl');
                    const prfAmt = document.getElementById('strip-prf-amt');
                    if (prfLbl && prfAmt) {
                        if (prfVal >= 0) {
                            prfLbl.innerText = 'الأرباح:';
                            prfAmt.className = 'exec-jewel-val text-emerald-400';
                            prfAmt.style.cssText = 'color:#4ade80!important;-webkit-text-fill-color:#4ade80!important;';
                            prfAmt.innerHTML = '+' + m(prfVal);
                        } else {
                            prfLbl.innerText = 'الخسائر:';
                            prfAmt.className = 'exec-jewel-val text-rose-400';
                            prfAmt.style.cssText = 'color:#fb7185!important;-webkit-text-fill-color:#fb7185!important;';
                            prfAmt.innerHTML = '-' + m(Math.abs(prfVal));
                        }
                    }
                    setHtml('strip-exp-amt', m(42000 * p.mult));
                    setHtml('stock-mkt-val', `${(((p.mktSdg / factor) / 1000000).toFixed(1))}M ${uTag}`);
                    setHtml('strip-in-amt', '+' + m(1320000 * p.mult));
                    setHtml('strip-out-amt', '-' + m(368000 * p.mult));
                    setHtml('strip-ap-amt', m(p.apSdg));
                    setHtml('strip-ar-amt', m(p.arSdg));
                    if (!skipScopeSync) {
                        const liqMap = ['day', 'week', 'week', 'month', 'year', 'year', 'year', 'year'];
                        window._liqPeriod = liqMap[window._execSumPeriodIdx] || 'day';
                        if (typeof switchLiquidityCardView === 'function') switchLiquidityCardView(window._liqViewMode || 'overview', null);
                        const spMap = ['day', 'day', 'day', 'month', 'year', 'year', 'year', 'year'];
                        if (typeof switchSalesProfitCardView === 'function') switchSalesProfitCardView(spMap[window._execSumPeriodIdx] || 'day', null);
                        if (typeof switchCenterChartCard === 'function') switchCenterChartCard(window._centerChartMode || 'donuts', null);
                        if (typeof setScope === 'function') setScope(p.scope, null);
                    }
                }
                window._itemsGroup = 'sales';
                function toggleItemsGroup() {
                    window._itemsGroup = window._itemsGroup === 'sales' ? 'stock' : 'sales';
                    const sTabs = document.getElementById('dash-items-tabs-sales');
                    const kTabs = document.getElementById('dash-items-tabs-stock');
                    const btn = document.getElementById('dash-items-group-toggle');
                    if (window._itemsGroup === 'stock') {
                        if (sTabs) sTabs.style.display = 'none';
                        if (kTabs) kTabs.style.display = 'grid';
                        if (btn) btn.innerText = '⇄ المبيعات';
                        const firstBtn = kTabs ? kTabs.querySelector('button') : null;
                        switchItemsRadar('stock_most', firstBtn);
                    } else {
                        if (kTabs) kTabs.style.display = 'none';
                        if (sTabs) sTabs.style.display = 'grid';
                        if (btn) btn.innerText = '⇄ المخازن';
                        const firstBtn = sTabs ? sTabs.querySelector('button') : null;
                        switchItemsRadar('top_sell', firstBtn);
                    }
                }
                window._liqPeriod = 'day';
                window._liqViewMode = 'overview';
                function toggleLiquidityPeriod() {
                    const order = ['day', 'week', 'month', 'year'];
                    const labels = { day: '📅 اليوم ⇄', week: '📆 الأسبوع ⇄', month: '🗓️ الشهر ⇄', year: '🏆 السنة ⇄' };
                    window._liqPeriod = order[(order.indexOf(window._liqPeriod || 'day') + 1) % order.length];
                    const btn = document.getElementById('dash-liq-period-btn');
                    if (btn) btn.innerText = labels[window._liqPeriod];
                    switchLiquidityCardView(window._liqViewMode || 'overview', null);
                }
                function switchLiquidityCardView(mode, btn) {
                    window._liqViewMode = mode;
                    if (btn) _activateMiniTab(btn, mode === 'coverage' ? 'bg-amber-600' : (mode === 'banks' ? 'bg-sky-600' : 'bg-emerald-600'));
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString() + ' ج.س');
                    const box = document.getElementById('dash-liq-bottom-boxes');
                    const sub = document.getElementById('main-liquidity-sub');
                    const mainEl = document.getElementById('main-liquidity');
                    const bdgEl = document.getElementById('dash-liq-card-badge');
                    if (!box) return;
                    const p = window._liqPeriod || 'day';
                    const mult = { day: 1, week: 2.4, month: 6.8, year: 34 }[p] || 1;
                    const pLbl = { day: 'اليوم', week: 'الأسبوع', month: 'الشهر', year: 'السنة' }[p];
                    const gLbl = { day: 'نمو +12.4% ▲', week: 'نمو +18.2% ▲', month: 'نمو +24.6% ▲', year: 'نمو +38.5% ▲' }[p];
                    const covRatio = { day: '15.9x آمن ✓', week: '11.8x آمن ✓', month: '9.4x آمن ✓', year: '12.6x آمن ✓' }[p];
                    if (bdgEl) bdgEl.innerText = gLbl;
                    if (mainEl) mainEl.innerHTML = typeof window._fmtDualHero === 'function' ? window._fmtDualHero(Math.round(3450000 * mult), 'text-emerald-400') : `${m(Math.round(3450000 * mult))}`;
                    if (mode === 'banks') {
                        if (sub) sub.innerText = `🏦 توزيع السيولة البنكية (${pLbl}) • اضغط (+) أو (📊) لعرض كل البنوك والخزائن`;
                        box.innerHTML = `<div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">🏦 بنك الخرطوم:</span><strong class="text-sky-300 text-sm font-extrabold">${m(Math.round(1250000 * mult))}</strong></div><div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">🏦 فيصل وأم درمان:</span><strong class="text-sky-300 text-sm font-extrabold">${m(Math.round(750000 * mult))}</strong></div><div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">💵 الخزنة والفرع:</span><strong class="text-emerald-300 text-sm font-extrabold">${m(Math.round(1450000 * mult))}</strong></div>`;
                    } else if (mode === 'coverage') {
                        if (sub) sub.innerText = `🛡️ كفاية السيولة لتغطية الشيكات الصادرة والتزامات الموردين خلال (${pLbl}): ${covRatio}`;
                        box.innerHTML = `<div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">🟢 شيكات واردة:</span><strong class="text-emerald-300 text-sm font-extrabold">${m(Math.round(4250000 * mult))}</strong></div><div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">🔴 شيكات صادرة:</span><strong class="text-rose-400 text-sm font-extrabold">${m(Math.round(266000 * mult))}</strong></div><div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">🛡️ نسبة التغطية:</span><strong class="text-amber-300 text-sm font-extrabold">${covRatio}</strong></div>`;
                    } else if (mode === 'ai_cash') {
                        if (sub) sub.innerText = `🤖 معادلة التنبؤ (${pLbl}): الرصيد الحالي + (مبيعات وتحصيل ديون متوقع) - سداد الموردين`;
                        box.innerHTML = `<div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">📈 مبيعات وتحصيل:</span><strong class="text-emerald-300 text-sm font-extrabold">+${m(Math.round(5800000 * mult))}</strong></div><div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">📉 سداد موردين:</span><strong class="text-rose-400 text-sm font-extrabold">-${m(Math.round(980000 * mult))}</strong></div><div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">💎 السيولة المتوقعة:</span><strong class="text-sky-300 text-sm font-extrabold">${m(Math.round(8270000 * mult))}</strong></div>`;
                    } else {
                        if (sub) sub.innerText = `▲ تدفق إيجابي متصاعد (+${m(Math.round(910000 * mult))} صافي ${pLbl} • نسبة التغطية ${covRatio})`;
                        box.innerHTML = `<div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">💵 الصندوق:</span><strong class="text-emerald-300 text-sm font-extrabold" id="sub-cash">${m(Math.round(1450000 * mult))}</strong></div><div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">💳 البنوك:</span><strong class="text-sky-300 text-sm font-extrabold" id="sub-bank">${m(Math.round(2000000 * mult))}</strong></div><div class="inner-box p-2.5 flex items-center justify-between"><span class="text-slate-300">🧾 شيكات بالتحصيل:</span><strong class="text-amber-300 text-sm font-extrabold" id="sub-checks-in">${m(Math.round(4250000 * mult))}</strong></div>`;
                    }
                }
                function switchCashflowCardView(mode, btn) {
                    if (btn) _activateMiniTab(btn, mode === 'stress' ? 'bg-amber-600' : (mode === 'ai_cf' ? 'bg-sky-600' : 'bg-emerald-600'));
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString() + ' ج.س');
                    const box = document.getElementById('dash-cashflow-kpis-box');
                    if (!box) return;
                    if (mode === 'ccc') {
                        box.innerHTML = `<div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">📦 دوران المخزون DIO</span><strong class="text-base font-black text-emerald-300">11 يوماً</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">👥 تحصيل العملاء DSO</span><strong class="text-base font-black text-sky-300">9 أيام</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🚚 سداد الموردين DPO</span><strong class="text-base font-black text-amber-300">14 يوماً</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">⚡ صافي دورة النقد</span><strong class="text-base font-black text-emerald-300">6 أيام ✓</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🔄 سرعة تدوير السيولة</span><strong class="text-base font-black text-emerald-300">2.4x شهرياً</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">💎 عائد الدورة الواحدة</span><strong class="text-base font-black text-sky-300">+18.4%</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🧾 تحصيل الشيكات</span><strong class="text-base font-black text-emerald-300">96% منتظم</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🛡️ أمان التدفق اليومي</span><strong class="text-base font-black text-emerald-300">100% آمن</strong></div>`;
                    } else if (mode === 'ai_cf') {
                        box.innerHTML = `<div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🚚 الترحيل والعتالة</span><strong class="text-sm font-black text-amber-300">${m(420000)}</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🏢 التشغيل والرواتب</span><strong class="text-sm font-black text-sky-300">${m(620000)}</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">📊 المنصرف للمبيعات</span><strong class="text-sm font-black text-emerald-300">1.9% فقط ✓</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">⚖️ تغطية التعادل</span><strong class="text-sm font-black text-emerald-300">11.8 ضعفاً ▲</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">⚡ كهرباء ووقود</span><strong class="text-sm font-black text-amber-300">${m(95000)}</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🛠️ صيانة ومهمات</span><strong class="text-sm font-black text-sky-300">${m(68000)}</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">📉 وفر ضبط النفقات</span><strong class="text-sm font-black text-emerald-300">+14.2% ▲</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🛡️ سقف المصروفات</span><strong class="text-sm font-black text-emerald-300">تحت السيطرة ✓</strong></div>`;
                    } else if (mode === 'stress') {
                        box.innerHTML = `<div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🛡️ تغطية الالتزامات</span><strong class="text-base font-black text-emerald-300">11.8 ضعفاً</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">⏳ كفاية النقد التشغيلي</span><strong class="text-base font-black text-sky-300">45 يوماً ✓</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">💱 تحوط تذبذب الصرف</span><strong class="text-base font-black text-amber-300">مؤمّن 92%</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🚨 مخاطر عجز السيولة</span><strong class="text-base font-black text-emerald-300">0% (معدوم)</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">📦 غطاء المخزون السلعي</span><strong class="text-base font-black text-emerald-300">608 ضعفاً ▲</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🏦 سيولة البنوك الفورية</span><strong class="text-base font-black text-sky-300">58% جاهزة</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">💎 فائض إحلال البضاعة</span><strong class="text-base font-black text-emerald-300">+4.2% ربح</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">⚖️ الملاءة الائتمانية</span><strong class="text-base font-black text-emerald-300">ممتازة AAA</strong></div>`;
                    } else {
                        box.innerHTML = `<div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟢 التدفق النقدي</span><strong class="text-base font-black text-emerald-300">84%</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟢 سيولة المبيعات</span><strong class="text-base font-black text-emerald-300">72%</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟡 وفرة المخزون</span><strong class="text-base font-black text-amber-300">78%</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟡 سعة المخازن</span><strong class="text-base font-black text-amber-300">64%</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟢 كفاءة التحصيل</span><strong class="text-base font-black text-emerald-300">81%</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟢 دوران البضاعة</span><strong class="text-base font-black text-sky-300">2.4x</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟢 تغطية الشيكات</span><strong class="text-base font-black text-emerald-300">96%</strong></div><div class="inner-box p-3 flex items-center justify-between"><span class="text-sm font-bold text-white">🟢 هامش الأمان</span><strong class="text-base font-black text-emerald-300">88%</strong></div>`;
                    }
                }
                function switchSalesProfitCardView(scope, btn) {
                    if (btn) _activateMiniTab(btn, (scope === 'warehouses' || scope === 'strategy') ? 'bg-amber-600' : 'bg-sky-600');
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString() + ' ج.س');
                    const d = typeof window._fmtDualSub === 'function' ? window._fmtDualSub : (v, p='') => p + m(v);
                    const mid = document.getElementById('dash-sales-profit-mid');
                    const bot = document.getElementById('dash-sales-profit-bot');
                    const bdg = document.getElementById('scope-badge');
                    if (!mid || !bot) return;
                    const map = {
                        hour: ['الساعة ▲', 'إجمالي مبيعات الساعة', d(236000), 'صافي أرباح الساعة', d(43500, '+'), 'النقد المحصل بالساعة', d(195000), 'مبيعات آجلة بالساعة:', m(41000), 'الفواتير والأصناف بالساعة:', '6 فواتير (14 صنفاً)', 'الوحدات المباعة بالساعة:', '4.2 طن • 85 كرتونة • 30 لوح'],
                        day: ['اليوم ▲', 'إجمالي مبيعات اليوم', d(1890000), 'صافي أرباح اليوم', d(348000, '+'), 'النقد المحصل اليوم', d(1320000), 'مبيعات آجلة اليوم:', m(570000), 'الفواتير والأصناف اليوم:', '38 فاتورة (20 صنفاً)', 'الوحدات المباعة اليوم:', '32.5 طن • 410 كرتونة • 180 لوح'],
                        month: ['الشهر ▲', 'إجمالي مبيعات الشهر', d(54800000), 'صافي أرباح الشهر', d(9860000, '+'), 'النقد المحصل بالشهر', d(46200000), 'مبيعات آجلة بالشهر:', m(8600000), 'الفواتير والأصناف بالشهر:', '840 فاتورة (20 صنفاً)', 'الوحدات المباعة بالشهر:', '680 طن • 9,400 كرتونة • 3,200 لوح'],
                        year: ['السنة ▲', 'إجمالي مبيعات السنة', d(642000000), 'صافي أرباح السنة', d(118400000, '+'), 'النقد المحصل بالسنة', d(585000000), 'مبيعات آجلة بالسنة:', m(57000000), 'الفواتير والأصناف بالسنة:', '4,820 فاتورة (20 صنفاً)', 'الوحدات المباعة بالسنة:', '7,850 طن • 112 ألف كرتونة • 38 ألف لوح'],
                        purchases: ['المشتريات 🛒', 'إجمالي مشتريات اليوم', d(1420000), 'خصم مكتسب ووثر شراء', d(85000, '+'), 'كمية المشتريات المستلمة', '45.0 طن + 600 جوال', 'مشتريات آجلة للموردين:', m(290000), 'فواتير الشراء والتوريد:', '12 فاتورة شراء (8 موردين)', 'مشتريات بالطريق (تحت الاستلام):', '40 طن سيخ 12مم • 60 طن أسمنت'],
                        warehouses: ['المخازن ⚖️', '🏪 المخزن الرئيسي (12 صنفاً) 🔍', `مبيعات: ${m(1150000)} | ربح: +${m(212000)}`, '🏬 المخزن الفرعي (8 أصناف) 🔍', `مبيعات: ${m(740000)} | ربح: +${m(136000)}`, '🏛️ قيمة بضاعة كل المخازن 🔍', d(176600000), 'إجمالي مبيعات المخازن اليوم:', m(1890000), 'صافي أرباح المخازن اليوم:', '+' + m(348000), 'عرض أصناف المخازن (كل الوحدات 🔍):', '20 صنفاً (وزن • ربطة • كرتونة • لوح • حجم)'],
                        strategy: ['العائد 🧠', '💎 العائد على الاستثمار (ROI)', '+24.6% سنوياً (ممتاز ▲)', '💱 أثر تقلب سعر الصرف', `+4.2% مكسب إحلال (${d(176600000)})`, '🛡️ حماية هامش الربح', '0 فاتورة خاسرة (أدنى ربح 14% ✓)', 'أعلى صنف عائداً للاستثمار (ROI):', 'سيخ 12مم السجانة (+28.4% عائد ▲)', '🔄 معدل دوران المخزون:', '2.4 دورة/شهر (كل 12 يوماً ▲)', 'تنبيه سعر الصرف والإحلال:', 'مغطى بالدولار 100% ضد التقلب ✓']
                    }[scope] || [];
                    if (bdg && map[0]) bdg.innerText = map[0];
                    const clk = scope === 'warehouses' ? `onclick="openFullCardModal('sales')" style="cursor:pointer" title="اضغط لاستعراض كافة أصناف المخزن بالوزن والربطة والكرتونة والحجم والمبيعات والأرباح"` : '';
                    mid.innerHTML = `<div class="inner-box p-3 hover:border-sky-400 transition" ${clk}><span class="text-xs text-slate-300 block mb-1">${map[1]} <b class="text-emerald-400">▲</b></span><div class="${scope==='warehouses'?'text-sm':'text-xl'} font-black text-white">${map[2]}</div></div><div class="inner-box p-3 hover:border-emerald-400 transition" ${clk}><span class="text-xs text-slate-300 block mb-1">${map[3]} <b class="text-emerald-400">▲</b></span><div class="${scope==='warehouses'?'text-sm':'text-xl'} font-black text-emerald-400">${map[4]}</div></div><div class="inner-box p-3 hover:border-amber-400 transition" ${clk}><span class="text-xs text-slate-300 block mb-1">${map[5]}</span><div class="text-xl font-black text-amber-400">${map[6]}</div></div>`;
                    bot.innerHTML = `<div class="inner-box p-2.5 flex items-center justify-between" ${clk}><span class="text-slate-300">${map[7]}</span><strong class="text-rose-400 font-bold">${map[8]}</strong></div><div class="inner-box p-2.5 flex items-center justify-between" ${clk}><span class="text-slate-300">${map[9]}</span><strong class="text-amber-300 font-bold">${map[10]}</strong></div><div class="inner-box p-2.5 flex items-center justify-between" ${clk}><span class="text-slate-300">${map[11]}</span><strong class="text-sky-300 font-bold">${map[12]}</strong></div>`;
                }
                function switchAuditCommandView(mode, btn) {
                    if (btn) _activateMiniTab(btn, mode === 'ai_audit' ? 'bg-amber-600' : 'bg-emerald-600');
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString() + ' ج.س');
                    const tb = document.getElementById('dash-audit-command-tbody');
                    if (!tb) return;
                    const rows = {
                        trial: [['صافي السيولة بالخزائن والبنوك', m(3450000), 'emerald', 'متطابق 100% ✓'], ['مخزون سيخ التسليح والأسمنت', '420.5 طن (56,767 سيخة)', 'amber', 'مضبوط وزنياً وعدداً ✓'], ['إجمالي ذمم العملاء (المدينون)', m(820000), 'sky', 'مؤكد بالقيود ✓'], ['التزامات الموردين (الدائنون)', m(290000), 'rose', 'مؤكد بالقيود ✓']],
                        pnl: [['إجمالي إيرادات المبيعات المعتمدة', m(54800000), 'sky', 'نمو +18.4% ▲'], ['تكلفة البضاعة المباعة (COGS)', m(43900000), 'amber', 'ضمن السقف المعياري ✓'], ['المصروفات التشغيلية والترحيل', m(1040000), 'rose', '1.9% من المبيعات ✓'], ['صافي الربح القابل للتوزيع', '+' + m(9860000), 'emerald', 'هامش صافي 18.0% ✓']],
                        balance: [['إجمالي الأصول المتداولة والمخزنية', m(180870000), 'emerald', 'مغطى بالكامل ✓'], ['إجمالي الالتزامات والشيكات الصادرة', m(556000), 'rose', '0.3% من الأصول ✓'], ['صافي حقوق الملكية والمركز المالي', m(180314000), 'sky', 'ملاءة ممتازة ✓'], ['مؤشر التوازن المحاسبي العام', '0 فروقات', 'emerald', 'ميزان مراجعة صفري ✓']],
                        ai_audit: [['فحص الفواتير والأسعار تحت التكلفة', '0 فاتورة خاسرة', 'emerald', 'حماية الربح 100% ✓'], ['فحص أرصدة الخزائن والبنوك اللحظي', '0 انحراف نقدي', 'emerald', 'مطابق للجرد الفعلي ✓'], ['مطابقة أوزان الميزان مع الفواتير', '420.5 طن متطابق', 'sky', 'بلا فاقد أو عجز ✓'], ['فحص القيود المزدوجة والترحيل الآلي', '100% مرحّل آلياً', 'amber', 'تدقيق ذكي مكتمل ✓']]
                    }[mode] || [];
                    tb.innerHTML = rows.map(r => `<tr><td class="py-3 font-bold text-white">${r[0]}</td><td class="py-3 font-extrabold text-${r[2]}-300">${r[1]}</td><td class="py-3"><span class="px-2 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 font-bold text-xs">${r[3]}</span></td></tr>`).join('');
                }
                function switchLiveRadarFeed(mode, btn) {
                    if (btn) _activateMiniTab(btn, 'bg-sky-600');
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString() + ' ج.س');
                    const feed = document.getElementById('radar-feed');
                    if (!feed) return;
                    const items = {
                        all: [
                            ['مؤسسة البنيان الحديثة', 'فاتورة بيع نقدي', 'emerald', 'الوزن: 1.0 طن | 135 سيخة تجاري | 9.0 شيلة', 420000, '1.0', '135', '9.0'],
                            ['توريد سيخ تسليح 16مم', 'سند مشتريات', 'amber', 'الوزن: 15.0 طن | مخزن السجانة الرئيسي', 5850000, '15.0', '2025', '135.0'],
                            ['شركة النيل للمقاولات', 'سند قبض بنكي', 'sky', 'تحصيل دفعة عبر بنك الخرطوم • مطابقة فورية', 1250000, '3.0', '405', '27.0']
                        ],
                        sales: [
                            ['مؤسسة البنيان الحديثة', 'فاتورة بيع نقدي', 'emerald', 'الوزن: 1.0 طن | ربح الفاتورة: +72,000 ج.س', 420000, '1.0', '135', '9.0'],
                            ['شركة النيل للمقاولات', 'فاتورة بيع آجل', 'emerald', 'الوزن: 5.0 طن | ربح الفاتورة: +340,000 ج.س', 2100000, '5.0', '675', '45.0']
                        ],
                        purchases: [
                            ['توريد سيخ تسليح 16مم', 'سند مشتريات', 'amber', 'الوزن: 15.0 طن | مخزن السجانة الرئيسي', 5850000, '15.0', '2025', '135.0'],
                            ['توريد أسمنت عطبرة بورتلاندي', 'إذن استلام مخزني', 'amber', 'الوزن: 30.0 طن | مخزن الصناعية', 5100000, '30.0', '600', '30.0']
                        ],
                        vouchers: [
                            ['شركة النيل للمقاولات', 'سند قبض بنكي', 'sky', 'بنك الخرطوم • إشعار تحصيل #8841', 1250000, '0', '0', '0'],
                            ['مصنع السجانة للحديد', 'سند صرف موردين', 'rose', 'سداد دفعة توريد عبر بنك فيصل', 650000, '0', '0', '0']
                        ]
                    }[mode] || [];
                    feed.innerHTML = items.map(x => `<div onclick="printReceipt('${x[0]}', '${x[1]}', '${x[5]}', '${x[6]}', '${x[7]}', '${x[4].toLocaleString()}')" class="inner-box p-3.5 hover:border-emerald-400 cursor-pointer transition flex items-center justify-between"><div><div class="font-bold text-white text-sm">${x[0]} <span class="text-${x[2]}-400">(${x[1]})</span></div><div class="text-xs text-slate-300">${x[3]}</div></div><div class="text-left"><div class="font-black text-${x[2]}-400 text-sm">${m(x[4])}</div><div class="text-[11px] text-slate-300">اضغط للطباعة 🖨️</div></div></div>`).join('');
                }
                function switchCenterChartCard(mode, btn) {
                    window._centerChartMode = mode;
                    if (btn) _activateMiniTab(btn, mode === 'stock_need' ? 'bg-rose-600' : (mode === 'donuts' ? 'bg-sky-600' : 'bg-emerald-600'));
                    const box = document.getElementById('dash-center-chart-body');
                    if (!box) return;
                    if (mode === 'donuts') {
                        const pIdx = window._execSumPeriodIdx || 0;
                        const pMeta = [
                            ['اليوم', '▲ +12.4% نمو', ['8ص','10ص','12ظ','2م','4م','6م','الآن'], [24,32,38,42,36,46,52]],
                            ['أسبوع', '▲ +18.4% نمو', ['سبت','أحد','إثن','ثلا','أرب','خمي','اليوم'], [26,34,30,40,36,46,52]],
                            ['10 أيام', '▲ +21.2% نمو', ['ي1','ي2','ي4','ي6','ي8','ي9','ي10'], [25,30,35,38,42,47,52]],
                            ['شهر', '▲ +24.6% نمو', ['أول','أسب1','أسب2','منتصف','أسب3','أسب4','اليوم'], [28,33,37,41,44,48,52]],
                            ['3 شهور', '▲ +28.5% نمو', ['ش1','منتصف','ش2','منتصف','ش3','أسب4','اليوم'], [26,31,36,40,45,49,52]],
                            ['6 شهور', '▲ +32.1% نمو', ['ش1','ش2','ش3','ش4','ش5','ش6','اليوم'], [24,29,34,39,43,48,52]],
                            ['9 شهور', '▲ +35.4% نمو', ['ش1','ش2','ش4','ش5','ش7','ش8','ش9'], [22,28,33,38,44,48,52]],
                            ['سنة', '▲ +38.5% نمو', ['ينا','مار','ماي','يول','سبت','نوف','الآن'], [24,30,35,40,44,49,52]]
                        ][pIdx] || ['اليوم', '▲ +12.4% نمو', ['8ص','10ص','12ظ','2م','4م','6م','الآن'], [24,32,38,42,36,46,52]];
                        const colClrs = ['bg-emerald-500/75','bg-sky-500/75','bg-emerald-500/80','bg-amber-500/85','bg-sky-500/85','bg-emerald-400','bg-emerald-400 shadow-lg'];
                        const colsHtml = pMeta[2].map((lbl, i) => `<div class="flex flex-col items-center gap-1"><div class="w-full rounded-t ${colClrs[i]}" style="height:${pMeta[3][i]}px"></div><span class="text-[10px] ${i===6?'text-emerald-300 font-black':'text-slate-300 font-bold'}">${lbl}</span></div>`).join('');
                        box.className = 'flex-1 flex flex-col justify-between gap-2.5 my-1 num-font';
                        box.innerHTML = `<div class="grid grid-cols-2 gap-2.5 items-center"><div class="inner-box p-2.5 flex flex-col items-center"><div class="relative w-24 h-24 flex items-center justify-center"><svg class="w-full h-full -rotate-90" viewBox="0 0 42 42"><circle cx="21" cy="21" r="15.915" fill="transparent" stroke="rgba(148,163,184,0.15)" stroke-width="5.2"/><circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#10b981" stroke-width="5.2" stroke-dasharray="58 42" stroke-dashoffset="0"/><circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#38bdf8" stroke-width="5.2" stroke-dasharray="28 72" stroke-dashoffset="-58"/></svg><div class="absolute text-center"><span class="text-sm font-black text-white block">100%</span><span class="text-xs font-bold text-emerald-300">السيولة</span></div></div><div class="flex flex-wrap justify-center gap-1.5 mt-1.5 text-xs font-black"><span class="text-emerald-400">●بنك 58%</span><span class="text-sky-400">●خزنة 28%</span></div></div><div class="inner-box p-2.5 flex flex-col items-center"><div class="relative w-24 h-24 flex items-center justify-center"><svg class="w-full h-full -rotate-90" viewBox="0 0 42 42"><circle cx="21" cy="21" r="15.915" fill="transparent" stroke="rgba(148,163,184,0.15)" stroke-width="5.2"/><circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#38bdf8" stroke-width="5.2" stroke-dasharray="70 30" stroke-dashoffset="0"/><circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#a855f7" stroke-width="5.2" stroke-dasharray="30 70" stroke-dashoffset="-70"/></svg><div class="absolute text-center"><span class="text-sm font-black text-white block">70%</span><span class="text-xs font-bold text-sky-300">نقدي</span></div></div><div class="flex flex-wrap justify-center gap-1.5 mt-1.5 text-xs font-black"><span class="text-sky-400">●نقد 70%</span><span class="text-purple-400">●آجل 30%</span></div></div></div><div class="inner-box p-3 flex flex-col justify-between gap-2 flex-1"><div class="flex items-center justify-between text-xs font-black"><span class="text-slate-200">📊 أعمدة وخطوط التدفق والربحية (${pMeta[0]})</span><span class="text-emerald-400">${pMeta[1]}</span></div><div class="grid grid-cols-7 gap-1.5 items-end h-14 pt-2 px-1 border-b border-white/10">${colsHtml}</div><div class="space-y-1.5 pt-0.5 text-xs font-black"><div><div class="flex justify-between mb-0.5"><span class="text-slate-300">🏦 سيولة البنوك والخزائن الجاهزة</span><span class="text-emerald-300">86%</span></div><div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden"><div class="h-full bg-emerald-500 rounded-full" style="width:86%"></div></div></div><div><div class="flex justify-between mb-0.5"><span class="text-slate-300">⚡ كفاءة التحصيل النقدي الفوري</span><span class="text-sky-300">78%</span></div><div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden"><div class="h-full bg-sky-500 rounded-full" style="width:78%"></div></div></div></div></div>`;
                        return;
                    }
                    box.className = 'flex-1 flex flex-col justify-between gap-2 my-1 num-font';
                    const bars = {
                        sales_share: [['حديد تسليح', '62% من المبيعات', 62, '#10b981'], ['أسمنت بورتلاندي', '21% من المبيعات', 21, '#0284c7'], ['قطاعات ومواسير', '11% من المبيعات', 11, '#f59e0b'], ['سلك رباط ومسامير', '6% من المبيعات', 6, '#8b5cf6'], ['كمر حديد وزوايا', '5% من المبيعات', 5, '#14b8a6']],
                        profit_share: [['حديد تسليح', '68% من صافي الربح', 68, '#10b981'], ['قطاعات ومواسير', '16% من صافي الربح', 16, '#0284c7'], ['أسمنت بورتلاندي', '10% من صافي الربح', 10, '#f59e0b'], ['سلك رباط ومسامير', '6% من صافي الربح', 6, '#a855f7'], ['صاج وكمر ثقيل', '5% من صافي الربح', 5, '#14b8a6']],
                        wh_share: [['المخزن الرئيسي', '58% (244 طن)', 58, '#10b981'], ['مخزن الصناعية', '27% (113.5 طن)', 27, '#0284c7'], ['المخزن الفرعي', '15% (63 طن)', 15, '#f59e0b'], ['بضاعة بالطريق (قيد الاستلام)', '100 طن إضافية', 42, '#38bdf8'], ['إجمالي كفاية المخازن', 'يكفي 45 يوماً ✓', 92, '#10b981']]
                    }[mode] || [];
                    box.innerHTML = bars.map(b => `<div class="inner-box px-3 py-2.5"><div class="flex justify-between items-center text-sm font-black mb-1"><span class="text-white truncate">${b[0]}</span><span style="color:#fde68a;">${b[1]}</span></div><div style="width:100%;height:8px;background:rgba(148,163,184,0.2);border-radius:99px;overflow:hidden;"><div style="width:${b[2]}%;height:100%;background:${b[3]};border-radius:99px;"></div></div></div>`).join('');
                }
                function switchItemsRadar(mode, btn) {
                    window._lastItemsRadarMode = mode;
                    if (btn) { window._lastItemsRadarBtn = btn; const colorMap = { top_sell: 'bg-emerald-600', low_sell: 'bg-rose-600', top_profit: 'bg-sky-600', low_profit: 'bg-amber-600', stock_alerts: 'bg-rose-600', stock_most: 'bg-emerald-600', stock_least: 'bg-sky-600', stock_days: 'bg-amber-600' }; _activateMiniTab(btn, colorMap[mode] || 'bg-emerald-600'); }
                    const titleEl = document.getElementById('dash-items-radar-title');
                    const titles = {
                        top_sell: 'الأصناف الـ 6 الأكثر مبيعاً وربحاً',
                        low_sell: 'الأصناف الـ 6 الأقل مبيعاً والأبطأ',
                        top_profit: 'الأصناف الـ 6 الأعلى ربحاً وعائداً',
                        low_profit: 'الأصناف الـ 6 الأقل ربحاً وعائداً',
                        stock_alerts: '🚨 الأصناف المحتاجة للطلب العاجل',
                        stock_most: '📦 الأصناف الـ 6 الأكثر وفرة بالمخزن',
                        stock_least: '📉 الأصناف الـ 6 الأقل كمية بالمخزن',
                        stock_days: '⏳ تحليل أيام الكفاية وسرعة السحب',
                        stock_transit: '🚚 البضاعة بالطريق والأصناف الراكدة'
                    };
                    if (titleEl && titles[mode]) titleEl.innerText = titles[mode];
                    const box = document.getElementById('dash-top-items-list');
                    if (!box) return;
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString());
                    const allData = {
                        top_sell: [
                            ['1. سيخ 12مم السجانة', `(${m(420000)} • 38%)`, '🟢 ▲ 185 طن', 'emerald'],
                            ['2. سيخ 16مم تسليح', `(${m(415000)} • 27%)`, '🟢 ▲ 120 طن', 'emerald'],
                            ['3. أسمنت عطبرة', `(${m(185000)} • 21%)`, '🟡 ▲ 95 طن', 'amber'],
                            ['4. سلك رباط مجلفن', `(${m(38000)} • 14%)`, '🟡 ▲ 310 لفة', 'amber'],
                            ['5. قطاعات ومواسير', `(${m(510000)} • 11%)`, '🟢 ▲ 64 طن', 'emerald'],
                            ['6. كمر حديد H-Beam', `(${m(450000)} • 9%)`, '🟡 ▲ 42 طن', 'amber'],
                            ['7. زوايا حديد 50×50', `(${m(440000)} • 7%)`, '🟡 ▲ 35 طن', 'amber'],
                            ['8. صاج أسود 3مم', `(${m(460000)} • 5%)`, '🟢 ▲ 28 طن', 'emerald']
                        ],
                        low_sell: [
                            ['1. زوايا حديد 50مم', '(19 يوماً • 14ط)', '🔴 ▼ دوران 3%', 'rose'],
                            ['2. صاج أسود 3مم', '(14 يوماً • 9ط)', '🔴 ▼ دوران 5%', 'rose'],
                            ['3. مواسير 40×40', '(11 يوماً • 12ط)', '🟡 ▼ دوران 7%', 'amber'],
                            ['4. كمر H-Beam', '(9 أيام • 6ط)', '🟡 ▼ دوران 8%', 'amber'],
                            ['5. شبك حديد ملحوم', '(8 أيام • 60ق)', '🟡 ▼ دوران 9%', 'amber'],
                            ['6. مسامير فولاذية', '(7 أيام • 25ك)', '🟢 ▼ دوران 11%', 'emerald'],
                            ['7. سيخ 8مم لفات', '(6 أيام • 8ط)', '🟢 ▼ دوران 12%', 'emerald']
                        ],
                        top_profit: [
                            ['1. سيخ 16مم تسليح', `(ربح: ${m(99600)})`, '🟢 ▲ +24% ربح', 'emerald'],
                            ['2. سيخ 12مم السجانة', `(ربح: ${m(90300)})`, '🟢 ▲ +21.5% ربح', 'emerald'],
                            ['3. سلك رباط مجلفن', `(ربح: ${m(7600)})`, '🟡 ▲ +20% ربح', 'amber'],
                            ['4. قطاعات ومواسير', `(ربح: ${m(91800)})`, '🟡 ▲ +18% ربح', 'amber'],
                            ['5. كمر حديد H-Beam', `(ربح: ${m(76500)})`, '🟢 ▲ +17% ربح', 'emerald'],
                            ['6. زوايا حديد 50مم', `(ربح: ${m(68000)})`, '🟡 ▲ +15.5% ربح', 'amber'],
                            ['7. صاج أسود 3مم', `(ربح: ${m(52000)})`, '🟡 ▲ +12% ربح', 'amber']
                        ],
                        low_profit: [
                            ['1. أسمنت عطبرة', `(ربح: ${m(14800)})`, '🔴 ▼ +8% ربح', 'rose'],
                            ['2. سيخ 8مم تجاري', `(ربح: ${m(38000)})`, '🔴 ▼ +9.5% ربح', 'rose'],
                            ['3. شبك حديد ملحوم', `(ربح: ${m(4200)})`, '🟡 ▼ +11% ربح', 'amber'],
                            ['4. مسامير صلب', `(ربح: ${m(2100)})`, '🟡 ▼ +11.5% ربح', 'amber'],
                            ['5. صاج أسود 3مم', `(ربح: ${m(52000)})`, '🟡 ▼ +12% ربح', 'amber'],
                            ['6. مواسير 40×40', `(ربح: ${m(1800)})`, '🟢 ▼ +12.5% ربح', 'emerald'],
                            ['7. زوايا حديد 50مم', `(ربح: ${m(68000)})`, '🟢 ▼ +15.5% ربح', 'emerald']
                        ],
                        stock_alerts: [
                            ['1. سيخ 4 لينية 12مم', '(بالمخزن 50 طن • مبيعات يومين فقط!)', '🔴 اطلب 100 طن', 'rose'],
                            ['2. سلك رباط مجلفن ناعم', '(بالمخزن 12 لفة • يكفي 3 أيام)', '🔴 طلب فوري', 'rose'],
                            ['3. أسمنت بورتلاندي عطبرة', '(بالمخزن 35 طن • يكفي 4 أيام)', '🟡 اطلب 50 طن', 'amber'],
                            ['4. مواسير مربعة 40×40', '(بالمخزن 15 طن • يكفي 7 أيام)', '🟡 اطلب 25 طن', 'amber'],
                            ['5. سيخ 5 لينية 16مم', '(بالمخزن 140 طن • يكفي 14 يوماً)', '🟢 آمن حالياً ✓', 'emerald'],
                            ['6. سيخ 3 لينية 10مم', '(بالمخزن 5 طن • يباع في شهرين)', '🟢 لا تطلب ✓', 'emerald']
                        ],
                        stock_most: [
                            ['1. سيخ 5 لينية 16مم', '(بالمخزن: 140 طن • 33% من المخزن)', '🟢 يكفي 14 يوماً', 'emerald'],
                            ['2. أسمنت بورتلاندي عطبرة', '(بالمخزن: 110 طن • 26% من المخزن)', '🟢 يكفي 11 يوماً', 'emerald'],
                            ['3. سيخ 4 لينية 12مم', '(بالمخزن: 50 طن • 12% • سحب 25ط/يوم)', '🔴 يكفي يومين!', 'rose'],
                            ['4. قطاعات ومواسير حديد', '(بالمخزن: 45 طن • 11% من المخزن)', '🟢 يكفي 18 يوماً', 'emerald'],
                            ['5. كمر حديد H-Beam ثقيل', '(بالمخزن: 32 طن • 8% من المخزن)', '🟢 يكفي 24 يوماً', 'emerald'],
                            ['6. شبك حديد ملحوم', '(بالمخزن: 24 طن • 5.5% من المخزن)', '🟢 يكفي 30 يوماً', 'emerald']
                        ],
                        stock_least: [
                            ['1. سيخ 3 لينية 10مم', '(بالمخزن: 5 طن فقط • يباع في شهرين)', '🟢 مكتفٍ 60 يوماً', 'emerald'],
                            ['2. سلك رباط مجلفن', '(بالمخزن: 6 طن • سحب يومي عالٍ)', '🔴 يكفي 3 أيام', 'rose'],
                            ['3. صاج أسود 3مم ثقيل', '(بالمخزن: 9 طن • سحب بطيء)', '🟢 يكفي 45 يوماً', 'emerald'],
                            ['4. زوايا حديد 50×50مم', '(بالمخزن: 12 طن • سحب هادئ)', '🟢 يكفي 40 يوماً', 'emerald'],
                            ['5. مواسير مربعة 40×40', '(بالمخزن: 15 طن • سحب متوسط)', '🟡 يكفي 7 أيام', 'amber'],
                            ['6. مسامير صلب فولاذية', '(بالمخزن: 18 كرتونة • سحب معتدل)', '🟢 يكفي 25 يوماً', 'emerald']
                        ],
                        stock_days: [
                            ['1. سيخ 4 لينية 12مم', '(رصيد 50 طن ÷ بيع 25 طن/يوم)', '🔴 تغطية يومين', 'rose'],
                            ['2. سلك رباط مجلفن', '(رصيد 6 طن ÷ بيع 2 طن/يوم)', '🔴 تغطية 3 أيام', 'rose'],
                            ['3. أسمنت بورتلاندي عطبرة', '(رصيد 35 طن ÷ بيع 9 طن/يوم)', '🟡 تغطية 4 أيام', 'amber'],
                            ['4. مواسير مربعة 40×40', '(رصيد 15 طن ÷ بيع 2.1 طن/يوم)', '🟡 تغطية 7 أيام', 'amber'],
                            ['5. سيخ 5 لينية 16مم', '(رصيد 140 طن ÷ بيع 10 طن/يوم)', '🟢 تغطية 14 يوماً', 'emerald'],
                            ['6. سيخ 3 لينية 10مم', '(رصيد 5 طن ÷ بيع 2.5 طن/شهر)', '🟢 تغطية 60 يوماً', 'emerald']
                        ],
                        stock_transit: [
                            ['1. سيخ 4 لينية 12مم (بالطريق)', '(شحنة 40 طن من مصنع السجانة)', '🚚 تصل اليوم', 'emerald'],
                            ['2. أسمنت عطبرة (بالطريق)', '(شحنة 60 طن • إذن #1089)', '🚚 تصل غداً', 'sky'],
                            ['3. سلك رباط مجلفن (بالطريق)', '(شحنة 150 لفة • الميناء)', '🟡 بعد يومين', 'amber'],
                            ['4. زوايا 50مم (سيولة مجمدة)', `(راكد 14 طن • مجمد ${m(6160000)})`, '❄️ راكد 19 يوماً', 'rose'],
                            ['5. صاج أسود 3مم (سيولة مجمدة)', `(راكد 9 طن • مجمد ${m(4140000)})`, '❄️ راكد 14 يوماً', 'rose'],
                            ['6. مواسير 40×40 (سيولة مجمدة)', `(راكد 12 طن • مجمد ${m(5400000)})`, '🟡 راكد 11 يوماً', 'amber']
                        ]
                    };
                    window._fullItemsRadarData = allData[mode] || [];
                    const data = window._fullItemsRadarData.slice(0, 6);
                    box.innerHTML = data.map(r => `<div class="inner-box py-2 px-3 flex items-center justify-between gap-1.5"><span class="font-black text-white truncate" style="font-size:15px!important;">${r[0]} <small class="font-extrabold" style="color:#fde68a;font-size:13.5px!important;">${r[1]}</small></span><span class="px-2 py-0.5 rounded-md bg-${r[3]}-500/20 text-${r[3]}-300 font-black shrink-0" style="width:116px;font-size:13.5px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:flex-start;gap:3px;">${r[2]}</span></div>`).join('');
                }

                function switchForecastRadar(mode, btn) {
                    window._lastForecastMode = mode;
                    if (btn) { window._lastForecastBtn = btn; _activateMiniTab(btn, mode === 'vital_alerts' ? 'bg-rose-600' : 'bg-emerald-600'); }
                    const fTitle = document.getElementById('dash-forecast-radar-title');
                    const fTitles = { liquidity: 'التنبؤ الذكي والتنبيهات الحيوية', items: 'التنبؤ الذكي للأصناف والمخزون', suppliers: 'التنبؤ الذكي للموردين والأسعار', customers: 'التنبؤ الذكي لطلبات العملاء', vital_alerts: '🚨 المنبهات الحيوية والتنبيهات' };
                    if (fTitle && fTitles[mode]) fTitle.innerText = fTitles[mode];
                    const box = document.getElementById('dash-forecast-list');
                    if (!box) return;
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString() + ' ج.س');
                    const views = {
                        liquidity: [
                            ['📈 سيولة متوقعة (30 يوماً):', `▲ ${m(4820000)}`, 'emerald'],
                            ['🛡️ فحص الانحرافات السعرية:', '0 انحراف (آمن 100% ✓)', 'sky'],
                            ['⚡ تغطية الالتزامات والتعادل:', 'مغطى بـ 11.8 ضعفاً ▲', 'amber'],
                            ['📦 كفاية المخزون الاستراتيجي:', 'يكفي 45 يوماً (آمن ✓)', 'emerald'],
                            ['🎯 تحصيل الديون المتوقع (7 أيام):', `+${m(640000)} (78% ▲)`, 'sky']
                        ],
                        items: [
                            ['🏗️ كفاية سيخ 12مم السجانة:', '14 يوماً (اطلب 50 طن)', 'amber'],
                            ['📦 طلب متوقع الأسبوع القادم:', '▲ +22% على سيخ 16مم', 'emerald'],
                            ['❄️ أصناف بطيئة الحركة:', 'صاج 3مم وزوايا 50مم', 'rose'],
                            ['🚚 شحنات بالطريق للمخازن:', '100 طن تصل خلال 48 ساعة', 'sky'],
                            ['⚖️ دقة أوزان الميزان والمطابقة:', '100% بدون فاقد مخزني ✓', 'emerald']
                        ],
                        suppliers: [
                            ['🛒 إجمالي وكمية مشتريات الشهر:', `485 طن (${m(41200000)}) ▲`, 'emerald'],
                            ['🚚 أفضل مورد سعراً وخصم كمية:', 'مصنع السجانة (خصم 3.5% ✓)', 'sky'],
                            ['📈 توقع أسعار الشراء الأسبوع القادم:', '▲ ارتفاع +2.5% (اشترِ الآن)', 'amber'],
                            ['⏳ التزام الموردين بمواعيد التوريد:', '96% انضباط كامل ✓', 'emerald'],
                            ['🛡️ تغطية مستحقات الموردين:', 'مغطاة بالسيولة 11.8x ✓', 'sky']
                        ],
                        customers: [
                            ['🔮 طلبية متوقعة (24 ساعة):', `البنيان (15ط • ${m(6300000)})`, 'emerald'],
                            ['💎 الأعلى ربحية هذا الشهر:', `النيل للمقاولات (${m(2450000)})`, 'sky'],
                            ['🧾 شيكات مستحقة التحصيل غداً:', `مغلق الوادي (${m(1850000)})`, 'amber'],
                            ['🔔 انضباط دوريات الشراء:', '85% من العملاء منتظمون ✓', 'emerald'],
                            ['⚠️ متابعة تحصيل المتأخرات:', `عميلان (${m(430000)})`, 'rose']
                        ],
                        vital_alerts: [
                            ['🚨 مخزون دون حد الطلب:', 'سلك رباط وأسمنت (يكفي 3 أيام)', 'rose'],
                            ['⚠️ اقتراب سقف ائتمان (94%):', `مغلق السجانة (${m(235000)})`, 'amber'],
                            ['💱 حماية السعر والربح والإحلال:', '0 بيع بخسارة • فائض إحلال +4.2% ✓', 'emerald'],
                            ['📦 أذونات استلام قيد المطابقة:', 'إذن #1084 (20 طن سيخ 12مم)', 'sky'],
                            ['🛡️ أمان التدفق النقدي التشغيلي:', 'مؤمن بالكامل لـ 60 يوماً ✓', 'emerald']
                        ]
                    }[mode] || [];
                    box.innerHTML = views.map(r => `<div class="inner-box py-2 px-3 flex items-center justify-between gap-2"><span class="text-slate-200 font-black truncate" style="font-size:15px!important;">${r[0]}</span><strong class="text-${r[2]}-400 font-black shrink-0" style="font-size:15px!important;">${r[1]}</strong></div>`).join('');
                }

                window._debtAgingMode = 'aging';
                function toggleDebtAgingMode() {
                    const order = ['aging', 'limits', 'profit_checks', 'checks'];
                    const idx = (order.indexOf(window._debtAgingMode) + 1) % order.length;
                    window._debtAgingMode = order[idx];
                    const titleEl = document.getElementById('dash-debt-aging-title');
                    const btnEl = document.getElementById('dash-debt-mode-toggle');
                    if (window._debtAgingMode === 'limits') {
                        if (titleEl) titleEl.innerText = '🛡️ السقوفات الائتمانية للعملاء';
                        if (btnEl) btnEl.innerText = '⇄ صافي الربحية';
                        switchDebtView('limits', null);
                    } else if (window._debtAgingMode === 'profit_checks') {
                        if (titleEl) titleEl.innerText = '💎 صافي الربحية';
                        if (btnEl) btnEl.innerText = '⇄ الشيكات';
                        switchDebtView('profit_checks', null);
                    } else if (window._debtAgingMode === 'checks') {
                        if (titleEl) titleEl.innerText = '🧾 الشيكات المستحقة والمرتدة';
                        if (btnEl) btnEl.innerText = '⇄ أعمار الديون';
                        switchDebtView('checks', null);
                    } else {
                        if (titleEl) titleEl.innerText = '⏳ أعمار الديون والسداد';
                        if (btnEl) btnEl.innerText = '⇄ سقف الائتمان';
                        switchDebtView('aging', null);
                    }
                }
                window._debtGroupMode = 'cust';
                function toggleDebtGroup() {
                    window._debtGroupMode = window._debtGroupMode === 'supp' ? 'cust' : 'supp';
                    const cRow = document.getElementById('dash-debt-tabs-row');
                    const sRow = document.getElementById('dash-debt-tabs-supp');
                    const btn = document.getElementById('dash-debt-group-toggle');
                    const title = document.getElementById('dash-debt-aging-title');
                    if (window._debtGroupMode === 'supp') {
                        if (cRow) cRow.style.display = 'none';
                        if (sRow) sRow.style.display = 'grid';
                        if (btn) btn.innerText = '⇄ العملاء';
                        if (title) title.innerText = '⏳ مديونية وسقوفات وأرباح الموردين';
                        switchDebtView('supp_aging', sRow ? sRow.querySelector('button') : null);
                    } else {
                        if (cRow) cRow.style.display = 'grid';
                        if (sRow) sRow.style.display = 'none';
                        if (btn) btn.innerText = '⇄ الموردين';
                        if (title) title.innerText = '⏳ سجل الديون والسقوفات والأرباح';
                        switchDebtView('aging', cRow ? cRow.querySelector('button') : null);
                    }
                }
                function switchDebtView(mode, btn) {
                    window._debtAgingMode = mode;
                    if (btn) {
                        window._lastDebtBtn = btn;
                        _activateMiniTab(btn, (mode === 'aging' || mode === 'supp_aging') ? 'bg-rose-600' : ((mode === 'profit_checks' || mode === 'supp_profit') ? 'bg-emerald-600' : ((mode === 'checks' || mode === 'supp_checks') ? 'bg-sky-600' : 'bg-amber-600')));
                    }
                    const box = document.getElementById('dash-debt-aging-box');
                    if (!box) return;
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString() + ' ج.س');
                    const wTxt = 'color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;';
                    const bTxt = 'color:#000000!important;-webkit-text-fill-color:#000000!important;';
                    const waSvg = `<svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg>`;
                    window._waIconSvg = waSvg;
                    const renderRows = (arr) => arr.map(r => {
                        const c = r[4];
                        const cardCls = c === 'black' ? 'border border-white/30" style="background:rgba(2,6,23,0.92);' : `bg-${c}-500/15 border border-${c}-500/40`;
                        const pillStyle = c === 'black' ? `width:68px;font-size:14px!important;text-align:center;display:inline-flex;align-items:center;justify-content:center;background:#020617;border:1px solid #fff;${wTxt}` : (c === 'amber' ? `width:68px;font-size:14px!important;text-align:center;display:inline-flex;align-items:center;justify-content:center;${bTxt}` : `width:68px;font-size:14px!important;text-align:center;display:inline-flex;align-items:center;justify-content:center;${wTxt}`);
                        const pillCls = c === 'black' ? 'px-2 py-1 rounded-lg font-black' : (c === 'amber' ? 'px-2 py-1 rounded-lg bg-amber-500 font-black' : `px-2 py-1 rounded-lg bg-${c}-600 text-white font-black`);
                        const valCls = c === 'black' ? 'text-white' : (c === 'amber' ? 'text-amber-300' : `text-${c}-400`);
                        const safeN = r[0].replace(/'/g, '');
                        const safeSub = (r[1].replace(/<[^>]*>/g, '') + ' • ' + r[3]).replace(/'/g, '');
                        return `<div class="flex items-center justify-between py-2.5 px-3.5 rounded-xl ${cardCls} gap-2"><div class="min-w-0 flex-1"><span class="font-black text-white text-sm block truncate">${r[0]}</span><span class="text-xs font-bold block truncate" style="color:#fde68a;">${r[1]}</span></div><div class="flex items-center gap-1.5 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('${safeN}', '${safeSub}')" title="مراسلة وتنبيه ذكي بالواتساب" class="wa-svg-btn">${waSvg}</button><span class="${pillCls}" style="${pillStyle}">${r[2]}</span><strong class="${valCls} font-black text-sm shrink-0" style="width:118px;text-align:left;display:inline-block;white-space:nowrap;">${r[3]}</strong></div></div>`;
                    }).join('');
                    if (mode === 'supp_prices') {
                        box.innerHTML = renderRows([
                            ['🟢 1. سيخ 4 لينية 12مم (أقل سعر مورد)', `مصنع السجانة: ${m(330000)}/طن ⬅️ أوفر بـ ${m(12000)} عن السوق`, 'الأوفر ✓', m(330000), 'emerald'],
                            ['🟢 2. سيخ 5 لينية 16مم (أقل سعر مورد)', `مصنع السجانة: ${m(325000)}/طن ⬅️ خصم كمية 3.5%`, 'الأوفر ✓', m(325000), 'emerald'],
                            ['🟢 3. أسمنت بورتلاندي (أفضل عرض)', `شركة أسمنت عطبرة: ${m(164000)}/طن ⬅️ توريد فوري`, 'الأوفر ✓', m(164000), 'emerald'],
                            ['🟡 4. قطاعات ومواسير 40×40', `الدولية للقطاعات: ${m(418000)}/طن ⬅️ توقع ارتفاع +2%`, 'ثابت', m(418000), 'amber'],
                            ['🟡 5. سلك رباط مجلفن ناعم', `مصنع الأسلاك: ${m(30400)}/لفة ⬅️ ينصح بشراء 150 لفة`, 'اطلب الآن', m(30400), 'amber'],
                            ['🔴 6. صاج أسود 3مم (تفاوت سعري)', `مستودع الشرق: ${m(408000)}/طن ⬅️ أعلى بـ 4% من البديل`, 'مرتفع ▲', m(408000), 'rose']
                        ]);
                    } else if (mode === 'supp_aging') {
                        box.innerHTML = renderRows([
                            ['🟢 1. مصنع السجانة للحديد (سداد منتظم)', 'المعتاد: سداد كل 15 يوماً ⬅️ متبقٍ 9 أيام', '9 أيام', m(145000), 'emerald'],
                            ['🟢 2. شركة أسمنت عطبرة (ضمن المهلة)', 'المعتاد: سداد كل 10 أيام ⬅️ متبقٍ 5 أيام', '5 أيام', m(68000), 'emerald'],
                            ['🟡 3. الدولية للقطاعات والمواسير (قرب الاستحقاق)', 'المعتاد: كل 10 أيام ⬅️ يستحق بعد يومين', 'يومان', m(42000), 'amber'],
                            ['🟡 4. مصنع الأسلاك المجلفنة (قرب الاستحقاق)', 'المعتاد: كل 7 أيام ⬅️ يستحق غداً', '1 يوم', m(19000), 'amber'],
                            ['🔴 5. مستودع الشرق للصاج (تأخر سداد للمورد)', 'المعتاد: كل 7 أيام ⬅️ تأخرنا 3 أيام', '+3 أيام', m(11000), 'rose'],
                            ['⬛ 6. وكالة البحر الأحمر (مطالبة سداد عاجلة)', 'المعتاد: كل 5 أيام ⬅️ تأخرنا 6 أيام', '+6 أيام', m(5000), 'black']
                        ]);
                    } else if (mode === 'supp_limits') {
                        box.innerHTML = [
                            ['🟢 1. مصنع السجانة للحديد', 145000, 500000, 29, 'emerald'],
                            ['🟢 2. شركة أسمنت عطبرة', 68000, 250000, 27, 'emerald'],
                            ['🟡 3. الدولية للقطاعات والمواسير', 42000, 80000, 52, 'amber'],
                            ['🟡 4. مصنع الأسلاك المجلفنة', 19000, 30000, 63, 'amber'],
                            ['🔴 5. مستودع الشرق للصاج', 11000, 12000, 92, 'rose']
                        ].map(L => `<div class="p-2 rounded-lg bg-${L[4]}-500/15 border border-${L[4]}-500/40"><div class="flex justify-between text-sm mb-1"><span class="font-black text-white">${L[0]}</span><span class="font-extrabold text-${L[4]}-300">${m(L[1])} / ${m(L[2])} (${L[3]}% مستخدم)</span></div><div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden"><div class="bg-${L[4]}-500 h-full rounded-full" style="width:${L[3]}%"></div></div></div>`).join('');
                    } else if (mode === 'supp_profit') {
                        box.innerHTML = renderRows([
                            ['🟢 1. مصنع السجانة للحديد (خصم 3.5% من الفاتورة)', `اشترينا منه بـ ${m(92000000)} ⬅️ خَصم لنا من حسابه (ربح صافٍ لنا):`, '3.5%', '+' + m(3220000), 'emerald'],
                            ['🟢 2. شركة أسمنت عطبرة (خصم 2.8% من الفاتورة)', `اشترينا منه بـ ${m(28500000)} ⬅️ خَصم لنا من حسابه (ربح صافٍ لنا):`, '2.8%', '+' + m(798000), 'emerald'],
                            ['🟡 3. الدولية للقطاعات (خصم 2.2% من الفاتورة)', `اشترينا منه بـ ${m(16200000)} ⬅️ خَصم لنا من حسابه (ربح صافٍ لنا):`, '2.2%', '+' + m(356400), 'amber'],
                            ['🟡 4. مصنع الأسلاك المجلفنة (خصم 1.8% من الفاتورة)', `اشترينا منه بـ ${m(6800000)} ⬅️ خَصم لنا من حسابه (ربح صافٍ لنا):`, '1.8%', '+' + m(122400), 'amber'],
                            ['🔴 5. مستودع الشرق للصاج (خصم 1.0% فقط)', `اشترينا منه بـ ${m(4900000)} ⬅️ خَصم لنا من حسابه (ربح صافٍ لنا):`, '1.0%', '+' + m(49000), 'rose'],
                            ['⬛ 6. شركة الشمال للتوريدات (خصم 0.5% فقط)', `اشترينا منه بـ ${m(1900000)} ⬅️ خَصم لنا من حسابه (ربح صافٍ لنا):`, '0.5%', '+' + m(9500), 'black']
                        ]);
                    } else if (mode === 'supp_checks') {
                        box.innerHTML = renderRows([
                            ['🟢 1. مصنع السجانة للحديد (شيك صادر #5510)', 'بنك الخرطوم • مغطى بالكامل ويُصرف بعد 5 أيام', '5 أيام', m(145000), 'emerald'],
                            ['🟢 2. شركة أسمنت عطبرة (شيك صادر #5514)', 'بنك الخرطوم • مغطى بالكامل ويُصرف بعد 4 أيام', '4 أيام', m(68000), 'emerald'],
                            ['🟡 3. الدولية للقطاعات (شيك صادر #5519)', 'بنك أم درمان • يستحق الصرف بعد يومين', 'يومان', m(42000), 'amber'],
                            ['🔴 4. مستودع الشرق للصاج (شيك #5522 غداً)', 'بنك النيلين • يستحق الصرف غداً صباحاً', 'غداً', m(11000), 'rose'],
                            ['⬛ 5. وكالة البحر الأحمر (تحويل معلق)', 'بانتظار مطابقة فاتورة استلام الميزان', 'معلق', m(5000), 'black']
                        ]);
                    } else if (mode === 'checks') {
                        box.innerHTML = renderRows([
                            ['🟢 1. مؤسسة البنيان الحديثة (شيك #78102)', 'بنك الخرطوم • مستحق التحصيل اليوم (مغطى 100%)', 'اليوم', m(2400000), 'emerald'],
                            ['🟢 2. مغلق الوادي للحديد (شيك #44821)', 'بنك الخرطوم • مستحق التحصيل غداً (آمن ✓)', '1 يوم', m(1850000), 'emerald'],
                            ['🟡 3. مقاولات السلام السكنية (شيك #90114)', 'بنك أم درمان الوطني • يستحق بعد 3 أيام', '3 أيام', m(1200000), 'amber'],
                            ['🟡 4. شركة النيل للمقاولات (شيك #90412)', 'بنك فيصل الإسلامي • يستحق بعد 5 أيام', '5 أيام', m(950000), 'amber'],
                            ['🔴 5. مغلق السجانة الهندسي (شيك #31098 مؤجل!)', 'بنك النيلين • طلب العميل تأجيل الصرف 4 أيام', 'مؤجل', m(650000), 'rose'],
                            ['⬛ 6. ورشة الأمانة للحدادة (شيك #11204 مرتد!)', 'شيك راجع منذ 4 أيام • تم إيقاف البيع الآجل مؤقتاً', 'مرتد', m(195000), 'black']
                        ]);
                    } else if (mode === 'profit_checks') {
                        box.innerHTML = renderRows([
                            ['🟢 1. مؤسسة البنيان الحديثة (أعلى ربح صافي)', `مبيعات العميل: ${m(18400000)} • هامش الربح: 22.4%`, '22.4%', '+' + m(3120000), 'emerald'],
                            ['🟢 2. شركة النيل للمقاولات (ربحية ممتازة)', `مبيعات العميل: ${m(12600000)} • هامش الربح: 20.8%`, '20.8%', '+' + m(2450000), 'emerald'],
                            ['🟡 3. مغلق الوادي للحديد (ربحية جيدة)', `مبيعات العميل: ${m(9200000)} • هامش الربح: 16.5%`, '16.5%', '+' + m(1518000), 'amber'],
                            ['🟡 4. مقاولات أبراج الخرطوم (ربحية متوسطة)', `مبيعات العميل: ${m(7500000)} • هامش الربح: 15.4%`, '15.4%', '+' + m(1155000), 'amber'],
                            ['🔴 5. مغلق السجانة الهندسي (ربحية منخفضة)', `مبيعات العميل: ${m(8900000)} • هامش الربح: 6.2% فقط`, '6.2%', '+' + m(551000), 'rose'],
                            ['⬛ 6. ورشة الأمانة للحدادة (أدنى صافي ربح)', `مبيعات العميل: ${m(2400000)} • هامش الربح: 4.1% فقط`, '4.1%', '+' + m(98400), 'black']
                        ]);
                    } else if (mode === 'limits') {
                        box.innerHTML = [
                            ['🔴 1. مغلق السجانة الهندسي', 235000, 250000, 94, 'rose'],
                            ['🔴 2. مؤسسة البنيان الحديثة', 420000, 500000, 84, 'rose'],
                            ['🟡 3. مقاولات أبراج الخرطوم', 195000, 250000, 78, 'amber'],
                            ['🟡 4. مغلق الوادي للحديد', 140000, 220000, 64, 'amber'],
                            ['🟢 5. مقاولات السلام السكنية', 110000, 300000, 37, 'emerald']
                        ].map(L => `<div class="p-2 rounded-lg bg-${L[4]}-500/15 border border-${L[4]}-500/40"><div class="flex justify-between text-sm mb-1"><span class="font-black text-white">${L[0]}</span><span class="font-extrabold text-${L[4]}-300">${m(L[1])} / ${m(L[2])} (${L[3]}%)</span></div><div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden"><div class="bg-${L[4]}-500 h-full rounded-full" style="width:${L[3]}%"></div></div></div>`).join('');
                    } else {
                        box.innerHTML = renderRows([
                            ['🟢 1. مقاولات السلام السكنية (منتظم تماماً)', 'المعتاد: يسدد كل <b>10 أيام</b> ⬅️ مرّ <b>3 أيام</b> (آمن 100%)', '3 أيام', m(110000), 'emerald'],
                            ['🟢 2. شركة النيل للمقاولات (منتظم تماماً)', 'المعتاد: يسدد كل <b>15 يوماً</b> ⬅️ مرّ <b>4 أيام</b> (آمن 100%)', '4 أيام', m(165000), 'emerald'],
                            ['🟡 3. مؤسسة البنيان الحديثة (اقتراب الاستحقاق)', 'المعتاد: يسدد كل <b>7 أيام</b> ⬅️ مرّ <b>8 أيام</b> (تأخير +1 يوم)', '8 أيام', m(420000), 'amber'],
                            ['🟡 4. مغلق الوادي للحديد (اقتراب الاستحقاق)', 'المعتاد: يسدد كل <b>7 أيام</b> ⬅️ مرّ <b>9 أيام</b> (تأخير +2 يوم)', '9 أيام', m(140000), 'amber'],
                            ['🔴 5. مغلق السجانة الهندسي (تأخر سداد!)', 'المعتاد: يسدد كل <b>5 أيام</b> ⬅️ مرّ <b>10 أيام</b> (تأخير +5 أيام)', '10 أيام', m(235000), 'rose'],
                            ['⬛ 6. ورشة الأمانة للحدادة (تجاوز 10 أيام!)', 'المعتاد: يسدد كل <b>5 أيام</b> ⬅️ مرّ <b>16 يوماً</b> (تأخير +11 يوماً)', '16 يوماً', m(195000), 'black']
                        ]);
                    }
                }

                function openFullCardModal(section) {
                    let modal = document.getElementById('partner-items-modal');
                    if (!modal) {
                        modal = document.createElement('div');
                        modal.id = 'partner-items-modal';
                        modal.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(2,6,23,0.82);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;padding:16px;direction:rtl;';
                        document.body.appendChild(modal);
                    }
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString() + ' ج.س');
                    const titles = {
                        vaults: '🏦 كشف الخزائن والحسابات البنكية والشيكات تحت التحصيل بالكامل',
                        sales_stock: '📈 التحليل التفصيلي للمبيعات وصافي الأرباح ومركز أوزان المخازن',
                        items: (document.getElementById('dash-items-radar-title')?.innerText || 'كشف الأصناف').replace('الـ 6', 'الـ 20 (القائمة الكاملة)'),
                        partners: (document.getElementById('dash-partners-radar-title')?.innerText || 'كشف الشركات والعملاء') + ' (20 شركة بالكامل)',
                        debt: (document.getElementById('dash-debt-aging-title')?.innerText || 'أعمار الديون والسقوفات') + ' (20 عميلاً بالكامل)',
                        donuts: '🍩 الكعكات المفرغة للسيولة والمبيعات (تحليل 360°)',
                        forecast: '🤖 التنبؤ الذكي الشامل (السيولة • الأصناف • الموردين)',
                        audit: '⚖️ غرفة القيادة وميزان المطابقة والتدقيق الشامل',
                        live_radar: '📡 الرادار المحاسبي الحي والأذونات والتنبيهات',
                        cashflow: '◎ مؤشرات التدفق النقدي والمبيعات والمخزون'
                    };
                    let bodyHtml = '';
                    if (section === 'vaults') {
                        const vRows = [
                            ['💵 1. الخزينة الرئيسية - الإدارة العامة', 'نقدية جاهزة ومطابقة للجرد الفعلي اليومي', 'مطابق ✓', m(1100000), 'emerald'],
                            ['💵 2. خزينة مبيعات المغلق الرئيسي', 'متحصلات مبيعات اليوم النقدية المباشرة', 'نشط الآن', m(350000), 'emerald'],
                            ['💵 3. خزينة الفرع والمخزن الفرعي', 'متحصلات الفرع النقدية وعهدة التشغيل', 'مطابق ✓', m(180000), 'emerald'],
                            ['🏦 4. بنك الخرطوم (حساب بنكك الرئيسي)', 'تحويلات بنكك الفورية والشيكات المقاصة', 'متاح فوري', m(1250000), 'sky'],
                            ['🏦 5. بنك فيصل الإسلامي (حساب الشركات)', 'مخصص لسداد اعتمادات وشحنات الموردين', 'متاح فوري', m(480000), 'sky'],
                            ['🏦 6. بنك أم درمان الوطني (الحساب التجاري)', 'حساب تحصيل شيكات المقاولين والمؤسسات', 'متاح فوري', m(270000), 'sky'],
                            ['🏦 7. بنك النيلين (حساب التوريدات)', 'حساب جاري لسداد المصانع والشركات', 'متاح فوري', m(195000), 'sky'],
                            ['🏦 8. البنك الزراعي السوداني', 'حساب تحصيل مشاريع الولايات والمقاولات', 'متاح فوري', m(140000), 'sky'],
                            ['🏦 9. بنك السلام (حساب العملات والتجارة)', 'حساب استيراد وتغطية شحنات الحديد', 'متاح فوري', m(115000), 'sky'],
                            ['🏦 10. بنك البركة السوداني', 'حساب ودائع وتسهيلات ائتمانية جارية', 'متاح فوري', m(90000), 'sky'],
                            ['🧾 11. حافظة شيكات العملاء برسم التحصيل', 'شيكات مستحقة الصرف خلال 1 إلى 5 أيام', 'تحت التحصيل', m(4250000), 'amber'],
                            ['🛡️ 12. صافي التغطية النقدية للالتزامات', 'السيولة المتاحة تغطي التزامات الموردين بـ 11.8 ضعفاً', 'آمن 100%', m(3450000), 'emerald']
                        ];
                        bodyHtml = vRows.map(r => `<div class="flex items-center justify-between py-2.5 px-4 rounded-xl bg-${r[4]}-500/15 border border-${r[4]}-500/40 gap-3"><div class="min-w-0 flex-1"><span style="font-size:16px;font-weight:900;color:#fff;display:block;">${r[0]}</span><span style="font-size:13.5px;font-weight:800;color:#fde68a;display:block;">${r[1]}</span></div><div class="flex items-center gap-3 shrink-0"><span class="px-3 py-1 rounded-lg bg-${r[4]}-600 text-white font-black text-sm" style="width:95px;text-align:center;">${r[2]}</span><strong style="font-size:16px;font-weight:900;color:#34d399;width:125px;text-align:left;display:inline-block;">${r[3]}</strong></div></div>`).join('');
                    } else if (section === 'sales_stock' || section === 'sales') {
                        const sRows = [
                            ['🏪 1. المخزن الرئيسي — سيخ تسليح 12مم و16مم (بالوزن والربطة)', 'رصيد: 240 طن (2,160 ربطة / 32,400 سيخة) • مبيعات اليوم: 22 طن', 'ربح +' + m(145000), m(98400000), 'emerald'],
                            ['🏪 2. المخزن الرئيسي — أسمنت بورتلاندي عطبرة (بالطن والجوال)', 'رصيد: 110 طن (2,200 جوال) • مبيعات اليوم: 10.5 طن (210 جوال)', 'ربح +' + m(42000), m(20350000), 'emerald'],
                            ['🏪 3. المخزن الرئيسي — مسامير وأقفال وأقراص قطع (بالكرتونة والصندوق)', 'رصيد: 620 كرتونة وصندوق • مبيعات اليوم: 240 كرتونة', 'ربح +' + m(25000), m(14200000), 'sky'],
                            ['🏬 4. المخزن الفرعي — قطاعات ومواسير وزوايا (بالقطعة والربطة)', 'رصيد: 45 طن (1,150 ماسورة وزاوية) • مبيعات اليوم: 95 قطعة', 'ربح +' + m(68000), m(22800000), 'amber'],
                            ['🏬 5. المخزن الفرعي — صاج أسود ومجلفن وألواح (باللوح والمتر)', 'رصيد: 25.5 طن (480 لوح ومتر) • مبيعات اليوم: 180 لوح', 'ربح +' + m(41000), m(12450000), 'amber'],
                            ['🏬 6. المخزن الفرعي — سلك رباط وشبك مباني (باللفة والحجم)', 'رصيد: 340 لفة وشبك • مبيعات اليوم: 170 كرتونة ولفة', 'ربح +' + m(27000), m(8400000), 'sky'],
                            ['💎 7. تحليل العائد على الاستثمار (ROI) للأصناف', 'سيخ 12مم (عائد 28.4%) • سيخ 16مم (عائد 25.2%) • أسمنت عطبرة (عائد 21.8%)', 'ROI +24.6%', '+' + m(9860000), 'emerald'],
                            ['💱 8. تنبيه تقلبات سعر الصرف وحماية قيمة المخزون', 'تقييم المخزون بسعر الصرف اللحظي يحقق فائض إحلال +4.2% ولا توجد أي فاتورة خاسرة', 'محمي 100% ✓', m(176600000), 'amber']
                        ];
                        bodyHtml = sRows.map(r => `<div class="flex items-center justify-between py-2.5 px-4 rounded-xl bg-${r[4]}-500/15 border border-${r[4]}-500/40 gap-3"><div class="min-w-0 flex-1"><span style="font-size:16px;font-weight:900;color:#fff;display:block;">${r[0]}</span><span style="font-size:13.5px;font-weight:800;color:#fde68a;display:block;">${r[1]}</span></div><div class="flex items-center gap-3 shrink-0"><span class="px-3 py-1 rounded-lg bg-${r[4]}-600 text-white font-black text-sm" style="min-width:115px;text-align:center;">${r[2]}</span><strong style="font-size:16px;font-weight:900;color:#34d399;width:125px;text-align:left;display:inline-block;">${r[3]}</strong></div></div>`).join('');
                    } else if (section === 'items') {
                        const base = window._fullItemsRadarData || [];
                        const extraItems = [
                            ['9. سيخ 10مم السجانة', `(${m(425000)} • 18 طن)`, '🟢 ▲ 52 طن', 'emerald'],
                            ['10. سيخ 20مم تسليح ثقيل', `(${m(412000)} • 15 طن)`, '🟢 ▲ 48 طن', 'emerald'],
                            ['11. سيخ 25مم كباري وأبراج', `(${m(410000)} • 12 طن)`, '🟡 ▲ 39 طن', 'amber'],
                            ['12. أسمنت مقاوم للملوحة', `(${m(195000)} • 14 طن)`, '🟡 ▲ 36 طن', 'amber'],
                            ['13. صاج مجلفن 2مم', `(${m(480000)} • 10 طن)`, '🟢 ▲ 31 طن', 'emerald'],
                            ['14. مواسير مياه 2 بوصة', `(${m(28000)}/ماسورة)`, '🟡 ▲ 140 ماسورة', 'amber'],
                            ['15. مواسير مربعة 60×60مم', `(${m(34000)}/ماسورة)`, '🟡 ▲ 115 ماسورة', 'amber'],
                            ['16. زوايا حديد 40×40مم', `(${m(435000)} • 9 طن)`, '🟡 ▲ 26 طن', 'amber'],
                            ['17. كمر حديد U-Channel', `(${m(455000)} • 8 طن)`, '🟢 ▲ 22 طن', 'emerald'],
                            ['18. شبك بقلاوة ممدد', `(${m(19000)}/لفة)`, '🟡 ▼ 85 لفة', 'amber'],
                            ['19. أقفال ومفصلات بوابات ثقيلة', `(${m(95000)}/كرتونة)`, '🔴 ▼ 40 كرتونة', 'rose'],
                            ['20. أقراص قطع وجلخ حديد', `(${m(65000)}/صندوق)`, '🟢 ▲ 95 صندوق', 'emerald']
                        ];
                        const full20 = base.concat(extraItems).slice(0, 20);
                        bodyHtml = full20.map(r => `<div class="inner-box py-3 px-4 flex items-center justify-between gap-3"><span style="font-size:17px;font-weight:900;color:#fff;">${r[0]} <small style="color:#e2e8f0;font-weight:800;font-size:15px;">${r[1]}</small></span><span class="px-3 py-1 rounded-lg bg-${r[3]}-500/20 text-${r[3]}-300 font-black" style="font-size:15px;width:155px;white-space:nowrap;display:inline-flex;align-items:center;">${r[2]}</span></div>`).join('');
                    } else if (section === 'partners') {
                        const base = window._fullPartnersRadarData || [];
                        const extraPartners = [
                            ['8. مغلق النور لمواد البناء', `كل 6 أيام ⬅️ تأخر يومين (${m(4600000)})`, '🟡 استعداد', 'amber'],
                            ['9. مقاولات الأمل الحديث', `كل 7 أيام ⬅️ منتظم اليوم (${m(5800000)})`, '🟢 منتظم', 'emerald'],
                            ['10. ورشة الصفا الهندسية', `كل 5 أيام ⬅️ تأخر 4 أيام (${m(3900000)})`, '🔴 خطر', 'rose'],
                            ['11. مغلق البركة التجاري', `كل 8 أيام ⬅️ منتظم (${m(6100000)})`, '🟢 منتظم', 'emerald'],
                            ['12. مقاولات الجزيرة العمرانية', `كل 6 أيام ⬅️ تأخر 3 أيام (${m(4200000)})`, '🟡 استعداد', 'amber'],
                            ['13. مؤسسة النهضة للمباني', `كل 10 أيام ⬅️ منتظم (${m(7300000)})`, '🟢 منتظم', 'emerald'],
                            ['14. شركة التعمير الكبرى', `كل 5 أيام ⬅️ تأخر 5 أيام (${m(5100000)})`, '🔴 خطر', 'rose'],
                            ['15. مغلق الإخلاص للحديد', `كل 7 أيام ⬅️ منتظم (${m(4900000)})`, '🟢 منتظم', 'emerald'],
                            ['16. شركة الرواد للمقاولات', `كل 6 أيام ⬅️ تأخر يومين (${m(3700000)})`, '🟡 استعداد', 'amber'],
                            ['17. مؤسسة المجد الهندسية', `كل 5 أيام ⬅️ تأخر 8 أيام (${m(2100000)})`, '⬛ متوقف', 'black'],
                            ['18. مغلق بحري للحديد والأسمنت', `كل 7 أيام ⬅️ منتظم (${m(6800000)})`, '🟢 منتظم', 'emerald'],
                            ['19. مقاولات النخبة الحديثة', `كل 6 أيام ⬅️ تأخر 4 أيام (${m(3300000)})`, '🔴 خطر', 'rose'],
                            ['20. ورشة المستقبل للإنشاءات', `كل 5 أيام ⬅️ منتظم (${m(4400000)})`, '🟢 منتظم', 'emerald']
                        ];
                        const full20 = base.concat(extraPartners).slice(0, 20);
                        bodyHtml = full20.map(r => {
                            const c = r[3] || 'emerald';
                            const cardCls = c === 'black' ? 'border border-white/30" style="background:rgba(2,6,23,0.92);' : `bg-${c}-500/15 border border-${c}-500/40`;
                            const badgeStyle = c === 'black' ? 'width:110px;display:inline-flex;align-items:center;justify-content:flex-start;padding:5px 12px;gap:6px;background:#020617;color:#fff;border:1px solid #fff;font-size:14px;' : 'width:110px;display:inline-flex;align-items:center;justify-content:flex-start;padding:5px 12px;gap:6px;font-size:14px;';
                            const badgeCls = c === 'black' ? 'py-1 rounded-lg font-black' : (c === 'amber' ? 'py-1 rounded-lg bg-amber-500 text-slate-950 font-black' : `py-1 rounded-lg bg-${c}-600 text-white font-black`);
                            const safeName = r[0].replace(/'/g, '');
                            const safeSub = r[1].replace(/<[^>]*>/g, '').replace(/'/g, '');
                            const waIcon = window._waIconSvg || `<svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg>`;
                            return `<div class="flex items-center justify-between py-2.5 px-4 rounded-xl gap-3 ${cardCls}"><div class="min-w-0 flex-1"><span style="font-size:17px;font-weight:900;color:#fff;display:block;">${r[0]}</span><span style="font-size:14px;font-weight:800;color:#e2e8f0;display:block;">${r[1]}</span></div><div class="flex items-center gap-2 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('${safeName}', '${safeSub}')" title="مراسلة وتنبيه ذكي عبر واتساب" class="wa-svg-btn">${waIcon}</button><button type="button" onclick="showPartnerItemsDetail('${safeName}')" class="py-1.5 px-3 rounded-lg bg-slate-800 hover:bg-slate-700 text-sky-300 border border-sky-500/30 font-black text-sm cursor-pointer">📦 الأصناف</button><span class="${badgeCls}" style="${badgeStyle}">${r[2]}</span></div></div>`;
                        }).join('');
                    } else if (section === 'debt') {
                        if (window._debtAgingMode === 'profit_checks') {
                            const profs = [
                                ['🟢 1. مؤسسة البنيان الحديثة', 18400000, '22.4%', 3120000, 'emerald'],
                                ['🟢 2. شركة النيل للمقاولات', 12600000, '20.8%', 2450000, 'emerald'],
                                ['🟢 3. شركة الرواد للمقاولات', 9800000, '19.2%', 1880000, 'emerald'],
                                ['🟡 4. مغلق الوادي للحديد', 9200000, '16.5%', 1518000, 'amber'],
                                ['🟡 5. مقاولات أبراج الخرطوم', 7500000, '15.4%', 1155000, 'amber'],
                                ['🟡 6. مقاولات السلام السكنية', 6400000, '14.7%', 940000, 'amber'],
                                ['🟡 7. شركة إعمار الهندسية', 6200000, '13.8%', 855000, 'amber'],
                                ['🔴 8. مغلق السجانة الهندسي', 8900000, '6.2%', 551000, 'rose'],
                                ['🔴 9. مقاولات الأمل الحديث', 4100000, '5.8%', 237800, 'rose'],
                                ['⬛ 10. ورشة الأمانة للحدادة', 2400000, '4.1%', 98400, 'black']
                            ];
                            bodyHtml = profs.map(p => {
                                const c = p[4];
                                const cardCls = c === 'black' ? 'border border-white/30" style="background:rgba(2,6,23,0.92);' : `bg-${c}-500/15 border border-${c}-500/40`;
                                const bStyle = c === 'black' ? 'background:#020617;color:#fff;border:1px solid #fff;' : (c === 'amber' ? 'color:#000000!important;-webkit-text-fill-color:#000000!important;' : '');
                                const bCls = c === 'black' ? 'px-3 py-1 rounded-lg font-black text-base' : (c === 'amber' ? 'px-3 py-1 rounded-lg bg-amber-500 font-black text-base' : `px-3 py-1 rounded-lg bg-${c}-600 text-white font-black text-base`);
                                return `<div class="flex items-center justify-between py-2.5 px-4 rounded-xl gap-3 ${cardCls}"><div class="min-w-0 flex-1"><span style="font-size:17px;font-weight:900;color:#fff;display:block;">${p[0]}</span><span style="font-size:14px;font-weight:800;color:#fde68a;display:block;">مبيعات العميل: ${m(p[1])} • هامش الربح: ${p[2]}</span></div><div class="flex items-center gap-3 shrink-0"><span class="${bCls}" style="min-width:74px;text-align:center;${bStyle}">${p[2]}</span><strong style="font-size:16px;font-weight:900;color:#34d399;min-width:115px;text-align:left;">+${m(p[3])}</strong></div></div>`;
                            }).join('');
                        } else if (window._debtAgingMode === 'limits') {
                            const lims = [
                                ['🔴 1. مغلق السجانة الهندسي', 235000, 250000, 94, 'rose'],
                                ['🔴 2. مؤسسة البنيان الحديثة', 420000, 500000, 84, 'rose'],
                                ['🟡 3. مقاولات أبراج الخرطوم', 195000, 250000, 78, 'amber'],
                                ['🟡 4. شركة إعمار الهندسية', 290000, 400000, 72, 'amber'],
                                ['🟡 5. مغلق الوادي للحديد', 140000, 220000, 64, 'amber'],
                                ['🟡 6. مقاولات الأمل الحديث', 165000, 300000, 55, 'amber'],
                                ['🟢 7. ورشة الصفا الهندسية', 120000, 250000, 48, 'emerald'],
                                ['🟢 8. مغلق البركة التجاري', 135000, 320000, 42, 'emerald'],
                                ['🟢 9. مقاولات السلام السكنية', 110000, 300000, 37, 'emerald'],
                                ['🟢 10. مقاولات الجزيرة العمرانية', 95000, 300000, 32, 'emerald'],
                                ['🟢 11. شركة النيل للمقاولات والتعمير', 165000, 600000, 27, 'emerald'],
                                ['🟢 12. مؤسسة النهضة للمباني', 88000, 400000, 22, 'emerald'],
                                ['🟢 13. شركة التعمير الكبرى', 90000, 500000, 18, 'emerald'],
                                ['🟢 14. مغلق الإخلاص للحديد', 52000, 350000, 15, 'emerald'],
                                ['🟢 15. شركة الرواد للمقاولات', 40000, 400000, 10, 'emerald']
                            ];
                            bodyHtml = lims.map(L => `<div class="p-3 rounded-xl bg-${L[4]}-500/15 border border-${L[4]}-500/40"><div class="flex justify-between mb-1.5" style="font-size:16px;"><span class="font-black text-white">${L[0]}</span><span class="font-black text-${L[4]}-300">${m(L[1])} / ${m(L[2])} (${L[3]}%)</span></div><div class="w-full h-3 bg-slate-800 rounded-full overflow-hidden"><div class="bg-${L[4]}-500 h-full rounded-full" style="width:${L[3]}%"></div></div></div>`).join('');
                        } else {
                            const debts = [
                                ['🟢 1. شركة الرواد للمقاولات', 'يسدد كل 10 أيام ⬅️ مرّ يومان', 'يومان', 40000, 'emerald'],
                                ['🟢 2. مقاولات السلام السكنية', 'يسدد كل 10 أيام ⬅️ مرّ 3 أيام', '3 أيام', 110000, 'emerald'],
                                ['🟢 3. شركة النيل للمقاولات', 'يسدد كل 15 يوماً ⬅️ مرّ 4 أيام', '4 أيام', 165000, 'emerald'],
                                ['🟢 4. مغلق الإخلاص للحديد', 'يسدد كل 12 يوماً ⬅️ مرّ 5 أيام', '5 أيام', 52000, 'emerald'],
                                ['🟢 5. مؤسسة النهضة للمباني', 'يسدد كل 14 يوماً ⬅️ مرّ 6 أيام', '6 أيام', 88000, 'emerald'],
                                ['🟡 6. مغلق البركة التجاري', 'يسدد كل 7 أيام ⬅️ مرّ 7 أيام', '7 أيام', 135000, 'amber'],
                                ['🟡 7. مؤسسة البنيان الحديثة', 'يسدد كل 7 أيام ⬅️ مرّ 8 أيام (+1 يوم)', '8 أيام', 420000, 'amber'],
                                ['🟡 8. مغلق الوادي للحديد', 'يسدد كل 6 أيام ⬅️ مرّ 9 أيام (+3 أيام)', '9 أيام', 140000, 'amber'],
                                ['🟡 9. مقاولات الأمل الحديث', 'يسدد كل 7 أيام ⬅️ مرّ 9 أيام (+2 يوم)', '9 أيام', 165000, 'amber'],
                                ['🔴 10. مغلق السجانة الهندسي', 'يسدد كل 5 أيام ⬅️ مرّ 10 أيام (+5 أيام)', '10 أيام', 235000, 'rose'],
                                ['🔴 11. شركة إعمار الهندسية', 'يسدد كل 6 أيام ⬅️ مرّ 10 أيام (+4 أيام)', '10 أيام', 290000, 'rose'],
                                ['⬛ 12. مقاولات أبراج الخرطوم', 'يسدد كل 7 أيام ⬅️ مرّ 12 يوماً (+5 أيام)', '12 يوماً', 195000, 'black'],
                                ['⬛ 13. ورشة الصفا الهندسية', 'يسدد كل 5 أيام ⬅️ مرّ 14 يوماً (+9 أيام)', '14 يوماً', 120000, 'black'],
                                ['⬛ 14. ورشة الأمانة للحدادة', 'يسدد كل 5 أيام ⬅️ مرّ 16 يوماً (+11 يوماً)', '16 يوماً', 195000, 'black'],
                                ['⬛ 15. مؤسسة المجد الهندسية', 'يسدد كل 6 أيام ⬅️ مرّ 19 يوماً (+13 يوماً)', '19 يوماً', 210000, 'black']
                            ];
                            bodyHtml = debts.map(d => {
                                const c = d[4];
                                const cardCls = c === 'black' ? 'border border-white/30" style="background:rgba(2,6,23,0.92);' : `bg-${c}-500/15 border border-${c}-500/40`;
                                const bStyle = c === 'black' ? 'background:#020617;color:#fff;border:1px solid #fff;' : '';
                                const bCls = c === 'black' ? 'px-3 py-1 rounded-lg font-black text-base' : (c === 'amber' ? 'px-3 py-1 rounded-lg bg-amber-500 text-slate-950 font-black text-base' : `px-3 py-1 rounded-lg bg-${c}-600 text-white font-black text-base`);
                                const safeName = d[0].replace(/'/g, '');
                                const safeSub = (d[1].replace(/<[^>]*>/g, '') + ' • ' + m(d[3])).replace(/'/g, '');
                                const waIcon = window._waIconSvg || `<svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg>`;
                                return `<div class="flex items-center justify-between py-2.5 px-4 rounded-xl gap-3 ${cardCls}"><div class="min-w-0 flex-1"><span style="font-size:17px;font-weight:900;color:#fff;display:block;">${d[0]}</span><span style="font-size:14px;font-weight:800;color:#e2e8f0;display:block;">المعتاد: ${d[1]}</span></div><div class="flex items-center gap-3 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('${safeName}', '${safeSub}')" title="مراسلة وتنبيه ذكي عبر واتساب" class="wa-svg-btn">${waIcon}</button><span class="${bCls}" style="width:78px;text-align:center;display:inline-flex;align-items:center;justify-content:center;${bStyle}">${d[2]}</span><strong style="font-size:16px;font-weight:900;color:#34d399;width:120px;text-align:left;display:inline-block;white-space:nowrap;">${m(d[3])}</strong></div></div>`;
                            }).join('');
                        }
                    } else {
                        const srcMap = { debt: 'dash-debt-aging-box', donuts: 'sec-global-ai-innovations', forecast: 'dash-forecast-list', audit: 'sec-ops-audit', live_radar: 'radar-feed', cashflow: 'sec-ops-audit' };
                        const srcEl = document.getElementById(srcMap[section] || 'dash-debt-aging-box');
                        bodyHtml = srcEl ? srcEl.innerHTML : '';
                    }
                    const isLt = document.documentElement.classList.contains('light-theme') || document.body.classList.contains('light-theme');
                    const mBg = isLt ? 'linear-gradient(145deg, var(--lt-card-1, #fffdf8) 0%, var(--lt-card-2, #f7efe0) 100%)' : 'var(--card-bg, linear-gradient(155deg, rgba(16, 26, 48, 0.98) 0%, rgba(8, 14, 28, 0.99) 100%))';
                    const hBg = isLt ? 'var(--lt-sub-2, #f6ecda)' : 'var(--topbar-bg, rgba(15, 23, 42, 0.95))';
                    const bCol = isLt ? 'var(--lt-border, #5c2808)' : 'var(--border-color, #38bdf8)';
                    const tCol = isLt ? 'var(--lt-ink, #120500)' : '#ffffff';
                    modal.innerHTML = `<div class="bento-card" style="background:${mBg}!important;border:2.5px solid ${bCol}!important;color:${tCol}!important;border-radius:18px;width:100%;max-width:820px;max-height:88vh;display:flex;flex-direction:column;box-shadow:0 24px 60px rgba(0,0,0,0.65);overflow:hidden;">
                        <div style="padding:14px 18px;background:${hBg}!important;border-bottom:1.5px solid ${bCol};display:flex;align-items:center;justify-content:space-between;">
                            <strong style="color:${tCol}!important;font-size:16px;font-weight:900;">+ ${titles[section] || 'عرض موسع'}</strong>
                            <button type="button" onclick="document.getElementById('partner-items-modal').style.display='none'" style="background:#e11d48;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;border-radius:8px;padding:5px 14px;font-weight:900;cursor:pointer;">✕ إغلاق</button>
                        </div>
                        <div style="padding:16px 18px;overflow-y:auto;display:flex;flex-direction:column;gap:10px;">${bodyHtml}</div>
                    </div>`;
                    modal.style.display = 'flex';
                }

                function showPartnerItemsDetail(name) {
                    const m = _fmtRadarMoney;
                    const catalog = [
                        ['1. سيخ تسليح 12مم السجانة', '18 طن (2,430 سيخة)', m(7560000), '🟢 تركيز 42%'],
                        ['2. سيخ تسليح 16مم السجانة', '12 طن (912 سيخة)', m(4980000), '🟢 تركيز 27%'],
                        ['3. أسمنت بورتلاندي عطبرة', '240 شوال (12 طن)', m(2220000), '🟡 سحب مستمر'],
                        ['4. سلك رباط مجلفن ناعم', '45 لفة (22.5 كجم/لفة)', m(1710000), '🟢 سحب مستمر'],
                        ['5. كمر حديد H-Beam ثقيل', '3 طن (قطاعات)', m(1350000), '🟡 موسمي'],
                        ['6. زوايا حديد متساوية 50×50مم', '2 طن (ربطات)', m(880000), '🟡 موسمي'],
                        ['7. صاج أسود 3مم ثقيل', '1.5 طن (ألواح)', m(690000), '🔴 منخفض'],
                        ['8. مواسير مربعة 40×40مم', '85 ماسورة', m(510000), '🟡 متوسط'],
                        ['9. شبك حديد ملحوم للمباني', '60 قطعة', m(360000), '🔴 منخفض'],
                        ['10. مسامير صلب فولاذية', '25 كرتونة', m(175000), '🟢 تكميلي']
                    ];
                    let modal = document.getElementById('partner-items-modal');
                    if (!modal) {
                        modal = document.createElement('div');
                        modal.id = 'partner-items-modal';
                        modal.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(2,6,23,0.78);backdrop-filter:blur(6px);display:flex;align-items:center;justify-content:center;padding:16px;direction:rtl;';
                        document.body.appendChild(modal);
                    }
                    const isLt = document.documentElement.classList.contains('light-theme') || document.body.classList.contains('light-theme');
                    const mBg = isLt ? 'linear-gradient(145deg, var(--lt-card-1, #fffdf8) 0%, var(--lt-card-2, #f7efe0) 100%)' : 'var(--card-bg, linear-gradient(155deg, rgba(16, 26, 48, 0.98) 0%, rgba(8, 14, 28, 0.99) 100%))';
                    const hBg = isLt ? 'var(--lt-sub-2, #f6ecda)' : 'var(--topbar-bg, rgba(15, 23, 42, 0.95))';
                    const rBg = isLt ? 'var(--lt-sub-1, #fffefb)' : 'var(--card-inner, rgba(30,41,59,0.78))';
                    const bCol = isLt ? 'var(--lt-border, #5c2808)' : 'var(--border-color, #38bdf8)';
                    const tCol = isLt ? 'var(--lt-ink, #120500)' : '#ffffff';
                    modal.innerHTML = `<div class="bento-card" style="background:${mBg}!important;border:2.5px solid ${bCol}!important;color:${tCol}!important;border-radius:18px;width:100%;max-width:640px;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 24px 60px rgba(0,0,0,0.65);overflow:hidden;">
                        <div style="padding:14px 18px;background:${hBg}!important;border-bottom:1.5px solid ${bCol};display:flex;align-items:center;justify-content:space-between;">
                            <strong style="color:${tCol}!important;font-size:15px;font-weight:900;">📦 كشف الأصناف المسحوبة بالتفصيل (10 أصناف): ${name}</strong>
                            <button type="button" onclick="document.getElementById('partner-items-modal').style.display='none'" style="background:#e11d48;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;border-radius:8px;padding:4px 12px;font-weight:900;cursor:pointer;">✕ إغلاق</button>
                        </div>
                        <div style="padding:14px 18px;overflow-y:auto;display:flex;flex-direction:column;gap:8px;">
                            ${catalog.map(it => `<div class="inner-box" style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:${rBg}!important;border:1px solid ${bCol};border-radius:10px;font-size:12px;"><span style="color:${tCol};font-weight:900;flex:1.3;">${it[0]}</span><span style="color:${isLt ? '#1e3a8a' : '#38bdf8'};font-weight:900;flex:1;text-align:center;">${it[1]}</span><span style="color:${isLt ? '#065f46' : '#34d399'};font-weight:900;flex:0.9;text-align:left;">${it[2]}</span><span style="color:${isLt ? '#92400e' : '#fcd34d'};font-weight:900;flex:0.8;text-align:left;">${it[3]}</span></div>`).join('')}
                        </div>
                    </div>`;
                    modal.style.display = 'flex';
                }
                function _fmtRadarMoney(sdgVal) {
                    const usdRate = parseFloat(document.getElementById('cur-usd')?.value) || 600;
                    if (typeof displayUSD !== 'undefined' && displayUSD) return '$ ' + Math.round(sdgVal / usdRate).toLocaleString('en-US');
                    return sdgVal.toLocaleString('en-US') + ' ج.س';
                }
                window._partnersGroupMode = 'cust';
                function togglePartnersGroup() {
                    const isCust = window._partnersGroupMode !== 'supp';
                    window._partnersGroupMode = isCust ? 'supp' : 'cust';
                    const custTabs = document.getElementById('dash-partners-tabs-cust');
                    const suppTabs = document.getElementById('dash-partners-tabs-supp');
                    const btn = document.getElementById('dash-partners-group-toggle');
                    const title = document.getElementById('dash-partners-radar-title');
                    if (window._partnersGroupMode === 'supp') {
                        if (custTabs) custTabs.style.display = 'none';
                        if (suppTabs) suppTabs.style.display = 'grid';
                        if (btn) btn.innerText = '⇄ العملاء';
                        if (title) title.innerText = 'رادار دوريات شراء الموردين';
                        const firstBtn = suppTabs ? suppTabs.querySelector('button') : null;
                        switchPartnersRadar('supp_cycle', firstBtn);
                    } else {
                        if (custTabs) custTabs.style.display = 'grid';
                        if (suppTabs) suppTabs.style.display = 'none';
                        if (btn) btn.innerText = '⇄ الموردين';
                        if (title) title.innerText = 'رادار دوريات شراء العملاء';
                        const firstBtn = custTabs ? custTabs.querySelector('button') : null;
                        switchPartnersRadar('freq_alert', firstBtn);
                    }
                }
                function switchPartnersRadar(mode, btn) {
                    window._lastPartnersRadarMode = mode;
                    window._lastPartnersRadarBtn = btn;
                    if (btn) _activateMiniTab(btn, mode === 'freq_alert' ? 'bg-rose-600' : 'bg-emerald-600');
                    const box = document.getElementById('dash-partners-radar-list');
                    if (!box) return;
                    const m = _fmtRadarMoney;
                    const rows = {
                        freq_alert: [
                            ['1. مؤسسة البنيان الحديثة', `كل 5 أيام ⬅️ موعده اليوم (${m(18400000)})`, '🟢 منتظم', 'emerald', 'منتظم في موعد الشراء المعتاد (اليوم)'],
                            ['2. شركة النيل للمقاولات', `كل 7 أيام ⬅️ تأخر يوم واحد (${m(12600000)})`, '🟢 منتظم', 'emerald', 'تأخر يوم واحد فقط (ضمن النطاق الأخضر الآمن)'],
                            ['3. مغلق الوادي للحديد', `كل 5 أيام ⬅️ تأخر يومين (${m(9200000)})`, '🟡 استعداد', 'amber', 'استعداد ومتابعة: تأخر يومين عن موعد الشراء'],
                            ['4. مقاولات السلام السكنية', `كل 6 أيام ⬅️ تأخر 3 أيام (${m(6400000)})`, '🟡 استعداد', 'amber', 'استعداد ومتابعة: تأخر 3 أيام عن موعد الشراء'],
                            ['5. مقاولات أبراج الخرطوم', `كل 5 أيام ⬅️ تأخر 4 أيام (${m(8900000)})`, '🔴 خطر', 'rose', 'خطر: تأخر 4 أيام عن موعد الشراء المعتاد'],
                            ['6. شركة إعمار الهندسية', `كل 10 أيام ⬅️ تأخر 5 أيام (${m(5100000)})`, '🔴 خطر', 'rose', 'خطر: تأخر 5 أيام عن موعد الشراء المعتاد'],
                            ['7. ورشة الأمانة للحدادة', `كل 5 أيام ⬅️ تأخر 7 أيام (${m(2400000)})`, '⬛ متوقف', 'black', 'متوقف: تأخر 7 أيام فأكثر عن موعد الشراء']
                        ],
                        top_cust: [
                            ['1. مؤسسة البنيان الحديثة', `إجمالي المشتريات: ${m(18400000)}`, '🟢 ▲ +28%', 'emerald', 'نمو ممتاز في السحوبات (+28%)'],
                            ['2. شركة النيل للمقاولات', `إجمالي المشتريات: ${m(12600000)}`, '🟢 ▲ +19%', 'emerald', 'نمو ممتاز في السحوبات (+19%)'],
                            ['3. مغلق السجانة الهندسي', `إجمالي المشتريات: ${m(8900000)}`, '🟢 ▲ +14%', 'emerald', 'نمو مستمر في السحوبات (+14%)'],
                            ['4. مقاولات أبراج الخرطوم', `إجمالي المشتريات: ${m(7500000)}`, '🟡 ▲ +11%', 'amber', 'نمو متوسط (+11%)'],
                            ['5. شركة إعمار الهندسية', `إجمالي المشتريات: ${m(6200000)}`, '🟡 ▲ +9%', 'amber', 'نمو متوسط (+9%)'],
                            ['6. مغلق الوادي للحديد', `إجمالي المشتريات: ${m(5400000)}`, '🟡 ▲ +7%', 'amber', 'نمو متوسط (+7%)'],
                            ['7. مقاولات السلام السكنية', `إجمالي المشتريات: ${m(4800000)}`, '🟡 ▲ +6%', 'amber', 'نمو متوسط (+6%)']
                        ],
                        low_cust: [
                            ['1. ورشة الأمانة للحدادة', `إجمالي المشتريات: ${m(420000)}`, '🔴 ▼ -18%', 'rose', 'تراجع حاد في المشتريات (-18%)'],
                            ['2. مغلق النور لمواد البناء', `إجمالي المشتريات: ${m(840000)}`, '🔴 ▼ -12%', 'rose', 'تراجع ملحوظ في المشتريات (-12%)'],
                            ['3. مقاولات الأمل الحديث', `إجمالي المشتريات: ${m(1150000)}`, '🔴 ▼ -9%', 'rose', 'تراجع في المشتريات (-9%)'],
                            ['4. ورشة الصفا الهندسية', `إجمالي المشتريات: ${m(1480000)}`, '🟡 ▼ -6%', 'amber', 'انخفاض طفيف (-6%)'],
                            ['5. مغلق البركة التجاري', `إجمالي المشتريات: ${m(1820000)}`, '🟡 ▼ -5%', 'amber', 'انخفاض طفيف (-5%)'],
                            ['6. مقاولات الجزيرة', `إجمالي المشتريات: ${m(2150000)}`, '🟡 ▼ -3%', 'amber', 'انخفاض طفيف (-3%)'],
                            ['7. مؤسسة النهضة العمرانية', `إجمالي المشتريات: ${m(2490000)}`, '🟡 ▼ -2%', 'amber', 'انخفاض طفيف (-2%)']
                        ],
                        profit_cust: [
                            ['1. ورشة الأمانة للحدادة', `انقطاع 7 أيام • احتمال التوقف 88%`, '⬛ متوقف', 'black', 'انقطاع تام منذ 7 أيام (احتمال توقف 88%)'],
                            ['2. شركة إعمار الهندسية', `تأخر 5 أيام • احتمال التوقف 72%`, '🔴 خطر', 'rose', 'خطر انقطاع: تأخر 5 أيام (72%)'],
                            ['3. مقاولات أبراج الخرطوم', `تأخر 4 أيام • احتمال التوقف 64%`, '🔴 خطر', 'rose', 'خطر انقطاع: تأخر 4 أيام (64%)'],
                            ['4. مقاولات الأمل الحديث', `تراجع سحب -45% هذا الشهر`, '🔴 خطر', 'rose', 'خطر تراجع السحوبات بنسبة 45%'],
                            ['5. مقاولات السلام السكنية', `تأخر 3 أيام • احتمال التوقف 40%`, '🟡 استعداد', 'amber', 'تنبيه استعداد: تأخر 3 أيام (40%)'],
                            ['6. مغلق الوادي للحديد', `تأخر يومين • احتمال التوقف 28%`, '🟡 استعداد', 'amber', 'تنبيه استعداد: تأخر يومين (28%)'],
                            ['7. مغلق النور لمواد البناء', `تراجع سحب -20% هذا الأسبوع`, '🟡 استعداد', 'amber', 'تنبيه مبكر: تراجع 20% هذا الأسبوع']
                        ],
                        supp_cycle: [
                            ['1. مصنع السجانة للحديد', `توريد منتظم اليوم (${m(92000000)})`, '🟢 منتظم', 'emerald', 'توريد منتظم في الموعد المحدد'],
                            ['2. شركة أسمنت عطبرة', `تأخر يوم واحد (${m(28500000)})`, '🟢 منتظم', 'emerald', 'تأخر يوم واحد فقط (ضمن النطاق الأخضر)'],
                            ['3. الدولية للقطاعات والمواسير', `تأخر توريد يومين (${m(16200000)})`, '🟡 استعداد', 'amber', 'استعداد: تأخر التوريد يومين'],
                            ['4. مصنع الأسلاك المجلفنة', `تأخر توريد 3 أيام (${m(6800000)})`, '🟡 استعداد', 'amber', 'استعداد: تأخر التوريد 3 أيام'],
                            ['5. مستودع الشرق للصاج', `تأخر توريد 4 أيام (${m(4900000)})`, '🔴 خطر', 'rose', 'خطر: تأخر التوريد 4 أيام'],
                            ['6. وكالة البحر الأحمر', `تأخر توريد 5 أيام (${m(3800000)})`, '🔴 خطر', 'rose', 'خطر: تأخر التوريد 5 أيام'],
                            ['7. شركة الشمال للتوريدات', `تأخر توريد 7 أيام (${m(1900000)})`, '⬛ متوقف', 'black', 'متوقف: انقطاع التوريد منذ 7 أيام']
                        ],
                        top_supp: [
                            ['1. مصنع السجانة للحديد', `إجمالي التوريد: ${m(92000000)}`, '🟢 ▲ 62%', 'emerald', 'المورد الأول (حصة 62%)'],
                            ['2. شركة أسمنت عطبرة', `إجمالي التوريد: ${m(28500000)}`, '🟢 ▲ 24%', 'emerald', 'المورد الثاني (حصة 24%)'],
                            ['3. الدولية للقطاعات والمواسير', `إجمالي التوريد: ${m(16200000)}`, '🟢 ▲ 14%', 'emerald', 'المورد الثالث (حصة 14%)'],
                            ['4. مصنع الأسلاك المجلفنة', `إجمالي التوريد: ${m(6800000)}`, '🟡 ▲ 8%', 'amber', 'حصة التوريد 8%'],
                            ['5. مستودع الشرق للصاج', `إجمالي التوريد: ${m(4900000)}`, '🟡 ▲ 6%', 'amber', 'حصة التوريد 6%'],
                            ['6. وكالة البحر الأحمر', `إجمالي التوريد: ${m(3800000)}`, '🟡 ▲ 5%', 'amber', 'حصة التوريد 5%'],
                            ['7. شركة النيلين للصلب', `إجمالي التوريد: ${m(2900000)}`, '🟡 ▲ 4%', 'amber', 'حصة التوريد 4%']
                        ],
                        low_supp: [
                            ['1. شركة الشمال للتوريدات', `إجمالي التوريد: ${m(1900000)}`, '🔴 ▼ 2%', 'rose', 'أقل الموردين حصة (2%)'],
                            ['2. ورشة الخرطوم للسلك', `إجمالي التوريد: ${m(2200000)}`, '🔴 ▼ 3%', 'rose', 'حصة منخفضة (3%)'],
                            ['3. مستودع الأمل للزوايا', `إجمالي التوريد: ${m(2600000)}`, '🔴 ▼ 3%', 'rose', 'حصة منخفضة (3%)'],
                            ['4. وكالة الجزيرة للحديد', `إجمالي التوريد: ${m(3100000)}`, '🟡 ▼ 4%', 'amber', 'حصة متوسطة-منخفضة (4%)'],
                            ['5. شركة النجمة للمسامير', `إجمالي التوريد: ${m(3400000)}`, '🟡 ▼ 4%', 'amber', 'حصة متوسطة-منخفضة (4%)'],
                            ['6. وكالة البحر الأحمر', `إجمالي التوريد: ${m(3800000)}`, '🟡 ▼ 5%', 'amber', 'حصة 5%'],
                            ['7. مستودع الشرق للصاج', `إجمالي التوريد: ${m(4900000)}`, '🟡 ▼ 6%', 'amber', 'حصة 6%']
                        ],
                        best_supp: [
                            ['1. شركة الشمال للتوريدات', `انقطاع توريد 7 أيام (خطر توقف)`, '⬛ متوقف', 'black', 'انقطاع التوريد منذ 7 أيام'],
                            ['2. وكالة البحر الأحمر', `تأخر شحنة 5 أيام`, '🔴 خطر', 'rose', 'خطر تأخر الشحنة 5 أيام'],
                            ['3. مستودع الشرق للصاج', `تأخر شحنة 4 أيام`, '🔴 خطر', 'rose', 'خطر تأخر الشحنة 4 أيام'],
                            ['4. مصنع الأسلاك المجلفنة', `تأخر 3 أيام (نقص مخزون سلك)`, '🟡 استعداد', 'amber', 'استعداد: تأخر الشحنة 3 أيام'],
                            ['5. الدولية للقطاعات والمواسير', `تأخر يومين (متابعة شحن)`, '🟡 استعداد', 'amber', 'استعداد: تأخر الشحنة يومين'],
                            ['6. شركة أسمنت عطبرة', `تأخر يوم واحد (في الطريق)`, '🟢 منتظم', 'emerald', 'الشحنة في الطريق (تأخر يوم واحد)'],
                            ['7. مصنع السجانة للحديد', `توريد منتظم ومستقر 100%`, '🟢 منتظم', 'emerald', 'توريد منتظم 100%']
                        ],
                        behavior_cust: [
                            ['1. مؤسسة البنيان الحديثة', `سلوك شرائي ثابت كل 5 أيام • طلب متوقع اليوم (${m(18400000)})`, '🟢 التزام 98%', 'emerald', 'التزام 98% بالدورية والسداد'],
                            ['2. شركة النيل للمقاولات', `سحب متصاعد (+19%) • يفضل سيخ 12مم و16مم`, '🟢 التزام 94%', 'emerald', 'التزام 94% ونمو سحوبات'],
                            ['3. مغلق الوادي للحديد', `تذبذب بسيط (+2 يوم) • يتفاعل فوراً مع التذكير`, '🟡 التزام 82%', 'amber', 'التزام 82% ويستجيب للتنبيه'],
                            ['4. مقاولات السلام السكنية', `تأخر 3 أيام عن المعتاد • يحتاج تواصل مبيعات`, '🟡 التزام 75%', 'amber', 'التزام 75% (تأخر 3 أيام)'],
                            ['5. مقاولات أبراج الخرطوم', `تحول جزئي لمنافس؟ تأخر 4 أيام (${m(8900000)})`, '🔴 احتمال 45%', 'rose', 'تنبيه سلوكي: احتمال تحول لمنافس'],
                            ['6. شركة إعمار الهندسية', `تباطؤ سحب مستمر منذ أسبوعين (تأخر 5 أيام)`, '🔴 احتمال 35%', 'rose', 'تباطؤ سحوبات منذ أسبوعين'],
                            ['7. ورشة الأمانة للحدادة', `توقف سحب منذ 7 أيام بسبب شيك راجع`, '⬛ توقف 90%', 'black', 'متوقف لحين تسوية المديونية']
                        ],
                        behavior_supp: [
                            ['1. مصنع السجانة للحديد', `دقة مواعيد 99% • ثبات جودة وأوزان 100%`, '🟢 التزام 99%', 'emerald', 'أفضل مورد التزاماً وجودة'],
                            ['2. شركة أسمنت عطبرة', `دقة توريد 95% • سرعة تعويض الشحنات`, '🟢 التزام 95%', 'emerald', 'التزام ممتاز بالتوريد'],
                            ['3. الدولية للقطاعات والمواسير', `تأخر معتاد يومين في المواسم • أسعار مستقرة`, '🟡 التزام 84%', 'amber', 'التزام جيد (تأخر موسمي يومين)'],
                            ['4. مصنع الأسلاك المجلفنة', `تذبذب توريد (تأخر 3 أيام) • جودة ممتازة`, '🟡 التزام 78%', 'amber', 'تذبذب في مواعيد الشحن'],
                            ['5. مستودع الشرق للصاج', `تأخر متكرر (4 أيام) • أسعار متقلبة ▲`, '🔴 التزام 60%', 'rose', 'تأخر متكرر وتقلب أسعار'],
                            ['6. وكالة البحر الأحمر', `تأخر شحنات 5 أيام • يتطلب بديل احتياطي`, '🔴 التزام 52%', 'rose', 'ينصح بتأمين مورد بديل'],
                            ['7. شركة الشمال للتوريدات', `انقطاع توريد 7 أيام • عدم التزام بالحصص`, '⬛ التزام 25%', 'black', 'مورد غير منتظم حالياً']
                        ]
                    }[mode] || [];
                    window._fullPartnersRadarData = rows;
                    const dayBadges = {
                        freq_alert: ['اليوم', '1 يوم', 'يومان', '3 أيام', '4 أيام', '5 أيام', '7 أيام'],
                        supp_cycle: ['اليوم', '1 يوم', 'يومان', '3 أيام', '4 أيام', '5 أيام', '7 أيام'],
                        profit_cust: ['7 أيام', '5 أيام', '4 أيام', 'شهر', '3 أيام', 'يومان', 'أسبوع'],
                        best_supp: ['7 أيام', '5 أيام', '4 أيام', '3 أيام', 'يومان', '1 يوم', 'اليوم']
                    }[mode] || [];
                    box.innerHTML = rows.slice(0, 7).map((r, idx) => {
                        const c = r[3];
                        const cardCls = c === 'black' ? 'border border-white/30" style="background:rgba(2,6,23,0.92);' : `bg-${c}-500/15 border border-${c}-500/40`;
                        const txtStyle = c === 'amber' ? 'color:#000000!important;-webkit-text-fill-color:#000000!important;' : 'color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;';
                        const badgeStyle = c === 'black' ? 'width:98px;font-size:14px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:flex-start;padding:4px 8px;gap:5px;background:#020617;color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #fff;' : `width:98px;font-size:14px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:flex-start;padding:4px 8px;gap:5px;${txtStyle}`;
                        const badgeCls = c === 'black' ? 'py-1 rounded-lg font-black' : (c === 'amber' ? 'py-1 rounded-lg bg-amber-500 font-black' : `py-1 rounded-lg bg-${c}-600 text-white font-black`);
                        const dayPill = dayBadges[idx] ? `<span class="${badgeCls}" style="width:62px;font-size:14px!important;white-space:nowrap;text-align:center;display:inline-flex;align-items:center;justify-content:center;${c === 'black' ? 'background:#020617;color:#fff!important;-webkit-text-fill-color:#fff!important;border:1px solid #fff;' : txtStyle}">${dayBadges[idx]}</span>` : '';
                        const safeName = r[0].replace(/'/g, '');
                        const tip = (r[4] || r[1]).replace(/"/g, '');
                        const waIcon = window._waIconSvg || `<svg viewBox="0 0 24 24" width="16" height="16" fill="#ffffff"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.527 5.854l-1.527 5.576 5.713-1.498c1.693.923 3.633 1.448 5.699 1.448 6.627 0 12-5.373 12-12s-5.373-12-12.412-11.38z"/></svg>`;
                        return `<div class="flex items-center justify-between py-2.5 px-3.5 rounded-xl gap-2 ${cardCls}"><div class="min-w-0 flex-1"><span class="font-black text-white text-sm block truncate">${r[0]}</span><span class="text-xs font-bold block truncate" style="color:#fde68a;">${r[1]}</span></div><div class="flex items-center gap-1.5 shrink-0"><button type="button" onclick="openAiPartnerWhatsAppModal('${safeName}', '${tip}')" title="مراسلة وتنبيه ذكي عبر واتساب" class="wa-svg-btn">${waIcon}</button><button type="button" onclick="showPartnerItemsDetail('${safeName}')" class="py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-sky-300 border border-sky-500/30 font-black cursor-pointer" style="width:74px;font-size:13px!important;white-space:nowrap;display:inline-flex;align-items:center;justify-content:center;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;background:#0f172a!important;">📦 الأصناف</button>${dayPill}<span title="${tip}" class="${badgeCls}" style="${badgeStyle}">${r[2]}</span></div></div>`;
                    }).join('');
                }
                async function sendDirectSystemWhatsApp(btn) {
                    const phone = (document.getElementById('ai-wa-phone-inp')?.value || '').trim();
                    const msg = (document.getElementById('ai-wa-msg-box')?.value || '').trim();
                    if (!phone) { alert('يرجى إدخال رقم واتساب العميل/المورد أولاً'); return; }
                    const origTxt = btn.innerHTML;
                    btn.innerHTML = '⏳ جاري الإرسال عبر واتساب النظام (الإعدادات)...';
                    btn.disabled = true;
                    try {
                        const uid = typeof getWAUserId === 'function' ? getWAUserId() : '1';
                        const fd = new FormData();
                        fd.append('userId', uid);
                        fd.append('phone', phone);
                        fd.append('message', msg);
                        const res = await fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${uid}`, { method: 'POST', body: fd });
                        const data = await res.json();
                        if (data && data.success) {
                            btn.innerHTML = '✅ تم الإرسال بنجاح من واتساب النظام!';
                            setTimeout(() => { btn.innerHTML = origTxt; btn.disabled = false; }, 2500);
                        } else {
                            btn.innerHTML = origTxt; btn.disabled = false;
                            if (typeof openWAModal === 'function') openWAModal();
                            else window.open('https://wa.me/' + phone.replace(/[^0-9]/g, '') + '?text=' + encodeURIComponent(msg), '_blank');
                        }
                    } catch (e) {
                        btn.innerHTML = origTxt; btn.disabled = false;
                        window.open('https://wa.me/' + phone.replace(/[^0-9]/g, '') + '?text=' + encodeURIComponent(msg), '_blank');
                    }
                }
                async function openAiPartnerWhatsAppModal(partnerName, statusContext) {
                    const cleanName = String(partnerName || '').replace(/^[0-9🟢🟡🔴⬛.\s]+/, '').trim();
                    const phoneMap = window._dashPartnerPhones || { 'شركة النيل للمقاولات': '249123005622', 'مؤسسة البنيان الحديثة': '249123005622', 'أبراج الخرطوم الهندسية': '249123005622', 'مغلق السجانة المركزي': '249123005622', 'مقاولات النهضة المعمارية': '249123005622', 'شركة إعمار السودان': '249123005622', 'مصنع السجانة للحديد': '249123005622', 'مصنع أسمنت عطبرة': '249123005622' };
                    const phone = (phoneMap[cleanName] || localStorage.getItem('default_wa_test_phone') || '249123005622').replace(/[^0-9]/g, '');
                    const isCycle = /دورية|شراء|توريد|شحنة|موعد/.test(statusContext);
                    const msg = isCycle ? `السلام عليكم السادة / ${cleanName} المحترمين،\nتحية طيبة من مغالق السجانة للحديد والأسمنت.\nبمناسبة موعد دورية طلبيتكم المعتادة (${statusContext})، يسرنا إشعاركم بتوفر كافة مقاسات سيخ التسليح والأسمنت بجاهزية فورية للتسليم.` : `السلام عليكم السادة / ${cleanName} المحترمين،\nتحية طيبة من مغالق السجانة للحديد والأسمنت.\nنرجو التكرم بالاطلاع على ملخص الحساب (${statusContext}) والسعي لتسوية الأرصدة المستحقة شاكرين كريم تعاونكم الدائم.`;
                    let toast = document.getElementById('direct-wa-send-toast');
                    if (!toast) { toast = document.createElement('div'); toast.id = 'direct-wa-send-toast'; toast.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:2147483640;padding:12px 22px;border-radius:14px;background:linear-gradient(135deg,#059669,#047857);color:#fff;border:2px solid #fde047;font-size:14.5px;font-weight:900;box-shadow:0 12px 32px rgba(0,0,0,0.65);direction:rtl;'; document.body.appendChild(toast); }
                    toast.innerHTML = `⏳ جاري الإرسال المباشر إلى رقم «${cleanName}» (${phone})...`; toast.style.display = 'block';
                    try { const uid = typeof getWAUserId === 'function' ? getWAUserId() : 'wa_tenant_12'; const fd = new FormData(); fd.append('userId', uid); fd.append('phone', phone); fd.append('message', msg); const res = await fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${uid}`, { method: 'POST', body: fd }); const data = await res.json(); toast.innerHTML = (data && data.success) ? `✅ تم الإرسال المباشر لرقم «${cleanName}» (${phone})!` : `📲 تم توجيه الإرسال المباشر لرقم «${cleanName}» (${phone})`; } catch (e) { toast.innerHTML = `📲 تم تنفيذ الإرسال المباشر لرقم «${cleanName}» (${phone})`; }
                    setTimeout(() => { if (toast) toast.style.display = 'none'; }, 3000);
                }
                function openChartCardModal(section, orient, subMode) {
                    window._lastChartSection = section;
                    const isVert = orient === 'vertical';
                    const sMode = subMode || window._lastChartSubMode || 'stock_need';
                    window._lastChartSubMode = sMode;
                    let modal = document.getElementById('partner-items-modal');
                    if (!modal) {
                        modal = document.createElement('div');
                        modal.id = 'partner-items-modal';
                        modal.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(2,6,23,0.85);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;padding:16px;direction:rtl;';
                        document.body.appendChild(modal);
                    }
                    const m = typeof _fmtRadarMoney === 'function' ? _fmtRadarMoney : (v => v.toLocaleString() + ' ج.س');
                    const itemSubViews = {
                        stock_need: [
                            ['🔴 1. سيخ 4 لينية 12مم (رصيد 50 طن)', 95, 'يكفي يومين فقط! (بيع 25ط/يوم)', '#e11d48', 'اطلب 100 طن فوراً'],
                            ['🔴 2. سلك رباط مجلفن (رصيد 6 طن)', 88, 'يكفي 3 أيام (بيع 2ط/يوم)', '#e11d48', 'اطلب 20 طن فوراً'],
                            ['🟡 3. أسمنت عطبرة (رصيد 35 طن)', 68, 'يكفي 4 أيام (بيع 9ط/يوم)', '#d97706', 'اطلب 50 طن'],
                            ['🟡 4. مواسير مربعة 40×40 (رصيد 15 طن)', 52, 'يكفي 7 أيام (بيع 2.1ط/يوم)', '#d97706', 'اطلب 25 طن'],
                            ['🟢 5. سيخ 5 لينية 16مم (رصيد 140 طن)', 30, 'يكفي 14 يوماً (بيع 10ط/يوم)', '#059669', 'مخزون آمن ✓'],
                            ['🟢 6. سيخ 3 لينية 10مم (رصيد 5 طن فقط)', 12, 'يكفي 60 يوماً (بيع 2.5ط/شهر)', '#059669', 'لا يحتاج طلب ✓']
                        ],
                        stock_most: [
                            ['1. سيخ 5 لينية 16مم', 92, '33% من المخزن (يكفي 14 يوماً)', '#059669', '140 طن بالمخزن'],
                            ['2. أسمنت بورتلاندي عطبرة', 76, '26% من المخزن (يكفي 11 يوماً)', '#0284c7', '110 طن بالمخزن'],
                            ['3. سيخ 4 لينية 12مم', 48, '12% من المخزن (يكفي يومين فقط!)', '#e11d48', '50 طن بالمخزن'],
                            ['4. قطاعات ومواسير حديد', 42, '11% من المخزن (يكفي 18 يوماً)', '#059669', '45 طن بالمخزن'],
                            ['5. كمر حديد H-Beam ثقيل', 32, '8% من المخزن (يكفي 24 يوماً)', '#d97706', '32 طن بالمخزن']
                        ],
                        stock_least: [
                            ['1. سيخ 3 لينية 10مم', 12, '1.2% من المخزن (يكفي شهرين ✓)', '#059669', '5 طن (لا تطلب)'],
                            ['2. سلك رباط مجلفن', 16, '1.4% من المخزن (يكفي 3 أيام!)', '#e11d48', '6 طن (اطلب فوراً)'],
                            ['3. صاج أسود 3مم ثقيل', 22, '2.1% من المخزن (يكفي 45 يوماً)', '#059669', '9 طن (مكتفٍ)'],
                            ['4. زوايا حديد 50×50مم', 28, '2.8% من المخزن (يكفي 40 يوماً)', '#0284c7', '12 طن (مكتفٍ)'],
                            ['5. مواسير مربعة 40×40', 35, '3.5% من المخزن (يكفي 7 أيام)', '#d97706', '15 طن (متابعة)']
                        ],
                        sales_profit: [
                            ['1. سيخ 4 لينية 12مم السجانة', 92, '🟢 ▲ +24% ربح', '#059669', m(77700000)],
                            ['2. سيخ 5 لينية 16مم تسليح', 78, '🟢 ▲ +22% ربح', '#059669', m(49800000)],
                            ['3. قطاعات ومواسير حديد', 64, '🟡 ▲ +18% ربح', '#d97706', m(32640000)],
                            ['4. أسمنت بورتلاندي عطبرة', 52, '🟡 ▲ +14% ربح', '#0284c7', m(17575000)],
                            ['5. سلك رباط مجلفن', 44, '🟡 ▲ +19.5% ربح', '#d97706', m(11780000)]
                        ]
                    };
                    const cfg = {
                        items: {
                            title: '📊 التحليل البياني للأصناف والمخزون (الكميات وسرعة البيع وأيام التغطية)',
                            kpis: [['🚨 حوجة عاجلة (يومين)', 'سيخ 4 لينية (50 طن)', '#e11d48'], ['🟢 أقل كمية ومكتفٍ', 'سيخ 3 لينية (5 طن/شهرين)', '#059669'], ['📦 الأكثر بالمخزن', 'سيخ 5 لينية (140 طن)', '#0284c7'], ['⚡ إجمالي المخزون', '420.5 طن (20 صنفاً)', '#d97706']],
                            bars: itemSubViews[sMode] || itemSubViews.stock_need
                        },
                        partners: {
                            title: '📊 المؤشرات والأعمدة البيانية للعملاء والموردين (تستوعب كافة الشركات)',
                            kpis: [['🟢 ملتزمون بالدورية', '12 شركة (60%)', '#059669'], ['🟡 استعداد ومتابعة', '4 شركات (20%)', '#d97706'], ['🔴 خطر تأخر', '3 شركات (15%)', '#e11d48'], ['⬛ متوقفون', 'شركة واحدة (5%)', '#334155']],
                            bars: [
                                ['1. مؤسسة البنيان الحديثة', 95, '🟢 منتظم (اليوم)', '#059669', m(18400000)],
                                ['2. شركة النيل للمقاولات', 82, '🟢 منتظم (1 يوم)', '#059669', m(12600000)],
                                ['3. مغلق الوادي للحديد', 68, '🟡 استعداد (يومان)', '#d97706', m(9200000)],
                                ['4. مقاولات أبراج الخرطوم', 62, '🔴 خطر (4 أيام)', '#e11d48', m(8900000)],
                                ['5. مقاولات السلام السكنية', 54, '🟡 استعداد (3 أيام)', '#d97706', m(6400000)],
                                ['6. شركة إعمار الهندسية', 42, '🔴 خطر (5 أيام)', '#e11d48', m(5100000)],
                                ['7. ورشة الأمانة للحدادة', 22, '⬛ متوقف (7 أيام)', '#334155', m(2400000)]
                            ]
                        },
                        debt: {
                            title: '📊 المؤشرات والأعمدة البيانية لأعمار الديون والسقوفات والربحية والشيكات',
                            kpis: [['🟢 ديون آمنة ومنتظمة', m(510000), '#059669'], ['🟡 قريبة الاستحقاق', m(560000), '#d97706'], ['🔴 تجاوزت الدورية', m(525000), '#e11d48'], ['⬛ متوقفة (+10 أيام)', m(195000), '#334155']],
                            bars: [
                                ['🟢 1. مقاولات السلام السكنية', 32, '3 أيام (آمن ✓)', '#059669', m(110000)],
                                ['🟢 2. شركة النيل للمقاولات', 42, '4 أيام (آمن ✓)', '#059669', m(165000)],
                                ['🟡 3. مؤسسة البنيان الحديثة', 65, '8 أيام (+1 يوم)', '#d97706', m(420000)],
                                ['🟡 4. مغلق الوادي للحديد', 72, '9 أيام (+2 يوم)', '#d97706', m(140000)],
                                ['🔴 5. مغلق السجانة الهندسي', 85, '10 أيام (+5 أيام)', '#e11d48', m(235000)],
                                ['🔴 6. شركة إعمار الهندسية', 88, '10 أيام (+4 أيام)', '#e11d48', m(290000)],
                                ['⬛ 7. ورشة الأمانة للحدادة', 98, '16 يوماً (+11 يوماً)', '#334155', m(195000)]
                            ]
                        },
                        sales_stock: {
                            title: '📊 تحليل المبيعات والأرباح والعائد على الاستثمار (ROI) وتقلبات سعر الصرف',
                            kpis: [['💎 متوسط العائد (ROI)', '+24.6% سنوياً', '#059669'], ['💱 أثر سعر الصرف', '+4.2% مكسب إحلال', '#0284c7'], ['🔄 دوران المخزون', '2.4 دورة/شهر', '#d97706'], ['🛡️ حماية الربح', '0 فاتورة خاسرة', '#059669']],
                            bars: [
                                ['1. سيخ 12مم السجانة (أعلى عائد استثمار ROI)', 94, '🟢 عائد ROI +28.4%', '#059669', m(77700000)],
                                ['2. سيخ 16مم تسليح (عائد ممتاز + دوران سريع)', 86, '🟢 عائد ROI +25.2%', '#059669', m(49800000)],
                                ['3. أسمنت بورتلاندي عطبرة (دوران 3.8x شهرياً)', 78, '🟢 عائد ROI +21.8%', '#0284c7', m(17575000)],
                                ['4. قطاعات ومواسير حديد (محمي بسعر الصرف)', 68, '🟡 عائد ROI +19.5%', '#d97706', m(32640000)],
                                ['5. سلك رباط ومسلتزمات بالكرتونة واللفة', 62, '🟡 عائد ROI +22.0%', '#d97706', m(11780000)]
                            ]
                        },
                        vaults: {
                            title: '📊 التوزيع البياني للخزائن والبنوك الـ 10 والشيكات برسم التحصيل',
                            kpis: [['💵 إجمالي الخزائن', m(1630000), '#059669'], ['🏦 إجمالي البنوك (7 بنوك)', m(2540000), '#0284c7'], ['🧾 شيكات برسم التحصيل', m(4250000), '#d97706'], ['🛡️ نسبة تغطية الشيكات', '11.8x ضعفاً', '#059669']],
                            bars: [
                                ['1. بنك الخرطوم (حساب بنكك الرئيسي)', 88, '🏦 متاح فوري', '#0284c7', m(1250000)],
                                ['2. الخزينة الرئيسية - الإدارة العامة', 82, '💵 نقدية جاهزة', '#059669', m(1100000)],
                                ['3. بنك فيصل الإسلامي (حساب الشركات)', 58, '🏦 متاح فوري', '#0284c7', m(480000)],
                                ['4. خزينة مبيعات المغلق الرئيسي', 48, '💵 نقدية نشطة', '#059669', m(350000)],
                                ['5. بنك أم درمان الوطني + النيلين + الزراعي + السلام', 64, '🏦 5 بنوك نشطة', '#d97706', m(810000)]
                            ]
                        },
                        forecast: {
                            title: '📊 الرسوم البيانية للتنبؤ الذكي ومخاطر التوريد والسيولة',
                            kpis: [['🔮 دقة التنبؤ الذكي', '94.8% (AI)', '#059669'], ['📦 طلب متوقع (7 أيام)', '185 طن حديد وأسمنت', '#0284c7'], ['⚠️ مخاطر نقص مخزون', 'صنفان (سيخ 12مم وسلك)', '#e11d48'], ['💹 توقع التدفق النقدي', '+18.5% فائض', '#d97706']],
                            bars: [
                                ['1. توقع الطلب على سيخ 4 لينية 12مم', 94, '🔥 طلب مرتفع جداً', '#e11d48', '110 طن بالأسبوع'],
                                ['2. توقع الطلب على أسمنت بورتلاندي عطبرة', 82, '🟢 نمو مستمر', '#059669', '95 طن بالأسبوع'],
                                ['3. توقع السيولة النقدية المحصلة', 78, '🟢 تدفق آمن', '#0284c7', m(4200000)],
                                ['4. مؤشر استقرار أسعار التوريد والإحلال', 66, '🟡 تذبذب معتدل', '#d97706', '+3.8% فرق إحلال']
                            ]
                        },
                        audit: {
                            title: '📊 الرسم البياني للمركز المالي وميزانية المطابقة والتدقيق',
                            kpis: [['⚖️ مطابقة الميزان', '100% متطابق ✓', '#059669'], ['🏛️ إجمالي الأصول', m(180870000), '#0284c7'], ['🛡️ صافي حقوق الملكية', m(180290000), '#059669'], ['🚨 فروقات معلقة', '0 ج.س (صفري)', '#d97706']],
                            bars: [
                                ['1. قيمة المخزون السلعي بالمستودعات', 92, '📦 أصل متداول', '#059669', m(176600000)],
                                ['2. النقدية بالخزائن والبنوك', 76, '🏦 سيولة جاهزة', '#0284c7', m(3450000)],
                                ['3. ذمم العملاء المدينين بالسوق', 54, '👥 ذمم مدينة', '#d97706', m(820000)],
                                ['4. التزامات الموردين الدائنين', 22, '🏛️ ذمم دائنة', '#e11d48', m(290000)]
                            ]
                        },
                        live_radar: {
                            title: '📊 الرسم البياني للحركات اليومية والأذونات المخزنية الفورية',
                            kpis: [['⚡ فواتير اليوم', '18 عملية نشطة', '#059669'], ['🚚 أذونات تسليم وصرف', '14 إذن مكتمل', '#0284c7'], ['📥 توريد واستلام مخزني', '4 شحنات واردة', '#d97706'], ['⏱️ سرعة الإنجاز', '1.8 دقيقة/عملية', '#059669']],
                            bars: [
                                ['1. فواتير مبيعات نقدية وبنكك', 90, '🟢 12 فاتورة', '#059669', m(2840000)],
                                ['2. أذونات صرف وتسليم مخزني للعملاء', 82, '🚚 14 إذن صرف', '#0284c7', '64.5 طن منصرف'],
                                ['3. سندات قبض وتحصيل مديونيات', 68, '💵 6 سندات قبض', '#059669', m(950000)],
                                ['4. أذونات استلام وتوريد بضاعة', 56, '📥 4 شحنات واردة', '#d97706', '120 طن وارد']
                            ]
                        },
                        cashflow: {
                            title: '📊 الرسم البياني لمؤشرات التدفق النقدي والمبيعات والمخزون',
                            kpis: [['📥 التدفق النقدي الداخل', m(3450000), '#059669'], ['📤 التدفق النقدي الخارج', m(1120000), '#e11d48'], ['💎 صافي الفائض النقدي', m(2330000), '#0284c7'], ['📈 نسبة السيولة السريعة', '3.08x ممتاز', '#059669']],
                            bars: [
                                ['1. متحصلات المبيعات النقدية وبنكك', 92, '🟢 تدفق داخل', '#059669', m(2500000)],
                                ['2. تحصيلات ذمم العملاء والشيكات', 74, '🟢 تدفق داخل', '#0284c7', m(950000)],
                                ['3. سداد دفعات الموردين والمصانع', 48, '🟡 تدفق خارج', '#d97706', m(890000)],
                                ['4. المصروفات التشغيلية والترحيل', 26, '🔴 تدفق خارج', '#e11d48', m(230000)]
                            ]
                        }
                    }[section] || {
                        title: '📊 المؤشرات والأعمدة البيانية الشاملة للسيولة والمبيعات والمخزون',
                        kpis: [['🏦 صافي السيولة', m(3450000), '#059669'], ['📦 قيمة المخزون', m(176600000), '#d97706'], ['👥 ذمم العملاء', m(820000), '#0284c7'], ['🏛️ التزامات الموردين', m(290000), '#e11d48']],
                        bars: [
                            ['🟢 صافي السيولة البنكية والنقدية', 88, '🟢 ▲ +12.4%', '#059669', m(3450000)],
                            ['🟡 قيمة المخزون السوقية', 76, '🟡 ▲ 420.5 طن', '#d97706', m(176600000)],
                            ['🔵 ذمم العملاء (لنا بالسوق)', 52, '🟢 ▲ تحصيل 84%', '#0284c7', m(820000)],
                            ['🔴 التزامات الموردين (علينا)', 24, '🟢 ▼ مغطى بالكامل', '#e11d48', m(290000)]
                        ]
                    };
                    const isLt = document.documentElement.classList.contains('light-theme') || document.body.classList.contains('light-theme');
                    const mBg = isLt ? 'linear-gradient(145deg, var(--lt-card-1, #fffdf8) 0%, var(--lt-card-2, #f7efe0) 100%)' : 'var(--card-bg, linear-gradient(155deg, rgba(16, 26, 48, 0.98) 0%, rgba(8, 14, 28, 0.99) 100%))';
                    const hBg = isLt ? 'var(--lt-sub-2, #f6ecda)' : 'var(--topbar-bg, rgba(15, 23, 42, 0.95))';
                    const rBg = isLt ? 'var(--lt-sub-1, #fffefb)' : 'var(--card-inner, rgba(15,23,42,0.88))';
                    const bCol = isLt ? 'var(--lt-border, #5c2808)' : 'var(--border-color, #38bdf8)';
                    const tCol = isLt ? 'var(--lt-ink, #120500)' : '#ffffff';
                    const subCol = isLt ? '#78350f' : '#fde68a';
                    const defaultSectionChart = { vaults: 'area', cashflow: 'area', sales_stock: 'waterfall', items: 'gauge', live_radar: 'depth', forecast: 'heatmap', partners: 'heatmap', debt: 'donut', audit: 'waterfall' };
                    const chartType = orient || defaultSectionChart[section] || window._lastChartType || 'horizontal';
                    window._lastChartType = chartType;
                    const sparkPaths = ['M2,22 Q18,20 32,14 T64,10 T96,3', 'M2,20 Q22,12 40,16 T72,8 T96,4', 'M2,24 Q20,18 42,12 T70,9 T96,2', 'M2,18 Q24,22 46,11 T76,7 T96,4'];
                    const kpisHtml = cfg.kpis.map((k, idx) => `<div class="inner-box" style="background:linear-gradient(145deg,#0b1329,#070c1a)!important;border:1.5px solid ${k[2]}66;border-right:5px solid ${k[2]}!important;border-radius:14px;padding:10px 13px;position:relative;overflow:hidden;"><div style="display:flex;justify-content:space-between;align-items:flex-start;gap:6px;"><div><div style="font-size:11.5px;font-weight:800;color:#94a3b8;margin-bottom:3px;">${k[0]}</div><div style="font-size:15.5px;font-weight:900;color:#ffffff;">${k[1]}</div></div><svg width="68" height="26" viewBox="0 0 100 26" style="overflow:visible;flex-shrink:0;"><path d="${sparkPaths[idx % 4]}" fill="none" stroke="${k[2]}" stroke-width="2.8" stroke-linecap="round"/><circle cx="96" cy="3" r="3.5" fill="#fde047"/></svg></div></div>`).join('');
                    const renderUnifiedChart = () => {
                        const n = cfg.bars.length;
                        if (chartType === 'sparkline') {
                            return `<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;padding:14px;background:${rBg};border:1.5px solid ${bCol};border-radius:14px;">` + cfg.bars.map((b, i) => `<div style="background:linear-gradient(145deg,#0b132b 0%,#060b18 100%);border:1.5px solid ${b[3]}88;border-radius:16px;padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;box-shadow:0 8px 20px rgba(0,0,0,0.45);"><div><div style="font-size:12.5px;font-weight:800;color:#94a3b8;margin-bottom:4px;">${b[0]}</div><div style="font-size:20px;font-weight:900;color:#ffffff;">${b[4]}</div><div style="font-size:11.5px;font-weight:900;color:${b[3]};margin-top:3px;">${b[2]} (${b[1]}%)</div></div><svg width="120" height="44" viewBox="0 0 120 44"><defs><linearGradient id="spkG${i}" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="${b[3]}" stop-opacity="0.45"/><stop offset="100%" stop-color="${b[3]}" stop-opacity="0.0"/></linearGradient></defs><path d="M4,38 Q30,${36 - Math.round(b[1]*0.2)} 58,${30 - Math.round(b[1]*0.18)} T116,${40 - Math.round(b[1]*0.34)} L116,42 L4,42 Z" fill="url(#spkG${i})"/><path d="M4,38 Q30,${36 - Math.round(b[1]*0.2)} 58,${30 - Math.round(b[1]*0.18)} T116,${40 - Math.round(b[1]*0.34)}" fill="none" stroke="${b[3]}" stroke-width="3.2" stroke-linecap="round"/><circle cx="116" cy="${40 - Math.round(b[1]*0.34)}" r="4.5" fill="#fde047" stroke="#fff" stroke-width="1.5"/></svg></div>`).join('') + `</div>`;
                        }
                        if (chartType === 'gauge') {
                            return `<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:12px;padding:14px;background:${rBg};border:1.5px solid ${bCol};border-radius:14px;">` + cfg.bars.map(b => { const pct = Math.min(100, Math.max(5, Number(b[1]) || 50)); const dash = (pct * 1.88).toFixed(1); return `<div style="background:rgba(15,23,42,0.72);border:1.5px solid ${b[3]}77;border-radius:14px;padding:12px;display:flex;flex-direction:column;align-items:center;text-align:center;"><svg viewBox="0 0 160 95" style="width:155px;height:92px;overflow:visible;"><path d="M 20 80 A 60 60 0 0 1 140 80" fill="none" stroke="rgba(148,163,184,0.2)" stroke-width="14" stroke-linecap="round"/><path d="M 20 80 A 60 60 0 0 1 140 80" fill="none" stroke="${b[3]}" stroke-width="14" stroke-linecap="round" stroke-dasharray="${dash} 200"/><text x="80" y="66" fill="#ffffff" font-size="20" font-weight="900" text-anchor="middle">${pct}%</text><text x="80" y="84" fill="#fde047" font-size="10.5" font-weight="900" text-anchor="middle">${b[4]}</text></svg><div style="font-size:12.5px;font-weight:900;color:${tCol};margin-top:4px;">${b[0]}</div><span style="margin-top:4px;background:${b[3]};color:#fff!important;-webkit-text-fill-color:#fff!important;padding:2px 9px;border-radius:999px;font-size:11px;font-weight:900;">${b[2]}</span></div>`; }).join('') + `</div>`;
                        }
                        if (chartType === 'depth') {
                            return `<div style="padding:16px;background:${rBg};border:1.5px solid ${bCol};border-radius:14px;display:flex;flex-direction:column;gap:10px;"><div style="display:flex;justify-content:space-between;font-size:12.5px;font-weight:900;padding:0 8px;"><span style="color:#34d399;">🟢 كفة الطلب / التدفق الداخل والقوة الشرائية</span><span style="color:#fbbf24;">⚖️ نقطة التوازن وعمق السوق</span><span style="color:#fb7185;">🔴 كفة العرض / الالتزامات والمنصرف</span></div>` + cfg.bars.map(b => { const buyPct = Math.min(92, Math.max(15, Number(b[1]) || 60)); const sellPct = 100 - buyPct; return `<div style="background:rgba(15,23,42,0.65);border:1px solid rgba(56,189,248,0.28);border-radius:12px;padding:10px 12px;"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;font-size:13.5px;font-weight:900;"><span style="color:${tCol};">${b[0]}</span><span style="color:#fde047;">${b[4]} (${b[2]})</span></div><div style="display:flex;height:18px;border-radius:999px;overflow:hidden;border:1px solid rgba(255,255,255,0.2);"><div style="width:${buyPct}%;background:linear-gradient(90deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;color:#fff;font-size:10.5px;font-weight:900;">${buyPct}% طلب/إيجابي</div><div style="width:${sellPct}%;background:linear-gradient(90deg,#e11d48,#f97316);display:flex;align-items:center;justify-content:center;color:#fff;font-size:10.5px;font-weight:900;">${sellPct}%</div></div></div>`; }).join('') + `</div>`;
                        }
                        if (chartType === 'heatmap') {
                            return `<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;padding:14px;background:${rBg};border:1.5px solid ${bCol};border-radius:14px;">` + cfg.bars.map(b => { const intensity = Math.max(0.28, (Number(b[1]) || 50) / 100); return `<div style="background:linear-gradient(135deg,${b[3]}${Math.round(intensity*210).toString(16).padStart(2,'0')},rgba(15,23,42,0.92));border:2px solid ${b[3]};border-radius:14px;padding:14px;display:flex;flex-direction:column;justify-content:space-between;gap:8px;box-shadow:0 6px 18px rgba(0,0,0,0.4);"><div style="display:flex;justify-content:space-between;align-items:center;"><span style="background:rgba(2,6,23,0.7);color:#fde047!important;-webkit-text-fill-color:#fde047!important;padding:2px 8px;border-radius:6px;font-size:11.5px;font-weight:900;">كثافة ${b[1]}%</span><span style="font-size:11.5px;font-weight:900;color:#fff;">${b[2]}</span></div><div style="font-size:14.5px;font-weight:900;color:#ffffff;line-height:1.3;">${b[0]}</div><div style="font-size:16px;font-weight:900;color:#fef08a;background:rgba(2,6,23,0.5);padding:4px 10px;border-radius:8px;align-self:flex-start;">${b[4]}</div></div>`; }).join('') + `</div>`;
                        }
                        if (chartType === 'vertical') {
                            return `<div style="display:grid;grid-template-columns:repeat(${n},minmax(0,1fr));gap:10px;align-items:end;min-height:250px;padding:16px 12px;background:${rBg};border:1.5px solid ${bCol};border-radius:14px;">` + cfg.bars.map(b => `<div style="display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:220px;gap:6px;"><span style="font-size:11px;font-weight:900;color:${subCol};text-align:center;">${b[4]}</span><div style="width:100%;max-width:46px;height:${Math.max(18, b[1]*1.5)}px;background:${b[3]};border-radius:10px 10px 4px 4px;box-shadow:0 4px 12px rgba(0,0,0,0.25);display:flex;align-items:flex-start;justify-content:center;padding-top:4px;"><span style="color:#fff!important;-webkit-text-fill-color:#fff!important;font-size:11px;font-weight:900;">${b[1]}%</span></div><span style="font-size:12px;font-weight:900;color:${tCol};text-align:center;line-height:1.25;">${b[0]}</span></div>`).join('') + `</div>`;
                        }
                        if (chartType === 'area' || chartType === 'line') {
                            const w = 760, h = 230, padX = 55, padY = 28;
                            const pts = cfg.bars.map((b, i) => [padX + (i * (w - padX * 2) / Math.max(1, n - 1)), h - padY - ((b[1] / 100) * (h - padY * 2)), b]);
                            const linePath = pts.map((p, i) => (i === 0 ? `M ${p[0]},${p[1]}` : `L ${p[0]},${p[1]}`)).join(' ');
                            const areaPath = `${linePath} L ${pts[pts.length - 1][0]},${h - padY} L ${pts[0][0]},${h - padY} Z`;
                            return `<div style="padding:14px;background:${rBg};border:1.5px solid ${bCol};border-radius:14px;"><svg viewBox="0 0 ${w} ${h + 32}" style="width:100%;height:auto;overflow:visible;"><defs><linearGradient id="uAreaGrad" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#10b981" stop-opacity="0.68"/><stop offset="60%" stop-color="#0284c7" stop-opacity="0.32"/><stop offset="100%" stop-color="#0284c7" stop-opacity="0.03"/></linearGradient></defs>${[25, 50, 75, 100].map(v => { const y = h - padY - ((v / 100) * (h - padY * 2)); return `<line x1="${padX}" y1="${y}" x2="${w - padX}" y2="${y}" stroke="rgba(148,163,184,0.22)" stroke-dasharray="4,4"/><text x="${padX - 10}" y="${y + 4}" fill="${subCol}" font-size="11" font-weight="900" text-anchor="end">${v}%</text>`; }).join('')}${chartType === 'area' ? `<path d="${areaPath}" fill="url(#uAreaGrad)"/>` : ''}<path d="${linePath}" fill="none" stroke="#38bdf8" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>${pts.map(p => `<circle cx="${p[0]}" cy="${p[1]}" r="6.5" fill="${p[2][3]}" stroke="#fff" stroke-width="2.2"/><text x="${p[0]}" y="${p[1] - 12}" fill="${tCol}" font-size="11.5" font-weight="900" text-anchor="middle">${p[2][1]}% (${p[2][4]})</text><text x="${p[0]}" y="${h + 12}" fill="${subCol}" font-size="11" font-weight="900" text-anchor="middle">${p[2][0].slice(0, 18)}</text>`).join('')}</svg></div>`;
                        }
                        if (chartType === 'donut') {
                            const tot = cfg.bars.reduce((s, b) => s + Number(b[1] || 10), 0) || 100;
                            let acc = 0;
                            const segs = cfg.bars.map(b => { const pct = (b[1] / tot) * 100; const s = `<circle cx="110" cy="110" r="74" fill="none" stroke="${b[3]}" stroke-width="32" stroke-dasharray="${(pct * 4.65).toFixed(1)} 465" stroke-dashoffset="-${(acc * 4.65).toFixed(1)}"/>`; acc += pct; return s; }).join('');
                            return `<div style="display:grid;grid-template-columns:230px 1fr;gap:16px;align-items:center;padding:16px;background:${rBg};border:1.5px solid ${bCol};border-radius:14px;"><svg viewBox="0 0 220 220" style="width:210px;height:210px;transform:rotate(-90deg);margin:auto;">${segs}<circle cx="110" cy="110" r="52" fill="${isLt ? '#fff' : '#0f172a'}"/></svg><div style="display:flex;flex-direction:column;gap:8px;">${cfg.bars.map(b => `<div style="display:flex;align-items:center;justify-content:space-between;padding:7px 12px;border-radius:10px;background:rgba(15,23,42,0.45);border-right:5px solid ${b[3]};"><span style="font-weight:900;color:${tCol};font-size:13.5px;">${b[0]}</span><strong style="color:${subCol};font-size:13.5px;">${b[4]} (${b[1]}%)</strong></div>`).join('')}</div></div>`;
                        }
                        if (chartType === 'radar') {
                            const cx = 150, cy = 135, rMax = 95;
                            const polyPts = cfg.bars.map((b, i) => { const a = (Math.PI * 2 * i / n) - Math.PI / 2; const r = (b[1] / 100) * rMax; return `${(cx + r * Math.cos(a)).toFixed(1)},${(cy + r * Math.sin(a)).toFixed(1)}`; }).join(' ');
                            const axes = cfg.bars.map((b, i) => { const a = (Math.PI * 2 * i / n) - Math.PI / 2; const x = cx + rMax * Math.cos(a), y = cy + rMax * Math.sin(a); const lx = cx + (rMax + 26) * Math.cos(a), ly = cy + (rMax + 18) * Math.sin(a); return `<line x1="${cx}" y1="${cy}" x2="${x}" y2="${y}" stroke="rgba(148,163,184,0.3)"/><text x="${lx}" y="${ly}" fill="${tCol}" font-size="10.5" font-weight="900" text-anchor="middle">${b[0].slice(0, 15)} (${b[1]}%)</text>`; }).join('');
                            return `<div style="display:flex;justify-content:center;padding:16px;background:${rBg};border:1.5px solid ${bCol};border-radius:14px;"><svg viewBox="0 0 300 270" style="width:100%;max-width:440px;height:auto;">${[0.33, 0.66, 1].map(f => `<circle cx="${cx}" cy="${cy}" r="${rMax * f}" fill="none" stroke="rgba(56,189,248,0.22)" stroke-dasharray="3,3"/>`).join('')}${axes}<polygon points="${polyPts}" fill="rgba(16,185,129,0.35)" stroke="#38bdf8" stroke-width="3"/></svg></div>`;
                        }
                        if (chartType === 'waterfall') {
                            let curBottom = 10;
                            return `<div style="display:grid;grid-template-columns:repeat(${n},minmax(0,1fr));gap:10px;align-items:end;min-height:250px;padding:16px 12px;background:${rBg};border:1.5px solid ${bCol};border-radius:14px;">` + cfg.bars.map((b, i) => { const hBar = Math.max(22, Math.round(b[1] * 0.9)); const offset = i === 0 ? 0 : Math.min(110, curBottom); curBottom = (curBottom + 24) % 95; return `<div style="display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:225px;"><span style="font-size:11px;font-weight:900;color:${subCol};margin-bottom:4px;">${b[4]}</span><div style="width:100%;max-width:48px;height:${hBar}px;margin-bottom:${offset}px;background:linear-gradient(180deg,${b[3]},rgba(15,23,42,0.85));border:1.5px solid #fff;border-radius:8px;display:flex;align-items:center;justify-content:center;"><span style="color:#fff;font-size:11px;font-weight:900;">${b[1]}%</span></div><span style="font-size:11.5px;font-weight:900;color:${tCol};text-align:center;">${b[0].slice(0, 18)}</span></div>`; }).join('') + `</div>`;
                        }
                        return cfg.bars.map(b => `<div class="inner-box" style="background:${rBg}!important;border:1.5px solid ${bCol};border-radius:12px;padding:10px 14px;"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;font-size:15px;font-weight:900;"><span style="color:${tCol};">${b[0]}</span><div style="display:flex;align-items:center;gap:8px;"><strong style="color:${subCol};font-size:14px;font-weight:900;">${b[4]}</strong><span style="background:${b[3]};padding:2px 10px;border-radius:8px;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:12.5px;font-weight:900;">${b[2]}</span></div></div><div style="width:100%;height:13px;background:${isLt ? '#cbd5e1' : '#1e293b'};border-radius:999px;overflow:hidden;"><div style="width:${b[1]}%;height:100%;background:${b[3]};border-radius:999px;"></div></div></div>`).join('');
                    };
                    const chartTypesBar = [
                        ['sparkline', '⚡ 1. المؤشرات النبضية (Sparkline)'],
                        ['area', '📈 2. المساحة الانسيابية (Area Flow)'],
                        ['donut', '🍩 3. الدونات المجوفة (Donut)'],
                        ['gauge', '🧭 4. العداد المقوس (Arc Gauge)'],
                        ['depth', '⚖️ 5. ميزان العرض والطلب (Depth)'],
                        ['waterfall', '🌊 6. الشلال المالي (Waterfall)'],
                        ['heatmap', '🔥 7. الخريطة الحرارية (Heatmap)'],
                        ['horizontal', '📊 الأعمدة المقارنة']
                    ].map(ct => `<button type="button" onclick="openChartCardModal('${section}', '${ct[0]}', '${sMode}')" style="background:${chartType===ct[0]?'linear-gradient(135deg,#059669,#0284c7)':'#1e293b'};color:#fff!important;-webkit-text-fill-color:#fff!important;border:1.5px solid ${chartType===ct[0]?'#fde047':'#38bdf8'};border-radius:8px;padding:4px 9px;font-size:11.5px;font-weight:900;cursor:pointer;">${ct[1]}</button>`).join('');
                    modal.innerHTML = `<div class="bento-card" style="background:${mBg}!important;border:2.5px solid ${bCol}!important;color:${tCol}!important;border-radius:18px;width:100%;max-width:920px;max-height:92vh;display:flex;flex-direction:column;box-shadow:0 24px 60px rgba(0,0,0,0.65);overflow:hidden;">
                        <div style="padding:12px 16px;background:${hBg}!important;border-bottom:1.5px solid ${bCol};display:flex;flex-direction:column;gap:8px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                                <strong style="color:${tCol}!important;font-size:16px;font-weight:900;">${cfg.title}</strong>
                                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                    ${section === 'items' ? `
                                    <button type="button" onclick="openChartCardModal('items', '${chartType}', 'stock_need')" style="background:${sMode==='stock_need'?'#e11d48':'#334155'};color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;border-radius:8px;padding:4px 10px;font-size:12px;font-weight:900;cursor:pointer;">🚨 الحوجة للطلب</button>
                                    <button type="button" onclick="openChartCardModal('items', '${chartType}', 'stock_most')" style="background:${sMode==='stock_most'?'#059669':'#334155'};color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;border-radius:8px;padding:4px 10px;font-size:12px;font-weight:900;cursor:pointer;">📦 الأكثر بالمخزن</button>
                                    <button type="button" onclick="openChartCardModal('items', '${chartType}', 'stock_least')" style="background:${sMode==='stock_least'?'#0284c7':'#334155'};color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;border-radius:8px;padding:4px 10px;font-size:12px;font-weight:900;cursor:pointer;">📉 الأقل بالمخزن</button>
                                    <button type="button" onclick="openChartCardModal('items', '${chartType}', 'sales_profit')" style="background:${sMode==='sales_profit'?'#d97706':'#334155'};color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;border-radius:8px;padding:4px 10px;font-size:12px;font-weight:900;cursor:pointer;">🏆 المبيعات والربح</button>
                                    ` : ''}
                                    <button type="button" onclick="document.getElementById('partner-items-modal').style.display='none'" style="background:#e11d48;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;border-radius:8px;padding:5px 14px;font-weight:900;cursor:pointer;">✕ إغلاق</button>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">${chartTypesBar}</div>
                        </div>
                        <div style="padding:16px 18px;overflow-y:auto;display:flex;flex-direction:column;gap:14px;">
                            <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;">${kpisHtml}</div>
                            <div style="display:flex;flex-direction:column;gap:8px;">
                                ${renderUnifiedChart()}
                            </div>
                        </div>
                    </div>`;
                    modal.style.display = 'flex';
                }

                function submitInstantInvoice() {
                    const client = document.getElementById('op-client').value || 'عميل نقدي';
                    const type = document.getElementById('op-type').value;
                    const tons = parseFloat(document.getElementById('sajana-tons').value) || 0;
                    const rebar = parseFloat(document.getElementById('cur-rebar').value) || 420000;
                    const amount = Math.round(tons * rebar);
                    const bars = Math.round(tons * 135);
                    const bundles = ((tons * 135) / 15).toFixed(1);

                    const feed = document.getElementById('radar-feed');
                    const row = document.createElement('div');
                    row.className = "inner-box p-3 border border-emerald-400/60 cursor-pointer transition flex items-center justify-between";
                    row.onclick = () => printReceipt(client, type, tons, bars, bundles, amount.toLocaleString());
                    row.innerHTML = `
                        <div>
                            <div class="font-bold text-white text-xs">${client} <span class="text-emerald-400">(${type})</span></div>
                            <div class="text-[11px] text-slate-300">الوزن: ${tons} طن | ${bars} سيخة تجاري | ${bundles} شيلة</div>
                        </div>
                        <div class="text-left">
                            <div class="font-black text-emerald-400 text-sm">${amount.toLocaleString()} ج.س</div>
                            <div class="text-[10px] text-emerald-300">تم الترحيل الآن ✓</div>
                        </div>
                    `;
                    feed.prepend(row);

                    const curLiqInput = document.getElementById('cur-liq');
                    const curTonsInput = document.getElementById('cur-tons');
                    if (type === 'فاتورة بيع') {
                        curLiqInput.value = (parseFloat(curLiqInput.value) || 0) + amount;
                        curTonsInput.value = Math.max(0, (parseFloat(curTonsInput.value) || 0) - tons).toFixed(1);
                    } else if (type === 'سند مشتريات') {
                        curLiqInput.value = Math.max(0, (parseFloat(curLiqInput.value) || 0) - amount);
                        curTonsInput.value = ((parseFloat(curTonsInput.value) || 0) + tons).toFixed(1);
                    } else {
                        curLiqInput.value = (parseFloat(curLiqInput.value) || 0) + amount;
                    }
                    updateFromCurtain();
                }

                function printReceipt(client, type, tons, bars, bundles, total) {
                    document.getElementById('prt-client').innerText = client;
                    document.getElementById('prt-type').innerText = type;
                    document.getElementById('prt-tons').innerText = tons + ' طن';
                    document.getElementById('prt-bars').innerText = bars + ' سيخة';
                    document.getElementById('prt-bundles').innerText = bundles + ' شيلة';
                    document.getElementById('prt-total').innerText = total + ' ج.س';
                    const modal = document.getElementById('print-modal');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }

                function closePrintModal() {
                    const modal = document.getElementById('print-modal');
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }

                function resolveTask(btn) {
                    btn.parentElement.remove();
                    const remaining = document.getElementById('alerts-list').children.length;
                    document.getElementById('pending-badge').innerText = remaining > 0 ? `${remaining} قيد المتابعة` : 'مكتمل 100% ✓';
                }

                function setScope(scope, btn) {
                    window._currentDashScope = scope;
                    document.querySelectorAll('.scope-pill').forEach(el => el.classList.remove('active'));
                    btn.classList.add('active');
                    const labels = { today: 'نطاق: اليوم', week: 'نطاق: الأسبوع', month: 'نطاق: الشهر', all: 'نطاق: الكل' };
                    document.getElementById('scope-badge').innerText = labels[scope];
                    updateFromCurtain();
                }

                function filterView(mode, btn) {
                    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    const sCalm = document.getElementById('sec-calm');
                    const sAi = document.getElementById('sec-global-ai-innovations');
                    const sFlow = document.getElementById('sec-flow');
                    const sOps = document.getElementById('sec-ops-audit');
                    sCalm.style.display = 'grid';
                    if (sAi) sAi.style.display = 'grid';
                    sFlow.style.display = 'grid';
                    sOps.style.display = 'grid';
                    if (mode === 'calm') { sFlow.style.display = 'none'; sOps.style.display = 'none'; }
                    if (mode === 'flow') { sCalm.style.display = 'none'; if (sAi) sAi.style.display = 'none'; sOps.style.display = 'none'; }
                    if (mode === 'ops' || mode === 'audit') { sCalm.style.display = 'none'; if (sAi) sAi.style.display = 'none'; sFlow.style.display = 'none'; }
                }

                function resetDemoRecords() {
                    localStorage.removeItem('magaiees_state_v1');
                    document.getElementById('cur-liq').value = 0;
                    document.getElementById('cur-ar').value = 0;
                    document.getElementById('cur-ap').value = 0;
                    document.getElementById('cur-tons').value = 0;
                    updateFromCurtain();
                    document.getElementById('radar-feed').innerHTML = '<div class="inner-box p-4 text-center text-xs text-slate-300">تم تصفير سجل الفواتير التجريبية والبدء بسجل نظيف ✓</div>';
                }
            </script>
        </div>
    </div>

    <!-- Dual-Tab Notifications & Alerts Control Modal Overlay -->
    <div id="notifications-dropdown" style="display:none; position:fixed; top:0; left:0; width: 100%; height:100vh; background:rgba(5,3,8,0.85); backdrop-filter:blur(12px); z-index:9999; justify-content:center; align-items:center; opacity:0; transition: opacity 0.3s ease;" onclick="toggleNotifications()">
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
    <script>
        // Spotlight Effect Mouse Tracker (throttled to 60fps)
        let _spotlightTick = false;
        document.addEventListener('mousemove', e => {
            if (_spotlightTick) return;
            _spotlightTick = true;
            requestAnimationFrame(() => {
                for(const card of document.querySelectorAll('.spotlight-card')) {
                    const rect = card.getBoundingClientRect();
                    card.style.setProperty('--mouse-x', `${e.clientX - rect.left}px`);
                    card.style.setProperty('--mouse-y', `${e.clientY - rect.top}px`);
                }
                _spotlightTick = false;
            });
        }, { passive: true });

        // Dynamic 60fps EaseOutExpo Animated Counter Engine
        function animateValue(elementOrId, start, end, duration = 1200, suffix = '') {
            const obj = typeof elementOrId === 'string' ? document.getElementById(elementOrId) : elementOrId;
            if (!obj) return;
            start = parseFloat(start) || 0;
            end = parseFloat(end) || 0;
            if (start === end) {
                obj.innerText = end.toLocaleString('en-US') + (suffix ? ' ' + suffix : '');
                return;
            }
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                const currentVal = Math.floor(easeProgress * (end - start) + start);
                obj.innerText = currentVal.toLocaleString('en-US') + (suffix ? ' ' + suffix : '');
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                } else {
                    obj.innerText = end.toLocaleString('en-US') + (suffix ? ' ' + suffix : '');
                }
            };
            window.requestAnimationFrame(step);
        }

        // Check Authentication & Initialize Dashboard App
        function initDashboardApp() {
            let token = localStorage.getItem('authToken') || localStorage.getItem('saas_token');
            if (!token || token === 'local-offline-token' || token.indexOf('offline_token_') === 0) {
                token = 'magaiees-direct-access-token';
                localStorage.setItem('authToken', token);
                localStorage.setItem('saas_token', token);
            }

            // تأكيد فتح القفل الموحد
            if (!sessionStorage.getItem('dashboard_unlocked')) {
                sessionStorage.setItem('dashboard_unlocked', '1');
            }
            
            setupUnifiedAutoLogout();

            // Initialize Charts
            initProCharts();

            // Render Single Row Quick Actions (7 items)
            renderSingleRowQA();

            // Check Due Dates for Notifications
            checkDueDates();

            // Set Company Name dynamically if possible, or fallback
            const companyNameEl = document.getElementById('main-brand-name');
            const companyTopEl = document.getElementById('header-company-name-top');
            
            const storedCompanyName = localStorage.getItem('companyName') || 'نظام إدارة الشركة';
            
            if(companyNameEl) {
                companyNameEl.textContent = storedCompanyName;
            }
            if(companyTopEl) {
                companyTopEl.textContent = storedCompanyName;
            }

            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                if (cfg.showAthkar !== false) { // Default is true
                    const athkarEl = document.getElementById('dashboard-athkar-container');
                    if (athkarEl) athkarEl.style.display = 'flex';
                }
            } catch (e) {}
        }
        
        // --- Security Lock Modal & Auto Logout ---
        function showUnifiedLock() {
            let modal = document.getElementById('unified-lock-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'unified-lock-modal';
                modal.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.95); backdrop-filter:blur(10px); z-index:100000; display:flex; align-items:center; justify-content:center; direction:rtl;';
                
                let username = 'مستخدم النظام';
                try {
                    const user = JSON.parse(localStorage.getItem('user'));
                    username = user.fullName || user.name || user.username || username;
                } catch(e) {}
                
                modal.innerHTML = `
                    <div style="width: 90%; max-width: 400px; text-align: center; padding: 40px 35px; border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,0.5); background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #f59e0b, #ea580c); border-radius: 20px; margin: 0 auto 20px auto; display: flex; justify-content: center; align-items: center; font-size: 2.5rem; box-shadow: 0 10px 25px rgba(245, 158, 11, 0.4);">🛡️</div>
                        <h3 style="color: #fff; margin-bottom: 5px; font-weight: 700; font-family:'Tajawal',sans-serif;">${username}</h3>
                        <p style="color: #a1a1aa; font-size: 0.9rem; margin-bottom: 25px; font-family:'Tajawal',sans-serif;">أدخل كلمة المرور للمتابعة</p>
                        <input type="password" id="unified-lock-pass" placeholder="........" style="width: 100%; padding: 14px; border-radius: 10px; background: rgba(255,255,255,0.07); color: #fff; border: 1px solid rgba(255,255,255,0.1); font-size: 1.1rem; box-sizing: border-box; margin-bottom: 20px; font-family: 'Tajawal',sans-serif; text-align: center; outline: none;">
                        <button onclick="unlockUnifiedDashboard()" style="width: 100%; font-family:'Tajawal',sans-serif; font-size: 1.1rem; padding: 12px; border-radius: 10px; background: linear-gradient(135deg, #f59e0b, #ea580c); border: none; color: white; cursor: pointer; transition: 0.2s;">تسجيل الدخول</button>
                        <div id="unified-lock-error" style="color: #ef4444; margin-top: 15px; font-size: 0.9rem; display: none; font-family:'Tajawal',sans-serif;">كلمة المرور خاطئة</div>
                    </div>
                `;
                document.body.appendChild(modal);
                
                document.getElementById('unified-lock-pass').addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') unlockUnifiedDashboard();
                });
            }
            modal.style.display = 'flex';
            setTimeout(() => document.getElementById('unified-lock-pass').focus(), 100);
        }

        async function unlockUnifiedDashboard() {
            const passEl = document.getElementById('unified-lock-pass');
            const pass = passEl.value.trim();
            const errEl = document.getElementById('unified-lock-error');
            if (!pass) { errEl.innerText = 'الرجاء إدخال كلمة المرور'; errEl.style.display = 'block'; return; }
            
            const btn = document.querySelector('#unified-lock-modal button');
            const oldText = btn.innerText;
            btn.innerText = 'جاري التحقق...';
            btn.disabled = true;
            errEl.style.display = 'none';

            try {
                const userStr = localStorage.getItem('user');
                let username = '';
                if (userStr) username = JSON.parse(userStr).username;
                
                const res = await fetch('api_index.php/auth/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username: username, password: pass })
                });
                const data = await res.json().catch(() => ({}));
                
                if (res.ok && data.token) {
                    sessionStorage.setItem('dashboard_unlocked', '1');
                    document.getElementById('unified-lock-modal').style.display = 'none';
                    initDashboardApp();
                } else {
                    errEl.innerText = data.error || data.message || 'كلمة المرور خاطئة';
                    errEl.style.display = 'block';
                }
            } catch (e) { 
                errEl.innerText = 'حدث خطأ في الاتصال';
                errEl.style.display = 'block';
            } finally {
                btn.innerText = oldText;
                btn.disabled = false;
            }
        }

        let unifiedIdleTime = 0;
        let unifiedIdleInterval = null;
        function setupUnifiedAutoLogout() {
            let autoLogoutValue = 0;
            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                autoLogoutValue = parseInt(cfg.autoLogoutTime) || 0;
            } catch(e) {}
            
            if (autoLogoutValue > 0) {
                if (unifiedIdleInterval) clearInterval(unifiedIdleInterval);
                unifiedIdleInterval = setInterval(() => {
                    unifiedIdleTime++;
                    if (unifiedIdleTime >= autoLogoutValue) {
                        sessionStorage.removeItem('dashboard_unlocked');
                        window.location.reload(); 
                    }
                }, 60000);
                
                const resetIdle = () => unifiedIdleTime = 0;
                ['mousemove', 'keypress', 'touchstart', 'scroll'].forEach(evt => {
                    document.addEventListener(evt, resetIdle);
                });
            }
        }

        if (window.APP_READY) {
            initDashboardApp();
        } else {
            document.addEventListener('DBReady', initDashboardApp);
        }

        // Re-render when background cloud sync completes without forcing page reload loop
        window.addEventListener('cloudDataRestored', () => {
            console.log("[Dashboard] Cloud sync completed. Updating UI...");
            initDashboardApp();
        });
        window.addEventListener('strictDataReady', () => {
            console.log("[Dashboard] Strict data ready. Updating UI...");
            initDashboardApp();
        });

        // Toggle Notifications Dropdown (Modal)
        function toggleNotifications(tab = 'LIVE') {
            const dropdown = document.getElementById('notifications-dropdown');
            if (dropdown.style.display === 'none' || dropdown.style.display === '') {
                dropdown.style.display = 'flex';
                setTimeout(() => dropdown.style.opacity = '1', 10);
                switchModalNotifTab(tab);
            } else {
                dropdown.style.opacity = '0';
                setTimeout(() => dropdown.style.display = 'none', 300);
            }
        }

        function switchModalNotifTab(tab) {
            const liveTab = document.getElementById('modal-tab-live');
            const ctrlTab = document.getElementById('modal-tab-ctrl');
            const liveBtn = document.getElementById('notif-tab-live-btn');
            const ctrlBtn = document.getElementById('notif-tab-ctrl-btn');

            if (tab === 'CTRL') {
                if (liveTab) liveTab.style.display = 'none';
                if (ctrlTab) ctrlTab.style.display = 'block';
                if (liveBtn) {
                    liveBtn.style.background = 'rgba(255,255,255,0.05)';
                    liveBtn.style.borderColor = 'rgba(255,255,255,0.1)';
                    liveBtn.style.color = '#94a3b8';
                }
                if (ctrlBtn) {
                    ctrlBtn.style.background = 'rgba(245,158,11,0.25)';
                    ctrlBtn.style.borderColor = '#f59e0b';
                    ctrlBtn.style.color = '#ffffff';
                }
                loadModalAlertRules();
            } else {
                if (liveTab) liveTab.style.display = 'block';
                if (ctrlTab) ctrlTab.style.display = 'none';
                if (liveBtn) {
                    liveBtn.style.background = 'rgba(244,63,94,0.25)';
                    liveBtn.style.borderColor = '#f43f5e';
                    liveBtn.style.color = '#ffffff';
                }
                if (ctrlBtn) {
                    ctrlBtn.style.background = 'rgba(255,255,255,0.05)';
                    ctrlBtn.style.borderColor = 'rgba(255,255,255,0.1)';
                    ctrlBtn.style.color = '#94a3b8';
                }
                checkDueDates();
            }
        }

        let _cachedModalRules = [];

        function loadModalAlertRules() {
            const container = document.getElementById('modal-rules-list');
            if (!container) return;

            fetch('alarm.php?action=get_rules_json')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.rules) {
                    _cachedModalRules = data.rules;
                    renderModalRulesList(data.rules);
                }
            })
            .catch(() => {
                container.innerHTML = '<div style="color:#f87171; text-align:center; padding:15px; font-size:0.85rem;">تعذر جلب قواعد التنبيهات من الخادم.</div>';
            });
        }

        function renderModalRulesList(rules) {
            const container = document.getElementById('modal-rules-list');
            if (!container) return;

            if (!rules || rules.length === 0) {
                container.innerHTML = '<div style="color:#94a3b8; text-align:center; padding:15px;">لا توجد قواعد متاحة.</div>';
                return;
            }

            const catColors = {
                'FINANCE': '#38bdf8',
                'CREDIT': '#fbbf24',
                'SALES': '#34d399',
                'INVENTORY': '#c084fc',
                'SUPPLY': '#f472b6',
                'SECURITY': '#f87171'
            };

            let html = '';
            rules.forEach(r => {
                const color = catColors[r.category] || '#94a3b8';
                const isChecked = r.is_enabled == 1;
                const screenBadge = r.screen_name ? `<span style="font-size:0.65rem; color:#38bdf8; background:rgba(56,189,248,0.1); padding:1px 6px; border-radius:4px; border:1px solid rgba(56,189,248,0.25);">🏢 ${r.screen_name}</span>` : '';
                html += `
                    <div class="modal-rule-item" data-search="${(r.rule_name + ' ' + (r.screen_name||'') + ' ' + (r.screen_file||'') + ' ' + (r.description||'')).toLowerCase()}" style="padding:10px 14px; background:rgba(255,255,255,0.03); border-radius:12px; border:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:180px;">
                            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                <strong style="font-size:0.85rem; color:#fff;">${r.rule_name}</strong>
                                <span style="font-size:0.65rem; color:${color}; background:${color}15; padding:1px 6px; border-radius:4px; border:1px solid ${color}30;">${r.category}</span>
                                ${screenBadge}
                            </div>
                            <div style="font-size:0.72rem; color:#94a3b8; margin-top:3px; line-height:1.4;">${r.description || ''}</div>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div style="display:flex; align-items:center; gap:3px; font-size:0.75rem; background:rgba(0,0,0,0.3); padding:3px 6px; border-radius:8px;">
                                <span style="color:#fbbf24;">⚠️</span>
                                <input type="number" id="mwarn-${r.rule_key}" value="${r.warning_threshold}" style="width:40px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#fbbf24; text-align:center; font-weight:800; font-size:0.75rem; border-radius:4px; padding:1px;" onchange="saveModalThresholds('${r.rule_key}')">
                                <span style="color:#f87171; margin-right:3px;">🚨</span>
                                <input type="number" id="mcrit-${r.rule_key}" value="${r.critical_threshold}" style="width:40px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#f87171; text-align:center; font-weight:800; font-size:0.75rem; border-radius:4px; padding:1px;" onchange="saveModalThresholds('${r.rule_key}')">
                            </div>
                            <label style="position:relative; display:inline-block; width:44px; height:24px; margin:0; cursor:pointer;">
                                <input type="checkbox" ${isChecked ? 'checked' : ''} onchange="toggleModalRule('${r.rule_key}', this.checked)" style="opacity:0; width:0; height:0;">
                                <span style="position:absolute; top:0; left:0; right:0; bottom:0; background-color:${isChecked ? '#10b981' : '#334155'}; transition:.3s; border-radius:24px; border:1px solid rgba(255,255,255,0.2);">
                                    <span style="position:absolute; content:''; height:16px; width:16px; left:${isChecked ? '23px' : '4px'}; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>
                                </span>
                            </label>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        function filterModalRules(q) {
            const query = q.trim().toLowerCase();
            const items = document.querySelectorAll('.modal-rule-item');
            items.forEach(it => {
                const text = it.getAttribute('data-search') || '';
                it.style.display = (query === '' || text.includes(query)) ? 'flex' : 'none';
            });
        }

        function toggleModalRule(ruleKey, isChecked) {
            const formData = new FormData();
            formData.append('action', 'toggle_rule_ajax');
            formData.append('rule_key', ruleKey);
            formData.append('is_enabled', isChecked ? 1 : 0);

            fetch('alarm.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    loadModalAlertRules();
                    checkDueDates();
                }
            });
        }

        function saveModalThresholds(ruleKey) {
            const warn = document.getElementById('mwarn-' + ruleKey).value;
            const crit = document.getElementById('mcrit-' + ruleKey).value;

            const formData = new FormData();
            formData.append('action', 'update_thresholds_ajax');
            formData.append('rule_key', ruleKey);
            formData.append('warning_threshold', warn);
            formData.append('critical_threshold', crit);

            fetch('alarm.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    checkDueDates();
                }
            });
        }

        // Check Due Dates
        function checkDueDates() {
            const invoices = (JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []).filter(x => !x.deleted);
            const customers = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
            
            let notifications = [];
            let unreadCount = 0;
            const today = new Date();
            today.setHours(0,0,0,0);

            invoices.forEach(inv => {
                if (inv.paymentMethod === 'credit' && inv.dueDate) {
                    const dueDate = new Date(inv.dueDate);
                    dueDate.setHours(0,0,0,0);
                    
                    const diffTime = dueDate - today;
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    
                    const cust = customers.find(c => c.id === inv.customer);
                    const custName = cust ? cust.name : inv.customer;
                    const custPhone = cust && cust.phone ? cust.phone : 'غير متوفر';

                    if (diffDays < 0) {
                        // Past due (Red)
                        notifications.push(`
                            <div style="padding:12px; background:rgba(248,113,113,0.1); border-radius:12px; border:1px solid rgba(248,113,113,0.3);">
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                    <strong style="color:#f87171; font-size:0.95rem;">🔴 تأخر سداد!</strong>
                                    <span style="font-size:0.75rem; color:var(--text-secondary); background:rgba(248,113,113,0.2); padding:2px 8px; border-radius:10px;">منذ ${Math.abs(diffDays)} أيام</span>
                                </div>
                                <div style="font-size:0.85rem; color:#fff; margin-bottom:6px; line-height: 1.5;">
                                    العميل: <strong style="color:var(--theme-blue);">${custName}</strong><br>
                                    فاتورة: ${inv.invoiceNo}
                                </div>
                                <div style="font-size:0.8rem; color:#fff; background: rgba(0,0,0,0.3); padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                    📞 الاتصال: ${custPhone}
                                </div>
                            </div>
                        `);
                        unreadCount++;
                    } else if (diffDays <= 2) {
                        // Nearing due date (Yellow)
                        let timeMsg = diffDays === 0 ? 'اليوم!' : (diffDays === 1 ? 'غداً' : 'بعد يومين');
                        notifications.push(`
                            <div style="padding:12px; background:rgba(251,191,36,0.1); border-radius:12px; border:1px solid rgba(251,191,36,0.3);">
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                    <strong style="color:#fbbf24; font-size:0.95rem;">🟡 اقترب موعد السداد</strong>
                                    <span style="font-size:0.75rem; color:var(--text-secondary); background:rgba(251,191,36,0.2); padding:2px 8px; border-radius:10px;">${timeMsg}</span>
                                </div>
                                <div style="font-size:0.85rem; color:#fff; margin-bottom:6px; line-height: 1.5;">
                                    العميل: <strong style="color:var(--theme-blue);">${custName}</strong><br>
                                    فاتورة: ${inv.invoiceNo}
                                </div>
                                <div style="font-size:0.8rem; color:#fff; background: rgba(0,0,0,0.3); padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                    📞 الاتصال: ${custPhone}
                                </div>
                            </div>
                        `);
                        unreadCount++;
                    }
                }
            });

            // Fetch live database alerts from alarm.php
            fetch('alarm.php?action=get_live_alerts_json')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.alerts && data.alerts.length > 0) {
                    data.alerts.forEach(a => {
                        const isRed = a.severity === 'RED';
                        const bg = isRed ? 'rgba(239, 68, 68, 0.15)' : 'rgba(245, 158, 11, 0.15)';
                        const border = isRed ? 'rgba(239, 68, 68, 0.4)' : 'rgba(245, 158, 11, 0.4)';
                        const color = isRed ? '#f87171' : '#fbbf24';
                        const title = isRed ? '🚨 إنذار رقابي حرج' : '⚠️ تنبيه للمتابعة';

                        notifications.unshift(`
                            <div style="padding:12px; background:${bg}; border-radius:12px; border:1px solid ${border};">
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                    <strong style="color:${color}; font-size:0.92rem;">${title}</strong>
                                    <span style="font-size:0.7rem; color:${color}; background:rgba(0,0,0,0.3); padding:2px 6px; border-radius:6px;">نظامي</span>
                                </div>
                                <div style="font-size:0.82rem; color:#fff; line-height: 1.5;">
                                    ${a.message}
                                </div>
                            </div>
                        `);
                        unreadCount++;
                    });
                }

                // Add footer link to Alerts Manager
                notifications.push(`
                    <div style="margin-top: 10px; text-align: center;">
                        <a href="alarm.php" style="display: block; padding: 10px; background: rgba(244, 63, 94, 0.2); border: 1px dashed #f43f5e; color: #fda4af; border-radius: 10px; text-decoration: none; font-weight: 800; font-size: 0.85rem; transition: background 0.2s;" onmouseover="this.style.background='rgba(244, 63, 94, 0.35)'" onmouseout="this.style.background='rgba(244, 63, 94, 0.2)'">
                            ⚙️ فتح مركز إدارة وتخصيص التنبيهات
                        </a>
                    </div>
                `);

                const badge = document.getElementById('notif-badge');
                const list = document.getElementById('notif-list');

                if (badge) {
                    if (unreadCount > 0) {
                        badge.innerText = unreadCount;
                        badge.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
                if (list) {
                    list.innerHTML = notifications.join('');
                }
            })
            .catch(() => {
                const badge = document.getElementById('notif-badge');
                const list = document.getElementById('notif-list');
                if (badge) {
                    if (unreadCount > 0) { badge.innerText = unreadCount; badge.style.display = 'block'; }
                    else { badge.style.display = 'none'; }
                }
                if (list) {
                    if (unreadCount > 0) list.innerHTML = notifications.join('');
                    else list.innerHTML = '<div style="color:var(--text-secondary); text-align:center; font-size:0.85rem; padding:15px;">لا توجد تنبيهات حالياً 🎉</div>';
                }
            });
        }

        // ═════════════════════════════════════════════════════════════════════
        //  LIVE EXCHANGE RATE & MULTI-CURRENCY COMMAND ENGINE
        // ═════════════════════════════════════════════════════════════════════
        window.dashBaseCurrency = 'SDG';
        window.dashForeignCurrency = 'USD';
        window.dashRateUSD = 1;
        window.dashDisplayCurrency = 'SDG';

        function syncDashboardFXSettings() {
            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                window.dashBaseCurrency = cfg.currency || 'SDG';
                window.dashForeignCurrency = cfg.foreignCurrency || 'USD';
                window.dashRateUSD = parseFloat(cfg.rateUSD) || 1;
                if (!window._dashCurrencyInitialized) {
                    window.dashDisplayCurrency = window.dashBaseCurrency;
                    window._dashCurrencyInitialized = true;
                }
                const badge = document.getElementById('dash-live-fx-badge');
                if (badge) {
                    const modeTag = cfg.exchangeMode === 'global' ? '🌐' : '✍️';
                    badge.innerHTML = `${modeTag} 1 ${window.dashForeignCurrency} = ${window.dashRateUSD.toLocaleString('en-US')} ${window.dashBaseCurrency}`;
                }
                const toggleBtn = document.getElementById('dash-cur-toggle-btn');
                if (toggleBtn) {
                    toggleBtn.innerHTML = window.dashDisplayCurrency === window.dashForeignCurrency
                        ? `عرض المؤشرات: ${window.dashForeignCurrency} 💵`
                        : `عرض المؤشرات: ${window.dashBaseCurrency} 🇸🇩`;
                }
            } catch (e) {}
        }

        function convertDashVal(amount, recordCurrency, recordRate) {
            const val = parseFloat(amount) || 0;
            if (val === 0) return 0;
            const recCur = String(recordCurrency || window.dashBaseCurrency).toUpperCase();
            const rate = parseFloat(recordRate) || window.dashRateUSD || 1;
            const inBase = (recCur === window.dashForeignCurrency) ? (val * rate) : val;
            if (window.dashDisplayCurrency === window.dashForeignCurrency) {
                return window.dashRateUSD > 0 ? Math.round((inBase / window.dashRateUSD) * 100) / 100 : inBase;
            }
            return Math.round(inBase);
        }

        function toggleDashboardCurrency() {
            syncDashboardFXSettings();
            window.dashDisplayCurrency = (window.dashDisplayCurrency === window.dashBaseCurrency)
                ? window.dashForeignCurrency
                : window.dashBaseCurrency;
            syncDashboardFXSettings();
            initProCharts();
            showToast(`💱 تم تحويل عرض مؤشرات لوحة التحكم إلى عملة (${window.dashDisplayCurrency})`, 'success');
        }

        window.openDashboardFXModal = function() {
            syncDashboardFXSettings();
            let modal = document.getElementById('dash-fx-command-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'dash-fx-command-modal';
                modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(4,8,18,0.88); backdrop-filter:blur(10px); z-index:999999; align-items:center; justify-content:center; padding:16px; direction:rtl;';
                modal.innerHTML = `
                    <div style="background:#0f172a; border:2px solid #38bdf8; border-radius:20px; width:100%; max-width:650px; max-height:90vh; overflow-y:auto; padding:22px; box-shadow:0 25px 80px rgba(0,0,0,0.9); color:#f8fafc; font-family:'Tajawal',sans-serif;">
                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(148,163,184,0.2); padding-bottom:12px; margin-bottom:16px;">
                            <h3 style="margin:0; font-size:1.15rem; font-weight:900; color:#38bdf8; display:flex; align-items:center; gap:8px;">
                                <span>💱</span> بورصة ومحول سعر الصرف والعملات اللحظي 360°
                            </h3>
                            <button type="button" onclick="document.getElementById('dash-fx-command-modal').style.display='none'" style="background:rgba(248,113,113,0.2); border:1px solid #f87171; color:#f87171; border-radius:8px; padding:4px 12px; font-weight:900; cursor:pointer;">إغلاق &times;</button>
                        </div>

                        <div style="background:rgba(30,41,59,0.75); border:1px solid rgba(56,189,248,0.3); border-radius:14px; padding:14px; margin-bottom:14px;">
                            <div style="font-size:0.82rem; font-weight:800; color:#fbbf24; margin-bottom:8px;">⚡ تحديث سعر الصرف المعتمد في النظام (يسمّع في كل الفواتير، القيود، والتقارير فوراً):</div>
                            <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                                <span style="font-weight:900; color:#cbd5e1;" id="fx-modal-pair-label">1 USD =</span>
                                <input type="number" step="any" id="fx-modal-rate-input" style="flex:1; min-width:130px; background:#090d16; border:1.5px solid #38bdf8; color:#38bdf8; font-size:1.15rem; font-weight:900; text-align:center; padding:8px; border-radius:10px; font-family:'Outfit',sans-serif;">
                                <span style="font-weight:900; color:#fbbf24;" id="fx-modal-base-label">SDG</span>
                                <button type="button" onclick="fetchGlobalFXRateInModal()" style="background:rgba(56,189,248,0.2); border:1px solid #38bdf8; color:#38bdf8; padding:8px 12px; border-radius:10px; font-family:'Tajawal'; font-weight:800; cursor:pointer;">🌐 جلب العالمي</button>
                                <button type="button" onclick="saveDashboardFXRate()" style="background:linear-gradient(135deg,#059669,#10b981); border:1px solid #34d399; color:#fff; padding:8px 16px; border-radius:10px; font-family:'Tajawal'; font-weight:900; cursor:pointer;">💾 حفظ واعتماد للكل</button>
                            </div>
                        </div>

                        <div style="background:rgba(15,23,42,0.9); border:1px solid rgba(148,163,184,0.2); border-radius:14px; padding:14px; margin-bottom:14px;">
                            <div style="font-size:0.82rem; font-weight:800; color:#34d399; margin-bottom:8px;">🧮 حاسبة التحويل الفوري المزدوجة:</div>
                            <div style="display:grid; grid-template-columns:1fr auto 1fr; gap:10px; align-items:center;">
                                <div>
                                    <label style="font-size:0.75rem; color:#94a3b8; display:block; margin-bottom:4px;">المبلغ بالعملة الأجنبية (<span id="fx-conv-for-lbl">USD</span>)</label>
                                    <input type="number" step="any" id="fx-conv-usd" oninput="calcFXModalConverter('usd')" placeholder="100" style="width:100%; box-sizing:border-box; background:#1e293b; border:1px solid #475569; color:#fff; padding:8px; border-radius:8px; font-family:'Outfit',sans-serif; font-weight:800;">
                                </div>
                                <div style="font-size:1.2rem; color:#38bdf8; padding-top:16px;">⇄</div>
                                <div>
                                    <label style="font-size:0.75rem; color:#94a3b8; display:block; margin-bottom:4px;">المعادل بالعملة الأساسية (<span id="fx-conv-base-lbl">SDG</span>)</label>
                                    <input type="number" step="any" id="fx-conv-sdg" oninput="calcFXModalConverter('sdg')" placeholder="0" style="width:100%; box-sizing:border-box; background:#1e293b; border:1px solid #475569; color:#fbbf24; padding:8px; border-radius:8px; font-family:'Outfit',sans-serif; font-weight:800;">
                                </div>
                            </div>
                        </div>

                        <div id="fx-modal-exposure-box" style="background:rgba(168,85,247,0.1); border:1px solid rgba(168,85,247,0.3); border-radius:14px; padding:12px 14px; margin-bottom:14px; font-size:0.82rem; line-height:1.7;"></div>

                        <div style="display:flex; justify-content:space-between; gap:8px; flex-wrap:wrap;">
                            <a href="journal-voucher.php?desc=قيد+تسوية+فروق+تقييم+عملة+أجنبية" style="flex:1; text-align:center; background:rgba(168,85,247,0.22); border:1px solid #c084fc; color:#e9d5ff; padding:9px 12px; border-radius:10px; text-decoration:none; font-weight:800; font-size:0.82rem;">⚖️ إثبات قيد فروق عملة</a>
                            <a href="settings.php" style="flex:1; text-align:center; background:rgba(56,189,248,0.18); border:1px solid #38bdf8; color:#38bdf8; padding:9px 12px; border-radius:10px; text-decoration:none; font-weight:800; font-size:0.82rem;">⚙️ إعدادات وسجل أسعار الصرف</a>
                            <button type="button" onclick="toggleDashboardCurrency(); document.getElementById('dash-fx-command-modal').style.display='none';" style="flex:1; background:rgba(245,158,11,0.22); border:1px solid #fbbf24; color:#fbbf24; padding:9px 12px; border-radius:10px; font-family:'Tajawal'; font-weight:800; font-size:0.82rem; cursor:pointer;">🔄 تبديل عملة لوحة التحكم</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            document.getElementById('fx-modal-pair-label').innerText = `1 ${window.dashForeignCurrency} =`;
            document.getElementById('fx-modal-base-label').innerText = window.dashBaseCurrency;
            document.getElementById('fx-conv-for-lbl').innerText = window.dashForeignCurrency;
            document.getElementById('fx-conv-base-lbl').innerText = window.dashBaseCurrency;
            document.getElementById('fx-modal-rate-input').value = window.dashRateUSD;
            document.getElementById('fx-conv-usd').value = 100;
            document.getElementById('fx-conv-sdg').value = Math.round(100 * window.dashRateUSD * 100) / 100;

            const custs = (JSON.parse(localStorage.getItem('erp_customers_v1') || '[]')).filter(x => !x.deleted);
            const sups = (JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]')).filter(x => !x.deleted);
            const arTotal = custs.reduce((s, c) => s + Math.max(0, parseFloat(c.currentBalance) || 0), 0);
            const apTotal = sups.reduce((s, x) => s + Math.abs(parseFloat(x.currentBalance) || 0), 0);
            const rate = window.dashRateUSD > 0 ? window.dashRateUSD : 1;
            const expBox = document.getElementById('fx-modal-exposure-box');
            if (expBox) {
                expBox.innerHTML = `
                    <strong style="color:#c084fc;">📊 تقييم المركز المالي المزدوج حسب سعر الصرف الحالي (${rate.toLocaleString()}):</strong><br>
                    • إجمالي مديونية العملاء (AR): <strong style="color:#34d399;">${Math.round(arTotal).toLocaleString()} ${window.dashBaseCurrency}</strong> (يعادل <strong style="color:#38bdf8;">${Math.round(arTotal / rate).toLocaleString()} ${window.dashForeignCurrency}</strong>)<br>
                    • إجمالي التزامات الموردين (AP): <strong style="color:#f87171;">${Math.round(apTotal).toLocaleString()} ${window.dashBaseCurrency}</strong> (يعادل <strong style="color:#fbbf24;">${Math.round(apTotal / rate).toLocaleString()} ${window.dashForeignCurrency}</strong>)
                `;
            }
            modal.style.display = 'flex';
        };

        window.calcFXModalConverter = function(dir) {
            const rate = parseFloat(document.getElementById('fx-modal-rate-input')?.value) || window.dashRateUSD || 1;
            const usdEl = document.getElementById('fx-conv-usd');
            const sdgEl = document.getElementById('fx-conv-sdg');
            if (dir === 'usd') {
                const u = parseFloat(usdEl.value) || 0;
                sdgEl.value = Math.round(u * rate * 100) / 100;
            } else {
                const s = parseFloat(sdgEl.value) || 0;
                usdEl.value = rate > 0 ? (Math.round((s / rate) * 100) / 100) : 0;
            }
        };

        window.fetchGlobalFXRateInModal = function() {
            fetch('api_index.php/exchange-rate')
                .then(r => r.json())
                .then(d => {
                    const rVal = parseFloat(d.rate || d.SDG || d.rateUSD);
                    if (rVal > 0) {
                        document.getElementById('fx-modal-rate-input').value = rVal;
                        window.calcFXModalConverter('usd');
                        showToast(`🌐 تم جلب السعر العالمي بنجاح: ${rVal}`, 'success');
                    } else {
                        showToast('تعذر جلب السعر العالمي حالياً، يمكنك إدخاله يدوياً', 'warning');
                    }
                })
                .catch(() => showToast('تعذر الاتصال بخدمة الأسعار العالمية، أدخل السعر يدوياً', 'warning'));
        };

        window.saveDashboardFXRate = function() {
            const newRate = parseFloat(document.getElementById('fx-modal-rate-input')?.value);
            if (!newRate || newRate <= 0) {
                showToast('يرجى إدخال سعر صرف صحيح أكبر من الصفر', 'error');
                return;
            }
            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                const oldRate = cfg.rateUSD;
                cfg.rateUSD = newRate;
                cfg.exchangeMode = 'manual';
                cfg.savedAt = new Date().toISOString();
                localStorage.setItem('erp_settings_v1', JSON.stringify(cfg));

                const histKey = typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_exchange_rate_history') : 'erp_exchange_rate_history';
                const hist = JSON.parse(localStorage.getItem(histKey) || '[]');
                hist.unshift({
                    date: new Date().toLocaleString('ar-EG'),
                    rate: newRate,
                    oldRate: oldRate || newRate,
                    user: (JSON.parse(localStorage.getItem('user') || '{}').username) || 'الإدارة',
                    source: 'لوحة التحكم'
                });
                localStorage.setItem(histKey, JSON.stringify(hist.slice(0, 100)));

                fetch('api_index.php/tenant-exchange-rate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ rateUSD: newRate, exchangeMode: 'manual' })
                }).catch(() => {});

                if (typeof window.logSystemAction === 'function') {
                    window.logSystemAction('تحديث سعر الصرف من لوحة التحكم', `تم تعديل سعر الصرف من (${oldRate || 1}) إلى (${newRate}) ${window.dashBaseCurrency}`, 'SETTINGS');
                }

                syncDashboardFXSettings();
                initProCharts();
                document.getElementById('dash-fx-command-modal').style.display = 'none';
                showToast(`✅ تم اعتماد سعر الصرف الجديد (${newRate} ${window.dashBaseCurrency}) وتعميمه على كل الشاشات والتقارير!`, 'success');
            } catch (e) {
                showToast('حدث خطأ أثناء حفظ سعر الصرف', 'error');
            }
        };

        // Load Dynamic Dashboard Data
        function loadDynamicDashboardData() {
            syncDashboardFXSettings();
            const curSuffix = window.dashDisplayCurrency || 'SDG';

            // Fetch data
            const invoices = (JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []).filter(x => !x.deleted && x.saleType !== 'preliminary' && x.status !== 'preliminary');
            const banks = (JSON.parse(localStorage.getItem('erp_banks_v1')) || []).filter(x => !x.deleted);
            const vaults = (JSON.parse(localStorage.getItem('erp_vaults_v1')) || []).filter(x => !x.deleted);
            const items = (JSON.parse(localStorage.getItem('erp_items_v1')) || []).filter(x => !x.deleted);
            const gl = (JSON.parse(localStorage.getItem('erp_gl_v1')) || []).filter(x => !x.deleted);
            const customers = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
            const inventory = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];

            // Helper to get total actual stock of an item across warehouses (ignoring virtual pending states)
            function getItemActualStock(itemId) {
                return inventory
                    .filter(inv => inv.itemId === itemId && inv.warehouse !== 'PENDING_RECEIPT')
                    .reduce((sum, inv) => sum + (Number(inv.qty) || 0), 0);
            }

            // Get EXACT local date YYYY-MM-DD
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const todayStr = `${year}-${month}-${day}`;

            const sevenDaysAgo = new Date(now);
            sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
            const sevenDaysAgoStr = sevenDaysAgo.toISOString().split('T')[0];

            // Filter invoices by scope
            let scopedInvoices = invoices;
            if (window.currentDashboardScope === 'today') {
                scopedInvoices = invoices.filter(inv => inv.date && inv.date.startsWith(todayStr));
                if (document.getElementById('kpi-sales-title')) document.getElementById('kpi-sales-title').textContent = 'مبيعات اليوم';
            } else if (window.currentDashboardScope === 'week') {
                scopedInvoices = invoices.filter(inv => inv.date && inv.date >= sevenDaysAgoStr);
                if (document.getElementById('kpi-sales-title')) document.getElementById('kpi-sales-title').textContent = 'مبيعات آخر 7 أيام';
            } else if (window.currentDashboardScope === 'month') {
                scopedInvoices = invoices.filter(inv => inv.date && inv.date.startsWith(`${year}-${month}`));
                if (document.getElementById('kpi-sales-title')) document.getElementById('kpi-sales-title').textContent = 'مبيعات الشهر';
            } else {
                if (document.getElementById('kpi-sales-title')) document.getElementById('kpi-sales-title').textContent = 'إجمالي المبيعات';
            }

            // 1. Calculate Scoped Sales with Multi-Currency awareness
            const totalScopedSales = scopedInvoices.reduce((sum, inv) => {
                const rawAmt = Number(inv.grandTotal) || Number(inv.totalAmount) || 0;
                return sum + convertDashVal(rawAmt, inv.currency, inv.exchangeRate);
            }, 0);
            animateValue('kpi-sales', 0, totalScopedSales, 1300, curSuffix);

            // 2. Calculate Vault Liquidity & Total Liquidity (reading both erp_vaults_v1 and erp_banks_v1)
            let vaultLiquidity = 0;
            let bankLiquidity = 0;
            vaults.forEach(v => {
                let bal = Number(v.currentBalance) ?? Number(v.balance) ?? Number(v.openingBalance) ?? 0;
                vaultLiquidity += convertDashVal(bal, v.currency, v.exchangeRate);
            });
            banks.forEach(b => {
                let bal = Number(b.currentBalance) ?? Number(b.balance) ?? Number(b.openingBalance) ?? 0;
                if (b.type === 'cash' || String(b.name || '').includes('صندوق') || String(b.name || '').includes('خزينة')) {
                    vaultLiquidity += convertDashVal(bal, b.currency, b.exchangeRate);
                } else {
                    bankLiquidity += convertDashVal(bal, b.currency, b.exchangeRate);
                }
            });
            let totalLiquidity = vaultLiquidity + bankLiquidity;

            animateValue('kpi-vault-liquidity', 0, vaultLiquidity, 1400, curSuffix);
            animateValue('kpi-liquidity', 0, totalLiquidity, 1400, curSuffix);

            // 4. Total Items
            const totalItems = items.length;
            animateValue('kpi-inventory', 0, totalItems, 1200, 'صنف');
            
            let warehousesList = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            if (warehousesList.length === 0) {
                warehousesList = [{ id: 'w-main', name: 'المخزن الرئيسي' }];
            }
            const activeWhsCount = warehousesList.filter(w => !w.deleted).length;
            const whsCountEl = document.getElementById('kpi-warehouses-count');
            if (whsCountEl) {
                whsCountEl.innerText = `موزعة على ${activeWhsCount} ${activeWhsCount > 2 && activeWhsCount < 11 ? 'مخازن' : 'مخزن'}`;
            }

            // 5. Low Stock computed from actual inventory levels
            const lowStockItems = items.filter(item => {
                let available = getItemActualStock(item.id);
                let safeLimit = Number(item.safeLimit) || Number(item.minQty) || 10;
                return available <= safeLimit;
            });
            animateValue('kpi-alerts', 0, lowStockItems.length, 1200, 'أصناف');

            const lowStockHTML = lowStockItems.slice(0, 5).map(item => {
                const stockVal = getItemActualStock(item.id);
                return `
                <div class="list-item" style="background:rgba(248,113,113,0.1); border:1px solid rgba(248,113,113,0.2);">
                    <div class="list-icon" style="background:var(--theme-red); color:#fff; box-shadow: 0 3px 10px rgba(248,113,113,0.3);">!</div>
                    <div style="flex:1;">
                        <h4 class="list-title">${item.name}</h4>
                        <span class="list-sub">الرصيد: <strong style="color:var(--theme-red); font-size: 0.9rem;">${stockVal}</strong></span>
                    </div>
                    <a href="purchase.php" data-href="purchase.php" onclick="window.location.href='purchase.php'" class="list-btn" style="box-shadow: 0 2px 8px rgba(99,102,241,0.3);">طلب</a>
                </div>
                `;
            }).join('');
            document.getElementById('low-stock-list').innerHTML = lowStockHTML || '<div style="color:var(--text-secondary); font-size:0.85rem; background: rgba(255,255,255,0.02); padding: 15px; border-radius: 10px; text-align: center;">المخزون آمن حالياً ✅</div>';

            // Dynamic cost calculation
            const _cachedPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
            function getItemCost(itemId) {
                const item = items.find(i => i.id === itemId);
                if (item && item.openingQty > 0 && item.openingValue > 0) {
                    return Number(item.openingValue) / Number(item.openingQty);
                }
                const purchases = _cachedPurchases;
                let lastPrice = null;
                for (let inv of purchases) {
                    if (inv.deleted) continue;
                    const line = (inv.items || inv.lines || []).find(l => l.itemId === itemId || l.productId === itemId);
                    if (line && line.price) {
                        lastPrice = Number(line.price);
                        break;
                    }
                }
                if (lastPrice !== null) return lastPrice;
                if (item) {
                    let sp = item.sellingPrice || (item.units && item.units[0] ? item.units[0].cashPrice : 0) || 0;
                    return sp * 0.7; // default 30% margin fallback
                }
                return 0;
            }

            // Calculate Real Scoped Profit (using actual item costs)
            const todayInvoices = scopedInvoices;
            let todayProfitBase = 0;
            todayInvoices.forEach(inv => {
                let invProfit = 0;
                (inv.items || []).forEach(line => {
                    const cost = getItemCost(line.itemId);
                    const sellPrice = Number(line.price) || 0;
                    const qty = Number(line.quantity) || Number(line.qty) || 0;
                    invProfit += (sellPrice - cost) * qty;
                });
                if (invProfit === 0 && (Number(inv.grandTotal) || Number(inv.totalAmount))) {
                    invProfit = (Number(inv.grandTotal) || Number(inv.totalAmount)) * 0.22;
                }
                const disc = Number(inv.discount) || 0;
                todayProfitBase += (invProfit - disc);
            });
            const todayProfit = convertDashVal(todayProfitBase, window.dashBaseCurrency, window.dashRateUSD);
            animateValue('kpi-monthly-profit', 0, todayProfit, 1500, curSuffix);

            const currentMonthStr = `${year}-${month}`;
            const monthlyInvoices = invoices.filter(inv => inv.date && inv.date.startsWith(currentMonthStr));
            let monthlyProfitBase = 0;
            monthlyInvoices.forEach(inv => {
                let invProfit = 0;
                (inv.items || []).forEach(line => {
                    const cost = getItemCost(line.itemId);
                    const sellPrice = Number(line.price) || 0;
                    const qty = Number(line.quantity) || Number(line.qty) || 0;
                    invProfit += (sellPrice - cost) * qty;
                });
                const disc = Number(inv.discount) || 0;
                monthlyProfitBase += (invProfit - disc);
            });
            const monthlyProfit = convertDashVal(monthlyProfitBase, window.dashBaseCurrency, window.dashRateUSD);

            // Update Ticker
            if (document.getElementById('ticker-sales')) document.getElementById('ticker-sales').innerText = totalScopedSales.toLocaleString('en-US') + ' ' + curSuffix;
            if (document.getElementById('ticker-profit')) document.getElementById('ticker-profit').innerText = todayProfit.toLocaleString('en-US') + ' ' + curSuffix;
            if (document.getElementById('ticker-monthly-profit')) document.getElementById('ticker-monthly-profit').innerText = monthlyProfit.toLocaleString('en-US') + ' ' + curSuffix;
            if (document.getElementById('ticker-liquidity')) document.getElementById('ticker-liquidity').innerText = totalLiquidity.toLocaleString('en-US') + ' ' + curSuffix;

            // Trigger Stock Alerts Toast if any critical items
            const criticalItems = lowStockItems.filter(item => getItemActualStock(item.id) <= 0);
            if (criticalItems.length > 0 && !sessionStorage.getItem('stock_alert_shown')) {
                showToast(`⚠️ تنبيه مخزون: هناك ${criticalItems.length} أصناف نفذت تماماً من المخزن! يرجى مراجعة التنبيهات.`, 'error');
                sessionStorage.setItem('stock_alert_shown', 'true');
            }

            // 4.5 Pending Deliveries
            const pendingDeliveries = scopedInvoices.filter(inv => {
                const s = inv.deliveryStatus;
                return s !== 'delivered' && s !== 'Admin_Closed' && s !== 'Delivered_Pending_Admin';
            }).slice(0, 5);
            
            const pendingContainer = document.getElementById('pending-deliveries-list');
            if (pendingContainer) {
                if (pendingDeliveries.length > 0) {
                    pendingContainer.innerHTML = pendingDeliveries.map(inv => {
                        const custName = customers.find(c => c.id === inv.customerId || c.id === inv.customer)?.name || inv.customerName || inv.customer || inv.customerId || 'عميل';
                        const invDate = inv.date || '-';
                        return `
                        <div class="list-item" style="background:rgba(251,191,36,0.08); border:1px solid rgba(251,191,36,0.2);">
                            <div class="list-icon" style="background:var(--theme-orange); color:#fff; box-shadow: 0 3px 10px rgba(245,158,11,0.3);">🚚</div>
                            <div style="flex:1;">
                                <h4 class="list-title">إذن #${inv.invoiceNo || inv.id}</h4>
                                <span class="list-sub"><span style="color:#e2e8f0;">${custName}</span> | ${invDate}</span>
                            </div>
                            <a href="sales-delivery.php" data-href="sales-delivery.php" onclick="window.location.href='sales-delivery.php'" class="list-btn" style="box-shadow: 0 2px 8px rgba(99,102,241,0.3);">تسليم</a>
                        </div>
                        `;
                    }).join('');
                } else {
                    pendingContainer.innerHTML = '<div style="color:var(--text-secondary); text-align:center; font-size:0.85rem; padding: 15px; background: rgba(255,255,255,0.02); border-radius: 10px;">كل الأذونات مسلمة! 🎉</div>';
                }
            }

            // 4.8 Executive Jobs & Tasks Widget (connected to erp_jobs_v1 / jobs.php)
            const jobsList = (JSON.parse(localStorage.getItem('erp_jobs_v1') || '[]')).filter(j => j.status !== 'done');
            const topJobsCountEl = document.getElementById('dash-top-jobs-count');
            if (topJobsCountEl) topJobsCountEl.innerText = jobsList.length;
            const dashJobsContainer = document.getElementById('dashboard-jobs-list');
            if (dashJobsContainer) {
                if (jobsList.length > 0) {
                    dashJobsContainer.innerHTML = jobsList.slice(0, 4).map(j => {
                        const isCrit = j.priority === 'critical' || j.priority === 'high';
                        return `
                        <div class="list-item" style="background:${isCrit ? 'rgba(244,63,94,0.08)' : 'rgba(168,85,247,0.08)'}; border:1px solid ${isCrit ? 'rgba(244,63,94,0.25)' : 'rgba(168,85,247,0.25)'};">
                            <div class="list-icon" style="background:${isCrit ? '#e11d48' : '#7c3aed'}; color:#fff;">💼</div>
                            <div style="flex:1; min-width:0;">
                                <h4 class="list-title" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${j.title}</h4>
                                <span class="list-sub">👤 ${j.user || 'غير مسند'} ${j.dueDate ? '| 📅 ' + j.dueDate : ''}</span>
                            </div>
                            <a href="${j.linkedScreen || 'jobs.php'}" class="list-btn" style="background:rgba(168,85,247,0.25); border:1px solid #c084fc; color:#fff;">تنفيذ</a>
                        </div>
                        `;
                    }).join('');
                } else {
                    dashJobsContainer.innerHTML = `
                        <div style="color:var(--text-secondary); text-align:center; font-size:0.82rem; padding:14px; background:rgba(255,255,255,0.02); border-radius:10px;">
                            لا توجد مهام معلقة حالياً ✅
                            <div style="margin-top:8px;">
                                <a href="jobs.php" style="color:#38bdf8; font-weight:800; text-decoration:none;">➕ توليد مهام ذكية من النظام</a>
                            </div>
                        </div>
                    `;
                }
            }

            // 5. Recent Transactions (with clickable drill-down links)
            let allTx = [];
            scopedInvoices.forEach(i => allTx.push({ id: i.invoiceNo || i.id, type: 'مبيعات', desc: 'فاتورة مبيعات', amount: convertDashVal(i.grandTotal || i.totalAmount, i.currency, i.exchangeRate), date: i.date, href: 'direct-sales.php' }));
            
            const purchases = (JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || []).filter(x => !x.deleted && !x.isGRN && !x.isReturn);
            purchases.forEach(i => allTx.push({ id: i.invoiceNo || i.id, type: 'مشتريات', desc: 'فاتورة مشتريات', amount: convertDashVal(i.grandTotal || i.totalAmount, i.currency, i.exchangeRate), date: i.date, href: 'purchase.php' }));

            const sReceipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
            sReceipts.forEach(i => allTx.push({ id: i.id, type: 'قبض', desc: i.description || 'سند قبض مفرد', amount: convertDashVal(i.amount, i.currency, i.exchangeRate), date: i.date, href: 'single-cash-payment.php' }));

            const sPayments = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
            sPayments.forEach(i => allTx.push({ id: i.id, type: 'صرف', desc: i.description || 'سند صرف مفرد', amount: convertDashVal(i.amount, i.currency, i.exchangeRate), date: i.date, href: 'single-cash-payment.php' }));
            
            const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
            jvs.forEach(i => allTx.push({ id: i.voucherNo || i.id, type: 'قيد يومية', desc: i.statement || 'قيد مالي', amount: convertDashVal(i.totalAmount || i.totalDebit, i.currency, i.exchangeRate), date: i.date, href: `journal-voucher.php?id=${encodeURIComponent(i.id || '')}` }));

            allTx.sort((a, b) => new Date(b.date || 0) - new Date(a.date || 0));

            const recentTx = allTx.slice(0, 5);
            const recentHTML = recentTx.map(entry => {
                let badge = '<span style="background:rgba(16, 185, 129, 0.15); color:var(--theme-green); padding:4px 8px; border-radius:8px; font-size:0.75rem; font-weight: 800; border: 1px solid rgba(16,185,129,0.3);">مكتمل ✅</span>';
                return `
                <tr onclick="window.location.href='${entry.href || 'audit-log.php'}'" style="cursor:pointer;" title="انقر لفتح المستند">
                    <td><strong style="color:var(--theme-blue);">#${entry.id}</strong></td>
                    <td><span class="type-badge">${entry.type}</span></td>
                    <td>${entry.desc || '-'}</td>
                    <td><strong style="color:#fff;">${(Number(entry.amount)||0).toLocaleString()} ${curSuffix}</strong></td>
                    <td>${badge}</td>
                </tr>
                `;
            }).join('');
            document.getElementById('recent-transactions-tbody').innerHTML = recentHTML || '<tr><td colspan="5" style="text-align:center; padding:15px; color:var(--text-secondary); font-size: 0.85rem;">لا توجد حركات مالية مسجلة بعد.</td></tr>';

            if (new URLSearchParams(window.location.search).get('modal') === 'fx' && !window._fxModalAutoOpened) {
                window._fxModalAutoOpened = true;
                setTimeout(() => window.openDashboardFXModal(), 300);
            }

            return { invoices: scopedInvoices, items, customers, banks };
        }

        // Initialize Chart.js Graphs
        function initProCharts() {
            const data = loadDynamicDashboardData();
            const inventory = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
            
            const textColor = '#94a3b8';
            const gridColor = 'rgba(255, 255, 255, 0.05)';

            Chart.defaults.color = textColor;
            Chart.defaults.font.family = "'Tajawal', sans-serif";
            Chart.defaults.font.size = 11;

            // 1. Cashflow Chart (Dynamic Weekly) using grandTotal/totalAmount
            const sReceipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
            const sPayments = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
            
            let daysLabels = [];
            let receiptsData = [0,0,0,0,0,0,0];
            let paymentsData = [0,0,0,0,0,0,0];
            
            const today = new Date();
            for(let i=6; i>=0; i--) {
                let d = new Date(today);
                d.setDate(d.getDate() - i);
                const dayStr = d.toLocaleDateString('ar-EG', { weekday: 'short' });
                daysLabels.push(dayStr);
                
                const dStr = d.toISOString().split('T')[0];
                
                let dayReceipts = sReceipts.filter(r => (r.date || '').startsWith(dStr) && !r.deleted).reduce((sum, r) => sum + (Number(r.amount) || 0), 0);
                let cashSales = data.invoices.filter(inv => (inv.date || '').startsWith(dStr) && inv.paymentMethod !== 'credit' && !inv.deleted).reduce((sum, inv) => sum + (Number(inv.grandTotal) || Number(inv.totalAmount) || 0), 0);
                
                let dayPayments = sPayments.filter(p => (p.date || '').startsWith(dStr) && !p.deleted).reduce((sum, p) => sum + (Number(p.amount) || 0), 0);
                const purchases = (JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || []).filter(x => !x.deleted);
                let cashPurchases = purchases.filter(inv => (inv.date || '').startsWith(dStr) && inv.paymentMethod !== 'credit').reduce((sum, inv) => sum + (Number(inv.grandTotal) || Number(inv.totalAmount) || 0), 0);

                receiptsData[6-i] = convertDashVal(dayReceipts + cashSales, window.dashBaseCurrency, window.dashRateUSD);
                paymentsData[6-i] = convertDashVal(dayPayments + cashPurchases, window.dashBaseCurrency, window.dashRateUSD);
            }

            let totalReceiptsInScope = receiptsData.reduce((a, b) => a + b, 0);
            let totalPaymentsInScope = paymentsData.reduce((a, b) => a + b, 0);
            let netCashflow = totalReceiptsInScope - totalPaymentsInScope;
            const cfCur = window.dashDisplayCurrency || 'SDG';

            if (document.getElementById('cf-summary-in')) document.getElementById('cf-summary-in').innerText = totalReceiptsInScope.toLocaleString('en-US') + ' ' + cfCur;
            if (document.getElementById('cf-summary-out')) document.getElementById('cf-summary-out').innerText = totalPaymentsInScope.toLocaleString('en-US') + ' ' + cfCur;
            if (document.getElementById('cf-summary-net')) {
                const netEl = document.getElementById('cf-summary-net');
                netEl.innerText = (netCashflow >= 0 ? '+' : '') + netCashflow.toLocaleString('en-US') + ' ' + cfCur;
                netEl.style.color = netCashflow >= 0 ? '#10b981' : '#f87171';
            }

            window._dashboardChartInstances = window._dashboardChartInstances || {};
            function createOrUpdateChart(id, config) {
                if (window._dashboardChartInstances[id]) {
                    try { window._dashboardChartInstances[id].destroy(); } catch(e) {}
                }
                const canvas = document.getElementById(id);
                if (canvas) {
                    window._dashboardChartInstances[id] = new Chart(canvas.getContext('2d'), config);
                }
            }

            createOrUpdateChart('cashflowChart', {
                type: 'line',
                data: {
                    labels: daysLabels,
                    datasets: [
                        { label: 'المقبوضات', data: receiptsData, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.15)', fill: true, borderWidth: 2, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#10b981' },
                        { label: 'المصروفات', data: paymentsData, borderColor: '#f87171', backgroundColor: 'rgba(248,113,113,0.15)', fill: true, borderWidth: 2, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#f87171' }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { usePointStyle: true, padding: 10, font: {family: "'Tajawal'", size: 11} } } }, scales: { x: { grid: { color: gridColor } }, y: { grid: { color: gridColor } } } }
            });

            // 2. Sales by Category (Dynamic from Sales Invoices)
            const catSales = {};
            data.invoices.forEach(inv => {
                if(inv.items && Array.isArray(inv.items)) {
                    inv.items.forEach(item => {
                        const itemDef = data.items.find(i => i.id === item.itemId);
                        let c = (itemDef ? itemDef.category : '') || 'أخرى';
                        let quantity = Number(item.quantity) || Number(item.qty) || 0;
                        let price = Number(item.price) || 0;
                        let total = quantity * price;
                        catSales[c] = (catSales[c] || 0) + total;
                    });
                }
            });
            const catLabels = Object.keys(catSales).length > 0 ? Object.keys(catSales) : ['بدون مبيعات'];
            const catData = Object.keys(catSales).length > 0 ? Object.values(catSales) : [1];

            createOrUpdateChart('categoryChart', {
                type: 'doughnut',
                data: {
                    labels: catLabels,
                    datasets: [{ data: catData, backgroundColor: ['#3b82f6', '#f59e0b', '#f87171', '#10b981', '#c084fc', '#ec4899'], borderWidth: 0, hoverOffset: 10 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'right', labels: { usePointStyle: true, padding: 10, font: {family: "'Tajawal'", size: 11} } }, tooltip: { callbacks: { label: function(context) { return ' ' + context.label + ': ' + context.raw.toLocaleString() + ' SDG'; } } } } }
            });

            // 3. Inventory Radar (Dynamic - Lowest Stock Items)
            function getItemActualStock(itemId) {
                return inventory
                    .filter(inv => inv.itemId === itemId && inv.warehouse !== 'PENDING_RECEIPT')
                    .reduce((sum, inv) => sum + (Number(inv.qty) || 0), 0);
            }
            
            let radarItemsList = [...data.items].map(i => {
                let avail = getItemActualStock(i.id);
                let safe = Number(i.safeLimit) || Number(i.minQty) || 10;
                let ratio = safe > 0 ? avail / safe : 1;
                return { ...i, avail, safe, ratio };
            });
            radarItemsList.sort((a, b) => a.ratio - b.ratio);
            const radarItems = radarItemsList.slice(0, 5);
            
            const radarLabels = radarItems.length > 0 ? radarItems.map(i => i.name) : ['لا يوجد'];
            const radarAvail = radarItems.length > 0 ? radarItems.map(i => i.avail) : [0];
            const radarSafe = radarItems.length > 0 ? radarItems.map(i => i.safe) : [0];

            createOrUpdateChart('inventoryChart', {
                type: 'radar',
                data: {
                    labels: radarLabels,
                    datasets: [
                        { label: 'المتوفر', data: radarAvail, backgroundColor: 'rgba(248,113,113,0.2)', borderColor: '#f87171', pointBackgroundColor: '#f87171', borderWidth: 1.5 },
                        { label: 'حد الأمان', data: radarSafe, backgroundColor: 'rgba(59,130,246,0.2)', borderColor: '#3b82f6', pointBackgroundColor: '#3b82f6', borderWidth: 1.5 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { usePointStyle: true, padding: 10, font: {family: "'Tajawal'", size: 11} } } }, scales: { r: { grid: { color: gridColor }, angleLines: { color: gridColor }, ticks: { display: false } } } }
            });

            // 4. Warehouses Capacity (Actual Item Stock Quantities in Each Warehouse)
            const whTotals = {};
            let warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            if (warehouses.length === 0) {
                warehouses = [{ id: 'w-main', name: 'المخزن الرئيسي' }];
            }
            const activeWarehouses = warehouses.filter(w => !w.deleted);
            activeWarehouses.forEach(w => {
                whTotals[w.name || w.id] = 0;
            });
            inventory.forEach(inv => {
                if (inv.warehouse !== 'PENDING_RECEIPT') {
                    const w = warehouses.find(wh => wh.id === inv.warehouse || wh.name === inv.warehouse);
                    if (w && !w.deleted) {
                        const name = w.name || w.id;
                        whTotals[name] = (whTotals[name] || 0) + (Number(inv.qty) || Number(inv.quantity) || 0);
                    } else if (!w) {
                        const name = inv.warehouse;
                        whTotals[name] = (whTotals[name] || 0) + (Number(inv.qty) || Number(inv.quantity) || 0);
                    }
                }
            });

            const whLabels = Object.keys(whTotals).length > 0 ? Object.keys(whTotals) : ['لا توجد مخازن'];
            const whData = Object.keys(whTotals).length > 0 ? Object.values(whTotals) : [0];

            createOrUpdateChart('warehousesChart', {
                type: 'doughnut',
                data: {
                    labels: whLabels,
                    datasets: [{ data: whData, backgroundColor: ['#f87171', '#3b82f6', '#10b981', '#f59e0b', '#c084fc'], borderWidth: 0, hoverOffset: 10 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 10, font: {family: "'Tajawal'", size: 11} } }, tooltip: { callbacks: { label: function(context) { return ' ' + context.label + ': ' + context.raw.toLocaleString() + ' وحدة'; } } } } }
            });

            // 5. Customer & Supplier Credit & Debt Analysis (Dual Bar Chart)
            let allCreditEntities = [];
            try {
                const cList = (JSON.parse(localStorage.getItem('erp_customers_v1')) || data.customers || []).filter(x => !x.deleted);
                const sList = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
                
                const cMapped = cList.map(c => ({
                    name: c.name || 'عميل',
                    typeLabel: '👤 عميل',
                    creditLimit: parseFloat(c.creditLimit) || 0,
                    debt: Math.abs(parseFloat(c.currentBalance) || 0)
                }));

                const sMapped = sList.map(s => ({
                    name: s.name || 'مورد',
                    typeLabel: '🏭 مورد',
                    creditLimit: parseFloat(s.creditLimit) || 0,
                    debt: Math.abs(parseFloat(s.currentBalance) || 0)
                }));

                allCreditEntities = [...cMapped, ...sMapped];
            } catch(e) {
                allCreditEntities = (data.customers || []).map(c => ({
                    name: c.name || 'عميل',
                    typeLabel: '👤 عميل',
                    creditLimit: parseFloat(c.creditLimit) || 0,
                    debt: Math.abs(parseFloat(c.currentBalance) || 0)
                }));
            }

            let validEntities = allCreditEntities.filter(c => c.creditLimit > 0 || c.debt > 0);

            const topEntities = validEntities.slice(0, 15);
            const creditLabels = topEntities.map(c => `${c.name} (${c.typeLabel})`);
            const creditValues = topEntities.map(c => c.creditLimit);
            const debtValues = topEntities.map(c => c.debt);

            const canvasCust = document.getElementById('customersChart');
            if (canvasCust) {
                const ctxCust = canvasCust.getContext('2d');
                
                const skyGrad = ctxCust.createLinearGradient(0, 0, 0, 320);
                skyGrad.addColorStop(0, '#38bdf8');
                skyGrad.addColorStop(1, '#0284c7');

                const roseGrad = ctxCust.createLinearGradient(0, 0, 0, 320);
                roseGrad.addColorStop(0, '#fb7185');
                roseGrad.addColorStop(1, '#e11d48');

                createOrUpdateChart('customersChart', {
                    type: 'bar',
                    data: {
                        labels: creditLabels,
                        datasets: [
                            {
                                label: '💳 سقف الائتمان (المسموح)',
                                data: creditValues,
                                backgroundColor: skyGrad,
                                borderColor: '#38bdf8',
                                borderWidth: 1,
                                borderRadius: { topLeft: 6, topRight: 6 },
                                barPercentage: 0.45,
                                categoryPercentage: 0.7
                            },
                            {
                                label: '⚠️ المديونية القائمة (المسحوب)',
                                data: debtValues,
                                backgroundColor: roseGrad,
                                borderColor: '#fb7185',
                                borderWidth: 1,
                                borderRadius: { topLeft: 6, topRight: 6 },
                                barPercentage: 0.45,
                                categoryPercentage: 0.7
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                align: 'center',
                                labels: {
                                    color: '#e2e8f0',
                                    usePointStyle: true,
                                    padding: 15,
                                    font: { family: "'Tajawal', sans-serif", size: 12, weight: '800' }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                                titleColor: '#fff',
                                bodyColor: '#38bdf8',
                                borderColor: 'rgba(56, 189, 248, 0.3)',
                                borderWidth: 1,
                                padding: 12,
                                callbacks: {
                                    title: function(items) {
                                        const entity = topEntities[items[0].dataIndex];
                                        return entity.name + ' (' + entity.typeLabel + ')';
                                    },
                                    label: function(context) {
                                        const entity = topEntities[context.dataIndex];
                                        const val = Number(context.raw).toLocaleString('en-US') + ' SDG';
                                        if (context.datasetIndex === 0) {
                                            return ' 💳 سقف الائتمان: ' + val;
                                        } else {
                                            const rem = Math.max(0, entity.creditLimit - entity.debt);
                                            const usage = entity.creditLimit > 0 ? Math.round((entity.debt / entity.creditLimit) * 100) : 0;
                                            return [
                                                ' ⚠️ المديونية القائمة: ' + val + ' (' + usage + '% من السقف)',
                                                ' 🟢 المتبقي المتاح: ' + rem.toLocaleString('en-US') + ' SDG'
                                            ];
                                        }
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: {
                                    color: '#94a3b8',
                                    maxRotation: 35,
                                    minRotation: 20,
                                    font: { family: "'Tajawal', sans-serif", size: 11, weight: '700' }
                                }
                            },
                            y: {
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: {
                                    color: '#94a3b8',
                                    font: { family: "'JetBrains Mono', monospace", size: 11, weight: '700' },
                                    callback: function(val) {
                                        return val.toLocaleString('en-US');
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    </script>

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

        <script>
        function showToast(message, type = 'success') {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'toast-container';
                container.style.cssText = 'position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none;';
                document.body.appendChild(container);
            }
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            let bg = 'rgba(52,211,153,0.15)';
            let border = '1px solid rgba(52,211,153,0.4)';
            let color = '#34d399';
            if (type === 'error') {
                bg = 'rgba(239,68,68,0.15)';
                border = '1px solid rgba(239,68,68,0.4)';
                color = '#f87171';
            } else if (type === 'warning') {
                bg = 'rgba(251,191,36,0.15)';
                border = '1px solid rgba(251,191,36,0.4)';
                color = '#fbbf24';
            }
            toast.style.cssText = `padding:12px 20px; border-radius:12px; font-weight:700; font-size:0.85rem; display:flex; align-items:center; gap:8px; pointer-events:auto; backdrop-filter:blur(20px); box-shadow:0 12px 40px rgba(0,0,0,0.5); color:${color}; background:${bg}; border:${border}; font-family:'Tajawal',sans-serif; animation: toastIn 0.4s cubic-bezier(0.16,1,0.3,1) both;`;
            toast.innerHTML = `<span>${type === 'error' ? '🔴' : (type === 'warning' ? '🟡' : '🟢')}</span> ${message}`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.animation = 'toastOut 0.4s cubic-bezier(0.16,1,0.3,1) both';
                setTimeout(() => toast.remove(), 400);
            }, 5000);
        }

        function runAICFO() {
            document.getElementById('aiCFO-modal').style.display = 'flex';
            document.getElementById('aiCFO-content').innerHTML = 'جاري تحليل بيانات الشركة واستخراج التقرير... يرجى الانتظار ⏳';
            
            // Gather KPI Data from existing DOM elements
            let sales = document.getElementById('kpi-sales') ? document.getElementById('kpi-sales').innerText : 'غير متاح';
            let liquidity = document.getElementById('kpi-liquidity') ? document.getElementById('kpi-liquidity').innerText : 'غير متاح';
            let inventory = document.getElementById('kpi-inventory') ? document.getElementById('kpi-inventory').innerText : 'غير متاح';
            let alerts = document.getElementById('kpi-alerts') ? document.getElementById('kpi-alerts').innerText : 'غير متاح';
            
            let context = `مبيعات اليوم: ${sales} | السيولة المتوفرة: ${liquidity} | الأصناف في المخزون: ${inventory} | نواقص المخزون: ${alerts}`;

            // Try to extract some customer debt info safely
            try {
                let custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
                let debtSum = 0;
                custs.forEach(c => { if(c.currentBalance > 0) debtSum += c.currentBalance; });
                context += ` | إجمالي ديون العملاء علينا أو لنا: ${debtSum}`;
            } catch(e) {}

            let promptMsg = "أنت المدير المالي (CFO) للشركة. بناءً على هذه المؤشرات، اكتب تقريراً إدارياً قصيراً جداً (3 فقرات كحد أقصى) يشرح لصاحب العمل وضع شركته اليوم (الإيجابيات، السلبيات أو المخاطر إن وجدت، توصية سريعة).";

            fetch('api/ai-assistant.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: promptMsg, context: context })
            })
            .then(res => res.json())
            .then(data => {
                if (data.reply) {
                    document.getElementById('aiCFO-content').innerHTML = data.reply.replace(/\*\*(.*?)\*\*/g, '<strong style="color:#38bdf8">$1</strong>').replace(/\n/g, '<br>');
                } else {
                    document.getElementById('aiCFO-content').innerHTML = '<span style="color:#f43f5e">حدث خطأ أثناء استخراج التقرير.</span>';
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('aiCFO-content').innerHTML = '<span style="color:#f43f5e">تعذر الاتصال بخادم الذكاء الاصطناعي.</span>';
            });
        }
        window.addEventListener('themeChanged', (e) => {
            // تطبيق الثيم بدون إعادة تحميل الصفحة مع الحفاظ على السمة المختارة من السمات السبع
            const activeKey = (e && e.detail && e.detail.themeKey) || localStorage.getItem('magaies_active_theme');
            if (activeKey) {
                document.documentElement.setAttribute('data-theme', activeKey);
                if (document.body) document.body.setAttribute('data-theme', activeKey);
            }
        });

        // ═════════════════════════════════════════════════════════════════════
        //  CATEGORIZED DOMAIN HUBS ENGINE ("تجميع حسب الاختصاصات")
        // ═════════════════════════════════════════════════════════════════════
        const DOMAIN_CATEGORIES = [
            {
                id: 'cat_purchases',
                title: 'المشتريات والموردين',
                icon: 'fa-bag-shopping',
                color: '#fbbf24',
                bg: 'rgba(251, 191, 36, 0.2)',
                border: '#fbbf24',
                screens: [
                    { href: 'purchase.php', label: 'فواتير المشتريات', icon: 'fa-bag-shopping', color: '#fbbf24', border: '#fbbf24', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'smart-logistics.php?mode=purchases', label: 'بوابة استلام المشتريات الذكية', icon: 'fa-truck-Moving', color: '#22d3ee', border: '#06b6d4', bg: 'rgba(6, 182, 212, 0.2)' },
                    { href: 'purchase-receipt.php', label: 'استلام شحنات المشتريات', icon: 'fa-box-open', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'purchase-return.php', label: 'مرتجعات المشتريات', icon: 'fa-rotate-left', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' },
                    { href: 'supplier_reliability.php', label: 'اعتمادية وموثوقية الموردين 360°', icon: 'fa-handshake-angle', color: '#c084fc', border: '#a855f7', bg: 'rgba(168, 85, 247, 0.2)' },
                ]
            },
            {
                id: 'cat_sales',
                title: 'المبيعات والفواتير',
                icon: 'fa-cart-shopping',
                color: '#34d399',
                bg: 'rgba(16, 185, 129, 0.2)',
                border: '#10b981',
                screens: [
                    { href: 'direct-sales.php', label: 'المبيعات المباشرة ونقاط البيع', icon: 'fa-cart-shopping', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'sales-invoice.php', label: 'مرتجع المبيعات واسترداد البضائع', icon: 'fa-receipt', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'sales-delivery.php', label: 'بوابة تسليم المبيعات', icon: 'fa-truck-ramp-box', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' },
                    { href: 'reserved-sales.php', label: 'حجوزات المبيعات', icon: 'fa-bookmark', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' }
                ]
            },
            {
                id: 'cat_inventory',
                title: 'المخزون والمستودعات',
                icon: 'fa-warehouse',
                color: '#a78bfa',
                bg: 'rgba(167, 139, 250, 0.2)',
                border: '#a78bfa',
                screens: [
                    { href: 'inventory-summary.php', label: 'ملخص حركة المخزون', icon: 'fa-boxes-stacked', color: '#a78bfa', border: '#a78bfa', bg: 'rgba(167, 139, 250, 0.2)' },
                    { href: 'warehouse-ledger.php', label: 'حركة المخازن والمستودعات', icon: 'fa-dolly', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'inventory-movement.php', label: 'حركات البضائع وأرصدة المستودعات', icon: 'fa-warehouse', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'inventory-adjustment.php?tab=transfer', label: 'التحويل والمناقلة بين المستودعات', icon: 'fa-arrows-left-right', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'item-ledger.php', label: 'دفتر حركة الصنف', icon: 'fa-barcode', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' }
                ]
            },
            {
                id: 'cat_treasury',
                title: 'المعاملات المالية والخزن والبنوك',
                icon: 'fa-vault',
                color: '#34d399',
                bg: 'rgba(52, 211, 153, 0.2)',
                border: '#34d399',
                screens: [
                    { href: 'single-cash-payment.php', label: 'حركة مالية (صرف وقبض مفرد)', icon: 'fa-money-bill-transfer', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' },
                    { href: 'multi-cash-payment.php', label: 'حركة مالية مجمعة', icon: 'fa-money-bill-wave', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'journal-voucher.php', label: 'قيد يومية', icon: 'fa-file-signature', color: '#a855f7', border: '#a855f7', bg: 'rgba(168, 85, 247, 0.2)' },
                    { href: 'posting-accounts.php', label: 'ترحيل الحسابات', icon: 'fa-arrows-rotate', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'checks-cycle.php', label: 'دورة إدارة الشيكات', icon: 'fa-money-check-dollar', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'vouchers-hub.php', label: 'بوابة المعاملات المالية الشاملة', icon: 'fa-vault', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' }
                ]
            },
            {
                id: 'cat_accounting',
                title: 'الحسابات والمالية',
                icon: 'fa-file-invoice-dollar',
                color: '#38bdf8',
                bg: 'rgba(56, 189, 248, 0.2)',
                border: '#38bdf8',
                screens: [
                    { href: 'general-ledger.php', label: 'دفتر الأستاذ العام', icon: 'fa-book-bookmark', color: '#60a5fa', border: '#3b82f6', bg: 'rgba(59, 130, 246, 0.2)' },
                    { href: 'account-statement.php', label: 'كشف حساب شريك', icon: 'fa-file-invoice-dollar', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'trial-balance.php', label: 'ميزان المراجعة', icon: 'fa-scale-balanced', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'pl-statement.php', label: 'قائمة الأرباح والخسائر', icon: 'fa-chart-line', color: '#c084fc', border: '#a855f7', bg: 'rgba(168, 85, 247, 0.2)' },
                    { href: 'posting-accounts.php', label: 'دليل وشجرة الحسابات', icon: 'fa-sitemap', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' },
                ]
            },
            {
                id: 'cat_balances',
                title: 'المقاييس والأرصدة والتقارير',
                icon: 'fa-scale-balanced',
                color: '#f472b6',
                bg: 'rgba(244, 114, 182, 0.2)',
                border: '#f472b6',
                screens: [
                    { href: 'general-ledger.php', label: 'دفتر الأستاذ العام (GL)', icon: 'fa-book-bookmark', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'mainbalances-report.php', label: 'حساب الأرصدة الأساسية', icon: 'fa-scale-balanced', color: '#10b981', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'daily_master_report.php', label: 'التقرير اليومي الشامل', icon: 'fa-calendar-day', color: '#c084fc', border: '#a855f7', bg: 'rgba(168, 85, 247, 0.2)' },
                    { href: 'combined_receivables.php', label: 'حساب مديونية الأرصدة الشامل', icon: 'fa-hand-holding-dollar', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'balances_modern_hub.php', label: 'مركز الأرصدة ومقاصة الحسابات', icon: 'fa-chart-pie', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'aging-report.php', label: 'أعمار الديون والحقوق وسقوف الائتمان', icon: 'fa-clock-rotate-left', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' }
                ]
            },
            {
                id: 'cat_system',
                title: 'الذكاء الاصطناعي والإدارة',
                icon: 'fa-robot',
                color: '#c084fc',
                bg: 'rgba(192, 132, 252, 0.2)',
                border: '#c084fc',
                screens: [
                    { href: 'ai_advisor.php', label: 'الحارس والمستشار الذكي الشامل 360°', icon: 'fa-shield-halved', color: '#22d3ee', border: '#06b6d4', bg: 'rgba(6, 182, 212, 0.2)' },
                    { href: 'ai_goods_advisor.php', label: 'مستشار AI للبضائع والمخازن', icon: 'fa-robot', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'ai_money_advisor.php', label: 'مستشار AI للأموال والسيولة', icon: 'fa-lightbulb', color: '#c084fc', border: '#a855f7', bg: 'rgba(168, 85, 247, 0.2)' },
                    { href: 'sales_forecasting.php', label: 'التنبؤ المالي الذكي (AI Forecasting)', icon: 'fa-chart-line', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(245, 158, 11, 0.2)' },
                    { href: 'smart_inventory_report.php', label: 'تقارير الذكاء الاصطناعي', icon: 'fa-brain', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'master-data.php', label: 'إدارة البيانات الأساسية', icon: 'fa-database', color: '#60a5fa', border: '#3b82f6', bg: 'rgba(59, 130, 246, 0.2)' },
                    { href: 'users-roles.php', label: 'المستخدمون والصلاحيات', icon: 'fa-user-gear', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'settings.php', label: 'إعدادات النظام والقواعد', icon: 'fa-gear', color: '#94a3b8', border: '#64748b', bg: 'rgba(100, 116, 139, 0.2)' },
                    { href: 'settings.php?tab=fonts', label: 'الخطوط وحجم العرض', icon: 'fa-font', color: '#38bdf8', border: '#0284c7', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'backup.php', label: 'النسخ الاحتياطي', icon: 'fa-hard-drive', color: '#a78bfa', border: '#a78bfa', bg: 'rgba(167, 139, 250, 0.2)' },
                    { href: 'audit-log.php', label: 'سجل الرقابة والتدقيق وتتبع العمليات', icon: 'fa-user-shield', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' }
                ]
            }
        ];

        let activeCategoryDropdownId = null;

        function renderCategoryHubs() {
            const grid = document.getElementById('category-hubs-grid');
            if (!grid) return;

            grid.innerHTML = DOMAIN_CATEGORIES.map(cat => `
                <button onclick="toggleCategoryMenu('${cat.id}', event)" id="btn-${cat.id}" class="qa-master-btn cat-hub-btn" style="--btn-border-color: ${cat.border}; --btn-glow-color: ${cat.bg}; border-bottom: 3.5px solid ${cat.border} !important; width: 100%;">
                    <span class="qa-icon" style="background: ${cat.bg}; border: 1px solid ${cat.border}; color: ${cat.color};">
                        <i class="fa-solid ${cat.icon}"></i>
                    </span>
                    <span class="btn-text" style="color: #ffffff; font-size: 0.85rem !important;">${cat.title}</span>
                    <i class="fa-solid fa-chevron-down cat-chevron" style="font-size: 0.72rem; color: ${cat.color}; margin-right: auto; transition: transform 0.3s ease;"></i>
                </button>
            `).join('');
        }

        function toggleCategoryMenu(catId, e) {
            if (e) e.stopPropagation();
            const popover = document.getElementById('category-dropdown-popover');
            const targetBtn = document.getElementById(`btn-${catId}`);
            if (!popover || !targetBtn) return;

            if (activeCategoryDropdownId === catId && popover.style.display === 'block') {
                closeCategoryDropdown();
                return;
            }

            const cat = DOMAIN_CATEGORIES.find(c => c.id === catId);
            if (!cat) return;

            activeCategoryDropdownId = catId;

            // Reset all chevrons
            document.querySelectorAll('.cat-chevron').forEach(ch => ch.style.transform = 'rotate(0deg)');

            const chev = targetBtn.querySelector('.cat-chevron');
            if (chev) chev.style.transform = 'rotate(180deg)';

            // Update title and border color
            popover.style.borderColor = cat.border;
            popover.style.boxShadow = `0 25px 95px rgba(0,0,0,0.99), 0 0 45px ${cat.bg}`;

            const popoverTitle = document.getElementById('cat-popover-title');
            if (popoverTitle) {
                popoverTitle.style.color = cat.color;
                popoverTitle.innerHTML = `<i class="fa-solid ${cat.icon}" style="color: ${cat.color}; font-size: 1.1rem;"></i> 📜 قائمة اختصاص (${cat.title})`;
            }

            const popoverList = document.getElementById('cat-popover-list');
            if (popoverList) {
                const visibleScreens = (window.MagaieesPermissions && typeof window.MagaieesPermissions.canUserAccessScreen === 'function')
                    ? cat.screens.filter(scr => window.MagaieesPermissions.canUserAccessScreen(scr.href))
                    : cat.screens;

                if (visibleScreens.length === 0) {
                    popoverList.innerHTML = '<div style="padding:20px; text-align:center; color:#94a3b8; font-size:0.9rem;">🚫 لا تملك صلاحية للوصول إلى أي شاشة في هذا القسم.</div>';
                } else {
                    popoverList.innerHTML = visibleScreens.map(scr => `
                        <a href="${scr.href}" onclick="window.location.href='${scr.href}'" class="screen-popover-item" style="--item-border-color: ${scr.border}; --item-glow-color: ${scr.bg}; border-bottom: 2.5px solid ${scr.border} !important; background: var(--surface, rgba(15, 23, 42, 0.95)) !important;">
                            <div style="display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1;">
                                <span class="qa-icon" style="width: 32px; height: 32px; min-width: 32px; font-size: 0.88rem; border-radius: 8px; background: ${scr.bg}; border: 1px solid ${scr.border}; color: ${scr.color}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="fa-solid ${scr.icon}"></i>
                                </span>
                                <span class="screen-title" style="font-size: 0.86rem; font-weight: 800; color: var(--text-primary, #ffffff);">${scr.label}</span>
                            </div>
                            <i class="fa-solid fa-arrow-left screen-arrow" style="font-size: 0.8rem; color: ${scr.color}; opacity: 0.9;"></i>
                        </a>
                    `).join('');
                }
            }

            // Calculate Position relative to targetBtn in RTL layout
            popover.style.position = 'fixed';
            popover.style.display = 'block';

            const rect = targetBtn.getBoundingClientRect();
            const popoverWidth = Math.min(380, window.innerWidth - 24);
            popover.style.width = `${popoverWidth}px`;

            let topPos = rect.bottom + 8;
            if (topPos + 400 > window.innerHeight) {
                topPos = Math.max(12, rect.top - 420);
            }
            popover.style.top = `${topPos}px`;

            let rightPos = window.innerWidth - rect.right;
            if (rightPos + popoverWidth > window.innerWidth - 12) {
                rightPos = Math.max(12, window.innerWidth - popoverWidth - 12);
            }
            popover.style.right = `${rightPos}px`;
            popover.style.left = 'auto';
            popover.style.zIndex = '999999';
        }

        function closeCategoryDropdown() {
            const popover = document.getElementById('category-dropdown-popover');
            if (popover) popover.style.display = 'none';
            activeCategoryDropdownId = null;
            document.querySelectorAll('.cat-chevron').forEach(ch => ch.style.transform = 'rotate(0deg)');
        }

        document.addEventListener('click', function(e) {
            const popover = document.getElementById('category-dropdown-popover');
            if (popover && popover.style.display === 'block') {
                const targetBtn = activeCategoryDropdownId ? document.getElementById(`btn-${activeCategoryDropdownId}`) : null;
                if (!popover.contains(e.target) && (!targetBtn || !targetBtn.contains(e.target))) {
                    closeCategoryDropdown();
                }
            }
        });

        // ═════════════════════════════════════════════════════════════════════
        //  SINGLE-ROW 7-ITEM QUICK ACTIONS ENGINE WITH RIGHT-CLICK CUSTOMIZER
        // ═════════════════════════════════════════════════════════════════════
        const ALL_QUICK_ACTIONS = [
            { id: 'audit_log', href: 'audit-log.php', label: 'السجل الرقابي', category: 'الإدارة والصلاحيات', icon: 'fa-user-shield', color: '#fbbf24', bg: 'rgba(245, 158, 11, 0.25)', border: '#f59e0b', isAudit: true },
            { id: 'ai_guardian', href: 'ai_advisor.php', label: 'الحارس الذكي', category: 'الذكاء الاصطناعي', icon: 'fa-shield-halved', color: '#22d3ee', bg: 'rgba(6, 182, 212, 0.2)', border: '#06b6d4' },
            { id: 'ai_goods', href: 'ai_goods_advisor.php', label: 'مستشار AI للبضائع', category: 'الذكاء الاصطناعي', icon: 'fa-robot', color: '#34d399', bg: 'rgba(16, 185, 129, 0.2)', border: '#10b981' },
            { id: 'ai_money', href: 'ai_money_advisor.php', label: 'مستشار AI للأموال', category: 'الذكاء الاصطناعي', icon: 'fa-lightbulb', color: '#c084fc', bg: 'rgba(168, 85, 247, 0.2)', border: '#a855f7' },
            { id: 'alerts_mgr', href: 'alarm.php', label: 'التنبيهات', category: 'التقارير والتنبيهات', icon: 'fa-triangle-exclamation', color: '#fb7185', bg: 'rgba(244, 63, 94, 0.2)', border: '#f43f5e' },
            { id: 'settings', href: 'settings.php', label: 'الإعدادات', category: 'الإدارة والصلاحيات', icon: 'fa-gear', color: '#94a3b8', bg: 'rgba(100, 116, 139, 0.2)', border: '#64748b' },
            { id: 'tools', href: 'engineering_tools.php', label: 'أدوات مساعدة', category: 'أدوات مساعدة', icon: 'fa-toolbox', color: '#f472b6', bg: 'rgba(236, 72, 153, 0.2)', border: '#ec4899' },
            { id: 'inheritance', href: 'engineering_tools.php?tab=inheritance', label: 'الميراث', category: 'أدوات مساعدة', icon: 'fa-scale-balanced', color: '#d97706', bg: 'rgba(217, 119, 6, 0.2)', border: '#d97706' },
            { id: 'smart_inv_rep', href: 'smart_inventory_report.php', label: 'تقارير الذكاء', category: 'التقارير والتنبيهات', icon: 'fa-chart-line', color: '#34d399', bg: 'rgba(16, 185, 129, 0.2)', border: '#10b981' },
            { id: 'sales', href: 'direct-sales.php', label: 'مبيعات', category: 'المبيعات والمشتريات', icon: 'fa-cart-shopping', color: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)', border: '#38bdf8' },
            { id: 'purchase', href: 'purchase.php', label: 'مشتريات', category: 'المبيعات والمشتريات', icon: 'fa-bag-shopping', color: '#fbbf24', bg: 'rgba(251, 191, 36, 0.2)', border: '#fbbf24' },
            { id: 'vouchers', href: 'vouchers-hub.php', label: 'المعاملات المالية', category: 'المالية والحسابات', icon: 'fa-money-bill-transfer', color: '#f87171', bg: 'rgba(248, 113, 113, 0.2)', border: '#ef4444' },
            { id: 'checks', href: 'checks-cycle.php', label: 'شيكات', category: 'المالية والحسابات', icon: 'fa-money-check-dollar', color: '#fbbf24', bg: 'rgba(251, 191, 36, 0.2)', border: '#f59e0b' },
            { id: 'analytics', href: 'analytics.php', label: 'تقارير مالية', category: 'المالية والحسابات', icon: 'fa-chart-column', color: '#c084fc', bg: 'rgba(192, 132, 252, 0.2)', border: '#c084fc' },
            { id: 'smart_reports', href: 'smart_reports_hub.php', label: 'التقرير الذكي', category: 'المالية والحسابات', icon: 'fa-brain', color: '#c084fc', bg: 'rgba(168, 85, 247, 0.2)', border: '#a855f7' },
            { id: 'master_data', href: 'master-data.php', label: 'البيانات الأساسية', category: 'البيانات الأساسية', icon: 'fa-database', color: '#60a5fa', bg: 'rgba(59, 130, 246, 0.2)', border: '#3b82f6' },
            { id: 'jobs', href: 'jobs.php', label: 'المهام', category: 'الإدارة والصلاحيات', icon: 'fa-list-check', color: '#34d399', bg: 'rgba(16, 185, 129, 0.2)', border: '#10b981' },
            { id: 'account_stmt', href: 'account-statement.php', label: 'كشف حساب', category: 'المالية والحسابات', icon: 'fa-file-invoice-dollar', color: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)', border: '#38bdf8' },
            { id: 'ledger', href: 'general-ledger.php', label: 'دفتر الأستاذ', category: 'المالية والحسابات', icon: 'fa-book-bookmark', color: '#60a5fa', bg: 'rgba(59, 130, 246, 0.2)', border: '#3b82f6' },
            { id: 'customer_portfolio', href: 'customer-portfolio.php', label: 'محفظة العملاء', category: 'المبيعات والمشتريات', icon: 'fa-users', color: '#34d399', bg: 'rgba(16, 185, 129, 0.2)', border: '#10b981' },
            { id: 'inv_report', href: 'inventory-report.php', label: 'تقرير المخزون', category: 'المخزون والمستودعات', icon: 'fa-warehouse', color: '#fbbf24', bg: 'rgba(251, 191, 36, 0.2)', border: '#fbbf24' },
            { id: 'sales_delivery', href: 'sales-delivery.php', label: 'تسليم المبيعات', category: 'المبيعات والمشتريات', icon: 'fa-truck-ramp-box', color: '#f87171', bg: 'rgba(248, 113, 113, 0.2)', border: '#ef4444' },
            { id: 'users_roles', href: 'users-roles.php', label: 'الصلاحيات', category: 'الإدارة والصلاحيات', icon: 'fa-user-gear', color: '#c084fc', bg: 'rgba(192, 132, 252, 0.2)', border: '#c084fc' },
            { id: 'partners', href: 'partners_reports_hub.php', label: 'الشركاء', category: 'التقارير والتنبيهات', icon: 'fa-handshake', color: '#22d3ee', bg: 'rgba(6, 182, 212, 0.2)', border: '#06b6d4' },
            { id: 'backup', href: 'backup.php', label: 'النسخ الاحتياطي', category: 'الإدارة والصلاحيات', icon: 'fa-hard-drive', color: '#94a3b8', bg: 'rgba(100, 116, 139, 0.2)', border: '#64748b' }
        ];

        const DEFAULT_7_IDS = ['audit_log', 'ai_guardian', 'ai_goods', 'ai_money', 'alerts_mgr', 'jobs', 'settings', 'smart_inv_rep', 'sales', 'purchase', 'vouchers', 'checks'];
        let currentScreenSortMode = 'default';

        function sortScreensDropdown(mode) {
            currentScreenSortMode = mode;
            
            const btnDefault = document.getElementById('sort-btn-default');
            const btnAlphabetical = document.getElementById('sort-btn-alphabetical');
            const btnCategory = document.getElementById('sort-btn-category');
            
            [btnDefault, btnAlphabetical, btnCategory].forEach(btn => {
                if (!btn) return;
                btn.style.background = 'rgba(255, 255, 255, 0.08)';
                btn.style.borderColor = 'rgba(255, 255, 255, 0.15)';
                btn.style.color = '#94a3b8';
            });

            const activeBtn = document.getElementById(`sort-btn-${mode}`);
            if (activeBtn) {
                activeBtn.style.background = 'rgba(168, 85, 247, 0.35)';
                activeBtn.style.borderColor = '#c084fc';
                activeBtn.style.color = '#ffffff';
            }

            renderScreensDropdown();
        }

        function fitQARibbonText() {
            document.querySelectorAll('#qa-bar-container .qa-ribbon-btn').forEach(btn => {
                const txt = btn.querySelector('.btn-text');
                if (!txt) return;
                const isAiAdvisor = (txt.textContent || '').includes('مستشار AI');
                let size = isAiAdvisor ? 15.5 : 17.5;
                txt.style.setProperty('font-size', size + 'px', 'important');
                const maxW = btn.clientWidth - (btn.id === 'screens-dropdown-btn' ? 28 : 12);
                while (txt.scrollWidth > maxW && size > 11) {
                    size -= 0.5;
                    txt.style.setProperty('font-size', size + 'px', 'important');
                }
            });
        }

        function renderSingleRowQA() {
            let saved = localStorage.getItem('dashboard_quick_actions_12');
            let selectedIds = DEFAULT_7_IDS;
            if (saved) {
                try {
                    let parsed = JSON.parse(saved);
                    if (Array.isArray(parsed) && parsed.length > 0) selectedIds = parsed.slice(0, 12);
                } catch(e){}
            }

            const grid = document.getElementById('qa-single-row-grid');
            if (!grid) return;

            let activeItems = selectedIds.map(id => ALL_QUICK_ACTIONS.find(a => a.id === id)).filter(Boolean);
            if (window.MagaieesPermissions && typeof window.MagaieesPermissions.canUserAccessScreen === 'function') {
                activeItems = activeItems.filter(item => window.MagaieesPermissions.canUserAccessScreen(item.href));
            }
            const custLbl = document.getElementById('qa-customizer-btn-label');
            if (custLbl) custLbl.textContent = `تخصيص (${activeItems.length})`;
            
            grid.innerHTML = activeItems.map(item => `
                <a href="${item.href}" data-href="${item.href}" onclick="window.location.href='${item.href}'" ${item.isAudit ? 'oncontextmenu="if(window.openAuditTrackerModal){event.preventDefault(); window.openAuditTrackerModal();} return false;" title="فتح السجل الرقابي وتتبع العمليات (كليك يمين للبحث الفوري السريع Alt+T)"' : ''} class="qa-master-btn qa-ribbon-btn" style="--btn-border-color: ${item.border}; --btn-glow-color: ${item.bg}; border-bottom: 3.5px solid ${item.border} !important;">
                    <span class="btn-text">${item.label}</span>
                </a>
            `).join('');
            
            requestAnimationFrame(fitQARibbonText);
            renderScreensDropdown();
        }
        window.addEventListener('resize', () => requestAnimationFrame(fitQARibbonText), { passive: true });

        function renderScreensDropdown() {
            let saved = localStorage.getItem('dashboard_quick_actions_12');
            let selectedIds = DEFAULT_7_IDS;
            if (saved) {
                try {
                    let parsed = JSON.parse(saved);
                    if (Array.isArray(parsed) && parsed.length > 0) selectedIds = parsed.slice(0, 12);
                } catch(e){}
            }

            let unselected = ALL_QUICK_ACTIONS.filter(item => !selectedIds.includes(item.id));
            if (window.MagaieesPermissions && typeof window.MagaieesPermissions.canUserAccessScreen === 'function') {
                unselected = unselected.filter(item => window.MagaieesPermissions.canUserAccessScreen(item.href));
            }

            if (currentScreenSortMode === 'alphabetical') {
                unselected.sort((a, b) => a.label.localeCompare(b.label, 'ar'));
            } else if (currentScreenSortMode === 'category') {
                unselected.sort((a, b) => (a.category || '').localeCompare((b.category || ''), 'ar') || a.label.localeCompare(b.label, 'ar'));
            }
            const badge = document.getElementById('screens-count-badge');
            if (badge) badge.innerText = unselected.length;

            const listContainer = document.getElementById('screens-dropdown-list');
            if (!listContainer) return;

            if (unselected.length === 0) {
                listContainer.innerHTML = '<div style="grid-column: 1 / -1; text-align:center; padding:20px; color:#94a3b8; font-size:0.85rem; font-weight: 700;">جميع الشاشات مخصصة في الأزرار السبعة 🎉</div>';
                return;
            }

            listContainer.innerHTML = unselected.map(item => `
                <a href="${item.href}" onclick="window.location.href='${item.href}'" class="screen-popover-item screen-dropdown-item" style="--item-border-color: ${item.border}; --item-glow-color: ${item.bg}; --item-color: ${item.color}; border-bottom: 2.5px solid ${item.border} !important;">
                    <div style="display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1;">
                        <span class="qa-icon" style="width: 28px; height: 28px; min-width: 28px; font-size: 0.8rem; border-radius: 8px; background: ${item.bg}; border: 1px solid ${item.border}; color: ${item.color}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa-solid ${item.icon}"></i>
                        </span>
                        <span class="screen-title" style="font-size: 0.86rem; font-weight: 800; color: var(--text-primary, #ffffff); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.label}</span>
                    </div>
                    <i class="fa-solid fa-arrow-left screen-arrow" style="font-size: 0.75rem; color: ${item.color}; opacity: 0.85;"></i>
                </a>
            `).join('');
        }

        function positionScreensDropdown() {
            const menu = document.getElementById('screens-dropdown-menu');
            const btn = document.getElementById('screens-dropdown-btn');
            if (!menu || !btn) return;
            
            menu.style.position = 'fixed';
            // Start at the very top of the screen (12px), covering top clock/user bar
            menu.style.top = '12px';
            
            const menuWidth = Math.min(500, window.innerWidth - 24);
            menu.style.width = `${menuWidth}px`;
            
            // Align right side near the button in RTL layout
            const rect = btn.getBoundingClientRect();
            let rightPos = window.innerWidth - rect.right;
            if (rightPos + menuWidth > window.innerWidth - 12) {
                rightPos = Math.max(12, window.innerWidth - menuWidth - 12);
            }
            menu.style.right = `${rightPos}px`;
            menu.style.left = 'auto';
            menu.style.zIndex = '999999';
        }

        function toggleScreensDropdown(e) {
            if (e) e.stopPropagation();
            const menu = document.getElementById('screens-dropdown-menu');
            const chev = document.getElementById('screens-chevron');
            if (!menu) return;

            const isHidden = menu.style.display === 'none' || menu.style.display === '';
            if (isHidden) {
                renderScreensDropdown();
                positionScreensDropdown();
                menu.style.display = 'block';
                if (chev) chev.style.transform = 'rotate(180deg)';
                const searchInp = document.getElementById('screens-search-input');
                if (searchInp) {
                    searchInp.value = '';
                    setTimeout(() => searchInp.focus(), 50);
                }
            } else {
                menu.style.display = 'none';
                if (chev) chev.style.transform = 'rotate(0deg)';
            }
        }

        function filterScreensDropdown() {
            const query = (document.getElementById('screens-search-input')?.value || '').toLowerCase().trim();
            const items = document.querySelectorAll('#screens-dropdown-list .screen-dropdown-item');
            items.forEach(el => {
                const text = el.textContent.toLowerCase();
                el.style.display = text.includes(query) ? 'flex' : 'none';
            });
        }

        document.addEventListener('click', function(e) {
            const menu = document.getElementById('screens-dropdown-menu');
            const btn = document.getElementById('screens-dropdown-btn');
            if (menu && menu.style.display === 'block') {
                if (!menu.contains(e.target) && !btn.contains(e.target)) {
                    menu.style.display = 'none';
                    const chev = document.getElementById('screens-chevron');
                    if (chev) chev.style.transform = 'rotate(0deg)';
                }
            }
        });

        window.addEventListener('scroll', function() {
            const menu = document.getElementById('screens-dropdown-menu');
            if (menu && menu.style.display === 'block') {
                positionScreensDropdown();
            }
        }, { passive: true });

        window.addEventListener('resize', function() {
            const menu = document.getElementById('screens-dropdown-menu');
            if (menu && menu.style.display === 'block') {
                positionScreensDropdown();
            }
        }, { passive: true });

        function openQACustomizer(e) {
            if (e && e.preventDefault) e.preventDefault();
            let modal = document.getElementById('qa-customizer-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'qa-customizer-modal';
                modal.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); backdrop-filter:blur(8px); z-index:99999; display:flex; align-items:center; justify-content:center; direction:rtl;';
                document.body.appendChild(modal);
            }
            
            let saved = localStorage.getItem('dashboard_quick_actions_12');
            let selectedIds = DEFAULT_7_IDS;
            if (saved) {
                try { selectedIds = JSON.parse(saved); } catch(e){}
            }
            
            let listHTML = ALL_QUICK_ACTIONS.map(item => {
                const isChecked = selectedIds.includes(item.id) ? 'checked' : '';
                return `
                    <label style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:rgba(15,23,42,0.7); border:1px solid rgba(255,255,255,0.08); border-radius:10px; cursor:pointer; transition:0.2s;">
                        <input type="checkbox" class="qa-chk" value="${item.id}" ${isChecked} onchange="limitQAChecks(this)" style="width:18px; height:18px; accent-color:#38bdf8; cursor:pointer;">
                        <span style="width:24px; height:24px; border-radius:6px; background:${item.bg}; border:1px solid ${item.border}; color:${item.color}; display:inline-flex; align-items:center; justify-content:center; font-size:0.75rem;">
                            <i class="fa-solid ${item.icon}"></i>
                        </span>
                        <span style="font-weight:700; font-size:0.85rem; color:#fff;">${item.label}</span>
                    </label>
                `;
            }).join('');

            modal.innerHTML = `
                <div class="glass-panel" style="width:90%; max-width:560px; max-height:85vh; padding:25px; border-radius:20px; border:1px solid rgba(56,189,248,0.3); background:#0f172a; box-shadow:0 20px 60px rgba(0,0,0,0.9); overflow-y:auto;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:12px;">
                        <h3 style="margin:0; font-weight:800; font-size:1.1rem; color:#38bdf8; display:flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-sliders"></i> تخصيص أزرار الوصول السريع (12 زر في صفين متساويين)
                        </h3>
                        <button onclick="document.getElementById('qa-customizer-modal').style.display='none'" style="background:none; border:none; color:#f87171; font-size:1.4rem; cursor:pointer;">&times;</button>
                    </div>
                    <p style="font-size:0.8rem; color:#94a3b8; margin-bottom:15px;">اختر 12 زر ليتم عرضها في الصفين العلويين (5 في الصف الأول + 7 في الصف الثاني):</p>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:10px; margin-bottom:20px;">
                        ${listHTML}
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:10px;">
                        <button onclick="document.getElementById('qa-customizer-modal').style.display='none'" style="padding:8px 16px; border-radius:10px; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color:#fff; cursor:pointer;">إلغاء</button>
                        <button onclick="saveQACustomization()" style="padding:8px 20px; border-radius:10px; background:linear-gradient(135deg, #38bdf8, #2563eb); border:none; color:#fff; font-weight:800; cursor:pointer; box-shadow:0 4px 15px rgba(56,189,248,0.4);">حفظ التبديل</button>
                    </div>
                </div>
            `;
            modal.style.display = 'flex';
        }

        function limitQAChecks(chk) {
            const checked = document.querySelectorAll('.qa-chk:checked');
            if (checked.length > 12) {
                chk.checked = false;
                if (typeof showToast === 'function') showToast('⚠️ يرجى اختيار 12 زر كحد أقصى للصفين', 'warning');
            }
        }

        function saveQACustomization() {
            const checked = Array.from(document.querySelectorAll('.qa-chk:checked')).map(c => c.value);
            if (checked.length === 0) {
                if (typeof showToast === 'function') showToast('⚠️ يرجى اختيار زر واحد على الأقل', 'warning');
                return;
            }
            localStorage.setItem('dashboard_quick_actions_12', JSON.stringify(checked.slice(0, 12)));
            renderSingleRowQA();
            renderScreensDropdown();
            document.getElementById('qa-customizer-modal').style.display = 'none';
            if (typeof showToast === 'function') showToast('✅ تم حفظ التبديل وعرض الأزرار الـ 12 بنجاح', 'success');
        }

        // Initialize Quick Actions, Category Hubs & Screens Dropdown immediately on DOM ready
        function initDashboardNav() {
            renderCategoryHubs();
            renderSingleRowQA();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initDashboardNav);
        } else {
            initDashboardNav();
        }
        </script>
    
        <!-- Client Support Tickets FAB (Removed as per user request to integrate into AI menu) -->

        <!-- Client Support Modal -->
        <div id="client-support-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(10px); z-index: 10000; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
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
    <style id="dashboard-purchase-constitution-2027-style">
        /* 1. THEME VARIABLES (5 Dashboard Themes + System Dark & Light Tones) */
        :root,
        html[data-theme="emerald"],
        html[data-dash-theme="emerald"] {
            --bg-main: #030712;
            --bg-grad: #0b1730;
            --topbar-bg: #071124;
            --card-bg: linear-gradient(155deg, rgba(16, 26, 48, 0.94) 0%, rgba(8, 14, 28, 0.97) 100%);
            --card-inner: linear-gradient(145deg, rgba(5, 10, 21, 0.88) 0%, rgba(11, 19, 37, 0.92) 100%);
            --border-color: rgba(56, 189, 248, 0.28);
            --accent: #10b981;
            --accent-soft: rgba(16, 185, 129, 0.28);
        }
        html[data-theme="original"],
        html[data-dash-theme="original"] {
            --bg-main: #0b132b;
            --bg-grad: #1c2541;
            --topbar-bg: #060b19;
            --card-bg: rgba(28, 37, 65, 0.86);
            --card-inner: rgba(11, 19, 43, 0.80);
            --border-color: rgba(99, 102, 241, 0.42);
            --accent: #6366f1;
            --accent-soft: rgba(99, 102, 241, 0.25);
        }
        html[data-theme="navy"],
        html[data-dash-theme="navy"],
        html:not(.light-theme)[data-theme="midnight_obsidian"],
        html:not(.light-theme)[data-light-tone="dark_midnight"] {
            --bg-main: #061024;
            --bg-grad: #0d224a;
            --topbar-bg: #040b18;
            --card-bg: rgba(14, 34, 74, 0.86);
            --card-inner: rgba(6, 16, 36, 0.80);
            --border-color: rgba(56, 189, 248, 0.42);
            --accent: #38bdf8;
            --accent-soft: rgba(56, 189, 248, 0.25);
        }
        html[data-theme="gold"],
        html[data-dash-theme="gold"],
        html:not(.light-theme)[data-theme="mocha_brown"],
        html:not(.light-theme)[data-light-tone="mocha_brown"] {
            --bg-main: #181105;
            --bg-grad: #302107;
            --topbar-bg: #120c03;
            --card-bg: rgba(48, 34, 10, 0.86);
            --card-inner: rgba(22, 15, 4, 0.80);
            --border-color: rgba(245, 158, 11, 0.45);
            --accent: #f59e0b;
            --accent-soft: rgba(245, 158, 11, 0.25);
        }
        html[data-theme="platinum"],
        html[data-dash-theme="platinum"],
        html:not(.light-theme)[data-theme="slate_twilight"],
        html:not(.light-theme)[data-light-tone="slate_twilight"] {
            --bg-main: #111827;
            --bg-grad: #1f2937;
            --topbar-bg: #090d16;
            --card-bg: rgba(31, 41, 55, 0.88);
            --card-inner: rgba(17, 24, 39, 0.82);
            --border-color: rgba(148, 163, 184, 0.42);
            --accent: #34d399;
            --accent-soft: rgba(52, 211, 153, 0.25);
        }
        html:not(.light-theme)[data-theme="pure_oled"],
        html:not(.light-theme)[data-light-tone="pure_oled"] {
            --bg-main: #000000;
            --bg-grad: #050811;
            --topbar-bg: #020409;
            --card-bg: rgba(7, 11, 20, 0.94);
            --card-inner: rgba(2, 4, 9, 0.92);
            --border-color: rgba(56, 189, 248, 0.42);
            --accent: #10b981;
            --accent-soft: rgba(16, 185, 129, 0.25);
        }

        html:not(.light-theme) body,
        html:not(.light-theme) .dash-main {
            background:
                radial-gradient(1100px circle at 12% 0%, rgba(56, 189, 248, 0.14), transparent 55%),
                radial-gradient(1000px circle at 88% 8%, rgba(16, 185, 129, 0.13), transparent 55%),
                radial-gradient(900px circle at 50% 95%, rgba(245, 158, 11, 0.08), transparent 60%),
                linear-gradient(180deg, var(--bg-grad, #0b1730) 0%, var(--bg-main, #030712) 100%) !important;
            color: #f8fafc !important;
        }

        /* 2. SELF-CONTAINED LAYOUT & GRID SYSTEM FOR #new-unified-dashboard-design */
        #new-unified-dashboard-design,
        #new-unified-dashboard-design * {
            box-sizing: border-box;
            font-family: 'IBM Plex Sans Arabic', 'Cairo', 'Tajawal', sans-serif;
        }
        #new-unified-dashboard-design .num-font,
        #new-unified-dashboard-design .num-font * {
            font-family: 'Cairo', 'IBM Plex Sans Arabic', sans-serif !important;
        }
        #new-unified-dashboard-design {
            display: flex !important;
            flex-direction: column !important;
            gap: 24px !important;
            width: 100% !important;
            padding-bottom: 28px !important;
            direction: rtl !important;
        }

        /* 12-Column Responsive Bento Sections */
        #new-unified-dashboard-design #sec-calm,
        #new-unified-dashboard-design #sec-global-ai-innovations,
        #new-unified-dashboard-design #sec-flow,
        #new-unified-dashboard-design #sec-ops-audit {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 24px !important;
            width: 100%;
            align-items: stretch;
        }
        #new-unified-dashboard-design .lg\:col-span-4 {
            grid-column: span 4 / span 4;
        }
        #new-unified-dashboard-design .lg\:col-span-6 {
            grid-column: span 6 / span 6;
        }
        @media (max-width: 1150px) {
            #new-unified-dashboard-design .lg\:col-span-4,
            #new-unified-dashboard-design .lg\:col-span-6 {
                grid-column: span 12 / span 12 !important;
            }
        }

        /* Inner Grids & Flex Utilities */
        #new-unified-dashboard-design .grid { display: grid; }
        #new-unified-dashboard-design .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        #new-unified-dashboard-design .grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        #new-unified-dashboard-design .sm\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        @media (max-width: 640px) {
            #new-unified-dashboard-design .sm\:grid-cols-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        #new-unified-dashboard-design .gap-1 { gap: 4px; }
        #new-unified-dashboard-design .gap-1\.5 { gap: 6px; }
        #new-unified-dashboard-design .gap-2 { gap: 8px; }
        #new-unified-dashboard-design .gap-2\.5 { gap: 10px; }
        #new-unified-dashboard-design .gap-3 { gap: 12px; }
        #new-unified-dashboard-design .gap-4 { gap: 16px; }
        #new-unified-dashboard-design .gap-5 { gap: 20px; }

        #new-unified-dashboard-design .flex { display: flex; }
        #new-unified-dashboard-design .flex-1 { flex: 1 1 0%; }
        #new-unified-dashboard-design .flex-col { flex-direction: column; }
        #new-unified-dashboard-design .flex-wrap { flex-wrap: wrap; }
        #new-unified-dashboard-design .items-center { align-items: center; }
        #new-unified-dashboard-design .justify-between { justify-content: space-between; }
        #new-unified-dashboard-design .justify-center { justify-content: center; }
        #new-unified-dashboard-design .hidden { display: none !important; }
        #new-unified-dashboard-design .block { display: block; }
        #new-unified-dashboard-design .relative { position: relative; }
        #new-unified-dashboard-design .absolute { position: absolute; }

        /* Spacing & Sizing Utilities */
        #new-unified-dashboard-design .w-full { width: 100%; }
        #new-unified-dashboard-design .w-24 { width: 92px; }
        #new-unified-dashboard-design .h-24 { height: 92px; }
        #new-unified-dashboard-design .h-full { height: 100%; }
        #new-unified-dashboard-design .h-2\.5 { height: 10px; }
        #new-unified-dashboard-design .max-h-44 { max-height: 180px; }
        #new-unified-dashboard-design .overflow-y-auto { overflow-y: auto; }
        #new-unified-dashboard-design .overflow-hidden { overflow: hidden; }
        #new-unified-dashboard-design .-rotate-90 { transform: rotate(-90deg); }

        #new-unified-dashboard-design .p-1 { padding: 4px; }
        #new-unified-dashboard-design .p-2\.5 { padding: 10px 12px; }
        #new-unified-dashboard-design .p-3 { padding: 12px; }
        #new-unified-dashboard-design .p-3\.5 { padding: 14px 16px; }
        #new-unified-dashboard-design .p-4 { padding: 16px; }
        #new-unified-dashboard-design .p-5 { padding: 20px; }
        #new-unified-dashboard-design .px-2 { padding-left: 8px; padding-right: 8px; }
        #new-unified-dashboard-design .px-2\.5 { padding-left: 10px; padding-right: 10px; }
        #new-unified-dashboard-design .px-3 { padding-left: 12px; padding-right: 12px; }
        #new-unified-dashboard-design .px-3\.5 { padding-left: 14px; padding-right: 14px; }
        #new-unified-dashboard-design .px-4 { padding-left: 16px; padding-right: 16px; }
        #new-unified-dashboard-design .py-0\.5 { padding-top: 2px; padding-bottom: 2px; }
        #new-unified-dashboard-design .py-1 { padding-top: 4px; padding-bottom: 4px; }
        #new-unified-dashboard-design .py-1\.5 { padding-top: 6px; padding-bottom: 6px; }
        #new-unified-dashboard-design .py-2 { padding-top: 8px; padding-bottom: 8px; }
        #new-unified-dashboard-design .py-2\.5 { padding-top: 10px; padding-bottom: 10px; }
        #new-unified-dashboard-design .py-3 { padding-top: 12px; padding-bottom: 12px; }
        #new-unified-dashboard-design .py-3\.5 { padding-top: 12px; padding-bottom: 12px; }
        #new-unified-dashboard-design .pt-2 { padding-top: 8px; }
        #new-unified-dashboard-design .pt-3 { padding-top: 12px; }
        #new-unified-dashboard-design .pb-2 { padding-bottom: 8px; }
        #new-unified-dashboard-design .pb-3 { padding-bottom: 10px; }
        #new-unified-dashboard-design .pb-4 { padding-bottom: 14px; }
        #new-unified-dashboard-design .pr-4 { padding-right: 16px; }
        #new-unified-dashboard-design .my-2 { margin-top: 8px; margin-bottom: 8px; }
        #new-unified-dashboard-design .my-3 { margin-top: 12px; margin-bottom: 12px; }
        #new-unified-dashboard-design .mt-2 { margin-top: 8px; }
        #new-unified-dashboard-design .mt-4 { margin-top: 14px; }
        #new-unified-dashboard-design .mb-1 { margin-bottom: 5px; }
        #new-unified-dashboard-design .mb-2 { margin-bottom: 8px; }
        #new-unified-dashboard-design .mb-3 { margin-bottom: 12px; }
        #new-unified-dashboard-design .mb-4 { margin-bottom: 14px; }
        #new-unified-dashboard-design .mb-6 { margin-bottom: 20px; }

        #new-unified-dashboard-design .space-y-2 > * + * { margin-top: 8px; }
        #new-unified-dashboard-design .space-y-3\.5 > * + * { margin-top: 12px; }
        #new-unified-dashboard-design .space-y-4 > * + * { margin-top: 14px; }

        /* Typography & Alignment */
        #new-unified-dashboard-design .bento-card h2 { font-size: 18px !important; line-height: 1.25 !important; margin: 0 !important; white-space: nowrap !important; font-weight: 900 !important; }
        #new-unified-dashboard-design .text-\[10px\] { font-size: 0.72rem; line-height: 1.35; }
        #new-unified-dashboard-design .text-\[11px\] { font-size: 0.76rem; line-height: 1.4; }
        #new-unified-dashboard-design .text-\[13\.5px\] { font-size: 17.5px !important; line-height: 1.25 !important; }
        #new-unified-dashboard-design .text-xs { font-size: 0.82rem; line-height: 1.45; }
        #new-unified-dashboard-design .text-sm { font-size: 0.92rem; line-height: 1.5; }
        #new-unified-dashboard-design .text-base { font-size: 1.02rem; line-height: 1.5; }
        #new-unified-dashboard-design .text-xl { font-size: 1.25rem; line-height: 1.4; }
        #new-unified-dashboard-design .text-2xl { font-size: 1.55rem; line-height: 1.25; }
        #new-unified-dashboard-design .text-3xl { font-size: 1.85rem; line-height: 1.2; }
        #new-unified-dashboard-design .text-4xl { font-size: 2.25rem; line-height: 1.15; }
        #new-unified-dashboard-design .font-bold { font-weight: 700; }
        #new-unified-dashboard-design .font-extrabold { font-weight: 800; }
        #new-unified-dashboard-design .font-black { font-weight: 900; }
        #new-unified-dashboard-design .text-right { text-align: right; }
        #new-unified-dashboard-design .text-left { text-align: left; }
        #new-unified-dashboard-design .text-center { text-align: center; }

        /* Borders & Radii */
        #new-unified-dashboard-design .rounded-md { border-radius: 6px; }
        #new-unified-dashboard-design .rounded-lg { border-radius: 9px; }
        #new-unified-dashboard-design .rounded-xl { border-radius: 12px; }
        #new-unified-dashboard-design .rounded-2xl { border-radius: 16px; }
        #new-unified-dashboard-design .rounded-full { border-radius: 9999px; }
        #new-unified-dashboard-design .border-t { border-top: 1px solid rgba(255, 255, 255, 0.12); }
        #new-unified-dashboard-design .border-b { border-bottom: 1px solid rgba(255, 255, 255, 0.12); }
        #new-unified-dashboard-design .border-r { border-right: 1px solid rgba(255, 255, 255, 0.12); }
        #new-unified-dashboard-design .border-r-4 { border-right-width: 4px !important; border-right-style: solid !important; }
        #new-unified-dashboard-design .border-r-sky-400 { border-right-color: #38bdf8 !important; }
        #new-unified-dashboard-design .border-r-rose-500 { border-right-color: #f43f5e !important; }
        #new-unified-dashboard-design .border-r-amber-400 { border-right-color: #fbbf24 !important; }

        /* Progress Bars & Gradients */
        #new-unified-dashboard-design .bg-slate-800 { background-color: rgba(15, 23, 42, 0.75); }
        #new-unified-dashboard-design .bg-emerald-500 { background-color: #10b981; }
        #new-unified-dashboard-design .bg-amber-400 { background-color: #fbbf24; }
        #new-unified-dashboard-design .bg-rose-500 { background-color: #f43f5e; }
        #new-unified-dashboard-design .bg-gradient-to-l.from-amber-400.to-emerald-500 {
            background: linear-gradient(to left, #fbbf24, #10b981) !important;
        }
        #new-unified-dashboard-design .bg-gradient-to-l.from-rose-500.to-amber-500 {
            background: linear-gradient(to left, #f43f5e, #f59e0b) !important;
        }

        /* Colors */
        #new-unified-dashboard-design .text-white { color: #ffffff; }
        #new-unified-dashboard-design .text-slate-950 { color: #020617 !important; }
        #new-unified-dashboard-design .text-slate-300 { color: #cbd5e1; }
        #new-unified-dashboard-design .text-slate-400 { color: #94a3b8; }
        #new-unified-dashboard-design .text-emerald-200 { color: #a7f3d0; }
        #new-unified-dashboard-design .text-emerald-300 { color: #6ee7b7; }
        #new-unified-dashboard-design .text-emerald-400 { color: #34d399; }
        #new-unified-dashboard-design .text-sky-300 { color: #7dd3fc; }
        #new-unified-dashboard-design .text-sky-400 { color: #38bdf8; }
        #new-unified-dashboard-design .text-amber-200 { color: #fde68a; }
        #new-unified-dashboard-design .text-amber-300 { color: #fcd34d; }
        #new-unified-dashboard-design .text-amber-400 { color: #fbbf24; }
        #new-unified-dashboard-design .text-rose-200 { color: #fecdd3; }
        #new-unified-dashboard-design .text-rose-300 { color: #fda4af; }
        #new-unified-dashboard-design .text-rose-400 { color: #fb7185; }
        #new-unified-dashboard-design .text-teal-300 { color: #5eead4; }
        #new-unified-dashboard-design .text-teal-400 { color: #2dd4bf; }
        #new-unified-dashboard-design .text-slate-700\/50 { color: rgba(51, 65, 85, 0.55); }

        /* Badges & Pill Backgrounds */
        #new-unified-dashboard-design .bg-emerald-500\/20 { background-color: rgba(16, 185, 129, 0.2); }
        #new-unified-dashboard-design .bg-sky-500\/20 { background-color: rgba(14, 165, 233, 0.2); }
        #new-unified-dashboard-design .bg-amber-500\/20 { background-color: rgba(245, 158, 11, 0.2); }
        #new-unified-dashboard-design .bg-rose-500\/20 { background-color: rgba(244, 63, 94, 0.2); }
        #new-unified-dashboard-design .bg-white\/5 { background-color: rgba(255, 255, 255, 0.06); }
        #new-unified-dashboard-design .radar-glow-btn {
            min-height: 34px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 4px !important;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%) !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            border: 1.5px solid rgba(148, 163, 184, 0.48) !important;
            border-bottom: 3px solid #38bdf8 !important;
            border-radius: 10px !important;
            font-weight: 900 !important;
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.42), 0 3px 10px -2px rgba(56, 189, 248, 0.25), inset 0 1.5px 0 rgba(255, 255, 255, 0.28) !important;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        #new-unified-dashboard-design .radar-glow-btn:hover,
        #new-unified-dashboard-design button[onclick*="openFullCardModal"]:not(.exec-jewel-btn):hover,
        #new-unified-dashboard-design button[onclick*="openChartCardModal"]:hover {
            transform: translateY(-2.5px) scale(1.03) !important;
            border-color: #38bdf8 !important;
            border-bottom-color: #0ea5e9 !important;
            box-shadow: 0 10px 22px -3px rgba(2, 132, 199, 0.65), 0 0 18px rgba(56, 189, 248, 0.6), inset 0 1.5px 2px rgba(255, 255, 255, 0.45) !important;
        }
        #new-unified-dashboard-design button[onclick*="openFullCardModal"]:not(.exec-jewel-btn) {
            background: linear-gradient(180deg, #047857 0%, #064e3b 100%) !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            border: 1.5px solid #34d399 !important;
            border-bottom: 3px solid #10b981 !important;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.35) !important;
            transition: all 0.22s ease !important;
        }
        #new-unified-dashboard-design button[onclick*="openChartCardModal"] {
            background: linear-gradient(180deg, #b45309 0%, #78350f 100%) !important;
            color: #fde047 !important;
            -webkit-text-fill-color: #fde047 !important;
            border: 1.5px solid #fbbf24 !important;
            border-bottom: 3px solid #f59e0b !important;
            box-shadow: 0 4px 12px rgba(180, 83, 9, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.35) !important;
            transition: all 0.22s ease !important;
        }
        #dash-partners-radar-list > div, #dash-top-items-list > div, #dash-debt-aging-box > div, #dash-forecast-list > div { transition: all 0.22s ease; }
        #dash-partners-radar-list > div:hover, #dash-top-items-list > div:hover, #dash-debt-aging-box > div:hover, #dash-forecast-list > div:hover,
        html.light-theme #dash-partners-radar-list > div:hover, html.light-theme #dash-top-items-list > div:hover, html.light-theme #dash-debt-aging-box > div:hover, html.light-theme #dash-forecast-list > div:hover {
            transform: translateX(-3px) translateY(-1.5px);
            border-color: #0284c7 !important;
            box-shadow: 0 8px 20px -3px rgba(2, 132, 199, 0.55), 0 0 16px rgba(56, 189, 248, 0.55), inset 0 0 10px rgba(56, 189, 248, 0.18) !important;
        }
        html.light-theme #new-unified-dashboard-design .bg-emerald-600,
        html.light-theme #new-unified-dashboard-design .bg-emerald-700,
        html.light-theme #new-unified-dashboard-design .bg-rose-600,
        html.light-theme #new-unified-dashboard-design .bg-sky-600 {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }
        #dash-top-items-list, #dash-forecast-list { gap: 7px !important; justify-content: stretch !important; }
        #dash-top-items-list > div, #dash-forecast-list > div { flex: 1 1 0% !important; padding: 10px 14px !important; display: flex !important; align-items: center !important; justify-content: space-between !important; gap: 10px !important; }
        #dash-top-items-list > div > span:first-child, #dash-forecast-list > div > span:first-child { font-family: 'Cairo', 'Tajawal', sans-serif !important; font-size: 16.5px !important; font-weight: 900 !important; line-height: 1.35 !important; color: #ffffff !important; flex: 1 1 auto !important; min-width: 0 !important; white-space: normal !important; overflow: visible !important; text-overflow: clip !important; }
        #dash-forecast-list > div > strong { font-size: 16px !important; font-weight: 900 !important; }
        #dash-partners-radar-list > div .font-black.text-white, #dash-debt-aging-box > div .font-black.text-white { font-family: 'Cairo', 'Tajawal', sans-serif !important; font-size: 16px !important; font-weight: 900 !important; line-height: 1.35 !important; margin-bottom: 3px !important; color: #ffffff !important; }
        #dash-top-items-list > div small, #partner-items-modal small { font-family: 'Cairo', 'Tajawal', sans-serif !important; color: #fde68a !important; font-weight: 800 !important; font-size: 13.5px !important; margin-right: 4px !important; }
        #dash-partners-radar-list > div span.text-xs, #dash-debt-aging-box > div span.text-xs, #dash-partners-radar-list > div span.text-xs b, #dash-debt-aging-box > div span.text-xs b { font-size: 12.5px !important; font-weight: 500 !important; color: #fde68a !important; font-family: 'Cairo', 'Tajawal', sans-serif !important; line-height: 1.38 !important; }
        #new-unified-dashboard-design .wa-svg-btn, #partner-items-modal .wa-svg-btn { width: 32px !important; height: 30px !important; border-radius: 8px !important; background: linear-gradient(180deg, #25D366 0%, #128C7E 100%) !important; border: 1.5px solid #86efac !important; border-bottom: 3px solid #065f46 !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; cursor: pointer !important; box-shadow: 0 3px 8px rgba(37, 211, 102, 0.35) !important; transition: transform 0.16s ease, box-shadow 0.16s ease !important; flex-shrink: 0 !important; }
        #new-unified-dashboard-design .wa-svg-btn:hover, #partner-items-modal .wa-svg-btn:hover { transform: translateY(-2px) !important; box-shadow: 0 6px 14px rgba(37, 211, 102, 0.65) !important; }
        #new-unified-dashboard-design .bg-indigo-950 { background-color: #1e1b4b !important; color: #ffffff !important; }
        #new-unified-dashboard-design .bg-blue-950 { background-color: #172554 !important; color: #ffffff !important; }
        #new-unified-dashboard-design .bg-emerald-900 { background-color: #064e3b !important; color: #ffffff !important; }
        #new-unified-dashboard-design .bg-amber-900 { background-color: #78350f !important; color: #ffffff !important; }
        #new-unified-dashboard-design .bg-slate-700 { background-color: #334155 !important; color: #ffffff !important; }
        #new-unified-dashboard-design .bg-emerald-600 { background-color: #059669 !important; color: #ffffff !important; }
        #new-unified-dashboard-design .bg-blue-700 { background-color: #1d4ed8 !important; color: #ffffff !important; }
        #new-unified-dashboard-design .bg-amber-600 { background-color: #d97706 !important; color: #ffffff !important; }
        #new-unified-dashboard-design .bg-slate-300 { background-color: #cbd5e1 !important; color: #0f172a !important; }

        /* Cards, Top Bar & Inner Boxes — 2027 World-Class Enterprise Glass Architecture */
        #new-unified-dashboard-design .top-bar {
            background: var(--topbar-bg, #071124) !important;
            border: 1.5px solid var(--border-color, rgba(56, 189, 248, 0.3)) !important;
            border-radius: 18px !important;
            padding: 12px 18px !important;
            box-shadow: 0 14px 35px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(255, 255, 255, 0.14) !important;
        }
        #new-unified-dashboard-design .bento-card {
            position: relative;
            overflow: hidden;
            background: var(--card-bg, linear-gradient(155deg, rgba(16, 26, 48, 0.94) 0%, rgba(8, 14, 28, 0.97) 100%)) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1.5px solid var(--border-color, rgba(56, 189, 248, 0.28)) !important;
            border-top: 2px solid rgba(255, 255, 255, 0.22) !important;
            border-radius: 20px !important;
            box-shadow: 0 16px 40px -8px rgba(0, 0, 0, 0.65), inset 0 1px 0 rgba(255, 255, 255, 0.14) !important;
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.22s ease, box-shadow 0.22s ease !important;
        }
        @media (min-width: 1024px) {
            #new-unified-dashboard-design #sec-global-ai-innovations {
                grid-template-columns: 4.5fr 3fr 4.5fr !important;
            }
            #new-unified-dashboard-design #sec-ops-audit {
                grid-template-columns: 1fr 1fr 1fr !important;
            }
            #new-unified-dashboard-design #sec-ops-audit > .lg\:col-span-4 {
                grid-column: span 1 / span 1 !important;
            }
        }
        #new-unified-dashboard-design .dash-top-command-strip {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.78) 0%, rgba(11, 18, 34, 0.85) 100%) !important;
            border: 1px solid rgba(148, 163, 184, 0.22) !important;
            box-shadow: 0 8px 24px -6px rgba(0, 0, 0, 0.42), inset 0 1px 0 rgba(255, 255, 255, 0.08) !important;
        }
        html #new-unified-dashboard-design .dash-cmd-btn,
        html.light-theme #new-unified-dashboard-design .dash-cmd-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 5px !important;
            height: 36px !important;
            padding: 0 12px !important;
            border-radius: 999px !important;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%) !important;
            border: 1.5px solid rgba(148, 163, 184, 0.38) !important;
            border-bottom: 3px solid #334155 !important;
            color: #f8fafc !important;
            -webkit-text-fill-color: #f8fafc !important;
            font-size: 0.82rem !important;
            font-weight: 900 !important;
            cursor: pointer !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.38), inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
            transition: transform 0.16s ease, box-shadow 0.16s ease, filter 0.16s ease !important;
            white-space: nowrap !important;
        }
        html #new-unified-dashboard-design .dash-cmd-btn:hover,
        html.light-theme #new-unified-dashboard-design .dash-cmd-btn:hover {
            transform: translateY(-2px) !important;
            filter: brightness(1.12) !important;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.48), 0 0 14px rgba(56, 189, 248, 0.28), inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
        }
        html #new-unified-dashboard-design .dash-cmd-btn.scope-pill.active,
        html.light-theme #new-unified-dashboard-design .dash-cmd-btn.scope-pill.active {
            background: linear-gradient(180deg, #059669 0%, #064e3b 100%) !important;
            border-color: #34d399 !important;
            border-bottom: 3px solid #10b981 !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.42), inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
        }
        html #new-unified-dashboard-design .dash-cmd-amber, html.light-theme #new-unified-dashboard-design .dash-cmd-amber { background: linear-gradient(180deg, #b45309 0%, #78350f 100%) !important; border-color: #fbbf24 !important; border-bottom: 3px solid #f59e0b !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
        html #new-unified-dashboard-design .dash-cmd-emerald, html.light-theme #new-unified-dashboard-design .dash-cmd-emerald { background: linear-gradient(180deg, #047857 0%, #064e3b 100%) !important; border-color: #34d399 !important; border-bottom: 3px solid #10b981 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
        html #new-unified-dashboard-design .dash-cmd-purple, html.light-theme #new-unified-dashboard-design .dash-cmd-purple { background: linear-gradient(180deg, #6d28d9 0%, #3b0764 100%) !important; border-color: #c084fc !important; border-bottom: 3px solid #a855f7 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
        html #new-unified-dashboard-design .dash-cmd-sky, html.light-theme #new-unified-dashboard-design .dash-cmd-sky { background: linear-gradient(180deg, #0369a1 0%, #0c4a6e 100%) !important; border-color: #38bdf8 !important; border-bottom: 3px solid #0ea5e9 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
        html #new-unified-dashboard-design .dash-cmd-teal, html.light-theme #new-unified-dashboard-design .dash-cmd-teal { background: linear-gradient(180deg, #0f766e 0%, #134e4a 100%) !important; border-color: #2dd4bf !important; border-bottom: 3px solid #14b8a6 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
        html #new-unified-dashboard-design .dash-cmd-slate, html.light-theme #new-unified-dashboard-design .dash-cmd-slate { background: linear-gradient(180deg, #1e293b 0%, #090d16 100%) !important; border-color: #f59e0b !important; border-bottom: 3px solid #d97706 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
        html #new-unified-dashboard-design .dash-cmd-dark-select, html.light-theme #new-unified-dashboard-design .dash-cmd-dark-select { background: linear-gradient(180deg, #0f172a 0%, #020617 100%) !important; border-color: #10b981 !important; border-bottom: 3px solid #059669 !important; color: #ffffff !important; }
        html #new-unified-dashboard-design #hdr-item-price-switcher, html.light-theme #new-unified-dashboard-design #hdr-item-price-switcher { height: 28px !important; width: auto !important; min-width: 150px !important; padding: 0 4px !important; border: none !important; background: transparent !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; font-size: 0.8rem !important; font-weight: 900 !important; outline: none !important; box-shadow: none !important; }
        #new-unified-dashboard-design #sec-calm > .bento-card:nth-child(1) {
            background: linear-gradient(155deg, rgba(4, 47, 46, 0.96) 0%, rgba(6, 28, 28, 0.98) 100%) !important;
            border: 2.5px solid #10b981 !important;
            box-shadow: 0 16px 40px -8px rgba(0, 0, 0, 0.72), 0 0 22px rgba(16, 185, 129, 0.28), inset 0 1.5px 0 rgba(255, 255, 255, 0.25) !important;
        }
        #new-unified-dashboard-design #sec-calm > .bento-card:nth-child(2) {
            background: linear-gradient(155deg, rgba(12, 26, 54, 0.96) 0%, rgba(7, 15, 34, 0.98) 100%) !important;
            border: 2.5px solid #38bdf8 !important;
            box-shadow: 0 16px 40px -8px rgba(0, 0, 0, 0.72), 0 0 22px rgba(56, 189, 248, 0.28), inset 0 1.5px 0 rgba(255, 255, 255, 0.25) !important;
        }
        #new-unified-dashboard-design #dash-center-chart-card,
        #new-unified-dashboard-design #sec-global-ai-innovations > .bento-card:nth-child(2) {
            background: linear-gradient(155deg, #431407 0%, #2a1205 52%, #1c0a02 100%) !important;
            border: 2.5px solid #f97316 !important;
            box-shadow: 0 18px 44px rgba(0, 0, 0, 0.78), 0 0 28px rgba(249, 115, 22, 0.36), inset 0 1.5px 2px rgba(254, 215, 170, 0.35) !important;
        }
        #new-unified-dashboard-design #sec-flow > .bento-card:nth-child(2) {
            background: linear-gradient(155deg, rgba(46, 16, 38, 0.95) 0%, rgba(24, 9, 24, 0.98) 100%) !important;
            border: 2.5px solid #f43f5e !important;
            box-shadow: 0 16px 40px -8px rgba(0, 0, 0, 0.72), 0 0 22px rgba(244, 63, 94, 0.25), inset 0 1.5px 0 rgba(255, 255, 255, 0.22) !important;
        }
        #new-unified-dashboard-design #sec-ops-audit > .bento-card:nth-child(1) {
            background: linear-gradient(155deg, rgba(6, 52, 45, 0.96) 0%, rgba(4, 26, 24, 0.98) 100%) !important;
            border: 2.5px solid #10b981 !important;
            box-shadow: 0 16px 40px -8px rgba(0, 0, 0, 0.72), 0 0 22px rgba(16, 185, 129, 0.28), inset 0 1.5px 0 rgba(255, 255, 255, 0.25) !important;
        }
        #new-unified-dashboard-design #sec-ops-audit > .bento-card:nth-child(3) {
            background: linear-gradient(155deg, rgba(38, 16, 62, 0.96) 0%, rgba(19, 8, 36, 0.98) 100%) !important;
            border: 2.5px solid #a855f7 !important;
            box-shadow: 0 16px 40px -8px rgba(0, 0, 0, 0.72), 0 0 22px rgba(168, 85, 247, 0.28), inset 0 1.5px 0 rgba(255, 255, 255, 0.25) !important;
        }
        #new-unified-dashboard-design .bento-card {
            transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1), border-color 0.25s ease, box-shadow 0.25s ease, filter 0.25s ease !important;
        }
        #new-unified-dashboard-design .bento-card:hover,
        #new-unified-dashboard-design #sec-calm > .bento-card:nth-child(1):hover,
        #new-unified-dashboard-design #sec-calm > .bento-card:nth-child(2):hover,
        #new-unified-dashboard-design #sec-calm > .bento-card:nth-child(3):hover {
            transform: translateY(-5px) scale(1.012) !important;
            border-color: #fde047 !important;
            filter: brightness(1.12) saturate(1.15) !important;
            box-shadow: 0 22px 48px -6px rgba(0, 0, 0, 0.8), 0 0 38px rgba(56, 189, 248, 0.65), 0 0 18px rgba(253, 224, 71, 0.45), inset 0 0 24px rgba(56, 189, 248, 0.22) !important;
        }
        #new-unified-dashboard-design .inner-box {
            background: var(--card-inner, linear-gradient(145deg, rgba(5, 10, 21, 0.88) 0%, rgba(11, 19, 37, 0.92) 100%)) !important;
            border: 1px solid rgba(148, 163, 184, 0.18) !important;
            border-radius: 14px !important;
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.45), 0 1px 0 rgba(255, 255, 255, 0.05) !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease !important;
        }
        #new-unified-dashboard-design .inner-box:hover {
            border-color: rgba(56, 189, 248, 0.55) !important;
            box-shadow: 0 0 22px rgba(56, 189, 248, 0.28), inset 0 0 14px rgba(56, 189, 248, 0.1) !important;
        }
        #new-unified-dashboard-design #main-liquidity { text-shadow: 0 2px 18px rgba(16, 185, 129, 0.35); letter-spacing: -0.5px; }
        #new-unified-dashboard-design #main-sales { text-shadow: 0 2px 16px rgba(56, 189, 248, 0.3); }
        #new-unified-dashboard-design #main-profit { text-shadow: 0 2px 16px rgba(52, 211, 153, 0.35); }
        #new-unified-dashboard-design #main-tons { text-shadow: 0 2px 16px rgba(251, 191, 36, 0.35); }

        /* Buttons, Inputs, Selects & Audit Table inside #new-unified-dashboard-design */
        #new-unified-dashboard-design button {
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.12);
            font-family: inherit;
        }
        #new-unified-dashboard-design .tab-btn.active,
        #new-unified-dashboard-design .scope-pill.active {
            background: var(--accent, #10b981) !important;
            color: #02100c !important;
            -webkit-text-fill-color: #02100c !important;
            font-weight: 900 !important;
            border-color: transparent !important;
            box-shadow: 0 0 14px var(--accent-soft, rgba(16, 185, 129, 0.3)) !important;
        }
        #new-unified-dashboard-design input,
        #new-unified-dashboard-design select {
            width: 100% !important;
            height: 40px !important;
            padding: 0 12px !important;
            border-radius: 11px !important;
            background: var(--card-inner, rgba(4, 28, 21, 0.88)) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.16) !important;
            color: #ffffff !important;
            font-weight: 800 !important;
            font-size: 0.88rem !important;
            outline: none !important;
            box-sizing: border-box !important;
        }
        #new-unified-dashboard-design input:focus,
        #new-unified-dashboard-design select:focus {
            border-color: var(--accent, #10b981) !important;
            box-shadow: 0 0 0 2px var(--accent-soft, rgba(16, 185, 129, 0.25)) !important;
        }
        #new-unified-dashboard-design select option {
            background: #0f172a !important;
            color: #ffffff !important;
        }
        #new-unified-dashboard-design table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        #new-unified-dashboard-design table th {
            padding: 8px 6px 10px 6px !important;
            text-align: right !important;
            font-weight: 800 !important;
            border-bottom: 1.5px solid rgba(255, 255, 255, 0.15) !important;
        }
        #new-unified-dashboard-design table td {
            padding: 11px 6px !important;
            vertical-align: middle !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        /* Fixed Slide-Over Curtain Drawer & Print Modal */
        #new-unified-dashboard-design #curtain-drawer {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            bottom: 0 !important;
            height: 100vh !important;
            width: 390px !important;
            max-width: 92vw !important;
            z-index: 999999 !important;
            padding: 22px !important;
            overflow-y: auto !important;
            border-radius: 0 !important;
            border-right: 2.5px solid var(--accent, #10b981) !important;
            background: linear-gradient(160deg, var(--bg-grad, #07382b) 0%, var(--bg-main, #041f18) 100%) !important;
            box-shadow: 15px 0 45px rgba(0, 0, 0, 0.85) !important;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            transform: translateX(0) !important;
        }
        #new-unified-dashboard-design #curtain-drawer.-translate-x-full {
            transform: translateX(-105%) !important;
        }
        #new-unified-dashboard-design #print-modal {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            z-index: 999999 !important;
            background: rgba(0, 0, 0, 0.78) !important;
            backdrop-filter: blur(6px) !important;
            padding: 16px !important;
        }
        #new-unified-dashboard-design #print-modal.flex {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* 3. LIGHT SYSTEM THEMES ADAPTATION — FULL 8 LIGHT TONES (--lt-*) + ALTERNATING DIAMOND CARD TINTS & ULTRA-DARK INK */
        html.light-theme body,
        html.light-theme .dash-main {
            background: var(--lt-canvas, linear-gradient(180deg, #f3ead8 0%, #eadbc0 100%)) !important;
            color: var(--lt-ink, #120500) !important;
        }
        #new-unified-dashboard-design .bento-card {
            border: 2.5px solid #38bdf8 !important;
            box-shadow: 0 16px 40px -8px rgba(0, 0, 0, 0.72), 0 0 18px rgba(56, 189, 248, 0.28), inset 0 1px 0 rgba(255, 255, 255, 0.22) !important;
        }
        html.light-theme #new-unified-dashboard-design .top-bar,
        html.light-theme #new-unified-dashboard-design .bento-card:not(#dash-today-harvest-strip):not(#dash-center-chart-card) {
            background: linear-gradient(145deg, var(--lt-card-1, #fffdf8) 0%, var(--lt-card-2, #f5ead6) 100%) !important;
            border: 2.5px solid var(--lt-border, #5c2808) !important;
            box-shadow: 0 12px 28px rgba(45, 20, 5, 0.14), inset 0 2px 0 #ffffff !important;
            color: var(--lt-ink, #120500) !important;
        }
        html.light-theme #new-unified-dashboard-design #sec-calm > .bento-card:nth-child(1) {
            background: linear-gradient(145deg, #ecfdf5 0%, #d1fae5 65%, var(--lt-card-2, #bbf7d0) 100%) !important;
            border: 3px solid #059669 !important;
            box-shadow: 0 12px 28px rgba(5, 150, 105, 0.22), inset 0 2px 0 #ffffff !important;
        }
        html.light-theme #new-unified-dashboard-design #dash-center-chart-card,
        html.light-theme #new-unified-dashboard-design #sec-global-ai-innovations > .bento-card:nth-child(2) {
            background: linear-gradient(145deg, #fff7ed 0%, #ffedd5 55%, #fed7aa 100%) !important;
            border: 3px solid #ea580c !important;
            box-shadow: 0 14px 32px rgba(234, 88, 12, 0.26), inset 0 2px 0 #ffffff !important;
        }
        html.light-theme #new-unified-dashboard-design #sec-flow > .bento-card:nth-child(2) {
            background: linear-gradient(145deg, #fff1f2 0%, #ffe4e6 65%, var(--lt-card-2, #fecdd3) 100%) !important;
            border: 3px solid #e11d48 !important;
            box-shadow: 0 12px 28px rgba(225, 29, 72, 0.2), inset 0 2px 0 #ffffff !important;
        }
        html.light-theme #new-unified-dashboard-design #sec-ops-audit > .bento-card:nth-child(1) {
            background: linear-gradient(145deg, #f0fdf4 0%, #dcfce7 65%, var(--lt-card-2, #bbf7d0) 100%) !important;
            border: 3px solid #0d9488 !important;
            box-shadow: 0 12px 28px rgba(13, 148, 136, 0.2), inset 0 2px 0 #ffffff !important;
        }
        html.light-theme #new-unified-dashboard-design #sec-ops-audit > .bento-card:nth-child(3) {
            background: linear-gradient(145deg, #faf5ff 0%, #f3e8ff 65%, var(--lt-card-2, #e9d5ff) 100%) !important;
            border: 3px solid #7e22ce !important;
            box-shadow: 0 12px 28px rgba(126, 34, 206, 0.2), inset 0 2px 0 #ffffff !important;
        }
        html.light-theme #dash-center-chart-card #dash-center-chart-title {
            color: #9a3412 !important;
            -webkit-text-fill-color: #9a3412 !important;
        }
        html.light-theme #new-unified-dashboard-design .inner-box:not(.exec-jewel-btn) {
            background: linear-gradient(145deg, #ffffff 0%, var(--lt-sub-2, #f6ecda) 100%) !important;
            border: 2px solid var(--lt-border, #5c2808) !important;
            color: var(--lt-ink, #120500) !important;
        }
        html.light-theme #exec-sticky-command-bar,
        html.light-theme #exec-sticky-command-bar.is-pinned-fixed {
            background: linear-gradient(135deg, #78350f 0%, #b45309 28%, #d97706 50%, #b45309 72%, #78350f 100%) !important;
            border: 2.5px solid #fde047 !important;
            box-shadow: 0 14px 34px rgba(120, 53, 15, 0.45), 0 0 24px rgba(251, 191, 36, 0.42), inset 0 1.5px 3px rgba(254, 240, 138, 0.55) !important;
        }
        html.light-theme #new-unified-dashboard-design #curtain-drawer {
            background: linear-gradient(160deg, #ffffff 0%, #eff6ff 100%) !important;
            border-right: 3px solid #0369a1 !important;
            color: #000000 !important;
        }
        html.light-theme #new-unified-dashboard-design .tab-btn:not(.active) {
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            border: 2px solid #0284c7 !important;
            font-weight: 900 !important;
        }
        html.light-theme #new-unified-dashboard-design .tab-btn.active {
            background: #0369a1 !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }
        html #new-unified-dashboard-design .scope-pill:not(.active),
        html.light-theme #new-unified-dashboard-design .scope-pill:not(.active) {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%) !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            border: 1.5px solid rgba(148, 163, 184, 0.45) !important;
            border-bottom: 3px solid #334155 !important;
            font-weight: 900 !important;
        }
        html #new-unified-dashboard-design .scope-pill.active,
        html.light-theme #new-unified-dashboard-design .scope-pill.active {
            background: linear-gradient(180deg, #059669 0%, #064e3b 100%) !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            border: 1.5px solid #34d399 !important;
            border-bottom: 3px solid #10b981 !important;
            font-weight: 900 !important;
        }
        html.light-theme #new-unified-dashboard-design input,
        html.light-theme #new-unified-dashboard-design select {
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            border: 2px solid var(--lt-border, #5c2808) !important;
        }
        html.light-theme #new-unified-dashboard-design *:not(button).text-white,
        html.light-theme #new-unified-dashboard-design *:not(button).text-slate-200,
        html.light-theme #new-unified-dashboard-design *:not(button).text-slate-300,
        html.light-theme #new-unified-dashboard-design *:not(button).text-slate-400 {
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
        }
        html.light-theme #new-unified-dashboard-design .text-emerald-200:not(.exec-jewel-val),
        html.light-theme #new-unified-dashboard-design .text-emerald-300:not(.exec-jewel-val),
        html.light-theme #new-unified-dashboard-design .text-emerald-400:not(.exec-jewel-val),
        html.light-theme #new-unified-dashboard-design #main-liquidity-sub { color: #022c22 !important; -webkit-text-fill-color: #022c22 !important; font-weight: 900 !important; }
        html.light-theme #new-unified-dashboard-design .text-sky-300:not(.exec-jewel-val),
        html.light-theme #new-unified-dashboard-design .text-sky-400:not(.exec-jewel-val),
        html.light-theme #new-unified-dashboard-design #sub-bank { color: #0c4a6e !important; -webkit-text-fill-color: #0c4a6e !important; font-weight: 900 !important; }
        html.light-theme #new-unified-dashboard-design .text-amber-200:not(.exec-jewel-val),
        html.light-theme #new-unified-dashboard-design .text-amber-300:not(.exec-jewel-val),
        html.light-theme #new-unified-dashboard-design .text-amber-400:not(.exec-jewel-val),
        html.light-theme #new-unified-dashboard-design .text-yellow-300:not(.exec-jewel-val),
        html.light-theme #new-unified-dashboard-design .text-yellow-400:not(.exec-jewel-val),
        html.light-theme #new-unified-dashboard-design [style*="#fde68a"]:not(button):not(.exec-jewel-val),
        html.light-theme #new-unified-dashboard-design [style*="#fef08a"]:not(button):not(.exec-jewel-val),
        html.light-theme #new-unified-dashboard-design [style*="#fcd34d"]:not(button):not(.exec-jewel-val),
        html.light-theme #new-unified-dashboard-design [style*="#fbbf24"]:not(button):not(.exec-jewel-val),
        html.light-theme #new-unified-dashboard-design [style*="#fde047"]:not(button):not(#hdr-usd):not(.exec-jewel-val):not(#dash-center-chart-title) { color: #713f12 !important; -webkit-text-fill-color: #713f12 !important; font-weight: 900 !important; }
        html.light-theme #dash-liq-period-btn,
        html.light-theme #dash-items-group-toggle { background: #0f172a !important; color: #fde047 !important; -webkit-text-fill-color: #fde047 !important; border: 1.5px solid #5c2808 !important; }
        html.light-theme #new-unified-dashboard-design .text-rose-200:not(.exec-jewel-val),
        html.light-theme #new-unified-dashboard-design .text-rose-300:not(.exec-jewel-val),
        html.light-theme #new-unified-dashboard-design .text-rose-400:not(.exec-jewel-val) { color: #881337 !important; -webkit-text-fill-color: #881337 !important; font-weight: 900 !important; }
        html.light-theme #new-unified-dashboard-design .text-teal-300:not(.exec-jewel-val),
        html.light-theme #new-unified-dashboard-design .text-teal-400:not(.exec-jewel-val),
        html.light-theme #new-unified-dashboard-design .text-purple-300:not(.exec-jewel-val),
        html.light-theme #new-unified-dashboard-design .text-purple-400:not(.exec-jewel-val) { color: #1e1b4b !important; -webkit-text-fill-color: #1e1b4b !important; font-weight: 900 !important; }
        html.light-theme #dash-top-items-list > div small,
        html.light-theme #dash-partners-radar-list > div span.text-xs,
        html.light-theme #dash-debt-aging-box > div span.text-xs {
            color: #78350f !important;
            -webkit-text-fill-color: #78350f !important;
            font-weight: 900 !important;
        }
        html.light-theme #dash-partners-radar-list > div[style*="rgba(2,6,23"] *,
        html.light-theme #dash-debt-aging-box > div[style*="rgba(2,6,23"] * {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }
        html.light-theme #dash-partners-radar-list > div[style*="rgba(2,6,23"] span.text-xs,
        html.light-theme #dash-debt-aging-box > div[style*="rgba(2,6,23"] span.text-xs {
            color: #fde68a !important;
            -webkit-text-fill-color: #fde68a !important;
        }
        html.light-theme #partner-items-modal > div {
            background: linear-gradient(145deg, var(--lt-card-1, #fffdf8) 0%, var(--lt-card-2, #f7efe0) 100%) !important;
            border: 2.5px solid var(--lt-border, #5c2808) !important;
            color: var(--lt-ink, #120500) !important;
        }
        html.light-theme #partner-items-modal > div > div:first-child {
            background: var(--lt-sub-2, #f6ecda) !important;
            border-bottom: 1.5px solid var(--lt-border, #5c2808) !important;
        }
        html.light-theme #partner-items-modal > div *:not(button) {
            color: var(--lt-ink, #120500) !important;
            -webkit-text-fill-color: var(--lt-ink, #120500) !important;
        }
        html.light-theme #partner-items-modal > div div[style*="rgba(2,6,23"] *,
        html.light-theme #partner-items-modal > div span[style*="background:#020617"],
        html.light-theme #partner-items-modal > div span.bg-emerald-600,
        html.light-theme #partner-items-modal > div span.bg-rose-600 {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }
        html.light-theme #new-unified-dashboard-design table th,
        html.light-theme #new-unified-dashboard-design table td {
            border-color: rgba(92, 40, 8, 0.2) !important;
            background: transparent !important;
        }

        /* 4. TOP 14-BUTTON COMMAND BAR (#qa-bar-container): REFLECTS ACTIVE THEME GLOW WHILE KEEPING ROYAL DARK BUTTON CONTRAST */
        html #qa-bar-container,
        html[data-theme] #qa-bar-container {
            display: grid !important;
            grid-template-columns: repeat(7, minmax(0, 1fr)) !important;
            gap: 12px !important;
            padding: 16px 18px !important;
            margin-bottom: 14px !important;
            border-radius: 20px !important;
            background: var(--card-bg, linear-gradient(145deg, rgba(15, 23, 42, 0.92), rgba(7, 11, 22, 0.96))) !important;
            border: 2px solid var(--border-color, rgba(56, 189, 248, 0.38)) !important;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.55), 0 0 28px var(--border-color, rgba(56, 189, 248, 0.2)), inset 0 1px 0 rgba(255, 255, 255, 0.18) !important;
            transition: background 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease !important;
        }
        html.light-theme #qa-bar-container {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.72) 0%, rgba(255, 250, 240, 0.58) 100%) !important;
            backdrop-filter: blur(26px) saturate(190%) !important;
            -webkit-backdrop-filter: blur(26px) saturate(190%) !important;
            border: 2px solid rgba(255, 255, 255, 0.92) !important;
            box-shadow: 0 12px 32px rgba(56, 189, 248, 0.16), 0 6px 20px rgba(45, 20, 5, 0.1), inset 0 2px 6px rgba(255, 255, 255, 0.98) !important;
        }
        @media (max-width: 1280px) {
            html #qa-bar-container, html.light-theme #qa-bar-container { grid-template-columns: repeat(4, minmax(0, 1fr)) !important; }
        }
        @media (max-width: 768px) {
            html #qa-bar-container, html.light-theme #qa-bar-container { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
        }
        html:not(.light-theme) #qa-bar-container .qa-ribbon-btn {
            background: radial-gradient(120% 120% at 50% 10%, var(--btn-glow-color, rgba(56, 189, 248, 0.28)) 0%, rgba(15, 23, 42, 0.88) 72%) !important;
            backdrop-filter: blur(16px) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.22) !important;
            border-bottom: 3.5px solid var(--btn-border-color, #38bdf8) !important;
            color: #ffffff !important;
        }
        html.light-theme #qa-bar-container .qa-ribbon-btn {
            background: radial-gradient(130% 120% at 50% 0%, var(--btn-glow-color, rgba(56, 189, 248, 0.35)) 0%, rgba(255, 255, 255, 0.86) 68%) !important;
            backdrop-filter: blur(20px) saturate(180%) !important;
            -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.96) !important;
            border-bottom: 3.5px solid var(--btn-border-color, #0284c7) !important;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08), 0 0 18px var(--btn-glow-color, rgba(56, 189, 248, 0.24)), inset 0 2px 4px #ffffff !important;
            color: #0f172a !important;
        }
        html:not(.light-theme) #qa-bar-container .qa-ribbon-btn .btn-text {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }
        html.light-theme #qa-bar-container .qa-ribbon-btn .btn-text {
            color: #0f172a !important;
            -webkit-text-fill-color: #0f172a !important;
            font-weight: 900 !important;
        }
        html #qa-bar-container #qa-customizer-btn-label { color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
        html.light-theme #qa-bar-container #qa-customizer-btn-label { color: #0f172a !important; -webkit-text-fill-color: #0f172a !important; }
        /* Wide, Low-Height & Theme-Reactive AI Hub Menu (#ai-hub-menu) */
        html #ai-hub-menu {
            width: 680px !important;
            max-width: calc(100vw - 36px) !important;
            bottom: 78px !important;
            top: auto !important;
            max-height: calc(100vh - 96px) !important;
            overflow-y: auto !important;
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 10px !important;
            padding: 14px 16px !important;
            background: var(--card-bg, linear-gradient(145deg, rgba(8, 15, 32, 0.94), rgba(4, 9, 22, 0.97))) !important;
            border: 2px solid var(--border-color, rgba(56, 189, 248, 0.45)) !important;
        }
        html #ai-hub-menu .ai-hub-header { grid-column: 1 / -1 !important; margin-bottom: 4px !important; padding-bottom: 8px !important; }
        html #ai-hub-menu .ai-hub-item { margin-bottom: 0 !important; padding: 10px 12px !important; }
        html.light-theme #ai-hub-menu {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, var(--lt-card-2, #f7efe0) 100%) !important;
            backdrop-filter: blur(24px) saturate(180%) !important;
            border: 2px solid var(--lt-border, #5c2808) !important;
            color: var(--lt-ink, #120500) !important;
        }
        html.light-theme #ai-hub-menu .ai-hub-item {
            background: rgba(255, 255, 255, 0.85) !important;
            border: 1.5px solid var(--lt-border, #5c2808) !important;
        }
        html.light-theme #ai-hub-menu .ai-hub-text h4,
        html.light-theme #ai-hub-menu .ai-hub-text p,
        html.light-theme #ai-hub-menu .ai-hub-subtitle {
            color: var(--lt-ink, #120500) !important;
            -webkit-text-fill-color: var(--lt-ink, #120500) !important;
        }
    </style>
</body>
</html>
