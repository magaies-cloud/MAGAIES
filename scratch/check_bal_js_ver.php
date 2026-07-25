<?php
$content = file_get_contents('c:/xampp/htdocs/ELSAJANA2/magaies/balances-report.php');
preg_match('/balances-report\.js[^"]*/', $content, $matches);
print_r($matches);
?>
