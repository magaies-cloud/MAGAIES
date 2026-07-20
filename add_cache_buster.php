<?php
function addCacheBuster($file) {
    if (!file_exists($file)) return;
    $c = file_get_contents($file);
    $c = preg_replace('/(src="js\/[a-zA-Z0-9_-]+\.js)"/', '$1?v=<?php echo time(); ?>"', $c);
    file_put_contents($file, $c);
}

addCacheBuster('single-cash-payment.php');
addCacheBuster('single-cash-receipt.php');
addCacheBuster('sales-invoice.php');

echo "Cache buster added.\n";
