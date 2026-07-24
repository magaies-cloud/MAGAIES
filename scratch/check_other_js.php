<?php
$files = ['js/balances-report.js', 'js/general-ledger.js', 'js/account-statement.js'];
foreach ($files as $f) {
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    echo "$f: " . (str_contains($c, 'toggleColThousands') ? 'has toggleColThousands' : 'no toggleColThousands') . "\n";
}
