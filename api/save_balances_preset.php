<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=UTF-8');

try {
    // Ensure table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS global_settings (
        setting_key VARCHAR(191) NOT NULL PRIMARY KEY,
        setting_value LONGTEXT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['id']) || !isset($input['config'])) {
        echo json_encode(['error' => 'Invalid data']);
        exit;
    }

    $rawId = (string)$input['id'];
    $presetId = 'balances_preset_' . $rawId;
    $isModel = strpos($rawId, 'm') === 0;
    if ($isModel) {
        $presetId = 'balances_model_' . substr($rawId, 1);
    }

    // Check if locked
    $stmt = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = ?");
    $stmt->execute([$presetId . '_locked']);
    $isLockedVal = $stmt->fetchColumn();

    if ($isLockedVal === 'true' && empty($input['overrideLock'])) {
        echo json_encode(['error' => 'This preset is permanently locked and cannot be modified.', 'isLocked' => true]);
        exit;
    }

    $configJson = json_encode($input['config'], JSON_UNESCAPED_UNICODE);

    // Save config into global_settings
    $stmtSave = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmtSave->execute([$presetId, $configJson, $configJson]);

    // Lock permanently if lockRequested is true
    if (!empty($input['lockRequested']) || !$isModel) {
        $stmtLock = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmtLock->execute([$presetId . '_locked', 'true', 'true']);
    }

    echo json_encode(['success' => true, 'presetId' => $presetId, 'isLocked' => true]);

} catch (Exception $e) {
    error_log("Error saving balances preset: " . $e->getMessage());
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
