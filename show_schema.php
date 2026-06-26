<?php
require __DIR__ . '/api/db.php';
$stmt = $pdo->query('SHOW CREATE TABLE warehouses');
print_r($stmt->fetch(PDO::FETCH_ASSOC));
