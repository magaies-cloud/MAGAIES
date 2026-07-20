<?php
require_once __DIR__ . '/api/db.php';

$tables = ['single_payments', 'single_receipts', 'payments', 'receipts'];
foreach ($tables as $t) {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$t` WHERE data LIKE '%C-%' OR data LIKE '%TEMP_%'");
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "$t: {$res['count']} legacy/TEMP format rows remaining\n";
}
