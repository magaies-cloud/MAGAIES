<?php
// ==============================================================
// جسر الربط الموحد والكامل لبيانات MAGAIEES ERP
// يربط كافة التقارير الاستراتيجية بقاعدة البيانات الحقيقية
// ==============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'magaies'); // تأكد من مطابقة اسم قاعدة بياناتك الحقيقية
define('DB_USER', 'root');     
define('DB_PASS', '');         

function getDBConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $pdo;
    } catch (PDOException $e) {
        // المحاولة باستخدام ملف الاتصال الافتراضي للنظام إذا كان متوفراً
        try {
            @include_once __DIR__ . '/api/db.php';
            if (isset($pdo)) {
                return $pdo;
            }
        } catch (Exception $ex) {}

        error_log("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
        die("<div style='background:#7f1d1d; color:#fecaca; padding:20px; text-align:center; font-family:sans-serif; border-radius:12px; margin:20px; direction:rtl;'>
                <h3 style='margin:0 0 10px 0;'>⚠️ فشل الاتصال بقاعدة البيانات الحقيقية</h3>
                <p style='margin:0; font-size:14px;'>الرجاء التأكد من تشغيل خادم MySQL (مثل XAMPP أو WampServer) ومطابقة اسم قاعدة البيانات المسمى <strong>magaies</strong>.</p>
             </div>");
    }
}

// دالة فحص وجود الحقل current_balance في جدول الحسابات
function hasCurrentBalanceColumn($pdo) {
    static $has_col = null;
    if ($has_col !== null) return $has_col;
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM accounts LIKE 'current_balance'");
        $has_col = ($stmt->rowCount() > 0);
    } catch (Exception $e) {
        $has_col = false;
    }
    return $has_col;
}

// -------------------------------------------------------------
// 1. تقرير "تحليل عمق الائتمان" (Debt Concentration)
// -------------------------------------------------------------
function getDebtConcentration($pdo) {
    try {
        if (hasCurrentBalanceColumn($pdo)) {
            $query = "
                SELECT 
                    a.name AS customer_name,
                    COALESCE(a.current_balance, 0) AS total_customer_debt,
                    ROUND((COALESCE(a.current_balance, 0) / NULLIF((SELECT SUM(current_balance) FROM accounts WHERE type = 'CUSTOMER'), 0)) * 100, 2) AS concentration_percentage
                FROM accounts a
                WHERE a.type = 'CUSTOMER' AND a.current_balance > 0
                ORDER BY total_customer_debt DESC;
            ";
        } else {
            $query = "
                SELECT 
                    a.name AS customer_name,
                    COALESCE((SELECT SUM(t.total_amount) FROM transactions t WHERE t.account_id = a.id), 0) AS total_customer_debt,
                    ROUND((COALESCE((SELECT SUM(t.total_amount) FROM transactions t WHERE t.account_id = a.id), 0) / NULLIF((SELECT SUM(t2.total_amount) FROM transactions t2 JOIN accounts a2 ON t2.account_id = a2.id WHERE a2.type = 'CUSTOMER'), 0)) * 100, 2) AS concentration_percentage
                FROM accounts a
                WHERE a.type = 'CUSTOMER'
                HAVING total_customer_debt > 0
                ORDER BY total_customer_debt DESC;
            ";
        }
        return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// -------------------------------------------------------------
// 2. مؤشر "معدل دوران الزبائن" (Customer Churn Rate)
// -------------------------------------------------------------
function getCustomerChurn($pdo, $days_threshold = 60) {
    try {
        $query = "
            SELECT 
                a.name AS customer_name,
                MAX(t.transaction_date) AS last_purchase_date,
                DATEDIFF(NOW(), MAX(t.transaction_date)) AS days_since_last_purchase,
                CASE 
                    WHEN DATEDIFF(NOW(), MAX(t.transaction_date)) > :days_threshold THEN 'HIGH RISK'
                    WHEN DATEDIFF(NOW(), MAX(t.transaction_date)) > (:days_threshold / 2) THEN 'MEDIUM RISK'
                    ELSE 'ACTIVE'
                END AS churn_risk_status
            FROM accounts a
            LEFT JOIN transactions t ON a.id = t.account_id
            WHERE a.type = 'CUSTOMER'
            GROUP BY a.id, a.name;
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['days_threshold' => $days_threshold]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// -------------------------------------------------------------
// 3. تقرير "ربحية الصنف/العميل" (Customer Profitability Matrix)
// -------------------------------------------------------------
function getCustomerProfitability($pdo) {
    try {
        $query = "
            SELECT 
                a.name AS customer_name,
                SUM(t.total_amount) AS gross_sales,
                SUM(t.total_amount - (t.quantity * t.actual_cost)) AS net_profit,
                ROUND((SUM(t.total_amount - (t.quantity * t.actual_cost)) / NULLIF(SUM(t.total_amount), 0)) * 100, 2) AS profit_margin_percentage
            FROM transactions t
            JOIN accounts a ON t.account_id = a.id
            WHERE a.type = 'CUSTOMER'
            GROUP BY a.id, a.name
            ORDER BY net_profit DESC;
        ";
        return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// -------------------------------------------------------------
// 4. لوحة "توازن الشيكات" (Check Clearing Velocity)
// -------------------------------------------------------------
function getCheckClearingVelocity($pdo) {
    try {
        $query = "
            SELECT 
                due_date,
                SUM(CASE WHEN type = 'INBOUND' THEN amount ELSE 0 END) AS expected_inflow,
                SUM(CASE WHEN type = 'OUTBOUND' THEN amount ELSE 0 END) AS expected_outflow,
                (SUM(CASE WHEN type = 'INBOUND' THEN amount ELSE 0 END) - 
                 SUM(CASE WHEN type = 'OUTBOUND' THEN amount ELSE 0 END)) AS net_daily_velocity
            FROM `checks`
            WHERE status = 'PENDING' AND due_date >= CURRENT_DATE()
            GROUP BY due_date
            ORDER BY due_date ASC;
        ";
        return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// -------------------------------------------------------------
// 5. تقرير "استهلاك الأصناف الموسمية" (Seasonal Trend Analysis)
// -------------------------------------------------------------
function getSeasonalTrends($pdo) {
    try {
        $query = "
            SELECT 
                i.name AS item_name,
                SUM(CASE WHEN MONTH(t.transaction_date) IN (6,7,8,9) THEN t.quantity ELSE 0 END) AS autumn_rainy_season_qty,
                SUM(CASE WHEN MONTH(t.transaction_date) NOT IN (6,7,8,9) THEN t.quantity ELSE 0 END) AS summer_dry_season_qty,
                SUM(t.quantity) AS total_annual_qty
            FROM transactions t
            JOIN inventory_items i ON t.item_id = i.id
            GROUP BY i.id, i.name
            ORDER BY total_annual_qty DESC;
        ";
        return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// -------------------------------------------------------------
// 6. دفتر الأستاذ العصري (Multi-Dimensional Ledger)
// -------------------------------------------------------------
function getMultiDimensionalLedger($pdo, $customer_id) {
    try {
        if (hasCurrentBalanceColumn($pdo)) {
            $query = "
                SELECT 
                    a.name AS customer_name,
                    COALESCE(a.current_balance, 0) AS current_balance,
                    (SELECT COUNT(*) FROM `checks` WHERE account_id = a.id AND status = 'BOUNCED') AS bounced_checks_count,
                    (SELECT MAX(DATEDIFF(CURRENT_DATE(), due_date)) FROM `checks` WHERE account_id = a.id AND status = 'PENDING' AND due_date < CURRENT_DATE()) AS max_days_overdue,
                    ROUND((COALESCE(a.current_balance, 0) / NULLIF((SELECT SUM(current_balance) FROM accounts WHERE type = 'CUSTOMER'), 0)) * 100, 2) AS share_of_total_company_debt
                FROM accounts a
                WHERE a.id = :customer_id AND a.type = 'CUSTOMER';
            ";
        } else {
            $query = "
                SELECT 
                    a.name AS customer_name,
                    COALESCE((SELECT SUM(t.total_amount) FROM transactions t WHERE t.account_id = a.id), 0) AS current_balance,
                    (SELECT COUNT(*) FROM `checks` WHERE account_id = a.id AND status = 'BOUNCED') AS bounced_checks_count,
                    (SELECT MAX(DATEDIFF(CURRENT_DATE(), due_date)) FROM `checks` WHERE account_id = a.id AND status = 'PENDING' AND due_date < CURRENT_DATE()) AS max_days_overdue,
                    ROUND((COALESCE((SELECT SUM(t.total_amount) FROM transactions t WHERE t.account_id = a.id), 0) / NULLIF((SELECT SUM(t2.total_amount) FROM transactions t2 JOIN accounts a2 ON t2.account_id = a2.id WHERE a2.type = 'CUSTOMER'), 0)) * 100, 2) AS share_of_total_company_debt
                FROM accounts a
                WHERE a.id = :customer_id AND a.type = 'CUSTOMER';
            ";
        }
        $stmt = $pdo->prepare($query);
        $stmt->execute(['customer_id' => $customer_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

// -------------------------------------------------------------
// 7. تقرير "انحراف السعر" (Price Variance Report)
// -------------------------------------------------------------
function getPriceVariance($pdo) {
    try {
        $query = "
            SELECT 
                t.id AS transaction_id,
                a.name AS customer_name,
                i.name AS item_name,
                t.unit_price AS actual_selling_price,
                t.actual_cost AS stock_cost_price,
                (t.unit_price - t.actual_cost) AS unit_margin,
                ROUND(((t.unit_price - t.actual_cost) / NULLIF(t.actual_cost, 0)) * 100, 2) AS profit_margin_percentage
            FROM transactions t
            JOIN accounts a ON t.account_id = a.id
            JOIN inventory_items i ON t.item_id = i.id
            WHERE t.unit_price <= (t.actual_cost * 1.05) 
            ORDER BY profit_margin_percentage ASC;
        ";
        return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// -------------------------------------------------------------
// 8. تقرير "أداء الموردين" (Supplier Reliability Index)
// -------------------------------------------------------------
function getSupplierPerformance($pdo) {
    try {
        $query = "
            SELECT 
                a.name AS supplier_name,
                COUNT(t.id) AS total_orders_placed,
                SUM(t.total_amount) AS total_business_volume,
                STDDEV(t.unit_price) AS price_volatility_variance 
            FROM transactions t
            JOIN accounts a ON t.account_id = a.id
            WHERE a.type = 'SUPPLIER'
            GROUP BY a.id, a.name
            ORDER BY total_business_volume DESC;
        ";
        return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// -------------------------------------------------------------
// 9. تحليل "السيولة اللحظية المتوقعة" (Live Cash Flow Forecast)
// -------------------------------------------------------------
function getLiveCashFlowForecast($pdo, $current_cash_on_hand) {
    try {
        $query = "
            SELECT 
                :current_cash AS cash_now,
                COALESCE(SUM(CASE WHEN type = 'INBOUND' THEN amount ELSE 0 END), 0) AS upcoming_cheques_collection,
                COALESCE(SUM(CASE WHEN type = 'OUTBOUND' THEN amount ELSE 0 END), 0) AS upcoming_payable_obligations,
                (:current_cash + 
                 COALESCE(SUM(CASE WHEN type = 'INBOUND' THEN amount ELSE 0 END), 0) - 
                 COALESCE(SUM(CASE WHEN type = 'OUTBOUND' THEN amount ELSE 0 END), 0)) AS projected_liquidity_30_days
            FROM `checks`
            WHERE status = 'PENDING' AND due_date BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY);
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['current_cash' => $current_cash_on_hand]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [
            'cash_now' => $current_cash_on_hand,
            'upcoming_cheques_collection' => 0,
            'upcoming_payable_obligations' => 0,
            'projected_liquidity_30_days' => $current_cash_on_hand
        ];
    }
}

// -------------------------------------------------------------
// 10. تقرير "مصفوفة تكرار الزيارة والولاء" (Customer Engagement Grid)
// -------------------------------------------------------------
function getCustomerEngagement($pdo) {
    try {
        $query = "
            SELECT 
                a.name AS customer_name,
                COUNT(t.id) AS total_visit_count,
                SUM(t.total_amount) AS total_spend,
                CASE 
                    WHEN COUNT(t.id) >= 15 THEN 'VIP'
                    WHEN COUNT(t.id) BETWEEN 5 AND 14 THEN 'REGULAR'
                    ELSE 'OCCASIONAL'
                END AS engagement_tier
            FROM transactions t
            JOIN accounts a ON t.account_id = a.id
            WHERE a.type = 'CUSTOMER'
            GROUP BY a.id, a.name
            ORDER BY total_visit_count DESC;
        ";
        return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// -------------------------------------------------------------
// جلب السيولة المجمعة للخزن والبنوك (التقرير الهجين الثاني)
// -------------------------------------------------------------
function getRealTimeLiquidity($pdo) {
    try {
        if (hasCurrentBalanceColumn($pdo)) {
            $query = "
                SELECT 
                    name AS vault_name, 
                    COALESCE(current_balance, 0) AS amount,
                    CASE 
                        WHEN name LIKE '%بنك%' THEN '#3b82f6' 
                        WHEN name LIKE '%خزنة%' THEN '#10b981' 
                        ELSE '#8b5cf6'
                    END AS color
                FROM accounts 
                WHERE type = 'BANK' OR type = 'VAULT'
                ORDER BY current_balance DESC
            ";
        } else {
            $query = "
                SELECT 
                    name AS vault_name, 
                    COALESCE((SELECT SUM(t.total_amount) FROM transactions t WHERE t.account_id = accounts.id), 0) AS amount,
                    CASE 
                        WHEN name LIKE '%بنك%' THEN '#3b82f6' 
                        WHEN name LIKE '%خزنة%' THEN '#10b981' 
                        ELSE '#8b5cf6'
                    END AS color
                FROM accounts 
                WHERE type = 'BANK' OR type = 'VAULT' OR type = 'CUSTOMER' OR type = 'SUPPLIER'
                ORDER BY amount DESC
            ";
        }
        return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// -------------------------------------------------------------
// جلب المديونيات الشاملة (تقرير مديونيات الأرصدة الحقيقي)
// -------------------------------------------------------------
function getCustomerExposureData($pdo) {
    try {
        if (hasCurrentBalanceColumn($pdo)) {
            $query = "
                SELECT 
                    a.id AS customer_id,
                    a.name AS customer_name,
                    COALESCE(a.current_balance, 0) AS ledger_balance,
                    COALESCE(SUM(CASE WHEN c.status = 'PENDING' AND c.type = 'INBOUND' THEN c.amount ELSE 0 END), 0) AS pending_checks_total,
                    (COALESCE(a.current_balance, 0) + COALESCE(SUM(CASE WHEN c.status = 'PENDING' AND c.type = 'INBOUND' THEN c.amount ELSE 0 END), 0)) AS total_exposure
                FROM accounts a
                LEFT JOIN `checks` c ON a.id = c.account_id
                WHERE a.type = 'CUSTOMER'
                GROUP BY a.id, a.name
                ORDER BY total_exposure DESC
            ";
        } else {
            $query = "
                SELECT 
                    a.id AS customer_id,
                    a.name AS customer_name,
                    COALESCE((SELECT SUM(t.total_amount) FROM transactions t WHERE t.account_id = a.id), 0) AS ledger_balance,
                    COALESCE((SELECT SUM(c.amount) FROM `checks` c WHERE c.account_id = a.id AND c.type = 'INBOUND' AND c.status = 'PENDING'), 0) AS pending_checks_total,
                    (COALESCE((SELECT SUM(t.total_amount) FROM transactions t WHERE t.account_id = a.id), 0) + COALESCE((SELECT SUM(c.amount) FROM `checks` c WHERE c.account_id = a.id AND c.type = 'INBOUND' AND c.status = 'PENDING'), 0)) AS total_exposure
                FROM accounts a
                WHERE a.type = 'CUSTOMER'
                GROUP BY a.id, a.name
                ORDER BY total_exposure DESC
            ";
        }
        return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}
?>
