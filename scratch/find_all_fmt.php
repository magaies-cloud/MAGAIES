<?php
$content = file_get_contents('js/balances-report.js');
preg_match_all('/.*(fmtColNum|setColDecimals|toggleColThousands|thousandsSep|decSep|roundMode).*/u', $content, $matches, PREG_OFFSET_CAPTURE);
echo "Total matches: " . count($matches[0]) . "\n";
foreach ($matches[0] as $m) {
    $line = substr_count(substr($content, 0, $m[1]), "\n") + 1;
    echo "Line $line: " . trim(substr($m[0], 0, 110)) . "\n";
}
