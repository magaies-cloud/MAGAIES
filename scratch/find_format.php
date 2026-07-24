<?php
$content = file_get_contents('js/balances-report.js');
preg_match_all('/function\s+format[A-Za-z0-9_]*/', $content, $matches);
print_r($matches[0]);
