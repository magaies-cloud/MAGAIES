<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - حزمة المساعدات ومفاتيح البنوك والموسوعة الكبرى لفتاوى البيوع V12
// كوكبة الأئمة الـ 12 + مكتبة أمهات الكتب + القواعد الفقهية الـ 5 + معجم المصطلحات + محرك البحث الذكي
// ====================================================================
$page_title = 'الأدوات المساعدة والحاسبات ومفاتيح تحويلات البنوك وفتاوى البيوع';
$header_theme_class = 'header-theme-reports';

require_once 'db_bridge.php';
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$pdo = getDBConnection();
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مفاتيح تحويلات البنوك وموسوعة فتاوى البيوع | Al-Sajana ERP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800;900&family=Cairo:wght@300;400;600;700;900&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #04020a;
            --panel-bg: rgba(14, 10, 26, 0.85);
            --border-neon: rgba(236, 72, 153, 0.25);
            --text-main: #ffffff;
            --text-muted: #94a3b8;
        }

        :root.light-theme {
            --bg-dark: #f8fafc;
            --panel-bg: #ffffff;
            --border-neon: #000000;
            --text-main: #000000;
            --text-muted: #334155;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Cairo', 'Tajawal', sans-serif;
            overflow-x: hidden;
        }

        .tools-master-wrapper {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 auto !important;
            padding: 10px 14px 60px 14px !important;
            box-sizing: border-box !important;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* ── أزرار التبويبات العشرة البارزة ثلاثية الأبعاد ── */
        .suite-pill-card {
            padding: 14px 8px;
            border-radius: 20px;
            min-height: 120px;
            cursor: pointer;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            border: 2px solid var(--p-border, rgba(255, 255, 255, 0.12));
            background: linear-gradient(145deg, rgba(20, 27, 45, 0.92), rgba(10, 15, 28, 0.98));
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.45), inset 0 1px 2px rgba(255, 255, 255, 0.15);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(15px);
        }

        .suite-pill-card:hover {
            transform: translateY(-5px) scale(1.02);
            border-color: var(--p-color, #ec4899);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.6), 0 0 20px var(--p-color);
        }

        .suite-pill-card.active {
            background: linear-gradient(145deg, rgba(236, 72, 153, 0.35), rgba(168, 85, 247, 0.45)) !important;
            border-color: #ec4899 !important;
            box-shadow: 0 12px 30px rgba(236, 72, 153, 0.5), 0 0 25px #ec4899 !important;
            transform: translateY(-4px) scale(1.03);
        }

        .suite-pill-icon {
            font-size: 1.8rem;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            filter: drop-shadow(0 3px 8px var(--p-color, #ec4899));
            transition: all 0.3s ease;
        }

        .suite-pill-title {
            font-size: 1.05rem;
            font-weight: 900;
            color: #ffffff;
            letter-spacing: -0.3px;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.8);
            margin: 2px 0;
            line-height: 1.2;
        }

        .suite-pill-sub {
            font-size: 0.7rem;
            color: #94a3b8;
            font-weight: 700;
            white-space: nowrap;
        }

        .glass-panel-v3 {
            background: var(--panel-bg);
            border: 2px solid var(--border-color, rgba(236, 72, 153, 0.3));
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(20px);
            position: relative;
        }

        :root.light-theme .glass-panel-v3 {
            background: #ffffff !important;
            border: 2px solid #cbd5e1 !important;
            color: #000000 !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
        }

        .tool-input {
            background: rgba(15, 23, 42, 0.95);
            border: 2px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            padding: 12px 16px;
            border-radius: 14px;
            font-weight: 800;
            font-size: 1rem;
            outline: none;
            width: 100%;
            transition: all 0.2s;
        }

        :root.light-theme .tool-input {
            background: #f8fafc;
            border: 2px solid #94a3b8;
            color: #000000;
        }

        .tool-input:focus {
            border-color: #ec4899;
            box-shadow: 0 0 15px rgba(236, 72, 153, 0.35);
        }

        .calc-result-box {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.12), rgba(168, 85, 247, 0.12));
            border: 2px solid rgba(236, 72, 153, 0.3);
            border-radius: 16px;
            padding: 16px;
            text-align: center;
        }

        :root.light-theme .calc-result-box {
            background: #fdf2f8;
            border: 2px solid #db2777;
        }

        /* ── بطاقات البنوك الفخمة والمفاتيح البارزة ── */
        .bban-bank-card {
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.96), rgba(6, 10, 20, 0.99));
            border: 2px solid rgba(56, 189, 248, 0.3);
            border-radius: 22px;
            padding: 22px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.12);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 14px;
        }

        .bban-bank-card:hover {
            transform: translateY(-5px) scale(1.02);
            border-color: #38bdf8;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.7), 0 0 25px rgba(56, 189, 248, 0.35);
        }

        .bban-code-box {
            background: #000000;
            border: 2px solid rgba(56, 189, 248, 0.6);
            border-radius: 14px;
            padding: 14px 16px;
            text-align: center;
            font-family: 'Outfit', 'JetBrains Mono', 'Courier New', monospace;
            font-size: 1.45rem;
            font-weight: 900;
            letter-spacing: 2px;
            color: #38bdf8;
            box-shadow: inset 0 3px 10px rgba(0, 0, 0, 0.9), 0 0 20px rgba(56, 189, 248, 0.2);
            direction: ltr;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2px;
        }

        /* ── المنصة الرئيسية العليا لتوليد الـ BBAN ── */
        .master-bban-hero-box {
            background: linear-gradient(145deg, rgba(8, 14, 28, 0.98), rgba(2, 6, 15, 0.99));
            border: 3px solid #f59e0b;
            border-radius: 28px;
            padding: 28px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.85), 0 0 40px rgba(245, 158, 11, 0.35);
            position: relative;
            overflow: hidden;
        }

        .master-bban-display-screen {
            background: #000000;
            border: 3px solid #10b981;
            border-radius: 22px;
            padding: 22px 26px;
            text-align: center;
            font-family: 'Outfit', 'JetBrains Mono', 'Courier New', monospace;
            font-size: 2.6rem;
            font-weight: 900;
            letter-spacing: 4px;
            color: #38bdf8;
            box-shadow: inset 0 4px 18px rgba(0, 0, 0, 0.95), 0 0 35px rgba(16, 185, 129, 0.45);
            direction: ltr;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        @media (max-width: 768px) {
            .master-bban-display-screen {
                font-size: 1.6rem;
                padding: 14px;
                letter-spacing: 1.5px;
            }
        }

        /* ── فتاوى البيوع الفخمة ── */
        .fiqh-master-card {
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.95), rgba(6, 10, 20, 0.98));
            border: 2px solid rgba(251, 191, 36, 0.3);
            border-radius: 22px;
            padding: 22px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
        }

        .fiqh-master-card:hover {
            border-color: #fbbf24;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.7), 0 0 25px rgba(251, 191, 36, 0.3);
            transform: translateY(-3px);
        }

        .fiqh-filter-btn {
            padding: 7px 13px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 800;
            background: rgba(15, 23, 42, 0.9);
            color: #cbd5e1;
            border: 2px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .fiqh-filter-btn:hover {
            border-color: #fbbf24;
            color: #fbbf24;
            transform: translateY(-2px);
        }

        .fiqh-filter-btn.active {
            background: linear-gradient(135deg, #d97706, #b45309) !important;
            border-color: #fbbf24 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.4);
        }

        .fiqh-chip {
            padding: 4px 10px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 800;
            background: rgba(30, 41, 59, 0.8);
            color: #93c5fd;
            border: 1px solid rgba(147, 197, 253, 0.3);
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .fiqh-chip:hover {
            background: #0284c7;
            color: #ffffff;
            border-color: #38bdf8;
        }

        .fiqh-nav-pill {
            padding: 10px 18px;
            border-radius: 14px;
            font-size: 0.95rem;
            font-weight: 900;
            background: rgba(15, 23, 42, 0.9);
            color: #94a3b8;
            border: 2px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .fiqh-nav-pill:hover {
            border-color: #fbbf24;
            color: #fbbf24;
        }

        .fiqh-nav-pill.active {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
            border-color: #fbbf24 !important;
            color: #020617 !important;
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .anim-suite { animation: fadeInUp 0.35s ease-out backwards; }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .tools-master-wrapper { padding: 0 !important; }
            .glass-panel-v3 { border: 1px solid #000 !important; box-shadow: none !important; }
        }
    </style>
</head>

<body class="no-page-transition">

    <div class="tools-master-wrapper">
        
        <?php include "includes/header.php"; ?>

        <!-- 🌟 ACTION CONTROLS & NAVIGATION BAR 🌟 -->
        <div class="flex flex-wrap items-center justify-between gap-4 no-print bg-slate-900/85 border-2 border-pink-500/40 p-5 rounded-2xl shadow-2xl backdrop-blur-2xl">
            <div>
                <a href="magaiees_unified_dashboard.php" class="inline-flex items-center gap-2 text-pink-400 font-extrabold hover:underline text-sm mb-1.5">
                    <i class="fa-solid fa-arrow-right"></i> ↩️ رجوع للوحة القيادة الموحدة (Unified Dashboard)
                </a>
                <h1 class="text-2xl md:text-3xl font-black flex items-center gap-3">
                    <i class="fa-solid fa-toolbox text-pink-400"></i>
                    مركز الأدوات والمساعدات ومفاتيح تحويلات البنوك (BBAN)
                </h1>
                <p class="text-slate-400 text-xs md:text-sm mt-1 font-bold">
                    حاسبات أوزان الحديد، الخرسانات، الطاقة والمضخات، الجمارك، مفاتيح التحويلات البنكية السودانية (BBAN)، فتاوى البيوع، والزكاة.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" onclick="window.print();" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-black rounded-xl text-xs transition flex items-center gap-1.5 border border-slate-700 shadow-md">
                    <i class="fa-solid fa-print"></i> طباعة التقرير / الحسابات
                </button>
            </div>
        </div>

        <!-- 🎛️ 10 PROMINENT SUITE TABS (3D CENTERED NEUMORPHIC) 🎛️ -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-10 gap-2.5" id="suite-tabs-container">
            
            <!-- 1. الحديد -->
            <div class="suite-pill-card active" style="--p-color:#ec4899; --p-border:rgba(236,72,153,0.5);" onclick="switchSuite('steel', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-cubes-stacked text-pink-400"></i></div>
                <div class="suite-pill-title">🔩 الحديد</div>
                <div class="suite-pill-sub">الأوزان والقطع</div>
            </div>

            <!-- 2. الخرسانات -->
            <div class="suite-pill-card" style="--p-color:#a78bfa; --p-border:rgba(167,139,250,0.5);" onclick="switchSuite('concrete', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-building text-purple-400"></i></div>
                <div class="suite-pill-title">🏗️ الخرسانة</div>
                <div class="suite-pill-sub">الأسقف والمونة</div>
            </div>

            <!-- 3. الطاقة الشمسية -->
            <div class="suite-pill-card" style="--p-color:#f59e0b; --p-border:rgba(245,158,11,0.5);" onclick="switchSuite('solar', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-solar-panel text-amber-400"></i></div>
                <div class="suite-pill-title">☀️ الطاقة</div>
                <div class="suite-pill-sub">المضخات والألواح</div>
            </div>

            <!-- 4. الجمارك والاستيراد -->
            <div class="suite-pill-card" style="--p-color:#38bdf8; --p-border:rgba(56,189,248,0.5);" onclick="switchSuite('freight', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-ship text-sky-400"></i></div>
                <div class="suite-pill-title">🚢 الجمارك</div>
                <div class="suite-pill-sub">الواصل والحاويات</div>
            </div>

            <!-- 5. التحويلات الهندسية -->
            <div class="suite-pill-card" style="--p-color:#34d399; --p-border:rgba(52,211,153,0.5);" onclick="switchSuite('units', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-calculator text-emerald-400"></i></div>
                <div class="suite-pill-title">📐 التحويلات</div>
                <div class="suite-pill-sub">الأراضي والقياسات</div>
            </div>

            <!-- 6. مفاتيح التحويلات البنكية BBAN -->
            <div class="suite-pill-card" style="--p-color:#22d3ee; --p-border:rgba(34,211,238,0.5);" onclick="switchSuite('banks', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-money-bill-transfer text-cyan-400"></i></div>
                <div class="suite-pill-title">🏦 مفاتيح التحويلات</div>
                <div class="suite-pill-sub">صيغ BBAN للبنوك (16)</div>
            </div>

            <!-- 7. فتاوى البيوع -->
            <div class="suite-pill-card" style="--p-color:#fbbf24; --p-border:rgba(251,191,36,0.5);" onclick="switchSuite('fiqh', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-book-quran text-amber-400"></i></div>
                <div class="suite-pill-title">📜 فتاوى البيوع</div>
                <div class="suite-pill-sub">موسوعة أئمة السنة (12)</div>
            </div>

            <!-- 8. حاسبة الزكاة -->
            <div class="suite-pill-card" style="--p-color:#10b981; --p-border:rgba(167,243,208,0.5);" onclick="switchSuite('zakat', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-hand-holding-dollar text-emerald-400"></i></div>
                <div class="suite-pill-title">⚖️ الزكاة</div>
                <div class="suite-pill-sub">عروض التجارة</div>
            </div>

            <!-- 9. الجدوى والأرباح -->
            <div class="suite-pill-card" style="--p-color:#f43f5e; --p-border:rgba(244,63,94,0.5);" onclick="switchSuite('feasibility', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-chart-line text-rose-400"></i></div>
                <div class="suite-pill-title">📊 الجدوى</div>
                <div class="suite-pill-sub">التعادل والهامش</div>
            </div>

            <!-- 10. نهاية الخدمة -->
            <div class="suite-pill-card" style="--p-color:#e879f9; --p-border:rgba(232,121,249,0.5);" onclick="switchSuite('hr_gratuity', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-user-tie text-fuchsia-400"></i></div>
                <div class="suite-pill-title">👥 نهاية الخدمة</div>
                <div class="suite-pill-sub">قوانين العمل والرواتب</div>
            </div>

        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 1. SUITE: STEEL & METALS WEIGHT & PIECE CALCULATOR               -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-steel" class="suite-section anim-suite space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(236, 72, 153, 0.4);">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-cubes-stacked text-pink-400"></i>
                        حاسبة أوزان وقطع حديد التسليح والمواسير والعلب والزنك الأمريكي
                    </h3>
                    <span class="text-xs font-bold text-pink-400 bg-pink-500/10 px-3 py-1 rounded-lg border border-pink-500/20">
                        معادلات التصنيع القياسية (ISO / DIN)
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4 lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">نوع القطاع / المنتج:</label>
                                <select id="steel_product_type" onchange="calculateSteel()" class="tool-input">
                                    <option value="rebar">حديد تسليح مباني (Rebar Bars)</option>
                                    <option value="pipe_rect">مواسير وعلب مستطيلة/مربعة (4×8، 2×4، 3×6 سم)</option>
                                    <option value="pipe_round">مواسير دائرية (Round Pipes)</option>
                                    <option value="sheet_zinc">صاج وزنك أمريكي مضلع وألواح مسطحة</option>
                                    <option value="beam_ipe">كمارات وقنوات (IPE / HEA / UPN)</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">المقاس المعتمد / السُمك:</label>
                                <select id="steel_preset_size" onchange="calculateSteel()" class="tool-input">
                                    <option value="rebar_8mm">تسليح 8 مم (لينة وربع - 12 متر)</option>
                                    <option value="rebar_10mm">تسليح 10 مم (3 لينة - 12 متر)</option>
                                    <option value="rebar_12mm" selected>تسليح 12 مم (4 لينة - 12 متر)</option>
                                    <option value="rebar_16mm">تسليح 16 مم (5 لينة - 12 متر)</option>
                                    <option value="rebar_20mm">تسليح 20 مم (6 لينة - 12 متر)</option>
                                    <option value="rebar_25mm">تسليح 25 مم (8 لينة - 12 متر)</option>
                                    <option value="rebar_32mm">تسليح 32 مم (10 لينة - 12 متر)</option>
                                    <option value="4x8_1.5">ماسورة علبة 4 × 8 سم (سُمك 1.5 مم - 6م)</option>
                                    <option value="4x8_2.0">ماسورة علبة 4 × 8 سم (سُمك 2.0 مم - 6م)</option>
                                    <option value="2x4_1.5">ماسورة علبة 2 × 4 سم (سُمك 1.5 مم - 6م)</option>
                                    <option value="2x4_2.0">ماسورة علبة 2 × 4 سم (سُمك 2.0 مم - 6م)</option>
                                    <option value="zinc_0.3">زنك أمريكي 0.30 مم (3.0م × 0.9م)</option>
                                    <option value="zinc_0.4">زنك أمريكي 0.40 مم (3.0م × 0.9م)</option>
                                    <option value="custom">مقاس وسُمك مخصص (Custom)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">القطر / العرض (مم):</label>
                                <input type="number" id="steel_width_mm" value="12" oninput="calculateSteel()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">الارتفاع (مم):</label>
                                <input type="number" id="steel_height_mm" value="12" oninput="calculateSteel()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">السُمك (مم):</label>
                                <input type="number" step="0.1" id="steel_thickness_mm" value="12" oninput="calculateSteel()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">الطول (متر):</label>
                                <input type="number" step="0.5" id="steel_length_m" value="12.0" oninput="calculateSteel()" class="tool-input">
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-300 block mb-1">الكمية المطلوبة (طن أو حبة):</label>
                            <div class="flex items-center gap-3">
                                <input type="number" id="steel_target_qty" value="1.0" oninput="calculateSteel()" class="tool-input font-bold text-lg text-pink-400">
                                <select id="steel_target_unit" onchange="calculateSteel()" class="tool-input w-40">
                                    <option value="ton">طن حديد (Ton)</option>
                                    <option value="pcs">عدد الحبات / الأسياخ (Pcs)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Calculated Results -->
                    <div class="space-y-3">
                        <div class="calc-result-box">
                            <span class="text-xs text-slate-400 font-bold block">📦 عدد الأسياخ / القطع في الطن الواحد</span>
                            <span class="text-4xl font-black text-pink-400 mt-1 block font-mono" id="res_pieces_per_ton">0</span>
                            <span class="text-xs text-pink-300/80 font-bold mt-1 block">سيخ / حبة في الطن المتري</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(56, 189, 248, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">⚖️ وزن السيخ / القطعة الواحدة</span>
                            <span class="text-3xl font-black text-sky-400 mt-1 block font-mono" id="res_weight_per_piece">0.00 kg</span>
                            <span class="text-xs text-sky-300/80 font-bold mt-1 block">كيلوجرام للقطعة</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(16, 185, 129, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">📊 ملخص الكمية المطلوبة</span>
                            <span class="text-xl font-black text-emerald-400 mt-1 block font-mono" id="res_total_summary">0 قطعة = 0.00 طن</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 2. SUITE: CONCRETE, SLABS & FINISHING CALCULATOR                 -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-concrete" class="suite-section anim-suite hidden space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(167, 139, 250, 0.4);">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-building text-purple-400"></i>
                        حاسبة كميات الخرسانة، الأسقف، البلوك، والتشطيبات والسيراميك
                    </h3>
                    <span class="text-xs font-bold text-purple-400 bg-purple-500/10 px-3 py-1 rounded-lg border border-purple-500/20">
                        تقدير دقيق للمواد الخام والمقاولات
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4 lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">نوع العنصر الإنشائي / السقف:</label>
                                <select id="concrete_type" onchange="calculateConcrete()" class="tool-input">
                                    <option value="beam_slab">سقف كمري عادي (Beam Slab)</option>
                                    <option value="hordy">سقف هوردي (Hollow Block / Joist Slab)</option>
                                    <option value="flat_slab">سقف فلات سلاب (Flat Slab)</option>
                                    <option value="building_skeleton">هيكل كامل (قواعد + أعمدة + سملات + أسقف)</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">مساحة المسطح ($m^2$):</label>
                                <input type="number" id="concrete_area" value="150" oninput="calculateConcrete()" class="tool-input text-lg font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">سُمك البلاطة ($cm$):</label>
                                <input type="number" id="concrete_thickness" value="15" oninput="calculateConcrete()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">عدد الطوابق:</label>
                                <input type="number" id="concrete_floors" value="1" oninput="calculateConcrete()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">نسبة التسليح ($kg/m^3$):</label>
                                <input type="number" id="concrete_rebar_ratio" value="110" oninput="calculateConcrete()" class="tool-input">
                            </div>
                        </div>

                        <!-- Finishing Extractor -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-purple-950/20 p-4 rounded-xl border border-purple-500/30">
                            <div>
                                <label class="text-xs font-bold text-purple-300 block mb-1">🧱 جدران البلوك (مساحة الجدران $m^2$):</label>
                                <input type="number" id="wall_area_m2" value="200" oninput="calculateConcrete()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-purple-300 block mb-1">🏺 أرضيات السيراميك ($m^2$):</label>
                                <input type="number" id="tiles_area_m2" value="150" oninput="calculateConcrete()" class="tool-input">
                            </div>
                        </div>
                    </div>

                    <!-- Calculated Concrete Results -->
                    <div class="space-y-2.5">
                        <div class="calc-result-box">
                            <span class="text-xs text-slate-400 font-bold block">🏗️ حجم الخرسانة المسلحة المطلوبة</span>
                            <span class="text-3xl font-black text-purple-400 mt-1 block font-mono" id="res_concrete_m3">0.00 m³</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(56, 189, 248, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">🔩 إجمالي حديد التسليح المطلوب</span>
                            <span class="text-3xl font-black text-sky-400 mt-1 block font-mono" id="res_rebar_tons">0.00 طن</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs font-bold text-center">
                            <div class="bg-slate-900/80 p-2.5 rounded-xl border border-slate-800">
                                <span class="text-slate-400 block">أكياس الأسمنت:</span>
                                <span class="text-amber-400 text-lg block font-mono" id="res_cement_bags">0</span>
                            </div>
                            <div class="bg-slate-900/80 p-2.5 rounded-xl border border-slate-800">
                                <span class="text-slate-400 block">طوب / بلوك الجدران:</span>
                                <span class="text-pink-400 text-lg block font-mono" id="res_wall_blocks">0 حبة</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs font-bold text-center">
                            <div class="bg-slate-900/80 p-2.5 rounded-xl border border-slate-800">
                                <span class="text-slate-400 block">كراتين السيراميك (1.44m²):</span>
                                <span class="text-emerald-400 text-base block font-mono" id="res_tiles_boxes">0 كرتونة</span>
                            </div>
                            <div class="bg-slate-900/80 p-2.5 rounded-xl border border-slate-800">
                                <span class="text-slate-400 block">أكياس غراء السيراميك:</span>
                                <span class="text-cyan-400 text-base block font-mono" id="res_tiles_glue">0 كيس</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 3. SUITE: SOLAR & DEEP WELL PUMPS CALCULATOR                     -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-solar" class="suite-section anim-suite hidden space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(245, 158, 11, 0.4);">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-solar-panel text-amber-400"></i>
                        حاسبة منظومات الطاقة الشمسية، البطاريات، ومضخات الآبار والري الغاطسة
                    </h3>
                    <span class="text-xs font-bold text-amber-400 bg-amber-500/10 px-3 py-1 rounded-lg border border-amber-500/20">
                        للمنازل والمزارع والمشاريع الإنتاجية
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4 lg:col-span-2">
                        <!-- Mode selector -->
                        <div class="flex items-center gap-3">
                            <label class="text-xs font-bold text-slate-300">نوع النظام:</label>
                            <select id="solar_sys_mode" onchange="toggleSolarMode()" class="tool-input flex-1">
                                <option value="home">منظومة منازل وشركات (إنفرتر + ألواح + بطاريات)</option>
                                <option value="pump">منظومة مضخة ري آبار غاطسة زراعية (Solar VFD Pumping)</option>
                            </select>
                        </div>

                        <!-- Home inputs -->
                        <div id="solar_home_inputs" class="grid grid-cols-2 md:grid-cols-3 gap-3 bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">مكيفات فريون (1.5 طن):</label>
                                <input type="number" id="solar_ac_count" value="1" oninput="calculateSolar()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">ثلاجات وفريزرات:</label>
                                <input type="number" id="solar_fridge_count" value="1" oninput="calculateSolar()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">إنارة ومراوح وشاشات (W):</label>
                                <input type="number" id="solar_lights_watt" value="500" oninput="calculateSolar()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">ساعات تشغيل البطاريات ليلاً:</label>
                                <input type="number" id="solar_night_hours" value="8" oninput="calculateSolar()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">قدرة اللوح الشمسي (وات):</label>
                                <input type="number" id="solar_panel_watt" value="550" oninput="calculateSolar()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">نوع البطارية المفضل:</label>
                                <select id="solar_bat_type" onchange="calculateSolar()" class="tool-input">
                                    <option value="lithium">ليثيوم LifePO4 (48V)</option>
                                    <option value="gel">جل / أسيد Tubular (48V)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Pump inputs -->
                        <div id="solar_pump_inputs" class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-amber-950/20 p-4 rounded-xl border border-amber-500/30 hidden">
                            <div>
                                <label class="text-xs font-bold text-amber-300 block mb-1">قدرة المضخة الغاطسة (حصان HP):</label>
                                <input type="number" id="solar_pump_hp_val" value="10" oninput="calculateSolar()" class="tool-input text-lg font-bold">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-amber-300 block mb-1">عمق البئر الإجمالي (متر):</label>
                                <input type="number" id="solar_well_depth_m" value="70" oninput="calculateSolar()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-amber-300 block mb-1">قدرة اللوح الشمسي (وات):</label>
                                <input type="number" id="solar_pump_panel_w" value="550" oninput="calculateSolar()" class="tool-input">
                            </div>
                        </div>
                    </div>

                    <!-- Calculated Solar Results -->
                    <div class="space-y-3">
                        <div class="calc-result-box">
                            <span class="text-xs text-slate-400 font-bold block">⚡ قدرة الإنفرتر الموصى به (Inverter / VFD)</span>
                            <span class="text-3xl font-black text-amber-400 mt-1 block font-mono" id="res_inverter_kw">0 kW</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(16, 185, 129, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">☀️ عدد ألواح الطاقة الشمسية</span>
                            <span class="text-3xl font-black text-emerald-400 mt-1 block font-mono" id="res_panel_count">0 لوح</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(56, 189, 248, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">🔋 سعة بنك البطاريات المطلوب</span>
                            <span class="text-2xl font-black text-sky-400 mt-1 block font-mono" id="res_battery_ah">0 Ah</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 4. SUITE: FREIGHT, LANDED COST & CONTAINER PLANNER              -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-freight" class="suite-section anim-suite hidden space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(56, 189, 248, 0.4);">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-ship text-sky-400"></i>
                        حاسبة تكاليف الاستيراد والجمارك والشحن والواصل وسعة الحاويات (Landed Cost)
                    </h3>
                    <span class="text-xs font-bold text-sky-400 bg-sky-500/10 px-3 py-1 rounded-lg border border-sky-500/20">
                        حساب دقيق لتكلفة الصنف الواصل للمخزن
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4 lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">قيمة فاتورة المصنع FOB ($):</label>
                                <input type="number" id="freight_fob_usd" value="50000" oninput="calculateFreight()" class="tool-input text-lg font-bold">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">تكلفة الشحن البحري والنولون ($):</label>
                                <input type="number" id="freight_sea_usd" value="3500" oninput="calculateFreight()" class="tool-input text-lg font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">الرسوم الجمركية (%):</label>
                                <input type="number" id="freight_customs_pct" value="15" oninput="calculateFreight()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">الموانئ والتخليص ($):</label>
                                <input type="number" id="freight_clearance_usd" value="1200" oninput="calculateFreight()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">النقل البري الداخلي ($):</label>
                                <input type="number" id="freight_inland_usd" value="800" oninput="calculateFreight()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">سعر صرف الدولار (SDG):</label>
                                <input type="number" id="freight_fx_rate" value="600" oninput="calculateFreight()" class="tool-input font-bold">
                            </div>
                        </div>

                        <!-- Container Planner Quick -->
                        <div class="bg-sky-950/20 p-4 rounded-xl border border-sky-500/30 space-y-2">
                            <span class="text-xs font-bold text-sky-300 block">📦 سعة الحاوية البحرية (Container Capacity Planner):</span>
                            <div class="grid grid-cols-3 gap-3 text-xs">
                                <div class="bg-slate-900/80 p-2 rounded-xl text-center">
                                    <span class="text-slate-400 block">حاوية 20 قدم:</span>
                                    <span class="text-sky-400 font-bold font-mono">33.2 CBM (~21.7 طن)</span>
                                </div>
                                <div class="bg-slate-900/80 p-2 rounded-xl text-center">
                                    <span class="text-slate-400 block">حاوية 40 قدم:</span>
                                    <span class="text-sky-400 font-bold font-mono">67.7 CBM (~26.5 طن)</span>
                                </div>
                                <div class="bg-slate-900/80 p-2 rounded-xl text-center">
                                    <span class="text-slate-400 block">حاوية 40 HQ:</span>
                                    <span class="text-sky-400 font-bold font-mono">76.3 CBM (~26.5 طن)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calculated Freight Results -->
                    <div class="space-y-3">
                        <div class="calc-result-box">
                            <span class="text-xs text-slate-400 font-bold block">💵 التكلفة الإجمالية الواصلة بالدولار</span>
                            <span class="text-3xl font-black text-sky-400 mt-1 block font-mono" id="res_landed_usd">$0</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(16, 185, 129, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">💰 التكلفة الإجمالية بالعملة المحلية</span>
                            <span class="text-2xl font-black text-emerald-400 mt-1 block font-mono" id="res_landed_sdg">0 SDG</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(245, 158, 11, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">📈 نسبة المصاريف المضافة إلى الشراء</span>
                            <span class="text-2xl font-black text-amber-400 mt-1 block font-mono" id="res_overhead_pct">%0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 5. SUITE: UNIVERSAL UNIT & LAND CONVERTER                        -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-units" class="suite-section anim-suite hidden space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(52, 211, 153, 0.4);">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-calculator text-emerald-400"></i>
                        حاسبة التحويلات الهندسية ومقاييس الأراضي والمساحات والأوزان الشاملة
                    </h3>
                    <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-3 py-1 rounded-lg border border-emerald-500/20">
                        تحويلات سريعة ومباشرة
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Land Area -->
                    <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800 space-y-2">
                        <span class="text-xs font-bold text-emerald-400 block">🌾 مساحات الأراضي (متر مربع ⬅️ فدان)</span>
                        <label class="text-[11px] text-slate-400 block">متر مربع ($m^2$):</label>
                        <input type="number" id="unit_sqm" value="4200" oninput="convertUnits('sqm')" class="tool-input">
                        <label class="text-[11px] text-slate-400 block mt-2">فدان سوداني (4200 متر مربع):</label>
                        <input type="number" id="unit_feddan" value="1" oninput="convertUnits('feddan')" class="tool-input">
                        <label class="text-[11px] text-slate-400 block mt-2">قيراط (24 قيراط = 1 فدان):</label>
                        <input type="number" id="unit_qirat" value="24" oninput="convertUnits('qirat')" class="tool-input">
                    </div>

                    <!-- Inches to mm -->
                    <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800 space-y-2">
                        <span class="text-xs font-bold text-pink-400 block">📏 الأطوال والمقاسات (بوصة ⬅️ مليمتر)</span>
                        <label class="text-[11px] text-slate-400 block">بوصة (Inch):</label>
                        <input type="number" id="unit_inch" value="4" oninput="convertUnits('inch')" class="tool-input">
                        <label class="text-[11px] text-slate-400 block mt-2">مليمتر (mm):</label>
                        <input type="number" id="unit_mm" value="101.6" oninput="convertUnits('mm')" class="tool-input">
                        <label class="text-[11px] text-slate-400 block mt-2">قدم (Foot = 12 Inch):</label>
                        <input type="number" id="unit_foot" value="0.333" oninput="convertUnits('foot')" class="tool-input">
                    </div>

                    <!-- Weight Units -->
                    <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800 space-y-2">
                        <span class="text-xs font-bold text-sky-400 block">⚖️ الأوزان والمحاصيل (كجم ⬅️ طن ⬅️ قنطار)</span>
                        <label class="text-[11px] text-slate-400 block">كيلوجرام (kg):</label>
                        <input type="number" id="unit_kg" value="1000" oninput="convertUnits('kg')" class="tool-input">
                        <label class="text-[11px] text-slate-400 block mt-2">طن متري (Ton = 1000 kg):</label>
                        <input type="number" id="unit_ton" value="1" oninput="convertUnits('ton')" class="tool-input">
                        <label class="text-[11px] text-slate-400 block mt-2">قنطار سمسم/صمغ (100 رطل = 45.36 kg):</label>
                        <input type="number" id="unit_qantar" value="22.04" oninput="convertUnits('qantar')" class="tool-input">
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 6. SUITE: BBAN SUDANESE BANK ROUTING & TRANSFER GUIDE           -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-banks" class="suite-section anim-suite hidden space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(34, 211, 238, 0.4);">
                
                <!-- 👑👑 MASTER LUXURY HERO CARD: THE 3 CORE ELEMENTS IN GOLDEN YELLOW & EXTRA LARGE FONTS 👑👑 -->
                <div class="master-bban-hero-box mb-8">
                    
                    <!-- Header of the Hero Section -->
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b-2 border-amber-500/40 pb-5 mb-6">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="px-4 py-1.5 bg-amber-500/20 text-amber-300 rounded-xl text-sm font-mono font-black border-2 border-amber-400 shadow-md">
                                    ★ المنظومة الموحدة للتحويلات البنكية (BBAN Engine)
                                </span>
                                <span class="px-3.5 py-1.5 bg-emerald-500/20 text-emerald-300 rounded-xl text-sm font-black border-2 border-emerald-400 shadow-md">
                                    14 خانة معتمدة ✓
                                </span>
                            </div>
                            <h2 class="text-2xl md:text-4xl font-black text-white flex items-center gap-3">
                                <i class="fa-solid fa-building-columns text-amber-400"></i>
                                المنصة الذكية لتوليد وتركيب أرقام الـ BBAN للتحويل المباشر
                            </h2>
                            <p class="text-sm md:text-base font-bold text-slate-300 mt-1.5">
                                الخطوات الثلاث الرئيسية: <span class="text-amber-300 font-black">1) اختيار اسم البنك</span> ⬅️ <span class="text-amber-300 font-black">2) كتابة رقم الحساب</span> ⬅️ <span class="text-emerald-300 font-black">3) نسخ صيغة الـ BBAN المكتملة</span>
                            </p>
                        </div>

                        <!-- Search Bar -->
                        <div class="w-full md:w-80">
                            <label class="text-xs font-bold text-slate-300 block mb-1">بحث سريع في دليل البنوك:</label>
                            <input type="text" id="bank-search-input" oninput="searchBanks(this.value)" placeholder="🔍 ابحث بالاسم أو التطبيق..." class="tool-input text-base font-bold">
                        </div>
                    </div>

                    <!-- 🎛️ 1 & 2: TWO MASSIVE INPUT SELECTORS WITH GOLDEN YELLOW TYPOGRAPHY 🎛️ -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        
                        <!-- 1. اختيار اسم البنك المستفيد -->
                        <div class="bg-slate-900/95 p-6 rounded-3xl border-3 border-amber-500/50 shadow-xl space-y-3">
                            <label class="text-xl md:text-2xl font-black text-amber-400 flex items-center gap-2">
                                <span class="w-9 h-9 rounded-full bg-amber-400 text-slate-950 font-black flex items-center justify-center text-lg shadow-md">1</span>
                                🏛️ اختيار اسم البنك المستفيد:
                            </label>
                            <select id="bban_bank_selector" onchange="generateUserBBAN()" style="color: #fbbf24; font-size: 1.55rem; font-weight: 900;" class="tool-input font-black py-4 px-5 bg-slate-950 border-3 border-amber-400 rounded-2xl cursor-pointer">
                                <option value="040|XXXXXXX|0001">1. بنك الخرطوم</option>
                                <option value="240|XXXXXXX|0001">2. بنك أم درمان الوطني</option>
                                <option value="02|XXXXXXXX|0001">3. بنك فيصل الإسلامي</option>
                                <option value="150000|XXXXXXXX|">4. بنك النيل الأزرق المشرق</option>
                                <option value="14000|XXXX|00001">5. البنك الإسلامي السوداني</option>
                                <option value="sahel">6. بنك النيل</option>
                                <option value="222|XXXXXXXXXXX|">7. بنك المزارع التجاري</option>
                                <option value="110|XXXXXX|00001">8. البنك الفرنسي السوداني</option>
                                <option value="03|XXXXXXX|01001">9. البنك الزراعي السوداني</option>
                                <option value="180|XXXXXX|00001">10. بنك السلام السوداني</option>
                                <option value="070|XXXXXX|00001">11. بنك البركة السوداني</option>
                                <option value="090|XXXXXX|00001">12. بنك الثروة الحيوانية</option>
                                <option value="120|XXXXXX|00001">13. بنك العمال الوطني</option>
                                <option value="160|XXXXXX|00001">14. البنك العقاري التجاري</option>
                                <option value="130|XXXXXX|00001">15. بنك التنمية الصناعية</option>
                                <option value="170|XXXXXX|00001">16. بنك المال المتحد</option>
                            </select>
                            <span class="text-xs text-slate-400 font-bold block mt-1" id="selected_bank_app_badge">
                                📱 التطبيق المعتمد: بنكك (Bank of Khartoum)
                            </span>
                        </div>

                        <!-- 2. كتابة رقم الحساب -->
                        <div class="bg-slate-900/95 p-6 rounded-3xl border-3 border-amber-500/50 shadow-xl space-y-3">
                            <label class="text-xl md:text-2xl font-black text-amber-400 flex items-center gap-2">
                                <span class="w-9 h-9 rounded-full bg-amber-400 text-slate-950 font-black flex items-center justify-center text-lg shadow-md">2</span>
                                🔢 كتابة رقم الحساب (بدون الصفر):
                            </label>
                            <input type="text" id="bban_raw_account" placeholder="اكتب رقم الحساب هنا" oninput="generateUserBBAN()" style="color: #fbbf24; font-size: 2.2rem; font-weight: 900; letter-spacing: 4px;" class="tool-input font-mono font-black py-4 px-6 bg-slate-950 border-3 border-amber-400 text-center rounded-2xl placeholder-slate-700 shadow-inner">
                            <div class="flex items-center justify-between text-xs font-black mt-1">
                                <span class="text-amber-300 font-bold" id="bban_strip_feedback">✓ يحذف الصفر في بداية الحساب تلقائياً</span>
                                <span class="text-rose-400 font-bold">⚠️ تنبيه: لا تكتب صفر في البداية</span>
                            </div>
                        </div>

                    </div>

                    <!-- 🌟 3. GRAND HERO DISPLAY: صيغة رقم الـ BBAN النهائي المكتمل 🌟 -->
                    <div class="bg-slate-950/95 p-6 md:p-8 rounded-3xl border-3 border-emerald-500/60 shadow-2xl space-y-4">
                        
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-3">
                            <label class="text-lg md:text-2xl font-black text-emerald-400 flex items-center gap-3">
                                <span class="w-10 h-10 rounded-full bg-emerald-500 text-slate-950 font-black flex items-center justify-center text-xl shadow-md">3</span>
                                👑 صيغة رقم الـ BBAN النهائي المكتمل (جاهز للتحويل المباشر):
                            </label>
                            <span class="px-4 py-1.5 bg-emerald-500/20 text-emerald-300 border-2 border-emerald-400 rounded-xl font-mono font-black text-sm md:text-base" id="bban_len_counter">
                                عدد الخانات: 14 رقماً ✓
                            </span>
                        </div>

                        <!-- 🖥️ THE GIGANTIC DIGITAL SCREEN 🖥️ -->
                        <div class="master-bban-display-screen" id="generated_bban_display_wrapper">
                            <span id="generated_bban_display">040-------0001</span>
                        </div>

                        <!-- Huge Copy Button -->
                        <button type="button" onclick="copyGeneratedBBAN()" class="w-full py-4 bg-gradient-to-r from-emerald-500 via-teal-400 to-cyan-400 hover:from-emerald-400 hover:to-cyan-300 text-slate-950 font-black rounded-2xl text-base md:text-lg transition flex items-center justify-center gap-3 shadow-2xl transform active:scale-95">
                            <i class="fa-regular fa-copy text-xl"></i>
                            اضغط هنا لنسخ رقم الـ BBAN المكتمل الآن (جاهز للصق في تطبيق البنك)
                        </button>

                    </div>

                </div>

                <!-- ⚠️ CRITICAL NOTICE BANNER ⚠️ -->
                <div class="p-4 bg-amber-500/15 border-2 border-amber-500/50 rounded-2xl flex items-center gap-4 text-amber-200 font-bold shadow-xl mb-6">
                    <span class="text-3xl">💡</span>
                    <div>
                        <h4 class="text-base md:text-lg font-black text-amber-300">قاعدة ذهبية لجميع التحويلات: حذف الصفر في بداية رقم الحساب</h4>
                        <p class="text-xs md:text-sm text-slate-200 mt-0.5">
                            عند تركيب رقم الحساب داخل صيغة الـ BBAN، <span class="text-rose-400 font-black underline">يتم حذف أي صفر في بداية رقم الحساب الأساسي</span> حتى تنضبط خانات الـ BBAN وتكتمل الـ 14 خانة بنجاح دون أي زيادة أو خطأ في التحويل.
                        </p>
                    </div>
                </div>

                <!-- 📋 Full 16 Banks Directory Grid 📋 -->
                <div class="mb-4">
                    <h3 class="text-xl md:text-2xl font-black text-white flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-cyan-400"></i>
                        دليل وصيغ الـ BBAN لجميع البنوك الـ 16 (للمراجعة والنسخ الفردي):
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="banks-grid-container">

                    <!-- Bank 1: بنك الخرطوم -->
                    <div class="bban-bank-card" data-bank-search="بنك الخرطوم تطبيق بنكك (bankak) 040xxxxxxx0001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    1
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/25 text-emerald-300 border-emerald-400 shadow-sm">
                                    📱 تطبيق بنكك (Bankak)
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك الخرطوم
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 040 + 7 أرقام حساب (بدون صفر البداية) + اللاحقة 0001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">040</span><span class="text-white font-black px-1">XXXXXXX</span><span class="text-emerald-400 font-black">0001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('040XXXXXXX0001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 2: بنك أم درمان الوطني -->
                    <div class="bban-bank-card" data-bank-search="بنك أم درمان الوطني تطبيق أوكاش (o-cash) 240xxxxxxx0001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    2
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-sky-500/25 text-sky-300 border-sky-400 shadow-sm">
                                    📱 تطبيق أوكاش (O-Cash)
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك أم درمان الوطني
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 240 + 7 أرقام حساب (بدون صفر البداية) + اللاحقة 0001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">240</span><span class="text-white font-black px-1">XXXXXXX</span><span class="text-emerald-400 font-black">0001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('240XXXXXXX0001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 3: بنك فيصل الإسلامي -->
                    <div class="bban-bank-card" data-bank-search="بنك فيصل الإسلامي تطبيق فوري (fawry) 02xxxxxxxx0001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    3
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-400 shadow-sm">
                                    📱 تطبيق فوري (Fawry)
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك فيصل الإسلامي
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 02 + 8 أرقام حساب (بدون صفر البداية) + اللاحقة 0001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">02</span><span class="text-white font-black px-1">XXXXXXXX</span><span class="text-emerald-400 font-black">0001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('02XXXXXXXX0001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 4: بنك النيل الأزرق المشرق -->
                    <div class="bban-bank-card" data-bank-search="بنك النيل الأزرق المشرق تطبيق مشرق (mashreq) 150000xxxxxxxx">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    4
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-yellow-500/25 text-yellow-300 border-yellow-400 shadow-sm">
                                    📱 تطبيق مشرق (Mashreq)
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك النيل الأزرق المشرق
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 150000 + 8 أرقام حساب (بدون صفر البداية)
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">150000</span><span class="text-white font-black px-1">XXXXXXXX</span><span class="text-emerald-400 font-black"></span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('150000XXXXXXXX', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 5: البنك الإسلامي السوداني -->
                    <div class="bban-bank-card" data-bank-search="البنك الإسلامي السوداني تطبيق آمن بلس (amen plus) 14000xxxx00001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    5
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-purple-500/25 text-purple-300 border-purple-400 shadow-sm">
                                    📱 تطبيق آمن بلس (Amen Plus)
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                البنك الإسلامي السوداني
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 14000 + 4 أرقام حساب + اللاحقة 00001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">14000</span><span class="text-white font-black px-1">XXXX</span><span class="text-emerald-400 font-black">00001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('14000XXXX00001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 6: بنك النيل -->
                    <div class="bban-bank-card" data-bank-search="بنك النيل تطبيق ساهل (sahel) يستخرج من داخل التطبيق">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    6
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-cyan-500/25 text-cyan-300 border-cyan-400 shadow-sm">
                                    📱 تطبيق ساهل (Sahel)
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك النيل
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                يتم نسخ رمز الـ BBAN مباشرة من الملف الشخصي داخل تطبيق ساهل
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-300 font-bold text-lg md:text-xl tracking-normal">يستخرج من داخل التطبيق</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('يستخرج من داخل التطبيق', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 7: بنك المزارع التجاري -->
                    <div class="bban-bank-card" data-bank-search="بنك المزارع التجاري تطبيق مصرفك (masrafak) 222xxxxxxxxxxx">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    7
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/25 text-emerald-300 border-emerald-400 shadow-sm">
                                    📱 تطبيق مصرفك (Masrafak)
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك المزارع التجاري
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 222 + 11 رقم حساب (بدون صفر البداية)
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">222</span><span class="text-white font-black px-1">XXXXXXXXXXX</span><span class="text-emerald-400 font-black"></span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('222XXXXXXXXXXX', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 8: البنك الفرنسي السوداني -->
                    <div class="bban-bank-card" data-bank-search="البنك الفرنسي السوداني الفرنسي موبايل 110xxxxxx00001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    8
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-sky-500/25 text-sky-300 border-sky-400 shadow-sm">
                                    📱 الفرنسي موبايل
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                البنك الفرنسي السوداني
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 110 + 6 أرقام حساب + اللاحقة 00001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">110</span><span class="text-white font-black px-1">XXXXXX</span><span class="text-emerald-400 font-black">00001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('110XXXXXX00001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 9: البنك الزراعي السوداني -->
                    <div class="bban-bank-card" data-bank-search="البنك الزراعي السوداني الزراعي موبايل 03xxxxxxx01001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    9
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-lime-500/25 text-lime-300 border-lime-400 shadow-sm">
                                    📱 الزراعي موبايل
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                البنك الزراعي السوداني
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 03 + 7 أرقام حساب + اللاحقة 01001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">03</span><span class="text-white font-black px-1">XXXXXXX</span><span class="text-emerald-400 font-black">01001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('03XXXXXXX01001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 10: بنك السلام السوداني -->
                    <div class="bban-bank-card" data-bank-search="بنك السلام السوداني السلام موبايل 180xxxxxx00001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    10
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-400 shadow-sm">
                                    📱 السلام موبايل
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك السلام السوداني
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 180 + 6 أرقام حساب + اللاحقة 00001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">180</span><span class="text-white font-black px-1">XXXXXX</span><span class="text-emerald-400 font-black">00001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('180XXXXXX00001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 11: بنك البركة السوداني -->
                    <div class="bban-bank-card" data-bank-search="بنك البركة السوداني البركة موبايل 070xxxxxx00001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    11
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-rose-500/25 text-rose-300 border-rose-400 shadow-sm">
                                    📱 البركة موبايل
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك البركة السوداني
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 070 + 6 أرقام حساب + اللاحقة 00001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">070</span><span class="text-white font-black px-1">XXXXXX</span><span class="text-emerald-400 font-black">00001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('070XXXXXX00001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 12: بنك الثروة الحيوانية -->
                    <div class="bban-bank-card" data-bank-search="بنك الثروة الحيوانية الثروة كاش 090xxxxxx00001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    12
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/25 text-emerald-300 border-emerald-400 shadow-sm">
                                    📱 الثروة كاش
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك الثروة الحيوانية
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 090 + 6 أرقام حساب + اللاحقة 00001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">090</span><span class="text-white font-black px-1">XXXXXX</span><span class="text-emerald-400 font-black">00001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('090XXXXXX00001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 13: بنك العمال الوطني -->
                    <div class="bban-bank-card" data-bank-search="بنك العمال الوطني العمال موبايل 120xxxxxx00001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    13
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-sky-500/25 text-sky-300 border-sky-400 shadow-sm">
                                    📱 العمال موبايل
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك العمال الوطني
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 120 + 6 أرقام حساب + اللاحقة 00001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">120</span><span class="text-white font-black px-1">XXXXXX</span><span class="text-emerald-400 font-black">00001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('120XXXXXX00001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 14: البنك العقاري التجاري -->
                    <div class="bban-bank-card" data-bank-search="البنك العقاري التجاري العقاري كاش 160xxxxxx00001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    14
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-purple-500/25 text-purple-300 border-purple-400 shadow-sm">
                                    📱 العقاري كاش
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                البنك العقاري التجاري
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 160 + 6 أرقام حساب + اللاحقة 00001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">160</span><span class="text-white font-black px-1">XXXXXX</span><span class="text-emerald-400 font-black">00001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('160XXXXXX00001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 15: بنك التنمية الصناعية -->
                    <div class="bban-bank-card" data-bank-search="بنك التنمية الصناعية الصناعي موبايل 130xxxxxx00001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    15
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-400 shadow-sm">
                                    📱 الصناعي موبايل
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك التنمية الصناعية
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 130 + 6 أرقام حساب + اللاحقة 00001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">130</span><span class="text-white font-black px-1">XXXXXX</span><span class="text-emerald-400 font-black">00001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('130XXXXXX00001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 16: بنك المال المتحد -->
                    <div class="bban-bank-card" data-bank-search="بنك المال المتحد المال كاش 170xxxxxx00001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    16
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-cyan-500/25 text-cyan-300 border-cyan-400 shadow-sm">
                                    📱 المال كاش
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك المال المتحد
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 170 + 6 أرقام حساب + اللاحقة 00001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">170</span><span class="text-white font-black px-1">XXXXXX</span><span class="text-emerald-400 font-black">00001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('170XXXXXX00001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                </div>

                <!-- 💡 Smart Instructions from Card 💡 -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 pt-6 border-t border-slate-800">
                    <div class="p-4 bg-slate-950/80 border-2 border-slate-800 rounded-2xl flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-cyan-500/20 text-cyan-300 text-2xl flex items-center justify-center flex-shrink-0">
                            🔍
                        </div>
                        <div>
                            <h5 class="text-sm md:text-base font-black text-white">راجع رقم BBAN بعناية</h5>
                            <p class="text-xs text-slate-300 font-bold">تأكد من مطابقة عدد الخانات (14 رقماً) وحذف الصفر في بداية الحساب.</p>
                        </div>
                    </div>

                    <div class="p-4 bg-slate-950/80 border-2 border-slate-800 rounded-2xl flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/20 text-emerald-300 text-2xl flex items-center justify-center flex-shrink-0">
                            👤
                        </div>
                        <div>
                            <h5 class="text-sm md:text-base font-black text-white">تطابق اسم المستفيد</h5>
                            <p class="text-xs text-slate-300 font-bold">لا تُكمل التحويل إلا بعد ظهور ومطابقة اسم المستفيد الرباعي على الشاشة.</p>
                        </div>
                    </div>

                    <div class="p-4 bg-slate-950/80 border-2 border-slate-800 rounded-2xl flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-amber-500/20 text-amber-300 text-2xl flex items-center justify-center flex-shrink-0">
                            💵
                        </div>
                        <div>
                            <h5 class="text-sm md:text-base font-black text-white">رسوم التحويل بين البنوك</h5>
                            <p class="text-xs text-slate-300 font-bold">تختلف الرسوم بين التطبيقات وغالباً ما تكون بين 400 - 500 جنيه.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 7. SUITE: MEGA FIQH PLATFORM (12 SCHOLARS, BOOKS, MAXIMS, TERMS)-->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-fiqh" class="suite-section anim-suite hidden space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(251, 191, 36, 0.45);">
                
                <!-- Section Master Header -->
                <div class="flex flex-wrap items-center justify-between gap-4 border-b-2 border-amber-500/40 pb-5 mb-6">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="px-4 py-1.5 bg-amber-500/20 text-amber-300 rounded-xl text-sm font-black border-2 border-amber-400 shadow-md">
                                ★ الموسوعة الكبرى لفتاوى وضوابط ومؤلفات البيوع
                            </span>
                            <span class="px-3.5 py-1.5 bg-emerald-500/20 text-emerald-300 rounded-xl text-sm font-black border-2 border-emerald-400 shadow-md">
                                12 إماماً وعالماً معتمداً ✓
                            </span>
                        </div>
                        <h2 class="text-2xl md:text-3xl font-black text-white flex items-center gap-3">
                            <i class="fa-solid fa-book-quran text-amber-400"></i>
                            منصة فتاوى البيوع والمعاملات التجارية ومكتبة أمهات الكتب
                        </h2>
                        <p class="text-xs md:text-sm font-bold text-slate-300 mt-1">
                            تحقيقات وفتاوى: <span class="text-amber-300 font-black">أحمد بن حنبل</span> · <span class="text-amber-300 font-black">ابن تيمية</span> · <span class="text-amber-300 font-black">ابن القيم</span> · <span class="text-amber-300 font-black">النووي</span> · <span class="text-amber-300 font-black">الذهبي</span> · <span class="text-amber-300 font-black">محمد بن عبد الوهاب</span> · <span class="text-amber-300 font-black">السعدي</span> · <span class="text-amber-300 font-black">الشنقيطي</span> · <span class="text-amber-300 font-black">الألباني</span> · <span class="text-amber-300 font-black">ابن باز</span> · <span class="text-amber-300 font-black">ابن عثيمين</span> · <span class="text-amber-300 font-black">الفوزان</span>
                        </p>
                    </div>

                    <!-- Instant Fatwa Search Bar with Live Status -->
                    <div class="w-full md:w-96 space-y-1">
                        <label class="text-xs font-bold text-slate-300 flex items-center justify-between">
                            <span>🔍 محرك البحث الفقهي الذكي:</span>
                            <span id="fiqh_search_badge" class="text-emerald-400 font-mono font-black text-xs">19 فتوى متاحة</span>
                        </label>
                        <div class="relative">
                            <input type="text" id="fiqh-search-input" oninput="filterFatwas()" placeholder="اكتب للبحث: تقسيط، شيك، عربون، استصناع..." class="tool-input text-sm font-bold pr-10">
                            <i class="fa-solid fa-search absolute right-3 top-3.5 text-slate-400"></i>
                        </div>
                    </div>
                </div>

                <!-- ⚡ Quick Search Chips (الأسئلة الشائعة بنقرة واحدة) ⚡ -->
                <div class="p-3 bg-slate-950/70 rounded-2xl border border-slate-800 flex flex-wrap items-center gap-2 mb-4">
                    <span class="text-xs font-black text-amber-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-bolt text-amber-400"></i> أسئلة شائعة سريعة:
                    </span>
                    <button type="button" class="fiqh-chip" onclick="setQuickSearch('التقسيط')">💳 حكم البيع بالتقسيط</button>
                    <button type="button" class="fiqh-chip" onclick="setQuickSearch('غرامات التأخير')">⚠️ غرامات التأخير</button>
                    <button type="button" class="fiqh-chip" onclick="setQuickSearch('خصم الشيكات')">📜 خصم الشيكات الآجلة</button>
                    <button type="button" class="fiqh-chip" onclick="setQuickSearch('العربون')">🤝 بيع العربون</button>
                    <button type="button" class="fiqh-chip" onclick="setQuickSearch('الذهب')">💎 شراء الذهب بالبطاقة</button>
                    <button type="button" class="fiqh-chip" onclick="setQuickSearch('العِينة')">🚫 حيل الربا والعِينة</button>
                    <button type="button" class="fiqh-chip" onclick="setQuickSearch('التأمين')">🛡️ التأمين التعاوني</button>
                    <button type="button" class="fiqh-chip" onclick="setQuickSearch('الاستصناع')">🏗️ عقد الاستصناع</button>
                    <button type="button" class="fiqh-chip" onclick="setQuickSearch('الغرر')">🔍 نفي الغرر والجهالة</button>
                    <button type="button" class="fiqh-chip bg-rose-500/20 text-rose-300 border-rose-500/40" onclick="clearFiqhSearch()">✕ تفريغ البحث</button>
                </div>

                <!-- 🗂️ 4 SUB-TABS WITHIN FIQH SUITE (أقسام المنظومة الفقهية) 🗂️ -->
                <div class="flex flex-wrap items-center gap-2 mb-6 border-b border-slate-800 pb-3">
                    <button type="button" class="fiqh-nav-pill active" onclick="switchFiqhSubTab('fatwas', this)">
                        📑 موسوعة الفتاوى والأحكام (19 مسألة)
                    </button>
                    <button type="button" class="fiqh-nav-pill" onclick="switchFiqhSubTab('books', this)">
                        📚 مكتبة أمهات كتب البيوع للشيوخ (12)
                    </button>
                    <button type="button" class="fiqh-nav-pill" onclick="switchFiqhSubTab('maxims', this)">
                        ⚖️ القواعد الفقهية الكبرى الـ 5 الحاكمة للتجارة
                    </button>
                    <button type="button" class="fiqh-nav-pill" onclick="switchFiqhSubTab('terms', this)">
                        📖 معجم المصطلحات الفقهية والاقتصادية
                    </button>
                </div>

                <!-- ════════════════════════════════════════════════════════════ -->
                <!-- SUB-SECTION 1: FATWAS & RULINGS GRID                         -->
                <!-- ════════════════════════════════════════════════════════════ -->
                <div id="fiqh-sub-fatwas" class="space-y-4">
                    
                    <!-- 🎛️ FILTER BAR 1: CHOOSE BY SCHOLAR (المشايخ الـ 12) 🎛️ -->
                    <div class="bg-slate-900/90 p-4 rounded-2xl border-2 border-amber-500/30 space-y-2">
                        <span class="text-xs font-black text-amber-400 block">
                            👤 اختيار الفتاوى حسب فضيلة الشيخ / الإمام (12 إماماً):
                        </span>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <button type="button" class="fiqh-filter-btn active" data-filter-type="scholar" data-filter-val="all" onclick="setFiqhScholar('all', this)">
                                🌟 جميع المشايخ (12)
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="scholar" data-filter-val="imam_ahmad" onclick="setFiqhScholar('imam_ahmad', this)">
                                👑 الإمام أحمد بن حنبل
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="scholar" data-filter-val="ibn_taymiyyah" onclick="setFiqhScholar('ibn_taymiyyah', this)">
                                📖 شيخ الإسلام ابن تيمية
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="scholar" data-filter-val="ibn_al_qayyim" onclick="setFiqhScholar('ibn_al_qayyim', this)">
                                ✨ الإمام ابن قيم الجوزية
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="scholar" data-filter-val="al_nawawi" onclick="setFiqhScholar('al_nawawi', this)">
                                📜 الإمام النووي
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="scholar" data-filter-val="al_dhahabi" onclick="setFiqhScholar('al_dhahabi', this)">
                                ⚖️ الإمام الذهبي
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="scholar" data-filter-val="muhammad_bin_abdulwahhab" onclick="setFiqhScholar('muhammad_bin_abdulwahhab', this)">
                                📚 الإمام محمد بن عبد الوهاب
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="scholar" data-filter-val="al_saadi" onclick="setFiqhScholar('al_saadi', this)">
                                💡 العلامة عبد الرحمن السعدي
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="scholar" data-filter-val="al_shinqiti" onclick="setFiqhScholar('al_shinqiti', this)">
                                📖 العلامة محمد الأمين الشنقيطي
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="scholar" data-filter-val="al_albani" onclick="setFiqhScholar('al_albani', this)">
                                📚 الإمام الألباني
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="scholar" data-filter-val="ibn_baz" onclick="setFiqhScholar('ibn_baz', this)">
                                📜 الإمام ابن باز
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="scholar" data-filter-val="ibn_uthaymeen" onclick="setFiqhScholar('ibn_uthaymeen', this)">
                                💡 الإمام ابن عثيمين
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="scholar" data-filter-val="al_fawzan" onclick="setFiqhScholar('al_fawzan', this)">
                                ⚖️ الشيخ صالح الفوزان
                            </button>
                        </div>
                    </div>

                    <!-- 🎛️ FILTER BAR 2: CHOOSE BY TRANSACTION / TOPIC (نوع المعاملة) 🎛️ -->
                    <div class="bg-slate-900/90 p-4 rounded-2xl border-2 border-cyan-500/30 space-y-2">
                        <span class="text-xs font-black text-cyan-300 block">
                            🏷️ اختيار وتصنيف الفتاوى حسب نوع المعاملة والمسألة:
                        </span>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" class="fiqh-filter-btn active" data-filter-type="topic" data-filter-val="all" onclick="setFiqhTopic('all', this)">
                                📑 جميع المسائل والمعاملات
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="topic" data-filter-val="ownership" onclick="setFiqhTopic('ownership', this)">
                                🛒 بيع ما لا يملك والوساطة
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="topic" data-filter-val="installments" onclick="setFiqhTopic('installments', this)">
                                💳 التقسيط وغرامات التأخير
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="topic" data-filter-val="exchange" onclick="setFiqhTopic('exchange', this)">
                                💱 الصرف والموبايل بنك
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="topic" data-filter-val="checks" onclick="setFiqhTopic('checks', this)">
                                📜 الشيكات وخصم الأوراق المالية
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="topic" data-filter-val="araboon" onclick="setFiqhTopic('araboon', this)">
                                🤝 بيع العربون واسترداده
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="topic" data-filter-val="contracts" onclick="setFiqhTopic('contracts', this)">
                                🏗️ العقود والشروط والاستصناع والتأمين
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="topic" data-filter-val="pricing" onclick="setFiqhTopic('pricing', this)">
                                📈 الصدق والتسعير والموازين والتطفيف
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="topic" data-filter-val="gold" onclick="setFiqhTopic('gold', this)">
                                💎 الذهب والبطاقات البنكية
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="topic" data-filter-val="defects" onclick="setFiqhTopic('defects', this)">
                                🔄 بيان العيوب ونفي الغرر
                            </button>
                            <button type="button" class="fiqh-filter-btn" data-filter-type="topic" data-filter-val="riba" onclick="setFiqhTopic('riba', this)">
                                🚫 إبطال حيل الربا والعِينة
                            </button>
                        </div>
                    </div>

                    <!-- 📋 FATWAS CARDS GRID 📋 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="fatwas-grid-container">

                    <!-- Fatwa Card 1 -->
                    <div class="fiqh-master-card" data-scholar="imam_ahmad" data-topic="contracts" data-search="1. الأصل في العقود والشروط الجواز والصحة وخيار الشرط والمجلس الإمام المبجل أحمد بن حنبل (رحمه الله) مذهب الإمام أحمد بن حنبل رحمه الله أن الأصل في العقود والشروط هو الحل والصحة ما لم تخالف نصاً شرعياً أو تنافي مقتضى العقد. وثبوت خيار الشرط وخيار المجلس للمتبايعين للتراضي التام. مسائل الإمام أحمد برواية ابنه عبد الله والمغني لابن قدامة">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    1. الأصل في العقود والشروط الجواز والصحة وخيار الشرط والمجلس
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام المبجل أحمد بن حنبل (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: مسائل الإمام أحمد برواية ابنه عبد الله والمغني لابن قدامة
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>الأصل في الشروط التجارية الجواز والوفاء بها</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            مذهب الإمام أحمد بن حنبل رحمه الله أن الأصل في العقود والشروط هو الحل والصحة ما لم تخالف نصاً شرعياً أو تنافي مقتضى العقد. وثبوت خيار الشرط وخيار المجلس للمتبايعين للتراضي التام.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قول النبي ﷺ: «المسلمون عند شروطهم»، وقوله: «البيعان بالخيار ما لم يتفرقا».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إتاحة خيار الشرط المكتوب وفترة الفحص التجاري المتفق عليها في عقود التوريد والفواتير.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 2 -->
                    <div class="fiqh-master-card" data-scholar="muhammad_bin_abdulwahhab" data-topic="pricing" data-search="2. وجوب الصدق وتحريم الحلف الكاذب لترويج البضاعة والرشوة الإمام الشيخ محمد بن عبد الوهاب (رحمه الله) أكد الإمام محمد بن عبد الوهاب رحمه الله على وجوب الصدق والأمانة في المعاملات، وحرَّم الحلف بالأيمان الكاذبة أو المبالغة لترويج البضاعة، وبيَّن أن الرشوة والعمولات الخفية من أكل أموال الناس بالباطل. مجموع مؤلفات الشيخ (الفقه - كتاب البيوع) وكتاب التوحيد">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    2. وجوب الصدق وتحريم الحلف الكاذب لترويج البضاعة والرشوة
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام الشيخ محمد بن عبد الوهاب (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: مجموع مؤلفات الشيخ (الفقه - كتاب البيوع) وكتاب التوحيد
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-rose-500/25 text-rose-300 border-rose-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✕</span>
                                <span>واجب شرعاً الصدق والبيان · ومحرم قطعاً الحلف لترويج السلعة</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            أكد الإمام محمد بن عبد الوهاب رحمه الله على وجوب الصدق والأمانة في المعاملات، وحرَّم الحلف بالأيمان الكاذبة أو المبالغة لترويج البضاعة، وبيَّن أن الرشوة والعمولات الخفية من أكل أموال الناس بالباطل.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قول النبي ﷺ: «الحلف مَنْفَقَةٌ للسلعة مَمْحَقَةٌ للبركة» متفق عليه، وحديث: «التاجر الصدوق الأمين مع النبيين والصديقين والشهداء».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: الشفافية التامة والنزاهة في عروض الأسعار والتكاليف ومنع أي عمولات خفية غير موثقة في الحسابات.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 3 -->
                    <div class="fiqh-master-card" data-scholar="al_saadi" data-topic="contracts" data-search="3. اعتبار العرف التجاري المنضبط وقواعد التيسير في المعاملات العلامة الشيخ عبد الرحمن بن ناصر السعدي (رحمه الله) قرر العلامة السعدي رحمه الله أن «العرف مطرد كالمشروط لفظاً»، وأن الأصل في المعاملات التيسير والإباحة، وكل شرط أو تعامل جرى به عرف التجار ولم يخالف الشرع فهو معتبر شرعاً. القواعد والأصول الجامعة (ص 245) والفتاوى السعدية">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    3. اعتبار العرف التجاري المنضبط وقواعد التيسير في المعاملات
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> العلامة الشيخ عبد الرحمن بن ناصر السعدي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: القواعد والأصول الجامعة (ص 245) والفتاوى السعدية
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>اعتبار الأعراف التجارية السليمة في تحديد الأجل والتسليم</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            قرر العلامة السعدي رحمه الله أن «العرف مطرد كالمشروط لفظاً»، وأن الأصل في المعاملات التيسير والإباحة، وكل شرط أو تعامل جرى به عرف التجار ولم يخالف الشرع فهو معتبر شرعاً.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قاعدة فقهية: «العادة محكّمة»، وقوله تعالى: ﴿خُذِ الْعَفْوَ وَأْمُرْ بِالْعُرْفِ وَأَعْرِضْ عَنِ الْجَاهِلِينَ﴾.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: اعتماد الآجال وأساليب السداد والتسليم المتعارف عليها في السوق التجاري ما دامت خالية من الربا والغرر.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 4 -->
                    <div class="fiqh-master-card" data-scholar="ibn_baz" data-topic="ownership" data-search="4. بيع ما لا يملك المندوب أو التاجر («لا تَبِعْ ما ليس عندك» والوساطة) الإمام عبد العزيز بن باز (رحمه الله) نهى النبي ﷺ حكيم بن حزام فقال: «لا تَبِعْ ما ليس عندك». فلا يجوز للتاجر أو المندوب أن يبرم عقد بيع باتّاً على سلعة معينة ليست في ملكه ولا في حوزته وضمانه، ثم يذهب ليشتريها ويسلمها للزبون. مجموع فتاوى ومقالات متنوعة (19/43)">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    4. بيع ما لا يملك المندوب أو التاجر («لا تَبِعْ ما ليس عندك» والوساطة)
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام عبد العزيز بن باز (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: مجموع فتاوى ومقالات متنوعة (19/43)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>محرم في العقد الباتّ · جائز بصيغة الوكالة أو الوعد غير الملزم</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            نهى النبي ﷺ حكيم بن حزام فقال: «لا تَبِعْ ما ليس عندك». فلا يجوز للتاجر أو المندوب أن يبرم عقد بيع باتّاً على سلعة معينة ليست في ملكه ولا في حوزته وضمانه، ثم يذهب ليشتريها ويسلمها للزبون.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    حديث صحيح رواه الترمذي وأبو داود والنسائي وصححه الألباني.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    الحل الشرعي المعتمد: 1) إخبار الزبون بالوعد غير الملزم حتى يشتري التاجر البضاعة ويحوزها في متجره. 2) أو الاتفاق على الوكالة بأجر معلوم لشراء السلعة له.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 5 -->
                    <div class="fiqh-master-card" data-scholar="ibn_uthaymeen" data-topic="installments" data-search="5. البيع بالتقسيط مع زيادة السعر عن الثمن النقدي (الكاش) الإمام محمد بن صالح العثيمين (رحمه الله) يجوز بيع السلعة بالتقسيط أو لأجل بثمن أزيد من ثمنها الحاضر (الكاش)، بشرط أن يتم تحديد الثمن الإجمالي وعدد الأقساط ومواعيدها جازماً عند توقيع العقد دون تردد أو خيار بين السعرين. الشرح الممتع على زاد المستقنع (8/360) وفتاوى نور على الدرب">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    5. البيع بالتقسيط مع زيادة السعر عن الثمن النقدي (الكاش)
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محمد بن صالح العثيمين (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: الشرح الممتع على زاد المستقنع (8/360) وفتاوى نور على الدرب
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز شرعاً بإجماع عامة العلماء المعاصرين</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يجوز بيع السلعة بالتقسيط أو لأجل بثمن أزيد من ثمنها الحاضر (الكاش)، بشرط أن يتم تحديد الثمن الإجمالي وعدد الأقساط ومواعيدها جازماً عند توقيع العقد دون تردد أو خيار بين السعرين.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قوله تعالى: ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا إِذَا تَدَايَنتُم بِدَيْنٍ إِلَىٰ أَجَلٍ مُّسَمًّى فَاكْتُبُوهُ﴾، ولأن للأجل حصة من الثمن عند عامة الفقهاء.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: يُثبت سعر الفاتورة الآجلة النهائي دون ربط السداد بأي زيادة إضافية بعد العقد.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 6 -->
                    <div class="fiqh-master-card" data-scholar="ibn_taymiyyah" data-topic="installments" data-search="6. تحريم فرض غرامات التأخير والفوائد على المدين المماطل في السداد شيخ الإسلام ابن تيمية (رحمه الله) الزيادة المشروطة على الدين مقابل تأخير السداد هي عين ربا الجاهلية («إما أن تقضي وإما أن تُربي»). ولا يجوز اشتراط غرامة مالية تدفع للبائع عند التأخر في سداد الأقساط أو الفواتير. مجموع الفتاوى (29/425) وقرارات هيئة كبار العلماء">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    6. تحريم فرض غرامات التأخير والفوائد على المدين المماطل في السداد
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> شيخ الإسلام ابن تيمية (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: مجموع الفتاوى (29/425) وقرارات هيئة كبار العلماء
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-rose-500/25 text-rose-300 border-rose-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✕</span>
                                <span>محرم قطعاً وباطل شرعاً (صريح ربا الجاهلية)</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            الزيادة المشروطة على الدين مقابل تأخير السداد هي عين ربا الجاهلية («إما أن تقضي وإما أن تُربي»). ولا يجوز اشتراط غرامة مالية تدفع للبائع عند التأخر في سداد الأقساط أو الفواتير.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قوله تعالى: ﴿وَإِن كَانَ ذُو عُسْرَةٍ فَنَظِرَةٌ إِلَىٰ مَيْسَرَةٍ﴾، وإجماع الأئمة على تحريم زيادة الدين لأجل التأخير.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    الحل الشرعي لحفظ الحقوق: طلب كفيل غارم، شيك ضمان، رهن أصول، أو رفع أمر المماطل للقضاء لإلزامه بالسداد وتعزيره.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 7 -->
                    <div class="fiqh-master-card" data-scholar="ibn_al_qayyim" data-topic="riba" data-search="7. إبطال حيل الربا، بيع العِينة، وضوابط التورق المنضبط الإمام شمس الدين ابن قيم الجوزية (رحمه الله) بسط الإمام ابن القيم رحمه الله القول في إبطال الحيل التي يتوصل بها إلى استحلال الربا باسم البيع (كبيع العِينة: أن يبيع التاجر سلعة بالأجل ثم يشتريها نقداً بأقل). وأكد أن العبرة في العقود بالمقاصد والمعاني لا بالألفاظ والمباني. إعلام الموقعين عن رب العالمين (3/182) وإغاثة اللهفان">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    7. إبطال حيل الربا، بيع العِينة، وضوابط التورق المنضبط
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام شمس الدين ابن قيم الجوزية (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: إعلام الموقعين عن رب العالمين (3/182) وإغاثة اللهفان
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>تحريم الحيل الربوية وبيع العينة · وجواز التورق الحقيقي المنضبط</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            بسط الإمام ابن القيم رحمه الله القول في إبطال الحيل التي يتوصل بها إلى استحلال الربا باسم البيع (كبيع العِينة: أن يبيع التاجر سلعة بالأجل ثم يشتريها نقداً بأقل). وأكد أن العبرة في العقود بالمقاصد والمعاني لا بالألفاظ والمباني.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    حديث النبي ﷺ: «إذا تبايعتم بالعِينة، وأخذتم أذناب البقر، ورضيتم بالزرع، وتركتم الجهاد، سلط الله عليكم ذلاً لا ينزعه حتى ترجعوا إلى دينكم» رواه أبو داود وصححه الألباني.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    التطبيق في النظام: يشترط في التورق أن يقبض المشتري السلعة فعلياً وتدخل في حيازته وضمانه، ثم يبيعها لطرف ثالث مستقل تماماً عن البائع الأول.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 8 -->
                    <div class="fiqh-master-card" data-scholar="al_nawawi" data-topic="defects" data-search="8. تحريم بيع الغرر والجهالة والمخاطرة في عقود التجارة الإمام محيي الدين يحيى بن شرف النووي (رحمه الله) قال الإمام النووي رحمه الله: «النهي عن بيع الغرر أصل عظيم من أصول كتاب البيوع، يدخل فيه مسائل كثيرة غير منحصرة». فيحرم بيع ما فيه جهالة فاحشة في ذات المبيع، أو مقداره، أو سُمكه، أو صفته، أو ثمنه، أو أجله. المجموع شرح المهذب (9/258) وشرح صحيح مسلم (10/156)">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    8. تحريم بيع الغرر والجهالة والمخاطرة في عقود التجارة
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محيي الدين يحيى بن شرف النووي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: المجموع شرح المهذب (9/258) وشرح صحيح مسلم (10/156)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-rose-500/25 text-rose-300 border-rose-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✕</span>
                                <span>محرم بإجماع الأمة في الغرر الكثير المؤثر</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            قال الإمام النووي رحمه الله: «النهي عن بيع الغرر أصل عظيم من أصول كتاب البيوع، يدخل فيه مسائل كثيرة غير منحصرة». فيحرم بيع ما فيه جهالة فاحشة في ذات المبيع، أو مقداره، أو سُمكه، أو صفته، أو ثمنه، أو أجله.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    حديث أبي هريرة رضي الله عنه: «نهى رسول الله ﷺ عن بيع الحصاة، وعن بيع الغرر» رواه مسلم.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    التطبيق في النظام: التوصيف التام والدقيق للصنف بالاسم والعيار والوزن والعدد والسعر الإفرادي بالفاتورة لقطع الجهالة والنزاع.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 9 -->
                    <div class="fiqh-master-card" data-scholar="al_dhahabi" data-topic="pricing" data-search="9. التطفيف في الكيل والميزان والغش والاحتكار في التجارة الإمام الحافظ شمس الدين الذهبي (رحمه الله) عدّ الإمام الذهبي رحمه الله التطفيف في الكيل والميزان (كنقص وزن حديد التسليح أو الخرسانة أو البضائع المعبأة عن المقدار المذكور) من كبائر الذنوب التي توعد الله أصحابها بالويل والهلاك في الآخرة. كتاب الكبائر (الكبيرة 29: التطفيف في الكيل والوزن، والكبيرة 30)">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    9. التطفيف في الكيل والميزان والغش والاحتكار في التجارة
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام الحافظ شمس الدين الذهبي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: كتاب الكبائر (الكبيرة 29: التطفيف في الكيل والوزن، والكبيرة 30)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-rose-500/25 text-rose-300 border-rose-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✕</span>
                                <span>من كبائر الذنوب المحرمة بإجماع المسلمين</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            عدّ الإمام الذهبي رحمه الله التطفيف في الكيل والميزان (كنقص وزن حديد التسليح أو الخرسانة أو البضائع المعبأة عن المقدار المذكور) من كبائر الذنوب التي توعد الله أصحابها بالويل والهلاك في الآخرة.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قوله تعالى: ﴿وَيْلٌ لِّلْمُطَفِّفِينَ * الَّذِينَ إِذَا اكْتَالُوا عَلَى النَّاسِ يَسْتَوْفُونَ * وَإِذَا كَالُوهُمْ أَو وَّزَنُوهُمْ يُخْسِرُونَ﴾.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    التطبيق في النظام: استخدام حاسبات الأوزان والمعايرة الرقمية الدقيقة وتطابق وزن الفاتورة مع وزن الميزان البسكول الفعلي.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 10 -->
                    <div class="fiqh-master-card" data-scholar="al_shinqiti" data-topic="contracts" data-search="10. ضوابط التأمين التجاري مقابل التأمين التعاوني التكافلي العلامة محمد الأمين الشنقيطي (رحمه الله) أوضح العلامة الشنقيطي رحمه الله في تفسيره أن عقود التأمين التجاري التقليدي تشتمل على الغرر الفاحش والمقامرة والربا لأن المستأمن يدفع أقساطاً ولا يدري هل يعوض أم لا. وأجاز التأمين التعاوني التكافلي القائم على التبرع المحض والتكافل بين المشتركين. أضواء البيان في إيضاح القرآن بالقرآن (تفسير سورة المائدة 2/45)">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    10. ضوابط التأمين التجاري مقابل التأمين التعاوني التكافلي
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> العلامة محمد الأمين الشنقيطي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: أضواء البيان في إيضاح القرآن بالقرآن (تفسير سورة المائدة 2/45)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>تحريم التأمين التجاري الربوي · وجواز التأمين التعاوني القائم على التبرع</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            أوضح العلامة الشنقيطي رحمه الله في تفسيره أن عقود التأمين التجاري التقليدي تشتمل على الغرر الفاحش والمقامرة والربا لأن المستأمن يدفع أقساطاً ولا يدري هل يعوض أم لا. وأجاز التأمين التعاوني التكافلي القائم على التبرع المحض والتكافل بين المشتركين.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قوله تعالى: ﴿وَتَعَاوَنُوا عَلَى الْبِرِّ وَالتَّقْوَىٰ وَلَا تَعَاوَنُوا عَلَى الْإِثْمِ وَالْعُدْوَانِ﴾، ونهي النبي ﷺ عن الميسر والغرر.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    التطبيق في النظام: اختيار شركات التأمين التكافلي الإسلامي المعتمدة في بوالص التأمين على البضائع والحاويات البحرية.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 11 -->
                    <div class="fiqh-master-card" data-scholar="al_fawzan" data-topic="exchange" data-search="11. الصرف والتحويلات الفورية عبر تطبيقات الموبايل بنك (القبض الحكمي) معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) يشترط في بيع العملات المختلفة (دولار مقابل جنيه أو ريال) التقابض الفوري. والقيد المصرفي المكتمل أو إشعار التحويل البنكي الفوري يُعد قبضاً حكمياً معتبراً شرعاً يقوم مقام القبض باليد. المنتقى من فتاوى الفوزان ومجمع الفقه الإسلامي">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    11. الصرف والتحويلات الفورية عبر تطبيقات الموبايل بنك (القبض الحكمي)
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: المنتقى من فتاوى الفوزان ومجمع الفقه الإسلامي
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز شرعاً طالما تم القيد والتحويل الفوري في المجلس</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يشترط في بيع العملات المختلفة (دولار مقابل جنيه أو ريال) التقابض الفوري. والقيد المصرفي المكتمل أو إشعار التحويل البنكي الفوري يُعد قبضاً حكمياً معتبراً شرعاً يقوم مقام القبض باليد.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    حديث النبي ﷺ: «فَإِذَا اخْتَلَفَتْ هَذِهِ الأَصْنَافُ فَبِيعُوا كَيْفَ شِئْتُمْ إِذَا كَانَ يَدًا بِيَدٍ».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    التنبيه في البرنامج: لا يجوز الصرف بالدين أو تأجيل تسليم أحد المبلغين، بل يجب أن يكون التحويل لحظياً ومؤكداً بالإشعار.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 12 -->
                    <div class="fiqh-master-card" data-scholar="ibn_baz" data-topic="checks" data-search="12. تحريم خصم الشيكات والأوراق التجارية الآجلة (بيع الدين بنقد حال أقل) الإمام عبد العزيز بن باز (رحمه الله) أفتى الشيخ ابن باز رحمه الله بتحريم بيع الشيك الآجل بنقد حال أقل من قيمته (مثل بيع شيك بـ 100 ألف جنيه مقابل 90 ألف كاش)، لأن حقيقته مبادلة نقد بنقد مع التفاضل والتأجيل وهو صريح الربا. مجموع فتاوى ابن باز (19/282)">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    12. تحريم خصم الشيكات والأوراق التجارية الآجلة (بيع الدين بنقد حال أقل)
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام عبد العزيز بن باز (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: مجموع فتاوى ابن باز (19/282)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-rose-500/25 text-rose-300 border-rose-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✕</span>
                                <span>محرم تحريماً قاطعاً (ربا فضل وربا نسيئة)</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            أفتى الشيخ ابن باز رحمه الله بتحريم بيع الشيك الآجل بنقد حال أقل من قيمته (مثل بيع شيك بـ 100 ألف جنيه مقابل 90 ألف كاش)، لأن حقيقته مبادلة نقد بنقد مع التفاضل والتأجيل وهو صريح الربا.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قول النبي ﷺ: «الذهب بالذهب والفضة بالفضة... مثلاً بمثل، يداً بيد».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    البديل الشرعي: الانتظار حتى موعد استحقاق الشيك، أو توكيل محصل ديون بأجرة مقطوعة ومعلومة مقابل أتعاب التحصيل والمتابعة.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 13 -->
                    <div class="fiqh-master-card" data-scholar="ibn_taymiyyah" data-topic="araboon" data-search="13. بيع العربون واسترداده في حال فسخ الصفقة من قِبل المشتري شيخ الإسلام ابن تيمية (رحمه الله) اختيار شيخ الإسلام ابن تيمية والإمام أحمد وابن القيم وابن باز والعثيمين: جواز بيع العربون (دفع مبلغ مقدماً فإن أتم المشتري البيع حُسب من الثمن، وإن نكل وفسخ فللبائع الاحتفاظ به) لثبوته عن عمر بن الخطاب رضي الله عنه. مجموع الفتاوى (29/536) واختيار الإمام أحمد">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    13. بيع العربون واسترداده في حال فسخ الصفقة من قِبل المشتري
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> شيخ الإسلام ابن تيمية (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: مجموع الفتاوى (29/536) واختيار الإمام أحمد
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز شرعاً للبائع تملك العربون إذا تراجع المشتري</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            اختيار شيخ الإسلام ابن تيمية والإمام أحمد وابن القيم وابن باز والعثيمين: جواز بيع العربون (دفع مبلغ مقدماً فإن أتم المشتري البيع حُسب من الثمن، وإن نكل وفسخ فللبائع الاحتفاظ به) لثبوته عن عمر بن الخطاب رضي الله عنه.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    ما رواه الأثرم وابن أبي شيبة عن نافع بن الحارث أنه اشترى لعمر دار السجن بمكة إن رضي عمر فالبيع نافذ وإلا فله كذا وكذا.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إثبات سند قبض العربون، وخصمه من إجمالي الفاتورة، أو قيده كتعويض حال تراجع العميل.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 14 -->
                    <div class="fiqh-master-card" data-scholar="ibn_al_qayyim" data-topic="contracts" data-search="14. جواز عقد الاستصناع والمقاولات والصناعة بالطلب الإمام شمس الدين ابن قيم الجوزية (رحمه الله) قرر الإمام ابن القيم وشيخ الإسلام ابن تيمية صحة عقد الاستصناع (الاتفاق مع الحداد أو المصنع على تصنيع أبواب، شبابيك، أو قطع غيار بمواصفات محددة ومواد من عند الصانع) وأنه عقد مستقل جائز شرعاً. إعلام الموقعين عن رب العالمين (4/12) ومجموع الفتاوى">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    14. جواز عقد الاستصناع والمقاولات والصناعة بالطلب
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام شمس الدين ابن قيم الجوزية (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: إعلام الموقعين عن رب العالمين (4/12) ومجموع الفتاوى
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز شرعاً وصحيح لحاجة الناس وتعاملهم</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            قرر الإمام ابن القيم وشيخ الإسلام ابن تيمية صحة عقد الاستصناع (الاتفاق مع الحداد أو المصنع على تصنيع أبواب، شبابيك، أو قطع غيار بمواصفات محددة ومواد من عند الصانع) وأنه عقد مستقل جائز شرعاً.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    أن النبي ﷺ استصنع خاتماً من ذهب ثم رمى به واستصنع خاتماً من فضة، واستصنع المنبر من خشب.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: فتح أمر تشغيل / تصنيع (Work Order) وتحديد المواد وتكلفة المصنعية وتاريخ التسليم بدقة.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 15 -->
                    <div class="fiqh-master-card" data-scholar="ibn_uthaymeen" data-topic="contracts" data-search="15. الشرط الجزائي في عقود التوريد والمقاولات والصيانة الإمام محمد بن صالح العثيمين (رحمه الله) يجوز الاتفاق على الشرط الجزائي في عقود المقاولات والتوريد لجبر الضرر المالي الحقيقي الناتج عن تأخر المقاول أو المورد عن الموعد المحدد، ولا يجوز اشتراطه في الديون والقروض. مجموع فتاوى ورسائل ابن عثيمين (18/142) وهيئة كبار العلماء">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    15. الشرط الجزائي في عقود التوريد والمقاولات والصيانة
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محمد بن صالح العثيمين (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: مجموع فتاوى ورسائل ابن عثيمين (18/142) وهيئة كبار العلماء
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>جائز في عقود الأعمال والمقاولات والتوريد · محرم في الديون</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يجوز الاتفاق على الشرط الجزائي في عقود المقاولات والتوريد لجبر الضرر المالي الحقيقي الناتج عن تأخر المقاول أو المورد عن الموعد المحدد، ولا يجوز اشتراطه في الديون والقروض.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قول النبي ﷺ: «المسلمون على شروطهم إلا شرطاً حرَّم حلالاً أو أحلَّ حراماً» رواه الترمذي وصححه الألباني.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: تقدير غرامة التأخير اليومية في عقود المقاولات بما يعادل الضرر المالي الفعلي فقط دون مبالغة.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 16 -->
                    <div class="fiqh-master-card" data-scholar="ibn_taymiyyah" data-topic="pricing" data-search="16. التسعير وتحديد نسبة الربح وحرية الأسعار والتراضي شيخ الإسلام ابن تيمية (رحمه الله) لم يحدد الشرع نسبة معينة للربح (كالعُشر أو الثلث)، بل الأصل هو التراضي بين البائع والمشتري. ويجوز لولي الأمر التسعير الجبري العادل فقط إذا احتكر التجار أقوات الناس ورفعوا الأسعار ظلماً. الحسبة في الإسلام لمجموع الفتاوى (28/75)">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    16. التسعير وتحديد نسبة الربح وحرية الأسعار والتراضي
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> شيخ الإسلام ابن تيمية (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: الحسبة في الإسلام لمجموع الفتاوى (28/75)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>الأصل حرية الربح بالتراضي · والتسعير الجبري يجب فقط عند الاحتكار</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            لم يحدد الشرع نسبة معينة للربح (كالعُشر أو الثلث)، بل الأصل هو التراضي بين البائع والمشتري. ويجوز لولي الأمر التسعير الجبري العادل فقط إذا احتكر التجار أقوات الناس ورفعوا الأسعار ظلماً.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قول النبي ﷺ لما قيل له سعِّر لنا: «إن الله هو الخالق القابض الباسط الرزاق المسعِّر...» رواه أبو داود والترمذي وصححه الألباني.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: حرية تحديد هامش الربح للصنف مع الالتزام بالصدق وعدم الغش أو الاحتكار.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 17 -->
                    <div class="fiqh-master-card" data-scholar="al_fawzan" data-topic="gold" data-search="17. شراء الذهب والفضة عبر البطاقات المصرفية ونقاط البيع (pos) معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) يجوز شراء الذهب والفضة ببطاقة الصراف المباشرة (مدى / الخصم الفوري) لأن الخصم من حساب المشتري والإيداع في حساب التاجر يتم فوراً في المجلس فيتحقق القبض الشرعي. الفتاوى الاقتصادية وقرار المجمع الفقهي الإسلامي">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    17. شراء الذهب والفضة عبر البطاقات المصرفية ونقاط البيع (POS)
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: الفتاوى الاقتصادية وقرار المجمع الفقهي الإسلامي
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>جائز ببطاقة الخصم المباشر (Debit) · محرم ببطاقة الائتمان الآجلة (Credit)</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يجوز شراء الذهب والفضة ببطاقة الصراف المباشرة (مدى / الخصم الفوري) لأن الخصم من حساب المشتري والإيداع في حساب التاجر يتم فوراً في المجلس فيتحقق القبض الشرعي.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    القبض الحكمي يقوم مقام القبض الحقيقي باليد في المعاملات المالية الحديثة.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تحذير النظام: يُحظر بيع الذهب بالتقسيط أو الشيكات الآجلة أو تأخير التسليم.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 18 -->
                    <div class="fiqh-master-card" data-scholar="al_albani" data-topic="defects" data-search="18. وجوب بيان عيوب البضاعة وخيار العيب ورد السلعة الإمام محمد ناصر الدين الألباني (رحمه الله) يحرم على التاجر كتمان أي عيب في الصنف أو المبيع. ومن اشترى سلعة فوجد بها عيباً قديماً لم يخبر به البائع، فله الخيار الكامل في رد السلعة واسترجاع الثمن كاملاً أو أخذ فارق القيمة (الأرش). سلسلة الأحاديث الصحيحة (رقم 1058) وفتاوى جدة">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    18. وجوب بيان عيوب البضاعة وخيار العيب ورد السلعة
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محمد ناصر الدين الألباني (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: سلسلة الأحاديث الصحيحة (رقم 1058) وفتاوى جدة
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>واجب شرعاً بيان كل عيب · ويحرم كتمانه أو تغطيته</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يحرم على التاجر كتمان أي عيب في الصنف أو المبيع. ومن اشترى سلعة فوجد بها عيباً قديماً لم يخبر به البائع، فله الخيار الكامل في رد السلعة واسترجاع الثمن كاملاً أو أخذ فارق القيمة (الأرش).
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قول النبي ﷺ: «المسلم أخو المسلم، لا يحل لمسلم باع من أخيه بيعاً فيه عيب إلا بينه له» رواه ابن ماجه وصححه الألباني.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إتاحة خيار إرجاع المبيعات واسترجاع النقدية بسهولة عند وجود تلف أو عيب مصنعي.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 19 -->
                    <div class="fiqh-master-card" data-scholar="al_shinqiti" data-topic="contracts" data-search="19. ضوابط بيع السَّلَم في التوريدات والمحاصيل الزراعية العلامة محمد الأمين الشنقيطي (رحمه الله) أكد العلامة الشنقيطي رحمه الله أن عقد السَّلَم مستثنى من بيع ما ليس عند الإنسان لحاجة الناس إليه، ويشترط لصحته: تسليم كامل رأس المال مقدماً في مجلس العقد، وانضباط الصفة والمقدار والأجل المحددين. أضواء البيان في إيضاح القرآن بالقرآن (تفسير سورة البقرة 1/214)">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    19. ضوابط بيع السَّلَم في التوريدات والمحاصيل الزراعية
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> العلامة محمد الأمين الشنقيطي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: أضواء البيان في إيضاح القرآن بالقرآن (تفسير سورة البقرة 1/214)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز شرعاً بإجماع الأمة بشروطه المنضبطة</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            أكد العلامة الشنقيطي رحمه الله أن عقد السَّلَم مستثنى من بيع ما ليس عند الإنسان لحاجة الناس إليه، ويشترط لصحته: تسليم كامل رأس المال مقدماً في مجلس العقد، وانضباط الصفة والمقدار والأجل المحددين.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    حديث ابن عباس: «قدم النبي ﷺ المدينة وهم يسلفون في الثمار السنة والسنتين، فقال: من أسلف في شيء فليسلف في كيل معلوم...» متفق عليه.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: قيد فواتير السَّلَم واستلام كامل الثمن في الخزينة مع تحديد تاريخ التوريد بدقة.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    </div>

                    <div id="no-fatwas-alert" class="p-8 text-center bg-slate-900/60 rounded-2xl border border-slate-800 hidden">
                        <span class="text-4xl block mb-2">🔍</span>
                        <h5 class="text-lg font-black text-slate-300">لا توجد فتاوى مطابقة لخيارات الفرز الحالية</h5>
                        <p class="text-xs text-slate-400 mt-1">يرجى تغيير الشيخ أو الموضوع أو مسح كلمة البحث لإظهار بقية الفتاوى.</p>
                    </div>

                </div>

                <!-- ════════════════════════════════════════════════════════════ -->
                <!-- SUB-SECTION 2: CLASSICAL REFERENCE BOOKS DIRECTORY           -->
                <!-- ════════════════════════════════════════════════════════════ -->
                <div id="fiqh-sub-books" class="space-y-4 hidden">
                    <div class="p-4 bg-amber-500/10 border border-amber-500/30 rounded-2xl">
                        <h3 class="text-lg font-black text-amber-300 flex items-center gap-2">
                            <i class="fa-solid fa-book-bookmark text-amber-400"></i>
                            مكتبة أمهات كتب ومؤلفات البيوع والمعاملات المالية للأئمة الـ 12
                        </h3>
                        <p class="text-xs text-slate-300 font-bold mt-1">
                            قائمة بالمصادر والمراجع الأصلية المعتمدة التي استنبطت منها فتاوى وأحكام المنظومة.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

        <div class="p-4 bg-slate-900/90 rounded-2xl border-2 border-amber-500/30 space-y-3">
            <h4 class="text-base font-black text-white flex items-center gap-2 border-b border-slate-800 pb-2">
                <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                الإمام أحمد بن حنبل (ت 241 هـ)
            </h4>
            <div class="space-y-2">
                
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 مسائل الإمام أحمد في البيوع (رواية عبد الله وأبي داود والأثرم)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">الأصل في تأسيس مذهب أهل السنة بأن الأصل في الشروط والعقود الإباحة وخيار المجلس والشرط.</p>
            </div>
        
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 الجامع في الفقه ومسائل الورع والتجارة</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">ضوابط الورع في التجارة واجتناب الشبهات والربا.</p>
            </div>
        
            </div>
        </div>
    
        <div class="p-4 bg-slate-900/90 rounded-2xl border-2 border-amber-500/30 space-y-3">
            <h4 class="text-base font-black text-white flex items-center gap-2 border-b border-slate-800 pb-2">
                <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                شيخ الإسلام ابن تيمية (ت 728 هـ)
            </h4>
            <div class="space-y-2">
                
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 مجموع الفتاوى (المجلدان 29 و 30 - كتاب البيوع)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">أعظم تحرير فقهي لقواعد المعاملات والعقود والشروط والربا والتسعير في التاريخ الإسلامي.</p>
            </div>
        
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 الحسبة في الإسلام</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">تنظيم الأسواق ومنع الاحتكار والغش ومراقبة الأوزان والمكاييل وضوابط ولاية السوق.</p>
            </div>
        
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 القواعد النورانية الفقهية (كتاب المعاملات)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">تأصيل القواعد الكبرى في العقود والشروط وإباحة ما ينفع الناس.</p>
            </div>
        
            </div>
        </div>
    
        <div class="p-4 bg-slate-900/90 rounded-2xl border-2 border-amber-500/30 space-y-3">
            <h4 class="text-base font-black text-white flex items-center gap-2 border-b border-slate-800 pb-2">
                <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                الإمام ابن قيم الجوزية (ت 751 هـ)
            </h4>
            <div class="space-y-2">
                
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 إعلام الموقعين عن رب العالمين (أبواب الربا والحيل)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">كشف وإبطال حيل الربا، بيع العينة، ضوابط التورق، وصحة عقد الاستصناع والمقاولات.</p>
            </div>
        
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 زاد المعاد في هدي خير العباد (فصل هَديه ﷺ في البيوع)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">بيان المعاملات النبوية الشريفة وأخلاق التاجر المسلم.</p>
            </div>
        
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 إغاثة اللهفان من مصائد الشيطان</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">تفصيل الحيل الربوية المحرمة وحماية المعاملات من الفساد.</p>
            </div>
        
            </div>
        </div>
    
        <div class="p-4 bg-slate-900/90 rounded-2xl border-2 border-amber-500/30 space-y-3">
            <h4 class="text-base font-black text-white flex items-center gap-2 border-b border-slate-800 pb-2">
                <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                الإمام محيي الدين النووي (ت 676 هـ)
            </h4>
            <div class="space-y-2">
                
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 المجموع شرح المهذب (المجلدات 9 و 10 و 11 - كتاب البيوع والربا والسلم)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">الموسوعة الفقهية الشاملة في تفصيل أركان البيع، شروط العوضين، ونفي الغرر والجهالة.</p>
            </div>
        
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 منهاج الطالبين وعمدة المفتين (قسم المعاملات)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">المتن المعتمد في ضبط خيارات البيع والشفعة والرهن والإجارة.</p>
            </div>
        
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 شرح صحيح مسلم (كتاب البيوع والمساقاة والربا)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">تأصيل الأحاديث النبوية الجامعة في تحريم الغرر وبيع الحصاة والربا.</p>
            </div>
        
            </div>
        </div>
    
        <div class="p-4 bg-slate-900/90 rounded-2xl border-2 border-amber-500/30 space-y-3">
            <h4 class="text-base font-black text-white flex items-center gap-2 border-b border-slate-800 pb-2">
                <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                الإمام الحافظ شمس الدين الذهبي (ت 748 هـ)
            </h4>
            <div class="space-y-2">
                
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 كتاب الكبائر (كبائر المعاملات والتجارة)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">بيان كبائر التجارة: الربا، التطفيف في الميزان والكيل، الغش، واليمين الكاذبة لترويج السلعة.</p>
            </div>
        
            </div>
        </div>
    
        <div class="p-4 bg-slate-900/90 rounded-2xl border-2 border-amber-500/30 space-y-3">
            <h4 class="text-base font-black text-white flex items-center gap-2 border-b border-slate-800 pb-2">
                <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                الإمام المجدد الشيخ محمد بن عبد الوهاب (ت 1206 هـ)
            </h4>
            <div class="space-y-2">
                
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 مجموع مؤلفات الشيخ (القسم الثاني: الفقه - كتاب البيوع)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">تحرير أحكام البيوع والربا والديون والشفعة بنصوص الكتاب والسنة.</p>
            </div>
        
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 مختصر الإنصاف والشرح الكبير في الفقه</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">تهذيب الأحكام الفقهية وتجريدها بالأدلة المعتبرة.</p>
            </div>
        
            </div>
        </div>
    
        <div class="p-4 bg-slate-900/90 rounded-2xl border-2 border-amber-500/30 space-y-3">
            <h4 class="text-base font-black text-white flex items-center gap-2 border-b border-slate-800 pb-2">
                <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                العلامة الشيخ عبد الرحمن بن ناصر السعدي (ت 1376 هـ)
            </h4>
            <div class="space-y-2">
                
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 القواعد والأصول الجامعة والفروق والتقاسيم البديعة النافعة</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">تأصيل قواعد: (الأصل في المعاملات الإباحة) و(العرف مطرد كالمشروط) و(الخراج بالضمان).</p>
            </div>
        
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 بهجة قلوب الأبرار وقرة عيون الأخيار (شرح جوامع الأخبار في البيوع)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">شرح أحاديث البيع المبرور وبركة الصدق ووعيد الكتمان.</p>
            </div>
        
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 الفتاوى السعدية في المعاملات والمعاملات المالية المستجدة</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">فتاوى عصرية في الشركات، الوكالات، المعاملات البنكية، والأسهم.</p>
            </div>
        
            </div>
        </div>
    
        <div class="p-4 bg-slate-900/90 rounded-2xl border-2 border-amber-500/30 space-y-3">
            <h4 class="text-base font-black text-white flex items-center gap-2 border-b border-slate-800 pb-2">
                <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                العلامة الشيخ محمد الأمين الشنقيطي (ت 1393 هـ)
            </h4>
            <div class="space-y-2">
                
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 أضواء البيان في إيضاح القرآن بالقرآن (تفسير آيات البيوع والربا والعقود)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">تحرير فقهي بديع لآيات سورة البقرة وآل عمران والمائدة، وضوابط التأمين التعاوني والسَّلَم.</p>
            </div>
        
            </div>
        </div>
    
        <div class="p-4 bg-slate-900/90 rounded-2xl border-2 border-amber-500/30 space-y-3">
            <h4 class="text-base font-black text-white flex items-center gap-2 border-b border-slate-800 pb-2">
                <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                الإمام محمد ناصر الدين الألباني (ت 1420 هـ)
            </h4>
            <div class="space-y-2">
                
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 سلسلة الأحاديث الصحيحة والضعيفة (أحاديث التجارة والبيوع)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">تحقيق وتخريج أحاديث البيوع والغش وخيار العيب والبركة في الصدق.</p>
            </div>
        
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 فتاوى جدة ورابغ (الفتاوى الاقتصادية والمالية)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">فتاوى في الصرف، تحريم الفوائد البنكية، وأحكام تجارة الذهب والعملات.</p>
            </div>
        
            </div>
        </div>
    
        <div class="p-4 bg-slate-900/90 rounded-2xl border-2 border-amber-500/30 space-y-3">
            <h4 class="text-base font-black text-white flex items-center gap-2 border-b border-slate-800 pb-2">
                <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                الإمام عبد العزيز بن باز (ت 1420 هـ)
            </h4>
            <div class="space-y-2">
                
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 مجموع فتاوى ومقالات متنوعة (المجلدان 19 و 20 - كتاب البيوع)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">المرجع الفقهي الأول في فتاوى البيوع والتقسيط وخصم الشيكات والمرابحة والتأمين.</p>
            </div>
        
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 فتاوى نور على الدرب (قسم المعاملات والبيوع والربا)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">إجابات واضحة ومباشرة لآلاف الأسئلة في التجارة والأسواق.</p>
            </div>
        
            </div>
        </div>
    
        <div class="p-4 bg-slate-900/90 rounded-2xl border-2 border-amber-500/30 space-y-3">
            <h4 class="text-base font-black text-white flex items-center gap-2 border-b border-slate-800 pb-2">
                <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                الإمام محمد بن صالح العثيمين (ت 1421 هـ)
            </h4>
            <div class="space-y-2">
                
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 الشرح الممتع على زاد المستقنع (المجلدات 8 و 9 - كتاب البيع والربا والسلم والإجارة)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">أوسع وأسهل شرح معاصر لفقه البيوع مع التطبيق على المعاملات المالية الحديثة.</p>
            </div>
        
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 فقه المعاملات المالية المعاصرة</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">تأصيل أحكام بطاقات الائتمان، الصرف الإلكتروني، الأسهم، والتأمين.</p>
            </div>
        
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 مجموع الفتاوى والرسائل (المجلدان 18 و 19 - البيوع)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">فتاوى محررة بالأدلة المقارنة لمسائل البيوع اليومية.</p>
            </div>
        
            </div>
        </div>
    
        <div class="p-4 bg-slate-900/90 rounded-2xl border-2 border-amber-500/30 space-y-3">
            <h4 class="text-base font-black text-white flex items-center gap-2 border-b border-slate-800 pb-2">
                <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)
            </h4>
            <div class="space-y-2">
                
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 الملخص الفقهي (كتاب المعاملات والبيوع)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">الدليل الفقهي الميسر والمحرر لكل تاجر ومحاسب ومسلم في أبواب البيوع.</p>
            </div>
        
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <span class="text-amber-300 font-black text-sm block">📚 المنتقى من فتاوى الفوزان (فتاوى المعاملات المصرفية والبيوع المعاصرة)</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">أحكام التطبيقات البنكية الحديثة، بطاقات مدى، وصرف العملات.</p>
            </div>
        
            </div>
        </div>
    
                    </div>
                </div>

                <!-- ════════════════════════════════════════════════════════════ -->
                <!-- SUB-SECTION 3: 5 GOLDEN FIQH MAXIMS OF COMMERCE              -->
                <!-- ════════════════════════════════════════════════════════════ -->
                <div id="fiqh-sub-maxims" class="space-y-4 hidden">
                    <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl">
                        <h3 class="text-lg font-black text-emerald-400 flex items-center gap-2">
                            <i class="fa-solid fa-scale-balanced"></i>
                            القواعد الفقهية الكبرى الـ 5 الحاكمة للبيوع والتجارة في الإسلام
                        </h3>
                        <p class="text-xs text-slate-300 font-bold mt-1">
                            القواعد الكلية التي تفرعت عنها أحكام التجارة والشركات والمصارف.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <div class="p-5 bg-slate-900/90 rounded-2xl border-2 border-emerald-500/40 space-y-2">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                <h4 class="text-base font-black text-emerald-300">1. «الأصل في العقود والشروط الحل والإباحة»</h4>
                                <span class="text-xs font-black px-2.5 py-1 bg-emerald-500/20 text-emerald-300 rounded-lg">قاعدة عامة</span>
                            </div>
                            <p class="text-xs text-slate-300 leading-relaxed font-bold">
                                كل عقد أو شرط تجاري ابتكره الناس لتحقيق مصالحهم فهو جائز ومباح شرعاً، إلا إذا تضمن ربا، أو غشاً، أو ظلماً، أو ميسراً، أو غرراً فاحشاً.
                            </p>
                            <span class="text-[11px] text-slate-400 block font-bold">المقررون: الإمام أحمد، ابن تيمية، ابن القيم، السعدي، وابن عثيمين.</span>
                        </div>

                        <div class="p-5 bg-slate-900/90 rounded-2xl border-2 border-amber-500/40 space-y-2">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                <h4 class="text-base font-black text-amber-300">2. «الخَرَاجُ بِالضَّمَانِ · والغُنْمُ بِالغُرْمِ»</h4>
                                <span class="text-xs font-black px-2.5 py-1 bg-amber-500/20 text-amber-300 rounded-lg">حديث نبوي صحيح</span>
                            </div>
                            <p class="text-xs text-slate-300 leading-relaxed font-bold">
                                لا يستحق التاجر أو الشريك الربح إلا إذا كان يتحمل مخاطرة الضمان وتلف البضاعة وخسارتها. أما ضمان رأس المال مع اشتراط فائدة مؤكدة فهو عين الربا المحرم.
                            </p>
                            <span class="text-[11px] text-slate-400 block font-bold">التطبيق: تحريم فوائد الودائع البنكية المضمونة مع ربح ثابت، وصحة المضاربة بالمخاطرة.</span>
                        </div>

                        <div class="p-5 bg-slate-900/90 rounded-2xl border-2 border-sky-500/40 space-y-2">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                <h4 class="text-base font-black text-sky-300">3. «لا ضَرَرَ وَلا ضِرَارَ»</h4>
                                <span class="text-xs font-black px-2.5 py-1 bg-sky-500/20 text-sky-300 rounded-lg">حديث نبوي صحيح</span>
                            </div>
                            <p class="text-xs text-slate-300 leading-relaxed font-bold">
                                تحريم كل تصرف تجاري يلحق ضرراً بالمتعاملين أو بالأسواق، كاحتكار السلع الأساسية، التلاعب بالأسعار (النجش)، أو بيع السلع المعيبة دون بيان.
                            </p>
                            <span class="text-[11px] text-slate-400 block font-bold">التطبيق: جواز التسعير الجبري عند احتكار أقوات الناس، وثبوت خيار العيب للمشتري.</span>
                        </div>

                        <div class="p-5 bg-slate-900/90 rounded-2xl border-2 border-purple-500/40 space-y-2">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                <h4 class="text-base font-black text-purple-300">4. «العَادَةُ مُحَكَّمَةٌ · والمَعْرُوفُ عُرْفاً كَالمَشْرُوطِ شَرْطاً»</h4>
                                <span class="text-xs font-black px-2.5 py-1 bg-purple-500/20 text-purple-300 rounded-lg">قاعدة أصولية</span>
                            </div>
                            <p class="text-xs text-slate-300 leading-relaxed font-bold">
                                ما تعارف عليه التجار وأهل السوق من مواعيد التسليم، طرق التغليف، مواعيد السداد، والخصومات، يُعد ملزماً كأنه منصوص عليه في صلب العقد.
                            </p>
                            <span class="text-[11px] text-slate-400 block font-bold">التطبيق: تطبيق أعراف التجارة الإلكترونية والشحن والتسليم.</span>
                        </div>

                    </div>
                </div>

                <!-- ════════════════════════════════════════════════════════════ -->
                <!-- SUB-SECTION 4: ISLAMIC BUSINESS TERMINOLOGY GUIDE            -->
                <!-- ════════════════════════════════════════════════════════════ -->
                <div id="fiqh-sub-terms" class="space-y-4 hidden">
                    <div class="p-4 bg-cyan-500/10 border border-cyan-500/30 rounded-2xl">
                        <h3 class="text-lg font-black text-cyan-400 flex items-center gap-2">
                            <i class="fa-solid fa-spell-check"></i>
                            معجم المصطلحات الفقهية والاقتصادية للتاجر والمحاسب المسلم
                        </h3>
                        <p class="text-xs text-slate-300 font-bold mt-1">
                            شرح مبسط لأهم العقود والمفاهيم المالية الإسلامية وتطبيقاتها المعاصرة.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">

        <div class="p-3.5 bg-slate-900/90 rounded-2xl border-2 border-cyan-500/30 space-y-1">
            <span class="text-cyan-300 font-black text-sm block">💡 المرابحة للآمر بالشراء:</span>
            <p class="text-xs text-slate-300 font-bold leading-relaxed">أن يشتري البنك أو التاجر السلعة ويقبضها وتدخل في ضمانه، ثم يبيعها للزبون بالأجل بربح معلوم متفق عليه (بديل شرعي صحيح للقرض بفائدة).</p>
        </div>
    
        <div class="p-3.5 bg-slate-900/90 rounded-2xl border-2 border-cyan-500/30 space-y-1">
            <span class="text-cyan-300 font-black text-sm block">💡 السَّلَم (السَّلَف):</span>
            <p class="text-xs text-slate-300 font-bold leading-relaxed">دفع كامل الثمن مقدماً نقداً للبائع في مجلس العقد، مقابل تسليم بضاعة موصوفة بدقة في الذمة (كحديد أو محاصيل) في تاريخ محدد مستقبلاً.</p>
        </div>
    
        <div class="p-3.5 bg-slate-900/90 rounded-2xl border-2 border-cyan-500/30 space-y-1">
            <span class="text-cyan-300 font-black text-sm block">💡 الاستصناع:</span>
            <p class="text-xs text-slate-300 font-bold leading-relaxed">عقد اتفاق مع صانع أو مصنع أو ورشة لتصنيع سلعة بمواصفات ومقاسات محددة بمواد من عند الصانع، وتجوز فيه الدفعات والمقدم.</p>
        </div>
    
        <div class="p-3.5 bg-slate-900/90 rounded-2xl border-2 border-cyan-500/30 space-y-1">
            <span class="text-cyan-300 font-black text-sm block">💡 التورق المنضبط:</span>
            <p class="text-xs text-slate-300 font-bold leading-relaxed">شراء سلعة بالأجل وقبضها وحيازتها فعلياً، ثم بيعها نقداً لطرف ثالث مستقل للحصول على السيولة النقدية دون تواطؤ.</p>
        </div>
    
        <div class="p-3.5 bg-slate-900/90 rounded-2xl border-2 border-cyan-500/30 space-y-1">
            <span class="text-cyan-300 font-black text-sm block">💡 بيع العِينة (المحرم):</span>
            <p class="text-xs text-slate-300 font-bold leading-relaxed">أن يبيع التاجر سلعة بالأجل ثم يعيد شراءها نفسها من المشتري نقداً بسعر أقل، وهي حيلة صريحة للربا.</p>
        </div>
    
        <div class="p-3.5 bg-slate-900/90 rounded-2xl border-2 border-cyan-500/30 space-y-1">
            <span class="text-cyan-300 font-black text-sm block">💡 بيع العربون:</span>
            <p class="text-xs text-slate-300 font-bold leading-relaxed">دفع جزء من الثمن مقدماً، فإن أتم المشتري الصفقة حُسب من الثمن، وإن نكل وفسخ فللبائع تملك العربون جبراناً للانتظار.</p>
        </div>
    
        <div class="p-3.5 bg-slate-900/90 rounded-2xl border-2 border-cyan-500/30 space-y-1">
            <span class="text-cyan-300 font-black text-sm block">💡 القبض الحكمي:</span>
            <p class="text-xs text-slate-300 font-bold leading-relaxed">استلام إشعار التحويل البنكي أو قيد المبلغ في الحساب المصرفي فوراً، وهو يقوم مقام القبض باليد في صرف العملات.</p>
        </div>
    
        <div class="p-3.5 bg-slate-900/90 rounded-2xl border-2 border-cyan-500/30 space-y-1">
            <span class="text-cyan-300 font-black text-sm block">💡 الشرط الجزائي:</span>
            <p class="text-xs text-slate-300 font-bold leading-relaxed">اتفاق مالي في عقود المقاولات والتوريد لجبر الضرر المالي الفعلي الناتج عن التأخير، ويجوز في الأعمال ويحرم في الديون.</p>
        </div>
    
                    </div>
                </div>

            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 8. SUITE: ENTERPRISE ZAKAT CALCULATOR                            -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-zakat" class="suite-section anim-suite hidden space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(16, 185, 129, 0.4);">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-emerald-400 flex items-center gap-2">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        حاسبة الزكاة الشرعية الذكية للمؤسسات وعروض التجارة والسيولة
                    </h3>
                    <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-3 py-1 rounded-lg border border-emerald-500/20">
                        نسبة ربع العشر (2.5%) عند حولان الحول
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4 lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">1. بضاعة عروض التجارة بالمخازن (بسعر السوق الجاري):</label>
                                <input type="number" id="zakat_inventory_val" value="10000000" oninput="calculateZakat()" class="tool-input text-lg font-bold text-emerald-400">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">2. النقدية والسيولة بالخزائن والحسابات البنكية:</label>
                                <input type="number" id="zakat_cash_val" value="3500000" oninput="calculateZakat()" class="tool-input text-lg font-bold text-emerald-400">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">3. الديون الجيدة المرجوة السداد على العملاء:</label>
                                <input type="number" id="zakat_debts_in" value="2000000" oninput="calculateZakat()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-rose-300 block mb-1">4. يُخصم: الديون والالتزامات الحالة على المنشأة:</label>
                                <input type="number" id="zakat_debts_out" value="1500000" oninput="calculateZakat()" class="tool-input text-rose-400">
                            </div>
                        </div>

                        <div class="bg-emerald-950/20 p-4 rounded-xl border border-emerald-500/30 flex items-center justify-between text-xs">
                            <span class="text-slate-300 font-bold">نصاب الذهب الشرعي (85 جرام عيار 24):</span>
                            <div class="flex items-center gap-2">
                                <span class="text-slate-400">سعر جرام الذهب:</span>
                                <input type="number" id="gold_gram_price" value="80000" oninput="calculateZakat()" class="w-24 tool-input text-center py-1">
                                <span class="text-slate-400">SDG</span>
                            </div>
                        </div>
                    </div>

                    <!-- Calculated Zakat Results -->
                    <div class="space-y-3">
                        <div class="calc-result-box" style="border-color: rgba(56, 189, 248, 0.4);">
                            <span class="text-xs text-slate-400 font-bold block">📊 إجمالي الوعاء الزكوي الخاضع للزكاة</span>
                            <span class="text-3xl font-black text-sky-400 mt-1 block font-mono" id="res_zakat_pool">0 SDG</span>
                            <span class="text-xs text-sky-300/80 font-bold mt-1 block" id="res_nisab_status">بلغ النصاب الشرعي ✓</span>
                        </div>

                        <div class="calc-result-box" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.3)); border-color: #10b981;">
                            <span class="text-xs text-emerald-300 font-bold block">⚖️ مقدار الزكاة الواجب إخراجها فوراً (2.5%)</span>
                            <span class="text-4xl font-black text-emerald-400 mt-1 block font-mono" id="res_zakat_due">0 SDG</span>
                            <span class="text-xs text-emerald-200/90 font-bold mt-1 block">ربع العشر من وعاء التجارة والسيولة</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 9. SUITE: FEASIBILITY, MARGIN & BREAK-EVEN CALCULATOR            -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-feasibility" class="suite-section anim-suite hidden space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(244, 63, 94, 0.4);">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-rose-400 flex items-center gap-2">
                        <i class="fa-solid fa-chart-line"></i>
                        حاسبة الجدوى، نقطة التعادل (Break-Even)، وهامش الربح مقابل الإضافة
                    </h3>
                    <span class="text-xs font-bold text-rose-400 bg-rose-500/10 px-3 py-1 rounded-lg border border-rose-500/20">
                        Margin vs Markup & ROI
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4 lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">المصاريف الثابتة الشهرية (إيجار + رواتب + كهرباء):</label>
                                <input type="number" id="fe_fixed_costs" value="500000" oninput="calculateFeasibility()" class="tool-input text-lg font-bold">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">متوسط سعر بيع الوحدة / القطعة:</label>
                                <input type="number" id="fe_unit_price" value="1000" oninput="calculateFeasibility()" class="tool-input text-lg font-bold text-emerald-400">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">التكلفة المتغيرة للوحدة (سعر الشراء):</label>
                                <input type="number" id="fe_unit_cost" value="750" oninput="calculateFeasibility()" class="tool-input text-rose-400">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">المبيعات الشهرية المتوقعة (عدد القطع):</label>
                                <input type="number" id="fe_expected_qty" value="3000" oninput="calculateFeasibility()" class="tool-input text-sky-400">
                            </div>
                        </div>
                    </div>

                    <!-- Feasibility Results -->
                    <div class="space-y-3">
                        <div class="calc-result-box" style="border-color: rgba(245, 158, 11, 0.4);">
                            <span class="text-xs text-slate-400 font-bold block">🎯 نقطة التعادل (Break-Even Units)</span>
                            <span class="text-3xl font-black text-amber-400 mt-1 block font-mono" id="res_breakeven_units">0 قطعة</span>
                            <span class="text-xs text-amber-300/80 font-bold mt-1 block" id="res_breakeven_revenue">0 SDG مبيعات للتعادل</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(16, 185, 129, 0.4);">
                            <span class="text-xs text-slate-400 font-bold block">💵 صافي الأرباح الشهرية المتوقعة</span>
                            <span class="text-3xl font-black text-emerald-400 mt-1 block font-mono" id="res_net_profit_month">0 SDG</span>
                            <span class="text-xs text-emerald-300/80 font-bold mt-1 block" id="res_margin_markup_info">هامش الربح: %0 | الإضافة: %0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 10. SUITE: PAYROLL & GRATUITY CALCULATOR                         -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-hr_gratuity" class="suite-section anim-suite hidden space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(232, 121, 249, 0.4);">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-fuchsia-400 flex items-center gap-2">
                        <i class="fa-solid fa-user-tie"></i>
                        حاسبة مكافأة نهاية الخدمة، مستحقات الإجازات، وقوانين العمل
                    </h3>
                    <span class="text-xs font-bold text-fuchsia-400 bg-fuchsia-500/10 px-3 py-1 rounded-lg border border-fuchsia-500/20">
                        وفق معايير وقوانين العمل المعتمدة
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4 lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">الراتب الأساسي الأخير للموظف:</label>
                                <input type="number" id="hr_basic_salary" value="200000" oninput="calculateGratuity()" class="tool-input text-lg font-bold text-fuchsia-400">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">سنوات الخدمة في المنشأة:</label>
                                <input type="number" step="0.5" id="hr_service_years" value="6.5" oninput="calculateGratuity()" class="tool-input text-lg font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">سبب انتهاء عقد العمل:</label>
                                <select id="hr_end_reason" onchange="calculateGratuity()" class="tool-input">
                                    <option value="contract_end">انتهاء العقد / فصل تعسفي / تقاعد (مستحقات 100%)</option>
                                    <option value="resignation">استقالة الموظف بمحض إرادته</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">رصيد الإجازات غير المستنفذة (أيام):</label>
                                <input type="number" id="hr_leave_days" value="21" oninput="calculateGratuity()" class="tool-input">
                            </div>
                        </div>
                    </div>

                    <!-- Gratuity Results -->
                    <div class="space-y-3">
                        <div class="calc-result-box" style="border-color: rgba(232, 121, 249, 0.4);">
                            <span class="text-xs text-slate-400 font-bold block">💼 مكافأة نهاية الخدمة المستحقة</span>
                            <span class="text-3xl font-black text-fuchsia-400 mt-1 block font-mono" id="res_gratuity_amount">0 SDG</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(56, 189, 248, 0.4);">
                            <span class="text-xs text-slate-400 font-bold block">🏖️ بدل الإجازات السنوية المستحق</span>
                            <span class="text-2xl font-black text-sky-400 mt-1 block font-mono" id="res_leave_amount">0 SDG</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(16, 185, 129, 0.4);">
                            <span class="text-xs text-slate-400 font-bold block">💵 إجمالي المستحقات النهائية للموظف</span>
                            <span class="text-3xl font-black text-emerald-400 mt-1 block font-mono" id="res_total_hr_due">0 SDG</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center text-xs text-slate-500 font-bold mt-2 no-print">
            نظام Al-Sajana Smart ERP 2026 · منصة المساعدات والحاسبات الهندسية والتجارية ومفاتيح البنوك
        </div>

    </div>

    <!-- JavaScript Controllers -->
    <script>
    let activeScholarFilter = 'all';
    let activeTopicFilter = 'all';

    function switchSuite(suiteId, el) {
        document.querySelectorAll('.suite-section').forEach(sec => sec.classList.add('hidden'));
        document.querySelectorAll('.suite-pill-card').forEach(btn => btn.classList.remove('active'));

        const targetSec = document.getElementById('suite-sec-' + suiteId);
        if (targetSec) targetSec.classList.remove('hidden');
        if (el) el.classList.add('active');
    }

    // ── Fiqh Sub-Tabs Controller ──
    function switchFiqhSubTab(subId, el) {
        document.getElementById('fiqh-sub-fatwas').classList.add('hidden');
        document.getElementById('fiqh-sub-books').classList.add('hidden');
        document.getElementById('fiqh-sub-maxims').classList.add('hidden');
        document.getElementById('fiqh-sub-terms').classList.add('hidden');

        document.querySelectorAll('.fiqh-nav-pill').forEach(btn => btn.classList.remove('active'));

        const target = document.getElementById('fiqh-sub-' + subId);
        if (target) target.classList.remove('hidden');
        if (el) el.classList.add('active');
    }

    // ── Fiqh Filters & Search Controller ──
    function setFiqhScholar(scholar, btn) {
        activeScholarFilter = scholar;
        document.querySelectorAll('[data-filter-type="scholar"]').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        filterFatwas();
    }

    function setFiqhTopic(topic, btn) {
        activeTopicFilter = topic;
        document.querySelectorAll('[data-filter-type="topic"]').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        filterFatwas();
    }

    function setQuickSearch(word) {
        document.getElementById('fiqh-search-input').value = word;
        switchFiqhSubTab('fatwas', document.querySelector('.fiqh-nav-pill'));
        filterFatwas();
    }

    function clearFiqhSearch() {
        document.getElementById('fiqh-search-input').value = '';
        activeScholarFilter = 'all';
        activeTopicFilter = 'all';
        document.querySelectorAll('.fiqh-filter-btn').forEach(b => {
            if (b.getAttribute('data-filter-val') === 'all') b.classList.add('active');
            else b.classList.remove('active');
        });
        filterFatwas();
    }

    function filterFatwas() {
        const query = (document.getElementById('fiqh-search-input').value || '').trim().toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.fiqh-master-card').forEach(card => {
            const cardScholar = card.getAttribute('data-scholar');
            const cardTopic = card.getAttribute('data-topic');
            const cardSearch = card.getAttribute('data-search') || '';

            const matchScholar = (activeScholarFilter === 'all' || cardScholar === activeScholarFilter);
            const matchTopic = (activeTopicFilter === 'all' || cardTopic === activeTopicFilter);
            const matchSearch = (query === '' || cardSearch.includes(query));

            if (matchScholar && matchTopic && matchSearch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const badgeEl = document.getElementById('fiqh_search_badge');
        if (badgeEl) {
            badgeEl.textContent = visibleCount + ' فتوى مطابقة ✓';
            badgeEl.className = visibleCount > 0 ? 'text-emerald-400 font-mono font-black text-xs' : 'text-rose-400 font-mono font-black text-xs';
        }

        const noDataEl = document.getElementById('no-fatwas-alert');
        if (noDataEl) {
            noDataEl.classList.toggle('hidden', visibleCount > 0);
        }
    }

    function toggleSolarMode() {
        const mode = document.getElementById('solar_sys_mode').value;
        const homeInp = document.getElementById('solar_home_inputs');
        const pumpInp = document.getElementById('solar_pump_inputs');
        if (mode === 'home') {
            homeInp.classList.remove('hidden');
            pumpInp.classList.add('hidden');
        } else {
            homeInp.classList.add('hidden');
            pumpInp.classList.remove('hidden');
        }
        calculateSolar();
    }

    // 1. Steel & Metals Calculation
    function calculateSteel() {
        const preset = document.getElementById('steel_preset_size').value;
        const targetQty = parseFloat(document.getElementById('steel_target_qty').value || 1);
        const targetUnit = document.getElementById('steel_target_unit').value;
        const prodType = document.getElementById('steel_product_type').value;

        let w_mm = parseFloat(document.getElementById('steel_width_mm').value || 12);
        let h_mm = parseFloat(document.getElementById('steel_height_mm').value || 12);
        let t_mm = parseFloat(document.getElementById('steel_thickness_mm').value || 12);
        let L_m = parseFloat(document.getElementById('steel_length_m').value || 12.0);

        if (preset === 'rebar_8mm') { w_mm = 8; t_mm = 8; L_m = 12.0; }
        else if (preset === 'rebar_10mm') { w_mm = 10; t_mm = 10; L_m = 12.0; }
        else if (preset === 'rebar_12mm') { w_mm = 12; t_mm = 12; L_m = 12.0; }
        else if (preset === 'rebar_16mm') { w_mm = 16; t_mm = 16; L_m = 12.0; }
        else if (preset === 'rebar_20mm') { w_mm = 20; t_mm = 20; L_m = 12.0; }
        else if (preset === 'rebar_25mm') { w_mm = 25; t_mm = 25; L_m = 12.0; }
        else if (preset === 'rebar_32mm') { w_mm = 32; t_mm = 32; L_m = 12.0; }
        else if (preset === '4x8_1.5') { w_mm = 40; h_mm = 80; t_mm = 1.5; L_m = 6.0; }
        else if (preset === '4x8_2.0') { w_mm = 40; h_mm = 80; t_mm = 2.0; L_m = 6.0; }
        else if (preset === '2x4_1.5') { w_mm = 20; h_mm = 40; t_mm = 1.5; L_m = 6.0; }
        else if (preset === '2x4_2.0') { w_mm = 20; h_mm = 40; t_mm = 2.0; L_m = 6.0; }

        if (preset !== 'custom') {
            document.getElementById('steel_width_mm').value = w_mm;
            document.getElementById('steel_height_mm').value = h_mm;
            document.getElementById('steel_thickness_mm').value = t_mm;
            document.getElementById('steel_length_m').value = L_m;
        }

        let weightPerPieceKg = 0;
        if (prodType === 'rebar') {
            const d = w_mm;
            weightPerPieceKg = (d * d / 162.162) * L_m;
        } else if (prodType === 'pipe_rect') {
            const perimeter_m = ((w_mm + h_mm) * 2) / 1000;
            const thickness_m = t_mm / 1000;
            weightPerPieceKg = perimeter_m * thickness_m * 7850 * L_m;
        } else if (prodType === 'pipe_round') {
            const outer_r = w_mm / 2000;
            const inner_r = (w_mm - (t_mm * 2)) / 2000;
            weightPerPieceKg = Math.PI * (outer_r * outer_r - inner_r * inner_r) * 7850 * L_m;
        } else {
            weightPerPieceKg = (w_mm / 1000) * (h_mm / 1000) * (t_mm / 1000) * 7850;
        }

        if (weightPerPieceKg <= 0) weightPerPieceKg = 1;
        const piecesPerTon = Math.floor(1000 / weightPerPieceKg);

        let totalPieces = 0;
        let totalTons = 0;

        if (targetUnit === 'ton') {
            totalTons = targetQty;
            totalPieces = Math.round(targetQty * piecesPerTon);
        } else {
            totalPieces = targetQty;
            totalTons = (targetQty * weightPerPieceKg) / 1000;
        }

        document.getElementById('res_pieces_per_ton').textContent = piecesPerTon.toLocaleString();
        document.getElementById('res_weight_per_piece').textContent = weightPerPieceKg.toFixed(2) + ' kg';
        document.getElementById('res_total_summary').textContent = totalPieces.toLocaleString() + ' قطعة = ' + totalTons.toFixed(2) + ' طن';
    }

    // 2. Concrete Calculation
    function calculateConcrete() {
        const area = parseFloat(document.getElementById('concrete_area').value || 100);
        const thickness_cm = parseFloat(document.getElementById('concrete_thickness').value || 15);
        const floors = parseFloat(document.getElementById('concrete_floors').value || 1);
        const rebarRatio = parseFloat(document.getElementById('concrete_rebar_ratio').value || 110);
        const wallArea = parseFloat(document.getElementById('wall_area_m2').value || 200);
        const tilesArea = parseFloat(document.getElementById('tiles_area_m2').value || 150);

        const vol_m3 = (area * (thickness_cm / 100)) * 1.25 * floors;
        const rebar_tons = (vol_m3 * rebarRatio) / 1000;
        const cement_bags = Math.ceil(vol_m3 * 7);
        const wall_blocks = Math.ceil(wallArea * 12.5);
        const tiles_boxes = Math.ceil(tilesArea / 1.44);
        const tiles_glue = Math.ceil(tilesArea / 5);

        document.getElementById('res_concrete_m3').textContent = vol_m3.toFixed(2) + ' m³';
        document.getElementById('res_rebar_tons').textContent = rebar_tons.toFixed(2) + ' طن';
        document.getElementById('res_cement_bags').textContent = cement_bags.toLocaleString() + ' كيس';
        document.getElementById('res_wall_blocks').textContent = wall_blocks.toLocaleString() + ' حبة';
        document.getElementById('res_tiles_boxes').textContent = tiles_boxes.toLocaleString() + ' كرتونة';
        document.getElementById('res_tiles_glue').textContent = tiles_glue.toLocaleString() + ' كيس';
    }

    // 3. Solar Calculation
    function calculateSolar() {
        const mode = document.getElementById('solar_sys_mode').value;
        if (mode === 'home') {
            const acCount = parseFloat(document.getElementById('solar_ac_count').value || 0);
            const fridgeCount = parseFloat(document.getElementById('solar_fridge_count').value || 0);
            const lightsWatt = parseFloat(document.getElementById('solar_lights_watt').value || 0);
            const nightHours = parseFloat(document.getElementById('solar_night_hours').value || 8);
            const panelWatt = parseFloat(document.getElementById('solar_panel_watt').value || 550);
            const batType = document.getElementById('solar_bat_type').value;

            const totalRunningWatt = (acCount * 1800) + (fridgeCount * 350) + lightsWatt;
            const inverterKw = Math.ceil((totalRunningWatt * 1.3) / 1000);
            const dailyKwh = (totalRunningWatt * 10) / 1000;
            const panelCount = Math.ceil((dailyKwh * 1000) / (panelWatt * 4.5));

            const dod = batType === 'lithium' ? 0.8 : 0.5;
            const nightWh = (totalRunningWatt * 0.4) * nightHours;
            const batteryAh48V = Math.ceil((nightWh / 48) / dod);

            document.getElementById('res_inverter_kw').textContent = Math.max(3, inverterKw) + ' kW';
            document.getElementById('res_panel_count').textContent = Math.max(4, panelCount) + ' لوح (' + panelWatt + 'W)';
            document.getElementById('res_battery_ah').textContent = batteryAh48V + ' Ah (48V)';
        } else {
            const hp = parseFloat(document.getElementById('solar_pump_hp_val').value || 10);
            const panelWatt = parseFloat(document.getElementById('solar_pump_panel_w').value || 550);
            
            const kw = hp * 0.746;
            const vfdKw = (kw * 1.3).toFixed(1);
            const panelCount = Math.ceil((kw * 1.35 * 1000) / panelWatt);

            document.getElementById('res_inverter_kw').textContent = vfdKw + ' kW Solar VFD';
            document.getElementById('res_panel_count').textContent = panelCount + ' لوح (' + panelWatt + 'W)';
            document.getElementById('res_battery_ah').textContent = 'بدون بطاريات (Direct VFD)';
        }
    }

    // 4. Freight Calculation
    function calculateFreight() {
        const fob = parseFloat(document.getElementById('freight_fob_usd').value || 0);
        const sea = parseFloat(document.getElementById('freight_sea_usd').value || 0);
        const customsPct = parseFloat(document.getElementById('freight_customs_pct').value || 0);
        const clearance = parseFloat(document.getElementById('freight_clearance_usd').value || 0);
        const inland = parseFloat(document.getElementById('freight_inland_usd').value || 0);
        const fx = parseFloat(document.getElementById('freight_fx_rate').value || 600);

        const customsUsd = (fob + sea) * (customsPct / 100);
        const totalLandedUsd = fob + sea + customsUsd + clearance + inland;
        const totalLandedSdg = totalLandedUsd * fx;
        const overheadPct = fob > 0 ? (((totalLandedUsd - fob) / fob) * 100).toFixed(1) : 0;

        document.getElementById('res_landed_usd').textContent = '$' + totalLandedUsd.toLocaleString(undefined, {maximumFractionDigits:2});
        document.getElementById('res_landed_sdg').textContent = totalLandedSdg.toLocaleString(undefined, {maximumFractionDigits:0}) + ' SDG';
        document.getElementById('res_overhead_pct').textContent = '%' + overheadPct;
    }

    // 5. Unit Converter
    function convertUnits(source) {
        if (source === 'sqm') {
            const v = parseFloat(document.getElementById('unit_sqm').value || 0);
            document.getElementById('unit_feddan').value = (v / 4200).toFixed(3);
            document.getElementById('unit_qirat').value = ((v / 4200) * 24).toFixed(2);
        } else if (source === 'feddan') {
            const v = parseFloat(document.getElementById('unit_feddan').value || 0);
            document.getElementById('unit_sqm').value = (v * 4200).toFixed(0);
            document.getElementById('unit_qirat').value = (v * 24).toFixed(1);
        } else if (source === 'qirat') {
            const v = parseFloat(document.getElementById('unit_qirat').value || 0);
            document.getElementById('unit_feddan').value = (v / 24).toFixed(3);
            document.getElementById('unit_sqm').value = ((v / 24) * 4200).toFixed(0);
        } else if (source === 'inch') {
            const v = parseFloat(document.getElementById('unit_inch').value || 0);
            document.getElementById('unit_mm').value = (v * 25.4).toFixed(2);
            document.getElementById('unit_foot').value = (v / 12).toFixed(3);
        } else if (source === 'mm') {
            const v = parseFloat(document.getElementById('unit_mm').value || 0);
            document.getElementById('unit_inch').value = (v / 25.4).toFixed(3);
            document.getElementById('unit_foot').value = (v / 304.8).toFixed(3);
        } else if (source === 'foot') {
            const v = parseFloat(document.getElementById('unit_foot').value || 0);
            document.getElementById('unit_inch').value = (v * 12).toFixed(2);
            document.getElementById('unit_mm').value = (v * 304.8).toFixed(1);
        } else if (source === 'kg') {
            const v = parseFloat(document.getElementById('unit_kg').value || 0);
            document.getElementById('unit_ton').value = (v / 1000).toFixed(3);
            document.getElementById('unit_qantar').value = (v / 45.36).toFixed(2);
        } else if (source === 'ton') {
            const v = parseFloat(document.getElementById('unit_ton').value || 0);
            document.getElementById('unit_kg').value = (v * 1000).toFixed(0);
            document.getElementById('unit_qantar').value = ((v * 1000) / 45.36).toFixed(2);
        } else if (source === 'qantar') {
            const v = parseFloat(document.getElementById('unit_qantar').value || 0);
            document.getElementById('unit_kg').value = (v * 45.36).toFixed(1);
            document.getElementById('unit_ton').value = ((v * 45.36) / 1000).toFixed(3);
        }
    }

    // 6. Bank Search
    function searchBanks(q) {
        const query = q.trim().toLowerCase();
        document.querySelectorAll('.bban-bank-card').forEach(card => {
            const text = card.getAttribute('data-bank-search') || '';
            card.style.display = (query === '' || text.includes(query)) ? '' : 'none';
        });
    }

    // 🌟 Interactive BBAN Generator with Automatic Zero-Stripping 🌟
    function generateUserBBAN() {
        const sel = document.getElementById('bban_bank_selector').value;
        let raw = document.getElementById('bban_raw_account').value.replace(/[^0-9]/g, '');
        const display = document.getElementById('generated_bban_display');
        const lenEl = document.getElementById('bban_len_counter');
        const feedbackEl = document.getElementById('bban_strip_feedback');
        const appBadgeEl = document.getElementById('selected_bank_app_badge');

        const selectObj = document.getElementById('bban_bank_selector');
        const selectedText = selectObj.options[selectObj.selectedIndex].text;
        if (appBadgeEl) {
            appBadgeEl.textContent = '📱 البنك المحدد: ' + selectedText;
        }

        if (sel === 'sahel') {
            display.innerHTML = '<span class="text-amber-300 font-bold text-2xl tracking-normal">يستخرج رقم الـ BBAN مباشرة من داخل تطبيق ساهل (بنك النيل)</span>';
            lenEl.textContent = 'تطبيق ساهل (بنك النيل)';
            if (feedbackEl) feedbackEl.textContent = 'انسخ الـ BBAN من ملفك الشخصي داخل التطبيق';
            return;
        }

        // ⚠️ قاعدة حذف الصفر في بداية رقم الحساب ⚠️
        const hadLeadingZero = raw.startsWith('0');
        let cleanAcc = raw.replace(/^0+/, ''); // حذف أي صفر في البداية

        if (feedbackEl) {
            if (hadLeadingZero) {
                feedbackEl.textContent = '✓ تم حذف الصفر في بداية الحساب تلقائياً لضبط الـ 14 خانة';
                feedbackEl.className = 'text-amber-300 font-black';
            } else if (cleanAcc.length > 0) {
                feedbackEl.textContent = '✓ صيغة رقم الحساب مدخلة وجاهزة للتحويل';
                feedbackEl.className = 'text-emerald-400 font-black';
            } else {
                feedbackEl.textContent = '✓ يحذف الصفر في بداية الحساب تلقائياً';
                feedbackEl.className = 'text-slate-400 font-bold';
            }
        }

        const parts = sel.split('|');
        const prefix = parts[0];
        const xMask = parts[1];
        const suffix = parts[2] || '';

        const neededDigits = xMask.length;
        let padded = cleanAcc;
        if (padded.length === 0) {
            padded = '-'.repeat(neededDigits);
        } else if (padded.length > neededDigits) {
            padded = padded.substring(padded.length - neededDigits);
        } else {
            padded = padded.padStart(neededDigits, '0');
        }

        const fullBBAN = prefix + padded + suffix;
        
        // Render with colored segments
        const styledHtml = '<span class="text-amber-400 font-black">' + prefix + '</span>' +
                           '<span class="text-white font-black px-2">' + padded + '</span>' +
                           '<span class="text-emerald-400 font-black">' + suffix + '</span>';

        display.innerHTML = styledHtml;
        display.setAttribute('data-raw-bban', fullBBAN);
        lenEl.textContent = 'عدد الخانات: ' + fullBBAN.length + ' رقماً ✓ (معتمدة)';
    }

    function copyGeneratedBBAN() {
        const display = document.getElementById('generated_bban_display');
        const rawText = display.getAttribute('data-raw-bban') || display.textContent;
        if (rawText.includes('-') || rawText.includes('يستخرج')) {
            alert('يرجى إدخال رقم حساب صحيح أولاً لتوليد رقم الـ BBAN المكتمل.');
            return;
        }
        navigator.clipboard.writeText(rawText).then(() => {
            alert('✓ تم نسخ رقم الـ BBAN بنجاح:\n' + rawText + '\n\nجاهز الآن للصق في تطبيق التحويل.');
        });
    }

    function copyBBANCode(code, btn) {
        navigator.clipboard.writeText(code).then(() => {
            const oldHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check text-emerald-400"></i> تم نسخ الصيغة!';
            setTimeout(() => { btn.innerHTML = oldHtml; }, 2000);
        });
    }

    // 7. Zakat Calculation
    function calculateZakat() {
        const inv = parseFloat(document.getElementById('zakat_inventory_val').value || 0);
        const cash = parseFloat(document.getElementById('zakat_cash_val').value || 0);
        const debtIn = parseFloat(document.getElementById('zakat_debts_in').value || 0);
        const debtOut = parseFloat(document.getElementById('zakat_debts_out').value || 0);
        const goldPrice = parseFloat(document.getElementById('gold_gram_price').value || 80000);

        const pool = Math.max(0, (inv + cash + debtIn) - debtOut);
        const zakatDue = pool * 0.025;
        const nisabValue = 85 * goldPrice;

        document.getElementById('res_zakat_pool').textContent = pool.toLocaleString() + ' SDG';
        document.getElementById('res_zakat_due').textContent = zakatDue.toLocaleString(undefined, {maximumFractionDigits:2}) + ' SDG';
        
        const nisabEl = document.getElementById('res_nisab_status');
        if (pool >= nisabValue) {
            nisabEl.textContent = '✓ بلغ النصاب الشرعي (' + nisabValue.toLocaleString() + ' SDG)';
            nisabEl.style.color = '#34d399';
        } else {
            nisabEl.textContent = '✕ لم يبلغ النصاب الشرعي (' + nisabValue.toLocaleString() + ' SDG)';
            nisabEl.style.color = '#f87171';
        }
    }

    // 8. Feasibility & Margin Calculation
    function calculateFeasibility() {
        const fixed = parseFloat(document.getElementById('fe_fixed_costs').value || 0);
        const price = parseFloat(document.getElementById('fe_unit_price').value || 0);
        const cost = parseFloat(document.getElementById('fe_unit_cost').value || 0);
        const expQty = parseFloat(document.getElementById('fe_expected_qty').value || 0);

        const contribMargin = Math.max(0.01, price - cost);
        const breakevenUnits = Math.ceil(fixed / contribMargin);
        const breakevenRev = breakevenUnits * price;

        const totalRev = expQty * price;
        const totalCost = fixed + (expQty * cost);
        const netProfit = totalRev - totalCost;

        const markupPct = cost > 0 ? (((price - cost) / cost) * 100).toFixed(1) : 0;
        const marginPct = price > 0 ? (((price - cost) / price) * 100).toFixed(1) : 0;

        document.getElementById('res_breakeven_units').textContent = breakevenUnits.toLocaleString() + ' قطعة';
        document.getElementById('res_breakeven_revenue').textContent = breakevenRev.toLocaleString() + ' SDG مبيعات للتعادل';
        document.getElementById('res_net_profit_month').textContent = netProfit.toLocaleString() + ' SDG';
        document.getElementById('res_margin_markup_info').textContent = 'هامش الربح (Margin): %' + marginPct + ' | الإضافة (Markup): %' + markupPct;
    }

    // 9. Gratuity Calculation
    function calculateGratuity() {
        const basic = parseFloat(document.getElementById('hr_basic_salary').value || 0);
        const years = parseFloat(document.getElementById('hr_service_years').value || 0);
        const reason = document.getElementById('hr_end_reason').value;
        const leaveDays = parseFloat(document.getElementById('hr_leave_days').value || 0);

        let gratuityMonths = 0;
        if (years <= 5) {
            gratuityMonths = years * 0.5;
        } else {
            gratuityMonths = (5 * 0.5) + ((years - 5) * 1.0);
        }

        if (reason === 'resignation') {
            if (years < 2) gratuityMonths = 0;
            else if (years < 5) gratuityMonths = gratuityMonths * (1/3);
            else if (years < 10) gratuityMonths = gratuityMonths * (2/3);
        }

        const gratuityAmt = gratuityMonths * basic;
        const leaveAmt = (basic / 30) * leaveDays;
        const totalDue = gratuityAmt + leaveAmt;

        document.getElementById('res_gratuity_amount').textContent = gratuityAmt.toLocaleString() + ' SDG';
        document.getElementById('res_leave_amount').textContent = leaveAmt.toLocaleString() + ' SDG';
        document.getElementById('res_total_hr_due').textContent = totalDue.toLocaleString() + ' SDG';
    }

    document.addEventListener('DOMContentLoaded', function() {
        calculateSteel();
        calculateConcrete();
        calculateSolar();
        calculateFreight();
        calculateZakat();
        calculateFeasibility();
        calculateGratuity();
        generateUserBBAN();
    });
    </script>
    <script defer src="js/app.js"></script>
</body>
</html>
