<?php
require 'api/db.php';

// 1. Create audit_logs table
$sql_audit = "
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `frontend_id` varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.id'))) VIRTUAL,
  `is_deleted` tinyint(1) GENERATED ALWAYS AS (if(json_unquote(json_extract(`data`,'$.deleted')) = 'true' or json_unquote(json_extract(`data`,'$.deleted')) = '1',1,0)) VIRTUAL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_audit_logs_unique_frontend` (`tenant_id`,`frontend_id`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";
try {
    $pdo->exec($sql_audit);
    echo "Created audit_logs table.\n";
} catch (PDOException $e) {
    echo "Error creating audit_logs: " . $e->getMessage() . "\n";
}

// 2. Add currency and exchange_rate to financial tables
$financial_tables = ['sales_invoices', 'purchase_invoices', 'gl_entries', 'receipts', 'payments'];
foreach ($financial_tables as $table) {
    try {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `currency_val` VARCHAR(10) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.currency'))) VIRTUAL");
        // For rate, it might be 'rate' or 'exchangeRate' depending on the module. We can use COALESCE if needed, or just extract both. Let's use 'rate' since JS has `rate` and `exchangeRate`.
        // Let's use JSON_UNQUOTE(JSON_EXTRACT(data, '$.currency'))
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `exchange_rate_val` DECIMAL(15,4) GENERATED ALWAYS AS (CAST(COALESCE(json_unquote(json_extract(`data`,'$.rate')), json_unquote(json_extract(`data`,'$.exchangeRate')), '1') AS DECIMAL(15,4))) VIRTUAL");
        
        $pdo->exec("CREATE INDEX idx_{$table}_currency ON `$table` (`currency_val`)");
        echo "Added currency to $table\n";
    } catch (PDOException $e) {}
}

// 3. Add created_by_val to main tables
$all_tables = ['customers', 'suppliers', 'items', 'sales_invoices', 'purchase_invoices', 'gl_entries', 'receipts', 'payments', 'journal_vouchers'];
foreach ($all_tables as $table) {
    try {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `created_by_val` VARCHAR(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.createdBy'))) VIRTUAL");
        $pdo->exec("CREATE INDEX idx_{$table}_created_by ON `$table` (`created_by_val`)");
        echo "Added created_by to $table\n";
    } catch (PDOException $e) {}
}

echo "Migration Phase 3 complete.\n";
