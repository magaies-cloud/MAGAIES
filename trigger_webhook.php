<?php
$payload = json_encode(['ref'=>'refs/heads/main','head_commit'=>['message'=>'Manual deploy from antigravity']]);
$secret = 'magaees_deploy_2026_secure';
$signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

$opts = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n" .
                    "X-Hub-Signature-256: $signature\r\n",
        'content' => $payload,
        'ignore_errors' => true,
        'timeout' => 60
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
];

$urls = ['https://c.elsajana.com/deploy_webhook.php'];
foreach ($urls as $url) {
    echo "Triggering: $url\n";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Hub-Signature-256: ' . $signature
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($result === false || $httpCode >= 400) {
        echo "Failed for $url (HTTP $httpCode): $err $result\n";
    } else {
        echo "Success for $url (HTTP $httpCode): $result\n";
    }
}

