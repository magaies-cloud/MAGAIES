<?php
require_once __DIR__ . '/api/db.php';

try {
    $stmt = $pdo->query("SELECT id, data, tenant_id FROM single_payments ORDER BY tenant_id ASC, id ASC");
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($payments) . " payments\n";

    $pdo->beginTransaction();

    // Pass 1: Set to TEMP_ to avoid virtual column unique constraint
    echo "Pass 1: Setting to TEMP_ ids...\n";
    foreach ($payments as $payment) {
        $data = json_decode($payment['data'], true);
        if (!$data) continue;
        $data['id'] = 'TEMP_' . $payment['id'];
        $newData = json_encode($data, JSON_UNESCAPED_UNICODE);
        
        $upd = $pdo->prepare("UPDATE single_payments SET data = ? WHERE id = ?");
        $upd->execute([$newData, $payment['id']]);
    }
    
    // Pass 2: Set sequentially
    echo "Pass 2: Setting to sequential E-format ids...\n";
    $counters = [];
    $updated = 0;

    foreach ($payments as $payment) {
        $tenantId = $payment['tenant_id'];
        if (!isset($counters[$tenantId])) $counters[$tenantId] = 1;

        $data = json_decode($payment['data'], true);
        if (!$data) continue;

        $date = isset($data['date']) ? $data['date'] : '2026-07-01';
        $timestamp = strtotime($date);
        $yy = date('y', $timestamp);
        $mm = date('m', $timestamp);
        $prefix = "E{$yy}{$mm}";

        $newId = $prefix . str_pad($counters[$tenantId], 4, '0', STR_PAD_LEFT);
        
        $data['id'] = $newId;
        $newData = json_encode($data, JSON_UNESCAPED_UNICODE);

        $upd = $pdo->prepare("UPDATE single_payments SET data = ? WHERE id = ?");
        $upd->execute([$newData, $payment['id']]);
        
        echo "Updated DB ID {$payment['id']} for Tenant $tenantId to $newId\n";
        
        $counters[$tenantId]++;
        $updated++;
    }

    $pdo->commit();
    echo "Successfully updated $updated payments sequentially.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage();
}
