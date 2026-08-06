<?php
require 'db.php';
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM global_settings WHERE setting_key LIKE 'balances_preset_%' OR setting_key LIKE 'balances_model_%'");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $presets = [];
    foreach ($results as $row) {
        $presets[$row['setting_key']] = json_decode($row['setting_value'], true);
    }
    
    echo json_encode(['success' => true, 'presets' => $presets]);
} catch (Exception $e) {
    error_log("Error getting balances presets: " . $e->getMessage());
    echo json_encode(['error' => 'Server error']);
}
?>
