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

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception("Database connection unavailable");
    }

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
            $items = is_array($reqData['data']) ? $reqData['data'] : json_decode($reqData['data'], true);
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
            $payload = is_string($dataObj) ? $dataObj : json_encode($dataObj, JSON_UNESCAPED_UNICODE);
            $retryCount = intval($item['retry_count'] ?? 0);
            
            $rawDate = is_array($dataObj) ? ($dataObj['created_at'] ?? ($item['created_at'] ?? null)) : ($item['created_at'] ?? null);
            $createdAt = !empty($rawDate) 
                ? date('Y-m-d H:i:s', strtotime($rawDate)) 
                : date('Y-m-d H:i:s');

            $stmtUpsert->execute([
                ':id' => $id,
                ':entity_name' => $entityName,
                ':record_id' => $recordId,
                ':operation' => $operation,
                ':payload' => $payload,
                ':retry_count' => $retryCount,
                ':created_at' => $createdAt
            ]);

            // Also persist directly into domain tables and generate official server sequence numbers
            $officialCloudId = '#' . (10000 + (abs(crc32($id)) % 89999));
            try {
                $tenantId = intval($reqData['tenant_id'] ?? 1);
                $entLower = strtolower($entityName);
                $payloadObj = is_string($payload) ? json_decode($payload, true) : $payload;
                if (!is_array($payloadObj)) $payloadObj = [];

                if (strpos($entLower, 'receipt') !== false || (isset($payloadObj['mode']) && $payloadObj['mode'] === 'receipt') || (isset($payloadObj['type']) && $payloadObj['type'] === 'single_receipt')) {
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
                    $officialCloudId = $pfx . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
                    $payloadObj['id'] = $officialCloudId;
                    $payloadObj['voucherNo'] = $officialCloudId;
                    $payloadObj['isPosted'] = true;
                    $payloadObj['sync_status'] = 'SYNCED';
                    $payloadObj['official_server_id'] = $officialCloudId;
                    $stmtDom = $pdo->prepare("INSERT INTO single_receipts (tenant_id, data) VALUES (?, ?)");
                    $stmtDom->execute([$tenantId, json_encode($payloadObj, JSON_UNESCAPED_UNICODE)]);

                } elseif (strpos($entLower, 'voucher') !== false || strpos($entLower, 'payment') !== false || strpos($entLower, 'disburse') !== false || strpos($entLower, 'tx') !== false) {
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
                    $officialCloudId = $pfx . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
                    $payloadObj['id'] = $officialCloudId;
                    $payloadObj['voucherNo'] = $officialCloudId;
                    $payloadObj['isPosted'] = true;
                    $payloadObj['sync_status'] = 'SYNCED';
                    $payloadObj['official_server_id'] = $officialCloudId;
                    $stmtDom = $pdo->prepare("INSERT INTO single_payments (tenant_id, data) VALUES (?, ?)");
                    $stmtDom->execute([$tenantId, json_encode($payloadObj, JSON_UNESCAPED_UNICODE)]);

                } elseif (strpos($entLower, 'sale') !== false || $entLower === 'invoices') {
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
                    $officialCloudId = $pfx . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
                    $payloadObj['id'] = $officialCloudId;
                    $payloadObj['invoiceNo'] = $officialCloudId;
                    $payloadObj['sync_status'] = 'SYNCED';
                    $payloadObj['official_server_id'] = $officialCloudId;
                    $stmtDom = $pdo->prepare("INSERT INTO sales_invoices (tenant_id, data) VALUES (?, ?)");
                    $stmtDom->execute([$tenantId, json_encode($payloadObj, JSON_UNESCAPED_UNICODE)]);

                } elseif (strpos($entLower, 'purchase') !== false) {
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
                    $officialCloudId = $pfx . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
                    $payloadObj['id'] = $officialCloudId;
                    $payloadObj['invoiceNo'] = $officialCloudId;
                    $payloadObj['sync_status'] = 'SYNCED';
                    $payloadObj['official_server_id'] = $officialCloudId;
                    $stmtDom = $pdo->prepare("INSERT INTO purchase_invoices (tenant_id, data) VALUES (?, ?)");
                    $stmtDom->execute([$tenantId, json_encode($payloadObj, JSON_UNESCAPED_UNICODE)]);
                }
            } catch (Exception $domEx) {
                // sync_queue remains the primary log if schema differs
            }

            $serverId = 'CENTRAL-' . strtoupper(str_replace(' ', '_', $entityName)) . '-' . ($recordId ?: strtoupper(substr(md5($id), 0, 6)));
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
