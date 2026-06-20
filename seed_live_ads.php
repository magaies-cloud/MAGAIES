<?php
$url = 'https://www.elsajana.com/api_index.php?r=saas&a=settings&a2=ads';
$payload = file_get_contents('payload.json');
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer SUPER_ADMIN_SECRET_2026'
]);
echo curl_exec($ch);
curl_close($ch);
