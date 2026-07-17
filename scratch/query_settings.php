<?php
require_once __DIR__ . '/../db_bridge.php';
$pdo = getDBConnection();
echo "--- global_settings ---\n";
try {
    $stmt = $pdo->query("SELECT * FROM global_settings");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error global_settings: " . $e->getMessage() . "\n";
}

echo "--- settings ---\n";
try {
    $stmt = $pdo->query("SELECT * FROM settings");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error settings: " . $e->getMessage() . "\n";
}
?>
