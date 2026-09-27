<?php
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

$query = trim($_GET['query'] ?? $_GET['q'] ?? $_POST['query'] ?? $_POST['q'] ?? '');
if (empty($query)) {
    echo json_encode(['success' => false, 'message' => 'يرجى إدخال رقم المستند أو الإذن']);
    exit;
}

// Clean query string from symbols like #, whitespace
$cleanQuery = preg_replace('/[#\s]/', '', $query);

$tenantId = null;
if (isset($_GET['tenant_id']) && is_numeric($_GET['tenant_id'])) {
    $tenantId = (int)$_GET['tenant_id'];
} elseif (!empty($_COOKIE['currentTenantId']) && is_numeric($_COOKIE['currentTenantId']) && empty($_GET['global'])) {
    $tenantId = (int)$_COOKIE['currentTenantId'];
}
$results = [];
$timeline = [];

$userMap = [];
try {
    $uStmt = $pdo->query("SELECT id, username, data FROM users");
    while ($ur = $uStmt->fetch(PDO::FETCH_ASSOC)) {
        $uData = json_decode($ur['data'] ?? '{}', true) ?: [];
        $uName = $uData['fullName'] ?? $uData['name'] ?? (!empty($ur['username']) ? $ur['username'] : ('مستخدم ' . $ur['id']));
        $userMap[(string)$ur['id']] = $uName;
        if (!empty($ur['username'])) $userMap[strtolower(trim($ur['username']))] = $uName;
        if (!empty($uData['id'])) $userMap[(string)$uData['id']] = $uName;
    }
} catch (Exception $e) {}

// Helper to safely extract JSON fields
function parseDocRow($row, $type, $label, $urlPattern) {
    global $userMap;
    $data = json_decode($row['data'] ?? '{}', true) ?: [];
    $docNo = $data['invoiceNo'] ?? $data['voucherNo'] ?? $data['receiptNo'] ?? $data['paymentNo'] ?? $data['jvNo'] ?? $row['invoice_no'] ?? $row['frontend_id'] ?? $data['id'] ?? $row['id'];
    $creatorName = $data['createdByName'] ?? $data['createdByUsername'] ?? $row['created_by_val'] ?? $data['createdBy'] ?? 'غير محدد';
    $creatorId = $data['createdBy'] ?? $row['created_by_val'] ?? '';
    
    if (isset($userMap[(string)$creatorName])) {
        $creatorName = $userMap[(string)$creatorName];
    } elseif (isset($userMap[(string)$creatorId])) {
        $creatorName = $userMap[(string)$creatorId];
    }
    
    $updatedName = $data['updatedByName'] ?? $data['updatedByUsername'] ?? $data['updatedBy'] ?? null;
    if ($updatedName && isset($userMap[(string)$updatedName])) {
        $updatedName = $userMap[(string)$updatedName];
    }
    $updatedAt = $data['updatedAt'] ?? $row['updated_at'] ?? null;
    $date = $data['date'] ?? $data['date_val'] ?? substr($row['created_at'] ?? '', 0, 10);
    $time = $data['time'] ?? substr($row['created_at'] ?? '', 11, 8);
    $amount = $data['grandTotal'] ?? $data['totalAmount'] ?? $data['amountLocal'] ?? $data['amount'] ?? 0;
    $currency = $data['currency'] ?? 'SDG';
    $party = $data['customerName'] ?? $data['supplierName'] ?? $data['partnerName'] ?? $data['toAccount'] ?? $data['beneficiary'] ?? '—';
    $status = $data['status'] ?? $data['deliveryStatus'] ?? 'مكتمل';
    
    return [
        'type' => $type,
        'label' => $label,
        'docNo' => $docNo,
        'tenantId' => $row['tenant_id'] ?? null,
        'creatorName' => $creatorName,
        'creatorId' => $creatorId,
        'updatedName' => $updatedName,
        'updatedAt' => $updatedAt,
        'date' => $date,
        'time' => $time,
        'createdAt' => $row['created_at'] ?? '',
        'amount' => $amount,
        'currency' => $currency,
        'party' => $party,
        'status' => $status,
        'deliveredByName' => $data['deliveredByName'] ?? null,
        'deliveredAt' => $data['deliveryDate'] ?? null,
        'url' => str_replace('{id}', urlencode($docNo), $urlPattern),
        'raw' => $data
    ];
}

try {
    $qLike = "%$cleanQuery%";
    $numId = is_numeric($cleanQuery) ? (int)$cleanQuery : 0;
    $tClause = $tenantId !== null ? "tenant_id = $tenantId AND " : "";

    // 1. Sales Invoices
    $stmt = $pdo->prepare("SELECT * FROM sales_invoices WHERE $tClause (invoice_no LIKE ? OR JSON_EXTRACT(data, '$.invoiceNo') LIKE ? OR id = ? OR frontend_id LIKE ?) ORDER BY id DESC LIMIT 5");
    $stmt->execute([$qLike, $qLike, $numId, $qLike]);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = parseDocRow($r, 'sales', 'فاتورة مبيعات', 'direct-sales.php?id={id}');
    }

    // 2. Purchase Invoices
    $stmt = $pdo->prepare("SELECT * FROM purchase_invoices WHERE $tClause (invoice_no LIKE ? OR JSON_EXTRACT(data, '$.invoiceNo') LIKE ? OR id = ? OR frontend_id LIKE ?) ORDER BY id DESC LIMIT 5");
    $stmt->execute([$qLike, $qLike, $numId, $qLike]);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = parseDocRow($r, 'purchase', 'فاتورة مشتريات', 'purchase.php?id={id}');
    }

    // 3. Single Payments (صرف نقدي)
    $stmt = $pdo->prepare("SELECT * FROM single_payments WHERE $tClause (JSON_EXTRACT(data, '$.id') LIKE ? OR JSON_EXTRACT(data, '$.paymentNo') LIKE ? OR JSON_EXTRACT(data, '$.voucherNo') LIKE ? OR id = ? OR frontend_id LIKE ?) ORDER BY id DESC LIMIT 5");
    $stmt->execute([$qLike, $qLike, $qLike, $numId, $qLike]);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = parseDocRow($r, 'single_payment', 'سند صرف نقدي', 'single-cash-payment.php?id={id}');
    }

    // 4. Single Receipts (قبض نقدي)
    $stmt = $pdo->prepare("SELECT * FROM single_receipts WHERE $tClause (JSON_EXTRACT(data, '$.id') LIKE ? OR JSON_EXTRACT(data, '$.receiptNo') LIKE ? OR JSON_EXTRACT(data, '$.voucherNo') LIKE ? OR id = ? OR frontend_id LIKE ?) ORDER BY id DESC LIMIT 5");
    $stmt->execute([$qLike, $qLike, $qLike, $numId, $qLike]);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = parseDocRow($r, 'single_receipt', 'سند قبض نقدي', 'single-cash-payment.php?type=receipt&id={id}');
    }

    // 5. Multi Payments / Receipts
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE $tClause (JSON_EXTRACT(data, '$.voucherNo') LIKE ? OR JSON_EXTRACT(data, '$.id') LIKE ? OR id = ? OR frontend_id LIKE ?) ORDER BY id DESC LIMIT 5");
    $stmt->execute([$qLike, $qLike, $numId, $qLike]);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = parseDocRow($r, 'multi_payment', 'حركة صرف مجمعة', 'multi-cash-payment.php?id={id}');
    }

    $stmt = $pdo->prepare("SELECT * FROM receipts WHERE $tClause (JSON_EXTRACT(data, '$.voucherNo') LIKE ? OR JSON_EXTRACT(data, '$.id') LIKE ? OR id = ? OR frontend_id LIKE ?) ORDER BY id DESC LIMIT 5");
    $stmt->execute([$qLike, $qLike, $numId, $qLike]);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = parseDocRow($r, 'multi_receipt', 'حركة قبض مجمعة', 'multi-cash-payment.php?type=receipt&id={id}');
    }

    // 6. Journal Vouchers
    $stmt = $pdo->prepare("SELECT * FROM journal_vouchers WHERE $tClause (JSON_EXTRACT(data, '$.id') LIKE ? OR JSON_EXTRACT(data, '$.voucherNo') LIKE ? OR JSON_EXTRACT(data, '$.jvNo') LIKE ? OR id = ? OR frontend_id LIKE ?) ORDER BY id DESC LIMIT 5");
    $stmt->execute([$qLike, $qLike, $qLike, $numId, $qLike]);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = parseDocRow($r, 'journal_voucher', 'قيد يومية', 'journal-voucher.php?id={id}');
    }

    // 7. Audit Logs matching this query
    $stmt = $pdo->prepare("SELECT * FROM audit_logs WHERE $tClause (data LIKE ? OR frontend_id LIKE ?) ORDER BY id DESC LIMIT 20");
    $stmt->execute([$qLike, $qLike]);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $logData = json_decode($r['data'] ?? '{}', true) ?: [];
        $timeline[] = [
            'id' => $r['id'],
            'userName' => $logData['userName'] ?? $logData['userId'] ?? 'مستخدم',
            'actionType' => $logData['actionType'] ?? 'حركة',
            'module' => $logData['module'] ?? 'عام',
            'description' => $logData['description'] ?? '',
            'timestamp' => $logData['timestamp'] ?? strtotime($r['created_at']) * 1000,
            'dateFormatted' => !empty($logData['timestamp']) ? date('Y-m-d H:i', (int)($logData['timestamp'] / 1000)) : $r['created_at']
        ];
    }

    echo json_encode([
        'success' => true,
        'query' => $query,
        'results_count' => count($results),
        'results' => $results,
        'audit_timeline_count' => count($timeline),
        'audit_timeline' => $timeline
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    error_log("Error in track_document: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
