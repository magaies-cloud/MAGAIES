<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$page_title = 'حساب الارصده الاساسية';
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
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&family=Roboto+Mono:wght@400;500;700&family=JetBrains+Mono:wght@400;600;800&family=Fira+Code:wght@400;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حساب الارصده الاساسية | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/theme.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/components.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/sales-invoice.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <style>
        /* Motion Engine & Dynamic Animation System */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(25px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(-25px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 15px rgba(56, 189, 248, 0.15); }
            50% { box-shadow: 0 0 25px rgba(56, 189, 248, 0.45); }
        }
        @keyframes countUp {
            from { opacity: 0; transform: scale(0.85); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-slide-up {
            animation: slideUp 0.55s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .animate-fade-in {
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .stagger-1 { animation-delay: 0.05s; }
        .stagger-2 { animation-delay: 0.1s; }
        .stagger-3 { animation-delay: 0.15s; }
        .stagger-4 { animation-delay: 0.2s; }

        /* CSS for pos-grid table enhancements */
        .pos-grid th, .pos-grid td {
            font-size: 0.8rem !important;
            padding: 8px 10px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .pos-grid .g-input, .pos-grid .g-select, .pos-grid .g-readonly {
            font-size: 0.85rem !important;
            padding: 8px 10px !important;
            background: rgba(15, 23, 42, 0.5) !important;
            color: #f8fafc !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 8px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .grid-scroll-wrap {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            overflow: visible;
        }
        .pos-grid thead th {
            background: rgba(15, 23, 42, 0.8) !important;
            backdrop-filter: blur(10px);
            border-bottom: 2px solid #eab308 !important; /* gold-primary fallback */
            color: #eab308 !important;
            font-weight: 800 !important;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            position: relative;
            border-top: none;
        }
        .pos-grid tbody tr {
            background: rgba(255, 255, 255, 0.02) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            transition: all 0.3s ease;
        }
        .pos-grid tbody tr:hover td {
            background-color: rgba(56, 189, 248, 0.08) !important;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.1), inset 0 -1px 0 rgba(255,255,255,0.1);
        }
        .pos-grid tbody tr:hover {
            transform: translateY(-2px) scale(1.002);
            box-shadow: 0 12px 40px rgba(56, 189, 248, 0.15);
            z-index: 2;
            position: relative;
        }
        .pos-grid .resizer {
            width: 5px;
            cursor: col-resize;
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
        }
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
        .toast-container { position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
        .toast { padding:12px 20px; border-radius:12px; font-weight:700; font-size:0.85rem; display:flex; align-items:center; gap:8px; pointer-events:auto; backdrop-filter:blur(20px); box-shadow:0 12px 40px rgba(0,0,0,0.5); animation:toastIn 0.4s cubic-bezier(0.16,1,0.3,1) both; font-family:'Cairo',sans-serif; }
        .toast.success { background:rgba(52,211,153,0.15); border:1px solid rgba(52,211,153,0.4); color:#34d399; }
        .toast.warning { background:rgba(251,191,36,0.15); border:1px solid rgba(251,191,36,0.4); color:#fbbf24; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }

        /* Report Header */
        .report-header { background: linear-gradient(135deg, rgba(56,189,248,0.15), rgba(15,23,42,0.9)); border: 1px solid rgba(56,189,248,0.2); border-radius: 14px; padding: 14px 18px; margin-bottom: 12px; }
        .filter-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-bottom: 10px; }
        .filter-group label { display: block; color: #94a3b8; font-size: 0.78rem; margin-bottom: 3px; font-weight: bold; }
        .filter-input { width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: #f8fafc; padding: 7px 10px; border-radius: 8px; font-family: inherit; font-size: 0.9rem; transition: all 0.2s; }
        .filter-input:focus { border-color: #38bdf8; outline: none; background: rgba(0,0,0,0.5); }

        .badge-sgl { background: #38bdf8; color: #0f172a; padding: 3px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: bold; letter-spacing: 1px; }
        .badge-active { background: rgba(52,211,153,0.15); color: #34d399; padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; border: 1px solid rgba(52,211,153,0.3); }

        .btn-run { background: linear-gradient(135deg, #38bdf8, #0284c7); color: #ffffff; border: none; padding: 8px 18px; border-radius: 8px; font-weight: 900; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); font-family: inherit; font-size: 0.9rem; box-shadow: 0 4px 10px rgba(2, 132, 199, 0.3); }
        .btn-run:hover { background: linear-gradient(135deg, #0ea5e9, #0369a1); transform: translateY(-2px) scale(1.02); box-shadow: 0 8px 20px rgba(2, 132, 199, 0.5); }
        .btn-run:active { transform: translateY(1px) scale(0.98); }
        .btn-action { background: rgba(255,255,255,0.05); color: #f8fafc; border: 1px solid rgba(255,255,255,0.1); padding: 7px 14px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s; font-family: inherit; }
        .btn-action:hover { background: rgba(255,255,255,0.1); }

        .audit-stamp {
            margin-top: 20px; padding: 15px; background: rgba(56,189,248,0.05);
            border: 1px dashed rgba(56,189,248,0.3); border-radius: 8px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            color: #38bdf8; font-weight: bold; font-size: 0.9rem;
        }

        /* Dynamic Enterprise Variables & Clickable Numbers */
        #v-classic-ent { background: transparent; padding: 0; border-radius: 8px; }
        /* Removed rigid table styles to allow pos-grid to shine */
        
        #v-classic-ent td.num { 
            text-align: center;
            font-size: var(--fs-num); 
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            font-family: var(--font-num);
            font-weight: 600;
            letter-spacing: 0px;
            position: relative;
            cursor: pointer;
        }
        #v-classic-ent td.num:hover {
            transform: scale(1.05);
            color: #38bdf8;
        }

        .resizer {
            position: absolute;
            left: -4px;
            top: 0;
            bottom: 0;
            width: 10px;
            cursor: col-resize;
            background: transparent;
            z-index: 10;
        }
        .resizer:hover, .resizer.resizing {
            background: #38bdf8;
            opacity: 0.8;
        }
        
        /* The Focused Specific Number Feature (Size Increase + Soft Light Yellow Background) */
        td.focused-num,
        #balances-table tbody td.focused-num,
        .pos-grid tbody td.focused-num {
            font-size: 1.75rem !important;
            font-weight: 900 !important;
            font-family: 'JetBrains Mono', 'Fira Code', 'Roboto Mono', monospace !important;
            font-variant-numeric: tabular-nums !important;
            background: #fef08a !important; /* Soft Light Yellow */
            background-color: #fef08a !important;
            color: #0f172a !important; /* Crisp Dark Contrast */
            border: 2.5px solid #eab308 !important;
            border-radius: 12px !important;
            box-shadow: 0 12px 30px rgba(234, 179, 8, 0.4), 0 4px 14px rgba(0, 0, 0, 0.2), inset 0 1px 2px rgba(255, 255, 255, 0.9) !important;
            z-index: 99999 !important;
            position: relative !important;
            display: table-cell !important;
            padding: 8px 20px !important;
            white-space: nowrap !important;
            min-width: max-content !important;
            width: auto !important;
            overflow: visible !important;
            letter-spacing: 0.8px !important;
            transform: scale(1.08) !important;
            line-height: 1.25 !important;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
            text-shadow: none !important;
            cursor: pointer !important;
        }
        :root.light-theme td.focused-num,
        :root.light-theme #balances-table tbody td.focused-num,
        :root.light-theme .pos-grid tbody td.focused-num {
            background: #fef08a !important;
            background-color: #fef08a !important;
            color: #0f172a !important;
            border: 2.5px solid #eab308 !important;
            box-shadow: 0 10px 25px rgba(234, 179, 8, 0.3), 0 4px 12px rgba(0, 0, 0, 0.1) !important;
            text-shadow: none !important;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            #v-classic-ent { padding: 0 !important; }
            #print-container { background: #fff !important; }
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
        /* ─── 5 Top English Fonts for Numbers ─── */
        .num-font-jetbrains { font-family: 'JetBrains Mono', monospace !important; }
        .num-font-roboto { font-family: 'Roboto Mono', monospace !important; }
        .num-font-firacode { font-family: 'Fira Code', monospace !important; }
        .num-font-inter { font-family: 'Inter', sans-serif !important; }
        .num-font-poppins { font-family: 'Poppins', sans-serif !important; }

        /* ─── SETTINGS SIDEBAR CSS (Copied from General Ledger) ─── */
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

        /* ─── Premium Luminous Glassmorphic Layout Preset Buttons ─── */
        .preset-slots-container {
            background: rgba(15, 23, 42, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 4px 12px;
            border-radius: 10px;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        /* ─── Ultra-Comprehensive 4 Master Visual Report Banner Headers ─── */
        .v-section-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.92), rgba(30, 41, 59, 0.85));
            border: 1.5px solid rgba(56, 189, 248, 0.45);
            border-radius: 18px;
            padding: 12px 22px;
            margin-top: 25px;
            margin-bottom: 20px;
            backdrop-filter: blur(16px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45), 0 0 25px rgba(56, 189, 248, 0.2);
        }
        .v-section-title {
            font-size: 1.18rem;
            font-weight: 900;
            color: #f8fafc;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .v-section-badge {
            font-size: 0.82rem;
            font-weight: 900;
            background: rgba(245, 158, 11, 0.22);
            border: 1px solid rgba(245, 158, 11, 0.55);
            color: #fbbf24;
            padding: 5px 14px;
            border-radius: 20px;
        }

        /* ════════════════════════════════════════════════════════════
            LUMINOUS GLASSMORPHIC HERO BUTTONS (PRESETS & ACTIONS)
            ════════════════════════════════════════════════════════════ */
        
        @keyframes shimmer-sweep {
            0% { transform: translateX(-150%) skewX(-25deg); }
            100% { transform: translateX(250%) skewX(-25deg); }
        }

        @keyframes neon-luminous-pulse {
            0%, 100% {
                box-shadow: 0 0 15px var(--glow-color, rgba(0, 243, 255, 0.7)), 0 0 30px var(--glow-color, rgba(0, 243, 255, 0.4)), inset 0 1px 2px rgba(255, 255, 255, 0.7);
                border-color: var(--border-color, #00f3ff);
            }
            50% {
                box-shadow: 0 0 30px var(--glow-color, rgba(0, 243, 255, 0.95)), 0 0 50px var(--glow-color, rgba(0, 243, 255, 0.7)), inset 0 1px 3px rgba(255, 255, 255, 0.9);
                border-color: #ffffff;
            }
        }

        /* ─── 1. Run Button (أيقونة التشغيل) Neon Styling & +50% Width / +10% Height Sizing ─── */
        .btn-run {
            background: linear-gradient(135deg, #00f3ff 0%, #0284c7 60%, #0369a1 100%) !important;
            color: #ffffff !important;
            border: 2px solid #00f3ff !important;
            padding: 10px 24px !important;
            border-radius: 12px !important;
            font-weight: 900 !important;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
            font-family: inherit;
            font-size: 0.95rem !important;
            box-shadow: 0 0 20px rgba(0, 243, 255, 0.7), inset 0 1px 3px rgba(255, 255, 255, 0.8) !important;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.3px;
        }

        .btn-run:hover {
            background: linear-gradient(135deg, #38bdf8 0%, #0284c7 50%, #0d9488 100%) !important;
            transform: translateY(-4px) scale(1.08) !important;
            box-shadow: 0 0 35px rgba(0, 243, 255, 0.95), 0 0 55px rgba(0, 243, 255, 0.6), inset 0 1px 4px rgba(255, 255, 255, 1) !important;
            filter: brightness(1.2);
        }

        .btn-run:active {
            transform: translateY(-1px) scale(1.02) !important;
        }

        /* Run Icon SVG Dimensions: Width +50% (24px from 16px), Height +10% (18px from 16px) */
        .btn-run svg {
            width: 24px !important;
            height: 18px !important;
            transform: scale(1.5, 1.1) !important;
            transform-origin: center center;
            filter: drop-shadow(0 0 8px #00f3ff) drop-shadow(0 0 15px rgba(0, 243, 255, 0.8));
            transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
            flex-shrink: 0;
        }

        .btn-run:hover svg {
            transform: scale(1.75, 1.25) rotate(15deg) !important;
            filter: drop-shadow(0 0 12px #ffffff) drop-shadow(0 0 25px #00f3ff) !important;
        }

        /* ─── 2. Preset Ribbon Buttons (افتراضي 1، افتراضي 2، إلى آخره) Luminous Neon Styling ─── */
        .preset-ribbon-btn,
        .preset-btn {
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 5px 12px !important;
            border-radius: 16px !important;
            font-size: 0.75rem !important;
            font-weight: 800 !important;
            font-family: inherit;
            cursor: pointer;
            user-select: none;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
            letter-spacing: 0.2px;
            animation: neon-luminous-pulse 4s infinite ease-in-out;
            white-space: nowrap;
        }

        .preset-ribbon-btn::after,
        .preset-btn::after,
        .btn-action-hero::after,
        .btn-run::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 50%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
            transform: translateX(-150%) skewX(-25deg);
            pointer-events: none;
        }

        .preset-ribbon-btn:hover::after,
        .preset-btn:hover::after,
        .btn-action-hero:hover::after,
        .btn-run:hover::after {
            animation: shimmer-sweep 0.75s ease-in-out forwards;
        }

        .preset-ribbon-btn:hover,
        .preset-btn:hover {
            transform: translateY(-5px) scale(1.09) !important;
            box-shadow: 0 0 35px var(--glow-color, rgba(0, 243, 255, 0.95)), 0 0 60px var(--glow-color, rgba(0, 243, 255, 0.6)), inset 0 1px 3px rgba(255, 255, 255, 0.9) !important;
            filter: brightness(1.25);
            z-index: 10;
        }

        .preset-ribbon-btn:active,
        .preset-btn:active {
            transform: translateY(-2px) scale(1.03) !important;
        }

        /* Clock SVG Icon inside Presets: Width +50% (25.5px), Height +10% (18.7px) */
        .preset-clock-svg {
            flex-shrink: 0;
            width: 25.5px !important;
            height: 18.7px !important;
            transform: scale(1.5, 1.1) !important;
            transform-origin: center center;
            filter: drop-shadow(0 0 6px var(--border-color, #00f3ff)) drop-shadow(0 0 12px var(--border-color, #00f3ff));
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.35s ease !important;
        }

        .preset-btn:hover .preset-clock-svg,
        .preset-ribbon-btn:hover .preset-clock-svg {
            transform: scale(1.75, 1.25) rotate(20deg) !important;
            filter: drop-shadow(0 0 14px #ffffff) drop-shadow(0 0 28px var(--border-color, #00f3ff)) !important;
        }

        .preset-clock-progress {
            stroke: #ffffff !important;
            stroke-width: 3px;
            transition: stroke-dashoffset 0.05s linear;
        }

        /* ─── Ultra Luminous Distinct Color Palette for Presets ─── */
        /* افتراضي 1 - Neon Electric Cyan & Aurora Blue */
        .preset-slot-0,
        .preset-ribbon-btn.preset-slot-0 {
            --glow-color: rgba(0, 243, 255, 0.85);
            --border-color: #00f3ff;
            background: linear-gradient(135deg, rgba(0, 243, 255, 0.45), rgba(2, 132, 199, 0.95)) !important;
            border: 2px solid #00f3ff !important;
            color: #ffffff !important;
            box-shadow: 0 0 20px rgba(0, 243, 255, 0.75), inset 0 1px 2px rgba(255, 255, 255, 0.8) !important;
        }

        /* افتراضي 2 - Neon Vivid Emerald & Acid Green */
        .preset-slot-1,
        .preset-ribbon-btn.preset-slot-1 {
            --glow-color: rgba(0, 255, 157, 0.85);
            --border-color: #00ff9d;
            background: linear-gradient(135deg, rgba(0, 255, 157, 0.45), rgba(5, 150, 105, 0.95)) !important;
            border: 2px solid #00ff9d !important;
            color: #ffffff !important;
            box-shadow: 0 0 20px rgba(0, 255, 157, 0.75), inset 0 1px 2px rgba(255, 255, 255, 0.8) !important;
        }

        /* افتراضي 3 - Neon Plasma Purple & Orchid Pink */
        .preset-slot-2,
        .preset-ribbon-btn.preset-slot-2 {
            --glow-color: rgba(217, 70, 239, 0.85);
            --border-color: #d946ef;
            background: linear-gradient(135deg, rgba(217, 70, 239, 0.45), rgba(126, 34, 206, 0.95)) !important;
            border: 2px solid #d946ef !important;
            color: #ffffff !important;
            box-shadow: 0 0 20px rgba(217, 70, 239, 0.75), inset 0 1px 2px rgba(255, 255, 255, 0.8) !important;
        }

        /* افتراضي 4 - Neon Plasma Coral & Crimson Rose */
        .preset-slot-3,
        .preset-ribbon-btn.preset-slot-3 {
            --glow-color: rgba(255, 51, 102, 0.85);
            --border-color: #ff3366;
            background: linear-gradient(135deg, rgba(255, 51, 102, 0.45), rgba(190, 18, 60, 0.95)) !important;
            border: 2px solid #ff3366 !important;
            color: #ffffff !important;
            box-shadow: 0 0 20px rgba(255, 51, 102, 0.75), inset 0 1px 2px rgba(255, 255, 255, 0.8) !important;
        }

        /* نموذج 1 - Neon Solar Gold & Amber Yellow */
        .preset-slot-4,
        .preset-ribbon-btn.preset-slot-4 {
            --glow-color: rgba(255, 204, 0, 0.85);
            --border-color: #ffcc00;
            background: linear-gradient(135deg, rgba(255, 204, 0, 0.45), rgba(180, 83, 9, 0.95)) !important;
            border: 2px solid #ffcc00 !important;
            color: #ffffff !important;
            box-shadow: 0 0 20px rgba(255, 204, 0, 0.75), inset 0 1px 2px rgba(255, 255, 255, 0.8) !important;
        }

        /* نموذج 2 - Neon Cyber Indigo & Sapphire */
        .preset-slot-5,
        .preset-ribbon-btn.preset-slot-5,
        .preset-slot-6 {
            --glow-color: rgba(99, 102, 241, 0.85);
            --border-color: #818cf8;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.45), rgba(67, 56, 202, 0.95)) !important;
            border: 2px solid #818cf8 !important;
            color: #ffffff !important;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.75), inset 0 1px 2px rgba(255, 255, 255, 0.8) !important;
        }

        /* ─── 3. Hero Action Panel & Button Icons Sizing (+50% Width, +10% Height) ─── */
        .actions-panel {
            padding: 8px 14px !important;
            background: rgba(15, 23, 42, 0.85) !important;
            backdrop-filter: blur(16px);
            border-radius: 20px !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
        }

        .btn-action-hero {
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 24px !important;
            border-radius: 24px !important;
            font-size: 0.94rem !important;
            font-weight: 900 !important;
            font-family: inherit;
            cursor: pointer;
            user-select: none;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3), inset 0 1px 1px rgba(255, 255, 255, 0.4);
        }

        .btn-action-hero:hover {
            transform: translateY(-4px) scale(1.08) !important;
            filter: brightness(1.25);
        }

        /* Hero Action Icons: +50% Width, +10% Height */
        .btn-action-hero span:first-child,
        .btn-action span:first-child,
        .badge-sgl span:first-child {
            display: inline-block;
            transform: scale(1.5, 1.1) !important;
            transform-origin: center center;
            filter: drop-shadow(0 0 8px currentColor);
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.35s ease !important;
            margin-left: 4px;
        }

        .btn-action-hero:hover span:first-child,
        .btn-action:hover span:first-child {
            transform: scale(1.75, 1.25) rotate(12deg) !important;
            filter: drop-shadow(0 0 15px currentColor) !important;
        }

        .btn-hero-print {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.5), rgba(67, 56, 202, 0.95)) !important;
            border: 2px solid #818cf8 !important;
            color: #ffffff !important;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.65), inset 0 1px 2px rgba(255, 255, 255, 0.5) !important;
        }
        .btn-hero-print:hover {
            box-shadow: 0 0 35px rgba(99, 102, 241, 0.95), 0 0 50px rgba(129, 140, 248, 0.7) !important;
        }

        .btn-hero-preview {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.5), rgba(5, 150, 105, 0.95)) !important;
            border: 2px solid #34d399 !important;
            color: #ffffff !important;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.65), inset 0 1px 2px rgba(255, 255, 255, 0.5) !important;
        }
        .btn-hero-preview:hover {
            box-shadow: 0 0 35px rgba(16, 185, 129, 0.95), 0 0 50px rgba(52, 211, 153, 0.7) !important;
        }

        .btn-hero-excel {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.5), rgba(217, 119, 6, 0.95)) !important;
            border: 2px solid #fbbf24 !important;
            color: #ffffff !important;
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.65), inset 0 1px 2px rgba(255, 255, 255, 0.5) !important;
        }
        .btn-hero-excel:hover {
            box-shadow: 0 0 35px rgba(245, 158, 11, 0.95), 0 0 50px rgba(251, 191, 36, 0.7) !important;
        }

        .btn-hero-email {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.5), rgba(109, 40, 217, 0.95)) !important;
            border: 2px solid #c084fc !important;
            color: #ffffff !important;
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.65), inset 0 1px 2px rgba(255, 255, 255, 0.5) !important;
        }
        .btn-hero-email:hover {
            box-shadow: 0 0 35px rgba(139, 92, 246, 0.95), 0 0 50px rgba(192, 132, 252, 0.7) !important;
        }

        .btn-hero-whatsapp {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.5), rgba(22, 163, 74, 0.95)) !important;
            border: 2px solid #4ade80 !important;
            color: #ffffff !important;
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.65), inset 0 1px 2px rgba(255, 255, 255, 0.5) !important;
        }
        .btn-hero-whatsapp:hover {
            box-shadow: 0 0 35px rgba(34, 197, 94, 0.95), 0 0 50px rgba(74, 222, 128, 0.7) !important;
        }
        :root.light-theme .preset-slots-container {
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }
        :root.light-theme .preset-btn {
            background: rgba(255, 255, 255, 0.7);
            border-color: rgba(15, 23, 42, 0.08);
            color: #334155;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
        }
        :root.light-theme .preset-btn:hover {
            background: rgba(255, 255, 255, 0.95);
            border-color: rgba(var(--accent-color), 0.7);
            color: #0f172a;
            box-shadow: 0 10px 25px rgba(var(--accent-color), 0.18);
        }
        :root.light-theme .preset-btn.active {
            background: rgba(var(--accent-color), 0.09);
            border-color: rgba(var(--accent-color), 0.8);
            color: #0f172a;
            box-shadow: 0 0 20px rgba(var(--accent-color), 0.28);
        }
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
</style>
</head>
<body class="has-sidebar">

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

<div class="toast-container" id="toast-container"></div>

<!-- Unified Context Menu for Columns & Data Formatting -->
<div id="columnContextMenu" style="display:none; position:fixed; z-index:1000000; top:50%; left:50%; transform:translate(-50%, -50%); background:rgba(15, 23, 42, 0.98); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border:2px solid rgba(56, 189, 248, 0.5); border-radius:18px; box-shadow:0 25px 70px rgba(0,0,0,0.85), 0 0 35px rgba(56, 189, 248, 0.3); padding:16px 20px 20px 20px; width:580px; color:#f8fafc; font-family:'Tajawal', 'Inter', sans-serif; max-height:88vh; overflow:auto; resize:both; min-width:340px; min-height:200px; direction:rtl; text-align:right;">
    <!-- Draggable Header -->
    <div id="columnContextMenuHeader" style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.15); cursor:move; user-select:none; background:rgba(255,255,255,0.05); margin:-16px -20px 14px -20px; padding:14px 20px; border-top-left-radius:16px; border-top-right-radius:16px;">
        <div style="font-weight:900; font-size:1.1rem; color:#38bdf8; display:flex; align-items:center; gap:8px;">
            <span style="font-size:1.2rem; color:#fbbf24;" title="اضغط واسحب للتحريك في أي مكان">⠿</span>
            <span>⚙️</span> تخصيص وإعدادات الأعمدة (إسحب للتحريك)
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <button type="button" title="تصغير / استعادة" onclick="toggleMinimizeCtxMenu()" style="cursor:pointer; font-weight:bold; font-size:0.9rem; color:#cbd5e1; border:none; padding:4px 10px; border-radius:6px; background:rgba(255,255,255,0.1); transition:all 0.2s;" onmouseover="this.style.background='rgba(56,189,248,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">🗕</button>
            <button type="button" title="تكبير / استعادة" onclick="toggleMaximizeCtxMenu()" style="cursor:pointer; font-weight:bold; font-size:0.9rem; color:#cbd5e1; border:none; padding:4px 10px; border-radius:6px; background:rgba(255,255,255,0.1); transition:all 0.2s;" onmouseover="this.style.background='rgba(56,189,248,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">🗖</button>
            <!-- High Visibility Red Close Button -->
            <button type="button" title="إغلاق النافذة" onclick="document.getElementById('columnContextMenu').style.display='none'" style="cursor:pointer; font-weight:900; font-size:1.15rem; color:#ffffff !important; border:1px solid rgba(255,255,255,0.4); padding:4px 14px; border-radius:8px; background:linear-gradient(135deg, #ef4444, #dc2626) !important; box-shadow:0 4px 15px rgba(239,68,68,0.5); transition:all 0.2s; display:flex; align-items:center; gap:4px;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                <span>✕</span> <span>إغلاق</span>
            </button>
        </div>
    </div>

    <!-- Menu Content Container (Hidden when minimized) -->
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
                <span>اسحب مقبض (<b>⣿</b>) لأعلى أو لأسفل لترتيب الأعمدة، وافتح ⚙️ لتخصيص خط ولون وكسور كل عمود بشكل مستقل!</span>
            </div>

            <!-- Dynamic Column Items with drag & per-column settings -->
            <div id="columnToggles" style="display:flex; flex-direction:column; gap:10px;"></div>
        </div>

        <!-- Tab 2: Formatting, Themes & Number Fonts -->
        <div id="ctx-tab-formatting" style="display:none;">
            <!-- Crystal Themes Selector -->
            <div style="margin-bottom:16px; background:rgba(0,0,0,0.2); padding:14px; border-radius:12px; border:1px solid rgba(255,255,255,0.08);">
                <label style="display:block; margin-bottom:10px; font-weight:800; color:#fbbf24;">✨ أنماط العرض الفاخرة (Crystal Themes):</label>
                <div class="crystal-theme-container" style="display:grid; grid-template-columns:repeat(2, 1fr); gap:8px;">
                    <button class="crystal-btn c-std active" onclick="changeTableTheme('')" style="padding:8px; border-radius:8px; cursor:pointer;">⚪ الأساسي (Default)</button>
                    <button class="crystal-btn c-blue" onclick="changeTableTheme('table-theme-blue')" style="padding:8px; border-radius:8px; cursor:pointer;">🔵 الأزرق العميق</button>
                    <button class="crystal-btn c-black" onclick="changeTableTheme('table-theme-black')" style="padding:8px; border-radius:8px; cursor:pointer;">⚫ الأسود الفاخر</button>
                    <button class="crystal-btn c-emerald" onclick="changeTableTheme('table-theme-emerald')" style="padding:8px; border-radius:8px; cursor:pointer;">🟢 الزمردي المالي</button>
                </div>
            </div>

            <!-- Global Number Colors & Size Customizer -->
            <div style="margin-bottom:16px; background:rgba(0,0,0,0.25); padding:14px; border-radius:12px; border:1px solid rgba(56,189,248,0.2);">
                <label style="display:block; margin-bottom:10px; font-weight:800; color:#38bdf8;">🎨 تخصيص ألوان وحجم خط الأرقام:</label>
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.85rem; font-weight:bold; color:#f87171;">🔴 لون أرقام المدين (عليه):</span>
                        <input type="color" value="#f87171" onchange="updateColField(6, 'color', this.value); updateColField(4, 'color', this.value);" style="border:none; width:45px; height:30px; border-radius:6px; cursor:pointer; background:transparent;">
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.85rem; font-weight:bold; color:#34d399;">🟢 لون أرقام الدائن (له):</span>
                        <input type="color" value="#34d399" onchange="updateColField(7, 'color', this.value); updateColField(5, 'color', this.value);" style="border:none; width:45px; height:30px; border-radius:6px; cursor:pointer; background:transparent;">
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.85rem; font-weight:bold; color:#fbbf24;">👤 لون اسم الحساب:</span>
                        <input type="color" value="#fbbf24" onchange="updateColField(0, 'color', this.value);" style="border:none; width:45px; height:30px; border-radius:6px; cursor:pointer; background:transparent;">
                    </div>
                </div>
            </div>

            <!-- 5 Number Fonts Selector -->
            <div style="margin-bottom:16px; background:rgba(0,0,0,0.2); padding:14px; border-radius:12px; border:1px solid rgba(255,255,255,0.08);">
                <label style="display:block; margin-bottom:10px; font-weight:800; color:#38bdf8;">📝 خطوط الأرقام الإنجليزية الفائقة الوضوح:</label>
                <div style="display:flex; flex-direction:column; gap:6px;">
                    <button class="font-chip" onclick="changeNumberFont('num-font-jetbrains')" style="font-family: 'JetBrains Mono', monospace; padding:8px 12px; border-radius:8px; text-align:right;">1. JetBrains Mono (مونوسبيس عالي الدقة)</button>
                    <button class="font-chip" onclick="changeNumberFont('num-font-roboto')" style="font-family: 'Roboto Mono', monospace; padding:8px 12px; border-radius:8px; text-align:right;">2. Roboto Mono (روبوتو المالي)</button>
                    <button class="font-chip" onclick="changeNumberFont('num-font-firacode')" style="font-family: 'Fira Code', monospace; padding:8px 12px; border-radius:8px; text-align:right;">3. Fira Code (فيرا كود الحسابي)</button>
                    <button class="font-chip" onclick="changeNumberFont('num-font-inter')" style="font-family: 'Inter', sans-serif; padding:8px 12px; border-radius:8px; text-align:right;">4. Inter Font (إنتر المريح للعين)</button>
                    <button class="font-chip" onclick="changeNumberFont('num-font-poppins')" style="font-family: 'Poppins', sans-serif; padding:8px 12px; border-radius:8px; text-align:right;">5. Poppins Font (بوبينز الفاخر)</button>
                </div>
            </div>

            <!-- Fast Action Shortcuts -->
            <div style="background:rgba(56,189,248,0.05); padding:12px; border-radius:10px; border:1px dashed rgba(56,189,248,0.2); display:flex; gap:8px;">
                <button type="button" onclick="window.print()" style="flex:1; padding:8px; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color:#fff; border-radius:6px; font-weight:bold; cursor:pointer;">🖨️ طباعة</button>
                <button type="button" onclick="exportPDF()" style="flex:1; padding:8px; background:rgba(56,189,248,0.2); border:1px solid #38bdf8; color:#38bdf8; border-radius:6px; font-weight:bold; cursor:pointer;">📄 تصدير PDF</button>
                <button type="button" onclick="exportExcel()" style="flex:1; padding:8px; background:rgba(16,185,129,0.2); border:1px solid #10b981; color:#34d399; border-radius:6px; font-weight:bold; cursor:pointer;">📊 إكسل</button>
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

<!-- Custom Context Menu for Partner Rows -->
<div id="partnerContextMenu" style="display:none; position:fixed; z-index:100000; background:rgba(15, 23, 42, 0.98); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); border:1px solid rgba(56, 189, 248, 0.4); border-radius:12px; box-shadow:0 20px 50px rgba(0,0,0,0.8), 0 0 20px rgba(56,189,248,0.3); padding:8px; width:260px; color:#f8fafc; font-family:'Tajawal', sans-serif;">
    <div id="partnerMenuHeader" style="font-weight:bold; font-size:0.88rem; color:#38bdf8; padding:8px 12px; border-bottom:1px solid rgba(255,255,255,0.1); margin-bottom:6px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">اسم الحساب</div>
    <div id="gotoLedgerItem" onclick="window.goToPartnerLedger()" style="display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:8px; cursor:pointer; font-weight:bold; font-size:0.9rem; transition:all 0.2s;" onmouseover="this.style.background='rgba(56,189,248,0.2)'; this.style.color='#38bdf8';" onmouseout="this.style.background='transparent'; this.style.color='#f8fafc';">
        <span style="font-size:1.2rem;">📖</span>
        <span>الذهاب إلى دفتر الأستاذ العام (GL)</span>
    </div>
</div>


<div class="sgl-master-container">
    <!-- Premium Navigation Header -->
    <?php $header_theme_class = 'header-theme-reports'; ?>
    <?php include 'includes/header.php'; ?>

    <!-- FILTERS & PRESET CONTROLS PANEL (EXPANDED ~1CM) -->
    <div class="report-header no-print" style="background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(255,255,255,0.15); border-radius: 14px; padding: 18px 24px; margin-bottom: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.4);">
        <div class="filter-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 12px; gap: 14px;">
            <!-- 1. نوع المجموعة -->
            <div class="filter-group">
                <label style="font-size:0.85rem; margin-bottom:6px; font-weight:800; color:#38bdf8; display:flex; align-items:center; gap:4px;">
                    <span>📂 نوع المجموعة</span>
                </label>
                <select class="filter-input" id="filter-group" style="padding:6px 12px; font-size:0.9rem; height:40px; border-radius:8px; font-weight:700;" onchange="runReport()">
                    <option value="ALL">جميع الحسابات والأرصدة (الكل)</option>
                    <option value="CUST">👥 أرصدة العملاء</option>
                    <option value="SUPP">🛍️ أرصدة الموردين</option>
                    <option value="BANK">🏦 حسابات البنوك</option>
                    <option value="SAFE">🔒 الخزن والصناديق</option>
                    <option value="EXP">📊 المصروفات والحسابات العامة</option>
                </select>
            </div>

            <!-- 2. ترتيب الأرصدة -->
            <div class="filter-group">
                <label style="font-size:0.85rem; margin-bottom:6px; font-weight:800; color:#38bdf8; display:flex; align-items:center; gap:4px;">
                    <span>🔃 ترتيب الأرصدة</span>
                </label>
                <select class="filter-input" id="filter-sort" style="padding:6px 12px; font-size:0.9rem; height:40px; border-radius:8px; font-weight:700;" onchange="runReport()">
                    <option value="BALANCE_DESC">حسب الرصيد (من الأعلى للأقل)</option>
                    <option value="BALANCE_ASC">حسب الرصيد (من الأقل للأعلى)</option>
                    <option value="DEBIT_DESC">🔴 الأعلى مديونية (عليه فقط)</option>
                    <option value="CREDIT_DESC">🟢 الأعلى دائنية (له فقط)</option>
                    <option value="NAME">🔤 حسب اسم الحساب (أبجدياً)</option>
                    <option value="LAST_DATE">⏱️ حسب تاريخ آخر حركة (الأحدث)</option>
                </select>
            </div>

            <!-- 3. مركز التكلفة -->
            <div class="filter-group">
                <label style="font-size:0.85rem; margin-bottom:6px; font-weight:800; color:#38bdf8; display:flex; align-items:center; gap:4px;">
                    <span>🏢 مركز التكلفة</span>
                </label>
                <select class="filter-input" id="filter-costcenter" style="padding:6px 12px; font-size:0.9rem; height:40px; border-radius:8px; font-weight:700;" onchange="runReport()">
                    <option value="ALL">جميع مراكز التكلفة (الكل)</option>
                </select>
            </div>

            <!-- 4. القطاعات والتصنيفات -->
            <div class="filter-group">
                <label style="font-size:0.85rem; margin-bottom:6px; font-weight:800; color:#38bdf8; display:flex; align-items:center; gap:4px;">
                    <span>🏷️ القطاع / التصنيف</span>
                </label>
                <select class="filter-input" id="filter-sector" style="padding:6px 12px; font-size:0.9rem; height:40px; border-radius:8px; font-weight:700;" onchange="runReport()">
                    <option value="ALL">جميع القطاعات والتصنيفات</option>
                    <option value="RETAIL">🛒 قطاع التجزئة</option>
                    <option value="WHOLESALE">🏢 قطاع الجملة والشركات</option>
                    <option value="VIP">⭐ كبار العملاء (Key Accounts)</option>
                    <option value="EXTERNAL">🌐 جهات ومؤسسات خارجية</option>
                </select>
            </div>

            <!-- 5. التحديد بالعملة -->
            <div class="filter-group">
                <label style="font-size:0.85rem; margin-bottom:6px; font-weight:800; color:#38bdf8; display:flex; align-items:center; gap:4px;">
                    <span>💱 العملة المستهدفة</span>
                </label>
                <select class="filter-input" id="filter-currency" style="padding:6px 12px; font-size:0.9rem; height:40px; border-radius:8px; font-weight:700;" onchange="runReport()">
                    <option value="ALL">جميع العملات (محلي + أجنبي)</option>
                    <option value="SDG">🇸🇩 الجنيه السوداني (SDG)</option>
                    <option value="USD">💵 الدولار الأمريكي (USD)</option>
                </select>
            </div>

            <!-- 6. حالة الرصيد والتصفية الذكية -->
            <div class="filter-group">
                <label style="font-size:0.85rem; margin-bottom:6px; font-weight:800; color:#38bdf8; display:flex; align-items:center; gap:4px;">
                    <span>🎯 حالة الرصيد</span>
                </label>
                <select class="filter-input" id="filter-condition" style="padding:6px 12px; font-size:0.9rem; height:40px; border-radius:8px; font-weight:700;" onchange="runReport()">
                    <option value="ALL">جميع الأرصدة (بدون تصفية)</option>
                    <option value="DEBTORS_ONLY">🔴 الأرصدة المدينة فقط (عليه)</option>
                    <option value="CREDITORS_ONLY">🟢 الأرصدة الدائنة فقط (له)</option>
                    <option value="ACTIVE_ONLY">⚡ الأرصدة المتحركة (ذات نشاط)</option>
                    <option value="NON_ZERO">≠ الأرصدة غير الصفرية فقط</option>
                </select>
            </div>
        </div>
        <div class="filter-row" style="margin-top: 10px; margin-bottom: 15px; display: flex; gap: 6px; flex-wrap: nowrap; width: 100%; align-items: center;">

            <div class="preset-slots-container no-print" id="main-presets-container" style="flex:1; display:flex; gap:6px; align-items:center; flex-wrap:nowrap; padding:2px 6px; border-radius:8px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); min-height: 36px; overflow-x: auto;">
                <!-- Column & Colors Control Button (زر التحكم) -->
                <button type="button" class="preset-ribbon-btn" onclick="openColumnContextMenu(event)" style="background: linear-gradient(135deg, #f59e0b, #d97706) !important; color: #ffffff !important; border: 1px solid rgba(251, 191, 36, 0.8) !important; font-weight: 900 !important; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4) !important;">⚙️ التحكم</button>
                <!-- Visual Intelligence Hub Toggle Button -->
                <button type="button" class="preset-ribbon-btn" onclick="window.toggleVisualHub()" style="background: linear-gradient(135deg, #0ea5e9, #6366f1) !important; color: #ffffff !important; border: 1px solid rgba(56, 189, 248, 0.6) !important; font-weight: 900 !important; box-shadow: 0 4px 15px rgba(14, 165, 233, 0.4) !important;">📊 الشاشات والتقارير البصرية 📈</button>
                <!-- Static fallback buttons matching General Ledger style -->
                <button type="button" class="preset-ribbon-btn preset-slot-0" onclick="window.openPresetModal(0, 'افتراضي 1')">💎 افتراضي 1 🔒</button>
                <button type="button" class="preset-ribbon-btn preset-slot-1" onclick="window.openPresetModal(1, 'افتراضي 2')">⭐ افتراضي 2 🔒</button>
                <button type="button" class="preset-ribbon-btn preset-slot-2" onclick="window.openPresetModal(2, 'افتراضي 3')">🎨 افتراضي 3 🔒</button>
                <button type="button" class="preset-ribbon-btn preset-slot-3" onclick="window.openPresetModal(3, 'افتراضي 4')">🔥 افتراضي 4 🔒</button>
                <button type="button" class="preset-ribbon-btn preset-slot-4" onclick="window.openPresetModal(4, 'نموذج 1')">✨ نموذج 1 🔄</button>
                <button type="button" class="preset-ribbon-btn preset-slot-5" onclick="window.openPresetModal(5, 'نموذج 2')">🚀 نموذج 2 🔄</button>
                <button type="button" class="preset-ribbon-btn preset-slot-6" onclick="window.openPresetModal(6, 'نموذج 3')">🌟 نموذج 3 🔄</button>
            </div>

        </div>
    </div>

    <!-- HIGH CONTRAST COMPACT KPI DASHBOARD -->
    <div id="analytics-dashboard" class="no-print" style="display:block; margin-bottom: 6px; padding: 4px 8px; background: rgba(15, 23, 42, 0.65); border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;">
            <!-- KPI 1: Customers -->
            <div style="background: linear-gradient(135deg, rgba(16,185,129,0.25), rgba(15,23,42,0.9)); border-radius: 6px; padding: 4px 8px; border: 1px solid rgba(16,185,129,0.45); box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                <div style="color: #ffffff; font-size: 0.85rem; font-weight: 900; text-align: center; margin-bottom: 2px; text-shadow: 0 1px 3px rgba(0,0,0,0.9);">إجمالي رصيد العملاء (عليه)</div>
                <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.12); padding-top: 2px; gap: 4px;">
                    <!-- Right: Foreign Currency / USD -->
                    <div style="flex: 1; text-align: right;">
                        <span style="color:#e2e8f0; font-size:0.68rem; font-weight:bold; display:block;">بالدولار / أجنبي</span>
                        <div id="kpi-dr-foreign" class="num" style="color: #34d399; font-size: 1.55rem; font-weight: 900; line-height: 1.05; text-shadow: 0 2px 4px rgba(0,0,0,0.9);">0.00 USD</div>
                    </div>
                    <!-- Divider line -->
                    <div style="width: 2px; height: 30px; background: rgba(255,255,255,0.25);"></div>
                    <!-- Left: Local Currency / SDG -->
                    <div style="flex: 1; text-align: left;">
                        <span style="color:#e2e8f0; font-size:0.68rem; font-weight:bold; display:block;">بالجنيه السوداني</span>
                        <div id="kpi-dr" class="num" style="color: #10b981; font-size: 1.55rem; font-weight: 900; line-height: 1.05; text-shadow: 0 2px 4px rgba(0,0,0,0.9);">0.00 SDG</div>
                    </div>
                </div>
            </div>

            <!-- KPI 2: Suppliers -->
            <div style="background: linear-gradient(135deg, rgba(244,63,94,0.25), rgba(15,23,42,0.9)); border-radius: 6px; padding: 4px 8px; border: 1px solid rgba(244,63,94,0.45); box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                <div style="color: #ffffff; font-size: 0.85rem; font-weight: 900; text-align: center; margin-bottom: 2px; text-shadow: 0 1px 3px rgba(0,0,0,0.9);">إجمالي رصيد الموردين (له)</div>
                <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.12); padding-top: 2px; gap: 4px;">
                    <!-- Right: Foreign Currency / USD -->
                    <div style="flex: 1; text-align: right;">
                        <span style="color:#e2e8f0; font-size:0.68rem; font-weight:bold; display:block;">بالدولار / أجنبي</span>
                        <div id="kpi-cr-foreign" class="num" style="color: #fb7185; font-size: 1.55rem; font-weight: 900; line-height: 1.05; text-shadow: 0 2px 4px rgba(0,0,0,0.9);">0.00 USD</div>
                    </div>
                    <!-- Divider line -->
                    <div style="width: 2px; height: 30px; background: rgba(255,255,255,0.25);"></div>
                    <!-- Left: Local Currency / SDG -->
                    <div style="flex: 1; text-align: left;">
                        <span style="color:#e2e8f0; font-size:0.68rem; font-weight:bold; display:block;">بالجنيه السوداني</span>
                        <div id="kpi-cr" class="num" style="color: #f43f5e; font-size: 1.55rem; font-weight: 900; line-height: 1.05; text-shadow: 0 2px 4px rgba(0,0,0,0.9);">0.00 SDG</div>
                    </div>
                </div>
            </div>

            <!-- KPI 3: Net Balance -->
            <div style="background: linear-gradient(135deg, rgba(56,189,248,0.25), rgba(15,23,42,0.9)); border-radius: 6px; padding: 4px 8px; border: 1px solid rgba(56,189,248,0.45); box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                <div style="color: #ffffff; font-size: 0.85rem; font-weight: 900; text-align: center; margin-bottom: 2px; text-shadow: 0 1px 3px rgba(0,0,0,0.9);">إجمالي الحصيلة الصافية</div>
                <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.12); padding-top: 2px; gap: 4px;">
                    <!-- Right: Foreign Currency / USD -->
                    <div style="flex: 1; text-align: right;">
                        <span style="color:#e2e8f0; font-size:0.68rem; font-weight:bold; display:block;">بالدولار / أجنبي</span>
                        <div id="kpi-bal-foreign" class="num" style="color: #7dd3fc; font-size: 1.55rem; font-weight: 900; line-height: 1.05; text-shadow: 0 2px 4px rgba(0,0,0,0.9);">0.00 USD</div>
                    </div>
                    <!-- Divider line -->
                    <div style="width: 2px; height: 30px; background: rgba(255,255,255,0.25);"></div>
                    <!-- Left: Local Currency / SDG -->
                    <div style="flex: 1; text-align: left;">
                        <span style="color:#e2e8f0; font-size:0.68rem; font-weight:bold; display:block;">بالجنيه السوداني</span>
                        <div id="kpi-bal" class="num" style="color: #38bdf8; font-size: 1.55rem; font-weight: 900; line-height: 1.05; text-shadow: 0 2px 4px rgba(0,0,0,0.9);">0.00 SDG</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Panel (Luminous Glassmorphic Hero Action Buttons) -->
    <div class="actions-panel no-print" id="actions-panel" style="display:none; flex-wrap: wrap; margin-bottom: 10px; gap: 10px; align-items: center;">
        <!-- The user requested the removal of the print, export, preview, email, and whatsapp buttons here to avoid conflicts -->
    </div>

    <!-- TABLE -->
    <div id="print-container" style="display:block; padding: 6px; border-radius: 10px; background: rgba(255,255,255,0.02);">
        
        <div id="v-classic-ent">
            


            <div class="grid-scroll-wrap" style="flex: 1; margin-top: 10px; width: 100%;">
            <table class="pos-grid" style="width: 100%; border-collapse: collapse;" id="balances-table">
                <thead>
                    <tr id="balances-header-row">
                        <th data-orig-idx="0" style="position:relative; cursor:grab; padding-top:20px;" draggable="true" ondragstart="window.mbHandleDragStart(event)" ondragover="window.mbHandleDragOver(event)" ondragenter="window.mbHandleDragEnter(event)" ondragleave="window.mbHandleDragLeave(event)" ondrop="window.mbHandleDrop(event)" ondragend="window.mbHandleDragEnd(event)"><div style="position:absolute; top:2px; left:0; right:0; display:flex; justify-content:space-between; padding:0 5px; font-size:11px; opacity:0.5; z-index:10;"><span style="cursor:pointer; padding:2px;" onclick="window.mbHandleArrowMove(event, this, 1)" title="����� ������">?</span><span style="cursor:pointer; padding:2px;" onclick="window.mbHandleArrowMove(event, this, -1)" title="����� ������">?</span></div>اسم الحساب<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="1" style="position:relative; cursor:grab; padding-top:20px;" draggable="true" ondragstart="window.mbHandleDragStart(event)" ondragover="window.mbHandleDragOver(event)" ondragenter="window.mbHandleDragEnter(event)" ondragleave="window.mbHandleDragLeave(event)" ondrop="window.mbHandleDrop(event)" ondragend="window.mbHandleDragEnd(event)"><div style="position:absolute; top:2px; left:0; right:0; display:flex; justify-content:space-between; padding:0 5px; font-size:11px; opacity:0.5; z-index:10;"><span style="cursor:pointer; padding:2px;" onclick="window.mbHandleArrowMove(event, this, 1)" title="����� ������">?</span><span style="cursor:pointer; padding:2px;" onclick="window.mbHandleArrowMove(event, this, -1)" title="����� ������">?</span></div>رقم الهاتف<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="2" style="position:relative; cursor:grab; padding-top:20px;" draggable="true" ondragstart="window.mbHandleDragStart(event)" ondragover="window.mbHandleDragOver(event)" ondragenter="window.mbHandleDragEnter(event)" ondragleave="window.mbHandleDragLeave(event)" ondrop="window.mbHandleDrop(event)" ondragend="window.mbHandleDragEnd(event)"><div style="position:absolute; top:2px; left:0; right:0; display:flex; justify-content:space-between; padding:0 5px; font-size:11px; opacity:0.5; z-index:10;"><span style="cursor:pointer; padding:2px;" onclick="window.mbHandleArrowMove(event, this, 1)" title="����� ������">?</span><span style="cursor:pointer; padding:2px;" onclick="window.mbHandleArrowMove(event, this, -1)" title="����� ������">?</span></div>الرقم الضريبي<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="3" style="position:relative; cursor:grab; padding-top:20px;" draggable="true" ondragstart="window.mbHandleDragStart(event)" ondragover="window.mbHandleDragOver(event)" ondragenter="window.mbHandleDragEnter(event)" ondragleave="window.mbHandleDragLeave(event)" ondrop="window.mbHandleDrop(event)" ondragend="window.mbHandleDragEnd(event)"><div style="position:absolute; top:2px; left:0; right:0; display:flex; justify-content:space-between; padding:0 5px; font-size:11px; opacity:0.5; z-index:10;"><span style="cursor:pointer; padding:2px;" onclick="window.mbHandleArrowMove(event, this, 1)" title="����� ������">?</span><span style="cursor:pointer; padding:2px;" onclick="window.mbHandleArrowMove(event, this, -1)" title="����� ������">?</span></div>العملة<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="4" style="position:relative; cursor:grab; padding-top:20px;" draggable="true" ondragstart="window.mbHandleDragStart(event)" ondragover="window.mbHandleDragOver(event)" ondragenter="window.mbHandleDragEnter(event)" ondragleave="window.mbHandleDragLeave(event)" ondrop="window.mbHandleDrop(event)" ondragend="window.mbHandleDragEnd(event)"><div style="position:absolute; top:2px; left:0; right:0; display:flex; justify-content:space-between; padding:0 5px; font-size:11px; opacity:0.5; z-index:10;"><span style="cursor:pointer; padding:2px;" onclick="window.mbHandleArrowMove(event, this, 1)" title="����� ������">?</span><span style="cursor:pointer; padding:2px;" onclick="window.mbHandleArrowMove(event, this, -1)" title="����� ������">?</span></div>عليه عملة<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="5" style="position:relative; cursor:grab; padding-top:20px;" draggable="true" ondragstart="window.mbHandleDragStart(event)" ondragover="window.mbHandleDragOver(event)" ondragenter="window.mbHandleDragEnter(event)" ondragleave="window.mbHandleDragLeave(event)" ondrop="window.mbHandleDrop(event)" ondragend="window.mbHandleDragEnd(event)"><div style="position:absolute; top:2px; left:0; right:0; display:flex; justify-content:space-between; padding:0 5px; font-size:11px; opacity:0.5; z-index:10;"><span style="cursor:pointer; padding:2px;" onclick="window.mbHandleArrowMove(event, this, 1)" title="����� ������">?</span><span style="cursor:pointer; padding:2px;" onclick="window.mbHandleArrowMove(event, this, -1)" title="����� ������">?</span></div>له عملة<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="6" style="position:relative; cursor:grab; padding-top:20px;" draggable="true" ondragstart="window.mbHandleDragStart(event)" ondragover="window.mbHandleDragOver(event)" ondragenter="window.mbHandleDragEnter(event)" ondragleave="window.mbHandleDragLeave(event)" ondrop="window.mbHandleDrop(event)" ondragend="window.mbHandleDragEnd(event)"><div style="position:absolute; top:2px; left:0; right:0; display:flex; justify-content:space-between; padding:0 5px; font-size:11px; opacity:0.5; z-index:10;"><span style="cursor:pointer; padding:2px;" onclick="window.mbHandleArrowMove(event, this, 1)" title="����� ������">?</span><span style="cursor:pointer; padding:2px;" onclick="window.mbHandleArrowMove(event, this, -1)" title="����� ������">?</span></div>عليه<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="7" style="position:relative; cursor:grab; padding-top:20px;" draggable="true" ondragstart="window.mbHandleDragStart(event)" ondragover="window.mbHandleDragOver(event)" ondragenter="window.mbHandleDragEnter(event)" ondragleave="window.mbHandleDragLeave(event)" ondrop="window.mbHandleDrop(event)" ondragend="window.mbHandleDragEnd(event)"><div style="position:absolute; top:2px; left:0; right:0; display:flex; justify-content:space-between; padding:0 5px; font-size:11px; opacity:0.5; z-index:10;"><span style="cursor:pointer; padding:2px;" onclick="window.mbHandleArrowMove(event, this, 1)" title="����� ������">?</span><span style="cursor:pointer; padding:2px;" onclick="window.mbHandleArrowMove(event, this, -1)" title="����� ������">?</span></div>له<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="8" style="position:relative; cursor:grab; padding-top:20px;" draggable="true" ondragstart="window.mbHandleDragStart(event)" ondragover="window.mbHandleDragOver(event)" ondragenter="window.mbHandleDragEnter(event)" ondragleave="window.mbHandleDragLeave(event)" ondrop="window.mbHandleDrop(event)" ondragend="window.mbHandleDragEnd(event)"><div style="position:absolute; top:2px; left:0; right:0; display:flex; justify-content:space-between; padding:0 5px; font-size:11px; opacity:0.5; z-index:10;"><span style="cursor:pointer; padding:2px;" onclick="window.mbHandleArrowMove(event, this, 1)" title="����� ������">?</span><span style="cursor:pointer; padding:2px;" onclick="window.mbHandleArrowMove(event, this, -1)" title="����� ������">?</span></div>ملحوظة<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                    </tr>
                </thead>
                <tbody id="balances-tbody">
                    <tr>
                        <td colspan="9" class="empty-state">
                            <span style="font-size:3rem; display:block; margin-bottom:15px;">🔄</span>
                            <div>جاري تحميل وعرض الأرصدة تلقائياً...</div>
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
            


            <!-- Visual Intelligence Analytics Hub (المركز البصري التحليلي والرسوم البيانية) -->
            <div id="visual-intelligence-hub" class="visual-hub-container no-print" style="display: none;">
                
                <!-- Section Header -->
                <div class="hub-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 14px; height: 14px; border-radius: 50%; background: #38bdf8; box-shadow: 0 0 15px #38bdf8; animation: pulse 2s infinite;"></div>
                        <h3 style="margin: 0; font-size: 1.35rem; font-weight: 900; color: #f8fafc; letter-spacing: -0.5px;">
                            📈 التقرير البصري التحليلي للأرصدة وهيكل السيولة (SDG + $ USD)
                        </h3>
                        <span class="hub-badge" style="background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 800;">
                            ✨ مزدوج العملتين (SDG + $ USD) 3.0
                        </span>
                    </div>
                    <div style="display: flex; gap: 8px;" class="no-print">
                        <button class="hub-tab-btn active" onclick="switchVisualTab('all')" id="tab-all">🚀 المشهد الكامل</button>
                        <button class="hub-tab-btn" onclick="switchVisualTab('partners')" id="tab-partners">👥 العملاء والموردين</button>
                        <button class="hub-tab-btn" onclick="switchVisualTab('treasury')" id="tab-treasury">🏦 الخزن والبنوك</button>
                    </div>
                </div>

                <!-- Visual Indicator Cards Row (أسهم ومؤشرات بصرية) -->
                <div class="visual-cards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-bottom: 30px;">
                    
                    <!-- Card 1: مؤشر الاتجاه والتغطية -->
                    <div class="v-card glow-sky">
                        <div class="v-card-header">
                            <span class="v-card-title">مؤشر صافي الاتجاه والسيولة (SDG + $ USD)</span>
                            <span class="v-card-icon" id="ind-trend-arrow" style="font-size: 1.4rem; color: #34d399;">↗️</span>
                        </div>
                        <div class="v-card-value" id="ind-net-val">0.00 SDG</div>
                        <div class="v-card-trend">
                            <span id="ind-trend-badge" class="v-badge badge-success">↑ فائض سيولة مستحقة</span>
                            <span id="ind-coverage-pct" style="font-size: 0.8rem; color: #94a3b8; font-weight: bold;">تغطية: 0%</span>
                        </div>
                        <div class="v-progress-bg">
                            <div class="v-progress-bar" id="ind-coverage-bar" style="width: 50%; background: linear-gradient(90deg, #10b981, #34d399);"></div>
                        </div>
                    </div>

                    <!-- Card 2: مؤشر تركز المخاطر (Concentration Risk) -->
                    <div class="v-card glow-purple">
                        <div class="v-card-header">
                            <span class="v-card-title">مؤشر التركّز المالي للأرصدة</span>
                            <span class="v-card-icon">🎯</span>
                        </div>
                        <div class="v-card-value" id="ind-top3-pct">0.0%</div>
                        <div class="v-card-trend">
                            <span id="ind-risk-badge" class="v-badge badge-purple">حجم أضخم 3 حسابات</span>
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
                            <span id="ind-active-count" style="font-size: 0.8rem; color: #94a3b8; font-weight: bold;">0 حساب نشط</span>
                        </div>
                        <div class="v-progress-bg">
                            <div class="v-progress-bar" id="ind-ratio-bar" style="width: 50%; background: linear-gradient(90deg, #38bdf8, #818cf8);"></div>
                        </div>
                    </div>

                    <!-- Card 4: درجة الصحة المالية الكلية -->
                    <div class="v-card glow-amber">
                        <div class="v-card-header">
                            <span class="v-card-title">مؤشر جودة المحفظة المالية</span>
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

                <!-- ════════════════════════════════════════════════════════════
                     1. التقرير البصري الأول: الهيكل المالي ونشرة الحسابات الأكبر
                     ════════════════════════════════════════════════════════════ -->
                <div class="v-section-banner">
                    <div class="v-section-title">
                        <span>💎</span> 1. التقرير البصري الأول: الهيكل المالي ونشرة الحسابات الأكبر (Capital Structure & Dominance)
                    </div>
                    <div class="v-section-badge">
                        مزدوج العملتين: (SDG + $ USD)
                    </div>
                </div>

                <!-- Charts Row 1: Donut & Hollow Donut Charts -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-bottom: 25px;">
                    
                    <!-- Chart 1: Hollow Donut Chart - Account Categories Distribution -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🍩 كعكة مجوفة: أين تتوزع أموالك وحساباتك؟</h4>
                                <span style="font-size: 0.72rem; background: rgba(56, 189, 248, 0.18); color: #38bdf8; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(56, 189, 248, 0.3);">💡 بالجنيه (SDG) + بالدولار ($ USD)</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: يوضح لك أي فئة تشغل الحجم الأكبر من أعمالك (العملاء أم الخزن أم الموردين) بالعملتين متلازمتين معاً</span>
                        </div>
                        <div id="chart-hollow-donut-categories" style="min-height: 320px;"></div>
                    </div>

                    <!-- Chart 2: Hollow Donut Chart - Top Debtor Exposure -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🍩 كعكة مفرغة: أين تتركز أكبر الديون لك؟ (أضخم العملاء)</h4>
                                <span style="font-size: 0.72rem; background: rgba(168, 85, 247, 0.18); color: #c084fc; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(168, 85, 247, 0.3);">🎯 بالجنيه (SDG) + بالدولار ($ USD)</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: أكثر 5 حسابات مطالبة بالسداد لك بالعملتين المحلية والدولار، لمعرفة من يجب التواصل معه أولاً</span>
                        </div>
                        <div id="chart-hollow-donut-top-debtors" style="min-height: 320px;"></div>
                    </div>

                </div>

                <!-- Charts Row 2: Top Creditors Donut Chart & Line Spectrum -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-bottom: 25px;">
                    
                    <!-- Chart 6: Hollow Donut Chart - Top Creditor Suppliers -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🍩 كعكة مجوفة: التزامات الموردين (أعلى 5 موردين دائنين)</h4>
                                <span style="font-size: 0.72rem; background: rgba(239, 68, 68, 0.18); color: #f87171; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(239, 68, 68, 0.3);">🔻 بالجنيه (SDG) + بالدولار ($ USD)</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: يوضح الحسابات الأكثر استحقاقاً للأموال من الموردين بالعملتين المحلية والدولار لجدولة السداد</span>
                        </div>
                        <div id="chart-hollow-donut-top-creditors" style="min-height: 320px;"></div>
                    </div>

                    <!-- Chart 3: Smooth Curved Gradient Line Chart -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">📈 المنحنى المتدرج للأرصدة (Balance Spectrum)</h4>
                                <span style="font-size: 0.72rem; background: rgba(16, 185, 129, 0.18); color: #34d399; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(16, 185, 129, 0.3);">🌊 تدفق مريح بالعملتين</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: انسيابية تدفق المبالغ المالية من الأكبر إلى الأصغر بدون ضغوط بصرية وبشكل متلازم بالعملتين</span>
                        </div>
                        <div id="chart-curved-line-spectrum" style="min-height: 320px;"></div>
                    </div>

                </div>

                <!-- ════════════════════════════════════════════════════════════
                     2. التقرير البصري الثاني: ميزان الحقوق والالتزامات ومخاطر التركّز
                     ════════════════════════════════════════════════════════════ -->
                <div class="v-section-banner">
                    <div class="v-section-title">
                        <span>⚖️</span> 2. التقرير البصري الثاني: ميزان الحقوق والالتزامات ومخاطر التركّز (Rights vs Obligations & Exposure)
                    </div>
                    <div class="v-section-badge">
                        مزدوج العملتين: (SDG + $ USD)
                    </div>
                </div>

                <!-- Charts Row 3: Bi-directional Comparative Bar & Polygon Radar -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-bottom: 25px;">
                    
                    <!-- Chart 4: Bi-Directional Comparative Column Chart -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">📊 أعمدة المقارنة المزدوجة: (ما لك 🟢) ضد (ما عليك 🔴)</h4>
                                <span style="font-size: 0.72rem; background: rgba(245, 158, 11, 0.18); color: #fbbf24; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(245, 158, 11, 0.3);">⚖️ بالجنيه (SDG) + بالدولار ($ USD)</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: يظهر بوضوح ملخص حقوقك المالية مقارنةً بالتزاماتك في كل قطاع متلازمة بالعملتين معاً</span>
                        </div>
                        <div id="chart-bidirectional-bar" style="min-height: 320px;"></div>
                    </div>

                    <!-- Chart 9: Polygon Radar Equilibrium Chart -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🎯 مخطط الرادار الخماسي: التوازن المالي للأقسام</h4>
                                <span style="font-size: 0.72rem; background: rgba(168, 85, 247, 0.18); color: #c084fc; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(168, 85, 247, 0.3);">🕸️ بالجنيه (SDG) + بالدولار ($ USD)</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: شبكة رادار خماسية تفاعلية توضح اتزان جميع الأنشطة بلمحة واحدة وبالعملتين معاً</span>
                        </div>
                        <div id="chart-radar-equilibrium" style="min-height: 320px;"></div>
                    </div>

                </div>

                <!-- Charts Row 4: Horizontal Bar Chart - Top 10 Accounts Ranking -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-bottom: 25px;">
                    
                    <!-- Chart 7: Horizontal Bar Chart - Top 10 Accounts Ranking -->
                    <div class="v-chart-card" style="grid-column: span 1 / -1;">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">📊 أعمدة أفقية هرمية: ترتيب أضخم 10 حسابات</h4>
                                <span style="font-size: 0.72rem; background: rgba(6, 182, 212, 0.18); color: #22d3ee; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(6, 182, 212, 0.3);">👑 بالجنيه (SDG) + بالدولار ($ USD)</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: شريط أفقي يُظهر أطول الأعمدة للحسابات الأكثر ثقلاً بالأرقام متلازمة بالعملتين معاً</span>
                        </div>
                        <div id="chart-horizontal-top-bar" style="min-height: 320px;"></div>
                    </div>

                </div>

                <!-- ════════════════════════════════════════════════════════════
                     3. التقرير البصري الثالث: مؤشر الأمان المالي ومقياس تدفق السيولة
                     ════════════════════════════════════════════════════════════ -->
                <div class="v-section-banner">
                    <div class="v-section-title">
                        <span>🧭</span> 3. التقرير البصري الثالث: مؤشر الأمان المالي ومقياس تدفق السيولة (Liquidity Health & Flow Spectrum)
                    </div>
                    <div class="v-section-badge">
                        مزدوج العملتين: (SDG + $ USD)
                    </div>
                </div>

                <!-- Charts Row 5: Radial Gauge & Fluid Area Spectrum -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-bottom: 25px;">
                    
                    <!-- Chart 5: Semi-Circle Donut Radial Gauge -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🧭 عدّاد ومقياس صحة ومأمونية السيولة (Liquidity Health Gauge)</h4>
                                <span style="font-size: 0.72rem; background: rgba(244, 63, 94, 0.18); color: #fb7185; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(244, 63, 94, 0.3);">🛡️ بالجنيه (SDG) + بالدولار ($ USD)</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: نسبة مئوية مباشرة تعكس أمان وضعك المالي وسرعة التحرّك وبقيم صريحة بالعملتين</span>
                        </div>
                        <div id="chart-semicircle-gauge" style="min-height: 320px;"></div>
                    </div>

                    <!-- Chart 8: Filled Area Fluid Curve -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🌊 منحنى المساحة المتدرجة الممتلئة (Fluid Area Curve)</h4>
                                <span style="font-size: 0.72rem; background: rgba(16, 185, 129, 0.18); color: #34d399; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(16, 185, 129, 0.3);">💧 بالجنيه (SDG) + بالدولار ($ USD)</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: مساحة ملونة انسيابية مريحة للعين تعكس الحجم التراكمي للسيولة بالعملتين معاً</span>
                        </div>
                        <div id="chart-cumulative-area-fluid" style="min-height: 320px;"></div>
                    </div>

                </div>

                <!-- Charts Row 6: Solid Pie & Multi-Radial Rings Gauge -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-bottom: 25px;">
                    
                    <!-- Chart 10: Solid Pie Chart - Active vs Zero Accounts -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🥧 كعكة دائريّة مصمتة (Pie Chart): الحسابات النشطة مقابل الصفرية</h4>
                                <span style="font-size: 0.72rem; background: rgba(245, 158, 11, 0.18); color: #fbbf24; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(245, 158, 11, 0.3);">🥧 بالجنيه (SDG) + بالدولار ($ USD)</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: دائرة كاملة مكسوة بالألوان تظهر نسبة الحسابات ذات الأرصدة المباشرة بالعملتين متلازمتين معاً</span>
                        </div>
                        <div id="chart-solid-pie-active-zero" style="min-height: 320px;"></div>
                    </div>

                    <!-- Chart 11: Multi-Radial Rings Gauge -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🔘 حلقات دوائر مضيئة متداخلة (Multi-Radial Rings)</h4>
                                <span style="font-size: 0.72rem; background: rgba(56, 189, 248, 0.18); color: #38bdf8; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(56, 189, 248, 0.3);">💫 بالجنيه (SDG) + بالدولار ($ USD)</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: ثلاث حلقات دائرية ملونة متداخلة تقارن حصة كل قطاع بنسبة مئوية واضحة وبالعملتين متلازمتين</span>
                        </div>
                        <div id="chart-multi-radial-rings" style="min-height: 320px;"></div>
                    </div>

                </div>

                <!-- ════════════════════════════════════════════════════════════
                     4. التقرير البصري الرابع: المدرّج الشامل لجميع الموردين والعملاء
                     ════════════════════════════════════════════════════════════ -->
                <div class="v-section-banner">
                    <div class="v-section-title">
                        <span>📊</span> 4. التقرير البصري الرابع: المدرّج التفصيلي الشامل لجميع الموردين والعملاء (Suppliers & Customers Master Columns)
                    </div>
                    <div class="v-section-badge">
                        مزدوج العملتين: (SDG + $ USD)
                    </div>
                </div>

                <!-- Charts Row 7: DEDICATED COLUMN CHARTS FOR ALL SUPPLIERS & ALL CUSTOMERS -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-bottom: 25px;">
                    
                    <!-- Chart 12: Dedicated Column Chart for ALL Suppliers -->
                    <div class="v-chart-card" style="grid-column: span 1 / -1;">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.1rem; font-weight: 900;">🏬 مدرّج أعمدة الموردين التفصيلي: (ارتفاع العمود بحسب حجم المبلغ دائن "له" ومدين "عليه" بالعملتين)</h4>
                                <span style="font-size: 0.75rem; background: rgba(244, 63, 94, 0.2); color: #fb7185; padding: 4px 12px; border-radius: 12px; font-weight: 900; border: 1px solid rgba(244, 63, 94, 0.4);">📊 بالجنيه (SDG) + بالدولار ($ USD) فوق كل عمود</span>
                            </div>
                            <span style="font-size: 0.8rem; color: #94a3b8;">توضيح بسيط: أعمدة شاهقة ترتفع وتتفاوت بوضوح حسب حجم مستحقات كل مورد، مع كتابة المبلغين (بالجنيه السوداني + الدولار الأجنبي) فوق العمود ومباشرة في التلميح</span>
                        </div>
                        <div id="chart-suppliers-columns" style="min-height: 360px;"></div>
                    </div>

                    <!-- Chart 13: Dedicated Column Chart for ALL Customers -->
                    <div class="v-chart-card" style="grid-column: span 1 / -1;">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.1rem; font-weight: 900;">👥 مدرّج أعمدة العملاء التفصيلي: (ارتفاع العمود بحسب حجم الديون مدين "عليه" ودائن "له" بالعملتين)</h4>
                                <span style="font-size: 0.75rem; background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 4px 12px; border-radius: 12px; font-weight: 900; border: 1px solid rgba(16, 185, 129, 0.4);">📊 بالجنيه (SDG) + بالدولار ($ USD) فوق كل عمود</span>
                            </div>
                            <span style="font-size: 0.8rem; color: #94a3b8;">توضيح بسيط: أعمدة بصرية مرتفعة تُميز مطالبات العملاء بوضوح شديد حسب المبلغ، مع كتابة المبلغين (بالجنيه السوداني + الدولار الأجنبي) فوق العمود ومباشرة في التلميح</span>
                        </div>
                        <div id="chart-customers-columns" style="min-height: 360px;"></div>
                    </div>

                </div>

                <!-- Charts Row 8: Treemap, Waterfall & Polar Area Spectrum -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 20px; margin-bottom: 25px;">
                    
                    <!-- Chart 14: Treemap Accounts -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🗺️ خريطة المساحة المربّعة للمحفظة (Treemap)</h4>
                                <span style="font-size: 0.72rem; background: rgba(168, 85, 247, 0.18); color: #c084fc; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(168, 85, 247, 0.3);">🗺️ مساحات ملوّنة</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: مربعات تترتب مساحاتها تلقائياً بحسب القيمة المالية للحساب</span>
                        </div>
                        <div id="chart-treemap-accounts" style="min-height: 340px;"></div>
                    </div>

                    <!-- Chart 15: Waterfall Net Balance -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">📉 شلال حركة الصافي المالي (Waterfall)</h4>
                                <span style="font-size: 0.72rem; background: rgba(56, 189, 248, 0.18); color: #38bdf8; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(56, 189, 248, 0.3);">🌊 تدفق الشلال</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: إظهار الفروقات الإيجابية والسلبية التي تصب في الصافي النهائي</span>
                        </div>
                        <div id="chart-waterfall-net" style="min-height: 340px;"></div>
                    </div>

                    <!-- Chart 16: Polar Area Categories -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🧭 المخطط القطبي المحوري (Polar Area Spectrum)</h4>
                                <span style="font-size: 0.72rem; background: rgba(245, 158, 11, 0.18); color: #fbbf24; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(245, 158, 11, 0.3);">🧭 قطاعات دائريّة</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: قطاعات مرسومة من المركز توضح اتساع نطاق كل قسم</span>
                        </div>
                        <div id="chart-polar-categories" style="min-height: 340px;"></div>
                    </div>

                </div>

                <!-- Charts Row 9: 3 NEW HIGHLY COMPREHENSIVE CHARTS (3 تقارير بصرية جديدة أكثر شمولية) -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-bottom: 25px;">
                    
                    <!-- Chart 17: Currency Share Donut -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🍩 كعكة توزيع الأرصدة حسب العملات (Currency Volume Share)</h4>
                                <span style="font-size: 0.72rem; background: rgba(16, 185, 129, 0.18); color: #34d399; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(16, 185, 129, 0.3);">🔱 وزن العملات</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: ثقل وحصة كل عملة (سوداني / دولار أجنبي / غيرها) بالعملتين فوراً</span>
                        </div>
                        <div id="chart-currency-share-donut" style="min-height: 330px;"></div>
                    </div>

                    <!-- Chart 18: Treasury & Cash Accounts Column Chart -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🏛️ مدرّج النقدية السائلة بالخزن والبنوك (Treasury & Cash Columns)</h4>
                                <span style="font-size: 0.72rem; background: rgba(56, 189, 248, 0.18); color: #38bdf8; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(56, 189, 248, 0.3);">🏦 السيولة السائلة</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: ارتفاع أعمدة الخزن والبنوك بحسب الرصيد الفعلي المتاح بالعملتين</span>
                        </div>
                        <div id="chart-treasury-cash-columns" style="min-height: 330px;"></div>
                    </div>

                    <!-- Chart 19: Dual Currency Cumulative Flow Spectrum Curve -->
                    <div class="v-chart-card" style="grid-column: span 1 / -1;">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.1rem; font-weight: 900;">📈 منحنى التراكم والترافق المزدوج للعملتين (SDG vs USD Cumulative Spectrum)</h4>
                                <span style="font-size: 0.75rem; background: rgba(245, 158, 11, 0.2); color: #fbbf24; padding: 4px 12px; border-radius: 12px; font-weight: 900; border: 1px solid rgba(245, 158, 11, 0.4);">🌊 تراكم العملتين</span>
                            </div>
                            <span style="font-size: 0.8rem; color: #94a3b8;">توضيح بسيط: منحنيات متدرجة ملونة تقارن تراكم مبالغ الجنيه السوداني مقابل المعادل بالدولار الأجنبي</span>
                        </div>
                        <div id="chart-cumulative-dual-currency-flow" style="min-height: 350px;"></div>
                    </div>

                </div>

                <!-- Charts Row 10: 3 ADDITIONAL COMPREHENSIVE ADVANCED MODELS (3 نماذج إضافية شاملة) -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px;">
                    
                    <!-- Chart 20: Pyramidal Distribution of Debts and Credits -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🔺 الهرم المزدوج لتركز الحقوق والالتزامات (Debts Pyramidal Distribution)</h4>
                                <span style="font-size: 0.72rem; background: rgba(168, 85, 247, 0.2); color: #c084fc; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(168, 85, 247, 0.4);">🔺 الهرم المزدوج</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: توزيع هرمي متقابل لأكبر الذمم المدينة والدائنة بالعملتين</span>
                        </div>
                        <div id="chart-pyramid-debt-credit" style="min-height: 340px;"></div>
                    </div>

                    <!-- Chart 21: Category Average Balance Bar Chart -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">📊 متوسط رصيد الحساب الواحدة في كل فئة (Category Average Balance)</h4>
                                <span style="font-size: 0.72rem; background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(16, 185, 129, 0.4);">📐 متوسطات الفئات</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: يُظهر متوسط الحجم المالي للحساب الواحد لدى العملاء والموردين والخزن</span>
                        </div>
                        <div id="chart-category-avg-balance" style="min-height: 340px;"></div>
                    </div>

                    <!-- Chart 22: Net Cashflow Spectrum Curve -->
                    <div class="v-chart-card" style="grid-column: span 1 / -1;">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.1rem; font-weight: 900;">🌊 منحنى التوازن وتدفق الصافي التراكمي الشامل (Net Cashflow Spectrum Curve)</h4>
                                <span style="font-size: 0.75rem; background: rgba(56, 189, 248, 0.2); color: #38bdf8; padding: 4px 12px; border-radius: 12px; font-weight: 900; border: 1px solid rgba(56, 189, 248, 0.4);">🌊 التوازن الصافي</span>
                            </div>
                            <span style="font-size: 0.8rem; color: #94a3b8;">توضيح بسيط: منحنى انسيابي متدرج يعكس الصافي الكلي المتبقي في المؤسسة بالعملتين فوراً</span>
                        </div>
                        <div id="chart-net-cashflow-spectrum" style="min-height: 360px;"></div>
                    </div>

                </div>

                <!-- Charts Row 11: CREDIT LIMITS & DEBT AGING MODELS (مخططات حد الائتمان وأعمار الديون) -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-top: 25px;">
                    
                    <!-- Chart 23: Credit Limit vs Debt Used vs Remaining Credit Allowance -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">💳 مقارنة حد الائتمان وسقف المديونية مع المتبقي المتاح (Credit Limit vs Debt Allowance)</h4>
                                <span style="font-size: 0.72rem; background: rgba(56, 189, 248, 0.2); color: #38bdf8; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(56, 189, 248, 0.4);">🛡️ سقف الائتمان</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: مقارنة بصرية سريعة لكل عميل بين المديونية الحالية المستهلكة والمتبقي من حد الائتمان</span>
                        </div>
                        <div id="chart-credit-limit-vs-debt" style="min-height: 360px;"></div>
                    </div>

                    <!-- Chart 24: Debt Aging Spectrum Brackets -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">⏳ التوزيع البصري لأعمار الديون والمديونية (Debt Aging Spectrum Brackets)</h4>
                                <span style="font-size: 0.72rem; background: rgba(245, 158, 11, 0.2); color: #fbbf24; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(245, 158, 11, 0.4);">⌛ أعمار المديونية</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: تصنيف الديون حسب الأقدمية (0-30 يوم، 31-60 يوم، 61-90 يوم، +90 يوم) بالعملتين</span>
                        </div>
                        <div id="chart-debt-aging-spectrum" style="min-height: 360px;"></div>
                    </div>

                </div>

                <!-- Charts Row 12: ADVANCED CREDIT UTILIZATION & DEBT AGING TREEMAP & POLAR (نماذج إضافية أكثر شمولية) -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 20px; margin-top: 25px;">
                    
                    <!-- Chart 25: Donut Credit Limit Utilization -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🍩 كعكة نسبة استنفاذ سقف الائتمان (Portfolio Credit Utilization Donut)</h4>
                                <span style="font-size: 0.72rem; background: rgba(244, 63, 94, 0.2); color: #fb7185; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(244, 63, 94, 0.4);">🎯 نسبة استهلاك الائتمان</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: نسبة المستهلك حالياً من إجمالي التسهيلات والائتمان مقابل المتبقي بالعملتين</span>
                        </div>
                        <div id="chart-credit-utilization-donut" style="min-height: 340px;"></div>
                    </div>

                    <!-- Chart 26: Treemap Debt Aging Risk Matrix -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🗺️ خريطة مسافات ومساحات مخاطر أعمار الديون (Aging Risk Treemap Matrix)</h4>
                                <span style="font-size: 0.72rem; background: rgba(168, 85, 247, 0.2); color: #c084fc; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(168, 85, 247, 0.4);">🗺️ مساحات الخطورة</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: مربعات ملونة تتوزع مساحاتها بحسب حجم الديون المتأخرة وحرجة الأقدمية</span>
                        </div>
                        <div id="chart-aging-risk-treemap" style="min-height: 340px;"></div>
                    </div>

                    <!-- Chart 27: Polar Area Credit vs Aging Matrix -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🧭 المخطط القطبي لرادار السقوف المالية وأعمار الذمم (Aging & Credit Polar Radar)</h4>
                                <span style="font-size: 0.72rem; background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(16, 185, 129, 0.4);">🧭 قطب السقوف والأعمار</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: قطاعات مرسومة من المركز توضح اتساع نطاق كل فئة عمرية للديون بالعملتين</span>
                        </div>
                        <div id="chart-aging-credit-radar" style="min-height: 340px;"></div>
                    </div>

                </div>

                <!-- ════════════════════════════════════════════════════════════
                     5. التقرير البصري الخامس: المؤشرات المبتكرة والجدارة المالية ومتابعة الحركة
                     ════════════════════════════════════════════════════════════ -->
                <div class="v-section-banner">
                    <div class="v-section-title">
                        <span>🚀</span> 5. التقرير البصري الخامس: مؤشرات الجدارة، حركة المحفظة، والأمان النقدي الفوري
                    </div>
                    <div class="v-section-badge">
                        مزدوج العملتين: (SDG + $ USD)
                    </div>
                </div>

                <!-- Charts Row 13: 6 NEW EASY-TO-UNDERSTAND POWERFUL ANALYTICAL REPORTS -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-top: 25px;">
                    
                    <!-- Chart 28: Financial Solvency & Risk Banding Donut -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🛡️ كعكة ميزان الجدارة المالية وتصنيف مستويات الخطورة (Solvency Risk Bands)</h4>
                                <span style="font-size: 0.72rem; background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(16, 185, 129, 0.4);">🛡️ مستويات الأمان</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: تصنيف المحفظة إلى 4 فئات أمان (فائقة الأمان، متوازنة، تحت المتابعة، عالية الخطورة) بالعملتين</span>
                        </div>
                        <div id="chart-solvency-risk-donut" style="min-height: 340px;"></div>
                    </div>

                    <!-- Chart 29: Period Movement Waterfall -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">📉 شلال حركة السيولة والذمم بين أول وأخر الفترة (Period Movement Waterfall)</h4>
                                <span style="font-size: 0.72rem; background: rgba(56, 189, 248, 0.2); color: #38bdf8; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(56, 189, 248, 0.4);">🌊 شلال الحركة</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: يوضح للشخص غير المحاسب كيف تطورت الأرصدة من البداية إلى الصافي الحالي بالعملتين</span>
                        </div>
                        <div id="chart-period-movement-waterfall" style="min-height: 340px;"></div>
                    </div>

                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-top: 25px;">
                    
                    <!-- Chart 30: Tri-Bar Credit vs Exposure vs Expected Collections -->
                    <div class="v-chart-card" style="grid-column: span 1 / -1;">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.1rem; font-weight: 900;">📊 المدرّج الثلاثي للتسهيلات والديون والتحصيلات المتوقعة (Tri-Bar Credit & Exposure)</h4>
                                <span style="font-size: 0.75rem; background: rgba(245, 158, 11, 0.2); color: #fbbf24; padding: 4px 12px; border-radius: 12px; font-weight: 900; border: 1px solid rgba(245, 158, 11, 0.4);">📊 مقارنة ثلاثية بالعملتين</span>
                            </div>
                            <span style="font-size: 0.8rem; color: #94a3b8;">توضيح بسيط: 3 أعمدة متجاورة تقارن السقف المالي والديون الفعلية والسيولة المتوقع تحصيلها عاجلاً</span>
                        </div>
                        <div id="chart-tribar-credit-exposure" style="min-height: 360px;"></div>
                    </div>

                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 20px; margin-top: 25px;">
                    
                    <!-- Chart 31: Turnover & Velocity Radar -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🎯 رادار سرعة دوران السيولة الذاتية للأنشطة (Velocity Radar)</h4>
                                <span style="font-size: 0.72rem; background: rgba(168, 85, 247, 0.2); color: #c084fc; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(168, 85, 247, 0.4);">🎯 دوران السيولة</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: شبكة رادار تعكس سرعة تحول المستحقات إلى نقدية سائلة في كل قسم بالعملتين</span>
                        </div>
                        <div id="chart-turnover-velocity-radar" style="min-height: 340px;"></div>
                    </div>

                    <!-- Chart 32: Dormancy & Recency Aging Donut -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">⏱️ كعكة التوزيع الزمني لحداثة نشاط الحسابات (Dormancy Aging Donut)</h4>
                                <span style="font-size: 0.72rem; background: rgba(244, 63, 94, 0.2); color: #fb7185; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(244, 63, 94, 0.4);">⏱️ حداثة النشاط</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: تمييز الحسابات ذات الحركة الحديثة مقابل الحسابات الخاملة الراكدة بالعملتين</span>
                        </div>
                        <div id="chart-dormancy-recency-donut" style="min-height: 340px;"></div>
                    </div>

                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-top: 25px;">
                    
                    <!-- Chart 33: Instant Cash-to-Debt Coverage Spectrum -->
                    <div class="v-chart-card" style="grid-column: span 1 / -1;">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.1rem; font-weight: 900;">🌊 منحنى التغطية النقدية الفورية للالتزامات (Instant Cash-to-Debt Coverage Spectrum)</h4>
                                <span style="font-size: 0.75rem; background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 4px 12px; border-radius: 12px; font-weight: 900; border: 1px solid rgba(16, 185, 129, 0.4);">🌊 التغطية النقدية الفورية</span>
                            </div>
                            <span style="font-size: 0.8rem; color: #94a3b8;">توضيح بسيط: مقارنة مساحية ملونة بين النقدية الجاهزة فوراً في الخزن والبنوك والديون الواجبة السداد بالعملتين</span>
                        </div>
                        <div id="chart-instant-cash-spectrum" style="min-height: 360px;"></div>
                    </div>

                </div>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- Include html2pdf library to export PDF -->
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script defer src="js/localforage.min.js"></script>
<script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
<script defer src="js/mainbalances-report.js?v=<?php echo time(); ?>"></script>
<script>
// Debug wrapper: override runReport to catch and display errors
document.addEventListener('DOMContentLoaded', function() {
    // Wait for DBReady to fire, then wrap runReport
    var checkInterval = setInterval(function() {
        if (typeof window.runReport === 'function' && !window._runReportWrapped) {
            window._runReportWrapped = true;
            var originalRunReport = window.runReport;
            window.runReport = function() {
                try {
                    console.log('[DEBUG] runReport called. ALL_PARTNERS:', (typeof ALL_PARTNERS !== 'undefined' ? ALL_PARTNERS.length : 'UNDEF'), 'ALL_TXNS:', (typeof ALL_TXNS !== 'undefined' ? ALL_TXNS.length : 'UNDEF'));
                    originalRunReport.apply(this, arguments);
                    console.log('[DEBUG] runReport completed successfully');
                } catch(e) {
                    alert('خطأ في التقرير: ' + e.message + '\n\nStack: ' + e.stack);
                    console.error('[DEBUG] runReport error:', e);
                }
            };
            clearInterval(checkInterval);
            console.log('[DEBUG] runReport wrapped successfully');
        }
    }, 200);
    
    // Also check after 10 seconds if runReport was ever defined
    setTimeout(function() {
        if (typeof window.runReport !== 'function') {
            console.error('[DEBUG] runReport is still not defined after 10 seconds!');
            var tbody = document.getElementById('balances-tbody');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="9" style="color:red;text-align:center;padding:20px;font-size:1.2em;">⚠️ خطأ: لم يتم تحميل ملف التقرير بشكل صحيح. يرجى مسح الكاش (Ctrl+Shift+Delete) وإعادة المحاولة.</td></tr>';
            }
        }
    }, 10000);
});
</script>
<!-- Customization Sidebar Modal (Copied from General Ledger) -->
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
            <label style="display:block; margin-bottom:10px; font-weight:800; color:#38bdf8;">📝 خطوط الأرقام الفائقة الوضوح (5 خطوط إنجليزية):</label>
            <div class="font-chips-container" style="display:flex; flex-wrap:wrap; gap:6px;">
                <button class="font-chip" onclick="changeNumberFont('num-font-jetbrains')" id="btn-font-num-font-jetbrains" style="font-family: 'JetBrains Mono', monospace;">1. JetBrains Mono</button>
                <button class="font-chip" onclick="changeNumberFont('num-font-roboto')" id="btn-font-num-font-roboto" style="font-family: 'Roboto Mono', monospace;">2. Roboto Mono</button>
                <button class="font-chip" onclick="changeNumberFont('num-font-firacode')" id="btn-font-num-font-firacode" style="font-family: 'Fira Code', monospace;">3. Fira Code</button>
                <button class="font-chip" onclick="changeNumberFont('num-font-inter')" id="btn-font-num-font-inter" style="font-family: 'Inter', sans-serif;">4. Inter Font</button>
                <button class="font-chip" onclick="changeNumberFont('num-font-poppins')" id="btn-font-num-font-poppins" style="font-family: 'Poppins', sans-serif;">5. Poppins Font</button>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.renderBalancesPresets === 'function') {
        window.renderBalancesPresets();
    }
});
</script>
</body>
</html>


