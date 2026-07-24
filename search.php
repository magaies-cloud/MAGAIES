<?php
$content = file_get_contents('general-ledger.php');
if (strpos($content, 'presets.js') !== false) {
    echo "presets.js is loaded\n";
} else {
    echo "presets.js is NOT loaded\n";
}
