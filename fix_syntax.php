<?php
// Fix db_bridge.php
$c = file_get_contents('db_bridge.php');
$c = preg_replace('/catch\s*\(\s*Exception\s+\$\s*\)\s*\{\s*error_log\s*\(\'DB Bridge Error in \' \. __FUNCTION__ \. \': \' \. \$\s*->getMessage\(\)\);\s*\}/', 'catch (Exception $e) { error_log(\'DB Bridge Error in \' . __FUNCTION__ . \': \' . $e->getMessage()); }', $c);
$c = preg_replace('/catch\s*\(\s*Exception\s+\$\s*\)\s*\{\s*error_log\s*\(\'DB Bridge Error in \' \. __FUNCTION__ \. \': \' \. \$\s*->getMessage\(\)\);/', 'catch (Exception $e) { error_log(\'DB Bridge Error in \' . __FUNCTION__ . \': \' . $e->getMessage());', $c);

// In case the quotes are double:
$c = preg_replace('/catch\s*\(\s*Exception\s+\$\s*\)\s*\{\s*error_log\s*\("DB Bridge Error in " \. __FUNCTION__ \. ": " \. \$\s*->getMessage\(\)\);\s*\}/', 'catch (Exception $e) { error_log("DB Bridge Error in " . __FUNCTION__ . ": " . $e->getMessage()); }', $c);
$c = preg_replace('/catch\s*\(\s*Exception\s+\$\s*\)\s*\{\s*error_log\s*\("DB Bridge Error in " \. __FUNCTION__ \. ": " \. \$\s*->getMessage\(\)\);/', 'catch (Exception $e) { error_log("DB Bridge Error in " . __FUNCTION__ . ": " . $e->getMessage());', $c);

file_put_contents('db_bridge.php', $c);

// Fix api_index.php
$c = file_get_contents('api_index.php');
$c = preg_replace('/catch\s*\(\s*PDOException\s+\$\s*\)\s*\{\s*error_log\s*\(\'API Index Error: \' \. \$\s*->getMessage\(\)\);/', 'catch (PDOException $e) { error_log(\'API Index Error: \' . $e->getMessage());', $c);
$c = preg_replace('/catch\s*\(\s*PDOException\s+\$\s*\)\s*\{\s*error_log\s*\("API Index Error: " \. \$\s*->getMessage\(\)\);/', 'catch (PDOException $e) { error_log("API Index Error: " . $e->getMessage());', $c);

file_put_contents('api_index.php', $c);

echo "Fixed syntax errors with regex!\n";
