<?php
$lines = file('c:/xampp/htdocs/ELSAJANA2/magaies/js/balances-report.js');
foreach ($lines as $i => $line) {
    if (strpos($line, 'Preset') !== false || strpos($line, 'preset') !== false) {
        echo ($i+1) . ': ' . trim($line) . "\n";
    }
}
?>
