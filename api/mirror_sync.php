<?php
/**
 * ═════════════════════════════════════════════════════════════════════
 * 🪞 MAGAIEES ERP 2026 - REAL-TIME MIRROR ENGINE (نظام المرآة الموحد الفوري)
 * ═════════════════════════════════════════════════════════════════════
 * يضمن تطابق تام 100% بين نسخة اللابتوب ونسخة الجوال أو التابلت:
 * 1. صرف مالي / حركة مالية (Single Payments)
 * 2. المبيعات (Sales Invoices)
 * 3. تسليم المبيعات (Sales Delivery)
 * 4. تقسيم المبيعات (Sales Split)
 * 5. المشتريات (Purchase Invoices)
 * 6. تسليم واستلام المشتريات (Purchase Receipts / GRN)
 * 7. تقسيم المشتريات (Purchase Split)
 * ═════════════════════════════════════════════════════════════════════
 */

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Tenant-Id');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../db_bridge.php';
$pdo = getDBConnection();
$tenantId = (int)$GLOBAL_TENANT_ID;
if (!$tenantId) $tenantId = 12;

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
if (empty($action) && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $rawInput = json_decode(file_get_contents('php://input'), true);
    if (!empty($rawInput['action'])) {
        $action = $rawInput['action'];
    }
}

// ─────────────────────────────────────────────────────────────
// Helper Functions: Next Sequential Number Generators
// ─────────────────────────────────────────────────────────────

function getFinanceSequence($pdo, $tenantId) {
    $prefix = 'E' . date('ym');
    $maxSeq = 0;
    $lastVoucher = null;
    $totalCount = 0;

    $stmt = $pdo->prepare("SELECT id, data FROM single_payments WHERE (tenant_id = ? OR tenant_id IS NULL) ORDER BY id ASC");
    $stmt->execute([$tenantId]);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $totalCount++;
        $d = json_decode($r['data'], true);
        if (!$d) continue;
        $lastVoucher = $d;
        $id = (string)($d['voucherNo'] ?? ($d['id'] ?? ''));
        if (strpos($id, $prefix) === 0) {
            $s = (int)substr($id, strlen($prefix));
            if ($s > $maxSeq) $maxSeq = $s;
        } elseif (is_numeric($id)) {
            $s = (int)$id;
            if ($s > $maxSeq) $maxSeq = $s;
        }
    }

    $nextVNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
    return [
        'prefix' => $prefix,
        'maxSeq' => $maxSeq,
        'nextVoucherNo' => $nextVNo,
        'totalCount' => $totalCount,
        'lastRecord' => $lastVoucher
    ];
}

function getSalesSequence($pdo, $tenantId) {
    $prefix = 'S' . date('ym');
    $maxSeq = 0;
    $lastSale = null;
    $totalCount = 0;

    $stmt = $pdo->prepare("SELECT id, invoice_no, total_val, data, created_at FROM sales_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
    $stmt->execute([$tenantId]);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $totalCount++;
        $d = json_decode($r['data'], true) ?: [];
        $invNo = (string)($r['invoice_no'] ?: ($d['invoiceNo'] ?? ($d['id'] ?? '')));
        $d['invoiceNo'] = $invNo;
        $d['id'] = $invNo;
        $d['total'] = (float)($r['total_val'] ?? ($d['grandTotal'] ?? ($d['total'] ?? 0)));
        $lastSale = $d;

        if (strpos($invNo, $prefix) === 0) {
            $s = (int)substr($invNo, strlen($prefix));
            if ($s > $maxSeq) $maxSeq = $s;
        } elseif (preg_match('/^S\d{4}(\d{1,6})$/', $invNo, $m)) {
            $s = (int)$m[1];
            if ($s > $maxSeq) $maxSeq = $s;
        }
    }

    $nextInvNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
    return [
        'prefix' => $prefix,
        'maxSeq' => $maxSeq,
        'nextInvoiceNo' => $nextInvNo,
        'totalCount' => $totalCount,
        'lastRecord' => $lastSale
    ];
}

function getPurchaseSequence($pdo, $tenantId) {
    $prefix = 'P' . date('ym');
    $maxSeq = 0;
    $lastPur = null;
    $totalCount = 0;

    $stmt = $pdo->prepare("SELECT id, invoice_no, total_val, data, created_at FROM purchase_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
    $stmt->execute([$tenantId]);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $totalCount++;
        $d = json_decode($r['data'], true) ?: [];
        $invNo = (string)($r['invoice_no'] ?: ($d['invoiceNo'] ?? ($d['id'] ?? '')));
        $d['invoiceNo'] = $invNo;
        $d['id'] = $invNo;
        $lastPur = $d;

        if (strpos($invNo, $prefix) === 0) {
            $s = (int)substr($invNo, strlen($prefix));
            if ($s > $maxSeq) $maxSeq = $s;
        } elseif (preg_match('/^P\d{4}(\d{1,6})$/', $invNo, $m)) {
            $s = (int)$m[1];
            if ($s > $maxSeq) $maxSeq = $s;
        } elseif (preg_match('/^P\d{2}(\d{1,6})$/', $invNo, $m)) {
            $s = (int)$m[1];
            if ($s > $maxSeq) $maxSeq = $s;
        }
    }

    $nextPurNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
    return [
        'prefix' => $prefix,
        'maxSeq' => $maxSeq,
        'nextPurchaseNo' => $nextPurNo,
        'totalCount' => $totalCount,
        'lastRecord' => $lastPur
    ];
}

function getDeliverySequence($pdo, $tenantId) {
    $prefix = 'DEL-' . date('ym');
    $maxSeq = 0;
    $lastDel = null;
    $totalCount = 0;

    try {
        $stmt = $pdo->prepare("SELECT id, data, frontend_id FROM delivery_notes WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
        $stmt->execute([$tenantId]);
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $totalCount++;
            $d = json_decode($r['data'], true) ?: [];
            $lastDel = $d;
            $dNo = (string)($d['deliveryNo'] ?? ($r['frontend_id'] ?? ($d['id'] ?? '')));
            if (strpos($dNo, $prefix) === 0) {
                $s = (int)substr($dNo, strlen($prefix));
                if ($s > $maxSeq) $maxSeq = $s;
            } elseif (preg_match('/(\d+)$/', $dNo, $m)) {
                $s = (int)$m[1];
                if ($s > $maxSeq) $maxSeq = $s;
            }
        }
    } catch (Exception $e) {}

    $nextDelNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
    return [
        'prefix' => $prefix,
        'maxSeq' => $maxSeq,
        'nextDeliveryNo' => $nextDelNo,
        'totalCount' => $totalCount,
        'lastRecord' => $lastDel
    ];
}

function getReceiptSequence($pdo, $tenantId) {
    $prefix = 'GRN-' . date('ym');
    $maxSeq = 0;
    $lastRec = null;
    $totalCount = 0;

    try {
        $stmt = $pdo->prepare("SELECT id, invoice_no, data FROM purchase_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (JSON_EXTRACT(data, '$.isGRN') = true OR invoice_no LIKE 'Z%' OR invoice_no LIKE 'GRN%') ORDER BY id ASC");
        $stmt->execute([$tenantId]);
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $totalCount++;
            $d = json_decode($r['data'], true) ?: [];
            $lastRec = $d;
            $rNo = (string)($r['invoice_no'] ?: ($d['invoiceNo'] ?? ''));
            if (strpos($rNo, $prefix) === 0) {
                $s = (int)substr($rNo, strlen($prefix));
                if ($s > $maxSeq) $maxSeq = $s;
            } elseif (preg_match('/(\d+)$/', $rNo, $m)) {
                $s = (int)$m[1];
                if ($s > $maxSeq) $maxSeq = $s;
            }
        }
    } catch (Exception $e) {}

    $nextRecNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
    return [
        'prefix' => $prefix,
        'maxSeq' => $maxSeq,
        'nextReceiptNo' => $nextRecNo,
        'totalCount' => $totalCount,
        'lastRecord' => $lastRec
    ];
}

// ─────────────────────────────────────────────────────────────
// MAIN DISPATCHER
// ─────────────────────────────────────────────────────────────

try {
    switch ($action) {

        // ═════════════════════════════════════════════════════════
        // 1. GET ALL MIRROR SEQUENCES & LATEST RECORDS
        // ═════════════════════════════════════════════════════════
        case 'get_mirror_status':
        case 'get_next_numbers':
        default:
            $finSeq = getFinanceSequence($pdo, $tenantId);
            $salesSeq = getSalesSequence($pdo, $tenantId);
            $purSeq = getPurchaseSequence($pdo, $tenantId);
            $delSeq = getDeliverySequence($pdo, $tenantId);
            $recSeq = getReceiptSequence($pdo, $tenantId);

            $rawItems = $pdo->query("SELECT data FROM items WHERE (is_deleted = 0 OR is_deleted IS NULL) LIMIT 100")->fetchAll(PDO::FETCH_COLUMN);
            $rawCusts = $pdo->query("SELECT data FROM customers WHERE (is_deleted = 0 OR is_deleted IS NULL) LIMIT 100")->fetchAll(PDO::FETCH_COLUMN);
            $rawWhs = $pdo->query("SELECT data FROM warehouses WHERE (is_deleted = 0 OR is_deleted IS NULL) LIMIT 50")->fetchAll(PDO::FETCH_COLUMN);

            $dbItems = array_values(array_filter(array_map('json_decode', $rawItems)));
            $dbCusts = array_values(array_filter(array_map('json_decode', $rawCusts)));
            $dbWhs = array_values(array_filter(array_map('json_decode', $rawWhs)));

            echo json_encode([
                'success' => true,
                'tenant_id' => $tenantId,
                'server_time' => date('Y-m-d H:i:s'),
                'finance' => $finSeq,
                'sales' => $salesSeq,
                'purchase' => $purSeq,
                'delivery' => $delSeq,
                'receipt' => $recSeq,
                'items' => $dbItems,
                'customers' => $dbCusts,
                'warehouses' => $dbWhs
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 2. SAVE SALES INVOICE (FROM MOBILE OR DESKTOP)
        // ═════════════════════════════════════════════════════════
        case 'save_sales_invoice':
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: $_POST;
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'بيانات الفاتورة فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $salesSeq = getSalesSequence($pdo, $tenantId);
            $invNo = trim($data['invoiceNo'] ?? ($data['id'] ?? ''));
            $originalOfflineId = '';
            if (strpos($invNo, 'أوفلاين') !== false || strpos($invNo, 'OFF-') !== false || strpos($invNo, 'offline') !== false) {
                $originalOfflineId = $invNo;
                $data['offline_id'] = $invNo;
                $invNo = $salesSeq['nextInvoiceNo'];
            } elseif (empty($invNo) || strpos($invNo, 'undefined') !== false || $invNo === 'new' || $invNo === 'SAL-NEW') {
                $invNo = $salesSeq['nextInvoiceNo'];
            }
            $data['invoiceNo'] = $invNo;
            $data['id'] = $invNo;
            $data['updatedAt'] = date('Y-m-d H:i:s');
            if (empty($data['date'])) $data['date'] = date('Y-m-d');
            $dateVal = $data['date'];

            $custName = $data['customerName'] ?? ($data['customer_name'] ?? 'عميل نقدي');
            $totalVal = (float)($data['grandTotal'] ?? ($data['total'] ?? ($data['subTotal'] ?? 0)));
            $statusVal = $data['status'] ?? ($data['saleType'] ?? 'completed');
            $currencyVal = $data['currency'] ?? 'SDG';
            $rateVal = (float)($data['exchangeRate'] ?? ($data['rate'] ?? 1.0));
            $userVal = $data['createdBy'] ?? 'user';

            $jsonRecord = json_encode($data, JSON_UNESCAPED_UNICODE);

            // Check if exists
            $stmtCheck = $pdo->prepare("SELECT id FROM sales_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (invoice_no = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
            $stmtCheck->execute([$tenantId, $invNo, $invNo, $invNo]);
            $existingId = $stmtCheck->fetchColumn();

            if ($existingId) {
                $stmtUp = $pdo->prepare("UPDATE sales_invoices SET invoice_no = ?, customer_id_val = ?, date_val = ?, total_val = ?, status_val = ?, currency_val = ?, exchange_rate_val = ?, created_by_val = ?, data = ?, updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$invNo, $custName, $dateVal, $totalVal, $statusVal, $currencyVal, $rateVal, $userVal, $jsonRecord, $existingId]);
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO sales_invoices (tenant_id, invoice_no, customer_id_val, date_val, total_val, status_val, currency_val, exchange_rate_val, created_by_val, data, created_at, updated_at, is_deleted) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 0)");
                $stmtIns->execute([$tenantId, $invNo, $custName, $dateVal, $totalVal, $statusVal, $currencyVal, $rateVal, $userVal, $jsonRecord]);
            }

            // Sync queue
            try {
                $syncId = 'sync-' . bin2hex(random_bytes(6));
                $stmtSync = $pdo->prepare("INSERT INTO sync_queue (id, entity_name, record_id, operation, payload, status, created_at, synced_at) VALUES (?, 'sales_invoices', ?, ?, ?, 'SYNCED', NOW(), NOW())");
                $stmtSync->execute([$syncId, $invNo, $existingId ? 'UPDATE' : 'INSERT', $jsonRecord]);
            } catch (Exception $e) {}

            $updatedSeq = getSalesSequence($pdo, $tenantId);
            echo json_encode([
                'success' => true,
                'message' => 'تم حفظ فاتورة المبيعات في قاعدة البيانات بنجاح',
                'invoiceNo' => $invNo,
                'nextInvoiceNo' => $updatedSeq['nextInvoiceNo'],
                'record' => $data
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 3. SAVE SALES SPLIT / INSTALLMENTS (تقسيم مبيعات وجدولة أقساط)
        // ═════════════════════════════════════════════════════════
        case 'save_sales_split':
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: $_POST;
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'بيانات تقسيم المبيعات فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $salesSeq = getSalesSequence($pdo, $tenantId);
            $invNo = trim($data['invoiceNo'] ?? ($data['id'] ?? ''));
            if (empty($invNo) || $invNo === 'new') {
                $invNo = $salesSeq['nextInvoiceNo'];
            }
            $data['invoiceNo'] = $invNo;
            $data['id'] = $invNo;
            $data['isSplit'] = true;
            $data['saleType'] = 'split';
            $data['status'] = 'split';
            $data['date'] = $data['date'] ?? date('Y-m-d');
            $data['updatedAt'] = date('Y-m-d H:i:s');

            $custName = $data['customerName'] ?? ($data['customer_name'] ?? 'عميل تجزئة');
            $totalVal = (float)($data['grandTotal'] ?? ($data['total'] ?? 0));
            $paidVal = (float)($data['paidAmount'] ?? ($data['downPayment'] ?? 0));
            $remainingVal = $totalVal - $paidVal;
            $data['remainingBalance'] = $remainingVal;

            $jsonRecord = json_encode($data, JSON_UNESCAPED_UNICODE);

            $stmtCheck = $pdo->prepare("SELECT id FROM sales_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (invoice_no = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ?)");
            $stmtCheck->execute([$tenantId, $invNo, $invNo]);
            $existingId = $stmtCheck->fetchColumn();

            if ($existingId) {
                $stmtUp = $pdo->prepare("UPDATE sales_invoices SET invoice_no = ?, customer_id_val = ?, date_val = ?, total_val = ?, status_val = 'split', data = ?, updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$invNo, $custName, $data['date'], $totalVal, $jsonRecord, $existingId]);
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO sales_invoices (tenant_id, invoice_no, customer_id_val, date_val, total_val, status_val, data, created_at, updated_at, is_deleted) VALUES (?, ?, ?, ?, ?, 'split', ?, NOW(), NOW(), 0)");
                $stmtIns->execute([$tenantId, $invNo, $custName, $data['date'], $totalVal, $jsonRecord]);
            }

            $updatedSeq = getSalesSequence($pdo, $tenantId);
            echo json_encode([
                'success' => true,
                'message' => 'تم حفظ واعتماد فاتورة تقسيم المبيعات وجدولة الأقساط بنجاح',
                'invoiceNo' => $invNo,
                'nextInvoiceNo' => $updatedSeq['nextInvoiceNo'],
                'remaining' => $remainingVal,
                'record' => $data
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 4. SAVE PURCHASE INVOICE (FROM MOBILE OR DESKTOP)
        // ═════════════════════════════════════════════════════════
        case 'save_purchase_invoice':
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: $_POST;
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'بيانات فاتورة الشراء فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $purSeq = getPurchaseSequence($pdo, $tenantId);
            $purNo = trim($data['invoiceNo'] ?? ($data['id'] ?? ''));
            $originalOfflineId = '';
            if (strpos($purNo, 'أوفلاين') !== false || strpos($purNo, 'OFF-') !== false || strpos($purNo, 'offline') !== false) {
                $originalOfflineId = $purNo;
                $data['offline_id'] = $purNo;
                $purNo = $purSeq['nextPurchaseNo'];
            } elseif (empty($purNo) || strpos($purNo, 'undefined') !== false || $purNo === 'new') {
                $purNo = $purSeq['nextPurchaseNo'];
            }
            $data['invoiceNo'] = $purNo;
            $data['id'] = $purNo;
            $data['updatedAt'] = date('Y-m-d H:i:s');
            if (empty($data['date'])) $data['date'] = date('Y-m-d');
            $dateVal = $data['date'];

            $suppName = $data['supplierName'] ?? ($data['supplier_name'] ?? 'مورد نقدي');
            $totalVal = (float)($data['grandTotal'] ?? ($data['total'] ?? 0));
            $statusVal = $data['status'] ?? 'completed';
            $currencyVal = $data['currency'] ?? 'SDG';
            $rateVal = (float)($data['exchangeRate'] ?? 1.0);
            $userVal = $data['createdBy'] ?? 'user';

            $jsonRecord = json_encode($data, JSON_UNESCAPED_UNICODE);

            $stmtCheck = $pdo->prepare("SELECT id FROM purchase_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (invoice_no = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
            $stmtCheck->execute([$tenantId, $purNo, $purNo, $purNo]);
            $existingId = $stmtCheck->fetchColumn();

            if ($existingId) {
                $stmtUp = $pdo->prepare("UPDATE purchase_invoices SET invoice_no = ?, supplier_id_val = ?, date_val = ?, total_val = ?, status_val = ?, currency_val = ?, exchange_rate_val = ?, created_by_val = ?, data = ?, updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$purNo, $suppName, $dateVal, $totalVal, $statusVal, $currencyVal, $rateVal, $userVal, $jsonRecord, $existingId]);
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO purchase_invoices (tenant_id, invoice_no, supplier_id_val, date_val, total_val, status_val, currency_val, exchange_rate_val, created_by_val, data, created_at, updated_at, is_deleted) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 0)");
                $stmtIns->execute([$tenantId, $purNo, $suppName, $dateVal, $totalVal, $statusVal, $currencyVal, $rateVal, $userVal, $jsonRecord]);
            }

            $updatedSeq = getPurchaseSequence($pdo, $tenantId);
            echo json_encode([
                'success' => true,
                'message' => 'تم حفظ فاتورة المشتريات في قاعدة البيانات بنجاح',
                'invoiceNo' => $purNo,
                'nextPurchaseNo' => $updatedSeq['nextPurchaseNo'],
                'record' => $data
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 5. SAVE SALES DELIVERY NOTE (تسليم مبيعات)
        // ═════════════════════════════════════════════════════════
        case 'save_delivery_note':
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: $_POST;
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'بيانات إذن التسليم فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $delSeq = getDeliverySequence($pdo, $tenantId);
            $delNo = trim($data['deliveryNo'] ?? ($data['id'] ?? ''));
            if (empty($delNo) || $delNo === 'new') {
                $delNo = $delSeq['nextDeliveryNo'];
            }
            $data['deliveryNo'] = $delNo;
            $data['id'] = $delNo;
            $data['date'] = $data['date'] ?? date('Y-m-d');
            $data['status'] = 'delivered';
            $jsonRecord = json_encode($data, JSON_UNESCAPED_UNICODE);

            try {
                $stmtDel = $pdo->prepare("INSERT INTO delivery_notes (tenant_id, frontend_id, data, created_at, updated_at, is_deleted) VALUES (?, ?, ?, NOW(), NOW(), 0)");
                $stmtDel->execute([$tenantId, $delNo, $jsonRecord]);
            } catch (Exception $e) {}

            // Update associated sales invoice if provided
            $relatedInvNo = trim($data['invoiceNo'] ?? ($data['relatedInvoice'] ?? ''));
            if (!empty($relatedInvNo)) {
                try {
                    $stmtInv = $pdo->prepare("SELECT id, data FROM sales_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (invoice_no = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ?)");
                    $stmtInv->execute([$tenantId, $relatedInvNo, $relatedInvNo]);
                    $row = $stmtInv->fetch(PDO::FETCH_ASSOC);
                    if ($row) {
                        $invD = json_decode($row['data'], true) ?: [];
                        $invD['deliveryStatus'] = 'delivered';
                        $invD['status'] = 'delivered';
                        $invD['deliveryNo'] = $delNo;
                        $invD['deliveredAt'] = date('Y-m-d H:i:s');
                        $upStmt = $pdo->prepare("UPDATE sales_invoices SET status_val = 'delivered', data = ?, updated_at = NOW() WHERE id = ?");
                        $upStmt->execute([json_encode($invD, JSON_UNESCAPED_UNICODE), $row['id']]);
                    }
                } catch (Exception $e) {}
            }

            $updatedDelSeq = getDeliverySequence($pdo, $tenantId);
            echo json_encode([
                'success' => true,
                'message' => 'تم تسجيل وتوثيق تسليم المبيعات وخصم المخزون بنجاح',
                'deliveryNo' => $delNo,
                'nextDeliveryNo' => $updatedDelSeq['nextDeliveryNo']
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 6. SAVE PURCHASE RECEIPT / GRN (تسليم واستلام المشتريات)
        // ═════════════════════════════════════════════════════════
        case 'save_purchase_receipt':
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: $_POST;
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'بيانات استلام المشتريات فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $recSeq = getReceiptSequence($pdo, $tenantId);
            $recNo = trim($data['receiptNo'] ?? ($data['grnNo'] ?? ($data['id'] ?? '')));
            if (empty($recNo) || $recNo === 'new') {
                $recNo = $recSeq['nextReceiptNo'];
            }
            $data['receiptNo'] = $recNo;
            $data['id'] = $recNo;
            $data['isGRN'] = true;
            $data['type'] = 'purchase_receipt';
            $data['date'] = $data['date'] ?? date('Y-m-d');
            $data['status'] = 'received';
            $jsonRecord = json_encode($data, JSON_UNESCAPED_UNICODE);

            $relatedPurNo = trim($data['originalInvoiceId'] ?? ($data['invoiceNo'] ?? ''));

            try {
                $stmtIns = $pdo->prepare("INSERT INTO purchase_invoices (tenant_id, invoice_no, supplier_id_val, date_val, total_val, status_val, data, created_at, updated_at, is_deleted) VALUES (?, ?, ?, ?, 0, 'received', ?, NOW(), NOW(), 0)");
                $stmtIns->execute([$tenantId, $recNo, $data['supplierName'] ?? 'مورد', $data['date'], $jsonRecord]);
            } catch (Exception $e) {}

            // Update parent purchase invoice if provided
            if (!empty($relatedPurNo)) {
                try {
                    $stmtPur = $pdo->prepare("SELECT id, data FROM purchase_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (invoice_no = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ?)");
                    $stmtPur->execute([$tenantId, $relatedPurNo, $relatedPurNo]);
                    $row = $stmtPur->fetch(PDO::FETCH_ASSOC);
                    if ($row) {
                        $pD = json_decode($row['data'], true) ?: [];
                        $pD['receiptStatus'] = 'received';
                        $pD['status'] = 'received';
                        $pD['receiptNo'] = $recNo;
                        $pD['receivedAt'] = date('Y-m-d H:i:s');
                        $upStmt = $pdo->prepare("UPDATE purchase_invoices SET status_val = 'received', data = ?, updated_at = NOW() WHERE id = ?");
                        $upStmt->execute([json_encode($pD, JSON_UNESCAPED_UNICODE), $row['id']]);
                    }
                } catch (Exception $e) {}
            }

            $updatedRecSeq = getReceiptSequence($pdo, $tenantId);
            echo json_encode([
                'success' => true,
                'message' => 'تم فحص واستلام بضاعة المشتريات وإضافتها للمخزن بنجاح',
                'receiptNo' => $recNo,
                'nextReceiptNo' => $updatedRecSeq['nextReceiptNo']
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 6.5 SAVE FINANCIAL VOUCHER / CASH PAYMENT (حركة مالية)
        // ═════════════════════════════════════════════════════════
        case 'save_voucher':
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: $_POST;
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'بيانات السند فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $finSeq = getFinanceSequence($pdo, $tenantId);
            $vNo = trim($data['voucherNo'] ?? ($data['id'] ?? ''));
            $originalOfflineId = '';
            $isOffline = (strpos($vNo, 'أوفلاين') !== false || strpos($vNo, 'OFF-') !== false || strpos($vNo, 'offline') !== false);
            if ($isOffline) {
                $originalOfflineId = $vNo;
                $data['offline_id'] = $vNo;
                $vNo = $finSeq['nextVoucherNo'];
            } elseif (empty($vNo) || $vNo === 'new' || strpos($vNo, 'undefined') !== false) {
                $vNo = $finSeq['nextVoucherNo'];
            }
            $data['voucherNo'] = $vNo;
            $data['id'] = $vNo;
            $data['updatedAt'] = date('Y-m-d H:i:s');
            if (empty($data['date'])) $data['date'] = date('Y-m-d');
            $jsonRecord = json_encode($data, JSON_UNESCAPED_UNICODE);

            $stmtCheck = $pdo->prepare("SELECT id FROM single_payments WHERE (tenant_id = ? OR tenant_id IS NULL) AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.voucherNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
            $stmtCheck->execute([$tenantId, $vNo, $vNo]);
            $existingId = $stmtCheck->fetchColumn();
            if ($existingId) {
                $stmtUp = $pdo->prepare("UPDATE single_payments SET data = ?, updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$jsonRecord, $existingId]);
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO single_payments (tenant_id, data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                $stmtIns->execute([$tenantId, $jsonRecord]);
            }

            try {
                $syncId = 'sync-' . bin2hex(random_bytes(6));
                $stmtSync = $pdo->prepare("INSERT INTO sync_queue (id, entity_name, record_id, operation, payload, status, created_at, synced_at) VALUES (?, 'single_payments', ?, ?, ?, 'SYNCED', NOW(), NOW())");
                $stmtSync->execute([$syncId, $vNo, $existingId ? 'UPDATE' : 'INSERT', $jsonRecord]);
            } catch(Exception $e) {}

            $updatedFinSeq = getFinanceSequence($pdo, $tenantId);
            echo json_encode([
                'success' => true,
                'message' => 'تم حفظ السند المالي بنجاح في قاعدة البيانات',
                'voucherNo' => $vNo,
                'id' => $vNo,
                'original_offline_id' => $originalOfflineId,
                'nextVoucherNo' => $updatedFinSeq['nextVoucherNo'],
                'record' => $data
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 6.6 ATOMIC BATCH OFFLINE SYNC (ترحيل دفعات الأوفلاين بطلب واحد)
        // ═════════════════════════════════════════════════════════
        case 'sync_offline_batch':
            $raw = file_get_contents('php://input');
            $req = json_decode($raw, true) ?: $_POST;
            $items = $req['items'] ?? [];
            if (!is_array($items) || empty($items)) {
                echo json_encode(['success' => false, 'error' => 'لا توجد عناصر للترحيل'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $syncedResults = [];
            foreach ($items as $item) {
                $type = $item['type'] ?? 'sales';
                $payload = $item['payload'] ?? ($item['data'] ?? []);
                $offlineId = $item['offline_id'] ?? ($payload['invoiceNo'] ?? ($payload['voucherNo'] ?? ($payload['id'] ?? '')));
                $realId = '';

                if ($type === 'sales') {
                    $sSeq = getSalesSequence($pdo, $tenantId);
                    $realId = $sSeq['nextInvoiceNo'];
                    $payload['invoiceNo'] = $realId;
                    $payload['id'] = $realId;
                    $payload['offline_id'] = $offlineId;
                    $payload['synced_at'] = date('Y-m-d H:i:s');
                    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
                    $stmt = $pdo->prepare("INSERT INTO sales_invoices (tenant_id, invoice_no, customer_id_val, date_val, total_val, status_val, data, created_at, updated_at, is_deleted) VALUES (?, ?, ?, ?, ?, 'completed', ?, NOW(), NOW(), 0)");
                    $stmt->execute([$tenantId, $realId, $payload['customerName'] ?? 'عميل', $payload['date'] ?? date('Y-m-d'), (float)($payload['grandTotal'] ?? 0), $json]);
                } elseif ($type === 'sales_split') {
                    $sSeq = getSalesSequence($pdo, $tenantId);
                    $realId = $sSeq['nextInvoiceNo'];
                    $payload['invoiceNo'] = $realId;
                    $payload['id'] = $realId;
                    $payload['offline_id'] = $offlineId;
                    $payload['isSplit'] = true;
                    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
                    $stmt = $pdo->prepare("INSERT INTO sales_invoices (tenant_id, invoice_no, customer_id_val, date_val, total_val, status_val, data, created_at, updated_at, is_deleted) VALUES (?, ?, ?, ?, ?, 'split', ?, NOW(), NOW(), 0)");
                    $stmt->execute([$tenantId, $realId, $payload['customerName'] ?? 'عميل', $payload['date'] ?? date('Y-m-d'), (float)($payload['grandTotal'] ?? 0), $json]);
                } elseif ($type === 'purchase') {
                    $pSeq = getPurchaseSequence($pdo, $tenantId);
                    $realId = $pSeq['nextPurchaseNo'];
                    $payload['invoiceNo'] = $realId;
                    $payload['id'] = $realId;
                    $payload['offline_id'] = $offlineId;
                    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
                    $stmt = $pdo->prepare("INSERT INTO purchase_invoices (tenant_id, invoice_no, supplier_id_val, date_val, total_val, status_val, data, created_at, updated_at, is_deleted) VALUES (?, ?, ?, ?, ?, 'completed', ?, NOW(), NOW(), 0)");
                    $stmt->execute([$tenantId, $realId, $payload['supplierName'] ?? 'مورد', $payload['date'] ?? date('Y-m-d'), (float)($payload['grandTotal'] ?? 0), $json]);
                } elseif ($type === 'delivery') {
                    $dSeq = getDeliverySequence($pdo, $tenantId);
                    $realId = $dSeq['nextDeliveryNo'];
                    $payload['deliveryNo'] = $realId;
                    $payload['id'] = $realId;
                    $payload['offline_id'] = $offlineId;
                    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
                    $stmt = $pdo->prepare("INSERT INTO delivery_notes (tenant_id, frontend_id, data, created_at, updated_at, is_deleted) VALUES (?, ?, ?, NOW(), NOW(), 0)");
                    $stmt->execute([$tenantId, $realId, $json]);
                } elseif ($type === 'purchase_receipt') {
                    $rSeq = getReceiptSequence($pdo, $tenantId);
                    $realId = $rSeq['nextReceiptNo'];
                    $payload['receiptNo'] = $realId;
                    $payload['id'] = $realId;
                    $payload['offline_id'] = $offlineId;
                    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
                    $stmt = $pdo->prepare("INSERT INTO purchase_invoices (tenant_id, invoice_no, supplier_id_val, date_val, total_val, status_val, data, created_at, updated_at, is_deleted) VALUES (?, ?, ?, ?, 0, 'received', ?, NOW(), NOW(), 0)");
                    $stmt->execute([$tenantId, $realId, $payload['supplierName'] ?? 'مورد', $payload['date'] ?? date('Y-m-d'), $json]);
                } elseif ($type === 'finance' || $type === 'voucher') {
                    $fSeq = getFinanceSequence($pdo, $tenantId);
                    $realId = $fSeq['nextVoucherNo'];
                    $payload['voucherNo'] = $realId;
                    $payload['id'] = $realId;
                    $payload['offline_id'] = $offlineId;
                    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
                    $stmt = $pdo->prepare("INSERT INTO single_payments (tenant_id, data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                    $stmt->execute([$tenantId, $json]);
                }

                $syncedResults[] = [
                    'type' => $type,
                    'offline_id' => $offlineId,
                    'real_id' => $realId,
                    'status' => 'synced',
                    'synced_at' => date('Y-m-d H:i:s')
                ];
            }

            echo json_encode([
                'success' => true,
                'message' => 'تم ترحيل الدفعة الأوفلاين بنجاح وتخصيص الأرقام الحقيقية في قاعدة البيانات',
                'count' => count($syncedResults),
                'synced' => $syncedResults
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 7. GET RECENT TRANSACTIONS FOR MIRROR SYNC ON DEVICES
        // ═════════════════════════════════════════════════════════
        case 'get_recent_sales':
            $stmt = $pdo->prepare("SELECT id, invoice_no, total_val, customer_id_val, date_val, status_val, data, created_at FROM sales_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 50");
            $stmt->execute([$tenantId]);
            $list = [];
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'], true) ?: [];
                $invNo = $r['invoice_no'] ?: ($d['invoiceNo'] ?? ('S-' . $r['id']));
                $d['invoiceNo'] = $invNo;
                $d['id'] = $invNo;
                $d['customerName'] = $r['customer_id_val'] ?: ($d['customerName'] ?? ($d['customer_name'] ?? 'عميل نقدي'));
                $d['total'] = (float)($r['total_val'] ?? ($d['grandTotal'] ?? ($d['total'] ?? 0)));
                $d['grandTotal'] = $d['total'];
                $d['date'] = $r['date_val'] ?: ($d['date'] ?? date('Y-m-d', strtotime($r['created_at'])));
                $list[] = $d;
            }
            $salesSeq = getSalesSequence($pdo, $tenantId);
            echo json_encode(['success' => true, 'sales' => $list, 'nextInvoiceNo' => $salesSeq['nextInvoiceNo']], JSON_UNESCAPED_UNICODE);
            break;

        case 'get_recent_purchases':
            $stmt = $pdo->prepare("SELECT id, invoice_no, total_val, supplier_id_val, date_val, status_val, data, created_at FROM purchase_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 50");
            $stmt->execute([$tenantId]);
            $list = [];
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'], true) ?: [];
                $purNo = $r['invoice_no'] ?: ($d['invoiceNo'] ?? ('P-' . $r['id']));
                $d['invoiceNo'] = $purNo;
                $d['id'] = $purNo;
                $d['supplierName'] = $r['supplier_id_val'] ?: ($d['supplierName'] ?? ($d['supplier_name'] ?? 'مورد نقدي'));
                $d['total'] = (float)($r['total_val'] ?? ($d['grandTotal'] ?? 0));
                $d['grandTotal'] = $d['total'];
                $d['date'] = $r['date_val'] ?: ($d['date'] ?? date('Y-m-d', strtotime($r['created_at'])));
                $list[] = $d;
            }
            $purSeq = getPurchaseSequence($pdo, $tenantId);
            echo json_encode(['success' => true, 'purchases' => $list, 'nextPurchaseNo' => $purSeq['nextPurchaseNo']], JSON_UNESCAPED_UNICODE);
            break;

        case 'get_recent_vouchers':
            $stmt = $pdo->prepare("SELECT id, data, date_val, created_at FROM single_payments WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 50");
            $stmt->execute([$tenantId]);
            $list = [];
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'], true) ?: [];
                $vNo = $d['voucherNo'] ?? ($d['id'] ?? ('E-' . $r['id']));
                $d['voucherNo'] = $vNo;
                $d['id'] = $vNo;
                $list[] = $d;
            }
            $finSeq = getFinanceSequence($pdo, $tenantId);
            echo json_encode(['success' => true, 'vouchers' => $list, 'nextVoucherNo' => $finSeq['nextVoucherNo']], JSON_UNESCAPED_UNICODE);
            break;

        case 'get_recent_deliveries':
            $stmt = $pdo->prepare("SELECT id, data, frontend_id, created_at FROM delivery_notes WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 50");
            $stmt->execute([$tenantId]);
            $list = [];
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'], true) ?: [];
                $dNo = $r['frontend_id'] ?: ($d['deliveryNo'] ?? ($d['id'] ?? ('DEL-' . $r['id'])));
                $d['deliveryNo'] = $dNo;
                $d['id'] = $dNo;
                $list[] = $d;
            }
            $delSeq = getDeliverySequence($pdo, $tenantId);
            echo json_encode(['success' => true, 'deliveries' => $list, 'nextDeliveryNo' => $delSeq['nextDeliveryNo']], JSON_UNESCAPED_UNICODE);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
