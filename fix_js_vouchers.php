<?php
function removeJSVoucherGeneration($file) {
    if (!file_exists($file)) return;
    $c = file_get_contents($file);
    // Comment out the initial call
    $c = preg_replace('/(?<!\/\/\s)setNextVoucherNumber\(\);/s', '// setNextVoucherNumber();', $c);
    // In resetVoucher, make it reload the page
    $c = preg_replace('/function resetVoucher\(\) \{.*?\}/s', "function resetVoucher() {\n    window.location.reload();\n}", $c);
    file_put_contents($file, $c);
}

removeJSVoucherGeneration('js/single-cash-payment.js');
removeJSVoucherGeneration('js/single-cash-receipt.js');
removeJSVoucherGeneration('js/sales-invoice.js');

echo "JS updated.\n";
