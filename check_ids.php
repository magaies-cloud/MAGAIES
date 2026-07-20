<?php
require 'db_bridge.php';
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT data FROM single_payments WHERE tenant_id=2");
$arr = [];
while($row=$stmt->fetch()){
    $d = json_decode($row['data'], true);
    if(isset($d['id'])) $arr[] = $d['id'];
}
print_r($arr);
