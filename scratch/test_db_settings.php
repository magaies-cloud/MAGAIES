<?php
try {
    $dbName = 'elsajana2_local';
    try {
        $pdo = new PDO("mysql:host=127.0.0.1;dbname=$dbName", "root", "");
    } catch(Exception $e) {
        $pdo = new PDO("mysql:host=127.0.0.1;dbname=magaees", "root", "");
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE TABLE IF NOT EXISTS global_settings (
        setting_key varchar(100) NOT NULL PRIMARY KEY,
        setting_value json DEFAULT NULL,
        updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    echo "✅ Table global_settings is ready in MySQL!\n";

    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM global_settings WHERE setting_key LIKE 'layout_%'");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Stored layouts count: " . count($rows) . "\n";
    foreach ($rows as $r) {
        echo "Key: " . $r['setting_key'] . " | Length: " . strlen($r['setting_value']) . "\n";
    }
} catch (Exception $e) {
    echo "❌ DB Error: " . $e->getMessage() . "\n";
}
