<?php
// preview-balances-design.php
// Excel-Style Preview Page for Balances - Pro Edition with Studio Tabs & Page Setup
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معاينة وتصميم الأرصدة | Al-Sajana ERP</title>
    <!-- Professional Arabic & Number Fonts Suite -->
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;600;800&family=Almarai:wght@400;700;800&family=Amiri:ital,wght@0,400;0,700;1,400&family=Cairo:wght@400;600;800;900&family=Changa:wght@400;600;800&family=Tajawal:wght@400;500;700;800;900&family=JetBrains+Mono:wght@400;600;800&family=Roboto+Mono:wght@400;600;800&family=Fira+Code:wght@400;600;800&family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    
    <style id="dynamic-paper-style">
        @page { size: A4 landscape; margin: 10mm; }
    </style>

    <style>
        :root {
            --bg-page: #f1f5f9;
            --text-color: #0f172a;
            --ribbon-bg: #1e293b;
            --ribbon-text: #f8fafc;
            --table-border: #cbd5e1;
            --table-header-bg: #e2e8f0;
            --table-bg: #ffffff;
            --num-font-family: 'JetBrains Mono', monospace;
        }
        body.dark-mode {
            --bg-page: #0b0f19;
            --text-color: #f8fafc;
            --ribbon-bg: #090d16;
            --ribbon-text: #f8fafc;
            --table-border: #334155;
            --table-header-bg: #1e293b;
            --table-bg: #0f172a;
        }

        /* Themes */
        table.excel-table.theme-gold th { background: #b45309 !important; color: #ffffff !important; border-color: #78350f !important; }
        table.excel-table.theme-gold td { border-color: #fde68a !important; }
        
        table.excel-table.theme-lightblue th { background: #0284c7 !important; color: #ffffff !important; border-color: #0369a1 !important; }
        table.excel-table.theme-lightblue td { border-color: #bae6fd !important; }
        
        table.excel-table.theme-slate th { background: #475569 !important; color: #ffffff !important; border-color: #334155 !important; }
        table.excel-table.theme-slate td { border-color: #cbd5e1 !important; }

        /* Fonts */
        .font-newsons { font-family: 'JetBrains Mono', monospace !important; }
        .font-ribot { font-family: 'Roboto Mono', monospace !important; }
        .font-firacode { font-family: 'Fira Code', monospace !important; }
        .font-ultra { font-family: 'Inter', sans-serif !important; }
        .font-bolt { font-family: 'Poppins', sans-serif !important; }

        /* Hide injected global navigation sidebar/header on preview page */
        .global-sidebar, header.premium-header, .global-header, .navbar {
            display: none !important;
        }

        body {
            margin: 0; padding: 0;
            font-family: 'Tajawal', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-color);
            transition: background 0.3s, color 0.3s;
        }

        /* Luxury Ribbon Toolbar with Pill Buttons */
        .ribbon {
            position: sticky; top: 0; z-index: 1000;
            background: var(--ribbon-bg); color: var(--ribbon-text);
            padding: 10px 20px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            border-bottom: 3px solid #0284c7;
            flex-wrap: wrap; gap: 10px;
        }
        .ribbon-btn {
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.25);
            color: white; padding: 7px 16px; border-radius: 20px; font-family: inherit; font-weight: 800;
            cursor: pointer; transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1); font-size: 0.85rem; display: flex; align-items: center; gap: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        .ribbon-btn:hover {
            transform: translateY(-2px);
            background: rgba(56,189,248,0.25); border-color: #38bdf8;
            box-shadow: 0 4px 12px rgba(56,189,248,0.3);
        }
        .ribbon-btn:active { transform: translateY(0); }
        
        .preset-btn {
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.25);
            color: #f1f5f9; padding: 7px 16px; border-radius: 20px; font-family: inherit; font-size: 0.95rem;
            cursor: pointer; transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1); font-weight: 800; display: inline-flex; align-items: center; gap: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.18);
        }
        .preset-btn.active {
            background: linear-gradient(135deg, #10b981, #059669); border-color: #34d399; color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(16,185,129,0.45); transform: translateY(-1px);
        }
        .preset-btn:hover:not(.active) {
            background: rgba(56,189,248,0.25); border-color: #38bdf8; color: #ffffff !important; transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(56,189,248,0.3);
        }

        /* Studio Tabs Dark-Gold Header Bar */
        .studio-tabs {
            display: flex; justify-content: space-between; align-items: center;
            background: linear-gradient(135deg, #ca8a04, #854d0e);
            color: #ffffff; padding: 8px 14px; border-radius: 10px;
            margin-bottom: 12px; box-shadow: 0 4px 12px rgba(133, 77, 14, 0.25);
        }
        body.dark-mode .studio-tabs {
            background: linear-gradient(135deg, #854d0e, #451a03);
        }
        .studio-tab-btn {
            background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.3);
            color: #ffffff !important; padding: 6px 18px; border-radius: 18px;
            font-family: inherit; font-weight: 800; cursor: pointer; transition: all 0.25s; font-size: 0.88rem;
        }
        .studio-tab-btn.active {
            background: #ffffff !important; color: #854d0e !important; font-weight: 900;
            box-shadow: 0 3px 10px rgba(0,0,0,0.25); border-color: #ffffff; transform: translateY(-1px);
        }
        .studio-tab-btn:hover:not(.active) {
            background: rgba(255, 255, 255, 0.3); color: #ffffff !important;
        }

        /* Yellow Studio Dropdown Panel with Slide-Down Animation */
        @keyframes studioSlideDown {
            from { opacity: 0; transform: translateY(-12px); max-height: 0; }
            to { opacity: 1; transform: translateY(0); max-height: 500px; }
        }

        .settings-panel {
            background: #fef9c3; /* Yellow-100 */
            color: #713f12; /* Yellow-900 */
            padding: 14px 20px; border-bottom: 4px solid #eab308;
            display: none; /* Hidden by default */
            font-size: 0.95rem; font-weight: 800;
            box-shadow: 0 10px 25px rgba(0,0,0,0.18);
            flex-direction: column; gap: 10px;
            animation: studioSlideDown 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            overflow: hidden;
        }
        body.dark-mode .settings-panel {
            background: #1c1507; color: #fef08a; border-bottom-color: #ca8a04;
        }
        
        .panel-card {
            background: rgba(255,255,255,0.7);
            border: 1px solid rgba(202, 138, 4, 0.4);
            border-radius: 8px; padding: 10px 14px;
            display: flex; flex-wrap: wrap; gap: 15px; align-items: center; justify-content: space-between;
        }
        body.dark-mode .panel-card {
            background: rgba(0,0,0,0.4); border-color: rgba(202, 138, 4, 0.3);
        }
        
        .panel-group { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .panel-group span { font-size: 0.95rem; font-weight: 900; }
        .panel-input {
            padding: 6px 12px; border-radius: 6px; border: 1px solid #ca8a04; font-family: inherit;
            background: #ffffff; color: #000; font-weight: 800; font-size: 0.9rem; outline: none;
        }
        .panel-input:focus { border-color: #0284c7; box-shadow: 0 0 0 2px rgba(2,132,199,0.2); }

        /* Printable Area - Realistic Solid Paper Sheet Boundary */
        .print-area {
            padding: 35px 40px;
            margin: 25px auto 50px auto;
            background: var(--table-bg);
            color: var(--text-color);
            border: 1px solid var(--table-border);
            border-radius: 4px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15), 0 2px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            box-sizing: border-box;
            position: relative;
        }

        body.dark-mode .print-area {
            border-color: #334155;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6);
        }

        .print-area.paper-portrait { max-width: 1000px; width: 94%; }
        .print-area.paper-landscape { max-width: 98%; width: 98%; }

        /* Paper sheet indicator badge */
        .print-area::before {
            content: '📄 ورقة طباعة رسمية (معاينة)';
            position: absolute;
            top: -12px;
            right: 25px;
            background: var(--ribbon-bg);
            color: var(--ribbon-text);
            padding: 3px 12px;
            border-radius: 12px;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }

        /* Company Header */
        .company-header {
            display: flex; justify-content: space-between; align-items: flex-start; height: 3.5cm;
            border-bottom: 3px solid var(--text-color); margin-bottom: 15px; padding-bottom: 10px;
            transition: all 0.3s;
        }
        .company-logo-wrap { width: 3.5cm; height: 100%; border: 1px solid var(--table-border); display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 8px; }
        .company-logo-wrap img { max-width: 100%; max-height: 100%; object-fit: contain; }

        /* Excel-like Flat Table */
        table.excel-table {
            width: 100%; border-collapse: collapse; table-layout: auto;
            background: var(--table-bg); color: inherit;
        }
        table.excel-table th, table.excel-table td {
            border: 1px solid var(--table-border);
            padding: 7px 10px; text-align: center; font-size: 0.9rem; color: inherit;
            white-space: nowrap !important; word-break: keep-all !important;
        }
        table.excel-table th {
            background: var(--table-header-bg); font-weight: 800; cursor: pointer; position: relative; user-select: none;
            white-space: nowrap !important; word-break: keep-all !important;
        }
        table.excel-table th:hover { background: rgba(56,189,248,0.25); }
        table.excel-table th.selected-col { background: #bae6fd !important; color: #0369a1 !important; border-bottom: 3px solid #0284c7 !important; }
        body.dark-mode table.excel-table th.selected-col { background: #0369a1 !important; color: #ffffff !important; border-bottom-color: #38bdf8 !important; }
        
        table.excel-table td.num-cell { font-family: var(--num-font-family); font-weight: 600; direction: ltr; }
        table.excel-table tfoot td { font-weight: 900; background: var(--table-header-bg); }
        
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .ribbon, .settings-panel, .print-area::before, #global-print-header {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #0f172a !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .print-area {
                background: #ffffff !important;
                color: inherit !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            table.excel-table {
                width: 100% !important;
                table-layout: auto !important;
            }
            table.excel-table th, table.excel-table td {
                border: 1px solid var(--table-border) !important;
                white-space: nowrap !important;
                word-break: keep-all !important;
                padding: 6px 8px !important;
            }
        }
    </style>
</head>
<body>

<!-- Luxury Ribbon Toolbar -->
<div class="ribbon no-print">
    <!-- Right Side: 6 Presets -->
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <div id="presets-container" style="display:flex; gap:8px; flex-wrap: wrap;">
            <!-- Preset buttons rendered via JS -->
        </div>
    </div>

    <!-- Center/Left Side: Controls & Live Search -->
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <!-- Live Quick Search Box -->
        <input type="text" id="live-search-input" placeholder="🔍 بحث سريع..." oninput="filterTableRows(this.value)" style="padding: 6px 14px; border-radius: 18px; border: 1px solid rgba(255,255,255,0.3); background: rgba(0,0,0,0.3); color: #fff; font-family: inherit; font-size: 0.85rem; outline: none; width: 140px;">

        <button class="ribbon-btn" onclick="executePrintWithPermission()" style="background: linear-gradient(135deg, #0284c7, #0369a1); border:none; box-shadow:0 2px 8px rgba(3,105,161,0.4);">
            🖨️ طباعة
        </button>
        <button class="ribbon-btn" onclick="toggleSettingsPanel()" style="background: linear-gradient(135deg, #eab308, #ca8a04); border:none; box-shadow:0 2px 8px rgba(202,138,4,0.4);">
            ⚙️ إعدادات الأعمدة
        </button>
        <button class="ribbon-btn" onclick="toggleDarkMode()">🌙 عكس الألوان</button>
        <button class="ribbon-btn" onclick="window.history.back()" style="color:#f87171; border-color:rgba(239,68,68,0.5);">❌ إغلاق / رجوع</button>
    </div>
</div>

<!-- Pro Studio Dropdown Panel with Slide Animation -->
<div class="settings-panel no-print" id="settings-panel">
    <!-- Studio Tabs Header -->
    <div class="studio-tabs" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ca8a04; margin-bottom: 10px; padding-bottom: 6px;">
        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
            <button class="studio-tab-btn active" id="tab-btn-cols" onclick="switchStudioTab('cols')">📊 إعدادات الأعمدة والتنسيق</button>
            <button class="studio-tab-btn" id="tab-btn-paper" onclick="switchStudioTab('paper')">📄 إعدادات الورقة والطباعة</button>
            <button class="studio-tab-btn" id="tab-btn-presets" onclick="switchStudioTab('presets')">💎 التخصيصات الجاهزة</button>
        </div>

        <button class="ribbon-btn" onclick="saveCurrentPreset()" style="background: linear-gradient(135deg, #10b981, #059669); color:#fff; border:none; padding:5px 16px; font-size:0.85rem; font-weight:800; border-radius:18px; box-shadow: 0 2px 8px rgba(16,185,129,0.3);">
            💾 حفظ الإعدادات
        </button>
    </div>

    <!-- TAB 1: Columns & Formatting -->
    <div id="studio-tab-cols">
        <!-- Row 1: Column Selection & Reordering -->
        <div class="panel-card" style="margin-bottom: 8px;">
            <div class="panel-group">
                <span>العمود المحدد:</span>
                <select id="selected-col-select" class="panel-input" style="color:#dc2626; font-size:0.95rem; font-weight:900; border-color:#dc2626; background:#ffffff;" onchange="selectColumn(parseInt(this.value))">
                    <!-- Column options populated dynamically via JS -->
                </select>
                
                <label style="display:flex; align-items:center; gap:4px; cursor:pointer;">
                    <input type="checkbox" id="col-visible-chk" checked onchange="updateSelectedCol('visible', this.checked)">
                    إظهار
                </label>

                <!-- Restore Hidden Columns Dropdown -->
                <select id="restore-hidden-col-select" class="panel-input" style="background:#fffeb3; border-color:#d97706;" onchange="restoreHiddenColumn(this.value)">
                    <option value="">👁️ الأعمدة المخفية (0)</option>
                </select>
            </div>

            <div class="panel-group">
                <span>إعادة تسمية الترويسة:</span>
                <input type="text" id="col-label-input" class="panel-input" style="width:130px;" oninput="updateSelectedCol('label', this.value)">
                
                <span>الترتيب:</span>
                <button class="ribbon-btn" style="padding:3px 10px; color:#000; background:#fff; border-color:#ca8a04;" onclick="moveSelectedCol(-1)">➡️ يمين</button>
                <button class="ribbon-btn" style="padding:3px 10px; color:#000; background:#fff; border-color:#ca8a04;" onclick="moveSelectedCol(1)">⬅️ يسار</button>
            </div>

            <div class="panel-group">
                <span>المحاذاة:</span>
                <select id="col-align-select" class="panel-input" onchange="updateSelectedCol('align', this.value)">
                    <option value="right">يمين ➡️</option>
                    <option value="center" selected>وسط 🟢</option>
                    <option value="left">يسار ⬅️</option>
                </select>
            </div>
        </div>

        <!-- Row 2: Colors, Font Sizes, Font Weights & Global Themes -->
        <div class="panel-card" style="margin-bottom: 8px;">
            <div class="panel-group">
                <span>حجم الخط:</span>
                <input type="range" id="col-fs-range" min="0.6" max="2.0" step="0.1" value="0.9" oninput="updateSelectedCol('fs', parseFloat(this.value))">
                <span id="col-fs-val">0.9rem</span>

                <span>وزن الخط:</span>
                <select id="col-fw-select" class="panel-input" onchange="updateSelectedCol('fontWeight', this.value)">
                    <option value="400">عادي (Normal 400)</option>
                    <option value="500">متوسط (Medium 500)</option>
                    <option value="600">بارز (Semi-Bold 600)</option>
                    <option value="700">عريض (Bold 700)</option>
                    <option value="800" selected>عريض جداً (Extra-Bold 800)</option>
                    <option value="900">أسود داكن (Black 900)</option>
                </select>

                <span>نوع الخط:</span>
                <select id="col-font-select" class="panel-input" onchange="updateSelectedCol('fontFamily', this.value)">
                    <option value="inherit">الافتراضي (افتراضي المستند)</option>
                    <option value="'Tajawal', sans-serif">Tajawal (تجوال العصري)</option>
                    <option value="'Cairo', sans-serif">Cairo (القاهرة الفاخر)</option>
                    <option value="'Almarai', sans-serif">Almarai (المراعي النظيف)</option>
                    <option value="'Alexandria', sans-serif">Alexandria (الإسكندرية الحديث)</option>
                    <option value="'Changa', sans-serif">Changa (شانغا الهندسي)</option>
                    <option value="'Amiri', serif">Amiri (الأميري الكلاسيكي)</option>
                    <option value="'JetBrains Mono', monospace">JetBrains Mono (رقمي برمجيات)</option>
                </select>

                <span>لون الخط:</span>
                <input type="color" id="col-color-picker" oninput="updateSelectedCol('color', this.value)">

                <span>خلفية العمود:</span>
                <input type="color" id="col-bg-picker" value="#ffffff" oninput="updateSelectedCol('bgColor', this.value)">
            </div>

            <div class="panel-group">
                <span>✨ أنماط العرض الفاخرة:</span>
                <select id="table-theme-select" class="panel-input" style="font-weight:800;" onchange="setTableTheme(this.value)">
                    <option value="">⚪ 1. الأساسي المعياري</option>
                    <option value="theme-gold">🟡 2. الأصفر الغامق (Luxury Gold)</option>
                    <option value="theme-lightblue">🔵 3. الأزرق الفاتح (Ocean Blue)</option>
                    <option value="theme-slate">⚪ 4. الرمادي الراقي (Slate Gray)</option>
                </select>
            </div>
        </div>

        <!-- Row 3: Number Formatting, Decimals, Separators & Rounding -->
        <div class="panel-card">
            <div class="panel-group">
                <span>الفاصلة الألفية:</span>
                <select id="col-sep-select" class="panel-input" onchange="updateSelectedCol('thousandSep', this.value)">
                    <option value="," selected>كومة ( 1,000 )</option>
                    <option value=".">نقطة ( 1.000 )</option>
                    <option value=" ">مسافة ( 1 000 )</option>
                    <option value="'">اقتباس ( 1'000 )</option>
                    <option value="_">شريط ( 1_000 )</option>
                </select>

                <span>الخانات العشرية:</span>
                <select id="col-dec-select" class="panel-input" onchange="updateSelectedCol('decimals', parseInt(this.value))">
                    <option value="0">0 (بدون كسور)</option>
                    <option value="1">1 (0.0)</option>
                    <option value="2" selected>2 (0.00)</option>
                    <option value="3">3 (0.000)</option>
                    <option value="4">4 (0.0000)</option>
                </select>

                <label style="display:flex; align-items:center; gap:4px; cursor:pointer;">
                    <input type="checkbox" id="col-dec-sep-chk" checked onchange="updateSelectedCol('showDecSep', this.checked)">
                    إظهار فاصلة الكسور
                </label>
            </div>

            <div class="panel-group">
                <span>التقريب:</span>
                <select id="col-rounding-select" class="panel-input" onchange="updateSelectedCol('rounding', this.value)">
                    <option value="nearest" selected>تقريب عادي (Math.round)</option>
                    <option value="up">للأعلى (Math.ceil)</option>
                    <option value="down">للأسفل (Math.floor)</option>
                    <option value="cut">قطع الكسر بدون تقريب</option>
                </select>

                <span>خط الأرقام:</span>
                <select id="num-font-select" class="panel-input" onchange="setNumberFont(this.value)">
                    <option value="font-newsons" selected>1. New Sons (JetBrains Mono)</option>
                    <option value="font-ribot">2. Ribot (Roboto Mono)</option>
                    <option value="font-firacode">3. Fira Code (الحسابي)</option>
                    <option value="font-ultra">4. Ultra (Inter المريح)</option>
                    <option value="font-bolt">5. Bolt (Poppins الفاخر)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- TAB 2: Paper & Print Setup -->
    <div id="studio-tab-paper" style="display:none;">
        <div class="panel-card" style="margin-bottom: 8px;">
            <div class="panel-group">
                <span>📏 مقاس الورقة (Paper Size):</span>
                <select id="paper-size-select" class="panel-input" onchange="setPaperSize(this.value)">
                    <option value="A4" selected>A4 (المعياري - 210x297 mm)</option>
                    <option value="A3">A3 (الموسع - 297x420 mm)</option>
                    <option value="A5">A5 (المصغر - 148x210 mm)</option>
                    <option value="Letter">Letter (الرسائل - 8.5x11 in)</option>
                    <option value="Legal">Legal (المستندات - 8.5x14 in)</option>
                </select>
            </div>

            <div class="panel-group">
                <span>📐 اتجاه الورقة (Orientation):</span>
                <select id="paper-orient-select" class="panel-input" onchange="setPageOrientation(this.value)">
                    <option value="landscape" selected>📜 عرضي (Landscape)</option>
                    <option value="portrait">📄 طولي (Portrait)</option>
                </select>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-group">
                <span>👁️ التحكم بعناصر الترويسة العليا:</span>
                
                <label style="display:flex; align-items:center; gap:5px; cursor:pointer; background:rgba(255,255,255,0.6); padding:5px 12px; border-radius:6px; border:1px solid #ca8a04;">
                    <input type="checkbox" id="hdr-show-logo-chk" checked onchange="toggleHeaderElement('logo', this.checked)">
                    إظهار شعار الشركة (Logo)
                </label>

                <label style="display:flex; align-items:center; gap:5px; cursor:pointer; background:rgba(255,255,255,0.6); padding:5px 12px; border-radius:6px; border:1px solid #ca8a04;">
                    <input type="checkbox" id="hdr-show-info-chk" checked onchange="toggleHeaderElement('info', this.checked)">
                    إظهار اسم وبيانات الشركة (الهاتف والتفاصيل)
                </label>
            </div>
        </div>
    </div>

    <!-- TAB 3: Presets Management -->
    <div id="studio-tab-presets" style="display:none;">
        <div class="panel-card" style="margin-bottom: 10px; justify-content: space-between; align-items: center;">
            <span style="font-size:0.95rem; font-weight:800;">💎 إدارة التخصيصات الجاهزة:</span>
            <button class="ribbon-btn" onclick="addNewPresetPrompt()" style="background: linear-gradient(135deg, #10b981, #059669); color:#fff; border:none; padding:5px 16px; font-weight:800; border-radius:18px;">
                ➕ إضافة تخصيص جديد
            </button>
        </div>

        <div id="presets-management-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 10px;">
            <!-- Presets list items rendered via JS -->
        </div>
    </div>
</div>

<!-- Print Area - Paper Sheet Layout View -->
<div class="print-area paper-landscape" id="print-area-wrapper">
    <!-- Company Header Area -->
    <div class="company-header report-header" id="main-company-header">
        <!-- Right Side: Company Details -->
        <div id="company-info-block" style="flex: 1; text-align: right; color: var(--text-color);">
            <h2 id="hdr-c-name" style="margin: 0 0 4px 0; font-size: 1.5rem; font-weight: 900;"></h2>
            <p id="hdr-c-reg" style="margin: 0 0 2px 0; font-size: 0.9rem; font-weight: 600;"></p>
            <p id="hdr-c-vat" style="margin: 0 0 2px 0; font-size: 0.9rem; font-weight: 600;"></p>
            <p id="hdr-c-phone" style="margin: 0 0 2px 0; font-size: 0.9rem; font-weight: 600;"></p>
            <p id="hdr-c-addr" style="margin: 0 0 2px 0; font-size: 0.9rem; font-weight: 600;"></p>
        </div>

        <!-- Center Side: Report Title -->
        <div style="flex: 1; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
            <h2 style="margin: 0; padding: 8px 18px; background: rgba(0,0,0,0.04); border-radius: 8px; border: 2px solid var(--text-color); font-size: 1.3rem;">تقرير الأرصدة التجميعية</h2>
            <p style="margin: 6px 0 0 0; font-size: 0.95rem; opacity: 0.85;" id="report-date-lbl"></p>
        </div>

        <!-- Left Side: Logo Area -->
        <div id="company-logo-block" style="flex: 1; text-align: left; display: flex; align-items: center; justify-content: flex-end; height: 100%;">
            <div class="company-logo-wrap">
                <img id="company-logo" src="" alt="شعار الشركة" style="max-height: 100%; max-width: 100%; object-fit: contain;">
            </div>
        </div>
    </div>

    <!-- Table -->
    <table class="excel-table font-newsons" id="preview-table">
        <thead id="preview-thead">
            <!-- Headers rendered via JS -->
        </thead>
        <tbody id="preview-tbody">
            <!-- Body rendered via JS -->
        </tbody>
        <tfoot id="preview-tfoot">
            <!-- Summary totals row rendered via JS -->
        </tfoot>
    </table>
</div>

<script src="js/localforage.min.js"></script>
<script src="js/app.js?v=<?php echo time(); ?>"></script>
<script src="js/preview-balances.js?v=<?php echo time(); ?>"></script>
</body>
</html>
