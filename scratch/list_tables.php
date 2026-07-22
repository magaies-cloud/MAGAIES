<?php
require_once __DIR__ . '/../api/db.php';
global $pdo;

$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode(['tables' => $tables], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
