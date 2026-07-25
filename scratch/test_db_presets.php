<?php
require 'api/db.php';
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Tables:\n" . implode("\n", $tables);

if (in_array('global_settings', $tables)) {
    echo "\n\nColumns in global_settings:\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM global_settings");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
if (in_array('settings', $tables)) {
    echo "\n\nColumns in settings:\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM settings");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
?>
