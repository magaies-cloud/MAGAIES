<?php
$lines = file('c:/xampp/htdocs/ELSAJANA2/magaies/balances-report.php');
foreach ($lines as $i => $line) {
    if (strpos($line, 'balances-presets-wrap') !== false || strpos($line, 'renderPresets') !== false || strpos($line, 'savePreset') !== false) {
        echo ($i+1) . ': ' . trim($line) . "\n";
    }
}
?>
