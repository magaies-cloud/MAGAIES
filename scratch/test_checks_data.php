<?php
require_once __DIR__ . '/../api/db.php';
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM `checks`");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($cols);
    
    $stmt = $pdo->query("SELECT * FROM `checks` LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
