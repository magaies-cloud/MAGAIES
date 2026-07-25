<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$input = json_encode(['id' => '1', 'config' => ['test' => 123]]);
file_put_contents('php://memory', $input); // Mocking input for php://input is tricky.
// I will just use a direct test inside save_balances_preset.php
?>
