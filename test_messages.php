<?php
require __DIR__ . "/api/db.php";

$userId = 1;
$tenant_id = 1;

$stmt = $pdo->prepare("
    SELECT * FROM broadcast_messages 
    WHERE is_active = 1 
    AND (
        JSON_CONTAINS(target_tenants, '\"all\"') 
        OR JSON_CONTAINS(target_tenants, ?)
    )
    AND id NOT IN (
        SELECT message_id FROM user_message_views WHERE user_id = ?
    )
    ORDER BY created_at ASC
");
$stmt->execute([(string)$tenant_id, $userId]);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
