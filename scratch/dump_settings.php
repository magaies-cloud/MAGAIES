<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=elsajana2_local", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT setting_key, setting_value FROM global_settings WHERE setting_key LIKE '%balances_report%'");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($results) . " rows:\n";
    foreach ($results as $row) {
        echo "- " . $row['setting_key'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
