<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$page_title = 'دفتر الأستاذ العام الذكي (SGL)';
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
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;700;800&family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/sales-invoice.css">
    <link rel="stylesheet" href="css/table-themes.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        /* ─── Visual Intelligence Analytics Hub Styling ─── */
        .visual-hub-container {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(30, 41, 59, 0.65));
            border: 1px solid rgba(56, 189, 248, 0.2);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            margin-top: 35px;
            font-family: 'Tajawal', sans-serif;
            color: #f8fafc;
        }
        :root.light-theme .visual-hub-container {
            background: linear-gradient(135deg, #ffffff, #f1f5f9) !important;
            border-color: rgba(15, 23, 42, 0.1) !important;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.06) !important;
            color: #0f172a !important;
        }
        .hub-tab-btn {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #cbd5e1;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            font-family: inherit;
        }
        .hub-tab-btn:hover {
            background: rgba(56, 189, 248, 0.15);
            border-color: #38bdf8;
            color: #38bdf8;
            transform: translateY(-2px);
        }
        .hub-tab-btn.active {
            background: #38bdf8;
            border-color: #38bdf8;
            color: #0f172a;
            box-shadow: 0 0 20px rgba(56, 189, 248, 0.4);
        }
        :root.light-theme .hub-tab-btn {
            background: rgba(0, 0, 0, 0.04);
            border-color: rgba(0, 0, 0, 0.1);
            color: #475569;
        }
        :root.light-theme .hub-tab-btn.active {
            background: #0284c7 !important;
            color: #ffffff !important;
        }
        .v-card {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 16px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .v-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }
        .glow-sky:hover { border-color: rgba(56, 189, 248, 0.4); box-shadow: 0 15px 35px rgba(56, 189, 248, 0.15); }
        .glow-purple:hover { border-color: rgba(168, 85, 247, 0.4); box-shadow: 0 15px 35px rgba(168, 85, 247, 0.15); }
        .glow-emerald:hover { border-color: rgba(16, 185, 129, 0.4); box-shadow: 0 15px 35px rgba(16, 185, 129, 0.15); }
        .glow-amber:hover { border-color: rgba(245, 158, 11, 0.4); box-shadow: 0 15px 35px rgba(245, 158, 11, 0.15); }

        :root.light-theme .v-card {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04) !important;
        }
        .v-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .v-card-title { font-size: 0.8rem; font-weight: bold; color: #94a3b8; }
        :root.light-theme .v-card-title { color: #64748b !important; }
        .v-card-value { font-size: 1.35rem; font-weight: 900; font-family: 'JetBrains Mono', monospace; color: #f8fafc; margin-bottom: 10px; }
        :root.light-theme .v-card-value { color: #0f172a !important; }

        .v-card-trend { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .v-badge { padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px; }
        .badge-success { background: rgba(52, 211, 153, 0.15); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.3); }
        .badge-danger { background: rgba(244, 63, 94, 0.15); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.3); }
        .badge-purple { background: rgba(192, 132, 252, 0.15); color: #c084fc; border: 1px solid rgba(192, 132, 252, 0.3); }
        .badge-emerald { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); }
        .badge-amber { background: rgba(251, 191, 36, 0.15); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3); }

        :root.light-theme .badge-success { color: #059669 !important; background: rgba(5, 150, 105, 0.1) !important; }
        :root.light-theme .badge-danger { color: #e11d48 !important; background: rgba(225, 29, 72, 0.1) !important; }
        :root.light-theme .badge-purple { color: #7c3aed !important; background: rgba(124, 58, 237, 0.1) !important; }

        .v-progress-bg { width: 100%; height: 6px; background: rgba(255, 255, 255, 0.08); border-radius: 10px; overflow: hidden; }
        :root.light-theme .v-progress-bg { background: rgba(0, 0, 0, 0.08) !important; }
        .v-progress-bar { height: 100%; border-radius: 10px; transition: width 0.8s cubic-bezier(0.16, 1, 0.3, 1); }

        .v-chart-card {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 18px;
            padding: 22px;
            transition: all 0.3s ease;
        }
        .v-chart-card:hover {
            border-color: rgba(56, 189, 248, 0.3);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.35);
        }
        :root.light-theme .v-chart-card {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
        }
        .v-chart-header { margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.06); }
        :root.light-theme .v-chart-header { border-bottom-color: rgba(0, 0, 0, 0.06) !important; }
        :root.light-theme .v-chart-header h4 { color: #0f172a !important; }

        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.7; }
            50% { transform: scale(1.15); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.7; }
        }
    </style>
    <style>
        .toast-container { position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
        .toast { padding:12px 20px; border-radius:12px; font-weight:700; font-size:0.85rem; display:flex; align-items:center; gap:8px; pointer-events:auto; backdrop-filter:blur(20px); box-shadow:0 12px 40px rgba(0,0,0,0.5); animation:toastIn 0.4s cubic-bezier(0.16,1,0.3,1) both; font-family:'Cairo',sans-serif; }
        .toast.success { background:rgba(52,211,153,0.15); border:1px solid rgba(52,211,153,0.4); color:#34d399; }
        .toast.error { background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#f87171; }
        .toast.warning { background:rgba(251,191,36,0.15); border:1px solid rgba(251,191,36,0.4); color:#fbbf24; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }

        /* Presets Slots Styles */
        .no-print .modern-btn {
            height: 38px !important;
            font-size: 0.88rem !important;
            padding: 0 16px !important;
            border-radius: 10px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-weight: 700 !important;
            font-family: 'Tajawal', sans-serif !important;
            box-sizing: border-box !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        .no-print .modern-btn:hover {
            transform: translateY(-1.5px);
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.05);
        }

        .presets-slots-wrap {
            flex-grow: 1;
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .preset-slot-btn {
            position: relative;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 38px !important;
            font-size: 0.85rem !important;
            padding: 0 14px !important;
            border-radius: 10px !important;
            font-weight: 700 !important;
            font-family: 'Tajawal', sans-serif !important;
            cursor: pointer;
            flex: 1 !important;
            min-width: 80px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            user-select: none;
            text-align: center;
            box-sizing: border-box !important;
        }

        /* Glowing Breathing Keyframes */
        @keyframes preset-glow-purple {
            0%, 100% { box-shadow: 0 0 5px rgba(168, 85, 247, 0.15); }
            50% { box-shadow: 0 0 15px rgba(168, 85, 247, 0.35); }
        }
        @keyframes preset-glow-blue {
            0%, 100% { box-shadow: 0 0 5px rgba(56, 189, 248, 0.15); }
            50% { box-shadow: 0 0 15px rgba(56, 189, 248, 0.35); }
        }
        @keyframes preset-glow-emerald {
            0%, 100% { box-shadow: 0 0 5px rgba(52, 211, 153, 0.15); }
            50% { box-shadow: 0 0 15px rgba(52, 211, 153, 0.35); }
        }
        @keyframes preset-glow-pink {
            0%, 100% { box-shadow: 0 0 5px rgba(244, 114, 182, 0.15); }
            50% { box-shadow: 0 0 15px rgba(244, 114, 182, 0.35); }
        }

        /* Slot 1: Purple */
        .preset-slot-btn[data-id="1"] {
            background: rgba(168, 85, 247, 0.03) !important;
            border: 1px dashed rgba(168, 85, 247, 0.25) !important;
            color: rgba(192, 132, 252, 0.75) !important;
        }
        .preset-slot-btn[data-id="1"]:hover,
        .preset-slot-btn[data-id="1"].has-data {
            background: rgba(168, 85, 247, 0.08) !important;
            border: 1px solid rgba(168, 85, 247, 0.4) !important;
            color: #c084fc !important;
        }
        .preset-slot-btn[data-id="1"].has-data {
            animation: preset-glow-purple 3.5s infinite ease-in-out;
        }
        .preset-slot-btn[data-id="1"]:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 6px 15px rgba(168, 85, 247, 0.35) !important;
        }

        /* Slot 2: Blue */
        .preset-slot-btn[data-id="2"] {
            background: rgba(56, 189, 248, 0.03) !important;
            border: 1px dashed rgba(56, 189, 248, 0.25) !important;
            color: rgba(56, 189, 248, 0.75) !important;
        }
        .preset-slot-btn[data-id="2"]:hover,
        .preset-slot-btn[data-id="2"].has-data {
            background: rgba(56, 189, 248, 0.08) !important;
            border: 1px solid rgba(56, 189, 248, 0.4) !important;
            color: #38bdf8 !important;
        }
        .preset-slot-btn[data-id="2"].has-data {
            animation: preset-glow-blue 3.5s infinite ease-in-out;
        }
        .preset-slot-btn[data-id="2"]:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 6px 15px rgba(56, 189, 248, 0.35) !important;
        }

        /* Slot 3: Emerald */
        .preset-slot-btn[data-id="3"] {
            background: rgba(52, 211, 153, 0.03) !important;
            border: 1px dashed rgba(52, 211, 153, 0.25) !important;
            color: rgba(52, 211, 153, 0.75) !important;
        }
        .preset-slot-btn[data-id="3"]:hover,
        .preset-slot-btn[data-id="3"].has-data {
            background: rgba(52, 211, 153, 0.08) !important;
            border: 1px solid rgba(52, 211, 153, 0.4) !important;
            color: #34d399 !important;
        }
        .preset-slot-btn[data-id="3"].has-data {
            animation: preset-glow-emerald 3.5s infinite ease-in-out;
        }
        .preset-slot-btn[data-id="3"]:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 6px 15px rgba(52, 211, 153, 0.35) !important;
        }

        /* Slot 4: Pink */
        .preset-slot-btn[data-id="4"] {
            background: rgba(244, 114, 182, 0.03) !important;
            border: 1px dashed rgba(244, 114, 182, 0.25) !important;
            color: rgba(244, 114, 182, 0.75) !important;
        }
        .preset-slot-btn[data-id="4"]:hover,
        .preset-slot-btn[data-id="4"].has-data {
            background: rgba(244, 114, 182, 0.08) !important;
            border: 1px solid rgba(244, 114, 182, 0.4) !important;
            color: #f472b6 !important;
        }
        .preset-slot-btn[data-id="4"].has-data {
            animation: preset-glow-pink 3.5s infinite ease-in-out;
        }
        .preset-slot-btn[data-id="4"]:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 6px 15px rgba(244, 114, 182, 0.35) !important;
        }

        .preset-slot-btn .hold-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            width: 0%;
            border-bottom-left-radius: 9px;
            border-bottom-right-radius: 9px;
        }
        .preset-slot-btn[data-id="1"] .hold-bar { background: #a855f7; }
        .preset-slot-btn[data-id="2"] .hold-bar { background: #38bdf8; }
        .preset-slot-btn[data-id="3"] .hold-bar { background: #34d399; }
        .preset-slot-btn[data-id="4"] .hold-bar { background: #f472b6; }




        /* SGL Theme */
        .ledger-header { 
            background: linear-gradient(135deg, rgba(192,132,252,0.15), rgba(15,23,42,0.9)); 
            border: 1px solid rgba(192,132,252,0.2); 
            border-radius: 16px; 
            padding: 32px 25px; 
            margin-bottom: 20px; 
            height: auto !important; 
            display: block !important; 
        }
        .badge-sgl { background: #c084fc; color: #fff; padding: 4px 10px; border-radius: 8px; font-size: 0.8rem; font-weight: 900; letter-spacing: 1px; display: inline-block; vertical-align: middle; margin-right: 10px; }
        
        .filter-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 15px; }
        .filter-row-single { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 15px; align-items: stretch; }
        @media (max-width: 1200px) { .filter-row-single { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) { .filter-row-single { grid-template-columns: 1fr; } }
        .filter-group { display: flex; flex-direction: column; gap: 8px; }
        .filter-group-inline { 
            display: flex; 
            flex-direction: column; 
            align-items: stretch; 
            gap: 2px; 
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 8px 14px;
            transition: all 0.3s ease;
            position: relative;
        }
        .filter-group-inline:focus-within {
            border-color: #a855f7;
            box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.15), inset 0 0 10px rgba(168, 85, 247, 0.1);
            background: rgba(15, 23, 42, 0.9);
            transform: translateY(-2px);
        }
        .filter-group-inline.col-span-2 { grid-column: span 2; }
        @media (max-width: 768px) { .filter-group-inline.col-span-2 { grid-column: span 1; } }
        .filter-group-inline label { 
            color: #38bdf8; 
            font-size: 0.85rem; 
            font-weight: 900; 
            margin-bottom: 4px; 
            letter-spacing: 0.5px;
            pointer-events: none;
            text-shadow: 0 1px 3px rgba(0,0,0,0.6);
        }

        /* Unified SGL Filter Grid Styles */
        .sgl-filter-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            row-gap: 20px;
            column-gap: 15px;
            align-items: stretch;
            margin-bottom: 0;
        }

        .filter-group-row {
            display: flex;
            flex-direction: row !important;
            align-items: center !important;
            gap: 12px !important;
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 10px 14px;
            transition: all 0.3s ease;
            position: relative;
            box-sizing: border-box;
            min-height: 48px;
        }
        .filter-group-row label {
            margin-bottom: 0 !important;
            white-space: nowrap !important;
            color: #ff7300 !important; 
            font-size: 0.95rem !important; 
            font-weight: 900 !important; 
            letter-spacing: 0.5px !important;
            text-shadow: 0 1px 3px rgba(0,0,0,0.8) !important;
        }
        .filter-group-row:focus-within {
            border-color: #a855f7;
            box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.15), inset 0 0 10px rgba(168, 85, 247, 0.1);
            background: rgba(15, 23, 42, 0.9);
            transform: translateY(-2px);
        }

        /* Stacking Context Fixes to keep dropdowns on top */
        .sgl-main-account { z-index: 30 !important; }
        .sgl-period-all, .sgl-period-from, .sgl-period-to { z-index: 25 !important; }
        .sgl-currency { z-index: 20 !important; }
        
        .sgl-type { z-index: 10 !important; }
        .sgl-costcenter { z-index: 9 !important; }
        .sgl-posted { z-index: 8 !important; }
        .sgl-search { z-index: 7 !important; }

        /* Desktop Layout (min-width: 1200px) */
        @media (min-width: 1200px) {
            .sgl-main-account { grid-column: span 6 !important; }
            .sgl-period-all { grid-column: span 4 !important; }
            .sgl-period-from { grid-column: span 2 !important; }
            .sgl-period-to { grid-column: span 2 !important; }
            .sgl-currency { grid-column: span 2 !important; }
            
            .sgl-type { grid-column: span 3 !important; }
            .sgl-costcenter { grid-column: span 3 !important; }
            .sgl-posted { grid-column: span 3 !important; }
            .sgl-search { grid-column: span 3 !important; }
        }

        /* Large Tablets / Small Desktops (768px to 1199px) */
        @media (min-width: 768px) and (max-width: 1199px) {
            .sgl-main-account { grid-column: span 12 !important; }
            .sgl-period-all { grid-column: span 6 !important; }
            .sgl-period-from { grid-column: span 3 !important; }
            .sgl-period-to { grid-column: span 3 !important; }
            .sgl-currency { grid-column: span 6 !important; }
            
            .sgl-type { grid-column: span 6 !important; }
            .sgl-costcenter { grid-column: span 6 !important; }
            .sgl-posted { grid-column: span 6 !important; }
            .sgl-search { grid-column: span 6 !important; }
        }

        /* Mobile Screens (max-width: 767px) */
        @media (max-width: 767px) {
            .sgl-filter-grid {
                grid-template-columns: 1fr !important;
            }
            .sgl-main-account,
            .sgl-period-all,
            .sgl-period-from,
            .sgl-period-to,
            .sgl-currency,
            .sgl-type,
            .sgl-costcenter,
            .sgl-posted,
            .sgl-search {
                grid-column: span 1 !important;
            }
        }
        /* Old duplicate filter-input CSS removed */
        .summary-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-bottom: 15px; }
        .summary-card { padding: 10px 15px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); position: relative; overflow: hidden; display: flex; justify-content: space-between; align-items: center; }
        .summary-card::after { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(45deg, transparent, rgba(255,255,255,0.02), transparent); transform: translateX(-100%); transition: transform 0.5s; }
        .summary-card:hover::after { transform: translateX(100%); }
        .s-label { font-size: 0.9rem; font-weight: 700; margin-bottom: 0; }
        .s-val { font-size: 1.2rem; font-weight: 900; letter-spacing: 0.5px; }

        .ledger-table-wrap { overflow-x: auto; background: rgba(15,23,42,0.5); border-radius: 12px; }
        .ledger-table { width: 100%; border-collapse: collapse; min-width: 800px; table-layout: fixed; }
        .ledger-table th, .ledger-table td { padding: 12px 15px; text-align: right; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem; }
        .ledger-table th { background: rgba(0,0,0,0.3); color: #94a3b8; font-weight: 700; white-space: nowrap; }
        .ledger-table tbody tr:hover { background: rgba(255,255,255,0.02); }
        .ledger-table tbody tr { transition: background 0.15s; }
        
        .td-debit { color: #34d399; font-weight: 700; font-family: monospace; }
        .td-credit { color: #f87171; font-weight: 700; font-family: monospace; }
        .td-balance { color: #c084fc; font-weight: 800; font-family: monospace; }
        .td-empty { color: rgba(255,255,255,0.15); }

        .btn-run { background: #c084fc; color: white; border: none; padding: 10px 25px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s; font-family: inherit; font-size: 0.95rem; }
        .btn-run:hover { background: #a855f7; transform: translateY(-1px); }
        .btn-action { background: rgba(255,255,255,0.05); color: #f8fafc; border: 1px solid rgba(255,255,255,0.1); padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s; font-family: inherit; }
        .btn-action:hover { background: rgba(255,255,255,0.1); }
        .btn-attach { background: rgba(56,189,248,0.1); color: #38bdf8; border: 1px solid rgba(56,189,248,0.3); padding: 4px 8px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; }
        .btn-attach:hover { background: rgba(56,189,248,0.2); }

        .empty-state { text-align: center; padding: 60px 20px; color: #475569; }

        /* Autocomplete Search Box */
        .search-wrap { position: relative; }
        .search-wrap .filter-input { padding-right: 36px; }
        .search-clear-btn {
            position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: #64748b; cursor: pointer;
            font-size: 1.1rem; display: none; padding: 0;
        }
        .autocomplete-list {
            position: absolute; top: calc(100% + 4px); right: 0; left: 0;
            background: #1e293b; border: 1px solid rgba(192,132,252,0.4);
            border-radius: 10px; z-index: 999; max-height: 220px;
            overflow-y: auto; box-shadow: 0 12px 30px rgba(0,0,0,0.6);
            display: none;
        }
        .autocomplete-list li {
            list-style: none; padding: 10px 14px; cursor: pointer;
            color: #e2e8f0; font-size: 0.9rem; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; gap: 8px; transition: background 0.15s;
        }
        .autocomplete-list li:hover, .autocomplete-list li.active { background: rgba(192,132,252,0.15); color: #c084fc; }
        .autocomplete-list li .ac-badge { font-size: 0.72rem; padding: 2px 6px; border-radius: 6px; background: rgba(255,255,255,0.08); }
        .selected-account-chip {
            display: none; align-items: center; gap: 10px; 
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(109, 40, 217, 0.2));
            border: 1px solid rgba(139, 92, 246, 0.5); border-radius: 12px;
            padding: 8px 16px; font-size: 0.95rem; color: #fff; font-weight: bold;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.2);
            transition: all 0.3s ease;
        }
        .chip-remove { background: rgba(255,255,255,0.1); border: none; cursor: pointer; color: #f87171; font-size: 1.1rem; padding: 2px 8px; border-radius: 6px; transition: 0.2s; display: flex; align-items: center; justify-content: center; }
        .chip-remove:hover { background: #f87171; color: white; }

        .audit-stamp {
            margin-top: 20px; padding: 15px; background: rgba(52,211,153,0.05);
            border: 1px dashed rgba(52,211,153,0.3); border-radius: 8px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            color: #34d399; font-weight: bold; font-size: 0.9rem;
        }

        /* Attachment Modal */
        .modal-overlay { position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); display:none; justify-content:center; align-items:center; z-index:100000; backdrop-filter: blur(5px); }
        .modal-content { background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 30px; max-width: 500px; width: 90%; text-align: center; }
        .modal-title { color: white; margin-bottom: 20px; font-size: 1.2rem; font-weight: bold; }
        .modal-btn { background: #38bdf8; color: #0f172a; padding: 8px 20px; border-radius: 8px; border: none; font-weight: bold; cursor: pointer; margin-top: 15px; }

        @media print {
            .no-print { display: none !important; }
            .hide-on-screen { display: flex !important; }
            body { background: white !important; color: black !important; }
            .ledger-table thead th { background: #eee !important; color: #333 !important; }
            .ledger-table tbody td { color: #000 !important; border-color: #ddd !important; }
            .audit-stamp { border-color: #333; color: #333; }
        }
        @media screen {
            .hide-on-screen { display: none !important; }
        }

        body {
            background-color: var(--bg-dark, #0a0e17) !important;
            color: var(--text-primary, #f8fafc);
        }
        :root.light-theme body {
            background-color: #f8fafc !important;
            color: #000000 !important;
        }
        .premium-header-bg {
            position: absolute;
            inset: 0;
            border-radius: 20px;
            overflow: hidden;
            pointer-events: none;
            z-index: -1; /* Place behind header content */
        }


        /* Glassmorphism Summary Cards */
        .summary-card {
            background: rgba(30, 41, 59, 0.6) !important;
            backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            border-radius: 16px !important;
        }
        .summary-card:hover {
            transform: translateY(-8px) scale(1.02) !important;
            background: rgba(30, 41, 59, 0.8) !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), inset 0 0 0 1px rgba(168, 85, 247, 0.5) !important;
        }
        .summary-card .s-val {
            background: linear-gradient(to right, #c084fc, #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px rgba(192, 132, 252, 0.3);
        }

        /* Filter Inputs - Nested Design */
        .filter-input {
            background: transparent !important;
            border: none !important;
            padding: 6px 0 !important;
            color: #f8fafc !important;
            font-size: 0.95rem !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            outline: none !important;
            width: 100%;
            font-family: inherit;
            text-align: center !important;
            text-align-last: center !important;
        }
        .filter-input:focus {
            box-shadow: none !important;
            background: transparent !important;
            transform: none !important;
            border: none !important;
        }
        select.filter-input option {
            background: #1e293b;
            color: #fff;
            padding: 10px;
            text-align: center !important;
        }
        /* Custom date picker icon color */
        ::-webkit-calendar-picker-indicator {
            filter: invert(1) opacity(0.7);
            cursor: pointer;
        }

        /* Themed Buttons */
        .btn-run {
            background: linear-gradient(135deg, #a855f7 0%, #7c3aed 100%) !important;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.4) !important;
            border-radius: 12px !important;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        .btn-run::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, #9333ea 0%, #6d28d9 100%);
            z-index: -1;
            transition: opacity 0.3s ease;
            opacity: 0;
        }
        .btn-run:hover::after { opacity: 1; }
        .btn-run:hover {
            transform: translateY(-2px) scale(1.02) !important;
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.6) !important;
        }

        /* Table Enhancements & Entry Animation */
        .ledger-table-wrap {
            border-radius: 16px !important;
            border: 1px solid rgba(255,255,255,0.05) !important;
            background: rgba(15, 23, 42, 0.4) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3) !important;
        }
        .ledger-table th {
            background: rgba(30, 41, 59, 0.8) !important;
            color: #cbd5e1 !important;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.85rem !important;
            border-bottom: 2px solid rgba(168, 85, 247, 0.3) !important;
        }
        .ledger-table tbody tr {
            animation: fadeInRow 0.5s ease backwards;
        }
        .ledger-table tbody tr:hover {
            background: rgba(168, 85, 247, 0.08) !important;
            transform: scale(1.002);
            box-shadow: inset 4px 0 0 #a855f7;
        }
        @keyframes fadeInRow {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Generate stagger delays for rows */
        .ledger-table tbody tr:nth-child(1) { animation-delay: 0.05s; }
        .ledger-table tbody tr:nth-child(2) { animation-delay: 0.10s; }
        .ledger-table tbody tr:nth-child(3) { animation-delay: 0.15s; }
        .ledger-table tbody tr:nth-child(4) { animation-delay: 0.20s; }
        .ledger-table tbody tr:nth-child(5) { animation-delay: 0.25s; }
        .ledger-table tbody tr:nth-child(6) { animation-delay: 0.30s; }
        .ledger-table tbody tr:nth-child(7) { animation-delay: 0.35s; }
        .ledger-table tbody tr:nth-child(8) { animation-delay: 0.40s; }
        .ledger-table tbody tr:nth-child(9) { animation-delay: 0.45s; }
        .ledger-table tbody tr:nth-child(10) { animation-delay: 0.50s; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: rgba(15, 23, 42, 0.5); }
        ::-webkit-scrollbar-thumb { background: rgba(168, 85, 247, 0.5); border-radius: 5px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(168, 85, 247, 0.8); }

        /* AI Button Glow */
        #btn-smart-search {
            animation: pulse-border 2s infinite;
        }
        @keyframes pulse-border {
            0% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(139, 92, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0); }
        }

        /* SETTINGS SIDEBAR CSS */
        .sidebar-overlay { position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 10000; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
        .sidebar-overlay.active { opacity: 1; pointer-events: all; }
        
        .settings-sidebar { position: fixed; top: 10%; left: 50%; transform: translateX(-50%); width:450px; height:80%; background: rgba(15,23,42,0.95); border: 1px solid rgba(168,85,247,0.5); box-shadow: 0 15px 50px rgba(0,0,0,0.7); z-index: 10001; display: none; flex-direction: column; backdrop-filter: blur(20px); border-radius: 12px; resize: both; overflow: hidden; min-width: 300px; min-height: 400px; }
        .settings-sidebar.active { display: flex; animation: windowPop 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes windowPop { from { opacity:0; transform: translate(-50%, -40%) scale(0.95); } to { opacity:1; transform: translate(-50%, 0) scale(1); } }
        
        .sidebar-header { padding: 15px 20px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center; cursor: move; background: rgba(0,0,0,0.2); }
        .sidebar-header h3 { margin: 0; font-size: 1.2rem; background: linear-gradient(to right, #c084fc, #60a5fa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .close-sidebar { background: none; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer; transition: 0.2s; }
        .close-sidebar:hover { color: #f87171; transform: scale(1.1); }
        .sidebar-content { padding: 20px; flex: 1; overflow-y: auto; }
        .sidebar-desc { color: #94a3b8; font-size: 0.85rem; margin-top: 0; margin-bottom: 20px; }
        .sidebar-footer { padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); background: rgba(0,0,0,0.2); }
        
        .columns-config-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
        .col-config-item { background: rgba(30,41,59,0.7); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 12px; transition: 0.2s; display: flex; flex-direction: column; gap: 10px; }
        .col-config-item.dragging { opacity: 0.5; background: rgba(168,85,247,0.2); border-color: #a855f7; transform: scale(0.98); }
        .col-config-header { display: flex; align-items: center; justify-content: space-between; }
        .col-drag-handle { cursor: grab; font-size: 1.2rem; color: #64748b; padding-left: 10px; }
        .col-drag-handle:active { cursor: grabbing; }
        .col-title-wrap { flex: 1; display: flex; align-items: center; gap: 10px; }
        .col-title { font-weight: bold; color: #e2e8f0; font-size: 0.9rem; }
        
        .col-toggle { cursor: pointer; width: 40px; height: 22px; background: #334155; border-radius: 12px; position: relative; transition: 0.3s; }
        .col-toggle::after { content: ''; position: absolute; top: 2px; right: 2px; width: 18px; height: 18px; background: #fff; border-radius: 50%; transition: 0.3s; }
        .col-toggle.on { background: #34d399; box-shadow: 0 0 10px rgba(52,211,153,0.3); }
        .col-toggle.on::after { right: 20px; }
        
        .col-settings-btn { background: rgba(56,189,248,0.1); color: #38bdf8; border: none; padding: 4px 10px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: bold; transition: 0.2s; }
        .col-settings-btn:hover { background: #38bdf8; color: #0f172a; }
        
        .col-settings-panel { display: none; background: rgba(15,23,42,0.8); padding: 15px; border-radius: 8px; border: 1px dashed rgba(168,85,247,0.4); gap: 10px; flex-direction: column; margin-top: 5px; }
        .col-settings-panel.open { display: flex; animation: fadeInDown 0.3s; }
        
        .cfg-row { display: flex; align-items: center; justify-content: space-between; font-size: 0.85rem; color: #cbd5e1; }
        .cfg-input { background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 6px 10px; border-radius: 6px; font-family: inherit; font-size: 0.85rem; outline: none; width: 120px; transition: 0.2s; }
        .cfg-input:focus { border-color: #c084fc; box-shadow: 0 0 0 2px rgba(192,132,252,0.2); }
        .cfg-input[type="color"] { width: 45px; padding: 0; height: 28px; border: none; border-radius: 6px; overflow: hidden; cursor: pointer; background: transparent; }
        
        .cfg-btns { display: flex; gap: 5px; background: rgba(0,0,0,0.3); padding: 3px; border-radius: 6px; }
        .cfg-btn { background: transparent; border: none; color: #94a3b8; border-radius: 4px; padding: 4px 8px; cursor: pointer; font-size: 1rem; font-weight: bold; transition: 0.2s; }
        .cfg-btn:hover { color: #fff; }
        .cfg-btn.active { background: #c084fc; color: #fff; }
        
        /* Smooth columns resize handled by table-themes.css */
        .ledger-table th { position: relative; user-select: none; }
        @keyframes fadeInDown { from { opacity:0; transform: translateY(-10px); } to { opacity:1; transform: translateY(0); } }

        /* Modernized Buttons and Layout */
        .modern-btn {
            border: 1px solid transparent;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 38px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
            outline: none;
        }
        .modern-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
            filter: brightness(1.15);
        }
        .modern-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        }
    
        
        /* Universal Light Theme Overrides for Internal Screens */
        :root.light-theme body,
        :root.light-theme .ambient-bg {
            background: #f8fafc !important;
        }
        :root.light-theme .glass-panel,
        :root.light-theme .neo-card,
        :root.light-theme .summary-card,
        :root.light-theme .filter-panel,
        :root.light-theme .chart-card,
        :root.light-theme .kpi-card,
        :root.light-theme .premium-header,
        :root.light-theme .top-header,
        :root.light-theme .header-area {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
            color: #0f172a !important;
        }
        :root.light-theme .glass-panel *,
        :root.light-theme .neo-card *,
        :root.light-theme .summary-card * {
            text-shadow: none !important;
        }
        :root.light-theme table.pro-table th,
        :root.light-theme .pos-grid thead th,
        :root.light-theme .ledger-table th,
        :root.light-theme .report-table th {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }
        :root.light-theme table.pro-table td,
        :root.light-theme .pos-grid tbody td,
        :root.light-theme .ledger-table td,
        :root.light-theme .report-table td {
            color: #1e293b !important;
            border-bottom: 1px solid rgba(0,0,0,0.08) !important;
        }
        :root.light-theme table.pro-table tbody tr:hover td,
        :root.light-theme .pos-grid tbody tr:hover td,
        :root.light-theme .ledger-table tbody tr:hover td,
        :root.light-theme .report-table tbody tr:hover td {
            background: rgba(0,0,0,0.02) !important;
        }
        :root.light-theme .h-label,
        :root.light-theme .form-label,
        :root.light-theme .tot-lbl,
        :root.light-theme .lbl,
        :root.light-theme label,
        :root.light-theme .table-header {
            color: #334155 !important;
        }
        :root.light-theme .tot-val,
        :root.light-theme .val {
            color: #0f172a !important;
        }
        :root.light-theme .h-form-group,
        :root.light-theme .control-panel {
            background: transparent !important;
            border-color: rgba(0,0,0,0.08) !important;
        }
        :root.light-theme .modern-btn {
            color: #1e293b !important;
        }
        :root.light-theme .modern-btn:hover {
            color: #0f172a !important;
        }
        /* Neon text colors correction for light theme */
        :root.light-theme .text-emerald-400, :root.light-theme span[style*="color: #34d399"], :root.light-theme span[style*="color:#34d399"] { color: #059669 !important; }
        :root.light-theme .text-rose-400, :root.light-theme span[style*="color: #f87171"], :root.light-theme span[style*="color:#f87171"] { color: #e11d48 !important; }
        :root.light-theme .text-sky-400, :root.light-theme span[style*="color: #38bdf8"], :root.light-theme span[style*="color:#38bdf8"] { color: #0284c7 !important; }
        :root.light-theme .text-purple-400, :root.light-theme span[style*="color: #c084fc"], :root.light-theme span[style*="color:#c084fc"] { color: #7c3aed !important; }
        :root.light-theme .text-amber-400, :root.light-theme span[style*="color: #fbbf24"], :root.light-theme span[style*="color:#fbbf24"] { color: #b45309 !important; }
        /* ── Ultra-Compact & Identical 8px Margins (Right & Left) ── */
        body {
            padding-right: 0 !important;
        }
        body.has-sidebar {
            padding-right: 0 !important;
        }
        .sgl-master-container {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding-top: 10px !important;
            padding-right: 8px !important;
            padding-bottom: 60px !important;
            padding-left: 8px !important;
            box-sizing: border-box !important;
            position: relative;
        }
\n</style>
    </head>
<body class="has-sidebar">
<div class="toast-container" id="toast-container"></div>

<!-- Modal -->
<div class="modal-overlay" id="attach-modal">
    <div class="modal-content">
        <div style="font-size:3rem; margin-bottom:10px;">📎</div>
        <div class="modal-title">المرفقات الحالية</div>
        <p style="color:#94a3b8; font-size:0.9rem;">هنا تظهر جميع المرفقات المرتبطة بالقيد، يمكنك عرض أي منها أو إزالتها.</p>
        <button class="modal-btn" onclick="document.getElementById('attach-modal').style.display='none'">إغلاق</button>
    </div>
</div>

<div class="sgl-master-container">

    <!-- Premium Navigation Header -->
    <?php $header_theme_class = 'header-theme-reports'; ?>
    <?php include 'includes/header.php'; ?>

    <!-- FILTER SECTION -->
    <div class="ledger-header no-print premium-header">
        <div class="premium-header-bg"></div>
        <div class="sgl-filter-grid">
            <!-- السطر الأول -->
            <!-- الحساب الرئيسي (Label and input on the same line) -->
            <div class="filter-group-row sgl-main-account">
                <label>الحساب الرئيسي</label>
                <div style="flex: 1; display: flex; align-items: center; position: relative;">
                    <!-- Input search container -->
                    <div class="search-wrap" id="account-search-wrap" style="display:flex; flex:1; align-items: center; position: relative; width: 100%;">
                        <input type="text" class="filter-input" id="account-search-input" placeholder="ابحث باسم أو رقم الحساب..." autocomplete="off" oninput="onAccountSearch()" onkeyup="onAccountSearchKey(event)" style="width:100%;">
                        <button class="search-clear-btn" id="search-clear-btn">✕</button>
                        <ul class="autocomplete-list" id="autocomplete-list"></ul>
                    </div>
                    <!-- Selected Account Chip (Styled cleanly like the input to keep height matching) -->
                    <div class="selected-account-chip" id="selected-chip" style="display: none; align-items: center; gap: 8px; width: 100%; border: none; background: transparent; padding: 0; margin: 0; box-shadow: none;">
                        <span id="chip-icon" style="font-size: 1.15rem; line-height: 1;">🏷️</span>
                        <span id="chip-name" style="flex:1; font-size: 0.95rem; color: #f8fafc; font-weight: bold; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></span>
                        <button class="chip-remove" onclick="clearAccountSearch()" title="إلغاء" style="background: none; border: none; color: #f87171; cursor: pointer; font-size: 1.1rem; padding: 0 5px; line-height: 1;">✕</button>
                    </div>
                </div>
                <input type="hidden" id="filter-account" value="ALL">
            </div>

            <!-- Date Filter Section -->
            <div class="filter-group-inline sgl-period-all" id="date-all-period-btn" style="cursor: pointer; display: flex; flex-direction: row; align-items: center; justify-content: center; gap: 8px; background: rgba(168, 85, 247, 0.1); border: 1px dashed rgba(168, 85, 247, 0.4); min-height: 48px; box-sizing: border-box;" onclick="setDateMode('range')">
                <span style="font-size: 1.2rem;">📅</span>
                <span style="color: #c084fc; font-weight: bold;">الفترة: كل الفترة</span>
                <span style="font-size: 0.75rem; background: rgba(168, 85, 247, 0.2); padding: 2px 6px; border-radius: 6px; color: #e9d5ff; border: 1px solid rgba(168, 85, 247, 0.3);">تخصيص ⚡</span>
            </div>

            <div class="filter-group-row sgl-period-from" id="date-from-container" style="display: none;">
                <label style="white-space: nowrap;">من تاريخ</label>
                <input type="date" class="filter-input" id="filter-from" onchange="runReport()" style="flex: 1;">
                <span onclick="setDateMode('all'); event.stopPropagation();" style="color: #f87171; font-size: 0.75rem; cursor: pointer; font-weight: bold; padding: 0 4px; white-space: nowrap;" title="إلغاء وتعيين كل الفترة">✕ إلغاء</span>
            </div>

            <div class="filter-group-row sgl-period-to" id="date-to-container" style="display: none;">
                <label style="white-space: nowrap;">إلى تاريخ</label>
                <input type="date" class="filter-input" id="filter-to" onchange="runReport()" style="flex: 1;">
            </div>

            <!-- العملة -->
            <div class="filter-group-row sgl-currency">
                <label>العملة</label>
                <select class="filter-input" id="filter-currency" style="flex: 1;">
                    <option value="ALL">جميع العملات</option>
                    <!-- Will be populated dynamically -->
                </select>
            </div>

            <!-- السطر الثاني -->
            <!-- نوع الحركة -->
            <div class="filter-group-row sgl-type">
                <label>نوع الحركة</label>
                <select class="filter-input" id="filter-type" style="flex: 1;">
                    <option value="ALL">جميع الحركات</option>
                    <option value="receipt">سند قبض</option>
                    <option value="payment">سند صرف</option>
                    <option value="jv">قيد يومية</option>
                    <option value="invoice">فاتورة مبيعات</option>
                    <option value="purchase">فاتورة مشتريات</option>
                </select>
            </div>

            <!-- مركز التكلفة -->
            <div class="filter-group-row sgl-costcenter">
                <label>مركز التكلفة</label>
                <select class="filter-input" id="filter-costcenter" style="flex: 1;">
                    <option value="ALL">الكل</option>
                    <!-- Will be populated dynamically -->
                </select>
            </div>

            <!-- حالة الترحيل -->
            <div class="filter-group-row sgl-posted">
                <label>حالة الترحيل</label>
                <select class="filter-input" id="filter-posted" onchange="runReport()" style="flex: 1;">
                    <option value="ALL">الكل (مرحل وغير مرحل)</option>
                    <option value="posted">مرحلة فقط</option>
                    <option value="unposted">غير مرحلة فقط</option>
                </select>
            </div>

            <!-- البحث النصي (Label and input on the same line) -->
            <div class="filter-group-row sgl-search">
                <label title="بحث سريع برقم المرجع أو كلمة في البيان">بحث نصي (مرجع/بيان)</label>
                <input type="text" class="filter-input" id="filter-search" placeholder="اكتب للبحث..." autocomplete="off" onkeyup="if(event.key === 'Enter') runReport()">
            </div>
        </div>
    </div>

        <!-- ACTION BUTTONS SECTION -->
    <style>
        .action-buttons-container {
            display: flex; 
            gap: 12px; 
            margin-bottom: 25px; 
            align-items: stretch; 
            width: 100%; 
            flex-wrap: nowrap;
            overflow-x: auto;
            padding: 15px 5px; /* space for hover scale */
        }
        
        .magic-btn {
            flex: 1 1 0; 
            min-width: fit-content; 
            white-space: nowrap; 
            justify-content: center;
            position: relative; 
            overflow: hidden; 
            border-radius: 14px; 
            font-weight: 800; 
            font-size: 0.95rem; 
            height: 48px; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 1;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0 16px;
        }
        .magic-btn svg { flex-shrink: 0; transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .magic-btn:hover { transform: translateY(-5px) scale(1.04); z-index: 10; }
        .magic-btn:hover svg { transform: scale(1.2) rotate(5deg); }
        .magic-btn:active { transform: translateY(2px) scale(0.96); }

        /* Shine effect */
        .magic-btn::after {
            content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.4) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-25deg); transition: 0.7s ease-in-out; z-index: -1;
        }
        .magic-btn:hover::after { left: 200%; }

        /* Specific Colors */
        .magic-run { background: linear-gradient(135deg, rgba(168,85,247,0.9), rgba(124,58,237,1)); color: #fff; border-color: rgba(168,85,247,0.6); box-shadow: 0 6px 20px rgba(124,58,237,0.5); }
        .magic-run:hover { box-shadow: 0 12px 30px rgba(124,58,237,0.7), 0 0 20px rgba(168,85,247,0.6); }

        .magic-preview { background: rgba(56,189,248,0.12); color: #38bdf8; border-color: rgba(56,189,248,0.3); }
        .magic-preview:hover { background: rgba(56,189,248,0.25); box-shadow: 0 10px 25px rgba(56,189,248,0.4), 0 0 15px rgba(56,189,248,0.3); color: #fff;}

        .magic-print { background: rgba(16,185,129,0.12); color: #10b981; border-color: rgba(16,185,129,0.3); }
        .magic-print:hover { background: rgba(16,185,129,0.25); box-shadow: 0 10px 25px rgba(16,185,129,0.4), 0 0 15px rgba(16,185,129,0.3); color:#fff; }

        .magic-export { background: rgba(251,191,36,0.12); color: #fbbf24; border-color: rgba(251,191,36,0.3); }
        .magic-export:hover { background: rgba(251,191,36,0.25); box-shadow: 0 10px 25px rgba(251,191,36,0.4), 0 0 15px rgba(251,191,36,0.3); color:#fff;}

        .magic-whatsapp { background: rgba(37,211,102,0.12); color: #25d366; border-color: rgba(37,211,102,0.3); }
        .magic-whatsapp:hover { background: rgba(37,211,102,0.25); box-shadow: 0 10px 25px rgba(37,211,102,0.4), 0 0 15px rgba(37,211,102,0.3); color:#fff;}

        .magic-email { background: rgba(249,115,22,0.12); color: #f97316; border-color: rgba(249,115,22,0.3); }
        .magic-email:hover { background: rgba(249,115,22,0.25); box-shadow: 0 10px 25px rgba(249,115,22,0.4), 0 0 15px rgba(249,115,22,0.3); color:#fff;}

        .magic-preset { background: rgba(255,255,255,0.04); color: #e2e8f0; border-color: rgba(255,255,255,0.15); font-size: 0.85rem;}
        .magic-preset:hover { background: rgba(255,255,255,0.15); color: #fff; box-shadow: 0 10px 25px rgba(255,255,255,0.15); }
        .magic-preset.active { background: linear-gradient(135deg, rgba(56,189,248,0.6), rgba(59,130,246,0.8)); color: #fff; border-color: #38bdf8; box-shadow: 0 0 20px rgba(56,189,248,0.6); transform: scale(1.02); }

        .magic-model { background: rgba(168,85,247,0.12); color: #d8b4fe; border-color: rgba(168,85,247,0.3); font-size: 0.85rem;}
        .magic-model:hover { background: rgba(168,85,247,0.25); color: #fff; box-shadow: 0 10px 25px rgba(168,85,247,0.4); }
        .magic-model.active { background: linear-gradient(135deg, rgba(217,70,239,0.7), rgba(168,85,247,0.9)); color: #fff; border-color: #d946ef; box-shadow: 0 0 20px rgba(217,70,239,0.7); transform: scale(1.02); }

        .magic-custom { background: linear-gradient(135deg, #ec4899 0%, #d946ef 100%); color: #fff; border-color: rgba(236,72,153,0.6); box-shadow: 0 6px 20px rgba(236,72,153,0.5); }
        .magic-custom:hover { box-shadow: 0 12px 30px rgba(236,72,153,0.7), 0 0 20px rgba(217,70,239,0.6); }
    </style>
    <div class="no-print action-buttons-container">
        <!-- 1. تشغيل التقرير -->
        <button class="magic-btn magic-run" onclick="runReport()" id="btn-run-report">
            <svg id="btn-run-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <svg id="btn-run-spinner" style="display:none; animation: rotate 1s linear infinite;" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"></path></svg>
            <span id="btn-run-text">تشغيل التقرير</span>
        </button>
        
        <!-- 3. المعاينة -->
        <button class="magic-btn magic-preview" onclick="previewGL()">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            معاينة
        </button>

        <!-- 4. طباعة مباشرة -->
        <button class="magic-btn magic-print" onclick="printDirectly()">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            طباعة
        </button>

        <!-- 5. تصدير PDF/Excel -->
        <button class="magic-btn magic-export" onclick="window.openExportModal(exportCSV, exportGLPDF)">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            تصدير
        </button>

        <!-- 6. مشاركة واتساب -->
        <button class="magic-btn magic-whatsapp" onclick="shareViaWhatsApp()" title="مشاركة كشف الحساب عبر الواتساب">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.888-.788-1.487-1.761-1.66-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
            واتساب
        </button>

        <!-- 7. مشاركة إيميل -->
        <button class="magic-btn magic-email" onclick="shareViaEmail()" title="إرسال كشف الحساب عبر البريد الإلكتروني">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            إيميل
        </button>

        <!-- النماذج الافتراضية -->
        <button class="magic-btn magic-preset" id="btn-preset-1" onclick="applyPresetGL(1)">افتراضي 1</button>
        <button class="magic-btn magic-preset" id="btn-preset-2" onclick="applyPresetGL(2)">افتراضي 2</button>
        <button class="magic-btn magic-preset" id="btn-preset-3" onclick="applyPresetGL(3)">افتراضي 3</button>
        <button class="magic-btn magic-preset" id="btn-preset-4" onclick="applyPresetGL(4)">افتراضي 4</button>
        
        <!-- النماذج المخصصة -->
        <button class="magic-btn magic-model" id="btn-preset-m1" onclick="applyPresetGL('m1')" onmousedown="startModelSave('m1')" onmouseup="stopModelSave()" onmouseleave="stopModelSave()" oncontextmenu="return false;" title="اضغط 8 ثوانٍ لحفظ التنسيق الحالي">نموذج 1</button>
        <button class="magic-btn magic-model" id="btn-preset-m2" onclick="applyPresetGL('m2')" onmousedown="startModelSave('m2')" onmouseup="stopModelSave()" onmouseleave="stopModelSave()" oncontextmenu="return false;" title="اضغط 8 ثوانٍ لحفظ التنسيق الحالي">نموذج 2</button>

        <!-- 8. تخصيص الجداول -->
        <button class="magic-btn magic-custom" onclick="toggleSettingsSidebar()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
            <span>تخصيص الجداول</span>
        </button>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="summary-cards" id="summary-cards" style="display:none;">
        <div class="summary-card" style="background: linear-gradient(135deg, #064e3b, #047857); border: 1px solid #10b981; display: flex; flex-direction: column; align-items: flex-start; justify-content: center; gap: 4px; padding: 15px 20px;">
            <div class="s-label" style="color:#a7f3d0;">إجمالي المدين</div>
            <div style="display: flex; flex-direction: column; gap: 2px; width: 100%;">
                <div class="s-val td-debit" id="sum-debit" style="color:#ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.5); font-weight: 800;">0.00</div>
                <div class="s-val-foreign" id="sum-debit-foreign" style="font-size: 0.95rem; font-weight: 600; color: #a7f3d0; opacity: 0.9;">(0.00 USD)</div>
            </div>
        </div>
        <div class="summary-card" style="background: linear-gradient(135deg, #7f1d1d, #b91c1c); border: 1px solid #ef4444; display: flex; flex-direction: column; align-items: flex-start; justify-content: center; gap: 4px; padding: 15px 20px;">
            <div class="s-label" style="color:#fecaca;">إجمالي الدائن</div>
            <div style="display: flex; flex-direction: column; gap: 2px; width: 100%;">
                <div class="s-val td-credit" id="sum-credit" style="color:#ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.5); font-weight: 800;">0.00</div>
                <div class="s-val-foreign" id="sum-credit-foreign" style="font-size: 0.95rem; font-weight: 600; color: #fecaca; opacity: 0.9;">(0.00 USD)</div>
            </div>
        </div>
        <div class="summary-card" style="background: linear-gradient(135deg, #312e81, #4f46e5); border: 1px solid #6366f1; display: flex; flex-direction: column; align-items: flex-start; justify-content: center; gap: 4px; padding: 15px 20px;">
            <div class="s-label" style="color:#c7d2fe;">الرصيد محلي</div>
            <div style="display: flex; flex-direction: column; gap: 2px; width: 100%;">
                <div class="s-val" id="sum-balance" style="color:#ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.5); font-weight: 800;">0.00</div>
                <div class="s-val-foreign" id="sum-balance-foreign" style="font-size: 0.95rem; font-weight: 600; color: #c7d2fe; opacity: 0.9;">(0.00 USD)</div>
            </div>
        </div>
    </div>

    <!-- LEDGER TABLE -->
    <div class="glass-panel" id="ledger-panel" style="padding:0; overflow:hidden;">
        <!-- Print Header -->
        <div style="padding:20px 20px 0 20px; display:none;" id="print-header">
            <div id="print-meta"></div>
        </div>

        <div class="ledger-table-wrap">
            <table class="ledger-table">
                <thead id="ledger-thead">
                    <!-- dynamically injected on load -->
                </thead>
                <tbody id="ledger-tbody">
                    <tr>
                        <td colspan="14" class="empty-state">
                            <span style="font-size:3rem; display:block; margin-bottom:15px;">🔍</span>
                            <div>يرجى تحديد الحساب والفترة ثم الضغط على "تشغيل التقرير الذكي"</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Audit Stamp -->
        <div class="audit-stamp" id="audit-stamp" style="display:none;">
            <span>✔️</span>
            <span>تدقيق معتمد: الأرصدة صحيحة ومطابقة وفق المعايير المحاسبية المعتمدة (Blockchain Ready).</span>
        </div>
    </div>

    <!-- Visual Intelligence Analytics Hub (المركز البصري التحليلي والرسوم البيانية) -->
    <div id="visual-intelligence-hub" class="visual-hub-container" style="display: none;">
        
        <!-- Section Header -->
        <div class="hub-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 14px; height: 14px; border-radius: 50%; background: #38bdf8; box-shadow: 0 0 15px #38bdf8; animation: pulse 2s infinite;"></div>
                <h3 style="margin: 0; font-size: 1.35rem; font-weight: 900; color: #f8fafc; letter-spacing: -0.5px;">
                    📈 التقرير البصري التحليلي للحركات وهيكل دفتر الأستاذ العام
                </h3>
                <span class="hub-badge" style="background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 800;">
                    ✨ visual intelligence 3.0
                </span>
            </div>
            <div style="display: flex; gap: 8px;" class="no-print">
                <button class="hub-tab-btn active" onclick="switchVisualTab('all')" id="tab-all">🚀 المشهد الكامل</button>
                <button class="hub-tab-btn" onclick="switchVisualTab('partners')" id="tab-partners">👥 العملاء والموردين</button>
                <button class="hub-tab-btn" onclick="switchVisualTab('treasury')" id="tab-treasury">🏦 الخزن والبنوك</button>
            </div>
        </div>

        <!-- Visual Indicator Cards Row -->
        <div class="visual-cards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-bottom: 30px;">
            
            <!-- Card 1: مؤشر الاتجاه والتغطية -->
            <div class="v-card glow-sky">
                <div class="v-card-header">
                    <span class="v-card-title">مؤشر صافي تدفق الحركات</span>
                    <span class="v-card-icon" id="ind-trend-arrow" style="font-size: 1.4rem; color: #34d399;">↗️</span>
                </div>
                <div class="v-card-value" id="ind-net-val">0.00 SDG</div>
                <div class="v-card-trend">
                    <span id="ind-trend-badge" class="v-badge badge-success">↑ رصيد صافي ممتاز</span>
                    <span id="ind-coverage-pct" style="font-size: 0.8rem; color: #94a3b8; font-weight: bold;">تغطية: 0%</span>
                </div>
                <div class="v-progress-bg">
                    <div class="v-progress-bar" id="ind-coverage-bar" style="width: 50%; background: linear-gradient(90deg, #10b981, #34d399);"></div>
                </div>
            </div>

            <!-- Card 2: مؤشر تركز المخاطر -->
            <div class="v-card glow-purple">
                <div class="v-card-header">
                    <span class="v-card-title">مؤشر التركّز المالي للحركات</span>
                    <span class="v-card-icon">🎯</span>
                </div>
                <div class="v-card-value" id="ind-top3-pct">0.0%</div>
                <div class="v-card-trend">
                    <span id="ind-risk-badge" class="v-badge badge-purple">حجم أضخم 3 قيود</span>
                    <span id="ind-top3-names" style="font-size: 0.75rem; color: #cbd5e1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">-</span>
                </div>
                <div class="v-progress-bg">
                    <div class="v-progress-bar" id="ind-risk-bar" style="width: 30%; background: linear-gradient(90deg, #a855f7, #c084fc);"></div>
                </div>
            </div>

            <!-- Card 3: نسبة نشاط وأرجوحة الحسابات -->
            <div class="v-card glow-emerald">
                <div class="v-card-header">
                    <span class="v-card-title">توازن الذمم المالي (المدين vs الدائن)</span>
                    <span class="v-card-icon" id="ind-ratio-icon">⚖️</span>
                </div>
                <div class="v-card-value" id="ind-ratio-val">1.0 : 1</div>
                <div class="v-card-trend">
                    <span id="ind-ratio-status" class="v-badge badge-emerald">نسبة مدين/دائن</span>
                    <span id="ind-active-count" style="font-size: 0.8rem; color: #94a3b8; font-weight: bold;">0 قيد نشط</span>
                </div>
                <div class="v-progress-bg">
                    <div class="v-progress-bar" id="ind-ratio-bar" style="width: 50%; background: linear-gradient(90deg, #38bdf8, #818cf8);"></div>
                </div>
            </div>

            <!-- Card 4: درجة الصحة المالية الكلية -->
            <div class="v-card glow-amber">
                <div class="v-card-header">
                    <span class="v-card-title">مؤشر جودة كشف الحساب</span>
                    <span class="v-card-icon">⭐</span>
                </div>
                <div class="v-card-value" id="ind-health-score">92/100</div>
                <div class="v-card-trend">
                    <span id="ind-health-badge" class="v-badge badge-amber">ممتاز</span>
                    <span id="ind-health-desc" style="font-size: 0.75rem; color: #94a3b8;">توازن آمن</span>
                </div>
                <div class="v-progress-bg">
                    <div class="v-progress-bar" id="ind-health-bar" style="width: 92%; background: linear-gradient(90deg, #f59e0b, #fbbf24);"></div>
                </div>
            </div>

        </div>

        <!-- 11 Comprehensive ApexCharts Grid -->

        <!-- Charts Row 1: Donut & Hollow Donut Charts -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-bottom: 25px;">
            
            <!-- Chart 1: Hollow Donut Chart - Document Types Distribution -->
            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🍩 كعكة مجوفة: أين تتوزع أنواع المعاملات والمستندات؟</h4>
                        <span style="font-size: 0.72rem; background: rgba(56, 189, 248, 0.18); color: #38bdf8; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(56, 189, 248, 0.3);">💡 هيكل المستندات</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: يوضح لك الفئة الأكثر استخداماً (مبيعات، مشتريات، مقبوضات، مدفوعات)</span>
                </div>
                <div id="chart-hollow-donut-categories" style="min-height: 320px;"></div>
            </div>

            <!-- Chart 2: Hollow Donut Chart - Top Debit Transactions -->
            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🍩 كعكة مفرغة: أضخم 5 قيود وتدفقات مدينة (عليها)</h4>
                        <span style="font-size: 0.72rem; background: rgba(168, 85, 247, 0.18); color: #c084fc; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(168, 85, 247, 0.3);">🎯 أعلى المدينات</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: يظهر أكبر القيود المالية الصادرة والتي تشكل أضخم المبالغ</span>
                </div>
                <div id="chart-hollow-donut-top-debtors" style="min-height: 320px;"></div>
            </div>

        </div>

        <!-- Charts Row 2: Line & Area Charts -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-bottom: 25px;">
            
            <!-- Chart 3: Smooth Curved Gradient Line Chart -->
            <div class="v-chart-card" style="grid-column: span 1 / -1;">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">📈 المنحنى المتدرج لحركة الأرصدة (Balance Spectrum Curve)</h4>
                        <span style="font-size: 0.72rem; background: rgba(16, 185, 129, 0.18); color: #34d399; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(16, 185, 129, 0.3);">🌊 انسيابية الحركات</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: منحنى سلس يوضح صعود وهبوط الرصيد مع كل حركة مالية</span>
                </div>
                <div id="chart-curved-line-spectrum" style="min-height: 330px;"></div>
            </div>

        </div>

        <!-- Charts Row 3: Bi-directional Comparative Bar & Semi-Donut Gauge -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-bottom: 25px;">
            
            <!-- Chart 4: Bi-Directional Comparative Column Chart -->
            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">📊 أعمدة المقارنة المزدوجة: المدين (🟢) ضد الدائن (🔴)</h4>
                        <span style="font-size: 0.72rem; background: rgba(245, 158, 11, 0.18); color: #fbbf24; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(245, 158, 11, 0.3);">⚖️ مقارنة الأطراف</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: يظهر بوضوح الفرق بين المقبوضات والمدفوعات لكل نوع مستند</span>
                </div>
                <div id="chart-bidirectional-bar" style="min-height: 320px;"></div>
            </div>

            <!-- Chart 5: Semi-Circle Donut Radial Gauge -->
            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🧭 عدّاد ومقياس صحة ومأمونية السيولة (Liquidity Health Gauge)</h4>
                        <span style="font-size: 0.72rem; background: rgba(244, 63, 94, 0.18); color: #fb7185; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(244, 63, 94, 0.3);">🛡️ مؤشر الأمان الكلي</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: نسبة مئوية مباشرة تعكس أمان الاستقرار المالي للحركات</span>
                </div>
                <div id="chart-semicircle-gauge" style="min-height: 320px;"></div>
            </div>

        </div>

        <!-- Charts Row 4: Top Creditors Hollow Donut & Horizontal Bar Ranking -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-bottom: 25px;">
            
            <!-- Chart 6: Hollow Donut Chart - Top Creditor Transactions -->
            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🍩 كعكة مجوفة: أضخم 5 قيود دائنة (عليها)</h4>
                        <span style="font-size: 0.72rem; background: rgba(239, 68, 68, 0.18); color: #f87171; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(239, 68, 68, 0.3);">🔻 أعلى الدائنيات</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: يوضح أضخم السندات والقيود الدائنة لتتبع المصروفات والالتزامات</span>
                </div>
                <div id="chart-hollow-donut-top-creditors" style="min-height: 320px;"></div>
            </div>

            <!-- Chart 7: Horizontal Bar Chart - Top 10 Transactions Ranking -->
            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">📊 أعمدة أفقية هرمية: ترتيب أضخم 10 قيود مالية</h4>
                        <span style="font-size: 0.72rem; background: rgba(6, 182, 212, 0.18); color: #22d3ee; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(6, 182, 212, 0.3);">👑 ترتيب الحركات</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: شريط أفقي يُظهر أطول الأعمدة للمعاملات الأكثر حجماً في التقرير</span>
                </div>
                <div id="chart-horizontal-top-bar" style="min-height: 320px;"></div>
            </div>

        </div>

        <!-- Charts Row 5: Filled Area Curve & Radar Equilibrium -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-bottom: 25px;">
            
            <!-- Chart 8: Filled Area Fluid Curve -->
            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🌊 منحنى المساحة المتدرجة الممتلئة (Fluid Area Curve)</h4>
                        <span style="font-size: 0.72rem; background: rgba(16, 185, 129, 0.18); color: #34d399; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(16, 185, 129, 0.3);">💧 المساحة التراكمية</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: مساحة ملونة انسيابية مريحة للعين تعكس التدفق التراكمي</span>
                </div>
                <div id="chart-cumulative-area-fluid" style="min-height: 320px;"></div>
            </div>

            <!-- Chart 9: Polygon Radar Equilibrium Chart -->
            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🎯 مخطط الرادار الخماسي: التوازن المالي للأقسام</h4>
                        <span style="font-size: 0.72rem; background: rgba(168, 85, 247, 0.18); color: #c084fc; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(168, 85, 247, 0.3);">🕸️ خريطة الرادار</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: شبكة رادار خماسية تفاعلية توضح اتزان الحركات المالية بلمحة واحدة</span>
                </div>
                <div id="chart-radar-equilibrium" style="min-height: 320px;"></div>
            </div>

        </div>

        <!-- Charts Row 6: Solid Pie & Multi-Radial Rings Gauge -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px;">
            
            <!-- Chart 10: Solid Pie Chart -->
            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🥧 كعكة دائريّة مصمتة (Pie Chart): توزيع اتجاه القيود</h4>
                        <span style="font-size: 0.72rem; background: rgba(245, 158, 11, 0.18); color: #fbbf24; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(245, 158, 11, 0.3);">🥧 دائرة مصمتة</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: دائرة كاملة مكسوة بالألوان تظهر نسبة القيود المدينة مقابل الدائنة</span>
                </div>
                <div id="chart-solid-pie-active-zero" style="min-height: 320px;"></div>
            </div>

            <!-- Chart 11: Multi-Radial Rings Gauge -->
            <div class="v-chart-card">
                <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🔘 حلقات دوائر مضيئة متداخلة (Multi-Radial Rings)</h4>
                        <span style="font-size: 0.72rem; background: rgba(56, 189, 248, 0.18); color: #38bdf8; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(56, 189, 248, 0.3);">💫 حلقات دائرية</span>
                    </div>
                    <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: ثلاث حلقات دائرية ملونة متداخلة تقارن حصة كل نوع بنسبة مئوية واضحة</span>
                </div>
                <div id="chart-multi-radial-rings" style="min-height: 320px;"></div>
            </div>

        </div>

    </div>

</div>

<!-- Settings Sidebar -->
<div class="sidebar-overlay" id="settings-overlay" onclick="toggleSettingsSidebar()"></div>
<div class="settings-sidebar" id="settings-sidebar" style="resize: both; overflow: auto;">
    <div class="sidebar-header">
        <h3>✨ تخصيص الجدول الديناميكي</h3>
        <button class="close-sidebar" onclick="toggleSettingsSidebar()">✕</button>
    </div>
    <div class="sidebar-content" style="height: calc(100% - 130px); overflow-y: auto;">
        
        <div style="margin-bottom: 20px;">
            <label style="display:block; margin-bottom:10px; font-weight:800; color:#fbbf24; text-shadow: 0 1px 2px rgba(0,0,0,0.8);">✨ أنماط العرض الفاخرة (Crystal Space)</label>
            <div class="crystal-theme-container">
                <button class="crystal-btn c-std active" onclick="changeTableTheme('')" id="btn-theme-">
                    <span style="font-size:1.2rem;">⚪</span>
                    <span>الأساسي</span>
                </button>
                <button class="crystal-btn c-blue" onclick="changeTableTheme('table-theme-blue')" id="btn-theme-table-theme-blue">
                    <span style="font-size:1.2rem;">🔵</span>
                    <span>الأزرق العميق</span>
                </button>
                <button class="crystal-btn c-black" onclick="changeTableTheme('table-theme-black')" id="btn-theme-table-theme-black">
                    <span style="font-size:1.2rem;">⚫</span>
                    <span>الأسود الفاخر</span>
                </button>
                <button class="crystal-btn c-emerald" onclick="changeTableTheme('table-theme-emerald')" id="btn-theme-table-theme-emerald">
                    <span style="font-size:1.2rem;">🟢</span>
                    <span>الزمردي المالي</span>
                </button>
            </div>
        </div>

        <div style="margin-bottom: 20px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
            <label style="display:block; margin-bottom:10px; font-weight:800; color:#38bdf8;">📝 خط الأرقام المحاسبية:</label>
            <div class="font-chips-container">
                <button class="font-chip" onclick="changeNumberFont('num-font-jetbrains')" id="btn-font-num-font-jetbrains" style="font-family: 'JetBrains Mono', monospace;">JetBrains Mono</button>
                <button class="font-chip" onclick="changeNumberFont('num-font-roboto')" id="btn-font-num-font-roboto" style="font-family: 'Roboto Mono', monospace;">Roboto Mono</button>
                <button class="font-chip" onclick="changeNumberFont('num-font-inter')" id="btn-font-num-font-inter" style="font-family: 'Inter', sans-serif;">Inter Font</button>
            </div>
        </div>


        <p class="sidebar-desc">رتب الأعمدة بالسحب والإفلات. اضغط "تخصيص" لتغيير المسمى، الألوان، وحجم الخط.</p>
        <ul id="columns-config-list" class="columns-config-list">
            <!-- Populated by JS -->
        </ul>
    </div>
    <div class="sidebar-footer" style="position: sticky; bottom: 0; background: inherit; padding-top: 10px;">
        <button class="btn-run" style="width:100%; justify-content:center; font-size:1.05rem;" onclick="applyAndSaveConfig()">💾 تطبيق وحفظ التعديلات</button>
        <button class="btn-action" style="width:100%; justify-content:center; margin-top:10px; color:#f87171;" onclick="resetColumnConfig()">🔄 إعادة للوضع الافتراضي</button>
    </div>
</div>

<!-- Custom Context Menu for Drill Down -->
<div id="drilldown-context-menu" class="custom-context-menu">
    <div class="context-menu-item" id="ctx-drilldown-btn">
        <span>📄</span>
        <span>الذهاب لأصل المستند (Drill-down)</span>
    </div>
</div>

<script defer src="js/localforage.min.js"></script>
<script defer src="js/app.js?v=20260723_v14"></script>
<script>
    // Make Sidebar Draggable and Resizable, with Permanent Saving
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('settings-sidebar');
        const header = sidebar.querySelector('.sidebar-header');
        let isDragging = false;
        let startX, startY, initialLeft, initialTop;

        header.addEventListener('mousedown', (e) => {
            if(e.target.classList.contains('close-sidebar')) return;
            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            
            // get actual computed style
            const style = window.getComputedStyle(sidebar);
            initialLeft = parseInt(style.left, 10);
            initialTop = parseInt(style.top, 10);
            
            // Disable translate centering so top/left work cleanly
            sidebar.style.transform = 'none';
            sidebar.style.left = initialLeft + 'px';
            sidebar.style.top = initialTop + 'px';
            
            sidebar.style.transition = 'none'; // disable CSS transition during drag
            document.body.style.userSelect = 'none';
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            sidebar.style.left = (initialLeft + dx) + 'px';
            sidebar.style.top = (initialTop + dy) + 'px';
        });

        document.addEventListener('mouseup', () => {
            if(isDragging) {
                isDragging = false;
                document.body.style.userSelect = 'auto';
                saveSidebarConfig();
            }
        });
        
        // Listen for resize changes via MutationObserver
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === "style" && sidebar.classList.contains('open') && !isDragging) {
                   saveSidebarConfig();
                }
            });
        });
        observer.observe(sidebar, { attributes: true });

        function saveSidebarConfig() {
            if (!window.APP_READY) return; // Prevent wiping settings before DB is loaded!
            if(typeof getTenantKey === 'function') {
                let settings = {};
                try {
                    const raw = localStorage.getItem('erp_settings_v1');
                    if (raw) settings = JSON.parse(raw);
                } catch(e) {}
                
                settings.sgl_sidebar_ui = {
                    left: sidebar.style.left,
                    top: sidebar.style.top,
                    width: sidebar.style.width,
                    height: sidebar.style.height,
                    transform: sidebar.style.transform
                };
                
                localStorage.setItem('erp_settings_v1', JSON.stringify(settings));
            }
        }
    });
</script>
<script defer src="js/html2pdf.bundle.min.js"></script>
<script defer src="js/whatsapp-integration.js"></script>
<script defer src="js/general-ledger.js?v=<?= time() ?>"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Default Dates: From the 1st of the current month to Today
    const dFrom = document.getElementById('filter-from');
    const dTo = document.getElementById('filter-to');
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    
    const formatDate = (d) => {
        let month = '' + (d.getMonth() + 1);
        let day = '' + d.getDate();
        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;
        return [d.getFullYear(), month, day].join('-');
    };
    
    if (dFrom && !dFrom.value) dFrom.value = formatDate(firstDay);
    if (dTo && !dTo.value) dTo.value = formatDate(today);
});
</script>
<script>
function runAISmartSearch() {
    const input = document.getElementById('account-search-input');
    const q = input.value.trim();
    const btn = document.getElementById('btn-smart-search');
    
    if (!q) {
        showToast('الرجاء كتابة وصف للحساب أولاً', 'warning');
        input.focus();
        return;
    }

    if (typeof ALL_ACCOUNTS === 'undefined' || ALL_ACCOUNTS.length <= 1) {
        showToast('قائمة الحسابات غير متوفرة بعد', 'error');
        return;
    }

    btn.innerHTML = '⏳...';
    btn.disabled = true;

    let accountsList = ALL_ACCOUNTS.filter(a => a.id !== 'ALL').map(a => `${a.id} - ${a.name} (${a.type})`).join('\n');
    
    let promptMsg = "أنت مساعد بحث ذكي في نظام محاسبي. المستخدم يبحث عن الحساب الأقرب للمعنى التالي: '" + q + "'.\nإليك قائمة الحسابات المتاحة:\n" + accountsList + "\n\nاستخرج فقط وفقط 'ID' (الكود) الخاص بأقرب حساب يطابق نية المستخدم. لا تكتب أي كلمة أخرى، فقط الـ ID.";

    fetch('api/ai-assistant.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: promptMsg, context: '' })
    })
    .then(res => res.json())
    .then(data => {
        btn.innerHTML = '🤖 ذكي';
        btn.disabled = false;
        
        if (data.reply) {
            let matchId = data.reply.trim().replace(/[^a-zA-Z0-9-]/g, '');
            let matchedAccount = ALL_ACCOUNTS.find(a => a.id === matchId);
            
            if (matchedAccount) {
                if (typeof selectAccount === 'function') {
                    selectAccount(matchedAccount.id, matchedAccount.name, matchedAccount.icon || '🏷️');
                    showToast('تم إيجاد الحساب الذكي: ' + matchedAccount.name);
                }
            } else {
                showToast('لم يتم العثور على حساب مطابق بدقة.', 'warning');
            }
        } else {
            showToast('حدث خطأ في البحث الذكي.', 'error');
        }
    })
    .catch(err => {
        btn.innerHTML = '🤖 ذكي';
        btn.disabled = false;
        showToast('تعذر الاتصال بالخادم.', 'error');
    });
}
</script>

<!-- Custom Context Menu for General Ledger -->
<div id="ledger-context-menu" class="glass-panel" style="
    display: none;
    position: fixed;
    z-index: 20000;
    width: 240px;
    padding: 8px;
    border-radius: 12px;
    background: rgba(15, 12, 22, 0.95);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    direction: rtl;
    font-family: 'Outfit', 'Tajawal', sans-serif;
">
    <!-- Option 1: Show Source Document -->
    <button id="btn-show-doc" style="
        width: 100%;
        text-align: right;
        background: none;
        border: none;
        color: #e2e8f0;
        padding: 10px 12px;
        font-size: 0.88rem;
        cursor: pointer;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        outline: none;
    " onmouseover="this.style.background='rgba(56, 189, 248, 0.15)'; this.style.color='#38bdf8';" onmouseout="this.style.background='none'; this.style.color='#e2e8f0';">
        <span>🔍</span>
        <span id="doc-action-text">عرض أصل المستند</span>
    </button>

    <!-- Option 2: Toggle Review Mode -->
    <button id="btn-toggle-review" style="
        width: 100%;
        text-align: right;
        background: none;
        border: none;
        color: #e2e8f0;
        padding: 10px 12px;
        font-size: 0.88rem;
        cursor: pointer;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        outline: none;
        border-top: 1px solid rgba(255,255,255,0.05);
        margin-top: 4px;
        padding-top: 10px;
    " onmouseover="this.style.background='rgba(16, 185, 129, 0.15)'; this.style.color='#10b981';" onmouseout="this.style.background='none'; this.style.color='#e2e8f0';">
        <span>✅</span>
        <span>تعليم كـ مراجع (مراجعة)</span>
    </button>

    <!-- Option 3: Go to Counter Account Ledger -->
    <button id="btn-go-to-ledger" style="
        width: 100%;
        text-align: right;
        background: none;
        border: none;
        color: #e2e8f0;
        padding: 10px 12px;
        font-size: 0.88rem;
        cursor: pointer;
        border-radius: 8px;
        display: none;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        outline: none;
        border-top: 1px solid rgba(255,255,255,0.05);
        margin-top: 4px;
        padding-top: 10px;
    " onmouseover="this.style.background='rgba(168, 85, 247, 0.15)'; this.style.color='#a855f7';" onmouseout="this.style.background='none'; this.style.color='#e2e8f0';">
        <span>📖</span>
        <span id="ledger-action-text">الذهاب لدفتر الأستاذ</span>
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tbody = document.getElementById('ledger-tbody');
        const menu = document.getElementById('ledger-context-menu');
        const btnShowDoc = document.getElementById('btn-show-doc');
        const btnToggleReview = document.getElementById('btn-toggle-review');
        const actionText = document.getElementById('doc-action-text');

        let currentType = '';
        let currentRef = '';
        let currentCell = null;

        window.openLedgerContextMenu = function(e, type, ref, cellEl = null) {
            e.preventDefault();
            e.stopPropagation();

            const btnGoTo = document.getElementById('btn-go-to-ledger');
            if (cellEl || !e.target.closest('.counter-acc-cell')) {
                if (btnGoTo) btnGoTo.style.display = 'none';
            }

            if (type === 'opening' && (!btnGoTo || btnGoTo.style.display === 'none')) {
                if (menu) menu.style.display = 'none';
                return;
            }

            currentType = type;
            currentRef = ref;
            currentCell = cellEl;

            // Map type to localized name
            let typeName = 'المستند';
            if (type === 'invoice') typeName = 'فاتورة المبيعات';
            else if (type === 'purchase') typeName = 'فاتورة المشتريات';
            else if (type === 'jv') typeName = 'قيد اليومية';
            else if (type === 'receipt') typeName = 'سند القبض';
            else if (type === 'payment') typeName = 'سند الصرف';

            actionText.textContent = `عرض ${typeName} (#${ref})`;

            // Show/hide toggle review button
            if (btnToggleReview) {
                btnToggleReview.style.display = cellEl ? 'flex' : 'none';
            }

            menu.style.display = 'block';
            
            // Position menu at mouse cursor
            let left = e.clientX;
            let top = e.clientY;
            if (left + 240 > window.innerWidth) left = window.innerWidth - 250;
            const menuHeight = cellEl ? 100 : 50;
            if (top + menuHeight > window.innerHeight) top = window.innerHeight - (menuHeight + 10);

            menu.style.left = left + 'px';
            menu.style.top = top + 'px';
        };

        if (tbody) {
            // Context menu delegation for the row (outside number cells)
            tbody.addEventListener('contextmenu', (e) => {
                // If it was clicked on a number cell wrapper, let the showContextMenu handle it
                if (e.target.closest('.num-cell-wrapper')) return;

                const tr = e.target.closest('tr');
                if (!tr) return;

                const isOpening = tr.getAttribute('data-is-opening') === 'true';
                const type = tr.getAttribute('data-type');
                const ref = tr.getAttribute('data-ref');

                const counterCell = e.target.closest('.counter-acc-cell');
                const btnGoTo = document.getElementById('btn-go-to-ledger');

                if (counterCell) {
                    const accId = counterCell.getAttribute('data-acc-id');
                    const accName = counterCell.getAttribute('data-acc-name');
                    if (accId && accId !== '-' && accId !== '') {
                        e.preventDefault();
                        e.stopPropagation();

                        if (btnGoTo) {
                            btnGoTo.style.display = 'flex';
                            const actionTxt = document.getElementById('ledger-action-text');
                            if (actionTxt) actionTxt.textContent = `دفتر أستاذ: ${accName}`;
                            btnGoTo.onclick = () => {
                                window.selectAccount(accId, accName, '🏷️');
                                if (menu) menu.style.display = 'none';
                            };
                        }

                        window.openLedgerContextMenu(e, type || 'opening', ref || 'OPENING', null);
                        return;
                    }
                }

                if (btnGoTo) btnGoTo.style.display = 'none';

                if (isOpening || !type || !ref) {
                    if (menu) menu.style.display = 'none';
                    return;
                }

                window.openLedgerContextMenu(e, type, ref, null);
            });
        }

        if (btnShowDoc) {
            btnShowDoc.addEventListener('click', () => {
                if (typeof openDrillDown === 'function') {
                    openDrillDown(currentType, currentRef);
                }
                menu.style.display = 'none';
            });
        }

        if (btnToggleReview) {
            btnToggleReview.addEventListener('click', () => {
                if (currentCell) {
                    currentCell.classList.toggle('reviewed-cell');
                }
                menu.style.display = 'none';
            });
        }

        document.addEventListener('click', (e) => {
            if (menu && !menu.contains(e.target)) {
                menu.style.display = 'none';
            }
        });
    });
</script>

</body>
</html>

