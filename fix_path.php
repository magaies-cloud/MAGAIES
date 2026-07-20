<?php
function fixRequirePath($file) {
    if (!file_exists($file)) return;
    $c = file_get_contents($file);
    $c = str_replace("require_once 'api/db_bridge.php';", "require_once 'db_bridge.php';", $c);
    file_put_contents($file, $c);
}
fixRequirePath('single-cash-payment.php');
fixRequirePath('single-cash-receipt.php');
fixRequirePath('sales-invoice.php');
echo "Fixed require paths.\n";
