<?php
// =============================================================
// نظام MAGAIEES ERP 2026 - البوابة المركزية الكاملة لتقارير المخزون الـ 20
// =============================================================
require_once 'db_bridge.php';
$pdo = getDBConnection();

try {
    $report_data = getInventoryReportData($pdo);
    $total_tons = 0;
    $total_profit = 0;
    $critical_count = 0;
    
    foreach ($report_data as $row) {
        $total_tons += $row['stock_qty'];
        $total_profit += ($row['selling_price'] - $row['cost_price']) * $row['sold_qty'];
        if ($row['stock_qty'] <= $row['min_limit']) {
            $critical_count++;
        }
    }
} catch (Exception $e) {
    $total_tons = 0;
    $total_profit = 0;
    $critical_count = 0;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بوابة المخازن والتقارير الاستراتيجية الـ 20 - MAGAIEES ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&family=Tajawal:wght@300;500;700;900&display=swap');
        
        body {
            font-family: 'Tajawal', sans-serif;
            background: radial-gradient(circle at top center, #0d162d 0%, #05080f 100%);
        }

        /* كروت الإحصائيات الفخمة */
        .stat-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.2) 0%, rgba(15, 23, 42, 0.6) 100%);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.35);
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--card-accent);
        }
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--card-accent-alpha);
            box-shadow: 0 12px 30px var(--card-glow);
        }

        /* الأزرار الحضارية */
        .modern-btn {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.04);
            background: linear-gradient(135deg, rgba(20, 30, 48, 0.4) 0%, rgba(15, 23, 42, 0.7) 100%);
            backdrop-filter: blur(12px);
            transition: all 0.45s cubic-bezier(0.25, 0.8, 0.25, 1);
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
            height: 8.5rem !important;
        }
        
        .modern-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -150%;
            width: 50%;
            height: 100%;
            background: linear-gradient(
                to right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.15) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            transform: skewX(-25deg);
            transition: 0.85s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .modern-btn:hover::after {
            left: 150%;
        }

        .modern-btn:hover {
            transform: translateY(-8px) scale(1.05);
            border-color: var(--hover-color);
            box-shadow: 0 15px 35px var(--glow-color);
        }

        .modern-btn span {
            font-size: 1.12rem !important;
            font-weight: 700 !important;
            line-height: 1.5 !important;
            padding: 0 6px;
        }
    </style>
</head>
<body class="text-slate-100 min-h-screen p-6">

    <div class="mb-8 border-b border-slate-800 pb-5 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-emerald-400 flex items-center gap-3">
                <i class="fa-solid fa-warehouse text-cyan-400"></i> لوحة تحكم وتقارير المخازن الذكية الـ 20 الكاملة
            </h1>
            <p class="text-slate-400 text-xs mt-1">المنصة المكتملة لإدارة حركة بضائع السجانة حياً ومباشرة من الداتابيز الحقيقية</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="alerts_manager.php" class="px-5 py-2.5 bg-red-950/40 hover:bg-red-900/40 text-red-400 border border-red-500/20 rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                <i class="fa-solid fa-bell"></i> ضبط تنبيهات المخازن الذكية
            </a>
            <a href="dashboard-pro.php" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 border border-slate-800 rounded-xl text-xs font-bold transition-all shadow-md">
                <i class="fa-solid fa-arrow-right ml-1"></i> لوحة التحكم الرئيسية
            </a>
        </div>
    </div>

    <!-- كروت المؤشرات الكبرى اللحظية الحقيقية -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <!-- كارت 1 -->
        <div class="stat-card p-6 rounded-2xl flex justify-between items-center" style="--card-accent: #06b6d4; --card-accent-alpha: rgba(6,182,212,0.4); --card-glow: rgba(6,182,212,0.15);">
            <div>
                <span class="text-[11px] text-slate-400 font-bold block uppercase tracking-wider">حجم بضاعة العنابر الحالية (حقيقي)</span>
                <span class="text-3xl font-black text-cyan-400 mt-2 block font-mono"><?php echo number_format($total_tons); ?> <span class="text-sm font-normal text-slate-500">طن</span></span>
            </div>
            <div class="p-4 bg-cyan-950/40 text-cyan-400 rounded-2xl text-xl border border-cyan-500/10 shadow-inner"><i class="fa-solid fa-cubes"></i></div>
        </div>
        <!-- كارت 2 -->
        <div class="stat-card p-6 rounded-2xl flex justify-between items-center" style="--card-accent: #10b981; --card-accent-alpha: rgba(16,185,129,0.4); --card-glow: rgba(16,185,129,0.15);">
            <div>
                <span class="text-[11px] text-slate-400 font-bold block uppercase tracking-wider">صافي العائد المالي الفعلي للأصناف</span>
                <span class="text-3xl font-black text-emerald-400 mt-2 block font-mono"><?php echo number_format($total_profit, 2); ?> <span class="text-sm font-normal text-slate-500">SDG</span></span>
            </div>
            <div class="p-4 bg-emerald-950/40 text-emerald-400 rounded-2xl text-xl border border-emerald-500/10 shadow-inner"><i class="fa-solid fa-money-bill-trend-up"></i></div>
        </div>
        <!-- كارت 3 -->
        <div class="stat-card p-6 rounded-2xl flex justify-between items-center" style="--card-accent: #ef4444; --card-accent-alpha: rgba(239,68,68,0.4); --card-glow: rgba(239,68,68,0.15);">
            <div>
                <span class="text-[11px] text-slate-400 font-bold block uppercase tracking-wider">أصناف تحت حد الأمان الأدنى حالياً</span>
                <span class="text-3xl font-black text-red-400 mt-2 block font-mono"><?php echo $critical_count; ?> <span class="text-sm font-normal text-slate-500">صنف حرج</span></span>
            </div>
            <div class="p-4 bg-red-950/40 text-red-400 rounded-2xl text-xl border border-red-500/10 shadow-inner"><i class="fa-solid fa-triangle-exclamation"></i></div>
        </div>
    </div>

    <!-- مصفوفة الأزرار الـ 20 كاملة ومقسمة بالمحاور الأربعة -->
    <div class="space-y-12">
        
        <!-- المحور الأول -->
        <div>
            <h3 class="text-[13px] font-black text-cyan-400 tracking-wider mb-6 border-b border-slate-800/80 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-shield-halved text-cyan-400"></i> المحور الأول: تقارير كميات المخزون وحدود الأمان المالي
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- زر 1 -->
                <a href="inv_rep1_limits.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #22d3ee; --glow-color: rgba(6,182,212,0.25);">
                    <i class="fa-solid fa-check-double text-cyan-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-cyan-400 transition-colors duration-300">1. حدود الأمان الأربعة</span>
                </a>
                <!-- زر 2 -->
                <a href="inv_rep2_coverage.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #22d3ee; --glow-color: rgba(6,182,212,0.25);">
                    <i class="fa-solid fa-hourglass-half text-cyan-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-cyan-400 transition-colors duration-300">2. أيام التغطية للمخزون</span>
                </a>
                <!-- زر 3 -->
                <a href="inv_rep3_deadstock.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #22d3ee; --glow-color: rgba(6,182,212,0.25);">
                    <i class="fa-solid fa-snowflake text-cyan-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-cyan-400 transition-colors duration-300">3. مصفوفة البضاعة الراكدة</span>
                </a>
                <!-- زر 4 -->
                <a href="inv_rep4_committed.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #22d3ee; --glow-color: rgba(6,182,212,0.25);">
                    <i class="fa-solid fa-user-lock text-cyan-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-cyan-400 transition-colors duration-300">4. البضاعة الملتزم بها للعملاء</span>
                </a>
                <!-- زر 5 -->
                <a href="inv_rep5_capacity.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #22d3ee; --glow-color: rgba(6,182,212,0.25);">
                    <i class="fa-solid fa-circle-dot text-cyan-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-cyan-400 transition-colors duration-300">5. الطاقة الاستيعابية للعنابر</span>
                </a>
            </div>
        </div>

        <!-- المحور الثاني -->
        <div>
            <h3 class="text-[13px] font-black text-emerald-400 tracking-wider mb-6 border-b border-slate-800/80 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-rotate text-emerald-400"></i> المحور الثاني: تقارير حركات التشغيل وسرعة الدوران
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- زر 6 -->
                <a href="inv_rep6_turnover.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #34d399; --glow-color: rgba(16,185,129,0.25);">
                    <i class="fa-solid fa-sync text-emerald-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-emerald-400 transition-colors duration-300">6. معدل دوران المخزون</span>
                </a>
                <!-- زر 7 -->
                <a href="inv_rep7_seasonal.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #34d399; --glow-color: rgba(16,185,129,0.25);">
                    <i class="fa-solid fa-cloud-sun text-emerald-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-emerald-400 transition-colors duration-300">7. السحب والطلب الموسمي</span>
                </a>
                <!-- زر 8 -->
                <a href="inv_rep8_flow.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #34d399; --glow-color: rgba(16,185,129,0.25);">
                    <i class="fa-solid fa-exchange-alt text-emerald-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-emerald-400 transition-colors duration-300">8. تدفق الوارد والصادر حياً</span>
                </a>
                <!-- زر 9 -->
                <a href="inv_rep9_leadtime.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #34d399; --glow-color: rgba(16,185,129,0.25);">
                    <i class="fa-solid fa-truck text-emerald-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-emerald-400 transition-colors duration-300">9. زمن توريد المصانع باليوم</span>
                </a>
                <!-- زر 10 -->
                <a href="inv_rep10_fulfillment.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #34d399; --glow-color: rgba(16,185,129,0.25);">
                    <i class="fa-solid fa-check-circle text-emerald-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-emerald-400 transition-colors duration-300">10. دقة طلبات الصرف الفوري</span>
                </a>
            </div>
        </div>

        <!-- المحور الثالث -->
        <div>
            <h3 class="text-[13px] font-black text-amber-400 tracking-wider mb-6 border-b border-slate-800/80 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-money-bill-trend-up text-amber-400"></i> المحور الثالث: تقارير ربحية الصنف ومراقبة الخسائر
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- زر 11 -->
                <a href="inv_rep11_velocity_margin.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #fbbf24; --glow-color: rgba(245,158,11,0.25);">
                    <i class="fa-solid fa-chart-line text-amber-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-amber-400 transition-colors duration-300">11. الربحية مقابل السرعة</span>
                </a>
                <!-- 12 -->
                <a href="inv_rep12_cost_variance.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #fbbf24; --glow-color: rgba(245,158,11,0.25);">
                    <i class="fa-solid fa-compass text-amber-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-amber-400 transition-colors duration-300">12. انحراف التكلفة المرجحة</span>
                </a>
                <!-- زر 13 -->
                <a href="inv_rep13_zero_margin.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #fbbf24; --glow-color: rgba(245,158,11,0.25);">
                    <i class="fa-solid fa-ban text-amber-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-amber-400 transition-colors duration-300">13. الأصناف الخاسرة والحرجة</span>
                </a>
                <!-- زر 14 -->
                <a href="inv_rep14_gmroii.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #fbbf24; --glow-color: rgba(245,158,11,0.25);">
                    <i class="fa-solid fa-coins text-amber-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-amber-400 transition-colors duration-300">14. العائد الاستثماري GMROII</span>
                </a>
                <!-- زر 15 -->
                <a href="inv_rep15_valuation.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #fbbf24; --glow-color: rgba(245,158,11,0.25);">
                    <i class="fa-solid fa-calculator text-amber-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-amber-400 transition-colors duration-300">15. تقييم المخزون المالي اللحظي</span>
                </a>
            </div>
        </div>

        <!-- المحور الرابع -->
        <div>
            <h3 class="text-[13px] font-black text-purple-400 tracking-wider mb-6 border-b border-slate-800/80 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-brain text-purple-400"></i> المحور الرابع: تقارير التنبؤ المستقبلي وحظر المخاطر
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- زر 16 -->
                <a href="inv_rep16_market_drop.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #c084fc; --glow-color: rgba(168,85,247,0.25);">
                    <i class="fa-solid fa-arrow-trend-down text-purple-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-purple-400 transition-colors duration-300">16. خطر هبوط الأسعار المفاجئ</span>
                </a>
                <!-- زر 17 -->
                <a href="inv_rep17_shrinkage.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #c084fc; --glow-color: rgba(168,85,247,0.25);">
                    <i class="fa-solid fa-cut text-purple-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-purple-400 transition-colors duration-300">17. عجز التدقيق وجرد العنابر</span>
                </a>
                <!-- زر 18 -->
                <a href="inv_rep18_transit.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #c084fc; --glow-color: rgba(168,85,247,0.25);">
                    <i class="fa-solid fa-ship text-purple-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-purple-400 transition-colors duration-300">18. طلبيات الشحن والاستيراد</span>
                </a>
                <!-- زر 19 -->
                <a href="inv_rep19_abc.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #c084fc; --glow-color: rgba(168,85,247,0.25);">
                    <i class="fa-solid fa-cubes-stacked text-purple-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-purple-400 transition-colors duration-300">19. تحليل تركيز البضائع ABC</span>
                </a>
                <!-- زر 20 -->
                <a href="inv_rep20_simulator.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #c084fc; --glow-color: rgba(168,85,247,0.25);">
                    <i class="fa-solid fa-magic text-purple-400 text-3xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-sm font-black text-slate-100 group-hover:text-purple-400 transition-colors duration-300">20. محاكي سيناريوهات النواقص</span>
                </a>
            </div>
        </div>

    </div>

</body>
</html>
