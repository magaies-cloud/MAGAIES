<?php
$files = ['balances-report.php', 'js/app.js', 'js/balances-report.js'];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    preg_match_all('/.*(حفظ الترتيب والتخصيص|مسمى العمود|تخصيص|contextmenu).*/u', $content, $matches, PREG_OFFSET_CAPTURE);
    echo "=== $file (" . count($matches[0]) . " matches) ===\n";
    foreach (array_slice($matches[0], 0, 10) as $m) {
        echo "Line " . substr_count(substr($content, 0, $m[1]), "\n") + 1 . ": " . trim($m[0]) . "\n";
    }
}
