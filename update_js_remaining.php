<?php
function fixRemainingJS($file, $tableVar, $localStorageKey) {
    if (!file_exists($file)) return;
    $c = file_get_contents($file);
    $oldStr = "JSON.parse(localStorage.getItem(getTenantKey('$localStorageKey')) || localStorage.getItem('$localStorageKey')) || []";
    $newStr = "(window.$tableVar && window.${tableVar}.length > 0 ? window.$tableVar : (JSON.parse(localStorage.getItem(getTenantKey('$localStorageKey')) || localStorage.getItem('$localStorageKey')) || []))";
    $c = str_replace($oldStr, $newStr, $c);
    file_put_contents($file, $c);
}

fixRemainingJS('js/single-cash-payment.js', 'SERVER_SINGLE_PAYMENTS', 'erp_single_payments_v1');
fixRemainingJS('js/single-cash-receipt.js', 'SERVER_SINGLE_RECEIPTS', 'erp_single_receipts_v1');
fixRemainingJS('js/sales-invoice.js', 'SERVER_SALES_INVOICES', 'erp_sales_invoices_v1');

echo "Remaining JS updated.\n";
