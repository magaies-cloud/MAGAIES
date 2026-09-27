<?php
/**
 * ═════════════════════════════════════════════════════════════════════
 * 🌐 MAGAIEES ERP 2026 - UNIVERSAL SMART ROUTER (الرابط الموحد الذكي)
 * ═════════════════════════════════════════════════════════════════════
 * رابط موحد عام لمنظومة مقاييس:
 * - التحقق من الجلسة: إذا لم يسجل الدخول يحوله فوراً لصفحة الدخول
 * - إذا فُتح من جهاز كمبيوتر/لابتوب -> يفتح شاشات سطح المكتب مباشرة
 * - إذا فُتح من هاتف محمول/آيفون/أندرويد/آيباد -> يفتح شاشات الهاتف السريعة
 * - يدعم رغبة المستخدم إذا اختار التبديل يدوياً من الداخل
 */

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isMobile = (bool)preg_match('/(android|iphone|ipad|ipod|mobile|blackberry|iemobile|opera mini)/i', $userAgent);
$pref = $_COOKIE['magaiees_view_preference'] ?? '';
$forceDesktop = isset($_GET['force_desktop']) && $_GET['force_desktop'] === '1';
$forceMobile = isset($_GET['force_mobile']) && $_GET['force_mobile'] === '1';

// Check Authentication cookies on non-localhost
$token = $_COOKIE['saas_token'] ?? $_COOKIE['authToken'] ?? '';
$sessionActive = $_COOKIE['session_active'] ?? '';
$isLocalhost = (php_sapi_name() === 'cli' || ($_SERVER['SERVER_NAME'] ?? '') === 'localhost' || ($_SERVER['SERVER_NAME'] ?? '') === '127.0.0.1');

if (!$isLocalhost && empty($token) && empty($sessionActive)) {
    $target = ($isMobile && $pref !== 'desktop') ? 'mobile-dashboard.php' : 'magaiees_unified_dashboard.php';
    header('Location: login.php?redirect=' . urlencode($target));
    exit;
}

// Respect explicit URL overrides
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

// Respect saved user preference
if ($pref === 'desktop') {
    header('Location: magaiees_unified_dashboard.php');
    exit;
}

if ($pref === 'mobile') {
    header('Location: mobile-dashboard.php');
    exit;
}

// Automatic Device Detection
if ($isMobile) {
    header('Location: mobile-dashboard.php');
} else {
    header('Location: magaiees_unified_dashboard.php');
}
exit;
