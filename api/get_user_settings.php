<?php
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

$userId = isset($_GET['user_id']) ? $_GET['user_id'] : 'default_user';

try {
    // Attempt to select from user_settings table
    // Using a quick check to see if the table exists first to avoid error spam in logs
    $tableExists = $pdo->query("SHOW TABLES LIKE 'user_settings'")->rowCount() > 0;
    
    if (!$tableExists) {
        // Table hasn't been created yet, return empty
        echo json_encode(['status' => 'success', 'data' => []]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM user_settings WHERE user_id = ?");
    $stmt->execute([$userId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $settings = [];
    foreach($results as $row) {
        $settings[$row['setting_key']] = json_decode($row['setting_value'], true);
    }
    
    echo json_encode(['status' => 'success', 'data' => $settings]);
} catch (PDOException $e) {
    error_log("Error fetching user settings: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error.', 'data' => []]);
}
