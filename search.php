<?php
$content = file_get_contents(__DIR__ . '/balances-report.php');
$lines = explode("\n", $content);
foreach($lines as $i => $line) {
    if(stripos($line, 'preset') !== false || strpos($line, 'نموذج') !== false) {
        echo ($i+1) . ": " . htmlspecialchars($line) . "<br>\n";
    }
}
echo "Done";
