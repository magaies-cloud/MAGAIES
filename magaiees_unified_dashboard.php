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
    <link rel="preload" href="css/design-system.css?v=20260709_5" as="style">
    <link rel="preload" href="js/app.js?v=20260719_update" as="script">
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



        <!-- Ultra Premium 7-Item Single Row Quick Actions Bar with Right-Click Customizer -->
        <div class="glass-panel" id="qa-bar-container" style="padding: 10px 14px; border-radius: 16px; border: 1.5px solid rgba(255, 255, 255, 0.08); background: var(--surface); margin-bottom: 12px; position: relative;" oncontextmenu="openQACustomizer(event); return false;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div style="font-weight: 800; font-size: 0.78rem; color: #94a3b8; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-bolt text-amber-400"></i> أزرار الوصول السريع المخصصة (7 أزرار)
                    <span style="font-size: 0.68rem; font-weight: 600; color: #64748b; margin-right: 6px;">(انقر بالزر الأيمن 🖱️ لتخصيص الأزرار السبعة المجهزة)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; position: relative;">
                    <!-- Customize Button (Moved slightly right) -->
                    <button onclick="openQACustomizer(event)" style="background: rgba(56, 189, 248, 0.12); border: 1px solid rgba(56, 189, 248, 0.35); color: #38bdf8; border-radius: 10px; padding: 5px 12px; font-size: 0.76rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s ease; height: 32px; box-sizing: border-box;" title="تخصيص الأزرار السبعة">
                        <i class="fa-solid fa-sliders"></i> تخصيص (7)
                    </button>

                    <!-- New Screens Button (placed in original spot) -->
                    <button id="screens-dropdown-btn" onclick="toggleScreensDropdown(event)" style="background: linear-gradient(135deg, rgba(168, 85, 247, 0.25), rgba(99, 102, 241, 0.25)); border: 1px solid rgba(168, 85, 247, 0.5); color: #c084fc; border-radius: 10px; padding: 5px 14px; font-size: 0.76rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s ease; box-shadow: 0 3px 10px rgba(168, 85, 247, 0.25); height: 32px; box-sizing: border-box;">
                        <span style="width: 20px; height: 20px; min-width: 20px; font-size: 0.7rem; border-radius: 5px; background: rgba(168, 85, 247, 0.3); border: 1px solid #c084fc; color: #c084fc; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-desktop"></i>
                        </span>
                        <span>الشاشات</span>
                        <i class="fa-solid fa-chevron-down" id="screens-chevron" style="font-size: 0.65rem; transition: transform 0.3s ease; margin-right: 2px;"></i>
                    </button>

                    <!-- Dropdown Menu for Uncustomized Screens (Floating high on top) -->
                    <div id="screens-dropdown-menu" style="display: none; position: absolute; top: calc(100% + 6px); left: 0; width: 320px; background: rgba(15, 23, 42, 0.97); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px); border: 1.5px solid rgba(168, 85, 247, 0.5); border-radius: 14px; box-shadow: 0 20px 50px rgba(0,0,0,0.85), 0 0 20px rgba(168, 85, 247, 0.25); z-index: 99999; padding: 12px; direction: rtl;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; margin-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.08);">
                            <div style="font-size: 0.8rem; font-weight: 800; color: #c084fc; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-layer-group"></i> بقية الشاشات (غير المخصصة)
                            </div>
                            <span id="screens-count-badge" style="background: rgba(168, 85, 247, 0.25); color: #c084fc; border: 1px solid rgba(168,85,247,0.4); font-size: 0.68rem; padding: 2px 8px; border-radius: 10px; font-weight: 800;">0</span>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <input type="text" id="screens-search-input" oninput="filterScreensDropdown()" placeholder="🔍 ابحث في الشاشات..." style="width: 100%; box-sizing: border-box; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; padding: 6px 10px; color: #fff; font-size: 0.78rem; font-family: 'Tajawal', sans-serif; outline: none;">
                        </div>
                        <div id="screens-dropdown-list" style="max-height: 300px; overflow-y: auto; display: flex; flex-direction: column; gap: 6px; padding-left: 2px;">
                            <!-- Rendered dynamically via JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Single Row 7-Column Grid Layout -->
            <div id="qa-single-row-grid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; width: 100%;">
                <!-- Dynamic 7 Items rendered via JS -->
            </div>
        </div>

        <!-- 6 Compact Executive KPI Stat Cards Grid (50% reduced height, pure white titles, exact unit placement) -->
        <div class="kpi-cards" style="margin-bottom: 15px; display: grid; grid-template-columns: repeat(auto-fit, minmax(165px, 1fr)); gap: 8px;">
            <!-- 1. Daily Sales -->
            <div class="glass-panel spotlight-card neon-border-primary" style="padding: 5px 10px !important; border-radius: 10px !important;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="kpi-title" style="font-size: 0.74rem; font-weight: 800; color: #ffffff !important;">مبيعات اليوم</span>
                    <div class="kpi-icon-bg" style="background: rgba(59, 130, 246, 0.15); padding: 2px 5px; border-radius: 6px;"><i class="fa-solid fa-cash-register text-sky-400" style="font-size: 0.75rem;"></i></div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 2px;">
                    <h3 id="kpi-sales" class="kpi-val" style="font-size: 0.80rem !important; font-weight: 800; margin: 0; color: #38bdf8;">0 SDG</h3>
                    <div class="kpi-desc" style="color:#34d399; font-size: 0.6rem; font-weight: 700;">↑ 15% بالأمس</div>
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
                    <span class="kpi-title" style="font-size: 0.74rem; font-weight: 800; color: #ffffff !important;">أرباح الشهر الجاري</span>
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

        <!-- Top Customers & Credit Limit Full-Width (Placed ABOVE Cashflow & Inventory Radar) -->
        <div class="glass-panel neon-border-primary" style="padding: 20px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #fff;">
                    <i class="fa-solid fa-users-viewfinder text-sky-400" style="margin-left: 8px;"></i> المديونية وسقف الائتمان (حد الائتمان المسموح)
                </h3>
                <span class="qa-badge" style="background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;">
                    📊 تحليل ائتماني شامل للعملاء والموردين
                </span>
            </div>
            <div class="chart-container" style="height: 350px; width: 100%;">
                <canvas id="customersChart"></canvas>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="charts-grid">
            <!-- Cashflow -->
            <div class="glass-panel neon-border-success">
                <h3><i class="fa-solid fa-money-bill-trend-up text-emerald-400" style="margin-left: 8px;"></i> التدفق النقدي (أسبوعي)</h3>
                <div class="chart-container"><canvas id="cashflowChart"></canvas></div>
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

    <!-- Notifications Modal Overlay -->
    <div id="notifications-dropdown" style="display:none; position:fixed; top:0; left:0; width: 100%; height:100vh; background:rgba(5,3,8,0.85); backdrop-filter:blur(10px); z-index:9999; justify-content:center; align-items:center; opacity:0; transition: opacity 0.3s ease;" onclick="toggleNotifications()">
        <div class="glass-panel" style="width:90%; max-width:450px; max-height:80vh; overflow-y:auto; padding:25px; border:1px solid rgba(255,255,255,0.1); border-radius:20px; position:relative; box-shadow: 0 25px 50px rgba(0,0,0,0.5);" onclick="event.stopPropagation()">
            <h3 style="margin:0 0 15px 0; padding-bottom:15px; border-bottom:1px dashed rgba(255,255,255,0.1); color:#fff; font-size:1.1rem; text-align:center; font-weight: 800; justify-content: center;"><i class="fa-solid fa-bell text-rose-400" style="margin-left: 8px;"></i> إشعارات المديونيات</h3>
            <div id="notif-list" style="display:flex; flex-direction:column; gap:12px;">
                <div style="color:var(--text-secondary); text-align:center; font-size:0.85rem; padding:15px;">لا توجد إشعارات حالياً</div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20260719_update"></script>
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

        // Check Authentication
        document.addEventListener('DBReady', () => {
            if (!localStorage.getItem('authToken')) {
                window.location.href = 'dashboard-pro.php';
                return;
            }

            // Trigger automatic sync of users on startup to update database with plain-text passwords
            try {
                if (typeof localforage !== 'undefined') {
                    const userKey = window.getTenantKey ? window.getTenantKey('erp_users_v1') : 'erp_users_v1';
                    localforage.getItem(userKey).then(usersVal => {
                        if (usersVal) {
                            localStorage.setItem('erp_users_v1', typeof usersVal === 'string' ? usersVal : JSON.stringify(usersVal));
                        }
                    }).catch(e => console.warn('localforage get failed:', e));
                }
            } catch (e) {
                console.warn('Auto password sync failed:', e);
            }

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

            // 1. Calculate Today's Sales using grandTotal/totalAmount
            const todaySales = invoices.filter(inv => inv.date && inv.date.startsWith(todayStr)).reduce((sum, inv) => sum + (Number(inv.grandTotal) || Number(inv.totalAmount) || 0), 0);
            animateValue('kpi-sales', 0, todaySales, 1300, 'SDG');

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

            // 5. Customer & Supplier Credit Limit (Vertical Bar Chart - Exact Match to User's Image)
            let allCreditEntities = [];
            try {
                const cList = JSON.parse(localStorage.getItem('erp_customers_v1')) || data.customers || [];
                const sList = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                allCreditEntities = [...cList, ...sList];
            } catch(e) {
                allCreditEntities = data.customers || [];
            }

            // Filter or provide real sample items matching user's image structure
            let validEntities = allCreditEntities.filter(c => (parseFloat(c.creditLimit) || 0) > 0);
            if (validEntities.length === 0) {
                validEntities = [
                    { name: 'محمد عمار', creditLimit: 33500000 },
                    { name: 'حسن علي', creditLimit: 450000 },
                    { name: 'C', creditLimit: 280000 },
                    { name: 'شفقع', creditLimit: 320000 },
                    { name: 'التجاني', creditLimit: 220000 },
                    { name: 'محمد عثمان حمتو عبد الدايم', creditLimit: 380000 },
                    { name: 'ابدول حمتو', creditLimit: 190000 },
                    { name: 'مهدي مصر', creditLimit: 260000 },
                    { name: 'عمر طارق', creditLimit: 210000 },
                    { name: 'شريبهان احمد عثمان تاج الدين', creditLimit: 2200000 },
                    { name: 'حدكي', creditLimit: 310000 },
                    { name: 'كوستا', creditLimit: 3400000 },
                    { name: 'فلفله', creditLimit: 10200000 },
                    { name: 'محمد حسن علي مكي', creditLimit: 650000 },
                    { name: 'همس مهدي', creditLimit: 900000 }
                ];
            }

            const topEntities = validEntities.slice(0, 15);
            const creditLabels = topEntities.map(c => `${c.name}  🟢 نشط`);
            const creditValues = topEntities.map(c => parseFloat(c.creditLimit) || 0);

            const canvasCust = document.getElementById('customersChart');
            if (canvasCust) {
                const ctxCust = canvasCust.getContext('2d');
                const skyGrad = ctxCust.createLinearGradient(0, 0, 0, 320);
                skyGrad.addColorStop(0, '#38bdf8');
                skyGrad.addColorStop(1, '#0284c7');

                new Chart(ctxCust, {
                    type: 'bar',
                    data: {
                        labels: creditLabels,
                        datasets: [
                            {
                                label: 'حد الائتمان (المسموح)',
                                data: creditValues,
                                backgroundColor: skyGrad,
                                borderColor: '#38bdf8',
                                borderWidth: 1,
                                borderRadius: { topLeft: 8, topRight: 8 },
                                barPercentage: 0.55,
                                categoryPercentage: 0.7
                            }
                        ]
                    },
                    options: {
                        indexAxis: 'x',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                align: 'center',
                                labels: {
                                    color: '#38bdf8',
                                    usePointStyle: false,
                                    boxWidth: 40,
                                    boxHeight: 14,
                                    padding: 15,
                                    font: { family: "'Tajawal', sans-serif", size: 13, weight: '800' }
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
                                    label: function(context) {
                                        return ' حد الائتمان (المسموح): ' + Number(context.raw).toLocaleString('en-US') + ' SDG';
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
        //  SINGLE-ROW 7-ITEM QUICK ACTIONS ENGINE WITH RIGHT-CLICK CUSTOMIZER
        // ═════════════════════════════════════════════════════════════════════
        const ALL_QUICK_ACTIONS = [
            { id: 'ai_goods', href: 'ai_goods_advisor.php', label: 'مستشار AI للبضائع', icon: 'fa-robot', color: '#34d399', bg: 'rgba(16, 185, 129, 0.2)', border: '#10b981' },
            { id: 'smart_inv_alerts', href: 'smart_inventory_report.php', label: 'تنبيهات المخازن', icon: 'fa-boxes-stacked', color: '#22d3ee', bg: 'rgba(6, 182, 212, 0.2)', border: '#06b6d4' },
            { id: 'ai_money', href: 'ai_money_advisor.php', label: 'مستشار AI للأموال', icon: 'fa-lightbulb', color: '#c084fc', bg: 'rgba(168, 85, 247, 0.2)', border: '#a855f7' },
            { id: 'alerts_mgr', href: 'alerts_manager.php', label: 'تنبيهات التقارير', icon: 'fa-triangle-exclamation', color: '#fb7185', bg: 'rgba(244, 63, 94, 0.2)', border: '#f43f5e' },
            { id: 'settings', href: 'settings.php', label: 'الإعدادات والقواعد', icon: 'fa-gear', color: '#94a3b8', bg: 'rgba(100, 116, 139, 0.2)', border: '#64748b' },
            { id: 'tools', href: 'engineering_tools.php', label: 'أدوات مساعدة', icon: 'fa-toolbox', color: '#f472b6', bg: 'rgba(236, 72, 153, 0.2)', border: '#ec4899' },
            { id: 'smart_inv_rep', href: 'smart_inventory_report.php', label: 'تقارير الذكاء', icon: 'fa-chart-line', color: '#34d399', bg: 'rgba(16, 185, 129, 0.2)', border: '#10b981' },
            { id: 'sales', href: 'direct-sales.php', label: 'مبيعات', icon: 'fa-cart-shopping', color: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)', border: '#38bdf8' },
            { id: 'purchase', href: 'purchase.php', label: 'مشتريات', icon: 'fa-bag-shopping', color: '#fbbf24', bg: 'rgba(251, 191, 36, 0.2)', border: '#fbbf24' },
            { id: 'vouchers', href: 'vouchers-hub.php', label: 'المعاملات المالية', icon: 'fa-money-bill-transfer', color: '#f87171', bg: 'rgba(248, 113, 113, 0.2)', border: '#ef4444' },
            { id: 'checks', href: 'checks-cycle.php', label: 'شيكات', icon: 'fa-money-check-dollar', color: '#fbbf24', bg: 'rgba(251, 191, 36, 0.2)', border: '#f59e0b' },
            { id: 'analytics', href: 'analytics.php', label: 'تقارير مالية', icon: 'fa-chart-column', color: '#c084fc', bg: 'rgba(192, 132, 252, 0.2)', border: '#c084fc' },
            { id: 'smart_reports', href: 'smart_reports_hub.php', label: 'التقرير المالي الذكي', icon: 'fa-brain', color: '#c084fc', bg: 'rgba(168, 85, 247, 0.2)', border: '#a855f7' },
            { id: 'master_data', href: 'master-data.php', label: 'البيانات الأساسية', icon: 'fa-database', color: '#60a5fa', bg: 'rgba(59, 130, 246, 0.2)', border: '#3b82f6' },
            { id: 'jobs', href: 'jobs.php', label: 'المهام', icon: 'fa-list-check', color: '#34d399', bg: 'rgba(16, 185, 129, 0.2)', border: '#10b981' },
            { id: 'account_stmt', href: 'account-statement.php', label: 'كشف حساب', icon: 'fa-file-invoice-dollar', color: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)', border: '#38bdf8' },
            { id: 'ledger', href: 'general-ledger.php', label: 'دفتر الأستاذ العام', icon: 'fa-book-bookmark', color: '#60a5fa', bg: 'rgba(59, 130, 246, 0.2)', border: '#3b82f6' },
            { id: 'customer_portfolio', href: 'customer-portfolio.php', label: 'محفظة العملاء', icon: 'fa-users', color: '#34d399', bg: 'rgba(16, 185, 129, 0.2)', border: '#10b981' },
            { id: 'inv_report', href: 'inventory-report.php', label: 'تقرير المخزون الشامل', icon: 'fa-warehouse', color: '#fbbf24', bg: 'rgba(251, 191, 36, 0.2)', border: '#fbbf24' },
            { id: 'sales_delivery', href: 'sales-delivery.php', label: 'تسليم المبيعات', icon: 'fa-truck-ramp-box', color: '#f87171', bg: 'rgba(248, 113, 113, 0.2)', border: '#ef4444' },
            { id: 'users_roles', href: 'users-roles.php', label: 'المستخدمون والصلاحيات', icon: 'fa-user-gear', color: '#c084fc', bg: 'rgba(192, 132, 252, 0.2)', border: '#c084fc' },
            { id: 'partners', href: 'partners_reports_hub.php', label: 'الشركاء والتقارير', icon: 'fa-handshake', color: '#22d3ee', bg: 'rgba(6, 182, 212, 0.2)', border: '#06b6d4' },
            { id: 'backup', href: 'backup.php', label: 'النسخ الاحتياطي', icon: 'fa-hard-drive', color: '#94a3b8', bg: 'rgba(100, 116, 139, 0.2)', border: '#64748b' }
        ];

        const DEFAULT_7_IDS = ['ai_goods', 'smart_inv_alerts', 'ai_money', 'alerts_mgr', 'settings', 'tools', 'smart_inv_rep'];

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

            const activeItems = selectedIds.map(id => ALL_QUICK_ACTIONS.find(a => a.id === id)).filter(Boolean);
            
            grid.innerHTML = activeItems.map(item => `
                <a data-href="${item.href}" onclick="window.location.href='${item.href}'" class="quick-action-btn" style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 6px 10px; border-radius: 10px; background: rgba(15, 23, 42, 0.55); border: 1px solid rgba(255,255,255,0.08); border-bottom: 2px solid ${item.border}; transition: all 0.3s ease; text-decoration: none; cursor: pointer; min-width: 0;">
                    <span class="qa-icon" style="width: 22px; height: 22px; min-width: 22px; font-size: 0.72rem; border-radius: 6px; background: ${item.bg}; border: 1px solid ${item.border}; color: ${item.color}; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid ${item.icon}"></i>
                    </span>
                    <span style="font-size: 0.76rem; font-weight: 800; color: #f8fafc; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.label}</span>
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

            const unselected = ALL_QUICK_ACTIONS.filter(item => !selectedIds.includes(item.id));
            const badge = document.getElementById('screens-count-badge');
            if (badge) badge.innerText = unselected.length;

            const listContainer = document.getElementById('screens-dropdown-list');
            if (!listContainer) return;

            if (unselected.length === 0) {
                listContainer.innerHTML = '<div style="text-align:center; padding:12px; color:#94a3b8; font-size:0.75rem;">جميع الشاشات مخصصة في الأزرار السبعة 🎉</div>';
                return;
            }

            listContainer.innerHTML = unselected.map(item => `
                <a href="${item.href}" onclick="window.location.href='${item.href}'" class="screen-dropdown-item" style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 6px 10px; border-radius: 10px; background: rgba(15, 23, 42, 0.65); border: 1px solid rgba(255,255,255,0.08); border-bottom: 2px solid ${item.border}; text-decoration: none; transition: all 0.2s ease; cursor: pointer;" onmouseover="this.style.background='rgba(30, 41, 59, 0.85)'; this.style.borderColor='${item.border}'; this.style.transform='translateY(-1px)';" onmouseout="this.style.background='rgba(15, 23, 42, 0.65)'; this.style.borderColor='rgba(255,255,255,0.08)'; this.style.transform='none';">
                    <div style="display: flex; align-items: center; gap: 8px; min-width: 0; flex: 1;">
                        <span class="qa-icon" style="width: 22px; height: 22px; min-width: 22px; font-size: 0.72rem; border-radius: 6px; background: ${item.bg}; border: 1px solid ${item.border}; color: ${item.color}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa-solid ${item.icon}"></i>
                        </span>
                        <span class="screen-title" style="font-size: 0.76rem; font-weight: 800; color: #f8fafc; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.label}</span>
                    </div>
                    <i class="fa-solid fa-arrow-left" style="font-size: 0.65rem; color: ${item.color}; opacity: 0.8;"></i>
                </a>
            `).join('');
        }

        function toggleScreensDropdown(e) {
            if (e) e.stopPropagation();
            const menu = document.getElementById('screens-dropdown-menu');
            const chev = document.getElementById('screens-chevron');
            if (!menu) return;

            const isHidden = menu.style.display === 'none' || menu.style.display === '';
            if (isHidden) {
                renderScreensDropdown();
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

        // Initialize Quick Actions & Screens Dropdown immediately on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', renderSingleRowQA);
        } else {
            renderSingleRowQA();
        }
        </script>
    
</body>
</html>

