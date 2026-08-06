<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS global_settings (
        setting_key VARCHAR(191) NOT NULL PRIMARY KEY,
        setting_value LONGTEXT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $stmt = $pdo->query("SELECT setting_key, setting_value FROM global_settings WHERE setting_key LIKE 'balances_preset_%' OR setting_key LIKE 'balances_model_%'");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $presets = [];
    foreach ($results as $row) {
        $key = $row['setting_key'];
        $val = json_decode($row['setting_value'], true);
        $presets[$key] = ($val !== null) ? $val : $row['setting_value'];
    }
    
    echo json_encode(['success' => true, 'presets' => $presets]);
} catch (Exception $e) {
    error_log("Error getting balances presets: " . $e->getMessage());
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
