<?php $page_title = 'تقرير الأرصدة الإجمالية'; ?>
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
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الأرصدة الإجمالية | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <link rel="stylesheet" href="css/sales-invoice.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
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

        .btn-run { background: #38bdf8; color: #0f172a; border: none; padding: 8px 18px; border-radius: 8px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s; font-family: inherit; font-size: 0.9rem; }
        .btn-run:hover { background: #0ea5e9; transform: translateY(-1px); }
        .btn-action { background: rgba(255,255,255,0.05); color: #f8fafc; border: 1px solid rgba(255,255,255,0.1); padding: 7px 14px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s; font-family: inherit; }
        .btn-action:hover { background: rgba(255,255,255,0.1); }

        .audit-stamp {
            margin-top: 20px; padding: 15px; background: rgba(56,189,248,0.05);
            border: 1px dashed rgba(56,189,248,0.3); border-radius: 8px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            color: #38bdf8; font-weight: bold; font-size: 0.9rem;
        }

        /* Dynamic Enterprise Variables & Clickable Numbers */
        #v-classic-ent { background: #fff; padding: 40px; border-radius: 8px; color: #111827; }
        #v-classic-ent table { --fs-text: 1.15rem; --fs-num: 1.15rem; --font-num: 'JetBrains Mono', monospace; transition: all 0.3s; width: 100%; table-layout: fixed; border-collapse: collapse; text-align: right; border: 4px solid #000000; }
        #v-classic-ent th { position: relative; background: #e2e8f0; color: #000000; padding: 10px 12px; border: 3px solid #111827; line-height: 1.2; font-size: calc(var(--fs-text) * 1.1); font-weight: 900; text-align: center; }
        #v-classic-ent td { padding: 5px 12px; border: 2px solid #475569; color: #000000; font-weight: 600; line-height: 1.3; font-size: var(--fs-text); text-align: center; }
        #v-classic-ent tr:nth-child(even) { background: #f9fafb; }
        
        #v-classic-ent td.num { 
            text-align: center;
            font-size: var(--fs-num); 
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            font-family: var(--font-num);
            font-weight: 600; color: #111827;
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
        
        /* The Focused Specific Number Feature */
        .focused-num {
            transform: scale(1.6) translateY(-2px) !important;
            color: #8b5cf6 !important;
            font-weight: 900 !important;
            background: #f3e8ff !important;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4), inset 0 0 0 2px #8b5cf6 !important;
            z-index: 50;
            display: inline-block;
            padding: 0 8px;
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
        :root.light-theme .text-amber-400, :root.light-theme span[style*="color: #fbbf24"], :root.light-theme span[style*="color:#fbbf24"] { color: #b45309 !important; }

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
            transition: all 0.3s ease;
        }
        .preset-btn {
            position: relative;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(30, 41, 59, 0.6));
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(var(--accent-color), 0.35);
            color: #e2e8f0;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 800;
            cursor: pointer;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 7px;
            transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            user-select: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2), inset 0 1px 1px rgba(255, 255, 255, 0.15);
        }
        
        .preset-btn[data-slot-id="default1"] { --accent-color: 234, 179, 8; font-weight: 900; }
        .preset-btn[data-slot-id="default2"] { --accent-color: 168, 85, 247; font-weight: 900; }
        .preset-btn[data-slot-id="1"] { --accent-color: 6, 182, 212; }
        .preset-btn[data-slot-id="2"] { --accent-color: 16, 185, 129; }
        .preset-btn[data-slot-id="3"] { --accent-color: 245, 158, 11; }
        
        @keyframes pulse-glow {
            0% { box-shadow: 0 0 6px rgba(var(--accent-color), 0.6); }
            50% { box-shadow: 0 0 16px rgba(var(--accent-color), 0.95); }
            100% { box-shadow: 0 0 6px rgba(var(--accent-color), 0.6); }
        }
        
        /* Glass Sheen Light Reflection */
        .preset-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -120%;
            width: 70%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transform: skewX(-22deg);
            transition: left 0.65s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none;
        }
        .preset-btn:hover::after {
            left: 140%;
        }

        .preset-btn::before {
            content: '';
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: rgb(var(--accent-color));
            box-shadow: 0 0 10px rgba(var(--accent-color), 0.9);
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            animation: pulse-glow 2s infinite ease-in-out;
        }
        .preset-btn:hover::before {
            transform: scale(1.5);
        }
        .preset-btn:hover {
            background: linear-gradient(135deg, rgba(var(--accent-color), 0.25), rgba(15, 23, 42, 0.85));
            border-color: rgba(var(--accent-color), 0.9);
            color: #ffffff;
            box-shadow: 0 0 22px rgba(var(--accent-color), 0.45), inset 0 0 10px rgba(var(--accent-color), 0.2);
            transform: translateY(-2px) scale(1.06);
        }
        .preset-btn.active {
            background: rgba(var(--accent-color), 0.22);
            border-color: rgba(var(--accent-color), 0.95);
            color: #ffffff;
            box-shadow: 0 0 25px rgba(var(--accent-color), 0.5), inset 0 1px 1px rgba(var(--accent-color), 0.3);
            transform: scale(1.04);
        }
        .preset-btn.holding {
            background: rgba(var(--accent-color), 0.35) !important;
            border-color: rgba(var(--accent-color), 1) !important;
            box-shadow: 0 0 35px rgba(var(--accent-color), 0.7) !important;
            transform: scale(0.96) translateY(0);
        }
        .preset-clock-svg {
            flex-shrink: 0;
            width: 17px;
            height: 17px;
            filter: drop-shadow(0 0 3px rgba(var(--accent-color), 0.6));
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.35s ease;
        }
        .preset-btn:hover .preset-clock-svg {
            transform: scale(1.35) rotate(18deg);
            filter: drop-shadow(0 0 8px rgba(var(--accent-color), 0.95));
        }
        .preset-clock-progress {
            stroke: rgb(var(--accent-color)) !important;
            transition: stroke-dashoffset 0.05s linear;
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
</style>
</head>
<body>

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

<div class="toast-container" id="toast-container"></div>

<!-- Unified Context Menu for Columns & Data Formatting -->
<div id="columnContextMenu" style="display:none; position:fixed; z-index:100000; background:rgba(15, 23, 42, 0.96); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); border:1px solid rgba(56, 189, 248, 0.35); border-radius:16px; box-shadow:0 25px 60px rgba(0,0,0,0.7), 0 0 30px rgba(56, 189, 248, 0.25); padding:16px 20px 20px 20px; width:510px; color:#f8fafc; font-family:'Tajawal', 'Inter', sans-serif; max-height:88vh; overflow:auto; resize:both; min-width:340px; min-height:200px; direction:rtl; text-align:right;">
    <!-- Draggable Header -->
    <div id="columnContextMenuHeader" style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.12); cursor:move; user-select:none; background:rgba(255,255,255,0.03); margin:-16px -20px 14px -20px; padding:12px 18px; border-top-left-radius:15px; border-top-right-radius:15px;">
        <div style="font-weight:900; font-size:1.05rem; color:#38bdf8; display:flex; align-items:center; gap:8px;">
            <span style="font-size:1.1rem; color:#94a3b8;" title="اضغط واسحب للتحريك في أي مكان">⠿</span>
            <span>⚙️</span> قائمة إعدادات الأعمدة وتخصيص البيانات
        </div>
        <div style="display:flex; align-items:center; gap:6px;">
            <button type="button" title="تصغير / استعادة" onclick="toggleMinimizeCtxMenu()" style="cursor:pointer; font-weight:bold; font-size:0.9rem; color:#cbd5e1; border:none; padding:3px 8px; border-radius:6px; background:rgba(255,255,255,0.08); transition:all 0.2s;" onmouseover="this.style.background='rgba(56,189,248,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.08)'">🗕</button>
            <button type="button" title="تكبير / استعادة" onclick="toggleMaximizeCtxMenu()" style="cursor:pointer; font-weight:bold; font-size:0.9rem; color:#cbd5e1; border:none; padding:3px 8px; border-radius:6px; background:rgba(255,255,255,0.08); transition:all 0.2s;" onmouseover="this.style.background='rgba(56,189,248,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.08)'">🗖</button>
            <button type="button" title="إغلاق" onclick="document.getElementById('columnContextMenu').style.display='none'" style="cursor:pointer; font-weight:bold; font-size:1.1rem; color:#ef4444; border:none; padding:2px 8px; border-radius:6px; background:rgba(239,68,68,0.15); transition:all 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.3)'" onmouseout="this.style.background='rgba(239,68,68,0.15)'">&times;</button>
        </div>
    </div>

    <!-- Menu Content Container (Hidden when minimized) -->
    <div id="columnContextMenuBody">
        <!-- Info Banner -->
        <div style="background:rgba(56, 189, 248, 0.1); border:1px solid rgba(56, 189, 248, 0.2); border-radius:10px; padding:10px 12px; margin-bottom:14px; font-size:0.8rem; color:#7dd3fc; line-height:1.5; display:flex; align-items:center; gap:8px;">
            <span style="font-size:1.1rem;">💡</span>
            <span>اسحب مقبض (<b>⣿</b>) لأعلى أو لأسفل لترتيب الأعمدة، وافتح ⚙️ لتخصيص خط ولون وكسور كل عمود بشكل مستقل!</span>
        </div>

        <!-- Dynamic Column Items with drag & per-column settings -->
        <div id="columnToggles" style="display:flex; flex-direction:column; gap:10px;"></div>

        <!-- Common Footer Buttons -->
        <div style="border-top:1px solid rgba(255,255,255,0.1); padding-top:15px; margin-top:16px; display:flex; gap:10px;">
            <button type="button" onclick="saveLayoutAndStyles()" style="flex:1.5; padding:11px; background:linear-gradient(135deg, #10b981, #059669); color:white; border:none; border-radius:8px; font-weight:bold; font-family:inherit; cursor:pointer; box-shadow:0 4px 14px rgba(16, 185, 129, 0.35); display:flex; align-items:center; justify-content:center; gap:6px; transition:all 0.2s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">
                💾 حفظ الترتيب والتخصيص
            </button>
            <button type="button" onclick="resetLayoutAndStyles()" style="flex:1; padding:11px; background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.3); color:#ef4444; border-radius:8px; font-weight:bold; font-family:inherit; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.25)'" onmouseout="this.style.background='rgba(239,68,68,0.15)'">
                🔄 إعادة الضبط
            </button>
        </div>
    </div>
</div>

<!-- Custom Context Menu for Partner Rows -->
<div id="partnerContextMenu" style="display:none; position:absolute; z-index:10000; background:rgba(15, 23, 42, 0.95); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.1); border-radius:12px; box-shadow:0 20px 40px rgba(0,0,0,0.5); padding:8px; width:220px; color:#f8fafc; font-family:'Tajawal', sans-serif;">
    <div id="partnerMenuHeader" style="font-weight:bold; font-size:0.85rem; color:#94a3b8; padding:8px 12px; border-bottom:1px solid rgba(255,255,255,0.08); margin-bottom:6px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">اسم الحساب</div>
    <div id="gotoLedgerItem" style="display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:8px; cursor:pointer; font-weight:bold; font-size:0.9rem; transition:all 0.2s;" onmouseover="this.style.background='rgba(56,189,248,0.15)'; this.style.color='#38bdf8';" onmouseout="this.style.background='transparent'; this.style.color='#f8fafc';">
        <span style="font-size:1.1rem;">📖</span>
        <span>الذهاب إلى دفتر الأستاذ</span>
    </div>
</div>


<div style="max-width: 1600px; margin: 0 auto; padding: 6px 14px; padding-bottom: 20px;">
    <!-- Premium Navigation Header -->
    <?php $header_theme_class = 'header-theme-reports'; ?>
    <?php include 'includes/header.php'; ?>

    <!-- FILTERS & PRESET CONTROLS PANEL (EXPANDED ~1CM) -->
    <div class="report-header no-print" style="background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(255,255,255,0.15); border-radius: 14px; padding: 18px 24px; margin-bottom: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.4);">
        <div class="filter-row" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); margin-bottom: 12px; gap: 16px;">
            <div class="filter-group">
                <label style="font-size:0.88rem; margin-bottom:6px; font-weight:800; color:#e2e8f0;">نوع المجموعة</label>
                <select class="filter-input" id="filter-group" style="padding:8px 16px; font-size:0.95rem; height:42px; border-radius:8px; font-weight:700;">
                    <option value="ALL">جميع الحسابات والأرصدة</option>
                    <option value="CUST">أرصدة العملاء</option>
                    <option value="SUPP">أرصدة الموردين</option>
                    <option value="BANK">حسابات البنوك</option>
                    <option value="SAFE">الخزن والصناديق</option>
                    <option value="EXP">المصروفات والحسابات العامة</option>
                </select>
            </div>
            <div class="filter-group">
                <label style="font-size:0.88rem; margin-bottom:6px; font-weight:800; color:#e2e8f0;">ترتيب الأرصدة</label>
                <select class="filter-input" id="filter-sort" style="padding:8px 16px; font-size:0.95rem; height:42px; border-radius:8px; font-weight:700;">
                    <option value="BALANCE_DESC">حسب الرصيد (من الأعلى للأقل)</option>
                    <option value="BALANCE_ASC">حسب الرصيد (من الأقل للأعلى)</option>
                    <option value="NAME">حسب اسم الحساب (أبجدياً)</option>
                    <option value="LAST_DATE">حسب تاريخ آخر حركة (الأحدث)</option>
                </select>
            </div>
            <div class="filter-group">
                <label style="font-size:0.88rem; margin-bottom:6px; font-weight:800; color:#e2e8f0;">مركز التكلفة</label>
                <select class="filter-input" id="filter-costcenter" style="padding:8px 16px; font-size:0.95rem; height:42px; border-radius:8px; font-weight:700;">
                    <option value="ALL">جميع مراكز التكلفة (الكل)</option>
                </select>
            </div>
        </div>
        <div style="display:flex; gap:12px; margin-top:10px; align-items:center; flex-wrap:wrap; width: 100%;">
            <button class="btn-run" onclick="runReport()" style="padding:8px 24px; font-size:0.95rem; height:40px; border-radius:8px; font-weight:900;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                تشغيل التقرير
            </button>

            <div style="height: 26px; width: 1px; background: rgba(255,255,255,0.2); margin: 0 6px;" class="no-print"></div>

            <div class="preset-slots-container no-print" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; height:40px; padding:4px 14px; border-radius:8px;">
                <span style="font-size:0.85rem; color:#cbd5e1; font-weight:bold; margin-left: 2px;">💾 التخطيطات:</span>
                <div id="balances-presets-wrap" style="display:flex; gap:8px; flex-wrap:wrap;">
                    <!-- Preset buttons generated by JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- HIGH CONTRAST COMPACT KPI DASHBOARD -->
    <div id="analytics-dashboard" class="no-print" style="display:none; margin-bottom: 6px; padding: 4px 8px; background: rgba(15, 23, 42, 0.65); border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
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

    <!-- Actions Panel -->
    <div class="actions-panel no-print" id="actions-panel" style="display:none; flex-wrap: wrap; margin-bottom: 6px; gap: 6px;">
        <button class="btn-action btn-secondary" onclick="window.print()" title="طباعة" style="padding:4px 10px; font-size:0.8rem;">🖨️ طباعة وتوثيق</button>
        <button class="btn-action btn-primary" onclick="exportPDF()" title="تصدير PDF" style="padding:4px 10px; font-size:0.8rem;">📄 تصدير PDF</button>
        <button class="btn-action" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; padding:4px 10px; font-size:0.8rem;" onclick="exportExcel()" title="تصدير إكسل">📊 إكسل (Excel)</button>
        <button class="btn-action" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: #fff; border: none; padding:4px 10px; font-size:0.8rem;" onclick="shareEmail()" title="مشاركة عبر الإيميل">📧 إيميل</button>
        <button class="btn-action" style="background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; border: none; padding:4px 10px; font-size:0.8rem;" onclick="shareWhatsApp()" title="مشاركة عبر واتساب">💬 واتساب</button>
    </div>

    <!-- TABLE -->
    <div id="print-container" style="display:none; padding: 6px; border-radius: 10px; background: rgba(255,255,255,0.02);">
        
        <div id="v-classic-ent">
            
            <div class="report-header" style="display: flex; justify-content: space-between; align-items: flex-start; min-height: 70px; border-bottom: 3px solid #111827; margin-bottom: 10px; padding-bottom: 8px; font-family: 'Tajawal', sans-serif;">
                
                <div style="flex: 1; text-align: right; color: #111827;">
                    <h2 style="margin: 0 0 5px 0; font-size: 1.6rem; font-weight: 900;">شركة الأفق للتجارة</h2>
                    <p style="margin: 0 0 3px 0; font-size: 0.95rem; font-weight: 600;">سجل تجاري: 1010101010</p>
                    <p style="margin: 0 0 3px 0; font-size: 0.95rem; font-weight: 600;">الرقم الضريبي: 300000000000003</p>
                    <p style="margin: 0 0 3px 0; font-size: 0.95rem; font-weight: 600;">الرياض، المملكة العربية السعودية</p>
                </div>

                <div style="flex: 1; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
                    <h2 style="margin: 0; padding: 10px 20px; background: #f8fafc; border-radius: 8px; border: 2px solid #111827; font-size: 1.4rem; color: #111827;">تقرير الأرصدة الإجمالية</h2>
                </div>

                <div style="flex: 1; text-align: left; display: flex; align-items: center; justify-content: flex-end; height: 100%;">
                    <div style="width: 4cm; height: 100%; border: 1px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; border-radius: 8px; background: #f8fafc; overflow: hidden; position: relative;" title="منطقة الشعار - يتم جلب الشعار من البيانات الأساسية">
                        <img id="company-logo" src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMDAgMTAwIj48Y2lyY2xlIGN4PSI1MCIgY3k9IjUwIiByPSI0MCIgZmlsbD0iI2UydThmMCIvPjx0ZXh0IHg9IjUwIiB5PSI1NSIgZm9udC1zaXplPSIyMCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY0NzQ4YiI+TE9HTzwvdGV4dD48L3N2Zz4=" alt="شعار الشركة" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                    </div>
                </div>
            </div>

            <table id="balances-table">
                <thead>
                    <tr id="balances-header-row">
                        <th data-orig-idx="0">اسم الحساب<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="1">رقم الهاتف<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="2">الرقم الضريبي<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="3">العملة<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="4">عليه عملة<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="5">له عملة<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="6">عليه<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="7">له<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="8">ملحوظة<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                    </tr>
                </thead>
                <tbody id="balances-tbody">
                    <tr>
                        <td colspan="11" class="empty-state">
                            <span style="font-size:3rem; display:block; margin-bottom:15px;">🔍</span>
                            <div>إضغط "تشغيل التقرير" لجلب جميع الأرصدة</div>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div class="audit-stamp">
                <span>✔️</span>
                <span>ENCRYPTED_DATA_LOCK: البيانات الواردة نهائية وتم استخراجها من السجلات المالية المشفرة للنظام.</span>
            </div>

            <!-- AI Financial Forecasting & Executive Decision Engine (مركز التنبؤ المالي والتوصيات بالذكاء الاصطناعي 🤖🔮) -->
            <div id="ai-forecasting-hub" class="ai-forecast-container" style="display: none; background: linear-gradient(135deg, rgba(168, 85, 247, 0.15), rgba(15, 23, 42, 0.95)); border: 1px solid rgba(168, 85, 247, 0.35); border-radius: 20px; padding: 25px; margin-top: 25px; margin-bottom: 25px; box-shadow: 0 20px 50px rgba(0,0,0,0.5), inset 0 1px 1px rgba(255,255,255,0.15); backdrop-filter: blur(16px); position: relative; overflow: hidden;">
                
                <!-- AI Ambient Glow Ring -->
                <div style="position: absolute; top: -50px; left: -50px; width: 180px; height: 180px; background: rgba(168, 85, 247, 0.25); filter: blur(50px); border-radius: 50%; pointer-events: none;"></div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, #a855f7, #6d28d9); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; box-shadow: 0 0 15px rgba(168,85,247,0.5);">🤖</div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.3rem; font-weight: 900; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
                                مركز التنبؤ المالي لاتخاذ القرارات بالذكاء الاصطناعي (AI Decision Engine)
                            </h3>
                            <span style="font-size: 0.78rem; color: #c084fc; font-weight: 700;">تنبؤات خوارزمية مستقبلية لـ 30 يوماً القادمة + توصيات تنفيذية آلية</span>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <span style="font-size: 0.75rem; background: rgba(168, 85, 247, 0.2); color: #e9d5ff; border: 1px solid rgba(168, 85, 247, 0.4); padding: 5px 12px; border-radius: 20px; font-weight: 800; display: flex; align-items: center; gap: 5px;">
                            <span>⚡ دقة التنبؤ:</span> <b id="ai-confidence-score">98.6%</b>
                        </span>
                        <button type="button" onclick="generateAIDeepAnalysis()" class="btn-run" style="background: linear-gradient(135deg, #a855f7, #7e22ce); color: #fff; font-size: 0.85rem; padding: 7px 16px; border-radius: 10px; border: none; font-weight: 800; cursor: pointer; box-shadow: 0 4px 15px rgba(168,85,247,0.35);">
                            ✨ استشارة الذكاء الاصطناعي
                        </button>
                    </div>
                </div>

                <!-- AI Executive Recommendation Banner -->
                <div style="background: rgba(30, 41, 59, 0.75); border: 1px solid rgba(168, 85, 247, 0.3); border-radius: 14px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
                    <span style="font-size: 2.2rem;">🔮</span>
                    <div style="flex: 1;">
                        <div style="font-size: 0.78rem; color: #a855f7; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px;">القرار التنفيذي الموصى به الآن:</div>
                        <div id="ai-executive-recommendation" style="font-size: 1.05rem; font-weight: 800; color: #ffffff; margin-top: 2px; line-height: 1.4;">
                            جاري استخراج القرار المالي الأمثل بواسطة خوارزميات الذكاء الاصطناعي...
                        </div>
                    </div>
                </div>

                <!-- AI Forecast 3-Grid Projection Cards -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 20px;">
                    <!-- Card 1: Projected 30-Day Liquidity -->
                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 14px; padding: 16px; text-align: right;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.8rem; color: #94a3b8; font-weight: 800;">💧 السيولة المتوقعة (نهاية الشهر)</span>
                            <span style="font-size: 1.1rem;">📈</span>
                        </div>
                        <div id="ai-projected-liquidity" style="font-size: 1.4rem; font-weight: 900; color: #38bdf8; font-family: 'JetBrains Mono', monospace;">0.00 SDG</div>
                        <div id="ai-projected-liquidity-sub" style="font-size: 0.75rem; color: #34d399; margin-top: 6px; font-weight: bold;">+0.0% نمو متوقع في المحفظة</div>
                    </div>

                    <!-- Card 2: Risk Bottleneck Forecast -->
                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(244, 63, 94, 0.25); border-radius: 14px; padding: 16px; text-align: right;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.8rem; color: #94a3b8; font-weight: 800;">⚠️ مخاطر التعثر والتأخير المتوقعة</span>
                            <span style="font-size: 1.1rem;">🛡️</span>
                        </div>
                        <div id="ai-risk-forecast" style="font-size: 1.4rem; font-weight: 900; color: #fb7185; font-family: 'JetBrains Mono', monospace;">منخفضة (Low Risk)</div>
                        <div id="ai-risk-forecast-sub" style="font-size: 0.75rem; color: #cbd5e1; margin-top: 6px;">لا توجد مؤشرات اختناق في التدفق النقدي</div>
                    </div>

                    <!-- Card 3: Optimal Opportunity -->
                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 14px; padding: 16px; text-align: right;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.8rem; color: #94a3b8; font-weight: 800;">💡 الفرصة المالية الذهبية المتاحة</span>
                            <span style="font-size: 1.1rem;">🚀</span>
                        </div>
                        <div id="ai-opportunity-title" style="font-size: 1.1rem; font-weight: 900; color: #34d399;">تحسين سرعة الدورة النقدية</div>
                        <div id="ai-opportunity-desc" style="font-size: 0.75rem; color: #94a3b8; margin-top: 6px;">تحصيل 3 ديون رئيسية يزيد السيولة بـ +25%</div>
                    </div>
                </div>

                <!-- AI 30-Day Forecast Trend ApexChart -->
                <div style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; padding: 18px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h4 style="margin: 0; color: #f8fafc; font-size: 1rem; font-weight: 900;">🔮 مسار التنبؤ المستقبلي لتدفق السيولة والأرصدة (30-Day Liquidity Forecast)</h4>
                        <span style="font-size: 0.75rem; color: #c084fc; font-weight: 700;">📊 نموذج إسقاط محاكاة AI</span>
                    </div>
                    <div id="chart-ai-liquidity-forecast" style="min-height: 300px;"></div>
                </div>
            </div>

            <!-- Visual Intelligence Analytics Hub (المركز البصري التحليلي والرسوم البيانية) -->
            <div id="visual-intelligence-hub" class="visual-hub-container" style="display: none;">
                
                <!-- Section Header -->
                <div class="hub-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 14px; height: 14px; border-radius: 50%; background: #38bdf8; box-shadow: 0 0 15px #38bdf8; animation: pulse 2s infinite;"></div>
                        <h3 style="margin: 0; font-size: 1.35rem; font-weight: 900; color: #f8fafc; letter-spacing: -0.5px;">
                            📈 التقرير البصري التحليلي للأرصدة وهيكل السيولة
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

                <!-- Visual Indicator Cards Row (أسهم ومؤشرات بصرية) -->
                <div class="visual-cards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-bottom: 30px;">
                    
                    <!-- Card 1: مؤشر الاتجاه والتغطية -->
                    <div class="v-card glow-sky">
                        <div class="v-card-header">
                            <span class="v-card-title">مؤشر صافي الاتجاه والسيولة</span>
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

                <!-- Charts Row 1: Donut & Hollow Donut Charts -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-bottom: 25px;">
                    
                    <!-- Chart 1: Hollow Donut Chart - Account Categories Distribution -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🍩 كعكة مجوفة: أين تتوزع أموالك وحساباتك؟</h4>
                                <span style="font-size: 0.72rem; background: rgba(56, 189, 248, 0.18); color: #38bdf8; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(56, 189, 248, 0.3);">💡 خريطة الثقل المالي</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: يوضح لك أي فئة تشغل الحجم الأكبر من أعمالك (العملاء أم الخزن أم الموردين)</span>
                        </div>
                        <div id="chart-hollow-donut-categories" style="min-height: 320px;"></div>
                    </div>

                    <!-- Chart 2: Hollow Donut Chart - Top Debtor Exposure -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🍩 كعكة مفرغة: أين تتركز أكبر الديون لك؟ (أضخم العملاء)</h4>
                                <span style="font-size: 0.72rem; background: rgba(168, 85, 247, 0.18); color: #c084fc; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(168, 85, 247, 0.3);">🎯 تركيز المستحقات</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: أكثر 5 حسابات مطالبة بالسداد لك، لمعرفة من يجب التواصل معه أولاً</span>
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
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">📈 المنحنى المتدرج للأرصدة (Balance Spectrum Curve)</h4>
                                <span style="font-size: 0.72rem; background: rgba(16, 185, 129, 0.18); color: #34d399; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(16, 185, 129, 0.3);">🌊 تدفق وتدرج مريح للعين</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: انسيابية تدفق المبالغ المالية من الأكبر إلى الأصغر بدون ضغوط بصرية</span>
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
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">📊 أعمدة المقارنة المزدوجة: (ما لك 🟢) ضد (ما عليك 🔴)</h4>
                                <span style="font-size: 0.72rem; background: rgba(245, 158, 11, 0.18); color: #fbbf24; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(245, 158, 11, 0.3);">⚖️ مقارنة الحصيلة</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: يظهر بوضوح ملخص حقوقك المالية مقارنةً بالتزاماتك في كل قطاع</span>
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
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: نسبة مئوية مباشرة تعكس أمان وضعك المالي وسرعة التحرّك</span>
                        </div>
                        <div id="chart-semicircle-gauge" style="min-height: 320px;"></div>
                    </div>

                </div>

                <!-- Charts Row 4: Top Creditors Hollow Donut & Horizontal Bar Ranking (جديد) -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-bottom: 25px;">
                    
                    <!-- Chart 6: Hollow Donut Chart - Top Creditor Suppliers -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🍩 كعكة مجوفة: أين تتركز التزامات الموردين؟ (أعلى 5 موردين دائنين)</h4>
                                <span style="font-size: 0.72rem; background: rgba(239, 68, 68, 0.18); color: #f87171; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(239, 68, 68, 0.3);">🔻 التزامات الموردين</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: يوضح الحسابات الأكثر استحقاقاً للأموال من الموردين لجدولة السداد</span>
                        </div>
                        <div id="chart-hollow-donut-top-creditors" style="min-height: 320px;"></div>
                    </div>

                    <!-- Chart 7: Horizontal Bar Chart - Top 10 Accounts Ranking -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">📊 أعمدة أفقية هرمية: ترتيب أضخم 10 حسابات</h4>
                                <span style="font-size: 0.72rem; background: rgba(6, 182, 212, 0.18); color: #22d3ee; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(6, 182, 212, 0.3);">👑 الترتيب الهرمي</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: شريط أفقي يُظهر أطول الأعمدة للحسابات الأكثر ثقلاً بالأرقام</span>
                        </div>
                        <div id="chart-horizontal-top-bar" style="min-height: 320px;"></div>
                    </div>

                </div>

                <!-- Charts Row 5: Filled Area Curve & Radar Equilibrium (جديد) -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-bottom: 25px;">
                    
                    <!-- Chart 8: Filled Area Fluid Curve -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🌊 منحنى المساحة المتدرجة الممتلئة (Fluid Area Curve)</h4>
                                <span style="font-size: 0.72rem; background: rgba(16, 185, 129, 0.18); color: #34d399; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(16, 185, 129, 0.3);">💧 تراكم السيولة</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: مساحة ملونة انسيابية مريحة للعين تعكس الحجم التراكمي للسيولة</span>
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
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: شبكة رادار خماسية تفاعلية توضح اتزان جميع الأنشطة بلمحة واحدة</span>
                        </div>
                        <div id="chart-radar-equilibrium" style="min-height: 320px;"></div>
                    </div>

                </div>

                <!-- Charts Row 6: Solid Pie & Multi-Radial Rings Gauge (جديد) -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px;">
                    
                    <!-- Chart 10: Solid Pie Chart - Active vs Zero Accounts -->
                    <div class="v-chart-card">
                        <div class="v-chart-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 900;">🥧 كعكة دائريّة مصمتة (Pie Chart): الحسابات النشطة مقابل الصفرية</h4>
                                <span style="font-size: 0.72rem; background: rgba(245, 158, 11, 0.18); color: #fbbf24; padding: 3px 10px; border-radius: 12px; font-weight: 800; border: 1px solid rgba(245, 158, 11, 0.3);">🥧 دائرة مصمتة</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: دائرة كاملة مكسوة بالألوان تظهر نسبة الحسابات ذات الأرصدة المباشرة</span>
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
                            <span style="font-size: 0.78rem; color: #94a3b8;">توضيح بسيط: ثلاث حلقات دائرية ملونة متداخلة تقارن حصة كل قطاع بنسبة مئوية واضحة</span>
                        </div>
                        <div id="chart-multi-radial-rings" style="min-height: 320px;"></div>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- Include html2pdf library to export PDF -->
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script defer src="js/localforage.min.js"></script>
<script defer src="js/app.js?v=1783054966.35444"></script>
<script defer src="js/balances-report.js?v=<?php echo time(); ?>"></script>
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
                    originalRunReport();
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
</body>
</html>

