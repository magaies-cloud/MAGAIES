<?php
// =============================================================
// MAGAIEES ERP 2026 - Inbound Bank Receipts & Reconciliation API
// منظومة استقبال وفحص إشعارات بنكك والتحصيلات المصرفية الواردة
// =============================================================
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db_bridge.php';

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'error' => 'تعذر الاتصال بقاعدة البيانات']);
    exit;
}

// 1. Read Request & Parse Payload
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$jsonData = null;

if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $parsed = json_decode($rawInput, true);
        if (is_array($parsed)) {
            $jsonData = $parsed;
            if (empty($action) && isset($jsonData['action'])) {
                $action = $jsonData['action'];
            }
        }
    }
}

global $GLOBAL_TENANT_ID;
$activeTenantId = !empty($_GET['tenant_id']) ? (string)$_GET['tenant_id'] 
    : (!empty($_POST['tenant_id']) ? (string)$_POST['tenant_id'] 
    : (!empty($jsonData['tenant_id']) ? (string)$jsonData['tenant_id'] 
    : (!empty($GLOBAL_TENANT_ID) ? (string)$GLOBAL_TENANT_ID : '2')));

// 2. Ensure Table Exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS inbound_bank_receipts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id VARCHAR(50) NOT NULL DEFAULT '2',
        customer_id VARCHAR(50) NULL,
        customer_name VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(50) NULL,
        receipt_image VARCHAR(500) NULL,
        bank_name VARCHAR(100) DEFAULT 'بنك الخرطوم (بنكك)',
        bank_code VARCHAR(20) DEFAULT '170',
        transfer_no VARCHAR(100) NULL,
        amount DECIMAL(15,2) DEFAULT 0.00,
        currency VARCHAR(10) DEFAULT 'SDG',
        status ENUM('pending', 'verified', 'duplicate_rejected', 'reconciled') DEFAULT 'pending',
        issue_reason VARCHAR(255) NULL,
        voucher_no VARCHAR(50) NULL,
        source VARCHAR(50) DEFAULT 'whatsapp',
        notes TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_status (status),
        INDEX idx_transfer_no (transfer_no)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// Ensure uploads directory exists
$uploadDir = __DIR__ . '/../uploads/receipts_inbox/' . $activeTenantId . '/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

// 3. Dispatch Handlers
switch ($action) {
    case 'list':
    case 'get_inbound_receipts':
        try {
            $filterStatus = $_GET['status'] ?? '';
            $sql = "SELECT * FROM inbound_bank_receipts WHERE tenant_id = ?";
            $params = [$activeTenantId];
            if (!empty($filterStatus)) {
                $sql .= " AND status = ?";
                $params[] = $filterStatus;
            }
            $sql .= " ORDER BY created_at DESC LIMIT 100";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Compute statistics
            $statsStmt = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM inbound_bank_receipts WHERE tenant_id = ? GROUP BY status");
            $statsStmt->execute([$activeTenantId]);
            $statsRaw = $statsStmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $stats = [
                'total' => array_sum($statsRaw),
                'verified' => (int)($statsRaw['verified'] ?? 0),
                'duplicate_rejected' => (int)($statsRaw['duplicate_rejected'] ?? 0),
                'pending' => (int)($statsRaw['pending'] ?? 0),
                'reconciled' => (int)($statsRaw['reconciled'] ?? 0),
            ];

            echo json_encode([
                'success' => true,
                'data' => $rows,
                'stats' => $stats,
                'tenant_id' => $activeTenantId
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'upload':
    case 'save_inbound':
        try {
            $custName = trim($_POST['customer_name'] ?? $jsonData['customer_name'] ?? 'زبون مباشر');
            $custId = trim($_POST['customer_id'] ?? $jsonData['customer_id'] ?? '');
            $custPhone = trim($_POST['customer_phone'] ?? $jsonData['customer_phone'] ?? '');
            $bankName = trim($_POST['bank_name'] ?? $jsonData['bank_name'] ?? 'بنك الخرطوم (بنكك)');
            $bankCode = trim($_POST['bank_code'] ?? $jsonData['bank_code'] ?? '170');
            $transferNo = trim($_POST['transfer_no'] ?? $jsonData['transfer_no'] ?? '');
            $amount = floatval($_POST['amount'] ?? $jsonData['amount'] ?? 0);
            $currency = trim($_POST['currency'] ?? $jsonData['currency'] ?? 'SDG');
            $notes = trim($_POST['notes'] ?? $jsonData['notes'] ?? '');
            $caption = trim($_POST['caption'] ?? $jsonData['caption'] ?? '');
            $imagePath = '';

            // Handle Caption extraction (if customer wrote details in WhatsApp message)
            if (!empty($caption)) {
                if (empty($notes)) {
                    $notes = $caption;
                } elseif (!str_contains($notes, $caption)) {
                    $notes .= ' | ' . $caption;
                }
                // Try extracting transfer number (6 to 12 consecutive digits)
                if (empty($transferNo)) {
                    if (preg_match('/(?:عملية|معاملة|مرجعي|رقم|id|ref|txn|no|#)\D*([0-9]{6,12})/ui', $caption, $mTx)) {
                        $transferNo = $mTx[1];
                    } elseif (preg_match('/\b([0-9]{7,10})\b/', $caption, $mTx2)) {
                        $transferNo = $mTx2[1];
                    }
                }
                // Try extracting amount
                if ($amount <= 0 && preg_match('/(?:مبلغ|تحويل|قيمة|مبلغ قدره)?\s*([0-9]+(?:\.[0-9]+)?|[0-9]{1,3}(?:,[0-9]{3})*(?:\.[0-9]+)?)\s*(?:ج|جنيه|sdg)?/ui', $caption, $mAmt)) {
                    $amount = floatval(str_replace(',', '', $mAmt[1]));
                }
            }

            // Auto-Match Customer from DB by phone if customer_id not supplied
            if (empty($custId) && !empty($custPhone)) {
                $cleanPhone = preg_replace('/\D/', '', $custPhone);
                if (strlen($cleanPhone) >= 7) {
                    $last9 = substr($cleanPhone, -9);
                    try {
                        $cStmt = $pdo->prepare("SELECT frontend_id, search_name FROM customers 
                            WHERE tenant_id = ? 
                              AND (is_deleted = 0 OR is_deleted IS NULL)
                              AND (search_phone LIKE ? OR data LIKE ?)
                            ORDER BY id DESC LIMIT 1");
                        $cStmt->execute([$activeTenantId, '%' . $last9 . '%', '%' . $last9 . '%']);
                        $matchedCust = $cStmt->fetch(PDO::FETCH_ASSOC);
                        if ($matchedCust) {
                            $custId = $matchedCust['frontend_id'];
                            if ($custName === 'زبون مباشر' || str_starts_with($custName, 'عميل واتساب')) {
                                $custName = $matchedCust['search_name'];
                            }
                        }
                    } catch (Exception $ce) {}
                }
            }

            // Handle file upload
            if (!empty($_FILES['receipt_file']['tmp_name'])) {
                $ext = strtolower(pathinfo($_FILES['receipt_file']['name'], PATHINFO_EXTENSION)) ?: 'png';
                $filename = 'bankak_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $target = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['receipt_file']['tmp_name'], $target)) {
                    $imagePath = 'uploads/receipts_inbox/' . $activeTenantId . '/' . $filename;
                }
            } elseif (!empty($_POST['receipt_base64']) || !empty($jsonData['receipt_base64'])) {
                $b64 = $_POST['receipt_base64'] ?? $jsonData['receipt_base64'];
                if (preg_match('/^data:image\/(\w+);base64,/', $b64, $m)) {
                    $ext = $m[1];
                    $b64 = substr($b64, strpos($b64, ',') + 1);
                } else {
                    $ext = 'png';
                }
                $decoded = base64_decode($b64);
                if ($decoded) {
                    $filename = 'bankak_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    file_put_contents($uploadDir . $filename, $decoded);
                    $imagePath = 'uploads/receipts_inbox/' . $activeTenantId . '/' . $filename;
                }
            } elseif (!empty($_POST['receipt_image']) || !empty($jsonData['receipt_image'])) {
                $imagePath = $_POST['receipt_image'] ?? $jsonData['receipt_image'];
            }

            // Quick pre-check for duplicate if transfer_no is provided
            $initialStatus = 'pending';
            $issueReason = null;
            if (!empty($transferNo) && strlen($transferNo) >= 6) {
                $dupCheck = checkTransferConflictInDB($pdo, $activeTenantId, $transferNo);
                if ($dupCheck['is_duplicate']) {
                    $initialStatus = 'duplicate_rejected';
                    $issueReason = 'duplicate_voucher_' . ($dupCheck['conflicting_voucher'] ?? '');
                    $notes = '⚠️ كشف تكرار مالي: رقم العملية ' . $transferNo . ' مسجل بالسند ' . ($dupCheck['conflicting_voucher'] ?? '');
                } else {
                    $initialStatus = 'verified';
                }
            }

            $stmt = $pdo->prepare("INSERT INTO inbound_bank_receipts 
                (tenant_id, customer_id, customer_name, customer_phone, receipt_image, bank_name, bank_code, transfer_no, amount, currency, status, issue_reason, notes, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $activeTenantId, $custId, $custName, $custPhone, $imagePath, $bankName, $bankCode, $transferNo, $amount, $currency, $initialStatus, $issueReason, $notes
            ]);
            $newId = $pdo->lastInsertId();

            // Automatic Backend OCR if amount or transfer_no is missing and image exists
            if (($amount <= 0 || empty($transferNo)) && !empty($imagePath)) {
                $ocrRes = performBackendReceiptOCR($pdo, $newId);
                if (!empty($ocrRes['success']) && !empty($ocrRes['data'])) {
                    $initialStatus = $ocrRes['data']['status'] ?? $initialStatus;
                    $issueReason = $ocrRes['data']['issue_reason'] ?? $issueReason;
                    $amount = $ocrRes['data']['amount'] ?? $amount;
                    $transferNo = $ocrRes['data']['transfer_no'] ?? $transferNo;
                }
            }

            echo json_encode([
                'success' => true,
                'id' => $newId,
                'status' => $initialStatus,
                'issue_reason' => $issueReason,
                'amount' => $amount,
                'transfer_no' => $transferNo,
                'receipt_image' => $imagePath,
                'message' => 'تم استلام وحفظ وفحص إشعار بنكك بنجاح بالذكاء الاصطناعي'
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'scan_receipt':
    case 'trigger_ocr':
        try {
            $id = intval($_GET['id'] ?? $_POST['id'] ?? $jsonData['id'] ?? 0);
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'معرّف الإشعار مطلوب']);
                exit;
            }
            $ocrRes = performBackendReceiptOCR($pdo, $id);
            echo json_encode($ocrRes);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'verify_duplicate':
        try {
            $transferNo = trim($_GET['transfer_no'] ?? $_POST['transfer_no'] ?? $jsonData['transfer_no'] ?? '');
            $excludeId = intval($_GET['receipt_id'] ?? $_POST['receipt_id'] ?? $jsonData['receipt_id'] ?? 0);
            
            if (empty($transferNo) || strlen($transferNo) < 6) {
                echo json_encode(['success' => true, 'is_duplicate' => false, 'message' => 'رقم المعاملة قصير أو غير متوفر']);
                exit;
            }

            $result = checkTransferConflictInDB($pdo, $activeTenantId, $transferNo, $excludeId);
            echo json_encode(array_merge(['success' => true], $result));
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'update_status':
        try {
            $id = intval($_POST['id'] ?? $jsonData['id'] ?? 0);
            $newStatus = trim($_POST['status'] ?? $jsonData['status'] ?? '');
            $issueReason = trim($_POST['issue_reason'] ?? $jsonData['issue_reason'] ?? '');
            $voucherNo = trim($_POST['voucher_no'] ?? $jsonData['voucher_no'] ?? '');

            if (!$id || empty($newStatus)) {
                echo json_encode(['success' => false, 'error' => 'معرّف الإشعار والحالة مطلوبان']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE inbound_bank_receipts 
                SET status = ?, issue_reason = COALESCE(NULLIF(?, ''), issue_reason), voucher_no = COALESCE(NULLIF(?, ''), voucher_no), updated_at = NOW() 
                WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$newStatus, $issueReason, $voucherNo, $id, $activeTenantId]);

            echo json_encode(['success' => true, 'message' => 'تم تحديث حالة الإشعار بنجاح']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'dismiss':
    case 'delete':
        try {
            $id = intval($_POST['id'] ?? $jsonData['id'] ?? 0);
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'معرّف الإشعار مطلوب']);
                exit;
            }
            $stmt = $pdo->prepare("DELETE FROM inbound_bank_receipts WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $activeTenantId]);
            echo json_encode(['success' => true, 'message' => 'تم حذف الإشعار من الصندوق']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'إجراء غير معروف']);
        break;
}

// -------------------------------------------------------------
// Helper: Check duplicate transfer number across existing data
// -------------------------------------------------------------
function checkTransferConflictInDB($pdo, $tenantId, $transferNo, $excludeReceiptId = 0) {
    $suffix = substr(trim($transferNo), -6);
    if (empty($suffix)) {
        return ['is_duplicate' => false, 'conflicting_voucher' => null];
    }
    
    // 1. Check existing inbound receipts
    $sql1 = "SELECT id, customer_name, voucher_no, amount, created_at 
             FROM inbound_bank_receipts 
             WHERE tenant_id = ? AND id != ? AND transfer_no LIKE ? AND status IN ('verified', 'reconciled', 'duplicate_rejected')
             LIMIT 1";
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute([$tenantId, $excludeReceiptId, '%' . $suffix]);
    $hit = $stmt1->fetch(PDO::FETCH_ASSOC);

    if ($hit) {
        return [
            'is_duplicate' => true,
            'conflicting_type' => 'inbound_receipt',
            'conflicting_voucher' => !empty($hit['voucher_no']) ? $hit['voucher_no'] : ('إشعار #' . $hit['id']),
            'customer_name' => $hit['customer_name'],
            'date' => date('Y-m-d', strtotime($hit['created_at'])),
            'amount' => (float)$hit['amount']
        ];
    }

    // 2. Check vouchers table
    try {
        $vStmt = $pdo->prepare("SELECT id, data FROM vouchers WHERE tenant_id = ? AND data LIKE ? LIMIT 1");
        $vStmt->execute([$tenantId, '%' . $suffix . '%']);
        $vHit = $vStmt->fetch(PDO::FETCH_ASSOC);
        if ($vHit) {
            $vData = json_decode($vHit['data'], true);
            return [
                'is_duplicate' => true,
                'conflicting_type' => 'voucher',
                'conflicting_voucher' => $vData['voucher_no'] ?? ('سند #' . $vHit['id']),
                'customer_name' => $vData['party_name'] ?? $vData['customer_name'] ?? 'عميل',
                'date' => $vData['date'] ?? '',
                'amount' => (float)($vData['amount'] ?? 0)
            ];
        }
    } catch (Exception $ve) {}

    return [
        'is_duplicate' => false,
        'conflicting_voucher' => null
    ];
}

// -------------------------------------------------------------
// Helper: Perform Backend AI OCR on Inbound Receipt Image
// -------------------------------------------------------------
function performBackendReceiptOCR($pdo, $receiptId) {
    $stmt = $pdo->prepare("SELECT * FROM inbound_bank_receipts WHERE id = ?");
    $stmt->execute([$receiptId]);
    $receipt = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$receipt || empty($receipt['receipt_image'])) {
        return ['success' => false, 'error' => 'الإشعار أو مسار الصورة غير موجود'];
    }

    $imageRel = $receipt['receipt_image'];
    $fullPath = __DIR__ . '/../' . ltrim($imageRel, '/\\');
    if (!file_exists($fullPath)) {
        return ['success' => false, 'error' => 'ملف صورة الإشعار غير موجود على الخادم: ' . $imageRel];
    }

    $imgBytes = file_get_contents($fullPath);
    if (empty($imgBytes)) {
        return ['success' => false, 'error' => 'تعذر قراءة ملف صورة الإشعار'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_buffer($finfo, $imgBytes) ?: 'image/jpeg';
    finfo_close($finfo);

    $base64 = 'data:' . $mime . ';base64,' . base64_encode($imgBytes);

    // Call OCR endpoint internally
    $ocrPayload = json_encode([
        'image' => $base64,
        'doc_hint' => 'transfer'
    ]);

    $ch = curl_init('http://localhost/magaees/api/ocr-financial.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $ocrPayload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $ocrRaw = curl_exec($ch);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if (empty($ocrRaw)) {
        return ['success' => false, 'error' => 'تعذر الاتصال بمحرك الذكاء الاصطناعي: ' . $curlErr];
    }

    $ocrData = json_decode($ocrRaw, true);
    if (!$ocrData || empty($ocrData['success']) || empty($ocrData['data'])) {
        return ['success' => false, 'error' => $ocrData['error'] ?? 'فشل فحص المستند بالذكاء الاصطناعي'];
    }

    $d = $ocrData['data'];
    $extractedAmount = floatval($d['amount'] ?? 0);
    $extractedRef = trim($d['transaction_ref'] ?? '');
    $extractedSender = trim($d['sender_name'] ?? '');
    $extractedBank = trim($d['bank_name'] ?? 'بنك الخرطوم (بنكك)');
    $summary = trim($d['summary'] ?? '');

    $tenantId = $receipt['tenant_id'];

    // Check duplicate
    $status = 'verified';
    $issueReason = null;
    $notes = $summary ?: ('تم الفحص بالذكاء الاصطناعي بنجاح | رقم العملية: ' . $extractedRef);

    if (!empty($extractedRef) && strlen($extractedRef) >= 6) {
        $dupCheck = checkTransferConflictInDB($pdo, $tenantId, $extractedRef, (int)$receiptId);
        if ($dupCheck['is_duplicate']) {
            $status = 'duplicate_rejected';
            $issueReason = 'duplicate_voucher_' . ($dupCheck['conflicting_voucher'] ?? '');
            $notes = '⚠️ كشف تكرار مالي: رقم العملية ' . $extractedRef . ' مسجل بالسند ' . ($dupCheck['conflicting_voucher'] ?? '');
        }
    }

    // Customer name resolution if generic
    $custName = $receipt['customer_name'];
    if (!empty($extractedSender) && ($custName === 'زبون مباشر' || str_starts_with($custName, 'عميل واتساب') || $custName === 'amar00105')) {
        $custName = $extractedSender;
    }

    // Match customer from DB if possible
    $custId = $receipt['customer_id'];
    if (empty($custId) && !empty($custName)) {
        try {
            $cStmt = $pdo->prepare("SELECT frontend_id FROM customers WHERE tenant_id = ? AND (search_name LIKE ? OR data LIKE ?) LIMIT 1");
            $cStmt->execute([$tenantId, '%' . $custName . '%', '%' . $custName . '%']);
            $fId = $cStmt->fetchColumn();
            if ($fId) $custId = $fId;
        } catch (Exception $ce) {}
    }

    $updateStmt = $pdo->prepare("UPDATE inbound_bank_receipts 
        SET amount = ?, 
            transfer_no = ?, 
            customer_id = ?,
            customer_name = ?,
            bank_name = ?,
            status = ?, 
            issue_reason = ?, 
            notes = ? 
        WHERE id = ?");
    $updateStmt->execute([
        $extractedAmount,
        $extractedRef,
        $custId,
        $custName,
        $extractedBank,
        $status,
        $issueReason,
        $notes,
        $receiptId
    ]);

    return [
        'success' => true,
        'data' => [
            'id' => $receiptId,
            'amount' => $extractedAmount,
            'transfer_no' => $extractedRef,
            'customer_id' => $custId,
            'customer_name' => $custName,
            'bank_name' => $extractedBank,
            'status' => $status,
            'issue_reason' => $issueReason,
            'notes' => $notes,
            'ocr_details' => $d
        ]
    ];
}
