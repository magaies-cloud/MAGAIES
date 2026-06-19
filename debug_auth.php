<?php
// Test script
function loadEnv($path) {
    if (!file_exists($path)) { echo "ENV NOT FOUND: $path\n"; return; }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
        putenv(trim($key) . '=' . trim($value));
    }
}
loadEnv(__DIR__ . '/.env');

$token = $_ENV['SAAS_ADMIN_TOKEN'] ?? 'NOT_SET';
$pass_env = $_ENV['SAAS_ADMIN_PASSWORD'] ?? 'NOT_SET';
echo json_encode(['token' => $token, 'pass_env' => $pass_env, 'env_file_exists' => file_exists(__DIR__ . '/.env')]);
