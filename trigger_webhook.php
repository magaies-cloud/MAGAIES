<?php
$payload = json_encode(['ref'=>'refs/heads/main','head_commit'=>['message'=>'Manual deploy from antigravity']]);
$secret = 'magaees_deploy_2026_secure';
$signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

$opts = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n" .
                    "X-Hub-Signature-256: $signature\r\n",
        'content' => $payload
    ]
];
$context = stream_context_create($opts);

$urls = ['http://147.182.214.194/deploy_webhook.php', 'https://c.elsajana.com/deploy_webhook.php'];
foreach ($urls as $url) {
    echo "Triggering: $url\n";
    $result = @file_get_contents($url, false, $context);
    if ($result === false) {
        echo "Failed for $url\n";
    } else {
        echo "Success for $url: $result\n";
    }
}
