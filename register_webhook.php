<?php
$token = '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';

// Create GitHub webhook
$url = 'https://api.github.com/repos/magaies-cloud/MAGAIES/hooks';
$webhookData = [
    'name' => 'web',
    'active' => true,
    'events' => ['push'],
    'config' => [
        'url' => 'https://c.elsajana.com/deploy_webhook.php',
        'content_type' => 'json',
        'secret' => 'magaees_deploy_2026_secure',
        'insecure_ssl' => '0'
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: token $token",
    "User-Agent: PHP-Script",
    "Accept: application/vnd.github.v3+json",
    "Content-Type: application/json"
]);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP: $httpCode\n";
$data = json_decode($res, true);
if (isset($data['id'])) {
    echo "✓ Webhook created! ID: " . $data['id'] . "\n";
    echo "URL: " . $data['config']['url'] . "\n";
    echo "Events: " . implode(', ', $data['events']) . "\n";
    echo "Active: " . ($data['active'] ? 'Yes' : 'No') . "\n";
} else {
    echo "Response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
}
