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
        .pos-grid th, .pos-grid td { padding: 8px 10px; font-size: 0.85rem; text-align: right; border-bottom: 1px solid rgba(255,255,255,0.05); color:#f8fafc; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; }
        .pos-grid thead th { background: rgba(15, 23, 42, 0.95); color: #c084fc; font-weight: 800; border-bottom: 2px solid #a855f7; position: sticky; top: 0; z-index: 10; }
        :root.light-theme .pos-grid th, :root.light-theme .pos-grid td { color: #0f172a !important; border-color: rgba(0,0,0,0.05) !important; }
        :root.light-theme .pos-grid thead th { background: #f1f5f9 !important; color: #9333ea !important; border-bottom: 2px solid #9333ea !important; }
        .pos-grid tbody tr:hover td { background: rgba(168,85,247,0.08); }
        :root.light-theme .pos-grid tbody tr:hover td { background: rgba(168,85,247,0.05) !important; }
        
        .pos-grid tbody tr { transform: none !important; opacity: 1; }
        
        .resizer {
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 7px;
            user-select: none;
            z-index: 30;
            background: transparent;
            transition: background 0.2s, box-shadow 0.2s;
        }
        .resizer:hover, .resizer.resizing {
            background: #38bdf8 !important;
            box-shadow: 0 0 10px #38bdf8;
        }
        
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

        /* ── Magnified Number Audit Effect ── */
        .pos-grid td.num-magnified {
            font-size: 1.85rem !important;
            font-weight: 900 !important;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.4), rgba(217, 119, 6, 0.5)) !important;
            color: #ffffff !important;
            border: 2px solid #fbbf24 !important;
            border-radius: 8px !important;
            box-shadow: 0 0 25px rgba(245, 158, 11, 0.7), inset 0 0 10px rgba(255, 255, 255, 0.4) !important;
            transform: scale(1.15) !important;
            transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            z-index: 50 !important;
            position: relative !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.6) !important;
            padding: 8px 12px !important;
            display: table-cell !important;
        }
        :root.light-theme .pos-grid td.num-magnified {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
            color: #ffffff !important;
        }

        /* ── Custom Context Menu Styling ── */
        .gl-ctx-menu {
            position: fixed;
            z-index: 99999;
            background: rgba(15, 23, 42, 0.96);
            border: 1px solid rgba(168, 85, 247, 0.4);
            border-radius: 14px;
            padding: 8px 0;
            width: 260px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.7), 0 0 20px rgba(168, 85, 247, 0.25);
            backdrop-filter: blur(16px);
            display: none;
            direction: rtl;
            font-family: 'Tajawal', sans-serif;
            user-select: none;
        }
        .gl-ctx-item {
            padding: 10px 18px;
            color: #f8fafc;
            font-size: 0.88rem;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            transition: all 0.2s;
        }
        .gl-ctx-item:hover {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.3), rgba(56, 189, 248, 0.3));
            color: #38bdf8;
            padding-right: 22px;
        }
        .gl-ctx-item.disabled {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }
        .gl-ctx-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 6px 0;
        }
        .gl-ctx-submenu {
            position: relative;
        }
        .gl-ctx-submenu-content {
            position: absolute;
            right: 100%;
            top: -4px;
            background: rgba(15, 23, 42, 0.96);
            border: 1px solid rgba(168, 85, 247, 0.4);
            border-radius: 14px;
            padding: 8px 0;
            width: 220px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.7);
            display: none;
            backdrop-filter: blur(16px);
        }
        .gl-ctx-submenu:hover .gl-ctx-submenu-content {
            display: block;
        }

        /* ── Draggable Luxury Master Column Customizer ── */
        .gl-modal-overlay {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(15, 23, 42, 0.25); backdrop-filter: blur(3px);
            z-index: 100000; display: none;
            direction: rtl; font-family: 'Tajawal', sans-serif;
            pointer-events: auto;
        }
        .gl-modal-content {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.96), rgba(15, 23, 42, 0.98));
            border: 1px solid rgba(168, 85, 247, 0.5);
            border-radius: 20px; padding: 14px 18px; width: 94%; max-width: 620px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8), 0 0 30px rgba(168, 85, 247, 0.3);
            color: #f8fafc;
            backdrop-filter: blur(16px);
            z-index: 100001;
            user-select: none;
            max-height: 90vh;
            overflow-y: auto;
            transition: width 0.3s, height 0.3s, max-width 0.3s;
        }

        .gl-col-chip {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 4px 10px; border-radius: 8px; font-size: 0.78rem; font-weight: 700;
            color: #cbd5e1; cursor: pointer; transition: all 0.2s;
        }
        .gl-col-chip:hover {
            background: rgba(168, 85, 247, 0.2); border-color: rgba(168, 85, 247, 0.5); color: #fff;
        }
        .gl-col-chip.active {
            background: rgba(56, 189, 248, 0.25); border-color: #38bdf8; color: #38bdf8;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.3);
        }
        .gl-col-chip input[type="checkbox"] {
            cursor: pointer; width: 14px; height: 14px; accent-color: #38bdf8; margin: 0;
        }

        /* Minimize & Maximize States */
        .gl-modal-content.minimized {
            height: 52px !important;
            overflow: hidden !important;
            padding-bottom: 0 !important;
            max-width: 440px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.6) !important;
        }
        .gl-modal-content.minimized .gl-preview-box,
        .gl-modal-content.minimized .gl-form-row,
        .gl-modal-content.minimized .gl-btn-group,
        .gl-modal-content.minimized #gl-quick-actions-bar {
            display: none !important;
        }

        .gl-modal-content.maximized {
            width: 95vw !important;
            max-width: 95vw !important;
            height: 92vh !important;
            top: 50% !important; left: 50% !important;
            transform: translate(-50%, -50%) !important;
            overflow-y: auto !important;
        }

        .gl-modal-ctrl-btn {
            background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.2);
            color: #cbd5e1; border-radius: 8px; width: 28px; height: 28px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.8rem; cursor: pointer; transition: all 0.2s;
        }
        .gl-modal-ctrl-btn:hover {
            background: rgba(56, 189, 248, 0.25); color: #38bdf8; border-color: #38bdf8; transform: scale(1.08);
        }

        .gl-modal-header {
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.12); padding-bottom: 8px; margin-bottom: 10px;
            cursor: grab;
            background: rgba(255,255,255,0.03);
            padding: 6px 10px;
            border-radius: 12px;
        }
        .gl-modal-header:active { cursor: grabbing; }
        .gl-modal-title { font-size: 1.05rem; font-weight: 900; color: #c084fc; display: flex; align-items: center; gap: 6px; }
        
        .gl-modal-close-btn {
            background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5);
            color: #f87171; border-radius: 50%; width: 30px; height: 30px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.05rem; cursor: pointer; transition: all 0.25s; font-weight: bold;
        }
        .gl-modal-close-btn:hover {
            background: #ef4444; color: #fff; transform: scale(1.1) rotate(90deg);
        }

        .gl-preview-box {
            background: rgba(0,0,0,0.4); border: 1px dashed rgba(168,85,247,0.4);
            border-radius: 12px; padding: 6px 10px; margin-bottom: 10px; text-align: center;
        }
        .gl-preview-table {
            width: 100%; border-collapse: collapse; margin-top: 4px;
        }
        .gl-preview-table th, .gl-preview-table td {
            padding: 6px 10px; border: 1px solid rgba(255,255,255,0.15); text-align: center; font-size: 0.85rem;
        }

        .gl-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 8px; }
        .gl-form-group { display: flex; flex-direction: column; gap: 4px; }
        .gl-form-group label { font-size: 0.78rem; font-weight: 800; color: #94a3b8; }
        .gl-input {
            background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.15);
            color: #fff; padding: 6px 10px; border-radius: 8px; font-family: inherit; font-size: 0.84rem; font-weight: 700;
        }
        .gl-input:focus { border-color: #c084fc; outline: none; box-shadow: 0 0 0 3px rgba(168,85,247,0.2); }
        .gl-input-color { height: 32px; padding: 2px; cursor: pointer; width: 100%; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.35); }
        .gl-btn-group { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.12); padding-top: 10px; }
    </style>
</head>
<body class="has-sidebar">
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
            <!-- Dynamic preset buttons rendered via JS -->
        </div>
        <button class="btn-action-secondary" onclick="resetMagnifiedNumbers()" title="إلغاء تكبير جميع الأرقام المكبرة للمراجعة">
            🔍 إلغاء تكبير الأرقام
        </button>
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
        <h4 style="padding: 15px; margin:0; color:#c084fc; font-family:'Tajawal'; border-bottom:1px solid rgba(255,255,255,0.05); display:flex; justify-content:space-between; align-items:center;">
            <span>🕒 أحدث العمليات (اضغط كليك يمين لتخصيص الأعمدة أو الذهاب للسند)</span>
            <span style="font-size:0.8rem; font-weight:normal; opacity:0.8; color:#38bdf8;">💡 انقر على أي رقم لتكبيره للمراجعة</span>
        </h4>
        <table class="pos-grid" id="recent-table">
            <thead>
                <tr>
                    <th width="40" style="position: relative; white-space: nowrap;">م<div class="resizer" title="اسحب لتغيير عرض العمود، انقر مرتين للتكيف الأوتوماتيكي"></div></th>
                    <th width="90" style="position: relative; white-space: nowrap;">التاريخ<div class="resizer" title="اسحب لتغيير عرض العمود، انقر مرتين للتكيف الأوتوماتيكي"></div></th>
                    <th width="90" style="position: relative; white-space: nowrap;">الرقم المرجعي<div class="resizer" title="اسحب لتغيير عرض العمود، انقر مرتين للتكيف الأوتوماتيكي"></div></th>
                    <th width="140" style="position: relative; white-space: nowrap;">الحساب<div class="resizer" title="اسحب لتغيير عرض العمود، انقر مرتين للتكيف الأوتوماتيكي"></div></th>
                    <th width="90" style="position: relative; white-space: nowrap;">نوع الحركة<div class="resizer" title="اسحب لتغيير عرض العمود، انقر مرتين للتكيف الأوتوماتيكي"></div></th>
                    <th style="position: relative; white-space: nowrap;">البيان<div class="resizer" title="اسحب لتغيير عرض العمود، انقر مرتين للتكيف الأوتوماتيكي"></div></th>
                    <th width="140" style="position: relative; white-space: nowrap;">الحساب المقابل<div class="resizer" title="اسحب لتغيير عرض العمود، انقر مرتين للتكيف الأوتوماتيكي"></div></th>
                    <th width="90" style="position: relative; white-space: nowrap;">مدين<div class="resizer" title="اسحب لتغيير عرض العمود، انقر مرتين للتكيف الأوتوماتيكي"></div></th>
                    <th width="90" style="position: relative; white-space: nowrap;">دائن<div class="resizer" title="اسحب لتغيير عرض العمود، انقر مرتين للتكيف الأوتوماتيكي"></div></th>
                    <th width="100" style="position: relative; white-space: nowrap;">الإجمالي المحلي<div class="resizer" title="اسحب لتغيير عرض العمود، انقر مرتين للتكيف الأوتوماتيكي"></div></th>
                    <th width="90" style="position: relative; white-space: nowrap;">سعر الصرف<div class="resizer" title="اسحب لتغيير عرض العمود، انقر مرتين للتكيف الأوتوماتيكي"></div></th>
                    <th width="90" style="position: relative; white-space: nowrap;">مدين أجنبي<div class="resizer" title="اسحب لتغيير عرض العمود، انقر مرتين للتكيف الأوتوماتيكي"></div></th>
                    <th width="90" style="position: relative; white-space: nowrap;">دائن أجنبي<div class="resizer" title="اسحب لتغيير عرض العمود، انقر مرتين للتكيف الأوتوماتيكي"></div></th>
                    <th width="120" style="position: relative; white-space: nowrap;">الإجمالي الأجنبي<div class="resizer" title="اسحب لتغيير عرض العمود، انقر مرتين للتكيف الأوتوماتيكي"></div></th>
                </tr>
            </thead>
            <tbody id="recent-tbody">
                <!-- Populated via JS -->
                <tr><td colspan="14" style="text-align:center; padding: 20px;">جاري تحميل البيانات...</td></tr>
            </tbody>
        </table>
    </div>

</div>

<!-- Context Menu HTML -->
<div id="gl-context-menu" class="gl-ctx-menu">
    <div class="gl-ctx-item" id="gl-ctx-drilldown">
        <span>🔗 الذهاب إلى أصل السند</span>
        <span style="font-size:0.75rem; color:#38bdf8;">◀</span>
    </div>
    <div class="gl-ctx-item" id="gl-ctx-toggle-zoom">
        <span>🔍 تكبير/تصغير الرقم للمراجعة</span>
        <span style="font-size:0.75rem; color:#fbbf24;">🔍</span>
    </div>
    <div class="gl-ctx-divider"></div>
    <div class="gl-ctx-item" id="gl-ctx-custom-col">
        <span>🎨 تخصيص العمود الحالي (ألوان وخطوط)</span>
        <span style="font-size:0.75rem; color:#c084fc;">⚙️</span>
    </div>
    <div class="gl-ctx-item" id="gl-ctx-hide-col">
        <span>👁️ إخفاء هذا العمود</span>
        <span style="font-size:0.75rem; color:#f87171;">✕</span>
    </div>
    <div class="gl-ctx-submenu gl-ctx-item">
        <span>👁️‍🗨️ إظهار الأعمدة المخفية</span>
        <span style="font-size:0.75rem; color:#34d399;">◀</span>
        <div class="gl-ctx-submenu-content" id="gl-ctx-hidden-list">
            <!-- Populated via JS -->
        </div>
    </div>
    <div class="gl-ctx-divider"></div>
    <div class="gl-ctx-item" id="gl-ctx-move-left">
        <span>➡️ تحريك العمود لليستار / لليمين</span>
        <span style="font-size:0.75rem; color:#38bdf8;">➡️</span>
    </div>
    <div class="gl-ctx-item" id="gl-ctx-move-right">
        <span>⬅️ تحريك العمود لليمين / لليسار</span>
        <span style="font-size:0.75rem; color:#38bdf8;">⬅️</span>
    </div>
    <div class="gl-ctx-divider"></div>
    <div class="gl-ctx-item" onclick="resetMagnifiedNumbers()">
        <span>✨ تصغير جميع الأرقام المكبرة</span>
        <span style="font-size:0.75rem; color:#38bdf8;">🔄</span>
    </div>
    <div class="gl-ctx-item" onclick="resetGLColumnsToDefault()">
        <span>🔄 إعادة ضبط كافة الأعمدة للافتراضي</span>
        <span style="font-size:0.75rem; color:#f87171;">🔄</span>
    </div>
</div>

<!-- Modal Customizer Container -->
<div id="gl-column-modal" class="gl-modal-overlay" onclick="if(event.target === this) closeGLColumnModal()">
    <div class="gl-modal-content" id="gl-modal-window">
        <div class="gl-modal-header" id="gl-draggable-header" title="انقر واسحب لتحريك النافذة في أرجاء الشاشة">
            <div class="gl-modal-title">
                <span style="font-size:1.1rem; color:#c084fc;">⋮⋮</span>
                <span>🎨 تخصيص العمود:</span>
                <span id="gl-modal-col-name" style="color:#38bdf8; font-weight:900;"></span>
            </div>
            <div style="display:flex; align-items:center; gap:5px;" onclick="event.stopPropagation()">
                <button type="button" class="gl-modal-ctrl-btn" onclick="positionGLModal('right')" title="تحريك إلى أقصى اليمين">↗️</button>
                <button type="button" class="gl-modal-ctrl-btn" onclick="positionGLModal('center')" title="تمركز في المنتصف">🎯</button>
                <button type="button" class="gl-modal-ctrl-btn" onclick="positionGLModal('left')" title="تحريك إلى أقصى اليسار">↖️</button>
                <button type="button" class="gl-modal-ctrl-btn" onclick="toggleMinimizeGLModal()" title="تصغير / طي النافذة">🗕</button>
                <button type="button" class="gl-modal-ctrl-btn" onclick="toggleMaximizeGLModal()" title="تكبير / استعادة">🗖</button>
                <button type="button" onclick="closeGLColumnModal()" class="gl-modal-close-btn" title="إغلاق النافذة العلوي (Esc)">✕</button>
            </div>
        </div>

        <input type="hidden" id="gl-modal-col-id">

        <!-- All Columns Selector Bar -->
        <div style="margin-bottom: 10px; background: rgba(0,0,0,0.35); border-radius: 12px; padding: 10px; border: 1px solid rgba(255,255,255,0.1);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                <span style="font-size:0.8rem; font-weight:bold; color:#38bdf8;">📋 الأعمدة المتاحة (تحديد لإظهار/إخفاء أو انقر للتنسيق):</span>
                <span style="font-size:0.75rem; color:#94a3b8;">انقر اسم العمود لتنسيقه ⚙️</span>
            </div>
            <div id="gl-modal-columns-list" style="display:flex; gap:6px; flex-wrap:wrap; max-height:100px; overflow-y:auto; padding:2px;">
                <!-- Populated via JS -->
            </div>
        </div>

        <!-- Quick Action Buttons Bar inside Modal -->
        <div id="gl-quick-actions-bar" style="margin-bottom: 10px; display: flex; gap: 6px; flex-wrap: wrap;">
            <button type="button" id="gl-modal-action-zoom" class="btn-action-secondary" style="font-size:0.75rem; height:30px; padding:0 10px; background:rgba(245,158,11,0.15); border-color:rgba(245,158,11,0.4); color:#fbbf24;">
                🔍 تكبير الرقم للمراجعة
            </button>
            <button type="button" id="gl-modal-action-hide" class="btn-action-secondary" style="font-size:0.75rem; height:30px; padding:0 10px; color:#f87171; border-color:rgba(239,68,68,0.4);">
                👁️ إخفاء العمود الحالي
            </button>
            <button type="button" id="gl-modal-action-move-left" class="btn-action-secondary" style="font-size:0.75rem; height:30px; padding:0 10px;">
                ➡️ تحريك يسار
            </button>
            <button type="button" id="gl-modal-action-move-right" class="btn-action-secondary" style="font-size:0.75rem; height:30px; padding:0 10px;">
                ⬅️ تحريك يمين
            </button>
        </div>

        <!-- Live Preview Box -->
        <div class="gl-preview-box" id="gl-preview-box">
            <div style="font-size:0.75rem; color:#c084fc; font-weight:bold; margin-bottom:4px;">👁️ المعاينة الحية المباشرة (Live Preview)</div>
            <table class="gl-preview-table">
                <thead>
                    <tr>
                        <th id="gl-prev-th" style="background:#0f172a; color:#c084fc; font-family:'Tajawal';">اسم العمود</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td id="gl-prev-td" style="background:#0f172a; color:#f87171; font-family:'Tajawal';">12,500.00</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="gl-form-row">
            <div class="gl-form-group">
                <label>خلفية ترويسة العمود</label>
                <input type="color" id="gl-cfg-header-bg" class="gl-input-color" value="#0f172a" oninput="updateGLModalLivePreview()">
            </div>
            <div class="gl-form-group">
                <label>لون نص ترويسة العمود</label>
                <input type="color" id="gl-cfg-header-color" class="gl-input-color" value="#c084fc" oninput="updateGLModalLivePreview()">
            </div>
        </div>
        <div class="gl-form-row">
            <div class="gl-form-group">
                <label>خلفية خلايا العمود</label>
                <input type="color" id="gl-cfg-cell-bg" class="gl-input-color" value="#0f172a" oninput="updateGLModalLivePreview()">
            </div>
            <div class="gl-form-group">
                <label>لون نص خلايا العمود</label>
                <input type="color" id="gl-cfg-cell-color" class="gl-input-color" value="#f8fafc" oninput="updateGLModalLivePreview()">
            </div>
        </div>
        <div class="gl-form-row">
            <div class="gl-form-group">
                <label>نوع الخط (Font Family)</label>
                <select id="gl-cfg-font" class="gl-input" onchange="updateGLModalLivePreview()">
                    <option value="Tajawal">Tajawal (تاواجل)</option>
                    <option value="Cairo">Cairo (القاهرة)</option>
                    <option value="Inter">Inter</option>
                    <option value="JetBrains Mono">JetBrains Mono</option>
                    <option value="Fira Code">Fira Code</option>
                    <option value="Alexandria">Alexandria</option>
                    <option value="Almarai">Almarai</option>
                </select>
            </div>
            <div class="gl-form-group">
                <label>سمك الخط (Font Weight)</label>
                <select id="gl-cfg-weight" class="gl-input" onchange="updateGLModalLivePreview()">
                    <option value="400">عادي (Normal)</option>
                    <option value="600">متوسط (Semi Bold)</option>
                    <option value="700">عريض (Bold)</option>
                    <option value="800">عريض جداً (Extra Bold)</option>
                    <option value="900">سماكة فائقة (Black)</option>
                </select>
            </div>
        </div>
        <div class="gl-form-row">
            <div class="gl-form-group">
                <label>مقاس الخط (Font Size)</label>
                <select id="gl-cfg-size-select" class="gl-input" onchange="document.getElementById('gl-cfg-size').value=this.value; updateGLModalLivePreview();">
                    <option value="0.85rem">0.85rem (قياسي - 14px)</option>
                    <option value="0.75rem">0.75rem (صغير - 12px)</option>
                    <option value="0.95rem">0.95rem (متوسط - 15px)</option>
                    <option value="1.1rem">1.1rem (كبير - 17px)</option>
                    <option value="1.25rem">1.25rem (كبير جداً - 20px)</option>
                </select>
                <input type="hidden" id="gl-cfg-size" value="0.85rem">
            </div>
            <div class="gl-form-group">
                <label>محاذاة النص</label>
                <select id="gl-cfg-align" class="gl-input" onchange="updateGLModalLivePreview()">
                    <option value="right">يمين (Right)</option>
                    <option value="center">وسط (Center)</option>
                    <option value="left">يسار (Left)</option>
                </select>
            </div>
        </div>
        <div class="gl-form-row">
            <div class="gl-form-group">
                <label>الفاصلة العشرية (Decimal Places)</label>
                <select id="gl-cfg-decimals" class="gl-input" onchange="updateGLModalLivePreview()">
                    <option value="2">خانتين عشرية (0.00)</option>
                    <option value="0">بدون أعداد عشرية (0)</option>
                    <option value="1">خانة واحدة (0.0)</option>
                    <option value="3">ثلاث خانات (0.000)</option>
                    <option value="4">أربع خانات (0.0000)</option>
                </select>
            </div>
            <div class="gl-form-group">
                <label>الفاصلة الألفية (Thousands Separator)</label>
                <select id="gl-cfg-thousands" class="gl-input" onchange="updateGLModalLivePreview()">
                    <option value="comma">كومة (1,234.56)</option>
                    <option value="space">مسافة (1 234.56)</option>
                    <option value="none">بدون فاصلة (1234.56)</option>
                </select>
            </div>
        </div>
        <div class="gl-form-row">
            <div class="gl-form-group" style="grid-column: span 2;">
                <label>لون الأرقام الخاص بالعمود</label>
                <input type="color" id="gl-cfg-num-color" class="gl-input-color" value="#34d399" oninput="updateGLModalLivePreview()">
            </div>
        </div>
        <div class="gl-btn-group">
            <button type="button" onclick="closeGLColumnModal()" class="btn-action-secondary" style="background:rgba(239,68,68,0.18); border-color:rgba(239,68,68,0.5); color:#f87171; font-weight:bold;">
                ✕ إغلاق النافذة
            </button>
            <button type="button" onclick="saveGLColumnCustomization()" class="btn-run">
                💾 حفظ التخصيص
            </button>
        </div>
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

