<?php
$content = file_get_contents('c:/xampp/htdocs/ELSAJANA2/magaies/balances-report.php');
$lines = explode("\n", $content);
foreach($lines as $i => $line) {
    if (strpos($line, 'balances-presets-wrap') !== false) {
        for ($j = max(0, $i - 5); $j <= min(count($lines)-1, $i + 15); $j++) {
            echo ($j+1) . ": " . $lines[$j] . "\n";
        }
        break;
    }
}
?>
