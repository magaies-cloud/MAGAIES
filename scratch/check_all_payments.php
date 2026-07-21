<?php
require_once __DIR__ . '/../api/db.php';
$stmt = $pdo->query('SELECT id, data FROM payments');
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($records as $rec) {
    $data = json_decode($rec['data'], true);
    $isGrouped = isset($data['isGrouped']) ? ($data['isGrouped'] ? 'true' : 'false') : 'missing';
    $vno = $data['voucherNo'] ?? 'missing';
    echo "ID: {$rec['id']} | voucherNo: $vno | isGrouped: $isGrouped\n";
}
