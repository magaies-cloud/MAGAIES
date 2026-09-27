<?php
header('Content-Type: application/json; charset=utf-8');

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
$url = trim($data['url'] ?? $_GET['url'] ?? '');

if (empty($url)) {
    echo json_encode(['success' => false, 'error' => 'الرجاء إدخال الرابط']);
    exit;
}

// Basic security check: URL validation
if (!filter_var($url, FILTER_VALIDATE_URL) || (!preg_match('/^https?:\/\//i', $url))) {
    echo json_encode(['success' => false, 'error' => 'رابط غير صالح']);
    exit;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 7);
curl_setopt($ch, CURLOPT_TIMEOUT, 8);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
$response = curl_exec($ch);
$effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

$lat = null;
$lng = null;

// Pattern 1: @lat,lng
if (preg_match('/@(-?\d{1,2}\.\d+),(-?\d{1,3}\.\d+)/', $effectiveUrl, $matches)) {
    $lat = (float)$matches[1];
    $lng = (float)$matches[2];
}
// Pattern 2: ?q=lat,lng or &q=lat,lng
elseif (preg_match('/[?&]q=(-?\d{1,2}\.\d+)[,+](-?\d{1,3}\.\d+)/', $effectiveUrl, $matches)) {
    $lat = (float)$matches[1];
    $lng = (float)$matches[2];
}
// Pattern 3: ?ll=lat,lng or &ll=lat,lng
elseif (preg_match('/[?&]ll=(-?\d{1,2}\.\d+)[,+](-?\d{1,3}\.\d+)/', $effectiveUrl, $matches)) {
    $lat = (float)$matches[1];
    $lng = (float)$matches[2];
}
// Pattern 4: /place/.../lat,lng
elseif (preg_match('/place\/[^\/]+\/(-?\d{1,2}\.\d+),(-?\d{1,3}\.\d+)/', $effectiveUrl, $matches)) {
    $lat = (float)$matches[1];
    $lng = (float)$matches[2];
}

echo json_encode([
    'success' => ($lat !== null && $lng !== null),
    'originalUrl' => $url,
    'effectiveUrl' => $effectiveUrl,
    'lat' => $lat,
    'lng' => $lng
], JSON_UNESCAPED_UNICODE);
