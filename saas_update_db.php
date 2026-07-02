<?php
require 'api/db.php';
header('Content-Type: text/plain; charset=utf-8');

$tenantCols = [
    'subdomain' => 'VARCHAR(50) NULL',
    'owner_name' => 'VARCHAR(100) NULL',
    'owner_phone' => 'VARCHAR(50) NULL',
    'phone2' => 'VARCHAR(50) NULL',
    'owner_email' => 'VARCHAR(100) NULL',
    'receipt_path' => 'VARCHAR(255) NULL',
    'valid_until' => 'DATETIME NULL',
    'max_users' => 'INT DEFAULT NULL',
    'plan_id' => 'INT DEFAULT NULL',
    'status' => "VARCHAR(20) DEFAULT 'pending'",
    'company_name' => 'VARCHAR(100) NULL'
];

foreach ($tenantCols as $col => $def) {
    try {
        $pdo->exec("ALTER TABLE tenants ADD COLUMN $col $def");
        echo "Added $col to tenants\n";
    } catch (Exception $e) {
        echo "Column $col in tenants might already exist or error: " . $e->getMessage() . "\n";
    }
}

$globalSettingsCols = [
    'setting_key' => 'VARCHAR(100) NOT NULL PRIMARY KEY',
    'setting_value' => 'LONGTEXT NULL'
];
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS global_settings (setting_key VARCHAR(100) NOT NULL PRIMARY KEY, setting_value LONGTEXT NULL)");
    echo "Ensured global_settings exists\n";
} catch (Exception $e) {}

echo "\nDone!\n";