<?php
$content = file_get_contents('c:/xampp/htdocs/ELSAJANA2/magaies/balances-report.php');
if (strpos($content, 'startPresetSave') !== false) {
    echo "startPresetSave is present.\n";
} else {
    echo "startPresetSave is NOT present.\n";
}

$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (strpos($line, 'افتراضي') !== false || strpos($line, 'preset') !== false) {
        echo ($i+1) . ': ' . trim($line) . "\n";
    }
}
?>
