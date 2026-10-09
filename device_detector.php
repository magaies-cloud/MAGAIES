<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════
 * 🌐 MAGAIEES ERP 2026 - INTELLIGENT DEVICE DETECTOR & ROUTER ENGINE
 * ═══════════════════════════════════════════════════════════════════════════
 * المحرك المركزي الذكي للتعرف الصارم على الأجهزة وتوجيه الروابط:
 * 1. اللابتوب والديسك توب: تفتح شاشات الكمبيوتر فوراً ودون أي ظهور لشاشات الجوال.
 * 2. الهواتف الذكية (الجوال): تفتح شاشات الجوال المتكيفة مع الهاتف.
 * 3. يدعم اللابتوبات اللمسية (Touchscreen Laptops) والشاشات بجميع مقاساتها.
 */

if (!function_exists('isDeviceDesktopOrLaptop')) {
    function isDeviceDesktopOrLaptop() {
        if (isset($_GET['force_desktop']) && $_GET['force_desktop'] === '1') return true;
        if (isset($_GET['force_mobile']) && $_GET['force_mobile'] === '1') return false;

        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $secMobile = $_SERVER['HTTP_SEC_CH_UA_MOBILE'] ?? '';

        // 1. فحص هيدر Client Hints المعتمد من المتصفحات الحديثة (?1 للجوال الصريح)
        if ($secMobile === '?1') return false;

        // 2. بصمات الهواتف الذكية والأجهزة اللوحية الصريحة (تأخذ الأولوية القصوى لمنع أي طرد)
        if (preg_match('/(android.*mobile|iphone|ipod|blackberry|iemobile|opera mini|windows phone|mobile)/i', $ua)) {
            return false;
        }

        // 3. أنظمة تشغيل الكمبيوتر واللابتوب الصريحة (Windows, Mac, Linux Desktop)
        // تأخذ الأولوية لمنع حبس شاشات اللابتوب في إطار الجوال 400px بسبب كوكيز قديم
        if (preg_match('/windows nt/i', $ua)) {
            return true;
        }
        if (preg_match('/macintosh/i', $ua) && !preg_match('/(ipad)/i', $ua)) {
            return true;
        }
        if (preg_match('/(x11; linux x86_64|cros)/i', $ua) && !preg_match('/android/i', $ua)) {
            return true;
        }

        if ($secMobile === '?0') return true;

        // 4. أولوية اختيار المستخدم الصريح للأجهزة المشتركة/غير المحددة
        $pref = $_COOKIE['magaiees_view_preference'] ?? '';
        if ($pref === 'mobile') return false;
        if ($pref === 'desktop') return true;

        return false;
    }
}
