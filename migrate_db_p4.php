<?php
require 'api/db.php';

// 1. Create financial_periods table
$sql_periods = "
CREATE TABLE IF NOT EXISTS `financial_periods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `frontend_id` varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.id'))) VIRTUAL,
  `is_deleted` tinyint(1) GENERATED ALWAYS AS (if(json_unquote(json_extract(`data`,'$.deleted')) = 'true' or json_unquote(json_extract(`data`,'$.deleted')) = '1',1,0)) VIRTUAL,
  `is_closed` tinyint(1) GENERATED ALWAYS AS (if(json_unquote(json_extract(`data`,'$.isClosed')) = 'true' or json_unquote(json_extract(`data`,'$.isClosed')) = '1',1,0)) VIRTUAL,
  `start_date` date GENERATED ALWAYS AS (STR_TO_DATE(json_unquote(json_extract(`data`,'$.startDate')), '%Y-%m-%d')) VIRTUAL,
  `end_date` date GENERATED ALWAYS AS (STR_TO_DATE(json_unquote(json_extract(`data`,'$.endDate')), '%Y-%m-%d')) VIRTUAL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_periods_unique_frontend` (`tenant_id`,`frontend_id`),
  CONSTRAINT `periods_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";
try {
    $pdo->exec($sql_periods);
    echo "Created financial_periods table.\n";
} catch (PDOException $e) {
    echo "Error creating financial_periods: " . $e->getMessage() . "\n";
}

// 2. Add status_val and date_val to financial tables
$financial_tables = ['sales_invoices', 'purchase_invoices', 'gl_entries', 'receipts', 'payments', 'journal_vouchers', 'single_receipts', 'single_payments'];
foreach ($financial_tables as $table) {
    try {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `status_val` VARCHAR(50) GENERATED ALWAYS AS (LOWER(json_unquote(json_extract(`data`,'$.status')))) VIRTUAL");
        $pdo->exec("CREATE INDEX idx_{$table}_status ON `$table` (`status_val`)");
        echo "Added status_val to $table\n";
    } catch (PDOException $e) {}

    try {
        // Date might be in $.date, $.invoiceDate, $.receiptDate. Use COALESCE.
        $date_extract = "COALESCE(
            json_unquote(json_extract(`data`,'$.date')),
            json_unquote(json_extract(`data`,'$.invoiceDate')),
            json_unquote(json_extract(`data`,'$.receiptDate')),
            json_unquote(json_extract(`data`,'$.paymentDate'))
        )";
        // In JS, dates are often stored as YYYY-MM-DD strings or ISO strings.
        // We just extract the first 10 chars (YYYY-MM-DD)
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `date_val` DATE GENERATED ALWAYS AS (STR_TO_DATE(LEFT($date_extract, 10), '%Y-%m-%d')) VIRTUAL");
        $pdo->exec("CREATE INDEX idx_{$table}_date ON `$table` (`date_val`)");
        echo "Added date_val to $table\n";
    } catch (PDOException $e) {
        echo "Error on date_val $table: " . $e->getMessage() . "\n";
    }
}

echo "Migration Phase 4 complete.\n";
