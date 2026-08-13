<?php
$c = file_get_contents('database/schema.sql');
$c = mb_convert_encoding($c, 'UTF-8', 'UTF-16LE');
preg_match_all('/CREATE TABLE `([a-zA-Z0-9_]+)`(.*?);/s', $c, $m);
foreach ($m[1] as $idx => $table) {
    if (strpos($m[2][$idx], 'tenant_id') === false) {
        echo "$table\n";
    }
}
