<?php
$c = file_get_contents('database/schema.sql');
$c = mb_convert_encoding($c, 'UTF-8', 'UTF-16LE');
preg_match_all('/CREATE TABLE `checks` \((.*?)\) ENGINE/s', $c, $m);
echo $m[0][0];
