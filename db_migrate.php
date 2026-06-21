<?php
require __DIR__ . "/api/db.php";

header('Content-Type: text/plain; charset=UTF-8');

try {
    // Add phone2
    try {
        $pdo->exec("ALTER TABLE tenants ADD COLUMN phone2 VARCHAR(255) NULL");
        echo "Added phone2 column.\n";
    } catch (PDOException $e) {
        echo "phone2 column might already exist or error: " . $e->getMessage() . "\n";
    }

    // Add receipt_path
    try {
        $pdo->exec("ALTER TABLE tenants ADD COLUMN receipt_path VARCHAR(255) NULL");
        echo "Added receipt_path column.\n";
    } catch (PDOException $e) {
        echo "receipt_path column might already exist or error: " . $e->getMessage() . "\n";
    }

    // Create safes table
    try {
        $pdo->exec("
        CREATE TABLE IF NOT EXISTS `safes` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `tenant_id` int(11) DEFAULT NULL,
          `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `tenant_id` (`tenant_id`),
          CONSTRAINT `safes_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
        echo "Ensured safes table exists.\n";
    } catch (PDOException $e) {
        echo "Error creating safes table: " . $e->getMessage() . "\n";
    }

    echo "\nMigration completed successfully!\n";
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage();
}
