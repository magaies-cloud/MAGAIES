<?php
$page_title = 'تقرير الجرد السنوي الشامل والمركز المالي والزكاة الشرعية';
require_once __DIR__ . '/db_bridge.php';

$pdo = getDBConnection();
global $GLOBAL_TENANT_ID;

// ==========================================
// 1. مديونيات العملاء المجمعة (له / عليه)
// ==========================================
$customers_list = [];
$total_customer_receivables = 0; // له (ديون للشركة عند العملاء)
$total_customer_payables = 0;    // عليه (أرصدة ودفعات مسبقة للعملاء)

try {
    $stmt = $pdo->prepare("SELECT data FROM customers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmt->execute([$GLOBAL_TENANT_ID]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $c = json_decode($row['data'], true);
        if ($c) {
            $bal = (float)($c['currentBalance'] ?? 0);
            $name = $c['name'] ?? 'عميل غير معروف';
            $phone = $c['phone'] ?? '';
            
            $receivable = $bal > 0 ? $bal : 0;
            $payable = $bal < 0 ? abs($bal) : 0;
            
            $total_customer_receivables += $receivable;
            $total_customer_payables += $payable;
            
            $customers_list[] = [
                'id' => $c['id'] ?? '',
                'name' => $name,
                'phone' => $phone,
                'balance' => $bal,
                'receivable' => $receivable,
                'payable' => $payable
            ];
        }
    }
} catch (Exception $e) {
    error_log("Error fetching customers: " . $e->getMessage());
}
$net_customers_balance = $total_customer_receivables - $total_customer_payables;

// ==========================================
// 2. مديونيات الموردين المجمعة (له / عليه)
// ==========================================
$suppliers_list = [];
$total_supplier_payables = 0;    // عليه (مستحقات للموردين)
$total_supplier_receivables = 0; // له (دفعات مسبقة للموردين)

try {
    $stmt = $pdo->prepare("SELECT data FROM suppliers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmt->execute([$GLOBAL_TENANT_ID]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $s = json_decode($row['data'], true);
        if ($s) {
            $bal = (float)($s['currentBalance'] ?? 0);
            $name = $s['name'] ?? 'مورد غير معروف';
            $phone = $s['phone'] ?? '';
            
            $payable = $bal > 0 ? $bal : 0;
            $receivable = $bal < 0 ? abs($bal) : 0;
            
            $total_supplier_payables += $payable;
            $total_supplier_receivables += $receivable;
            
            $suppliers_list[] = [
                'id' => $s['id'] ?? '',
                'name' => $name,
                'phone' => $phone,
                'balance' => $bal,
                'payable' => $payable,
                'receivable' => $receivable
            ];
        }
    }
} catch (Exception $e) {
    error_log("Error fetching suppliers: " . $e->getMessage());
}
$net_suppliers_balance = $total_supplier_payables - $total_supplier_receivables;

// ==========================================
// 3. الأصول الثابتة والإهلاكات (Fixed Assets)
// ==========================================
$fixed_assets_list = [];
$total_fixed_assets_cost = 0;
$total_fixed_assets_depreciation = 0;

try {
    $stmt = $pdo->prepare("SELECT data FROM accounts WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmt->execute([$GLOBAL_TENANT_ID]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $acc = json_decode($row['data'], true);
        if ($acc) {
            $type = strtolower($acc['type'] ?? ($acc['account_type'] ?? ''));
            $code = $acc['code'] ?? ($acc['accountNo'] ?? '');
            if (strpos($type, 'fixed') !== false || strpos($type, 'asset') !== false || strpos($code, '11') === 0) {
                $cost = (float)($acc['cost'] ?? ($acc['currentBalance'] ?? 0));
                $depr = (float)($acc['accumulatedDepreciation'] ?? 0);
                
                $total_fixed_assets_cost += $cost;
                $total_fixed_assets_depreciation += $depr;
                
                $fixed_assets_list[] = [
                    'name' => $acc['name'] ?? 'أصل ثابت',
                    'code' => $code,
                    'cost' => $cost,
                    'depreciation' => $depr,
                    'net' => ($cost - $depr)
                ];
            }
        }
    }
} catch (Exception $e) {
    error_log("Error fetching fixed assets: " . $e->getMessage());
}
$net_fixed_assets = $total_fixed_assets_cost - $total_fixed_assets_depreciation;

// ==========================================
// 4. تقييم وجرد مخازن البضاعة (عروض التجارة)
// ==========================================
$inventory_list = [];
$total_inventory_items = 0;
$total_inventory_cost_value = 0;
$total_inventory_sell_value = 0;

try {
    $stmt = $pdo->prepare("SELECT data FROM items WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmt->execute([$GLOBAL_TENANT_ID]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $item = json_decode($row['data'], true);
        if ($item) {
            $name = $item['name'] ?? 'صنف غير معروف';
            $cat = $item['category'] ?? 'عام';
            $sellingPrice = (float)($item['sellingPrice'] ?? 0);
            $costPrice = (float)($item['openingValue'] ?? ($sellingPrice * 0.8));
            
            $stockQty = 0;
            if (isset($item['units']) && is_array($item['units'])) {
                foreach ($item['units'] as $u) {
                    $qty = (float)($u['stock'] ?? 0);
                    $factor = (float)($u['factor'] ?? 1);
                    if ($factor <= 0) $factor = 1;
                    $stockQty += ($qty * $factor);
                }
            } else {
                $stockQty = (float)($item['openingQty'] ?? 0);
            }
            
            if ($costPrice <= 0 && $sellingPrice > 0) {
                $costPrice = $sellingPrice * 0.8;
            }
            
            $itemCostTotal = $stockQty * $costPrice;
            $itemSellTotal = $stockQty * $sellingPrice;
            
            $total_inventory_items += $stockQty;
            $total_inventory_cost_value += $itemCostTotal;
            $total_inventory_sell_value += $itemSellTotal;
            
            $inventory_list[] = [
                'id' => $item['id'] ?? '',
                'name' => $name,
                'category' => $cat,
                'unit' => $item['baseUnit'] ?? 'قطعة',
                'stock' => $stockQty,
                'cost_price' => $costPrice,
                'sell_price' => $sellingPrice,
                'cost_total' => $itemCostTotal,
                'sell_total' => $itemSellTotal
            ];
        }
    }
} catch (Exception $e) {
    error_log("Error fetching items: " . $e->getMessage());
}

// ==========================================
// السيولة النقدية والبنكية (صناديق وبنوك)
// ==========================================
$total_cash_and_banks = 0;
try {
    $stmt = $pdo->prepare("SELECT data FROM banks WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmt->execute([$GLOBAL_TENANT_ID]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $b = json_decode($row['data'], true);
        if ($b) {
            $bal = (float)($b['currentBalance'] ?? ($b['openingBalance'] ?? 0));
            $total_cash_and_banks += $bal;
        }
    }
} catch (Exception $e) {
    error_log("Error fetching banks: " . $e->getMessage());
}

// ==========================================
// 5. القسم الخامس المجمع: الجدول التجميعي الموحد وحاسبة الزكاة
// ==========================================

// وعاء الزكاة = السيولة + مخزون البضاعة بسعر البيع + ديون العملاء المرجوة (له) - مديونية الموردين المطلوبة (عليه)
$zakat_base = ($total_cash_and_banks + $total_inventory_sell_value + $total_customer_receivables) - $total_supplier_payables;
if ($zakat_base < 0) $zakat_base = 0;

$zakat_rate = 0.025; // 2.5%
$zakat_amount = $zakat_base * $zakat_rate;

$sum_all_receivables_le = $total_customer_receivables + $total_supplier_receivables + $net_fixed_assets + $total_inventory_sell_value + $total_cash_and_banks;
$sum_all_payables_aleh = $total_customer_payables + $total_supplier_payables;
$net_total_capital_all = $sum_all_receivables_le - $sum_all_payables_aleh;
$net_capital_after_zakat = $net_total_capital_all - $zakat_amount;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js"></script>
<script>
(function(){
    var l = localStorage.getItem('erp_lang') || 'ar';
    if(l==='en') {
        document.documentElement.setAttribute('dir','ltr');
        document.documentElement.setAttribute('lang','en');
    }
})();
</script>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>مقاييس (MAGAIEES) - تقرير الجرد السنوي المجمع والزكاة الشرعية</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #0a0e17;
            --panel-bg: rgba(16, 24, 39, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
        }

        :root.light-theme {
            --bg-dark: #f1f5f9;
            --panel-bg: rgba(255, 255, 255, 0.95);
            --text-primary: #000000;
            --text-secondary: #1e293b;
            --glass-border: rgba(0, 0, 0, 0.15);
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-primary);
            font-family: 'Inter', 'Tajawal', sans-serif;
            margin: 0; padding: 0;
            min-height: 100vh;
        }

        .dashboard-container {
            display: flex; flex-direction: column; gap: 24px;
            padding: 20px 30px; box-sizing: border-box; max-width: 1600px; margin: 0 auto;
        }

        .report-section-card {
            background: var(--panel-bg);
            border: 2px solid var(--glass-border);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        :root.light-theme .report-section-card {
            background: #ffffff !important;
            border: 3px solid #000000 !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08) !important;
            color: #000000 !important;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--glass-border);
        }

        :root.light-theme .section-header {
            border-bottom-color: #000000 !important;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-custom {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--glass-border);
        }

        :root.light-theme .table-custom {
            border: 2.5px solid #000000 !important;
        }

        .table-custom th {
            background: rgba(255, 255, 255, 0.06);
            color: var(--text-primary);
            padding: 12px 16px;
            font-weight: 900;
            font-size: 0.95rem;
            text-align: right;
            border-bottom: 2px solid var(--glass-border);
        }

        :root.light-theme .table-custom th {
            background: #e2e8f0 !important;
            color: #000000 !important;
            border-bottom: 2.5px solid #000000 !important;
        }

        .table-custom td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--glass-border);
            font-weight: 700;
            font-size: 0.95rem;
        }

        :root.light-theme .table-custom td {
            border-bottom: 1.5px solid #cbd5e1 !important;
            color: #000000 !important;
        }

        .num-font {
            font-family: 'JetBrains Mono', monospace;
        }

        /* SECTION 5 MASTER TABLE BOX */
        .master-summary-box {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.12), rgba(2, 132, 199, 0.12));
            border: 3px solid #10b981;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 15px 40px rgba(16, 185, 129, 0.15);
        }

        :root.light-theme .master-summary-box {
            background: linear-gradient(135deg, #ecfdf5, #f0f9ff) !important;
            border: 4px solid #047857 !important;
            color: #000000 !important;
        }

        .table-master {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 14px;
            overflow: hidden;
            border: 2px solid #10b981;
        }

        :root.light-theme .table-master {
            border: 3px solid #000000 !important;
        }

        .table-master th {
            background: rgba(16, 185, 129, 0.25);
            color: var(--text-primary);
            padding: 14px 18px;
            font-weight: 900;
            font-size: 1rem;
            text-align: right;
            border-bottom: 2px solid #10b981;
        }

        :root.light-theme .table-master th {
            background: #d1fae5 !important;
            color: #000000 !important;
            border-bottom: 3px solid #000000 !important;
        }

        .table-master td {
            padding: 14px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            font-weight: 700;
            font-size: 0.98rem;
        }

        :root.light-theme .table-master td {
            border-bottom: 2px solid #cbd5e1 !important;
            color: #000000 !important;
        }

        @media print {
            .no-print, .premium-header, sidebar-container { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .dashboard-container { padding: 0 !important; max-width: 100% !important; }
            .report-section-card, .master-summary-box { border: 1px solid #000 !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="no-page-transition">

    <div class="dashboard-container">

        <!-- 🌟 PROPER SYSTEM HEADER 🌟 -->
        <?php $header_theme_class = 'header-theme-reports'; ?>
        <?php include 'includes/header.php'; ?>

        <!-- Action Controls -->
        <div class="flex items-center justify-between gap-4 no-print bg-slate-900/60 dark:bg-slate-900/80 border border-slate-800 p-4 rounded-xl">
            <div>
                <a href="analytics.php" class="inline-flex items-center gap-2 text-sky-400 font-bold hover:underline text-sm mb-1">
                    <i class="fa-solid fa-arrow-right"></i> العودة لشاشة التقارير
                </a>
                <h1 class="text-xl font-black">تقرير الجرد السنوي المجمع والمركز المالي وحساب الزكاة</h1>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl flex items-center gap-2 transition shadow-lg text-sm">
                    <i class="fa-solid fa-print"></i> طباعة التقرير الشامل
                </button>
            </div>
        </div>

        <!-- ==========================================
             1. تقرير مديونية العملاء المجمع (له / عليه)
             ========================================== -->
        <div class="report-section-card" id="sec-1">
            <div class="section-header">
                <h2 class="section-title text-emerald-400">
                    <i class="fa-solid fa-1 text-base p-2 bg-emerald-500/20 rounded-lg"></i>
                    1. تقرير مديونية العملاء المجمع (له وعليه)
                </h2>
                <span class="text-xs font-bold bg-emerald-500/20 text-emerald-400 px-3 py-1.5 rounded-lg border border-emerald-500/30">
                    عدد العملاء: <?php echo count($customers_list); ?>
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>اسم العميل</th>
                            <th>الهاتف</th>
                            <th>المطالبات على العميل (له - مدين)</th>
                            <th>الأرصدة والدفعات المسبقة (عليه - دائن)</th>
                            <th>الرصيد النهائي لكل عميل</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customers_list)): ?>
                            <tr><td colspan="5" class="text-center text-slate-400 py-6">لا يوجد بيانات عملاء مسجلة.</td></tr>
                        <?php else: ?>
                            <?php foreach ($customers_list as $cust): ?>
                                <tr>
                                    <td class="font-bold"><?php echo htmlspecialchars($cust['name']); ?></td>
                                    <td class="num-font text-slate-400"><?php echo htmlspecialchars($cust['phone']); ?></td>
                                    <td class="num-font text-emerald-400 font-bold"><?php echo number_format($cust['receivable'], 2); ?></td>
                                    <td class="num-font text-rose-400 font-bold"><?php echo number_format($cust['payable'], 2); ?></td>
                                    <td class="num-font font-black <?php echo $cust['balance'] >= 0 ? 'text-emerald-400' : 'text-rose-400'; ?>">
                                        <?php echo number_format($cust['balance'], 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-black/20 dark:bg-black/40 font-black text-base">
                            <td colspan="2" class="text-left font-bold">الإجمالي المجمع للعملاء:</td>
                            <td class="num-font text-emerald-400"><?php echo number_format($total_customer_receivables, 2); ?></td>
                            <td class="num-font text-rose-400"><?php echo number_format($total_customer_payables, 2); ?></td>
                            <td class="num-font text-sky-400"><?php echo number_format($net_customers_balance, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- ==========================================
             2. تقرير مديونية الموردين المجمع (له / عليه)
             ========================================== -->
        <div class="report-section-card" id="sec-2">
            <div class="section-header">
                <h2 class="section-title text-rose-400">
                    <i class="fa-solid fa-2 text-base p-2 bg-rose-500/20 rounded-lg"></i>
                    2. تقرير مديونية الموردين المجمع (له وعليه)
                </h2>
                <span class="text-xs font-bold bg-rose-500/20 text-rose-400 px-3 py-1.5 rounded-lg border border-rose-500/30">
                    عدد الموردين: <?php echo count($suppliers_list); ?>
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>اسم المورد</th>
                            <th>الهاتف</th>
                            <th>مستحقات المورد المطلوبة (عليه - دائن)</th>
                            <th>الدفعات المسبقة للمورد (له - مدين)</th>
                            <th>الرصيد النهائي لكل مورد</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($suppliers_list)): ?>
                            <tr><td colspan="5" class="text-center text-slate-400 py-6">لا يوجد بيانات موردين مسجلة.</td></tr>
                        <?php else: ?>
                            <?php foreach ($suppliers_list as $sup): ?>
                                <tr>
                                    <td class="font-bold"><?php echo htmlspecialchars($sup['name']); ?></td>
                                    <td class="num-font text-slate-400"><?php echo htmlspecialchars($sup['phone']); ?></td>
                                    <td class="num-font text-rose-400 font-bold"><?php echo number_format($sup['payable'], 2); ?></td>
                                    <td class="num-font text-emerald-400 font-bold"><?php echo number_format($sup['receivable'], 2); ?></td>
                                    <td class="num-font font-black <?php echo $sup['balance'] >= 0 ? 'text-rose-400' : 'text-emerald-400'; ?>">
                                        <?php echo number_format($sup['balance'], 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-black/20 dark:bg-black/40 font-black text-base">
                            <td colspan="2" class="text-left font-bold">الإجمالي المجمع للموردين:</td>
                            <td class="num-font text-rose-400"><?php echo number_format($total_supplier_payables, 2); ?></td>
                            <td class="num-font text-emerald-400"><?php echo number_format($total_supplier_receivables, 2); ?></td>
                            <td class="num-font text-sky-400"><?php echo number_format($net_suppliers_balance, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- ==========================================
             3. تقرير الأصول الثابتة
             ========================================== -->
        <div class="report-section-card" id="sec-3">
            <div class="section-header">
                <h2 class="section-title text-sky-400">
                    <i class="fa-solid fa-3 text-base p-2 bg-sky-500/20 rounded-lg"></i>
                    3. تقرير الأصول الثابتة الإجمالية (عروض القنية)
                </h2>
                <span class="text-xs font-bold bg-sky-500/20 text-sky-400 px-3 py-1.5 rounded-lg border border-sky-500/30">
                    ✓ مستثناة من وعاء الزكاة شرعاً
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>كود الحساب / الأصل</th>
                            <th>اسم الأصل الثابت</th>
                            <th>القيمة التكلفية بالأصل</th>
                            <th>مجمع الإهلاك السنوي</th>
                            <th>صافي القيمة الدفترية للأصل</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($fixed_assets_list)): ?>
                            <tr><td colspan="5" class="text-center text-slate-400 py-6">لا يوجد أصول ثابتة مسجلة بجدول الحسابات.</td></tr>
                        <?php else: ?>
                            <?php foreach ($fixed_assets_list as $asset): ?>
                                <tr>
                                    <td class="num-font text-slate-400"><?php echo htmlspecialchars($asset['code']); ?></td>
                                    <td class="font-bold"><?php echo htmlspecialchars($asset['name']); ?></td>
                                    <td class="num-font font-bold"><?php echo number_format($asset['cost'], 2); ?></td>
                                    <td class="num-font text-rose-400"><?php echo number_format($asset['depreciation'], 2); ?></td>
                                    <td class="num-font text-sky-400 font-bold"><?php echo number_format($asset['net'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-black/20 dark:bg-black/40 font-black text-base">
                            <td colspan="2" class="text-left font-bold">الإجمالي المجمع للأصول الثابتة:</td>
                            <td class="num-font"><?php echo number_format($total_fixed_assets_cost, 2); ?></td>
                            <td class="num-font text-rose-400"><?php echo number_format($total_fixed_assets_depreciation, 2); ?></td>
                            <td class="num-font text-sky-400"><?php echo number_format($net_fixed_assets, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- ==========================================
             4. تقرير وجرد مخازن البضاعة (عروض التجارة)
             ========================================== -->
        <div class="report-section-card" id="sec-4">
            <div class="section-header">
                <h2 class="section-title text-sky-400">
                    <i class="fa-solid fa-4 text-base p-2 bg-sky-500/20 rounded-lg"></i>
                    4. تقرير وجرد تقييم مخازن البضاعة (عروض التجارة)
                </h2>
                <span class="text-xs font-bold bg-sky-500/20 text-sky-400 px-3 py-1.5 rounded-lg border border-sky-500/30">
                    إجمالي القطع بالمخزن: <?php echo number_format($total_inventory_items); ?>
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>اسم الصنف</th>
                            <th>التصنيف</th>
                            <th>الوحدة</th>
                            <th>الكمية بالمخزن</th>
                            <th>سعر التكلفة</th>
                            <th>سعر البيع المقترح</th>
                            <th>إجمالي التكلفة</th>
                            <th>إجمالي قيمة البيع (عروض التجارة)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inventory_list)): ?>
                            <tr><td colspan="8" class="text-center text-slate-400 py-6">لا يوجد أصناف في المخزن.</td></tr>
                        <?php else: ?>
                            <?php foreach ($inventory_list as $inv): ?>
                                <tr>
                                    <td class="font-bold"><?php echo htmlspecialchars($inv['name']); ?></td>
                                    <td class="text-slate-400"><?php echo htmlspecialchars($inv['category']); ?></td>
                                    <td><?php echo htmlspecialchars($inv['unit']); ?></td>
                                    <td class="num-font font-bold text-amber-400"><?php echo number_format($inv['stock']); ?></td>
                                    <td class="num-font"><?php echo number_format($inv['cost_price'], 2); ?></td>
                                    <td class="num-font text-sky-400"><?php echo number_format($inv['sell_price'], 2); ?></td>
                                    <td class="num-font font-bold"><?php echo number_format($inv['cost_total'], 2); ?></td>
                                    <td class="num-font font-bold text-emerald-400"><?php echo number_format($inv['sell_total'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-black/20 dark:bg-black/40 font-black text-base">
                            <td colspan="3" class="text-left font-bold">إجمالي تقييم المخزون المجمع:</td>
                            <td class="num-font text-amber-400"><?php echo number_format($total_inventory_items); ?></td>
                            <td colspan="2"></td>
                            <td class="num-font"><?php echo number_format($total_inventory_cost_value, 2); ?></td>
                            <td class="num-font text-emerald-400"><?php echo number_format($total_inventory_sell_value, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- ==========================================
             5. القسم الخامس المجمع: الجدول التجميعي الموحد وحاسبة الزكاة
             ========================================== -->
        <div class="master-summary-box" id="sec-5">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-emerald-500/30 pb-4 mb-6">
                <div>
                    <span class="bg-emerald-500 text-white font-black px-3 py-1 rounded-lg text-xs">
                        <i class="fa-solid fa-5"></i> القسم الخامس الموحد
                    </span>
                    <h2 class="text-2xl font-black mt-2">5. الجدول التجميعي الموحد لجميع الأقسام وحسبـة الزكاة الشرعية</h2>
                    <p class="text-xs opacity-85 mt-1">تجمع وتلخيص كافة مديونيات العملاء والموردين والأصول والمخزون في جدول موحد بشريحة (البيان | له | عليه | الإجمالي) مع حساب الزكاة (2.5%).</p>
                </div>
                <div class="text-left dir-ltr">
                    <span class="text-xs opacity-75 font-bold uppercase block">التاريخ والسنة المالية</span>
                    <span class="text-lg font-black num-font text-emerald-400"><?php echo date('Y-m-d H:i'); ?></span>
                </div>
            </div>

            <!-- 📊 MASTER SUMMARY TABLE (الجدول الموحد المطلوب) 📊 -->
            <div class="overflow-x-auto mb-8">
                <table class="table-master">
                    <thead>
                        <tr>
                            <th style="width: 40%;">البيـان (عناصر الجرد والتقرير المالي)</th>
                            <th style="width: 20%;" class="text-emerald-400">المبلغ (له - مدين/أصول)</th>
                            <th style="width: 20%;" class="text-rose-400">المبلغ (عليه - دائن/التزامات)</th>
                            <th style="width: 20%;" class="text-sky-400">الإجمالي (الصافي)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- 1. إجمالي مديونيات العملاء -->
                        <tr>
                            <td class="font-bold">
                                <i class="fa-solid fa-users text-emerald-400 ml-2"></i> 1. إجمالي مديونيات العملاء المجمعة
                            </td>
                            <td class="num-font text-emerald-400 font-bold"><?php echo number_format($total_customer_receivables, 2); ?></td>
                            <td class="num-font text-rose-400 font-bold"><?php echo number_format($total_customer_payables, 2); ?></td>
                            <td class="num-font font-black <?php echo $net_customers_balance >= 0 ? 'text-emerald-400' : 'text-rose-400'; ?>">
                                <?php echo number_format($net_customers_balance, 2); ?>
                            </td>
                        </tr>

                        <!-- 2. إجمالي مديونيات الموردين -->
                        <tr>
                            <td class="font-bold">
                                <i class="fa-solid fa-truck-field text-rose-400 ml-2"></i> 2. إجمالي مديونيات الموردين المجمعة
                            </td>
                            <td class="num-font text-emerald-400 font-bold"><?php echo number_format($total_supplier_receivables, 2); ?></td>
                            <td class="num-font text-rose-400 font-bold"><?php echo number_format($total_supplier_payables, 2); ?></td>
                            <td class="num-font font-black <?php echo $net_suppliers_balance <= 0 ? 'text-rose-400' : 'text-emerald-400'; ?>">
                                <?php echo number_format($net_suppliers_balance, 2); ?>
                            </td>
                        </tr>

                        <!-- 3. تقرير الأصول الثابتة -->
                        <tr>
                            <td class="font-bold">
                                <i class="fa-solid fa-building text-purple-400 ml-2"></i> 3. تقرير الأصول الثابتة الإجمالية (صافي القيمة الدفترية)
                            </td>
                            <td class="num-font text-purple-400 font-bold"><?php echo number_format($net_fixed_assets, 2); ?></td>
                            <td class="num-font text-slate-400">0.00</td>
                            <td class="num-font text-purple-400 font-bold"><?php echo number_format($net_fixed_assets, 2); ?></td>
                        </tr>

                        <!-- 4. تقرير وجرد وتقييم مخازن البضاعة -->
                        <tr>
                            <td class="font-bold">
                                <i class="fa-solid fa-boxes-stacked text-sky-400 ml-2"></i> 4. تقرير وجرد تقييم مخازن البضاعة (عروض التجارة)
                            </td>
                            <td class="num-font text-sky-400 font-bold"><?php echo number_format($total_inventory_sell_value, 2); ?></td>
                            <td class="num-font text-slate-400">0.00</td>
                            <td class="num-font text-sky-400 font-bold"><?php echo number_format($total_inventory_sell_value, 2); ?></td>
                        </tr>

                        <!-- 5. السيولة النقدية والبنكية -->
                        <tr>
                            <td class="font-bold">
                                <i class="fa-solid fa-vault text-amber-400 ml-2"></i> 5. السيولة النقدية والبنكية (الصناديق والحسابات)
                            </td>
                            <td class="num-font text-amber-400 font-bold"><?php echo number_format($total_cash_and_banks, 2); ?></td>
                            <td class="num-font text-slate-400">0.00</td>
                            <td class="num-font text-amber-400 font-bold"><?php echo number_format($total_cash_and_banks, 2); ?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <!-- الإجمالي المجمع النهائي -->
                        <tr class="bg-black/30 dark:bg-black/60 font-black text-base">
                            <td class="font-black text-emerald-400 text-lg">الإجمالي المجمع لكافة بنود التقرير والمركز المالي:</td>
                            <td class="num-font text-emerald-400 text-lg"><?php echo number_format($sum_all_receivables_le, 2); ?></td>
                            <td class="num-font text-rose-400 text-lg"><?php echo number_format($sum_all_payables_aleh, 2); ?></td>
                            <td class="num-font text-sky-400 text-xl font-black"><?php echo number_format($net_total_capital_all, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- ZAKAT FINAL CALCULATION BOX -->
            <div class="pt-6 border-t border-emerald-500/30 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-black/30 dark:bg-black/50 p-5 rounded-xl border border-emerald-500/40 text-center">
                    <div class="text-xs opacity-80 font-bold mb-1">إجمالي وعاء الزكاة الشرعي (الخاضع للزكاة)</div>
                    <div class="text-2xl font-black text-emerald-400 num-font"><?php echo number_format($zakat_base, 2); ?> <span class="text-xs">ج.س</span></div>
                    <div class="text-[11px] opacity-75 mt-1">(السيولة + مخزون البضاعة + ديون العملاء) - ديون الموردين</div>
                </div>

                <div class="bg-black/30 dark:bg-black/50 p-5 rounded-xl border border-amber-500/40 text-center">
                    <div class="text-xs opacity-80 font-bold mb-1">مبلغ الزكاة الشرعية الواجبة (2.5%)</div>
                    <div class="text-2xl font-black text-amber-400 num-font"><?php echo number_format($zakat_amount, 2); ?> <span class="text-xs">ج.س</span></div>
                    <div class="text-[11px] opacity-75 mt-1">الزكاة الواجب إخراجها شرعاً عن العام المالي</div>
                </div>

                <div class="bg-black/30 dark:bg-black/50 p-5 rounded-xl border border-sky-500/40 text-center">
                    <div class="text-xs opacity-80 font-bold mb-1">صافي المال النهائي والمتبقي بعد خصم الزكاة</div>
                    <div class="text-2xl font-black text-sky-400 num-font"><?php echo number_format($net_capital_after_zakat, 2); ?> <span class="text-xs">ج.س</span></div>
                    <div class="text-[11px] opacity-75 mt-1">إجمالي التجمعات المتبقية - حسم الزكاة المحسوبة</div>
                </div>
            </div>
        </div>

    </div>

    <!-- System Scripts for Sidebar & Theme Integration -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?= time() ?>"></script>
</body>
</html>
