<?php
require 'api/db.php';
$stmt = $pdo->query("SELECT * FROM tenants");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt = $pdo->query("SELECT tenant_id, count(*) as count FROM customers GROUP BY tenant_id");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt = $pdo->query("SELECT tenant_id, count(*) as count FROM sales_invoices GROUP BY tenant_id");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
