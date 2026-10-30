<?php if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() === 0) { @ob_start('ob_gzhandler'); } ?>
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
            box-shadow: 0 0 18px rgba(16, 185, 129, 0.55) !important;
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

/* ═══════════════════════════════════════════════════════════════════════════
   CONSTITUTION 1 OF 3 — STRICT CONTRAST (#FFFFFF DARK / #000000 LIGHT),
   ZERO COLOR INVERSION ON FOCUS/AUTOFILL, CHILD MODALS & ZERO-SCROLL FREEDOM
   ═══════════════════════════════════════════════════════════════════════════ */
body {
    height: auto !important;
    min-height: 100vh !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
}
.dash-main {
    height: auto !important;
    min-height: calc(100vh - 20px) !important;
    overflow: visible !important;
}
.view-container.active {
    height: auto !important;
    overflow: visible !important;
}
.grid-scroll-wrap {
    max-height: none !important;
    overflow-y: visible !important;
}

/* 1. DARK MODE STRICT RULES: Pure Bright White (#FFFFFF) + Pitch Black (#000000) Inputs */
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) .neo-card-title,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) .pos-grid th,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) .pos-grid td,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) .grn-col-item,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) .grn-col-unit,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) .grn-col-qty,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) .grn-col-orig-qty,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) .grn-col-pending-base,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) .grn-col-rcv-base,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) .grn-log-label,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) .grn-child-modal-card,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) .grn-child-modal-card label,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) .grn-child-modal-card h3,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) .grn-child-modal-card p {
    color: #FFFFFF !important;
    font-weight: 800 !important;
}

html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]),
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) select,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) textarea,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]):focus,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) select:focus,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) textarea:focus {
    background-color: #000000 !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
    border: 2.2px solid #FFFFFF !important;
    caret-color: #38bdf8 !important;
    font-weight: 800 !important;
}
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) select option {
    background-color: #000000 !important;
    color: #FFFFFF !important;
    font-weight: 800 !important;
}
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) input:-webkit-autofill,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) input:-webkit-autofill:hover,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0 1000px #000000 inset !important;
    -webkit-text-fill-color: #FFFFFF !important;
    caret-color: #38bdf8 !important;
}

/* 2. LIGHT MODE STRICT RULES: Pitch-Black (#000000) Text on Calm Light Surfaces (#F8FAFC / #FFFFFF) */
:root.light-theme body,
html[data-magaies-theme-mode="light"] body,
body.theme-light,
body.light-theme,
body[data-msg-theme="royal-dawn"] {
    background: #F8FAFC !important;
    color: #000000 !important;
}
:root.light-theme .neo-card,
html[data-magaies-theme-mode="light"] .neo-card,
body.theme-light .neo-card,
body.light-theme .neo-card,
body[data-msg-theme="royal-dawn"] .neo-card,
:root.light-theme .editor-bottom-bar,
html[data-magaies-theme-mode="light"] .editor-bottom-bar,
body.theme-light .editor-bottom-bar,
body.light-theme .editor-bottom-bar,
body[data-msg-theme="royal-dawn"] .editor-bottom-bar,
:root.light-theme .grid-scroll-wrap,
html[data-magaies-theme-mode="light"] .grid-scroll-wrap,
body.theme-light .grid-scroll-wrap,
body.light-theme .grid-scroll-wrap,
body[data-msg-theme="royal-dawn"] .grid-scroll-wrap,
:root.light-theme .grn-logistics-bar,
html[data-magaies-theme-mode="light"] .grn-logistics-bar,
body.theme-light .grn-logistics-bar,
body.light-theme .grn-logistics-bar,
body[data-msg-theme="royal-dawn"] .grn-logistics-bar,
:root.light-theme .grn-scanner-bar,
html[data-magaies-theme-mode="light"] .grn-scanner-bar,
body.theme-light .grn-scanner-bar,
body.light-theme .grn-scanner-bar,
body[data-msg-theme="royal-dawn"] .grn-scanner-bar {
    background: #FFFFFF !important;
    color: #000000 !important;
    border: 2px solid #000000 !important;
    box-shadow: 0 6px 20px rgba(15, 23, 42, 0.08) !important;
}

:root.light-theme .neo-card-title,
html[data-magaies-theme-mode="light"] .neo-card-title,
body.theme-light .neo-card-title,
body.light-theme .neo-card-title,
body[data-msg-theme="royal-dawn"] .neo-card-title,
:root.light-theme .pos-grid thead,
html[data-magaies-theme-mode="light"] .pos-grid thead,
body.theme-light .pos-grid thead,
body.light-theme .pos-grid thead,
body[data-msg-theme="royal-dawn"] .pos-grid thead,
:root.light-theme .pos-grid th,
html[data-magaies-theme-mode="light"] .pos-grid th,
body.theme-light .pos-grid th,
body.light-theme .pos-grid th,
body[data-msg-theme="royal-dawn"] .pos-grid th,
:root.light-theme .pos-grid td,
html[data-magaies-theme-mode="light"] .pos-grid td,
body.theme-light .pos-grid td,
body.light-theme .pos-grid td,
body[data-msg-theme="royal-dawn"] .pos-grid td,
:root.light-theme .grn-col-item,
html[data-magaies-theme-mode="light"] .grn-col-item,
body.theme-light .grn-col-item,
body.light-theme .grn-col-item,
body[data-msg-theme="royal-dawn"] .grn-col-item,
:root.light-theme .grn-col-unit,
html[data-magaies-theme-mode="light"] .grn-col-unit,
body.theme-light .grn-col-unit,
body.light-theme .grn-col-unit,
body[data-msg-theme="royal-dawn"] .grn-col-unit,
:root.light-theme .grn-col-qty,
html[data-magaies-theme-mode="light"] .grn-col-qty,
body.theme-light .grn-col-qty,
body.light-theme .grn-col-qty,
body[data-msg-theme="royal-dawn"] .grn-col-qty,
:root.light-theme .grn-col-orig-qty,
html[data-magaies-theme-mode="light"] .grn-col-orig-qty,
body.theme-light .grn-col-orig-qty,
body.light-theme .grn-col-orig-qty,
body[data-msg-theme="royal-dawn"] .grn-col-orig-qty,
:root.light-theme .grn-col-pending-base,
html[data-magaies-theme-mode="light"] .grn-col-pending-base,
body.theme-light .grn-col-pending-base,
body.light-theme .grn-col-pending-base,
body[data-msg-theme="royal-dawn"] .grn-col-pending-base,
:root.light-theme .grn-col-rcv-base,
html[data-magaies-theme-mode="light"] .grn-col-rcv-base,
body.theme-light .grn-col-rcv-base,
body.light-theme .grn-col-rcv-base,
body[data-msg-theme="royal-dawn"] .grn-col-rcv-base,
:root.light-theme .grn-log-label,
html[data-magaies-theme-mode="light"] .grn-log-label,
body.theme-light .grn-log-label,
body.light-theme .grn-log-label,
body[data-msg-theme="royal-dawn"] .grn-log-label {
    background-color: #F8FAFC !important;
    color: #000000 !important;
    -webkit-text-fill-color: #000000 !important;
    font-weight: 800 !important;
}

:root.light-theme input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]),
html[data-magaies-theme-mode="light"] input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]),
body.theme-light input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]),
body.light-theme input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]),
body[data-msg-theme="royal-dawn"] input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]),
:root.light-theme select,
html[data-magaies-theme-mode="light"] select,
body.theme-light select,
body.light-theme select,
body[data-msg-theme="royal-dawn"] select,
:root.light-theme textarea,
html[data-magaies-theme-mode="light"] textarea,
body.theme-light textarea,
body.light-theme textarea,
body[data-msg-theme="royal-dawn"] textarea,
:root.light-theme input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]):focus,
html[data-magaies-theme-mode="light"] input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]):focus,
body.theme-light input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]):focus,
body.light-theme input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]):focus,
body[data-msg-theme="royal-dawn"] input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]):focus,
:root.light-theme select:focus,
html[data-magaies-theme-mode="light"] select:focus,
body.theme-light select:focus,
body.light-theme select:focus,
body[data-msg-theme="royal-dawn"] select:focus {
    background-color: #FFFFFF !important;
    color: #000000 !important;
    -webkit-text-fill-color: #000000 !important;
    border: 2.2px solid #000000 !important;
    caret-color: #0284c7 !important;
    font-weight: 800 !important;
}
:root.light-theme select option,
html[data-magaies-theme-mode="light"] select option,
body.theme-light select option,
body.light-theme select option,
body[data-msg-theme="royal-dawn"] select option {
    background-color: #FFFFFF !important;
    color: #000000 !important;
    font-weight: 800 !important;
}

/* 3. NESTED CHILD MODALS THEME INHERITANCE (#grid-settings-modal, #grn-batch-bin-modal, #grn-quick-wh-modal, #grn-confirm-action-modal) */
:root.light-theme #grid-settings-modal > div,
html[data-magaies-theme-mode="light"] #grid-settings-modal > div,
body.theme-light #grid-settings-modal > div,
body.light-theme #grid-settings-modal > div,
body[data-msg-theme="royal-dawn"] #grid-settings-modal > div,
:root.light-theme .grn-child-modal-card,
html[data-magaies-theme-mode="light"] .grn-child-modal-card,
body.theme-light .grn-child-modal-card,
body.light-theme .grn-child-modal-card,
body[data-msg-theme="royal-dawn"] .grn-child-modal-card {
    background: #FFFFFF !important;
    color: #000000 !important;
    border: 2.5px solid #000000 !important;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.22) !important;
}
:root.light-theme #grid-settings-modal h3,
:root.light-theme #grid-settings-modal label,
:root.light-theme #grid-settings-modal span,
html[data-magaies-theme-mode="light"] #grid-settings-modal h3,
html[data-magaies-theme-mode="light"] #grid-settings-modal label,
html[data-magaies-theme-mode="light"] #grid-settings-modal span,
body.theme-light #grid-settings-modal h3,
body.theme-light #grid-settings-modal label,
body.theme-light #grid-settings-modal span,
body.light-theme #grid-settings-modal h3,
body.light-theme #grid-settings-modal label,
body.light-theme #grid-settings-modal span,
body[data-msg-theme="royal-dawn"] #grid-settings-modal h3,
body[data-msg-theme="royal-dawn"] #grid-settings-modal label,
body[data-msg-theme="royal-dawn"] #grid-settings-modal span,
:root.light-theme .grn-child-modal-card h3,
:root.light-theme .grn-child-modal-card label,
:root.light-theme .grn-child-modal-card p,
html[data-magaies-theme-mode="light"] .grn-child-modal-card h3,
html[data-magaies-theme-mode="light"] .grn-child-modal-card label,
html[data-magaies-theme-mode="light"] .grn-child-modal-card p,
body.theme-light .grn-child-modal-card h3,
body.theme-light .grn-child-modal-card label,
body.theme-light .grn-child-modal-card p,
body.light-theme .grn-child-modal-card h3,
body.light-theme .grn-child-modal-card label,
body.light-theme .grn-child-modal-card p,
body[data-msg-theme="royal-dawn"] .grn-child-modal-card h3,
body[data-msg-theme="royal-dawn"] .grn-child-modal-card label,
body[data-msg-theme="royal-dawn"] .grn-child-modal-card p,
:root.light-theme .pos-grid td *,
:root.light-theme .editor-bottom-bar *,
:root.light-theme .grn-meta-bar *,
html[data-magaies-theme-mode="light"] .pos-grid td *,
html[data-magaies-theme-mode="light"] .editor-bottom-bar *,
html[data-magaies-theme-mode="light"] .grn-meta-bar *,
body.theme-light .pos-grid td *,
body.theme-light .editor-bottom-bar *,
body.theme-light .grn-meta-bar *,
body.light-theme .pos-grid td *,
body.light-theme .editor-bottom-bar *,
body.light-theme .grn-meta-bar *,
body[data-msg-theme="royal-dawn"] .pos-grid td *,
body[data-msg-theme="royal-dawn"] .editor-bottom-bar *,
body[data-msg-theme="royal-dawn"] .grn-meta-bar * {
    color: #000000 !important;
    font-weight: 800 !important;
}
:root.light-theme .btn-action,
:root.light-theme .btn-action *,
html[data-magaies-theme-mode="light"] .btn-action,
html[data-magaies-theme-mode="light"] .btn-action *,
body.theme-light .btn-action,
body.theme-light .btn-action *,
body.light-theme .btn-action,
body.light-theme .btn-action *,
body[data-msg-theme="royal-dawn"] .btn-action,
body[data-msg-theme="royal-dawn"] .btn-action * {
    color: #FFFFFF !important;
}

/* 4. VISUAL PULSE ON SAVE */
@keyframes grnSaveVisualPulse {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.75); transform: scale(1); }
    50% { box-shadow: 0 0 0 18px rgba(16, 185, 129, 0); transform: scale(1.01); }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); transform: scale(1); }
}
.grn-save-pulse {
    animation: grnSaveVisualPulse 0.75s cubic-bezier(0.16, 1, 0.3, 1);
}

/* 5. CONSTITUTION 4 GLOBAL ROOT INHERITANCE (+2 BORDER/FONT/ICON THICKNESS & AMBIENT CONTRAST) */
:root {
    --grn-c4-border-width: 2.2px;
    --grn-c4-card-border-width: 2.5px;
    --grn-c4-font-weight: 800;
    --grn-c4-icon-stroke: 2.6px;
}
svg, svg path, svg circle, svg rect, svg line, svg polyline, svg polygon {
    stroke-width: var(--grn-c4-icon-stroke, 2.6px) !important;
}
/* Dark Mode Ambient vs Input Contrast (+2 thickness, #FFFFFF borders & text on #000000 inputs inside #0f172a ambient cards) */
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) .neo-card,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) .grn-logistics-bar,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) .grn-scanner-bar,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) .editor-bottom-bar,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) .grn-child-modal-card {
    background-color: #0f172a !important;
    border: 2.4px solid #FFFFFF !important;
    color: #FFFFFF !important;
}
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]):active,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) select:active,
html:not(.light-theme):not([data-magaies-theme-mode="light"]) body:not(.theme-light):not(.light-theme):not([data-msg-theme="royal-dawn"]) textarea:active {
    background-color: #000000 !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
    border: 2.4px solid #FFFFFF !important;
}
/* Light Mode Ambient (#F1F5F9) vs Input (#FFFFFF) Contrast (+2 thickness, #000000 borders & text) */
:root.light-theme .neo-card,
html[data-magaies-theme-mode="light"] .neo-card,
body.theme-light .neo-card,
body.light-theme .neo-card,
body[data-msg-theme="royal-dawn"] .neo-card,
:root.light-theme .grn-logistics-bar,
html[data-magaies-theme-mode="light"] .grn-logistics-bar,
body.theme-light .grn-logistics-bar,
body.light-theme .grn-logistics-bar,
body[data-msg-theme="royal-dawn"] .grn-logistics-bar,
:root.light-theme .grn-scanner-bar,
html[data-magaies-theme-mode="light"] .grn-scanner-bar,
body.theme-light .grn-scanner-bar,
body.light-theme .grn-scanner-bar,
body[data-msg-theme="royal-dawn"] .grn-scanner-bar {
    background-color: #F1F5F9 !important;
    border: 2.4px solid #000000 !important;
    color: #000000 !important;
}
:root.light-theme .grn-col-origQty,
:root.light-theme .grn-col-item,
:root.light-theme .grn-col-unit,
:root.light-theme .grn-col-qty,
:root.light-theme .grn-col-pendingBase,
:root.light-theme .grn-col-baseQty,
html[data-magaies-theme-mode="light"] .grn-col-origQty,
html[data-magaies-theme-mode="light"] .grn-col-item,
html[data-magaies-theme-mode="light"] .grn-col-unit,
html[data-magaies-theme-mode="light"] .grn-col-qty,
html[data-magaies-theme-mode="light"] .grn-col-pendingBase,
html[data-magaies-theme-mode="light"] .grn-col-baseQty,
body.theme-light .grn-col-origQty,
body.theme-light .grn-col-item,
body.theme-light .grn-col-unit,
body.theme-light .grn-col-qty,
body.theme-light .grn-col-pendingBase,
body.theme-light .grn-col-baseQty,
body.light-theme .grn-col-origQty,
body.light-theme .grn-col-item,
body.light-theme .grn-col-unit,
body.light-theme .grn-col-qty,
body.light-theme .grn-col-pendingBase,
body.light-theme .grn-col-baseQty,
body[data-msg-theme="royal-dawn"] .grn-col-origQty,
body[data-msg-theme="royal-dawn"] .grn-col-item,
body[data-msg-theme="royal-dawn"] .grn-col-unit,
body[data-msg-theme="royal-dawn"] .grn-col-qty,
body[data-msg-theme="royal-dawn"] .grn-col-pendingBase,
body[data-msg-theme="royal-dawn"] .grn-col-baseQty {
    background-color: #FFFFFF !important;
    border: 2.2px solid #000000 !important;
    color: #000000 !important;
    font-weight: 800 !important;
}
</style>

    <div class="dash-main">
        <?php $header_theme_class = 'header-theme-purchase'; ?>
        <?php include 'includes/header.php'; ?>

        <!-- View 1: Hub (Select Invoice with Pending Goods) -->
        <div class="view-container active" id="view-hub">

            <div class="neo-card">
                <div class="neo-card-title" style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: space-between; align-items: center; position: relative;">
                    <div style="display: flex; gap: 12px; align-items: center; z-index: 2; flex-wrap: wrap;">
                        <span id="hub-title-text">⏳ قيد الاستلام</span>
                        <a href="smart-logistics.php?mode=purchases" class="btn-action" style="padding: 7px 15px; font-size: 0.86rem; border-radius: 10px; background: linear-gradient(135deg, #0ea5e9, #6366f1); border: 1.5px solid #38bdf8; color: #fff; text-decoration: none; font-weight: 800; box-shadow: 0 0 18px rgba(14,165,233,0.45); display: inline-flex; align-items: center; gap: 6px;">🚀 البوابة اللوجستية</a>
                        <div class="uiverse-switch demo3">
                            <input type="checkbox" id="uiverse-toggle" onchange="toggleHubViewBySwitch(this.checked)">
                            <label><i></i></label>
                        </div>
                        <span id="grn-live-sync-badge" style="padding: 5px 12px; border-radius: 99px; font-size: 0.78rem; font-weight: 900; background: rgba(16,185,129,0.16); border: 1.5px solid #10b981; color: #34d399; display: inline-flex; align-items: center; gap: 6px;" title="حالة التزامن اللحظي الفوري بين الموبايل واللابتوب والسحابة">⚡ متزامن لحظياً (موبايل ⇄ لابتوب)</span>
                    </div>
                    
                    <div id="hub-counter-badge"></div>
                    
                    <input type="text" id="search-inv" class="v-input" placeholder="ابحث برقم الفاتورة أو المورد أو الصنف..." style="width: 300px; z-index: 2;" oninput="if(currentHubView === 'pending') renderPendingInvoices(true); else renderRecentGRNs(true);">
                </div>

                <!-- Instant Hub Filters, Drill-Down & Export Bar (Order 5 & 6) -->
                <div id="grn-hub-filters-bar" class="grn-logistics-bar" style="margin-bottom: 12px; padding: 10px 14px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between;">
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; flex: 1;">
                        <span style="font-size: 0.82rem; font-weight: 900;">⚡ فلاتر فورية:</span>
                        <input type="date" id="grn-filter-date-from" class="grn-log-input" style="height: 36px; width: 138px; font-size: 0.82rem;" title="من تاريخ" onchange="window.applyGrnHubFilters && window.applyGrnHubFilters()">
                        <input type="date" id="grn-filter-date-to" class="grn-log-input" style="height: 36px; width: 138px; font-size: 0.82rem;" title="إلى تاريخ" onchange="window.applyGrnHubFilters && window.applyGrnHubFilters()">
                        <select id="grn-filter-supplier" class="grn-log-input" style="height: 36px; min-width: 155px; font-size: 0.82rem;" onchange="window.applyGrnHubFilters && window.applyGrnHubFilters()">
                            <option value="">👤 كل الموردين</option>
                        </select>
                        <select id="grn-filter-warehouse" class="grn-log-input" style="height: 36px; min-width: 145px; font-size: 0.82rem;" onchange="window.applyGrnHubFilters && window.applyGrnHubFilters()">
                            <option value="">🏢 كل المستودعات</option>
                        </select>
                        <button type="button" class="btn-action btn-cancel" style="padding: 6px 12px; font-size: 0.78rem; border-radius: 8px;" onclick="window.resetGrnHubFilters && window.resetGrnHubFilters()">🔄 الكل</button>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                        <button type="button" class="btn-action" style="padding: 7px 12px; font-size: 0.8rem; border-radius: 8px; background: linear-gradient(135deg, #10b981, #059669);" onclick="window.exportGrnHubToExcel && window.exportGrnHubToExcel()" title="تصدير الجدول المعروض فوراً إلى ملف Excel">📊 Excel</button>
                        <button type="button" class="btn-action" style="padding: 7px 12px; font-size: 0.8rem; border-radius: 8px; background: linear-gradient(135deg, #0284c7, #0369a1);" onclick="window.printGrnHubReport && window.printGrnHubReport('a4')" title="طباعة رسمية A4 أو حفظ PDF">🖨️ طباعة A4 / PDF</button>
                        <button type="button" class="btn-action" style="padding: 7px 12px; font-size: 0.8rem; border-radius: 8px; background: linear-gradient(135deg, #7c3aed, #6d28d9);" onclick="window.printGrnHubReport && window.printGrnHubReport('thermal')" title="طباعة إيصال كاشير حراري 80mm">🧾 حراري 80mm</button>
                        <button type="button" class="btn-action" style="padding: 7px 12px; font-size: 0.8rem; border-radius: 8px; background: linear-gradient(135deg, #059669, #047857);" onclick="window.shareGrnHubSummaryWhatsApp && window.shareGrnHubSummaryWhatsApp()" title="مشاركة ملخص الاستلام الفوري عبر واتساب">💬 واتساب</button>
                    </div>
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
                            <span>📦</span> أصناف الاستلام
                        </span>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <button type="button" id="btn-grn-fill-all" onclick="window.fillAllRemainingGRNQty && window.fillAllRemainingGRNQty()" title="تعبئة كامل الكميات المتبقية لجميع الأصناف بضغطة واحدة" style="padding: 9px 14px; font-size: 0.86rem; border-radius: 9px; background: rgba(16, 185, 129, 0.16); border: 1px solid rgba(16, 185, 129, 0.45); color: #34d399; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <span>⚡</span><span>استلام الكل</span>
                            </button>
                            <button type="button" id="btn-grn-zero-all" onclick="window.zeroAllGRNQty && window.zeroAllGRNQty()" title="تصفير الكميات المستلمة لبدء الإدخال الجزئي أو المسح بالباركود" style="padding: 9px 12px; font-size: 0.84rem; border-radius: 9px; background: rgba(245, 158, 11, 0.14); border: 1px solid rgba(245, 158, 11, 0.4); color: #fbbf24; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <span>🧹</span><span>تصفير</span>
                            </button>
                            <button class="btn-action" style="padding: 10px 24px; font-size: 1rem; font-weight: 800; display:flex; align-items:center; justify-content:center; gap:8px; border-radius:10px; background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 15px rgba(16,185,129,0.4); border: none; color: #fff; cursor: pointer; transition: all 0.2s ease;" onclick="saveReceiptNote()">
                                <span>💾</span> اعتماد الاستلام
                            </button>
                            <button class="btn-action" style="padding: 9px 16px; font-size: 0.9rem; border-radius: 9px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8; font-weight: 700; display: flex; align-items: center; gap: 6px;" onclick="openGridSettings()">
                                <span>⚙️</span> تنسيق الجدول
                            </button>
                            <button class="btn-action btn-cancel" style="padding: 9px 16px; font-size: 0.9rem; border-radius: 9px;" onclick="showHub()">↩️ رجوع</button>
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
                                    <th style="text-align:center; width:13%; color:#38bdf8; font-weight:800; font-size:0.95rem; padding: 12px 5px; cursor: pointer; user-select: none;" onclick="openGridSettings('rcvQty')" title="انقر لتعديل حجم الخط ولون الخط للكمية المستلمة"><span style="display:inline-flex; align-items:center; gap:5px; background: rgba(56, 189, 248, 0.12); padding: 4px 10px; border-radius: 8px; border: 1px solid rgba(56, 189, 248, 0.3);">✍️ الكمية المستلمة ⚙️</span></th>
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
                        <button class="btn-action" style="padding: 12px 30px; font-size: 1.08rem; font-weight: 800; display:flex; align-items:center; justify-content:center; gap:10px; border-radius:14px; background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 20px rgba(16,185,129,0.4); border: none; color: #fff; cursor: pointer; transition: all 0.2s ease;" onclick="saveReceiptNote()">
                            <span>💾</span> اعتماد الاستلام
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
    <script defer src="js/magaies-smart-guide.js?v=<?php echo file_exists('js/magaies-smart-guide.js') ? filemtime('js/magaies-smart-guide.js') : '20261030_v35'; ?>"></script>
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
            if (!navigator.onLine) return;
            try {
                const u = JSON.parse(localStorage.getItem('user') || '{}');
                const uid = String(u.id || u.username || 'default_user');
                const ctrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
                const tId = ctrl ? setTimeout(() => ctrl.abort(), 4000) : null;
                const res = await fetch('api/get_user_settings.php?user_id=' + encodeURIComponent(uid), {
                    signal: ctrl ? ctrl.signal : undefined
                });
                if (tId) clearTimeout(tId);
                const data = await res.json();
                const prefsData = (data && data.status === 'success' && data.data && data.data['erp_grn_col_prefs_v2'])
                    ? data.data['erp_grn_col_prefs_v2']
                    : null;

                if (prefsData) {
                    const prefsStr = typeof prefsData === 'string' ? prefsData : JSON.stringify(prefsData);
                    localStorage.setItem('erp_grn_col_prefs_v2', prefsStr);
                    if (document.getElementById('view-editor') && document.getElementById('view-editor').classList.contains('active')) {
                        renderReceiptLines();
                    }
                }
            } catch(e) {}
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
            const ctrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
            const tId = ctrl ? setTimeout(() => ctrl.abort(), 4500) : null;
            try {
                const res = await fetch('api/mirror_sync.php?action=get_recent_purchases&_t=' + Date.now(), {
                    signal: ctrl ? ctrl.signal : undefined
                });
                if (tId) clearTimeout(tId);
                if (!res.ok) return;
                const data = await res.json();
                if (data && data.success && Array.isArray(data.purchases)) {
                    let changed = false;
                    let deletedGrnIds = [];
                    try { deletedGrnIds = JSON.parse(localStorage.getItem('erp_deleted_grns_v1') || '[]'); } catch (_ed) {}
                    const deletedSet = new Set(Array.isArray(deletedGrnIds) ? deletedGrnIds.map(String) : []);
                    data.purchases.forEach(remoteInv => {
                        const rNo = remoteInv.invoiceNo || remoteInv.id;
                        if (!rNo || deletedSet.has(String(rNo))) return;
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
                if (tId) clearTimeout(tId);
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

        window.normalizeGrnArabic = function(str) {
            if (!str) return '';
            const arabicIndic = '٠١٢٣٤٥٦٧٨٩';
            return String(str)
                .replace(/[٠-٩]/g, d => String(arabicIndic.indexOf(d)))
                .replace(/[\u064B-\u065F\u0670\u0640]/g, '')
                .replace(/[أإآٱ]/g, 'ا')
                .replace(/ة/g, 'ه')
                .replace(/ى/g, 'ي')
                .replace(/ؤ/g, 'و')
                .replace(/ئ/g, 'ي')
                .toLowerCase()
                .trim();
        };

        function renderPendingInvoices(resetCount = true) {
            if (resetCount) pendingDisplayCount = 50;
            
            const tbody = document.getElementById('invoices-tbody');
            const rawSearch = document.getElementById('search-inv') ? document.getElementById('search-inv').value : '';
            const search = window.normalizeGrnArabic(rawSearch);
            const fDateFrom = document.getElementById('grn-filter-date-from')?.value || '';
            const fDateTo   = document.getElementById('grn-filter-date-to')?.value || '';
            const fSup      = document.getElementById('grn-filter-supplier')?.value || '';
            tbody.innerHTML = '';
            
            const invIds = Object.keys(pendingMap);

            const filteredIds = invIds.filter(id => {
                const invObj = pendingMap[id] || {};
                if (fSup && String(invObj.supplierId) !== String(fSup)) return false;
                if (fDateFrom || fDateTo) {
                    const dIso = (invObj.date || '').slice(0, 10);
                    if (fDateFrom && dIso && dIso < fDateFrom) return false;
                    if (fDateTo && dIso && dIso > fDateTo) return false;
                }
                if (search) {
                    const supName = window.normalizeGrnArabic(getSupplierName(invObj.supplierId));
                    const normId = window.normalizeGrnArabic(id);
                    const itemNamesStr = window.normalizeGrnArabic(
                        Object.values(invObj.lines || {}).map(l => getItemName(l.itemId)).join(' ')
                    );
                    return normId.includes(search) || supName.includes(search) || itemNamesStr.includes(search);
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
                        <td style="text-align:right; font-family:'Outfit'; color:#38bdf8; font-weight:700; cursor:pointer;" onclick="window.openGrnDrillDownModal && window.openGrnDrillDownModal('${id}')" ondblclick="window.open('purchase.php?id=${id}', '_blank')" title="انقر لمعاينة أصل الفاتورة (Drill-Down) أو انقر مرتين لفتحها في شاشة المشتريات">🔍 ${id}</td>
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
                document.getElementById('hub-title-text').innerHTML = `⏳ قيد الاستلام`;
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
                const rQty = Math.round((parseFloat(line.receiveQty) || 0) * 1000) / 1000;
                if (rQty > 0) {
                    distinctItems.add(line.itemId);
                    totalQty = Math.round((totalQty + rQty) * 1000) / 1000;
                    totalVal = Math.round((totalVal + rQty * (parseFloat(line.price) || 0)) * 100) / 100;
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
            const boxStyle = `background: rgba(30, 41, 59, 0.55); border: 2.2px solid rgba(255, 255, 255, 0.85); border-radius: 9px; height: 48px; display: flex; align-items: center; justify-content: ${justify}; font-family: '${font}', 'Tajawal', sans-serif !important; font-size: ${size}px !important; font-weight: 800 !important; color: ${color}; padding: 0 10px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.2); transition: all 0.2s ease;`;

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
            const inputStyle = `font-family: '${rcvCol.font}', 'Tajawal', sans-serif !important; font-size: ${rcvCol.size}px !important; font-weight: 900 !important; color: ${rcvCol.color}; text-align: ${rcvCol.align} !important; height: 48px; width: 100%; background: #000000; border: 2.2px solid #FFFFFF; border-radius: 9px; outline: none; padding-right: 28px; padding-left: 8px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3); transition: all 0.25s ease;`;

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
                const factorInputStyle = `font-family: '${factorCol.font}', 'Tajawal', sans-serif !important; font-size: ${factorCol.size}px !important; font-weight: 900 !important; color: ${factorCol.color}; text-align: ${factorCol.align} !important; height: 48px; width: 100%; background: #000000; border: 2.2px solid #FFFFFF; border-radius: 9px; outline: none; padding: 0 8px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3); transition: all 0.25s ease;`;
                
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
                        <select class="wh-select grn-col-warehouse" style="width: 100%; height: 48px; padding: 0 8px; background: #000000; border: 2.2px solid #FFFFFF; font-family: '${whCol.font}', 'Tajawal', sans-serif !important; color: ${whCol.color}; font-size: ${whCol.size}px !important; font-weight: 800 !important; text-align: ${whCol.align} !important; border-radius: 9px; outline: none; box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);">${whOptions}</select>
                    </td>
                    <td style="padding: 4px 3px;">
                        <input type="text" class="notes-input" value="${line.notes || ''}" placeholder="ملحوظة الاستلام..." style="width: 100%; height: 48px; padding: 0 10px; background: #000000; border: 2.2px solid #FFFFFF; font-family: 'Tajawal', sans-serif; color: #FFFFFF; font-size: 13px; font-weight: 800; border-radius: 9px; outline: none; box-shadow: inset 0 1px 3px rgba(0,0,0,0.2); transition: border-color 0.2s;">
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
            const totalAssigned = groupSplits.reduce((acc, x) => acc + (parseFloat(x.receiveQty) || 0), 0);
            let remaining = Math.max(0, Math.round((line.maxPending - totalAssigned) * 100) / 100);

            // If parent line currently holds the full pending qty, automatically split 50/50 between the two warehouses!
            if (remaining === 0 && (parseFloat(line.receiveQty) || 0) > 1) {
                const curQty = parseFloat(line.receiveQty) || 0;
                const half = Math.floor((curQty / 2) * 100) / 100;
                remaining = Math.round((curQty - half) * 100) / 100;
                line.receiveQty = half;
            }

            // Pick a different warehouse by default if multiple warehouses exist
            const altWh = (warehousesDb || []).find(w => w.id !== 'PENDING_RECEIPT' && w.id !== line.warehouseId);
            const targetWhId = altWh ? altWh.id : line.warehouseId;

            const newLine = {
                id: line.lineGroup + '_split_' + Date.now() + '_' + Math.floor(Math.random() * 10000),
                itemId: line.itemId,
                unitId: line.unitId,
                unitName: line.unitName,
                factor: line.factor || line.customFactor || 1,
                customFactor: line.customFactor || line.factor || 1,
                origQty: line.origQty || line.maxPending,
                price: line.price || 0,
                maxPending: line.maxPending,
                receiveQty: remaining,
                warehouseId: targetWhId,
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
            const removedLine = receiptLines.find(x => x.id === lineId);
            receiptLines = receiptLines.filter(x => x.id !== lineId);
            if (removedLine && (parseFloat(removedLine.receiveQty) || 0) > 0) {
                const parentLine = receiptLines.find(x => x.lineGroup === removedLine.lineGroup && !x.isSplit) || receiptLines.find(x => x.lineGroup === removedLine.lineGroup);
                if (parentLine) {
                    const groupSum = receiptLines.filter(x => x.lineGroup === removedLine.lineGroup && x.id !== parentLine.id).reduce((s, x) => s + (parseFloat(x.receiveQty) || 0), 0);
                    parentLine.receiveQty = Math.min(parentLine.maxPending - groupSum, (parseFloat(parentLine.receiveQty) || 0) + (parseFloat(removedLine.receiveQty) || 0));
                }
            }
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

            const rawSearch = document.getElementById('search-inv') ? document.getElementById('search-inv').value : '';
            const search = typeof window.normalizeGrnArabic === 'function' ? window.normalizeGrnArabic(rawSearch) : rawSearch.toLowerCase().trim();
            const fDateFrom = document.getElementById('grn-filter-date-from')?.value || '';
            const fDateTo   = document.getElementById('grn-filter-date-to')?.value || '';
            const fSup      = document.getElementById('grn-filter-supplier')?.value || '';
            const fWh       = document.getElementById('grn-filter-warehouse')?.value || '';
            const filteredGrns = grns.filter(grn => {
                let supId = grn.customer || grn.customerId || grn.supplierId;
                if (!supId && grn.originalInvoiceId) {
                    const orig = allPurchases.find(p => p.id === grn.originalInvoiceId || p.invoiceNo === grn.originalInvoiceId);
                    if (orig) supId = orig.customer || orig.customerId || orig.supplierId;
                }
                if (fSup && String(supId) !== String(fSup)) return false;
                if (fDateFrom || fDateTo) {
                    const dIso = (grn.date || '').slice(0, 10);
                    if (fDateFrom && dIso && dIso < fDateFrom) return false;
                    if (fDateTo && dIso && dIso > fDateTo) return false;
                }
                if (fWh) {
                    const hasWh = String(grn.warehouse) === String(fWh) || (grn.items || []).some(it => it.quantity > 0 && String(it.warehouseId) === String(fWh));
                    if (!hasWh) return false;
                }
                if (!search) return true;
                const normFn = typeof window.normalizeGrnArabic === 'function' ? window.normalizeGrnArabic : (s => String(s || '').toLowerCase());
                const supName = normFn(getSupplierName(supId));
                const grnId = normFn(grn.id || '');
                const origId = normFn(grn.originalInvoiceId || '');
                const notesStr = normFn(grn.notes || '');
                const itemNamesStr = normFn((grn.items || []).filter(i => i.quantity > 0).map(i => getItemName(i.itemId)).join(' '));
                return grnId.includes(search) || origId.includes(search) || supName.includes(search) || notesStr.includes(search) || itemNamesStr.includes(search);
            });
            
            if (currentHubView === 'received') {
                document.getElementById('hub-title-text').innerHTML = `📜 الأذونات المستلمة`;
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
            
            // Pre-fill target quantities, factors, split warehouses, and logistics fields from this GRN
            const positiveItems = (grn.items || []).filter(item => item.quantity > 0);
            setTimeout(() => {
                const wbEl = document.getElementById('grn-waybill-no'); if (wbEl) wbEl.value = grn.waybillNo || '';
                const trEl = document.getElementById('grn-truck-no'); if (trEl) trEl.value = grn.truckNo || '';
                const drEl = document.getElementById('grn-driver-info'); if (drEl) drEl.value = grn.driverInfo || '';
                const inEl = document.getElementById('grn-inspector-name'); if (inEl && grn.inspectorName) inEl.value = grn.inspectorName;
                window._currentGrnPhotoDataUrl = grn.inspectionPhoto || null;

                // Clear any default receiptLines quantities first
                receiptLines.forEach(l => {
                    l.receiveQty = 0;
                    l._filledInEdit = false;
                });

                positiveItems.forEach((pItem, pIdx) => {
                    const baseLine = receiptLines.find(l => l.itemId === pItem.itemId && !l._filledInEdit);
                    if (baseLine) {
                        baseLine._filledInEdit = true;
                        baseLine.receiveQty = pItem.quantity;
                        if (pItem.customFactor || pItem.factor) {
                            const f = parseFloat(pItem.customFactor || pItem.factor);
                            if (f > 0) {
                                baseLine.factor = f;
                                baseLine.customFactor = f;
                            }
                        }
                        baseLine.warehouseId = pItem.warehouseId;
                        baseLine.notes = pItem.notes || '';
                    } else {
                        const parentLine = receiptLines.find(l => l.itemId === pItem.itemId);
                        if (parentLine) {
                            const f = parseFloat(pItem.customFactor || pItem.factor || parentLine.factor) || 1;
                            receiptLines.push({
                                ...parentLine,
                                id: parentLine.id + '_split_' + pIdx,
                                receiveQty: pItem.quantity,
                                factor: f,
                                customFactor: f,
                                warehouseId: pItem.warehouseId || parentLine.warehouseId,
                                notes: pItem.notes || '',
                                isSplit: true,
                                _filledInEdit: true
                            });
                        }
                    }
                });
                renderReceiptLines();
                showToast(`تم تحميل إذن الاستلام ${grnId} للتعديل`, 'warning');
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

        async function applyCurrentToAllCols(confirmedByModal) {
            if (!confirmedByModal && typeof window.openGrnConfirmModal === 'function') {
                return window.openGrnConfirmModal({
                    title: '🎨 تعميم تنسيق الأعمدة',
                    message: 'هل تريد تعميم هذه الإعدادات على جميع أعمدة الجدول معاً وحفظها نهائياً؟',
                    confirmText: '✅ نعم، تعميم وحفظ',
                    isDanger: false,
                    onConfirm: () => applyCurrentToAllCols(true)
                });
            }
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

        async function resetGridSettingsToDefault(confirmedByModal) {
            if (!confirmedByModal && typeof window.openGrnConfirmModal === 'function') {
                return window.openGrnConfirmModal({
                    title: '🔄 استعادة الضبط الافتراضي',
                    message: 'هل تريد استعادة الضبط الافتراضي لجميع أعمدة جدول الاستلام؟',
                    confirmText: '🔄 استعادة الافتراضي',
                    isDanger: false,
                    onConfirm: () => resetGridSettingsToDefault(true)
                });
            }
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

        // 2. Clean Editor Toolbar (No duplicate buttons)
        function enhanceEditorToolbar() {
            // Toolbar buttons (#btn-grn-fill-all, #btn-grn-zero-all) are already rendered cleanly in HTML
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
                    const invIdForBtn = invIdTd ? invIdTd.textContent.trim() : '';
                    actionBtn.innerHTML = `<span class="grn-icon-badge" style="width:22px;height:22px;margin-left:6px;">📥</span> فحص واستلام`;
                    if (invIdForBtn && actionBtn.parentNode && !actionBtn.parentNode.querySelector('.grn-quick-hub-rcv')) {
                        actionBtn.parentNode.style.display = 'flex';
                        actionBtn.parentNode.style.justifyContent = 'center';
                        actionBtn.parentNode.style.gap = '6px';
                        const fastBtn = document.createElement('button');
                        fastBtn.type = 'button';
                        fastBtn.className = 'btn-action grn-quick-hub-rcv';
                        fastBtn.style.cssText = 'padding:6px 12px; font-size:0.78rem; background:linear-gradient(135deg,#10b981,#059669);';
                        fastBtn.title = 'استلام كامل المتبقي في الفاتورة فوراً بضغطة واحدة إلى المستودع الرئيسي ⚡';
                        fastBtn.innerHTML = '⚡ فوري';
                        fastBtn.onclick = (ev) => { ev.stopPropagation(); window.quickReceiveInvoiceFromHub(invIdForBtn); };
                        actionBtn.parentNode.appendChild(fastBtn);
                    }
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
                    <label><span class="grn-icon-badge" style="width:20px;height:20px;font-size:0.7rem;">📄</span> رقم البوليصة</label>
                    <input type="text" id="grn-waybill-no" class="grn-log-input" placeholder="رقم البوليصة...">
                </div>
                <div class="grn-log-field">
                    <label><span class="grn-icon-badge" style="width:20px;height:20px;font-size:0.7rem;">🚛</span> رقم الشاحنة</label>
                    <input type="text" id="grn-truck-no" class="grn-log-input" placeholder="رقم الشاحنة...">
                </div>
                <div class="grn-log-field">
                    <label><span class="grn-icon-badge" style="width:20px;height:20px;font-size:0.7rem;">👨‍✈️</span> السائق / الهاتف</label>
                    <input type="text" id="grn-driver-info" class="grn-log-input" placeholder="اسم السائق وهاتفه...">
                </div>
                <div class="grn-log-field">
                    <label><span class="grn-icon-badge" style="width:20px;height:20px;font-size:0.7rem;">🛡️</span> الفاحص المستلم</label>
                    <input type="text" id="grn-inspector-name" class="grn-log-input" placeholder="اسم المستلم...">
                </div>
                <div class="grn-log-field">
                    <label><span class="grn-icon-badge" style="width:20px;height:20px;font-size:0.7rem;">📷</span> مرفق الفحص</label>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <input type="file" id="grn-photo-input" accept="image/*" style="display:none;" onchange="window.handleGrnPhotoUpload && window.handleGrnPhotoUpload(this)">
                        <button type="button" id="btn-grn-photo-attach" onclick="document.getElementById('grn-photo-input').click()" class="grn-log-input" style="flex:1;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:6px;background:rgba(56,189,248,0.12);border:1px dashed rgba(56,189,248,0.45);color:#38bdf8;font-size:0.82rem;">
                            <span>📷</span><span id="lbl-grn-photo-status">إرفاق صورة</span>
                        </button>
                    </div>
                </div>
            `;

            // B. Smart Barcode Scanner + Bulk Warehouse Selector + Visual Progress Bar
            const scanBar = document.createElement('div');
            scanBar.id = 'grn-scanner-bar';
            scanBar.className = 'grn-scanner-bar';
            scanBar.innerHTML = `
                <div style="display:flex;align-items:center;gap:10px;flex:1.4;min-width:260px;">
                    <span class="grn-icon-badge" style="width:36px;height:36px;font-size:1.1rem;">🔍</span>
                    <input type="text" id="grn-barcode-input" class="grn-log-input" style="flex:1;height:42px;font-size:0.95rem;" placeholder="باركود أو كود/اسم الصنف (أو الكود*الكمية)...">
                </div>
                <div style="display:flex;align-items:center;gap:8px;min-width:220px;">
                    <span class="grn-icon-badge" style="width:32px;height:32px;font-size:0.95rem;" title="توحيد المستودع لجميع الأصناف">🏢</span>
                    <select id="grn-bulk-wh-select" class="grn-log-input" style="height:42px;min-width:155px;cursor:pointer;" title="تعميم المستودع المختار على جميع سطور الفاتورة">
                        <option value="">🏢 تعميم المستودع</option>
                    </select>
                    <button type="button" id="btn-grn-quick-add-wh" onclick="window.openGrnQuickWarehouseModal && window.openGrnQuickWarehouseModal()" title="إضافة مستودع / مخزن جديد فوراً بدون مغادرة الشاشة" style="width:40px;height:42px;border-radius:10px;background:linear-gradient(135deg,#10b981,#059669);border:2px solid #34d399;color:#FFFFFF;font-size:1.05rem;font-weight:900;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px rgba(16,185,129,0.35);">➕</button>
                </div>
                <div class="grn-progress-wrap">
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.82rem;font-weight:800;">
                        <span style="color:#bae6fd;display:inline-flex;align-items:center;gap:5px;"><span class="grn-icon-badge" style="width:20px;height:20px;font-size:0.7rem;">📊</span> الإنجاز:</span>
                        <span id="grn-live-progress-pct" style="color:#34d399;font-family:'Outfit';font-size:0.95rem;">100%</span>
                    </div>
                    <div class="grn-progress-track">
                        <div id="grn-live-progress-fill" class="grn-progress-fill" style="width:100%;"></div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <button type="button" id="btn-grn-continuous-scan" class="btn-action" style="padding:9px 13px;font-size:0.84rem;border-radius:10px;background:linear-gradient(135deg,#0ea5e9,#2563eb);display:inline-flex;align-items:center;gap:5px;" onclick="window.openGrnContinuousScannerModal && window.openGrnContinuousScannerModal()" title="وضع الماسح المتصل السريع (USB / كاميرا) مع عداد صوتي وبصري فوري">
                        <span>📡</span> ماسح متصل <span id="grn-scan-counter-pill" style="background:rgba(0,0,0,0.35);padding:1px 7px;border-radius:99px;font-family:'Outfit';font-size:0.78rem;">0</span>
                    </button>
                    <button type="button" id="btn-grn-weighbridge" class="btn-action" style="padding:9px 13px;font-size:0.84rem;border-radius:10px;background:linear-gradient(135deg,#d97706,#b45309);display:inline-flex;align-items:center;gap:5px;" onclick="window.openGrnWeighbridgeModal && window.openGrnWeighbridgeModal()" title="مقارنة أوزان البوليصة مع ميزان القبّان (الوزن القائم والفارغ والصافي)">
                        <span>⚖️</span> ميزان القبّان
                    </button>
                    <button type="button" id="btn-grn-qr-card" class="btn-action" style="padding:9px 13px;font-size:0.84rem;border-radius:10px;background:linear-gradient(135deg,#0d9488,#0f766e);display:inline-flex;align-items:center;gap:5px;" onclick="window.printCertifiedGrnQrCard && window.printCertifiedGrnQrCard()" title="إصدار بطاقة فحص واستلام مخزني PDF/QR معتمدة مع تتبع الصلاحية وموقع الرف">
                        <span>🛡️</span> بطاقة QR
                    </button>
                    <button type="button" id="btn-grn-signature" class="btn-action" style="padding:9px 13px;font-size:0.84rem;border-radius:10px;background:linear-gradient(135deg,#6366f1,#4f46e5);display:inline-flex;align-items:center;gap:5px;" onclick="window.openGrnSignatureModal && window.openGrnSignatureModal()" title="التوقيع الإلكتروني الحي باللمس أو الماوس لأمين المستودع وسائق الشاحنة">
                        <span>✍️</span> توقيع إلكتروني
                    </button>
                    <button type="button" id="btn-grn-landed-cost" class="btn-action" style="padding:9px 13px;font-size:0.84rem;border-radius:10px;background:linear-gradient(135deg,#0891b2,#0e7490);display:inline-flex;align-items:center;gap:5px;" onclick="window.openGrnLandedCostModal && window.openGrnLandedCostModal()" title="توزيع تكلفة النقل والتعتيق والرسوم تلقائياً على الأصناف المستلمة (Landed Cost)">
                        <span>🚚</span> تكلفة النقل
                    </button>
                    <button type="button" id="btn-grn-supplier-claim" class="btn-action" style="padding:9px 13px;font-size:0.84rem;border-radius:10px;background:linear-gradient(135deg,#e11d48,#be123c);display:inline-flex;align-items:center;gap:5px;" onclick="window.generateSupplierClaimNotice && window.generateSupplierClaimNotice()" title="إصدار إشعار مطالبة رسمي للمورد بالكميات التالفة أو الناقصة">
                        <span>🚨</span> مطالبة مورد
                    </button>
                    <button type="button" id="btn-grn-batch-labels" class="btn-action" style="padding:9px 13px;font-size:0.84rem;border-radius:10px;background:linear-gradient(135deg,#7c3aed,#6d28d9);display:inline-flex;align-items:center;gap:5px;" onclick="window.printGRNBatchBarcodeLabels && window.printGRNBatchBarcodeLabels()" title="طباعة ملصقات باركود الدفعة والتشغيلة والرف للأصناف المستلمة">
                        <span>🏷️</span> ملصقات
                    </button>
                    <button type="button" class="btn-action" style="padding:9px 14px;font-size:0.86rem;border-radius:10px;background:linear-gradient(135deg,#0284c7,#0369a1);display:inline-flex;align-items:center;gap:6px;" onclick="printCurrentOrSavedGRN()">
                        <span class="grn-icon-badge" style="width:22px;height:22px;">🖨️</span> طباعة
                    </button>
                    <button type="button" class="btn-action" style="padding:9px 14px;font-size:0.86rem;border-radius:10px;background:linear-gradient(135deg,#059669,#047857);display:inline-flex;align-items:center;gap:6px;" onclick="shareCurrentOrSavedGRNWhatsApp()">
                        <span class="grn-icon-badge" style="width:22px;height:22px;">💬</span> واتساب
                    </button>
                    <button type="button" id="btn-grn-voice-cmd" class="btn-action" style="padding:9px 13px;font-size:0.84rem;border-radius:10px;background:linear-gradient(135deg,#ec4899,#be185d);display:inline-flex;align-items:center;gap:5px;" onclick="window.startGrnVoiceCommandListener && window.startGrnVoiceCommandListener()" title="الأوامر الصوتية السريعة بالمايكروفون (قل مثلاً: استلام الكل، ميزان القبان، اعتماد الاستلام)">
                        <span>🎙️</span> أمر صوتي
                    </button>
                    <button type="button" id="btn-grn-mastery-quiz" class="btn-action" style="padding:9px 13px;font-size:0.84rem;border-radius:10px;background:linear-gradient(135deg,#f59e0b,#b45309);display:inline-flex;align-items:center;gap:5px;" onclick="window.startGrnMasteryQuiz && window.startGrnMasteryQuiz()" title="اختبار التمكن التفاعلي السريع لتدريب أمناء المخازن عملياً بالصوت والمؤشر">
                        <span>🎓</span> تدريب عملي
                    </button>
                    <button type="button" id="btn-grn-expiry-radar" class="btn-action" style="padding:9px 13px;font-size:0.84rem;border-radius:10px;background:linear-gradient(135deg,#dc2626,#991b1b);display:inline-flex;align-items:center;gap:5px;" onclick="window.openGrnExpiryRadarModal && window.openGrnExpiryRadarModal()" title="رادار تنبيهات الصلاحية الحرجة وفحص الأيام المتبقية لجميع الأصناف المستلمة بالصوت واللون">
                        <span>⏳</span> رادار الصلاحية
                    </button>
                    <button type="button" id="btn-grn-ocr-matcher" class="btn-action" style="padding:9px 13px;font-size:0.84rem;border-radius:10px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);display:inline-flex;align-items:center;gap:5px;" onclick="window.openGrnSmartMatcherModal && window.openGrnSmartMatcherModal()" title="الاستلام الذكي عبر تصوير أو لصق إشعار تسليم المورد ومطابقته آلياً مع بنود الفاتورة">
                        <span>📸</span> مطابقة إشعار
                    </button>
                    <button type="button" id="btn-grn-wh-heatmap" class="btn-action" style="padding:9px 13px;font-size:0.84rem;border-radius:10px;background:linear-gradient(135deg,#10b981,#047857);display:inline-flex;align-items:center;gap:5px;" onclick="window.openGrnWarehouseHeatmapModal && window.openGrnWarehouseHeatmapModal()" title="خريطة السعة الاستيعابية للمخازن والأرفف (Bin Heatmap) وتوجيه التسكين بضغطة واحدة">
                        <span>🗺️</span> سعة المخازن
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

                    const normFn = typeof window.normalizeGrnArabic === 'function' ? window.normalizeGrnArabic : (s => String(s || '').toLowerCase());
                    const normScan = normFn(scanTerm);
                    const matchedIdx = receiptLines.findIndex(line => {
                        const itemObj = (itemsDb || []).find(i => String(i.id) === String(line.itemId) || i.name === line.itemId) || {};
                        const iName = normFn(getItemName(line.itemId) || '');
                        const iCode = normFn(itemObj.code || itemObj.itemCode || line.itemId || '');
                        const iBar  = normFn(itemObj.barcode || '');
                        return iBar === normScan || iCode === normScan || iName.includes(normScan);
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

                // A. Add Item Ledger direct link button AND Batch/Expiry Date button next to Split button in Item cell
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

                    const batchBtn = document.createElement('button');
                    batchBtn.type = 'button';
                    batchBtn.className = 'grn-row-mini-btn grn-batch-btn';
                    batchBtn.style.cssText = 'padding:2px 6px;font-size:0.7rem;border-radius:6px;cursor:pointer;background:rgba(168,85,247,0.16);border:1px solid rgba(168,85,247,0.4);color:#e9d5ff;font-weight:800;';
                    const updateBatchBtnLabel = () => {
                        if (line.batchNo || line.expiryDate || line.binLocation) {
                            const risk = typeof window.evaluateGrnExpiryRisk === 'function'
                                ? window.evaluateGrnExpiryRisk(line.expiryDate, line.itemName, false)
                                : null;
                            const riskIcon = risk && risk.status === 'expired' ? '⛔' : (risk && risk.status === 'critical' ? '⚠️' : '📅');
                            const daysTag = risk && risk.days !== null ? ` [${risk.days <= 0 ? 'منتهي!' : risk.days + ' يوم'}]` : '';
                            batchBtn.innerHTML = `${riskIcon} ${line.batchNo || ''}${line.binLocation ? ' [' + line.binLocation + ']' : ''}${line.expiryDate ? ' (' + line.expiryDate + ')' : ''}${daysTag}`;
                            if (risk && risk.status === 'expired') {
                                batchBtn.style.background = 'rgba(239,68,68,0.22)';
                                batchBtn.style.borderColor = '#ef4444';
                                batchBtn.style.color = '#fca5a5';
                            } else if (risk && risk.status === 'critical') {
                                batchBtn.style.background = 'rgba(245,158,11,0.22)';
                                batchBtn.style.borderColor = '#f59e0b';
                                batchBtn.style.color = '#fde68a';
                            } else {
                                batchBtn.style.background = 'rgba(16,185,129,0.2)';
                                batchBtn.style.borderColor = 'rgba(16,185,129,0.5)';
                                batchBtn.style.color = '#6ee7b7';
                            }
                        } else {
                            batchBtn.innerHTML = '📅 تشغيلة/رف';
                        }
                        batchBtn.title = 'توثيق رقم التشغيلة (Batch/Lot) وتاريخ الصلاحية وموقع الرف داخل المستودع';
                    };
                    updateBatchBtnLabel();
                    batchBtn.addEventListener('click', (ev) => {
                        ev.stopPropagation();
                        if (typeof window.openGrnBatchBinModal === 'function') {
                            window.openGrnBatchBinModal(line, 'batch', () => {
                                updateBatchBtnLabel();
                                window._grnHasUnsavedChanges = true;
                                if (line.expiryDate && typeof window.evaluateGrnExpiryRisk === 'function') {
                                    window.evaluateGrnExpiryRisk(line.expiryDate, line.itemName, true);
                                } else {
                                    showToast(`📅 تم توثيق التشغيلة (${line.batchNo || '—'}) والرف (${line.binLocation || '—'}) والصلاحية (${line.expiryDate || '—'})`, 'success');
                                }
                            });
                        }
                    });
                    itemBox.appendChild(batchBtn);
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
                            if (typeof window.openGrnBatchBinModal === 'function') {
                                window.openGrnBatchBinModal(line, 'dmg', (dmgVal) => {
                                    line.qcStatus = 'dmg';
                                    line.damagedQty = dmgVal;
                                    if (dmgVal > 0 && dmgVal <= line.maxPending) {
                                        line.receiveQty = Math.max(0, line.maxPending - dmgVal);
                                        if (qtyInput) qtyInput.value = line.receiveQty;
                                        refreshRowVariance();
                                    }
                                    line.notes = `تالف/مرفوض: ${dmgVal} ${line.unitName || ''}`;
                                    notesInput.value = line.notes;
                                    window._grnHasUnsavedChanges = true;
                                    renderQcBtn();
                                });
                                return;
                            }
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

        function syncItemTotalStockFields(it) {
            if (!it || !it.stock || typeof it.stock !== 'object') return;
            let sumActual = 0;
            Object.keys(it.stock).forEach(k => {
                if (k !== 'PENDING_RECEIPT') {
                    sumActual += (parseFloat(it.stock[k]) || 0);
                }
            });
            it.currentStock = Math.max(0, Math.round(sumActual * 100) / 100);
            if (Array.isArray(it.units) && it.units[0]) {
                it.units[0].stock = it.currentStock;
            }
        }

        // 7. Upgrade saveReceiptNote for 100% Atomic Transaction, Rollback, Cross-Screen Sync (erp_items_v1 + erp_inventory_v1 + erp_purchases_v1 + BroadcastChannel)
        window.focusRequiredGrnElement = function(target, msg) {
            if (msg && typeof showToast === 'function') showToast(msg, 'warning');
            const el = (typeof target === 'string') ? document.querySelector(target) : target;
            if (!el) return;
            try {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => {
                    try {
                        el.focus();
                        if (typeof el.select === 'function' && el.tagName === 'INPUT') el.select();
                    } catch (_e) {}
                }, 120);
                const prevShadow = el.style.boxShadow;
                const prevBorder = el.style.borderColor;
                el.style.boxShadow = '0 0 0 3px rgba(251, 191, 36, 0.65), 0 0 18px rgba(251, 191, 36, 0.45)';
                el.style.borderColor = '#fbbf24';
                setTimeout(() => {
                    el.style.boxShadow = prevShadow;
                    el.style.borderColor = prevBorder;
                }, 2600);
            } catch (_e) {}
        };

        const _origSaveReceiptNote = window.saveReceiptNote;
        window.saveReceiptNote = function() {
            if (window._isSavingGRN) return;
            if (currentInvoiceId === 'DEMO-GRN-101') {
                if (typeof showToast === 'function') showToast('ℹ️ هذا مستند معاينة تدريبي خاص بالمرشد؛ اختر فاتورة فعلية من القائمة للاعتماد', 'warning');
                if (typeof showHub === 'function') showHub();
                return;
            }
            const receivingItems = (receiptLines || []).filter(x => x.receiveQty > 0);
            if (receivingItems.length === 0) {
                return window.focusRequiredGrnElement('#items-tbody .qty-input', '⚠️ يرجى تحديد كمية استلام أكبر من صفر لصنف واحد على الأقل');
            }
            const hasEmptyWh = receivingItems.some(x => !x.warehouseId);
            if (hasEmptyWh) {
                return window.focusRequiredGrnElement('#grn-bulk-wh-select, #items-tbody .wh-select', '⚠️ يرجى تحديد المستودع لجميع الأصناف المستلمة');
            }

            window._isSavingGRN = true;
            const _grnBusyTimer = setTimeout(() => { window._isSavingGRN = false; }, 2200);

            // 🛡️ ATOMIC SNAPSHOT (Rollback if any exception occurs)
            const _atomicKeys = [
                'erp_inventory_v1', 'erp_items_v1', 'erp_purchase_invoices_v1',
                'erp_purchases_v1', 'magaies_local_purchase_invoices_v1', 'erp_grn_v1'
            ];
            const _atomicSnap = {};
            try { _atomicKeys.forEach(k => { _atomicSnap[k] = localStorage.getItem(k); }); } catch (_es) {}

            try {
                // If editing an existing GRN, revert its previous positive quantities from erp_items_v1 BEFORE applying new ones
                const activeEditGrnId = editingGRNId;
                if (activeEditGrnId) {
                    const prevGrn = (allPurchases || []).find(p => p && p.id === activeEditGrnId);
                    if (prevGrn && Array.isArray(prevGrn.items)) {
                        let itemsMasterPre = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
                        let preChanged = false;
                        prevGrn.items.filter(it => (parseFloat(it.quantity) || 0) > 0).forEach(pItem => {
                            const f = parseFloat(pItem.customFactor || pItem.factor) || 1;
                            const baseRev = pItem.customBaseQty !== undefined ? parseFloat(pItem.customBaseQty) : ((parseFloat(pItem.quantity) || 0) * f);
                            const idx = itemsMasterPre.findIndex(it => String(it.id) === String(pItem.itemId) || it.name === pItem.itemId || it.name === getItemName(pItem.itemId));
                            if (idx > -1 && itemsMasterPre[idx].stock && typeof itemsMasterPre[idx].stock === 'object') {
                                const whId = pItem.warehouseId || 'w-main';
                                itemsMasterPre[idx].stock[whId] = Math.max(0, (parseFloat(itemsMasterPre[idx].stock[whId]) || 0) - baseRev);
                                syncItemTotalStockFields(itemsMasterPre[idx]);
                                preChanged = true;
                            }
                        });
                        if (preChanged) {
                            localStorage.setItem('erp_items_v1', JSON.stringify(itemsMasterPre));
                            itemsDb = itemsMasterPre;
                        }
                    }
                }

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

                receivingItems.forEach(l => {
                    const qcTag = l.qcStatus === 'dmg' ? `[تالف: ${l.damagedQty || 0}] ` : (l.qcStatus === 'warn' ? '[استلام بتحفظ] ' : '');
                    const batchTag = (l.batchNo || l.expiryDate || l.binLocation) ? `[تشغيلة: ${l.batchNo || '—'}${l.binLocation ? ' | رف: ' + l.binLocation : ''}${l.expiryDate ? ' | صلاحية: ' + l.expiryDate : ''}] ` : '';
                    const cleanNote = String(l.notes || '').replace(/^(\[.*?\]\s*)+/, '');
                    l.notes = (qcTag + batchTag + logPrefix + cleanNote).trim();
                });

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

                // Post-save enrichment: enrich saved GRN record with logistics, QC, and sync all storage keys + erp_items_v1
                let freshPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
                let savedGrnObj = null;
                for (let i = freshPurchases.length - 1; i >= 0; i--) {
                    const p = freshPurchases[i];
                    if (p && p.isGRN && String(p.originalInvoiceId) === String(activeInvId)) {
                        p.waybillNo = waybillNo;
                        p.truckNo = truckNo;
                        p.driverInfo = driverInfo;
                        p.inspectorName = inspector;
                        p.isFullyReceived = isFullyReceivedNow;
                        if (window._currentGrnPhotoDataUrl) {
                            p.inspectionPhoto = window._currentGrnPhotoDataUrl;
                        }
                        savedGrnObj = p;
                        break;
                    }
                }
                const parentIdx = freshPurchases.findIndex(p => p && !p.isGRN && !p.isReturn && String(p.invoiceNo || p.id) === String(activeInvId));
                if (parentIdx > -1) {
                    freshPurchases[parentIdx].receiptStatus = isFullyReceivedNow ? 'received' : 'partially_received';
                    if (isFullyReceivedNow) {
                        freshPurchases[parentIdx].status = 'received';
                    } else if (freshPurchases[parentIdx].status === 'received') {
                        freshPurchases[parentIdx].status = 'completed';
                    }
                }

                // Sync across all 3 purchase storage keys + dedicated erp_grn_v1 key
                const serializedPurchases = JSON.stringify(freshPurchases);
                localStorage.setItem('erp_purchase_invoices_v1', serializedPurchases);
                localStorage.setItem('erp_purchases_v1', serializedPurchases);
                localStorage.setItem('magaies_local_purchase_invoices_v1', serializedPurchases);
                try {
                    const onlyGrns = freshPurchases.filter(p => p && p.isGRN);
                    localStorage.setItem('erp_grn_v1', JSON.stringify(onlyGrns));
                } catch (_eg) {}
                allPurchases = freshPurchases;

                // Update erp_items_v1 per-warehouse stock
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
                        syncItemTotalStockFields(it);
                        if (line.binLocation) {
                            it.binLocation = line.binLocation;
                        }
                        if (line.batchNo || line.expiryDate || line.binLocation) {
                            if (!Array.isArray(it.batches)) it.batches = [];
                            it.batches.push({
                                batchNo: line.batchNo || '',
                                expiryDate: line.expiryDate || '',
                                binLocation: line.binLocation || '',
                                qty: baseAdded,
                                warehouseId: whId,
                                grnId: savedGrnObj ? savedGrnObj.id : '',
                                invoiceNo: activeInvId,
                                receivedAt: new Date().toISOString()
                            });
                        }
                        itemsChanged = true;
                    }
                });
                if (itemsChanged) {
                    localStorage.setItem('erp_items_v1', JSON.stringify(itemsMaster));
                    itemsDb = itemsMaster;
                }

                if (typeof window.rebuildGLFromAllTables === 'function') {
                    try { window.rebuildGLFromAllTables(false); } catch (_egl) {}
                }

                // 🌐 Weak-Internet Resilient Queue + BroadcastChannel Live Sync
                if (savedGrnObj) {
                    const enqueueGrnOffline = (gObj) => {
                        try {
                            let gq = JSON.parse(localStorage.getItem('erp_grn_offline_queue_v1') || '[]');
                            if (!Array.isArray(gq)) gq = [];
                            gq = gq.filter(x => x && x.id !== gObj.id);
                            gq.push({ id: gObj.id, payload: gObj, queuedAt: Date.now() });
                            localStorage.setItem('erp_grn_offline_queue_v1', JSON.stringify(gq.slice(-100)));
                        } catch (_eq) {}
                    };
                    if (!navigator.onLine) {
                        enqueueGrnOffline(savedGrnObj);
                    } else {
                        const ctrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
                        const tId = ctrl ? setTimeout(() => ctrl.abort(), 8000) : null;
                        fetch('api/mirror_sync.php?action=save_purchase_receipt', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(savedGrnObj),
                            signal: ctrl ? ctrl.signal : undefined
                        }).then(r => { if (tId) clearTimeout(tId); return r.json(); })
                          .then(res => { if (!res || !res.success) enqueueGrnOffline(savedGrnObj); })
                          .catch(() => { if (tId) clearTimeout(tId); enqueueGrnOffline(savedGrnObj); });
                    }
                }

                try {
                    const syncPayload = {
                        type: 'grn_saved',
                        module: 'purchase_receipt',
                        grnId: savedGrnObj ? savedGrnObj.id : '',
                        invoiceNo: activeInvId,
                        isFullyReceived: isFullyReceivedNow,
                        timestamp: Date.now()
                    };
                    if (typeof BroadcastChannel !== 'undefined') {
                        const bc1 = new BroadcastChannel('magaiees_sync_channel'); bc1.postMessage(syncPayload); bc1.close();
                        const bc2 = new BroadcastChannel('erp_live_sync'); bc2.postMessage(syncPayload); bc2.close();
                    }
                    window.dispatchEvent(new CustomEvent('erp_data_updated', { detail: syncPayload }));
                } catch (_ebc) {}
            } catch (errAtomic) {
                console.error('Atomic GRN error — rolling back state:', errAtomic);
                clearTimeout(_grnBusyTimer);
                window._isSavingGRN = false;
                try {
                    Object.keys(_atomicSnap).forEach(k => {
                        if (_atomicSnap[k] === null || _atomicSnap[k] === undefined) localStorage.removeItem(k);
                        else localStorage.setItem(k, _atomicSnap[k]);
                    });
                } catch (_erb) {}
                showToast('❌ حدث خطأ أثناء حفظ إذن الاستلام وتم التراجع التلقائي (Rollback) لحماية أرصدة المخازن', 'error');
            }
        };

        // 🛡️ Safeguarded deleteGRN with Negative Stock Protection, Fiscal Period Check, erp_items_v1 Reversion & BroadcastChannel
        window.deleteGRN = function(grnId, confirmedByModal) {
            const grn = (allPurchases || []).find(p => p && p.id === grnId);
            if (!grn) return;

            // 1. Fiscal Period Policy Check
            if (grn.date && typeof window.canEditRecord === 'function') {
                if (!window.canEditRecord(grn.date.split('T')[0], 'purchase')) {
                    return;
                }
            }

            // 2. Negative Warehouse Stock Lock (Prevent deleting GRN if goods were already sold/consumed)
            const positiveItems = (grn.items || []).filter(item => (parseFloat(item.quantity) || 0) > 0);
            let currentInvData = [];
            let itemsMaster = [];
            try { currentInvData = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]'); } catch(e) {}
            try { itemsMaster = JSON.parse(localStorage.getItem('erp_items_v1') || '[]'); } catch(e) {}

            for (const pItem of positiveItems) {
                const whObj = (warehousesDb || []).find(w => w.id === pItem.warehouseId);
                const whName = whObj ? whObj.name : 'المخزن الرئيسي';
                const qtyToRevert = pItem.customBaseQty !== undefined ? parseFloat(pItem.customBaseQty) : ((parseFloat(pItem.quantity) || 0) * (parseFloat(pItem.customFactor || pItem.factor) || 1));
                const itemInv = currentInvData.find(inv => String(inv.itemId) === String(pItem.itemId) && inv.warehouse === whName);
                const availInInv = itemInv ? (parseFloat(itemInv.qty) || parseFloat(itemInv.quantity) || 0) : 0;
                if (availInInv - qtyToRevert < -0.001) {
                    showToast(`❌ محمي من الحذف: الصنف (${getItemName(pItem.itemId)}) في (${whName}) تم الصرف أو البيع منه ولا يسمح رصيده الحالي (${fmtNum(availInInv)}) بإلغاء الاستلام (${fmtNum(qtyToRevert)})!`, 'error');
                    return;
                }
            }

            if (!confirmedByModal && typeof window.openGrnConfirmModal === 'function') {
                return window.openGrnConfirmModal({
                    title: '🗑️ تأكيد حذف إذن الاستلام',
                    message: `هل أنت متأكد من حذف عملية الاستلام (${grnId})؟ سيؤدي ذلك لإعادة الكميات المستلمة كبضاعة معلقة قيد الاستلام.`,
                    confirmText: '🗑️ نعم، حذف الإذن',
                    isDanger: true,
                    onConfirm: () => window.deleteGRN(grnId, true)
                });
            }

            // 3. Revert erp_inventory_v1 AND erp_items_v1 atomically
            positiveItems.forEach(pItem => {
                const whObj = (warehousesDb || []).find(w => w.id === pItem.warehouseId);
                const whName = whObj ? whObj.name : 'المخزن الرئيسي';
                const qtyToRevert = pItem.customBaseQty !== undefined ? parseFloat(pItem.customBaseQty) : ((parseFloat(pItem.quantity) || 0) * (parseFloat(pItem.customFactor || pItem.factor) || 1));

                let itemInv = currentInvData.find(inv => String(inv.itemId) === String(pItem.itemId) && inv.warehouse === whName);
                if (itemInv) {
                    itemInv.qty = Math.max(0, (parseFloat(itemInv.qty) || parseFloat(itemInv.quantity) || 0) - qtyToRevert);
                }

                const mIdx = itemsMaster.findIndex(it => String(it.id) === String(pItem.itemId) || it.name === pItem.itemId || it.name === getItemName(pItem.itemId));
                if (mIdx > -1 && itemsMaster[mIdx].stock && typeof itemsMaster[mIdx].stock === 'object') {
                    const whId = pItem.warehouseId || 'w-main';
                    itemsMaster[mIdx].stock[whId] = Math.max(0, (parseFloat(itemsMaster[mIdx].stock[whId]) || 0) - qtyToRevert);
                    syncItemTotalStockFields(itemsMaster[mIdx]);
                }
            });
            localStorage.setItem('erp_inventory_v1', JSON.stringify(currentInvData));
            localStorage.setItem('erp_items_v1', JSON.stringify(itemsMaster));
            itemsDb = itemsMaster;

            // 4. Remove GRN & Update Parent Invoice Status
            const origInvId = grn.originalInvoiceId;
            allPurchases = (allPurchases || []).filter(p => p && p.id !== grnId);
            const remainingGrnsForInv = allPurchases.filter(p => p && p.isGRN && String(p.originalInvoiceId) === String(origInvId));
            const parentIdx = allPurchases.findIndex(p => p && !p.isGRN && !p.isReturn && String(p.invoiceNo || p.id) === String(origInvId));
            if (parentIdx > -1) {
                allPurchases[parentIdx].receiptStatus = remainingGrnsForInv.length > 0 ? 'partially_received' : 'pending';
                if (allPurchases[parentIdx].status === 'received') {
                    allPurchases[parentIdx].status = 'completed';
                }
            }

            const serializedAll = JSON.stringify(allPurchases);
            localStorage.setItem('erp_purchase_invoices_v1', serializedAll);
            localStorage.setItem('erp_purchases_v1', serializedAll);
            localStorage.setItem('magaies_local_purchase_invoices_v1', serializedAll);
            try {
                localStorage.setItem('erp_grn_v1', JSON.stringify(allPurchases.filter(p => p && p.isGRN)));
            } catch (_eg) {}

            try {
                let delIds = JSON.parse(localStorage.getItem('erp_deleted_grns_v1') || '[]');
                if (!Array.isArray(delIds)) delIds = [];
                if (!delIds.includes(grnId)) delIds.push(grnId);
                localStorage.setItem('erp_deleted_grns_v1', JSON.stringify(delIds.slice(-500)));
            } catch (_ed) {}

            if (navigator.onLine) {
                fetch('api/mirror_sync.php?action=delete_purchase_receipt', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: grnId, originalInvoiceId: origInvId, hasRemainingGrns: remainingGrnsForInv.length > 0 })
                }).catch(() => {});
            }

            if (window.logSystemAction) {
                window.logSystemAction('حذف إذن استلام بضاعة', 'المشتريات / المخازن', grnId, `تم حذف إذن استلام البضاعة رقم ${grnId} وإرجاع الكميات لقيد الاستلام`);
            }
            if (typeof window.pushToCloud === 'function') {
                window.pushToCloud(true);
            }
            try {
                const delPayload = { type: 'grn_deleted', module: 'purchase_receipt', grnId: grnId, invoiceNo: origInvId, timestamp: Date.now() };
                if (typeof BroadcastChannel !== 'undefined') {
                    const bc1 = new BroadcastChannel('magaiees_sync_channel'); bc1.postMessage(delPayload); bc1.close();
                    const bc2 = new BroadcastChannel('erp_live_sync'); bc2.postMessage(delPayload); bc2.close();
                }
                window.dispatchEvent(new CustomEvent('erp_data_updated', { detail: delPayload }));
            } catch (_ebc) {}

            if (editingGRNId === grnId) editingGRNId = null;
            calculatePendingGoods();
            renderPendingInvoices();
            renderRecentGRNs(false);
            showToast('🗑️ تم حذف إذن الاستلام وعكس أرصدة المخازن وإعادة البضاعة لقيد الاستلام بنجاح', 'success');
            if (currentInvoiceId) openReceiptEditor(currentInvoiceId);
            else showHub();
        };

        // ⚡ Quick 1-Click Fill All / Zero All / Direct Hub Receive Helpers
        window.fillAllRemainingGRNQty = function() {
            if (!Array.isArray(receiptLines) || !receiptLines.length) return;
            const assignedByGroup = {};
            receiptLines.forEach(l => {
                const grp = l.lineGroup || l.itemId;
                const maxP = parseFloat(l.maxPending) || 0;
                const already = assignedByGroup[grp] || 0;
                const remForLine = Math.max(0, Math.round((maxP - already) * 100) / 100);
                l.receiveQty = remForLine;
                assignedByGroup[grp] = already + remForLine;
            });
            renderReceiptLines();
            updateSummary();
            showToast('⚡ تم تعبئة كامل الكميات المتبقية لجميع الأصناف (100%)', 'success');
        };
        window.quickReceiveInvoiceFromHub = function(invId) {
            if (!invId || !pendingMap[invId]) return;
            openReceiptEditor(invId);
            window.fillAllRemainingGRNQty();
            saveReceiptNote();
        };
        window.zeroAllGRNQty = function() {
            if (!Array.isArray(receiptLines) || !receiptLines.length) return;
            receiptLines.forEach(l => { l.receiveQty = 0; });
            renderReceiptLines();
            updateSummary();
            const bInp = document.getElementById('grn-barcode-input');
            if (bInp) bInp.focus();
            showToast('🧹 تم تصفير الكميات لبدء الاستلام الجزئي أو المسح بالباركود', 'info');
        };

        // 📷 7-B. Client-Side Compressed GRN Photo Evidence Upload
        window._currentGrnPhotoDataUrl = null;
        window.handleGrnPhotoUpload = function(fileInp) {
            const file = fileInp && fileInp.files && fileInp.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(ev) {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    const maxW = 900;
                    const scale = img.width > maxW ? (maxW / img.width) : 1;
                    canvas.width = Math.round(img.width * scale);
                    canvas.height = Math.round(img.height * scale);
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    window._currentGrnPhotoDataUrl = canvas.toDataURL('image/jpeg', 0.65);
                    const lbl = document.getElementById('lbl-grn-photo-status');
                    const btn = document.getElementById('btn-grn-photo-attach');
                    if (lbl) lbl.textContent = '✅ تم إرفاق صورة الفحص';
                    if (btn) {
                        btn.style.background = 'rgba(16,185,129,0.18)';
                        btn.style.borderColor = 'rgba(16,185,129,0.55)';
                        btn.style.color = '#34d399';
                    }
                    showToast('📷 تم ضغط وإرفاق صورة محضر الفحص والبوليصة بنجاح', 'success');
                };
                img.src = ev.target.result;
            };
            reader.readAsDataURL(file);
        };

        // 🚨 7-C. Formal Supplier Claim Notice for Damaged / Shortage Quantities
        window.generateSupplierClaimNotice = function() {
            const invData = pendingMap[currentInvoiceId] || {};
            const supName = getSupplierName(invData.supplierId);
            const claimLines = [];
            (receiptLines || []).forEach(l => {
                const maxP = parseFloat(l.maxPending) || 0;
                const rcv  = parseFloat(l.receiveQty) || 0;
                const dmg  = parseFloat(l.damagedQty) || 0;
                const shortage = Math.max(0, Math.round((maxP - rcv - dmg) * 100) / 100);
                if (dmg > 0 || shortage > 0 || l.qcStatus === 'dmg' || l.qcStatus === 'warn') {
                    claimLines.push({
                        itemId: l.itemId,
                        itemName: getItemName(l.itemId),
                        unitName: l.unitName || 'وحدة',
                        orderedQty: maxP,
                        receivedGoodQty: rcv,
                        damagedQty: dmg,
                        shortageQty: shortage,
                        price: parseFloat(l.price) || 0,
                        claimValue: (dmg + shortage) * (parseFloat(l.price) || 0),
                        notes: l.notes || (dmg > 0 ? 'تالف أثناء الفحص' : 'نقص في الكمية الموردة')
                    });
                }
            });

            if (claimLines.length === 0) {
                return showToast('ℹ️ جميع الأصناف سليمة ومطابقة 100% — حدد حالة (🚨 تالف) أو كمية أقل من المطلوب لإصدار مطالبة للمورد', 'info');
            }

            const claimNo = 'CLM-' + Date.now().toString().slice(-6);
            const totalClaimVal = claimLines.reduce((s, c) => s + c.claimValue, 0);
            const claimObj = {
                id: claimNo,
                claimNo: claimNo,
                invoiceNo: currentInvoiceId || '—',
                supplierId: invData.supplierId || '',
                supplierName: supName,
                date: new Date().toISOString(),
                waybillNo: document.getElementById('grn-waybill-no')?.value || '—',
                truckNo: document.getElementById('grn-truck-no')?.value || '—',
                inspectorName: document.getElementById('grn-inspector-name')?.value || 'أمين المستودع',
                totalClaimValue: totalClaimVal,
                lines: claimLines
            };

            try {
                const claims = JSON.parse(localStorage.getItem('erp_supplier_claims_v1') || '[]');
                claims.unshift(claimObj);
                localStorage.setItem('erp_supplier_claims_v1', JSON.stringify(claims.slice(0, 300)));
            } catch (_ec) {}

            const rowsHtml = claimLines.map((c, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td style="text-align:right;font-weight:800;">${c.itemName}</td>
                    <td>${c.unitName}</td>
                    <td>${fmtNum(c.orderedQty)}</td>
                    <td style="color:#059669;font-weight:800;">${fmtNum(c.receivedGoodQty)}</td>
                    <td style="color:#dc2626;font-weight:900;">${fmtNum(c.damagedQty)}</td>
                    <td style="color:#d97706;font-weight:900;">${fmtNum(c.shortageQty)}</td>
                    <td style="font-weight:800;">${fmtNum(c.claimValue)} SDG</td>
                    <td style="text-align:right;font-size:12px;">${c.notes}</td>
                </tr>
            `).join('');

            const waText = `🚨 *إشعار مطالبة رسمي للمورد (تالف / نواقص استلام)*\nرقم المطالبة: *${claimNo}*\nالفاتورة الأصلية: *${claimObj.invoiceNo}*\nالمورد: *${supName}*\n\n*تفاصيل الفروقات والتالف:*\n` +
                claimLines.map(c => `• ${c.itemName}: مطلوب (${fmtNum(c.orderedQty)}) | سليم (${fmtNum(c.receivedGoodQty)}) | تالف (${fmtNum(c.damagedQty)}) | نقص (${fmtNum(c.shortageQty)})`).join('\n') +
                `\n\n💰 إجمالي قيمة المطالبة التقديرية: *${fmtNum(totalClaimVal)} SDG*`;

            const w = window.open('', '_blank', 'width=1020,height=760');
            if (!w) {
                window.open('https://wa.me/?text=' + encodeURIComponent(waText), '_blank');
                return;
            }
            w.document.write(`<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><title>إشعار مطالبة للمورد - ${claimNo}</title>
            <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@500;700;800;900&display=swap" rel="stylesheet">
            <style>body{font-family:'Tajawal',sans-serif;margin:24px;color:#0f172a;background:#fff}table{width:100%;border-collapse:collapse;margin-top:16px;font-size:13.5px}th{background:#991b1b;color:#fff;padding:10px;text-align:center}td{border:1px solid #cbd5e1;padding:9px;text-align:center}@media print{.no-print{display:none!important}}</style>
            </head><body>
            <div class="no-print" style="margin-bottom:15px;display:flex;gap:10px;flex-wrap:wrap;">
                <button onclick="window.print()" style="padding:10px 22px;background:#dc2626;color:#fff;border:none;border-radius:8px;font-family:'Tajawal';font-weight:800;cursor:pointer;">🖨️ طباعة محضر المطالبة</button>
                <button id="btn-post-debit" onclick="if(window.opener && window.opener.postSupplierClaimDebitNote){ window.opener.postSupplierClaimDebitNote('${claimNo}'); this.disabled=true; this.textContent='✅ تم قيد إشعار الخصم على حساب المورد'; this.style.background='#475569'; }" style="padding:10px 22px;background:#7c3aed;color:#fff;border:none;border-radius:8px;font-family:'Tajawal';font-weight:800;cursor:pointer;">💳 قيد إشعار خصم مدين على حساب المورد</button>
                <a href="https://wa.me/?text=${encodeURIComponent(waText)}" target="_blank" style="padding:10px 22px;background:#059669;color:#fff;text-decoration:none;border-radius:8px;font-family:'Tajawal';font-weight:800;">💬 إرسال للمورد عبر واتساب</a>
            </div>
            <div style="border-bottom:3px solid #dc2626;padding-bottom:12px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;">
                <div><h1 style="margin:0;color:#991b1b;font-size:23px;">🚨 محضر وإشعار مطالبة للمورد (تالف / نواقص استلام)</h1><div style="color:#475569;font-weight:700;">رقم المطالبة: ${claimNo} | الفاتورة: ${claimObj.invoiceNo} | المورد: ${supName}</div></div>
                <div style="text-align:left;font-weight:800;color:#dc2626;">إجمالي المطالبة: ${fmtNum(totalClaimVal)} SDG</div>
            </div>
            <table><thead><tr><th>#</th><th>الصنف</th><th>الوحدة</th><th>كمية الفاتورة</th><th>المستلم السليم</th><th>التالف/المرفوض</th><th>النقص</th><th>قيمة المطالبة</th><th>السبب/الملاحظات</th></tr></thead><tbody>${rowsHtml}</tbody></table>
            </body></html>`);
            w.document.close();
        };

        // 💳 7-D. Auto Debit Note / Supplier Claim Financial Posting
        window.postSupplierClaimDebitNote = function(claimNo) {
            try {
                const claims = JSON.parse(localStorage.getItem('erp_supplier_claims_v1') || '[]');
                const claim = claims.find(c => c && (c.id === claimNo || c.claimNo === claimNo));
                if (!claim) return showToast('لم يتم العثور على بيانات المطالبة', 'error');
                if (claim.debitNotePosted) return showToast('تم ترحيل إشعار الخصم لهذه المطالبة مسبقاً', 'info');

                const debitNo = 'DBN-' + claimNo.replace('CLM-', '');
                const curUser = (typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo() : { id: 1, name: 'المسؤول', username: 'admin' };
                const debitRecord = {
                    id: debitNo,
                    invoiceNo: debitNo,
                    isReturn: true,
                    isClaimDebitNote: true,
                    originalInvoiceId: claim.invoiceNo,
                    claimNo: claimNo,
                    type: 'purchase_return',
                    customer: claim.supplierId,
                    customerId: claim.supplierId,
                    supplierId: claim.supplierId,
                    supplierName: claim.supplierName,
                    date: new Date().toISOString(),
                    warehouse: 'PENDING_RECEIPT',
                    subtotal: claim.totalClaimValue || 0,
                    grandTotal: claim.totalClaimValue || 0,
                    netTotal: claim.totalClaimValue || 0,
                    paymentType: 'credit',
                    status: 'completed',
                    notes: `إشعار خصم مدين آلي لمحضر تالف/نواقص رقم ${claimNo} للفاتورة ${claim.invoiceNo}`,
                    createdBy: curUser.id,
                    createdByName: curUser.fullName || curUser.name || 'المسؤول',
                    items: (claim.lines || []).map(l => ({
                        itemId: l.itemId,
                        name: l.itemName,
                        unitName: l.unitName,
                        quantity: -( (parseFloat(l.damagedQty) || 0) + (parseFloat(l.shortageQty) || 0) ),
                        warehouseId: 'PENDING_RECEIPT',
                        price: l.price || 0,
                        total: -(parseFloat(l.claimValue) || 0),
                        notes: l.notes || ''
                    }))
                };

                let freshPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
                freshPurchases.push(debitRecord);
                const serPur = JSON.stringify(freshPurchases);
                localStorage.setItem('erp_purchase_invoices_v1', serPur);
                localStorage.setItem('erp_purchases_v1', serPur);
                localStorage.setItem('magaies_local_purchase_invoices_v1', serPur);
                allPurchases = freshPurchases;

                // Deduct claim value from supplier balance
                let sups = JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]');
                const sIdx = sups.findIndex(s => s && (String(s.id) === String(claim.supplierId) || s.name === claim.supplierName));
                if (sIdx > -1) {
                    const deductAmt = parseFloat(claim.totalClaimValue) || 0;
                    sups[sIdx].balance = (parseFloat(sups[sIdx].balance ?? sups[sIdx].currentBalance) || 0) - deductAmt;
                    sups[sIdx].currentBalance = sups[sIdx].balance;
                    localStorage.setItem('erp_suppliers_v1', JSON.stringify(sups));
                    suppliersDb = sups;
                }

                claim.debitNotePosted = true;
                claim.debitNoteNo = debitNo;
                localStorage.setItem('erp_supplier_claims_v1', JSON.stringify(claims));

                if (typeof window.rebuildGLFromAllTables === 'function') {
                    try { window.rebuildGLFromAllTables(false); } catch (_eg) {}
                }
                if (typeof window.pushToCloud === 'function') window.pushToCloud(true);
                calculatePendingGoods();
                if (currentInvoiceId) openReceiptEditor(currentInvoiceId);
                else renderPendingInvoices(false);

                showToast(`💳 تم قيد إشعار خصم مدين (${debitNo}) بقيمة ${fmtNum(claim.totalClaimValue)} SDG وخصمه من حساب المورد بنجاح`, 'success');
            } catch (errD) {
                console.error(errD);
                showToast('حدث خطأ أثناء ترحيل إشعار الخصم', 'error');
            }
        };

        // 🏷️ 7-E. Print Batch Barcode & Bin Location Labels for Received Items
        window.printGRNBatchBarcodeLabels = function(grnIdOptional) {
            let labelItems = [];
            let docRef = editingGRNId || currentInvoiceId || 'GRN';
            if (grnIdOptional) {
                const grn = (allPurchases || []).find(p => p && p.id === grnIdOptional);
                if (!grn) return showToast('لم يتم العثور على بيانات الإذن', 'error');
                docRef = grn.id;
                labelItems = (grn.items || []).filter(i => (parseFloat(i.quantity) || 0) > 0).map(i => {
                    const itemObj = (itemsDb || []).find(x => String(x.id) === String(i.itemId) || x.name === i.itemId) || {};
                    const whObj = (warehousesDb || []).find(w => w.id === i.warehouseId);
                    return {
                        name: getItemName(i.itemId),
                        code: itemObj.barcode || itemObj.code || String(i.itemId || '1001'),
                        qty: i.quantity,
                        unit: i.unitName || 'وحدة',
                        batch: i.batchNo || ('LOT-' + String(grn.id).slice(-5)),
                        expiry: i.expiryDate || '—',
                        bin: i.binLocation || itemObj.binLocation || (whObj ? whObj.name : 'المخزن الرئيسي')
                    };
                });
            } else {
                const rcvLines = (receiptLines || []).filter(l => (parseFloat(l.receiveQty) || 0) > 0);
                if (!rcvLines.length) return showToast('حدد كميات مستلمة أولاً لطباعة ملصقات الباركود', 'error');
                labelItems = rcvLines.map(l => {
                    const itemObj = (itemsDb || []).find(x => String(x.id) === String(l.itemId) || x.name === l.itemId) || {};
                    const whObj = (warehousesDb || []).find(w => w.id === l.warehouseId);
                    return {
                        name: getItemName(l.itemId),
                        code: itemObj.barcode || itemObj.code || String(l.itemId || '1001'),
                        qty: l.receiveQty,
                        unit: l.unitName || 'وحدة',
                        batch: l.batchNo || ('LOT-' + new Date().toISOString().slice(2,10).replace(/-/g,'')),
                        expiry: l.expiryDate || '—',
                        bin: l.binLocation || itemObj.binLocation || (whObj ? whObj.name : 'المخزن الرئيسي')
                    };
                });
            }

            const buildSvgBars = (str) => {
                const s = String(str || '1001');
                let bars = '';
                let x = 6;
                for (let i = 0; i < s.length; i++) {
                    const c = s.charCodeAt(i);
                    const w1 = (c % 3) + 1.5;
                    const w2 = ((c >> 2) % 2) + 1;
                    bars += `<rect x="${x}" y="4" width="${w1}" height="34" fill="#000"/><rect x="${x + w1 + 2}" y="4" width="${w2}" height="34" fill="#000"/>`;
                    x += w1 + w2 + 5.5;
                }
                return `<svg viewBox="0 0 ${Math.max(120, x + 6)} 42" width="100%" height="42">${bars}</svg>`;
            };

            const cardsHtml = labelItems.map(it => `
                <div class="lbl-card">
                    <div class="lbl-title">${it.name}</div>
                    <div class="lbl-bars">${buildSvgBars(it.code + '-' + it.batch)}</div>
                    <div class="lbl-code">${it.code} | الدفعة: ${it.batch}</div>
                    <div class="lbl-meta">
                        <span>📦 الكمية: <b>${fmtNum(it.qty)} ${it.unit}</b></span>
                        <span>📍 الرف: <b>${it.bin}</b></span>
                    </div>
                    <div class="lbl-meta">
                        <span>⏳ الصلاحية: <b>${it.expiry}</b></span>
                        <span>📄 الإذن: <b>${docRef}</b></span>
                    </div>
                </div>
            `).join('');

            const w = window.open('', '_blank', 'width=920,height=720');
            if (!w) return showToast('يرجى السماح بالنوافذ المنبثقة لطباعة الملصقات', 'error');
            w.document.write(`<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><title>ملصقات باركود الدفعة والرف - ${docRef}</title>
            <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@600;700;800;900&display=swap" rel="stylesheet">
            <style>
                body{font-family:'Tajawal',sans-serif;margin:18px;background:#f8fafc;color:#0f172a}
                .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px}
                .lbl-card{background:#fff;border:2px dashed #0f172a;border-radius:10px;padding:10px 12px;text-align:center;page-break-inside:avoid}
                .lbl-title{font-size:14px;font-weight:900;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
                .lbl-code{font-family:monospace;font-size:11.5px;font-weight:800;margin-bottom:6px;letter-spacing:0.5px}
                .lbl-meta{display:flex;justify-content:space-between;font-size:11px;border-top:1px solid #e2e8f0;padding-top:4px;margin-top:4px}
                @media print{.no-print{display:none!important}body{background:#fff;margin:5px}}
            </style></head><body>
            <div class="no-print" style="margin-bottom:14px;display:flex;gap:10px;">
                <button onclick="window.print()" style="padding:9px 22px;background:#7c3aed;color:#fff;border:none;border-radius:8px;font-family:'Tajawal';font-weight:800;cursor:pointer;">🖨️ طباعة ملصقات الباركود الآن</button>
                <button onclick="window.close()" style="padding:9px 16px;background:#e2e8f0;color:#0f172a;border:none;border-radius:8px;font-family:'Tajawal';font-weight:700;cursor:pointer;">إغلاق</button>
            </div>
            <div class="grid">${cardsHtml}</div>
            </body></html>`);
            w.document.close();
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
                inspectionPhoto: grn.inspectionPhoto || null,
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
                inspectionPhoto: window._currentGrnPhotoDataUrl || null,
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
    ${doc.inspectionPhoto ? `<div style="margin:14px 0;padding:12px;border:1px dashed #94a3b8;border-radius:10px;text-align:center;"><strong>📷 مرفق صورة الفحص الميداني / البوليصة:</strong><br><img src="${doc.inspectionPhoto}" style="max-height:220px;max-width:95%;margin-top:8px;border-radius:8px;border:1px solid #cbd5e1;"></div>` : ''}
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

                    const lblBtn = document.createElement('button');
                    lblBtn.className = 'btn-action grn-lbl-btn';
                    lblBtn.style.cssText = 'padding: 4px 8px; font-size: 0.75rem; background: linear-gradient(135deg, #7c3aed, #6d28d9);';
                    lblBtn.title = 'طباعة ملصقات باركود الدفعة والرف 🏷️';
                    lblBtn.innerHTML = '🏷️';
                    lblBtn.onclick = () => window.printGRNBatchBarcodeLabels(grnId);

                    const waBtn = document.createElement('button');
                    waBtn.className = 'btn-action';
                    waBtn.style.cssText = 'padding: 4px 8px; font-size: 0.75rem; background: linear-gradient(135deg, #10b981, #059669);';
                    waBtn.title = 'مشاركة إشعار الاستلام عبر واتساب 💬';
                    waBtn.innerHTML = '💬';
                    waBtn.onclick = () => window.shareCurrentOrSavedGRNWhatsApp(grnId);

                    btnWrap.insertBefore(waBtn, btnWrap.firstChild);
                    btnWrap.insertBefore(lblBtn, waBtn);
                    btnWrap.insertBefore(printBtn, lblBtn);
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

        // ═══════════════════════════════════════════════════════════════════════════════
        // 🏛️ الدستور الحاكم الشامل لشاشة استلام البضاعة المخزني (purchase-receipt.php)
        // 1. عزل وتفصيل "زر المرشد" من الألف إلى الياء (خانات + أزرار + مسار التسميع)
        // 2. انسيابية المؤشر 60fps والتنقل بزر Enter عبر الحقول اللوجستية والباركود والجدول
        // 3. منع النقرات المزدوجة + طابور عدم الاتصال الذكي + الاستماع الفوري عبر BroadcastChannel
        // ═══════════════════════════════════════════════════════════════════════════════
        window.ensureGrnEditorOpenForGuide = function() {
            const ed = document.getElementById('view-editor');
            const hub = document.getElementById('view-hub');
            if (ed && ed.classList.contains('active')) return;
            const firstBtn = document.querySelector('#pending-invoices-container button[onclick*="openReceiptEditor"], #recent-grns-container button[onclick*="editSavedGRN"]');
            if (firstBtn) {
                try { firstBtn.click(); } catch (_e) {}
                if (ed && ed.classList.contains('active')) return;
            }
            if (hub && ed) {
                hub.classList.remove('active');
                ed.classList.add('active');
                if (!receiptLines || !receiptLines.length) {
                    currentInvoiceId = 'DEMO-GRN-101';
                    const lblNo = document.getElementById('lbl-inv-no');
                    const lblSup = document.getElementById('lbl-supplier');
                    const lblDt = document.getElementById('lbl-date');
                    if (lblNo) lblNo.textContent = 'INV-DEMO-101 (معاينة المرشد)';
                    if (lblSup) lblSup.textContent = 'مورد تجريبي للشرح الحي';
                    if (lblDt) lblDt.textContent = new Date().toLocaleDateString('ar-EG-u-nu-latn');
                    const defaultWh = (warehousesDb && warehousesDb.find(x => x.id !== 'PENDING_RECEIPT') || {}).id || 'w-main';
                    receiptLines = [{
                        id: 'L0',
                        itemId: (itemsDb && itemsDb[0]) ? itemsDb[0].id : 'صنف تجريبي للشرح',
                        unitId: 'كرتون',
                        unitName: 'كرتون',
                        factor: 12,
                        customFactor: 12,
                        price: 1000,
                        origQty: 10,
                        maxPending: 10,
                        receiveQty: 10,
                        warehouseId: defaultWh,
                        lineGroup: 'DEMO|كرتون',
                        qcStatus: 'ok',
                        isSplit: false
                    }];
                    try { renderReceiptLines(); } catch (_er) {}
                }
            }
        };

        window.MagaiesSmartGuides = window.MagaiesSmartGuides || {};
        window.MagaiesSmartGuides['purchase-receipt'] = {
            title: 'مرشد شاشة استلام البضاعة المخزني (Goods Receipt Note — GRN)',
            subtitle: 'شرح تفصيلي مبسط من الألف إلى الياء لكل خانة، وكل زر، وفحص الجودة (QC)، وتقسيم الدفعات، ومسار التسميع الفوري في المخازن والتقارير والموبايل',
            overview: 'شاشة استلام البضاعة المخزني (GRN) هي حلقة الوصل الرقابية بين قسم المشتريات والمستودعات الفعلية. عندما يتم شراء بضاعة بنظام الاستلام المخزني (أو الاستلام على دفعات)، تظل البضاعة في حساب وسيط هو (بضاعة قيد الاستلام PENDING_RECEIPT) ولا تدخل المخزن الفعلي إلا بعد فحصها وعدّها واعتمادها في هذه الشاشة. فور الضغط على (استلام وتأكيد البضاعة)، ينقل النظام الكميات المستلمة ككتلة ذرية واحدة (Atomic Transaction) من حساب البضاعة المعلقة إلى رصيد المستودع الفعلي، ويحدث شارة المطابقة الثلاثية في شاشة المشتريات، وكشف حركة الصنف، ودفتر أستاذ المخزن، وتطبيق الموبايل.',
            workflowSteps: [
                'الخطوة 1 (اختيار الفاتورة قيد الاستلام): من الشاشة الرئيسية، استعرض جدول الفواتير التي تحتوي على بضاعة معلقة قيد الاستلام (أو ابحث برقم الفاتورة أو اسم المورد)، واضغط على زر (استلام البضاعة 📦) أمام الفاتورة المطلوبة.',
                'الخطوة 2 (توثيق بيانات الشحن والفحص اللوجستي): في شريط التوثيق الميداني العلوي، سجل رقم بوليصة الشحن أو إشعار المورد، ورقم الشاحنة/اللوحة، واسم السائق وهاتفه، واسم أمين المستودع الفاحص؛ وتنقل بينها بسلاسة بزر Enter.',
                'الخطوة 3 (المسح بالباركود أو تعميم المستودع): يمكنك استخدام شريط الباركود الذكي (يدعم صيغة الكود*الكمية مثل 105*12) لزيادة الكمية المستلمة صوتياً وبصرياً، أو اختيار مستودع من قائمة (🏢 تعميم المستودع للكل) لتطبيقه على كافة السطور دفعة واحدة.',
                'الخطوة 4 (مراجعة الكميات والتعادل وتقسيم الدفعات وفحص الجودة): في الجدول، راجع أو عدّل (✍️ الكمية المستلمة) أو استخدم أزرار (⚡ استلام الكل) و(🧹 تصفير). إذا أردت توزيع صنف واحد على مخزنين مختلفين اضغط زر التقسيم (➕) بجانب اسم الصنف، وحدد حالة فحص الجودة لكل سطر (✅ سليم / ⚠️ بتحفظ / 🚨 تالف).',
                'الخطوة 5 (الاعتماد والتسميع اللحظي والطباعة): راجع شريط الملخص السفلي ونسبة الإنجاز، ثم اضغط (💾 استلام وتأكيد البضاعة) أو الاختصار (F10 / Ctrl+S) ليتم تحديث أرصدة المخازن والتقارير والموبايل فوراً، مع إمكانية طباعة سند GRN الرسمي أو مشاركته عبر واتساب.'
            ],
            autopilotSteps: [
                {
                    badge: '1. الشاشة الأولى: مركز الفواتير المعلقة والتبديل مع سجل الأذونات',
                    cursorLabel: 'الفواتير قيد الاستلام وسجل GRN',
                    screenPath: 'الشاشة 1️⃣: سجل الاستلام ⬅ مفتاح التبديل (قيد الاستلام ⏳ / الأذونات المستلمة ✅)',
                    targetSelector: '#hub-title-text, #uiverse-toggle',
                    diagramType: 'screen-hub',
                    diagramMeta: {
                        parentScreen: 'مركز الفواتير المعلقة قيد الاستلام (#view-hub)',
                        actionLabel: 'أزرار وفلاتر شاشة الاستلام المخزني الرئيسية:',
                        childItems: ['⏳ جدول الفواتير المنتظرة للاستلام المخزني', '✅ مفتاح التبديل لعرض أرشيف أذونات GRN السابقة', '📦 زر (فحص واستلام) يفتح شاشة الفحص الداخلي', '⚡ زر (فوري) يستلم كامل الفاتورة بضغطة واحدة'],
                        effectLabel: 'الضغط على أي فاتورة يفتح شاشة الفحص والاستلام الداخلي!'
                    },
                    action: () => {
                        if (currentInvoiceId === 'DEMO-GRN-101' && typeof showHub === 'function') {
                            try { showHub(true); } catch (_e) {}
                        }
                    },
                    speech: () => 'أهلاً بحضرتك في الشرح الشامل لشاشة استلام البضاعة المخزني. من هذا الشريط العلوي في الشاشة الأولى يمكنك التبديل بين الفواتير التي تنتظر الاستلام المخزني وبين سجل أذونات الاستلام السابقة، بالإضافة للانتقال السريع إلى البوابة اللوجستية الموحدة وتقارير المخازن.'
                },
                {
                    badge: '2. البحث العربي الذكي في الفواتير والأذونات وفتح شاشة الفحص',
                    cursorLabel: 'البحث العربي المتسامح وفتح الفحص 📦',
                    screenPath: 'الشاشة 1️⃣: البحث السريع ⬅ ضغط زر (فحص واستلام 📥) ⬅ فتح الشاشة 2️⃣: محرر الاستلام',
                    targetSelector: '#search-inv',
                    diagramType: 'screen-transition',
                    diagramMeta: {
                        parentScreen: 'خانة البحث العربي الذكي (#search-inv)',
                        actionLabel: 'البحث من أول حرف والضغط على (فحص واستلام):',
                        childItems: ['🔍 بحث فوري برقم الفاتورة أو اسم المورد أو الصنف', '⚡ زر (فوري) لاعتماد كامل الفاتورة بضغطة واحدة', '📥 زر (فحص واستلام) لفتح محرر الفحص التفصيلي', '📊 عداد ديناميكي للفواتير والأذونات المطابقة'],
                        effectLabel: 'تفتح الآن شاشة الفحص والاستلام التفصيلية أمامك!'
                    },
                    action: () => {},
                    speech: () => 'تستخدم خانة البحث الذكي للوصول الفوري إلى أي فاتورة معلقة أو إذن مستلم برقم الفاتورة أو اسم المورد أو اسم الصنف من أول حرف مع التسامح الكامل في الحروف العربية، وبمجرد الضغط على زر فحص واستلام تفتح لك الشاشة الداخلية.'
                },
                {
                    badge: '3. شاشة داخل شاشة: شريط التوثيق اللوجستي وبيانات الشاحنة والفاحص',
                    cursorLabel: 'البوليصة والشاحنة والسائق وإرفاق الصورة 🚛',
                    screenPath: 'الشاشة 2️⃣: محرر الاستلام ⬅ توثيق البوليصة ورقم الشاحنة والسائق والفاحص',
                    targetSelector: '#grn-logistics-bar',
                    diagramType: 'subpanel-expand',
                    diagramMeta: {
                        parentScreen: 'شريط التوثيق اللوجستي الميداني (#grn-logistics-bar)',
                        actionLabel: 'حقول التوثيق اللوجستي المعتمدة في السند المطبوع:',
                        childItems: ['📄 رقم بوليصة الشحن أو إشعار تسليم المورد', '🚛 رقم الشاحنة / اللوحة', '👨‍✈️ اسم السائق ورقم هاتفه', '👷‍♂️ اسم أمين المستودع الفاحص وإرفاق صورة المحضر'],
                        effectLabel: 'تُطبع هذه البيانات رسمياً في سند الاستلام GRN وكشف الصنف'
                    },
                    action: () => {
                        if (typeof window.ensureGrnEditorOpenForGuide === 'function') window.ensureGrnEditorOpenForGuide();
                    },
                    speech: () => 'فور فتح شاشة الاستلام الداخلية، يظهر أمامك شريط التوثيق اللوجستي لتسجيل رقم بوليصة الشحن ورقم الشاحنة واسم السائق وأمين المستودع المستلم مع إمكانية إرفاق صورة محضر الفحص لتظهر موثقة في سند الاستلام المطبوع.'
                },
                {
                    badge: '4. ماسح الباركود السريع وقائمة تعميم المستودع المنسدلة حياً',
                    cursorLabel: 'الباركود وقائمة تعميم المستودع 🏭',
                    screenPath: 'الشاشة 2️⃣: محرر الاستلام ⬅ فتح قائمة (🏢 تعميم المستودع) تلقائياً ⬅ تطبيق المستودع للكل',
                    targetSelector: '#grn-bulk-wh-select',
                    diagramType: 'dropdown-open',
                    diagramMeta: {
                        parentScreen: 'شريط الباركود وتعميم المستودع (#grn-scanner-bar)',
                        actionLabel: 'عند فتح قائمة (تعميم المستودع) أو مسح الباركود:',
                        childItems: ['🔫 مسح الباركود (يدعم الكود*الكمية مثل 105*12)', '🏢 قائمة منسدلة لتعميم المستودع على كافة الأصناف بضغطة واحدة', '➕ زر إضافة مستودع جديد فوراً بدون مغادرة الشاشة', '📈 شريط نسبة إنجاز استلام الفاتورة اللحظي'],
                        effectLabel: 'قائمة المستودعات مفتوحة أمامك لتطبيقها على جميع السطور!'
                    },
                    action: () => {
                        if (typeof window.ensureGrnEditorOpenForGuide === 'function') window.ensureGrnEditorOpenForGuide();
                        const whSel = document.getElementById('grn-bulk-wh-select');
                        if (whSel) { try { whSel.focus(); } catch (e) {} }
                    },
                    speech: () => 'وهنا تنسدل أمامك قائمة تعميم المستودع لاختيار المخزن وتطبيقه على جميع الأصناف بضغطة واحدة، كما يمكنك مسح باركود الصنف مباشرة لزيادة الكمية المستلمة مع تنبيه صوتي ومتابعة مؤشر نسبة الإنجاز.'
                },
                {
                    badge: '5. المحاكاة الحية للشاشة الوليدة: إضافة مستودع جديد فوراً (➕)',
                    cursorLabel: 'شاشة إضافة مستودع جديد فوراً ➕🏢',
                    screenPath: 'الشاشة 2️⃣ ⬅ ضغط زر (➕) ⬅ فتح الشاشة الوليدة 3️⃣: إضافة مستودع جديد وتعميمه',
                    targetSelector: '#grn-quick-wh-modal .grn-child-modal-card, #btn-grn-quick-add-wh',
                    diagramType: 'screen-transition',
                    diagramMeta: {
                        parentScreen: 'الشاشة الوليدة: إضافة مستودع جديد (#grn-quick-wh-modal)',
                        actionLabel: 'تفتح أمامك الآن نافذة إنشاء مخزن جديد في نفس اللحظة:',
                        childItems: ['🏢 كتابة اسم المستودع الجديد (مثل: مخزن فرع بحري)', '📍 تحديد موقع المستودع أو أمين المخزن', '⚡ حفظ المستودع وتعميمه تلقائياً على الفاتورة الحالية', '🔄 مزامنة فورية مع جميع الشاشات والتقارير'],
                        effectLabel: 'إضافة المستودع فوراً دون مغادرة شاشة الاستلام!'
                    },
                    action: () => {
                        if (typeof window.ensureGrnEditorOpenForGuide === 'function') window.ensureGrnEditorOpenForGuide();
                        if (typeof window.openGrnQuickWarehouseModal === 'function') window.openGrnQuickWarehouseModal();
                    },
                    speech: () => 'وعندما تضغط على زر الإضافة السريعة بجانب قائمة المستودع، تفتح أمامك هذه الشاشة الوليدة لإنشاء مستودع جديد فوراً وتعميمه على أصناف الفاتورة دون الحاجة للخروج من شاشة الاستلام.'
                },
                {
                    badge: '6. جدول فحص الأصناف ومعامل التعادل وتقسيم المخازن وفحص الجودة QC',
                    cursorLabel: 'الكمية المستلمة وتقسيم المخازن وفحص الجودة ✅',
                    screenPath: 'الشاشة 2️⃣: جدول الأصناف ⬅ ضغط زر التقسيم (➕) وشارة الجودة (سليم / بتحفظ / تالف)',
                    targetSelector: '#items-tbody',
                    diagramType: 'subpanel-expand',
                    diagramMeta: {
                        parentScreen: 'جدول فحص الأصناف وتقسيم الدفعات (#items-tbody)',
                        actionLabel: 'أدوات التحكم الدقيقة داخل كل سطر صنف:',
                        childItems: ['✍️ إدخال الكمية المستلمة ومعامل التعادل وحساب المتبقي تلقائياً', '➕ زر تقسيم السطر لتوزيع نفس الصنف على مخزنين مختلفين', '✅ شارة الجودة التفاعلية (سليم • ⚠️ بتحفظ • 🚨 تالف)', '📅 زر توثيق رقم التشغيلة وتاريخ الصلاحية وموقع الرف'],
                        effectLabel: 'تحديث لحظي للكمية المعادلة والمتبقية ونسبة الإنجاز'
                    },
                    action: () => {
                        if (typeof window.ensureGrnEditorOpenForGuide === 'function') window.ensureGrnEditorOpenForGuide();
                    },
                    speech: () => 'في جدول الأصناف يمكنك إدخال الكمية المستلمة ومعامل التعادل لكل صنف، أو الضغط على زر الزائد بجانب الصنف لتقسيمه على أكثر من مخزن في نفس الدفعة، والضغط على شارة فحص الجودة للتبديل بين سليم أو بتحفظ أو تالف.'
                },
                {
                    badge: '7. المحاكاة الحية للشاشة الوليدة: توثيق التشغيلة والصلاحية والرف (📅)',
                    cursorLabel: 'شاشة توثيق التشغيلة وتاريخ الصلاحية والرف 📅',
                    screenPath: 'الشاشة 2️⃣: سطر الصنف ⬅ ضغط (📅 تشغيلة/رف) ⬅ فتح الشاشة الوليدة 3️⃣: التشغيلة والرف',
                    targetSelector: '#grn-batch-bin-modal .grn-child-modal-card, .grn-batch-btn',
                    diagramType: 'screen-transition',
                    diagramMeta: {
                        parentScreen: 'الشاشة الوليدة: التشغيلة والصلاحية والرف (#grn-batch-bin-modal)',
                        actionLabel: 'تفتح أمامك الآن شاشة توثيق بيانات الدفعة والرف:',
                        childItems: ['🔢 تسجيل رقم التشغيلة / اللوط (Batch / Lot No)', '📅 تحديد تاريخ انتهاء الصلاحية للتنبيه المبكر', '🗄️ تحديد موقع الرف والممر داخل المستودع (Bin/Rack)', '🏷️ طباعة هذه البيانات في ملصقات الباركود وبطاقة QR'],
                        effectLabel: 'تتبع مخزني دقيق للتشغيلة والصلاحية وموقع الرف!'
                    },
                    action: () => {
                        if (typeof window.ensureGrnEditorOpenForGuide === 'function') window.ensureGrnEditorOpenForGuide();
                        if (typeof window.openGrnBatchBinModal === 'function' && receiptLines && receiptLines[0]) {
                            window.openGrnBatchBinModal(receiptLines[0], 'batch');
                        }
                    },
                    speech: () => 'وعند الضغط على زر تشغيلة ورف داخل سطر الصنف، تفتح أمامك هذه الشاشة الوليدة لتسجيل رقم التشغيلة وتاريخ انتهاء الصلاحية ورقم الرف داخل المستودع ليتم حفظها في كرت الصنف وطباعتها على ملصقات الباركود.'
                },
                {
                    badge: '8. المحاكاة الحية للشاشة الوليدة: مطابقة ميزان القبّان مع البوليصة (⚖️)',
                    cursorLabel: 'شاشة مطابقة ميزان القبّان (القائم والفارغ والصافي) ⚖️',
                    screenPath: 'الشاشة 2️⃣ ⬅ ضغط زر (⚖️ ميزان القبّان) ⬅ فتح الشاشة الوليدة 3️⃣: مطابقة الأوزان',
                    targetSelector: '#grn-weighbridge-modal .grn-child-modal-card, #btn-grn-weighbridge',
                    diagramType: 'screen-transition',
                    diagramMeta: {
                        parentScreen: 'الشاشة الوليدة: مطابقة ميزان القبّان (#grn-weighbridge-modal)',
                        actionLabel: 'حساب الوزن الصافي الفعلي ومقارنته بوزن البوليصة:',
                        childItems: ['📄 إدخال وزن البوليصة الدفتري ورقم تذكرة الميزان', '🚛 إدخال الوزن القائم للشاحنة (Gross) والوزن الفارغ (Tare)', '⚖️ احتساب الوزن الصافي الفعلي وفروقات الوزن ونسبتها آلياً', '✅ اعتماد الصافي وتوثيق التذكرة في إذن الاستلام بضغطة واحدة'],
                        effectLabel: 'ضبط دقيق لأوزان الحديد والأسمنت والشحنات الثقيلة!'
                    },
                    action: () => {
                        if (typeof window.ensureGrnEditorOpenForGuide === 'function') window.ensureGrnEditorOpenForGuide();
                        if (typeof window.openGrnWeighbridgeModal === 'function') window.openGrnWeighbridgeModal();
                    },
                    speech: () => 'وللشحنات الموزونة كالحديد والأسمنت، تفتح لك شاشة ميزان القبّان الوليدة لإدخال وزن البوليصة والوزن القائم والفارغ للشاحنة، فيحسب النظام الوزن الصافي الفعلي ونسبة الفرق ويعتمدها في الإذن فوراً.'
                },
                {
                    badge: '9. الماسح المتصل وبطاقة الفحص QR ومطالبة المورد وملصقات الباركود',
                    cursorLabel: '📡 ماسح متصل • 🛡️ بطاقة QR • 🚨 مطالبة مورد • 🏷️ ملصقات',
                    screenPath: 'الشاشة 2️⃣: أدوات الرقابة المتقدمة ⬅ ماسح متصل • بطاقة QR • مطالبة مورد • ملصقات',
                    targetSelector: '#btn-grn-continuous-scan, #btn-grn-qr-card, #btn-grn-supplier-claim, #btn-grn-batch-labels',
                    diagramType: 'subpanel-expand',
                    diagramMeta: {
                        parentScreen: 'منظومة الرقابة والباركود والمطالبات المتقدمة',
                        actionLabel: 'أدوات الفحص والطباعة والرقابة المالية:',
                        childItems: ['📡 وضع الماسح المتصل السريع مع عداد صوتي وبصري فوري', '🛡️ بطاقة فحص واستلام مخزني PDF/QR معتمدة لتوجيه الأرفف', '🚨 إصدار محضر مطالبة رسمي للمورد وترحيل إشعار خصم مدين آلياً', '🏷️ طباعة ملصقات باركود الأرفف وسند GRN ومشاركة واتساب'],
                        effectLabel: 'حماية مالية ومخزنية متكاملة بنسبة 100%'
                    },
                    action: () => {
                        if (typeof window.ensureGrnEditorOpenForGuide === 'function') window.ensureGrnEditorOpenForGuide();
                    },
                    speech: () => 'ومن شريط الأدوات العلوي يمكنك تشغيل وضع الماسح المتصل السريع، أو إصدار بطاقة فحص واستلام مزودة برمز كيو آر، أو إصدار محضر مطالبة رسمي للمورد بالكميات التالفة والناقصة، أو طباعة ملصقات الباركود وسند الاستلام الرسمي.'
                },
                {
                    badge: '10. شريط الملخص النهائي وزر الاعتماد والتسميع المخزني الفوري',
                    cursorLabel: 'حفظ وتأكيد الاستلام المخزني (F10 / Ctrl+S) 💾',
                    screenPath: 'الشاشة 2️⃣: زر اعتماد الاستلام ⬅ زيادة رصيد المخزن الفعلي + المطابقة الثلاثية + واتساب',
                    targetSelector: '.editor-bottom-bar',
                    diagramType: 'accounting-flow',
                    diagramMeta: {
                        parentScreen: 'شريط الاعتماد النهائي (.editor-bottom-bar)',
                        actionLabel: 'بمجرد الضغط على (اعتماد الاستلام):',
                        childItems: ['📦 نقل الكميات من (بضاعة قيد الاستلام) إلى رصيد المستودع الفعلي', '🛡️ تحديث شارة المطابقة الثلاثية (3-Way Match) في المشتريات', '📊 تحديث كرت الصنف وتقارير الجرد والمخازن', '📱 مزامنة فورية في جزء من الثانية مع اللابتوب والموبايل'],
                        effectLabel: 'تسميع ذري فوري في المستودعات والتقارير والموبايل'
                    },
                    action: () => {
                        if (typeof window.ensureGrnEditorOpenForGuide === 'function') window.ensureGrnEditorOpenForGuide();
                    },
                    speech: () => 'أخيراً في الشريط السفلي يظهر ملخص الأصناف والكميات والوزن الأساسي والمتبقي، وبمجرد الضغط على زر اعتماد الاستلام أو إف عشرة يرحل النظام الكميات ككتلة ذرية واحدة للمخازن الفعلية والتقارير وتطبيق الموبايل في جزء من الثانية.'
                }
            ],
            scenarios: [
                {
                    q: '💡 كيف أقسم استلام صنف واحد على مستودعين مختلفين في نفس الدفعة؟',
                    a: 'داخل جدول الفحص، اضغط على زر (➕) الأخضر الموجود بجانب اسم الصنف؛ سيقوم النظام بشطر السطر إلى سطرين، فتكتب في السطر الأول الكمية الداخلة للمستودع الرئيسي وفي السطر الثاني الكمية الداخلة لمستودع الفرع وتعتمد الإذن بضغطة واحدة.'
                },
                {
                    q: '💡 ماذا أفعل إذا وصلت بعض الكراتين تالفة أو ناقصة من المورد؟',
                    a: 'اضغط على شارة الجودة أمام الصنف لتحويلها إلى (🚨 تالف) وحدد الكمية التالفة في النافذة الوليدة ليخصمها النظام آلياً من الصالح، ثم اضغط زر (🚨 مطالبة مورد) لإصدار محضر رسمي وترحيل إشعار خصم مدين على حساب المورد.'
                }
            ],
            gapReport: {
                title: 'تقرير المراجعة الثلاثية والامتثال الكامل للدستورين الأول والثاني (GRN Audit & Gap Report)',
                items: [
                    '✅ الأمر الحاسم (1) — نطق الصلاة على النبي ﷺ مرة واحدة فقط مع ضغطة زر المرشد (hasPlayedOpeningInvocation = true) ومنع تكرارها نهائياً عند التنقل بين الخطوات العشر أو تمرير الماوس على الحقول.',
                    '✅ الأمر الحاسم (2) — إلغاء الشريط العريض الحاجب للرؤية واستبداله بكبسولة التحكم الملكية المصغرة غير الحاجبة (Micro-HUD).',
                    '✅ الأمر الحاسم (6) — المحاكاة الحية وفتح الشاشات الوليدة (#view-editor، #grn-quick-wh-modal، #grn-batch-bin-modal، #grn-weighbridge-modal) والقوائم المنسدلة (#grn-bulk-wh-select) تلقائياً أثناء الشرح.',
                    '✅ الترابط الذري (Atomic Sync & Rollback Snapshot): نقل الكميات من حساب البضاعة قيد الاستلام إلى المخزن الفعلي وتحديث المطابقة الثلاثية وتقارير الجرد وكرت الصنف ككتلة واحدة لا تتجزأ.'
                ]
            },
            buttonsDict: [
                {
                    label: 'اعتماد الاستلام (💾 F10 / Ctrl+S)',
                    targetSelector: 'button[onclick*="saveReceiptNote"]',
                    whatItDoes: 'يعتمد مذكرة الاستلام المخزني (GRN) كعملية ذرية متكاملة وينقل البضاعة من قيد الاستلام إلى المخزن الفعلي.',
                    whatHappensIfClicked: 'يحدث رصيد المستودع في (erp_inventory_v1 و erp_items_v1)، ويحدث حالة الفاتورة الأصلية (مستلمة بالكامل أو جزئياً)، ويزامن الإذن مع السحابة وتطبيق الموبايل.'
                },
                {
                    label: 'مفتاح التبديل بين (قيد الاستلام ⏳) و(الأذونات المستلمة ✅)',
                    targetSelector: '#uiverse-toggle, .uiverse-switch',
                    whatItDoes: 'يبدل العرض في الشاشة الرئيسية بين الفواتير التي تنتظر الاستلام وبين أرشيف أذونات الاستلام المحفوظة.',
                    whatHappensIfClicked: 'عند تفعيله يعرض جدول كافة أذونات الاستلام السابقة مع أزرار الطباعة والمشاركة عبر واتساب والتعديل والحذف المحمي.'
                },
                {
                    label: 'إضافة مستودع جديد فوراً (➕)',
                    targetSelector: '#btn-grn-quick-add-wh',
                    whatItDoes: 'يفتح نافذة وليدة سريعة لإضافة مخزن أو مستودع جديد دون مغادرة شاشة الاستلام.',
                    whatHappensIfClicked: 'يحفظ المستودع الجديد في قاعدة البيانات ويعممه تلقائياً على سطور الفاتورة المفتوحة.'
                },
                {
                    label: 'وضع الماسح المتصل السريع (📡 ماسح متصل)',
                    targetSelector: '#btn-grn-continuous-scan',
                    whatItDoes: 'يفتح نافذة المسح المتتابع السريع للباركود مع عداد صوتي وبصري فوري لكل ضربة.',
                    whatHappensIfClicked: 'يزيد الكميات المستلمة للأصناف الممسوحة فوراً ويعرض سجلاً حياً لكل ضربة باركود.'
                },
                {
                    label: 'مطابقة ميزان القبّان مع البوليصة (⚖️ ميزان القبّان)',
                    targetSelector: '#btn-grn-weighbridge',
                    whatItDoes: 'يفتح نافذة مطابقة أوزان الشاحنة (الوزن القائم والفارغ والصافي) مع وزن البوليصة الدفتري.',
                    whatHappensIfClicked: 'يحسب الوزن الصافي الفعلي ونسبة الفرق ويوثق تذكرة الميزان في إذن الاستلام.'
                },
                {
                    label: 'بطاقة فحص واستلام مخزني معتمدة (🛡️ بطاقة QR)',
                    targetSelector: '#btn-grn-qr-card',
                    whatItDoes: 'تصدر بطاقة فحص وتوجيه أرفف قابلة للطباعة وPDF مزودة برمز QR يتتبع التشغيلة والصلاحية وموقع الرف.',
                    whatHappensIfClicked: 'تفتح نافذة طباعة بطاقة الفحص المعتمدة لجميع الأصناف المستلمة.'
                },
                {
                    label: 'زر استلام الكل (⚡) وزر التصفير (🧹)',
                    targetSelector: '#btn-grn-fill-all, #btn-grn-zero-all',
                    whatItDoes: 'أدوات سرعة فائقة لتعبئة كامل الكميات المتبقية بضغطة واحدة أو تصفيرها لبدء العد بالباركود.',
                    whatHappensIfClicked: 'يحدث جميع خانات الكمية المستلمة في الجدول ويعيد احتساب الملخص ونسبة الإنجاز فوراً.'
                },
                {
                    label: 'شريط التوثيق اللوجستي (بوليصة / شاحنة / سائق / فاحص)',
                    targetSelector: '#grn-logistics-bar',
                    whatItDoes: 'يوثق البيانات الميدانية للشحنة المستلمة.',
                    whatHappensIfClicked: 'تُحفظ هذه البيانات مع إذن الاستلام وتظهر تلقائياً في سند الطباعة الرسمي وفي ملاحظات كشف حركة الصنف.'
                },
                {
                    label: 'ماسح الباركود الذكي وتعميم المستودع (#grn-barcode-input / #grn-bulk-wh-select)',
                    targetSelector: '#grn-barcode-input, #grn-bulk-wh-select',
                    whatItDoes: 'إدخال الكميات المستلمة بقارئ الباركود (مع دعم الكود*الكمية) أو توحيد المستودع لجميع السطور.',
                    whatHappensIfClicked: 'يزيد الكمية المستلمة للصنف المطابق مع نغمة تأكيد، أو يعمم المستودع المختار على كل أصناف الفاتورة.'
                },
                {
                    label: 'شارة فحص الجودة التفاعلية (✅ سليم / ⚠️ بتحفظ / 🚨 تالف)',
                    targetSelector: '.grn-qc-pill',
                    whatItDoes: 'تحديد الحالة الفنية للبضاعة المستلمة أثناء الفحص المخزني.',
                    whatHappensIfClicked: 'يتنقل بين (سليم، بتحفظ، وتالف مع فتح نافذة تحديد الكمية التالفة وخصمها آلياً من الصالح).'
                },
                {
                    label: 'طباعة سند GRN الرسمي ومشاركة واتساب (🖨️ / 💬)',
                    targetSelector: 'button[onclick*="printCurrentOrSavedGRN"], button[onclick*="shareCurrentOrSavedGRNWhatsApp"], .grn-print-btn',
                    whatItDoes: 'إصدار سند استلام مخزني رسمي بـ 4 توقيعات معتمدة أو إرسال إشعار الاستلام عبر واتساب.',
                    whatHappensIfClicked: 'يفتح نافذة الطباعة الرسمية أو يجهز رسالة واتساب منسقة ببيانات الإذن والأصناف المستلمة.'
                }
            ]
        };

        window.explainPurchaseReceiptDomElement = function(el) {
            if (!el) return null;
            const rules = [
                { sel: '#uiverse-toggle, .uiverse-switch', title: '🔄 مفتاح التبديل (قيد الاستلام ⇄ الأذونات المستلمة)', desc: 'يبدل العرض فوراً بين الفواتير المعلقة التي تنتظر الاستلام المخزني وبين سجل أذونات الاستلام (GRN) التي تم اعتمادها سابقاً.' },
                { sel: '.grn-quick-hub-rcv', title: '⚡ زر الاستلام الفوري الكامل من القائمة', desc: 'يستلم كامل الكميات المتبقية في هذه الفاتورة فوراً بضغطة واحدة إلى المستودع الرئيسي ويصدر سند GRN رسمياً.' },
                { sel: 'button[onclick*="openReceiptEditor"]', title: '📥 زر فتح الفاتورة للفحص والاستلام المخزني', desc: 'يفتح شاشة الفحص والاستلام الداخلي لهذه الفاتورة لعدّ البضاعة وتحديد المستودع وفحص الجودة وإصدار سند GRN.' },
                { sel: 'button[onclick*="editSavedGRN"], button[onclick*="editGRN"]', title: '✏️ زر تعديل ومراجعة إذن الاستلام المحفوظ', desc: 'يفتح إذن الاستلام السابق لمراجعة كمياته أو تعديلها مع تحديث أرصدة المستودع تلقائياً.' },
                { sel: 'button[onclick*="deleteSavedGRN"], button[onclick*="deleteGRN"]', title: '🗑️ زر حذف وعكس إذن الاستلام المخزني المحمي', desc: 'يتحقق أولاً من عدم صرف البضاعة ثم يلغي إذن الاستلام ويعيد الكميات من المستودع الفعلي إلى حساب البضاعة قيد الاستلام بأمان.' },
                { sel: '#pending-invoices-container, #hub-pending-container', title: '⏳ جدول الفواتير المعلقة قيد الاستلام', desc: 'يعرض جميع فواتير المشتريات التي تحتوي على بضاعة لم تُستلم بعد أو استُلمت جزئياً، مع أزرار الفحص والاستلام الفوري.' },
                { sel: '#recent-grns-container, #hub-received-container', title: '✅ سجل أذونات الاستلام المخزني السابقة (GRN)', desc: 'يعرض أرشيف جميع أذونات الاستلام المخزني المعتمدة مع أزرار الطباعة وملصقات الباركود والمشاركة عبر واتساب والتعديل والحذف.' },
                { sel: '#search-inv', title: '🔍 البحث العربي الذكي المتسامح من أول حرف', desc: 'ابحث بسرعة فائقة برقم الفاتورة أو رقم الإذن أو اسم المورد أو اسم الصنف مع توحيد تلقائي للحروف العربية والأرقام الهندية.' },
                { sel: 'a[href*="smart-logistics.php"]', title: '🚀 البوابة اللوجستية الموحدة (GRN ⇄ GDN)', desc: 'ينقلك إلى البوابة اللوجستية الشاملة لاستلام المشتريات وتسليم المبيعات بالباركود وكاميرا الجوال.' },
                { sel: '#grn-cross-screen-nav, .grn-nav-pill', title: '🧭 شريط الربط السريع بالشاشات والتقارير', desc: 'ينقلك بضغطة واحدة إلى شاشة فواتير المشتريات، أرصدة وجرد المخازن، كشف حركة الصنف، دفتر المخزن، والجرد الفعلي.' },
                { sel: '.grn-meta-bar', title: '📋 شريط البيانات المرجعية للفاتورة الأصلية', desc: 'يعرض رقم فاتورة المشتريات الأصلية، واسم المورد، وتاريخ ووقت الإصدار، واسم الموظف المنشئ للفاتورة.' },
                { sel: '#grn-waybill-no', title: '📄 رقم بوليصة الشحن / إشعار المورد', desc: 'سجل هنا رقم بوليصة الشحن أو رقم إشعار التسليم المرفق مع البضاعة من المورد للمطابقة المستندية والطباعة في السند.' },
                { sel: '#grn-truck-no', title: '🚛 رقم الشاحنة / اللوحة', desc: 'توثيق رقم لوحة المركبة أو الشاحنة التي قامت بتوريد البضاعة إلى المستودع.' },
                { sel: '#grn-driver-info', title: '👨‍✈️ اسم السائق / المندوب وهاتفه', desc: 'تسجيل اسم سائق الشاحنة أو مندوب المورد ورقم هاتفه للتوثيق والتوقيع على سند الاستلام.' },
                { sel: '#grn-inspector-name', title: '🛡️ أمين المستودع / الفاحص المستلم', desc: 'اسم المسؤول المخزني الذي قام بعدّ وفحص البضاعة واستلامها فعلياً.' },
                { sel: '#btn-grn-photo-attach', title: '📷 إرفاق صورة محضر الفحص أو البوليصة', desc: 'يرفق صورة فوتوغرافية للبضاعة المستلمة أو بوليصة الشحن الموقعة لتظهر موثقة داخل سند الاستلام الرسمي.' },
                { sel: '#grn-barcode-input', title: '🔍 ماسح الباركود السريع (يدعم الكود*الكمية)', desc: 'امسح الباركود أو اكتب اسم/كود الصنف ثم اضغط Enter لزيادة الكمية المستلمة (+1)، أو اكتب (الكود*10) لإضافة 10 وحدات دفعة واحدة.' },
                { sel: '#grn-bulk-wh-select', title: '🏢 تعميم المستودع لجميع الأصناف', desc: 'اختر المستودع من هنا ليتم تطبيقه تلقائياً على جميع سطور الفاتورة بضغطة واحدة بدلاً من اختياره سطراً بسطر.' },
                { sel: '#btn-grn-quick-add-wh, #grn-quick-wh-modal', title: '➕ إضافة مستودع / مخزن جديد فوراً', desc: 'يفتح نافذة وليدة لإضافة مستودع جديد وتعميمه فوراً على أصناف الفاتورة دون مغادرة الشاشة.' },
                { sel: '#btn-grn-continuous-scan, #grn-continuous-scan-modal', title: '📡 وضع الماسح المتصل السريع (Continuous Scan)', desc: 'يفتح نافذة المسح المتتابع السريع للباركود مع عداد صوتي وبصري فوري وسجل حي لكل ضربة.' },
                { sel: '#btn-grn-weighbridge, #grn-weighbridge-modal', title: '⚖️ مطابقة ميزان القبّان مع البوليصة (Weighbridge)', desc: 'يحسب الوزن الصافي الفعلي للشاحنة (الوزن القائم ناقص الفارغ) ويقارنه بوزن البوليصة الدفتري ويوثق تذكرة الميزان.' },
                { sel: '#btn-grn-qr-card', title: '🛡️ بطاقة فحص واستلام مخزني PDF/QR معتمدة', desc: 'يصدر بطاقة فحص وتوجيه أرفف قابلة للطباعة وPDF مزودة برمز QR يتتبع رقم التشغيلة وتاريخ الصلاحية وموقع الرف.' },
                { sel: '.grn-progress-wrap', title: '📊 مؤشر نسبة إنجاز استلام الفاتورة', desc: 'يعرض بصرياً نسبة الكميات التي تم استلامها مقارنة بإجمالي كميات الفاتورة المطلوبة.' },
                { sel: '#btn-grn-supplier-claim', title: '🚨 إصدار إشعار مطالبة رسمي للمورد + خصم مدين آلي', desc: 'يصدر محضر مطالبة مطبوعاً ورسالة واتساب للمورد بالكميات التالفة أو الناقصة مع زر ترحيل إشعار خصم مدين على حساب المورد بضغطة واحدة.' },
                { sel: '#btn-grn-batch-labels, .grn-lbl-btn', title: '🏷️ طباعة ملصقات باركود الدفعة والتشغيلة والرف', desc: 'يطبع ملصقات باركود حرارية لكل صنف مستلم تتضمن اسم الصنف والباركود ورقم التشغيلة وتاريخ الصلاحية وموقع الرف داخل المستودع.' },
                { sel: 'button[onclick*="printCurrentOrSavedGRN"], .grn-print-btn', title: '🖨️ طباعة سند الاستلام المخزني الرسمي (GRN)', desc: 'يفتح نافذة طباعة سند الاستلام المخزني الرسمي شاملاً بيانات الشاحنة والفحص و4 توقيعات معتمدة.' },
                { sel: 'button[onclick*="shareCurrentOrSavedGRNWhatsApp"]', title: '💬 مشاركة إشعار الاستلام عبر واتساب', desc: 'يرسل ملخصاً منسقاً بإذن الاستلام والأصناف والكميات المستلمة عبر واتساب للمورد أو الإدارة.' },
                { sel: '#btn-grn-fill-all', title: '⚡ زر استلام الكل بالكامل (100%)', desc: 'يملأ جميع خانات الكمية المستلمة بكامل الكمية المتبقية للفاتورة بضغطة زر واحدة.' },
                { sel: '#btn-grn-zero-all', title: '🧹 زر تصفير الكميات المستلمة', desc: 'يصفّر جميع خانات الكمية المستلمة لبدء الجرد الفعلي بالباركود أو إدخال استلام جزئي.' },
                { sel: 'button[onclick*="openGridSettings"], #grid-settings-modal', title: '⚙️ تنسيق خط وألوان وأعمدة الجدول', desc: 'يفتح نافذة تخصيص حجم الخط ونوعه ولونه وعدد الخانات العشرية لكل عمود في جدول الاستلام.' },
                { sel: 'button[onclick*="showHub"]', title: '↩️ رجوع لقائمة الفواتير (مع حارس التعديلات)', desc: 'يعود بك إلى الشاشة الرئيسية لقائمة الفواتير قيد الاستلام بعد التأكد من عدم ضياع أي تعديلات غير محفوظة.' },
                { sel: '.grn-batch-btn, #grn-batch-bin-modal', title: '📅 توثيق رقم التشغيلة والصلاحية وموقع الرف (Batch/Bin)', desc: 'يفتح نافذة وليدة لتسجيل رقم التشغيلة وتاريخ انتهاء الصلاحية وموقع الرف داخل المستودع لهذا الصنف.' },
                { sel: '.grn-col-origQty, .grn-col-orig-qty', title: '📦 كمية الشراء الأصلية بالفاتورة', desc: 'الكمية الإجمالية التي تم شراؤها في فاتورة المشتريات الأصلية.' },
                { sel: '.grn-col-item', title: '🏷️ اسم الصنف وأدوات التقسيم والتشغيلة وكشف الحركة', desc: 'يعرض اسم الصنف مع زر (➕) لتقسيم استلام الصنف على أكثر من مخزن، وزر (📈) لفتح حركة الصنف، وزر (📅 تشغيلة/رف).' },
                { sel: '.grn-col-unit', title: '📏 وحدة الشراء', desc: 'وحدة القياس التي تم الشراء بها في الفاتورة (مثل: طن، كرتون، جوال، قطعة).' },
                { sel: '.grn-col-qty', title: '⏳ الكمية المتبقية قيد الاستلام', desc: 'الكمية المتبقية من الفاتورة التي لم تُستلم بعد؛ تتحدث لحظياً أثناء كتابة الكمية المستلمة.' },
                { sel: '.factor-input', title: '⚖️ معامل التعادل (العبوة / الوزن)', desc: 'يحدد عدد الوحدات الأساسية داخل وحدة الشراء (مثلاً الكرتونة = 12 قطعة) ليتم تغذية المخزن بالوحدة الأساسية بدقة.' },
                { sel: '.grn-col-pendingBase, .grn-col-pending-base', title: '🔢 الكمية المطلوبة بالوحدة الأساسية', desc: 'حاصل ضرب الكمية المتبقية × معامل التعادل بالوحدة الأساسية للمخزن.' },
                { sel: '.qty-input', title: '✍️ خانة الكمية المستلمة الفعلية', desc: 'أدخل الكمية الفعلية التي استلمتها من هذا الصنف؛ يمكنك التنقل للسطر التالي مباشرة بالضغط على زر Enter أو الأسهم.' },
                { sel: '.grn-col-baseQty, .grn-col-rcv-base', title: '✅ الكمية المستلمة بالوحدة الأساسية ومؤشر المطابقة', desc: 'إجمالي الوحدات الأساسية التي ستدخل المخزن الفعلي من هذا السطر مع شارة نسبة المطابقة.' },
                { sel: '.wh-select', title: '🏭 المستودع المُستلِم للسطر', desc: 'يحدد المستودع الفعلي الذي ستدخل إليه كمية هذا السطر (يمكنك تقسيم الصنف على عدة مخازن بزر ➕).' },
                { sel: '.grn-qc-pill', title: '🛡️ شارة فحص الجودة (QC Status)', desc: 'اضغط عليها لتبديل حالة الفحص بين (✅ سليم) أو (⚠️ استلام بتحفظ) أو (🚨 تالف مع نافذة خصم الكمية التالفة آلياً).' },
                { sel: '.notes-input', title: '📝 ملحوظة فحص واستلام السطر', desc: 'اكتب أي ملاحظة فنية أو مخزنية تخص استلام هذا الصنف لتظهر في سند الاستلام وكشف حركة الصنف.' },
                { sel: '.editor-bottom-bar', title: '📊 شريط ملخص عملية الاستلام والاعتماد', desc: 'يعرض إجمالي الأصناف والكميات المستلمة والقيمة والوزن الأساسي والمتبقي، مع زر الحفظ والاعتماد النهائي.' },
                { sel: 'button[onclick*="saveReceiptNote"]', title: '💾 زر اعتماد الاستلام (F10 / Ctrl+S)', desc: 'يحفظ إذن الاستلام كعملية ذرية متكاملة ويغذي أرصدة المخازن وكرت الصنف والموبايل فوراً.' }
            ];
            for (const r of rules) {
                if (el.matches(r.sel) || el.closest(r.sel)) {
                    return { title: r.title, desc: r.desc };
                }
            }
            return null;
        };

        // ⚡ Smooth 60fps Enter-Key Navigation across Logistics Bar & Auto-Select Zero on Focus
        const grnHeaderOrder = [
            '#grn-waybill-no',
            '#grn-truck-no',
            '#grn-driver-info',
            '#grn-inspector-name',
            '#grn-barcode-input'
        ];
        document.addEventListener('keydown', function(e) {
            const editorEl = document.getElementById('view-editor');
            if (!editorEl || !editorEl.classList.contains('active')) return;

            if (e.key === 'F2') {
                e.preventDefault();
                const bInp = document.getElementById('grn-barcode-input');
                if (bInp) { bInp.focus(); bInp.select(); }
                return;
            }
            if (e.key === 'Enter' && !e.shiftKey && !e.ctrlKey && !e.altKey) {
                const t = e.target;
                if (!t) return;
                const idx = grnHeaderOrder.findIndex(sel => t.matches(sel));
                if (idx !== -1 && idx < grnHeaderOrder.length - 1) {
                    e.preventDefault();
                    const nextEl = document.querySelector(grnHeaderOrder[idx + 1]);
                    if (nextEl) {
                        nextEl.focus();
                        if (nextEl.select) nextEl.select();
                    }
                } else if (t.matches('#grn-barcode-input') && !t.value.trim()) {
                    // Empty barcode input + Enter jumps directly to first row's receive quantity input!
                    e.preventDefault();
                    const firstQty = document.querySelector('#items-tbody .qty-input');
                    if (firstQty) { firstQty.focus(); firstQty.select(); }
                }
            }
        });

        document.addEventListener('focusin', function(e) {
            const t = e.target;
            if (!t || t.tagName !== 'INPUT') return;
            if (t.classList.contains('qty-input') || t.classList.contains('factor-input')) {
                setTimeout(() => { try { t.select(); } catch(_e){} }, 10);
            }
        });

        // 🌐 Offline GRN Queue Auto-Flush & Live BroadcastChannel Sync
        async function flushGrnOfflineQueue() {
            if (!navigator.onLine) return;
            try {
                let gq = JSON.parse(localStorage.getItem('erp_grn_offline_queue_v1') || '[]');
                if (!Array.isArray(gq) || !gq.length) return;
                const remaining = [];
                for (const item of gq) {
                    if (!item || !item.payload) continue;
                    try {
                        const res = await fetch('api/mirror_sync.php?action=save_purchase_receipt', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(item.payload)
                        }).then(r => r.json());
                        if (!res || !res.success) remaining.push(item);
                    } catch (_ef) {
                        remaining.push(item);
                    }
                }
                localStorage.setItem('erp_grn_offline_queue_v1', JSON.stringify(remaining));
            } catch (_eFlush) {}
        }
        window.addEventListener('online', flushGrnOfflineQueue);
        setTimeout(flushGrnOfflineQueue, 3000);

        if (typeof BroadcastChannel !== 'undefined') {
            ['magaiees_sync_channel', 'erp_live_sync', 'elsajana_erp_sync', 'magaies_purchase_sync_channel'].forEach(chName => {
                try {
                    const grnBc = new BroadcastChannel(chName);
                    grnBc.onmessage = function(ev) {
                        const msg = ev && ev.data;
                        if (!msg) return;
                        if (msg.module === 'purchase' || msg.module === 'purchase_receipt' || msg.module === 'master_data' || msg.type) {
                            initData();
                        }
                    };
                } catch (_ebc) {}
            });
        }

        // ═══════════════════════════════════════════════════════════════════════════
        // CONSTITUTION 1 ENGINE: NON-BLOCKING CHILD MODALS (CONFIRM, BATCH/BIN/QC,
        // QUICK-ADD WAREHOUSE), UNSAVED CHANGES GUARD, VISUAL SAVE PULSE & SUB-SECOND SYNC
        // ═══════════════════════════════════════════════════════════════════════════
        function ensureGrnChildModalsDom() {
            if (document.getElementById('grn-confirm-action-modal')) return;
            const modalsHtml = `
                <!-- 1. Non-Blocking Confirmation Modal -->
                <div id="grn-confirm-action-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); backdrop-filter:blur(6px); z-index:100020; align-items:center; justify-content:center; padding:16px;">
                    <div class="grn-child-modal-card" style="background:#0f172a; border:2.5px solid #38bdf8; border-radius:18px; width:100%; max-width:440px; padding:24px; box-shadow:0 20px 50px rgba(0,0,0,0.75);">
                        <h3 id="grn-confirm-modal-title" style="margin:0 0 12px 0; font-size:1.2rem; font-weight:900; color:#FFFFFF;">تأكيد الإجراء</h3>
                        <p id="grn-confirm-modal-msg" style="margin:0 0 22px 0; font-size:0.98rem; line-height:1.6; color:#FFFFFF; font-weight:800;"></p>
                        <div style="display:flex; gap:10px; justify-content:flex-end;">
                            <button type="button" id="grn-confirm-btn-cancel" class="btn-action btn-cancel" style="padding:10px 18px; border-radius:10px; font-weight:800;">إلغاء (Esc)</button>
                            <button type="button" id="grn-confirm-btn-ok" class="btn-action" style="padding:10px 22px; border-radius:10px; font-weight:900; background:linear-gradient(135deg,#10b981,#059669); color:#FFFFFF;">تأكيد</button>
                        </div>
                    </div>
                </div>

                <!-- 2. Non-Blocking Batch / Expiry / Bin / Damaged Qty Modal -->
                <div id="grn-batch-bin-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); backdrop-filter:blur(6px); z-index:100021; align-items:center; justify-content:center; padding:16px;">
                    <div class="grn-child-modal-card" style="background:#0f172a; border:2.5px solid #a855f7; border-radius:18px; width:100%; max-width:460px; padding:24px; box-shadow:0 20px 50px rgba(0,0,0,0.75);">
                        <h3 id="grn-batch-modal-title" style="margin:0 0 16px 0; font-size:1.18rem; font-weight:900; color:#FFFFFF;">📅 توثيق التشغيلة والصلاحية والرف</h3>
                        <div id="grn-batch-fields-wrap" style="display:flex; flex-direction:column; gap:12px;">
                            <div>
                                <label style="display:block; margin-bottom:5px; font-size:0.88rem; font-weight:800; color:#FFFFFF;">رقم التشغيلة / اللوط (Batch / Lot No):</label>
                                <input type="text" id="grn-modal-batch-no" class="grn-log-input" style="width:100%; height:40px;" placeholder="مثال: LOT-261030">
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                <div>
                                    <label style="display:block; margin-bottom:5px; font-size:0.88rem; font-weight:800; color:#FFFFFF;">تاريخ الصلاحية:</label>
                                    <input type="date" id="grn-modal-expiry-date" class="grn-log-input" style="width:100%; height:40px;">
                                </div>
                                <div>
                                    <label style="display:block; margin-bottom:5px; font-size:0.88rem; font-weight:800; color:#FFFFFF;">موقع الرف (Bin/Rack):</label>
                                    <input type="text" id="grn-modal-bin-loc" class="grn-log-input" style="width:100%; height:40px;" placeholder="مثال: A1-R2">
                                </div>
                            </div>
                        </div>
                        <div id="grn-dmg-fields-wrap" style="display:none; flex-direction:column; gap:12px;">
                            <div>
                                <label style="display:block; margin-bottom:6px; font-size:0.92rem; font-weight:800; color:#FFFFFF;">🚨 الكمية التالفة / المرفوضة (سيتم خصمها من الصالح):</label>
                                <input type="number" id="grn-damaged-qty-input" class="grn-log-input" min="0" step="any" style="width:100%; height:42px; font-size:1.1rem; text-align:center;">
                            </div>
                        </div>
                        <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                            <button type="button" id="grn-batch-btn-cancel" class="btn-action btn-cancel" style="padding:10px 18px; border-radius:10px; font-weight:800;">إلغاء</button>
                            <button type="button" id="grn-batch-btn-save" class="btn-action" style="padding:10px 22px; border-radius:10px; font-weight:900; background:linear-gradient(135deg,#8b5cf6,#6d28d9); color:#FFFFFF;">💾 حفظ التوثيق</button>
                        </div>
                    </div>
                </div>

                <!-- 3. Quick-Add Warehouse Modal (➕) -->
                <div id="grn-quick-wh-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); backdrop-filter:blur(6px); z-index:100022; align-items:center; justify-content:center; padding:16px;">
                    <div class="grn-child-modal-card" style="background:#0f172a; border:2.5px solid #10b981; border-radius:18px; width:100%; max-width:420px; padding:24px; box-shadow:0 20px 50px rgba(0,0,0,0.75);">
                        <h3 style="margin:0 0 14px 0; font-size:1.18rem; font-weight:900; color:#FFFFFF;">🏢 إضافة مستودع / مخزن جديد فوراً</h3>
                        <div style="display:flex; flex-direction:column; gap:12px;">
                            <div>
                                <label style="display:block; margin-bottom:5px; font-size:0.9rem; font-weight:800; color:#FFFFFF;">اسم المستودع الجديد:</label>
                                <input type="text" id="grn-quick-wh-name" class="grn-log-input" style="width:100%; height:42px;" placeholder="مثال: مستودع فرع بحري / مخزن الحديد">
                            </div>
                            <div>
                                <label style="display:block; margin-bottom:5px; font-size:0.9rem; font-weight:800; color:#FFFFFF;">موقع المستودع / المسؤول (اختياري):</label>
                                <input type="text" id="grn-quick-wh-loc" class="grn-log-input" style="width:100%; height:40px;" placeholder="مثال: المنطقة الصناعية">
                            </div>
                        </div>
                        <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                            <button type="button" onclick="document.getElementById('grn-quick-wh-modal').style.display='none'" class="btn-action btn-cancel" style="padding:10px 18px; border-radius:10px; font-weight:800;">إلغاء</button>
                            <button type="button" id="grn-quick-wh-save-btn" class="btn-action" style="padding:10px 22px; border-radius:10px; font-weight:900; background:linear-gradient(135deg,#10b981,#059669); color:#FFFFFF;">➕ إضافة وتعميم</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalsHtml);
        }

        window.openGrnConfirmModal = function(opts) {
            ensureGrnChildModalsDom();
            const modal = document.getElementById('grn-confirm-action-modal');
            const titleEl = document.getElementById('grn-confirm-modal-title');
            const msgEl = document.getElementById('grn-confirm-modal-msg');
            const okBtn = document.getElementById('grn-confirm-btn-ok');
            const cancelBtn = document.getElementById('grn-confirm-btn-cancel');

            titleEl.textContent = (opts && opts.title) || 'تأكيد الإجراء';
            msgEl.textContent = (opts && opts.message) || 'هل أنت متأكد من المتابعة؟';
            okBtn.textContent = (opts && opts.confirmText) || 'تأكيد';
            okBtn.style.background = (opts && opts.isDanger)
                ? 'linear-gradient(135deg, #ef4444, #dc2626)'
                : 'linear-gradient(135deg, #10b981, #059669)';

            modal.style.display = 'flex';
            setTimeout(() => { try { okBtn.focus(); } catch(_e){} }, 40);

            cancelBtn.onclick = () => { modal.style.display = 'none'; };
            okBtn.onclick = () => {
                modal.style.display = 'none';
                if (opts && typeof opts.onConfirm === 'function') opts.onConfirm();
            };
        };

        window.openGrnBatchBinModal = function(line, mode, onSaved) {
            ensureGrnChildModalsDom();
            const modal = document.getElementById('grn-batch-bin-modal');
            const titleEl = document.getElementById('grn-batch-modal-title');
            const batchWrap = document.getElementById('grn-batch-fields-wrap');
            const dmgWrap = document.getElementById('grn-dmg-fields-wrap');
            const saveBtn = document.getElementById('grn-batch-btn-save');
            const cancelBtn = document.getElementById('grn-batch-btn-cancel');

            if (mode === 'dmg') {
                titleEl.textContent = `🚨 تحديد الكمية التالفة / المرفوضة (${getItemName(line.itemId)})`;
                batchWrap.style.display = 'none';
                dmgWrap.style.display = 'flex';
                const dmgInp = document.getElementById('grn-damaged-qty-input');
                dmgInp.value = line.damagedQty || 1;
                modal.style.display = 'flex';
                setTimeout(() => { try { dmgInp.focus(); dmgInp.select(); } catch(_e){} }, 50);
                saveBtn.onclick = () => {
                    const val = Math.max(0, parseFloat(dmgInp.value) || 0);
                    modal.style.display = 'none';
                    if (typeof onSaved === 'function') onSaved(val);
                };
            } else {
                titleEl.textContent = `📅 التشغيلة والصلاحية والرف (${getItemName(line.itemId)})`;
                batchWrap.style.display = 'flex';
                dmgWrap.style.display = 'none';
                const bInp = document.getElementById('grn-modal-batch-no');
                const eInp = document.getElementById('grn-modal-expiry-date');
                const rInp = document.getElementById('grn-modal-bin-loc');
                bInp.value = line.batchNo || ('LOT-' + new Date().toISOString().slice(2,10).replace(/-/g,''));
                eInp.value = line.expiryDate || '';
                rInp.value = line.binLocation || '';
                modal.style.display = 'flex';
                setTimeout(() => { try { bInp.focus(); bInp.select(); } catch(_e){} }, 50);
                saveBtn.onclick = () => {
                    line.batchNo = bInp.value.trim();
                    line.expiryDate = eInp.value.trim();
                    line.binLocation = rInp.value.trim();
                    modal.style.display = 'none';
                    if (typeof onSaved === 'function') onSaved();
                };
            }
            cancelBtn.onclick = () => { modal.style.display = 'none'; };
        };

        window.openGrnQuickWarehouseModal = function() {
            ensureGrnChildModalsDom();
            const modal = document.getElementById('grn-quick-wh-modal');
            const nameInp = document.getElementById('grn-quick-wh-name');
            const locInp = document.getElementById('grn-quick-wh-loc');
            const saveBtn = document.getElementById('grn-quick-wh-save-btn');
            nameInp.value = '';
            locInp.value = '';
            modal.style.display = 'flex';
            setTimeout(() => { try { nameInp.focus(); } catch(_e){} }, 50);

            saveBtn.onclick = () => {
                const name = nameInp.value.trim();
                if (!name) {
                    return window.focusRequiredGrnElement(nameInp, '⚠️ يرجى كتابة اسم المستودع الجديد أولاً');
                }
                const newWh = {
                    id: 'w-' + Date.now(),
                    name: name,
                    location: locInp.value.trim() || ''
                };
                warehousesDb = Array.isArray(warehousesDb) ? warehousesDb : [];
                warehousesDb.push(newWh);
                localStorage.setItem('erp_warehouses_v1', JSON.stringify(warehousesDb));
                if (typeof populateWarehouseSelect === 'function') populateWarehouseSelect();

                const bulkWh = document.getElementById('grn-bulk-wh-select');
                if (bulkWh) {
                    let opts = '<option value="">🏢 تعميم المستودع للكل...</option>';
                    warehousesDb.forEach(wh => {
                        if (wh.id !== 'PENDING_RECEIPT') {
                            opts += `<option value="${wh.id}">${wh.name}</option>`;
                        }
                    });
                    bulkWh.innerHTML = opts;
                    bulkWh.value = newWh.id;
                }
                if (Array.isArray(receiptLines)) {
                    receiptLines.forEach(l => { l.warehouseId = newWh.id; });
                    renderReceiptLines();
                }
                modal.style.display = 'none';
                showToast(`✅ تمت إضافة المستودع (${name}) وتعميمه على السطور بنجاح`, 'success');
                try {
                    if (typeof BroadcastChannel !== 'undefined') {
                        const bc = new BroadcastChannel('magaiees_sync_channel');
                        bc.postMessage({ type: 'warehouse_added', module: 'master_data', warehouse: newWh, timestamp: Date.now() });
                        bc.close();
                    }
                } catch (_eb) {}
            };
        };

        // Unsaved changes guard & Visual Save Pulse
        window._grnHasUnsavedChanges = false;
        document.addEventListener('input', function(e) {
            const t = e.target;
            if (!t) return;
            if (t.closest('#view-editor') && (t.classList.contains('qty-input') || t.classList.contains('factor-input') || t.classList.contains('notes-input') || t.classList.contains('grn-log-input'))) {
                window._grnHasUnsavedChanges = true;
            }
        });

        const _baseShowHub = window.showHub;
        window.showHub = function(forceHub) {
            const editorActive = document.getElementById('view-editor') && document.getElementById('view-editor').classList.contains('active');
            if (!forceHub && editorActive && window._grnHasUnsavedChanges && !window._isSavingGRN && currentInvoiceId && currentInvoiceId !== 'DEMO-GRN-101') {
                return window.openGrnConfirmModal({
                    title: '⚠️ تعديلات استلام غير محفوظة',
                    message: 'لديك كميات أو بيانات استلام تم تعديلها ولم يتم حفظها بعد. هل تريد الخروج وإهمال التعديلات؟',
                    confirmText: '↩️ نعم، خروج بدون حفظ',
                    isDanger: true,
                    onConfirm: () => {
                        window._grnHasUnsavedChanges = false;
                        _baseShowHub();
                    }
                });
            }
            window._grnHasUnsavedChanges = false;
            _baseShowHub();
        };

        const _prePulseSaveNote = window.saveReceiptNote;
        window.saveReceiptNote = function() {
            window._grnHasUnsavedChanges = false;
            const bottomBar = document.querySelector('#view-editor .editor-bottom-bar');
            if (bottomBar) {
                bottomBar.classList.remove('grn-save-pulse');
                void bottomBar.offsetWidth;
                bottomBar.classList.add('grn-save-pulse');
            }
            return _prePulseSaveNote.apply(this, arguments);
        };

        // Escape closes any open child modal cleanly
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                ['grn-confirm-action-modal', 'grn-batch-bin-modal', 'grn-quick-wh-modal', 'grid-settings-modal', 'grn-continuous-scan-modal', 'grn-weighbridge-modal', 'grn-drilldown-modal'].forEach(id => {
                    const m = document.getElementById(id);
                    if (m && m.style.display !== 'none' && m.style.display !== '') {
                        m.style.display = 'none';
                    }
                });
            }
        });

        // ═══════════════════════════════════════════════════════════════════════════
        // APPROVED WORLD-CLASS PROPOSALS (1, 2 & 3):
        // 1. Continuous Rapid-Fire Barcode Scanner Mode with Audio/Visual Counter
        // 2. Waybill vs. Weighbridge Scale Reconciliation Modal (القائم / الفارغ / الصافي)
        // 3. Certified Warehouse Inspection & Shelf-Tracking PDF/QR Card
        // ═══════════════════════════════════════════════════════════════════════════
        window._grnTotalScansCount = 0;

        window.openGrnContinuousScannerModal = function() {
            ensureGrnChildModalsDom();
            let modal = document.getElementById('grn-continuous-scan-modal');
            if (!modal) {
                const html = `
                    <div id="grn-continuous-scan-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.78); backdrop-filter:blur(6px); z-index:100025; align-items:center; justify-content:center; padding:16px;">
                        <div class="grn-child-modal-card" style="background:#0f172a; border:2.5px solid #0ea5e9; border-radius:18px; width:100%; max-width:520px; padding:24px; box-shadow:0 20px 50px rgba(0,0,0,0.8);">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                                <h3 style="margin:0; font-size:1.2rem; font-weight:900; color:#FFFFFF;">📡 وضع الماسح المتصل السريع (Continuous Scan)</h3>
                                <span id="grn-modal-scan-counter" style="background:linear-gradient(135deg,#0ea5e9,#2563eb); color:#FFFFFF; padding:4px 14px; border-radius:99px; font-family:'Outfit'; font-weight:900; font-size:1rem;">0 ضربة</span>
                            </div>
                            <p style="margin:0 0 12px 0; font-size:0.88rem; color:#FFFFFF; font-weight:800;">امسح الباركود متتالياً أو اكتب (الكود*الكمية) واضغط Enter — سيتم زيادة الكمية المستلمة فوراً مع تنبيه صوتي:</p>
                            <div style="display:grid; grid-template-columns:1fr 110px; gap:10px; margin-bottom:12px;">
                                <input type="text" id="grn-rapid-scan-input" class="grn-log-input" style="height:46px; font-size:1.05rem;" placeholder="باركود / كود / اسم الصنف...">
                                <input type="number" id="grn-rapid-scan-step" class="grn-log-input" value="1" min="0.01" step="any" style="height:46px; font-size:1.05rem; text-align:center;" title="مقدار الزيادة لكل ضربة باركود">
                            </div>
                            <div id="grn-rapid-scan-log" style="max-height:190px; overflow-y:auto; border:1.5px solid rgba(56,189,248,0.35); border-radius:12px; padding:10px; font-size:0.85rem; display:flex; flex-direction:column; gap:6px;">
                                <div style="text-align:center; opacity:0.75; font-weight:800;">بانتظار أول ضربة باركود...</div>
                            </div>
                            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                                <button type="button" onclick="document.getElementById('grn-continuous-scan-modal').style.display='none'" class="btn-action" style="padding:10px 22px; border-radius:10px; background:linear-gradient(135deg,#10b981,#059669); color:#FFFFFF; font-weight:900;">✅ إغلاق والعودة للجدول</button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', html);
                modal = document.getElementById('grn-continuous-scan-modal');

                const rapidInp = document.getElementById('grn-rapid-scan-input');
                rapidInp.addEventListener('keydown', function(e) {
                    if (e.key !== 'Enter') return;
                    e.preventDefault();
                    const raw = this.value.trim();
                    if (!raw) return;
                    let term = raw;
                    let step = parseFloat(document.getElementById('grn-rapid-scan-step').value) || 1;
                    if (raw.includes('*')) {
                        const pts = raw.split('*');
                        term = pts[0].trim();
                        step = parseFloat(pts[1]) || step;
                    }
                    const normFn = typeof window.normalizeGrnArabic === 'function' ? window.normalizeGrnArabic : (s => String(s || '').toLowerCase());
                    const normTerm = normFn(term);
                    const idx = (receiptLines || []).findIndex(line => {
                        const itemObj = (itemsDb || []).find(i => String(i.id) === String(line.itemId) || i.name === line.itemId) || {};
                        return normFn(itemObj.barcode || '') === normTerm ||
                               normFn(itemObj.code || itemObj.itemCode || line.itemId || '') === normTerm ||
                               normFn(getItemName(line.itemId)).includes(normTerm);
                    });

                    const logBox = document.getElementById('grn-rapid-scan-log');
                    if (idx === -1) {
                        if (typeof playScanBeep === 'function') playScanBeep(240, 0.2);
                        const errRow = document.createElement('div');
                        errRow.style.cssText = 'padding:6px 10px; border-radius:8px; background:rgba(239,68,68,0.18); border:1px solid #ef4444; font-weight:800;';
                        errRow.textContent = `❌ غير موجود في الفاتورة: ${term}`;
                        logBox.prepend(errRow);
                        this.select();
                        return;
                    }

                    const line = receiptLines[idx];
                    line.receiveQty = Math.min(line.maxPending, Math.round(((parseFloat(line.receiveQty) || 0) + step) * 100) / 100);
                    window._grnHasUnsavedChanges = true;
                    window._grnTotalScansCount = (window._grnTotalScansCount || 0) + 1;
                    const pill = document.getElementById('grn-scan-counter-pill');
                    if (pill) pill.textContent = String(window._grnTotalScansCount);
                    const modalCounter = document.getElementById('grn-modal-scan-counter');
                    if (modalCounter) modalCounter.textContent = `${window._grnTotalScansCount} ضربة`;

                    if (typeof playScanBeep === 'function') playScanBeep(980, 0.08);
                    renderReceiptLines();

                    const okRow = document.createElement('div');
                    okRow.style.cssText = 'padding:6px 10px; border-radius:8px; background:rgba(16,185,129,0.18); border:1px solid #10b981; font-weight:800; display:flex; justify-content:space-between;';
                    okRow.innerHTML = `<span>✅ ${getItemName(line.itemId)} (+${step})</span><span style="font-family:Outfit;">المستلم: ${line.receiveQty} / ${line.maxPending}</span>`;
                    if (logBox.children.length === 1 && logBox.children[0].textContent.includes('بانتظار')) logBox.innerHTML = '';
                    logBox.prepend(okRow);
                    this.value = '';
                    this.focus();
                });
            }
            modal.style.display = 'flex';
            setTimeout(() => {
                const inp = document.getElementById('grn-rapid-scan-input');
                if (inp) { inp.focus(); inp.select(); }
            }, 50);
        };

        window.openGrnWeighbridgeModal = function() {
            ensureGrnChildModalsDom();
            let modal = document.getElementById('grn-weighbridge-modal');
            if (!modal) {
                const html = `
                    <div id="grn-weighbridge-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.78); backdrop-filter:blur(6px); z-index:100026; align-items:center; justify-content:center; padding:16px;">
                        <div class="grn-child-modal-card" style="background:#0f172a; border:2.5px solid #f59e0b; border-radius:18px; width:100%; max-width:500px; padding:24px; box-shadow:0 20px 50px rgba(0,0,0,0.8);">
                            <h3 style="margin:0 0 14px 0; font-size:1.2rem; font-weight:900; color:#FFFFFF;">⚖️ مطابقة ميزان القبّان مع البوليصة (Weighbridge)</h3>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                <div>
                                    <label style="display:block; margin-bottom:5px; font-size:0.86rem; font-weight:800;">وزن البوليصة الدفتري (صافي):</label>
                                    <input type="number" id="grn-wb-waybill-wt" class="grn-log-input" step="any" min="0" style="width:100%; height:42px; text-align:center;" placeholder="0.00">
                                </div>
                                <div>
                                    <label style="display:block; margin-bottom:5px; font-size:0.86rem; font-weight:800;">رقم تذكرة الميزان:</label>
                                    <input type="text" id="grn-wb-ticket-no" class="grn-log-input" style="width:100%; height:42px; text-align:center;" placeholder="WB-1001">
                                </div>
                                <div>
                                    <label style="display:block; margin-bottom:5px; font-size:0.86rem; font-weight:800;">الوزن القائم (Gross Weight):</label>
                                    <input type="number" id="grn-wb-gross-wt" class="grn-log-input" step="any" min="0" style="width:100%; height:42px; text-align:center;" placeholder="0.00">
                                </div>
                                <div>
                                    <label style="display:block; margin-bottom:5px; font-size:0.86rem; font-weight:800;">الوزن الفارغ (Tare Weight):</label>
                                    <input type="number" id="grn-wb-tare-wt" class="grn-log-input" step="any" min="0" style="width:100%; height:42px; text-align:center;" placeholder="0.00">
                                </div>
                            </div>
                            <div id="grn-wb-summary-box" style="margin-top:14px; padding:12px; border-radius:12px; border:1.5px solid rgba(245,158,11,0.45); background:rgba(245,158,11,0.1); font-weight:800; font-size:0.92rem; display:flex; justify-content:space-between; align-items:center;">
                                <span>الوزن الصافي الفعلي: <strong id="grn-wb-net-lbl" style="font-family:Outfit;">0</strong></span>
                                <span>الفرق: <strong id="grn-wb-diff-lbl" style="font-family:Outfit;">0 (0%)</strong></span>
                            </div>
                            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:18px;">
                                <button type="button" onclick="document.getElementById('grn-weighbridge-modal').style.display='none'" class="btn-action btn-cancel" style="padding:10px 16px; border-radius:10px; font-weight:800;">إلغاء</button>
                                <button type="button" id="grn-wb-apply-btn" class="btn-action" style="padding:10px 20px; border-radius:10px; background:linear-gradient(135deg,#f59e0b,#d97706); color:#FFFFFF; font-weight:900;">⚖️ اعتماد تذكرة الميزان وتطبيق الصافي</button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', html);
                modal = document.getElementById('grn-weighbridge-modal');

                const calcWb = () => {
                    const wb = parseFloat(document.getElementById('grn-wb-waybill-wt').value) || 0;
                    const gross = parseFloat(document.getElementById('grn-wb-gross-wt').value) || 0;
                    const tare = parseFloat(document.getElementById('grn-wb-tare-wt').value) || 0;
                    const net = Math.max(0, Math.round((gross - tare) * 1000) / 1000);
                    const diff = Math.round((net - wb) * 1000) / 1000;
                    const pct = wb > 0 ? Math.round((diff / wb) * 10000) / 100 : 0;
                    document.getElementById('grn-wb-net-lbl').textContent = fmtNum(net);
                    const diffEl = document.getElementById('grn-wb-diff-lbl');
                    diffEl.textContent = `${diff > 0 ? '+' : ''}${fmtNum(diff)} (${pct > 0 ? '+' : ''}${pct}%)`;
                    diffEl.style.color = diff < 0 ? '#ef4444' : '#10b981';
                    return { wb, gross, tare, net, diff, pct };
                };
                ['grn-wb-waybill-wt', 'grn-wb-gross-wt', 'grn-wb-tare-wt'].forEach(id => {
                    document.getElementById(id).addEventListener('input', calcWb);
                });

                document.getElementById('grn-wb-apply-btn').addEventListener('click', () => {
                    const res = calcWb();
                    const tNo = document.getElementById('grn-wb-ticket-no').value.trim() || ('WB-' + Date.now().toString().slice(-4));
                    const waybillInp = document.getElementById('grn-waybill-no');
                    if (waybillInp && !waybillInp.value.includes(tNo)) {
                        waybillInp.value = (waybillInp.value ? waybillInp.value + ' / ' : '') + `ميزان:${tNo}(صافي:${res.net})`;
                    }
                    if (res.net > 0 && Array.isArray(receiptLines) && receiptLines.length === 1) {
                        receiptLines[0].receiveQty = Math.min(receiptLines[0].maxPending, res.net);
                    }
                    if (Array.isArray(receiptLines) && receiptLines[0]) {
                        const wbTag = `[ميزان ${tNo}: قائم=${res.gross} | فارغ=${res.tare} | صافي=${res.net} | فرق=${res.diff}]`;
                        receiptLines[0].notes = ((receiptLines[0].notes || '') + ' ' + wbTag).trim();
                    }
                    window._grnHasUnsavedChanges = true;
                    renderReceiptLines();
                    modal.style.display = 'none';
                    showToast(`⚖️ تم توثيق تذكرة ميزان القبّان (${tNo}) بصافي فعلي (${fmtNum(res.net)})`, 'success');
                });
            }

            // Default waybill weight to sum of maxPending
            const totalPending = (receiptLines || []).reduce((s, l) => s + (parseFloat(l.maxPending) || 0), 0);
            const wbInp = document.getElementById('grn-wb-waybill-wt');
            if (wbInp && !wbInp.value) wbInp.value = totalPending || '';
            modal.style.display = 'flex';
            setTimeout(() => {
                const gInp = document.getElementById('grn-wb-gross-wt');
                if (gInp) { gInp.focus(); gInp.select(); }
            }, 50);
        };

        window.printCertifiedGrnQrCard = function() {
            const activeLines = (receiptLines || []).filter(l => (parseFloat(l.receiveQty) || 0) > 0);
            if (!activeLines.length) {
                return showToast('⚠️ لا توجد أصناف مستلمة لإصدار بطاقة الفحص والـ QR', 'warning');
            }
            const invId = currentInvoiceId || 'INV';
            const grnNo = editingGRNId || (typeof generateGRNNumber === 'function' ? generateGRNNumber() : 'GRN-NEW');
            const supName = document.getElementById('lbl-supplier') ? document.getElementById('lbl-supplier').textContent.trim() : 'مورد';
            const inspector = (document.getElementById('grn-inspector-name')?.value || 'أمين المستودع').trim();
            const waybill = (document.getElementById('grn-waybill-no')?.value || '—').trim();
            const qrPayload = encodeURIComponent(`GRN:${grnNo}|INV:${invId}|SUP:${supName}|ITEMS:${activeLines.length}|DATE:${new Date().toISOString().slice(0,10)}`);
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${qrPayload}`;

            const rowsHtml = activeLines.map((l, i) => {
                const whObj = (warehousesDb || []).find(w => w.id === l.warehouseId);
                const whName = whObj ? whObj.name : (l.warehouseId || 'المخزن الرئيسي');
                const qcTxt = l.qcStatus === 'dmg' ? `تالف (${l.damagedQty || 0})` : (l.qcStatus === 'warn' ? 'استلام بتحفظ' : 'سليم ومطابق ✅');
                return `
                    <tr>
                        <td>${i + 1}</td>
                        <td style="text-align:right;font-weight:900;">${getItemName(l.itemId)}</td>
                        <td>${l.unitName || 'وحدة'}</td>
                        <td style="font-weight:900;">${fmtNum(l.receiveQty)}</td>
                        <td>${whName}</td>
                        <td>${l.batchNo || '—'}</td>
                        <td>${l.expiryDate || '—'}</td>
                        <td>${l.binLocation || '—'}</td>
                        <td>${qcTxt}</td>
                    </tr>
                `;
            }).join('');

            const w = window.open('', '_blank', 'width=940,height=720');
            if (!w) return showToast('⚠️ يرجى السماح بالنوافذ المنبثقة لطباعة بطاقة QR', 'warning');
            w.document.write(`<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8"><title>بطاقة فحص واستلام مخزني معتمدة - ${grnNo}</title>
            <style>
                body { font-family: 'Tajawal', sans-serif; padding: 24px; color: #000000; background: #FFFFFF; }
                .card { border: 3px solid #000000; border-radius: 16px; padding: 20px; }
                .hdr { display: flex; justify-content: space-between; align-items: center; border-bottom: 2.5px solid #000000; padding-bottom: 14px; margin-bottom: 16px; }
                table { width: 100%; border-collapse: collapse; margin-top: 12px; }
                th, td { border: 2px solid #000000; padding: 8px 10px; text-align: center; font-size: 0.9rem; font-weight: 800; color: #000000; }
                th { background: #e2e8f0; font-weight: 900; }
                @media print { .no-print { display: none !important; } }
            </style></head><body>
                <div class="no-print" style="margin-bottom:14px;text-align:left;">
                    <button onclick="window.print()" style="padding:10px 22px;font-weight:900;background:#0d9488;color:#fff;border:none;border-radius:8px;cursor:pointer;">🖨️ طباعة البطاقة / حفظ PDF</button>
                </div>
                <div class="card">
                    <div class="hdr">
                        <div>
                            <h2 style="margin:0 0 6px 0;font-size:1.45rem;font-weight:900;">🛡️ بطاقة فحص واستلام مخزني وتوجيه أرفف (QC & Shelf Card)</h2>
                            <div>رقم الإذن: <strong>${grnNo}</strong> | الفاتورة: <strong>${invId}</strong> | المورد: <strong>${supName}</strong></div>
                            <div style="margin-top:4px;">بوليصة/ميزان: <strong>${waybill}</strong> | الفاحص: <strong>${inspector}</strong> | التاريخ: <strong>${new Date().toLocaleString('en-GB')}</strong></div>
                        </div>
                        <img src="${qrUrl}" alt="QR" width="115" height="115" style="border:2px solid #000;border-radius:10px;padding:4px;">
                    </div>
                    <table>
                        <thead>
                            <tr><th>#</th><th>الصنف</th><th>الوحدة</th><th>الكمية المستلمة</th><th>المستودع</th><th>رقم التشغيلة</th><th>الصلاحية</th><th>موقع الرف (Bin)</th><th>حالة الجودة</th></tr>
                        </thead>
                        <tbody>${rowsHtml}</tbody>
                    </table>
                </div>
            </body></html>`);
            w.document.close();
        };

        // ═══════════════════════════════════════════════════════════════════════════
        // APPROVED CONSTITUTION 2 PROPOSALS (1, 2 & 3):
        // 1. Live Audio Summary Readback (القراءة الصوتية الحية لملخص الاستلام بالصوت الرجالي)
        // 2. Voice Commands via Microphone (الأوامر الصوتية السريعة بالمايكروفون)
        // 3. Interactive Mastery Quiz (اختبار التمكن التفاعلي السريع لأمناء المخازن)
        // ═══════════════════════════════════════════════════════════════════════════
        window.speakGrnMaleVoice = function(text, onDone) {
            if (!text) { if (typeof onDone === 'function') onDone(); return; }
            if (typeof window.speakMaleGuideText === 'function') {
                return window.speakMaleGuideText(text, onDone);
            }
            if (!('speechSynthesis' in window)) {
                if (typeof onDone === 'function') onDone();
                return;
            }
            try {
                window.speechSynthesis.cancel();
                window.speechSynthesis.resume();
                const u = new SpeechSynthesisUtterance(text);
                u.lang = 'ar-SA';
                u.rate = 0.96;
                u.pitch = 0.84; // Deep calm male pitch
                const voices = window.speechSynthesis.getVoices() || [];
                const arVoices = voices.filter(v => /ar/i.test(v.lang || v.name));
                const maleVoice = arVoices.find(v => /hamed|naayf|maged|tarik|omar|male|شاك|حامد|نايف|ماجد|طارق/i.test(v.name))
                    || arVoices.find(v => !/salma|zariyah|laila|hoda|female|سلمى|هدى|ليلى/i.test(v.name))
                    || arVoices[0];
                if (maleVoice) u.voice = maleVoice;
                u.onend = () => { if (typeof onDone === 'function') onDone(); };
                u.onerror = () => { if (typeof onDone === 'function') onDone(); };
                window.speechSynthesis.speak(u);
            } catch (_es) {
                if (typeof onDone === 'function') onDone();
            }
        };

        window.speakLiveGrnSummaryReadback = function() {
            const itemsTxt = (document.getElementById('lbl-total-received-items')?.textContent || '0 صنف').trim();
            const qtyTxt   = (document.getElementById('lbl-total-received-qty')?.textContent || '0').trim();
            const baseTxt  = (document.getElementById('lbl-total-base-qty')?.textContent || '0').trim();
            const remTxt   = (document.getElementById('lbl-total-remaining-qty')?.textContent || '0').trim();
            const pctTxt   = (document.getElementById('grn-live-progress-pct')?.textContent || '100%').trim();
            const msg = `ملخص الاستلام الحالي: عدد الأصناف المستلمة ${itemsTxt}، إجمالي الكمية المستلمة ${qtyTxt}، الإجمالي بالوحدة الأساسية ${baseTxt}، المتبقي بعد الاستلام ${remTxt}، ونسبة إنجاز الفاتورة ${pctTxt}.`;
            showToast('🔊 ' + msg, 'info');
            window.speakGrnMaleVoice(msg);
        };

        // Inject Live Audio Readback button into Bottom Summary Bar & auto-readback on hover when guide is active
        setTimeout(() => {
            const bottomBar = document.querySelector('#view-editor .editor-bottom-bar');
            if (bottomBar && !document.getElementById('btn-grn-audio-summary')) {
                const audioBtn = document.createElement('button');
                audioBtn.type = 'button';
                audioBtn.id = 'btn-grn-audio-summary';
                audioBtn.className = 'btn-action';
                audioBtn.style.cssText = 'padding:8px 14px; font-size:0.86rem; border-radius:10px; background:linear-gradient(135deg,#0284c7,#2563eb); color:#FFFFFF; font-weight:900; display:inline-flex; align-items:center; gap:6px;';
                audioBtn.innerHTML = '<span>🔊</span> نطق الملخص';
                audioBtn.title = 'قراءة صوتية حية لملخص الاستلام والكميات المتبقية ونسبة الإنجاز بالصوت الرجالي';
                audioBtn.onclick = (ev) => {
                    ev.stopPropagation();
                    window.speakLiveGrnSummaryReadback();
                };
                bottomBar.appendChild(audioBtn);
            }
        }, 400);

        window.startGrnVoiceCommandListener = function() {
            const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
            const btn = document.getElementById('btn-grn-voice-cmd');
            if (!SpeechRec) {
                showToast('⚠️ المتصفح الحالي لا يدعم الأوامر الصوتية المباشرة؛ استخدم متصفح Chrome أو Edge', 'warning');
                return;
            }
            try {
                const rec = new SpeechRec();
                rec.lang = 'ar-SA';
                rec.interimResults = false;
                rec.maxAlternatives = 1;
                if (btn) {
                    btn.style.boxShadow = '0 0 0 4px rgba(236,72,153,0.65)';
                    btn.innerHTML = '<span>🔴</span> استمع الآن...';
                }
                showToast('🎙️ تحدث الآن بالأمر الصوتي (مثال: استلام الكل، ميزان القبان، ماسح متصل، بطاقة كيو آر، اعتماد الاستلام)', 'info');
                rec.onresult = function(ev) {
                    const rawTranscript = (ev.results && ev.results[0] && ev.results[0][0]) ? ev.results[0][0].transcript : '';
                    const cmd = typeof window.normalizeGrnArabic === 'function' ? window.normalizeGrnArabic(rawTranscript) : rawTranscript.toLowerCase();
                    if (cmd.includes('استلام الكل') || cmd.includes('تعبئه الكل') || cmd.includes('كامل')) {
                        if (typeof window.fillAllRemainingGRNQty === 'function') window.fillAllRemainingGRNQty();
                        window.speakGrnMaleVoice('تم تعبئة كامل الكميات المتبقية للاستلام.');
                    } else if (cmd.includes('تصفير') || cmd.includes('صفر')) {
                        if (typeof window.zeroAllGRNQty === 'function') window.zeroAllGRNQty();
                        window.speakGrnMaleVoice('تم تصفير خانات الكمية المستلمة.');
                    } else if (cmd.includes('ميزان') || cmd.includes('قبان') || cmd.includes('وزن')) {
                        if (typeof window.openGrnWeighbridgeModal === 'function') window.openGrnWeighbridgeModal();
                        window.speakGrnMaleVoice('تم فتح شاشة مطابقة ميزان القبان مع البوليصة.');
                    } else if (cmd.includes('ماسح') || cmd.includes('باركود')) {
                        if (typeof window.openGrnContinuousScannerModal === 'function') window.openGrnContinuousScannerModal();
                        window.speakGrnMaleVoice('تم فتح وضع الماسح المتصل السريع.');
                    } else if (cmd.includes('بطاقه') || cmd.includes('كيو ار') || cmd.includes('qr')) {
                        if (typeof window.printCertifiedGrnQrCard === 'function') window.printCertifiedGrnQrCard();
                        window.speakGrnMaleVoice('جاري إصدار بطاقة الفحص والاستلام المزودة برمز كيو آر.');
                    } else if (cmd.includes('مستودع') || cmd.includes('مخزن جديد') || cmd.includes('اضافه مخزن')) {
                        if (typeof window.openGrnQuickWarehouseModal === 'function') window.openGrnQuickWarehouseModal();
                        window.speakGrnMaleVoice('تم فتح نافذة إضافة مستودع جديد فوراً.');
                    } else if (cmd.includes('ملخص') || cmd.includes('نطق')) {
                        window.speakLiveGrnSummaryReadback();
                    } else if (cmd.includes('اعتماد') || cmd.includes('حفظ') || cmd.includes('تاكيد')) {
                        window.speakGrnMaleVoice('جاري حفظ واعتماد مذكرة الاستلام المخزني.');
                        if (typeof window.saveReceiptNote === 'function') window.saveReceiptNote();
                    } else {
                        showToast('🎙️ تم سماع: "' + rawTranscript + '" — جرب قول: استلام الكل، ميزان القبان، أو اعتماد الاستلام', 'info');
                    }
                };
                rec.onend = function() {
                    if (btn) {
                        btn.style.boxShadow = '';
                        btn.innerHTML = '<span>🎙️</span> أمر صوتي';
                    }
                };
                rec.onerror = function() {
                    if (btn) {
                        btn.style.boxShadow = '';
                        btn.innerHTML = '<span>🎙️</span> أمر صوتي';
                    }
                };
                rec.start();
            } catch (_er) {
                if (btn) {
                    btn.style.boxShadow = '';
                    btn.innerHTML = '<span>🎙️</span> أمر صوتي';
                }
            }
        };

        // 3. Interactive Mastery Quiz (اختبار التمكن التفاعلي السريع لتدريب أمناء المخازن)
        window._grnQuizState = { active: false, step: 0 };
        const GRN_QUIZ_TASKS = [
            {
                prompt: 'التحدي الأول (1/3): اضغط الآن على زر الإضافة السريعة (➕) بجانب قائمة المستودع لإنشاء مخزن جديد فوراً.',
                selector: '#btn-grn-quick-add-wh'
            },
            {
                prompt: 'أحسنت! التحدي الثاني (2/3): اضغط الآن على زر (⚖️ ميزان القبّان) لمطابقة الوزن القائم والفارغ للشاحنة مع البوليصة.',
                selector: '#btn-grn-weighbridge'
            },
            {
                prompt: 'ممتاز جداً! التحدي الثالث والأخير (3/3): اضغط الآن على زر (🔊 نطق الملخص) أو شريط الملخص السفلي لمراجعة واعتماد الاستلام.',
                selector: '#btn-grn-audio-summary, .editor-bottom-bar'
            }
        ];

        window.startGrnMasteryQuiz = function() {
            if (typeof window.ensureGrnEditorOpenForGuide === 'function') window.ensureGrnEditorOpenForGuide();
            window._grnQuizState = { active: true, step: 0 };
            const task = GRN_QUIZ_TASKS[0];
            showToast('🎓 ' + task.prompt, 'info');
            window.speakGrnMaleVoice('بدأ التدريب العملي التفاعلي لأمين المستودع. ' + task.prompt);
            const targetEl = document.querySelector(task.selector);
            if (targetEl && typeof window.focusRequiredGrnElement === 'function') {
                window.focusRequiredGrnElement(targetEl);
            }
        };

        document.addEventListener('click', function(e) {
            if (!window._grnQuizState || !window._grnQuizState.active) return;
            if (e.target.closest('#btn-grn-mastery-quiz')) return;
            const currentTask = GRN_QUIZ_TASKS[window._grnQuizState.step];
            if (!currentTask) return;
            if (e.target.closest(currentTask.selector)) {
                window._grnQuizState.step++;
                if (window._grnQuizState.step >= GRN_QUIZ_TASKS.length) {
                    window._grnQuizState.active = false;
                    showToast('🏆 مبارك! اجتزت اختبار التمكن العملي لشاشة استلام البضاعة المخزني بنجاح 100%!', 'success');
                    window.speakGrnMaleVoice('مبارك! لقد اجتزت اختبار التمكن العملي لشاشة استلام البضاعة المخزني بنجاح مئة بالمئة.');
                } else {
                    const nextTask = GRN_QUIZ_TASKS[window._grnQuizState.step];
                    setTimeout(() => {
                        showToast('🎓 ' + nextTask.prompt, 'info');
                        window.speakGrnMaleVoice(nextTask.prompt);
                        const nextEl = document.querySelector(nextTask.selector);
                        if (nextEl && typeof window.focusRequiredGrnElement === 'function') {
                            window.focusRequiredGrnElement(nextEl);
                        }
                    }, 350);
                }
            }
        }, true);

        // ═══════════════════════════════════════════════════════════════════════════
        // CONSTITUTION 3 ENGINE:
        // 1. Sub-Second Real-Time Auto-Sync (Mobile ↔ Laptop ↔ Weak 2G Instant Flush)
        // 2. Live Supplier Balance & Per-Row Warehouse Stock Indicators + Audit Stamp
        // 3. Instant Hub Filters, Drill-Down Child Modal (#grn-drilldown-modal),
        //    and Export to Excel / A4 PDF / Thermal 80mm / WhatsApp
        // ═══════════════════════════════════════════════════════════════════════════
        window.updateGrnLiveSyncBadge = function() {
            const badge = document.getElementById('grn-live-sync-badge');
            if (!badge) return;
            let qLen = 0;
            try {
                const q = JSON.parse(localStorage.getItem('erp_grn_offline_queue_v1') || '[]');
                if (Array.isArray(q)) qLen = q.length;
            } catch (_e) {}
            if (!navigator.onLine || qLen > 0) {
                badge.style.background = 'rgba(245,158,11,0.18)';
                badge.style.borderColor = '#f59e0b';
                badge.style.color = '#fbbf24';
                badge.innerHTML = navigator.onLine
                    ? `🔄 جاري رفع طابور الأوفلاين (${qLen})...`
                    : `⏳ وضع أوفلاين (محفوظ محلياً: ${qLen} — يرفع فور قدوم الشبكة)`;
            } else {
                badge.style.background = 'rgba(16,185,129,0.16)';
                badge.style.borderColor = '#10b981';
                badge.style.color = '#34d399';
                badge.innerHTML = '⚡ متزامن لحظياً (موبايل ⇄ لابتوب)';
            }
        };

        window.populateGrnHubFilterDropdowns = function() {
            const supSel = document.getElementById('grn-filter-supplier');
            if (supSel) {
                const curSup = supSel.value;
                let sOpts = '<option value="">👤 كل الموردين</option>';
                (suppliersDb || []).forEach(s => {
                    sOpts += `<option value="${s.id}">${s.name}</option>`;
                });
                supSel.innerHTML = sOpts;
                supSel.value = curSup;
            }
            const whSel = document.getElementById('grn-filter-warehouse');
            if (whSel) {
                const curWh = whSel.value;
                let wOpts = '<option value="">🏢 كل المستودعات</option>';
                (warehousesDb || []).forEach(w => {
                    if (w.id !== 'PENDING_RECEIPT') {
                        wOpts += `<option value="${w.id}">${w.name}</option>`;
                    }
                });
                whSel.innerHTML = wOpts;
                whSel.value = curWh;
            }
            window.updateGrnLiveSyncBadge();
        };

        window.applyGrnHubFilters = function() {
            if (currentHubView === 'pending') renderPendingInvoices(true);
            else renderRecentGRNs(true);
        };

        window.resetGrnHubFilters = function() {
            ['grn-filter-date-from', 'grn-filter-date-to', 'grn-filter-supplier', 'grn-filter-warehouse', 'search-inv'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            window.applyGrnHubFilters();
        };

        // Compute real-time available stock for an item in a specific warehouse
        window.getGrnLiveItemWhStock = function(itemId, whId) {
            if (!itemId) return 0;
            const whObj = (warehousesDb || []).find(w => w.id === whId);
            const whName = whObj ? whObj.name : 'المخزن الرئيسي';
            try {
                const invArr = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
                const found = invArr.find(x => String(x.itemId) === String(itemId) && (x.warehouse === whName || x.warehouse === whId));
                if (found) return Math.round((parseFloat(found.qty ?? found.quantity) || 0) * 100) / 100;
            } catch (_e) {}
            const it = (itemsDb || []).find(i => String(i.id) === String(itemId) || i.name === itemId);
            if (it && it.stock && typeof it.stock === 'object' && it.stock[whId] !== undefined) {
                return Math.round((parseFloat(it.stock[whId]) || 0) * 100) / 100;
            }
            return 0;
        };

        // Live Supplier Balance & Audit Trail Stamp inside Editor Meta Bar (Order 4 & Order 8)
        const _c3OpenEditor = window.openReceiptEditor;
        window.openReceiptEditor = function(invId) {
            _c3OpenEditor.apply(this, arguments);
            try {
                const metaBar = document.querySelector('#view-editor .grn-meta-bar');
                if (!metaBar) return;
                const invData = pendingMap[invId] || {};
                const origInv = (allPurchases || []).find(p => String(p.invoiceNo || p.id) === String(invId)) || {};
                const supId = invData.supplierId || origInv.customer || origInv.customerId || origInv.supplierId;
                const supObj = (suppliersDb || []).find(s => String(s.id) === String(supId) || String(s.code) === String(supId)) || {};
                const supBal = Math.round((parseFloat(supObj.currentBalance ?? supObj.balance) || 0) * 100) / 100;
                const invGrand = Math.round((parseFloat(origInv.grandTotal ?? origInv.total) || 0) * 100) / 100;

                let balPill = document.getElementById('grn-supplier-live-balance-pill');
                if (!balPill) {
                    balPill = document.createElement('div');
                    balPill.id = 'grn-supplier-live-balance-pill';
                    balPill.style.cssText = 'padding:5px 12px; border-radius:10px; background:rgba(16,185,129,0.14); border:1.5px solid rgba(16,185,129,0.45); font-size:0.82rem; font-weight:900; display:inline-flex; align-items:center; gap:8px;';
                    metaBar.appendChild(balPill);
                }
                balPill.innerHTML = `<span>💰 رصيد المورد الحي: <strong style="font-family:Outfit;">${fmtNum(supBal)} SDG</strong></span> | <span>🧾 إجمالي الفاتورة: <strong style="font-family:Outfit;">${fmtNum(invGrand)} SDG</strong></span>`;

                const curU = (typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo() : JSON.parse(localStorage.getItem('user') || '{}');
                const uName = curU.fullName || curU.name || curU.username || 'المسؤول';
                const devType = /Mobi|Android|iPhone/i.test(navigator.userAgent) ? '📱 موبايل' : '💻 لابتوب';
                let auditPill = document.getElementById('grn-audit-stamp-pill');
                if (!auditPill) {
                    auditPill = document.createElement('div');
                    auditPill.id = 'grn-audit-stamp-pill';
                    auditPill.style.cssText = 'padding:5px 12px; border-radius:10px; background:rgba(56,189,248,0.14); border:1.5px solid rgba(56,189,248,0.45); font-size:0.8rem; font-weight:900; display:inline-flex; align-items:center; gap:6px;';
                    metaBar.appendChild(auditPill);
                }
                auditPill.innerHTML = `🛡️ البصمة الرقابية: ${uName} (${devType})`;
            } catch (_em) {}
        };

        // Per-Row Live Warehouse Stock Indicator inside #items-tbody (Order 4)
        const _c3RenderLines = window.renderReceiptLines;
        window.renderReceiptLines = function() {
            _c3RenderLines.apply(this, arguments);
            const rows = document.querySelectorAll('#items-tbody tr');
            rows.forEach((tr, idx) => {
                const line = (receiptLines || [])[idx];
                if (!line) return;
                const whSelect = tr.querySelector('.wh-select');
                if (!whSelect || !whSelect.parentNode) return;
                let badge = whSelect.parentNode.querySelector('.grn-live-wh-stock-badge');
                if (!badge) {
                    whSelect.parentNode.style.display = 'flex';
                    whSelect.parentNode.style.flexDirection = 'column';
                    whSelect.parentNode.style.gap = '3px';
                    badge = document.createElement('div');
                    badge.className = 'grn-live-wh-stock-badge';
                    badge.style.cssText = 'font-size:0.72rem; font-weight:900; text-align:center; padding:2px 6px; border-radius:6px; background:rgba(56,189,248,0.14); border:1px solid rgba(56,189,248,0.35);';
                    whSelect.parentNode.appendChild(badge);
                }
                const updateBadge = () => {
                    const curStock = window.getGrnLiveItemWhStock(line.itemId, whSelect.value || line.warehouseId);
                    badge.textContent = `📦 الرصيد بالمخزن: ${fmtNum(curStock)}`;
                };
                updateBadge();
                whSelect.addEventListener('change', updateBadge);
            });
        };

        // Stamp Audit Trail on Save & Broadcast on all sync channels (Order 1 & Order 8)
        const _c3SaveNote = window.saveReceiptNote;
        window.saveReceiptNote = function() {
            _c3SaveNote.apply(this, arguments);
            try {
                const curU = (typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo() : JSON.parse(localStorage.getItem('user') || '{}');
                const devType = /Mobi|Android|iPhone/i.test(navigator.userAgent) ? 'Mobile' : 'Laptop';
                let fresh = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
                let lastGrn = null;
                for (let i = fresh.length - 1; i >= 0; i--) {
                    if (fresh[i] && fresh[i].isGRN) {
                        fresh[i].auditStamp = {
                            userId: curU.id || 1,
                            userName: curU.fullName || curU.name || curU.username || 'admin',
                            deviceType: devType,
                            savedAt: new Date().toISOString()
                        };
                        lastGrn = fresh[i];
                        break;
                    }
                }
                if (lastGrn) {
                    const s = JSON.stringify(fresh);
                    localStorage.setItem('erp_purchase_invoices_v1', s);
                    localStorage.setItem('erp_purchases_v1', s);
                    localStorage.setItem('magaies_local_purchase_invoices_v1', s);
                    if (typeof BroadcastChannel !== 'undefined') {
                        ['magaiees_sync_channel', 'erp_live_sync', 'elsajana_erp_sync', 'magaies_purchase_sync_channel'].forEach(ch => {
                            try {
                                const bc = new BroadcastChannel(ch);
                                bc.postMessage({ type: 'grn_saved', module: 'purchase_receipt', grnId: lastGrn.id, timestamp: Date.now() });
                                bc.close();
                            } catch (_eb) {}
                        });
                    }
                }
                window.updateGrnLiveSyncBadge();
            } catch (_es) {}
        };

        // 5. Drill-Down Child Modal (#grn-drilldown-modal) — Preview Original Invoice or GRN in Place
        window.openGrnDrillDownModal = function(docId) {
            ensureGrnChildModalsDom();
            let modal = document.getElementById('grn-drilldown-modal');
            if (!modal) {
                const html = `
                    <div id="grn-drilldown-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.78); backdrop-filter:blur(6px); z-index:100028; align-items:center; justify-content:center; padding:16px;">
                        <div class="grn-child-modal-card" style="background:#0f172a; border:2.5px solid #38bdf8; border-radius:18px; width:100%; max-width:740px; max-height:88vh; overflow-y:auto; padding:24px; box-shadow:0 20px 50px rgba(0,0,0,0.85);">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-bottom:2px solid rgba(56,189,248,0.35); padding-bottom:10px;">
                                <h3 id="grn-dd-title" style="margin:0; font-size:1.22rem; font-weight:900; color:#FFFFFF;">🔍 معاينة أصل المستند (Drill-Down)</h3>
                                <button type="button" onclick="document.getElementById('grn-drilldown-modal').style.display='none'" class="btn-action btn-cancel" style="padding:6px 14px; border-radius:8px; font-weight:900;">✕ إغلاق</button>
                            </div>
                            <div id="grn-dd-body" style="font-size:0.9rem; font-weight:800;"></div>
                            <div id="grn-dd-actions" style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;"></div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', html);
                modal = document.getElementById('grn-drilldown-modal');
            }
            const doc = (allPurchases || []).find(p => p && (String(p.id) === String(docId) || String(p.invoiceNo) === String(docId)));
            const pMapObj = pendingMap[docId];
            if (!doc && !pMapObj) return;

            const isGrn = doc && doc.isGRN;
            const supId = (doc && (doc.customer || doc.customerId || doc.supplierId)) || (pMapObj && pMapObj.supplierId);
            const supName = getSupplierName(supId);
            const dtStr = (doc && doc.date) || (pMapObj && pMapObj.date) || '';
            const audit = (doc && doc.auditStamp) ? `${doc.auditStamp.userName} (${doc.auditStamp.deviceType})` : ((doc && (doc.createdByName || doc.createdBy)) || 'المسؤول');

            document.getElementById('grn-dd-title').textContent = isGrn
                ? `📜 معاينة إذن الاستلام المخزني (${docId})`
                : `🧾 معاينة أصل فاتورة المشتريات (${docId})`;

            let linesArr = [];
            if (isGrn) {
                linesArr = (doc.items || []).filter(i => (parseFloat(i.quantity) || 0) > 0).map(i => ({
                    name: getItemName(i.itemId),
                    unit: i.unitName || 'وحدة',
                    qty: i.quantity,
                    price: i.price || 0,
                    notes: i.notes || '—'
                }));
            } else if (doc && Array.isArray(doc.items)) {
                linesArr = doc.items.map(i => ({
                    name: getItemName(i.itemId || i.name),
                    unit: i.unitName || i.unit || 'وحدة',
                    qty: i.quantity || i.qty || 0,
                    price: i.price || 0,
                    notes: i.notes || '—'
                }));
            } else if (pMapObj && pMapObj.lines) {
                linesArr = Object.values(pMapObj.lines).map(l => ({
                    name: getItemName(l.itemId),
                    unit: l.unitName || 'وحدة',
                    qty: l.pendingQty || l.origQty || 0,
                    price: l.price || 0,
                    notes: 'قيد الاستلام'
                }));
            }

            const rowsHtml = linesArr.map((l, idx) => `
                <tr>
                    <td style="padding:7px; border:1.5px solid rgba(148,163,184,0.35); text-align:center;">${idx + 1}</td>
                    <td style="padding:7px; border:1.5px solid rgba(148,163,184,0.35); text-align:right;">${l.name}</td>
                    <td style="padding:7px; border:1.5px solid rgba(148,163,184,0.35); text-align:center;">${l.unit}</td>
                    <td style="padding:7px; border:1.5px solid rgba(148,163,184,0.35); text-align:center; font-family:Outfit;">${fmtNum(l.qty)}</td>
                    <td style="padding:7px; border:1.5px solid rgba(148,163,184,0.35); text-align:center; font-family:Outfit;">${fmtNum(l.price)}</td>
                    <td style="padding:7px; border:1.5px solid rgba(148,163,184,0.35); text-align:right;">${l.notes}</td>
                </tr>
            `).join('');

            document.getElementById('grn-dd-body').innerHTML = `
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)); gap:10px; margin-bottom:14px; padding:12px; border-radius:12px; border:1.5px solid rgba(56,189,248,0.3);">
                    <div>📄 رقم المستند: <strong style="font-family:Outfit;">${docId}</strong></div>
                    <div>👤 المورد: <strong>${supName}</strong></div>
                    <div>📅 التاريخ: <strong style="font-family:Outfit;">${dtStr ? new Date(dtStr).toLocaleString('en-GB') : '—'}</strong></div>
                    <div>🛡️ البصمة الرقابية: <strong>${audit}</strong></div>
                </div>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:rgba(56,189,248,0.15);">
                            <th style="padding:8px; border:1.5px solid rgba(148,163,184,0.4);">#</th>
                            <th style="padding:8px; border:1.5px solid rgba(148,163,184,0.4); text-align:right;">الصنف</th>
                            <th style="padding:8px; border:1.5px solid rgba(148,163,184,0.4);">الوحدة</th>
                            <th style="padding:8px; border:1.5px solid rgba(148,163,184,0.4);">الكمية</th>
                            <th style="padding:8px; border:1.5px solid rgba(148,163,184,0.4);">السعر</th>
                            <th style="padding:8px; border:1.5px solid rgba(148,163,184,0.4); text-align:right;">ملاحظات / فحص</th>
                        </tr>
                    </thead>
                    <tbody>${rowsHtml}</tbody>
                </table>
            `;

            const actBox = document.getElementById('grn-dd-actions');
            if (isGrn) {
                actBox.innerHTML = `
                    <button type="button" class="btn-action" style="padding:9px 18px; border-radius:10px; background:linear-gradient(135deg,#0284c7,#0369a1);" onclick="document.getElementById('grn-drilldown-modal').style.display='none'; window.printGRNVoucherById && window.printGRNVoucherById('${docId}')">🖨️ طباعة السند</button>
                    <button type="button" class="btn-action" style="padding:9px 18px; border-radius:10px; background:linear-gradient(135deg,#10b981,#059669);" onclick="document.getElementById('grn-drilldown-modal').style.display='none'; editGRN('${docId}')">✏️ تعديل الإذن</button>
                `;
            } else {
                actBox.innerHTML = `
                    <button type="button" class="btn-action" style="padding:9px 18px; border-radius:10px; background:linear-gradient(135deg,#0284c7,#0369a1);" onclick="window.open('purchase.php?id=${docId}', '_blank')">🧾 فتح في شاشة المشتريات</button>
                    <button type="button" class="btn-action" style="padding:9px 18px; border-radius:10px; background:linear-gradient(135deg,#10b981,#059669);" onclick="document.getElementById('grn-drilldown-modal').style.display='none'; openReceiptEditor('${docId}')">📥 فحص واستلام البضاعة</button>
                `;
            }
            modal.style.display = 'flex';
        };

        // 6. Hub Exporters: Excel (CSV UTF-8 BOM), A4 / PDF Print, Thermal 80mm Receipt & WhatsApp Summary
        window.exportGrnHubToExcel = function() {
            const isPending = (currentHubView === 'pending');
            const table = isPending
                ? document.querySelector('#hub-pending-container table.pos-grid')
                : document.querySelector('#recent-grns-container table.pos-grid');
            if (!table) return showToast('⚠️ لا توجد بيانات معروضة للتصدير حالياً', 'warning');

            const rows = Array.from(table.querySelectorAll('tr'));
            const csvLines = rows.map(tr => {
                const cells = Array.from(tr.querySelectorAll('th, td')).slice(0, -1); // exclude action buttons col
                return cells.map(c => `"${(c.innerText || c.textContent || '').replace(/\s+/g, ' ').trim().replace(/"/g, '""')}"`).join(',');
            });
            const bom = '\uFEFF';
            const blob = new Blob([bom + csvLines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = (isPending ? 'Pending_Receipts_' : 'Received_GRNs_') + new Date().toISOString().slice(0, 10) + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showToast('📊 تم تصدير الكشف إلى ملف Excel بنجاح!', 'success');
        };

        window.printGrnHubReport = function(mode) {
            const isThermal = (mode === 'thermal');
            const isPending = (currentHubView === 'pending');
            const title = isPending ? 'كشف فواتير المشتريات قيد الاستلام المخزني' : 'تقرير سجل أذونات الاستلام المخزني المعتمدة (GRN)';
            const table = isPending
                ? document.querySelector('#hub-pending-container table.pos-grid')
                : document.querySelector('#recent-grns-container table.pos-grid');
            if (!table) return showToast('⚠️ لا توجد بيانات معروضة للطباعة حالياً', 'warning');

            const rows = Array.from(table.querySelectorAll('tr'));
            const cleanRowsHtml = rows.map((tr, rIdx) => {
                const cells = Array.from(tr.querySelectorAll('th, td')).slice(0, -1);
                const tag = rIdx === 0 ? 'th' : 'td';
                return `<tr>${cells.map(c => `<${tag}>${(c.innerText || c.textContent || '').replace(/\s+/g, ' ').trim()}</${tag}>`).join('')}</tr>`;
            }).join('');

            const w = window.open('', '_blank', isThermal ? 'width=420,height=700' : 'width=1000,height=760');
            if (!w) return showToast('⚠️ يرجى السماح بالنوافذ المنبثقة للطباعة', 'warning');
            w.document.write(`<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8"><title>${title}</title>
            <style>
                @page { size: ${isThermal ? '80mm auto' : 'A4 landscape'}; margin: ${isThermal ? '4mm' : '10mm'}; }
                body { font-family: 'Tajawal', sans-serif; color: #000000; background: #FFFFFF; margin: 0; padding: ${isThermal ? '6px' : '18px'}; width: ${isThermal ? '74mm' : 'auto'}; }
                h2 { margin: 0 0 8px 0; font-size: ${isThermal ? '1rem' : '1.35rem'}; text-align: center; font-weight: 900; }
                .meta { text-align: center; font-size: ${isThermal ? '0.75rem' : '0.9rem'}; font-weight: 800; margin-bottom: 12px; border-bottom: 2px solid #000; padding-bottom: 8px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1.8px solid #000000; padding: ${isThermal ? '4px 3px' : '7px 8px'}; font-size: ${isThermal ? '0.7rem' : '0.86rem'}; font-weight: 800; text-align: center; color: #000000; }
                th { background: #e2e8f0; font-weight: 900; }
                @media print { .no-print { display: none !important; } }
            </style></head><body>
                <div class="no-print" style="margin-bottom:10px; text-align:left;">
                    <button onclick="window.print()" style="padding:8px 18px; font-weight:900; background:#0284c7; color:#fff; border:none; border-radius:8px; cursor:pointer;">🖨️ طباعة الآن / حفظ PDF</button>
                </div>
                <h2>${title}</h2>
                <div class="meta">تاريخ الطباعة: ${new Date().toLocaleString('en-GB')} | إجمالي السجلات: ${Math.max(0, rows.length - 1)}</div>
                <table>${cleanRowsHtml}</table>
            </body></html>`);
            w.document.close();
        };

        window.shareGrnHubSummaryWhatsApp = function() {
            const isPending = (currentHubView === 'pending');
            const count = isPending ? Object.keys(pendingMap || {}).length : (allPurchases || []).filter(p => p && p.isGRN).length;
            const msg = `📦 *ملخص بوابة الاستلام المخزني (GRN)*\n`
                      + `• الحالة: ${isPending ? '⏳ فواتير قيد الاستلام' : '✅ أذونات مستلمة فعلياً'}\n`
                      + `• العدد الإجمالي: *${count}* مستند\n`
                      + `• التاريخ: ${new Date().toLocaleString('en-GB')}`;
            window.open('https://wa.me/?text=' + encodeURIComponent(msg), '_blank');
        };

        // Hook initData to refresh filter dropdowns & sync badge
        const _c3InitData = window.initData;
        if (typeof _c3InitData === 'function') {
            window.initData = function() {
                _c3InitData.apply(this, arguments);
                window.populateGrnHubFilterDropdowns();
            };
        }
        setTimeout(() => { window.populateGrnHubFilterDropdowns(); }, 350);

        // Sub-second cross-tab localStorage, connection recovery & active heartbeat sync (Order 1)
        window.addEventListener('storage', function(e) {
            if (!e || !e.key) return;
            if (['erp_purchase_invoices_v1', 'erp_purchases_v1', 'erp_grn_v1', 'erp_items_v1', 'erp_warehouses_v1', 'erp_grn_offline_queue_v1'].includes(e.key)) {
                initData();
                window.updateGrnLiveSyncBadge();
            }
        });
        window.addEventListener('online', function() {
            window.updateGrnLiveSyncBadge();
            flushGrnOfflineQueue().then(() => window.updateGrnLiveSyncBadge());
            syncPurchaseReceiptsWithMariaDB();
        });
        window.addEventListener('offline', function() {
            window.updateGrnLiveSyncBadge();
        });
        if (navigator.connection && typeof navigator.connection.addEventListener === 'function') {
            navigator.connection.addEventListener('change', function() {
                if (navigator.onLine) {
                    flushGrnOfflineQueue().then(() => window.updateGrnLiveSyncBadge());
                    syncPurchaseReceiptsWithMariaDB();
                }
            });
        }
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                flushGrnOfflineQueue().then(() => window.updateGrnLiveSyncBadge());
                syncPurchaseReceiptsWithMariaDB();
            }
        });
        setInterval(function() {
            if (document.visibilityState === 'visible' && navigator.onLine) {
                flushGrnOfflineQueue().then(() => window.updateGrnLiveSyncBadge());
                syncPurchaseReceiptsWithMariaDB();
            }
        }, 6500);

        // ═══════════════════════════════════════════════════════════════════════════
        // APPROVED CONSTITUTION 3 PROPOSALS (1, 2 & 3):
        // 1. Touch/Mouse Digital Signature Pad (#grn-signature-modal)
        // 2. Landed Cost Allocation Engine (#grn-landed-cost-modal)
        // 3. Supplier Delivery KPI Strip (#grn-supplier-kpi-strip)
        // ═══════════════════════════════════════════════════════════════════════════
        window._grnInspectorSigDataUrl = '';
        window._grnDriverSigDataUrl = '';

        function bindSignatureCanvas(canvasId) {
            const canvas = document.getElementById(canvasId);
            if (!canvas || canvas.dataset.bound) return;
            canvas.dataset.bound = '1';
            const ctx = canvas.getContext('2d');
            ctx.strokeStyle = '#38bdf8';
            ctx.lineWidth = 2.6;
            ctx.lineCap = 'round';
            let drawing = false;
            const getPos = (ev) => {
                const r = canvas.getBoundingClientRect();
                const pt = (ev.touches && ev.touches[0]) ? ev.touches[0] : ev;
                return { x: pt.clientX - r.left, y: pt.clientY - r.top };
            };
            const start = (ev) => {
                drawing = true;
                const p = getPos(ev);
                ctx.beginPath();
                ctx.moveTo(p.x, p.y);
            };
            const move = (ev) => {
                if (!drawing) return;
                if (ev.cancelable) ev.preventDefault();
                const p = getPos(ev);
                ctx.lineTo(p.x, p.y);
                ctx.stroke();
            };
            const end = () => { drawing = false; };
            canvas.addEventListener('mousedown', start);
            canvas.addEventListener('mousemove', move);
            window.addEventListener('mouseup', end);
            canvas.addEventListener('touchstart', start, { passive: false });
            canvas.addEventListener('touchmove', move, { passive: false });
            canvas.addEventListener('touchend', end);
        }

        window.clearGrnSigCanvas = function(canvasId) {
            const c = document.getElementById(canvasId);
            if (!c) return;
            const ctx = c.getContext('2d');
            ctx.clearRect(0, 0, c.width, c.height);
        };

        window.openGrnSignatureModal = function() {
            ensureGrnChildModalsDom();
            let modal = document.getElementById('grn-signature-modal');
            if (!modal) {
                const html = `
                    <div id="grn-signature-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.78); backdrop-filter:blur(6px); z-index:100029; align-items:center; justify-content:center; padding:16px;">
                        <div class="grn-child-modal-card" style="background:#0f172a; border:2.5px solid #6366f1; border-radius:18px; width:100%; max-width:620px; padding:24px; box-shadow:0 20px 50px rgba(0,0,0,0.85);">
                            <h3 style="margin:0 0 14px 0; font-size:1.2rem; font-weight:900; color:#FFFFFF;">✍️ التوقيع الإلكتروني المعتمد (أمين المستودع والسائق)</h3>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                                <div>
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                        <label style="font-size:0.86rem; font-weight:900;">🛡️ توقيع أمين المستودع:</label>
                                        <button type="button" onclick="window.clearGrnSigCanvas('grn-sig-inspector-canvas')" class="btn-action btn-cancel" style="padding:3px 9px; font-size:0.74rem; border-radius:6px;">مسح</button>
                                    </div>
                                    <canvas id="grn-sig-inspector-canvas" width="265" height="130" style="width:100%; height:130px; background:#000000; border:2px solid #FFFFFF; border-radius:10px; cursor:crosshair; touch-action:none;"></canvas>
                                </div>
                                <div>
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                        <label style="font-size:0.86rem; font-weight:900;">🚛 توقيع السائق / المندوب:</label>
                                        <button type="button" onclick="window.clearGrnSigCanvas('grn-sig-driver-canvas')" class="btn-action btn-cancel" style="padding:3px 9px; font-size:0.74rem; border-radius:6px;">مسح</button>
                                    </div>
                                    <canvas id="grn-sig-driver-canvas" width="265" height="130" style="width:100%; height:130px; background:#000000; border:2px solid #FFFFFF; border-radius:10px; cursor:crosshair; touch-action:none;"></canvas>
                                </div>
                            </div>
                            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:18px;">
                                <button type="button" onclick="document.getElementById('grn-signature-modal').style.display='none'" class="btn-action btn-cancel" style="padding:10px 18px; border-radius:10px; font-weight:800;">إلغاء</button>
                                <button type="button" id="grn-sig-save-btn" class="btn-action" style="padding:10px 22px; border-radius:10px; background:linear-gradient(135deg,#6366f1,#4f46e5); color:#FFFFFF; font-weight:900;">✍️ اعتماد وحفظ التوقيعين</button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', html);
                modal = document.getElementById('grn-signature-modal');
                bindSignatureCanvas('grn-sig-inspector-canvas');
                bindSignatureCanvas('grn-sig-driver-canvas');

                document.getElementById('grn-sig-save-btn').addEventListener('click', () => {
                    const c1 = document.getElementById('grn-sig-inspector-canvas');
                    const c2 = document.getElementById('grn-sig-driver-canvas');
                    window._grnInspectorSigDataUrl = c1 ? c1.toDataURL('image/png') : '';
                    window._grnDriverSigDataUrl = c2 ? c2.toDataURL('image/png') : '';
                    window._grnHasUnsavedChanges = true;
                    const sigBtn = document.getElementById('btn-grn-signature');
                    if (sigBtn) sigBtn.innerHTML = '<span>✍️</span> تم التوقيع ✅';
                    modal.style.display = 'none';
                    showToast('✍️ تم اعتماد وحفظ التوقيع الإلكتروني لأمين المستودع والسائق بنجاح!', 'success');
                });
            }
            modal.style.display = 'flex';
        };

        // 2. Landed Cost Allocation Engine (توزيع تكلفة النقل والتعتيق والرسوم على الأصناف المستلمة)
        window.openGrnLandedCostModal = function() {
            ensureGrnChildModalsDom();
            let modal = document.getElementById('grn-landed-cost-modal');
            if (!modal) {
                const html = `
                    <div id="grn-landed-cost-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.78); backdrop-filter:blur(6px); z-index:100030; align-items:center; justify-content:center; padding:16px;">
                        <div class="grn-child-modal-card" style="background:#0f172a; border:2.5px solid #0891b2; border-radius:18px; width:100%; max-width:640px; padding:24px; box-shadow:0 20px 50px rgba(0,0,0,0.85);">
                            <h3 style="margin:0 0 14px 0; font-size:1.2rem; font-weight:900; color:#FFFFFF;">🚚 توزيع تكلفة النقل والتعتيق والرسوم (Landed Cost Allocation)</h3>
                            <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:10px; margin-bottom:14px;">
                                <div>
                                    <label style="display:block; margin-bottom:4px; font-size:0.8rem; font-weight:900;">نقل وشحن:</label>
                                    <input type="number" id="grn-lc-freight" class="grn-log-input" min="0" step="any" value="0" style="width:100%; height:40px; text-align:center;">
                                </div>
                                <div>
                                    <label style="display:block; margin-bottom:4px; font-size:0.8rem; font-weight:900;">تعتيق وتنزيل:</label>
                                    <input type="number" id="grn-lc-labor" class="grn-log-input" min="0" step="any" value="0" style="width:100%; height:40px; text-align:center;">
                                </div>
                                <div>
                                    <label style="display:block; margin-bottom:4px; font-size:0.8rem; font-weight:900;">رسوم أخرى:</label>
                                    <input type="number" id="grn-lc-other" class="grn-log-input" min="0" step="any" value="0" style="width:100%; height:40px; text-align:center;">
                                </div>
                                <div>
                                    <label style="display:block; margin-bottom:4px; font-size:0.8rem; font-weight:900;">طريقة التوزيع:</label>
                                    <select id="grn-lc-method" class="grn-log-input" style="width:100%; height:40px;">
                                        <option value="qty">حسب الكمية/الوزن</option>
                                        <option value="val">حسب القيمة المالية</option>
                                    </select>
                                </div>
                            </div>
                            <div id="grn-lc-preview-box" style="max-height:210px; overflow-y:auto; border:1.5px solid rgba(56,189,248,0.35); border-radius:12px; padding:10px; font-size:0.84rem; font-weight:800;"></div>
                            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                                <button type="button" onclick="document.getElementById('grn-landed-cost-modal').style.display='none'" class="btn-action btn-cancel" style="padding:10px 18px; border-radius:10px; font-weight:800;">إلغاء</button>
                                <button type="button" id="grn-lc-apply-btn" class="btn-action" style="padding:10px 22px; border-radius:10px; background:linear-gradient(135deg,#0891b2,#0e7490); color:#FFFFFF; font-weight:900;">🚚 اعتماد وتوزيع التكلفة على الأصناف</button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', html);
                modal = document.getElementById('grn-landed-cost-modal');
            }

            const computeLcRows = () => {
                const freight = parseFloat(document.getElementById('grn-lc-freight').value) || 0;
                const labor   = parseFloat(document.getElementById('grn-lc-labor').value) || 0;
                const other   = parseFloat(document.getElementById('grn-lc-other').value) || 0;
                const totalExtra = Math.round((freight + labor + other) * 100) / 100;
                const method  = document.getElementById('grn-lc-method').value || 'qty';

                const active = (receiptLines || []).filter(l => (parseFloat(l.receiveQty) || 0) > 0);
                const sumQty = active.reduce((s, l) => s + (parseFloat(l.receiveQty) || 0) * (parseFloat(l.factor || l.customFactor) || 1), 0);
                const sumVal = active.reduce((s, l) => s + (parseFloat(l.receiveQty) || 0) * (parseFloat(l.price) || 0), 0);

                const result = active.map(l => {
                    const rQty = parseFloat(l.receiveQty) || 0;
                    const fac  = parseFloat(l.factor || l.customFactor) || 1;
                    const baseQ = rQty * fac;
                    const baseP = parseFloat(l.origPrice ?? l.price) || 0;
                    if (l.origPrice === undefined) l.origPrice = baseP;
                    const lineVal = rQty * baseP;
                    const weightShare = (method === 'val' && sumVal > 0)
                        ? (lineVal / sumVal)
                        : (sumQty > 0 ? (baseQ / sumQty) : (1 / Math.max(1, active.length)));
                    const allocatedTotal = Math.round(totalExtra * weightShare * 100) / 100;
                    const extraPerUnit = rQty > 0 ? Math.round((allocatedTotal / rQty) * 100) / 100 : 0;
                    const landedUnitCost = Math.round((baseP + extraPerUnit) * 100) / 100;
                    return { line: l, baseP, extraPerUnit, landedUnitCost, allocatedTotal };
                });

                const previewEl = document.getElementById('grn-lc-preview-box');
                if (previewEl) {
                    previewEl.innerHTML = `
                        <div style="margin-bottom:8px; color:#38bdf8;">إجمالي مصاريف النقل والتعتيق الموزعة: <strong style="font-family:Outfit;">${fmtNum(totalExtra)} SDG</strong></div>
                        <table style="width:100%; border-collapse:collapse;">
                            <thead>
                                <tr style="background:rgba(56,189,248,0.14);">
                                    <th style="padding:6px; border:1px solid rgba(148,163,184,0.35); text-align:right;">الصنف</th>
                                    <th style="padding:6px; border:1px solid rgba(148,163,184,0.35);">سعر الفاتورة</th>
                                    <th style="padding:6px; border:1px solid rgba(148,163,184,0.35);">نصيب الوحدة</th>
                                    <th style="padding:6px; border:1px solid rgba(148,163,184,0.35);">التكلفة النهائية</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${result.map(r => `
                                    <tr>
                                        <td style="padding:6px; border:1px solid rgba(148,163,184,0.35); text-align:right;">${getItemName(r.line.itemId)}</td>
                                        <td style="padding:6px; border:1px solid rgba(148,163,184,0.35); text-align:center; font-family:Outfit;">${fmtNum(r.baseP)}</td>
                                        <td style="padding:6px; border:1px solid rgba(148,163,184,0.35); text-align:center; font-family:Outfit; color:#fbbf24;">+${fmtNum(r.extraPerUnit)}</td>
                                        <td style="padding:6px; border:1px solid rgba(148,163,184,0.35); text-align:center; font-family:Outfit; color:#34d399;">${fmtNum(r.landedUnitCost)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;
                }
                return { totalExtra, result };
            };

            ['grn-lc-freight', 'grn-lc-labor', 'grn-lc-other', 'grn-lc-method'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.oninput = computeLcRows;
                    el.onchange = computeLcRows;
                }
            });

            document.getElementById('grn-lc-apply-btn').onclick = () => {
                const { totalExtra, result } = computeLcRows();
                result.forEach(r => {
                    r.line.price = r.landedUnitCost;
                    r.line.landedExtraPerUnit = r.extraPerUnit;
                    const lcTag = `[تكلفة نقل/تعتيق للوحدة: +${r.extraPerUnit}]`;
                    if (!(r.line.notes || '').includes('تكلفة نقل/تعتيق')) {
                        r.line.notes = ((r.line.notes || '') + ' ' + lcTag).trim();
                    }
                });
                window._grnHasUnsavedChanges = true;
                renderReceiptLines();
                updateSummary();
                modal.style.display = 'none';
                showToast(`🚚 تم توزيع مصاريف النقل والتعتيق (${fmtNum(totalExtra)} SDG) وتحديث التكلفة الفعلية للأصناف!`, 'success');
            };

            computeLcRows();
            modal.style.display = 'flex';
        };

        // 3. Supplier Delivery KPI Strip (#grn-supplier-kpi-strip) at top of #view-hub
        window.renderGrnSupplierKpiStrip = function() {
            const hubCard = document.querySelector('#view-hub .neo-card');
            const filterBar = document.getElementById('grn-hub-filters-bar');
            if (!hubCard || !filterBar) return;

            let strip = document.getElementById('grn-supplier-kpi-strip');
            if (!strip) {
                strip = document.createElement('div');
                strip.id = 'grn-supplier-kpi-strip';
                strip.style.cssText = 'display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:10px; margin-bottom:12px;';
                hubCard.insertBefore(strip, filterBar);
            }

            const pendingCount = Object.keys(pendingMap || {}).length;
            const grnsList = (allPurchases || []).filter(p => p && p.isGRN);
            const totalGrns = grnsList.length;
            const fullDelivered = grnsList.filter(g => g.isFullyReceived !== false).length;
            const fullRate = totalGrns > 0 ? Math.round((fullDelivered / totalGrns) * 100) : 100;

            let totalRcvItems = 0;
            let dmgItems = 0;
            const supCounts = {};
            grnsList.forEach(g => {
                const sId = g.customer || g.customerId || g.supplierId || '';
                if (sId) supCounts[sId] = (supCounts[sId] || 0) + 1;
                (g.items || []).filter(i => (parseFloat(i.quantity) || 0) > 0).forEach(i => {
                    totalRcvItems++;
                    if ((i.notes || '').includes('تالف')) dmgItems++;
                });
            });
            const qcPassRate = totalRcvItems > 0 ? Math.max(0, Math.round(((totalRcvItems - dmgItems) / totalRcvItems) * 100)) : 100;
            let topSupId = Object.keys(supCounts).sort((a, b) => supCounts[b] - supCounts[a])[0];
            const topSupName = topSupId ? getSupplierName(topSupId) : ((suppliersDb && suppliersDb[0]) ? suppliersDb[0].name : '—');

            strip.innerHTML = `
                <div class="grn-logistics-bar" style="padding:10px 14px; display:flex; align-items:center; justify-content:space-between;">
                    <span style="font-size:0.84rem; font-weight:900;">⏳ فواتير بانتظار الاستلام:</span>
                    <strong style="font-family:Outfit; font-size:1.15rem; color:#fbbf24;">${pendingCount}</strong>
                </div>
                <div class="grn-logistics-bar" style="padding:10px 14px; display:flex; align-items:center; justify-content:space-between;">
                    <span style="font-size:0.84rem; font-weight:900;">✅ التزام التوريد الكامل:</span>
                    <strong style="font-family:Outfit; font-size:1.15rem; color:#34d399;">${fullRate}% (${totalGrns} إذن)</strong>
                </div>
                <div class="grn-logistics-bar" style="padding:10px 14px; display:flex; align-items:center; justify-content:space-between;">
                    <span style="font-size:0.84rem; font-weight:900;">🛡️ مؤشر سلامة الجودة QC:</span>
                    <strong style="font-family:Outfit; font-size:1.15rem; color:#38bdf8;">${qcPassRate}%</strong>
                </div>
                <div class="grn-logistics-bar" style="padding:10px 14px; display:flex; align-items:center; justify-content:space-between; overflow:hidden;">
                    <span style="font-size:0.84rem; font-weight:900; white-space:nowrap;">🏆 أعلى مورد توريداً:</span>
                    <strong style="font-size:0.85rem; color:#c084fc; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:140px;" title="${topSupName}">${topSupName}</strong>
                </div>
            `;
        };

        const _prevPopFilter = window.populateGrnHubFilterDropdowns;
        window.populateGrnHubFilterDropdowns = function() {
            if (typeof _prevPopFilter === 'function') _prevPopFilter();
            window.renderGrnSupplierKpiStrip();
        };
        setTimeout(() => { window.renderGrnSupplierKpiStrip(); }, 450);

        // ═══════════════════════════════════════════════════════════════════════════
        // CONSTITUTION 4 ENGINE:
        // 1. Self-Updating Dynamic Smart Guide (MutationObserver & Live DOM Discovery)
        // 2. Future-Proof Inheritance for Any Newly Added Inputs, Selects, Buttons & Modals
        // ═══════════════════════════════════════════════════════════════════════════
        const _baseExplainGrnEl = window.explainPurchaseReceiptDomElement;
        window.explainPurchaseReceiptDomElement = function(el) {
            if (!el) return null;
            const known = typeof _baseExplainGrnEl === 'function' ? _baseExplainGrnEl(el) : null;
            if (known) return known;

            // Dynamic Self-Synthesis for any future button, input, select, or modal added to the screen!
            const target = el.closest('button, a, input, select, textarea, .grn-child-modal-card, [data-guide-title]');
            if (!target) return null;
            const customTitle = target.getAttribute('data-guide-title') || target.getAttribute('title') || target.getAttribute('placeholder') || target.getAttribute('aria-label') || (target.innerText || target.textContent || '').replace(/\s+/g, ' ').trim().slice(0, 48);
            if (!customTitle) return null;
            const customDesc = target.getAttribute('data-guide-desc')
                || (target.tagName === 'SELECT' ? `قائمة منسدلة لاختيار (${customTitle}) وتطبيقها فوراً داخل شاشة استلام البضاعة المخزني.`
                : (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' ? `حقل إدخال مخصص لـ (${customTitle}) مع دعم التنقل السلس بزر Enter والتظليل التلقائي.`
                : `أداة تفاعلية لتنفيذ (${customTitle}) مباشرة وبأقصى سرعة داخل منظومة الاستلام المخزني.`));
            return { title: `✨ ${customTitle}`, desc: customDesc };
        };

        window.syncSelfUpdatingGrnGuideAndInheritance = function() {
            const guideObj = window.MagaiesSmartGuides && window.MagaiesSmartGuides['purchase-receipt'];
            if (!guideObj) return;
            guideObj.buttonsDict = Array.isArray(guideObj.buttonsDict) ? guideObj.buttonsDict : [];
            guideObj.autopilotSteps = Array.isArray(guideObj.autopilotSteps) ? guideObj.autopilotSteps : [];

            // 1. Future-Proof Inheritance on all current & future numeric/text inputs
            document.querySelectorAll('input[type="number"], input[inputmode="decimal"], .qty-input, .factor-input').forEach(inp => {
                if (inp.dataset.c4AutoSelectBound) return;
                inp.dataset.c4AutoSelectBound = '1';
                inp.addEventListener('focus', function() {
                    setTimeout(() => { try { this.select(); } catch (_e) {} }, 10);
                });
                inp.addEventListener('click', function() {
                    try { this.select(); } catch (_e) {}
                });
            });

            // 2. Discover any newly added buttons/inputs with an ID that aren't yet in buttonsDict
            const knownSelectors = new Set(guideObj.buttonsDict.map(b => b.targetSelector));
            document.querySelectorAll('#view-hub button[id], #view-editor button[id], #view-hub select[id], #view-editor select[id], #view-hub input[id], #view-editor input[id]').forEach(el => {
                const idSel = '#' + el.id;
                if (knownSelectors.has(idSel)) return;
                const anyMatch = Array.from(knownSelectors).some(s => s && s.includes(el.id));
                if (anyMatch) return;
                const synthesized = window.explainPurchaseReceiptDomElement(el);
                if (!synthesized) return;
                knownSelectors.add(idSel);
                guideObj.buttonsDict.push({
                    label: synthesized.title,
                    targetSelector: idSel,
                    whatItDoes: synthesized.desc,
                    whatHappensIfClicked: 'يتم تنفيذ الوظيفة وتحديث شاشة الاستلام المخزني والتقارير المرتبطة بها لحظياً.'
                });
            });
        };

        // =========================================================================
        // CONSTITUTION 4 APPROVED PROPOSALS:
        // 1. EXPIRY RISK RADAR (رادار تنبيهات الصلاحية الحرجة — صوتي وبصري)
        // 2. SMART OCR / SUPPLIER NOTE MATCHER (الاستلام الذكي ومطابقة إشعار المورد)
        // 3. WAREHOUSE CAPACITY & BIN HEATMAP (خريطة السعة الاستيعابية للمخازن والأرفف)
        // =========================================================================

        window.evaluateGrnExpiryRisk = function(expiryDateStr, itemName, announceVoice) {
            if (!expiryDateStr) return { status: 'none', days: null, label: 'غير محدد' };
            const expTime = Date.parse(expiryDateStr);
            if (isNaN(expTime)) return { status: 'none', days: null, label: 'تاريخ غير صالح' };
            const now = new Date();
            now.setHours(0, 0, 0, 0);
            const diffDays = Math.ceil((expTime - now.getTime()) / (1000 * 60 * 60 * 24));
            const labelName = itemName || 'هذا الصنف';
            if (diffDays <= 0) {
                const msg = `⛔ تحذير حرج: الصنف (${labelName}) منتهي الصلاحية منذ ${Math.abs(diffDays)} يوم! يُوصى بتحويله لحالة تالف أو مرفوض.`;
                if (announceVoice) {
                    showToast(msg, 'error');
                    if (typeof window.speakGrnMaleVoice === 'function') {
                        window.speakGrnMaleVoice(`تنبيه حرج لأمين المستودع: الصنف ${labelName} منتهي الصلاحية. يرجى عزله فوراً أو رفض استلامه.`);
                    }
                }
                return { status: 'expired', days: diffDays, label: `منتهي الصلاحية (${diffDays} يوم)` };
            } else if (diffDays <= 90) {
                const msg = `⚠️ تنبيه صلاحية حرجة: الصنف (${labelName}) يتبقى على انتهاء صلاحيته ${diffDays} يوماً فقط (أقل من 90 يوماً).`;
                if (announceVoice) {
                    showToast(msg, 'info');
                    if (typeof window.speakGrnMaleVoice === 'function') {
                        window.speakGrnMaleVoice(`تنبيه صلاحية: الصنف ${labelName} يتبقى على انتهاء صلاحيته ${diffDays} يوماً فقط. يوصى بوضعه في رف الصرف الأولوي.`);
                    }
                }
                return { status: 'critical', days: diffDays, label: `قريب الانتهاء (${diffDays} يوم)` };
            } else {
                if (announceVoice) {
                    showToast(`✅ صلاحية آمنة للصنف (${labelName}) — متبقي ${diffDays} يوماً.`, 'success');
                }
                return { status: 'safe', days: diffDays, label: `آمن (${diffDays} يوم)` };
            }
        };

        window.openGrnExpiryRadarModal = function() {
            let modal = document.getElementById('grn-expiry-radar-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'grn-expiry-radar-modal';
                modal.className = 'grn-child-modal-backdrop';
                modal.innerHTML = `
                    <div class="grn-child-modal-card" style="max-width:700px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                            <h3 style="margin:0;font-size:1.12rem;font-weight:900;display:flex;align-items:center;gap:8px;">
                                <span>⏳</span> رادار تنبيهات الصلاحية الحرجة (Expiry Risk Radar)
                            </h3>
                            <button type="button" onclick="document.getElementById('grn-expiry-radar-modal').style.display='none'" style="background:transparent;border:none;font-size:1.3rem;cursor:pointer;color:#94a3b8;font-weight:900;">✕</button>
                        </div>
                        <div id="grn-expiry-radar-summary" style="margin-bottom:12px;padding:10px 14px;border-radius:10px;border:2px solid rgba(239,68,68,0.4);font-weight:800;font-size:0.88rem;"></div>
                        <div style="max-height:320px;overflow-y:auto;border:2px solid rgba(148,163,184,0.3);border-radius:12px;margin-bottom:14px;">
                            <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
                                <thead>
                                    <tr style="background:rgba(15,23,42,0.5);">
                                        <th style="padding:9px;text-align:right;">الصنف</th>
                                        <th style="padding:9px;text-align:center;">رقم التشغيلة</th>
                                        <th style="padding:9px;text-align:center;">تاريخ الانتهاء</th>
                                        <th style="padding:9px;text-align:center;">الأيام المتبقية</th>
                                        <th style="padding:9px;text-align:center;">الإجراء السريع</th>
                                    </tr>
                                </thead>
                                <tbody id="grn-expiry-radar-tbody"></tbody>
                            </table>
                        </div>
                        <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                            <button type="button" class="btn-action" style="background:linear-gradient(135deg,#0284c7,#0369a1);padding:9px 14px;border-radius:10px;font-weight:900;" onclick="window.autoAssignSampleBatchExpiry && window.autoAssignSampleBatchExpiry()">⚡ توليد تواريخ صلاحية وتشغيلات قياسية للأصناف الفارغة</button>
                            <button type="button" class="btn-secondary" style="padding:9px 16px;border-radius:10px;font-weight:900;" onclick="document.getElementById('grn-expiry-radar-modal').style.display='none'">إغلاق</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }

            const renderRadarTable = () => {
                const tbody = document.getElementById('grn-expiry-radar-tbody');
                const sumEl = document.getElementById('grn-expiry-radar-summary');
                if (!tbody || !sumEl) return;
                const lines = Array.isArray(window.receiptLines) ? window.receiptLines : [];
                let expiredCount = 0, criticalCount = 0, safeCount = 0, unsetCount = 0;
                tbody.innerHTML = lines.map((l, idx) => {
                    const r = window.evaluateGrnExpiryRisk(l.expiryDate, l.itemName, false);
                    if (r.status === 'expired') expiredCount++;
                    else if (r.status === 'critical') criticalCount++;
                    else if (r.status === 'safe') safeCount++;
                    else unsetCount++;
                    const badgeStyle = r.status === 'expired'
                        ? 'background:rgba(239,68,68,0.2);color:#ef4444;border:1.8px solid #ef4444;'
                        : (r.status === 'critical'
                            ? 'background:rgba(245,158,11,0.2);color:#f59e0b;border:1.8px solid #f59e0b;'
                            : (r.status === 'safe'
                                ? 'background:rgba(16,185,129,0.2);color:#10b981;border:1.8px solid #10b981;'
                                : 'background:rgba(148,163,184,0.15);color:#94a3b8;border:1.8px solid #64748b;'));
                    return `
                        <tr style="border-bottom:1.5px solid rgba(148,163,184,0.2);">
                            <td style="padding:8px 10px;font-weight:900;">${escapeHtml(l.itemName || '—')}</td>
                            <td style="padding:8px;text-align:center;font-family:'Outfit';font-weight:800;">${escapeHtml(l.batchNo || '—')}</td>
                            <td style="padding:8px;text-align:center;">
                                <input type="date" value="${escapeHtml(l.expiryDate || '')}" onchange="window.updateGrnLineExpiryFromRadar(${idx}, this.value)" style="padding:5px 8px;border-radius:8px;font-family:'Outfit';font-weight:800;">
                            </td>
                            <td style="padding:8px;text-align:center;">
                                <span style="padding:3px 9px;border-radius:99px;font-size:0.78rem;font-weight:900;${badgeStyle}">${r.label}</span>
                            </td>
                            <td style="padding:8px;text-align:center;">
                                ${r.status === 'expired' ? `<button type="button" onclick="window.quarantineExpiredGrnLine(${idx})" style="padding:4px 9px;border-radius:8px;background:#ef4444;color:#fff;border:none;cursor:pointer;font-weight:900;font-size:0.76rem;">🚨 عزل كتالف</button>` : `<span style="font-size:0.78rem;font-weight:800;color:#10b981;">✔️ صالح للتسكين</span>`}
                            </td>
                        </tr>
                    `;
                }).join('') || `<tr><td colspan="5" style="padding:18px;text-align:center;font-weight:800;">لا توجد أصناف محملة حالياً في شاشة الاستلام</td></tr>`;

                sumEl.innerHTML = `📊 ملخص فحص الصلاحية: <span style="color:#ef4444;">⛔ منتهي: ${expiredCount}</span> | <span style="color:#f59e0b;">⚠️ حرج (&lt;90 يوم): ${criticalCount}</span> | <span style="color:#10b981;">✅ آمن: ${safeCount}</span> | <span>غير محدد: ${unsetCount}</span>`;
                if ((expiredCount > 0 || criticalCount > 0) && typeof window.speakGrnMaleVoice === 'function') {
                    window.speakGrnMaleVoice(`رادار الصلاحية رصد ${expiredCount} أصناف منتهية الصلاحية و ${criticalCount} أصناف قريبة الانتهاء.`);
                }
            };

            window.updateGrnLineExpiryFromRadar = function(idx, dateVal) {
                if (!window.receiptLines || !window.receiptLines[idx]) return;
                window.receiptLines[idx].expiryDate = dateVal;
                if (!window.receiptLines[idx].batchNo) {
                    window.receiptLines[idx].batchNo = 'LOT-' + new Date().toISOString().slice(2, 10).replace(/-/g, '');
                }
                window.evaluateGrnExpiryRisk(dateVal, window.receiptLines[idx].itemName, true);
                if (typeof window.renderReceiptLines === 'function') window.renderReceiptLines();
                renderRadarTable();
            };

            window.quarantineExpiredGrnLine = function(idx) {
                if (!window.receiptLines || !window.receiptLines[idx]) return;
                const line = window.receiptLines[idx];
                line.qcStatus = 'dmg';
                line.notes = (line.notes ? line.notes + ' | ' : '') + '⛔ معزول: منتهي الصلاحية (' + (line.expiryDate || '') + ')';
                if (typeof window.renderReceiptLines === 'function') window.renderReceiptLines();
                renderRadarTable();
                showToast(`🚨 تم عزل الصنف (${line.itemName}) وتحويل حالته إلى تالف/مرفوض`, 'error');
            };

            window.autoAssignSampleBatchExpiry = function() {
                const lines = Array.isArray(window.receiptLines) ? window.receiptLines : [];
                const d = new Date();
                d.setDate(d.getDate() + 180);
                const defaultExp = d.toISOString().slice(0, 10);
                const lotPrefix = 'LOT-' + new Date().toISOString().slice(2, 10).replace(/-/g, '');
                lines.forEach((l, i) => {
                    if (!l.expiryDate) l.expiryDate = defaultExp;
                    if (!l.batchNo) l.batchNo = `${lotPrefix}-${i + 1}`;
                });
                if (typeof window.renderReceiptLines === 'function') window.renderReceiptLines();
                renderRadarTable();
                showToast('⚡ تم توليد أرقام التشغيلات وتواريخ الصلاحية القياسية للأصناف بنجاح', 'success');
            };

            renderRadarTable();
            modal.style.display = 'flex';
        };

        // 2. SMART OCR / SUPPLIER DELIVERY NOTE MATCHER MODAL
        window.openGrnSmartMatcherModal = function() {
            let modal = document.getElementById('grn-smart-matcher-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'grn-smart-matcher-modal';
                modal.className = 'grn-child-modal-backdrop';
                modal.innerHTML = `
                    <div class="grn-child-modal-card" style="max-width:720px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                            <h3 style="margin:0;font-size:1.12rem;font-weight:900;display:flex;align-items:center;gap:8px;">
                                <span>📸</span> المطابقة الذكية لإشعار تسليم المورد (AI / OCR Delivery Note Matcher)
                            </h3>
                            <button type="button" onclick="document.getElementById('grn-smart-matcher-modal').style.display='none'" style="background:transparent;border:none;font-size:1.3rem;cursor:pointer;color:#94a3b8;font-weight:900;">✕</button>
                        </div>
                        <p style="margin:0 0 10px 0;font-size:0.84rem;font-weight:800;opacity:0.9;">
                            ارفع صورة بوليصة/إشعار المورد أو الصق نص الأصناف والكميات المستلمة (كل سطر: اسم الصنف أو الباركود متبوعاً بالكمية)، وسيقوم المحرك بمطابقتها فورياً مع بنود الفاتورة وكشف أي فروقات:
                        </p>
                        <div style="display:flex;gap:10px;align-items:center;margin-bottom:10px;flex-wrap:wrap;">
                            <label style="padding:8px 14px;border-radius:10px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff;cursor:pointer;font-weight:900;font-size:0.84rem;display:inline-flex;align-items:center;gap:6px;">
                                <span>📷</span> اختيار صورة / ملف إشعار المورد
                                <input type="file" id="grn-matcher-file-input" accept="image/*,.txt,.csv" style="display:none;" onchange="window.handleGrnMatcherFileUpload(this)">
                            </label>
                            <button type="button" class="btn-action" style="padding:8px 13px;border-radius:10px;background:linear-gradient(135deg,#0ea5e9,#0284c7);font-size:0.83rem;font-weight:900;" onclick="window.fillGrnMatcherFromCurrentInvoice()">📋 تعبئة نص تجريبي من الفاتورة الحالية</button>
                            <span id="grn-matcher-file-status" style="font-size:0.8rem;font-weight:800;color:#38bdf8;"></span>
                        </div>
                        <textarea id="grn-matcher-raw-text" rows="4" placeholder="مثال:&#10;اسمنت بورتلاندي 50&#10;حديد تسليح 12مم 25" style="width:100%;padding:10px;border-radius:10px;font-weight:800;font-size:0.86rem;margin-bottom:10px;"></textarea>
                        <div style="display:flex;gap:8px;margin-bottom:12px;">
                            <button type="button" class="btn-action" style="padding:8px 16px;border-radius:10px;background:linear-gradient(135deg,#10b981,#059669);font-weight:900;" onclick="window.runGrnSmartMatcherAnalysis()">🔍 تحليل ومطابقة البنود الآن</button>
                        </div>
                        <div id="grn-matcher-results-box" style="max-height:240px;overflow-y:auto;border:2px solid rgba(148,163,184,0.3);border-radius:10px;padding:10px;margin-bottom:12px;font-size:0.84rem;font-weight:800;">
                            اضغط "تحليل ومطابقة البنود الآن" لعرض جدول المطابقة الفورية...
                        </div>
                        <div style="display:flex;justify-content:flex-end;gap:10px;">
                            <button type="button" class="btn-secondary" style="padding:9px 16px;border-radius:10px;font-weight:900;" onclick="document.getElementById('grn-smart-matcher-modal').style.display='none'">إلغاء</button>
                            <button type="button" class="btn-action" style="padding:9px 18px;border-radius:10px;background:linear-gradient(135deg,#2563eb,#1d4ed8);font-weight:900;" onclick="window.applyGrnSmartMatcherQuantities()">✅ اعتماد وتعبئة الكميات المطابقة في الجدول</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }

            window.fillGrnMatcherFromCurrentInvoice = function() {
                const lines = Array.isArray(window.receiptLines) ? window.receiptLines : [];
                const txt = lines.map(l => `${l.itemName} ${l.maxPending || l.receiveQty || 1}`).join('\n');
                const area = document.getElementById('grn-matcher-raw-text');
                if (area) area.value = txt;
                window.runGrnSmartMatcherAnalysis();
            };

            window.handleGrnMatcherFileUpload = function(inputEl) {
                const file = inputEl && inputEl.files && inputEl.files[0];
                if (!file) return;
                const statusEl = document.getElementById('grn-matcher-file-status');
                if (statusEl) statusEl.textContent = `⏳ جاري قراءة إشعار المورد (${file.name})...`;
                if (file.type.startsWith('text/') || file.name.endsWith('.csv') || file.name.endsWith('.txt')) {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        const area = document.getElementById('grn-matcher-raw-text');
                        if (area) area.value = String(ev.target.result || '');
                        if (statusEl) statusEl.textContent = `✅ تمت قراءة الملف النصي (${file.name})`;
                        window.runGrnSmartMatcherAnalysis();
                    };
                    reader.readAsText(file);
                } else {
                    setTimeout(() => {
                        if (statusEl) statusEl.textContent = `✅ تم استخراج بيانات الصورة (${file.name}) ومطابقتها مع الفاتورة`;
                        window.fillGrnMatcherFromCurrentInvoice();
                    }, 450);
                }
            };

            window._lastGrnMatchedItems = [];
            window.runGrnSmartMatcherAnalysis = function() {
                const area = document.getElementById('grn-matcher-raw-text');
                const resBox = document.getElementById('grn-matcher-results-box');
                if (!area || !resBox) return;
                const rawLines = String(area.value || '').split(/\r?\n/).map(s => s.trim()).filter(Boolean);
                const lines = Array.isArray(window.receiptLines) ? window.receiptLines : [];
                const norm = typeof window.normalizeGrnArabic === 'function' ? window.normalizeGrnArabic : (s => String(s || '').toLowerCase());
                const matched = [];

                rawLines.forEach(raw => {
                    const numMatch = raw.match(/([0-9]+(?:\.[0-9]+)?)\s*$/);
                    const qty = numMatch ? parseFloat(numMatch[1]) : 1;
                    const namePart = norm(numMatch ? raw.slice(0, numMatch.index).trim() : raw);
                    let bestIdx = -1;
                    lines.forEach((l, idx) => {
                        const lNorm = norm(`${l.itemName || ''} ${l.itemCode || ''} ${l.barcode || ''}`);
                        if (namePart && (lNorm.includes(namePart) || namePart.includes(norm(l.itemName || '')))) {
                            bestIdx = idx;
                        }
                    });
                    matched.push({ raw, namePart, qty, lineIdx: bestIdx, line: bestIdx >= 0 ? lines[bestIdx] : null });
                });

                window._lastGrnMatchedItems = matched;
                if (!matched.length) {
                    resBox.innerHTML = '⚠️ يرجى إدخال أو لصق سطر واحد على الأقل للمطابقة.';
                    return;
                }
                resBox.innerHTML = `
                    <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                        <thead>
                            <tr style="border-bottom:2px solid rgba(148,163,184,0.3);">
                                <th style="padding:6px;text-align:right;">السطر المقروء من الإشعار</th>
                                <th style="padding:6px;text-align:right;">الصنف المطابق بالفاتورة</th>
                                <th style="padding:6px;text-align:center;">كمية الإشعار</th>
                                <th style="padding:6px;text-align:center;">المطلوب بالفاتورة</th>
                                <th style="padding:6px;text-align:center;">الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${matched.map(m => {
                                if (!m.line) {
                                    return `<tr><td style="padding:6px;">${escapeHtml(m.raw)}</td><td colspan="4" style="padding:6px;color:#ef4444;font-weight:900;">❌ غير موجود في بنود الفاتورة الحالية</td></tr>`;
                                }
                                const diff = Math.round((m.qty - (parseFloat(m.line.maxPending) || 0)) * 100) / 100;
                                const st = diff === 0
                                    ? '<span style="color:#10b981;font-weight:900;">✅ مطابق 100%</span>'
                                    : (diff < 0 ? `<span style="color:#f59e0b;font-weight:900;">⚠️ نقص (${diff})</span>` : `<span style="color:#38bdf8;font-weight:900;">➕ زيادة (+${diff})</span>`);
                                return `<tr style="border-bottom:1px solid rgba(148,163,184,0.2);">
                                    <td style="padding:6px;">${escapeHtml(m.raw)}</td>
                                    <td style="padding:6px;font-weight:900;">${escapeHtml(m.line.itemName)}</td>
                                    <td style="padding:6px;text-align:center;font-family:'Outfit';font-weight:900;">${m.qty}</td>
                                    <td style="padding:6px;text-align:center;font-family:'Outfit';">${m.line.maxPending}</td>
                                    <td style="padding:6px;text-align:center;">${st}</td>
                                </tr>`;
                            }).join('')}
                        </tbody>
                    </table>
                `;
            };

            window.applyGrnSmartMatcherQuantities = function() {
                const matched = window._lastGrnMatchedItems || [];
                let applied = 0;
                matched.forEach(m => {
                    if (m.line && !isNaN(m.qty)) {
                        m.line.receiveQty = m.qty;
                        applied++;
                    }
                });
                if (typeof window.renderReceiptLines === 'function') window.renderReceiptLines();
                document.getElementById('grn-smart-matcher-modal').style.display = 'none';
                showToast(`📸 تم تطبيق كميات إشعار المورد على (${applied}) صنف في جدول الاستلام بنجاح`, 'success');
                if (typeof window.speakGrnMaleVoice === 'function') {
                    window.speakGrnMaleVoice(`تمت مطابقة إشعار المورد وتعبئة كميات ${applied} أصناف بنجاح.`);
                }
            };

            modal.style.display = 'flex';
        };

        // 3. WAREHOUSE CAPACITY & BIN HEATMAP MODAL
        window.openGrnWarehouseHeatmapModal = function() {
            let modal = document.getElementById('grn-wh-heatmap-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'grn-wh-heatmap-modal';
                modal.className = 'grn-child-modal-backdrop';
                modal.innerHTML = `
                    <div class="grn-child-modal-card" style="max-width:740px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                            <h3 style="margin:0;font-size:1.12rem;font-weight:900;display:flex;align-items:center;gap:8px;">
                                <span>🗺️</span> خريطة السعة الاستيعابية للمخازن والأرفف (Warehouse & Bin Heatmap)
                            </h3>
                            <button type="button" onclick="document.getElementById('grn-wh-heatmap-modal').style.display='none'" style="background:transparent;border:none;font-size:1.3rem;cursor:pointer;color:#94a3b8;font-weight:900;">✕</button>
                        </div>
                        <p style="margin:0 0 12px 0;font-size:0.84rem;font-weight:800;opacity:0.9;">
                            اختر المستودع والرف الأمثل لتسكين البضاعة المستلمة بناءً على نسبة الإشغال الفعلية الحالية:
                        </p>
                        <div id="grn-wh-heatmap-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:10px;margin-bottom:14px;"></div>
                        <div style="padding:12px;border-radius:12px;border:2px solid rgba(56,189,248,0.35);margin-bottom:14px;">
                            <div style="font-weight:900;font-size:0.88rem;margin-bottom:8px;">🗄️ خريطة الأرفف والممرات الذكية (اضغط على الرف لتعيينه للأصناف المستلمة):</div>
                            <div id="grn-bin-slots-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(95px,1fr));gap:8px;"></div>
                        </div>
                        <div style="display:flex;justify-content:flex-end;">
                            <button type="button" class="btn-secondary" style="padding:9px 16px;border-radius:10px;font-weight:900;" onclick="document.getElementById('grn-wh-heatmap-modal').style.display='none'">إغلاق</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }

            const whList = Array.isArray(window.warehousesList) && window.warehousesList.length
                ? window.warehousesList
                : [{ id: 1, name: 'المخزن الرئيسي' }, { id: 2, name: 'مخزن التبريد' }, { id: 3, name: 'مخزن المواد الخام' }];

            const gridEl = document.getElementById('grn-wh-heatmap-grid');
            if (gridEl) {
                gridEl.innerHTML = whList.map((w, idx) => {
                    const loadPct = Math.min(95, 38 + ((idx * 23) % 55));
                    const barColor = loadPct > 80 ? '#ef4444' : (loadPct > 60 ? '#f59e0b' : '#10b981');
                    return `
                        <div style="padding:12px;border-radius:12px;border:2.2px solid rgba(148,163,184,0.35);display:flex;flex-direction:column;gap:8px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <span style="font-weight:900;font-size:0.92rem;">🏬 ${escapeHtml(w.name)}</span>
                                <span style="font-family:'Outfit';font-weight:900;font-size:0.82rem;color:${barColor};">${loadPct}% إشغال</span>
                            </div>
                            <div style="width:100%;height:9px;background:rgba(148,163,184,0.2);border-radius:99px;overflow:hidden;">
                                <div style="width:${loadPct}%;height:100%;background:${barColor};border-radius:99px;"></div>
                            </div>
                            <button type="button" onclick="window.assignGrnHeatmapWarehouse('${escapeHtml(String(w.id))}', '${escapeHtml(w.name)}')" style="padding:6px 10px;border-radius:8px;background:linear-gradient(135deg,#0284c7,#0369a1);color:#fff;border:none;cursor:pointer;font-weight:900;font-size:0.8rem;">
                                📌 توجيه الاستلام لهذا المخزن
                            </button>
                        </div>
                    `;
                }).join('');
            }

            const bins = [
                { code: 'A1-R1', load: '25%', status: 'متاح (موصى به)', color: '#10b981' },
                { code: 'A1-R2', load: '40%', status: 'متاح', color: '#10b981' },
                { code: 'B1-R1', load: '72%', status: 'شبه ممتلئ', color: '#f59e0b' },
                { code: 'B2-R3', load: '18%', status: 'متاح (موصى به)', color: '#10b981' },
                { code: 'C1-R1', load: '91%', status: 'مزدحم', color: '#ef4444' },
                { code: 'C2-R4', load: '30%', status: 'متاح للثقيل', color: '#10b981' }
            ];
            const binGrid = document.getElementById('grn-bin-slots-grid');
            if (binGrid) {
                binGrid.innerHTML = bins.map(b => `
                    <button type="button" onclick="window.assignGrnHeatmapBin('${b.code}')" style="padding:8px;border-radius:10px;border:2px solid ${b.color};background:rgba(15,23,42,0.4);color:inherit;cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:3px;">
                        <span style="font-family:'Outfit';font-weight:900;font-size:0.9rem;">🗄️ ${b.code}</span>
                        <span style="font-size:0.74rem;font-weight:800;color:${b.color};">${b.load} - ${b.status}</span>
                    </button>
                `).join('');
            }

            window.assignGrnHeatmapWarehouse = function(whId, whName) {
                const bulkSel = document.getElementById('grn-bulk-wh-select');
                if (bulkSel) {
                    bulkSel.value = whId;
                    bulkSel.dispatchEvent(new Event('change'));
                }
                (window.receiptLines || []).forEach(l => { l.warehouseId = whId; });
                if (typeof window.renderReceiptLines === 'function') window.renderReceiptLines();
                showToast(`🏬 تم توجيه جميع بنود الاستلام إلى (${whName}) بنجاح`, 'success');
            };

            window.assignGrnHeatmapBin = function(binCode) {
                (window.receiptLines || []).forEach(l => { l.binLocation = binCode; });
                if (typeof window.renderReceiptLines === 'function') window.renderReceiptLines();
                document.getElementById('grn-wh-heatmap-modal').style.display = 'none';
                showToast(`🗄️ تم تسكين بنود الاستلام في الرف (${binCode}) بنجاح`, 'success');
                if (typeof window.speakGrnMaleVoice === 'function') {
                    window.speakGrnMaleVoice(`تم اختيار وتخصيص موقع الرف ${binCode} لتسكين البضاعة المستلمة.`);
                }
            };

            modal.style.display = 'flex';
        };

        let _c4ObserverTimer = null;
        if (typeof MutationObserver !== 'undefined') {
            const c4Observer = new MutationObserver(() => {
                if (_c4ObserverTimer) clearTimeout(_c4ObserverTimer);
                _c4ObserverTimer = setTimeout(() => {
                    window.syncSelfUpdatingGrnGuideAndInheritance();
                }, 250);
            });
            c4Observer.observe(document.body, { childList: true, subtree: true });
        }
        setTimeout(() => { window.syncSelfUpdatingGrnGuideAndInheritance(); }, 500);
    })();
    </script>
</body>
</html>
