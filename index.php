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
        @setcookie('authToken', '', time() - 3600, '/');
        @setcookie('mg_device_auth', '', time() - 3600, '/');
        @setcookie('currentTenantId', '', time() - 3600, '/');
        @setcookie('currentSubdomain', '', time() - 3600, '/');
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

// 3. فحص حالة المصادقة الفعلية وعنوان الشركة (Subdomain / Tenant)
$rawTokenCookie = trim((string)($_COOKIE['saas_token'] ?? ($_COOKIE['authToken'] ?? ($_COOKIE['mg_device_auth'] ?? ''))));
$isRealToken = ($rawTokenCookie !== '' && strpos($rawTokenCookie, 'offline_token_') !== 0 && $rawTokenCookie !== 'local-offline-token');

// استخراج عنوان الشركة المطلوب من الرابط أو النطاق الفرعي
$reqSubdomain = strtolower(trim((string)($_GET['tenant'] ?? $_GET['subdomain'] ?? '')));
if ($reqSubdomain === '') {
    $host = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));
    if ($host !== '' && !preg_match('/^[0-9.]+$/', $host) && $host !== 'localhost') {
        $parts = explode('.', $host);
        if (substr($host, -10) === '.localhost' && count($parts) >= 2) {
            $reqSubdomain = $parts[0];
        } elseif (count($parts) > 2 && $parts[0] !== 'www') {
            $reqSubdomain = $parts[0];
        }
    }
}

$cookieSubdomain = strtolower(trim((string)($_COOKIE['currentSubdomain'] ?? '')));
$hasTenantSwitch = (!empty($_GET['tenant']) || !empty($_GET['subdomain'])) || ($reqSubdomain !== '' && $cookieSubdomain !== '' && $reqSubdomain !== $cookieSubdomain);

$isAuthenticated = $isRealToken && !$hasTenantSwitch;
if (!$isAuthenticated && !$hasTenantSwitch) {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    if (!empty($_SESSION['user']['id'])) {
        $sessSub = strtolower(trim((string)($_SESSION['user']['subdomain'] ?? '')));
        if ($reqSubdomain === '' || $sessSub === '' || $reqSubdomain === $sessSub) {
            $isAuthenticated = true;
        }
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
