<?php
// Debug script to diagnose login 500 error on live server
$secret = $_GET['secret'] ?? '';
if ($secret !== 'magaees_deploy_2026_secure') {
    http_response_code(403);
    die('Forbidden');
}

ini_set('display_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/db.php';
    require_once __DIR__ . '/services/EmailService.php';
    require_once __DIR__ . '/controllers/AuthController.php';

    $auth = new AuthController($pdo);
    
    // Simulate login input
    $input = [
        'username' => 'c',
        'password' => '0000',
        'deviceUuid' => 'test-device-uuid',
        'deviceName' => 'Test Mobile',
        'deviceType' => 'mobile'
    ];

    echo json_encode(['status' => 'initialized', 'testing' => true]);
    
    // Run login
    $auth->handleRequest('login', 'POST', $input);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
