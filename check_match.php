<?php
require 'api/db.php';
$stmt = $pdo->query("SELECT data FROM sales_invoices WHERE tenant_id = '2'");
$invoice_customers = [];
while ($row = $stmt->fetch()) {
    $inv = json_decode($row['data'], true);
    if ($inv && isset($inv['customerId'])) {
        $invoice_customers[] = $inv['customerId'];
    }
}
$invoice_customers = array_unique($invoice_customers);

$stmt = $pdo->query("SELECT data FROM customers WHERE tenant_id = '2'");
$actual_customers = [];
while ($row = $stmt->fetch()) {
    $c = json_decode($row['data'], true);
    if ($c && isset($c['id'])) {
        $actual_customers[] = $c['id'];
    }
}

$matched = array_intersect($invoice_customers, $actual_customers);
echo "Unique Invoice Customer IDs: " . implode(', ', $invoice_customers) . "\n";
echo "Actual Customer IDs: " . implode(', ', $actual_customers) . "\n";
echo "Matched: " . count($matched) . "\n";
