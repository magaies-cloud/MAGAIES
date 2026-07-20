<?php
function fixBraces($file) {
    if (!file_exists($file)) return;
    $c = file_get_contents($file);
    $c = str_replace("function resetVoucher() {\r\n    window.location.href = window.location.pathname;\r\n}\r\n}", "function resetVoucher() {\r\n    window.location.href = window.location.pathname;\r\n}", $c);
    $c = str_replace("function resetVoucher() {\n    window.location.href = window.location.pathname;\n}\n}", "function resetVoucher() {\n    window.location.href = window.location.pathname;\n}", $c);
    file_put_contents($file, $c);
}

fixBraces('js/single-cash-payment.js');
fixBraces('js/single-cash-receipt.js');
fixBraces('js/sales-invoice.js');

echo "Syntax error fixed.\n";
