<?php
require 'db.php';
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

try {
    $grid_id = $_GET['grid_id'] ?? '';
    if (!$grid_id) {
        echo json_encode(['config' => null]);
        exit;
    }

    $key = 'grid_config_' . $grid_id;

    $stmt = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['setting_value']) {
        echo json_encode(['config' => json_decode($row['setting_value'], true)]);
    } else {
        echo json_encode(['config' => null]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
