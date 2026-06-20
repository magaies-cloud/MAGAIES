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

    echo "\nMigration completed successfully!\n";
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage();
}
