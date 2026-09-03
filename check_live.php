<?php
header('Content-Type: application/json; charset=utf-8');

function getNormalizedHash($filePath) {
    if (!file_exists($filePath)) return null;
    $content = file_get_contents($filePath);
    $normalized = trim(str_replace("\r\n", "\n", $content));
    return hash('sha256', $normalized);
}

$gitCommit = trim(@shell_exec('git rev-parse HEAD 2>&1') ?: '');

$files = [
    'js/general-ledger.js' => getNormalizedHash(__DIR__ . '/js/general-ledger.js'),
    'js/mainbalances-report.js' => getNormalizedHash(__DIR__ . '/js/mainbalances-report.js'),
    'single-cash-payment.php' => getNormalizedHash(__DIR__ . '/single-cash-payment.php'),
];

// Check specific keywords inside single-cash-payment.php
$scpContent = file_exists(__DIR__ . '/single-cash-payment.php') ? file_get_contents(__DIR__ . '/single-cash-payment.php') : '';

$checks = [
    'date_font_1_85rem' => strpos($scpContent, '1.85rem !important') !== false,
    'currency_grid_1_0fr' => strpos($scpContent, 'grid-template-columns: 1.0fr') !== false,
    'currency_height_68px' => strpos($scpContent, 'height: 68px') !== false,
];

// Check general-ledger.js keywords
$glContent = file_exists(__DIR__ . '/js/general-ledger.js') ? file_get_contents(__DIR__ . '/js/general-ledger.js') : '';
$checks['gl_transfer_opening'] = strpos($glContent, "transferNo: 'رصيد افتتاحي'") !== false;

// Check mainbalances-report.js keywords
$mbContent = file_exists(__DIR__ . '/js/mainbalances-report.js') ? file_get_contents(__DIR__ . '/js/mainbalances-report.js') : '';
$checks['mb_gl_sync'] = strpos($mbContent, "calculateAccountLedgerBalance") !== false;

echo json_encode([
    'server' => (php_sapi_name() === 'cli' || ($_SERVER['SERVER_NAME'] ?? '') === 'localhost' || ($_SERVER['SERVER_NAME'] ?? '') === '127.0.0.1') ? 'LOCAL' : 'LIVE_SERVER',
    'git_commit' => $gitCommit,
    'files' => $files,
    'checks' => $checks,
    'timestamp' => date('Y-m-d H:i:s')
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
