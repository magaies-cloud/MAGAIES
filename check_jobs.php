<?php
$token = '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';
$runId = 29733154296;

$url = "https://api.github.com/repos/magaies-cloud/MAGAIES/actions/runs/$runId/jobs";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: token $token",
    "User-Agent: PHP-Script",
    "Accept: application/vnd.github.v3+json"
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP: $httpCode\n";
$data = json_decode($response, true);
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
