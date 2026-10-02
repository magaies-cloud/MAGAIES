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
    'js/chatbot.js' => getNormalizedHash(__DIR__ . '/js/chatbot.js'),
    'includes/header.php' => getNormalizedHash(__DIR__ . '/includes/header.php'),
    'js/magaiees_back.js' => getNormalizedHash(__DIR__ . '/js/magaiees_back.js'),
    'js/global_background_media.js' => getNormalizedHash(__DIR__ . '/js/global_background_media.js'),
    'background_player.php' => getNormalizedHash(__DIR__ . '/background_player.php'),
    'direct-sales.php' => getNormalizedHash(__DIR__ . '/direct-sales.php'),
    'js/direct-sales-v3.js' => getNormalizedHash(__DIR__ . '/js/direct-sales-v3.js'),
    'purchase.php' => getNormalizedHash(__DIR__ . '/purchase.php'),
    'js/purchase-pro-v5.js' => getNormalizedHash(__DIR__ . '/js/purchase-pro-v5.js'),
    'inventory-report.php' => getNormalizedHash(__DIR__ . '/inventory-report.php'),
    'js/multi-cash-payment.js' => getNormalizedHash(__DIR__ . '/js/multi-cash-payment.js'),
    'multi-cash-payment.php' => getNormalizedHash(__DIR__ . '/multi-cash-payment.php'),
    'settings.php' => getNormalizedHash(__DIR__ . '/settings.php'),
    'sw.js' => getNormalizedHash(__DIR__ . '/sw.js'),
    'live_sw.js' => getNormalizedHash(__DIR__ . '/live_sw.js'),
    'api/ocr-invoice.php' => getNormalizedHash(__DIR__ . '/api/ocr-invoice.php'),
    'api/ocr-financial.php' => getNormalizedHash(__DIR__ . '/api/ocr-financial.php'),
    'api/ai-assistant.php' => getNormalizedHash(__DIR__ . '/api/ai-assistant.php'),
    'js/financial-ocr.js' => getNormalizedHash(__DIR__ . '/js/financial-ocr.js'),
    'includes/financial-ocr-modal.php' => getNormalizedHash(__DIR__ . '/includes/financial-ocr-modal.php'),
    'js/promissory-flight-animation.js' => getNormalizedHash(__DIR__ . '/js/promissory-flight-animation.js'),
    'js/auto-header-scroll.js' => getNormalizedHash(__DIR__ . '/js/auto-header-scroll.js'),
    'js/master-banks-helper.js' => getNormalizedHash(__DIR__ . '/js/master-banks-helper.js'),
    'master-data.php' => getNormalizedHash(__DIR__ . '/master-data.php'),
    'js/erp-whatsapp-suite.js' => getNormalizedHash(__DIR__ . '/js/erp-whatsapp-suite.js'),
    'preview-ledger.php' => getNormalizedHash(__DIR__ . '/preview-ledger.php'),
    'dashboard.php' => getNormalizedHash(__DIR__ . '/dashboard.php'),
    'magaiees_unified_dashboard.php' => getNormalizedHash(__DIR__ . '/magaiees_unified_dashboard.php'),
    'mobile-dashboard.php' => getNormalizedHash(__DIR__ . '/mobile-dashboard.php'),
    'manifest.json' => getNormalizedHash(__DIR__ . '/manifest.json'),
    'api/ai-reconciliation.php' => getNormalizedHash(__DIR__ . '/api/ai-reconciliation.php'),
    'api/get_master_data.php' => getNormalizedHash(__DIR__ . '/api/get_master_data.php'),
    'api/sync_queue_api.php' => getNormalizedHash(__DIR__ . '/api/sync_queue_api.php'),
    'js/accounting_reconciliation.js' => getNormalizedHash(__DIR__ . '/js/accounting_reconciliation.js'),
    'js/pwa-manager.js' => getNormalizedHash(__DIR__ . '/js/pwa-manager.js'),
    'mobile_camera_sync.php' => getNormalizedHash(__DIR__ . '/mobile_camera_sync.php'),
    'package.json' => getNormalizedHash(__DIR__ . '/package.json'),
    'build_production_pwa.js' => getNormalizedHash(__DIR__ . '/build_production_pwa.js'),
    'index.html' => getNormalizedHash(__DIR__ . '/index.html'),
    'royal-glass.css' => getNormalizedHash(__DIR__ . '/royal-glass.css'),
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
$checks['fiqh_zero_duplicates'] = (strpos($fiqhContent, '0 Duplicates') !== false || strpos($fiqhContent, 'Zero Suffixes') !== false || strpos($fiqhContent, 'صفر تكرار') !== false);
$checks['fiqh_4500_count'] = (strpos($fiqhContent, '4,500 UNIQUE') !== false || strpos($fiqhContent, '4,500') !== false);
$checks['fiqh_lazy_loaded'] = strpos(file_get_contents(__DIR__ . '/engineering_tools.php'), 'ensureFiqhLoaded') !== false;
$checks['chatbot_fiqh_integrated'] = strpos(file_get_contents(__DIR__ . '/js/chatbot.js'), 'handleFiqhAndTools') !== false;
$checks['tools_chatbot_bridge'] = strpos(file_get_contents(__DIR__ . '/engineering_tools.php'), 'window.openToolSuite') !== false;
$checks['back_btn_in_header'] = strpos(file_get_contents(__DIR__ . '/includes/header.php'), 'magaiees-master-back-btn') !== false;
$checks['magaiees_back_script_exists'] = file_exists(__DIR__ . '/js/magaiees_back.js');
$checks['media_engine_mutex'] = strpos(file_get_contents(__DIR__ . '/js/global_background_media.js'), 'WINDOW_INSTANCE_ID') !== false;
$checks['media_engine_v5'] = strpos(file_get_contents(__DIR__ . '/js/global_background_media.js'), 'v2026.5') !== false;
$checks['popout_mutex_support'] = strpos(file_get_contents(__DIR__ . '/background_player.php'), 'POPOUT_PLAYER') !== false;
$checks['direct_sales_header'] = strpos(file_get_contents(__DIR__ . '/direct-sales.php'), 'includes/header.php') !== false;
$checks['inventory_report_header'] = strpos(file_get_contents(__DIR__ . '/inventory-report.php'), 'includes/header.php') !== false;
$checks['direct_sales_overpayment_modal'] = strpos(file_get_contents(__DIR__ . '/js/direct-sales-v3.js'), 'showOverpaymentConfirmModal') !== false;
$checks['purchase_overpayment_modal'] = strpos(file_get_contents(__DIR__ . '/js/purchase-pro-v5.js'), 'showOverpaymentConfirmModal') !== false;
$checks['purchase_exchange_rate_keep'] = strpos(file_get_contents(__DIR__ . '/purchase.php'), 'prevCurrency') !== false;
$checks['virtual_cfo_engine'] = strpos(file_get_contents(__DIR__ . '/js/chatbot.js'), 'analyzeCashFlowRunway') !== false;
$checks['ocr_invoice_scanner'] = (file_exists(__DIR__ . '/api/ocr-invoice.php') && strpos(file_get_contents(__DIR__ . '/purchase.php'), 'triggerInvoiceOCR') !== false);


echo json_encode([
    'server' => (php_sapi_name() === 'cli' || ($_SERVER['SERVER_NAME'] ?? '') === 'localhost' || ($_SERVER['SERVER_NAME'] ?? '') === '127.0.0.1') ? 'LOCAL' : 'LIVE_SERVER',
    'git_commit' => $gitCommit,
    'git_tree' => $gitTree,
    'git_clean' => empty($gitStatus),
    'git_diff_clean' => empty($gitDiff),
    'git_status_porcelain' => $gitStatus,
    'tracked_files_count' => $trackedCount,
    'untracked_or_ignored' => array_values(array_filter(explode("\n", trim((string)@shell_exec('git status --ignored --porcelain 2>&1'))))),
    'recent_modified_files' => array_values(array_filter(explode("\n", trim((string)@shell_exec('find . -maxdepth 2 -type f -mtime -3 2>&1'))))),
    'files' => $files,
    'checks' => $checks,
    'timestamp' => date('Y-m-d H:i:s')
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
