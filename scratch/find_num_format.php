<?php
$content = file_get_contents('js/balances-report.js');
preg_match_all('/.*(toFixed|toLocaleString|formatNum|thousands|dec).*/u', $content, $matches, PREG_OFFSET_CAPTURE);
echo "Matches in js/balances-report.js: " . count($matches[0]) . "\n";
foreach (array_slice($matches[0], 0, 30) as $m) {
    $line = substr_count(substr($content, 0, $m[1]), "\n") + 1;
    echo "Line $line: " . trim(substr($m[0], 0, 100)) . "\n";
}
