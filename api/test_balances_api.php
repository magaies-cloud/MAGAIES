<?php
$data = ['id' => '1', 'config' => ['test' => 123]];
$options = [
    'http' => [
        'header'  => "Content-Type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data)
    ]
];
$context  = stream_context_create($options);
$result = file_get_contents('http://localhost:8080/api/save_balances_preset.php', false, $context);
echo "Save result: " . $result . "\n";

$result2 = file_get_contents('http://localhost:8080/api/get_balances_presets.php');
echo "Get result: " . $result2 . "\n";
?>
