<?php
$page_title = 'حساب مديونية الأرصدة الشامل';
$header_theme_class = 'header-theme-reports';

// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - تقرير حساب مديونية الأرصدة الشامل (دفتر الأستاذ + الشيكات)
// ====================================================================

$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'BOTH'); // SDG, USD, BOTH
$layout_mode = isset($_GET['layout']) ? $_GET['layout'] : 'premium'; // premium, simple

// سعر صرف افتراضي للتثمين
$exchange_rate = 600.0; 

$report_data = [];
$is_mock_data = false;
try {
    require_once __DIR__ . '/db_bridge.php';
    $pdo = getDBConnection();
    if (isset($pdo)) {
        // --- جلب العملاء وأرصدتهم من جدول customers (بنية JSON) ---
        $customers = [];
        $cust_stmt = $pdo->prepare("SELECT id, data FROM customers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
        $cust_stmt->execute([$GLOBAL_TENANT_ID]);
        while ($row = $cust_stmt->fetch()) {
            $d = json_decode($row['data'], true);
            if (!$d) continue;
            $cid = $d['id'] ?? $row['id'];
            $customers[$cid] = [
                'id' => $row['id'],
                'account_name' => $d['name'] ?? 'عميل غير معروف',
                'account_type' => 'CUSTOMER',
                'ledger_balance' => (float)($d['currentBalance'] ?? 0),
                'pending_inbound' => 0.0,
                'pending_outbound' => 0.0
            ];
        }
        
        // --- جلب الموردين وأرصدتهم من جدول suppliers (بنية JSON) ---
        $suppliers = [];
        try {
            $sup_stmt = $pdo->prepare("SELECT id, data FROM suppliers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $sup_stmt->execute([$GLOBAL_TENANT_ID]);
            while ($row = $sup_stmt->fetch()) {
                $d = json_decode($row['data'], true);
                if (!$d) continue;
                $sid = $d['id'] ?? $row['id'];
                $suppliers[$sid] = [
                    'id' => $row['id'],
                    'account_name' => $d['name'] ?? 'مورد غير معروف',
                    'account_type' => 'SUPPLIER',
                    'ledger_balance' => -1 * abs((float)($d['currentBalance'] ?? 0)),
                    'pending_inbound' => 0.0,
                    'pending_outbound' => 0.0
                ];
            }
        } catch (Exception $ex) {}
        
        // --- جلب الشيكات المعلقة وربطها بالعملاء/الموردين ---
        try {
            $chk_stmt = $pdo->query("SELECT data FROM `checks` WHERE tenant_id = '{$GLOBAL_TENANT_ID}' AND (is_deleted = 0 OR is_deleted IS NULL)");
            while ($row = $chk_stmt->fetch()) {
                $c = json_decode($row['data'], true);
                if (!$c || ($c['status'] ?? '') !== 'PENDING') continue;
                $amount = (float)($c['amount'] ?? 0);
                $custId = $c['customerId'] ?? ($c['customer'] ?? '');
                $supId = $c['supplierId'] ?? ($c['supplier'] ?? '');
                
                if (($c['type'] ?? '') === 'INBOUND' && $custId && isset($customers[$custId])) {
                    $customers[$custId]['pending_inbound'] += $amount;
                } elseif (($c['type'] ?? '') === 'OUTBOUND' && $supId && isset($suppliers[$supId])) {
                    $suppliers[$supId]['pending_outbound'] += $amount;
                }
            }
        } catch (Exception $ex) {}
        
        // دمج القوائم
        $report_data = array_merge(array_values($customers), array_values($suppliers));
    }
} catch (Exception $e) {
    // خطأ أو الجداول غير مهيأة
}

// البديل في حال خلو قاعدة البيانات
// Mock data fallback removed for SaaS environment

// حساب المؤشرات الإجمالية
$total_ledger_receivables = 0; // مدين (عملاء)
$total_ledger_payables = 0;    // دائن (موردين)
$total_pending_inbound = 0;
$total_pending_outbound = 0;

foreach ($report_data as $row) {
    if ($row['ledger_balance'] > 0) {
        $total_ledger_receivables += $row['ledger_balance'];
    } else {
        $total_ledger_payables += abs($row['ledger_balance']);
    }
    $total_pending_inbound += $row['pending_inbound'];
    $total_pending_outbound += $row['pending_outbound'];
}

$total_combined_receivables = $total_ledger_receivables + $total_pending_inbound;
$total_combined_payables = $total_ledger_payables + $total_pending_outbound;

// دالة تحويل العملة الموحدة للتقارير
if (!function_exists('convertReportAmount')) {
    function convertReportAmount($amount, $txCurrency, $txRate, $selectedCurrency) {
        if (empty($txCurrency)) $txCurrency = 'SDG';
        if ($txCurrency === $selectedCurrency) {
            return (float)$amount;
        }
        $usdToSdgRate = 600; // السعر القياسي الافتراضي للنظام
        $rate = (float)$txRate;
        if ($rate <= 0) {
            $rate = $usdToSdgRate;
        }
        if ($txCurrency === 'USD' && $selectedCurrency === 'SDG') {
            return (float)$amount * $rate;
        } else if ($txCurrency === 'SDG' && $selectedCurrency === 'USD') {
            return (float)$amount / $rate;
        }
        return (float)$amount;
    }
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js"></script>
<script>
(function(){
    var l = localStorage.getItem("erp_lang") || "ar";
    if(l==="en") {
        document.documentElement.setAttribute("dir","ltr");
        document.documentElement.setAttribute("lang","en");
    }
})();
</script>

<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;800&family=Cairo:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">

    <meta charset="UTF-8">
    <title>تقرير مديونية الأرصدة الشامل | Al-Sajana ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden;
        }
        .page-container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 0px 30px 20px 30px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        body {
            font-family: 'Cairo', sans-serif;
        }
        <?php if ($layout_mode === 'premium'): ?>
        body {
            background-color: #06040d;
            background-image: radial-gradient(circle at top right, rgba(99, 102, 241, 0.08), transparent 45%),
                              radial-gradient(circle at bottom left, rgba(236, 72, 153, 0.08), transparent 45%);
            background-attachment: fixed;
        }
        .premium-card {
            background: rgba(18, 14, 30, 0.7);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
        }
        <?php else: ?>
        body {
            background-color: #f8fafc;
        }
        <?php endif; ?>
    </style>
</head>
<body class="<?php echo $layout_mode === 'premium' ? 'text-slate-100' : 'text-slate-800'; ?> min-h-screen">
    <div class="page-container">
        <?php include "includes/header.php"; ?>

    <div class="max-w-7xl mx-auto">

        <?php if ($is_mock_data): ?>
        <div class="bg-amber-900/30 border border-amber-500/30 text-amber-300 px-4 py-2 rounded-xl text-sm text-center mb-6 font-bold">
            ⚠️ الشاشة تعرض بيانات تجريبية حالياً — سيتم عرض البيانات الحقيقية عند توفر عمليات بيعية في النظام
        </div>
        <?php endif; ?>

        <!-- هيدر الصفحة الرئيسي -->
        

        <?php if ($layout_mode === 'premium'): ?>
        <!-- ==================== العرض العالمي الفخم ==================== -->
        
                <!-- شريط التحكم الفوري والفلترة والطباعة -->
        <div class="premium-card p-5 rounded-2xl mb-6 flex flex-wrap justify-between items-center gap-4 no-print">
            <div>
                <h1 class="text-xl font-black text-sky-400 flex items-center gap-2 m-0">
                    <span>⚖️</span> حساب مديونية الأرصدة الشامل وتقييم المخاطرة (الأستاذ + الشيكات)
                </h1>
                <p class="text-xs text-slate-400 mt-1 m-0">يجمع بين رصيد العميل/المورد في دفتر الأستاذ العام والشيكات المعلقة بالعملتين المحلية (SDG) والأجنبية (USD) مع تقييم حالة الأمان</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <input type="text" id="cr-search-input" oninput="window.filterCombinedReceivables()" placeholder="🔍 ابحث باسم العميل أو المورد..." class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-900/80 border border-slate-700 text-slate-100 outline-none" style="min-width:210px;">
                <select id="cr-type-filter" onchange="window.filterCombinedReceivables()" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-900/80 border border-slate-700 text-slate-100 outline-none">
                    <option value="ALL">الكل (عملاء + موردين)</option>
                    <option value="CUSTOMER">👥 العملاء فقط</option>
                    <option value="SUPPLIER">🛍️ الموردين فقط</option>
                </select>
                <div class="flex bg-slate-900/80 rounded-xl p-1 border border-slate-700">
                    <a href="?currency=BOTH&layout=<?php echo htmlspecialchars($layout_mode); ?>" class="px-2.5 py-1 rounded-lg text-xs font-bold <?php echo $selected_currency === 'BOTH' ? 'bg-sky-500 text-slate-950' : 'text-slate-400'; ?>">SDG + USD</a>
                    <a href="?currency=SDG&layout=<?php echo htmlspecialchars($layout_mode); ?>" class="px-2.5 py-1 rounded-lg text-xs font-bold <?php echo $selected_currency === 'SDG' ? 'bg-sky-500 text-slate-950' : 'text-slate-400'; ?>">SDG</a>
                    <a href="?currency=USD&layout=<?php echo htmlspecialchars($layout_mode); ?>" class="px-2.5 py-1 rounded-lg text-xs font-bold <?php echo $selected_currency === 'USD' ? 'bg-sky-500 text-slate-950' : 'text-slate-400'; ?>">USD</a>
                </div>
                <button type="button" onclick="window.print()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold cursor-pointer border-0">🖨️ طباعة</button>
                <a href="aging-report.php" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold no-underline">⏳ أعمار الديون وسقوف الائتمان</a> <a href="smart_reports_hub.php" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 border border-slate-600 text-slate-200 rounded-xl text-xs font-bold no-underline">↩️ البوابة</a>
            </div>
        </div>

        <!-- كروت المؤشرات المضيئة -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- 1. إجمالي مديونية العملاء -->
            <div class="premium-card p-6 rounded-2xl">
                <div class="flex justify-between items-start mb-4">
                    <span class="text-xs font-bold text-slate-400">إجمالي ديون العملاء (دفتر أستاذ)</span>
                    <span class="text-emerald-400 bg-emerald-500/10 text-xs px-2 py-0.5 rounded-md font-bold">نشط</span>
                </div>
                <h3 class="text-2xl font-black text-emerald-400">
                    <?php 
                    if ($selected_currency === 'SDG') echo number_format($total_ledger_receivables, 2) . ' SDG';
                    elseif ($selected_currency === 'USD') echo number_format($total_ledger_receivables / $exchange_rate, 2) . ' USD';
                    else echo number_format($total_ledger_receivables, 0) . ' SDG <span class="text-xs block text-slate-400 mt-1">' . number_format($total_ledger_receivables / $exchange_rate, 2) . ' USD</span>';
                    ?>
                </h3>
                <p class="text-[11px] text-slate-400 mt-2">مجموع المبالغ المستحقة على العملاء حالياً.</p>
            </div>

            <!-- 2. الشيكات الواردة المعلقة -->
            <div class="premium-card p-6 rounded-2xl">
                <div class="flex justify-between items-start mb-4">
                    <span class="text-xs font-bold text-slate-400">شيكات واردة تحت مقاصة</span>
                    <span class="text-indigo-400 bg-indigo-500/10 text-xs px-2 py-0.5 rounded-md font-bold">معلقة</span>
                </div>
                <h3 class="text-2xl font-black text-indigo-400">
                    <?php 
                    if ($selected_currency === 'SDG') echo number_format($total_pending_inbound, 2) . ' SDG';
                    elseif ($selected_currency === 'USD') echo number_format($total_pending_inbound / $exchange_rate, 2) . ' USD';
                    else echo number_format($total_pending_inbound, 0) . ' SDG <span class="text-xs block text-slate-400 mt-1">' . number_format($total_pending_inbound / $exchange_rate, 2) . ' USD</span>';
                    ?>
                </h3>
                <p class="text-[11px] text-slate-400 mt-2">شيكات عملاء لم تدخل الصندوق الفعلي بعد.</p>
            </div>

            <!-- 3. المديونية الشاملة على العملاء -->
            <div class="premium-card p-6 rounded-2xl border-indigo-500/30 ring-1 ring-indigo-500/20">
                <div class="flex justify-between items-start mb-4">
                    <span class="text-xs font-bold text-slate-400">المديونية الشاملة على العملاء</span>
                    <span class="text-rose-400 bg-rose-500/10 text-xs px-2 py-0.5 rounded-md font-bold">المخاطر الشاملة</span>
                </div>
                <h3 class="text-2xl font-black text-rose-400">
                    <?php 
                    if ($selected_currency === 'SDG') echo number_format($total_combined_receivables, 2) . ' SDG';
                    elseif ($selected_currency === 'USD') echo number_format($total_combined_receivables / $exchange_rate, 2) . ' USD';
                    else echo number_format($total_combined_receivables, 0) . ' SDG <span class="text-xs block text-slate-400 mt-1">' . number_format($total_combined_receivables / $exchange_rate, 2) . ' USD</span>';
                    ?>
                </h3>
                <p class="text-[11px] text-slate-400 mt-2">حساب مديونية الأرصدة بالكامل (أستاذ + شيكات).</p>
            </div>

            <!-- 4. المديونية الشاملة للموردين -->
            <div class="premium-card p-6 rounded-2xl">
                <div class="flex justify-between items-start mb-4">
                    <span class="text-xs font-bold text-slate-400">إجمالي الالتزامات للموردين</span>
                    <span class="text-amber-400 bg-amber-500/10 text-xs px-2 py-0.5 rounded-md font-bold">دائن</span>
                </div>
                <h3 class="text-2xl font-black text-amber-400">
                    <?php 
                    if ($selected_currency === 'SDG') echo number_format($total_combined_payables, 2) . ' SDG';
                    elseif ($selected_currency === 'USD') echo number_format($total_combined_payables / $exchange_rate, 2) . ' USD';
                    else echo number_format($total_combined_payables, 0) . ' SDG <span class="text-xs block text-slate-400 mt-1">' . number_format($total_combined_payables / $exchange_rate, 2) . ' USD</span>';
                    ?>
                </h3>
                <p class="text-[11px] text-slate-400 mt-2">شامل أرصدة الموردين والشيكات الصادرة المعلقة.</p>
            </div>
        </div>

        <!-- جدول البيانات العالمي -->
        <div class="premium-card rounded-2xl overflow-hidden mb-8">
            <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-lg">📊 سجل حساب مديونية الأرصدة الشامل للشركاء</h3>
                <span class="text-xs text-indigo-400"><i class="fa-solid fa-circle-info mr-1"></i> تظهر المبالغ السالبة كالتزامات دفع لصالح الموردين</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead>
                        <tr class="bg-slate-900/60 border-b border-slate-800 text-slate-400">
                            <th class="p-4 font-bold">الكيان / الحساب</th>
                            <th class="p-4 font-bold text-center">نوع الحساب</th>
                            <th class="p-4 font-bold">رصيد دفتر الأستاذ</th>
                            <th class="p-4 font-bold text-emerald-400">شيكات واردة (معلقة)</th>
                            <th class="p-4 font-bold text-amber-400">شيكات صادرة (معلقة)</th>
                            <th class="p-4 font-bold text-indigo-300">المديونية الشاملة</th>
                            <th class="p-4 font-bold text-center">تقييم المخاطرة</th>
                            <th class="p-4 font-bold text-center no-print">إجراء</th>
                        </tr>
                    </thead>
                    <tbody id="cr-tbody" class="divide-y divide-slate-800/50">
                        <?php foreach ($report_data as $row): ?>
                        <tr class="hover:bg-slate-800/20 transition-colors">
                            <!-- اسم الحساب -->
                            <td class="p-4 font-bold text-slate-100 flex items-center gap-3">
                                <span class="w-2 h-2 rounded-full <?php echo $row['account_type'] === 'CUSTOMER' ? 'bg-emerald-500' : 'bg-amber-500'; ?>"></span>
                                <?php echo $row['account_name']; ?>
                            </td>
                            <!-- نوع الحساب -->
                            <td class="p-4 text-center">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold <?php echo $row['account_type'] === 'CUSTOMER' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'; ?>">
                                    <?php echo $row['account_type'] === 'CUSTOMER' ? 'عميل' : 'مورد'; ?>
                                </span>
                            </td>
                            <!-- رصيد دفتر الأستاذ -->
                            <td class="p-4">
                                <?php if ($selected_currency === 'SDG'): ?>
                                    <span class="font-bold <?php echo $row['ledger_balance'] >= 0 ? 'text-slate-100' : 'text-amber-500'; ?>">
                                        <?php echo number_format($row['ledger_balance'], 2); ?>
                                    </span>
                                <?php elseif ($selected_currency === 'USD'): ?>
                                    <span class="font-bold <?php echo $row['ledger_balance'] >= 0 ? 'text-slate-100' : 'text-amber-500'; ?>">
                                        <?php echo number_format($row['ledger_balance'] / $exchange_rate, 2); ?>
                                    </span>
                                <?php else: ?>
                                    <div class="font-bold <?php echo $row['ledger_balance'] >= 0 ? 'text-slate-100' : 'text-amber-500'; ?>">
                                        <?php echo number_format($row['ledger_balance'], 0); ?> <span class="text-xs font-normal text-slate-400">SDG</span>
                                    </div>
                                    <div class="text-xs text-slate-400 mt-0.5">
                                        <?php echo number_format($row['ledger_balance'] / $exchange_rate, 2); ?> USD
                                    </div>
                                <?php endif; ?>
                            </td>
                            <!-- شيكات واردة -->
                            <td class="p-4 text-emerald-400 font-bold">
                                <?php 
                                if ($selected_currency === 'SDG') echo number_format($row['pending_inbound'], 2);
                                elseif ($selected_currency === 'USD') echo number_format($row['pending_inbound'] / $exchange_rate, 2);
                                else echo number_format($row['pending_inbound'], 0) . ' <span class="text-xs text-slate-400">SDG</span> <span class="block text-xs font-normal text-slate-400 mt-0.5">' . number_format($row['pending_inbound'] / $exchange_rate, 2) . ' USD</span>';
                                ?>
                            </td>
                            <!-- شيكات صادرة -->
                            <td class="p-4 text-amber-500 font-bold">
                                <?php 
                                if ($selected_currency === 'SDG') echo number_format($row['pending_outbound'], 2);
                                elseif ($selected_currency === 'USD') echo number_format($row['pending_outbound'] / $exchange_rate, 2);
                                else echo number_format($row['pending_outbound'], 0) . ' <span class="text-xs text-slate-400">SDG</span> <span class="block text-xs font-normal text-slate-400 mt-0.5">' . number_format($row['pending_outbound'] / $exchange_rate, 2) . ' USD</span>';
                                ?>
                            </td>
                            <!-- المديونية الشاملة -->
                            <td class="p-4 font-black">
                                <?php 
                                // حاصل الأستاذ + شيكات واردة - شيكات صادرة
                                $combined = $row['ledger_balance'] + $row['pending_inbound'] - $row['pending_outbound'];
                                ?>
                                <?php if ($selected_currency === 'SDG'): ?>
                                    <span class="<?php echo $combined >= 0 ? 'text-indigo-400' : 'text-amber-500'; ?>">
                                        <?php echo ($combined >= 0 ? '+' : '') . number_format($combined, 2); ?>
                                    </span>
                                <?php elseif ($selected_currency === 'USD'): ?>
                                    <span class="<?php echo $combined >= 0 ? 'text-indigo-400' : 'text-amber-500'; ?>">
                                        <?php echo ($combined >= 0 ? '+' : '') . number_format($combined / $exchange_rate, 2); ?>
                                    </span>
                                <?php else: ?>
                                    <div class="<?php echo $combined >= 0 ? 'text-indigo-400' : 'text-amber-500'; ?>">
                                        <?php echo ($combined >= 0 ? '+' : '') . number_format($combined, 0); ?> <span class="text-xs font-normal text-slate-400">SDG</span>
                                    </div>
                                    <div class="text-xs text-slate-400 mt-0.5">
                                        <?php echo ($combined >= 0 ? '+' : '') . number_format($combined / $exchange_rate, 2); ?> USD
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php else: ?>
        <!-- ==================== العرض المحاسبي البسيط (أعمدة عادية) ==================== -->
        
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 overflow-hidden">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-lg text-slate-800"><i class="fa-solid fa-table-list mr-1 text-slate-600"></i> جدول مديونية الأرصدة الشامل للشركاء (عرض مبسط)</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm text-slate-700">
                    <thead>
                        <tr class="bg-slate-100 border-b border-slate-300 text-slate-600 font-bold">
                            <th class="p-3 border">الاسم الحسابي</th>
                            <th class="p-3 border text-center">النوع</th>
                            <th class="p-3 border">رصيد دفتر الأستاذ</th>
                            <th class="p-3 border">الشيكات الواردة المعلقة (+)</th>
                            <th class="p-3 border">الشيكات الصادرة المعلقة (-)</th>
                            <th class="p-3 border bg-slate-200">المديونية الشاملة النهائية</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report_data as $row): ?>
                        <tr class="hover:bg-slate-50 border-b border-slate-200">
                            <!-- الاسم -->
                            <td class="p-3 border font-semibold text-slate-900"><?php echo $row['account_name']; ?></td>
                            
                            <!-- النوع -->
                            <td class="p-3 border text-center font-medium">
                                <?php echo $row['account_type'] === 'CUSTOMER' ? 'عميل' : 'مورد'; ?>
                            </td>
                            
                            <!-- دفتر الأستاذ -->
                            <td class="p-3 border font-mono">
                                <?php if ($selected_currency === 'SDG'): ?>
                                    <?php echo number_format($row['ledger_balance'], 2); ?> SDG
                                <?php elseif ($selected_currency === 'USD'): ?>
                                    <?php echo number_format($row['ledger_balance'] / $exchange_rate, 2); ?> USD
                                <?php else: ?>
                                    <?php echo number_format($row['ledger_balance'], 0); ?> SDG | <?php echo number_format($row['ledger_balance'] / $exchange_rate, 2); ?> USD
                                <?php endif; ?>
                            </td>
                            
                            <!-- شيكات واردة -->
                            <td class="p-3 border font-mono text-emerald-600 font-bold">
                                <?php if ($selected_currency === 'SDG'): ?>
                                    <?php echo number_format($row['pending_inbound'], 2); ?> SDG
                                <?php elseif ($selected_currency === 'USD'): ?>
                                    <?php echo number_format($row['pending_inbound'] / $exchange_rate, 2); ?> USD
                                <?php else: ?>
                                    <?php echo number_format($row['pending_inbound'], 0); ?> SDG | <?php echo number_format($row['pending_inbound'] / $exchange_rate, 2); ?> USD
                                <?php endif; ?>
                            </td>
                            
                            <!-- شيكات صادرة -->
                            <td class="p-3 border font-mono text-rose-600 font-bold">
                                <?php if ($selected_currency === 'SDG'): ?>
                                    <?php echo number_format($row['pending_outbound'], 2); ?> SDG
                                <?php elseif ($selected_currency === 'USD'): ?>
                                    <?php echo number_format($row['pending_outbound'] / $exchange_rate, 2); ?> USD
                                <?php else: ?>
                                    <?php echo number_format($row['pending_outbound'], 0); ?> SDG | <?php echo number_format($row['pending_outbound'] / $exchange_rate, 2); ?> USD
                                <?php endif; ?>
                            </td>
                            
                            <!-- المديونية الشاملة -->
                            <td class="p-3 border font-mono font-bold bg-slate-50 text-indigo-700">
                                <?php 
                                $combined = $row['ledger_balance'] + $row['pending_inbound'] - $row['pending_outbound'];
                                ?>
                                <?php if ($selected_currency === 'SDG'): ?>
                                    <?php echo number_format($combined, 2); ?> SDG
                                <?php elseif ($selected_currency === 'USD'): ?>
                                    <?php echo number_format($combined / $exchange_rate, 2); ?> USD
                                <?php else: ?>
                                    <?php echo number_format($combined, 0); ?> SDG | <?php echo number_format($combined / $exchange_rate, 2); ?> USD
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php endif; ?>

    </div>

    </div>
    <script defer src="js/app.js?v=20261016_v28"></script>
    <style>
        html[data-theme="warm_cream"] body, html[data-theme="ultra_white"] body, html[data-theme="exec_gray"] body {
            background: var(--bg-primary, #f8fafc) !important;
            color: #0f172a !important;
        }
        html[data-theme="warm_cream"] .premium-card, html[data-theme="ultra_white"] .premium-card, html[data-theme="exec_gray"] .premium-card {
            background: #ffffff !important;
            color: #0f172a !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: 0 4px 14px rgba(0,0,0,0.05) !important;
        }
        html[data-theme="warm_cream"] .text-slate-100, html[data-theme="ultra_white"] .text-slate-100, html[data-theme="exec_gray"] .text-slate-100 {
            color: #0f172a !important;
        }
        @media print { .no-print { display: none !important; } }
    </style>
    <script>
    (function() {
        const selectedCur = <?php echo json_encode($selected_currency); ?>;
        const rate = parseFloat(window.SYSTEM_USD_RATE || localStorage.getItem('erp_usd_rate') || 600);

        function fmt(n, d = 0) {
            return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d });
        }

        function buildHybridData() {
            const tbody = document.getElementById('cr-tbody');
            if (!tbody) return;

            const custs = (JSON.parse(localStorage.getItem('erp_customers_v1') || '[]')).filter(x => !x.deleted);
            const supps = (JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]')).filter(x => !x.deleted);
            const checks = (JSON.parse(localStorage.getItem('erp_checks_v1') || '[]')).filter(x => !x.deleted);

            if (custs.length === 0 && supps.length === 0 && tbody.children.length > 0) return;

            const map = new Map();
            custs.forEach(c => {
                const bal = parseFloat(c.currentBalance ?? c.balance ?? c.ledger ?? 0) || 0;
                map.set('C_' + c.id, {
                    id: c.id,
                    name: c.name || 'عميل',
                    phone: c.phone || '',
                    type: 'CUSTOMER',
                    creditLimit: parseFloat(c.creditLimit || 0) || 0,
                    ledger: bal,
                    inChecks: parseFloat(c.checks || 0) || 0,
                    outChecks: 0
                });
            });
            supps.forEach(s => {
                const bal = parseFloat(s.currentBalance ?? s.balance ?? 0) || 0;
                map.set('S_' + s.id, {
                    id: s.id,
                    name: s.name || 'مورد',
                    phone: s.phone || '',
                    type: 'SUPPLIER',
                    creditLimit: 0,
                    ledger: -Math.abs(bal),
                    inChecks: 0,
                    outChecks: 0
                });
            });
            checks.forEach(chk => {
                const st = String(chk.status || '').toUpperCase();
                if (st && st !== 'PENDING' && st !== 'معلق' && st !== 'تحت التحصيل') return;
                const amt = parseFloat(chk.amount || 0) || 0;
                const cid = chk.customerId || chk.customer;
                const sid = chk.supplierId || chk.supplier;
                if (cid && map.has('C_' + cid)) map.get('C_' + cid).inChecks += amt;
                if (sid && map.has('S_' + sid)) map.get('S_' + sid).outChecks += amt;
            });

            const rows = Array.from(map.values()).filter(r => Math.abs(r.ledger) > 0 || r.inChecks > 0 || r.outChecks > 0);
            if (rows.length === 0) return;

            let totRec = 0, totPay = 0, totIn = 0, totOut = 0;
            rows.forEach(r => {
                if (r.ledger > 0) totRec += r.ledger;
                else totPay += Math.abs(r.ledger);
                totIn += r.inChecks;
                totOut += r.outChecks;
            });

            const h3s = document.querySelectorAll('.premium-card h3.text-2xl');
            const renderAmt = (val) => {
                if (selectedCur === 'SDG') return fmt(val, 0) + ' SDG';
                if (selectedCur === 'USD') return fmt(val / rate, 2) + ' USD';
                return fmt(val, 0) + ' SDG <span class="text-xs block text-slate-400 mt-1">' + fmt(val / rate, 2) + ' USD</span>';
            };
            if (h3s.length >= 4) {
                h3s[0].innerHTML = renderAmt(totRec);
                h3s[1].innerHTML = renderAmt(totIn);
                h3s[2].innerHTML = renderAmt(totRec + totIn);
                h3s[3].innerHTML = renderAmt(totPay + totOut);
            }

            const renderCell = (val) => {
                if (selectedCur === 'SDG') return fmt(val, 0) + ' SDG';
                if (selectedCur === 'USD') return fmt(val / rate, 2) + ' USD';
                return `<div>${fmt(val, 0)} <span class="text-xs text-slate-400">SDG</span></div><div class="text-xs text-slate-400">${fmt(val / rate, 2)} USD</div>`;
            };

            tbody.innerHTML = rows.map(r => {
                const combined = r.ledger + r.inChecks - r.outChecks;
                let riskBadge = '<span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">✓ آمن ومستقر</span>';
                if (r.type === 'CUSTOMER') {
                    if ((r.creditLimit > 0 && combined > r.creditLimit) || combined > rate * 4000) {
                        riskBadge = '<span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-rose-500/15 text-rose-400 border border-rose-500/30">🚨 مخاطرة عالية</span>';
                    } else if (combined > rate * 1500) {
                        riskBadge = '<span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-amber-500/15 text-amber-400 border border-amber-500/30">⚠️ انتباه / متوسط</span>';
                    }
                }
                const stmtHref = `account-statement.php?id=${encodeURIComponent(r.id)}&type=${r.type.toLowerCase()}`;
                return `
                    <tr class="cr-row hover:bg-slate-800/20 transition-colors" data-type="${r.type}" data-name="${r.name.toLowerCase()}">
                        <td class="p-4 font-bold text-slate-100 flex items-center gap-3">
                            <span class="w-2.5 h-2.5 rounded-full ${r.type === 'CUSTOMER' ? 'bg-emerald-500' : 'bg-amber-500'}"></span>
                            ${r.name}
                        </td>
                        <td class="p-4 text-center">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold ${r.type === 'CUSTOMER' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'}">
                                ${r.type === 'CUSTOMER' ? 'عميل' : 'مورد'}
                            </span>
                        </td>
                        <td class="p-4 font-bold">${renderCell(r.ledger)}</td>
                        <td class="p-4 text-emerald-400 font-bold">${renderCell(r.inChecks)}</td>
                        <td class="p-4 text-amber-500 font-bold">${renderCell(r.outChecks)}</td>
                        <td class="p-4 font-black ${combined >= 0 ? 'text-indigo-400' : 'text-amber-500'}">${renderCell(combined)}</td>
                        <td class="p-4 text-center">${riskBadge}</td>
                        <td class="p-4 text-center no-print">
                            <a href="${stmtHref}" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-sky-500/15 text-sky-400 border border-sky-500/30 no-underline">📑 كشف حساب</a>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        window.filterCombinedReceivables = function() {
            const q = (document.getElementById('cr-search-input')?.value || '').trim().toLowerCase();
            const tf = document.getElementById('cr-type-filter')?.value || 'ALL';
            document.querySelectorAll('#cr-tbody tr').forEach(tr => {
                const name = (tr.getAttribute('data-name') || tr.innerText || '').toLowerCase();
                const tp = tr.getAttribute('data-type') || 'ALL';
                const matchName = !q || name.includes(q);
                const matchType = tf === 'ALL' || tp === tf;
                tr.style.display = (matchName && matchType) ? '' : 'none';
            });
        };

        document.addEventListener('DOMContentLoaded', () => setTimeout(buildHybridData, 150));
        document.addEventListener('DBReady', buildHybridData);
    })();
    </script>
</body>
</html>
