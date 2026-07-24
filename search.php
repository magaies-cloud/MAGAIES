<?php
$files = ['css/design-system.css', 'css/theme.css', 'css/components.css', 'css/sales-invoice.css', 'css/table-themes.css'];
foreach($files as $file) {
    if (!file_exists($file)) continue;
    $lines = file($file);
    foreach($lines as $i => $line) {
        if (strpos($line, 'num-font-jetbrains') !== false || strpos($line, 'num-font-poppins') !== false) {
            echo basename($file) . " Line " . ($i+1) . ": " . trim($line) . "\n";
        }
    }
}
