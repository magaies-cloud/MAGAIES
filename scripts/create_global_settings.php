<?php
require __DIR__ . "/../api/db.php";

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS global_settings (
        setting_key VARCHAR(100) PRIMARY KEY,
        setting_value JSON,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );");

    // Insert default exchange rate setting if not exists
    $stmt = $pdo->prepare("INSERT IGNORE INTO global_settings (setting_key, setting_value) VALUES ('exchange_rate', '{\"rate\": 1.0, \"visible\": false}')");
    $stmt->execute();

    echo "global_settings table created successfully.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
?>
