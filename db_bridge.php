<?php
// ==============================================================
// جسر البيانات الذكي والسريع لربط "السجانة سمارت ERP" بقاعدة البيانات الحقيقية
// يربط كافة التقارير الاستراتيجية بقاعدة البيانات الحقيقية
// ==============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'magaees'); // تأكد من مطابقة اسم قاعدة بياناتك الحقيقية
define('DB_USER', 'root');     
define('DB_PASS', '');         

$GLOBAL_TENANT_ID = null;

function getDBConnection() {
    global $GLOBAL_TENANT_ID;
    global $pdo, $GLOBAL_TENANT_ID;
    if ($pdo === null) {
        require_once __DIR__ . '/api/db.php';
    }
    
    if ($GLOBAL_TENANT_ID === null) {
        $token = $_COOKIE['saas_token'] ?? '';
        if (empty($token)) {
            die("<script>alert('غير مصرح! يرجى تسجيل الدخول من البوابة أولاً.'); window.location.href='login.php';</script>");
        }
        $stmt = $pdo->prepare("SELECT tenant_id FROM users WHERE token = ? AND (token_expires_at IS NULL OR token_expires_at > NOW()) AND status = 'active'");
        $stmt->execute([$token]);
        $tenantId = $stmt->fetchColumn();
        if (!$tenantId) {
            die("<script>alert('انتهت صلاحية الجلسة، يرجى تسجيل الدخول مجدداً.'); window.location.href='login.php';</script>");
        }
        $GLOBAL_TENANT_ID = (int)$tenantId;
    }
    
    return $pdo;
}

// دالة فحص وجود الحقل current_balance في جدول الحسابات لمنع الأخطاء البرمجية
function hasCurrentBalanceColumn($pdo) {
    global $GLOBAL_TENANT_ID;
    static $has_col = null;
    if ($has_col !== null) return $has_col;
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM accounts LIKE 'current_balance'");
        $has_col = ($stmt->rowCount() > 0);
    } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
        $has_col = false;
    }
    return $has_col;
}

// -------------------------------------------------------------
// 1. تقرير "تحليل عمق الائتمان" (Debt Concentration)
// -------------------------------------------------------------
function getDebtConcentrationData($pdo) {
    global $GLOBAL_TENANT_ID;
    try {
        $customers = getCustomerExposureData($pdo);
        $debts = [];
        $total_debt = 0;
        foreach ($customers as $c) {
            if ($c['ledger_balance'] > 0) {
                $debts[] = $c;
                $total_debt += $c['ledger_balance'];
            }
        }
        
        $result = [];
        foreach ($debts as $d) {
            $pct = $total_debt > 0 ? round(($d['ledger_balance'] / $total_debt) * 100, 2) : 0;
            $result[] = [
                'customer_name' => $d['customer_name'],
                'total_customer_debt' => $d['ledger_balance'],
                'concentration_percentage' => $pct
            ];
        }
        
        // ترتيب تنازلي حسب الديون
        usort($result, function($a, $b) {
            return $b['total_customer_debt'] <=> $a['total_customer_debt'];
        });
        return $result;
    } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
        return [];
    }
}
function getDebtConcentration($pdo) {
    global $GLOBAL_TENANT_ID;
    return getDebtConcentrationData($pdo);
}

// -------------------------------------------------------------
// 2. مؤشر "معدل دوران الزبائن" (Customer Churn Rate)
// -------------------------------------------------------------
function getCustomerChurnData($pdo, $days_threshold = 60) {
    global $GLOBAL_TENANT_ID;
    try {
        $result = [];
        $last_purchase = [];
        
        // جلب آخر عمليات الشراء من جدول الفواتير sales_invoices الحقيقي
        try {
            $stmt = $pdo->query("SELECT data FROM sales_invoices WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
            while ($row = $stmt->fetch()) {
                $inv = json_decode($row['data'], true);
                if ($inv) {
                    $custId = $inv['customerId'] ?? ($inv['customer'] ?? '');
                    $date = $inv['date'] ?? '';
                    if ($custId && $date) {
                        if (!isset($last_purchase[$custId]) || $date > $last_purchase[$custId]) {
                            $last_purchase[$custId] = $date;
                        }
                    }
                }
            }
        } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage()); }

        // جلب العملاء الحقيقيين وحساب المخاطر
        $stmt = $pdo->query("SELECT data FROM customers WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
        while ($row = $stmt->fetch()) {
            $c = json_decode($row['data'], true);
            if ($c) {
                $custId = $c['id'] ?? '';
                $name = $c['name'] ?? 'عميل غير معروف';
                $last_date = $last_purchase[$custId] ?? null;
                if ($last_date) {
                    $days = (int)round((time() - strtotime($last_date)) / (60 * 60 * 24));
                    if ($days < 0) $days = 0;
                } else {
                    $days = 999; // لا توجد عمليات شراء سابقة
                }
                
                $status = 'ACTIVE';
                if ($days > $days_threshold) {
                    $status = 'HIGH RISK';
                } elseif ($days > ($days_threshold / 2)) {
                    $status = 'MEDIUM RISK';
                }
                
                $result[] = [
                    'customer_name' => $name,
                    'last_purchase_date' => $last_date ? $last_date : 'لا يوجد مبيعات سابقة',
                    'days_since_last_purchase' => $days == 999 ? 'غير نشط بالكامل' : $days,
                    'churn_risk_status' => $status
                ];
            }
        }

        // Mock data fallback removed for SaaS environment
        return $result;
    } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
        return [];
    }
}
function getCustomerChurn($pdo, $days_threshold = 60) {
    global $GLOBAL_TENANT_ID;
    return getCustomerChurnData($pdo, $days_threshold);
}

// -------------------------------------------------------------
// 3. تقرير "ربحية الصنف/العميل" (Customer Profitability Matrix)
// -------------------------------------------------------------
function getCustomerProfitabilityData($pdo) {
    global $GLOBAL_TENANT_ID;
    try {
        $result = [];
        $sales_by_cust = [];
        
        // جلب الفواتير لحساب الأرباح الحقيقية
        try {
            $stmt = $pdo->query("SELECT data FROM sales_invoices WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
            while ($row = $stmt->fetch()) {
                $inv = json_decode($row['data'], true);
                if ($inv) {
                    $custId = $inv['customerId'] ?? ($inv['customer'] ?? '');
                    $custName = $inv['customerName'] ?? 'عميل غير معروف';
                    $total = (float)($inv['grandTotal'] ?? ($inv['totalAmount'] ?? 0));
                    
                    if ($custId) {
                        if (!isset($sales_by_cust[$custId])) {
                            $sales_by_cust[$custId] = [
                                'name' => $custName,
                                'gross' => 0,
                                'net' => 0
                            ];
                        }
                        $sales_by_cust[$custId]['gross'] += $total;
                        
                        // حساب الأرباح بناءً على تكلفة الصنف
                        $net = 0;
                        $items = $inv['items'] ?? [];
                        $has_cost = false;
                        foreach ($items as $item) {
                            $qty = (float)($item['quantity'] ?? ($item['qty'] ?? 0));
                            $price = (float)($item['price'] ?? 0);
                            $cost = (float)($item['cost'] ?? ($item['actualCost'] ?? 0));
                            if ($cost > 0) {
                                $net += ($qty * ($price - $cost));
                                $has_cost = true;
                            }
                        }
                        if (!$has_cost) {
                            $net = $total * 0.15; // هامش افتراضي 15% إذا كانت التكلفة غير مسجلة
                        }
                        $sales_by_cust[$custId]['net'] += $net;
                    }
                }
            }
        } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage()); }

        foreach ($sales_by_cust as $custId => $c) {
            $margin = $c['gross'] > 0 ? round(($c['net'] / $c['gross']) * 100, 2) : 0;
            $result[] = [
                'customer_name' => $c['name'],
                'gross_sales' => $c['gross'],
                'net_profit' => $c['net'],
                'profit_margin_percentage' => $margin
            ];
        }

        // Mock data fallback removed for SaaS environment

        // ترتيب تنازلي حسب الربح الصافي
        usort($result, function($a, $b) {
            return $b['net_profit'] <=> $a['net_profit'];
        });

        return $result;
    } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
        return [];
    }
}
function getCustomerProfitability($pdo) {
    global $GLOBAL_TENANT_ID;
    return getCustomerProfitabilityData($pdo);
}

// -------------------------------------------------------------
// 4. لوحة "توازن الشيكات" (Check Clearing Velocity)
// -------------------------------------------------------------
function getCheckClearingVelocityData($pdo) {
    global $GLOBAL_TENANT_ID;
    try {
        $result = [];
        $velocity = [];
        
        try {
            $stmt = $pdo->query("SELECT data FROM `checks` WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
            while ($row = $stmt->fetch()) {
                $c = json_decode($row['data'], true);
                if ($c && ($c['status'] ?? '') === 'PENDING') {
                    $date = $c['dueDate'] ?? ($c['due_date'] ?? '');
                    if ($date) {
                        if (!isset($velocity[$date])) {
                            $velocity[$date] = [
                                'due_date' => $date,
                                'expected_inflow' => 0,
                                'expected_outflow' => 0
                            ];
                        }
                        $amount = (float)($c['amount'] ?? 0);
                        if (($c['type'] ?? '') === 'INBOUND') {
                            $velocity[$date]['expected_inflow'] += $amount;
                        } else {
                            $velocity[$date]['expected_outflow'] += $amount;
                        }
                    }
                }
            }
        } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage()); }

        foreach ($velocity as $v) {
            $result[] = [
                'due_date' => $v['due_date'],
                'expected_inflow' => $v['expected_inflow'],
                'expected_outflow' => $v['expected_outflow'],
                'net_daily_velocity' => $v['expected_inflow'] - $v['expected_outflow']
            ];
        }

        if (empty($result)) {
            $rate = 600000;
            $today = date('Y-m-d');
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            $result = [
                ['due_date' => $today, 'expected_inflow' => $rate * 4.5, 'expected_outflow' => $rate * 2.0, 'net_daily_velocity' => $rate * 2.5],
                ['due_date' => $tomorrow, 'expected_inflow' => $rate * 2.0, 'expected_outflow' => $rate * 3.5, 'net_daily_velocity' => -$rate * 1.5]
            ];
        }

        // ترتيب تصاعدي حسب التاريخ
        usort($result, function($a, $b) {
            return strcmp($a['due_date'], $b['due_date']);
        });

        return $result;
    } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
        return [];
    }
}
function getCheckClearingVelocity($pdo) {
    global $GLOBAL_TENANT_ID;
    return getCheckClearingVelocityData($pdo);
}

// -------------------------------------------------------------
// 5. تقرير "استهلاك الأصناف الموسمية" (Seasonal Trend Analysis)
// -------------------------------------------------------------
function getSeasonalTrendsData($pdo) {
    global $GLOBAL_TENANT_ID;
    try {
        $trends = [];
        
        $itemNames = [];
        try {
            $stmtItems = $pdo->query("SELECT frontend_id, search_name FROM items WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
            while ($rowItem = $stmtItems->fetch()) {
                if (!empty($rowItem['frontend_id'])) {
                    $itemNames[$rowItem['frontend_id']] = $rowItem['search_name'];
                }
            }
        } catch (Exception $e) { error_log('Items fetch error: ' . $e->getMessage()); }

        try {
            $stmt = $pdo->query("SELECT data FROM sales_invoices WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
            while ($row = $stmt->fetch()) {
                $inv = json_decode($row['data'], true);
                if ($inv) {
                    $date = $inv['date'] ?? '';
                    $month = $date ? (int)date('m', strtotime($date)) : 0;
                    $items = $inv['items'] ?? [];
                    foreach ($items as $item) {
                        $name = $item['itemName'] ?? ($item['name'] ?? null);
                        if (!$name && !empty($item['itemId']) && isset($itemNames[$item['itemId']])) {
                            $name = $itemNames[$item['itemId']];
                        }
                        if (!$name) $name = 'صنف غير معروف';
                        
                        $qty = (float)($item['quantity'] ?? ($item['qty'] ?? 0));
                        
                        if (!isset($trends[$name])) {
                            $trends[$name] = [
                                'item_name' => $name,
                                'autumn_rainy_season_qty' => 0,
                                'summer_dry_season_qty' => 0,
                                'total_annual_qty' => 0
                            ];
                        }
                        
                        if (in_array($month, [6, 7, 8, 9])) {
                            $trends[$name]['autumn_rainy_season_qty'] += $qty;
                        } else {
                            $trends[$name]['summer_dry_season_qty'] += $qty;
                        }
                        $trends[$name]['total_annual_qty'] += $qty;
                    }
                }
            }
        } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage()); }

        $result = array_values($trends);

        if (empty($result)) {
            $result = [
                ['item_name' => 'حديد تسليح 5 لينيا الأسعد', 'autumn_rainy_season_qty' => 450, 'summer_dry_season_qty' => 850, 'total_annual_qty' => 1300],
                ['item_name' => 'أسمنت عطبرة مقاوم', 'autumn_rainy_season_qty' => 1200, 'summer_dry_season_qty' => 2200, 'total_annual_qty' => 3400]
            ];
        }

        // ترتيب تنازلي حسب إجمالي الكمية السنوية
        usort($result, function($a, $b) {
            return $b['total_annual_qty'] <=> $a['total_annual_qty'];
        });

        return $result;
    } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
        return [];
    }
}
function getSeasonalTrends($pdo) {
    global $GLOBAL_TENANT_ID;
    return getSeasonalTrendsData($pdo);
}

// -------------------------------------------------------------
// 6. دفتر الأستاذ العصري المطور (Multi-Dimensional Ledger)
// -------------------------------------------------------------
function getMultiDimensionalLedgerData($pdo, $customer_id) {
    global $GLOBAL_TENANT_ID;
    try {
        // جلب معلومات العميل من جدول customers الحقيقي
        global $GLOBAL_TENANT_ID;
        $stmt = $pdo->prepare("SELECT data FROM customers WHERE tenant_id = '{$GLOBAL_TENANT_ID}' AND (id = ? OR JSON_EXTRACT(data, '$.id') = ?)");
        $stmt->execute([$customer_id, $customer_id]);
        $row = $stmt->fetch();
        
        $customer = null;
        if ($row) {
            $customer = json_decode($row['data'], true);
        } else {
            $customer = $pdo->query("SELECT data FROM customers WHERE tenant_id = '{$GLOBAL_TENANT_ID}' LIMIT 1")->fetchColumn();
            if ($customer) $customer = json_decode($customer, true);
        }

        if ($customer) {
            $custId = $customer['id'] ?? '';
            $name = $customer['name'] ?? 'عميل غير معروف';
            $balance = (float)($customer['currentBalance'] ?? 0);
            
            // حساب الشيكات المرتجعة والشيكات المعلقة
            $bounced = 0;
            $max_overdue = 0;
            try {
                $cstmt = $pdo->query("SELECT data FROM `checks` WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
                while ($crow = $cstmt->fetch()) {
                    $c = json_decode($crow['data'], true);
                    if ($c && ($c['customerId'] ?? ($c['customer'] ?? '')) === $custId) {
                        if (($c['status'] ?? '') === 'BOUNCED') {
                            $bounced++;
                        }
                        if (($c['status'] ?? '') === 'PENDING') {
                            $due = $c['dueDate'] ?? ($c['due_date'] ?? '');
                            if ($due && $due < date('Y-m-d')) {
                                $days = (int)round((time() - strtotime($due)) / (60 * 60 * 24));
                                if ($days > $max_overdue) $max_overdue = $days;
                            }
                        }
                    }
                }
            } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage()); }

            // حساب إجمالي ديون الشركة لتحديد نسبة العميل
            $total_debt = 0;
            try {
                $cstmt = $pdo->query("SELECT data FROM customers WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
                while ($crow = $cstmt->fetch()) {
                    $cdata = json_decode($crow['data'], true);
                    if ($cdata) {
                        $bal = (float)($cdata['currentBalance'] ?? 0);
                        if ($bal > 0) $total_debt += $bal;
                    }
                }
            } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage()); }

            $share = $total_debt > 0 ? round(($balance / $total_debt) * 100, 2) : 0;

            return [
                'customer_name' => $name,
                'current_balance' => $balance,
                'bounced_checks_count' => $bounced,
                'max_days_overdue' => $max_overdue,
                'share_of_total_company_debt' => $share
            ];
        }

        return null;
    } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
        return null;
    }
}
function getMultiDimensionalLedger($pdo, $customer_id) {
    global $GLOBAL_TENANT_ID;
    return getMultiDimensionalLedgerData($pdo, $customer_id);
}

// -------------------------------------------------------------
// 7. تقرير "انحراف السعر" (Price Variance Report)
// -------------------------------------------------------------
function getPriceVarianceData($pdo) {
    global $GLOBAL_TENANT_ID;
    try {
        $result = [];
        try {
            $stmt = $pdo->query("SELECT data FROM sales_invoices WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
            while ($row = $stmt->fetch()) {
                $inv = json_decode($row['data'], true);
                if ($inv) {
                    $custName = $inv['customerName'] ?? 'عميل غير معروف';
                    $items = $inv['items'] ?? [];
                    foreach ($items as $item) {
                        $itemName = $item['itemName'] ?? ($item['name'] ?? 'صنف غير معروف');
                        $qty = (float)($item['quantity'] ?? ($item['qty'] ?? 0));
                        $price = (float)($item['price'] ?? 0);
                        $cost = (float)($item['cost'] ?? ($item['actualCost'] ?? 0));
                        
                        if ($cost > 0 && $price <= ($cost * 1.05)) {
                            $margin = $price - $cost;
                            $margin_pct = round(($margin / $cost) * 100, 2);
                            $result[] = [
                                'transaction_id' => $inv['invoiceNo'] ?? 'TRX',
                                'customer_name' => $custName,
                                'item_name' => $itemName,
                                'actual_selling_price' => $price,
                                'stock_cost_price' => $cost,
                                'unit_margin' => $margin,
                                'profit_margin_percentage' => $margin_pct
                            ];
                        }
                    }
                }
            }
        } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage()); }

        // Mock data fallback removed for SaaS environment

        // ترتيب تصاعدي حسب هامش الربح
        usort($result, function($a, $b) {
            return $a['profit_margin_percentage'] <=> $b['profit_margin_percentage'];
        });

        return $result;
    } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
        return [];
    }
}
function getPriceVariance($pdo) {
    global $GLOBAL_TENANT_ID;
    return getPriceVarianceData($pdo);
}

// -------------------------------------------------------------
// 8. تقرير "أداء الموردين" (Supplier Reliability Index)
// -------------------------------------------------------------
function getSupplierPerformanceData($pdo) {
    global $GLOBAL_TENANT_ID;
    try {
        $result = [];
        $suppliers_volume = [];
        
        try {
            $stmt = $pdo->query("SELECT data FROM purchase_invoices WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
            while ($row = $stmt->fetch()) {
                $inv = json_decode($row['data'], true);
                if ($inv) {
                    $supId = $inv['supplierId'] ?? ($inv['supplier'] ?? '');
                    $supName = $inv['supplierName'] ?? 'مورد غير معروف';
                    $total = (float)($inv['grandTotal'] ?? ($inv['totalAmount'] ?? 0));
                    
                    if ($supId) {
                        if (!isset($suppliers_volume[$supId])) {
                            $suppliers_volume[$supId] = [
                                'supplier_name' => $supName,
                                'total_orders_placed' => 0,
                                'total_business_volume' => 0,
                                'prices' => []
                            ];
                        }
                        $suppliers_volume[$supId]['total_orders_placed']++;
                        $suppliers_volume[$supId]['total_business_volume'] += $total;
                        
                        $items = $inv['items'] ?? [];
                        foreach ($items as $item) {
                            $price = (float)($item['price'] ?? 0);
                            if ($price > 0) {
                                $suppliers_volume[$supId]['prices'][] = $price;
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage()); }

        foreach ($suppliers_volume as $supId => $s) {
            $prices = $s['prices'];
            $vol = 0;
            if (count($prices) > 1) {
                $mean = array_sum($prices) / count($prices);
                $sum_sq = 0;
                foreach ($prices as $p) {
                    $sum_sq += pow($p - $mean, 2);
                }
                $vol = sqrt($sum_sq / (count($prices) - 1));
            }
            
            $result[] = [
                'supplier_name' => $s['supplier_name'],
                'total_orders_placed' => $s['total_orders_placed'],
                'total_business_volume' => $s['total_business_volume'],
                'price_volatility_variance' => round($vol, 2)
            ];
        }

        if (empty($result)) {
            $rate = 600000;
            $result = [
                ['supplier_name' => 'مصنع الأسعد للحديد', 'total_orders_placed' => 18, 'total_business_volume' => $rate * 85.0, 'price_volatility_variance' => 12.4],
                ['supplier_name' => 'شركة أسمنت عطبرة', 'total_orders_placed' => 12, 'total_business_volume' => $rate * 45.0, 'price_volatility_variance' => 4.5]
            ];
        }

        // ترتيب تنازلي حسب الحجم المالي
        usort($result, function($a, $b) {
            return $b['total_business_volume'] <=> $a['total_business_volume'];
        });

        return $result;
    } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
        return [];
    }
}
function getSupplierPerformance($pdo) {
    global $GLOBAL_TENANT_ID;
    return getSupplierPerformanceData($pdo);
}

// -------------------------------------------------------------
// 9. تحليل "السيولة اللحظية المتوقعة" (Live Cash Flow Forecast)
// -------------------------------------------------------------
function getLiveCashFlowForecastData($pdo, $current_cash_on_hand) {
    global $GLOBAL_TENANT_ID;
    try {
        $inflow = 0;
        $outflow = 0;
        try {
            $stmt = $pdo->query("SELECT data FROM `checks` WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
            while ($row = $stmt->fetch()) {
                $c = json_decode($row['data'], true);
                if ($c && ($c['status'] ?? '') === 'PENDING') {
                    $due = $c['dueDate'] ?? ($c['due_date'] ?? '');
                    if ($due && $due >= date('Y-m-d') && $due <= date('Y-m-d', strtotime('+30 days'))) {
                        $amount = (float)($c['amount'] ?? 0);
                        if (($c['type'] ?? '') === 'INBOUND') {
                            $inflow += $amount;
                        } else {
                            $outflow += $amount;
                        }
                    }
                }
            }
        } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage()); }

        return [
            'cash_now' => $current_cash_on_hand,
            'upcoming_cheques_collection' => $inflow,
            'upcoming_payable_obligations' => $outflow,
            'projected_liquidity_30_days' => $current_cash_on_hand + $inflow - $outflow
        ];
    } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
        return [
            'cash_now' => $current_cash_on_hand,
            'upcoming_cheques_collection' => 0,
            'upcoming_payable_obligations' => 0,
            'projected_liquidity_30_days' => $current_cash_on_hand
        ];
    }
}
function getLiveCashFlowForecast($pdo, $current_cash_on_hand) {
    global $GLOBAL_TENANT_ID;
    return getLiveCashFlowForecastData($pdo, $current_cash_on_hand);
}

// -------------------------------------------------------------
// 10. تقرير "مصفوفة تكرار الزيارة والولاء" (Customer Engagement Grid)
// -------------------------------------------------------------
function getCustomerEngagementData($pdo) {
    global $GLOBAL_TENANT_ID;
    try {
        $result = [];
        $visits = [];
        
        try {
            $stmt = $pdo->query("SELECT data FROM sales_invoices WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
            while ($row = $stmt->fetch()) {
                $inv = json_decode($row['data'], true);
                if ($inv) {
                    $custId = $inv['customerId'] ?? ($inv['customer'] ?? '');
                    $custName = $inv['customerName'] ?? 'عميل غير معروف';
                    $total = (float)($inv['grandTotal'] ?? ($inv['totalAmount'] ?? 0));
                    
                    if ($custId) {
                        if (!isset($visits[$custId])) {
                            $visits[$custId] = [
                                'customer_name' => $custName,
                                'total_visit_count' => 0,
                                'total_spend' => 0
                            ];
                        }
                        $visits[$custId]['total_visit_count']++;
                        $visits[$custId]['total_spend'] += $total;
                    }
                }
            }
        } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage()); }

        foreach ($visits as $v) {
            $tier = 'OCCASIONAL';
            if ($v['total_visit_count'] >= 15) {
                $tier = 'VIP';
            } elseif ($v['total_visit_count'] >= 5) {
                $tier = 'REGULAR';
            }
            $result[] = [
                'customer_name' => $v['customer_name'],
                'total_visit_count' => $v['total_visit_count'],
                'total_spend' => $v['total_spend'],
                'engagement_tier' => $tier
            ];
        }

        // Mock data fallback removed for SaaS environment

        // ترتيب تنازلي حسب عدد الزيارات
        usort($result, function($a, $b) {
            return $b['total_visit_count'] <=> $a['total_visit_count'];
        });

        return $result;
    } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
        return [];
    }
}
function getCustomerEngagement($pdo) {
    global $GLOBAL_TENANT_ID;
    return getCustomerEngagementData($pdo);
}

// -------------------------------------------------------------
// ميزة متممة: جلب السيولة اللحظية للبنوك والخزن (حقيقي)
// -------------------------------------------------------------
function getRealTimeLiquidity($pdo) {
    global $GLOBAL_TENANT_ID;
    try {
        $vaults = [];
        
        // 1. جلب الخزن الحقيقية
        try {
            $stmt = $pdo->query("SELECT data FROM safes WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
            while ($row = $stmt->fetch()) {
                $data = json_decode($row['data'], true);
                if ($data) {
                    $vaults[] = [
                        'vault_name' => $data['name'] ?? 'خزنة كاش',
                        'amount' => (float)($data['balance'] ?? 0),
                        'color' => '#10b981'
                    ];
                }
            }
        } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage()); }

        // 2. جلب الحسابات البنكية الحقيقية
        try {
            $stmt = $pdo->query("SELECT data FROM banks WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
            while ($row = $stmt->fetch()) {
                $data = json_decode($row['data'], true);
                if ($data) {
                    $vaults[] = [
                        'vault_name' => $data['name'] ?? 'حساب بنكي',
                        'amount' => (float)($data['balance'] ?? 0),
                        'color' => '#3b82f6'
                    ];
                }
            }
        } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage()); }

        // Mock data fallback removed for SaaS environment

        // ترتيب تنازلي حسب الرصيد المتوفر
        usort($vaults, function($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });
        
        return $vaults;
    } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
        return [];
    }
}

// -------------------------------------------------------------
// ميزة متممة: جلب تقرير مديونيات الأرصدة الشامل (دفتر + شيكات)
// -------------------------------------------------------------
function getCustomerExposureData($pdo) {
    global $GLOBAL_TENANT_ID;
    try {
        $report_data = [];
        
        // 1. جلب الشيكات المعلقة لكل عميل
        $checks_by_cust = [];
        try {
            $stmt = $pdo->query("SELECT data FROM `checks` WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
            while ($row = $stmt->fetch()) {
                $c = json_decode($row['data'], true);
                if ($c && ($c['status'] ?? '') === 'PENDING' && ($c['type'] ?? '') === 'INBOUND') {
                    $custId = $c['customerId'] ?? ($c['customer'] ?? '');
                    if ($custId) {
                        if (!isset($checks_by_cust[$custId])) $checks_by_cust[$custId] = 0;
                        $checks_by_cust[$custId] += (float)($c['amount'] ?? 0);
                    }
                }
            }
        } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage()); }

        // 2. جلب العملاء الحقيقيين وأرصدتهم
        $stmt = $pdo->query("SELECT data FROM customers WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
        while ($row = $stmt->fetch()) {
            $c = json_decode($row['data'], true);
            if ($c) {
                $custId = $c['id'] ?? '';
                $name = $c['name'] ?? 'عميل غير معروف';
                $ledger = (float)($c['currentBalance'] ?? 0);
                $pending = isset($checks_by_cust[$custId]) ? $checks_by_cust[$custId] : 0;
                $report_data[] = [
                    'customer_id' => $custId,
                    'customer_name' => $name,
                    'ledger_balance' => $ledger,
                    'pending_checks_total' => $pending,
                    'total_exposure' => $ledger + $pending
                ];
            }
        }

        // Mock data fallback removed for SaaS environment

        // ترتيب تنازلي حسب الخطورة الإجمالية
        usort($report_data, function($a, $b) {
            return $b['total_exposure'] <=> $a['total_exposure'];
        });

        return $report_data;
    } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
        return [];
    }
}

// -------------------------------------------------------------
// دالة إرسال التنبيهات مباشرة إلى رقمك على الواتساب
// -------------------------------------------------------------
function sendWhatsAppAlert($message) {
    global $GLOBAL_TENANT_ID;
    $instance_id = 'instance99999'; 
    $token = 'your_token_here';     
    $my_phone = '+249912345678';    
    
    try {
        @include_once __DIR__ . '/api/db.php';
        if (isset($pdo)) {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM global_settings WHERE setting_key IN ('whatsapp_instance_id', 'whatsapp_token', 'whatsapp_phone')");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            if (!empty($settings['whatsapp_instance_id'])) $instance_id = $settings['whatsapp_instance_id'];
            if (!empty($settings['whatsapp_token'])) $token = $settings['whatsapp_token'];
            if (!empty($settings['whatsapp_phone'])) $my_phone = $settings['whatsapp_phone'];
        }
    } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage()); }

    $params = array(
        'token' => $token,
        'to' => $my_phone,
        'body' => $message
    );
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.ultramsg.com/" . $instance_id . "/messages/chat",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => http_build_query($params),
        CURLOPT_HTTPHEADER => array(
            "content-type: application-x-www-form-urlencoded"
        ),
    ));
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    
    if ($err) {
        error_log("فشل إرسال واتساب: " . $err);
        return false;
    } else {
        return true;
    }
}

// -------------------------------------------------------------
// جلب بيانات تقارير المخازن الـ 20 اللحظية من الجداول الحقيقية
// -------------------------------------------------------------
function getInventoryReportData($pdo) {
    global $GLOBAL_TENANT_ID;
    try {
        // جلب جميع الأصناف
        $stmt = $pdo->query("SELECT * FROM items WHERE is_deleted = 0");
        $items_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // جلب المشتريات المؤكدة
        $stmt = $pdo->query("SELECT * FROM purchase_invoices WHERE is_deleted = 0 AND status_val = 'confirmed'");
        $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // جلب المبيعات
        $stmt = $pdo->query("SELECT * FROM sales_invoices WHERE is_deleted = 0");
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $report_data = [];
        $thirty_days_ago = date('Y-m-d', strtotime('-30 days'));

        foreach ($items_rows as $item_row) {
            $item_data = json_decode($item_row['data'], true);
            if (!$item_data) continue;
            
            $item_id = $item_data['id'] ?? '';
            $item_name = $item_data['name'] ?? 'بدون اسم';
            $category = $item_data['category'] ?? '';
            $opening_qty = (float)($item_data['openingQty'] ?? 0);
            
            // قراءة حدود الأمان
            $min_limit = (float)($item_data['minLimit'] ?? ($item_data['reorderLevel'] ?? 50));
            if ($min_limit <= 0) $min_limit = 50;
            $max_limit = (float)($item_data['maxLimit'] ?? 600);
            if ($max_limit <= 0) $max_limit = 600;
            $demand_limit = (float)($item_data['reorderLevel'] ?? 120);
            if ($demand_limit <= 0) $demand_limit = 120;
            
            $selling_price = (float)($item_data['sellingPrice'] ?? 0);

            // حساب المشتريات ومتوسط سعر التكلفة وتاريخ آخر شراء
            $purchased_qty = 0;
            $purchase_costs = [];
            $last_purchase_date = null;
            
            foreach ($purchases as $p) {
                $p_data = json_decode($p['data'], true);
                if ($p_data && isset($p_data['items'])) {
                    $p_date = $p['date_val'] ?? ($p_data['date'] ?? null);
                    $has_item = false;
                    foreach ($p_data['items'] as $item) {
                        $p_item_id = $item['itemId'] ?? ($item['id'] ?? '');
                        if ($p_item_id === $item_id) {
                            $qty = (float)($item['quantity'] ?? ($item['qty'] ?? 0));
                            $purchased_qty += $qty;
                            $has_item = true;
                            $price = (float)($item['price'] ?? 0);
                            if ($price > 0) {
                                $purchase_costs[] = $price;
                            }
                        }
                    }
                    if ($has_item && $p_date) {
                        if ($last_purchase_date === null || $p_date > $last_purchase_date) {
                            $last_purchase_date = $p_date;
                        }
                    }
                }
            }
            
            $cost_price = !empty($purchase_costs) ? (array_sum($purchase_costs) / count($purchase_costs)) : ($selling_price * 0.8);

            // حساب المبيعات، البضاعة المحجوزة، وتاريخ آخر سحب
            $sold_qty = 0;
            $committed_qty = 0;
            $sold_last_30_days = 0;
            $last_sales_date = null;

            foreach ($sales as $s) {
                $s_data = json_decode($s['data'], true);
                if ($s_data) {
                    $status = $s['status_val'] ?? ($s_data['status'] ?? '');
                    $s_date = $s['date_val'] ?? ($s_data['date'] ?? null);
                    $items_list = $s_data['items'] ?? [];
                    $has_item = false;
                    
                    foreach ($items_list as $item) {
                        $s_item_id = $item['itemId'] ?? ($item['id'] ?? '');
                        if ($s_item_id === $item_id) {
                            $qty = (float)($item['quantity'] ?? ($item['qty'] ?? 0));
                            $has_item = true;
                            
                            if ($status === 'delivered') {
                                $sold_qty += $qty;
                                if ($s_date >= $thirty_days_ago) {
                                    $sold_last_30_days += $qty;
                                }
                            } elseif ($status === 'pending_delivery') {
                                $committed_qty += $qty;
                            }
                        }
                    }
                    if ($has_item && $s_date && $status === 'delivered') {
                        if ($last_sales_date === null || $s_date > $last_sales_date) {
                            $last_sales_date = $s_date;
                        }
                    }
                }
            }

            $current_stock = $opening_qty + $purchased_qty - $sold_qty;
            $avg_daily_sales = $sold_last_30_days / 30.0;

            $days_coverage = 999;
            if ($avg_daily_sales > 0) {
                $days_coverage = round($current_stock / $avg_daily_sales);
            }

            $last_movement_date = 'لا يوجد حركة';
            if ($last_sales_date && $last_purchase_date) {
                $last_movement_date = max($last_sales_date, $last_purchase_date);
            } elseif ($last_sales_date) {
                $last_movement_date = $last_sales_date;
            } elseif ($last_purchase_date) {
                $last_movement_date = $last_purchase_date;
            }

            $report_data[] = [
                'item_id' => $item_id,
                'item_name' => $item_name,
                'category' => $category,
                'stock_qty' => $current_stock,
                'min_limit' => $min_limit,
                'max_limit' => $max_limit,
                'demand_limit' => $demand_limit,
                'avg_daily_sales' => $avg_daily_sales,
                'days_coverage' => $days_coverage,
                'cost_price' => $cost_price,
                'selling_price' => $selling_price,
                'committed_qty' => $committed_qty,
                'purchased_qty' => $purchased_qty,
                'sold_qty' => $sold_qty,
                'last_movement_date' => $last_movement_date
            ];
        }
        return $report_data;
    } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
        error_log("Error in getInventoryReportData: " . $e->getMessage());
        return [];
    }
}

// -------------------------------------------------------------
// جلب استهلاك الطاقة الاستيعابية الفعلي لعنابر ومستودعات التخزين
// -------------------------------------------------------------
function getWarehouseCapacities($pdo) {
    global $GLOBAL_TENANT_ID;
    try {
        // جلب المستودعات
        $stmt = $pdo->query("SELECT * FROM warehouses WHERE is_deleted = 0");
        $wh_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // جلب المشتريات والمبيعات
        $stmt = $pdo->query("SELECT * FROM purchase_invoices WHERE is_deleted = 0 AND status_val = 'confirmed'");
        $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->query("SELECT * FROM sales_invoices WHERE is_deleted = 0 AND status_val = 'delivered'");
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $wh_data = [];
        foreach ($wh_rows as $row) {
            $data = json_decode($row['data'], true);
            if (!$data) continue;
            $wh_id = $data['id'] ?? '';
            $wh_name = $data['name'] ?? 'مخزن غير معروف';
            
            // حساب رصيد المواد المخزنة
            $stock = 0;
            
            // الوارد
            foreach ($purchases as $p) {
                $p_data = json_decode($p['data'], true);
                if ($p_data && ($p_data['warehouse'] ?? '') === $wh_id && isset($p_data['items'])) {
                    foreach ($p_data['items'] as $item) {
                        $stock += (float)($item['quantity'] ?? ($item['qty'] ?? 0));
                    }
                }
            }
            
            // الصادر
            foreach ($sales as $s) {
                $s_data = json_decode($s['data'], true);
                if ($s_data && ($s_data['warehouse'] ?? '') === $wh_id && isset($s_data['items'])) {
                    foreach ($s_data['items'] as $item) {
                        $stock -= (float)($item['quantity'] ?? ($item['qty'] ?? 0));
                    }
                }
            }
            
            if ($stock < 0) $stock = 0;
            
            // السعة الافتراضية للعنابر بالسجانة
            $capacity = 5000;
            $pct = $capacity > 0 ? round(($stock / $capacity) * 100, 1) : 0;
            
            $wh_data[] = [
                'warehouse_name' => $wh_name,
                'stock_qty' => $stock,
                'capacity' => $capacity,
                'utilization_pct' => $pct
            ];
        }
        
        // Mock data fallback removed for SaaS environment
        return $wh_data;
    } catch (Exception $e) { error_log('DB Bridge Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
        return [];
    }
}
?>
