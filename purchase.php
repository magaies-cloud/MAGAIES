<?php $page_title = 'المشتريات'; ?>
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
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استلام بضاعة | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <link rel="stylesheet" href="css/sales-invoice.css">
    <link rel="stylesheet" href="css/print-invoice.css">
    <style>
        table.pos-grid th, table.pos-grid td { padding: 4px 4px !important; }
        table.pos-grid .g-input, table.pos-grid .g-select { padding: 4px !important; margin: 0 !important; }

        :root {
            --bg-color: #050308;
            --velvet-bg: #050308;
            --velvet-panel: rgba(18, 15, 25, 0.65);
            --velvet-panel-hover: rgba(28, 24, 38, 0.85);
            --gold-primary: #38bdf8;
            --gold-glow: rgba(56, 189, 248, 0.4);
            --gold-border: rgba(255, 255, 255, 0.08);
            --text-main: #f3f4f6;
            --text-dim: #9ca3af;
            --danger-color: #ef4444;
            --success-color: #10b981;
            --primary-accent: #6366f1;
            --theme-pink: #ec4899;
            --theme-blue: #3b82f6;
        }

        html {
            font-size: 18px;
        }

        .ambient-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            overflow: hidden; z-index: -1; pointer-events: none;
            background: radial-gradient(circle at 50% 50%, #0c0812 0%, #000000 100%);
        }
        .orb { position: absolute; border-radius: 50%; filter: blur(120px); opacity: 0.15; animation: floatOrb 20s infinite alternate ease-in-out; }
        .orb-1 { width: 500px; height: 500px; background: var(--primary-accent); top: -5%; right: -5%; }
        .orb-2 { width: 450px; height: 450px; background: var(--theme-pink); bottom: -10%; left: -5%; animation-delay: -7s; }
        .orb-3 { width: 600px; height: 600px; background: var(--theme-blue); top: 30%; left: 30%; animation-duration: 25s; opacity: 0.1; }
        @keyframes floatOrb { 0% { transform: translate(0, 0) scale(1); } 33% { transform: translate(30px, -50px) scale(1.1); } 66% { transform: translate(-30px, 30px) scale(0.9); } 100% { transform: translate(0, 0) scale(1); } }



        .header-brand { display: flex; align-items: center; gap: 12px; }
        .brand-icon { width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #38bdf8, #818cf8); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; box-shadow: 0 6px 15px rgba(56, 189, 248, 0.4), inset 0 2px 5px rgba(255,255,255,0.3); border: 1px solid rgba(255,255,255,0.2); }
        .brand-text h1 { margin: 0; font-size: 1.1rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; text-shadow: 0 2px 5px rgba(255,255,255,0.1); }
        .brand-text p { margin: 0; font-size: 0.75rem; color: #94a3b8; font-weight: 500; }
        .header-center { text-align: center; }
        .header-center h2 { margin: 0; font-size: 1.2rem; font-weight: 900; background: linear-gradient(to right, #fff, #cbd5e1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 1px; }
        .header-controls { display: flex; align-items: center; gap: 12px; }
        
        .clock-container { display: flex; flex-direction: column; align-items: center; background: rgba(15,23,42,0.6); padding: 6px 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); min-width: 100px; }
        .clock-time { color: #38bdf8; font-size: 1rem; font-weight: 900; font-variant-numeric: tabular-nums; letter-spacing: 1px; text-shadow: 0 0 8px rgba(56,189,248,0.5); font-family: 'Outfit', sans-serif;}
        .clock-date { color: var(--text-secondary); font-size: 0.65rem; margin-top: 1px; }

        .icon-btn { background: rgba(255,255,255,0.03); border: 1px solid var(--gold-border); width: 36px; height: 36px; border-radius: 10px; color: #fff; font-size: 1rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; position: relative; }
        .icon-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); box-shadow: 0 5px 10px rgba(0,0,0,0.3); }

        .user-info { display: flex; align-items: center; gap: 8px; background: rgba(15,23,42,0.6); padding: 6px 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); }
        
        .dash-main { width: 100%; max-width: 100%; margin: 0; padding: 0px 15px 20px 15px; box-sizing: border-box; display: flex; flex-direction: column; gap: 15px; flex: 1; animation: fadeIn 0.8s ease-out forwards; }

        body {
            background-color: var(--bg-color);
            margin: 0; padding: 0; min-height: 100vh;
            display: flex; flex-direction: column; overflow-x: hidden;
            font-family: 'Inter', 'Tajawal', sans-serif; color: var(--text-main);
        }
        
        body.has-sidebar { padding-right: 0 !important; } /* Override default if needed */

        /* Toast Notifications */
        .toast-container { position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
        .toast {
            padding:12px 20px; border-radius:12px; font-weight:700; font-size:0.85rem;
            display:flex; align-items:center; gap:8px; pointer-events:auto;
            backdrop-filter:blur(20px); box-shadow:0 12px 40px rgba(0,0,0,0.5);
            animation:toastIn 0.4s cubic-bezier(0.16,1,0.3,1) both;
            font-family:'Cairo',sans-serif; max-width:90vw;
        }
        .toast.success { background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.4); color:#34d399; }
        .toast.error { background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#f87171; }
        .toast.warning { background:rgba(245,158,11,0.15); border:1px solid rgba(245,158,11,0.4); color:#fbbf24; }
        .toast.hide { animation:toastOut 0.4s forwards; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOut { to { opacity:0; transform:translateY(-20px) scale(0.95); } }

        /* Custom Dropdown */
        .custom-dropdown { position: relative; width: 100%; display: flex; gap: 6px; }
        .custom-dropdown-input-wrap { position: relative; flex: 1; }
        .custom-dropdown-list {
            position: absolute; top: 100%; left: 0; right: 0; margin-top: 4px;
            max-height: 280px; overflow-y: auto;
            background: rgba(30, 41, 59, 0.96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(251, 191, 36, 0.25);
            border-radius: 10px; box-shadow: 0 15px 50px rgba(0,0,0,0.6);
            z-index: 99999; display: none; flex-direction: column;
            min-width: 320px;
            scrollbar-width: thin;
            scrollbar-color: rgba(251, 191, 36, 0.3) transparent;
        }
        .custom-dropdown-list::-webkit-scrollbar {
            width: 6px;
        }
        .custom-dropdown-list::-webkit-scrollbar-thumb {
            background: rgba(251, 191, 36, 0.3);
            border-radius: 4px;
        }
        .custom-dropdown-list.show { display: flex; }
        td:has(.custom-dropdown-list.show),
        .m-card-full:has(.custom-dropdown-list.show),
        .custom-dropdown-input-wrap:has(.custom-dropdown-list.show) { z-index: 99999 !important; }

        .custom-dropdown-item {
            padding: 11px 15px; cursor: pointer; font-size: 0.9rem; font-weight: 600;
            color: rgba(241, 245, 249, 0.9); border-bottom: 1px solid rgba(255,255,255,0.03);
            transition: all 0.2s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .custom-dropdown-item:last-child {
            border-bottom: none;
        }
        .custom-dropdown-item:hover, .custom-dropdown-item.selected {
            background: rgba(251,191,36,0.12); color: var(--gold-primary);
            padding-right: 18px; /* subtle sliding micro-animation */
        }
        .custom-dropdown-empty { padding: 10px 12px; font-size: 0.8rem; color: var(--text-dim); text-align: center; }

        /* TOP NAV (Minimalist) */
        .velvet-nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 20px; background: var(--velvet-panel);
            border-bottom: 1px solid var(--gold-border);
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            height: 60px; box-sizing: border-box;
        }
        .velvet-nav-title {
            display: flex; align-items: center; gap: 12px;
        }
        .velvet-nav-title .icon-box {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, var(--gold-primary), #b45309);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; box-shadow: 0 0 15px var(--gold-glow);
            color: #fff;
        }
        .velvet-nav-title h2 { margin: 0; font-size: 1.2rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; }

        /* TABS (Direct, Split, Delivery, Proforma) */
        .velvet-tabs { display: flex; gap: 8px; }
        .velvet-tab {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            color: var(--text-dim); padding: 6px 14px; border-radius: 8px; cursor: pointer;
            font-weight: 600; font-size: 0.8rem; transition: 0.3s; display: flex; align-items: center; gap: 6px;
        }
        .velvet-tab:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .velvet-tab.active {
            background: rgba(251,191,36,0.15); border-color: var(--gold-primary);
            color: var(--gold-primary); box-shadow: inset 0 0 10px rgba(251,191,36,0.1);
        }

        .nav-controls { display: flex; align-items: center; gap: 12px; }
        .btn-icon-nav {
            width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1); color: var(--text-main);
            display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s;
        }
        .btn-icon-nav:hover { background: rgba(251,191,36,0.1); color: var(--gold-primary); border-color: var(--gold-border); }

        /* SPLIT SCREEN LAYOUT */
        .split-layout {
            display: flex; height: calc(100vh - 60px); width: 100%;
        }

        /* RIGHT PANEL: SUPPLIER & TOTALS (320px width) */
        .split-right {
            width: 340px; min-width: 340px; background: transparent;
            display: flex; flex-direction: column;
            z-index: 10;
        }
        .split-right-scroll {
            flex: 1; overflow-y: auto; padding: 15px;
            display: flex; flex-direction: column; gap: 15px;
        }
        .split-right-scroll::-webkit-scrollbar { width: 4px; }
        .split-right-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); }

        /* Neumorphic Cards inside Right Panel */
        .neo-card {
            background: rgba(18, 15, 25, 0.65);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--gold-border);
            border-radius: 18px; padding: 15px; margin-bottom: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3), inset 0 1px 1px rgba(255,255,255,0.03);
        }
        .neo-card-title {
            font-size: 0.75rem; font-weight: 800; color: var(--gold-primary);
            text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px;
            display: flex; justify-content: space-between; align-items: center;
        }

        /* Forms in Right Panel */
        .v-form-group { margin-bottom: 10px; }
        .v-label { display: block; font-size: 0.65rem; color: var(--text-dim); margin-bottom: 4px; font-weight: 600; }
        .v-input {
            width: 100%; padding: 8px 10px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 6px; color: #fff; font-size: 0.8rem; outline: none; transition: 0.2s;
            box-sizing: border-box; font-family: 'Inter', sans-serif;
        }
        .v-input:focus { border-color: var(--gold-primary); box-shadow: 0 0 0 2px rgba(251,191,36,0.15); }
        .btn-icon-add { padding: 0 10px; border-radius: 6px; background: rgba(251,191,36,0.1); border: 1px solid var(--gold-border); color: var(--gold-primary); cursor: pointer; }

        /* Fin Strip Grid */
        .fin-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px;
        }
        .fin-box {
            background: rgba(0,0,0,0.4); border-radius: 8px; padding: 8px;
            border: 1px solid rgba(255,255,255,0.04); text-align: center;
        }
        .fin-box.full { grid-column: span 2; }
        .fin-box.avail { background: linear-gradient(135deg, rgba(251,191,36,0.15), rgba(180,83,9,0.1)); border-color: var(--gold-border); box-shadow: 0 0 15px rgba(251,191,36,0.05); }
        .fin-box-lbl { font-size: 0.6rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 2px; }
        .fin-box-val { font-size: 0.85rem; color: #fff; font-weight: 900; direction: ltr; }
        .fin-box.avail .fin-box-val { color: var(--gold-primary); font-size: 1rem; text-shadow: 0 0 10px rgba(251,191,36,0.4); }
        .fin-box.warn .fin-box-val { color: var(--danger-color); }

        /* Totals Area */
        .tot-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 6px 0; border-bottom: 1px dashed rgba(255,255,255,0.05);
        }
        .tot-row:last-child { border-bottom: none; }
        .tot-lbl { font-size: 0.7rem; color: var(--text-dim); font-weight: 600; }
        .tot-val { font-size: 0.85rem; font-weight: 800; direction: ltr; }
        .tot-input { width: 70px; padding: 2px 6px; font-size: 0.75rem; text-align: left; }
        .tot-grand {
            margin-top: 10px; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3);
            border-radius: 8px; padding: 12px; display: flex; justify-content: space-between; align-items: center;
        }
        .tot-grand .lbl { color: var(--success-color); font-weight: 800; font-size: 0.8rem; }
        .tot-grand .val { color: var(--success-color); font-weight: 900; font-size: 1.2rem; direction: ltr; text-shadow: 0 0 15px rgba(16,185,129,0.4); }

        /* Action Footer in Right Panel */
        .right-footer {
            padding: 15px; border-top: 1px solid rgba(255,255,255,0.05);
            display: flex; flex-direction: column; gap: 8px; background: transparent;
        }
        .btn-action {
            width: 100%; padding: 12px; border-radius: 8px; font-weight: 800; font-size: 0.9rem;
            cursor: pointer; border: none; transition: 0.3s;
        }
        .btn-submit { background: linear-gradient(135deg, #38bdf8, #818cf8); color: #fff; box-shadow: 0 4px 15px var(--gold-glow); }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px var(--gold-glow); }
        .btn-draft { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; }
        .btn-draft:hover { background: rgba(255,255,255,0.1); }
        .btn-delete { background: rgba(239,68,68,0.1); color: var(--danger-color); border: 1px solid rgba(239,68,68,0.2); }
        .btn-delete:hover { background: rgba(239,68,68,0.2); }

        /* LEFT PANEL: ITEMS & SEARCH (Flex 1) */
        .split-left {
            flex: 1; display: flex; flex-direction: column; padding: 0 15px 15px 15px;
            overflow: hidden; background: transparent;
        }

        /* Toolbar above table */
        .items-toolbar {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;
            background: rgba(18, 15, 25, 0.65); padding: 12px 15px; border-radius: 18px;
            border: 1px solid var(--gold-border); box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
        }
        .quick-search {
            flex: 1; max-width: 400px; position: relative;
        }
        .quick-search input {
            width: 100%; padding: 10px 15px; background: rgba(0,0,0,0.4); border: 1px solid var(--gold-border);
            border-radius: 8px; color: #fff; font-size: 0.9rem; outline: none; transition: 0.3s; box-sizing: border-box;
        }
        .quick-search input:focus { border-color: var(--gold-primary); box-shadow: 0 0 15px rgba(251,191,36,0.1); }

        /* Table Area */
        .table-container {
            background: rgba(18, 15, 25, 0.65); border-radius: 18px;
            border: 1px solid var(--gold-border); box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            overflow: visible; display: flex; flex-direction: column;
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
        }
        .grid-scroll-wrap { overflow-y: visible; overflow-x: auto; }
        .grid-scroll-wrap::-webkit-scrollbar { width: 6px; height: 6px; }
        .grid-scroll-wrap::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 6px; }
        
        .pos-grid { width: 100%; border-collapse: separate; border-spacing: 0 8px; min-width: 900px; }
        .pos-grid thead th {
            background: transparent; color: #94a3b8; font-size: 0.85rem; font-weight: 800;
            padding: 10px 15px; text-align: center; position: sticky; top: 0; z-index: 5;
            border-bottom: 2px solid rgba(255,255,255,0.05); text-transform: uppercase; letter-spacing: 0.5px;
        }
        .pos-grid tbody tr {
            background: rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        .pos-grid tbody tr:hover {
            background: rgba(255,255,255,0.05); transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .pos-grid tbody td {
            padding: 12px 10px; border-bottom: 1px solid rgba(255,255,255,0.02);
            border-top: 1px solid rgba(255,255,255,0.02);
            font-size: 0.85rem; vertical-align: middle;
        }
        .pos-grid tbody td:first-child { border-radius: 12px 0 0 12px; border-right: 1px solid rgba(255,255,255,0.02); }
        .pos-grid tbody td:last-child { border-radius: 0 12px 12px 0; border-left: 1px solid rgba(255,255,255,0.02); }
        
        .g-input, .g-select {
            width: 100%; padding: 10px 12px; background: rgba(15,23,42,0.4); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px; color: #f8fafc; text-align: center; outline: none; font-size: 0.9rem; font-family: 'Inter', sans-serif;
            transition: all 0.3s ease; box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
        }
        .g-input:focus, .g-select:focus { 
            border-color: #38bdf8; background: rgba(15,23,42,0.8); box-shadow: 0 0 0 2px rgba(56,189,248,0.2), inset 0 2px 4px rgba(0,0,0,0.2); 
        }
        .g-input[type="text"] { text-align: right; }
        
        .g-readonly {
            display: inline-block; padding: 8px 12px; background: rgba(255,255,255,0.05); border-radius: 8px; font-weight: 800; text-align: center; width: 100%; box-sizing: border-box;
        }
        
        .del-btn {
            width: 32px; height: 32px; border-radius: 8px; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2);
            color: var(--danger-color); cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center;
        }
        .del-btn:hover { background: var(--danger-color); color: #fff; box-shadow: 0 4px 10px rgba(239,68,68,0.4); transform: scale(1.1); }

        .empty-state {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            height: 100%; color: var(--text-dim); padding: 40px;
        }
        .empty-state .icon { font-size: 3rem; margin-bottom: 10px; opacity: 0.5; filter: drop-shadow(0 0 10px var(--gold-glow)); }

        /* Notes Bar at Bottom of Left Panel */
        .notes-bar {
            margin-top: 15px; background: rgba(18, 15, 25, 0.65); padding: 15px; border-radius: 18px;
            border: 1px solid var(--gold-border); box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
        }
        .notes-bar textarea {
            width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05);
            border-radius: 8px; color: #fff; padding: 10px; resize: vertical; min-height: 40px; outline: none;
            box-sizing: border-box; font-family: 'Inter', sans-serif;
        }
        .notes-bar textarea:focus { border-color: var(--gold-primary); }

        /* Modal specific to theme */
        .modal-card { background: var(--velvet-panel); border: 1px solid var(--gold-border); box-shadow: 0 20px 60px rgba(0,0,0,0.8); }
        .modal-header h3 { color: var(--gold-primary); }
        .modal-header { border-bottom-color: rgba(255,255,255,0.05); }
        .modal-footer { border-top-color: rgba(255,255,255,0.05); }

        @media (max-width: 1024px) {
            .split-layout { flex-direction: column; overflow-y: auto; height: auto; }
            .split-right { width: 100%; min-width: 100%; border-left: none; border-bottom: 1px solid var(--gold-border); }
            .split-left { overflow: visible; }
            .table-container { min-height: 400px; }
        }
    
        .fin-strip-inline.visible {
            flex: 0 0 43% !important;
            width: auto !important;
            opacity: 1 !important;
            min-width: 400px !important;
            margin-left: 5px;
        }
\n
    #purchase-top-container #totals-calc-card.hidden-totals {
        flex: 0 0 0px !important;
        min-width: 0px !important;
        width: 0px !important;
        opacity: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin: 0 !important;
        border: none !important;
        transform: scaleX(0);
    }


    /* ================= 2027 LEDGER BUTTON DESIGN ================= */
    @keyframes neonPulse {
        0% { box-shadow: 0 0 5px #0ea5e9, 0 0 10px #0ea5e9, inset 0 0 5px rgba(14, 165, 233, 0.5); border-color: rgba(14, 165, 233, 0.8); }
        50% { box-shadow: 0 0 15px #38bdf8, 0 0 25px #38bdf8, inset 0 0 10px rgba(56, 189, 248, 0.8); border-color: #38bdf8; }
        100% { box-shadow: 0 0 5px #0ea5e9, 0 0 10px #0ea5e9, inset 0 0 5px rgba(14, 165, 233, 0.5); border-color: rgba(14, 165, 233, 0.8); }
    }

    @keyframes subtleShake {
        0% { transform: translateY(-50%) rotate(0deg); }
        25% { transform: translateY(-50%) rotate(0.8deg); }
        50% { transform: translateY(-50%) rotate(0deg); }
        75% { transform: translateY(-50%) rotate(-0.8deg); }
        100% { transform: translateY(-50%) rotate(0deg); }
    }

    .ledger-btn-2027 {
        position: absolute;
        left: 6px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 5;
        font-size: 0.8rem;
        font-weight: 800;
        padding: 0 10px;
        height: 26px;
        display: none; /* Displayed via JS when customer selected */
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(2, 132, 199, 0.3));
        color: #e0f2fe;
        border: 1px solid rgba(14, 165, 233, 0.5);
        border-radius: 6px;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        animation: neonPulse 2s infinite alternate, subtleShake 4s infinite ease-in-out;
        text-shadow: 0 0 4px rgba(255,255,255,0.4);
        cursor: pointer;
        overflow: hidden;
        font-family: 'Tajawal', sans-serif;
    }

    .ledger-btn-2027::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.4) 0%, transparent 50%);
        opacity: 0;
        transform: scale(0.5);
        transition: all 0.4s ease;
        pointer-events: none;
    }

    .ledger-btn-2027:hover::before {
        opacity: 0.3;
        transform: scale(1);
    }

    .ledger-btn-2027:hover {
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.4), rgba(2, 132, 199, 0.7));
        color: #ffffff;
        text-shadow: 0 0 8px #ffffff;
        box-shadow: 0 0 20px #0ea5e9, 0 0 30px #38bdf8, inset 0 0 15px rgba(56, 189, 248, 0.9);
        animation: subtleShake 1s infinite ease-in-out; /* Shake faster on hover */
    }

    :root.light-theme .ledger-btn-2027 {
        color: #0c4a6e;
        text-shadow: none;
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.2), rgba(14, 165, 233, 0.4));
        border-color: #0ea5e9;
        animation: subtleShake 4s infinite ease-in-out; /* Removed pulse from light theme to avoid washed out look */
        box-shadow: 0 2px 8px rgba(14, 165, 233, 0.4);
    }
    :root.light-theme .ledger-btn-2027:hover {
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.8), rgba(2, 132, 199, 1));
        color: white;
        box-shadow: 0 4px 15px rgba(14, 165, 233, 0.6);
    }
    
</style>

    <style>
        .hub-action-card {
            border-radius: 12px;
            padding: 12px 15px;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            gap: 15px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            height: 80px; /* Reduced height to ~50% of the previous tall card */
        }
        .hub-action-card::after {
            content: "";
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.05) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-25deg);
            transition: all 0.6s ease;
        }
        .hub-action-card:hover::after {
            left: 200%;
        }
        .hub-action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.4), inset 0 0 0 1px rgba(255, 255, 255, 0.15);
        }
        .hub-action-card .icon {
            font-size: 1.8rem;
            margin-bottom: 0;
            padding: 8px;
            border-radius: 10px;
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
        }
        .hub-action-card:hover .icon {
            transform: scale(1.1) rotate(-5deg);
        }
        .hub-action-card .text-content {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            text-align: right;
        }
        .hub-action-card .title {
            font-size: 1.05rem;
            font-weight: 800;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }
        .hub-action-card .subtitle {
            font-size: 0.7rem;
            font-weight: 600;
            text-align: right;
            margin: 0;
        }

        /* ── Theme-aware Card Styling (Dark Mode Defaults with Magical Glassy Borders & Glow) ── */
        .card-orange {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.25), rgba(180, 83, 9, 0.08)) !important;
            border: 1px solid rgba(245, 158, 11, 0.3) !important; /* Translucent orange border */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.1) !important;
        }
        .card-orange .icon { color: #f59e0b; background: rgba(245, 158, 11, 0.08); }
        .card-orange .title { color: #f59e0b !important; }
        .card-orange .subtitle { color: rgba(255, 255, 255, 0.7) !important; }
        .card-orange:hover {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.4), rgba(180, 83, 9, 0.15)) !important;
            border-color: rgba(245, 158, 11, 0.7) !important; /* Glowing orange glass border */
            box-shadow: 0 12px 40px 0 rgba(245, 158, 11, 0.35), inset 0 0 18px rgba(245, 158, 11, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
        }

        .card-blue {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.25), rgba(3, 105, 161, 0.08)) !important;
            border: 1px solid rgba(14, 165, 233, 0.3) !important; /* Translucent blue border */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.1) !important;
        }
        .card-blue .icon { color: #38bdf8; background: rgba(56, 189, 248, 0.08); }
        .card-blue .title { color: #38bdf8 !important; }
        .card-blue .subtitle { color: rgba(255, 255, 255, 0.7) !important; }
        .card-blue:hover {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.4), rgba(3, 105, 161, 0.15)) !important;
            border-color: rgba(14, 165, 233, 0.7) !important; /* Glowing blue glass border */
            box-shadow: 0 12px 40px 0 rgba(14, 165, 233, 0.35), inset 0 0 18px rgba(14, 165, 233, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
        }

        .card-pink {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.25), rgba(190, 24, 74, 0.08)) !important;
            border: 1px solid rgba(236, 72, 153, 0.3) !important; /* Translucent pink border */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.1) !important;
        }
        .card-pink .icon { color: #f43f5e; background: rgba(244, 63, 94, 0.08); }
        .card-pink .title { color: #f43f5e !important; }
        .card-pink .subtitle { color: rgba(255, 255, 255, 0.7) !important; }
        .card-pink:hover {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.4), rgba(190, 24, 74, 0.15)) !important;
            border-color: rgba(236, 72, 153, 0.7) !important; /* Glowing pink glass border */
            box-shadow: 0 12px 40px 0 rgba(236, 72, 153, 0.35), inset 0 0 18px rgba(236, 72, 153, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
        }

        .card-purple {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.25), rgba(109, 40, 217, 0.08)) !important;
            border: 1px solid rgba(168, 85, 247, 0.3) !important; /* Translucent purple border */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.1) !important;
        }
        .card-purple .icon { color: #a855f7; background: rgba(168, 85, 247, 0.08); }
        .card-purple .title { color: #a855f7 !important; }
        .card-purple .subtitle { color: rgba(255, 255, 255, 0.7) !important; }
        .card-purple:hover {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.4), rgba(109, 40, 217, 0.15)) !important;
            border-color: rgba(168, 85, 247, 0.7) !important; /* Glowing purple glass border */
            box-shadow: 0 12px 40px 0 rgba(168, 85, 247, 0.35), inset 0 0 18px rgba(168, 85, 247, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
        }

        /* ── Light Mode overrides for Hub Cards (Light Pastel Glassy Gradients, Vibrant Colored Text & Magical Hover Border Glows) ── */
        :root.light-theme .card-orange {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.16), rgba(251, 191, 36, 0.05)) !important;
            border: 1px solid rgba(245, 158, 11, 0.28) !important; /* Translucent orange glassy border */
            box-shadow: 0 8px 32px 0 rgba(245, 158, 11, 0.06), inset 0 1px 0 0 rgba(255, 255, 255, 0.6) !important;
        }
        :root.light-theme .card-orange .icon { color: #d97706 !important; background: rgba(245, 158, 11, 0.08) !important; }
        :root.light-theme .card-orange .title { color: #d97706 !important; }
        :root.light-theme .card-orange .subtitle { color: rgba(217, 119, 6, 0.8) !important; }
        :root.light-theme .card-orange:hover {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.3), rgba(251, 191, 36, 0.1)) !important;
            border-color: rgba(245, 158, 11, 0.6) !important; /* Glowing orange glass border */
            box-shadow: 0 12px 40px 0 rgba(245, 158, 11, 0.22), inset 0 0 15px rgba(245, 158, 11, 0.35), inset 0 1px 0 0 rgba(255, 255, 255, 0.75) !important;
        }

        :root.light-theme .card-blue {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.16), rgba(56, 189, 248, 0.05)) !important;
            border: 1px solid rgba(14, 165, 233, 0.28) !important; /* Translucent blue glassy border */
            box-shadow: 0 8px 32px 0 rgba(14, 165, 233, 0.06), inset 0 1px 0 0 rgba(255, 255, 255, 0.6) !important;
        }
        :root.light-theme .card-blue .icon { color: #0284c7 !important; background: rgba(14, 165, 233, 0.08) !important; }
        :root.light-theme .card-blue .title { color: #0284c7 !important; }
        :root.light-theme .card-blue .subtitle { color: rgba(2, 132, 199, 0.8) !important; }
        :root.light-theme .card-blue:hover {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.3), rgba(56, 189, 248, 0.1)) !important;
            border-color: rgba(14, 165, 233, 0.6) !important; /* Glowing blue glass border */
            box-shadow: 0 12px 40px 0 rgba(14, 165, 233, 0.22), inset 0 0 15px rgba(14, 165, 233, 0.35), inset 0 1px 0 0 rgba(255, 255, 255, 0.75) !important;
        }

        :root.light-theme .card-pink {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.16), rgba(244, 114, 182, 0.05)) !important;
            border: 1px solid rgba(236, 72, 153, 0.28) !important; /* Translucent pink glassy border */
            box-shadow: 0 8px 32px 0 rgba(236, 72, 153, 0.06), inset 0 1px 0 0 rgba(255, 255, 255, 0.6) !important;
        }
        :root.light-theme .card-pink .icon { color: #db2777 !important; background: rgba(236, 72, 153, 0.08) !important; }
        :root.light-theme .card-pink .title { color: #db2777 !important; }
        :root.light-theme .card-pink .subtitle { color: rgba(219, 39, 119, 0.8) !important; }
        :root.light-theme .card-pink:hover {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.3), rgba(244, 114, 182, 0.1)) !important;
            border-color: rgba(236, 72, 153, 0.6) !important; /* Glowing pink glass border */
            box-shadow: 0 12px 40px 0 rgba(236, 72, 153, 0.22), inset 0 0 15px rgba(236, 72, 153, 0.35), inset 0 1px 0 0 rgba(255, 255, 255, 0.75) !important;
        }

        :root.light-theme .card-purple {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.16), rgba(192, 132, 252, 0.05)) !important;
            border: 1px solid rgba(168, 85, 247, 0.28) !important; /* Translucent purple glassy border */
            box-shadow: 0 8px 32px 0 rgba(168, 85, 247, 0.06), inset 0 1px 0 0 rgba(255, 255, 255, 0.6) !important;
        }
        :root.light-theme .card-purple .icon { color: #7c3aed !important; background: rgba(168, 85, 247, 0.08) !important; }
        :root.light-theme .card-purple .title { color: #7c3aed !important; }
        :root.light-theme .card-purple .subtitle { color: rgba(124, 58, 237, 0.8) !important; }
        :root.light-theme .card-purple:hover {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.3), rgba(192, 132, 252, 0.1)) !important;
            border-color: rgba(168, 85, 247, 0.6) !important; /* Glowing purple glass border */
            box-shadow: 0 12px 40px 0 rgba(168, 85, 247, 0.22), inset 0 0 15px rgba(168, 85, 247, 0.35), inset 0 1px 0 0 rgba(255, 255, 255, 0.75) !important;
        }
        .hub-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-block;
        }
        .hub-badge.completed { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
        .hub-badge.draft { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); }
    </style>
    <style>
        :root.light-theme {
            --bg-color: #f1f5f9;
            --surface: #ffffff;
            --surface-hover: rgba(241, 245, 249, 0.98);
            --border: rgba(0, 0, 0, 0.08);
            --border-highlight: rgba(0, 0, 0, 0.15);
        }
        :root.light-theme body {
            background-color: var(--bg-color);
            color: #000000;
        }
        :root.light-theme .ambient-bg {
            background: radial-gradient(circle at 50% 50%, #f8fafc 0%, #cbd5e1 100%) !important;
        }
        :root.light-theme .panel,
        :root.light-theme .neo-card,
        :root.light-theme .glass-panel {
            background: var(--surface) !important;
            border-color: var(--border) !important;
            color: #000000 !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06) !important;
        }
        :root.light-theme td, :root.light-theme th, :root.light-theme tr {
            color: #000000 !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
        }
        :root.light-theme .pos-grid thead th {
            background: rgba(0, 0, 0, 0.04) !important;
            color: #000000 !important;
        }
        :root.light-theme .pos-grid tbody tr {
            background: rgba(255, 255, 255, 0.95) !important;
        }
        :root.light-theme .pos-grid tbody tr:hover {
            background: rgba(0, 0, 0, 0.04) !important;
        }
        :root.light-theme input, :root.light-theme select, :root.light-theme textarea {
            background: #ffffff !important;
            color: #000000 !important;
            border: 1px solid rgba(0, 0, 0, 0.18) !important;
        }
    
    <style>
        /* CSS for table enhancements */
        .pos-grid th, .pos-grid td {
            font-size: 0.8rem !important;
            padding: 8px 10px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .pos-grid .g-input, .pos-grid .g-select, .pos-grid .g-readonly {
            font-size: 0.85rem !important;
            padding: 8px 10px !important;
            background: rgba(15, 23, 42, 0.5) !important;
            color: #f8fafc !important; /* Restore bright text */
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 8px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Modern Glassmorphism Styling */
        .grid-scroll-wrap {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .pos-grid thead th {
            background: rgba(15, 23, 42, 0.8) !important;
            backdrop-filter: blur(10px);
            border-bottom: 2px solid var(--gold-primary) !important;
            color: var(--gold-primary) !important;
            font-weight: 800 !important;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .pos-grid tbody tr {
            background: rgba(255, 255, 255, 0.02) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        }

        /* Hover effect on rows - Dynamic Elevation */
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

        /* Input Focus Glow Effect */
        .pos-grid .g-input:focus, .pos-grid .g-select:focus {
            background: rgba(15, 23, 42, 0.9) !important;
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.4), 0 0 15px rgba(56, 189, 248, 0.3) !important;
            transform: scale(1.02);
            z-index: 10;
        }
        
        .pos-grid .g-input::placeholder {
            color: #94a3b8 !important;
        }

        /* Resizable columns */
        .pos-grid th {
            position: relative;
        }
        .resizer {
            width: 5px;
            cursor: col-resize;
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            background-color: transparent;
            z-index: 10;
        }
        .resizer:hover, .resizing {
            background-color: var(--gold-primary);
        }

        /* Context Menu */
        #col-context-menu {
            position: absolute;
            z-index: 100000;
            background: var(--velvet-panel-hover);
            border: 1px solid var(--gold-border);
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.8);
            display: none;
            min-width: 150px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .col-toggle-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            cursor: pointer;
            color: #fff;
            font-size: 0.8rem;
            border-radius: 4px;
        }
        .col-toggle-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }
    </style>

<style>
/* Purchase Top Container CSS */
        #purchase-top-container {
        display: flex; gap: 20px; align-items: stretch; width: 100%; margin-bottom: 15px;
    }
    #purchase-top-container #form-fields-card {
        flex: 1; 
        min-width: 0; 
        transition: flex 0.6s cubic-bezier(0.2, 0.8, 0.2, 1), max-width 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) !important;
    }
    #purchase-top-container #totals-calc-card {
        display: flex !important;
        flex: 0 0 360px;
        min-width: 360px;
        max-width: 360px;
        opacity: 1;
        overflow: hidden;
        white-space: nowrap;
        background: linear-gradient(180deg, rgba(15,23,42,0.8), rgba(154,52,18,0.4));
        border: 1px solid rgba(249, 115, 22, 0.3);
        border-top: 3px solid #f97316 !important;
        border-radius: 12px;
        padding: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        transition: all 0.6s cubic-bezier(0.2, 0.8, 0.2, 1);
        transform-origin: right center;
    }
    #purchase-top-container #totals-calc-card.hidden-totals {
        flex: 0 0 0px !important;
        min-width: 0px !important;
        max-width: 0px !important;
        opacity: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        border-width: 0 !important;
    }
    .tot-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 6px 0; border-bottom: 1px dashed rgba(255,255,255,0.05);
        }
        .tot-row:last-child { border-bottom: none; }
        .tot-lbl { font-size: 0.7rem; color: var(--text-dim); font-weight: 600; }
        .tot-val { font-size: 0.85rem; font-weight: 800; direction: ltr; }
        .tot-input { width: 70px; padding: 2px 6px; font-size: 0.75rem; text-align: left; }
        .tot-grand {
            margin-top: 10px; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3);
            border-radius: 8px; padding: 12px; display: flex; justify-content: space-between; align-items: center;
        }
        .tot-grand .lbl { color: var(--success-color); font-weight: 800; font-size: 0.8rem; }
        .tot-grand .val { color: var(--success-color); font-weight: 900; font-size: 1.2rem; direction: ltr; text-shadow: 0 0 15px rgba(16,185,129,0.4); }

        /* Action Footer in Right Panel */
        .right-footer {
            padding: 15px; border-top: 1px solid rgba(255,255,255,0.05);
            display: flex; flex-direction: column; gap: 8px; background: transparent;
        }
        .btn-action {
            width: 100%; padding: 12px; border-radius: 8px; font-weight: 800; font-size: 0.9rem;
            cursor: pointer; border: none; transition: 0.3s;
        }
        .btn-submit { background: linear-gradient(135deg, #38bdf8, #818cf8); color: #fff; box-shadow: 0 4px 15px var(--gold-glow); }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px var(--gold-glow); }
        .btn-draft { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; }
        .btn-draft:hover { background: rgba(255,255,255,0.1); }
        .btn-delete { background: rgba(239,68,68,0.1); color: var(--danger-color); border: 1px solid rgba(239,68,68,0.2); }
        .btn-delete:hover { background: rgba(239,68,68,0.2); }

        /* LEFT PANEL: ITEMS & SEARCH (Flex 1) */
        .split-left {
            flex: 1; display: flex; flex-direction: column; padding: 0 15px 15px 15px;
            overflow: hidden; background: transparent;
        }

        /* Toolbar above table */
        .items-toolbar {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;
            background: rgba(18, 15, 25, 0.65); padding: 12px 15px; border-radius: 18px;
            border: 1px solid var(--gold-border); box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
        }
        .quick-search {
            flex: 1; max-width: 400px; position: relative;
        }
        .quick-search input {
            width: 100%; padding: 10px 15px; background: rgba(0,0,0,0.4); border: 1px solid var(--gold-border);
            border-radius: 8px; color: #fff; font-size: 0.9rem; outline: none; transition: 0.3s; box-sizing: border-box;
        }
        .quick-search input:focus { border-color: var(--gold-primary); box-shadow: 0 0 15px rgba(251,191,36,0.1); }

        /* Table Area */
        .table-container {
            background: rgba(18, 15, 25, 0.65); border-radius: 18px;
            border: 1px solid var(--gold-border); box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            overflow: visible; display: flex; flex-direction: column;
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
        }
        .grid-scroll-wrap { overflow-y: visible; overflow-x: auto; }
        .grid-scroll-wrap::-webkit-scrollbar { width: 6px; height: 6px; }
        .grid-scroll-wrap::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 6px; }
        
        .pos-grid { width: 100%; border-collapse: separate; border-spacing: 0 8px; min-width: 900px; }
        .pos-grid thead th {
            background: transparent; color: #94a3b8; font-size: 0.85rem; font-weight: 800;
            padding: 10px 15px; text-align: center; position: sticky; top: 0; z-index: 5;
            border-bottom: 2px solid rgba(255,255,255,0.05); text-transform: uppercase; letter-spacing: 0.5px;
        }
        .pos-grid tbody tr {
            background: rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        .pos-grid tbody tr:hover {
            background: rgba(255,255,255,0.05); transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .pos-grid tbody td {
            padding: 12px 10px; border-bottom: 1px solid rgba(255,255,255,0.02);
            border-top: 1px solid rgba(255,255,255,0.02);
            font-size: 0.85rem; vertical-align: middle;
        }
        .pos-grid tbody td:first-child { border-radius: 12px 0 0 12px; border-right: 1px solid rgba(255,255,255,0.02); }
        .pos-grid tbody td:last-child { border-radius: 0 12px 12px 0; border-left: 1px solid rgba(255,255,255,0.02); }
        
        .g-input, .g-select {
            width: 100%; padding: 10px 12px; background: rgba(15,23,42,0.4); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px; color: #f8fafc; text-align: center; outline: none; font-size: 0.9rem; font-family: 'Inter', sans-serif;
            transition: all 0.3s ease; box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
        }
        .g-input:focus, .g-select:focus { 
            border-color: #38bdf8; background: rgba(15,23,42,0.8); box-shadow: 0 0 0 2px rgba(56,189,248,0.2), inset 0 2px 4px rgba(0,0,0,0.2); 
        }
        .g-input[type="text"] { text-align: right; }
        
        .g-readonly {
            display: inline-block; padding: 8px 12px; background: rgba(255,255,255,0.05); border-radius: 8px; font-weight: 800; text-align: center; width: 100%; box-sizing: border-box;
        }
        
        .del-btn {
            width: 32px; height: 32px; border-radius: 8px; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2);
            color: var(--danger-color); cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center;
        }
        .del-btn:hover { background: var(--danger-color); color: #fff; box-shadow: 0 4px 10px rgba(239,68,68,0.4); transform: scale(1.1); }

        .empty-state {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            height: 100%; color: var(--text-dim); padding: 40px;
        }
        .empty-state .icon { font-size: 3rem; margin-bottom: 10px; opacity: 0.5; filter: drop-shadow(0 0 10px var(--gold-glow)); }

        /* Notes Bar at Bottom of Left Panel */
        .notes-bar {
            margin-top: 15px; background: rgba(18, 15, 25, 0.65); padding: 15px; border-radius: 18px;
            border: 1px solid var(--gold-border); box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
        }
        .notes-bar textarea {
            width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05);
            border-radius: 8px; color: #fff; padding: 10px; resize: vertical; min-height: 40px; outline: none;
            box-sizing: border-box; font-family: 'Inter', sans-serif;
        }
        .notes-bar textarea:focus { border-color: var(--gold-primary); }

        /* Modal specific to theme */
        .modal-card { background: var(--velvet-panel); border: 1px solid var(--gold-border); box-shadow: 0 20px 60px rgba(0,0,0,0.8); }
        .modal-header h3 { color: var(--gold-primary); }
        .modal-header { border-bottom-color: rgba(255,255,255,0.05); }
        .modal-footer { border-top-color: rgba(255,255,255,0.05); }

        @media (max-width: 1024px) {
            .split-layout { flex-direction: column; overflow-y: auto; height: auto; }
            .split-right { width: 100%; min-width: 100%; border-left: none; border-bottom: 1px solid var(--gold-border); }
            .split-left { overflow: visible; }
            .table-container { min-height: 400px; }
        }
    
        .fin-strip-inline.visible {
            flex: 0 0 43% !important;
            width: auto !important;
            opacity: 1 !important;
            min-width: 400px !important;
            margin-left: 5px;
        }
\n
    #purchase-top-container #totals-calc-card.hidden-totals {
        flex: 0 0 0px !important;
        min-width: 0px !important;
        width: 0px !important;
        opacity: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin: 0 !important;
        border: none !important;
        transform: scaleX(0);
    }

</style>

    <style>
        .hub-action-card {
            border-radius: 12px;
            padding: 12px 15px;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            gap: 15px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            height: 80px; /* Reduced height to ~50% of the previous tall card */
        }
        .hub-action-card::after {
            content: "";
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.05) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-25deg);
            transition: all 0.6s ease;
        }
        .hub-action-card:hover::after {
            left: 200%;
        }
        .hub-action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.4), inset 0 0 0 1px rgba(255, 255, 255, 0.15);
        }
        .hub-action-card .icon {
            font-size: 1.8rem;
            margin-bottom: 0;
            padding: 8px;
            border-radius: 10px;
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
        }
        .hub-action-card:hover .icon {
            transform: scale(1.1) rotate(-5deg);
        }
        .hub-action-card .text-content {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            text-align: right;
        }
        .hub-action-card .title {
            font-size: 1.05rem;
            font-weight: 800;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }
        .hub-action-card .subtitle {
            font-size: 0.7rem;
            font-weight: 600;
            text-align: right;
            margin: 0;
        }

        /* ── Theme-aware Card Styling (Dark Mode Defaults with Magical Glassy Borders & Glow) ── */
        .card-orange {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.25), rgba(180, 83, 9, 0.08)) !important;
            border: 1px solid rgba(245, 158, 11, 0.3) !important; /* Translucent orange border */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.1) !important;
        }
        .card-orange .icon { color: #f59e0b; background: rgba(245, 158, 11, 0.08); }
        .card-orange .title { color: #f59e0b !important; }
        .card-orange .subtitle { color: rgba(255, 255, 255, 0.7) !important; }
        .card-orange:hover {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.4), rgba(180, 83, 9, 0.15)) !important;
            border-color: rgba(245, 158, 11, 0.7) !important; /* Glowing orange glass border */
            box-shadow: 0 12px 40px 0 rgba(245, 158, 11, 0.35), inset 0 0 18px rgba(245, 158, 11, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
        }

        .card-blue {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.25), rgba(3, 105, 161, 0.08)) !important;
            border: 1px solid rgba(14, 165, 233, 0.3) !important; /* Translucent blue border */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.1) !important;
        }
        .card-blue .icon { color: #38bdf8; background: rgba(56, 189, 248, 0.08); }
        .card-blue .title { color: #38bdf8 !important; }
        .card-blue .subtitle { color: rgba(255, 255, 255, 0.7) !important; }
        .card-blue:hover {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.4), rgba(3, 105, 161, 0.15)) !important;
            border-color: rgba(14, 165, 233, 0.7) !important; /* Glowing blue glass border */
            box-shadow: 0 12px 40px 0 rgba(14, 165, 233, 0.35), inset 0 0 18px rgba(14, 165, 233, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
        }

        .card-pink {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.25), rgba(190, 24, 74, 0.08)) !important;
            border: 1px solid rgba(236, 72, 153, 0.3) !important; /* Translucent pink border */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.1) !important;
        }
        .card-pink .icon { color: #f43f5e; background: rgba(244, 63, 94, 0.08); }
        .card-pink .title { color: #f43f5e !important; }
        .card-pink .subtitle { color: rgba(255, 255, 255, 0.7) !important; }
        .card-pink:hover {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.4), rgba(190, 24, 74, 0.15)) !important;
            border-color: rgba(236, 72, 153, 0.7) !important; /* Glowing pink glass border */
            box-shadow: 0 12px 40px 0 rgba(236, 72, 153, 0.35), inset 0 0 18px rgba(236, 72, 153, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
        }

        .card-purple {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.25), rgba(109, 40, 217, 0.08)) !important;
            border: 1px solid rgba(168, 85, 247, 0.3) !important; /* Translucent purple border */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.1) !important;
        }
        .card-purple .icon { color: #a855f7; background: rgba(168, 85, 247, 0.08); }
        .card-purple .title { color: #a855f7 !important; }
        .card-purple .subtitle { color: rgba(255, 255, 255, 0.7) !important; }
        .card-purple:hover {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.4), rgba(109, 40, 217, 0.15)) !important;
            border-color: rgba(168, 85, 247, 0.7) !important; /* Glowing purple glass border */
            box-shadow: 0 12px 40px 0 rgba(168, 85, 247, 0.35), inset 0 0 18px rgba(168, 85, 247, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
        }

        /* ── Light Mode overrides for Hub Cards (Light Pastel Glassy Gradients, Vibrant Colored Text & Magical Hover Border Glows) ── */
        :root.light-theme .card-orange {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.16), rgba(251, 191, 36, 0.05)) !important;
            border: 1px solid rgba(245, 158, 11, 0.28) !important; /* Translucent orange glassy border */
            box-shadow: 0 8px 32px 0 rgba(245, 158, 11, 0.06), inset 0 1px 0 0 rgba(255, 255, 255, 0.6) !important;
        }
        :root.light-theme .card-orange .icon { color: #d97706 !important; background: rgba(245, 158, 11, 0.08) !important; }
        :root.light-theme .card-orange .title { color: #d97706 !important; }
        :root.light-theme .card-orange .subtitle { color: rgba(217, 119, 6, 0.8) !important; }
        :root.light-theme .card-orange:hover {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.3), rgba(251, 191, 36, 0.1)) !important;
            border-color: rgba(245, 158, 11, 0.6) !important; /* Glowing orange glass border */
            box-shadow: 0 12px 40px 0 rgba(245, 158, 11, 0.22), inset 0 0 15px rgba(245, 158, 11, 0.35), inset 0 1px 0 0 rgba(255, 255, 255, 0.75) !important;
        }

        :root.light-theme .card-blue {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.16), rgba(56, 189, 248, 0.05)) !important;
            border: 1px solid rgba(14, 165, 233, 0.28) !important; /* Translucent blue glassy border */
            box-shadow: 0 8px 32px 0 rgba(14, 165, 233, 0.06), inset 0 1px 0 0 rgba(255, 255, 255, 0.6) !important;
        }
        :root.light-theme .card-blue .icon { color: #0284c7 !important; background: rgba(14, 165, 233, 0.08) !important; }
        :root.light-theme .card-blue .title { color: #0284c7 !important; }
        :root.light-theme .card-blue .subtitle { color: rgba(2, 132, 199, 0.8) !important; }
        :root.light-theme .card-blue:hover {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.3), rgba(56, 189, 248, 0.1)) !important;
            border-color: rgba(14, 165, 233, 0.6) !important; /* Glowing blue glass border */
            box-shadow: 0 12px 40px 0 rgba(14, 165, 233, 0.22), inset 0 0 15px rgba(14, 165, 233, 0.35), inset 0 1px 0 0 rgba(255, 255, 255, 0.75) !important;
        }

        :root.light-theme .card-pink {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.16), rgba(244, 114, 182, 0.05)) !important;
            border: 1px solid rgba(236, 72, 153, 0.28) !important; /* Translucent pink glassy border */
            box-shadow: 0 8px 32px 0 rgba(236, 72, 153, 0.06), inset 0 1px 0 0 rgba(255, 255, 255, 0.6) !important;
        }
        :root.light-theme .card-pink .icon { color: #db2777 !important; background: rgba(236, 72, 153, 0.08) !important; }
        :root.light-theme .card-pink .title { color: #db2777 !important; }
        :root.light-theme .card-pink .subtitle { color: rgba(219, 39, 119, 0.8) !important; }
        :root.light-theme .card-pink:hover {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.3), rgba(244, 114, 182, 0.1)) !important;
            border-color: rgba(236, 72, 153, 0.6) !important; /* Glowing pink glass border */
            box-shadow: 0 12px 40px 0 rgba(236, 72, 153, 0.22), inset 0 0 15px rgba(236, 72, 153, 0.35), inset 0 1px 0 0 rgba(255, 255, 255, 0.75) !important;
        }

        :root.light-theme .card-purple {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.16), rgba(192, 132, 252, 0.05)) !important;
            border: 1px solid rgba(168, 85, 247, 0.28) !important; /* Translucent purple glassy border */
            box-shadow: 0 8px 32px 0 rgba(168, 85, 247, 0.06), inset 0 1px 0 0 rgba(255, 255, 255, 0.6) !important;
        }
        :root.light-theme .card-purple .icon { color: #7c3aed !important; background: rgba(168, 85, 247, 0.08) !important; }
        :root.light-theme .card-purple .title { color: #7c3aed !important; }
        :root.light-theme .card-purple .subtitle { color: rgba(124, 58, 237, 0.8) !important; }
        :root.light-theme .card-purple:hover {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.3), rgba(192, 132, 252, 0.1)) !important;
            border-color: rgba(168, 85, 247, 0.6) !important; /* Glowing purple glass border */
            box-shadow: 0 12px 40px 0 rgba(168, 85, 247, 0.22), inset 0 0 15px rgba(168, 85, 247, 0.35), inset 0 1px 0 0 rgba(255, 255, 255, 0.75) !important;
        }
        .hub-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-block;
        }
        .hub-badge.completed { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
        .hub-badge.draft { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); }
    </style>
    
    
    <style>
        /* CSS for table enhancements */
        .pos-grid th, .pos-grid td {
            font-size: 0.8rem !important;
            padding: 8px 10px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .pos-grid .g-input, .pos-grid .g-select, .pos-grid .g-readonly {
            font-size: 0.85rem !important;
            padding: 8px 10px !important;
            background: rgba(15, 23, 42, 0.5) !important;
            color: #f8fafc !important; /* Restore bright text */
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 8px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Modern Glassmorphism Styling */
        .grid-scroll-wrap {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .pos-grid thead th {
            background: rgba(15, 23, 42, 0.8) !important;
            backdrop-filter: blur(10px);
            border-bottom: 2px solid var(--gold-primary) !important;
            color: var(--gold-primary) !important;
            font-weight: 800 !important;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .pos-grid tbody tr {
            background: rgba(255, 255, 255, 0.02) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        }

        /* Hover effect on rows - Dynamic Elevation */
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

        /* Input Focus Glow Effect */
        .pos-grid .g-input:focus, .pos-grid .g-select:focus {
            background: rgba(15, 23, 42, 0.9) !important;
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.4), 0 0 15px rgba(56, 189, 248, 0.3) !important;
            transform: scale(1.02);
            z-index: 10;
        }
        
        .pos-grid .g-input::placeholder {
            color: #94a3b8 !important;
        }

        /* Resizable columns */
        .pos-grid th {
            position: relative;
        }
        .resizer {
            width: 5px;
            cursor: col-resize;
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            background-color: transparent;
            z-index: 10;
        }
        .resizer:hover, .resizing {
            background-color: var(--gold-primary);
        }

        /* Context Menu */
        #col-context-menu {
            position: absolute;
            z-index: 100000;
            background: var(--velvet-panel-hover);
            border: 1px solid var(--gold-border);
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.8);
            display: none;
            min-width: 150px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .col-toggle-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            cursor: pointer;
            color: #fff;
            font-size: 0.8rem;
            border-radius: 4px;
        }
        .col-toggle-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }
    </style>

<style>
/* Purchase Top Container CSS */
    #purchase-top-container {
        display: flex; gap: 20px; align-items: stretch; width: 100%; margin-bottom: 15px;
    }
    #purchase-top-container #form-fields-card {
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        flex: 1; /* Grows to take all available space automatically */
        min-width: 0; /* Prevents flex blowout on small screens */
    }
    #purchase-top-container #form-fields-card {
        transition: all 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) !important;
    }
    #purchase-top-container #totals-calc-card {
        transition: all 0.6s cubic-bezier(0.2, 0.8, 0.2, 1);
        flex: 0 0 360px; /* Fixed professional width for the totals */
        min-width: 360px;
        opacity: 1;
        overflow: hidden;
        transform-origin: right center;
        background: linear-gradient(180deg, rgba(15,23,42,0.8), rgba(30,58,138,0.4));
        border: 1px solid rgba(56, 189, 248, 0.3);
        border-top: 3px solid #38bdf8 !important;
        border-radius: 12px;
        padding: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    }
    .tot-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 3px 0; border-bottom: 1px dashed rgba(255,255,255,0.1);
    }
    .tot-lbl { font-size: 0.75rem; font-weight: 700; color: #94a3b8; }
    .tot-val { font-size: 0.85rem; font-weight: 900; color: #fff; font-family: 'Outfit', sans-serif; }
    .tot-input { font-weight: bold; font-family: 'Outfit', sans-serif; font-size: 0.9rem !important; }
    .tot-grand {
        margin-top: 15px; background: rgba(16,185,129,0.15); padding: 15px; border-radius: 8px; border: 1px solid rgba(16,185,129,0.3); box-shadow: 0 0 15px rgba(16,185,129,0.2);
        display: flex; justify-content: space-between; align-items: center;
    }
    .tot-grand .lbl { font-size: 0.75rem; font-weight: 900; color: #34d399; }
    .tot-grand .val { font-size: 1.15rem; font-weight: 900; color: #fff; font-family: 'Outfit', sans-serif; text-shadow: 0 0 10px rgba(255,255,255,0.3); }

    .purchase-bottom .table-container {
        min-height: unset !important;
        overflow: visible !important;
    }
    .purchase-bottom .grid-scroll-wrap {
        min-height: unset !important;
        overflow: visible !important;
    }
    .pos-grid thead th {
        font-size: 0.95rem !important;
        font-weight: 900 !important;
        color: #cbd5e1 !important;
    }
    .pos-grid .g-input, .pos-grid .g-select, .pos-grid .g-readonly {
        font-size: 1.05rem !important;
        font-weight: 700 !important;
    }
    html.light-theme th,
    :root.light-theme th,
    .light-theme th,
    .light-theme .pos-grid thead th,
    :root.light-theme .pos-grid thead th {
        background: #f1f5f9 !important; /* Solid light gray to prevent dark parent background bleeding */
        color: #0f172a !important;
        border-bottom: 2px solid #cbd5e1 !important;
    }
    .light-theme thead, .light-theme thead tr {
        background: transparent !important;
    }
    .light-theme .grid-scroll-wrap {
        background: transparent !important;
        box-shadow: none !important;
    }
    .light-theme .h-label,
    .light-theme .tot-lbl,
    .light-theme .lbl,
    .light-theme label,
    .light-theme span[style*="color:#94a3b8"],
    .light-theme span[style*="color: #94a3b8"] {
        color: #334155 !important;
        text-shadow: none !important;
    }
    .light-theme .tot-val,
    .light-theme .val {
        color: #0f172a !important;
        text-shadow: none !important;
    }
    .light-theme .neo-card-title {
        color: #0ea5e9 !important;
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
\n</style>
</head>
<body>
    <div class="toast-container" id="toast-container"></div>

    <!-- ═══ Add Supplier Modal ═══ -->
    <div class="modal-overlay" id="add-customer-modal">
        <div class="modal-card">
            <div class="modal-header">
                <h3>➕ إضافة مورد جديد</h3>
                <button class="modal-close" onclick="closeModal('add-customer-modal')" style="color:#fff;">✕</button>
            </div>
            <div class="modal-body">
                <div class="v-form-group">
                    <label class="v-label">اسم المورد</label>
                    <input type="text" class="v-input ctrl-input" id="new-cust-name" placeholder="اسم المورد...">
                </div>
                <div class="v-form-group">
                    <label class="v-label">رقم الهاتف</label>
                    <input type="tel" class="v-input ctrl-input" id="new-cust-phone" placeholder="09XXXXXXXX">
                </div>
                <div class="v-form-group">
                    <label class="v-label">حد الائتمان</label>
                    <input type="text" inputmode="decimal" class="v-input ctrl-input" id="new-cust-credit" placeholder="0" onfocus="this.select()" onblur="this.value=fmtNum(parseNum(this.value))">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-action btn-draft" style="width:auto; padding:8px 16px;" onclick="closeModal('add-customer-modal')">إلغاء</button>
                <button class="btn-action btn-submit" style="width:auto; padding:8px 16px;" onclick="addNewCustomer()">💾 حفظ المورد</button>
            </div>
        </div>
    </div>

    <!-- ═══ VELVET NAV ═══ -->
<div class="dash-main">
    
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>
    
    <?php $header_theme_class = 'header-theme-purchase'; ?>
    <?php include 'includes/header.php'; ?>

    <!-- ═══ PURCHASE HUB VIEW ═══ -->
    <div id="purchase-hub-view" style="display: flex; flex-direction: column; flex: 1; gap: 20px; animation: fadeIn 0.5s ease-out; padding: 10px 0; max-width: 100%; margin: 0; overflow-y: auto;">
        
        <!-- Action Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 25px;">


            <!-- Direct Purchase Invoice -->
            <button onclick="openPurchaseEditor('direct')" class="hub-action-card card-orange">
                <div class="icon">💵</div>
                <div class="text-content">
                    <div class="title">فاتورة مشتريات مباشرة</div>
                    <div class="subtitle">إنشاء فاتورة شراء نقدية مباشرة مع استلام فوري.</div>
                </div>
            </button>

            <!-- Goods Receipt Note -->
            <button onclick="window.location.href='purchase-receipt.php'" class="hub-action-card card-blue">
                <div class="icon">🚛</div>
                <div class="text-content">
                    <div class="title">استلام بضاعة (GRN)</div>
                    <div class="subtitle">استلام البضاعة المعلقة وإدخالها للمستودعات.</div>
                </div>
            </button>

            <!-- Purchase Return -->
            <button onclick="window.location.href='purchase-return.php'" class="hub-action-card card-pink">
                <div class="icon">↩️</div>
                <div class="text-content">
                    <div class="title">استرداد بضاعة</div>
                    <div class="subtitle">مردودات مشتريات مع تأثير عكسي على الحسابات.</div>
                </div>
            </button>

            <!-- Proforma Invoice -->
            <button onclick="openPurchaseEditor('proforma')" class="hub-action-card card-purple">
                <div class="icon">📋</div>
                <div class="text-content">
                    <div class="title">فاتورة مبدئية</div>
                    <div class="subtitle">عرض سعر / طلب شراء (لا تؤثر محاسبياً ولا مخزنياً).</div>
                </div>
            </button>
        </div>

        <!-- Recent Transactions Table -->
        <div class="neo-card" style="flex: 1; display: flex; flex-direction: column; overflow: hidden; margin-bottom: 15px;">
            <div class="neo-card-title" style="font-size: 1.1rem; display: flex; justify-content: space-between; align-items: center;">
                <span>📦 عمليات الشراء الأخيرة</span>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="hub-search" class="v-input" placeholder="بحث برقم الفاتورة أو المورد..." style="width: 250px;" oninput="filterHubTransactions()">
                    <button class="btn-action" style="width: auto; padding: 6px 12px; font-size: 0.8rem; background: rgba(255,255,255,0.05);" onclick="loadHubTransactions()">🔄 تحديث</button>
                </div>
            </div>
            
            <div class="grid-scroll-wrap" style="flex: 1; margin-top: 10px;">
                <table class="pos-grid" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="text-align:right;">رقم الفاتورة</th>
                            <th style="text-align:right;">التاريخ</th>
                            <th style="text-align:right;">المورد</th>
                            <th style="text-align:right;">الصنف</th>
                            <th style="text-align:center;">الكمية</th>
                            <th style="text-align:center;">السعر</th>
                            <th style="text-align:center;">الإجمالي</th>
                            <th style="text-align:center;">المخزن</th>
                            <th style="text-align:center;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="hub-transactions-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
                <div class="empty-state" id="hub-empty-state" style="display: none;">
                    <div class="icon">📭</div>
                    <h3>لا توجد مشتريات مسجلة</h3>
                    <p>اضغط على أحد الخيارات بالأعلى للبدء.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ PURCHASE EDITOR VIEW ═══ -->
    <div id="purchase-editor-view" style="display: none; flex-direction: column; flex: 1; overflow-y: auto; padding-bottom: 30px;">
    

    <!-- Form Fields (Now first in DOM, rendering on the Right in RTL) -->
        
<!-- WRAPPER FOR TOP SECTION -->
<div id="purchase-top-container" style="display: flex; gap: 15px; align-items: stretch; transition: all 0.5s ease; width: 100%; margin-bottom: 10px;">
<div class="neo-card expanded-form" id="form-fields-card" style="margin-bottom: 0;">
            <div style="display: flex; flex-direction: column; gap: 15px;">
                
                
                <!-- ROW 0 WRAPPER (Dynamic Split) -->
                <div style="display: flex; width: 100%; gap: 15px; margin-bottom: 12px; transition: all 0.4s ease;">
                    
                    <!-- LEFT: Invoice & Date -->
                    <div 
                    
                    id="invoice-info-box" style="flex: 1.3; display: flex; gap: 20px; width: 100%; background: linear-gradient(90deg, rgba(30,58,138,0.2), rgba(15,23,42,0.4)); padding: 6px 15px; border-radius: 12px; border: 1px solid rgba(56,189,248,0.25); box-shadow: 0 4px 15px rgba(0,0,0,0.2), inset 0 2px 5px rgba(255,255,255,0.05); align-items: center; transition: all 0.4s ease;">
                        
                        <div class="h-form-group" style="flex: 1; display:flex; align-items:center; gap:15px; margin:0;; flex-direction: row !important;">

                            <label id="hdr-inv-label" class="h-label" style="margin:0; color:#38bdf8; font-size: 0.9rem; font-weight:bold; white-space:nowrap; text-shadow: 0 0 5px rgba(56,189,248,0.3); transition: all 0.3s ease;">رقم الفاتورة:</label>
                            <div style="display:flex; align-items:center; justify-content:space-between; flex:1; background: rgba(0,0,0,0.3); padding: 3px 8px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                                <button class="pro-serial-btn" onclick="loadPreviousInvoice()" title="السابقة" style="background: transparent; border: none; color: #fff; font-size: 1.2rem; cursor: pointer; padding: 0 10px; opacity:0.7; transition:0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.7">❮</button>
                                <span id="inv-number" class="pro-serial-number" style="font-size: 0.9rem; font-family: 'Outfit', sans-serif; font-weight: 900; color: #38bdf8; letter-spacing: 2px; text-shadow: 0 0 10px rgba(56,189,248,0.5); transition: all 0.3s ease;">INV-000001</span>
                                <input type="hidden" id="hdr-inv-no">
                                <button class="pro-serial-btn" onclick="loadNextInvoice()" title="التالية" style="background: transparent; border: none; color: #fff; font-size: 1.2rem; cursor: pointer; padding: 0 10px; opacity:0.7; transition:0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.7">❯</button>
                            </div>
                        </div>

                        <div style="width: 2px; height: 40px; background: rgba(255,255,255,0.1); margin: 0 10px;"></div>

                        <div class="h-form-group" style="flex: 1; display:flex; align-items:center; gap:15px; margin:0;; flex-direction: row !important;">

                            <label class="h-label" id="hdr-date-label" style="margin:0; color:#ef4444; font-size: 0.9rem; font-weight:bold; white-space:nowrap; text-shadow: 0 0 5px rgba(239,68,68,0.3); transition: all 0.3s ease;">التاريخ:</label>
                            <input type="date" class="v-input ctrl-input" id="hdr-date" style="flex:1; text-align: center; background: rgba(0,0,0,0.3); border: 1px solid rgba(239,68,68,0.2); border-radius: 8px; color:#ef4444; padding:4px 8px; font-family:'Outfit',sans-serif; font-weight:bold; font-size:0.95rem; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3); transition: all 0.3s ease;">
                        </div>
                    </div>
                    
                    <!-- RIGHT: Financial Strip -->
                    <div id="customer-financial-card" class="fin-strip-inline" style="flex: 0; width: 0; opacity: 0; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); display: flex;">
                        <div style="width: 100%; background: linear-gradient(90deg, rgba(15,23,42,0.4), rgba(30,58,138,0.2)); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 12px; padding: 4px 10px; display: flex; align-items: center; justify-content: space-around; gap: 5px; box-shadow: 0 4px 15px rgba(0,0,0,0.2), inset 0 2px 5px rgba(255,255,255,0.05); min-width: 500px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.2rem;">📊</span>
                <div style="display:flex; flex-direction:column;">
                    <span style="font-weight: 800; color:#fff; font-size:0.8rem;">الحالة المالية للمورد</span>
                    <span id="fin-status-msg" style="color:var(--text-dim); font-size:0.65rem;">لا يوجد</span>
                </div>
            </div>
            
            <div style="display:flex; gap: 15px; align-items: center;">
                <div id="fin-wrap-ledger" style="display:flex; flex-direction:column; align-items:center;">
                    <span style="font-size:0.6rem; color:#94a3b8; font-weight:700;">الرصيد الدفتري</span>
                    <span id="fin-ledger" style="font-weight:800; color:#fff; font-size:0.8rem;">0</span>
                </div>
                <div style="width:1px; height:15px; background:rgba(255,255,255,0.1);"></div>
                
                <div id="fin-wrap-debt" style="display:flex; flex-direction:column; align-items:center;">
                    <span style="font-size:0.6rem; color:#ef4444; font-weight:700;">المديونية علينا</span>
                    <span id="fin-total-debt" style="font-weight:800; color:#ef4444; font-size:0.8rem;">0</span>
                </div>
                <div style="width:1px; height:15px; background:rgba(255,255,255,0.1);"></div>
                
                <div id="fin-wrap-checks" style="display:flex; flex-direction:column; align-items:center;">
                    <span style="font-size:0.6rem; color:#f59e0b; font-weight:700;">شيكات مدفوعة</span>
                    <span id="fin-checks" style="font-weight:800; color:#f59e0b; font-size:0.8rem;">0</span>
                </div>
                <div style="width:1px; height:15px; background:rgba(255,255,255,0.1);"></div>
                
                <div id="fin-wrap-limit" style="display:flex; flex-direction:column; align-items:center;">
                    <span style="font-size:0.6rem; color:#38bdf8; font-weight:700;">حد المورد</span>
                    <span id="fin-credit-limit" style="font-weight:800; color:#38bdf8; font-size:0.8rem;">0</span>
                </div>
                <div style="width:1px; height:15px; background:rgba(255,255,255,0.1);"></div>
                
                <div style="display:flex; flex-direction:column; align-items:center; background: rgba(16,185,129,0.1); padding: 3px 10px; border-radius: 6px; border: 1px solid rgba(16,185,129,0.2);">
                    <span style="font-size:0.6rem; color:#34d399; font-weight:800;">✨ المتاح</span>
                    <span id="fin-remaining" style="font-weight:900; color:#34d399; font-size:0.9rem;">0</span>
                </div>
            </div>
                        </div>
                    </div>

                </div>
                <!-- Row 1: Supplier, Currency -->
                <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 5px;">
                    <div class="h-form-group" style="flex: 3; min-width: 250px; display: flex; align-items: center; gap: 10px; flex-direction: row !important;">
                          <label class="h-label" style="margin: 0; white-space: nowrap; min-width: 60px;">المورد</label>
                          <div class="h-input-wrapper" style="display: flex; flex-direction: row; align-items: center; gap: 8px; flex: 1;">
                                    <div class="custom-dropdown" style="flex:1;">
                                        <div class="custom-dropdown-input-wrap" style="position: relative;">
                                            <input type="text" class="ctrl-input v-input" id="hdr-customer-search" placeholder="ابحث عن اسم المورد..." onfocus="showCustomerDropdown()" onblur="hideCustomerDropdownDelayed()" oninput="filterCustomerDropdown()" style="width:100%; background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.15); padding-left: 60px !important;" autocomplete="one-time-code">
                                            <button type="button" id="hdr-customer-ledger-btn" onclick="openGeneralLedgerForAccount(document.getElementById('hdr-customer').value)" class="ledger-btn-2027" title="دفتر الأستاذ العام">أستاذ</button>
                                            <input type="hidden" id="hdr-customer" onchange="if(typeof onCustomerChange === 'function') onCustomerChange()">
                                            <div class="custom-dropdown-list" id="customer-dropdown-list">
                                                <!-- Populated via JS -->
                                            </div>
                                        </div>
                                    </div>
                          </div>
                    </div>
                    <div class="h-form-group" style="flex: 2; min-width: 200px; display: flex; align-items: center; gap: 10px; flex-direction: row !important;">
                          <label class="h-label" style="margin: 0; white-space: nowrap; min-width: 60px;">سعر الصرف</label>
                          <div class="h-input-wrapper" style="display: flex; flex-direction: row; align-items: center; gap: 5px; flex: 1;">
                            <select class="v-input ctrl-input system-currency-select" id="hdr-currency" onchange="handleCurrencyChange(this.value)" style="flex: 1;">
                                <option value="SDG">🇸🇩 جنيه</option>
                                <option value="USD">🇺🇸 دولار</option>
                                <option value="SAR">🇸🇦 ريال</option>
                                <option value="AED">🇦🇪 درهم</option>
                                <option value="EGP">🇪🇬 مصري</option>
                            </select>
                            <input type="number" class="v-input ctrl-input" id="hdr-exchange-rate" value="1" disabled oninput="recalcAll()" style="flex: 1.5; text-align: center;" placeholder="الصرف" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" >
                        </div>
                    </div>
                </div>

                <!-- Row 2: Warehouse, Cost Center -->
                <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 5px;">
                    <div class="h-form-group" style="flex: 1; min-width: 150px; display: flex; align-items: center; gap: 10px; flex-direction: row !important;">
                          <label class="h-label" style="margin: 0; white-space: nowrap; min-width: 60px;">المخزن</label>
                          <div class="h-input-wrapper" style="flex: 1; flex-direction: row !important; display: flex !important; align-items: center !important;">
                            <select class="v-input ctrl-input" id="hdr-warehouse" onchange="onMainWarehouseChange()"></select>
                        </div>
                    </div>
                    <div class="h-form-group" style="flex: 1; min-width: 150px; display: flex; align-items: center; gap: 10px;; flex-direction: row !important;">
                        <label class="h-label" style="margin: 0; white-space: nowrap; min-width: 80px;">مركز الكلفة</label>
                        <div class="h-input-wrapper" style="flex: 1;; flex-direction: row !important; display: flex !important; align-items: center !important;">
                            <select class="v-input ctrl-input" id="hdr-cost-center">
                                <option value="">-- بدون مركز --</option>
                                <option value="C1">المركز الرئيسي</option>
                                <option value="C2">الفرع الأول</option>
                                <option value="C3">المشروع أ</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Row 4: Description -->
                <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 5px;">
                    <div class="h-form-group" style="flex: 1; min-width: 100%; display: flex; align-items: center; gap: 10px;; flex-direction: row !important;">
                        <label class="h-label" style="margin: 0; white-space: nowrap; min-width: 60px;">البيان</label>
                        <div class="h-input-wrapper" style="flex: 1;; flex-direction: row !important; display: flex !important; align-items: center !important;">
                            <input type="text" class="v-input ctrl-input" id="hdr-description" placeholder="ملاحظات، أرقام مرجعية...">
                        </div>
                    </div>
                </div>
                
                <!-- Due Date (hidden normally) -->
                <div class="h-form-group" id="due-date-group" style="display:none; border-color: rgba(245,158,11,0.3); background: rgba(245,158,11,0.05);; flex-direction: row !important;">
                    <label class="h-label" style="color: #f59e0b;">الاستحقاق</label>
                    <div class="h-input-wrapper" style="display: flex !important; flex-direction: row !important; align-items: center !important; flex: 1;">
                        <input type="date" class="v-input ctrl-input" id="hdr-due-date" style="color:#f59e0b; background: rgba(245,158,11,0.1);">
                    </div>
                </div>

            </div>
        </div>

        <!-- Totals Card (Now second in DOM, rendering on the Left in RTL) -->
                <div class="neo-card totals-card hidden-totals" id="totals-calc-card" style="margin-bottom: 0; flex-direction: column; justify-content: space-between; border-top: 2px solid var(--gold-primary); width: 330px; flex-shrink: 0;">
            <div class="neo-card-title" style="margin-bottom: 10px; text-align: center; color: var(--gold-primary); font-size: 0.95rem;">🧮 الإجماليات</div>
            
            <span id="t-invoice-total" style="display:none;">0</span>
            <span id="t-invoice-total-fc" style="display:none;">0</span>

            <table style="width:100%; border-collapse: collapse; text-align: right; margin-bottom: 10px;">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); color: #cbd5e1; font-size: 0.8rem;">
                        <th style="padding-bottom:5px; text-align:right;">البيان</th>
                        <th style="padding-bottom:5px; text-align:center;">محلي</th>
                        <th style="padding-bottom:5px; text-align:center;">أجنبي</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 8px 0; color: #fff; font-size:0.85rem;">الترحيل</td>
                        <td style="padding: 8px 2px;"><input type="text" inputmode="decimal" class="v-input tot-input tl-input amount" id="t-transport" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('transport')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="width:100%; text-align:center; background: rgba(249, 115, 22, 0.15); border: 1px solid rgba(249, 115, 22, 0.4); border-radius: 4px; padding: 2px; height: 26px; color: #fff; font-size: 0.85rem;" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" ></td>
                        <td style="padding: 8px 2px;"><input type="text" inputmode="decimal" class="v-input tot-input tl-input amount" id="t-transport-fc" value="0" onfocus="this.select()" oninput="if(typeof syncLocal==='function')syncLocal('transport')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="width:100%; text-align:center; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); border-radius: 4px; padding: 2px; height: 26px; color: #fff; font-size: 0.85rem;" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" ></td>
                    </tr>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 8px 0; color: #fff; font-size:0.85rem;">عمالة/طرح</td>
                        <td style="padding: 8px 2px;"><input type="text" inputmode="decimal" class="v-input tot-input tl-input amount" id="t-labor" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('labor')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="width:100%; text-align:center; background: rgba(249, 115, 22, 0.15); border: 1px solid rgba(249, 115, 22, 0.4); border-radius: 4px; padding: 2px; height: 26px; color: #fff; font-size: 0.85rem;" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" ></td>
                        <td style="padding: 8px 2px;"><input type="text" inputmode="decimal" class="v-input tot-input tl-input amount" id="t-labor-fc" value="0" onfocus="this.select()" oninput="if(typeof syncLocal==='function')syncLocal('labor')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="width:100%; text-align:center; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); border-radius: 4px; padding: 2px; height: 26px; color: #fff; font-size: 0.85rem;" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" ></td>
                    </tr>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 8px 0; color: var(--gold-primary); font-size:0.85rem;">إضافات</td>
                        <td style="padding: 8px 2px;"><input type="text" inputmode="decimal" class="v-input tot-input tl-input amount" id="t-additions-loc" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('additions')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="width:100%; text-align:center; background: rgba(234, 179, 8, 0.15); border: 1px solid rgba(234, 179, 8, 0.4); border-radius: 4px; padding: 2px; height: 26px; color: #fff; font-size: 0.85rem;" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" ></td>
                        <td style="padding: 8px 2px;"><input type="text" inputmode="decimal" class="v-input tot-input tl-input amount" id="t-additions-fc" value="0" onfocus="this.select()" oninput="if(typeof syncLocal==='function')syncLocal('additions')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="width:100%; text-align:center; background: rgba(234, 179, 8, 0.15); border: 1px solid rgba(234, 179, 8, 0.4); border-radius: 4px; padding: 2px; height: 26px; color: #fff; font-size: 0.85rem;" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" ></td>
                    </tr>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 8px 0; color: var(--danger-color); font-size:0.85rem;">الضريبة</td>
                        <td style="padding: 8px 2px;"><input type="text" inputmode="decimal" class="v-input tot-input tl-input amount" id="t-tax-loc" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('tax')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="width:100%; text-align:center; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 4px; padding: 2px; height: 26px; color: #fff; font-size: 0.85rem;" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" ></td>
                        <td style="padding: 8px 2px;"><input type="text" inputmode="decimal" class="v-input tot-input tl-input amount" id="t-tax-fc" value="0" onfocus="this.select()" oninput="if(typeof syncLocal==='function')syncLocal('tax')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="width:100%; text-align:center; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 4px; padding: 2px; height: 26px; color: #fff; font-size: 0.85rem;" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" ></td>
                    </tr>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 8px 0; color: var(--success-color); font-size:0.85rem;">خصم</td>
                        <td style="padding: 8px 2px;"><input type="text" inputmode="decimal" class="v-input tot-input tl-input amount" id="t-discount-loc" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('discount')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="width:100%; text-align:center; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); border-radius: 4px; padding: 2px; height: 26px; color: #fff; font-size: 0.85rem;" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" ></td>
                        <td style="padding: 8px 2px;"><input type="text" inputmode="decimal" class="v-input tot-input tl-input amount" id="t-discount-fc" value="0" onfocus="this.select()" oninput="if(typeof syncLocal==='function')syncLocal('discount')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="width:100%; text-align:center; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); border-radius: 4px; padding: 2px; height: 26px; color: #fff; font-size: 0.85rem;" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" ></td>
                    </tr>
                </tbody>
            </table>

            <div class="tot-grand" style="margin-top: auto; background: rgba(56, 189, 248, 0.1); padding: 10px; border-radius: 8px; border: 1px solid rgba(56, 189, 248, 0.3); display: flex; flex-direction: column; gap: 5px;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="lbl" id="lbl-grand-local" style="font-weight: 800; color: #38bdf8; font-size:0.9rem;">صافي محلي</span>
                    <span class="val amount" id="t-grand-local" style="font-size: 1.2rem; color: #fff;">0</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="lbl" id="lbl-grand-fc" style="font-weight: 800; color: #10b981; font-size:0.9rem;">صافي أجنبي</span>
                    <span class="val amount" id="t-grand-fc" style="font-size: 1.2rem; color: #fff;">0</span>
                </div>
            </div>
        </div>

    </div> <!-- End purchase-top-row -->

    <!-- BOTTOM PANEL (Items Table) -->
    <div class="purchase-bottom" style="flex: none; display: flex; flex-direction: column; padding: 0 15px 15px 15px; overflow: visible; margin-top: 15px;">
        
        <!-- items-toolbar was moved below the table-container -->

        <div class="table-container">
            <div class="grid-scroll-wrap">
                <table class="pos-grid" id="pos-grid">
                    <thead id="grid-head">
                    </thead>
                    <tbody id="grid-body"></tbody>
                </table>
                <div class="empty-state" id="empty-state">
                    <div class="icon">📦</div>
                    <h3>قائمة المشتريات فارغة</h3>
                    <p>استخدم شريط البحث بالأعلى أو اضغط على الزائد بالمنتصف للبدء.</p>
                </div>
            </div>
            <!-- Centered Add Row Button -->
            <div id="add-row-container" style="display: flex; justify-content: center; margin-top: 15px; margin-bottom: 10px;">
                <button type="button" onclick="addRow()" style="
                    width: 40px;
                    height: 40px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: linear-gradient(135deg, #10b981, #059669);
                    color: white;
                    border: none;
                    border-radius: 50%;
                    cursor: pointer;
                    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.25);
                    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                    outline: none;
                " onmouseover="this.style.transform='scale(1.15) rotate(90deg)'; this.style.boxShadow='0 6px 20px rgba(16, 185, 129, 0.4)';" onmouseout="this.style.transform='scale(1) rotate(0deg)'; this.style.boxShadow='0 4px 15px rgba(16, 185, 129, 0.25)';" title="إضافة سطر">
                    <span style="font-size: 1.2rem; font-weight: bold; line-height: 1;">➕</span>
                </button>
            </div>
            <div class="mobile-cards" id="mobile-cards"></div>
        </div>

            <!-- ══ FULL-WIDTH: تفاصيل الدفع ══ -->
            <div class="panel" id="payment-panel" style="z-index: 40; margin-top: 15px; padding: 0 !important; background: rgba(15, 23, 42, 0.22) !important; border: 1px solid rgba(234, 179, 8, 0.22) !important; backdrop-filter: blur(18px) saturate(130%) !important; -webkit-backdrop-filter: blur(18px) saturate(130%) !important;">
                <style>
                    #payment-panel {
                        animation: glassPulseGold 3.5s infinite ease-in-out;
                        transition: all 0.4s ease;
                    }
                    @keyframes glassPulseGold {
                        0%, 100% {
                            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37), 0 0 10px rgba(234, 179, 8, 0.18), inset 0 1px 1px rgba(255, 255, 255, 0.05);
                            border-color: rgba(234, 179, 8, 0.22) !important;
                        }
                        50% {
                            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.45), 0 0 20px rgba(234, 179, 8, 0.38), inset 0 1px 1px rgba(255, 255, 255, 0.12);
                            border-color: rgba(234, 179, 8, 0.45) !important;
                        }
                    }
                    .pay-toggle-btn.active {
                        background: linear-gradient(135deg, #ff7300, #ff9100) !important;
                        border-color: #ff7300 !important;
                        color: #fff !important;
                        box-shadow: 0 0 12px rgba(255, 115, 0, 0.4) !important;
                    }
                    .payment-summary-card {
                        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
                        transform-origin: top;
                        max-height: 0;
                        opacity: 0;
                        overflow: hidden;
                        padding: 0 20px !important;
                        border-color: transparent !important;
                        transform: scaleY(0);
                        flex: 0;
                        min-width: 0 !important;
                        margin: 0;
                    }
                    .payment-summary-card.show-totals {
                        max-height: 350px;
                        opacity: 1;
                        padding: 20px !important;
                        border-color: rgba(255,255,255,0.08) !important;
                        transform: scaleY(1);
                        flex: 1;
                        min-width: 280px !important;
                    }
                </style>
                <div class="panel-header-row" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; padding: 12px 20px; background: rgba(15,23,42,0.4); border-bottom: 1px solid rgba(255,255,255,0.06); border-top-left-radius: 12px; border-top-right-radius: 12px; margin: 0;">
                    <div class="panel-title" style="background: linear-gradient(135deg, #ff4d4d, #f87171); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 1.15rem; font-weight: 900; display: inline-flex; align-items: center; gap: 6px; margin: 0;">
                        <span>💳</span> طريقة وتفاصيل الدفع
                    </div>
                    <div class="payment-toggles" style="display: flex; gap: 8px;">
                        <button type="button" class="pay-toggle-btn" id="toggle-cash" onclick="togglePaymentSection('cash')" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 6px;">💵 نقدي</button>
                        <button type="button" class="pay-toggle-btn" id="toggle-credit" onclick="togglePaymentSection('credit')" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 6px;">💳 آجل</button>
                        <button type="button" class="pay-toggle-btn" id="toggle-bank" onclick="togglePaymentSection('bank')" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 6px;">🏦 تحويل بنكي</button>
                        <button type="button" class="pay-toggle-btn" id="toggle-cheque" onclick="togglePaymentSection('cheque')" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 6px;">✍️ شيك</button>
                    </div>
                </div>
                
                <div class="payment-layout" id="payment-layout" style="display: none; gap: 15px; padding: 15px; flex-wrap: wrap;">
                    <!-- Left Section: Invoice Totals Summary -->
                    <div class="payment-summary-card" style="flex: 1; min-width: 280px; background: rgba(30,41,59,0.4); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 20px; display: flex; flex-direction: column; justify-content: center; gap: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 5px;">
                            <span style="color: #94a3b8; font-size: 0.9rem; font-weight: bold;">💵 إجمالي المدفوع:</span>
                            <span id="payment-paid-indicator" style="font-size: 1.15rem; font-weight: 900; color: #34d399; text-shadow: 0 0 8px rgba(52,211,153,0.15);">0.00 SDG</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 12px;">
                            <span style="color: #94a3b8; font-size: 1rem; font-weight: bold;">📊 إجمالي الفاتورة:</span>
                            <span id="payment-total-indicator" style="font-size: 1.5rem; font-weight: 900; color: #fff;">0.00 SDG</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 12px;">
                            <span style="color: #94a3b8; font-size: 1.05rem; font-weight: bold;">⚖️ المبلغ المتبقي:</span>
                            <span id="payment-remaining-indicator" style="font-size: 1.7rem; font-weight: 900; color: #f87171; text-shadow: 0 0 10px rgba(248,113,113,0.3);">0.00 SDG</span>
                        </div>
                        <!-- Quick Actions to autofill -->
                        <div style="display: flex; gap: 10px; margin-top: 10px;">
                            <button type="button" class="btn-action" onclick="autofillRemaining('cash')" style="flex: 1; font-size: 0.8rem; background: rgba(52, 211, 153, 0.15); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.4); padding: 8px; border-radius: 8px; cursor: pointer;">تعبئة النقدي</button>
                            <button type="button" class="btn-action" onclick="autofillRemaining('credit')" style="flex: 1; font-size: 0.8rem; background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.4); padding: 8px; border-radius: 8px; cursor: pointer;">تعبئة الآجل</button>
                        </div>
                    </div>

                    <!-- Right Section: Payment Details Forms -->
                    <div class="payment-details-card" style="flex: 2; min-width: 320px; display: flex; flex-direction: column; gap: 10px;">
                        
                        <!-- Cash payment box (Visible by default) -->
                        <div id="pay-cash-section" class="payment-method-box" style="background: rgba(16, 185, 129, 0.04); border: 1px solid rgba(16, 185, 129, 0.18); border-radius: 12px; padding: 15px; display: flex; flex-direction: column; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 1.2rem;">💵</span>
                                <span style="font-weight: 800; color: #34d399; font-size: 0.95rem;">الدفع النقدي (كاش)</span>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="number" class="v-input ctrl-input" id="pay-cash-amount" value="0" step="any" oninput="onPaymentAmountInput()" placeholder="المبلغ..." style="flex: 0.8; background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 38px; text-align: center;">
                                <select class="v-input ctrl-input" id="hdr-payment-account" style="flex: 1.2; background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 38px;">
                                    <!-- Populated dynamically -->
                                </select>
                            </div>
                        </div>

                        <!-- Credit payment box (Toggled off by default) -->
                        <div id="pay-credit-section" class="payment-method-box" style="background: rgba(245, 158, 11, 0.04); border: 1px solid rgba(245, 158, 11, 0.18); border-radius: 12px; padding: 15px; display: none; flex-direction: column; gap: 10px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 1.2rem;">💳</span>
                                    <span style="font-weight: 800; color: #fbbf24; font-size: 0.95rem;">الشراء الآجل (على الحساب)</span>
                                </div>
                                <span style="font-size: 0.72rem; color: #94a3b8;">ℹ️ إقرار دين للمورد تلقائي</span>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; width: 100%;">
                                <input type="number" class="v-input ctrl-input" id="pay-credit-amount" value="0" step="any" oninput="onPaymentAmountInput()" placeholder="المبلغ الآجل..." style="flex: 1; min-width: 110px; background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 38px; text-align: center;">
                                
                                <div id="pay-credit-due-group" style="flex: 1.2; min-width: 160px; display: flex; align-items: center; gap: 5px;">
                                    <label style="font-size: 0.72rem; color: #94a3b8; white-space: nowrap;">تاريخ الاستحقاق</label>
                                    <input type="date" class="v-input ctrl-input" id="pay-credit-due-date" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 38px; font-size:0.85rem; padding: 0 4px; width: 100%;">
                                </div>
                                
                                <button type="button" class="btn-action" onclick="previewCurrentPromissoryNote()" style="flex: 0.8; min-width: 110px; height: 38px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: #f59e0b; padding: 0 10px; border-radius: 8px; font-size: 0.8rem; cursor: pointer; font-weight: bold; transition: 0.2s;" onmouseover="this.style.background='rgba(245, 158, 11, 0.25)';" onmouseout="this.style.background='rgba(245, 158, 11, 0.15)';">📄 معاينة الكمبيالة</button>
                            </div>
                        </div>

                        <!-- Bank Transfers box (Toggled off by default) -->
                        <div id="pay-bank-section" class="payment-method-box" style="background: rgba(59, 130, 246, 0.04); border: 1px solid rgba(59, 130, 246, 0.18); border-radius: 12px; padding: 15px; display: none; flex-direction: column; gap: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 1.2rem;">🏦</span>
                                    <span style="font-weight: 800; color: #60a5fa; font-size: 0.95rem;">التحويلات البنكية</span>
                                </div>
                                <button type="button" class="btn-action" onclick="addBankTransferRow()" style="background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.4); color: #60a5fa; font-weight: bold; padding: 5px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; transition: 0.3s; display: flex; align-items: center; gap: 4px;" onmouseover="this.style.background='rgba(59, 130, 246, 0.25)';" onmouseout="this.style.background='rgba(59, 130, 246, 0.15)';">➕ إضافة تحويل</button>
                            </div>
                            <!-- Dynamic bank transfers rows -->
                            <div id="bank-transfers-list" style="display: flex; flex-direction: column; gap: 8px; margin-top: 5px;"></div>
                        </div>

                        <!-- Cheques box (Toggled off by default) -->
                        <div id="pay-cheque-section" class="payment-method-box" style="background: rgba(139, 92, 246, 0.04); border: 1px solid rgba(139, 92, 246, 0.18); border-radius: 12px; padding: 15px; display: none; flex-direction: column; gap: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 1.2rem;">✍️</span>
                                    <span style="font-weight: 800; color: #c084fc; font-size: 0.95rem;">الشيكات الصادرة (المؤجلة / الفورية)</span>
                                </div>
                                <button type="button" class="btn-action" onclick="addChequeRow()" style="background: rgba(139, 92, 246, 0.15); border: 1px solid rgba(139, 92, 246, 0.4); color: #c084fc; font-weight: bold; padding: 5px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; transition: 0.3s; display: flex; align-items: center; gap: 4px;" onmouseover="this.style.background='rgba(139, 92, 246, 0.25)';" onmouseout="this.style.background='rgba(139, 92, 246, 0.15)';">➕ إضافة شيك</button>
                            </div>
                            <!-- Dynamic cheque rows -->
                            <div id="cheques-list" style="display: flex; flex-direction: column; gap: 8px; margin-top: 5px;"></div>
                        </div>
                    </div>
                </div>
            </div>

        <div class="items-toolbar" style="display: flex; align-items: center; justify-content: center; padding: 15px; border-radius: 12px; background: rgba(18, 15, 25, 0.8); border: 1px solid rgba(255,255,255,0.05); margin-top: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
            
            <div class="toolbar-actions" style="display: flex; gap: 12px; width: 100%; align-items: center;">
                
                <!-- AI OCR Button: Teal -->
                <button type="button" class="btn-action" style="flex: 1; height: 45px; background: #0d9488; border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(13, 148, 136, 0.3);" onmouseover="this.style.background='#0f766e'; this.style.boxShadow='0 4px 15px rgba(13, 148, 136, 0.5)';" onmouseout="this.style.background='#0d9488'; this.style.boxShadow='0 2px 10px rgba(13, 148, 136, 0.3)';" onclick="document.getElementById('ocr-upload').click()" id="btn-ocr" title="قراءة الفاتورة بالذكاء الاصطناعي">📸 مسح ضوئي</button>
                <input type="file" id="ocr-upload" accept="image/*,application/pdf" style="display: none;" onchange="processInvoiceImage(event)">

                <!-- Barcode Camera Button -->
                <button type="button" class="btn-action" style="flex: 1; height: 45px; background: #10b981; border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(16, 185, 129, 0.3);" onmouseover="this.style.background='#059669'; this.style.boxShadow='0 4px 15px rgba(16, 185, 129, 0.5)';" onmouseout="this.style.background='#10b981'; this.style.boxShadow='0 2px 10px rgba(16, 185, 129, 0.3)';" onclick="startCameraScanner('quick-purchase-scan')" id="btn-barcode-scan" title="مسح باركود الصنف بالكاميرا">📷 مسح باركود</button>
                
                <!-- Attachment Button: Solid Purple -->
                <button class="btn-action" id="attach-btn" style="flex: 1; height: 45px; background: #8b5cf6; border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(139, 92, 246, 0.3);" onmouseover="this.style.background='#7c3aed'; this.style.boxShadow='0 4px 15px rgba(139, 92, 246, 0.5)';" onmouseout="this.style.background='#8b5cf6'; this.style.boxShadow='0 2px 10px rgba(139, 92, 246, 0.3)';" onclick="document.getElementById('hdr-attachment').click()">📎 إرفاق مستند</button>
                <input type="file" id="hdr-attachment" accept="image/*,application/pdf" style="display:none;" onchange="document.getElementById('attach-btn').innerText = this.files.length > 0 ? '📎 تم الإرفاق' : '📎 إرفاق مستند'; document.getElementById('attach-btn').style.background = this.files.length > 0 ? '#10b981' : '#8b5cf6';">
                
                <!-- Cancel Button: Solid Red -->
                <button class="btn-action btn-delete" style="flex: 1; height: 45px; background: #ef4444; border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(239, 68, 68, 0.3);" onmouseover="this.style.background='#dc2626'; this.style.boxShadow='0 4px 15px rgba(239, 68, 68, 0.5)';" onmouseout="this.style.background='#ef4444'; this.style.boxShadow='0 2px 10px rgba(239, 68, 68, 0.3)';" onclick="confirmDeleteTransaction()">❌ إلغاء</button>
                
                <!-- Preview Button: Solid Blue -->
                <button type="button" class="btn-action btn-preview" id="btn-preview-invoice" style="flex: 1; height: 45px; background: #3b82f6; border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(59, 130, 246, 0.3); display: none; align-items: center; justify-content: center;" onmouseover="this.style.background='#2563eb'; this.style.boxShadow='0 4px 15px rgba(59, 130, 246, 0.5)';" onmouseout="this.style.background='#3b82f6'; this.style.boxShadow='0 2px 10px rgba(59, 130, 246, 0.3)';" onclick="printCurrentInvoice()">👁️ معاينة</button>
                
                <!-- Email Button: Solid Orange -->
                <button type="button" class="btn-action btn-email" id="btn-email-invoice" style="flex: 1.2; height: 45px; background: #f59e0b; border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(245, 158, 11, 0.3); display: flex; align-items: center; justify-content: center; gap: 8px;" onmouseover="this.style.background='#d97706'; this.style.boxShadow='0 4px 15px rgba(245, 158, 11, 0.5)';" onmouseout="this.style.background='#f59e0b'; this.style.boxShadow='0 2px 10px rgba(245, 158, 11, 0.3)';" onclick="sendCurrentInvoiceEmail()">
                    <span style="font-size: 1.2rem;">📧</span>
                    <span>إيميل</span>
                </button>
                
                <!-- WhatsApp Button: Solid Green -->
                <button type="button" class="btn-action btn-whatsapp" id="btn-whatsapp-invoice" style="flex: 1.2; height: 45px; background: #25d366; border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(37, 211, 102, 0.3); display: flex; align-items: center; justify-content: center; gap: 8px;" onmouseover="this.style.background='#1da851'; this.style.boxShadow='0 4px 15px rgba(37, 211, 102, 0.5)';" onmouseout="this.style.background='#25d366'; this.style.boxShadow='0 2px 10px rgba(37, 211, 102, 0.3)';" onclick="sendCurrentInvoiceWA()">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    <span>واتساب</span>
                </button>
                
                <!-- Submit Button: Solid Blue Gradient -->
                <span style="display:none;" id="footer-grand"></span>
                <button class="btn-action btn-submit" style="flex: 1.5; height: 45px; box-shadow: 0 2px 10px rgba(56, 189, 248, 0.4); background: linear-gradient(135deg, #0ea5e9, #2563eb); color: #fff; border: none; font-size: 1.1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s;" onmouseover="this.style.boxShadow='0 6px 20px rgba(56, 189, 248, 0.6)';" onmouseout="this.style.boxShadow='0 2px 10px rgba(56, 189, 248, 0.4)';" onclick="submitInvoice()">✅ تأكيد وحفظ</button>
            </div>
        </div>

        <!-- Hidden Adjustments & Notes -->
        <div id="adjustments-panel" style="display: none;">
            <input type="hidden" id="inv-notes" value="">
            <input type="text" id="discount-amt" value="0" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" >
            <input type="number" id="discount-pct" value="0">
            <input type="text" id="tax-amt" value="0" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" >
            <input type="number" id="tax-pct" value="0">
            <input type="text" id="addition-amt" value="0" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" >
            <input type="number" id="addition-pct" value="0">
        </div>

    </div> <!-- End purchase-bottom -->

    </div>
</div>
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/html2pdf.bundle.min.js"></script>
    <script defer src="js/whatsapp-integration.js"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1.0.0'; ?>"></script>
    <script defer src="js/print-engine.js"></script>
    
    <script defer src="js/purchase-pro-v4.js?v=<?php echo file_exists('js/purchase-pro-v4.js') ? filemtime('js/purchase-pro-v4.js') : '1.0.0'; ?>"></script>
</div>

    <script>
        // Real-time synchronization of local and foreign adjustments
        window.syncFc = function(name) {
            let localId = '';
            let fcId = '';
            if (name === 'transport') { localId = 't-transport'; fcId = 't-transport-fc'; }
            else if (name === 'labor') { localId = 't-labor'; fcId = 't-labor-fc'; }
            else if (name === 'additions') { localId = 't-additions-loc'; fcId = 't-additions-fc'; }
            else if (name === 'tax') { localId = 't-tax-loc'; fcId = 't-tax-fc'; }
            else if (name === 'discount') { localId = 't-discount-loc'; fcId = 't-discount-fc'; }

            if (!localId || !fcId) return;

            const rateInput = document.getElementById('hdr-exchange-rate');
            const rate = rateInput ? (parseFloat(rateInput.value) || 1) : 1;
            const localEl = document.getElementById(localId);
            const fcEl = document.getElementById(fcId);
            
            if (localEl && fcEl) {
                const pNum = typeof window.parseNum === 'function' ? window.parseNum : function(v) { return parseFloat(v.toString().replace(/,/g, '')) || 0; };
                const fNum = typeof window.fmtNum === 'function' ? window.fmtNum : function(v) { return v % 1 === 0 ? v : parseFloat(v.toFixed(4)); };
                
                const localVal = pNum(localEl.value);
                const fcVal = rate > 0 ? (localVal / rate) : 0;
                fcEl.value = fNum(fcVal);
                if (typeof window.recalcAll === 'function') window.recalcAll();
            }
        };

        window.syncLocal = function(name) {
            let localId = '';
            let fcId = '';
            if (name === 'transport') { localId = 't-transport'; fcId = 't-transport-fc'; }
            else if (name === 'labor') { localId = 't-labor'; fcId = 't-labor-fc'; }
            else if (name === 'additions') { localId = 't-additions-loc'; fcId = 't-additions-fc'; }
            else if (name === 'tax') { localId = 't-tax-loc'; fcId = 't-tax-fc'; }
            else if (name === 'discount') { localId = 't-discount-loc'; fcId = 't-discount-fc'; }

            if (!localId || !fcId) return;

            const rateInput = document.getElementById('hdr-exchange-rate');
            const rate = rateInput ? (parseFloat(rateInput.value) || 1) : 1;
            const localEl = document.getElementById(localId);
            const fcEl = document.getElementById(fcId);
            
            if (localEl && fcEl) {
                const pNum = typeof window.parseNum === 'function' ? window.parseNum : function(v) { return parseFloat(v.toString().replace(/,/g, '')) || 0; };
                const fNum = typeof window.fmtNum === 'function' ? window.fmtNum : function(v) { return v % 1 === 0 ? v : parseFloat(v.toFixed(4)); };
                
                const fcVal = pNum(fcEl.value);
                const localVal = fcVal * rate;
                localEl.value = fNum(localVal);
                if (typeof window.recalcAll === 'function') window.recalcAll();
            }
        };

        // Purchase Hub Logic
        let currentPurchaseView = 'hub';

        function handleCurrencyChange(val) {
            const rateInput = document.getElementById('hdr-exchange-rate');
            if (!rateInput) return;
            
            if (val === 'SDG') {
                rateInput.value = '1';
                rateInput.disabled = true;
                if (typeof recalcAll === 'function') recalcAll();
                return;
            }
            
            // Always enable editing for foreign currencies
            rateInput.disabled = false;
            
            // Step 1: Try to get rate from local settings function
            let rate = 0;
            if (window.getExchangeRateForCurrency) {
                rate = window.getExchangeRateForCurrency(val);
            }
            
            if (rate > 0) {
                rateInput.value = rate;
                if (typeof recalcAll === 'function') recalcAll();
                return;
            }
            
            // Step 2: Try to read directly from localStorage settings (any mode)
            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                if (val === 'USD' && cfg.rateUSD) { rateInput.value = cfg.rateUSD; if (typeof recalcAll === 'function') recalcAll(); return; }
                if (val === 'SAR' && cfg.rateSAR) { rateInput.value = cfg.rateSAR; if (typeof recalcAll === 'function') recalcAll(); return; }
                if (val === 'AED' && cfg.rateAED) { rateInput.value = cfg.rateAED; if (typeof recalcAll === 'function') recalcAll(); return; }
            } catch(e) {}
            
            // Step 3: Try to fetch from API directly (central rate)
            rateInput.value = '';
            rateInput.placeholder = '⏳ جاري التحميل...';
            
            fetch('api_index.php/exchange-rate')
                .then(r => r.json())
                .then(data => {
                    if (data && data.rate) {
                        const usdRate = parseFloat(data.rate);
                        if (usdRate > 0) {
                            window._cachedGlobalRate = usdRate;
                            let finalRate = usdRate;
                            if (val === 'SAR') finalRate = parseFloat((usdRate / 3.75).toFixed(4));
                            if (val === 'AED') finalRate = parseFloat((usdRate / 3.67).toFixed(4));
                            if (val === 'EGP') finalRate = parseFloat((usdRate / 50.0).toFixed(4));
                            
                            rateInput.value = finalRate;
                            if (typeof recalcAll === 'function') recalcAll();
                            return;
                        }
                    }
                    // No rate found from API either
                    rateInput.value = '';
                    rateInput.placeholder = 'أدخل سعر الصرف';
                })
                .catch(() => {
                    rateInput.value = '';
                    rateInput.placeholder = 'أدخل سعر الصرف';
                });
        }

        document.addEventListener('DOMContentLoaded', () => {
            // No need for auto-init - currency defaults to SDG
            // handleCurrencyChange only needed when user explicitly changes currency
        });

        function showPurchaseHub() {
            document.getElementById('purchase-editor-view').style.display = 'none';
            document.getElementById('purchase-hub-view').style.display = 'flex';
            currentPurchaseView = 'hub';
            loadHubTransactions();
            
            const dTitle = document.getElementById('default-header-title');
            const eCtrls = document.getElementById('editor-header-controls');
            if (dTitle) dTitle.style.display = 'flex';
            if (eCtrls) eCtrls.style.display = 'none';
            
            const backBtn = document.querySelector('button[title="رجوع"]');
            if (backBtn) {
                backBtn.onclick = function() { 
                    if (document.referrer && document.referrer.indexOf('general-ledger.php') !== -1) { 
                        history.back(); 
                    } else { 
                        window.location.href = 'dashboard-pro.php'; 
                    } 
                };
                backBtn.innerHTML = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"></path><polyline points="12 19 5 12 12 5"></polyline></svg>';
            }
        }

        function openPurchaseEditor(type = 'direct', isEdit = false) {
            document.getElementById('purchase-hub-view').style.display = 'none';
            document.getElementById('purchase-editor-view').style.display = 'flex';
            currentPurchaseView = 'editor';
            
            const dTitle = document.getElementById('default-header-title');
            const eCtrls = document.getElementById('editor-header-controls');
            if (dTitle) dTitle.style.display = 'none';
            if (eCtrls) eCtrls.style.display = 'flex';
            
            const backBtn = document.querySelector('button[title="رجوع"]');
            if (backBtn) {
                backBtn.onclick = function() { showPurchaseHub(); };
                backBtn.innerHTML = '<span style="font-weight:bold; font-size:0.8rem; padding: 0 4px;">العودة للقائمة</span>';
                backBtn.style.width = 'auto';
                backBtn.style.padding = '0 10px';
            }

            setSaleType(type);
            
            if (!isEdit) {
                if (typeof onSuccessNewInvoice === 'function') {
                    onSuccessNewInvoice(false);
                }
            }
            
            window.dispatchEvent(new Event('resize'));
        }

        function loadHubTransactions() {
            const tbody = document.getElementById('hub-transactions-body');
            const emptyState = document.getElementById('hub-empty-state');
            
            const savedData = localStorage.getItem('erp_purchase_invoices_v1');
            let invoices = [];
            if (savedData) {
                try { 
                    invoices = JSON.parse(savedData); 
                    if (Array.isArray(invoices)) {
                        invoices = invoices.filter(inv => !inv.deleted && !inv.isGRN && !inv.isReturn);
                    }
                } catch(e) {}
            }
            
            if (!invoices || invoices.length === 0) {
                tbody.innerHTML = '';
                emptyState.style.display = 'flex';
                return;
            }
            
            // Load suppliers to map IDs to names
            let suppliers = [];
            try {
                let rawSupps = localStorage.getItem('erp_suppliers_v1');
                if (typeof rawSupps === 'string') suppliers = JSON.parse(rawSupps) || [];
                else if (rawSupps && typeof rawSupps === 'object') suppliers = rawSupps;
            } catch(e) {}

            function getSupplierName(id) {
                const s = suppliers.find(x => String(x.id) === String(id));
                return s ? s.name : id;
            }

            // Load warehouses to map IDs to names
            let warehouses = [];
            try {
                let rawWh = localStorage.getItem('erp_warehouses_v1');
                if (typeof rawWh === 'string') warehouses = JSON.parse(rawWh) || [];
                else if (rawWh && typeof rawWh === 'object') warehouses = rawWh;
            } catch(e) {}

            function getWarehouseName(id) {
                if (id === 'PENDING_RECEIPT' || !id) {
                    return '<span style="color: #f97316; font-weight: 700;">قيد الاستلام</span>';
                }
                const w = warehouses.find(x => x.id === id);
                return w ? w.name : id;
            }

            // Load items to map IDs to names
            let items = [];
            try {
                let rawItems = localStorage.getItem('erp_items_v1');
                if (typeof rawItems === 'string') items = JSON.parse(rawItems) || [];
                else if (rawItems && typeof rawItems === 'object') items = rawItems;
            } catch(e) {}

            function getItemName(id) {
                const item = items.find(x => x.id === id);
                return item ? item.name : id || 'صنف غير معروف';
            }

            emptyState.style.display = 'none';
            tbody.innerHTML = '';
            
            invoices.sort((a,b) => {
                const dateA = new Date(a.timestamp || a.date || 0).getTime();
                const dateB = new Date(b.timestamp || b.date || 0).getTime();
                return (isNaN(dateB) ? 0 : dateB) - (isNaN(dateA) ? 0 : dateA);
            });
            
            const fragment = document.createDocumentFragment();
            invoices.forEach(inv => {
                const tr = document.createElement('tr');
                
                let itemText = 'بدون أصناف';
                let qtyText = '0';
                let priceText = '0';
                
                const lines = inv.items || [];
                if (lines.length === 1) {
                    const line = lines[0];
                    itemText = line.itemName || line.name || getItemName(line.itemId);
                    qtyText = fmtNum(line.quantity || 0);
                    priceText = fmtNum(line.price || 0) + ' SDG';
                } else if (lines.length > 1) {
                    itemText = lines.length + ' أصناف';
                    let totalQty = 0;
                    lines.forEach(l => {
                        totalQty += parseFloat(l.quantity) || 0;
                    });
                    qtyText = fmtNum(totalQty);
                    priceText = '—';
                }

                tr.innerHTML = `
                    <td style="text-align:right; font-weight:800; color:var(--gold-primary);">${inv.invoiceNo}</td>
                    <td style="text-align:right; color:var(--text-dim);">${new Date(inv.timestamp || inv.date).toLocaleString('ar-EG-u-nu-latn')}</td>
                    <td style="text-align:right; font-weight:600;">${inv.supplierName || getSupplierName(inv.customer || inv.customerId || inv.supplierId) || 'مورد غير محدد'}</td>
                    <td style="text-align:right; color:var(--text-dim);">${itemText}</td>
                    <td style="text-align:center; color:var(--text-dim);">${qtyText}</td>
                    <td style="text-align:center; color:var(--text-dim);">${priceText}</td>
                    <td style="text-align:center; font-weight:900; direction:ltr; color:#fff;">${fmtNum(inv.totals?.grandTotal || inv.grandTotal || 0)} <span style="color:#a855f7; font-weight:700;">SDG</span></td>
                    <td style="text-align:center; font-weight:600; color:var(--text-dim);">${getWarehouseName(inv.warehouse)}</td>
                    <td style="text-align:center;">
                        <button onclick="editHubTransaction('${inv.invoiceNo}')" style="background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.3); color:#38bdf8; border-radius:6px; width:34px; height:34px; cursor:pointer; margin-left:4px; display:inline-flex; align-items:center; justify-content:center;" title="تعديل / عرض">👁️</button>
                        <button onclick="sendInvoiceWA('${inv.invoiceNo}')" style="background:rgba(37,211,102,0.1); border:1px solid rgba(37,211,102,0.3); color:#25d366; border-radius:6px; width:34px; height:34px; cursor:pointer; margin-left:4px; display:inline-flex; align-items:center; justify-content:center;" title="إرسال عبر الواتساب">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </button>
                        <button onclick="deleteHubTransaction('${inv.invoiceNo}')" style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#ef4444; border-radius:6px; width:34px; height:34px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;" title="حذف">🗑️</button>
                    </td>
                `;
                fragment.appendChild(tr);
            });
            tbody.appendChild(fragment);
        }
        
        function filterHubTransactions() {
            const query = document.getElementById('hub-search').value.toLowerCase();
            const rows = document.querySelectorAll('#hub-transactions-body tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        }

        function editHubTransaction(invNo) {
            openPurchaseEditor('direct', true);
            if(typeof loadInvoiceFromStorage === 'function') {
                setTimeout(() => loadInvoiceFromStorage(invNo), 100);
            }
        }
        
        function deleteHubTransaction(invNo) {
            if(confirm('هل أنت متأكد من حذف الفاتورة ' + invNo + ' نهائياً؟')) {
                let savedData = localStorage.getItem('erp_purchase_invoices_v1');
                if (savedData) {
                    let invoices = JSON.parse(savedData);
                    invoices = invoices.filter(i => i.invoiceNo !== invNo);
                    localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(invoices));
                    showToast('تم حذف الفاتورة بنجاح', 'success');
                    loadHubTransactions();
                }
            }
        }

        document.addEventListener('DBReady', () => {
            setTimeout(() => {
                showPurchaseHub();
            }, 50);
        });
    </script>
    <script>
async function sendInvoiceWA(invoiceNo) {
    try {
        const userId = typeof getWAUserId === 'function' ? getWAUserId() : localStorage.getItem('erp_wa_user_id');
        const res = await fetch(`http://localhost:3001/wa/status?userId=${userId}`);
        const data = await res.json();
        
        if (data.status !== 'CONNECTED' && data.status !== 'AUTHENTICATED') {
            const wantsToConnect = typeof showWAConnectPrompt === 'function' ? await showWAConnectPrompt() : confirm('رقم الواتساب غير مربوط. هل تريد الربط الآن؟');
            if (wantsToConnect) {
                if (typeof openWAModal === 'function') {
                    openWAModal();
                } else {
                    alert('يرجى الذهاب إلى شاشة الإعدادات لربط الواتساب.');
                }
            }
            return;
        }
    } catch (err) {
        alert('خدمة الواتساب غير متاحة حالياً. تأكد من تشغيل الخادم.');
        return;
    }

    let allSales = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
    let inv = allSales.find(i => i.invoiceNo === invoiceNo);
    if (!inv) { alert('لم يتم العثور على الفاتورة'); return; }

    let defaultPhone = '';
    let supplierName = inv.supplierName || (typeof getSupplierName === 'function' ? getSupplierName(inv.customer || inv.customerId || inv.supplierId) : '') || 'مورد غير محدد';
    const supplierId = inv.customer || inv.customerId || inv.supplierId;
    if (supplierId && supplierId !== 'walk-in') {
        let suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
        let sup = suppliers.find(s => String(s.id) === String(supplierId));
        if(sup) {
            defaultPhone = sup.whatsapp || sup.phone || '';
            supplierName = sup.name || supplierName;
        }
    }

    const phone = await new Promise((resolve) => {
        const overlayId = 'wa-phone-prompt-overlay';
        if (document.getElementById(overlayId)) document.getElementById(overlayId).remove();

        window.toggleEditPhone = function() {
            const displayWrap = document.getElementById('wa-phone-display-wrap');
            const inputWrap = document.getElementById('wa-phone-input-wrap');
            if (displayWrap && inputWrap) {
                if (displayWrap.style.display !== 'none') {
                    displayWrap.style.display = 'none';
                    inputWrap.style.display = 'block';
                    const inp = document.getElementById('wa-prompt-input');
                    if (inp) {
                        inp.focus();
                        inp.select();
                    }
                } else {
                    displayWrap.style.display = 'flex';
                    inputWrap.style.display = 'none';
                }
            }
        };
        
        const html = `
        <div id="${overlayId}" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.8); z-index:999999; display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity 0.3s ease; backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px);">
            <div style="background:linear-gradient(145deg, rgba(30,41,59,0.9), rgba(15,23,42,0.95)); border:1px solid rgba(37,211,102,0.4); border-radius:24px; padding:35px; width:90%; max-width:450px; text-align:center; transform:translateY(40px) scale(0.9); transition:all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); box-shadow:0 25px 50px -12px rgba(37,211,102,0.2), inset 0 1px 0 rgba(255,255,255,0.1);">
                
                <div style="width:80px; height:80px; background:linear-gradient(135deg, #25D366, #128C7E); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px auto; color:#fff; box-shadow:0 10px 20px rgba(37,211,102,0.4); border:3px solid rgba(255,255,255,0.2);">
                    <svg viewBox="0 0 24 24" width="45" height="45" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                
                <h3 style="color:#f8fafc; font-size:1.6rem; font-family:'Tajawal',sans-serif; margin-bottom:10px; margin-top:0; font-weight:800;">إرسال الفاتورة عبر واتساب</h3>
                <p style="color:#94a3b8; font-size:0.95rem; margin-bottom:20px; line-height:1.6; font-family:'Tajawal',sans-serif;">يرجى تأكيد إرسال الفاتورة إلى المورد:</p>
                
                <!-- Display card -->
                <div id="wa-phone-display-wrap" style="display: flex; align-items: center; justify-content: space-between; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; padding: 15px 20px; margin-bottom: 25px; text-align: right;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <span style="font-size: 0.85rem; color: #94a3b8; font-weight: 500;">👤 مورد الفاتورة:</span>
                        <span style="font-size: 1.15rem; color: #25d366; font-weight: 800;">${supplierName}</span>
                        <span style="font-size: 0.95rem; color: #38bdf8; font-weight: 700; direction: ltr; margin-top: 4px; display: inline-flex; align-items: center; gap: 5px;">
                            <span>📞</span> <span id="wa-display-phone-text">${defaultPhone || 'لا يوجد رقم مسجل'}</span>
                        </span>
                    </div>
                    <button type="button" onclick="toggleEditPhone()" style="background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.2); color: #38bdf8; border-radius: 12px; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(56, 189, 248, 0.2)'; this.style.transform='scale(1.05)';" onmouseout="this.style.background='rgba(56, 189, 248, 0.1)'; this.style.transform='scale(1)';" title="إرسال لرقم آخر">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Input container (Hidden by default) -->
                <div id="wa-phone-input-wrap" style="display: none; margin-bottom: 25px; animation: wa-scale-up 0.3s ease-out; text-align: right;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 0.85rem; color: #94a3b8; font-weight: 500;">✏️ إرسال إلى رقم هاتف آخر:</span>
                        <button type="button" onclick="toggleEditPhone()" style="background: transparent; border: none; color: #cbd5e1; font-size: 0.75rem; cursor: pointer; text-decoration: underline; outline: none;">رجوع</button>
                    </div>
                    <div style="position: relative;">
                        <input type="tel" id="wa-prompt-input" placeholder="رقم الهاتف الجديد..." value="${defaultPhone}" oninput="document.getElementById('wa-display-phone-text').textContent = this.value;" style="width:100%; background:rgba(0,0,0,0.4); border:2px solid rgba(255,255,255,0.1); color:#fff; padding:12px 15px; border-radius:12px; font-size:1.15rem; text-align:center; transition:all 0.3s; outline:none; font-family:'Inter',sans-serif;" onfocus="this.style.borderColor='#25d366'; this.style.boxShadow='0 0 12px rgba(37,211,102,0.2)';" onblur="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.boxShadow='none';">
                    </div>
                </div>

                <div style="display:flex; gap:15px; justify-content:center;">
                    <button id="wa-prompt-send" style="background:linear-gradient(135deg, #25d366, #1da851); color:#fff; border:none; padding:12px 30px; border-radius:12px; font-weight:bold; font-size:1.1rem; cursor:pointer; font-family:'Tajawal',sans-serif; transition:all 0.3s; box-shadow:0 4px 15px rgba(37,211,102,0.4); display:flex; align-items:center; gap:8px;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(37,211,102,0.5)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(37,211,102,0.4)';">
                        <span>إرسال</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                    <button id="wa-prompt-cancel" style="background:rgba(255,255,255,0.05); color:#cbd5e1; border:1px solid rgba(255,255,255,0.1); padding:12px 25px; border-radius:12px; font-weight:bold; font-size:1.1rem; cursor:pointer; font-family:'Tajawal',sans-serif; transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">إلغاء</button>
                </div>
            </div>
        </div>
        `;
        document.body.insertAdjacentHTML('beforeend', html);
        
        const overlay = document.getElementById(overlayId);
        const modal = overlay.children[0];
        const input = document.getElementById('wa-prompt-input');
        input.value = defaultPhone;

        // Animate in
        requestAnimationFrame(() => {
            overlay.style.opacity = '1';
            modal.style.transform = 'translateY(0) scale(1)';
            setTimeout(() => input.focus(), 400); // Focus input after animation
        });

        const closePrompt = (resultVal) => {
            overlay.style.opacity = '0';
            modal.style.transform = 'translateY(40px) scale(0.9)';
            setTimeout(() => {
                overlay.remove();
                resolve(resultVal);
            }, 400);
        };

        document.getElementById('wa-prompt-send').onclick = () => closePrompt(input.value.trim());
        document.getElementById('wa-prompt-cancel').onclick = () => closePrompt(null);
        input.addEventListener('keyup', (e) => {
            if(e.key === 'Enter') closePrompt(input.value.trim());
        });
    });

    if (!phone) return;

    const htmlContent = window.InvoicePrinter.buildHTML(inv, 'نسخة العميل');
    
    const printContainer = document.createElement('div');
    printContainer.className = 'print-preview-wrapper';
    printContainer.style.position = 'fixed';
    printContainer.style.top = '0';
    printContainer.style.left = '0';
    printContainer.style.width = '800px';
    printContainer.style.zIndex = '-99999';
    printContainer.style.opacity = '0.01';
    printContainer.style.pointerEvents = 'none';
    printContainer.style.padding = '24px';
    printContainer.style.background = '#f0f4f8';
    
    printContainer.innerHTML = htmlContent;
    document.body.appendChild(printContainer);

    const message = 'مرحباً، مرفق فاتورتكم المرجعية رقم ' + invoiceNo + ' من ${JSON.parse(localStorage.getItem('user'))?.company_name || 'مؤسستنا'}.';
    
    try {
        await sendPDFViaWhatsApp(printContainer, phone, message);
    } catch (e) {
        console.error(e);
        printContainer.remove();
    }
}

function updateActionButtonsVisibility() {
    const previewBtn = document.getElementById('btn-preview-invoice');
    const waBtn = document.getElementById('btn-whatsapp-invoice');
    const emailBtn = document.getElementById('btn-email-invoice');
    if (currentViewedInvoiceNo) {
        if (previewBtn) previewBtn.style.display = 'flex';
    } else {
        if (previewBtn) previewBtn.style.display = 'none';
    }
    // WhatsApp and Email are now permanently visible as per user request
}

async function sendCurrentInvoiceWA() {
    if (currentViewedInvoiceNo) {
        await sendInvoiceWA(currentViewedInvoiceNo);
    } else {
        showToast('⚠️ يرجى حفظ وتأكيد الفاتورة أولاً', 'warning');
    }
}

async function sendCurrentInvoiceEmail() {
    if (currentViewedInvoiceNo) {
        showToast('جاري تجهيز الفاتورة للإرسال عبر البريد الإلكتروني...', 'success');
        // Fallback for Email if no backend email API exists
        const mailtoLink = `mailto:?subject=فاتورة مشتريات رقم ${currentViewedInvoiceNo}&body=مرحباً، مرفق تفاصيل الفاتورة.`;
        window.location.href = mailtoLink;
    } else {
        showToast('⚠️ يرجى حفظ وتأكيد الفاتورة أولاً', 'warning');
    }
}
</script>

<!-- Barcode Scanner Modal -->
<div id="barcode-scanner-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); backdrop-filter: blur(8px); z-index: 999999; justify-content: center; align-items: center; direction: rtl;">
    <div class="glass-panel" style="width: 90%; max-width: 480px; padding: 25px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); position: relative; text-align: center; background: rgba(30,41,59,0.9);">
        <button onclick="stopCameraScanner()" style="position: absolute; top: 15px; left: 15px; background: none; border: none; color: #f43f5e; font-size: 1.8rem; cursor: pointer;">&times;</button>
        <h3 style="color: #38bdf8; margin-bottom: 15px; font-weight: 800;">📷 قارئ الباركود عبر الكاميرا</h3>
        <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 20px;">وجه مربع الكاميرا نحو الرمز الشريطي (Barcode) للمنتج لقراءته تلقائياً.</p>
        <div id="scanner-viewport" style="width: 100%; aspect-ratio: 4/3; background: #000; border-radius: 12px; overflow: hidden; border: 2px solid rgba(56,189,248,0.4); position: relative;"></div>
        <div id="scanner-status" style="margin-top: 15px; color: #fbbf24; font-weight: bold; font-size: 0.9rem;">جاري تهيئة الكاميرا...</div>
    </div>
</div>

<script>
let html5QrcodeScanner = null;

function startCameraScanner(targetInputId) {
    document.getElementById('barcode-scanner-modal').style.display = 'flex';
    document.getElementById('scanner-status').innerText = 'جاري تشغيل الكاميرا...';
    document.getElementById('scanner-status').style.color = '#fbbf24';

    loadScannerLibrary(() => {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear().catch(() => {});
        }
        
        html5QrcodeScanner = new Html5Qrcode("scanner-viewport");
        const config = { fps: 15, qrbox: { width: 280, height: 180 } };
        
        html5QrcodeScanner.start(
            { facingMode: "environment" },
            config,
            (decodedText, decodedResult) => {
                const items = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
                const matchedItem = items.find(item => {
                    if (item.barcode === decodedText) return true;
                    if (item.units && Array.isArray(item.units)) {
                        return item.units.some(u => u.barcode === decodedText);
                    }
                    return false;
                });
                
                if (matchedItem) {
                    playScanBeep();
                    if (typeof selectQuickItem === 'function') {
                        selectQuickItem(matchedItem.id);
                    } else {
                        const input = document.getElementById(targetInputId);
                        if (input) {
                            input.value = decodedText;
                            input.dispatchEvent(new Event('input'));
                        }
                    }
                    stopCameraScanner();
                } else {
                    playScanBeep();
                    const input = document.getElementById(targetInputId);
                    if (input) {
                        input.value = decodedText;
                        input.dispatchEvent(new Event('input'));
                    }
                    alert(`⚠️ لم يتم العثور على صنف بالباركود: ${decodedText}`);
                    stopCameraScanner();
                }
            },
            (errorMessage) => {}
        ).then(() => {
            document.getElementById('scanner-status').innerText = '🟢 الكاميرا متصلة وتعمل حالياً';
            document.getElementById('scanner-status').style.color = '#34d399';
        }).catch(err => {
            document.getElementById('scanner-status').innerText = '❌ تعذر الوصول للكاميرا: يرجى إعطاء الصلاحية';
            document.getElementById('scanner-status').style.color = '#f43f5e';
            console.error("Camera access failed", err);
        });
    });
}

function stopCameraScanner() {
    document.getElementById('barcode-scanner-modal').style.display = 'none';
    if (html5QrcodeScanner) {
        html5QrcodeScanner.stop().then(() => {
            return html5QrcodeScanner.clear();
        }).catch(err => {
            console.error("Failed to stop scanner", err);
        }).finally(() => {
            html5QrcodeScanner = null;
        });
    }
}

function loadScannerLibrary(callback) {
    if (window.Html5Qrcode) {
        callback();
        return;
    }
    const script = document.createElement('script');
    script.src = 'https://unpkg.com/html5-qrcode';
    script.onload = callback;
    document.head.appendChild(script);
}

function playScanBeep() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(1000, audioCtx.currentTime);
        gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.15);
    } catch(e) {
        console.error("Failed to play audio", e);
    }
}
</script>
<!-- ═══ ADVANCED COLUMN SETTINGS MODAL ═══ -->
<div class="modal-overlay" id="advanced-col-settings-modal">
    <div class="modal-card" style="max-width: 450px;">
        <div class="modal-header">
            <h3>⚙️ إعدادات العمود المتقدمة</h3>
            <button class="modal-close" onclick="closeModal('advanced-col-settings-modal')" style="color:#fff;">✕</button>
        </div>
        <div class="modal-body" style="display: flex; flex-direction: column; gap: 12px; max-height: 60vh; overflow-y: auto;">
            <input type="hidden" id="edit-col-id">
            
            <div class="v-form-group">
                <label class="v-label">الاسم المخصص (الظاهر للعميل / المستخدم)</label>
                <input type="text" class="v-input" id="edit-col-alias" placeholder="الاسم..." oninput="if(typeof previewAdvancedColSettings === 'function') previewAdvancedColSettings()">
            </div>
            
            <div style="display: flex; gap: 10px;">
                <div class="v-form-group" style="flex: 1;">
                    <label class="v-label">لون النص</label>
                    <input type="color" class="v-input" id="edit-col-color" style="height: 40px; padding: 2px;" oninput="if(typeof previewAdvancedColSettings === 'function') previewAdvancedColSettings()">
                </div>
                <div class="v-form-group" style="flex: 1;">
                    <label class="v-label">لون الخلفية</label>
                    <input type="color" class="v-input" id="edit-col-bgcolor" style="height: 40px; padding: 2px;" oninput="if(typeof previewAdvancedColSettings === 'function') previewAdvancedColSettings()">
                </div>
                <div class="v-form-group" style="flex: 1;">
                    <label class="v-label">حجم الخط</label>
                    <input type="text" class="v-input" id="edit-col-fontsize" placeholder="مثال: 0.95rem" oninput="if(typeof previewAdvancedColSettings === 'function') previewAdvancedColSettings()">
                </div>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <div class="v-form-group" style="flex: 1;">
                    <label class="v-label">سماكة الخط</label>
                    <select class="v-input" id="edit-col-fontweight" onchange="if(typeof previewAdvancedColSettings === 'function') previewAdvancedColSettings()">
                        <option value="400">عادي (Normal)</option>
                        <option value="700">عريض (Bold)</option>
                        <option value="900">عريض جداً (Extra Bold)</option>
                    </select>
                </div>
                <div class="v-form-group" style="flex: 1;">
                    <label class="v-label">المحاذاة</label>
                    <select class="v-input" id="edit-col-align" onchange="if(typeof previewAdvancedColSettings === 'function') previewAdvancedColSettings()">
                        <option value="right">يمين</option>
                        <option value="center">وسط</option>
                        <option value="left">يسار</option>
                    </select>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <div class="v-form-group" style="flex: 1;">
                    <label class="v-label">الخانات العشرية (للأرقام)</label>
                    <input type="number" min="0" max="4" class="v-input" id="edit-col-decimals" placeholder="0" oninput="if(typeof previewAdvancedColSettings === 'function') previewAdvancedColSettings()">
                </div>
                <div class="v-form-group" style="flex: 1;">
                    <label class="v-label">الفاصلة الألفية</label>
                    <select class="v-input" id="edit-col-thousands" onchange="if(typeof previewAdvancedColSettings === 'function') previewAdvancedColSettings()">
                        <option value=",">فاصلة ( , )</option>
                        <option value=".">نقطة ( . )</option>
                        <option value="">بدون فاصلة</option>
                    </select>
                </div>
                <div class="v-form-group" style="flex: 1;">
                    <label class="v-label">عرض العمود (المساحة)</label>
                    <input type="text" class="v-input" id="edit-col-width" placeholder="مثال: 120px" oninput="if(typeof previewAdvancedColSettings === 'function') previewAdvancedColSettings()">
                </div>
            </div>
            
            <div style="margin-top: 5px; margin-bottom: 5px; padding: 10px; background: rgba(251, 191, 36, 0.1); border-radius: 8px; border: 1px dashed rgba(251, 191, 36, 0.5);">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; color: #fbbf24; cursor: pointer; font-weight: bold;">
                    <input type="checkbox" id="edit-col-apply-all" style="width: 18px; height: 18px;" onchange="if(typeof previewAdvancedColSettings === 'function') previewAdvancedColSettings()">
                    <span>تطبيق هذه التنسيقات (اللون، الخط، المساحة) على جميع الأعمدة حفظاً دائماً</span>
                </label>
            </div>
            
            <hr style="border-color: rgba(255,255,255,0.1); margin: 10px 0;">
            <h4 style="color: #38bdf8; margin-bottom: 5px;">👁️ إظهار / إخفاء جميع الأعمدة</h4>
            <div id="global-col-visibility-list" style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 8px;">
                <!-- Populated dynamically -->
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-action btn-draft" style="width:auto; padding:8px 16px;" onclick="if(typeof cancelAdvancedColSettings === 'function') cancelAdvancedColSettings(); else closeModal('advanced-col-settings-modal');">إلغاء</button>
            <button class="btn-action btn-submit" style="width:auto; padding:8px 16px;" onclick="saveAdvancedColSettings()">💾 حفظ جميع الإعدادات</button>
        </div>
    </div>
</div>

<script>
// Make the advanced settings modal draggable
const advancedModal = document.getElementById('advanced-col-settings-modal');
const advancedModalCard = advancedModal.querySelector('.modal-card');
const advancedModalHeader = advancedModal.querySelector('.modal-header');

let isDraggingModal = false;
let dragStartX, dragStartY, initialLeft, initialTop;

advancedModalHeader.style.cursor = 'move';

advancedModalHeader.addEventListener('mousedown', (e) => {
    isDraggingModal = true;
    dragStartX = e.clientX;
    dragStartY = e.clientY;
    
    const rect = advancedModalCard.getBoundingClientRect();
    initialLeft = rect.left;
    initialTop = rect.top;
    
    advancedModalCard.style.position = 'absolute';
    advancedModalCard.style.margin = '0';
    advancedModalCard.style.left = initialLeft + 'px';
    advancedModalCard.style.top = initialTop + 'px';
    advancedModalCard.style.transform = 'none';
    advancedModalCard.style.transition = 'none';
});

document.addEventListener('mousemove', (e) => {
    if (!isDraggingModal) return;
    
    const dx = e.clientX - dragStartX;
    const dy = e.clientY - dragStartY;
    
    advancedModalCard.style.left = (initialLeft + dx) + 'px';
    advancedModalCard.style.top = (initialTop + dy) + 'px';
});

document.addEventListener('mouseup', () => {
    if (isDraggingModal) {
        isDraggingModal = false;
        advancedModalCard.style.transition = ''; // restore transition
    }
});
</script>

<!-- Numeric Custom Context Menu -->
<div id="num-context-menu" class="glass-panel" style="
    display: none;
    position: fixed;
    z-index: 20000;
    width: 280px;
    padding: 15px;
    border-radius: 16px;
    background: rgba(15, 12, 22, 0.95);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    direction: rtl;
    font-family: 'Outfit', sans-serif;
">
    <div style="font-weight: bold; color: #38bdf8; margin-bottom: 12px; font-size: 0.9rem; border-bottom: 1px dashed rgba(255,255,255,0.15); padding-bottom: 5px; display:flex; align-items:center; gap:6px;">
        <span>⚙️</span> خصائص عرض الأرقام
    </div>
    
    <!-- Font Color Selector -->
    <div style="margin-bottom: 12px;">
        <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 6px;">لون النص:</div>
        <div style="display: flex; gap: 8px;">
            <button onclick="setNumStyle('color', '#ffffff')" style="width:20px; height:20px; border-radius:50%; background:#ffffff; border:1px solid #fff; cursor:pointer;" title="أبيض"></button>
            <button onclick="setNumStyle('color', '#38bdf8')" style="width:20px; height:20px; border-radius:50%; background:#38bdf8; border:none; cursor:pointer;" title="لبني"></button>
            <button onclick="setNumStyle('color', '#10b981')" style="width:20px; height:20px; border-radius:50%; background:#10b981; border:none; cursor:pointer;" title="أخضر"></button>
            <button onclick="setNumStyle('color', '#fbbf24')" style="width:20px; height:20px; border-radius:50%; background:#fbbf24; border:none; cursor:pointer;" title="أصفر"></button>
            <button onclick="setNumStyle('color', '#ef4444')" style="width:20px; height:20px; border-radius:50%; background:#ef4444; border:none; cursor:pointer;" title="أحمر"></button>
            <button onclick="setNumStyle('color', '#c084fc')" style="width:20px; height:20px; border-radius:50%; background:#c084fc; border:none; cursor:pointer;" title="بنفسجي"></button>
        </div>
    </div>

    <!-- Font Size Selector -->
    <div style="margin-bottom: 12px;">
        <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 6px;">حجم الخط:</div>
        <select onchange="setNumStyle('size', this.value)" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.15); color:#fff; border-radius:6px; padding:4px; font-size:0.8rem; outline:none;">
            <option value="">افتراضي</option>
            <option value="0.75rem">صغير جداً (0.75)</option>
            <option value="0.85rem">صغير (0.85)</option>
            <option value="1rem">متوسط (1.0)</option>
            <option value="1.15rem">كبير (1.15)</option>
            <option value="1.3rem">كبير جداً (1.3)</option>
        </select>
    </div>

    <!-- Font Family Selector -->
    <div style="margin-bottom: 12px;">
        <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 6px;">نوع الخط (أوضح 3 خطوط للأرقام):</div>
        <select onchange="setNumStyle('font', this.value)" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.15); color:#fff; border-radius:6px; padding:4px; font-size:0.8rem; outline:none;">
            <option value="Inter">1. Inter (خط واجهة مستخدم نقي)</option>
            <option value="Outfit">2. Outfit (هندسي عصري)</option>
            <option value="JetBrains Mono">3. JetBrains Mono (خطي مونو دقيق جداً للأرقام)</option>
        </select>
    </div>

    <!-- Thousands Separator -->
    <div style="margin-bottom: 12px;">
        <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 6px;">فاصل الآلاف:</div>
        <select onchange="setNumFormat('thousandSep', this.value)" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.15); color:#fff; border-radius:6px; padding:4px; font-size:0.8rem; outline:none;">
            <option value=",">فاصلة ( , )</option>
            <option value=" ">مسافة فارغة (   )</option>
            <option value="none">بدون فاصل</option>
        </select>
    </div>

    <!-- Decimal Separator -->
    <div style="margin-bottom: 12px;">
        <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 6px;">الفاصل العشري:</div>
        <select onchange="setNumFormat('decimalSep', this.value)" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.15); color:#fff; border-radius:6px; padding:4px; font-size:0.8rem; outline:none;">
            <option value=".">نقطة ( . )</option>
            <option value=",">فاصلة ( , )</option>
        </select>
    </div>

    <!-- Rounding Decimals -->
    <div style="margin-bottom: 5px;">
        <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 6px;">خانة التقريب العشري:</div>
        <select onchange="setNumFormat('decimals', parseInt(this.value))" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.15); color:#fff; border-radius:6px; padding:4px; font-size:0.8rem; outline:none;">
            <option value="0">بدون فواصل (0)</option>
            <option value="1">منزلة واحدة (1)</option>
            <option value="2">منزلتين (2)</option>
            <option value="3">3 منازل (3)</option>
            <option value="4">4 منازل (4)</option>
        </select>
    </div>
</div>

<script>
    function applyStylingPrefs() {
        const prefs = JSON.parse(localStorage.getItem('num_style_prefs') || '{}');
        const color = prefs.color || '';
        const size = prefs.size || '';
        const font = prefs.font || '';

        let css = '';
        if (color) {
            css += `.amount, .val, .g-input, .g-readonly, .ctrl-input, .tot-input { color: ${color} !important; }\n`;
        }
        if (size) {
            css += `.amount, .val, .g-input, .g-readonly, .ctrl-input, .tot-input { font-size: ${size} !important; }\n`;
        }
        if (font) {
            let fontStack = '';
            if (font === 'Inter') fontStack = "'Inter', sans-serif";
            else if (font === 'Outfit') fontStack = "'Outfit', sans-serif";
            else if (font === 'JetBrains Mono') fontStack = "'JetBrains Mono', monospace";
            
            if (fontStack) {
                css += `.amount, .val, .g-input, .g-readonly, .ctrl-input, .tot-input { font-family: ${fontStack} !important; }\n`;
            }
        }

        let styleEl = document.getElementById('custom-num-styles');
        if (!styleEl) {
            styleEl = document.createElement('style');
            styleEl.id = 'custom-num-styles';
            document.head.appendChild(styleEl);
        }
        styleEl.innerHTML = css;
    }

    window.setNumStyle = function(prop, val) {
        const prefs = JSON.parse(localStorage.getItem('num_style_prefs') || '{}');
        prefs[prop] = val;
        localStorage.setItem('num_style_prefs', JSON.stringify(prefs));
        applyStylingPrefs();
    };

    window.setNumFormat = function(prop, val) {
        const prefs = JSON.parse(localStorage.getItem('num_format_prefs') || '{}');
        prefs[prop] = val;
        localStorage.setItem('num_format_prefs', JSON.stringify(prefs));
        if (typeof window.recalcAll === 'function') window.recalcAll();
    };

    window.openNumContextMenu = function(e) {
        e.preventDefault();
        const menu = document.getElementById('num-context-menu');
        if (!menu) return;

        menu.style.display = 'block';
        
        let left = e.clientX;
        let top = e.clientY;
        if (left + 280 > window.innerWidth) left = window.innerWidth - 290;
        if (top + 380 > window.innerHeight) top = window.innerHeight - 390;
        
        menu.style.left = left + 'px';
        menu.style.top = top + 'px';

        const formatPrefs = JSON.parse(localStorage.getItem('num_format_prefs') || '{}');
        const stylePrefs = JSON.parse(localStorage.getItem('num_style_prefs') || '{}');

        const sizeSelect = menu.querySelector('select[onchange*="size"]');
        if (sizeSelect && stylePrefs.size !== undefined) sizeSelect.value = stylePrefs.size;

        const fontSelect = menu.querySelector('select[onchange*="font"]');
        if (fontSelect && stylePrefs.font !== undefined) fontSelect.value = stylePrefs.font;

        const thousandSelect = menu.querySelector('select[onchange*="thousandSep"]');
        if (thousandSelect && formatPrefs.thousandSep !== undefined) thousandSelect.value = formatPrefs.thousandSep;

        const decimalSelect = menu.querySelector('select[onchange*="decimalSep"]');
        if (decimalSelect && formatPrefs.decimalSep !== undefined) decimalSelect.value = formatPrefs.decimalSep;

        const decimalsSelect = menu.querySelector('select[onchange*="decimals"]');
        if (decimalsSelect && formatPrefs.decimals !== undefined) decimalsSelect.value = formatPrefs.decimals;
    };

    document.addEventListener('contextmenu', function(e) {
        const target = e.target;
        const isNumberElement = target.classList.contains('amount') || 
                                target.classList.contains('val') || 
                                target.classList.contains('g-input') || 
                                target.classList.contains('g-readonly') || 
                                target.classList.contains('ctrl-input') || 
                                target.classList.contains('tot-input') ||
                                target.id.startsWith('t-') ||
                                target.id.includes('grand') ||
                                /^[0-9,.\s\-+]+$/.test(target.textContent.trim()) ||
                                (target.value && /^[0-9,.\s\-+]+$/.test(target.value));

        if (isNumberElement) {
            openNumContextMenu(e);
        } else {
            const menu = document.getElementById('num-context-menu');
            if (menu) menu.style.display = 'none';
        }
    });

    document.addEventListener('click', function(e) {
        const menu = document.getElementById('num-context-menu');
        if (menu && !menu.contains(e.target)) {
            menu.style.display = 'none';
        }
    });

    applyStylingPrefs();
</script>

</body>
</html>

