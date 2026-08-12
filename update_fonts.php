<?php
$path = 'js/general-ledger.js';
$content = file_get_contents($path);

// Increase ApexCharts font sizes by 3
$content = preg_replace_callback("/fontSize:\s*'(\d+)px'/", function($m) {
    return "fontSize: '" . ((int)$m[1] + 3) . "px'";
}, $content);

file_put_contents($path, $content);

$phpPath = 'general-ledger.php';
$phpContent = file_get_contents($phpPath);
$phpContent = str_replace('font-size: 1.05rem;', 'font-size: 1.25rem;', $phpContent);
$phpContent = str_replace('font-size: 0.72rem;', 'font-size: 0.85rem;', $phpContent);
$phpContent = str_replace('font-size: 0.78rem;', 'font-size: 0.90rem;', $phpContent);
$phpContent = str_replace('font-size: 1.35rem;', 'font-size: 1.50rem;', $phpContent);

file_put_contents($phpPath, $phpContent);

echo "SUCCESS";
