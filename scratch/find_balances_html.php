<?php
$lines = file('c:/xampp/htdocs/ELSAJANA2/magaies/balances-report.php');
for ($i = 745; $i <= 765; $i++) {
    echo ($i+1) . ': ' . rtrim($lines[$i]) . "\n";
}
?>
