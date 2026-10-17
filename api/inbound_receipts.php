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
            $customerCodeHint = trim($_POST['customer_code'] ?? $jsonData['customer_code'] ?? '');
            $imagePath = '';

            // Handle Caption extraction (if customer wrote details or code in WhatsApp message)
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
                // Only extract amount from caption if explicitly labeled as an amount or currency (so customer codes like 105 aren't mistaken for amount)
                if ($amount <= 0 && preg_match('/(?:(?:مبلغ|تحويل|قيمة|مبلغ قدره)\s*([0-9]+(?:\.[0-9]+)?|[0-9]{1,3}(?:,[0-9]{3})*(?:\.[0-9]+)?)|([0-9]{1,3}(?:,[0-9]{3})+(?:\.[0-9]+)?|[0-9]{4,}(?:\.[0-9]+)?)\s*(?:ج|جنيه|sdg))/ui', $caption, $mAmt)) {
                    $amtStr = !empty($mAmt[1]) ? $mAmt[1] : ($mAmt[2] ?? '0');
                    $amount = floatval(str_replace(',', '', $amtStr));
                }
            }

            // Auto-Match Customer from DB by Receipt Code (Number or Word or Both), WhatsApp phone / Delegate SubPhone, LID, or Name
            $routingHint = trim($customerCodeHint . ' ' . $caption . ' ' . $notes);
            $resolvedCust = resolveInboundCustomer($pdo, $activeTenantId, $custId, $custName, $custPhone, $routingHint);
            if (!empty($resolvedCust['id'])) {
                $custId = $resolvedCust['id'];
                $custName = $resolvedCust['name'] ?: $custName;
                if (!empty($resolvedCust['phone'])) {
                    $custPhone = $resolvedCust['phone'];
                }
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

            // Quick pre-check for transfer_no length (>= 6 digits) & duplicate if transfer_no is provided
            $initialStatus = 'pending';
            $issueReason = null;
            $cleanTnoDigits = preg_replace('/\D/', '', $transferNo);
            if (!empty($transferNo) && strlen($cleanTnoDigits) < 6) {
                $initialStatus = 'pending';
                $issueReason = 'invalid_transfer_no_under_6_digits';
                $notes = '⚠️ رقم التحويل يجب ألا يقل عن 6 أرقام (' . $transferNo . ')';
            } elseif (!empty($cleanTnoDigits) && strlen($cleanTnoDigits) >= 6) {
                $dupCheck = checkTransferConflictInDB($pdo, $activeTenantId, $transferNo);
                if ($dupCheck['is_duplicate']) {
                    $initialStatus = 'duplicate_rejected';
                    $issueReason = 'duplicate_voucher_' . ($dupCheck['conflicting_voucher'] ?? '');
                    $notes = '⚠️ كشف تكرار مالي: رقم العملية ' . $transferNo . ' مسجل بالسند ' . ($dupCheck['conflicting_voucher'] ?? '');
                } elseif ($amount > 0) {
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

            // Automatic Backend OCR (WITHOUT forgery check — fraud_check = false) if amount or transfer_no is missing and image exists
            if (($amount <= 0 || strlen($cleanTnoDigits) < 6) && !empty($imagePath)) {
                $ocrRes = performBackendReceiptOCR($pdo, $newId, false);
                if (!empty($ocrRes['success']) && !empty($ocrRes['data'])) {
                    $initialStatus = $ocrRes['data']['status'] ?? $initialStatus;
                    $issueReason = $ocrRes['data']['issue_reason'] ?? $issueReason;
                    $amount = (float)($ocrRes['data']['amount'] ?? $amount);
                    $transferNo = $ocrRes['data']['transfer_no'] ?? $transferNo;
                    $custName = $ocrRes['data']['customer_name'] ?? $custName;
                    $custId = $ocrRes['data']['customer_id'] ?? $custId;
                    $postingResult = $ocrRes['data']['posting'] ?? null;
                }
            }

            // Automatic Account Posting if receipt is verified and valid (and not already posted inside performBackendReceiptOCR)
            if (empty($postingResult) && $initialStatus === 'verified' && $amount > 0 && strlen(preg_replace('/\D/', '', (string)$transferNo)) >= 6) {
                $postingResult = postInboundReceiptToAccounts($pdo, $newId);
            }

            // Reload final receipt row
            $finalStmt = $pdo->prepare("SELECT * FROM inbound_bank_receipts WHERE id = ?");
            $finalStmt->execute([$newId]);
            $finalRow = $finalStmt->fetch(PDO::FETCH_ASSOC);

            $finalVoucherNo = $finalRow['voucher_no'] ?? ($postingResult['voucher_no'] ?? null);
            $finalCustName = $finalRow['customer_name'] ?? $custName;
            $finalAmount = (float)($finalRow['amount'] ?? $amount);
            $finalTransferNo = $finalRow['transfer_no'] ?? $transferNo;

            $adminNotificationText = $postingResult['admin_notification'] ?? null;

            echo json_encode([
                'success' => true,
                'id' => $newId,
                'status' => $finalRow['status'] ?? $initialStatus,
                'issue_reason' => $finalRow['issue_reason'] ?? $issueReason,
                'amount' => $finalAmount,
                'transfer_no' => $finalTransferNo,
                'customer_id' => $finalRow['customer_id'] ?? $custId,
                'customer_name' => $finalCustName,
                'customer_phone' => $finalRow['customer_phone'] ?? $custPhone,
                'voucher_no' => $finalVoucherNo,
                'receipt_image' => $imagePath,
                'posting' => $postingResult,
                'receipt' => $finalRow,
                'admin_notification' => $adminNotificationText,
                'message' => !empty($finalVoucherNo)
                    ? ('تم فحص إشعار البنك وتنزيله في حساب (' . $finalCustName . ') إلى بنك الخرطوم بالسند رقم ' . $finalVoucherNo)
                    : 'تم استلام وحفظ وفحص إشعار بنكك بنجاح'
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
            // Automatic / standard OCR always runs WITHOUT forgery check (fraudCheck = false)
            $ocrRes = performBackendReceiptOCR($pdo, $id, false);
            echo json_encode($ocrRes);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'check_forgery':
    case 'forensic_check':
        // On-Demand Forgery Check ONLY when requested by user ("فحص التزوير عند الطلب فقط")
        try {
            $id = intval($_GET['id'] ?? $_POST['id'] ?? $jsonData['id'] ?? 0);
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'معرّف الإشعار مطلوب لفحص التزوير']);
                exit;
            }
            $forgeryRes = performBackendReceiptOCR($pdo, $id, true);
            echo json_encode($forgeryRes);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'scan_pending_batch':
        // Sequentially scan all pending receipts for tenant without duplicate scanning and without forgery check
        try {
            $pStmt = $pdo->prepare("SELECT id FROM inbound_bank_receipts WHERE tenant_id = ? AND (status = 'pending' OR (status = 'verified' AND (voucher_no IS NULL OR voucher_no = ''))) ORDER BY id ASC LIMIT 50");
            $pStmt->execute([$activeTenantId]);
            $pendingIds = $pStmt->fetchAll(PDO::FETCH_COLUMN);
            $results = [];
            $postedItems = [];
            foreach ($pendingIds as $pid) {
                $resItem = performBackendReceiptOCR($pdo, (int)$pid, false);
                $results[] = $resItem;
                if (!empty($resItem['success']) && !empty($resItem['data']['voucher_no'])) {
                    $postedItems[] = $resItem['data'];
                }
            }
            echo json_encode([
                'success' => true,
                'processed_count' => count($results),
                'posted_count' => count($postedItems),
                'posted_items' => $postedItems,
                'results' => $results
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'get_admin_notifications':
        try {
            $nStmt = $pdo->prepare("SELECT pref_value FROM ui_preferences WHERE tenant_id = ? AND pref_key = 'admin_inbound_notifications' LIMIT 1");
            $nStmt->execute([$activeTenantId]);
            $rawNotifs = $nStmt->fetchColumn();
            $list = $rawNotifs ? (json_decode($rawNotifs, true) ?: []) : [];
            echo json_encode(['success' => true, 'notifications' => $list]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'notifications' => []]);
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
                echo json_encode(['success' => true, 'is_duplicate' => false, 'invalid_length' => true, 'message' => 'رقم المعاملة يجب ألا يقل عن 6 أرقام']);
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

    case 'assign_customer_by_code':
        try {
            $id = intval($_POST['id'] ?? $jsonData['id'] ?? 0);
            $codeOrName = trim($_POST['customer_code'] ?? $jsonData['customer_code'] ?? '');
            $linkSenderPhone = !empty($_POST['link_sender_phone']) || !empty($jsonData['link_sender_phone']);

            if (!$id || empty($codeOrName)) {
                echo json_encode(['success' => false, 'error' => 'معرّف الإشعار وكود/اسم العميل مطلوبان']);
                exit;
            }

            $rStmt = $pdo->prepare("SELECT * FROM inbound_bank_receipts WHERE id = ? AND tenant_id = ? LIMIT 1");
            $rStmt->execute([$id, $activeTenantId]);
            $recRow = $rStmt->fetch(PDO::FETCH_ASSOC);
            if (!$recRow) {
                echo json_encode(['success' => false, 'error' => 'الإشعار غير موجود']);
                exit;
            }

            $senderPhone = trim((string)($recRow['customer_phone'] ?? ''));
            $resolved = resolveInboundCustomer($pdo, $activeTenantId, $codeOrName, $codeOrName, '', $codeOrName);
            if (empty($resolved['row']) || empty($resolved['id'])) {
                echo json_encode(['success' => false, 'error' => 'لم يتم العثور على عميل مسجل بهذا الكود أو الاسم: ' . $codeOrName]);
                exit;
            }

            $targetCustRow = $resolved['row'];
            $targetCustObj = json_decode($targetCustRow['data'], true) ?: [];
            $targetCustId = $resolved['id'];
            $targetCustName = $resolved['name'];

            // Optionally link sender's phone to customer's subPhones so future receipts from this phone route automatically
            $linkedPhoneMsg = '';
            if ($linkSenderPhone && !empty($senderPhone) && strlen(preg_replace('/\D/', '', $senderPhone)) >= 7) {
                $cleanSender = preg_replace('/\D/', '', $senderPhone);
                $last9Sender = substr($cleanSender, -9);
                $subPhones = isset($targetCustObj['subPhones']) && is_array($targetCustObj['subPhones']) ? $targetCustObj['subPhones'] : [];
                $alreadyLinked = false;
                foreach ($subPhones as $sp) {
                    $spNum = is_array($sp) ? ($sp['phone'] ?? '') : (string)$sp;
                    $spDigits = preg_replace('/\D/', '', $spNum);
                    if ($spDigits === $cleanSender || (strlen($spDigits) >= 9 && substr($spDigits, -9) === $last9Sender)) {
                        $alreadyLinked = true;
                        break;
                    }
                }
                if (!$alreadyLinked) {
                    $subPhones[] = [
                        'phone' => $senderPhone,
                        'label' => 'مرسل إشعار #' . $id
                    ];
                    $targetCustObj['subPhones'] = $subPhones;
                    $updSub = $pdo->prepare("UPDATE customers SET data = ? WHERE id = ?");
                    $updSub->execute([json_encode($targetCustObj, JSON_UNESCAPED_UNICODE), $targetCustRow['id']]);
                    $linkedPhoneMsg = ' + تم ربط رقم المرسل (' . $senderPhone . ') بحساب العميل تلقائياً';
                }
            }

            // Update receipt customer_id and customer_name
            $newNotes = trim((string)($recRow['notes'] ?? ''));
            $codeBadge = $resolved['matched_code'] ?? $codeOrName;
            $routeNote = '🎯 توجيه لحساب: ' . $targetCustName . ' (كود: ' . $codeBadge . ')';
            if (mb_strpos($newNotes, $routeNote) === false) {
                $newNotes = !empty($newNotes) ? ($newNotes . ' | ' . $routeNote) : $routeNote;
            }

            // Clear old voucher if it was previously posted to another customer so postInboundReceiptToAccounts posts to the new customer
            $oldVoucherNo = trim((string)($recRow['voucher_no'] ?? ''));
            if (!empty($oldVoucherNo)) {
                $delOldRec = $pdo->prepare("DELETE FROM receipts WHERE tenant_id = ? AND (frontend_id = ? OR receipt_no = ?)");
                $delOldRec->execute([$activeTenantId, $oldVoucherNo, $oldVoucherNo]);
                $delOldGl = $pdo->prepare("DELETE FROM gl_entries WHERE tenant_id = ? AND data LIKE ?");
                $delOldGl->execute([$activeTenantId, '%' . $oldVoucherNo . '%']);
            }

            $uStmt = $pdo->prepare("UPDATE inbound_bank_receipts SET customer_id = ?, customer_name = ?, voucher_no = NULL, notes = ? WHERE id = ?");
            $uStmt->execute([$targetCustId, $targetCustName, $newNotes, $id]);

            $postRes = postInboundReceiptToAccounts($pdo, $id);
            echo json_encode([
                'success' => true,
                'customer_id' => $targetCustId,
                'customer_name' => $targetCustName,
                'posting' => $postRes,
                'customer' => $postRes['customer'] ?? $targetCustObj,
                'message' => '✅ تم توجيه الإشعار وتنزيله في حساب (' . $targetCustName . ') بنجاح' . $linkedPhoneMsg
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'إجراء غير معروف']);
        break;
}

// -------------------------------------------------------------
// Helper: Normalize Arabic/Persian Digits to ASCII
// -------------------------------------------------------------
function normalizeInboundDigits($str) {
    $ar = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
    $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    return str_replace($fa, $en, str_replace($ar, $en, (string)$str));
}

// -------------------------------------------------------------
// Helper: Ensure all active customers in DB have gap-aware receiptCodeNum (1..infinity)
// -------------------------------------------------------------
function fetchAndEnsureCustomerCodes($pdo, $tenantId) {
    try {
        $stmt = $pdo->prepare("SELECT id, frontend_id, search_name, search_phone, data FROM customers WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
        $stmt->execute([$tenantId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }

    $usedNums = [];
    foreach ($rows as &$r) {
        $cObj = json_decode($r['data'], true) ?: [];
        if (!empty($cObj['deleted'])) continue;
        $num = isset($cObj['receiptCodeNum']) ? (int)normalizeInboundDigits($cObj['receiptCodeNum']) : 0;
        if ($num >= 1 && !isset($usedNums[$num])) {
            $usedNums[$num] = true;
            $cObj['receiptCodeNum'] = $num;
        } else {
            $cObj['receiptCodeNum'] = 0;
        }
        $r['_parsed'] = $cObj;
    }
    unset($r);

    $nextCandidate = 1;
    foreach ($rows as &$r) {
        $cObj = $r['_parsed'] ?? [];
        if (!empty($cObj['deleted'])) continue;
        if (empty($cObj['receiptCodeNum']) || $cObj['receiptCodeNum'] < 1) {
            while (isset($usedNums[$nextCandidate])) {
                $nextCandidate++;
            }
            $cObj['receiptCodeNum'] = $nextCandidate;
            $usedNums[$nextCandidate] = true;
            $newJson = json_encode($cObj, JSON_UNESCAPED_UNICODE);
            $r['data'] = $newJson;
            $r['_parsed'] = $cObj;
            try {
                $u = $pdo->prepare("UPDATE customers SET data = ? WHERE id = ?");
                $u->execute([$newJson, $r['id']]);
            } catch (Exception $e) {}
        }
    }
    unset($r);

    return $rows;
}

// -------------------------------------------------------------
// Helper: Resolve Customer from Receipt Code (Numeric 1..∞ or Word/Phrase or Both),
// WhatsApp Primary Phone / Delegate SubPhones / LID, or Arabic & English Name
// -------------------------------------------------------------
function resolveInboundCustomer($pdo, $tenantId, $custId = '', $custName = '', $custPhone = '', $routingHint = '') {
    $cleanCustId = preg_replace('/^cust-/', '', trim((string)$custId));
    $custName = trim((string)$custName);
    $custPhone = trim((string)$custPhone);
    $cleanPhone = preg_replace('/\D/', '', normalizeInboundDigits($custPhone));
    $routingHint = trim((string)$routingHint);

    $allCusts = fetchAndEnsureCustomerCodes($pdo, $tenantId);
    if (empty($allCusts)) {
        return ['row' => null, 'id' => $cleanCustId, 'name' => $custName, 'phone' => $custPhone];
    }

    // 0. Match by Customer Receipt Code (Option 1: Numeric Code e.g. 105, Option 2: Word/Phrase Code e.g. بون, or Both together!)
    $combinedCodePool = normalizeInboundDigits(trim($routingHint . ' ' . $cleanCustId . ' ' . $custName));
    if (!empty($combinedCodePool)) {
        // 0a. Check Word/Phrase Code (`receiptCodeWord`) first (longest match first so specific phrases win)
        $wordMatchedCust = null;
        $bestWordLen = 0;
        foreach ($allCusts as $cRow) {
            $cObj = $cRow['_parsed'] ?? (json_decode($cRow['data'], true) ?: []);
            if (!empty($cObj['deleted'])) continue;
            $codeWord = trim((string)($cObj['receiptCodeWord'] ?? ''));
            if (empty($codeWord)) continue;

            $pattern = '/(?:^|[\s#:\-_.,\/|()0-9])' . preg_quote($codeWord, '/') . '(?:$|[\s#:\-_.,\/|()0-9])/ui';
            if (mb_strtolower($cleanCustId) === mb_strtolower($codeWord) ||
                mb_strtolower($custName) === mb_strtolower($codeWord) ||
                preg_match($pattern, $combinedCodePool)) {
                $len = mb_strlen($codeWord);
                if ($len > $bestWordLen) {
                    $bestWordLen = $len;
                    $fId = $cRow['frontend_id'] ?: ($cObj['id'] ?? '');
                    $codeNum = $cObj['receiptCodeNum'] ?? '';
                    $wordMatchedCust = [
                        'row' => $cRow,
                        'id' => $fId,
                        'name' => $cObj['name'] ?? $cRow['search_name'] ?? $custName,
                        'phone' => $cObj['whatsapp'] ?: ($cObj['phone'] ?: $custPhone),
                        'matched_by' => 'code_word',
                        'matched_code' => ($codeNum ? '#' . $codeNum . ' - ' : '') . $codeWord,
                        'sender_phone' => $custPhone
                    ];
                }
            }
        }
        if ($wordMatchedCust) {
            return $wordMatchedCust;
        }

        // 0b. Check Numeric Code (`receiptCodeNum`: 1..infinity)
        // Look for explicit code patterns (e.g. "#105", "كود 105", "حساب 105") OR standalone short number tokens in routingHint / cleanCustId / custName
        $candidateCodeNums = [];
        $cleanIdDigits = normalizeInboundDigits($cleanCustId);
        if (preg_match('/^[1-9][0-9]{0,5}$/', $cleanIdDigits)) {
            $candidateCodeNums[] = (int)$cleanIdDigits;
        }
        $cleanNameDigits = normalizeInboundDigits($custName);
        if (preg_match('/^#?([1-9][0-9]{0,5})$/', $cleanNameDigits, $mNameCode)) {
            $candidateCodeNums[] = (int)$mNameCode[1];
        }
        if (!empty($routingHint)) {
            $normHint = normalizeInboundDigits($routingHint);
            // Explicit prefix like: كود 105 / #105 / رقم الكود 105 / حساب 105
            if (preg_match_all('/(?:كود|الكود|حساب|رقم\s*الكود|رمز|#)\s*[:\-]?\s*([1-9][0-9]{0,5})(?!\d)/ui', $normHint, $mExplicit)) {
                foreach ($mExplicit[1] as $numStr) {
                    $candidateCodeNums[] = (int)$numStr;
                }
            }
            // Standalone number in caption/notes (1 to 4 digits, not preceded by مبلغ/قيمة/عملية/سند)
            // Strip out known system tags ("رقم العملية: ...", "بالسند ...") first so we only inspect user-written caption/notes
            $userHintOnly = preg_replace('/(?:تم الفحص التلقائي|رقم العملية|مسجل بالسند|تم التنزيل في الحسابات).*$/u', '', $normHint);
            if (preg_match_all('/(?<!مبلغ\s)(?<!قيمة\s)(?<!عملية\s)(?:^|[\s|,\-\/])#?([1-9][0-9]{0,4})(?:$|[\s|,\-\/])/u', trim($userHintOnly), $mStandalone)) {
                foreach ($mStandalone[1] as $numStr) {
                    $candidateCodeNums[] = (int)$numStr;
                }
            }
        }

        $candidateCodeNums = array_values(array_unique($candidateCodeNums));
        if (!empty($candidateCodeNums)) {
            foreach ($candidateCodeNums as $targetCodeNum) {
                foreach ($allCusts as $cRow) {
                    $cObj = $cRow['_parsed'] ?? (json_decode($cRow['data'], true) ?: []);
                    if (!empty($cObj['deleted'])) continue;
                    $cNum = isset($cObj['receiptCodeNum']) ? (int)$cObj['receiptCodeNum'] : 0;
                    if ($cNum >= 1 && $cNum === $targetCodeNum) {
                        $fId = $cRow['frontend_id'] ?: ($cObj['id'] ?? '');
                        $codeWord = trim((string)($cObj['receiptCodeWord'] ?? ''));
                        return [
                            'row' => $cRow,
                            'id' => $fId,
                            'name' => $cObj['name'] ?? $cRow['search_name'] ?? $custName,
                            'phone' => $cObj['whatsapp'] ?: ($cObj['phone'] ?: $custPhone),
                            'matched_by' => 'code_num',
                            'matched_code' => '#' . $cNum . ($codeWord ? ' - ' . $codeWord : ''),
                            'sender_phone' => $custPhone
                        ];
                    }
                }
            }
        }
    }

    // 1. Direct match by frontend_id
    if (!empty($cleanCustId)) {
        foreach ($allCusts as $cRow) {
            $cObj = $cRow['_parsed'] ?? (json_decode($cRow['data'], true) ?: []);
            $fId = $cRow['frontend_id'] ?: ($cObj['id'] ?? '');
            if ($fId === $cleanCustId) {
                return [
                    'row' => $cRow,
                    'id' => $fId,
                    'name' => $cObj['name'] ?? $cRow['search_name'] ?? $custName,
                    'phone' => $cObj['whatsapp'] ?? $cObj['phone'] ?? $custPhone
                ];
            }
        }
    }

    // 2. Match by WhatsApp Phone Number (Primary phone/whatsapp/LID OR Delegate subPhones!)
    if (strlen($cleanPhone) >= 7) {
        $last9 = substr($cleanPhone, -9);
        foreach ($allCusts as $cRow) {
            $cObj = $cRow['_parsed'] ?? (json_decode($cRow['data'], true) ?: []);
            if (!empty($cObj['deleted'])) continue;
            $candPhones = [
                $cRow['search_phone'] ?? '',
                $cObj['whatsapp'] ?? '',
                $cObj['phone'] ?? '',
                $cObj['mobile'] ?? '',
                $cObj['whatsappLid'] ?? ''
            ];
            foreach ($candPhones as $cp) {
                $cpDigits = preg_replace('/\D/', '', normalizeInboundDigits((string)$cp));
                if (empty($cpDigits)) continue;
                if ($cpDigits === $cleanPhone || (strlen($cpDigits) >= 9 && substr($cpDigits, -9) === $last9)) {
                    $fId = $cRow['frontend_id'] ?: ($cObj['id'] ?? '');
                    return [
                        'row' => $cRow,
                        'id' => $fId,
                        'name' => $cObj['name'] ?? $cRow['search_name'] ?? $custName,
                        'phone' => $cObj['whatsapp'] ?: ($cObj['phone'] ?: $custPhone),
                        'matched_by' => 'primary_phone',
                        'sender_phone' => $custPhone
                    ];
                }
            }

            // Also check Delegate / Sub-Sender Phones (`subPhones`)
            if (!empty($cObj['subPhones']) && is_array($cObj['subPhones'])) {
                foreach ($cObj['subPhones'] as $sp) {
                    $spPhone = is_array($sp) ? ($sp['phone'] ?? '') : (string)$sp;
                    $spLabel = is_array($sp) ? ($sp['label'] ?? '') : '';
                    $spDigits = preg_replace('/\D/', '', normalizeInboundDigits((string)$spPhone));
                    if (empty($spDigits)) continue;
                    if ($spDigits === $cleanPhone || (strlen($spDigits) >= 9 && substr($spDigits, -9) === $last9)) {
                        $fId = $cRow['frontend_id'] ?: ($cObj['id'] ?? '');
                        return [
                            'row' => $cRow,
                            'id' => $fId,
                            'name' => $cObj['name'] ?? $cRow['search_name'] ?? $custName,
                            'phone' => $cObj['whatsapp'] ?: ($cObj['phone'] ?: $custPhone),
                            'matched_by' => 'sub_phone',
                            'delegate_label' => $spLabel,
                            'sender_phone' => $custPhone
                        ];
                    }
                }
            }
        }
    }

    // 3. Match by Arabic or English Transliterated Customer Name (e.g. "Mahdi Mohammed Mekki" -> "مهدي مكي")
    if (!empty($custName) && $custName !== 'زبون مباشر' && !str_starts_with($custName, 'عميل واتساب') && $custName !== 'عميل غير معروف') {
        $enToArMap = [
            'mahdi' => 'مهدي', 'mehdi' => 'مهدي', 'mahdy' => 'مهدي',
            'mohammed' => 'محمد', 'mohamed' => 'محمد', 'muhammad' => 'محمد', 'mohammad' => 'محمد', 'mhmd' => 'محمد',
            'mekki' => 'مكي', 'makki' => 'مكي', 'maki' => 'مكي', 'meki' => 'مكي', 'مكى' => 'مكي',
            'ahmed' => 'احمد', 'ahmad' => 'احمد', 'أحمد' => 'احمد', 'إبراهيم' => 'ابراهيم',
            'omer' => 'عمر', 'omar' => 'عمر', 'umar' => 'عمر',
            'ali' => 'علي', 'aly' => 'علي', 'على' => 'علي',
            'hassan' => 'حسن', 'hasan' => 'حسن',
            'osman' => 'عثمان', 'othman' => 'عثمان', 'uthman' => 'عثمان',
            'amar' => 'عمار', 'ammar' => 'عمار',
            'tarig' => 'طارق', 'tariq' => 'طارق', 'tarek' => 'طارق',
            'hams' => 'همس', 'hamas' => 'همس',
            'aya' => 'ايه', 'ayah' => 'ايه', 'آية' => 'ايه', 'آيه' => 'ايه', 'أيه' => 'ايه',
            'shol' => 'شول', 'chol' => 'شول', 'dr' => 'دكتور', 'doctor' => 'دكتور',
            'khalid' => 'خالد', 'khaled' => 'خالد',
            'ibrahim' => 'ابراهيم', 'mustafa' => 'مصطفى', 'mostafa' => 'مصطفى',
            'abdo' => 'عبدو', 'abdu' => 'عبدو', 'abdelrahim' => 'عبدالرحيم', 'abdulrahim' => 'عبدالرحيم'
        ];

        $normalizeTokens = function($str) use ($enToArMap) {
            $s = mb_strtolower(trim((string)$str));
            $s = str_replace(['أ','إ','آ'], 'ا', $s);
            $s = str_replace(['ى'], 'ي', $s);
            $s = str_replace(['ة'], 'ه', $s);
            $rawWords = preg_split('/[\s\-_.,\/]+/u', $s, -1, PREG_SPLIT_NO_EMPTY);
            $tokens = [];
            foreach ($rawWords as $w) {
                if (isset($enToArMap[$w])) {
                    $tokens[] = $enToArMap[$w];
                } elseif (mb_strlen($w) >= 2) {
                    $tokens[] = $w;
                }
            }
            return array_values(array_unique($tokens));
        };

        $inputTokens = $normalizeTokens($custName);

        // 3a. Exact normalized name match
        foreach ($allCusts as $cRow) {
            $cObj = $cRow['_parsed'] ?? (json_decode($cRow['data'], true) ?: []);
            $dbName = trim($cObj['name'] ?? $cRow['search_name'] ?? '');
            if (empty($dbName)) continue;
            if (mb_strtolower($dbName) === mb_strtolower($custName)) {
                $fId = $cRow['frontend_id'] ?: ($cObj['id'] ?? '');
                return [
                    'row' => $cRow,
                    'id' => $fId,
                    'name' => $dbName,
                    'phone' => $cObj['whatsapp'] ?: ($cObj['phone'] ?: $custPhone)
                ];
            }
        }

        // 3b. Token overlap scoring (prioritizing customers whose full name tokens are completely present in inputTokens, e.g. "مهدي مكي" inside "مهدي محمد مكي")
        if (!empty($inputTokens)) {
            $bestMatch = null;
            $bestScore = 0.0;
            foreach ($allCusts as $cRow) {
                $cObj = $cRow['_parsed'] ?? (json_decode($cRow['data'], true) ?: []);
                $dbName = trim($cObj['name'] ?? $cRow['search_name'] ?? '');
                if (empty($dbName)) continue;
                $dbTokens = $normalizeTokens($dbName);
                if (empty($dbTokens)) continue;

                $common = array_intersect($dbTokens, $inputTokens);
                $matchCount = count($common);
                if ($matchCount < 2 && !(count($dbTokens) === 1 && $matchCount === 1 && count($inputTokens) === 1)) {
                    continue;
                }

                // Calculate score: coverage of dbTokens + coverage of inputTokens + first & last token match bonus
                $dbCoverage = $matchCount / count($dbTokens);
                $inCoverage = $matchCount / count($inputTokens);
                $score = ($dbCoverage * 60.0) + ($inCoverage * 30.0) + ($matchCount * 5.0);
                if ($dbTokens[0] === $inputTokens[0]) {
                    $score += 15.0;
                }
                if (end($dbTokens) === end($inputTokens)) {
                    $score += 15.0;
                }

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = [
                        'row' => $cRow,
                        'id' => $cRow['frontend_id'] ?: ($cObj['id'] ?? ''),
                        'name' => $dbName,
                        'phone' => $cObj['whatsapp'] ?: ($cObj['phone'] ?: $custPhone)
                    ];
                }
            }

            if ($bestMatch && $bestScore >= 75.0) {
                // Save WhatsApp LID on matched customer so future messages match by ID/LID directly
                if (!empty($cleanPhone) && strlen($cleanPhone) >= 12) {
                    try {
                        $mObj = json_decode($bestMatch['row']['data'], true) ?: [];
                        if (($mObj['whatsappLid'] ?? '') !== $cleanPhone) {
                            $mObj['whatsappLid'] = $cleanPhone;
                            $uStmt = $pdo->prepare("UPDATE customers SET data = ? WHERE id = ?");
                            $uStmt->execute([json_encode($mObj, JSON_UNESCAPED_UNICODE), $bestMatch['row']['id']]);
                        }
                    } catch (Exception $e) {}
                }
                return $bestMatch;
            }
        }
    }

    return ['row' => null, 'id' => $cleanCustId, 'name' => $custName, 'phone' => $custPhone];
}

// -------------------------------------------------------------
// Helper: Record System Admin Notification
// -------------------------------------------------------------
function recordAdminInboundNotification($pdo, $tenantId, $notifItem) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS ui_preferences (
            tenant_id VARCHAR(50) NOT NULL,
            pref_key VARCHAR(100) NOT NULL,
            pref_value LONGTEXT,
            PRIMARY KEY (tenant_id, pref_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $stmt = $pdo->prepare("SELECT pref_value FROM ui_preferences WHERE tenant_id = ? AND pref_key = 'admin_inbound_notifications' LIMIT 1");
        $stmt->execute([$tenantId]);
        $existingJson = $stmt->fetchColumn();
        $list = $existingJson ? (json_decode($existingJson, true) ?: []) : [];
        if (!is_array($list)) $list = [];

        array_unshift($list, $notifItem);
        if (count($list) > 50) {
            $list = array_slice($list, 0, 50);
        }

        $encoded = json_encode($list, JSON_UNESCAPED_UNICODE);
        $up = $pdo->prepare("INSERT INTO ui_preferences (tenant_id, pref_key, pref_value) VALUES (?, 'admin_inbound_notifications', ?) ON DUPLICATE KEY UPDATE pref_value = VALUES(pref_value)");
        $up->execute([$tenantId, $encoded]);
    } catch (Exception $e) {}
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
             WHERE tenant_id = ? AND id != ? AND transfer_no LIKE ? AND (status IN ('verified', 'reconciled') OR (voucher_no IS NOT NULL AND voucher_no != ''))
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
// Helper: Post Verified Inbound Receipt to Accounts (Receipts, Customers, Bank of Khartoum, GL)
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
    $cleanRefDigits = preg_replace('/\D/', '', $transferNo);
    if (strlen($cleanRefDigits) < 6) {
        return ['success' => false, 'error' => 'رقم التحويل يجب ألا يقل عن 6 أرقام لتنزيله في الحسابات'];
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
    $originalSenderPhone = $custPhone;
    $resolvedCust = resolveInboundCustomer($pdo, $tenantId, $custId, $custName, $custPhone, $receipt['notes'] ?? '');
    $matchedCustomerRow = $resolvedCust['row'] ?? null;
    $cleanCustId = $resolvedCust['id'] ?: preg_replace('/^cust-/', '', $custId);
    if (!empty($resolvedCust['name'])) {
        $custName = $resolvedCust['name'];
    }
    if (!empty($resolvedCust['phone'])) {
        $custPhone = $resolvedCust['phone'];
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
        // Compute lowest available gap-aware receiptCodeNum (1..infinity)
        $existingCustRows = fetchAndEnsureCustomerCodes($pdo, $tenantId);
        $usedCodeNums = [];
        foreach ($existingCustRows as $ecr) {
            $ecObj = $ecr['_parsed'] ?? (json_decode($ecr['data'], true) ?: []);
            if (!empty($ecObj['deleted'])) continue;
            $n = isset($ecObj['receiptCodeNum']) ? (int)$ecObj['receiptCodeNum'] : 0;
            if ($n >= 1) $usedCodeNums[$n] = true;
        }
        $nextCodeNum = 1;
        while (isset($usedCodeNums[$nextCodeNum])) {
            $nextCodeNum++;
        }

        $custObj = [
            'id' => $custFrontendId,
            'name' => $custName,
            'receiptCodeNum' => $nextCodeNum,
            'receiptCodeWord' => '',
            'subPhones' => [],
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

    // 2. Always Resolve to Bank of Khartoum (`بنك الخرطوم`) in `banks` table and update balance
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
            if (!empty($bRowName) && (mb_stripos($bRowName, 'الخرطوم') !== false || mb_stripos($bRowName, 'بنكك') !== false)) {
                $matchedBankRow = $bRow;
                break;
            }
        }
    }

    if ($matchedBankRow) {
        $bankObj = json_decode($matchedBankRow['data'], true) ?: [];
        $bankFrontendId = $bankObj['id'] ?? 'B-1781625755439';
        $resolvedBankName = $bankObj['name'] ?? 'بنك الخرطوم';
        $bankObj['currentBalance'] = (float)($bankObj['currentBalance'] ?? 0) + $amount;
        $updBank = $pdo->prepare("UPDATE banks SET data = ? WHERE id = ?");
        $updBank->execute([json_encode($bankObj, JSON_UNESCAPED_UNICODE), $matchedBankRow['id']]);
    } else {
        $bankFrontendId = 'B-1781625755439';
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
    $routingAuditSuffix = '';
    if (!empty($resolvedCust['matched_code'])) {
        $routingAuditSuffix .= ' [كود: ' . $resolvedCust['matched_code'] . ']';
    }
    if (!empty($resolvedCust['delegate_label'])) {
        $routingAuditSuffix .= ' [مرسل: ' . $resolvedCust['delegate_label'] . ']';
    } elseif (!empty($originalSenderPhone) && $originalSenderPhone !== $custPhone) {
        $routingAuditSuffix .= ' [مرسل: ' . $originalSenderPhone . ']';
    }
    $voucherDesc = 'سداد إشعار تحويل بنكي (' . $bankName . ') رقم العملية: ' . $transferNo . ' - العميل: ' . $custName . $routingAuditSuffix . ' إلى ' . $resolvedBankName;
    $itemStatement = 'إشعار تحويل ' . $bankName . ' رقم ' . $transferNo . $routingAuditSuffix;

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
                'statement' => $itemStatement,
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
                        'statement' => 'إشعار وارد #' . $receiptId . ' - ' . $custName . $routingAuditSuffix
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

    // 5. Update `inbound_bank_receipts` with voucher_no, customer_id, customer_name, customer_phone
    $updatedNotes = trim((string)($receipt['notes'] ?? ''));
    $postedTag = '✅ تم التنزيل في الحسابات بالسند ' . $vNo;
    if (mb_strpos($updatedNotes, $vNo) === false) {
        $updatedNotes = !empty($updatedNotes) ? ($updatedNotes . ' | ' . $postedTag) : $postedTag;
    }

    $updInb = $pdo->prepare("UPDATE inbound_bank_receipts 
        SET customer_id = ?, customer_name = ?, customer_phone = ?, voucher_no = ?, status = 'verified', notes = ?, updated_at = NOW() 
        WHERE id = ?");
    $updInb->execute([$custFrontendId, $custName, $custPhone, $vNo, $updatedNotes, $receiptId]);

    $adminNotificationText = "🔔 إشعار لمدير النظام: تم فحص وإضافة إشعار بنكي بمبلغ (" . number_format($amount, 2) . " SDG) رقم العملية (" . $transferNo . ") في حساب (" . $custName . ") إلى حساب (" . $resolvedBankName . ") وحفظه تلقائياً بالسند رقم (" . $vNo . ").";
    recordAdminInboundNotification($pdo, (string)$tenantId, [
        'receipt_id' => (int)$receiptId,
        'voucher_no' => $vNo,
        'customer_id' => $custFrontendId,
        'customer_name' => $custName,
        'bank_name' => $resolvedBankName,
        'amount' => $amount,
        'transfer_no' => $transferNo,
        'message' => $adminNotificationText,
        'created_at' => date('Y-m-d H:i:s')
    ]);

    return [
        'success' => true,
        'already_posted' => false,
        'voucher_no' => $vNo,
        'customer_id' => $custFrontendId,
        'customer_name' => $custName,
        'customer_phone' => $custPhone,
        'bank_id' => $bankFrontendId,
        'bank_name' => $resolvedBankName,
        'voucher' => $voucherObj,
        'customer' => $custObj,
        'bank' => $bankObj,
        'admin_notification' => $adminNotificationText,
        'message' => 'تم فحص الإشعار وتنزيله في حساب (' . $custName . ') إلى (' . $resolvedBankName . ') بنجاح بالسند رقم ' . $vNo
    ];
}

// -------------------------------------------------------------
// Helper: Perform Backend AI OCR on Inbound Receipt Image
// ($fraudCheck = false by default; true ONLY when user requests "فحص التزوير")
// -------------------------------------------------------------
function performBackendReceiptOCR($pdo, $receiptId, $fraudCheck = false) {
    $stmt = $pdo->prepare("SELECT * FROM inbound_bank_receipts WHERE id = ?");
    $stmt->execute([$receiptId]);
    $receipt = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$receipt || empty($receipt['receipt_image'])) {
        return ['success' => false, 'error' => 'الإشعار أو مسار الصورة غير موجود'];
    }

    // Prevent duplicate inspection if already verified & posted (unless on-demand forgery check is requested)
    if (!$fraudCheck && !empty($receipt['voucher_no']) && $receipt['status'] === 'verified' && (float)$receipt['amount'] > 0 && strlen(preg_replace('/\D/', '', (string)$receipt['transfer_no'])) >= 6) {
        return [
            'success' => true,
            'already_inspected' => true,
            'data' => [
                'id' => $receiptId,
                'amount' => (float)$receipt['amount'],
                'transfer_no' => $receipt['transfer_no'],
                'customer_id' => $receipt['customer_id'],
                'customer_name' => $receipt['customer_name'],
                'bank_name' => $receipt['bank_name'],
                'status' => $receipt['status'],
                'issue_reason' => $receipt['issue_reason'],
                'voucher_no' => $receipt['voucher_no'],
                'notes' => $receipt['notes']
            ]
        ];
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
        'doc_hint' => 'transfer',
        'fraud_check' => (bool)$fraudCheck
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

    // If this is an On-Demand Forgery Check ("فحص التزوير عند الطلب فقط"), return forensic result without re-posting
    if ($fraudCheck) {
        $tamperingScore = (int)($d['tampering_score'] ?? 0);
        $isSuspicious = !empty($d['is_suspicious']) || $tamperingScore >= 40;
        $tamperingReasons = trim($d['tampering_reasons'] ?? '');
        if (empty($tamperingReasons)) {
            $tamperingReasons = $isSuspicious
                ? 'تم رصد مؤشرات اشتباه أو تلاعب في بيانات الإشعار'
                : 'الإشعار سليم وأصلي — لم يتم رصد أي تلاعب رقمي أو تعديل في خانات رقم العملية أو المبلغ';
        }
        return [
            'success' => true,
            'fraud_checked' => true,
            'id' => $receiptId,
            'tampering_score' => $tamperingScore,
            'is_suspicious' => $isSuspicious,
            'tampering_reasons' => $tamperingReasons,
            'amount' => $extractedAmount,
            'transfer_no' => $extractedRef,
            'ocr_details' => $d
        ];
    }

    // Validate Transfer Number (>= 6 digits) & Check Duplicate
    $cleanRefDigits = preg_replace('/\D/', '', $extractedRef);
    $status = 'verified';
    $issueReason = null;
    $existingNotes = trim((string)($receipt['notes'] ?? ''));
    $ocrSummaryNote = $summary ?: ('تم الفحص التلقائي بنجاح | رقم العملية: ' . $extractedRef);
    $notes = !empty($existingNotes) ? ($existingNotes . ' | ' . $ocrSummaryNote) : $ocrSummaryNote;

    if (strlen($cleanRefDigits) < 6) {
        $status = 'pending';
        $issueReason = 'invalid_transfer_no_under_6_digits';
        $notes = (!empty($existingNotes) ? $existingNotes . ' | ' : '') . '⚠️ مرفوض: رقم التحويل يجب ألا يقل عن 6 أرقام (' . ($extractedRef ?: 'غير موجود') . ')';
    } elseif ($extractedAmount <= 0) {
        $status = 'pending';
        $issueReason = 'zero_amount';
        $notes = (!empty($existingNotes) ? $existingNotes . ' | ' : '') . '⚠️ المبلغ غير واضح في الإشعار';
    } else {
        $dupCheck = checkTransferConflictInDB($pdo, $tenantId, $extractedRef, (int)$receiptId);
        if ($dupCheck['is_duplicate']) {
            $status = 'duplicate_rejected';
            $issueReason = 'duplicate_voucher_' . ($dupCheck['conflicting_voucher'] ?? '');
            $notes = (!empty($existingNotes) ? $existingNotes . ' | ' : '') . '⚠️ كشف تكرار مالي: رقم العملية ' . $extractedRef . ' مسجل بالسند ' . ($dupCheck['conflicting_voucher'] ?? '');
        }
    }

    // Customer resolution via Receipt Code (Number / Word / Both), WhatsApp phone / Delegate SubPhone, LID, or name
    $custName = $receipt['customer_name'];
    if (!empty($extractedSender) && ($custName === 'زبون مباشر' || str_starts_with($custName, 'عميل واتساب') || $custName === 'amar00105')) {
        $custName = $extractedSender;
    }
    $custId = $receipt['customer_id'];
    $custPhone = $receipt['customer_phone'];

    $ocrRoutingHint = trim($existingNotes . ' ' . ($d['comment'] ?? '') . ' ' . ($d['notes'] ?? '') . ' ' . $summary);
    $resolvedCust = resolveInboundCustomer($pdo, $tenantId, $custId, $custName, $custPhone, $ocrRoutingHint);
    if (!empty($resolvedCust['id'])) {
        $custId = $resolvedCust['id'];
        $custName = $resolvedCust['name'] ?: $custName;
        if (!empty($resolvedCust['phone'])) {
            $custPhone = $resolvedCust['phone'];
        }
    }

    $updateStmt = $pdo->prepare("UPDATE inbound_bank_receipts 
        SET amount = ?, 
            transfer_no = ?, 
            customer_id = ?,
            customer_name = ?,
            customer_phone = ?,
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
        $custPhone,
        $extractedBank,
        $status,
        $issueReason,
        $notes,
        $receiptId
    ]);

    // Automatically post to accounts (from Customer to Bank of Khartoum) if verified
    $postingResult = null;
    if ($status === 'verified' && $extractedAmount > 0 && strlen($cleanRefDigits) >= 6) {
        $postingResult = postInboundReceiptToAccounts($pdo, $receiptId);
        if (!empty($postingResult['voucher_no'])) {
            $custId = $postingResult['customer_id'] ?? $custId;
            $custName = $postingResult['customer_name'] ?? $custName;
            $custPhone = $postingResult['customer_phone'] ?? $custPhone;
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
            'customer_phone' => $custPhone,
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


