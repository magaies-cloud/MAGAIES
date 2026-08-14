<?php
require 'db.php';
header('Content-Type: application/json; charset=UTF-8');

try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM global_settings WHERE setting_key LIKE 'num_prop_%'");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $props = [];
    foreach ($results as $row) {
        $props[$row['setting_key']] = json_decode($row['setting_value'], true);
    }
    
    echo json_encode($props);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
