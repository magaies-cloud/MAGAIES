<?php
/**
 * ═════════════════════════════════════════════════════════════════════
 * 🚪 MAGAIEES ERP 2026 - SECURE AUTHENTICATION GATEWAY
 * ═════════════════════════════════════════════════════════════════════
 * Unconditionally routes all visitors directly to login.php.
 * Strictly requires Username and Password FIRST before entering the system.
 */

// Strictly prevent caching of gateway redirect across all browsers/proxies
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

$qs = !empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '';

// Unconditional immediate redirect to login.php
header("Location: login.php{$qs}");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0;url=login.php<?php echo htmlspecialchars($qs); ?>">
    <script>window.location.replace('login.php' + window.location.search);</script>
    <title>تسجيل الدخول - مقاييس</title>
</head>
<body style="background:#0c101b;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;">
    <p>جاري الانتقال لشاشة تسجيل الدخول... <a href="login.php<?php echo htmlspecialchars($qs); ?>" style="color:#38bdf8;">اضغط هنا إذا لم يتم التحويل تلقائياً</a></p>
</body>
</html>
