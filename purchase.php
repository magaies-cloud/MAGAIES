<?php $page_title = 'استلام بضاعة'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استلام بضاعة | Al-Sajana ERP</title>
    <!-- We skip standard theme.css for this page to allow the unique Deep Velvet theme to take over -->
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <link rel="stylesheet" href="css/sales-invoice.css">
    <link rel="stylesheet" href="css/print-invoice.css">
    <style>
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

        
        @keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

        
        .premium-header {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.15), rgba(99, 102, 241, 0.15), rgba(236, 72, 153, 0.15), rgba(16, 185, 129, 0.15));
            background-size: 300% 300%;
            backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 20px; padding: 12px 20px;
            display: flex; justify-content: space-between; align-items: center;
            height: 110px; box-sizing: border-box;
            margin-bottom: 20px; width: 100%;
            box-shadow: 0 15px 30px rgba(0,0,0,0.4), inset 0 1px 1px rgba(255,255,255,0.2), inset 0 -1px 1px rgba(0,0,0,0.3);
            animation: slideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1), animateGradient 12s ease infinite;
            position: relative;
            overflow: hidden;
        }

        .premium-header::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 40%; height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.04) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-25deg);
            animation: shimmer 8s cubic-bezier(0.25, 0.8, 0.25, 1) infinite;
            pointer-events: none;
        }

        @keyframes animateGradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes shimmer {
            0% { left: -100%; }
            25% { left: 200%; }
            100% { left: 200%; }
        }

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

</style>

    <style>
        .hub-action-card {
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-top: 1px solid rgba(255, 255, 255, 0.15);
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
            background: rgba(255, 255, 255, 0.05);
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
            color: rgba(255, 255, 255, 0.6);
            font-weight: 500;
            text-align: right;
            margin: 0;
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
            --text-main: #0f172a;
            --text-secondary: #475569;
            --border-color: #e2e8f0;
        }
        :root.light-theme body { background-color: var(--bg-color); color: var(--text-main); }
        :root.light-theme .premium-header {
            background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(241,245,249,0.9));
            border: 1px solid rgba(0,0,0,0.1);
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }
        :root.light-theme .header-center h2 { background: linear-gradient(to right, #0f172a, #334155); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        :root.light-theme .brand-text h1 { color: #0f172a; text-shadow: none; }
        :root.light-theme .brand-text p { color: #475569; text-shadow: none; }
        :root.light-theme .icon-btn, :root.light-theme .user-info, :root.light-theme .clock-container { background: rgba(255,255,255,0.8); color: #0f172a; border-color: rgba(0,0,0,0.1); }
        :root.light-theme .icon-btn:hover { background: #f8fafc; color: #000; box-shadow: 0 5px 10px rgba(0,0,0,0.1); }
        :root.light-theme #header-user-name { color: #0f172a !important; }
        :root.light-theme .online-users { background: rgba(16,185,129,0.1) !important; border-color: rgba(16,185,129,0.2) !important; }
    </style>
    <script>
        if(localStorage.getItem('erp_theme') === 'light') document.documentElement.classList.add('light-theme');
        function toggleTheme() {
            document.documentElement.classList.toggle('light-theme');
            localStorage.setItem('erp_theme', document.documentElement.classList.contains('light-theme') ? 'light' : 'dark');
        }
    </script>
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
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-top: 1px solid rgba(255, 255, 255, 0.15);
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
            background: rgba(255, 255, 255, 0.05);
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
            color: rgba(255, 255, 255, 0.6);
            font-weight: 500;
            text-align: right;
            margin: 0;
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
            --text-main: #0f172a;
            --text-secondary: #475569;
            --border-color: #e2e8f0;
        }
        :root.light-theme body { background-color: var(--bg-color); color: var(--text-main); }
        :root.light-theme .premium-header {
            background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(241,245,249,0.9));
            border: 1px solid rgba(0,0,0,0.1);
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }
        :root.light-theme .header-center h2 { background: linear-gradient(to right, #0f172a, #334155); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        :root.light-theme .brand-text h1 { color: #0f172a; text-shadow: none; }
        :root.light-theme .brand-text p { color: #475569; text-shadow: none; }
        :root.light-theme .icon-btn, :root.light-theme .user-info, :root.light-theme .clock-container { background: rgba(255,255,255,0.8); color: #0f172a; border-color: rgba(0,0,0,0.1); }
        :root.light-theme .icon-btn:hover { background: #f8fafc; color: #000; box-shadow: 0 5px 10px rgba(0,0,0,0.1); }
        :root.light-theme #header-user-name { color: #0f172a !important; }
        :root.light-theme .online-users { background: rgba(16,185,129,0.1) !important; border-color: rgba(16,185,129,0.2) !important; }
    </style>
    <script>
        if(localStorage.getItem('erp_theme') === 'light') document.documentElement.classList.add('light-theme');
        function toggleTheme() {
            document.documentElement.classList.toggle('light-theme');
            localStorage.setItem('erp_theme', document.documentElement.classList.contains('light-theme') ? 'light' : 'dark');
        }
    </script>
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
</style>
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
    
    <?php include 'includes/header.php'; ?>

    <!-- ═══ PURCHASE HUB VIEW ═══ -->
    <div id="purchase-hub-view" style="display: flex; flex-direction: column; height: 100%; gap: 20px; animation: fadeIn 0.5s ease-out; padding: 10px 0; max-width: 100%; margin: 0; overflow-y: auto;">
        
        <!-- Action Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 25px;">


            <!-- Direct Purchase Invoice -->
            <button onclick="openPurchaseEditor('direct')" class="hub-action-card" style="background: linear-gradient(135deg, rgba(245,158,11,0.1), rgba(245,158,11,0.02)); border-color: rgba(245,158,11,0.2);">
                <div class="icon" style="color: #f59e0b; text-shadow: 0 0 10px rgba(245, 158, 11, 0.4); box-shadow: inset 0 0 10px rgba(245,158,11,0.1);">💵</div>
                <div class="text-content">
                    <div class="title" style="color: #f59e0b;">فاتورة مشتريات مباشرة</div>
                    <div class="subtitle">إنشاء فاتورة شراء نقدية مباشرة مع استلام فوري.</div>
                </div>
            </button>

            <!-- Goods Receipt Note -->
            <button onclick="window.location.href='purchase-receipt.php'" class="hub-action-card" style="background: linear-gradient(135deg, rgba(56,189,248,0.1), rgba(56,189,248,0.02)); border-color: rgba(56,189,248,0.2);">
                <div class="icon" style="color: #38bdf8; text-shadow: 0 0 10px rgba(56, 189, 248, 0.4); box-shadow: inset 0 0 10px rgba(56,189,248,0.1);">🚛</div>
                <div class="text-content">
                    <div class="title" style="color: #38bdf8;">استلام بضاعة (GRN)</div>
                    <div class="subtitle">استلام البضاعة المعلقة وإدخالها للمستودعات.</div>
                </div>
            </button>

            <!-- Purchase Return -->
            <button onclick="window.location.href='purchase-return.php'" class="hub-action-card" style="background: linear-gradient(135deg, rgba(244,63,94,0.1), rgba(244,63,94,0.02)); border-color: rgba(244,63,94,0.2);">
                <div class="icon" style="color: #f43f5e; text-shadow: 0 0 10px rgba(244, 63, 94, 0.4); box-shadow: inset 0 0 10px rgba(244,63,94,0.1);">↩️</div>
                <div class="text-content">
                    <div class="title" style="color: #f43f5e;">استرداد بضاعة</div>
                    <div class="subtitle">مردودات مشتريات مع تأثير عكسي على الحسابات.</div>
                </div>
            </button>

            <!-- Proforma Invoice -->
            <button onclick="openPurchaseEditor('proforma')" class="hub-action-card" style="background: linear-gradient(135deg, rgba(168,85,247,0.1), rgba(168,85,247,0.02)); border-color: rgba(168,85,247,0.2);">
                <div class="icon" style="color: #a855f7; text-shadow: 0 0 10px rgba(168, 85, 247, 0.4); box-shadow: inset 0 0 10px rgba(168,85,247,0.1);">📋</div>
                <div class="text-content">
                    <div class="title" style="color: #a855f7;">فاتورة مبدئية</div>
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
    <div id="purchase-editor-view" style="display: none; flex-direction: column; height: 100%; flex: 1; overflow-y: auto; padding-bottom: 30px;">
    

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
                <!-- Row 1: Supplier, Currency, Payment -->
                <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 5px;">
                    <div class="h-form-group" style="flex: 1.5; min-width: 200px; display: flex; align-items: center; gap: 10px;; flex-direction: row !important;">
                          <label class="h-label" style="margin: 0; white-space: nowrap; min-width: 60px;">المورد</label>
                          <div class="h-input-wrapper" style="display: flex; flex-direction: row; align-items: center; gap: 8px; flex: 1;">
                            
                                    <div class="custom-dropdown" style="flex:1;">
                                        <div class="custom-dropdown-input-wrap" style="position: relative;">
                                            <input type="text" class="ctrl-input v-input" id="hdr-customer-search" placeholder="ابحث عن اسم المورد..." onfocus="showCustomerDropdown()" onblur="hideCustomerDropdownDelayed()" oninput="filterCustomerDropdown()" style="width:100%; background: rgba(30,41,59,0.8); border: 1px solid rgba(255,255,255,0.15); padding-left: 95px !important;" autocomplete="one-time-code">
                                            <button type="button" id="hdr-customer-ledger-btn" onclick="openGeneralLedgerForAccount(document.getElementById('hdr-customer').value)" class="crystal-btn-sm btn-blue-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); z-index: 5; font-size: 0.8rem; padding: 4px 8px; height: 32px; display: none;" title="دفتر الأستاذ العام">📖 دفتر الأستاذ</button>
                                            <input type="hidden" id="hdr-customer" onchange="if(typeof onCustomerChange === 'function') onCustomerChange()">
                                            <div class="custom-dropdown-list" id="customer-dropdown-list">
                                                <!-- Populated via JS -->
                                            </div>
                                        </div>
                                    </div>

                            <button onclick="window.location.href='master-data.html?tab=suppliers'" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: 1px solid rgba(16,212,143,0.3); width: 32px; height: 32px; border-radius: 6px; font-size: 1.1rem; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 10px rgba(16, 185, 129, 0.2); transition: 0.2s;" title="البيانات الأساسية للموردين">
                                ➕
                            </button>
                        </div>
                    </div>

                    <div class="h-form-group" style="flex: 1.6; min-width: 220px; display: flex; align-items: center; gap: 10px;; flex-direction: row !important;">
                          <label class="h-label" style="margin: 0; white-space: nowrap; min-width: 60px;">سعر الصرف</label>
                          <div class="h-input-wrapper" style="display: flex; flex-direction: row; align-items: center; gap: 5px; flex: 1;">
                            <select class="v-input ctrl-input system-currency-select" id="hdr-currency" onchange="document.getElementById('hdr-exchange-rate').disabled = (this.value === 'SDG'); document.getElementById('hdr-exchange-rate').value = (this.value === 'SDG') ? '1' : ''; recalcAll();" style="flex: 1;">
                                <option value="SDG">🇸🇩 جنيه</option>
                                <option value="USD">🇺🇸 دولار</option>
                                <option value="SAR">🇸🇦 ريال</option>
                                <option value="AED">🇦🇪 درهم</option>
                                <option value="EGP">🇪🇬 مصري</option>
                            </select>
                            <input type="number" class="v-input ctrl-input" id="hdr-exchange-rate" value="1" disabled oninput="recalcAll()" style="flex: 1.5; text-align: center;" placeholder="الصرف">
                        </div>
                    </div>

                    <div class="h-form-group" style="flex: 1; min-width: 120px; display: flex; align-items: center; gap: 10px;; flex-direction: row !important;">
                          <label class="h-label" style="margin: 0; white-space: nowrap; min-width: 60px;">الدفع</label>
                          <div class="h-input-wrapper" style="flex: 1;; flex-direction: row !important; display: flex !important; align-items: center !important;">
                            <select class="v-input ctrl-input" id="hdr-payment-method" onchange="togglePaymentAccount()">
                                <option value="cash">نقدي فوري</option>
                                <option value="credit">آجل (على الحساب)</option>
                                <option value="bank">تحويل بنكي</option>
                                <option value="cheque">شيك</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Row 1.5: Payment Details (Hidden by default, shown for Bank/Cheque) -->
                <div id="payment-details-row" style="display: none; gap: 15px; flex-wrap: wrap; margin-top: -5px; padding: 10px; background: rgba(56, 189, 248, 0.05); border: 1px dashed rgba(56, 189, 248, 0.3); border-radius: 8px;">
                    <div class="h-form-group" style="flex: 1; min-width: 120px; display: flex; align-items: center; gap: 10px;; flex-direction: row !important;">
                          <label class="h-label" id="pay-detail-1-label" style="margin: 0; white-space: nowrap; font-size:0.9rem; color:#38bdf8;">رقم المرجع</label>
                          <div class="h-input-wrapper" style="flex: 1;; flex-direction: row !important; display: flex !important; align-items: center !important;">
                            <input type="text" class="v-input ctrl-input" id="pay-detail-1" placeholder="..." style="height: 32px !important; min-height: 32px !important; font-size: 0.95rem !important;">
                        </div>
                    </div>
                    <div class="h-form-group" style="flex: 1; min-width: 120px; display: flex; align-items: center; gap: 10px;; flex-direction: row !important;">
                          <label class="h-label" id="pay-detail-2-label" style="margin: 0; white-space: nowrap; font-size:0.9rem; color:#38bdf8;">اسم البنك</label>
                          <div class="h-input-wrapper" style="flex: 1;; flex-direction: row !important; display: flex !important; align-items: center !important;">
                            <input type="text" class="v-input ctrl-input" id="pay-detail-2" placeholder="..." style="height: 32px !important; min-height: 32px !important; font-size: 0.95rem !important;">
                        </div>
                    </div>
                    <div class="h-form-group" style="flex: 1; min-width: 120px; display: flex; align-items: center; gap: 10px;; flex-direction: row !important;">
                          <label class="h-label" id="pay-detail-3-label" style="margin: 0; white-space: nowrap; font-size:0.9rem; color:#38bdf8;">الفرع</label>
                          <div class="h-input-wrapper" style="flex: 1;; flex-direction: row !important; display: flex !important; align-items: center !important;">
                            <input type="text" class="v-input ctrl-input" id="pay-detail-3" placeholder="..." style="height: 32px !important; min-height: 32px !important; font-size: 0.95rem !important;">
                        </div>
                    </div>
                </div>

                <!-- Row 2: Treasury, Warehouse, Cost Center -->
                <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 5px;">
                    <div class="h-form-group" id="payment-account-group" style="flex: 1; min-width: 150px; display: flex; align-items: center; gap: 10px;; flex-direction: row !important;">
                          <label class="h-label" style="margin: 0; white-space: nowrap; min-width: 80px;">الخزينة / البنك</label>
                          <div class="h-input-wrapper" style="flex: 1;; flex-direction: row !important; display: flex !important; align-items: center !important;">
                            <select class="v-input ctrl-input" id="hdr-payment-account"></select>
                        </div>
                    </div>

                    <div class="h-form-group" style="flex: 1; min-width: 150px; display: flex; align-items: center; gap: 10px;; flex-direction: row !important;">
                          <label class="h-label" style="margin: 0; white-space: nowrap; min-width: 60px;">المخزن</label>
                          <div class="h-input-wrapper" style="flex: 1;; flex-direction: row !important; display: flex !important; align-items: center !important;">
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
        <div class="neo-card totals-card hidden-totals" id="totals-calc-card" style="margin-bottom: 0; flex-direction: column; justify-content: space-between; border-top: 2px solid var(--gold-primary);">
            <div class="neo-card-title" style="margin-bottom: 10px; text-align: center; color: var(--gold-primary); font-size: 0.95rem;">🧮 الإجماليات</div>
            
            <!-- Hidden: keeps JS id reference working -->
            <span id="t-invoice-total" style="display:none;">0</span>
            
            <div class="tot-row" style="padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span class="tot-lbl">الترحيل</span>
                <input type="text" inputmode="decimal" class="v-input tot-input tl-input" id="t-transport" value="0" onfocus="this.select()" oninput="recalcAll()" onblur="this.value=fmtNum(parseNum(this.value))" style="width:120px; text-align:left; background: rgba(249, 115, 22, 0.15); border: 1px solid rgba(249, 115, 22, 0.4); border-radius: 6px; padding: 2px 8px; height: 26px; color: #fff; font-size: 0.85rem; line-height: 1;">
            </div>
            <div class="tot-row" style="padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span class="tot-lbl">تكلفة العمالة</span>
                <input type="text" inputmode="decimal" class="v-input tot-input tl-input" id="t-labor" value="0" onfocus="this.select()" oninput="recalcAll()" onblur="this.value=fmtNum(parseNum(this.value))" style="width:120px; text-align:left; background: rgba(249, 115, 22, 0.15); border: 1px solid rgba(249, 115, 22, 0.4); border-radius: 6px; padding: 2px 8px; height: 26px; color: #fff; font-size: 0.85rem; line-height: 1;">
            </div>
            <div class="tot-row" style="padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span class="tot-lbl">إضافات أخرى</span>
                <span class="tot-val" id="t-additions" style="color:var(--gold-primary);">+ 0</span>
            </div>
            <div class="tot-row" style="padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span class="tot-lbl">الضريبة</span>
                <span class="tot-val" id="t-tax" style="color:var(--danger-color);">+ 0</span>
            </div>
            <div class="tot-row" style="padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span class="tot-lbl">خصم مكتسب</span>
                <span class="tot-val" id="t-discount" style="color:var(--success-color);">- 0</span>
            </div>

            <div class="tot-grand" style="margin-top: auto; background: rgba(56, 189, 248, 0.1); padding: 10px 15px; border-radius: 8px; border: 1px solid rgba(56, 189, 248, 0.3);">
                <span class="lbl" style="font-weight: 800; color: #38bdf8;">الصافي النهائي</span>
                <span class="val" id="t-grand" style="font-size: 1.4rem; color: #fff;">0</span>
            </div>
        </div>

    </div> <!-- End purchase-top-row -->

    <!-- BOTTOM PANEL (Items Table) -->
    <div class="purchase-bottom" style="flex: none; display: flex; flex-direction: column; padding: 0 15px 15px 15px; overflow: visible; margin-top: 15px;">
        
        <!-- items-toolbar was moved below the table-container -->

        <div class="table-container">
            <div class="grid-scroll-wrap">
                <table class="pos-grid" id="pos-grid">
                    <thead>
                        <tr>
                            <th class="th-num" style="width:40px; text-align:center;">#</th>
                            <th class="th-item" style="width:25%; min-width:180px; text-align:right;">الصنف المورد</th>
                            <th class="th-unit" style="width:8%; min-width:80px; text-align:center;">الوحدة</th>
                            <th class="th-factor" style="width:7%; min-width:70px; text-align:center;">معادل</th>
                            <th class="th-qty" style="width:8%; min-width:80px; text-align:center;">الكمية الوحدة</th>
                            <th class="th-price" style="width:10%; min-width:100px; text-align:center;">سعر الوحدة</th>
                            <th class="th-total" style="width:9%; min-width:100px; text-align:center;">الجملة</th>
                            <th class="th-baseqty" style="width:7%; min-width:70px; text-align:center;">العدد</th>
                            <th class="th-available" style="width:7%; min-width:70px; text-align:center;">الكمية المتاحة</th>
                            <th class="th-remaining" style="width:7%; min-width:70px; text-align:center;">الكمية المتبقية</th>
                            <th class="th-fcprice" style="width:10%; min-width:100px; text-align:center;">سعر الوحدة أجنبي</th>
                            <th class="th-fctotal" style="width:10%; min-width:100px; text-align:center;">جملة الوحدة أجنبي</th>
                            <th class="th-wh" style="width:9%; min-width:100px; text-align:center;">المخزن</th>
                            <th class="th-shila" style="width:7%; min-width:70px; text-align:center;">الشيلة</th>
                            <th class="th-shila-rem" style="width:7%; min-width:70px; text-align:center;">باقي سيخ</th>
                            <th class="th-note" style="width:10%; min-width:100px; text-align:center;">ملحوظة</th>
                            <th class="th-buy-price" style="width:8%; min-width:80px; text-align:center;">سعر الشراء</th>
                            <th class="th-sell-price" style="width:8%; min-width:80px; text-align:center;">سعر البيع</th>
                            <th class="th-del" style="width:80px; text-align:center;">إجراء</th>
                        </tr>
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

        <div class="items-toolbar" style="display: flex; align-items: center; justify-content: center; padding: 15px; border-radius: 12px; background: rgba(18, 15, 25, 0.8); border: 1px solid rgba(255,255,255,0.05); margin-top: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
            
            <div class="toolbar-actions" style="display: flex; gap: 12px; width: 100%; align-items: center;">
                
                <!-- AI OCR Button: Teal -->
                <button type="button" class="btn-action" style="flex: 1; height: 45px; background: #0d9488; border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(13, 148, 136, 0.3);" onmouseover="this.style.background='#0f766e'; this.style.boxShadow='0 4px 15px rgba(13, 148, 136, 0.5)';" onmouseout="this.style.background='#0d9488'; this.style.boxShadow='0 2px 10px rgba(13, 148, 136, 0.3)';" onclick="document.getElementById('ocr-upload').click()" id="btn-ocr" title="قراءة الفاتورة بالذكاء الاصطناعي">📸 مسح ضوئي</button>
                <input type="file" id="ocr-upload" accept="image/*,application/pdf" style="display: none;" onchange="processInvoiceImage(event)">
                
                <!-- Attachment Button: Solid Purple -->
                <button class="btn-action" id="attach-btn" style="flex: 1; height: 45px; background: #8b5cf6; border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(139, 92, 246, 0.3);" onmouseover="this.style.background='#7c3aed'; this.style.boxShadow='0 4px 15px rgba(139, 92, 246, 0.5)';" onmouseout="this.style.background='#8b5cf6'; this.style.boxShadow='0 2px 10px rgba(139, 92, 246, 0.3)';" onclick="document.getElementById('hdr-attachment').click()">📎 إرفاق مستند</button>
                <input type="file" id="hdr-attachment" accept="image/*,application/pdf" style="display:none;" onchange="document.getElementById('attach-btn').innerText = this.files.length > 0 ? '📎 تم الإرفاق' : '📎 إرفاق مستند'; document.getElementById('attach-btn').style.background = this.files.length > 0 ? '#10b981' : '#8b5cf6';">
                
                <!-- Cancel Button: Solid Red -->
                <button class="btn-action btn-delete" style="flex: 1; height: 45px; background: #ef4444; border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(239, 68, 68, 0.3);" onmouseover="this.style.background='#dc2626'; this.style.boxShadow='0 4px 15px rgba(239, 68, 68, 0.5)';" onmouseout="this.style.background='#ef4444'; this.style.boxShadow='0 2px 10px rgba(239, 68, 68, 0.3)';" onclick="confirmDeleteTransaction()">❌ إلغاء</button>
                
                <!-- Preview Button: Solid Blue -->
                <button type="button" class="btn-action btn-preview" id="btn-preview-invoice" style="flex: 1; height: 45px; background: #3b82f6; border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(59, 130, 246, 0.3); display: none; align-items: center; justify-content: center;" onmouseover="this.style.background='#2563eb'; this.style.boxShadow='0 4px 15px rgba(59, 130, 246, 0.5)';" onmouseout="this.style.background='#3b82f6'; this.style.boxShadow='0 2px 10px rgba(59, 130, 246, 0.3)';" onclick="printCurrentInvoice()">👁️ معاينة</button>
                
                <!-- WhatsApp Button: Solid Green -->
                <button type="button" class="btn-action btn-whatsapp" id="btn-whatsapp-invoice" style="flex: 1.2; height: 45px; background: #25d366; border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(37, 211, 102, 0.3); display: none; align-items: center; justify-content: center; gap: 8px;" onmouseover="this.style.background='#1da851'; this.style.boxShadow='0 4px 15px rgba(37, 211, 102, 0.5)';" onmouseout="this.style.background='#25d366'; this.style.boxShadow='0 2px 10px rgba(37, 211, 102, 0.3)';" onclick="sendCurrentInvoiceWA()">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    <span>إرسال للواتساب</span>
                </button>
                
                <!-- Submit Button: Solid Blue Gradient -->
                <span style="display:none;" id="footer-grand"></span>
                <button class="btn-action btn-submit" style="flex: 1.5; height: 45px; box-shadow: 0 2px 10px rgba(56, 189, 248, 0.4); background: linear-gradient(135deg, #0ea5e9, #2563eb); color: #fff; border: none; font-size: 1.1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s;" onmouseover="this.style.boxShadow='0 6px 20px rgba(56, 189, 248, 0.6)';" onmouseout="this.style.boxShadow='0 2px 10px rgba(56, 189, 248, 0.4)';" onclick="submitInvoice()">✅ تأكيد وحفظ</button>
            </div>
        </div>

        <!-- Hidden Adjustments & Notes -->
        <div id="adjustments-panel" style="display: none;">
            <input type="hidden" id="inv-notes" value="">
            <input type="text" id="discount-amt" value="0">
            <input type="number" id="discount-pct" value="0">
            <input type="text" id="tax-amt" value="0">
            <input type="number" id="tax-pct" value="0">
            <input type="text" id="addition-amt" value="0">
            <input type="number" id="addition-pct" value="0">
        </div>

    </div> <!-- End purchase-bottom -->

    </div>
</div>
    <script src="js/localforage.min.js"></script>
    <script src="js/html2pdf.bundle.min.js"></script>
    <script src="js/whatsapp-integration.js"></script>
    <script src="js/app.js?v=1783054969.28779"></script>
    <script src="js/print-engine.js"></script>
    
    <script>
      document.addEventListener('DOMContentLoaded', () => {
          
              }
          });
      });
    </script>
    <script src="js/purchase-pro-v4.js?v=24"></script>
</div>

    <script>
        // Purchase Hub Logic
        let currentPurchaseView = 'hub';

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
            
            invoices.sort((a,b) => new Date(b.timestamp || b.date) - new Date(a.timestamp || a.date));
            
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
                    <td style="text-align:right; color:var(--text-dim);">${new Date(inv.timestamp || inv.date).toLocaleString('ar-EG')}</td>
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
                tbody.appendChild(tr);
            });
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
    if (currentViewedInvoiceNo) {
        if (previewBtn) previewBtn.style.display = 'flex';
        if (waBtn) waBtn.style.display = 'flex';
    } else {
        if (previewBtn) previewBtn.style.display = 'none';
        if (waBtn) waBtn.style.display = 'none';
    }
}

async function sendCurrentInvoiceWA() {
    if (currentViewedInvoiceNo) {
        await sendInvoiceWA(currentViewedInvoiceNo);
    } else {
        showToast('⚠️ يرجى حفظ وتأكيد الفاتورة أولاً', 'warning');
    }
}
</script>
        <!-- Context Menu for Columns -->
    <div id="col-context-menu">
        <div style="font-size: 0.75rem; color: var(--gold-primary); margin-bottom: 8px; font-weight: bold; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 4px;">إظهار/إخفاء الأعمدة</div>
        <div id="col-toggle-list" style="max-height: 300px; overflow-y: auto;"></div>
    </div>

    <script>
    function initTableEnhancements() {
        const table = document.getElementById('pos-grid');
        if(!table) return;

        // --- Column Resizing ---
        const ths = table.querySelectorAll('thead th');
        ths.forEach((th, index) => {
            if(th.querySelector('.resizer')) return; // already added

            const resizer = document.createElement('div');
            resizer.classList.add('resizer');
            th.appendChild(resizer);
            
            let x = 0;
            let w = 0;
            
            const mouseDownHandler = function(e) {
                x = e.clientX;
                const styles = window.getComputedStyle(th);
                w = parseInt(styles.width, 10);
                
                document.addEventListener('mousemove', mouseMoveHandler);
                document.addEventListener('mouseup', mouseUpHandler);
                resizer.classList.add('resizing');
            };
            
            const mouseMoveHandler = function(e) {
                const dx = e.clientX - x;
                // RTL layout: moving left decreases X, increasing dx negatively.
                // Left resizer drag to left (dx < 0) means width increases.
                const isRTL = document.dir === 'rtl' || document.documentElement.dir === 'rtl' || getComputedStyle(document.body).direction === 'rtl';
                th.style.width = isRTL ? `${w - dx}px` : `${w + dx}px`;
                th.style.minWidth = th.style.width;
            };
            
            const mouseUpHandler = function() {
                document.removeEventListener('mousemove', mouseMoveHandler);
                document.removeEventListener('mouseup', mouseUpHandler);
                resizer.classList.remove('resizing');
            };
            
            resizer.addEventListener('mousedown', mouseDownHandler);
        });

        // --- Context Menu ---
        const menu = document.getElementById('col-context-menu');
        const toggleList = document.getElementById('col-toggle-list');
        
        const updateMenu = () => {
            toggleList.innerHTML = '';
            const currentThs = table.querySelectorAll('thead th');
            currentThs.forEach((th, index) => {
                let title = th.textContent.trim() || 'إجراء';
                
                const item = document.createElement('label');
                item.className = 'col-toggle-item';
                
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.checked = th.style.display !== 'none';
                checkbox.dataset.colIndex = index + 1;
                
                checkbox.addEventListener('change', (e) => {
                    const isChecked = e.target.checked;
                    const colIndex = e.target.dataset.colIndex;
                    
                    table.querySelector(`thead th:nth-child(${colIndex})`).style.display = isChecked ? '' : 'none';
                    
                    const rows = table.querySelectorAll('tbody tr');
                    rows.forEach(row => {
                        const td = row.querySelector(`td:nth-child(${colIndex})`);
                        if(td) td.style.display = isChecked ? '' : 'none';
                    });
                    
                    if (window.saveColumnVisibility) window.saveColumnVisibility();
                });
                
                item.appendChild(checkbox);
                item.appendChild(document.createTextNode(title));
                toggleList.appendChild(item);
            });
        };

        table.addEventListener('contextmenu', (e) => {
            // Check if context menu is within table body or header
            if(e.target.closest('table') === table) {
                e.preventDefault();
                updateMenu();
                menu.style.display = 'block';
                menu.style.left = `${e.pageX}px`;
                menu.style.top = `${e.pageY}px`;
            }
        });

        document.addEventListener('click', (e) => {
            if (!menu.contains(e.target) && e.button !== 2) {
                menu.style.display = 'none';
            }
        });

        // Observer for dynamically added rows
        if(!window.tableObserver) {
            const tbody = document.getElementById('grid-body');
            if(tbody) {
                window.tableObserver = new MutationObserver((mutations) => {
                    mutations.forEach(mutation => {
                        if (mutation.addedNodes.length > 0) {
                            const currentThs = table.querySelectorAll('thead th');
                            currentThs.forEach((th, index) => {
                                if(th.style.display === 'none') {
                                    const colIndex = index + 1;
                                    mutation.addedNodes.forEach(row => {
                                        if(row.nodeType === 1 && row.tagName === 'TR') {
                                            const td = row.querySelector(`td:nth-child(${colIndex})`);
                                            if(td) td.style.display = 'none';
                                        }
                                    });
                                }
                            });
                        }
                    });
                });
                window.tableObserver.observe(tbody, { childList: true });
            }
        }
    }

    document.addEventListener('DOMContentLoaded', initTableEnhancements);
    // --- Column Reordering Logic ---
    let dragSrcCol = null;

    function initDraggableColumns() {
        const theadTr = document.querySelector('#pos-grid thead tr');
        if(!theadTr) return;
        const ths = theadTr.querySelectorAll('th');
        ths.forEach((th, index) => {
            if(!th.hasAttribute('data-col-id')) {
                th.setAttribute('data-col-id', index);
            }
            th.setAttribute('draggable', 'true');
            th.style.cursor = 'grab';
            
            th.addEventListener('dragstart', handleColDragStart);
            th.addEventListener('dragenter', handleColDragEnter);
            th.addEventListener('dragover', handleColDragOver);
            th.addEventListener('dragleave', handleColDragLeave);
            th.addEventListener('drop', handleColDrop);
            th.addEventListener('dragend', handleColDragEnd);
        });
        
        applySavedColumnOrder();
    }

    function handleColDragStart(e) {
        // Prevent drag if clicking on resizer
        if(e.target.classList.contains('resizer')) {
            e.preventDefault();
            return;
        }
        dragSrcCol = this;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
        this.style.opacity = '0.4';
    }

    function handleColDragOver(e) {
        if (e.preventDefault) e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        return false;
    }

    function handleColDragEnter(e) {
        let th = e.target.closest('th');
        if (th && th !== dragSrcCol) {
            th.style.border = '2px dashed #38bdf8';
        }
    }

    function handleColDragLeave(e) {
        let th = e.target.closest('th');
        if (th && th !== dragSrcCol) {
            th.style.border = '';
            th.style.borderBottom = '2px solid rgba(255,255,255,0.05)';
        }
    }

    function handleColDrop(e) {
        if (e.stopPropagation) e.stopPropagation();
        let targetTh = e.target.closest('th');
        if (dragSrcCol && targetTh && dragSrcCol !== targetTh) {
            const tr = targetTh.parentElement;
            const children = Array.from(tr.children);
            const srcIdx = children.indexOf(dragSrcCol);
            const targetIdx = children.indexOf(targetTh);

            if (srcIdx < targetIdx) {
                targetTh.after(dragSrcCol);
            } else {
                targetTh.before(dragSrcCol);
            }
            
            saveColumnOrder();
            applySavedColumnOrder(true);
        }
        return false;
    }

    function handleColDragEnd(e) {
        this.style.opacity = '1';
        let ths = document.querySelectorAll('#pos-grid thead th');
        ths.forEach(th => {
            th.style.border = '';
            th.style.borderBottom = '2px solid rgba(255,255,255,0.05)';
        });
    }

    function saveColumnOrder() {
        let ths = document.querySelectorAll('#pos-grid thead tr th');
        let order = Array.from(ths).map(th => parseInt(th.getAttribute('data-col-id')));
        localStorage.setItem('erp_purchase_col_order', JSON.stringify(order));
    }

    window.reorderTds = function(tr) {
        let orderStr = localStorage.getItem('erp_purchase_col_order');
        let hiddenStr = localStorage.getItem('erp_purchase_col_vis');
        let hidden = hiddenStr ? JSON.parse(hiddenStr) : [];
        
        if (!orderStr && hidden.length === 0) return;
        
        let order = orderStr ? JSON.parse(orderStr) : null;
        let tds = Array.from(tr.children);
        
        if (tds.length > 0 && !tds[0].hasAttribute('data-col-id')) {
            tds.forEach((td, idx) => td.setAttribute('data-col-id', idx));
        }
        
        if (order) {
            order.forEach(id => {
                let td = tds.find(t => parseInt(t.getAttribute('data-col-id')) === id);
                if (td) tr.appendChild(td);
            });
        }
        
        if (hidden.length > 0) {
            Array.from(tr.children).forEach(td => {
                let id = parseInt(td.getAttribute('data-col-id'));
                if (hidden.includes(id)) {
                    td.style.display = 'none';
                } else {
                    td.style.display = '';
                }
            });
        }
    };

    function applySavedColumnOrder(forceTbody = false) {
        let orderStr = localStorage.getItem('erp_purchase_col_order');
        if (!orderStr) return;
        let order = JSON.parse(orderStr);
        
        let theadTr = document.querySelector('#pos-grid thead tr');
        if (theadTr) {
            let ths = Array.from(theadTr.children);
            order.forEach(id => {
                let th = ths.find(t => parseInt(t.getAttribute('data-col-id')) === id);
                if (th) theadTr.appendChild(th);
            });
        }
        
        if (forceTbody) {
            let trs = document.querySelectorAll('#pos-grid tbody tr');
            trs.forEach(tr => window.reorderTds(tr));
        }
    }

    window.saveColumnVisibility = function() {
        let ths = document.querySelectorAll('#pos-grid thead th');
        let hidden = [];
        ths.forEach(th => {
            if(th.style.display === 'none') hidden.push(parseInt(th.getAttribute('data-col-id')));
        });
        localStorage.setItem('erp_purchase_col_vis', JSON.stringify(hidden));
    };

    window.applySavedColumnVisibility = function() {
        let hiddenStr = localStorage.getItem('erp_purchase_col_vis');
        if(!hiddenStr) return;
        let hidden = JSON.parse(hiddenStr);
        
        let ths = document.querySelectorAll('#pos-grid thead th');
        ths.forEach(th => {
            let id = parseInt(th.getAttribute('data-col-id'));
            if(hidden.includes(id)) {
                th.style.display = 'none';
            }
        });
        
        let trs = document.querySelectorAll('#pos-grid tbody tr');
        trs.forEach(tr => {
            let tds = Array.from(tr.children);
            tds.forEach(td => {
                let id = parseInt(td.getAttribute('data-col-id'));
                if(id !== null && !isNaN(id) && hidden.includes(id)) {
                    td.style.display = 'none';
                }
            });
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            initDraggableColumns();
            window.applySavedColumnVisibility();
        }, 600);
    });
    </script>

</body>
</html>

