<?php
require 'api/db.php';

$allowed_tables = [
    'users', 'customers', 'suppliers', 'items', 'inventory',
    'sales_invoices', 'purchase_invoices', 'receipts', 'payments',
    'journal_vouchers', 'settings', 'warehouses', 'banks', 'safes',
    'categories', 'roles', 'checks', 'gl_entries', 'single_receipts', 
    'single_payments', 'partners', 'employees', 'payroll', 
    'reserved_sales', 'delivery_notes', 'accounts'
];

foreach ($allowed_tables as $table) {
    echo "Processing table: $table\n";
    // 1. updated_at
    try {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "  - Added updated_at\n";
    } catch (PDOException $e) {}

    // 2. frontend_id and Unique Index
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'data'");
        if ($stmt->rowCount() > 0) {
            $pdo->exec("ALTER TABLE `$table` ADD COLUMN `frontend_id` VARCHAR(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.id'))) VIRTUAL");
            echo "  - Added frontend_id\n";
            $pdo->exec("CREATE UNIQUE INDEX idx_{$table}_unique_frontend ON `$table` (tenant_id, frontend_id)");
            echo "  - Added UNIQUE INDEX\n";
        }
    } catch (PDOException $e) {}

    // 3. is_deleted (VIRTUAL)
    try {
        // Just in case it was created as a real column somewhere
        $pdo->exec("ALTER TABLE `$table` DROP COLUMN `is_deleted`");
    } catch (PDOException $e) {}
    try {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `is_deleted` TINYINT(1) GENERATED ALWAYS AS (IF(json_unquote(json_extract(`data`,'$.deleted')) = 'true' OR json_unquote(json_extract(`data`,'$.deleted')) = '1', 1, 0)) VIRTUAL");
        echo "  - Added virtual is_deleted\n";
    } catch (PDOException $e) {}
}
echo "Live Migration Phase 2 complete.\n";
