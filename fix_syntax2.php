<?php
$f = file_get_contents('api_index.php');
$f = str_replace("catch (PDOException $ ) { error_log('API Index Error: ' . $ ->getMessage());", "catch (PDOException \$e) { error_log('API Index Error: ' . \$e->getMessage());", $f);
file_put_contents('api_index.php', $f);

$f = file_get_contents('db_bridge.php');
$f = str_replace("catch (Exception $ ) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $ ->getMessage());", "catch (Exception \$e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . \$e->getMessage());", $f);
// check if there is an empty catch block replaced earlier
$f = str_replace("catch (Exception $ ) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $ ->getMessage()); }", "catch (Exception \$e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . \$e->getMessage()); }", $f);
file_put_contents('db_bridge.php', $f);
echo "Fixed!\n";
