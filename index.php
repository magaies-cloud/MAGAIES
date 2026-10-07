<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════
 * 🌐 MAGAIEES ERP 2026 - UNIFIED MASTER SMART ROUTER & ENTRY GATEWAY
 * ═══════════════════════════════════════════════════════════════════════════
 * الرابط الموحد المعتمد لجميع وظائف النظام والحي (https://elsajana.com/index.php):
 * 1. يبدأ فوراً من شاشة الدخول الذكية المتكيفة (اسم المستخدم، كلمة المرور، المايك الصوتي)
 * 2. يدعم تشغيل هذا الرابط أو أي رابط آخر ممرر عبر (?url=... أو ?target=...)
 * 3. يتكيف بنسبة 100% مع كافة أجهزة العالم ومقاسات الشاشات:
 *    - جميع اللابتوبات: 12"، 13"، 14"، 15.6"، 16"، 17" بمختلف الدقات (1366x768, 1440x900, 1920x1080, 2560x1600 ريتينا)
 *    - جميع الديسكتوب: 1080p، 2K، 4K، 8K، وشاشات Ultrawide فائقة العرض (21:9 و 32:9)
 *    - جميع التابلت والآيباد: iPad Mini/Air/Pro 11" و 12.9"/13"، هواوي MatePad، هونر Pad، سامسونج Galaxy Tab و Ultra 14.6"
 *    - جميع أجهزة الآيفون: Classic/SE، X، 11، 12، 13، 14، 15، 16، 17، 18، 20، 28 والمستقبل (برو ماكس، بلس، عادي، ميني)
 *    - جميع أجهزة هواوي: سلسلة Mate المنحنية (Mate 20, 30, 40, 50, 60, 70, 80)، وسلسلة P و Pura (P20, 30, 40, 50, 60, 70, Pura 70 Ultra)
 *    - جميع أجهزة هونر: سلسلة Magic الشلالية (Magic 3, 4, 5, 6, 7, V, Vs, V2, V3)، وسلسلة الأرقام (Honor 20 إلى 800) وسلسلة X
 *    - جميع أجهزة سامسونج: سلسلة Galaxy Ultra (S20 Ultra إلى S27 Ultra)، وسلسلة S، وسلسلة Z Fold القابلة للطي و Flip
 *    - جميع أجهزة سوني: شاشات Sony Xperia السينمائية 21:9 CinemaWide
 *    - جميع أجهزة موتورولا: شاشات Moto Edge المنحنية و Moto Razr القابلة للطي و Moto G
 * 4. حماية فائقة من الكاش (Zero Cache) لضمان العمل الفوري
 */

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

$qs = !empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '';

// 1. التوجيه المباشر لشاشة الحركة المالية بالجوال عند طلبها صراحة
if (isset($_GET['voucher']) || (isset($_GET['screen']) && $_GET['screen'] === 'voucher') || (isset($_GET['page']) && $_GET['page'] === 'voucher')) {
    header("Location: mobile-voucher.php{$qs}");
    exit;
}

// 2. فحص وجود رابط مخصص أو طلب صريح للوحة التحكم مباشرة (?direct=1 أو ?dashboard=1)
$customTarget = $_GET['url'] ?? $_GET['target'] ?? $_GET['target_url'] ?? $_GET['redirect'] ?? null;
if (!empty($customTarget)) {
    header("Location: " . $customTarget);
    exit;
}

$directRequested = isset($_GET['direct']) || isset($_GET['dashboard']) || isset($_GET['app']);
$token = $_COOKIE['saas_token'] ?? ($_COOKIE['authToken'] ?? null);
$hasSessionActive = !empty($_COOKIE['session_active']) && $_COOKIE['session_active'] === 'true';
$isLoggedIn = !empty($token) || $hasSessionActive;
$forceLogin = isset($_GET['login']) || isset($_GET['logout']) || isset($_GET['reauth']);
$viewPref = $_COOKIE['magaiees_view_preference'] ?? '';

// يتم التحويل التلقائي للوحة التحكم فقط عند طلب الدخول المباشر صراحة (?direct=1):
if ($directRequested && $isLoggedIn && !$forceLogin) {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $secMobile = $_SERVER['HTTP_SEC_CH_UA_MOBILE'] ?? '';
    $isWindowsOrLinux = (bool)preg_match('/(windows nt|x11; linux x86_64|cros)/i', $userAgent) && !preg_match('/(android|mobile)/i', $userAgent);
    $isMacPC = (bool)preg_match('/macintosh/i', $userAgent) && $secMobile !== '?1';
    $isDesktopOrLaptop = $isWindowsOrLinux || $isMacPC;
    $isAppleTablet = (bool)preg_match('/(ipad)/i', $userAgent) || (preg_match('/macintosh/i', $userAgent) && $secMobile === '?1');
    $isAndroidTablet = (bool)preg_match('/(android)/i', $userAgent) && !preg_match('/(mobile)/i', $userAgent);
    $isTablet = $isAppleTablet || $isAndroidTablet || (bool)preg_match('/tablet/i', $userAgent);

    $target = 'mobile-dashboard.php';
    if ($viewPref === 'desktop' || ($isDesktopOrLaptop && $viewPref !== 'mobile' && $viewPref !== 'tablet')) {
        $target = 'magaiees_unified_dashboard.php?force_desktop=1';
    } elseif ($viewPref === 'tablet' || ($isTablet && $viewPref !== 'desktop' && $viewPref !== 'mobile')) {
        $target = 'mobile-dashboard.php?device=tablet';
    }

    $tenantSub = $_COOKIE['currentTenantSubdomain'] ?? ($_COOKIE['tenant_subdomain'] ?? '');
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (!empty($tenantSub) && strpos($host, 'elsajana.com') !== false && strpos($host, $tenantSub . '.elsajana.com') === false) {
        header("Location: https://{$tenantSub}.elsajana.com/{$target}");
        exit;
    }

    header("Location: " . $target);
    exit;
}

// 3. المستخدم غير مسجل، أو طلب الدخول صراحة:
// تشغيل شاشة الدخول الذكية المتكيفة مباشرة في نفس الرابط index.php بدون أي تحويل
require __DIR__ . '/login.php';
