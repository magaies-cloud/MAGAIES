<?php
// 1. Update balances-report.php
$phpPath = 'balances-report.php';
$phpContent = file_get_contents($phpPath);
$phpContent = str_replace("\r\n", "\n", $phpContent);

$oldDiv = '<div id="columnContextMenu" style="display:none; position:fixed; z-index:100000;';
$newDiv = '<div id="columnContextMenu" style="display:block; position:fixed; z-index:100000; top:90px; left:50%; transform:translateX(-50%);';

if (str_contains($phpContent, $oldDiv)) {
    $phpContent = str_replace($oldDiv, $newDiv, $phpContent);
    file_put_contents($phpPath, $phpContent);
    echo "Updated balances-report.php to display modal by default!\n";
} else {
    echo "WARNING: oldDiv not found in balances-report.php\n";
}

// 2. Update js/balances-report.js
$jsPath = 'js/balances-report.js';
$jsContent = file_get_contents($jsPath);
$jsContent = str_replace("\r\n", "\n", $jsContent);

$oldInit = "document.addEventListener('DOMContentLoaded', () => {
    bindReportContextMenu();
});";

$newInit = "document.addEventListener('DOMContentLoaded', () => {
    bindReportContextMenu();
    setTimeout(() => {
        if (typeof openColumnContextMenu === 'function') openColumnContextMenu();
    }, 300);
});

document.addEventListener('DBReady', () => {
    bindReportContextMenu();
    setTimeout(() => {
        if (typeof openColumnContextMenu === 'function') openColumnContextMenu();
    }, 500);
});";

if (str_contains($jsContent, $oldInit)) {
    $jsContent = str_replace($oldInit, $newInit, $jsContent);
    file_put_contents($jsPath, $jsContent);
    echo "Updated js/balances-report.js to auto-open menu on page load!\n";
} else {
    echo "WARNING: oldInit not found in js/balances-report.js\n";
}
