<?php
require_once __DIR__ . '/../api/db.php';
global $pdo;

$tables = ['customers', 'suppliers', 'items', 'accounts', 'banks', 'sales_invoices', 'purchase_invoices'];
$results = [];

foreach ($tables as $t) {
    try {
        $stmt = $pdo->query("SELECT data FROM `$t` LIMIT 1");
        $row = $stmt->fetchColumn();
        if ($row) {
            $results[$t] = json_decode($row, true);
        } else {
            $results[$t] = 'NO DATA';
        }
    } catch (Exception $e) {
        $results[$t] = 'ERROR: ' . $e->getMessage();
    }
}

header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
