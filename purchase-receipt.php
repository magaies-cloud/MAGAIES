<?php $page_title = 'استلام البضاعة (GRN)'; ?>
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استلام البضاعة (GRN) | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <style>
        :root {
            --bg-color: #050b14;
            --grn-primary: #38bdf8;
            --grn-hover: #0284c7;
            --grn-glow: rgba(56, 189, 248, 0.4);
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        :root.light-theme {
            --bg-color: #f1f5f9;
            --glass-border: rgba(0, 0, 0, 0.08);
        }
        :root.light-theme body {
            background-color: var(--bg-color);
            color: #000000;
        }
        :root.light-theme .neo-card,
        :root.light-theme .editor-left,
        :root.light-theme .info-card,
        :root.light-theme .recent-receipts-card,
        :root.light-theme div[style*="background: rgba(30, 41, 59, 0.4)"] {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            color: #000000 !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06) !important;
        }
        :root.light-theme .grn-meta-bar {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.12), rgba(99, 102, 241, 0.12)) !important;
            border: 1px solid rgba(56, 189, 248, 0.2) !important;
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.05) !important;
        }
        :root.light-theme .grn-meta-bar div {
            border-right-color: rgba(56, 189, 248, 0.2) !important;
        }
        :root.light-theme .grn-meta-bar span {
            color: #0f172a !important;
        }
        :root.light-theme .grn-meta-bar span[style*="color: #94a3b8"] {
            color: #0284c7 !important;
        }
        :root.light-theme #lbl-inv-no {
            color: #0369a1 !important;
        }
        :root.light-theme #lbl-creator {
            color: #6d28d9 !important;
        }
        :root.light-theme #recent-grns-container table.pos-grid tbody tr {
            background: #ffffff !important;
            color: #000000 !important;
        }
        :root.light-theme #recent-grns-container table.pos-grid tbody td {
            color: #000000 !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08) !important;
        }
        :root.light-theme #recent-grns-container table.pos-grid tbody td[style*="color: #38bdf8"] {
            color: #1d4ed8 !important;
        }
        :root.light-theme #recent-grns-container table.pos-grid tbody td[style*="color: #fbbf24"] {
            color: #b45309 !important;
        }
        :root.light-theme #recent-grns-container table.pos-grid tbody td[style*="color:#e2e8f0"] {
            color: #1e293b !important;
        }
        :root.light-theme #recent-grns-container table.pos-grid tbody td[style*="color:#a855f7"] {
            color: #6d28d9 !important;
        }
        :root.light-theme .neo-card-title,
        :root.light-theme .neo-card-title span,
        :root.light-theme h3,
        :root.light-theme .info-card h3,
        :root.light-theme .info-row,
        :root.light-theme .info-val,
        :root.light-theme span[style*="color: #fff"],
        :root.light-theme span[style*="color: #bae6fd"] {
            color: #000000 !important;
        }
        :root.light-theme .info-lbl {
            color: #475569 !important;
        }
        :root.light-theme .v-input,
        :root.light-theme .wh-select,
        :root.light-theme .qty-input {
            background: #ffffff !important;
            color: #000000 !important;
            border: 1px solid rgba(0, 0, 0, 0.15) !important;
        }
        :root.light-theme .wh-select option {
            background: #ffffff !important;
            color: #000000 !important;
        }
        :root.light-theme .pos-grid thead th {
            background: rgba(0, 0, 0, 0.04) !important;
            color: #000000 !important;
            border-bottom: 2px solid rgba(0, 0, 0, 0.08) !important;
        }
        :root.light-theme .pos-grid tbody tr {
            background: rgba(255, 255, 255, 0.95) !important;
            color: #000000 !important;
        }
        :root.light-theme .pos-grid tbody tr:hover {
            background: rgba(0, 0, 0, 0.04) !important;
        }
        :root.light-theme .pos-grid tbody td {
            color: #000000 !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
        }

        body {
            background-color: var(--bg-color);
            color: #f1f5f9;
            font-family: 'Tajawal', sans-serif;
            margin: 0; padding: 0;
            overflow-x: hidden;
            display: flex;
            height: 100vh;
        }

        body.has-sidebar { padding-right: 0 !important; }

        /* Ambient Animated Background for GRN (Blue/Cyan tones) */
        .ambient-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            z-index: -1; overflow: hidden; pointer-events: none;
            background: radial-gradient(circle at 50% 50%, #031020 0%, #000000 100%);
        }
        .orb {
            position: absolute; border-radius: 50%; filter: blur(120px);
            animation: floatOrb 20s infinite alternate ease-in-out; opacity: 0.15;
        }
        .orb-1 { width: 600px; height: 600px; background: #38bdf8; top: -10%; right: -10%; }
        .orb-2 { width: 500px; height: 500px; background: #0ea5e9; bottom: -10%; left: -10%; animation-delay: -5s; }
        .orb-3 { width: 400px; height: 400px; background: #0284c7; top: 40%; left: 40%; animation-delay: -10s; opacity: 0.1;}

        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, -50px) scale(1.1); }
        }

        .dash-main {
            flex: 1; display: flex; flex-direction: column; overflow: hidden;
        }

        /* Premium Header */
        @keyframes slideDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
        
        .premium-header {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.15), rgba(2, 132, 199, 0.15), rgba(14, 165, 233, 0.15));
            background-size: 300% 300%;
            backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15); padding: 12px 25px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            animation: slideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative; overflow: hidden; flex-shrink: 0;
            margin: 15px; border-radius: 20px;
        }

        .header-brand { display: flex; align-items: center; gap: 15px; }
        .brand-icon {
            width: 50px; height: 50px; border-radius: 16px;
            background: linear-gradient(135deg, #38bdf8, #0284c7);
            display: flex; align-items: center; justify-content: center; font-size: 1.8rem;
            box-shadow: 0 8px 20px rgba(56, 189, 248, 0.4), inset 0 2px 5px rgba(255,255,255,0.3);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .brand-text h1 { margin: 0; font-size: 1.4rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; text-shadow: 0 2px 10px rgba(255,255,255,0.1); }
        .brand-text p { margin: 0; font-size: 0.85rem; color: #bae6fd; font-weight: 500; }
        
        .header-controls { display: flex; align-items: center; gap: 15px; }
        .icon-btn { background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); width: 46px; height: 46px; border-radius: 14px; color: #fff; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; }
        .icon-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }

        /* Container Views */
        .view-container {
            display: none; flex-direction: column; height: 100%; flex: 1; padding: 0 15px 15px 15px; overflow-y: auto;
            animation: fadeIn 0.5s ease backwards;
        }
        .view-container.active { display: flex; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* Hub View (Invoice Selection) */
        .neo-card {
            background: rgba(15, 23, 42, 0.65); border: 1px solid var(--glass-border); border-radius: 20px;
            padding: 20px; display: flex; flex-direction: column; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3); flex: 1;
        }
        .neo-card-title { font-size: 1.2rem; font-weight: 800; color: #fff; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }

        .v-input {
            width: 100%; padding: 10px 15px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px; color: #fff; font-size: 0.9rem; outline: none; transition: 0.3s; font-family: 'Outfit', 'Tajawal', sans-serif;
        }
        .v-input:focus { border-color: var(--grn-primary); box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15); }

        /* Data Grid */
        .grid-scroll-wrap { flex: 1; overflow-y: auto; overflow-x: auto; padding-right: 5px; }
        .grid-scroll-wrap::-webkit-scrollbar { width: 6px; height: 6px; }
        .grid-scroll-wrap::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 6px; }
        
        .pos-grid { width: 100%; border-collapse: separate; border-spacing: 0 8px; min-width: 900px; }
        .pos-grid thead th {
            background: rgba(255,255,255,0.02); color: #94a3b8; font-size: 0.85rem; font-weight: 800;
            padding: 12px 15px; text-align: center; position: sticky; top: 0; z-index: 5;
            border-bottom: 2px solid rgba(255,255,255,0.05); text-transform: uppercase; letter-spacing: 0.5px;
        }
        .pos-grid tbody tr {
            background: rgba(0,0,0,0.2); transition: all 0.3s ease;
        }
        .pos-grid tbody tr:hover {
            background: rgba(255,255,255,0.05); transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .pos-grid tbody td {
            padding: 15px 12px; border-bottom: 1px solid rgba(255,255,255,0.02); border-top: 1px solid rgba(255,255,255,0.02);
            font-size: 0.9rem; vertical-align: middle;
        }
        .pos-grid tbody td:first-child { border-radius: 12px 0 0 12px; border-right: 1px solid rgba(255,255,255,0.02); }
        .pos-grid tbody td:last-child { border-radius: 0 12px 12px 0; border-left: 1px solid rgba(255,255,255,0.02); }

        .btn-action {
            background: linear-gradient(135deg, var(--grn-primary), var(--grn-hover)); color: #fff;
            border: none; padding: 8px 16px; border-radius: 8px; font-weight: 800; cursor: pointer;
            transition: 0.3s; box-shadow: 0 4px 15px var(--grn-glow); font-family: 'Tajawal', sans-serif;
        }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 6px 20px var(--grn-glow); }
        .btn-cancel { background: rgba(255,255,255,0.05); box-shadow: none; border: 1px solid rgba(255,255,255,0.1); color: #fff; }
        .btn-cancel:hover { background: rgba(255,255,255,0.1); box-shadow: 0 4px 10px rgba(0,0,0,0.3); }

        .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-dim); padding: 40px; }
        .empty-state .icon { font-size: 4rem; margin-bottom: 15px; opacity: 0.5; filter: drop-shadow(0 0 10px rgba(56, 189, 248, 0.3)); }

        /* Editor View */
        .editor-layout { display: flex; gap: 20px; height: 100%; }
        .editor-left { flex: 1; display: flex; flex-direction: column; background: rgba(15, 23, 42, 0.65); border: 1px solid var(--glass-border); border-radius: 20px; padding: 20px; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); overflow: hidden; min-height: 0; }
        .editor-right { width: 350px; display: flex; flex-direction: column; gap: 20px; }
        
        .info-card { background: rgba(0,0,0,0.4); border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); padding: 20px; }
        .info-card h3 { margin: 0 0 15px 0; color: #fff; font-size: 1.1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem; }
        .info-lbl { color: #94a3b8; }
        .info-val { color: #fff; font-weight: 800; font-family: 'Outfit', sans-serif; direction: ltr; }
        
        .qty-input {
            width: 80px; text-align: center; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.5);
            color: #fff; padding: 8px; border-radius: 8px; font-weight: 800; font-family: 'Outfit', sans-serif;
        }
        .qty-input:focus { border-color: #38bdf8; box-shadow: 0 0 10px rgba(56, 189, 248, 0.3); }

        .wh-select {
            width: 100%; padding: 12px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.2);
            border-radius: 10px; color: #fff; font-size: 0.95rem; font-family: 'Tajawal', sans-serif; margin-bottom: 15px;
        }
        .wh-select:focus { border-color: var(--grn-primary); outline: none; }
        .wh-select option { background: #0f172a; color: #fff; }

    
        
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
\n</style>
</head>
<body>

    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>
<style>
/* Custom Switch from User Uiverse */
.uiverse-switch {
  position: relative;
  display: inline-block;
  margin: 5px 15px;
}

.uiverse-switch input {
  top: 0; right: 0; bottom: 0; left: 0; opacity: 0; z-index: 100; position: absolute; width: 100%; height: 100%; cursor: pointer;
}

.uiverse-switch.demo3 {
  width: 210px;
  height: 42px;
}

.uiverse-switch.demo3 label {
  display: block; width: 100%; height: 100%; background: #d8ba5f; border-radius: 50px;
  box-shadow: inset 0 4px 8px 2px rgba(248, 6, 6, 0.2), 0 1px 0 rgba(255, 255, 255, 0.5);
  transition: all 0.4s ease;
}

.uiverse-switch.demo3 label:after {
  content: ""; position: absolute; z-index: -1; top: -6px; right: -6px; bottom: -6px; left: -6px;
  border-radius: inherit; background: #ababab; background: linear-gradient(#f2f2f2, #ababab);
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.3), 0 1px 1px rgba(0, 0, 0, 0.25);
}

.uiverse-switch.demo3 label:before {
  content: ""; position: absolute; z-index: -1; top: -12px; right: -12px; bottom: -12px; left: -12px;
  border-radius: inherit; background: #eee; background: linear-gradient(#e5e7e6, #eee);
  box-shadow: 0 1px 0 rgba(255, 255, 255, 0.5); filter: blur(1px);
}

.uiverse-switch.demo3 label i {
  display: block; height: 100%; width: 45%; border-radius: inherit; background: silver;
  position: absolute; z-index: 2; right: 55%; top: 0; background: linear-gradient(#f7f2f6, #b2ac9e);
  box-shadow: inset 0 1px 0 white, 0 0 8px rgba(0, 0, 0, 0.3), 0 5px 5px rgba(0, 0, 0, 0.2);
  transition: all 0.4s ease;
}

.uiverse-switch.demo3 label i:after {
  content: ""; position: absolute; left: 15%; top: 25%; width: 70%; height: 50%;
  background: #d2cbc3; background: linear-gradient(#cbc7bc, #d2cbc3); border-radius: inherit;
}

.uiverse-switch.demo3 label i:before {
  content: "قيد الاستلام"; text-transform: uppercase; font-style: normal; font-weight: bold;
  color: rgba(0, 0, 0, 0.6); text-shadow: 0 1px 0 #bcb8ae, 0 -1px 0 #97958e;
  font-family: 'Tajawal', Helvetica, Arial, sans-serif; font-size: 15px; position: absolute;
  top: 50%; margin-top: -11px; right: -115%; transition: all 0.4s ease;
}

.uiverse-switch.demo3 input:checked ~ label {
  background: #9abb82;
}

.uiverse-switch.demo3 input:checked ~ label i {
  right: -1%;
}

.uiverse-switch.demo3 input:checked ~ label i:before {
  content: "مستلم"; right: 135%; color: #82a06a; text-shadow: 0 1px 0 #afcb9b, 0 -1px 0 #111311;
}

@keyframes popInBouncy {
    0% { transform: translate(-50%, -50%) scale(0.3); opacity: 0; }
    50% { transform: translate(-50%, -50%) scale(1.15); opacity: 1; }
    100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
}
@keyframes popInBouncyRelative {
    0% { transform: scale(0.3); opacity: 0; }
    50% { transform: scale(1.15); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}
.counter-animate-abs {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    animation: popInBouncy 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    z-index: 10;
}
@media (max-width: 1100px) {
    .counter-animate-abs {
        position: relative;
        left: auto;
        top: auto;
        transform: none;
        animation: popInBouncyRelative 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }
}
</style>

    <div class="dash-main">
        <?php $header_theme_class = 'header-theme-purchase'; ?>
        <?php include 'includes/header.php'; ?>

        <!-- View 1: Hub (Select Invoice with Pending Goods) -->
        <div class="view-container active" id="view-hub">

            <div class="neo-card">
                <div class="neo-card-title" style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: space-between; align-items: center; position: relative;">
                    <div style="display: flex; gap: 15px; align-items: center; z-index: 2;">
                        <span id="hub-title-text">⏳ فواتير تحتوي على بضاعة قيد الاستلام</span>
                        <div class="uiverse-switch demo3">
                            <input type="checkbox" id="uiverse-toggle" onchange="toggleHubViewBySwitch(this.checked)">
                            <label><i></i></label>
                        </div>
                    </div>
                    
                    <div id="hub-counter-badge"></div>
                    
                    <input type="text" id="search-inv" class="v-input" placeholder="ابحث برقم الفاتورة أو المورد..." style="width: 300px; z-index: 2;" oninput="renderPendingInvoices()">
                </div>
                
                <div id="hub-pending-container">
                    <div class="grid-scroll-wrap">
                        <table class="pos-grid">
                            <thead>
                                <tr>
                                    <th style="text-align:right;">رقم الفاتورة الأصلي</th>
                                    <th style="text-align:right;">التاريخ</th>
                                    <th style="text-align:right;">المورد</th>
                                    <th style="text-align:center;">إجمالي الأصناف المعلقة</th>
                                    <th style="text-align:center;">إجراء</th>
                                </tr>
                            </thead>
                            <tbody id="invoices-tbody">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                        <div class="empty-state" id="empty-hub" style="display: none;">
                            <div class="icon">✨</div>
                            <h2>لا توجد بضاعة قيد الاستلام</h2>
                            <p>جميع مشترياتك تم استلامها بالكامل وتم إدخالها في المخازن الفعلية.</p>
                        </div>
                    </div>
                </div>

                <div id="hub-received-container" style="display: none;">
                    <div class="grid-scroll-wrap" style="padding-top: 15px;">
                        <div id="recent-grns-container" style="overflow-x: auto;">
                            <!-- Populated dynamically by JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View 2: Editor (Receive Items) -->
        <div class="view-container" id="view-editor" style="gap: 20px; animation: fadeIn 0.4s ease-out;">
            <!-- Header Metadata Bar -->
            <div class="grn-meta-bar" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; background: rgba(30, 41, 59, 0.4); border: 1px solid rgba(255,255,255,0.05); padding: 15px 25px; border-radius: 16px; backdrop-filter: blur(10px);">
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <span style="color: #94a3b8; font-size: 0.8rem; font-weight: 600;">رقم الفاتورة</span>
                    <span id="lbl-inv-no" style="color: #38bdf8; font-size: 1.2rem; font-weight: 800; font-family: 'Outfit';"></span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px; border-right: 1px solid rgba(255,255,255,0.05); padding-right: 15px;">
                    <span style="color: #94a3b8; font-size: 0.8rem; font-weight: 600;">المورد</span>
                    <span id="lbl-supplier" style="color: #fff; font-size: 1.1rem; font-weight: 700;">-</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px; border-right: 1px solid rgba(255,255,255,0.05); padding-right: 15px;">
                    <span style="color: #94a3b8; font-size: 0.8rem; font-weight: 600;">تاريخ ووقت الفاتورة</span>
                    <span id="lbl-date" style="color: #fff; font-size: 1.1rem; font-weight: 700; font-family: 'Outfit';">-</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px; border-right: 1px solid rgba(255,255,255,0.05); padding-right: 15px;">
                    <span style="color: #94a3b8; font-size: 0.8rem; font-weight: 600;">منشئ الفاتورة</span>
                    <span id="lbl-creator" style="color: #a855f7; font-size: 1.1rem; font-weight: 700;">-</span>
                </div>
            </div>

            <div class="editor-layout" style="display: flex; gap: 20px; flex: 1; align-items: stretch; min-height: 0;">
                <!-- Left: Items Table -->
                <div class="editor-left" style="flex: 1; display: flex; flex-direction: column;">
                    <div class="neo-card-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span>📦 قائمة الأصناف المعلقة للفاتورة</span>
                        <button class="btn-action btn-cancel" style="padding: 6px 12px; font-size: 0.8rem;" onclick="showHub()">إلغاء وتراجع</button>
                    </div>
                    
                    <div class="grid-scroll-wrap" style="flex:1; max-height: 420px; min-height: 220px; overflow-y: auto; border-radius: 12px; border: 1px solid rgba(255,255,255,0.06); background: rgba(15, 23, 42, 0.4);">
                        <table class="pos-grid" style="width: 100%;">
                            <thead style="position: sticky; top: 0; z-index: 10; background: #0f172a;">
                                <tr>
                                    <th style="text-align:right; width:20%;">الصنف</th>
                                    <th style="text-align:center; width:8%;">الوحدة</th>
                                    <th style="text-align:center; width:12%;">سعر الوحدة</th>
                                    <th style="text-align:center; width:10%;">الكمية المعلقة</th>
                                    <th style="text-align:center; width:150px; color:#38bdf8;">الكمية المستلمة (الآن)</th>
                                    <th style="text-align:center; width:10%; color:#f59e0b;">المتبقي بعد الاستلام</th>
                                    <th style="text-align:center; width:12%;">القيمة المستلمة</th>
                                    <th style="text-align:center; width:160px;">المستودع المُستلِم</th>
                                    <th style="text-align:center; width:60px;">تقسيم</th>
                                </tr>
                            </thead>
                            <tbody id="items-tbody">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right: Totals & Save -->
                <div class="editor-right" style="width: 320px; display: flex; flex-direction: column; gap: 20px; flex-shrink: 0;">
                    <!-- Hidden reference for default warehouse to avoid breaking JS -->
                    <select id="actual-warehouse" style="display:none;"></select>

                    <!-- Summary Card (Moved to Top) -->
                    <div class="info-card" style="background: rgba(30, 41, 59, 0.4); border: 1px solid rgba(255,255,255,0.05); padding: 20px; border-radius: 16px; display: flex; flex-direction: column;">
                        <h3 style="margin-top: 0; color: #fff; font-size: 0.95rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                            <span>📊</span> ملخص عملية الاستلام
                        </h3>
                        <div style="display: flex; flex-direction: column; gap: 15px;">
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                                <span style="color: #94a3b8; font-size: 0.85rem;">أصناف مستلمة:</span>
                                <span id="lbl-total-received-items" style="color: #10b981; font-weight: 800; font-family: 'Outfit';">0 أصناف</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                                <span style="color: #94a3b8; font-size: 0.85rem;">كمية مستلمة:</span>
                                <span id="lbl-total-received-qty" style="color: #10b981; font-weight: 800; font-family: 'Outfit';">0</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                                <span style="color: #94a3b8; font-size: 0.85rem;">القيمة المستلمة:</span>
                                <span id="lbl-total-received-val" style="color: var(--gold-primary); font-weight: 800; font-family: 'Outfit';">0 SDG</span>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <button class="btn-action" style="padding: 15px; font-size: 1.1rem; display:flex; align-items:center; justify-content:center; gap:10px; border-radius:12px; background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 15px rgba(16,185,129,0.25);" onclick="saveReceiptNote()">
                            <span>💾</span> حفظ مذكرة الاستلام (GRN)
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Operations moved to Hub View -->
        </div>
        </div>
    </div>

    <!-- Notification Toast Container -->
    <div class="toast-container" id="toast-container" style="position:fixed; bottom:20px; left:20px; z-index:99999; display:flex; flex-direction:column; gap:10px;"></div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
    <script>
        let allPurchases = [];
        let itemsDb = [];
        let suppliersDb = [];
        let warehousesDb = [];
        let usersDb = [];
        let pendingMap = {}; // originalInvoiceId -> array of pending items
        let currentInvoiceId = null;
        let receiptLines = [];
        let editingGRNId = null; // Currently edited GRN ID

        // Formatting utilities
        function fmtNum(n) { return Number(n).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2}); }
        function parseNum(str) { if(!str) return 0; return parseFloat(String(str).replace(/,/g, '')) || 0; }
        function showToast(msg, type='info') {
            const container = document.getElementById('toast-container');
            const t = document.createElement('div');
            t.style.padding = '12px 20px'; t.style.borderRadius = '8px'; t.style.color = '#fff'; t.style.fontSize = '0.9rem'; t.style.fontWeight = '700'; t.style.boxShadow = '0 5px 15px rgba(0,0,0,0.4)'; t.style.animation = 'slideDown 0.3s ease forwards';
            if(type === 'error') t.style.background = 'rgba(239, 68, 68, 0.9)';
            else if(type === 'success') t.style.background = 'rgba(16, 185, 129, 0.9)';
            else t.style.background = 'rgba(56, 189, 248, 0.9)';
            t.textContent = msg;
            container.appendChild(t);
            setTimeout(() => { t.style.opacity = '0'; setTimeout(()=>t.remove(), 300); }, 3000);
        }

        let currentHubView = 'pending';
        function toggleHubViewBySwitch(isReceivedView) {
            if (isReceivedView) {
                currentHubView = 'received';
                document.getElementById('hub-pending-container').style.display = 'none';
                document.getElementById('hub-received-container').style.display = 'block';
                renderRecentGRNs();
            } else {
                currentHubView = 'pending';
                document.getElementById('hub-pending-container').style.display = 'block';
                document.getElementById('hub-received-container').style.display = 'none';
                renderPendingInvoices();
            }
        }


        function initData() {
            function loadKey(key) {
                let raw = localStorage.getItem(key);
                if (typeof raw === 'string') {
                    try { return JSON.parse(raw) || []; } catch(e) { return []; }
                } else if (raw && typeof raw === 'object') {
                    return raw;
                }
                return [];
            }
            allPurchases = loadKey('erp_purchase_invoices_v1');
            itemsDb = loadKey('erp_items_v1');
            suppliersDb = loadKey('erp_suppliers_v1');
            warehousesDb = loadKey('erp_warehouses_v1');
            usersDb = loadKey('erp_users_v1');
            if(warehousesDb.length === 0) warehousesDb = [{ id:'w-main', name:'المخزن الرئيسي' }];

            populateWarehouseSelect();
            calculatePendingGoods();
            renderPendingInvoices();
        }

        function getUserName(id) {
            if (!id || id === 'unknown') return 'مدير النظام';
            const u = usersDb.find(x => x.id === id || String(x.id) === String(id));
            return u ? u.name : id;
        }

        if (window.APP_READY) {
            initData();
            if (typeof window.pullFromCloud === 'function') {
                window.pullFromCloud(true);
            }
        } else {
            document.addEventListener('DBReady', () => {
                initData();
                if (typeof window.pullFromCloud === 'function') {
                    window.pullFromCloud(true);
                }
            });
        }
        window.addEventListener('cloudDataRestored', initData);
        window.addEventListener('strictDataReady', initData);

        function populateWarehouseSelect() {
            const select = document.getElementById('actual-warehouse');
            select.innerHTML = '';
            // Only actual warehouses
            warehousesDb.forEach(wh => {
                if (wh.id !== 'PENDING_RECEIPT') {
                    const opt = document.createElement('option');
                    opt.value = wh.id;
                    opt.text = wh.name;
                    select.appendChild(opt);
                }
            });
        }

        // Extremely critical: calculate actual pending balances
        // For each invoice, sum quantities where warehouseId == 'PENDING_RECEIPT'.
        // Original invoices add positive pending qty. Returns subtract pending qty. GRNs subtract pending qty.
        function calculatePendingGoods() {
            pendingMap = {}; // target: { invId: { supplierId, date, lines: { itemId: { unitId, qty, originalLine } } } }

            console.log("GRN Debug - allPurchases loaded:", allPurchases);

            // Base tracking by invoice
            allPurchases.filter(inv => !inv.deleted && inv.status !== 'draft' && inv.status !== 'proforma' && inv.id !== editingGRNId).forEach(inv => {
                const rootInvId = inv.isReturn || inv.isGRN ? inv.originalInvoiceId : (inv.invoiceNo || inv.id);
                if (!rootInvId) {
                    console.log("GRN Debug - Skipping invoice because rootInvId is falsy:", inv);
                    return;
                }

                if (!pendingMap[rootInvId]) {
                    pendingMap[rootInvId] = {
                        supplierId: inv.customer || inv.customerId || inv.supplierId,
                        date: inv.date,
                        createdBy: inv.createdBy || 'unknown',
                        lines: {}
                    };
                } else if (inv.createdBy && pendingMap[rootInvId].createdBy === 'unknown') {
                    pendingMap[rootInvId].createdBy = inv.createdBy;
                }

                const items = inv.items || inv.lines || [];
                const globalWh = inv.warehouse;

                console.log(`GRN Debug - Processing invoice ${rootInvId}, global warehouse: ${globalWh}`);

                items.forEach(line => {
                    const lineWh = line.warehouseId || globalWh;
                    
                    const isPending = (lineWh === 'PENDING_RECEIPT' || !lineWh || lineWh === '⏳ بضاعة قيد الاستلام (لم تحدد بعد)' || lineWh === '⏳ بضاعة قيد الاستلام');
                    console.log(`GRN Debug - Item: ${line.itemId}, Line warehouse: ${lineWh}, isPending: ${isPending}`);

                    if (isPending) {
                        const lineKey = line.itemId + '|' + (line.unitId || line.selectedUnit || '');
                        if (!pendingMap[rootInvId].lines[lineKey]) {
                            pendingMap[rootInvId].lines[lineKey] = {
                                itemId: line.itemId,
                                unitId: line.unitId || line.selectedUnit || '',
                                unitName: line.unitName || line.selectedUnit || 'حبة',
                                price: parseFloat(line.price) || 0, // needed if we return or value it, though GRN takes 0 value
                                pendingQty: 0
                            };
                        }
                        
                        // If it's the original invoice, quantity is positive (adding to pending).
                        // If it's a GRN, the GRN creates a negative line for PENDING_RECEIPT (subtracting from pending).
                        // If it's a Return, the return creates a negative line for PENDING_RECEIPT (subtracting from pending).
                        // So we just add the quantity!
                        pendingMap[rootInvId].lines[lineKey].pendingQty += (parseFloat(line.quantity) || 0);
                    }
                });
            });

            // Cleanup: remove items with <= 0 pending qty, and invoices with no pending items
            console.log("GRN Debug - pendingMap before cleanup:", JSON.parse(JSON.stringify(pendingMap)));

            Object.keys(pendingMap).forEach(invId => {
                const lines = pendingMap[invId].lines;
                Object.keys(lines).forEach(lk => {
                    // Precision fix
                    lines[lk].pendingQty = Math.round(lines[lk].pendingQty * 100) / 100;
                    if (lines[lk].pendingQty <= 0) {
                        delete lines[lk];
                    }
                });
                if (Object.keys(lines).length === 0) {
                    delete pendingMap[invId];
                }
            });

            console.log("GRN Debug - Final pendingMap:", pendingMap);
        }

        function getSupplierName(id) {
            if(!id) return 'مورد نقدي';
            const sup = suppliersDb.find(s => s.id === id);
            return sup ? sup.name : 'مورد نقدي';
        }

        function getItemName(id) {
            const it = itemsDb.find(i => i.id === id);
            return it ? it.name : 'صنف غير معروف';
        }

        function renderPendingInvoices() {
            const tbody = document.getElementById('invoices-tbody');
            const search = document.getElementById('search-inv').value.toLowerCase();
            tbody.innerHTML = '';
            
            const invIds = Object.keys(pendingMap);

            const filteredIds = invIds.filter(id => {
                if (search) {
                    const supName = getSupplierName(pendingMap[id].supplierId).toLowerCase();
                    return id.toLowerCase().includes(search) || supName.includes(search);
                }
                return true;
            });

            if (filteredIds.length === 0) {
                document.getElementById('empty-hub').style.display = 'flex';
            } else {
                document.getElementById('empty-hub').style.display = 'none';
                
                // Sort by date desc (safely)
                filteredIds.sort((a,b) => {
                    let tA = new Date(pendingMap[a].date).getTime();
                    let tB = new Date(pendingMap[b].date).getTime();
                    if(isNaN(tA)) tA = 0;
                    if(isNaN(tB)) tB = 0;
                    return tB - tA;
                });
                
                filteredIds.forEach(id => {
                    const invData = pendingMap[id];
                    const supName = getSupplierName(invData.supplierId);
                    
                    const totalPendingItems = Object.values(invData.lines).length;

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td style="text-align:right; font-family:'Outfit'; color:#38bdf8; font-weight:700;">${id}</td>
                        <td style="text-align:right; font-family:'Outfit';">${new Date(invData.date).toLocaleDateString('en-GB')}</td>
                        <td style="text-align:right; font-weight:700;">${supName}</td>
                        <td style="text-align:center; font-family:'Outfit'; font-weight:800;">${totalPendingItems} صنف</td>
                        <td style="text-align:center;">
                            <button class="btn-action" style="padding:6px 15px; font-size:0.8rem;" onclick="openReceiptEditor('${id}')">استلام البضاعة</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
            
            if (currentHubView === 'pending') {
                document.getElementById('hub-title-text').innerHTML = `⏳ فواتير تحتوي على بضاعة قيد الاستلام`;
                const counterContainer = document.getElementById('hub-counter-badge');
                counterContainer.innerHTML = '';
                setTimeout(() => {
                    counterContainer.innerHTML = `<span class="counter-animate-abs" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; padding: 6px 30px; border-radius: 40px; font-size: 1.6rem; font-weight: 900; box-shadow: 0 10px 25px rgba(245, 158, 11, 0.5), inset 0 2px 5px rgba(255,255,255,0.3); border: 2px solid rgba(255,255,255,0.2); font-family: 'Outfit', 'Tajawal', sans-serif; white-space: nowrap; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">${filteredIds.length} فاتورة</span>`;
                }, 10);
            }
        }

        function showHub() {
            document.getElementById('view-editor').classList.remove('active');
            document.getElementById('view-hub').classList.add('active');
            currentInvoiceId = null;
            receiptLines = [];
            editingGRNId = null; // Clear edit mode
            calculatePendingGoods();
            renderPendingInvoices();
        }

        function openReceiptEditor(invId) {
            currentInvoiceId = invId;
            const invData = pendingMap[invId];
            if (!invData) return showToast('خطأ في البيانات', 'error');

            document.getElementById('lbl-inv-no').textContent = invId;
            document.getElementById('lbl-supplier').textContent = getSupplierName(invData.supplierId);
            document.getElementById('lbl-date').textContent = new Date(invData.date).toLocaleString('ar-EG-u-nu-latn');
            document.getElementById('lbl-creator').textContent = getUserName(invData.createdBy);

            const defaultWh = document.getElementById('actual-warehouse').value || (warehousesDb.find(x => x.id !== 'PENDING_RECEIPT') || {}).id || 'w-main';

            receiptLines = Object.values(invData.lines).map((line, index) => {
                return {
                    id: 'L' + index,
                    itemId: line.itemId,
                    unitId: line.unitId,
                    unitName: line.unitName,
                    price: line.price || 0,
                    maxPending: line.pendingQty,
                    receiveQty: line.pendingQty, // Default to fully receiving what's pending
                    warehouseId: defaultWh,
                    lineGroup: line.itemId + '|' + (line.unitId || ''),
                    isSplit: false
                };
            });

            document.getElementById('view-hub').classList.remove('active');
            document.getElementById('view-editor').classList.add('active');
            renderReceiptLines();
            renderRecentGRNs(invId);
        }

        function renderReceiptLines() {
            const scrollWrap = document.querySelector('#view-editor .grid-scroll-wrap');
            const savedScrollTop = scrollWrap ? scrollWrap.scrollTop : 0;

            const tbody = document.getElementById('items-tbody');
            tbody.innerHTML = '';

            receiptLines.forEach(line => {
                const tr = document.createElement('tr');
                const lineTotal = (line.price || 0) * (line.receiveQty || 0);
                
                // Build warehouse options
                let whOptions = '';
                warehousesDb.forEach(wh => {
                    if (wh.id !== 'PENDING_RECEIPT') {
                        const selected = wh.id === line.warehouseId ? 'selected' : '';
                        whOptions += `<option value="${wh.id}" ${selected}>${wh.name}</option>`;
                    }
                });

                // Calculate remaining quantity
                const otherSplits = receiptLines.filter(x => x.lineGroup === line.lineGroup && x.id !== line.id);
                const totalAssigned = otherSplits.reduce((acc, x) => acc + (x.receiveQty || 0), 0) + (line.receiveQty || 0);
                const remainingQty = Math.round((line.maxPending - totalAssigned) * 100) / 100;
                
                // Styling for remaining
                let remainingColor = '#10b981'; // Green (Fully Received)
                if (remainingQty > 0) remainingColor = '#f59e0b'; // Orange (Partially Received)
                else if (remainingQty < 0) remainingColor = '#ef4444'; // Red (Error)

                tr.innerHTML = `
                    <td style="text-align:right; font-weight:700; ${line.isSplit ? 'padding-right: 25px; color:#94a3b8;' : ''}">
                        ${line.isSplit ? '↳ ' : ''}${getItemName(line.itemId)}
                    </td>
                    <td style="text-align:center; color:#94a3b8;">${line.unitName}</td>
                    <td style="text-align:center; font-family:'Outfit';">${fmtNum(line.price)} SDG</td>
                    <td style="text-align:center; font-family:'Outfit'; font-weight:800;">${line.isSplit ? '—' : line.maxPending}</td>
                    <td style="text-align:center;">
                        <div style="display: inline-flex; align-items: center; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; overflow: hidden; padding: 2px;">
                            <button type="button" onclick="changeQtyStep('${line.id}', -1)" style="background: none; border: none; color: #ef4444; width: 26px; height: 26px; font-weight: 800; cursor: pointer; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; transition: background 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.1)'" onmouseout="this.style.background='none'">-</button>
                            <input type="text" class="qty-input" value="${line.receiveQty || 0}" style="width: 50px; border: none; background: none; text-align: center; color: #fff; font-weight: 800; font-family: 'Outfit'; font-size: 0.95rem; padding: 0; outline: none;" onfocus="this.select()" onchange="updateReceiveQty('${line.id}', this.value)">
                            <button type="button" onclick="changeQtyStep('${line.id}', 1)" style="background: none; border: none; color: #10b981; width: 26px; height: 26px; font-weight: 800; cursor: pointer; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; transition: background 0.2s;" onmouseover="this.style.background='rgba(16,185,129,0.1)'" onmouseout="this.style.background='none'">+</button>
                        </div>
                    </td>
                    <td style="text-align:center; font-family:'Outfit'; font-weight:800; color:${remainingColor};">${line.isSplit ? '—' : remainingQty}</td>
                    <td style="text-align:center; font-family:'Outfit'; font-weight:700; color:var(--gold-primary);">${fmtNum(lineTotal)} SDG</td>
                    <td style="text-align:center;">
                        <select class="wh-select" style="padding: 4px 8px; border-radius: 6px; font-size: 0.85rem; width: 100%;" 
                                onchange="updateLineWarehouse('${line.id}', this.value)">
                            ${whOptions}
                        </select>
                    </td>
                    <td style="text-align:center; display: flex; gap: 4px; justify-content: center; align-items: center; border: none; padding-top: 12px;">
                        <button type="button" class="btn-action" style="padding: 4px 8px; font-size: 0.8rem; background: rgba(56,189,248,0.2); border-color: rgba(56,189,248,0.3); color: #38bdf8;" 
                                onclick="splitLine(event, '${line.id}')" title="تقسيم استلام هذا الصنف">➕</button>
                        ${line.isSplit ? `<button type="button" class="btn-action" style="padding: 4px 8px; font-size: 0.8rem; background: rgba(239,68,68,0.2); border-color: rgba(239,68,68,0.3); color: #ef4444;" onclick="removeSplit(event, '${line.id}')" title="حذف هذا التقسيم">❌</button>` : ''}
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Update summary details
            let totalItems = 0;
            let totalQty = 0;
            let totalVal = 0;
            
            const distinctItems = new Set();
            receiptLines.forEach(line => {
                if (line.receiveQty > 0) {
                    distinctItems.add(line.itemId);
                    totalQty += line.receiveQty;
                    totalVal += line.receiveQty * line.price;
                }
            });
            totalItems = distinctItems.size;

            document.getElementById('lbl-total-received-items').textContent = totalItems + ' أصناف';
            document.getElementById('lbl-total-received-qty').textContent = fmtNum(totalQty);
            document.getElementById('lbl-total-received-val').textContent = fmtNum(totalVal) + ' SDG';

            if (scrollWrap) {
                scrollWrap.scrollTop = savedScrollTop;
            }
        }

        function updateLineWarehouse(lineId, whId) {
            const line = receiptLines.find(x => x.id === lineId);
            if (line) {
                line.warehouseId = whId;
            }
        }

        function changeQtyStep(lineId, step) {
            const line = receiptLines.find(x => x.id === lineId);
            if (line) {
                const newVal = Math.max(0, (line.receiveQty || 0) + step);
                updateReceiveQty(lineId, String(newVal));
            }
        }

        function splitLine(e, lineId) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            const line = receiptLines.find(x => x.id === lineId);
            if (!line) return;
            
            const parentIndex = receiptLines.findIndex(x => x.id === lineId);
            
            // Calculate total unassigned qty for this group
            const groupSplits = receiptLines.filter(x => x.lineGroup === line.lineGroup);
            const totalAssigned = groupSplits.reduce((acc, x) => acc + (x.receiveQty || 0), 0);
            const remaining = Math.max(0, line.maxPending - totalAssigned);

            const newLine = {
                id: line.lineGroup + '_split_' + Date.now() + '_' + Math.floor(Math.random() * 10000),
                itemId: line.itemId,
                unitId: line.unitId,
                unitName: line.unitName,
                price: line.price || 0,
                maxPending: line.maxPending,
                receiveQty: remaining, // default to remaining qty
                warehouseId: line.warehouseId,
                lineGroup: line.lineGroup,
                isSplit: true
            };
            
            receiptLines.splice(parentIndex + 1, 0, newLine);
            renderReceiptLines();
        }

        function removeSplit(e, lineId) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            receiptLines = receiptLines.filter(x => x.id !== lineId);
            renderReceiptLines();
        }

        function updateReceiveQty(lineId, valStr) {
            const val = parseNum(valStr);
            const line = receiptLines.find(x => x.id === lineId);
            if (line) {
                // Sum all splits for this item group EXCEPT the current one
                const otherSplits = receiptLines.filter(x => x.lineGroup === line.lineGroup && x.id !== lineId);
                const otherQty = otherSplits.reduce((acc, x) => acc + (x.receiveQty || 0), 0);
                const maxAllowed = line.maxPending - otherQty;

                if (val > maxAllowed) {
                    showToast(`الكمية المستلمة لا يمكن أن تتجاوز الكمية المعلقة للملف (${line.maxPending}). المتبقي المتاح: ${maxAllowed}`, 'error');
                    line.receiveQty = Math.max(0, maxAllowed);
                } else if (val < 0) {
                    line.receiveQty = 0;
                } else {
                    line.receiveQty = val;
                }
            }
            renderReceiptLines();
        }

        function generateGRNNumber() {
            const d = new Date();
            const yy = d.getFullYear().toString().slice(-2);
            const mm = (d.getMonth() + 1).toString().padStart(2, '0');
            const prefix = `Z${yy}${mm}`;
            
            let maxSeq = -1;
            if (window.allPurchases) {
                allPurchases.forEach(p => {
                    if (p.isGRN && p.id && p.id.startsWith(prefix)) {
                        const seqStr = p.id.substring(prefix.length);
                        const seqNum = parseInt(seqStr, 10);
                        if (!isNaN(seqNum) && seqNum > maxSeq) {
                            maxSeq = seqNum;
                        }
                    }
                });
            }
            const nextSeq = maxSeq === -1 ? 0 : maxSeq + 1;
            return prefix + nextSeq.toString().padStart(5, '0');
        }

        function saveReceiptNote() {
            const receivingItems = receiptLines.filter(x => x.receiveQty > 0);
            if (receivingItems.length === 0) {
                return showToast('يجب تحديد كمية استلام لصنف واحد على الأقل', 'error');
            }

            const hasEmptyWh = receivingItems.some(x => !x.warehouseId);
            if (hasEmptyWh) return showToast('يرجى تحديد المستودع لجميع الأصناف المستلمة', 'error');

            const invData = pendingMap[currentInvoiceId];
            
            const newItems = [];
            receivingItems.forEach(line => {
                // Negative (Take out of PENDING)
                newItems.push({
                    itemId: line.itemId,
                    unitId: line.unitId,
                    unitName: line.unitName,
                    quantity: -line.receiveQty,
                    warehouseId: 'PENDING_RECEIPT',
                    price: line.price || 0,
                    taxRate: 0,
                    total: -((line.price || 0) * line.receiveQty)
                });
                // Positive (Put into ACTUAL)
                newItems.push({
                    itemId: line.itemId,
                    unitId: line.unitId,
                    unitName: line.unitName,
                    quantity: line.receiveQty,
                    warehouseId: line.warehouseId,
                    price: line.price || 0,
                    taxRate: 0,
                    total: (line.price || 0) * line.receiveQty
                });
            });

            const firstWh = receivingItems[0].warehouseId;

            const grnInv = {
                id: generateGRNNumber(),
                isGRN: true,
                originalInvoiceId: currentInvoiceId,
                type: 'purchase',
                customerId: invData.supplierId,
                date: new Date().toISOString(),
                warehouse: firstWh,
                subtotal: 0,
                taxValue: 0,
                discountValue: 0,
                grandTotal: 0,
                items: newItems,
                status: 'completed',
                notes: `مذكرة استلام بضاعة للفاتورة ${currentInvoiceId}`,
                createdBy: (JSON.parse(localStorage.getItem('user')) || {}).id || 'unknown'
            };

            // Revert stock of the old GRN first if we are in edit mode
            let currentInvData = [];
            try {
                currentInvData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
            } catch(e) {}

            if (editingGRNId) {
                const oldGrn = allPurchases.find(p => p.id === editingGRNId);
                if (oldGrn) {
                    const oldPositiveItems = oldGrn.items.filter(item => item.quantity > 0);
                    oldPositiveItems.forEach(pItem => {
                        const whObj = warehousesDb.find(w => w.id === pItem.warehouseId);
                        const whName = whObj ? whObj.name : 'المخزن الرئيسي';
                        let itemInv = currentInvData.find(inv => inv.itemId === pItem.itemId && inv.warehouse === whName);
                        if (itemInv) {
                            itemInv.qty = (parseFloat(itemInv.qty) || parseFloat(itemInv.quantity) || 0) - parseFloat(pItem.quantity);
                            if (itemInv.qty < 0) itemInv.qty = 0;
                        }
                    });
                }
            }

            // Update actual stock in erp_inventory_v1 for new quantities
            receivingItems.forEach(line => {
                const whObj = warehousesDb.find(w => w.id === line.warehouseId);
                const whName = whObj ? whObj.name : 'المخزن الرئيسي';
                
                // We add to actual stock level
                let itemInv = currentInvData.find(inv => inv.itemId === line.itemId && inv.warehouse === whName);
                if (itemInv) {
                    itemInv.qty = (parseFloat(itemInv.qty) || parseFloat(itemInv.quantity) || 0) + parseFloat(line.receiveQty);
                } else {
                    currentInvData.push({
                        itemId: line.itemId,
                        warehouse: whName,
                        qty: parseFloat(line.receiveQty),
                        minQty: 10
                    });
                }
            });
            localStorage.setItem('erp_inventory_v1', JSON.stringify(currentInvData));

            // Refresh from storage to prevent stale data overwrites (Concurrency fix)
            let freshPurchases = [];
            try {
                freshPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
            } catch(e) {}

            if (editingGRNId) {
                // Edit mode: overwrite old GRN in freshPurchases
                const grnIdx = freshPurchases.findIndex(p => p.id === editingGRNId);
                if (grnIdx > -1) {
                    freshPurchases[grnIdx].items = newItems;
                    freshPurchases[grnIdx].date = new Date().toISOString();
                    freshPurchases[grnIdx].warehouse = firstWh;
                    freshPurchases[grnIdx].notes = `تعديل مذكرة استلام بضاعة للفاتورة ${currentInvoiceId}`;
                    freshPurchases[grnIdx].createdBy = (JSON.parse(localStorage.getItem('user')) || {}).id || 'unknown';
                }
                showToast(`تم تعديل الإذن ${editingGRNId} بنجاح`, 'success');
                editingGRNId = null; // Clear edit mode
            } else {
                // New mode: add new GRN
                freshPurchases.push(grnInv);
                showToast(`تم حفظ مذكرة الاستلام وتحديث المخزون الفعلي بنجاح`, 'success');
            }

            localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(freshPurchases));
            allPurchases = freshPurchases; // Update local memory reference
            
            // 🔥 CRITICAL: Force immediate cloud sync to prevent data loss when navigating to reports
            if (typeof window.pushToCloud === 'function') {
                window.pushToCloud(true);
            }
            
            setTimeout(() => {
                calculatePendingGoods();
                renderPendingInvoices();
                if (pendingMap[currentInvoiceId]) {
                    openReceiptEditor(currentInvoiceId);
                } else {
                    showHub();
                }
            }, 1500);
        }

        function renderRecentGRNs(invoiceId) {
            const container = document.getElementById('recent-grns-container');
            if (!container) return;

            // Load all GRNs for all invoices
            const grns = allPurchases.filter(p => p.isGRN);
            
            // Sort by date descending (newest first - safely)
            grns.sort((a, b) => {
                let tA = new Date(a.date).getTime();
                let tB = new Date(b.date).getTime();
                if(isNaN(tA)) tA = 0;
                if(isNaN(tB)) tB = 0;
                return tB - tA;
            });
            
            if (currentHubView === 'received') {
                document.getElementById('hub-title-text').innerHTML = `📜 أذونات الاستلام (الفواتير المستلمة)`;
                const counterContainer = document.getElementById('hub-counter-badge');
                counterContainer.innerHTML = '';
                setTimeout(() => {
                    counterContainer.innerHTML = `<span class="counter-animate-abs" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; padding: 6px 30px; border-radius: 40px; font-size: 1.6rem; font-weight: 900; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.5), inset 0 2px 5px rgba(255,255,255,0.3); border: 2px solid rgba(255,255,255,0.2); font-family: 'Outfit', 'Tajawal', sans-serif; white-space: nowrap; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">${grns.length} إذن</span>`;
                }, 10);
            }
            
            if (grns.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; color: #64748b; padding: 20px; font-size: 0.85rem;">
                        لا توجد عمليات استلام سابقة في النظام.
                    </div>
                `;
                return;
            }

            let html = `
                <table class="pos-grid" style="width: 100%; font-size: 0.8rem; margin-top: 10px;">
                    <thead>
                        <tr>
                            <th style="text-align:right;">رقم الاستلام</th>
                            <th style="text-align:center; width:15%;">الفاتورة الأصلية</th>
                            <th style="text-align:center;">التاريخ والوقت</th>
                            <th style="text-align:right;">الأصناف المستلمة</th>
                            <th style="text-align:center;">بواسطة</th>
                            <th style="text-align:center; width: 120px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            grns.forEach(grn => {
                const isEditingThis = grn.id === editingGRNId;
                const rowBg = isEditingThis ? 'background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.4);' : '';
                const editBadge = isEditingThis ? '<span style="background: #f59e0b; color: #000; padding: 2px 6px; border-radius: 4px; font-size: 0.65rem; font-weight: bold; margin-right: 8px;">قيد التعديل ✏️</span>' : '';

                const positiveItems = grn.items.filter(item => item.quantity > 0);
                let itemsSummary = positiveItems.map(item => {
                    const itemObj = itemsDb.find(i => i.id === item.itemId);
                    const name = itemObj ? itemObj.name : 'صنف غير معروف';
                    return `${name} (${item.quantity} ${item.unitName || 'وحدة'})`;
                }).join('<br>');

                let dateStr = '—';
                try {
                    dateStr = new Date(grn.date).toLocaleString('ar-EG-u-nu-latn', {
                        year: 'numeric', month: 'numeric', day: 'numeric',
                        hour: '2-digit', minute: '2-digit'
                    });
                } catch(e) {}

                const creatorUser = usersDb.find(u => u.id === grn.createdBy || u.username === grn.createdBy);
                const creatorName = creatorUser ? creatorUser.name : grn.createdBy || 'غير معروف';

                html += `
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.03); ${rowBg}">
                        <td style="color: #38bdf8; font-weight: 800; font-family: 'Outfit';">${grn.id} ${editBadge}</td>
                        <td style="text-align:center; color: #fbbf24; font-weight: bold; font-family: 'Outfit';">${grn.originalInvoiceId}</td>
                        <td style="text-align:center; font-family: 'Outfit';">${dateStr}</td>
                        <td style="color:#e2e8f0; line-height: 1.4;">${itemsSummary}</td>
                        <td style="text-align:center; color:#a855f7; font-weight: 600;">${creatorName}</td>
                        <td style="text-align:center;">
                            <div style="display: flex; gap: 6px; justify-content: center;">
                                <button class="btn-action" style="padding: 4px 8px; font-size: 0.75rem; background: linear-gradient(135deg, #3b82f6, #2563eb);" onclick="editGRN('${grn.id}')">تعديل</button>
                                <button class="btn-action" style="padding: 4px 8px; font-size: 0.75rem; background: linear-gradient(135deg, #ef4444, #dc2626);" onclick="deleteGRN('${grn.id}')">حذف</button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
            `;
            container.innerHTML = html;
        }

        window.deleteGRN = function(grnId) {
            if (!confirm('هل أنت متأكد من حذف عملية الاستلام هذه؟ سيؤدي ذلك لإعادة الكميات المستلمة كبضاعة معلقة.')) {
                return;
            }

            const grn = allPurchases.find(p => p.id === grnId);
            if (!grn) return;

            // Revert actual stock in erp_inventory_v1
            let currentInvData = [];
            try {
                currentInvData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
            } catch(e) {}

            const positiveItems = grn.items.filter(item => item.quantity > 0);
            positiveItems.forEach(pItem => {
                const whObj = warehousesDb.find(w => w.id === pItem.warehouseId);
                const whName = whObj ? whObj.name : 'المخزن الرئيسي';

                let itemInv = currentInvData.find(inv => inv.itemId === pItem.itemId && inv.warehouse === whName);
                if (itemInv) {
                    itemInv.qty = (parseFloat(itemInv.qty) || parseFloat(itemInv.quantity) || 0) - parseFloat(pItem.quantity);
                    if (itemInv.qty < 0) itemInv.qty = 0;
                }
            });
            localStorage.setItem('erp_inventory_v1', JSON.stringify(currentInvData));

            const index = allPurchases.findIndex(p => p.id === grnId);
            if (index > -1) {
                allPurchases.splice(index, 1);
                localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));
                showToast('تم حذف عملية الاستلام وتحديث البضاعة المعلقة بنجاح', 'success');
                
                if (editingGRNId === grnId) {
                    editingGRNId = null;
                }

                calculatePendingGoods();
                renderPendingInvoices();
                
                // If we are currently editing an invoice, refresh the editor
                if (currentInvoiceId) {
                    openReceiptEditor(currentInvoiceId);
                } else {
                    showHub();
                }
            }
        };

        window.editGRN = function(grnId) {
            const grn = allPurchases.find(p => p.id === grnId);
            if (!grn) return;

            editingGRNId = grnId; // Enter edit mode

            // Recalculate pending goods ignoring this GRN
            calculatePendingGoods();
            renderPendingInvoices();
            
            // Open editor for the original invoice of this GRN
            openReceiptEditor(grn.originalInvoiceId);
            
            // Pre-fill target quantities from this GRN
            const positiveItems = grn.items.filter(item => item.quantity > 0);
            setTimeout(() => {
                // Clear any default receiptLines quantities first
                receiptLines.forEach(l => {
                    l.receiveQty = 0;
                });

                positiveItems.forEach(pItem => {
                    const line = receiptLines.find(l => l.itemId === pItem.itemId);
                    if (line) {
                        line.receiveQty = pItem.quantity;
                        line.warehouseId = pItem.warehouseId;
                    }
                });
                renderReceiptLines();
                showToast(`تم تحميل إذن الاستلام ${grnId} للتعديل. يرجى مراجعة وتعديل الكميات والحفظ.`, 'warning');
            }, 150);
        };

        document.getElementById('actual-warehouse').addEventListener('change', (e) => {
            const wh = e.target.value;
            receiptLines.forEach(line => {
                line.warehouseId = wh;
            });
            renderReceiptLines();
        });

    </script>
</body>
</html>

