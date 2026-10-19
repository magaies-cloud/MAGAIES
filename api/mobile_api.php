<?php
/**
 * ═════════════════════════════════════════════════════════════════════
 * 📱 MAGAIEES ERP 2026 - UNIFIED REAL-TIME MOBILE DATA ENGINE
 * ═════════════════════════════════════════════════════════════════════
 * Official Mobile API Driver for Magaiees ERP (مقاييس):
 * - Live real data bridge between Mobile PWA and Computer MariaDB/MySQL
 * - Customers, Suppliers, Banks, Visa cards, Safes, Items, Units
 * - Real-time financial balances, KPIs, transaction recording & sync
 */

if (file_exists(__DIR__ . '/db.php')) {
    require_once __DIR__ . '/db.php';
} elseif (file_exists(__DIR__ . '/../ELSAJANA2/magaies/api/db.php')) {
    require_once __DIR__ . '/../ELSAJANA2/magaies/api/db.php';
} elseif (file_exists(dirname(__DIR__) . '/ELSAJANA2/magaies/api/db.php')) {
    require_once dirname(__DIR__) . '/ELSAJANA2/magaies/api/db.php';
} else {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=magaees;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
}

if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Tenant-Id');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
    ob_start('ob_gzhandler');
}

// 1. Determine Tenant ID with smart fallback
$tenant_id = null;
if (!empty($_SERVER['HTTP_X_TENANT_ID'])) {
    $tenant_id = (int)$_SERVER['HTTP_X_TENANT_ID'];
} elseif (!empty($_GET['tenant_id'])) {
    $tenant_id = (int)$_GET['tenant_id'];
} elseif (!empty($_COOKIE['currentTenantId'])) {
    $tenant_id = (int)$_COOKIE['currentTenantId'];
}

// Auth Token Check
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);
if (!empty($token) && empty($tenant_id)) {
    try {
        $stmtAuth = $pdo->prepare('SELECT tenant_id FROM users WHERE token = ? LIMIT 1');
        $stmtAuth->execute([$token]);
        $t = $stmtAuth->fetchColumn();
        if ($t) { $tenant_id = (int)$t; }
    } catch (Exception $e) {}
}

// Smart Tenant Fallback: Use unified db_bridge tenant resolution (default 12)
if (empty($tenant_id)) {
    try {
        $bestTenant = $pdo->query("SELECT tenant_id, COUNT(*) as cnt FROM sales_invoices WHERE (is_deleted = 0 OR is_deleted IS NULL) GROUP BY tenant_id ORDER BY cnt DESC LIMIT 1")->fetchColumn();
        $tenant_id = $bestTenant ? (int)$bestTenant : 12;
    } catch (Exception $e) {
        $tenant_id = 12;
    }
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
if (empty($action) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = json_decode(file_get_contents('php://input'), true);
    if (!empty($rawInput['action'])) {
        $action = $rawInput['action'];
    }
}

try {
    switch ($action) {

        // ═════════════════════════════════════════════════════════════════════
        // ACTION 1: GET COMPLETE REAL MOBILE DATA BINDING
        // ═════════════════════════════════════════════════════════════════════
        case 'get_full_mobile_data':
        default:
            $currencyRateUSD = 5999.0; // Default SDG per USD fallback
            try {
                $tKey = 'tenant_rate_' . intval($tenant_id);
                $stmtCr = $pdo->prepare("SELECT setting_key, setting_value FROM global_settings WHERE setting_key IN ('exchange_rate', 'erp_rate_usd', 'erp_currency_rates_v1', ?)");
                $stmtCr->execute([$tKey]);
                $rowsCr = $stmtCr->fetchAll(PDO::FETCH_KEY_PAIR);
                $resolvedRate = 0;
                if (!empty($rowsCr['exchange_rate'])) {
                    $val = trim((string)$rowsCr['exchange_rate']);
                    if (is_numeric($val) && (float)$val > 1) {
                        $resolvedRate = (float)$val;
                    } else {
                        $dec = json_decode($val, true);
                        if (is_array($dec)) {
                            $r = (float)($dec['rate'] ?? ($dec['current'] ?? ($dec['rateUSD'] ?? ($dec['USD'] ?? 0))));
                            if ($r > 1) $resolvedRate = $r;
                        }
                    }
                }
                if ($resolvedRate <= 1 && !empty($rowsCr[$tKey])) {
                    $valT = trim((string)$rowsCr[$tKey]);
                    if (is_numeric($valT) && (float)$valT > 1) {
                        $resolvedRate = (float)$valT;
                    } else {
                        $decT = json_decode($valT, true);
                        if (is_array($decT)) {
                            $r = (float)($decT['current'] ?? ($decT['rate'] ?? ($decT['rateUSD'] ?? 0)));
                            if ($r > 1) $resolvedRate = $r;
                        }
                    }
                }
                if ($resolvedRate <= 1 && !empty($rowsCr['erp_rate_usd']) && is_numeric($rowsCr['erp_rate_usd']) && (float)$rowsCr['erp_rate_usd'] > 1) {
                    $resolvedRate = (float)$rowsCr['erp_rate_usd'];
                }
                if ($resolvedRate <= 1 && !empty($rowsCr['erp_currency_rates_v1'])) {
                    $rates = json_decode($rowsCr['erp_currency_rates_v1'], true);
                    if (!empty($rates['USD']) && (float)$rates['USD'] > 1) $resolvedRate = (float)$rates['USD'];
                }
                if ($resolvedRate > 1) {
                    $currencyRateUSD = $resolvedRate;
                }
            } catch (Exception $e) {}

            // 1. Units (وحدات القياس الحقيقية)
            $units = ['طن', 'سيخة', 'كيلو', 'شوال', 'كرتونة', 'حبة', 'قطعة', 'متر', 'لتر', 'شيلة', 'طرد', 'برميل'];
            try {
                $stmtUnits = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'erp_global_units_v1'");
                $stmtUnits->execute();
                $uVal = $stmtUnits->fetchColumn();
                if ($uVal) {
                    $decodedUnits = json_decode($uVal, true);
                    if (is_array($decodedUnits) && count($decodedUnits) > 0) {
                        $units = array_values(array_unique(array_merge($units, $decodedUnits)));
                    }
                }
            } catch (Exception $e) {}

            // 2. Real Customers (العملاء الحقيقيون)
            $customers = [];
            $totalReceivables = 0;
            $stmtCust = $pdo->prepare("SELECT id, frontend_id, search_name, search_phone, data FROM customers WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC");
            $stmtCust->execute([$tenant_id]);
            while ($row = $stmtCust->fetch(PDO::FETCH_ASSOC)) {
                $d = !empty($row['data']) ? json_decode($row['data'], true) : [];
                $name = $d['name'] ?? ($row['search_name'] ?? ('عميل #' . $row['id']));
                $phone = $d['phone'] ?? ($row['search_phone'] ?? '');
                $bal = isset($d['currentBalance']) ? (float)$d['currentBalance'] : (float)($d['openingBalance'] ?? 0);
                $currency = $d['currency'] ?? 'SDG';

                if ($bal > 0) {
                    $totalReceivables += ($currency === 'USD') ? ($bal * $currencyRateUSD) : $bal;
                }

                $nature = ($bal > 0) ? 'مدين (عليه دين لنا)' : (($bal < 0) ? 'دائن (له مستحقات)' : 'مسدد (رصيد صفر)');
                $natureClass = ($bal > 0) ? 'text-rose-400 bg-rose-950/60 border-rose-500/30' : (($bal < 0) ? 'text-cyan-400 bg-cyan-950/60 border-cyan-500/30' : 'text-slate-400 bg-slate-900 border-slate-700/40');

                $customers[] = [
                    'id' => $row['frontend_id'] ?: ('CUST-' . $row['id']),
                    'numericId' => (int)$row['id'],
                    'name' => $name,
                    'phone' => $phone,
                    'category' => 'customer',
                    'type' => 'عميل',
                    'currency' => $currency,
                    'balanceNum' => $bal,
                    'balanceFormatted' => ($currency === 'USD') ? ('$' . number_format($bal, 2)) : (number_format($bal) . ' ج.س'),
                    'sdgFormatted' => number_format(($currency === 'USD') ? ($bal * $currencyRateUSD) : $bal) . ' ج.س',
                    'nature' => $nature,
                    'natureClass' => $natureClass,
                    'creditLimit' => (float)($d['creditLimit'] ?? 0),
                    'bankAccounts' => $d['bankAccounts'] ?? []
                ];
            }

            // 3. Real Suppliers (الموردون الحقيقيون)
            $suppliers = [];
            $totalPayables = 0;
            $stmtSupp = $pdo->prepare("SELECT id, frontend_id, search_name, search_phone, data FROM suppliers WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC");
            $stmtSupp->execute([$tenant_id]);
            while ($row = $stmtSupp->fetch(PDO::FETCH_ASSOC)) {
                $d = !empty($row['data']) ? json_decode($row['data'], true) : [];
                $name = $d['name'] ?? ($row['search_name'] ?? ('مورد #' . $row['id']));
                $phone = $d['phone'] ?? ($row['search_phone'] ?? '');
                $bal = isset($d['currentBalance']) ? (float)$d['currentBalance'] : (float)($d['openingBalance'] ?? 0);
                $currency = $d['currency'] ?? 'SDG';

                if ($bal > 0) {
                    $totalPayables += ($currency === 'USD') ? ($bal * $currencyRateUSD) : $bal;
                }

                $nature = ($bal > 0) ? 'دائن (له مستحقات علينا)' : (($bal < 0) ? 'مدين (مسترجع لنا)' : 'مسدد (رصيد صفر)');
                $natureClass = ($bal > 0) ? 'text-amber-400 bg-amber-950/60 border-amber-500/30' : 'text-slate-400 bg-slate-900 border-slate-700/40';

                $suppliers[] = [
                    'id' => $row['frontend_id'] ?: ('SUP-' . $row['id']),
                    'numericId' => 1000 + (int)$row['id'],
                    'name' => $name,
                    'phone' => $phone,
                    'category' => 'vendor',
                    'type' => 'مورد',
                    'currency' => $currency,
                    'balanceNum' => $bal,
                    'balanceFormatted' => ($currency === 'USD') ? ('$' . number_format($bal, 2)) : (number_format($bal) . ' ج.س'),
                    'sdgFormatted' => number_format(($currency === 'USD') ? ($bal * $currencyRateUSD) : $bal) . ' ج.س',
                    'nature' => $nature,
                    'natureClass' => $natureClass
                ];
            }

            // 4. Real Banks & Visa/Cards (البنوك والفيزا والمصارف)
            $banks = [];
            $totalBankBalance = 0;
            $stmtBanks = $pdo->prepare("SELECT id, frontend_id, data FROM banks WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
            $stmtBanks->execute([$tenant_id]);
            while ($row = $stmtBanks->fetch(PDO::FETCH_ASSOC)) {
                $d = !empty($row['data']) ? json_decode($row['data'], true) : [];
                $name = $d['name'] ?? ('حساب مصرفي #' . $row['id']);
                $bal = isset($d['currentBalance']) ? (float)$d['currentBalance'] : (float)($d['openingBalance'] ?? 0);
                $currency = $d['currency'] ?? 'SDG';
                $type = $d['type'] ?? 'bank'; // bank / visa / card

                $totalBankBalance += ($currency === 'USD') ? ($bal * $currencyRateUSD) : $bal;

                $banks[] = [
                    'id' => $row['frontend_id'] ?: ('BANK-' . $row['id']),
                    'numericId' => 2000 + (int)$row['id'],
                    'name' => $name,
                    'branch' => $d['branch'] ?? '',
                    'iban' => $d['iban'] ?? ($d['accountNo'] ?? ''),
                    'category' => 'cash',
                    'type' => (stripos($name, 'فيزا') !== false || stripos($name, 'visa') !== false || $type === 'visa') ? 'فيزا' : 'بنك',
                    'currency' => $currency,
                    'balanceNum' => $bal,
                    'balanceFormatted' => ($currency === 'USD') ? ('$' . number_format($bal, 2)) : (number_format($bal) . ' ج.س'),
                    'sdgFormatted' => number_format(($currency === 'USD') ? ($bal * $currencyRateUSD) : $bal) . ' ج.س',
                    'nature' => 'رصيد بنكي متاح',
                    'natureClass' => 'text-emerald-400 bg-emerald-950/60 border-emerald-500/30'
                ];
            }

            // 5. Real Cash Safes (الخزن النقدية ونقاط البيع)
            $safes = [];
            $totalSafeBalance = 0;
            $stmtSafes = $pdo->prepare("SELECT id, frontend_id, data FROM safes WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
            $stmtSafes->execute([$tenant_id]);
            while ($row = $stmtSafes->fetch(PDO::FETCH_ASSOC)) {
                $d = !empty($row['data']) ? json_decode($row['data'], true) : [];
                $name = $d['name'] ?? ('خزينة #' . $row['id']);
                $bal = isset($d['currentBalance']) ? (float)$d['currentBalance'] : (float)($d['openingBalance'] ?? 0);
                $currency = $d['currency'] ?? 'SDG';

                $totalSafeBalance += ($currency === 'USD') ? ($bal * $currencyRateUSD) : $bal;

                $safes[] = [
                    'id' => $row['frontend_id'] ?: ('SAFE-' . $row['id']),
                    'numericId' => 3000 + (int)$row['id'],
                    'name' => $name,
                    'responsible' => $d['responsible'] ?? '',
                    'category' => 'cash',
                    'type' => 'خزينة',
                    'currency' => $currency,
                    'balanceNum' => $bal,
                    'balanceFormatted' => ($currency === 'USD') ? ('$' . number_format($bal, 2)) : (number_format($bal) . ' ج.س'),
                    'sdgFormatted' => number_format(($currency === 'USD') ? ($bal * $currencyRateUSD) : $bal) . ' ج.س',
                    'nature' => 'نقدية بالصندوق',
                    'natureClass' => 'text-amber-400 bg-amber-950/60 border-amber-500/30'
                ];
            }

            // 6. Real Items & Products (الأصناف والأسعار والمخزون)
            $items = [];
            $totalInventoryQty = 0;
            $stmtItems = $pdo->prepare("SELECT id, frontend_id, search_name, search_barcode, category_val, data FROM items WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC");
            $stmtItems->execute([$tenant_id]);
            while ($row = $stmtItems->fetch(PDO::FETCH_ASSOC)) {
                $d = !empty($row['data']) ? json_decode($row['data'], true) : [];
                $name = $d['name'] ?? ($row['search_name'] ?? ('صنف #' . $row['id']));
                $unit = $d['unit'] ?? ($d['baseUnit'] ?? 'قطعة');
                $price = (float)($d['price'] ?? ($d['cashPrice'] ?? 0));
                
                // Calculate stock count
                $stock = 0;
                if (isset($d['stock'])) {
                    if (is_numeric($d['stock'])) {
                        $stock = (float)$d['stock'];
                    } elseif (is_array($d['stock'])) {
                        foreach ($d['stock'] as $wStock) {
                            $stock += (float)$wStock;
                        }
                    }
                }
                $totalInventoryQty += $stock;

                $unitsArr = $d['unitsArray'] ?? ($d['units'] ?? []);
                if (empty($unitsArr) && !empty($unit)) {
                    $unitsArr = [['name' => $unit, 'unitName' => $unit, 'factor' => 1, 'cashPrice' => $price]];
                }

                $items[] = [
                    'id' => $row['frontend_id'] ?: ('ITEM-' . $row['id']),
                    'name' => $name,
                    'barcode' => $d['barcode'] ?? ($row['search_barcode'] ?? ''),
                    'unit' => $unit,
                    'units' => $unitsArr,
                    'price' => $price,
                    'priceFormatted' => number_format($price) . ' ج.س',
                    'stock' => $stock,
                    'stockFormatted' => number_format($stock) . ' ' . $unit,
                    'category' => $d['category'] ?? ($row['category_val'] ?? 'عام')
                ];
            }

            // 7. Real Sales Invoices & Recent Transactions
            $salesCount = 0;
            $salesTotalSum = 0;
            $recentTransactions = [];

            try {
                $stmtSales = $pdo->prepare("SELECT COUNT(*) as cnt FROM sales_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmtSales->execute([$tenant_id]);
                $salesCount = (int)$stmtSales->fetchColumn();

                $stmtRecent = $pdo->prepare("SELECT id, invoice_no, total_val, data, created_at FROM sales_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 15");
                $stmtRecent->execute([$tenant_id]);
                while ($inv = $stmtRecent->fetch(PDO::FETCH_ASSOC)) {
                    $invData = !empty($inv['data']) ? json_decode($inv['data'], true) : [];
                    $total = (float)($inv['total_val'] ?? ($invData['total'] ?? ($invData['grand_total'] ?? 0)));
                    $salesTotalSum += $total;
                    $custName = $invData['customerName'] ?? ($invData['customer_name'] ?? 'عميل نقدي');
                    $invNo = $inv['invoice_no'] ?? ($invData['invoiceNo'] ?? ('INV-' . $inv['id']));
                    
                    $recentTransactions[] = [
                        'id' => $invNo,
                        'type' => 'فاتورة مبيعات',
                        'customer' => $custName,
                        'title' => "مبيعات: $custName",
                        'subtitle' => "فاتورة رقم #$invNo",
                        'amountSdg' => number_format($total) . ' ج.س',
                        'amountUsd' => '$' . number_format($total / max(1, $currencyRateUSD), 2),
                        'date' => date('Y-m-d H:i', strtotime($inv['created_at'])),
                        'badge' => 'مبيعات',
                        'color' => 'cyan'
                    ];
                }
            } catch (Exception $e) {}

            // Calculate total liquidity in SDG and USD
            $totalLiquiditySdg = $totalBankBalance + $totalSafeBalance;
            $totalLiquidityUsd = round($totalLiquiditySdg / max(1, $currencyRateUSD), 2);

            // Synthesize Master Accounts List for unified dropdowns & statement selections
            $masterAccountsList = [];
            foreach ($banks as $b) { $masterAccountsList[] = $b; }
            foreach ($safes as $s) { $masterAccountsList[] = $s; }
            foreach ($customers as $c) { $masterAccountsList[] = $c; }
            foreach ($suppliers as $sp) { $masterAccountsList[] = $sp; }

            echo json_encode([
                'success' => true,
                'tenant_id' => $tenant_id,
                'currency_rate_usd' => $currencyRateUSD,
                'kpis' => [
                    'total_liquidity_sdg' => number_format($totalLiquiditySdg) . ' ج.س',
                    'total_liquidity_usd' => '$' . number_format($totalLiquidityUsd, 2),
                    'total_bank_balance_sdg' => number_format($totalBankBalance) . ' ج.س',
                    'total_safe_balance_sdg' => number_format($totalSafeBalance) . ' ج.س',
                    'total_receivables_sdg' => number_format($totalReceivables) . ' ج.س',
                    'total_receivables_usd' => '$' . number_format($totalReceivables / max(1, $currencyRateUSD), 2),
                    'total_payables_sdg' => number_format($totalPayables) . ' ج.س',
                    'total_payables_usd' => '$' . number_format($totalPayables / max(1, $currencyRateUSD), 2),
                    'total_customers_count' => count($customers),
                    'total_suppliers_count' => count($suppliers),
                    'total_banks_count' => count($banks),
                    'total_safes_count' => count($safes),
                    'total_items_count' => count($items),
                    'total_inventory_qty' => $totalInventoryQty,
                    'total_sales_count' => $salesCount,
                    'total_sales_amount_sdg' => number_format($salesTotalSum) . ' ج.س'
                ],
                'units' => $units,
                'banks' => $banks,
                'safes' => $safes,
                'customers' => $customers,
                'suppliers' => $suppliers,
                'items' => $items,
                'master_accounts' => $masterAccountsList,
                'recent_transactions' => $recentTransactions,
                'server_time' => date('Y-m-d H:i:s')
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════════════════
        // ACTION 2: ADD NEW CUSTOMER OR SUPPLIER DIRECTLY INTO DATABASE
        // ═════════════════════════════════════════════════════════════════════
        case 'add_partner':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) { $input = $_POST; }

            $partnerType = $input['type'] ?? 'customer'; // customer or vendor
            $name = trim($input['name'] ?? '');
            $phone = trim($input['phone'] ?? '');
            $balance = (float)($input['openingBalance'] ?? 0);
            $currency = $input['currency'] ?? 'SDG';

            if (empty($name)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'اسم الحساب مطلوب'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $table = ($partnerType === 'vendor' || $partnerType === 'supplier') ? 'suppliers' : 'customers';
            $prefix = ($table === 'suppliers') ? 'SUP-' : 'CUST-';
            $frontendId = $prefix . time() . rand(100, 999);

            $partnerData = [
                'id' => $frontendId,
                'name' => $name,
                'phone' => $phone,
                'whatsapp' => $phone,
                'openingBalance' => $balance,
                'currentBalance' => $balance,
                'currency' => $currency,
                'status' => 'active',
                'createdAt' => date('Y-m-d H:i:s'),
                'notes' => 'تمت الإضافة عبر تطبيق الجوال'
            ];

            $jsonData = json_encode($partnerData, JSON_UNESCAPED_UNICODE);
            $stmt = $pdo->prepare("INSERT INTO `$table` (tenant_id, frontend_id, search_name, search_phone, data, is_deleted) VALUES (?, ?, ?, ?, ?, 0)");
            $stmt->execute([$tenant_id, $frontendId, $name, $phone, $jsonData]);

            echo json_encode([
                'success' => true,
                'message' => 'تم حفظ الحساب بنجاح في قاعدة البيانات الحقيقية',
                'partner' => [
                    'id' => $frontendId,
                    'name' => $name,
                    'phone' => $phone,
                    'category' => ($table === 'suppliers') ? 'vendor' : 'customer',
                    'type' => ($table === 'suppliers') ? 'مورد' : 'عميل',
                    'balanceNum' => $balance,
                    'balanceFormatted' => ($currency === 'USD') ? ('$' . number_format($balance, 2)) : (number_format($balance) . ' ج.س'),
                    'nature' => ($balance >= 0) ? 'مدين' : 'دائن'
                ]
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════════════════
        // ACTION 3: RECORD REAL FINANCIAL TRANSFER & UPDATE BALANCES IN MYSQL
        // ═════════════════════════════════════════════════════════════════════
        case 'record_financial_transfer':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) { $input = $_POST; }

            $fromId = $input['from_account_id'] ?? '';
            $toId = $input['to_account_id'] ?? '';
            $amount = (float)($input['amount'] ?? 0);
            $currency = $input['currency'] ?? 'USD';
            $method = $input['method'] ?? 'bank';
            $refNumber = trim($input['ref_number'] ?? ('TX-' . rand(100000, 999999)));
            $notes = trim($input['notes'] ?? 'تحويل ومناقلة مالية عبر تطبيق الجوال');

            if ($amount <= 0 || empty($fromId) || empty($toId)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'بيانات الحركة المالية غير مكتملة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Function to update entity balance in DB
            $updateEntityBalance = function($id, $delta) use ($pdo, $tenant_id) {
                $tables = ['banks', 'safes', 'customers', 'suppliers'];
                foreach ($tables as $tbl) {
                    $stmt = $pdo->prepare("SELECT id, data FROM `$tbl` WHERE (tenant_id = ? OR tenant_id IS NULL) AND (frontend_id = ? OR id = ?) LIMIT 1");
                    $stmt->execute([$tenant_id, $id, $id]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($row) {
                        $d = !empty($row['data']) ? json_decode($row['data'], true) : [];
                        $oldBal = (float)($d['currentBalance'] ?? ($d['openingBalance'] ?? 0));
                        $newBal = $oldBal + $delta;
                        $d['currentBalance'] = $newBal;
                        $json = json_encode($d, JSON_UNESCAPED_UNICODE);
                        $stmtUp = $pdo->prepare("UPDATE `$tbl` SET data = ?, updated_at = NOW() WHERE id = ?");
                        $stmtUp->execute([$json, $row['id']]);
                        return ['found' => true, 'table' => $tbl, 'name' => $d['name'] ?? '', 'newBalance' => $newBal];
                    }
                }
                return ['found' => false];
            };

            $senderRes = $updateEntityBalance($fromId, -$amount);
            $receiverRes = $updateEntityBalance($toId, +$amount);

            // Record into receipts and single_payments for unified cross-platform sync
            try {
                $transferRecord = [
                    'id' => $refNumber ?: ('TX-' . time()),
                    'voucherNo' => $refNumber ?: ('TX-' . time()),
                    'reference' => $refNumber,
                    'method' => $method,
                    'paymentMethod' => $method,
                    'amount' => $amount,
                    'totalAmount' => $amount,
                    'currency' => $currency,
                    'from' => $senderRes['name'] ?? $fromId,
                    'accountName' => $senderRes['name'] ?? $fromId,
                    'to' => $receiverRes['name'] ?? $toId,
                    'beneficiaryName' => $receiverRes['name'] ?? $toId,
                    'notes' => $notes,
                    'description' => $notes,
                    'date' => date('Y-m-d'),
                    'createdAt' => date('Y-m-d H:i:s'),
                    'source' => 'mobile_app'
                ];
                $jsonRecord = json_encode($transferRecord, JSON_UNESCAPED_UNICODE);
                $stmtTx = $pdo->prepare("INSERT INTO receipts (tenant_id, data, is_deleted) VALUES (?, ?, 0)");
                $stmtTx->execute([$tenant_id, $jsonRecord]);
                $stmtSp = $pdo->prepare("INSERT INTO single_payments (tenant_id, data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                $stmtSp->execute([$tenant_id, $jsonRecord]);
            } catch (Exception $e) {}

            echo json_encode([
                'success' => true,
                'message' => 'تم ترحيل الحركة المالية وتحديث أرصدة الطرفين في قاعدة البيانات فوراً',
                'reference' => $refNumber,
                'sender' => $senderRes,
                'receiver' => $receiverRes
            ], JSON_UNESCAPED_UNICODE);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
