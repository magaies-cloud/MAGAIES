<?php
/**
 * ═════════════════════════════════════════════════════════════════════
 * 🌐 MAGAIEES ERP 2026 - UNIVERSAL SMART ROUTER (الرابط الموحد الذكي)
 * ═════════════════════════════════════════════════════════════════════
 * رابط موحد عام لمنظومة مقاييس:
 * - أي جهاز موبايل (آيفون، سامسونج، هواوي، هونر، أندرويد، شاومي) يتم التعرف
 *   عليه فوراً وبنسبة 100% ويفتح شاشات الموبايل المخصصة والمعايرة
 * - أجهزة الكمبيوتر وسطح المكتب تفتح لوحة التحكم الكاملة مباشرة
 * - يمكن للمستخدم في أي وقت التبديل لشاشة الكمبيوتر من داخل الموبايل عبر ?force_desktop=1
 */

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$secChUaMobile = $_SERVER['HTTP_SEC_CH_UA_MOBILE'] ?? '';

$forceDesktop = isset($_GET['force_desktop']) && $_GET['force_desktop'] === '1';
$forceMobile = isset($_GET['force_mobile']) && $_GET['force_mobile'] === '1';

// 1. Explicit user URL override takes precedence
if ($forceDesktop) {
    setcookie('magaiees_view_preference', 'desktop', time() + (86400 * 365), '/');
    header('Location: magaiees_unified_dashboard.php?force_desktop=1');
    exit;
}

if ($forceMobile) {
    setcookie('magaiees_view_preference', 'mobile', time() + (86400 * 365), '/');
    header('Location: mobile-dashboard.php?force_mobile=1');
    exit;
}

// 2. Comprehensive Mobile & Tablet Hardware Detection:
// Detects iPhone, iPad, Android, Samsung, Huawei, Honor, HarmonyOS, Xiaomi, Oppo, Vivo, etc.
$isMobile = (bool)preg_match(
    '/(android|iphone|ipad|ipod|mobile|blackberry|iemobile|opera mini|opera mobi|samsung|sm-[a-z0-9]+|huawei|honor|harmonyos|xiaomi|redmi|poco|oppo|vivo|oneplus|pixel|meizu|realme)/i',
    $userAgent
) || ($secChUaMobile === '?1');

// Any mobile device visiting the universal link ALWAYS defaults to mobile screens!
if ($isMobile) {
    header('Location: mobile-dashboard.php');
    exit;
}

// 3. For Desktop browsers: if user explicitly saved preference to view mobile
$pref = $_COOKIE['magaiees_view_preference'] ?? '';
if ($pref === 'mobile') {
    header('Location: mobile-dashboard.php');
    exit;
}

// 4. For edge-case devices (e.g. iPadOS Safari requesting desktop site or touch laptops):
// Output ultra-fast 0ms client-side hardware detector and redirect
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>مقاييس - جاري توجيهك للنظام...</title>
    <script>
    (function() {
        var ua = navigator.userAgent || '';
        var isTouchDevice = (navigator.maxTouchPoints && navigator.maxTouchPoints > 0);
        var isSmallScreen = (window.innerWidth <= 1024 || window.screen.width <= 1024);
        var isMobileUA = /Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Samsung|Huawei|Honor|HarmonyOS|Xiaomi|POCO|Redmi|OPPO|vivo|OnePlus|Pixel/i.test(ua);

        var params = new URLSearchParams(window.location.search);
        if (params.get('force_desktop') === '1') {
            window.location.replace('magaiees_unified_dashboard.php?force_desktop=1');
        } else if (isMobileUA || (isTouchDevice && isSmallScreen) || params.get('force_mobile') === '1') {
            window.location.replace('mobile-dashboard.php');
        } else {
            window.location.replace('magaiees_unified_dashboard.php');
        }
    })();
    </script>
    <noscript>
        <meta http-equiv="refresh" content="0;url=magaiees_unified_dashboard.php">
    </noscript>
    <style>
        body { background: #030712; color: #38bdf8; font-family: 'Tajawal', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .spinner { width: 38px; height: 38px; border: 3px solid rgba(56,189,248,0.2); border-top-color: #38bdf8; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 12px; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div style="text-align:center;">
        <div class="spinner"></div>
        <div style="font-size:0.95rem; font-weight:700;">جاري فتح لوحة التحكم المناسبة لجهازك...</div>
    </div>
</body>
</html>
