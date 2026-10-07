<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════
 * 🌐 MAGAIEES ERP 2026 - UNIFIED MASTER SMART ROUTER & ENTRY GATEWAY
 * ═══════════════════════════════════════════════════════════════════════════
 * الرابط الموحد المعتمد لجميع وظائف النظام والحي (https://elsajana.com/index.php):
 * 1. يبدأ من شاشة الدخول الذكية login.php (اسم المستخدم وكلمة المرور والمايك الصوتي)
 * 2. يدعم تشغيل هذا الرابط أو أي رابط آخر ممرر عبر (?url=... أو ?target=...)
 * 3. يكتشف بالذكاء الاصطناعي نوع ومقاس الشاشة بدقة متناهية:
 *    - اللابتوب: يتكيف حسب مقاس شاشة اللابتوب (13"، 14"، 15.6"، 16" ريتينا)
 *    - الكمبيوتر المكتبي: يتكيف مع شاشات 1080p، 2K، 4K والشاشات فائقة العرض (Ultrawide 21:9 / 32:9)
 *    - جميع أجهزة التابلت والآيباد: تابلت هواوي MatePad، تابلت هونر Pad، تابلت سامسونج Galaxy Tab، آبل iPad Pro / Air / Mini
 *    - جميع الجوالات والهواتف الذكية:
 *        * آيفون: آيفون برو ماكس والبلس، آيفون العادي والبرو، آيفون X/11 والميني، وآيفون SE
 *        * هواوي: سلسلة Mate المنحنية (Mate 20/30/40/50/60/70)، سلسلة P و Pura الفوتوغرافية (P20/30/40/50/60/Pura 70)
 *        * هونر: شاشات Magic الرباعية الانحناء Quad-Curved 120Hz، وسلسلة X
 *        * سامسونج: فلاجشيب Galaxy Ultra، شاشات Galaxy Fold القابلة للطي، Galaxy Flip، وسلسلة S و A
 *        * سوني: هواتف Sony Xperia ذات الشاشة السينمائية الطولية 21:9 CinemaWide
 *        * موتورولا: هواتف Moto Edge المنحنية و Moto Razr القابلة للطي
 * 4. حماية فائقة من التخزين المؤقت (Zero Cache) للتحديث الفوري لحظة بلحظة
 * ═══════════════════════════════════════════════════════════════════════════
 */

// إرسال ترويسات منع الكاش الصارم لضمان التحديث الفوري لحظياً على جميع الشبكات
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

$qs = !empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '';

// 1. التوجيه المباشر لشاشة الحركة المالية الذكية بالجوال عند طلبها صراحة
if (isset($_GET['voucher']) || (isset($_GET['screen']) && $_GET['screen'] === 'voucher') || (isset($_GET['page']) && $_GET['page'] === 'voucher')) {
    header("Location: mobile-voucher.php{$qs}");
    exit;
}

// 2. الكشف الذكي عن نوع الجهاز وشاشته من ترويسات الخادم
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$secMobile = $_SERVER['HTTP_SEC_CH_UA_MOBILE'] ?? '';
$secPlatform = strtolower($_SERVER['HTTP_SEC_CH_UA_PLATFORM'] ?? '');

// الكشف عن الكمبيوتر واللابتوب (Windows, Mac, Linux)
$isWindowsOrLinux = (bool)preg_match('/(windows nt|x11; linux x86_64|cros)/i', $userAgent) && !preg_match('/(android|mobile)/i', $userAgent);
$isMacPC = (bool)preg_match('/macintosh/i', $userAgent) && $secMobile !== '?1';
$isDesktopOrLaptop = $isWindowsOrLinux || $isMacPC;

// الكشف عن التابلت والآيباد
$isAppleTablet = (bool)preg_match('/(ipad)/i', $userAgent) || (preg_match('/macintosh/i', $userAgent) && $secMobile === '?1');
$isAndroidTablet = (bool)preg_match('/(android)/i', $userAgent) && !preg_match('/(mobile)/i', $userAgent);
$isTablet = $isAppleTablet || $isAndroidTablet || (bool)preg_match('/tablet/i', $userAgent);

// الكشف عن الهواتف الذكية (آيفون، سامسونج، هواوي، هونر، سوني، موتورولا)
$isPhone = (bool)preg_match('/(iphone|ipod|android.*mobile|mobile|blackberry|iemobile|opera mini)/i', $userAgent) || ($secMobile === '?1');

// فحص وجود رابط مخصص ممرر (Custom Target URL)
$customTarget = $_GET['url'] ?? $_GET['target'] ?? $_GET['target_url'] ?? $_GET['redirect'] ?? null;

// 3. التحقق من وجود جلسة دخول نشطة مسبقاً
$isLoggedIn = !empty($_COOKIE['session_active']) && $_COOKIE['session_active'] === 'true' && !empty($_COOKIE['saas_token']);
$forceLogin = isset($_GET['login']) || isset($_GET['logout']) || isset($_GET['reauth']);
$viewPref = $_COOKIE['magaiees_view_preference'] ?? '';

// إذا كان المستخدم مسجلاً دخوله مسبقاً ولم يطلب شاشة تسجيل الدخول صراحة:
if ($isLoggedIn && !$forceLogin) {
    if (!empty($customTarget)) {
        header("Location: " . $customTarget);
        exit;
    }

    if ($viewPref === 'desktop' || ($isDesktopOrLaptop && $viewPref !== 'mobile' && $viewPref !== 'tablet')) {
        header("Location: magaiees_unified_dashboard.php?force_desktop=1");
        exit;
    } elseif ($viewPref === 'tablet' || ($isTablet && $viewPref !== 'desktop' && $viewPref !== 'mobile')) {
        header("Location: mobile-dashboard.php?device=tablet");
        exit;
    } else {
        header("Location: mobile-dashboard.php");
        exit;
    }
}

// 4. البداية الرسمية من شاشة تسجيل الدخول المتكيفة الذكية (اسم المستخدم، كلمة المرور، المايك، وتكيف الشاشات)
$targetLogin = 'login.php' . $qs;
header("Location: {$targetLogin}");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="refresh" content="0;url=<?php echo htmlspecialchars($targetLogin); ?>">
    <script src="js/master-device-engine.js?v=2026_master_v1"></script>
    <script>
        (function() {
            var target = <?php echo json_encode($targetLogin); ?>;
            window.location.replace(target);
        })();
    </script>
    <title>مقاييس ERP 2026 - بوابة التكيف الذكي</title>
</head>
<body style="background:#0c101b;color:#38bdf8;font-family:sans-serif;display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;margin:0;text-align:center;padding:20px;">
    <div style="font-size:2.5rem;margin-bottom:15px;">🌐</div>
    <h2 style="margin:0 0 10px 0;font-size:1.3rem;color:#f8fafc;">جاري التحويل الذكي لبوابة النظام الموحدة...</h2>
    <p style="color:#94a3b8;font-size:0.9rem;margin:0 0 15px 0;">يتم الآن التعرف على عتاد جهازك ومقاس الشاشة تلقائياً</p>
    <a href="<?php echo htmlspecialchars($targetLogin); ?>" style="color:#38bdf8;font-weight:bold;text-decoration:none;padding:10px 20px;background:rgba(56,189,248,0.15);border:1px solid #38bdf8;border-radius:12px;">اضغط هنا للمتابعة الفورية</a>
</body>
</html>
