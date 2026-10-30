<?php
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

$tenantId = resolveCurrentTenantId($pdo);
$rawUserId = isset($_GET['user_id']) ? trim((string)$_GET['user_id']) : 'default_user';
$scopedUserId = (strpos($rawUserId, 't' . $tenantId . '_') === 0) ? $rawUserId : ('t' . $tenantId . '_' . $rawUserId);

try {
    $tableExists = $pdo->query("SHOW TABLES LIKE 'user_settings'")->rowCount() > 0;
    
    if (!$tableExists) {
        echo json_encode(['status' => 'success', 'data' => []]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM user_settings WHERE user_id = ?");
    $stmt->execute([$scopedUserId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $settings = [];
    foreach ($results as $row) {
        $settings[$row['setting_key']] = json_decode($row['setting_value'], true);
    }
    
    echo json_encode(['status' => 'success', 'data' => $settings]);
} catch (PDOException $e) {
    error_log("Error fetching user settings: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error.', 'data' => []]);
}
