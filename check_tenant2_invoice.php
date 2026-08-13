<?php
require 'api/db.php';
$stmt = $pdo->query("SELECT data FROM sales_invoices WHERE tenant_id = '2' LIMIT 1");
echo $stmt->fetchColumn() . "\n";
