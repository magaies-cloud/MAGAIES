<?php
require_once __DIR__ . '/../api/db.php';
$tables = [
    'users', 'customers', 'suppliers', 'items', 'inventory',
    'sales_invoices', 'purchase_invoices', 'receipts', 'payments',
    'journal_vouchers', 'warehouses', 'banks', 'safes',
    'categories', 'roles', 'checks', 'gl_entries', 'single_receipts', 
    'single_payments', 'partners', 'employees', 'payroll', 
    'reserved_sales', 'delivery_notes', 'accounts', 'audit_logs', 'financial_periods'
];

foreach ($tables as $t) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "$t: $count\n";
    } catch (Exception $e) {
        echo "$t: Error (" . $e->getMessage() . ")\n";
    }
}
?>
