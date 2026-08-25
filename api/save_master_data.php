<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$tenant_id = 1;
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);
if (!empty($token)) {
    $stmtAuth = $pdo->prepare('SELECT tenant_id FROM users WHERE token = ? LIMIT 1');
    $stmtAuth->execute([$token]);
    $t = $stmtAuth->fetchColumn();
    if ($t) { $tenant_id = (int)$t; }
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['type'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid payload']);
    exit;
}

$type = $input['type'];
$data = $input['data'] ?? null;

try {
    switch ($type) {
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
            }
            break;

        case 'currency_rates':
            if (is_array($data)) {
                $crJson = json_encode($data, JSON_UNESCAPED_UNICODE);
                $stmt = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('erp_currency_rates_v1', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
                $stmt->execute([$crJson]);
            }
            break;

        case 'save_item':
            if ($data && isset($data['id'])) {
                $itemId = (string)$data['id'];
                $itemName = $data['name'] ?? '';
                $barcode = $data['barcode'] ?? '';
                $category = $data['category'] ?? '';
                $status = $data['status'] ?? 'active';
                $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);

                $stmtCheck = $pdo->prepare("SELECT id FROM items WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                $stmtCheck->execute([$tenant_id, $itemId, $itemId]);
                $existingId = $stmtCheck->fetchColumn();

                if ($existingId) {
                    $stmtUp = $pdo->prepare("UPDATE items SET data = ?, search_name = ?, search_barcode = ?, category_val = ?, status_val = ?, is_deleted = 0, updated_at = NOW() WHERE id = ?");
                    $stmtUp->execute([$jsonData, $itemName, $barcode, $category, $status, $existingId]);
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO items (tenant_id, frontend_id, data, search_name, search_barcode, category_val, status_val, is_deleted) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
                    $stmtIns->execute([$tenant_id, $itemId, $jsonData, $itemName, $barcode, $category, $status]);
                }
            }
            break;

        case 'delete_item':
            if ($data && isset($data['id'])) {
                $itemId = (string)$data['id'];
                $stmtDel = $pdo->prepare("UPDATE items SET is_deleted = 1, updated_at = NOW() WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                $stmtDel->execute([$tenant_id, $itemId, $itemId]);
            }
            break;

        case 'items_bulk':
            if (is_array($data)) {
                foreach ($data as $item) {
                    if (!$item || !isset($item['id'])) continue;
                    $itemId = (string)$item['id'];
                    $itemName = $item['name'] ?? '';
                    $barcode = $item['barcode'] ?? '';
                    $category = $item['category'] ?? '';
                    $status = $item['status'] ?? 'active';
                    $isDeleted = !empty($item['deleted']) ? 1 : 0;
                    $jsonData = json_encode($item, JSON_UNESCAPED_UNICODE);

                    $stmtCheck = $pdo->prepare("SELECT id FROM items WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                    $stmtCheck->execute([$tenant_id, $itemId, $itemId]);
                    $existingId = $stmtCheck->fetchColumn();

                    if ($existingId) {
                        $stmtUp = $pdo->prepare("UPDATE items SET data = ?, search_name = ?, search_barcode = ?, category_val = ?, status_val = ?, is_deleted = ?, updated_at = NOW() WHERE id = ?");
                        $stmtUp->execute([$jsonData, $itemName, $barcode, $category, $status, $isDeleted, $existingId]);
                    } else {
                        $stmtIns = $pdo->prepare("INSERT INTO items (tenant_id, frontend_id, data, search_name, search_barcode, category_val, status_val, is_deleted) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmtIns->execute([$tenant_id, $itemId, $jsonData, $itemName, $barcode, $category, $status, $isDeleted]);
                    }
                }
            }
            break;

        case 'save_warehouse':
            if ($data && isset($data['id'])) {
                $whId = (string)$data['id'];
                $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
                $stmtCheck = $pdo->prepare("SELECT id FROM warehouses WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                $stmtCheck->execute([$tenant_id, $whId, $whId]);
                $existingId = $stmtCheck->fetchColumn();
                if ($existingId) {
                    $stmtUp = $pdo->prepare("UPDATE warehouses SET data = ?, is_deleted = 0, updated_at = NOW() WHERE id = ?");
                    $stmtUp->execute([$jsonData, $existingId]);
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO warehouses (tenant_id, frontend_id, data, is_deleted) VALUES (?, ?, ?, 0)");
                    $stmtIns->execute([$tenant_id, $whId, $jsonData]);
                }
            }
            break;

        case 'delete_warehouse':
            if ($data && isset($data['id'])) {
                $whId = (string)$data['id'];
                $stmtDel = $pdo->prepare("UPDATE warehouses SET is_deleted = 1, updated_at = NOW() WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                $stmtDel->execute([$tenant_id, $whId, $whId]);
            }
            break;

        case 'save_customer':
            if ($data && isset($data['id'])) {
                $cId = (string)$data['id'];
                $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
                $stmtCheck = $pdo->prepare("SELECT id FROM customers WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                $stmtCheck->execute([$tenant_id, $cId, $cId]);
                $existingId = $stmtCheck->fetchColumn();
                if ($existingId) {
                    $stmtUp = $pdo->prepare("UPDATE customers SET data = ?, is_deleted = 0, updated_at = NOW() WHERE id = ?");
                    $stmtUp->execute([$jsonData, $existingId]);
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO customers (tenant_id, frontend_id, data, is_deleted) VALUES (?, ?, ?, 0)");
                    $stmtIns->execute([$tenant_id, $cId, $jsonData]);
                }
            }
            break;

        case 'delete_customer':
            if ($data && isset($data['id'])) {
                $cId = (string)$data['id'];
                $stmtDel = $pdo->prepare("UPDATE customers SET is_deleted = 1, updated_at = NOW() WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                $stmtDel->execute([$tenant_id, $cId, $cId]);
            }
            break;

        case 'save_supplier':
            if ($data && isset($data['id'])) {
                $sId = (string)$data['id'];
                $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
                $stmtCheck = $pdo->prepare("SELECT id FROM suppliers WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                $stmtCheck->execute([$tenant_id, $sId, $sId]);
                $existingId = $stmtCheck->fetchColumn();
                if ($existingId) {
                    $stmtUp = $pdo->prepare("UPDATE suppliers SET data = ?, is_deleted = 0, updated_at = NOW() WHERE id = ?");
                    $stmtUp->execute([$jsonData, $existingId]);
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO suppliers (tenant_id, frontend_id, data, is_deleted) VALUES (?, ?, ?, 0)");
                    $stmtIns->execute([$tenant_id, $sId, $jsonData]);
                }
            }
            break;

        case 'delete_supplier':
            if ($data && isset($data['id'])) {
                $sId = (string)$data['id'];
                $stmtDel = $pdo->prepare("UPDATE suppliers SET is_deleted = 1, updated_at = NOW() WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                $stmtDel->execute([$tenant_id, $sId, $sId]);
            }
            break;

        case 'save_bank':
            if ($data && isset($data['id'])) {
                $bId = (string)$data['id'];
                $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
                $stmtCheck = $pdo->prepare("SELECT id FROM banks WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                $stmtCheck->execute([$tenant_id, $bId, $bId]);
                $existingId = $stmtCheck->fetchColumn();
                if ($existingId) {
                    $stmtUp = $pdo->prepare("UPDATE banks SET data = ?, is_deleted = 0, updated_at = NOW() WHERE id = ?");
                    $stmtUp->execute([$jsonData, $existingId]);
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO banks (tenant_id, frontend_id, data, is_deleted) VALUES (?, ?, ?, 0)");
                    $stmtIns->execute([$tenant_id, $bId, $jsonData]);
                }
            }
            break;

        case 'delete_bank':
            if ($data && isset($data['id'])) {
                $bId = (string)$data['id'];
                $stmtDel = $pdo->prepare("UPDATE banks SET is_deleted = 1, updated_at = NOW() WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                $stmtDel->execute([$tenant_id, $bId, $bId]);
            }
            break;

        case 'save_safe':
            if ($data && isset($data['id'])) {
                $sId = (string)$data['id'];
                $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
                $stmtCheck = $pdo->prepare("SELECT id FROM safes WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
                $stmtCheck->execute([$tenant_id, $sId, $sId]);
                $existingId = $stmtCheck->fetchColumn();
                if ($existingId) {
                    $stmtUp = $pdo->prepare("UPDATE safes SET data = ?, is_deleted = 0, updated_at = NOW() WHERE id = ?");
                    $stmtUp->execute([$jsonData, $existingId]);
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO safes (tenant_id, frontend_id, data, is_deleted) VALUES (?, ?, ?, 0)");
                    $stmtIns->execute([$tenant_id, $sId, $jsonData]);
                }
            }
            break;

        case 'delete_safe':
            if ($data && isset($data['id'])) {
                $sId = (string)$data['id'];
                $stmtDel = $pdo->prepare("UPDATE safes SET is_deleted = 1, updated_at = NOW() WHERE tenant_id = ? AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                $stmtDel->execute([$tenant_id, $sId, $sId]);
            }
            break;
    }

    echo json_encode(['success' => true, 'type' => $type], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}