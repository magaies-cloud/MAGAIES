<?php
// 1. Fix db_bridge.php
$c = file_get_contents('db_bridge.php');
$c = preg_replace('/\$rate = 600000;\s*return \[\s*\'customer_name\' => \'شركة النيل للتجارة والإنشاءات\',.*?\];/s', 'return null;', $c);
file_put_contents('db_bridge.php', $c);

// 2. Fix customer_exposure.php
$c = file_get_contents('customer_exposure.php');
$c = preg_replace('/if \(\$mock_mode \|\| empty\(\$report_data\)\) \{.*?\];\s*\}/s', '', $c);
file_put_contents('customer_exposure.php', $c);

// 3. Fix general_ledger_v2.php
$c = file_get_contents('general_ledger_v2.php');
// Remove mock block
$c = preg_replace('/if \(\$mock_mode \|\| empty\(\$gl_entries\)\) \{.*?\};/s', '', $c);
$c = preg_replace('/\$selected_customer = \'شركة النيل الأزرق للمقاولات\';/s', '$selected_customer = \'\';', $c);
file_put_contents('general_ledger_v2.php', $c);

// 4. Fix ai_advisor.php
$c = file_get_contents('ai_advisor.php');
$c = preg_replace('/\$top_customers = \[\["customer_name" => "شركة النيل", "total_exposure" => 4500000\]\];/s', '$top_customers = [];', $c);
file_put_contents('ai_advisor.php', $c);

// 5. Fix ai_money_advisor.php
$c = file_get_contents('ai_money_advisor.php');
$c = preg_replace('/\$top_customers = \[\["customer_name" => "شركة النيل", "total_exposure" => 4500000\]\];/s', '$top_customers = [];', $c);
file_put_contents('ai_money_advisor.php', $c);

echo "Final mock data removed from 5 files.\n";
