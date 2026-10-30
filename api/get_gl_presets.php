<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

try {
    $tenantId = resolveCurrentTenantId($pdo);
    $prefix = 'tenant_' . $tenantId . '_';
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM global_settings WHERE setting_key LIKE ? OR setting_key LIKE ? OR setting_key LIKE 'gl_preset_%' OR setting_key LIKE 'gl_model_%'");
    $stmt->execute([$prefix . 'gl_preset_%', $prefix . 'gl_model_%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $presets = [];
    foreach ($results as $row) {
        if (strpos($row['setting_key'], 'gl_') === 0) {
            $presets[$row['setting_key']] = json_decode($row['setting_value'], true);
        }
    }
    foreach ($results as $row) {
        if (strpos($row['setting_key'], $prefix . 'gl_') === 0) {
            $cleanKey = substr($row['setting_key'], strlen($prefix));
            $presets[$cleanKey] = json_decode($row['setting_value'], true);
        }
    }
    
    echo json_encode(['success' => true, 'presets' => $presets]);
} catch (Exception $e) {
    error_log("Error getting GL presets: " . $e->getMessage());
    echo json_encode(['error' => 'Server error']);
}
?>
