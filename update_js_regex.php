<?php
function updateJSRegex($file, $tableVar, $localStorageKey) {
    if (!file_exists($file)) return;
    $c = file_get_contents($file);
    // Find patterns like JSON.parse(localStorage.getItem(...)) || []
    $c = preg_replace('/JSON\.parse\(localStorage\.getItem\([^)]+?\)\s*\|\|\s*localStorage\.getItem\([^)]+?\)\)\s*\|\|\s*\[\]/', "(window.$tableVar && window.${tableVar}.length > 0 ? window.$tableVar : (JSON.parse(localStorage.getItem('$localStorageKey')) || []))", $c);
    $c = preg_replace('/JSON\.parse\(localStorage\.getItem\([^)]+?\)\)\s*\|\|\s*\[\]/', "(window.$tableVar && window.${tableVar}.length > 0 ? window.$tableVar : (JSON.parse(localStorage.getItem('$localStorageKey')) || []))", $c);
    file_put_contents($file, $c);
}

updateJSRegex('js/single-cash-payment.js', 'SERVER_SINGLE_PAYMENTS', 'erp_single_payments_v1');
updateJSRegex('js/single-cash-receipt.js', 'SERVER_SINGLE_RECEIPTS', 'erp_single_receipts_v1');
updateJSRegex('js/sales-invoice.js', 'SERVER_SALES_INVOICES', 'erp_sales_invoices_v1');

echo "Regex JS updated.\n";
