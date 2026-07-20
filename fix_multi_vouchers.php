<?php
require_once __DIR__ . '/api/db.php';

try {
    $pdo->beginTransaction();

    // Tables to process
    $tables = ['payments', 'receipts'];

    foreach ($tables as $table) {
        echo "Processing $table...\n";
        
        $stmt = $pdo->query("SELECT id, data, tenant_id FROM $table ORDER BY tenant_id ASC, id ASC");
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "Found " . count($records) . " records in $table\n";

        // Pass 1: Set to TEMP_ to avoid unique constraint
        echo "Pass 1: Setting to TEMP_ ids...\n";
        foreach ($records as $record) {
            $data = json_decode($record['data'], true);
            if (!$data) continue;
            // Only touch grouped vouchers
            if (!isset($data['isGrouped']) || $data['isGrouped'] !== true) continue;
            
            $data['id'] = 'TEMP_' . $record['id'];
            if (isset($data['voucherNo'])) $data['voucherNo'] = 'TEMP_' . $record['id'];
            
            $newData = json_encode($data, JSON_UNESCAPED_UNICODE);
            
            $upd = $pdo->prepare("UPDATE $table SET data = ? WHERE id = ?");
            $upd->execute([$newData, $record['id']]);
        }
        
        // Pass 2: Set sequentially
        echo "Pass 2: Setting to sequential M-format ids...\n";
        // To match JS, sequence resets per month per tenant
        $counters = []; 
        $updated = 0;

        foreach ($records as $record) {
            $data = json_decode($record['data'], true);
            if (!$data) continue;
            // Only touch grouped vouchers
            if (!isset($data['isGrouped']) || $data['isGrouped'] !== true) continue;

            $tenantId = $record['tenant_id'];
            
            $date = isset($data['date']) ? $data['date'] : '2026-07-01';
            $timestamp = strtotime($date);
            $yy = date('y', $timestamp);
            $mm = date('m', $timestamp);
            $prefix = "M{$yy}{$mm}";

            $counterKey = "{$tenantId}_{$yy}_{$mm}";
            if (!isset($counters[$counterKey])) $counters[$counterKey] = 1;

            $newId = $prefix . str_pad($counters[$counterKey], 4, '0', STR_PAD_LEFT);
            
            $data['id'] = $newId;
            $data['voucherNo'] = $newId;
            $newData = json_encode($data, JSON_UNESCAPED_UNICODE);

            $upd = $pdo->prepare("UPDATE $table SET data = ? WHERE id = ?");
            $upd->execute([$newData, $record['id']]);
            
            echo "Updated DB ID {$record['id']} for Tenant $tenantId to $newId\n";
            
            $counters[$counterKey]++;
            $updated++;
        }

        echo "Successfully updated $updated grouped vouchers in $table sequentially.\n";
    }

    $pdo->commit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage();
}
