<?php
$lines = file('general-ledger.php');
foreach($lines as $i => $line) {
    if (strpos($line, 'magic-preview') !== false || strpos($line, 'magic-btn') !== false) {
        if ($i > 1250 && $i < 1300) {
            echo ($i + 1) . ": " . htmlspecialchars($line) . "\n";
        }
    }
}
