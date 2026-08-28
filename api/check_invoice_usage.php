<?php
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Invalid JSON payload');
    }

    $invoiceNo = $input['invoiceNo'] ?? '';
    if (empty($invoiceNo)) {
        throw new Exception('Invoice number is required');
    }

    // Default tenant to 1 if auth not fully implemented
    $tenant_id = 1;

    $usages = [];

    // 1. Check Single Receipts (سندات القبض الفردية)
    $stmt = $pdo->prepare("SELECT frontend_id, data FROM single_receipts WHERE tenant_id = ? AND is_deleted = 0");
    $stmt->execute([$tenant_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($row['data'], true);
        if (!$d) continue;
        
        $linked = false;
        if (($d['againstInvoice'] ?? '') == $invoiceNo) $linked = true;
        if (($d['invoiceNo'] ?? '') == $invoiceNo) $linked = true;
        if (($d['linkedInvoice'] ?? '') == $invoiceNo) $linked = true;
        
        if ($linked) {
            $usages[] = [
                'type' => 'سند قبض',
                'doc_no' => $row['frontend_id'],
                'date' => $d['date'] ?? '',
                'label' => 'سند قبض رقم ' . $row['frontend_id'] . (!empty($d['date']) ? ' بتاريخ ' . $d['date'] : '')
            ];
        }
    }

    // 2. Check Multi-Receipts (سندات القبض المجمعة)
    $stmt = $pdo->prepare("SELECT frontend_id, data FROM receipts WHERE tenant_id = ? AND is_deleted = 0");
    $stmt->execute([$tenant_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($row['data'], true);
        if (!$d) continue;
        
        $linked = false;
        if (isset($d['invoices']) && is_array($d['invoices'])) {
            foreach ($d['invoices'] as $inv) {
                if (($inv['invoiceNo'] ?? '') == $invoiceNo) $linked = true;
            }
        }
        
        if ($linked) {
            $usages[] = [
                'type' => 'سند قبض مجمع',
                'doc_no' => $row['frontend_id'],
                'date' => $d['date'] ?? '',
                'label' => 'سند قبض مجمع رقم ' . $row['frontend_id'] . (!empty($d['date']) ? ' بتاريخ ' . $d['date'] : '')
            ];
        }
    }

    // 3. Check Delivery Notes (سندات التسليم وأذونات الصرف)
    $stmt = $pdo->prepare("SELECT frontend_id, data FROM delivery_notes WHERE tenant_id = ? AND is_deleted = 0");
    $stmt->execute([$tenant_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($row['data'], true);
        if (!$d) continue;
        
        if (($d['invoiceNo'] ?? $d['sourceDocNo'] ?? '') == $invoiceNo) {
            $usages[] = [
                'type' => 'سند تسليم / إذن صرف',
                'doc_no' => $row['frontend_id'],
                'date' => $d['date'] ?? '',
                'label' => 'سند تسليم رقم ' . $row['frontend_id'] . (!empty($d['date']) ? ' بتاريخ ' . $d['date'] : '')
            ];
        }
    }

    // 4. Check Inventory Movements (الحركات المخزنية)
    $stmt = $pdo->prepare("SELECT frontend_id, data FROM inventory WHERE tenant_id = ? AND is_deleted = 0");
    $stmt->execute([$tenant_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($row['data'], true);
        if (!$d) continue;
        
        if (($d['invoiceNo'] ?? $d['sourceDocNo'] ?? $d['refNo'] ?? '') == $invoiceNo) {
            $usages[] = [
                'type' => 'حركة مخزنية',
                'doc_no' => $row['frontend_id'],
                'date' => $d['date'] ?? '',
                'label' => 'حركة مخزنية رقم ' . $row['frontend_id'] . (!empty($d['date']) ? ' بتاريخ ' . $d['date'] : '')
            ];
        }
    }

    // 5. Check GL Entries (القيود المحاسبية اليومية)
    $stmt = $pdo->prepare("SELECT frontend_id, data FROM gl_entries WHERE tenant_id = ? AND is_deleted = 0");
    $stmt->execute([$tenant_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($row['data'], true);
        if (!$d) continue;
        
        if (($d['invoiceNo'] ?? $d['sourceDocNo'] ?? $d['refNo'] ?? $d['reference'] ?? '') == $invoiceNo) {
            $usages[] = [
                'type' => 'قيد محاسبي',
                'doc_no' => $row['frontend_id'],
                'date' => $d['date'] ?? '',
                'label' => 'قيد محاسبي رقم ' . $row['frontend_id'] . (!empty($d['date']) ? ' بتاريخ ' . $d['date'] : '')
            ];
        }
    }

    // 6. Check Cheques (الشيكات المرتبطة)
    $stmt = $pdo->prepare("SELECT frontend_id, data FROM cheques WHERE tenant_id = ? AND is_deleted = 0");
    $stmt->execute([$tenant_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($row['data'], true);
        if (!$d) continue;
        
        if (($d['invoiceNo'] ?? $d['sourceDocNo'] ?? $d['refNo'] ?? '') == $invoiceNo) {
            $usages[] = [
                'type' => 'شيك بنكي',
                'doc_no' => $row['frontend_id'],
                'date' => $d['date'] ?? '',
                'label' => 'شيك بنكي رقم ' . $row['frontend_id'] . (!empty($d['date']) ? ' بتاريخ ' . $d['date'] : '')
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'is_used' => count($usages) > 0,
        'usage_count' => count($usages),
        'usages' => $usages
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
