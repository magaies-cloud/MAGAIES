<?php
require_once __DIR__ . '/../api/db.php';
$stmt = $pdo->query("SELECT data FROM customers LIMIT 3");
while ($row = $stmt->fetch()) {
    echo $row['data'] . "\n\n";
}
?>
