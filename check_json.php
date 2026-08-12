<?php
require 'api/db.php';
$GLOBAL_TENANT_ID = 1; // Assuming tenant 1 is the main tenant
$stmt = $pdo->query("SELECT data FROM customers WHERE tenant_id = '1' AND (is_deleted = 0 OR is_deleted IS NULL) LIMIT 1");
echo "Customer JSON:\n" . $stmt->fetchColumn() . "\n\n";

$stmt = $pdo->query("SELECT data FROM sales_invoices WHERE tenant_id = '1' AND (is_deleted = 0 OR is_deleted IS NULL) LIMIT 1");
echo "Invoice JSON:\n" . $stmt->fetchColumn() . "\n";
