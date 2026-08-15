<?php
require 'api/db.php';
$stmt = $pdo->query('SELECT setting_key, setting_value FROM global_settings');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['setting_key'] . "\n";
}
