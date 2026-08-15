<?php
// =============================================================
// نظام MAGAIEES ERP 2026 - البوابة المركزية الكاملة لتقارير المخزون الـ 20
// =============================================================
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

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
    <title>بوابة المخازن والتقارير الاستراتيجية الـ 20 - MAGAIEES ERP</title>
    <link rel="stylesheet" href="css/design-system.css?v=20260723_v14">
    <link rel="stylesheet" href="css/theme.css?v=20260723_v14">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&family=Tajawal:wght@300;500;700;900&display=swap');
        
        body {
            font-family: 'Inter', 'Tajawal', sans-serif;
            background-color: var(--bg-dark, #050308) !important;
            color: var(--text-primary, #ffffff) !important;
            background-image: radial-gradient(circle at top right, rgba(236,72,153,0.03), transparent 45%),
                              radial-gradient(circle at bottom left, rgba(16,185,129,0.03), transparent 45%) !important;
            margin: 0; padding: 0;
            min-height: 100vh;
        }

        :root.light-theme body {
            background-image: none !important;
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
        }

        .dashboard-container {
            width: 100%;
            max-width: 98%;
            margin: 0 auto;
            padding: 0px 25px 25px 25px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 25px;
            flex: 1;
        }

        /* كروت الإحصائيات الفخمة */
        .stat-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.15) 0%, rgba(15, 23, 42, 0.45) 100%) !important;
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05) !important;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2) !important;
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
            border-color: var(--card-accent-alpha) !important;
            box-shadow: 0 12px 30px var(--card-glow) !important;
        }

        :root.light-theme .stat-card {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05) !important;
        }
        :root.light-theme .stat-card span {
            color: #1e293b !important;
        }
        :root.light-theme .stat-card .text-slate-400 {
            color: #64748b !important;
        }

        /* الأزرار الحضارية */
        .modern-btn {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.04) !important;
            background: linear-gradient(135deg, rgba(20, 30, 48, 0.25) 0%, rgba(15, 23, 42, 0.45) 100%) !important;
            backdrop-filter: blur(12px);
            transition: all 0.45s cubic-bezier(0.25, 0.8, 0.25, 1);
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25) !important;
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
            border-color: var(--hover-color) !important;
            box-shadow: 0 15px 35px var(--glow-color) !important;
        }

        .modern-btn span {
            font-size: 1.12rem !important;
            font-weight: 700 !important;
            line-height: 1.5 !important;
            padding: 0 6px;
        }

        :root.light-theme .modern-btn {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05) !important;
        }
        :root.light-theme .modern-btn span {
            color: #1e293b !important;
        }
        :root.light-theme .modern-btn:hover {
            background: rgba(0, 0, 0, 0.02) !important;
        }

        :root.light-theme h3 {
            border-bottom-color: rgba(0, 0, 0, 0.08) !important;
        }
        :root.light-theme .border-b {
            border-color: rgba(0, 0, 0, 0.08) !important;
        }
        :root.light-theme .text-slate-400 {
            color: #475569 !important;
        }
        :root.light-theme .text-slate-100 {
            color: #1e293b !important;
        }
    </style>
</head>
<body class="text-slate-100 min-h-screen">
    <div class="dashboard-container">
        <?php $page_title = 'بوابة المخازن والتقارير الاستراتيجية الـ 20'; include 'includes/header.php'; ?>

        <div class="mb-8 border-b border-slate-800 pb-5 flex justify-between items-center">
            <div>
                <p class="text-slate-400 text-xs mt-1">المنصة المكتملة لإدارة حركة بضائع السجانة حياً ومباشرة من الداتابيز الحقيقية</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="alerts_manager.php" class="px-5 py-2.5 bg-red-950/40 hover:bg-red-900/40 text-red-400 border border-red-500/20 rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-bell"></i> ضبط تنبيهات المخازن الذكية
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
                    <svg class="w-10 h-10 mb-3 text-cyan-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                    <span class="text-sm font-black text-slate-100 group-hover:text-cyan-400 transition-colors duration-300">1. حدود الأمان الأربعة</span>
                </a>
                <!-- زر 2 -->
                <a href="inv_rep2_coverage.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #22d3ee; --glow-color: rgba(6,182,212,0.25);">
                    <svg class="w-10 h-10 mb-3 text-cyan-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                        <circle cx="12" cy="16" r="3"/>
                        <polyline points="12 14 12 16 13.5 16.5"/>
                    </svg>
                    <span class="text-sm font-black text-slate-100 group-hover:text-cyan-400 transition-colors duration-300">2. أيام التغطية للمخزون</span>
                </a>
                <!-- زر 3 -->
                <a href="inv_rep3_deadstock.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #22d3ee; --glow-color: rgba(6,182,212,0.25);">
                    <svg class="w-10 h-10 mb-3 text-cyan-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="2" x2="12" y2="22"/>
                        <line x1="2" y1="12" x2="22" y2="12"/>
                        <path d="m20 16-4-4 4-4"/>
                        <path d="m4 8 4 4-4 4"/>
                        <path d="m16 4-4 4-4-4"/>
                        <path d="m8 20 4-4 4 4"/>
                    </svg>
                    <span class="text-sm font-black text-slate-100 group-hover:text-cyan-400 transition-colors duration-300">3. مصفوفة البضاعة الراكدة</span>
                </a>
                <!-- زر 4 -->
                <a href="inv_rep4_committed.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #22d3ee; --glow-color: rgba(6,182,212,0.25);">
                    <svg class="w-10 h-10 mb-3 text-cyan-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        <path d="M12 15v3"/>
                    </svg>
                    <span class="text-sm font-black text-slate-100 group-hover:text-cyan-400 transition-colors duration-300">4. البضاعة الملتزم بها للعملاء</span>
                </a>
                <!-- زر 5 -->
                <a href="inv_rep5_capacity.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #22d3ee; --glow-color: rgba(6,182,212,0.25);">
                    <svg class="w-10 h-10 mb-3 text-cyan-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21V10l9-6 9 6v11"/>
                        <path d="M9 21V13h6v8"/>
                    </svg>
                    <span class="text-sm font-black text-slate-100 group-hover:text-cyan-400 transition-colors duration-300">5. الطاقة الاستيعابية للعنابر</span>
                </a>
            </div>
        </div>

        <!-- المحور الثاني -->
        <div>
            <h3 class="text-[13px] font-black text-emerald-400 tracking-wider mb-6 border-b border-slate-800/80 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-arrows-spin text-emerald-400"></i> المحور الثاني: تقارير حركات التشغيل وسرعة الدوران
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- زر 6 -->
                <a href="inv_rep6_turnover.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #34d399; --glow-color: rgba(16,185,129,0.25);">
                    <svg class="w-10 h-10 mb-3 text-emerald-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                    </svg>
                    <span class="text-sm font-black text-slate-100 group-hover:text-emerald-400 transition-colors duration-300">6. معدل دوران المخزون</span>
                </a>
                <!-- زر 7 -->
                <a href="inv_rep7_seasonal.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #34d399; --glow-color: rgba(16,185,129,0.25);">
                    <svg class="w-10 h-10 mb-3 text-emerald-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                        <path d="M16 13a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"/>
                    </svg>
                    <span class="text-sm font-black text-slate-100 group-hover:text-emerald-400 transition-colors duration-300">7. السحب والطلب الموسمي</span>
                </a>
                <!-- زر 8 -->
                <a href="inv_rep8_flow.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #34d399; --glow-color: rgba(16,185,129,0.25);">
                    <svg class="w-10 h-10 mb-3 text-emerald-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m17 3 4 4-4 4M21 7H3M7 21l-4-4 4-4M3 17h18"/>
                    </svg>
                    <span class="text-sm font-black text-slate-100 group-hover:text-emerald-400 transition-colors duration-300">8. تدفق الوارد والصادر حياً</span>
                </a>
                <!-- زر 9 -->
                <a href="inv_rep9_leadtime.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #34d399; --glow-color: rgba(16,185,129,0.25);">
                    <svg class="w-10 h-10 mb-3 text-emerald-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="3" width="15" height="13"/>
                        <polygon points="16 8 20 8 23 11 23 16 16 16"/>
                        <circle cx="5.5" cy="18.5" r="2.5"/>
                        <circle cx="18.5" cy="18.5" r="2.5"/>
                    </svg>
                    <span class="text-sm font-black text-slate-100 group-hover:text-emerald-400 transition-colors duration-300">9. زمن توريد المصانع باليوم</span>
                </a>
                <!-- زر 10 -->
                <a href="inv_rep10_fulfillment.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #34d399; --glow-color: rgba(16,185,129,0.25);">
                    <svg class="w-10 h-10 mb-3 text-emerald-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                        <polyline points="9 11 11 13 15 9"/>
                    </svg>
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
                    <svg class="w-10 h-10 mb-3 text-amber-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 3v18h18M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/>
                    </svg>
                    <span class="text-sm font-black text-slate-100 group-hover:text-amber-400 transition-colors duration-300">11. الربحية مقابل السرعة</span>
                </a>
                <!-- 12 -->
                <a href="inv_rep12_cost_variance.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #fbbf24; --glow-color: rgba(245,158,11,0.25);">
                    <svg class="w-10 h-10 mb-3 text-amber-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="3" x2="12" y2="21"/>
                        <line x1="7" y1="12" x2="17" y2="10"/>
                        <path d="M7 12V21M17 10V21"/>
                        <circle cx="12" cy="3" r="1"/>
                    </svg>
                    <span class="text-sm font-black text-slate-100 group-hover:text-amber-400 transition-colors duration-300">12. انحراف التكلفة المرجحة</span>
                </a>
                <!-- زر 13 -->
                <a href="inv_rep13_zero_margin.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #fbbf24; --glow-color: rgba(245,158,11,0.25);">
                    <svg class="w-10 h-10 mb-3 text-amber-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    <span class="text-sm font-black text-slate-100 group-hover:text-amber-400 transition-colors duration-300">13. الأصناف الخاسرة والحرجة</span>
                </a>
                <!-- زر 14 -->
                <a href="inv_rep14_gmroii.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #fbbf24; --glow-color: rgba(245,158,11,0.25);">
                    <svg class="w-10 h-10 mb-3 text-amber-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="6" x2="12" y2="18"/>
                        <path d="M14.5 9.5a2.5 2.5 0 0 0-5 0v5a2.5 2.5 0 0 0 5 0"/>
                    </svg>
                    <span class="text-sm font-black text-slate-100 group-hover:text-amber-400 transition-colors duration-300">14. العائد الاستثماري GMROII</span>
                </a>
                <!-- زر 15 -->
                <a href="inv_rep15_valuation.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #fbbf24; --glow-color: rgba(245,158,11,0.25);">
                    <svg class="w-10 h-10 mb-3 text-amber-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                    <span class="text-sm font-black text-slate-100 group-hover:text-amber-400 transition-colors duration-300">15. تقييم المخزون المالي اللحظي</span>
                </a>
            </div>
        </div>

        <!-- المحور الرابع -->
        <div>
            <h3 class="text-[13px] font-black text-purple-400 tracking-wider mb-6 border-b border-slate-800/80 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles text-purple-400"></i> المحور الرابع: تقارير التنبؤ المستقبلي وحظر المخاطر
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- زر 16 -->
                <a href="inv_rep16_market_drop.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #c084fc; --glow-color: rgba(168,85,247,0.25);">
                    <svg class="w-10 h-10 mb-3 text-purple-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/>
                        <polyline points="17 18 23 18 23 12"/>
                    </svg>
                    <span class="text-sm font-black text-slate-100 group-hover:text-purple-400 transition-colors duration-300">16. خطر هبوط الأسعار المفاجئ</span>
                </a>
                <!-- زر 17 -->
                <a href="inv_rep17_shrinkage.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #c084fc; --glow-color: rgba(168,85,247,0.25);">
                    <svg class="w-10 h-10 mb-3 text-purple-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        <line x1="8" y1="11" x2="14" y2="11"/>
                    </svg>
                    <span class="text-sm font-black text-slate-100 group-hover:text-purple-400 transition-colors duration-300">17. عجز التدقيق وجرد العنابر</span>
                </a>
                <!-- زر 18 -->
                <a href="inv_rep18_transit.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #c084fc; --glow-color: rgba(168,85,247,0.25);">
                    <svg class="w-10 h-10 mb-3 text-purple-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 17h20l-2 4H4l-2-4zM12 3v14M12 3c-3 0-6 4-6 6h12c0-2-3-6-6-6z"/>
                    </svg>
                    <span class="text-sm font-black text-slate-100 group-hover:text-purple-400 transition-colors duration-300">18. طلبيات الشحن والاستيراد</span>
                </a>
                <!-- زر 19 -->
                <a href="inv_rep19_abc.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #c084fc; --glow-color: rgba(168,85,247,0.25);">
                    <svg class="w-10 h-10 mb-3 text-purple-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                        <polyline points="2 17 12 22 22 17"/>
                        <polyline points="2 12 12 17 22 12"/>
                    </svg>
                    <span class="text-sm font-black text-slate-100 group-hover:text-purple-400 transition-colors duration-300">19. تحليل تركيز البضائع ABC</span>
                </a>
                <!-- زر 20 -->
                <a href="inv_rep20_simulator.php" class="modern-btn p-6 flex flex-col items-center justify-center text-center h-40 group" style="--hover-color: #c084fc; --glow-color: rgba(168,85,247,0.25);">
                    <svg class="w-10 h-10 mb-3 text-purple-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 4-2 2M19 8l-2 2M20 3v2M15 8h2M18 19l-9-9-6 6 9 9z"/>
                    </svg>
                    <span class="text-sm font-black text-slate-100 group-hover:text-purple-400 transition-colors duration-300">20. محاكي سيناريوهات النواقص</span>
                </a>
            </div>
        </div>

    </div>

    </div> <!-- Close .dashboard-container -->

    <!-- Scripts -->
    <script defer src="js/localforage.min.js?v=20260723_v14"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
</body>
</html>
