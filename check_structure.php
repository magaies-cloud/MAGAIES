<?php
require 'api/db.php';
$tables = ['items', 'customers', 'suppliers', 'accounts'];
foreach ($tables as $t) {
    $stmt = $pdo->query("SHOW CREATE TABLE $t");
    echo $stmt->fetch(PDO::FETCH_NUM)[1] . "\n\n";
}
