<?php
$lines = file('c:/xampp/htdocs/ELSAJANA2/magaies/balances-report.php');
for ($i = 2790; $i <= 2860; $i++) {
    echo ($i+1) . ': ' . rtrim($lines[$i]) . "\n";
}
?>
