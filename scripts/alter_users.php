<?php
require __DIR__ . '/../api/db.php';
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN last_active DATETIME NULL");
    echo "Column last_active added successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
