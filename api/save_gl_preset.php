<?php
require 'db.php';
header('Content-Type: application/json; charset=UTF-8');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['id']) || !isset($input['config'])) {
        echo json_encode(['error' => 'Invalid data']);
        exit;
    }

    $presetId = 'gl_preset_' . $input['id']; // e.g., gl_preset_1
    $isModel = strpos($input['id'], 'm') === 0; // m1, m2
    if ($isModel) {
        $presetId = 'gl_model_' . substr($input['id'], 1);
    }
    
    $configJson = json_encode($input['config']);

    // Check if locked
    $stmt = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = ?");
    $stmt->execute([$presetId . '_locked']);
    $isLocked = $stmt->fetchColumn();

    if ($isLocked === 'true' && !$isModel) {
        echo json_encode(['error' => 'This preset is permanently locked and cannot be modified.']);
        exit;
    }

    // Save config
    $stmt = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$presetId, $configJson, $configJson]);

    // Lock if not a model
    if (!$isModel) {
        $stmt->execute([$presetId . '_locked', 'true', 'true']);
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("Error saving GL preset: " . $e->getMessage());
    echo json_encode(['error' => 'Server error']);
}
?>
