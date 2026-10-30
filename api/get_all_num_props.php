<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=UTF-8');

try {
    $tenantId = resolveCurrentTenantId($pdo);
    $prefix = 'tenant_' . $tenantId . '_';
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM global_settings WHERE setting_key LIKE 'num_prop_%' OR setting_key LIKE ?");
    $stmt->execute([$prefix . 'num_prop_%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $props = [];
    foreach ($results as $row) {
        if (strpos($row['setting_key'], 'num_prop_') === 0) {
            $props[$row['setting_key']] = json_decode($row['setting_value'], true);
        }
    }
    foreach ($results as $row) {
        if (strpos($row['setting_key'], $prefix . 'num_prop_') === 0) {
            $cleanKey = substr($row['setting_key'], strlen($prefix));
            $props[$cleanKey] = json_decode($row['setting_value'], true);
        }
    }
    
    echo json_encode($props);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
