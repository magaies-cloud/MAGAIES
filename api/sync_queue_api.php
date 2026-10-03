<?php
/**
 * ═════════════════════════════════════════════════════════════════════
 * 🛰️ SYNC QUEUE API ENDPOINT (FAST BIDIRECTIONAL SYNC ENGINE)
 * ═════════════════════════════════════════════════════════════════════
 * Handles batch synchronization of the `sync_queue` table:
 * Table Schema:
 *   - id: TEXT PRIMARY KEY (UUID)
 *   - entity_name: TEXT ('invoices', 'vouchers', 'stock_transfers')
 *   - record_id: TEXT
 *   - operation: TEXT ('INSERT', 'UPDATE', 'DELETE')
 *   - payload: TEXT (JSON)
 *   - status: TEXT ('PENDING', 'SYNCING', 'FAILED', 'SYNCED')
 *   - retry_count: INTEGER DEFAULT 0
 *   - created_at: TIMESTAMP
 * ═════════════════════════════════════════════════════════════════════
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Idempotency-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../db_bridge.php';

function ensureSyncInfrastructure($pdo) {
    static $initialized = false;
    if ($initialized) return;

    try {
        // 1. Ensure sync_queue table exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS `sync_queue` (
          `id` varchar(64) NOT NULL,
          `entity_name` varchar(64) NOT NULL COMMENT 'invoices, vouchers, stock_transfers',
          `record_id` varchar(64) DEFAULT NULL,
          `operation` varchar(16) NOT NULL DEFAULT 'INSERT' COMMENT 'INSERT, UPDATE, DELETE',
          `payload` longtext NOT NULL COMMENT 'JSON payload',
          `status` varchar(32) NOT NULL DEFAULT 'PENDING' COMMENT 'PENDING, SYNCING, FAILED, SYNCED',
          `retry_count` int(11) NOT NULL DEFAULT 0,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `synced_at` timestamp NULL DEFAULT NULL,
          `error_message` text DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_entity_status` (`entity_name`,`status`),
          KEY `idx_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // 2. Ensure domain storage tables exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS `single_receipts` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `tenant_id` int(11) NOT NULL DEFAULT 1,
          `data` longtext NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_tenant` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `single_payments` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `tenant_id` int(11) NOT NULL DEFAULT 1,
          `data` longtext NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_tenant` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `sales_invoices` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `tenant_id` int(11) NOT NULL DEFAULT 1,
          `data` longtext NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_tenant` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `purchase_invoices` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `tenant_id` int(11) NOT NULL DEFAULT 1,
          `data` longtext NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_tenant` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `stock_transfers` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `tenant_id` int(11) NOT NULL DEFAULT 1,
          `data` longtext NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_tenant` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $initialized = true;
    } catch (Exception $e) {
        // Log silently, allow request to proceed
    }
}

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception("Database connection unavailable");
    }
    ensureSyncInfrastructure($pdo);

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? ($method === 'POST' ? 'push' : 'pull');

    // ─────────────────────────────────────────────────────────────────
    // 1. PUSH BATCH TRANSACTIONS (Client ➔ Server Sync Queue)
    // ─────────────────────────────────────────────────────────────────
    if ($action === 'push' || $method === 'POST') {
        $rawInput = file_get_contents('php://input');
        $reqData = json_decode($rawInput, true);
        if (!$reqData && !empty($_POST)) {
            $reqData = $_POST;
        }

        $items = $reqData['items'] ?? ($reqData['transactions'] ?? []);
        if (empty($items) && isset($reqData['data'])) {
            $parsedData = is_array($reqData['data']) ? $reqData['data'] : json_decode($reqData['data'], true);
            if (isset($parsedData['items']) && is_array($parsedData['items'])) {
                $items = $parsedData['items'];
                if (isset($parsedData['tenant_id'])) $reqData['tenant_id'] = $parsedData['tenant_id'];
            } elseif (is_array($parsedData)) {
                $items = $parsedData;
            }
        }

        // Support single record push
        if (isset($reqData['entity_name']) && isset($reqData['payload'])) {
            $items = [$reqData];
        }

        if (empty($items) || !is_array($items)) {
            echo json_encode([
                'success' => false,
                'error' => 'No sync items found in request payload. Expected array of sync_queue records.'
            ]);
            exit;
        }

        $deviceId = $reqData['device_id'] ?? 'UNKNOWN_DEV';
        $branchId = $reqData['branch_id'] ?? 'BR-CENTRAL';

        $stmtUpsert = $pdo->prepare("INSERT INTO `sync_queue` 
            (`id`, `entity_name`, `record_id`, `operation`, `payload`, `status`, `retry_count`, `created_at`, `synced_at`)
            VALUES (:id, :entity_name, :record_id, :operation, :payload, 'SYNCED', :retry_count, :created_at, NOW())
            ON DUPLICATE KEY UPDATE 
                `status` = 'SYNCED',
                `synced_at` = NOW(),
                `retry_count` = VALUES(`retry_count`),
                `payload` = VALUES(`payload`),
                `error_message` = NULL");

        $pdo->beginTransaction();
        $results = [];

        // In-memory batch sequence counters ensuring strictly unique, incrementing numbers
        static $lastAssignedReceiptSeq = 0;
        static $lastAssignedPaymentSeq = 0;
        static $lastAssignedSaleSeq = 0;
        static $lastAssignedPurchaseSeq = 0;
        static $lastAssignedTransferSeq = 0;

        foreach ($items as $item) {
            $dataObj = $item['data'] ?? ($item['payload'] ?? []);
            $id = $item['queue_id'] ?? ($item['id'] ?? null);
            if (empty($id)) {
                $id = is_array($dataObj) && !empty($dataObj['id']) ? $dataObj['id'] : null;
            }
            if (empty($id)) {
                // Generate RFC 4122 v4 UUID
                $randBytes = random_bytes(16);
                $randBytes[6] = chr(ord($randBytes[6]) & 0x0f | 0x40);
                $randBytes[8] = chr(ord($randBytes[8]) & 0x3f | 0x80);
                $id = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($randBytes), 4));
            }

            $entityName = $item['entity'] ?? ($item['entity_name'] ?? 'sale_invoice');
            $recordId = is_array($dataObj) ? ($dataObj['id'] ?? ($item['record_id'] ?? null)) : ($item['record_id'] ?? null);
            $operation = strtoupper($item['operation'] ?? 'INSERT');
            $payloadObj = is_string($dataObj) ? json_decode($dataObj, true) : $dataObj;
            if (!is_array($payloadObj)) $payloadObj = [];
            $retryCount = intval($item['retry_count'] ?? 0);
            
            $rawDate = is_array($dataObj) ? ($dataObj['created_at'] ?? ($item['created_at'] ?? null)) : ($item['created_at'] ?? null);
            $createdAt = !empty($rawDate) 
                ? date('Y-m-d H:i:s', strtotime($rawDate)) 
                : date('Y-m-d H:i:s');

            $tenantId = intval($reqData['tenant_id'] ?? 1);
            $entLower = strtolower($entityName);

            // 1. Idempotency Check: if this UUID or record was already synced, reuse its official ID
            $officialCloudId = null;
            try {
                $stmtExisting = $pdo->prepare("SELECT payload, status FROM sync_queue WHERE id = ? AND status = 'SYNCED' LIMIT 1");
                $stmtExisting->execute([$id]);
                $existingRow = $stmtExisting->fetch(PDO::FETCH_ASSOC);
                if ($existingRow) {
                    $exPayload = json_decode($existingRow['payload'] ?? '', true);
                    if (!empty($exPayload['official_server_id'])) {
                        $officialCloudId = $exPayload['official_server_id'];
                    }
                }
            } catch (Exception $e){}

            // 2. Generate new sequential serial number if not already synced
            if (!$officialCloudId) {
                $isReceipt = (
                    strpos($entLower, 'receipt') !== false ||
                    (isset($payloadObj['type']) && in_array(strtolower($payloadObj['type']), ['receipt', 'single_receipt', 'in', 'cash_receipt'])) ||
                    (isset($payloadObj['mode']) && in_array(strtolower($payloadObj['mode']), ['receipt', 'single_receipt', 'in', 'cash_receipt'])) ||
                    (isset($payloadObj['voucherNo']) && (strpos($payloadObj['voucherNo'], 'IN-') === 0 || strpos($payloadObj['voucherNo'], 'R') === 0)) ||
                    (isset($payloadObj['id']) && (strpos($payloadObj['id'], 'IN-') === 0 || strpos($payloadObj['id'], 'R') === 0))
                );

                $isPayment = !$isReceipt && (
                    strpos($entLower, 'payment') !== false ||
                    strpos($entLower, 'voucher') !== false ||
                    strpos($entLower, 'disburse') !== false ||
                    (isset($payloadObj['type']) && in_array(strtolower($payloadObj['type']), ['payment', 'disbursement', 'single_payment', 'out', 'cash_payment'])) ||
                    (isset($payloadObj['mode']) && in_array(strtolower($payloadObj['mode']), ['payment', 'disbursement', 'single_payment', 'out', 'cash_payment'])) ||
                    (isset($payloadObj['voucherNo']) && (strpos($payloadObj['voucherNo'], 'OUT-') === 0 || strpos($payloadObj['voucherNo'], 'E') === 0)) ||
                    (isset($payloadObj['id']) && (strpos($payloadObj['id'], 'OUT-') === 0 || strpos($payloadObj['id'], 'E') === 0))
                );

                $isSale = (
                    strpos($entLower, 'sale') !== false ||
                    $entLower === 'invoices' ||
                    (isset($payloadObj['type']) && in_array(strtolower($payloadObj['type']), ['sale', 'sale_invoice', 'pos'])) ||
                    (isset($payloadObj['invoiceNo']) && (strpos($payloadObj['invoiceNo'], 'INV-') === 0))
                );

                $isPurchase = (
                    strpos($entLower, 'purchase') !== false ||
                    (isset($payloadObj['type']) && in_array(strtolower($payloadObj['type']), ['purchase', 'purchase_invoice'])) ||
                    (isset($payloadObj['invoiceNo']) && (strpos($payloadObj['invoiceNo'], 'PUR-') === 0))
                );

                $isTransfer = (
                    strpos($entLower, 'transfer') !== false ||
                    (isset($payloadObj['type']) && strpos(strtolower($payloadObj['type']), 'transfer') !== false) ||
                    (isset($payloadObj['transferNo']) && strpos($payloadObj['transferNo'], 'TR-') === 0)
                );

                $isAudit = (
                    strpos($entLower, 'audit') !== false ||
                    (isset($payloadObj['type']) && strpos(strtolower($payloadObj['type']), 'audit') !== false) ||
                    (isset($payloadObj['auditNo']) && strpos($payloadObj['auditNo'], 'AUD-') === 0)
                );

                if ($operation === 'DELETE') {
                    try {
                        if ($isReceipt) {
                            $stmtDel = $pdo->prepare("DELETE FROM single_receipts WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.voucherNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                            $stmtDel->execute([$tenantId, $recordId, $recordId]);
                        } elseif ($isPayment) {
                            $stmtDel = $pdo->prepare("DELETE FROM single_payments WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.voucherNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                            $stmtDel->execute([$tenantId, $recordId, $recordId]);
                        } elseif ($isSale) {
                            $stmtDel = $pdo->prepare("DELETE FROM sales_invoices WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                            $stmtDel->execute([$tenantId, $recordId, $recordId]);
                        } elseif ($isPurchase) {
                            $stmtDel = $pdo->prepare("DELETE FROM purchase_invoices WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                            $stmtDel->execute([$tenantId, $recordId, $recordId]);
                        } elseif ($isTransfer || $isAudit) {
                            $stmtDel = $pdo->prepare("DELETE FROM stock_transfers WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.transferNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.auditNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                            $stmtDel->execute([$tenantId, $recordId, $recordId, $recordId]);
                        }
                    } catch (Exception $delEx) {}
                    $officialCloudId = 'DELETED-' . $recordId;
                    $payloadObj['sync_status'] = 'DELETED';
                    $payloadObj['official_server_id'] = $officialCloudId;
                } elseif ($operation === 'UPDATE') {
                    try {
                        $upData = json_encode($payloadObj, JSON_UNESCAPED_UNICODE);
                        if ($isReceipt) {
                            $stmtUp = $pdo->prepare("UPDATE single_receipts SET data = ? WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.voucherNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                            $stmtUp->execute([$upData, $tenantId, $recordId, $recordId]);
                        } elseif ($isPayment) {
                            $stmtUp = $pdo->prepare("UPDATE single_payments SET data = ? WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.voucherNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                            $stmtUp->execute([$upData, $tenantId, $recordId, $recordId]);
                        } elseif ($isSale) {
                            $stmtUp = $pdo->prepare("UPDATE sales_invoices SET data = ? WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                            $stmtUp->execute([$upData, $tenantId, $recordId, $recordId]);
                        } elseif ($isPurchase) {
                            $stmtUp = $pdo->prepare("UPDATE purchase_invoices SET data = ? WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                            $stmtUp->execute([$upData, $tenantId, $recordId, $recordId]);
                        } elseif ($isTransfer || $isAudit) {
                            $stmtUp = $pdo->prepare("UPDATE stock_transfers SET data = ? WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.transferNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.auditNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                            $stmtUp->execute([$upData, $tenantId, $recordId, $recordId, $recordId]);
                        }
                    } catch (Exception $upEx) {}
                    $officialCloudId = $recordId;
                    $payloadObj['sync_status'] = 'SYNCED';
                    $payloadObj['official_server_id'] = $officialCloudId;
                } else {
                    try {
                    if ($isReceipt) {
                        // 1. سند استلام / قبض نقدي أو بنكي (single_receipts)
                        $pfx = 'R' . date('ym');
                        $maxSeq = 0;
                        $stmtCheck = $pdo->prepare("SELECT data FROM single_receipts WHERE tenant_id = ? ORDER BY id DESC LIMIT 50");
                        $stmtCheck->execute([$tenantId]);
                        while ($rRow = $stmtCheck->fetch(PDO::FETCH_ASSOC)) {
                            $pData = json_decode($rRow['data'] ?? '', true);
                            if (!$pData) continue;
                            $c = $pData['voucherNo'] ?? ($pData['id'] ?? '');
                            if (strpos($c, $pfx) === 0) {
                                $num = (int)substr($c, strlen($pfx));
                                if ($num > $maxSeq) $maxSeq = $num;
                            }
                        }
                        if ($lastAssignedReceiptSeq > $maxSeq) {
                            $maxSeq = $lastAssignedReceiptSeq;
                        }
                        $nextSeq = $maxSeq + 1;
                        $lastAssignedReceiptSeq = $nextSeq;
                        $officialCloudId = $pfx . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

                        $payloadObj['id'] = $officialCloudId;
                        $payloadObj['voucherNo'] = $officialCloudId;
                        $payloadObj['offline_record_id'] = $recordId;
                        $payloadObj['isPosted'] = true;
                        $payloadObj['sync_status'] = 'SYNCED';
                        $payloadObj['official_server_id'] = $officialCloudId;
                        $stmtDom = $pdo->prepare("INSERT INTO single_receipts (tenant_id, data) VALUES (?, ?)");
                        $stmtDom->execute([$tenantId, json_encode($payloadObj, JSON_UNESCAPED_UNICODE)]);

                    } elseif ($isPayment) {
                        // 2. سند صرف نقدي / منصرفات (single_payments)
                        $pfx = 'E' . date('ym');
                        $maxSeq = 0;
                        $stmtCheck = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ? ORDER BY id DESC LIMIT 50");
                        $stmtCheck->execute([$tenantId]);
                        while ($pRow = $stmtCheck->fetch(PDO::FETCH_ASSOC)) {
                            $pData = json_decode($pRow['data'] ?? '', true);
                            if (!$pData) continue;
                            $c = $pData['voucherNo'] ?? ($pData['id'] ?? '');
                            if (strpos($c, $pfx) === 0) {
                                $num = (int)substr($c, strlen($pfx));
                                if ($num > $maxSeq) $maxSeq = $num;
                            }
                        }
                        if ($lastAssignedPaymentSeq > $maxSeq) {
                            $maxSeq = $lastAssignedPaymentSeq;
                        }
                        $nextSeq = $maxSeq + 1;
                        $lastAssignedPaymentSeq = $nextSeq;
                        $officialCloudId = $pfx . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

                        $payloadObj['id'] = $officialCloudId;
                        $payloadObj['voucherNo'] = $officialCloudId;
                        $payloadObj['offline_record_id'] = $recordId;
                        $payloadObj['isPosted'] = true;
                        $payloadObj['sync_status'] = 'SYNCED';
                        $payloadObj['official_server_id'] = $officialCloudId;
                        $stmtDom = $pdo->prepare("INSERT INTO single_payments (tenant_id, data) VALUES (?, ?)");
                        $stmtDom->execute([$tenantId, json_encode($payloadObj, JSON_UNESCAPED_UNICODE)]);

                    } elseif ($isSale) {
                        // 3. فواتير مبيعات ونقاط بيع (sales_invoices)
                        $pfx = 'INV-' . date('ym');
                        $maxSeq = 0;
                        $stmtCheck = $pdo->prepare("SELECT data FROM sales_invoices WHERE tenant_id = ? ORDER BY id DESC LIMIT 50");
                        $stmtCheck->execute([$tenantId]);
                        while ($sRow = $stmtCheck->fetch(PDO::FETCH_ASSOC)) {
                            $sData = json_decode($sRow['data'] ?? '', true);
                            if (!$sData) continue;
                            $c = $sData['invoiceNo'] ?? ($sData['id'] ?? '');
                            if (strpos($c, $pfx) === 0) {
                                $num = (int)substr($c, strlen($pfx));
                                if ($num > $maxSeq) $maxSeq = $num;
                            }
                        }
                        if ($lastAssignedSaleSeq > $maxSeq) {
                            $maxSeq = $lastAssignedSaleSeq;
                        }
                        $nextSeq = $maxSeq + 1;
                        $lastAssignedSaleSeq = $nextSeq;
                        $officialCloudId = $pfx . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

                        $payloadObj['id'] = $officialCloudId;
                        $payloadObj['invoiceNo'] = $officialCloudId;
                        $payloadObj['offline_record_id'] = $recordId;
                        $payloadObj['sync_status'] = 'SYNCED';
                        $payloadObj['official_server_id'] = $officialCloudId;
                        $stmtDom = $pdo->prepare("INSERT INTO sales_invoices (tenant_id, data) VALUES (?, ?)");
                        $stmtDom->execute([$tenantId, json_encode($payloadObj, JSON_UNESCAPED_UNICODE)]);

                    } elseif ($isPurchase) {
                        // 4. فواتير مشتريات وتوريد (purchase_invoices)
                        $pfx = 'PUR-' . date('ym');
                        $maxSeq = 0;
                        $stmtCheck = $pdo->prepare("SELECT data FROM purchase_invoices WHERE tenant_id = ? ORDER BY id DESC LIMIT 50");
                        $stmtCheck->execute([$tenantId]);
                        while ($purRow = $stmtCheck->fetch(PDO::FETCH_ASSOC)) {
                            $purData = json_decode($purRow['data'] ?? '', true);
                            if (!$purData) continue;
                            $c = $purData['invoiceNo'] ?? ($purData['id'] ?? '');
                            if (strpos($c, $pfx) === 0) {
                                $num = (int)substr($c, strlen($pfx));
                                if ($num > $maxSeq) $maxSeq = $num;
                            }
                        }
                        if ($lastAssignedPurchaseSeq > $maxSeq) {
                            $maxSeq = $lastAssignedPurchaseSeq;
                        }
                        $nextSeq = $maxSeq + 1;
                        $lastAssignedPurchaseSeq = $nextSeq;
                        $officialCloudId = $pfx . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

                        $payloadObj['id'] = $officialCloudId;
                        $payloadObj['invoiceNo'] = $officialCloudId;
                        $payloadObj['offline_record_id'] = $recordId;
                        $payloadObj['sync_status'] = 'SYNCED';
                        $payloadObj['official_server_id'] = $officialCloudId;
                        $stmtDom = $pdo->prepare("INSERT INTO purchase_invoices (tenant_id, data) VALUES (?, ?)");
                        $stmtDom->execute([$tenantId, json_encode($payloadObj, JSON_UNESCAPED_UNICODE)]);

                    } elseif ($isTransfer || $isAudit) {
                        // 5. تحويلات وجرد المستودعات (stock_transfers)
                        $pfx = $isAudit ? ('AUD-' . date('ym')) : ('TR-' . date('ym'));
                        $maxSeq = 0;
                        $stmtCheck = $pdo->prepare("SELECT data FROM stock_transfers WHERE tenant_id = ? ORDER BY id DESC LIMIT 50");
                        $stmtCheck->execute([$tenantId]);
                        while ($tRow = $stmtCheck->fetch(PDO::FETCH_ASSOC)) {
                            $tData = json_decode($tRow['data'] ?? '', true);
                            if (!$tData) continue;
                            $c = $tData['transferNo'] ?? ($tData['auditNo'] ?? ($tData['id'] ?? ''));
                            if (strpos($c, $pfx) === 0) {
                                $num = (int)substr($c, strlen($pfx));
                                if ($num > $maxSeq) $maxSeq = $num;
                            }
                        }
                        if ($lastAssignedTransferSeq > $maxSeq) {
                            $maxSeq = $lastAssignedTransferSeq;
                        }
                        $nextSeq = $maxSeq + 1;
                        $lastAssignedTransferSeq = $nextSeq;
                        $officialCloudId = $pfx . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

                        $payloadObj['id'] = $officialCloudId;
                        $payloadObj['official_server_id'] = $officialCloudId;
                        $payloadObj['sync_status'] = 'SYNCED';
                        $stmtDom = $pdo->prepare("INSERT INTO stock_transfers (tenant_id, data) VALUES (?, ?)");
                        $stmtDom->execute([$tenantId, json_encode($payloadObj, JSON_UNESCAPED_UNICODE)]);

                    } else {
                        $officialCloudId = '#' . (10000 + (abs(crc32($id)) % 89999));
                        $payloadObj['official_server_id'] = $officialCloudId;
                        $payloadObj['sync_status'] = 'SYNCED';
                    }
                } catch (Exception $domEx) {
                    $officialCloudId = $officialCloudId ?: ('#' . (10000 + (abs(crc32($id)) % 89999)));
                    }
                }
            }

            // 3. Upsert sync_queue with final official sequence number embedded
            $finalPayloadStr = json_encode($payloadObj, JSON_UNESCAPED_UNICODE);
            $stmtUpsert->execute([
                ':id' => $id,
                ':entity_name' => $entityName,
                ':record_id' => $recordId,
                ':operation' => $operation,
                ':payload' => $finalPayloadStr,
                ':retry_count' => $retryCount,
                ':created_at' => $createdAt
            ]);

            $serverId = 'CENTRAL-' . strtoupper(str_replace(' ', '_', $entityName)) . '-' . ($officialCloudId ?: strtoupper(substr(md5($id), 0, 6)));
            $results[] = [
                'queue_id' => $id,
                'id' => $id,
                'entity' => $entityName,
                'record_id' => $recordId,
                'status' => 'SYNCED',
                'server_id' => $officialCloudId,
                'official_server_id' => $officialCloudId,
                'server_ref' => $officialCloudId,
                'central_code' => $serverId,
                'synced_at' => date('c')
            ];
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'device_id' => $deviceId,
            'branch_id' => $branchId,
            'batch_id' => 'BATCH-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
            'processed_count' => count($results),
            'failed_count' => 0,
            'results' => $results,
            'server_time' => date('c')
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────
    // 2. PULL TRANSACTIONS (Server ➔ Client Delta Sync)
    // ─────────────────────────────────────────────────────────────────
    if ($action === 'pull' || $method === 'GET') {
        $statusFilter = $_GET['status'] ?? null;
        $entityFilter = $_GET['entity_name'] ?? null;
        $limit = min(100, max(1, intval($_GET['limit'] ?? 50)));

        $sql = "SELECT `id`, `entity_name`, `record_id`, `operation`, `payload`, `status`, `retry_count`, `created_at`, `synced_at` 
                FROM `sync_queue` WHERE 1=1";
        $params = [];

        if (!empty($statusFilter)) {
            $sql .= " AND `status` = :status";
            $params[':status'] = strtoupper($statusFilter);
        }
        if (!empty($entityFilter)) {
            $sql .= " AND `entity_name` = :entity";
            $params[':entity'] = $entityFilter;
        }

        $sql .= " ORDER BY `created_at` DESC LIMIT " . $limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'count' => count($records),
            'items' => $records,
            'timestamp' => date('c')
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
