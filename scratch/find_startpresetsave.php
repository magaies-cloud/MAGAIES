<?php
$lines = file('c:/xampp/htdocs/ELSAJANA2/magaies/js/general-ledger.js');
$target = -1;
foreach($lines as $i => $line) {
    if (strpos($line, 'startPresetSave') !== false) {
        $target = $i;
        break;
    }
}
if ($target !== -1) {
    $start = max(0, $target - 10);
    $end = min(count($lines), $target + 150);
    for ($j = $start; $j < $end; $j++) {
        echo ($j+1) . ': ' . rtrim($lines[$j]) . "\n";
    }
} else {
    echo "startPresetSave not found in general-ledger.js";
}
?>
