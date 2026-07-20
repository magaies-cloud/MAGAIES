<?php
function removeJSVoucherGenerationFix($file) {
    if (!file_exists($file)) return;
    $c = file_get_contents($file);
    $c = preg_replace('/window\.location\.reload\(\);/s', 'window.location.href = window.location.pathname;', $c);
    file_put_contents($file, $c);
}

removeJSVoucherGenerationFix('js/single-cash-payment.js');
removeJSVoucherGenerationFix('js/single-cash-receipt.js');
removeJSVoucherGenerationFix('js/sales-invoice.js');

echo "JS updated again.\n";
