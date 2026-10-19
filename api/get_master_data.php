<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../db_bridge.php';
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

try {
    $stmtVerifyTenant = $pdo->prepare('SELECT id FROM tenants WHERE id = ? LIMIT 1');
    $stmtVerifyTenant->execute([$tenant_id]);
    if (!$stmtVerifyTenant->fetchColumn()) {
        $tenant_id = !empty($GLOBAL_TENANT_ID) && (int)$GLOBAL_TENANT_ID > 1 ? (int)$GLOBAL_TENANT_ID : 12;
    }
} catch (Exception $e) {
    if ($tenant_id <= 1) $tenant_id = 12;
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

    // 3. Items (matches direct-sales preload)
    $stmtItems = $pdo->prepare("SELECT data FROM items WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmtItems->execute([$tenant_id]);
    $items = [];
    while ($row = $stmtItems->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['data'])) {
            $d = json_decode($row['data'], true);
            if ($d && empty($d['deleted'])) $items[] = $d;
        }
    }
    $response['items'] = $items;

    // 4. Warehouses
    $stmtWh = $pdo->prepare("SELECT data FROM warehouses WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmtWh->execute([$tenant_id]);
    $whs = [];
    while ($row = $stmtWh->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['data'])) {
            $d = json_decode($row['data'], true);
            if ($d && empty($d['deleted'])) $whs[] = $d;
        }
    }
    $response['warehouses'] = $whs;

    // 5. Customers (with automatic gap-aware receiptCodeNum 1..infinity assignment & statementPin preservation)
    $stmtCust = $pdo->prepare("SELECT id, data FROM customers WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
    $stmtCust->execute([$tenant_id]);
    $custRows = $stmtCust->fetchAll(PDO::FETCH_ASSOC);
    $usedCodeNums = [];
    $usedPins = [];
    $custs = [];
    foreach ($custRows as $row) {
        if (!empty($row['data'])) {
            $d = json_decode($row['data'], true);
            if ($d && empty($d['deleted'])) {
                $codeNum = isset($d['receiptCodeNum']) ? (int)$d['receiptCodeNum'] : 0;
                if ($codeNum >= 1 && !isset($usedCodeNums[$codeNum])) {
                    $usedCodeNums[$codeNum] = true;
                    $d['receiptCodeNum'] = $codeNum;
                } else {
                    $d['receiptCodeNum'] = 0;
                }
                if (empty($d['statementAccessMode']) || !in_array($d['statementAccessMode'], ['disabled', 'balance_only', 'detailed'], true)) {
                    $d['statementAccessMode'] = (isset($d['allowStatementRequest']) && !$d['allowStatementRequest']) ? 'disabled' : 'detailed';
                }
                $d['allowStatementRequest'] = ($d['statementAccessMode'] !== 'disabled');
                $pinStr = isset($d['statementPin']) ? preg_replace('/[^0-9]/', '', trim((string)$d['statementPin'])) : '';
                if ($pinStr !== '' && !isset($usedPins[$pinStr])) {
                    $usedPins[$pinStr] = true;
                    $d['statementPin'] = $pinStr;
                } else {
                    $d['statementPin'] = '';
                }
                $d['_db_row_id'] = (int)$row['id'];
                $custs[] = $d;
            }
        }
    }
    $nextCodeCandidate = 1;
    foreach ($custs as &$cItem) {
        $dbRowId = $cItem['_db_row_id'] ?? 0;
        unset($cItem['_db_row_id']);
        $needsUpdate = false;
        if (empty($cItem['receiptCodeNum']) || $cItem['receiptCodeNum'] < 1) {
            while (isset($usedCodeNums[$nextCodeCandidate])) {
                $nextCodeCandidate++;
            }
            $cItem['receiptCodeNum'] = $nextCodeCandidate;
            $usedCodeNums[$nextCodeCandidate] = true;
            $needsUpdate = true;
        }
        if (empty($cItem['statementPin'])) {
            for ($att = 0; $att < 500; $att++) {
                $candP = (string)random_int(100, 999);
                if (!isset($usedPins[$candP])) {
                    $cItem['statementPin'] = $candP;
                    $usedPins[$candP] = true;
                    $needsUpdate = true;
                    break;
                }
            }
        }
        if ($needsUpdate && $dbRowId > 0) {
            $updC = $pdo->prepare("UPDATE customers SET data = ? WHERE id = ?");
            $updC->execute([json_encode($cItem, JSON_UNESCAPED_UNICODE), $dbRowId]);
        }
    }
    unset($cItem);
    $response['customers'] = $custs;

    // 6. Suppliers (with gap-aware receiptCodeNum & allowStatementRequest / statementAccessMode / statementPin support)
    $stmtSupp = $pdo->prepare("SELECT id, data FROM suppliers WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
    $stmtSupp->execute([$tenant_id]);
    $suppRows = $stmtSupp->fetchAll(PDO::FETCH_ASSOC);
    $supps = [];
    foreach ($suppRows as $row) {
        if (!empty($row['data'])) {
            $d = json_decode($row['data'], true);
            if ($d && empty($d['deleted'])) {
                $codeNum = isset($d['receiptCodeNum']) ? (int)$d['receiptCodeNum'] : 0;
                if ($codeNum >= 1 && !isset($usedCodeNums[$codeNum])) {
                    $usedCodeNums[$codeNum] = true;
                    $d['receiptCodeNum'] = $codeNum;
                } else {
                    $d['receiptCodeNum'] = 0;
                }
                if (empty($d['statementAccessMode']) || !in_array($d['statementAccessMode'], ['disabled', 'balance_only', 'detailed'], true)) {
                    $d['statementAccessMode'] = (isset($d['allowStatementRequest']) && !$d['allowStatementRequest']) ? 'disabled' : 'detailed';
                }
                $d['allowStatementRequest'] = ($d['statementAccessMode'] !== 'disabled');
                $pinStr = isset($d['statementPin']) ? preg_replace('/[^0-9]/', '', trim((string)$d['statementPin'])) : '';
                if ($pinStr !== '' && !isset($usedPins[$pinStr])) {
                    $usedPins[$pinStr] = true;
                    $d['statementPin'] = $pinStr;
                } else {
                    $d['statementPin'] = '';
                }
                $d['_db_row_id'] = (int)$row['id'];
                $supps[] = $d;
            }
        }
    }
    foreach ($supps as &$sItem) {
        $dbRowId = $sItem['_db_row_id'] ?? 0;
        unset($sItem['_db_row_id']);
        $needsUpdate = false;
        if (empty($sItem['receiptCodeNum']) || $sItem['receiptCodeNum'] < 1) {
            while (isset($usedCodeNums[$nextCodeCandidate])) {
                $nextCodeCandidate++;
            }
            $sItem['receiptCodeNum'] = $nextCodeCandidate;
            $usedCodeNums[$nextCodeCandidate] = true;
            $needsUpdate = true;
        }
        if (empty($sItem['statementPin'])) {
            for ($att = 0; $att < 500; $att++) {
                $candP = (string)random_int(100, 999);
                if (!isset($usedPins[$candP])) {
                    $sItem['statementPin'] = $candP;
                    $usedPins[$candP] = true;
                    $needsUpdate = true;
                    break;
                }
            }
        }
        if ($needsUpdate && $dbRowId > 0) {
            $updS = $pdo->prepare("UPDATE suppliers SET data = ? WHERE id = ?");
            $updS->execute([json_encode($sItem, JSON_UNESCAPED_UNICODE), $dbRowId]);
        }
    }
    unset($sItem);
    $response['suppliers'] = $supps;

    // 6b. WhatsApp Statement Admin Policy
    $defaultPolicy = [
        'masterMode' => 'per_account', // 'per_account' | 'force_detailed_all' | 'force_balance_only_all' | 'force_disabled_all'
        'phoneVerification' => 'phone_or_pin', // 'any_phone_with_code' | 'registered_if_set' | 'strict_registered_only' | 'phone_or_pin' | 'pin_always'
        'statementScope' => 'all', // 'all' | 'last_20' | 'last_50' | 'current_month'
        'voucherCompanyName' => '',
        'voucherStampText' => 'ختم مصادقة مالية إلكترونية',
        'voucherStampImage' => '',
        'includeInvoiceItems' => true,
        'notifyAdminOnRequest' => true
    ];
    try {
        $stmtPol = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'erp_whatsapp_statement_policy_v1' LIMIT 1");
        $stmtPol->execute();
        $polVal = $stmtPol->fetchColumn();
        $parsedPol = $polVal ? json_decode($polVal, true) : null;
        $mergedPol = is_array($parsedPol) ? array_merge($defaultPolicy, $parsedPol) : $defaultPolicy;
        if (empty($mergedPol['voucherCompanyName'])) {
            try {
                $stmtSet = $pdo->prepare("SELECT data FROM settings WHERE (tenant_id = ? OR tenant_id IS NULL) ORDER BY id DESC LIMIT 1");
                $stmtSet->execute([$tenant_id]);
                $rawSet = $stmtSet->fetchColumn();
                if ($rawSet) {
                    $setDec = json_decode((string)$rawSet, true);
                    if (is_array($setDec) && !empty($setDec['companyName'])) {
                        $mergedPol['voucherCompanyName'] = trim((string)$setDec['companyName']);
                    }
                }
            } catch (Exception $e) {}
        }
        $response['whatsapp_statement_policy'] = $mergedPol;
    } catch (Exception $e) {
        $response['whatsapp_statement_policy'] = $defaultPolicy;
    }

    // 7. Banks
    $stmtBanks = $pdo->prepare("SELECT data FROM banks WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
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
    $stmtSafes = $pdo->prepare("SELECT data FROM safes WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
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
    $stmtCc = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key IN ('erp_cost_centers_v1', 'erp_jobs_v1') ORDER BY FIELD(setting_key, 'erp_cost_centers_v1', 'erp_jobs_v1') LIMIT 1");
    $stmtCc->execute();
    $ccVal = $stmtCc->fetchColumn();
    $response['cost_centers'] = $ccVal ? json_decode($ccVal, true) : [];

    // 10. Currency Rates
    $stmtCr = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'erp_currency_rates_v1'");
    $stmtCr->execute();
    $crVal = $stmtCr->fetchColumn();
    $response['currency_rates'] = $crVal ? json_decode($crVal, true) : [];

    // 11. Employees / Users
    $stmtUsers = $pdo->prepare("SELECT data, username, search_name, role, status, frontend_id, id FROM users WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmtUsers->execute([$tenant_id]);
    $users = [];
    while ($row = $stmtUsers->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['data'])) {
            $d = json_decode($row['data'], true);
            if ($d && empty($d['deleted'])) {
                if (empty($d['name'])) $d['name'] = $row['search_name'] ?: $row['username'];
                if (empty($d['id'])) $d['id'] = $row['frontend_id'] ?: ('EMP-' . $row['id']);
                $users[] = $d;
            }
        } else {
            $users[] = [
                'id' => $row['frontend_id'] ?: ('EMP-' . $row['id']),
                'name' => $row['search_name'] ?: $row['username'],
                'username' => $row['username'],
                'role' => $row['role'] ?: 'cashier',
                'status' => $row['status'] ?: 'active',
                'securityLevel' => 1
            ];
        }
    }
    $response['employees'] = $users;

    // 12. Accounts (General Ledger / Chart of Accounts)
    try {
        $stmtAccs = $pdo->prepare("SELECT data FROM accounts WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
        $stmtAccs->execute([$tenant_id]);
        $accsList = [];
        while ($row = $stmtAccs->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['data'])) {
                $d = json_decode($row['data'], true);
                if ($d && empty($d['deleted'])) $accsList[] = $d;
            }
        }
        $response['accounts'] = $accsList;
    } catch(Exception $ex) {
        $response['accounts'] = [];
    }

    echo json_encode([
        'success' => true,
        'status' => 'success',
        'tenant_id' => $tenant_id,
        'customers' => $response['customers'] ?? [],
        'suppliers' => $response['suppliers'] ?? [],
        'data' => $response
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}