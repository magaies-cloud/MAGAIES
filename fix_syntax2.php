<?php
function fixSyntax($file) {
    if (!file_exists($file)) return;
    $c = file_get_contents($file);
    $c = str_replace("</script>\n<?php\n\n<!DOCTYPE html>", "</script>\n?>\n<!DOCTYPE html>", $c);
    $c = str_replace("</script>\r\n<?php\r\n\r\n<!DOCTYPE html>", "</script>\r\n?>\r\n<!DOCTYPE html>", $c);
    $c = preg_replace('/<\?php\s*<!DOCTYPE html>/', "?>\n<!DOCTYPE html>", $c);
    file_put_contents($file, $c);
}

fixSyntax('single-cash-payment.php');
fixSyntax('single-cash-receipt.php');
fixSyntax('sales-invoice.php');

echo "Syntax fixed.\n";
