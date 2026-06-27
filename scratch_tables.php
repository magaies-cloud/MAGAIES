<?php
require 'api/db.php';
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);

foreach ($tables as $table) {
    if (in_array(strtolower($table), ['items', 'products', 'inventory'])) {
        echo "Table: $table\n";
        $row = $pdo->query("SELECT * FROM $table LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        print_r($row);
    }
}
