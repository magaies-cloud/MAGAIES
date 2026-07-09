<?php $page_title = 'مقاييس (MAGAIEES) - لوحة التحكم'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>مقاييس (MAGAIEES) - لوحة التحكم</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
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
            padding: 20px 30px;
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

        /* Compact Ultra Premium Header */
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

        @keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

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

        .qa-icon { font-size: 1.1rem; transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: inline-block; filter: drop-shadow(0 2px 2px rgba(0,0,0,0.5)); }
        .quick-action-btn:hover .qa-icon { transform: scale(1.2) rotate(5deg) translateY(-2px); }

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

        @media (max-width: 768px) {
            .premium-header { flex-direction: column; gap: 12px; }
            .header-controls { justify-content: center; width: 100%; flex-wrap: wrap; }
            .charts-grid, .bottom-section { grid-template-columns: 1fr; }
        }
        
        /* Fix double sidebar spacing issue */
        body.has-sidebar { padding-right: 0 !important; }
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
        <?php include 'includes/header.php'; ?>

        <!-- Ultra Premium Quick Actions Grid -->
        <div class="glass-panel">
            <h3>
                <span style="font-size: 1.3rem;">⚡</span> إجراءات سريعة
            </h3>
            
            <div style="display: flex; gap: 10px; overflow-x: auto; padding-bottom: 5px; flex-wrap: nowrap;">
                
                <a data-href="account-statement.php?model=v-classic-ent" onclick="window.location.href='account-statement.php?model=v-classic-ent'" class="quick-action-btn qa-danger" style="flex: 1 1 auto; justify-content: center; white-space: nowrap; font-size: 0.85rem; padding: 8px 12px; border-bottom: 2px solid #f43f5e; background: rgba(244,63,94,0.15); color: #f43f5e;">
                    <span class="qa-icon">🏢</span> كشف مؤسسي
                </a>
                
                <a data-href="direct-sales.php" onclick="window.location.href='direct-sales.php'" class="quick-action-btn qa-primary" style="flex: 1 1 auto; justify-content: center; white-space: nowrap; font-size: 0.85rem; padding: 8px 12px;">
                    <span class="qa-icon">⚡</span> مبيعات
                </a>
                
                <a data-href="purchase.php" onclick="window.location.href='purchase.php'" class="quick-action-btn qa-warning" style="flex: 1 1 auto; justify-content: center; white-space: nowrap; font-size: 0.85rem; padding: 8px 12px;">
                    <span class="qa-icon">📦</span> مشتريات
                </a>
                

                
                <a data-href="vouchers-hub.php" onclick="window.location.href='vouchers-hub.php'" class="quick-action-btn qa-danger" style="flex: 1 1 auto; justify-content: center; white-space: nowrap; font-size: 0.85rem; padding: 8px 12px;">
                    <span class="qa-icon">💸</span> الحسابات
                </a>
                

                
                <a data-href="checks-cycle.php" onclick="window.location.href='checks-cycle.php'" class="quick-action-btn qa-warning" style="flex: 1 1 auto; justify-content: center; white-space: nowrap; font-size: 0.85rem; padding: 8px 12px;">
                    <span class="qa-icon">🧾</span> شيكات
                </a>
                
                <a data-href="settings.php" onclick="window.location.href='settings.php'" class="quick-action-btn" style="flex: 1 1 auto; justify-content: center; white-space: nowrap; font-size: 0.85rem; padding: 8px 12px; border-bottom: 2px solid #64748b;">
                    <span class="qa-icon">⚙️</span> نظام
                </a>
                
                <a data-href="jobs.php" onclick="window.location.href='jobs.php'" class="quick-action-btn" style="flex: 1 1 auto; justify-content: center; white-space: nowrap; font-size: 0.85rem; padding: 8px 12px; border-bottom: 2px solid #10b981;">
                    <span class="qa-icon">💼</span> إدارة
                </a>
                
                <a data-href="analytics.php" onclick="window.location.href='analytics.php'" class="quick-action-btn qa-purple" style="flex: 1 1 auto; justify-content: center; white-space: nowrap; font-size: 0.85rem; padding: 8px 12px;">
                    <span class="qa-icon">📊</span> إحصائيات
                </a>

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
    <script src="js/localforage.min.js"></script>
    <script src="js/app.js?v=<?php echo time(); ?>"></script>
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
        </script>
    
</body>
</html>

