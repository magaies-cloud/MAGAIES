<?php
$lines = file('c:/xampp/htdocs/ELSAJANA2/magaies/general-ledger.php');
foreach($lines as $line) {
    if (strpos($line, '<script') !== false && strpos($line, 'src=') !== false) {
        echo trim($line) . "\n";
    }
}
?>
