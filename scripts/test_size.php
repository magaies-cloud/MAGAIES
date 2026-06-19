<?php
require "../api/db.php";

$tables = ["customers", "suppliers", "items", "inventory", "sales_invoices", "purchase_invoices", "receipts", "payments", "journal_vouchers", "settings", "warehouses", "banks", "categories", "roles", "checks", "gl_entries", "single_receipts", "single_payments", "partners", "employees", "payroll", "reserved_sales", "delivery_notes", "accounts"];

$unionQueries = [];
foreach ($tables as $t) {
    $unionQueries[] = "SELECT tenant_id, IFNULL(SUM(LENGTH(data)), 0) as sz FROM $t GROUP BY tenant_id";
}

$sql = "SELECT tenant_id, SUM(sz) as total_size FROM (" . implode(" UNION ALL ", $unionQueries) . ") as all_sizes GROUP BY tenant_id";

$stmt = $pdo->query($sql);
$sizes = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($sizes);
