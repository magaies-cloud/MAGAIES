<?php
require_once __DIR__ . '/api/db.php';

try {
    $pdo->exec("DROP TABLE IF EXISTS `transactions`");
    $pdo->exec("DROP TABLE IF EXISTS `checks_velocity`");
    $pdo->exec("DROP TABLE IF EXISTS `customer_credit_profiles`");
    $pdo->exec("DROP TABLE IF EXISTS `inventory_items`");

    if (file_exists(__DIR__ . '/api/financial_health.php')) {
        unlink(__DIR__ . '/api/financial_health.php');
    }
    if (file_exists(__DIR__ . '/api/strategic_reports.php')) {
        unlink(__DIR__ . '/api/strategic_reports.php');
    }

    // Update schema.sql by removing these tables using a regex
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    $schema = mb_convert_encoding($schema, 'UTF-8', 'UTF-16LE');

    $schema = preg_replace('/-- ----------------------------------------------------------------------\r?\n-- Table: `(transactions|checks_velocity|customer_credit_profiles|inventory_items)`\r?\n-- ----------------------------------------------------------------------\r?\nDROP TABLE IF EXISTS `\1`;\r?\nCREATE TABLE `\1` \(.*?;/s', '', $schema);

    file_put_contents(__DIR__ . '/database/schema.sql', mb_convert_encoding($schema, 'UTF-16LE', 'UTF-8'));
    
    echo "Ghost tables and orphaned APIs removed!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
