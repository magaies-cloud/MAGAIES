<?php
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['setting_key']) || !isset($data['setting_value'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit;
}

$userId = isset($data['user_id']) ? $data['user_id'] : 'default_user';
$settingKey = $data['setting_key'];
$settingValue = is_array($data['setting_value']) ? json_encode($data['setting_value'], JSON_UNESCAPED_UNICODE) : $data['setting_value'];

try {
    // Ensure the table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(50) NOT NULL,
        setting_key VARCHAR(100) NOT NULL,
        setting_value LONGTEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY user_setting_unique (user_id, setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Insert or Update the setting
    $stmt = $pdo->prepare("
        INSERT INTO user_settings (user_id, setting_key, setting_value) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");
    $stmt->execute([$userId, $settingKey, $settingValue]);

    echo json_encode(['status' => 'success', 'message' => 'Settings saved successfully.']);
} catch (PDOException $e) {
    error_log("Error saving user settings: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
