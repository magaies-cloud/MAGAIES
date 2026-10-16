<?php
/**
 * ═════════════════════════════════════════════════════════════════════
 * 📱 MAGAIEES ERP 2026 - ULTRA-FAST 2G POCKET EXECUTIVE GATEWAY
 * ═════════════════════════════════════════════════════════════════════
 * بوابة الجوال السريعة والخفيفة (تدعم ضغط Gzip الفوري وإغلاق الجلسة لمنع التعليق)
 */

// 1. Instant zero-overhead heartbeat probe response (before DB or session)
if ((isset($_GET['action']) && $_GET['action'] === 'heartbeat_ping') || (isset($_POST['action']) && $_POST['action'] === 'heartbeat_ping')) {
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('X-Magaiees-Heartbeat: 1');
    echo '1';
    exit;
}

// 2. Desktop explicit redirect if requested
if (isset($_GET['force_desktop']) && $_GET['force_desktop'] === '1') {
    header('Location: magaiees_unified_dashboard.php?force_desktop=1');
    exit;
}

// 3. Database & Mobile Auth Guard (Resilient Offline-Capable)
if (!defined('ALLOW_OFFLINE_DB_FALLBACK')) {
    define('ALLOW_OFFLINE_DB_FALLBACK', true);
}
if (file_exists(__DIR__ . '/db_bridge.php')) {
    require_once __DIR__ . '/db_bridge.php';
} elseif (file_exists(__DIR__ . '/ELSAJANA2/magaies/db_bridge.php')) {
    require_once __DIR__ . '/ELSAJANA2/magaies/db_bridge.php';
}

if (file_exists(__DIR__ . '/mobile_auth_guard.php')) {
    require_once __DIR__ . '/mobile_auth_guard.php';
} elseif (file_exists(__DIR__ . '/ELSAJANA2/magaies/mobile_auth_guard.php')) {
    require_once __DIR__ . '/ELSAJANA2/magaies/mobile_auth_guard.php';
}

$pdo = function_exists('getDBConnection') ? getDBConnection() : null;
$authUser = function_exists('requireMobileAuth') ? requireMobileAuth($pdo, false) : [];

// Release session lock immediately so parallel AJAX calls never block on 2G
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

// 4. Serve the Mobile Suite with Gzip compression & ETag support for 2G speed
$suiteFile = __DIR__ . '/mobile-suite.html';
if (!file_exists($suiteFile)) {
    $suiteFile = __DIR__ . '/mobile_erp_global_suite.html';
}

if (file_exists($suiteFile)) {
    $mtime = filemtime($suiteFile);
    $etag = '"' . md5($mtime . '-' . filesize($suiteFile)) . '"';

    header('Content-Type: text/html; charset=UTF-8');
    header('ETag: ' . $etag);
    header('Cache-Control: private, max-age=0, must-revalidate');

    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
        http_response_code(304);
        exit;
    }

    if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
        ob_start('ob_gzhandler');
    }

    readfile($suiteFile);
    exit;
}

http_response_code(404);
echo 'Mobile suite file not found.';
