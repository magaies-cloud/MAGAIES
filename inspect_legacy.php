<?php
require_once __DIR__ . '/api/db.php';

$tables = ['single_payments', 'payments'];
foreach ($tables as $t) {
    echo "--- Table: $t ---\n";
    $stmt = $pdo->query("SELECT id, data FROM `$t` WHERE data LIKE '%C-%' OR data LIKE '%TEMP_%'");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $data = json_decode($r['data'], true);
        $vid = $data['id'] ?? 'missing';
        $vno = $data['voucherNo'] ?? 'missing';
        echo "DB ID: {$r['id']} | JSON ID: $vid | JSON VoucherNo: $vno\n";
    }
}
