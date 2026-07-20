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
    @include_once __DIR__ . '/api/db.php';
    if (isset($pdo)) {
        // --- جلب العملاء وأرصدتهم من جدول customers (بنية JSON) ---
        $customers = [];
        $cust_stmt = $pdo->query("SELECT id, data FROM customers WHERE is_deleted = 0 OR is_deleted IS NULL");
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
            $sup_stmt = $pdo->query("SELECT id, data FROM suppliers WHERE is_deleted = 0 OR is_deleted IS NULL");
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
    <link rel="stylesheet" href="css/components.css?v=20260626_4">

    <meta charset="UTF-8">
    <title>تقرير مديونية الأرصدة الشامل | Al-Sajana ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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
<body class="<?php echo $layout_mode === 'premium' ? 'text-slate-100' : 'text-slate-800'; ?>
    <div class="page-container">
        <?php include "includes/header.php"; ?> min-h-screen p-6">

    <div class="max-w-7xl mx-auto">

        <?php if ($is_mock_data): ?>
        <div class="bg-amber-900/30 border border-amber-500/30 text-amber-300 px-4 py-2 rounded-xl text-sm text-center mb-6 font-bold">
            ⚠️ الشاشة تعرض بيانات تجريبية حالياً — سيتم عرض البيانات الحقيقية عند توفر عمليات بيعية في النظام
        </div>
        <?php endif; ?>

        <!-- هيدر الصفحة الرئيسي -->
        

        <?php if ($layout_mode === 'premium'): ?>
        <!-- ==================== العرض العالمي الفخم ==================== -->
        
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
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50">
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
    <script defer src="js/app.js"></script>
</body>
</html>
