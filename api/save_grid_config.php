<?php
require 'db.php';
header('Content-Type: application/json; charset=UTF-8');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['grid_id']) || !isset($input['config'])) {
        echo json_encode(['error' => 'Invalid data']);
        exit;
    }

    $key = 'grid_config_' . $input['grid_id'];
    $configJson = json_encode($input['config'], JSON_UNESCAPED_UNICODE);

    $stmt = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $stmt->execute([$key, $configJson]);

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
