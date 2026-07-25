<?php
$lines = file('c:/xampp/htdocs/ELSAJANA2/magaies/general-ledger.php');
$inScript = false;
foreach($lines as $i => $line) {
    if (strpos($line, 'function startPresetSave') !== false || strpos($line, 'function startModelSave') !== false) {
        $inScript = true;
    }
    if ($inScript) {
        echo ($i+1) . ': ' . rtrim($line) . "\n";
    }
    if ($inScript && strpos($line, '</script>') !== false) {
        break;
    }
}
?>
