<?php
$lines = file('c:/xampp/htdocs/ELSAJANA2/magaies/js/balances-report.js');
for ($i = 2790; $i <= 2950; $i++) {
    if (isset($lines[$i])) {
        echo ($i+1) . ': ' . rtrim($lines[$i]) . "\n";
    }
}
?>
