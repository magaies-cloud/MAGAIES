<?php
$lines = file('c:/xampp/htdocs/ELSAJANA2/magaies/general-ledger.php');
foreach($lines as $i => $line) {
    if (strpos($line, 'preset-slot-btn') !== false || strpos($line, 'افتراضي') !== false || strpos($line, 'نموذج') !== false) {
        echo ($i+1) . ': ' . trim($line) . "\n";
    }
}
?>
