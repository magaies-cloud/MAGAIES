<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════
 * 🌐 MAGAIEES ERP 2026 - UNIFIED MASTER SMART ROUTER & ENTRY GATEWAY
 * ═══════════════════════════════════════════════════════════════════════════
 * الرابط الموحد المعتمد لنظام مقاييس ERP (https://elsajana.com/index.php):
 * 1. ذكاء تلقائي فوري 100%:
 *    - عند فتح الرابط على اللابتوب أو الديسك توب: تفتح فوراً شاشات الكمبيوتر واللابتوب المتكاملة (لا تفتح شاشة الجوال نهائياً).
 *    - عند فتح الرابط على الجوال: تفتح فوراً شاشات الجوال المتكيفة مع الهاتف.
 * 2. التوجيه الذكي للحركات المالية والسندات:
 *    - على اللابتوب: تفتح شاشة السندات والحسابات المتكاملة (vouchers-hub.php).
 *    - على الجوال: تفتح شاشة السند المالي السريع للهاتف (mobile-voucher.php).
 * 3. حماية تامة من الكاش لضمان التوجيه الفوري.
 */

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

require_once __DIR__ . '/device_detector.php';

$qs = !empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '';
$isLaptop = isDeviceDesktopOrLaptop();

// 1. التوجيه الذكي لشاشات الحركة المالية والسندات بحسب الجهاز
if (isset($_GET['voucher']) || (isset($_GET['screen']) && $_GET['screen'] === 'voucher') || (isset($_GET['page']) && $_GET['page'] === 'voucher')) {
    if ($isLaptop) {
        setcookie('magaiees_view_preference', 'desktop', time() + (86400 * 365), '/');
        header("Location: vouchers-hub.php{$qs}");
        exit;
    } else {
        setcookie('magaiees_view_preference', 'mobile', time() + (86400 * 365), '/');
        header("Location: mobile-voucher.php{$qs}");
        exit;
    }
}

// 2. فحص وجود رابط مخصص ممرر بالرابط
$customTarget = $_GET['url'] ?? $_GET['target'] ?? $_GET['target_url'] ?? $_GET['redirect'] ?? null;
if (!empty($customTarget)) {
    header("Location: " . $customTarget);
    exit;
}

// 3. فحص حالة المصادقة والدخول
$token = $_COOKIE['saas_token'] ?? ($_COOKIE['authToken'] ?? null);
$hasSessionActive = !empty($_COOKIE['session_active']) && $_COOKIE['session_active'] === 'true';
$isLoggedIn = !empty($token) || $hasSessionActive;
$forceLogin = isset($_GET['login']) || isset($_GET['logout']) || isset($_GET['reauth']);

// 4. إذا كان المستخدم مسجلاً بالفعل، يتم توجيهه فوراً وذكياً إلى لوحة التحكم المناسبة لجهازه
if ($isLoggedIn && !$forceLogin) {
    if ($isLaptop) {
        setcookie('magaiees_view_preference', 'desktop', time() + (86400 * 365), '/');
        $target = 'magaiees_unified_dashboard.php?force_desktop=1';
    } else {
        setcookie('magaiees_view_preference', 'mobile', time() + (86400 * 365), '/');
        $target = 'mobile-dashboard.php';
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

// 5. إذا لم يكن مسجلاً، فتح شاشة الدخول المتكيفة مع حفظ نوع الجهاز
if ($isLaptop) {
    setcookie('magaiees_view_preference', 'desktop', time() + (86400 * 365), '/');
} else {
    setcookie('magaiees_view_preference', 'mobile', time() + (86400 * 365), '/');
}
require __DIR__ . '/login.php';
