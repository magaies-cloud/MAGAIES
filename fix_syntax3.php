<?php
$f = file_get_contents('api_index.php');
$f = str_replace('PDOException $ )', 'PDOException $e )', $f);
$f = str_replace('$ ->getMessage', '$e->getMessage', $f);
file_put_contents('api_index.php', $f);

$f2 = file_get_contents('db_bridge.php');
$f2 = str_replace('Exception $ )', 'Exception $e )', $f2);
$f2 = str_replace('$ ->getMessage', '$e->getMessage', $f2);
file_put_contents('db_bridge.php', $f2);
echo "Fixed!\n";
