<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
ob_start();
require 'c:/xampp/htdocs/ELSAJANA2/magaies/api/get_balances_presets.php';
$output = ob_get_clean();
echo "Output: " . $output;
?>
