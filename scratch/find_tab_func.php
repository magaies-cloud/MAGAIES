<?php
$content = file_get_contents('js/balances-report.js');
preg_match_all('/.*switchCtxTab.*/u', $content, $matches, PREG_OFFSET_CAPTURE);
foreach ($matches[0] as $m) {
    $line = substr_count(substr($content, 0, $m[1]), "\n") + 1;
    echo "Line $line: " . trim($m[0]) . "\n";
}
