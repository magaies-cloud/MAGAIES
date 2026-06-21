<?php
require __DIR__ . '/../api/db.php';
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS otp_verifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            otp VARCHAR(10) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NOT NULL,
            is_used BOOLEAN DEFAULT 0,
            INDEX (email),
            INDEX (otp)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Table otp_verifications created successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

