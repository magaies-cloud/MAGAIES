<?php
require 'api/db.php';
$stmt = $pdo->query('SELECT id, company_name, receipt_path FROM tenants ORDER BY id DESC LIMIT 5');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
