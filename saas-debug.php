<?php
// ============================================================
// TEMPORARY DEBUG FILE — DELETE AFTER FIXING
// ============================================================
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

// Simple token check — pass ?token=SUPER_ADMIN_SECRET_2026 in URL
$token = $_GET['token'] ?? '';

// Load env
function loadEnv($path) {
    if (!file_exists($path)) return;
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

$saasToken = $_ENV['SAAS_ADMIN_TOKEN'] ?? 'SUPER_ADMIN_SECRET_2026';

if (!$token || $token !== $saasToken) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized. Pass ?token=YOUR_SAAS_TOKEN']);
    exit;
}

$debug = [];

// 1. ENV vars (masked)
$debug['env'] = [
    'DB_HOST' => $_ENV['DB_HOST'] ?? 'NOT SET',
    'DB_PORT' => $_ENV['DB_PORT'] ?? 'NOT SET',
    'DB_USER' => $_ENV['DB_USER'] ?? 'NOT SET',
    'DB_NAME' => $_ENV['DB_NAME'] ?? 'NOT SET',
    'DB_PASS'  => !empty($_ENV['DB_PASS']) ? '***SET***' : 'EMPTY',
    'SAAS_ADMIN_TOKEN' => !empty($_ENV['SAAS_ADMIN_TOKEN']) ? '***SET***' : 'NOT SET',
    'token_received' => $token,
    'token_match' => ($token === $saasToken) ? 'YES' : 'NO',
];

// 2. DB connection
try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';
    $db   = $_ENV['DB_NAME'] ?? 'defaultdb';

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ]);
    $debug['db_connection'] = 'SUCCESS';

    // 3. List all tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $debug['tables'] = $tables;

    // 4. Tenants count + data
    if (in_array('tenants', $tables)) {
        $count = $pdo->query("SELECT COUNT(*) FROM tenants")->fetchColumn();
        $debug['tenants_count'] = (int)$count;
        $debug['tenants_sample'] = $pdo->query("SELECT id, company_name, subdomain, owner_name, status, valid_until FROM tenants ORDER BY created_at DESC LIMIT 10")->fetchAll();
    } else {
        $debug['tenants_table'] = 'TABLE DOES NOT EXIST';
    }

    // 5. REQUEST_URI test
    $debug['request_uri'] = $_SERVER['REQUEST_URI'];
    $debug['path_info']   = $_SERVER['PATH_INFO'] ?? 'NOT SET';

} catch (Exception $e) {
    $debug['db_connection'] = 'FAILED';
    $debug['db_error'] = $e->getMessage();
}

echo json_encode($debug, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
