<?php
require 'db_bridge.php';
$pdo = getDBConnection();
$GLOBAL_TENANT_ID = 2; // Assuming the user is tenant 2
$stmt = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ? ORDER BY id ASC");
$stmt->execute([$GLOBAL_TENANT_ID]);
$arr = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $arr[] = json_decode($row['data'], true);
}
echo json_encode($arr);
