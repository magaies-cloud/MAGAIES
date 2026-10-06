<?php
/**
 * ═════════════════════════════════════════════════════════════════════
 * 🌐 MAGAIEES ERP 2026 - UNIFIED MASTER SMART ROUTER & ENTRY GATEWAY
 * ═════════════════════════════════════════════════════════════════════
 * الرابط الموحد المعتمد لجميع وظائف النظام (https://elsajana.com/index.php):
 * 1. يبدأ من شاشة الدخول الذكية login.php
 * 2. يتكيف أوتوماتيكياً وبالذكاء الاصطناعي مع نوع ومقاس الشاشة:
 *    - اللابتوب / الكمبيوتر: يفتح لوحة التحكم الشاملة للكمبيوتر (magaiees_unified_dashboard.php)
 *    - الهواتف الذكية (سامسونج، هواوي، هونر، آيفون، أندرويد): يفتح الواجهة التنفيذية المتكيفة (mobile-dashboard.php)
 *    - الأجهزة اللوحية (الآيباد والتابلت): يتكيف مع المقاس اللوحي الذكي
 * 3. حماية فائقة من التخزين المؤقت (Zero Cache) للتحديث الفوري لحظة بلحظة
 */

// إرسال ترويسات منع الكاش الصارم لضمان التحديث الفوري لحظياً
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

$qs = !empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '';

// 1. التوجيه المباشر لشاشة الحركة المالية الذكية بالجوال عند طلبها
if (isset($_GET['voucher']) || (isset($_GET['screen']) && $_GET['screen'] === 'voucher') || (isset($_GET['page']) && $_GET['page'] === 'voucher')) {
    header("Location: mobile-voucher.php{$qs}");
    exit;
}

// 2. الكشف الذكي عن نوع الجهاز وشاشته
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$secMobile = $_SERVER['HTTP_SEC_CH_UA_MOBILE'] ?? '';
$isWindowsOrLinux = (bool)preg_match('/(windows nt|x11; linux x86_64|cros)/i', $userAgent);
$isExplicitTablet = (bool)preg_match('/(ipad|tablet)/i', $userAgent) || (preg_match('/macintosh/i', $userAgent) && $secMobile === '?1');
$isPureMobile = (bool)preg_match('/(android.*mobile|iphone|ipod|blackberry|iemobile|opera mini)/i', $userAgent) || ($secMobile === '?1');

// 3. التحقق من وجود جلسة دخول نشطة مسبقاً
$isLoggedIn = !empty($_COOKIE['session_active']) && $_COOKIE['session_active'] === 'true' && !empty($_COOKIE['saas_token']);
$forceLogin = isset($_GET['login']) || isset($_GET['logout']);

if ($isLoggedIn && !$forceLogin) {
    if ($isWindowsOrLinux) {
        header("Location: magaiees_unified_dashboard.php?force_desktop=1");
        exit;
    } elseif ($isExplicitTablet) {
        header("Location: mobile-dashboard.php?device=tablet");
        exit;
    } elseif ($isPureMobile) {
        header("Location: mobile-dashboard.php");
        exit;
    } else {
        header("Location: dashboard.php{$qs}");
        exit;
    }
}

// 4. البداية الرسمية من شاشة تسجيل الدخول المتكيفة الذكية
header("Location: login.php{$qs}");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="refresh" content="0;url=login.php<?php echo htmlspecialchars($qs); ?>">
    <script>window.location.replace('login.php' + window.location.search);</script>
    <title>مقاييس ERP 2026 - جاري التحويل</title>
</head>
<body style="background:#0c101b;color:#38bdf8;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;">
    <p>جاري التوجيه الذكي لشاشة الدخول... <a href="login.php<?php echo htmlspecialchars($qs); ?>" style="color:#38bdf8;font-weight:bold;">اضغط هنا للمتابعة</a></p>
</body>
</html>
