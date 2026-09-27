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
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="dns-prefetch" href="https://cdn.jsdelivr.net"><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>لوحة التحكم</title>
    <link rel="preload" href="css/design-system.css?v=20260723_v14" as="style">
    <link rel="stylesheet" href="css/design-system.css?v=20260723_v14">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/unified-brain.css?v=20260722">
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
            position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.12;
            animation: floatOrb 20s infinite alternate ease-in-out;
            will-change: transform;
            contain: layout style paint;
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

        /* Compact KPI Cards - Slim Executive 6-Card Grid (50% Reduced Height) */
        .kpi-cards {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(165px, 1fr)); gap: 8px;
        }
        .kpi-cards .glass-panel {
            padding: 5px 10px !important;
            border-radius: 10px !important;
            min-height: 44px !important;
        }
        .kpi-title { color: #ffffff !important; font-size: 0.74rem !important; font-weight: 800 !important; letter-spacing: 0.2px; text-shadow: 0 1px 2px rgba(0,0,0,0.5); }
        .kpi-icon-bg { padding: 2px 5px !important; border-radius: 6px !important; }
        .kpi-icon-bg span { font-size: 0.75rem !important; }
        .kpi-val { margin:0 !important; font-size:0.80rem !important; font-weight:800; font-family: 'Outfit', 'Tajawal', sans-serif; line-height: 1.1 !important; }
        .kpi-desc { font-size:0.6rem !important; margin-top:0 !important; font-weight: 700; }

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
            width: 38px; /* Slightly larger wrapper */
            height: 38px;
            border-radius: 10px; /* Sharp rounded corners */
            border: 2px solid rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.04);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            margin-left: 8px; /* Spacing for RTL */
            flex-shrink: 0;
        }
        
        /* Default/Dark Mode: Color coding for the quick action buttons */
        /* 1. Settings */
        .admin-action-settings .qa-icon { background: rgba(100, 116, 139, 0.15); border-color: rgba(100, 116, 139, 0.35); }
        /* 2. Master Data */
        .admin-action-master .qa-icon { background: rgba(59, 130, 246, 0.15); border-color: rgba(59, 130, 246, 0.35); }
        /* 3. Jobs/Tasks */
        .admin-action-jobs .qa-icon { background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.35); }
        /* 4. Notifications */
        .admin-action-notifications .qa-icon { background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.35); }
        /* 5. Sales (qa-primary) */
        .qa-primary .qa-icon { background: rgba(56, 189, 248, 0.15); border-color: rgba(56, 189, 248, 0.35); }
        /* 6. Purchases & 8. Checks (qa-warning) */
        .qa-warning .qa-icon { background: rgba(251, 191, 36, 0.15); border-color: rgba(251, 191, 36, 0.35); }
        /* 7. Transactions (qa-danger) */
        .qa-danger .qa-icon { background: rgba(248, 113, 113, 0.15); border-color: rgba(248, 113, 113, 0.35); }
        /* 9. Reports (qa-purple) */
        .qa-purple .qa-icon { background: rgba(192, 132, 252, 0.15); border-color: rgba(192, 132, 252, 0.35); }
        /* 10. Warehouses (inventory-report link) */
        a[data-href="inventory-report.php"] .qa-icon { background: rgba(167, 139, 250, 0.15); border-color: rgba(167, 139, 250, 0.35); }

        /* Hover states in default/Dark Mode: scale to 1.4, border to 4.5px, background darkens */
        .admin-action-btn:hover .qa-icon,
        .quick-action-btn:hover .qa-icon {
            transform: scale(1.4) rotate(5deg) translateY(-2px) !important;
            border-width: 4.5px !important;
        }
        
        .admin-action-settings:hover .qa-icon { background: rgba(100, 116, 139, 0.45) !important; border-color: #64748b !important; }
        .admin-action-master:hover .qa-icon { background: rgba(59, 130, 246, 0.45) !important; border-color: #3b82f6 !important; }
        .admin-action-jobs:hover .qa-icon { background: rgba(16, 185, 129, 0.45) !important; border-color: #10b981 !important; }
        .admin-action-notifications:hover .qa-icon { background: rgba(239, 68, 68, 0.45) !important; border-color: #ef4444 !important; }
        .qa-primary:hover .qa-icon { background: rgba(56, 189, 248, 0.45) !important; border-color: #38bdf8 !important; }
        .qa-warning:hover .qa-icon { background: rgba(251, 191, 36, 0.45) !important; border-color: #fbbf24 !important; }
        .qa-danger:hover .qa-icon { background: rgba(248, 113, 113, 0.45) !important; border-color: #f87171 !important; }
        .qa-purple:hover .qa-icon { background: rgba(192, 132, 252, 0.45) !important; border-color: #c084fc !important; }
        a[data-href="inventory-report.php"]:hover .qa-icon { background: rgba(167, 139, 250, 0.45) !important; border-color: #a78bfa !important; }

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

        /* Ultra-Premium Glassmorphic Screen Dropdown & Animated Micro-Interactions */
        #screens-dropdown-menu {
            transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1), transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* 💎 Ultra-Premium Master Quick Action Buttons (15% Wider, 52px Height, Equal Size) */
        .qa-master-btn {
            position: relative;
            overflow: hidden;
            height: 52px !important;
            min-height: 52px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 10px !important;
            padding: 10px 18px !important; /* 15% wider padding */
            border-radius: 14px !important;
            font-size: 0.92rem !important;
            font-weight: 900 !important;
            color: #ffffff !important;
            background: rgba(15, 23, 42, 0.78) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.14) !important;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(14px);
            transition: all 0.35s cubic-bezier(0.25, 1, 0.5, 1) !important;
            cursor: pointer !important;
            white-space: nowrap !important;
            text-overflow: ellipsis !important;
            text-decoration: none !important;
            box-sizing: border-box !important;
            flex: 0 0 auto !important; /* Prevent shrinking when in scrollable flex container */
        }

        /* Animated Light Wave Shimmer Effect */
        .qa-master-btn::before {
            content: '';
            position: absolute;
            top: 0; left: -150%;
            width: 70%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.38), transparent);
            transform: skewX(-25deg);
            pointer-events: none;
            transition: none;
        }

        .qa-master-btn:hover::before {
            left: 170%;
            transition: all 0.75s ease-in-out;
        }

        /* Pulsing Light Wave Aura on Hover */
        .qa-master-btn:hover {
            transform: translateY(-5px) scale(1.04) !important;
            background: rgba(30, 41, 59, 0.95) !important;
            border-color: var(--btn-border-color, #a855f7) !important;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.6), 0 0 25px var(--btn-glow-color, rgba(168, 85, 247, 0.6)), inset 0 0 12px var(--btn-glow-color, rgba(168, 85, 247, 0.2)) !important;
            z-index: 10;
        }

        /* Icon Micro-Animations & Glow */
        .qa-master-btn .qa-icon {
            width: 30px !important;
            height: 30px !important;
            min-width: 30px !important;
            font-size: 0.9rem !important;
            border-radius: 8px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex-shrink: 0 !important;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.35s ease !important;
        }

        .qa-master-btn:hover .qa-icon {
            transform: scale(1.35) rotate(8deg) !important;
            box-shadow: 0 0 16px var(--btn-glow-color, rgba(168, 85, 247, 0.8)) !important;
        }

        .qa-master-btn .btn-text {
            font-size: 0.92rem !important;
            font-weight: 900 !important;
            color: #ffffff !important;
            transition: text-shadow 0.3s ease, color 0.3s ease;
        }

        .qa-master-btn:hover .btn-text {
            color: #ffffff !important;
            text-shadow: 0 0 12px var(--btn-glow-color, rgba(255, 255, 255, 0.9)) !important;
        }

        #screens-dropdown-list {
            scroll-behavior: smooth !important;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
            padding-bottom: 30px !important;
        }
        #screens-dropdown-list::-webkit-scrollbar {
            width: 7px;
        }
        #screens-dropdown-list::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.7);
            border-radius: 10px;
        }
        #screens-dropdown-list::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #a855f7, #6366f1);
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        #screens-dropdown-list::-webkit-scrollbar-thumb:hover {
            background: #c084fc;
        }

        .scope-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #94a3b8;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.25s ease;
            font-family: 'Tajawal', sans-serif;
        }
        .scope-btn:hover {
            background: rgba(56, 189, 248, 0.15);
            border-color: rgba(56, 189, 248, 0.4);
            color: #38bdf8;
        }
        .scope-btn.active {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.3), rgba(99, 102, 241, 0.3));
            border-color: #38bdf8;
            color: #ffffff;
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.4);
        }

        .screen-popover-item {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 12px 16px !important;
            min-height: 52px !important;
            border-radius: 14px !important;
            background: rgba(15, 23, 42, 0.88) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.14) !important;
            text-decoration: none !important;
            transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1) !important;
            cursor: pointer;
            box-sizing: border-box;
        }

        /* Glass Shimmer Overlay Sweep */
        .screen-popover-item::after {
            content: '';
            position: absolute;
            top: 0; left: -150%;
            width: 80%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
            transform: skewX(-20deg);
            transition: none;
            pointer-events: none;
        }

        .screen-popover-item:hover::after {
            left: 150%;
            transition: all 0.6s ease-in-out;
        }

        /* Hover Motion & Dynamic Glow */
        .screen-popover-item:hover {
            transform: translateX(-10px) translateY(-2px) scale(1.03) !important;
            background: rgba(30, 41, 59, 0.98) !important;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.7), 0 0 25px var(--item-glow-color, rgba(168, 85, 247, 0.6)), 0 0 40px var(--item-glow-color, rgba(168, 85, 247, 0.3)) !important;
            border-color: var(--item-border-color, #c084fc) !important;
            z-index: 5;
        }

        .screen-popover-item .qa-icon {
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.35s ease !important;
        }

        .screen-popover-item:hover .qa-icon {
            transform: scale(1.4) rotate(8deg) !important;
            box-shadow: 0 0 20px var(--item-glow-color, rgba(168, 85, 247, 0.8)), 0 0 35px var(--item-glow-color, rgba(168, 85, 247, 0.5)) !important;
        }

        .screen-popover-item .screen-title {
            font-size: 1.0rem !important; /* Increased font by 1 step */
            font-weight: 900 !important;
            letter-spacing: 0.4px;
            color: #ffffff !important;
            transition: color 0.3s ease, text-shadow 0.3s ease;
        }

        .screen-popover-item:hover .screen-title {
            color: #ffffff !important;
            text-shadow: 0 0 12px var(--item-glow-color, rgba(255, 255, 255, 0.9)) !important;
        }

        .screen-popover-item .screen-arrow {
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .screen-popover-item:hover .screen-arrow {
            transform: translateX(-6px) scale(1.3);
            opacity: 1 !important;
        }
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
        :root.light-theme .admin-action-settings .qa-icon { background: rgba(100, 116, 139, 0.1) !important; border-color: rgba(100, 116, 139, 0.35) !important; }
        :root.light-theme .admin-action-master .qa-icon { background: rgba(59, 130, 246, 0.1) !important; border-color: rgba(59, 130, 246, 0.35) !important; }
        :root.light-theme .admin-action-jobs .qa-icon { background: rgba(16, 185, 129, 0.1) !important; border-color: rgba(16, 185, 129, 0.35) !important; }
        :root.light-theme .admin-action-notifications .qa-icon { background: rgba(239, 68, 68, 0.1) !important; border-color: rgba(239, 68, 68, 0.35) !important; }
        :root.light-theme .qa-primary .qa-icon { background: rgba(56, 189, 248, 0.1) !important; border-color: rgba(56, 189, 248, 0.35) !important; }
        :root.light-theme .qa-warning .qa-icon { background: rgba(251, 191, 36, 0.1) !important; border-color: rgba(251, 191, 36, 0.35) !important; }
        :root.light-theme .qa-danger .qa-icon { background: rgba(248, 113, 113, 0.1) !important; border-color: rgba(248, 113, 113, 0.35) !important; }
        :root.light-theme .qa-purple .qa-icon { background: rgba(192, 132, 252, 0.1) !important; border-color: rgba(192, 132, 252, 0.35) !important; }
        :root.light-theme a[data-href="inventory-report.php"] .qa-icon { background: rgba(167, 139, 250, 0.1) !important; border-color: rgba(167, 139, 250, 0.35) !important; }

        /* Hover states in Light Mode: backgrounds darken, borders thicken (4.5px), scale up to 1.4 */
        :root.light-theme .admin-action-btn:hover .qa-icon,
        :root.light-theme .quick-action-btn:hover .qa-icon {
            border-width: 4.5px !important;
            transform: scale(1.4) rotate(5deg) translateY(-2px) !important;
        }
        
        :root.light-theme .admin-action-settings:hover .qa-icon { background: rgba(100, 116, 139, 0.3) !important; border-color: #64748b !important; }
        :root.light-theme .admin-action-master:hover .qa-icon { background: rgba(59, 130, 246, 0.3) !important; border-color: #3b82f6 !important; }
        :root.light-theme .admin-action-jobs:hover .qa-icon { background: rgba(16, 185, 129, 0.3) !important; border-color: #10b981 !important; }
        :root.light-theme .admin-action-notifications:hover .qa-icon { background: rgba(239, 68, 68, 0.3) !important; border-color: #ef4444 !important; }
        :root.light-theme .qa-primary:hover .qa-icon { background: rgba(56, 189, 248, 0.3) !important; border-color: #38bdf8 !important; }
        :root.light-theme .qa-warning:hover .qa-icon { background: rgba(251, 191, 36, 0.3) !important; border-color: #fbbf24 !important; }
        :root.light-theme .qa-danger:hover .qa-icon { background: rgba(248, 113, 113, 0.3) !important; border-color: #f87171 !important; }
        :root.light-theme .qa-purple:hover .qa-icon { background: rgba(192, 132, 252, 0.3) !important; border-color: #c084fc !important; }
        :root.light-theme a[data-href="inventory-report.php"]:hover .qa-icon { background: rgba(167, 139, 250, 0.3) !important; border-color: #a78bfa !important; }
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
\n
        /* =======================================================================
           تعديلات التبسيط الجمالي لتوحيد التصميم وجعله مريحاً للعين (حذف النيون والألوان الصارخة)
           ======================================================================= */
        .neon-border-primary,
        .neon-border-success,
        .neon-border-warning,
        .neon-border-danger,
        .neon-border-purple {
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        :root.light-theme .neon-border-primary,
        :root.light-theme .neon-border-success,
        :root.light-theme .neon-border-warning,
        :root.light-theme .neon-border-danger,
        :root.light-theme .neon-border-purple {
            border: 1px solid rgba(0, 0, 0, 0.06) !important;
            border-top: 1px solid rgba(0, 0, 0, 0.06) !important;
        }

        .quick-action-btn, .admin-action-btn {
            border-bottom: 1.5px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15) !important;
            transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1) !important;
        }

        .quick-action-btn:hover, .admin-action-btn:hover {
            transform: translateY(-2px) !important;
            background: rgba(255, 255, 255, 0.08) !important;
            border-color: rgba(255, 255, 255, 0.25) !important;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25) !important;
            color: #fff !important;
            animation: none !important;
        }

        .qa-primary, .qa-warning, .qa-success, .qa-danger, .qa-purple,
        .admin-action-settings, .admin-action-master, .admin-action-jobs, .admin-action-notifications {
            border-bottom: 1.5px solid rgba(255, 255, 255, 0.08) !important;
        }
        
        .qa-primary:hover, .qa-warning:hover, .qa-success:hover, .qa-danger:hover, .qa-purple:hover,
        .admin-action-settings:hover, .admin-action-master:hover, .admin-action-jobs:hover, .admin-action-notifications:hover {
            background: rgba(255, 255, 255, 0.08) !important;
            color: #fff !important;
            border-color: rgba(255, 255, 255, 0.25) !important;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25) !important;
        }

        :root.light-theme .quick-action-btn, 
        :root.light-theme .admin-action-btn,
        :root.light-theme .qa-primary, 
        :root.light-theme .qa-warning, 
        :root.light-theme .qa-success, 
        :root.light-theme .qa-danger, 
        :root.light-theme .qa-purple,
        :root.light-theme .admin-action-settings, 
        :root.light-theme .admin-action-master, 
        :root.light-theme .admin-action-jobs, 
        :root.light-theme .admin-action-notifications {
            background: #ffffff !important;
            color: #1e293b !important;
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
            border-bottom: 1.5px solid rgba(0, 0, 0, 0.12) !important;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.04) !important;
        }
        
        :root.light-theme .quick-action-btn:hover, 
        :root.light-theme .admin-action-btn:hover,
        :root.light-theme .qa-primary:hover, 
        :root.light-theme .qa-warning:hover, 
        :root.light-theme .qa-success:hover, 
        :root.light-theme .qa-danger:hover, 
        :root.light-theme .qa-purple:hover,
        :root.light-theme .admin-action-settings:hover, 
        :root.light-theme .admin-action-master, 
        :root.light-theme .admin-action-jobs, 
        :root.light-theme .admin-action-notifications {
            background: rgba(0, 0, 0, 0.02) !important;
            color: #0f172a !important;
            border-color: rgba(0, 0, 0, 0.18) !important;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.06) !important;
        }
    
</style>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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



        <!-- Ultra Premium Quick Actions Bar (Top Header Row for Controls + 7 Master Buttons Below) -->
        <div class="glass-panel" id="qa-bar-container" style="padding: 14px 18px; border-radius: 18px; border: 1.5px solid rgba(255, 255, 255, 0.1); background: var(--surface); margin-bottom: 14px; position: relative;" oncontextmenu="openQACustomizer(event); return false;">
            
            <!-- Top Sub-Row: Title on Right, Customize (7) & Screens Master Buttons on Left -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                <div style="font-weight: 900; font-size: 0.88rem; color: #cbd5e1; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-wand-magic-sparkles text-amber-400" style="font-size: 1.05rem;"></i> أزرار الوصول السريع المخصصة (7 أزرار)
                    <span style="font-size: 0.72rem; font-weight: 600; color: #94a3b8; margin-right: 6px;">(انقر بالزر الأيمن 🖱️ لتخصيص الأزرار السبعة)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <!-- Track Who Did What Button -->
                    <button onclick="window.openAuditTrackerModal ? window.openAuditTrackerModal() : window.location.href='audit-log.php'" class="qa-master-btn" style="--btn-border-color: #f59e0b; --btn-glow-color: rgba(245, 158, 11, 0.6); border-bottom: 3.5px solid #f59e0b !important; min-width: 175px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.25), rgba(239, 68, 68, 0.2)) !important;" title="تتبع نشاط المستخدمين ومن فعل ماذا والبحث عن أي إذن أو فاتورة (Alt+T)">
                        <span class="qa-icon" style="background: rgba(245, 158, 11, 0.25); border: 1px solid #f59e0b; color: #fbbf24;">
                            <i class="fa-solid fa-magnifying-glass-chart"></i>
                        </span>
                        <span class="btn-text" style="color: #fbbf24; font-weight:800;">تتبع من فعل ماذا 🔍</span>
                    </button>

                    <!-- Customize Button (Top Row Master Button) -->
                    <button onclick="openQACustomizer(event)" class="qa-master-btn" style="--btn-border-color: #38bdf8; --btn-glow-color: rgba(56, 189, 248, 0.6); border-bottom: 3.5px solid #38bdf8 !important; min-width: 145px;" title="تخصيص الأزرار السبعة">
                        <span class="qa-icon" style="background: rgba(56, 189, 248, 0.25); border: 1px solid #38bdf8; color: #38bdf8;">
                            <i class="fa-solid fa-sliders"></i>
                        </span>
                        <span class="btn-text" style="color: #38bdf8;">تخصيص (7)</span>
                    </button>

                    <!-- Screens Button (Top Row Master Button) -->
                    <button id="screens-dropdown-btn" onclick="toggleScreensDropdown(event)" class="qa-master-btn" style="--btn-border-color: #a855f7; --btn-glow-color: rgba(168, 85, 247, 0.6); background: linear-gradient(135deg, rgba(168, 85, 247, 0.25), rgba(99, 102, 241, 0.25)) !important; border-bottom: 3.5px solid #a855f7 !important; min-width: 145px;">
                        <span class="qa-icon" style="background: rgba(168, 85, 247, 0.35); border: 1px solid #c084fc; color: #fff;">
                            <i class="fa-solid fa-desktop"></i>
                        </span>
                        <span class="btn-text" style="color: #fff;">الشاشات</span>
                        <i class="fa-solid fa-chevron-down" id="screens-chevron" style="font-size: 0.75rem; transition: transform 0.3s ease; color: #c084fc;"></i>
                    </button>
                </div>
            </div>

            <!-- Bottom Grid Row: 7 Custom Quick Action Master Buttons -->
            <div id="qa-single-row-grid" style="display: flex; gap: 12px; width: 100%; overflow-x: auto; padding-bottom: 8px; scrollbar-width: none; -ms-overflow-style: none;">
                <!-- Dynamic 7 Master Items rendered via JS -->
            </div>
        </div>

        <!-- Categorized Navigation Hub Bar ("تجميع حسب الاختصاصات") -->
        <div class="glass-panel" id="category-hubs-container" style="padding: 14px 18px; border-radius: 18px; border: 1.5px solid rgba(56, 189, 248, 0.25); background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.8)); margin-bottom: 14px; position: relative;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <div style="font-weight: 900; font-size: 0.9rem; color: #38bdf8; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-layer-group text-sky-400" style="font-size: 1.1rem;"></i> الشاشات والتقارير مجمعة حسب الاختصاص والقطاع 📂
                    <span style="font-size: 0.72rem; font-weight: 700; color: #94a3b8; margin-right: 6px;">(اختر أي تخصص لتنسدل لك قائمه المخصصة فوراً)</span>
                </div>
            </div>
            
            <!-- 7 Domain Specialty Buttons Grid -->
            <div id="category-hubs-grid" style="display: flex; gap: 10px; width: 100%; overflow-x: auto; padding-bottom: 8px; scrollbar-width: none; -ms-overflow-style: none;">
                <!-- Rendered dynamically by renderCategoryHubs() -->
            </div>
        </div>

        <!-- Floating Popover Container for Domain Dropdowns -->
        <div id="category-dropdown-popover" style="display: none; position: fixed; width: 380px; max-width: 94vw; background: #070a12 !important; border: 2px solid #38bdf8; border-radius: 18px; box-shadow: 0 25px 95px rgba(0,0,0,0.99), 0 0 45px rgba(56, 189, 248, 0.55); z-index: 999999; padding: 14px; direction: rtl;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; margin-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.12);">
                <div id="cat-popover-title" style="font-size: 0.95rem; font-weight: 900; color: #38bdf8; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-briefcase"></i> قائمة الاختصاص
                </div>
                <button onclick="closeCategoryDropdown()" style="background: rgba(248, 113, 113, 0.25); border: 1.5px solid #f87171; color: #f87171; border-radius: 10px; padding: 4px 12px; font-size: 0.82rem; font-weight: 900; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: all 0.2s;" onmouseover="this.style.background='rgba(248,113,113,0.4)';" onmouseout="this.style.background='rgba(248,113,113,0.25)';">
                    <span>إغلاق</span> &times;
                </button>
            </div>
            <div id="cat-popover-list" style="max-height: 420px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; padding-left: 4px;">
                <!-- Rendered via JS -->
            </div>
        </div>

        <!-- 100% OPAQUE Floating Popover Menu for Uncustomized Screens (Zero Transparency, Covers Top Bar) -->
        <div id="screens-dropdown-menu" style="display: none; position: fixed; width: 500px; max-width: 96vw; background: #070a12 !important; border: 2px solid #a855f7; border-radius: 18px; box-shadow: 0 25px 95px rgba(0,0,0,0.99), 0 0 45px rgba(168, 85, 247, 0.55); z-index: 999999; padding: 14px; direction: rtl;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; margin-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.12);">
                <div style="font-size: 0.98rem; font-weight: 900; color: #c084fc; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-desktop" style="font-size: 1.1rem; color: #a855f7;"></i> بقية الشاشات (غير المخصصة)
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span id="screens-count-badge" style="background: rgba(168, 85, 247, 0.35); color: #e9d5ff; border: 1px solid #c084fc; font-size: 0.75rem; padding: 2px 10px; border-radius: 10px; font-weight: 900;">0</span>
                    <button onclick="toggleScreensDropdown(event)" style="background: rgba(248, 113, 113, 0.25); border: 1.5px solid #f87171; color: #f87171; border-radius: 10px; padding: 5px 14px; font-size: 0.88rem; font-weight: 900; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 4px 12px rgba(248,113,113,0.3); display: flex; align-items: center; gap: 6px;" title="إغلاق القائمة" onmouseover="this.style.background='rgba(248,113,113,0.4)'; this.style.transform='scale(1.05)';" onmouseout="this.style.background='rgba(248,113,113,0.25)'; this.style.transform='scale(1)';">
                        <span>إغلاق</span>
                        <i class="fa-solid fa-xmark" style="font-size: 1rem;"></i>
                    </button>
                </div>
            </div>
            <div style="margin-bottom: 8px;">
                <input type="text" id="screens-search-input" oninput="filterScreensDropdown()" placeholder="🔍 ابحث عن أي شاشة..." style="width: 100%; box-sizing: border-box; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.22); border-radius: 8px; padding: 6px 12px; color: #fff; font-size: 0.82rem; font-family: 'Tajawal', sans-serif; outline: none;">
            </div>
            
            <!-- Compact Sorting Controls Bar -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; gap: 6px; background: rgba(255,255,255,0.03); padding: 4px 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.08);">
                <div style="font-size: 0.75rem; font-weight: 800; color: #c084fc; display: flex; align-items: center; gap: 5px;">
                    <i class="fa-solid fa-arrow-down-short-wide"></i> ترتيب:
                </div>
                <div style="display: flex; gap: 4px;">
                    <button onclick="sortScreensDropdown('default')" id="sort-btn-default" style="background: rgba(168, 85, 247, 0.35); border: 1px solid #c084fc; color: #ffffff; font-size: 0.72rem; font-weight: 800; padding: 3px 10px; border-radius: 6px; cursor: pointer; transition: all 0.2s ease;">الافتراضي</button>
                    <button onclick="sortScreensDropdown('alphabetical')" id="sort-btn-alphabetical" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); color: #94a3b8; font-size: 0.72rem; font-weight: 800; padding: 3px 10px; border-radius: 6px; cursor: pointer; transition: all 0.2s ease;">أبجديًا (أ-ي)</button>
                    <button onclick="sortScreensDropdown('category')" id="sort-btn-category" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); color: #94a3b8; font-size: 0.72rem; font-weight: 800; padding: 3px 10px; border-radius: 6px; cursor: pointer; transition: all 0.2s ease;">حسب الفئة</button>
                </div>
            </div>

            <div id="screens-dropdown-list" style="max-height: calc(100vh - 140px); overflow-y: auto; display: flex; flex-direction: column; gap: 8px; padding-left: 4px;">
                <!-- Rendered dynamically via JS -->
            </div>
        </div>

        <!-- Global Date Scope Filter Bar -->
        <div class="glass-panel" style="padding: 10px 16px; margin-bottom: 15px; border-radius: 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(56, 189, 248, 0.25);">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-calendar-days text-sky-400" style="font-size: 1.1rem;"></i>
                <span style="font-size: 0.9rem; font-weight: 800; color: #fff;">نطاق التقرير والتحليلات:</span>
                <span id="current-scope-label" style="font-size: 0.8rem; color: #38bdf8; font-weight: 700; background: rgba(56, 189, 248, 0.12); padding: 3px 10px; border-radius: 20px; border: 1px solid rgba(56, 189, 248, 0.3);">الكل</span>
            </div>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;" id="dashboard-scope-btns">
                <button type="button" onclick="setDashboardScope('all')" class="scope-btn active" id="scope-btn-all">🌐 الكل</button>
                <button type="button" onclick="setDashboardScope('today')" class="scope-btn" id="scope-btn-today">📅 اليوم</button>
                <button type="button" onclick="setDashboardScope('week')" class="scope-btn" id="scope-btn-week">📊 آخر 7 أيام</button>
                <button type="button" onclick="setDashboardScope('month')" class="scope-btn" id="scope-btn-month">🗓️ الشهر الحالي</button>
            </div>
        </div>

        <!-- 6 Compact Executive KPI Stat Cards Grid -->
        <div class="kpi-cards" style="margin-bottom: 15px; display: grid; grid-template-columns: repeat(auto-fit, minmax(165px, 1fr)); gap: 8px;">
            <!-- 1. Sales KPI (Title changes dynamically with scope) -->
            <div class="glass-panel spotlight-card neon-border-primary" style="padding: 5px 10px !important; border-radius: 10px !important;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span id="kpi-sales-title" class="kpi-title" style="font-size: 0.74rem; font-weight: 800; color: #ffffff !important;">إجمالي المبيعات</span>
                    <div class="kpi-icon-bg" style="background: rgba(59, 130, 246, 0.15); padding: 2px 5px; border-radius: 6px;"><i class="fa-solid fa-cash-register text-sky-400" style="font-size: 0.75rem;"></i></div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 2px;">
                    <h3 id="kpi-sales" class="kpi-val" style="font-size: 0.80rem !important; font-weight: 800; margin: 0; color: #38bdf8;">0 SDG</h3>
                    <div class="kpi-desc" style="color:#34d399; font-size: 0.6rem; font-weight: 700;">↑ حسب النطاق</div>
                </div>
            </div>
            
            <!-- 2. Vault Cash Liquidity -->
            <div class="glass-panel spotlight-card neon-border-success" style="padding: 5px 10px !important; border-radius: 10px !important;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="kpi-title" style="font-size: 0.74rem; font-weight: 800; color: #ffffff !important;">سيولة الصندوق</span>
                    <div class="kpi-icon-bg" style="background: rgba(16, 185, 129, 0.15); padding: 2px 5px; border-radius: 6px;"><span style="font-size: 0.75rem;">💵</span></div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 2px;">
                    <h3 id="kpi-vault-liquidity" class="kpi-val" style="font-size: 0.80rem !important; font-weight: 800; margin: 0; color: #34d399;">0 SDG</h3>
                    <div class="kpi-desc" style="color:#34d399; font-size: 0.6rem; font-weight: 700;">صندوق المحل</div>
                </div>
            </div>

            <!-- 3. Total Liquidity (Cash + Bank) -->
            <div class="glass-panel spotlight-card neon-border-success" style="padding: 5px 10px !important; border-radius: 10px !important;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="kpi-title" style="font-size: 0.74rem; font-weight: 800; color: #ffffff !important;">إجمالي السيولة</span>
                    <div class="kpi-icon-bg" style="background: rgba(16, 185, 129, 0.15); padding: 2px 5px; border-radius: 6px;"><span style="font-size: 0.75rem;">💰</span></div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 2px;">
                    <h3 id="kpi-liquidity" class="kpi-val" style="font-size: 0.80rem !important; font-weight: 800; margin: 0; color: #34d399;">0 SDG</h3>
                    <div class="kpi-desc" style="color:#34d399; font-size: 0.6rem; font-weight: 700;">صناديق وبنوك</div>
                </div>
            </div>

            <!-- 4. Current Month Profit -->
            <div class="glass-panel spotlight-card neon-border-purple" style="padding: 5px 10px !important; border-radius: 10px !important;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="kpi-title" style="font-size: 0.74rem; font-weight: 800; color: #ffffff !important;">أرباح النطاق الجاري</span>
                    <div class="kpi-icon-bg" style="background: rgba(168, 85, 247, 0.15); padding: 2px 5px; border-radius: 6px;"><i class="fa-solid fa-arrow-trend-up text-purple-400" style="font-size: 0.75rem;"></i></div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 2px;">
                    <h3 id="kpi-monthly-profit" class="kpi-val" style="font-size: 0.80rem !important; font-weight: 800; margin: 0; color: #c084fc;">0 SDG</h3>
                    <div class="kpi-desc" style="color:#c084fc; font-size: 0.6rem; font-weight: 700;">صافي الأرباح</div>
                </div>
            </div>
            
            <!-- 5. Total Inventory -->
            <div class="glass-panel spotlight-card neon-border-warning" style="padding: 5px 10px !important; border-radius: 10px !important;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="kpi-title" style="font-size: 0.74rem; font-weight: 800; color: #ffffff !important;">إجمالي المخزون</span>
                    <div class="kpi-icon-bg" style="background: rgba(245, 158, 11, 0.15); padding: 2px 5px; border-radius: 6px;"><i class="fa-solid fa-warehouse text-amber-400" style="font-size: 0.75rem;"></i></div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 2px;">
                    <h3 id="kpi-inventory" class="kpi-val" style="font-size: 0.80rem !important; font-weight: 800; margin: 0; color: #fbbf24;">0 صنف</h3>
                    <div id="kpi-warehouses-count" class="kpi-desc" style="color:#fbbf24; font-size: 0.6rem; font-weight: 700;">في 3 مخازن</div>
                </div>
            </div>
            
            <!-- 6. Inventory Alerts -->
            <div class="glass-panel spotlight-card neon-border-danger" style="padding: 5px 10px !important; border-radius: 10px !important;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="kpi-title" style="font-size: 0.74rem; font-weight: 800; color: #ffffff !important;">تنبيهات المخزون</span>
                    <div class="kpi-icon-bg" style="background: rgba(248, 113, 113, 0.15); padding: 2px 5px; border-radius: 6px;"><i class="fa-solid fa-triangle-exclamation text-rose-400" style="font-size: 0.75rem;"></i></div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 2px;">
                    <h3 id="kpi-alerts" class="kpi-val" style="font-size: 0.80rem !important; font-weight: 800; margin: 0; color: #f87171;">0 أصناف</h3>
                    <div class="kpi-desc" style="color:#f87171; font-size: 0.6rem; font-weight: 700;">إعادة طلب</div>
                </div>
            </div>
        </div>

        <!-- Top Customers & Suppliers Credit Limit & Debt Analysis Full-Width -->
        <div class="glass-panel neon-border-primary" style="padding: 20px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #fff;">
                    <i class="fa-solid fa-users-viewfinder text-sky-400" style="margin-left: 8px;"></i> المديونية وسقف الائتمان (شامل العملاء والموردين)
                </h3>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="qa-badge" style="background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;">
                        📊 تحليل مزدوج: السقف vs المديونية
                    </span>
                    <a href="credit_limits_dashboard.php" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.4); color: #fbbf24; text-decoration: none; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; transition: all 0.2s;">
                        🔍 التقرير التفصيلي 🔗
                    </a>
                </div>
            </div>
            <div class="chart-container" style="height: 380px; width: 100%;">
                <canvas id="customersChart"></canvas>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="charts-grid">
            <!-- Cashflow -->
            <div class="glass-panel neon-border-success">
                <h3><i class="fa-solid fa-money-bill-trend-up text-emerald-400" style="margin-left: 8px;"></i> التدفق النقدي وميزان السيولة</h3>
                <div class="chart-container"><canvas id="cashflowChart"></canvas></div>
                <div style="display: flex; justify-content: space-around; gap: 8px; margin-top: 10px; padding-top: 8px; border-top: 1px solid rgba(255,255,255,0.08); font-size: 0.75rem; text-align: center;">
                    <div><span style="color:#94a3b8;">المقبوضات: </span><strong id="cf-summary-in" style="color:#10b981;">0 SDG</strong></div>
                    <div><span style="color:#94a3b8;">المصروفات: </span><strong id="cf-summary-out" style="color:#f87171;">0 SDG</strong></div>
                    <div><span style="color:#94a3b8;">صافي التدفق: </span><strong id="cf-summary-net" style="color:#38bdf8;">0 SDG</strong></div>
                </div>
            </div>
            <!-- Sales Distribution -->
            <div class="glass-panel neon-border-primary">
                <h3><i class="fa-solid fa-chart-pie text-sky-400" style="margin-left: 8px;"></i> توزيع المبيعات حسب الفئة</h3>
                <div class="chart-container"><canvas id="categoryChart"></canvas></div>
            </div>
            <!-- Inventory Radar -->
            <div class="glass-panel neon-border-warning">
                <h3><i class="fa-solid fa-bullseye text-amber-400" style="margin-left: 8px;"></i> حالة المخزون (الرادار)</h3>
                <div class="chart-container"><canvas id="inventoryChart"></canvas></div>
            </div>
            <!-- Warehouses Capacity -->
            <div class="glass-panel neon-border-danger">
                <h3><i class="fa-solid fa-warehouse text-rose-400" style="margin-left: 8px;"></i> السعة التخزينية للمخازن</h3>
                <div class="chart-container"><canvas id="warehousesChart"></canvas></div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="bottom-section">
            <!-- Recent Transactions -->
            <div class="glass-panel neon-border-primary">
                <h3><i class="fa-solid fa-clock-rotate-left text-sky-400" style="margin-left: 8px;"></i> أحدث العمليات</h3>
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
                <h3><i class="fa-solid fa-hourglass-half text-amber-400" style="margin-left: 8px;"></i> أذونات لم تستلم (قيد الانتظار)</h3>
                <div id="pending-deliveries-list" style="display:flex; flex-direction:column; gap:12px;">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div class="glass-panel neon-border-danger">
                <h3><i class="fa-solid fa-triangle-exclamation text-rose-400" style="margin-left: 8px;"></i> تنبيهات المخزون (نواقص)</h3>
                <div id="low-stock-list" style="display:flex; flex-direction:column; gap:12px;"></div>
            </div>
        </div>

        <!-- Sajana Protocol (Social & Security) -->
        <div class="glass-panel" style="text-align:center; border: 1px solid rgba(56,189,248,0.2); background: linear-gradient(to right, rgba(15,23,42,0.6), rgba(30,41,59,0.4)); padding: 20px;">
            <h3 style="margin:0 0 15px; font-size:1.1rem; font-weight:900; justify-content: center;">
                <i class="fa-solid fa-shield-halved text-sky-400" style="margin-left: 8px;"></i> بروتوكول مقاييس الدولي - نظام الحماية
            </h3>
            
            <div style="display:flex; justify-content:center; gap:15px; flex-wrap:wrap;">
                <a href="https://facebook.com" target="_blank" title="صفحة الفيسبوك الرسمية" style="display:flex; align-items:center; justify-content:center; width:45px; height:45px; border-radius:50%; background:linear-gradient(135deg, #1877F2, #0d5bbd); color:#fff; text-decoration:none; font-size:1.4rem; transition:transform 0.3s; box-shadow:0 5px 15px rgba(24,119,242,0.3);" onmouseover="this.style.transform='scale(1.15) translateY(-3px)'" onmouseout="this.style.transform='scale(1) translateY(0)'">f</a>
                
                <a href="https://wa.me/249000000000" target="_blank" title="الدعم الفني عبر الواتساب" style="display:flex; align-items:center; justify-content:center; width:45px; height:45px; border-radius:50%; background:linear-gradient(135deg, #25D366, #1da851); color:#fff; text-decoration:none; font-size:1.4rem; transition:transform 0.3s; box-shadow:0 5px 15px rgba(37,211,102,0.3);" onmouseover="this.style.transform='scale(1.15) translateY(-3px)'" onmouseout="this.style.transform='scale(1) translateY(0)'">💬</a>
                
                <a href="https://tiktok.com" target="_blank" title="حساب تيك توك" style="display:flex; align-items:center; justify-content:center; width:45px; height:45px; border-radius:50%; background:linear-gradient(135deg, #333, #000); color:#fff; text-decoration:none; font-size:1.4rem; transition:transform 0.3s; box-shadow:0 5px 15px rgba(0,0,0,0.3);" onmouseover="this.style.transform='scale(1.15) translateY(-3px)'" onmouseout="this.style.transform='scale(1) translateY(0)'">🎵</a>
            </div>
        </div>
    </div>

    <!-- Dual-Tab Notifications & Alerts Control Modal Overlay -->
    <div id="notifications-dropdown" style="display:none; position:fixed; top:0; left:0; width: 100%; height:100vh; background:rgba(5,3,8,0.85); backdrop-filter:blur(12px); z-index:9999; justify-content:center; align-items:center; opacity:0; transition: opacity 0.3s ease;" onclick="toggleNotifications()">
        <div class="glass-panel" style="width:95%; max-width:640px; max-height:85vh; overflow-y:auto; padding:22px; border:2px solid rgba(244,63,94,0.3); border-radius:24px; position:relative; box-shadow: 0 25px 60px rgba(0,0,0,0.7); background: rgba(14, 8, 28, 0.95);" onclick="event.stopPropagation()">
            
            <!-- Modal Header & Tabs -->
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:12px; margin-bottom:15px; flex-wrap:wrap; gap:8px;">
                <div style="display:flex; gap:8px;">
                    <button type="button" id="notif-tab-live-btn" onclick="switchModalNotifTab('LIVE')" style="padding:8px 14px; border-radius:12px; font-size:0.85rem; font-weight:800; border:1px solid #f43f5e; background:rgba(244,63,94,0.25); color:#fff; cursor:pointer; display:flex; align-items:center; gap:6px;">
                        <i class="fa-solid fa-bell text-rose-400"></i> الإنذارات النشطة
                    </button>
                    <button type="button" id="notif-tab-ctrl-btn" onclick="switchModalNotifTab('CTRL')" style="padding:8px 14px; border-radius:12px; font-size:0.85rem; font-weight:800; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.05); color:#94a3b8; cursor:pointer; display:flex; align-items:center; gap:6px;">
                        <i class="fa-solid fa-sliders text-amber-400"></i> 🎛️ التحكم في التنبيهات (On/Off)
                    </button>
                </div>
                <button type="button" onclick="toggleNotifications()" style="background:none; border:none; color:#94a3b8; font-size:1.3rem; cursor:pointer; padding:4px 8px; font-weight:900;">✕</button>
            </div>

            <!-- Tab 1: Live Alerts List -->
            <div id="modal-tab-live">
                <div id="notif-list" style="display:flex; flex-direction:column; gap:10px;">
                    <div style="color:var(--text-secondary); text-align:center; font-size:0.85rem; padding:15px;">جاري تحميل التنبيهات...</div>
                </div>
            </div>

            <!-- Tab 2: Quick Rules Control Panel -->
            <div id="modal-tab-ctrl" style="display:none;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; gap:8px; flex-wrap:wrap;">
                    <span style="font-size:0.8rem; color:#cbd5e1; font-weight:700;">🎛️ يمكنك تفعيل أو إيقاف أي تنبيه بنقرة واحدة:</span>
                    <a href="alerts_manager.php" style="font-size:0.75rem; color:#38bdf8; text-decoration:none; font-weight:800; background:rgba(56,189,248,0.1); padding:4px 10px; border-radius:8px; border:1px solid rgba(56,189,248,0.25);">مركز التخصيص المتقدم ↗</a>
                </div>
                <div style="margin-bottom:10px;">
                    <input type="text" id="modal-rules-search" oninput="filterModalRules(this.value)" placeholder="🔍 ابحث عن أي تنبيه أو شرط..." style="width:100%; box-sizing:border-box; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); border-radius:10px; padding:7px 12px; color:#fff; font-size:0.8rem; outline:none; font-family:'Tajawal', sans-serif;">
                </div>
                <div id="modal-rules-list" style="display:flex; flex-direction:column; gap:8px; max-height:480px; overflow-y:auto; padding-right:4px;">
                    <div style="text-align:center; color:#94a3b8; font-size:0.85rem; padding:15px;">جاري تحميل قواعد التنبيهات...</div>
                </div>
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
    <script>
        // Spotlight Effect Mouse Tracker (throttled to 60fps)
        let _spotlightTick = false;
        document.addEventListener('mousemove', e => {
            if (_spotlightTick) return;
            _spotlightTick = true;
            requestAnimationFrame(() => {
                for(const card of document.querySelectorAll('.spotlight-card')) {
                    const rect = card.getBoundingClientRect();
                    card.style.setProperty('--mouse-x', `${e.clientX - rect.left}px`);
                    card.style.setProperty('--mouse-y', `${e.clientY - rect.top}px`);
                }
                _spotlightTick = false;
            });
        }, { passive: true });

        // Dynamic 60fps EaseOutExpo Animated Counter Engine
        function animateValue(elementOrId, start, end, duration = 1200, suffix = '') {
            const obj = typeof elementOrId === 'string' ? document.getElementById(elementOrId) : elementOrId;
            if (!obj) return;
            start = parseFloat(start) || 0;
            end = parseFloat(end) || 0;
            if (start === end) {
                obj.innerText = end.toLocaleString('en-US') + (suffix ? ' ' + suffix : '');
                return;
            }
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                const currentVal = Math.floor(easeProgress * (end - start) + start);
                obj.innerText = currentVal.toLocaleString('en-US') + (suffix ? ' ' + suffix : '');
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                } else {
                    obj.innerText = end.toLocaleString('en-US') + (suffix ? ' ' + suffix : '');
                }
            };
            window.requestAnimationFrame(step);
        }

        // Check Authentication & Initialize Dashboard App
        function initDashboardApp() {
            let token = localStorage.getItem('authToken');
            if (!token) {
                // في البيئة المحلية، تأسيس الجلسة تلقائياً لمنع أي حلقة تحويل
                if (window.location.hostname.includes('localhost') || window.location.hostname.includes('127.0.0.1')) {
                    token = 'offline_token_' + Date.now();
                    localStorage.setItem('authToken', token);
                    localStorage.setItem('user', JSON.stringify({
                        id: 1,
                        username: 'admin',
                        fullName: 'مدير النظام (الباشمهندس مهدي)',
                        role: 'admin',
                        roleId: 'admin_fallback',
                        securityLevel: 10,
                        subdomain: ''
                    }));
                    document.cookie = "session_active=true; path=/";
                    sessionStorage.setItem('session_active', 'true');
                    sessionStorage.setItem('dashboard_unlocked', '1');
                } else {
                    window.location.href = 'login.php';
                    return;
                }
            }

            // تأكيد فتح القفل الموحد
            if (!sessionStorage.getItem('dashboard_unlocked')) {
                sessionStorage.setItem('dashboard_unlocked', '1');
            }
            
            setupUnifiedAutoLogout();

            // Initialize Charts
            initProCharts();

            // Render Single Row Quick Actions (7 items)
            renderSingleRowQA();

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

            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                if (cfg.showAthkar !== false) { // Default is true
                    const athkarEl = document.getElementById('dashboard-athkar-container');
                    if (athkarEl) athkarEl.style.display = 'flex';
                }
            } catch (e) {}
        }
        
        // --- Security Lock Modal & Auto Logout ---
        function showUnifiedLock() {
            let modal = document.getElementById('unified-lock-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'unified-lock-modal';
                modal.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.95); backdrop-filter:blur(10px); z-index:100000; display:flex; align-items:center; justify-content:center; direction:rtl;';
                
                let username = 'مستخدم النظام';
                try {
                    const user = JSON.parse(localStorage.getItem('user'));
                    username = user.fullName || user.name || user.username || username;
                } catch(e) {}
                
                modal.innerHTML = `
                    <div style="width: 90%; max-width: 400px; text-align: center; padding: 40px 35px; border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,0.5); background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #f59e0b, #ea580c); border-radius: 20px; margin: 0 auto 20px auto; display: flex; justify-content: center; align-items: center; font-size: 2.5rem; box-shadow: 0 10px 25px rgba(245, 158, 11, 0.4);">🛡️</div>
                        <h3 style="color: #fff; margin-bottom: 5px; font-weight: 700; font-family:'Tajawal',sans-serif;">${username}</h3>
                        <p style="color: #a1a1aa; font-size: 0.9rem; margin-bottom: 25px; font-family:'Tajawal',sans-serif;">أدخل كلمة المرور للمتابعة</p>
                        <input type="password" id="unified-lock-pass" placeholder="........" style="width: 100%; padding: 14px; border-radius: 10px; background: rgba(255,255,255,0.07); color: #fff; border: 1px solid rgba(255,255,255,0.1); font-size: 1.1rem; box-sizing: border-box; margin-bottom: 20px; font-family: 'Tajawal',sans-serif; text-align: center; outline: none;">
                        <button onclick="unlockUnifiedDashboard()" style="width: 100%; font-family:'Tajawal',sans-serif; font-size: 1.1rem; padding: 12px; border-radius: 10px; background: linear-gradient(135deg, #f59e0b, #ea580c); border: none; color: white; cursor: pointer; transition: 0.2s;">تسجيل الدخول</button>
                        <div id="unified-lock-error" style="color: #ef4444; margin-top: 15px; font-size: 0.9rem; display: none; font-family:'Tajawal',sans-serif;">كلمة المرور خاطئة</div>
                    </div>
                `;
                document.body.appendChild(modal);
                
                document.getElementById('unified-lock-pass').addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') unlockUnifiedDashboard();
                });
            }
            modal.style.display = 'flex';
            setTimeout(() => document.getElementById('unified-lock-pass').focus(), 100);
        }

        async function unlockUnifiedDashboard() {
            const passEl = document.getElementById('unified-lock-pass');
            const pass = passEl.value.trim();
            const errEl = document.getElementById('unified-lock-error');
            if (!pass) { errEl.innerText = 'الرجاء إدخال كلمة المرور'; errEl.style.display = 'block'; return; }
            
            const btn = document.querySelector('#unified-lock-modal button');
            const oldText = btn.innerText;
            btn.innerText = 'جاري التحقق...';
            btn.disabled = true;
            errEl.style.display = 'none';

            try {
                const userStr = localStorage.getItem('user');
                let username = '';
                if (userStr) username = JSON.parse(userStr).username;
                
                const res = await fetch('api_index.php/auth/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username: username, password: pass })
                });
                const data = await res.json().catch(() => ({}));
                
                if (res.ok && data.token) {
                    sessionStorage.setItem('dashboard_unlocked', '1');
                    document.getElementById('unified-lock-modal').style.display = 'none';
                    initDashboardApp();
                } else {
                    errEl.innerText = data.error || data.message || 'كلمة المرور خاطئة';
                    errEl.style.display = 'block';
                }
            } catch (e) { 
                errEl.innerText = 'حدث خطأ في الاتصال';
                errEl.style.display = 'block';
            } finally {
                btn.innerText = oldText;
                btn.disabled = false;
            }
        }

        let unifiedIdleTime = 0;
        let unifiedIdleInterval = null;
        function setupUnifiedAutoLogout() {
            let autoLogoutValue = 0;
            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                autoLogoutValue = parseInt(cfg.autoLogoutTime) || 0;
            } catch(e) {}
            
            if (autoLogoutValue > 0) {
                if (unifiedIdleInterval) clearInterval(unifiedIdleInterval);
                unifiedIdleInterval = setInterval(() => {
                    unifiedIdleTime++;
                    if (unifiedIdleTime >= autoLogoutValue) {
                        sessionStorage.removeItem('dashboard_unlocked');
                        window.location.reload(); 
                    }
                }, 60000);
                
                const resetIdle = () => unifiedIdleTime = 0;
                ['mousemove', 'keypress', 'touchstart', 'scroll'].forEach(evt => {
                    document.addEventListener(evt, resetIdle);
                });
            }
        }

        if (window.APP_READY) {
            initDashboardApp();
        } else {
            document.addEventListener('DBReady', initDashboardApp);
        }

        // Re-render when background cloud sync completes without forcing page reload loop
        window.addEventListener('cloudDataRestored', () => {
            console.log("[Dashboard] Cloud sync completed. Updating UI...");
            initDashboardApp();
        });
        window.addEventListener('strictDataReady', () => {
            console.log("[Dashboard] Strict data ready. Updating UI...");
            initDashboardApp();
        });

        // Toggle Notifications Dropdown (Modal)
        function toggleNotifications(tab = 'LIVE') {
            const dropdown = document.getElementById('notifications-dropdown');
            if (dropdown.style.display === 'none' || dropdown.style.display === '') {
                dropdown.style.display = 'flex';
                setTimeout(() => dropdown.style.opacity = '1', 10);
                switchModalNotifTab(tab);
            } else {
                dropdown.style.opacity = '0';
                setTimeout(() => dropdown.style.display = 'none', 300);
            }
        }

        function switchModalNotifTab(tab) {
            const liveTab = document.getElementById('modal-tab-live');
            const ctrlTab = document.getElementById('modal-tab-ctrl');
            const liveBtn = document.getElementById('notif-tab-live-btn');
            const ctrlBtn = document.getElementById('notif-tab-ctrl-btn');

            if (tab === 'CTRL') {
                if (liveTab) liveTab.style.display = 'none';
                if (ctrlTab) ctrlTab.style.display = 'block';
                if (liveBtn) {
                    liveBtn.style.background = 'rgba(255,255,255,0.05)';
                    liveBtn.style.borderColor = 'rgba(255,255,255,0.1)';
                    liveBtn.style.color = '#94a3b8';
                }
                if (ctrlBtn) {
                    ctrlBtn.style.background = 'rgba(245,158,11,0.25)';
                    ctrlBtn.style.borderColor = '#f59e0b';
                    ctrlBtn.style.color = '#ffffff';
                }
                loadModalAlertRules();
            } else {
                if (liveTab) liveTab.style.display = 'block';
                if (ctrlTab) ctrlTab.style.display = 'none';
                if (liveBtn) {
                    liveBtn.style.background = 'rgba(244,63,94,0.25)';
                    liveBtn.style.borderColor = '#f43f5e';
                    liveBtn.style.color = '#ffffff';
                }
                if (ctrlBtn) {
                    ctrlBtn.style.background = 'rgba(255,255,255,0.05)';
                    ctrlBtn.style.borderColor = 'rgba(255,255,255,0.1)';
                    ctrlBtn.style.color = '#94a3b8';
                }
                checkDueDates();
            }
        }

        let _cachedModalRules = [];

        function loadModalAlertRules() {
            const container = document.getElementById('modal-rules-list');
            if (!container) return;

            fetch('alerts_manager.php?action=get_rules_json')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.rules) {
                    _cachedModalRules = data.rules;
                    renderModalRulesList(data.rules);
                }
            })
            .catch(() => {
                container.innerHTML = '<div style="color:#f87171; text-align:center; padding:15px; font-size:0.85rem;">تعذر جلب قواعد التنبيهات من الخادم.</div>';
            });
        }

        function renderModalRulesList(rules) {
            const container = document.getElementById('modal-rules-list');
            if (!container) return;

            if (!rules || rules.length === 0) {
                container.innerHTML = '<div style="color:#94a3b8; text-align:center; padding:15px;">لا توجد قواعد متاحة.</div>';
                return;
            }

            const catColors = {
                'FINANCE': '#38bdf8',
                'CREDIT': '#fbbf24',
                'SALES': '#34d399',
                'INVENTORY': '#c084fc',
                'SUPPLY': '#f472b6',
                'SECURITY': '#f87171'
            };

            let html = '';
            rules.forEach(r => {
                const color = catColors[r.category] || '#94a3b8';
                const isChecked = r.is_enabled == 1;
                const screenBadge = r.screen_name ? `<span style="font-size:0.65rem; color:#38bdf8; background:rgba(56,189,248,0.1); padding:1px 6px; border-radius:4px; border:1px solid rgba(56,189,248,0.25);">🏢 ${r.screen_name}</span>` : '';
                html += `
                    <div class="modal-rule-item" data-search="${(r.rule_name + ' ' + (r.screen_name||'') + ' ' + (r.screen_file||'') + ' ' + (r.description||'')).toLowerCase()}" style="padding:10px 14px; background:rgba(255,255,255,0.03); border-radius:12px; border:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:180px;">
                            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                <strong style="font-size:0.85rem; color:#fff;">${r.rule_name}</strong>
                                <span style="font-size:0.65rem; color:${color}; background:${color}15; padding:1px 6px; border-radius:4px; border:1px solid ${color}30;">${r.category}</span>
                                ${screenBadge}
                            </div>
                            <div style="font-size:0.72rem; color:#94a3b8; margin-top:3px; line-height:1.4;">${r.description || ''}</div>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div style="display:flex; align-items:center; gap:3px; font-size:0.75rem; background:rgba(0,0,0,0.3); padding:3px 6px; border-radius:8px;">
                                <span style="color:#fbbf24;">⚠️</span>
                                <input type="number" id="mwarn-${r.rule_key}" value="${r.warning_threshold}" style="width:40px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#fbbf24; text-align:center; font-weight:800; font-size:0.75rem; border-radius:4px; padding:1px;" onchange="saveModalThresholds('${r.rule_key}')">
                                <span style="color:#f87171; margin-right:3px;">🚨</span>
                                <input type="number" id="mcrit-${r.rule_key}" value="${r.critical_threshold}" style="width:40px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#f87171; text-align:center; font-weight:800; font-size:0.75rem; border-radius:4px; padding:1px;" onchange="saveModalThresholds('${r.rule_key}')">
                            </div>
                            <label style="position:relative; display:inline-block; width:44px; height:24px; margin:0; cursor:pointer;">
                                <input type="checkbox" ${isChecked ? 'checked' : ''} onchange="toggleModalRule('${r.rule_key}', this.checked)" style="opacity:0; width:0; height:0;">
                                <span style="position:absolute; top:0; left:0; right:0; bottom:0; background-color:${isChecked ? '#10b981' : '#334155'}; transition:.3s; border-radius:24px; border:1px solid rgba(255,255,255,0.2);">
                                    <span style="position:absolute; content:''; height:16px; width:16px; left:${isChecked ? '23px' : '4px'}; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>
                                </span>
                            </label>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        function filterModalRules(q) {
            const query = q.trim().toLowerCase();
            const items = document.querySelectorAll('.modal-rule-item');
            items.forEach(it => {
                const text = it.getAttribute('data-search') || '';
                it.style.display = (query === '' || text.includes(query)) ? 'flex' : 'none';
            });
        }

        function toggleModalRule(ruleKey, isChecked) {
            const formData = new FormData();
            formData.append('action', 'toggle_rule_ajax');
            formData.append('rule_key', ruleKey);
            formData.append('is_enabled', isChecked ? 1 : 0);

            fetch('alerts_manager.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    loadModalAlertRules();
                    checkDueDates();
                }
            });
        }

        function saveModalThresholds(ruleKey) {
            const warn = document.getElementById('mwarn-' + ruleKey).value;
            const crit = document.getElementById('mcrit-' + ruleKey).value;

            const formData = new FormData();
            formData.append('action', 'update_thresholds_ajax');
            formData.append('rule_key', ruleKey);
            formData.append('warning_threshold', warn);
            formData.append('critical_threshold', crit);

            fetch('alerts_manager.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    checkDueDates();
                }
            });
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

            // Fetch live database alerts from alerts_manager.php
            fetch('alerts_manager.php?action=get_live_alerts_json')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.alerts && data.alerts.length > 0) {
                    data.alerts.forEach(a => {
                        const isRed = a.severity === 'RED';
                        const bg = isRed ? 'rgba(239, 68, 68, 0.15)' : 'rgba(245, 158, 11, 0.15)';
                        const border = isRed ? 'rgba(239, 68, 68, 0.4)' : 'rgba(245, 158, 11, 0.4)';
                        const color = isRed ? '#f87171' : '#fbbf24';
                        const title = isRed ? '🚨 إنذار رقابي حرج' : '⚠️ تنبيه للمتابعة';

                        notifications.unshift(`
                            <div style="padding:12px; background:${bg}; border-radius:12px; border:1px solid ${border};">
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                    <strong style="color:${color}; font-size:0.92rem;">${title}</strong>
                                    <span style="font-size:0.7rem; color:${color}; background:rgba(0,0,0,0.3); padding:2px 6px; border-radius:6px;">نظامي</span>
                                </div>
                                <div style="font-size:0.82rem; color:#fff; line-height: 1.5;">
                                    ${a.message}
                                </div>
                            </div>
                        `);
                        unreadCount++;
                    });
                }

                // Add footer link to Alerts Manager
                notifications.push(`
                    <div style="margin-top: 10px; text-align: center;">
                        <a href="alerts_manager.php" style="display: block; padding: 10px; background: rgba(244, 63, 94, 0.2); border: 1px dashed #f43f5e; color: #fda4af; border-radius: 10px; text-decoration: none; font-weight: 800; font-size: 0.85rem; transition: background 0.2s;" onmouseover="this.style.background='rgba(244, 63, 94, 0.35)'" onmouseout="this.style.background='rgba(244, 63, 94, 0.2)'">
                            ⚙️ فتح مركز إدارة وتخصيص التنبيهات
                        </a>
                    </div>
                `);

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
                    list.innerHTML = notifications.join('');
                }
            })
            .catch(() => {
                const badge = document.getElementById('notif-badge');
                const list = document.getElementById('notif-list');
                if (badge) {
                    if (unreadCount > 0) { badge.innerText = unreadCount; badge.style.display = 'block'; }
                    else { badge.style.display = 'none'; }
                }
                if (list) {
                    if (unreadCount > 0) list.innerHTML = notifications.join('');
                    else list.innerHTML = '<div style="color:var(--text-secondary); text-align:center; font-size:0.85rem; padding:15px;">لا توجد تنبيهات حالياً 🎉</div>';
                }
            });
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

            const sevenDaysAgo = new Date(now);
            sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
            const sevenDaysAgoStr = sevenDaysAgo.toISOString().split('T')[0];

            // Filter invoices by scope
            let scopedInvoices = invoices;
            if (window.currentDashboardScope === 'today') {
                scopedInvoices = invoices.filter(inv => inv.date && inv.date.startsWith(todayStr));
                if (document.getElementById('kpi-sales-title')) document.getElementById('kpi-sales-title').textContent = 'مبيعات اليوم';
            } else if (window.currentDashboardScope === 'week') {
                scopedInvoices = invoices.filter(inv => inv.date && inv.date >= sevenDaysAgoStr);
                if (document.getElementById('kpi-sales-title')) document.getElementById('kpi-sales-title').textContent = 'مبيعات آخر 7 أيام';
            } else if (window.currentDashboardScope === 'month') {
                scopedInvoices = invoices.filter(inv => inv.date && inv.date.startsWith(`${year}-${month}`));
                if (document.getElementById('kpi-sales-title')) document.getElementById('kpi-sales-title').textContent = 'مبيعات الشهر';
            } else {
                if (document.getElementById('kpi-sales-title')) document.getElementById('kpi-sales-title').textContent = 'إجمالي المبيعات';
            }

            // 1. Calculate Scoped Sales using grandTotal/totalAmount
            const totalScopedSales = scopedInvoices.reduce((sum, inv) => sum + (Number(inv.grandTotal) || Number(inv.totalAmount) || 0), 0);
            animateValue('kpi-sales', 0, totalScopedSales, 1300, 'SDG');

            // 2. Calculate Vault Liquidity & Total Liquidity
            let vaultLiquidity = 0;
            let bankLiquidity = 0;
            banks.forEach(b => {
                let bal = Number(b.currentBalance) || Number(b.openingBalance) || 0;
                if (b.type === 'cash' || String(b.name || '').includes('صندوق')) {
                    vaultLiquidity += bal;
                } else {
                    bankLiquidity += bal;
                }
            });
            let totalLiquidity = vaultLiquidity + bankLiquidity;
            if (totalLiquidity === 0 && banks.length > 0) {
                totalLiquidity = banks.reduce((sum, b) => sum + (Number(b.currentBalance) || Number(b.openingBalance) || 0), 0);
                vaultLiquidity = Math.round(totalLiquidity * 0.45);
            }

            animateValue('kpi-vault-liquidity', 0, vaultLiquidity, 1400, 'SDG');
            animateValue('kpi-liquidity', 0, totalLiquidity, 1400, 'SDG');

            // 3. Current Month Profit Calculation
            const currentMonthPrefix = `${year}-${month}`;
            const monthInvoices = invoices.filter(inv => inv.date && inv.date.startsWith(currentMonthPrefix));
            let monthSales = monthInvoices.reduce((sum, inv) => sum + (Number(inv.grandTotal) || Number(inv.totalAmount) || 0), 0);
            let approxMonthlyProfit = Math.round(monthSales * 0.22); // 22% estimated profit
            animateValue('kpi-monthly-profit', 0, approxMonthlyProfit, 1500, 'SDG');

            // 4. Total Items
            const totalItems = items.length;
            animateValue('kpi-inventory', 0, totalItems, 1200, 'صنف');
            
            let warehousesList = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            if (warehousesList.length === 0) {
                warehousesList = [{ id: 'w-main', name: 'المخزن الرئيسي' }];
            }
            const activeWhsCount = warehousesList.filter(w => !w.deleted).length;
            const whsCountEl = document.getElementById('kpi-warehouses-count');
            if (whsCountEl) {
                whsCountEl.innerText = `موزعة على ${activeWhsCount} ${activeWhsCount > 2 && activeWhsCount < 11 ? 'مخازن' : 'مخزن'}`;
            }

            // 5. Low Stock computed from actual inventory levels
            const lowStockItems = items.filter(item => {
                let available = getItemActualStock(item.id);
                let safeLimit = Number(item.safeLimit) || Number(item.minQty) || 10;
                return available <= safeLimit;
            });
            animateValue('kpi-alerts', 0, lowStockItems.length, 1200, 'أصناف');

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

            // Calculate Profit
            const todayInvoices = scopedInvoices;
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
            if (document.getElementById('ticker-sales')) document.getElementById('ticker-sales').innerText = totalScopedSales.toLocaleString('en-US') + ' SDG';
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
            const pendingDeliveries = scopedInvoices.filter(inv => {
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
            scopedInvoices.forEach(i => allTx.push({ id: i.invoiceNo, type: 'مبيعات', desc: 'فاتورة مبيعات', amount: i.grandTotal, date: i.date }));
            
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
                    <td><span class="type-badge">${entry.type}</span></td>
                    <td>${entry.desc || '-'}</td>
                    <td><strong style="color:#fff;">${(Number(entry.amount)||0).toLocaleString()} SDG</strong></td>
                    <td>${badge}</td>
                </tr>
                `;
            }).join('');
            document.getElementById('recent-transactions-tbody').innerHTML = recentHTML || '<tr><td colspan="5" style="text-align:center; padding:15px; color:var(--text-secondary); font-size: 0.85rem;">لا توجد حركات مالية مسجلة بعد.</td></tr>';

            return { invoices: scopedInvoices, items, customers, banks };
        }

        // Initialize Chart.js Graphs
        function initProCharts() {
            const data = loadDynamicDashboardData();
            const inventory = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
            
            const textColor = '#94a3b8';
            const gridColor = 'rgba(255, 255, 255, 0.05)';

            Chart.defaults.color = textColor;
            Chart.defaults.font.family = "'Tajawal', sans-serif";
            Chart.defaults.font.size = 11;

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

            let totalReceiptsInScope = receiptsData.reduce((a, b) => a + b, 0);
            let totalPaymentsInScope = paymentsData.reduce((a, b) => a + b, 0);
            let netCashflow = totalReceiptsInScope - totalPaymentsInScope;

            if (document.getElementById('cf-summary-in')) document.getElementById('cf-summary-in').innerText = totalReceiptsInScope.toLocaleString('en-US') + ' SDG';
            if (document.getElementById('cf-summary-out')) document.getElementById('cf-summary-out').innerText = totalPaymentsInScope.toLocaleString('en-US') + ' SDG';
            if (document.getElementById('cf-summary-net')) {
                const netEl = document.getElementById('cf-summary-net');
                netEl.innerText = (netCashflow >= 0 ? '+' : '') + netCashflow.toLocaleString('en-US') + ' SDG';
                netEl.style.color = netCashflow >= 0 ? '#10b981' : '#f87171';
            }

            window._dashboardChartInstances = window._dashboardChartInstances || {};
            function createOrUpdateChart(id, config) {
                if (window._dashboardChartInstances[id]) {
                    try { window._dashboardChartInstances[id].destroy(); } catch(e) {}
                }
                const canvas = document.getElementById(id);
                if (canvas) {
                    window._dashboardChartInstances[id] = new Chart(canvas.getContext('2d'), config);
                }
            }

            createOrUpdateChart('cashflowChart', {
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

            createOrUpdateChart('categoryChart', {
                type: 'doughnut',
                data: {
                    labels: catLabels,
                    datasets: [{ data: catData, backgroundColor: ['#3b82f6', '#f59e0b', '#f87171', '#10b981', '#c084fc', '#ec4899'], borderWidth: 0, hoverOffset: 10 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'right', labels: { usePointStyle: true, padding: 10, font: {family: "'Tajawal'", size: 11} } }, tooltip: { callbacks: { label: function(context) { return ' ' + context.label + ': ' + context.raw.toLocaleString() + ' SDG'; } } } } }
            });

            // 3. Inventory Radar (Dynamic - Lowest Stock Items)
            function getItemActualStock(itemId) {
                return inventory
                    .filter(inv => inv.itemId === itemId && inv.warehouse !== 'PENDING_RECEIPT')
                    .reduce((sum, inv) => sum + (Number(inv.qty) || 0), 0);
            }
            
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

            createOrUpdateChart('inventoryChart', {
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

            createOrUpdateChart('warehousesChart', {
                type: 'doughnut',
                data: {
                    labels: whLabels,
                    datasets: [{ data: whData, backgroundColor: ['#f87171', '#3b82f6', '#10b981', '#f59e0b', '#c084fc'], borderWidth: 0, hoverOffset: 10 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 10, font: {family: "'Tajawal'", size: 11} } }, tooltip: { callbacks: { label: function(context) { return ' ' + context.label + ': ' + context.raw.toLocaleString() + ' وحدة'; } } } } }
            });

            // 5. Customer & Supplier Credit & Debt Analysis (Dual Bar Chart)
            let allCreditEntities = [];
            try {
                const cList = (JSON.parse(localStorage.getItem('erp_customers_v1')) || data.customers || []).filter(x => !x.deleted);
                const sList = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
                
                const cMapped = cList.map(c => ({
                    name: c.name || 'عميل',
                    typeLabel: '👤 عميل',
                    creditLimit: parseFloat(c.creditLimit) || 0,
                    debt: Math.abs(parseFloat(c.currentBalance) || 0)
                }));

                const sMapped = sList.map(s => ({
                    name: s.name || 'مورد',
                    typeLabel: '🏭 مورد',
                    creditLimit: parseFloat(s.creditLimit) || 0,
                    debt: Math.abs(parseFloat(s.currentBalance) || 0)
                }));

                allCreditEntities = [...cMapped, ...sMapped];
            } catch(e) {
                allCreditEntities = (data.customers || []).map(c => ({
                    name: c.name || 'عميل',
                    typeLabel: '👤 عميل',
                    creditLimit: parseFloat(c.creditLimit) || 0,
                    debt: Math.abs(parseFloat(c.currentBalance) || 0)
                }));
            }

            let validEntities = allCreditEntities.filter(c => c.creditLimit > 0 || c.debt > 0);

            const topEntities = validEntities.slice(0, 15);
            const creditLabels = topEntities.map(c => `${c.name} (${c.typeLabel})`);
            const creditValues = topEntities.map(c => c.creditLimit);
            const debtValues = topEntities.map(c => c.debt);

            const canvasCust = document.getElementById('customersChart');
            if (canvasCust) {
                const ctxCust = canvasCust.getContext('2d');
                
                const skyGrad = ctxCust.createLinearGradient(0, 0, 0, 320);
                skyGrad.addColorStop(0, '#38bdf8');
                skyGrad.addColorStop(1, '#0284c7');

                const roseGrad = ctxCust.createLinearGradient(0, 0, 0, 320);
                roseGrad.addColorStop(0, '#fb7185');
                roseGrad.addColorStop(1, '#e11d48');

                createOrUpdateChart('customersChart', {
                    type: 'bar',
                    data: {
                        labels: creditLabels,
                        datasets: [
                            {
                                label: '💳 سقف الائتمان (المسموح)',
                                data: creditValues,
                                backgroundColor: skyGrad,
                                borderColor: '#38bdf8',
                                borderWidth: 1,
                                borderRadius: { topLeft: 6, topRight: 6 },
                                barPercentage: 0.45,
                                categoryPercentage: 0.7
                            },
                            {
                                label: '⚠️ المديونية القائمة (المسحوب)',
                                data: debtValues,
                                backgroundColor: roseGrad,
                                borderColor: '#fb7185',
                                borderWidth: 1,
                                borderRadius: { topLeft: 6, topRight: 6 },
                                barPercentage: 0.45,
                                categoryPercentage: 0.7
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                align: 'center',
                                labels: {
                                    color: '#e2e8f0',
                                    usePointStyle: true,
                                    padding: 15,
                                    font: { family: "'Tajawal', sans-serif", size: 12, weight: '800' }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                                titleColor: '#fff',
                                bodyColor: '#38bdf8',
                                borderColor: 'rgba(56, 189, 248, 0.3)',
                                borderWidth: 1,
                                padding: 12,
                                callbacks: {
                                    title: function(items) {
                                        const entity = topEntities[items[0].dataIndex];
                                        return entity.name + ' (' + entity.typeLabel + ')';
                                    },
                                    label: function(context) {
                                        const entity = topEntities[context.dataIndex];
                                        const val = Number(context.raw).toLocaleString('en-US') + ' SDG';
                                        if (context.datasetIndex === 0) {
                                            return ' 💳 سقف الائتمان: ' + val;
                                        } else {
                                            const rem = Math.max(0, entity.creditLimit - entity.debt);
                                            const usage = entity.creditLimit > 0 ? Math.round((entity.debt / entity.creditLimit) * 100) : 0;
                                            return [
                                                ' ⚠️ المديونية القائمة: ' + val + ' (' + usage + '% من السقف)',
                                                ' 🟢 المتبقي المتاح: ' + rem.toLocaleString('en-US') + ' SDG'
                                            ];
                                        }
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: {
                                    color: '#94a3b8',
                                    maxRotation: 35,
                                    minRotation: 20,
                                    font: { family: "'Tajawal', sans-serif", size: 11, weight: '700' }
                                }
                            },
                            y: {
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: {
                                    color: '#94a3b8',
                                    font: { family: "'JetBrains Mono', monospace", size: 11, weight: '700' },
                                    callback: function(val) {
                                        return val.toLocaleString('en-US');
                                    }
                                }
                            }
                        }
                    }
                });
            }
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
            // تطبيق الثيم بدون إعادة تحميل الصفحة - أسرع بكثير
            const isLight = document.documentElement.classList.contains('light-theme');
            document.documentElement.setAttribute('data-theme', isLight ? 'light' : 'dark');
        });

        // ═════════════════════════════════════════════════════════════════════
        //  CATEGORIZED DOMAIN HUBS ENGINE ("تجميع حسب الاختصاصات")
        // ═════════════════════════════════════════════════════════════════════
        const DOMAIN_CATEGORIES = [
            {
                id: 'cat_accounting',
                title: 'الحسابات والمالية',
                icon: 'fa-file-invoice-dollar',
                color: '#38bdf8',
                bg: 'rgba(56, 189, 248, 0.2)',
                border: '#38bdf8',
                screens: [
                    { href: 'account-statement.php', label: 'كشف حساب شريك', icon: 'fa-file-invoice-dollar', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'general-ledger.php', label: 'دفتر الأستاذ العام', icon: 'fa-book-bookmark', color: '#60a5fa', border: '#3b82f6', bg: 'rgba(59, 130, 246, 0.2)' },
                    { href: 'trial-balance.php', label: 'ميزان المراجعة', icon: 'fa-scale-balanced', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'pl-statement.php', label: 'قائمة الأرباح والخسائر', icon: 'fa-chart-line', color: '#c084fc', border: '#a855f7', bg: 'rgba(168, 85, 247, 0.2)' },
                    { href: 'posting-accounts.php', label: 'دليل وشجرة الحسابات', icon: 'fa-sitemap', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'journal-voucher.php', label: 'سند قيد اليومية', icon: 'fa-pen-to-square', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' }
                ]
            },
            {
                id: 'cat_sales',
                title: 'المبيعات والفواتير',
                icon: 'fa-cart-shopping',
                color: '#34d399',
                bg: 'rgba(16, 185, 129, 0.2)',
                border: '#10b981',
                screens: [
                    { href: 'direct-sales.php', label: 'المبيعات المباشرة ونقاط البيع', icon: 'fa-cart-shopping', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'sales-invoice.php', label: 'فواتير المبيعات الآجلة', icon: 'fa-receipt', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'sales-delivery.php', label: 'تسليم وإذون المبيعات', icon: 'fa-truck-ramp-box', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' },
                    { href: 'customer-portfolio.php', label: 'محفظة وتحليلات العملاء', icon: 'fa-users', color: '#c084fc', border: '#a855f7', bg: 'rgba(168, 85, 247, 0.2)' },
                    { href: 'reserved-sales.php', label: 'حجوزات المبيعات', icon: 'fa-bookmark', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' }
                ]
            },
            {
                id: 'cat_purchases',
                title: 'المشتريات والموردين',
                icon: 'fa-bag-shopping',
                color: '#fbbf24',
                bg: 'rgba(251, 191, 36, 0.2)',
                border: '#fbbf24',
                screens: [
                    { href: 'purchase.php', label: 'فواتير المشتريات', icon: 'fa-bag-shopping', color: '#fbbf24', border: '#fbbf24', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'purchase-receipt.php', label: 'استلام شحنات المشتريات', icon: 'fa-box-open', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'purchase-return.php', label: 'مرتجعات المشتريات', icon: 'fa-rotate-left', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' },
                    { href: 'supplier_reliability.php', label: 'اعتمادية وموثوقية الموردين', icon: 'fa-award', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' }
                ]
            },
            {
                id: 'cat_inventory',
                title: 'المخزون والمستودعات',
                icon: 'fa-warehouse',
                color: '#a78bfa',
                bg: 'rgba(167, 139, 250, 0.2)',
                border: '#a78bfa',
                screens: [
                    { href: 'inventory-summary.php', label: 'ملخص حركة المخزون', icon: 'fa-boxes-stacked', color: '#a78bfa', border: '#a78bfa', bg: 'rgba(167, 139, 250, 0.2)' },
                    { href: 'inventory-movement.php', label: 'حركة الأصناف التفصيلية', icon: 'fa-dolly', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'warehouse-inventory.php', label: 'مخزون المستودعات', icon: 'fa-warehouse', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'warehouse-transfer.php', label: 'التحويل بين المستودعات', icon: 'fa-arrows-left-right', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'item-ledger.php', label: 'دفتر حركة الصنف', icon: 'fa-barcode', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' }
                ]
            },
            {
                id: 'cat_balances',
                title: 'المقاييس والأرصدة والتقارير',
                icon: 'fa-scale-balanced',
                color: '#f472b6',
                bg: 'rgba(244, 114, 182, 0.2)',
                border: '#f472b6',
                screens: [
                    { href: 'mainbalances-report.php', label: 'تقرير الأرصدة الأساسية', icon: 'fa-scale-balanced', color: '#00f3ff', border: '#00f3ff', bg: 'rgba(0, 243, 255, 0.2)' },
                    { href: 'balances_modern_hub.php', label: 'أرصدة تجميعية متساوية', icon: 'fa-layer-group', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'balances_modern_hub.php', label: 'مركز الأرصدة الموحد', icon: 'fa-chart-pie', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'daily_master_report.php', label: 'التقرير اليومي الشامل', icon: 'fa-calendar-day', color: '#c084fc', border: '#a855f7', bg: 'rgba(168, 85, 247, 0.2)' },
                    { href: 'aging-report.php', label: 'تقرير أعمار الديون', icon: 'fa-clock-rotate-left', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' },
                    { href: 'combined_receivables.php', label: 'تجميعية المستحقات والديون', icon: 'fa-hand-holding-dollar', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' }
                ]
            },
            {
                id: 'cat_treasury',
                title: 'الخزن والبنوك والسيولة',
                icon: 'fa-vault',
                color: '#34d399',
                bg: 'rgba(52, 211, 153, 0.2)',
                border: '#34d399',
                screens: [
                    { href: 'cash-bank-statement.php', label: 'كشف حركة الخزن والبنوك', icon: 'fa-building-columns', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'multi-cash-payment.php', label: 'سند دفع وصرف متعدد', icon: 'fa-money-bill-wave', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'single-cash-payment.php', label: 'سند صرف نقدية', icon: 'fa-file-invoice', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' },
                    { href: 'single-cash-receipt.php', label: 'سند قبض نقدية', icon: 'fa-receipt', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'safe-transfer.php', label: 'تحويل نقدية بين الخزن', icon: 'fa-arrow-right-arrow-left', color: '#c084fc', border: '#a855f7', bg: 'rgba(168, 85, 247, 0.2)' },
                    { href: 'checks-cycle.php', label: 'دورة إدارة الشيكات', icon: 'fa-money-check-dollar', color: '#fbbf24', border: '#fbbf24', bg: 'rgba(251, 191, 36, 0.2)' }
                ]
            },
            {
                id: 'cat_system',
                title: 'الذكاء الاصطناعي والإدارة',
                icon: 'fa-robot',
                color: '#c084fc',
                bg: 'rgba(192, 132, 252, 0.2)',
                border: '#c084fc',
                screens: [
                    { href: 'ai_goods_advisor.php', label: 'مستشار AI للبضائع', icon: 'fa-robot', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'ai_money_advisor.php', label: 'مستشار AI للأموال', icon: 'fa-lightbulb', color: '#c084fc', border: '#a855f7', bg: 'rgba(168, 85, 247, 0.2)' },
                    { href: 'smart_inventory_report.php', label: 'تقارير الذكاء الاصطناعي', icon: 'fa-brain', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'master-data.php', label: 'إدارة البيانات الأساسية', icon: 'fa-database', color: '#60a5fa', border: '#3b82f6', bg: 'rgba(59, 130, 246, 0.2)' },
                    { href: 'users-roles.php', label: 'المستخدمون والصلاحيات', icon: 'fa-user-gear', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'settings.php', label: 'إعدادات النظام والقواعد', icon: 'fa-gear', color: '#94a3b8', border: '#64748b', bg: 'rgba(100, 116, 139, 0.2)' },
                    { href: 'backup.php', label: 'النسخ الاحتياطي', icon: 'fa-hard-drive', color: '#a78bfa', border: '#a78bfa', bg: 'rgba(167, 139, 250, 0.2)' },
                    { href: 'audit-log.php', label: 'سجل العمليات والرقابة', icon: 'fa-user-shield', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' }
                ]
            }
        ];

        let activeCategoryDropdownId = null;

        function renderCategoryHubs() {
            const grid = document.getElementById('category-hubs-grid');
            if (!grid) return;

            grid.innerHTML = DOMAIN_CATEGORIES.map(cat => `
                <button onclick="toggleCategoryMenu('${cat.id}', event)" id="btn-${cat.id}" class="qa-master-btn cat-hub-btn" style="--btn-border-color: ${cat.border}; --btn-glow-color: ${cat.bg}; border-bottom: 3.5px solid ${cat.border} !important; width: 100%;">
                    <span class="qa-icon" style="background: ${cat.bg}; border: 1px solid ${cat.border}; color: ${cat.color};">
                        <i class="fa-solid ${cat.icon}"></i>
                    </span>
                    <span class="btn-text" style="color: #ffffff; font-size: 0.85rem !important;">${cat.title}</span>
                    <i class="fa-solid fa-chevron-down cat-chevron" style="font-size: 0.72rem; color: ${cat.color}; margin-right: auto; transition: transform 0.3s ease;"></i>
                </button>
            `).join('');
        }

        function toggleCategoryMenu(catId, e) {
            if (e) e.stopPropagation();
            const popover = document.getElementById('category-dropdown-popover');
            const targetBtn = document.getElementById(`btn-${catId}`);
            if (!popover || !targetBtn) return;

            if (activeCategoryDropdownId === catId && popover.style.display === 'block') {
                closeCategoryDropdown();
                return;
            }

            const cat = DOMAIN_CATEGORIES.find(c => c.id === catId);
            if (!cat) return;

            activeCategoryDropdownId = catId;

            // Reset all chevrons
            document.querySelectorAll('.cat-chevron').forEach(ch => ch.style.transform = 'rotate(0deg)');

            const chev = targetBtn.querySelector('.cat-chevron');
            if (chev) chev.style.transform = 'rotate(180deg)';

            // Update title and border color
            popover.style.borderColor = cat.border;
            popover.style.boxShadow = `0 25px 95px rgba(0,0,0,0.99), 0 0 45px ${cat.bg}`;

            const popoverTitle = document.getElementById('cat-popover-title');
            if (popoverTitle) {
                popoverTitle.style.color = cat.color;
                popoverTitle.innerHTML = `<i class="fa-solid ${cat.icon}" style="color: ${cat.color}; font-size: 1.1rem;"></i> 📜 قائمة اختصاص (${cat.title})`;
            }

            const popoverList = document.getElementById('cat-popover-list');
            if (popoverList) {
                const visibleScreens = (window.MagaieesPermissions && typeof window.MagaieesPermissions.canUserAccessScreen === 'function')
                    ? cat.screens.filter(scr => window.MagaieesPermissions.canUserAccessScreen(scr.href))
                    : cat.screens;

                if (visibleScreens.length === 0) {
                    popoverList.innerHTML = '<div style="padding:20px; text-align:center; color:#94a3b8; font-size:0.9rem;">🚫 لا تملك صلاحية للوصول إلى أي شاشة في هذا القسم.</div>';
                } else {
                    popoverList.innerHTML = visibleScreens.map(scr => `
                        <a href="${scr.href}" onclick="window.location.href='${scr.href}'" class="screen-popover-item" style="--item-border-color: ${scr.border}; --item-glow-color: ${scr.bg}; border-bottom: 2.5px solid ${scr.border} !important; background: rgba(15, 23, 42, 0.95) !important;">
                            <div style="display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1;">
                                <span class="qa-icon" style="width: 32px; height: 32px; min-width: 32px; font-size: 0.88rem; border-radius: 8px; background: ${scr.bg}; border: 1px solid ${scr.border}; color: ${scr.color}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="fa-solid ${scr.icon}"></i>
                                </span>
                                <span class="screen-title" style="font-size: 0.92rem; font-weight: 800; color: #ffffff;">${scr.label}</span>
                            </div>
                            <i class="fa-solid fa-arrow-left screen-arrow" style="font-size: 0.8rem; color: ${scr.color}; opacity: 0.9;"></i>
                        </a>
                    `).join('');
                }
            }

            // Calculate Position relative to targetBtn in RTL layout
            popover.style.position = 'fixed';
            popover.style.display = 'block';

            const rect = targetBtn.getBoundingClientRect();
            const popoverWidth = Math.min(380, window.innerWidth - 24);
            popover.style.width = `${popoverWidth}px`;

            let topPos = rect.bottom + 8;
            if (topPos + 400 > window.innerHeight) {
                topPos = Math.max(12, rect.top - 420);
            }
            popover.style.top = `${topPos}px`;

            let rightPos = window.innerWidth - rect.right;
            if (rightPos + popoverWidth > window.innerWidth - 12) {
                rightPos = Math.max(12, window.innerWidth - popoverWidth - 12);
            }
            popover.style.right = `${rightPos}px`;
            popover.style.left = 'auto';
            popover.style.zIndex = '999999';
        }

        function closeCategoryDropdown() {
            const popover = document.getElementById('category-dropdown-popover');
            if (popover) popover.style.display = 'none';
            activeCategoryDropdownId = null;
            document.querySelectorAll('.cat-chevron').forEach(ch => ch.style.transform = 'rotate(0deg)');
        }

        document.addEventListener('click', function(e) {
            const popover = document.getElementById('category-dropdown-popover');
            if (popover && popover.style.display === 'block') {
                const targetBtn = activeCategoryDropdownId ? document.getElementById(`btn-${activeCategoryDropdownId}`) : null;
                if (!popover.contains(e.target) && (!targetBtn || !targetBtn.contains(e.target))) {
                    closeCategoryDropdown();
                }
            }
        });

        // ═════════════════════════════════════════════════════════════════════
        //  SINGLE-ROW 7-ITEM QUICK ACTIONS ENGINE WITH RIGHT-CLICK CUSTOMIZER
        // ═════════════════════════════════════════════════════════════════════
        const ALL_QUICK_ACTIONS = [
            { id: 'ai_goods', href: 'ai_goods_advisor.php', label: 'مستشار AI للبضائع', category: 'الذكاء الاصطناعي', icon: 'fa-robot', color: '#34d399', bg: 'rgba(16, 185, 129, 0.2)', border: '#10b981' },
            { id: 'smart_inv_alerts', href: 'smart_inventory_report.php', label: 'تنبيهات المخازن', category: 'المخزون والمستودعات', icon: 'fa-boxes-stacked', color: '#22d3ee', bg: 'rgba(6, 182, 212, 0.2)', border: '#06b6d4' },
            { id: 'ai_money', href: 'ai_money_advisor.php', label: 'مستشار AI للأموال', category: 'الذكاء الاصطناعي', icon: 'fa-lightbulb', color: '#c084fc', bg: 'rgba(168, 85, 247, 0.2)', border: '#a855f7' },
            { id: 'alerts_mgr', href: 'alerts_manager.php', label: 'تنبيهات التقارير', category: 'التقارير والتنبيهات', icon: 'fa-triangle-exclamation', color: '#fb7185', bg: 'rgba(244, 63, 94, 0.2)', border: '#f43f5e' },
            { id: 'settings', href: 'settings.php', label: 'الإعدادات والقواعد', category: 'الإدارة والصلاحيات', icon: 'fa-gear', color: '#94a3b8', bg: 'rgba(100, 116, 139, 0.2)', border: '#64748b' },
            { id: 'tools', href: 'engineering_tools.php', label: 'أدوات مساعدة', category: 'أدوات مساعدة', icon: 'fa-toolbox', color: '#f472b6', bg: 'rgba(236, 72, 153, 0.2)', border: '#ec4899' },
            { id: 'inheritance', href: 'engineering_tools.php?tab=inheritance', label: 'الميراث الشرعي', category: 'أدوات مساعدة', icon: 'fa-scale-balanced', color: '#d97706', bg: 'rgba(217, 119, 6, 0.2)', border: '#d97706' },
            { id: 'smart_inv_rep', href: 'smart_inventory_report.php', label: 'تقارير الذكاء', category: 'التقارير والتنبيهات', icon: 'fa-chart-line', color: '#34d399', bg: 'rgba(16, 185, 129, 0.2)', border: '#10b981' },
            { id: 'sales', href: 'direct-sales.php', label: 'مبيعات', category: 'المبيعات والمشتريات', icon: 'fa-cart-shopping', color: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)', border: '#38bdf8' },
            { id: 'purchase', href: 'purchase.php', label: 'مشتريات', category: 'المبيعات والمشتريات', icon: 'fa-bag-shopping', color: '#fbbf24', bg: 'rgba(251, 191, 36, 0.2)', border: '#fbbf24' },
            { id: 'vouchers', href: 'vouchers-hub.php', label: 'المعاملات المالية', category: 'المالية والحسابات', icon: 'fa-money-bill-transfer', color: '#f87171', bg: 'rgba(248, 113, 113, 0.2)', border: '#ef4444' },
            { id: 'checks', href: 'checks-cycle.php', label: 'شيكات', category: 'المالية والحسابات', icon: 'fa-money-check-dollar', color: '#fbbf24', bg: 'rgba(251, 191, 36, 0.2)', border: '#f59e0b' },
            { id: 'analytics', href: 'analytics.php', label: 'تقارير مالية', category: 'المالية والحسابات', icon: 'fa-chart-column', color: '#c084fc', bg: 'rgba(192, 132, 252, 0.2)', border: '#c084fc' },
            { id: 'smart_reports', href: 'smart_reports_hub.php', label: 'التقرير المالي الذكي', category: 'المالية والحسابات', icon: 'fa-brain', color: '#c084fc', bg: 'rgba(168, 85, 247, 0.2)', border: '#a855f7' },
            { id: 'master_data', href: 'master-data.php', label: 'البيانات الأساسية', category: 'البيانات الأساسية', icon: 'fa-database', color: '#60a5fa', bg: 'rgba(59, 130, 246, 0.2)', border: '#3b82f6' },
            { id: 'jobs', href: 'jobs.php', label: 'المهام', category: 'الإدارة والصلاحيات', icon: 'fa-list-check', color: '#34d399', bg: 'rgba(16, 185, 129, 0.2)', border: '#10b981' },
            { id: 'account_stmt', href: 'account-statement.php', label: 'كشف حساب', category: 'المالية والحسابات', icon: 'fa-file-invoice-dollar', color: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)', border: '#38bdf8' },
            { id: 'ledger', href: 'general-ledger.php', label: 'دفتر الأستاذ العام', category: 'المالية والحسابات', icon: 'fa-book-bookmark', color: '#60a5fa', bg: 'rgba(59, 130, 246, 0.2)', border: '#3b82f6' },
            { id: 'customer_portfolio', href: 'customer-portfolio.php', label: 'محفظة العملاء', category: 'المبيعات والمشتريات', icon: 'fa-users', color: '#34d399', bg: 'rgba(16, 185, 129, 0.2)', border: '#10b981' },
            { id: 'inv_report', href: 'inventory-report.php', label: 'تقرير المخزون الشامل', category: 'المخزون والمستودعات', icon: 'fa-warehouse', color: '#fbbf24', bg: 'rgba(251, 191, 36, 0.2)', border: '#fbbf24' },
            { id: 'sales_delivery', href: 'sales-delivery.php', label: 'تسليم المبيعات', category: 'المبيعات والمشتريات', icon: 'fa-truck-ramp-box', color: '#f87171', bg: 'rgba(248, 113, 113, 0.2)', border: '#ef4444' },
            { id: 'users_roles', href: 'users-roles.php', label: 'المستخدمون والصلاحيات', category: 'الإدارة والصلاحيات', icon: 'fa-user-gear', color: '#c084fc', bg: 'rgba(192, 132, 252, 0.2)', border: '#c084fc' },
            { id: 'partners', href: 'partners_reports_hub.php', label: 'الشركاء والتقارير', category: 'التقارير والتنبيهات', icon: 'fa-handshake', color: '#22d3ee', bg: 'rgba(6, 182, 212, 0.2)', border: '#06b6d4' },
            { id: 'backup', href: 'backup.php', label: 'النسخ الاحتياطي', category: 'الإدارة والصلاحيات', icon: 'fa-hard-drive', color: '#94a3b8', bg: 'rgba(100, 116, 139, 0.2)', border: '#64748b' }
        ];

        const DEFAULT_7_IDS = ['ai_goods', 'smart_inv_alerts', 'ai_money', 'alerts_mgr', 'settings', 'tools', 'smart_inv_rep'];
        let currentScreenSortMode = 'default';

        function sortScreensDropdown(mode) {
            currentScreenSortMode = mode;
            
            const btnDefault = document.getElementById('sort-btn-default');
            const btnAlphabetical = document.getElementById('sort-btn-alphabetical');
            const btnCategory = document.getElementById('sort-btn-category');
            
            [btnDefault, btnAlphabetical, btnCategory].forEach(btn => {
                if (!btn) return;
                btn.style.background = 'rgba(255, 255, 255, 0.08)';
                btn.style.borderColor = 'rgba(255, 255, 255, 0.15)';
                btn.style.color = '#94a3b8';
            });

            const activeBtn = document.getElementById(`sort-btn-${mode}`);
            if (activeBtn) {
                activeBtn.style.background = 'rgba(168, 85, 247, 0.35)';
                activeBtn.style.borderColor = '#c084fc';
                activeBtn.style.color = '#ffffff';
            }

            renderScreensDropdown();
        }

        function renderSingleRowQA() {
            let saved = localStorage.getItem('dashboard_quick_actions_7');
            let selectedIds = DEFAULT_7_IDS;
            if (saved) {
                try {
                    let parsed = JSON.parse(saved);
                    if (Array.isArray(parsed) && parsed.length > 0) selectedIds = parsed.slice(0, 7);
                } catch(e){}
            }

            const grid = document.getElementById('qa-single-row-grid');
            if (!grid) return;

            let activeItems = selectedIds.map(id => ALL_QUICK_ACTIONS.find(a => a.id === id)).filter(Boolean);
            if (window.MagaieesPermissions && typeof window.MagaieesPermissions.canUserAccessScreen === 'function') {
                activeItems = activeItems.filter(item => window.MagaieesPermissions.canUserAccessScreen(item.href));
            }
            
            grid.innerHTML = activeItems.map(item => `
                <a data-href="${item.href}" onclick="window.location.href='${item.href}'" class="qa-master-btn" style="--btn-border-color: ${item.border}; --btn-glow-color: ${item.bg}; border-bottom: 3.5px solid ${item.border} !important;">
                    <span class="qa-icon" style="background: ${item.bg}; border: 1px solid ${item.border}; color: ${item.color};">
                        <i class="fa-solid ${item.icon}"></i>
                    </span>
                    <span class="btn-text">${item.label}</span>
                </a>
            `).join('');
            
            renderScreensDropdown();
        }

        function renderScreensDropdown() {
            let saved = localStorage.getItem('dashboard_quick_actions_7');
            let selectedIds = DEFAULT_7_IDS;
            if (saved) {
                try {
                    let parsed = JSON.parse(saved);
                    if (Array.isArray(parsed) && parsed.length > 0) selectedIds = parsed.slice(0, 7);
                } catch(e){}
            }

            let unselected = ALL_QUICK_ACTIONS.filter(item => !selectedIds.includes(item.id));
            if (window.MagaieesPermissions && typeof window.MagaieesPermissions.canUserAccessScreen === 'function') {
                unselected = unselected.filter(item => window.MagaieesPermissions.canUserAccessScreen(item.href));
            }

            if (currentScreenSortMode === 'alphabetical') {
                unselected.sort((a, b) => a.label.localeCompare(b.label, 'ar'));
            } else if (currentScreenSortMode === 'category') {
                unselected.sort((a, b) => (a.category || '').localeCompare((b.category || ''), 'ar') || a.label.localeCompare(b.label, 'ar'));
            }
            const badge = document.getElementById('screens-count-badge');
            if (badge) badge.innerText = unselected.length;

            const listContainer = document.getElementById('screens-dropdown-list');
            if (!listContainer) return;

            if (unselected.length === 0) {
                listContainer.innerHTML = '<div style="grid-column: 1 / -1; text-align:center; padding:20px; color:#94a3b8; font-size:0.85rem; font-weight: 700;">جميع الشاشات مخصصة في الأزرار السبعة 🎉</div>';
                return;
            }

            listContainer.innerHTML = unselected.map(item => `
                <a href="${item.href}" onclick="window.location.href='${item.href}'" class="screen-popover-item screen-dropdown-item" style="--item-border-color: ${item.border}; --item-glow-color: ${item.bg}; --item-color: ${item.color}; border-bottom: 2.5px solid ${item.border} !important;">
                    <div style="display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1;">
                        <span class="qa-icon" style="width: 28px; height: 28px; min-width: 28px; font-size: 0.8rem; border-radius: 8px; background: ${item.bg}; border: 1px solid ${item.border}; color: ${item.color}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa-solid ${item.icon}"></i>
                        </span>
                        <span class="screen-title" style="font-size: 0.88rem; font-weight: 800; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.label}</span>
                    </div>
                    <i class="fa-solid fa-arrow-left screen-arrow" style="font-size: 0.75rem; color: ${item.color}; opacity: 0.85;"></i>
                </a>
            `).join('');
        }

        function positionScreensDropdown() {
            const menu = document.getElementById('screens-dropdown-menu');
            const btn = document.getElementById('screens-dropdown-btn');
            if (!menu || !btn) return;
            
            menu.style.position = 'fixed';
            // Start at the very top of the screen (12px), covering top clock/user bar
            menu.style.top = '12px';
            
            const menuWidth = Math.min(500, window.innerWidth - 24);
            menu.style.width = `${menuWidth}px`;
            
            // Align right side near the button in RTL layout
            const rect = btn.getBoundingClientRect();
            let rightPos = window.innerWidth - rect.right;
            if (rightPos + menuWidth > window.innerWidth - 12) {
                rightPos = Math.max(12, window.innerWidth - menuWidth - 12);
            }
            menu.style.right = `${rightPos}px`;
            menu.style.left = 'auto';
            menu.style.zIndex = '999999';
        }

        function toggleScreensDropdown(e) {
            if (e) e.stopPropagation();
            const menu = document.getElementById('screens-dropdown-menu');
            const chev = document.getElementById('screens-chevron');
            if (!menu) return;

            const isHidden = menu.style.display === 'none' || menu.style.display === '';
            if (isHidden) {
                renderScreensDropdown();
                positionScreensDropdown();
                menu.style.display = 'block';
                if (chev) chev.style.transform = 'rotate(180deg)';
                const searchInp = document.getElementById('screens-search-input');
                if (searchInp) {
                    searchInp.value = '';
                    setTimeout(() => searchInp.focus(), 50);
                }
            } else {
                menu.style.display = 'none';
                if (chev) chev.style.transform = 'rotate(0deg)';
            }
        }

        function filterScreensDropdown() {
            const query = (document.getElementById('screens-search-input')?.value || '').toLowerCase().trim();
            const items = document.querySelectorAll('#screens-dropdown-list .screen-dropdown-item');
            items.forEach(el => {
                const text = el.textContent.toLowerCase();
                el.style.display = text.includes(query) ? 'flex' : 'none';
            });
        }

        document.addEventListener('click', function(e) {
            const menu = document.getElementById('screens-dropdown-menu');
            const btn = document.getElementById('screens-dropdown-btn');
            if (menu && menu.style.display === 'block') {
                if (!menu.contains(e.target) && !btn.contains(e.target)) {
                    menu.style.display = 'none';
                    const chev = document.getElementById('screens-chevron');
                    if (chev) chev.style.transform = 'rotate(0deg)';
                }
            }
        });

        window.addEventListener('scroll', function() {
            const menu = document.getElementById('screens-dropdown-menu');
            if (menu && menu.style.display === 'block') {
                positionScreensDropdown();
            }
        }, { passive: true });

        window.addEventListener('resize', function() {
            const menu = document.getElementById('screens-dropdown-menu');
            if (menu && menu.style.display === 'block') {
                positionScreensDropdown();
            }
        }, { passive: true });

        function openQACustomizer(e) {
            if (e && e.preventDefault) e.preventDefault();
            let modal = document.getElementById('qa-customizer-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'qa-customizer-modal';
                modal.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); backdrop-filter:blur(8px); z-index:99999; display:flex; align-items:center; justify-content:center; direction:rtl;';
                document.body.appendChild(modal);
            }
            
            let saved = localStorage.getItem('dashboard_quick_actions_7');
            let selectedIds = DEFAULT_7_IDS;
            if (saved) {
                try { selectedIds = JSON.parse(saved); } catch(e){}
            }
            
            let listHTML = ALL_QUICK_ACTIONS.map(item => {
                const isChecked = selectedIds.includes(item.id) ? 'checked' : '';
                return `
                    <label style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:rgba(15,23,42,0.7); border:1px solid rgba(255,255,255,0.08); border-radius:10px; cursor:pointer; transition:0.2s;">
                        <input type="checkbox" class="qa-chk" value="${item.id}" ${isChecked} onchange="limitQAChecks(this)" style="width:18px; height:18px; accent-color:#38bdf8; cursor:pointer;">
                        <span style="width:24px; height:24px; border-radius:6px; background:${item.bg}; border:1px solid ${item.border}; color:${item.color}; display:inline-flex; align-items:center; justify-content:center; font-size:0.75rem;">
                            <i class="fa-solid ${item.icon}"></i>
                        </span>
                        <span style="font-weight:700; font-size:0.85rem; color:#fff;">${item.label}</span>
                    </label>
                `;
            }).join('');

            modal.innerHTML = `
                <div class="glass-panel" style="width:90%; max-width:560px; max-height:85vh; padding:25px; border-radius:20px; border:1px solid rgba(56,189,248,0.3); background:#0f172a; box-shadow:0 20px 60px rgba(0,0,0,0.9); overflow-y:auto;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:12px;">
                        <h3 style="margin:0; font-weight:800; font-size:1.1rem; color:#38bdf8; display:flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-sliders"></i> تخصيص أزرار الوصول السريع (7 أزرار في سطر واحد)
                        </h3>
                        <button onclick="document.getElementById('qa-customizer-modal').style.display='none'" style="background:none; border:none; color:#f87171; font-size:1.4rem; cursor:pointer;">&times;</button>
                    </div>
                    <p style="font-size:0.8rem; color:#94a3b8; margin-bottom:15px;">اختر 7 أزرار فقط ليتم عرضها في سطر واحد أعلى لوحة التحكم:</p>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:10px; margin-bottom:20px;">
                        ${listHTML}
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:10px;">
                        <button onclick="document.getElementById('qa-customizer-modal').style.display='none'" style="padding:8px 16px; border-radius:10px; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color:#fff; cursor:pointer;">إلغاء</button>
                        <button onclick="saveQACustomization()" style="padding:8px 20px; border-radius:10px; background:linear-gradient(135deg, #38bdf8, #2563eb); border:none; color:#fff; font-weight:800; cursor:pointer; box-shadow:0 4px 15px rgba(56,189,248,0.4);">حفظ التبديل</button>
                    </div>
                </div>
            `;
            modal.style.display = 'flex';
        }

        function limitQAChecks(chk) {
            const checked = document.querySelectorAll('.qa-chk:checked');
            if (checked.length > 7) {
                chk.checked = false;
                if (typeof showToast === 'function') showToast('⚠️ يرجى اختيار 7 أزرار كحد أقصى للسطر الواحد', 'warning');
            }
        }

        function saveQACustomization() {
            const checked = Array.from(document.querySelectorAll('.qa-chk:checked')).map(c => c.value);
            if (checked.length === 0) {
                if (typeof showToast === 'function') showToast('⚠️ يرجى اختيار زر واحد على الأقل', 'warning');
                return;
            }
            localStorage.setItem('dashboard_quick_actions_7', JSON.stringify(checked.slice(0, 7)));
            renderSingleRowQA();
            renderScreensDropdown();
            document.getElementById('qa-customizer-modal').style.display = 'none';
            if (typeof showToast === 'function') showToast('✅ تم حفظ التبديل وعرض الأزرار الـ 7 بنجاح', 'success');
        }

        // Initialize Quick Actions, Category Hubs & Screens Dropdown immediately on DOM ready
        function initDashboardNav() {
            renderCategoryHubs();
            renderSingleRowQA();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initDashboardNav);
        } else {
            initDashboardNav();
        }
        </script>
    
        <!-- Client Support Tickets FAB (Removed as per user request to integrate into AI menu) -->

        <!-- Client Support Modal -->
        <div id="client-support-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(10px); z-index: 10000; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
            <div class="glass-panel" style="width: 100%; max-width: 600px; max-height: 90vh; display: flex; flex-direction: column; padding: 0; overflow: hidden; border-radius: 20px;">
                <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.2);">
                    <h3 style="margin: 0; font-size: 1.2rem; display: flex; align-items: center; gap: 10px; color: white;">
                        <i class="fa-solid fa-headset" style="color: #60a5fa;"></i> مركز الدعم الفني
                    </h3>
                    <button onclick="closeClientSupportModal()" style="background: transparent; border: none; color: #ef4444; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 5px; border-radius: 50%; transition: background 0.2s;" onmouseover="this.style.backgroundColor='rgba(239, 68, 68, 0.1)'" onmouseout="this.style.backgroundColor='transparent'">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                
                <div style="display: flex; border-bottom: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.1);">
                    <button onclick="switchSupportTab('list')" id="tab-btn-list" style="flex: 1; padding: 15px; background: transparent; border: none; color: white; cursor: pointer; border-bottom: 2px solid #3b82f6; font-weight: bold; outline: none; transition: all 0.2s;">تذاكري</button>
                    <button onclick="switchSupportTab('new')" id="tab-btn-new" style="flex: 1; padding: 15px; background: transparent; border: none; color: #94a3b8; cursor: pointer; border-bottom: 2px solid transparent; font-weight: bold; outline: none; transition: all 0.2s;">تذكرة جديدة</button>
                </div>

                <!-- Tab: Ticket List -->
                <div id="support-tab-list" style="padding: 20px; overflow-y: auto; flex: 1; min-height: 300px;">
                    <div id="client-tickets-container" style="display: flex; flex-direction: column; gap: 10px;">
                        <!-- Tickets load here -->
                    </div>
                </div>

                <!-- Tab: New Ticket -->
                <div id="support-tab-new" style="padding: 20px; display: none; overflow-y: auto; flex: 1; min-height: 300px;">
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 8px; color: #cbd5e1; font-size: 0.9rem;">عنوان المشكلة / الطلب</label>
                        <input type="text" id="new-ticket-subject" placeholder="مثال: مشكلة في طباعة الفاتورة" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3); color: white; outline: none; box-sizing: border-box; font-family: inherit;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 8px; color: #cbd5e1; font-size: 0.9rem;">التفاصيل</label>
                        <textarea id="new-ticket-message" rows="5" placeholder="اشرح المشكلة بالتفصيل لمساعدتنا في حلها بسرعة..." style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3); color: white; outline: none; box-sizing: border-box; resize: vertical; font-family: inherit;"></textarea>
                    </div>
                    <button onclick="submitNewClientTicket()" style="width: 100%; padding: 14px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: opacity 0.2s; font-family: inherit;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">إرسال التذكرة</button>
                </div>
                
                <!-- Tab: Ticket Conversation -->
                <div id="support-tab-chat" style="padding: 0; display: none; flex-direction: column; flex: 1; min-height: 300px;">
                    <div style="padding: 15px; background: rgba(0,0,0,0.2); border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 10px;">
                        <button onclick="switchSupportTab('list')" style="background: rgba(255,255,255,0.1); border: none; border-radius: 6px; color: white; width: 30px; height: 30px; cursor: pointer; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-arrow-right"></i></button>
                        <h4 id="chat-ticket-subject" style="margin: 0; color: white; flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">...</h4>
                    </div>
                    <div id="chat-messages-container" style="flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; min-height: 250px;">
                        <!-- Messages -->
                    </div>
                    <div style="padding: 15px; background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.1); display: flex; gap: 10px;">
                        <input type="hidden" id="chat-ticket-id">
                        <input type="text" id="chat-reply-input" placeholder="اكتب ردك هنا..." style="flex: 1; padding: 12px 15px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3); color: white; outline: none; font-family: inherit;" onkeypress="if(event.key==='Enter') sendClientReply()">
                        <button onclick="sendClientReply()" style="background: #10b981; color: white; border: none; border-radius: 50%; width: 45px; height: 45px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; padding-right: 5px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" style="transform: rotate(180deg); pointer-events: none;">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" style="pointer-events: none;"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        function openClientSupportModal() {
            document.getElementById('client-support-modal').style.display = 'flex';
            switchSupportTab('list');
            loadClientTickets();
        }

        function closeClientSupportModal() {
            document.getElementById('client-support-modal').style.display = 'none';
        }

        function switchSupportTab(tab) {
            document.getElementById('support-tab-list').style.display = tab === 'list' ? 'block' : 'none';
            document.getElementById('support-tab-new').style.display = tab === 'new' ? 'block' : 'none';
            document.getElementById('support-tab-chat').style.display = tab === 'chat' ? 'flex' : 'none';
            
            document.getElementById('tab-btn-list').style.borderBottomColor = tab === 'list' ? '#3b82f6' : 'transparent';
            document.getElementById('tab-btn-list').style.color = tab === 'list' ? 'white' : '#94a3b8';
            document.getElementById('tab-btn-new').style.borderBottomColor = tab === 'new' ? '#3b82f6' : 'transparent';
            document.getElementById('tab-btn-new').style.color = tab === 'new' ? 'white' : '#94a3b8';
        }

        async function loadClientTickets() {
            const container = document.getElementById('client-tickets-container');
            container.innerHTML = '<div style="text-align:center; padding: 20px; color: #94a3b8;"><i class="fa-solid fa-circle-notch fa-spin"></i> جاري التحميل...</div>';
            try {
                const res = await fetch('api_index.php?r=client-tickets', {
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('authToken') }
                });
                const data = await res.json();
                if (data.success) {
                    if (data.tickets.length === 0) {
                        container.innerHTML = '<div style="text-align:center; padding: 30px; color: #64748b;"><i class="fa-solid fa-inbox" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i><br>لا توجد تذاكر حالية</div>';
                        return;
                    }
                    container.innerHTML = '';
                    data.tickets.forEach(tk => {
                        let statusColor = '#f59e0b'; let statusText = 'مفتوحة';
                        if (tk.status === 'in_progress') { statusColor = '#38bdf8'; statusText = 'قيد المعالجة'; }
                        if (tk.status === 'closed') { statusColor = '#10b981'; statusText = 'مغلقة'; }
                        
                        container.innerHTML += `
                            <div onclick="openClientTicketChat(${tk.id}, '${tk.subject.replace(/'/g, "\\'")}')" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 12px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='rgba(0,0,0,0.3)'">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <strong style="color: white; font-size: 1.05rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 70%;">${tk.subject}</strong>
                                    <span style="background: ${statusColor}20; color: ${statusColor}; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; border: 1px solid ${statusColor}40;">${statusText}</span>
                                </div>
                                <div style="color: #64748b; font-size: 0.8rem; display: flex; justify-content: space-between;">
                                    <span>تذكرة #${tk.id}</span>
                                    <span style="direction: ltr;">${new Date(tk.created_at).toLocaleString('ar-SA')}</span>
                                </div>
                            </div>
                        `;
                    });
                }
            } catch (e) {
                container.innerHTML = '<div style="text-align:center; color: #ef4444;">❌ خطأ في تحميل التذاكر</div>';
            }
        }

        async function submitNewClientTicket() {
            const subject = document.getElementById('new-ticket-subject').value.trim();
            const message = document.getElementById('new-ticket-message').value.trim();
            if (!subject || !message) {
                if (typeof showToast === 'function') showToast('يرجى كتابة عنوان وتفاصيل المشكلة', 'warning');
                else alert('يرجى كتابة عنوان وتفاصيل المشكلة');
                return;
            }
            try {
                const res = await fetch('api_index.php?r=client-tickets', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + localStorage.getItem('authToken') },
                    body: JSON.stringify({ subject, message })
                });
                const data = await res.json();
                if (data.success) {
                    document.getElementById('new-ticket-subject').value = '';
                    document.getElementById('new-ticket-message').value = '';
                    switchSupportTab('list');
                    loadClientTickets();
                    if (typeof showToast === 'function') showToast('✅ تم فتح التذكرة وإرسالها للدعم الفني', 'success');
                }
            } catch (e) {}
        }

        async function openClientTicketChat(id, subject) {
            document.getElementById('chat-ticket-id').value = id;
            document.getElementById('chat-ticket-subject').innerText = subject;
            switchSupportTab('chat');
            
            const container = document.getElementById('chat-messages-container');
            container.innerHTML = '<div style="text-align:center; padding: 20px; color: #94a3b8;"><i class="fa-solid fa-circle-notch fa-spin"></i> جاري التحميل...</div>';
            
            try {
                const res = await fetch(`api_index.php?r=client-tickets&a=replies&id=${id}`, {
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('authToken') }
                });
                const data = await res.json();
                if (data.success) {
                    container.innerHTML = '';
                    data.replies.forEach(rep => {
                        const isClient = rep.sender === 'client';
                        const align = isClient ? 'flex-start' : 'flex-end';
                        const bg = isClient ? '#3b82f6' : 'rgba(255,255,255,0.1)';
                        const color = isClient ? 'white' : '#cbd5e1';
                        const radius = isClient ? '15px 15px 0 15px' : '15px 15px 15px 0';
                        const name = isClient ? 'أنت' : 'الدعم الفني';
                        const icon = isClient ? 'fa-user' : 'fa-headset';
                        
                        container.innerHTML += `
                            <div style="align-self: ${align}; max-width: 85%; display: flex; flex-direction: column; gap: 5px;">
                                <span style="font-size: 0.75rem; color: #64748b; margin: 0 5px; align-self: ${align}; display: flex; align-items: center; gap: 5px;"><i class="fa-solid ${icon}"></i> ${name}</span>
                                <div style="background: ${bg}; color: ${color}; padding: 12px 18px; border-radius: ${radius}; font-size: 0.95rem; line-height: 1.6; border: 1px solid rgba(255,255,255,0.05);">
                                    ${rep.message.replace(/\n/g, '<br>')}
                                </div>
                                <span style="font-size: 0.7rem; color: #475569; align-self: ${align}; direction: ltr;">${new Date(rep.created_at).toLocaleString('ar-SA')}</span>
                            </div>
                        `;
                    });
                    if (data.replies.length === 0) {
                        container.innerHTML = '<div style="text-align:center; padding: 20px; color: #64748b;">لا توجد ردود حتى الآن</div>';
                    }
                    setTimeout(() => container.scrollTop = container.scrollHeight, 100);
                }
            } catch (e) {
                container.innerHTML = '<div style="text-align:center; color: #ef4444;">❌ خطأ في تحميل الردود</div>';
            }
        }

        async function sendClientReply() {
            const id = document.getElementById('chat-ticket-id').value;
            const input = document.getElementById('chat-reply-input');
            const message = input.value.trim();
            if (!message) return;
            
            input.disabled = true;
            try {
                const res = await fetch('api_index.php?r=client-tickets&a=reply', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + localStorage.getItem('authToken') },
                    body: JSON.stringify({ id, message })
                });
                const data = await res.json();
                if (data.success) {
                    input.value = '';
                    openClientTicketChat(id, document.getElementById('chat-ticket-subject').innerText);
                } else {
                    if (typeof showToast === 'function') showToast(data.error || 'خطأ في إرسال الرد', 'error');
                    else alert(data.error || 'خطأ في إرسال الرد');
                }
            } catch (e) {
                console.error("Reply Error: ", e);
                alert("حدث خطأ غير متوقع: " + e.message);
            }
            input.disabled = false;
            input.focus();
        }
        </script>
<script src="js/unified-brain.js?v=20260722"></script>
</body>
</html>
