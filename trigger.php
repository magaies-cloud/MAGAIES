<?php
$secret = 'magaees_deploy_2026_secure';
$payload = json_encode(['ref' => 'refs/heads/main']);
$sig = 'sha256=' . hash_hmac('sha256', $payload, $secret);
$ch = curl_init('https://c.elsajana.com/deploy_webhook.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'X-Hub-Signature-256: ' . $sig]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
if($response === false) {
    echo 'Curl error: ' . curl_error($ch);
} else {
    echo 'Response: ' . $response;
}
