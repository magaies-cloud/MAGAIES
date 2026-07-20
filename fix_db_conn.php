<?php
function fixDBConnection($file) {
    if (!file_exists($file)) return;
    $c = file_get_contents($file);
    $c = preg_replace('/require_once \'db_bridge\.php\';\s*global \$pdo, \$GLOBAL_TENANT_ID;/', "require_once 'db_bridge.php';\n\$pdo = getDBConnection();\nglobal \$GLOBAL_TENANT_ID;", $c);
    file_put_contents($file, $c);
}
fixDBConnection('single-cash-payment.php');
fixDBConnection('single-cash-receipt.php');
fixDBConnection('sales-invoice.php');
echo "Fixed DB connection.\n";
