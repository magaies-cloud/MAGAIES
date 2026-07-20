<?php
function updateJS($file, $old, $new) {
    if (!file_exists($file)) return;
    $c = file_get_contents($file);
    $c = str_replace($old, $new, $c);
    file_put_contents($file, $c);
}

updateJS('js/single-cash-payment.js', 
    "JSON.parse(localStorage.getItem('erp_single_payments_v1')) || []", 
    "(window.SERVER_SINGLE_PAYMENTS && window.SERVER_SINGLE_PAYMENTS.length > 0 ? window.SERVER_SINGLE_PAYMENTS : (JSON.parse(localStorage.getItem('erp_single_payments_v1')) || []))");

updateJS('js/single-cash-receipt.js', 
    "JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || []", 
    "(window.SERVER_SINGLE_RECEIPTS && window.SERVER_SINGLE_RECEIPTS.length > 0 ? window.SERVER_SINGLE_RECEIPTS : (JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || []))");

updateJS('js/sales-invoice.js', 
    "JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []", 
    "(window.SERVER_SALES_INVOICES && window.SERVER_SALES_INVOICES.length > 0 ? window.SERVER_SALES_INVOICES : (JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []))");

echo "JS files updated to use SERVER vars.\n";
