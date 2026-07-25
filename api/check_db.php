<?php
require 'db.php';
$stmt = $pdo->query("SELECT * FROM global_settings");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
