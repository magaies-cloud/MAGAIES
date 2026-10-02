<?php
/**
 * ═════════════════════════════════════════════════════════════════════
 * 🌐 ELSAJANA ERP 2026 - UNIFIED PWA SMART ROUTER (الرابط الموحد الذكي)
 * ═════════════════════════════════════════════════════════════════════
 * Single Canonical Web Link & PWA Gateway:
 * - Desktop/Laptop (Screen width >= 1024px): Renders Full-Scale ERP Layout
 * - Mobile/Tablet (Screen width < 1024px): Serves Ultra-Fast Pocket Dashboard
 * - PWA Ready: Manifest v3, Standalone Mode, Offline Service Worker
 * - Manual Overrides: ?force_desktop=1 | ?force_mobile=1
 */

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$secChUaMobile = $_SERVER['HTTP_SEC_CH_UA_MOBILE'] ?? '';

$forceDesktop = isset($_GET['force_desktop']) && $_GET['force_desktop'] === '1';
$forceMobile = isset($_GET['force_mobile']) && $_GET['force_mobile'] === '1';

// Preserve all incoming query parameters (such as PWA source, action, filters)
$queryString = $_SERVER['QUERY_STRING'] ?? '';
$qPrefix = $queryString ? '?' . $queryString : '';

// 1. Explicit user URL override takes precedence
if ($forceDesktop) {
    setcookie('magaiees_view_preference', 'desktop', time() + (86400 * 365), '/');
    header("Location: magaiees_unified_dashboard.php{$qPrefix}");
    exit;
}

if ($forceMobile) {
    setcookie('magaiees_view_preference', 'mobile', time() + (86400 * 365), '/');
    header("Location: mobile-dashboard.php{$qPrefix}");
    exit;
}

// 2. Server-Side Mobile Hardware & Client Hints Detection
$isMobileUserAgent = (bool)preg_match(
    '/(android|iphone|ipad|ipod|mobile|blackberry|iemobile|opera mini|opera mobi|samsung|sm-[a-z0-9]+|huawei|honor|harmonyos|xiaomi|redmi|poco|oppo|vivo|oneplus|pixel|meizu|realme)/i',
    $userAgent
) || ($secChUaMobile === '?1');

$savedPref = $_COOKIE['magaiees_view_preference'] ?? '';

// If mobile device visits without desktop preference, route to mobile dashboard
if ($isMobileUserAgent && $savedPref !== 'desktop') {
    header("Location: mobile-dashboard.php{$qPrefix}");
    exit;
}

if (!$isMobileUserAgent && $savedPref === 'mobile') {
    header("Location: mobile-dashboard.php{$qPrefix}");
    exit;
}

// 3. Client-Side 0ms Dynamic Viewport Detection (Handles Hybrid Laptops, iPads, Resized Windows)
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    
    <!-- PWA & Standalone Native Configuration -->
    <link rel="manifest" href="manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="مقاييس ERP">
    <link rel="apple-touch-icon" href="assets/icon-192.png">
    <link rel="icon" type="image/svg+xml" href="assets/icon.svg">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/icon-192.png">
    <meta name="theme-color" content="#0F172A">

    <title>Magaiees ERP - مقاييس | جاري التحويل للواجهة المثالية...</title>
    
    <script>
    (function() {
        var width = window.innerWidth || document.documentElement.clientWidth || screen.width;
        var ua = navigator.userAgent || '';
        var isMobileUA = /Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Samsung|Huawei|Honor|HarmonyOS|Xiaomi|POCO|Redmi|OPPO|vivo|OnePlus|Pixel/i.test(ua);
        var isTouch = (navigator.maxTouchPoints && navigator.maxTouchPoints > 0);
        
        var params = new URLSearchParams(window.location.search);
        var qs = window.location.search;

        if (params.get('force_desktop') === '1') {
            window.location.replace('magaiees_unified_dashboard.php' + qs);
        } else if (params.get('force_mobile') === '1') {
            window.location.replace('mobile-dashboard.php' + qs);
        } else if (width < 1024 || (isTouch && isMobileUA)) {
            window.location.replace('mobile-dashboard.php' + qs);
        } else {
            window.location.replace('magaiees_unified_dashboard.php' + qs);
        }
    })();
    </script>
    
    <noscript>
        <meta http-equiv="refresh" content="0;url=magaiees_unified_dashboard.php">
    </noscript>

    <style>
        body {
            background: #000000;
            color: #38bdf8;
            font-family: system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }
        .gateway-card {
            text-align: center;
            padding: 30px 24px;
            background: #0f172a;
            border: 1.5px solid rgba(56, 189, 248, 0.3);
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.8);
            max-width: 320px;
        }
        .gateway-logo {
            width: 56px;
            height: 56px;
            margin: 0 auto 16px;
            border-radius: 14px;
            background: linear-gradient(135deg, #0ea5e9, #10b981);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #fff;
            box-shadow: 0 0 25px rgba(14, 165, 233, 0.4);
        }
        .spinner {
            width: 32px;
            height: 32px;
            border: 3px solid rgba(56, 189, 248, 0.2);
            border-top-color: #38bdf8;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 14px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="gateway-card">
        <div class="gateway-logo">⚡</div>
        <div class="spinner"></div>
        <strong style="display:block; font-size:1rem; color:#fff; margin-bottom:6px;">السجانة ERP الذكي</strong>
        <span style="font-size:0.8rem; color:rgba(255,255,255,0.7);">جاري فحص دقة الشاشة وتوجيهك فوراً...</span>
    </div>
</body>
</html>
