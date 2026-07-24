<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=elsajana2_local', 'root', '');

echo "=== GLOBAL SETTINGS ===\n";
$stmt = $pdo->query("SELECT * FROM global_settings");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Key: " . $row['setting_key'] . "\nValue: " . $row['setting_value'] . "\n---\n";
}

echo "=== SETTINGS ===\n";
$stmt = $pdo->query("SELECT * FROM settings WHERE setting_key LIKE '%ad%' OR setting_key LIKE '%banner%' OR setting_key LIKE '%saas%'");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Key: " . $row['setting_key'] . "\nValue: " . $row['setting_value'] . "\n---\n";
}
