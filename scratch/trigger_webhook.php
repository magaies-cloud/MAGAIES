<?php
$secret = 'magaees_deploy_2026_secure';
$payload = json_encode(['ref' => 'refs/heads/main']);
$signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

$ch = curl_init('https://c.elsajana.com/deploy_webhook.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-Hub-Signature-256: ' . $signature
]);

$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: $status\nResponse: $response\n";
