<?php
require_once __DIR__ . '/../api/db.php';
$stmt = $pdo->query('SHOW CREATE TABLE payments');
print_r($stmt->fetch(PDO::FETCH_ASSOC));

$stmt2 = $pdo->query('SELECT id, data, tenant_id FROM payments LIMIT 2');
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
