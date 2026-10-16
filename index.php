<?php
/**
 * ═════════════════════════════════════════════════════════════════════
 * 🌐 MAGAIEES ERP 2026 - UNIFIED MASTER SMART ROUTER & ENTRY GATEWAY
 * ═════════════════════════════════════════════════════════════════════
 * الرابط الموحد العام للموبايل واللابتوب:
 * 1. يتعرف تلقائياً وفورياً على نوع الجهاز:
 *    - الموبايل / الجوال (أندرويد، آيفون، سامسونج، هواوي، هونر، شاومي...): يفتح شاشات الموبايل (mobile-dashboard.php)
 *    - اللابتوب / الكمبيوتر المكتبي (Windows, Mac, Linux): يفتح شاشات اللابتوب (magaiees_unified_dashboard.php)
 *    - الآيباد / التابلت: يفتح الواجهة اللوحية المتكيفة (mobile-dashboard.php?device=tablet)
 * 2. يدعم الاستئناف الفوري للجلسة النشطة والمحلية بدون أي تعليق أو حلقات تحويل
 * 3. حماية فائقة من التخزين المؤقت القديم (Zero Stale Cache)
 */

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

$qs = !empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '';

// 0. في حال طلب تسجيل الخروج الصريح، المسح والتوجيه المباشر لصفحة الدخول
if (isset($_GET['logout']) || isset($_GET['login']) || isset($_GET['force_login'])) {
    if (isset($_GET['logout'])) {
        @setcookie('session_active', '', time() - 3600, '/');
        @setcookie('saas_token', '', time() - 3600, '/');
        @setcookie('mg_device_auth', '', time() - 3600, '/');
    }
    header("Location: login.php{$qs}");
    exit;
}

// 1. التوجيه المباشر لشاشة الحركة المالية الذكية بالجوال عند طلبها
if (isset($_GET['voucher']) || (isset($_GET['screen']) && $_GET['screen'] === 'voucher') || (isset($_GET['page']) && $_GET['page'] === 'voucher')) {
    header("Location: mobile-voucher.php{$qs}");
    exit;
}

// 2. الكشف الذكي الشامل عن نوع الجهاز (موبايل / تابلت / لابتوب)
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$secMobile = $_SERVER['HTTP_SEC_CH_UA_MOBILE'] ?? '';

$forceDesktop = isset($_GET['force_desktop']) && $_GET['force_desktop'] === '1';
$forceMobile  = isset($_GET['force_mobile']) && $_GET['force_mobile'] === '1';

$isExplicitTablet = (bool)preg_match('/(ipad|tablet|playbook|silk)|(android(?!.*mobile))/i', $userAgent)
    || (preg_match('/macintosh/i', $userAgent) && $secMobile === '?1');

$isMobilePhone = !$isExplicitTablet && (
    (bool)preg_match('/(android|iphone|ipod|mobile|blackberry|iemobile|opera mini|samsung|huawei|honor|harmonyos|xiaomi|redmi|poco|oppo|vivo|oneplus|pixel|realme|infinix|tecno)/i', $userAgent)
    || ($secMobile === '?1')
);

$isLaptopOrPC = !$isMobilePhone && !$isExplicitTablet && (
    (bool)preg_match('/(windows nt|macintosh|x11; linux x86_64|cros)/i', $userAgent)
);

// 3. فحص حالة المصادقة أو الشبكة المحلية (Localhost / LAN)
$hasAuthCookie = (!empty($_COOKIE['saas_token']) || !empty($_COOKIE['authToken']) || !empty($_COOKIE['mg_device_auth']) || (!empty($_COOKIE['session_active']) && $_COOKIE['session_active'] === 'true'));

$isLocalRequest = false;
$remoteIp = $_SERVER['REMOTE_ADDR'] ?? '';
$hasProxyHeaders = false;
foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_FORWARDED', 'HTTP_CF_RAY'] as $h) {
    if (!empty($_SERVER[$h])) {
        $hasProxyHeaders = true;
        break;
    }
}
if (!$hasProxyHeaders && (in_array($remoteIp, ['127.0.0.1', '::1'], true) || preg_match('/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.)/', $remoteIp))) {
    $isLocalRequest = true;
}

$isAuthenticated = $hasAuthCookie || $isLocalRequest;
if (!$isAuthenticated) {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    if (!empty($_SESSION['user']['id'])) {
        $isAuthenticated = true;
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
}

// 4. التوجيه الفوري حسب نوع الجهاز (للمستخدم المسجل أو المحلي)
if ($isAuthenticated) {
    if ($forceMobile) {
        @setcookie('magaiees_view_preference', 'mobile', time() + (86400 * 365), '/');
        header("Location: mobile-dashboard.php{$qs}");
        exit;
    }
    if ($forceDesktop) {
        @setcookie('magaiees_view_preference', 'desktop', time() + (86400 * 365), '/');
        header("Location: magaiees_unified_dashboard.php{$qs}");
        exit;
    }
    if ($isExplicitTablet) {
        @setcookie('magaiees_view_preference', 'tablet', time() + (86400 * 365), '/');
        $tabletQs = $qs ? ($qs . '&device=tablet') : '?device=tablet';
        header("Location: mobile-dashboard.php{$tabletQs}");
        exit;
    }
    if ($isMobilePhone) {
        @setcookie('magaiees_view_preference', 'mobile', time() + (86400 * 365), '/');
        header("Location: mobile-dashboard.php{$qs}");
        exit;
    }
    if ($isLaptopOrPC) {
        @setcookie('magaiees_view_preference', 'desktop', time() + (86400 * 365), '/');
        header("Location: magaiees_unified_dashboard.php{$qs}");
        exit;
    }
    header("Location: dashboard.php{$qs}");
    exit;
}

// 5. إذا لم تتوفر كوكيز على السيرفر، التوجيه السريع لشاشة الدخول الذكية (والتي تستأنف الجلسة تلقائياً إن وجدت في المتصفح)
header("Location: login.php{$qs}");
exit;
