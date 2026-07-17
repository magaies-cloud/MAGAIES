<?php
require_once __DIR__ . '/../db_bridge.php';
$pdo = getDBConnection();

echo "--- items (first 3) ---\n";
$stmt = $pdo->query("SELECT * FROM items LIMIT 3");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

echo "--- inventory (first 3) ---\n";
$stmt = $pdo->query("SELECT * FROM inventory LIMIT 3");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

echo "--- inventory_items (first 3) ---\n";
$stmt = $pdo->query("SELECT * FROM inventory_items LIMIT 3");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>
