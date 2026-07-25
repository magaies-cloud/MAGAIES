<?php
$lines = file('c:/xampp/htdocs/ELSAJANA2/magaies/js/general-ledger.js');
foreach($lines as $i => $line) {
    if (strpos($line, 'DEFAULT_PRESETS_GL') !== false) {
        echo "js:" . ($i+1) . ': ' . trim($line) . "\n";
    }
}
$lines = file('c:/xampp/htdocs/ELSAJANA2/magaies/general-ledger.php');
foreach($lines as $i => $line) {
    if (strpos($line, 'DEFAULT_PRESETS_GL') !== false) {
        echo "php:" . ($i+1) . ': ' . trim($line) . "\n";
    }
}
?>
