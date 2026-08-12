<?php
$content = file_get_contents('general-ledger.php');
$lines = explode("\n", $content);
foreach($lines as $i => $line) {
    if (strpos($line, '.ledger-header') !== false || strpos($line, '.action-buttons-container') !== false) {
        echo "Line " . ($i+1) . ": " . htmlspecialchars($line) . "\n";
        for ($j=1; $j<=15; $j++) {
            if (isset($lines[$i+$j])) {
                echo "Line " . ($i+$j+1) . ": " . htmlspecialchars($lines[$i+$j]) . "\n";
            }
        }
        echo "--------------\n";
    }
}
