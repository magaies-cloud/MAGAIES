<?php
require 'api/db.php';
$stmt = $pdo->query('SELECT data FROM suppliers LIMIT 5');
print_r($stmt->fetchAll());
