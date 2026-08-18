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
                                    <th style="text-align:center; width:10%; color:#e2e8f0; font-weight:700; font-size:0.95rem; padding: 12px 5px;">كمية الشراء</th>
                                    <th style="text-align:right; width:22%; color:#e2e8f0; font-weight:700; font-size:0.95rem; padding: 12px 10px;">الصنف</th>
                                    <th style="text-align:center; width:9%; color:#e2e8f0; font-weight:700; font-size:0.95rem; padding: 12px 5px;">وحدة الشراء</th>
                                    <th style="text-align:center; width:9%; color:#e2e8f0; font-weight:700; font-size:0.95rem; padding: 12px 5px;">الكمية المتبقية</th>
                                    <th style="text-align:center; width:7%; color:#e2e8f0; font-weight:700; font-size:0.95rem; padding: 12px 5px;">التعادل</th>
                                    <th style="text-align:center; width:11%; color:#e2e8f0; font-weight:700; font-size:0.95rem; padding: 12px 5px;">الكمية بالوحدة الأساسية</th>
                                    <th style="text-align:center; width:14%; color:#38bdf8; font-weight:800; font-size:0.95rem; padding: 12px 5px; cursor: pointer; user-select: none;" onclick="openGridSettings('rcvQty')" title="انقر لتعديل حجم الخط ولون الخط للكمية المستلمة"><span style="display:inline-flex; align-items:center; gap:5px; background: rgba(56, 189, 248, 0.12); padding: 4px 10px; border-radius: 8px; border: 1px solid rgba(56, 189, 248, 0.3);">✍️ الكمية المستلمة (اكتب هنا) ⚙️</span></th>
                                    <th style="text-align:center; width:9%; color:#e2e8f0; font-weight:700; font-size:0.95rem; padding: 12px 5px;">الكمية بالوحدة</th>
                                    <th style="text-align:center; width:9%; color:#e2e8f0; font-weight:700; font-size:0.95rem; padding: 12px 5px;">المستودع المُستلِم</th>
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

                const isOriginal = (!inv.isReturn && !inv.isGRN);
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
                                unitName: line.unitName || line.selectedUnit || line.unit || 'حبة',
                                factor: line.customFactor || line.factor || line.equiv || line.conversionFactor || 0,
                                price: parseFloat(line.price) || 0,
                                origQty: 0,
                                pendingQty: 0
                            };
                        }
                        
                        const q = parseFloat(line.quantity) || 0;
                        if (isOriginal && q > 0) {
                            pendingMap[rootInvId].lines[lineKey].origQty = (pendingMap[rootInvId].lines[lineKey].origQty || 0) + q;
                        }
                        pendingMap[rootInvId].lines[lineKey].pendingQty += q;
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
                return {
                    id: 'L' + index,
                    itemId: line.itemId,
                    unitId: line.unitId,
                    unitName: line.unitName,
                    factor: line.factor || 0,
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
                
                let factor = 1;
                if (line.factor && parseFloat(line.factor) > 0) {
                    factor = parseFloat(line.factor);
                } else if (typeof itemsDb !== 'undefined') {
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
                const baseQty = (line.maxPending * factor);
                const rcvBaseQty = ((line.receiveQty || 0) * factor);
                const whCol = getColStyleObj('warehouse');
                
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
                        <div style="${getColStyleObj('factor').boxStyle}" class="grn-col-factor">${fmtColVal('factor', factor)}</div>
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
                `;
                const qtyInput = tr.querySelector('.qty-input');
                const baseRcvCell = tr.querySelector('.base-rcv-qty');
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
                    if (baseRcvCell) {
                        baseRcvCell.textContent = fmtColVal('rcvBase', val * factor);
                    }
                    updateSummary();
                });

                const whSelect = tr.querySelector('.wh-select');
                whSelect.addEventListener('change', function() {
                    line.warehouseId = this.value;
                });

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
                // احسب عامل التحويل من بيانات الصنف
                let lineFactor = 1;
                if (line.factor && parseFloat(line.factor) > 0) {
                    lineFactor = parseFloat(line.factor);
                } else if (typeof itemsDb !== 'undefined') {
                    const itemObj = itemsDb.find(i => String(i.id) === String(line.itemId));
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

                // Negative (Take out of PENDING)
                newItems.push({
                    itemId: line.itemId,
                    unitId: line.unitId,
                    unitName: line.unitName,
                    quantity: -line.receiveQty,
                    customFactor: lineFactor,
                    customBaseQty: -(line.receiveQty * lineFactor),
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
                    customFactor: lineFactor,
                    customBaseQty: line.receiveQty * lineFactor,
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

                    return `${name} (${item.quantity} ${item.unitName || 'وحدة'})`;
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

</body>
</html>
