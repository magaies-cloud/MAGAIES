<?php
// =============================================================
// MAGAIEES ERP 2026 - Inbound Bank Receipts & Reconciliation API
// منظومة استقبال وفحص إشعارات بنكك والتحصيلات المصرفية الواردة
// =============================================================
@ini_set('display_errors', '0');
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
if ($activeTenantId === 'default' || $activeTenantId === '0' || $activeTenantId === '1' || !is_numeric($activeTenantId)) {
    $activeTenantId = (!empty($GLOBAL_TENANT_ID) && is_numeric($GLOBAL_TENANT_ID) && (int)$GLOBAL_TENANT_ID > 1) ? (string)$GLOBAL_TENANT_ID : '12';
}

// 2. Ensure Table Exists (Cached via stamp file to avoid DDL lock on every poll)
$__inbStamp = sys_get_temp_dir() . '/magaies_inb_table_v2.stamp';
if (!file_exists($__inbStamp)) {
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
        @file_put_contents($__inbStamp, (string)time());
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

// Ensure uploads directory exists
$uploadDir = __DIR__ . '/../uploads/receipts_inbox/' . $activeTenantId . '/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

function isLocalInboundLaptopEnv() {
    $host = strtolower($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '');
    if (strpos($host, 'elsajana.com') !== false) {
        return false;
    }
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false || strpos($host, '192.168.') === 0 || strpos($host, '10.') === 0) {
        return true;
    }
    return (bool)stristr(PHP_OS, 'WIN');
}

// Mirror mutating inbound receipt actions (approvals, bank changes, policy settings) from Local Laptop to 24/7 Cloud Server
if (isLocalInboundLaptopEnv() && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && empty($_SERVER['HTTP_X_FORWARDED_FROM_LOCAL'])) {
    $__cloudSyncActions = [
        'post_to_accounts', 'reconcile_receipt', 'approve_batch', 'change_target_bank',
        'update_status', 'dismiss', 'delete', 'save_statement_policy',
        'update_customer_statement_settings', 'save_customer_pin', 'assign_customer'
    ];
    if (in_array($action, $__cloudSyncActions, true)) {
        $__rawInbBody = file_get_contents('php://input');
        register_shutdown_function(function() use ($action, $activeTenantId, $__rawInbBody) {
            try {
                if (function_exists('finishClientResponseEarly')) {
                    finishClientResponseEarly();
                } elseif (function_exists('fastcgi_finish_request')) {
                    @fastcgi_finish_request();
                }
                $qs = $_SERVER['QUERY_STRING'] ?? '';
                if (strpos($qs, 'action=') === false) {
                    $qs = ($qs ? $qs . '&' : '') . 'action=' . urlencode($action);
                }
                if (strpos($qs, 'tenant_id=') === false) {
                    $qs = ($qs ? $qs . '&' : '') . 'tenant_id=' . (int)$activeTenantId;
                }
                $cloudUrl = 'https://c.elsajana.com/api/inbound_receipts.php?' . $qs;
                $ch = curl_init($cloudUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                if (!empty($_POST)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Forwarded-From-Local: 1']);
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $__rawInbBody ?: json_encode($GLOBALS['jsonData'] ?? [], JSON_UNESCAPED_UNICODE));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'X-Forwarded-From-Local: 1']);
                }
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_exec($ch);
                curl_close($ch);
            } catch (Throwable $e) {}
        });
    }
}

/**
 * Pulls new or updated inbound Bankak receipts from the 24/7 Cloud Server (https://c.elsajana.com)
 * into the Local Laptop's MySQL database and posts any verified receipts to local accounts.
 */
function syncInboundReceiptsFromCloud($pdo, $activeTenantId, $uploadDir) {
    if (!isLocalInboundLaptopEnv() || !empty($_GET['no_cloud_pull'])) {
        return;
    }
    // Throttle cloud polling to once every 8 seconds so local UI stays ultra-fast
    $stampFile = sys_get_temp_dir() . '/magaies_cloud_inb_pull_' . (int)$activeTenantId . '.stamp';
    if (empty($_GET['force_cloud_pull']) && file_exists($stampFile) && (time() - @filemtime($stampFile)) < 8) {
        return;
    }
    @file_put_contents($stampFile, (string)time());

    try {
        $cloudUrl = 'https://c.elsajana.com/api/inbound_receipts.php?action=list&tenant_id=' . (int)$activeTenantId . '&no_cloud_pull=1';
        $ch = curl_init($cloudUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $resp = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || empty($resp)) return;
        $cloudPayload = json_decode($resp, true);
        if (empty($cloudPayload['success']) || empty($cloudPayload['data']) || !is_array($cloudPayload['data'])) return;

        // Load existing local receipts for fast deduplication
        $locStmt = $pdo->prepare("SELECT id, transfer_no, receipt_image, voucher_no, status, issue_reason FROM inbound_bank_receipts WHERE tenant_id = ?");
        $locStmt->execute([$activeTenantId]);
        $localRows = $locStmt->fetchAll(PDO::FETCH_ASSOC);

        $byTransfer = [];
        $byImage = [];
        $byVoucher = [];
        foreach ($localRows as $lr) {
            $tClean = preg_replace('/\D/', '', (string)($lr['transfer_no'] ?? ''));
            if (strlen($tClean) >= 6) $byTransfer[$tClean] = $lr;
            $imgBase = basename((string)($lr['receipt_image'] ?? ''));
            if ($imgBase !== '') $byImage[$imgBase] = $lr;
            $vNo = trim((string)($lr['voucher_no'] ?? ''));
            if ($vNo !== '') $byVoucher[$vNo] = $lr;
        }

        // Process oldest first so vouchers are created in chronological order
        $cloudReceipts = array_reverse($cloudPayload['data']);
        foreach ($cloudReceipts as $cRow) {
            $cTransferClean = preg_replace('/\D/', '', (string)($cRow['transfer_no'] ?? ''));
            $cImgRel = trim((string)($cRow['receipt_image'] ?? ''));
            $cImgBase = basename($cImgRel);
            $cVoucherNo = trim((string)($cRow['voucher_no'] ?? ''));
            $cStatus = (string)($cRow['status'] ?? 'pending');
            $cIssue = $cRow['issue_reason'] ?? null;

            $matchedLocal = null;
            if (strlen($cTransferClean) >= 6 && isset($byTransfer[$cTransferClean])) {
                $matchedLocal = $byTransfer[$cTransferClean];
            } elseif ($cVoucherNo !== '' && isset($byVoucher[$cVoucherNo])) {
                $matchedLocal = $byVoucher[$cVoucherNo];
            } elseif ($cImgBase !== '' && isset($byImage[$cImgBase])) {
                $matchedLocal = $byImage[$cImgBase];
            }

            if (!$matchedLocal) {
                // Ensure receipt image is downloaded locally for new receipts
                if ($cImgRel !== '' && $cImgBase !== '') {
                    $localImgPath = $uploadDir . $cImgBase;
                    if (!file_exists($localImgPath)) {
                        $imgUrl = 'https://c.elsajana.com/' . ltrim($cImgRel, '/');
                        $chImg = curl_init($imgUrl);
                        curl_setopt($chImg, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($chImg, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                        curl_setopt($chImg, CURLOPT_CONNECTTIMEOUT, 2);
                        curl_setopt($chImg, CURLOPT_TIMEOUT, 3);
                        curl_setopt($chImg, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($chImg, CURLOPT_SSL_VERIFYHOST, false);
                        $imgBytes = curl_exec($chImg);
                        $imgCode = (int)curl_getinfo($chImg, CURLINFO_HTTP_CODE);
                        curl_close($chImg);
                        if ($imgCode === 200 && !empty($imgBytes)) {
                            @file_put_contents($localImgPath, $imgBytes);
                        }
                    }
                }

                $ins = $pdo->prepare("INSERT INTO inbound_bank_receipts 
                    (tenant_id, customer_id, customer_name, customer_phone, receipt_image, bank_name, bank_code, transfer_no, amount, currency, status, issue_reason, voucher_no, source, notes, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $ins->execute([
                    $activeTenantId,
                    $cRow['customer_id'] ?? null,
                    $cRow['customer_name'] ?? 'عميل واتساب',
                    $cRow['customer_phone'] ?? null,
                    $cImgRel,
                    $cRow['bank_name'] ?? 'بنك الخرطوم (بنكك)',
                    $cRow['bank_code'] ?? '170',
                    $cRow['transfer_no'] ?? null,
                    (float)($cRow['amount'] ?? 0),
                    $cRow['currency'] ?? 'SDG',
                    $cStatus,
                    $cIssue,
                    $cVoucherNo ?: null,
                    $cRow['source'] ?? 'whatsapp',
                    $cRow['notes'] ?? null,
                    $cRow['created_at'] ?? date('Y-m-d H:i:s')
                ]);
                $newLocalId = (int)$pdo->lastInsertId();
                if ($newLocalId > 0 && (float)($cRow['amount'] ?? 0) > 0 && strlen($cTransferClean) >= 6 && $cStatus !== 'duplicate_rejected') {
                    if ($cStatus === 'verified' || $cStatus === 'reconciled' || $cIssue === 'awaiting_posting_approval') {
                        postInboundReceiptToAccounts($pdo, $newLocalId, [
                            'force_post' => ($cStatus === 'verified' || $cStatus === 'reconciled'),
                            'target_bank_id' => (string)($cRow['bank_code'] ?? ''),
                            'target_bank_name' => (string)($cRow['bank_name'] ?? ''),
                            'suppress_whatsapp_notify' => true
                        ]);
                    }
                }
            } else {
                // If local row was still pending while cloud row got verified/posted, upgrade local row and post to local accounts
                $locStatus = (string)($matchedLocal['status'] ?? 'pending');
                if ($locStatus === 'pending' && ($cStatus === 'verified' || $cStatus === 'reconciled')) {
                    $upd = $pdo->prepare("UPDATE inbound_bank_receipts SET customer_id = COALESCE(NULLIF(?, ''), customer_id), customer_name = COALESCE(NULLIF(?, ''), customer_name), bank_name = COALESCE(NULLIF(?, ''), bank_name), bank_code = COALESCE(NULLIF(?, ''), bank_code), transfer_no = COALESCE(NULLIF(?, ''), transfer_no), amount = IF(? > 0, ?, amount), voucher_no = COALESCE(NULLIF(?, ''), voucher_no), status = ?, issue_reason = ?, notes = COALESCE(NULLIF(?, ''), notes) WHERE id = ?");
                    $amtVal = (float)($cRow['amount'] ?? 0);
                    $upd->execute([
                        (string)($cRow['customer_id'] ?? ''),
                        (string)($cRow['customer_name'] ?? ''),
                        (string)($cRow['bank_name'] ?? ''),
                        (string)($cRow['bank_code'] ?? ''),
                        (string)($cRow['transfer_no'] ?? ''),
                        $amtVal,
                        $amtVal,
                        $cVoucherNo,
                        $cStatus,
                        $cIssue,
                        (string)($cRow['notes'] ?? ''),
                        (int)$matchedLocal['id']
                    ]);
                    postInboundReceiptToAccounts($pdo, (int)$matchedLocal['id'], [
                        'force_post' => true,
                        'target_bank_id' => (string)($cRow['bank_code'] ?? ''),
                        'target_bank_name' => (string)($cRow['bank_name'] ?? ''),
                        'suppress_whatsapp_notify' => true
                    ]);
                }
            }
        }
    } catch (Throwable $e) {
        error_log('Cloud inbound receipt sync notice: ' . $e->getMessage());
    }
}

// 3. Dispatch Handlers
switch ($action) {
    case 'list':
    case 'get_inbound_receipts':
        try {
            syncInboundReceiptsFromCloud($pdo, $activeTenantId, $uploadDir);
            $filterStatus = $_GET['status'] ?? '';
            $sql = "SELECT * FROM inbound_bank_receipts WHERE tenant_id = ?";
            $params = [$activeTenantId];
            if (!empty($filterStatus)) {
                if ($filterStatus === 'verified') {
                    $sql .= " AND status IN ('verified', 'reconciled')";
                } elseif ($filterStatus === 'awaiting_approval') {
                    $sql .= " AND status = 'pending' AND issue_reason = 'awaiting_posting_approval'";
                } else {
                    $sql .= " AND status = ?";
                    $params[] = $filterStatus;
                }
            }
            $sql .= " ORDER BY created_at DESC LIMIT 120";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Compute statistics
            $statsStmt = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM inbound_bank_receipts WHERE tenant_id = ? GROUP BY status");
            $statsStmt->execute([$activeTenantId]);
            $statsRaw = $statsStmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $awaitStmt = $pdo->prepare("SELECT COUNT(*) FROM inbound_bank_receipts WHERE tenant_id = ? AND status = 'pending' AND issue_reason = 'awaiting_posting_approval'");
            $awaitStmt->execute([$activeTenantId]);
            $awaitingApprovalCount = (int)$awaitStmt->fetchColumn();

            $verifiedTotal = (int)($statsRaw['verified'] ?? 0) + (int)($statsRaw['reconciled'] ?? 0);
            $stats = [
                'total' => array_sum($statsRaw),
                'verified' => $verifiedTotal,
                'duplicate_rejected' => (int)($statsRaw['duplicate_rejected'] ?? 0),
                'pending' => (int)($statsRaw['pending'] ?? 0),
                'awaiting_approval' => $awaitingApprovalCount,
                'reconciled' => (int)($statsRaw['reconciled'] ?? 0),
            ];

            // Fetch active banks for Target Bank Selector in UI
            $availableBanks = [];
            try {
                $bStmt = $pdo->prepare("SELECT id, data FROM banks WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
                $bStmt->execute([$activeTenantId]);
                while ($bRow = $bStmt->fetch(PDO::FETCH_ASSOC)) {
                    $bObj = json_decode($bRow['data'] ?? '', true) ?: [];
                    if (!empty($bObj['deleted']) || !empty($bObj['isInactive'])) continue;
                    if (strtolower($bObj['type'] ?? 'bank') !== 'safe') {
                        $availableBanks[] = [
                            'id' => (string)($bObj['id'] ?? ('B-' . $bRow['id'])),
                            'name' => (string)($bObj['name'] ?? $bObj['bankName'] ?? 'بنك'),
                            'branch' => (string)($bObj['branch'] ?? ''),
                            'accountNo' => (string)($bObj['accountNo'] ?? $bObj['iban'] ?? '')
                        ];
                    }
                }
            } catch (Exception $e) {}

            $policy = loadWhatsAppStatementPolicy($pdo, $activeTenantId);

            $vNoStmt = $pdo->prepare("SELECT voucher_no FROM inbound_bank_receipts WHERE tenant_id = ? AND voucher_no IS NOT NULL AND voucher_no != ''");
            $vNoStmt->execute([$activeTenantId]);
            $activeVoucherNos = array_values(array_unique(array_filter($vNoStmt->fetchAll(PDO::FETCH_COLUMN))));

            echo json_encode([
                'success' => true,
                'data' => $rows,
                'stats' => $stats,
                'banks' => $availableBanks,
                'policy' => $policy,
                'active_voucher_nos' => $activeVoucherNos,
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
            $bankName = trim($_POST['bank_name'] ?? $jsonData['bank_name'] ?? '');
            $bankCode = trim($_POST['bank_code'] ?? $jsonData['bank_code'] ?? '');
            $targetBankId = trim($_POST['target_bank_id'] ?? $jsonData['target_bank_id'] ?? '');
            if (!empty($targetBankId)) {
                $bankCode = $targetBankId;
            }
            $transferNo = trim($_POST['transfer_no'] ?? $jsonData['transfer_no'] ?? '');
            $amount = floatval($_POST['amount'] ?? $jsonData['amount'] ?? 0);
            $currency = trim($_POST['currency'] ?? $jsonData['currency'] ?? 'SDG');
            $notes = trim($_POST['notes'] ?? $jsonData['notes'] ?? '');
            $caption = trim($_POST['caption'] ?? $jsonData['caption'] ?? '');
            $customerCodeHint = trim($_POST['customer_code'] ?? $jsonData['customer_code'] ?? '');
            $forcePostUpload = !empty($_POST['force_post']) || !empty($jsonData['force_post']);
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

            // Auto-Match Customer from DB by Receipt Code / Secret PIN, WhatsApp phone / Delegate SubPhone, LID, or Name
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

            // Idempotency guard: if the exact same receipt_image was already uploaded for this tenant (e.g. local endpoint fallback retry), return it directly without inserting a duplicate row
            if (!empty($imagePath)) {
                $dupImgStmt = $pdo->prepare("SELECT * FROM inbound_bank_receipts WHERE tenant_id = ? AND receipt_image = ? ORDER BY id DESC LIMIT 1");
                $dupImgStmt->execute([$activeTenantId, $imagePath]);
                $existingImgRow = $dupImgStmt->fetch(PDO::FETCH_ASSOC);
                if ($existingImgRow) {
                    $existingId = (int)$existingImgRow['id'];
                    $existingVoucher = $existingImgRow['voucher_no'] ?? null;
                    $existingMode = ($existingImgRow['issue_reason'] ?? '') === 'awaiting_posting_approval' ? 'review_first' : 'auto_post';
                    echo json_encode([
                        'success' => true,
                        'id' => $existingId,
                        'status' => $existingImgRow['status'],
                        'issue_reason' => $existingImgRow['issue_reason'],
                        'approval_mode' => $existingMode,
                        'amount' => (float)($existingImgRow['amount'] ?? 0),
                        'transfer_no' => $existingImgRow['transfer_no'] ?? '',
                        'customer_id' => $existingImgRow['customer_id'] ?? '',
                        'customer_name' => $existingImgRow['customer_name'] ?? $custName,
                        'customer_phone' => $existingImgRow['customer_phone'] ?? $custPhone,
                        'bank_id' => $existingImgRow['bank_code'] ?? '',
                        'bank_name' => $existingImgRow['bank_name'] ?? 'بنك الخرطوم',
                        'voucher_no' => $existingVoucher,
                        'receipt_image' => $imagePath,
                        'receipt' => $existingImgRow,
                        'message' => 'تم معالجة الإشعار مسبقاً'
                    ]);
                    exit;
                }
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
                $activeTenantId, $custId, $custName, $custPhone, $imagePath, $bankName ?: 'بنك الخرطوم (بنكك)', $bankCode ?: '170', $transferNo, $amount, $currency, $initialStatus, $issueReason, $notes
            ]);
            $newId = (int)$pdo->lastInsertId();

            // Automatic Backend OCR (WITHOUT forgery check — fraud_check = false) if amount or transfer_no is missing and image exists
            $postingResult = null;
            if (($amount <= 0 || strlen($cleanTnoDigits) < 6) && !empty($imagePath)) {
                $ocrRes = performBackendReceiptOCR($pdo, $newId, false, [
                    'force_post' => $forcePostUpload,
                    'target_bank_id' => $targetBankId,
                    'target_bank_name' => $bankName
                ]);
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
                $postingResult = postInboundReceiptToAccounts($pdo, $newId, [
                    'force_post' => $forcePostUpload,
                    'target_bank_id' => $targetBankId,
                    'target_bank_name' => $bankName
                ]);
            }

            // Reload final receipt row
            $finalStmt = $pdo->prepare("SELECT * FROM inbound_bank_receipts WHERE id = ?");
            $finalStmt->execute([$newId]);
            $finalRow = $finalStmt->fetch(PDO::FETCH_ASSOC);

            $finalVoucherNo = $finalRow['voucher_no'] ?? ($postingResult['voucher_no'] ?? null);
            $finalCustName = $finalRow['customer_name'] ?? $custName;
            $finalAmount = (float)($finalRow['amount'] ?? $amount);
            $finalTransferNo = $finalRow['transfer_no'] ?? $transferNo;
            $finalBankName = $postingResult['bank_name'] ?? ($finalRow['bank_name'] ?? ($bankName ?: 'بنك الخرطوم'));
            $finalBankId = $postingResult['bank_id'] ?? ($finalRow['bank_code'] ?? '');
            $finalApprovalMode = $postingResult['approval_mode'] ?? (($finalRow['issue_reason'] ?? '') === 'awaiting_posting_approval' ? 'review_first' : 'auto_post');

            $adminNotificationText = $postingResult['admin_notification'] ?? null;

            echo json_encode([
                'success' => true,
                'id' => $newId,
                'status' => $finalRow['status'] ?? $initialStatus,
                'issue_reason' => $finalRow['issue_reason'] ?? $issueReason,
                'approval_mode' => $finalApprovalMode,
                'amount' => $finalAmount,
                'transfer_no' => $finalTransferNo,
                'customer_id' => $finalRow['customer_id'] ?? $custId,
                'customer_name' => $finalCustName,
                'customer_phone' => $finalRow['customer_phone'] ?? $custPhone,
                'bank_id' => $finalBankId,
                'bank_name' => $finalBankName,
                'voucher_no' => $finalVoucherNo,
                'receipt_image' => $imagePath,
                'posting' => $postingResult,
                'receipt' => $finalRow,
                'admin_notification' => $adminNotificationText,
                'message' => ($finalApprovalMode === 'review_first')
                    ? ('تم استلام وفحص إشعار (' . $finalCustName . ') وحفظه بانتظار التمرير والترحيل في حساب (' . $finalBankName . ')')
                    : (!empty($finalVoucherNo)
                        ? ('تم فحص إشعار البنك وتنزيله في حساب (' . $finalCustName . ') إلى (' . $finalBankName . ') بالسند رقم ' . $finalVoucherNo)
                        : 'تم استلام وحفظ وفحص إشعار بنكك بنجاح')
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
            $ocrRes = performBackendReceiptOCR($pdo, $id, false);
            echo json_encode($ocrRes);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'check_forgery':
    case 'forensic_check':
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
        try {
            $pStmt = $pdo->prepare("SELECT id FROM inbound_bank_receipts WHERE tenant_id = ? AND (status = 'pending' AND (issue_reason IS NULL OR issue_reason != 'awaiting_posting_approval')) ORDER BY id ASC LIMIT 50");
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
                'scanned_count' => count($results),
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
            $targetBankId = trim($_GET['target_bank_id'] ?? $_POST['target_bank_id'] ?? $jsonData['target_bank_id'] ?? $_POST['bank_id'] ?? $jsonData['bank_id'] ?? '');
            $targetBankName = trim($_GET['target_bank_name'] ?? $_POST['target_bank_name'] ?? $jsonData['target_bank_name'] ?? $_POST['bank_name'] ?? $jsonData['bank_name'] ?? '');
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'معرّف الإشعار مطلوب']);
                exit;
            }
            // Explicit manual post/pass from UI always forces immediate posting (`force_post => true`)
            $postRes = postInboundReceiptToAccounts($pdo, $id, [
                'force_post' => true,
                'target_bank_id' => $targetBankId,
                'target_bank_name' => $targetBankName,
                'notify_customer_whatsapp' => true
            ]);
            echo json_encode($postRes);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'approve_batch':
        try {
            $ids = $_POST['receipt_ids'] ?? $jsonData['receipt_ids'] ?? $_POST['ids'] ?? $jsonData['ids'] ?? [];
            if (is_string($ids)) {
                $decodedIds = json_decode($ids, true);
                $ids = is_array($decodedIds) ? $decodedIds : array_filter(array_map('trim', explode(',', $ids)));
            }
            $targetBankId = trim($_POST['target_bank_id'] ?? $jsonData['target_bank_id'] ?? $_POST['bank_id'] ?? $jsonData['bank_id'] ?? '');
            $targetBankName = trim($_POST['target_bank_name'] ?? $jsonData['target_bank_name'] ?? $_POST['bank_name'] ?? $jsonData['bank_name'] ?? '');
            if (!is_array($ids) || empty($ids)) {
                echo json_encode(['success' => false, 'error' => 'قائمة الإشعارات مطلوبة للتمرير المجمع']);
                exit;
            }
            $postedResults = [];
            foreach ($ids as $rawId) {
                $rid = (int)$rawId;
                if ($rid <= 0) continue;
                $res = postInboundReceiptToAccounts($pdo, $rid, [
                    'force_post' => true,
                    'target_bank_id' => $targetBankId,
                    'target_bank_name' => $targetBankName,
                    'notify_customer_whatsapp' => true
                ]);
                if (!empty($res['success'])) {
                    $postedResults[] = $res;
                }
            }
            echo json_encode([
                'success' => true,
                'approved_count' => count($postedResults),
                'results' => $postedResults,
                'message' => '✅ تم تمرير وترحيل (' . count($postedResults) . ') إشعار في الحسابات بنجاح!'
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'change_target_bank':
        try {
            $id = intval($_POST['id'] ?? $jsonData['id'] ?? 0);
            $targetBankId = trim($_POST['bank_id'] ?? $jsonData['bank_id'] ?? $_POST['target_bank_id'] ?? $jsonData['target_bank_id'] ?? '');
            $targetBankName = trim($_POST['bank_name'] ?? $jsonData['bank_name'] ?? $_POST['target_bank_name'] ?? $jsonData['target_bank_name'] ?? '');
            if (!$id || (empty($targetBankId) && empty($targetBankName))) {
                echo json_encode(['success' => false, 'error' => 'معرّف الإشعار والبنك المستلم مطلوبان']);
                exit;
            }
            $rStmt = $pdo->prepare("SELECT * FROM inbound_bank_receipts WHERE id = ? AND tenant_id = ? LIMIT 1");
            $rStmt->execute([$id, $activeTenantId]);
            $recRow = $rStmt->fetch(PDO::FETCH_ASSOC);
            if (!$recRow) {
                echo json_encode(['success' => false, 'error' => 'الإشعار غير موجود']);
                exit;
            }

            // If receipt had a voucher, reverse old bank balance if it was already posted
            $oldVoucherNo = trim((string)($recRow['voucher_no'] ?? ''));
            $wasAlreadyPosted = ($recRow['status'] === 'verified' || $recRow['status'] === 'reconciled');
            if (!empty($oldVoucherNo)) {
                $vChk = $pdo->prepare("SELECT data FROM receipts WHERE tenant_id = ? AND (frontend_id = ? OR receipt_no = ?) LIMIT 1");
                $vChk->execute([$activeTenantId, $oldVoucherNo, $oldVoucherNo]);
                $oldVJson = $vChk->fetchColumn();
                if ($oldVJson) {
                    $oldVObj = json_decode($oldVJson, true) ?: [];
                    $wasAlreadyPosted = !empty($oldVObj['isPosted']);
                    if ($wasAlreadyPosted) {
                        // Reverse old bank balance and customer balance before re-posting to new bank
                        $oldBankId = preg_replace('/^bank-/', '', (string)($oldVObj['toAccount'] ?? $oldVObj['mainAccount'] ?? ''));
                        $oldAmt = (float)($recRow['amount'] ?? 0);
                        if ($oldAmt > 0 && !empty($oldBankId)) {
                            $bRowsStmt = $pdo->prepare("SELECT id, data FROM banks WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                            $bRowsStmt->execute([$activeTenantId]);
                            while ($br = $bRowsStmt->fetch(PDO::FETCH_ASSOC)) {
                                $bObj = json_decode($br['data'], true) ?: [];
                                if (($bObj['id'] ?? '') === $oldBankId) {
                                    $bObj['currentBalance'] = (float)($bObj['currentBalance'] ?? 0) - $oldAmt;
                                    $pdo->prepare("UPDATE banks SET data = ? WHERE id = ?")->execute([json_encode($bObj, JSON_UNESCAPED_UNICODE), $br['id']]);
                                    break;
                                }
                            }
                        }
                        $oldCustId = preg_replace('/^cust-/', '', (string)($recRow['customer_id'] ?? ''));
                        if ($oldAmt > 0 && !empty($oldCustId)) {
                            $cRowsStmt = $pdo->prepare("SELECT id, data FROM customers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                            $cRowsStmt->execute([$activeTenantId]);
                            while ($cr = $cRowsStmt->fetch(PDO::FETCH_ASSOC)) {
                                $cObj = json_decode($cr['data'], true) ?: [];
                                if (($cObj['id'] ?? '') === $oldCustId) {
                                    $cObj['currentBalance'] = (float)($cObj['currentBalance'] ?? 0) + $oldAmt;
                                    $pdo->prepare("UPDATE customers SET data = ? WHERE id = ?")->execute([json_encode($cObj, JSON_UNESCAPED_UNICODE), $cr['id']]);
                                    break;
                                }
                            }
                        }
                    }
                }
                $pdo->prepare("DELETE FROM receipts WHERE tenant_id = ? AND (frontend_id = ? OR receipt_no = ?)")->execute([$activeTenantId, $oldVoucherNo, $oldVoucherNo]);
                $pdo->prepare("DELETE FROM gl_entries WHERE tenant_id = ? AND data LIKE ?")->execute([$activeTenantId, '%' . $oldVoucherNo . '%']);
            }

            $uStmt = $pdo->prepare("UPDATE inbound_bank_receipts SET bank_code = ?, bank_name = ?, voucher_no = NULL WHERE id = ?");
            $uStmt->execute([$targetBankId ?: '170', $targetBankName ?: 'بنك الخرطوم', $id]);

            $postRes = null;
            if ((float)($recRow['amount'] ?? 0) > 0 && strlen(preg_replace('/\D/', '', (string)($recRow['transfer_no'] ?? ''))) >= 6 && $recRow['status'] !== 'duplicate_rejected') {
                $postRes = postInboundReceiptToAccounts($pdo, $id, [
                    'force_post' => $wasAlreadyPosted,
                    'target_bank_id' => $targetBankId,
                    'target_bank_name' => $targetBankName
                ]);
            }

            echo json_encode([
                'success' => true,
                'id' => $id,
                'bank_id' => $postRes['bank_id'] ?? $targetBankId,
                'bank_name' => $postRes['bank_name'] ?? $targetBankName,
                'posting' => $postRes,
                'message' => '🏦 تم تحديد البنك المستلم (' . ($postRes['bank_name'] ?? $targetBankName) . ') للإشعار بنجاح'
            ]);
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
            $rStmt = $pdo->prepare("SELECT * FROM inbound_bank_receipts WHERE id = ? AND tenant_id = ? LIMIT 1");
            $rStmt->execute([$id, $activeTenantId]);
            $recRow = $rStmt->fetch(PDO::FETCH_ASSOC);
            $oldVoucherNo = '';
            if ($recRow) {
                $oldVoucherNo = trim((string)($recRow['voucher_no'] ?? ''));
                $oldCustId = preg_replace('/^cust-/', '', (string)($recRow['customer_id'] ?? ''));
                $oldAmt = (float)($recRow['amount'] ?? 0);
                if (!empty($oldVoucherNo)) {
                    $vChk = $pdo->prepare("SELECT data FROM receipts WHERE tenant_id = ? AND (frontend_id = ? OR receipt_no = ?) LIMIT 1");
                    $vChk->execute([$activeTenantId, $oldVoucherNo, $oldVoucherNo]);
                    $oldVJson = $vChk->fetchColumn();
                    if ($oldVJson) {
                        $oldVObj = json_decode($oldVJson, true) ?: [];
                        if (!empty($oldVObj['isPosted']) && $oldAmt > 0) {
                            if (!empty($oldCustId)) {
                                $cRowsStmt = $pdo->prepare("SELECT id, data FROM customers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                                $cRowsStmt->execute([$activeTenantId]);
                                while ($cr = $cRowsStmt->fetch(PDO::FETCH_ASSOC)) {
                                    $cObj = json_decode($cr['data'], true) ?: [];
                                    if (($cObj['id'] ?? '') === $oldCustId) {
                                        $cObj['currentBalance'] = (float)($cObj['currentBalance'] ?? 0) + $oldAmt;
                                        $pdo->prepare("UPDATE customers SET data = ? WHERE id = ?")->execute([json_encode($cObj, JSON_UNESCAPED_UNICODE), $cr['id']]);
                                        break;
                                    }
                                }
                            }
                            $oldBankId = preg_replace('/^bank-/', '', (string)($oldVObj['toAccount'] ?? $oldVObj['mainAccount'] ?? ''));
                            if (!empty($oldBankId)) {
                                $bRowsStmt = $pdo->prepare("SELECT id, data FROM banks WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                                $bRowsStmt->execute([$activeTenantId]);
                                while ($br = $bRowsStmt->fetch(PDO::FETCH_ASSOC)) {
                                    $bObj = json_decode($br['data'], true) ?: [];
                                    if (($bObj['id'] ?? '') === $oldBankId) {
                                        $bObj['currentBalance'] = (float)($bObj['currentBalance'] ?? 0) - $oldAmt;
                                        $pdo->prepare("UPDATE banks SET data = ? WHERE id = ?")->execute([json_encode($bObj, JSON_UNESCAPED_UNICODE), $br['id']]);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    $pdo->prepare("DELETE FROM receipts WHERE tenant_id = ? AND (frontend_id = ? OR receipt_no = ?)")->execute([$activeTenantId, $oldVoucherNo, $oldVoucherNo]);
                    $pdo->prepare("DELETE FROM gl_entries WHERE tenant_id = ? AND data LIKE ?")->execute([$activeTenantId, '%' . $oldVoucherNo . '%']);
                }
            }
            // Also delete any receipts or gl_entries referencing this inboundReceiptId
            $pdo->prepare("DELETE FROM receipts WHERE tenant_id = ? AND JSON_UNQUOTE(JSON_EXTRACT(data, '$.inboundReceiptId')) = ?")->execute([$activeTenantId, (string)$id]);

            $stmt = $pdo->prepare("DELETE FROM inbound_bank_receipts WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $activeTenantId]);
            echo json_encode([
                'success' => true,
                'deleted_voucher_no' => $oldVoucherNo,
                'message' => 'تم حذف الإشعار وسنده المرتبط من الصندوق والحسابات'
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'assign_customer_by_code':
        try {
            $id = intval($_POST['id'] ?? $jsonData['id'] ?? 0);
            $codeOrName = trim($_POST['customer_code'] ?? $jsonData['customer_code'] ?? '');
            $customWaPhone = trim($_POST['customer_phone'] ?? $jsonData['customer_phone'] ?? '');
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

            // If user provided/updated customer's WhatsApp phone number, save it immediately to customer's record in DB
            $custUpdatedInDb = false;
            if (!empty($customWaPhone) && strlen(preg_replace('/\D/', '', $customWaPhone)) >= 7) {
                $targetCustObj['whatsapp'] = $customWaPhone;
                if (empty($targetCustObj['phone'])) {
                    $targetCustObj['phone'] = $customWaPhone;
                }
                $custUpdatedInDb = true;
            }

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
                    $custUpdatedInDb = true;
                    $linkedPhoneMsg = ' + تم ربط رقم المرسل (' . $senderPhone . ') بحساب العميل تلقائياً';
                }
            }
            if ($custUpdatedInDb) {
                $updSub = $pdo->prepare("UPDATE customers SET data = ? WHERE id = ?");
                $updSub->execute([json_encode($targetCustObj, JSON_UNESCAPED_UNICODE), $targetCustRow['id']]);
            }

            // Reverse old customer balance & old bank balance if it was previously posted to another customer
            $oldVoucherNo = trim((string)($recRow['voucher_no'] ?? ''));
            $oldCustId = preg_replace('/^cust-/', '', (string)($recRow['customer_id'] ?? ''));
            $oldAmt = (float)($recRow['amount'] ?? 0);
            if (!empty($oldVoucherNo)) {
                $vChk = $pdo->prepare("SELECT data FROM receipts WHERE tenant_id = ? AND (frontend_id = ? OR receipt_no = ?) LIMIT 1");
                $vChk->execute([$activeTenantId, $oldVoucherNo, $oldVoucherNo]);
                $oldVJson = $vChk->fetchColumn();
                if ($oldVJson) {
                    $oldVObj = json_decode($oldVJson, true) ?: [];
                    if (!empty($oldVObj['isPosted']) && $oldAmt > 0) {
                        if (!empty($oldCustId)) {
                            $cRowsStmt = $pdo->prepare("SELECT id, data FROM customers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                            $cRowsStmt->execute([$activeTenantId]);
                            while ($cr = $cRowsStmt->fetch(PDO::FETCH_ASSOC)) {
                                $cObj = json_decode($cr['data'], true) ?: [];
                                if (($cObj['id'] ?? '') === $oldCustId) {
                                    $cObj['currentBalance'] = (float)($cObj['currentBalance'] ?? 0) + $oldAmt;
                                    $pdo->prepare("UPDATE customers SET data = ? WHERE id = ?")->execute([json_encode($cObj, JSON_UNESCAPED_UNICODE), $cr['id']]);
                                    break;
                                }
                            }
                        }
                        $oldBankId = preg_replace('/^bank-/', '', (string)($oldVObj['toAccount'] ?? $oldVObj['mainAccount'] ?? ''));
                        if (!empty($oldBankId)) {
                            $bRowsStmt = $pdo->prepare("SELECT id, data FROM banks WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                            $bRowsStmt->execute([$activeTenantId]);
                            while ($br = $bRowsStmt->fetch(PDO::FETCH_ASSOC)) {
                                $bObj = json_decode($br['data'], true) ?: [];
                                if (($bObj['id'] ?? '') === $oldBankId) {
                                    $bObj['currentBalance'] = (float)($bObj['currentBalance'] ?? 0) - $oldAmt;
                                    $pdo->prepare("UPDATE banks SET data = ? WHERE id = ?")->execute([json_encode($bObj, JSON_UNESCAPED_UNICODE), $br['id']]);
                                    break;
                                }
                            }
                        }
                    }
                }
                $delOldRec = $pdo->prepare("DELETE FROM receipts WHERE tenant_id = ? AND (frontend_id = ? OR receipt_no = ?)");
                $delOldRec->execute([$activeTenantId, $oldVoucherNo, $oldVoucherNo]);
                $delOldGl = $pdo->prepare("DELETE FROM gl_entries WHERE tenant_id = ? AND data LIKE ?");
                $delOldGl->execute([$activeTenantId, '%' . $oldVoucherNo . '%']);
            }

            $effectiveStatus = $recRow['status'];
            if ($effectiveStatus === 'duplicate_rejected' && !empty($recRow['transfer_no'])) {
                if (reassignMisroutedInboundConflictIfDifferentCustomer($pdo, $activeTenantId, $recRow['transfer_no'], $id, $targetCustId, $targetCustName)) {
                    $effectiveStatus = 'verified';
                }
            }

            // Update receipt customer_id and customer_name
            $newNotes = trim((string)($recRow['notes'] ?? ''));
            if ($effectiveStatus === 'verified') {
                $newNotes = preg_replace('/\s*\|\s*⚠️ كشف تكرار مالي:.*$/u', '', $newNotes);
            }
            $codeBadge = $resolved['matched_code'] ?? $codeOrName;
            $routeNote = '🎯 توجيه لحساب: ' . $targetCustName . ' (كود: ' . $codeBadge . ')';
            if (mb_strpos($newNotes, $routeNote) === false) {
                $newNotes = !empty($newNotes) ? ($newNotes . ' | ' . $routeNote) : $routeNote;
            }

            $effectivePhone = !empty($customWaPhone) ? $customWaPhone : ($targetCustObj['whatsapp'] ?: ($targetCustObj['phone'] ?: $senderPhone));
            $uStmt = $pdo->prepare("UPDATE inbound_bank_receipts SET customer_id = ?, customer_name = ?, customer_phone = ?, status = ?, voucher_no = NULL, notes = ? WHERE id = ?");
            $uStmt->execute([$targetCustId, $targetCustName, $effectivePhone, $effectiveStatus, $newNotes, $id]);

            $postRes = postInboundReceiptToAccounts($pdo, $id, [
                'force_post' => true,
                'notify_customer_whatsapp' => true
            ]);
            $waNotifNote = !empty($postRes['whatsapp_notified']) ? ' + 📲 تم إرسال إشعار واتساب للعميل' : '';
            echo json_encode([
                'success' => true,
                'customer_id' => $targetCustId,
                'customer_name' => $targetCustName,
                'customer_phone' => $effectivePhone,
                'whatsapp_notified' => !empty($postRes['whatsapp_notified']),
                'posting' => $postRes,
                'customer' => $postRes['customer'] ?? $targetCustObj,
                'message' => '✅ تم توجيه الإشعار وتنزيله في حساب (' . $targetCustName . ') بنجاح' . $linkedPhoneMsg . $waNotifNote
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'assign_batch_by_code':
        try {
            $receiptIds = $_POST['receipt_ids'] ?? $jsonData['receipt_ids'] ?? [];
            $codeOrName = trim($_POST['customer_code'] ?? $jsonData['customer_code'] ?? '');
            if (empty($receiptIds) || !is_array($receiptIds) || empty($codeOrName)) {
                echo json_encode(['success' => false, 'error' => 'قائمة الإشعارات والباسورد/الكود مطلوبان']);
                exit;
            }

            $resolved = resolveInboundCustomer($pdo, $activeTenantId, $codeOrName, $codeOrName, '', $codeOrName);
            if (empty($resolved['row']) || empty($resolved['id'])) {
                echo json_encode(['success' => false, 'error' => 'لم يتم العثور على حساب بهذا الباسورد أو الكود: ' . $codeOrName]);
                exit;
            }

            $targetCustId = $resolved['id'];
            $targetCustName = $resolved['name'];
            $codeBadge = $resolved['matched_code'] ?? $codeOrName;
            $updatedCount = 0;
            $lastBankName = 'بنك الخرطوم';

            foreach ($receiptIds as $rawId) {
                $id = (int)$rawId;
                if ($id <= 0) continue;
                $rStmt = $pdo->prepare("SELECT * FROM inbound_bank_receipts WHERE id = ? AND tenant_id = ? LIMIT 1");
                $rStmt->execute([$id, $activeTenantId]);
                $recRow = $rStmt->fetch(PDO::FETCH_ASSOC);
                if (!$recRow) continue;

                // Reverse old customer balance if it was already posted to a different customer
                $oldVoucherNo = trim((string)($recRow['voucher_no'] ?? ''));
                $oldCustId = preg_replace('/^cust-/', '', (string)($recRow['customer_id'] ?? ''));
                $oldAmt = (float)($recRow['amount'] ?? 0);
                if (!empty($oldVoucherNo)) {
                    $vChk = $pdo->prepare("SELECT data FROM receipts WHERE tenant_id = ? AND (frontend_id = ? OR receipt_no = ?) LIMIT 1");
                    $vChk->execute([$activeTenantId, $oldVoucherNo, $oldVoucherNo]);
                    $oldVJson = $vChk->fetchColumn();
                    if ($oldVJson) {
                        $oldVObj = json_decode($oldVJson, true) ?: [];
                        if (!empty($oldVObj['isPosted']) && $oldAmt > 0) {
                            // Reverse old customer balance and old bank balance before re-posting
                            if (!empty($oldCustId)) {
                                $cRowsStmt = $pdo->prepare("SELECT id, data FROM customers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                                $cRowsStmt->execute([$activeTenantId]);
                                while ($cr = $cRowsStmt->fetch(PDO::FETCH_ASSOC)) {
                                    $cObj = json_decode($cr['data'], true) ?: [];
                                    if (($cObj['id'] ?? '') === $oldCustId) {
                                        $cObj['currentBalance'] = (float)($cObj['currentBalance'] ?? 0) + $oldAmt;
                                        $pdo->prepare("UPDATE customers SET data = ? WHERE id = ?")->execute([json_encode($cObj, JSON_UNESCAPED_UNICODE), $cr['id']]);
                                        break;
                                    }
                                }
                            }
                            $oldBankId = preg_replace('/^bank-/', '', (string)($oldVObj['toAccount'] ?? $oldVObj['mainAccount'] ?? ''));
                            if (!empty($oldBankId)) {
                                $bRowsStmt = $pdo->prepare("SELECT id, data FROM banks WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                                $bRowsStmt->execute([$activeTenantId]);
                                while ($br = $bRowsStmt->fetch(PDO::FETCH_ASSOC)) {
                                    $bObj = json_decode($br['data'], true) ?: [];
                                    if (($bObj['id'] ?? '') === $oldBankId) {
                                        $bObj['currentBalance'] = (float)($bObj['currentBalance'] ?? 0) - $oldAmt;
                                        $pdo->prepare("UPDATE banks SET data = ? WHERE id = ?")->execute([json_encode($bObj, JSON_UNESCAPED_UNICODE), $br['id']]);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    $pdo->prepare("DELETE FROM receipts WHERE tenant_id = ? AND (frontend_id = ? OR receipt_no = ?)")->execute([$activeTenantId, $oldVoucherNo, $oldVoucherNo]);
                    $pdo->prepare("DELETE FROM gl_entries WHERE tenant_id = ? AND data LIKE ?")->execute([$activeTenantId, '%' . $oldVoucherNo . '%']);
                }

                // If this receipt was marked duplicate_rejected because of an earlier misrouted receipt on a DIFFERENT customer, reassign it now!
                $effectiveStatus = $recRow['status'];
                if ($effectiveStatus === 'duplicate_rejected' && !empty($recRow['transfer_no'])) {
                    if (reassignMisroutedInboundConflictIfDifferentCustomer($pdo, $activeTenantId, $recRow['transfer_no'], $id, $targetCustId, $targetCustName)) {
                        $effectiveStatus = 'verified';
                    }
                }

                $newNotes = trim((string)($recRow['notes'] ?? ''));
                if ($effectiveStatus === 'verified') {
                    $newNotes = preg_replace('/\s*\|\s*⚠️ كشف تكرار مالي:.*$/u', '', $newNotes);
                }
                $routeNote = '🎯 توجيه بالباسورد لحساب: ' . $targetCustName . ' (' . $codeBadge . ')';
                if (mb_strpos($newNotes, $routeNote) === false) {
                    $newNotes = !empty($newNotes) ? ($newNotes . ' | ' . $routeNote) : $routeNote;
                }

                $uStmt = $pdo->prepare("UPDATE inbound_bank_receipts SET customer_id = ?, customer_name = ?, status = ?, voucher_no = NULL, notes = ? WHERE id = ?");
                $uStmt->execute([$targetCustId, $targetCustName, $effectiveStatus, $newNotes, $id]);

                if ($effectiveStatus !== 'duplicate_rejected' && $oldAmt > 0 && strlen(preg_replace('/\D/', '', (string)($recRow['transfer_no'] ?? ''))) >= 6) {
                    $postRes = postInboundReceiptToAccounts($pdo, $id);
                    if (!empty($postRes['bank_name'])) {
                        $lastBankName = $postRes['bank_name'];
                    }
                }
                $updatedCount++;
            }

            echo json_encode([
                'success' => true,
                'updated_count' => $updatedCount,
                'customer_id' => $targetCustId,
                'customer_name' => $targetCustName,
                'bank_name' => $lastBankName,
                'message' => '✅ تم توجيه (' . $updatedCount . ') إشعار بنكي إلى حساب (' . $targetCustName . ') بنجاح!'
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'whatsapp_statement_request':
        try {
            $msgText = trim((string)($_GET['message_text'] ?? $_POST['message_text'] ?? $jsonData['message_text'] ?? $_GET['message'] ?? $_POST['message'] ?? $jsonData['message'] ?? $_GET['text'] ?? $_POST['text'] ?? $jsonData['text'] ?? ''));
            $senderPhone = trim((string)($_GET['sender_phone'] ?? $_POST['sender_phone'] ?? $jsonData['sender_phone'] ?? ''));
            $senderName = trim((string)($_GET['sender_name'] ?? $_POST['sender_name'] ?? $jsonData['sender_name'] ?? ''));
            $stmtResult = handleWhatsAppStatementRequest($pdo, $activeTenantId, $msgText, $senderPhone, $senderName);
            echo json_encode($stmtResult, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
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

    // Also collect existing supplier PINs so customer PINs never collide with supplier PINs
    $usedNums = [];
    $usedPins = [];
    try {
        $sStmt = $pdo->prepare("SELECT data FROM suppliers WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
        $sStmt->execute([$tenantId]);
        while ($sr = $sStmt->fetch(PDO::FETCH_ASSOC)) {
            $sObj = json_decode($sr['data'] ?? '', true) ?: [];
            if (!empty($sObj['deleted'])) continue;
            $sPin = preg_replace('/[^0-9]/', '', normalizeInboundDigits((string)($sObj['statementPin'] ?? '')));
            if ($sPin !== '') $usedPins[$sPin] = true;
            $sNum = isset($sObj['receiptCodeNum']) ? (int)normalizeInboundDigits($sObj['receiptCodeNum']) : 0;
            if ($sNum >= 1) $usedNums[$sNum] = true;
        }
    } catch (Exception $e) {}

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

        $pin = preg_replace('/[^0-9]/', '', normalizeInboundDigits((string)($cObj['statementPin'] ?? '')));
        if ($pin !== '' && !isset($usedPins[$pin])) {
            $usedPins[$pin] = true;
            $cObj['statementPin'] = $pin;
        } else {
            $cObj['statementPin'] = '';
        }
        $r['_parsed'] = $cObj;
    }
    unset($r);

    $nextCandidate = 1;
    $nextPinSeed = 101;
    foreach ($rows as &$r) {
        $cObj = $r['_parsed'] ?? [];
        if (!empty($cObj['deleted'])) continue;
        $rowChanged = false;
        if (empty($cObj['receiptCodeNum']) || $cObj['receiptCodeNum'] < 1) {
            while (isset($usedNums[$nextCandidate])) {
                $nextCandidate++;
            }
            $cObj['receiptCodeNum'] = $nextCandidate;
            $usedNums[$nextCandidate] = true;
            $rowChanged = true;
        }
        if (empty($cObj['statementPin'])) {
            // Generate a unique 3-or-4-digit password (PIN)
            $candPin = '';
            for ($try = 0; $try < 50; $try++) {
                $randPin = (string)rand(100, 999);
                if (!isset($usedPins[$randPin])) {
                    $candPin = $randPin;
                    break;
                }
            }
            if ($candPin === '') {
                while (isset($usedPins[(string)$nextPinSeed])) {
                    $nextPinSeed++;
                }
                $candPin = (string)$nextPinSeed;
            }
            $cObj['statementPin'] = $candPin;
            $usedPins[$candPin] = true;
            $rowChanged = true;
        }
        if ($rowChanged) {
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
// Helper: Resolve Customer from Password/PIN (`statementPin` - Top Priority, overrides sender phone!),
// Legacy Code, WhatsApp Primary Phone / Delegate SubPhones / LID, or Arabic & English Name
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

    $normNameFn = function($str) {
        $s = mb_strtolower(trim((string)$str));
        $s = str_replace(['أ','إ','آ'], 'ا', $s);
        $s = str_replace(['ى'], 'ي', $s);
        $s = str_replace(['ة'], 'ه', $s);
        $s = preg_replace('/\s+/u', ' ', $s);
        return $s;
    };

    // 0. PRIORITY 0: Match by Customer Secret Password / PIN (`statementPin`) written with the receipt!
    // Even if the sender's WhatsApp phone is registered to another customer (e.g., Osman sending a receipt with Abdullah's password),
    // the written Password/PIN takes absolute priority and routes the receipt to the password owner's account!
    $combinedCodePool = normalizeInboundDigits(trim($routingHint . ' ' . $cleanCustId . ' ' . $custName));
    $extractedWaCaptionText = '';
    if (!empty($combinedCodePool)) {
        $candidateTokens = [];
        $explicitTokens = [];
        $cleanIdDigits = trim(normalizeInboundDigits($cleanCustId));
        if (preg_match('/^[0-9]{1,8}$/', $cleanIdDigits)) {
            $candidateTokens[] = $cleanIdDigits;
            $explicitTokens[$cleanIdDigits] = true;
        }
        $cleanNameDigits = trim(normalizeInboundDigits($custName));
        if (preg_match('/^#?([0-9]{1,8})$/', $cleanNameDigits, $mNameCode)) {
            $candidateTokens[] = $mNameCode[1];
            $explicitTokens[$mNameCode[1]] = true;
        }
        if (!empty($routingHint)) {
            $normHint = normalizeInboundDigits($routingHint);
            // Strip out known system tags & OCR summaries first so we ONLY inspect user-written caption/notes
            $userHintOnly = preg_replace('/(?:⚠️|✅|تم الفحص التلقائي|رقم العملية|مسجل بالسند|تم التنزيل في الحسابات|تحويل بنك|تحويل من).*$/u', '', $normHint);
            // Strip formatted currency amounts (e.g., "5,000,000.00 SDG", "700,000 جنيه") and decimal/comma numbers & dates
            $userHintOnly = preg_replace('/\b\d[\d,]*(\.\d+)?\s*(?:SDG|USD|جنيه|ج\.س)\b/iu', ' ', $userHintOnly);
            $userHintOnly = preg_replace('/\b\d{4}[-\/]\d{1,2}[-\/]\d{1,2}\b|\b\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}\b/u', ' ', $userHintOnly);
            $userHintOnly = preg_replace('/\b\d+[,.]\d+(?:[,.]\d+)*\b/u', ' ', $userHintOnly);

            // Extract parenthesized caption from "إشعار تحويل وارد من واتساب (532)" if present
            if (preg_match('/إشعار تحويل وارد من واتساب\s*\(([^)]+)\)/u', $userHintOnly, $mWaCap)) {
                $waCap = trim($mWaCap[1]);
                $extractedWaCaptionText = $waCap;
                if (preg_match('/^\s*#?([0-9]{1,6})\s*$/u', $waCap, $mPureCap)) {
                    $candidateTokens[] = $mPureCap[1];
                    $explicitTokens[$mPureCap[1]] = true;
                } elseif (preg_match_all('/(?<!مبلغ\s)(?<!قيمة\s)(?<!عملية\s)(?<![0-9.,\/\-])#?([0-9]{1,6})(?![0-9.,\/\-])/u', $waCap, $mCapNums)) {
                    foreach ($mCapNums[1] as $tStr) {
                        $candidateTokens[] = $tStr;
                        $explicitTokens[$tStr] = true;
                    }
                }
            }
            // Explicit prefix like: باسورد 532 / سري 532 / الرقم السري 532 / كود 532 / #532
            if (preg_match_all('/(?:باسورد|الباسورد|سري|السري|الرقم\s*السري|رمز|كود|الكود|حساب|pin|#)\s*[:\-]?\s*([0-9]{1,6})(?![0-9.,\/\-])/ui', $userHintOnly, $mExplicit)) {
                foreach ($mExplicit[1] as $numStr) {
                    $candidateTokens[] = $numStr;
                    $explicitTokens[$numStr] = true;
                }
            }
            // Standalone number in caption/notes (1 to 6 digits, NEVER adjacent to commas/dots/slashes/hyphens)
            $cleanUserHint = str_replace(['إشعار تحويل وارد من واتساب', 'إشعار تحويل وارد تلقائياً عبر محادثة واتساب', '(', ')'], ' ', $userHintOnly);
            $trimmedCleanHint = trim($cleanUserHint);
            if (preg_match('/^#?([0-9]{1,6})$/u', $trimmedCleanHint, $mPureHint)) {
                $candidateTokens[] = $mPureHint[1];
                $explicitTokens[$mPureHint[1]] = true;
            } elseif (preg_match_all('/(?<!مبلغ\s)(?<!قيمة\s)(?<!عملية\s)(?<![0-9.,\/\-])#?([0-9]{1,6})(?![0-9.,\/\-])/u', $trimmedCleanHint, $mStandalone)) {
                foreach ($mStandalone[1] as $numStr) {
                    $candidateTokens[] = $numStr;
                }
            }
        }

        $candidateTokens = array_values(array_unique($candidateTokens));

        // 0a. Match by Customer Password / PIN (`statementPin`) FIRST!
        if (!empty($candidateTokens)) {
            foreach ($candidateTokens as $tok) {
                foreach ($allCusts as $cRow) {
                    $cObj = $cRow['_parsed'] ?? (json_decode($cRow['data'], true) ?: []);
                    if (!empty($cObj['deleted'])) continue;
                    $cPin = preg_replace('/[^0-9]/', '', normalizeInboundDigits((string)($cObj['statementPin'] ?? '')));
                    if ($cPin !== '' && $cPin === (string)$tok) {
                        $fId = $cRow['frontend_id'] ?: ($cObj['id'] ?? '');
                        return [
                            'row' => $cRow,
                            'id' => $fId,
                            'name' => $cObj['name'] ?? $cRow['search_name'] ?? $custName,
                            'phone' => $cObj['whatsapp'] ?: ($cObj['phone'] ?: $custPhone),
                            'matched_by' => 'statement_pin',
                            'matched_code' => '🔐 باسورد ' . $cPin,
                            'sender_phone' => $custPhone
                        ];
                    }
                }
            }

            // 0b. Secondary fallback: Match by combined PIN+Code, or explicit `receiptCodeNum`
            foreach ($candidateTokens as $tok) {
                $targetCodeNum = (int)$tok;
                $isExplicitTok = !empty($explicitTokens[$tok]);
                foreach ($allCusts as $cRow) {
                    $cObj = $cRow['_parsed'] ?? (json_decode($cRow['data'], true) ?: []);
                    if (!empty($cObj['deleted'])) continue;
                    $cNum = isset($cObj['receiptCodeNum']) ? (int)$cObj['receiptCodeNum'] : 0;
                    $cPin = preg_replace('/[^0-9]/', '', normalizeInboundDigits((string)($cObj['statementPin'] ?? '')));
                    $isCombinedMatch = ($cPin !== '' && $cNum >= 1 && ($tok === ($cPin . $cNum) || $tok === ($cNum . $cPin)));
                    // Only match bare 1-digit receiptCodeNum if the user explicitly typed ONLY that code or prefixed it
                    $isBareCodeMatch = ($cNum >= 1 && $cNum === $targetCodeNum && (string)$cNum === (string)$tok && ($isExplicitTok || $cNum >= 10));
                    if ($isCombinedMatch || $isBareCodeMatch) {
                        $fId = $cRow['frontend_id'] ?: ($cObj['id'] ?? '');
                        return [
                            'row' => $cRow,
                            'id' => $fId,
                            'name' => $cObj['name'] ?? $cRow['search_name'] ?? $custName,
                            'phone' => $cObj['whatsapp'] ?: ($cObj['phone'] ?: $custPhone),
                            'matched_by' => 'code_num',
                            'matched_code' => '🔐 ' . ($cPin ?: '#' . $cNum),
                            'sender_phone' => $custPhone
                        ];
                    }
                }
            }
        }
    }

    // 1. Direct match by frontend_id or DB id
    if (!empty($cleanCustId)) {
        foreach ($allCusts as $cRow) {
            $cObj = $cRow['_parsed'] ?? (json_decode($cRow['data'], true) ?: []);
            if (!empty($cObj['deleted'])) continue;
            $fId = $cRow['frontend_id'] ?: ($cObj['id'] ?? '');
            if ($fId === $cleanCustId || (string)$cRow['id'] === $cleanCustId) {
                return [
                    'row' => $cRow,
                    'id' => $fId,
                    'name' => $cObj['name'] ?? $cRow['search_name'] ?? $custName,
                    'phone' => $cObj['whatsapp'] ?: ($cObj['phone'] ?: $custPhone),
                    'matched_by' => 'customer_id'
                ];
            }
        }
    }

    // 1.5. PRIORITY 1.5: Match by Explicit Customer Name or `receiptCodeWord` BEFORE Sender Phone!
    // This guarantees that when a customer name is selected in the UI or written in the WhatsApp caption,
    // it routes to that exact customer even if the WhatsApp message came from another phone number.
    $nameCandidates = [];
    if (!empty($extractedWaCaptionText) && !preg_match('/^#?\d+$/', $extractedWaCaptionText)) {
        $nameCandidates[] = $extractedWaCaptionText;
    }
    if (!empty($custName) && $custName !== 'زبون مباشر' && $custName !== 'عميل غير معروف' && $custName !== 'عميل بنكك' && !str_starts_with($custName, 'عميل واتساب') && !str_starts_with($custName, 'عميل بنكك')) {
        $nameCandidates[] = $custName;
    }
    if (!empty($routingHint) && mb_strpos($routingHint, 'إشعار تحويل') === false && mb_strpos($routingHint, 'تم الفحص') === false && mb_strpos($routingHint, '🎯') === false && !preg_match('/^#?\d+$/', $routingHint)) {
        $nameCandidates[] = $routingHint;
    }
    $nameCandidates = array_values(array_unique(array_filter(array_map('trim', $nameCandidates))));

    foreach ($nameCandidates as $candName) {
        $normCand = $normNameFn($candName);
        if (mb_strlen($normCand) < 2) continue;

        // 1.5a: Exact match on customer name or receiptCodeWord
        foreach ($allCusts as $cRow) {
            $cObj = $cRow['_parsed'] ?? (json_decode($cRow['data'], true) ?: []);
            if (!empty($cObj['deleted'])) continue;
            $dbName = trim($cObj['name'] ?? $cRow['search_name'] ?? '');
            $codeWord = trim($cObj['receiptCodeWord'] ?? '');
            if (!empty($dbName) && $normNameFn($dbName) === $normCand) {
                $fId = $cRow['frontend_id'] ?: ($cObj['id'] ?? '');
                return [
                    'row' => $cRow,
                    'id' => $fId,
                    'name' => $dbName,
                    'phone' => $cObj['whatsapp'] ?: ($cObj['phone'] ?: $custPhone),
                    'matched_by' => 'exact_name',
                    'matched_code' => $dbName
                ];
            }
            if (!empty($codeWord) && $normNameFn($codeWord) === $normCand) {
                $fId = $cRow['frontend_id'] ?: ($cObj['id'] ?? '');
                return [
                    'row' => $cRow,
                    'id' => $fId,
                    'name' => $dbName ?: $custName,
                    'phone' => $cObj['whatsapp'] ?: ($cObj['phone'] ?: $custPhone),
                    'matched_by' => 'code_word',
                    'matched_code' => $codeWord
                ];
            }
        }

        // 1.5b: Unambiguous partial/prefix match when user writes/selects a multi-character Arabic name (e.g., "عمر طارق")
        if (mb_strlen($normCand) >= 4 && preg_match('/\p{Arabic}/u', $normCand)) {
            $partialMatches = [];
            foreach ($allCusts as $cRow) {
                $cObj = $cRow['_parsed'] ?? (json_decode($cRow['data'], true) ?: []);
                if (!empty($cObj['deleted'])) continue;
                $dbName = trim($cObj['name'] ?? $cRow['search_name'] ?? '');
                if (empty($dbName)) continue;
                $normDb = $normNameFn($dbName);
                if (mb_strpos($normDb, $normCand) !== false || mb_strpos($normCand, $normDb) !== false) {
                    $partialMatches[] = ['row' => $cRow, 'obj' => $cObj, 'name' => $dbName];
                }
            }
            if (count($partialMatches) === 1) {
                $pm = $partialMatches[0];
                $fId = $pm['row']['frontend_id'] ?: ($pm['obj']['id'] ?? '');
                return [
                    'row' => $pm['row'],
                    'id' => $fId,
                    'name' => $pm['name'],
                    'phone' => $pm['obj']['whatsapp'] ?: ($pm['obj']['phone'] ?: $custPhone),
                    'matched_by' => 'partial_name',
                    'matched_code' => $pm['name']
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

    return ['row' => null, 'id' => $cleanCustId, 'name' => $custName, 'phone' => $custPhone];
}

// -------------------------------------------------------------
// Helper: Reassign a previously misrouted receipt if it was recorded under a different customer
// -------------------------------------------------------------
function reassignMisroutedInboundConflictIfDifferentCustomer($pdo, $tenantId, $transferNo, $excludeReceiptId, $targetCustId, $targetCustName) {
    $cleanDigits = preg_replace('/\D/', '', (string)$transferNo);
    $suffix = strlen($cleanDigits) >= 8 ? substr($cleanDigits, -8) : substr($cleanDigits, -6);
    if (strlen($suffix) < 6 || empty($targetCustId)) {
        return false;
    }
    $cleanTargetId = preg_replace('/^(cust|supp?)-/i', '', trim((string)$targetCustId));

    $stmt = $pdo->prepare("SELECT * FROM inbound_bank_receipts WHERE tenant_id = ? AND id != ? AND transfer_no LIKE ?");
    $stmt->execute([$tenantId, (int)$excludeReceiptId, '%' . $suffix]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // First check if it's already posted to the SAME target customer (prevent double-counting in the same customer's account)
    foreach ($rows as $oldRow) {
        $oldCustId = preg_replace('/^(cust|supp?)-/i', '', trim((string)($oldRow['customer_id'] ?? '')));
        $oldVNo = trim((string)($oldRow['voucher_no'] ?? ''));
        if ($oldCustId !== '' && $oldCustId === $cleanTargetId && (!empty($oldVNo) || in_array($oldRow['status'] ?? '', ['verified', 'reconciled'], true))) {
            // Verify that the voucher actually still exists in receipts table
            if (!empty($oldVNo)) {
                $vChkSame = $pdo->prepare("SELECT COUNT(*) FROM receipts WHERE tenant_id = ? AND (frontend_id = ? OR receipt_no = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) AND (is_deleted = 0 OR is_deleted IS NULL)");
                $vChkSame->execute([$tenantId, $oldVNo, $oldVNo, $oldVNo]);
                if ((int)$vChkSame->fetchColumn() > 0) {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    $reassignedAny = false;
    foreach ($rows as $oldRow) {
        $oldId = (int)$oldRow['id'];
        $oldVoucherNo = trim((string)($oldRow['voucher_no'] ?? ''));
        $oldCustId = preg_replace('/^(cust|supp?)-/i', '', trim((string)($oldRow['customer_id'] ?? '')));
        $oldAmt = (float)($oldRow['amount'] ?? 0);

        if (!empty($oldVoucherNo)) {
            $vChk = $pdo->prepare("SELECT id, data FROM receipts WHERE tenant_id = ? AND (frontend_id = ? OR receipt_no = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
            $vChk->execute([$tenantId, $oldVoucherNo, $oldVoucherNo, $oldVoucherNo]);
            $oldVRow = $vChk->fetch(PDO::FETCH_ASSOC);
            if ($oldVRow && !empty($oldVRow['data'])) {
                $oldVObj = json_decode($oldVRow['data'], true) ?: [];
                if (!empty($oldVObj['isPosted']) && $oldAmt > 0) {
                    if (!empty($oldCustId)) {
                        $cRowsStmt = $pdo->prepare("SELECT id, data FROM customers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                        $cRowsStmt->execute([$tenantId]);
                        while ($cr = $cRowsStmt->fetch(PDO::FETCH_ASSOC)) {
                            $cObj = json_decode($cr['data'], true) ?: [];
                            if (preg_replace('/^(cust|supp?)-/i', '', (string)($cObj['id'] ?? '')) === $oldCustId) {
                                $cObj['currentBalance'] = (float)($cObj['currentBalance'] ?? 0) + $oldAmt;
                                $pdo->prepare("UPDATE customers SET data = ? WHERE id = ?")->execute([json_encode($cObj, JSON_UNESCAPED_UNICODE), $cr['id']]);
                                break;
                            }
                        }
                    }
                    $oldBankId = preg_replace('/^bank-/', '', (string)($oldVObj['toAccount'] ?? $oldVObj['mainAccount'] ?? ''));
                    if (!empty($oldBankId)) {
                        $bRowsStmt = $pdo->prepare("SELECT id, data FROM banks WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                        $bRowsStmt->execute([$tenantId]);
                        while ($br = $bRowsStmt->fetch(PDO::FETCH_ASSOC)) {
                            $bObj = json_decode($br['data'], true) ?: [];
                            if (($bObj['id'] ?? '') === $oldBankId) {
                                $bObj['currentBalance'] = (float)($bObj['currentBalance'] ?? 0) - $oldAmt;
                                $pdo->prepare("UPDATE banks SET data = ? WHERE id = ?")->execute([json_encode($bObj, JSON_UNESCAPED_UNICODE), $br['id']]);
                                break;
                            }
                        }
                    }
                }
                $pdo->prepare("DELETE FROM receipts WHERE id = ?")->execute([$oldVRow['id']]);
            }
            $pdo->prepare("DELETE FROM receipts WHERE tenant_id = ? AND (frontend_id = ? OR receipt_no = ?)")->execute([$tenantId, $oldVoucherNo, $oldVoucherNo]);
            $pdo->prepare("DELETE FROM gl_entries WHERE tenant_id = ? AND data LIKE ?")->execute([$tenantId, '%' . $oldVoucherNo . '%']);
            $reassignedAny = true;
        }
        $pdo->prepare("DELETE FROM inbound_bank_receipts WHERE id = ? AND tenant_id = ?")->execute([$oldId, $tenantId]);
        $reassignedAny = true;
    }

    // Also check `receipts` table directly for any voucher with this transferNo that was recorded under a DIFFERENT customer
    try {
        $vDirectStmt = $pdo->prepare("SELECT id, data FROM receipts WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) AND data LIKE ?");
        $vDirectStmt->execute([$tenantId, '%' . $suffix . '%']);
        while ($vDirectRow = $vDirectStmt->fetch(PDO::FETCH_ASSOC)) {
            $vd = json_decode($vDirectRow['data'], true);
            if (!is_array($vd) || !empty($vd['deleted'])) continue;
            $vCustRaw = $vd['items'][0]['account'] ?? $vd['items'][0]['accountId'] ?? $vd['fromAccount'] ?? '';
            $vCustClean = preg_replace('/^(cust|supp?)-/i', '', trim((string)$vCustRaw));
            if ($vCustClean !== '' && $vCustClean !== $cleanTargetId) {
                $vNo = $vd['voucherNo'] ?? $vd['id'] ?? '';
                $vAmt = (float)($vd['totalAmount'] ?? $vd['amountLocal'] ?? 0);
                if (!empty($vd['isPosted']) && $vAmt > 0) {
                    $cRowsStmt = $pdo->prepare("SELECT id, data FROM customers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                    $cRowsStmt->execute([$tenantId]);
                    while ($cr = $cRowsStmt->fetch(PDO::FETCH_ASSOC)) {
                        $cObj = json_decode($cr['data'], true) ?: [];
                        if (preg_replace('/^(cust|supp?)-/i', '', (string)($cObj['id'] ?? '')) === $vCustClean) {
                            $cObj['currentBalance'] = (float)($cObj['currentBalance'] ?? 0) + $vAmt;
                            $pdo->prepare("UPDATE customers SET data = ? WHERE id = ?")->execute([json_encode($cObj, JSON_UNESCAPED_UNICODE), $cr['id']]);
                            break;
                        }
                    }
                    $oldBankId = preg_replace('/^bank-/', '', (string)($vd['toAccount'] ?? $vd['mainAccount'] ?? ''));
                    if (!empty($oldBankId)) {
                        $bRowsStmt = $pdo->prepare("SELECT id, data FROM banks WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                        $bRowsStmt->execute([$tenantId]);
                        while ($br = $bRowsStmt->fetch(PDO::FETCH_ASSOC)) {
                            $bObj = json_decode($br['data'], true) ?: [];
                            if (($bObj['id'] ?? '') === $oldBankId) {
                                $bObj['currentBalance'] = (float)($bObj['currentBalance'] ?? 0) - $vAmt;
                                $pdo->prepare("UPDATE banks SET data = ? WHERE id = ?")->execute([json_encode($bObj, JSON_UNESCAPED_UNICODE), $br['id']]);
                                break;
                            }
                        }
                    }
                }
                $pdo->prepare("DELETE FROM receipts WHERE id = ?")->execute([$vDirectRow['id']]);
                if (!empty($vNo)) {
                    $pdo->prepare("DELETE FROM gl_entries WHERE tenant_id = ? AND data LIKE ?")->execute([$tenantId, '%' . $vNo . '%']);
                }
                $reassignedAny = true;
            }
        }
    } catch (Exception $e) {}

    return $reassignedAny;
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
    $suffix = strlen($cleanDigits) >= 8 ? substr($cleanDigits, -8) : substr($cleanDigits, -6);
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
    
    // 1. Check existing inbound receipts (and verify their voucher still exists in `receipts`!)
    $sql1 = "SELECT id, customer_id, customer_name, voucher_no, amount, created_at, transfer_no 
             FROM inbound_bank_receipts 
             WHERE tenant_id = ? AND id != ? AND transfer_no LIKE ? AND (status IN ('verified', 'reconciled') OR (voucher_no IS NOT NULL AND voucher_no != ''))
             ORDER BY id DESC";
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute([$tenantId, $excludeReceiptId, '%' . $suffix]);
    $hits = $stmt1->fetchAll(PDO::FETCH_ASSOC) ?: [];

    foreach ($hits as $hit) {
        $hitVNo = trim((string)($hit['voucher_no'] ?? ''));
        if ($hitVNo !== '') {
            // Check that the voucher was not deleted from `receipts`
            $chkV = $pdo->prepare("SELECT COUNT(*) FROM receipts WHERE tenant_id = ? AND (frontend_id = ? OR receipt_no = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) AND (is_deleted = 0 OR is_deleted IS NULL)");
            $chkV->execute([$tenantId, $hitVNo, $hitVNo, $hitVNo]);
            if ((int)$chkV->fetchColumn() === 0) {
                // Voucher was deleted from receipts — clean up orphan inbound_bank_receipts record so it never blocks re-upload!
                $pdo->prepare("DELETE FROM inbound_bank_receipts WHERE id = ?")->execute([$hit['id']]);
                continue;
            }
        }
        return [
            'is_duplicate' => true,
            'conflicting_type' => 'inbound_receipt',
            'conflicting_voucher' => !empty($hit['voucher_no']) ? $hit['voucher_no'] : ('إشعار #' . $hit['id']),
            'customer_id' => $hit['customer_id'] ?? '',
            'customer_name' => $hit['customer_name'],
            'date' => date('Y-m-d', strtotime($hit['created_at'])),
            'amount' => (float)$hit['amount']
        ];
    }

    // 2. Check actual receipt voucher tables in MySQL (`receipts` and `single_receipts` only — never block an inbound receipt on a payment voucher!)
    $voucherTables = ['receipts', 'single_receipts'];
    foreach ($voucherTables as $tbl) {
        try {
            $vStmt = $pdo->prepare("SELECT id, data FROM `$tbl` WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) AND data LIKE ? LIMIT 10");
            $vStmt->execute([$tenantId, '%' . $suffix . '%']);
            while ($vHit = $vStmt->fetch(PDO::FETCH_ASSOC)) {
                $vData = json_decode($vHit['data'], true);
                if (!is_array($vData) || !empty($vData['deleted'])) continue;

                $vNo = $vData['voucherNo'] ?? $vData['receiptNo'] ?? $vData['paymentNo'] ?? $vData['invoiceNo'] ?? $vData['id'] ?? ('سند #' . $vHit['id']);
                if (!empty($ownVoucherNo) && (string)$vNo === (string)$ownVoucherNo) {
                    continue;
                }
                if ($excludeReceiptId > 0 && isset($vData['inboundReceiptId']) && (int)$vData['inboundReceiptId'] === (int)$excludeReceiptId) {
                    continue;
                }

                // Verify the suffix actually belongs to a transfer number field inside the receipt voucher
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
                    $cmpLen = strlen($suffix);
                    if (strlen($candDigits) >= $cmpLen && substr($candDigits, -$cmpLen) === $suffix) {
                        $matchedTransfer = true;
                        break;
                    }
                }

                if ($matchedTransfer) {
                    $partyName = $vData['party_name'] ?? $vData['customer_name'] ?? $vData['customerName'] ?? $vData['supplierName'] ?? $vData['accountName'] ?? '';
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
// Helper: Post or Stage Verified Inbound Receipt to Accounts
// Supports Target Bank Selection & Per-Account/Global Approval Mode (`auto_post` vs `review_first`)
// -------------------------------------------------------------
function postInboundReceiptToAccounts($pdo, $receiptId, $options = []) {
    $stmt = $pdo->prepare("SELECT * FROM inbound_bank_receipts WHERE id = ? LIMIT 1");
    $stmt->execute([$receiptId]);
    $receipt = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$receipt) {
        return ['success' => false, 'error' => 'الإشعار غير موجود'];
    }

    $forcePost = !empty($options['force_post']);
    $overrideBankId = trim((string)($options['target_bank_id'] ?? ''));
    $overrideBankName = trim((string)($options['target_bank_name'] ?? ''));

    $tenantId = (int)$receipt['tenant_id'];
    $amount = (float)($receipt['amount'] ?? 0);
    $transferNo = trim((string)($receipt['transfer_no'] ?? ''));
    $custName = trim((string)($receipt['customer_name'] ?? 'عميل بنكك'));
    $custId = trim((string)($receipt['customer_id'] ?? ''));
    $custPhone = trim((string)($receipt['customer_phone'] ?? ''));
    $bankName = trim((string)($receipt['bank_name'] ?? 'بنك الخرطوم (بنكك)'));
    $bankCode = trim((string)($receipt['bank_code'] ?? ''));
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

    $policy = loadWhatsAppStatementPolicy($pdo, $tenantId);

    // Check if already posted to receipts table (Idempotency)
    $existingVoucherNo = trim((string)($receipt['voucher_no'] ?? ''));
    if (!empty($existingVoucherNo)) {
        try {
            $chkV = $pdo->prepare("SELECT data FROM receipts WHERE tenant_id = ? AND (frontend_id = ? OR receipt_no = ?) AND (is_deleted = 0 OR is_deleted IS NULL) LIMIT 1");
            $chkV->execute([$tenantId, $existingVoucherNo, $existingVoucherNo]);
            $existingVJson = $chkV->fetchColumn();
            if ($existingVJson) {
                $existingVObj = json_decode($existingVJson, true);
                $alreadyPostedFlag = !empty($existingVObj['isPosted']);
                // If already fully posted (`isPosted === true`) and no bank change requested, return idempotent success
                if ($alreadyPostedFlag && empty($overrideBankId)) {
                    return [
                        'success' => true,
                        'already_posted' => true,
                        'approval_mode' => 'auto_post',
                        'voucher_no' => $existingVoucherNo,
                        'customer_id' => $custId,
                        'customer_name' => $custName,
                        'bank_id' => preg_replace('/^bank-/', '', (string)($existingVObj['toAccount'] ?? '')),
                        'bank_name' => $existingVObj['items'][0]['bankName'] ?? $bankName,
                        'voucher' => $existingVObj,
                        'message' => 'الإشعار منزل مسبقاً في الحسابات بالسند رقم ' . $existingVoucherNo
                    ];
                }
            }
        } catch (Exception $e) {}
    }

    // 1. Resolve or Auto-Create Customer in `customers` table
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

    $custObj = [];
    if ($matchedCustomerRow) {
        $custObj = json_decode($matchedCustomerRow['data'], true) ?: [];
        $custFrontendId = $custObj['id'] ?? $cleanCustId ?: ('CUST-' . round(microtime(true) * 1000));
        $custName = $custObj['name'] ?? $custName;
        if (empty($custObj['phone']) && !empty($custPhone)) {
            $custObj['phone'] = $custPhone;
        }
    } else {
        $custFrontendId = !empty($cleanCustId) ? $cleanCustId : ('CUST-' . round(microtime(true) * 1000));
        if (empty($custName) || $custName === 'زبون مباشر') {
            $custName = !empty($custPhone) ? ('عميل بنكك (' . $custPhone . ')') : ('عميل بنكك #' . $receiptId);
        }
        $existingCustRows = fetchAndEnsureCustomerCodes($pdo, $tenantId);
        $usedCodeNums = [];
        $usedPins = [];
        foreach ($existingCustRows as $ecr) {
            $ecObj = $ecr['_parsed'] ?? (json_decode($ecr['data'], true) ?: []);
            if (!empty($ecObj['deleted'])) continue;
            $n = isset($ecObj['receiptCodeNum']) ? (int)$ecObj['receiptCodeNum'] : 0;
            if ($n >= 1) $usedCodeNums[$n] = true;
            $p = preg_replace('/\D/', '', (string)($ecObj['statementPin'] ?? ''));
            if ($p !== '') $usedPins[$p] = true;
        }
        $nextCodeNum = 1;
        while (isset($usedCodeNums[$nextCodeNum])) {
            $nextCodeNum++;
        }
        $newPin = (string)rand(100, 999);
        while (isset($usedPins[$newPin])) {
            $newPin = (string)rand(100, 999);
        }

        $custObj = [
            'id' => $custFrontendId,
            'name' => $custName,
            'receiptCodeNum' => $nextCodeNum,
            'statementPin' => $newPin,
            'receiptCodeWord' => '',
            'receiptApprovalMode' => 'auto_post',
            'defaultBankId' => '',
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
            'currentBalance' => 0,
            'creditLimit' => 0,
            'securityLevel' => 1,
            'status' => 'active',
            'notes' => 'تم فتح الحساب تلقائياً من إشعار تحويل بنكي وارد رقم ' . $transferNo,
            'bankAccounts' => [],
            'date' => date('Y-m-d'),
            'createdAt' => gmdate('Y-m-d\TH:i:s.v\Z')
        ];
    }

    // Determine effective Approval Mode (`auto_post` vs `review_first`)
    $globalReceiptMode = $policy['receiptApprovalMode'] ?? 'per_account';
    $custReceiptMode = $custObj['receiptApprovalMode'] ?? 'auto_post';
    if ($forcePost) {
        $effectiveApprovalMode = 'auto_post';
    } elseif ($globalReceiptMode === 'review_first_all') {
        $effectiveApprovalMode = 'review_first';
    } elseif ($globalReceiptMode === 'auto_post_all') {
        $effectiveApprovalMode = 'auto_post';
    } else {
        $effectiveApprovalMode = ($custReceiptMode === 'review_first') ? 'review_first' : 'auto_post';
    }
    $shouldPostNow = ($effectiveApprovalMode === 'auto_post');

    // Update or insert Customer (only deduct balance if $shouldPostNow is true!)
    if ($shouldPostNow) {
        $custObj['currentBalance'] = (float)($custObj['currentBalance'] ?? 0) - $amount;
    }
    if ($matchedCustomerRow) {
        $updCust = $pdo->prepare("UPDATE customers SET data = ? WHERE id = ?");
        $updCust->execute([json_encode($custObj, JSON_UNESCAPED_UNICODE), $matchedCustomerRow['id']]);
    } else {
        $insCust = $pdo->prepare("INSERT INTO customers (tenant_id, customer_id, data) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data)");
        $insCust->execute([$tenantId, $custFrontendId, json_encode($custObj, JSON_UNESCAPED_UNICODE)]);
    }

    // 2. Resolve Target Receiving Bank (`تحديد البنك المستلم للإشعارات`)
    // Priority: 1) $overrideBankId / $overrideBankName -> 2) $receipt['bank_code'] (if bank ID) -> 3) Customer's defaultBankId -> 4) Global Policy defaultReceiptBankId -> 5) Match by name / Bank of Khartoum
    $preferredBankId = $overrideBankId;
    if (empty($preferredBankId) && !empty($bankCode) && $bankCode !== '170') {
        $preferredBankId = $bankCode;
    }
    if (empty($preferredBankId) && !empty($custObj['defaultBankId'])) {
        $preferredBankId = trim((string)$custObj['defaultBankId']);
    }
    if (empty($preferredBankId) && !empty($policy['defaultReceiptBankId'])) {
        $preferredBankId = trim((string)$policy['defaultReceiptBankId']);
    }
    $preferredBankIdClean = preg_replace('/^bank-/', '', $preferredBankId);
    $preferredBankName = $overrideBankName ?: ($policy['defaultReceiptBankName'] ?? '');

    $matchedBankRow = null;
    $fallbackBankRow = null;
    $bokBankRow = null;
    $bStmt = $pdo->prepare("SELECT id, data FROM banks WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
    $bStmt->execute([$tenantId]);
    $allBankRows = $bStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($allBankRows as $bRow) {
        $bData = json_decode($bRow['data'], true);
        if (!$bData || !empty($bData['deleted']) || !empty($bData['isInactive'])) continue;
        $bType = strtolower($bData['type'] ?? 'bank');
        if ($bType === 'safe') continue;

        $bRowId = (string)($bData['id'] ?? ('B-' . $bRow['id']));
        $bRowName = trim((string)($bData['name'] ?? $bData['bankName'] ?? ''));

        if (!$fallbackBankRow) {
            $fallbackBankRow = $bRow;
        }
        if (!$bokBankRow && !empty($bRowName) && (mb_stripos($bRowName, 'الخرطوم') !== false || mb_stripos($bRowName, 'بنكك') !== false)) {
            $bokBankRow = $bRow;
        }
        if (!empty($preferredBankIdClean) && ($bRowId === $preferredBankIdClean || (string)$bRow['id'] === $preferredBankIdClean)) {
            $matchedBankRow = $bRow;
            break;
        }
        if (!empty($preferredBankName) && !empty($bRowName) && (mb_stripos($bRowName, $preferredBankName) !== false || mb_stripos($preferredBankName, $bRowName) !== false)) {
            $matchedBankRow = $bRow;
            break;
        }
    }

    if (!$matchedBankRow) {
        $matchedBankRow = $bokBankRow ?: $fallbackBankRow;
    }

    if ($matchedBankRow) {
        $bankObj = json_decode($matchedBankRow['data'], true) ?: [];
        $bankFrontendId = $bankObj['id'] ?? ('B-' . $matchedBankRow['id']);
        $resolvedBankName = $bankObj['name'] ?? $bankObj['bankName'] ?? 'بنك الخرطوم';
        if ($shouldPostNow) {
            $bankObj['currentBalance'] = (float)($bankObj['currentBalance'] ?? 0) + $amount;
            $updBank = $pdo->prepare("UPDATE banks SET data = ? WHERE id = ?");
            $updBank->execute([json_encode($bankObj, JSON_UNESCAPED_UNICODE), $matchedBankRow['id']]);
        }
    } else {
        $bankFrontendId = 'B-1781625755439';
        $resolvedBankName = !empty($overrideBankName) ? $overrideBankName : 'بنك الخرطوم';
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
            'currentBalance' => $shouldPostNow ? $amount : 0,
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
    $voucherDesc = 'سداد إشعار تحويل بنكي (' . $resolvedBankName . ') رقم العملية: ' . $transferNo . ' - العميل: ' . $custName . $routingAuditSuffix . ' إلى ' . $resolvedBankName;
    $itemStatement = 'إشعار تحويل ' . $resolvedBankName . ' رقم ' . $transferNo . $routingAuditSuffix;

    $voucherObj = [
        'id' => $vNo,
        'voucherNo' => $vNo,
        'type' => 'receipt',
        'isGrouped' => true,
        'source' => 'whatsapp_inbound',
        'inboundReceiptId' => (int)$receiptId,
        'receiptImage' => $receipt['receipt_image'] ?? '',
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
        'isPosted' => $shouldPostNow,
        'createdAt' => gmdate('Y-m-d\TH:i:s.v\Z'),
        'createdBy' => 9,
        'createdByName' => 'النظام الذكي (فحص الإشعارات)'
    ];

    $insRec = $pdo->prepare("INSERT INTO receipts (tenant_id, receipt_no, data) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data)");
    $insRec->execute([$tenantId, $vNo, json_encode($voucherObj, JSON_UNESCAPED_UNICODE)]);

    // 4. Insert General Ledger Double-Entry Records in `gl_entries` ONLY when posted (`$shouldPostNow === true`)
    if ($shouldPostNow) {
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
    }

    // 5. Update `inbound_bank_receipts`
    $updatedNotes = trim((string)($receipt['notes'] ?? ''));
    $updatedNotes = preg_replace('/\s*\|\s*⏳ بانتظار التمرير والترحيل في شاشة الترحيل \([^)]*\)/u', '', $updatedNotes);
    $updatedNotes = preg_replace('/^⏳ بانتظار التمرير والترحيل في شاشة الترحيل \([^)]*\)\s*\|?\s*/u', '', $updatedNotes);

    if ($shouldPostNow) {
        $postedTag = '✅ تم التنزيل في الحسابات (' . $resolvedBankName . ') بالسند ' . $vNo;
        if (mb_strpos($updatedNotes, $vNo) === false) {
            $updatedNotes = !empty($updatedNotes) ? ($updatedNotes . ' | ' . $postedTag) : $postedTag;
        }
        $newInbStatus = 'verified';
        $newIssueReason = null;
    } else {
        $holdTag = '⏳ بانتظار التمرير والترحيل في شاشة الترحيل (' . $vNo . ' -> ' . $resolvedBankName . ')';
        if (mb_strpos($updatedNotes, $vNo) === false) {
            $updatedNotes = !empty($updatedNotes) ? ($updatedNotes . ' | ' . $holdTag) : $holdTag;
        }
        $newInbStatus = 'pending';
        $newIssueReason = 'awaiting_posting_approval';
    }

    $updInb = $pdo->prepare("UPDATE inbound_bank_receipts 
        SET customer_id = ?, customer_name = ?, customer_phone = ?, bank_name = ?, bank_code = ?, voucher_no = ?, status = ?, issue_reason = ?, notes = ?, updated_at = NOW() 
        WHERE id = ?");
    $updInb->execute([$custFrontendId, $custName, $custPhone, $resolvedBankName, $bankFrontendId, $vNo, $newInbStatus, $newIssueReason, $updatedNotes, $receiptId]);

    $adminNotificationText = $shouldPostNow
        ? ("🔔 إشعار لمدير النظام: تم فحص وإضافة إشعار بنكي بمبلغ (" . number_format($amount, 2) . " SDG) رقم العملية (" . $transferNo . ") في حساب (" . $custName . ") إلى حساب (" . $resolvedBankName . ") وحفظه تلقائياً بالسند رقم (" . $vNo . ").")
        : ("👁️ إشعار لمدير النظام: تم استلام وفحص إشعار بنكي بمبلغ (" . number_format($amount, 2) . " SDG) رقم العملية (" . $transferNo . ") لحساب (" . $custName . ") إلى (" . $resolvedBankName . ") وهو بانتظار التمرير في شاشة الترحيل بالسند (" . $vNo . ").");

    recordAdminInboundNotification($pdo, (string)$tenantId, [
        'receipt_id' => (int)$receiptId,
        'voucher_no' => $vNo,
        'customer_id' => $custFrontendId,
        'customer_name' => $custName,
        'bank_id' => $bankFrontendId,
        'bank_name' => $resolvedBankName,
        'approval_mode' => $effectiveApprovalMode,
        'amount' => $amount,
        'transfer_no' => $transferNo,
        'message' => $adminNotificationText,
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // Send WhatsApp notification directly to customer's registered WhatsApp number when manually assigned/approved OR when routed via PIN/Name from a different sender phone
    $whatsappNotified = false;
    $suppressWaNotify = !empty($options['suppress_whatsapp_notify']);
    $custTargetWa = trim((string)($custObj['whatsapp'] ?? ($custObj['phone'] ?? ($custObj['whatsappLid'] ?? ''))));
    if (!$suppressWaNotify && $shouldPostNow && !empty($custTargetWa) && strlen(preg_replace('/\D/', '', $custTargetWa)) >= 7) {
        $cleanTargetDigits = substr(preg_replace('/\D/', '', $custTargetWa), -9);
        $cleanSenderDigits = substr(preg_replace('/\D/', '', (string)$originalSenderPhone), -9);
        $shouldNotifyCustomerWa = !empty($options['notify_customer_whatsapp']) || (!empty($cleanTargetDigits) && $cleanTargetDigits !== $cleanSenderDigits);
        if ($shouldNotifyCustomerWa) {
            $curBal = (float)($custObj['currentBalance'] ?? 0);
            $balLabel = $curBal > 0.009 ? (number_format($curBal, 2) . ' ' . $currency . ' (مدين / عليه)') : ($curBal < -0.009 ? (number_format(abs($curBal), 2) . ' ' . $currency . ' (دائن / له)') : '0.00 (خالص)');
            $custPinBadge = !empty($custObj['statementPin']) ? ("\n🔐 *رمز الحساب (الباسورد):* " . $custObj['statementPin']) : '';
            $waCustomerMsg = "✅ *إشعار قيد مالي معتمد*\n"
                . "━━━━━━━━━━━━━━\n"
                . "👤 *الحساب:* {$custName}{$custPinBadge}\n"
                . "💰 *المبلغ:* " . number_format($amount, 2) . " {$currency}\n"
                . "🔢 *رقم العملية:* {$transferNo}\n"
                . "🏦 *البنك المستلم:* {$resolvedBankName}\n"
                . "🧾 *رقم السند:* {$vNo}\n"
                . "📊 *الرصيد الحالي:* {$balLabel}\n"
                . "━━━━━━━━━━━━━━\n"
                . "💡 يمكنك إرسال الباسورد في أي وقت للحصول على كشف الحساب الفوري.";
            $whatsappNotified = sendInboundWhatsAppNotification($tenantId, $custTargetWa, $waCustomerMsg);
        }
    }

    return [
        'success' => true,
        'already_posted' => false,
        'approval_mode' => $effectiveApprovalMode,
        'is_posted' => $shouldPostNow,
        'whatsapp_notified' => $whatsappNotified,
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
        'message' => $shouldPostNow
            ? ('تم فحص الإشعار وتنزيله في حساب (' . $custName . ') إلى (' . $resolvedBankName . ') بنجاح بالسند رقم ' . $vNo)
            : ('تم فحص الإشعار وحفظه بانتظار التمرير والترحيل في حساب (' . $custName . ') إلى (' . $resolvedBankName . ') بالسند رقم ' . $vNo)
    ];
}

// -------------------------------------------------------------
// Helper: Send Direct WhatsApp Notification to Customer via 24/7 Cloud Hub or Local Service (Port 3001)
// -------------------------------------------------------------
function sendInboundWhatsAppNotification($tenantId, $phone, $message) {
    $phone = trim((string)$phone);
    if (empty($phone) || empty($message)) return false;
    try {
        $userId = 'wa_tenant_' . (int)$tenantId;
        $payload = json_encode([
            'userId' => $userId,
            'phone' => $phone,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);

        // When running on the local laptop, send via the 24/7 Cloud WhatsApp Hub (https://c.elsajana.com) first
        if (function_exists('isLocalInboundLaptopEnv') && isLocalInboundLaptopEnv()) {
            $cloudUrl = 'https://c.elsajana.com/api_index.php?r=wa-tenant&endpoint=send&userId=' . urlencode($userId) . '&local_only=1';
            $chCloud = curl_init($cloudUrl);
            curl_setopt($chCloud, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($chCloud, CURLOPT_POST, true);
            curl_setopt($chCloud, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($chCloud, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            ]);
            curl_setopt($chCloud, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($chCloud, CURLOPT_TIMEOUT, 6);
            curl_setopt($chCloud, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($chCloud, CURLOPT_SSL_VERIFYHOST, false);
            $cResp = curl_exec($chCloud);
            $cCode = (int)curl_getinfo($chCloud, CURLINFO_HTTP_CODE);
            curl_close($chCloud);
            if ($cCode === 200 && !empty($cResp)) {
                $cDec = json_decode($cResp, true);
                if (!empty($cDec['success'])) {
                    return true;
                }
            }
        }

        $ch = curl_init('http://127.0.0.1:3001/wa/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        ]);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        $resp = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200 && !empty($resp)) {
            $dec = json_decode($resp, true);
            return !empty($dec['success']);
        }
    } catch (Throwable $e) {}
    return false;
}

// -------------------------------------------------------------
// Helper: Perform Backend AI OCR on Inbound Receipt Image
// ($fraudCheck = false by default; true ONLY when user requests "فحص التزوير")
// -------------------------------------------------------------
function performBackendReceiptOCR($pdo, $receiptId, $fraudCheck = false, $postOptions = []) {
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

    // Call OCR endpoint internally using fast 127.0.0.1 IPv4 priority to avoid Windows localhost IPv6 delay
    $ocrPayload = json_encode([
        'image' => $base64,
        'doc_hint' => 'transfer',
        'fraud_check' => (bool)$fraudCheck
    ]);

    $candidateUrls = [
        'http://127.0.0.1/ELSAJANA2/magaies/api/ocr-financial.php'
    ];
    if (!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['SCRIPT_NAME'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = str_ireplace('localhost', '127.0.0.1', $_SERVER['HTTP_HOST']);
        $baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $candidateUrls[] = $scheme . '://' . $host . $baseDir . '/ocr-financial.php';
    }
    $candidateUrls[] = 'http://localhost/ELSAJANA2/magaies/api/ocr-financial.php';
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
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
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
    $isNonFinancial = (isset($d['is_financial_doc']) && $d['is_financial_doc'] === false) || !empty($d['is_non_financial']);
    $extractedAmount = floatval($d['amount'] ?? 0) ?: floatval($receipt['amount'] ?? 0);
    $extractedRef = trim($d['transaction_ref'] ?? '') ?: trim($receipt['transfer_no'] ?? '');
    $extractedSender = trim($d['sender_name'] ?? '');
    $extractedBank = trim($d['bank_name'] ?? '') ?: trim($receipt['bank_name'] ?? 'بنك الخرطوم (بنكك)');
    $summary = trim($d['summary'] ?? '');

    $tenantId = $receipt['tenant_id'];

    // If this is an On-Demand Forgery Check ("فحص التزوير عند الطلب فقط"), return forensic result without re-posting
    if ($fraudCheck) {
        $tamperingScore = (int)($d['tampering_score'] ?? 0);
        $isSuspicious = !empty($d['is_suspicious']) || $tamperingScore >= 40;
        $tamperingReasons = $d['tampering_reasons'] ?? '';
        if (is_array($tamperingReasons)) {
            $tamperingReasonsArr = $tamperingReasons;
        } else {
            $tamperingReasonsArr = !empty($tamperingReasons) ? [$tamperingReasons] : [];
        }
        return [
            'success' => true,
            'fraud_checked' => true,
            'id' => $receiptId,
            'data' => $receipt,
            'forgery_check' => [
                'tampering_score' => $tamperingScore,
                'is_suspicious' => $isSuspicious,
                'tampering_reasons' => $tamperingReasonsArr
            ],
            'tampering_score' => $tamperingScore,
            'is_suspicious' => $isSuspicious,
            'tampering_reasons' => $tamperingReasonsArr,
            'amount' => $extractedAmount,
            'transfer_no' => $extractedRef,
            'ocr_details' => $d
        ];
    }

    // Validate All Receipt Conditions: Financial Document, Transfer Number >= 6 digits, Amount > 0, Not Duplicate
    $cleanRefDigits = preg_replace('/\D/', '', $extractedRef);
    $status = 'verified';
    $issueReason = null;
    $existingNotes = trim((string)($receipt['notes'] ?? ''));
    $existingNotes = preg_replace('/\s*\|?\s*⚠️\s*كشف تكرار مالي:[^|]*/u', '', $existingNotes);
    $existingNotes = preg_replace('/\s*\|?\s*تم الفحص التلقائي بنجاح[^|]*/u', '', $existingNotes);
    $existingNotes = trim($existingNotes, " |\t\n\r");
    $ocrSummaryNote = $summary ?: ('تم الفحص التلقائي بنجاح | رقم العملية: ' . $extractedRef);
    $notes = !empty($existingNotes) ? ($existingNotes . ' | ' . $ocrSummaryNote) : $ocrSummaryNote;

    if ($isNonFinancial) {
        $status = 'pending';
        $issueReason = 'non_financial_image';
        $notes = (!empty($existingNotes) ? $existingNotes . ' | ' : '') . '⚠️ مرفوض: الصورة المرسلة ليست إشعار تحويل بنكي مالي معتمد';
    } elseif (strlen($cleanRefDigits) < 6) {
        $status = 'pending';
        $issueReason = 'invalid_transfer_no_under_6_digits';
        $notes = (!empty($existingNotes) ? $existingNotes . ' | ' : '') . '⚠️ مرفوض: رقم التحويل يجب ألا يقل عن 6 أرقام (' . ($extractedRef ?: 'غير موجود') . ')';
    } elseif ($extractedAmount <= 0) {
        $status = 'pending';
        $issueReason = 'zero_amount';
        $notes = (!empty($existingNotes) ? $existingNotes . ' | ' : '') . '⚠️ المبلغ غير واضح في الإشعار';
    }

    // Customer resolution via Secret PIN (`statementPin`), Receipt Code, WhatsApp phone / Delegate SubPhone, LID, or name
    // IMPORTANT: Never pass $summary or OCR-extracted amounts into resolveInboundCustomer! Only pass user-provided $existingNotes.
    $custName = $receipt['customer_name'];
    if (!empty($extractedSender) && ($custName === 'زبون مباشر' || str_starts_with($custName, 'عميل واتساب') || $custName === 'amar00105')) {
        $custName = $extractedSender;
    }
    $custId = $receipt['customer_id'];
    $custPhone = $receipt['customer_phone'];

    $ocrRoutingHint = $existingNotes;
    $resolvedCust = resolveInboundCustomer($pdo, $tenantId, $custId, $custName, $custPhone, $ocrRoutingHint);
    if (!empty($resolvedCust['id'])) {
        $custId = $resolvedCust['id'];
        $custName = $resolvedCust['name'] ?: $custName;
        if (!empty($resolvedCust['phone'])) {
            $custPhone = $resolvedCust['phone'];
        }
    }
    if (!$isNonFinancial && strlen($cleanRefDigits) >= 6 && $extractedAmount > 0) {
        // If this transfer_no was previously misrouted to a DIFFERENT customer during testing or earlier routing, reassign it to the resolved customer!
        if (!empty($custId)) {
            reassignMisroutedInboundConflictIfDifferentCustomer($pdo, $tenantId, $extractedRef, (int)$receiptId, $custId, $custName);
        }
        $dupCheck = checkTransferConflictInDB($pdo, $tenantId, $extractedRef, (int)$receiptId);
        if ($dupCheck['is_duplicate']) {
            $status = 'duplicate_rejected';
            $issueReason = 'duplicate_voucher_' . ($dupCheck['conflicting_voucher'] ?? '');
            $notes = (!empty($existingNotes) ? $existingNotes . ' | ' : '') . '⚠️ كشف تكرار مالي: رقم العملية ' . $extractedRef . ' مسجل بالسند ' . ($dupCheck['conflicting_voucher'] ?? '');
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

    // Automatically post or stage to accounts if verified
    $postingResult = null;
    if ($status === 'verified' && $extractedAmount > 0 && strlen($cleanRefDigits) >= 6) {
        $postingResult = postInboundReceiptToAccounts($pdo, $receiptId, $postOptions);
        if (!empty($postingResult['voucher_no'])) {
            $custId = $postingResult['customer_id'] ?? $custId;
            $custName = $postingResult['customer_name'] ?? $custName;
            $custPhone = $postingResult['customer_phone'] ?? $custPhone;
            $extractedBank = $postingResult['bank_name'] ?? $extractedBank;
            if (($postingResult['approval_mode'] ?? '') === 'review_first') {
                $status = 'pending';
                $issueReason = 'awaiting_posting_approval';
            }
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
            'approval_mode' => $postingResult['approval_mode'] ?? 'auto_post',
            'voucher_no' => $postingResult['voucher_no'] ?? ($receipt['voucher_no'] ?? null),
            'notes' => $notes,
            'posting' => $postingResult,
            'ocr_details' => $d
        ]
    ];
}

// -------------------------------------------------------------
// Helper: Ensure all active suppliers in DB have gap-aware receiptCodeNum (1..infinity)
// -------------------------------------------------------------
function fetchAndEnsureSupplierCodes($pdo, $tenantId, $existingCustRows = null) {
    if ($existingCustRows === null) {
        $existingCustRows = fetchAndEnsureCustomerCodes($pdo, $tenantId);
    }
    $usedNums = [];
    $usedPins = [];
    foreach ($existingCustRows as $cr) {
        $cObj = $cr['_parsed'] ?? (json_decode($cr['data'] ?? '', true) ?: []);
        if (!empty($cObj['deleted'])) continue;
        $n = isset($cObj['receiptCodeNum']) ? (int)normalizeInboundDigits($cObj['receiptCodeNum']) : 0;
        if ($n >= 1) $usedNums[$n] = true;
        $cPin = preg_replace('/[^0-9]/', '', normalizeInboundDigits((string)($cObj['statementPin'] ?? '')));
        if ($cPin !== '') $usedPins[$cPin] = true;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, frontend_id, supplier_id, data FROM suppliers WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
        $stmt->execute([$tenantId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }

    foreach ($rows as &$r) {
        $sObj = json_decode($r['data'], true) ?: [];
        if (!empty($sObj['deleted'])) continue;
        $num = isset($sObj['receiptCodeNum']) ? (int)normalizeInboundDigits($sObj['receiptCodeNum']) : 0;
        if ($num >= 1 && !isset($usedNums[$num])) {
            $usedNums[$num] = true;
            $sObj['receiptCodeNum'] = $num;
        } else {
            $sObj['receiptCodeNum'] = 0;
        }

        $pin = preg_replace('/[^0-9]/', '', normalizeInboundDigits((string)($sObj['statementPin'] ?? '')));
        if ($pin !== '' && !isset($usedPins[$pin])) {
            $usedPins[$pin] = true;
            $sObj['statementPin'] = $pin;
        } else {
            $sObj['statementPin'] = '';
        }
        $r['_parsed'] = $sObj;
    }
    unset($r);

    $nextCandidate = 1;
    $nextPinSeed = 501;
    foreach ($rows as &$r) {
        $sObj = $r['_parsed'] ?? [];
        if (!empty($sObj['deleted'])) continue;
        $rowChanged = false;
        if (empty($sObj['receiptCodeNum']) || $sObj['receiptCodeNum'] < 1) {
            while (isset($usedNums[$nextCandidate])) {
                $nextCandidate++;
            }
            $sObj['receiptCodeNum'] = $nextCandidate;
            $usedNums[$nextCandidate] = true;
            $rowChanged = true;
        }
        if (empty($sObj['statementPin'])) {
            $candPin = '';
            for ($try = 0; $try < 50; $try++) {
                $randPin = (string)rand(100, 999);
                if (!isset($usedPins[$randPin])) {
                    $candPin = $randPin;
                    break;
                }
            }
            if ($candPin === '') {
                while (isset($usedPins[(string)$nextPinSeed])) {
                    $nextPinSeed++;
                }
                $candPin = (string)$nextPinSeed;
            }
            $sObj['statementPin'] = $candPin;
            $usedPins[$candPin] = true;
            $rowChanged = true;
        }
        if ($rowChanged) {
            $newJson = json_encode($sObj, JSON_UNESCAPED_UNICODE);
            $r['data'] = $newJson;
            $r['_parsed'] = $sObj;
            try {
                $u = $pdo->prepare("UPDATE suppliers SET data = ? WHERE id = ?");
                $u->execute([$newJson, $r['id']]);
            } catch (Exception $e) {}
        }
    }
    unset($r);

    return $rows;
}

// -------------------------------------------------------------
// Helper: Load System Admin WhatsApp Statement & Inbound Receipt Policy
// -------------------------------------------------------------
function loadWhatsAppStatementPolicy($pdo, $tenantId) {
    $policy = [
        'masterMode' => 'per_account', // 'per_account' | 'force_detailed_all' | 'force_balance_only_all' | 'force_disabled_all'
        'phoneVerification' => 'phone_or_pin', // 'any_phone_with_code' | 'registered_if_set' | 'strict_registered_only' | 'phone_or_pin' | 'pin_always'
        'statementScope' => 'all', // 'all' | 'last_30_days' | 'last_20' | 'last_50' | 'current_month'
        'receiptApprovalMode' => 'per_account', // 'per_account' | 'auto_post_all' | 'review_first_all'
        'defaultReceiptBankId' => '', // Optional default receiving bank ID from `banks`
        'defaultReceiptBankName' => '',
        'voucherCompanyName' => '',
        'voucherStampText' => 'ختم مصادقة مالية إلكترونية',
        'voucherStampImage' => '',
        'includeInvoiceItems' => true,
        'notifyAdminOnRequest' => false,
        'antiSpamCooldown' => true
    ];
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'erp_whatsapp_statement_policy_v1' LIMIT 1");
        $stmt->execute();
        $polVal = $stmt->fetchColumn();
        if (!empty($polVal)) {
            $decoded = json_decode($polVal, true);
            if (is_array($decoded)) {
                $policy = array_merge($policy, $decoded);
            }
        }
    } catch (Exception $e) {
        // Fallback to default policy
    }
    return $policy;
}

// -------------------------------------------------------------
// Helper: Handle Incoming WhatsApp Account Statement / Balance Request (Customers & Suppliers)
// -------------------------------------------------------------
function handleWhatsAppStatementRequest($pdo, $tenantId, $messageText, $senderPhone = '', $senderName = '') {
    $normMsg = trim(normalizeInboundDigits((string)$messageText));
    if (empty($normMsg) || mb_strlen($normMsg) > 120) {
        return ['success' => true, 'matched' => false];
    }

    $policy = loadWhatsAppStatementPolicy($pdo, $tenantId);

    $custRows = fetchAndEnsureCustomerCodes($pdo, $tenantId);
    $suppRows = fetchAndEnsureSupplierCodes($pdo, $tenantId, $custRows);

    // Build unified pool of entities (Customers & Suppliers)
    $entities = [];
    foreach ($custRows as $cr) {
        $cObj = $cr['_parsed'] ?? (json_decode($cr['data'] ?? '', true) ?: []);
        if (empty($cObj) || !empty($cObj['deleted'])) continue;
        $fId = $cObj['id'] ?? $cr['frontend_id'] ?? ('CUST-' . $cr['id']);
        $cObj['id'] = $fId;

        $mode = $cObj['statementAccessMode'] ?? '';
        if (!in_array($mode, ['disabled', 'balance_only', 'detailed'], true)) {
            $mode = (isset($cObj['allowStatementRequest']) && $cObj['allowStatementRequest'] === false) ? 'disabled' : 'detailed';
        }
        $cleanPin = preg_replace('/[^0-9]/', '', normalizeInboundDigits((string)($cObj['statementPin'] ?? '')));

        $entities[] = [
            'type' => 'customer',
            'type_label' => 'عميل',
            'db_row' => $cr,
            'obj' => $cObj,
            'id' => (string)$fId,
            'name' => trim((string)($cObj['name'] ?? $cr['search_name'] ?? '')),
            'code_num' => isset($cObj['receiptCodeNum']) ? (int)$cObj['receiptCodeNum'] : 0,
            'code_word' => trim((string)($cObj['receiptCodeWord'] ?? '')),
            'statement_pin' => $cleanPin,
            'access_mode' => $mode,
            'allow_statement' => ($mode !== 'disabled')
        ];
    }
    foreach ($suppRows as $sr) {
        $sObj = $sr['_parsed'] ?? (json_decode($sr['data'] ?? '', true) ?: []);
        if (empty($sObj) || !empty($sObj['deleted'])) continue;
        $fId = $sObj['id'] ?? $sr['frontend_id'] ?? $sr['supplier_id'] ?? ('SUP-' . $sr['id']);
        $sObj['id'] = $fId;

        $mode = $sObj['statementAccessMode'] ?? '';
        if (!in_array($mode, ['disabled', 'balance_only', 'detailed'], true)) {
            $mode = (isset($sObj['allowStatementRequest']) && $sObj['allowStatementRequest'] === false) ? 'disabled' : 'detailed';
        }
        $cleanPin = preg_replace('/[^0-9]/', '', normalizeInboundDigits((string)($sObj['statementPin'] ?? '')));

        $entities[] = [
            'type' => 'supplier',
            'type_label' => 'مورد',
            'db_row' => $sr,
            'obj' => $sObj,
            'id' => (string)$fId,
            'name' => trim((string)($sObj['name'] ?? '')),
            'code_num' => isset($sObj['receiptCodeNum']) ? (int)$sObj['receiptCodeNum'] : 0,
            'code_word' => trim((string)($sObj['receiptCodeWord'] ?? '')),
            'statement_pin' => $cleanPin,
            'access_mode' => $mode,
            'allow_statement' => ($mode !== 'disabled')
        ];
    }

    if (empty($entities)) {
        return ['success' => true, 'matched' => false];
    }

    // Anti-Brute-Force Rate Limit helper for PIN attempts (max 5 failed attempts per 15 mins per senderPhone)
    $rateLimitFile = sys_get_temp_dir() . '/elsajana_wa_pin_ratelimit.json';
    $senderKey = preg_replace('/\D/', '', normalizeInboundDigits((string)$senderPhone)) ?: 'anon';
    $checkAndRecordRateLimit = function($action = 'check') use ($rateLimitFile, $senderKey) {
        $now = time();
        $windowSec = 900; // 15 minutes
        $maxAttempts = 5;
        $data = [];
        if (file_exists($rateLimitFile)) {
            $raw = @file_get_contents($rateLimitFile);
            $data = json_decode((string)$raw, true) ?: [];
        }
        // Prune expired entries
        foreach ($data as $k => $entry) {
            if (($now - ($entry['last_attempt'] ?? 0)) > $windowSec) {
                unset($data[$k]);
            }
        }
        $cur = $data[$senderKey] ?? ['fails' => 0, 'last_attempt' => $now];
        if ($action === 'check') {
            return ($cur['fails'] >= $maxAttempts);
        } elseif ($action === 'fail') {
            $cur['fails'] = ($cur['fails'] ?? 0) + 1;
            $cur['last_attempt'] = $now;
            $data[$senderKey] = $cur;
            @file_put_contents($rateLimitFile, json_encode($data));
            return ($cur['fails'] >= $maxAttempts);
        } elseif ($action === 'reset') {
            if (isset($data[$senderKey])) {
                unset($data[$senderKey]);
                @file_put_contents($rateLimitFile, json_encode($data));
            }
            return false;
        }
        return false;
    };

    // Check if the message explicitly contains statement/balance request keywords (including single-letter shortcuts 'ك'/'كاف' and 'ر'/'راء' with or without spaces) or is a direct PIN/Password
    $isShortKaf = (bool)preg_match('/^[\s#:\-_.,\/()]*(\d{1,10}[\s#:\-_.,\/()]*)?(?:ك|كاف|الكاف|حرف\s*ك|حرف\s*كاف|حرف\s*الكاف)([\s#:\-_.,\/()]*\d{1,10})?[\s#:\-_.,\/()]*$/ui', $normMsg);
    $isShortRaa = (bool)preg_match('/^[\s#:\-_.,\/()]*(\d{1,10}[\s#:\-_.,\/()]*)?(?:ر|راء|الراء|حرف\s*ر|حرف\s*راء|حرف\s*الراء)([\s#:\-_.,\/()]*\d{1,10})?[\s#:\-_.,\/()]*$/ui', $normMsg);

    $hasStatementKeyword = $isShortKaf || $isShortRaa || (bool)preg_match('/(?:طلب\s*كشف|كشف\s*حساب|كشف\s*الحساب|كشف|حسابي|رصيدي|الرصيد|رصيد|معرفة\s*الرصيد|كم\s*رصيدي)/ui', $normMsg);
    $explicitBalanceOnlyRequest = $isShortRaa || ((bool)preg_match('/(?:رصيدي\s*فقط|معرفة\s*الرصيد\s*فقط|رصيدي|الرصيد|رصيد|كم\s*رصيدي)/ui', $normMsg) && !preg_match('/كشف/ui', $normMsg) && !$isShortKaf);

    // Strip common prefixes AND suffixes (so "رصيد 532", "532 رصيد", "532رصيد", "532 ر", "532ر", "ر 532", "ر532", "532 كشف", "532كشف", "كشف 532", "532 ك", "532ك", "ك 532", "ك532", "باسورد 532" all isolate "532")
    $kwPattern = '(?:طلب\s*كشف\s*الحساب|طلب\s*كشف\s*حساب|كشف\s*الحساب|كشف\s*حساب|طلب\s*كشف|معرفة\s*الرصيد\s*فقط|معرفة\s*الرصيد|كم\s*رصيدي|رصيدي\s*فقط|كشف|حسابي|حساب|رصيدي|الرصيد|رصيد|حرف\s*الكاف|حرف\s*كاف|حرف\s*ك|الكاف|كاف|ك|حرف\s*الراء|حرف\s*راء|حرف\s*ر|الراء|راء|ر|باسورد|الباسورد|سري|السري|الرقم\s*السري|كود|الكود|رقم\s*الكود|رمز|الرمز|عميل|العميل|مورد|المورد|pin|code|pass|#|:|\-|\s)';
    $cleanedToken = preg_replace('/^' . $kwPattern . '+|' . $kwPattern . '+$/ui', '', $normMsg);
    $cleanedToken = trim($cleanedToken, " \t\n\r\0\x0B#:-_.,/()");
    $compactDigitsToken = preg_replace('/[\s\-_.*#\/]+/', '', $cleanedToken);
    if (!preg_match('/^\d+$/', $compactDigitsToken) && preg_match('/(?<!\d)(\d{1,8})(?!\d)/', $normMsg, $mNumFallback)) {
        $compactDigitsToken = $mNumFallback[1];
    }

    $matchedEntity = null;
    $pinVerified = false;

    // 0. PRIORITY 0: Match Direct Secret Password / PIN (`statement_pin`, e.g., "532", "532 رصيد", "532 كشف", "532ر", "532ك")
    // Takes priority even if sent from a registered phone belonging to another customer (e.g. Osman querying Abdullah's account via Abdullah's PIN)
    if (!empty($compactDigitsToken)) {
        foreach ($entities as $ent) {
            $pin = $ent['statement_pin'];
            if (empty($pin)) continue;
            if ($compactDigitsToken === $pin) {
                $matchedEntity = $ent;
                $pinVerified = true;
                break;
            }
        }
    }

    // 0b. Backward-compatibility: Match Combined Secret PIN + Legacy Account Code (e.g. "532150" or "150532")
    if (!$matchedEntity && !empty($compactDigitsToken)) {
        foreach ($entities as $ent) {
            $pin = $ent['statement_pin'];
            if (empty($pin)) continue;
            $cNumStr = ($ent['code_num'] >= 1) ? (string)$ent['code_num'] : '';
            if ($cNumStr !== '') {
                $pinPlusCode = $pin . $cNumStr;
                $codePlusPin = $cNumStr . $pin;
                if ($compactDigitsToken === $pinPlusCode || $compactDigitsToken === $codePlusPin) {
                    $matchedEntity = $ent;
                    $pinVerified = true;
                    break;
                }
            }
        }
    }

    // 0c. Fallback: Match Direct Account Code (`code_num`, e.g. "1 رصيد", "15 كشف", "49 ك", "#45", "45") when no 3-digit statement_pin matched
    if (!$matchedEntity && !empty($compactDigitsToken) && preg_match('/^\d{1,5}$/', $compactDigitsToken)) {
        $isPureCodeMessage = (bool)preg_match('/^(?:#|كود|الكود|حساب|عميل|مورد|code|pin|\s)*\d{1,5}\s*$/ui', $normMsg);
        if ($hasStatementKeyword || $isPureCodeMessage) {
            $targetCodeNum = (int)$compactDigitsToken;
            if ($targetCodeNum >= 1) {
                foreach ($entities as $ent) {
                    if ((int)$ent['code_num'] === $targetCodeNum) {
                        $matchedEntity = $ent;
                        $pinVerified = true;
                        break;
                    }
                }
            }
        }
    }

    // 0d. Fallback: Match Custom Code Word (`code_word`, e.g. "رواد كشف") or Entity Name (e.g. "رصيد مهدي مكي", "كشف حساب الاسعد")
    if (!$matchedEntity && !empty($cleanedToken) && !preg_match('/^\d+$/', $cleanedToken)) {
        $normNameFn = function($str) {
            $s = mb_strtolower(trim((string)$str));
            $s = str_replace(['أ','إ','آ'], 'ا', $s);
            $s = str_replace(['ى'], 'ي', $s);
            $s = str_replace(['ة'], 'ه', $s);
            $s = preg_replace('/\s+/u', ' ', $s);
            return $s;
        };
        $normTok = $normNameFn($cleanedToken);
        foreach ($entities as $ent) {
            if (!empty($ent['code_word']) && $normNameFn($ent['code_word']) === $normTok) {
                $matchedEntity = $ent;
                $pinVerified = true;
                break;
            }
        }
        if (!$matchedEntity && $hasStatementKeyword && mb_strlen($normTok) >= 2) {
            foreach ($entities as $ent) {
                if (!empty($ent['name']) && $normNameFn($ent['name']) === $normTok) {
                    $matchedEntity = $ent;
                    $pinVerified = true;
                    break;
                }
            }
            if (!$matchedEntity && mb_strlen($normTok) >= 3) {
                $partialMatches = [];
                foreach ($entities as $ent) {
                    $enName = $normNameFn($ent['name'] ?? '');
                    if ($enName !== '' && (mb_strpos($enName, $normTok) !== false || mb_strpos($normTok, $enName) !== false)) {
                        $partialMatches[] = $ent;
                    }
                }
                if (count($partialMatches) === 1) {
                    $matchedEntity = $partialMatches[0];
                    $pinVerified = true;
                }
            }
        }
    }

    // Helper to check if senderPhone matches entity's registered phones
    $checkSenderMatchesEntityPhones = function($ent, $senderPhoneRaw) {
        $cleanSender = preg_replace('/\D/', '', normalizeInboundDigits((string)$senderPhoneRaw));
        $eObj = $ent['obj'];
        $candPhones = [
            $eObj['whatsapp'] ?? '',
            $eObj['phone'] ?? '',
            $eObj['mobile'] ?? '',
            $eObj['whatsappLid'] ?? ''
        ];
        if (!empty($eObj['subPhones']) && is_array($eObj['subPhones'])) {
            foreach ($eObj['subPhones'] as $sp) {
                $candPhones[] = is_array($sp) ? ($sp['phone'] ?? '') : (string)$sp;
            }
        }
        $validRegisteredPhones = [];
        foreach ($candPhones as $cp) {
            $cpDigits = preg_replace('/\D/', '', normalizeInboundDigits((string)$cp));
            if (strlen($cpDigits) >= 7) {
                $validRegisteredPhones[] = $cpDigits;
            }
        }
        $hasRegistered = !empty($validRegisteredPhones);
        $isMatched = false;
        if (strlen($cleanSender) >= 7 && $hasRegistered) {
            $last9 = substr($cleanSender, -9);
            foreach ($validRegisteredPhones as $vp) {
                if ($vp === $cleanSender || (strlen($vp) >= 9 && substr($vp, -9) === $last9)) {
                    $isMatched = true;
                    break;
                }
            }
        }
        return ['has_registered' => $hasRegistered, 'is_matched' => $isMatched];
    };

    // 1. PRIORITY 1: If user wrote ONLY "كشف" or "رصيد" (no password token), match by their Registered WhatsApp Phone!
    if (!$matchedEntity && empty($compactDigitsToken) && $hasStatementKeyword && !empty($senderPhone)) {
        foreach ($entities as $ent) {
            $phoneCheck = $checkSenderMatchesEntityPhones($ent, $senderPhone);
            if ($phoneCheck['is_matched']) {
                $matchedEntity = $ent;
                break;
            }
        }

        // 1b. Fallback: Check if sender's WhatsApp pushName matches an existing Customer/Supplier name
        if (!$matchedEntity && !empty($senderName) && !str_starts_with($senderName, 'عميل واتساب')) {
            $normSenderName = mb_strtolower(trim($senderName));
            foreach ($entities as $ent) {
                if (!empty($ent['name']) && mb_strtolower(trim($ent['name'])) === $normSenderName) {
                    $matchedEntity = $ent;
                    $pinVerified = true;
                    break;
                }
            }
        }

        // If sender wrote "كشف" or "رصيد" without a password and their phone is NOT registered to any account:
        if (!$matchedEntity) {
            $reqTypeLabel = $explicitBalanceOnlyRequest ? 'رصيد' : 'كشف';
            return [
                'success' => true,
                'matched' => true,
                'allowed' => false,
                'blocked_reason' => 'unregistered_phone_needs_pin',
                'entity_type' => 'customer',
                'entity_id' => '',
                'entity_name' => $senderName ?: $senderPhone,
                'code_num' => 0,
                'code_word' => '',
                'reply_text' => "🔐 *الرقم غير مسجل — مطلوب الرقم السري (الباسورد)*\n" .
                                "━━━━━━━━━━━━━━\n" .
                                "مرحباً بك، رقم الواتساب المرسل منه غير مسجل لدينا في بطاقة الحساب.\n" .
                                "💡 للاستعلام من رقم غير مسجل، يرجى إرسال *(الرقم السري + {$reqTypeLabel})* في رسالة واحدة (مثال: *532 {$reqTypeLabel}*)."
            ];
        }
    }

    // 2. If user wrote a Password/Number + "كشف" or "رصيد" (e.g. "999 رصيد" or "999 كشف"), and it did NOT match any account's PIN:
    if (!$matchedEntity && !empty($compactDigitsToken) && $hasStatementKeyword && preg_match('/^[0-9]{2,8}$/', $compactDigitsToken)) {
        $isLocked = $checkAndRecordRateLimit('fail');
        $lockMsg = $isLocked
            ? "\n⛔ تم إيقاف المحاولات مؤقتاً لمدة 15 دقيقة بسبب تكرار إدخال رقم سري خاطئ."
            : "";
        return [
            'success' => true,
            'matched' => true,
            'allowed' => false,
            'blocked_reason' => $isLocked ? 'pin_rate_limited' : 'wrong_pin',
            'entity_type' => 'customer',
            'entity_id' => '',
            'entity_name' => $senderName ?: $senderPhone,
            'code_num' => 0,
            'code_word' => '',
            'reply_text' => "🔐 *الرقم السري (الباسورد) غير صحيح*\n" .
                            "━━━━━━━━━━━━━━\n" .
                            "عذراً، الرقم السري المدخل (*{$compactDigitsToken}*) غير مطابق لأي حساب مسجل في النظام.\n" .
                            "يرجى التأكد من الرقم السري الخاص بحسابكم والمحاولة مرة أخرى." . $lockMsg
        ];
    }

    if (!$matchedEntity) {
        return ['success' => true, 'matched' => false];
    }

    // Check if sender is currently rate-limited due to too many wrong PIN attempts
    if ($checkAndRecordRateLimit('check')) {
        return [
            'success' => true,
            'matched' => true,
            'allowed' => false,
            'blocked_reason' => 'pin_rate_limited',
            'entity_type' => $matchedEntity['type'],
            'entity_id' => $matchedEntity['id'],
            'entity_name' => $matchedEntity['name'],
            'code_num' => $matchedEntity['code_num'],
            'code_word' => $matchedEntity['code_word'],
            'reply_text' => "⛔ *تنبيه حماية الحسابات*\n" .
                            "━━━━━━━━━━━━━━\n" .
                            "تم إيقاف الاستعلام مؤقتاً لمدة 15 دقيقة بسبب تجاوز الحد المسموح لمحاولات الرقم السري الخاطئة."
        ];
    }

    if ($pinVerified) {
        $checkAndRecordRateLimit('reset');
    }

    $codeDisplay = !empty($matchedEntity['statement_pin']) ? ('🔐 ' . $matchedEntity['statement_pin']) : ('#' . $matchedEntity['code_num']);

    // Resolve Effective Access Mode based on System Admin Master Policy
    $masterMode = $policy['masterMode'] ?? 'per_account';
    if ($masterMode === 'force_disabled_all') {
        $effectiveMode = 'disabled';
    } elseif ($masterMode === 'force_balance_only_all') {
        $effectiveMode = 'balance_only';
    } elseif ($masterMode === 'force_detailed_all') {
        $effectiveMode = 'detailed';
    } else {
        $effectiveMode = $matchedEntity['access_mode']; // 'disabled' | 'balance_only' | 'detailed'
    }

    // Check Phone & Secret PIN Verification Policy
    $phonePolicy = $policy['phoneVerification'] ?? 'phone_or_pin';
    $entityHasPin = !empty($matchedEntity['statement_pin']);
    if ($effectiveMode !== 'disabled') {
        $phoneCheck = $checkSenderMatchesEntityPhones($matchedEntity, $senderPhone);
        $blockedByAuth = false;
        $blockedReason = 'phone_mismatch';
        $replyAuthText = '';

        if ($phonePolicy === 'pin_always') {
            if (!$pinVerified) {
                $blockedByAuth = true;
                $blockedReason = 'pin_required';
                $replyAuthText = "🔐 *مطلوب الرقم السري (الباسورد) للحساب*\n" .
                                 "━━━━━━━━━━━━━━\n" .
                                 "مرحباً بك: *{$matchedEntity['name']}*\n" .
                                 "سياسة الأمان تشترط إرسال *(الرقم السري)* الخاص بحسابكم مع كلمة كشف أو رصيد.";
            }
        } elseif ($phonePolicy === 'phone_or_pin') {
            if ($pinVerified || $phoneCheck['is_matched']) {
                $blockedByAuth = false;
            } else {
                if ($entityHasPin || !empty($senderPhone)) {
                    $blockedByAuth = true;
                    $blockedReason = $entityHasPin ? 'pin_or_phone_required' : 'phone_mismatch_no_pin';
                    $pinHint = "للدخول الفوري من أي رقم غير مسجل، يرجى إرسال *(الرقم السري / الباسورد)* متبوعاً بكلمة كشف أو رصيد.";
                    $replyAuthText = "🔐 *التحقق من هوية الحساب (الرقم المسجل / الرقم السري)*\n" .
                                     "━━━━━━━━━━━━━━\n" .
                                     "الطلب مرسل من رقم غير مسجل في بطاقة الحساب.\n" .
                                     "💡 " . $pinHint;
                }
            }
        } elseif ($phonePolicy === 'strict_registered_only') {
            if (!empty($senderPhone) && !$phoneCheck['is_matched'] && !$pinVerified) {
                $blockedByAuth = true;
                $blockedReason = 'phone_mismatch';
                $replyAuthText = "🛡️ *تنبيه أمان وحماية الحسابات*\n" .
                                 "━━━━━━━━━━━━━━\n" .
                                 "عذراً، سياسة مدير النظام تشترط إرسال الطلب من رقم الواتساب المسجل رسمياً لهذا الحساب، أو كتابة الرقم السري (الباسورد).";
            }
        } elseif ($phonePolicy === 'registered_if_set') {
            if (!empty($senderPhone) && $phoneCheck['has_registered'] && !$phoneCheck['is_matched'] && !$pinVerified) {
                $blockedByAuth = true;
                $blockedReason = 'phone_mismatch';
                $replyAuthText = "🛡️ *تنبيه أمان وحماية الحسابات*\n" .
                                 "━━━━━━━━━━━━━━\n" .
                                 "عذراً، سياسة مدير النظام تشترط إرسال الطلب من رقم الواتساب المسجل رسمياً لهذا الحساب، أو كتابة الرقم السري (الباسورد).";
            }
        }

        if (!$blockedByAuth && $entityHasPin && !$pinVerified && !$phoneCheck['is_matched']) {
            $blockedByAuth = true;
            $blockedReason = 'account_pin_required';
            $replyAuthText = "🔐 *حساب محمي برقم سري (PIN)*\n" .
                             "━━━━━━━━━━━━━━\n" .
                             "للاستعلام من هذا الرقم، يرجى إرسال *(الرقم السري / الباسورد)* الخاص بالحساب.";
        }

        if ($blockedByAuth) {
            return [
                'success' => true,
                'matched' => true,
                'allowed' => false,
                'access_mode' => $effectiveMode,
                'blocked_reason' => $blockedReason,
                'entity_type' => $matchedEntity['type'],
                'entity_id' => $matchedEntity['id'],
                'entity_name' => $matchedEntity['name'],
                'code_num' => $matchedEntity['code_num'],
                'code_word' => $matchedEntity['code_word'],
                'reply_text' => $replyAuthText
            ];
        }
    }

    // Check if service is disabled for this Customer/Supplier (or globally by System Admin)
    if ($effectiveMode === 'disabled') {
        $disabledReasonLine = ($masterMode === 'force_disabled_all')
            ? "عذراً، خدمة الاستعلام التلقائي عبر الواتساب موقوفة حالياً بقرار من مدير النظام."
            : "عذراً، خدمة سحب كشف الحساب أو معرفة الرصيد عبر الواتساب غير مفعّلة لحسابكم حالياً.\nيرجى مراجعة مدير النظام لتفعيل الصلاحية.";

        return [
            'success' => true,
            'matched' => true,
            'allowed' => false,
            'access_mode' => 'disabled',
            'entity_type' => $matchedEntity['type'],
            'entity_id' => $matchedEntity['id'],
            'entity_name' => $matchedEntity['name'],
            'code_num' => $matchedEntity['code_num'],
            'code_word' => $matchedEntity['code_word'],
            'reply_text' => "🔒 *خدمة الاستعلام الحسابي غير مفعّلة*\n" .
                            "━━━━━━━━━━━━━━\n" .
                            "مرحباً بك: *{$matchedEntity['name']}* ({$codeDisplay})\n" .
                            $disabledReasonLine
        ];
    }

    // Determine final output mode ('balance_only' vs 'detailed')
    $renderMode = ($effectiveMode === 'balance_only' || $explicitBalanceOnlyRequest) ? 'balance_only' : 'detailed';

    // Anti-Spam Cooldown: Prevent network double-delivery within 1 second without blocking interactive queries
    $enableCooldown = !isset($policy['antiSpamCooldown']) || (bool)$policy['antiSpamCooldown'];
    if ($enableCooldown) {
        $cooldownFile = sys_get_temp_dir() . '/elsajana_wa_stmt_cooldown.json';
        $cooldownSec = 1; // 1 second to prevent accidental double webhook triggers
        $nowTs = time();
        $cdData = [];
        if (file_exists($cooldownFile)) {
            $cdRaw = @file_get_contents($cooldownFile);
            $cdData = json_decode((string)$cdRaw, true) ?: [];
        }
        foreach ($cdData as $k => $ts) {
            if (($nowTs - (int)$ts) >= $cooldownSec) {
                unset($cdData[$k]);
            }
        }
        $cdKey = $senderKey . '_' . $matchedEntity['type'] . '_' . $matchedEntity['id'] . '_' . $renderMode . '_' . substr(md5($normMsg), 0, 8);
        if (isset($cdData[$cdKey])) {
            $elapsed = $nowTs - (int)$cdData[$cdKey];
            if ($elapsed < $cooldownSec) {
                return [
                    'success' => true,
                    'matched' => false,
                    'allowed' => false,
                    'blocked_reason' => 'cooldown_dedup'
                ];
            }
        }
        $cdData[$cdKey] = $nowTs;
        @file_put_contents($cooldownFile, json_encode($cdData));
    }

    // Build statement or balance summary according to mode and System Admin policy
    $stmtResult = buildDetailedAccountStatementForWhatsApp(
        $pdo,
        $tenantId,
        $matchedEntity['type'],
        $matchedEntity['obj'],
        [
            'mode' => $renderMode,
            'effectiveAccessMode' => $effectiveMode,
            'statementScope' => $policy['statementScope'] ?? 'all',
            'includeInvoiceItems' => isset($policy['includeInvoiceItems']) ? (bool)$policy['includeInvoiceItems'] : true,
            'statementPin' => $matchedEntity['statement_pin'] ?? '',
            'policy' => $policy,
            'returnStructured' => true
        ]
    );
    $statementText = is_array($stmtResult) ? ($stmtResult['text'] ?? '') : (string)$stmtResult;
    $voucherData = is_array($stmtResult) ? ($stmtResult['voucher_data'] ?? null) : null;

    // Optional System Admin audit notification
    $adminNotification = null;
    if (!empty($policy['notifyAdminOnRequest'])) {
        $modeAr = ($renderMode === 'balance_only') ? 'معرفة الرصيد فقط' : 'كشف حساب مفصل';
        $senderDisp = $senderPhone ?: 'غير معروف';
        $adminNotification = "🛡️ *إشعار رقابي لمدير النظام*\n" .
                             "━━━━━━━━━━━━━━\n" .
                             "تم إجراء استعلام تلقائي عبر الواتساب:\n" .
                             "• *الحساب:* {$matchedEntity['name']} ({$matchedEntity['type_label']})\n" .
                             "• *الكود:* {$codeDisplay}\n" .
                             "• *نوع الاستعلام:* {$modeAr}\n" .
                             "• *رقم المرسل:* {$senderDisp}\n" .
                             "• *الوقت:* " . date('Y-m-d H:i');
    }

    return [
        'success' => true,
        'matched' => true,
        'allowed' => true,
        'access_mode' => $effectiveMode,
        'render_mode' => $renderMode,
        'entity_type' => $matchedEntity['type'],
        'entity_id' => $matchedEntity['id'],
        'entity_name' => $matchedEntity['name'],
        'code_num' => $matchedEntity['code_num'],
        'code_word' => $matchedEntity['code_word'],
        'reply_text' => $statementText,
        'voucher_data' => $voucherData,
        'admin_notification' => $adminNotification
    ];
}

// -------------------------------------------------------------
// Helper: Build Detailed Chronological Account Statement or Balance Summary for Customer or Supplier
// -------------------------------------------------------------
function buildDetailedAccountStatementForWhatsApp($pdo, $tenantId, $entityType, $entityObj, $options = []) {
    $renderMode = $options['mode'] ?? 'detailed'; // 'detailed' | 'balance_only'
    $effectiveAccessMode = $options['effectiveAccessMode'] ?? $renderMode;
    $statementScope = $options['statementScope'] ?? 'all'; // 'all' | 'last_20' | 'last_50' | 'current_month'
    $includeInvoiceItems = isset($options['includeInvoiceItems']) ? (bool)$options['includeInvoiceItems'] : true;
    $statementPin = trim((string)($options['statementPin'] ?? ($entityObj['whatsappStatementPin'] ?? '')));
    $returnStructured = !empty($options['returnStructured']);

    $isCustomer = ($entityType === 'customer');
    $rawId = trim((string)($entityObj['id'] ?? ''));
    $cleanId = preg_replace('/^(cust|supp?)-/i', '', $rawId);
    $entityName = trim((string)($entityObj['name'] ?? ''));
    $codeNum = isset($entityObj['receiptCodeNum']) ? (int)$entityObj['receiptCodeNum'] : 0;
    $codeWord = trim((string)($entityObj['receiptCodeWord'] ?? ''));
    $entityCurrency = trim((string)($entityObj['currency'] ?? 'SDG')) ?: 'SDG';
    $currency = 'SDG';

    // Resolve System USD Exchange Rate from global_settings
    $systemUsdRate = 6000.0;
    try {
        $tenantRateKey = 'tenant_rate_' . $tenantId;
        $stmtRate = $pdo->prepare("SELECT setting_key, setting_value FROM global_settings WHERE setting_key IN (?, 'exchange_rate', 'erp_rate_usd', 'erp_currency_rates_v1')");
        $stmtRate->execute([$tenantRateKey]);
        $rateRows = $stmtRate->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        if (!empty($rateRows['exchange_rate'])) {
            $val = trim((string)$rateRows['exchange_rate']);
            if (is_numeric($val) && (float)$val > 1) {
                $systemUsdRate = (float)$val;
            } else {
                $dec = json_decode($val, true);
                if (is_array($dec)) {
                    $r = (float)($dec['rate'] ?? ($dec['current'] ?? ($dec['rateUSD'] ?? ($dec['USD'] ?? 0))));
                    if ($r > 1) $systemUsdRate = $r;
                }
            }
        } elseif (!empty($rateRows[$tenantRateKey])) {
            $val = trim((string)$rateRows[$tenantRateKey]);
            if (is_numeric($val) && (float)$val > 1) {
                $systemUsdRate = (float)$val;
            } else {
                $dec = json_decode($val, true);
                if (is_array($dec)) {
                    $r = (float)($dec['rateUSD'] ?? ($dec['current'] ?? ($dec['rate'] ?? 0)));
                    if ($r > 1) $systemUsdRate = $r;
                }
            }
        } elseif (!empty($rateRows['erp_rate_usd']) && is_numeric($rateRows['erp_rate_usd']) && (float)$rateRows['erp_rate_usd'] > 1) {
            $systemUsdRate = (float)$rateRows['erp_rate_usd'];
        } elseif (!empty($rateRows['erp_currency_rates_v1'])) {
            $dec = json_decode((string)$rateRows['erp_currency_rates_v1'], true);
            if (is_array($dec) && !empty($dec['USD']) && (float)$dec['USD'] > 1) {
                $systemUsdRate = (float)$dec['USD'];
            }
        }
    } catch (Exception $e) {}
    if ($systemUsdRate <= 1) {
        $systemUsdRate = 6000.0;
    }

    // =========================================================================
    // 1-TO-1 EXACT PORT OF LAPTOP GENERAL LEDGER (`js/app.js` + `js/general-ledger.js`)
    // Guarantees 100% mathematical & row-by-row parity between Laptop and WhatsApp
    // =========================================================================
    $jsStrOr = function(...$vals) {
        foreach ($vals as $v) {
            if (is_array($v)) {
                $sub = $v['id'] ?? $v['accountId'] ?? $v['name'] ?? '';
                if (is_scalar($sub) && (string)$sub !== '') return (string)$sub;
                continue;
            }
            if ($v !== null && $v !== false && is_scalar($v) && (string)$v !== '') {
                return (string)$v;
            }
        }
        return '';
    };

    $parseNum = function($val) {
        if ($val === null || $val === false || $val === '') return 0.0;
        if (is_numeric($val)) return (float)$val;
        $clean = str_replace(',', '', trim((string)$val));
        return is_numeric($clean) ? (float)$clean : 0.0;
    };

    $accVal = ($isCustomer ? 'cust-' : 'supp-') . $cleanId;

    $getEffectiveRate = function($item, $txnRate) use ($systemUsdRate, $parseNum) {
        $saved = $parseNum($item['systemExchangeRate'] ?? 0);
        if ($saved > 1) return $saved;
        if ($txnRate && $txnRate > 1) return (float)$txnRate;
        return ($systemUsdRate > 0 ? $systemUsdRate : 1.0);
    };

    $loadMaster = function($table) use ($pdo, $tenantId) {
        try {
            $stmt = $pdo->prepare("SELECT data FROM `$table` WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
            $stmt->execute([$tenantId]);
            $res = [];
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'] ?? '', true);
                if (is_array($d) && empty($d['deleted'])) $res[] = $d;
            }
            return $res;
        } catch (Throwable $e) {
            return [];
        }
    };

    $custs = $loadMaster('customers');
    $sups = $loadMaster('suppliers');
    $banks = $loadMaster('banks');
    $safes = $loadMaster('safes');
    $accounts = $loadMaster('accounts');

    $accountMap = [];
    $accountNameCache = [];
    $registerAcc = function($item, $prefix) use (&$accountMap, &$accountNameCache) {
        if (!is_array($item)) return;
        $id = trim((string)($item['id'] ?? ''));
        $name = trim((string)($item['name'] ?? $item['bankName'] ?? $item['safeName'] ?? $item['accountName'] ?? ''));
        $code = trim((string)($item['code'] ?? ''));
        $canonicalId = $id;
        if ($prefix !== '' && strpos($id, $prefix) !== 0) {
            $canonicalId = $prefix . $id;
        }
        if ($id !== '') {
            $accountMap[mb_strtolower($id)] = $canonicalId;
            $clean = preg_replace('/^(cust|supp|bank|safe|exp)-/i', '', $id);
            $accountMap[mb_strtolower($clean)] = $canonicalId;
            if ($name !== '') {
                $accountNameCache[mb_strtolower($canonicalId)] = $name;
                $accountNameCache[mb_strtolower($clean)] = $name;
            }
        }
        if ($name !== '') {
            $accountMap[mb_strtolower($name)] = $canonicalId;
            if (!isset($accountNameCache[mb_strtolower($name)])) {
                $accountNameCache[mb_strtolower($name)] = $name;
            }
        }
        if ($code !== '') {
            $accountMap[mb_strtolower($code)] = $canonicalId;
        }
    };

    foreach ($custs as $c) $registerAcc($c, 'cust-');
    foreach ($sups as $s) $registerAcc($s, 'supp-');
    foreach ($banks as $b) $registerAcc($b, 'bank-');
    foreach ($safes as $sf) $registerAcc($sf, 'safe-');
    foreach ($accounts as $a) $registerAcc($a, 'exp-');

    $resolveAccount = function($accValInput, $fallback = '') use (&$accountMap) {
        if (is_array($accValInput)) {
            $accValInput = $accValInput['id'] ?? $accValInput['accountId'] ?? $accValInput['name'] ?? '';
        }
        if (!is_scalar($accValInput) || trim((string)$accValInput) === '') return $fallback;
        $str = mb_strtolower(trim((string)$accValInput));
        if (isset($accountMap[$str])) return $accountMap[$str];
        $cleanStr = preg_replace('/^(cust|supp|bank|safe|exp)-/i', '', $str);
        if (isset($accountMap[$cleanStr])) return $accountMap[$cleanStr];
        return trim((string)$accValInput);
    };

    $getAccountName = function($val) use (&$accountNameCache) {
        if ($val === 'ALL') return 'جميع الحسابات';
        if ($val === '-' || $val === '' || $val === null) return '-';
        $idLower = mb_strtolower(trim((string)$val));
        $name = '';
        if (isset($accountNameCache[$idLower])) {
            $name = $accountNameCache[$idLower];
        } else {
            $cleanIdSub = trim(preg_replace('/^[a-zA-Z]+\s*[-_:\/]\s*/', '', $idLower));
            if (isset($accountNameCache[$cleanIdSub])) {
                $name = $accountNameCache[$cleanIdSub];
            }
        }
        if ($name === '') {
            $name = (string)$val;
        }
        $name = preg_replace('/^\[\d+\]\s*/u', '', $name);
        $name = preg_replace('/^\d+\s*[-–]\s*/u', '', $name);
        return trim($name);
    };

    // Deduplicate source table rows by voucherNo/id exactly like `loadSourceTable` in `js/app.js`
    $loadSourceTable = function($table) use ($pdo, $tenantId, $jsStrOr) {
        try {
            $stmt = $pdo->prepare("SELECT id, data FROM `$table` WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
            $stmt->execute([$tenantId]);
            $map = [];
            $seq = 0;
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $v = json_decode($r['data'] ?? '', true);
                if (!is_array($v)) continue;
                $seq++;
                $key = trim((string)$jsStrOr($v['voucherNo'] ?? null, $v['id'] ?? null, $v['paymentNo'] ?? null, $v['receiptNo'] ?? null, $v['invoiceNo'] ?? null, '_row_' . $r['id'] . '_' . $seq));
                $map[$key] = $v;
            }
            $result = [];
            foreach ($map as $v) {
                if (!empty($v['deleted'])) continue;
                $result[] = $v;
            }
            return $result;
        } catch (Throwable $e) {
            return [];
        }
    };

    $txns = [];
    $timeNow = '00:00';

    // 1. Single Receipts (`single_receipts`)
    $singleReceipts = $loadSourceTable('single_receipts');
    foreach ($singleReceipts as $v) {
        $costCenter = $jsStrOr($v['costCenter'] ?? null, 'بدون مركز');
        $vCurrency = $jsStrOr($v['currency'] ?? null, 'SDG');
        $time = $jsStrOr($v['time'] ?? null, $timeNow);
        $isPosted = !isset($v['isPosted']) || $v['isPosted'] !== false;

        $rate = $parseNum($v['rate'] ?? 1);
        $effRate = $getEffectiveRate($v, $rate);
        $isBaseSDG = ($vCurrency === 'SDG');
        $amount = $parseNum(isset($v['amount']) ? $v['amount'] : ($isBaseSDG ? ($v['amountLocal'] ?? 0) : ($v['amountForeign'] ?? 0)));
        $amountLocal = $parseNum($jsStrOr($v['amountLocal'] ?? null) ?: ($amount * $effRate));
        $valLoc = $amountLocal;
        $valFc = $isBaseSDG ? ($amountLocal / $effRate) : $amount;
        $toAcct = $resolveAccount($jsStrOr($v['toAccount'] ?? null, 'safe-001'));
        $fromAcct = $resolveAccount($v['fromAccount'] ?? '');
        $refNo = $jsStrOr($v['voucherNo'] ?? null, $v['id'] ?? null);

        $hasTransfers = !empty($v['bankTransfers']) && is_array($v['bankTransfers']);
        $hasCheques = !empty($v['cheques']) && is_array($v['cheques']);
        $pm = $v['paymentMethod'] ?? '';

        if ($hasTransfers && $pm !== 'cash') {
            foreach ($v['bankTransfers'] as $bt) {
                $amt = $parseNum($bt['amount'] ?? 0);
                $amtLoc = $isBaseSDG ? $amt : ($amt > $valFc * 1.5 ? $amt : $amt * $effRate);
                $amtFc = $isBaseSDG ? ($amt / $effRate) : ($amt > $valFc * 1.5 ? ($amt / $effRate) : $amt);
                $stmt = $jsStrOr($bt['statement'] ?? null, $v['statement'] ?? null, $v['fromStatement'] ?? null, 'تحويل بنكي - سند قبض #' . $refNo);

                $txns[] = [
                    'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'receipt', 'typeLabel' => 'سند قبض',
                    'account' => $toAcct, 'counterAccount' => $fromAcct,
                    'statement' => $stmt, 'additionalNote' => $stmt,
                    'transferNo' => $bt['transferNo'] ?? '', 'bankName' => $bt['note'] ?? '',
                    'debit' => $amtLoc, 'credit' => 0, 'debitForeign' => $amtFc, 'creditForeign' => 0,
                    'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                ];
                $txns[] = [
                    'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'receipt', 'typeLabel' => 'سند صرف',
                    'account' => $fromAcct, 'counterAccount' => $toAcct,
                    'statement' => $stmt, 'additionalNote' => $stmt,
                    'transferNo' => $bt['transferNo'] ?? '', 'bankName' => $bt['note'] ?? '',
                    'debit' => 0, 'credit' => $amtLoc, 'debitForeign' => 0, 'creditForeign' => $amtFc,
                    'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                ];
            }
        }
        if ($hasCheques && $pm !== 'cash') {
            foreach ($v['cheques'] as $ch) {
                $amt = $parseNum($ch['amount'] ?? 0);
                $amtLoc = $isBaseSDG ? $amt : ($amt > $valFc * 1.5 ? $amt : $amt * $effRate);
                $amtFc = $isBaseSDG ? ($amt / $effRate) : ($amt > $valFc * 1.5 ? ($amt / $effRate) : $amt);
                $chkLabel = !empty($ch['checkNo']) ? ('الشيك ' . $ch['checkNo']) : 'الشيك';
                $stmt = $jsStrOr($ch['statement'] ?? null, $chkLabel . ' / قبض نقدية');

                $txns[] = [
                    'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'receipt', 'typeLabel' => 'سند قبض',
                    'account' => $toAcct, 'counterAccount' => $fromAcct,
                    'statement' => $stmt, 'additionalNote' => $stmt,
                    'checkNo' => $ch['checkNo'] ?? '', 'bankName' => $ch['checkBranch'] ?? '',
                    'debit' => $amtLoc, 'credit' => 0, 'debitForeign' => $amtFc, 'creditForeign' => 0,
                    'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                ];
                $txns[] = [
                    'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'receipt', 'typeLabel' => 'سند صرف',
                    'account' => $fromAcct, 'counterAccount' => $toAcct,
                    'statement' => $stmt, 'additionalNote' => $stmt,
                    'checkNo' => $ch['checkNo'] ?? '', 'bankName' => $ch['checkBranch'] ?? '',
                    'debit' => 0, 'credit' => $amtLoc, 'debitForeign' => 0, 'creditForeign' => $amtFc,
                    'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                ];
            }
        }
        if (!$hasTransfers && !$hasCheques) {
            $singleTr = $jsStrOr($v['transferNo'] ?? null, $v['mobileTransferNo'] ?? null);
            $singleBank = $jsStrOr($v['bankName'] ?? null, $v['mobileNote'] ?? null);
            $singleChk = $jsStrOr($v['checkNo'] ?? null, $v['chequeNo'] ?? null);
            $stmt = $jsStrOr($v['statement'] ?? null, 'سند قبض #' . $refNo);
            $addNote = $jsStrOr($v['fromStatement'] ?? null, 'سند قبض نقدي');
            $txns[] = [
                'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'receipt', 'typeLabel' => 'سند قبض',
                'account' => $toAcct, 'counterAccount' => $fromAcct,
                'statement' => $stmt, 'additionalNote' => $addNote,
                'transferNo' => $singleTr, 'bankName' => $singleBank, 'checkNo' => $singleChk,
                'debit' => $valLoc, 'credit' => 0, 'debitForeign' => $valFc, 'creditForeign' => 0,
                'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
            ];
            $txns[] = [
                'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'receipt', 'typeLabel' => 'سند صرف',
                'account' => $fromAcct, 'counterAccount' => $toAcct,
                'statement' => $stmt, 'additionalNote' => $addNote,
                'transferNo' => $singleTr, 'bankName' => $singleBank, 'checkNo' => $singleChk,
                'debit' => 0, 'credit' => $valLoc, 'debitForeign' => 0, 'creditForeign' => $valFc,
                'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
            ];
        }
    }

    // 2. Single Payments (`single_payments`)
    $singlePayments = $loadSourceTable('single_payments');
    foreach ($singlePayments as $v) {
        $costCenter = $jsStrOr($v['costCenter'] ?? null, 'بدون مركز');
        $vCurrency = $jsStrOr($v['currency'] ?? null, 'SDG');
        $time = $jsStrOr($v['time'] ?? null, $timeNow);
        $isPosted = !isset($v['isPosted']) || $v['isPosted'] !== false;

        $rate = $parseNum($v['rate'] ?? 1);
        $effRate = $getEffectiveRate($v, $rate);
        $isBaseSDG = ($vCurrency === 'SDG');
        $amount = $parseNum(isset($v['amount']) ? $v['amount'] : ($isBaseSDG ? ($v['amountLocal'] ?? 0) : ($v['amountForeign'] ?? 0)));
        $amountLocal = $parseNum($jsStrOr($v['amountLocal'] ?? null) ?: ($amount * $effRate));
        $valLoc = $amountLocal;
        $valFc = $isBaseSDG ? ($amountLocal / $effRate) : $amount;
        $fromAcct = $resolveAccount($jsStrOr($v['fromAccount'] ?? null, 'safe-001'));
        $toAcct = $resolveAccount($v['toAccount'] ?? '');
        $refNo = $jsStrOr($v['voucherNo'] ?? null, $v['id'] ?? null);

        $hasTransfers = !empty($v['bankTransfers']) && is_array($v['bankTransfers']);
        $hasCheques = !empty($v['cheques']) && is_array($v['cheques']);
        $pm = $v['paymentMethod'] ?? '';

        if ($hasTransfers && $pm !== 'cash') {
            foreach ($v['bankTransfers'] as $bt) {
                $amt = $parseNum($bt['amount'] ?? 0);
                $amtLoc = $isBaseSDG ? $amt : ($amt > $valFc * 1.5 ? $amt : $amt * $effRate);
                $amtFc = $isBaseSDG ? ($amt / $effRate) : ($amt > $valFc * 1.5 ? ($amt / $effRate) : $amt);
                $stmt = $jsStrOr($bt['statement'] ?? null, $v['statement'] ?? null, $v['toStatement'] ?? null, 'تحويل بنكي - سند صرف #' . $refNo);

                $txns[] = [
                    'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'payment', 'typeLabel' => 'سند صرف',
                    'account' => $toAcct, 'counterAccount' => $fromAcct,
                    'statement' => $stmt, 'additionalNote' => $stmt,
                    'transferNo' => $bt['transferNo'] ?? '', 'bankName' => $bt['note'] ?? '',
                    'debit' => $amtLoc, 'credit' => 0, 'debitForeign' => $amtFc, 'creditForeign' => 0,
                    'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                ];
                $txns[] = [
                    'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'payment', 'typeLabel' => 'سند صرف',
                    'account' => $fromAcct, 'counterAccount' => $toAcct,
                    'statement' => $stmt, 'additionalNote' => $stmt,
                    'transferNo' => $bt['transferNo'] ?? '', 'bankName' => $bt['note'] ?? '',
                    'debit' => 0, 'credit' => $amtLoc, 'debitForeign' => 0, 'creditForeign' => $amtFc,
                    'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                ];
            }
        }
        if ($hasCheques && $pm !== 'cash') {
            foreach ($v['cheques'] as $ch) {
                $amt = $parseNum($ch['amount'] ?? 0);
                $amtLoc = $isBaseSDG ? $amt : ($amt > $valFc * 1.5 ? $amt : $amt * $effRate);
                $amtFc = $isBaseSDG ? ($amt / $effRate) : ($amt > $valFc * 1.5 ? ($amt / $effRate) : $amt);
                $chkLabel = !empty($ch['checkNo']) ? ('الشيك ' . $ch['checkNo']) : 'الشيك';
                $stmt = $jsStrOr($ch['statement'] ?? null, $chkLabel . ' / صرف نقدية');

                $txns[] = [
                    'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'payment', 'typeLabel' => 'سند صرف',
                    'account' => $toAcct, 'counterAccount' => $fromAcct,
                    'statement' => $stmt, 'additionalNote' => $stmt,
                    'checkNo' => $ch['checkNo'] ?? '', 'bankName' => $ch['checkBranch'] ?? '',
                    'debit' => $amtLoc, 'credit' => 0, 'debitForeign' => $amtFc, 'creditForeign' => 0,
                    'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                ];
                $txns[] = [
                    'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'payment', 'typeLabel' => 'سند صرف',
                    'account' => $fromAcct, 'counterAccount' => $toAcct,
                    'statement' => $stmt, 'additionalNote' => $stmt,
                    'checkNo' => $ch['checkNo'] ?? '', 'bankName' => $ch['checkBranch'] ?? '',
                    'debit' => 0, 'credit' => $amtLoc, 'debitForeign' => 0, 'creditForeign' => $amtFc,
                    'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                ];
            }
        }
        if (!$hasTransfers && !$hasCheques) {
            $singleTr = $jsStrOr($v['transferNo'] ?? null, $v['mobileTransferNo'] ?? null);
            $singleBank = $jsStrOr($v['bankName'] ?? null, $v['mobileNote'] ?? null);
            $singleChk = $jsStrOr($v['checkNo'] ?? null, $v['chequeNo'] ?? null);
            $stmt = $jsStrOr($v['statement'] ?? null, 'سند صرف #' . $refNo);
            $addNote = $jsStrOr($v['toStatement'] ?? null, 'سند صرف نقدي');
            $txns[] = [
                'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'payment', 'typeLabel' => 'سند صرف',
                'account' => $toAcct, 'counterAccount' => $fromAcct,
                'statement' => $stmt, 'additionalNote' => $addNote,
                'transferNo' => $singleTr, 'bankName' => $singleBank, 'checkNo' => $singleChk,
                'debit' => $valLoc, 'credit' => 0, 'debitForeign' => $valFc, 'creditForeign' => 0,
                'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
            ];
            $txns[] = [
                'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'payment', 'typeLabel' => 'سند صرف',
                'account' => $fromAcct, 'counterAccount' => $toAcct,
                'statement' => $stmt, 'additionalNote' => $addNote,
                'transferNo' => $singleTr, 'bankName' => $singleBank, 'checkNo' => $singleChk,
                'debit' => 0, 'credit' => $valLoc, 'debitForeign' => 0, 'creditForeign' => $valFc,
                'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
            ];
        }
    }

    // 2b. Multi-Receipts (`receipts`)
    $multiReceipts = $loadSourceTable('receipts');
    foreach ($multiReceipts as &$v) {
        $vNo = (string)($v['voucherNo'] ?? '');
        $vId = (string)($v['id'] ?? '');
        if ($vNo !== '' && strpos($vNo, 'MV-') !== 0 && strpos($vNo, 'MV17') !== 0) {
            $v['id'] = $vNo;
        } elseif ($vId !== '' && strpos($vId, 'MV-') !== 0 && strpos($vId, 'MV17') !== 0) {
            $v['voucherNo'] = $vId;
        }
    }
    unset($v);
    foreach ($multiReceipts as $v) {
        $costCenter = $jsStrOr($v['costCenter'] ?? null, 'بدون مركز');
        $vCurrency = $jsStrOr($v['currency'] ?? null, 'SDG');
        $time = $jsStrOr($v['time'] ?? null, $timeNow);
        $isPosted = !isset($v['isPosted']) || $v['isPosted'] !== false;
        $mainAcc = $resolveAccount($jsStrOr($v['toAccount'] ?? null, $v['mainAccount'] ?? null, $v['depositAccount'] ?? null, 'safe-001'), 'safe-001');
        $vNo = (string)($v['voucherNo'] ?? '');
        $vId = (string)($v['id'] ?? '');
        $refNo = ($vNo !== '' && strpos($vNo, 'MV-') !== 0) ? $vNo : (($vId !== '' && strpos($vId, 'MV-') !== 0) ? $vId : $jsStrOr($vNo, $vId));

        $rate = $parseNum($v['exchangeRate'] ?? 1);
        $effRate = $getEffectiveRate($v, $rate);
        $isBaseSDG = ($vCurrency === 'SDG');

        if (!empty($v['items']) && is_array($v['items'])) {
            foreach ($v['items'] as $item) {
                if (!is_array($item)) continue;
                $pms = (!empty($item['paymentMethods']) && is_array($item['paymentMethods'])) ? $item['paymentMethods'] : [$jsStrOr($item['paymentMethod'] ?? null, 'cash')];
                $itemAcct = $resolveAccount($jsStrOr($item['accountId'] ?? null, $item['account'] ?? null, $item['accountName'] ?? null), 'unknown');
                $itemTotal = $parseNum($item['amount'] ?? 0);

                $cashAmt = $parseNum($item['cashAmount'] ?? 0);
                $bankAmt = $parseNum($item['bankAmount'] ?? 0);
                $chequeAmt = $parseNum($item['chequeAmount'] ?? 0);

                if ($itemTotal > 0 && $cashAmt <= 0 && $bankAmt <= 0 && $chequeAmt <= 0) {
                    if (in_array('bank', $pms, true)) $bankAmt = $itemTotal;
                    elseif (in_array('cheque', $pms, true)) $chequeAmt = $itemTotal;
                    else $cashAmt = $itemTotal;
                }

                if ((in_array('cash', $pms, true) || $cashAmt > 0) && $cashAmt > 0) {
                    $amtLoc = $isBaseSDG ? $cashAmt : $cashAmt * $effRate;
                    $amtFc = $isBaseSDG ? ($cashAmt / $effRate) : $cashAmt;
                    $safeAcct = $mainAcc;
                    $baseStmt = $jsStrOr($item['cashStatement'] ?? null, $item['statement'] ?? null, $v['description'] ?? null, 'قبض نقدية');
                    $stmt = !empty($item['cashSafe']) ? ("خزينة: {$item['cashSafe']} - {$baseStmt}") : $baseStmt;

                    $txns[] = [
                        'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'multi-receipt', 'typeLabel' => 'سند قبض مجمع',
                        'account' => $safeAcct, 'counterAccount' => $itemAcct,
                        'statement' => $jsStrOr($v['description'] ?? null, $stmt),
                        'additionalNote' => $jsStrOr($v['description'] ?? null, $stmt),
                        'debit' => $amtLoc, 'credit' => 0, 'debitForeign' => $amtFc, 'creditForeign' => 0,
                        'costCenter' => $jsStrOr($item['costCenter'] ?? null, $costCenter), 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                    ];
                    $txns[] = [
                        'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'multi-receipt', 'typeLabel' => 'سند قبض مجمع',
                        'account' => $itemAcct, 'counterAccount' => $safeAcct,
                        'statement' => $stmt, 'additionalNote' => $stmt,
                        'debit' => 0, 'credit' => $amtLoc, 'debitForeign' => 0, 'creditForeign' => $amtFc,
                        'costCenter' => $jsStrOr($item['costCenter'] ?? null, $costCenter), 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                    ];
                }

                if ((in_array('bank', $pms, true) || $bankAmt > 0) && $bankAmt > 0) {
                    $transfers = (!empty($item['bankTransfers']) && is_array($item['bankTransfers'])) ? $item['bankTransfers'] : [[
                        'amount' => $bankAmt,
                        'bankName' => $item['bankName'] ?? '',
                        'transferNo' => $item['transferNo'] ?? '',
                        'statement' => $item['bankStatement'] ?? ''
                    ]];
                    foreach ($transfers as $bt) {
                        $amt = $parseNum($jsStrOr($bt['amount'] ?? null) ?: $bankAmt);
                        if ($amt <= 0) continue;
                        $amtLoc = $isBaseSDG ? $amt : $amt * $effRate;
                        $amtFc = $isBaseSDG ? ($amt / $effRate) : $amt;
                        $bankAcct = $mainAcc;
                        $baseStmt = $jsStrOr($bt['statement'] ?? null, $item['statement'] ?? null, $v['description'] ?? null, 'قبض تحويل بنكي');
                        $stmt = !empty($bt['bankName']) ? ("بنك: {$bt['bankName']} - {$baseStmt}") : $baseStmt;

                        $txns[] = [
                            'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'multi-receipt', 'typeLabel' => 'سند قبض مجمع',
                            'account' => $bankAcct, 'counterAccount' => $itemAcct,
                            'statement' => $jsStrOr($v['description'] ?? null, $stmt),
                            'additionalNote' => $jsStrOr($v['description'] ?? null, $stmt),
                            'transferNo' => $bt['transferNo'] ?? '', 'bankName' => $jsStrOr($bt['bankName'] ?? null, $bt['note'] ?? null),
                            'debit' => $amtLoc, 'credit' => 0, 'debitForeign' => $amtFc, 'creditForeign' => 0,
                            'costCenter' => $jsStrOr($item['costCenter'] ?? null, $costCenter), 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                        ];
                        $txns[] = [
                            'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'multi-receipt', 'typeLabel' => 'سند قبض مجمع',
                            'account' => $itemAcct, 'counterAccount' => $bankAcct,
                            'statement' => $stmt, 'additionalNote' => $stmt,
                            'transferNo' => $bt['transferNo'] ?? '', 'bankName' => $jsStrOr($bt['bankName'] ?? null, $bt['note'] ?? null),
                            'debit' => 0, 'credit' => $amtLoc, 'debitForeign' => 0, 'creditForeign' => $amtFc,
                            'costCenter' => $jsStrOr($item['costCenter'] ?? null, $costCenter), 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                        ];
                    }
                }

                if ((in_array('cheque', $pms, true) || $chequeAmt > 0) && $chequeAmt > 0) {
                    $cheques = (!empty($item['cheques']) && is_array($item['cheques'])) ? $item['cheques'] : [[
                        'amount' => $chequeAmt,
                        'chequeBank' => $item['chequeBank'] ?? '',
                        'chequeNo' => $jsStrOr($item['chequeNo'] ?? null, $item['checkNo'] ?? null),
                        'chequeDate' => $jsStrOr($item['chequeDate'] ?? null, $item['checkDate'] ?? null),
                        'statement' => $item['chequeStatement'] ?? ''
                    ]];
                    foreach ($cheques as $ch) {
                        $amt = $parseNum($jsStrOr($ch['amount'] ?? null) ?: $chequeAmt);
                        if ($amt <= 0) continue;
                        $amtLoc = $isBaseSDG ? $amt : $amt * $effRate;
                        $amtFc = $isBaseSDG ? ($amt / $effRate) : $amt;
                        $chequeAcct = $mainAcc;
                        $baseStmt = $jsStrOr($ch['statement'] ?? null, $item['statement'] ?? null, $v['description'] ?? null, 'قبض شيك');
                        $stmt = !empty($ch['chequeBank']) ? ("شيك بنك: {$ch['chequeBank']} - {$baseStmt}") : $baseStmt;

                        $txns[] = [
                            'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'multi-receipt', 'typeLabel' => 'سند قبض مجمع',
                            'account' => $chequeAcct, 'counterAccount' => $itemAcct,
                            'statement' => $jsStrOr($v['description'] ?? null, $stmt),
                            'additionalNote' => $jsStrOr($v['description'] ?? null, $stmt),
                            'checkNo' => $jsStrOr($ch['chequeNo'] ?? null, $ch['checkNo'] ?? null), 'bankName' => $jsStrOr($ch['chequeBank'] ?? null, $ch['checkBank'] ?? null),
                            'debit' => $amtLoc, 'credit' => 0, 'debitForeign' => $amtFc, 'creditForeign' => 0,
                            'costCenter' => $jsStrOr($item['costCenter'] ?? null, $costCenter), 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                        ];
                        $txns[] = [
                            'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'multi-receipt', 'typeLabel' => 'سند قبض مجمع',
                            'account' => $itemAcct, 'counterAccount' => $chequeAcct,
                            'statement' => $stmt, 'additionalNote' => $stmt,
                            'checkNo' => $jsStrOr($ch['chequeNo'] ?? null, $ch['checkNo'] ?? null), 'bankName' => $jsStrOr($ch['chequeBank'] ?? null, $ch['checkBank'] ?? null),
                            'debit' => 0, 'credit' => $amtLoc, 'debitForeign' => 0, 'creditForeign' => $amtFc,
                            'costCenter' => $jsStrOr($item['costCenter'] ?? null, $costCenter), 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                        ];
                    }
                }
            }
        } elseif (!empty($v['details']) && is_array($v['details'])) {
            foreach ($v['details'] as $det) {
                if (!is_array($det) || (empty($det['accountId']) && empty($det['account']))) continue;
                $detAmtLocal = $parseNum($jsStrOr($det['amountLocal'] ?? null, $det['amount'] ?? null));
                $detAmtForeign = $parseNum($det['amountForeign'] ?? 0);
                if ($detAmtLocal <= 0 && $detAmtForeign <= 0) continue;
                $detAcct = $resolveAccount($jsStrOr($det['accountId'] ?? null, $det['account'] ?? null));
                $detRate = $parseNum($jsStrOr($det['rate'] ?? null) ?: $effRate);
                $stmt = $jsStrOr($det['note'] ?? null, $det['statement'] ?? null, $v['statement'] ?? null, $v['description'] ?? null, 'قبض نقدية');
                $txns[] = [
                    'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'receipt', 'typeLabel' => 'سند قبض نقدية',
                    'account' => $mainAcc, 'counterAccount' => $detAcct,
                    'statement' => $stmt, 'additionalNote' => $stmt,
                    'debit' => $detAmtLocal, 'credit' => 0, 'debitForeign' => $detAmtForeign, 'creditForeign' => 0,
                    'costCenter' => $jsStrOr($det['costCenter'] ?? null, $costCenter), 'currency' => $vCurrency, 'exchangeRate' => $detRate, 'isPosted' => $isPosted
                ];
                $txns[] = [
                    'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'receipt', 'typeLabel' => 'سند قبض نقدية',
                    'account' => $detAcct, 'counterAccount' => $mainAcc,
                    'statement' => $stmt, 'additionalNote' => $stmt,
                    'debit' => 0, 'credit' => $detAmtLocal, 'debitForeign' => 0, 'creditForeign' => $detAmtForeign,
                    'costCenter' => $jsStrOr($det['costCenter'] ?? null, $costCenter), 'currency' => $vCurrency, 'exchangeRate' => $detRate, 'isPosted' => $isPosted
                ];
            }
        } else {
            $totalAmount = $parseNum($v['totalAmount'] ?? 0);
            $valLoc = $isBaseSDG ? $totalAmount : $totalAmount * $effRate;
            $valFc = $isBaseSDG ? ($totalAmount / $effRate) : $totalAmount;
            $txns[] = [
                'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'multi-receipt', 'typeLabel' => 'سند قبض مجمع',
                'account' => $mainAcc, 'counterAccount' => 'حسابات فرعية متعددة',
                'statement' => $jsStrOr($v['description'] ?? null, 'قبض مجمع'),
                'additionalNote' => $jsStrOr($v['description'] ?? null, 'سند قبض مجمع'),
                'debit' => $valLoc, 'credit' => 0, 'debitForeign' => $valFc, 'creditForeign' => 0,
                'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
            ];
        }
    }

    // 2c. Multi-Payments (`payments`)
    $multiPayments = $loadSourceTable('payments');
    foreach ($multiPayments as &$v) {
        $vNo = (string)($v['voucherNo'] ?? '');
        $vId = (string)($v['id'] ?? '');
        if ($vNo !== '' && strpos($vNo, 'MV-') !== 0 && strpos($vNo, 'MV17') !== 0) {
            $v['id'] = $vNo;
        } elseif ($vId !== '' && strpos($vId, 'MV-') !== 0 && strpos($vId, 'MV17') !== 0) {
            $v['voucherNo'] = $vId;
        }
    }
    unset($v);
    foreach ($multiPayments as $v) {
        $costCenter = $jsStrOr($v['costCenter'] ?? null, 'بدون مركز');
        $vCurrency = $jsStrOr($v['currency'] ?? null, 'SDG');
        $time = $jsStrOr($v['time'] ?? null, $timeNow);
        $isPosted = !isset($v['isPosted']) || $v['isPosted'] !== false;
        $mainAcc = $resolveAccount($jsStrOr($v['fromAccount'] ?? null, $v['mainAccount'] ?? null, $v['withdrawalAccount'] ?? null, 'safe-001'), 'safe-001');
        $vNo = (string)($v['voucherNo'] ?? '');
        $vId = (string)($v['id'] ?? '');
        $refNo = ($vNo !== '' && strpos($vNo, 'MV-') !== 0) ? $vNo : (($vId !== '' && strpos($vId, 'MV-') !== 0) ? $vId : $jsStrOr($vNo, $vId));

        $rate = $parseNum($v['exchangeRate'] ?? 1);
        $effRate = $getEffectiveRate($v, $rate);
        $isBaseSDG = ($vCurrency === 'SDG');

        if (!empty($v['items']) && is_array($v['items'])) {
            foreach ($v['items'] as $item) {
                if (!is_array($item)) continue;
                $pms = (!empty($item['paymentMethods']) && is_array($item['paymentMethods'])) ? $item['paymentMethods'] : [$jsStrOr($item['paymentMethod'] ?? null, 'cash')];
                $itemAcct = $resolveAccount($jsStrOr($item['accountId'] ?? null, $item['account'] ?? null, $item['accountName'] ?? null), 'unknown');
                $itemTotal = $parseNum($item['amount'] ?? 0);

                $cashAmt = $parseNum($item['cashAmount'] ?? 0);
                $bankAmt = $parseNum($item['bankAmount'] ?? 0);
                $chequeAmt = $parseNum($item['chequeAmount'] ?? 0);

                if ($itemTotal > 0 && $cashAmt <= 0 && $bankAmt <= 0 && $chequeAmt <= 0) {
                    if (in_array('bank', $pms, true)) $bankAmt = $itemTotal;
                    elseif (in_array('cheque', $pms, true)) $chequeAmt = $itemTotal;
                    else $cashAmt = $itemTotal;
                }

                if ((in_array('cash', $pms, true) || $cashAmt > 0) && $cashAmt > 0) {
                    $amtLoc = $isBaseSDG ? $cashAmt : $cashAmt * $effRate;
                    $amtFc = $isBaseSDG ? ($cashAmt / $effRate) : $cashAmt;
                    $safeAcct = $mainAcc;
                    $baseStmt = $jsStrOr($item['cashStatement'] ?? null, $item['statement'] ?? null, $v['description'] ?? null, 'صرف نقدية');
                    $stmt = !empty($item['cashSafe']) ? ("خزينة: {$item['cashSafe']} - {$baseStmt}") : $baseStmt;

                    $txns[] = [
                        'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'multi-payment', 'typeLabel' => 'سند صرف مجمع',
                        'account' => $itemAcct, 'counterAccount' => $safeAcct,
                        'statement' => $stmt, 'additionalNote' => $stmt,
                        'debit' => $amtLoc, 'credit' => 0, 'debitForeign' => $amtFc, 'creditForeign' => 0,
                        'costCenter' => $jsStrOr($item['costCenter'] ?? null, $costCenter), 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                    ];
                    $txns[] = [
                        'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'multi-payment', 'typeLabel' => 'سند صرف مجمع',
                        'account' => $safeAcct, 'counterAccount' => $itemAcct,
                        'statement' => $jsStrOr($v['description'] ?? null, $stmt),
                        'additionalNote' => $jsStrOr($v['description'] ?? null, $stmt),
                        'debit' => 0, 'credit' => $amtLoc, 'debitForeign' => 0, 'creditForeign' => $amtFc,
                        'costCenter' => $jsStrOr($item['costCenter'] ?? null, $costCenter), 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                    ];
                }

                if ((in_array('bank', $pms, true) || $bankAmt > 0) && $bankAmt > 0) {
                    $transfers = (!empty($item['bankTransfers']) && is_array($item['bankTransfers'])) ? $item['bankTransfers'] : [[
                        'amount' => $bankAmt,
                        'bankName' => $item['bankName'] ?? '',
                        'transferNo' => $item['transferNo'] ?? '',
                        'statement' => $item['bankStatement'] ?? ''
                    ]];
                    foreach ($transfers as $bt) {
                        $amt = $parseNum($jsStrOr($bt['amount'] ?? null) ?: $bankAmt);
                        if ($amt <= 0) continue;
                        $amtLoc = $isBaseSDG ? $amt : $amt * $effRate;
                        $amtFc = $isBaseSDG ? ($amt / $effRate) : $amt;
                        $bankAcct = $mainAcc;
                        $baseStmt = $jsStrOr($bt['statement'] ?? null, $item['statement'] ?? null, $v['description'] ?? null, 'صرف تحويل بنكي');
                        $stmt = !empty($bt['bankName']) ? ("بنك: {$bt['bankName']} - {$baseStmt}") : $baseStmt;

                        $txns[] = [
                            'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'multi-payment', 'typeLabel' => 'سند صرف مجمع',
                            'account' => $itemAcct, 'counterAccount' => $bankAcct,
                            'statement' => $stmt, 'additionalNote' => $stmt,
                            'transferNo' => $bt['transferNo'] ?? '', 'bankName' => $jsStrOr($bt['bankName'] ?? null, $bt['note'] ?? null),
                            'debit' => $amtLoc, 'credit' => 0, 'debitForeign' => $amtFc, 'creditForeign' => 0,
                            'costCenter' => $jsStrOr($item['costCenter'] ?? null, $costCenter), 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                        ];
                        $txns[] = [
                            'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'multi-payment', 'typeLabel' => 'سند صرف مجمع',
                            'account' => $bankAcct, 'counterAccount' => $itemAcct,
                            'statement' => $jsStrOr($v['description'] ?? null, $stmt),
                            'additionalNote' => $jsStrOr($v['description'] ?? null, $stmt),
                            'transferNo' => $bt['transferNo'] ?? '', 'bankName' => $jsStrOr($bt['bankName'] ?? null, $bt['note'] ?? null),
                            'debit' => 0, 'credit' => $amtLoc, 'debitForeign' => 0, 'creditForeign' => $amtFc,
                            'costCenter' => $jsStrOr($item['costCenter'] ?? null, $costCenter), 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                        ];
                    }
                }

                if ((in_array('cheque', $pms, true) || $chequeAmt > 0) && $chequeAmt > 0) {
                    $cheques = (!empty($item['cheques']) && is_array($item['cheques'])) ? $item['cheques'] : [[
                        'amount' => $chequeAmt,
                        'chequeBank' => $item['chequeBank'] ?? '',
                        'chequeNo' => $jsStrOr($item['chequeNo'] ?? null, $item['checkNo'] ?? null),
                        'chequeDate' => $jsStrOr($item['chequeDate'] ?? null, $item['checkDate'] ?? null),
                        'statement' => $item['chequeStatement'] ?? ''
                    ]];
                    foreach ($cheques as $ch) {
                        $amt = $parseNum($jsStrOr($ch['amount'] ?? null) ?: $chequeAmt);
                        if ($amt <= 0) continue;
                        $amtLoc = $isBaseSDG ? $amt : $amt * $effRate;
                        $amtFc = $isBaseSDG ? ($amt / $effRate) : $amt;
                        $chequeAcct = $mainAcc;
                        $baseStmt = $jsStrOr($ch['statement'] ?? null, $item['statement'] ?? null, $v['description'] ?? null, 'صرف شيك');
                        $stmt = !empty($ch['chequeBank']) ? ("شيك بنك: {$ch['chequeBank']} - {$baseStmt}") : $baseStmt;

                        $txns[] = [
                            'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'multi-payment', 'typeLabel' => 'سند صرف مجمع',
                            'account' => $itemAcct, 'counterAccount' => $chequeAcct,
                            'statement' => $stmt, 'additionalNote' => $stmt,
                            'checkNo' => $jsStrOr($ch['chequeNo'] ?? null, $ch['checkNo'] ?? null), 'bankName' => $jsStrOr($ch['chequeBank'] ?? null, $ch['checkBank'] ?? null),
                            'debit' => $amtLoc, 'credit' => 0, 'debitForeign' => $amtFc, 'creditForeign' => 0,
                            'costCenter' => $jsStrOr($item['costCenter'] ?? null, $costCenter), 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                        ];
                        $txns[] = [
                            'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'multi-payment', 'typeLabel' => 'سند صرف مجمع',
                            'account' => $chequeAcct, 'counterAccount' => $itemAcct,
                            'statement' => $jsStrOr($v['description'] ?? null, $stmt),
                            'additionalNote' => $jsStrOr($v['description'] ?? null, $stmt),
                            'checkNo' => $jsStrOr($ch['chequeNo'] ?? null, $ch['checkNo'] ?? null), 'bankName' => $jsStrOr($ch['chequeBank'] ?? null, $ch['checkBank'] ?? null),
                            'debit' => 0, 'credit' => $amtLoc, 'debitForeign' => 0, 'creditForeign' => $amtFc,
                            'costCenter' => $jsStrOr($item['costCenter'] ?? null, $costCenter), 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                        ];
                    }
                }
            }
        } elseif (!empty($v['details']) && is_array($v['details'])) {
            foreach ($v['details'] as $det) {
                if (!is_array($det) || (empty($det['accountId']) && empty($det['account']))) continue;
                $detAmtLocal = $parseNum($jsStrOr($det['amountLocal'] ?? null, $det['amount'] ?? null));
                $detAmtForeign = $parseNum($det['amountForeign'] ?? 0);
                if ($detAmtLocal <= 0 && $detAmtForeign <= 0) continue;
                $detAcct = $resolveAccount($jsStrOr($det['accountId'] ?? null, $det['account'] ?? null));
                $detRate = $parseNum($jsStrOr($det['rate'] ?? null) ?: $effRate);
                $stmt = $jsStrOr($det['note'] ?? null, $det['statement'] ?? null, $v['statement'] ?? null, $v['description'] ?? null, 'صرف نقدية');
                $txns[] = [
                    'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'payment', 'typeLabel' => 'سند صرف نقدية',
                    'account' => $detAcct, 'counterAccount' => $mainAcc,
                    'statement' => $stmt, 'additionalNote' => $stmt,
                    'debit' => $detAmtLocal, 'credit' => 0, 'debitForeign' => $detAmtForeign, 'creditForeign' => 0,
                    'costCenter' => $jsStrOr($det['costCenter'] ?? null, $costCenter), 'currency' => $vCurrency, 'exchangeRate' => $detRate, 'isPosted' => $isPosted
                ];
                $txns[] = [
                    'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'payment', 'typeLabel' => 'سند صرف نقدية',
                    'account' => $mainAcc, 'counterAccount' => $detAcct,
                    'statement' => $stmt, 'additionalNote' => $stmt,
                    'debit' => 0, 'credit' => $detAmtLocal, 'debitForeign' => 0, 'creditForeign' => $detAmtForeign,
                    'costCenter' => $jsStrOr($det['costCenter'] ?? null, $costCenter), 'currency' => $vCurrency, 'exchangeRate' => $detRate, 'isPosted' => $isPosted
                ];
            }
        } else {
            $totalAmount = $parseNum($v['totalAmount'] ?? 0);
            $valLoc = $isBaseSDG ? $totalAmount : $totalAmount * $effRate;
            $valFc = $isBaseSDG ? ($totalAmount / $effRate) : $totalAmount;
            $txns[] = [
                'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => 'multi-payment', 'typeLabel' => 'سند صرف مجمع',
                'account' => $mainAcc, 'counterAccount' => 'حسابات فرعية متعددة',
                'statement' => $jsStrOr($v['description'] ?? null, 'صرف مجمع'),
                'additionalNote' => $jsStrOr($v['description'] ?? null, 'سند صرف مجمع'),
                'debit' => 0, 'credit' => $valLoc, 'debitForeign' => 0, 'creditForeign' => $valFc,
                'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
            ];
        }
    }

    // 3. Journal Vouchers (`journal_vouchers`)
    $jvs = $loadSourceTable('journal_vouchers');
    foreach ($jvs as $v) {
        if (empty($v['lines']) || !is_array($v['lines'])) continue;
        $time = $jsStrOr($v['time'] ?? null, $timeNow);
        $isPosted = !isset($v['isPosted']) || $v['isPosted'] !== false;
        foreach ($v['lines'] as $line) {
            if (!is_array($line)) continue;
            $costCenter = $jsStrOr($line['costCenter'] ?? null, $v['costCenter'] ?? null, 'بدون مركز');
            $vCurrency = $jsStrOr($line['currency'] ?? null, $v['currency'] ?? null, 'SDG');
            $rate = $parseNum($line['rate'] ?? 1);
            $effRate = $getEffectiveRate($v, $rate);
            $isBaseSDG = ($vCurrency === 'SDG');
            $deb = $parseNum($line['debit'] ?? 0);
            $cred = $parseNum($line['credit'] ?? 0);
            $debLoc = $isBaseSDG ? $deb : $deb * $effRate;
            $debFc = $isBaseSDG ? ($deb / $effRate) : $deb;
            $credLoc = $isBaseSDG ? $cred : $cred * $effRate;
            $credFc = $isBaseSDG ? ($cred / $effRate) : $cred;
            $txns[] = [
                'date' => $v['date'] ?? '', 'time' => $time, 'ref' => $v['id'] ?? '', 'type' => 'jv', 'typeLabel' => 'قيد يومي',
                'account' => $line['account'] ?? '', 'counterAccount' => '-',
                'statement' => 'قيد يومي #' . $jsStrOr($v['voucherNo'] ?? null, $v['id'] ?? null, 'بدون_رقم'),
                'additionalNote' => $jsStrOr($v['statement'] ?? null, $line['note'] ?? null, 'قيد يومي'),
                'debit' => $debLoc, 'credit' => $credLoc, 'debitForeign' => $debFc, 'creditForeign' => $credFc,
                'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
            ];
        }
    }

    // 4. Sales Invoices (`sales_invoices`)
    $sales = $loadSourceTable('sales_invoices');
    foreach ($sales as $inv) {
        if (!empty($inv['isGDN'])) continue;
        $total = $parseNum($jsStrOr($inv['totalAmount'] ?? null, $inv['total'] ?? null, $inv['grandTotal'] ?? null));
        $subtotal = 0.0;
        $itemParts = [];
        if (!empty($inv['items']) && is_array($inv['items'])) {
            foreach ($inv['items'] as $it) {
                $itTot = $parseNum($it['total'] ?? 0);
                $iQty = $parseNum($it['quantity'] ?? $it['qty'] ?? 0);
                $iPrice = $parseNum($it['price'] ?? $it['unitPrice'] ?? 0);
                if ($itTot == 0) $itTot = $iQty * $iPrice;
                $subtotal += $itTot;
                if ($includeInvoiceItems && $iQty > 0) {
                    $iName = trim((string)($it['itemName'] ?? $it['name'] ?? 'صنف'));
                    $itemParts[] = "{$iName} ({$iQty} × " . number_format($iPrice) . ")";
                }
            }
        } else {
            $subtotal = $total - $parseNum($inv['laborCost'] ?? 0) - $parseNum($inv['transportCost'] ?? 0) - $parseNum($inv['additions'] ?? 0) - $parseNum($jsStrOr($inv['taxAmt'] ?? null, $inv['vatAmount'] ?? null)) + $parseNum($jsStrOr($inv['discountAmt'] ?? null, $inv['discountAmount'] ?? null));
        }
        $tax = $parseNum($jsStrOr($inv['taxAmt'] ?? null, $inv['vatAmount'] ?? null));
        $discount = $parseNum($jsStrOr($inv['discountAmt'] ?? null, $inv['discountAmount'] ?? null));
        $additions = $parseNum($inv['additions'] ?? 0) + $parseNum($inv['laborCost'] ?? 0) + $parseNum($inv['transportCost'] ?? 0);
        if ($total == 0 && $subtotal > 0) {
            $total = $subtotal + $additions + $tax - $discount;
        }
        if (!$total) continue;
        $costCenter = $jsStrOr($inv['costCenter'] ?? null, 'بدون مركز');
        $vCurrency = $jsStrOr($inv['currency'] ?? null, 'SDG');
        $time = $jsStrOr($inv['time'] ?? null, $timeNow);
        $refNo = $jsStrOr($inv['invoiceNo'] ?? null, $inv['id'] ?? null);
        $customerAcct = $jsStrOr($inv['customerId'] ?? null, $inv['customer'] ?? null);
        $isPosted = !isset($inv['isPosted']) || $inv['isPosted'] !== false;
        $rate = $parseNum($inv['exchangeRate'] ?? 1);
        $effRate = $getEffectiveRate($inv, $rate);
        $isBaseSDG = ($vCurrency === 'SDG');
        $isReturn = (($inv['type'] ?? '') === 'return' || !empty($inv['isReturn']));

        $totalLoc = $isBaseSDG ? $total : $total * $effRate;
        $totalFc = $isBaseSDG ? ($total / $effRate) : $total;
        $itemsDesc = !empty($itemParts) ? ('الأصناف: ' . implode(' + ', array_slice($itemParts, 0, 6))) : '';

        $txns[] = [
            'date' => $inv['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => $isReturn ? 'sales-return' : 'invoice', 'typeLabel' => $isReturn ? 'مرتجع مبيعات' : 'مبيعات',
            'account' => $customerAcct, 'counterAccount' => 'revenue-sales',
            'statement' => ($isReturn ? 'مرتجع مبيعات #' : 'فاتورة مبيعات #') . $refNo,
            'additionalNote' => ($isReturn ? 'مردودات مبيعات — ' : 'مبيعات آجلة — ') . $jsStrOr($inv['customerName'] ?? null, $inv['customer'] ?? null),
            'itemsDesc' => $itemsDesc,
            'debit' => $isReturn ? 0 : $totalLoc, 'credit' => $isReturn ? $totalLoc : 0,
            'debitForeign' => $isReturn ? 0 : $totalFc, 'creditForeign' => $isReturn ? $totalFc : 0,
            'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
        ];

        $pm = $inv['paymentMethod'] ?? '';
        if ($pm === 'hybrid' || $pm === 'multipay') {
            $cash = $parseNum($inv['cashAmount'] ?? 0);
            if ($cash > 0) {
                $cashLoc = $isBaseSDG ? $cash : $cash * $effRate;
                $cashFc = $isBaseSDG ? ($cash / $effRate) : $cash;
                $paymentAcct = $jsStrOr($inv['paymentAccountId'] ?? null, 'safe-001');
                $debCash = $isReturn ? 0 : $cashLoc;
                $credCash = $isReturn ? $cashLoc : 0;
                $debCashFc = $isReturn ? 0 : $cashFc;
                $credCashFc = $isReturn ? $cashFc : 0;
                $txns[] = [
                    'date' => $inv['date'] ?? '', 'time' => $time, 'ref' => $refNo . '-PAID', 'type' => $isReturn ? 'payment' : 'receipt', 'typeLabel' => $isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                    'account' => $customerAcct, 'counterAccount' => $paymentAcct,
                    'statement' => $isReturn ? 'سداد نقدي لمرتجع' : 'استلام نقدية (نقداً)',
                    'additionalNote' => $isReturn ? 'صرف نقدي للمرتجع' : 'سداد نقدي مع الفاتورة',
                    'debit' => $credCash, 'credit' => $debCash, 'debitForeign' => $credCashFc, 'creditForeign' => $debCashFc,
                    'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                ];
            }
            if (!empty($inv['bankTransfers']) && is_array($inv['bankTransfers'])) {
                foreach ($inv['bankTransfers'] as $idx => $bt) {
                    $amt = $parseNum($bt['amount'] ?? 0);
                    if ($amt > 0) {
                        $amtLocBt = $isBaseSDG ? $amt : $amt * $effRate;
                        $amtFcBt = $isBaseSDG ? ($amt / $effRate) : $amt;
                        $bankAcct = $jsStrOr($bt['bankAccountId'] ?? null, 'bank-001');
                        $debBt = $isReturn ? 0 : $amtLocBt;
                        $credBt = $isReturn ? $amtLocBt : 0;
                        $debBtFc = $isReturn ? 0 : $amtFcBt;
                        $credBtFc = $isReturn ? $amtFcBt : 0;
                        $txns[] = [
                            'date' => $inv['date'] ?? '', 'time' => $time, 'ref' => $refNo . '-BT' . ($idx + 1), 'type' => $isReturn ? 'payment' : 'receipt', 'typeLabel' => $isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                            'account' => $customerAcct, 'counterAccount' => $bankAcct,
                            'statement' => 'تحويل بنكي مرجع #' . ($bt['reference'] ?? ''),
                            'additionalNote' => $isReturn ? 'إرجاع تحويل بنكي' : 'سداد تحويل بنكي مع الفاتورة',
                            'transferNo' => $jsStrOr($bt['reference'] ?? null, $bt['transferNo'] ?? null), 'bankName' => $jsStrOr($bt['bankName'] ?? null, 'البنك'),
                            'debit' => $credBt, 'credit' => $debBt, 'debitForeign' => $credBtFc, 'creditForeign' => $debBtFc,
                            'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                        ];
                    }
                }
            }
            if (!empty($inv['cheques']) && is_array($inv['cheques'])) {
                foreach ($inv['cheques'] as $idx => $ch) {
                    $amt = $parseNum($ch['amount'] ?? 0);
                    if ($amt > 0) {
                        $amtLocCh = $isBaseSDG ? $amt : $amt * $effRate;
                        $amtFcCh = $isBaseSDG ? ($amt / $effRate) : $amt;
                        $debCh = $isReturn ? 0 : $amtLocCh;
                        $credCh = $isReturn ? $amtLocCh : 0;
                        $debChFc = $isReturn ? 0 : $amtFcCh;
                        $credChFc = $isReturn ? $amtFcCh : 0;
                        $txns[] = [
                            'date' => $inv['date'] ?? '', 'time' => $time, 'ref' => $refNo . '-CH' . ($idx + 1), 'type' => $isReturn ? 'payment' : 'receipt', 'typeLabel' => $isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                            'account' => $customerAcct, 'counterAccount' => 'cheques-receivable',
                            'statement' => 'شيك رقم #' . ($ch['chequeNo'] ?? '') . ' استحقاق ' . ($ch['dueDate'] ?? ''),
                            'additionalNote' => $isReturn ? 'إرجاع شيك' : 'سداد شيك مع الفاتورة',
                            'debit' => $credCh, 'credit' => $debCh, 'debitForeign' => $credChFc, 'creditForeign' => $debChFc,
                            'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                        ];
                    }
                }
            }
        } elseif ($pm !== 'credit') {
            $pms = (!empty($inv['paymentMethods']) && is_array($inv['paymentMethods'])) ? $inv['paymentMethods'] : [$pm];
            $cashAmt = $parseNum($inv['cashAmount'] ?? 0);
            $bankAmt = 0.0;
            if (!empty($inv['bankTransfers']) && is_array($inv['bankTransfers'])) {
                foreach ($inv['bankTransfers'] as $b) $bankAmt += $parseNum($b['amount'] ?? 0);
            } else {
                $bankAmt = $parseNum($inv['bankAmount'] ?? 0);
            }
            $chequeAmt = 0.0;
            if (!empty($inv['cheques']) && is_array($inv['cheques'])) {
                foreach ($inv['cheques'] as $c) $chequeAmt += $parseNum($c['amount'] ?? 0);
            } else {
                $chequeAmt = $parseNum($inv['chequeAmount'] ?? 0);
            }
            $totalForFallback = $totalFc;
            if ($totalForFallback > 0 && $cashAmt <= 0 && $bankAmt <= 0 && $chequeAmt <= 0) {
                if (in_array('bank', $pms, true)) $bankAmt = $totalForFallback;
                elseif (in_array('cheque', $pms, true)) $chequeAmt = $totalForFallback;
                else $cashAmt = $totalForFallback;
            }
            if ($cashAmt > 0) {
                $cLoc = $isBaseSDG ? $cashAmt : $cashAmt * $effRate;
                $cFc = $isBaseSDG ? ($cashAmt / $effRate) : $cashAmt;
                $paymentAcct = $jsStrOr($inv['paymentAccountId'] ?? null, 'safe-001');
                $stmt = $isReturn ? 'سداد مرتجع نقدي' : 'استلام نقدية';
                $addNote = $isReturn ? "صرف مبالغ المرتجع" : "سداد نقدي مبيعات ({$refNo})";
                $txns[] = [
                    'date' => $inv['date'] ?? '', 'time' => $time, 'ref' => $refNo . '-SAL-PAID-C', 'type' => $isReturn ? 'payment' : 'receipt', 'typeLabel' => $isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                    'account' => $customerAcct, 'counterAccount' => $paymentAcct,
                    'statement' => $stmt, 'additionalNote' => $addNote,
                    'debit' => $isReturn ? $cLoc : 0, 'credit' => $isReturn ? 0 : $cLoc,
                    'debitForeign' => $isReturn ? $cFc : 0, 'creditForeign' => $isReturn ? 0 : $cFc,
                    'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                ];
            }
            if ($bankAmt > 0 && !empty($inv['bankTransfers']) && is_array($inv['bankTransfers'])) {
                foreach ($inv['bankTransfers'] as $idx => $bt) {
                    $btFc = $parseNum($bt['amount'] ?? 0);
                    if ($btFc <= 0) continue;
                    $btLoc = $isBaseSDG ? $btFc : $btFc * $effRate;
                    $stmt = $isReturn ? 'سداد مرتجع تحويل بنكي' : 'استلام تحويل بنكي';
                    $addNote = "سداد تحويلة مبيعات ({$refNo})";
                    $trNum = $jsStrOr($bt['reference'] ?? null, $bt['transferNo'] ?? null);
                    $txns[] = [
                        'date' => $inv['date'] ?? '', 'time' => $time, 'ref' => $refNo . '-SAL-PAID-B' . $idx, 'type' => $isReturn ? 'payment' : 'receipt', 'typeLabel' => $isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                        'account' => $customerAcct, 'counterAccount' => $jsStrOr($bt['bankAccountId'] ?? null, 'bank-001'),
                        'statement' => $stmt, 'additionalNote' => $addNote, 'transferNo' => $trNum,
                        'debit' => $isReturn ? $btLoc : 0, 'credit' => $isReturn ? 0 : $btLoc,
                        'debitForeign' => $isReturn ? $btFc : 0, 'creditForeign' => $isReturn ? 0 : $btFc,
                        'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                    ];
                }
            }
            if ($chequeAmt > 0 && !empty($inv['cheques']) && is_array($inv['cheques'])) {
                foreach ($inv['cheques'] as $idx => $ch) {
                    $chFc = $parseNum($ch['amount'] ?? 0);
                    if ($chFc <= 0) continue;
                    $chLoc = $isBaseSDG ? $chFc : $chFc * $effRate;
                    $stmt = $isReturn ? 'سداد مرتجع شيك' : 'استلام شيك';
                    $addNote = "سداد شيك مبيعات ({$refNo})";
                    $txns[] = [
                        'date' => $inv['date'] ?? '', 'time' => $time, 'ref' => $refNo . '-SAL-PAID-Q' . $idx, 'type' => $isReturn ? 'payment' : 'receipt', 'typeLabel' => $isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                        'account' => $customerAcct, 'counterAccount' => $jsStrOr($ch['bankAccountId'] ?? null, 'cheques-receivable'),
                        'statement' => $stmt, 'additionalNote' => $addNote,
                        'debit' => $isReturn ? $chLoc : 0, 'credit' => $isReturn ? 0 : $chLoc,
                        'debitForeign' => $isReturn ? $chFc : 0, 'creditForeign' => $isReturn ? 0 : $chFc,
                        'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                    ];
                }
            }
        }
    }

    // 5. Purchase Invoices (`purchase_invoices`)
    $purchases = $loadSourceTable('purchase_invoices');
    foreach ($purchases as $inv) {
        if (!empty($inv['isGRN']) || !empty($inv['isAdjustment'])) continue;
        $total = $parseNum($jsStrOr($inv['totalAmount'] ?? null, $inv['total'] ?? null, $inv['grandTotal'] ?? null));
        $subtotal = 0.0;
        $itemParts = [];
        if (!empty($inv['items']) && is_array($inv['items'])) {
            foreach ($inv['items'] as $it) {
                $itTot = $parseNum($it['total'] ?? 0);
                $iQty = $parseNum($it['quantity'] ?? $it['qty'] ?? 0);
                $iPrice = $parseNum($it['price'] ?? $it['unitPrice'] ?? 0);
                if ($itTot == 0) $itTot = $iQty * $iPrice;
                $subtotal += $itTot;
                if ($includeInvoiceItems && $iQty > 0) {
                    $iName = trim((string)($it['itemName'] ?? $it['name'] ?? 'صنف'));
                    $itemParts[] = "{$iName} ({$iQty} × " . number_format($iPrice) . ")";
                }
            }
        } else {
            $subtotal = $total - $parseNum($inv['laborCost'] ?? 0) - $parseNum($inv['transportCost'] ?? 0) - $parseNum($inv['additions'] ?? 0) - $parseNum($jsStrOr($inv['taxAmt'] ?? null, $inv['vatAmount'] ?? null)) + $parseNum($jsStrOr($inv['discountAmt'] ?? null, $inv['discountAmount'] ?? null));
        }
        $tax = $parseNum($jsStrOr($inv['taxAmt'] ?? null, $inv['vatAmount'] ?? null));
        $discount = $parseNum($jsStrOr($inv['discountAmt'] ?? null, $inv['discountAmount'] ?? null));
        $additions = $parseNum($inv['additions'] ?? 0) + $parseNum($inv['laborCost'] ?? 0) + $parseNum($inv['transportCost'] ?? 0);
        if ($total == 0 && $subtotal > 0) {
            $total = $subtotal + $additions + $tax - $discount;
        }
        if (!$total) continue;
        $costCenter = $jsStrOr($inv['costCenter'] ?? null, 'بدون مركز');
        $vCurrency = $jsStrOr($inv['currency'] ?? null, 'SDG');
        $time = $jsStrOr($inv['time'] ?? null, $timeNow);
        $refNo = $jsStrOr($inv['invoiceNo'] ?? null, $inv['id'] ?? null);
        $supplierAcct = $resolveAccount($jsStrOr($inv['supplierId'] ?? null, $inv['supplier'] ?? null, $inv['customer'] ?? null, 'supp-001'));
        $isPosted = !isset($inv['isPosted']) || $inv['isPosted'] !== false;
        $rate = $parseNum($inv['exchangeRate'] ?? 1);
        $effRate = $getEffectiveRate($inv, $rate);
        $isBaseSDG = ($vCurrency === 'SDG');
        $isReturn = (($inv['type'] ?? '') === 'return' || !empty($inv['isReturn']));

        $totalLoc = $isBaseSDG ? $total : $total * $effRate;
        $totalFc = $isBaseSDG ? ($total / $effRate) : $total;
        $itemsDesc = !empty($itemParts) ? ('الأصناف: ' . implode(' + ', array_slice($itemParts, 0, 6))) : '';

        $txns[] = [
            'date' => $inv['date'] ?? '', 'time' => $time, 'ref' => $refNo, 'type' => $isReturn ? 'purchase-return' : 'purchase', 'typeLabel' => $isReturn ? 'مرتجع مشتريات' : 'مشتريات',
            'account' => $supplierAcct, 'counterAccount' => 'exp-purchases',
            'statement' => ($isReturn ? 'مرتجع مشتريات #' : 'فاتورة مشتريات #') . $refNo,
            'additionalNote' => $isReturn ? "مسترد مورد (مرتجع مشتريات {$refNo})" : "مستحقات مورد (مشتريات {$refNo})",
            'itemsDesc' => $itemsDesc,
            'debit' => $isReturn ? $totalLoc : 0, 'credit' => $isReturn ? 0 : $totalLoc,
            'debitForeign' => $isReturn ? $totalFc : 0, 'creditForeign' => $isReturn ? 0 : $totalFc,
            'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
        ];

        $pm = $inv['paymentMethod'] ?? '';
        if ($pm !== 'credit') {
            $pms = (!empty($inv['paymentMethods']) && is_array($inv['paymentMethods'])) ? $inv['paymentMethods'] : [$pm];
            $cashAmt = $parseNum($inv['cashAmount'] ?? 0);
            $bankAmt = 0.0;
            if (!empty($inv['bankTransfers']) && is_array($inv['bankTransfers'])) {
                foreach ($inv['bankTransfers'] as $b) $bankAmt += $parseNum($b['amount'] ?? 0);
            } else {
                $bankAmt = $parseNum($inv['bankAmount'] ?? 0);
            }
            $chequeAmt = 0.0;
            if (!empty($inv['cheques']) && is_array($inv['cheques'])) {
                foreach ($inv['cheques'] as $c) $chequeAmt += $parseNum($c['amount'] ?? 0);
            } else {
                $chequeAmt = $parseNum($inv['chequeAmount'] ?? 0);
            }
            $totalForFallback = $totalFc;
            if ($totalForFallback > 0 && $cashAmt <= 0 && $bankAmt <= 0 && $chequeAmt <= 0) {
                if (in_array('bank', $pms, true)) $bankAmt = $totalForFallback;
                elseif (in_array('cheque', $pms, true)) $chequeAmt = $totalForFallback;
                else $cashAmt = $totalForFallback;
            }
            if ($cashAmt > 0) {
                $cLoc = $isBaseSDG ? $cashAmt : $cashAmt * $effRate;
                $cFc = $isBaseSDG ? ($cashAmt / $effRate) : $cashAmt;
                $paymentAcct = $jsStrOr($inv['paymentAccountId'] ?? null, 'safe-001');
                $stmt = $isReturn ? 'استرداد نقدية' : 'صرف نقدية';
                $addNote = $isReturn ? "استرداد نقدي مع مرتجع الفاتورة" : "سداد نقدي مشتريات ({$refNo})";
                $txns[] = [
                    'date' => $inv['date'] ?? '', 'time' => $time, 'ref' => $refNo . '-PUR-PAID-C', 'type' => $isReturn ? 'receipt' : 'payment', 'typeLabel' => $isReturn ? 'استرداد مشتريات' : 'سداد مشتريات',
                    'account' => $supplierAcct, 'counterAccount' => $paymentAcct,
                    'statement' => $stmt, 'additionalNote' => $addNote,
                    'debit' => $isReturn ? 0 : $cLoc, 'credit' => $isReturn ? $cLoc : 0,
                    'debitForeign' => $isReturn ? 0 : $cFc, 'creditForeign' => $isReturn ? $cFc : 0,
                    'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                ];
            }
            if ($bankAmt > 0 && !empty($inv['bankTransfers']) && is_array($inv['bankTransfers'])) {
                foreach ($inv['bankTransfers'] as $idx => $bt) {
                    $btFc = $parseNum($bt['amount'] ?? 0);
                    if ($btFc <= 0) continue;
                    $btLoc = $isBaseSDG ? $btFc : $btFc * $effRate;
                    $stmt = $isReturn ? 'استرداد تحويل بنكي' : 'صرف تحويل بنكي';
                    $addNote = "سداد تحويلة مشتريات ({$refNo})";
                    $trNum = $jsStrOr($bt['reference'] ?? null, $bt['transferNo'] ?? null);
                    $txns[] = [
                        'date' => $inv['date'] ?? '', 'time' => $time, 'ref' => $refNo . '-PUR-PAID-B' . $idx, 'type' => $isReturn ? 'receipt' : 'payment', 'typeLabel' => $isReturn ? 'استرداد مشتريات' : 'سداد مشتريات',
                        'account' => $supplierAcct, 'counterAccount' => $jsStrOr($bt['bankAccountId'] ?? null, 'bank-001'),
                        'statement' => $stmt, 'additionalNote' => $addNote, 'transferNo' => $trNum,
                        'debit' => $isReturn ? 0 : $btLoc, 'credit' => $isReturn ? $btLoc : 0,
                        'debitForeign' => $isReturn ? 0 : $btFc, 'creditForeign' => $isReturn ? $btFc : 0,
                        'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                    ];
                }
            }
            if ($chequeAmt > 0 && !empty($inv['cheques']) && is_array($inv['cheques'])) {
                foreach ($inv['cheques'] as $idx => $ch) {
                    $chFc = $parseNum($ch['amount'] ?? 0);
                    if ($chFc <= 0) continue;
                    $chLoc = $isBaseSDG ? $chFc : $chFc * $effRate;
                    $stmt = $isReturn ? 'استرداد شيك' : 'صرف شيك';
                    $addNote = "سداد شيك مشتريات ({$refNo})";
                    $txns[] = [
                        'date' => $inv['date'] ?? '', 'time' => $time, 'ref' => $refNo . '-PUR-PAID-Q' . $idx, 'type' => $isReturn ? 'receipt' : 'payment', 'typeLabel' => $isReturn ? 'استرداد مشتريات' : 'سداد مشتريات',
                        'account' => $supplierAcct, 'counterAccount' => $jsStrOr($ch['bankAccountId'] ?? null, 'cheques-payable'),
                        'statement' => $stmt, 'additionalNote' => $addNote,
                        'debit' => $isReturn ? 0 : $chLoc, 'credit' => $isReturn ? $chLoc : 0,
                        'debitForeign' => $isReturn ? 0 : $chFc, 'creditForeign' => $isReturn ? $chFc : 0,
                        'costCenter' => $costCenter, 'currency' => $vCurrency, 'exchangeRate' => $effRate, 'isPosted' => $isPosted
                    ];
                }
            }
        }
    }

    // Recalibrate true exchange rate if both debit/credit exist in local and foreign (`js/app.js` lines 7030-7042)
    foreach ($txns as &$t) {
        $d = (float)($t['debit'] ?? 0);
        $c = (float)($t['credit'] ?? 0);
        $df = (float)($t['debitForeign'] ?? 0);
        $cf = (float)($t['creditForeign'] ?? 0);
        $loc = $d > 0 ? $d : $c;
        $forgn = $df > 0 ? $df : $cf;
        if ($loc > 0 && $forgn > 0) {
            $t['exchangeRate'] = $loc / $forgn;
        }
    }
    unset($t);

    // Filter by account (`js/general-ledger.js` lines 1520-1539)
    $selectedAccName = $getAccountName($accVal);
    $filtered = [];
    $seqIdx = 0;
    foreach ($txns as $t) {
        $tAcc = trim((string)($t['account'] ?? ''));
        $cleanTAcc = preg_replace('/^(cust|supp|bank|safe|exp)-/i', '', $tAcc);
        $matched = false;
        if ($tAcc === $accVal || $tAcc === $cleanId || $cleanTAcc === $cleanId) {
            $matched = true;
        } elseif ($selectedAccName !== '' && $selectedAccName !== $accVal) {
            $tAccName = $getAccountName($tAcc);
            if (($tAccName !== '' && $tAccName === $selectedAccName) || $tAcc === $selectedAccName) {
                $matched = true;
            }
        }
        if ($matched) {
            $t['_seq'] = $seqIdx++;
            $filtered[] = $t;
        }
    }

    // Parse 24h time for chronological sort (`js/general-ledger.js` lines 1605-1616)
    $parseTimeTo24h = function($timeStr) {
        if (!$timeStr) return '00:00:00';
        $timeStr = trim((string)$timeStr);
        $isPM = (bool)preg_match('/pm|مساء/iu', $timeStr);
        $isAM = (bool)preg_match('/am|صباحا|صباحاً/iu', $timeStr);
        $clean = preg_replace('/[^0-9:]/', '', $timeStr);
        $parts = explode(':', $clean);
        $h = isset($parts[0]) ? (int)$parts[0] : 0;
        $m = isset($parts[1]) ? (int)$parts[1] : 0;
        $s = isset($parts[2]) ? (int)$parts[2] : 0;
        if ($isPM && $h < 12) $h += 12;
        if ($isAM && $h === 12) $h = 0;
        return str_pad((string)$h, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string)$m, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string)$s, 2, '0', STR_PAD_LEFT);
    };

    // Sort movements chronologically matching `js/general-ledger.js` (lines 1744-1752)
    usort($filtered, function($a, $b) use ($parseTimeTo24h) {
        $da = ($a['date'] ?? '') . ' ' . $parseTimeTo24h($a['time'] ?? '');
        $db = ($b['date'] ?? '') . ' ' . $parseTimeTo24h($b['time'] ?? '');
        $cmp = strcmp($da, $db);
        if ($cmp === 0) {
            $cmp = strnatcasecmp((string)($a['ref'] ?? ''), (string)($b['ref'] ?? ''));
        }
        if ($cmp === 0) {
            $cmp = ($a['_seq'] ?? 0) <=> ($b['_seq'] ?? 0);
        }
        return $cmp;
    });

    // Opening balance calculation (matching `js/general-ledger.js` lines 1698-1740)
    $opDebit = $parseNum($entityObj['openingDebit'] ?? $entityObj['debit'] ?? 0);
    $opCredit = $parseNum($entityObj['openingCredit'] ?? $entityObj['credit'] ?? 0);
    $rawOpBal = $parseNum($entityObj['openingBalance'] ?? $entityObj['opening_balance'] ?? $entityObj['balance'] ?? 0);
    $intrinsicBal = 0.0;
    if ($opDebit > 0 || $opCredit > 0) {
        $intrinsicBal = $opDebit - $opCredit;
    } elseif ($rawOpBal != 0) {
        $bType = strtolower((string)($entityObj['balance_type'] ?? $entityObj['balanceType'] ?? 'debit'));
        if ($bType === 'credit') {
            $intrinsicBal = -abs($rawOpBal);
        } else {
            $intrinsicBal = abs($rawOpBal);
        }
    }

    $savedAccRate = $parseNum($entityObj['exchangeRate'] ?? $entityObj['exRate'] ?? $entityObj['rate'] ?? 0);
    $entityExRate = ($entityCurrency === 'SDG') ? ($systemUsdRate > 1 ? $systemUsdRate : 1.0) : ($savedAccRate > 1 ? $savedAccRate : ($systemUsdRate > 1 ? $systemUsdRate : 1.0));

    $openingDebit = $intrinsicBal > 0 ? $intrinsicBal : 0.0;
    $openingCredit = $intrinsicBal < 0 ? abs($intrinsicBal) : 0.0;
    $openingDebitUsd = $entityExRate > 1 ? ($openingDebit / $entityExRate) : 0.0;
    $openingCreditUsd = $entityExRate > 1 ? ($openingCredit / $entityExRate) : 0.0;
    $intrinsicBalFc = $entityExRate > 1 ? ($intrinsicBal / $entityExRate) : 0.0;

    $runningNetDebit = $intrinsicBal;
    $runningNetDebitUsd = $intrinsicBalFc;
    $totalDebit = $openingDebit;
    $totalCredit = $openingCredit;
    $totalDebitUsd = $openingDebitUsd;
    $totalCreditUsd = $openingCreditUsd;

    $rows = [];
    foreach ($filtered as $t) {
        $debLoc = $parseNum($t['debit'] ?? 0);
        $credLoc = $parseNum($t['credit'] ?? 0);
        $rate = $parseNum($t['exchangeRate'] ?? $entityExRate ?: 1);
        if ($rate <= 0) $rate = $entityExRate ?: 1.0;

        $df = $parseNum($t['debitForeign'] ?? 0);
        $cf = $parseNum($t['creditForeign'] ?? 0);
        if ($debLoc > 0 && ($df == 0 || $df == $debLoc) && $rate > 0) {
            $df = $debLoc / $rate;
        }
        if ($credLoc > 0 && ($cf == 0 || $cf == $credLoc) && $rate > 0) {
            $cf = $credLoc / $rate;
        }

        $runningNetDebit += ($debLoc - $credLoc);
        $runningNetDebitUsd += ($df - $cf);
        $rowBalFc = $runningNetDebitUsd;
        if ($runningNetDebit < 0) {
            $rowBalFc = -abs($rowBalFc);
        } elseif ($runningNetDebit > 0) {
            $rowBalFc = abs($rowBalFc);
        }

        $totalDebit += $debLoc;
        $totalCredit += $credLoc;
        $totalDebitUsd += $df;
        $totalCreditUsd += $cf;

        $counterAccName = $getAccountName($t['counterAccount'] ?? '-');
        $extraParts = [];
        if ($counterAccName !== '' && $counterAccName !== '-') {
            $extraParts[] = "الحساب المقابل: {$counterAccName}";
        }
        if (!empty($t['bankName'])) {
            $extraParts[] = "البنك: {$t['bankName']}";
        }
        if (!empty($t['transferNo'])) {
            $extraParts[] = "رقم العملية: {$t['transferNo']}";
        }
        if (!empty($t['checkNo'])) {
            $extraParts[] = "رقم الشيك: {$t['checkNo']}";
        }
        if (!empty($t['itemsDesc'])) {
            $extraParts[] = $t['itemsDesc'];
        }

        $rows[] = [
            'date' => trim((string)($t['date'] ?? '')) ?: date('Y-m-d'),
            'time' => trim((string)($t['time'] ?? '')) ?: '00:00',
            'ref' => trim((string)($t['ref'] ?? '')) ?: '-',
            'type_label' => $t['typeLabel'] ?? '',
            'statement' => trim((string)($t['statement'] ?? '')),
            'extra_info' => implode(' | ', $extraParts),
            'debit' => $debLoc,
            'credit' => $credLoc,
            'debit_usd' => $df,
            'credit_usd' => $cf,
            'rate' => $rate,
            'running_net_debit' => $runningNetDebit,
            'running_net_debit_usd' => $rowBalFc
        ];
    }

    // Final balance alignment matching `js/general-ledger.js` lines 1857-1869
    $runningNetDebit = $totalDebit - $totalCredit;
    $runningNetDebitUsd = $totalDebitUsd - $totalCreditUsd;
    $equivalentUsdCurrent = ($systemUsdRate > 0) ? round(abs($runningNetDebit) / $systemUsdRate, 2) : abs($runningNetDebitUsd);
    if ($runningNetDebit < -0.009) {
        $runningNetDebitUsd = -abs($runningNetDebitUsd);
    } elseif ($runningNetDebit > 0.009) {
        $runningNetDebitUsd = abs($runningNetDebitUsd);
    } else {
        $runningNetDebitUsd = 0.0;
    }

    $currencyLabelAr = ($currency === 'SDG') ? 'ج.س' : $currency;

    $formatBalanceTag = function($netDebitVal, $netUsdVal = null) use ($currencyLabelAr, $systemUsdRate) {
        $absVal = number_format(abs($netDebitVal), 0);
        $usdVal = ($netUsdVal !== null) ? abs((float)$netUsdVal) : (($systemUsdRate > 0) ? abs($netDebitVal) / $systemUsdRate : 0.0);
        $usdFormatted = '$' . number_format($usdVal, 1) . ' USD';
        if ($netDebitVal > 0.009) {
            return "🔶 *{$absVal} {$currencyLabelAr}  〘 `{$usdFormatted}` 〙 (مدين عليه)*";
        } elseif ($netDebitVal < -0.009) {
            return "🔷 *{$absVal} {$currencyLabelAr}  〘 `{$usdFormatted}` 〙 (دائن له)*";
        }
        return "⚪ *0 {$currencyLabelAr}  〘 `$0.0 USD` 〙 (مسدد / متوازن)*";
    };

    $roleTitle = $isCustomer ? 'العميل' : 'المورد';
    $nowFormatted = date('Y-m-d H:i');
    $issueDate = date('Y-m-d');
    $issueTime = date('H:i');
    $lastMovementDate = !empty($rows) ? $rows[count($rows) - 1]['date'] : 'لا توجد حركات';
    $voucherSerial = 'MGS-' . date('Ym') . '-' . str_pad((string)($codeNum ?: substr(preg_replace('/\D/', '', $rawId), -3) ?: '100'), 3, '0', STR_PAD_LEFT) . '-' . substr((string)abs(crc32($entityName . date('YmdHi'))), 0, 4);

    // Resolve Company Name & Electronic Stamp from Master Data policy -> settings -> tenants
    $pol = isset($options['policy']) && is_array($options['policy']) ? $options['policy'] : loadWhatsAppStatementPolicy($pdo, $tenantId);
    $companyName = trim((string)($pol['voucherCompanyName'] ?? ''));
    if ($companyName === '') {
        try {
            $stmtSet = $pdo->prepare("SELECT data FROM settings WHERE (tenant_id = ? OR tenant_id IS NULL) ORDER BY id DESC LIMIT 1");
            $stmtSet->execute([$tenantId]);
            $rawSet = $stmtSet->fetchColumn();
            if ($rawSet) {
                $setDec = json_decode((string)$rawSet, true);
                if (is_array($setDec)) {
                    $companyName = trim((string)($setDec['companyName'] ?? ($setDec['company_name'] ?? '')));
                }
            }
            if ($companyName === '' && !empty($tenantId)) {
                $stmtTen = $pdo->prepare("SELECT company_name FROM tenants WHERE id = ? LIMIT 1");
                $stmtTen->execute([$tenantId]);
                $tenName = $stmtTen->fetchColumn();
                if ($tenName) {
                    $companyName = trim((string)$tenName);
                }
            }
        } catch (Exception $e) {}
    }
    $stampText = trim((string)($pol['voucherStampText'] ?? '')) ?: 'ختم مصادقة مالية إلكترونية';
    $stampImage = trim((string)($pol['voucherStampImage'] ?? ''));

    $balanceStatusType = 'settled'; // 'debit' | 'credit' | 'settled'
    $balanceStatusAr = 'مسدد بالكامل';
    $balanceStatusEn = 'SETTLED / BALANCED';
    if ($runningNetDebit > 0.009) {
        $balanceStatusType = 'debit';
        $balanceStatusAr = 'مدين عليه';
        $balanceStatusEn = 'DEBIT BALANCE (RECEIVABLE)';
    } elseif ($runningNetDebit < -0.009) {
        $balanceStatusType = 'credit';
        $balanceStatusAr = 'دائن له';
        $balanceStatusEn = 'CREDIT BALANCE (PAYABLE)';
    }

    $tafqeetLocal = formatArabicTafqeetForVoucher(abs($runningNetDebit), ($currency === 'SDG') ? 'جنيه سوداني' : $currency);
    $tafqeetUsd = formatArabicTafqeetForVoucher(abs($runningNetDebitUsd), 'دولار أمريكي');
    $pinDisplay = $statementPin !== '' ? "🔐 •••• (محمي)" : ($codeNum > 0 ? "#{$codeNum}" : 'معتمد');

    $voucherData = [
        'voucher_serial' => $voucherSerial,
        'issue_datetime' => $nowFormatted,
        'issue_date' => $issueDate,
        'issue_time' => $issueTime,
        'company_name' => $companyName,
        'stamp_text' => $stampText,
        'stamp_image' => $stampImage,
        'entity_type' => $entityType,
        'role_title' => $roleTitle,
        'entity_name' => $entityName,
        'code_num' => $codeNum,
        'code_word' => $codeWord,
        'currency' => $currency,
        'currency_label_ar' => $currencyLabelAr,
        'currency_full_ar' => ($currency === 'SDG') ? 'جنيه سوداني' : $currency,
        'exchange_rate' => $systemUsdRate,
        'exchange_rate_formatted' => number_format($systemUsdRate, 0),
        'total_debit_local' => round($totalDebit, 0),
        'total_debit_local_fmt' => number_format($totalDebit, 0),
        'total_credit_local' => round($totalCredit, 0),
        'total_credit_local_fmt' => number_format($totalCredit, 0),
        'net_balance_local' => round(abs($runningNetDebit), 0),
        'net_balance_local_fmt' => number_format(abs($runningNetDebit), 0),
        'total_debit_usd' => round($totalDebitUsd, 1),
        'total_debit_usd_fmt' => number_format($totalDebitUsd, 1),
        'total_credit_usd' => round($totalCreditUsd, 1),
        'total_credit_usd_fmt' => number_format($totalCreditUsd, 1),
        'net_balance_usd' => round(abs($runningNetDebitUsd), 1),
        'net_balance_usd_fmt' => number_format(abs($runningNetDebitUsd), 1),
        'equivalent_usd_current_fmt' => number_format($equivalentUsdCurrent, 1),
        'status_type' => $balanceStatusType,
        'status_label_ar' => $balanceStatusAr,
        'status_label_en' => $balanceStatusEn,
        'tafqeet_ar' => $tafqeetLocal,
        'tafqeet_usd_ar' => $tafqeetUsd,
        'last_movement_date' => $lastMovementDate,
        'movements_count' => count($rows),
        'render_mode' => $renderMode
    ];

    // =========================================================================
    // MODE A: BALANCE ONLY (`معرفة الرصيد فقط` — سند رصيد مزدوج العملة)
    // =========================================================================
    if ($renderMode === 'balance_only') {
        $statusBadgeEmoji = ($balanceStatusType === 'debit') ? '🔶' : (($balanceStatusType === 'credit') ? '🔷' : '✅');
        $lines = [];
        $lines[] = "🏛️ *مـنـظـومـة مـقـايـيـس الـمـحـاسـبـيـة الـعـالـمـيـة*";
        $lines[] = "══════════";
        $lines[] = "   📜 *سَـنَـد رَصِـيـد*";
        $lines[] = $nowFormatted;
        $lines[] = "الصرف: " . round($systemUsdRate) . "  ج س ";
        $lines[] = " *الاسم:* *{$entityName}*";
        $lines[] = "";
        $lines[] = "🟡 *الجنيه السوداني:* *" . number_format(abs($runningNetDebit), 0) . " {$currencyLabelAr}*";
        $lines[] = "💵 *المعادل بالدولار:* 〘 `$" . number_format(abs($runningNetDebitUsd), 1) . " USD` 〙";
        $lines[] = "{$statusBadgeEmoji} *الرصيد النهائي:* " . $formatBalanceTag($runningNetDebit, $runningNetDebitUsd);
        $lines[] = "──────────────────────────";
        $lines[] = "🔴 *إجمالي المدين:* 🟡 *" . number_format($totalDebit, 0) . " {$currencyLabelAr}* 〘 💵 `$" . number_format($totalDebitUsd, 1) . "` 〙";
        $lines[] = "🟢 *إجمالي الدائن:* 🟡 *" . number_format($totalCredit, 0) . " {$currencyLabelAr}* 〘 💵 `$" . number_format($totalCreditUsd, 1) . "` 〙";
        $lines[] = "✍️ *التفقيط:* _{$tafqeetLocal}_";
        $finalText = implode("\n", $lines);
        return $returnStructured ? ['text' => $finalText, 'voucher_data' => $voucherData] : $finalText;
    }

    // =========================================================================
    // MODE B: DETAILED STATEMENT (`كشف حساب` بالصيغة المختصرة وتمايز الألوان)
    // =========================================================================
    $displayRows = $rows;
    $broughtForwardNet = $openingDebit - $openingCredit;
    $broughtForwardNetUsd = $openingDebitUsd - $openingCreditUsd;
    $hasBroughtForward = false;
    $scopeLabel = 'كامل الفترة';

    if ($statementScope === 'last_30_days') {
        $cutoffDate = date('Y-m-d', strtotime('-30 days'));
        $filtered = [];
        foreach ($rows as $r) {
            if ($r['date'] >= $cutoffDate) {
                $filtered[] = $r;
            } else {
                $broughtForwardNet = $r['running_net_debit'];
                $broughtForwardNetUsd = $r['running_net_debit_usd'];
                $hasBroughtForward = true;
            }
        }
        $displayRows = $filtered;
        $scopeLabel = 'آخر 30 يوماً (منذ ' . $cutoffDate . ')';
    } elseif ($statementScope === 'last_20' && count($rows) > 20) {
        $cutIndex = count($rows) - 20;
        $broughtForwardNet = $rows[$cutIndex - 1]['running_net_debit'];
        $broughtForwardNetUsd = $rows[$cutIndex - 1]['running_net_debit_usd'];
        $displayRows = array_slice($rows, $cutIndex);
        $hasBroughtForward = true;
        $scopeLabel = 'آخر 20 حركة مالية';
    } elseif ($statementScope === 'last_50' && count($rows) > 50) {
        $cutIndex = count($rows) - 50;
        $broughtForwardNet = $rows[$cutIndex - 1]['running_net_debit'];
        $broughtForwardNetUsd = $rows[$cutIndex - 1]['running_net_debit_usd'];
        $displayRows = array_slice($rows, $cutIndex);
        $hasBroughtForward = true;
        $scopeLabel = 'آخر 50 حركة مالية';
    } elseif ($statementScope === 'current_month') {
        $currentYm = date('Y-m');
        $filtered = [];
        foreach ($rows as $r) {
            if (strpos($r['date'], $currentYm) === 0) {
                $filtered[] = $r;
            } else {
                $broughtForwardNet = $r['running_net_debit'];
                $broughtForwardNetUsd = $r['running_net_debit_usd'];
                $hasBroughtForward = true;
            }
        }
        $displayRows = $filtered;
        $scopeLabel = 'حركات الشهر الحالي (' . $currentYm . ')';
    }

    $lines = [];
    $lines[] = "🏛️ *مـنـظـومـة مـقـايـيـس الـمـحـاسـبـيـة الـعـالـمـيـة*";
    $lines[] = "══════════";
    $lines[] = "   📊 *كَـشْـف حِـسَـاب*";
    $lines[] = $nowFormatted;
    $lines[] = " {$scopeLabel}";
    $lines[] = "الصرف: " . round($systemUsdRate) . "  ج س ";
    $lines[] = " *الاسم:* *{$entityName}*";
    $lines[] = "  ";

    if ($hasBroughtForward) {
        $lines[] = "🔄 *رصيد سابق مرحّل:* " . $formatBalanceTag($broughtForwardNet, $broughtForwardNetUsd);
    } elseif ($openingDebit > 0 || $openingCredit > 0) {
        $lines[] = "🏁 *الرصيد الافتتاحي:* " . $formatBalanceTag($openingDebit - $openingCredit, $openingDebitUsd - $openingCreditUsd);
    } else {
        $lines[] = "🏁 *الرصيد الافتتاحي:* 🟡 0 {$currencyLabelAr} 〘 💵 `$0.0 USD` 〙";
    }

    $hasMultiPeriodRateDiff = false;
    if (empty($displayRows)) {
        $lines[] = "ℹ️ لا توجد حركات مالية مسجلة ضمن هذا النطاق.";
    } else {
        foreach ($displayRows as $idx => $r) {
            $num = $idx + 1;
            $movTypeIcon = $r['debit'] > 0 ? '🔴' : '🟢';
            $txRate = (float)($r['rate'] ?? $systemUsdRate);
            $rateDiffers = ($txRate > 1 && $systemUsdRate > 1 && abs($txRate - $systemUsdRate) >= 1.0);
            if ($rateDiffers) {
                $hasMultiPeriodRateDiff = true;
            }
            $rateNoteSuffix = $rateDiffers ? (" | الصرف: " . round($txRate) . " ج.س") : "";

            $amtPart = $r['debit'] > 0
                ? ("مدين (عليه): 🟡 *" . number_format($r['debit'], 0) . " {$currencyLabelAr}* 〘 💵 `$" . number_format($r['debit_usd'], 1) . "`{$rateNoteSuffix} 〙")
                : ("دائن (له): 🟡 *" . number_format($r['credit'], 0) . " {$currencyLabelAr}* 〘 💵 `$" . number_format($r['credit_usd'], 1) . "`{$rateNoteSuffix} 〙");

            $rowBlock = "{$movTypeIcon} *({$num}) {$r['date']} |*\n";
            $rowBlock .= "   • {$amtPart}\n";
            if (!empty($r['statement'])) {
                $rowBlock .= "   • البيان: {$r['statement']}\n";
            }
            if (!empty($r['extra_info'])) {
                $rowBlock .= "   • التفاصيل: {$r['extra_info']}\n";
            }
            $rowBlock .= "   • الرصيد التراكمي: " . $formatBalanceTag($r['running_net_debit'], $r['running_net_debit_usd']);
            $lines[] = $rowBlock;
        }
    }

    $lines[] = "══════════";
    $lines[] = "📌 *الـخـلاصـة الـنـهـائـيـة:*";
    $lines[] = "• 🔴 إجمالي المدين: 🟡 *" . number_format($totalDebit, 0) . " {$currencyLabelAr}* 〘 💵 `$" . number_format($totalDebitUsd, 1) . " USD` 〙";
    $lines[] = "• 🟢 إجمالي الدائن: 🟡 *" . number_format($totalCredit, 0) . " {$currencyLabelAr}* 〘 💵 `$" . number_format($totalCreditUsd, 1) . " USD` 〙";
    $lines[] = "• الرصيد الختامي: " . $formatBalanceTag($runningNetDebit, $runningNetDebitUsd);
    if ($hasMultiPeriodRateDiff && abs(abs($runningNetDebitUsd) - $equivalentUsdCurrent) >= 0.2) {
        $fxDiffUsd = round(abs($runningNetDebitUsd) - $equivalentUsdCurrent, 1);
        $lines[] = "• ⚖️ المعادل بسعر الصرف الحالي (" . round($systemUsdRate) . "): 〘 💵 `$" . number_format($equivalentUsdCurrent, 1) . " USD` 〙 (فرق تغير الصرف: `$" . number_format(abs($fxDiffUsd), 1) . "`)";
    }
    $lines[] = "✍️ *التفقيط:* _{$tafqeetLocal}_";

    $finalText = implode("\n", $lines);
    return $returnStructured ? ['text' => $finalText, 'voucher_data' => $voucherData] : $finalText;
}

// -------------------------------------------------------------
// Helper: Convert numeric amount into formal Arabic financial words (التفقيط المالي للسندات)
// -------------------------------------------------------------
function formatArabicTafqeetForVoucher($amount, $unitName = 'جنيه سوداني') {
    $num = (int)round(abs((float)$amount));
    if ($num <= 0) {
        return "صفر {$unitName} — الحساب مسدد ومتوازن بالكامل";
    }

    $ones = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة', 'عشرة', 'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر'];
    $tens = ['', '', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
    $hundreds = ['', 'مائة', 'مائتان', 'ثلاثمائة', 'أربعمائة', 'خمسمائة', 'ستمائة', 'سبعمائة', 'ثمانمائة', 'تسعمائة'];

    $convertTriplet = function($n) use ($ones, $tens, $hundreds) {
        $n = (int)$n % 1000;
        if ($n <= 0) return '';
        $parts = [];
        $h = intdiv($n, 100);
        $rem = $n % 100;
        if ($h > 0 && isset($hundreds[$h])) $parts[] = $hundreds[$h];
        if ($rem > 0) {
            if ($rem < 20) {
                $parts[] = $ones[$rem];
            } else {
                $t = intdiv($rem, 10);
                $o = $rem % 10;
                if ($o > 0) {
                    $parts[] = $ones[$o] . ' و' . $tens[$t];
                } else {
                    $parts[] = $tens[$t];
                }
            }
        }
        return implode(' و', $parts);
    };

    $trillions = intdiv($num, 1000000000000);
    $billions = intdiv($num % 1000000000000, 1000000000);
    $millions = intdiv($num % 1000000000, 1000000);
    $thousands = intdiv($num % 1000000, 1000);
    $remainder = $num % 1000;

    $sections = [];
    if ($trillions > 0) {
        if ($trillions == 1) $sections[] = 'تريليون';
        elseif ($trillions == 2) $sections[] = 'تريليونان';
        elseif ($trillions >= 3 && $trillions <= 10) $sections[] = $convertTriplet($trillions) . ' تريليونات';
        else $sections[] = $convertTriplet($trillions) . ' تريليوناً';
    }
    if ($billions > 0) {
        if ($billions == 1) $sections[] = 'مليار';
        elseif ($billions == 2) $sections[] = 'ملياران';
        elseif ($billions >= 3 && $billions <= 10) $sections[] = $convertTriplet($billions) . ' مليارات';
        else $sections[] = $convertTriplet($billions) . ' ملياراً';
    }
    if ($millions > 0) {
        if ($millions == 1) $sections[] = 'مليون';
        elseif ($millions == 2) $sections[] = 'مليونان';
        elseif ($millions >= 3 && $millions <= 10) $sections[] = $convertTriplet($millions) . ' ملايين';
        else $sections[] = $convertTriplet($millions) . ' مليوناً';
    }
    if ($thousands > 0) {
        if ($thousands == 1) $sections[] = 'ألف';
        elseif ($thousands == 2) $sections[] = 'ألفان';
        elseif ($thousands >= 3 && $thousands <= 10) $sections[] = $convertTriplet($thousands) . ' آلاف';
        else $sections[] = $convertTriplet($thousands) . ' ألفاً';
    }
    if ($remainder > 0) {
        $sections[] = $convertTriplet($remainder);
    }

    return 'فقط ' . implode(' و', $sections) . ' ' . $unitName . ' لا غير';
}



