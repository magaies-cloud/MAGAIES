<?php
require __DIR__ . '/api/db.php';

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
    echo "safes table created successfully!\n";
} catch (PDOException $e) {
    echo "Error creating safes table: " . $e->getMessage() . "\n";
}
