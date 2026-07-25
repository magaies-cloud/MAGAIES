<?php
$content = file_get_contents('c:/xampp/htdocs/ELSAJANA2/magaies/js/balances-report.js');
preg_match_all('/window\.([a-zA-Z0-9_]+)\s*=/', $content, $matches);
$vars = array_unique($matches[1]);
print_r($vars);
?>
