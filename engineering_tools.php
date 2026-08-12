<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - حزمة الأدوات الهندسية والمساعدة V2
// شاشة الأدوات المساعدة الشاملة (حسابات حديد، خرسانات، طاقة شمسية، واستيراد)
// ====================================================================
$page_title = 'الأدوات المساعدة والحاسبات الهندسية';
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
    <title>الأدوات المساعدة والحاسبات الهندسية | Al-Sajana ERP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;800&family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #04020a;
            --panel-bg: rgba(14, 8, 28, 0.75);
            --border-neon: rgba(236, 72, 153, 0.25);
            --text-main: #ffffff;
            --text-muted: #a0aec0;
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
            padding: 12px 12px 60px 12px !important;
            box-sizing: border-box !important;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .tool-tab-btn {
            background: rgba(30, 41, 59, 0.6);
            color: var(--text-muted);
            border: 2px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 14px 20px;
            font-weight: 800;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        :root.light-theme .tool-tab-btn {
            background: #f1f5f9;
            color: #475569;
            border: 2px solid #cbd5e1;
        }

        .tool-tab-btn.active {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.25), rgba(168, 85, 247, 0.25));
            color: #f472b6;
            border-color: #ec4899;
            box-shadow: 0 10px 25px rgba(236, 72, 153, 0.25);
        }

        :root.light-theme .tool-tab-btn.active {
            background: #db2777;
            color: #ffffff;
            border-color: #be185d;
        }

        .glass-panel-v2 {
            background: var(--panel-bg);
            border: 2px solid var(--border-neon);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.3);
        }

        :root.light-theme .glass-panel-v2 {
            background: #ffffff !important;
            border: 3px solid #000000 !important;
            color: #000000 !important;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06) !important;
        }

        .tool-input {
            background: rgba(15, 23, 42, 0.8);
            border: 2px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
            padding: 10px 14px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
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
            box-shadow: 0 0 15px rgba(236, 72, 153, 0.3);
        }

        .calc-result-box {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.12), rgba(168, 85, 247, 0.12));
            border: 2px solid rgba(236, 72, 153, 0.3);
            border-radius: 16px;
            padding: 18px;
            text-align: center;
        }

        :root.light-theme .calc-result-box {
            background: #fdf2f8;
            border: 2px solid #db2777;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .anim { animation: fadeInUp 0.4s ease-out backwards; }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .tools-master-wrapper { padding: 0 !important; }
            .glass-panel-v2 { border: 1px solid #000 !important; box-shadow: none !important; }
        }
    </style>
</head>

<body class="no-page-transition">

    <div class="tools-master-wrapper">
        
        <?php include "includes/header.php"; ?>

        <!-- 🌟 ACTION CONTROLS & NAVIGATION BAR 🌟 -->
        <div class="flex flex-wrap items-center justify-between gap-4 no-print bg-slate-900/80 dark:bg-slate-900/90 border-2 border-pink-500/30 p-5 rounded-2xl shadow-xl backdrop-blur-xl">
            <div>
                <a href="smart_reports_hub.php" onclick="window.location.href='smart_reports_hub.php'; return false;" class="inline-flex items-center gap-2 text-pink-400 font-extrabold hover:underline text-sm mb-1.5">
                    <i class="fa-solid fa-arrow-right"></i> ↩️ رجوع لمركز التقارير المحاسبية V2
                </a>
                <h1 class="text-2xl md:text-3xl font-black flex items-center gap-3">
                    <i class="fa-solid fa-toolbox text-pink-400"></i>
                    حزمة الأدوات المساعدة والحاسبات الهندسية الاحترافية
                </h1>
                <p class="text-slate-400 text-xs md:text-sm mt-1 font-bold">حسابات أوزان الحديد والمواسير والصاج، التقدير الإنشائي للخرسانات والأسقف، منظومات الطاقة الشمسية، والتكاليف.</p>
            </div>

            <div class="flex items-center gap-3">
                <button onclick="window.print();" class="bg-pink-600 hover:bg-pink-500 text-white font-black px-5 py-2.5 rounded-xl text-sm transition shadow-lg flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> طباعة كشف التقديرات
                </button>
            </div>
        </div>

        <!-- 🎛️ TOOL SUITE TABS NAVIGATION 🎛️ -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 no-print">
            <button onclick="switchTab('steel')" id="tab-btn-steel" class="tool-tab-btn active">
                <i class="fa-solid fa-cubes-stacked text-xl"></i>
                <span>أوزان وقطع الحديد</span>
            </button>
            <button onclick="switchTab('concrete')" id="tab-btn-concrete" class="tool-tab-btn">
                <i class="fa-solid fa-building text-xl"></i>
                <span>الخرسانات والأسقف</span>
            </button>
            <button onclick="switchTab('solar')" id="tab-btn-solar" class="tool-tab-btn">
                <i class="fa-solid fa-solar-panel text-xl"></i>
                <span>الطاقة الشمسية</span>
            </button>
            <button onclick="switchTab('freight')" id="tab-btn-freight" class="tool-tab-btn">
                <i class="fa-solid fa-ship text-xl"></i>
                <span>التكاليف والجمارك</span>
            </button>
            <button onclick="switchTab('units')" id="tab-btn-units" class="tool-tab-btn">
                <i class="fa-solid fa-calculator text-xl"></i>
                <span>التحويلات الهندسية</span>
            </button>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- TOOL 1: STEEL & METALS WEIGHT & PIECE CALCULATOR                -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="tool-sec-steel" class="tool-section anim space-y-6">
            <div class="glass-panel-v2">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-cubes-stacked text-pink-400"></i>
                        حاسبة أوزان وعدد حبات طن الحديد (مواسير - صاج - تسليح - كمارات)
                    </h3>
                    <span class="text-xs font-bold text-pink-400 bg-pink-500/10 px-3 py-1 rounded-lg border border-pink-500/20">
                        معادلات التصنيع القياسية (ISO / DIN Standard)
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Inputs Form -->
                    <div class="space-y-4 lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">نوع القطاع / المنتج:</label>
                                <select id="steel_product_type" onchange="calculateSteel()" class="tool-input">
                                    <option value="pipe_rect">مواسير وعلب مستطيلة/مربعة (مثال: 4×8، 2×4 سم)</option>
                                    <option value="pipe_round">مواسير دائرية (Pipes)</option>
                                    <option value="sheet_zinc">صاج مضلع / زنك / ألواح صاج مسطحة</option>
                                    <option value="rebar">حديد تسليح مباني (Rebar Bars)</option>
                                    <option value="beam_ipe">كمارات وقنوات (IPE / HEA / UPN)</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">المقاس / السُمك المطلوب:</label>
                                <select id="steel_preset_size" onchange="calculateSteel()" class="tool-input">
                                    <option value="4x8_1.5">ماسورة 4 × 8 سم (سُمك 1.5 مم - طول 6م)</option>
                                    <option value="4x8_2.0">ماسورة 4 × 8 سم (سُمك 2.0 مم - طول 6م)</option>
                                    <option value="2x4_1.5">ماسورة 2 × 4 سم (سُمك 1.5 مم - طول 6م)</option>
                                    <option value="2x4_2.0">ماسورة 2 × 4 سم (سُمك 2.0 مم - طول 6م)</option>
                                    <option value="zinc_0.3">زنك / صاج مموج (سُمك 0.30 مم - 2.5م × 0.9م)</option>
                                    <option value="zinc_0.4">زنك / صاج مموج (سُمك 0.40 مم - 3.0م × 0.9م)</option>
                                    <option value="rebar_8mm">حديد تسليح 8 مم (لينة)</option>
                                    <option value="rebar_10mm">حديد تسليح 10 مم (3 لينية)</option>
                                    <option value="rebar_12mm">حديد تسليح 12 مم (4 لينية)</option>
                                    <option value="rebar_16mm">حديد تسليح 16 مم (5 لينية)</option>
                                    <option value="rebar_20mm">حديد تسليح 20 مم (6 لينية)</option>
                                    <option value="custom">مقاس وحجم مخصص (Custom Dimensions)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">العرض (مم):</label>
                                <input type="number" id="steel_width_mm" value="40" oninput="calculateSteel()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">الارتفاع (مم):</label>
                                <input type="number" id="steel_height_mm" value="80" oninput="calculateSteel()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">السُمك (مم):</label>
                                <input type="number" step="0.1" id="steel_thickness_mm" value="1.5" oninput="calculateSteel()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">الطول (متر):</label>
                                <input type="number" step="0.5" id="steel_length_m" value="6.0" oninput="calculateSteel()" class="tool-input">
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-300 block mb-1">الكمية الإجمالية المطلوبة (طن / قطعة):</label>
                            <div class="flex items-center gap-3">
                                <input type="number" id="steel_target_qty" value="1.0" oninput="calculateSteel()" class="tool-input font-bold text-lg text-pink-400">
                                <select id="steel_target_unit" onchange="calculateSteel()" class="tool-input w-36">
                                    <option value="ton">طن (Ton)</option>
                                    <option value="pcs">حبة / قطعة (Pcs)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Calculated Results -->
                    <div class="space-y-4">
                        <div class="calc-result-box">
                            <span class="text-xs text-slate-400 font-bold block">📦 عدد الحبات / القطع في الطن الواحد</span>
                            <span class="text-4xl font-black text-pink-400 mt-2 block font-mono" id="res_pieces_per_ton">0</span>
                            <span class="text-xs text-pink-300/80 font-bold mt-1 block">حبة / لوح لكل طن حديد</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(56, 189, 248, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">⚖️ وزن الحبة / القطعة الواحدة</span>
                            <span class="text-3xl font-black text-sky-400 mt-2 block font-mono" id="res_weight_per_piece">0.00 kg</span>
                            <span class="text-xs text-sky-300/80 font-bold mt-1 block">كيلوجرام للقطعة الواحدة</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(16, 185, 129, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">📊 إجمالي الوزن والقطع للكمية المطلوبة</span>
                            <span class="text-2xl font-black text-emerald-400 mt-2 block font-mono" id="res_total_summary">0 قطعة = 0.00 طن</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- TOOL 2: STRUCTURAL CONCRETE & REBAR CALCULATOR                   -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="tool-sec-concrete" class="tool-section anim hidden space-y-6">
            <div class="glass-panel-v2">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-building text-pink-400"></i>
                        حاسبة كميات الخرسانة، الأسقف، وحديد التسليح للمباني الهيكلية
                    </h3>
                    <span class="text-xs font-bold text-pink-400 bg-pink-500/10 px-3 py-1 rounded-lg border border-pink-500/20">
                        تقدير دقيق للكميات والمواد الخام
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4 lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">نوع السقف / العنصر الإنشائي:</label>
                                <select id="concrete_type" onchange="calculateConcrete()" class="tool-input">
                                    <option value="beam_slab">سقف كمري عادي (Beam with Slab)</option>
                                    <option value="hordy">سقف هوردي (Hollow Block / Joist Slab)</option>
                                    <option value="flat_slab">سقف لاكمري (Flat Slab)</option>
                                    <option value="building_skeleton">هيكل مبنى كامل (قواعد + أعمدة + أسقف)</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">مساحة السقف / المبنى ($m^2$):</label>
                                <input type="number" id="concrete_area" value="150" oninput="calculateConcrete()" class="tool-input text-lg font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">سُمك البلاطة ($cm$):</label>
                                <input type="number" id="concrete_thickness" value="15" oninput="calculateConcrete()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">عدد الطوابق (للظرف الإجمالي):</label>
                                <input type="number" id="concrete_floors" value="1" oninput="calculateConcrete()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">نسبة التسليح ($kg/m^3$):</label>
                                <input type="number" id="concrete_rebar_ratio" value="110" oninput="calculateConcrete()" class="tool-input">
                            </div>
                        </div>
                    </div>

                    <!-- Calculated Concrete Results -->
                    <div class="space-y-3">
                        <div class="calc-result-box">
                            <span class="text-xs text-slate-400 font-bold block">🏗️ حجم الخرسانة المسلحة المطلوبة</span>
                            <span class="text-3xl font-black text-pink-400 mt-1 block font-mono" id="res_concrete_m3">0.00 m³</span>
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
                                <span class="text-slate-400 block">بلوك هوردي (إن وجد):</span>
                                <span class="text-purple-400 text-lg block font-mono" id="res_hordy_blocks">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- TOOL 3: SOLAR POWER & BATTERY SYSTEM CALCULATOR                  -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="tool-sec-solar" class="tool-section anim hidden space-y-6">
            <div class="glass-panel-v2">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-solar-panel text-pink-400"></i>
                        حاسبة منظومات الطاقة الشمسية، عدد الألواح، والأحمال الكهربائية
                    </h3>
                    <span class="text-xs font-bold text-pink-400 bg-pink-500/10 px-3 py-1 rounded-lg border border-pink-500/20">
                        حساب دقيق للمكيفات والأجهزة والبطاريات
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4 lg:col-span-2">
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">مكيفات فريون (1.5 طن / 18000 BTU):</label>
                                <input type="number" id="solar_ac_count" value="1" oninput="calculateSolar()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">ثلاجات / ديب فريزر:</label>
                                <input type="number" id="solar_fridge_count" value="1" oninput="calculateSolar()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">مضخات مياه / طلمبات (حصان):</label>
                                <input type="number" id="solar_pump_hp" value="1" oninput="calculateSolar()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">إنارة وشاشات ومراوح (وات):</label>
                                <input type="number" id="solar_lights_watt" value="500" oninput="calculateSolar()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">ساعات تشغيل البطاريات ليلاً:</label>
                                <input type="number" id="solar_night_hours" value="8" oninput="calculateSolar()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">قدرة لوح الطاقة (وات):</label>
                                <input type="number" id="solar_panel_watt" value="550" oninput="calculateSolar()" class="tool-input">
                            </div>
                        </div>
                    </div>

                    <!-- Calculated Solar Results -->
                    <div class="space-y-3">
                        <div class="calc-result-box">
                            <span class="text-xs text-slate-400 font-bold block">⚡ قدرة الانفرتر الموصى بها (Inverter)</span>
                            <span class="text-3xl font-black text-amber-400 mt-1 block font-mono" id="res_inverter_kw">0 kW</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(16, 185, 129, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">☀️ عدد ألواح الطاقة الشمسية (550W)</span>
                            <span class="text-3xl font-black text-emerald-400 mt-1 block font-mono" id="res_panel_count">0 لوح</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(56, 189, 248, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">🔋 سعة بنك البطاريات المطلوب (48V)</span>
                            <span class="text-2xl font-black text-sky-400 mt-1 block font-mono" id="res_battery_ah">0 Ah</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- TOOL 4: FREIGHT & IMPORT LANDED COST CALCULATOR                 -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="tool-sec-freight" class="tool-section anim hidden space-y-6">
            <div class="glass-panel-v2">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-ship text-pink-400"></i>
                        حاسبة تكاليف الاستيراد، الشحن البحري، التخليص والجمارك (Landed Cost)
                    </h3>
                    <span class="text-xs font-bold text-pink-400 bg-pink-500/10 px-3 py-1 rounded-lg border border-pink-500/20">
                        حساب التكلفة الإجمالية الواصلة للمخزن
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4 lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">قيمة فاتورة الشراء FOB ($):</label>
                                <input type="number" id="freight_fob_usd" value="50000" oninput="calculateFreight()" class="tool-input text-lg font-bold">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">تكلفة الشحن البحري الإجمالية ($):</label>
                                <input type="number" id="freight_sea_usd" value="3500" oninput="calculateFreight()" class="tool-input text-lg font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">الرسوم الجمركية (%):</label>
                                <input type="number" id="freight_customs_pct" value="15" oninput="calculateFreight()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">رسوم الموانئ والتخليص ($):</label>
                                <input type="number" id="freight_clearance_usd" value="1200" oninput="calculateFreight()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">سعر صرف الجمارك/الدولار:</label>
                                <input type="number" id="freight_fx_rate" value="600" oninput="calculateFreight()" class="tool-input">
                            </div>
                        </div>
                    </div>

                    <!-- Calculated Freight Results -->
                    <div class="space-y-3">
                        <div class="calc-result-box">
                            <span class="text-xs text-slate-400 font-bold block">💵 التكلفة الإجمالية الواصلة بالدولار</span>
                            <span class="text-3xl font-black text-pink-400 mt-1 block font-mono" id="res_landed_usd">$0</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(56, 189, 248, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">💰 التكلفة الإجمالية بالعملة المحلية (SDG)</span>
                            <span class="text-2xl font-black text-sky-400 mt-1 block font-mono" id="res_landed_sdg">0 SDG</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(16, 185, 129, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">📈 نسبة التكاليف المضافة إلى الشراء</span>
                            <span class="text-2xl font-black text-emerald-400 mt-1 block font-mono" id="res_overhead_pct">%0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- TOOL 5: UNIT CONVERSION CALCULATOR                               -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="tool-sec-units" class="tool-section anim hidden space-y-6">
            <div class="glass-panel-v2">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-calculator text-pink-400"></i>
                        حاسبة التحويلات الهندسية الشاملة (أطوال - أوزان - مساحات - ضغط)
                    </h3>
                    <span class="text-xs font-bold text-pink-400 bg-pink-500/10 px-3 py-1 rounded-lg border border-pink-500/20">
                        تحويلات سريعة ومباشرة
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Inches to mm -->
                    <div class="bg-slate-900/60 p-4 rounded-xl border border-slate-800 space-y-2">
                        <span class="text-xs font-bold text-pink-400 block">بوصة (Inch) ⬅️➡️ مليمتر (mm)</span>
                        <input type="number" id="unit_inch" value="4" oninput="convertUnits('inch')" class="tool-input" placeholder="بوصة">
                        <input type="number" id="unit_mm" value="101.6" oninput="convertUnits('mm')" class="tool-input" placeholder="مليمتر">
                    </div>

                    <!-- Kg to Ton -->
                    <div class="bg-slate-900/60 p-4 rounded-xl border border-slate-800 space-y-2">
                        <span class="text-xs font-bold text-sky-400 block">كيلوجرام (kg) ⬅️➡️ طن (Ton)</span>
                        <input type="number" id="unit_kg" value="1000" oninput="convertUnits('kg')" class="tool-input" placeholder="كجم">
                        <input type="number" id="unit_ton" value="1" oninput="convertUnits('ton')" class="tool-input" placeholder="طن">
                    </div>

                    <!-- Sqm to Sqft / Feddan -->
                    <div class="bg-slate-900/60 p-4 rounded-xl border border-slate-800 space-y-2">
                        <span class="text-xs font-bold text-emerald-400 block">متر مربع ($m^2$) ⬅️➡️ فدان</span>
                        <input type="number" id="unit_sqm" value="4200" oninput="convertUnits('sqm')" class="tool-input" placeholder="متر مربع">
                        <input type="number" id="unit_feddan" value="1" oninput="convertUnits('feddan')" class="tool-input" placeholder="فدان">
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center text-xs text-slate-500 font-bold mt-2 no-print">
            نظام مقاييس ERP 2026 · الأدوات والمساعدات الهندسية المعتمدة
        </div>

    </div>

    <!-- Interactive Calculation Scripts -->
    <script>
    function switchTab(tabId) {
        document.querySelectorAll('.tool-section').forEach(sec => sec.classList.add('hidden'));
        document.querySelectorAll('.tool-tab-btn').forEach(btn => btn.classList.remove('active'));

        const targetSec = document.getElementById('tool-sec-' + tabId);
        const targetBtn = document.getElementById('tab-btn-' + tabId);

        if (targetSec) targetSec.classList.remove('hidden');
        if (targetBtn) targetBtn.classList.add('active');
    }

    // 1. Steel & Metals Calculation
    function calculateSteel() {
        const preset = document.getElementById('steel_preset_size').value;
        const targetQty = parseFloat(document.getElementById('steel_target_qty').value || 1);
        const targetUnit = document.getElementById('steel_target_unit').value;

        let w_mm = parseFloat(document.getElementById('steel_width_mm').value || 40);
        let h_mm = parseFloat(document.getElementById('steel_height_mm').value || 80);
        let t_mm = parseFloat(document.getElementById('steel_thickness_mm').value || 1.5);
        let L_m = parseFloat(document.getElementById('steel_length_m').value || 6.0);

        // Apply presets if chosen
        if (preset === '4x8_1.5') { w_mm = 40; h_mm = 80; t_mm = 1.5; L_m = 6.0; }
        else if (preset === '4x8_2.0') { w_mm = 40; h_mm = 80; t_mm = 2.0; L_m = 6.0; }
        else if (preset === '2x4_1.5') { w_mm = 20; h_mm = 40; t_mm = 1.5; L_m = 6.0; }
        else if (preset === '2x4_2.0') { w_mm = 20; h_mm = 40; t_mm = 2.0; L_m = 6.0; }
        else if (preset === 'rebar_12mm') { w_mm = 12; t_mm = 12; L_m = 12.0; }

        if (preset !== 'custom') {
            document.getElementById('steel_width_mm').value = w_mm;
            document.getElementById('steel_height_mm').value = h_mm;
            document.getElementById('steel_thickness_mm').value = t_mm;
            document.getElementById('steel_length_m').value = L_m;
        }

        // Calculate Weight per piece (Steel density = 7850 kg/m^3)
        let weightPerPieceKg = 0;
        const prodType = document.getElementById('steel_product_type').value;

        if (prodType === 'rebar') {
            // Rebar d^2 / 162 * length
            const d = w_mm;
            weightPerPieceKg = (d * d / 162.162) * L_m;
        } else {
            // Rectangular tube perimeter * thickness * 7850 * length
            const perimeter_m = ((w_mm + h_mm) * 2) / 1000;
            const thickness_m = t_mm / 1000;
            weightPerPieceKg = perimeter_m * thickness_m * 7850 * L_m;
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
        document.getElementById('res_total_summary').textContent = `${totalPieces.toLocaleString()} قطعة = ${totalTons.toFixed(2)} طن`;
    }

    // 2. Concrete Calculation
    function calculateConcrete() {
        const area = parseFloat(document.getElementById('concrete_area').value || 100);
        const thickness_cm = parseFloat(document.getElementById('concrete_thickness').value || 15);
        const floors = parseFloat(document.getElementById('concrete_floors').value || 1);
        const rebarRatio = parseFloat(document.getElementById('concrete_rebar_ratio').value || 110);

        const vol_m3 = (area * (thickness_cm / 100)) * 1.25 * floors; // 25% extra for beams
        const rebar_tons = (vol_m3 * rebarRatio) / 1000;
        const cement_bags = Math.ceil(vol_m3 * 7);
        const hordy_blocks = Math.ceil(area * 12 * floors);

        document.getElementById('res_concrete_m3').textContent = vol_m3.toFixed(2) + ' m³';
        document.getElementById('res_rebar_tons').textContent = rebar_tons.toFixed(2) + ' طن';
        document.getElementById('res_cement_bags').textContent = cement_bags.toLocaleString();
        document.getElementById('res_hordy_blocks').textContent = hordy_blocks.toLocaleString();
    }

    // 3. Solar System Calculation
    function calculateSolar() {
        const acCount = parseFloat(document.getElementById('solar_ac_count').value || 0);
        const fridgeCount = parseFloat(document.getElementById('solar_fridge_count').value || 0);
        const pumpHp = parseFloat(document.getElementById('solar_pump_hp').value || 0);
        const lightsWatt = parseFloat(document.getElementById('solar_lights_watt').value || 0);
        const nightHours = parseFloat(document.getElementById('solar_night_hours').value || 8);
        const panelWatt = parseFloat(document.getElementById('solar_panel_watt').value || 550);

        const totalRunningWatt = (acCount * 1800) + (fridgeCount * 350) + (pumpHp * 750) + lightsWatt;
        const inverterKw = Math.ceil((totalRunningWatt * 1.3) / 1000); // 30% safety margin

        const dailyKwh = (totalRunningWatt * 10) / 1000;
        const panelCount = Math.ceil((dailyKwh * 1000) / (panelWatt * 4.5)); // 4.5 peak sun hours

        const nightWh = (totalRunningWatt * 0.4) * nightHours;
        const batteryAh48V = Math.ceil((nightWh / 48) * 1.25);

        document.getElementById('res_inverter_kw').textContent = inverterKw + ' kW / KVA';
        document.getElementById('res_panel_count').textContent = panelCount + ' لوح (' + panelWatt + 'W)';
        document.getElementById('res_battery_ah').textContent = batteryAh48V + ' Ah (48V)';
    }

    // 4. Freight Calculation
    function calculateFreight() {
        const fob = parseFloat(document.getElementById('freight_fob_usd').value || 0);
        const sea = parseFloat(document.getElementById('freight_sea_usd').value || 0);
        const customsPct = parseFloat(document.getElementById('freight_customs_pct').value || 0);
        const clearance = parseFloat(document.getElementById('freight_clearance_usd').value || 0);
        const fx = parseFloat(document.getElementById('freight_fx_rate').value || 600);

        const customsUsd = (fob + sea) * (customsPct / 100);
        const totalLandedUsd = fob + sea + customsUsd + clearance;
        const totalLandedSdg = totalLandedUsd * fx;
        const overheadPct = fob > 0 ? (((totalLandedUsd - fob) / fob) * 100).toFixed(1) : 0;

        document.getElementById('res_landed_usd').textContent = '$' + totalLandedUsd.toLocaleString(undefined, {maximumFractionDigits:2});
        document.getElementById('res_landed_sdg').textContent = totalLandedSdg.toLocaleString(undefined, {maximumFractionDigits:0}) + ' SDG';
        document.getElementById('res_overhead_pct').textContent = '%' + overheadPct;
    }

    // 5. Unit Converter
    function convertUnits(source) {
        if (source === 'inch') {
            const v = parseFloat(document.getElementById('unit_inch').value || 0);
            document.getElementById('unit_mm').value = (v * 25.4).toFixed(2);
        } else if (source === 'mm') {
            const v = parseFloat(document.getElementById('unit_mm').value || 0);
            document.getElementById('unit_inch').value = (v / 25.4).toFixed(3);
        } else if (source === 'kg') {
            const v = parseFloat(document.getElementById('unit_kg').value || 0);
            document.getElementById('unit_ton').value = (v / 1000).toFixed(3);
        } else if (source === 'ton') {
            const v = parseFloat(document.getElementById('unit_ton').value || 0);
            document.getElementById('unit_kg').value = (v * 1000).toFixed(0);
        } else if (source === 'sqm') {
            const v = parseFloat(document.getElementById('unit_sqm').value || 0);
            document.getElementById('unit_feddan').value = (v / 4200).toFixed(3);
        } else if (source === 'feddan') {
            const v = parseFloat(document.getElementById('unit_feddan').value || 0);
            document.getElementById('unit_sqm').value = (v * 4200).toFixed(0);
        }
    }

    // Run initial calculations on load
    document.addEventListener('DOMContentLoaded', function() {
        calculateSteel();
        calculateConcrete();
        calculateSolar();
        calculateFreight();
    });
    </script>
    <script defer src="js/app.js"></script>
</body>
</html>
