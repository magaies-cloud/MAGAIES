<?php
require __DIR__ . "/api/db.php";
$stmt = $pdo->query("SELECT id, JSON_CONTAINS(target_tenants, '\"all\"') as c1, JSON_CONTAINS(target_tenants, '\"1\"') as c2 FROM broadcast_messages");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
