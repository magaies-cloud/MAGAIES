<?php
require_once __DIR__ . '/db.php';
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

    $tenant_id = resolveCurrentTenantId($pdo, $input);
    $usages = [];

    // 1. Check Single Receipts (سندات القبض الفردية)
    $stmt = $pdo->prepare("SELECT frontend_id, data FROM single_receipts WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmt->execute([$tenant_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($row['data'], true);
        if (!$d || !empty($d['deleted'])) continue;
        
        $linked = false;
        if (($d['againstInvoice'] ?? '') == $invoiceNo) $linked = true;
        if (($d['invoiceNo'] ?? '') == $invoiceNo) $linked = true;
        if (($d['linkedInvoice'] ?? '') == $invoiceNo) $linked = true;
        
        if ($linked) {
            $docNo = $row['frontend_id'] ?: ($d['voucherNo'] ?? $d['id'] ?? '');
            $usages[] = [
                'type' => 'سند قبض',
                'doc_no' => $docNo,
                'date' => $d['date'] ?? '',
                'label' => 'سند قبض رقم ' . $docNo . (!empty($d['date']) ? ' بتاريخ ' . $d['date'] : '')
            ];
        }
    }

    // 1B. Check Single Payments (سندات الصرف/الحركة المالية الفردية)
    $stmt = $pdo->prepare("SELECT frontend_id, data FROM single_payments WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmt->execute([$tenant_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($row['data'], true);
        if (!$d || !empty($d['deleted'])) continue;
        
        $linked = false;
        if (($d['againstInvoice'] ?? '') == $invoiceNo) $linked = true;
        if (($d['invoiceNo'] ?? '') == $invoiceNo) $linked = true;
        if (($d['linkedInvoice'] ?? '') == $invoiceNo) $linked = true;
        
        if ($linked) {
            $docNo = $row['frontend_id'] ?: ($d['voucherNo'] ?? $d['id'] ?? '');
            $usages[] = [
                'type' => 'سند مالي',
                'doc_no' => $docNo,
                'date' => $d['date'] ?? '',
                'label' => 'سند مالي رقم ' . $docNo . (!empty($d['date']) ? ' بتاريخ ' . $d['date'] : '')
            ];
        }
    }

    // 2. Check Multi-Receipts (سندات القبض المجمعة)
    $stmt = $pdo->prepare("SELECT frontend_id, data FROM receipts WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmt->execute([$tenant_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($row['data'], true);
        if (!$d || !empty($d['deleted'])) continue;
        
        $linked = false;
        if (($d['againstInvoice'] ?? $d['invoiceNo'] ?? '') == $invoiceNo) $linked = true;
        if (isset($d['invoices']) && is_array($d['invoices'])) {
            foreach ($d['invoices'] as $inv) {
                if (($inv['invoiceNo'] ?? '') == $invoiceNo) $linked = true;
            }
        }
        
        if ($linked) {
            $docNo = $row['frontend_id'] ?: ($d['voucherNo'] ?? $d['id'] ?? '');
            $usages[] = [
                'type' => 'سند قبض مجمع',
                'doc_no' => $docNo,
                'date' => $d['date'] ?? '',
                'label' => 'سند قبض مجمع رقم ' . $docNo . (!empty($d['date']) ? ' بتاريخ ' . $d['date'] : '')
            ];
        }
    }

    // 3. Check Delivery Notes (سندات التسليم وأذونات الصرف)
    $stmt = $pdo->prepare("SELECT frontend_id, data FROM delivery_notes WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmt->execute([$tenant_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($row['data'], true);
        if (!$d || !empty($d['deleted'])) continue;
        
        if (($d['invoiceNo'] ?? $d['sourceDocNo'] ?? '') == $invoiceNo) {
            $docNo = $row['frontend_id'] ?: ($d['deliveryNo'] ?? $d['id'] ?? '');
            $usages[] = [
                'type' => 'سند تسليم / إذن صرف',
                'doc_no' => $docNo,
                'date' => $d['date'] ?? '',
                'label' => 'سند تسليم رقم ' . $docNo . (!empty($d['date']) ? ' بتاريخ ' . $d['date'] : '')
            ];
        }
    }

    // 4. Check Inventory Movements (الحركات المخزنية)
    $stmt = $pdo->prepare("SELECT frontend_id, data FROM inventory WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmt->execute([$tenant_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($row['data'], true);
        if (!$d || !empty($d['deleted'])) continue;
        
        if (($d['invoiceNo'] ?? $d['sourceDocNo'] ?? $d['refNo'] ?? '') == $invoiceNo) {
            $docNo = $row['frontend_id'] ?: ($d['docNo'] ?? $d['id'] ?? '');
            $usages[] = [
                'type' => 'حركة مخزنية',
                'doc_no' => $docNo,
                'date' => $d['date'] ?? '',
                'label' => 'حركة مخزنية رقم ' . $docNo . (!empty($d['date']) ? ' بتاريخ ' . $d['date'] : '')
            ];
        }
    }

    // 5. Check GL Entries (القيود المحاسبية اليومية)
    $stmt = $pdo->prepare("SELECT frontend_id, data FROM gl_entries WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmt->execute([$tenant_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($row['data'], true);
        if (!$d || !empty($d['deleted'])) continue;
        
        if (($d['invoiceNo'] ?? $d['sourceDocNo'] ?? $d['refNo'] ?? $d['reference'] ?? '') == $invoiceNo) {
            $docNo = $row['frontend_id'] ?: ($d['voucherNo'] ?? $d['id'] ?? '');
            $usages[] = [
                'type' => 'قيد محاسبي',
                'doc_no' => $docNo,
                'date' => $d['date'] ?? '',
                'label' => 'قيد محاسبي رقم ' . $docNo . (!empty($d['date']) ? ' بتاريخ ' . $d['date'] : '')
            ];
        }
    }

    // 6. Check Checks (الشيكات المرتبطة)
    try {
        $stmt = $pdo->prepare("SELECT frontend_id, data FROM `checks` WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
        $stmt->execute([$tenant_id]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $d = json_decode($row['data'], true);
            if (!$d || !empty($d['deleted'])) continue;
            
            if (($d['invoiceNo'] ?? $d['sourceDocNo'] ?? $d['refNo'] ?? '') == $invoiceNo) {
                $docNo = $row['frontend_id'] ?: ($d['checkNo'] ?? $d['id'] ?? '');
                $usages[] = [
                    'type' => 'شيك بنكي',
                    'doc_no' => $docNo,
                    'date' => $d['date'] ?? '',
                    'label' => 'شيك بنكي رقم ' . $docNo . (!empty($d['date']) ? ' بتاريخ ' . $d['date'] : '')
                ];
            }
        }
    } catch (Throwable $e) {}

    echo json_encode([
        'success' => true,
        'is_used' => count($usages) > 0,
        'usage_count' => count($usages),
        'usages' => $usages
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
