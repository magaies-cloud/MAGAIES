<?php
require 'api/db.php';

$queries = [
    // CUSTOMERS
    "ALTER TABLE `customers` ADD COLUMN `search_name` VARCHAR(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.name'))) VIRTUAL",
    "ALTER TABLE `customers` ADD COLUMN `search_phone` VARCHAR(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.phone'))) VIRTUAL",
    "ALTER TABLE `customers` ADD COLUMN `search_email` VARCHAR(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.email'))) VIRTUAL",
    "ALTER TABLE `customers` ADD COLUMN `status_val` VARCHAR(50) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.status'))) VIRTUAL",
    "CREATE INDEX idx_cust_name ON `customers` (`search_name`)",
    "CREATE INDEX idx_cust_phone ON `customers` (`search_phone`)",
    "CREATE INDEX idx_cust_status ON `customers` (`status_val`)",

    // ITEMS
    "ALTER TABLE `items` ADD COLUMN `search_name` VARCHAR(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.name'))) VIRTUAL",
    "ALTER TABLE `items` ADD COLUMN `search_barcode` VARCHAR(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.barcode'))) VIRTUAL",
    "ALTER TABLE `items` ADD COLUMN `category_val` VARCHAR(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.category'))) VIRTUAL",
    "ALTER TABLE `items` ADD COLUMN `status_val` VARCHAR(50) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.status'))) VIRTUAL",
    "CREATE INDEX idx_item_name ON `items` (`search_name`)",
    "CREATE INDEX idx_item_barcode ON `items` (`search_barcode`)",
    "CREATE INDEX idx_item_category ON `items` (`category_val`)",

    // SALES INVOICES
    "ALTER TABLE `sales_invoices` ADD COLUMN `invoice_no` VARCHAR(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.invoiceNo'))) VIRTUAL",
    "ALTER TABLE `sales_invoices` ADD COLUMN `customer_id_val` VARCHAR(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.customerId'))) VIRTUAL",
    "ALTER TABLE `sales_invoices` ADD COLUMN `date_val` VARCHAR(50) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.date'))) VIRTUAL",
    "ALTER TABLE `sales_invoices` ADD COLUMN `total_val` DECIMAL(15,2) GENERATED ALWAYS AS (CAST(json_unquote(json_extract(`data`,'$.netTotal')) AS DECIMAL(15,2))) VIRTUAL",
    "ALTER TABLE `sales_invoices` ADD COLUMN `status_val` VARCHAR(50) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.status'))) VIRTUAL",
    "CREATE INDEX idx_sales_invoice_no ON `sales_invoices` (`invoice_no`)",
    "CREATE INDEX idx_sales_customer ON `sales_invoices` (`customer_id_val`)",
    "CREATE INDEX idx_sales_date ON `sales_invoices` (`date_val`)",

    // PURCHASE INVOICES
    "ALTER TABLE `purchase_invoices` ADD COLUMN `invoice_no` VARCHAR(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.invoiceNo'))) VIRTUAL",
    "ALTER TABLE `purchase_invoices` ADD COLUMN `supplier_id_val` VARCHAR(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.supplierId'))) VIRTUAL",
    "ALTER TABLE `purchase_invoices` ADD COLUMN `date_val` VARCHAR(50) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.date'))) VIRTUAL",
    "ALTER TABLE `purchase_invoices` ADD COLUMN `total_val` DECIMAL(15,2) GENERATED ALWAYS AS (CAST(json_unquote(json_extract(`data`,'$.netTotal')) AS DECIMAL(15,2))) VIRTUAL",
    "CREATE INDEX idx_purch_invoice_no ON `purchase_invoices` (`invoice_no`)",
    "CREATE INDEX idx_purch_supplier ON `purchase_invoices` (`supplier_id_val`)",
    
    // GL ENTRIES
    "ALTER TABLE `gl_entries` ADD COLUMN `account_id_val` VARCHAR(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.accountId'))) VIRTUAL",
    "ALTER TABLE `gl_entries` ADD COLUMN `date_val` VARCHAR(50) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,'$.date'))) VIRTUAL",
    "ALTER TABLE `gl_entries` ADD COLUMN `debit_val` DECIMAL(15,2) GENERATED ALWAYS AS (CAST(json_unquote(json_extract(`data`,'$.debit')) AS DECIMAL(15,2))) VIRTUAL",
    "ALTER TABLE `gl_entries` ADD COLUMN `credit_val` DECIMAL(15,2) GENERATED ALWAYS AS (CAST(json_unquote(json_extract(`data`,'$.credit')) AS DECIMAL(15,2))) VIRTUAL",
    "CREATE INDEX idx_gl_account ON `gl_entries` (`account_id_val`)",
    "CREATE INDEX idx_gl_date ON `gl_entries` (`date_val`)"
];

foreach ($queries as $q) {
    try {
        $pdo->exec($q);
        echo "Success: $q\n";
    } catch (PDOException $e) {
        // Ignore duplicate column/index errors
        if ($e->getCode() == '42S21' || strpos($e->getMessage(), 'Duplicate') !== false) {
            echo "Skipped (Already exists): $q\n";
        } else {
            echo "Error: $q - " . $e->getMessage() . "\n";
        }
    }
}
echo "Migration complete.\n";
