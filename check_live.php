<?php
header('Content-Type: application/json; charset=utf-8');

function getNormalizedHash($filePath) {
    if (!file_exists($filePath)) return null;
    $content = file_get_contents($filePath);
    $normalized = trim(str_replace("\r\n", "\n", $content));
    return hash('sha256', $normalized);
}

$gitCommit = trim(@shell_exec('git rev-parse HEAD 2>&1') ?: '');
$gitTree = trim(@shell_exec('git rev-parse "HEAD^{tree}" 2>&1') ?: '');
$gitStatus = trim(@shell_exec('git status --porcelain 2>&1') ?: '');
$gitDiff = trim(@shell_exec('git diff 2>&1') ?: '');
$trackedFilesOutput = trim(@shell_exec('git ls-files 2>&1') ?: '');
$trackedCount = !empty($trackedFilesOutput) ? count(array_filter(preg_split('/\r\n|\r|\n/', $trackedFilesOutput))) : 0;

$files = [
    'single-cash-payment.php' => getNormalizedHash(__DIR__ . '/single-cash-payment.php'),
    'js/single-cash-payment.js' => getNormalizedHash(__DIR__ . '/js/single-cash-payment.js'),
    'general-ledger.php' => getNormalizedHash(__DIR__ . '/general-ledger.php'),
    'js/general-ledger.js' => getNormalizedHash(__DIR__ . '/js/general-ledger.js'),
    'mainbalances-report.php' => getNormalizedHash(__DIR__ . '/mainbalances-report.php'),
    'js/mainbalances-report.js' => getNormalizedHash(__DIR__ . '/js/mainbalances-report.js'),
    'api_index.php' => getNormalizedHash(__DIR__ . '/api_index.php'),
    'js/api.js' => getNormalizedHash(__DIR__ . '/js/api.js'),
    'db_bridge.php' => getNormalizedHash(__DIR__ . '/db_bridge.php'),
    'login.php' => getNormalizedHash(__DIR__ . '/login.php'),
    'index.php' => getNormalizedHash(__DIR__ . '/index.php'),
    'engineering_tools.php' => getNormalizedHash(__DIR__ . '/engineering_tools.php'),
    'js/fiqh_20000_data.js' => getNormalizedHash(__DIR__ . '/js/fiqh_20000_data.js'),
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

// Check fiqh encyclopedia keywords
$fiqhContent = file_exists(__DIR__ . '/js/fiqh_20000_data.js') ? file_get_contents(__DIR__ . '/js/fiqh_20000_data.js') : '';
$checks['fiqh_hadith_evidence'] = strpos($fiqhContent, 'evidence') !== false;
$checks['fiqh_zero_duplicates'] = (strpos($fiqhContent, '0 Duplicates') !== false || strpos($fiqhContent, 'صفر تكرار') !== false);
$checks['fiqh_4500_count'] = (strpos($fiqhContent, '4,500 UNIQUE') !== false || strpos($fiqhContent, '4,500') !== false);
$checks['fiqh_lazy_loaded'] = strpos(file_get_contents(__DIR__ . '/engineering_tools.php'), 'ensureFiqhLoaded') !== false;

echo json_encode([
    'server' => (php_sapi_name() === 'cli' || ($_SERVER['SERVER_NAME'] ?? '') === 'localhost' || ($_SERVER['SERVER_NAME'] ?? '') === '127.0.0.1') ? 'LOCAL' : 'LIVE_SERVER',
    'git_commit' => $gitCommit,
    'git_tree' => $gitTree,
    'git_clean' => empty($gitStatus),
    'git_diff_clean' => empty($gitDiff),
    'git_status_porcelain' => $gitStatus,
    'tracked_files_count' => $trackedCount,
    'files' => $files,
    'checks' => $checks,
    'timestamp' => date('Y-m-d H:i:s')
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
