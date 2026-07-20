<?php
require 'db_bridge.php';
$GLOBAL_TENANT_ID = 2;
$pdo = getDBConnection();
$currentDate = date('Y-m-d');
$y = substr(date('Y', strtotime($currentDate)), 2, 2);
$m = date('m', strtotime($currentDate));
$prefix = 'E' . $y . $m;
$maxSeq = 0;

$stmt = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ?");
$stmt->execute([$GLOBAL_TENANT_ID]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $d = json_decode($row['data'], true);
    $id = isset($d['id']) ? (string)$d['id'] : '';
    if (strpos($id, $prefix) === 0) {
        $seq = (int)substr($id, strlen($prefix));
        if ($seq > $maxSeq) $maxSeq = $seq;
    }
}
$voucher_no = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
echo $voucher_no;
