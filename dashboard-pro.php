<?php $page_title = 'لوحة التحكم'; ?>
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
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>لوحة التحكم</title>
    <link rel="stylesheet" href="css/design-system.css?v=20260709_5">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        html {
            font-size: 20px !important;
        }

        :root {
            --bg-color: #050308;
            --surface: rgba(18, 15, 25, 0.65);
            --surface-hover: rgba(28, 24, 38, 0.85);
            --border: rgba(255, 255, 255, 0.08);
            --border-highlight: rgba(255, 255, 255, 0.2);
            
            --primary-accent: #6366f1;
            --theme-pink: #ec4899;
            --theme-blue: #3b82f6;
            --theme-green: #10b981;
            --theme-orange: #f59e0b;
            --theme-red: #f87171;
            --theme-purple: #c084fc;
        }

        body {
            background-color: var(--bg-color);
            margin: 0; padding: 0;
            min-height: 100vh;
            display: flex; flex-direction: column;
            overflow-x: hidden;
            font-family: 'Tajawal', sans-serif;
            color: #fff;
            font-weight: 500;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.2); }
        ::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.4); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(99, 102, 241, 0.8); }

        /* Ambient Animated Background Orbs */
        .ambient-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            overflow: hidden; z-index: -1; pointer-events: none;
            background: radial-gradient(circle at 50% 50%, #0c0812 0%, #000000 100%);
        }

        .orb {
            position: absolute; border-radius: 50%; filter: blur(120px); opacity: 0.15;
            animation: floatOrb 20s infinite alternate ease-in-out;
        }

        .orb-1 { width: 500px; height: 500px; background: var(--primary-accent); top: -5%; right: -5%; }
        .orb-2 { width: 450px; height: 450px; background: var(--theme-pink); bottom: -10%; left: -5%; animation-delay: -7s; }
        .orb-3 { width: 600px; height: 600px; background: var(--theme-blue); top: 30%; left: 30%; animation-duration: 25s; opacity: 0.1; }

        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-30px, 30px) scale(0.9); }
            100% { transform: translate(0, 0) scale(1); }
        }

        /* Compact Layout Container */
        .dash-main {
            width: 100%;
            max-width: 1600px; margin: 0 auto;
            margin: 0 auto;
            padding: 0px 30px 20px 30px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 15px;
            flex: 1;
            animation: fadeIn 0.8s ease-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Athkar Ticker */
        .islamic-ticker-container { overflow: hidden; white-space: nowrap; max-width: 400px; margin: 10px auto 0; position: relative; display: flex; align-items: center; border-radius: 8px; background: rgba(0,0,0,0.2); padding: 5px 10px; border: 1px solid rgba(255,255,255,0.05); }
        .islamic-ticker {
            display: inline-block; font-weight: 700; font-size: 0.95rem; color: transparent;
            background: linear-gradient(90deg, #f59e0b, #10b981, #3b82f6, #a855f7, #f59e0b);
            background-size: 200% auto; -webkit-background-clip: text; background-clip: text;
            animation: gradientMove 3s linear infinite, scrollTicker 20s linear infinite;
            text-shadow: 0 0 10px rgba(245, 158, 11, 0.2); white-space: nowrap; padding-left: 100%;
        }
        @keyframes scrollTicker { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }

        .header-brand { display: flex; align-items: center; gap: 12px; }
        .brand-icon {
            width: 40px; height: 40px; border-radius: 12px;
            background: linear-gradient(135deg, #38bdf8, #818cf8);
            display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
            box-shadow: 0 6px 15px rgba(56, 189, 248, 0.4), inset 0 2px 5px rgba(255,255,255,0.3); border: 1px solid rgba(255,255,255,0.2);
        }
        .brand-text h1 { margin: 0; font-size: 1.1rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; text-shadow: 0 2px 5px rgba(255,255,255,0.1); }
        .brand-text p { margin: 0; font-size: 0.75rem; color: #94a3b8; font-weight: 500; }

        .header-center { text-align: center; }
        .header-center h2 { margin: 0; font-size: 1.2rem; font-weight: 900; background: linear-gradient(to right, #fff, #cbd5e1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 1px; }

        .header-controls { display: flex; align-items: center; gap: 12px; }
        
        .clock-container {
            display: flex; flex-direction: column; align-items: center; 
            background: rgba(15,23,42,0.6); padding: 6px 12px; border-radius: 12px; 
            border: 1px solid rgba(255,255,255,0.08); min-width: 100px;
        }
        .clock-time { color: #38bdf8; font-size: 1rem; font-weight: 900; font-variant-numeric: tabular-nums; letter-spacing: 1px; text-shadow: 0 0 8px rgba(56,189,248,0.5); font-family: 'Outfit', sans-serif;}
        .clock-date { color: var(--text-secondary); font-size: 0.65rem; margin-top: 1px; }

        @keyframes tactileBuzz {
            0% { transform: translateY(-3px) scale(1.02) rotate(0deg); }
            25% { transform: translateY(-3px) scale(1.02) rotate(-0.5deg); }
            50% { transform: translateY(-3px) scale(1.02) rotate(0.5deg); }
            75% { transform: translateY(-3px) scale(1.02) rotate(-0.5deg); }
            100% { transform: translateY(-3px) scale(1.02) rotate(0deg); }
        }

        .icon-btn {
            background: rgba(255,255,255,0.03); border: 1px solid var(--border); width: 36px; height: 36px;
            border-radius: 10px; color: #fff; font-size: 1rem; display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1); position: relative;
        }
        .icon-btn:hover { 
            animation: tactileBuzz 0.1s linear infinite;
            background: rgba(56, 189, 248, 0.1); 
            border-color: #38bdf8; 
            box-shadow: 0 0 15px rgba(56,189,248,0.5), 0 0 30px rgba(56,189,248,0.3), inset 0 0 10px rgba(56,189,248,0.2); 
            z-index: 10;
        }
        .icon-btn > * { transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: inline-block; }
        .icon-btn:hover > * { transform: scale(1.2); }

        .user-info {
            display: flex; align-items: center; gap: 8px; background: rgba(15,23,42,0.6); padding: 6px 12px; 
            border-radius: 12px; border: 1px solid rgba(255,255,255,0.08);
        }

        /* Compact Glass Panels */
        .glass-panel {
            background: var(--surface); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border); border-radius: 18px; padding: 18px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3), inset 0 1px 1px rgba(255,255,255,0.03);
            transition: all 0.4s ease;
        }
        .glass-panel:hover { border-color: var(--border-highlight); box-shadow: 0 15px 35px rgba(0,0,0,0.4); transform: translateY(-3px); }

        .glass-panel h3 { margin: 0 0 15px 0; font-size: 1.05rem; font-weight: 800; display:flex; align-items:center; gap:8px; }

        /* Neon Borders */
        .neon-border-primary { border-top: 3px solid var(--theme-blue); }
        .neon-border-success { border-top: 3px solid var(--theme-green); }
        .neon-border-warning { border-top: 3px solid var(--theme-orange); }
        .neon-border-danger { border-top: 3px solid var(--theme-red); }
        .neon-border-purple { border-top: 3px solid var(--theme-purple); }

        /* Compact KPI Cards */
        .kpi-cards {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px;
        }
        .kpi-cards .glass-panel {
            padding: 8px 12px;
            border-radius: 12px;
        }
        .kpi-title { color:var(--text-secondary); font-size:0.75rem; font-weight: 700; }
        .kpi-icon-bg { padding: 4px 6px; border-radius: 8px; }
        .kpi-icon-bg span { font-size: 1rem; }
        .kpi-val { margin:2px 0 0; font-size:1.15rem; font-weight:900; font-family: 'Outfit', sans-serif; line-height: 1.2; }
        .kpi-desc { font-size:0.65rem; margin-top:2px; font-weight: 600; }

        /* Dynamic Spotlight Glass Effect for KPI Cards */
        .spotlight-card {
            position: relative;
            overflow: hidden;
            transform: translateZ(0);
        }
        .spotlight-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(
                450px circle at var(--mouse-x, 0) var(--mouse-y, 0),
                rgba(255, 255, 255, 0.12),
                transparent 45%
            );
            z-index: 0;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .spotlight-card:hover::before {
            opacity: 1;
        }
        .spotlight-card > * {
            position: relative;
            z-index: 1;
        }

        /* Compact Charts Grid */
        .charts-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 15px;
        }
        .chart-container { position: relative; height: 240px; width: 100%; }
        
        .bottom-section {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 15px;
        }

        /* Compact Quick Action Buttons */
        .qa-card-wrapper {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.4) 0%, rgba(15, 23, 42, 0.6) 100%);
            padding: 15px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); 
            box-shadow: inset 0 0 15px rgba(255,255,255,0.02), 0 5px 15px rgba(0,0,0,0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease; height: 100%; box-sizing: border-box;
        }
        .qa-card-wrapper:hover {
            transform: translateY(-3px); box-shadow: inset 0 0 20px rgba(255,255,255,0.04), 0 8px 25px rgba(0,0,0,0.3);
        }
        .qa-card-wrapper h4 { margin: 0 0 12px 0; font-size: 0.95rem; font-weight: 800; display: flex; align-items: center; gap: 8px; }

        .quick-action-btn {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px; border-radius: 12px; text-decoration: none; font-weight: 800; font-size: 0.9rem;
            color: #fff; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 3px 10px rgba(0,0,0,0.2), inset 0 0 0 1px rgba(255,255,255,0.05);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1); position: relative; overflow: hidden;
            font-family: 'Tajawal', sans-serif;
        }
        .quick-action-btn:hover { 
            animation: tactileBuzz 0.1s linear infinite;
            border-color: rgba(255,255,255,0.3);
            z-index: 10;
        }

        .qa-primary { border-bottom: 2px solid rgba(56,189,248,0.5); }
        .qa-primary:hover { background: rgba(56,189,248,0.1); color: #38bdf8; border-color: #38bdf8; box-shadow: 0 0 15px rgba(56,189,248,0.6), 0 0 30px rgba(56,189,248,0.3), inset 0 0 15px rgba(56,189,248,0.2) !important; }
        
        .qa-warning { border-bottom: 2px solid rgba(251,191,36,0.5); }
        .qa-warning:hover { background: rgba(251,191,36,0.1); color: #fbbf24; border-color: #fbbf24; box-shadow: 0 0 15px rgba(251,191,36,0.6), 0 0 30px rgba(251,191,36,0.3), inset 0 0 15px rgba(251,191,36,0.2) !important; }
        
        .qa-success { border-bottom: 2px solid rgba(52,211,153,0.5); }
        .qa-success:hover { background: rgba(52,211,153,0.1); color: #34d399; border-color: #34d399; box-shadow: 0 0 15px rgba(52,211,153,0.6), 0 0 30px rgba(52,211,153,0.3), inset 0 0 15px rgba(52,211,153,0.2) !important; }
        
        .qa-danger { border-bottom: 2px solid rgba(248,113,113,0.5); }
        .qa-danger:hover { background: rgba(248,113,113,0.1); color: #f87171; border-color: #f87171; box-shadow: 0 0 15px rgba(248,113,113,0.6), 0 0 30px rgba(248,113,113,0.3), inset 0 0 15px rgba(248,113,113,0.2) !important; }
        
        .qa-purple { border-bottom: 2px solid rgba(192,132,252,0.5); }
        .qa-purple:hover { background: rgba(192,132,252,0.1); color: #c084fc; border-color: #c084fc; box-shadow: 0 0 15px rgba(192,132,252,0.6), 0 0 30px rgba(192,132,252,0.3), inset 0 0 15px rgba(192,132,252,0.2) !important; }

        .quick-action-btn:not([class*="qa-"]):hover { box-shadow: 0 0 15px rgba(255,255,255,0.4), 0 0 30px rgba(255,255,255,0.2), inset 0 0 15px rgba(255,255,255,0.1) !important; }

        .qa-icon {
            font-size: 1.3rem; /* Increased size by one step */
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 10px; /* Sharp rounded corners */
            border: 2px solid rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.04);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            margin-left: 8px; /* Spacing for RTL */
            flex-shrink: 0;
        }
        
        /* Specific accent backgrounds and borders for admin action buttons (Dark Mode default) */
        .admin-action-settings .qa-icon {
            background: rgba(100, 116, 139, 0.15);
            border-color: rgba(100, 116, 139, 0.3);
        }
        .admin-action-master .qa-icon {
            background: rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.3);
        }
        .admin-action-jobs .qa-icon {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.3);
        }
        .admin-action-notifications .qa-icon {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.3);
        }

        /* Hover states: border thickens (sumk yazid daraja), icon scales up, background darkens */
        .admin-action-btn:hover .qa-icon {
            transform: scale(1.25) rotate(5deg) translateY(-2px);
            border-width: 3.5px; /* Thicker border (حواف تزيد أكثر لتكون وضوح) */
        }
        .admin-action-settings:hover .qa-icon {
            background: rgba(100, 116, 139, 0.35) !important;
            border-color: #64748b !important;
        }
        .admin-action-master:hover .qa-icon {
            background: rgba(59, 130, 246, 0.35) !important;
            border-color: #3b82f6 !important;
        }
        .admin-action-jobs:hover .qa-icon {
            background: rgba(16, 185, 129, 0.35) !important;
            border-color: #10b981 !important;
        }
        .admin-action-notifications:hover .qa-icon {
            background: rgba(239, 68, 68, 0.35) !important;
            border-color: #ef4444 !important;
        }
        
        .quick-action-btn:hover .qa-icon {
            transform: scale(1.25) rotate(5deg) translateY(-2px);
            border-width: 3.5px;
        }

        /* Compact Tables */
        .premium-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.85rem; }
        .premium-table th { padding: 10px; text-align: right; color: #94a3b8; border-bottom: 1px solid rgba(255,255,255,0.05); font-weight: 700; }
        .premium-table td { padding: 10px; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.03); }
        .premium-table tbody tr:hover td { background: rgba(255,255,255,0.02); }

        /* List Items */
        .list-item {
            display:flex; align-items:center; gap:12px; padding:12px; border-radius:12px; 
        }
        .list-icon {
            width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; 
        }
        .list-title { margin:0; color:#fff; font-size:0.9rem; }
        .list-sub { color:var(--text-secondary); font-size:0.75rem; }
        .list-btn { padding:6px 12px; background:var(--primary-accent); color:#fff; border-radius:8px; text-decoration:none; font-size:0.8rem; font-weight:800; }

        /* Admin action buttons styling */
        .admin-action-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 16px; border-radius: 20px; text-decoration: none; font-weight: 800; font-size: 0.85rem;
            color: #fff; background: rgba(15, 23, 42, 0.4); border: 1.5px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1); cursor: pointer;
            font-family: 'Tajawal', sans-serif;
            box-shadow: inset 0 0 10px rgba(255,255,255,0.01), 0 4px 10px rgba(0,0,0,0.15);
        }
        .admin-action-btn:hover {
            transform: translateY(-2px);
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.25);
            box-shadow: 0 6px 15px rgba(0,0,0,0.3);
        }
        .admin-action-settings {
            border-bottom: 2.5px solid #64748b !important;
        }
        .admin-action-settings:hover {
            color: #fff;
            box-shadow: 0 0 15px rgba(100,116,139,0.5), inset 0 0 10px rgba(100,116,139,0.15) !important;
            border-color: #94a3b8 !important;
        }
        .admin-action-master {
            border-bottom: 2.5px solid var(--theme-blue) !important;
        }
        .admin-action-master:hover {
            color: #fff;
            box-shadow: 0 0 15px rgba(59,130,246,0.5), inset 0 0 10px rgba(59,130,246,0.15) !important;
            border-color: #60a5fa !important;
        }
        .admin-action-jobs {
            border-bottom: 2.5px solid #10b981 !important;
        }
        .admin-action-jobs:hover {
            color: #fff;
            box-shadow: 0 0 15px rgba(16,185,129,0.5), inset 0 0 10px rgba(16,185,129,0.15) !important;
            border-color: #34d399 !important;
        }
        .admin-action-notifications {
            border-bottom: 2.5px solid var(--theme-red) !important;
        }
        .admin-action-notifications:hover {
            color: #fff;
            box-shadow: 0 0 15px rgba(248,113,113,0.5), inset 0 0 10px rgba(248,113,113,0.15) !important;
            border-color: #f87171 !important;
        }
        .admin-action-btn:hover .qa-icon {
            transform: scale(1.4) rotate(5deg) translateY(-2px);
            filter: brightness(1.25) drop-shadow(0 2px 6px rgba(255,255,255,0.4));
        }

        /* Two levels rows styling */
        .admin-actions-row {
            display: flex; gap: 15px; margin-bottom: 8px; align-items: center; 
            border: 1px solid rgba(255,255,255,0.05); background: rgba(15, 23, 42, 0.25); 
            padding: 6px 14px; border-radius: 14px; flex-wrap: wrap; 
            box-shadow: inset 0 0 15px rgba(255,255,255,0.01);
        }
        .operations-actions-row {
            display: flex; gap: 15px; align-items: center; 
            border: 1px solid rgba(255,255,255,0.03); background: rgba(15, 23, 42, 0.15); 
            padding: 6px 14px; border-radius: 14px; flex-wrap: wrap; 
            box-shadow: inset 0 0 10px rgba(255,255,255,0.01);
        }
        .actions-row-title {
            font-size: 0.82rem; font-weight: 800; color: #94a3b8; 
            display: flex; align-items: center; gap: 8px; min-width: 140px; 
            border-left: 2px solid rgba(255,255,255,0.15); padding-left: 10px; 
            margin-left: 5px;
        }

        @media (max-width: 768px) {
            .header-controls { justify-content: center; width: 100%; flex-wrap: wrap; }
            .charts-grid, .bottom-section { grid-template-columns: 1fr; }
        }
        
        /* Fix double sidebar spacing issue */
        body.has-sidebar { padding-right: 0 !important; }

        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOut { from { opacity:1; transform:translateY(0) scale(1); } to { opacity:0; transform:translateY(-20px) scale(0.95); } }
    </style>
    <style>
        :root.light-theme {
            --bg-color: #f1f5f9;
            --surface: #ffffff;
            --surface-hover: rgba(241, 245, 249, 0.98);
            --border: rgba(0, 0, 0, 0.08);
            --border-highlight: rgba(0, 0, 0, 0.15);
            --primary-accent: #2563eb;
            --theme-pink: #db2777;
            --theme-blue: #1d4ed8;
            --theme-green: #059669;
            --theme-orange: #d97706;
            --theme-red: #dc2626;
            --theme-purple: #7c3aed;
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
        :root.light-theme .admin-action-btn {
            background: rgba(0,0,0,0.03);
            border-color: rgba(0,0,0,0.1);
            color: #000000;
        }
        :root.light-theme .admin-action-btn:hover {
            background: rgba(0,0,0,0.06);
            border-color: rgba(0,0,0,0.18);
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        :root.light-theme .qa-icon {
            border: 2px solid rgba(0, 0, 0, 0.15) !important;
            background: rgba(0, 0, 0, 0.03) !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05) !important;
        }
        
        /* Light Mode specific color coding (lighter backgrounds, clear borders) */
        :root.light-theme .admin-action-settings .qa-icon {
            background: rgba(100, 116, 139, 0.1) !important;
            border-color: rgba(100, 116, 139, 0.35) !important;
        }
        :root.light-theme .admin-action-master .qa-icon {
            background: rgba(59, 130, 246, 0.1) !important;
            border-color: rgba(59, 130, 246, 0.35) !important;
        }
        :root.light-theme .admin-action-jobs .qa-icon {
            background: rgba(16, 185, 129, 0.1) !important;
            border-color: rgba(16, 185, 129, 0.35) !important;
        }
        :root.light-theme .admin-action-notifications .qa-icon {
            background: rgba(239, 68, 68, 0.1) !important;
            border-color: rgba(239, 68, 68, 0.35) !important;
        }

        /* Hover states in Light Mode: backgrounds darken, borders thicken */
        :root.light-theme .admin-action-btn:hover .qa-icon {
            border-width: 3.5px !important;
            transform: scale(1.25) rotate(5deg) translateY(-2px) !important;
        }
        :root.light-theme .admin-action-settings:hover .qa-icon {
            background: rgba(100, 116, 139, 0.3) !important;
            border-color: #64748b !important;
        }
        :root.light-theme .admin-action-master:hover .qa-icon {
            background: rgba(59, 130, 246, 0.3) !important;
            border-color: #3b82f6 !important;
        }
        :root.light-theme .admin-action-jobs:hover .qa-icon {
            background: rgba(16, 185, 129, 0.3) !important;
            border-color: #10b981 !important;
        }
        :root.light-theme .admin-action-notifications:hover .qa-icon {
            background: rgba(239, 68, 68, 0.3) !important;
            border-color: #ef4444 !important;
        }
        :root.light-theme .admin-actions-row {
            background: rgba(0, 0, 0, 0.03);
            border-color: rgba(0, 0, 0, 0.06);
            box-shadow: none;
        }
        :root.light-theme .operations-actions-row {
            background: rgba(0, 0, 0, 0.015);
            border-color: rgba(0, 0, 0, 0.04);
            box-shadow: none;
        }
        :root.light-theme .actions-row-title {
            color: #1e293b;
            border-left-color: rgba(0,0,0,0.12);
        }
        :root.light-theme .islamic-ticker-container {
            background: rgba(0, 0, 0, 0.04) !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
        }
        :root.light-theme #ticker-profit,
        :root.light-theme #ticker-monthly-profit {
            color: #059669 !important; /* High contrast green */
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

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="dash-main">

        <!-- Unified Premium Header -->
        <?php $header_theme_class = 'header-theme-dashboard'; ?>
        <?php include 'includes/header.php'; ?>

        <!-- Ultra Premium Instant Financial Ticker -->
        <div class="glass-panel no-print" style="padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap; margin-bottom: 20px; border-left: 4px solid var(--theme-green); overflow: hidden; border-radius: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.2rem; animation: pulse 2s infinite;">📈</span>
                <span style="font-weight: 800; font-size: 0.9rem; color: var(--text-secondary);">ملخص الأرباح والخسائر الفوري (اليوم/الشهر):</span>
            </div>
            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                <div style="font-size: 0.9rem; font-weight: 700;">
                    <span style="color: var(--text-secondary);">مبيعات اليوم: </span>
                    <strong id="ticker-sales" style="color: var(--theme-blue);">0 SDG</strong>
                </div>
                <div style="font-size: 0.9rem; font-weight: 700;">
                    <span style="color: var(--text-secondary);">أرباح اليوم التقريبية: </span>
                    <strong id="ticker-profit" style="color: #34d399;">0 SDG</strong>
                </div>
                <div style="font-size: 0.9rem; font-weight: 700;">
                    <span style="color: var(--text-secondary);">أرباح الشهر الجاري: </span>
                    <strong id="ticker-monthly-profit" style="color: #34d399; font-weight: 900;">0 SDG</strong>
                </div>
                <div style="font-size: 0.9rem; font-weight: 700;">
                    <span style="color: var(--text-secondary);">إجمالي السيولة: </span>
                    <strong id="ticker-liquidity" style="color: var(--theme-green);">0 SDG</strong>
                </div>
            </div>
        </div>

        <!-- Ultra Premium Quick Actions Grid -->
        <div class="glass-panel" style="padding: 14px 18px;">
            
            <!-- Row 1: لوحة الإدارة والتحكم (الإعدادات والمهام) -->
            <div class="admin-actions-row">
                <div class="actions-row-title" style="min-width: auto; padding-left: 15px;">
                    <span>🛠️</span>
                </div>
                <div style="display: flex; gap: 12px; flex-wrap: wrap; flex: 1;">
                    <a data-href="settings.php" onclick="window.location.href='settings.php'" class="admin-action-btn admin-action-settings" style="flex: 1; justify-content: center; white-space: nowrap;">
                        <span class="qa-icon">⚙️</span> الإعدادات
                    </a>
                    
                    <a data-href="master-data.php" onclick="window.location.href='master-data.php'" class="admin-action-btn admin-action-master" style="flex: 1; justify-content: center; white-space: nowrap;">
                        <span class="qa-icon">🗂️</span> البيانات الأساسية
                    </a>
                    
                    <a data-href="jobs.php" onclick="window.location.href='jobs.php'" class="admin-action-btn admin-action-jobs" style="flex: 1; justify-content: center; white-space: nowrap;">
                        <span class="qa-icon">💼</span> المهام
                    </a>
                    
                    <a onclick="toggleNotifications()" class="admin-action-btn admin-action-notifications" style="flex: 1; justify-content: center; white-space: nowrap; cursor: pointer; position: relative;">
                        <span class="qa-icon">🔔</span> التنبيهات
                        <span id="notif-badge" style="display: none; position: absolute; top: -5px; right: -5px; background: var(--theme-red); color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.65rem; font-weight: bold; border: 1px solid #fff;">0</span>
                    </a>
                </div>
            </div>
            
            <!-- Row 2: العمليات الحالية والمبيعات والمشتريات -->
            <div class="operations-actions-row">
                <div class="actions-row-title" style="min-width: auto; padding-left: 15px;">
                    <span>📊</span>
                </div>
                <div style="display: flex; gap: 10px; overflow-x: auto; padding-bottom: 5px; flex-wrap: nowrap; flex: 1;">
                    <a data-href="direct-sales.php" onclick="window.location.href='direct-sales.php'" class="quick-action-btn qa-primary" style="flex: 1 1 auto; justify-content: center; white-space: nowrap; font-size: 0.85rem; padding: 8px 12px;">
                        <span class="qa-icon">⚡</span> مبيعات
                    </a>
                    
                    <a data-href="purchase.php" onclick="window.location.href='purchase.php'" class="quick-action-btn qa-warning" style="flex: 1 1 auto; justify-content: center; white-space: nowrap; font-size: 0.85rem; padding: 8px 12px;">
                        <span class="qa-icon">📦</span> مشتريات
                    </a>
                    
                    <a data-href="vouchers-hub.php" onclick="window.location.href='vouchers-hub.php'" class="quick-action-btn qa-danger" style="flex: 1 1 auto; justify-content: center; white-space: nowrap; font-size: 0.85rem; padding: 8px 12px;">
                        <span class="qa-icon">💸</span> المعاملات المالية
                    </a>
                    
                    <a data-href="checks-cycle.php" onclick="window.location.href='checks-cycle.php'" class="quick-action-btn qa-warning" style="flex: 1 1 auto; justify-content: center; white-space: nowrap; font-size: 0.85rem; padding: 8px 12px;">
                        <span class="qa-icon">🧾</span> شيكات
                    </a>
                    
                    <a data-href="analytics.php" onclick="window.location.href='analytics.php'" class="quick-action-btn qa-purple" style="flex: 1 1 auto; justify-content: center; white-space: nowrap; font-size: 0.85rem; padding: 8px 12px;">
                        <span class="qa-icon">📊</span> تقارير مالية
                    </a>
                    
                    <a data-href="inventory-report.php" onclick="window.location.href='inventory-report.php'" class="quick-action-btn" style="flex: 1 1 auto; justify-content: center; white-space: nowrap; font-size: 0.85rem; padding: 8px 12px; border-bottom: 2px solid #a78bfa;">
                        <span class="qa-icon">📦</span> المخازن
                    </a>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-cards">
            <div class="glass-panel spotlight-card neon-border-primary">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="kpi-title">مبيعات اليوم</span>
                    <div class="kpi-icon-bg" style="background: rgba(59, 130, 246, 0.1);"><span>🛒</span></div>
                </div>
                <h3 id="kpi-sales" class="kpi-val" style="color: var(--theme-blue);">0 SDG</h3>
                <div class="kpi-desc" style="color:#34d399;">↑ 15% مقارنة بالأمس</div>
            </div>
            
            <div class="glass-panel spotlight-card neon-border-success">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="kpi-title">السيولة (صندوق/بنك)</span>
                    <div class="kpi-icon-bg" style="background: rgba(16, 185, 129, 0.1);"><span>💰</span></div>
                </div>
                <h3 id="kpi-liquidity" class="kpi-val" style="color: var(--theme-green);">0 SDG</h3>
                <div class="kpi-desc" style="color:#34d399;">↑ 5% منذ بداية الأسبوع</div>
            </div>
            
            <div class="glass-panel spotlight-card neon-border-warning">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="kpi-title">إجمالي المخزون</span>
                    <div class="kpi-icon-bg" style="background: rgba(245, 158, 11, 0.1);"><span>📦</span></div>
                </div>
                <h3 id="kpi-inventory" class="kpi-val" style="color: var(--theme-orange);">0 صنف</h3>
                <div id="kpi-warehouses-count" class="kpi-desc" style="color:#fbbf24;">موزعة على 3 مخازن</div>
            </div>
            
            <div class="glass-panel spotlight-card neon-border-danger">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="kpi-title">تنبيهات مخزون</span>
                    <div class="kpi-icon-bg" style="background: rgba(248, 113, 113, 0.1);"><span>⚠️</span></div>
                </div>
                <h3 id="kpi-alerts" class="kpi-val" style="color: var(--theme-red);">0 أصناف</h3>
                <div class="kpi-desc" style="color:#f87171;">تحتاج لإعادة طلب سريع</div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="charts-grid">
            <!-- Cashflow -->
            <div class="glass-panel neon-border-success">
                <h3 style="color: var(--theme-green);">💹 التدفق النقدي (أسبوعي)</h3>
                <div class="chart-container"><canvas id="cashflowChart"></canvas></div>
            </div>
            <!-- Sales Distribution -->
            <div class="glass-panel neon-border-primary">
                <h3 style="color: var(--theme-blue);">📊 توزيع المبيعات حسب الفئة</h3>
                <div class="chart-container"><canvas id="categoryChart"></canvas></div>
            </div>
            <!-- Inventory Radar -->
            <div class="glass-panel neon-border-warning">
                <h3 style="color: var(--theme-orange);">🎯 حالة المخزون (الرادار)</h3>
                <div class="chart-container"><canvas id="inventoryChart"></canvas></div>
            </div>
            <!-- Warehouses Capacity -->
            <div class="glass-panel neon-border-danger">
                <h3 style="color: var(--theme-red);">🏢 السعة التخزينية للمخازن</h3>
                <div class="chart-container"><canvas id="warehousesChart"></canvas></div>
            </div>
        </div>

        <!-- Top Customers full-width -->
        <div class="glass-panel neon-border-purple" style="padding: 20px;">
            <h3 style="color: var(--theme-purple);">👥 المديونية وسقف الائتمان</h3>
            <div class="chart-container" style="height:280px;"><canvas id="customersChart"></canvas></div>
        </div>

        <!-- Bottom Section -->
        <div class="bottom-section">
            <!-- Recent Transactions -->
            <div class="glass-panel neon-border-primary">
                <h3>📋 أحدث العمليات</h3>
                <div style="overflow-x:auto;">
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>رقم المرجع</th>
                                <th>النوع</th>
                                <th>البيان</th>
                                <th>المبلغ</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody id="recent-transactions-tbody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Unreceived Permissions (Pending Deliveries) -->
            <div class="glass-panel neon-border-warning">
                <h3>⏳ أذونات لم تستلم (قيد الانتظار)</h3>
                <div id="pending-deliveries-list" style="display:flex; flex-direction:column; gap:12px;">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div class="glass-panel neon-border-danger">
                <h3>🔴 تنبيهات المخزون (نواقص)</h3>
                <div id="low-stock-list" style="display:flex; flex-direction:column; gap:12px;"></div>
            </div>
        </div>

        <!-- Sajana Protocol (Social & Security) -->
        <div class="glass-panel" style="text-align:center; border: 1px solid rgba(56,189,248,0.2); background: linear-gradient(to right, rgba(15,23,42,0.6), rgba(30,41,59,0.4)); padding: 20px;">
            <h3 style="margin:0 0 15px; font-size:1.1rem; color:var(--theme-blue); font-weight:900; justify-content: center;">
                🛡️ بروتوكول مقاييس الدولي - نظام الحماية
            </h3>
            
            <div style="display:flex; justify-content:center; gap:15px; flex-wrap:wrap;">
                <a href="https://facebook.com" target="_blank" title="صفحة الفيسبوك الرسمية" style="display:flex; align-items:center; justify-content:center; width:45px; height:45px; border-radius:50%; background:linear-gradient(135deg, #1877F2, #0d5bbd); color:#fff; text-decoration:none; font-size:1.4rem; transition:transform 0.3s; box-shadow:0 5px 15px rgba(24,119,242,0.3);" onmouseover="this.style.transform='scale(1.15) translateY(-3px)'" onmouseout="this.style.transform='scale(1) translateY(0)'">f</a>
                
                <a href="https://wa.me/249000000000" target="_blank" title="الدعم الفني عبر الواتساب" style="display:flex; align-items:center; justify-content:center; width:45px; height:45px; border-radius:50%; background:linear-gradient(135deg, #25D366, #1da851); color:#fff; text-decoration:none; font-size:1.4rem; transition:transform 0.3s; box-shadow:0 5px 15px rgba(37,211,102,0.3);" onmouseover="this.style.transform='scale(1.15) translateY(-3px)'" onmouseout="this.style.transform='scale(1) translateY(0)'">💬</a>
                
                <a href="https://tiktok.com" target="_blank" title="حساب تيك توك" style="display:flex; align-items:center; justify-content:center; width:45px; height:45px; border-radius:50%; background:linear-gradient(135deg, #333, #000); color:#fff; text-decoration:none; font-size:1.4rem; transition:transform 0.3s; box-shadow:0 5px 15px rgba(0,0,0,0.3);" onmouseover="this.style.transform='scale(1.15) translateY(-3px)'" onmouseout="this.style.transform='scale(1) translateY(0)'">🎵</a>
            </div>
        </div>
    </div>

    <!-- Notifications Modal Overlay -->
    <div id="notifications-dropdown" style="display:none; position:fixed; top:0; left:0; width: 100%; height:100vh; background:rgba(5,3,8,0.85); backdrop-filter:blur(10px); z-index:9999; justify-content:center; align-items:center; opacity:0; transition: opacity 0.3s ease;" onclick="toggleNotifications()">
        <div class="glass-panel" style="width:90%; max-width:450px; max-height:80vh; overflow-y:auto; padding:25px; border:1px solid rgba(255,255,255,0.1); border-radius:20px; position:relative; box-shadow: 0 25px 50px rgba(0,0,0,0.5);" onclick="event.stopPropagation()">
            <h3 style="margin:0 0 15px 0; padding-bottom:15px; border-bottom:1px dashed rgba(255,255,255,0.1); color:#fff; font-size:1.1rem; text-align:center; font-weight: 800; justify-content: center;">🔔 إشعارات المديونيات</h3>
            <div id="notif-list" style="display:flex; flex-direction:column; gap:12px;">
                <div style="color:var(--text-secondary); text-align:center; font-size:0.85rem; padding:15px;">لا توجد إشعارات حالياً</div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo time(); ?>"></script>
    <script>
        // Spotlight Effect Mouse Tracker
        document.addEventListener('mousemove', e => {
            for(const card of document.querySelectorAll('.spotlight-card')) {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                card.style.setProperty('--mouse-x', `${x}px`);
                card.style.setProperty('--mouse-y', `${y}px`);
            }
        });

        // Check Authentication
        document.addEventListener('DBReady', () => {
            if (!localStorage.getItem('authToken')) {
                window.location.href = 'login.php';
                return;
            }

            // Initialize Charts
            initProCharts();

            // Check Due Dates for Notifications
            checkDueDates();

            // Set Company Name dynamically if possible, or fallback
            const companyNameEl = document.getElementById('main-brand-name');
            const companyTopEl = document.getElementById('header-company-name-top');
            
            const storedCompanyName = localStorage.getItem('companyName') || 'نظام إدارة الشركة';
            
            if(companyNameEl) {
                companyNameEl.textContent = storedCompanyName;
            }
            if(companyTopEl) {
                companyTopEl.textContent = storedCompanyName;
            }

            // Load Settings for Athkar
            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                if (cfg.showAthkar !== false) { // Default is true
                    document.getElementById('dashboard-athkar-container').style.display = 'flex';
                }
            } catch (e) {}
        });

        // Reload page once background cloud sync completes (essential for iOS Safari onboarding)
        window.addEventListener('cloudDataRestored', () => {
            console.log("[Dashboard] Cloud sync completed. Reloading...");
            window.location.reload();
        });

        // Toggle Notifications Dropdown (Modal)
        function toggleNotifications() {
            const dropdown = document.getElementById('notifications-dropdown');
            if (dropdown.style.display === 'none' || dropdown.style.display === '') {
                dropdown.style.display = 'flex';
                setTimeout(() => dropdown.style.opacity = '1', 10);
            } else {
                dropdown.style.opacity = '0';
                setTimeout(() => dropdown.style.display = 'none', 300);
            }
        }

        // Check Due Dates
        function checkDueDates() {
            const invoices = (JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []).filter(x => !x.deleted);
            const customers = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
            
            let notifications = [];
            let unreadCount = 0;
            const today = new Date();
            today.setHours(0,0,0,0);

            invoices.forEach(inv => {
                if (inv.paymentMethod === 'credit' && inv.dueDate) {
                    const dueDate = new Date(inv.dueDate);
                    dueDate.setHours(0,0,0,0);
                    
                    const diffTime = dueDate - today;
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    
                    const cust = customers.find(c => c.id === inv.customer);
                    const custName = cust ? cust.name : inv.customer;
                    const custPhone = cust && cust.phone ? cust.phone : 'غير متوفر';

                    if (diffDays < 0) {
                        // Past due (Red)
                        notifications.push(`
                            <div style="padding:12px; background:rgba(248,113,113,0.1); border-radius:12px; border:1px solid rgba(248,113,113,0.3);">
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                    <strong style="color:#f87171; font-size:0.95rem;">🔴 تأخر سداد!</strong>
                                    <span style="font-size:0.75rem; color:var(--text-secondary); background:rgba(248,113,113,0.2); padding:2px 8px; border-radius:10px;">منذ ${Math.abs(diffDays)} أيام</span>
                                </div>
                                <div style="font-size:0.85rem; color:#fff; margin-bottom:6px; line-height: 1.5;">
                                    العميل: <strong style="color:var(--theme-blue);">${custName}</strong><br>
                                    فاتورة: ${inv.invoiceNo}
                                </div>
                                <div style="font-size:0.8rem; color:#fff; background: rgba(0,0,0,0.3); padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                    📞 الاتصال: ${custPhone}
                                </div>
                            </div>
                        `);
                        unreadCount++;
                    } else if (diffDays <= 2) {
                        // Nearing due date (Yellow)
                        let timeMsg = diffDays === 0 ? 'اليوم!' : (diffDays === 1 ? 'غداً' : 'بعد يومين');
                        notifications.push(`
                            <div style="padding:12px; background:rgba(251,191,36,0.1); border-radius:12px; border:1px solid rgba(251,191,36,0.3);">
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                    <strong style="color:#fbbf24; font-size:0.95rem;">🟡 اقترب موعد السداد</strong>
                                    <span style="font-size:0.75rem; color:var(--text-secondary); background:rgba(251,191,36,0.2); padding:2px 8px; border-radius:10px;">${timeMsg}</span>
                                </div>
                                <div style="font-size:0.85rem; color:#fff; margin-bottom:6px; line-height: 1.5;">
                                    العميل: <strong style="color:var(--theme-blue);">${custName}</strong><br>
                                    فاتورة: ${inv.invoiceNo}
                                </div>
                                <div style="font-size:0.8rem; color:#fff; background: rgba(0,0,0,0.3); padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                    📞 الاتصال: ${custPhone}
                                </div>
                            </div>
                        `);
                        unreadCount++;
                    }
                }
            });

            const badge = document.getElementById('notif-badge');
            const list = document.getElementById('notif-list');

            if (badge) {
                if (unreadCount > 0) {
                    badge.innerText = unreadCount;
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            }
            if (list) {
                if (unreadCount > 0) {
                    list.innerHTML = notifications.join('');
                } else {
                    list.innerHTML = '<div style="color:var(--text-secondary); text-align:center; font-size:0.85rem; padding:15px; background: rgba(255,255,255,0.02); border-radius: 10px;">لا توجد تنبيهات لفترات السماح حالياً 🎉</div>';
                }
            }
        }

        // Load Dynamic Dashboard Data
        function loadDynamicDashboardData() {
            // Fetch data
            const invoices = (JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []).filter(x => !x.deleted && x.saleType !== 'preliminary' && x.status !== 'preliminary');
            const banks = (JSON.parse(localStorage.getItem('erp_banks_v1')) || []).filter(x => !x.deleted);
            const items = (JSON.parse(localStorage.getItem('erp_items_v1')) || []).filter(x => !x.deleted);
            const gl = (JSON.parse(localStorage.getItem('erp_gl_v1')) || []).filter(x => !x.deleted);
            const customers = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
            const inventory = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];

            // Helper to get total actual stock of an item across warehouses (ignoring virtual pending states)
            function getItemActualStock(itemId) {
                return inventory
                    .filter(inv => inv.itemId === itemId && inv.warehouse !== 'PENDING_RECEIPT')
                    .reduce((sum, inv) => sum + (Number(inv.qty) || 0), 0);
            }

            // Get EXACT local date YYYY-MM-DD
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const todayStr = `${year}-${month}-${day}`;

            // 1. Calculate Today's Sales using grandTotal/totalAmount (not netTotal which doesn't exist)
            const todaySales = invoices.filter(inv => inv.date && inv.date.startsWith(todayStr)).reduce((sum, inv) => sum + (Number(inv.grandTotal) || Number(inv.totalAmount) || 0), 0);
            document.getElementById('kpi-sales').innerText = todaySales.toLocaleString('en-US') + ' SDG';

            // 2. Calculate Liquidity
            let totalLiquidity = banks.reduce((sum, b) => sum + (Number(b.currentBalance) || Number(b.openingBalance) || 0), 0);
            document.getElementById('kpi-liquidity').innerText = totalLiquidity.toLocaleString('en-US') + ' SDG';

            // 3. Total Items
            const totalItems = items.length;
            document.getElementById('kpi-inventory').innerText = totalItems.toLocaleString() + ' صنف';
            
            let warehousesList = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            if (warehousesList.length === 0) {
                warehousesList = [{ id: 'w-main', name: 'المخزن الرئيسي' }];
            }
            const activeWhsCount = warehousesList.filter(w => !w.deleted).length;
            const whsCountEl = document.getElementById('kpi-warehouses-count');
            if (whsCountEl) {
                whsCountEl.innerText = `موزعة على ${activeWhsCount} ${activeWhsCount > 2 && activeWhsCount < 11 ? 'مخازن' : 'مخزن'}`;
            }

            // 4. Low Stock computed from actual inventory levels
            const lowStockItems = items.filter(item => {
                let available = getItemActualStock(item.id);
                let safeLimit = Number(item.safeLimit) || Number(item.minQty) || 10;
                return available <= safeLimit;
            });
            document.getElementById('kpi-alerts').innerText = lowStockItems.length + ' أصناف';

            const lowStockHTML = lowStockItems.slice(0, 5).map(item => {
                const stockVal = getItemActualStock(item.id);
                return `
                <div class="list-item" style="background:rgba(248,113,113,0.1); border:1px solid rgba(248,113,113,0.2);">
                    <div class="list-icon" style="background:var(--theme-red); color:#fff; box-shadow: 0 3px 10px rgba(248,113,113,0.3);">!</div>
                    <div style="flex:1;">
                        <h4 class="list-title">${item.name}</h4>
                        <span class="list-sub">الرصيد: <strong style="color:var(--theme-red); font-size: 0.9rem;">${stockVal}</strong></span>
                    </div>
                    <a data-href="purchase.php" onclick="window.location.href='purchase.php'" class="list-btn" style="box-shadow: 0 2px 8px rgba(99,102,241,0.3);">طلب</a>
                </div>
                `;
            }).join('');
            document.getElementById('low-stock-list').innerHTML = lowStockHTML || '<div style="color:var(--text-secondary); font-size:0.85rem; background: rgba(255,255,255,0.02); padding: 15px; border-radius: 10px; text-align: center;">المخزون آمن حالياً ✅</div>';

            // Dynamic cost calculation
            const _cachedPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
            function getItemCost(itemId) {
                const item = items.find(i => i.id === itemId);
                if (item && item.openingQty > 0 && item.openingValue > 0) {
                    return Number(item.openingValue) / Number(item.openingQty);
                }
                const purchases = _cachedPurchases;
                let lastPrice = null;
                for (let inv of purchases) {
                    if (inv.deleted) continue;
                    const line = (inv.items || inv.lines || []).find(l => l.itemId === itemId || l.productId === itemId);
                    if (line && line.price) {
                        lastPrice = Number(line.price);
                        break;
                    }
                }
                if (lastPrice !== null) return lastPrice;
                if (item) {
                    let sp = item.sellingPrice || (item.units && item.units[0] ? item.units[0].cashPrice : 0) || 0;
                    return sp * 0.7; // default 30% margin fallback
                }
                return 0;
            }

            // Calculate Today's Profit & Monthly Profit
            const todayInvoices = invoices.filter(inv => inv.date && inv.date.startsWith(todayStr));
            let todayProfit = 0;
            todayInvoices.forEach(inv => {
                let invProfit = 0;
                (inv.items || []).forEach(line => {
                    const cost = getItemCost(line.itemId);
                    const sellPrice = Number(line.price) || 0;
                    const qty = Number(line.quantity) || 0;
                    invProfit += (sellPrice - cost) * qty;
                });
                const disc = Number(inv.discount) || 0;
                todayProfit += (invProfit - disc);
            });

            const currentMonthStr = `${year}-${month}`;
            const monthlyInvoices = invoices.filter(inv => inv.date && inv.date.startsWith(currentMonthStr));
            let monthlyProfit = 0;
            monthlyInvoices.forEach(inv => {
                let invProfit = 0;
                (inv.items || []).forEach(line => {
                    const cost = getItemCost(line.itemId);
                    const sellPrice = Number(line.price) || 0;
                    const qty = Number(line.quantity) || 0;
                    invProfit += (sellPrice - cost) * qty;
                });
                const disc = Number(inv.discount) || 0;
                monthlyProfit += (invProfit - disc);
            });

            // Update Ticker
            if (document.getElementById('ticker-sales')) document.getElementById('ticker-sales').innerText = todaySales.toLocaleString('en-US') + ' SDG';
            if (document.getElementById('ticker-profit')) document.getElementById('ticker-profit').innerText = todayProfit.toLocaleString('en-US') + ' SDG';
            if (document.getElementById('ticker-monthly-profit')) document.getElementById('ticker-monthly-profit').innerText = monthlyProfit.toLocaleString('en-US') + ' SDG';
            if (document.getElementById('ticker-liquidity')) document.getElementById('ticker-liquidity').innerText = totalLiquidity.toLocaleString('en-US') + ' SDG';

            // Trigger Stock Alerts Toast if any critical items
            const criticalItems = lowStockItems.filter(item => getItemActualStock(item.id) <= 0);
            if (criticalItems.length > 0 && !sessionStorage.getItem('stock_alert_shown')) {
                showToast(`⚠️ تنبيه مخزون: هناك ${criticalItems.length} أصناف نفذت تماماً من المخزن! يرجى مراجعة التنبيهات.`, 'error');
                sessionStorage.setItem('stock_alert_shown', 'true');
            }

            // 4.5 Pending Deliveries
            const pendingDeliveries = invoices.filter(inv => {
                const s = inv.deliveryStatus;
                return s !== 'delivered' && s !== 'Admin_Closed' && s !== 'Delivered_Pending_Admin';
            }).slice(0, 5);
            
            const pendingContainer = document.getElementById('pending-deliveries-list');
            if (pendingContainer) {
                if (pendingDeliveries.length > 0) {
                    pendingContainer.innerHTML = pendingDeliveries.map(inv => {
                        const custName = customers.find(c => c.id === inv.customerId)?.name || inv.customerName || inv.customerId || 'عميل';
                        const invDate = inv.date || '-';
                        return `
                        <div class="list-item" style="background:rgba(251,191,36,0.08); border:1px solid rgba(251,191,36,0.2);">
                            <div class="list-icon" style="background:var(--theme-orange); color:#fff; box-shadow: 0 3px 10px rgba(245,158,11,0.3);">🚚</div>
                            <div style="flex:1;">
                                <h4 class="list-title">إذن #${inv.invoiceNo}</h4>
                                <span class="list-sub"><span style="color:#e2e8f0;">${custName}</span> | ${invDate}</span>
                            </div>
                            <a data-href="sales-delivery.php" onclick="window.location.href='sales-delivery.php'" class="list-btn" style="box-shadow: 0 2px 8px rgba(99,102,241,0.3);">تسليم</a>
                        </div>
                        `;
                    }).join('');
                } else {
                    pendingContainer.innerHTML = '<div style="color:var(--text-secondary); text-align:center; font-size:0.85rem; padding: 15px; background: rgba(255,255,255,0.02); border-radius: 10px;">كل الأذونات مسلمة! 🎉</div>';
                }
            }

            // 5. Recent Transactions
            let allTx = [];
            invoices.forEach(i => allTx.push({ id: i.invoiceNo, type: 'مبيعات', desc: 'فاتورة مبيعات', amount: i.grandTotal, date: i.date }));
            
            const purchases = (JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || []).filter(x => !x.deleted && !x.isGRN && !x.isReturn);
            purchases.forEach(i => allTx.push({ id: i.invoiceNo, type: 'مشتريات', desc: 'فاتورة مشتريات', amount: i.grandTotal, date: i.date }));

            const sReceipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
            sReceipts.forEach(i => allTx.push({ id: i.id, type: 'قبض', desc: 'سند قبض مفرد', amount: i.amount, date: i.date }));

            const sPayments = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
            sPayments.forEach(i => allTx.push({ id: i.id, type: 'صرف', desc: 'سند صرف مفرد', amount: i.amount, date: i.date }));
            
            const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
            jvs.forEach(i => allTx.push({ id: i.id, type: 'قيد يومية', desc: i.statement || 'قيد مالي', amount: i.totalAmount, date: i.date }));

            allTx.sort((a, b) => new Date(b.date || 0) - new Date(a.date || 0));

            const recentTx = allTx.slice(0, 5);
            const recentHTML = recentTx.map(entry => {
                let badge = '<span style="background:rgba(16, 185, 129, 0.15); color:var(--theme-green); padding:4px 8px; border-radius:8px; font-size:0.75rem; font-weight: 800; border: 1px solid rgba(16,185,129,0.3);">مكتمل ✅</span>';
                return `
                <tr>
                    <td><strong style="color:var(--theme-blue);">#${entry.id}</strong></td>
                    <td><span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; font-size: 0.8rem;">${entry.type}</span></td>
                    <td>${entry.desc}</td>
                    <td><strong style="font-family: 'Outfit', sans-serif;">${(Number(entry.amount) || 0).toLocaleString('en-US')}</strong></td>
                    <td>${badge}</td>
                </tr>
                `;
            }).join('');
            document.getElementById('recent-transactions-tbody').innerHTML = recentHTML || '<tr><td colspan="5" style="text-align:center; padding:15px; color:var(--text-secondary); font-size: 0.85rem;">لا توجد حركات مالية مسجلة بعد.</td></tr>';

            return { invoices, items, customers, banks };
        }

        // Initialize Chart.js Graphs
        // Initialize Chart.js Graphs
        function initProCharts() {
            const data = loadDynamicDashboardData();
            const inventory = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];

            // Helper to get total actual stock of an item across warehouses (ignoring virtual pending states)
            function getItemActualStock(itemId) {
                return inventory
                    .filter(inv => inv.itemId === itemId && inv.warehouse !== 'PENDING_RECEIPT')
                    .reduce((sum, inv) => sum + (Number(inv.qty) || 0), 0);
            }
            
            const textColor = '#94a3b8';
            const gridColor = 'rgba(255, 255, 255, 0.05)';

            Chart.defaults.color = textColor;
            Chart.defaults.font.family = "'Tajawal', sans-serif";
            Chart.defaults.font.size = 11; // Smaller default font

            // 1. Cashflow Chart (Dynamic Weekly) using grandTotal/totalAmount
            const sReceipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
            const sPayments = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
            
            let daysLabels = [];
            let receiptsData = [0,0,0,0,0,0,0];
            let paymentsData = [0,0,0,0,0,0,0];
            
            const today = new Date();
            for(let i=6; i>=0; i--) {
                let d = new Date(today);
                d.setDate(d.getDate() - i);
                const dayStr = d.toLocaleDateString('ar-EG', { weekday: 'short' });
                daysLabels.push(dayStr);
                
                const dStr = d.toISOString().split('T')[0];
                
                let dayReceipts = sReceipts.filter(r => (r.date || '').startsWith(dStr) && !r.deleted).reduce((sum, r) => sum + (Number(r.amount) || 0), 0);
                let cashSales = data.invoices.filter(inv => (inv.date || '').startsWith(dStr) && inv.paymentMethod !== 'credit' && !inv.deleted).reduce((sum, inv) => sum + (Number(inv.grandTotal) || Number(inv.totalAmount) || 0), 0);
                
                let dayPayments = sPayments.filter(p => (p.date || '').startsWith(dStr) && !p.deleted).reduce((sum, p) => sum + (Number(p.amount) || 0), 0);
                const purchases = (JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || []).filter(x => !x.deleted);
                let cashPurchases = purchases.filter(inv => (inv.date || '').startsWith(dStr) && inv.paymentMethod !== 'credit').reduce((sum, inv) => sum + (Number(inv.grandTotal) || Number(inv.totalAmount) || 0), 0);

                receiptsData[6-i] = dayReceipts + cashSales;
                paymentsData[6-i] = dayPayments + cashPurchases;
            }

            new Chart(document.getElementById('cashflowChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: daysLabels,
                    datasets: [
                        { label: 'المقبوضات', data: receiptsData, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.15)', fill: true, borderWidth: 2, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#10b981' },
                        { label: 'المصروفات', data: paymentsData, borderColor: '#f87171', backgroundColor: 'rgba(248,113,113,0.15)', fill: true, borderWidth: 2, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#f87171' }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { usePointStyle: true, padding: 10, font: {family: "'Tajawal'", size: 11} } } }, scales: { x: { grid: { color: gridColor } }, y: { grid: { color: gridColor } } } }
            });

            // 2. Sales by Category (Dynamic from Sales Invoices)
            const catSales = {};
            data.invoices.forEach(inv => {
                if(inv.items && Array.isArray(inv.items)) {
                    inv.items.forEach(item => {
                        const itemDef = data.items.find(i => i.id === item.itemId);
                        let c = (itemDef ? itemDef.category : '') || 'أخرى';
                        let quantity = Number(item.quantity) || Number(item.qty) || 0;
                        let price = Number(item.price) || 0;
                        let total = quantity * price;
                        catSales[c] = (catSales[c] || 0) + total;
                    });
                }
            });
            const catLabels = Object.keys(catSales).length > 0 ? Object.keys(catSales) : ['بدون مبيعات'];
            const catData = Object.keys(catSales).length > 0 ? Object.values(catSales) : [1];

            new Chart(document.getElementById('categoryChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: catLabels,
                    datasets: [{ data: catData, backgroundColor: ['#3b82f6', '#f59e0b', '#f87171', '#10b981', '#c084fc', '#ec4899'], borderWidth: 0, hoverOffset: 10 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'right', labels: { usePointStyle: true, padding: 10, font: {family: "'Tajawal'", size: 11} } }, tooltip: { callbacks: { label: function(context) { return ' ' + context.label + ': ' + context.raw.toLocaleString() + ' SDG'; } } } } }
            });

            // 3. Inventory Radar (Dynamic - Lowest Stock Items)
            let radarItemsList = [...data.items].map(i => {
                let avail = getItemActualStock(i.id);
                let safe = Number(i.safeLimit) || Number(i.minQty) || 10;
                let ratio = safe > 0 ? avail / safe : 1;
                return { ...i, avail, safe, ratio };
            });
            radarItemsList.sort((a, b) => a.ratio - b.ratio);
            const radarItems = radarItemsList.slice(0, 5);
            
            const radarLabels = radarItems.length > 0 ? radarItems.map(i => i.name) : ['لا يوجد'];
            const radarAvail = radarItems.length > 0 ? radarItems.map(i => i.avail) : [0];
            const radarSafe = radarItems.length > 0 ? radarItems.map(i => i.safe) : [0];

            new Chart(document.getElementById('inventoryChart').getContext('2d'), {
                type: 'radar',
                data: {
                    labels: radarLabels,
                    datasets: [
                        { label: 'المتوفر', data: radarAvail, backgroundColor: 'rgba(248,113,113,0.2)', borderColor: '#f87171', pointBackgroundColor: '#f87171', borderWidth: 1.5 },
                        { label: 'حد الأمان', data: radarSafe, backgroundColor: 'rgba(59,130,246,0.2)', borderColor: '#3b82f6', pointBackgroundColor: '#3b82f6', borderWidth: 1.5 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { usePointStyle: true, padding: 10, font: {family: "'Tajawal'", size: 11} } } }, scales: { r: { grid: { color: gridColor }, angleLines: { color: gridColor }, ticks: { display: false } } } }
            });

            // 4. Warehouses Capacity (Actual Item Stock Quantities in Each Warehouse)
            const whTotals = {};
            let warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            if (warehouses.length === 0) {
                warehouses = [{ id: 'w-main', name: 'المخزن الرئيسي' }];
            }
            const activeWarehouses = warehouses.filter(w => !w.deleted);
            activeWarehouses.forEach(w => {
                whTotals[w.name || w.id] = 0;
            });
            inventory.forEach(inv => {
                if (inv.warehouse !== 'PENDING_RECEIPT') {
                    const w = warehouses.find(wh => wh.id === inv.warehouse || wh.name === inv.warehouse);
                    if (w && !w.deleted) {
                        const name = w.name || w.id;
                        whTotals[name] = (whTotals[name] || 0) + (Number(inv.qty) || Number(inv.quantity) || 0);
                    } else if (!w) {
                        const name = inv.warehouse;
                        whTotals[name] = (whTotals[name] || 0) + (Number(inv.qty) || Number(inv.quantity) || 0);
                    }
                }
            });

            const whLabels = Object.keys(whTotals).length > 0 ? Object.keys(whTotals) : ['لا توجد مخازن'];
            const whData = Object.keys(whTotals).length > 0 ? Object.values(whTotals) : [0];

            new Chart(document.getElementById('warehousesChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: whLabels,
                    datasets: [{ data: whData, backgroundColor: ['#f87171', '#3b82f6', '#10b981', '#f59e0b', '#c084fc'], borderWidth: 0, hoverOffset: 10 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 10, font: {family: "'Tajawal'", size: 11} } }, tooltip: { callbacks: { label: function(context) { return ' ' + context.label + ': ' + context.raw.toLocaleString() + ' وحدة'; } } } } }
            });

            // 5. Customer Credit (Horizontal Bar with clean parse)
            const activeCustomers = data.customers.filter(c => (parseFloat(c.creditLimit) || 0) > 0 || (parseFloat(c.currentBalance) || 0) > 0);
            activeCustomers.sort((a, b) => (parseFloat(b.currentBalance) || 0) - (parseFloat(a.currentBalance) || 0));
            const topCustomers = activeCustomers.slice(0, 5);

            const custLabels = topCustomers.length > 0 ? topCustomers.map(c => c.name) : ['لا توجد بيانات'];
            const custDebt = topCustomers.length > 0 ? topCustomers.map(c => {
                let debt = parseFloat(c.currentBalance) || 0;
                return debt > 0 ? debt : 0;
            }) : [0];
            const custRemaining = topCustomers.length > 0 ? topCustomers.map(c => {
                let debt = parseFloat(c.currentBalance) || 0;
                let limit = parseFloat(c.creditLimit) || 0;
                let rem = limit - debt;
                return rem > 0 ? rem : 0;
            }) : [0];

            new Chart(document.getElementById('customersChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: custLabels,
                    datasets: [
                        { label: 'المديونية الحالية', data: custDebt, backgroundColor: 'rgba(248,113,113,0.85)', borderRadius: 4 },
                        { label: 'المتبقي من السقف', data: custRemaining, backgroundColor: 'rgba(16,185,129,0.85)', borderRadius: 4 }
                    ]
                },
                options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { usePointStyle: true, padding: 10, font: {family: "'Tajawal'", size: 11} } } }, scales: { x: { stacked: true, grid: { color: gridColor } }, y: { stacked: true, grid: { display: false }, barPercentage: 0.6 } } }
            });
        }
    </script>

        <!-- AI CFO Modal -->
        <div id="aiCFO-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center; direction: rtl;">
            <div class="glass-panel" style="width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto; padding: 25px; position: relative;">
                <button onclick="document.getElementById('aiCFO-modal').style.display='none'" style="position: absolute; top: 15px; left: 15px; background: none; border: none; color: #f43f5e; font-size: 1.5rem; cursor: pointer;">&times;</button>
                <h3 style="color: #0ea5e9; margin-bottom: 20px; font-weight: 800; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
                    <span style="font-size: 1.5rem;">🤖</span> تقرير المستشار المالي (AI CFO)
                </h3>
                <div id="aiCFO-content" style="color: #e2e8f0; line-height: 1.8; font-size: 1.05rem;">
                    جاري تحليل بيانات الشركة واستخراج التقرير... يرجى الانتظار ⏳
                </div>
            </div>
        </div>

        <script>
        function showToast(message, type = 'success') {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'toast-container';
                container.style.cssText = 'position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none;';
                document.body.appendChild(container);
            }
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            let bg = 'rgba(52,211,153,0.15)';
            let border = '1px solid rgba(52,211,153,0.4)';
            let color = '#34d399';
            if (type === 'error') {
                bg = 'rgba(239,68,68,0.15)';
                border = '1px solid rgba(239,68,68,0.4)';
                color = '#f87171';
            } else if (type === 'warning') {
                bg = 'rgba(251,191,36,0.15)';
                border = '1px solid rgba(251,191,36,0.4)';
                color = '#fbbf24';
            }
            toast.style.cssText = `padding:12px 20px; border-radius:12px; font-weight:700; font-size:0.85rem; display:flex; align-items:center; gap:8px; pointer-events:auto; backdrop-filter:blur(20px); box-shadow:0 12px 40px rgba(0,0,0,0.5); color:${color}; background:${bg}; border:${border}; font-family:'Tajawal',sans-serif; animation: toastIn 0.4s cubic-bezier(0.16,1,0.3,1) both;`;
            toast.innerHTML = `<span>${type === 'error' ? '🔴' : (type === 'warning' ? '🟡' : '🟢')}</span> ${message}`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.animation = 'toastOut 0.4s cubic-bezier(0.16,1,0.3,1) both';
                setTimeout(() => toast.remove(), 400);
            }, 5000);
        }

        function runAICFO() {
            document.getElementById('aiCFO-modal').style.display = 'flex';
            document.getElementById('aiCFO-content').innerHTML = 'جاري تحليل بيانات الشركة واستخراج التقرير... يرجى الانتظار ⏳';
            
            // Gather KPI Data from existing DOM elements
            let sales = document.getElementById('kpi-sales') ? document.getElementById('kpi-sales').innerText : 'غير متاح';
            let liquidity = document.getElementById('kpi-liquidity') ? document.getElementById('kpi-liquidity').innerText : 'غير متاح';
            let inventory = document.getElementById('kpi-inventory') ? document.getElementById('kpi-inventory').innerText : 'غير متاح';
            let alerts = document.getElementById('kpi-alerts') ? document.getElementById('kpi-alerts').innerText : 'غير متاح';
            
            let context = `مبيعات اليوم: ${sales} | السيولة المتوفرة: ${liquidity} | الأصناف في المخزون: ${inventory} | نواقص المخزون: ${alerts}`;

            // Try to extract some customer debt info safely
            try {
                let custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
                let debtSum = 0;
                custs.forEach(c => { if(c.currentBalance > 0) debtSum += c.currentBalance; });
                context += ` | إجمالي ديون العملاء علينا أو لنا: ${debtSum}`;
            } catch(e) {}

            let promptMsg = "أنت المدير المالي (CFO) للشركة. بناءً على هذه المؤشرات، اكتب تقريراً إدارياً قصيراً جداً (3 فقرات كحد أقصى) يشرح لصاحب العمل وضع شركته اليوم (الإيجابيات، السلبيات أو المخاطر إن وجدت، توصية سريعة).";

            fetch('api/ai-assistant.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: promptMsg, context: context })
            })
            .then(res => res.json())
            .then(data => {
                if (data.reply) {
                    document.getElementById('aiCFO-content').innerHTML = data.reply.replace(/\*\*(.*?)\*\*/g, '<strong style="color:#38bdf8">$1</strong>').replace(/\n/g, '<br>');
                } else {
                    document.getElementById('aiCFO-content').innerHTML = '<span style="color:#f43f5e">حدث خطأ أثناء استخراج التقرير.</span>';
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('aiCFO-content').innerHTML = '<span style="color:#f43f5e">تعذر الاتصال بخادم الذكاء الاصطناعي.</span>';
            });
        }
        window.addEventListener('themeChanged', () => {
            location.reload();
        });
        </script>
    
</body>
</html>

