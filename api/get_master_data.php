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

try {
    $response = [];

    // 1. Units
    $stmtUnits = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'erp_global_units_v1'");
    $stmtUnits->execute();
    $unitsVal = $stmtUnits->fetchColumn();
    $response['units'] = $unitsVal ? json_decode($unitsVal, true) : [];

    // 2. Categories
    $stmtCats = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'erp_categories_v1'");
    $stmtCats->execute();
    $catsVal = $stmtCats->fetchColumn();
    $response['categories'] = $catsVal ? json_decode($catsVal, true) : ['مواد بناء', 'أدوات صحية', 'كهرباء'];

    // 3. Items (no tenant filter - matches direct-sales.php preload)
    $stmtItems = $pdo->query("SELECT data FROM items WHERE is_deleted = 0 OR is_deleted IS NULL");
    $items = [];
    while ($row = $stmtItems->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['data'])) {
            $d = json_decode($row['data'], true);
            if ($d && empty($d['deleted'])) $items[] = $d;
        }
    }
    $response['items'] = $items;

    // 4. Warehouses
    $stmtWh = $pdo->prepare("SELECT data FROM warehouses WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmtWh->execute([$tenant_id]);
    $whs = [];
    while ($row = $stmtWh->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['data'])) {
            $d = json_decode($row['data'], true);
            if ($d && empty($d['deleted'])) $whs[] = $d;
        }
    }
    $response['warehouses'] = $whs;

    // 5. Customers
    $stmtCust = $pdo->prepare("SELECT data FROM customers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmtCust->execute([$tenant_id]);
    $custs = [];
    while ($row = $stmtCust->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['data'])) {
            $d = json_decode($row['data'], true);
            if ($d && empty($d['deleted'])) $custs[] = $d;
        }
    }
    $response['customers'] = $custs;

    // 6. Suppliers
    $stmtSupp = $pdo->prepare("SELECT data FROM suppliers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmtSupp->execute([$tenant_id]);
    $supps = [];
    while ($row = $stmtSupp->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['data'])) {
            $d = json_decode($row['data'], true);
            if ($d && empty($d['deleted'])) $supps[] = $d;
        }
    }
    $response['suppliers'] = $supps;

    // 7. Banks
    $stmtBanks = $pdo->prepare("SELECT data FROM banks WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmtBanks->execute([$tenant_id]);
    $banks = [];
    while ($row = $stmtBanks->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['data'])) {
            $d = json_decode($row['data'], true);
            if ($d && empty($d['deleted'])) $banks[] = $d;
        }
    }
    $response['banks'] = $banks;

    // 8. Safes
    $stmtSafes = $pdo->prepare("SELECT data FROM safes WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmtSafes->execute([$tenant_id]);
    $safes = [];
    while ($row = $stmtSafes->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['data'])) {
            $d = json_decode($row['data'], true);
            if ($d && empty($d['deleted'])) $safes[] = $d;
        }
    }
    $response['safes'] = $safes;

    // 9. Cost Centers
    $stmtCc = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'erp_cost_centers_v1'");
    $stmtCc->execute();
    $ccVal = $stmtCc->fetchColumn();
    $response['cost_centers'] = $ccVal ? json_decode($ccVal, true) : [];

    // 10. Currency Rates
    $stmtCr = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'erp_currency_rates_v1'");
    $stmtCr->execute();
    $crVal = $stmtCr->fetchColumn();
    $response['currency_rates'] = $crVal ? json_decode($crVal, true) : [];

    echo json_encode(['success' => true, 'data' => $response], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}