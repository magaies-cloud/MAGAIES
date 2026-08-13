<?php
$files = ['customer_exposure.php', 'db_bridge.php', 'general_ledger_v2.php', 'ai_advisor.php', 'ai_money_advisor.php'];
foreach ($files as $f) {
    echo "=== $f ===\n";
    $lines = file($f);
    foreach ($lines as $k => $v) {
        if (preg_match('/(?:شركة النيل|محلات أولاد|المقاولات السودانية)/ui', $v)) {
            echo "Line " . ($k+1) . ": " . trim($v) . "\n";
        }
    }
}
