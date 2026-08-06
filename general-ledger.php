<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$page_title = 'دفتر الأستاذ (SGL) - Launcher';
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
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;800&family=Fira+Code:wght@400;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دفتر الأستاذ | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css?v=20260726">
    <link rel="stylesheet" href="css/theme.css?v=20260726">
    <link rel="stylesheet" href="css/components.css?v=20260726">
    <link rel="stylesheet" href="css/sales-invoice.css?v=20260726">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        /* ── Modern Launcher Styling ── */
        .report-header { 
            background: linear-gradient(135deg, rgba(168,85,247,0.15), rgba(15,23,42,0.9)); 
            border: 1px solid rgba(168,85,247,0.2); 
            border-radius: 14px; 
            padding: 18px 24px; 
            margin-bottom: 12px; 
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
            position: relative;
            z-index: 100;
        }
        :root.light-theme .report-header {
            background: linear-gradient(135deg, #ffffff, #f8fafc) !important;
            border-color: rgba(168,85,247,0.3) !important;
        }
        
        .filter-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 14px; }
        .filter-group { display: flex; flex-direction: column; gap: 6px; position: relative; }
        .filter-group label { font-size:0.85rem; font-weight:800; color:#c084fc; display:flex; align-items:center; gap:4px; }
        :root.light-theme .filter-group label { color:#9333ea !important; }
        
        .filter-input { 
            width: 100%; 
            background: rgba(0,0,0,0.3); 
            border: 1px solid rgba(255,255,255,0.1); 
            color: #f8fafc; 
            padding: 8px 12px; 
            border-radius: 8px; 
            font-family: inherit; 
            font-size: 0.9rem; 
            font-weight: 700;
            transition: all 0.2s; 
            box-sizing: border-box;
            height: 40px;
        }
        .filter-input:focus { border-color: #c084fc; outline: none; background: rgba(0,0,0,0.5); box-shadow: 0 0 0 3px rgba(168,85,247,0.15); }
        :root.light-theme .filter-input { background: #ffffff !important; color: #0f172a !important; border-color: rgba(0,0,0,0.1) !important; }
        
        /* AutoComplete Styles */
        .search-wrap { display:flex; width: 100%; align-items: center; position: relative; }
        .search-clear-btn { position:absolute; left:8px; top:50%; transform:translateY(-50%); background:none; border:none; color:#f87171; cursor:pointer; display:none; padding:4px; z-index:10; }
        .autocomplete-list { position:absolute; top:calc(100% + 4px); right:0; left:0; background:#1e293b; border:1px solid #334155; border-radius:8px; list-style:none; margin:0; padding:4px; display:none; max-height:250px; overflow-y:auto; z-index:1000; box-shadow:0 10px 25px rgba(0,0,0,0.5); }
        .autocomplete-list li { padding:8px 12px; cursor:pointer; color:#f8fafc; font-size:0.85rem; border-radius:6px; margin-bottom:2px; font-weight:bold; display:flex; justify-content:space-between; }
        .autocomplete-list li:hover, .autocomplete-list li.active { background:#38bdf8; color:#0f172a; }
        :root.light-theme .autocomplete-list { background:#ffffff !important; border-color:#e2e8f0 !important; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        :root.light-theme .autocomplete-list li { color:#1e293b !important; }
        :root.light-theme .autocomplete-list li:hover { background:#e0f2fe !important; color:#0284c7 !important; }
        
        .selected-account-chip { display:none; align-items:center; gap:8px; width:100%; background:rgba(168,85,247,0.15); border:1px solid rgba(168,85,247,0.4); border-radius:8px; padding:0 12px; height:40px; box-sizing:border-box; }
        :root.light-theme .selected-account-chip { background:rgba(168,85,247,0.08) !important; border-color:rgba(168,85,247,0.3) !important; }
        
        .btn-run { background: linear-gradient(135deg, #a855f7, #7e22ce); color: #ffffff; border: none; padding: 8px 20px; border-radius: 8px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content:center; gap: 8px; transition: all 0.3s; font-family: inherit; font-size: 0.95rem; height: 40px; box-shadow: 0 4px 15px rgba(126, 34, 206, 0.4); }
        .btn-run:hover { background: linear-gradient(135deg, #c084fc, #9333ea); transform: translateY(-2px); box-shadow: 0 8px 25px rgba(126, 34, 206, 0.6); }

        .btn-action-secondary { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); color: #f8fafc; padding: 8px 16px; border-radius: 8px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content:center; gap: 6px; transition: all 0.3s; font-family: inherit; font-size: 0.88rem; height: 40px; }
        .btn-action-secondary:hover { background: rgba(255,255,255,0.18); transform: translateY(-2px); }
        :root.light-theme .btn-action-secondary { background: #ffffff !important; border-color: rgba(0,0,0,0.15) !important; color: #0f172a !important; }
        
        /* Preset Buttons styles */
        .preset-slots-container {
            background: rgba(15, 23, 42, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            backdrop-filter: blur(8px);
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.2);
            flex-wrap: wrap;
            gap: 10px;
        }
        :root.light-theme .preset-slots-container {
            background: #ffffff !important;
            border-color: rgba(0,0,0,0.1) !important;
        }
        .presets-slots-wrap { flex-grow: 1; display: flex; gap: 8px; align-items: center; }
        .preset-slot-btn {
            position: relative; display: inline-flex; align-items: center; justify-content: center; height: 38px; font-size: 0.85rem; padding: 0 14px; border-radius: 10px; font-weight: 700; font-family: inherit; cursor: pointer; flex: 1; min-width: 80px; transition: all 0.3s; user-select: none; text-align: center; box-sizing: border-box; overflow:hidden;
        }
        .preset-slot-btn .hold-bar { position: absolute; bottom: 0; left: 0; height: 3px; width: 0%; border-bottom-left-radius: 9px; border-bottom-right-radius: 9px; }
        
        /* 1: Purple */
        .preset-slot-btn[data-id="1"] { background: rgba(168,85,247,0.05); border: 1px dashed rgba(168,85,247,0.3); color: rgba(168,85,247,0.8); }
        .preset-slot-btn[data-id="1"]:hover, .preset-slot-btn[data-id="1"].has-data { background: rgba(168,85,247,0.15); border: 1px solid rgba(168,85,247,0.5); color: #c084fc; }
        .preset-slot-btn[data-id="1"] .hold-bar { background: #a855f7; }
        
        /* 2: Blue */
        .preset-slot-btn[data-id="2"] { background: rgba(56,189,248,0.05); border: 1px dashed rgba(56,189,248,0.3); color: rgba(56,189,248,0.8); }
        .preset-slot-btn[data-id="2"]:hover, .preset-slot-btn[data-id="2"].has-data { background: rgba(56,189,248,0.15); border: 1px solid rgba(56,189,248,0.5); color: #38bdf8; }
        .preset-slot-btn[data-id="2"] .hold-bar { background: #38bdf8; }
        
        /* 3: Emerald */
        .preset-slot-btn[data-id="3"] { background: rgba(16,185,129,0.05); border: 1px dashed rgba(16,185,129,0.3); color: rgba(16,185,129,0.8); }
        .preset-slot-btn[data-id="3"]:hover, .preset-slot-btn[data-id="3"].has-data { background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.5); color: #34d399; }
        .preset-slot-btn[data-id="3"] .hold-bar { background: #10b981; }
        
        /* 4: Amber */
        .preset-slot-btn[data-id="4"] { background: rgba(245,158,11,0.05); border: 1px dashed rgba(245,158,11,0.3); color: rgba(245,158,11,0.8); }
        .preset-slot-btn[data-id="4"]:hover, .preset-slot-btn[data-id="4"].has-data { background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.5); color: #fbbf24; }
        .preset-slot-btn[data-id="4"] .hold-bar { background: #f59e0b; }
        
        /* 5: Rose */
        .preset-slot-btn[data-id="5"] { background: rgba(244,63,94,0.05); border: 1px dashed rgba(244,63,94,0.3); color: rgba(244,63,94,0.8); }
        .preset-slot-btn[data-id="5"]:hover, .preset-slot-btn[data-id="5"].has-data { background: rgba(244,63,94,0.15); border: 1px solid rgba(244,63,94,0.5); color: #fb7185; }
        .preset-slot-btn[data-id="5"] .hold-bar { background: #f43f5e; }

        .preset-slot-btn.has-data { box-shadow: 0 4px 15px rgba(255,255,255,0.05); animation: pulse 2s infinite ease-in-out; }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.02); } }

        .pos-grid { width: 100%; border-collapse: collapse; font-family: 'Tajawal', sans-serif; }
        .pos-grid th, .pos-grid td { padding: 8px 10px; font-size: 0.85rem; text-align: right; border-bottom: 1px solid rgba(255,255,255,0.05); color:#f8fafc; }
        .pos-grid thead th { background: rgba(15, 23, 42, 0.95); color: #c084fc; font-weight: 800; border-bottom: 2px solid #a855f7; position: sticky; top: 0; z-index: 10; }
        :root.light-theme .pos-grid th, :root.light-theme .pos-grid td { color: #0f172a !important; border-color: rgba(0,0,0,0.05) !important; }
        :root.light-theme .pos-grid thead th { background: #f1f5f9 !important; color: #9333ea !important; border-bottom: 2px solid #9333ea !important; }
        .pos-grid tbody tr:hover td { background: rgba(168,85,247,0.08); }
        :root.light-theme .pos-grid tbody tr:hover td { background: rgba(168,85,247,0.05) !important; }
        
        .grid-scroll-wrap {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            overflow-x: auto;
        }
        :root.light-theme .grid-scroll-wrap { background:#ffffff !important; border-color:rgba(0,0,0,0.1) !important; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }

        .summary-card-val {
            font-size: 1.4rem; font-weight: 900; color: #f8fafc; font-family: 'JetBrains Mono', monospace;
        }
        :root.light-theme .summary-card-val {
            color: #0f172a !important;
        }

        /* Table Themes */
        table.table-theme-blue th { background: linear-gradient(135deg, #1e3a8a, #1d4ed8) !important; color: #ffffff !important; }
        table.table-theme-blue td { border-color: rgba(59, 130, 246, 0.2) !important; }

        table.table-theme-black th { background: linear-gradient(135deg, #0f172a, #1e293b) !important; color: #f8fafc !important; }
        table.table-theme-black td { border-color: rgba(255, 255, 255, 0.08) !important; }

        table.table-theme-emerald th { background: linear-gradient(135deg, #065f46, #047857) !important; color: #ffffff !important; }
        table.table-theme-emerald td { border-color: rgba(16, 185, 129, 0.2) !important; }

        /* Number Fonts */
        .num-font-jetbrains { font-family: 'JetBrains Mono', monospace !important; }
        .num-font-roboto { font-family: 'Roboto Mono', monospace !important; }
        .num-font-firacode { font-family: 'Fira Code', monospace !important; }
        .num-font-inter { font-family: 'Inter', sans-serif !important; }
        .num-font-poppins { font-family: 'Poppins', sans-serif !important; }

        /* Cumulative Cell Magnification on Click */
        td.focused-num {
            transform: scale(1.45) translateY(-2px) !important;
            color: #38bdf8 !important;
            font-weight: 900 !important;
            background: rgba(15, 23, 42, 0.96) !important;
            border-radius: 8px !important;
            box-shadow: 0 0 30px rgba(56, 189, 248, 0.7), inset 0 0 0 2px #38bdf8 !important;
            z-index: 1000 !important;
            position: relative !important;
            display: inline-block !important;
            padding: 4px 12px !important;
            transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.8) !important;
        }
        :root.light-theme td.focused-num {
            color: #0284c7 !important;
            background: #ffffff !important;
            box-shadow: 0 10px 30px rgba(2, 132, 199, 0.35), inset 0 0 0 2px #0284c7 !important;
            text-shadow: none !important;
        }

        /* Col Config Item in Context Menu */
        .col-config-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            padding: 8px 12px;
            transition: all 0.2s;
        }
        .col-config-item:hover {
            background: rgba(56, 189, 248, 0.1);
            border-color: rgba(56, 189, 248, 0.3);
        }
    </style>
</head>
<body class="has-sidebar">
<!-- Unified Context Menu for Columns & Data Formatting in General Ledger -->
<div id="columnContextMenu" style="display:none; position:fixed; z-index:1000000; top:50%; left:50%; transform:translate(-50%, -50%); background:rgba(15, 23, 42, 0.98); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border:2px solid rgba(56, 189, 248, 0.5); border-radius:18px; box-shadow:0 25px 70px rgba(0,0,0,0.85), 0 0 35px rgba(56, 189, 248, 0.3); padding:16px 20px 20px 20px; width:600px; color:#f8fafc; font-family:'Tajawal', 'Inter', sans-serif; max-height:88vh; overflow:auto; resize:both; min-width:340px; min-height:200px; direction:rtl; text-align:right;">
    <!-- Draggable Header -->
    <div id="columnContextMenuHeader" style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.15); cursor:move; user-select:none; background:rgba(255,255,255,0.05); margin:-16px -20px 14px -20px; padding:14px 20px; border-top-left-radius:16px; border-top-right-radius:16px;">
        <div style="font-weight:900; font-size:1.1rem; color:#38bdf8; display:flex; align-items:center; gap:8px;">
            <span style="font-size:1.2rem; color:#fbbf24;" title="اضغط واسحب للتحريك في أي مكان">⠿</span>
            <span>⚙️</span> تخصيص وإعدادات الأعمدة (إسحب للتحريك)
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <!-- High Visibility Red Close Button -->
            <button type="button" title="إغلاق النافذة" onclick="document.getElementById('columnContextMenu').style.display='none'" style="cursor:pointer; font-weight:900; font-size:1.15rem; color:#ffffff !important; border:1px solid rgba(255,255,255,0.4); padding:4px 14px; border-radius:8px; background:linear-gradient(135deg, #ef4444, #dc2626) !important; box-shadow:0 4px 15px rgba(239,68,68,0.5); transition:all 0.2s; display:flex; align-items:center; gap:4px;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                <span>✕</span> <span>إغلاق</span>
            </button>
        </div>
    </div>

    <!-- Menu Content Container -->
    <div id="columnContextMenuBody">
        <!-- Navigation Tabs inside Context Menu -->
        <div style="display:flex; gap:6px; margin-bottom:14px; background:rgba(0,0,0,0.25); padding:4px; border-radius:10px; border:1px solid rgba(255,255,255,0.08);">
            <button type="button" id="tab-btn-cols" onclick="switchCtxTab('cols')" style="flex:1; padding:8px; border:none; border-radius:8px; background:#38bdf8; color:#0f172a; font-weight:900; font-family:inherit; cursor:pointer; font-size:0.85rem; transition:all 0.2s;">
                📊 ترتيب الأعمدة والإظهار
            </button>
            <button type="button" id="tab-btn-formatting" onclick="switchCtxTab('formatting')" style="flex:1; padding:8px; border:none; border-radius:8px; background:transparent; color:#94a3b8; font-weight:700; font-family:inherit; cursor:pointer; font-size:0.85rem; transition:all 0.2s;">
                🎨 الثيمات والخطوط والألوان
            </button>
        </div>

        <!-- Tab 1: Columns & Visibility -->
        <div id="ctx-tab-cols">
            <!-- Info Banner -->
            <div style="background:rgba(56, 189, 248, 0.1); border:1px solid rgba(56, 189, 248, 0.2); border-radius:10px; padding:10px 12px; margin-bottom:14px; font-size:0.8rem; color:#7dd3fc; line-height:1.5; display:flex; align-items:center; gap:8px;">
                <span style="font-size:1.1rem;">💡</span>
                <span>اسحب مقبض (<b>⣿</b>) أو استخدم أزرار ⬆️ ⬇️ لترتيب الأعمدة، وافتح ⚙️ لتخصيص خط ولون وكسور وحدود كل عمود بشكل مستقل!</span>
            </div>

            <!-- Dynamic Column Items -->
            <div id="columnToggles" style="display:flex; flex-direction:column; gap:10px;"></div>
        </div>

        <!-- Tab 2: Formatting, Themes & Number Fonts -->
        <div id="ctx-tab-formatting" style="display:none;">
            <!-- Crystal Themes Selector -->
            <div style="margin-bottom:16px; background:rgba(0,0,0,0.2); padding:14px; border-radius:12px; border:1px solid rgba(255,255,255,0.08);">
                <label style="display:block; margin-bottom:10px; font-weight:800; color:#fbbf24;">✨ أنماط العرض الفاخرة (Crystal Themes):</label>
                <div class="crystal-theme-container" style="display:grid; grid-template-columns:repeat(2, 1fr); gap:8px;">
                    <button type="button" class="crystal-btn c-std active" onclick="changeTableTheme('')" style="padding:8px; border-radius:8px; cursor:pointer;">⚪ الأساسي (Default)</button>
                    <button type="button" class="crystal-btn c-blue" onclick="changeTableTheme('table-theme-blue')" style="padding:8px; border-radius:8px; cursor:pointer;">🔵 الأزرق العميق</button>
                    <button type="button" class="crystal-btn c-black" onclick="changeTableTheme('table-theme-black')" style="padding:8px; border-radius:8px; cursor:pointer;">⚫ الأسود الفاخر</button>
                    <button type="button" class="crystal-btn c-emerald" onclick="changeTableTheme('table-theme-emerald')" style="padding:8px; border-radius:8px; cursor:pointer;">🟢 الزمردي المالي</button>
                </div>
            </div>

            <!-- Global Number Colors Customizer -->
            <div style="margin-bottom:16px; background:rgba(0,0,0,0.25); padding:14px; border-radius:12px; border:1px solid rgba(56,189,248,0.2);">
                <label style="display:block; margin-bottom:10px; font-weight:800; color:#38bdf8;">🎨 تخصيص ألوان وحجم خط الأرقام والحسابات:</label>
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.85rem; font-weight:bold; color:#34d399;">🟢 لون أرقام المدين:</span>
                        <input type="color" value="#34d399" onchange="updateColField(7, 'color', this.value); updateColField(11, 'color', this.value);" style="border:none; width:45px; height:30px; border-radius:6px; cursor:pointer; background:transparent;">
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.85rem; font-weight:bold; color:#f87171;">🔴 لون أرقام الدائن:</span>
                        <input type="color" value="#f87171" onchange="updateColField(8, 'color', this.value); updateColField(12, 'color', this.value);" style="border:none; width:45px; height:30px; border-radius:6px; cursor:pointer; background:transparent;">
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.85rem; font-weight:bold; color:#fbbf24;">👤 لون اسم الحساب:</span>
                        <input type="color" value="#fbbf24" onchange="updateColField(3, 'color', this.value);" style="border:none; width:45px; height:30px; border-radius:6px; cursor:pointer; background:transparent;">
                    </div>
                </div>
            </div>

            <!-- Number Fonts Selector -->
            <div style="margin-bottom:16px; background:rgba(0,0,0,0.2); padding:14px; border-radius:12px; border:1px solid rgba(255,255,255,0.08);">
                <label style="display:block; margin-bottom:10px; font-weight:800; color:#38bdf8;">📝 خطوط الأرقام الفائقة الوضوح:</label>
                <div style="display:flex; flex-direction:column; gap:6px;">
                    <button type="button" class="font-chip" onclick="changeNumberFont('num-font-jetbrains')" style="font-family: 'JetBrains Mono', monospace; padding:8px 12px; border-radius:8px; text-align:right; color:#fff;">1. JetBrains Mono (مونوسبيس عالي الدقة)</button>
                    <button type="button" class="font-chip" onclick="changeNumberFont('num-font-roboto')" style="font-family: 'Roboto Mono', monospace; padding:8px 12px; border-radius:8px; text-align:right; color:#fff;">2. Roboto Mono (روبوتو المالي)</button>
                    <button type="button" class="font-chip" onclick="changeNumberFont('num-font-firacode')" style="font-family: 'Fira Code', monospace; padding:8px 12px; border-radius:8px; text-align:right; color:#fff;">3. Fira Code (فيرا كود الحسابي)</button>
                    <button type="button" class="font-chip" onclick="changeNumberFont('num-font-inter')" style="font-family: 'Inter', sans-serif; padding:8px 12px; border-radius:8px; text-align:right; color:#fff;">4. Inter Font (إنتر المريح للعين)</button>
                    <button type="button" class="font-chip" onclick="changeNumberFont('num-font-poppins')" style="font-family: 'Poppins', sans-serif; padding:8px 12px; border-radius:8px; text-align:right; color:#fff;">5. Poppins Font (بوبينز الفاخر)</button>
                </div>
            </div>
        </div>

        <!-- Common Footer Buttons -->
        <div style="border-top:1px solid rgba(255,255,255,0.1); padding-top:15px; margin-top:16px; display:flex; gap:10px;">
            <button type="button" onclick="saveLayoutAndStyles()" style="flex:1.5; padding:11px; background:linear-gradient(135deg, #10b981, #059669); color:white; border:none; border-radius:8px; font-weight:bold; font-family:inherit; cursor:pointer; box-shadow:0 4px 14px rgba(16, 185, 129, 0.35); display:flex; align-items:center; justify-content:center; gap:6px; transition:all 0.2s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">
                💾 حفظ الترتيب والتخصيص
            </button>
            <button type="button" onclick="resetLayoutAndStyles()" style="flex:1; padding:11px; background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.3); color:#ef4444; border-radius:8px; font-weight:bold; font-family:inherit; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.25)'" onmouseout="this.style.background='rgba(239,68,68,0.15)'">
                🔄 إعادة الضبط
            </button>
            <button type="button" onclick="document.getElementById('columnContextMenu').style.display='none'" style="flex:1; padding:11px; background:linear-gradient(135deg, #ef4444, #dc2626); color:white; border:none; border-radius:8px; font-weight:900; font-family:inherit; cursor:pointer; box-shadow:0 4px 14px rgba(239,68,68,0.35); display:flex; align-items:center; justify-content:center; gap:6px; transition:all 0.2s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">
                ✕ إغلاق
            </button>
        </div>
    </div>
</div>

<div class="sgl-master-container" style="padding: 10px 20px;">
    <?php $header_theme_class = 'header-theme-reports'; ?>
    <?php if(file_exists(__DIR__ . '/includes/header.php')) include __DIR__ . '/includes/header.php'; ?>

    <!-- Filter Launcher Panel -->
    <div class="report-header no-print">
        <!-- ROW 1 -->
        <div class="filter-row">
            <!-- Main Account -->
            <div class="filter-group" style="flex:2;">
                <label>🏦 الحساب الرئيسي</label>
                <div class="search-wrap" id="account-search-wrap">
                    <input type="text" class="filter-input" id="account-search-input" placeholder="ابحث باسم أو رقم الحساب..." autocomplete="off">
                    <button class="search-clear-btn" id="search-clear-btn">✕</button>
                    <ul class="autocomplete-list" id="autocomplete-list"></ul>
                </div>
                <div class="selected-account-chip" id="selected-chip">
                    <span id="chip-icon">🏷️</span>
                    <span id="chip-name" style="flex:1; font-size:0.95rem; font-weight:bold;"></span>
                    <button class="chip-remove" id="chip-remove-btn" title="إلغاء" style="background:none;border:none;color:#f87171;cursor:pointer;font-size:1.1rem;" onclick="clearAccountSearch()">✕</button>
                </div>
                <input type="hidden" id="filter-account" value="ALL">
            </div>

            <!-- Date From -->
            <div class="filter-group">
                <label>📅 من تاريخ</label>
                <input type="date" class="filter-input" id="filter-date-from" value="<?php echo date('Y-m-01'); ?>">
            </div>

            <!-- Date To -->
            <div class="filter-group">
                <label>📅 إلى تاريخ</label>
                <input type="date" class="filter-input" id="filter-date-to" value="<?php echo date('Y-m-t'); ?>">
            </div>

            <!-- Currency -->
            <div class="filter-group">
                <label>💱 العملة</label>
                <select class="filter-input" id="filter-currency">
                    <option value="ALL">جميع العملات (محلي + أجنبي)</option>
                    <option value="LOCAL">العملة المحلية فقط</option>
                    <option value="FOREIGN">العملات الأجنبية فقط</option>
                </select>
            </div>
        </div>

        <!-- ROW 2 -->
        <div class="filter-row">
            <!-- Transaction Type -->
            <div class="filter-group">
                <label>🧾 نوع الحركة</label>
                <select class="filter-input" id="filter-type">
                    <option value="ALL">جميع الحركات</option>
                    <option value="sales">مبيعات وإيرادات</option>
                    <option value="purchases">مشتريات ومصروفات</option>
                    <option value="receipt">سندات قبض</option>
                    <option value="payment">سندات صرف</option>
                    <option value="journal">قيود يومية</option>
                </select>
            </div>

            <!-- Cost Center -->
            <div class="filter-group">
                <label>🏢 مركز التكلفة</label>
                <select class="filter-input" id="filter-costcenter">
                    <option value="ALL">جميع مراكز التكلفة</option>
                </select>
            </div>

            <!-- Posting Status -->
            <div class="filter-group">
                <label>✅ حالة الترحيل</label>
                <select class="filter-input" id="filter-posted">
                    <option value="ALL">الكل (مرحل وغير مرحل)</option>
                    <option value="POSTED">المرحلة فقط</option>
                    <option value="UNPOSTED">غير المرحلة فقط (مسودة)</option>
                </select>
            </div>

            <!-- Smart Search -->
            <div class="filter-group">
                <label>🔍 بحث ذكي</label>
                <input type="text" class="filter-input" id="filter-smart" placeholder="رقم القيد، البيان، الشيك...">
            </div>
        </div>
    </div>

    <!-- Presets & Action Buttons -->
    <div class="filter-row" style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
        <div class="preset-slots-container no-print" id="presets-container" style="flex:1; display:flex; gap:6px; align-items:center; flex-wrap:wrap; padding:2px 10px; border-radius:8px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); min-width: 250px;">
            <!-- Column & Colors Control Button -->
            <button type="button" class="preset-ribbon-btn" onclick="openColumnContextMenu(event)" style="background: linear-gradient(135deg, #f59e0b, #d97706) !important; color: #ffffff !important; border: 1px solid rgba(251, 191, 36, 0.8) !important; font-weight: 900 !important; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4) !important;">⚙️ التحكم</button>
            <!-- Dynamic preset buttons rendered via JS -->
        </div>
        <button class="btn-action-secondary" onclick="shareViaWhatsApp()" title="مشاركة ملخص التقرير عبر واتساب">
            💬 واتساب
        </button>
        <button class="btn-action-secondary" onclick="openPreviewDesign()" title="طباعة مباشرة">
            🖨️ طباعة
        </button>
        <button class="btn-run" onclick="openPreviewDesign()">
            👁️ الشاشة الذكية للمعاينة والتصميم والطباعة والتخصيص
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="filter-row" style="margin-bottom: 20px;">
        <div class="filter-group" style="background: rgba(52, 211, 153, 0.1); border: 1px solid rgba(52, 211, 153, 0.3); border-radius: 12px; padding: 15px; text-align: center;">
            <div style="font-size: 0.85rem; font-weight: 800; color: #34d399; margin-bottom: 5px;">إجمالي المدين</div>
            <div id="launcher-total-debit" class="summary-card-val">0.00</div>
            <div id="launcher-total-debit-foreign" style="font-size: 0.9rem; margin-top: 5px; opacity: 0.8; font-family: 'JetBrains Mono', monospace;">0.00</div>
        </div>
        <div class="filter-group" style="background: rgba(248, 113, 113, 0.1); border: 1px solid rgba(248, 113, 113, 0.3); border-radius: 12px; padding: 15px; text-align: center;">
            <div style="font-size: 0.85rem; font-weight: 800; color: #f87171; margin-bottom: 5px;">إجمالي الدائن</div>
            <div id="launcher-total-credit" class="summary-card-val">0.00</div>
            <div id="launcher-total-credit-foreign" style="font-size: 0.9rem; margin-top: 5px; opacity: 0.8; font-family: 'JetBrains Mono', monospace;">0.00</div>
        </div>
        <div class="filter-group" style="background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 12px; padding: 15px; text-align: center;">
            <div style="font-size: 0.85rem; font-weight: 800; color: #38bdf8; margin-bottom: 5px;">الرصيد التراكمي</div>
            <div id="launcher-total-balance" class="summary-card-val">0.00</div>
            <div id="launcher-total-balance-foreign" style="font-size: 0.9rem; margin-top: 5px; opacity: 0.8; font-family: 'JetBrains Mono', monospace;">0.00</div>
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="grid-scroll-wrap">
        <h4 style="padding: 15px; margin:0; color:#c084fc; font-family:'Tajawal'; border-bottom:1px solid rgba(255,255,255,0.05);">🕒 أحدث العمليات (آخر 50 عملية)</h4>
        <table class="pos-grid" id="recent-table">
            <thead>
                <tr>
                    <th width="40">م</th>
                    <th width="90">التاريخ</th>
                    <th width="90">الرقم المرجعي</th>
                    <th width="140">الحساب</th>
                    <th width="90">نوع الحركة</th>
                    <th>البيان</th>
                    <th width="140">الحساب المقابل</th>
                    <th width="90">مدين</th>
                    <th width="90">دائن</th>
                    <th width="100">الإجمالي المحلي</th>
                    <th width="90">سعر الصرف</th>
                    <th width="90">مدين أجنبي</th>
                    <th width="90">دائن أجنبي</th>
                    <th width="120">الإجمالي الأجنبي</th>
                </tr>
            </thead>
            <tbody id="recent-tbody">
                <!-- Populated via JS -->
                <tr><td colspan="14" style="text-align:center; padding: 20px;">جاري تحميل البيانات...</td></tr>
            </tbody>
        </table>
    </div>

</div>

<!-- Core JS -->
<script src="js/localforage.min.js"></script>
<script src="js/app.js?v=<?php echo time(); ?>"></script>
<script src="js/general-ledger.js?v=<?php echo time(); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.renderLedgerPresets === 'function') {
        window.renderLedgerPresets();
    }
});
</script>
</body>
</html>
