<?php
function fixSyntax($file) {
    if (!file_exists($file)) return;
    $c = file_get_contents($file);
    // Find <?php \n\n <!DOCTYPE html> and replace with ?> \n\n <!DOCTYPE html>
    $c = preg_replace('/<\?php\s*<!DOCTYPE html>/s', "?>\n<!DOCTYPE html>", $c);
    file_put_contents($file, $c);
}

fixSyntax('single-cash-payment.php');
fixSyntax('single-cash-receipt.php');
fixSyntax('sales-invoice.php');

echo "Syntax fixed.\n";
