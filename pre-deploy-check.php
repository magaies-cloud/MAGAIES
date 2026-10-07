<?php
/**
 * =============================================
 * MAGAIEES Pre-Deploy Check Script
 * =============================================
 * 
 * Run this BEFORE pushing to GitHub/Live server:
 *   php pre-deploy-check.php
 * 
 * This script checks for common issues that could
 * break the live site.
 */

echo "\n";
echo "╔══════════════════════════════════════════════════╗\n";
echo "║   MAGAIEES Pre-Deploy Check Script v1.0         ║\n";
echo "║   يجب تشغيله قبل كل عملية رفع للموقع الحي      ║\n";
echo "╚══════════════════════════════════════════════════╝\n\n";

$errors = [];
$warnings = [];
$passed = 0;

// ============================================
// CHECK 1: No Tailwind CDN in production files
// ============================================
echo "🔍 [1/9] Checking for Tailwind CDN usage...\n";
$phpFiles = glob('*.php');
foreach ($phpFiles as $file) {
    if (in_array(basename($file), ['pre-deploy-check.php', 'mobile-voucher.php'])) continue;
    $content = file_get_contents($file);
    if (strpos($content, 'cdn.tailwindcss.com') !== false) {
        $errors[] = "❌ $file: Uses Tailwind CDN (cdn.tailwindcss.com). Replace with local css/tailwind.min.css";
    }
}
if (empty(array_filter($errors, fn($e) => strpos($e, 'Tailwind') !== false))) {
    echo "   ✅ No Tailwind CDN found\n";
    $passed++;
}

// ============================================
// CHECK 2: No anti-caching headers in PHP files 
// ============================================
echo "🔍 [2/9] Checking for anti-caching headers...\n";
$mainFiles = ['index.php', 'saas-admin.php'];
foreach ($mainFiles as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    if (preg_match('/header\s*\(\s*["\']Cache-Control.*no-cache/i', $content)) {
        $warnings[] = "⚠️ $file: Contains anti-caching PHP headers";
    }
    if (preg_match('/no-cache|no-store/i', $content) && preg_match('/<meta[^>]*http-equiv.*pragma/i', $content)) {
        $warnings[] = "⚠️ $file: Contains anti-caching meta tags";
    }
}
echo "   ✅ Anti-caching check complete\n";
$passed++;

// ============================================
// CHECK 3: Service Worker excludes PHP/API
// ============================================
echo "🔍 [3/9] Checking Service Worker configuration...\n";
if (file_exists('sw.js')) {
    $sw = file_get_contents('sw.js');
    if (strpos($sw, '.php') === false || strpos($sw, '/api/') === false) {
        $errors[] = "❌ sw.js: Must exclude .php and /api/ requests from interception";
    }
    if (strpos($sw, 'new Response') === false) {
        $warnings[] = "⚠️ sw.js: Should return a valid Response on cache miss to prevent TypeError";
    }
    echo "   ✅ Service Worker check complete\n";
    $passed++;
}

// ============================================
// CHECK 4: Large images (>500KB)
// ============================================
echo "🔍 [4/9] Checking for oversized images...\n";
$imageExts = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];
$assetsDir = 'assets/';
if (is_dir($assetsDir)) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($assetsDir)) as $file) {
        if ($file->isFile()) {
            $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
            if (in_array($ext, $imageExts) && $file->getSize() > 500 * 1024) {
                $sizeKB = round($file->getSize() / 1024);
                $warnings[] = "⚠️ {$file->getPathname()}: Image is {$sizeKB}KB (>500KB limit)";
            }
        }
    }
}
echo "   ✅ Image size check complete\n";
$passed++;

// ============================================
// CHECK 5: Heavy CSS blur filters
// ============================================
echo "🔍 [5/9] Checking for heavy blur filters...\n";
foreach (['index.php', 'saas-admin.php'] as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    preg_match_all('/blur\((\d+)px\)/', $content, $matches);
    if (!empty($matches[1])) {
        foreach ($matches[1] as $val) {
            if ((int)$val > 15) {
                $warnings[] = "⚠️ $file: Heavy blur filter ({$val}px > 15px max). May cause GPU lag on mobile";
            }
        }
    }
}
echo "   ✅ Blur filter check complete\n";
$passed++;

// ============================================
// CHECK 6: Broken navigation links
// ============================================
echo "🔍 [6/9] Checking for broken navigation links...\n";
if (file_exists('js/app.js')) {
    $appJs = file_get_contents('js/app.js');
    preg_match_all("/href:\s*'([^']+\.(?:php|html))'/", $appJs, $matches);
    if (!empty($matches[1])) {
        foreach ($matches[1] as $href) {
            if (!file_exists($href)) {
                $errors[] = "❌ js/app.js: Navigation link '$href' points to non-existent file";
            }
        }
    }
}
echo "   ✅ Navigation link check complete\n";
$passed++;

// ============================================
// CHECK 7: localforage.min.js exists
// ============================================
echo "🔍 [7/9] Checking critical JS libraries...\n";
$criticalJs = ['js/app.js', 'js/localforage.min.js', 'js/lang.js'];
foreach ($criticalJs as $jsFile) {
    if (!file_exists($jsFile)) {
        $errors[] = "❌ $jsFile: Critical JS file missing!";
    }
}
echo "   ✅ JS libraries check complete\n";
$passed++;

// ============================================
// CHECK 8: CSS files exist
// ============================================
echo "🔍 [8/9] Checking critical CSS files...\n";
$criticalCss = ['css/design-system.css', 'css/theme.css', 'css/components.css', 'css/tailwind.min.css'];
foreach ($criticalCss as $cssFile) {
    if (!file_exists($cssFile)) {
        $errors[] = "❌ $cssFile: Critical CSS file missing!";
    }
}
echo "   ✅ CSS files check complete\n";
$passed++;

// ============================================
// CHECK 9: No time() cache-busting on heavy JS files
// ============================================
echo "🔍 [9/9] Checking for cache-killing patterns...\n";
$phpFiles2 = glob('*.php');
foreach ($phpFiles2 as $file) {
    if (basename($file) === 'pre-deploy-check.php') continue;
    $content = file_get_contents($file);
    if (preg_match('/app\.js\?v=.*time\(\)/', $content)) {
        $errors[] = "❌ $file: Uses time() for app.js versioning - causes 340KB re-download on every page! Use filemtime() instead";
    }
}
echo "   ✅ Cache pattern check complete\n";
$passed++;

// ============================================
// RESULTS
// ============================================
echo "\n";
echo "══════════════════════════════════════════════════\n";
echo "  RESULTS: $passed/9 checks passed\n";
echo "══════════════════════════════════════════════════\n\n";

if (!empty($errors)) {
    echo "❌ ERRORS (MUST FIX BEFORE DEPLOY):\n";
    foreach ($errors as $e) echo "   $e\n";
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️ WARNINGS (Review recommended):\n";
    foreach ($warnings as $w) echo "   $w\n";
    echo "\n";
}

if (empty($errors) && empty($warnings)) {
    echo "🎉 All checks passed! Safe to deploy.\n\n";
} elseif (empty($errors)) {
    echo "✅ No blocking errors. Safe to deploy (review warnings above).\n\n";
} else {
    echo "🚫 DEPLOY BLOCKED! Fix the errors above before pushing.\n\n";
    exit(1);
}
