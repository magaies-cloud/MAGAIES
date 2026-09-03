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
    
        /* 📱 ULTRA-MODERN MOBILE-FIRST RESPONSIVE DESIGN 📱 */
        @media (max-width: 768px) {
            .tools-master-wrapper {
                padding: 10px !important;
            }
            .glass-panel-v3 {
                padding: 14px !important;
                border-radius: 18px !important;
            }
            #suite-tabs-container {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 8px !important;
            }
            .suite-pill-card {
                padding: 10px 8px !important;
                border-radius: 14px !important;
            }
            .suite-pill-icon {
                font-size: 1.1rem !important;
            }
            .suite-pill-title {
                font-size: 0.78rem !important;
            }
            .suite-pill-sub {
                display: none !important;
            }
            #master_bban_display {
                font-size: 1.25rem !important;
                letter-spacing: 1px !important;
                padding: 12px 6px !important;
            }
            .fiqh-nav-pill {
                font-size: 0.72rem !important;
                padding: 6px 10px !important;
            }
            .fiqh-chip {
                font-size: 0.7rem !important;
                padding: 4px 8px !important;
            }
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
                <div class="suite-pill-title">📜 فتاوى البيوع والمعاملات</div>
                <div class="suite-pill-sub">12 إماماً وموسوعة</div>
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
                    <div class="w-full lg:w-[520px] p-4 bg-slate-950/95 border-2 border-amber-400/80 rounded-2xl shadow-2xl space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-amber-300 flex items-center gap-1.5">
                                <i class="fa-solid fa-magnifying-glass text-amber-400"></i>
                                ابحث في الفتاوى والمسائل (محلياً وفي قوقل):
                            </span>
                            <span id="fiqh_search_badge" class="text-emerald-400 font-mono font-black text-[11px] px-2.5 py-0.5 bg-emerald-950 border border-emerald-500/50 rounded-lg">
                                80 فتوى متاحة للبحث
                            </span>
                        </div>
                        
                        <div class="flex flex-wrap sm:flex-nowrap gap-2 items-center">
                            <div class="relative flex-1 w-full">
                                <input type="text" id="fiqh-search-input" oninput="filterFatwas()" placeholder="اكتب سؤالك: تقسيط، شيك، ذهب، عربون..." class="w-full py-2.5 pr-10 pl-3 bg-slate-900 border-2 border-amber-500/50 focus:border-amber-400 text-white rounded-xl text-xs md:text-sm font-bold shadow-inner">
                                <i class="fa-solid fa-search absolute right-3.5 top-3.5 text-amber-400/80"></i>
                            </div>
                            <button type="button" onclick="executeQuickGoogleSearch()" title="ابحث في قوقل الموثق للشيوخ الـ 12" class="px-4 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 text-white font-black text-xs rounded-xl shadow-lg border border-sky-300/50 flex items-center justify-center gap-1.5 flex-shrink-0 transition transform hover:-translate-y-0.5 w-full sm:w-auto">
                                <i class="fa-brands fa-google text-sky-200"></i>
                                <span>بحث قوقل ↗</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ⚡ 20 CATEGORIZED COMMON COMMERCIAL QUESTIONS (أسئلة شائعة سريعة بنقرة واحدة) ⚡ -->
                <div class="p-4 bg-slate-950/90 rounded-2xl border border-amber-500/30 space-y-3 mb-5 shadow-lg">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                        <span class="text-xs sm:text-sm font-black text-amber-300 flex items-center gap-2">
                            <i class="fa-solid fa-bolt text-amber-400"></i>
                            20 مسألة وسؤالاً تجارياً شائعاً (اضغط للبحث الفوري محلياً وفي قوقل):
                        </span>
                        <button type="button" onclick="clearFiqhSearch()" class="px-3 py-1 bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 rounded-lg text-xs font-black transition">
                            ✕ تفريغ البحث وعرض الأساسي
                        </button>
                    </div>

                    <!-- Category 1: التمويل والتقسيط -->
                    <div class="space-y-1.5">
                        <span class="text-[11px] font-black text-cyan-400 block">💳 التمويل والتقسيط والديون:</span>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('التقسيط')">💳 البيع بالتقسيط وزيادة الأجل</button>
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('غرامات التأخير')">⚠️ غرامات التأخير والشرط الجزائي</button>
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('المرابحة')">🏢 المرابحة للآمر بالشراء</button>
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('التورق')">🔄 التورق للحصول على سيولة</button>
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('الإيجار المنتهي بالتمليك')">🚗 الإيجار المنتهي بالتمليك</button>
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('إنظار المعسر')">⏳ إنظار المعسر والتيسير</button>
                        </div>
                    </div>

                    <!-- Category 2: الأوراق المالية والمدفوعات -->
                    <div class="space-y-1.5">
                        <span class="text-[11px] font-black text-emerald-400 block">📜 الأوراق التجارية والمدفوعات:</span>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('خصم الشيكات')">📜 خصم الشيكات والكمبيالات</button>
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('العربون')">🤝 بيع العربون وحجز البضاعة</button>
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('بيع الدين')">💼 بيع الدين والمقاصة المالية</button>
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('إقالة النادم')">🔄 إقالة النادم وفسخ الصفقة</button>
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('السلم')">📦 بيع السلم ودفع الثمن مقدماً</button>
                        </div>
                    </div>

                    <!-- Category 3: الذهب والعملات والصرافة -->
                    <div class="space-y-1.5">
                        <span class="text-[11px] font-black text-amber-400 block">💎 الذهب والعملات والتحويلات:</span>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('الذهب')">💎 شراء الذهب ببطاقات الصراف ومدى</button>
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('الصرف بالموبايل')">📱 الصرف بتطبيقات بنكك وفوري</button>
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('العِينة')">🚫 حيل الربا وبيوع العِينة</button>
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('العملات المشفرة')">🪙 العملات الرقمية والفوركس</button>
                        </div>
                    </div>

                    <!-- Category 4: المقاولات والسلع والضمانات -->
                    <div class="space-y-1.5">
                        <span class="text-[11px] font-black text-purple-400 block">🏗️ المقاولات والسلع والضمانات وحقوق العمال:</span>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('الاستصناع')">🏗️ عقد الاستصناع وتوريد الحديد</button>
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('السكراب')">🔩 بيع حديد السكراب والخردة</button>
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('بيع ما لا يملك')">🛒 بيع ما لا يملك والوساطة</button>
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('ضمان العيوب')">🛡️ ضمان العيوب والكفالة</button>
                            <button type="button" class="fiqh-chip" onclick="setQuickSearch('حقوق العمال')">👥 أجور العمال ومكافأة الخدمة</button>
                        </div>
                    </div>

                </div>

                <!-- 🗂️ 6 SUB-TABS WITHIN FIQH SUITE (أقسام المنظومة الفقهية) 🗂️ -->
                <div class="flex flex-wrap items-center gap-2 mb-6 border-b border-slate-800 pb-3">
                    <button type="button" class="fiqh-nav-pill active" onclick="switchFiqhSubTab('fatwas', this)">
                        📑 موسوعة الفتاوى والأحكام (80 مسألة كبرى)
                    </button>
                    <button type="button" class="fiqh-nav-pill bg-gradient-to-r from-sky-500/20 to-blue-600/30 text-sky-300 border-sky-400/60 hover:border-sky-300" onclick="switchFiqhSubTab('google', this)">
                        🌐 محرك بحث قوقل للشيوخ الـ 12 (المواقع العالمية)
                    </button>
                    <button type="button" class="fiqh-nav-pill" onclick="switchFiqhSubTab('books', this)">
                        📚 مكتبة أمهات كتب البيوع للشيوخ (26 مؤلفاً وموسوعة)
                    </button>
                    <button type="button" class="fiqh-nav-pill" onclick="switchFiqhSubTab('maxims', this)">
                        ⚖️ القواعد الفقهية الكبرى الـ 5 الحاكمة للتجارة
                    </button>
                    <button type="button" class="fiqh-nav-pill" onclick="switchFiqhSubTab('quran', this)">
                        📖 الآيات القرآنية في الديون والمعاملات (15 آية)
                    </button>
                    <button type="button" class="fiqh-nav-pill" onclick="switchFiqhSubTab('hadiths', this)">
                        📜 أحاديث البيوع والديون والمعاملات المالية (32 حديثاً صحيحة)</button>
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
                    
                    <!-- 💡 Smart Mode Notification Banner 💡 -->
                    <div id="fiqh-view-mode-banner" class="p-3.5 bg-gradient-to-r from-amber-500/15 via-slate-900/90 to-emerald-500/15 border-2 border-amber-500/40 rounded-2xl flex flex-wrap items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">💡</span>
                            <span class="text-slate-200 font-bold" id="fiqh-mode-text">
                                الوضع الحالي: <strong class="text-amber-300 font-black">الفتاوى الأساسية الأكثر شيوعاً (18 فتوى)</strong>. تظهر بقية الموسوعة الشاملة الـ <strong class="text-emerald-300 font-black">80 فتوى</strong> تلقائياً فور كتابة أي كلمة في البحث الذكي 🔍
                            </span>
                        </div>
                        <button type="button" id="toggle-all-fatwas-btn" onclick="toggleAllFatwasMode()" class="px-3.5 py-1.5 bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-400/50 rounded-xl font-black transition">
                            👁️ عرض جميع الـ 80 فتوى دفعة واحدة
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="fatwas-grid-container">

                    <!-- Fatwa Card 1 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="imam_ahmad" data-topic="contracts" data-search="1. الأصل في العقود والشروط الجواز والصحة وخيار الشرط والمجلس الإمام المبجل أحمد بن حنبل (رحمه الله) مذهب الإمام أحمد بن حنبل رحمه الله أن الأصل في العقود والشروط هو الحل والصحة ما لم تخالف نصاً شرعياً أو تنافي مقتضى العقد. وثبوت خيار الشرط وخيار المجلس للمتبايعين للتراضي التام. مسائل الإمام أحمد برواية ابنه عبد الله والمغني لابن قدامة تطبيق النظام: إتاحة خيار الشرط المكتوب وفترة الفحص التجاري المتفق عليها في عقود التوريد والفواتير.">
                        
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
                    <div class="fiqh-master-card" data-featured="true" data-scholar="imam_ahmad" data-topic="installments" data-search="2. اشتراط الأجل في القرض وتأجيل الديون بالتراضي دون زيادة الإمام المبجل أحمد بن حنبل (رحمه الله) ذهب الإمام أحمد في رواية معتمدة إلى صحة ولزوم تأجيل القرض وتحديد موعد محدد لسداده برضا الطرفين، فلا يحق للدائن مطالبة المدين قبل حلول الأجل المتفق عليه. الإنصاف في معرفة الراجح من الخلاف ومسائل الكوسج تطبيق النظام: إثبات تاريخ استحقاق الفاتورة الآجلة في جدول المدفوعات والالتزام بعدم المطالبة قبل حلوله.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    2. اشتراط الأجل في القرض وتأجيل الديون بالتراضي دون زيادة
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام المبجل أحمد بن حنبل (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: الإنصاف في معرفة الراجح من الخلاف ومسائل الكوسج
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز وملزم شرعاً للمقرض والمقترض</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            ذهب الإمام أحمد في رواية معتمدة إلى صحة ولزوم تأجيل القرض وتحديد موعد محدد لسداده برضا الطرفين، فلا يحق للدائن مطالبة المدين قبل حلول الأجل المتفق عليه.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قوله تعالى: ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا إِذَا تَدَايَنتُم بِدَيْنٍ إِلَىٰ أَجَلٍ مُّسَمًّى فَاكْتُبُوهُ﴾.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إثبات تاريخ استحقاق الفاتورة الآجلة في جدول المدفوعات والالتزام بعدم المطالبة قبل حلوله.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 3 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="imam_ahmad" data-topic="pricing" data-search="3. حكم بيع المزايدة العلنية (الحراج) وضوابط المزاد الإمام المبجل أحمد بن حنبل (رحمه الله) يجوز بيع المزايدة بأن يعرض البائع سلعته في المزاد ويطلب الزيادة حتى تستقر على أعلى سعر، بشرط خلو المزاد من النجش (المزايدة الصورية الكاذبة). المغني لابن قدامة (كتاب البيوع - فصل بيع من يزيد) تطبيق النظام: تسجيل عروض الأسعار التنافسية في المناقصات والمزايدات بكل شفافية ونزاهة.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    3. حكم بيع المزايدة العلنية (الحراج) وضوابط المزاد
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام المبجل أحمد بن حنبل (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: المغني لابن قدامة (كتاب البيوع - فصل بيع من يزيد)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز شرعاً بإجماع العلماء ما لم يقع نجش أو تواطؤ</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يجوز بيع المزايدة بأن يعرض البائع سلعته في المزاد ويطلب الزيادة حتى تستقر على أعلى سعر، بشرط خلو المزاد من النجش (المزايدة الصورية الكاذبة).
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    أن النبي ﷺ باع قعباً وحلساً فيمن يزيد فقال: «من يشتري هذا؟ فقال رجل: أنا آخذهما بدرهم، فقال: من يزيد على درهم؟ فأعطاه رجل درهمين فباعهما له».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: تسجيل عروض الأسعار التنافسية في المناقصات والمزايدات بكل شفافية ونزاهة.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 4 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="imam_ahmad" data-topic="defects" data-search="4. استحباب قبول إرجاع البضاعة وإقالة النادم في البيع الإمام المبجل أحمد بن حنبل (رحمه الله) يستحب للتاجر إذا ندم المشتري أو تعثر في سداد البضاعة وأراد إرجاعها أن يقبل إقالته ويفسخ العقد ويرد له ثمنه كاملاً لوجه الله تعالى دون خصم تعسفي. المغني لابن قدامة (فصل الإقالة في البيع) تطبيق النظام: إصدار إشعار دائن (credit note) وإرجاع البضاعة للمخزن واسترداد المبلغ بسلاسة.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    4. استحباب قبول إرجاع البضاعة وإقالة النادم في البيع
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام المبجل أحمد بن حنبل (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: المغني لابن قدامة (فصل الإقالة في البيع)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>مستحب ومندوب شرعاً وفيه أجر عظيم للتاجر</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يستحب للتاجر إذا ندم المشتري أو تعثر في سداد البضاعة وأراد إرجاعها أن يقبل إقالته ويفسخ العقد ويرد له ثمنه كاملاً لوجه الله تعالى دون خصم تعسفي.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قول النبي ﷺ: «مَنْ أَقَالَ مُسْلِمًا أَقَالَهُ اللَّهُ عَثْرَتَهُ يَوْمَ الْقِيَامَةِ» رواه أبو داود.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إصدار إشعار دائن (Credit Note) وإرجاع البضاعة للمخزن واسترداد المبلغ بسلاسة.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 5 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="imam_ahmad" data-topic="contracts" data-search="5. حكم بيع الثمار قبل بدوّ صلاحها وأثر ذلك على العقد الإمام المبجل أحمد بن حنبل (رحمه الله) يحرم بيع المحاصيل والثمار قبل بدو صلاحها (ظهور النضج والحلاوة) إذا اشتُرط بقاؤها على الشجر لاحتمال تعرضها للآفات، وتصح إذا قُطعت فوراً. مسائل الإمام أحمد برواية الأثرم والمغني تطبيق النظام: قيد عقود التوريد الزراعي بعد فحص جودة المحصول وتأكيد جاهزيته للحصاد.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    5. حكم بيع الثمار قبل بدوّ صلاحها وأثر ذلك على العقد
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام المبجل أحمد بن حنبل (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: مسائل الإمام أحمد برواية الأثرم والمغني
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>محرم بيعها قبل بدو الصلاح إلا بشرط القطع في الحال</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يحرم بيع المحاصيل والثمار قبل بدو صلاحها (ظهور النضج والحلاوة) إذا اشتُرط بقاؤها على الشجر لاحتمال تعرضها للآفات، وتصح إذا قُطعت فوراً.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    نهى رسول الله ﷺ عن بيع الثمار حتى يبدو صلاحها.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: قيد عقود التوريد الزراعي بعد فحص جودة المحصول وتأكيد جاهزيته للحصاد.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 6 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="imam_ahmad" data-topic="contracts" data-search="6. تضمين الأجير المشترك (الورش، المصانع، والمقاولين) عند التفريط الإمام المبجل أحمد بن حنبل (رحمه الله) الأجير المشترك (كالحداد، الصباغ، الميكانيكي، أو مقاول البناء) يضمن ما يفسده بيده أو بتفريطه في الصنعة لحماية أموال أصحاب الأعمال. المغني لابن قدامة (باب الإجارة وتضمين الأجراء) تطبيق النظام: إثبات بنود المسؤولية عن التلفيات وجودة التصنيع في أوامر الشغل ومقايسات الورش.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    6. تضمين الأجير المشترك (الورش، المصانع، والمقاولين) عند التفريط
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام المبجل أحمد بن حنبل (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: المغني لابن قدامة (باب الإجارة وتضمين الأجراء)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>يضمن الأجير المشترك ما تلف بفعله أو تفريطه لحفظ أموال الناس</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            الأجير المشترك (كالحداد، الصباغ، الميكانيكي، أو مقاول البناء) يضمن ما يفسده بيده أو بتفريطه في الصنعة لحماية أموال أصحاب الأعمال.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قضاء الخلفاء الراشدين رضي الله عنهم بتضمين الصناع لئلا يتساهلوا في أموال الناس.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إثبات بنود المسؤولية عن التلفيات وجودة التصنيع في أوامر الشغل ومقايسات الورش.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 7 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="muhammad_bin_abdulwahhab" data-topic="pricing" data-search="7. وجوب الصدق وتحريم الحلف الكاذب لترويج البضاعة والرشوة الإمام الشيخ محمد بن عبد الوهاب (رحمه الله) أكد الإمام محمد بن عبد الوهاب رحمه الله على وجوب الصدق والأمانة في المعاملات، وحرَّم الحلف بالأيمان الكاذبة أو المبالغة لترويج البضاعة، وبيَّن أن الرشوة والعمولات الخفية من أكل أموال الناس بالباطل. مجموع مؤلفات الشيخ (الفقه - كتاب البيوع) وكتاب التوحيد تطبيق النظام: الشفافية التامة والنزاهة في عروض الأسعار والتكاليف ومنع أي عمولات خفية غير موثقة.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    7. وجوب الصدق وتحريم الحلف الكاذب لترويج البضاعة والرشوة
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
                                    قول النبي ﷺ: «الحلف مَنْفَقَةٌ للسلعة مَمْحَقَةٌ للبركة» متفق عليه.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: الشفافية التامة والنزاهة في عروض الأسعار والتكاليف ومنع أي عمولات خفية غير موثقة.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 8 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="muhammad_bin_abdulwahhab" data-topic="ownership" data-search="8. تحريم أكل أموال الناس بالباطل ووجوب توثيق الديون والحقوق الإمام الشيخ محمد بن عبد الوهاب (رحمه الله) شدد الشيخ رحمه الله على حرمة المماطلة وجحود الحقوق، وأوجب توثيق المعاملات المالية والعقود بالسندات والشهود حفاظاً على أموال المسلمين ومنعاً للشحناء والبغضاء. مختصر الإنصاف والشرح الكبير (باب الديون والتوثيق) تطبيق النظام: استخراج سندات القبض والصرف الرسمية والفواتير الضريبية الموثقة لكل حركة مالية.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    8. تحريم أكل أموال الناس بالباطل ووجوب توثيق الديون والحقوق
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام الشيخ محمد بن عبد الوهاب (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: مختصر الإنصاف والشرح الكبير (باب الديون والتوثيق)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>واجب شرعاً كتابة وتوثيق الديون وأداء الحقوق</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            شدد الشيخ رحمه الله على حرمة المماطلة وجحود الحقوق، وأوجب توثيق المعاملات المالية والعقود بالسندات والشهود حفاظاً على أموال المسلمين ومنعاً للشحناء والبغضاء.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قوله تعالى: ﴿وَلَا تَأْكُلُوا أَمْوَالَكُم بَيْنَكُم بِالْبَاطِلِ﴾.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: استخراج سندات القبض والصرف الرسمية والفواتير الضريبية الموثقة لكل حركة مالية.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 9 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="muhammad_bin_abdulwahhab" data-topic="pricing" data-search="9. تحريم احتكار السلع وقت الغلاء واستغلال حاجات الناس الإمام الشيخ محمد بن عبد الوهاب (رحمه الله) حرم الشيخ رحمه الله حبس السلع ومواد البناء والأقوات عن السوق بقصد رفع أسعارها واضطرار الناس لشرائها بأثمان باهظة. رسائل وفتاوى الشيخ محمد بن عبد الوهاب في الاقتصاد تطبيق النظام: تدوير المخزون بانتظام والبيع بهوامش ربح عادلة وفق حركة السوق الطبيعية.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    9. تحريم احتكار السلع وقت الغلاء واستغلال حاجات الناس
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام الشيخ محمد بن عبد الوهاب (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: رسائل وفتاوى الشيخ محمد بن عبد الوهاب في الاقتصاد
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-rose-500/25 text-rose-300 border-rose-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✕</span>
                                <span>محرم بإجماع الأمة عند الإضرار بالمسلمين</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            حرم الشيخ رحمه الله حبس السلع ومواد البناء والأقوات عن السوق بقصد رفع أسعارها واضطرار الناس لشرائها بأثمان باهظة.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قول النبي ﷺ: «لَا يَحْتَكِرُ إِلَّا خَاطِئٌ» رواه مسلم.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: تدوير المخزون بانتظام والبيع بهوامش ربح عادلة وفق حركة السوق الطبيعية.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 10 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="muhammad_bin_abdulwahhab" data-topic="riba" data-search="10. تحريم اشتراط الفوائد الإضافية في عقود تأجير المعدات والآلات الإمام الشيخ محمد بن عبد الوهاب (رحمه الله) بيَّن الشيخ أن الأجرة المتفق عليها في عقد تأجير المعدات أو الشاحنات تثبت ديناً في ذمة المستأجر، ولا يجوز فرض أي مبالغ إضافية على التأخر في السداد لأنها تدخل في صريح الربا. مؤلفات الشيخ محمد بن عبد الوهاب (كتاب الإجارة) تطبيق النظام: إلزام المستأجر بدفع الأجرة المتفق عليها وطلب شيك ضمان أو كفالة بنكية دون فرض غرامات ربوية.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    10. تحريم اشتراط الفوائد الإضافية في عقود تأجير المعدات والآلات
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام الشيخ محمد بن عبد الوهاب (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: مؤلفات الشيخ محمد بن عبد الوهاب (كتاب الإجارة)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-rose-500/25 text-rose-300 border-rose-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✕</span>
                                <span>محرم فرض أي فوائد تأخير على أجرة المعدات</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            بيَّن الشيخ أن الأجرة المتفق عليها في عقد تأجير المعدات أو الشاحنات تثبت ديناً في ذمة المستأجر، ولا يجوز فرض أي مبالغ إضافية على التأخر في السداد لأنها تدخل في صريح الربا.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قاعدة: «كل قرض أو دين جر نفعاً مشروطاً فهو ربا».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إلزام المستأجر بدفع الأجرة المتفق عليها وطلب شيك ضمان أو كفالة بنكية دون فرض غرامات ربوية.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 11 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="muhammad_bin_abdulwahhab" data-topic="contracts" data-search="11. وجوب إخراج زكاة عروض التجارة وتقييم المخزون بسعر السوق الحالي الإمام الشيخ محمد بن عبد الوهاب (رحمه الله) أوجب الشيخ تقييم كافة البضائع المعروضة للبيع عند حولان الحول بقيمتها السوقية الحالية (سعر الجملة الحالي) وإخراج ربع العشر (2.5%) للفقراء والمستحقين. مجموع مؤلفات الشيخ (كتاب الزكاة - زكاة عروض التجارة) تطبيق النظام: استخدام حاسبة الزكاة الذكية المدمجة في النظام لتقييم المخزون تلقائياً بسعر السوق.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    11. وجوب إخراج زكاة عروض التجارة وتقييم المخزون بسعر السوق الحالي
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام الشيخ محمد بن عبد الوهاب (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: مجموع مؤلفات الشيخ (كتاب الزكاة - زكاة عروض التجارة)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>واجب شرعاً تقييم البضائع بسعر البيع الحالي وإخراج 2.5%</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            أوجب الشيخ تقييم كافة البضائع المعروضة للبيع عند حولان الحول بقيمتها السوقية الحالية (سعر الجملة الحالي) وإخراج ربع العشر (2.5%) للفقراء والمستحقين.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    إجماع الصحابة وأمر عمر بن الخطاب لحماس بتقييم الجلود وإخراج زكاتها.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: استخدام حاسبة الزكاة الذكية المدمجة في النظام لتقييم المخزون تلقائياً بسعر السوق.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 12 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="muhammad_bin_abdulwahhab" data-topic="pricing" data-search="12. تحريم كتمان التكلفة الحقيقية في بيوع المرابحة والأمانة الإمام الشيخ محمد بن عبد الوهاب (رحمه الله) إذا باع التاجر بطريق المرابحة (أن يخبر المشتري برأس ماله ويطلب ربحاً معلوماً)، وجب عليه الصدق التام في بيان التكلفة الفعلية والخصومات التي نالها من المورد دون زيادة مصطنعة. مختصر الإنصاف (باب بيوع الأمانة) تطبيق النظام: احتساب التكلفة الفعلية (landed cost) بعد حسم الخصومات وإظهار هامش المرابحة بوضوح.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    12. تحريم كتمان التكلفة الحقيقية في بيوع المرابحة والأمانة
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام الشيخ محمد بن عبد الوهاب (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: مختصر الإنصاف (باب بيوع الأمانة)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>واجب الصدق في بيان رأس المال والخصومات المكتسبة</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            إذا باع التاجر بطريق المرابحة (أن يخبر المشتري برأس ماله ويطلب ربحاً معلوماً)، وجب عليه الصدق التام في بيان التكلفة الفعلية والخصومات التي نالها من المورد دون زيادة مصطنعة.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قاعدة: «بيوع الأمانة مبناها على الصدق والنزاهة التامة».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: احتساب التكلفة الفعلية (Landed Cost) بعد حسم الخصومات وإظهار هامش المرابحة بوضوح.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 13 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="al_saadi" data-topic="contracts" data-search="13. اعتبار العرف التجاري المنضبط وقواعد التيسير في المعاملات العلامة الشيخ عبد الرحمن بن ناصر السعدي (رحمه الله) قرر العلامة السعدي رحمه الله أن «العرف مطرد كالمشروط لفظاً»، وأن الأصل في المعاملات التيسير والإباحة، وكل شرط أو تعامل جرى به عرف التجار ولم يخالف الشرع فهو معتبر شرعاً. القواعد والأصول الجامعة (ص 245) والفتاوى السعدية تطبيق النظام: اعتماد الآجال وأساليب السداد والتسليم المتعارف عليها في السوق التجاري.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    13. اعتبار العرف التجاري المنضبط وقواعد التيسير في المعاملات
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
                                    قاعدة فقهية: «العادة محكّمة»، وقوله تعالى: ﴿خُذِ الْعَفْوَ وَأْمُرْ بِالْعُرْفِ﴾.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: اعتماد الآجال وأساليب السداد والتسليم المتعارف عليها في السوق التجاري.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 14 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="al_saadi" data-topic="contracts" data-search="14. أحكام المضاربة والشراكة وتوزيع الأرباح بالنسبة الشائعة لا المقطوعة العلامة الشيخ عبد الرحمن بن ناصر السعدي (رحمه الله) يشترط لصحة عقد الشركة والمضاربة أن يكون نصيب كل شريك نسبة مئوية شائعة من الربح الصافي (مثل: 50% أو 30%)، ولا يجوز اشتراط مبلغ مقطوع (مثل: 50 ألف شهرياً) لأن ذلك يحول العقد إلى قرض ربوي مضمون. الفتاوى السعدية (قسم الشركات والمضاربة ص 280) تطبيق النظام: حساب وتوزيع أرباح الشركاء بنسب الحصص المئوية بعد إقفال الحسابات وقائمة الدخل.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    14. أحكام المضاربة والشراكة وتوزيع الأرباح بالنسبة الشائعة لا المقطوعة
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> العلامة الشيخ عبد الرحمن بن ناصر السعدي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: الفتاوى السعدية (قسم الشركات والمضاربة ص 280)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>واجب توزيع الأرباح بنسبة مئوية شائعة · ويبطل العقد بتحديد مبلغ مقطوع</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يشترط لصحة عقد الشركة والمضاربة أن يكون نصيب كل شريك نسبة مئوية شائعة من الربح الصافي (مثل: 50% أو 30%)، ولا يجوز اشتراط مبلغ مقطوع (مثل: 50 ألف شهرياً) لأن ذلك يحول العقد إلى قرض ربوي مضمون.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    إجماع الفقهاء على أن تحديد مبلغ مقطوع لأحد الشركاء يقطع موجب الشركة في الغنم والغرم.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: حساب وتوزيع أرباح الشركاء بنسب الحصص المئوية بعد إقفال الحسابات وقائمة الدخل.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 15 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_saadi" data-topic="contracts" data-search="15. صحة المعاملات والتعاقدات الإلكترونية عبر الرسائل والإنترنت العلامة الشيخ عبد الرحمن بن ناصر السعدي (رحمه الله) قرر الشيخ السعدي أن العقود والبيوع تنعقد بكل ما يدل عليها من قول أو فعل أو كتابة أو إشارة مفهومة، فيصح الإيجاب والقبول عبر الرسائل الإلكترونية، الواتساب، وأنظمة الـ erp المعتمدة. القواعد والأصول الجامعة (الأصل: العقود تنعقد بما دل عليها عرفاً) تطبيق النظام: إرسال عروض الأسعار وفواتير pdf واعتمادها إلكترونياً من العميل كعقد ملزم.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    15. صحة المعاملات والتعاقدات الإلكترونية عبر الرسائل والإنترنت
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> العلامة الشيخ عبد الرحمن بن ناصر السعدي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: القواعد والأصول الجامعة (الأصل: العقود تنعقد بما دل عليها عرفاً)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز وصحيح شرعاً وينعقد به البيع والشراء</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            قرر الشيخ السعدي أن العقود والبيوع تنعقد بكل ما يدل عليها من قول أو فعل أو كتابة أو إشارة مفهومة، فيصح الإيجاب والقبول عبر الرسائل الإلكترونية، الواتساب، وأنظمة الـ ERP المعتمدة.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قاعدة: «العبرة في العقود بالمقاصد والمعاني لا بالألفاظ والمباني».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إرسال عروض الأسعار وفواتير PDF واعتمادها إلكترونياً من العميل كعقد ملزم.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 16 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_saadi" data-topic="contracts" data-search="16. أحكام الوكالة التجارية الحصرية وحماية الوكيل من المنافسة غير المشروعة العلامة الشيخ عبد الرحمن بن ناصر السعدي (رحمه الله) تجوز عقود الوكالة التجارية الحصرية والتوزيع المعتمد لمنتج معين ما دام الوكيل يوفر السلعة بسعر عادل ولا يستغل حصر الوكالة لرفع الأسعار واحتكار السوق ظلماً. الفتاوى السعدية (ص 305) تطبيق النظام: إدارة عقود التوكيلات وحسابات الموردين الحصريين وضبط نسب الخصم المعتمدة.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    16. أحكام الوكالة التجارية الحصرية وحماية الوكيل من المنافسة غير المشروعة
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> العلامة الشيخ عبد الرحمن بن ناصر السعدي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: الفتاوى السعدية (ص 305)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>جائزة شرعاً إذا لم تؤدِّ إلى احتكار مضِر بالأسواق</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            تجوز عقود الوكالة التجارية الحصرية والتوزيع المعتمد لمنتج معين ما دام الوكيل يوفر السلعة بسعر عادل ولا يستغل حصر الوكالة لرفع الأسعار واحتكار السوق ظلماً.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قوله تعالى: ﴿أَوْفُوا بِالْعُقُودِ﴾.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إدارة عقود التوكيلات وحسابات الموردين الحصريين وضبط نسب الخصم المعتمدة.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 17 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_saadi" data-topic="contracts" data-search="17. حكم مكافأة نهاية الخدمة والتزام صاحب العمل بحقوق العمال العلامة الشيخ عبد الرحمن بن ناصر السعدي (رحمه الله) مكافأة نهاية الخدمة وبدل الإجازات المنصوص عليها في عقود العمل ولوائح العمل الرسمية ملزمة شرعاً لصاحب العمل وتعد جزءاً من الأجر المشروط عرفاً ونظاماً. الفتاوى السعدية (قسم الإجارة وحقوق العمال) تطبيق النظام: احتساب مكافأة نهاية الخدمة والبدلات آلياً عبر حاسبة الرواتب والعمل في النظام.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    17. حكم مكافأة نهاية الخدمة والتزام صاحب العمل بحقوق العمال
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> العلامة الشيخ عبد الرحمن بن ناصر السعدي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: الفتاوى السعدية (قسم الإجارة وحقوق العمال)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>واجبة شرعاً إذا اشترطت في العقد أو قررها النظام</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            مكافأة نهاية الخدمة وبدل الإجازات المنصوص عليها في عقود العمل ولوائح العمل الرسمية ملزمة شرعاً لصاحب العمل وتعد جزءاً من الأجر المشروط عرفاً ونظاماً.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قول النبي ﷺ: «المسلمون على شروطهم».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: احتساب مكافأة نهاية الخدمة والبدلات آلياً عبر حاسبة الرواتب والعمل في النظام.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 18 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_saadi" data-topic="contracts" data-search="18. شروط تسليم البضائع وتحديد مسؤولية الشحن (fob / cif) العلامة الشيخ عبد الرحمن بن ناصر السعدي (رحمه الله) يجوز اشتراط تسليم البضاعة في ميناء الشحن أو في مخازن المشتري، ويكون ضمان التلف ومصاريف النقل والتأمين على الطرف الملتزم به في العقد. القواعد والأصول الجامعة (باب الشروط في العقود) تطبيق النظام: تضمين مصاريف النولون والجمارك والتأمين في حاسبة التكلفة الواصلة للبضائع.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    18. شروط تسليم البضائع وتحديد مسؤولية الشحن (FOB / CIF)
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> العلامة الشيخ عبد الرحمن بن ناصر السعدي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: القواعد والأصول الجامعة (باب الشروط في العقود)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز وصحيح بحسب ما اتفق عليه الطرفان</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يجوز اشتراط تسليم البضاعة في ميناء الشحن أو في مخازن المشتري، ويكون ضمان التلف ومصاريف النقل والتأمين على الطرف الملتزم به في العقد.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قاعدة: «المشروط عرفاً أو شرطاً كالمقطوع به حكماً».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: تضمين مصاريف النولون والجمارك والتأمين في حاسبة التكلفة الواصلة للبضائع.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 19 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="ibn_baz" data-topic="ownership" data-search="19. بيع ما لا يملك المندوب أو التاجر («لا تَبِعْ ما ليس عندك» والوساطة) الإمام عبد العزيز بن باز (رحمه الله) نهى النبي ﷺ حكيم بن حزام فقال: «لا تَبِعْ ما ليس عندك». فلا يجوز للتاجر أو المندوب أن يبرم عقد بيع باتّاً على سلعة معينة ليست في ملكه ولا في حوزته وضمانه، ثم يذهب ليشتريها ويسلمها للزبون. مجموع فتاوى ومقالات متنوعة (19/43) الحل الشرعي: 1) إخبار الزبون بالوعد غير الملزم حتى يحوز التاجر البضاعة. 2) أو الاتفاق على الوكالة بأجر معلوم.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    19. بيع ما لا يملك المندوب أو التاجر («لا تَبِعْ ما ليس عندك» والوساطة)
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
                                    الحل الشرعي: 1) إخبار الزبون بالوعد غير الملزم حتى يحوز التاجر البضاعة. 2) أو الاتفاق على الوكالة بأجر معلوم.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 20 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="ibn_baz" data-topic="checks" data-search="20. تحريم خصم الشيكات والأوراق التجارية الآجلة (بيع الدين بنقد حال أقل) الإمام عبد العزيز بن باز (رحمه الله) أفتى الشيخ ابن باز رحمه الله بتحريم بيع الشيك الآجل بنقد حال أقل من قيمته (مثل بيع شيك بـ 100 ألف جنيه مقابل 90 ألف كاش)، لأن حقيقته مبادلة نقد بنقد مع التفاضل والتأجيل وهو صريح الربا. مجموع فتاوى ابن باز (19/282) البديل الشرعي: الانتظار حتى موعد الاستحقاق، أو توكيل محصل ديون بأجرة مقطوعة معلومة مقابل المتابعة.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    20. تحريم خصم الشيكات والأوراق التجارية الآجلة (بيع الدين بنقد حال أقل)
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
                                    البديل الشرعي: الانتظار حتى موعد الاستحقاق، أو توكيل محصل ديون بأجرة مقطوعة معلومة مقابل المتابعة.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 21 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="ibn_baz" data-topic="contracts" data-search="21. حكم شراء وتداول الأسهم في الشركات المساهمة وتنقيتها الإمام عبد العزيز بن باز (رحمه الله) يجوز المساهمة وشراء أسهم الشركات التي تعمل في أنشطة مباحة (كالصناعة، المقاولات، الزراعة، والتجارة المباحة) ولا تقترض أو تودع بالربا. ويحرم الاستثمار في البنوك والشركات الربوية. مجموع فتاوى ابن باز (19/298) تطبيق النظام: حصر استثمارات الشركة في الأصول والأنشطة التشغيلية الحلال بنسبة 100%.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    21. حكم شراء وتداول الأسهم في الشركات المساهمة وتنقيتها
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام عبد العزيز بن باز (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: مجموع فتاوى ابن باز (19/298)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>جائز في الشركات النقية المباحة النشاط · محرم في الشركات الربوية</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يجوز المساهمة وشراء أسهم الشركات التي تعمل في أنشطة مباحة (كالصناعة، المقاولات، الزراعة، والتجارة المباحة) ولا تقترض أو تودع بالربا. ويحرم الاستثمار في البنوك والشركات الربوية.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قوله تعالى: ﴿وَأَحَلَّ اللَّهُ الْبَيْعَ وَحَرَّمَ الرِّبَا﴾.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: حصر استثمارات الشركة في الأصول والأنشطة التشغيلية الحلال بنسبة 100%.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 22 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="ibn_baz" data-topic="riba" data-search="22. تحريم بطاقات الائتمان (الفيزا/الماستر) المشتملة على فوائد تأخير الإمام عبد العزيز بن باز (رحمه الله) أفتى الشيخ ابن باز بتحريم استخراج بطاقات الائتمان (credit cards) التي تشترط فوائد وغرامات عند التأخر في السداد، لأن التوقيع على هذا الشرط ربا صريح ولو نوى العميل السداد قبل المهلة. مجموع فتاوى ابن باز (19/402) البديل الشرعي: استخدام بطاقات الخصم المباشر (debit cards) أو بطاقات الشحن مسبقة الدفع الخالية من أي شرط ربوي.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    22. تحريم بطاقات الائتمان (الفيزا/الماستر) المشتملة على فوائد تأخير
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام عبد العزيز بن باز (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: مجموع فتاوى ابن باز (19/402)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-rose-500/25 text-rose-300 border-rose-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✕</span>
                                <span>محرم استخدام البطاقة الائتمانية التي تشترط غرامة تأخير ربوية</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            أفتى الشيخ ابن باز بتحريم استخراج بطاقات الائتمان (Credit Cards) التي تشترط فوائد وغرامات عند التأخر في السداد، لأن التوقيع على هذا الشرط ربا صريح ولو نوى العميل السداد قبل المهلة.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    حديث جابر: «لعن رسول الله ﷺ آكل الربا وموكله وكاتبه وشاهديه».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    البديل الشرعي: استخدام بطاقات الخصم المباشر (Debit Cards) أو بطاقات الشحن مسبقة الدفع الخالية من أي شرط ربوي.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 23 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="ibn_baz" data-topic="riba" data-search="23. حكم شراء السلع بالتقسيط ثم بيعها للحصول على السيولة (التورق) الإمام عبد العزيز بن باز (رحمه الله) أجاز الشيخ ابن باز التورق الفردي للحاجة وسداد الديون، بشرط أن يشتري السلعة بالأجل ويحوزها فعلياً، ثم يبيعها في السوق لطرف آخر نقداً دون تواطؤ. مجموع فتاوى ابن باز (19/245) تطبيق النظام: تسجيل فواتير الشراء والبيع لطرف ثالث مستقل تماماً لضبط قيد التورق.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    23. حكم شراء السلع بالتقسيط ثم بيعها للحصول على السيولة (التورق)
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام عبد العزيز بن باز (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: مجموع فتاوى ابن باز (19/245)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>جائز إذا قبض المشتري السلعة وباعها لطرف ثالث غير البائع الأول</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            أجاز الشيخ ابن باز التورق الفردي للحاجة وسداد الديون، بشرط أن يشتري السلعة بالأجل ويحوزها فعلياً، ثم يبيعها في السوق لطرف آخر نقداً دون تواطؤ.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    عموم قوله تعالى: ﴿وَأَحَلَّ اللَّهُ الْبَيْعَ وَحَرَّمَ الرِّبَا﴾.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: تسجيل فواتير الشراء والبيع لطرف ثالث مستقل تماماً لضبط قيد التورق.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 24 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="ibn_baz" data-topic="contracts" data-search="24. حكم شراء البضائع المحجوزة بالجمارك أو المتروكة في الموانئ الإمام عبد العزيز بن باز (رحمه الله) يجوز الدخول في مزادات الجمارك وشراء البضائع المتروكة التي أعلن عنها نظاماً وتخلى عنها أصحابها بعد انقضاء المهلة النظامية القانونية. فتاوى نور على الدرب (كتاب البيوع - المزادات الجمركية) تطبيق النظام: قيد فواتير الشراء من المزادات الرسمية وتحديث المخزون بتكاليف الرسو الفعلية.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    24. حكم شراء البضائع المحجوزة بالجمارك أو المتروكة في الموانئ
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام عبد العزيز بن باز (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: فتاوى نور على الدرب (كتاب البيوع - المزادات الجمركية)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>جائز بعد استيفاء الإجراءات القانونية وإعذار أصحابها</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يجوز الدخول في مزادات الجمارك وشراء البضائع المتروكة التي أعلن عنها نظاماً وتخلى عنها أصحابها بعد انقضاء المهلة النظامية القانونية.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قاعدة: «تصرف ولي الأمر على الرعية منوط بالمصلحة وتفريغ الموانئ».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: قيد فواتير الشراء من المزادات الرسمية وتحديث المخزون بتكاليف الرسو الفعلية.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 25 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="ibn_uthaymeen" data-topic="installments" data-search="25. البيع بالتقسيط مع زيادة السعر عن الثمن النقدي (الكاش) الإمام محمد بن صالح العثيمين (رحمه الله) يجوز بيع السلعة بالتقسيط أو لأجل بثمن أزيد من ثمنها الحاضر (الكاش)، بشرط أن يتم تحديد الثمن الإجمالي وعدد الأقساط ومواعيدها جازماً عند توقيع العقد دون تردد أو خيار بين السعرين. الشرح الممتع على زاد المستقنع (8/360) وفتاوى نور على الدرب تطبيق النظام: يُثبت سعر الفاتورة الآجلة النهائي دون ربط السداد بأي زيادة إضافية بعد العقد.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    25. البيع بالتقسيط مع زيادة السعر عن الثمن النقدي (الكاش)
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
                                    قوله تعالى: ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا إِذَا تَدَايَنتُم بِدَيْنٍ إِلَىٰ أَجَلٍ مُّسَمًّى فَاكْتُبُوهُ﴾.
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
    
                    <!-- Fatwa Card 26 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="ibn_uthaymeen" data-topic="contracts" data-search="26. الشرط الجزائي في عقود التوريد والمقاولات والصيانة الإمام محمد بن صالح العثيمين (رحمه الله) يجوز الاتفاق على الشرط الجزائي في عقود المقاولات والتوريد لجبر الضرر المالي الحقيقي الناتج عن تأخر المقاول أو المورد عن الموعد المحدد، ولا يجوز اشتراطه في الديون والقروض. مجموع فتاوى ورسائل ابن عثيمين (18/142) وهيئة كبار العلماء تطبيق النظام: تقدير غرامة التأخير اليومية في عقود المقاولات بما يعادل الضرر المالي الفعلي فقط دون مبالغة.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    26. الشرط الجزائي في عقود التوريد والمقاولات والصيانة
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
                                    قول النبي ﷺ: «المسلمون على شروطهم إلا شرطاً حرَّم حلالاً أو أحلَّ حراماً».
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
    
                    <!-- Fatwa Card 27 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="ibn_uthaymeen" data-topic="pricing" data-search="27. حكم التخفيضات (الديسكاونت)، كوبونات الخصم، وبطاقات النقاط الترويجية الإمام محمد بن صالح العثيمين (رحمه الله) يجوز للمحل التجاري منح تخفيضات وكوبونات ونقاط مكافآت مجانية للزبائن. ويحرم فقط إذا دفع الزبون رسوم اشتراك لشراء بطاقة الخصومات لأنها تدخل في الغرر والميسر. فتاوى ورسائل ابن عثيمين (18/215) تطبيق النظام: إنشاء نظام كوبونات الخصم ونقاط الولاء المجانية لعملاء المؤسسة.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    27. حكم التخفيضات (الديسكاونت)، كوبونات الخصم، وبطاقات النقاط الترويجية
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محمد بن صالح العثيمين (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: فتاوى ورسائل ابن عثيمين (18/215)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>جائزة شرعاً إذا كانت مجانية بدون رسوم اشتراك مغرر بها</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يجوز للمحل التجاري منح تخفيضات وكوبونات ونقاط مكافآت مجانية للزبائن. ويحرم فقط إذا دفع الزبون رسوم اشتراك لشراء بطاقة الخصومات لأنها تدخل في الغرر والميسر.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    الأصل في المعاملات الحل ما لم تتضمن قماراً أو غرراً.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إنشاء نظام كوبونات الخصم ونقاط الولاء المجانية لعملاء المؤسسة.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 28 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="ibn_uthaymeen" data-topic="contracts" data-search="28. حكم عقد الإيجار المنتهي بالتمليك وضوابطه الشرعية الصحيحة الإمام محمد بن صالح العثيمين (رحمه الله) أجاز الشيخ ابن عثيمين وهيئة كبار العلماء الإيجار المنتهي بالتمليك بشرط إبرام عقد إيجار حقيقي أولاً مع وعد بالهبة أو البيع بسعر رمزي عند نهاية الأقساط بعقد مستقل، مع تحمل المؤجر صيانة وضمان العين الأساسية. مجموع الفتاوى والرسائل وقرار هيئة كبار العلماء تطبيق النظام: إنشاء عقود إيجار تشغيلي للأصول والمعدات مع ملحق وعد بالتمليك عند تمام السداد.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    28. حكم عقد الإيجار المنتهي بالتمليك وضوابطه الشرعية الصحيحة
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محمد بن صالح العثيمين (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: مجموع الفتاوى والرسائل وقرار هيئة كبار العلماء
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>جائز بشرط فصل عقد الإيجار عن عقد الهبة أو البيع النهائي</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            أجاز الشيخ ابن عثيمين وهيئة كبار العلماء الإيجار المنتهي بالتمليك بشرط إبرام عقد إيجار حقيقي أولاً مع وعد بالهبة أو البيع بسعر رمزي عند نهاية الأقساط بعقد مستقل، مع تحمل المؤجر صيانة وضمان العين الأساسية.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    النهي عن صفقتين في صفقة واحدة حتى ينفصل العقدان.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إنشاء عقود إيجار تشغيلي للأصول والمعدات مع ملحق وعد بالتمليك عند تمام السداد.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 29 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="ibn_uthaymeen" data-topic="contracts" data-search="29. الضمان التجاري (warranty) وفترات الكفالة المصنعية وخدمات ما بعد البيع الإمام محمد بن صالح العثيمين (رحمه الله) الضمان المصنعي المجاني التابع للسلعة جائز شرعاً ومستحب لتوثيق الجودة ونفي الغرر. أما بيع ضمان تجاري ممتد منفصل بمقابل مالي بعد انتهاء الضمان الأصلي ففيه غرر ومقامرة. الشرح الممتع (8/240) تطبيق النظام: تفعيل بطاقات الضمان المصنعي وتاريخ انتهاء الكفالة تلقائياً مع كل فاتورة بيع.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    29. الضمان التجاري (Warranty) وفترات الكفالة المصنعية وخدمات ما بعد البيع
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محمد بن صالح العثيمين (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: الشرح الممتع (8/240)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز وصحيح إذا كان الضمان مشمولاً ضمن سعر السلعة الأصلي</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            الضمان المصنعي المجاني التابع للسلعة جائز شرعاً ومستحب لتوثيق الجودة ونفي الغرر. أما بيع ضمان تجاري ممتد منفصل بمقابل مالي بعد انتهاء الضمان الأصلي ففيه غرر ومقامرة.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قاعدة: «يُغتفر في التوابع ما لا يُغتفر في الأوائل».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: تفعيل بطاقات الضمان المصنعي وتاريخ انتهاء الكفالة تلقائياً مع كل فاتورة بيع.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 30 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="ibn_uthaymeen" data-topic="contracts" data-search="30. اشتراط عدم بيع السلعة لطرف منافس (شرط الحصرية الجغرافية) الإمام محمد بن صالح العثيمين (رحمه الله) يجوز للمصنع أو المورد الاتفاق مع الموزع على عدم بيع منتجاته في منطقة جغرافية معينة أو لمنافس مباشر لحماية الاستثمار التسويقي. الشرح الممتع (8/285) تطبيق النظام: تخصيص مناطق التوزيع وحسابات المناديب الحصرية في شاشات المبيعات.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    30. اشتراط عدم بيع السلعة لطرف منافس (شرط الحصرية الجغرافية)
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محمد بن صالح العثيمين (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: الشرح الممتع (8/285)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز وصحيح إذا كان فيه مصلحة معتبرة للتاجر ولا يضر بالسوق العام</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يجوز للمصنع أو المورد الاتفاق مع الموزع على عدم بيع منتجاته في منطقة جغرافية معينة أو لمنافس مباشر لحماية الاستثمار التسويقي.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قول النبي ﷺ: «المسلمون على شروطهم».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: تخصيص مناطق التوزيع وحسابات المناديب الحصرية في شاشات المبيعات.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 31 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="ibn_uthaymeen" data-topic="contracts" data-search="31. حكم شراء السلع عبر المتاجر الإلكترونية (أمازون/علي بابا) قبل وصولها الإمام محمد بن صالح العثيمين (رحمه الله) يصح الشراء عبر المواقع والتطبيقات إذا كانت مواصفات السلعة منضبطة ومعلومة بالصور والقياسات، ويثبت للمشتري حق الإرجاع الكامل إذا وصلت مخالفة. فتاوى المعاملات المعاصرة تطبيق النظام: ربط كتالوج المنتجات الإلكتروني بالمواصفات الفنية والصور الحقيقية للصنف.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    31. حكم شراء السلع عبر المتاجر الإلكترونية (أمازون/علي بابا) قبل وصولها
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محمد بن صالح العثيمين (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: فتاوى المعاملات المعاصرة
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز شرعاً (بيع بالصفة) مع ثبوت خيار الرؤية عند الاستلام</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يصح الشراء عبر المواقع والتطبيقات إذا كانت مواصفات السلعة منضبطة ومعلومة بالصور والقياسات، ويثبت للمشتري حق الإرجاع الكامل إذا وصلت مخالفة.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قياساً على بيع السَّلَم وخيار العيب الثابت بالسنة.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: ربط كتالوج المنتجات الإلكتروني بالمواصفات الفنية والصور الحقيقية للصنف.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 32 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="ibn_uthaymeen" data-topic="defects" data-search="32. حكم التبرؤ من العيوب عند بيع السلعة المستعملة أو السكراب الإمام محمد بن صالح العثيمين (رحمه الله) إذا باع التاجر سيارة مستعملة أو حديد سكراب واشترط البراءة من العيوب، فإنه يبرأ فقط من العيوب الخفية التي لا يعلمها، أما العيوب التي يعلمها فيحرم كتمانها ولا تسقط بالشرط. الشرح الممتع (8/330) تطبيق النظام: تدوين حالة الصنف المستعمل والملاحظات الفنية بالفاتورة صراحة لقطع النزاع.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    32. حكم التبرؤ من العيوب عند بيع السلعة المستعملة أو السكراب
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محمد بن صالح العثيمين (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: الشرح الممتع (8/330)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>يبرأ البائع من العيوب التي لا يعلمها · ولا يبرأ من عيب يعلمه وكتمه</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            إذا باع التاجر سيارة مستعملة أو حديد سكراب واشترط البراءة من العيوب، فإنه يبرأ فقط من العيوب الخفية التي لا يعلمها، أما العيوب التي يعلمها فيحرم كتمانها ولا تسقط بالشرط.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قول النبي ﷺ: «لا يحل لمسلم باع من أخيه بيعاً فيه عيب إلا بينه له».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: تدوين حالة الصنف المستعمل والملاحظات الفنية بالفاتورة صراحة لقطع النزاع.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 33 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="ibn_taymiyyah" data-topic="installments" data-search="33. تحريم فرض غرامات التأخير والفوائد على المدين المماطل في السداد شيخ الإسلام ابن تيمية (رحمه الله) الزيادة المشروطة على الدين مقابل تأخير السداد هي عين ربا الجاهلية («إما أن تقضي وإما أن تُربي»). ولا يجوز اشتراط غرامة مالية تدفع للبائع عند التأخر في سداد الأقساط أو الفواتير. مجموع الفتاوى (29/425) وقرارات هيئة كبار العلماء الحل الشرعي لحفظ الحقوق: طلب كفيل غارم، شيك ضمان، رهن أصول، أو رفع أمر المماطل للقضاء لإلزامه بالسداد وتعزيره.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    33. تحريم فرض غرامات التأخير والفوائد على المدين المماطل في السداد
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
                                    قوله تعالى: ﴿وَإِن كَانَ ذُو عُسْرَةٍ فَنَظِرَةٌ إِلَىٰ مَيْسَرَةٍ﴾.
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
    
                    <!-- Fatwa Card 34 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="ibn_taymiyyah" data-topic="araboon" data-search="34. بيع العربون واسترداده في حال فسخ الصفقة من قِبل المشتري شيخ الإسلام ابن تيمية (رحمه الله) اختيار شيخ الإسلام ابن تيمية والإمام أحمد وابن القيم وابن باز والعثيمين: جواز بيع العربون (دفع مبلغ مقدماً فإن أتم المشتري البيع حُسب من الثمن، وإن نكل وفسخ فللبائع الاحتفاظ به) لثبوته عن عمر بن الخطاب رضي الله عنه. مجموع الفتاوى (29/536) واختيار الإمام أحمد تطبيق النظام: إثبات سند قبض العربون، وخصمه من إجمالي الفاتورة، أو قيده كتعويض حال تراجع العميل.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    34. بيع العربون واسترداده في حال فسخ الصفقة من قِبل المشتري
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
    
                    <!-- Fatwa Card 35 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="ibn_taymiyyah" data-topic="pricing" data-search="35. التسعير وتحديد نسبة الربح وحرية الأسعار والتراضي شيخ الإسلام ابن تيمية (رحمه الله) لم يحدد الشرع نسبة معينة للربح (كالعُشر أو الثلث)، بل الأصل هو التراضي بين البائع والمشتري. ويجوز لولي الأمر التسعير الجبري العادل فقط إذا احتكر التجار أقوات الناس ورفعوا الأسعار ظلماً. الحسبة في الإسلام لمجموع الفتاوى (28/75) تطبيق النظام: حرية تحديد هامش الربح للصنف مع الالتزام بالصدق وعدم الغش أو الاحتكار.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    35. التسعير وتحديد نسبة الربح وحرية الأسعار والتراضي
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
                                    قول النبي ﷺ لما قيل له سعِّر لنا: «إن الله هو الخالق القابض الباسط الرزاق المسعِّر...».
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
    
                    <!-- Fatwa Card 36 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="ibn_taymiyyah" data-topic="checks" data-search="36. بيع الدين لغير من هو عليه وضوابط الحوالة والمقاصة التجارية شيخ الإسلام ابن تيمية (رحمه الله) قرر شيخ الإسلام جواز حوالة الدين ونقل الالتزام المالي إلى طرف ثالث مليء بالمثل (المقاصة وتسوية الحسابات بين التجار)، بشرط عدم بيع الدين المؤجل بنقد حال أقل تفادياً للربا. مجموع الفتاوى (29/460) تطبيق النظام: إجراء قيود المقاصة المحاسبية التلقائية لتسوية حسابات العملاء والموردين المشتركة.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    36. بيع الدين لغير من هو عليه وضوابط الحوالة والمقاصة التجارية
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> شيخ الإسلام ابن تيمية (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: مجموع الفتاوى (29/460)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>جائزة الحوالة والمقاصة بالتماثل · محرم بيعه بنقد أقل</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            قرر شيخ الإسلام جواز حوالة الدين ونقل الالتزام المالي إلى طرف ثالث مليء بالمثل (المقاصة وتسوية الحسابات بين التجار)، بشرط عدم بيع الدين المؤجل بنقد حال أقل تفادياً للربا.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قول النبي ﷺ: «وَإِذَا أُتْبِعَ أَحَدُكُمْ عَلَى مَلِيءٍ فَلْيَتَّبِعْ».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إجراء قيود المقاصة المحاسبية التلقائية لتسوية حسابات العملاء والموردين المشتركة.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 37 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="ibn_taymiyyah" data-topic="contracts" data-search="37. حكم تعليق العقود والبيوع على شروط مستقبلية (العقود المعلقة) شيخ الإسلام ابن تيمية (رحمه الله) اختار شيخ الإسلام صحة تعليق البيع أو الإجارة على شرط مستقبلي معتبر (مثل: إن وصلت السفينة أو رسى العطاء فقد بعتك بكذا) لعدم وجود مانع شرعي. مجموع الفتاوى (29/340) تطبيق النظام: إبرام مذكرات التفاهم وعقود التوريد المشروطة بفتح الاعتماد المستندي.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    37. حكم تعليق العقود والبيوع على شروط مستقبلية (العقود المعلقة)
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> شيخ الإسلام ابن تيمية (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: مجموع الفتاوى (29/340)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز وصحيح شرعاً لحاجة الناس وتصحيح المعاملات</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            اختار شيخ الإسلام صحة تعليق البيع أو الإجارة على شرط مستقبلي معتبر (مثل: إن وصلت السفينة أو رسى العطاء فقد بعتك بكذا) لعدم وجود مانع شرعي.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    الأصل في العقود والشروط الصحة والإباحة.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إبرام مذكرات التفاهم وعقود التوريد المشروطة بفتح الاعتماد المستندي.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 38 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="ibn_taymiyyah" data-topic="contracts" data-search="38. حكم بيع المغيبات في الأرض كالمناجم والمعادن والرخام شيخ الإسلام ابن تيمية (رحمه الله) يصح بيع ما في باطن الأرض من محاصيل ومعادن ورخام بالاعتماد على الفحص الجيولوجي والظاهر دون اشتراط قلعها ورؤية كل جزء منها. مجموع الفتاوى (29/210) تطبيق النظام: اعتماد تقارير فحص الجودة الجيولوجية ومواصفات المحاجر في عقود التوريد.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    38. حكم بيع المغيبات في الأرض كالمناجم والمعادن والرخام
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> شيخ الإسلام ابن تيمية (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: مجموع الفتاوى (29/210)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز بيعها اعتماداً على الاختبار والخبرة والظاهر المعتاد</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يصح بيع ما في باطن الأرض من محاصيل ومعادن ورخام بالاعتماد على الفحص الجيولوجي والظاهر دون اشتراط قلعها ورؤية كل جزء منها.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قاعدة: «الجهالة اليسيرة التي يشق الاحتراز منها مغتفرة في الشرع».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: اعتماد تقارير فحص الجودة الجيولوجية ومواصفات المحاجر في عقود التوريد.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 39 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="ibn_al_qayyim" data-topic="riba" data-search="39. إبطال حيل الربا، بيع العِينة، وضوابط التورق المنضبط الإمام شمس الدين ابن قيم الجوزية (رحمه الله) بسط الإمام ابن القيم رحمه الله القول في إبطال الحيل التي يتوصل بها إلى استحلال الربا باسم البيع (كبيع العِينة: أن يبيع التاجر سلعة بالأجل ثم يشتريها نقداً بأقل). وأكد أن العبرة في العقود بالمقاصد والمعاني لا بالألفاظ والمباني. إعلام الموقعين عن رب العالمين (3/182) وإغاثة اللهفان تطبيق النظام: يشترط في التورق أن يقبض المشتري السلعة فعلياً وتدخل في حيازته وضمانه، ثم يبيعها لطرف ثالث مستقل.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    39. إبطال حيل الربا، بيع العِينة، وضوابط التورق المنضبط
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
                                    حديث النبي ﷺ: «إذا تبايعتم بالعِينة، وأخذتم أذناب البقر... سلط الله عليكم ذلاً لا ينزعه حتى ترجعوا إلى دينكم».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: يشترط في التورق أن يقبض المشتري السلعة فعلياً وتدخل في حيازته وضمانه، ثم يبيعها لطرف ثالث مستقل.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 40 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="ibn_al_qayyim" data-topic="contracts" data-search="40. جواز عقد الاستصناع والمقاولات والصناعة بالطلب الإمام شمس الدين ابن قيم الجوزية (رحمه الله) قرر الإمام ابن القيم وشيخ الإسلام ابن تيمية صحة عقد الاستصناع (الاتفاق مع الحداد أو المصنع على تصنيع أبواب، شبابيك، أو قطع غيار بمواصفات محددة ومواد من عند الصانع) وأنه عقد مستقل جائز شرعاً. إعلام الموقعين عن رب العالمين (4/12) ومجموع الفتاوى تطبيق النظام: فتح أمر تشغيل / تصنيع (work order) وتحديد المواد وتكلفة المصنعية وتاريخ التسليم بدقة.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    40. جواز عقد الاستصناع والمقاولات والصناعة بالطلب
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
                                    أن النبي ﷺ استصنع خاتماً واستصنع المنبر من خشب.
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
    
                    <!-- Fatwa Card 41 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="ibn_al_qayyim" data-topic="contracts" data-search="41. ضوابط السمسرة والوكالة بالعمولة وأجرة الدلالة والمطابقة الإمام شمس الدين ابن قيم الجوزية (رحمه الله) تصح السمسرة والوساطة التجارية (الدلالة) مقابل نسبة مئوية معلومة من قيمة الصفقة أو مبلغ مقطوع، ويستحق السمسار الأجرة بمجرد إتمام العقد وتوافق الطرفين دون غش أو تدليس. إعلام الموقعين (4/24) ومسائل السمسرة لابن عباس تطبيق النظام: تسجيل عمولات المناديب والوسطاء آلياً في فواتير المبيعات بحسابات دقيقة.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    41. ضوابط السمسرة والوكالة بالعمولة وأجرة الدلالة والمطابقة
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام شمس الدين ابن قيم الجوزية (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: إعلام الموقعين (4/24) ومسائل السمسرة لابن عباس
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائزة وحلال بأجر مقطوع أو نسبة معلومة من الصفقة</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            تصح السمسرة والوساطة التجارية (الدلالة) مقابل نسبة مئوية معلومة من قيمة الصفقة أو مبلغ مقطوع، ويستحق السمسار الأجرة بمجرد إتمام العقد وتوافق الطرفين دون غش أو تدليس.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قال ابن عباس: «لا بأس أن يقول: بع هذا الثوب فما زاد على كذا وكذا فهو لك».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: تسجيل عمولات المناديب والوسطاء آلياً في فواتير المبيعات بحسابات دقيقة.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 42 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="ibn_al_qayyim" data-topic="contracts" data-search="42. وضع الجوائح وتضمين خسائر تلف المحاصيل والبضائع قبل التسليم الإمام شمس الدين ابن قيم الجوزية (رحمه الله) إذا أصابت البضاعة أو المحاصيل جائحة سماوية (كغرق سفينة أو سيول أو تلف عام خارج عن إرادة المشتري قبل تمكنه من قبضها)، وجب على البائع وضع الجائحة وإسقاط الثمن ولا يجوز أخذ مال المشتري بغير حق. زاد المعاد في هدي خير العباد (5/802) تطبيق النظام: تسجيل مذكرات الخصم والتسوية الدائنة والتأمينية عند حدوث تلفيات الشحن القهرية.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    42. وضع الجوائح وتضمين خسائر تلف المحاصيل والبضائع قبل التسليم
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام شمس الدين ابن قيم الجوزية (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: زاد المعاد في هدي خير العباد (5/802)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>واجب شرعاً وضع الجائحة وإسقاط الثمن عن المشتري</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            إذا أصابت البضاعة أو المحاصيل جائحة سماوية (كغرق سفينة أو سيول أو تلف عام خارج عن إرادة المشتري قبل تمكنه من قبضها)، وجب على البائع وضع الجائحة وإسقاط الثمن ولا يجوز أخذ مال المشتري بغير حق.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    أمر رسول الله ﷺ بوضع الجوائح وقال: «بِمَ تَأْخُذُ مَالَ أَخِيكَ بِغَيْرِ حَقٍّ؟» رواه مسلم.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: تسجيل مذكرات الخصم والتسوية الدائنة والتأمينية عند حدوث تلفيات الشحن القهرية.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 43 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="ibn_al_qayyim" data-topic="ownership" data-search="43. جواز بيع الاسم التجاري (brand) والعلامات التجارية وبراءات الاختراع الإمام شمس الدين ابن قيم الجوزية (رحمه الله) الاسم التجاري، والعلامة التجارية، وبراءات الاختراع حقوق معنوية ذات قيمة مالية معتبرة عرفاً، فيجوز بيعها والتنازل عنها أو الترخيص باستعمالها (franchise) بعوض مالي معلوم. إعلام الموقعين ومجمع الفقه الإسلامي الدولي تطبيق النظام: تقييم الأصول غير الملموسة (intangibles / goodwill) في المركز المالي للمؤسسة.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    43. جواز بيع الاسم التجاري (Brand) والعلامات التجارية وبراءات الاختراع
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام شمس الدين ابن قيم الجوزية (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: إعلام الموقعين ومجمع الفقه الإسلامي الدولي
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز وحق مالي مشروع للمالك يجوز التنازل عنه بعوض</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            الاسم التجاري، والعلامة التجارية، وبراءات الاختراع حقوق معنوية ذات قيمة مالية معتبرة عرفاً، فيجوز بيعها والتنازل عنها أو الترخيص باستعمالها (Franchise) بعوض مالي معلوم.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قاعدة: «الحقوق المالية المعنوية معتبرة شرعاً ويجوز الاعتياض عنها».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: تقييم الأصول غير الملموسة (Intangibles / Goodwill) في المركز المالي للمؤسسة.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 44 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="ibn_al_qayyim" data-topic="contracts" data-search="44. تضمين الغاصب والمعتدي تكاليف تعطيل المعدات والآلات التجارية الإمام شمس الدين ابن قيم الجوزية (رحمه الله) من غصب شاحنة أو معدة بناء أو حبسها بغير حق، لزمه ردها ودفع أجرة مثلها عن مدة التعطيل لجبر الضرر المالي الواقع على صاحب العمل. زاد المعاد في هدي خير العباد (5/815) تطبيق النظام: إثبات المطالبات القضائية والتعويضات المستحقة في حساب المدينين الآخرين.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    44. تضمين الغاصب والمعتدي تكاليف تعطيل المعدات والآلات التجارية
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام شمس الدين ابن قيم الجوزية (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: زاد المعاد في هدي خير العباد (5/815)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>واجب تضمين الغاصب أجرة المثل عن فترة التعطيل</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            من غصب شاحنة أو معدة بناء أو حبسها بغير حق، لزمه ردها ودفع أجرة مثلها عن مدة التعطيل لجبر الضرر المالي الواقع على صاحب العمل.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قول النبي ﷺ: «على اليد ما أخذت حتى تؤديه».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إثبات المطالبات القضائية والتعويضات المستحقة في حساب المدينين الآخرين.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 45 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="al_nawawi" data-topic="defects" data-search="45. تحريم بيع الغرر والجهالة والمخاطرة في عقود التجارة الإمام محيي الدين يحيى بن شرف النووي (رحمه الله) قال الإمام النووي رحمه الله: «النهي عن بيع الغرر أصل عظيم من أصول كتاب البيوع، يدخل فيه مسائل كثيرة غير منحصرة». فيحرم بيع ما فيه جهالة فاحشة في ذات المبيع، أو مقداره، أو سُمكه، أو صفته، أو ثمنه، أو أجله. المجموع شرح المهذب (9/258) وشرح صحيح مسلم (10/156) تطبيق النظام: التوصيف التام والدقيق للصنف بالاسم والعيار والوزن والعدد والسعر الإفرادي بالفاتورة لقطع الجهالة.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    45. تحريم بيع الغرر والجهالة والمخاطرة في عقود التجارة
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
                                    تطبيق النظام: التوصيف التام والدقيق للصنف بالاسم والعيار والوزن والعدد والسعر الإفرادي بالفاتورة لقطع الجهالة.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 46 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="al_nawawi" data-topic="contracts" data-search="46. صحة البيع بالمعاطاة والبيع الآلي ونقاط البيع السريعة pos الإمام محيي الدين يحيى بن شرف النووي (رحمه الله) اختار الإمام النووي رحمه الله صحة البيع بالمعاطاة (أخذ السلعة ودفع ثمنها في نقاط البيع وماكينات البيع الذاتي والمواقع الإلكترونية دون اشتراط التلفظ بصيغة بعت واشتريت) في كل قليل وكثير مما يتعارف الناس على بيعه بالمعاطاة. المجموع شرح المهذب (9/162) وروضة الطالبين تطبيق النظام: سرعة إصدار فواتير نقاط البيع (pos) والكاشير بنقرة واحدة عبر الباركود.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    46. صحة البيع بالمعاطاة والبيع الآلي ونقاط البيع السريعة POS
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محيي الدين يحيى بن شرف النووي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: المجموع شرح المهذب (9/162) وروضة الطالبين
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز وصحيح شرعاً في كل ما تعارف عليه الناس</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            اختار الإمام النووي رحمه الله صحة البيع بالمعاطاة (أخذ السلعة ودفع ثمنها في نقاط البيع وماكينات البيع الذاتي والمواقع الإلكترونية دون اشتراط التلفظ بصيغة بعت واشتريت) في كل قليل وكثير مما يتعارف الناس على بيعه بالمعاطاة.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    لأن الأصل في التراضي هو ما دل عليه العرف ولم يثبت في الشرع لفظ محدد للإيجاب والقبول.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: سرعة إصدار فواتير نقاط البيع (POS) والكاشير بنقرة واحدة عبر الباركود.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 47 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_nawawi" data-topic="defects" data-search="47. خيار الرؤية وضمان سلامة المبيع من التلف قبل تسليمه للزبون الإمام محيي الدين يحيى بن شرف النووي (رحمه الله) إذا اشترى الزبون بضاعة موصوفة غائبة، فله الحق الكامل في معاينتها عند وصولها فإن وجدها مطابقة لزم البيع، وإن وجدها مخالفة للمواصفات أو تالفة فله خيار الرد وفسخ العقد واسترداد أمواله كاملة. المجموع شرح المهذب (9/340) تطبيق النظام: إصدار إذن تسليم مخزني (delivery note) يوقعه العميل بعد المعاينة وتأكيد السلامة.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    47. خيار الرؤية وضمان سلامة المبيع من التلف قبل تسليمه للزبون
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محيي الدين يحيى بن شرف النووي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: المجموع شرح المهذب (9/340)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>ثبوت خيار الرؤية للمشتري وتضمين البائع للبضاعة حتى تسليمها</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            إذا اشترى الزبون بضاعة موصوفة غائبة، فله الحق الكامل في معاينتها عند وصولها فإن وجدها مطابقة لزم البيع، وإن وجدها مخالفة للمواصفات أو تالفة فله خيار الرد وفسخ العقد واسترداد أمواله كاملة.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قاعدة: «الضمان على البائع حتى يستوفي المشتري قبض السلعة».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إصدار إذن تسليم مخزني (Delivery Note) يوقعه العميل بعد المعاينة وتأكيد السلامة.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 48 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_nawawi" data-topic="contracts" data-search="48. جواز بيع الجزاف (ما يشق عده ووزنه كأكوام البضائع) الإمام محيي الدين يحيى بن شرف النووي (رحمه الله) يصح بيع الجزاف (بيع كومة رمل، أو كمية خردة، أو حديد سكراب ككتلة واحدة بسعر مقطوع) إذا كانت البضاعة مرئية ومشهودة للطرفين دون الحاجة لوزن كل حبة بمفردها. المجموع شرح المهذب (9/275) تطبيق النظام: إمكانية إدخال وحدات البيع المقطوعة (lot / lump sum) في الفواتير.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    48. جواز بيع الجزاف (ما يشق عده ووزنه كأكوام البضائع)
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محيي الدين يحيى بن شرف النووي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: المجموع شرح المهذب (9/275)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز شرعاً إذا رآه المتبايعان وتراضيا على سعره الإجمالي</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يصح بيع الجزاف (بيع كومة رمل، أو كمية خردة، أو حديد سكراب ككتلة واحدة بسعر مقطوع) إذا كانت البضاعة مرئية ومشهودة للطرفين دون الحاجة لوزن كل حبة بمفردها.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    أن الصحابة كانوا يشترون الطعام جزافاً في عهد النبي ﷺ فيجيزه لهم.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إمكانية إدخال وحدات البيع المقطوعة (Lot / Lump Sum) في الفواتير.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 49 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_nawawi" data-topic="contracts" data-search="49. أحكام الوديعة المصرفية وحفظ الأمانات التجارية وتضمين الوديع الإمام محيي الدين يحيى بن شرف النووي (رحمه الله) الأموال والبضائع المودعة في مخازن الغير أو الخزائن أمانة في يد الوديع، فلا يضمنها إذا تلفت بقوة قاهرة إلا إذا قصر في حراستها أو استعملها دون إذن صاحبها. المجموع شرح المهذب (كتاب الوديعة 15/210) تطبيق النظام: إدارة بضائع الأمانة (consignment stock) في حسابات مستقلة عن بضائع الشركة.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    49. أحكام الوديعة المصرفية وحفظ الأمانات التجارية وتضمين الوديع
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محيي الدين يحيى بن شرف النووي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: المجموع شرح المهذب (كتاب الوديعة 15/210)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>الوديعة أمانة لا تضمن إلا بالتعدي أو التفريط</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            الأموال والبضائع المودعة في مخازن الغير أو الخزائن أمانة في يد الوديع، فلا يضمنها إذا تلفت بقوة قاهرة إلا إذا قصر في حراستها أو استعملها دون إذن صاحبها.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قول النبي ﷺ: «من أُودع وديعة فلا ضمان عليه» رواه ابن ماجه.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إدارة بضائع الأمانة (Consignment Stock) في حسابات مستقلة عن بضائع الشركة.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 50 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_nawawi" data-topic="contracts" data-search="50. مشروعية عقد الجعالة (تخصيص مكافأة مالية لإنجاز مهمة محددة) الإمام محيي الدين يحيى بن شرف النووي (رحمه الله) يصح عقد الجعالة (كقول التاجر: من جلب لي عقداً تجارياً أو استعاد شحنة مفقودة فله مكافأة 500 دولار)، ويستحق العامل المكافأة فور تسليم العمل المطلوب كاملاً. المجموع شرح المهذب (كتاب الجعالة 15/120) تطبيق النظام: رصد وصرف مكافآت وحوافز الإنجاز لفرق المبيعات والتحصيل آلياً.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    50. مشروعية عقد الجعالة (تخصيص مكافأة مالية لإنجاز مهمة محددة)
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محيي الدين يحيى بن شرف النووي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: المجموع شرح المهذب (كتاب الجعالة 15/120)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز وصحيح شرعاً ويثبت الجعل بتمام العمل</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يصح عقد الجعالة (كقول التاجر: من جلب لي عقداً تجارياً أو استعاد شحنة مفقودة فله مكافأة 500 دولار)، ويستحق العامل المكافأة فور تسليم العمل المطلوب كاملاً.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قوله تعالى: ﴿وَلِمَن جَاءَ بِهِ حِمْلُ بَعِيرٍ وَأَنَا بِهِ زَعِيمٌ﴾.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: رصد وصرف مكافآت وحوافز الإنجاز لفرق المبيعات والتحصيل آلياً.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 51 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="al_dhahabi" data-topic="pricing" data-search="51. التطفيف في الكيل والميزان والغش والاحتكار في التجارة الإمام الحافظ شمس الدين الذهبي (رحمه الله) عدّ الإمام الذهبي رحمه الله التطفيف في الكيل والميزان (كنقص وزن حديد التسليح أو الخرسانة أو البضائع المعبأة عن المقدار المذكور) من كبائر الذنوب التي توعد الله أصحابها بالويل والهلاك في الآخرة. كتاب الكبائر (الكبيرة 29: التطفيف في الكيل والوزن، والكبيرة 30) تطبيق النظام: استخدام حاسبات الأوزان والمعايرة الرقمية الدقيقة وتطابق وزن الفاتورة مع وزن الميزان البسكول الفعلي.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    51. التطفيف في الكيل والميزان والغش والاحتكار في التجارة
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
                                    تطبيق النظام: استخدام حاسبات الأوزان والمعايرة الرقمية الدقيقة وتطابق وزن الفاتورة مع وزن الميزان البسكول الفعلي.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 52 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="al_dhahabi" data-topic="defects" data-search="52. تحريم تزوير العلامات التجارية وتغيير بلد المنشأ وتدليس البضائع الإمام الحافظ شمس الدين الذهبي (رحمه الله) بيَّن الإمام الذهبي أن التدليس والغش في بلد المنشأ (كإيهام المشتري بأن البضاعة أصلية أو مستوردة من بلد معين وهي مقلدة) يجمع بين الكذب وأكل الحرام وخيانة الأمانة. كتاب الكبائر (باب الغش والخيانة وأكل أموال الناس بالباطل) تطبيق النظام: ربط الباركود ببلد المنشأ الحقيقي والشركة المصنعة دون أي تعديل أو تضليل.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    52. تحريم تزوير العلامات التجارية وتغيير بلد المنشأ وتدليس البضائع
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام الحافظ شمس الدين الذهبي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: كتاب الكبائر (باب الغش والخيانة وأكل أموال الناس بالباطل)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-rose-500/25 text-rose-300 border-rose-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✕</span>
                                <span>محرم تحريماً قاطعاً ومن كبائر الغش</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            بيَّن الإمام الذهبي أن التدليس والغش في بلد المنشأ (كإيهام المشتري بأن البضاعة أصلية أو مستوردة من بلد معين وهي مقلدة) يجمع بين الكذب وأكل الحرام وخيانة الأمانة.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قول النبي ﷺ: «مَنْ غَشَّ فَلَيْسَ مِنِّي» رواه مسلم.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: ربط الباركود ببلد المنشأ الحقيقي والشركة المصنعة دون أي تعديل أو تضليل.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 53 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_dhahabi" data-topic="pricing" data-search="53. تحريم التواطؤ والاتفاق السري بين التجار لرفع الأسعار في المناقصات الإمام الحافظ شمس الدين الذهبي (رحمه الله) حذر الذهبي من اتفاق الموردين السري فيما بينهم لتقاسم المناقصات وتقديم عروض أسعار متفق عليها مسبقاً لرفع السعر على المشتري، وبيّن أن هذا غدر وخيانة محرمة. كتاب الكبائر (باب الخيانة والغدر في المعاملات) تطبيق النظام: اعتماد مبدأ المنافسة الحرة والشفافية التامة في تسعير العطاءات.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    53. تحريم التواطؤ والاتفاق السري بين التجار لرفع الأسعار في المناقصات
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام الحافظ شمس الدين الذهبي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: كتاب الكبائر (باب الخيانة والغدر في المعاملات)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-rose-500/25 text-rose-300 border-rose-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✕</span>
                                <span>محرم وخيانة للأمانة وأكل للمال العام بالباطل</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            حذر الذهبي من اتفاق الموردين السري فيما بينهم لتقاسم المناقصات وتقديم عروض أسعار متفق عليها مسبقاً لرفع السعر على المشتري، وبيّن أن هذا غدر وخيانة محرمة.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قول النبي ﷺ: «لكل غادر لواء يوم القيامة يُرفع له بقدر غدرته».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: اعتماد مبدأ المنافسة الحرة والشفافية التامة في تسعير العطاءات.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 54 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_dhahabi" data-topic="ownership" data-search="54. تحريم أكل أموال اليتامى والقصَّر في تصفية الشركات والتركات الإمام الحافظ شمس الدين الذهبي (رحمه الله) شدد الإمام الذهبي على حرمة بخس حقوق الورثة القاصرين عند تصفية الشركات أو بيع عقارات التركات بأقل من قيمتها السوقية العادلة. كتاب الكبائر (الكبيرة السادسة: أكل مال اليتيم) تطبيق النظام: إجراء التقييم المحاسبي الدقيق للأصول وفق القيمة العادلة عند تصفية الحصص.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    54. تحريم أكل أموال اليتامى والقصَّر في تصفية الشركات والتركات
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام الحافظ شمس الدين الذهبي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: كتاب الكبائر (الكبيرة السادسة: أكل مال اليتيم)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-rose-500/25 text-rose-300 border-rose-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✕</span>
                                <span>من الموبقات السبع المحرمة بإجماع الأمة</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            شدد الإمام الذهبي على حرمة بخس حقوق الورثة القاصرين عند تصفية الشركات أو بيع عقارات التركات بأقل من قيمتها السوقية العادلة.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قوله تعالى: ﴿إِنَّ الَّذِينَ يَأْكُلُونَ أَمْوَالَ الْيَتَامَىٰ ظُلْمًا إِنَّمَا يَأْكُلُونَ فِي بُطُونِهِمْ نَارًا﴾.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إجراء التقييم المحاسبي الدقيق للأصول وفق القيمة العادلة عند تصفية الحصص.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 55 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_dhahabi" data-topic="pricing" data-search="55. تحريم ترويج الشائعات الكاذبة للتأثير على الأسعار والأسهم الإمام الحافظ شمس الدين الذهبي (رحمه الله) يحرم نشر الأخبار الكاذبة أو تضخيم الشائعات في أسواق الذهب أو العملات أو مواد البناء للتأثير على رغبات المشترين ورفع الأسعار أو خفضها بغير حق. كتاب الكبائر (باب الكذب والبهتان في التجارة) تطبيق النظام: الاعتماد الحصري على البيانات والمؤشرات السوقية الرسمية المعتمدة.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    55. تحريم ترويج الشائعات الكاذبة للتأثير على الأسعار والأسهم
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام الحافظ شمس الدين الذهبي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: كتاب الكبائر (باب الكذب والبهتان في التجارة)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-rose-500/25 text-rose-300 border-rose-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✕</span>
                                <span>محرم لما فيه من التغرير وإفساد الأسواق</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يحرم نشر الأخبار الكاذبة أو تضخيم الشائعات في أسواق الذهب أو العملات أو مواد البناء للتأثير على رغبات المشترين ورفع الأسعار أو خفضها بغير حق.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قول النبي ﷺ: «لا تناجشوا» وقوله: «كفى بالمرء كذباً أن يحدث بكل ما سمع».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: الاعتماد الحصري على البيانات والمؤشرات السوقية الرسمية المعتمدة.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 56 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_dhahabi" data-topic="contracts" data-search="56. وجوب الوفاء بالعهود والشروط مع غير المسلمين في التجارة الدولية الإمام الحافظ شمس الدين الذهبي (رحمه الله) أكد الذهبي أن الوفاء بالعقود والاعتمادات المستندية والشروط واجب شرعاً مع كافة المتعاملين والشركات الدولية دون تمييز أو غدر. كتاب الكبائر (باب الوفاء بالعهود) تطبيق النظام: الالتزام التام بكافة بنود عقود الاستيراد والاعتمادات المستندية البنكية.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    56. وجوب الوفاء بالعهود والشروط مع غير المسلمين في التجارة الدولية
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام الحافظ شمس الدين الذهبي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: كتاب الكبائر (باب الوفاء بالعهود)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>واجب شرعاً والمسلم لا يخون ولا يغدر</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            أكد الذهبي أن الوفاء بالعقود والاعتمادات المستندية والشروط واجب شرعاً مع كافة المتعاملين والشركات الدولية دون تمييز أو غدر.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قول النبي ﷺ: «أَدِّ الأَمَانَةَ إِلَى مَنِ ائْتَمَنَكَ، وَلا تَخُنْ مَنْ خَانَكَ».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: الالتزام التام بكافة بنود عقود الاستيراد والاعتمادات المستندية البنكية.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 57 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="al_shinqiti" data-topic="contracts" data-search="57. ضوابط التأمين التجاري مقابل التأمين التعاوني التكافلي العلامة محمد الأمين الشنقيطي (رحمه الله) أوضح العلامة الشنقيطي رحمه الله في تفسيره أن عقود التأمين التجاري التقليدي تشتمل على الغرر الفاحش والمقامرة والربا لأن المستأمن يدفع أقساطاً ولا يدري هل يعوض أم لا. وأجاز التأمين التعاوني التكافلي القائم على التبرع المحض والتكافل بين المشتركين. أضواء البيان في إيضاح القرآن بالقرآن (تفسير سورة المائدة 2/45) تطبيق النظام: اختيار شركات التأمين التكافلي الإسلامي المعتمدة في بوالص التأمين على البضائع والحاويات البحرية.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    57. ضوابط التأمين التجاري مقابل التأمين التعاوني التكافلي
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
                                    قوله تعالى: ﴿وَتَعَاوَنُوا عَلَى الْبِرِّ وَالتَّقْوَىٰ وَلَا تَعَاوَنُوا عَلَى الْإِثْمِ وَالْعُدْوَانِ﴾.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: اختيار شركات التأمين التكافلي الإسلامي المعتمدة في بوالص التأمين على البضائع والحاويات البحرية.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 58 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="al_shinqiti" data-topic="contracts" data-search="58. ضوابط بيع السَّلَم في التوريدات والمحاصيل الزراعية ورأس المال العلامة محمد الأمين الشنقيطي (رحمه الله) أكد العلامة الشنقيطي رحمه الله أن عقد السَّلَم مستثنى من بيع ما ليس عند الإنسان لحاجة الناس إليه، ويشترط لصحته: تسليم كامل رأس المال مقدماً في مجلس العقد، وانضباط الصفة والمقدار والأجل المحددين. أضواء البيان في إيضاح القرآن بالقرآن (تفسير سورة البقرة 1/214) تطبيق النظام: قيد فواتير السَّلَم واستلام كامل الثمن في الخزينة مع تحديد تاريخ التوريد بدقة.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    58. ضوابط بيع السَّلَم في التوريدات والمحاصيل الزراعية ورأس المال
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
                                    حديث ابن عباس: «من أسلف في شيء فليسلف في كيل معلوم ووزن معلوم إلى أجل معلوم» متفق عليه.
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
    
                    <!-- Fatwa Card 59 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_shinqiti" data-topic="contracts" data-search="59. ضوابط المزارعة والمساقاة ونسب قسمة المحصول بين المالك والعامل العلامة محمد الأمين الشنقيطي (رحمه الله) تجوز المزارعة والمساقاة بدفع الأرض أو البستان للعامل على أن يكون له جزء مشاع من المحصول الناتج، ويحرم تخصيص قطعة معينة من الأرض لأحدهما لاحتمال تلفها دون غيرها. أضواء البيان (تفسير سورة الأنعام) تطبيق النظام: محاسبة عقود الإنتاج الزراعي وتوزيع الأطنان بنسب الحصص المشاعة.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    59. ضوابط المزارعة والمساقاة ونسب قسمة المحصول بين المالك والعامل
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> العلامة محمد الأمين الشنقيطي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: أضواء البيان (تفسير سورة الأنعام)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>جائزة وصحيحة إذا كانت قسمة المحصول بنسبة مشاعة (كالنصف أو الثلث)</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            تجوز المزارعة والمساقاة بدفع الأرض أو البستان للعامل على أن يكون له جزء مشاع من المحصول الناتج، ويحرم تخصيص قطعة معينة من الأرض لأحدهما لاحتمال تلفها دون غيرها.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    معاملة النبي ﷺ لأهل خيبر بشطر ما يخرج منها من ثمر أو زرع.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: محاسبة عقود الإنتاج الزراعي وتوزيع الأطنان بنسب الحصص المشاعة.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 60 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_shinqiti" data-topic="contracts" data-search="60. أحكام قسمة الأموال والشركات والتركات التجارية بالقرعة أو التراضي العلامة محمد الأمين الشنقيطي (رحمه الله) تصح قسمة التراضي أو قسمة القرعة بين الشركاء بعد تعديل الحصص وتثمين الأعيان بالعدل والقسطاس المستقيم لقطع النزاع. أضواء البيان (تفسير سورة النساء) تطبيق النظام: إعداد تقارير قسمة التصفية المحاسبية للشركاء وفق الحصص الرأسمالية.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    60. أحكام قسمة الأموال والشركات والتركات التجارية بالقرعة أو التراضي
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> العلامة محمد الأمين الشنقيطي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: أضواء البيان (تفسير سورة النساء)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائزة وصحيحة شرعاً بعد تقييم الحصص العادل</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            تصح قسمة التراضي أو قسمة القرعة بين الشركاء بعد تعديل الحصص وتثمين الأعيان بالعدل والقسطاس المستقيم لقطع النزاع.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قوله تعالى: ﴿وَإِذَا حَكَمْتُم بَيْنَ النَّاسِ أَن تَحْكُمُوا بِالْعَدْلِ﴾.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إعداد تقارير قسمة التصفية المحاسبية للشركاء وفق الحصص الرأسمالية.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 61 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_shinqiti" data-topic="ownership" data-search="61. تحريم ربح ما لم يُضمن والبيع قبل تمام القبض والحيازة العلامة محمد الأمين الشنقيطي (رحمه الله) نهى الشنقيطي عن بيع البضائع والسلع قبل أن تدخل في ضمان المشتري ويتمكن من حيازتها ونقلها إلى مخازنه تفادياً للوقوع في النهي عن ربح ما لم يضمن. المذكرات الفقهية في البيوع والمعاملات تطبيق النظام: اشتراط استلام البضاعة في المخزن أولاً وتأكيد إذن الإضافة قبل إنشاء فاتورة البيع.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    61. تحريم ربح ما لم يُضمن والبيع قبل تمام القبض والحيازة
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> العلامة محمد الأمين الشنقيطي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: المذكرات الفقهية في البيوع والمعاملات
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-rose-500/25 text-rose-300 border-rose-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✕</span>
                                <span>محرم بيع البضاعة قبل أن يستوفيها المشتري ويقبضها</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            نهى الشنقيطي عن بيع البضائع والسلع قبل أن تدخل في ضمان المشتري ويتمكن من حيازتها ونقلها إلى مخازنه تفادياً للوقوع في النهي عن ربح ما لم يضمن.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قول النبي ﷺ: «لا يحل سلف وبيع، ولا شرطان في بيع، ولا ربح ما لم يضمن».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: اشتراط استلام البضاعة في المخزن أولاً وتأكيد إذن الإضافة قبل إنشاء فاتورة البيع.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 62 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_shinqiti" data-topic="contracts" data-search="62. حكم إفراد الشهود والتوثيق المحاسبي في صفقات البيع الكبرى العلامة محمد الأمين الشنقيطي (رحمه الله) بسط العلامة الشنقيطي دلالة قوله تعالى: ﴿وَأَشْهِدُوا إِذَا تَبَايَعْتُمْ﴾ وبيَّن أن الإشهاد وتوثيق الفواتير في صفقات البيوع الآجلة والمقاولات من أعظم وسائل حماية أموال المسلمين. أضواء البيان (تفسير آية المداينة 1/210) تطبيق النظام: إدراج توقيع البائع والمشتري وأرقام السجلات التجارية رسمياً على الفواتير.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    62. حكم إفراد الشهود والتوثيق المحاسبي في صفقات البيع الكبرى
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> العلامة محمد الأمين الشنقيطي (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: أضواء البيان (تفسير آية المداينة 1/210)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>مستحب ومؤكد جداً لحفظ الأموال وقطع الخصومات</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            بسط العلامة الشنقيطي دلالة قوله تعالى: ﴿وَأَشْهِدُوا إِذَا تَبَايَعْتُمْ﴾ وبيَّن أن الإشهاد وتوثيق الفواتير في صفقات البيوع الآجلة والمقاولات من أعظم وسائل حماية أموال المسلمين.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    أمر الله بالإشهاد والتوثيق في سورة البقرة.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إدراج توقيع البائع والمشتري وأرقام السجلات التجارية رسمياً على الفواتير.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 63 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="al_albani" data-topic="defects" data-search="63. وجوب بيان عيوب البضاعة وخيار العيب ورد السلعة واسترجاع الثمن الإمام محمد ناصر الدين الألباني (رحمه الله) يحرم على التاجر كتمان أي عيب في الصنف أو المبيع. ومن اشترى سلعة فوجد بها عيباً قديماً لم يخبر به البائع، فله الخيار الكامل في رد السلعة واسترجاع الثمن كاملاً أو أخذ فارق القيمة (الأرش). سلسلة الأحاديث الصحيحة (رقم 1058) وفتاوى جدة تطبيق النظام: إتاحة خيار إرجاع المبيعات واسترجاع النقدية بسهولة عند وجود تلف أو عيب مصنعي.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    63. وجوب بيان عيوب البضاعة وخيار العيب ورد السلعة واسترجاع الثمن
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
    
                    <!-- Fatwa Card 64 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="al_albani" data-topic="riba" data-search="64. تحريم التسويق الهرمي والشبكي لجمعه بين الربا والغرر الإمام محمد ناصر الدين الألباني (رحمه الله) أفتى الشيخ الألباني رحمه الله واللجنة الدائمة بتحريم معاملات التسويق الشبكي والهرمي؛ لأن مقصود المعاملة هو العمولات وتجنيد المشتركين وليس السلعة، وهو يجمع بين ربا الفضل والنسيئة والغرر وأكل أموال الناس بالباطل. فتاوى الشيخ الألباني الاقتصادية وقرار مجمع الفقه الإسلامي تطبيق النظام: منع أنظمة الشجرة والعمولات الهرمية والاعتماد على التجارة السلعية الحقيقية.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    64. تحريم التسويق الهرمي والشبكي لجمعه بين الربا والغرر
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محمد ناصر الدين الألباني (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: فتاوى الشيخ الألباني الاقتصادية وقرار مجمع الفقه الإسلامي
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-rose-500/25 text-rose-300 border-rose-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✕</span>
                                <span>محرم تحريماً قاطعاً وباطلاً شرعاً</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            أفتى الشيخ الألباني رحمه الله واللجنة الدائمة بتحريم معاملات التسويق الشبكي والهرمي؛ لأن مقصود المعاملة هو العمولات وتجنيد المشتركين وليس السلعة، وهو يجمع بين ربا الفضل والنسيئة والغرر وأكل أموال الناس بالباطل.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    نهي النبي ﷺ عن الغرر وعن بيعتين في بيعة.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: منع أنظمة الشجرة والعمولات الهرمية والاعتماد على التجارة السلعية الحقيقية.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 65 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_albani" data-topic="exchange" data-search="65. جواز أخذ الأجرة على الحوالات المصرفية مقابل الخدمة الفعلية الإمام محمد ناصر الدين الألباني (رحمه الله) يجوز للبنوك والصرافات أخذ أجر مقطوع ومعلوم مقابل خدمة إرسال التحويل المالي وإشعار الاستلام، بشرط ألا تكون الأجرة نسبة مئوية تزيد بزيادة المبلغ عند اتحاد العملة تفادياً لشبهة الربا. سلسلة الهدى والنور وفتاوى المعاملات تطبيق النظام: قيد رسوم التحويلات المصرفية الثابتة كـ مصاريف بنكية تشغيلية.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    65. جواز أخذ الأجرة على الحوالات المصرفية مقابل الخدمة الفعلية
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محمد ناصر الدين الألباني (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: سلسلة الهدى والنور وفتاوى المعاملات
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>جائز إذا كانت الأجرة مقطوعة مقابل خدمة التحويل والاتصال</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يجوز للبنوك والصرافات أخذ أجر مقطوع ومعلوم مقابل خدمة إرسال التحويل المالي وإشعار الاستلام، بشرط ألا تكون الأجرة نسبة مئوية تزيد بزيادة المبلغ عند اتحاد العملة تفادياً لشبهة الربا.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قاعدة: «الأصل في أجور الوكالات والخدمات الإباحة».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: قيد رسوم التحويلات المصرفية الثابتة كـ مصاريف بنكية تشغيلية.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 66 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_albani" data-topic="riba" data-search="66. تحريم بيع العِينة والتواطؤ في التورق الصوري الثلاثي الإمام محمد ناصر الدين الألباني (رحمه الله) أكد الشيخ الألباني على تحريم بيوع العِينة والحيل الربوية التي يتواطأ فيها التاجر مع طرف ثالث لإعادة شراء السلعة نفسها بنقد حال أقل، وشدد على ضرورة خروج البضاعة من يد البائع الأول تماماً وانتقال ضمانها. السلسلة الصحيحة (رقم 11) وفتاوى رابغ تطبيق النظام: اشتراط القبض والحيازة ونقل المخزون فعلياً قبل إنشاء فاتورة البيع اللاحقة.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    66. تحريم بيع العِينة والتواطؤ في التورق الصوري الثلاثي
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محمد ناصر الدين الألباني (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: السلسلة الصحيحة (رقم 11) وفتاوى رابغ
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-rose-500/25 text-rose-300 border-rose-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✕</span>
                                <span>محرم وباطل شرعاً</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            أكد الشيخ الألباني على تحريم بيوع العِينة والحيل الربوية التي يتواطأ فيها التاجر مع طرف ثالث لإعادة شراء السلعة نفسها بنقد حال أقل، وشدد على ضرورة خروج البضاعة من يد البائع الأول تماماً وانتقال ضمانها.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    حديث: «إذا تبايعتم بالعينة وأخذتم أذناب البقر... سلط الله عليكم ذلاً».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: اشتراط القبض والحيازة ونقل المخزون فعلياً قبل إنشاء فاتورة البيع اللاحقة.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 67 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_albani" data-topic="contracts" data-search="67. حكم فتح الحسابات الجارية واستلام الرواتب عبر البنوك عند الحاجة الإمام محمد ناصر الدين الألباني (رحمه الله) يجوز فتح حساب جارٍ بدون فوائد لاستلام الرواتب أو إدارة التحويلات التجارية الضرورية مع الحذر التام من الدخول في حسابات الودائع الاستثمارية الربوية. فتاوى الهدى والنور (شريط 312) تطبيق النظام: ربط حسابات المؤسسة بالحسابات الجارية النقية ومطابقة كشوف الحساب دورياً.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    67. حكم فتح الحسابات الجارية واستلام الرواتب عبر البنوك عند الحاجة
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محمد ناصر الدين الألباني (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: فتاوى الهدى والنور (شريط 312)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>جائز للحاجة في الحسابات الجارية غير الربوية دون أخذ فوائد</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يجوز فتح حساب جارٍ بدون فوائد لاستلام الرواتب أو إدارة التحويلات التجارية الضرورية مع الحذر التام من الدخول في حسابات الودائع الاستثمارية الربوية.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قاعدة: «الحاجة العامة تنزل منزلة الضرورة الخاصة».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: ربط حسابات المؤسسة بالحسابات الجارية النقية ومطابقة كشوف الحساب دورياً.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 68 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_albani" data-topic="contracts" data-search="68. حكم طباعة ونشر وتجارة الكتب والمصاحف وأخذ الأجرة عليها الإمام محمد ناصر الدين الألباني (رحمه الله) يجوز بيع وشراء الكتب المطبوعة والمصاحف وتجارة الورق وأخذ الأرباح العادلة مقابل تكاليف الورق والتجليد والنقل والتوزيع. فتاوى الشيخ الألباني تطبيق النظام: احتساب تكلفة الطباعة والتغليف والتوزيع ضمن تكلفة المنتج النهائي.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    68. حكم طباعة ونشر وتجارة الكتب والمصاحف وأخذ الأجرة عليها
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> الإمام محمد ناصر الدين الألباني (رحمه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: فتاوى الشيخ الألباني
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز شرعاً وأخذ الأجر مقابل الورق والطباعة والتجليد والتوزيع</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يجوز بيع وشراء الكتب المطبوعة والمصاحف وتجارة الورق وأخذ الأرباح العادلة مقابل تكاليف الورق والتجليد والنقل والتوزيع.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    عمل الصحابة والتابعين في كتابة المصاحف بالأجرة وبيع الورق.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: احتساب تكلفة الطباعة والتغليف والتوزيع ضمن تكلفة المنتج النهائي.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 69 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="al_fawzan" data-topic="exchange" data-search="69. الصرف والتحويلات الفورية عبر تطبيقات الموبايل بنك (القبض الحكمي) معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) يشترط في بيع العملات المختلفة (دولار مقابل جنيه أو ريال) التقابض الفوري. والقيد المصرفي المكتمل أو إشعار التحويل البنكي الفوري يُعد قبضاً حكمياً معتبراً شرعاً يقوم مقام القبض باليد. المنتقى من فتاوى الفوزان ومجمع الفقه الإسلامي التنبيه في البرنامج: لا يجوز الصرف بالدين أو تأجيل تسليم أحد المبلغين، بل يجب أن يكون التحويل لحظياً ومؤكداً بالإشعار.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    69. الصرف والتحويلات الفورية عبر تطبيقات الموبايل بنك (القبض الحكمي)
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
    
                    <!-- Fatwa Card 70 -->
                    <div class="fiqh-master-card" data-featured="true" data-scholar="al_fawzan" data-topic="gold" data-search="70. شراء الذهب والفضة عبر البطاقات المصرفية ونقاط البيع (pos) معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) يجوز شراء الذهب والفضة ببطاقة الصراف المباشرة (مدى / الخصم الفوري) لأن الخصم من حساب المشتري والإيداع في حساب التاجر يتم فوراً في المجلس فيتحقق القبض الشرعي. الفتاوى الاقتصادية وقرار المجمع الفقهي الإسلامي تحذير النظام: يُحظر بيع الذهب بالتقسيط أو الشيكات الآجلة أو تأخير التسليم.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    70. شراء الذهب والفضة عبر البطاقات المصرفية ونقاط البيع (POS)
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
    
                    <!-- Fatwa Card 71 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_fawzan" data-topic="contracts" data-search="71. ضوابط المرابحة للآمر بالشراء عبر البنوك (شراء الحديد، المعدات، والسيارات) معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) تصح معاملة المرابحة للآمر بالشراء إذا اشترى البنك السلعة أولاً من المورد وقبضها وحازها ودخلت في ضمانه، ثم بعد ذلك أبرم عقد البيع بالتقسيط مع العميل بربح معلوم. ولا يجوز إذا دفع البنك النقد مباشرة للعميل أو قبل تملك البضاعة. المنتقى من فتاوى الفوزان (فتاوى المعاملات المصرفية) تطبيق النظام: إثبات فاتورة شراء الأصول أولاً في حساب المخزن قبل إنشاء فاتورة البيع والتقسيط للعميل.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    71. ضوابط المرابحة للآمر بالشراء عبر البنوك (شراء الحديد، المعدات، والسيارات)
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: المنتقى من فتاوى الفوزان (فتاوى المعاملات المصرفية)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>جائزة بشرط شراء البنك وقبضه للسلعة قبل بيعها للعميل</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            تصح معاملة المرابحة للآمر بالشراء إذا اشترى البنك السلعة أولاً من المورد وقبضها وحازها ودخلت في ضمانه، ثم بعد ذلك أبرم عقد البيع بالتقسيط مع العميل بربح معلوم. ولا يجوز إذا دفع البنك النقد مباشرة للعميل أو قبل تملك البضاعة.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    حديث النبي ﷺ: «لا تبع ما ليس عندك» و «نهى عن ربح ما لم يضمن».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: إثبات فاتورة شراء الأصول أولاً في حساب المخزن قبل إنشاء فاتورة البيع والتقسيط للعميل.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 72 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_fawzan" data-topic="exchange" data-search="72. التحذير من التعامل بالعملات الرقمية المشفرة (البيتكوين والفوركس غير المنضبط) معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) حذر الشيخ الفوزان من تداول العملات الرقمية والمشفرة غير الصادرة عن جهات معترف بها دولياً لأنها مجهولة المصدر وشديدة التذبذب والمخاطرة وتدخل في بيع الغرر والمقامرة والميسر. فتاوى الشيخ الفوزان في العملات الرقمية والبورصات تطبيق النظام: قصر المعاملات المالية على العملات الرسمية المعتمدة (sdg, usd, sar, aed, eur).">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    72. التحذير من التعامل بالعملات الرقمية المشفرة (البيتكوين والفوركس غير المنضبط)
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: فتاوى الشيخ الفوزان في العملات الرقمية والبورصات
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-rose-500/25 text-rose-300 border-rose-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✕</span>
                                <span>محرم لما فيها من الغرر الشديد والمقامرة والجهالة</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            حذر الشيخ الفوزان من تداول العملات الرقمية والمشفرة غير الصادرة عن جهات معترف بها دولياً لأنها مجهولة المصدر وشديدة التذبذب والمخاطرة وتدخل في بيع الغرر والمقامرة والميسر.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    نهي النبي ﷺ عن بيع الغرر وعن إضاعة المال.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: قصر المعاملات المالية على العملات الرسمية المعتمدة (SDG, USD, SAR, AED, EUR).
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 73 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_fawzan" data-topic="riba" data-search="73. ضوابط التورق الفردي للحصول على السيولة النقدية وسداد الالتزامات معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) يجوز لمن احتاج إلى نقد أن يشتري سلعة (كحديد أو سيارة) بالأجل بثمن أعلى، ثم يقبضها ويحوزها فعلياً، ثم يبيعها لطرف ثالث في السوق نقداً بسعر أقل ليسدد بها ديونه أو التزاماته. الملخص الفقهي (كتاب البيوع - باب التورق) تطبيق النظام: تسجيل فواتير الشراء والبيع لطرف ثالث مستقل تماماً لضبط قيد التورق.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    73. ضوابط التورق الفردي للحصول على السيولة النقدية وسداد الالتزامات
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: الملخص الفقهي (كتاب البيوع - باب التورق)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>جائز بالشروط الشرعية المعتبرة</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يجوز لمن احتاج إلى نقد أن يشتري سلعة (كحديد أو سيارة) بالأجل بثمن أعلى، ثم يقبضها ويحوزها فعلياً، ثم يبيعها لطرف ثالث في السوق نقداً بسعر أقل ليسدد بها ديونه أو التزاماته.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    عموم قوله تعالى: ﴿وَأَحَلَّ اللَّهُ الْبَيْعَ﴾.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: تسجيل فواتير الشراء والبيع لطرف ثالث مستقل تماماً لضبط قيد التورق.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 74 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_fawzan" data-topic="contracts" data-search="74. حكم شراء السلع عبر المتاجر الإلكترونية والتطبيقات بالصفة المحددة معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) يجوز الشراء عبر التطبيقات والمواقع الإلكترونية للسلع الموصوفة بدقة (البيع بالصفة)، فإذا وصلت السلعة مطابقة لزم البيع، وإن خالفت المواصفات ثبت للمشتري حق الإرجاع والاسترداد. الفتاوى العصرية في التجارة الإلكترونية تطبيق النظام: ربط كتالوج المنتجات الإلكتروني بالمواصفات الفنية والصور الحقيقية للصنف.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    74. حكم شراء السلع عبر المتاجر الإلكترونية والتطبيقات بالصفة المحددة
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: الفتاوى العصرية في التجارة الإلكترونية
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز شرعاً بضبط المواصفات وثبوت خيار الرؤية عند الاستلام</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يجوز الشراء عبر التطبيقات والمواقع الإلكترونية للسلع الموصوفة بدقة (البيع بالصفة)، فإذا وصلت السلعة مطابقة لزم البيع، وإن خالفت المواصفات ثبت للمشتري حق الإرجاع والاسترداد.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قياساً على بيع السَّلَم وخيار العيب الثابت بالسنة.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: ربط كتالوج المنتجات الإلكتروني بالمواصفات الفنية والصور الحقيقية للصنف.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 75 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_fawzan" data-topic="contracts" data-search="75. حكم الصناديق الاستثمارية في العقارات والحديد ومواد البناء معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) تصح الصناديق والمساهمات الاستثمارية التي تشتري وتطور العقارات أو تستورد الحديد ومواد البناء وتباشر التجارة الحقيقية مع توزيع الأرباح بنسب مئوية من صافي العائد الفعلي. المنتقى من فتاوى الفوزان (فتاوى الاستثمار والتجارة) تطبيق النظام: تقييم الأصول السنوي واحتساب العائد الحقيقي على الاستثمار (roi) لكل مساهم.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    75. حكم الصناديق الاستثمارية في العقارات والحديد ومواد البناء
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: المنتقى من فتاوى الفوزان (فتاوى الاستثمار والتجارة)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>جائزة شرعاً إذا كانت استثماراً في أصول حقيقية بأرباح مشاعة</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            تصح الصناديق والمساهمات الاستثمارية التي تشتري وتطور العقارات أو تستورد الحديد ومواد البناء وتباشر التجارة الحقيقية مع توزيع الأرباح بنسب مئوية من صافي العائد الفعلي.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قاعدة: «الغُنْم بالغُرْم والخراج بالضمان».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: تقييم الأصول السنوي واحتساب العائد الحقيقي على الاستثمار (ROI) لكل مساهم.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 76 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_fawzan" data-topic="pricing" data-search="76. حكم البيع والشراء عبر المزادات الرقمية والتطبيقات الذكية معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) تصح المزادات الإلكترونية لبيع السيارات والمعدات والعقارات عبر التطبيقات والمنصات الرقمية ما دامت المنظومة تمنع المزايدات الوهمية الصورية (النجش) وتضمن فحص المبيع. فتاوى التقنية الحديثة والمعاملات تطبيق النظام: توثيق عروض المزايدة برقم الهوية ورقم الحساب البنكي لضمان الجدية.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    76. حكم البيع والشراء عبر المزادات الرقمية والتطبيقات الذكية
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: فتاوى التقنية الحديثة والمعاملات
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز إذا انضبطت المزايدة وخيار المعاينة وخلوها من النجش الآلي</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            تصح المزادات الإلكترونية لبيع السيارات والمعدات والعقارات عبر التطبيقات والمنصات الرقمية ما دامت المنظومة تمنع المزايدات الوهمية الصورية (النجش) وتضمن فحص المبيع.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    الأصل في وسائل البيع الحديثة الحل ما لم تتضمن غشاً.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: توثيق عروض المزايدة برقم الهوية ورقم الحساب البنكي لضمان الجدية.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 77 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_fawzan" data-topic="contracts" data-search="77. حكم التعامل مع المقاولين من الباطن وشروط استحقاق الدفعات معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) يجوز للمقاول الرئيسي إسناد جزء من الأعمال لمقاول باطن متخصص ما لم يشترط المالك المباشرة الشخصية، ويكون المقاول الرئيسي مسؤولاً عن الجودة أمام المالك. المنتقى من فتاوى الفوزان (كتاب الإجارة) تطبيق النظام: ربط مستخلصات مقاولي الباطن بنسب الإنجاز الفعلي المعتمدة هندسياً.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    77. حكم التعامل مع المقاولين من الباطن وشروط استحقاق الدفعات
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: المنتقى من فتاوى الفوزان (كتاب الإجارة)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز بشرط الوفاء بمواصفات العقد الأصلي والجدول الزمني</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يجوز للمقاول الرئيسي إسناد جزء من الأعمال لمقاول باطن متخصص ما لم يشترط المالك المباشرة الشخصية، ويكون المقاول الرئيسي مسؤولاً عن الجودة أمام المالك.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قوله تعالى: ﴿وَأَوْفُوا بِالْعَهْدِ إِنَّ الْعَهْدَ كَانَ مَسْئُولًا﴾.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: ربط مستخلصات مقاولي الباطن بنسب الإنجاز الفعلي المعتمدة هندسياً.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 78 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_fawzan" data-topic="contracts" data-search="78. ضوابط بيع وشراء السكراب والحديد الخردة والمعادن المستهلكة معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) يجوز بيع حديد السكراب والمعادن المستعملة بالوزن على الميزان البسكول أو بيعه جزافاً بعد المعاينة المباشرة ونفي الجهالة الفاحشة. فتاوى نور على الدرب والملخص الفقهي تطبيق النظام: ربط فواتير السكراب بأوزان ميزان البسكول وخصم أوزان الشوائب المتفق عليها.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    78. ضوابط بيع وشراء السكراب والحديد الخردة والمعادن المستهلكة
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: فتاوى نور على الدرب والملخص الفقهي
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>جائز بالوزن الفعلي وتحديد السعر لكل طن أو مقطوعاً بعد الرؤية</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يجوز بيع حديد السكراب والمعادن المستعملة بالوزن على الميزان البسكول أو بيعه جزافاً بعد المعاينة المباشرة ونفي الجهالة الفاحشة.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    عموم أدلة إباحة التجارة والبيوع.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: ربط فواتير السكراب بأوزان ميزان البسكول وخصم أوزان الشوائب المتفق عليها.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 79 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_fawzan" data-topic="contracts" data-search="79. أحكام الضمانات البنكية (خطابات الضمان الابتدائية والنهائية lg) معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) يجوز للبنوك أخذ رسوم ومصاريف إدارية مقطوعة مقابل إصدار خطابات الضمان (letters of guarantee) للدخول في المناقصات وتنفيذ المشروعات، ويحرم أخذ نسبة ربوية ترتبط بالمبلغ والأجل دون غطاء نقدي. فتاوى المعاملات المصرفية وقرار مجمع الفقه تطبيق النظام: قيد مصاريف خطابات الضمان كـ مصاريف بنكية تشغيلية.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    79. أحكام الضمانات البنكية (خطابات الضمان الابتدائية والنهائية LG)
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: فتاوى المعاملات المصرفية وقرار مجمع الفقه
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-500/50 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>⚠️</span>
                                <span>جائزة الرسوم الفعلية لإصدار الخطاب · ومحرم أخذ فائدة ربوية</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            يجوز للبنوك أخذ رسوم ومصاريف إدارية مقطوعة مقابل إصدار خطابات الضمان (Letters of Guarantee) للدخول في المناقصات وتنفيذ المشروعات، ويحرم أخذ نسبة ربوية ترتبط بالمبلغ والأجل دون غطاء نقدي.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    الكفالة والضمان عقد إرفاق، والرسوم جائزة مقابل الخدمة الإدارية الفعلية.
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: قيد مصاريف خطابات الضمان كـ مصاريف بنكية تشغيلية.
                                </span>
                            </div>
                        </div>

                    </div>
    
                    <!-- Fatwa Card 80 -->
                    <div class="fiqh-master-card" data-featured="false" data-scholar="al_fawzan" data-topic="contracts" data-search="80. حكم تصفية الشركات عند الخسارة وتوزيع الموجودات بالحصص العادلة معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) قاعدة الشريعة المحكمة في الشركات: الخسارة المالية تقع على رأس المال بنسبة حصة كل شريك، ولا يجوز تحميل أحد الشركاء خسارة أكثر من نسبة رأسماله. الملخص الفقهي (باب الشركات وتصفيتها) تطبيق النظام: حساب نسب التصفية وتوزيع الخسائر تلقائياً وفق رأس المال المسجل.">
                        
                        <!-- Header: Title & Scholar Badge -->
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 pb-3 mb-3">
                            <div class="space-y-1.5">
                                <h4 class="text-lg md:text-xl font-black text-amber-300 leading-tight">
                                    80. حكم تصفية الشركات عند الخسارة وتوزيع الموجودات بالحصص العادلة
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/50 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-user-tie text-amber-400"></i> معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">
                                        📖 المصدر المعتمد: الملخص الفقهي (باب الشركات وتصفيتها)
                                    </span>
                                </div>
                            </div>

                            <!-- Ruling Badge -->
                            <div class="px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/20 text-emerald-300 border-emerald-500/40 text-xs md:text-sm font-black flex items-center gap-1.5 shadow-md flex-shrink-0">
                                <span>✓</span>
                                <span>توزع الخسارة على رؤوس الأموال حصراً · وتوزع الأرباح حسب الاتفاق</span>
                            </div>
                        </div>

                        <!-- Fatwa Text Body -->
                        <p class="text-sm md:text-base text-slate-200 font-bold leading-relaxed mb-3">
                            قاعدة الشريعة المحكمة في الشركات: الخسارة المالية تقع على رأس المال بنسبة حصة كل شريك، ولا يجوز تحميل أحد الشركاء خسارة أكثر من نسبة رأسماله.
                        </p>

                        <!-- Evidence & Practical Solution Blocks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-800/80 text-xs">
                            <div class="p-3.5 bg-slate-950/80 rounded-2xl border border-slate-800">
                                <span class="text-sky-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    📜 الدليل الشرعي وتأصيل المسألة:
                                </span>
                                <span class="text-slate-300 font-bold leading-relaxed block">
                                    قضاء علي بن أبي طالب رضي الله عنه: «الوضيعة على المال، والربح على ما اصطلحوا عليه».
                                </span>
                            </div>

                            <div class="p-3.5 bg-emerald-950/30 rounded-2xl border border-emerald-500/40">
                                <span class="text-emerald-400 font-black flex items-center gap-1.5 mb-1 text-xs">
                                    💡 التطبيق والبديل الشرعي المعتمد في البرنامج:
                                </span>
                                <span class="text-slate-200 font-bold leading-relaxed block">
                                    تطبيق النظام: حساب نسب التصفية وتوزيع الخسائر تلقائياً وفق رأس المال المسجل.
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
                
                <!-- ════════════════════════════════════════════════════════════ -->
                <!-- SUB-SECTION 7: LIVE TARGETED GOOGLE SCHOLAR SEARCH ENGINE    -->
                <!-- ════════════════════════════════════════════════════════════ -->
                <div id="fiqh-sub-google" class="space-y-4 hidden">
                    <div class="p-6 bg-gradient-to-br from-slate-900 via-sky-950/40 to-slate-900 border-2 border-sky-500/50 rounded-2xl shadow-2xl space-y-4">
                        
                        <!-- Header -->
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="px-3 py-1 bg-sky-500/20 text-sky-300 font-mono font-black text-xs rounded-xl border border-sky-400">
                                        ★ محرك بحث قوقل الذكي الموثق (Google Search Engine)
                                    </span>
                                    <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-black text-xs rounded-xl border border-amber-400">
                                        حصر النتائج في الشيوخ الـ 12 والمواقع الرسمية فقط ✓
                                    </span>
                                </div>
                                <h3 class="text-xl md:text-2xl font-black text-sky-300 flex items-center gap-2">
                                    <i class="fa-brands fa-google text-sky-400"></i>
                                    البحث العالمي المباشر عن الفتاوى عبر قوقل للشيوخ الـ 12 المعتمدين
                                </h3>
                                <p class="text-xs md:text-sm text-slate-300 font-bold mt-1">
                                    يبحث هذا المحرك الذكي في شبكة الإنترنت العالمية مع <strong class="text-amber-300 font-black">إلزام محرك قوقل بعدم إظهار أي فتوى إلا إذا كانت صادرة عن أحد الأئمة الـ 12 المعتمدين حصراً</strong> ومن المواقع والمكتبات الإسلامية الرسمية والمحققة.
                                </p>
                            </div>
                            <span class="text-4xl hidden md:block">🌐</span>
                        </div>

                        <!-- Google Live Search Form -->
                        <div class="p-5 bg-slate-950/90 rounded-2xl border border-sky-500/30 space-y-4 shadow-inner">
                            
                            <!-- Search Input -->
                            <div>
                                <label class="text-xs md:text-sm font-black text-amber-300 block mb-1.5">
                                    🔍 اكتب سؤالك أو مسألتك الفقهية للبحث عنها في قوقل:
                                </label>
                                <div class="relative">
                                    <input type="text" id="google-fiqh-query" placeholder="مثال: حكم شراء سيارة أو معدات بالمرابحة البنكية، بيع سكراب، تأخير تسليم حديد..." class="tool-input text-sm md:text-base font-bold pr-4 pl-12 py-3 bg-slate-900 border-2 border-sky-500/40 focus:border-sky-400 text-white rounded-xl w-full">
                                    <button type="button" onclick="executeGoogleScholarSearch()" class="absolute left-2 top-2 px-4 py-1.5 bg-sky-500 hover:bg-sky-400 text-slate-950 font-black text-xs rounded-lg shadow transition">
                                        بحث ↗
                                    </button>
                                </div>
                            </div>

                            <!-- Filter Grid: Scholar + Target Domain -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                
                                <!-- Scholar Filter Selector -->
                                <div>
                                    <label class="text-xs font-black text-slate-300 block mb-1">
                                        👤 تخصيص الشيخ أو الإمام في قوقل:
                                    </label>
                                    <select id="google-scholar-select" class="tool-input text-xs font-black bg-slate-900 border border-slate-700 text-amber-300 py-2.5 rounded-xl w-full">
                                        <option value="all">🌟 جميع الأئمة والعلماء الـ 12 معاً (تلقائي)</option>
                                        <option value="ibn_baz">📜 الإمام عبد العزيز بن باز (رحمه الله)</option>
                                        <option value="ibn_uthaymeen">💡 الإمام محمد بن صالح العثيمين (رحمه الله)</option>
                                        <option value="al_fawzan">⚖️ معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)</option>
                                        <option value="ibn_taymiyyah">📖 شيخ الإسلام ابن تيمية (رحمه الله)</option>
                                        <option value="ibn_al_qayyim">✨ الإمام ابن قيم الجوزية (رحمه الله)</option>
                                        <option value="imam_ahmad">👑 الإمام أحمد بن حنبل (رحمه الله)</option>
                                        <option value="al_nawawi">📜 الإمام محيي الدين النووي (رحمه الله)</option>
                                        <option value="al_dhahabi">⚖️ الإمام الحافظ الذهبي (رحمه الله)</option>
                                        <option value="muhammad_bin_abdulwahhab">📚 الإمام محمد بن عبد الوهاب (رحمه الله)</option>
                                        <option value="al_saadi">💡 العلامة الشيخ عبد الرحمن السعدي (رحمه الله)</option>
                                        <option value="al_shinqiti">📖 العلامة محمد الأمين الشنقيطي (رحمه الله)</option>
                                        <option value="al_albani">📚 الإمام محمد ناصر الدين الألباني (رحمه الله)</option>
                                    </select>
                                </div>

                                <!-- Target Sites Selector -->
                                <div>
                                    <label class="text-xs font-black text-slate-300 block mb-1">
                                        🏛️ نطاق المواقع والمكتبات العالمية المعتمدة:
                                    </label>
                                    <select id="google-sites-select" class="tool-input text-xs font-black bg-slate-900 border border-slate-700 text-emerald-300 py-2.5 rounded-xl w-full">
                                        <option value="all_approved">🌐 كافة المواقع الرسمية والمكتبة الشاملة والدرر السنية وإسلام ويب</option>
                                        <option value="official_only">🏛️ المواقع الرسمية للشيوخ فقط (ابن باز، ابن عثيمين، الفوزان، الألباني)</option>
                                        <option value="shamela">📚 المكتبة الشاملة الحديثة (shamela.ws)</option>
                                        <option value="binbaz">📜 موقع الشيخ ابن باز الرسمي (binbaz.org.sa)</option>
                                        <option value="uthaymeen">💡 موقع الشيخ ابن عثيمين الرسمي (ibnothaimeen.net)</option>
                                        <option value="fawzan">⚖️ موقع الشيخ صالح الفوزان الرسمي (alfawzan.af.org.sa)</option>
                                        <option value="albany">📚 موقع الشيخ الألباني الرسمي (alalbany.org)</option>
                                        <option value="dorar">💎 موسوعة الدرر السنية (dorar.net)</option>
                                        <option value="islamweb">📖 شبكة إسلام ويب (islamweb.net)</option>
                                        <option value="alukah">📄 شبكة الألوكة الفقهية (alukah.net)</option>
                                    </select>
                                </div>

                            </div>

                            <!-- Main Search Execution Button -->
                            <div class="pt-2">
                                <button type="button" onclick="executeGoogleScholarSearch()" class="w-full py-3.5 px-6 bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600 hover:from-sky-400 hover:via-blue-500 hover:to-indigo-500 text-white font-black text-sm md:text-base rounded-xl shadow-xl flex items-center justify-center gap-2 transition transform hover:-translate-y-0.5 border border-sky-300/40">
                                    <i class="fa-brands fa-google text-lg"></i>
                                    <span>افتح نتائج البحث الموثق في محرك قوقل العالمي الآن ↗</span>
                                </button>
                            </div>

                        </div>

                        <!-- 10 Instant Topic Search Chips -->
                        <div class="space-y-2 pt-2">
                            <span class="text-xs font-black text-amber-300 block">
                                ⚡ نماذج أسئلة وفتاوى تجارية جاهزة للبحث الفوري في قوقل (اضغط للبحث مباشرة):
                            </span>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم شراء سيارات أو معدات بالمرابحة البنكية للآمر بالشراء')">🚗 المرابحة البنكية للسيارات</button>
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم الشرط الجزائي وغرامات التأخير في عقود المقاولات والتوريد')">🏗️ الشرط الجزائي للمقاولات</button>
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم بيع حديد السكراب والخردة وضوابط وزن البسكول')">🔩 بيع حديد السكراب</button>
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم التورق الفردي وسداد الديون للحصول على السيولة')">💳 التورق للحصول على سيولة</button>
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم خصم الشيكات والكمبيالات الآجلة بنقد حال')">📜 حكم خصم الشيكات</button>
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم شراء الذهب والفضة عبر بطاقات الصراف ومدى ونقاط البيع')">💎 شراء الذهب بالبطاقة</button>
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم الإيجار المنتهي بالتمليك وضوابطه الشرعية الصحيحة')">🏢 الإيجار المنتهي بالتمليك</button>
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم بيع الاسم التجاري والعلامات التجارية والفرنشايز')">🏷️ بيع العلامة التجارية</button>
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم التأمين التعاوني التكافلي للبضائع والشحن البحري')">🚢 التأمين التكافلي للبضائع</button>
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم الصرف الفوري عبر تطبيقات الموبايل بنك والقبض الحكمي')">📱 الصرف عبر الموبايل بنك</button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- SUB-SECTION 2: GRAND CLASSICAL & CONTEMPORARY BOOKS LIBRARY  -->
                <!-- ════════════════════════════════════════════════════════════ -->
                <div id="fiqh-sub-books" class="space-y-4 hidden">
                    <div class="p-5 bg-amber-500/15 border-2 border-amber-500/40 rounded-2xl flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 font-black text-xs rounded-xl border border-amber-400">
                                    ★ المكتبة الفقهية الشاملة للبيوع والمعاملات
                                </span>
                                <span class="px-3.5 py-1 bg-emerald-500/20 text-emerald-300 font-mono font-black text-xs rounded-xl border border-emerald-400">
                                    26 كتاباً وموسوعة محققة ✓
                                </span>
                            </div>
                            <h3 class="text-xl md:text-2xl font-black text-amber-300 flex items-center gap-2">
                                <i class="fa-solid fa-book-bookmark text-amber-400"></i>
                                مكتبة أمهات كتب ومؤلفات ومجلدات البيوع لكبار أئمة وعلماء السنة (12 إماماً)
                            </h3>
                            <p class="text-xs md:text-sm text-slate-200 font-bold mt-1">
                                الفهرس والمحتوى العلمي الدقيق لأمهات كتب ومؤلفات: <span class="text-amber-300 font-black">أحمد بن حنبل</span> · <span class="text-amber-300 font-black">ابن تيمية</span> · <span class="text-amber-300 font-black">ابن القيم</span> · <span class="text-amber-300 font-black">النووي</span> · <span class="text-amber-300 font-black">الذهبي</span> · <span class="text-amber-300 font-black">محمد بن عبد الوهاب</span> · <span class="text-amber-300 font-black">السعدي</span> · <span class="text-amber-300 font-black">الشنقيطي</span> · <span class="text-amber-300 font-black">الألباني</span> · <span class="text-amber-300 font-black">ابن باز</span> · <span class="text-amber-300 font-black">ابن عثيمين</span> · <span class="text-amber-300 font-black">الفوزان</span>.
                            </p>
                        </div>

                        <!-- Instant Books Search Box -->
                        <div class="w-full md:w-80">
                            <label class="text-xs font-bold text-slate-300 block mb-1">🔍 ابحث في أسماء ومجلدات الكتب:</label>
                            <input type="text" id="book-search-input" oninput="filterBooksLibrary()" placeholder="اكتب للبحث: مجموع الفتاوى، الممتع، إعلام الموقعين..." class="tool-input text-xs font-bold">
                        </div>
                    </div>

                    <!-- 🎛️ Filter Books by Scholar Bar 🎛️ -->
                    <div class="bg-slate-900/90 p-3.5 rounded-2xl border border-amber-500/30 flex flex-wrap items-center gap-1.5">
                        <span class="text-xs font-black text-amber-400 block w-full mb-1">
                            👤 تصفية الكتب حسب الإمام والمؤلف:
                        </span>
                        <button type="button" class="book-filter-btn fiqh-filter-btn active" data-bscholar="all" onclick="setBookScholar('all', this)">🌟 جميع الكتب (26)</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="imam_ahmad" onclick="setBookScholar('imam_ahmad', this)">👑 أحمد بن حنبل</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="ibn_taymiyyah" onclick="setBookScholar('ibn_taymiyyah', this)">📖 ابن تيمية</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="ibn_al_qayyim" onclick="setBookScholar('ibn_al_qayyim', this)">✨ ابن القيم</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="al_nawawi" onclick="setBookScholar('al_nawawi', this)">📜 النووي</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="al_dhahabi" onclick="setBookScholar('al_dhahabi', this)">⚖️ الذهبي</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="muhammad_bin_abdulwahhab" onclick="setBookScholar('muhammad_bin_abdulwahhab', this)">📚 محمد بن عبد الوهاب</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="al_saadi" onclick="setBookScholar('al_saadi', this)">💡 السعدي</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="al_shinqiti" onclick="setBookScholar('al_shinqiti', this)">📖 الشنقيطي</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="al_albani" onclick="setBookScholar('al_albani', this)">📚 الألباني</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="ibn_baz" onclick="setBookScholar('ibn_baz', this)">📜 ابن باز</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="ibn_uthaymeen" onclick="setBookScholar('ibn_uthaymeen', this)">💡 ابن عثيمين</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="al_fawzan" onclick="setBookScholar('al_fawzan', this)">⚖️ الفوزان</button>
                    </div>

                    <!-- Books Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="books-grid-container">

        <!-- Book Card 1 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="imam_ahmad" data-book-search="مسائل الإمام أحمد في البيوع والتجارة (رواية عبد الله والكوسج والمغني) الإمام المبجل أحمد بن حنبل (ت 241 هـ) خيار المجلس · خيار الشرط · السَّلَم · الرهن · القرض وتأجيل الديون · الشفعة الأصل العظيم في تأسيس مذهب أهل السنة بأن الأصل في العقود والشروط الإباحة والصحة، وثبوت خيار المجلس والشرط للتراضي التام، وجواز تأجيل سداد القرض بالتراضي.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #1
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 مجلدات وروايات متعددة
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/8505/1800" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 مسائل الإمام أحمد في البيوع والتجارة (رواية عبد الله والكوسج والمغني) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام المبجل أحمد بن حنبل (ت 241 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">خيار المجلس · خيار الشرط · السَّلَم · الرهن · القرض وتأجيل الديون · الشفعة</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    الأصل العظيم في تأسيس مذهب أهل السنة بأن الأصل في العقود والشروط الإباحة والصحة، وثبوت خيار المجلس والشرط للتراضي التام، وجواز تأجيل سداد القرض بالتراضي.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المرجع الفقهي الأول للمذهب الحنبلي وأهل الحديث في المعاملات المالية.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/8505/1800" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (المغني وشرح مسائل الإمام أحمد)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 2 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="imam_ahmad" data-book-search="كتاب الورع للإمام أحمد (أبواب الورع في المكاسب والتجارة والأسواق) الإمام المبجل أحمد بن حنبل (ت 241 هـ) الحلال والحرام · اجتناب الشبهات · الورع في المماكسة · النزاهة المالية بيان دقيق لضوابط الورع في التجارة، وكيفية تطهير المكاسب من الشبهات، والتحذير من أكل أموال الناس بالباطل والمماطلة في الديون.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #2
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 مجلد واحد مفرد
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/7454" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 كتاب الورع للإمام أحمد (أبواب الورع في المكاسب والتجارة والأسواق) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام المبجل أحمد بن حنبل (ت 241 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">الحلال والحرام · اجتناب الشبهات · الورع في المماكسة · النزاهة المالية</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    بيان دقيق لضوابط الورع في التجارة، وكيفية تطهير المكاسب من الشبهات، والتحذير من أكل أموال الناس بالباطل والمماطلة في الديون.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">الدستور الأخلاقي والسلوكي للتاجر والمحاسب المسلم.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/7454" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (كتاب الورع للإمام أحمد)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 3 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="imam_ahmad" data-book-search="الجامع لعلوم الإمام أحمد (فقه المعاملات المالية المروية) الإمام المبجل أحمد بن حنبل (ت 241 هـ) القواعد الفقهية · تصحيح شروط التجار · التيسير في المعاملات جمع شامل لكافة نصوص وفتاوى الإمام أحمد في البيوع والشركات والمضاربة والإجارات والرهونات.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #3
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 موسوعة جامعة (قسم البيوع)
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/97356" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 الجامع لعلوم الإمام أحمد (فقه المعاملات المالية المروية) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام المبجل أحمد بن حنبل (ت 241 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">القواعد الفقهية · تصحيح شروط التجار · التيسير في المعاملات</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    جمع شامل لكافة نصوص وفتاوى الإمام أحمد في البيوع والشركات والمضاربة والإجارات والرهونات.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">مرجع للمقارنة بين الروايات الفقهية واختيارات كبار الأئمة.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/97356" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (الجامع لعلوم الإمام أحمد)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 4 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_taymiyyah" data-book-search="مجموع الفتاوى (المجلدان 29 و 30 - كتاب البيوع، العقود، الشروط، والربا) شيخ الإسلام ابن تيمية (ت 728 هـ) الأصل في العقود الإباحة · إبطال حيل الربا · بيع المعدوم · تصحيح شروط التجار · المقاصة أعظم وأشمل تحرير فقهي لقواعد المعاملات والعقود والشروط في التاريخ الإسلامي. قرر فيه حرية العقود ما لم تخالف نصاً، وأبطل الحيل الربوية وبيع العينة، وأصّل لقواعد المقاصة وحوالة الديون.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #4
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلدان 29 و 30 (أكثر من 1000 صفحة)
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/7289/13768" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 مجموع الفتاوى (المجلدان 29 و 30 - كتاب البيوع، العقود، الشروط، والربا) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: شيخ الإسلام ابن تيمية (ت 728 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">الأصل في العقود الإباحة · إبطال حيل الربا · بيع المعدوم · تصحيح شروط التجار · المقاصة</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    أعظم وأشمل تحرير فقهي لقواعد المعاملات والعقود والشروط في التاريخ الإسلامي. قرر فيه حرية العقود ما لم تخالف نصاً، وأبطل الحيل الربوية وبيع العينة، وأصّل لقواعد المقاصة وحوالة الديون.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">العمدة الأولى والأساس الذي انطلقت منه الفتاوى الاقتصادية المعاصرة وهيئات الرقابة الشرعية.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/7289/13768" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (مجموع الفتاوى - المجلد 29)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 5 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_taymiyyah" data-book-search="الحسبة في الإسلام (وظيفة ولاية السوق ومراقبة المكاييل ومنع الاحتكار) شيخ الإسلام ابن تيمية (ت 728 هـ) مراقبة الأسواق · منع الغش والتدليس · التسعير العادل الجبري · تحريم التواطؤ الاحتكاري تنظيم السوق التجاري، واجبات ولي الأمر في مراقبة الموازين والمكاييل ومنع الغش، وبيان ضوابط التسعير العادل عند احتكار السلع والأقوات.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #5
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 كتاب مفرد محقق
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/21703" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 الحسبة في الإسلام (وظيفة ولاية السوق ومراقبة المكاييل ومنع الاحتكار) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: شيخ الإسلام ابن تيمية (ت 728 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">مراقبة الأسواق · منع الغش والتدليس · التسعير العادل الجبري · تحريم التواطؤ الاحتكاري</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    تنظيم السوق التجاري، واجبات ولي الأمر في مراقبة الموازين والمكاييل ومنع الغش، وبيان ضوابط التسعير العادل عند احتكار السلع والأقوات.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المرجع الكلاسيكي الأول في الاقتصاد الكلي وتنظيم الأسواق الحرة في الإسلام.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/21703" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (الحسبة في الإسلام)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 6 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_taymiyyah" data-book-search="القواعد النورانية الفقهية (كتاب المعاملات المالية والعقود) شيخ الإسلام ابن تيمية (ت 728 هـ) القواعد الكلية · استحقاق الربح بالضمان والعمل · نفي الغرر · بيع العربون تأصيل القواعد الكلية الجامعة للمعاملات المالية، وتفصيل قاعدة (الغنم بالغرم والخراج بالضمان)، وصحة بيع العربون.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #6
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 مجلد محقق
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/7288" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 القواعد النورانية الفقهية (كتاب المعاملات المالية والعقود) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: شيخ الإسلام ابن تيمية (ت 728 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">القواعد الكلية · استحقاق الربح بالضمان والعمل · نفي الغرر · بيع العربون</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    تأصيل القواعد الكلية الجامعة للمعاملات المالية، وتفصيل قاعدة (الغنم بالغرم والخراج بالضمان)، وصحة بيع العربون.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">تأصيل نظريات العقد والضمان في الفقه الإسلامي المقارن.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/7288" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (القواعد النورانية الفقهية)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 7 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_al_qayyim" data-book-search="إعلام الموقعين عن رب العالمين (أبواب الربا، الحيل، وعقود البيوع) الإمام شمس الدين ابن قيم الجوزية (ت 751 هـ) المقاصد والمعاني · إبطال حيل الربا · عقد الاستصناع والمقاولات · التورق المنضبط تأسيس قاعدة أن العبرة في العقود بالمقاصد والمعاني لا بالألفاظ والمباني، وكشف وإبطال حيل الربا كبيع العينة، وإثبات صحة عقد الاستصناع والصناعة بالطلب.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #7
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلدات 3 و 4
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/21609" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 إعلام الموقعين عن رب العالمين (أبواب الربا، الحيل، وعقود البيوع) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام شمس الدين ابن قيم الجوزية (ت 751 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">المقاصد والمعاني · إبطال حيل الربا · عقد الاستصناع والمقاولات · التورق المنضبط</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    تأسيس قاعدة أن العبرة في العقود بالمقاصد والمعاني لا بالألفاظ والمباني، وكشف وإبطال حيل الربا كبيع العينة، وإثبات صحة عقد الاستصناع والصناعة بالطلب.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المرجع الفقهي الأهم في فقه المقاصد المالية ومكافحة التحايل المالي.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/21609" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (إعلام الموقعين)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 8 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_al_qayyim" data-book-search="زاد المعاد في هدي خير العباد (المجلد الخامس - فصول هَديه ﷺ في البيوع) الإمام شمس الدين ابن قيم الجوزية (ت 751 هـ) المعاملات النبوية · وضع الجوائح والتلف · السمسرة · خيار العيب · تضمين الصناع بيان شامل للتطبيقات النبوية في البيوع، ووجوب وضع الجوائح وإسقاط الثمن عن المشتري عند تلف البضاعة قبل التسليم، وضوابط أجور السمسرة.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #8
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلد الخامس
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/7440/1715" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 زاد المعاد في هدي خير العباد (المجلد الخامس - فصول هَديه ﷺ في البيوع) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام شمس الدين ابن قيم الجوزية (ت 751 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">المعاملات النبوية · وضع الجوائح والتلف · السمسرة · خيار العيب · تضمين الصناع</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    بيان شامل للتطبيقات النبوية في البيوع، ووجوب وضع الجوائح وإسقاط الثمن عن المشتري عند تلف البضاعة قبل التسليم، وضوابط أجور السمسرة.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">التطبيق العملي للسنة النبوية الشريفة في إدارة التجارة والأعمال.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/7440/1715" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (زاد المعاد - المجلد الخامس)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 9 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_al_qayyim" data-book-search="إغاثة اللهفان من مصائد الشيطان (أبواب الحيل في المعاملات) الإمام شمس الدين ابن قيم الجوزية (ت 751 هـ) مفسدات العقود · التحذير من التلاعب بالألفاظ · حماية المعاملات من الفساد تفصيل دقيق لكيفية حماية العقود والمعاملات التجارية من الشبهات والربا الخفي.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #9
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلد الثاني
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/21604" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 إغاثة اللهفان من مصائد الشيطان (أبواب الحيل في المعاملات) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام شمس الدين ابن قيم الجوزية (ت 751 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">مفسدات العقود · التحذير من التلاعب بالألفاظ · حماية المعاملات من الفساد</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    تفصيل دقيق لكيفية حماية العقود والمعاملات التجارية من الشبهات والربا الخفي.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">حماية النزاهة المالية للمؤسسات والشركات.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/21604" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (إغاثة اللهفان)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 10 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_nawawi" data-book-search="المجموع شرح المهذب (المجلدات 9 و 10 و 11 - كتاب البيوع والربا والسلم) الإمام محيي الدين يحيى بن شرف النووي (ت 676 هـ) أركان البيع · نفي الغرر والجهالة · البيع بالمعاطاة · السلم · الإجارة · الشركات الموسوعة الفقهية المقارنة الكبرى. تفصيل دقيق لشروط العوضين، نفي الغرر، صحة البيع بالمعاطاة والبيع الآلي ونقاط البيع السريعة، وأحكام الإجارة والجعالة والشركات.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #10
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلدات 9 و 10 و 11 (أكثر من 1500 صفحة)
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/2186/3890" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 المجموع شرح المهذب (المجلدات 9 و 10 و 11 - كتاب البيوع والربا والسلم) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام محيي الدين يحيى بن شرف النووي (ت 676 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">أركان البيع · نفي الغرر والجهالة · البيع بالمعاطاة · السلم · الإجارة · الشركات</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    الموسوعة الفقهية المقارنة الكبرى. تفصيل دقيق لشروط العوضين، نفي الغرر، صحة البيع بالمعاطاة والبيع الآلي ونقاط البيع السريعة، وأحكام الإجارة والجعالة والشركات.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">أوسع مرجع فقهي مقارن بين المذاهب الأربعة في تحرير شروط البيوع والربا.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/2186/3890" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (المجموع للنووي - المجلد التاسع)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 11 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_nawawi" data-book-search="منهاج الطالبين وعمدة المفتين (قسم المعاملات المالية والبيوع) الإمام محيي الدين يحيى بن شرف النووي (ت 676 هـ) خيارات البيع · الرهن · الضمان · الحوالة · الشفعة · القراض المتن الفقهي الأكثر ضبطاً واعتماداً في تحرير مسائل البيوع، وضبط خيار الرؤية وخيار العيب، وتوثيق الديون بالرهن والضمان.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #11
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 متن فقهي عمدة
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/11818" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 منهاج الطالبين وعمدة المفتين (قسم المعاملات المالية والبيوع) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام محيي الدين يحيى بن شرف النووي (ت 676 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">خيارات البيع · الرهن · الضمان · الحوالة · الشفعة · القراض</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    المتن الفقهي الأكثر ضبطاً واعتماداً في تحرير مسائل البيوع، وضبط خيار الرؤية وخيار العيب، وتوثيق الديون بالرهن والضمان.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المتن المعياري المعتمد لدى المجامع الفقهية في الصياغة القانونية للمعاملات.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/11818" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (منهاج الطالبين)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 12 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_nawawi" data-book-search="شرح صحيح مسلم (كتاب البيوع، المساقاة، والربا) الإمام محيي الدين يحيى بن شرف النووي (ت 676 هـ) أحاديث البيوع · تحريم بيع الحصاة · الربويات الستة · الصرف والتقابض شرح وتأصيل الأحاديث النبوية الجامعة في تحريم الغرر والربا والنجش والاحتكار، واشتراط التقابض في صرف العملات والذهب.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #12
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلدان 10 و 11
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/1711/4060" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 شرح صحيح مسلم (كتاب البيوع، المساقاة، والربا) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام محيي الدين يحيى بن شرف النووي (ت 676 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">أحاديث البيوع · تحريم بيع الحصاة · الربويات الستة · الصرف والتقابض</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    شرح وتأصيل الأحاديث النبوية الجامعة في تحريم الغرر والربا والنجش والاحتكار، واشتراط التقابض في صرف العملات والذهب.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">تأصيل فقه الحديث في المعاملات المالية المعاصرة.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/1711/4060" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (شرح صحيح مسلم للنووي)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 13 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_dhahabi" data-book-search="كتاب الكبائر (كبائر المعاملات، الربا، الغش، والتطفيف) الإمام الحافظ شمس الدين الذهبي (ت 748 هـ) أكل الربا · التطفيف في الميزان · اليمين الغموس لترويج السلع · الغش وتزوير المنشأ بيان الكبائر والموبقات المالية: التطفيف في الميزان والكيل، الغش في بلد المنشأ والمواصفات، واليمين الكاذبة، والرشوة وأكل أموال الناس بالباطل.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #13
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 كتاب مفرد محقق
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/10543" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 كتاب الكبائر (كبائر المعاملات، الربا، الغش، والتطفيف) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام الحافظ شمس الدين الذهبي (ت 748 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">أكل الربا · التطفيف في الميزان · اليمين الغموس لترويج السلع · الغش وتزوير المنشأ</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    بيان الكبائر والموبقات المالية: التطفيف في الميزان والكيل، الغش في بلد المنشأ والمواصفات، واليمين الكاذبة، والرشوة وأكل أموال الناس بالباطل.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">التحذير والضبط الرادع للجرائم المالية والغش التجاري.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/10543" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (كتاب الكبائر للذهبي)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 14 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="muhammad_bin_abdulwahhab" data-book-search="مجموع مؤلفات الشيخ (القسم الثاني: الفقه - كتاب البيوع والديون) الإمام الشيخ محمد بن عبد الوهاب (ت 1206 هـ) شروط البيع · تحريم الربا · توثيق الديون · الشفعة · منع الاحتكار تحرير أحكام البيوع والربا والديون بالسند والدليل من الكتاب والسنة، والتأكيد على وجوب كتابة الديون والصدق في الأسعار.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #14
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 القسم الثاني (الفقه)
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/8381" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 مجموع مؤلفات الشيخ (القسم الثاني: الفقه - كتاب البيوع والديون) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام الشيخ محمد بن عبد الوهاب (ت 1206 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">شروط البيع · تحريم الربا · توثيق الديون · الشفعة · منع الاحتكار</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    تحرير أحكام البيوع والربا والديون بالسند والدليل من الكتاب والسنة، والتأكيد على وجوب كتابة الديون والصدق في الأسعار.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المنهج الفقهي الأصيل المجرد بالدليل الشرعي المباشر.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/8381" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (مؤلفات الشيخ محمد بن عبد الوهاب)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 15 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="muhammad_bin_abdulwahhab" data-book-search="مختصر الإنصاف والشرح الكبير في الفقه (قسم المعاملات) الإمام الشيخ محمد بن عبد الوهاب (ت 1206 هـ) تهذيب الأحكام · فقه المعاملات الحنبلية المحررة تهذيب وتلخيص أدلة الراجح في الخلاف الفقهي في البيوع والإجارات والشركات والرهن.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #15
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 مجلد محقق
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/8382" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 مختصر الإنصاف والشرح الكبير في الفقه (قسم المعاملات) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام الشيخ محمد بن عبد الوهاب (ت 1206 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">تهذيب الأحكام · فقه المعاملات الحنبلية المحررة</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    تهذيب وتلخيص أدلة الراجح في الخلاف الفقهي في البيوع والإجارات والشركات والرهن.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">خلاصة فقه المعاملات المالية المعتمدة.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/8382" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (مختصر الإنصاف والشرح الكبير)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 16 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_saadi" data-book-search="القواعد والأصول الجامعة والفروق والتقاسيم البديعة النافعة في المعاملات العلامة الشيخ عبد الرحمن بن ناصر السعدي (ت 1376 هـ) الأصل في المعاملات الإباحة · العرف مطرد كالمشروط · الخراج بالضمان · التعاقد الإلكتروني تأصيل بديع لقواعد التيسير ورفع الحرج في المعاملات المالية، واعتبار العرف التجاري، وصحة التعاقد بالمراسلات والوسائل الحديثة.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #16
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 كتاب مفرد محقق
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/8122" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 القواعد والأصول الجامعة والفروق والتقاسيم البديعة النافعة في المعاملات ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: العلامة الشيخ عبد الرحمن بن ناصر السعدي (ت 1376 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">الأصل في المعاملات الإباحة · العرف مطرد كالمشروط · الخراج بالضمان · التعاقد الإلكتروني</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    تأصيل بديع لقواعد التيسير ورفع الحرج في المعاملات المالية، واعتبار العرف التجاري، وصحة التعاقد بالمراسلات والوسائل الحديثة.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">أعظم كتاب معاصر في تبسيط وتطبيق القواعد الفقهية على التجارة الحديثة.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/8122" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (القواعد والأصول الجامعة للسعدي)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 17 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_saadi" data-book-search="الفتاوى السعدية في المعاملات المالية المستجدة والشركات العلامة الشيخ عبد الرحمن بن ناصر السعدي (ت 1376 هـ) الأسهم · التأمين التعاوني · الوكالات الحصرية · المضاربة والنسب الشائعة · المعاملات البنكية فتاوى عصرية رائدة في قسمة أرباح الشركات بالنسب المئوية، جواز الوكالات التجارية، وضوابط المعاملات البنكية الخالية من الربا.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #17
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 مجلد الفتاوى (ص 240 - 320)
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/8120" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 الفتاوى السعدية في المعاملات المالية المستجدة والشركات ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: العلامة الشيخ عبد الرحمن بن ناصر السعدي (ت 1376 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">الأسهم · التأمين التعاوني · الوكالات الحصرية · المضاربة والنسب الشائعة · المعاملات البنكية</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    فتاوى عصرية رائدة في قسمة أرباح الشركات بالنسب المئوية، جواز الوكالات التجارية، وضوابط المعاملات البنكية الخالية من الربا.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">الريادة في الإفتاء المالي المعاصر والتيسير المنضبط.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/8120" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (الفتاوى السعدية)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 18 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_saadi" data-book-search="بهجة قلوب الأبرار (شرح جوامع الأخبار في البيوع والتجارة) العلامة الشيخ عبد الرحمن بن ناصر السعدي (ت 1376 هـ) شرح أحاديث التجارة · بركة الصدق · نفي الغرر · بيع ما لا يملك شرح أحاديث البيع المبرور، خيار المجلس، النهي عن الغش، وفضل إنظار المعسرين.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #18
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 مجلد محقق
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/8119" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 بهجة قلوب الأبرار (شرح جوامع الأخبار في البيوع والتجارة) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: العلامة الشيخ عبد الرحمن بن ناصر السعدي (ت 1376 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">شرح أحاديث التجارة · بركة الصدق · نفي الغرر · بيع ما لا يملك</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    شرح أحاديث البيع المبرور، خيار المجلس، النهي عن الغش، وفضل إنظار المعسرين.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">شرح حديثي فقهي تربوي لكل تاجر ورجل أعمال.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/8119" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (بهجة قلوب الأبرار للسعدي)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 19 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_shinqiti" data-book-search="أضواء البيان في إيضاح القرآن بالقرآن (تفسير آيات البيوع والربا والمداينة) العلامة الشيخ محمد الأمين الشنقيطي (ت 1393 هـ) آية المداينة · تحريم الربا والتأمين التجاري · جواز التأمين التعاوني · عقد السَّلَم · المزارعة تحرير فقهي وتفسيري بديع لآيات سورة البقرة في المداينة والربا، وسورة المائدة في العقود، وتفصيل الفارق الجوهري بين التأمين التجاري الربوي والتأمين التعاوني التكافلي.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #19
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلدات 1 و 2 و 3
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/21626" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 أضواء البيان في إيضاح القرآن بالقرآن (تفسير آيات البيوع والربا والمداينة) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: العلامة الشيخ محمد الأمين الشنقيطي (ت 1393 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">آية المداينة · تحريم الربا والتأمين التجاري · جواز التأمين التعاوني · عقد السَّلَم · المزارعة</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    تحرير فقهي وتفسيري بديع لآيات سورة البقرة في المداينة والربا، وسورة المائدة في العقود، وتفصيل الفارق الجوهري بين التأمين التجاري الربوي والتأمين التعاوني التكافلي.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المرجع التفسيري والفقهي الأعمق في استنباط أحكام المعاملات المالية من القرآن الكريم.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/21626" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (أضواء البيان للشنقيطي)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 20 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_albani" data-book-search="سلسلة الأحاديث الصحيحة والضعيفة (موسوعة أحاديث التجارة والبيوع) الإمام محمد ناصر الدين الألباني (ت 1420 هـ) تحقيق أحاديث البيوع · تحريم بيع العينة · خيار العيب · بركة الصدق · نفي الغش التحقيق الحديثي الصارم لأحاديث التجارة والبيوع، وتصحيح أحاديث وضع الجوائح والبركة في الصدق، وتضعيف الأحاديث الواهية في المعاملات.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #20
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 مجلدات متعددة
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/9442" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 سلسلة الأحاديث الصحيحة والضعيفة (موسوعة أحاديث التجارة والبيوع) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام محمد ناصر الدين الألباني (ت 1420 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">تحقيق أحاديث البيوع · تحريم بيع العينة · خيار العيب · بركة الصدق · نفي الغش</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    التحقيق الحديثي الصارم لأحاديث التجارة والبيوع، وتصحيح أحاديث وضع الجوائح والبركة في الصدق، وتضعيف الأحاديث الواهية في المعاملات.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المرجع الحديثي الأول لتمييز الأحاديث الصحيحة من الضعيفة في البيوع.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/9442" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (سلسلة الأحاديث الصحيحة للألباني)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 21 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_albani" data-book-search="إرواء الغليل في تخريج أحاديث منار السبيل (المجلد الخامس - كتاب البيوع) الإمام محمد ناصر الدين الألباني (ت 1420 هـ) تخريج أحاديث العقود · السلم · الرهن · الإجارة · الشركات · الغصب تخريج وتحقيق موسع لكافة أحاديث المعاملات المالية والعقود الفقهية.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #21
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلد الخامس
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/12063" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 إرواء الغليل في تخريج أحاديث منار السبيل (المجلد الخامس - كتاب البيوع) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام محمد ناصر الدين الألباني (ت 1420 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">تخريج أحاديث العقود · السلم · الرهن · الإجارة · الشركات · الغصب</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    تخريج وتحقيق موسع لكافة أحاديث المعاملات المالية والعقود الفقهية.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">الموسوعة التخريجية الكبرى لأحاديث الفقه المالي.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/12063" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (إرواء الغليل - المجلد الخامس)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 22 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_albani" data-book-search="فتاوى المعاملات المالية والاقتصادية للشيخ الألباني الإمام محمد ناصر الدين الألباني (ت 1420 هـ) تحريم التسويق الهرمي والشبكي · أجور الحوالات المصرفية · الصرف والذهب فتاوى معاصرة في إبطال التسويق الشبكي لجمعه بين الربا والغرر، وجواز أخذ أجر مقطوع على خدمات التحويل البنكي.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #22
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 تسجيلات ورسائل محررة
                        </span>
                    </div>
                    <a href="https://alalbany.org" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 فتاوى المعاملات المالية والاقتصادية للشيخ الألباني ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام محمد ناصر الدين الألباني (ت 1420 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">تحريم التسويق الهرمي والشبكي · أجور الحوالات المصرفية · الصرف والذهب</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    فتاوى معاصرة في إبطال التسويق الشبكي لجمعه بين الربا والغرر، وجواز أخذ أجر مقطوع على خدمات التحويل البنكي.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">فتاوى حديثية معاصرة في النوازل الاقتصادية.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://alalbany.org" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: الموقع الرسمي للإمام الألباني (الفتاوى الصوتية والمحررة)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 23 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_baz" data-book-search="مجموع فتاوى ومقالات متنوعة (المجلدان 19 و 20 - البيوع والمعاملات المعاصرة) الإمام عبد العزيز بن باز (ت 1420 هـ) البيع بالتقسيط · خصم الشيكات · التورق · تداول الأسهم النقية · بطاقات الائتمان · الصرافة المرجع الفقهي اليومي الأول لمسائل البيوع المعاصرة. تحريم خصم الشيكات الآجلة (صريح الربا)، تحريم بيع ما لا يملك، وضوابط الاستثمار في الأسهم النقية الخالية من الربا.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #23
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلدان 19 و 20 (أكثر من 900 صفحة)
                        </span>
                    </div>
                    <a href="https://binbaz.org.sa/categories/fiqhi/16" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 مجموع فتاوى ومقالات متنوعة (المجلدان 19 و 20 - البيوع والمعاملات المعاصرة) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام عبد العزيز بن باز (ت 1420 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">البيع بالتقسيط · خصم الشيكات · التورق · تداول الأسهم النقية · بطاقات الائتمان · الصرافة</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    المرجع الفقهي اليومي الأول لمسائل البيوع المعاصرة. تحريم خصم الشيكات الآجلة (صريح الربا)، تحريم بيع ما لا يملك، وضوابط الاستثمار في الأسهم النقية الخالية من الربا.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المرجع الفتوى الأكثر انتشاراً وثقة في العالم الإسلامي في المعاملات التجارية.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://binbaz.org.sa/categories/fiqhi/16" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: الموقع الرسمي لسماحة الشيخ ابن باز (فقه المعاملات المالية)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 24 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_baz" data-book-search="فتاوى نور على الدرب (قسم المعاملات والبيوع والربا والديون) الإمام عبد العزيز بن باز (ت 1420 هـ) أجوبة التجار · قضايا الديون والرهن · إنظار المعسر · الصرف بالموبايل إجابات مباشرة وميسرة لآلاف النوازل والأسئلة التجارية اليومية للتجار والمحاسبين.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #24
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلدات الاقتصادية
                        </span>
                    </div>
                    <a href="https://binbaz.org.sa/fatwas/kind/1" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 فتاوى نور على الدرب (قسم المعاملات والبيوع والربا والديون) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام عبد العزيز بن باز (ت 1420 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">أجوبة التجار · قضايا الديون والرهن · إنظار المعسر · الصرف بالموبايل</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    إجابات مباشرة وميسرة لآلاف النوازل والأسئلة التجارية اليومية للتجار والمحاسبين.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">سهولة التطبيق وحل الإشكالات المالية السريعة.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://binbaz.org.sa/fatwas/kind/1" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: الموقع الرسمي لسماحة الشيخ ابن باز (فتاوى نور على الدرب)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 25 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_uthaymeen" data-book-search="الشرح الممتع على زاد المستقنع (المجلدات 8 و 9 - كتاب البيع والربا والسلم والإجارة) الإمام محمد بن صالح العثيمين (ت 1421 هـ) البيع بالتقسيط · الشرط الجزائي · الكوبونات والديسكاونت · الإيجار المنتهي بالتمليك · الضمان المصنعي أوسع وأوضح شرح تطبيقي معاصر لفقه البيوع. ربط القواعد الفقهية بالمعاملات الاقتصادية الحديثة، وإجازة البيع بالتقسيط، وفصل عقد الإيجار المنتهي بالتمليك، والشرط الجزائي في المقاولات والتوريد.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #25
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلدان 8 و 9 (أكثر من 1100 صفحة)
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/23746/2800" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 الشرح الممتع على زاد المستقنع (المجلدات 8 و 9 - كتاب البيع والربا والسلم والإجارة) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام محمد بن صالح العثيمين (ت 1421 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">البيع بالتقسيط · الشرط الجزائي · الكوبونات والديسكاونت · الإيجار المنتهي بالتمليك · الضمان المصنعي</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    أوسع وأوضح شرح تطبيقي معاصر لفقه البيوع. ربط القواعد الفقهية بالمعاملات الاقتصادية الحديثة، وإجازة البيع بالتقسيط، وفصل عقد الإيجار المنتهي بالتمليك، والشرط الجزائي في المقاولات والتوريد.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المرجع الأسهل والأشمل للفقهاء والمحاسبين ورجال الأعمال في العصر الحديث.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/23746/2800" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (الشرح الممتع للشيخ ابن عثيمين - كتاب البيوع)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 26 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_uthaymeen" data-book-search="فقه المعاملات المالية المعاصرة ومجموع الفتاوى والرسائل (المجلدان 18 و 19) الإمام محمد بن صالح العثيمين (ت 1421 هـ) بطاقات الائتمان · الصرف الإلكتروني · نقاط الولاء المجانية · المقاصة · حماية المستهلك فتاوى محررة بالأدلة المقارنة في النوازل المالية، وتأصيل حكم بطاقات الخصومات المجانية والكفالة المصنعية وخدمات ما بعد البيع.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #26
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلدان 18 و 19
                        </span>
                    </div>
                    <a href="https://ibnothaimeen.net/fatawa" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 فقه المعاملات المالية المعاصرة ومجموع الفتاوى والرسائل (المجلدان 18 و 19) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام محمد بن صالح العثيمين (ت 1421 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">بطاقات الائتمان · الصرف الإلكتروني · نقاط الولاء المجانية · المقاصة · حماية المستهلك</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    فتاوى محررة بالأدلة المقارنة في النوازل المالية، وتأصيل حكم بطاقات الخصومات المجانية والكفالة المصنعية وخدمات ما بعد البيع.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المرجع الأساسي في فقه النوازل التجارية والمصرفية المعاصرة.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://ibnothaimeen.net/fatawa" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: الموقع الرسمي لفضيلة الشيخ ابن عثيمين (فتاوى المعاملات)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 27 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_fawzan" data-book-search="الملخص الفقهي (المجلد الثاني - كتاب المعاملات والبيوع والشركات والديون) معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) شروط البيع · الربا · السلم · القرض والرهن · الإجارة · الوكالة · الشركات الدليل الفقهي الميسر والمحرر لكل مسلم وتاجر ومحاسب في ضبط كافة بنود العقود المالية والديون.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #27
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلد الثاني (قسم المعاملات)
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/11440" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 الملخص الفقهي (المجلد الثاني - كتاب المعاملات والبيوع والشركات والديون) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">شروط البيع · الربا · السلم · القرض والرهن · الإجارة · الوكالة · الشركات</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    الدليل الفقهي الميسر والمحرر لكل مسلم وتاجر ومحاسب في ضبط كافة بنود العقود المالية والديون.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المنهاج المعتمد لتعليم فقه المعاملات في الجامعات والمؤسسات المالية.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/11440" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (الملخص الفقهي للشيخ الفوزان)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 28 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_fawzan" data-book-search="المنتقى من فتاوى الفوزان (فتاوى المعاملات المصرفية والبيوع المعاصرة) معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) التحويلات عبر الموبايل بنك · نقاط البيع pos · المرابحة البنكية · تحريم البيتكوين · الصناديق العقارية فتاوى مصرفية عصرية في اعتبار التحويل الإلكتروني قبضاً حكمياً صحيحاً، ضوابط المرابحة للآمر بالشراء، والتحذير من العملات الرقمية المشفرة والمخاطرة والغرر.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #28
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 مجلدات متعددة
                        </span>
                    </div>
                    <a href="https://alfawzan.af.org.sa/ar/node/2065" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 المنتقى من فتاوى الفوزان (فتاوى المعاملات المصرفية والبيوع المعاصرة) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">التحويلات عبر الموبايل بنك · نقاط البيع POS · المرابحة البنكية · تحريم البيتكوين · الصناديق العقارية</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    فتاوى مصرفية عصرية في اعتبار التحويل الإلكتروني قبضاً حكمياً صحيحاً، ضوابط المرابحة للآمر بالشراء، والتحذير من العملات الرقمية المشفرة والمخاطرة والغرر.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المرجع الفتوى الأحدث لكبار العلماء المعاصرين في المعاملات الرقمية والبنكية.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://alfawzan.af.org.sa/ar/node/2065" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: الموقع الرسمي لمعالي الشيخ صالح الفوزان</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 29 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_fawzan" data-book-search="شرح كتاب البيوع من بلوغ المرام من أدلة الأحكام معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) أحاديث الأحكام المالية · الخيارات · الربا والصرف · السلم · الإجارة شرح حديثي فقهي رصين لأحاديث البيوع وضوابط الحلال والحرام في المكاسب.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #29
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 مجلد محقق
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/37575" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 شرح كتاب البيوع من بلوغ المرام من أدلة الأحكام ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">أحاديث الأحكام المالية · الخيارات · الربا والصرف · السلم · الإجارة</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    شرح حديثي فقهي رصين لأحاديث البيوع وضوابط الحلال والحرام في المكاسب.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">تأصيل أدلة الأحكام في الفقه المالي المعاصر.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/37575" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (شرح بلوغ المرام - كتاب البيوع)</span>
                </a>
            </div>

        </div>
    
                    </div>

                    <div id="no-books-alert" class="p-8 text-center bg-slate-900/60 rounded-2xl border border-slate-800 hidden">
                        <span class="text-4xl block mb-2">🔍</span>
                        <h5 class="text-lg font-black text-slate-300">لا توجد كتب مطابقة للبحث الحالي</h5>
                        <p class="text-xs text-slate-400 mt-1">يرجى مسح كلمة البحث أو اختيار إمام آخر لإظهار بقية المؤلفات.</p>
                    </div>
                    
                

                id="fiqh-sub-maxims" class="space-y-4 hidden">
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
                
                <!-- ════════════════════════════════════════════════════════════ -->
                
                <!-- ════════════════════════════════════════════════════════════ -->
                <!-- SUB-SECTION 6: QURANIC VERSES OF DEBTS & TRANSACTIONS        -->
                <!-- ════════════════════════════════════════════════════════════ -->
                <div id="fiqh-sub-quran" class="space-y-4 hidden">
                    <div class="p-5 bg-emerald-950/40 border-2 border-emerald-500/50 rounded-2xl flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-3.5 py-1 bg-emerald-500/20 text-emerald-300 font-black text-xs rounded-xl border border-emerald-400">
                                    ★ الدستور الإلهي الحاكم للمعاملات والديون
                                </span>
                                <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400">
                                    15 آية قرآنية محكمة ✓
                                </span>
                            </div>
                            <h3 class="text-xl md:text-2xl font-black text-emerald-300 flex items-center gap-2">
                                <i class="fa-solid fa-book-open-reader text-emerald-400"></i>
                                الآيات القرآنية في الديون، القروض، توثيق العقود، إحلال البيع، وتحريم الربا
                            </h3>
                            <p class="text-xs md:text-sm text-slate-200 font-bold mt-1">
                                تشمل: <span class="text-amber-300 font-black">آية المداينة (أطول آية)</span> · <span class="text-sky-300 font-black">آيات إنظار المعسر والرهن</span> · <span class="text-rose-300 font-black">تحريم الربا والرشوة</span> · <span class="text-emerald-300 font-black">ضبط الموازين والمكاييل</span> · <span class="text-purple-300 font-black">التراضي وأداء الأمانات</span>.
                            </p>
                        </div>

                        <!-- Instant Quranic Search Box -->
                        <div class="w-full md:w-80">
                            <label class="text-xs font-bold text-slate-300 block mb-1">🔍 ابحث في نصوص وتوجيهات الآيات:</label>
                            <input type="text" id="quran-search-input" oninput="filterQuranVerses()" placeholder="اكتب للبحث: دين، رهن، ربا، ميزان، تراضي..." class="tool-input text-xs font-bold">
                        </div>
                    </div>

                    <!-- 🎛️ Quranic Topic Filter Bar 🎛️ -->
                    <div class="bg-slate-900/90 p-3.5 rounded-2xl border border-emerald-500/30 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-black text-emerald-400 block">
                            🏷️ تصفية الآيات حسب الموضوع:
                        </span>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn active" data-qcat="all" onclick="setQuranTopic('all', this)">
                            🌟 جميع الآيات القرآنية (15)
                        </button>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn" data-qcat="debts" onclick="setQuranTopic('debts', this)">
                            💳 الديون والمداينة والرهن وإنظار المعسر
                        </button>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn" data-qcat="riba" onclick="setQuranTopic('riba', this)">
                            🚫 إحلال البيع وتحريم الربا والرشوة
                        </button>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn" data-qcat="contracts" onclick="setQuranTopic('contracts', this)">
                            🤝 التراضي والوفاء بالعقود والتكافل
                        </button>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn" data-qcat="weights" onclick="setQuranTopic('weights', this)">
                            ⚖️ إيفاء المكيال والميزان ونفي التطفيف
                        </button>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn" data-qcat="trust" onclick="setQuranTopic('trust', this)">
                            👥 أداء الأمانات والكفاءة في العمل
                        </button>
                    </div>

                    <!-- Verses Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="quran-verses-grid-container">

        <!-- Quranic Verse Card 1 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="debts" data-ayah-search="آية المداينة (أطول آية في القرآن الكريم - دستور التوثيق المالي والديون) ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا إِذَا تَدَايَنتُم بِدَيْنٍ إِلَىٰ أَجَلٍ مُّسَمًّى فَاكْتُبُوهُ ۚ وَلْيَكْتُب بَّيْنَكُمْ كَاتِبٌ بِالْعَدْلِ ۚ وَلَا يَأْبَ كَاتِبٌ أَن يَكْتُبَ كَمَا عَلَّمَهُ اللَّهُ ۚ فَلْيَكْتُبْ وَلْيُمْلِلِ الَّذِي عَلَيْهِ الْحَقُّ وَلْيَتَّقِ اللَّهَ رَبَّهُ وَلَا يَبْخَسْ مِنْهُ شَيْئًا... وَأَشْهِدُوا إِذَا تَبَايَعْتُمْ ۚ وَلَا يُضَارَّ كَاتِبٌ وَلَا شَهِيدٌ﴾ سورة البقرة: الآية 282 التوجيه المحاسبي والتجاري: وجوب وتأكيد توثيق كافة المعاملات الآجلة والفواتير كتابياً، وتحديد الأجل والمقدار بدقة، والعدالة التامة في القيود المحاسبية لقطع النزاع وحفظ الحقوق.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        1
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        آية المداينة (أطول آية في القرآن الكريم - دستور التوثيق المالي والديون)
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 الديون وتوثيق العقود
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة البقرة: الآية 282
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا إِذَا تَدَايَنتُم بِدَيْنٍ إِلَىٰ أَجَلٍ مُّسَمًّى فَاكْتُبُوهُ ۚ وَلْيَكْتُب بَّيْنَكُمْ كَاتِبٌ بِالْعَدْلِ ۚ وَلَا يَأْبَ كَاتِبٌ أَن يَكْتُبَ كَمَا عَلَّمَهُ اللَّهُ ۚ فَلْيَكْتُبْ وَلْيُمْلِلِ الَّذِي عَلَيْهِ الْحَقُّ وَلْيَتَّقِ اللَّهَ رَبَّهُ وَلَا يَبْخَسْ مِنْهُ شَيْئًا... وَأَشْهِدُوا إِذَا تَبَايَعْتُمْ ۚ وَلَا يُضَارَّ كَاتِبٌ وَلَا شَهِيدٌ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: وجوب وتأكيد توثيق كافة المعاملات الآجلة والفواتير كتابياً، وتحديد الأجل والمقدار بدقة، والعدالة التامة في القيود المحاسبية لقطع النزاع وحفظ الحقوق.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 2 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="debts" data-ayah-search="مشروعية الرهن المقبوض في السفر والديون وأداء الأمانات ﴿وَإِن كُنتُمْ عَلَىٰ سَفَرٍ وَلَمْ تَجِدُوا كَاتِبًا فَرِهَانٌ مَّقْبُوضَةٌ ۖ فَإِنْ أَمِنَ بَعْضُكُم بَعْضًا فَلْيُؤَدِّ الَّذِي اؤْتُمِنَ أَمَانَتَهُ وَلْيَتَّقِ اللَّهَ رَبَّهُ ۗ وَلَا تَكْتُمُوا الشَّهَادَةَ ۚ وَمَن يَكْتُمْهَا فَإِنَّهُ آثِمٌ قَلْبُهُ ۗ وَاللَّهُ بِمَا تَعْمَلُونَ عَلِيمٌ﴾ سورة البقرة: الآية 283 التوجيه المحاسبي والتجاري: مشروعية أخذ الرهونات والضمانات العينية لتوثيق الديون عند تعذر التوثيق الكتابي، ووجوب أداء الأمانات والشهادات دون كتمان.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        2
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        مشروعية الرهن المقبوض في السفر والديون وأداء الأمانات
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 الرهن والضمانات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة البقرة: الآية 283
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿وَإِن كُنتُمْ عَلَىٰ سَفَرٍ وَلَمْ تَجِدُوا كَاتِبًا فَرِهَانٌ مَّقْبُوضَةٌ ۖ فَإِنْ أَمِنَ بَعْضُكُم بَعْضًا فَلْيُؤَدِّ الَّذِي اؤْتُمِنَ أَمَانَتَهُ وَلْيَتَّقِ اللَّهَ رَبَّهُ ۗ وَلَا تَكْتُمُوا الشَّهَادَةَ ۚ وَمَن يَكْتُمْهَا فَإِنَّهُ آثِمٌ قَلْبُهُ ۗ وَاللَّهُ بِمَا تَعْمَلُونَ عَلِيمٌ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: مشروعية أخذ الرهونات والضمانات العينية لتوثيق الديون عند تعذر التوثيق الكتابي، ووجوب أداء الأمانات والشهادات دون كتمان.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 3 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="debts" data-ayah-search="وجوب إنظار المعسر والتجاوز عنه بالصدقة والإبراء ﴿وَإِن كَانَ ذُو عُسْرَةٍ فَنَظِرَةٌ إِلَىٰ مَيْسَرَةٍ ۚ وَأَن تَصَدَّقُوا خَيْرٌ لَّكُمْ ۖ إِن كُنتُمْ تَعْلَمُونَ﴾ سورة البقرة: الآية 280 التوجيه المحاسبي والتجاري: وجوب إمهال المدين المعسر شرعاً دون فرض أي غرامات تأخير أو فوائد، واستحباب التنازل عن الدين أو إسقاط جزء منه كصدقة لوجه الله.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        3
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وجوب إنظار المعسر والتجاوز عنه بالصدقة والإبراء
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 إنظار المعسر والتيسير
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة البقرة: الآية 280
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿وَإِن كَانَ ذُو عُسْرَةٍ فَنَظِرَةٌ إِلَىٰ مَيْسَرَةٍ ۚ وَأَن تَصَدَّقُوا خَيْرٌ لَّكُمْ ۖ إِن كُنتُمْ تَعْلَمُونَ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: وجوب إمهال المدين المعسر شرعاً دون فرض أي غرامات تأخير أو فوائد، واستحباب التنازل عن الدين أو إسقاط جزء منه كصدقة لوجه الله.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 4 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="riba" data-ayah-search="إحلال البيع والتجارة وتحريم الربا القاطع ﴿الَّذِينَ يَأْكُلُونَ الرِّبَا لَا يَقُومُونَ إِلَّا كَمَا يَقُومُ الَّذِي يَتَخَبَّطُهُ الشَّيْطَانُ مِنَ الْمَسِّ ۚ ذَٰلِكَ بِأَنَّهُمْ قَالُوا إِنَّمَا الْبَيْعُ مِثْلُ الرِّبَا ۗ وَأَحَلَّ اللَّهُ الْبَيْعَ وَحَرَّمَ الرِّبَا ۚ فَمَن جَاءَهُ مَوْعِظَةٌ مِّن رَّبِّهِ فَانتَهَىٰ فَلَهُ مَا سَلَفَ وَأَمْرُهُ إِلَى اللَّهِ ۖ وَمَنْ عَادَ فَأُولَٰئِكَ أَصْحَابُ النَّارِ ۖ هُمْ فِيهَا خَالِدُونَ﴾ سورة البقرة: الآية 275 التوجيه المحاسبي والتجاري: الأصل الشرعي هو إباحة البيع والتجارة وتحقيق الأرباح العادلة، والتحريم القاطع للربا والزيادات المشروطة على الديون والقروض.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        4
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        إحلال البيع والتجارة وتحريم الربا القاطع
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 إحلال البيع وتحريم الربا
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة البقرة: الآية 275
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿الَّذِينَ يَأْكُلُونَ الرِّبَا لَا يَقُومُونَ إِلَّا كَمَا يَقُومُ الَّذِي يَتَخَبَّطُهُ الشَّيْطَانُ مِنَ الْمَسِّ ۚ ذَٰلِكَ بِأَنَّهُمْ قَالُوا إِنَّمَا الْبَيْعُ مِثْلُ الرِّبَا ۗ وَأَحَلَّ اللَّهُ الْبَيْعَ وَحَرَّمَ الرِّبَا ۚ فَمَن جَاءَهُ مَوْعِظَةٌ مِّن رَّبِّهِ فَانتَهَىٰ فَلَهُ مَا سَلَفَ وَأَمْرُهُ إِلَى اللَّهِ ۖ وَمَنْ عَادَ فَأُولَٰئِكَ أَصْحَابُ النَّارِ ۖ هُمْ فِيهَا خَالِدُونَ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: الأصل الشرعي هو إباحة البيع والتجارة وتحقيق الأرباح العادلة، والتحريم القاطع للربا والزيادات المشروطة على الديون والقروض.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 5 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="riba" data-ayah-search="محق الربا وبركة الصدقات ونماء المال الحلال ﴿يَمْحَقُ اللَّهُ الرِّبَا وَيُرْبِي الصَّدَقَاتِ ۗ وَاللَّهُ لَا يُحِبُّ كُلَّ كَفَّارٍ أَثِيمٍ﴾ سورة البقرة: الآية 276 التوجيه المحاسبي والتجاري: زوال البركة والهلاك الحتمي للأموال الربوية وإن كثرت أرقامها، ونماء وبركة التجارة الصادقة المصحوبة بإخراج الزكاة والصدقات.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        5
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        محق الربا وبركة الصدقات ونماء المال الحلال
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 محق الربا ونماء الصدقات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة البقرة: الآية 276
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿يَمْحَقُ اللَّهُ الرِّبَا وَيُرْبِي الصَّدَقَاتِ ۗ وَاللَّهُ لَا يُحِبُّ كُلَّ كَفَّارٍ أَثِيمٍ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: زوال البركة والهلاك الحتمي للأموال الربوية وإن كثرت أرقامها، ونماء وبركة التجارة الصادقة المصحوبة بإخراج الزكاة والصدقات.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 6 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="riba" data-ayah-search="إعلان الحرب الإلهية على من لم يذر الربا وحق استرداد رأس المال فقط ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا اتَّقُوا اللَّهَ وَذَرُوا مَا بَقِيَ مِنَ الرِّبَا إِن كُنتُم مُّؤْمِنِينَ * فَإِن لَّمْ تَفْعَلُوا فَأْذَنُوا بِحَرْبٍ مِّنَ اللَّهِ وَرَسُولِهِ ۖ وَإِن تُبْتُمْ فَلَكُمْ رُءُوسُ أَمْوَالِكُمْ لَا تَظْلِمُونَ وَلَا تُظْلَمُونَ﴾ سورة البقرة: الآيات 278-279 التوجيه المحاسبي والتجاري: تحريم تقاضي أي زيادة فوق مبلغ الدين الأصلي، وحق الدائن محصور في استرداد رأس ماله الفعلي فقط دون ظلم أو مظلمة.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        6
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        إعلان الحرب الإلهية على من لم يذر الربا وحق استرداد رأس المال فقط
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 وجوب ترك الربا
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة البقرة: الآيات 278-279
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا اتَّقُوا اللَّهَ وَذَرُوا مَا بَقِيَ مِنَ الرِّبَا إِن كُنتُم مُّؤْمِنِينَ * فَإِن لَّمْ تَفْعَلُوا فَأْذَنُوا بِحَرْبٍ مِّنَ اللَّهِ وَرَسُولِهِ ۖ وَإِن تُبْتُمْ فَلَكُمْ رُءُوسُ أَمْوَالِكُمْ لَا تَظْلِمُونَ وَلَا تُظْلَمُونَ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: تحريم تقاضي أي زيادة فوق مبلغ الدين الأصلي، وحق الدائن محصور في استرداد رأس ماله الفعلي فقط دون ظلم أو مظلمة.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 7 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="contracts" data-ayah-search="ركن التراضي في التجارة وتحريم أكل أموال الناس بالباطل ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا لَا تَأْكُلُوا أَمْوَالَكُم بَيْنَكُم بِالْبَاطِلِ إِلَّا أَن تَكُونَ تِجَارَةً عَن تَرَاضٍ مِّنكُمْ ۚ وَلَا تَقْتُلُوا أَنفُسَكُمْ ۚ إِنَّ اللَّهَ كَانَ بِكُمْ رَحِيمًا﴾ سورة النساء: الآية 29 التوجيه المحاسبي والتجاري: اشتراط التراضي التام ونفي الإكراه والغرر في عقود البيع، وتحريم كافة صور أكل أموال الناس بالحيل والسرقة والغش والاحتيال.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        7
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        ركن التراضي في التجارة وتحريم أكل أموال الناس بالباطل
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🤝 التراضي والعقود
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة النساء: الآية 29
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا لَا تَأْكُلُوا أَمْوَالَكُم بَيْنَكُم بِالْبَاطِلِ إِلَّا أَن تَكُونَ تِجَارَةً عَن تَرَاضٍ مِّنكُمْ ۚ وَلَا تَقْتُلُوا أَنفُسَكُمْ ۚ إِنَّ اللَّهَ كَانَ بِكُمْ رَحِيمًا﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: اشتراط التراضي التام ونفي الإكراه والغرر في عقود البيع، وتحريم كافة صور أكل أموال الناس بالحيل والسرقة والغش والاحتيال.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 8 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="contracts" data-ayah-search="الأمر الإلهي القاطع بالوفاء بكافة العقود والشروط ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا أَوْفُوا بِالْعُقُودِ ۚ أُحِلَّتْ لَكُم بَهِيمَةُ الْأَنْعَامِ إِلَّا مَا يُتْلَىٰ عَلَيْكُمْ...﴾ سورة المائدة: الآية 1 التوجيه المحاسبي والتجاري: الإلزام الشرعي التام بالوفاء بكافة بنود العقود المبرمة (عقود التوريد، المقاولات، الشراكات، والتوكيلات التجارية).">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        8
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        الأمر الإلهي القاطع بالوفاء بكافة العقود والشروط
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🤝 الوفاء بالعقود
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة المائدة: الآية 1
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا أَوْفُوا بِالْعُقُودِ ۚ أُحِلَّتْ لَكُم بَهِيمَةُ الْأَنْعَامِ إِلَّا مَا يُتْلَىٰ عَلَيْكُمْ...﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: الإلزام الشرعي التام بالوفاء بكافة بنود العقود المبرمة (عقود التوريد، المقاولات، الشراكات، والتوكيلات التجارية).
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 9 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="weights" data-ayah-search="إيفاء الكيل والميزان بالقسطاس المستقيم ﴿وَأَوْفُوا الْكَيْلَ إِذَا كِلْتُمْ وَزِنُوا بِالْقِسْطَاسِ الْمُسْتَقِيمِ ۚ ذَٰلِكَ خَيْرٌ وَأَحْسَنُ تَأْوِيلًا﴾ سورة الإسراء: الآية 35 التوجيه المحاسبي والتجاري: الأمر الرباني بالمعايرة الدقيقة للموازين الرقمية والمكاييل وتطابق وزن بضائع التسليح والمخازن مع الفواتير الصادرة.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        9
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        إيفاء الكيل والميزان بالقسطاس المستقيم
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        ⚖️ ضبط الموازين والمكاييل
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة الإسراء: الآية 35
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿وَأَوْفُوا الْكَيْلَ إِذَا كِلْتُمْ وَزِنُوا بِالْقِسْطَاسِ الْمُسْتَقِيمِ ۚ ذَٰلِكَ خَيْرٌ وَأَحْسَنُ تَأْوِيلًا﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: الأمر الرباني بالمعايرة الدقيقة للموازين الرقمية والمكاييل وتطابق وزن بضائع التسليح والمخازن مع الفواتير الصادرة.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 10 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="weights" data-ayah-search="الوعيد الشديد والهلاك للمطففين في الكيل والوزن ﴿وَيْلٌ لِّلْمُطَفِّفِينَ * الَّذِينَ إِذَا اكْتَالُوا عَلَى النَّاسِ يَسْتَوْفُونَ * وَإِذَا كَالُوهُمْ أَو وَّزَنُوهُمْ يُخْسِرُونَ * أَلَا يَظُنُّ أُولَٰئِكَ أَنَّهُم مَّبْعُوثُونَ * لِيَوْمٍ عَظِيمٍ﴾ سورة المطففين: الآيات 1-5 التوجيه المحاسبي والتجاري: تحريم إنقاص أوزان الحديد أو الخرسانة أو أمتار الأقمشة والأراضي عند البيع للزبائن، وبيان أن التطفيف من كبائر الذنوب.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        10
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        الوعيد الشديد والهلاك للمطففين في الكيل والوزن
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        ⚖️ تحريم التطفيف
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة المطففين: الآيات 1-5
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿وَيْلٌ لِّلْمُطَفِّفِينَ * الَّذِينَ إِذَا اكْتَالُوا عَلَى النَّاسِ يَسْتَوْفُونَ * وَإِذَا كَالُوهُمْ أَو وَّزَنُوهُمْ يُخْسِرُونَ * أَلَا يَظُنُّ أُولَٰئِكَ أَنَّهُم مَّبْعُوثُونَ * لِيَوْمٍ عَظِيمٍ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: تحريم إنقاص أوزان الحديد أو الخرسانة أو أمتار الأقمشة والأراضي عند البيع للزبائن، وبيان أن التطفيف من كبائر الذنوب.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 11 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="trust" data-ayah-search="وجوب أداء الأمانات إلى أهلها والعدل في المعاملات ﴿إِنَّ اللَّهَ يَأْمُرُكُمْ أَن تُؤَدُّوا الْأَمَانَاتِ إِلَىٰ أَهْلِهَا وَإِذَا حَكَمْتُم بَيْنَ النَّاسِ أَن تَحْكُمُوا بِالْعَدْلِ ۚ إِنَّ اللَّهَ نِعِمَّا يَعِظُكُم بِهِ ۗ إِنَّ اللَّهَ كَانَ سَمِيعًا بَصِيرًا﴾ سورة النساء: الآية 58 التوجيه المحاسبي والتجاري: وجوب رد الودائع، حفظ أموال الشركاء، أداء مستحقات الموردين والعملاء، وإقامة العدل في الحسابات.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        11
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وجوب أداء الأمانات إلى أهلها والعدل في المعاملات
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-purple-500/20 text-purple-300 border-purple-400/40">
                        🛡️ أداء الأمانات والعدل
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة النساء: الآية 58
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿إِنَّ اللَّهَ يَأْمُرُكُمْ أَن تُؤَدُّوا الْأَمَانَاتِ إِلَىٰ أَهْلِهَا وَإِذَا حَكَمْتُم بَيْنَ النَّاسِ أَن تَحْكُمُوا بِالْعَدْلِ ۚ إِنَّ اللَّهَ نِعِمَّا يَعِظُكُم بِهِ ۗ إِنَّ اللَّهَ كَانَ سَمِيعًا بَصِيرًا﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: وجوب رد الودائع، حفظ أموال الشركاء، أداء مستحقات الموردين والعملاء، وإقامة العدل في الحسابات.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 12 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="riba" data-ayah-search="تحريم الرشوة والوساطة غير المشروعة لأكل أموال الناس ﴿وَلَا تَأْكُلُوا أَمْوَالَكُم بَيْنَكُم بِالْبَاطِلِ وَتُدْلُوا بِهَا إِلَى الْحُكَّامِ لِتَأْكُلُوا فَرِيقًا مِّنْ أَمْوَالِ النَّاسِ بِالْإِثْمِ وَأَنتُمْ تَعْلَمُونَ﴾ سورة البقرة: الآية 188 التوجيه المحاسبي والتجاري: تحريم دفع الرشاوى والعمولات الخفية للمسؤولين أو موظفي المشتريات لكسب المناقصات أو تجاوز اللوائح بالباطل.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        12
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم الرشوة والوساطة غير المشروعة لأكل أموال الناس
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 تحريم الرشوة والفساد
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة البقرة: الآية 188
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿وَلَا تَأْكُلُوا أَمْوَالَكُم بَيْنَكُم بِالْبَاطِلِ وَتُدْلُوا بِهَا إِلَى الْحُكَّامِ لِتَأْكُلُوا فَرِيقًا مِّنْ أَمْوَالِ النَّاسِ بِالْإِثْمِ وَأَنتُمْ تَعْلَمُونَ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: تحريم دفع الرشاوى والعمولات الخفية للمسؤولين أو موظفي المشتريات لكسب المناقصات أو تجاوز اللوائح بالباطل.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 13 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="contracts" data-ayah-search="التعاون على البر والتقوى في الشراكات والتأمين التكافلي ﴿وَتَعَاوَنُوا عَلَى الْبِرِّ وَالتَّقْوَىٰ ۖ وَلَا تَعَاوَنُوا عَلَى الْإِثْمِ وَالْعُدْوَانِ ۚ وَاتَّقُوا اللَّهَ ۖ إِنَّ اللَّهَ شَدِيدُ الْعِقَابِ﴾ سورة المائدة: الآية 2 التوجيه المحاسبي والتجاري: مشروعية وتأصيل عقود التأمين التعاوني الإسلامي وصناديق التكافل والشركات الإنتاجية، وتحريم الشراكة في الحرام.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        13
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        التعاون على البر والتقوى في الشراكات والتأمين التكافلي
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🤝 التعاون والتكافل التجاري
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة المائدة: الآية 2
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿وَتَعَاوَنُوا عَلَى الْبِرِّ وَالتَّقْوَىٰ ۖ وَلَا تَعَاوَنُوا عَلَى الْإِثْمِ وَالْعُدْوَانِ ۚ وَاتَّقُوا اللَّهَ ۖ إِنَّ اللَّهَ شَدِيدُ الْعِقَابِ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: مشروعية وتأصيل عقود التأمين التعاوني الإسلامي وصناديق التكافل والشركات الإنتاجية، وتحريم الشراكة في الحرام.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 14 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="weights" data-ayah-search="إتمام الكيل والعدل وعدم بخس الناس أشياءهم ﴿وَيَا قَوْمِ أَوْفُوا الْمِكْيَالَ وَالْمِيزَانَ بِالْقِسْطِ ۖ وَلَا تَبْخَسُوا النَّاسَ أَشْيَاءَهُمْ وَلَا تَعْثَوْا فِي الْأَرْضِ مُفْسِدِينَ * بَقِيَّتُ اللَّهِ خَيْرٌ لَّكُمْ إِن كُنتُم مُّؤْمِنِينَ ۚ وَمَا أَنَا عَلَيْكُم بِحَفِيظٍ﴾ سورة هود: الآيات 85-86 التوجيه المحاسبي والتجاري: تحريم التقليل من قيمة وجودة سلع الموردين أو البائعين ظلماً، والتأكيد على أن الربح الحلال القليل خير وأبقى من الحرام الكثير.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        14
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        إتمام الكيل والعدل وعدم بخس الناس أشياءهم
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        ⚖️ عدم بخس سلع الناس
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة هود: الآيات 85-86
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿وَيَا قَوْمِ أَوْفُوا الْمِكْيَالَ وَالْمِيزَانَ بِالْقِسْطِ ۖ وَلَا تَبْخَسُوا النَّاسَ أَشْيَاءَهُمْ وَلَا تَعْثَوْا فِي الْأَرْضِ مُفْسِدِينَ * بَقِيَّتُ اللَّهِ خَيْرٌ لَّكُمْ إِن كُنتُم مُّؤْمِنِينَ ۚ وَمَا أَنَا عَلَيْكُم بِحَفِيظٍ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: تحريم التقليل من قيمة وجودة سلع الموردين أو البائعين ظلماً، والتأكيد على أن الربح الحلال القليل خير وأبقى من الحرام الكثير.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 15 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="trust" data-ayah-search="معيار الكفاءة والأمانة في التوظيف وعقود العمل والمقاولات ﴿قَالَتْ إِحْدَاهُمَا يَا أَبَتِ اسْتَأْجِرْهُ ۖ إِنَّ خَيْرَ مَنِ اسْتَأْجَرْتَ الْقَوِيُّ الْأَمِينُ﴾ سورة القصص: الآية 26 التوجيه المحاسبي والتجاري: ركنا النجاح والبركة في إدارة الأعمال والمؤسسات: الكفاءة الفنية والمهنية (القوة) والصدق والنزاهة المحاسبية (الأمانة).">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        15
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        معيار الكفاءة والأمانة في التوظيف وعقود العمل والمقاولات
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-purple-500/20 text-purple-300 border-purple-400/40">
                        👥 الأمانة والكفاءة في العمل
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة القصص: الآية 26
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿قَالَتْ إِحْدَاهُمَا يَا أَبَتِ اسْتَأْجِرْهُ ۖ إِنَّ خَيْرَ مَنِ اسْتَأْجَرْتَ الْقَوِيُّ الْأَمِينُ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: ركنا النجاح والبركة في إدارة الأعمال والمؤسسات: الكفاءة الفنية والمهنية (القوة) والصدق والنزاهة المحاسبية (الأمانة).
                </span>
            </div>

        </div>
    
                    </div>

                    <div id="no-quran-alert" class="p-8 text-center bg-slate-900/60 rounded-2xl border border-slate-800 hidden">
                        <span class="text-4xl block mb-2">🔍</span>
                        <h5 class="text-lg font-black text-slate-300">لا توجد آيات مطابقة للبحث الحالي</h5>
                        <p class="text-xs text-slate-400 mt-1">يرجى مسح كلمة البحث أو اختيار تصنيف آخر لإظهار بقية الآيات.</p>
                    </div>
                </div>

                <!-- SUB-SECTION 5: AUTHENTIC HADITHS OF DEBTS & ALL TRANSACTIONS -->
                <!-- ════════════════════════════════════════════════════════════ -->
                <div id="fiqh-sub-hadiths" class="space-y-4 hidden">
                    <div class="p-5 bg-amber-500/15 border-2 border-amber-500/40 rounded-2xl flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 font-black text-xs rounded-xl border border-amber-400">
                                    ★ الموسوعة النبوية الشاملة للمعاملات المالية
                                </span>
                                <span class="px-3.5 py-1 bg-emerald-500/20 text-emerald-300 font-mono font-black text-xs rounded-xl border border-emerald-400">
                                    32 حديثاً صحيحاً 100% ✓
                                </span>
                            </div>
                            <h3 class="text-xl md:text-2xl font-black text-amber-300 flex items-center gap-2">
                                <i class="fa-solid fa-scroll text-amber-400"></i>
                                أحاديث البيوع، الديون، القروض، الرهن، والأجور والمعاملات المالية (الصحيحة فقط)
                            </h3>
                            <p class="text-xs md:text-sm text-slate-200 font-bold mt-1">
                                تشمل: <span class="text-sky-300 font-black">أحكام الديون وسدادها</span> · <span class="text-emerald-300 font-black">إنظار المعسرين والرهن</span> · <span class="text-fuchsia-300 font-black">أجور العمال والشركات</span> · <span class="text-amber-300 font-black">البيوع والصدق والتسعير</span> · <span class="text-rose-300 font-black">تحريم الربا والرشوة والغلول</span>.
                            </p>
                        </div>

                        <!-- Instant Hadith Search Box -->
                        <div class="w-full md:w-80">
                            <label class="text-xs font-bold text-slate-300 block mb-1">🔍 ابحث في نصوص وتخريج الأحاديث:</label>
                            <input type="text" id="hadith-search-input" oninput="filterHadiths()" placeholder="اكتب للبحث: دين، رهن، أجر، ربا، رشوة..." class="tool-input text-xs font-bold">
                        </div>
                    </div>

                    <!-- 🎛️ Hadiths Category Filter Bar 🎛️ -->
                    <div class="bg-slate-900/90 p-3.5 rounded-2xl border border-amber-500/30 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-black text-amber-400 block">
                            🏷️ تصفية الأحاديث حسب الباب المالي:
                        </span>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn active" data-hcat="all" onclick="setHadithCategory('all', this)">
                            🌟 جميع الأحاديث النبوية (32)
                        </button>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn" data-hcat="debts" onclick="setHadithCategory('debts', this)">
                            💳 أحاديث الديون والقروض والرهن والإنظار (10)
                        </button>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn" data-hcat="sales" onclick="setHadithCategory('sales', this)">
                            🛒 أحاديث البيوع والصدق والتسعير والغش (12)
                        </button>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn" data-hcat="labor" onclick="setHadithCategory('labor', this)">
                            👥 أحاديث الأجور والعمال والشركات (4)
                        </button>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn" data-hcat="prohibitions" onclick="setHadithCategory('prohibitions', this)">
                            🚫 أحاديث تحريم الربا والصرف والرشوة والغلول (6)
                        </button>
                    </div>

                    <!-- Hadiths Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="hadiths-grid-container">

        <!-- Hadith Card 1 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="نية سداد الدين وعون الله للمدين الصادق وعقوبة المتلف «مَنْ أَخَذَ أَمْوَالَ النَّاسِ يُرِيدُ أَدَاءَهَا أَدَّى اللَّهُ عَنْهُ، وَمَنْ أَخَذَ يُرِيدُ إِتْلَافَهَا أَتْلَفَهُ اللَّهُ» صحيح البخاري (2387) النية الصادقة في سداد الديون تستجلب معونة الله وتيسير الرزق، ونية المماطلة وأكل أموال الناس سبب لهلاك المال وصاحبه.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        1
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        نية سداد الدين وعون الله للمدين الصادق وعقوبة المتلف
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنْ أَخَذَ أَمْوَالَ النَّاسِ يُرِيدُ أَدَاءَهَا أَدَّى اللَّهُ عَنْهُ، وَمَنْ أَخَذَ يُرِيدُ إِتْلَافَهَا أَتْلَفَهُ اللَّهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2387)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">النية الصادقة في سداد الديون تستجلب معونة الله وتيسير الرزق، ونية المماطلة وأكل أموال الناس سبب لهلاك المال وصاحبه.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 2 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="تحريم مَطْل الغني والمماطلة في سداد الديون الحالة «مَطْلُ الْغَنِيِّ ظُلْمٌ، وَإِذَا أُتْبِعَ أَحَدُكُمْ عَلَى مَلِيءٍ فَلْيَتَّبِعْ» صحيح البخاري (2287) وصحيح مسلم (1564) تأخير سداد الدين مع القدرة المالية ظلم صريح وكبيرة، وجواز الحوالة المالية على الشخص القادر المليء.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        2
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم مَطْل الغني والمماطلة في سداد الديون الحالة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَطْلُ الْغَنِيِّ ظُلْمٌ، وَإِذَا أُتْبِعَ أَحَدُكُمْ عَلَى مَلِيءٍ فَلْيَتَّبِعْ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2287) وصحيح مسلم (1564)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تأخير سداد الدين مع القدرة المالية ظلم صريح وكبيرة، وجواز الحوالة المالية على الشخص القادر المليء.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 3 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="خطورة الدين وتعلق نفس المؤمن به بعد الموت «نَفْسُ الْمُؤْمِنِ مُعَلَّقَةٌ بِدَيْنِهِ حَتَّى يُقْضَى عَنْهُ» رواه الترمذي (1078) وابن ماجه وصححه الألباني وجوب المبادرة بقضاء الديون وتوثيقها وكتابة الوصية بها لإبراء ذمة المتوفى قبل تقسيم التركة.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        3
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        خطورة الدين وتعلق نفس المؤمن به بعد الموت
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «نَفْسُ الْمُؤْمِنِ مُعَلَّقَةٌ بِدَيْنِهِ حَتَّى يُقْضَى عَنْهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (1078) وابن ماجه وصححه الألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">وجوب المبادرة بقضاء الديون وتوثيقها وكتابة الوصية بها لإبراء ذمة المتوفى قبل تقسيم التركة.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 4 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="الدين لا يسقط حتى بالشهادة في سبيل الله «يُغْفَرُ لِلشَّهِيدِ كُلُّ ذَنْبٍ إِلَّا الدَّيْنَ» صحيح مسلم (1886) حقوق العباد والديون مبنية على المشاحة ولا تسقط إلا بالأداء أو الإبراء من صاحب الحق.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        4
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        الدين لا يسقط حتى بالشهادة في سبيل الله
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمرو بن العاص رضي الله عنهما:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «يُغْفَرُ لِلشَّهِيدِ كُلُّ ذَنْبٍ إِلَّا الدَّيْنَ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح مسلم (1886)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">حقوق العباد والديون مبنية على المشاحة ولا تسقط إلا بالأداء أو الإبراء من صاحب الحق.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 5 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="فضل الوفاء وحسن القضاء والزيادة غير المشروطة عند السداد «إِنَّ خِيَارَكُمْ أَحْسَنُكُمْ قَضَاءً» صحيح البخاري (2305) وصحيح مسلم (1601) استحباب رد الدين بأحسن منه في الوصف أو زيادة طيبة من غير شرط ولا تواطؤ مسبق إكراماً للمقرض.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        5
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        فضل الوفاء وحسن القضاء والزيادة غير المشروطة عند السداد
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «إِنَّ خِيَارَكُمْ أَحْسَنُكُمْ قَضَاءً»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2305) وصحيح مسلم (1601)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">استحباب رد الدين بأحسن منه في الوصف أو زيادة طيبة من غير شرط ولا تواطؤ مسبق إكراماً للمقرض.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 6 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="مشروعية الرهن التجاري في الديون لتوثيق الحقوق «تُوُفِّيَ رَسُولُ اللَّهِ ﷺ وَدِرْعُهُ مَرْهُونَةٌ عِنْدَ يَهُودِيٍّ بِثَلَاثِينَ صَاعًا مِنْ شَعِيرٍ» صحيح البخاري (2916) وصحيح مسلم (1603) جواز رهن الأصول والضمانات العينية لتوثيق الديون والمشتريات الآجلة حفظاً للحقوق المالية.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        6
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        مشروعية الرهن التجاري في الديون لتوثيق الحقوق
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عائشة رضي الله عنها:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «تُوُفِّيَ رَسُولُ اللَّهِ ﷺ وَدِرْعُهُ مَرْهُونَةٌ عِنْدَ يَهُودِيٍّ بِثَلَاثِينَ صَاعًا مِنْ شَعِيرٍ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2916) وصحيح مسلم (1603)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">جواز رهن الأصول والضمانات العينية لتوثيق الديون والمشتريات الآجلة حفظاً للحقوق المالية.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 7 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="فضل إنظار المعسر ومضاعفة أجر الصدقة عن كل يوم تأخير «مَنْ أَنْظَرَ مُعْسِرًا فَلَهُ بِكُلِّ يَوْمٍ مِثْلِهِ صَدَقَةٌ، حَتَّى إِذَا حَلَّ الدَّيْنُ فَأَنْظَرَهُ فَلَهُ بِكُلِّ يَوْمٍ مِثْلَيْهِ صَدَقَةٌ» رواه أحمد (23046) وابن ماجه وصححه الألباني في السلسلة الصحيحة (860) الأجر المضاعف للتاجر الذي يمهل المدين المعسر عن تاريخ الاستحقاق دون احتساب أي فوائد ربوية.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        7
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        فضل إنظار المعسر ومضاعفة أجر الصدقة عن كل يوم تأخير
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن بريدة بن الحصيب رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنْ أَنْظَرَ مُعْسِرًا فَلَهُ بِكُلِّ يَوْمٍ مِثْلِهِ صَدَقَةٌ، حَتَّى إِذَا حَلَّ الدَّيْنُ فَأَنْظَرَهُ فَلَهُ بِكُلِّ يَوْمٍ مِثْلَيْهِ صَدَقَةٌ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أحمد (23046) وابن ماجه وصححه الألباني في السلسلة الصحيحة (860)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">الأجر المضاعف للتاجر الذي يمهل المدين المعسر عن تاريخ الاستحقاق دون احتساب أي فوائد ربوية.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 8 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="تجاوز الله ومغفرته لمن كان يتجاوز عن المعسرين في الأسواق «كَانَ رَجُلٌ يُدَايِنُ النَّاسَ، فَكَانَ يَقُولُ لِفَتَاهُ: إِذَا أَتَيْتَ مُعْسِرًا فَتَجَاوَزْ عَنْهُ، لَعَلَّ اللَّهَ أَنْ يَتَجَاوَزَ عَنَّا، فَلَقِيَ اللَّهَ فَتَجَاوَزَ عَنْهُ» صحيح البخاري (2078) وصحيح مسلم (1562) التيسير على المدينين وإسقاط جزء من الدين أو المسامحة فيه سبب مباشر لمغفرة الله يوم القيامة.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        8
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تجاوز الله ومغفرته لمن كان يتجاوز عن المعسرين في الأسواق
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن حذيفة وأبي هريرة رضي الله عنهما:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «كَانَ رَجُلٌ يُدَايِنُ النَّاسَ، فَكَانَ يَقُولُ لِفَتَاهُ: إِذَا أَتَيْتَ مُعْسِرًا فَتَجَاوَزْ عَنْهُ، لَعَلَّ اللَّهَ أَنْ يَتَجَاوَزَ عَنَّا، فَلَقِيَ اللَّهَ فَتَجَاوَزَ عَنْهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2078) وصحيح مسلم (1562)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">التيسير على المدينين وإسقاط جزء من الدين أو المسامحة فيه سبب مباشر لمغفرة الله يوم القيامة.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 9 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="مشروعية الكفالة والضمان المالي (الزعيم غارم) «الْعَارِيَّةُ مُؤَدَّاةٌ، وَالْمِنْحَةُ مَرْدُودَةٌ، وَالدَّيْنُ مَقْضِيٌّ، وَالزَّعِيمُ غَارِمٌ» رواه الترمذي (1265) وأبو داود (3565) وصححه الألباني التزام الكفيل والغارم بسداد الدين عند تعذر المدين الأصلي، وثبوت أحكام الكفالة والضمان في المعاملات.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        9
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        مشروعية الكفالة والضمان المالي (الزعيم غارم)
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي أمامة الباهلي رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «الْعَارِيَّةُ مُؤَدَّاةٌ، وَالْمِنْحَةُ مَرْدُودَةٌ، وَالدَّيْنُ مَقْضِيٌّ، وَالزَّعِيمُ غَارِمٌ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (1265) وأبو داود (3565) وصححه الألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">التزام الكفيل والغارم بسداد الدين عند تعذر المدين الأصلي، وثبوت أحكام الكفالة والضمان في المعاملات.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 10 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="عقوبة المماطل المقتدر وجواز شكايته وحبسه شرعاً «لَيُّ الْوَاجِدِ يُحِلُّ عِرْضَهُ وَعُقُوبَتَهُ» رواه أبو داود (3628) والنسائي وصححه الألباني، وعلقه البخاري بصيغة الجزم المماطلة في السداد مع القدرة المالية تجيز للدائن رفع أمره للقضاء وللحاكم تعزيره وإلزامه بالتنفيذ الجبري.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        10
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        عقوبة المماطل المقتدر وجواز شكايته وحبسه شرعاً
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن الشريد بن سويد رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَيُّ الْوَاجِدِ يُحِلُّ عِرْضَهُ وَعُقُوبَتَهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3628) والنسائي وصححه الألباني، وعلقه البخاري بصيغة الجزم</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">المماطلة في السداد مع القدرة المالية تجيز للدائن رفع أمره للقضاء وللحاكم تعزيره وإلزامه بالتنفيذ الجبري.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 11 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="labor" data-hadith-search="وجوب دفع أجر العامل والأجير فور الفراغ من عمله «أَعْطُوا الْأَجِيرَ أَجْرَهُ قَبْلَ أَنْ يَجِفَّ عَرَقُهُ» رواه ابن ماجه (2443) وصححه الألباني في صحيح الجامع (1055) تحريم تأخير صرف رواتب ومستحقات العمال والموظفين والمقاولين بعد إتمام المهام المطلوبة.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        11
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وجوب دفع أجر العامل والأجير فور الفراغ من عمله
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-fuchsia-500/20 text-fuchsia-300 border-fuchsia-400/40">
                        👥 أجور وعمال وشراكة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمر رضي الله عنهما:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «أَعْطُوا الْأَجِيرَ أَجْرَهُ قَبْلَ أَنْ يَجِفَّ عَرَقُهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه ابن ماجه (2443) وصححه الألباني في صحيح الجامع (1055)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم تأخير صرف رواتب ومستحقات العمال والموظفين والمقاولين بعد إتمام المهام المطلوبة.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 12 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="labor" data-hadith-search="وعيد الله الشديد لمن استأجر أجيراً ولم يوفه أجره كاملاً «قَالَ اللَّهُ تَعَالَى: ثَلَاثَةٌ أَنَا خَصْمُهُمْ يَوْمَ الْقِيَامَةِ... وَرَجُلٌ اسْتَأْجَرَ أَجِيرًا فَاسْتَوْفَى مِنْهُ وَلَمْ يُعْطِهِ أَجْرَهُ» صحيح البخاري (2227) إنقاص مستحقات العامل أو بخسه حقه بعد أداء عمله من أعظم الظلم الذي يجعل الله خصماً لصاحب العمل.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        12
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وعيد الله الشديد لمن استأجر أجيراً ولم يوفه أجره كاملاً
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-fuchsia-500/20 text-fuchsia-300 border-fuchsia-400/40">
                        👥 أجور وعمال وشراكة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «قَالَ اللَّهُ تَعَالَى: ثَلَاثَةٌ أَنَا خَصْمُهُمْ يَوْمَ الْقِيَامَةِ... وَرَجُلٌ اسْتَأْجَرَ أَجِيرًا فَاسْتَوْفَى مِنْهُ وَلَمْ يُعْطِهِ أَجْرَهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2227)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">إنقاص مستحقات العامل أو بخسه حقه بعد أداء عمله من أعظم الظلم الذي يجعل الله خصماً لصاحب العمل.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 13 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="labor" data-hadith-search="بركة الشراكة والأمانة بين الشركاء وخروج البركة بالخيانة «إِنَّ اللَّهَ يَقُولُ: أَنَا ثَالِثُ الشَّرِيكَيْنِ مَا لَمْ يَخُنْ أَحَدُهُمَا صَاحِبَهُ، فَإِذَا خَانَهُ خَرَجْتُ مِنْ بَيْنِهِمَا» رواه أبو داود (3383) وصححه الحاكم ووافقه الذهبي الأمانة والشفافية المحاسبية التامة بين الشركاء سبب توفيق واستثمار ونماء الشركة، والخيانة تمحق المال.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        13
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        بركة الشراكة والأمانة بين الشركاء وخروج البركة بالخيانة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-fuchsia-500/20 text-fuchsia-300 border-fuchsia-400/40">
                        👥 أجور وعمال وشراكة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «إِنَّ اللَّهَ يَقُولُ: أَنَا ثَالِثُ الشَّرِيكَيْنِ مَا لَمْ يَخُنْ أَحَدُهُمَا صَاحِبَهُ، فَإِذَا خَانَهُ خَرَجْتُ مِنْ بَيْنِهِمَا»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3383) وصححه الحاكم ووافقه الذهبي</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">الأمانة والشفافية المحاسبية التامة بين الشركاء سبب توفيق واستثمار ونماء الشركة، والخيانة تمحق المال.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 14 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="labor" data-hadith-search="وجوب الإتقان والجودة في الأعمال والتصنيع والخدمات «إِنَّ اللَّهَ يُحِبُّ إِذَا عَمِلَ أَحَدُكُمْ عَمَلًا أَنْ يُتْقِنَهُ» رواه البيهقي في شعب الإيمان وصححه الألباني في السلسلة الصحيحة (1113) معيار الجودة والإتقان في الإنتاج الصناعي والمقاولات والخدمات واجب شرعي وأخلاقي في الإسلام.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        14
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وجوب الإتقان والجودة في الأعمال والتصنيع والخدمات
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-fuchsia-500/20 text-fuchsia-300 border-fuchsia-400/40">
                        👥 أجور وعمال وشراكة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عائشة رضي الله عنها:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «إِنَّ اللَّهَ يُحِبُّ إِذَا عَمِلَ أَحَدُكُمْ عَمَلًا أَنْ يُتْقِنَهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه البيهقي في شعب الإيمان وصححه الألباني في السلسلة الصحيحة (1113)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">معيار الجودة والإتقان في الإنتاج الصناعي والمقاولات والخدمات واجب شرعي وأخلاقي في الإسلام.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 15 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="prohibitions" data-hadith-search="لعن آكل الربا وموكله وكاتبه وشاهديه ومساواتهم في الإثم «لَعَنَ رَسُولُ اللَّهِ ﷺ آكِلَ الرِّبَا، وَمُؤْكِلَهُ، وَكَاتِبَهُ، وَشَاهِدَيْهِ، وَقَالَ: هُمْ سَوَاءٌ» صحيح مسلم (1598) تحريم التعامل بالربا أو توثيقه أو برمجته أو قيده في السجلات المحاسبية والأنظمة المالية.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        15
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        لعن آكل الربا وموكله وكاتبه وشاهديه ومساواتهم في الإثم
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف ورشوة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن جابر بن عبد الله رضي الله عنهما:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَعَنَ رَسُولُ اللَّهِ ﷺ آكِلَ الرِّبَا، وَمُؤْكِلَهُ، وَكَاتِبَهُ، وَشَاهِدَيْهِ، وَقَالَ: هُمْ سَوَاءٌ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح مسلم (1598)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم التعامل بالربا أو توثيقه أو برمجته أو قيده في السجلات المحاسبية والأنظمة المالية.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 16 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="prohibitions" data-hadith-search="الأصناف الربوية وشرط التقابض الفوري في صرف العملات «الذَّهَبُ بِالذَّهَبِ، وَالْفِضَّةُ بِالْفِضَّةِ، وَالْبُرُّ بِالْبُرِّ، وَالشَّعِيرُ بِالشَّعِيرِ، وَالتَّمْرُ بِالتَّمْرِ، وَالْمِلْحُ بِالْمِلْحِ، مِثْلًا بِمِثْلٍ، سَوَاءً بِسَوَاءٍ، يَدًا بِيَدٍ، فَإِذَا اخْتَلَفَتْ هَذِهِ الْأَصْنَافُ فَبِيعُوا كَيْفَ شِئْتُمْ إِذَا كَانَ يَدًا بِيَدٍ» صحيح مسلم (1587) اشتراط التقابض الفوري الحقيقي أو الحكمي (التحويل البنكي الفوري) عند مبادلة العملات المختلفة.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        16
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        الأصناف الربوية وشرط التقابض الفوري في صرف العملات
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف ورشوة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبادة بن الصامت رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «الذَّهَبُ بِالذَّهَبِ، وَالْفِضَّةُ بِالْفِضَّةِ، وَالْبُرُّ بِالْبُرِّ، وَالشَّعِيرُ بِالشَّعِيرِ، وَالتَّمْرُ بِالتَّمْرِ، وَالْمِلْحُ بِالْمِلْحِ، مِثْلًا بِمِثْلٍ، سَوَاءً بِسَوَاءٍ، يَدًا بِيَدٍ، فَإِذَا اخْتَلَفَتْ هَذِهِ الْأَصْنَافُ فَبِيعُوا كَيْفَ شِئْتُمْ إِذَا كَانَ يَدًا بِيَدٍ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح مسلم (1587)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">اشتراط التقابض الفوري الحقيقي أو الحكمي (التحويل البنكي الفوري) عند مبادلة العملات المختلفة.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 17 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="prohibitions" data-hadith-search="لعن الراشي والمرتشي في المعاملات والوظائف والمناقصات «لَعَنَ رَسُولُ اللَّهِ ﷺ الرَّاشِيَ وَالْمُرْتَشِيَ (وفي رواية: وَالرَّائِشَ)» رواه الترمذي (1337) وأبو داود (3580) وصححه الألباني تحريم دفع أو قبول أي مبالغ أو عمولات خفية للتأثير على المناقصات أو العقود أو الإجراءات الرسمية.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        17
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        لعن الراشي والمرتشي في المعاملات والوظائف والمناقصات
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف ورشوة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمرو وأبي هريرة رضي الله عنهما:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَعَنَ رَسُولُ اللَّهِ ﷺ الرَّاشِيَ وَالْمُرْتَشِيَ (وفي رواية: وَالرَّائِشَ)»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (1337) وأبو داود (3580) وصححه الألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم دفع أو قبول أي مبالغ أو عمولات خفية للتأثير على المناقصات أو العقود أو الإجراءات الرسمية.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 18 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="prohibitions" data-hadith-search="تحريم هدايا وعمولات الموظفين والمسؤولين والمشترين (الغلول) «هَدَايَا الْعُمَّالِ غُلُولٌ» رواه أحمد (23604) والبيهقي وصححه الألباني في صحيح الجامع (7021) تحريم الهدايا والعمولات الخاصة التي يأخذها مسؤول المشتريات أو الموظف من الموردين دون إذن صاحب المنشأة.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        18
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم هدايا وعمولات الموظفين والمسؤولين والمشترين (الغلول)
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف ورشوة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي حميد الساعدي رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «هَدَايَا الْعُمَّالِ غُلُولٌ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أحمد (23604) والبيهقي وصححه الألباني في صحيح الجامع (7021)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم الهدايا والعمولات الخاصة التي يأخذها مسؤول المشتريات أو الموظف من الموردين دون إذن صاحب المنشأة.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 19 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="prohibitions" data-hadith-search="شؤم المال الحرام واستحقاق النار للجسد النابت من السحت «لَا يَرْبُو لَحْمٌ نَبَتَ مِنْ سُحْتٍ إِلَّا كَانَتِ النَّارُ أَوْلَى بِهِ» رواه الترمذي (614) وصححه الألباني في صحيح سنن الترمذي تطهير التجارة من أي مكاسب خبيثة لأن أكل الحرام يحبط العمل ويمنع إجابة الدعاء ويمحق البركة.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        19
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        شؤم المال الحرام واستحقاق النار للجسد النابت من السحت
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف ورشوة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن كعب بن عجرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَا يَرْبُو لَحْمٌ نَبَتَ مِنْ سُحْتٍ إِلَّا كَانَتِ النَّارُ أَوْلَى بِهِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (614) وصححه الألباني في صحيح سنن الترمذي</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تطهير التجارة من أي مكاسب خبيثة لأن أكل الحرام يحبط العمل ويمنع إجابة الدعاء ويمحق البركة.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 20 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="prohibitions" data-hadith-search="عقوبة اقتطاع أموال الناس وحقوقهم بالأيمان الكاذبة «مَنِ اقْتَطَعَ حَقَّ امْرِئٍ مُسْلِمٍ بِيَمِينِهِ، فَقَدْ أَوْجَبَ اللَّهُ لَهُ النَّارَ، وَحَرَّمَ عَلَيْهِ الْجَنَّةَ، فَقَالَ رَجُلٌ: وَإِنْ كَانَ شَيْئًا يَسِيرًا يَا رَسُولَ اللَّهِ؟ قَالَ: وَإِنْ قَضِيبًا مِنْ أَرَاكٍ» صحيح مسلم (137) حرمة أكل أموال الناس بالباطل أو إنكار الديون أو الفواتير المستحقة مهما كانت قيمتها ضئيلة.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        20
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        عقوبة اقتطاع أموال الناس وحقوقهم بالأيمان الكاذبة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف ورشوة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي أمامة إياس بن ثعلبة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنِ اقْتَطَعَ حَقَّ امْرِئٍ مُسْلِمٍ بِيَمِينِهِ، فَقَدْ أَوْجَبَ اللَّهُ لَهُ النَّارَ، وَحَرَّمَ عَلَيْهِ الْجَنَّةَ، فَقَالَ رَجُلٌ: وَإِنْ كَانَ شَيْئًا يَسِيرًا يَا رَسُولَ اللَّهِ؟ قَالَ: وَإِنْ قَضِيبًا مِنْ أَرَاكٍ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح مسلم (137)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">حرمة أكل أموال الناس بالباطل أو إنكار الديون أو الفواتير المستحقة مهما كانت قيمتها ضئيلة.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 21 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="البركة في الصدق والبيان وشؤم الكتمان والكذب «الْبَيِّعَانِ بِالْخِيَارِ مَا لَمْ يَتَفَرَّقَا، فَإِنْ صَدَقَا وَبَيَّنَا بُورِكَ لَهُمَا فِي بَيْعِهِمَا، وَإِنْ كَتَمَا وَكَذَبَا مُحِقَتْ بَرَكَةُ بَيْعِهِمَا» صحيح البخاري (2079) وصحيح مسلم (1532) الصدق في بيان تكلفة وسعر السلعة وعيوبها هو مفتاح البركة والنماء، والكتمان والكذب يمحق الأرباح.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        21
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        البركة في الصدق والبيان وشؤم الكتمان والكذب
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن حكيم بن حزام رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «الْبَيِّعَانِ بِالْخِيَارِ مَا لَمْ يَتَفَرَّقَا، فَإِنْ صَدَقَا وَبَيَّنَا بُورِكَ لَهُمَا فِي بَيْعِهِمَا، وَإِنْ كَتَمَا وَكَذَبَا مُحِقَتْ بَرَكَةُ بَيْعِهِمَا»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2079) وصحيح مسلم (1532)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">الصدق في بيان تكلفة وسعر السلعة وعيوبها هو مفتاح البركة والنماء، والكتمان والكذب يمحق الأرباح.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 22 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="فضل التاجر الصدوق الأمين ومنزلته مع النبيين والشهداء «التَّاجِرُ الصَّدُوقُ الأَمِينُ مَعَ النَّبِيِّينَ وَالصِّدِّيقِينَ وَالشُّهَدَاءِ» رواه الترمذي (1209) وصححه الألباني في صحيح الترغيب (1782) بيان شرف مهنة التجارة إذا اقترنت بالأمانة والصدق والبعد عن الغش والخيانة.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        22
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        فضل التاجر الصدوق الأمين ومنزلته مع النبيين والشهداء
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي سعيد الخدري رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «التَّاجِرُ الصَّدُوقُ الأَمِينُ مَعَ النَّبِيِّينَ وَالصِّدِّيقِينَ وَالشُّهَدَاءِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (1209) وصححه الألباني في صحيح الترغيب (1782)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">بيان شرف مهنة التجارة إذا اقترنت بالأمانة والصدق والبعد عن الغش والخيانة.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 23 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="السماحة واليسر في البيع والشراء والقضاء والمطالبة «رَحِمَ اللَّهُ رَجُلًا سَمْحًا إِذَا بَاعَ، وَإِذَا اشْتَرَى، وَإِذَا اقْتَضَى» صحيح البخاري (2076) الدعاء بالرحمة النبوية لكل من يتساهل ولا يشدد على الناس في المماكسة وسداد الديون.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        23
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        السماحة واليسر في البيع والشراء والقضاء والمطالبة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن جابر بن عبد الله رضي الله عنهما:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «رَحِمَ اللَّهُ رَجُلًا سَمْحًا إِذَا بَاعَ، وَإِذَا اشْتَرَى، وَإِذَا اقْتَضَى»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2076)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">الدعاء بالرحمة النبوية لكل من يتساهل ولا يشدد على الناس في المماكسة وسداد الديون.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 24 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="النهي الصريح عن بيع ما لا يملك التاجر (بيع ما ليس عندك) «قُلْتُ: يَا رَسُولَ اللَّهِ، يَأْتِينِي الرَّجُلُ فَيُرِيدُ مِنِّي الْبَيْعَ لَيْسَ عِنْدِي، أَفَأَبْتَاعُهُ لَهُ مِنَ السُّوقِ؟ فَقَالَ: لَا تَبِعْ مَا لَيْسَ عِنْدَكَ» رواه أبو داود (3503) والترمذي (1232) وصححه الألباني تحريم إبرام عقد بيع باتّ على سلعة معينة قبل شرائها وقبضها ودخولها في ملك وضمان التاجر.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        24
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        النهي الصريح عن بيع ما لا يملك التاجر (بيع ما ليس عندك)
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن حكيم بن حزام رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «قُلْتُ: يَا رَسُولَ اللَّهِ، يَأْتِينِي الرَّجُلُ فَيُرِيدُ مِنِّي الْبَيْعَ لَيْسَ عِنْدِي، أَفَأَبْتَاعُهُ لَهُ مِنَ السُّوقِ؟ فَقَالَ: لَا تَبِعْ مَا لَيْسَ عِنْدَكَ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3503) والترمذي (1232) وصححه الألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم إبرام عقد بيع باتّ على سلعة معينة قبل شرائها وقبضها ودخولها في ملك وضمان التاجر.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 25 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="النهي عن بيوع الغرر والمخاطرة والجهالة في السلع «نَهَى رَسُولُ اللَّهِ ﷺ عَنْ بَيْعِ الْحَصَاةِ، وَعَنْ بَيْعِ الْغَرَرِ» صحيح مسلم (1513) وجوب نفي الجهالة وتحديد مواصفات وأبعاد وأوزان وأسعار السلع بدقة بالفاتورة لقطع النزاع.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        25
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        النهي عن بيوع الغرر والمخاطرة والجهالة في السلع
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «نَهَى رَسُولُ اللَّهِ ﷺ عَنْ بَيْعِ الْحَصَاةِ، وَعَنْ بَيْعِ الْغَرَرِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح مسلم (1513)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">وجوب نفي الجهالة وتحديد مواصفات وأبعاد وأوزان وأسعار السلع بدقة بالفاتورة لقطع النزاع.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 26 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="وجوب الوفاء بالشروط التجارية والعقود المبرمة «الْمُسْلِمُونَ عَلَى شُرُوطِهِمْ، إِلَّا شَرْطًا حَرَّمَ حَلَالًا أَوْ أَحَلَّ حَرَامًا» رواه الترمذي (1352) وصححه الألباني في الإرواء (1303) إلزامية كافة الشروط التجارية المتفق عليها (مواعيد التسليم، الدفعات، المواصفات) ما لم تخالف الشرع.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        26
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وجوب الوفاء بالشروط التجارية والعقود المبرمة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عمرو بن عوف المزني رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «الْمُسْلِمُونَ عَلَى شُرُوطِهِمْ، إِلَّا شَرْطًا حَرَّمَ حَلَالًا أَوْ أَحَلَّ حَرَامًا»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (1352) وصححه الألباني في الإرواء (1303)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">إلزامية كافة الشروط التجارية المتفق عليها (مواعيد التسليم، الدفعات، المواصفات) ما لم تخالف الشرع.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 27 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="تحريم الغش والتطفيف والبراءة النبوية من الغاش «أَنَّ رَسُولَ اللَّهِ ﷺ مَرَّ عَلَى صُبْرَةِ طَعَامٍ فَأَدْخَلَ يَدَهُ فِيهَا، فَنَالَتْ أَصَابِعُهُ بَلَلًا، فَقَالَ: مَا هَذَا يَا صَاحِبَ الطَّعَامِ؟ قَالَ: أَصَابَتْهُ السَّمَاءُ يَا رَسُولَ اللَّهِ، قَالَ: أَفَلَا جَعَلْتَهُ فَوْقَ الطَّعَامِ كَيْ يَرَاهُ النَّاسُ، مَنْ غَشَّ فَلَيْسَ مِنِّي» صحيح مسلم (102) تحريم إخفاء البضاعة التالفة تحت السليمة أو تغيير المواصفات، ووجوب إظهار العيوب للمشتري.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        27
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم الغش والتطفيف والبراءة النبوية من الغاش
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «أَنَّ رَسُولَ اللَّهِ ﷺ مَرَّ عَلَى صُبْرَةِ طَعَامٍ فَأَدْخَلَ يَدَهُ فِيهَا، فَنَالَتْ أَصَابِعُهُ بَلَلًا، فَقَالَ: مَا هَذَا يَا صَاحِبَ الطَّعَامِ؟ قَالَ: أَصَابَتْهُ السَّمَاءُ يَا رَسُولَ اللَّهِ، قَالَ: أَفَلَا جَعَلْتَهُ فَوْقَ الطَّعَامِ كَيْ يَرَاهُ النَّاسُ، مَنْ غَشَّ فَلَيْسَ مِنِّي»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح مسلم (102)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم إخفاء البضاعة التالفة تحت السليمة أو تغيير المواصفات، ووجوب إظهار العيوب للمشتري.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 28 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="فضل إقالة النادم وفسخ البيع برضا وقبول المرتجع «مَنْ أَقَالَ مُسْلِمًا، أَقَالَهُ اللَّهُ عَثْرَتَهُ يَوْمَ الْقِيَامَةِ» رواه أبو داود (3460) وابن ماجه (2199) وصححه الألباني استحباب قبول إرجاع البضاعة واسترداد الثمن للزبون النادم الذي لم يعد بحاجة للسلعة وتفريج كربته.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        28
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        فضل إقالة النادم وفسخ البيع برضا وقبول المرتجع
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنْ أَقَالَ مُسْلِمًا، أَقَالَهُ اللَّهُ عَثْرَتَهُ يَوْمَ الْقِيَامَةِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3460) وابن ماجه (2199) وصححه الألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">استحباب قبول إرجاع البضاعة واسترداد الثمن للزبون النادم الذي لم يعد بحاجة للسلعة وتفريج كربته.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 29 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="النهي والتحذير الشديد من الحلف الكاذب لترويج البضائع «الْحَلِفُ مُنَفِّقَةٌ لِلسِّلْعَةِ، مُمْحِقَةٌ لِلْبَرَكَةِ» صحيح البخاري (2087) وصحيح مسلم (1606) تحريم الإكثار من الحلف بالله في الأسواق لإقناع المشتري بالسعر أو جودة البضاعة.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        29
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        النهي والتحذير الشديد من الحلف الكاذب لترويج البضائع
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «الْحَلِفُ مُنَفِّقَةٌ لِلسِّلْعَةِ، مُمْحِقَةٌ لِلْبَرَكَةِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2087) وصحيح مسلم (1606)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم الإكثار من الحلف بالله في الأسواق لإقناع المشتري بالسعر أو جودة البضاعة.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 30 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="تحريم الحيل الربوية وبيع العِينة «إِذَا تَبَايَعْتُمْ بِالْعِينَةِ، وَأَخَذْتُمْ أَذْنَابَ الْبَقَرِ، وَرَضِيتُمْ بِالزَّرْعِ، وَتَرَكْتُمُ الْجِهَادَ، سَلَّطَ اللَّهُ عَلَيْكُمْ ذُلًّا لَا يَنْزِعُهُ حَتَّى تَرْجِعُوا إِلَى دِينِكُمْ» رواه أبو داود (3462) وصححه الألباني في السلسلة الصحيحة (11) تحريم التحايل على القرض الربوي بصور بيع صورية، كبيع السلعة بالآجل ثم شراؤها نقداً بأقل.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        30
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم الحيل الربوية وبيع العِينة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن ابن عمر رضي الله عنهما:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «إِذَا تَبَايَعْتُمْ بِالْعِينَةِ، وَأَخَذْتُمْ أَذْنَابَ الْبَقَرِ، وَرَضِيتُمْ بِالزَّرْعِ، وَتَرَكْتُمُ الْجِهَادَ، سَلَّطَ اللَّهُ عَلَيْكُمْ ذُلًّا لَا يَنْزِعُهُ حَتَّى تَرْجِعُوا إِلَى دِينِكُمْ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3462) وصححه الألباني في السلسلة الصحيحة (11)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم التحايل على القرض الربوي بصور بيع صورية، كبيع السلعة بالآجل ثم شراؤها نقداً بأقل.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 31 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="تحريم النَّجْش (المزايدة الكاذبة في الأسواق والمزادات) «نَهَى النَّبِيُّ ﷺ عَنِ النَّجْشِ» صحيح البخاري (2142) وصحيح مسلم (1516) تحريم أن يرفع شخص سعر السلعة في المزاد أو السوق وهو لا يريد شراءها بل ليغر المشتري ويرفع السعر عليه.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        31
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم النَّجْش (المزايدة الكاذبة في الأسواق والمزادات)
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمر رضي الله عنهما:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «نَهَى النَّبِيُّ ﷺ عَنِ النَّجْشِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2142) وصحيح مسلم (1516)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم أن يرفع شخص سعر السلعة في المزاد أو السوق وهو لا يريد شراءها بل ليغر المشتري ويرفع السعر عليه.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 32 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="تحريم الاحتكار وحبس السلع الأساسية لرفع أسعارها «لَا يَحْتَكِرُ إِلَّا خَاطِئٌ» صحيح مسلم (1605) تحريم شراء أقوات وحاجات الناس وتخزينها لحبسها عن الأسواق بغرض إغلاء الأسعار واستغلال الأزمات.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        32
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم الاحتكار وحبس السلع الأساسية لرفع أسعارها
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن معمر بن عبد الله رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَا يَحْتَكِرُ إِلَّا خَاطِئٌ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح مسلم (1605)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم شراء أقوات وحاجات الناس وتخزينها لحبسها عن الأسواق بغرض إغلاء الأسعار واستغلال الأزمات.</span>
                </div>
            </div>

        </div>
    
                    </div>

                    <div id="no-hadiths-alert" class="p-8 text-center bg-slate-900/60 rounded-2xl border border-slate-800 hidden">
                        <span class="text-4xl block mb-2">🔍</span>
                        <h5 class="text-lg font-black text-slate-300">لا توجد أحاديث مطابقة للبحث الحالي</h5>
                        <p class="text-xs text-slate-400 mt-1">يرجى مسح كلمة البحث أو اختيار تصنيف آخر لإظهار بقية الأحاديث.</p>
                    </div>
                    
                

                id="fiqh-sub-terms" class="space-y-4 hidden">
                    <div class="p-6 bg-slate-900/90 rounded-2xl border-2 border-amber-500/30 space-y-4">
                        <div class="border-b border-slate-800 pb-3">
                            <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-black text-xs rounded-xl border border-amber-400">
                                📖 معجم المصطلحات الفقهية والاقتصادية
                            </span>
                            <h3 class="text-xl font-black text-white mt-2">
                                قاموس مصطلحات البيوع والمعاملات للتاجر المسلم
                            </h3>
                            <p class="text-xs text-slate-400 font-bold mt-0.5">
                                تعريفات موجزة ومحررة لأهم المصطلحات الفقهية المتداولة في الأسواق وعقود التجارة الحديثة.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            
                            <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1.5 hover:border-amber-400/60 transition">
                                <span class="px-2 py-0.5 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded">1. عقد الاستصناع (Istisna'a)</span>
                                <h5 class="text-sm font-black text-white">طلب تصنيع سلعة بمواصفات محددة ومواد من الصانع</h5>
                                <p class="text-xs text-slate-300 font-bold">عقد على مبيع في الذمة شرط فيه العمل، كطلبيات حديد التسليح الخاص وتصنيع الهياكل المعدنية والخرسانات الجاهزة، ويجوز فيه تأجيل الثمن أو تقسيطه.</p>
                            </div>

                            <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1.5 hover:border-amber-400/60 transition">
                                <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 font-mono font-black text-xs rounded">2. بيع السلم (Salam Sale)</span>
                                <h5 class="text-sm font-black text-white">شراء بضاعة آجلة بثمن مدفوع مقدماً بالكامل</h5>
                                <p class="text-xs text-slate-300 font-bold">بيع موصوف في الذمة مؤجل التسليم بثمن مقبوض فوراً في مجلس العقد، وهو وسيلة شرعية ممتازة لتمويل المشروعات الزراعية والمقاولات وتوريد المواد.</p>
                            </div>

                            <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1.5 hover:border-amber-400/60 transition">
                                <span class="px-2 py-0.5 bg-sky-500/20 text-sky-300 font-mono font-black text-xs rounded">3. بيع العربون (Down Payment Sale)</span>
                                <h5 class="text-sm font-black text-white">دفع جزء من الثمن لتأكيد الشراء أو اعتباره هدراً للبائع</h5>
                                <p class="text-xs text-slate-300 font-bold">أن يشتري السلعة ويدفع جزءاً مقدماً فإن أتم الشراء حُسب من الثمن وإن تراجع اختص به البائع تعويضاً عن حجز السلعة (وهو مذهب الإمام أحمد وصحيح فتاوى ابن باز والعثيمين).</p>
                            </div>

                            <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1.5 hover:border-amber-400/60 transition">
                                <span class="px-2 py-0.5 bg-purple-500/20 text-purple-300 font-mono font-black text-xs rounded">4. بيع المرابحة للآمر بالشراء</span>
                                <h5 class="text-sm font-black text-white">شراء المؤسسة للسلعة ثم بيعها للعميل بالتقسيط بربح معلوم</h5>
                                <p class="text-xs text-slate-300 font-bold">أن يطلب العميل من البنك أو التاجر شراء معدات أو مواد محددة، فإذا تملكها وقبضها البائع باعها للعميل بالتقسيط بربح متفق عليه، بشرط خلوها من العينة.</p>
                            </div>

                            <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1.5 hover:border-amber-400/60 transition">
                                <span class="px-2 py-0.5 bg-rose-500/20 text-rose-300 font-mono font-black text-xs rounded">5. بيع العِينة المحرم (Eina Sale)</span>
                                <h5 class="text-sm font-black text-white">بيع السلعة لأجل ثم شراؤها نقداً بثمن أقل</h5>
                                <p class="text-xs text-slate-300 font-bold">حيلة ربوية صريحة محرمة، وصورتها أن يبيع التاجر سلعة بـ 100 ألف لأجل ثم يشتريها في نفس المجلس نقداً بـ 80 ألفاً، فيكون القصد قرض 80 واسترداد 100.</p>
                            </div>

                            <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1.5 hover:border-amber-400/60 transition">
                                <span class="px-2 py-0.5 bg-cyan-500/20 text-cyan-300 font-mono font-black text-xs rounded">6. عقد المقاصة المالية (Settlement)</span>
                                <h5 class="text-sm font-black text-white">إسقاط دين مطلوب بدين مقابل متساوٍ</h5>
                                <p class="text-xs text-slate-300 font-bold">براءة ذمة كل من الطرفين بما عليه لصاحبه من التزامات مالية متقابلة متماثلة، وهي جائزة شرعاً وتوفر سيولة هائلة وتسرع تسوية الديون التجارية.</p>
                            </div>

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

    
    // 🚀 AUTO-ACTIVATE TAB FROM URL HASH OR PARAMETER 🚀
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab') || window.location.hash.replace('#', '');
        
        if (tabParam) {
            const targetPill = document.querySelector('[onclick*="switchSuite(\'' + tabParam + '\'"]');
            if (targetPill) {
                switchSuite(tabParam, targetPill);
            }
        }
    });

    function switchSuite(suiteId, el) {
        document.querySelectorAll('.suite-section').forEach(sec => sec.classList.add('hidden'));
        document.querySelectorAll('.suite-pill-card').forEach(btn => btn.classList.remove('active'));

        const targetSec = document.getElementById('suite-sec-' + suiteId);
        if (targetSec) targetSec.classList.remove('hidden');
        if (el) el.classList.add('active');
    }

    // ── Fiqh Sub-Tabs Controller ──
    
    let activeHadithCat = 'all';
    function setHadithCategory(cat, btn) {
        activeHadithCat = cat;
        document.querySelectorAll('.hadith-filter-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        filterHadiths();
    }

    function filterHadiths() {
        const query = (document.getElementById('hadith-search-input') ? document.getElementById('hadith-search-input').value : '').trim().toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.fiqh-hadith-card').forEach(card => {
            const cat = card.getAttribute('data-hadith-cat');
            const search = card.getAttribute('data-hadith-search') || '';

            const matchCat = (activeHadithCat === 'all' || cat === activeHadithCat);
            const matchSearch = (query === '' || search.includes(query));

            if (matchCat && matchSearch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noAlert = document.getElementById('no-hadiths-alert');
        if (noAlert) noAlert.classList.toggle('hidden', visibleCount > 0);
    }

    
    let activeQuranTopic = 'all';
    function setQuranTopic(cat, btn) {
        activeQuranTopic = cat;
        document.querySelectorAll('.quran-filter-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        filterQuranVerses();
    }

    function filterQuranVerses() {
        const query = (document.getElementById('quran-search-input') ? document.getElementById('quran-search-input').value : '').trim().toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.fiqh-ayah-card').forEach(card => {
            const cat = card.getAttribute('data-ayah-topic');
            const search = card.getAttribute('data-ayah-search') || '';

            const matchCat = (activeQuranTopic === 'all' || cat === activeQuranTopic);
            const matchSearch = (query === '' || search.includes(query));

            if (matchCat && matchSearch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noAlert = document.getElementById('no-quran-alert');
        if (noAlert) noAlert.classList.toggle('hidden', visibleCount > 0);
    }

    
    let activeBookScholar = 'all';
    function setBookScholar(scholar, btn) {
        activeBookScholar = scholar;
        document.querySelectorAll('.book-filter-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        filterBooksLibrary();
    }

    function filterBooksLibrary() {
        const query = (document.getElementById('book-search-input') ? document.getElementById('book-search-input').value : '').trim().toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.fiqh-book-card').forEach(card => {
            const scholar = card.getAttribute('data-book-scholar');
            const search = card.getAttribute('data-book-search') || '';

            const matchScholar = (activeBookScholar === 'all' || scholar === activeBookScholar);
            const matchSearch = (query === '' || search.includes(query));

            if (matchScholar && matchSearch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noAlert = document.getElementById('no-books-alert');
        if (noAlert) noAlert.classList.toggle('hidden', visibleCount > 0);
    }

    
    // 🌐 LIVE GOOGLE SCHOLAR SEARCH ENGINE CONTROLLER 🌐
    const googleScholarNamesMap = {
        'all': '("ابن باز" OR "ابن عثيمين" OR "ابن تيمية" OR "ابن القيم" OR "أحمد بن حنبل" OR "النووي" OR "الذهبي" OR "محمد بن عبد الوهاب" OR "السعدي" OR "الشنقيطي" OR "الألباني" OR "صالح الفوزان")',
        'ibn_baz': '"ابن باز"',
        'ibn_uthaymeen': '"ابن عثيمين"',
        'al_fawzan': '"صالح الفوزان"',
        'ibn_taymiyyah': '"ابن تيمية"',
        'ibn_al_qayyim': '"ابن القيم"',
        'imam_ahmad': '"أحمد بن حنبل"',
        'al_nawawi': '"النووي"',
        'al_dhahabi': '"الذهبي"',
        'muhammad_bin_abdulwahhab': '"محمد بن عبد الوهاب"',
        'al_saadi': '"السعدي"',
        'al_shinqiti': '"الشنقيطي"',
        'al_albani': '"الألباني"'
    };

    const googleSitesMap = {
        'all_approved': '(site:binbaz.org.sa OR site:ibnothaimeen.net OR site:alfawzan.af.org.sa OR site:alalbany.org OR site:shamela.ws OR site:islamweb.net OR site:alukah.net OR site:dorar.net)',
        'official_only': '(site:binbaz.org.sa OR site:ibnothaimeen.net OR site:alfawzan.af.org.sa OR site:alalbany.org)',
        'shamela': 'site:shamela.ws',
        'binbaz': 'site:binbaz.org.sa',
        'uthaymeen': 'site:ibnothaimeen.net',
        'fawzan': 'site:alfawzan.af.org.sa',
        'albany': 'site:alalbany.org',
        'dorar': 'site:dorar.net',
        'islamweb': 'site:islamweb.net',
        'alukah': 'site:alukah.net'
    };

    function executeGoogleScholarSearch() {
        const queryInput = (document.getElementById('google-fiqh-query') ? document.getElementById('google-fiqh-query').value : '').trim();
        if (!queryInput) {
            alert('يرجى كتابة كلمة البحث أو السؤال الفقهي أولاً للبحث في قوقل.');
            if (document.getElementById('google-fiqh-query')) document.getElementById('google-fiqh-query').focus();
            return;
        }

        const scholarKey = document.getElementById('google-scholar-select') ? document.getElementById('google-scholar-select').value : 'all';
        const sitesKey = document.getElementById('google-sites-select') ? document.getElementById('google-sites-select').value : 'all_approved';

        const scholarDork = googleScholarNamesMap[scholarKey] || googleScholarNamesMap['all'];
        const sitesDork = googleSitesMap[sitesKey] || googleSitesMap['all_approved'];

        const fullSearchQuery = queryInput + ' ' + scholarDork + ' ' + sitesDork;
        const googleUrl = 'https://www.google.com/search?q=' + encodeURIComponent(fullSearchQuery);
        window.open(googleUrl, '_blank');
    }

    function quickGoogleSearch(phrase) {
        if (document.getElementById('google-fiqh-query')) {
            document.getElementById('google-fiqh-query').value = phrase;
        }
        executeGoogleScholarSearch();
    }

    function executeQuickGoogleSearch() {
        const queryInput = (document.getElementById('fiqh-search-input') ? document.getElementById('fiqh-search-input').value : '').trim();
        const baseQuery = queryInput !== '' ? queryInput : 'فتاوى البيوع والمعاملات المالية';
        const fullSearchQuery = baseQuery + ' ' + googleScholarNamesMap['all'] + ' ' + googleSitesMap['all_approved'];
        const googleUrl = 'https://www.google.com/search?q=' + encodeURIComponent(fullSearchQuery);
        window.open(googleUrl, '_blank');
    }

    function switchFiqhSubTab(subId, el) {
        document.getElementById('fiqh-sub-fatwas').classList.add('hidden');
        document.getElementById('fiqh-sub-books').classList.add('hidden');
        document.getElementById('fiqh-sub-maxims').classList.add('hidden');
        document.getElementById('fiqh-sub-terms').classList.add('hidden');
        if (document.getElementById('fiqh-sub-quran')) document.getElementById('fiqh-sub-quran').classList.add('hidden');
        if (document.getElementById('fiqh-sub-google')) document.getElementById('fiqh-sub-google').classList.add('hidden');
        if (document.getElementById('fiqh-sub-hadiths')) document.getElementById('fiqh-sub-hadiths').classList.add('hidden');

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

    // 🧠 ADVANCED ARABIC AI SYNONYM & FUZZY SEARCH ENGINE 🧠
    function normalizeArabicText(text) {
        if (!text) return '';
        return text.toString()
            .replace(/[\u064B-\u065F\u0670]/g, '') // Remove Tashkeel
            .replace(/[إأآا]/g, 'ا')
            .replace(/ة/g, 'ه')
            .replace(/ى/g, 'ي')
            .replace(/\bابن\b/g, 'بن')
            .replace(/[\s\-_,.:;،؟/\\\(\)\[\]"']+/g, ' ')
            .trim()
            .toLowerCase();
    }

    function cleanArabicPrefix(w) {
        let t = w.replace(/^(ال|وال|بال|كال|فال|لل)/, '');
        if (t === 'ابن') t = 'بن';
        return t.length >= 2 ? t : w;
    }

    const fiqhSynonymsMap = {
        'تقسيط': ['تقسيط', 'اقساط', 'قسط', 'اجل', 'دفعه', 'دفعات', 'تاجيل', 'مرابحه'],
        'شيك': ['شيك', 'شيكات', 'كمبياله', 'كمبيالات', 'سند', 'اوراق تجاريه', 'اوراق ماليه', 'خصم'],
        'عربون': ['عربون', 'مقدم', 'دفعه اولي', 'مقدم حجز', 'عربين'],
        'ذهب': ['ذهب', 'فضه', 'عيار', 'مجوهرات', 'صاغه', 'بطاقه', 'مدي', 'pos'],
        'صرف': ['صرف', 'تحويل', 'عمله', 'دولار', 'جنيه', 'ريال', 'بنكك', 'فوري', 'موبايل بنك', 'اوكاش', 'ساهل'],
        'غرامه': ['غرامه', 'تاخير', 'فوائد', 'مماطل', 'ربا', 'مطل', 'غرامات', 'فائده'],
        'استصناع': ['استصناع', 'تصنيع', 'مقاولات', 'طلب', 'صناعه', 'ورشه', 'حداده', 'تشغيل'],
        'عينه': ['عينه', 'تورق', 'حيل', 'ربا', 'حيله', 'شراء نقدا', 'بيع بالاجل'],
        'غرر': ['غرر', 'جهاله', 'مخاطره', 'حصاه', 'مجهول', 'تدليس', 'مخاطر'],
        'تطفيف': ['تطفيف', 'ميزان', 'كيل', 'وزن', 'بسكول', 'نقص', 'غش', 'احتكار'],
        'تامين': ['تامين', 'تكافلي', 'تعاوني', 'بوليصه', 'شحن بحري', 'تكافل'],
        'ضمان': ['ضمان', 'كفاله', 'وارنتي', 'warranty', 'صيانه', 'كفيل', 'غارم', 'عيوب'],
        'ايجار': ['ايجار', 'تمليك', 'منتهي بالتمليك', 'تاجيري', 'مؤجر', 'اجاره'],
        'راتب': ['راتب', 'اجر', 'عمال', 'اجير', 'مكافاه', 'نهايه خدمه', 'خدمه'],
        'شركه': ['شركه', 'شراكه', 'مضاربه', 'شريك', 'اسهم', 'تداول', 'مساهمه'],
        'بيتكوين': ['بيتكوين', 'مشفره', 'رقميه', 'فوركس', 'تداول عملات', 'كريبتو', 'crypto']
    };

    let showAllFatwasForced = false;

    function toggleAllFatwasMode() {
        showAllFatwasForced = !showAllFatwasForced;
        const btn = document.getElementById('toggle-all-fatwas-btn');
        const bannerText = document.getElementById('fiqh-mode-text');
        if (btn) {
            btn.textContent = showAllFatwasForced ? '🔒 إخفاء الفتاوى الإضافية والعودة للأساسية' : '👁️ عرض جميع الـ 80 فتوى دفعة واحدة';
        }
        if (bannerText) {
            bannerText.innerHTML = showAllFatwasForced 
                ? 'الوضع الحالي: <strong class="text-emerald-300 font-black">عرض الموسوعة الشاملة كاملة (80 فتوى)</strong> 📚'
                : 'الوضع الحالي: <strong class="text-amber-300 font-black">الفتاوى الأساسية الأكثر شيوعاً (18 فتوى)</strong>. تظهر بقية الموسوعة الشاملة الـ <strong class="text-emerald-300 font-black">80 فتوى</strong> تلقائياً فور كتابة أي كلمة في البحث الذكي 🔍';
        }
        filterFatwas();
    }

    function filterFatwas() {
        const rawInput = (document.getElementById('fiqh-search-input') ? document.getElementById('fiqh-search-input').value : '').trim();
        const normQuery = normalizeArabicText(rawInput);
        let visibleCount = 0;

        const isSearchingOrFiltering = (normQuery !== '' || activeScholarFilter !== 'all' || activeTopicFilter !== 'all');

        // Auto-switch to fatwas sub-tab when user searches so results are immediately visible!
        if (normQuery !== '') {
            const fatwasTab = document.getElementById('fiqh-sub-fatwas');
            if (fatwasTab && fatwasTab.classList.contains('hidden')) {
                switchFiqhSubTab('fatwas', document.querySelector('.fiqh-nav-pill'));
            }
        }

        const words = normQuery.split(' ').filter(w => w.length > 0);

        document.querySelectorAll('.fiqh-master-card').forEach(card => {
            const isFeatured = card.getAttribute('data-featured') === 'true';
            const cardScholar = card.getAttribute('data-scholar') || '';
            const cardTopic = card.getAttribute('data-topic') || '';
            const cardSearchAttr = card.getAttribute('data-search') || '';
            const cardFullText = normalizeArabicText(cardSearchAttr + ' ' + (card.textContent || ''));

            const matchScholar = (activeScholarFilter === 'all' || cardScholar === activeScholarFilter);
            const matchTopic = (activeTopicFilter === 'all' || cardTopic === activeTopicFilter);

            let matchSearch = (normQuery === '');
            if (!matchSearch) {
                // Direct full query match
                if (cardFullText.includes(normQuery)) {
                    matchSearch = true;
                } else if (words.length > 1) {
                    // Multi-word AND matching
                    matchSearch = words.every(w => {
                        if (cardFullText.includes(w)) return true;
                        const stripped = cleanArabicPrefix(w);
                        return cardFullText.includes(stripped);
                    });
                } else if (words.length === 1) {
                    // Single word with synonyms
                    const w = words[0];
                    const strippedW = cleanArabicPrefix(w);
                    let syns = [];
                    for (const [key, synList] of Object.entries(fiqhSynonymsMap)) {
                        if (w === key || strippedW === key || synList.includes(w) || synList.includes(strippedW)) {
                            syns = syns.concat(synList);
                        }
                    }
                    matchSearch = syns.some(s => cardFullText.includes(s) || cardFullText.includes(cleanArabicPrefix(s)));
                }
            }

            // Visibility rule
            let shouldShow = false;
            if (isSearchingOrFiltering || showAllFatwasForced) {
                shouldShow = (matchScholar && matchTopic && matchSearch);
            } else {
                shouldShow = isFeatured;
            }

            if (shouldShow) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const badgeEl = document.getElementById('fiqh_search_badge');
        if (badgeEl) {
            badgeEl.textContent = visibleCount + ' فتوى مطابقة ✓';
            badgeEl.className = visibleCount > 0 ? 'text-emerald-400 font-mono font-black text-xs px-2.5 py-0.5 bg-emerald-950 border border-emerald-500/50 rounded-lg' : 'text-rose-400 font-mono font-black text-xs px-2.5 py-0.5 bg-rose-950 border border-rose-500/50 rounded-lg';
        }

        const noDataEl = document.getElementById('no-fatwas-alert');
        if (noDataEl) {
            noDataEl.classList.toggle('hidden', visibleCount > 0);
        }
    }

    

    function clearFiqhSearch() {
        const inp = document.getElementById('fiqh-search-input');
        if (inp) inp.value = '';
        activeScholarFilter = 'all';
        activeTopicFilter = 'all';
        document.querySelectorAll('.fiqh-filter-btn').forEach(b => {
            if (b.getAttribute('data-filter-val') === 'all') b.classList.add('active');
            else b.classList.remove('active');
        });
        filterFatwas();
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
