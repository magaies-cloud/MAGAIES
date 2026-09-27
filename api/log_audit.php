<?php
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

// Multi-tenant resolution
$tenantId = 1;
if (!empty($_COOKIE['currentTenantId']) && is_numeric($_COOKIE['currentTenantId'])) {
    $tenantId = (int)$_COOKIE['currentTenantId'];
} elseif (!empty($_SESSION['user']['tenant_id'])) {
    $tenantId = (int)$_SESSION['user']['tenant_id'];
}

// Handle GET to list recent audit logs
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT id, data, created_at FROM audit_logs WHERE tenant_id = ? ORDER BY id DESC LIMIT 100");
        $stmt->execute([$tenantId]);
        $rows = [];
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $parsed = json_decode($r['data'] ?? '{}', true) ?: [];
            $parsed['_db_id'] = $r['id'];
            $parsed['_db_created_at'] = $r['created_at'];
            $rows[] = $parsed;
        }
        echo json_encode(['status' => 'success', 'data' => $rows], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !is_array($data)) {
    echo json_encode(['status' => 'error', 'message' => 'بيانات غير صالحة']);
    exit;
}

$frontendId = $data['id'] ?? ('log_' . round(microtime(true) * 1000) . '_' . bin2hex(random_bytes(3)));
$data['id'] = $frontendId;
if (!isset($data['timestamp'])) {
    $data['timestamp'] = round(microtime(true) * 1000);
}

try {
    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare("INSERT INTO audit_logs (tenant_id, data, frontend_id) VALUES (?, ?, ?)");
    $stmt->execute([$tenantId, $jsonData, $frontendId]);

    echo json_encode([
        'status' => 'success',
        'id' => $pdo->lastInsertId(),
        'frontend_id' => $frontendId
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    error_log("Error saving audit log: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
