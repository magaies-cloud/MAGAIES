<?php
$page_title = 'تقرير مديونيات الأرصدة الاستراتيجي';
$header_theme_class = 'header-theme-reports';

// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - تقرير مديونيات الأرصدة الاستراتيجي
// ====================================================================

// استدعاء جسر الربط الموحد الحقيقي
require_once 'db_bridge.php';

$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$rate = ($selected_currency === 'SDG') ? 600000 : 1000;

// الاتصال المباشر بالقاعدة الحقيقية لجهازك
$mock_mode = false;
$report_data = [];
try {
    $pdo = getDBConnection();
    // جلب بيانات مديونيات العملاء الحقيقية من الخزن والعملاء والشيكات
    $report_data = getCustomerExposureData($pdo);
} catch (Exception $e) {
    $mock_mode = true;
}



// حساب المجاميع الكلية بشكل لحظي فائق السرعة
$total_ledger_all = array_sum(array_column($report_data, 'ledger_balance'));
$total_checks_all = array_sum(array_column($report_data, 'pending_checks_total'));
$total_exposure_all = array_sum(array_column($report_data, 'total_exposure'));

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
    <title>تقرير مديونيات الأرصدة الشملية - السجانة سمارت</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
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

        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen p-6">
    <div class="page-container">
        <?php include "includes/header.php"; ?>

    <div class="mb-8 border-b border-slate-700 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-amber-400 flex items-center gap-2">
                <i class="fa-solid fa-calculator"></i>
                تقرير مديونيات الأرصدة الاستراتيجي (الأستاذ + الشيكات للعملاء)
            </h1>
            <p class="text-slate-400 text-sm mt-1">يعرض الالتزامات المالية الحقيقية الكلية القائمة على كل عميل في السوق بشكل منفصل</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex bg-slate-800 rounded-xl p-1 border border-slate-700">
                <a href="?currency=SDG" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all <?php echo $selected_currency === 'SDG' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-slate-200'; ?>">SDG</a>
                <a href="?currency=USD" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all <?php echo $selected_currency === 'USD' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-slate-200'; ?>">USD</a>
            </div>
            <a href="steel_market_forecast.php?currency=<?php echo $selected_currency; ?>" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-600 rounded-xl text-sm font-bold transition-all">
                <i class="fa-solid fa-chart-line ml-1"></i> الانتقال لتقرير البورصة
            </a>
            <a href="smart_reports_hub.php?currency=<?php echo $selected_currency; ?>" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-600 rounded-xl text-sm font-bold transition-all">
                البوابة <i class="fa-solid fa-arrow-left mr-1"></i>
            </a>
        </div>
    </div>

    <!-- المؤشرات العصرية (العلمية) في الأعلى -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <div class="bg-slate-800/60 p-5 rounded-2xl border border-slate-700/50 flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold block">إجمالي أرصدة دفتر الأستاذ للعملاء</span>
                <span class="text-2xl font-black text-slate-200 mt-1 block"><?php echo number_format($total_ledger_all, 2); ?> <span class="text-xs text-slate-400 font-normal"><?php echo $selected_currency; ?></span></span>
            </div>
            <div class="p-4 bg-blue-950 text-blue-400 rounded-xl text-xl" style="background-color: rgba(59, 130, 246, 0.15);"><i class="fa-solid fa-book"></i></div>
        </div>

        <div class="bg-slate-800/60 p-5 rounded-2xl border border-slate-700/50 flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold block">إجمالي الشيكات المؤجلة المستلمة</span>
                <span class="text-2xl font-black text-emerald-400 mt-1 block"><?php echo number_format($total_checks_all, 2); ?> <span class="text-xs text-slate-400 font-normal"><?php echo $selected_currency; ?></span></span>
            </div>
            <div class="p-4 bg-emerald-950 text-emerald-400 rounded-xl text-xl" style="background-color: rgba(16, 185, 129, 0.15);"><i class="fa-solid fa-money-check-dollar"></i></div>
        </div>

        <div class="bg-amber-955/40 p-5 rounded-2xl border-2 border-amber-500/40 flex items-center justify-between" style="background-color: rgba(245, 158, 11, 0.15);">
            <div>
                <span class="text-xs text-amber-400 font-bold block">⚠️ المديونية الكلية (حجم المخاطرة للعملاء)</span>
                <span class="text-2xl font-black text-amber-300 mt-1 block"><?php echo number_format($total_exposure_all, 2); ?> <span class="text-xs text-slate-400 font-normal"><?php echo $selected_currency; ?></span></span>
            </div>
            <div class="p-4 bg-amber-500 text-slate-900 rounded-xl text-xl font-bold"><i class="fa-solid fa-shield-halved"></i></div>
        </div>

    </div>

    <!-- الجدول الكلاسيكي المعتاد في الأسفل -->
    <div class="bg-slate-800/40 rounded-2xl border border-slate-700 overflow-hidden shadow-md">
        <div class="p-4 bg-slate-800 border-b border-slate-700">
            <h3 class="font-bold text-slate-200">جدول مديونيات الأرصدة التفصيلي (عميل بعد عميل)</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-slate-900/50 text-slate-400 text-xs border-b border-slate-700">
                        <th class="p-4 font-bold">اسم العميل</th>
                        <th class="p-4 font-bold text-left">رصيد دفتر الأستاذ الفعلي</th>
                        <th class="p-4 font-bold text-left text-emerald-400">الشيكات المؤجلة</th>
                        <th class="p-4 font-bold text-left text-amber-400">التوتال (المديونية الشاملة)</th>
                        <th class="p-4 font-bold text-center">تقييم حالة الأمان</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50 text-sm">
                    <?php foreach ($report_data as $row): ?>
                        <tr class="hover:bg-slate-700/30 transition-colors">
                            <td class="p-4 font-bold text-slate-200"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td class="p-4 text-left font-mono"><?php echo number_format($row['ledger_balance'], 2); ?></td>
                            <td class="p-4 text-left font-mono text-emerald-400"><?php echo number_format($row['pending_checks_total'], 2); ?></td>
                            <td class="p-4 text-left font-mono text-amber-300 bg-amber-500/5 font-bold"><?php echo number_format($row['total_exposure'], 2); ?></td>
                            <td class="p-4 text-center">
                                <?php if ($row['total_exposure'] > ($rate * 4.0)): ?>
                                    <span class="px-3 py-1 text-xs font-bold rounded-md bg-red-950 text-red-400 border border-red-500/30">🚨 مخاطرة عالية</span>
                                <?php elseif ($row['total_exposure'] > ($rate * 2.0)): ?>
                                    <span class="px-3 py-1 text-xs font-bold rounded-md bg-amber-950 text-amber-400 border border-amber-500/30">⚠️ انتباه / متوسط</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 text-xs font-bold rounded-md bg-emerald-950 text-emerald-400 border border-emerald-500/30">✓ آمن ومستقر</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    </div>
    <script defer src="js/app.js"></script>
</body>
</html>
