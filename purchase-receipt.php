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
        :root.light-theme .qty-input,
        :root.light-theme .factor-input {
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
\n
        /* Hide number input spinners / up-down arrows */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none !important;
            margin: 0 !important;
        }
        input[type=number] {
            -moz-appearance: textfield !important;
            appearance: textfield !important;
        }
    
        @keyframes writeGlowPulse {
            0% { border-color: #facc15; box-shadow: 0 0 6px rgba(250, 204, 21, 0.35), inset 0 2px 4px rgba(0,0,0,0.3); }
            50% { border-color: #38bdf8; box-shadow: 0 0 16px rgba(56, 189, 248, 0.55), inset 0 2px 4px rgba(0,0,0,0.3); }
            100% { border-color: #facc15; box-shadow: 0 0 6px rgba(250, 204, 21, 0.35), inset 0 2px 4px rgba(0,0,0,0.3); }
        }
        .write-indicator-box {
            animation: writeGlowPulse 2.5s infinite ease-in-out;
        }
        .write-indicator-box:focus, .write-indicator-box.focused {
            animation: none !important;
            border: 2.5px solid #10b981 !important;
            box-shadow: 0 0 22px rgba(16, 185, 129, 0.65), inset 0 2px 4px rgba(0,0,0,0.3) !important;
            background: rgba(15, 23, 42, 1) !important;
        }
    </style>
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
                    
                    <input type="text" id="search-inv" class="v-input" placeholder="ابحث برقم الفاتورة أو المورد..." style="width: 300px; z-index: 2;" oninput="if(currentHubView === 'pending') renderPendingInvoices(true); else renderRecentGRNs(true);">
                </div>
                
                <div id="hub-pending-container">
                    <div class="grid-scroll-wrap">
                        <table class="pos-grid">
                            <thead>
                                <tr>
                                    <th style="text-align:right;">رقم الفاتورة الأصلي</th>
                                    <th style="text-align:right;">التاريخ</th>
                                    <th style="text-align:right;">المورد</th>
                                    <th style="text-align:center;">الصنف</th>
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

            <div class="editor-layout" style="display: flex; flex-direction: column; gap: 15px; flex: 1; width: 100%; min-height: 0;">
                <!-- Full Width: Items Table -->
                <div class="editor-table-box" style="display: flex; flex-direction: column; width: 100%; flex: 1;">
                    <div class="neo-card-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; background: rgba(30, 41, 59, 0.4); padding: 12px 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); flex-wrap: wrap; gap: 10px;">
                        <span style="font-size: 1.15rem; font-weight: 800; color: #fff; display: flex; align-items: center; gap: 8px;">
                            <span>📦</span> قائمة الأصناف المعلقة للفاتورة
                        </span>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <button class="btn-action" style="padding: 10px 26px; font-size: 1.05rem; font-weight: 800; display:flex; align-items:center; justify-content:center; gap:8px; border-radius:10px; background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 15px rgba(16,185,129,0.4); border: none; color: #fff; cursor: pointer; transition: all 0.2s ease;" onclick="saveReceiptNote()">
                                <span>💾</span> استلام وتأكيد البضاعة
                            </button>
                            <button class="btn-action" style="padding: 9px 16px; font-size: 0.9rem; border-radius: 9px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8; font-weight: 700; display: flex; align-items: center; gap: 6px;" onclick="openGridSettings()">
                                <span>⚙️</span> خيارات وتنسيق الجدول
                            </button>
                            <button class="btn-action btn-cancel" style="padding: 9px 16px; font-size: 0.9rem; border-radius: 9px;" onclick="showHub()">إلغاء وتراجع ↩️</button>
                        </div>
                    </div>
                    
                    <div class="grid-scroll-wrap" style="flex: 1; max-height: 480px; min-height: 240px; overflow-y: auto; border-radius: 14px; border: 1px solid rgba(255,255,255,0.08); background: rgba(15, 23, 42, 0.6); box-shadow: inset 0 2px 10px rgba(0,0,0,0.3);">
                        <table class="pos-grid" style="width: 100%; border-collapse: separate; border-spacing: 0 6px; padding: 0 10px;">
                            <thead style="position: sticky; top: 0; z-index: 10; background: #0f172a;">
                                <tr>
                                    <th style="display:none;">سعر الوحدة</th>
                                    <th style="display:none;">القيمة المستلمة</th>
                                    <th style="text-align:center; width:9%; color:#e2e8f0; font-weight:700; font-size:0.95rem; padding: 12px 5px;">كمية الشراء</th>
                                    <th style="text-align:right; width:19%; color:#e2e8f0; font-weight:700; font-size:0.95rem; padding: 12px 10px;">الصنف</th>
                                    <th style="text-align:center; width:8%; color:#e2e8f0; font-weight:700; font-size:0.95rem; padding: 12px 5px;">وحدة الشراء</th>
                                    <th style="text-align:center; width:8%; color:#e2e8f0; font-weight:700; font-size:0.95rem; padding: 12px 5px;">الكمية المتبقية</th>
                                    <th style="text-align:center; width:6%; color:#e2e8f0; font-weight:700; font-size:0.95rem; padding: 12px 5px;">التعادل</th>
                                    <th style="text-align:center; width:10%; color:#e2e8f0; font-weight:700; font-size:0.95rem; padding: 12px 5px;">الكمية بالوحدة الأساسية</th>
                                    <th style="text-align:center; width:13%; color:#38bdf8; font-weight:800; font-size:0.95rem; padding: 12px 5px; cursor: pointer; user-select: none;" onclick="openGridSettings('rcvQty')" title="انقر لتعديل حجم الخط ولون الخط للكمية المستلمة"><span style="display:inline-flex; align-items:center; gap:5px; background: rgba(56, 189, 248, 0.12); padding: 4px 10px; border-radius: 8px; border: 1px solid rgba(56, 189, 248, 0.3);">✍️ الكمية المستلمة (اكتب هنا) ⚙️</span></th>
                                    <th style="text-align:center; width:8%; color:#e2e8f0; font-weight:700; font-size:0.95rem; padding: 12px 5px;">الكمية بالوحدة</th>
                                    <th style="text-align:center; width:9%; color:#e2e8f0; font-weight:700; font-size:0.95rem; padding: 12px 5px;">المستودع المُستلِم</th>
                                    <th style="text-align:center; width:10%; color:#38bdf8; font-weight:700; font-size:0.95rem; padding: 12px 5px;">📝 ملحوظة الاستلام</th>
                                </tr>
                            </thead>
                            <tbody id="items-tbody">
                                <!-- Populated by JS with Boxed Elements -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Hidden reference for default warehouse to avoid breaking JS -->
                <select id="actual-warehouse" style="display:none;"></select>

                <!-- Full Width Bottom Summary Bar -->
                <div class="editor-bottom-bar" style="position: sticky; bottom: 0; z-index: 20; background: rgba(15, 23, 42, 0.85); border: 1.5px solid rgba(56, 189, 248, 0.25); padding: 12px 24px; border-radius: 16px; display: flex; justify-content: space-between; align-items: center; gap: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); backdrop-filter: blur(16px); flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                        <span style="color: #94a3b8; font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 1.3rem;">📊</span> ملخص عملية الاستلام:
                        </span>
                        
                        <div style="background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(16, 185, 129, 0.3); padding: 8px 18px; border-radius: 12px; display: flex; align-items: center; gap: 10px;">
                            <span style="color: #94a3b8; font-size: 0.85rem;">أصناف مستلمة:</span>
                            <span id="lbl-total-received-items" style="color: #10b981; font-weight: 800; font-family: 'Outfit', sans-serif; font-size: 1.2rem;">0 أصناف</span>
                        </div>

                        <div style="background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(56, 189, 248, 0.3); padding: 8px 18px; border-radius: 12px; display: flex; align-items: center; gap: 10px;">
                            <span style="color: #94a3b8; font-size: 0.85rem;">كمية مستلمة:</span>
                            <span id="lbl-total-received-qty" style="color: #38bdf8; font-weight: 800; font-family: 'Outfit', sans-serif; font-size: 1.3rem;">0</span>
                        </div>

                        <div style="background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(250, 204, 21, 0.3); padding: 8px 18px; border-radius: 12px; display: flex; align-items: center; gap: 10px;">
                            <span style="color: #94a3b8; font-size: 0.85rem;">القيمة المستلمة:</span>
                            <span id="lbl-total-received-val" style="color: #facc15; font-weight: 800; font-family: 'Outfit', sans-serif; font-size: 1.25rem;">0 SDG</span>
                        </div>
                    </div>

                    <div>
                        <button class="btn-action" style="padding: 12px 35px; font-size: 1.15rem; font-weight: 800; display:flex; align-items:center; justify-content:center; gap:10px; border-radius:14px; background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 20px rgba(16,185,129,0.4); border: none; color: #fff; cursor: pointer; transition: all 0.2s ease;" onclick="saveReceiptNote()">
                            <span>💾</span> استلام وتأكيد البضاعة
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

    <script src="js/magaies-offline-sync.js?v=20261014"></script>
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>
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

        // --- Pagination Logic ---
        let pendingDisplayCount = 50;
        let receivedDisplayCount = 50;

        window.loadMorePending = function() {
            pendingDisplayCount += 50;
            renderPendingInvoices(false);
        };

        window.loadMoreReceived = function() {
            receivedDisplayCount += 50;
            renderRecentGRNs(false);
        };

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


        
        async function loadGridPrefsFromDB() {
            try {
                const u = JSON.parse(localStorage.getItem('user') || '{}');
                const userCandidates = [];
                if (u.id) userCandidates.push(String(u.id));
                if (u.username && u.username !== u.id) userCandidates.push(String(u.username));
                userCandidates.push('default_user');

                let prefsData = null;
                for (const uid of userCandidates) {
                    try {
                        const res = await fetch('api/get_user_settings.php?user_id=' + encodeURIComponent(uid));
                        const data = await res.json();
                        if (data.status === 'success' && data.data && data.data['erp_grn_col_prefs_v2']) {
                            prefsData = data.data['erp_grn_col_prefs_v2'];
                            break;
                        }
                    } catch(err) {}
                }

                if (prefsData) {
                    const prefsStr = typeof prefsData === 'string' ? prefsData : JSON.stringify(prefsData);
                    localStorage.setItem('erp_grn_col_prefs_v2', prefsStr);
                    if (document.getElementById('view-editor') && document.getElementById('view-editor').classList.contains('active')) {
                        renderReceiptLines();
                    }
                }
            } catch(e) { console.error('Error loading grid prefs from DB', e); }
        }

        function initData() {
            loadGridPrefsFromDB();

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
            if (!suppliersDb || suppliersDb.length === 0) {
                suppliersDb = [
    { id: 'SUP-001', code: 'SUP-001', name: 'شركة مصنع الأسعد للحديد والصلب', phone: '0123456789', currentBalance: 0, creditLimit: 50000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-002', code: 'SUP-002', name: 'شركة أوميغا للصناعات والصلب', phone: '0912345678', currentBalance: 0, creditLimit: 30000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-003', code: 'SUP-003', name: 'شركة أسمنت عطبرة المحدودة', phone: '0112345678', currentBalance: 0, creditLimit: 20000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-004', code: 'SUP-004', name: 'مجمع جياد الصناعي', phone: '0901234567', currentBalance: 0, creditLimit: 100000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-005', code: 'SUP-005', name: 'مؤسسة الهدى للتجارة والتوريدات', phone: '0123987654', currentBalance: 0, creditLimit: 15000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-006', code: 'SUP-006', name: 'شركة أسمنت بربر المحدودة', phone: '0912987654', currentBalance: 0, creditLimit: 25000000, status: 'active', securityLevel: 1 },
    { id: 'SUP-007', code: 'SUP-007', name: 'مصنع الراجحي للحديد والصلب', phone: '0123112233', currentBalance: 0, creditLimit: 40000000, status: 'active', securityLevel: 1 }
];
            }
            warehousesDb = loadKey('erp_warehouses_v1');
            usersDb = loadKey('erp_users_v1');
            if(warehousesDb.length === 0) warehousesDb = [{ id:'w-main', name:'المخزن الرئيسي' }];

            // Auto-migrate and fix any old GRN sequences or duplicates
            if (!localStorage.getItem('grns_renumbered_clean_z2')) {
                let allGrns = allPurchases.filter(p => p.isGRN);
                if (allGrns.length > 0) {
                    allGrns.sort((a,b) => new Date(a.date).getTime() - new Date(b.date).getTime());
                    let c = {};
                    let changed = false;
                    allGrns.forEach(grn => {
                        const d = new Date(grn.date);
                        if(isNaN(d.getTime())) return;
                        const yy = d.getFullYear().toString().slice(-2);
                        const mm = (d.getMonth() + 1).toString().padStart(2, '0');
                        const prefix = `Z${yy}${mm}`;
                        if (!c[prefix]) c[prefix] = 0;
                        const newId = prefix + c[prefix].toString().padStart(5, '0');
                        c[prefix]++;
                        
                        if (grn.id !== newId) {
                            const idx = allPurchases.findIndex(x => x.id === grn.id);
                            if (idx > -1) {
                                allPurchases[idx].id = newId;
                                changed = true;
                            }
                        }
                    });
                    if (changed) {
                        localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));
                        if (typeof window.pushToCloud === 'function') window.pushToCloud(true);
                    }
                }
                localStorage.setItem('grns_renumbered_clean_z2', 'true');
            }

            populateWarehouseSelect();
            calculatePendingGoods();
            renderPendingInvoices();
            syncPurchaseReceiptsWithMariaDB();
        }

        let _isSyncingPurReceiptsDB = false;
        async function syncPurchaseReceiptsWithMariaDB() {
            if (_isSyncingPurReceiptsDB || !navigator.onLine) return;
            _isSyncingPurReceiptsDB = true;
            try {
                const res = await fetch('api/mirror_sync.php?action=get_recent_purchases&_t=' + Date.now());
                if (!res.ok) return;
                const data = await res.json();
                if (data && data.success && Array.isArray(data.purchases)) {
                    let changed = false;
                    data.purchases.forEach(remoteInv => {
                        const rNo = remoteInv.invoiceNo || remoteInv.id;
                        if (!rNo) return;
                        const existIdx = allPurchases.findIndex(p => (p.invoiceNo || p.id) === rNo);
                        if (existIdx === -1) {
                            allPurchases.unshift(remoteInv);
                            changed = true;
                        } else {
                            if (remoteInv.receiptStatus === 'received' && allPurchases[existIdx].receiptStatus !== 'received') {
                                allPurchases[existIdx].receiptStatus = 'received';
                                allPurchases[existIdx].status = 'received';
                                changed = true;
                            }
                        }
                    });
                    if (changed) {
                        localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));
                        calculatePendingGoods();
                        renderPendingInvoices(false);
                    }
                }
            } catch (e) {
                console.warn('Purchase receipt MariaDB sync warning:', e);
            } finally {
                _isSyncingPurReceiptsDB = false;
            }
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
        // For each invoice, sum quantities where warehouseId == 'PENDING_RECEIPT' or unreceived Mobile purchase invoices.
        // Original invoices add positive pending qty. Returns subtract pending qty. GRNs subtract pending qty.
        function calculatePendingGoods() {
            pendingMap = {}; // target: { invId: { supplierId, date, lines: { itemId: { unitId, qty, originalLine } } } }

            // Base tracking by invoice
            const grnInvoiceIds = new Set(allPurchases.filter(x => x && !x.deleted && (x.isGRN || x.isReturn) && x.originalInvoiceId).map(x => String(x.originalInvoiceId)));
            allPurchases.filter(inv => !inv.deleted && inv.status !== 'draft' && inv.status !== 'proforma' && inv.id !== editingGRNId && (inv.isGRN || inv.isReturn || grnInvoiceIds.has(String(inv.invoiceNo || inv.id)) || (inv.receiptStatus !== 'received' && inv.status !== 'received'))).forEach(inv => {
                const rootInvId = inv.isReturn || inv.isGRN ? inv.originalInvoiceId : (inv.invoiceNo || inv.id);
                if (!rootInvId) {
                    return;
                }

                if (!pendingMap[rootInvId]) {
                    pendingMap[rootInvId] = {
                        supplierId: inv.customer || inv.customerId || inv.supplierId || inv.supplierName || inv.supplier,
                        supplierName: inv.supplierName || inv.vendorName || inv.supplier || '',
                        date: inv.date || (inv.savedAt ? inv.savedAt.split('T')[0] : ''),
                        createdBy: inv.createdBy || inv.createdByName || 'unknown',
                        lines: {}
                    };
                } else if (inv.createdBy && pendingMap[rootInvId].createdBy === 'unknown') {
                    pendingMap[rootInvId].createdBy = inv.createdBy;
                }

                const items = inv.items || inv.lines || [];
                const globalWh = inv.warehouse;

                const isOriginal = (!inv.isReturn && !inv.isGRN);
                items.forEach(line => {
                    const lineWh = line.warehouseId || globalWh;
                    const isMobileUnreceived = isOriginal && (inv.receiptStatus === 'pending_receipt' || (!line.warehouseId && (line.qty !== undefined || line.name !== undefined)));
                    const isPending = isMobileUnreceived || (lineWh === 'PENDING_RECEIPT' || !lineWh || lineWh === '⏳ بضاعة قيد الاستلام (لم تحدد بعد)' || lineWh === '⏳ بضاعة قيد الاستلام');

                    if (isPending) {
                        const resolvedItemId = line.itemId || line.id || line.name || line.itemName || 'صنف';
                        const resolvedUnit = line.unitId || line.selectedUnit || line.unit || line.unitName || 'حبة';
                        const lineKey = resolvedItemId + '|' + resolvedUnit;
                        if (!pendingMap[rootInvId].lines[lineKey]) {
                            pendingMap[rootInvId].lines[lineKey] = {
                                itemId: resolvedItemId,
                                itemName: line.name || line.itemName || '',
                                unitId: resolvedUnit,
                                unitName: line.unitName || line.selectedUnit || line.unit || 'حبة',
                                factor: line.customFactor || line.factor || line.equiv || line.conversionFactor || 1,
                                price: parseFloat(line.price) || 0,
                                origQty: 0,
                                pendingQty: 0
                            };
                        }
                        
                        const q = parseFloat(line.quantity !== undefined ? line.quantity : line.qty) || 0;
                        if (isOriginal && q > 0) {
                            pendingMap[rootInvId].lines[lineKey].origQty = (pendingMap[rootInvId].lines[lineKey].origQty || 0) + q;
                        }
                        pendingMap[rootInvId].lines[lineKey].pendingQty += q;
                    }
                });
            });

            // Cleanup: remove items with <= 0 pending qty, and invoices with no pending items
            Object.keys(pendingMap).forEach(invId => {
                const lines = pendingMap[invId].lines;
                Object.keys(lines).forEach(lk => {
                    // Precision fix
                    lines[lk].pendingQty = Math.round(lines[lk].pendingQty * 100) / 100;
                    if (!lines[lk].origQty) {
                        lines[lk].origQty = lines[lk].pendingQty;
                    }
                    if (lines[lk].pendingQty <= 0) {
                        delete lines[lk];
                    }
                });
                if (Object.keys(lines).length === 0) {
                    delete pendingMap[invId];
                }
            });
        }

        function getSupplierName(id) {
            if(!id) return 'مورد نقدي';
            const sup = suppliersDb.find(s => s.id === id || s.name === id);
            if (sup) return sup.name;
            return String(id).startsWith('SUP-') || String(id).startsWith('vend-') ? 'مورد نقدي' : String(id);
        }

        function getItemName(id) {
            if (!id) return 'صنف غير معروف';
            const it = itemsDb.find(i => i.id === id || i.name === id);
            return it ? it.name : String(id);
        }

        function renderPendingInvoices(resetCount = true) {
            if (resetCount) pendingDisplayCount = 50;
            
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
                let btn = document.getElementById('pending-load-more');
                if (btn) btn.style.display = 'none';
            } else {
                document.getElementById('empty-hub').style.display = 'none';
                
                // Sort by date desc (safely)
                filteredIds.sort((a,b) => {
                    let tA = new Date(pendingMap[a].date).getTime();
                    let tB = new Date(pendingMap[b].date).getTime();
                    if(isNaN(tA)) tA = 0;
                    if(isNaN(tB)) tB = 0;
                    if (tA === tB) return b.localeCompare(a);
                    return tB - tA;
                });
                
                const itemsToRender = filteredIds.slice(0, pendingDisplayCount);
                
                itemsToRender.forEach(id => {
                    const invData = pendingMap[id];
                    const supName = getSupplierName(invData.supplierId);
                    
                    const totalPendingItems = Object.values(invData.lines).length;
                    const itemNames = Object.values(invData.lines).map(line => getItemName(line.itemId)).join('، ');

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td style="text-align:right; font-family:'Outfit'; color:#38bdf8; font-weight:700; cursor:pointer;" ondblclick="window.open('purchase.php?id=${id}', '_blank')" title="انقر مرتين لفتح الفاتورة">${id}</td>
                        <td style="text-align:right; font-family:'Outfit';">${new Date(invData.date).toLocaleDateString('en-GB')}</td>
                        <td style="text-align:right; font-weight:700;">${supName}</td>
                        <td style="text-align:center; font-weight:600; font-size:0.85rem; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${itemNames}">${itemNames}</td>
                        <td style="text-align:center; font-family:'Outfit'; font-weight:800;">${totalPendingItems} صنف</td>
                        <td style="text-align:center;">
                            <button class="btn-action" style="padding:6px 15px; font-size:0.8rem;" onclick="openReceiptEditor('${id}')">استلام البضاعة</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
                
                let loadMoreBtn = document.getElementById('pending-load-more');
                if (!loadMoreBtn) {
                    loadMoreBtn = document.createElement('div');
                    loadMoreBtn.id = 'pending-load-more';
                    loadMoreBtn.style = 'text-align: center; margin-top: 15px; margin-bottom: 20px;';
                    loadMoreBtn.innerHTML = `<button class="btn-action" style="padding: 10px 30px; font-size: 1rem; border-radius: 30px; background: linear-gradient(135deg, #f59e0b, #d97706);" onclick="loadMorePending()">عرض 50 فاتورة أخرى ⬇️</button>`;
                    document.getElementById('hub-pending-container').appendChild(loadMoreBtn);
                }
                if (filteredIds.length > pendingDisplayCount) {
                    loadMoreBtn.style.display = 'block';
                } else {
                    loadMoreBtn.style.display = 'none';
                }
            }
            
            if (currentHubView === 'pending') {
                document.getElementById('hub-title-text').innerHTML = `⏳ فواتير تحتوي على بضاعة قيد الاستلام`;
                const counterContainer = document.getElementById('hub-counter-badge');
                counterContainer.innerHTML = '';
                setTimeout(() => {
                    counterContainer.innerHTML = `<span class="counter-animate-abs" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; padding: 6px 30px; border-radius: 40px; font-size: 1.6rem; font-weight: 900; box-shadow: 0 10px 25px rgba(245, 158, 11, 0.5), inset 0 2px 5px rgba(255,255,255,0.3); border: 2px solid rgba(255,255,255,0.2); font-family: 'Outfit', 'Tajawal', sans-serif; white-space: nowrap; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">${filteredIds.length} إذن لم يستلم</span>`;
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
                let initialFactor = 1;
                if (line.factor && parseFloat(line.factor) > 0) {
                    initialFactor = parseFloat(line.factor);
                } else if (line.customFactor && parseFloat(line.customFactor) > 0) {
                    initialFactor = parseFloat(line.customFactor);
                } else if (typeof itemsDb !== 'undefined') {
                    const itemObj = itemsDb.find(i => String(i.id) === String(line.itemId) || i.name === line.itemId || i.name === getItemName(line.itemId));
                    if (itemObj) {
                        const uName = String(line.unitName || line.unit || '').trim();
                        if (itemObj.units && Array.isArray(itemObj.units)) {
                            const u = itemObj.units.find(x => (x.name && x.name.trim() === uName) || (x.unitName && x.unitName.trim() === uName) || (x.id && String(x.id) === String(line.unitId)));
                            if (u && (u.factor || u.equiv)) initialFactor = parseFloat(u.factor || u.equiv) || 1;
                        } else if (itemObj.unitsArray && Array.isArray(itemObj.unitsArray)) {
                            const u = itemObj.unitsArray.find(x => (x.name && x.name.trim() === uName) || (x.unitName && x.unitName.trim() === uName));
                            if (u && (u.factor || u.equiv)) initialFactor = parseFloat(u.factor || u.equiv) || 1;
                        }
                    }
                }
                if (isNaN(initialFactor) || initialFactor <= 0) initialFactor = 1;

                return {
                    id: 'L' + index,
                    itemId: line.itemId,
                    unitId: line.unitId,
                    unitName: line.unitName,
                    factor: initialFactor,
                    customFactor: initialFactor,
                    price: line.price || 0,
                    origQty: line.origQty || line.pendingQty,
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

        
        function updateSummary() {
            let totalItems = 0;
            let totalQty = 0;
            let totalVal = 0;
            
            const distinctItems = new Set();
            receiptLines.forEach(line => {
                const rQty = parseFloat(line.receiveQty) || 0;
                if (rQty > 0) {
                    distinctItems.add(line.itemId);
                    totalQty += rQty;
                    totalVal += rQty * (parseFloat(line.price) || 0);
                }
            });
            totalItems = distinctItems.size;

            const elItems = document.getElementById('lbl-total-received-items');
            const elQty = document.getElementById('lbl-total-received-qty');
            const elVal = document.getElementById('lbl-total-received-val');

            if (elItems) elItems.textContent = totalItems + ' أصناف';
            if (elQty) elQty.textContent = fmtNum(totalQty);
            if (elVal) elVal.textContent = fmtNum(totalVal) + ' SDG';
        }


        // ══════════════════════════════════════════════════════════
        // ADVANCED GRID FORMATTING (PER-COLUMN & GLOBAL CONTROLS)
        // ══════════════════════════════════════════════════════════
        const DEFAULT_GRID_PREFS = {
            global: {
                font: "Tajawal",
                size: "16",
                weight: "700",
                color: "#ffffff",
                decimals: 0,
                thousands: true,
                align: "center"
            },
            cols: {
                origQty:     { font: "", size: "17", weight: "800", color: "#34d399", decimals: 0, thousands: true, align: "center" },
                item:        { font: "", size: "15", weight: "700", color: "#f8fafc", decimals: 0, thousands: true, align: "right" },
                unit:        { font: "", size: "15", weight: "600", color: "#93c5fd", decimals: 0, thousands: true, align: "center" },
                remQty:      { font: "", size: "17", weight: "800", color: "#fbbf24", decimals: 0, thousands: true, align: "center" },
                factor:      { font: "", size: "16", weight: "700", color: "#c084fc", decimals: 0, thousands: true, align: "center" },
                pendingBase: { font: "", size: "16", weight: "700", color: "#38bdf8", decimals: 0, thousands: true, align: "center" },
                rcvQty:      { font: "", size: "18", weight: "900", color: "#ffffff", decimals: 0, thousands: true, align: "center" },
                rcvBase:     { font: "", size: "17", weight: "800", color: "#34d399", decimals: 0, thousands: true, align: "center" },
                warehouse:   { font: "", size: "14", weight: "600", color: "#ffffff", decimals: 0, thousands: true, align: "center" }
            }
        };

        function getStoredGridPrefs() {
            let p = JSON.parse(JSON.stringify(DEFAULT_GRID_PREFS));
            try {
                const raw = localStorage.getItem("erp_grn_col_prefs_v2");
                if (raw) {
                    const parsed = JSON.parse(raw);
                    if (parsed.global) p.global = { ...p.global, ...parsed.global };
                    if (parsed.cols) p.cols = { ...p.cols, ...parsed.cols };
                }
            } catch(e) {}
            return p;
        }

        function getColStyleObj(colKey, fallbackAlign="center") {
            const prefs = getStoredGridPrefs();
            const g = prefs.global || DEFAULT_GRID_PREFS.global;
            const c = (prefs.cols && prefs.cols[colKey]) ? prefs.cols[colKey] : {};

            const font = (c.font && c.font !== "") ? c.font : g.font;
            const size = (c.size && c.size !== "") ? c.size : g.size;
            const weight = (c.weight && c.weight !== "") ? c.weight : g.weight;
            const color = (c.color && c.color !== "") ? c.color : g.color;
            const align = (c.align && c.align !== "") ? c.align : (g.align || fallbackAlign);
            const decimals = (c.decimals !== undefined && c.decimals !== "") ? parseInt(c.decimals) : parseInt(g.decimals || 0);
            const thousands = (c.thousands !== undefined) ? c.thousands : (g.thousands !== false);

            const justify = align === "right" ? "flex-start" : (align === "left" ? "flex-end" : "center");
            const boxStyle = `background: rgba(30, 41, 59, 0.45); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 9px; height: 48px; display: flex; align-items: center; justify-content: ${justify}; font-family: '${font}', 'Tajawal', sans-serif !important; font-size: ${size}px !important; font-weight: ${weight} !important; color: ${color} !important; padding: 0 10px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.2); transition: all 0.2s ease;`;

            return {
                boxStyle,
                font, size, weight, color, align, decimals, thousands
            };
        }

        function fmtColVal(colKey, n) {
            const cs = getColStyleObj(colKey);
            const num = Number(n) || 0;
            if (cs.thousands) {
                return num.toLocaleString("en-US", { minimumFractionDigits: cs.decimals, maximumFractionDigits: cs.decimals });
            } else {
                return num.toFixed(cs.decimals);
            }
        }

        function renderReceiptLines() {
            const scrollWrap = document.querySelector('#view-editor .grid-scroll-wrap');
            const savedScrollTop = scrollWrap ? scrollWrap.scrollTop : 0;

            const tbody = document.getElementById('items-tbody');
            tbody.innerHTML = '';

            const rcvCol = getColStyleObj('rcvQty');
            const inputStyle = `font-family: '${rcvCol.font}', 'Tajawal', sans-serif !important; font-size: ${rcvCol.size}px !important; font-weight: ${rcvCol.weight} !important; color: ${rcvCol.color} !important; text-align: ${rcvCol.align} !important; height: 48px; width: 100%; background: rgba(15, 23, 42, 0.95); border: 2px solid #38bdf8; border-radius: 9px; outline: none; padding-right: 28px; padding-left: 8px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3); transition: all 0.25s ease;`;

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
                
                let factor = line.factor !== undefined ? parseFloat(line.factor) : (line.customFactor !== undefined ? parseFloat(line.customFactor) : 1);
                if (isNaN(factor) || factor <= 0) {
                    if (typeof itemsDb !== 'undefined') {
                        const itemObj = itemsDb.find(i => String(i.id) === String(line.itemId) || i.name === line.itemId || i.name === getItemName(line.itemId));
                        if (itemObj) {
                            const uName = String(line.unitName || line.unit || '').trim();
                            if (itemObj.units && Array.isArray(itemObj.units)) {
                                const u = itemObj.units.find(x => (x.name && x.name.trim() === uName) || (x.unitName && x.unitName.trim() === uName) || (x.id && String(x.id) === String(line.unitId)));
                                if (u && (u.factor || u.equiv)) factor = parseFloat(u.factor || u.equiv) || 1;
                            } else if (itemObj.unitsArray && Array.isArray(itemObj.unitsArray)) {
                                const u = itemObj.unitsArray.find(x => (x.name && x.name.trim() === uName) || (x.unitName && x.unitName.trim() === uName));
                                if (u && (u.factor || u.equiv)) factor = parseFloat(u.factor || u.equiv) || 1;
                            }
                        }
                    }
                }
                if (isNaN(factor) || factor <= 0) factor = 1;
                line.factor = factor;
                line.customFactor = factor;

                const baseQty = (line.maxPending * factor);
                const rcvBaseQty = ((line.receiveQty || 0) * factor);
                const whCol = getColStyleObj('warehouse');
                const factorCol = getColStyleObj('factor');
                const factorInputStyle = `font-family: '${factorCol.font}', 'Tajawal', sans-serif !important; font-size: ${factorCol.size}px !important; font-weight: ${factorCol.weight} !important; color: ${factorCol.color} !important; text-align: ${factorCol.align} !important; height: 48px; width: 100%; background: rgba(15, 23, 42, 0.95); border: 2px solid rgba(56, 189, 248, 0.6); border-radius: 9px; outline: none; padding: 0 8px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3); transition: all 0.25s ease;`;
                
                tr.innerHTML = `
                    <td style="display:none;" class="grn-col-item">${line.price}</td>
                    <td style="display:none;" class="grn-col-item">${lineTotal}</td>
                    <td style="padding: 4px 3px;">
                        <div style="${getColStyleObj('origQty').boxStyle}" class="grn-col-origQty" title="كمية الشراء الأصلية بالفاتورة">${fmtColVal('origQty', line.origQty || line.maxPending)}</div>
                    </td>
                    <td style="padding: 4px 3px;">
                        <div style="${getColStyleObj('item', 'right').boxStyle}; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;" class="grn-col-item" title="${getItemName(line.itemId)}">📦 ${getItemName(line.itemId)}</div>
                    </td>
                    <td style="padding: 4px 3px;">
                        <div style="${getColStyleObj('unit').boxStyle}" class="grn-col-unit">${line.unitName || line.unit || '—'}</div>
                    </td>
                    <td style="padding: 4px 3px;">
                        <div style="${getColStyleObj('remQty').boxStyle}" class="grn-col-qty" title="الكمية المتبقية للاستلام">${fmtColVal('remQty', line.maxPending)}</div>
                    </td>
                    <td style="padding: 4px 3px;">
                        <div style="position: relative; width: 100%; display: flex; align-items: center;">
                            <input type="text" inputmode="decimal" class="factor-input grn-col-factor write-indicator-box" value="${factor}" placeholder="التعادل..." title="معامل التعادل (يمكن تعديله لتغيير الكمية المخزنية)" onfocus="this.select(); this.classList.add('focused');" onblur="this.classList.remove('focused'); if(!this.value.trim() || parseFloat(this.value) <= 0) this.value='1';" style="${factorInputStyle}">
                        </div>
                    </td>
                    <td style="padding: 4px 3px;">
                        <div style="${getColStyleObj('pendingBase').boxStyle}" class="grn-col-pendingBase">${fmtColVal('pendingBase', baseQty)}</div>
                    </td>
                    <td style="padding: 4px 3px;">
                        <div style="position: relative; width: 100%; display: flex; align-items: center;">
                            <span style="position: absolute; right: 10px; font-size: 1rem; color: #38bdf8; pointer-events: none; opacity: 0.9; z-index: 2;" title="حقل كتابة الكمية المستلمة">✏️</span>
                            <input type="text" inputmode="decimal" class="qty-input grn-col-rcvQty write-indicator-box" value="${line.receiveQty !== undefined ? line.receiveQty : line.maxPending}" placeholder="اكتب..." onfocus="this.select(); this.classList.add('focused');" onblur="this.classList.remove('focused'); if(!this.value.trim()) this.value='0';" style="${inputStyle}">
                        </div>
                    </td>
                    <td style="padding: 4px 3px;">
                        <div class="base-rcv-qty grn-col-baseQty" style="${getColStyleObj('rcvBase').boxStyle}">${fmtColVal('rcvBase', rcvBaseQty)}</div>
                    </td>
                    <td style="padding: 4px 3px;">
                        <select class="wh-select grn-col-warehouse" style="width: 100%; height: 48px; padding: 0 8px; background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(255,255,255,0.15); font-family: '${whCol.font}', 'Tajawal', sans-serif !important; color: ${whCol.color} !important; font-size: ${whCol.size}px !important; font-weight: ${whCol.weight} !important; text-align: ${whCol.align} !important; border-radius: 9px; outline: none; box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);">${whOptions}</select>
                    </td>
                    <td style="padding: 4px 3px;">
                        <input type="text" class="notes-input" value="${line.notes || ''}" placeholder="ملحوظة الاستلام..." style="width: 100%; height: 48px; padding: 0 10px; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255,255,255,0.15); font-family: 'Tajawal', sans-serif; color: #f8fafc; font-size: 13px; font-weight: 600; border-radius: 9px; outline: none; box-shadow: inset 0 1px 3px rgba(0,0,0,0.2); transition: border-color 0.2s;" onfocus="this.style.borderColor='#38bdf8'; this.style.boxShadow='0 0 8px rgba(56,189,248,0.3)';" onblur="this.style.borderColor='rgba(255,255,255,0.15)'; this.style.boxShadow='inset 0 1px 3px rgba(0,0,0,0.2)';">
                    </td>
                `;

                const factorInput = tr.querySelector('.factor-input');
                const qtyInput = tr.querySelector('.qty-input');
                const baseRcvCell = tr.querySelector('.base-rcv-qty');
                const pendingBaseCell = tr.querySelector('.grn-col-pendingBase');

                factorInput.addEventListener('input', function() {
                    let clean = this.value.replace(/[^0-9.]/g, '');
                    const parts = clean.split('.');
                    if (parts.length > 2) {
                        clean = parts[0] + '.' + parts.slice(1).join('');
                    }
                    if (this.value !== clean) {
                        this.value = clean;
                    }
                    let fVal = parseFloat(clean);
                    if (isNaN(fVal) || fVal <= 0) {
                        fVal = 1;
                    }
                    line.factor = fVal;
                    line.customFactor = fVal;

                    if (pendingBaseCell) {
                        pendingBaseCell.textContent = fmtColVal('pendingBase', (line.maxPending || 0) * fVal);
                    }
                    if (baseRcvCell) {
                        baseRcvCell.textContent = fmtColVal('rcvBase', (line.receiveQty || 0) * fVal);
                    }
                    updateSummary();
                });

                qtyInput.addEventListener('input', function() {
                    let clean = this.value.replace(/[^0-9.]/g, '');
                    const parts = clean.split('.');
                    if (parts.length > 2) {
                        clean = parts[0] + '.' + parts.slice(1).join('');
                    }
                    if (this.value !== clean) {
                        this.value = clean;
                    }
                    let val = parseFloat(clean);
                    if (isNaN(val) || val < 0) {
                        val = 0;
                    }
                    if (val > line.maxPending) {
                        if (typeof showToast === 'function') showToast('تنبيه: الكمية المستلمة تتجاوز الكمية المعلقة (' + line.maxPending + ')', 'warning');
                    }
                    line.receiveQty = val;
                    const curFactor = (line.factor !== undefined && line.factor !== null) ? parseFloat(line.factor) : factor;
                    if (baseRcvCell) {
                        baseRcvCell.textContent = fmtColVal('rcvBase', val * curFactor);
                    }
                    updateSummary();
                });

                const whSelect = tr.querySelector('.wh-select');
                whSelect.addEventListener('change', function() {
                    line.warehouseId = this.value;
                });

                const notesInput = tr.querySelector('.notes-input');
                if (notesInput) {
                    notesInput.addEventListener('input', function() {
                        line.notes = this.value;
                    });
                }

                tbody.appendChild(tr);
            });
                // Update summary details
            updateSummary();

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
            if (typeof allPurchases !== 'undefined' && allPurchases) {
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
                // احسب عامل التحويل من بيانات السطر أو الصنف
                let lineFactor = 1;
                if (line.factor !== undefined && parseFloat(line.factor) > 0) {
                    lineFactor = parseFloat(line.factor);
                } else if (line.customFactor !== undefined && parseFloat(line.customFactor) > 0) {
                    lineFactor = parseFloat(line.customFactor);
                } else if (typeof itemsDb !== 'undefined') {
                    const itemObj = itemsDb.find(i => String(i.id) === String(line.itemId) || i.name === line.itemId);
                    if (itemObj) {
                        const uName = String(line.unitName || line.unit || '').trim();
                        if (itemObj.units && Array.isArray(itemObj.units)) {
                            const u = itemObj.units.find(x => (x.name && x.name.trim() === uName) || (x.unitName && x.unitName.trim() === uName) || (x.id && String(x.id) === String(line.unitId)));
                            if (u && (u.factor || u.equiv)) lineFactor = parseFloat(u.factor || u.equiv) || 1;
                        } else if (itemObj.unitsArray && Array.isArray(itemObj.unitsArray)) {
                            const u = itemObj.unitsArray.find(x => (x.name && x.name.trim() === uName) || (x.unitName && x.unitName.trim() === uName));
                            if (u && (u.factor || u.equiv)) lineFactor = parseFloat(u.factor || u.equiv) || 1;
                        }
                    }
                }

                const baseQtyReceived = (parseFloat(line.receiveQty) || 0) * lineFactor;

                // Negative (Take out of PENDING)
                newItems.push({
                    itemId: line.itemId,
                    unitId: line.unitId,
                    unitName: line.unitName,
                    quantity: -line.receiveQty,
                    factor: lineFactor,
                    customFactor: lineFactor,
                    customBaseQty: -baseQtyReceived,
                    warehouseId: 'PENDING_RECEIPT',
                    price: line.price || 0,
                    taxRate: 0,
                    notes: line.notes || '',
                    total: -((line.price || 0) * line.receiveQty)
                });
                // Positive (Put into ACTUAL)
                newItems.push({
                    itemId: line.itemId,
                    unitId: line.unitId,
                    unitName: line.unitName,
                    quantity: line.receiveQty,
                    factor: lineFactor,
                    customFactor: lineFactor,
                    customBaseQty: baseQtyReceived,
                    warehouseId: line.warehouseId,
                    price: line.price || 0,
                    taxRate: 0,
                    notes: line.notes || '',
                    total: (line.price || 0) * line.receiveQty
                });
            });

            const firstWh = receivingItems[0].warehouseId;

            // Generate combined notes
            const lineNotesList = receivingItems.map(l => l.notes ? `${getItemName(l.itemId)}: ${l.notes}` : '').filter(Boolean);
            const notesSummary = lineNotesList.length > 0 ? ` (${lineNotesList.join(' | ')})` : '';

            const curUser = (typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo() : { id: 1, name: 'المسؤول', username: 'admin' };

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
                notes: `مذكرة استلام بضاعة للفاتورة ${currentInvoiceId}${notesSummary}`,
                createdBy: curUser.id,
                createdByName: curUser.fullName || curUser.name,
                createdByUsername: curUser.username
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
                            let qtyToRevert = pItem.customBaseQty !== undefined ? parseFloat(pItem.customBaseQty) : ((parseFloat(pItem.quantity) || 0) * (parseFloat(pItem.customFactor || pItem.factor) || 1));
                            itemInv.qty = (parseFloat(itemInv.qty) || parseFloat(itemInv.quantity) || 0) - qtyToRevert;
                            if (itemInv.qty < 0) itemInv.qty = 0;
                        }
                    });
                }
            }

            // Update actual stock in erp_inventory_v1 for new quantities (Feeding Warehouse: Qty * Factor)
            receivingItems.forEach(line => {
                const whObj = warehousesDb.find(w => w.id === line.warehouseId);
                const whName = whObj ? whObj.name : 'المخزن الرئيسي';
                
                let lineFactor = 1;
                if (line.factor !== undefined && parseFloat(line.factor) > 0) {
                    lineFactor = parseFloat(line.factor);
                } else if (line.customFactor !== undefined && parseFloat(line.customFactor) > 0) {
                    lineFactor = parseFloat(line.customFactor);
                }
                const baseQtyToAdd = (parseFloat(line.receiveQty) || 0) * lineFactor;

                // We add to actual stock level
                let itemInv = currentInvData.find(inv => inv.itemId === line.itemId && inv.warehouse === whName);
                if (itemInv) {
                    itemInv.qty = (parseFloat(itemInv.qty) || parseFloat(itemInv.quantity) || 0) + baseQtyToAdd;
                } else {
                    currentInvData.push({
                        itemId: line.itemId,
                        warehouse: whName,
                        qty: baseQtyToAdd,
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
                    freshPurchases[grnIdx].updatedBy = curUser.id;
                    freshPurchases[grnIdx].updatedByName = curUser.fullName || curUser.name;
                    freshPurchases[grnIdx].updatedAt = new Date().toISOString();
                }
                showToast(`تم تعديل الإذن ${editingGRNId} بنجاح`, 'success');
                if (window.logSystemAction) {
                    window.logSystemAction('تعديل استلام بضاعة', 'المشتريات / المخازن', editingGRNId, `تعديل إذن استلام بضاعة رقم ${editingGRNId} لفاتورة المشتريات ${currentInvoiceId}`);
                }
                editingGRNId = null; // Clear edit mode
            } else {
                // New mode: add new GRN
                freshPurchases.push(grnInv);
                showToast(`تم حفظ مذكرة الاستلام وتحديث المخزون الفعلي بنجاح`, 'success');
                if (window.logSystemAction) {
                    window.logSystemAction('استلام بضاعة', 'المشتريات / المخازن', grnInv.id, `إصدار إذن استلام بضاعة رقم ${grnInv.id} لفاتورة المشتريات ${currentInvoiceId}`);
                }
            }

            localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(freshPurchases));
            allPurchases = freshPurchases; // Update local memory reference
            
            // 🔥 CRITICAL: Force immediate cloud sync + Universal Offline Queue for Purchase Receipt
            if (typeof window.pushToCloud === 'function') {
                window.pushToCloud(true);
            }
            try {
                if (!navigator.onLine) {
                    if (window.MagaiesOffline && typeof window.MagaiesOffline.enqueue === 'function') {
                        window.MagaiesOffline.enqueue('purchase_receipt', grnInv, grnInv.id);
                    }
                } else {
                    fetch('api/mirror_sync.php?action=save_purchase_receipt', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(grnInv)
                    }).catch(() => {
                        if (window.MagaiesOffline && typeof window.MagaiesOffline.enqueue === 'function') {
                            window.MagaiesOffline.enqueue('purchase_receipt', grnInv, grnInv.id);
                        }
                    });
                }
            } catch (eOff) {}
            
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

        function renderRecentGRNs(resetCount = true) {
            if (resetCount) receivedDisplayCount = 50;
            
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
                if (tA === tB) return (b.id || "").localeCompare(a.id || "");
                return tB - tA;
            });

            const search = (document.getElementById('search-inv') ? document.getElementById('search-inv').value : '').toLowerCase().trim();
            const filteredGrns = grns.filter(grn => {
                if (!search) return true;
                let supId = grn.customer || grn.customerId || grn.supplierId;
                if (!supId && grn.originalInvoiceId) {
                    const orig = allPurchases.find(p => p.id === grn.originalInvoiceId || p.invoiceNo === grn.originalInvoiceId);
                    if (orig) supId = orig.customer || orig.customerId || orig.supplierId;
                }
                const supName = getSupplierName(supId).toLowerCase();
                const grnId = (grn.id || '').toLowerCase();
                const origId = (grn.originalInvoiceId || '').toLowerCase();
                return grnId.includes(search) || origId.includes(search) || supName.includes(search);
            });
            
            if (currentHubView === 'received') {
                document.getElementById('hub-title-text').innerHTML = `📜 أذونات الاستلام (المستلم)`;
                const counterContainer = document.getElementById('hub-counter-badge');
                counterContainer.innerHTML = '';
                setTimeout(() => {
                    counterContainer.innerHTML = `<span class="counter-animate-abs" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; padding: 6px 30px; border-radius: 40px; font-size: 1.6rem; font-weight: 900; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.5), inset 0 2px 5px rgba(255,255,255,0.3); border: 2px solid rgba(255,255,255,0.2); font-family: 'Outfit', 'Tajawal', sans-serif; white-space: nowrap; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">${filteredGrns.length} إذن مستلم</span>`;
                }, 10);
            }
            
            if (filteredGrns.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; color: #64748b; padding: 20px; font-size: 0.85rem;">
                        لا توجد عمليات استلام مطابقة في النظام.
                    </div>
                `;
                return;
            }

            let html = `
                <table class="pos-grid" style="width: 100%; font-size: 0.8rem; margin-top: 10px;">
                    <thead>
                        <tr>
                            <th style="text-align:right;">رقم الاستلام</th>
                            <th style="text-align:center; width:13%;">الفاتورة الأصلية</th>
                            <th style="text-align:right; width:15%;">المورد</th>
                            <th style="text-align:center;">التاريخ والوقت</th>
                            <th style="text-align:right;">الأصناف المستلمة</th>
                            <th style="text-align:center;">المستودع</th>
                            <th style="text-align:center;">إجمالي القيمة</th>
                            <th style="text-align:center;">بواسطة</th>
                            <th style="text-align:center; width: 120px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            const itemsToRender = filteredGrns.slice(0, receivedDisplayCount);

            itemsToRender.forEach(grn => {
                const isEditingThis = grn.id === editingGRNId;
                const rowBg = isEditingThis ? 'background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.4);' : '';
                const editBadge = isEditingThis ? '<span style="background: #f59e0b; color: #000; padding: 2px 6px; border-radius: 4px; font-size: 0.65rem; font-weight: bold; margin-right: 8px;">قيد التعديل ✏️</span>' : '';

                let supId = grn.customer || grn.customerId || grn.supplierId;
                if (!supId && grn.originalInvoiceId) {
                    const orig = allPurchases.find(p => p.id === grn.originalInvoiceId || p.invoiceNo === grn.originalInvoiceId);
                    if (orig) supId = orig.customer || orig.customerId || orig.supplierId;
                }
                const supName = getSupplierName(supId);

                const positiveItems = (grn.items || []).filter(item => item.quantity > 0);
                let totalValue = 0;
                let grnWarehouses = new Set();

                let itemsSummary = positiveItems.map(item => {
                    const itemObj = itemsDb.find(i => i.id === item.itemId);
                    const name = itemObj ? itemObj.name : 'صنف غير معروف';
                    
                    totalValue += (parseFloat(item.price) || 0) * (parseFloat(item.quantity) || 0);
                    
                    if (item.warehouseId && item.warehouseId !== 'PENDING_RECEIPT') {
                        const whObj = warehousesDb.find(w => w.id === item.warehouseId);
                        if (whObj) grnWarehouses.add(whObj.name);
                        else grnWarehouses.add(item.warehouseId);
                    } else if (grn.warehouse) {
                        const whObj = warehousesDb.find(w => w.id === grn.warehouse);
                        if (whObj) grnWarehouses.add(whObj.name);
                        else grnWarehouses.add(grn.warehouse);
                    }

                    const notePart = item.notes ? ` <span style="color:#38bdf8; font-size:0.75rem; background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.3); padding:1px 6px; border-radius:5px; font-weight:600;">📝 ${item.notes}</span>` : '';
                    return `${name} (${item.quantity} ${item.unitName || 'وحدة'})${notePart}`;
                }).join('<br>');
                
                let warehouseStr = Array.from(grnWarehouses).join('، ') || 'المخزن الرئيسي';

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
                        <td style="text-align:center; color: #fbbf24; font-weight: bold; font-family: 'Outfit'; cursor: pointer;" ondblclick="window.open('purchase.php?id=${grn.originalInvoiceId}', '_blank')" title="انقر مرتين لفتح الفاتورة">${grn.originalInvoiceId}</td>
                        <td style="text-align:right; font-weight: 700; color: #fbbf24;">${supName}</td>
                        <td style="text-align:center; font-family: 'Outfit';">${dateStr}</td>
                        <td style="color:#e2e8f0; line-height: 1.4;">${itemsSummary}</td>
                        <td style="text-align:center; font-weight: 600; color: #94a3b8;">${warehouseStr}</td>
                        <td style="text-align:center; font-family: 'Outfit'; font-weight: 800; color: #fbbf24;">${fmtNum(totalValue)} SDG</td>
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
            
            if (filteredGrns.length > receivedDisplayCount) {
                html += `
                <div style="text-align: center; margin-top: 15px; margin-bottom: 20px;">
                    <button class="btn-action" style="padding: 10px 30px; font-size: 1rem; border-radius: 30px; background: linear-gradient(135deg, #10b981, #059669);" onclick="loadMoreReceived()">عرض 50 إذن آخر ⬇️</button>
                </div>`;
            }
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
                    let qtyToRevert = pItem.customBaseQty !== undefined ? parseFloat(pItem.customBaseQty) : ((parseFloat(pItem.quantity) || 0) * (parseFloat(pItem.customFactor || pItem.factor) || 1));
                    itemInv.qty = (parseFloat(itemInv.qty) || parseFloat(itemInv.quantity) || 0) - qtyToRevert;
                    if (itemInv.qty < 0) itemInv.qty = 0;
                }
            });
            localStorage.setItem('erp_inventory_v1', JSON.stringify(currentInvData));

            const index = allPurchases.findIndex(p => p.id === grnId);
            if (index > -1) {
                allPurchases.splice(index, 1);
                localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));
                if (window.logSystemAction) {
                    window.logSystemAction('حذف إذن استلام بضاعة', 'المشتريات / المخازن', grnId, `تم حذف إذن استلام البضاعة رقم ${grnId}`);
                }
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
            
            // Pre-fill target quantities and factors from this GRN
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
                        if (pItem.customFactor || pItem.factor) {
                            const f = parseFloat(pItem.customFactor || pItem.factor);
                            if (f > 0) {
                                line.factor = f;
                                line.customFactor = f;
                            }
                        }
                        line.warehouseId = pItem.warehouseId;
                        line.notes = pItem.notes || '';
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

        // --- Grid Formatting Context Menu ---
        function showGridContextMenu(e) {
            e.preventDefault();
            const menu = document.getElementById('grid-context-menu');
            menu.style.display = 'block';
            menu.style.left = e.pageX + 'px';
            menu.style.top = e.pageY + 'px';
        }

        document.addEventListener('click', function(e) {
            const menu = document.getElementById('grid-context-menu');
            if (menu && menu.style.display === 'block') {
                menu.style.display = 'none';
            }
        });

        let currentEditingTarget = 'all'; // 'all' or col key

        function openGridSettings(targetCol = 'all') {
            populateColSelectDropdown();
            currentEditingTarget = targetCol;
            document.getElementById('grid-target-col').value = targetCol;
            loadTargetSettingsToUI(targetCol);
            updateGridPreview();
            document.getElementById('grid-settings-modal').style.display = 'flex';
        }

        function populateColSelectDropdown() {
            const sel = document.getElementById('grid-target-col');
            sel.innerHTML = `
                <option value="all">🌐 جميع الأعمدة (ضبط شامل موحد)</option>
                <option value="rcvQty">✍️ 7️⃣ الكمية المستلمة (اكتب هنا) ⭐</option>
                <option value="origQty">1️⃣ كمية الشراء (المرجع)</option>
                <option value="item">2️⃣ اسم الصنف</option>
                <option value="unit">3️⃣ وحدة الشراء</option>
                <option value="remQty">4️⃣ الكمية المتبقية</option>
                <option value="factor">5️⃣ التعادل</option>
                <option value="pendingBase">6️⃣ الكمية بالوحدة الأساسية</option>
                <option value="rcvBase">8️⃣ الكمية بالوحدة المستلمة</option>
                <option value="warehouse">9️⃣ المستودع المُستلِم</option>
            `;
        }

        function onTargetColChange() {
            const target = document.getElementById('grid-target-col').value;
            currentEditingTarget = target;
            loadTargetSettingsToUI(target);
            updateGridPreview();
        }

        function loadTargetSettingsToUI(target) {
            const prefs = getStoredGridPrefs();
            let p = {};
            if (target === 'all') {
                p = prefs.global || DEFAULT_GRID_PREFS.global;
            } else {
                p = (prefs.cols && prefs.cols[target]) ? prefs.cols[target] : {};
                const g = prefs.global || DEFAULT_GRID_PREFS.global;
                if (!p.font) p.font = g.font;
                if (!p.size) p.size = g.size;
                if (!p.weight) p.weight = g.weight;
                if (!p.color) p.color = g.color;
                if (!p.align) p.align = g.align;
                if (p.decimals === undefined) p.decimals = g.decimals;
                if (p.thousands === undefined) p.thousands = g.thousands;
            }

            const currentFont = p.font || 'Tajawal';
            const currentSize = String(p.size || '16');
            const currentWeight = String(p.weight || '700');
            const currentColor = p.color || '#ffffff';

            document.getElementById('grid-font-family').value = currentFont;
            
            const sizeSelect = document.getElementById('grid-font-size');
            sizeSelect.value = currentSize;
            if (!sizeSelect.value) {
                const opt = document.createElement('option');
                opt.value = currentSize;
                opt.text = currentSize + 'px';
                sizeSelect.appendChild(opt);
                sizeSelect.value = currentSize;
            }
            document.getElementById('grid-font-size-custom').value = currentSize;

            document.getElementById('grid-font-weight').value = currentWeight;
            document.getElementById('grid-text-color').value = currentColor;
            document.getElementById('grid-text-color-hex').value = currentColor;
            document.getElementById('grid-decimals').value = (p.decimals !== undefined) ? p.decimals : 0;
            document.getElementById('grid-thousands-sep').checked = (p.thousands !== false);
            document.getElementById('grid-text-align').value = p.align || 'center';
        }

        function syncFontSizeFromSelect() {
            const s = document.getElementById('grid-font-size').value;
            document.getElementById('grid-font-size-custom').value = s;
            updateGridPreview();
        }

        function syncFontSizeFromCustom() {
            const val = parseInt(document.getElementById('grid-font-size-custom').value);
            if (!isNaN(val) && val >= 10 && val <= 48) {
                document.getElementById('grid-font-size').value = String(val);
                updateGridPreview();
            }
        }

        function syncColorFromPicker(val) {
            document.getElementById('grid-text-color-hex').value = val;
            updateGridPreview();
        }

        function syncColorFromHex(val) {
            if (/^#[0-9A-F]{6}$/i.test(val) || /^#[0-9A-F]{3}$/i.test(val)) {
                document.getElementById('grid-text-color').value = val;
                updateGridPreview();
            }
        }

        function setPresetColor(c) {
            document.getElementById('grid-text-color').value = c;
            document.getElementById('grid-text-color-hex').value = c;
            updateGridPreview();
        }

        function updateGridPreview() {
            const p = document.getElementById('grid-preview-box');
            if (!p) return;
            const font = document.getElementById('grid-font-family').value;
            const size = document.getElementById('grid-font-size-custom').value || document.getElementById('grid-font-size').value || '16';
            const weight = document.getElementById('grid-font-weight').value;
            const color = document.getElementById('grid-text-color').value;
            const decimals = parseInt(document.getElementById('grid-decimals').value) || 0;
            const thousands = document.getElementById('grid-thousands-sep').checked;
            const align = document.getElementById('grid-text-align').value;

            let sampleNum = 12500.75;
            let formatted = thousands 
                ? sampleNum.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals })
                : sampleNum.toFixed(decimals);

            if (currentEditingTarget === 'item') formatted = '📦 سيخ 4 لينيا الأسعد';
            else if (currentEditingTarget === 'unit') formatted = 'طن عددي';
            else if (currentEditingTarget === 'warehouse') formatted = 'المخزن الرئيسي';
            else if (currentEditingTarget === 'rcvQty') {
                formatted = '✍️ 250 (الكمية المستلمة)';
                p.style.border = '2px solid #38bdf8';
                p.style.boxShadow = '0 0 15px rgba(56, 189, 248, 0.4)';
            } else {
                p.style.border = '1px solid rgba(255,255,255,0.1)';
                p.style.boxShadow = 'none';
            }

            p.style.fontFamily = `'${font}', 'Tajawal', sans-serif`;
            p.style.fontSize = size + 'px';
            p.style.fontWeight = weight;
            p.style.color = color;
            p.style.textAlign = align;
            p.textContent = formatted;
        }

        async function saveGridSettingsToDB(prefs) {
            try {
                const u = JSON.parse(localStorage.getItem('user') || '{}');
                const userTargets = new Set();
                if (u.id) userTargets.add(String(u.id));
                if (u.username) userTargets.add(String(u.username));
                userTargets.add('default_user');

                const savePromises = Array.from(userTargets).map(uid => 
                    fetch('api/save_user_settings.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            user_id: uid,
                            setting_key: 'erp_grn_col_prefs_v2',
                            setting_value: prefs
                        })
                    })
                );

                await Promise.all(savePromises);
                console.log('✅ Grid preferences permanently saved to MySQL database for targets:', Array.from(userTargets));
            } catch(e) {
                console.error('❌ Error saving grid prefs to DB:', e);
            }
        }

        async function saveGridSettings() {
            const prefs = getStoredGridPrefs();
            const font = document.getElementById('grid-font-family').value;
            const size = document.getElementById('grid-font-size-custom').value || document.getElementById('grid-font-size').value || '16';
            const weight = document.getElementById('grid-font-weight').value;
            const color = document.getElementById('grid-text-color').value;
            const decimals = parseInt(document.getElementById('grid-decimals').value) || 0;
            const thousands = document.getElementById('grid-thousands-sep').checked;
            const align = document.getElementById('grid-text-align').value;

            const targetObj = { font, size, weight, color, decimals, thousands, align };

            if (currentEditingTarget === 'all') {
                prefs.global = targetObj;
            } else {
                if (!prefs.cols) prefs.cols = {};
                prefs.cols[currentEditingTarget] = targetObj;
            }

            localStorage.setItem('erp_grn_col_prefs_v2', JSON.stringify(prefs));
            document.getElementById('grid-settings-modal').style.display = 'none';
            renderReceiptLines();

            // Permanent database persistence
            await saveGridSettingsToDB(prefs);
            showToast('تم حفظ التنسيق نهائياً في قاعدة البيانات بنجاح! 💾', 'success');
        }

        async function applyCurrentToAllCols() {
            if (!confirm('هل تريد تعميم هذه الإعدادات على جميع أعمدة الجدول معاً وحفظها نهائياً؟')) return;
            const font = document.getElementById('grid-font-family').value;
            const size = document.getElementById('grid-font-size-custom').value || document.getElementById('grid-font-size').value || '16';
            const weight = document.getElementById('grid-font-weight').value;
            const color = document.getElementById('grid-text-color').value;
            const decimals = parseInt(document.getElementById('grid-decimals').value) || 0;
            const thousands = document.getElementById('grid-thousands-sep').checked;
            const align = document.getElementById('grid-text-align').value;

            const targetObj = { font, size, weight, color, decimals, thousands, align };
            const prefs = {
                global: targetObj,
                cols: {
                    origQty:     { ...targetObj },
                    item:        { ...targetObj, align: align === 'center' ? 'right' : align },
                    unit:        { ...targetObj },
                    remQty:      { ...targetObj },
                    factor:      { ...targetObj },
                    pendingBase: { ...targetObj },
                    rcvQty:      { ...targetObj },
                    rcvBase:     { ...targetObj },
                    warehouse:   { ...targetObj }
                }
            };
            localStorage.setItem('erp_grn_col_prefs_v2', JSON.stringify(prefs));
            document.getElementById('grid-settings-modal').style.display = 'none';
            renderReceiptLines();

            // Permanent database persistence
            await saveGridSettingsToDB(prefs);
            showToast('تم تعميم الضبط وحفظه نهائياً في قاعدة البيانات بنجاح! 💾', 'success');
        }

        async function resetGridSettingsToDefault() {
            if (!confirm('هل تريد استعادة الضبط الافتراضي لجميع الأعمدة؟')) return;
            localStorage.removeItem('erp_grn_col_prefs_v2');
            
            const defaultPrefs = DEFAULT_GRID_PREFS;
            await saveGridSettingsToDB(defaultPrefs);

            document.getElementById('grid-settings-modal').style.display = 'none';
            renderReceiptLines();
            showToast('تمت استعادة الضبط الافتراضي وحفظه في قاعدة البيانات بنجاح!', 'info');
        }
    </script>

    <!-- Context Menu HTML -->
    <div id="grid-context-menu" style="display: none; position: absolute; z-index: 100000; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); border: 1px solid #38bdf8; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.5); padding: 5px; min-width: 150px;">
        <div onclick="openGridSettings()" style="padding: 10px 15px; color: #fff; cursor: pointer; border-radius: 5px; transition: background 0.2s; font-size: 0.9rem;" onmouseover="this.style.background='rgba(56,189,248,0.2)'" onmouseout="this.style.background='none'">
            🎨 خيارات وتنسيق الجدول
        </div>
    </div>

    <!-- Grid Settings Modal -->
    <div class="modal-overlay" id="grid-settings-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(8px); z-index: 100001; justify-content: center; align-items: center;">
        <div class="modal-card" style="width: 550px; max-width: 95vw; background: #0f172a; border: 1.5px solid #38bdf8; border-radius: 16px; padding: 25px; box-shadow: 0 15px 40px rgba(0,0,0,0.7);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 12px;">
                <h3 style="color: #38bdf8; margin: 0; font-family: 'Tajawal', sans-serif; font-size: 1.25rem; display: flex; align-items: center; gap: 8px;">
                    <span>⚙️</span> خيارات وتنسيق خط وأرقام الجدول
                </h3>
                <button onclick="document.getElementById('grid-settings-modal').style.display='none'" style="background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 1.4rem; padding: 0 5px;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94a3b8'">✕</button>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 14px; font-family: 'Tajawal', sans-serif;">
                
                <!-- Target Column Selector -->
                <div style="background: rgba(30, 41, 59, 0.7); border: 1.5px solid rgba(56, 189, 248, 0.4); border-radius: 12px; padding: 12px 15px;">
                    <label style="color: #38bdf8; font-size: 0.9rem; font-weight: 800; display: block; margin-bottom: 6px;">🎯 اختر نطاق التنسيق (العمود المراد تعديل خطه أو كل الأعمدة):</label>
                    <select id="grid-target-col" class="v-input" style="width: 100%; height: 44px; border-radius: 8px; font-size: 1rem; font-weight: 700; background: #0f172a; color: #fff;" onchange="onTargetColChange()">
                        <!-- Populated by JS -->
                    </select>
                </div>

                <!-- Live Preview Box -->
                <div style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; padding: 12px; text-align: center;">
                    <div style="color: #94a3b8; font-size: 0.8rem; margin-bottom: 6px;">معاينة حية لشكل النص / الرقم / حقل الإدخال:</div>
                    <div id="grid-preview-box" style="background: rgba(15, 23, 42, 0.95); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; min-height: 50px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; color: #fff; padding: 5px 15px; transition: all 0.2s ease;">
                        12,500
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="color: #94a3b8; font-size: 0.85rem; display: block; margin-bottom: 5px;">نوع الخط</label>
                        <select id="grid-font-family" class="v-input" style="width: 100%; height: 42px; border-radius: 8px;" onchange="updateGridPreview()">
                            <option value="Tajawal">Tajawal (تجوال)</option>
                            <option value="Cairo">Cairo (كايرو)</option>
                            <option value="Outfit">Outfit (أوتفيت)</option>
                            <option value="Almarai">Almarai (المراعي)</option>
                            <option value="Arial">Arial (عادي)</option>
                        </select>
                    </div>

                    <div>
                        <label style="color: #94a3b8; font-size: 0.85rem; display: block; margin-bottom: 5px;">حجم الخط (px)</label>
                        <div style="display: flex; gap: 6px; align-items: center;">
                            <select id="grid-font-size" class="v-input" style="flex: 1; height: 42px; border-radius: 8px;" onchange="syncFontSizeFromSelect()">
                                <option value="12">12px (صغير جداً)</option>
                                <option value="13">13px</option>
                                <option value="14">14px (صغير)</option>
                                <option value="15">15px</option>
                                <option value="16">16px (متوسط - افتراضي)</option>
                                <option value="17">17px (فوق المتوسط)</option>
                                <option value="18">18px (كبير)</option>
                                <option value="20">20px (كبير جداً)</option>
                                <option value="22">22px (ضخم)</option>
                                <option value="24">24px (عريض جداً)</option>
                                <option value="26">26px</option>
                                <option value="28">28px</option>
                                <option value="30">30px (كبير ممتاز)</option>
                                <option value="32">32px</option>
                            </select>
                            <input type="number" id="grid-font-size-custom" min="10" max="48" style="width: 65px; height: 42px; border-radius: 8px; text-align: center; font-weight: 700; background: #0f172a; color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4);" placeholder="px" oninput="syncFontSizeFromCustom()" title="أو اكتب الحجم مباشرة">
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="color: #94a3b8; font-size: 0.85rem; display: block; margin-bottom: 5px;">سُمك الخط (Font Weight)</label>
                        <select id="grid-font-weight" class="v-input" style="width: 100%; height: 42px; border-radius: 8px;" onchange="updateGridPreview()">
                            <option value="400">عادي (Normal 400)</option>
                            <option value="600">متوسط (Medium 600)</option>
                            <option value="700">سميك (Bold 700)</option>
                            <option value="800">سميك جداً (Extra Bold 800)</option>
                            <option value="900">عريض للغاية (Black 900)</option>
                        </select>
                    </div>

                    <div>
                        <label style="color: #94a3b8; font-size: 0.85rem; display: block; margin-bottom: 5px;">محاذاة النص</label>
                        <select id="grid-text-align" class="v-input" style="width: 100%; height: 42px; border-radius: 8px;" onchange="updateGridPreview()">
                            <option value="center">في المنتصف (Center)</option>
                            <option value="right">على اليمين (Right)</option>
                            <option value="left">على اليسار (Left)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="color: #94a3b8; font-size: 0.85rem; display: block; margin-bottom: 5px;">لون النص / الخط</label>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="color" id="grid-text-color" style="width: 48px; height: 42px; padding: 2px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: #1e293b; cursor: pointer;" value="#ffffff" onchange="syncColorFromPicker(this.value)">
                        <input type="text" id="grid-text-color-hex" style="width: 90px; height: 42px; border-radius: 8px; text-align: center; font-family: monospace; font-weight: 700; background: #0f172a; color: #fff; border: 1px solid rgba(255,255,255,0.2);" value="#ffffff" oninput="syncColorFromHex(this.value)" title="كود اللون Hex">
                        <!-- Quick Presets -->
                        <div style="display: flex; gap: 6px; flex: 1;">
                            <button type="button" onclick="setPresetColor('#ffffff')" style="flex:1; height:42px; background:#ffffff; border:1px solid #fff; border-radius:6px; cursor:pointer;" title="أبيض ناصع"></button>
                            <button type="button" onclick="setPresetColor('#facc15')" style="flex:1; height:42px; background:#facc15; border:1px solid #d97706; border-radius:6px; cursor:pointer;" title="أصفر فاقع"></button>
                            <button type="button" onclick="setPresetColor('#38bdf8')" style="flex:1; height:42px; background:#38bdf8; border:1px solid #0284c7; border-radius:6px; cursor:pointer;" title="سماوي مريح"></button>
                            <button type="button" onclick="setPresetColor('#34d399')" style="flex:1; height:42px; background:#34d399; border:1px solid #059669; border-radius:6px; cursor:pointer;" title="زمردي هادئ"></button>
                            <button type="button" onclick="setPresetColor('#fb7185')" style="flex:1; height:42px; background:#fb7185; border:1px solid #e11d48; border-radius:6px; cursor:pointer;" title="وردي مشرق"></button>
                            <button type="button" onclick="setPresetColor('#c084fc')" style="flex:1; height:42px; background:#c084fc; border:1px solid #9333ea; border-radius:6px; cursor:pointer;" title="بنفسجي هادئ"></button>
                            <button type="button" onclick="setPresetColor('#f97316')" style="flex:1; height:42px; background:#f97316; border:1px solid #ea580c; border-radius:6px; cursor:pointer;" title="برتقالي مميز"></button>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="color: #94a3b8; font-size: 0.85rem; display: block; margin-bottom: 5px;">الفاصلة العشرية (المنازل)</label>
                        <select id="grid-decimals" class="v-input" style="width: 100%; height: 42px; border-radius: 8px;" onchange="updateGridPreview()">
                            <option value="0">بدون كسور (0)</option>
                            <option value="1">منزلة واحدة (1.0)</option>
                            <option value="2">منزلتان (1.00)</option>
                            <option value="3">3 منازل (1.000)</option>
                        </select>
                    </div>

                    <div style="display: flex; flex-direction: column; justify-content: flex-end;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; height: 42px; background: rgba(30,41,59,0.5); padding: 0 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.08);">
                            <input type="checkbox" id="grid-thousands-sep" checked style="width: 18px; height: 18px; cursor: pointer;" onchange="updateGridPreview()">
                            <span style="color: #fff; font-size: 0.85rem; font-weight: 600;">الفاصلة الألفية (1,000)</span>
                        </label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 10px;">
                    <div style="display: flex; gap: 10px;">
                        <button class="btn-action" style="flex: 2; padding: 12px; font-size: 1.05rem; font-weight: 800; border-radius: 10px; background: linear-gradient(135deg, #10b981, #059669); border: none; color: #fff; cursor: pointer;" onclick="saveGridSettings()">
                            💾 حفظ للعمود المحدد
                        </button>
                        <button type="button" class="btn-action" style="flex: 2; padding: 12px; font-size: 0.95rem; font-weight: 700; border-radius: 10px; background: linear-gradient(135deg, #0284c7, #0369a1); border: none; color: #fff; cursor: pointer;" onclick="applyCurrentToAllCols()" title="نسخ هذا التنسيق وتطبيقه على كل الأعمدة معاً">
                            🌐 تعميم على كل الأعمدة
                        </button>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="button" style="flex: 1; padding: 10px; font-size: 0.85rem; border-radius: 8px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; cursor: pointer;" onclick="resetGridSettingsToDefault()">
                            ↺ استعادة الافتراضي
                        </button>
                        <button type="button" class="btn-action btn-cancel" style="flex: 1; padding: 10px; font-size: 0.9rem; border-radius: 8px;" onclick="document.getElementById('grid-settings-modal').style.display='none'">
                            إغلاق
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════════════════════
         WORLD-CLASS GRN ROYAL GLASS WAVE, KINETIC ICONS, CURSOR ENGINE & BUG FIXES
         ════════════════════════════════════════════════════════════════════════════ -->
    <style id="grn-world-class-upgrade-styles">
        @keyframes grnGlassSweep {
            0%   { left: -120%; opacity: 0; }
            30%  { opacity: 0.65; }
            70%  { opacity: 0.65; }
            100% { left: 160%; opacity: 0; }
        }
        @keyframes grnIconFloatGlow {
            0%, 100% { transform: translateY(0) scale(1); filter: drop-shadow(0 0 6px rgba(56, 189, 248, 0.55)); }
            50%      { transform: translateY(-2px) scale(1.09); filter: drop-shadow(0 0 14px rgba(16, 185, 129, 0.9)); }
        }
        @keyframes grnButtonRippleWave {
            0%   { box-shadow: 0 0 0 0 rgba(56, 189, 248, 0.45), 0 10px 25px rgba(2, 132, 199, 0.35); }
            70%  { box-shadow: 0 0 0 12px rgba(56, 189, 248, 0), 0 14px 32px rgba(16, 185, 129, 0.45); }
            100% { box-shadow: 0 0 0 0 rgba(56, 189, 248, 0), 0 10px 25px rgba(2, 132, 199, 0.35); }
        }

        /* 1. Borderless & Floating Unit Cell ("الوحدة تضيع حوافها وتكون أجمل") */
        .grn-col-unit {
            border: 1px solid transparent !important;
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.13), rgba(99, 102, 241, 0.10)) !important;
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.16), 0 4px 14px rgba(0, 0, 0, 0.18) !important;
            border-radius: 12px !important;
            backdrop-filter: blur(12px);
            position: relative;
            overflow: hidden;
            letter-spacing: 0.4px;
        }

        /* 2. Soft Borderless Item Cell & Read-Only Cells */
        .grn-col-item,
        .grn-col-origQty,
        .grn-col-qty,
        .grn-col-pendingBase,
        .grn-col-baseQty {
            border: 1px solid rgba(255, 255, 255, 0.04) !important;
            border-radius: 12px !important;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        /* 3. Universal Cursor/Hover Effect: Dissolve Borders + Expand Size + Glass Wave */
        .grn-col-origQty,
        .grn-col-item,
        .grn-col-unit,
        .grn-col-qty,
        .grn-col-pendingBase,
        .grn-col-baseQty,
        .factor-input,
        .qty-input,
        .wh-select,
        .notes-input,
        .v-input {
            transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1),
                        border-color 0.22s ease,
                        box-shadow 0.28s ease,
                        background 0.28s ease !important;
            will-change: transform, box-shadow;
        }

        .grn-col-origQty:hover,
        .grn-col-item:hover,
        .grn-col-unit:hover,
        .grn-col-qty:hover,
        .grn-col-pendingBase:hover,
        .grn-col-baseQty:hover,
        .factor-input:hover,
        .qty-input:hover,
        .wh-select:hover,
        .notes-input:hover,
        .v-input:hover,
        .factor-input:focus,
        .qty-input:focus,
        .wh-select:focus,
        .notes-input:focus,
        .v-input:focus {
            border-color: transparent !important;
            outline: none !important;
            transform: scale(1.055) translateY(-2px) !important;
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.98)) !important;
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.5),
                        0 0 22px rgba(56, 189, 248, 0.38),
                        inset 0 1px 2px rgba(255, 255, 255, 0.28) !important;
            z-index: 15 !important;
        }

        .write-indicator-box:focus,
        .write-indicator-box.focused {
            animation: none !important;
            border-color: transparent !important;
            transform: scale(1.065) translateY(-2px) !important;
            box-shadow: 0 14px 32px rgba(0, 0, 0, 0.55),
                        0 0 26px rgba(16, 185, 129, 0.65),
                        inset 0 1px 3px rgba(255, 255, 255, 0.35) !important;
            background: linear-gradient(135deg, rgba(6, 78, 59, 0.55), rgba(15, 23, 42, 0.98)) !important;
        }

        /* Luminous Glass Wave Sweep on Hover */
        .grn-col-item::after,
        .grn-col-unit::after,
        .grn-col-origQty::after,
        .grn-col-qty::after,
        .grn-col-pendingBase::after,
        .grn-col-baseQty::after,
        .btn-action::after {
            content: "";
            position: absolute;
            top: 0;
            left: -120%;
            width: 55%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.26), transparent);
            transform: skewX(-24deg);
            pointer-events: none;
        }
        .grn-col-item:hover::after,
        .grn-col-unit:hover::after,
        .grn-col-origQty:hover::after,
        .grn-col-qty:hover::after,
        .grn-col-pendingBase:hover::after,
        .grn-col-baseQty:hover::after,
        .btn-action:hover::after {
            animation: grnGlassSweep 0.75s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* 4. Kinetic Optical Icons — Expand Size & Glow on Hover */
        .grn-icon-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 9px;
            background: radial-gradient(circle at 30% 30%, rgba(56, 189, 248, 0.32), rgba(14, 165, 233, 0.08));
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.28), 0 4px 12px rgba(56, 189, 248, 0.25);
            transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1),
                        box-shadow 0.28s ease,
                        background 0.28s ease;
            animation: grnIconFloatGlow 3.2s infinite ease-in-out;
            flex-shrink: 0;
            cursor: pointer;
        }
        .grn-icon-badge:hover,
        *:hover > .grn-icon-badge {
            transform: scale(1.28) rotate(-6deg) !important;
            background: radial-gradient(circle at 30% 30%, rgba(16, 185, 129, 0.55), rgba(56, 189, 248, 0.28));
            box-shadow: 0 0 22px rgba(56, 189, 248, 0.85), inset 0 1px 3px rgba(255, 255, 255, 0.5);
        }

        /* 5. Action Buttons & Quick Mini-Buttons */
        .btn-action {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.14) !important;
            backdrop-filter: blur(12px);
            transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1),
                        box-shadow 0.28s ease,
                        border-color 0.22s ease !important;
        }
        .btn-action:hover {
            transform: translateY(-3px) scale(1.055) !important;
            border-color: transparent !important;
            animation: grnButtonRippleWave 1.5s infinite;
        }
        .grn-row-mini-btn {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            border: 1px solid transparent;
            background: rgba(56, 189, 248, 0.14);
            color: #38bdf8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: inset 0 1px 1px rgba(255,255,255,0.2);
            flex-shrink: 0;
        }
        .grn-row-mini-btn:hover {
            transform: scale(1.26) translateY(-1px);
            background: linear-gradient(135deg, #38bdf8, #0284c7);
            color: #fff;
            box-shadow: 0 0 16px rgba(56, 189, 248, 0.75);
        }
        .grn-row-mini-btn.danger {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
        }
        .grn-row-mini-btn.danger:hover {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
            box-shadow: 0 0 16px rgba(239, 68, 68, 0.75);
        }

        /* 6. Well-Spaced Date & Time Pills ("إبعاد التاريخ والوقت ليكونا أوضح") */
        .grn-datetime-spaced {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            direction: ltr;
            flex-wrap: wrap;
        }
        .grn-date-chip,
        .grn-time-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 10px;
            font-family: 'Outfit', 'Tajawal', sans-serif;
            font-weight: 800;
            font-size: 0.92rem;
            border: 1px solid transparent;
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease;
        }
        .grn-date-chip {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.16), rgba(2, 132, 199, 0.10));
            color: #e0f2fe;
            box-shadow: inset 0 1px 1px rgba(255,255,255,0.15);
        }
        .grn-time-chip {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.18), rgba(99, 102, 241, 0.12));
            color: #f3e8ff;
            box-shadow: inset 0 1px 1px rgba(255,255,255,0.15);
        }
        .grn-date-chip:hover,
        .grn-time-chip:hover {
            transform: scale(1.1) translateY(-1px);
            border-color: transparent !important;
            box-shadow: 0 6px 18px rgba(56, 189, 248, 0.35);
        }
        :root.light-theme .grn-date-chip { background: rgba(2, 132, 199, 0.12) !important; color: #0369a1 !important; }
        :root.light-theme .grn-time-chip { background: rgba(124, 58, 237, 0.12) !important; color: #6d28d9 !important; }

        /* 7. Active Row Highlight & Progress Bar */
        .pos-grid tbody tr.grn-active-row {
            background: linear-gradient(90deg, rgba(56, 189, 248, 0.12), rgba(16, 185, 129, 0.10)) !important;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(56, 189, 248, 0.28) !important;
            transform: translateY(-2px);
        }
        .grn-meta-bar > div {
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            border-radius: 12px;
            padding: 6px 12px;
        }
        .grn-meta-bar > div:hover {
            transform: scale(1.04) translateY(-2px);
            background: rgba(56, 189, 248, 0.08);
            border-color: transparent !important;
        }
    </style>

    <script>
    (function() {
        // 1. Format Date & Time with Clear Visual Spacing
        window.formatSpacedDateTime = function(rawDate, fallbackTimeStr) {
            if (!rawDate) return '<span class="grn-date-chip">📅 —</span>';
            const d = new Date(rawDate);
            if (isNaN(d.getTime())) {
                return `<span class="grn-date-chip">📅 ${rawDate}</span>`;
            }
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            const datePart = `${yyyy}-${mm}-${dd}`;

            let hours = d.getHours();
            let mins = d.getMinutes();
            // If invoice date is midnight-only (00:00), check if rawDate had time or use readable format
            const hasTime = (hours !== 0 || mins !== 0 || String(rawDate).includes(':'));
            const timePart = hasTime
                ? d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true })
                : (fallbackTimeStr || '12:00 PM');

            return `<span class="grn-datetime-spaced" title="التاريخ والوقت مفصولان بوضوح">
                <span class="grn-date-chip"><span class="grn-icon-badge" style="width:22px;height:22px;font-size:0.75rem;">📅</span> ${datePart}</span>
                <span class="grn-time-chip"><span class="grn-icon-badge" style="width:22px;height:22px;font-size:0.75rem;">🕒</span> ${timePart}</span>
            </span>`;
        };

        // 2. Inject Quick Action Toolbar Buttons (Fill All, Zero All, Print GRN, Keyboard Hint)
        function enhanceEditorToolbar() {
            const titleBar = document.querySelector('#view-editor .editor-table-box .neo-card-title > div:last-child');
            if (!titleBar || document.getElementById('grn-btn-fill-all')) return;

            const fillBtn = document.createElement('button');
            fillBtn.id = 'grn-btn-fill-all';
            fillBtn.className = 'btn-action';
            fillBtn.style.cssText = 'padding: 9px 14px; font-size: 0.88rem; border-radius: 9px; background: rgba(16, 185, 129, 0.16); color: #34d399; font-weight: 800; display: flex; align-items: center; gap: 6px;';
            fillBtn.innerHTML = '<span class="grn-icon-badge" style="width:24px;height:24px;">⚡</span> استلام الكل';
            fillBtn.title = 'تعبئة كامل الكميات المتبقية لجميع الأصناف بضغطة واحدة';
            fillBtn.onclick = function() {
                receiptLines.forEach(l => { l.receiveQty = l.maxPending; });
                renderReceiptLines();
                showToast('تم تعبئة كامل الكميات المتبقية لجميع الأصناف ⚡', 'success');
            };

            const zeroBtn = document.createElement('button');
            zeroBtn.className = 'btn-action';
            zeroBtn.style.cssText = 'padding: 9px 14px; font-size: 0.88rem; border-radius: 9px; background: rgba(245, 158, 11, 0.15); color: #fbbf24; font-weight: 800; display: flex; align-items: center; gap: 6px;';
            zeroBtn.innerHTML = '<span class="grn-icon-badge" style="width:24px;height:24px;">🔄</span> تصفير';
            zeroBtn.title = 'تصفير الكميات المستلمة لإدخال الأصناف المستلمة فعلياً فقط';
            zeroBtn.onclick = function() {
                receiptLines.forEach(l => { l.receiveQty = 0; });
                renderReceiptLines();
                showToast('تم تصفير الكميات لإدخال الاستلام الجزئي يدوياً', 'info');
            };

            titleBar.insertBefore(zeroBtn, titleBar.children[1] || null);
            titleBar.insertBefore(fillBtn, zeroBtn);
        }

        // 3. Upgrade renderPendingInvoices to show spaced Date & Time and Kinetic Icons
        const _origRenderPending = window.renderPendingInvoices;
        window.renderPendingInvoices = function(resetCount = true) {
            _origRenderPending(resetCount);
            const rows = document.querySelectorAll('#invoices-tbody tr');
            rows.forEach(tr => {
                const dateTd = tr.children[1];
                const invIdTd = tr.children[0];
                if (invIdTd && dateTd && pendingMap[invIdTd.textContent.trim()]) {
                    const invId = invIdTd.textContent.trim();
                    const origInv = (allPurchases || []).find(p => (p.invoiceNo || p.id) === invId) || {};
                    const rawDt = origInv.savedAt || origInv.createdAt || pendingMap[invId].date;
                    dateTd.innerHTML = window.formatSpacedDateTime(rawDt);
                }
                const actionBtn = tr.querySelector('button.btn-action');
                if (actionBtn && !actionBtn.dataset.enhanced) {
                    actionBtn.dataset.enhanced = '1';
                    actionBtn.innerHTML = `<span class="grn-icon-badge" style="width:22px;height:22px;margin-left:6px;">📥</span> استلام البضاعة`;
                }
            });
        };

        // 4. Upgrade openReceiptEditor to format Spaced Date & Time and auto-focus first Qty input
        const _origOpenEditor = window.openReceiptEditor;
        window.openReceiptEditor = function(invId) {
            _origOpenEditor(invId);
            enhanceEditorToolbar();
            const origInv = (allPurchases || []).find(p => (p.invoiceNo || p.id) === invId) || {};
            const invData = pendingMap[invId] || {};
            const rawDt = origInv.savedAt || origInv.createdAt || invData.date;
            const lblDate = document.getElementById('lbl-date');
            if (lblDate) {
                lblDate.innerHTML = window.formatSpacedDateTime(rawDt);
            }
            setTimeout(() => {
                const firstQty = document.querySelector('#items-tbody .qty-input');
                if (firstQty) {
                    firstQty.focus();
                    firstQty.select();
                }
            }, 80);
        };

        // 5. Upgrade renderReceiptLines with:
        //    - Live Remaining Qty update on typing
        //    - Borderless Unit pill with unit icon
        //    - Split-Warehouse button (+) & Remove-Split button (✕) on each row
        //    - 2D Keyboard & Cursor Navigation (Enter / ArrowUp / ArrowDown / ArrowLeft / ArrowRight / F10)
        const _origRenderLines = window.renderReceiptLines;
        window.renderReceiptLines = function() {
            _origRenderLines();
            const tbody = document.getElementById('items-tbody');
            if (!tbody) return;

            const rows = Array.from(tbody.querySelectorAll('tr'));
            rows.forEach((tr, rowIdx) => {
                const line = receiptLines[rowIdx];
                if (!line) return;

                // A. Enhance Item cell with kinetic icon + Split Warehouse button
                const itemBox = tr.querySelector('.grn-col-item:not([style*="display:none"])');
                if (itemBox) {
                    const cleanName = getItemName(line.itemId);
                    const splitActionBtn = line.isSplit
                        ? `<button type="button" class="grn-row-mini-btn danger" title="إلغاء تقسيم هذا السطر" onclick="removeSplit(event, '${line.id}')">✕</button>`
                        : `<button type="button" class="grn-row-mini-btn" title="تقسيم استلام هذا الصنف على أكثر من مخزن" onclick="splitLine(event, '${line.id}')">➕</button>`;

                    itemBox.style.justifyContent = 'space-between';
                    itemBox.style.gap = '8px';
                    itemBox.innerHTML = `
                        <span style="display:inline-flex;align-items:center;gap:8px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <span class="grn-icon-badge">📦</span>
                            <span style="overflow:hidden;text-overflow:ellipsis;">${cleanName}${line.isSplit ? ' <small style="color:#38bdf8;">(مقسّم)</small>' : ''}</span>
                        </span>
                        ${splitActionBtn}
                    `;
                }

                // B. Enhance Unit cell ("الوحدة تضيع حوافها وتكون أجمل")
                const unitBox = tr.querySelector('.grn-col-unit');
                if (unitBox) {
                    const uTxt = line.unitName || line.unit || 'وحدة';
                    unitBox.innerHTML = `<span style="display:inline-flex;align-items:center;gap:6px;"><span class="grn-icon-badge" style="width:24px;height:24px;font-size:0.8rem;">🏷️</span> <span>${uTxt}</span></span>`;
                }

                // C. Live Remaining Qty calculation on typing in qtyInput
                const remBox = tr.querySelector('.grn-col-qty');
                const qtyInput = tr.querySelector('.qty-input');
                const updateLiveRemaining = () => {
                    if (!remBox) return;
                    const groupSplits = receiptLines.filter(x => x.lineGroup === line.lineGroup);
                    const totalAssigned = groupSplits.reduce((acc, x) => acc + (parseFloat(x.receiveQty) || 0), 0);
                    const rem = Math.max(0, Math.round((line.maxPending - totalAssigned) * 100) / 100);
                    remBox.textContent = fmtColVal('remQty', rem);
                    if (rem === 0) {
                        remBox.style.color = '#34d399';
                        remBox.title = 'تم استلام كامل الكمية المتبقية بالكامل ✅';
                    } else {
                        remBox.style.color = '#fbbf24';
                        remBox.title = 'الكمية المتبقية بعد هذا الاستلام: ' + rem;
                    }
                };
                updateLiveRemaining();
                if (qtyInput) {
                    qtyInput.addEventListener('input', updateLiveRemaining);
                }

                // D. Highlight Active Row & Enable Smooth 2D Keyboard Cursor Navigation
                const interactiveInputs = Array.from(tr.querySelectorAll('.factor-input, .qty-input, .wh-select, .notes-input'));
                interactiveInputs.forEach((inp, colIdx) => {
                    inp.addEventListener('focus', () => {
                        rows.forEach(r => r.classList.remove('grn-active-row'));
                        tr.classList.add('grn-active-row');
                    });
                    inp.addEventListener('keydown', (ev) => {
                        if (ev.key === 'Enter') {
                            ev.preventDefault();
                            // From Qty input, pressing Enter jumps directly to next row's Qty input for ultra-fast entry!
                            if (inp.classList.contains('qty-input')) {
                                const nextRow = rows[rowIdx + 1];
                                if (nextRow) {
                                    const nextQty = nextRow.querySelector('.qty-input');
                                    if (nextQty) { nextQty.focus(); nextQty.select(); return; }
                                }
                            }
                            const nextInRow = interactiveInputs[colIdx + 1];
                            if (nextInRow) {
                                nextInRow.focus();
                                if (nextInRow.select) nextInRow.select();
                            } else if (rows[rowIdx + 1]) {
                                const firstNext = rows[rowIdx + 1].querySelector('.qty-input') || rows[rowIdx + 1].querySelector('.factor-input');
                                if (firstNext) { firstNext.focus(); if (firstNext.select) firstNext.select(); }
                            }
                        } else if (ev.key === 'ArrowDown') {
                            if (inp.tagName === 'SELECT') return;
                            ev.preventDefault();
                            const nextRow = rows[rowIdx + 1];
                            if (nextRow) {
                                const target = nextRow.querySelectorAll('.factor-input, .qty-input, .wh-select, .notes-input')[colIdx];
                                if (target) { target.focus(); if (target.select) target.select(); }
                            }
                        } else if (ev.key === 'ArrowUp') {
                            if (inp.tagName === 'SELECT') return;
                            ev.preventDefault();
                            const prevRow = rows[rowIdx - 1];
                            if (prevRow) {
                                const target = prevRow.querySelectorAll('.factor-input, .qty-input, .wh-select, .notes-input')[colIdx];
                                if (target) { target.focus(); if (target.select) target.select(); }
                            }
                        }
                    });
                });
            });
        };

        // 6. Upgrade renderRecentGRNs to format Spaced Date & Time and sync deleteGRN with Cloud/DB
        const _origRenderRecentGRNs = window.renderRecentGRNs;
        window.renderRecentGRNs = function(resetCount = true) {
            _origRenderRecentGRNs(resetCount);
            const rows = document.querySelectorAll('#recent-grns-container tbody tr');
            rows.forEach((tr) => {
                const grnIdCell = tr.children[0];
                const dateCell = tr.children[3];
                if (!grnIdCell || !dateCell) return;
                const grnId = grnIdCell.textContent.trim().split(/\s+/)[0];
                const grnObj = (allPurchases || []).find(p => p.id === grnId);
                if (grnObj && grnObj.date) {
                    dateCell.innerHTML = window.formatSpacedDateTime(grnObj.date);
                }
            });
        };

        // 7. Fix deleteGRN Cloud Sync & Smart Warehouse/Item Matching
        const _origDeleteGRN = window.deleteGRN;
        window.deleteGRN = function(grnId) {
            _origDeleteGRN(grnId);
            if (typeof window.pushToCloud === 'function') {
                window.pushToCloud(true);
            }
        };

        // Global shortcut: F10 or Ctrl+S saves the Receipt Note when Editor is active
        document.addEventListener('keydown', function(e) {
            const editorActive = document.getElementById('view-editor') && document.getElementById('view-editor').classList.contains('active');
            if (!editorActive) return;
            if (e.key === 'F10' || ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's')) {
                e.preventDefault();
                saveReceiptNote();
            }
        });
    })();
    </script>
    <!-- ════════════════════════════════════════════════════════════════════════════
         WORLD-CLASS 6-PILLAR GRN SUITE & 100% CROSS-SCREEN / REPORT INTEGRATION
         ════════════════════════════════════════════════════════════════════════════ -->
    <style id="grn-six-pillars-styles">
        /* Logistics & Scanner Glass Bars */
        .grn-logistics-bar,
        .grn-scanner-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 12px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.78), rgba(30, 41, 59, 0.55));
            border: 1px solid rgba(56, 189, 248, 0.16);
            padding: 12px 18px;
            border-radius: 16px;
            backdrop-filter: blur(16px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.28);
            margin-bottom: 4px;
            align-items: center;
        }
        .grn-scanner-bar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 14px;
            background: linear-gradient(90deg, rgba(6, 78, 59, 0.35), rgba(15, 23, 42, 0.8), rgba(2, 132, 199, 0.25));
            border-color: rgba(16, 185, 129, 0.28);
        }
        .grn-log-field {
            display: flex;
            flex-direction: column;
            gap: 4px;
            position: relative;
        }
        .grn-log-field label {
            font-size: 0.78rem;
            font-weight: 700;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .grn-log-input {
            height: 40px;
            padding: 0 12px;
            border-radius: 10px;
            background: rgba(15, 23, 42, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #f8fafc;
            font-family: 'Tajawal', 'Outfit', sans-serif;
            font-size: 0.9rem;
            font-weight: 700;
            transition: all 0.26s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .grn-log-input:hover,
        .grn-log-input:focus {
            border-color: transparent !important;
            outline: none !important;
            transform: scale(1.045) translateY(-2px);
            background: rgba(15, 23, 42, 0.96);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.45), 0 0 18px rgba(56, 189, 248, 0.38);
        }

        /* Fulfillment Progress Bar */
        .grn-progress-wrap {
            display: flex;
            flex-direction: column;
            gap: 5px;
            min-width: 220px;
            flex: 1;
        }
        .grn-progress-track {
            width: 100%;
            height: 11px;
            background: rgba(15, 23, 42, 0.85);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.4);
            position: relative;
        }
        .grn-progress-fill {
            height: 100%;
            border-radius: 20px;
            background: linear-gradient(90deg, #0ea5e9, #10b981, #34d399);
            box-shadow: 0 0 14px rgba(16, 185, 129, 0.65);
            transition: width 0.35s cubic-bezier(0.22, 1, 0.36, 1);
        }

        /* QC Status Badge inside Row */
        .grn-qc-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 4px 9px;
            border-radius: 9px;
            font-size: 0.76rem;
            font-weight: 800;
            cursor: pointer;
            user-select: none;
            border: 1px solid transparent;
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            white-space: nowrap;
            flex-shrink: 0;
        }
        .grn-qc-pill.qc-ok {
            background: rgba(16, 185, 129, 0.16);
            color: #34d399;
        }
        .grn-qc-pill.qc-warn {
            background: rgba(245, 158, 11, 0.18);
            color: #fbbf24;
        }
        .grn-qc-pill.qc-dmg {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }
        .grn-qc-pill:hover {
            transform: scale(1.14) translateY(-1px);
            box-shadow: 0 6px 16px rgba(56, 189, 248, 0.35);
            border-color: transparent !important;
        }

        /* Variance Pill inside Base Qty cell */
        .grn-var-tag {
            display: inline-flex;
            align-items: center;
            padding: 2px 7px;
            border-radius: 6px;
            font-size: 0.72rem;
            font-weight: 800;
            margin-right: 6px;
            font-family: 'Outfit', 'Tajawal', sans-serif;
        }
        .grn-var-tag.exact { background: rgba(16, 185, 129, 0.18); color: #34d399; }
        .grn-var-tag.short { background: rgba(245, 158, 11, 0.18); color: #fbbf24; }
        .grn-var-tag.over  { background: rgba(56, 189, 248, 0.22); color: #38bdf8; }

        /* Cross-Screen Quick Navigation Pills */
        .grn-nav-links-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .grn-nav-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 13px;
            border-radius: 11px;
            background: rgba(30, 41, 59, 0.65);
            border: 1px solid transparent;
            color: #bae6fd;
            font-size: 0.82rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.26s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .grn-nav-pill:hover {
            transform: scale(1.07) translateY(-2px);
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.25), rgba(16, 185, 129, 0.2));
            color: #fff;
            box-shadow: 0 8px 20px rgba(56, 189, 248, 0.3);
        }

        :root.light-theme .grn-logistics-bar,
        :root.light-theme .grn-scanner-bar {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.1) !important;
        }
        :root.light-theme .grn-log-input {
            background: #f8fafc !important;
            color: #0f172a !important;
            border-color: rgba(0, 0, 0, 0.15) !important;
        }
        :root.light-theme .grn-nav-pill {
            background: #f1f5f9 !important;
            color: #0369a1 !important;
        }
    </style>

    <script>
    (function() {
        // Subtle Web Audio Beep for Barcode Scan Confirmation
        function playScanBeep(freq = 880, duration = 0.09) {
            try {
                const Ctx = window.AudioContext || window.webkitAudioContext;
                if (!Ctx) return;
                const ctx = new Ctx();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, ctx.currentTime);
                gain.gain.setValueAtTime(0.12, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + duration);
            } catch (e) {}
        }

        // 1. Inject Cross-Screen Navigation Pills into Hub Title Bar
        function injectHubCrossScreenLinks() {
            const hubTitle = document.querySelector('#view-hub .neo-card-title');
            if (!hubTitle || document.getElementById('grn-cross-screen-nav')) return;

            const navBar = document.createElement('div');
            navBar.id = 'grn-cross-screen-nav';
            navBar.className = 'grn-nav-links-bar';
            navBar.innerHTML = `
                <a href="purchase.php" class="grn-nav-pill" title="فتح شاشة فواتير المشتريات"><span class="grn-icon-badge" style="width:22px;height:22px;font-size:0.75rem;">🧾</span> فواتير المشتريات</a>
                <a href="warehouse-inventory.php" class="grn-nav-pill" title="فتح شاشة جرد وأرصدة المستودعات"><span class="grn-icon-badge" style="width:22px;height:22px;font-size:0.75rem;">🏛️</span> أرصدة وجرد المخازن</a>
                <a href="item-ledger.php" class="grn-nav-pill" title="فتح كشف حركة الصنف التفصيلي"><span class="grn-icon-badge" style="width:22px;height:22px;font-size:0.75rem;">📈</span> حركة الصنف</a>
                <a href="warehouse-ledger.php" class="grn-nav-pill" title="فتح دفتر أستاذ المخزن"><span class="grn-icon-badge" style="width:22px;height:22px;font-size:0.75rem;">📒</span> دفتر المخزن</a>
                <a href="inventory-stocktake.php" class="grn-nav-pill" title="فتح شاشة الجرد الفعلي والتسويات"><span class="grn-icon-badge" style="width:22px;height:22px;font-size:0.75rem;">⚖️</span> الجرد والتسويات</a>
            `;
            hubTitle.appendChild(navBar);
        }

        // 2. Inject Logistics Bar, Barcode Scanner, Warehouse Bulk Selector & Live Progress Bar into Editor View
        function injectEditorWorldClassBars() {
            const viewEditor = document.getElementById('view-editor');
            const editorLayout = viewEditor ? viewEditor.querySelector('.editor-layout') : null;
            if (!viewEditor || !editorLayout || document.getElementById('grn-logistics-bar')) return;

            // A. Logistics & Field Documentation Bar
            const logBar = document.createElement('div');
            logBar.id = 'grn-logistics-bar';
            logBar.className = 'grn-logistics-bar';
            logBar.innerHTML = `
                <div class="grn-log-field">
                    <label><span class="grn-icon-badge" style="width:20px;height:20px;font-size:0.7rem;">📄</span> رقم بوليصة الشحن / إشعار المورد</label>
                    <input type="text" id="grn-waybill-no" class="grn-log-input" placeholder="مثال: WB-90421 / إشعار المورد...">
                </div>
                <div class="grn-log-field">
                    <label><span class="grn-icon-badge" style="width:20px;height:20px;font-size:0.7rem;">🚛</span> رقم الشاحنة / اللوحة</label>
                    <input type="text" id="grn-truck-no" class="grn-log-input" placeholder="مثال: شاحنة خ3 - 45812">
                </div>
                <div class="grn-log-field">
                    <label><span class="grn-icon-badge" style="width:20px;height:20px;font-size:0.7rem;">👨‍✈️</span> اسم السائق / المندوب وهاتفه</label>
                    <input type="text" id="grn-driver-info" class="grn-log-input" placeholder="اسم السائق أو رقم الجوال...">
                </div>
                <div class="grn-log-field">
                    <label><span class="grn-icon-badge" style="width:20px;height:20px;font-size:0.7rem;">🛡️</span> أمين المستودع / فاحص الجودة</label>
                    <input type="text" id="grn-inspector-name" class="grn-log-input" placeholder="اسم المستلم الفعلي...">
                </div>
            `;

            // B. Smart Barcode Scanner + Bulk Warehouse Selector + Visual Progress Bar
            const scanBar = document.createElement('div');
            scanBar.id = 'grn-scanner-bar';
            scanBar.className = 'grn-scanner-bar';
            scanBar.innerHTML = `
                <div style="display:flex;align-items:center;gap:10px;flex:1.4;min-width:260px;">
                    <span class="grn-icon-badge" style="width:36px;height:36px;font-size:1.1rem;">🔍</span>
                    <input type="text" id="grn-barcode-input" class="grn-log-input" style="flex:1;height:42px;font-size:0.95rem;" placeholder="امسح الباركود أو اكتب اسم/كود الصنف ثم اضغط Enter لزيادة الاستلام (+1)...">
                </div>
                <div style="display:flex;align-items:center;gap:8px;min-width:220px;">
                    <span class="grn-icon-badge" style="width:32px;height:32px;font-size:0.95rem;" title="توحيد المستودع لجميع الأصناف">🏢</span>
                    <select id="grn-bulk-wh-select" class="grn-log-input" style="height:42px;min-width:170px;cursor:pointer;" title="تعميم المستودع المختار على جميع سطور الفاتورة">
                        <option value="">🏢 تعميم المستودع للكل...</option>
                    </select>
                </div>
                <div class="grn-progress-wrap">
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.82rem;font-weight:800;">
                        <span style="color:#bae6fd;display:inline-flex;align-items:center;gap:5px;"><span class="grn-icon-badge" style="width:20px;height:20px;font-size:0.7rem;">📊</span> نسبة إنجاز استلام الفاتورة:</span>
                        <span id="grn-live-progress-pct" style="color:#34d399;font-family:'Outfit';font-size:0.95rem;">100%</span>
                    </div>
                    <div class="grn-progress-track">
                        <div id="grn-live-progress-fill" class="grn-progress-fill" style="width:100%;"></div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <button type="button" class="btn-action" style="padding:9px 14px;font-size:0.86rem;border-radius:10px;background:linear-gradient(135deg,#0284c7,#0369a1);display:inline-flex;align-items:center;gap:6px;" onclick="printCurrentOrSavedGRN()">
                        <span class="grn-icon-badge" style="width:22px;height:22px;">🖨️</span> طباعة سند GRN
                    </button>
                    <button type="button" class="btn-action" style="padding:9px 14px;font-size:0.86rem;border-radius:10px;background:linear-gradient(135deg,#059669,#047857);display:inline-flex;align-items:center;gap:6px;" onclick="shareCurrentOrSavedGRNWhatsApp()">
                        <span class="grn-icon-badge" style="width:22px;height:22px;">💬</span> واتساب
                    </button>
                </div>
            `;

            viewEditor.insertBefore(logBar, editorLayout);
            viewEditor.insertBefore(scanBar, editorLayout);

            // Bind Bulk Warehouse change
            const bulkWh = document.getElementById('grn-bulk-wh-select');
            if (bulkWh) {
                bulkWh.addEventListener('change', function() {
                    if (!this.value) return;
                    receiptLines.forEach(l => { l.warehouseId = this.value; });
                    renderReceiptLines();
                    showToast('تم تعميم المستودع المختار على جميع الأصناف 🏢', 'success');
                });
            }

            // Bind Barcode / Quick Item Scanner
            const barcodeInp = document.getElementById('grn-barcode-input');
            if (barcodeInp) {
                barcodeInp.addEventListener('keydown', function(e) {
                    if (e.key !== 'Enter') return;
                    e.preventDefault();
                    const q = this.value.trim().toLowerCase();
                    if (!q) return;

                    // Support syntax: "code*qty" e.g. "1010*5"
                    let scanTerm = q;
                    let addStep = 1;
                    if (q.includes('*')) {
                        const parts = q.split('*');
                        scanTerm = parts[0].trim();
                        addStep = parseFloat(parts[1]) || 1;
                    }

                    const matchedIdx = receiptLines.findIndex(line => {
                        const itemObj = (itemsDb || []).find(i => String(i.id) === String(line.itemId) || i.name === line.itemId) || {};
                        const iName = (getItemName(line.itemId) || '').toLowerCase();
                        const iCode = String(itemObj.code || itemObj.itemCode || line.itemId || '').toLowerCase();
                        const iBar  = String(itemObj.barcode || '').toLowerCase();
                        return iBar === scanTerm || iCode === scanTerm || iName.includes(scanTerm);
                    });

                    if (matchedIdx === -1) {
                        playScanBeep(260, 0.18);
                        showToast('⚠️ لم يتم العثور على صنف مطابق للباركود/الاسم في هذه الفاتورة: ' + scanTerm, 'error');
                        this.select();
                        return;
                    }

                    const targetLine = receiptLines[matchedIdx];
                    const nextVal = Math.min(targetLine.maxPending, (parseFloat(targetLine.receiveQty) || 0) + addStep);
                    targetLine.receiveQty = nextVal;
                    this.value = '';
                    playScanBeep(960, 0.08);
                    renderReceiptLines();

                    // Highlight & scroll to matched row
                    const rows = document.querySelectorAll('#items-tbody tr');
                    if (rows[matchedIdx]) {
                        rows.forEach(r => r.classList.remove('grn-active-row'));
                        rows[matchedIdx].classList.add('grn-active-row');
                        rows[matchedIdx].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    showToast(`✅ تم مسح (${getItemName(targetLine.itemId)}) — الكمية المستلمة الآن: ${nextVal}`, 'success');
                });
            }

            // Inject Variance & Base Weight Pill into Bottom Summary Bar
            const bottomLeft = document.querySelector('#view-editor .editor-bottom-bar > div:first-child');
            if (bottomLeft && !document.getElementById('grn-summary-base-box')) {
                const baseBox = document.createElement('div');
                baseBox.id = 'grn-summary-base-box';
                baseBox.style.cssText = 'background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(192, 132, 252, 0.35); padding: 8px 16px; border-radius: 12px; display: flex; align-items: center; gap: 10px;';
                baseBox.innerHTML = `
                    <span style="color: #94a3b8; font-size: 0.85rem;">⚖️ إجمالي بالوحدة الأساسية:</span>
                    <span id="lbl-total-base-qty" style="color: #c084fc; font-weight: 800; font-family: 'Outfit', sans-serif; font-size: 1.2rem;">0</span>
                `;
                const remSummaryBox = document.createElement('div');
                remSummaryBox.id = 'grn-summary-rem-box';
                remSummaryBox.style.cssText = 'background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(251, 191, 36, 0.35); padding: 8px 16px; border-radius: 12px; display: flex; align-items: center; gap: 10px;';
                remSummaryBox.innerHTML = `
                    <span style="color: #94a3b8; font-size: 0.85rem;">⏳ المتبقي بعد الاستلام:</span>
                    <span id="lbl-total-remaining-qty" style="color: #34d399; font-weight: 800; font-family: 'Outfit', sans-serif; font-size: 1.15rem;">0 (مكتمل ✅)</span>
                `;
                bottomLeft.appendChild(baseBox);
                bottomLeft.appendChild(remSummaryBox);
            }
        }

        // 3. Populate Bulk Warehouse Options & Default Inspector Name on openReceiptEditor
        const _prevOpenReceiptEditor = window.openReceiptEditor;
        window.openReceiptEditor = function(invId) {
            injectEditorWorldClassBars();
            _prevOpenReceiptEditor(invId);

            // Populate bulk warehouse select
            const bulkWh = document.getElementById('grn-bulk-wh-select');
            if (bulkWh) {
                let opts = '<option value="">🏢 تعميم المستودع للكل...</option>';
                (warehousesDb || []).forEach(wh => {
                    if (wh.id !== 'PENDING_RECEIPT') {
                        opts += `<option value="${wh.id}">${wh.name}</option>`;
                    }
                });
                bulkWh.innerHTML = opts;
            }

            // Pre-fill inspector name from logged-in user if empty
            const inspInp = document.getElementById('grn-inspector-name');
            if (inspInp && !inspInp.value) {
                const curU = (typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo() : JSON.parse(localStorage.getItem('user') || '{}');
                inspInp.value = curU.fullName || curU.name || curU.username || 'أمين المستودع';
            }
            if (!editingGRNId) {
                ['grn-waybill-no', 'grn-truck-no', 'grn-driver-info'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.value = '';
                });
            }
            updateLiveProgressAndVariance();
        };

        // 4. Live Progress Bar, Weight/Base Qty & Variance Summary Calculation
        function updateLiveProgressAndVariance() {
            let totalMaxPending = 0;
            let totalRcvQty = 0;
            let totalBaseQty = 0;

            const seenGroups = new Set();
            (receiptLines || []).forEach(l => {
                const rQty = parseFloat(l.receiveQty) || 0;
                const fac = parseFloat(l.factor || l.customFactor) || 1;
                totalRcvQty += rQty;
                totalBaseQty += rQty * fac;
                if (!seenGroups.has(l.lineGroup)) {
                    seenGroups.add(l.lineGroup);
                    totalMaxPending += parseFloat(l.maxPending) || 0;
                }
            });

            const pct = totalMaxPending > 0 ? Math.min(100, Math.round((totalRcvQty / totalMaxPending) * 100)) : 100;
            const remTotal = Math.max(0, Math.round((totalMaxPending - totalRcvQty) * 100) / 100);

            const pctEl = document.getElementById('grn-live-progress-pct');
            const fillEl = document.getElementById('grn-live-progress-fill');
            if (pctEl) {
                pctEl.textContent = pct + '%';
                pctEl.style.color = pct >= 100 ? '#34d399' : (pct > 0 ? '#38bdf8' : '#fbbf24');
            }
            if (fillEl) {
                fillEl.style.width = pct + '%';
            }

            const baseEl = document.getElementById('lbl-total-base-qty');
            if (baseEl) baseEl.textContent = fmtNum(totalBaseQty);

            const remEl = document.getElementById('lbl-total-remaining-qty');
            if (remEl) {
                if (remTotal <= 0) {
                    remEl.textContent = '0 (مكتمل 100% ✅)';
                    remEl.style.color = '#34d399';
                } else {
                    remEl.textContent = fmtNum(remTotal) + ' متبقي قيد الاستلام';
                    remEl.style.color = '#fbbf24';
                }
            }
        }

        // 5. Enhance renderReceiptLines with QC Inspection Badge, Item Ledger Quick Link, and Variance Indicator
        const _prevRenderReceiptLines = window.renderReceiptLines;
        window.renderReceiptLines = function() {
            _prevRenderReceiptLines();
            const tbody = document.getElementById('items-tbody');
            if (!tbody) return;

            const rows = Array.from(tbody.querySelectorAll('tr'));
            rows.forEach((tr, idx) => {
                const line = receiptLines[idx];
                if (!line) return;
                if (!line.qcStatus) line.qcStatus = 'ok'; // 'ok' | 'warn' | 'dmg'

                // A. Add Item Ledger direct link button next to Split button in Item cell
                const itemBox = tr.querySelector('.grn-col-item:not([style*="display:none"])');
                if (itemBox && !itemBox.querySelector('.grn-ledger-link')) {
                    const ledgerBtn = document.createElement('a');
                    ledgerBtn.href = `item-ledger.php?item=${encodeURIComponent(line.itemId)}`;
                    ledgerBtn.target = '_blank';
                    ledgerBtn.className = 'grn-row-mini-btn grn-ledger-link';
                    ledgerBtn.title = 'فتح كشف حركة هذا الصنف في تبويب جديد 📈';
                    ledgerBtn.style.textDecoration = 'none';
                    ledgerBtn.innerHTML = '📈';
                    itemBox.appendChild(ledgerBtn);
                }

                // B. Add Variance Indicator inside Base Received Qty cell
                const baseRcvBox = tr.querySelector('.base-rcv-qty');
                const qtyInput = tr.querySelector('.qty-input');
                const factorInput = tr.querySelector('.factor-input');
                const refreshRowVariance = () => {
                    if (!baseRcvBox) return;
                    const f = parseFloat(line.factor || line.customFactor) || 1;
                    const rcv = parseFloat(line.receiveQty) || 0;
                    const maxP = parseFloat(line.maxPending) || 0;
                    const diff = Math.round((rcv - maxP) * 100) / 100;
                    let varBadge = '';
                    if (diff === 0 && maxP > 0) {
                        varBadge = `<span class="grn-var-tag exact" title="مطابق تماماً للكمية المطلوبة">100% ✅</span>`;
                    } else if (diff < 0) {
                        const pctRow = maxP > 0 ? Math.round((rcv / maxP) * 100) : 0;
                        varBadge = `<span class="grn-var-tag short" title="استلام جزئي — متبقي ${Math.abs(diff)}">${pctRow}%</span>`;
                    } else if (diff > 0) {
                        varBadge = `<span class="grn-var-tag over" title="زيادة في الوزن/الكمية المستلمة (+${diff})">+${diff} ⚖️</span>`;
                    }
                    baseRcvBox.innerHTML = `<span>${fmtColVal('rcvBase', rcv * f)}</span> ${varBadge}`;
                    updateLiveProgressAndVariance();
                };
                refreshRowVariance();
                if (qtyInput) qtyInput.addEventListener('input', refreshRowVariance);
                if (factorInput) factorInput.addEventListener('input', refreshRowVariance);

                // C. Inject QC Status Interactive Pill next to Notes Input
                const notesTd = tr.children[tr.children.length - 1];
                const notesInput = notesTd ? notesTd.querySelector('.notes-input') : null;
                if (notesTd && notesInput && !notesTd.querySelector('.grn-qc-pill')) {
                    const wrap = document.createElement('div');
                    wrap.style.cssText = 'display:flex;align-items:center;gap:6px;width:100%;';
                    notesInput.parentNode.insertBefore(wrap, notesInput);

                    const qcBtn = document.createElement('span');
                    const renderQcBtn = () => {
                        if (line.qcStatus === 'ok') {
                            qcBtn.className = 'grn-qc-pill qc-ok';
                            qcBtn.innerHTML = '✅ سليم';
                            qcBtn.title = 'فحص الجودة: البضاعة سليمة ومطابقة — انقر للتغيير (سليم / تحفظ / تالف)';
                        } else if (line.qcStatus === 'warn') {
                            qcBtn.className = 'grn-qc-pill qc-warn';
                            qcBtn.innerHTML = '⚠️ بتحفظ';
                            qcBtn.title = 'فحص الجودة: استلام بتحفظ — انقر للتغيير';
                        } else {
                            qcBtn.className = 'grn-qc-pill qc-dmg';
                            qcBtn.innerHTML = `🚨 تالف (${line.damagedQty || 0})`;
                            qcBtn.title = 'فحص الجودة: يوجد تالف/مرفوض — انقر للتغيير';
                        }
                    };
                    renderQcBtn();

                    qcBtn.addEventListener('click', () => {
                        if (line.qcStatus === 'ok') {
                            line.qcStatus = 'warn';
                            if (!line.notes) {
                                line.notes = 'استلام بتحفظ (فحص جودة)';
                                notesInput.value = line.notes;
                            }
                        } else if (line.qcStatus === 'warn') {
                            line.qcStatus = 'dmg';
                            const dmgStr = prompt('أدخل الكمية التالفة / المرفوضة من هذا الصنف (سيتم توثيقها وخصمها من الصالح):', line.damagedQty || '1');
                            const dmgVal = Math.max(0, parseFloat(dmgStr) || 0);
                            line.damagedQty = dmgVal;
                            if (dmgVal > 0 && dmgVal <= line.maxPending) {
                                line.receiveQty = Math.max(0, line.maxPending - dmgVal);
                                if (qtyInput) qtyInput.value = line.receiveQty;
                                refreshRowVariance();
                            }
                            line.notes = `تالف/مرفوض: ${dmgVal} ${line.unitName || ''}`;
                            notesInput.value = line.notes;
                        } else {
                            line.qcStatus = 'ok';
                            line.damagedQty = 0;
                            if (line.notes && (line.notes.startsWith('تالف/مرفوض:') || line.notes === 'استلام بتحفظ (فحص جودة)')) {
                                line.notes = '';
                                notesInput.value = '';
                            }
                        }
                        renderQcBtn();
                    });

                    wrap.appendChild(qcBtn);
                    wrap.appendChild(notesInput);
                }
            });
            updateLiveProgressAndVariance();
        };

        // 6. Enhance Pending Invoices Hub with Visual Progress Bar per Invoice
        const _prevRenderPendingHub = window.renderPendingInvoices;
        window.renderPendingInvoices = function(resetCount = true) {
            injectHubCrossScreenLinks();
            _prevRenderPendingHub(resetCount);
            const rows = document.querySelectorAll('#invoices-tbody tr');
            rows.forEach(tr => {
                const invIdTd = tr.children[0];
                const pendingCntTd = tr.children[4];
                if (!invIdTd || !pendingCntTd) return;
                const invId = invIdTd.textContent.trim();
                const pData = pendingMap[invId];
                if (!pData || !pData.lines) return;

                let origSum = 0;
                let pendSum = 0;
                Object.values(pData.lines).forEach(l => {
                    origSum += parseFloat(l.origQty || l.pendingQty) || 0;
                    pendSum += parseFloat(l.pendingQty) || 0;
                });
                const rcvSum = Math.max(0, origSum - pendSum);
                const pct = origSum > 0 ? Math.min(99, Math.round((rcvSum / origSum) * 100)) : 0;
                const totalPendingItems = Object.values(pData.lines).length;

                pendingCntTd.innerHTML = `
                    <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                        <span style="font-weight:900;color:${pct > 0 ? '#38bdf8' : '#fbbf24'};">${totalPendingItems} صنف (${pct > 0 ? 'مستلم جزئياً ' + pct + '%' : 'بانتظار الاستلام'})</span>
                        <div class="grn-progress-track" style="height:7px;width:130px;">
                            <div class="grn-progress-fill" style="width:${pct}%;"></div>
                        </div>
                    </div>
                `;
            });
        };

        // 7. Upgrade saveReceiptNote for 100% Cross-Screen Sync (erp_items_v1 + erp_inventory_v1 + Logistics Metadata + Partial Receipt Accuracy)
        const _origSaveReceiptNote = window.saveReceiptNote;
        window.saveReceiptNote = function() {
            const receivingItems = (receiptLines || []).filter(x => x.receiveQty > 0);
            if (receivingItems.length === 0) {
                return showToast('يجب تحديد كمية استلام لصنف واحد على الأقل', 'error');
            }
            const hasEmptyWh = receivingItems.some(x => !x.warehouseId);
            if (hasEmptyWh) return showToast('يرجى تحديد المستودع لجميع الأصناف المستلمة', 'error');

            // Capture Logistics & QC fields before saving
            const waybillNo = (document.getElementById('grn-waybill-no')?.value || '').trim();
            const truckNo   = (document.getElementById('grn-truck-no')?.value || '').trim();
            const driverInfo = (document.getElementById('grn-driver-info')?.value || '').trim();
            const inspector = (document.getElementById('grn-inspector-name')?.value || '').trim();

            const logParts = [];
            if (waybillNo) logParts.push(`بوليصة: ${waybillNo}`);
            if (truckNo)   logParts.push(`شاحنة: ${truckNo}`);
            if (driverInfo) logParts.push(`سائق: ${driverInfo}`);
            if (inspector) logParts.push(`فحص: ${inspector}`);
            const logPrefix = logParts.length > 0 ? `[${logParts.join(' | ')}] ` : '';

            // Attach logistics & QC note onto lines so item-ledger.php & warehouse-ledger.php display them automatically
            receivingItems.forEach(l => {
                const qcTag = l.qcStatus === 'dmg' ? `[تالف: ${l.damagedQty || 0}] ` : (l.qcStatus === 'warn' ? '[استلام بتحفظ] ' : '');
                const cleanNote = String(l.notes || '').replace(/^\[.*?\]\s*/, '');
                l.notes = (qcTag + logPrefix + cleanNote).trim();
            });

            // Determine if this receipt completes 100% of all pending items on the invoice
            const seenGroups = new Set();
            let totalMaxPending = 0;
            let totalReceivingNow = 0;
            (receiptLines || []).forEach(l => {
                totalReceivingNow += parseFloat(l.receiveQty) || 0;
                if (!seenGroups.has(l.lineGroup)) {
                    seenGroups.add(l.lineGroup);
                    totalMaxPending += parseFloat(l.maxPending) || 0;
                }
            });
            const isFullyReceivedNow = (totalReceivingNow >= totalMaxPending - 0.001);
            const activeInvId = currentInvoiceId;

            // Call original saveReceiptNote
            _origSaveReceiptNote();

            // Post-save enrichment: enrich saved GRN record with logistics, QC, and sync erp_items_v1 stock map
            try {
                let freshPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
                // Find the most recently created/updated GRN for activeInvId
                for (let i = freshPurchases.length - 1; i >= 0; i--) {
                    const p = freshPurchases[i];
                    if (p && p.isGRN && String(p.originalInvoiceId) === String(activeInvId)) {
                        p.waybillNo = waybillNo;
                        p.truckNo = truckNo;
                        p.driverInfo = driverInfo;
                        p.inspectorName = inspector;
                        p.isFullyReceived = isFullyReceivedNow;
                        break;
                    }
                }
                // Update parent invoice receiptStatus accurately (partially_received vs received)
                const parentIdx = freshPurchases.findIndex(p => p && !p.isGRN && !p.isReturn && String(p.invoiceNo || p.id) === String(activeInvId));
                if (parentIdx > -1) {
                    freshPurchases[parentIdx].receiptStatus = isFullyReceivedNow ? 'received' : 'partially_received';
                    if (isFullyReceivedNow) {
                        freshPurchases[parentIdx].status = 'received';
                    } else if (freshPurchases[parentIdx].status === 'received') {
                        freshPurchases[parentIdx].status = 'completed';
                    }
                }
                localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(freshPurchases));
                allPurchases = freshPurchases;

                // Also update erp_items_v1 per-warehouse stock so Direct Sales, Item Master & Stocktake see the exact stock immediately!
                let itemsMaster = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
                let itemsChanged = false;
                receivingItems.forEach(line => {
                    const factor = parseFloat(line.factor || line.customFactor) || 1;
                    const baseAdded = (parseFloat(line.receiveQty) || 0) * factor;
                    const itemIdx = itemsMaster.findIndex(it => String(it.id) === String(line.itemId) || it.name === line.itemId || it.name === getItemName(line.itemId));
                    if (itemIdx > -1) {
                        const it = itemsMaster[itemIdx];
                        if (!it.stock || typeof it.stock !== 'object') it.stock = {};
                        const whId = line.warehouseId || 'w-main';
                        it.stock[whId] = (parseFloat(it.stock[whId]) || 0) + baseAdded;
                        if (it.stock['PENDING_RECEIPT'] !== undefined) {
                            it.stock['PENDING_RECEIPT'] = Math.max(0, (parseFloat(it.stock['PENDING_RECEIPT']) || 0) - baseAdded);
                        }
                        itemsChanged = true;
                    }
                });
                if (itemsChanged) {
                    localStorage.setItem('erp_items_v1', JSON.stringify(itemsMaster));
                    itemsDb = itemsMaster;
                }
            } catch (errSync) {
                console.warn('GRN cross-screen enrichment notice:', errSync);
            }
        };

        // 8. Official GRN Print Voucher & WhatsApp Share (Works for current Editor AND any saved GRN in Hub)
        window.printGRNVoucherById = function(grnId) {
            const grn = (allPurchases || []).find(p => p && p.id === grnId);
            if (!grn) return showToast('لم يتم العثور على بيانات الإذن للطباعة', 'error');

            const supName = getSupplierName(grn.customer || grn.customerId || grn.supplierId);
            const posItems = (grn.items || []).filter(i => (parseFloat(i.quantity) || 0) > 0);
            openOfficialGRNPrintWindow({
                grnNo: grn.id,
                invNo: grn.originalInvoiceId || '—',
                supplierName: supName,
                dateStr: new Date(grn.date || Date.now()).toLocaleString('en-GB'),
                waybillNo: grn.waybillNo || '—',
                truckNo: grn.truckNo || '—',
                driverInfo: grn.driverInfo || '—',
                inspectorName: grn.inspectorName || grn.createdByName || grn.createdBy || 'أمين المستودع',
                lines: posItems.map(it => {
                    const whObj = (warehousesDb || []).find(w => w.id === it.warehouseId);
                    const fac = parseFloat(it.customFactor || it.factor) || 1;
                    const q = parseFloat(it.quantity) || 0;
                    return {
                        itemName: getItemName(it.itemId),
                        unitName: it.unitName || 'وحدة',
                        origQty: q,
                        receiveQty: q,
                        factor: fac,
                        baseQty: it.customBaseQty || (q * fac),
                        warehouseName: whObj ? whObj.name : (it.warehouseId || 'المخزن الرئيسي'),
                        qcLabel: (it.notes && it.notes.includes('تالف')) ? 'يوجد تالف' : ((it.notes && it.notes.includes('بتحفظ')) ? 'بتحفظ' : 'مطابق ✅'),
                        notes: it.notes || '—'
                    };
                })
            });
        };

        window.printCurrentOrSavedGRN = function() {
            const invData = pendingMap[currentInvoiceId] || {};
            const linesToPrint = (receiptLines || []).filter(l => (parseFloat(l.receiveQty) || 0) > 0);
            if (linesToPrint.length === 0) return showToast('لا توجد كميات مستلمة لطباعتها', 'error');

            openOfficialGRNPrintWindow({
                grnNo: editingGRNId || generateGRNNumber(),
                invNo: currentInvoiceId || '—',
                supplierName: getSupplierName(invData.supplierId),
                dateStr: new Date().toLocaleString('en-GB'),
                waybillNo: document.getElementById('grn-waybill-no')?.value || '—',
                truckNo: document.getElementById('grn-truck-no')?.value || '—',
                driverInfo: document.getElementById('grn-driver-info')?.value || '—',
                inspectorName: document.getElementById('grn-inspector-name')?.value || 'أمين المستودع',
                lines: linesToPrint.map(l => {
                    const whObj = (warehousesDb || []).find(w => w.id === l.warehouseId);
                    const fac = parseFloat(l.factor || l.customFactor) || 1;
                    const q = parseFloat(l.receiveQty) || 0;
                    return {
                        itemName: getItemName(l.itemId),
                        unitName: l.unitName || 'وحدة',
                        origQty: l.origQty || l.maxPending,
                        receiveQty: q,
                        factor: fac,
                        baseQty: q * fac,
                        warehouseName: whObj ? whObj.name : 'المخزن الرئيسي',
                        qcLabel: l.qcStatus === 'dmg' ? `تالف (${l.damagedQty || 0})` : (l.qcStatus === 'warn' ? 'بتحفظ ⚠️' : 'سليم ومطابق ✅'),
                        notes: l.notes || '—'
                    };
                })
            });
        };

        function openOfficialGRNPrintWindow(doc) {
            const rowsHtml = doc.lines.map((r, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td style="text-align:right;font-weight:800;">${r.itemName}</td>
                    <td>${r.unitName}</td>
                    <td>${fmtNum(r.origQty)}</td>
                    <td style="font-weight:900;color:#0369a1;">${fmtNum(r.receiveQty)}</td>
                    <td>${fmtNum(r.factor)}</td>
                    <td style="font-weight:800;color:#059669;">${fmtNum(r.baseQty)}</td>
                    <td>${r.warehouseName}</td>
                    <td>${r.qcLabel}</td>
                    <td style="text-align:right;font-size:12px;">${r.notes}</td>
                </tr>
            `).join('');

            const w = window.open('', '_blank', 'width=1050,height=800');
            if (!w) return showToast('يرجى السماح بالنوافذ المنبثقة لطباعة السند', 'error');
            w.document.write(`<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>سند استلام مخزني (GRN) - ${doc.grnNo}</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@500;700;800;900&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Tajawal', sans-serif; margin: 24px; color: #0f172a; background: #fff; }
    .hdr { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #0284c7; padding-bottom: 14px; margin-bottom: 18px; }
    .hdr h1 { margin: 0; font-size: 24px; color: #0369a1; font-weight: 900; }
    .meta-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 14px; margin-bottom: 18px; font-size: 13.5px; }
    .meta-grid div strong { display: block; color: #64748b; font-size: 11.5px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 13.5px; }
    th { background: #0f172a; color: #fff; padding: 10px 8px; text-align: center; font-weight: 800; }
    td { border: 1px solid #cbd5e1; padding: 9px 8px; text-align: center; }
    tr:nth-child(even) { background: #f8fafc; }
    .sigs { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 35px; text-align: center; font-weight: 800; font-size: 14px; }
    .sigs div { border-top: 2px dashed #94a3b8; padding-top: 10px; }
    @media print { .no-print { display: none !important; } body { margin: 10px; } }
</style>
</head>
<body>
    <div class="no-print" style="margin-bottom:15px;display:flex;gap:10px;">
        <button onclick="window.print()" style="padding:10px 24px;background:#0284c7;color:#fff;border:none;border-radius:8px;font-family:'Tajawal';font-weight:800;cursor:pointer;font-size:15px;">🖨️ طباعة السند الآن</button>
        <button onclick="window.close()" style="padding:10px 18px;background:#e2e8f0;color:#0f172a;border:none;border-radius:8px;font-family:'Tajawal';font-weight:700;cursor:pointer;">إغلاق</button>
    </div>
    <div class="hdr">
        <div>
            <h1>📦 سند استلام بضاعة مخزني (Official GRN)</h1>
            <div style="color:#475569;font-weight:700;margin-top:4px;">منظومة مقاييس العالمية لإدارة المخازن وسلاسل الإمداد</div>
        </div>
        <div style="text-align:left;font-weight:900;font-size:18px;color:#0369a1;">
            رقم الإذن: ${doc.grnNo}<br>
            <span style="font-size:13px;color:#64748b;">التاريخ: ${doc.dateStr}</span>
        </div>
    </div>
    <div class="meta-grid">
        <div><strong>رقم فاتورة الشراء الأصلية</strong>${doc.invNo}</div>
        <div><strong>المورد</strong>${doc.supplierName}</div>
        <div><strong>رقم بوليصة الشحن / إشعار المورد</strong>${doc.waybillNo}</div>
        <div><strong>رقم الشاحنة / اللوحة</strong>${doc.truckNo}</div>
        <div><strong>اسم السائق / المندوب</strong>${doc.driverInfo}</div>
        <div><strong>أمين المستودع / الفاحص</strong>${doc.inspectorName}</div>
        <div><strong>إجمالي الأصناف المستلمة</strong>${doc.lines.length} أصناف</div>
        <div><strong>حالة الفحص</strong>مفحوص ومعتمد مخزنياً ✅</div>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>اسم الصنف</th>
                <th>الوحدة</th>
                <th>كمية الفاتورة</th>
                <th>الكمية المستلمة</th>
                <th>التعادل</th>
                <th>الكمية بالوحدة الأساسية</th>
                <th>المستودع المُستلِم</th>
                <th>فحص الجودة (QC)</th>
                <th>ملاحظات</th>
            </tr>
        </thead>
        <tbody>${rowsHtml}</tbody>
    </table>
    <div class="sigs">
        <div>توقيع أمين المستودع المستلم<br><small style="color:#64748b;">${doc.inspectorName}</small></div>
        <div>توقيع السائق / مندوب التوريد<br><small style="color:#64748b;">${doc.driverInfo}</small></div>
        <div>مسؤول فحص الجودة (QC)</div>
        <div>اعتماد إدارة المشتريات والمخازن</div>
    </div>
</body>
</html>`);
            w.document.close();
        }

        window.shareCurrentOrSavedGRNWhatsApp = function(grnIdOptional) {
            let text = '';
            if (grnIdOptional) {
                const grn = (allPurchases || []).find(p => p && p.id === grnIdOptional);
                if (!grn) return;
                const supName = getSupplierName(grn.customer || grn.customerId || grn.supplierId);
                const posItems = (grn.items || []).filter(i => (parseFloat(i.quantity) || 0) > 0);
                const itemsLines = posItems.map(i => `• ${getItemName(i.itemId)}: *${fmtNum(i.quantity)} ${i.unitName || 'وحدة'}*`).join('\n');
                text = `📦 *إشعار استلام بضاعة مخزني (GRN)*\nرقم الإذن: *${grn.id}*\nالفاتورة الأصلية: *${grn.originalInvoiceId}*\nالمورد: *${supName}*\n\n*الأصناف المستلمة:*\n${itemsLines}\n\n✅ تم الاستلام والفحص المخزني بنجاح.`;
            } else {
                const invData = pendingMap[currentInvoiceId] || {};
                const supName = getSupplierName(invData.supplierId);
                const lines = (receiptLines || []).filter(l => (parseFloat(l.receiveQty) || 0) > 0);
                const itemsLines = lines.map(l => `• ${getItemName(l.itemId)}: *${fmtNum(l.receiveQty)} ${l.unitName || 'وحدة'}*`).join('\n');
                text = `📦 *إشعار استلام بضاعة مخزني (GRN)*\nالفاتورة: *${currentInvoiceId}*\nالمورد: *${supName}*\n\n*الأصناف المستلمة:*\n${itemsLines}\n\n✅ تم الاستلام والفحص المخزني بنجاح.`;
            }
            window.open('https://wa.me/?text=' + encodeURIComponent(text), '_blank');
        };

        // 9. Add Print & WhatsApp buttons to each row in Recent GRNs table (`#recent-grns-container`)
        const _prevRenderRecentGRNs2 = window.renderRecentGRNs;
        window.renderRecentGRNs = function(resetCount = true) {
            _prevRenderRecentGRNs2(resetCount);
            const rows = document.querySelectorAll('#recent-grns-container tbody tr');
            rows.forEach(tr => {
                const grnIdCell = tr.children[0];
                const actionsCell = tr.children[tr.children.length - 1];
                if (!grnIdCell || !actionsCell) return;
                const grnId = grnIdCell.textContent.trim().split(/\s+/)[0];
                const btnWrap = actionsCell.querySelector('div');
                if (btnWrap && !btnWrap.querySelector('.grn-print-btn')) {
                    const printBtn = document.createElement('button');
                    printBtn.className = 'btn-action grn-print-btn';
                    printBtn.style.cssText = 'padding: 4px 8px; font-size: 0.75rem; background: linear-gradient(135deg, #0284c7, #0369a1);';
                    printBtn.title = 'طباعة سند الاستلام المخزني 🖨️';
                    printBtn.innerHTML = '🖨️';
                    printBtn.onclick = () => window.printGRNVoucherById(grnId);

                    const waBtn = document.createElement('button');
                    waBtn.className = 'btn-action';
                    waBtn.style.cssText = 'padding: 4px 8px; font-size: 0.75rem; background: linear-gradient(135deg, #10b981, #059669);';
                    waBtn.title = 'مشاركة إشعار الاستلام عبر واتساب 💬';
                    waBtn.innerHTML = '💬';
                    waBtn.onclick = () => window.shareCurrentOrSavedGRNWhatsApp(grnId);

                    btnWrap.insertBefore(waBtn, btnWrap.firstChild);
                    btnWrap.insertBefore(printBtn, waBtn);
                }
            });
        };

        // 10. Deep-Link Handler: Support ?id=Z26... or ?id=PUR-... or ?invoice=... from item-ledger.php, warehouse-ledger.php, purchase.php
        function handleIncomingDeepLink() {
            const params = new URLSearchParams(window.location.search);
            const targetId = (params.get('id') || params.get('invoice') || params.get('grn') || '').trim();
            if (!targetId) return;

            if (pendingMap && pendingMap[targetId]) {
                openReceiptEditor(targetId);
                return;
            }
            const grnMatch = (allPurchases || []).find(p => p && p.isGRN && (String(p.id) === targetId || String(p.originalInvoiceId) === targetId));
            if (grnMatch) {
                const toggleInput = document.getElementById('uiverse-toggle');
                if (toggleInput) toggleInput.checked = true;
                const searchInp = document.getElementById('search-inv');
                if (searchInp) searchInp.value = targetId;
                toggleHubViewBySwitch(true);
            }
        }

        setTimeout(() => {
            injectHubCrossScreenLinks();
            if (currentHubView === 'pending') renderPendingInvoices(false);
            handleIncomingDeepLink();
        }, 250);
    })();
    </script>
</body>
</html>
