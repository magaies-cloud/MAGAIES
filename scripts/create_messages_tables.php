<?php
require __DIR__ . "/../api/db.php";

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS broadcast_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        message TEXT,
        target_tenants JSON,
        show_once BOOLEAN DEFAULT TRUE,
        theme VARCHAR(50) DEFAULT 'info',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS user_message_views (
        user_id INT,
        message_id INT,
        viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, message_id)
    );");

    echo "Tables created successfully.\n";
} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage() . "\n";
}
?>
