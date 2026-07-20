<?php
require_once __DIR__ . '/../api/db.php';

try {
    $pdo->beginTransaction();

    // 1. Process Single Tables
    $singleConfigs = [
        'single_payments' => 'E',
        'single_receipts' => 'E'
    ];

    foreach ($singleConfigs as $table => $charPrefix) {
        echo "Processing single table: $table...\n";
        $stmt = $pdo->query("SELECT id, data, tenant_id FROM `$table` ORDER BY tenant_id ASC, id ASC");
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pass 1: Set IDs to TEMP_ to avoid conflicts
        foreach ($records as $record) {
            $data = json_decode($record['data'], true);
            if (!$data) continue;
            $data['id'] = 'TEMP_' . $record['id'];
            if (isset($data['voucherNo'])) $data['voucherNo'] = 'TEMP_' . $record['id'];
            $newData = json_encode($data, JSON_UNESCAPED_UNICODE);
            $upd = $pdo->prepare("UPDATE `$table` SET data = ? WHERE id = ?");
            $upd->execute([$newData, $record['id']]);
        }

        // Pass 2: Sequence by tenant + month
        $counters = [];
        foreach ($records as $record) {
            $data = json_decode($record['data'], true);
            if (!$data) continue;

            $tenantId = $record['tenant_id'];
            $date = $data['date'] ?? '2026-07-01';
            $timestamp = strtotime($date);
            $yy = date('y', $timestamp);
            $mm = date('m', $timestamp);
            
            $prefix = "{$charPrefix}{$yy}{$mm}";
            $counterKey = "{$tenantId}_{$yy}_{$mm}";
            if (!isset($counters[$counterKey])) {
                $counters[$counterKey] = 1;
            }

            $newId = $prefix . str_pad($counters[$counterKey], 4, '0', STR_PAD_LEFT);
            $data['id'] = $newId;
            if (isset($data['voucherNo'])) $data['voucherNo'] = $newId;

            $newData = json_encode($data, JSON_UNESCAPED_UNICODE);
            $upd = $pdo->prepare("UPDATE `$table` SET data = ? WHERE id = ?");
            $upd->execute([$newData, $record['id']]);
            $counters[$counterKey]++;
        }
        echo "Finished $table.\n";
    }

    // 2. Process Grouped Tables
    $groupedConfigs = [
        'payments' => 'M',
        'receipts' => 'M'
    ];

    foreach ($groupedConfigs as $table => $charPrefix) {
        echo "Processing grouped table: $table...\n";
        $stmt = $pdo->query("SELECT id, data, tenant_id FROM `$table` ORDER BY tenant_id ASC, id ASC");
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pass 1: Set IDs to TEMP_ to avoid conflicts
        foreach ($records as $record) {
            $data = json_decode($record['data'], true);
            if (!$data) continue;
            $data['id'] = 'TEMP_' . $record['id'];
            if (isset($data['voucherNo'])) $data['voucherNo'] = 'TEMP_' . $record['id'];
            $newData = json_encode($data, JSON_UNESCAPED_UNICODE);
            $upd = $pdo->prepare("UPDATE `$table` SET data = ? WHERE id = ?");
            $upd->execute([$newData, $record['id']]);
        }

        // Pass 2: Sequence by tenant + month
        $counters = [];
        foreach ($records as $record) {
            $data = json_decode($record['data'], true);
            if (!$data) continue;

            $tenantId = $record['tenant_id'];
            $date = $data['date'] ?? '2026-07-01';
            $timestamp = strtotime($date);
            $yy = date('y', $timestamp);
            $mm = date('m', $timestamp);
            
            $prefix = "{$charPrefix}{$yy}{$mm}";
            $counterKey = "{$tenantId}_{$yy}_{$mm}";
            if (!isset($counters[$counterKey])) {
                $counters[$counterKey] = 1;
            }

            $newId = $prefix . str_pad($counters[$counterKey], 4, '0', STR_PAD_LEFT);
            $data['id'] = $newId;
            if (isset($data['voucherNo'])) $data['voucherNo'] = $newId;

            $newData = json_encode($data, JSON_UNESCAPED_UNICODE);
            $upd = $pdo->prepare("UPDATE `$table` SET data = ? WHERE id = ?");
            $upd->execute([$newData, $record['id']]);
            $counters[$counterKey]++;
        }
        echo "Finished $table.\n";
    }

    $pdo->commit();
    echo "All tables fixed successfully for all tenants!\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}
