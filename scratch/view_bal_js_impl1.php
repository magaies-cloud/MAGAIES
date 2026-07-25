<?php
$lines = file('c:/xampp/htdocs/ELSAJANA2/magaies/js/balances-report.js');
for ($i = 1460; $i <= 1500; $i++) {
    if (isset($lines[$i])) {
        echo ($i+1) . ': ' . rtrim($lines[$i]) . "\n";
    }
}
?>
