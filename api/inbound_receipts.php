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
    : (!empty($GLOBAL_TENANT_ID) ? (string)$GLOBAL_TENANT_ID : '12')));

// 2. Ensure Table Exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS inbound_bank_receipts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id VARCHAR(50) NOT NULL DEFAULT '12',
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
                if ($filterStatus === 'verified') {
                    $sql .= " AND status IN ('verified', 'reconciled')";
                } else {
                    $sql .= " AND status = ?";
                    $params[] = $filterStatus;
                }
            }
            $sql .= " ORDER BY created_at DESC LIMIT 100";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Compute statistics
            $statsStmt = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM inbound_bank_receipts WHERE tenant_id = ? GROUP BY status");
            $statsStmt->execute([$activeTenantId]);
            $statsRaw = $statsStmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $verifiedTotal = (int)($statsRaw['verified'] ?? 0) + (int)($statsRaw['reconciled'] ?? 0);
            $stats = [
                'total' => array_sum($statsRaw),
                'verified' => $verifiedTotal,
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

            // Auto-Match Customer from DB by phone or name if customer_id not supplied
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
                            if ($custName === 'زبون مباشر' || str_starts_with($custName, 'عميل واتساب') || $custName === 'عميل غير معروف') {
                                $custName = $matchedCust['search_name'];
                            }
                        }
                    } catch (Exception $ce) {}
                }
            }
            if (empty($custId) && !empty($custName) && $custName !== 'زبون مباشر' && !str_starts_with($custName, 'عميل واتساب')) {
                try {
                    $cStmt2 = $pdo->prepare("SELECT frontend_id, search_name FROM customers 
                        WHERE tenant_id = ? 
                          AND (is_deleted = 0 OR is_deleted IS NULL)
                          AND (search_name = ? OR search_name LIKE ?)
                        ORDER BY id DESC LIMIT 1");
                    $cStmt2->execute([$activeTenantId, $custName, '%' . $custName . '%']);
                    $matchedCust2 = $cStmt2->fetch(PDO::FETCH_ASSOC);
                    if ($matchedCust2) {
                        $custId = $matchedCust2['frontend_id'];
                        $custName = $matchedCust2['search_name'] ?: $custName;
                    }
                } catch (Exception $ce2) {}
            }

            // Handle file upload
            $allowedReceiptExts = ['png', 'jpg', 'jpeg', 'webp'];
            if (!empty($_FILES['receipt_file']['tmp_name'])) {
                $ext = strtolower(pathinfo($_FILES['receipt_file']['name'], PATHINFO_EXTENSION)) ?: 'png';
                if (!in_array($ext, $allowedReceiptExts, true)) {
                    $ext = 'png';
                }
                $filename = 'bankak_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $target = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['receipt_file']['tmp_name'], $target)) {
                    $imagePath = 'uploads/receipts_inbox/' . $activeTenantId . '/' . $filename;
                }
            } elseif (!empty($_POST['receipt_base64']) || !empty($jsonData['receipt_base64'])) {
                $b64 = $_POST['receipt_base64'] ?? $jsonData['receipt_base64'];
                if (preg_match('/^data:image\/(\w+);base64,/', $b64, $m)) {
                    $ext = strtolower($m[1]);
                    $b64 = substr($b64, strpos($b64, ',') + 1);
                } else {
                    $ext = 'png';
                }
                if (!in_array($ext, $allowedReceiptExts, true)) {
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
            $cleanTnoDigits = preg_replace('/\D/', '', $transferNo);
            if (!empty($cleanTnoDigits) && strlen($cleanTnoDigits) >= 6) {
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
            $newId = (int)$pdo->lastInsertId();

            // Automatic Backend OCR if amount or transfer_no is missing and image exists
            if (($amount <= 0 || empty($transferNo)) && !empty($imagePath)) {
                $ocrRes = performBackendReceiptOCR($pdo, $newId);
                if (!empty($ocrRes['success']) && !empty($ocrRes['data'])) {
                    $initialStatus = $ocrRes['data']['status'] ?? $initialStatus;
                    $issueReason = $ocrRes['data']['issue_reason'] ?? $issueReason;
                    $amount = (float)($ocrRes['data']['amount'] ?? $amount);
                    $transferNo = $ocrRes['data']['transfer_no'] ?? $transferNo;
                    $custName = $ocrRes['data']['customer_name'] ?? $custName;
                    $custId = $ocrRes['data']['customer_id'] ?? $custId;
                }
            }

            // Automatic Account Posting if receipt is verified and valid
            $postingResult = null;
            if ($initialStatus === 'verified' && $amount > 0 && strlen(preg_replace('/\D/', '', (string)$transferNo)) >= 6) {
                $postingResult = postInboundReceiptToAccounts($pdo, $newId);
            }

            // Reload final receipt row
            $finalStmt = $pdo->prepare("SELECT * FROM inbound_bank_receipts WHERE id = ?");
            $finalStmt->execute([$newId]);
            $finalRow = $finalStmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'id' => $newId,
                'status' => $finalRow['status'] ?? $initialStatus,
                'issue_reason' => $finalRow['issue_reason'] ?? $issueReason,
                'amount' => (float)($finalRow['amount'] ?? $amount),
                'transfer_no' => $finalRow['transfer_no'] ?? $transferNo,
                'customer_id' => $finalRow['customer_id'] ?? $custId,
                'customer_name' => $finalRow['customer_name'] ?? $custName,
                'voucher_no' => $finalRow['voucher_no'] ?? ($postingResult['voucher_no'] ?? null),
                'receipt_image' => $imagePath,
                'posting' => $postingResult,
                'receipt' => $finalRow,
                'message' => !empty($postingResult['voucher_no'])
                    ? ('تم فحص إشعار البنك وتنزيله في الحسابات بنجاح بالسند رقم ' . $postingResult['voucher_no'])
                    : 'تم استلام وحفظ وفحص إشعار بنكك بنجاح بالذكاء الاصطناعي'
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

    case 'post_to_accounts':
    case 'reconcile_receipt':
        try {
            $id = intval($_GET['id'] ?? $_POST['id'] ?? $jsonData['id'] ?? 0);
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'معرّف الإشعار مطلوب']);
                exit;
            }
            $postRes = postInboundReceiptToAccounts($pdo, $id);
            echo json_encode($postRes);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'verify_duplicate':
        try {
            $transferNo = trim($_GET['transfer_no'] ?? $_POST['transfer_no'] ?? $jsonData['transfer_no'] ?? '');
            $excludeId = intval($_GET['receipt_id'] ?? $_POST['receipt_id'] ?? $jsonData['receipt_id'] ?? 0);
            
            if (empty($transferNo) || strlen(preg_replace('/\D/', '', $transferNo)) < 6) {
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
    $cleanDigits = preg_replace('/\D/', '', (string)$transferNo);
    $suffix = substr($cleanDigits, -6);
    if (strlen($suffix) < 6) {
        return ['is_duplicate' => false, 'conflicting_voucher' => null];
    }

    // Find own voucher_no if excludeReceiptId is provided so we don't conflict with ourselves
    $ownVoucherNo = '';
    if ($excludeReceiptId > 0) {
        try {
            $ownStmt = $pdo->prepare("SELECT voucher_no FROM inbound_bank_receipts WHERE id = ? LIMIT 1");
            $ownStmt->execute([$excludeReceiptId]);
            $ownVoucherNo = (string)($ownStmt->fetchColumn() ?: '');
        } catch (Exception $e) {}
    }
    
    // 1. Check existing inbound receipts
    $sql1 = "SELECT id, customer_name, voucher_no, amount, created_at, transfer_no 
             FROM inbound_bank_receipts 
             WHERE tenant_id = ? AND id != ? AND transfer_no LIKE ? AND status IN ('verified', 'reconciled')
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

    // 2. Check actual financial voucher tables in MySQL
    $voucherTables = ['receipts', 'payments', 'single_receipts', 'single_payments'];
    foreach ($voucherTables as $tbl) {
        try {
            $vStmt = $pdo->prepare("SELECT id, data FROM `$tbl` WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) AND data LIKE ? LIMIT 10");
            $vStmt->execute([$tenantId, '%' . $suffix . '%']);
            while ($vHit = $vStmt->fetch(PDO::FETCH_ASSOC)) {
                $vData = json_decode($vHit['data'], true);
                if (!is_array($vData) || !empty($vData['deleted'])) continue;

                $vNo = $vData['voucherNo'] ?? $vData['receiptNo'] ?? $vData['paymentNo'] ?? $vData['id'] ?? ('سند #' . $vHit['id']);
                if (!empty($ownVoucherNo) && (string)$vNo === (string)$ownVoucherNo) {
                    continue;
                }
                if ($excludeReceiptId > 0 && isset($vData['inboundReceiptId']) && (int)$vData['inboundReceiptId'] === (int)$excludeReceiptId) {
                    continue;
                }

                // Verify the 6-digit suffix actually belongs to a transfer number field inside the voucher
                $matchedTransfer = false;
                $checkCandidates = [
                    $vData['transferNo'] ?? '',
                    $vData['mobileTransferNo'] ?? '',
                    $vData['reference'] ?? ''
                ];
                if (!empty($vData['bankTransfers']) && is_array($vData['bankTransfers'])) {
                    foreach ($vData['bankTransfers'] as $bt) {
                        $checkCandidates[] = $bt['transferNo'] ?? ($bt['reference'] ?? '');
                    }
                }
                if (!empty($vData['items']) && is_array($vData['items'])) {
                    foreach ($vData['items'] as $it) {
                        $checkCandidates[] = $it['transferNo'] ?? '';
                        if (!empty($it['bankTransfers']) && is_array($it['bankTransfers'])) {
                            foreach ($it['bankTransfers'] as $bt) {
                                $checkCandidates[] = $bt['transferNo'] ?? ($bt['reference'] ?? '');
                            }
                        }
                    }
                }
                foreach ($checkCandidates as $cand) {
                    $candDigits = preg_replace('/\D/', '', (string)$cand);
                    if (strlen($candDigits) >= 6 && substr($candDigits, -6) === $suffix) {
                        $matchedTransfer = true;
                        break;
                    }
                }

                if ($matchedTransfer) {
                    $partyName = $vData['party_name'] ?? $vData['customer_name'] ?? $vData['accountName'] ?? '';
                    if (empty($partyName) && !empty($vData['items'][0]['accountName'])) {
                        $partyName = $vData['items'][0]['accountName'];
                    }
                    return [
                        'is_duplicate' => true,
                        'conflicting_type' => $tbl,
                        'conflicting_voucher' => $vNo,
                        'customer_name' => $partyName ?: 'عميل',
                        'date' => $vData['date'] ?? '',
                        'amount' => (float)($vData['totalAmount'] ?? $vData['amount'] ?? 0)
                    ];
                }
            }
        } catch (Exception $ve) {}
    }

    return [
        'is_duplicate' => false,
        'conflicting_voucher' => null
    ];
}

// -------------------------------------------------------------
// Helper: Post Verified Inbound Receipt to Accounts (Receipts, Customers, Banks, GL)
// -------------------------------------------------------------
function postInboundReceiptToAccounts($pdo, $receiptId) {
    $stmt = $pdo->prepare("SELECT * FROM inbound_bank_receipts WHERE id = ? LIMIT 1");
    $stmt->execute([$receiptId]);
    $receipt = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$receipt) {
        return ['success' => false, 'error' => 'الإشعار غير موجود'];
    }

    $tenantId = (int)$receipt['tenant_id'];
    $amount = (float)($receipt['amount'] ?? 0);
    $transferNo = trim((string)($receipt['transfer_no'] ?? ''));
    $custName = trim((string)($receipt['customer_name'] ?? 'عميل بنكك'));
    $custId = trim((string)($receipt['customer_id'] ?? ''));
    $custPhone = trim((string)($receipt['customer_phone'] ?? ''));
    $bankName = trim((string)($receipt['bank_name'] ?? 'بنك الخرطوم (بنكك)'));
    $currency = trim((string)($receipt['currency'] ?? 'SDG')) ?: 'SDG';

    if ($receipt['status'] === 'duplicate_rejected') {
        return ['success' => false, 'error' => 'لا يمكن تنزيل إشعار مكرر في الحسابات'];
    }
    if ($amount <= 0) {
        return ['success' => false, 'error' => 'مبلغ الإشعار يجب أن يكون أكبر من الصفر لتنزيله في الحسابات'];
    }

    // Check if already posted to receipts table (Idempotency)
    $existingVoucherNo = trim((string)($receipt['voucher_no'] ?? ''));
    if (!empty($existingVoucherNo)) {
        try {
            $chkV = $pdo->prepare("SELECT data FROM receipts WHERE tenant_id = ? AND frontend_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) LIMIT 1");
            $chkV->execute([$tenantId, $existingVoucherNo]);
            $existingVJson = $chkV->fetchColumn();
            if ($existingVJson) {
                $existingVObj = json_decode($existingVJson, true);
                return [
                    'success' => true,
                    'already_posted' => true,
                    'voucher_no' => $existingVoucherNo,
                    'customer_id' => $custId,
                    'customer_name' => $custName,
                    'voucher' => $existingVObj,
                    'message' => 'الإشعار منزل مسبقاً في الحسابات بالسند رقم ' . $existingVoucherNo
                ];
            }
        } catch (Exception $e) {}
    }

    // 1. Resolve or Auto-Create Customer in `customers` table and update balance
    $cleanCustId = preg_replace('/^cust-/', '', $custId);
    $matchedCustomerRow = null;

    if (!empty($cleanCustId)) {
        $cStmt = $pdo->prepare("SELECT id, data FROM customers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) AND frontend_id = ? LIMIT 1");
        $cStmt->execute([$tenantId, $cleanCustId]);
        $matchedCustomerRow = $cStmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$matchedCustomerRow && !empty($custPhone)) {
        $cleanPhone = preg_replace('/\D/', '', $custPhone);
        if (strlen($cleanPhone) >= 7) {
            $last9 = substr($cleanPhone, -9);
            $cStmt = $pdo->prepare("SELECT id, data FROM customers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) AND (search_phone LIKE ? OR data LIKE ?) ORDER BY id DESC LIMIT 1");
            $cStmt->execute([$tenantId, '%' . $last9 . '%', '%' . $last9 . '%']);
            $matchedCustomerRow = $cStmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    if (!$matchedCustomerRow && !empty($custName) && $custName !== 'زبون مباشر' && !str_starts_with($custName, 'عميل واتساب')) {
        $cStmt = $pdo->prepare("SELECT id, data FROM customers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) AND (search_name = ? OR search_name LIKE ?) ORDER BY id DESC LIMIT 1");
        $cStmt->execute([$tenantId, $custName, '%' . $custName . '%']);
        $matchedCustomerRow = $cStmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($matchedCustomerRow) {
        $custObj = json_decode($matchedCustomerRow['data'], true) ?: [];
        $custFrontendId = $custObj['id'] ?? $cleanCustId ?: ('CUST-' . round(microtime(true) * 1000));
        $custName = $custObj['name'] ?? $custName;
        if (empty($custObj['phone']) && !empty($custPhone)) {
            $custObj['phone'] = $custPhone;
        }
        $custObj['currentBalance'] = (float)($custObj['currentBalance'] ?? 0) - $amount;
        $updCust = $pdo->prepare("UPDATE customers SET data = ? WHERE id = ?");
        $updCust->execute([json_encode($custObj, JSON_UNESCAPED_UNICODE), $matchedCustomerRow['id']]);
    } else {
        $custFrontendId = !empty($cleanCustId) ? $cleanCustId : ('CUST-' . round(microtime(true) * 1000));
        if (empty($custName) || $custName === 'زبون مباشر') {
            $custName = !empty($custPhone) ? ('عميل بنكك (' . $custPhone . ')') : ('عميل بنكك #' . $receiptId);
        }
        $custObj = [
            'id' => $custFrontendId,
            'name' => $custName,
            'phone' => $custPhone,
            'whatsapp' => $custPhone,
            'email' => '',
            'contactPerson' => '',
            'address' => '',
            'openingDebit' => 0,
            'openingCredit' => 0,
            'currency' => $currency,
            'exRate' => 1,
            'taxNumber' => '',
            'accountNo' => '',
            'currentBalance' => -$amount,
            'creditLimit' => 0,
            'securityLevel' => 1,
            'status' => 'active',
            'notes' => 'تم فتح الحساب تلقائياً من إشعار تحويل بنكي وارد رقم ' . $transferNo,
            'bankAccounts' => [],
            'date' => date('Y-m-d'),
            'createdAt' => gmdate('Y-m-d\TH:i:s.v\Z')
        ];
        $insCust = $pdo->prepare("INSERT INTO customers (tenant_id, customer_id, data) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data)");
        $insCust->execute([$tenantId, $custFrontendId, json_encode($custObj, JSON_UNESCAPED_UNICODE)]);
    }

    // 2. Resolve or Auto-Create Bank in `banks` table and update balance
    $matchedBankRow = null;
    $bStmt = $pdo->prepare("SELECT id, data FROM banks WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
    $bStmt->execute([$tenantId]);
    $allBankRows = $bStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($allBankRows as $bRow) {
        $bData = json_decode($bRow['data'], true);
        if (!$bData || !empty($bData['deleted'])) continue;
        $bType = strtolower($bData['type'] ?? 'bank');
        $bRowName = $bData['name'] ?? $bData['bankName'] ?? '';
        if ($bType === 'bank') {
            if (!$matchedBankRow) {
                $matchedBankRow = $bRow;
            }
            if (!empty($bRowName) && (mb_stripos($bankName, $bRowName) !== false || mb_stripos($bRowName, 'الخرطوم') !== false || mb_stripos($bRowName, 'بنكك') !== false)) {
                $matchedBankRow = $bRow;
                break;
            }
        }
    }

    if ($matchedBankRow) {
        $bankObj = json_decode($matchedBankRow['data'], true) ?: [];
        $bankFrontendId = $bankObj['id'] ?? 'B-1700000000001';
        $resolvedBankName = $bankObj['name'] ?? $bankName;
        $bankObj['currentBalance'] = (float)($bankObj['currentBalance'] ?? 0) + $amount;
        $updBank = $pdo->prepare("UPDATE banks SET data = ? WHERE id = ?");
        $updBank->execute([json_encode($bankObj, JSON_UNESCAPED_UNICODE), $matchedBankRow['id']]);
    } else {
        $bankFrontendId = 'B-1700000000001';
        $resolvedBankName = 'بنك الخرطوم';
        $bankObj = [
            'id' => $bankFrontendId,
            'name' => $resolvedBankName,
            'type' => 'bank',
            'branch' => 'الرئيسي',
            'iban' => '',
            'swift' => '',
            'currency' => 'SDG',
            'maxLimit' => 0,
            'minLimit' => 0,
            'openingBalance' => 0,
            'currentBalance' => $amount,
            'confidentiality' => '1',
            'isInactive' => false
        ];
        $insBank = $pdo->prepare("INSERT INTO banks (tenant_id, data) VALUES (?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data)");
        $insBank->execute([$tenantId, json_encode($bankObj, JSON_UNESCAPED_UNICODE)]);
    }

    // 3. Generate Voucher Number and Save Grouped Receipt Voucher in `receipts`
    $sysRate = function_exists('getSystemUsdExchangeRate') ? (float)getSystemUsdExchangeRate($pdo, $tenantId) : 5999.0;
    if ($sysRate <= 0) $sysRate = 5999.0;

    $vNo = !empty($existingVoucherNo) ? $existingVoucherNo : ('M' . date('ym') . rand(1000, 9999));
    if (empty($existingVoucherNo)) {
        for ($try = 0; $try < 5; $try++) {
            $chk = $pdo->prepare("SELECT COUNT(*) FROM receipts WHERE tenant_id = ? AND frontend_id = ?");
            $chk->execute([$tenantId, $vNo]);
            if ((int)$chk->fetchColumn() === 0) break;
            $vNo = 'M' . date('ym') . rand(1000, 9999);
        }
    }

    $today = date('Y-m-d');
    $nowTime = date('H:i');
    $voucherDesc = 'سداد إشعار تحويل بنكي (' . $bankName . ') رقم العملية: ' . $transferNo . ' - العميل: ' . $custName;

    $voucherObj = [
        'id' => $vNo,
        'voucherNo' => $vNo,
        'type' => 'receipt',
        'isGrouped' => true,
        'mainAccount' => 'bank-' . $bankFrontendId,
        'toAccount' => 'bank-' . $bankFrontendId,
        'fromAccount' => 'حسابات فرعية متعددة',
        'date' => $today,
        'time' => $nowTime,
        'description' => $voucherDesc,
        'notes' => $voucherDesc,
        'totalAmount' => $amount,
        'amountLocal' => ($currency === 'SDG') ? $amount : ($amount * $sysRate),
        'foreignAmount' => ($currency === 'SDG') ? round($amount / $sysRate, 2) : $amount,
        'amountForeign' => ($currency === 'SDG') ? round($amount / $sysRate, 2) : $amount,
        'currency' => $currency,
        'exchangeRate' => 1,
        'systemExchangeRate' => $sysRate,
        'paymentMethod' => 'hybrid',
        'checkNo' => '',
        'checkBranch' => '',
        'checkDate' => '',
        'checkDueDate' => '',
        'mobileTransferNo' => $transferNo,
        'mobileNote' => $voucherDesc,
        'bankTransfers' => [],
        'cheques' => [],
        'inboundReceiptId' => (int)$receiptId,
        'items' => [
            [
                'account' => 'cust-' . $custFrontendId,
                'accountId' => 'cust-' . $custFrontendId,
                'accountName' => $custName,
                'amount' => $amount,
                'statement' => 'إشعار تحويل ' . $bankName . ' رقم ' . $transferNo,
                'costCenter' => '',
                'costCenterId' => '',
                'costCenterName' => '',
                'paymentMethods' => ['bank'],
                'paymentMethod' => 'bank',
                'cashAmount' => 0,
                'cashSafe' => '',
                'cashStatement' => '',
                'bankAmount' => $amount,
                'bankTransfers' => [
                    [
                        'amount' => $amount,
                        'bankName' => $resolvedBankName,
                        'transferNo' => $transferNo,
                        'statement' => 'إشعار وارد #' . $receiptId . ' - ' . $custName
                    ]
                ],
                'chequeAmount' => 0,
                'cheques' => [],
                'bankName' => $resolvedBankName,
                'transferNo' => $transferNo,
                'chequeBank' => '',
                'chequeNo' => '',
                'chequeDate' => '',
                'chequeDueDate' => ''
            ]
        ],
        'isPosted' => true,
        'createdAt' => gmdate('Y-m-d\TH:i:s.v\Z'),
        'createdBy' => 9,
        'createdByName' => 'النظام الذكي (فحص الإشعارات)'
    ];

    $insRec = $pdo->prepare("INSERT INTO receipts (tenant_id, receipt_no, data) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data)");
    $insRec->execute([$tenantId, $vNo, json_encode($voucherObj, JSON_UNESCAPED_UNICODE)]);

    // 4. Insert General Ledger Double-Entry Records in `gl_entries`
    try {
        $glDr = [
            'id' => 'GL-' . $vNo . '-DR',
            'refNo' => $vNo,
            'voucherNo' => $vNo,
            'voucherId' => $vNo,
            'date' => $today,
            'account' => 'bank-' . $bankFrontendId,
            'accountId' => 'bank-' . $bankFrontendId,
            'accountName' => $resolvedBankName,
            'debit' => $amount,
            'credit' => 0,
            'description' => $voucherDesc,
            'type' => 'receipt',
            'currency' => $currency,
            'exchangeRate' => 1,
            'status' => 'posted',
            'createdAt' => gmdate('Y-m-d\TH:i:s.v\Z')
        ];
        $glCr = [
            'id' => 'GL-' . $vNo . '-CR',
            'refNo' => $vNo,
            'voucherNo' => $vNo,
            'voucherId' => $vNo,
            'date' => $today,
            'account' => 'cust-' . $custFrontendId,
            'accountId' => 'cust-' . $custFrontendId,
            'accountName' => $custName,
            'debit' => 0,
            'credit' => $amount,
            'description' => $voucherDesc,
            'type' => 'receipt',
            'currency' => $currency,
            'exchangeRate' => 1,
            'status' => 'posted',
            'createdAt' => gmdate('Y-m-d\TH:i:s.v\Z')
        ];
        $insGl = $pdo->prepare("INSERT INTO gl_entries (tenant_id, data) VALUES (?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data)");
        $insGl->execute([$tenantId, json_encode($glDr, JSON_UNESCAPED_UNICODE)]);
        $insGl->execute([$tenantId, json_encode($glCr, JSON_UNESCAPED_UNICODE)]);
    } catch (Exception $glErr) {
        error_log('Inbound Receipt GL Insert Warning: ' . $glErr->getMessage());
    }

    // 5. Update `inbound_bank_receipts` with voucher_no and customer_id
    $updatedNotes = trim((string)($receipt['notes'] ?? ''));
    $postedTag = '✅ تم التنزيل في الحسابات بالسند ' . $vNo;
    if (mb_strpos($updatedNotes, $vNo) === false) {
        $updatedNotes = !empty($updatedNotes) ? ($updatedNotes . ' | ' . $postedTag) : $postedTag;
    }

    $updInb = $pdo->prepare("UPDATE inbound_bank_receipts 
        SET customer_id = ?, customer_name = ?, voucher_no = ?, status = 'verified', notes = ?, updated_at = NOW() 
        WHERE id = ?");
    $updInb->execute([$custFrontendId, $custName, $vNo, $updatedNotes, $receiptId]);

    return [
        'success' => true,
        'already_posted' => false,
        'voucher_no' => $vNo,
        'customer_id' => $custFrontendId,
        'customer_name' => $custName,
        'bank_id' => $bankFrontendId,
        'bank_name' => $resolvedBankName,
        'voucher' => $voucherObj,
        'customer' => $custObj,
        'bank' => $bankObj,
        'message' => 'تم فحص الإشعار وتنزيله في الحسابات بنجاح بالسند رقم ' . $vNo
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

    // Call OCR endpoint internally using dynamic URL resolution
    $ocrPayload = json_encode([
        'image' => $base64,
        'doc_hint' => 'transfer'
    ]);

    $candidateUrls = [];
    if (!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['SCRIPT_NAME'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $candidateUrls[] = $scheme . '://' . $_SERVER['HTTP_HOST'] . $baseDir . '/ocr-financial.php';
    }
    $candidateUrls[] = 'http://localhost/ELSAJANA2/magaies/api/ocr-financial.php';
    $candidateUrls[] = 'http://127.0.0.1/ELSAJANA2/magaies/api/ocr-financial.php';
    $candidateUrls[] = 'http://127.0.0.1/api/ocr-financial.php';
    $candidateUrls[] = 'https://c.elsajana.com/api/ocr-financial.php';
    $candidateUrls = array_values(array_unique($candidateUrls));

    $ocrRaw = '';
    $curlErr = '';
    foreach ($candidateUrls as $ocrUrl) {
        $ch = curl_init($ocrUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $ocrPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $resp = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200 && !empty($resp)) {
            $parsedTry = json_decode($resp, true);
            if (is_array($parsedTry) && isset($parsedTry['success'])) {
                $ocrRaw = $resp;
                break;
            }
        }
    }

    if (empty($ocrRaw)) {
        return ['success' => false, 'error' => 'تعذر الاتصال بمحرك الذكاء الاصطناعي: ' . $curlErr];
    }

    $ocrData = json_decode($ocrRaw, true);
    if (!$ocrData || empty($ocrData['success']) || empty($ocrData['data'])) {
        return ['success' => false, 'error' => $ocrData['error'] ?? 'فشل فحص المستند بالذكاء الاصطناعي'];
    }

    $d = $ocrData['data'];
    $extractedAmount = floatval($d['amount'] ?? 0) ?: floatval($receipt['amount'] ?? 0);
    $extractedRef = trim($d['transaction_ref'] ?? '') ?: trim($receipt['transfer_no'] ?? '');
    $extractedSender = trim($d['sender_name'] ?? '');
    $extractedBank = trim($d['bank_name'] ?? 'بنك الخرطوم (بنكك)');
    $summary = trim($d['summary'] ?? '');

    $tenantId = $receipt['tenant_id'];

    // Check duplicate
    $status = 'verified';
    $issueReason = null;
    $notes = $summary ?: ('تم الفحص بالذكاء الاصطناعي بنجاح | رقم العملية: ' . $extractedRef);

    if (!empty($extractedRef) && strlen(preg_replace('/\D/', '', $extractedRef)) >= 6) {
        $dupCheck = checkTransferConflictInDB($pdo, $tenantId, $extractedRef, (int)$receiptId);
        if ($dupCheck['is_duplicate']) {
            $status = 'duplicate_rejected';
            $issueReason = 'duplicate_voucher_' . ($dupCheck['conflicting_voucher'] ?? '');
            $notes = '⚠️ كشف تكرار مالي: رقم العملية ' . $extractedRef . ' مسجل بالسند ' . ($dupCheck['conflicting_voucher'] ?? '');
        }
    } elseif ($extractedAmount <= 0) {
        $status = 'pending';
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
            $cStmt = $pdo->prepare("SELECT frontend_id FROM customers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) AND (search_name LIKE ? OR data LIKE ?) LIMIT 1");
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

    // Automatically post to accounts if verified
    $postingResult = null;
    if ($status === 'verified' && $extractedAmount > 0 && strlen(preg_replace('/\D/', '', $extractedRef)) >= 6) {
        $postingResult = postInboundReceiptToAccounts($pdo, $receiptId);
        if (!empty($postingResult['voucher_no'])) {
            $custId = $postingResult['customer_id'] ?? $custId;
            $custName = $postingResult['customer_name'] ?? $custName;
        }
    }

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
            'voucher_no' => $postingResult['voucher_no'] ?? ($receipt['voucher_no'] ?? null),
            'notes' => $notes,
            'posting' => $postingResult,
            'ocr_details' => $d
        ]
    ];
}

