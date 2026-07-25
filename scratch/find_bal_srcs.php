<?php
$content = file_get_contents('c:/xampp/htdocs/ELSAJANA2/magaies/balances-report.php');
preg_match_all('/<script[^>]+src="([^"]+)"/i', $content, $matches);
print_r($matches[1]);
?>
