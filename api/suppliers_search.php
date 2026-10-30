<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=UTF-8');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (!empty($origin)) {
    $allowed = false;
    $exact_allowed = [
        'https://elsajana.com',
        'https://www.elsajana.com',
        'https://c.elsajana.com',
        'http://localhost:8080',
        'http://localhost:3000',
        'http://127.0.0.1:8080',
        'http://127.0.0.1:3000'
    ];
    if (in_array($origin, $exact_allowed)) {
        $allowed = true;
    } else {
        $parsed = parse_url($origin);
        if (isset($parsed['host'])) {
            $host = $parsed['host'];
            if ($host === 'elsajana.com' || (function_exists('str_ends_with') ? str_ends_with($host, '.elsajana.com') : substr($host, -13) === '.elsajana.com')) {
                $allowed = true;
            }
        }
    }
    if ($allowed) {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
    }
}

$tenant_id = resolveCurrentTenantId($pdo);
if (!$tenant_id) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([]);
    exit;
}

$q = trim(mb_strtolower($_GET['q'] ?? ''));

try {
    if ($q === '') {
        $sql = "SELECT data FROM suppliers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 20";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tenant_id]);
    } else {
        $sql = "SELECT data FROM suppliers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) AND LOWER(data) LIKE :q ORDER BY id DESC LIMIT 20";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $tenant_id);
        $stmt->bindValue(':q', "%$q%");
        $stmt->execute();
    }

    $results = [];
    while ($row = $stmt->fetch()) {
        $data = json_decode($row['data'], true);
        if ($data && empty($data['deleted'])) {
            $results[] = [
                'id' => $data['id'] ?? '',
                'name' => $data['name'] ?? '',
                'ledger' => floatval($data['currentBalance'] ?? 0),
                'creditLimit' => floatval($data['creditLimit'] ?? 0),
                'phone' => $data['phone'] ?? '',
                'code' => $data['code'] ?? ''
            ];
        }
    }
    
    echo json_encode($results, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log('Supplier Search API Error: ' . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
}
