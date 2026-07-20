<?php
$f = file_get_contents('api_index.php');
$f = preg_replace('/catch\s*\(\s*PDOException\s+\$\x01\s*\)\s*\{\s*error_log\(\'API Index Error: \' \. \$\x01->getMessage\(\)\);/', 'catch (PDOException $e) { error_log(\'API Index Error: \' . $e->getMessage());', $f);
file_put_contents('api_index.php', $f);

$f2 = file_get_contents('db_bridge.php');
$f2 = preg_replace('/catch\s*\(\s*Exception\s+\$\x01\s*\)\s*\{\s*error_log\(\'DB Bridge Error in \' \. __FUNCTION__ \. \': \' \. \$\x01->getMessage\(\)\);/', 'catch (Exception $e) { error_log(\'DB Bridge Error in \' . __FUNCTION__ . \': \' . $e->getMessage());', $f2);
file_put_contents('db_bridge.php', $f2);
echo "Fixed ASCII 0x01!\n";
