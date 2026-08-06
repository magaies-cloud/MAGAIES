<?php
require 'db.php';
header('Content-Type: application/json; charset=UTF-8');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['id']) || !isset($input['config'])) {
        echo json_encode(['error' => 'Invalid data']);
        exit;
    }

    $presetId = 'balances_preset_' . $input['id'];
    $isModel = strpos($input['id'], 'm') === 0;
    if ($isModel) {
        $presetId = 'balances_model_' . substr($input['id'], 1);
    }
    
    $configJson = json_encode($input['config']);

    // Check if locked
    $stmt = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = ?");
    $stmt->execute([$presetId . '_locked']);
    $isLocked = $stmt->fetchColumn();

    if ($isLocked === 'true' && !$isModel && empty($input['force'])) {
        echo json_encode(['error' => '🔒 هذا التقرير مقفول نهائياً كضبط مصنع ثابت لجميع الشركات ولا يمكن تعديله أو تغييره.', 'isLocked' => true]);
        exit;
    }

    // Save config
    $stmt = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$presetId, $configJson, $configJson]);

    // Lock if not a model
    if (!$isModel) {
        $stmt->execute([$presetId . '_locked', 'true', 'true']);
    }

    echo json_encode(['success' => true, 'isLocked' => true]);

} catch (Exception $e) {
    error_log("Error saving balances preset: " . $e->getMessage());
    echo json_encode(['error' => 'Server error']);
}
?>
