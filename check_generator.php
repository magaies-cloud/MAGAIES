<?php
$c = file_get_contents('db_bridge.php');
preg_match_all('/function\s+.*?(?:generate|number|no)\(/i', $c, $m);
print_r($m[0]);
