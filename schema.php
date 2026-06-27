<?php
require 'api/db.php';
$stmt = $pdo->query('DESCRIBE suppliers');
print_r($stmt->fetchAll());
$stmt = $pdo->query('DESCRIBE partners');
print_r($stmt->fetchAll());
