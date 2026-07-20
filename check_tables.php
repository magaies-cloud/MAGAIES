<?php
$c = file_get_contents('database/schema.sql');
$c = mb_convert_encoding($c, 'UTF-8', 'UTF-16LE');
preg_match_all('/CREATE TABLE `(?:alert_logs|alert_settings|checks_velocity|customer_credit_profiles|inventory_items|transactions)`(.*?);/s', $c, $m);
foreach ($m[0] as $table) {
    echo "$table\n";
}
