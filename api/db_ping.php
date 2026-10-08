<?php
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Tenant-Id');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

echo json_encode([
    'status' => 'ok',
    'timestamp' => round(microtime(true) * 1000),
    'server_time' => date('Y-m-d H:i:s')
], JSON_UNESCAPED_UNICODE);
