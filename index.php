<?php
/**
 * ═════════════════════════════════════════════════════════════════════
 * 🚪 MAGAIEES ERP 2026 - SECURE AUTHENTICATION GATEWAY & SMART ROUTER
 * ═════════════════════════════════════════════════════════════════════
 * التوجيه الذكي المباشر:
 * - يدعم الدخول المباشر لشاشة الحركة المالية عبر ?voucher=1 أو ?screen=voucher
 * - يحول تلقائياً لشاشة تسجيل الدخول login.php لباقي الزيارات
 * - يمنع الكاش تماماً لضمان وصول أحدث التعديلات في الحين
 */

// منع التخزين المؤقت تماماً لضمان التحديث الفوري
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

$qs = !empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '';

// 1. التوجيه المباشر لشاشة الحركة المالية الذكية بالجوال
if (isset($_GET['voucher']) || (isset($_GET['screen']) && $_GET['screen'] === 'voucher') || (isset($_GET['page']) && $_GET['page'] === 'voucher')) {
    header("Location: mobile-voucher.php{$qs}");
    exit;
}

// 2. التوجيه المباشر لنسخة الـ HTML المستقلة
if (isset($_GET['html']) || (isset($_GET['v']) && $_GET['v'] === 'html')) {
    header("Location: mobile-voucher.html{$qs}");
    exit;
}

// 3. التوجيه الافتراضي لشاشة الدخول
header("Location: login.php{$qs}");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="refresh" content="0;url=login.php<?php echo htmlspecialchars($qs); ?>">
    <script>window.location.replace('login.php' + window.location.search);</script>
    <title>مقاييس 2026</title>
</head>
<body style="background:#0c101b;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;">
    <p>جاري التوجيه المباشر... <a href="login.php<?php echo htmlspecialchars($qs); ?>" style="color:#38bdf8;">اضغط هنا للمتابعة</a></p>
</body>
</html>
