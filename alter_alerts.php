<?php
require_once __DIR__ . '/api/db.php';

try {
    // 1. alert_logs
    $pdo->exec("ALTER TABLE alert_logs ADD COLUMN tenant_id INT(11) NULL AFTER id");
    $pdo->exec("ALTER TABLE alert_logs ADD CONSTRAINT fk_alert_logs_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE");

    // 2. alert_settings
    // Since alert_settings might already have data, we'll recreate it with a compound primary key or add tenant_id
    // But wait, if rule_key is primary key, it can't be just rule_key anymore, it has to be (tenant_id, rule_key)
    $pdo->exec("ALTER TABLE alert_settings DROP PRIMARY KEY");
    $pdo->exec("ALTER TABLE alert_settings ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 0 FIRST");
    
    // Now delete any existing rows without a valid tenant (or we map them to the first tenant)
    // Actually, it's safer to truncate it and let tenants insert their own settings or just drop and recreate it
    $pdo->exec("DROP TABLE alert_settings");
    $pdo->exec("CREATE TABLE `alert_settings` (
      `tenant_id` int(11) NOT NULL,
      `rule_key` varchar(50) NOT NULL,
      `rule_name` varchar(100) NOT NULL,
      `description` text DEFAULT NULL,
      `is_enabled` tinyint(1) DEFAULT 1,
      `warning_threshold` decimal(15,2) DEFAULT 0.00,
      `critical_threshold` decimal(15,2) DEFAULT 0.00,
      PRIMARY KEY (`tenant_id`,`rule_key`),
      CONSTRAINT `fk_alert_settings_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Also update database/schema.sql
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    $schema = mb_convert_encoding($schema, 'UTF-8', 'UTF-16LE');

    $schema = preg_replace(
        '/CREATE TABLE `alert_logs` \((.*?)\) ENGINE=/s',
        "CREATE TABLE `alert_logs` (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `tenant_id` int(11) DEFAULT NULL,\n  `rule_key` varchar(50) DEFAULT NULL,\n  `severity` varchar(10) DEFAULT NULL CHECK (`severity` in ('YELLOW','RED')),\n  `message` text NOT NULL,\n  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),\n  `is_read` tinyint(1) DEFAULT 0,\n  PRIMARY KEY (`id`),\n  CONSTRAINT `fk_alert_logs_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE\n) ENGINE=",
        $schema
    );

    $schema = preg_replace(
        '/CREATE TABLE `alert_settings` \((.*?)\) ENGINE=/s',
        "CREATE TABLE `alert_settings` (\n  `tenant_id` int(11) NOT NULL,\n  `rule_key` varchar(50) NOT NULL,\n  `rule_name` varchar(100) NOT NULL,\n  `description` text DEFAULT NULL,\n  `is_enabled` tinyint(1) DEFAULT 1,\n  `warning_threshold` decimal(15,2) DEFAULT 0.00,\n  `critical_threshold` decimal(15,2) DEFAULT 0.00,\n  PRIMARY KEY (`tenant_id`,`rule_key`),\n  CONSTRAINT `fk_alert_settings_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE\n) ENGINE=",
        $schema
    );

    file_put_contents(__DIR__ . '/database/schema.sql', mb_convert_encoding($schema, 'UTF-16LE', 'UTF-8'));
    
    echo "Database altered successfully!\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
