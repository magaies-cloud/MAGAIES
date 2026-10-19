<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../db_bridge.php';
require_once __DIR__ . '/check_entity_usage.php';
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

getDBConnection();
global $GLOBAL_TENANT_ID;
$tenant_id = !empty($GLOBAL_TENANT_ID) ? (int)$GLOBAL_TENANT_ID : 12;

$reqHeaderTid = $_SERVER['HTTP_X_TENANT_ID'] ?? '';
$reqGetTid = $_GET['tenant_id'] ?? '';
$reqCookieTid = $_COOKIE['currentTenantId'] ?? '';

if (is_numeric($reqHeaderTid) && (int)$reqHeaderTid > 1) {
    $tenant_id = (int)$reqHeaderTid;
} elseif (is_numeric($reqGetTid) && (int)$reqGetTid > 1) {
    $tenant_id = (int)$reqGetTid;
} elseif (is_numeric($reqCookieTid) && (int)$reqCookieTid > 1) {
    $tenant_id = (int)$reqCookieTid;
}

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = trim(str_replace('Bearer', '', $authHeader));
if (empty($token)) {
    $token = $_COOKIE['saas_token'] ?? $_COOKIE['authToken'] ?? '';
}
if (!empty($token)) {
    $stmtAuth = $pdo->prepare('SELECT tenant_id FROM users WHERE token = ? LIMIT 1');
    $stmtAuth->execute([$token]);
    $t = $stmtAuth->fetchColumn();
    if ($t) { $tenant_id = (int)$t; }
}

// Verify tenant_id exists in tenants table to prevent Foreign Key constraint failures
try {
    $stmtVerifyTenant = $pdo->prepare('SELECT id FROM tenants WHERE id = ? LIMIT 1');
    $stmtVerifyTenant->execute([$tenant_id]);
    if (!$stmtVerifyTenant->fetchColumn()) {
        $tenant_id = !empty($GLOBAL_TENANT_ID) && (int)$GLOBAL_TENANT_ID > 1 ? (int)$GLOBAL_TENANT_ID : 12;
    }
} catch (Exception $e) {
    if ($tenant_id <= 1) $tenant_id = 12;
}

$input = json_decode(file_get_contents('php://input'), true);
$type = $input['type'] ?? $input['action'] ?? null;
if (!$input || !$type) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid payload'], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = $input['data'] ?? $input['customer'] ?? null;

function isDuplicateNameDB($pdo, $tenant_id, $table, $name, $currentId) {
    if (empty(trim($name))) return false;
    $name = trim($name);
    $hasSearchName = in_array($table, ['items', 'customers'], true);
    if ($hasSearchName) {
        $stmt = $pdo->prepare("SELECT id FROM `$table` WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) AND LOWER(search_name) = LOWER(?) AND (frontend_id IS NULL OR frontend_id != ?) LIMIT 1");
        $stmt->execute([$tenant_id, $name, $currentId]);
        return $stmt->fetchColumn() ? true : false;
    } else {
        $stmt = $pdo->prepare("SELECT id FROM `$table` WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) AND LOWER(JSON_UNQUOTE(JSON_EXTRACT(data, '$.name'))) = LOWER(?) AND (frontend_id IS NULL OR frontend_id != ?) LIMIT 1");
        $stmt->execute([$tenant_id, $name, $currentId]);
        return $stmt->fetchColumn() ? true : false;
    }
}

try {
    switch ($type) {
        // --- 1. Global Settings Collections ---
        case 'units':
            if (is_array($data)) {
                $unitsJson = json_encode(array_values(array_unique(array_filter($data))), JSON_UNESCAPED_UNICODE);
                $stmt = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('erp_global_units_v1', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
                $stmt->execute([$unitsJson]);
            }
            break;

        case 'categories':
            if (is_array($data)) {
                $catsJson = json_encode(array_values(array_unique(array_filter($data))), JSON_UNESCAPED_UNICODE);
                $stmt = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('erp_categories_v1', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
                $stmt->execute([$catsJson]);
            }
            break;

        case 'cost_centers':
            if (is_array($data)) {
                $ccJson = json_encode($data, JSON_UNESCAPED_UNICODE);
                $stmt = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('erp_cost_centers_v1', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
                $stmt->execute([$ccJson]);
                $stmt2 = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('erp_jobs_v1', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
                $stmt2->execute([$ccJson]);
            }
            break;

        case 'currency_rates':
            if (is_array($data)) {
                $crJson = json_encode($data, JSON_UNESCAPED_UNICODE);
                $stmt = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('erp_currency_rates_v1', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
                $stmt->execute([$crJson]);
            }
            break;

        case 'whatsapp_statement_policy':
            if (is_array($data)) {
                $polJson = json_encode($data, JSON_UNESCAPED_UNICODE);
                $stmt = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('erp_whatsapp_statement_policy_v1', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
                $stmt->execute([$polJson]);
            }
            break;

        // --- 2. Check Entity Usage (Unified for all entities) ---
        case 'check_entity_usage':
        case 'check_item_usage':
            if ($data && (isset($data['id']) || isset($data['name']))) {
                $entType = $data['entity_type'] ?? ($type === 'check_item_usage' ? 'item' : 'item');
                $entId = (string)($data['id'] ?? '');
                $entName = (string)($data['name'] ?? '');
                $usageResult = checkEntityUsageInDatabase($pdo, $tenant_id, $entType, $entId, $entName, $data);
                echo json_encode([
                    'success' => true,
                    'type' => 'check_entity_usage',
                    'entity_type' => $entType,
                    'id' => $entId,
                    'name' => $entName,
                    'is_used' => $usageResult['is_used'],
                    'can_delete' => $usageResult['can_delete'],
                    'usage_count' => $usageResult['usage_count'],
                    'usages' => $usageResult['usages'],
                    'message' => $usageResult['is_used']
                        ? 'هذا المكون مستخدم في سندات أو سجلات مسجلة بالنظام ولا يمكن حذفه نهائياً.'
                        : 'هذا المكون غير مستخدم في أي سند أو حركة، ويمكن حذفه نهائياً من قاعدة البيانات.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            break;

        // --- 3. Items ---
        case 'save_item':
            if ($data && isset($data['id'])) {
                $itemId = (string)$data['id'];
                $itemName = $data['name'] ?? '';
                if (isDuplicateNameDB($pdo, $tenant_id, 'items', $itemName, $itemId)) {
                    echo json_encode(['success' => false, 'error' => 'الاسم موجود مسبقاً، يرجى اختيار اسم آخر لتجنب التكرار.']);
                    exit;
                }
                $data['deleted'] = 0;
                $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);

                $stmtCheck = $pdo->prepare("SELECT id FROM items WHERE tenant_id = ? AND (frontend_id = ? OR item_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                $stmtCheck->execute([$tenant_id, $itemId, $itemId, $itemId]);
                $existingId = $stmtCheck->fetchColumn();

                if ($existingId) {
                    $stmtUp = $pdo->prepare("UPDATE items SET item_id = ?, data = ?, updated_at = NOW() WHERE id = ?");
                    $stmtUp->execute([$itemId, $jsonData, $existingId]);
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO items (tenant_id, item_id, data) VALUES (?, ?, ?)");
                    $stmtIns->execute([$tenant_id, $itemId, $jsonData]);
                }
            }
            break;

        case 'delete_item':
            if ($data && (isset($data['id']) || isset($data['name']))) {
                $itemId = (string)($data['id'] ?? '');
                $itemName = (string)($data['name'] ?? '');

                // Security check
                $usageResult = checkEntityUsageInDatabase($pdo, $tenant_id, 'item', $itemId, $itemName, $data);
                if ($usageResult['is_used']) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'type' => 'delete_item',
                        'can_delete' => false,
                        'is_used' => true,
                        'usage_count' => $usageResult['usage_count'],
                        'usages' => $usageResult['usages'],
                        'error' => 'لا يمكن حذف هذا الصنف نهائياً لأنه مستخدم في سندات أو فواتير مسجلة في النظام.'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                // Hard delete from database
                $stmtDel = $pdo->prepare("DELETE FROM items WHERE (tenant_id = ? OR tenant_id IS NULL) AND (frontend_id = ? OR id = ? OR search_name = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                $stmtDel->execute([$tenant_id, $itemId, $itemId, $itemName, $itemId]);

                echo json_encode([
                    'success' => true,
                    'type' => 'delete_item',
                    'can_delete' => true,
                    'is_used' => false,
                    'message' => 'تم حذف الصنف نهائياً من قاعدة البيانات بنجاح ولا يمكن استرجاعه.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            break;

        case 'items_bulk':
            if (is_array($data)) {
                foreach ($data as $item) {
                    if (!$item || !isset($item['id'])) continue;
                    $itemId = (string)$item['id'];
                    $item['deleted'] = !empty($item['deleted']) ? 1 : 0;
                    $jsonData = json_encode($item, JSON_UNESCAPED_UNICODE);

                    $stmtCheck = $pdo->prepare("SELECT id FROM items WHERE tenant_id = ? AND (frontend_id = ? OR item_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                    $stmtCheck->execute([$tenant_id, $itemId, $itemId, $itemId]);
                    $existingId = $stmtCheck->fetchColumn();

                    if ($existingId) {
                        $stmtUp = $pdo->prepare("UPDATE items SET item_id = ?, data = ?, updated_at = NOW() WHERE id = ?");
                        $stmtUp->execute([$itemId, $jsonData, $existingId]);
                    } else {
                        $stmtIns = $pdo->prepare("INSERT INTO items (tenant_id, item_id, data) VALUES (?, ?, ?)");
                        $stmtIns->execute([$tenant_id, $itemId, $jsonData]);
                    }
                }
            }
            break;

        // --- 4. Warehouses ---
        case 'save_warehouse':
            if ($data && isset($data['id'])) {
                $whId = (string)$data['id'];
                $whName = $data['name'] ?? '';
                if (isDuplicateNameDB($pdo, $tenant_id, 'warehouses', $whName, $whId)) {
                    echo json_encode(['success' => false, 'error' => 'الاسم موجود مسبقاً، يرجى اختيار اسم آخر لتجنب التكرار.']);
                    exit;
                }
                $data['deleted'] = 0;
                $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
                $stmtCheck = $pdo->prepare("SELECT id FROM warehouses WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                $stmtCheck->execute([$tenant_id, $whId, $whId]);
                $existingId = $stmtCheck->fetchColumn();
                if ($existingId) {
                    $stmtUp = $pdo->prepare("UPDATE warehouses SET data = ?, updated_at = NOW() WHERE id = ?");
                    $stmtUp->execute([$jsonData, $existingId]);
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO warehouses (tenant_id, data) VALUES (?, ?)");
                    $stmtIns->execute([$tenant_id, $jsonData]);
                }
            }
            break;

        case 'warehouses_bulk':
        case 'warehouses':
            if (is_array($data)) {
                foreach ($data as $item) {
                    if (!$item || !isset($item['id'])) continue;
                    $whId = (string)$item['id'];
                    $item['deleted'] = !empty($item['deleted']) ? 1 : 0;
                    $jsonData = json_encode($item, JSON_UNESCAPED_UNICODE);
                    $stmtCheck = $pdo->prepare("SELECT id FROM warehouses WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                    $stmtCheck->execute([$tenant_id, $whId, $whId]);
                    $existingId = $stmtCheck->fetchColumn();
                    if ($existingId) {
                        $stmtUp = $pdo->prepare("UPDATE warehouses SET data = ?, updated_at = NOW() WHERE id = ?");
                        $stmtUp->execute([$jsonData, $existingId]);
                    } else {
                        $stmtIns = $pdo->prepare("INSERT INTO warehouses (tenant_id, data) VALUES (?, ?)");
                        $stmtIns->execute([$tenant_id, $jsonData]);
                    }
                }
            }
            break;

        case 'delete_warehouse':
            if ($data && (isset($data['id']) || isset($data['name']))) {
                $whId = (string)($data['id'] ?? '');
                $whName = (string)($data['name'] ?? '');

                // Security check
                $usageResult = checkEntityUsageInDatabase($pdo, $tenant_id, 'warehouse', $whId, $whName, $data);
                if ($usageResult['is_used']) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'type' => 'delete_warehouse',
                        'can_delete' => false,
                        'is_used' => true,
                        'usage_count' => $usageResult['usage_count'],
                        'usages' => $usageResult['usages'],
                        'error' => 'لا يمكن حذف هذا المخزن نهائياً لأنه مدرج في سندات أو فواتير أو حركات مسجلة بالنظام.'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                // Hard delete from database
                $stmtDel = $pdo->prepare("DELETE FROM warehouses WHERE (tenant_id = ? OR tenant_id IS NULL) AND (frontend_id = ? OR id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                $stmtDel->execute([$tenant_id, $whId, $whId, $whId]);

                echo json_encode([
                    'success' => true,
                    'type' => 'delete_warehouse',
                    'can_delete' => true,
                    'is_used' => false,
                    'message' => 'تم حذف المخزن نهائياً من قاعدة البيانات بنجاح ولا يمكن استرجاعه.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            break;

        // --- 5. Customers ---
        case 'save_customer':
            if ($data && isset($data['id'])) {
                $cId = (string)$data['id'];
                $cName = $data['name'] ?? '';
                if (isDuplicateNameDB($pdo, $tenant_id, 'customers', $cName, $cId)) {
                    echo json_encode(['success' => false, 'error' => 'الاسم موجود مسبقاً، يرجى اختيار اسم آخر لتجنب التكرار.']);
                    exit;
                }

                // Normalize requested statementPin (Password)
                $rawPin = trim((string)($data['statementPin'] ?? ''));
                $rawPin = strtr($rawPin, ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9','۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']);
                $reqPin = preg_replace('/[^0-9]/', '', $rawPin);

                // Validate unique statementPin across Customers & Suppliers (and maintain background receiptCodeNum)
                $stmtAllCust = $pdo->prepare("SELECT frontend_id, customer_id, data FROM customers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmtAllCust->execute([$tenant_id]);
                $usedCodeNums = [];
                $usedPins = [];
                $reqCodeNum = isset($data['receiptCodeNum']) ? (int)$data['receiptCodeNum'] : 0;
                $reqCodeWord = trim((string)($data['receiptCodeWord'] ?? ''));

                while ($cRow = $stmtAllCust->fetch(PDO::FETCH_ASSOC)) {
                    $cObj = json_decode($cRow['data'], true) ?: [];
                    if (!empty($cObj['deleted'])) continue;
                    $otherId = (string)($cObj['id'] ?? $cRow['frontend_id'] ?? $cRow['customer_id'] ?? '');
                    if ($otherId === $cId) continue;

                    $otherNum = isset($cObj['receiptCodeNum']) ? (int)$cObj['receiptCodeNum'] : 0;
                    if ($otherNum >= 1) {
                        $usedCodeNums[$otherNum] = $cObj['name'] ?? 'عميل آخر';
                    }
                    $otherPinRaw = trim((string)($cObj['statementPin'] ?? ''));
                    $otherPinRaw = strtr($otherPinRaw, ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9','۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']);
                    $otherPin = preg_replace('/[^0-9]/', '', $otherPinRaw);
                    if ($otherPin !== '') {
                        $usedPins[$otherPin] = $cObj['name'] ?? 'عميل آخر';
                        if ($reqPin !== '' && $otherPin === $reqPin) {
                            echo json_encode(['success' => false, 'error' => 'الرقم السري / الباسورد (' . $reqPin . ') مسجل مسبقاً باسم العميل: ' . ($cObj['name'] ?? '') . ' — يرجى اختيار باسورد آخر أو توليد رقم جديد.']);
                            exit;
                        }
                    }
                }

                $stmtAllSupp = $pdo->prepare("SELECT frontend_id, supplier_id, data FROM suppliers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmtAllSupp->execute([$tenant_id]);
                while ($sRow = $stmtAllSupp->fetch(PDO::FETCH_ASSOC)) {
                    $sObj = json_decode($sRow['data'], true) ?: [];
                    if (!empty($sObj['deleted'])) continue;
                    $otherNum = isset($sObj['receiptCodeNum']) ? (int)$sObj['receiptCodeNum'] : 0;
                    if ($otherNum >= 1) {
                        $usedCodeNums[$otherNum] = $sObj['name'] ?? 'مورد آخر';
                    }
                    $otherPinRaw = trim((string)($sObj['statementPin'] ?? ''));
                    $otherPinRaw = strtr($otherPinRaw, ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9','۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']);
                    $otherPin = preg_replace('/[^0-9]/', '', $otherPinRaw);
                    if ($otherPin !== '') {
                        $usedPins[$otherPin] = $sObj['name'] ?? 'مورد آخر';
                        if ($reqPin !== '' && $otherPin === $reqPin) {
                            echo json_encode(['success' => false, 'error' => 'الرقم السري / الباسورد (' . $reqPin . ') مسجل مسبقاً باسم المورد: ' . ($sObj['name'] ?? '') . ' — يرجى اختيار باسورد آخر أو توليد رقم جديد.']);
                            exit;
                        }
                    }
                }

                if ($reqCodeNum < 1 || isset($usedCodeNums[$reqCodeNum])) {
                    $cand = 1;
                    while (isset($usedCodeNums[$cand])) {
                        $cand++;
                    }
                    $data['receiptCodeNum'] = $cand;
                } else {
                    $data['receiptCodeNum'] = $reqCodeNum;
                }
                $data['receiptCodeWord'] = $reqCodeWord;

                if ($reqPin === '') {
                    for ($att = 0; $att < 900; $att++) {
                        $candPin = (string)random_int(100, 999);
                        if (!isset($usedPins[$candPin])) {
                            $reqPin = $candPin;
                            break;
                        }
                    }
                    if ($reqPin === '') {
                        $seqPin = 100;
                        while (isset($usedPins[(string)$seqPin])) {
                            $seqPin++;
                        }
                        $reqPin = (string)$seqPin;
                    }
                }
                $data['statementPin'] = $reqPin;
                $mode = trim((string)($data['statementAccessMode'] ?? ''));
                if (!in_array($mode, ['disabled', 'balance_only', 'detailed'], true)) {
                    $mode = (isset($data['allowStatementRequest']) && !$data['allowStatementRequest']) ? 'disabled' : 'detailed';
                }
                $data['statementAccessMode'] = $mode;
                $data['allowStatementRequest'] = ($mode !== 'disabled');

                $data['deleted'] = 0;
                $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
                $stmtCheck = $pdo->prepare("SELECT id FROM customers WHERE tenant_id = ? AND (frontend_id = ? OR customer_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                $stmtCheck->execute([$tenant_id, $cId, $cId, $cId]);
                $existingId = $stmtCheck->fetchColumn();
                if ($existingId) {
                    $stmtUp = $pdo->prepare("UPDATE customers SET customer_id = ?, data = ?, updated_at = NOW() WHERE id = ?");
                    $stmtUp->execute([$cId, $jsonData, $existingId]);
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO customers (tenant_id, customer_id, data) VALUES (?, ?, ?)");
                    $stmtIns->execute([$tenant_id, $cId, $jsonData]);
                }
            }
            break;

        case 'customers_bulk':
        case 'customers':
            if (is_array($data)) {
                foreach ($data as $item) {
                    if (!$item || !isset($item['id'])) continue;
                    $cId = (string)$item['id'];
                    $rawPin = trim((string)($item['statementPin'] ?? ''));
                    $rawPin = strtr($rawPin, ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9','۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']);
                    $item['statementPin'] = preg_replace('/[^0-9]/', '', $rawPin);
                    $mode = trim((string)($item['statementAccessMode'] ?? ''));
                    if (!in_array($mode, ['disabled', 'balance_only', 'detailed'], true)) {
                        $mode = (isset($item['allowStatementRequest']) && !$item['allowStatementRequest']) ? 'disabled' : 'detailed';
                    }
                    $item['statementAccessMode'] = $mode;
                    $item['allowStatementRequest'] = ($mode !== 'disabled');
                    $item['deleted'] = !empty($item['deleted']) ? 1 : 0;
                    $jsonData = json_encode($item, JSON_UNESCAPED_UNICODE);

                    $stmtCheck = $pdo->prepare("SELECT id FROM customers WHERE tenant_id = ? AND (frontend_id = ? OR customer_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                    $stmtCheck->execute([$tenant_id, $cId, $cId, $cId]);
                    $existingId = $stmtCheck->fetchColumn();

                    if ($existingId) {
                        $stmtUp = $pdo->prepare("UPDATE customers SET customer_id = ?, data = ?, updated_at = NOW() WHERE id = ?");
                        $stmtUp->execute([$cId, $jsonData, $existingId]);
                    } else {
                        $stmtIns = $pdo->prepare("INSERT INTO customers (tenant_id, customer_id, data) VALUES (?, ?, ?)");
                        $stmtIns->execute([$tenant_id, $cId, $jsonData]);
                    }
                }
            }
            break;

        case 'delete_customer':
            if ($data && (isset($data['id']) || isset($data['name']))) {
                $cId = (string)($data['id'] ?? '');
                $cName = (string)($data['name'] ?? '');

                // Security check
                $usageResult = checkEntityUsageInDatabase($pdo, $tenant_id, 'customer', $cId, $cName, $data);
                if ($usageResult['is_used']) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'type' => 'delete_customer',
                        'can_delete' => false,
                        'is_used' => true,
                        'usage_count' => $usageResult['usage_count'],
                        'usages' => $usageResult['usages'],
                        'error' => 'لا يمكن حذف هذا العميل نهائياً لأنه مدرج في سندات أو فواتير أو قيود مسجلة بالنظام.'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                // Hard delete from database
                $stmtDel = $pdo->prepare("DELETE FROM customers WHERE (tenant_id = ? OR tenant_id IS NULL) AND (frontend_id = ? OR id = ? OR search_name = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                $stmtDel->execute([$tenant_id, $cId, $cId, $cName, $cId]);

                echo json_encode([
                    'success' => true,
                    'type' => 'delete_customer',
                    'can_delete' => true,
                    'is_used' => false,
                    'message' => 'تم حذف العميل نهائياً من قاعدة البيانات بنجاح ولا يمكن استرجاعه.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            break;

        // --- 6. Suppliers ---
        case 'save_supplier':
            if ($data && isset($data['id'])) {
                $sId = (string)$data['id'];
                $sName = $data['name'] ?? '';
                if (isDuplicateNameDB($pdo, $tenant_id, 'suppliers', $sName, $sId)) {
                    echo json_encode(['success' => false, 'error' => 'الاسم موجود مسبقاً، يرجى اختيار اسم آخر لتجنب التكرار.']);
                    exit;
                }

                // Normalize requested statementPin (Password)
                $rawPin = trim((string)($data['statementPin'] ?? ''));
                $rawPin = strtr($rawPin, ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9','۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']);
                $reqPin = preg_replace('/[^0-9]/', '', $rawPin);

                // Validate unique statementPin across Customers & Suppliers (and maintain background receiptCodeNum)
                $usedCodeNums = [];
                $usedPins = [];
                $reqCodeNum = isset($data['receiptCodeNum']) ? (int)$data['receiptCodeNum'] : 0;
                $reqCodeWord = trim((string)($data['receiptCodeWord'] ?? ''));

                $stmtAllCust = $pdo->prepare("SELECT frontend_id, customer_id, data FROM customers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmtAllCust->execute([$tenant_id]);
                while ($cRow = $stmtAllCust->fetch(PDO::FETCH_ASSOC)) {
                    $cObj = json_decode($cRow['data'], true) ?: [];
                    if (!empty($cObj['deleted'])) continue;
                    $otherNum = isset($cObj['receiptCodeNum']) ? (int)$cObj['receiptCodeNum'] : 0;
                    if ($otherNum >= 1) {
                        $usedCodeNums[$otherNum] = $cObj['name'] ?? 'عميل آخر';
                    }
                    $otherPinRaw = trim((string)($cObj['statementPin'] ?? ''));
                    $otherPinRaw = strtr($otherPinRaw, ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9','۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']);
                    $otherPin = preg_replace('/[^0-9]/', '', $otherPinRaw);
                    if ($otherPin !== '') {
                        $usedPins[$otherPin] = $cObj['name'] ?? 'عميل آخر';
                        if ($reqPin !== '' && $otherPin === $reqPin) {
                            echo json_encode(['success' => false, 'error' => 'الرقم السري / الباسورد (' . $reqPin . ') مسجل مسبقاً باسم العميل: ' . ($cObj['name'] ?? '') . ' — يرجى اختيار باسورد آخر أو توليد رقم جديد.']);
                            exit;
                        }
                    }
                }

                $stmtAllSupp = $pdo->prepare("SELECT frontend_id, supplier_id, data FROM suppliers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmtAllSupp->execute([$tenant_id]);
                while ($sRow = $stmtAllSupp->fetch(PDO::FETCH_ASSOC)) {
                    $sObj = json_decode($sRow['data'], true) ?: [];
                    if (!empty($sObj['deleted'])) continue;
                    $otherId = (string)($sObj['id'] ?? $sRow['frontend_id'] ?? $sRow['supplier_id'] ?? '');
                    if ($otherId === $sId) continue;

                    $otherNum = isset($sObj['receiptCodeNum']) ? (int)$sObj['receiptCodeNum'] : 0;
                    if ($otherNum >= 1) {
                        $usedCodeNums[$otherNum] = $sObj['name'] ?? 'مورد آخر';
                    }
                    $otherPinRaw = trim((string)($sObj['statementPin'] ?? ''));
                    $otherPinRaw = strtr($otherPinRaw, ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9','۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']);
                    $otherPin = preg_replace('/[^0-9]/', '', $otherPinRaw);
                    if ($otherPin !== '') {
                        $usedPins[$otherPin] = $sObj['name'] ?? 'مورد آخر';
                        if ($reqPin !== '' && $otherPin === $reqPin) {
                            echo json_encode(['success' => false, 'error' => 'الرقم السري / الباسورد (' . $reqPin . ') مسجل مسبقاً باسم المورد: ' . ($sObj['name'] ?? '') . ' — يرجى اختيار باسورد آخر أو توليد رقم جديد.']);
                            exit;
                        }
                    }
                }

                if ($reqCodeNum < 1 || isset($usedCodeNums[$reqCodeNum])) {
                    $cand = 1;
                    while (isset($usedCodeNums[$cand])) {
                        $cand++;
                    }
                    $data['receiptCodeNum'] = $cand;
                } else {
                    $data['receiptCodeNum'] = $reqCodeNum;
                }
                $data['receiptCodeWord'] = $reqCodeWord;

                if ($reqPin === '') {
                    for ($att = 0; $att < 900; $att++) {
                        $candPin = (string)random_int(100, 999);
                        if (!isset($usedPins[$candPin])) {
                            $reqPin = $candPin;
                            break;
                        }
                    }
                    if ($reqPin === '') {
                        $seqPin = 100;
                        while (isset($usedPins[(string)$seqPin])) {
                            $seqPin++;
                        }
                        $reqPin = (string)$seqPin;
                    }
                }
                $data['statementPin'] = $reqPin;
                $mode = trim((string)($data['statementAccessMode'] ?? ''));
                if (!in_array($mode, ['disabled', 'balance_only', 'detailed'], true)) {
                    $mode = (isset($data['allowStatementRequest']) && !$data['allowStatementRequest']) ? 'disabled' : 'detailed';
                }
                $data['statementAccessMode'] = $mode;
                $data['allowStatementRequest'] = ($mode !== 'disabled');
                $data['deleted'] = 0;
                $sPhoneVal = trim((string)($data['phone'] ?? $data['whatsapp'] ?? ''));
                $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
                $stmtCheck = $pdo->prepare("SELECT id FROM suppliers WHERE tenant_id = ? AND (frontend_id = ? OR supplier_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                $stmtCheck->execute([$tenant_id, $sId, $sId, $sId]);
                $existingId = $stmtCheck->fetchColumn();
                if ($existingId) {
                    $stmtUp = $pdo->prepare("UPDATE suppliers SET supplier_id = ?, search_name = ?, search_phone = ?, data = ?, updated_at = NOW() WHERE id = ?");
                    $stmtUp->execute([$sId, trim((string)$sName), $sPhoneVal, $jsonData, $existingId]);
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO suppliers (tenant_id, supplier_id, search_name, search_phone, data) VALUES (?, ?, ?, ?, ?)");
                    $stmtIns->execute([$tenant_id, $sId, trim((string)$sName), $sPhoneVal, $jsonData]);
                }
            }
            break;

        case 'suppliers_bulk':
        case 'suppliers':
            if (is_array($data)) {
                foreach ($data as $item) {
                    if (!$item || !isset($item['id'])) continue;
                    $sId = (string)$item['id'];
                    $sNameVal = trim((string)($item['name'] ?? ''));
                    $sPhoneVal = trim((string)($item['phone'] ?? $item['whatsapp'] ?? ''));
                    $rawPin = trim((string)($item['statementPin'] ?? ''));
                    $rawPin = strtr($rawPin, ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9','۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']);
                    $item['statementPin'] = preg_replace('/[^0-9]/', '', $rawPin);
                    $mode = trim((string)($item['statementAccessMode'] ?? ''));
                    if (!in_array($mode, ['disabled', 'balance_only', 'detailed'], true)) {
                        $mode = (isset($item['allowStatementRequest']) && !$item['allowStatementRequest']) ? 'disabled' : 'detailed';
                    }
                    $item['statementAccessMode'] = $mode;
                    $item['allowStatementRequest'] = ($mode !== 'disabled');
                    $item['deleted'] = !empty($item['deleted']) ? 1 : 0;
                    $jsonData = json_encode($item, JSON_UNESCAPED_UNICODE);

                    $stmtCheck = $pdo->prepare("SELECT id FROM suppliers WHERE tenant_id = ? AND (frontend_id = ? OR supplier_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                    $stmtCheck->execute([$tenant_id, $sId, $sId, $sId]);
                    $existingId = $stmtCheck->fetchColumn();

                    if ($existingId) {
                        $stmtUp = $pdo->prepare("UPDATE suppliers SET supplier_id = ?, search_name = ?, search_phone = ?, data = ?, updated_at = NOW() WHERE id = ?");
                        $stmtUp->execute([$sId, $sNameVal, $sPhoneVal, $jsonData, $existingId]);
                    } else {
                        $stmtIns = $pdo->prepare("INSERT INTO suppliers (tenant_id, supplier_id, search_name, search_phone, data) VALUES (?, ?, ?, ?, ?)");
                        $stmtIns->execute([$tenant_id, $sId, $sNameVal, $sPhoneVal, $jsonData]);
                    }
                }
            }
            break;

        case 'delete_supplier':
            if ($data && (isset($data['id']) || isset($data['name']))) {
                $sId = (string)($data['id'] ?? '');
                $sName = (string)($data['name'] ?? '');

                // Security check
                $usageResult = checkEntityUsageInDatabase($pdo, $tenant_id, 'supplier', $sId, $sName, $data);
                if ($usageResult['is_used']) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'type' => 'delete_supplier',
                        'can_delete' => false,
                        'is_used' => true,
                        'usage_count' => $usageResult['usage_count'],
                        'usages' => $usageResult['usages'],
                        'error' => 'لا يمكن حذف هذا المورد نهائياً لأنه مدرج في سندات أو فواتير أو قيود مسجلة بالنظام.'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                // Hard delete from database
                $stmtDel = $pdo->prepare("DELETE FROM suppliers WHERE (tenant_id = ? OR tenant_id IS NULL) AND (frontend_id = ? OR id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.name')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                $stmtDel->execute([$tenant_id, $sId, $sId, $sName, $sId]);

                echo json_encode([
                    'success' => true,
                    'type' => 'delete_supplier',
                    'can_delete' => true,
                    'is_used' => false,
                    'message' => 'تم حذف المورد نهائياً من قاعدة البيانات بنجاح ولا يمكن استرجاعه.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            break;

        // --- 7. Banks ---
        case 'save_bank':
            if ($data && isset($data['id'])) {
                $bId = (string)$data['id'];
                $bName = $data['name'] ?? '';
                if (isDuplicateNameDB($pdo, $tenant_id, 'banks', $bName, $bId)) {
                    echo json_encode(['success' => false, 'error' => 'الاسم موجود مسبقاً، يرجى اختيار اسم آخر لتجنب التكرار.']);
                    exit;
                }
                $data['deleted'] = 0;
                $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
                $stmtCheck = $pdo->prepare("SELECT id FROM banks WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                $stmtCheck->execute([$tenant_id, $bId, $bId]);
                $existingId = $stmtCheck->fetchColumn();
                if ($existingId) {
                    $stmtUp = $pdo->prepare("UPDATE banks SET data = ?, updated_at = NOW() WHERE id = ?");
                    $stmtUp->execute([$jsonData, $existingId]);
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO banks (tenant_id, data) VALUES (?, ?)");
                    $stmtIns->execute([$tenant_id, $jsonData]);
                }
            }
            break;

        case 'banks_bulk':
        case 'banks':
            if (is_array($data)) {
                foreach ($data as $item) {
                    if (!$item || !isset($item['id'])) continue;
                    $bId = (string)$item['id'];
                    $item['deleted'] = !empty($item['deleted']) ? 1 : 0;
                    $jsonData = json_encode($item, JSON_UNESCAPED_UNICODE);
                    $stmtCheck = $pdo->prepare("SELECT id FROM banks WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                    $stmtCheck->execute([$tenant_id, $bId, $bId]);
                    $existingId = $stmtCheck->fetchColumn();
                    if ($existingId) {
                        $stmtUp = $pdo->prepare("UPDATE banks SET data = ?, updated_at = NOW() WHERE id = ?");
                        $stmtUp->execute([$jsonData, $existingId]);
                    } else {
                        $stmtIns = $pdo->prepare("INSERT INTO banks (tenant_id, data) VALUES (?, ?)");
                        $stmtIns->execute([$tenant_id, $jsonData]);
                    }
                }
            }
            break;

        case 'delete_bank':
            if ($data && (isset($data['id']) || isset($data['name']))) {
                $bId = (string)($data['id'] ?? '');
                $bName = (string)($data['name'] ?? '');

                // Security check
                $usageResult = checkEntityUsageInDatabase($pdo, $tenant_id, 'bank', $bId, $bName, $data);
                if ($usageResult['is_used']) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'type' => 'delete_bank',
                        'can_delete' => false,
                        'is_used' => true,
                        'usage_count' => $usageResult['usage_count'],
                        'usages' => $usageResult['usages'],
                        'error' => 'لا يمكن حذف هذا البنك نهائياً لأنه مدرج في سندات أو فواتير أو حركات دفع/قبض مسجلة بالنظام.'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                // Hard delete from database
                $stmtDel = $pdo->prepare("DELETE FROM banks WHERE (tenant_id = ? OR tenant_id IS NULL) AND (frontend_id = ? OR id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                $stmtDel->execute([$tenant_id, $bId, $bId, $bId]);

                echo json_encode([
                    'success' => true,
                    'type' => 'delete_bank',
                    'can_delete' => true,
                    'is_used' => false,
                    'message' => 'تم حذف البنك نهائياً من قاعدة البيانات بنجاح ولا يمكن استرجاعه.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            break;

        // --- 8. Safes ---
        case 'save_safe':
            if ($data && isset($data['id'])) {
                $sId = (string)$data['id'];
                $sName = $data['name'] ?? '';
                if (isDuplicateNameDB($pdo, $tenant_id, 'safes', $sName, $sId)) {
                    echo json_encode(['success' => false, 'error' => 'الاسم موجود مسبقاً، يرجى اختيار اسم آخر لتجنب التكرار.']);
                    exit;
                }
                $data['deleted'] = 0;
                $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
                $stmtCheck = $pdo->prepare("SELECT id FROM safes WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                $stmtCheck->execute([$tenant_id, $sId, $sId]);
                $existingId = $stmtCheck->fetchColumn();
                if ($existingId) {
                    $stmtUp = $pdo->prepare("UPDATE safes SET data = ?, updated_at = NOW() WHERE id = ?");
                    $stmtUp->execute([$jsonData, $existingId]);
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO safes (tenant_id, data) VALUES (?, ?)");
                    $stmtIns->execute([$tenant_id, $jsonData]);
                }
            }
            break;

        case 'safes_bulk':
        case 'safes':
            if (is_array($data)) {
                foreach ($data as $item) {
                    if (!$item || !isset($item['id'])) continue;
                    $sId = (string)$item['id'];
                    $item['deleted'] = !empty($item['deleted']) ? 1 : 0;
                    $jsonData = json_encode($item, JSON_UNESCAPED_UNICODE);
                    $stmtCheck = $pdo->prepare("SELECT id FROM safes WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                    $stmtCheck->execute([$tenant_id, $sId, $sId]);
                    $existingId = $stmtCheck->fetchColumn();
                    if ($existingId) {
                        $stmtUp = $pdo->prepare("UPDATE safes SET data = ?, updated_at = NOW() WHERE id = ?");
                        $stmtUp->execute([$jsonData, $existingId]);
                    } else {
                        $stmtIns = $pdo->prepare("INSERT INTO safes (tenant_id, data) VALUES (?, ?)");
                        $stmtIns->execute([$tenant_id, $jsonData]);
                    }
                }
            }
            break;

        case 'delete_safe':
            if ($data && (isset($data['id']) || isset($data['name']))) {
                $sId = (string)($data['id'] ?? '');
                $sName = (string)($data['name'] ?? '');

                // Security check
                $usageResult = checkEntityUsageInDatabase($pdo, $tenant_id, 'safe', $sId, $sName, $data);
                if ($usageResult['is_used']) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'type' => 'delete_safe',
                        'can_delete' => false,
                        'is_used' => true,
                        'usage_count' => $usageResult['usage_count'],
                        'usages' => $usageResult['usages'],
                        'error' => 'لا يمكن حذف هذه الخزينة نهائياً لأنها مدرجة في سندات أو فواتير أو حركات نقدية مسجلة بالنظام.'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                // Hard delete from database
                $stmtDel = $pdo->prepare("DELETE FROM safes WHERE (tenant_id = ? OR tenant_id IS NULL) AND (frontend_id = ? OR id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                $stmtDel->execute([$tenant_id, $sId, $sId, $sId]);

                echo json_encode([
                    'success' => true,
                    'type' => 'delete_safe',
                    'can_delete' => true,
                    'is_used' => false,
                    'message' => 'تم حذف الخزينة نهائياً من قاعدة البيانات بنجاح ولا يمكن استرجاعها.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            break;

        // --- 9. Employees / Users ---
        case 'save_employee':
        case 'save_user':
            if ($data && isset($data['id'])) {
                $uId = (string)$data['id'];
                $uName = $data['username'] ?? $data['name'] ?? '';
                if (empty($uName)) {
                    $uName = $data['fullName'] ?? ('user_' . $uId);
                }
                if (isDuplicateNameDB($pdo, $tenant_id, 'users', $uName, $uId)) {
                    echo json_encode(['success' => false, 'error' => 'الاسم موجود مسبقاً، يرجى اختيار اسم آخر لتجنب التكرار.']);
                    exit;
                }
                $uRole = $data['role'] ?? (($data['userType'] ?? '') === 'admin' ? 'admin' : 'cashier');
                $uStatus = $data['status'] ?? 'active';
                $rawPass = !empty($data['password']) ? trim($data['password']) : '123456';
                $hashedPass = password_hash($rawPass, PASSWORD_DEFAULT);
                $data['deleted'] = 0;
                $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);

                $stmtCheck = $pdo->prepare("SELECT id, password FROM users WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                $stmtCheck->execute([$tenant_id, $uId, $uId]);
                $existingRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                if ($existingRow) {
                    if (!empty($data['password'])) {
                        $stmtUp = $pdo->prepare("UPDATE users SET data = ?, username = COALESCE(NULLIF(username, ''), ?), password = ?, role = ?, status = ?, updated_at = NOW() WHERE id = ?");
                        $stmtUp->execute([$jsonData, $uName, $hashedPass, $uRole, $uStatus, $existingRow['id']]);
                    } else {
                        $passSql = empty($existingRow['password']) ? ", password = " . $pdo->quote($hashedPass) : "";
                        $stmtUp = $pdo->prepare("UPDATE users SET data = ?, username = COALESCE(NULLIF(username, ''), ?){$passSql}, role = ?, status = ?, updated_at = NOW() WHERE id = ?");
                        $stmtUp->execute([$jsonData, $uName, $uRole, $uStatus, $existingRow['id']]);
                    }
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO users (tenant_id, data, username, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmtIns->execute([$tenant_id, $jsonData, $uName, $hashedPass, $uRole, $uStatus]);
                }
                echo json_encode(['success' => true, 'id' => $uId], JSON_UNESCAPED_UNICODE);
                exit;
            }
            break;

        case 'delete_user':
        case 'delete_employee':
            if ($data && (isset($data['id']) || isset($data['username']))) {
                $uId = (string)($data['id'] ?? '');
                $uName = (string)($data['username'] ?? $data['name'] ?? '');

                if (in_array(strtolower($uName), ['admin', 'c']) || $uId === 'u_admin_default') {
                    echo json_encode([
                        'success' => false,
                        'is_used' => true,
                        'can_delete' => false,
                        'message' => 'لا يمكن حذف الحساب السيادي للنظام.'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                $usageResult = checkEntityUsageInDatabase($pdo, $tenant_id, 'user', $uId, $uName, $data);
                if ($usageResult['is_used']) {
                    echo json_encode([
                        'success' => false,
                        'type' => 'delete_safe',
                        'can_delete' => false,
                        'is_used' => true,
                        'usage_count' => $usageResult['usage_count'],
                        'usages' => $usageResult['usages'],
                        'message' => 'لا يمكن حذف هذا المستخدم نهائياً نظراً لوجود عمليات أو فواتير أو سندات مرتبطة به في النظام. يمكنك بدلاً من ذلك تعطيل حسابه لمنعه من الدخول والعمل.'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                $stmtDel = $pdo->prepare("DELETE FROM users WHERE tenant_id = ? AND (frontend_id = ? OR id = ? OR username = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                $stmtDel->execute([$tenant_id, $uId, $uId, $uName, $uId]);

                echo json_encode([
                    'success' => true,
                    'type' => 'delete_safe',
                    'can_delete' => true,
                    'is_used' => false,
                    'message' => 'تم حذف المستخدم نهائياً بنجاح.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            break;

        case 'toggle_user_status':
        case 'update_user_status':
            if ($data && isset($data['id'])) {
                $uId = (string)$data['id'];
                $newStatus = ($data['status'] === 'inactive') ? 'inactive' : 'active';
                $stmtCheck = $pdo->prepare("SELECT id, data FROM users WHERE tenant_id = ? AND (frontend_id = ? OR id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                $stmtCheck->execute([$tenant_id, $uId, $uId, $uId]);
                $userRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                if ($userRow) {
                    $uData = json_decode($userRow['data'] ?? '{}', true) ?: [];
                    $uData['status'] = $newStatus;
                    $updatedJson = json_encode($uData, JSON_UNESCAPED_UNICODE);
                    $stmtUp = $pdo->prepare("UPDATE users SET status = ?, data = ?, updated_at = NOW() WHERE id = ?");
                    $stmtUp->execute([$newStatus, $updatedJson, $userRow['id']]);
                }
                echo json_encode(['success' => true, 'status' => $newStatus], JSON_UNESCAPED_UNICODE);
                exit;
            }
            break;

        case 'employees_bulk':
        case 'users_bulk':
        case 'users':
            if (is_array($data)) {
                foreach ($data as $item) {
                    if (!$item || !isset($item['id'])) continue;
                    $uId = (string)$item['id'];
                    $uName = $item['username'] ?? $item['name'] ?? '';
                    if (empty($uName)) {
                        $uName = $item['fullName'] ?? ('user_' . $uId);
                    }
                    $uRole = $item['role'] ?? (($item['userType'] ?? '') === 'admin' ? 'admin' : 'cashier');
                    $uStatus = $item['status'] ?? 'active';
                    $item['deleted'] = !empty($item['deleted']) ? 1 : 0;
                    $rawPass = !empty($item['password']) ? trim($item['password']) : '123456';
                    $hashedPass = password_hash($rawPass, PASSWORD_DEFAULT);
                    $jsonData = json_encode($item, JSON_UNESCAPED_UNICODE);

                    $stmtCheck = $pdo->prepare("SELECT id, password FROM users WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                    $stmtCheck->execute([$tenant_id, $uId, $uId]);
                    $existingRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                    if ($existingRow) {
                        if (!empty($item['password'])) {
                            $stmtUp = $pdo->prepare("UPDATE users SET data = ?, username = COALESCE(NULLIF(username, ''), ?), password = ?, role = ?, status = ?, updated_at = NOW() WHERE id = ?");
                            $stmtUp->execute([$jsonData, $uName, $hashedPass, $uRole, $uStatus, $existingRow['id']]);
                        } else {
                            $passSql = empty($existingRow['password']) ? ", password = " . $pdo->quote($hashedPass) : "";
                            $stmtUp = $pdo->prepare("UPDATE users SET data = ?, username = COALESCE(NULLIF(username, ''), ?){$passSql}, role = ?, status = ?, updated_at = NOW() WHERE id = ?");
                            $stmtUp->execute([$jsonData, $uName, $uRole, $uStatus, $existingRow['id']]);
                        }
                    } else {
                        $stmtIns = $pdo->prepare("INSERT INTO users (tenant_id, data, username, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmtIns->execute([$tenant_id, $jsonData, $uName, $hashedPass, $uRole, $uStatus]);
                    }
                }
            }
            break;

        // --- 10. Units ---
        case 'delete_unit':
            if ($data && (isset($data['name']) || isset($data['id']))) {
                $unitName = (string)($data['name'] ?? $data['id'] ?? '');

                // Security check
                $usageResult = checkEntityUsageInDatabase($pdo, $tenant_id, 'unit', $unitName, $unitName, $data);
                if ($usageResult['is_used']) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'type' => 'delete_unit',
                        'can_delete' => false,
                        'is_used' => true,
                        'usage_count' => $usageResult['usage_count'],
                        'usages' => $usageResult['usages'],
                        'error' => 'لا يمكن حذف هذه الوحدة نهائياً لأنها مستخدمة في بطاقات أصناف أو فواتير أو سندات مسجلة بالنظام.'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                // Remove unit from global settings
                $stmt = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'erp_global_units_v1'");
                $stmt->execute();
                $cur = json_decode($stmt->fetchColumn() ?: '[]', true) ?: [];
                $newUnits = array_values(array_filter($cur, function($u) use ($unitName) {
                    return trim($u) !== trim($unitName);
                }));
                $stmtUp = $pdo->prepare("UPDATE global_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'erp_global_units_v1'");
                $stmtUp->execute([json_encode($newUnits, JSON_UNESCAPED_UNICODE)]);

                echo json_encode([
                    'success' => true,
                    'type' => 'delete_unit',
                    'can_delete' => true,
                    'is_used' => false,
                    'message' => 'تم حذف الوحدة نهائياً من قاعدة البيانات بنجاح.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            break;

        // --- 11. Cost Centers ---
        case 'delete_cost_center':
            if ($data && (isset($data['id']) || isset($data['name']))) {
                $ccId = (string)($data['id'] ?? '');
                $ccName = (string)($data['name'] ?? '');

                // Security check
                $usageResult = checkEntityUsageInDatabase($pdo, $tenant_id, 'cost_center', $ccId, $ccName, $data);
                if ($usageResult['is_used']) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'type' => 'delete_cost_center',
                        'can_delete' => false,
                        'is_used' => true,
                        'usage_count' => $usageResult['usage_count'],
                        'usages' => $usageResult['usages'],
                        'error' => 'لا يمكن حذف مركز التكلفة هذا نهائياً لأنه مدرج في سندات أو فواتير أو قيود مسجلة بالنظام.'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                // Remove from global settings
                $stmt = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'erp_cost_centers_v1'");
                $stmt->execute();
                $cur = json_decode($stmt->fetchColumn() ?: '[]', true) ?: [];
                $newCC = array_values(array_filter($cur, function($c) use ($ccId, $ccName) {
                    if (is_array($c)) {
                        if ($ccId && ($c['id'] ?? '') === $ccId) return false;
                        if ($ccName && ($c['name'] ?? '') === $ccName) return false;
                    }
                    return true;
                }));
                $stmtUp = $pdo->prepare("UPDATE global_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'erp_cost_centers_v1'");
                $stmtUp->execute([json_encode($newCC, JSON_UNESCAPED_UNICODE)]);

                echo json_encode([
                    'success' => true,
                    'type' => 'delete_cost_center',
                    'can_delete' => true,
                    'is_used' => false,
                    'message' => 'تم حذف مركز التكلفة نهائياً من قاعدة البيانات بنجاح.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            break;

        // --- 12. Categories ---
        case 'delete_category':
            if ($data && (isset($data['name']) || isset($data['id']))) {
                $catName = (string)($data['name'] ?? $data['id'] ?? '');

                // Security check
                $usageResult = checkEntityUsageInDatabase($pdo, $tenant_id, 'category', $catName, $catName, $data);
                if ($usageResult['is_used']) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'type' => 'delete_category',
                        'can_delete' => false,
                        'is_used' => true,
                        'usage_count' => $usageResult['usage_count'],
                        'usages' => $usageResult['usages'],
                        'error' => 'لا يمكن حذف هذه الفئة نهائياً لأنها مرتبطة بأصناف مسجلة بالنظام.'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                // Remove category from global settings
                $stmt = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'erp_categories_v1'");
                $stmt->execute();
                $cur = json_decode($stmt->fetchColumn() ?: '[]', true) ?: [];
                $newCats = array_values(array_filter($cur, function($c) use ($catName) {
                    return trim($c) !== trim($catName);
                }));
                $stmtUp = $pdo->prepare("UPDATE global_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'erp_categories_v1'");
                $stmtUp->execute([json_encode($newCats, JSON_UNESCAPED_UNICODE)]);

                echo json_encode([
                    'success' => true,
                    'type' => 'delete_category',
                    'can_delete' => true,
                    'is_used' => false,
                    'message' => 'تم حذف الفئة نهائياً من قاعدة البيانات بنجاح.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            break;
    }

    echo json_encode(['success' => true, 'type' => $type, 'data' => $data, 'tenant_id' => $tenant_id], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}