<?php
require_once __DIR__ . '/../db_bridge.php';
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM inventory LIMIT 5");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
