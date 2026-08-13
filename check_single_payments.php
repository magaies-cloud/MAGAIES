<?php
require 'api/db.php';
$stmt = $pdo->query("SELECT data FROM single_payments WHERE tenant_id=2 LIMIT 3");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $r) {
    print_r(json_decode($r['data'], true));
}
